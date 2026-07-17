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
$baseClasses = 'group flex items-center rounded-xl px-[14px] py-2.5 text-sm font-medium transition-[color,background-color] duration-200 relative overflow-hidden shrink-0 w-full';
@endphp

@if ($disabled)
    <div class="{{ $baseClasses }} text-neutral-400 dark:text-neutral-600 cursor-not-allowed select-none"
        title="{{ $title }}">
        @if($icon)
        <div class="shrink-0 w-5 h-5 flex items-center justify-center">
            <i class="{{ $icon }} text-base opacity-50"></i>
        </div>
        @endif

        <span class="sidebar-text-transition truncate flex-1"
            x-bind:class="sidebarIsMini ? 'max-w-0 opacity-0 ml-0 pointer-events-none' : 'max-w-[180px] opacity-100 ml-3'">
            {{ $title }}
        </span>
    </div>
@else
    <a href="{{ $href }}" target="{{ $target }}" class="{{ $baseClasses }}"
        x-bind:class="[
            isActive('{{ $href }}')
                ? 'text-white bg-[#2c2724]'
                : 'text-neutral-400 hover:text-white hover:bg-[#2c2724]/40'
        ]" 
        x-bind:title="'{{ $title }}'" 
        x-bind:aria-current="isActive('{{ $href }}') ? 'page' : 'false'" 
        @if($target !== '_blank') wire:navigate @endif>

        @if($icon)
        <div class="shrink-0 w-5 h-5 flex items-center justify-center">
            <i class="{{ $icon }} text-base motion-safe:group-hover:scale-110 motion-safe:transition-transform duration-200"></i>
        </div>
        @endif

        <span class="sidebar-text-transition truncate flex-1 flex items-center justify-between"
            x-bind:class="sidebarIsMini ? 'max-w-0 opacity-0 ml-0 pointer-events-none' : 'max-w-[180px] opacity-100 ml-3'">
            <span class="truncate">{{ $title }}</span>
            @if($badge)
            <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-accent-amber rounded-full shrink-0">
                {{ $badge }}
            </span>
            @endif
          </span>

        <div class="absolute inset-x-0 bottom-0 h-0.5 bg-accent-amber transform motion-safe:scale-x-0 motion-safe:group-hover:scale-x-100 motion-safe:transition-transform duration-300"
            x-bind:class="isActive('{{ $href }}') ? 'motion-safe:scale-x-100 scale-x-100' : ''">
        </div>
    </a>
@endif
