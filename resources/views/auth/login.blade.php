@extends('layouts.app')

@section('content')

    <div class="flex justify-center px-4 pt-10 pb-6">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">

        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900">
                Welcome Back
            </h2>

            <p class="text-gray-500 mt-2">
                Login to your account
            </p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <!-- Email -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="Enter your email"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-black focus:outline-none"
                    required
                >

                @error('email')
                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    placeholder="Enter your password"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-black focus:outline-none"
                    required
                >

                @error('password')
                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Button -->
            <button
                type="submit"
                class="w-full bg-black text-white py-3 rounded-xl hover:bg-gray-800 transition duration-200 font-medium"
            >
                Login
            </button>
        </form>

        <!-- Register -->
        <p class="text-center text-sm text-gray-500 mt-6">
            Don’t have an account?

            <a href="{{ route('register') }}"
               class="text-black font-medium hover:underline">
                Register
            </a>
        </p>

    </div>
</div>

@endsection
