@extends('layouts.app')

@section('content')

{{-- Carousel & hover CSS --}}
<style>
    .hover\:grow { transition: all 0.4s cubic-bezier(0.22, 1, 0.36, 1); transform: scale(1); }
    .hover\:grow:hover { transform: scale(1.04); }

    .carousel-open:checked + .carousel-item {
        position: static;
        opacity: 100;
    }
    .carousel-item {
        -webkit-transition: opacity 0.8s ease-out;
        transition: opacity 0.8s ease-out;
    }

    #carousel-1:checked ~ .control-1,
    #carousel-2:checked ~ .control-2,
    #carousel-3:checked ~ .control-3 { display: block; }

    .carousel-indicators {
        list-style: none;
        margin: 0; padding: 0;
        position: absolute;
        bottom: 5%; left: 0; right: 0;
        text-align: center;
        z-index: 10;
    }

    #carousel-1:checked ~ .control-1 ~ .carousel-indicators li:nth-child(1) .carousel-bullet,
    #carousel-2:checked ~ .control-2 ~ .carousel-indicators li:nth-child(2) .carousel-bullet,
    #carousel-3:checked ~ .control-3 ~ .carousel-indicators li:nth-child(3) .carousel-bullet {
        background-color: #fff;
        width: 2.25rem;
    }

    .hero-overlay {
        background: linear-gradient(90deg, rgba(0,0,0,0.65) 0%, rgba(0,0,0,0.25) 45%, rgba(0,0,0,0) 100%);
    }
</style>

{{-- ── Hero Carousel ── --}}
<div class="carousel relative w-full">
    <div class="carousel-inner relative overflow-hidden w-full">
        {{-- Slide 1 --}}
        <input class="carousel-open" type="radio" id="carousel-1" name="carousel" aria-hidden="true" hidden checked>
        <div class="carousel-item absolute opacity-0" style="height:100vh;">
            <div class="relative h-full w-full bg-cover bg-center"
                 style="background-image:url('image/Untitled.png');">
                <div class="absolute inset-0 bg-black opacity-80"></div>
                <div class="relative h-full container mx-auto flex items-center">
                    <div class="flex flex-col w-full lg:w-1/2 md:ml-12 items-start px-6 animate-fade-rise">
                        <h1 class="font-display text-5xl md:text-7xl font-bold text-white leading-[0.95] mb-6">
                            Populist<br>Crew
                        </h1>
                        <p class="text-white/80 text-lg mb-8 max-w-md">Statement outerwear built for the cold months. Limited run, dropping 26.08.25.</p>
                        <a class="inline-flex items-center gap-2 bg-white text-gray-900 px-8 py-3.5 rounded-full font-medium hover:bg-gray-100 transition"
                           href="{{ route('products.index') }}">
                            Shop the drop
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Feature strip ── --}}
<section class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-6 py-8 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
        @foreach ([
            ['M5 13l4 4L19 7', 'Free shipping', 'On orders over Rp 500K'],
            ['M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'Easy 30-day returns', 'No questions asked'],
            ['M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'Secure checkout', 'Encrypted payments'],
            ['M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4', 'Quality guaranteed', 'Crafted to last'],
        ] as [$icon, $title, $sub])
            <div class="flex flex-col items-center">
                <svg class="w-6 h-6 text-gray-900 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="{{ $icon }}"/>
                </svg>
                <p class="text-sm font-semibold text-gray-900">{{ $title }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $sub }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- ── NEW ARRIVALS ── --}}
<section class="bg-white py-16">
    <div class="max-w-7xl mx-auto px-6">

        {{-- Section header --}}
        <div class="flex items-end justify-between mb-10">
            <div>
                <span class="uppercase tracking-[0.3em] text-xs text-gray-400">Just landed</span>
                <h2 class="font-display text-3xl md:text-4xl font-bold text-gray-900 tracking-tight mt-2">New Arrivals</h2>
            </div>
            <a href="{{ route('products.index') }}"
               class="hidden sm:inline-flex items-center gap-2 text-sm font-medium text-gray-900 link-underline">
                View all
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>

        {{-- Product grid --}}
        @forelse($featuredProducts as $product)
            @if($loop->first)
                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 lg:gap-6">
            @endif
                <a href="{{ route('products.show', $product) }}"
                   class="group bg-white rounded-2xl border border-gray-100 hover:border-gray-200 hover:shadow-lg transition overflow-hidden block">
                    <div class="relative aspect-square overflow-hidden bg-gray-50">
                        @if($loop->index < 3)
                            <span class="absolute top-3 left-3 z-10 bg-gray-900 text-white text-[10px] font-semibold tracking-wider uppercase px-2.5 py-1 rounded-full">New</span>
                        @endif
                        <img
                            class="hover:grow w-full h-full object-cover"
                            src="{{ $product->mainImage
                                ? asset('storage/' . $product->mainImage->path)
                                : 'https://picsum.photos/seed/' . $product->slug . '/400/400' }}"
                            alt="{{ $product->name }}"
                            loading="lazy"
                        >
                    </div>
                    <div class="p-4">
                        @if($product->category)
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5 truncate">{{ $product->category->name }}</p>
                        @endif
                        <h3 class="text-sm font-medium text-gray-900 line-clamp-1">{{ $product->name }}</h3>
                        <p class="pt-1.5 text-gray-900 font-bold">
                            Rp&nbsp;{{ number_format($product->price, 0, ',', '.') }}
                        </p>
                    </div>
                </a>
            @if($loop->last)
                </div>
            @endif
        @empty
            <div class="w-full text-center py-16 text-gray-400 border border-dashed border-gray-200 rounded-2xl">
                <p class="text-lg">No products yet. Check back soon!</p>
            </div>
        @endforelse

        {{-- View all button --}}
        @if($featuredProducts->isNotEmpty())
            <div class="text-center mt-12">
                <a href="{{ route('products.index') }}"
                   class="inline-flex items-center gap-2 bg-gray-900 text-white px-8 py-3.5 rounded-full hover:bg-gray-800 transition font-medium text-sm">
                    View All Products
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
        @endif

    </div>
</section>

{{-- ── About teaser ── --}}
<section class="bg-gray-50 border-t border-gray-100">
    <div class="max-w-7xl mx-auto px-6 py-20 grid md:grid-cols-2 gap-12 items-center">
        <div class="rounded-3xl overflow-hidden aspect-[4/3] shadow-sm">
            <img src="{{ asset('image/populist_home.jpeg') }}"
                 alt="Our studio" class="w-full h-full object-cover">
        </div>
        <div>
            <span class="uppercase tracking-[0.3em] text-xs text-gray-400">Our story</span>
            <h2 class="font-display text-3xl md:text-4xl font-bold text-gray-900 tracking-tight mt-3 mb-5">
                Style that doesn't shout.
            </h2>
            <p class="text-gray-600 leading-relaxed mb-4">
                Populist is a curated clothing store inspired by Scandinavian minimalism — clean lines,
                premium fabrics, and timeless pieces that carry you from season to season.
            </p>
            <p class="text-gray-600 leading-relaxed mb-8">
                Every piece is chosen for its quality, versatility, and understated elegance —
                made to be worn, loved, and kept for years.
            </p>
            <a href="{{ route('about') }}"
               class="inline-flex items-center gap-2 text-sm font-semibold text-gray-900 link-underline">
                More about us
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </div>
</section>

@endsection
