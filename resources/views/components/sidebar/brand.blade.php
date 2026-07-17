<a href="{{ route('dashboard') }}" wire:navigate class="flex items-center justify-center mb-8 group w-full">
    <span class="sr-only">homepage</span>
    <div class="flex items-center overflow-hidden w-full justify-center">
        <div
            class="shrink-0 w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-105 p-1 ring-1 ring-neutral-200/50 dark:ring-neutral-800">
            <img src="{{ asset('images/thumb-qhrmi.webp') }}" class="w-8 h-8 object-contain" alt="Logo">
        </div>
        <div class="flex flex-col sidebar-text-transition overflow-hidden"
            x-bind:class="sidebarIsMini ? 'max-w-0 opacity-0 ml-0 pointer-events-none' : 'max-w-[180px] opacity-100 ml-3'">
            <span class="text-lg font-bold text-white leading-none">SPSP</span>
            <span class="text-[10px] text-neutral-400 leading-tight">Quantum HRM</span>
        </div>
    </div>
</a>
