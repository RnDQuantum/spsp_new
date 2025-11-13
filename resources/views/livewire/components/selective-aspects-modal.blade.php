{{-- Selective Aspects Modal - Pure Mary UI Implementation --}}
<div>
    {{-- Mary UI Modal --}}
    <x-mary-modal wire:model="show" title="Pilih Aspek & Sub-Aspek {{ ucfirst($categoryCode) }} untuk Analisis"
        subtitle="Pilih minimal 3 aspek dengan total bobot 100%" class="backdrop-blur" box-class="w-full max-w-4xl"
        separator>
        {{-- Loading State --}}
        <div wire:loading wire:target="show" class="p-12">
            <div class="flex flex-col items-center justify-center space-y-4">
                <svg class="animate-spin h-12 w-12 text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <p class="text-gray-600 dark:text-gray-400">Memuat data aspek dan sub-aspek...</p>
            </div>
        </div>

        {{-- Main Content --}}
        <div wire:loading.remove wire:target="show">
            @if ($dataLoaded)
            {{-- Bulk Actions --}}
            <div class="flex items-center gap-3 mb-4">
                <x-mary-button label="✓ Pilih Semua" wire:click="selectAll"
                    class="btn-sm bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-900/50" />
                <x-mary-button label="✗ Hapus Semua" wire:click="deselectAll"
                    class="btn-sm bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600" />
                <x-mary-button label="⚖ Distribusi Bobot Otomatis" wire:click="autoDistributeWeights"
                    class="btn-sm bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300 hover:bg-green-200 dark:hover:bg-green-900/50" />
            </div>

            {{-- Scrollable Aspects List --}}
            <div class="max-h-[50vh] overflow-y-auto custom-scrollbar space-y-3">
                @forelse ($this->templateAspects as $aspect)
                <div
                    class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                    {{-- Aspect Row --}}
                    <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50">
                        {{-- Checkbox --}}
                        <input type="checkbox" wire:model.live="selectedAspects.{{ $aspect->code }}"
                            id="aspect_{{ $aspect->code }}"
                            class="w-5 h-5 rounded text-blue-600 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 transition-all cursor-pointer">

                        {{-- Expand/Collapse Button (for Potensi) --}}
                        @if ($categoryCode === 'potensi' && $aspect->subAspects && $aspect->subAspects->count() > 0)
                        <button wire:click="toggleExpand('{{ $aspect->code }}')" type="button"
                            class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors"
                            aria-label="Toggle sub-aspects">
                            <svg class="w-5 h-5 transform transition-transform duration-200 {{ $expandedAspects[$aspect->code] ?? false ? 'rotate-90' : '' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </button>
                        @endif

                        {{-- Aspect Name --}}
                        <div class="flex-1">
                            <span
                                class="font-semibold text-gray-900 dark:text-gray-100 {{ !($selectedAspects[$aspect->code] ?? true) ? 'line-through opacity-50' : '' }}">
                                {{ $aspect->name }}
                            </span>
                            @if ($aspect->subAspects && $aspect->subAspects->count() > 0)
                            <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">
                                ({{ $aspect->subAspects->count() }} sub-aspek)
                            </span>
                            @endif
                        </div>

                        {{-- Weight Input --}}
                        <div class="flex items-center gap-2">
                            <input type="number" wire:model.blur="aspectWeights.{{ $aspect->code }}" min="0" max="100"
                                class="w-20 px-2 py-1 text-center border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all {{ !($selectedAspects[$aspect->code] ?? true) ? 'opacity-50 cursor-not-allowed' : '' }}"
                                {{ !($selectedAspects[$aspect->code] ?? true) ? 'disabled' : '' }}
                            >
                            <span class="text-sm text-gray-600 dark:text-gray-400">%</span>
                        </div>
                    </div>

                    {{-- Sub-Aspects (for Potensi) --}}
                    @if ($categoryCode === 'potensi' && $aspect->subAspects && $aspect->subAspects->count() > 0 &&
                    ($expandedAspects[$aspect->code] ?? false))
                    <div
                        class="border-t border-gray-200 dark:border-gray-700 p-3 pl-12 space-y-2 bg-white dark:bg-gray-800">
                        @foreach ($aspect->subAspects as $subAspect)
                        <div
                            class="flex items-center gap-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-700/30 rounded transition-colors">
                            {{-- Sub-Aspect Checkbox --}}
                            <input type="checkbox"
                                wire:model.live="selectedSubAspects.{{ $aspect->code }}.{{ $subAspect->code }}"
                                id="subaspect_{{ $aspect->code }}_{{ $subAspect->code }}"
                                class="w-4 h-4 rounded text-blue-600 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 transition-all cursor-pointer"
                                {{ !($selectedAspects[$aspect->code] ?? true) ? 'disabled' : '' }}
                            >

                            {{-- Sub-Aspect Name --}}
                            <div class="flex-1">
                                <span
                                    class="text-sm text-gray-700 dark:text-gray-300 {{ !($selectedAspects[$aspect->code] ?? true) || !($selectedSubAspects[$aspect->code][$subAspect->code] ?? true) ? 'line-through opacity-50' : '' }}">
                                    {{ $subAspect->name }}
                                </span>
                            </div>

                            {{-- Standard Rating Display --}}
                            <div
                                class="text-xs text-gray-500 dark:text-gray-400 px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">
                                Rating: {{ $subAspect->standard_rating }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @empty
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    Tidak ada aspek ditemukan untuk kategori ini.
                </div>
                @endforelse
            </div>

            {{-- Validation Summary --}}
            <div class="mt-4 grid grid-cols-2 gap-3">
                {{-- Active Aspects Counter --}}
                <div
                    class="flex items-center justify-between p-3 rounded-lg transition-all duration-200 {{ $this->activeAspectsCount >= 3 ? 'bg-green-50 dark:bg-green-900/20' : 'bg-amber-50 dark:bg-amber-900/20' }}">
                    <div class="flex items-center gap-2">
                        @if ($this->activeAspectsCount >= 3)
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"></path>
                        </svg>
                        @else
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"></path>
                        </svg>
                        @endif
                        <span
                            class="text-sm font-medium {{ $this->activeAspectsCount >= 3 ? 'text-green-800 dark:text-green-300' : 'text-amber-800 dark:text-amber-300' }}">
                            Aspek Aktif:
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span
                            class="text-sm font-bold {{ $this->activeAspectsCount >= 3 ? 'text-green-800 dark:text-green-300' : 'text-amber-800 dark:text-amber-300' }}">
                            {{ $this->activeAspectsCount }}/{{ $this->totalAspectsCount }}
                        </span>
                        <span
                            class="text-xs px-2 py-0.5 rounded-full {{ $this->activeAspectsCount >= 3 ? 'bg-green-200 dark:bg-green-800 text-green-800 dark:text-green-200' : 'bg-amber-200 dark:bg-amber-800 text-amber-800 dark:text-amber-200' }}">
                            Min: 3
                        </span>
                    </div>
                </div>

                {{-- Total Weight Display --}}
                <div
                    class="flex items-center justify-between p-3 rounded-lg transition-all duration-200 {{ $this->totalWeight === 100 ? 'bg-green-100 dark:bg-green-900/30' : 'bg-amber-100 dark:bg-amber-900/30' }}">
                    <div class="flex items-center gap-2">
                        @if ($this->totalWeight === 100)
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"></path>
                        </svg>
                        @else
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"></path>
                        </svg>
                        @endif
                        <span
                            class="font-semibold text-sm {{ $this->totalWeight === 100 ? 'text-green-800 dark:text-green-300' : 'text-amber-800 dark:text-amber-300' }}">
                            Total Bobot:
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span
                            class="font-bold text-lg {{ $this->totalWeight === 100 ? 'text-green-800 dark:text-green-300' : 'text-amber-800 dark:text-amber-300' }}">
                            {{ $this->totalWeight }}%
                        </span>
                        @if ($this->totalWeight !== 100)
                        <span
                            class="text-xs font-medium {{ $this->totalWeight > 100 ? 'text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400' }}">
                            {{ $this->totalWeight > 100 ? '+' : '' }}{{ $this->totalWeight - 100 }}%
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Error Messages --}}
            @if (!$this->validationResult['valid'])
            <div class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <div class="flex gap-2">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <div class="text-sm text-red-800 dark:text-red-300">
                        <p class="font-semibold mb-1">Perbaiki error berikut:</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($this->validationResult['errors'] as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif
            @endif
        </div>

        {{-- Actions Slot (Footer Buttons) --}}
        <x-slot:actions>
            <x-mary-button label="Batal" @click="$wire.show = false" />
            <x-mary-button
                label="{{ $this->validationResult['valid'] ? '✓ Terapkan Perubahan' : '⚠ Perbaiki Error Dulu' }}"
                wire:click="applySelection" class="btn-primary" :disabled="!$this->validationResult['valid']"
                spinner="applySelection" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Success/Error Toast (using Mary UI toast component if available, or custom) --}}
    <div x-data="{ show: false, message: '', type: 'success' }"
        @show-validation-error.window="show = true; type = 'error'; message = $event.detail.errors[0]; setTimeout(() => show = false, 4000)"
        @show-success.window="show = true; type = 'success'; message = $event.detail.message; setTimeout(() => show = false, 3000)"
        x-show="show" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2" class="fixed bottom-4 right-4 z-[60] max-w-md"
        style="display: none;">
        <div :class="type === 'success' ? 'bg-green-500' : 'bg-red-500'"
            class="text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-3">
            <svg x-show="type === 'success'" class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd"></path>
            </svg>
            <svg x-show="type === 'error'" class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                    clip-rule="evenodd"></path>
            </svg>
            <span x-text="message" class="flex-1"></span>
        </div>
    </div>

    {{-- Custom Scrollbar Styles --}}
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.5);
        }

        .dark .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
    </style>
</div>