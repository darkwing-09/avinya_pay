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
     * Prevents UTR replay / double-spending fraud and underpayment fraud.
     */
    public function verifyCustomerUtr(Order $order, string $utr): Order
    {
        $utr = preg_replace('/\s+/', '', $utr);

        return DB::transaction(function () use ($order, $utr) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            // Defense 1: Prevent UTR Replay Fraud (Double Spending)
            $alreadyUsed = Order::where('utr', $utr)
                ->where('id', '!=', $locked->id)
                ->where('status', Order::PAID)
                ->exists();

            if ($alreadyUsed) {
                Log::warning("FRAUD BLOCKED: Customer attempted to reuse already spent UTR {$utr} on order {$locked->order_no}");
                $locked->update([
                    'review_note' => 'This UTR has already been claimed on another completed order.',
                    'utr_attempts' => DB::raw('utr_attempts + 1'),
                ]);
                return $locked->refresh();
            }

            // Defense 2: Look for authentic UNMATCHED verified signals with this Order ID or UTR
            $signal = PaymentSignal::where(function ($q) use ($locked, $utr) {
                $q->where('order_id', $locked->order_no)
                  ->orWhere('order_id', $locked->client_order_id)
                  ->orWhere('utr', $utr);
            })->where('status', 'unmatched')
              ->whereNull('matched_order_id')
              ->latest('id')
              ->first();

            if ($signal) {
                // Defense 3: Underpayment Check
                if ($signal->amount !== null && round((float) $signal->amount, 2) < round((float) $locked->amount, 2)) {
                    Log::warning("FRAUD BLOCKED: Underpayment on UTR submit for {$locked->order_no}. Expected: {$locked->amount}, got {$signal->amount}");
                    $locked->update([
                        'status' => Order::REVIEW,
                        'customer_utr' => $utr,
                        'review_note' => "Partial payment received (₹{$signal->amount} of ₹{$locked->amount}). Full payment required.",
                    ]);
                    $signal->update(['status' => 'ignored', 'matched_order_id' => $locked->id]);
                    return $locked->refresh();
                }

                // Verified authentic signal matches!
                $locked->update([
                    'status' => Order::PAID,
                    'utr' => $utr,
                    'customer_utr' => $utr,
                    'paid_at' => now(),
                    'verified_by' => 'agent_' . $signal->source,
                    'review_note' => null,
                ]);

                $signal->update([
                    'status' => 'matched',
                    'matched_order_id' => $locked->id,
                ]);

                Log::info("Order {$locked->order_no} verified and marked PAID via matched signal #{$signal->id}");
                app(WebhookOutboxService::class)->record($locked->refresh(), 'order.paid');
            } else {
                // Defense 4: No matching bank signal yet -> strictly hold in REVIEW (NEVER mark PAID!)
                $locked->update([
                    'status' => Order::REVIEW,
                    'customer_utr' => $utr,
                    'utr_submitted_at' => now(),
                    'utr_attempts' => DB::raw('utr_attempts + 1'),
                    'review_note' => null,
                ]);
                Log::info("Order {$locked->order_no} recorded customer UTR {$utr}; awaiting authentic signal from Paytm/Bank");
            }

            return $locked->refresh();
        });
    }

    /**
     * Mark an order as paid from a verified signal.
     * Enforces strict amount matching (blocks partial payment attacks).
     */
    private function confirmPayment(Order $order, PaymentSignal $signal): Order
    {
        return DB::transaction(function () use ($order, $signal) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === Order::PAID) {
                return $locked;
            }

            // Defense: Prevent Underpayment Fraud
            if ($signal->amount !== null) {
                $signalAmt = round((float) $signal->amount, 2);
                $orderAmt = round((float) $locked->amount, 2);
                if ($signalAmt < $orderAmt) {
                    Log::warning("FRAUD BLOCKED: Underpayment detected for {$locked->order_no}. Order expects ₹{$orderAmt}, but signal was ₹{$signalAmt}");
                    $signal->update([
                        'status' => 'ignored',
                        'matched_order_id' => $locked->id,
                    ]);
                    $locked->update([
                        'status' => Order::REVIEW,
                        'review_note' => "Partial payment received (₹{$signalAmt} of ₹{$orderAmt}). Full payment required.",
                    ]);
                    return $locked->refresh();
                }
            }

            $utr = $signal->utr ?: ($locked->customer_utr ?: 'SIGNAL_' . $signal->id);

            // Defense: Check if this UTR was already used on another order
            if ($signal->utr) {
                $duplicate = Order::where('utr', $signal->utr)->where('id', '!=', $locked->id)->where('status', Order::PAID)->exists();
                if ($duplicate) {
                    Log::warning("FRAUD BLOCKED: Duplicate UTR {$signal->utr} detected in signal #{$signal->id} for order {$locked->order_no}");
                    $signal->update(['status' => 'ignored', 'matched_order_id' => $locked->id]);
                    return $locked->refresh();
                }
            }

            $locked->update([
                'status' => Order::PAID,
                'paid_at' => now(),
                'utr' => $utr,
                'customer_utr' => $signal->utr ?: $locked->customer_utr,
                'verified_by' => 'agent_' . $signal->source,
                'review_note' => null,
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
