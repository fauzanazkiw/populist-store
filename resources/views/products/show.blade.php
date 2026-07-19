@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs text-gray-400 mb-8" aria-label="Breadcrumb">
        <a href="{{ url('/') }}" class="hover:text-gray-700 transition">Home</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('products.index') }}" class="hover:text-gray-700 transition">Products</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-700 font-medium truncate max-w-xs">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16">

        {{-- ── Left: Images ── --}}
        <div class="space-y-4">

            {{-- Main image --}}
            <div class="bg-gray-50 rounded-2xl overflow-hidden aspect-square">
                <img
                    id="main-product-image"
                    src="{{ $product->mainImage
                        ? asset('storage/' . $product->mainImage->path)
                        : 'https://picsum.photos/seed/' . $product->slug . '/800/800' }}"
                    alt="{{ $product->name }}"
                    class="w-full h-full object-cover"
                >
            </div>

            {{-- Thumbnail strip --}}
            @if($product->images->count() > 1)
                <div class="flex gap-3 overflow-x-auto pb-1">
                    @foreach($product->images as $image)
                        <button
                            type="button"
                            onclick="swapImage('{{ asset('storage/' . $image->path) }}', '{{ addslashes($image->alt ?? $product->name) }}', this)"
                            class="flex-shrink-0 w-20 h-20 rounded-xl overflow-hidden border-2 transition
                                   {{ $image->is_main ? 'border-black' : 'border-transparent hover:border-gray-300' }}"
                            aria-label="View image {{ $loop->iteration }}"
                        >
                            <img
                                src="{{ asset('storage/' . $image->path) }}"
                                alt="{{ $image->alt ?? $product->name }}"
                                class="w-full h-full object-cover"
                            >
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ── Right: Details ── --}}
        <div class="flex flex-col space-y-5">

            {{-- Category badge --}}
            @if($product->category)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 self-start">
                    {{ $product->category->name }}
                </span>
            @endif

            {{-- Name --}}
            <h1 class="text-3xl font-bold text-gray-900 leading-tight">
                {{ $product->name }}
            </h1>

            {{-- Price --}}
            <p class="text-2xl font-bold text-gray-900">
                Rp&nbsp;{{ number_format($product->price, 0, ',', '.') }}
            </p>

            {{-- Description --}}
            @if($product->description)
                <p class="text-sm text-gray-600 leading-relaxed">
                    {{ $product->description }}
                </p>
            @endif

            {{-- Stock badge --}}
            <div>
                @if($product->stock > 0)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span>
                        In Stock ({{ $product->stock }} available)
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full mr-1.5"></span>
                        Out of Stock
                    </span>
                @endif
            </div>

            {{-- Add to cart --}}
            @auth
                {{-- Authenticated user --}}
                <form method="POST" action="{{ route('cart.add', $product) }}" class="space-y-4 pt-1">
                    @csrf

                    {{-- Size --}}
                    @if(!empty($product->sizes))
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Size <span class="text-red-500">*</span>
                            </label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($product->sizes as $size)
                                    <label class="cursor-pointer">
                                        <input
                                            type="radio"
                                            name="size"
                                            value="{{ $size }}"
                                            @checked(old('size') === $size)
                                            {{ $product->stock <= 0 ? 'disabled' : '' }}
                                            required
                                            class="peer sr-only"
                                        >
                                        <span class="inline-block border border-gray-300 rounded-xl px-4 py-2.5 text-sm font-medium
                                                     peer-checked:bg-black peer-checked:text-white peer-checked:border-black
                                                     hover:border-gray-400 transition">{{ $size }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('size')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    {{-- Quantity --}}
                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                        <div class="flex items-center gap-3">
                            <input
                                id="quantity"
                                type="number"
                                name="quantity"
                                value="1"
                                min="1"
                                max="{{ $product->stock }}"
                                {{ $product->stock <= 0 ? 'disabled' : '' }}
                                class="w-24 border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-black focus:outline-none text-sm text-center
                                       {{ $product->stock <= 0 ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                            >
                            @if($product->stock > 0 && $product->stock <= 10)
                                <span class="text-xs text-amber-600 font-medium">
                                    Only {{ $product->stock }} left!
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Submit button --}}
                    <button
                        type="submit"
                        {{ $product->stock <= 0 ? 'disabled' : '' }}
                        class="w-full sm:w-auto bg-black text-white px-8 py-3 rounded-xl font-semibold text-sm
                               hover:bg-gray-800 transition
                               {{ $product->stock <= 0 ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}"
                    >
                        @if($product->stock > 0)
                            Add to Cart
                        @else
                            Out of Stock
                        @endif
                    </button>
                </form>
            @else
                {{-- Unauthenticated user --}}
                <div class="space-y-4 pt-1">
                    <p class="text-sm text-gray-600">
                        Sign in to your account to add items to cart
                    </p>
                    <div class="flex gap-3">
                        <a href="{{ route('login') }}" class="flex-1 bg-black text-white px-6 py-3 rounded-xl font-semibold text-sm hover:bg-gray-800 transition text-center">
                            Sign In
                        </a>
                        <a href="{{ route('register') }}" class="flex-1 border-2 border-black text-black px-6 py-3 rounded-xl font-semibold text-sm hover:bg-gray-50 transition text-center">
                            Create Account
                        </a>
                    </div>
                </div>
            @endauth

            <hr class="border-gray-100">

            {{-- Back link --}}
            <a href="{{ route('products.index') }}"
               class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-900 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Shop
            </a>

        </div>
    </div>
</div>

<script>
function swapImage(src, alt, btn) {
    var mainImg = document.getElementById('main-product-image');
    mainImg.src = src;
    mainImg.alt = alt;

    document.querySelectorAll('[onclick^="swapImage"]').forEach(function (b) {
        b.classList.remove('border-black');
        b.classList.add('border-transparent');
    });
    btn.classList.remove('border-transparent');
    btn.classList.add('border-black');
}
</script>
@endsection
