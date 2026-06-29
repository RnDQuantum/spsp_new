@props([
    'icon' => null,
    'title',
    'href' => '#',
    'active' => false,
    'badge' => null,
    'target' => '_self',
    'disabled' => false,
])

@php
$baseClasses = 'group flex items-center rounded-xl gap-3 px-3 py-2.5 text-sm font-medium transition-all duration-200 relative overflow-hidden shrink-0';
@endphp

@if ($disabled)
    <div class="{{ $baseClasses }} text-neutral-400 dark:text-neutral-600 cursor-not-allowed select-none"
        x-bind:class="sidebarIsMini ? 'justify-center px-2.5 py-3' : ''"
        title="{{ $title }}">
        @if($icon)
        <div class="shrink-0 w-5 h-5 flex items-center justify-center">
            <i class="{{ $icon }} text-base opacity-50"></i>
        </div>
        @endif

        <span x-show="!sidebarIsMini" x-transition class="truncate">
            {{ $title }}
        </span>
    </div>
@else
    <a href="{{ $href }}" target="{{ $target }}" class="{{ $baseClasses }}"
        x-bind:class="[
            sidebarIsMini ? 'justify-center px-2.5 py-3' : '',
            isActive('{{ $href }}')
                ? 'text-red-600 bg-red-50/70 dark:text-red-400 dark:bg-red-950/50'
                : 'text-neutral-700 hover:text-red-600 hover:bg-red-50/50 dark:text-neutral-300 dark:hover:text-red-400 dark:hover:bg-red-950/50'
        ]" 
        x-bind:title="'{{ $title }}'" 
        x-bind:aria-current="isActive('{{ $href }}') ? 'page' : 'false'" 
        @if($target !== '_blank') wire:navigate @endif>

        @if($icon)
        <div class="shrink-0 w-5 h-5 flex items-center justify-center">
            <i class="{{ $icon }} text-base group-hover:scale-110 transition-transform duration-200"></i>
        </div>
        @endif

        <span x-show="!sidebarIsMini" x-transition class="truncate">
            {{ $title }}
            @if($badge)
            <span class="ml-auto inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-red-500 rounded-full">
                {{ $badge }}
            </span>
            @endif
        </span>

        <div class="absolute inset-x-0 bottom-0 h-0.5 bg-linear-to-r from-red-500 to-orange-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"
            x-bind:class="isActive('{{ $href }}') ? 'scale-x-100' : ''">
        </div>
    </a>
@endif
