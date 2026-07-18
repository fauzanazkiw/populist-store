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
<div class="max-w-4xl mx-auto px-4 py-10">

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">My Orders</h1>
        <p class="text-gray-500 mt-2">Track your orders and payments</p>
    </div>

    @if($orders->isEmpty())
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <h3 class="text-lg font-medium text-gray-900 mb-2">You have no orders yet</h3>
            <a href="{{ route('products.index') }}" class="inline-block mt-4 bg-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition">
                Start Shopping
            </a>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm overflow-hidden divide-y divide-gray-100">
            @foreach($orders as $order)
                <a href="{{ route('orders.show', $order) }}" class="flex items-center justify-between p-5 hover:bg-gray-50 transition">
                    <div>
                        <p class="font-semibold text-gray-900">Order #{{ $order->id }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $order->created_at->format('d M Y, H:i') }} &middot; {{ $order->items_count }} item(s)
                        </p>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="font-semibold text-gray-900">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                        <span class="px-3 py-1 rounded-full text-xs font-medium {{ $statusStyles[$order->status] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
