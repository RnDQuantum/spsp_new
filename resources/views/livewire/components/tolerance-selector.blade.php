<div>
    <!-- Tolerance Selector Section - DARK MODE READY -->
    <div class="p-4 bg-white dark:bg-gray-800">
        <div class="flex items-center justify-between max-w-6xl mx-auto">
            <!-- Tolerance Dropdown -->
            <div class="flex items-center gap-4">
                <label class="font-semibold text-gray-700 dark:text-gray-300">
                    🔍 Toleransi Analisis:
                </label>
                <select wire:model.live="tolerancePercentage"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
                           focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 
                           focus:outline-none 
                           bg-white dark:bg-gray-700 
                           text-gray-700 dark:text-gray-200">
                    @foreach ($toleranceOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>

                <!-- Loading Indicator -->
                <div wire:loading wire:target="tolerancePercentage" class="text-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        <svg class="inline w-4 h-4 animate-spin text-blue-500 dark:text-blue-400"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Memperbarui data...
                    </span>
                </div>
            </div>

            <!-- Summary Statistics -->
            @if ($showSummary && $totalCount > 0)
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <span class="font-semibold text-green-600 dark:text-green-400">
                        {{ $passingCount }} dari {{ $totalCount }} aspek
                    </span>
                    <span class="font-semibold text-gray-600 dark:text-gray-400">
                        ({{ $this->passingPercentage }}%)
                    </span>
                    memenuhi standard
                </div>
            @endif
        </div>

        <!-- Warning Message -->
        <div class="mt-2 text-center">
            <p class="text-xs text-gray-600 dark:text-gray-400">
                <span class="inline-block w-3 h-3 bg-yellow-400 dark:bg-yellow-500 rounded-full mr-1"></span>
                <strong class="text-gray-800 dark:text-gray-200">Catatan:</strong>
                Toleransi tidak mempengaruhi Data asli.
            </p>
        </div>
    </div>
</div>
