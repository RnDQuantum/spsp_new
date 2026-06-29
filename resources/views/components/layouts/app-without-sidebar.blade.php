@props([
    'title' => null,
])

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
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>

<body class="bg-neutral-50 dark:bg-neutral-900">
    <div class="h-svh w-full overflow-y-auto bg-white/80 backdrop-blur-sm dark:bg-neutral-950/80">
        <x-navbar.index :showSidebarToggle="false" :title="$title ?? 'Dashboard'">
            {{ $slot }}
        </x-navbar.index>
    </div>
    @livewireScripts
</body>

</html>
