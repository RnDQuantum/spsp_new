@props([
    'icon' => null,
    'title',
    'active' => '',
    'items' => [],
])

<div x-data="{
    isExpanded: ({{ !empty($active) && request()->routeIs($active) ? 'true' : 'false' }}) || (localStorage.getItem('sidebar-dropdown-{{ Str::slug($title) }}') === 'true'),
    childHrefs: [
        @foreach ($items as $item)
            '{{ $item['href'] ?? '#' }}',
        @endforeach
    ],
    isActiveDropdown() {
        return this.childHrefs.some(href => isActive(href));
    },
    init() {
        this.$watch('isExpanded', value => localStorage.setItem('sidebar-dropdown-{{ Str::slug($title) }}', value));

        // Auto-expand dropdown on livewire:navigated if any sub-item is active
        const checkChildren = () => {
            const currentPath = window.location.pathname.replace(/\/$/, '');
            const hasActiveChild = this.childHrefs.some(href => {
                try {
                    if (href === '#') return false;
                    const hrefPath = new URL(href, window.location.origin).pathname.replace(/\/$/, '');
                    return currentPath === hrefPath || currentPath.startsWith(hrefPath + '/');
                } catch(e) {
                    return false;
                }
            });
            if (hasActiveChild) {
                this.isExpanded = true;
            }
        };

        checkChildren();
        window.addEventListener('livewire:navigated', checkChildren);
    }
}" x-on:close-all-dropdowns.window="isExpanded = false" class="flex flex-col shrink-0">
    <button type="button" x-on:click="isExpanded = !isExpanded" id="{{ Str::slug($title) }}-btn"
        aria-controls="{{ Str::slug($title) }}" x-bind:aria-expanded="isExpanded ? 'true' : 'false'"
        class="group flex items-center justify-between rounded-xl px-[14px] py-2.5 text-sm font-medium transition-[color,background-color] duration-200 relative overflow-hidden w-full"
        x-bind:class="{
            'text-white bg-[#2c2724]': isActiveDropdown(),
            'text-neutral-400 hover:text-white hover:bg-[#2c2724]/40':
                !isActiveDropdown()
        }"
        x-bind:title="sidebarIsMini ? '{{ $title }}' : ''">

        <div class="flex items-center">
            @if ($icon)
                <div class="shrink-0 w-5 h-5 flex items-center justify-center">
                    <i class="{{ $icon }} text-base motion-safe:group-hover:scale-110 motion-safe:transition-transform duration-200"></i>
                </div>
            @endif

            <span class="sidebar-text-transition truncate"
                x-bind:class="sidebarIsMini ? 'max-w-0 opacity-0 ml-0 pointer-events-none' : 'max-w-[180px] opacity-100 ml-3'">
                {{ $title }}
            </span>
        </div>

        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
            class="w-4 h-4 text-neutral-400 group-hover:text-white transition-[transform,color,max-width,opacity,margin] duration-250 ease-[cubic-bezier(0.16,1,0.3,1)] shrink-0"
            x-bind:class="[
                isExpanded ? 'rotate-180 text-white' : 'rotate-0',
                sidebarIsMini ? 'max-w-0 opacity-0 ml-0 pointer-events-none' : 'max-w-[16px] opacity-100 ml-2'
            ]" aria-hidden="true">
            <path fill-rule="evenodd"
                d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z"
                clip-rule="evenodd" />
        </svg>

        <div
            class="absolute inset-x-0 bottom-0 h-0.5 bg-accent-amber transform motion-safe:scale-x-0 motion-safe:group-hover:scale-x-100 motion-safe:transition-transform duration-300"
            x-bind:class="isActiveDropdown() ? 'motion-safe:scale-x-100 scale-x-100' : ''">
        </div>
    </button>

    <ul x-cloak x-collapse x-show="isExpanded" aria-labelledby="{{ Str::slug($title) }}-btn"
        id="{{ Str::slug($title) }}" x-bind:class="sidebarIsMini ? 'hidden' : ''"
        class="mt-1 mb-1 ml-2 pl-4 border-l-2 border-neutral-800 space-y-0.5">

        {{-- Show selected participant warning if Individual Report and reports cannot be shown --}}
        @if ($title === 'Individual Report' && isset($this) && method_exists($this, 'canShowIndividualReports') && !$this->canShowIndividualReports())
            <li class="mx-2 my-2 p-2.5 bg-accent-amber/10 border border-accent-amber/30 rounded-lg">
                <div class="flex items-start gap-2">
                    <i class="fa-solid fa-circle-exclamation text-accent-amber text-sm mt-0.5"></i>
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-accent-amber mb-1">Pilih Peserta</p>
                        <p class="text-[10px] text-neutral-400 leading-normal">
                            Pilih Proyek & Peserta di <a href="{{ route('dashboard') }}" class="text-white hover:underline font-medium">Beranda</a> atau di <a href="{{ route('shortlist') }}" class="text-white hover:underline font-medium">Daftar Peserta</a>.
                        </p>
                    </div>
                </div>
            </li>
        @endif

        @foreach ($items as $item)
            @php
                $isDisabled = $item['disabled'] ?? false;
                $subHref = $item['href'] ?? '#';
            @endphp
            <li>
                @if ($isDisabled)
                    <span class="group flex items-center rounded-lg gap-2 px-3 py-1.5 text-xs text-neutral-400 dark:text-neutral-600 cursor-not-allowed select-none">
                        <span class="w-1.5 h-1.5 rounded-full bg-neutral-200 dark:bg-neutral-800"></span>
                        {{ $item['title'] }}
                    </span>
                @else
                    <a href="{{ $subHref }}"
                        @isset($item['target']) target="{{ $item['target'] }}" @endisset
                        @if(!isset($item['target']) || $item['target'] !== '_blank') wire:navigate @endif
                        class="group flex items-center rounded-lg gap-2 px-3 py-1.5 text-sm transition-all duration-200"
                        x-bind:class="isActive('{{ $subHref }}')
                            ? 'text-white bg-[#2c2724]'
                            : 'text-neutral-400 hover:text-white hover:bg-[#2c2724]/40'">

                        @if (isset($item['icon']))
                            <i class="{{ $item['icon'] }} text-xs w-4 text-center group-hover:scale-110 transition-transform duration-200"></i>
                        @else
                            <span class="w-1.5 h-1.5 rounded-full bg-neutral-600 group-hover:bg-accent-amber transition-colors duration-200"
                                x-bind:class="isActive('{{ $subHref }}') ? 'bg-accent-amber' : ''"></span>
                        @endif

                        {{ $item['title'] }}

                        @if (isset($item['badge']))
                            <span class="ml-auto inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-accent-amber rounded-full">
                                {{ $item['badge'] }}
                            </span>
                        @endif
                    </a>
                @endif
            </li>
        @endforeach
    </ul>
</div>
