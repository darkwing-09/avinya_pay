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
     */
    public function matchSignal(PaymentSignal $signal): ?Order
    {
        $order = null;

        // Strategy 1: Match by direct Order ID
        if ($signal->order_id) {
            $order = Order::where(function ($q) use ($signal) {
                $q->where('order_no', $signal->order_id)
                  ->orWhere('client_order_id', $signal->order_id);
            })->whereIn('status', [Order::PENDING, Order::REVIEW, Order::EXPIRED])->first();
        }

        // Strategy 2: Match by customer-submitted UTR
        if (! $order && $signal->utr) {
            $order = Order::where('customer_utr', $signal->utr)
                ->whereIn('status', [Order::PENDING, Order::REVIEW, Order::EXPIRED])
                ->first();
        }

        // Strategy 3: Match by exact amount for recently created pending orders (last 20 mins)
        if (! $order && $signal->amount) {
            $order = Order::where('status', Order::PENDING)
                ->where('amount', $signal->amount)
                ->where('created_at', '>=', now()->subMinutes(25))
                ->latest('id')
                ->first();
        }

        if ($order) {
            return $this->confirmPayment($order, $signal);
        }

        return null;
    }

    /**
     * Verify customer-submitted 12-digit UTR from the checkout page.
     */
    public function verifyCustomerUtr(Order $order, string $utr): Order
    {
        $utr = preg_replace('/\s+/', '', $utr);

        return DB::transaction(function () use ($order, $utr) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            // Check if there is an unmatched signal with this UTR
            $signal = PaymentSignal::where('utr', $utr)
                ->where('status', 'unmatched')
                ->latest('id')
                ->first();

            $verifiedBy = $signal ? 'agent_' . $signal->source : 'agent_utr_verified';

            $locked->update([
                'status' => Order::PAID,
                'utr' => $utr,
                'customer_utr' => $utr,
                'paid_at' => now(),
                'verified_by' => $verifiedBy,
            ]);

            if ($signal) {
                $signal->update([
                    'status' => 'matched',
                    'matched_order_id' => $locked->id,
                ]);
            }

            Log::info("Order {$locked->order_no} verified and marked PAID via {$verifiedBy}", [
                'utr' => $utr,
                'amount' => $locked->amount,
            ]);

            app(WebhookOutboxService::class)->record($locked->refresh(), 'order.paid');

            return $locked->refresh();
        });
    }

    /**
     * Mark an order as paid from a matched signal.
     */
    private function confirmPayment(Order $order, PaymentSignal $signal): Order
    {
        return DB::transaction(function () use ($order, $signal) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            $utr = $signal->utr ?: ($locked->customer_utr ?: 'AGENT_' . $signal->id);

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

            Log::info("Order {$locked->order_no} marked PAID from signal #{$signal->id} ({$signal->source})");

            app(WebhookOutboxService::class)->record($locked->refresh(), 'order.paid');

            return $locked->refresh();
        });
    }
}
