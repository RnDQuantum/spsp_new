<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Login - {{ config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles

        <style>
            /* Force dark mode visibility */
            .dark input[type="email"],
            .dark input[type="password"] {
                background-color: rgb(55, 65, 81) !important;
                color: rgb(243, 244, 246) !important;
                border-color: rgb(75, 85, 99) !important;
            }

            .dark input[type="email"]::placeholder,
            .dark input[type="password"]::placeholder {
                color: rgb(156, 163, 175) !important;
            }

            .dark label {
                color: rgb(229, 231, 235) !important;
            }

            .dark input[type="checkbox"] {
                background-color: rgb(55, 65, 81) !important;
                border-color: rgb(75, 85, 99) !important;
            }

            .dark input[type="checkbox"]:checked {
                background-color: rgb(59, 130, 246) !important;
                border-color: rgb(59, 130, 246) !important;
            }

            /* Icon colors */
            .dark svg {
                color: rgb(209, 213, 219) !important;
            }
        </style>
    </head>

    <body
        class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-100 via-gray-200 to-gray-300 dark:from-gray-900 dark:via-gray-800 dark:to-gray-700">
        <div class="w-full max-w-lg p-6" x-data="{ loading: false }">
            {{-- Header --}}
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                    Static Pribadi Spider Plot (SPSP)
                </h1>
                <p class="text-gray-600 dark:text-gray-300">
                    Silakan login untuk melanjutkan
                </p>
            </div>

            {{-- Login Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-8 border border-gray-200 dark:border-gray-700">
                <form action="{{ route('login') }}" method="POST" @submit="loading = true">
                    @csrf

                    {{-- Error Messages --}}
                    @if ($errors->any())
                        <div
                            class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 mr-3" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <ul class="list-disc list-inside text-sm text-red-800 dark:text-red-200">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    {{-- Email Input --}}
                    <div class="mb-5">
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                autocomplete="email" placeholder="admin@example.com"
                                class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent">
                        </div>
                    </div>

                    {{-- Password Input --}}
                    <div class="mb-5">
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input type="password" name="password" id="password" required
                                autocomplete="current-password" placeholder="••••••••"
                                class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent">
                        </div>
                    </div>

                    {{-- Remember Me Checkbox --}}
                    <div class="mb-6">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="remember" id="remember"
                                class="w-4 h-4 border-2 border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-blue-600 focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 cursor-pointer">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-200">Ingat saya</span>
                        </label>
                    </div>

                    {{-- Login Button --}}
                    <button type="submit" x-bind:disabled="loading"
                        class="w-full bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-semibold py-2.5 px-4 rounded-lg transition duration-200 ease-in-out flex items-center justify-center relative disabled:opacity-70 disabled:cursor-not-allowed">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        Login
                        <template x-if="loading">
                            <span class="loading loading-spinner loading-sm absolute right-4"></span>
                        </template>
                    </button>
                </form>

                <div class="mt-3">
                    <a href="{{ url('/') }}"
                        class="w-full inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium rounded-lg
              border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200
              bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700
              transition duration-200 ease-in-out">
                        Kembali ke Beranda
                    </a>
                </div>



                {{-- Default Credentials Info --}}
                <div
                    class="mt-6 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gray-100 dark:bg-gray-700 px-4 py-2 border-b border-gray-200 dark:border-gray-600">
                        <p class="text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wide">
                            Demo Credentials
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Klik untuk mengisi otomatis
                        </p>
                    </div>

                    <!-- Scrollable Content -->
                    <div class="max-h-64 overflow-y-auto p-4 space-y-2">
                        <!-- Admin -->
                        <div class="flex items-center justify-between p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-600/50 transition-colors"
                            x-data="{ clicked: false }">
                            <div class="flex items-center space-x-2 flex-1">
                                <div
                                    class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-200">Admin</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Super Administrator
                                    </p>
                                </div>
                            </div>
                            <button type="button"
                                @click="
                                    document.getElementById('email').value = 'admin@example.com';
                                    document.getElementById('password').value = 'password';
                                    clicked = true;
                                    setTimeout(() => clicked = false, 2000);
                                "
                                class="ml-2 px-3 py-1.5 text-xs font-medium rounded-md bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors flex items-center space-x-1 flex-shrink-0">
                                <span x-show="!clicked">admin@example.com</span>
                                <span x-show="clicked" x-transition class="text-green-600 dark:text-green-400">✓
                                    Filled</span>
                            </button>
                        </div>

                        @if (isset($institutions) && $institutions->count() > 0)
                            <!-- Dynamic Institutions -->
                            @foreach ($institutions as $index => $institution)
                                @if ($institution->users->first())
                                    @php
                                        $user = $institution->users->first();
                                    @endphp
                                    <div class="flex items-center justify-between p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-600/50 transition-colors"
                                        x-data="{ clicked: false }">
                                        <div class="flex items-center space-x-2 flex-1">
                                            <div
                                                class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs font-semibold text-gray-700 dark:text-gray-200 truncate">
                                                    {{ $institution->name }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                    {{ $user->name }}
                                                </p>
                                            </div>
                                        </div>
                                        <button type="button"
                                            @click="
                                                document.getElementById('email').value = '{{ $user->email }}';
                                                document.getElementById('password').value = 'password';
                                                clicked = true;
                                                setTimeout(() => clicked = false, 2000);
                                            "
                                            class="ml-2 px-3 py-1.5 text-xs font-medium rounded-md bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 hover:bg-green-100 dark:hover:bg-green-900/50 transition-colors flex items-center space-x-1 flex-shrink-0">
                                            <span x-show="!clicked" class="truncate max-w-[120px]">{{ $user->email }}</span>
                                            <span x-show="clicked" x-transition class="text-green-600 dark:text-green-400">✓
                                                Filled</span>
                                        </button>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>

                    <!-- Footer Note -->
                    <div class="bg-gray-100 dark:bg-gray-700 px-4 py-2 border-t border-gray-200 dark:border-gray-600">
                        <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                            <svg class="w-3 h-3 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            Semua password: <span class="font-mono font-semibold">password</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @livewireScripts
        @stack('scripts')
    </body>

</html>
