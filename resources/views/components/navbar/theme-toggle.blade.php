<div x-data="themeState()" x-init="initTheme()">
    <button type="button"
        class="group flex items-center justify-center w-8 h-8 rounded-lg text-neutral-600 hover:text-accent-amber hover:bg-warm-ivory transition-[color,background-color] duration-200 dark:text-neutral-400 dark:hover:text-amber-500 dark:hover:bg-neutral-900 cursor-pointer"
        x-on:click="toggleDarkMode()" aria-label="Toggle dark mode">
        <!-- Sun icon (shown in dark mode) -->
        <i x-show="darkMode" class="fas fa-sun text-sm motion-safe:group-hover:rotate-180 motion-safe:transition-transform duration-500"></i>
        <!-- Moon icon (shown in light mode) -->
        <i x-show="!darkMode" class="fas fa-moon text-sm motion-safe:group-hover:rotate-12 motion-safe:transition-transform duration-500"></i>
    </button>
</div>
