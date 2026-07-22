@extends('layouts.app')

@php
    $statusStyles = [
        'pending'    => 'bg-amber-100 text-amber-800',
        'processing' => 'bg-blue-100 text-blue-800',
        'completed'  => 'bg-green-100 text-green-800',
        'failed'     => 'bg-red-100 text-red-800',
        'cancelled'  => 'bg-gray-100 text-gray-700',
    ];
@endphp

@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Order #{{ $order->id }}</h1>
            <p class="text-gray-500 mt-2">{{ $order->created_at->format('d M Y, H:i') }}</p>
        </div>
        <span class="px-3 py-1 rounded-full text-sm font-medium {{ $statusStyles[$order->status] ?? 'bg-gray-100 text-gray-700' }}">
            {{ ucfirst($order->status) }}
        </span>
    </div>

    {{-- Payment call-to-action --}}
    @if($order->status === 'pending')
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6 border border-amber-200">
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Menunggu Pembayaran</h2>
            <p class="text-sm text-gray-500 mb-4">Selesaikan pembayaran untuk memproses pesanan Anda.</p>

            @if($order->payment_link)
                <a href="{{ $order->payment_link }}"
                   class="inline-block bg-black text-white px-6 py-3 rounded-lg hover:bg-gray-800 transition font-semibold">
                    Bayar Sekarang
                </a>
            @else
                {{-- No link yet (invoice creation failed) — allow regeneration --}}
                <form method="POST" action="{{ route('orders.pay', $order) }}" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP</label>
                        <input type="tel" name="phone" required placeholder="08xxxxxxxxxx"
                               class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-black focus:outline-none">
                    </div>
                    <button type="submit" class="bg-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition font-semibold">
                        Buat Link Pembayaran
                    </button>
                </form>
            @endif
        </div>
    @elseif($order->status === 'completed')
        <div class="bg-green-50 border border-green-200 rounded-xl p-6 mb-6">
            <p class="font-semibold text-green-800">Pembayaran diterima</p>
            @if($order->paid_at)
                <p class="text-sm text-green-700 mt-1">Dibayar pada {{ $order->paid_at->format('d M Y, H:i') }}</p>
            @endif
        </div>
    @endif

    {{-- Items --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Items</h2>
        <div class="divide-y divide-gray-100">
            @foreach($order->items as $item)
                <div class="flex justify-between py-3 text-sm">
                    <span class="text-gray-700">
                        {{ $item->product?->name ?? 'Produk dihapus' }}
                        @if($item->size)
                            <span class="text-gray-400">({{ $item->size }})</span>
                        @endif
                        <span class="text-gray-400">x{{ $item->quantity }}</span>
                    </span>
                    <span class="font-medium text-gray-900">Rp {{ number_format($item->total, 0, ',', '.') }}</span>
                </div>
            @endforeach
        </div>

        <div class="flex justify-between mt-6 pt-4 border-t border-gray-100">
            <span class="text-lg font-semibold text-gray-900">Total</span>
            <span class="text-xl font-bold text-gray-900">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
        </div>
    </div>

    {{-- Resi pengiriman --}}
    @if($order->tracking_number)
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6 border border-blue-100">
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Informasi Pengiriman</h2>
            <div class="text-sm text-gray-600 space-y-1">
                @if($order->shipping_courier)
                    <div class="flex gap-2">
                        <span class="text-gray-400 min-w-24">Kurir</span>
                        <span class="font-medium text-gray-900">{{ $order->shipping_courier }}</span>
                    </div>
                @endif
                <div class="flex gap-2">
                    <span class="text-gray-400 min-w-24">Nomor Resi</span>
                    <span class="font-mono font-medium text-gray-900">{{ $order->tracking_number }}</span>
                </div>
                @if($order->shipped_at)
                    <div class="flex gap-2">
                        <span class="text-gray-400 min-w-24">Dikirim</span>
                        <span class="text-gray-900">{{ $order->shipped_at->format('d M Y, H:i') }}</span>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Shipping address --}}
    @if($order->shipping_address)
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Shipping Address</h2>
            <p class="text-sm text-gray-600 whitespace-pre-line">{{ is_array($order->shipping_address) ? implode("\n", $order->shipping_address) : $order->shipping_address }}</p>
        </div>
    @endif

    <a href="{{ route('orders.index') }}" class="text-sm text-gray-600 hover:text-gray-900 transition">&larr; Kembali ke daftar order</a>
</div>
@endsection
