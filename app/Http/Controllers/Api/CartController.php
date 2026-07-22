<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CartController extends Controller
{
    /**
     * Get user's cart items
     */
    public function index(Request $request): JsonResponse
    {
        $cartItems = $request->user()
            ->cartItems()
            ->with('product.mainImage')
            ->get();

        $subtotal = $cartItems->sum(fn($item) => $item->product->price * $item->quantity);
        $total = $subtotal;

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $cartItems,
                'summary' => [
                    'subtotal' => $subtotal,
                    'total' => $total,
                    'item_count' => $cartItems->count(),
                ],
            ],
        ]);
    }

    /**
     * Add item to cart
     */
    public function add(Request $request, Product $product): JsonResponse
    {
        $hasSizes = ! empty($product->sizes);

        $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $product->stock,
            'size' => [$hasSizes ? 'required' : 'nullable', Rule::in($product->sizes ?? [])],
        ]);

        if (! $product->active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product is not available',
            ], 404);
        }

        if ($product->stock < $request->quantity) {
            return response()->json([
                'status' => 'error',
                'message' => 'Not enough stock available',
            ], 422);
        }

        $size = $hasSizes ? $request->input('size') : null;

        $cartItem = $request->user()
            ->cartItems()
            ->where('product_id', $product->id)
            ->where('size', $size)
            ->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $request->quantity;
            if ($newQuantity > $product->stock) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Not enough stock available',
                ], 422);
            }
            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            $cartItem = $request->user()->cartItems()->create([
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'size' => $size,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Item added to cart',
            'data' => $cartItem->load('product.mainImage'),
        ], 201);
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, CartItem $cartItem): JsonResponse
    {
        if ($cartItem->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $cartItem->product->stock,
        ]);

        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json([
            'status' => 'success',
            'message' => 'Cart updated',
            'data' => $cartItem->load('product.mainImage'),
        ]);
    }

    /**
     * Remove item from cart
     */
    public function remove(Request $request, CartItem $cartItem): JsonResponse
    {
        if ($cartItem->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 403);
        }

        $cartItem->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Item removed from cart',
        ]);
    }

    /**
     * Clear entire cart
     */
    public function clear(Request $request): JsonResponse
    {
        $request->user()->cartItems()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Cart cleared',
        ]);
    }
}
