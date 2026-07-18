@extends('admin.layouts.app')

@section('page_title', 'Shopping Carts')
@section('page_subtitle', 'Monitor customer shopping carts')

@section('content')

{{-- Summary cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 font-medium">Total Active Carts</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalActiveCarts }}</p>
            </div>
            <svg class="w-10 h-10 text-blue-500 opacity-20" fill="currentColor" viewBox="0 0 24 24">
                <path d="M7 4V2m10 2v2M7 20v2m10-2v2M5 9h14M5 14h14M3 4h18v16a2 2 0 01-2 2H5a2 2 0 01-2-2V4z"/>
            </svg>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 font-medium">Total Cart Items</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalItems }}</p>
            </div>
            <svg class="w-10 h-10 text-green-500 opacity-20" fill="currentColor" viewBox="0 0 24 24">
                <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 font-medium">Total Cart Value</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">Rp {{ number_format($totalValue, 0, ',', '.') }}</p>
            </div>
            <svg class="w-10 h-10 text-purple-500 opacity-20" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-lg shadow-sm p-4 mb-6">
    <form method="GET" action="{{ route('admin.carts.index') }}" class="flex gap-3 flex-wrap items-center">
        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Search by customer name or email..."
            class="flex-1 min-w-64 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm"
        >
        <select name="sort" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm bg-white">
            <option value="latest" @selected(request('sort') === 'latest')>Latest</option>
            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest</option>
            <option value="value_high" @selected(request('sort') === 'value_high')>Highest Value</option>
            <option value="value_low" @selected(request('sort') === 'value_low')>Lowest Value</option>
        </select>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium">
            Filter
        </button>
        @if(request('search') || request('sort') !== 'latest')
            <a href="{{ route('admin.carts.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition text-sm">
                Clear
            </a>
        @endif
    </form>
</div>

{{-- Carts table --}}
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Items</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Cart Value</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Updated</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($carts as $cart)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $cart['user']->name }}</p>
                                <p class="text-xs text-gray-500">{{ $cart['user']->email }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                {{ $cart['items_count'] }} item{{ $cart['items_count'] !== 1 ? 's' : '' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            Rp {{ number_format($cart['total_value'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $cart['updated_at']->format('d M Y H:i') }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.carts.show', $cart['user']) }}"
                               class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <svg class="mx-auto w-10 h-10 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            <p class="text-sm text-gray-400 font-medium">No active carts found.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Pagination --}}
@if($carts->hasPages())
    <div class="mt-6 flex justify-center">
        {{ $carts->appends(request()->except('page'))->links() }}
    </div>
@endif

@endsection
