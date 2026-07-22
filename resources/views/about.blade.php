@extends('layouts.app')

@section('content')

{{-- ── Hero ── --}}
<section class="relative bg-gray-950 text-white overflow-hidden">
    <div class="absolute inset-0 opacity-30 bg-cover bg-center"
         style="background-image:url('https://images.unsplash.com/photo-1445205170230-053b83016050?ixlib=rb-1.2.1&auto=format&fit=crop&w=1600&q=80');"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/70 to-gray-950/40"></div>
    <div class="relative max-w-5xl mx-auto px-6 py-24 md:py-32 text-center animate-fade-rise">
        <span class="uppercase tracking-[0.4em] text-xs text-white/60">Est. 2020 — Kebumen</span>
        <h1 class="font-display text-5xl md:text-7xl font-bold tracking-tight mt-5 mb-6 leading-[0.95]">
            We make the<br>essentials extraordinary.
        </h1>
        <p class="text-lg text-white/70 max-w-2xl mx-auto">
            Populist is a curated clothing label built on a simple belief — that everyday clothing
            deserves the same care, quality, and design as anything on the runway.
        </p>
    </div>
</section>

{{-- ── Values ── --}}
<section class="bg-gray-50 border-y border-gray-100">
    <div class="max-w-7xl mx-auto px-6 py-20">
        <div class="text-center max-w-2xl mx-auto mb-14">
            <span class="uppercase tracking-[0.3em] text-xs text-gray-400">What we stand for</span>
            <h2 class="font-display text-3xl md:text-4xl font-bold text-gray-900 tracking-tight mt-3">
                Principles we don't compromise on.
            </h2>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            @foreach ([
                ['M5 13l4 4L19 7', 'Quality first', 'Premium, responsibly sourced fabrics and finishes chosen to last well beyond a single season.'],
                ['M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064', 'Made responsibly', 'We partner with mills and workshops that treat people and the planet with respect.'],
                ['M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z', 'Made to be loved', 'Timeless design over trends — pieces you reach for again and again.'],
            ] as [$icon, $title, $desc])
                <div class="bg-white rounded-2xl border border-gray-100 p-8 hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-xl bg-gray-900 flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="{{ $icon }}"/>
                        </svg>
                    </div>
                    <h3 class="font-display text-xl font-semibold text-gray-900 mb-2">{{ $title }}</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── FAQ ── --}}
<section id="faq" class="bg-gray-50 border-y border-gray-100 scroll-mt-24">
    <div class="max-w-3xl mx-auto px-6 py-20">
        <div class="text-center mb-12">
            <span class="uppercase tracking-[0.3em] text-xs text-gray-400">Good to know</span>
            <h2 class="font-display text-3xl md:text-4xl font-bold text-gray-900 tracking-tight mt-3">
                Frequently asked questions.
            </h2>
        </div>
        <div class="space-y-3">
            @foreach ([
                ['How do your sizes run?', 'Our pieces are designed with a modern, true-to-size fit. Each product page includes a detailed size guide — when in doubt, size up for a relaxed look.'],
                ['What payment methods do you accept?', 'We accept all major credit and debit cards, plus popular local e-wallets and bank transfers, all processed through a secure encrypted checkout.'],
                ['Can I change or cancel my order?', 'Reach out within 2 hours of ordering and we\'ll do our best to update or cancel it before it ships.'],
                ['How do I track my order?', 'Once your order ships you\'ll receive an email with a tracking link. You can also view order status any time from your account.'],
            ] as $i => [$q, $a])
                <details class="group bg-white rounded-2xl border border-gray-100 p-5 open:shadow-sm" {{ $i === 0 ? 'open' : '' }}>
                    <summary class="flex items-center justify-between cursor-pointer list-none font-medium text-gray-900">
                        {{ $q }}
                        <svg class="w-5 h-5 text-gray-400 transition-transform group-open:rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </summary>
                    <p class="text-sm text-gray-600 leading-relaxed mt-3">{{ $a }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>

{{-- ── Contact / CTA ── --}}
<section id="contact" class="bg-gray-950 text-white scroll-mt-24">
    <div class="max-w-5xl mx-auto px-6 py-20 text-center">
        <span class="uppercase tracking-[0.3em] text-xs text-white/50">Say hello</span>
        <h2 class="font-display text-3xl md:text-5xl font-bold tracking-tight mt-4 mb-5">
            Questions? We're here to help.
        </h2>
        <p class="text-white/70 max-w-xl mx-auto mb-10">
            Our team usually replies within one business day. Drop us a line or start browsing the collection.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="mailto:hello@populist.example"
               class="inline-flex items-center gap-2 bg-white text-gray-900 px-8 py-3.5 rounded-full font-medium hover:bg-gray-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                hello@populist.example
            </a>
            <a href="{{ route('products.index') }}"
               class="inline-flex items-center gap-2 border border-white/25 text-white px-8 py-3.5 rounded-full font-medium hover:bg-white/10 transition">
                Shop the collection
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </div>
</section>

@endsection
