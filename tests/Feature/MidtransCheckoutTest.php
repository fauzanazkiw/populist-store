<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

/**
 * Create a customer with a single product ($product qty $qty) in their cart.
 *
 * @return array{0: User, 1: Product}
 */
function seedCartUser(int $stock = 10, int $cartQty = 2, int $price = 100000): array
{
    $category = Category::create(['name' => 'Shirts', 'slug' => 'shirts']);

    $product = Product::create([
        'name' => 'Test Tee',
        'slug' => 'test-tee',
        'description' => 'A tee',
        'price' => $price,
        'stock' => $stock,
        'active' => true,
        'category_id' => $category->id,
    ]);

    $user = User::factory()->create();
    $user->cartItems()->create(['product_id' => $product->id, 'quantity' => $cartQty]);

    return [$user, $product];
}

/**
 * Build the Midtrans notification signature the same way the gateway does.
 */
function midtransSignature(string $orderId, string $statusCode, string $grossAmount, string $serverKey): string
{
    return hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);
}

beforeEach(function () {
    config()->set('services.midtrans.base_url', 'https://app.sandbox.midtrans.com/snap/v1');
    config()->set('services.midtrans.server_key', 'test-server-key');
});

it('creates an order, stores the Midtrans payment link and deducts stock on checkout', function () {
    // Mock the gateway at the container boundary so the controller logic is
    // exercised without making a real HTTP call.
    $this->mock(MidtransService::class)
        ->shouldReceive('createTransaction')
        ->once()
        ->withArgs(function (Order $order, string $mobile): bool {
            // 2 * 100000 + 50000 shipping + 20000 tax
            return $mobile === '08123456789'
                && (int) $order->getRawOriginal('total') === 270000;
        })
        ->andReturn([
            'token' => 'snap-token-abc',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v3/redirection/abc',
            'order_id' => 'POP-1-1700000000',
        ]);

    [$user, $product] = seedCartUser(stock: 10, cartQty: 2, price: 100000);

    $response = $this->actingAs($user)->post(route('cart.checkout'), [
        'shipping_address' => 'Jl. Merdeka No. 1',
        'phone' => '08123456789',
    ]);

    $order = Order::first();

    expect($order)->not->toBeNull()
        ->and($order->status)->toBe('pending')
        ->and($order->payment_link)->toBe('https://app.sandbox.midtrans.com/snap/v3/redirection/abc')
        ->and($order->payment_transaction_id)->toBe('POP-1-1700000000');

    $response->assertRedirect(route('orders.show', $order));

    // Stock reserved: 10 - 2 = 8, and the cart is cleared.
    expect($product->fresh()->stock)->toBe(8)
        ->and($user->cartItems()->count())->toBe(0);
});

it('keeps the order but warns when Midtrans transaction creation fails', function () {
    $this->mock(MidtransService::class)
        ->shouldReceive('createTransaction')
        ->once()
        ->andThrow(new RuntimeException('gateway down'));

    [$user, $product] = seedCartUser(stock: 5, cartQty: 1);

    $response = $this->actingAs($user)->post(route('cart.checkout'), [
        'shipping_address' => 'Jl. Merdeka No. 2',
        'phone' => '08123456789',
    ]);

    $order = Order::first();

    expect($order)->not->toBeNull()
        ->and($order->status)->toBe('pending')
        ->and($order->payment_link)->toBeNull();

    $response->assertRedirect(route('orders.show', $order));
    $response->assertSessionHas('warning');

    // Stock is still deducted (order is valid, only the payment link is missing).
    expect($product->fresh()->stock)->toBe(4);
});

it('builds a Snap payload whose item_details sum to gross_amount', function () {
    Http::fake([
        '*/transactions' => Http::response([
            'token' => 'snap-token-999',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v3/redirection/999',
        ], 201),
    ]);

    [$user, $product] = seedCartUser(price: 100000);
    $order = $user->orders()->create(['total' => 270000, 'status' => 'pending']);
    $order->items()->create([
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 100000,
        'total' => 200000,
    ]);

    $data = app(MidtransService::class)->createTransaction($order, '08123456789');

    expect($data['redirect_url'])->toBe('https://app.sandbox.midtrans.com/snap/v3/redirection/999')
        ->and($data['token'])->toBe('snap-token-999')
        ->and($data['order_id'])->toStartWith('POP-'.$order->id.'-');

    Http::assertSent(function ($request) use ($order) {
        $itemsTotal = 0;

        foreach ($request['item_details'] as $item) {
            $itemsTotal += $item['price'] * $item['quantity'];
        }

        return str_contains($request->url(), '/transactions')
            && $request['customer_details']['phone'] === '08123456789'
            && str_starts_with($request['transaction_details']['order_id'], 'POP-'.$order->id.'-')
            // Product line (200000) + shipping & tax adjustment (70000) == gross_amount.
            && $itemsTotal === 270000
            && $request['transaction_details']['gross_amount'] === 270000;
    });
});

