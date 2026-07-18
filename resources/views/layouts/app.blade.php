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
                    <li><a href="{{ route('about') }}#shipping" class="text-gray-400 hover:text-white transition-colors">Shipping Info</a></li>
                </ul>
            </div>

            <!-- Social -->
            <div>
                <h4 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Follow Us</h4>
                <div class="flex space-x-3">
                    <a href="#" aria-label="Facebook" class="p-2.5 bg-white/5 border border-white/10 rounded-xl hover:bg-white hover:text-gray-900 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="#" aria-label="Twitter" class="p-2.5 bg-white/5 border border-white/10 rounded-xl hover:bg-white hover:text-gray-900 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2s9 5 20 5a9.5 9.5 0 00-9-5.5c4.75 2.25 9 5.75 9 5.75s-3 1-5 1c-5 0-10-2-10-8s5-8 10-8c3 0 5.5 1 7 2.5z"/></svg>
                    </a>
                    <a href="#" aria-label="LinkedIn" class="p-2.5 bg-white/5 border border-white/10 rounded-xl hover:bg-white hover:text-gray-900 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.225 0z"/></svg>
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
