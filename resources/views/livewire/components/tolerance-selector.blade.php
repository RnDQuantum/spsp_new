<div>
    <!-- Tolerance Selector Section -->
    <div class="p-4 bg-yellow-50 border-b-2 border-yellow-300">
        <div class="flex items-center justify-between max-w-6xl mx-auto">
            <!-- Tolerance Dropdown -->
            <div class="flex items-center gap-4">
                <label class="font-semibold text-gray-700">
                    🔍 Toleransi Analisis:
                </label>
                <select wire:model.live="tolerancePercentage"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none text-gray-600">
                    @foreach ($toleranceOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Summary Statistics -->
            @if ($showSummary && $totalCount > 0)
                <div class="text-sm text-gray-600">
                    <span class="font-semibold text-green-600">
                        {{ $passingCount }} dari {{ $totalCount }} aspek
                    </span>
                    <span class="font-semibold text-gray-600">
                        ({{ $this->passingPercentage }}%)
                    </span>
                    memenuhi standard
                </div>
            @endif
        </div>

        <!-- Warning Message -->
        <div class="mt-2 text-center">
            <p class="text-xs text-gray-600">
                <span class="inline-block w-3 h-3 bg-yellow-400 rounded-full mr-1"></span>
                <strong>Catatan:</strong> Toleransi tidak mempengaruhi Data asli.
            </p>
        </div>

        <!-- Loading Indicator -->
        <div wire:loading wire:target="tolerancePercentage" class="mt-2 text-center">
            <span class="text-sm text-gray-600">
                <svg class="inline w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
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
</div>
