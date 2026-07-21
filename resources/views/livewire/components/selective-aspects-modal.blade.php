{{-- Selective Aspects Modal - Executive Journal Theme --}}
<div>
    {{-- Mary UI Modal --}}
    <x-mary-modal wire:model="show" title="Pilih Aspek & Sub-Aspek {{ ucfirst($categoryCode) }} untuk Analisis"
        subtitle="Pilih minimal 3 aspek dengan total bobot 100%" class="backdrop-blur" box-class="w-full max-w-4xl rounded-xl border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-100 shadow-2xl font-sans"
        separator>
        {{-- Loading State --}}
        <div wire:loading wire:target="show" class="p-12">
            <div class="flex flex-col items-center justify-center space-y-4">
                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-accent-amber mb-2"></div>
                <p class="text-xs font-mono-data text-primary-ink/75 dark:text-neutral-400">Memuat data aspek dan sub-aspek...</p>
            </div>
        </div>

        {{-- Main Content --}}
        <div wire:loading.remove wire:target="show">
            @if ($dataLoaded)
            {{-- Bulk Actions --}}
            <div class="flex flex-wrap items-center gap-2.5 mb-4">
                <button type="button" wire:click="selectAll"
                    wire:loading.attr="disabled" wire:target="selectAll"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-bold font-mono-data bg-warm-ivory dark:bg-[#1f1b18] border border-warm-border dark:border-[#25211e] text-primary-ink dark:text-neutral-100 hover:bg-accent-amber hover:text-white transition-colors cursor-pointer disabled:opacity-50">
                    <svg wire:loading wire:target="selectAll" class="animate-spin h-3 w-3 text-accent-amber" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>✓ Pilih Semua</span>
                </button>
                <button type="button" wire:click="deselectAll"
                    wire:loading.attr="disabled" wire:target="deselectAll"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-bold font-mono-data bg-warm-ivory dark:bg-[#1f1b18] border border-warm-border dark:border-[#25211e] text-primary-ink dark:text-neutral-100 hover:bg-accent-amber hover:text-white transition-colors cursor-pointer disabled:opacity-50">
                    <svg wire:loading wire:target="deselectAll" class="animate-spin h-3 w-3 text-accent-amber" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>✗ Hapus Semua</span>
                </button>
                <button type="button" wire:click="autoDistributeWeights"
                    wire:loading.attr="disabled" wire:target="autoDistributeWeights"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-bold font-mono-data bg-warm-ivory dark:bg-[#1f1b18] border border-warm-border dark:border-[#25211e] text-primary-ink dark:text-neutral-100 hover:bg-accent-amber hover:text-white transition-colors cursor-pointer disabled:opacity-50">
                    <svg wire:loading wire:target="autoDistributeWeights" class="animate-spin h-3 w-3 text-accent-amber" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>⚖ Distribusi Bobot Otomatis</span>
                </button>
            </div>

            {{-- Scrollable Aspects List --}}
            <div class="max-h-[50vh] overflow-y-auto custom-scrollbar space-y-3 pr-1">
                @forelse ($this->templateAspects as $aspect)
                <div
                    class="border border-warm-border dark:border-[#25211e] rounded-xl overflow-hidden bg-white dark:bg-[#171412] transition-all">
                    {{-- Aspect Row --}}
                    <div class="flex items-center gap-3 p-3 bg-warm-ivory/60 dark:bg-[#1f1b18]">
                        {{-- Mary UI Checkbox --}}
                        <x-mary-checkbox wire:model.live="selectedAspects.{{ $aspect->code }}"
                            id="aspect_{{ $aspect->code }}" class="checkbox-amber" />

                        {{-- Expand/Collapse Button (for Potensi) --}}
                        @if ($categoryCode === 'potensi' && $aspect->subAspects && $aspect->subAspects->count() > 0)
                        <button wire:click="toggleExpand('{{ $aspect->code }}')" type="button"
                            class="text-primary-ink/60 hover:text-accent-amber dark:text-neutral-400 dark:hover:text-accent-amber transition-colors"
                            aria-label="Toggle sub-aspects">
                            <svg class="w-4 h-4 transform transition-transform duration-200 {{ $expandedAspects[$aspect->code] ?? false ? 'rotate-90' : '' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </button>
                        @endif

                        {{-- Aspect Name --}}
                        <div class="flex-1">
                            <span
                                class="font-semibold text-sm text-primary-ink dark:text-neutral-100 {{ !($selectedAspects[$aspect->code] ?? true) ? 'line-through opacity-50' : '' }}">
                                {{ $aspect->name }}
                            </span>
                            @if ($aspect->subAspects && $aspect->subAspects->count() > 0)
                            <span class="text-xs font-mono-data text-primary-ink/60 dark:text-neutral-400 ml-2">
                                ({{ $aspect->subAspects->count() }} sub-aspek)
                            </span>
                            @endif
                        </div>

                        {{-- Weight Input --}}
                        <div class="flex items-center gap-1.5">
                            <input type="number" wire:model.blur="aspectWeights.{{ $aspect->code }}" min="0" max="100"
                                class="w-20 px-2 py-1 text-center font-mono-data font-bold text-xs border border-warm-border dark:border-[#25211e] rounded-md bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-100 focus:outline-none focus:border-accent-amber transition-all {{ !($selectedAspects[$aspect->code] ?? true) ? 'opacity-50 cursor-not-allowed' : '' }}"
                                {{ !($selectedAspects[$aspect->code] ?? true) ? 'disabled' : '' }}
                            >
                            <span class="text-xs font-mono-data text-primary-ink/70 dark:text-neutral-400">%</span>
                        </div>
                    </div>

                    {{-- Sub-Aspects (for Potensi) --}}
                    @if ($categoryCode === 'potensi' && $aspect->subAspects && $aspect->subAspects->count() > 0 &&
                    ($expandedAspects[$aspect->code] ?? false))
                    <div
                        class="border-t border-warm-border dark:border-[#25211e] p-3 pl-10 space-y-2 bg-white dark:bg-[#171412]">
                        @foreach ($aspect->subAspects as $subAspect)
                        <div
                            class="flex items-center gap-3 py-1 px-2 hover:bg-warm-ivory/40 dark:hover:bg-[#1f1b18]/40 rounded-lg transition-colors">
                            {{-- Mary UI Sub-Aspect Checkbox --}}
                            <x-mary-checkbox
                                wire:model.live="selectedSubAspects.{{ $aspect->code }}.{{ $subAspect->code }}"
                                id="subaspect_{{ $aspect->code }}_{{ $subAspect->code }}"
                                class="checkbox-sm checkbox-amber"
                                :disabled="!($selectedAspects[$aspect->code] ?? true)" />

                            {{-- Sub-Aspect Name --}}
                            <div class="flex-1">
                                <span
                                    class="text-xs text-primary-ink dark:text-neutral-200 {{ !($selectedAspects[$aspect->code] ?? true) || !($selectedSubAspects[$aspect->code][$subAspect->code] ?? true) ? 'line-through opacity-50' : '' }}">
                                    {{ $subAspect->name }}
                                </span>
                            </div>

                            {{-- Standard Rating Display --}}
                            <div
                                class="text-xs font-mono-data font-bold text-primary-ink/75 dark:text-neutral-400 px-2 py-0.5 bg-warm-ivory dark:bg-[#1f1b18] border border-warm-border dark:border-[#25211e] rounded">
                                Rating: {{ $subAspect->standard_rating }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @empty
                <div class="text-center py-8 text-xs font-mono-data text-primary-ink/60 dark:text-neutral-400">
                    Tidak ada aspek ditemukan untuk kategori ini.
                </div>
                @endforelse
            </div>

            {{-- Validation Summary --}}
            <div class="mt-4 grid grid-cols-2 gap-3">
                {{-- Active Aspects Counter --}}
                <div
                    class="flex items-center justify-between p-3 rounded-lg border border-warm-border dark:border-[#25211e] bg-warm-ivory/60 dark:bg-[#1f1b18]">
                    <div class="flex items-center gap-2">
                        @if ($this->activeAspectsCount >= 3)
                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"></path>
                        </svg>
                        @else
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"></path>
                        </svg>
                        @endif
                        <span class="text-xs font-bold font-mono-data text-primary-ink dark:text-neutral-200">
                            Aspek Aktif:
                        </span>
                    </div>
                    <div class="flex items-center gap-2 font-mono-data">
                        <span class="text-xs font-bold text-primary-ink dark:text-neutral-100">
                            {{ $this->activeAspectsCount }}/{{ $this->totalAspectsCount }}
                        </span>
                        <span class="text-[10px] px-2 py-0.5 rounded font-bold border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412]">
                            Min: 3
                        </span>
                    </div>
                </div>

                {{-- Total Weight Display --}}
                <div
                    class="flex items-center justify-between p-3 rounded-lg border border-warm-border dark:border-[#25211e] bg-warm-ivory/60 dark:bg-[#1f1b18]">
                    <div class="flex items-center gap-2">
                        @if ($this->totalWeight === 100)
                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"></path>
                        </svg>
                        @else
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"></path>
                        </svg>
                        @endif
                        <span class="text-xs font-bold font-mono-data text-primary-ink dark:text-neutral-200">
                            Total Bobot:
                        </span>
                    </div>
                    <div class="flex items-center gap-2 font-mono-data">
                        <span class="text-sm font-bold text-primary-ink dark:text-neutral-100">
                            {{ $this->totalWeight }}%
                        </span>
                        @if ($this->totalWeight !== 100)
                        <span class="text-xs font-bold {{ $this->totalWeight > 100 ? 'text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400' }}">
                            {{ $this->totalWeight > 100 ? '+' : '' }}{{ $this->totalWeight - 100 }}%
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            @endif
        </div>

        {{-- Actions Slot (Footer Buttons) --}}
        <x-slot:actions>
            <button type="button" @click="$wire.show = false"
                class="rounded-lg border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] px-4 py-2 text-xs font-bold uppercase tracking-wider text-primary-ink dark:text-neutral-200 hover:bg-warm-ivory transition-colors cursor-pointer">
                Batal
            </button>
            <button type="button" wire:click="applySelection"
                wire:loading.attr="disabled"
                wire:target="applySelection"
                :disabled="!$wire.validationResult.valid"
                class="inline-flex items-center gap-2 rounded-lg bg-accent-amber px-5 py-2 text-xs font-bold uppercase tracking-wider text-white hover:bg-amber-700 transition-colors shadow-xs disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
                <svg wire:loading wire:target="applySelection" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span>{{ $this->validationResult['valid'] ? '✓ Terapkan Perubahan' : '⚠ Perbaiki Error Dulu' }}</span>
            </button>
        </x-slot:actions>
    </x-mary-modal>

    {{-- Success/Error Toast --}}
    <div x-data="{ show: false, message: '', type: 'success' }"
        @show-validation-error.window="show = true; type = 'error'; message = $event.detail.errors[0]; setTimeout(() => show = false, 4000)"
        @show-success.window="show = true; type = 'success'; message = $event.detail.message; setTimeout(() => show = false, 3000)"
        x-show="show" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2" class="fixed bottom-4 right-4 z-[60] max-w-md"
        style="display: none;">
        <div :class="type === 'success' ? 'bg-green-700 text-white' : 'bg-red-700 text-white'"
            class="px-5 py-3 rounded-lg shadow-xl border border-white/20 flex items-center gap-3 font-mono-data text-xs">
            <svg x-show="type === 'success'" class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd"></path>
            </svg>
            <svg x-show="type === 'error'" class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                    clip-rule="evenodd"></path>
            </svg>
            <span x-text="message" class="flex-1 font-bold"></span>
        </div>
    </div>

    {{-- Custom Scrollbar Styles --}}
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.4);
        }

        .dark .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.4);
        }
    </style>
</div>