it('requires shipping address and phone', function () {
    [$user] = seedCartUser();

    $this->actingAs($user)
        ->post(route('cart.checkout'), [])
        ->assertSessionHasErrors(['shipping_address', 'phone']);

    expect(Order::count())->toBe(0);
});

it('marks an order completed when a valid payment webhook arrives', function () {
    [$user] = seedCartUser();
    $order = $user->orders()->create([
        'total' => 270000,
        'status' => 'pending',
        'payment_transaction_id' => 'POP-99-1700000000',
    ]);

    $payload = [
        'order_id' => 'POP-99-1700000000',
        'status_code' => '200',
        'gross_amount' => '270000.00',
        'transaction_status' => 'settlement',
    ];
    $payload['signature_key'] = midtransSignature(
        $payload['order_id'], $payload['status_code'], $payload['gross_amount'], 'test-server-key'
    );

    $this->postJson('/api/webhooks/midtrans', $payload)->assertOk();

    $order->refresh();
    expect($order->status)->toBe('completed')
        ->and($order->paid_at)->not->toBeNull();
});

it('resolves the order by parsing the local id out of the Midtrans order_id', function () {
    [$user] = seedCartUser();
    // No payment_transaction_id stored, so resolution falls back to parsing.
    $order = $user->orders()->create(['total' => 100000, 'status' => 'pending']);

    $orderId = 'POP-'.$order->id.'-1700000000';
    $payload = [
        'order_id' => $orderId,
        'status_code' => '200',
        'gross_amount' => '100000.00',
        'transaction_status' => 'capture',
        'fraud_status' => 'accept',
    ];
    $payload['signature_key'] = midtransSignature(
        $payload['order_id'], $payload['status_code'], $payload['gross_amount'], 'test-server-key'
    );

    $this->postJson('/api/webhooks/midtrans', $payload)->assertOk();

    expect($order->fresh()->status)->toBe('completed');
});

it('rejects webhooks with an invalid signature and leaves the order untouched', function () {
    [$user] = seedCartUser();
    $order = $user->orders()->create([
        'total' => 100000,
        'status' => 'pending',
        'payment_transaction_id' => 'POP-42-1700000000',
    ]);

    $this->postJson('/api/webhooks/midtrans', [
        'order_id' => 'POP-42-1700000000',
        'status_code' => '200',
        'gross_amount' => '100000.00',
        'transaction_status' => 'settlement',
        'signature_key' => 'forged-signature',
    ])->assertStatus(401);

    expect($order->fresh()->status)->toBe('pending');
});

it('reconciles a pending order against Midtrans when the customer views it', function () {
    config()->set('services.midtrans.api_url', 'https://api.sandbox.midtrans.com/v2');

    Http::fake([
        '*/v2/*/status' => Http::response([
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
        ]),
    ]);

    [$user] = seedCartUser();
    $order = $user->orders()->create([
        'total' => 100000,
        'status' => 'pending',
        'payment_transaction_id' => 'POP-55-1700000000',
    ]);

    $this->actingAs($user)->get(route('orders.show', $order))->assertOk();

    $order->refresh();
    expect($order->status)->toBe('completed')
        ->and($order->paid_at)->not->toBeNull();

    Http::assertSent(fn ($request) => str_contains($request->url(), '/POP-55-1700000000/status'));
});

it('leaves a pending order untouched when Midtrans status lookup fails', function () {
    config()->set('services.midtrans.api_url', 'https://api.sandbox.midtrans.com/v2');

    Http::fake([
        '*/v2/*/status' => Http::response([], 404),
    ]);

    [$user] = seedCartUser();
    $order = $user->orders()->create([
        'total' => 100000,
        'status' => 'pending',
        'payment_transaction_id' => 'POP-56-1700000000',
    ]);

    $this->actingAs($user)->get(route('orders.show', $order))->assertOk();

    expect($order->fresh()->status)->toBe('pending');
});

it('is idempotent and does not overwrite paid_at on repeat webhooks', function () {
    [$user] = seedCartUser();
    $paidAt = now()->subDay()->startOfSecond();
    $order = $user->orders()->create([
        'total' => 100000,
        'status' => 'completed',
        'paid_at' => $paidAt,
        'payment_transaction_id' => 'POP-7-1700000000',
    ]);

    $payload = [
        'order_id' => 'POP-7-1700000000',
        'status_code' => '200',
        'gross_amount' => '100000.00',
        'transaction_status' => 'settlement',
    ];
    $payload['signature_key'] = midtransSignature(
        $payload['order_id'], $payload['status_code'], $payload['gross_amount'], 'test-server-key'
    );

    $this->postJson('/api/webhooks/midtrans', $payload)->assertOk();

    expect($order->fresh()->paid_at->equalTo($paidAt))->toBeTrue();
});
