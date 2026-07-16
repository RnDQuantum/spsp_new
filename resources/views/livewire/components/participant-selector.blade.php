<div class="w-full" x-data="{
    open: false,
    editing: false,
    selectedName: '',
    init() {
        // Watch for participantId changes from Livewire
        this.$watch('$wire.participantId', (value) => {
            if (value) {
                const selected = $wire.availableParticipants.find(p => p.id === value);
                this.selectedName = selected ? `${selected.name} (${selected.test_number})` : '';
                this.editing = false;
            } else {
                this.selectedName = '';
                this.editing = true;
            }
        });

        // Initialize editing state based on current value
        this.editing = !$wire.participantId;
    }
}" @click.away="open = false; if ($wire.participantId) editing = false;">
    <div class="flex items-center gap-4">
        @if ($showLabel)
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">
                Pilih Peserta :
            </label>
        @endif

        <div class="flex-1 relative">
            {{-- Selected State Container --}}
            <div x-show="!editing && $wire.participantId"
                 @click="editing = true; $nextTick(() => { $refs.searchInput.focus(); }); $wire.loadInitial(true);"
                 class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                        bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 cursor-pointer
                        flex items-center justify-between shadow-sm hover:border-gray-400 dark:hover:border-gray-500
                        transition duration-150 ease-in-out min-h-[42px]">
                
                @if ($participantId)
                    @php
                        $selected = collect($availableParticipants)->firstWhere('id', $participantId);
                    @endphp
                    @if ($selected)
                        <div class="flex items-center gap-2 truncate">
                            <svg class="w-4.5 h-4.5 text-blue-600 dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span class="font-medium text-gray-900 dark:text-gray-100 truncate">{{ $selected['name'] }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 shrink-0">({{ $selected['test_number'] }})</span>
                        </div>
                    @endif
                @endif

                {{-- Action Controls Inside Box --}}
                <div class="flex items-center gap-1.5 shrink-0 ml-2">
                    <button type="button" @click.stop="$wire.resetParticipant()"
                        class="p-1 text-gray-400 hover:text-red-500 dark:hover:text-red-400 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition duration-150"
                        title="Hapus pilihan">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </div>

            {{-- Search & Input Container (Editing Mode / Not Selected) --}}
            <div x-show="editing || !$wire.participantId" class="relative">
                <input type="text" x-ref="searchInput" wire:model.live.debounce.300ms="search"
                    @focus="open = true; $wire.loadInitial()"
                    placeholder="Cari nama peserta atau nomor ujian..."
                    class="w-full px-4 py-2 pr-10 border border-gray-300 dark:border-gray-600 rounded-lg
                           bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                           focus:ring-2 focus:ring-blue-500 focus:border-transparent
                           placeholder-gray-400 dark:placeholder-gray-500 min-h-[42px]">

                {{-- Dropdown Icon --}}
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </div>

            {{-- Dropdown Menu with Infinite Scroll --}}
            <div x-show="open && $wire.availableParticipants.length > 0"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-80 overflow-y-auto"
                @scroll="
                    const el = $el;
                    const scrollBottom = el.scrollHeight - el.scrollTop - el.clientHeight;
                    if (scrollBottom < 50 && $wire.hasMorePages && !$wire.__instance.loading) {
                        $wire.loadMore();
                    }
                ">

                {{-- Participant List --}}
                <template x-for="participant in $wire.availableParticipants" :key="participant.id">
                    <div @click="$wire.set('participantId', participant.id); open = false; editing = false;"
                        :class="{ 'bg-blue-100 dark:bg-blue-900/50': $wire.participantId === participant.id }"
                        class="px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-b-0">
                        <div class="font-medium text-gray-900 dark:text-gray-100" x-text="participant.name"></div>
                        <div class="text-sm text-gray-500 dark:text-gray-400" x-text="participant.test_number"></div>
                    </div>
                </template>

                {{-- Loading More Indicator --}}
                <div x-show="$wire.hasMorePages" class="px-4 py-3 text-center text-sm text-gray-500 dark:text-gray-400">
                    <div wire:loading wire:target="loadMore" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span>Memuat lebih banyak...</span>
                    </div>
                    <div wire:loading.remove wire:target="loadMore">
                        <span>Scroll untuk memuat lebih banyak...</span>
                    </div>
                </div>

                {{-- Total Count --}}
                <div class="px-4 py-2 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
                    <span class="text-xs text-gray-600 dark:text-gray-400">
                        Menampilkan <span x-text="$wire.availableParticipants.length"></span> peserta
                        <span x-show="$wire.hasMorePages"> - Scroll untuk lebih banyak</span>
                    </span>
                </div>
            </div>

            {{-- No Results Message --}}
            <div x-show="open && $wire.availableParticipants.length === 0" x-transition
                class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg p-4">
                <div wire:loading wire:target="loadInitial,search"
                    class="text-sm text-blue-600 dark:text-blue-400 italic text-center flex items-center justify-center gap-2">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span>Memuat peserta...</span>
                </div>
                <div wire:loading.remove wire:target="loadInitial,search">
                    <div x-show="$wire.search.length > 0"
                        class="text-sm text-amber-600 dark:text-amber-400 italic text-center">
                        ⚠️ Tidak ada peserta ditemukan dengan kata kunci "<span x-text="$wire.search"></span>"
                    </div>
                    <div x-show="$wire.search.length === 0"
                        class="text-sm text-gray-500 dark:text-gray-400 italic text-center">
                        💡 Tidak ada peserta untuk posisi ini
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Loading Indicator --}}
    <div wire:loading wire:target="search" class="mt-2 text-sm text-blue-600 dark:text-blue-400 flex items-center gap-2">
        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
            </circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
        </svg>
        <span>Mencari peserta...</span>
    </div>
</div>
