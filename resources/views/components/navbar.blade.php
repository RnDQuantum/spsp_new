@props(['title' => 'Dashboard'])

<nav class="sticky top-0 z-30 flex items-center justify-between border-b border-neutral-200/60 bg-white/95 backdrop-blur-md px-4 py-2.5 dark:border-neutral-800/60 dark:bg-neutral-950/95 shadow-sm w-full"
    aria-label="top navigation bar">

    <div class="flex items-center gap-3">
        <!-- Sidebar Toggle Button -->
        <button type="button"
            class="group flex items-center justify-center w-8 h-8 rounded-lg text-neutral-600 hover:text-red-600 hover:bg-red-50/50 transition-all duration-200 dark:text-neutral-400 dark:hover:text-red-400 dark:hover:bg-red-950/50 cursor-pointer"
            x-on:click="toggleSidebar()">
            <i class="fa-solid fa-left-right text-sm group-hover:scale-110 transition-transform duration-200"></i>
            <span class="sr-only">sidebar toggle</span>
        </button>

        <!-- Page Title -->
        <h2 class="text-sm md:text-base font-semibold text-neutral-800 dark:text-neutral-200 truncate max-w-[200px] sm:max-w-xs md:max-w-md lg:max-w-lg">
            {!! $title !!}
        </h2>
    </div>

    <div class="flex items-center gap-2">
        <!-- Notification Button -->
        <button x-data="{ hasNotification: false }" x-tooltip.raw="'Notifikasi'"
            class="relative flex items-center justify-center w-8 h-8 rounded-lg text-neutral-600 hover:text-red-600 hover:bg-red-50/50 transition-all duration-200 dark:text-neutral-400 dark:hover:text-red-400 dark:hover:bg-red-950/50 cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                </path>
            </svg>
            <span x-show="hasNotification"
                class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full animate-pulse"
                style="display: none;"></span>
        </button>

        <!-- Dark Mode Toggle Button -->
        <div x-data="themeState()" x-init="initTheme()">
            <button type="button"
                class="group flex items-center justify-center w-8 h-8 rounded-lg text-neutral-600 hover:text-red-600 hover:bg-red-50/50 transition-all duration-200 dark:text-neutral-400 dark:hover:text-red-400 dark:hover:bg-red-950/50 cursor-pointer"
                x-on:click="toggleDarkMode()" aria-label="Toggle dark mode">
                <!-- Sun icon (shown in dark mode) -->
                <i x-show="darkMode" class="fas fa-sun text-sm group-hover:rotate-180 transition-transform duration-500"></i>
                <!-- Moon icon (shown in light mode) -->
                <i x-show="!darkMode" class="fas fa-moon text-sm group-hover:rotate-12 transition-transform duration-500"></i>
            </button>
        </div>

        <!-- Divider -->
        <div class="h-5 w-px bg-neutral-200 dark:bg-neutral-800 mx-1"></div>

        <!-- Profile Menu Dropdown -->
        <div x-data="{ userDropdownIsOpen: false }" class="relative" x-on:keydown.esc.window="userDropdownIsOpen = false">
            <button type="button"
                class="group flex items-center gap-2 rounded-lg p-1.5 text-left text-neutral-600 hover:text-red-600 hover:bg-red-50/50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-500 transition-all duration-200 dark:text-neutral-400 dark:hover:text-red-400 dark:hover:bg-red-950/50 dark:focus-visible:outline-red-400 cursor-pointer"
                x-bind:class="userDropdownIsOpen ? 'bg-red-50/70 text-red-600 dark:bg-red-950/50 dark:text-red-400' : ''"
                aria-haspopup="true" x-on:click="userDropdownIsOpen = ! userDropdownIsOpen"
                x-bind:aria-expanded="userDropdownIsOpen">
                <div class="relative">
                    <div
                        class="w-8 h-8 bg-red-600 dark:bg-red-500 rounded-lg flex items-center justify-center text-white text-xs font-semibold ring-2 ring-neutral-200 dark:ring-neutral-700 group-hover:ring-red-200 transition-all duration-200">
                        {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 2)) }}
                    </div>
                    <div
                        class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-green-500 rounded-full border-2 border-white dark:border-neutral-900">
                    </div>
                </div>
                <div class="hidden md:flex flex-col">
                    <span
                        class="text-xs font-semibold text-neutral-900 dark:text-white group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors duration-200 leading-tight">
                        {{ auth()->user()?->name ?? 'User' }}</span>
                    <span class="text-[9px] text-neutral-500 dark:text-neutral-400 leading-none"
                        aria-hidden="true">{{ auth()->user()?->email ?? 'User' }}</span>
                </div>
                <i class="fas fa-chevron-down text-[10px] text-neutral-400 group-hover:text-red-600 transition-all duration-200 dark:text-neutral-600 dark:group-hover:text-red-400"
                    x-bind:class="userDropdownIsOpen ? 'rotate-180' : ''"></i>
            </button>

            <!-- User Dropdown Menu -->
            <div x-cloak x-show="userDropdownIsOpen"
                class="absolute top-12 right-0 z-50 w-56 rounded-xl border border-neutral-200/60 bg-white shadow-lg divide-y divide-neutral-100 dark:border-neutral-800/60 dark:bg-neutral-950 dark:divide-neutral-800"
                role="menu" x-on:click.outside="userDropdownIsOpen = false" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95">

                <!-- User Info (Mobile only) -->
                <div class="px-4 py-2.5 md:hidden">
                    <p class="text-xs font-semibold text-neutral-900 dark:text-white truncate">
                        {{ auth()->user()?->name ?? 'User' }}
                    </p>
                    <p class="text-[10px] text-neutral-500 dark:text-neutral-400 truncate">
                        {{ auth()->user()?->email ?? '' }}
                    </p>
                </div>

                <!-- Sign Out -->
                <div class="py-2">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full group flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50/50 transition-all duration-200 dark:text-red-400 dark:hover:bg-red-950/50 cursor-pointer"
                            role="menuitem">
                            <div
                                class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-950/50 flex items-center justify-center group-hover:bg-red-200 dark:group-hover:bg-red-950 transition-colors duration-200">
                                <i class="fas fa-right-from-bracket text-xs"></i>
                            </div>
                            <div class="flex-1 text-left">
                                <p class="font-medium text-xs leading-none">Sign Out</p>
                                <p class="text-[10px] text-red-500/70 dark:text-red-400/70 mt-1">End your session</p>
                            </div>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>
