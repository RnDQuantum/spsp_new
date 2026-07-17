<div x-data="{ userDropdownIsOpen: false }" class="relative" x-on:keydown.esc.window="userDropdownIsOpen = false">
    <button type="button"
        class="group flex items-center gap-2 rounded-lg p-1.5 text-left text-neutral-600 hover:text-accent-amber hover:bg-warm-ivory focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-amber transition-[color,background-color,ring-color] duration-200 dark:text-neutral-400 dark:hover:text-amber-500 dark:hover:bg-[#1f1b18] dark:focus-visible:outline-amber-500 cursor-pointer"
        x-bind:class="userDropdownIsOpen ? 'bg-warm-ivory text-accent-amber dark:bg-[#1f1b18] dark:text-amber-500' : ''"
        aria-haspopup="true" x-on:click="userDropdownIsOpen = ! userDropdownIsOpen"
        x-bind:aria-expanded="userDropdownIsOpen">
        <div class="relative">
            <div
                class="w-8 h-8 bg-[#2c2724] rounded-lg flex items-center justify-center text-accent-amber text-xs font-bold ring-2 ring-accent-amber/50 group-hover:ring-accent-amber transition-[box-shadow,ring-color] duration-200">
                {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 2)) }}
            </div>
            <div
                class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-green-500 rounded-full border-2 border-white dark:border-neutral-900">
            </div>
        </div>
        <div class="hidden md:flex flex-col">
            <span
                class="text-xs font-semibold text-neutral-900 dark:text-white group-hover:text-accent-amber dark:group-hover:text-amber-500 transition-colors duration-200 leading-tight">
                {{ auth()->user()?->name ?? 'User' }}</span>
            <span class="text-[9px] text-neutral-500 dark:text-neutral-400 leading-none"
                aria-hidden="true">{{ auth()->user()?->email ?? 'User' }}</span>
        </div>
        <i class="fas fa-chevron-down text-[10px] text-neutral-400 group-hover:text-accent-amber transition-[transform,color] duration-200 dark:text-neutral-600 dark:group-hover:text-amber-500"
            x-bind:class="userDropdownIsOpen ? 'rotate-180' : ''"></i>
    </button>

    <div x-cloak x-show="userDropdownIsOpen"
        class="absolute top-12 right-0 z-50 w-56 rounded-xl border border-warm-border bg-white shadow-md divide-y divide-neutral-100 dark:border-[#25211e] dark:bg-[#171412] dark:divide-neutral-800"
        role="menu" x-on:click.outside="userDropdownIsOpen = false" 
        x-transition:enter="transition motion-safe:ease-[cubic-bezier(0.16,1,0.3,1)] ease-out duration-200"
        x-transition:enter-start="opacity-0 motion-safe:transform motion-safe:scale-95" x-transition:enter-end="opacity-100 motion-safe:transform motion-safe:scale-100"
        x-transition:leave="transition motion-safe:ease-[cubic-bezier(0.3,0,0.66,1)] ease-in duration-150" x-transition:leave-start="opacity-100 motion-safe:transform motion-safe:scale-100"
        x-transition:leave-end="opacity-0 motion-safe:transform motion-safe:scale-95">

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
