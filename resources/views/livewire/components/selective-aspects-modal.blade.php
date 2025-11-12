{{-- Selective Aspects Modal - UX Optimized: Checkbox-only interaction --}}
<div>
    {{-- Modal Container with Alpine.js for instant display --}}
    <div x-data="selectiveAspectsModal()" x-on:open-selection-modal-instant.window="openModal()"
        x-on:modal-loading.window="loading = true" x-on:modal-ready.window="loading = false; dataReady = true"
        x-on:modal-closed.window="handleClose()" x-on:close-modal.window="isOpen = false; handleClose()"
        x-on:show-validation-error.window="showValidationError($event.detail.errors)"
        x-on:show-success.window="showSuccessMessage($event.detail.message)">
        {{-- Modal Wrapper --}}
        <div x-show="isOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" aria-labelledby="modal-title"
            role="dialog" aria-modal="true">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-gray-900/75 dark:bg-gray-900/90 transition-opacity" aria-hidden="true"
                @click="closeModal()"></div>

            {{-- Modal Content Container --}}
            <div class="flex min-h-full items-center justify-center p-4">
                <div @click.away="closeModal()" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative w-full max-w-4xl bg-white dark:bg-gray-800 rounded-lg shadow-xl transform transition-all">
                    {{-- Loading State --}}
                    <div x-show="loading && !dataReady" class="p-12">
                        <div class="flex flex-col items-center justify-center space-y-4">
                            <svg class="animate-spin h-12 w-12 text-blue-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <p class="text-gray-600 dark:text-gray-400">Memuat data aspek dan sub-aspek...</p>
                        </div>
                    </div>

                    {{-- Actual Modal Content --}}
                    <div x-show="!loading || dataReady" style="display: none;">
                        @if ($dataLoaded)
                        {{-- Header --}}
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    Pilih Aspek & Sub-Aspek {{ ucfirst($categoryCode) }} untuk Analisis
                                </h3>
                                <button @click="closeModal()" type="button"
                                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            {{-- Bulk Actions --}}
                            <div class="flex items-center gap-3 mt-3">
                                <button wire:click="selectAll" type="button"
                                    class="px-3 py-1.5 text-sm bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 rounded-md hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors">
                                    ✓ Pilih Semua
                                </button>
                                <button wire:click="deselectAll" type="button"
                                    class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                    ✗ Hapus Semua
                                </button>
                                <button wire:click="autoDistributeWeights" type="button"
                                    class="px-3 py-1.5 text-sm bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300 rounded-md hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors">
                                    ⚖ Distribusi Bobot Otomatis
                                </button>
                            </div>
                        </div>

                        {{-- Body with Scrollable Content --}}
                        <div class="px-6 py-4 max-h-[60vh] overflow-y-auto custom-scrollbar">
                            @forelse ($this->templateAspects as $aspect)
                            <div
                                class="mb-4 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                                {{-- Aspect Row - OPTIMIZED: Removed cursor-pointer and for attribute --}}
                                <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50">
                                    {{-- Checkbox - Only clickable area --}}
                                    <input type="checkbox" wire:model.live="selectedAspects.{{ $aspect->code }}"
                                        id="aspect_{{ $aspect->code }}"
                                        class="w-5 h-5 rounded text-blue-600 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 transition-all cursor-pointer">

                                    {{-- Expand/Collapse Button (for Potensi) --}}
                                    @if ($categoryCode === 'potensi' && $aspect->subAspects &&
                                    $aspect->subAspects->count() > 0)
                                    <button wire:click="toggleExpand('{{ $aspect->code }}')" type="button"
                                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors"
                                        aria-label="Toggle sub-aspects">
                                        <svg class="w-5 h-5 transform transition-transform duration-200 {{ $expandedAspects[$aspect->code] ?? false ? 'rotate-90' : '' }}"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </button>
                                    @endif

                                    {{-- Aspect Name - REMOVED: for attribute and cursor-pointer --}}
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
                                        <input type="number" wire:model.blur="aspectWeights.{{ $aspect->code }}" min="0"
                                            max="100"
                                            class="w-20 px-2 py-1 text-center border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all {{ !($selectedAspects[$aspect->code] ?? true) ? 'opacity-50 cursor-not-allowed' : '' }}"
                                            {{ !($selectedAspects[$aspect->code] ?? true) ? 'disabled' : '' }}>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">%</span>
                                    </div>
                                </div>

                                {{-- Sub-Aspects (for Potensi) --}}
                                @if ($categoryCode === 'potensi' && $aspect->subAspects && $aspect->subAspects->count()
                                > 0)
                                <div x-data="{ show: {{ $expandedAspects[$aspect->code] ?? false ? 'true' : 'false' }} }"
                                    x-show="show" wire:ignore.self x-collapse
                                    class="border-t border-gray-200 dark:border-gray-700">
                                    @if ($expandedAspects[$aspect->code] ?? false)
                                    <div class="p-3 pl-12 space-y-2 bg-white dark:bg-gray-800">
                                        @foreach ($aspect->subAspects as $subAspect)
                                        <div
                                            class="flex items-center gap-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-700/30 rounded transition-colors">
                                            {{-- Sub-Aspect Checkbox - Only clickable area --}}
                                            <input type="checkbox"
                                                wire:model.live="selectedSubAspects.{{ $aspect->code }}.{{ $subAspect->code }}"
                                                id="subaspect_{{ $aspect->code }}_{{ $subAspect->code }}"
                                                class="w-4 h-4 rounded text-blue-600 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 transition-all cursor-pointer"
                                                {{ !($selectedAspects[$aspect->code] ?? true) ? 'disabled' : '' }}>

                                            {{-- Sub-Aspect Name - REMOVED: for attribute and cursor-pointer --}}
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
                                @endif
                            </div>
                            @empty
                            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                Tidak ada aspek ditemukan untuk kategori ini.
                            </div>
                            @endforelse
                        </div>

                        {{-- Validation Section --}}
                        <div
                            class="px-6 py-3 bg-gray-50 dark:bg-gray-700/50 border-t border-b border-gray-200 dark:border-gray-700">
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
                                    <div class="flex items-center gap-2">
                                        <span class="text-green-600">✓</span>
                                        Total Bobot: {{ $this->totalWeight }}%
                                        <span class="text-xs text-green-600 dark:text-green-400">(Valid -
                                            100%)</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-green-600">✓</span>
                                        Aspek Aktif:
                                        {{ $this->activeAspectsCount }}/{{ $this->totalAspectsCount }}
                                        <span class="text-xs text-green-600 dark:text-green-400">(Minimal
                                            3)</span>
                                    </div>
                                    @if ($categoryCode === 'potensi')
                                    <div class="flex items-center gap-2">
                                        <span class="text-green-600">✓</span>
                                        Sub-Aspek: Semua aspek aktif memiliki minimal 1 sub-aspek
                                    </div>
                                    @endif
                                </div>
                                @else
                                {{-- Invalid State with Alert --}}
                                <div x-data="{ showErrors: true }"
                                    class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-3">
                                    <div class="flex items-start gap-2 text-red-700 dark:text-red-400">
                                        <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        <div class="flex-1">
                                            <div class="font-semibold mb-1">Perbaiki kesalahan berikut:</div>
                                            <ul class="list-disc list-inside space-y-1 text-sm">
                                                @foreach ($this->validationResult['errors'] as $error)
                                                <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="px-6 py-4 flex items-center justify-end">

                            <div class="flex items-center gap-3">
                                <button @click="closeModal()" type="button"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all">
                                    Batal
                                </button>
                                <button wire:click="applySelection" wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-wait" type="button"
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all {{ !$this->validationResult['valid'] ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    {{ !$this->validationResult['valid'] ? 'disabled' : '' }}>
                                    <span wire:loading.remove wire:target="applySelection">Terapkan
                                        Perubahan</span>
                                    <span wire:loading wire:target="applySelection">Menyimpan...</span>
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Toast Notifications --}}
        <div x-show="toast.show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2" class="fixed bottom-4 right-4 z-[60]"
            style="display: none;">
            <div :class="toast.type === 'success' ? 'bg-green-500' : 'bg-red-500'"
                class="text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-3">
                <svg x-show="toast.type === 'success'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd"></path>
                </svg>
                <svg x-show="toast.type === 'error'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd"></path>
                </svg>
                <span x-text="toast.message"></span>
            </div>
        </div>
    </div>

    {{-- Alpine.js Component Script --}}
    <script>
        function selectiveAspectsModal() {
            return {
                isOpen: false,
                loading: false,
                dataReady: false,
                toast: {
                    show: false,
                    type: 'success',
                    message: ''
                },

                init() {
                    // Listen for close-modal event
                    this.$watch('$wire.show', (value) => {
                        if (!value) {
                            this.isOpen = false;
                        }
                    });
                },

                openModal() {
                    this.isOpen = true;
                    this.loading = true;
                    this.dataReady = false;
                },

                closeModal() {
                    this.isOpen = false;
                    this.$wire.close();
                },

                handleClose() {
                    setTimeout(() => {
                        this.loading = false;
                        this.dataReady = false;
                    }, 300);
                },

                showValidationError(errors) {
                    this.toast.type = 'error';
                    this.toast.message = errors[0] || 'Validasi gagal';
                    this.toast.show = true;
                    setTimeout(() => {
                        this.toast.show = false;
                    }, 3000);
                },

                showSuccessMessage(message) {
                    this.toast.type = 'success';
                    this.toast.message = message;
                    this.toast.show = true;
                    // Close modal after showing success
                    setTimeout(() => {
                        this.toast.show = false;
                        this.isOpen = false; // Close modal
                    }, 2000);
                }
            }
        }
    </script>

    {{-- Custom Styles for Scrollbar --}}
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