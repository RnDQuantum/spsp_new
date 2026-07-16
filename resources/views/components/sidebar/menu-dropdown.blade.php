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
        class="group flex items-center justify-between rounded-xl gap-3 px-3 py-2.5 text-sm font-medium transition-all duration-200 relative overflow-hidden"
        x-bind:class="{
            'justify-center px-2.5 py-3': sidebarIsMini,
            'text-red-600 bg-red-50/70 dark:text-red-400 dark:bg-red-950/50': isActiveDropdown(),
            'text-neutral-700 hover:text-red-600 hover:bg-red-50/50 dark:text-neutral-300 dark:hover:text-red-400 dark:hover:bg-red-950/50':
                !isActiveDropdown()
        }"
        x-bind:title="sidebarIsMini ? '{{ $title }}' : ''">

        <div class="flex items-center gap-3">
            @if ($icon)
                <div class="shrink-0 w-5 h-5 flex items-center justify-center">
                    <i class="{{ $icon }} text-base group-hover:scale-110 transition-transform duration-200"></i>
                </div>
            @endif

            <span x-show="!sidebarIsMini" x-transition class="truncate">
                {{ $title }}
            </span>
        </div>

        <svg x-show="!sidebarIsMini" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
            class="w-4 h-4 text-neutral-400 group-hover:text-red-600 transition-all duration-200 shrink-0"
            x-bind:class="isExpanded ? 'rotate-180 text-red-600 dark:text-red-400' : 'rotate-0'" aria-hidden="true">
            <path fill-rule="evenodd"
                d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z"
                clip-rule="evenodd" />
        </svg>

        <div
            class="absolute inset-x-0 bottom-0 h-0.5 bg-linear-to-r from-red-500 to-orange-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300">
        </div>
    </button>

    <ul x-cloak x-collapse x-show="isExpanded" aria-labelledby="{{ Str::slug($title) }}-btn"
        id="{{ Str::slug($title) }}" x-bind:class="sidebarIsMini ? 'hidden' : ''"
        class="mt-1 mb-1 ml-2 pl-4 border-l-2 border-neutral-200 dark:border-neutral-700 space-y-0.5">

        {{-- Show selected participant warning if Individual Report and reports cannot be shown --}}
        @if ($title === 'Individual Report' && isset($this) && method_exists($this, 'canShowIndividualReports') && !$this->canShowIndividualReports())
            <li class="mx-2 my-2 p-2.5 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-600/50 rounded-lg">
                <div class="flex items-start gap-2">
                    <i class="fa-solid fa-circle-exclamation text-yellow-600 dark:text-yellow-400 text-sm mt-0.5"></i>
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-yellow-800 dark:text-yellow-300 mb-1">Pilih Peserta</p>
                        <p class="text-[10px] text-yellow-700 dark:text-yellow-400/90 leading-normal">
                            Pilih Proyek & Peserta di <a href="{{ route('dashboard') }}" class="underline font-medium">Beranda</a> atau di <a href="{{ route('shortlist') }}" class="underline font-medium">Daftar Peserta</a>.
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
                            ? 'text-red-600 bg-red-50/30 dark:text-red-400 dark:bg-red-950/30'
                            : 'text-neutral-700 hover:text-red-600 hover:bg-red-50/30 dark:text-neutral-300 dark:hover:text-red-400 dark:hover:bg-red-950/30'">

                        @if (isset($item['icon']))
                            <i class="{{ $item['icon'] }} text-xs w-4 text-center group-hover:scale-110 transition-transform duration-200"></i>
                        @else
                            <span class="w-1.5 h-1.5 rounded-full bg-neutral-300 group-hover:bg-red-500 transition-colors duration-200 dark:bg-neutral-600"
                                x-bind:class="isActive('{{ $subHref }}') ? 'bg-red-500' : ''"></span>
                        @endif

                        {{ $item['title'] }}

                        @if (isset($item['badge']))
                            <span class="ml-auto inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-red-500 rounded-full">
                                {{ $item['badge'] }}
                            </span>
                        @endif
                    </a>
                @endif
            </li>
        @endforeach
    </ul>
</div>
