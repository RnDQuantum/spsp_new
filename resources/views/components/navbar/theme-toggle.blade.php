<div x-data="themeState()" x-init="initTheme()">
    <button type="button"
        class="group flex items-center justify-center w-8 h-8 rounded-lg text-neutral-600 hover:text-red-600 hover:bg-red-50/50 transition-[color,background-color] duration-200 dark:text-neutral-400 dark:hover:text-red-400 dark:hover:bg-red-950/50 cursor-pointer"
        x-on:click="toggleDarkMode()" aria-label="Toggle dark mode">
        <!-- Sun icon (shown in dark mode) -->
        <i x-show="darkMode" class="fas fa-sun text-sm motion-safe:group-hover:rotate-180 motion-safe:transition-transform duration-500"></i>
        <!-- Moon icon (shown in light mode) -->
        <i x-show="!darkMode" class="fas fa-moon text-sm motion-safe:group-hover:rotate-12 motion-safe:transition-transform duration-500"></i>
    </button>
</div>
