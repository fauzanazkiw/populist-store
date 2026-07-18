<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50">

    <div class="flex">
        <!-- Sidebar -->
        @include('admin.layouts.sidebar')

        <!-- Main Content -->
        <main class="ml-64 flex-1 flex flex-col min-h-screen">
            <!-- Top Bar -->
            <div class="bg-white border-b border-gray-200 shadow-sm">
                <div class="px-8 py-4 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">@yield('page_title', 'Dashboard')</h1>
                        <p class="text-sm text-gray-500 mt-1">@yield('page_subtitle', '')</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        @yield('page_actions')
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 p-8">
                @if(session('success'))
                    <x-toast type="success">{{ session('success') }}</x-toast>
                @endif

                @if(session('error'))
                    <x-toast type="error">{{ session('error') }}</x-toast>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

</body>
</html>
