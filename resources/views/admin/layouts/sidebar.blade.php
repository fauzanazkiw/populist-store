<!-- Admin Sidebar -->
<aside class="w-64 bg-slate-900 text-slate-100 min-h-screen flex flex-col fixed left-0 top-0 z-40">
    <!-- Logo -->
    <div class="p-6 border-b border-slate-700">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2 text-xl font-bold hover:text-white transition">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M13 2H3v8h10V2zm8 0h-6v8h6V2zM3 12h10v8H3v-8zm12 0h6v8h-6v-8z"/>
            </svg>
            <span>Admin</span>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 py-8 space-y-2 overflow-y-auto">
        <!-- Dashboard -->
        <a href="{{ route('admin.dashboard') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg transition
                  {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 11l4-4m0 0l4 4"/>
            </svg>
            <span class="font-medium">Dashboard</span>
        </a>

        <!-- Products -->
        <a href="{{ route('admin.products.index') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg transition
                  {{ request()->routeIs('admin.products.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m0 0l8 4m-8-4v10l8 4m0-10l8 4m-8-4v10m8-10l-8 4"/>
            </svg>
            <span class="font-medium">Products</span>
        </a>

        <!-- Customers -->
        <a href="{{ route('admin.customers.index') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg transition
                  {{ request()->routeIs('admin.customers.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 8.646 4 4 0 010-8.646M9 9H5m14 0h-4m0 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="font-medium">Customers</span>
        </a>

        <!-- Carts -->
        <a href="{{ route('admin.carts.index') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg transition
                  {{ request()->routeIs('admin.carts.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span class="font-medium">Carts</span>
        </a>

        <!-- Orders / Payments -->
        <a href="{{ route('admin.payments.index') }}"
           class="flex items-center space-x-3 px-4 py-3 rounded-lg transition
                  {{ request()->routeIs('admin.payments.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h4m4 0h4M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            <span class="font-medium">Payments</span>
        </a>
    </nav>

    <!-- User Section -->
    <div class="p-4 border-t border-slate-700">
        <div class="flex items-center space-x-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center font-bold">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="text-sm flex-1 truncate">
                <p class="font-medium truncate">{{ auth()->user()->name }}</p>
                <p class="text-slate-400 text-xs">Administrator</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center space-x-2 px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 transition text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>
