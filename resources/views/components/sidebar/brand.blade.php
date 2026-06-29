<a href="{{ route('dashboard') }}" wire:navigate class="flex items-center justify-center mb-8 group">
    <span class="sr-only">homepage</span>
    <div class="flex items-center gap-3 overflow-hidden">
        <div
            class="shrink-0 w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-105 p-1 ring-1 ring-neutral-200/50 dark:ring-neutral-800">
            <img src="{{ asset('images/thumb-qhrmi.webp') }}" class="w-8 h-8 object-contain" alt="Logo">
        </div>
        <div x-show="!sidebarIsMini" x-transition class="flex flex-col">
            <span class="text-lg font-bold text-neutral-900 dark:text-white leading-none">SPSP</span>
            <span class="text-[10px] text-neutral-500 dark:text-neutral-400 leading-tight">Quantum HRM</span>
        </div>
    </div>
</a>
