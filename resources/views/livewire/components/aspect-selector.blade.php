<div class="w-full">
    <div class="flex items-center gap-4">
        @if ($showLabel)
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">
                Pilih Aspek :
            </label>
        @endif

        <div class="flex-1">
            <x-mary-choices-offline wire:model.live="aspectId" :options="$availableAspects" option-value="id"
                placeholder="Cari aspek..." single searchable>
                {{-- Custom item slot untuk list dropdown dengan badge kategori --}}
                @scope('item', $aspect)
                    <div class="flex items-center gap-3 py-1 font-semibold">
                        @php
                            $colorClass =
                                strtolower($aspect['category']) === 'potensi'
                                    ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300'
                                    : 'bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-300';
                        @endphp
                        <span class="px-2 mx-2 py-1 rounded text-xs {{ $colorClass }}">
                            {{ strtoupper($aspect['category']) }}
                        </span>
                        <span class="text-gray-900 dark:text-gray-100">{{ $aspect['name'] }}</span>
                    </div>
                @endscope

                {{-- Custom selection slot --}}
                @scope('selection', $aspect)
                    <div class="flex items-center gap-2">
                        @php
                            $colorClass =
                                strtolower($aspect['category']) === 'potensi'
                                    ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300'
                                    : 'bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-300';
                        @endphp
                        <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $colorClass }}">
                            {{ strtoupper($aspect['category']) }}
                        </span>
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $aspect['name'] }}</span>
                    </div>
                @endscope
            </x-mary-choices-offline>
        </div>
    </div>
</div>
