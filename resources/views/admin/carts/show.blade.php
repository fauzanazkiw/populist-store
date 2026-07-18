@extends('admin.layouts.app')

@section('page_title', 'Cart for ' . $user->name)
@section('page_subtitle', 'Review customer cart items')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    {{-- Cart items --}}
    <div class="lg:col-span-2">
        {{-- Customer info --}}
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center gap-4 pb-6 border-b border-gray-100">
                <div class="w-12 h-12 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-lg">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $user->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                </div>
                <a href="{{ route('admin.customers.show', $user) }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                    View Profile →
                </a>
            </div>
        </div>

        {{-- Items --}}
        @if($cartItems->isEmpty())
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <svg class="mx-auto w-16 h-16 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Cart is empty</h3>
                <p class="text-gray-500">This customer hasn't added any items to their cart yet</p>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="divide-y divide-gray-100">
                    @foreach($cartItems as $item)
                        <div class="p-6 flex gap-4">
                            {{-- Product image --}}
                            <div class="flex-shrink-0">
                                @if($item->product && $item->product->mainImage)
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
                                <div class="mb-3">
                                    @if($item->product)
                                        <a href="{{ route('products.show', $item->product) }}" target="_blank" class="text-base font-semibold text-gray-900 hover:text-blue-600">
                                            {{ $item->product->name }}
                                        </a>
                                        <p class="text-sm text-gray-500 mt-0.5">
                                            {{ $item->product->category?->name ?? 'Uncategorized' }} • SKU: {{ $item->product->id }}
                                        </p>
                                    @else
                                        <p class="text-base font-semibold text-gray-400 italic">Produk telah dihapus</p>
                                    @endif
                                </div>

                                {{-- Status badges --}}
                                <div class="flex gap-2 mb-3">
                                    @if(!$item->product)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Unavailable
                                        </span>
                                    @else
                                        @if(!$item->product->active)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Inactive
                                            </span>
                                        @endif
                                        @if($item->product->stock === 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Out of Stock
                                            </span>
                                        @elseif($item->product->stock < 5)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                Low Stock ({{ $item->product->stock }})
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                In Stock ({{ $item->product->stock }})
                                            </span>
                                        @endif
                                    @endif
                                </div>

                                {{-- Quantity and price --}}
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">Quantity</p>
                                        <p class="text-lg font-semibold text-gray-900">{{ $item->quantity }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">Unit Price</p>
                                        <p class="text-lg font-semibold text-gray-900">Rp {{ number_format($item->product?->price ?? 0, 0, ',', '.') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">Subtotal</p>
                                        <p class="text-lg font-semibold text-gray-900">Rp {{ number_format(($item->product?->price ?? 0) * $item->quantity, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Summary --}}
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-sm p-6 sticky top-24">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Cart Summary</h2>

            {{-- Stats --}}
            <div class="space-y-4 mb-6 pb-6 border-b border-gray-100">
                <div>
                    <p class="text-xs text-gray-500 font-medium">Total Items</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $cartItems->sum('quantity') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium">Cart Value</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($totalValue, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium">Cart Created</p>
                    <p class="text-sm text-gray-900 mt-1">{{ $oldestItem?->created_at?->format('d M Y H:i') ?? 'N/A' }}</p>
                </div>
            </div>

            {{-- Info box --}}
            @if($cartItems->isNotEmpty())
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-blue-900">
                        This customer has <strong>{{ $cartItems->sum('quantity') }} item{{ $cartItems->sum('quantity') !== 1 ? 's' : '' }}</strong> in their cart worth <strong>Rp {{ number_format($totalValue, 0, ',', '.') }}</strong>. Consider reaching out to encourage checkout.
                    </p>
                </div>
            @endif

            {{-- Actions --}}
            <div class="space-y-3">
                <a href="{{ route('admin.customers.index') }}" class="w-full border border-gray-300 text-gray-700 py-2.5 rounded-lg hover:bg-gray-50 transition font-medium text-center block text-sm">
                    Back to Customers
                </a>
                <a href="{{ route('admin.carts.index') }}" class="w-full bg-gray-200 text-gray-700 py-2.5 rounded-lg hover:bg-gray-300 transition font-medium text-center block text-sm">
                    All Carts
                </a>
            </div>
        </div>
    </div>
</div>

@endsection
