<div x-data="{ 
    weight1: @entangle('editingWeight1'), 
    weight2: @entangle('editingWeight2'),
    originalWeight1: @entangle('originalWeight1'),
    originalWeight2: @entangle('originalWeight2'),
    get total() { return Number(this.weight1) + Number(this.weight2); },
    get isValid() { return this.total === 100; }
}">
    {{-- Button to trigger modal --}}
    <button wire:click="openModal" type="button"
        class="px-4 py-2 text-sm font-medium {{ $weights['isAdjusted'] ? 'text-amber-700 dark:text-amber-300 bg-amber-100 dark:bg-amber-900/30 border-amber-400 dark:border-amber-600' : 'text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600' }} border rounded-lg hover:bg-opacity-80 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
        <span class="flex items-center gap-2">
            📊 Bobot Standar: {{ $weights['name1'] }} {{ $weights['weight1'] }}% | {{ $weights['name2'] }} {{
            $weights['weight2'] }}%
            @if ($weights['isAdjusted'])
            <span class="text-xs">(disesuaikan)</span>
            @endif
        </span>
    </button>

    {{-- Modal --}}
    @if ($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100">

        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-gray-900/75 dark:bg-gray-900/90 transition-opacity" wire:click="closeModal">
        </div>

        {{-- Modal Content --}}
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-lg bg-white dark:bg-gray-800 rounded-lg shadow-xl"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">

                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Edit Bobot Kategori</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total bobot harus 100%</p>
                        </div>
                        <button wire:click="closeModal" type="button"
                            class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition-colors">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Body --}}
                <div class="px-6 py-4 space-y-4">
                    {{-- Category 1 Weight --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Bobot {{ $categoryName1 }}:
                        </label>
                        <div class="relative">
                            <input type="number" x-model.number="weight1" min="0" max="100" step="1"
                                class="w-full px-3 py-2 pr-12 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                placeholder="0">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <span class="text-gray-500 dark:text-gray-400">%</span>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Nilai standar: <span x-text="originalWeight1"></span>%
                        </p>
                    </div>

                    {{-- Category 2 Weight --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Bobot {{ $categoryName2 }}:
                        </label>
                        <div class="relative">
                            <input type="number" x-model.number="weight2" min="0" max="100" step="1"
                                class="w-full px-3 py-2 pr-12 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                placeholder="0">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <span class="text-gray-500 dark:text-gray-400">%</span>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Nilai standar: <span x-text="originalWeight2"></span>%
                        </p>
                    </div>

                    {{-- Quick Presets --}}
                    <div class="pt-2">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Preset Cepat:</p>
                        <div class="flex gap-2">
                            <button @click="weight1 = 50; weight2 = 50" type="button"
                                class="px-3 py-1.5 text-xs bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 rounded-md hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors">
                                50/50
                            </button>
                            <button @click="weight1 = 60; weight2 = 40" type="button"
                                class="px-3 py-1.5 text-xs bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 rounded-md hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors">
                                60/40
                            </button>
                            <button @click="weight1 = 70; weight2 = 30" type="button"
                                class="px-3 py-1.5 text-xs bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 rounded-md hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors">
                                70/30
                            </button>
                            <button @click="weight1 = originalWeight1; weight2 = originalWeight2" type="button"
                                class="px-3 py-1.5 text-xs bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                Reset
                            </button>
                        </div>
                    </div>

                    {{-- Real-time Total Display --}}
                    <div class="p-4 rounded-lg transition-all duration-200"
                        :class="isValid ? 'bg-green-100 dark:bg-green-900/30' : 'bg-amber-100 dark:bg-amber-900/30'">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold transition-colors"
                                :class="isValid ? 'text-green-800 dark:text-green-300' : 'text-amber-800 dark:text-amber-300'">
                                Total Bobot:
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-xl transition-colors"
                                    :class="isValid ? 'text-green-800 dark:text-green-300' : 'text-amber-800 dark:text-amber-300'"
                                    x-text="total + '%'">
                                </span>
                                <svg x-show="isValid" class="w-5 h-5 text-green-600 dark:text-green-400"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div x-show="!isValid" class="mt-2 flex items-start gap-2">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <p class="text-sm text-amber-700 dark:text-amber-400">
                                <span x-show="total < 100">Total masih kurang <strong
                                        x-text="100 - total"></strong>%</span>
                                <span x-show="total > 100">Total kelebihan <strong
                                        x-text="total - 100"></strong>%</span>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div
                    class="px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-200 dark:border-gray-700">
                    <button wire:click="closeModal" type="button"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all">
                        Batal
                    </button>
                    <button wire:click="saveWeights" type="button" :disabled="!isValid"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                        <span wire:loading.remove wire:target="saveWeights">Simpan Perubahan</span>
                        <span wire:loading wire:target="saveWeights" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Menyimpan...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>