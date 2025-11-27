@props(['title' => 'Dashboard'])

<!-- Navbar Sticky -->
<nav x-data="{ sidebarMinimized: $persist(true).as('sidebar_minimized') }" @sidebar-toggled.window="sidebarMinimized = $event.detail.minimized"
    :class="sidebarMinimized ? 'md:left-20' : 'md:left-64'"
    class="fixed top-0 right-0 left-0 z-30 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-300 md:left-20 backdrop-blur-sm bg-opacity-95 dark:bg-opacity-95">
    <div class="px-4 sm:px-6 lg:px-8 py-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center flex-1 min-w-0">
                <h2 class="text-lg md:text-xl font-semibold text-gray-800 dark:text-gray-100 ml-12 md:ml-0 truncate">
                    {!! $title !!}</h2>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Dark Mode Toggle -->
                <div class="hidden sm:block">
                    <x-mary-theme-toggle class="btn btn-circle btn-ghost" />
                </div>

                <!-- Notification -->
                <button x-data="{ hasNotification: false }" x-tooltip.raw="'Notifikasi'"
                    class="relative p-2 text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-200">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                        </path>
                    </svg>
                    <span x-show="hasNotification"
                        class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full animate-pulse"
                        style="display: none;"></span>
                </button>

                <!-- User Profile with Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="flex items-center gap-2 sm:gap-3 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg py-1.5 px-2 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400">
                        <div class="hidden lg:block text-right">
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                {{ auth()->user()?->name ?? 'John Doe' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ auth()->user()?->email ?? 'Administrator' }}</p>
                        </div>
                        <div
                            class="w-9 h-9 sm:w-10 sm:h-10 bg-blue-600 dark:bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold text-sm ring-2 ring-gray-200 dark:ring-gray-700">
                            {{ strtoupper(substr(auth()->user()?->name ?? 'JD', 0, 2)) }}
                        </div>
                        <svg class="hidden sm:block w-4 h-4 text-gray-500 dark:text-gray-400"
                            :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-3 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-xl py-2 z-50 border border-gray-200 dark:border-gray-700"
                        style="display: none;">
                        <!-- User Info Section -->
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 bg-blue-600 dark:bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                    {{ strtoupper(substr(auth()->user()?->name ?? 'JD', 0, 2)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate">
                                        {{ auth()->user()?->name ?? 'John Doe' }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        {{ auth()->user()?->email ?? 'user@example.com' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Menu Items -->
                        <div class="py-2">
                            <!-- Profile Link (Optional - uncomment if needed) -->
                            <!--
                            <a href="{#  #}
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <i class="fa-solid fa-user w-4 text-center"></i>
                                <span>Profile</span>
                            </a>
                            -->

                            <!-- Dark Mode Toggle - Mobile Only -->
                            <div class="sm:hidden px-4 py-2.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Dark Mode</span>
                                    <x-mary-theme-toggle class="btn btn-sm btn-circle btn-ghost" />
                                </div>
                            </div>
                        </div>

                        <!-- Logout Section -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-2">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                    <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>
                                    <span class="font-medium">Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
