@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Pelanggan</h1>
            <p class="text-gray-500 mt-1">Kelola daftar pelanggan Anda</p>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4">
        <form method="GET" action="{{ route('admin.customers.index') }}" class="flex gap-3">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari pelanggan..."
                class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-black focus:outline-none"
            >
            <button type="submit" class="bg-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition font-medium">
                Cari
            </button>
            @if(request('search'))
                <a href="{{ route('admin.customers.index') }}" class="bg-gray-100 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-200 transition font-medium">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Customers Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr class="text-xs font-semibold text-gray-500 uppercase">
                        <th class="px-6 py-4 text-left">Nama</th>
                        <th class="px-6 py-4 text-left">Email</th>
                        <th class="px-6 py-4 text-left">No. HP</th>
                        <th class="px-6 py-4 text-center">Total Pesanan</th>
                        <th class="px-6 py-4 text-right">Total Belanja</th>
                        <th class="px-6 py-4 text-left">Terdaftar</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $customer->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $customer->email }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $customer->phone ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-center text-gray-900 font-medium">
                                {{ $customer->orders()->count() }}
                            </td>
                            <td class="px-6 py-4 text-sm text-right text-gray-900 font-medium">
                                Rp {{ number_format($customer->orders()->sum('total'), 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $customer->created_at?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.customers.show', $customer) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                Tidak ada pelanggan ditemukan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($customers->hasPages())
        <div class="flex justify-center">
            {{ $customers->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection
