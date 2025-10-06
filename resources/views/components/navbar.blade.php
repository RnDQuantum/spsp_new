@props(['title' => 'Dashboard'])

<!-- Navbar Sticky -->
<nav :class="sidebarMinimized ? 'md:left-20' : 'md:left-64'"
    class="fixed top-0 right-0 left-0 z-30 bg-white border-b border-gray-200 shadow-sm transition-all duration-300">
    <div class="px-4 py-3 lg:px-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <h2 class="text-xl font-semibold text-gray-800 ml-12 md:ml-0">{{ $title }}</h2>
            </div>

            <div class="flex items-center space-x-4">
                <!-- Notification -->
                <button class="relative p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                        </path>
                    </svg>
                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>

                <!-- User Profile -->
                <div class="flex items-center space-x-3">
                    <div class="hidden md:block text-right">
                        <p class="text-sm font-medium text-gray-800">{{ auth()->user()?->name ?? 'John Doe' }}</p>
                        <p class="text-xs text-gray-500">{{ auth()->user()?->email ?? 'Administrator' }}</p>
                    </div>
                    <div
                        class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold">
                        {{ strtoupper(substr(auth()->user()?->name ?? 'JD', 0, 2)) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>