<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        {{-- <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title> --}}
        <title>{{ isset($title) ? strip_tags($title) . ' - ' . config('app.name') : config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        <link rel="icon" type="image/x-icon" href="{{ asset('images/thumb-qhrmi.png') }}">
    </head>

    <body class="bg-gray-100 min-h-screen" x-data="{
        sidebarMinimized: $persist(true).as('sidebar_minimized'),
        init() {
            // Listen for sidebar toggle events to keep in sync
            this.$watch('sidebarMinimized', value => {
                this.$dispatch('sidebar-toggled', { minimized: value });
            });
        }
    }" x-init="init()"
        @sidebar-toggled.window="sidebarMinimized = $event.detail.minimized">

        {{-- SIDEBAR --}}
        <livewire:components.sidebar />

        {{-- NAVBAR --}}
        <x-navbar :title="$title ?? 'Dashboard'" />

        {{-- MAIN CONTENT --}}
        <div :class="sidebarMinimized ? 'md:ml-20' : 'md:ml-64'"
            class="transition-all duration-300 pt-16 md:pt-20 px-4 sm:px-6 lg:px-8 py-6 min-h-screen bg-gray-100 dark:bg-gray-900">
            {{ $slot }}
        </div>

        @livewireScripts
        @stack('scripts')
    </body>

</html>
