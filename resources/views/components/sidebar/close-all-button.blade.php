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
        class="w-full group flex items-center justify-center rounded-xl px-[14px] py-2.5 text-sm font-medium transition-[color,background-color,border-color] duration-200 relative overflow-hidden text-neutral-400 hover:text-white hover:bg-[#2c2724]/40 border border-neutral-800 hover:border-accent-amber/50"
        x-bind:title="sidebarIsMini ? 'Tutup Semua Menu' : ''"
    >
        <div class="flex items-center justify-center">
            <div class="shrink-0 w-5 h-5 flex items-center justify-center">
                <i class="fa-solid fa-xmark text-base motion-safe:group-hover:scale-110 motion-safe:group-hover:rotate-180 motion-safe:transition-transform duration-300"></i>
            </div>
            
            <span class="sidebar-text-transition truncate"
                x-bind:class="sidebarIsMini ? 'max-w-0 opacity-0 ml-0 pointer-events-none' : 'max-w-[180px] opacity-100 ml-2'">
                Tutup Semua Menu
            </span>
        </div>
    </button>
</div>
