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
    <div class="flex items-center gap-3">
        @if ($showLabel)
            <label class="text-sm font-bold font-mono-data uppercase tracking-wider text-primary-ink/80 dark:text-neutral-300 whitespace-nowrap">
                Pilih Peserta :
            </label>
        @endif

        <div class="flex-1 relative">
            {{-- Selected State Container --}}
            <div x-show="!editing && $wire.participantId"
                 @click="editing = true; $nextTick(() => { $refs.searchInput.focus(); }); $wire.loadInitial(true);"
                 class="w-full px-4 py-2.5 border border-warm-border dark:border-[#25211e] rounded-lg
                        bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-100 cursor-pointer
                        flex items-center justify-between shadow-xs hover:border-accent-amber
                        transition duration-150 ease-in-out min-h-[44px]">
                
                @if ($participantId)
                    @php
                        $selected = collect($availableParticipants)->firstWhere('id', $participantId);
                    @endphp
                    @if ($selected)
                        <div class="flex items-center gap-2 truncate">
                            <svg class="w-4.5 h-4.5 text-accent-amber shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span class="font-bold font-mono-data text-primary-ink dark:text-neutral-100 truncate text-sm">{{ $selected['name'] }}</span>
                            <span class="text-xs font-mono-data text-primary-ink/60 dark:text-neutral-400 shrink-0">({{ $selected['test_number'] }})</span>
                        </div>
                    @endif
                @endif

                {{-- Action Controls Inside Box --}}
                <div class="flex items-center gap-1.5 shrink-0 ml-2">
                    <button type="button" @click.stop="$wire.resetParticipant()"
                        class="p-1 text-primary-ink/40 hover:text-red-600 dark:hover:text-red-400 rounded-full hover:bg-warm-ivory dark:hover:bg-[#1f1b18] transition duration-150"
                        title="Hapus pilihan">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    <svg class="w-4.5 h-4.5 text-primary-ink/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </div>

            {{-- Search & Input Container (Editing Mode / Not Selected) --}}
            <div x-show="editing || !$wire.participantId" class="relative">
                <input type="text" x-ref="searchInput" wire:model.live.debounce.300ms="search"
                    @focus="open = true; $wire.loadInitial()"
                    placeholder="Cari nama peserta atau nomor ujian..."
                    class="w-full px-4 py-2.5 pr-10 border border-warm-border dark:border-[#25211e] rounded-lg
                           bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-100 font-mono-data text-sm
                           focus:outline-none focus:border-accent-amber
                           placeholder-primary-ink/40 dark:placeholder-neutral-500 min-h-[44px]">

                {{-- Dropdown Icon --}}
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <svg class="w-4.5 h-4.5 text-primary-ink/40 dark:text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                class="absolute z-50 w-full mt-1 bg-white dark:bg-[#171412] border border-warm-border dark:border-[#25211e] rounded-lg shadow-xl max-h-80 overflow-y-auto font-mono-data"
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
                        :class="{ 'bg-warm-ivory dark:bg-[#1f1b18] font-bold text-accent-amber border-l-2 border-accent-amber': $wire.participantId === participant.id }"
                        class="px-4 py-3 hover:bg-warm-ivory dark:hover:bg-[#1f1b18] cursor-pointer border-b border-warm-border/50 dark:border-[#25211e] last:border-b-0 transition-colors">
                        <div class="font-bold text-sm text-primary-ink dark:text-neutral-100" x-text="participant.name"></div>
                        <div class="text-xs text-primary-ink/60 dark:text-neutral-400" x-text="participant.test_number"></div>
                    </div>
                </template>

                {{-- Loading More Indicator --}}
                <div x-show="$wire.hasMorePages" class="px-4 py-3 text-center text-xs text-primary-ink/60 dark:text-neutral-400">
                    <div wire:loading wire:target="loadMore" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-accent-amber" xmlns="http://www.w3.org/2000/svg" fill="none"
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
                <div class="px-4 py-2.5 bg-warm-ivory dark:bg-[#1f1b18] border-t border-warm-border dark:border-[#25211e]">
                    <span class="text-xs text-primary-ink/75 dark:text-neutral-400 font-mono-data">
                        Menampilkan <span x-text="$wire.availableParticipants.length" class="font-bold"></span> peserta
                        <span x-show="$wire.hasMorePages"> - Scroll untuk lebih banyak</span>
                    </span>
                </div>
            </div>

            {{-- No Results Message --}}
            <div x-show="open && $wire.availableParticipants.length === 0" x-transition
                class="absolute z-50 w-full mt-1 bg-white dark:bg-[#171412] border border-warm-border dark:border-[#25211e] rounded-lg shadow-xl p-4 font-mono-data">
                <div wire:loading wire:target="loadInitial,search"
                    class="text-xs text-accent-amber italic text-center flex items-center justify-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-accent-amber" xmlns="http://www.w3.org/2000/svg" fill="none"
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
                        class="text-xs text-amber-700 dark:text-amber-400 italic text-center">
                        ⚠️ Tidak ada peserta ditemukan dengan kata kunci "<span x-text="$wire.search"></span>"
                    </div>
                    <div x-show="$wire.search.length === 0"
                        class="text-xs text-primary-ink/60 dark:text-neutral-400 italic text-center">
                        💡 Tidak ada peserta untuk posisi ini
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Loading Indicator --}}
    <div wire:loading wire:target="search" class="mt-2 text-xs font-mono-data text-accent-amber flex items-center gap-2">
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
