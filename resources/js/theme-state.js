/**
 * Theme State Management
 *
 * This module provides Alpine.js state management for dark mode functionality
 * including toggle and persistence.
 */

export function themeState() {
    return {
        darkMode:
            localStorage.theme === "dark" ||
            (!("theme" in localStorage) &&
                window.matchMedia("(prefers-color-scheme: dark)").matches),

        /**
         * Initialize theme
         */
        initTheme() {
            this.applyTheme();
        },

        /**
         * Apply theme to document
         */
        applyTheme() {
            if (this.darkMode) {
                document.documentElement.classList.add("dark");
                document.documentElement.setAttribute("data-theme", "dark");
            } else {
                document.documentElement.classList.remove("dark");
                document.documentElement.setAttribute("data-theme", "light");
            }
        },

        /**
         * Toggle dark mode
         */
        toggleDarkMode() {
            this.darkMode = !this.darkMode;
            this.applyTheme();

            if (this.darkMode) {
                localStorage.theme = "dark";
            } else {
                localStorage.theme = "light";
            }
        },

        /**
         * Set dark mode explicitly
         * @param {boolean} isDark
         */
        setDarkMode(isDark) {
            this.darkMode = isDark;
            this.applyTheme();

            if (this.darkMode) {
                localStorage.theme = "dark";
            } else {
                localStorage.theme = "light";
            }
        },
    };
}
