<div>
    {{-- Button to trigger modal --}}
    <button wire:click="openModal" type="button"
        class="px-4 py-2 text-sm font-medium {{ $weights['isAdjusted'] ? 'text-amber-700 dark:text-amber-300 bg-amber-100 dark:bg-amber-900/30 border-amber-400 dark:border-amber-600' : 'text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600' }} border rounded-lg hover:bg-opacity-80 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
        <span class="flex items-center gap-2">
            📊 Bobot Standar: {{ $weights['name1'] }} {{ $weights['weight1'] }}% | {{ $weights['name2'] }} {{ $weights['weight2'] }}%
            @if ($weights['isAdjusted'])
                <span class="text-xs">(disesuaikan)</span>
            @endif
        </span>
    </button>

    {{-- Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900/75 dark:bg-gray-900/90 transition-opacity"
                wire:click="closeModal">
            </div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg bg-white dark:bg-gray-800 rounded-lg shadow-xl">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Edit Bobot Kategori</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total bobot harus 100%</p>
                    </div>
                    <div class="px-6 py-4 space-y-4">
                        {{-- Category 1 Weight --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Bobot {{ $categoryName1 }} (%):
                            </label>
                            <input type="number" wire:model.live="editingWeight1" min="0" max="100"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Nilai asli: {{ $originalWeight1 }}%
                            </p>
                        </div>

                        {{-- Category 2 Weight --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Bobot {{ $categoryName2 }} (%):
                            </label>
                            <input type="number" wire:model.live="editingWeight2" min="0" max="100"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Nilai asli: {{ $originalWeight2 }}%
                            </p>
                        </div>

                        {{-- Total Display --}}
                        <div
                            class="p-4 rounded-lg {{ $editingWeight1 + $editingWeight2 === 100 ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }}">
                            <div class="flex items-center justify-between">
                                <span
                                    class="font-semibold {{ $editingWeight1 + $editingWeight2 === 100 ? 'text-green-800 dark:text-green-300' : 'text-red-800 dark:text-red-300' }}">
                                    Total:
                                </span>
                                <span
                                    class="font-bold text-lg {{ $editingWeight1 + $editingWeight2 === 100 ? 'text-green-800 dark:text-green-300' : 'text-red-800 dark:text-red-300' }}">
                                    {{ $editingWeight1 + $editingWeight2 }}%
                                </span>
                            </div>
                            @if ($editingWeight1 + $editingWeight2 !== 100)
                                <p class="text-sm text-red-700 dark:text-red-400 mt-2">
                                    ⚠️ Total harus 100%
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="px-6 py-4 flex items-center justify-end gap-3">
                        <button wire:click="closeModal" type="button"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600">
                            Batal
                        </button>
                        <button wire:click="saveWeights" type="button"
                            {{ $editingWeight1 + $editingWeight2 !== 100 ? 'disabled' : '' }}
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
