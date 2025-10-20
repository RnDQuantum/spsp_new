<div class="w-full">
    <div class="flex items-center gap-4">
        @if ($showLabel)
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">
                Pilih Jabatan :
            </label>
        @endif

        <div class="flex-1">
            <x-mary-choices-offline wire:model.live="positionFormationId" :options="$availablePositions" option-value="id"
                option-label="name" placeholder="Pilih jabatan..." single searchable>
                {{-- Custom item slot untuk list dropdown --}}
                @scope('item', $position)
                    <div
                        class="p-2 hover:bg-blue-50 dark:hover:bg-blue-900/30 cursor-pointer
                        {{ $this->positionFormationId == $position->id ? 'bg-blue-100 dark:bg-blue-900/50 font-semibold text-blue-700 dark:text-blue-300' : 'dark:text-gray-200' }}">
                        {{ $position->name }}
                    </div>
                @endscope

                {{-- Optional: Custom selection slot --}}
                @scope('selection', $position)
                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $position->name }}</span>
                @endscope
            </x-mary-choices-offline>
        </div>
    </div>
</div>
