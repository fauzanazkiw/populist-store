@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-10">

    {{-- Page header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">My Shopping Cart</h1>
        <p class="text-gray-500 mt-2">Review and manage your items</p>
    </div>

    @if($cartItems->isEmpty())
        {{-- Empty cart --}}
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <svg class="mx-auto w-16 h-16 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Your cart is empty</h3>
            <p class="text-gray-500 mb-6">Start shopping to add items to your cart</p>
            <a href="{{ route('products.index') }}" class="inline-block bg-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition">
                Continue Shopping
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Cart items --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="divide-y divide-gray-100">
                        @foreach($cartItems as $item)
                            <div class="p-6 flex gap-4 hover:bg-gray-50 transition">
                                {{-- Product image --}}
                                <div class="flex-shrink-0">
                                    @if($item->product->mainImage)
                                        <img
                                            src="{{ asset('storage/' . $item->product->mainImage->path) }}"
                                            alt="{{ $item->product->name }}"
                                            class="w-24 h-24 rounded-lg object-cover bg-gray-100"
                                        >
                                    @else
                                        <div class="w-24 h-24 rounded-lg bg-gray-100 flex items-center justify-center">
                                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                {{-- Product info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-4 mb-3">
                                        <div>
                                            <a href="{{ route('products.show', $item->product) }}" class="text-base font-semibold text-gray-900 hover:text-blue-600 truncate block">
                                                {{ $item->product->name }}
                                            </a>
                                            <p class="text-sm text-gray-500 mt-0.5">
                                                SKU: {{ $item->product->id }}
                                            </p>
                                        </div>
                                        <form action="{{ route('cart.remove', $item) }}" method="POST" onsubmit="return confirm('Remove this item?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-medium transition">
                                                Remove
                                            </button>
                                        </form>
                                    </div>

                                    {{-- Quantity and price --}}
                                    <div class="flex items-center justify-between gap-4">
                                        <form action="{{ route('cart.update', $item) }}" method="POST" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <button type="button" onclick="decreaseQty(this)" class="px-2 py-1 bg-gray-100 rounded hover:bg-gray-200 transition">−</button>
                                            <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $item->product->stock }}" class="w-12 text-center border border-gray-300 rounded py-1 focus:outline-none focus:ring-2 focus:ring-black" onchange="this.form.submit()">
                                            <button type="button" onclick="increaseQty(this)" class="px-2 py-1 bg-gray-100 rounded hover:bg-gray-200 transition">+</button>
                                        </form>
                                        <div class="text-right">
                                            <p class="text-sm text-gray-500">Rp {{ number_format($item->product->price, 0, ',', '.') }}</p>
                                            <p class="text-lg font-semibold text-gray-900">
                                                Rp {{ number_format($item->product->price * $item->quantity, 0, ',', '.') }}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Stock warning --}}
                                    @if($item->product->stock === 0)
                                        <p class="text-xs text-red-600 font-medium mt-2">Out of stock</p>
                                    @elseif($item->product->stock < 5)
                                        <p class="text-xs text-amber-600 font-medium mt-2">Only {{ $item->product->stock }} left in stock</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Order summary --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm p-6 sticky top-24">
                    <h2 class="text-lg font-semibold text-gray-900 mb-6">Order Summary</h2>

                    {{-- Breakdown --}}
                    <div class="space-y-3 mb-6 pb-6 border-b border-gray-100">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium text-gray-900">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Shipping</span>
                            <span class="font-medium text-gray-900">Rp {{ number_format($shipping, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Tax (10%)</span>
                            <span class="font-medium text-gray-900">Rp {{ number_format($tax, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Total --}}
                    <div class="flex justify-between mb-6">
                        <span class="text-lg font-semibold text-gray-900">Total</span>
                        <span class="text-2xl font-bold text-gray-900">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>

                    {{-- Checkout button --}}
                    <form action="{{ route('cart.checkout') }}" method="POST" class="space-y-3">
                        @csrf
                        <button type="submit" class="w-full bg-black text-white py-3 rounded-lg hover:bg-gray-800 transition font-medium">
                            Proceed to Checkout
                        </button>
                    </form>

                    {{-- Continue shopping --}}
                    <a href="{{ route('products.index') }}" class="block w-full border border-gray-300 text-gray-700 py-2.5 rounded-lg hover:bg-gray-50 transition font-medium text-center text-sm mt-2">
                        Continue Shopping
                    </a>

                    {{-- Items count --}}
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <p class="text-xs text-gray-500">{{ $cartItems->count() }} item{{ $cartItems->count() !== 1 ? 's' : '' }} in cart</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>

<script>
function decreaseQty(btn) {
    const input = btn.parentElement.querySelector('input[type="number"]');
    const newVal = Math.max(1, parseInt(input.value) - 1);
    input.value = newVal;
}

function increaseQty(btn) {
    const input = btn.parentElement.querySelector('input[type="number"]');
    const max = parseInt(input.max);
    const newVal = Math.min(max, parseInt(input.value) + 1);
    input.value = newVal;
}
</script>
@endsection
