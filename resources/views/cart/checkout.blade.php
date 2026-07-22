@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-10">

    {{-- Page header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Checkout</h1>
        <p class="text-gray-500 mt-2">Complete your order</p>
    </div>

    {{-- Checkout form --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Form --}}
        <div class="lg:col-span-2">
            <form method="POST" action="{{ route('cart.checkout') }}" class="space-y-6">
                @csrf

                {{-- Shipping Address --}}
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Shipping Address</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input
                                type="text"
                                value="{{ auth()->user()->name }}"
                                disabled
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-700"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input
                                type="email"
                                value="{{ auth()->user()->email }}"
                                disabled
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-700"
                            >
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input
                                type="tel"
                                id="phone"
                                name="phone"
                                value="{{ old('phone') }}"
                                placeholder="08xxxxxxxxxx"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-black focus:outline-none"
                                required
                            >
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <textarea
                                id="address"
                                name="shipping_address"
                                rows="3"
                                placeholder="Enter your shipping address"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-black focus:outline-none"
                                required
                            >{{ old('shipping_address') }}</textarea>
                            @error('shipping_address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Payment Method --}}
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Method</h2>

                    <div class="flex items-center gap-3 p-4 border border-gray-200 rounded-lg bg-gray-50">
                        <svg class="w-6 h-6 text-gray-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-800">Pembayaran online via Midtrans</p>
                            <p class="text-xs text-gray-500">Anda akan diarahkan ke halaman pembayaran aman (kartu, VA, e-wallet, QRIS) setelah membuat order.</p>
                        </div>
                    </div>
                </div>

                {{-- Terms --}}
                <div class="flex items-start gap-3">
                    <input type="checkbox" id="terms" required class="w-4 h-4 mt-1">
                    <label for="terms" class="text-sm text-gray-600">
                        I agree to the terms and conditions and privacy policy
                    </label>
                </div>

                {{-- Submit button --}}
                <button
                    type="submit"
                    class="w-full bg-black text-white py-3 rounded-lg hover:bg-gray-800 transition font-semibold text-lg"
                >
                    Place Order
                </button>

                {{-- Back to cart --}}
                <a href="{{ route('cart.index') }}" class="block text-center text-sm text-gray-600 hover:text-gray-900 transition">
                    Back to Cart
                </a>
            </form>
        </div>

        {{-- Order Summary (Sticky) --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm p-6 sticky top-24">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">Order Summary</h2>

                {{-- Items list --}}
                <div class="space-y-3 mb-6 pb-6 border-b border-gray-100 max-h-64 overflow-y-auto">
                    @php
                        $cartItems = auth()->user()->cartItems()->with('product')->get();
                        $subtotal = $cartItems->sum(fn($item) => $item->product->price * $item->quantity);
                        $total = $subtotal;
                    @endphp

                    @foreach($cartItems as $item)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">
                                {{ $item->product->name }}
                                @if($item->size)
                                    <span class="text-gray-400">({{ $item->size }})</span>
                                @endif
                                <span class="text-gray-500">x{{ $item->quantity }}</span>
                            </span>
                            <span class="font-medium text-gray-900">
                                Rp {{ number_format($item->product->price * $item->quantity, 0, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                </div>

                {{-- Breakdown --}}
                <div class="space-y-3 mb-6 pb-6 border-b border-gray-100">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-medium text-gray-900">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Total --}}
                <div class="flex justify-between">
                    <span class="text-lg font-semibold text-gray-900">Total</span>
                    <span class="text-2xl font-bold text-gray-900">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
