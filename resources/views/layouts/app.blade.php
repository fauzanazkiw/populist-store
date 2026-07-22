<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Clothing Shop</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased">

<!-- Navbar -->
<nav id="header" class="w-full z-30 top-0 py-1 fixed transition-all duration-300 {{ request()->routeIs('home') ? 'navbar-transparent' : 'navbar-solid' }}" data-is-home="{{ request()->routeIs('home') ? 'true' : 'false' }}">
    <div class="max-w-7xl mx-auto flex items-center justify-between mt-0 px-6 py-3">

        {{-- Desktop nav links (LEFT) --}}
        <div class="hidden md:flex md:items-center order-1" id="menu">
            <ul class="md:flex items-center text-lg pt-4 md:pt-0 gap-1">
                <li>
                    <a href="/"
                       class="inline-block py-2 px-4 rounded-lg text-sm font-medium transition nav-link hover:text-gray-200">
                           HOME
                    </a>
                </li>
                <li>
                    <a href="{{ route('products.index') }}"
                       class="inline-block py-2 px-4 rounded-lg text-sm font-medium transition nav-link hover:text-gray-200 {{ request()->routeIs('products.*') ? 'font-semibold' : '' }}">
                        SHOP
                    </a>
                </li>
                <li>
                    <a href="{{ route('about') }}"
                       class="inline-block py-2 px-4 rounded-lg text-sm font-medium transition nav-link hover:text-gray-200 {{ request()->routeIs('about') ? 'font-semibold' : '' }}">
                        ABOUT
                    </a>
                </li>
                @auth
                    <li>
                        <a href="{{ route('orders.index') }}"
                           class="inline-block py-2 px-4 rounded-lg text-sm font-medium transition nav-link hover:text-gray-200 {{ request()->routeIs('orders.*') ? 'font-semibold' : '' }}">
                               MY ORDERS
                        </a>
                    </li>
                    @if(auth()->user()->is_admin)
                        <li>
                            <a href="{{ route('admin.products.index') }}"
                               class="inline-block py-2 px-4 rounded-lg text-sm font-medium transition nav-link hover:text-gray-200 {{ request()->routeIs('admin.*') ? 'font-semibold' : '' }}">
                                Admin
                            </a>
                        </li>
                    @endif
                @endauth
            </ul>
        </div>

        {{-- Logo (CENTER) --}}
        <div class="order-2 md:order-2 absolute left-1/2 transform -translate-x-1/2">
            <a class="flex items-center no-underline hover:no-underline nav-logo" href="{{ url('/') }}">
                <img src="{{ asset('image/populist.png') }}" alt="Populist" class="h-26 md:h-28 w-auto">
            </a>
        </div>

        {{-- Mobile hamburger --}}
        <button
            type="button"
            onclick="document.getElementById('mobile-menu').classList.toggle('hidden')"
            class="cursor-pointer md:hidden block focus:outline-none order-1"
            aria-label="Toggle menu"
        >
            <svg class="fill-current nav-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20">
                <path d="M0 3h20v2H0V3zm0 6h20v2H0V9zm0 6h20v2H0v-2z"/>
            </svg>
        </button>

        {{-- Right icons --}}
        <div class="order-3 flex items-center gap-3" id="nav-content">

            @auth
                {{-- Logged-in: greeting + logout --}}
                <span class="hidden md:inline text-sm nav-text">
                    Hi, <span class="font-medium">{{ auth()->user()->name }}</span>
                </span>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1 text-sm transition focus:outline-none nav-text"
                            title="Logout">
                        <svg class="fill-current nav-icon" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24">
                            <path d="M16 13v-2H7V8l-5 4 5 4v-3z"/>
                            <path d="M20 3h-9c-1.103 0-2 .897-2 2v4h2V5h9v14h-9v-4H9v4c0 1.103.897 2 2 2h9c1.103 0 2-.897 2-2V5c0-1.103-.897-2-2-2z"/>
                        </svg>
                    </button>
                </form>
            @else
                {{-- Guest: user icon → login --}}
                <a href="{{ route('login') }}" class="inline-block no-underline" title="Login">
                    <svg class="fill-current nav-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <circle fill="none" cx="12" cy="7" r="3"/>
                        <path d="M12 2C9.243 2 7 4.243 7 7s2.243 5 5 5 5-2.243 5-5S14.757 2 12 2zM12 10c-1.654 0-3-1.346-3-3s1.346-3 3-3 3 1.346 3 3S13.654 10 12 10zM21 21v-1c0-3.859-3.141-7-7-7h-4c-3.86 0-7 3.141-7 7v1h2v-1c0-2.757 2.243-5 5-5h4c2.757 0 5 2.243 5 5v1H21z"/>
                    </svg>
                </a>
            @endauth

            {{-- Cart icon --}}
                        @auth
                            @if(!auth()->user()->is_admin)
                                <a href="{{ route('cart.index') }}" class="inline-block no-underline relative" title="Shopping Cart">
                                    <svg class="fill-current nav-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                        <path d="M21,7H7.462L5.91,3.586C5.748,3.229,5.392,3,5,3H2v2h2.356L9.09,15.414C9.252,15.771,9.608,16,10,16h8 c0.4,0,0.762-0.238,0.919-0.606l3-7c0.133-0.309,0.101-0.663-0.084-0.944C21.649,7.169,21.336,7,21,7z M17.341,14h-6.697L8.371,9 h11.112L17.341,14z"/>
                                        <circle cx="10.5" cy="18.5" r="1.5"/>
                                        <circle cx="17.5" cy="18.5" r="1.5"/>
                                    </svg>
                                    @if(auth()->user()->cartItems()->count() > 0)
                                        <span class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">{{ auth()->user()->cartItems()->count() }}</span>
                                    @endif
                                </a>
                            @endif
                        @endauth
                        @guest
                            <a href="{{ route('login') }}" class="inline-block no-underline" title="Shopping Cart">
                                <svg class="fill-current nav-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <path d="M21,7H7.462L5.91,3.586C5.748,3.229,5.392,3,5,3H2v2h2.356L9.09,15.414C9.252,15.771,9.608,16,10,16h8 c0.4,0,0.762-0.238,0.919-0.606l3-7c0.133-0.309,0.101-0.663-0.084-0.944C21.649,7.169,21.336,7,21,7z M17.341,14h-6.697L8.371,9 h11.112L17.341,14z"/>
                                    <circle cx="10.5" cy="18.5" r="1.5"/>
                                    <circle cx="17.5" cy="18.5" r="1.5"/>
                                </svg>
                            </a>
                        @endguest

        </div>
    </div>

    {{-- Mobile dropdown menu --}}
    <div id="mobile-menu" class="hidden md:hidden border-t border-gray-100 bg-white px-6 py-4 space-y-1">
        <a href="{{ route('products.index') }}"
           class="block py-2 px-3 rounded-lg text-sm font-medium
                  {{ request()->routeIs('products.*') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-black' }}">
            Shop
        </a>
        <a href="{{ route('about') }}"
           class="block py-2 px-3 rounded-lg text-sm font-medium {{ request()->routeIs('about') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-black' }}">
            About
        </a>
        @auth
            @if(auth()->user()->is_admin)
                <a href="{{ route('admin.products.index') }}"
                   class="block py-2 px-3 rounded-lg text-sm font-medium
                          {{ request()->routeIs('admin.*') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-700 hover:bg-gray-50 hover:text-black' }}">
                    Admin
                </a>
            @endif
            <div class="pt-2 mt-2 border-t border-gray-100">
                <p class="px-3 text-xs text-gray-400 mb-1">Signed in as <strong>{{ auth()->user()->name }}</strong></p>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left py-2 px-3 rounded-lg text-sm text-gray-700 hover:bg-gray-50 hover:text-black">
                        Logout
                    </button>
                </form>
            </div>
        @else
            <a href="{{ route('login') }}"
               class="block py-2 px-3 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-black">
                Login
            </a>
        @endauth
    </div>
</nav>

<main class="{{ request()->routeIs('home') ? '' : 'pt-20' }}">
    @if(session('success'))
        <x-toast type="success">{{ session('success') }}</x-toast>
    @endif

    @if(session('error'))
        <x-toast type="error">{{ session('error') }}</x-toast>
    @endif

    @if(session('warning'))
        <x-toast type="warning">{{ session('warning') }}</x-toast>
    @endif

    @yield('content')
</main>

<!-- Footer -->
<footer class="bg-gray-950 text-gray-300 mt-16">
    <div class="max-w-7xl mx-auto px-6">

        <!-- Newsletter strip -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 py-12 border-b border-white/10">
            <div>
                <h3 class="font-display text-2xl font-semibold text-white tracking-tight">Join the club</h3>
                <p class="text-sm text-gray-400 mt-1">New drops, early access, and members-only offers — straight to your inbox.</p>
            </div>
            <form class="flex w-full md:w-auto gap-2" onsubmit="return false;">
                <input type="email" placeholder="you@email.com"
                       class="flex-1 md:w-72 bg-white/5 border border-white/15 rounded-full px-5 py-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-white/40">
                <button type="submit"
                        class="bg-white text-gray-900 font-medium text-sm px-6 py-3 rounded-full hover:bg-gray-200 transition">
                    Subscribe
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 py-12">
            <!-- Brand -->
            <div>
                <div class="flex items-center mb-4">
                    <img src="{{ asset('image/populist.png') }}" alt="Populist" class="h-20 w-auto brightness-0 invert">
                </div>
                <p class="text-sm text-gray-400 leading-relaxed">Curated everyday essentials — minimalist design, premium fabrics, made to last.</p>
            </div>

            <!-- Links -->
            <div>
                <h4 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Quick Links</h4>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="/" class="text-gray-400 hover:text-white transition-colors">HOME</a></li>
                    <li><a href="{{ route('about') }}" class="text-gray-400 hover:text-white transition-colors">ABOUT US</a></li>
                    <li><a href="{{ route('products.index') }}" class="text-gray-400 hover:text-white transition-colors">PRODUCTS</a></li>
                </ul>
            </div>

            <!-- Support -->
            <div>
                <h4 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Support</h4>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="{{ route('about') }}#contact" class="text-gray-400 hover:text-white transition-colors">Contact Us</a></li>
                    <li><a href="{{ route('about') }}#faq" class="text-gray-400 hover:text-white transition-colors">FAQ</a></li>
                </ul>
            </div>

            <!-- Social -->
            <div>
                <h4 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Follow Us</h4>
                <div class="flex space-x-3">
                    <a href="#" aria-label="Facebook" class="p-2.5 bg-white/5 border border-white/10 rounded-xl hover:bg-white hover:text-gray-900 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="https://wa.me/6285166480293" target="_blank" aria-label="WhatsApp" class="p-2.5 bg-white/5 border border-white/10 rounded-xl hover:bg-white hover:text-gray-900 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </a>
                    <a href="https://instagram.com/populist._" target="_blank" aria-label="Instagram" class="p-2.5 bg-white/5 border border-white/10 rounded-xl hover:bg-white hover:text-gray-900 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="border-t border-white/10 py-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-gray-500">
            <p>&copy; {{ date('Y') }} Populist. All rights reserved.</p>
            <p>Designed with care in Indonesia.</p>
        </div>
    </div>
</footer>

<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
    }

    // Navbar scroll effect
    function initNavbar() {
        const header = document.getElementById('header');
        if (!header) return;

        const isHomePage = header.getAttribute('data-is-home') === 'true';

        function applyTransparentStyle() {
            // Toggle CSS classes (inline styles lose to the .navbar-* !important rules)
            header.classList.add('navbar-transparent');
            header.classList.remove('navbar-solid');
        }

        function applySolidStyle() {
            // White background + black text is defined by .navbar-solid in app.css
            header.classList.add('navbar-solid');
            header.classList.remove('navbar-transparent');
        }

        if (!isHomePage) {
            // Not home page - apply solid immediately
            applySolidStyle();
            return;
        }

        // Home page - handle scroll effect
        function updateNavbar() {
            if (window.scrollY > 10) {
                applySolidStyle();
            } else {
                applyTransparentStyle();
            }
        }

        // Run on page load
        updateNavbar();

        // Run on scroll
        window.addEventListener('scroll', updateNavbar, { passive: true });
    }

    // Run immediately
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNavbar);
    } else {
        initNavbar();
    }
</script>
