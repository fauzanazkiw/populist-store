<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Receives HTTP notifications from Midtrans.
 *
 * Register this endpoint's URL as the "Payment Notification URL" in the
 * Midtrans dashboard (Settings → Configuration). Midtrans does not send a
 * bearer token; instead every call carries a signature_key we verify against
 * MIDTRANS_SERVER_KEY.
 *
 * @see https://docs.midtrans.com/reference/http-s-notification
 */
class MidtransWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $orderId = (string) $request->input('order_id');
        $statusCode = (string) $request->input('status_code');
        $grossAmount = (string) $request->input('gross_amount');

        // --- Authenticate via signature_key -----------------------------
        $serverKey = (string) config('services.midtrans.server_key');
        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        if ($serverKey === '' || ! hash_equals($expected, (string) $request->input('signature_key'))) {
            Log::warning('Midtrans webhook rejected: invalid signature', ['ip' => $request->ip()]);

            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // --- Locate the order -------------------------------------------
        $order = $this->resolveOrder($orderId);

        if (! $order) {
            Log::warning('Midtrans webhook: order not found', ['order_id' => $orderId]);

            // 200 so Midtrans does not keep retrying an unmatchable event.
            return response()->json(['message' => 'Order not found']);
        }

        // --- Apply the event (idempotent) -------------------------------
        $status = MidtransService::mapStatus(
            (string) $request->input('transaction_status'),
            (string) $request->input('fraud_status', 'accept'),
        );

        $order->applyPaymentStatus($status);

        return response()->json(['message' => 'ok']);
    }

    /**
     * Resolve the local order from the Midtrans order_id.
     *
     * Primary: exact match on the reference we stored at checkout. Fallback:
     * parse the local id out of our "POP-{id}-{timestamp}" convention.
     */
    private function resolveOrder(string $midtransOrderId): ?Order
    {
        if ($midtransOrderId === '') {
            return null;
        }

        if ($order = Order::where('payment_transaction_id', $midtransOrderId)->first()) {
            return $order;
        }

        if (preg_match('/^POP-(\d+)-/', $midtransOrderId, $m)) {
            return Order::find((int) $m[1]);
        }

        return null;
    }
}
