@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">

    {{-- Page header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Shop</h1>
        <p class="text-sm text-gray-500 mt-1">Discover our curated collection</p>
    </div>

    {{-- Mobile: filter toggle button --}}
    <div class="lg:hidden mb-4">
        <button
            onclick="document.getElementById('filter-sidebar').classList.toggle('hidden')"
            class="inline-flex items-center gap-2 bg-black text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition text-sm font-medium"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            Filters &amp; Sort
        </button>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">

        {{-- ── Filter Sidebar ── --}}
        <aside id="filter-sidebar" class="hidden lg:block w-full lg:w-64 flex-shrink-0">

            @php
                $sb = $filters['sort_by'] ?? '';
                $sd = $filters['sort_dir'] ?? '';
                if ($sb === 'name'  && $sd === 'asc')  $currentSort = 'name_asc';
                elseif ($sb === 'name'  && $sd === 'desc') $currentSort = 'name_desc';
                elseif ($sb === 'price' && $sd === 'asc')  $currentSort = 'price_asc';
                elseif ($sb === 'price' && $sd === 'desc') $currentSort = 'price_desc';
                else $currentSort = 'newest';
            @endphp

            <form method="GET" action="{{ route('products.index') }}">

                <div class="bg-white rounded-xl shadow-sm p-5 space-y-5">

                    <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wider border-b border-gray-100 pb-3">
                        Filters
                    </h2>

                    {{-- Search --}}
                    <div>
                        <label for="filter-search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input
                            id="filter-search"
                            type="text"
                            name="search"
                            value="{{ $filters['search'] ?? '' }}"
                            placeholder="Search products…"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-black focus:outline-none text-sm"
                        >
                    </div>

                    {{-- Category --}}
                    <div>
                        <label for="filter-category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select
                            id="filter-category"
                            name="category_id"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-black focus:outline-none text-sm bg-white"
                        >
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option
                                    value="{{ $category->id }}"
                                    @selected(($filters['category_id'] ?? '') == $category->id)
                                >
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Price range --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price Range (Rp)</label>
                        <div class="flex items-center gap-2">
                            <input
                                type="number"
                                name="min_price"
                                value="{{ $filters['min_price'] ?? '' }}"
                                placeholder="Min"
                                min="0"
                                class="w-full border border-gray-300 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-black focus:outline-none text-sm"
                            >
                            <span class="text-gray-400 text-xs flex-shrink-0">–</span>
                            <input
                                type="number"
                                name="max_price"
                                value="{{ $filters['max_price'] ?? '' }}"
                                placeholder="Max"
                                min="0"
                                class="w-full border border-gray-300 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-black focus:outline-none text-sm"
                            >
                        </div>
                    </div>

                    {{-- In Stock --}}
                    <div class="flex items-center gap-3">
                        <input
                            type="checkbox"
                            id="in_stock"
                            name="in_stock"
                            value="1"
                            @checked(!empty($filters['in_stock']))
                            class="w-4 h-4 rounded border-gray-300 accent-black cursor-pointer"
                        >
                        <label for="in_stock" class="text-sm font-medium text-gray-700 cursor-pointer select-none">
                            In Stock Only
                        </label>
                    </div>

                    {{-- Sort --}}
                    <div>
                        <label for="filter-sort" class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                        <select
                            id="filter-sort"
                            name="sort"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-black focus:outline-none text-sm bg-white"
                        >
                            <option value="newest"     @selected($currentSort === 'newest')>Newest</option>
                            <option value="name_asc"   @selected($currentSort === 'name_asc')>Name (A–Z)</option>
                            <option value="name_desc"  @selected($currentSort === 'name_desc')>Name (Z–A)</option>
                            <option value="price_asc"  @selected($currentSort === 'price_asc')>Price (Low–High)</option>
                            <option value="price_desc" @selected($currentSort === 'price_desc')>Price (High–Low)</option>
                        </select>
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-2 pt-1">
                        <button
                            type="submit"
                            class="flex-1 bg-black text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition text-sm font-medium"
                        >
                            Apply
                        </button>
                        <a
                            href="{{ route('products.index') }}"
                            class="flex-1 text-center bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition text-sm font-medium"
                        >
                            Reset
                        </a>
                    </div>

                </div>
            </form>
        </aside>

        {{-- ── Product Grid ── --}}
        <section class="flex-1 min-w-0">

            {{-- Results bar + active chips --}}
            <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                <p class="text-sm text-gray-500">
                    <span class="font-medium text-gray-900">{{ $products->total() }}</span>
                    {{ $products->total() === 1 ? 'product' : 'products' }} found
                </p>

                <div class="flex flex-wrap gap-2">
                    @if(!empty($filters['search']))
                        <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-700 text-xs px-3 py-1 rounded-full">
                            "{{ $filters['search'] }}"
                            <a href="{{ route('products.index', request()->except(['search', 'page'])) }}"
                               class="ml-0.5 text-gray-400 hover:text-gray-700">&times;</a>
                        </span>
                    @endif
                    @if(!empty($filters['category_id']))
                        @php $activeCategory = $categories->firstWhere('id', $filters['category_id']) @endphp
                        @if($activeCategory)
                            <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-700 text-xs px-3 py-1 rounded-full">
                                {{ $activeCategory->name }}
                                <a href="{{ route('products.index', request()->except(['category_id', 'page'])) }}"
                                   class="ml-0.5 text-gray-400 hover:text-gray-700">&times;</a>
                            </span>
                        @endif
                    @endif
                    @if(!empty($filters['in_stock']))
                        <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs px-3 py-1 rounded-full">
                            In Stock
                            <a href="{{ route('products.index', request()->except(['in_stock', 'page'])) }}"
                               class="ml-0.5 text-green-500 hover:text-green-700">&times;</a>
                        </span>
                    @endif
                </div>
            </div>

            @if($products->isEmpty())
                {{-- Empty state --}}
                <div class="bg-white rounded-xl shadow-sm flex flex-col items-center justify-center py-20 px-6 text-center">
                    <svg class="w-14 h-14 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <p class="text-base font-semibold text-gray-700 mb-1">No products found</p>
                    <p class="text-sm text-gray-400 mb-5">Try adjusting your filters or browsing all products.</p>
                    <a href="{{ route('products.index') }}"
                       class="bg-black text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
                        Clear All Filters
                    </a>
                </div>
            @else
                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 lg:gap-5">
                    @foreach($products as $product)
                        <a href="{{ route('products.show', $product) }}"
                           class="group bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden block">

                            {{-- Image --}}
                            <div class="aspect-square overflow-hidden bg-gray-50">
                                <img
                                    src="{{ $product->mainImage
                                        ? asset('storage/' . $product->mainImage->path)
                                        : 'https://picsum.photos/seed/' . $product->slug . '/400/400' }}"
                                    alt="{{ $product->name }}"
                                    loading="lazy"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                >
                            </div>

                            {{-- Info --}}
                            <div class="p-3">
                                @if($product->category)
                                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5 truncate">
                                        {{ $product->category->name }}
                                    </p>
                                @endif
                                <h3 class="text-sm font-medium text-gray-900 line-clamp-2 leading-snug">
                                    {{ $product->name }}
                                </h3>
                                <p class="mt-1.5 text-sm font-bold text-gray-900">
                                    Rp&nbsp;{{ number_format($product->price, 0, ',', '.') }}
                                </p>
                            </div>

                        </a>
                    @endforeach
                </div>

                @if($products->hasPages())
                    <div class="mt-10 flex justify-center">
                        {{ $products->appends(request()->except('page'))->links() }}
                    </div>
                @endif
            @endif

        </section>
    </div>
</div>
@endsection
