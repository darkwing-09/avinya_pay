<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentSignal;
use App\Services\PaymentVerificationAgent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentAgentController extends Controller
{
    public function __construct(private PaymentVerificationAgent $agent) {}

    /**
     * Ingest payment evidence from Gmail, Android notifications, or SMS.
     */
    public function ingest(Request $request): JsonResponse
    {
        // Security: Optional secret header/parameter to prevent arbitrary public signal spoofing
        $agentSecret = env('AGENT_WEBHOOK_SECRET', 'sec_arIFgtqDF92CnF2uV8IZYk4ZQHfg0LsfyIRxddhD');
        $incomingSecret = $request->header('X-Agent-Secret') ?? $request->input('secret') ?? $request->bearerToken();
        
        if (config('app.env') === 'production' && env('REQUIRE_AGENT_SECRET', false)) {
            if (! $incomingSecret || ! hash_equals($agentSecret, (string) $incomingSecret)) {
                return response()->json(['ok' => false, 'error' => 'Unauthorized agent signal'], 401);
            }
        }

        $data = $request->validate([
            'raw_text' => ['nullable', 'string'],
            'text' => ['nullable', 'string'],
            'body' => ['nullable', 'string'],
            'snippet' => ['nullable', 'string'],
            'message' => ['nullable', 'string'],
            'source' => ['nullable', 'string', 'in:gmail,notification,sms,webhook'],
            'sender' => ['nullable', 'string', 'max:150'],
            'amount' => ['nullable', 'numeric'],
            'utr' => ['nullable', 'string', 'max:40'],
            'order_id' => ['nullable', 'string', 'max:40'],
        ]);

        $rawText = $data['raw_text'] ?? $data['text'] ?? $data['body'] ?? $data['snippet'] ?? $data['message'] ?? '';
        if (empty($rawText)) {
            $rawText = 'Payment: ' . ($data['amount'] ?? '') . ' UTR: ' . ($data['utr'] ?? '') . ' Order: ' . ($data['order_id'] ?? '');
        }

        $source = $data['source'] ?? 'notification';
        $sender = $data['sender'] ?? null;

        $signal = $this->agent->ingest($rawText, $source, $sender);

        // If explicit fields were provided, override any parsed misses
        if (! empty($data['utr']) && empty($signal->utr)) {
            $signal->utr = $data['utr'];
        }
        if (! empty($data['order_id']) && empty($signal->order_id)) {
            $signal->order_id = $data['order_id'];
        }
        if (! empty($data['amount']) && empty($signal->amount)) {
            $signal->amount = $data['amount'];
        }
        $signal->save();

        // Trigger agent matching
        $matchedOrder = $this->agent->matchSignal($signal);

        return response()->json([
            'ok' => true,
            'signal_id' => $signal->id,
            'status' => $signal->status,
            'extracted' => [
                'amount' => $signal->amount,
                'utr' => $signal->utr,
                'order_id' => $signal->order_id,
            ],
            'matched_order' => $matchedOrder ? [
                'order_no' => $matchedOrder->order_no,
                'status' => $matchedOrder->status,
                'amount' => $matchedOrder->amount,
            ] : null,
        ]);
    }

    /**
     * List recent signals (read-only for monitoring/debugging).
     */
    public function listSignals(): JsonResponse
    {
        $signals = PaymentSignal::latest('id')->limit(20)->get();

        return response()->json([
            'ok' => true,
            'count' => $signals->count(),
            'signals' => $signals,
        ]);
    }

    /**
     * Re-run agent verification for a specific order.
     */
    public function verifyOrder(string $orderNo): JsonResponse
    {
        $order = Order::where('order_no', $orderNo)->firstOrFail();

        // Search matching signals
        $signal = PaymentSignal::where('order_id', $order->order_no)
            ->orWhere('utr', $order->customer_utr)
            ->orWhere(function ($q) use ($order) {
                $q->where('amount', $order->amount)
                  ->where('created_at', '>=', $order->created_at->subMinutes(10));
            })->latest('id')->first();

        if ($signal) {
            $this->agent->matchSignal($signal);
            $order->refresh();
        }

        return response()->json([
            'order_no' => $order->order_no,
            'status' => $order->status,
            'paid_at' => $order->paid_at,
            'utr' => $order->utr,
            'matched_signal' => $signal ? $signal->id : null,
        ]);
    }

    /**
     * Purge all expired orders and expired pending checkouts.
     */
    public function cleanupExpired(Request $request): JsonResponse
    {
        $expiredQuery = Order::where(function ($q) {
            $q->where('status', Order::EXPIRED)
              ->orWhere(function ($sub) {
                  $sub->where('status', Order::PENDING)
                      ->where('expires_at', '<', now());
              });
        });

        $expiredIds = $expiredQuery->pluck('id');
        $deletedCount = $expiredIds->count();

        if ($deletedCount > 0) {
            \DB::table('webhook_outbox')->whereIn('order_id', $expiredIds)->delete();
            \DB::table('webhook_logs')->whereIn('order_id', $expiredIds)->delete();
            \DB::table('payment_signals')->whereIn('matched_order_id', $expiredIds)->update(['matched_order_id' => null]);
            Order::whereIn('id', $expiredIds)->delete();
        }

        return response()->json([
            'ok' => true,
            'deleted_expired_orders' => $deletedCount,
            'message' => "Successfully removed {$deletedCount} expired orders.",
        ]);
    }
}
