<div class="w-full">
    <div class="flex items-center gap-4">
        @if ($showLabel)
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">
                Pilih Peserta :
            </label>
        @endif

        <div class="flex flex-1 gap-2">
            <div class="flex-1">
                <x-mary-choices-offline wire:model.live="participantId" :options="$availableParticipants" option-value="id"
                    option-label="name" placeholder="Pilih peserta..." single searchable>
                    {{-- Custom item slot untuk list dropdown --}}
                    @scope('item', $participant)
                        <div
                            class="p-2 hover:bg-blue-50 dark:hover:bg-blue-900/30 cursor-pointer
                            {{ $this->participantId == $participant['id'] ? 'bg-blue-100 dark:bg-blue-900/50 font-semibold text-blue-700 dark:text-blue-300' : 'dark:text-gray-200' }}">
                            <div class="font-medium">{{ $participant['name'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $participant['test_number'] }}</div>
                        </div>
                    @endscope

                    {{-- Optional: Custom selection slot --}}
                    @scope('selection', $participant)
                        <div>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $participant['name'] }}</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">({{ $participant['test_number'] }})</span>
                        </div>
                    @endscope
                </x-mary-choices-offline>
            </div>

            @if ($participantId)
                <button wire:click="resetParticipant"
                    class="px-4 py-2 bg-red-500 hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700 text-white rounded-lg transition-colors duration-200 flex items-center gap-2 whitespace-nowrap"
                    title="Reset filter peserta">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span>Reset</span>
                </button>
            @endif
        </div>
    </div>
</div>
