@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Pembayaran</h1>
            <p class="text-gray-500 mt-1">Kelola dan pantau pembayaran pesanan</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="text-gray-500 text-sm font-medium mb-2">Total Pendapatan</div>
            <div class="text-2xl font-bold text-gray-900">
                Rp {{ number_format($stats['totalRevenue'] ?? 0, 0, ',', '.') }}
            </div>
            <p class="text-xs text-gray-400 mt-2">Semua pesanan</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="text-gray-500 text-sm font-medium mb-2">Pesanan Berhasil</div>
            <div class="text-2xl font-bold text-green-600">
                {{ $stats['successfulOrders'] ?? 0 }}
            </div>
            <p class="text-xs text-gray-400 mt-2">Status completed</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="text-gray-500 text-sm font-medium mb-2">Menunggu Pembayaran</div>
            <div class="text-2xl font-bold text-amber-600">
                {{ $stats['pendingOrders'] ?? 0 }}
            </div>
            <p class="text-xs text-gray-400 mt-2">Status pending</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="text-gray-500 text-sm font-medium mb-2">Total Pesanan</div>
            <div class="text-2xl font-bold text-blue-600">
                {{ $stats['totalOrders'] ?? 0 }}
            </div>
            <p class="text-xs text-gray-400 mt-2">Semua waktu</p>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
        <form method="GET" action="{{ route('admin.payments.index') }}" class="flex gap-3 flex-wrap">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari nomor pesanan atau pelanggan..."
                class="flex-1 min-w-xs border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-black focus:outline-none"
            >
            <select name="status" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-black focus:outline-none">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Gagal</option>
            </select>
            <button type="submit" class="bg-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition font-medium">
                Cari
            </button>
            @if(request('search') || request('status'))
                <a href="{{ route('admin.payments.index') }}" class="bg-gray-100 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-200 transition font-medium">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr class="text-xs font-semibold text-gray-500 uppercase">
                        <th class="px-6 py-4 text-left">No. Pesanan</th>
                        <th class="px-6 py-4 text-left">Pelanggan</th>
                        <th class="px-6 py-4 text-right">Jumlah</th>
                        <th class="px-6 py-4 text-left">Status</th>
                        <th class="px-6 py-4 text-left">Tanggal</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                #{{ $payment->id }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="font-medium text-gray-900">{{ $payment->user?->name ?? 'Pengguna dihapus' }}</div>
                                <div class="text-gray-600">{{ $payment->user?->email ?? '—' }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-right font-medium text-gray-900">
                                Rp {{ number_format($payment->total, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @switch($payment->status)
                                    @case('pending')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700">
                                            Menunggu
                                        </span>
                                        @break
                                    @case('completed')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700">
                                            Selesai
                                        </span>
                                        @break
                                    @case('failed')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700">
                                            Gagal
                                        </span>
                                        @break
                                @endswitch
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $payment->created_at?->format('d M Y H:i') ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.payments.show', $payment->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                Tidak ada data pembayaran
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($payments->hasPages())
        <div class="flex justify-center">
            {{ $payments->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection
