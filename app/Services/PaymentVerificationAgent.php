<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentSignal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Intelligent Payment Verification Agent
 * Extracts Order ID, UTR, and Amount from incoming Gmail receipts,
 * push notifications, and SMS feeds, then auto-reconciles pending orders.
 * Designed for high concurrency (1000+ simultaneous orders).
 */
class PaymentVerificationAgent
{
    /**
     * Extract structured payment details from raw text (Gmail body, push notification, SMS)
     */
    public function extract(string $text, ?string $sender = null): array
    {
        $details = [
            'amount' => null,
            'utr' => null,
            'order_id' => null,
            'payer_vpa' => null,
        ];

        // 1. Amount Extraction (₹19, Rs. 19, 19.00 INR, "Received Rs 19.00", "Credited with INR 19")
        if (preg_match('/(?:received|credited|payment\s+of|amount\s*(?:of)?)\s*(?:rs\.?|inr|₹)?\s*([0-9]+(?:\.[0-9]{1,2})?)/i', $text, $m)) {
            $details['amount'] = number_format((float) $m[1], 2, '.', '');
        } elseif (preg_match('/(?:rs\.?|inr|₹)\s*([0-9]+(?:\.[0-9]{1,2})?)/i', $text, $m)) {
            $details['amount'] = number_format((float) $m[1], 2, '.', '');
        }

        // 2. 12-digit UTR / RRN Extraction
        if (preg_match('/(?:utr|rrn|ref(?:erence)?\s*(?:no|num|id)?|txn\s*(?:id)?|transaction\s*id)[\s:#-]+([0-9]{12})\b/i', $text, $m)) {
            $details['utr'] = $m[1];
        } elseif (preg_match('/\b(UPI\s*(?:Ref|No)?\s*[:#-]?\s*([0-9]{12}))\b/i', $text, $m)) {
            $details['utr'] = $m[2];
        } elseif (preg_match('/\b([0-9]{12})\b/', $text, $m)) {
            $details['utr'] = $m[1];
        }

        // 3. Order ID Extraction (ORD... or gc_...)
        if (preg_match('/\b(ORD[A-Z0-9]{14,24})\b/', $text, $m)) {
            $details['order_id'] = $m[1];
        } elseif (preg_match('/\b(gc_[a-zA-Z0-9_]{6,24})\b/', $text, $m)) {
            $details['order_id'] = $m[1];
        }

        // 4. Payer VPA
        if (preg_match('/\b([a-zA-Z0-9.\-_]{2,49}@[a-zA-Z0-9]{2,20})\b/', $text, $m)) {
            $details['payer_vpa'] = strtolower($m[1]);
        }

        return $details;
    }

    /**
     * Ingest an incoming payment signal from Gmail, phone push notification, or SMS.
     */
    public function ingest(string $rawText, string $source = 'notification', ?string $sender = null): PaymentSignal
    {
        $extracted = $this->extract($rawText, $sender);

        $signal = PaymentSignal::create([
            'source' => $source,
            'sender' => $sender,
            'raw_text' => $rawText,
            'amount' => $extracted['amount'],
            'utr' => $extracted['utr'],
            'order_id' => $extracted['order_id'],
            'payer_vpa' => $extracted['payer_vpa'],
            'status' => 'unmatched',
        ]);

        Log::info('Payment signal ingested by Agent', [
            'id' => $signal->id,
            'source' => $source,
            'extracted' => $extracted,
        ]);

        $this->matchSignal($signal);

        return $signal->refresh();
    }

    /**
     * Reconcile an ingested signal against pending/review orders.
     * Orders are verified strictly by Order ID or matching UTR from official receipts.
     * NEVER by amount alone, preventing collisions even with 1000+ concurrent checkouts.
     */
    public function matchSignal(PaymentSignal $signal): ?Order
    {
        $order = null;

        // Primary: Match strictly by unique Order ID from the email / notification
        if ($signal->order_id) {
            $order = Order::where(function ($q) use ($signal) {
                $q->where('order_no', $signal->order_id)
                  ->orWhere('client_order_id', $signal->order_id);
            })->whereIn('status', [Order::PENDING, Order::REVIEW, Order::EXPIRED])->first();
        }

        // Secondary: Match by customer-submitted UTR if Order ID was not in the note
        if (! $order && $signal->utr) {
            $order = Order::where('customer_utr', $signal->utr)
                ->whereIn('status', [Order::PENDING, Order::REVIEW, Order::EXPIRED])
                ->first();
        }

        if ($order) {
            return $this->confirmPayment($order, $signal);
        }

        return null;
    }

    /**
     * Customer submits 12-digit UTR after the 5-minute wait window.
     * Checks if a verified signal is already in the ledger with this Order ID or UTR.
     * If found, marks PAID immediately; otherwise records customer_utr and waits for the email signal.
     */
    public function verifyCustomerUtr(Order $order, string $utr): Order
    {
        $utr = preg_replace('/\s+/', '', $utr);

        return DB::transaction(function () use ($order, $utr) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            // Look for existing verified signals with this Order ID or UTR
            $signal = PaymentSignal::where(function ($q) use ($locked, $utr) {
                $q->where('order_id', $locked->order_no)
                  ->orWhere('order_id', $locked->client_order_id)
                  ->orWhere('utr', $utr);
            })->latest('id')->first();

            if ($signal) {
                // Signal confirmed by Gmail/notification!
                $locked->update([
                    'status' => Order::PAID,
                    'utr' => $utr,
                    'customer_utr' => $utr,
                    'paid_at' => now(),
                    'verified_by' => 'agent_' . $signal->source,
                ]);

                $signal->update([
                    'status' => 'matched',
                    'matched_order_id' => $locked->id,
                ]);

                Log::info("Order {$locked->order_no} verified and marked PAID via matched signal #{$signal->id}");
                app(WebhookOutboxService::class)->record($locked->refresh(), 'order.paid');
            } else {
                // Keep record of UTR; will be confirmed automatically the second the Gmail signal arrives
                $locked->update([
                    'status' => Order::REVIEW,
                    'customer_utr' => $utr,
                    'utr_submitted_at' => now(),
                    'utr_attempts' => DB::raw('utr_attempts + 1'),
                ]);
                Log::info("Order {$locked->order_no} recorded customer UTR {$utr}; awaiting incoming Order ID from Gmail/notification");
            }

            return $locked->refresh();
        });
    }

    /**
     * Mark an order as paid from a verified signal.
     */
    private function confirmPayment(Order $order, PaymentSignal $signal): Order
    {
        return DB::transaction(function () use ($order, $signal) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === Order::PAID) {
                return $locked;
            }

            $utr = $signal->utr ?: ($locked->customer_utr ?: 'SIGNAL_' . $signal->id);

            $locked->update([
                'status' => Order::PAID,
                'paid_at' => now(),
                'utr' => $utr,
                'customer_utr' => $signal->utr ?: $locked->customer_utr,
                'verified_by' => 'agent_' . $signal->source,
            ]);

            $signal->update([
                'status' => 'matched',
                'matched_order_id' => $locked->id,
            ]);

            Log::info("Order {$locked->order_no} marked PAID from signal #{$signal->id} ({$signal->source}) with Order ID {$signal->order_id}");

            app(WebhookOutboxService::class)->record($locked->refresh(), 'order.paid');

            return $locked->refresh();
        });
    }
}
