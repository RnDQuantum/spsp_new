@props([
    'menuItems' => [],
])

<nav x-cloak
    class="fixed left-0 z-40 flex h-svh shrink-0 flex-col border-r border-neutral-800/40 bg-primary-ink p-4 motion-safe:transition-[width,transform,background-color,border-color] motion-safe:duration-250 ease-[cubic-bezier(0.16,1,0.3,1)] shadow-xs"
    x-bind:class="[
        getSidebarWidthClass(),
        getSidebarTransformClass()
    ]"
    aria-label="sidebar navigation">

    <!-- Sidebar Brand/Logo -->
    <x-sidebar.brand />

    <!-- Sidebar Menu -->
    <div class="flex flex-col gap-1.5 pb-6 flex-1 overflow-y-auto overflow-x-hidden scrollbar-hidden sidebar-scroll-container" wire:navigate:scroll
        x-data="{
            init() {
                let scrollPos = localStorage.getItem('sidebarScroll');
                if (scrollPos) {
                    this.$el.scrollTop = parseInt(scrollPos);
                }
            },
            saveScroll() {
                localStorage.setItem('sidebarScroll', this.$el.scrollTop);
            }
        }"
        @scroll.debounce.100ms="saveScroll">
        <x-sidebar.menu :items="$menuItems" />
    </div>

    <!-- Close All Menus Button -->
    <div class="pt-4 border-t border-neutral-800/50">
        <x-sidebar.close-all-button />
    </div>
</nav>
