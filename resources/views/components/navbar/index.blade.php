@props([
    'showSidebarToggle' => true,
    'title' => 'Dashboard',
])

<nav class="sticky top-0 z-30 flex items-center justify-between border-b border-warm-border bg-white/80 backdrop-blur-md px-4 py-2.5 dark:border-neutral-900 dark:bg-neutral-950/80 shadow-xs w-full"
    aria-label="top navigation bar">

    <div class="flex items-center gap-3">
        <!-- Sidebar Toggle Button -->
        @if ($showSidebarToggle)
            <x-navbar.toggle />
        @endif

        <!-- Page Title -->
        <h2 class="text-sm md:text-base font-bold text-primary-ink dark:text-neutral-100 truncate max-w-[200px] sm:max-w-xs md:max-w-md lg:max-w-lg font-sans">
            {!! $title !!}
        </h2>
    </div>

    <div class="flex items-center gap-2">
        <!-- Dark Mode Toggle Button -->
        <x-navbar.theme-toggle />

        <!-- Divider -->
        <div class="h-5 w-px bg-neutral-200 dark:bg-neutral-800 mx-1"></div>

        <!-- Profile Menu -->
        <x-navbar.user-menu />
    </div>
</nav>

<!-- Main Content -->
<main id="main-content" class="flex-1">
    <div class="p-6 lg:p-8">
        <div class="max-w-7xl mx-auto">
            {{ $slot }}
        </div>
    </div>
</main>
