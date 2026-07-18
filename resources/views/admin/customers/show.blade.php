@extends('admin.layouts.app')

@section('title', 'Pelanggan: ' . $customer->name)
@section('page_title', $customer->name)
@section('page_subtitle', 'Detail dan riwayat pelanggan')

@section('page_actions')
    <a href="{{ route('admin.customers.index') }}"
       class="inline-flex items-center gap-2 border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition font-medium text-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>
@endsection

@php
    $statusMap = [
        'pending'    => ['label' => 'Menunggu',  'class' => 'bg-amber-50 text-amber-700'],
        'processing' => ['label' => 'Diproses',  'class' => 'bg-blue-50 text-blue-700'],
        'completed'  => ['label' => 'Selesai',   'class' => 'bg-green-50 text-green-700'],
        'failed'     => ['label' => 'Gagal',     'class' => 'bg-red-50 text-red-700'],
        'cancelled'  => ['label' => 'Dibatalkan','class' => 'bg-gray-100 text-gray-600'],
    ];
    $totalSpent = $customer->orders->whereIn('status', ['completed'])->sum('total');
    $cartCount  = $customer->cartItems->sum('quantity');
@endphp

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    {{-- ── Left ── --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Profile card --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-gray-900 flex items-center justify-center text-white font-bold text-2xl">
                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <h2 class="text-xl font-bold text-gray-900 truncate">{{ $customer->name }}</h2>
                    <p class="text-gray-500 truncate">{{ $customer->email }}</p>
                    <p class="text-gray-500 truncate">{{ $customer->phone ?? 'No. HP belum diisi' }}</p>
                    <p class="text-xs text-gray-400 mt-1">Terdaftar {{ $customer->created_at?->format('d M Y') ?? '—' }}</p>
                </div>
            </div>
        </div>

        {{-- Orders --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Riwayat Pesanan ({{ $customer->orders->count() }})</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr class="text-xs font-semibold text-gray-500 uppercase">
                            <th class="px-6 py-3 text-left">No.</th>
                            <th class="px-6 py-3 text-left">Tanggal</th>
                            <th class="px-6 py-3 text-center">Item</th>
                            <th class="px-6 py-3 text-right">Total</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($customer->orders->sortByDesc('created_at') as $order)
                            @php $s = $statusMap[$order->status] ?? ['label' => ucfirst($order->status), 'class' => 'bg-gray-100 text-gray-600']; @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">#{{ $order->id }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $order->created_at?->format('d M Y') ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-center text-gray-900">{{ $order->items->sum('quantity') }}</td>
                                <td class="px-6 py-4 text-sm text-right font-medium text-gray-900">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $s['class'] }}">{{ $s['label'] }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.payments.show', $order) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">Belum ada pesanan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Current cart --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">Keranjang Saat Ini</h2>
                @if($customer->cartItems->isNotEmpty())
                    <a href="{{ route('admin.carts.show', $customer) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Kelola →</a>
                @endif
            </div>
            @if($customer->cartItems->isEmpty())
                <div class="p-8 text-center text-gray-500 text-sm">Keranjang kosong.</div>
            @else
                <div class="divide-y divide-gray-50">
                    @foreach($customer->cartItems as $item)
                        <div class="px-6 py-4 flex items-center gap-4">
                            <div class="flex-shrink-0">
                                @if($item->product && $item->product->mainImage)
                                    <img src="{{ asset('storage/' . $item->product->mainImage->path) }}" alt="{{ $item->product->name }}" class="w-14 h-14 rounded-lg object-cover bg-gray-100">
                                @else
                                    <div class="w-14 h-14 rounded-lg bg-gray-100"></div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $item->product?->name ?? 'Produk dihapus' }}</p>
                                <p class="text-xs text-gray-500">Qty: {{ $item->quantity }}</p>
                            </div>
                            <p class="text-sm font-medium text-gray-900">
                                Rp {{ number_format(($item->product?->price ?? 0) * $item->quantity, 0, ',', '.') }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ── Right: stats + actions ── --}}
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 space-y-5">
            <h2 class="font-semibold text-gray-900">Ringkasan</h2>
            <div>
                <p class="text-xs text-gray-500 font-medium">Total Pesanan</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $customer->orders->count() }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium">Total Belanja (selesai)</p>
                <p class="text-2xl font-bold text-green-600 mt-1">Rp {{ number_format($totalSpent, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium">Item di Keranjang</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $cartCount }}</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 space-y-3">
            <a href="{{ route('admin.carts.show', $customer) }}"
               class="w-full border border-gray-300 text-gray-700 py-2.5 rounded-lg hover:bg-gray-50 transition font-medium text-center block text-sm">
                Lihat Keranjang
            </a>
            <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}"
                  onsubmit="return confirm('Hapus pelanggan ini? Tindakan ini tidak dapat dibatalkan.');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="w-full bg-red-50 text-red-600 py-2.5 rounded-lg hover:bg-red-100 transition font-medium text-sm">
                    Hapus Pelanggan
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
