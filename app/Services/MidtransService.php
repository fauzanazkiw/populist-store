<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin client for the Midtrans "Snap" payment API.
 *
 * Snap returns a hosted payment page (redirect_url) that supports cards,
 * bank transfer / VA, e-wallets and QRIS — the same UX the store had before.
 *
 * @see https://docs.midtrans.com/reference/backend-integration
 */
class MidtransService
{
    public function __construct(
        private ?string $baseUrl = null,
        private ?string $serverKey = null,
        private ?string $apiUrl = null,
    ) {
        $this->baseUrl = rtrim($baseUrl ?? (string) config('services.midtrans.base_url'), '/');
        $this->serverKey = $serverKey ?? (string) config('services.midtrans.server_key');
        $this->apiUrl = rtrim($apiUrl ?? (string) config('services.midtrans.api_url'), '/');
    }

    /**
     * Create a Snap transaction for the given order and return Midtrans' payload.
     *
     * Returns an array with at least `redirect_url` (the customer-facing payment
     * page), `token` (the Snap token) and `order_id` (the reference we generated,
     * used to correlate incoming webhooks back to this order).
     *
     * @return array{redirect_url:string, token:string, order_id:string}
     */
    public function createTransaction(Order $order, string $customerMobile): array
    {
        if ($this->serverKey === '') {
            throw new RuntimeException('Midtrans server key is not configured (MIDTRANS_SERVER_KEY).');
        }

        $order->loadMissing(['user', 'items.product']);

        // Midtrans requires a globally-unique order_id per transaction. Embed the
        // local order id (so webhooks can be traced back) plus a timestamp so a
        // retry produces a fresh, non-colliding reference.
        $midtransOrderId = 'POP-'.$order->id.'-'.Carbon::now()->getTimestamp();

        $items = [];
        $lineItemsTotal = 0;

        foreach ($order->items as $item) {
            $quantity = (int) $item->quantity;
            $price = (int) $item->getRawOriginal('price');

            $items[] = [
                'id' => (string) $item->product_id,
                'price' => $price,
                'quantity' => $quantity,
                // Midtrans caps item names at 50 characters.
                'name' => mb_substr($item->product?->name ?? ('Product #'.$item->product_id), 0, 50),
            ];

            $lineItemsTotal += $price * $quantity;
        }

        // Fold shipping + tax (order total minus line items) into a single line so
        // gross_amount equals the sum of item_details — Midtrans rejects mismatches.
        $grossAmount = (int) $order->getRawOriginal('total');
        $adjustment = $grossAmount - $lineItemsTotal;
        if ($adjustment !== 0) {
            $items[] = [
                'id' => 'ADJ',
                'price' => $adjustment,
                'quantity' => 1,
                'name' => 'Shipping & tax',
            ];
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $midtransOrderId,
                'gross_amount' => $grossAmount,
            ],
            'item_details' => $items,
            'customer_details' => [
                'first_name' => $order->user?->name ?? 'Customer',
                'email' => $order->user?->email ?? 'customer@example.com',
                'phone' => $customerMobile,
            ],
            'callbacks' => [
                'finish' => route('orders.show', $order),
            ],
            'expiry' => [
                'unit' => 'hour',
                'duration' => 24,
            ],
        ];

        $response = Http::withBasicAuth($this->serverKey, '')
            ->acceptJson()
            ->timeout(30)
            ->post($this->baseUrl.'/transactions', $payload);

        if ($response->failed()) {
            throw new RuntimeException(
                'Midtrans transaction creation failed (HTTP '.$response->status().'): '.$response->body()
            );
        }

        if (empty($response->json('redirect_url')) || empty($response->json('token'))) {
            throw new RuntimeException('Midtrans response did not contain a payment redirect URL.');
        }

        return [
            'redirect_url' => $response->json('redirect_url'),
            'token' => $response->json('token'),
            'order_id' => $midtransOrderId,
        ];
    }

    /**
     * Fetch the current status of a transaction from the Midtrans Core API.
     *
     * Used to reconcile an order whose webhook never arrived (e.g. the
     * notification URL is unreachable in local/sandbox environments). Returns
     * the decoded response (containing `transaction_status`, `fraud_status`,
     * etc.) or null when the lookup fails or the transaction is unknown.
     *
     * @return array<string, mixed>|null
     */
    public function getTransactionStatus(string $midtransOrderId): ?array
    {
        if ($this->serverKey === '' || $midtransOrderId === '') {
            return null;
        }

        $response = Http::withBasicAuth($this->serverKey, '')
            ->acceptJson()
            ->timeout(30)
            ->get($this->apiUrl.'/'.rawurlencode($midtransOrderId).'/status');

        if ($response->failed()) {
            return null;
        }

        return $response->json();
    }

    /**
     * Translate a Midtrans transaction_status into a local order status.
     *
     * @return 'completed'|'failed'|'pending'
     */
    public static function mapStatus(string $transactionStatus, string $fraudStatus = 'accept'): string
    {
        return match ($transactionStatus) {
            'settlement' => 'completed',
            // Card captures are only settled once fraud review passes.
            'capture' => $fraudStatus === 'accept' ? 'completed' : 'pending',
            'deny', 'cancel', 'expire' => 'failed',
            default => 'pending',
        };
    }
}
