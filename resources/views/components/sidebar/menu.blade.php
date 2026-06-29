@props([
    'items' => [],
])

@foreach ($items as $item)
    @if (isset($item['type']) && $item['type'] === 'section')
        <!-- Section Header -->
        <div x-show="!sidebarIsMini" x-transition class="px-3 py-2">
            <p class="text-xs font-semibold text-neutral-500 uppercase tracking-wider dark:text-neutral-400">
                {{ $item['title'] }}
            </p>
        </div>
    @elseif(isset($item['type']) && $item['type'] === 'divider')
        <!-- Divider -->
        <x-sidebar.divider />
    @elseif(isset($item['type']) && $item['type'] === 'dropdown')
        <!-- Dropdown Menu -->
        <x-sidebar.menu-dropdown :icon="$item['icon'] ?? null" :title="$item['title']" :active="$item['active'] ?? ''" :items="$item['items'] ?? []" />
    @else
        <!-- Single Menu Item -->
        <x-sidebar.menu-item :icon="$item['icon'] ?? null" :title="$item['title']" :href="$item['href'] ?? '#'" :active="$item['active'] ?? false"
            :badge="$item['badge'] ?? null" :target="$item['target'] ?? '_self'" />
    @endif
@endforeach
