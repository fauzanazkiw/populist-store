<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Api\ProductService;
use App\Services\MidtransService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private ProductService $products,
        private MidtransService $midtrans,
    ) {}

    /**
     * Show the shopping cart
     */
    public function index(): View
    {
        $cartItems = $this->authenticatedUser()->cartItems()->with('product.mainImage')->get();

        $subtotal = $this->cartSubtotal($cartItems);
        $total = $subtotal;

        return view('cart.index', compact('cartItems', 'subtotal', 'total'));
    }

    /**
     * Add item to cart
     */
    public function add(Request $request, Product $product): RedirectResponse
    {
        $hasSizes = ! empty($product->sizes);

        $request->validate([
            'quantity' => 'required|integer|min:1|max:'.$product->stock,
            'size' => [$hasSizes ? 'required' : 'nullable', Rule::in($product->sizes ?? [])],
        ]);

        abort_if(! $product->active, 404);
        abort_if($product->stock < $request->quantity, 422, 'Not enough stock available');

        $size = $hasSizes ? $request->input('size') : null;

        $user = $this->authenticatedUser();
        $cartItem = $user->cartItems()
            ->where('product_id', $product->id)
            ->where('size', $size)
            ->first();

        if ($cartItem) {
            // Update quantity if already in cart
            $newQuantity = $cartItem->quantity + $request->quantity;
            abort_if($newQuantity > $product->stock, 422, 'Not enough stock available');
            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            // Create new cart item
            $user->cartItems()->create([
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'size' => $size,
            ]);
        }

        return back()->with('success', 'Item added to cart');
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        abort_if($cartItem->user_id !== auth()->id(), 403);

        $request->validate([
            'quantity' => 'required|integer|min:1|max:'.$cartItem->product->stock,
        ]);

        $cartItem->update(['quantity' => $request->quantity]);

        return back()->with('success', 'Cart updated');
    }

    /**
     * Remove item from cart
     */
    public function remove(CartItem $cartItem): RedirectResponse
    {
        abort_if($cartItem->user_id !== auth()->id(), 403);

        $cartItem->delete();

        return back()->with('success', 'Item removed from cart');
    }

    /**
     * Clear entire cart
     */
    public function clear(): RedirectResponse
    {
        $this->authenticatedUser()->cartItems()->delete();

        return redirect()->route('cart.index')->with('success', 'Cart cleared');
    }

    /**
     * Show checkout page
     */
    public function checkoutShow(): View
    {
        $cartItems = $this->authenticatedUser()->cartItems()->with('product.mainImage')->get();

        abort_if($cartItems->isEmpty(), 422, 'Cart is empty');

        $subtotal = $this->cartSubtotal($cartItems);
        $total = $subtotal;

        return view('cart.checkout', compact('cartItems', 'subtotal', 'total'));
    }

    /**
     * Process checkout
     */
    public function checkout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shipping_address' => 'required|string',
            // Midtrans customer_details expects a mobile number.
            'phone' => 'required|string|max:20',
        ]);

        $user = $this->authenticatedUser();
        $cartItems = $user->cartItems()->with('product')->get();

        abort_if($cartItems->isEmpty(), 422, 'Cart is empty');

        // Validate all items are still in stock
        foreach ($cartItems as $item) {
            abort_if($item->product->stock < $item->quantity, 422, 'Not enough stock for '.$item->product->name);
        }

        // Calculate totals
        $subtotal = $this->cartSubtotal($cartItems);
        $total = $subtotal;

        // Persist order, items and stock changes atomically
        $order = DB::transaction(function () use ($cartItems, $user, $total, $validated) {
            $order = $user->orders()->create([
                'total' => $total,
                'status' => 'pending',
                'shipping_address' => $validated['shipping_address'],
            ]);

            foreach ($cartItems as $item) {
                $price = (int) $item->product->getRawOriginal('price');
                $lineTotal = $price * (int) $item->quantity;

                $order->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'size' => $item->size,
                    'price' => $price,
                    'total' => $lineTotal,
                ]);

                // Deduct stock through the service (guards against overselling)
                $this->products->deductStock($item->product, $item->quantity);
            }

            $user->cartItems()->delete();

            return $order;
        });

        // Create the Midtrans transaction outside the DB transaction: a
        // payment-gateway failure should not roll back an order whose stock is
        // already reserved.
        try {
            $payment = $this->midtrans->createTransaction($order, $validated['phone']);

            $order->update([
                'payment_link' => $payment['redirect_url'],
                'payment_transaction_id' => $payment['order_id'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Midtrans transaction creation failed for order '.$order->id.': '.$e->getMessage());

            return redirect()->route('orders.show', $order)
                ->with('warning', 'Order dibuat, tetapi link pembayaran gagal dibuat. Silakan coba lagi dari halaman order.');
        }

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order berhasil dibuat. Silakan lanjutkan pembayaran.');
    }

    private function cartSubtotal($cartItems): float
    {
        $subtotal = 0.0;

        foreach ($cartItems as $item) {
            $subtotal += (int) $item->product->getRawOriginal('price') * (int) $item->quantity;
        }

        return $subtotal;
    }

    private function authenticatedUser(): User
    {
        /** @var User $user */
        $user = auth()->user();

        return $user;
    }

    /**
     * Regenerate the Midtrans payment link for an existing pending order.
     */
    public function retryPayment(Request $request, Order $order): RedirectResponse
    {
        abort_if($order->user_id !== auth()->id(), 403);
        abort_if($order->status !== 'pending', 422, 'Order is not awaiting payment');

        $validated = $request->validate([
            'phone' => 'required|string|max:20',
        ]);

        try {
            $payment = $this->midtrans->createTransaction($order, $validated['phone']);

            $order->update([
                'payment_link' => $payment['redirect_url'],
                'payment_transaction_id' => $payment['order_id'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Midtrans transaction retry failed for order '.$order->id.': '.$e->getMessage());

            return back()->with('warning', 'Gagal membuat link pembayaran. Silakan coba lagi.');
        }

        return redirect()->away($order->payment_link);
    }
}
