<div x-data="{
    closeAllMenus() {
        // Get all localStorage keys that start with 'sidebar-dropdown-'
        const keys = Object.keys(localStorage).filter(key => key.startsWith('sidebar-dropdown-'));
        
        // Set all dropdown states to 'false' (closed)
        keys.forEach(key => {
            localStorage.setItem(key, 'false');
        });
        
        // Dispatch a custom event to notify all dropdowns to update their state
        window.dispatchEvent(new CustomEvent('close-all-dropdowns'));
    }
}">
    <!-- Close All Button -->
    <button 
        type="button" 
        @click="closeAllMenus()" 
        aria-label="Tutup Semua Menu"
        class="w-full group flex items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium transition-[color,background-color,border-color] duration-200 relative overflow-hidden text-neutral-700 hover:text-red-600 hover:bg-red-50/50 dark:text-neutral-300 dark:hover:text-red-400 dark:hover:bg-red-950/50 border border-neutral-200 dark:border-neutral-700 hover:border-red-300 dark:hover:border-red-800"
        x-bind:class="sidebarIsMini ? 'px-2.5 py-3' : ''"
        x-bind:title="sidebarIsMini ? 'Tutup Semua Menu' : ''"
    >
        <div class="flex items-center gap-2">
            <div class="shrink-0 w-5 h-5 flex items-center justify-center">
                <i class="fa-solid fa-xmark text-base motion-safe:group-hover:scale-110 motion-safe:group-hover:rotate-180 motion-safe:transition-transform duration-300"></i>
            </div>
            
            <span x-show="!sidebarIsMini" x-transition class="truncate">
                Tutup Semua Menu
            </span>
        </div>
    </button>
</div>
