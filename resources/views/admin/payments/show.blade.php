@extends('admin.layouts.app')

@section('title', 'Pesanan #' . $order->id)
@section('page_title', 'Pesanan #' . $order->id)
@section('page_subtitle', 'Detail pesanan dan pembayaran')

@section('page_actions')
    <a href="{{ route('admin.payments.index') }}"
       class="inline-flex items-center gap-2 border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition font-medium text-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>
@endsection

@php
    $statusMap = [
        'pending'    => ['label' => 'Menunggu',  'class' => 'bg-amber-50 text-amber-700 ring-amber-600/20'],
        'processing' => ['label' => 'Diproses',  'class' => 'bg-blue-50 text-blue-700 ring-blue-600/20'],
        'completed'  => ['label' => 'Selesai',   'class' => 'bg-green-50 text-green-700 ring-green-600/20'],
        'failed'     => ['label' => 'Gagal',     'class' => 'bg-red-50 text-red-700 ring-red-600/20'],
        'cancelled'  => ['label' => 'Dibatalkan','class' => 'bg-gray-100 text-gray-600 ring-gray-500/20'],
    ];
    $current = $statusMap[$order->status] ?? ['label' => ucfirst($order->status), 'class' => 'bg-gray-100 text-gray-600 ring-gray-500/20'];
