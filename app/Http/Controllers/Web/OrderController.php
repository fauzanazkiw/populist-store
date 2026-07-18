<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * List the authenticated user's orders.
     */
    public function index(): View
    {
        $orders = $this->authenticatedUser()->orders()
            ->withCount('items')
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    private function authenticatedUser(): User
    {
        /** @var User $user */
        $user = auth()->user();

        return $user;
    }

    /**
     * Show a single order belonging to the authenticated user.
     *
     * Midtrans redirects the customer back here after payment (the `finish`
     * callback). Because the webhook that flips the status may be delayed or
     * unreachable (common in local/sandbox setups), reconcile a still-pending
     * order against the gateway before rendering so a paid order no longer shows
     * as "menunggu".
     */
    public function show(Order $order, MidtransService $midtrans): View
    {
        abort_if($order->user_id !== auth()->id(), 403);

        if ($order->status === 'pending' && $order->payment_transaction_id) {
            $this->reconcilePaymentStatus($order, $midtrans);
        }

        $order->load('items.product.mainImage');

        return view('orders.show', compact('order'));
    }

    /**
     * Pull the latest transaction status from Midtrans and apply it locally.
     *
     * Failures are swallowed (logged only) so a gateway hiccup never blocks the
     * customer from viewing their order.
     */
    private function reconcilePaymentStatus(Order $order, MidtransService $midtrans): void
    {
        try {
            $result = $midtrans->getTransactionStatus($order->payment_transaction_id);

            if (! $result) {
                return;
            }

            $status = MidtransService::mapStatus(
                (string) ($result['transaction_status'] ?? ''),
                (string) ($result['fraud_status'] ?? 'accept'),
            );

            $order->applyPaymentStatus($status);
        } catch (\Throwable $e) {
            Log::warning('Midtrans status reconcile failed for order '.$order->id.': '.$e->getMessage());
        }
    }
}
