@extends('admin.layouts.app')

@section('page_title', 'Products')
@section('page_subtitle', 'Manage your product catalogue')

@section('page_actions')
<a href="{{ route('admin.products.create') }}"
   class="bg-black text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition text-sm font-medium inline-flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Add Product
</a>
@endsection

@section('content')


    {{-- Search --}}
    <form method="GET" action="{{ route('admin.products.index') }}" class="mb-6">
        <div class="flex gap-3 max-w-sm">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search products…"
                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-black focus:outline-none text-sm"
            >
            <button type="submit"
                    class="bg-black text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition text-sm font-medium flex-shrink-0">
                Search
            </button>
            @if(request('search'))
                <a href="{{ route('admin.products.index') }}"
                   class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-200 transition text-sm flex-shrink-0 flex items-center">
                    Clear
                </a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead>
                    <tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4 text-left w-12">#</th>
                        <th class="px-6 py-4 text-left">Name</th>
                        <th class="px-6 py-4 text-left">Category</th>
                        <th class="px-6 py-4 text-left">Price</th>
                        <th class="px-6 py-4 text-center">Stock</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50 transition">

                            <td class="px-6 py-4 text-sm text-gray-400">{{ $product->id }}</td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($product->mainImage)
                                        <img
                                            src="{{ asset('storage/' . $product->mainImage->path) }}"
                                            alt="{{ $product->name }}"
                                            class="w-10 h-10 rounded-lg object-cover flex-shrink-0 bg-gray-100"
                                        >
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <span class="text-sm font-medium text-gray-900">{{ $product->name }}</span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $product->category?->name ?? '—' }}
                            </td>

                            <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                                Rp&nbsp;{{ number_format($product->price, 0, ',', '.') }}
                            </td>

                            <td class="px-6 py-4 text-sm text-center">
                                <span class="{{ $product->stock > 0 ? 'text-gray-900 font-medium' : 'text-red-500 font-medium' }}">
                                    {{ $product->stock }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-center">
                                @if($product->active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 flex-wrap">

                                    {{-- Edit --}}
                                    <a href="{{ route('admin.products.edit', $product) }}"
                                       class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-200 transition text-sm">
                                        Edit
                                    </a>

                                    {{-- Toggle Active --}}
                                    <form action="{{ route('admin.products.toggle-active', $product) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="px-3 py-1.5 rounded-lg transition text-sm
                                                       {{ $product->active
                                                           ? 'bg-amber-50 text-amber-700 hover:bg-amber-100'
                                                           : 'bg-green-50 text-green-700 hover:bg-green-100' }}">
                                            {{ $product->active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>

                                    {{-- Delete --}}
                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST"
                                          onsubmit="return confirm('Delete \'{{ addslashes($product->name) }}\'? This action cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="bg-red-600 text-white px-3 py-1.5 rounded-lg hover:bg-red-700 transition text-sm">
                                            Delete
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <svg class="mx-auto w-10 h-10 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0H4"/>
                                </svg>
                                <p class="text-sm text-gray-400 font-medium">No products found.</p>
                                <a href="{{ route('admin.products.create') }}"
                                   class="inline-block mt-3 text-sm text-black underline underline-offset-2 hover:no-underline">
                                    Create your first product
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($products->hasPages())
        <div class="mt-6 flex justify-center">
            {{ $products->appends(request()->except('page'))->links() }}
        </div>
    @endif

@endsection