@endphp

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    {{-- ── Left: order details ── --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Status + meta --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div>
                    <p class="text-sm text-gray-500">Nomor Pesanan</p>
                    <p class="text-2xl font-bold text-gray-900">#{{ $order->id }}</p>
                    <p class="text-sm text-gray-500 mt-1">Dibuat {{ $order->created_at?->format('d M Y, H:i') ?? '—' }}</p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ring-1 ring-inset {{ $current['class'] }}">
                    {{ $current['label'] }}
                </span>
            </div>

            @if($order->paid_at)
                <div class="mt-4 pt-4 border-t border-gray-100 text-sm text-gray-600">
                    Dibayar pada <span class="font-medium text-gray-900">{{ $order->paid_at->format('d M Y, H:i') }}</span>
                    @if($order->payment_transaction_id)
                        • ID Transaksi: <span class="font-mono text-gray-900">{{ $order->payment_transaction_id }}</span>
                    @endif
                </div>
            @endif
        </div>

        {{-- Items --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Item Pesanan ({{ $order->items->count() }})</h2>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($order->items as $item)
                    <div class="p-6 flex gap-4">
                        <div class="flex-shrink-0">
                            @if($item->product && $item->product->mainImage)
                                <img src="{{ asset('storage/' . $item->product->mainImage->path) }}"
                                     alt="{{ $item->product->name }}"
                                     class="w-20 h-20 rounded-lg object-cover bg-gray-100">
                            @else
                                <div class="w-20 h-20 rounded-lg bg-gray-100 flex items-center justify-center">
                                    <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            @if($item->product)
                                <a href="{{ route('products.show', $item->product) }}" target="_blank"
                                   class="font-semibold text-gray-900 hover:text-blue-600">{{ $item->product->name }}</a>
                                <p class="text-sm text-gray-500 mt-0.5">{{ $item->product->category?->name ?? 'Tanpa kategori' }}</p>
                            @else
                                <p class="font-semibold text-gray-400 italic">Produk telah dihapus</p>
                            @endif
                            <p class="text-sm text-gray-600 mt-2">
                                {{ $item->quantity }} × Rp {{ number_format($item->price, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500 mb-1">Subtotal</p>
                            <p class="font-semibold text-gray-900">Rp {{ number_format($item->total, 0, ',', '.') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500">Tidak ada item pada pesanan ini.</div>
                @endforelse
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                <span class="font-medium text-gray-700">Total</span>
                <span class="text-xl font-bold text-gray-900">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Shipping address --}}
        @if(!empty($order->shipping_address))
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-900 mb-4">Alamat Pengiriman</h2>
                <div class="text-sm text-gray-700 space-y-1">
                    @if(is_array($order->shipping_address))
                        @foreach($order->shipping_address as $key => $value)
                            @if(!is_null($value) && $value !== '')
                                <div class="flex gap-2">
                                    <span class="text-gray-400 capitalize min-w-32">{{ str_replace('_', ' ', $key) }}</span>
                                    <span class="text-gray-900">{{ is_array($value) ? implode(', ', $value) : $value }}</span>
                                </div>
                            @endif
                        @endforeach
                    @else
                        <p>{{ $order->shipping_address }}</p>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- ── Right: customer + status update ── --}}
    <div class="lg:col-span-1 space-y-6">

        {{-- Customer --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Pelanggan</h2>
            @if($order->user)
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-full bg-gray-900 flex items-center justify-center text-white font-bold">
                        {{ strtoupper(substr($order->user->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="font-medium text-gray-900 truncate">{{ $order->user->name }}</p>
                        <p class="text-sm text-gray-500 truncate">{{ $order->user->email }}</p>
                    </div>
                </div>
                <a href="{{ route('admin.customers.show', $order->user) }}"
                   class="mt-4 inline-block text-sm text-blue-600 hover:text-blue-800 font-medium">
                    Lihat profil pelanggan →
                </a>
            @else
                <p class="text-sm text-gray-500 italic">Pelanggan telah dihapus.</p>
            @endif
        </div>

        {{-- Update status --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Ubah Status</h2>
            <form method="POST" action="{{ route('admin.payments.update-status', $order) }}" class="space-y-3">
                @csrf
                @method('PATCH')
                <select name="status"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-black focus:outline-none text-sm bg-white">
                    @foreach($statusMap as $value => $meta)
                        <option value="{{ $value }}" @selected($order->status === $value)>{{ $meta['label'] }}</option>
                    @endforeach
                </select>
                @error('status')
                    <p class="text-red-500 text-xs">{{ $message }}</p>
                @enderror
                <button type="submit"
                        class="w-full bg-black text-white py-2.5 rounded-lg hover:bg-gray-800 transition font-medium text-sm">
                    Simpan Perubahan
                </button>
            </form>
        </div>

        {{-- Resi pengiriman (input manual oleh admin, setelah dibayar) --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <h2 class="font-semibold text-gray-900 mb-1">Resi Pengiriman</h2>
            <p class="text-xs text-gray-500 mb-4">Nomor resi ditambahkan manual oleh admin.</p>

            @if($order->isPaid())
                <form method="POST" action="{{ route('admin.payments.update-tracking', $order) }}" class="space-y-3">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kurir / Ekspedisi</label>
                        <input type="text" name="shipping_courier" value="{{ old('shipping_courier', $order->shipping_courier) }}"
                               placeholder="Contoh: JNE, J&T, SiCepat"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-black focus:outline-none text-sm">
                        @error('shipping_courier')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nomor Resi</label>
                        <input type="text" name="tracking_number" value="{{ old('tracking_number', $order->tracking_number) }}"
                               placeholder="Masukkan nomor resi"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-black focus:outline-none text-sm">
                        @error('tracking_number')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    @if($order->tracking_number)
                        <p class="text-xs text-gray-500">
                            Dikirim {{ $order->shipped_at?->format('d M Y, H:i') ?? '—' }}
                        </p>
                    @endif
                    <button type="submit"
                            class="w-full bg-black text-white py-2.5 rounded-lg hover:bg-gray-800 transition font-medium text-sm">
                        {{ $order->tracking_number ? 'Perbarui Resi' : 'Simpan Resi' }}
                    </button>
                </form>
            @else
                <p class="text-sm text-gray-500 italic">Resi dapat ditambahkan setelah pelanggan menyelesaikan pembayaran.</p>
            @endif
        </div>
    </div>
</div>
@endsection
