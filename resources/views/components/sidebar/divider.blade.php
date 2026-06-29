@props([
    'margin' => 'my'
])

<div class="h-px bg-neutral-200 dark:bg-neutral-800 {{ $margin }}-3 shrink-0"
     x-show="!sidebarIsMini"
     x-transition></div>
