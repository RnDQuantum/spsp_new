{{-- Selective Aspects Modal - Always has root element for Livewire --}}
<div>
    @if ($show)
        <div x-data="{ show: @entangle('show') }" x-show="show" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-gray-900/75 dark:bg-gray-900/90 transition-opacity" aria-hidden="true"
            wire:click="close"></div>

        {{-- Modal Content --}}
        <div class="flex min-h-full items-center justify-center p-4">
            <div @click.away="$wire.close()"
                class="relative w-full max-w-4xl bg-white dark:bg-gray-800 rounded-lg shadow-xl transform transition-all">

                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Pilih Aspek & Sub-Aspek {{ ucfirst($categoryCode) }} untuk Analisis
                        </h3>
                        <button wire:click="close" type="button"
                            class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    {{-- Bulk Actions --}}
                    <div class="flex items-center gap-3 mt-3">
                        <button wire:click="selectAll" type="button"
                            class="px-3 py-1 text-sm bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 rounded hover:bg-blue-200 dark:hover:bg-blue-900/50">
                            ✓ Select All
                        </button>
                        <button wire:click="deselectAll" type="button"
                            class="px-3 py-1 text-sm bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded hover:bg-gray-200 dark:hover:bg-gray-600">
                            ✗ Deselect All
                        </button>
                    </div>
                </div>

                {{-- Body --}}
                <div class="px-6 py-4 max-h-[60vh] overflow-y-auto">
                    @foreach ($this->templateAspects as $aspect)
                        <div class="mb-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                            {{-- Aspect Row --}}
                            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50">
                                {{-- Checkbox --}}
                                <input type="checkbox" wire:model.live="selectedAspects.{{ $aspect->code }}"
                                    class="w-5 h-5 rounded text-blue-600 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600">

                                {{-- Expand/Collapse Button (for Potensi) --}}
                                @if ($categoryCode === 'potensi' && $aspect->subAspects->count() > 0)
                                    <button wire:click="toggleExpand('{{ $aspect->code }}')" type="button"
                                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                        @if (($expandedAspects[$aspect->code] ?? false))
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        @endif
                                    </button>
                                @endif

                                {{-- Aspect Name --}}
                                <div class="flex-1">
                                    <span
                                        class="font-semibold text-gray-900 dark:text-gray-100 {{ ! ($selectedAspects[$aspect->code] ?? true) ? 'line-through opacity-50' : '' }}">
                                        {{ $aspect->name }}
                                    </span>
                                    @if ($aspect->subAspects->count() > 0)
                                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">
                                            ({{ $aspect->subAspects->count() }} sub-aspek)
                                        </span>
                                    @endif
                                </div>

                                {{-- Weight Input --}}
                                <div class="flex items-center gap-2">
                                    <input type="number" wire:model.blur="aspectWeights.{{ $aspect->code }}"
                                        min="0" max="100"
                                        class="w-20 px-2 py-1 text-center border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 {{ ! ($selectedAspects[$aspect->code] ?? true) ? 'opacity-50' : '' }}"
                                        {{ ! ($selectedAspects[$aspect->code] ?? true) ? 'disabled' : '' }}>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">%</span>
                                </div>
                            </div>

                            {{-- Sub-Aspects (for Potensi) --}}
                            @if ($categoryCode === 'potensi' && $aspect->subAspects->count() > 0 && ($expandedAspects[$aspect->code] ?? false))
                                <div class="p-3 pl-12 space-y-2 bg-white dark:bg-gray-800">
                                    @foreach ($aspect->subAspects as $subAspect)
                                        <div class="flex items-center gap-3">
                                            {{-- Sub-Aspect Checkbox --}}
                                            <input type="checkbox"
                                                wire:model.live="selectedSubAspects.{{ $aspect->code }}.{{ $subAspect->code }}"
                                                class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600"
                                                {{ ! ($selectedAspects[$aspect->code] ?? true) ? 'disabled' : '' }}>

                                            {{-- Sub-Aspect Name --}}
                                            <div class="flex-1">
                                                <span
                                                    class="text-sm text-gray-700 dark:text-gray-300 {{ ! ($selectedAspects[$aspect->code] ?? true) || ! ($selectedSubAspects[$aspect->code][$subAspect->code] ?? true) ? 'line-through opacity-50' : '' }}">
                                                    {{ $subAspect->name }}
                                                </span>
                                            </div>

                                            {{-- Standard Rating Display --}}
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                Std: {{ $subAspect->standard_rating }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Validation Section --}}
                <div class="px-6 py-3 bg-gray-50 dark:bg-gray-700/50 border-t border-b border-gray-200 dark:border-gray-700">
                    <div class="text-sm space-y-1">
                        @if ($this->validationResult['valid'])
                            {{-- Valid State --}}
                            <div class="flex items-center gap-2 text-green-700 dark:text-green-400">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                <span class="font-semibold">Validasi Berhasil!</span>
                            </div>
                            <div class="ml-7 space-y-1 text-gray-600 dark:text-gray-400">
                                <div>✓ Total Bobot: {{ $this->totalWeight }}% (valid)</div>
                                <div>✓ Aspek Aktif: {{ $this->activeAspectsCount }}/{{ $this->totalAspectsCount }}
                                    (minimal 3 - valid)</div>
                                @if ($categoryCode === 'potensi')
                                    <div>✓ Sub-Aspek: Semua aspek punya min 1 sub-aspek aktif</div>
                                @endif
                            </div>
                        @else
                            {{-- Invalid State --}}
                            <div class="flex items-start gap-2 text-red-700 dark:text-red-400">
                                <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                <div class="flex-1">
                                    <div class="font-semibold mb-1">Validasi Gagal:</div>
                                    <ul class="list-disc list-inside space-y-1 text-sm">
                                        @foreach ($this->validationResult['errors'] as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 flex items-center justify-end gap-3">
                    <button wire:click="close" type="button"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600">
                        Batal
                    </button>
                    <button wire:click="applySelection" type="button"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        {{ ! $this->validationResult['valid'] ? 'disabled' : '' }}>
                        Terapkan Perubahan
                    </button>
                </div>
            </div>
        </div>
        </div>
    @endif
</div>
