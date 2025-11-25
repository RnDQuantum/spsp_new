@if($hasAdjustments)
    @php
        $sizeClasses = match($size) {
            'sm' => 'text-xs px-2 py-0.5',
            'lg' => 'text-base px-4 py-2',
            default => 'text-sm px-3 py-1',
        };

        $iconSize = match($size) {
            'sm' => 'w-3 h-3',
            'lg' => 'w-5 h-5',
            default => 'w-4 h-4',
        };

        $positionClasses = match($position) {
            'block' => 'block w-full justify-center',
            'float' => 'float-right',
            default => 'inline-flex',
        };
    @endphp

    <span class="{{ $positionClasses }} items-center gap-1 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 {{ $sizeClasses }}">
        @if($showIcon)
            <svg class="{{ $iconSize }}" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
        @endif
        <span>{{ $customLabel ?? 'Standar Disesuaikan' }}</span>
    </span>
@endif