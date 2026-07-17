<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? strip_tags($title) : config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Favicon & Preloads --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('images/thumb-qhrmi.webp') }}">
    <link rel="preload" as="image" href="{{ asset('images/thumb-qhrmi.webp') }}" fetchpriority="high">
    <link rel="preload" as="image" href="{{ asset('images/thumb-qhrmi-hd.webp') }}" fetchpriority="high">

    {{-- Preconnect hints for external resources --}}
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    {{-- Google Fonts: Instrument Sans --}}
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script>
        // Initialize dark mode from localStorage before page renders
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia(
                '(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            document.documentElement.setAttribute('data-theme', 'light');
        }
    </script>
</head>

<body class="bg-neutral-50 dark:bg-neutral-900 h-svh overflow-hidden">
    <div x-data="sidebarState()" 
        x-on:resize.window.debounce.100ms="handleResize()"
        x-on:livewire:navigated.window="currentPath = window.location.pathname"
        x-on:modal-opened.window="modalOpen = true"
        x-on:modal-closed.window="modalOpen = false"
        class="relative flex w-full flex-col md:flex-row h-full">
        
        <!-- Skip to main content for screen readers -->
        <a class="sr-only" href="#main-content">skip to the main content</a>

        <!-- Dark overlay for when the sidebar is open on smaller screens -->
        <div x-cloak x-show="sidebarIsOpen" class="fixed inset-0 z-20 bg-neutral-950/10 backdrop-blur-xs md:hidden"
            aria-hidden="true" x-on:click="sidebarIsOpen = false" x-transition.opacity></div>

        <!-- Sidebar Component -->
        @persist('sidebar')
            <livewire:components.sidebar />
        @endpersist

        <!-- Main Content Area with Navbar -->
        <div id="main-content" class="h-full w-full bg-white/80 motion-safe:transition-[margin] motion-safe:duration-250 ease-[cubic-bezier(0.16,1,0.3,1)] ease-out will-change-[margin] dark:bg-neutral-950/80 overflow-y-auto"
            x-bind:class="[getContentMarginClass().margin]">
            <x-navbar.index :title="$title ?? 'Dashboard'">
                {{ $slot }}
            </x-navbar.index>
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
</body>

</html>
