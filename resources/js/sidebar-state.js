/**
 * Sidebar State Management
 *
 * This module provides Alpine.js state management for sidebar functionality
 * including toggle, mini mode, and responsive behavior.
 */

export function sidebarState() {
    return {
        sidebarIsOpen: window.innerWidth >= 768,
        sidebarIsMini: false,
        modalOpen: false,
        currentPath: window.location.pathname,

        /**
         * Handle window resize events
         */
        handleResize() {
            if (window.innerWidth >= 768) {
                this.sidebarIsOpen = true;
            } else {
                this.sidebarIsMini = false;
            }
        },

        /**
         * Toggle sidebar state
         * - Desktop: Toggle between full and mini mode
         * - Mobile: Toggle open/close
         */
        toggleSidebar() {
            if (window.innerWidth >= 768) {
                // Desktop: toggle between full and mini
                this.sidebarIsMini = !this.sidebarIsMini;
            } else {
                // Mobile: toggle open/close
                this.sidebarIsOpen = !this.sidebarIsOpen;
            }
        },

        /**
         * Check if a menu item is active based on current route
         * @param {string} pattern - Route pattern to check
         * @returns {boolean}
         */
        isActive(pattern) {
            const currentPath = this.currentPath || window.location.pathname;
            let patternPath = pattern;
            try {
                if (pattern.startsWith('http://') || pattern.startsWith('https://')) {
                    patternPath = new URL(pattern, window.location.origin).pathname;
                }
            } catch (e) {}

            // Handle wildcards or exact path matching
            const cleanPath = currentPath.replace(/\/$/, '');
            const cleanPattern = patternPath.replace(/\/$/, '');

            if (cleanPattern.includes('*')) {
                const regex = new RegExp("^" + cleanPattern.replace(/\*/g, ".*"));
                return regex.test(cleanPath);
            }

            if (cleanPattern === '') {
                return cleanPath === '';
            }
            return cleanPath === cleanPattern || cleanPath.startsWith(cleanPattern + '/');
        },

        /**
         * Check if a dropdown should be expanded
         * @param {string} pattern - Route pattern to check
         * @returns {boolean}
         */
        isExpanded(pattern) {
            return this.isActive(pattern);
        },

        /**
         * Get sidebar width class based on state
         * @returns {string}
         */
        getSidebarWidthClass() {
            if (this.sidebarIsMini) {
                return "w-20";
            }
            return "w-64 lg:w-72";
        },

        /**
         * Get sidebar transform class based on state
         * @returns {string}
         */
        getSidebarTransformClass() {
            if (this.sidebarIsOpen) {
                return "translate-x-0";
            }
            return this.sidebarIsMini
                ? "-translate-x-20"
                : "-translate-x-64 lg:-translate-x-72";
        },

        /**
         * Get main content margin class based on sidebar state
         * @returns {object} - Object containing margin and overflow classes
         */
        getContentMarginClass() {
            let marginClass;
            if (!this.sidebarIsOpen) {
                marginClass = "md:ml-0";
            } else if (this.sidebarIsMini) {
                marginClass = "md:ml-20";
            } else {
                marginClass = "md:ml-64 lg:ml-72";
            }

            return {
                margin: marginClass,
                overflow: this.modalOpen
                    ? "overflow-hidden"
                    : "overflow-y-auto",
            };
        },
    };
}
