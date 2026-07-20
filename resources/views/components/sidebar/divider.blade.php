@props([
    'margin' => 'my'
])

<div class="h-px bg-neutral-200 dark:bg-warm-border {{ $margin }}-3 shrink-0"
     x-show="!sidebarIsMini"
     x-transition></div>
