<div class="max-w-7xl mx-auto">
    <div class="bg-white dark:bg-gray-800 p-6 rounded shadow text-gray-900 dark:text-gray-100">
        {{-- Filter Section --}}
        <div class="mb-6 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-300 dark:border-gray-600">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Event Filter --}}
                <div>
                    @livewire('components.event-selector', ['showLabel' => true])
                </div>

                {{-- Position Filter --}}
                <div>
                    @livewire('components.position-selector', ['showLabel' => true])
                </div>
            </div>

            {{-- Template Info (Display Only) --}}
            @if ($selectedTemplate)
            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600">
                <div class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                    <span class="font-semibold">📄 Template:</span>
                    <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $selectedTemplate->name }}
                    </div>
                </div>
                @if ($selectedEvent)
                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 mt-2">
                    <span class="font-semibold">🏢 Institusi:</span>
                    <span class="text-gray-900 dark:text-gray-100">{{ $selectedEvent->institution->name ?? 'N/A'
                        }}</span>
                </div>
                @endif
            </div>

            {{-- PHASE 2C: Control Buttons --}}
            <div class="mt-4 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    {{-- Category Weight Display --}}
                    @php
                        $dynamicService = app(\App\Services\DynamicStandardService::class);
                        $potensiWeight = $dynamicService->getCategoryWeight($selectedTemplate->id, 'potensi');
                        $kompetensiWeight = $dynamicService->getCategoryWeight($selectedTemplate->id, 'kompetensi');
                        $potensiCategory = \App\Models\CategoryType::where('template_id', $selectedTemplate->id)
                            ->where('code', 'potensi')
                            ->first();
                        $kompetensiCategory = \App\Models\CategoryType::where('template_id', $selectedTemplate->id)
                            ->where('code', 'kompetensi')
                            ->first();
                        $potensiOriginal = $potensiCategory?->weight_percentage ?? 50;
                        $kompetensiOriginal = $kompetensiCategory?->weight_percentage ?? 50;
                        $isWeightAdjusted =
                            $potensiWeight !== $potensiOriginal || $kompetensiWeight !== $kompetensiOriginal;
                    @endphp
                    <button wire:click="openEditCategoryWeights" type="button"
                        class="px-4 py-2 text-sm font-medium {{ $isWeightAdjusted ? 'text-amber-700 dark:text-amber-300 bg-amber-100 dark:bg-amber-900/30 border-amber-400 dark:border-amber-600' : 'text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600' }} border rounded-lg hover:bg-opacity-80 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                        <span class="flex items-center gap-2">
                            📊 Bobot Standar: Potensi {{ $potensiWeight }}% | Kompetensi {{ $kompetensiWeight }}%
                            @if ($isWeightAdjusted)
                                <span class="text-xs">(disesuaikan)</span>
                            @endif
                        </span>
                    </button>
                </div>

                <div class="flex items-center gap-3">
                    {{-- Optimized button with instant modal display --}}
                    <button x-data @click="
                                $dispatch('open-selection-modal-instant');
                                $wire.openSelectionModal();
                            " type="button"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled" wire:loading.class="opacity-75">
                        <span class="flex items-center gap-2">
                            <span wire:loading.remove wire:target="openSelectionModal">🎯 Pilih Aspek
                                Kompetensi</span>
                            <span wire:loading wire:target="openSelectionModal" class="flex items-center">
                                <svg class="animate-spin h-4 w-4 text-white mr-1" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Memuat...
                            </span>
                        </span>
                    </button>

                    <button wire:click="resetAdjustments" type="button"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all">
                        ↻ Reset ke Default
                    </button>
                </div>

                {{-- Adjustment Indicator --}}
                @php
                $hasAdjustments = app(\App\Services\DynamicStandardService::class)->hasCategoryAdjustments(
                $selectedTemplate->id,
                'kompetensi'
                );
                @endphp
                @if ($hasAdjustments)
                <div class="flex items-center gap-2 text-sm">
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                        Standar Disesuaikan
                    </span>
                </div>
                @endif
            </div>
            @endif
        </div>

        {{-- Header Title --}}
        <div
            class="text-center font-bold uppercase mb-4 text-sm bg-blue-200 dark:bg-blue-900 py-2 border border-black dark:border-gray-600">
            STANDAR PEMETAAN KOMPETENSI INDIVIDU "STATIC PRIBADI SPIDER PLOT"
        </div>

        {{-- Empty State --}}
        @if (!$selectedEvent || !$selectedTemplate)
        <div class="text-center py-12">
            <div class="text-gray-500 dark:text-gray-300 text-lg mb-2">Tidak ada data untuk ditampilkan</div>
            <div class="text-gray-400 dark:text-gray-400 text-sm">Silakan pilih event dan jabatan terlebih dahulu.
            </div>
        </div>
        @else
        <table class="w-full border border-black dark:border-gray-600 text-xs mb-4">
            <tr>
                <td
                    class="border border-black dark:border-gray-600 px-2 py-1 bg-blue-100 dark:bg-blue-900 font-semibold w-1/5">
                    Perusahaan/Lembaga</td>
                <td class="border border-black dark:border-gray-600 px-2 py-1 w-2/5">
                    {{ strtoupper($selectedEvent->institution->name ?? 'N/A') }}</td>
                <td
                    class="border border-black dark:border-gray-600 px-2 py-1 bg-blue-100 dark:bg-blue-900 font-semibold w-1/6">
                </td>
                <td class="border border-black dark:border-gray-600 px-2 py-1 w-1/6"></td>
            </tr>
            <tr>
                <td
                    class="border border-black dark:border-gray-600 px-2 py-1 bg-blue-100 dark:bg-blue-900 font-semibold">
                    Standard Penilaian</td>
                <td class="border border-black dark:border-gray-600 px-2 py-1">{{ $selectedTemplate->name }}</td>
                <td
                    class="border border-black dark:border-gray-600 px-2 py-1 bg-blue-100 dark:bg-blue-900 font-semibold">
                    Kode:</td>
                <td class="border border-black dark:border-gray-600 px-2 py-1 text-center">
                    {{ $selectedTemplate->code }}</td>
            </tr>
        </table>
        @endif

        {{-- Tabel Detail dengan Sub-Aspects --}}
        @if (count($categoryData) > 0)
        <div class="mt-4 mb-4">
            <table class="w-full border border-black dark:border-gray-600 text-xs">
                <thead>
                    <tr class="bg-blue-100 dark:bg-blue-900">
                        <th class="border border-black dark:border-gray-600 px-2 py-2 w-12">No.</th>
                        <th class="border border-black dark:border-gray-600 px-2 py-2">ATRIBUT</th>
                        <th class="border border-black dark:border-gray-600 px-2 py-2 w-24">NILAI STANDAR</th>
                        <th class="border border-black dark:border-gray-600 px-2 py-2 w-24">JUMLAH ATRIBUT</th>
                        <th class="border border-black dark:border-gray-600 px-2 py-2 w-20">BOBOT (%)</th>
                        <th class="border border-black dark:border-gray-600 px-2 py-2 w-24">RATING Rata-Rata</th>
                        <th class="border border-black dark:border-gray-600 px-2 py-2 w-20">SKOR</th>
                    </tr>
                </thead>
                <tbody>
                    @php $romanNumerals = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII',
                    'XIII', 'XIV', 'XV', 'XVI', 'XVII', 'XVIII', 'XIX', 'XX']; @endphp

                    @foreach ($categoryData as $catIndex => $category)
                    {{-- Aspects within Kompetensi Category --}}
                    @foreach ($category['aspects'] as $aspectIndex => $aspect)
                    <tr>
                        <td class="border border-black dark:border-gray-600 px-2 py-2 text-center">
                            {{ $aspectIndex + 1 }}</td>
                        <td class="border border-black dark:border-gray-600 px-2 py-2 pl-4">
                            <p class="font-bold mb-1">{{ $aspect['name'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $aspect['description'] }}
                            </p>
                        </td>
                        {{-- PHASE 2C: Clickable Rating Cell with Visual Indicator --}}
                        <td wire:click="openEditAspectRating('{{ $aspect['code'] }}', {{ $aspect['standard_rating'] }})"
                            class="border border-black dark:border-gray-600 px-2 py-2 text-center cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 {{ $aspect['is_rating_adjusted'] ?? false ? 'bg-amber-100 dark:bg-amber-900/30' : '' }}"
                            title="{{ $aspect['is_rating_adjusted'] ?? false ? 'Disesuaikan dari ' . $aspect['original_rating'] . ' - Klik untuk edit' : 'Klik untuk edit' }}">
                            {{ number_format($aspect['standard_rating'], 2) }}
                            @if ($aspect['is_rating_adjusted'] ?? false)
                            <span class="text-amber-600 dark:text-amber-400"></span>
                            @endif
                        </td>
                        <td class="border border-black dark:border-gray-600 px-2 py-2 text-center">
                            {{ $aspect['attribute_count'] }}</td>
                        {{-- PHASE 2C: Weight Cell with Visual Indicator (edit via modal only) --}}
                        <td class="border border-black dark:border-gray-600 px-2 py-2 text-center {{ $aspect['is_weight_adjusted'] ?? false ? 'bg-amber-100 dark:bg-amber-900/30' : '' }}"
                            title="{{ $aspect['is_weight_adjusted'] ?? false ? 'Disesuaikan dari ' . $aspect['original_weight'] . '% - Edit via modal Pilih Aspek' : '' }}">
                            {{ $aspect['weight_percentage'] }}
                            @if ($aspect['is_weight_adjusted'] ?? false)
                            <span class="text-amber-600 dark:text-amber-400"></span>
                            @endif
                        </td>
                        <td class="border border-black dark:border-gray-600 px-2 py-2 text-center">
                            {{ number_format($aspect['average_rating'], 2) }}</td>
                        <td class="border border-black dark:border-gray-600 px-2 py-2 text-center">
                            {{ number_format($aspect['score'], 2) }}</td>
                    </tr>
                    @endforeach
                    @endforeach

                    {{-- TOTAL --}}
                    <tr class="bg-blue-100 dark:bg-blue-900 font-bold">
                        <td class="border border-black dark:border-gray-600 px-2 py-2 text-center" colspan="2">
                            JUMLAH</td>
                        <td class="border border-black dark:border-gray-600 px-2 py-2 text-center">
                            {{ number_format($totals['total_standard_rating_sum'], 2) }}</td>
                        <td class="border border-black dark:border-gray-600 px-2 py-2 text-center">
                            {{ $totals['total_aspects'] }}</td>
                        <td class="border border-black dark:border-gray-600 px-2 py-2 text-center">
                            {{ $totals['total_weight'] }}</td>
                        <td class="border border-black dark:border-gray-600 px-2 py-2 text-center">
                            {{ number_format($totals['total_rating_sum'], 2) }}</td>
                        <td class="border border-black dark:border-gray-600 px-2 py-2 text-center">
                            {{ number_format($totals['total_score'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif

        {{-- CHART SATU - Rating --}}
        @if (count($chartData['labels']) > 0)
        <div class="border border-black dark:border-gray-600 mb-6 mt-6 p-4 bg-gray-50 dark:bg-gray-700" wire:ignore
            id="chart-rating-{{ $chartId }}">
            <div class="text-center text-xs font-bold mb-4">
                Gambar Rating Standar Atribut Managerial Kompetensi Mapping Static Pribadi Spider Plot
            </div>
            <div class="flex justify-center">
                <canvas id="chartRating-{{ $chartId }}" style="max-width:600px; max-height:400px;"></canvas>
            </div>
        </div>

        {{-- Tabel Mapping Summary --}}
        <div
            class="mb-2 mt-6 font-bold text-xs bg-blue-100 dark:bg-blue-900 border border-black dark:border-gray-600 px-2 py-2 text-center">
            ATRIBUT MANAGERIAL KOMPETENSI MAPPING
        </div>
        <table class="w-full border border-black dark:border-gray-600 text-xs mb-4">
            <thead>
                <tr class="bg-blue-100 dark:bg-blue-900">
                    <th class="border border-black dark:border-gray-600 px-2 py-2 w-12">No.</th>
                    <th class="border border-black dark:border-gray-600 px-2 py-2">ATRIBUT</th>
                    <th class="border border-black dark:border-gray-600 px-2 py-2 w-24">JUMLAH ATRIBUT</th>
                    <th class="border border-black dark:border-gray-600 px-2 py-2 w-20">BOBOT (%)</th>
                    <th class="border border-black dark:border-gray-600 px-2 py-2 w-24">RATING Rata-Rata</th>
                    <th class="border border-black dark:border-gray-600 px-2 py-2 w-20">SKOR</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categoryData as $catIndex => $category)
                @foreach ($category['aspects'] as $aspectIndex => $aspect)
                <tr>
                    <td class="border border-black dark:border-gray-600 px-2 py-1 text-center">
                        {{ $romanNumerals[$aspectIndex] ?? $aspectIndex + 1 }}</td>
                    <td class="border border-black dark:border-gray-600 px-2 py-1">{{ $aspect['name'] }}
                    </td>
                    <td class="border border-black dark:border-gray-600 px-2 py-1 text-center">
                        {{ $aspect['attribute_count'] }}</td>
                    <td class="border border-black dark:border-gray-600 px-2 py-1 text-center">
                        {{ $aspect['weight_percentage'] }}</td>
                    <td class="border border-black dark:border-gray-600 px-2 py-1 text-center">
                        {{ number_format($aspect['standard_rating'], 2) }}</td>
                    <td class="border border-black dark:border-gray-600 px-2 py-1 text-center">
                        {{ number_format($aspect['score'], 2) }}</td>
                </tr>
                @endforeach
                @endforeach
            </tbody>
            <tfoot>
                <tr class="font-bold bg-blue-100 dark:bg-blue-900">
                    <td class="border border-black dark:border-gray-600 px-2 py-2 text-center" colspan="2">Jumlah
                    </td>
                    <td class="border border-black dark:border-gray-600 px-2 py-2 text-center">
                        {{ $totals['total_aspects'] }}</td>
                    <td class="border border-black dark:border-gray-600 px-2 py-2 text-center">
                        {{ $totals['total_weight'] }}</td>
                    <td class="border border-black dark:border-gray-600 px-2 py-2 text-center">
                        {{ number_format($totals['total_rating_sum'], 2) }}</td>
                    <td class="border border-black dark:border-gray-600 px-2 py-2 text-center">
                        {{ number_format($totals['total_score'], 2) }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- CHART DUA - Skor --}}
        <div class="border border-black dark:border-gray-600 mb-8 mt-6 p-4 bg-gray-50 dark:bg-gray-700" wire:ignore
            id="chart-skor-{{ $chartId }}">
            <div class="text-center text-xs font-bold mb-4">
                Gambar Skor Standar Atribut Managerial Kompetensi Mapping Static Pribadi Spider Plot
            </div>
            <div class="flex justify-center">
                <canvas id="chartSkor-{{ $chartId }}" style="max-width:600px; max-height:400px;"></canvas>
            </div>
        </div>
        @endif

        {{-- Footer TTD dan Catatan --}}
        <div class="mt-16 grid grid-cols-2 gap-8 text-xs">
            <div>
                <div class="mb-1">Menyetujui,</div>
                <div class="font-bold mb-16">{{ strtoupper($selectedEvent->institution->name ?? 'N/A') }}</div>
                <div class="border-b border-black dark:border-gray-400 w-3/4 mb-1"></div>
                <div class="w-3/4">................................................</div>
            </div>
            <div class="text-right">
                <div class="mb-1">Surabaya, {{ now()->format('d F Y') }}</div>
                <div class="mb-1">Mengetahui,</div>
                <div class="font-bold mb-16">PT. Quantum HRM Internasional</div>
                <div class="font-bold underline mb-1">Prof. Dr. Pribadiyono, MS</div>
                <div class="text-xs">Pemegang Hak Cipta Haki No. 027762 - 10 Maret 2004</div>
            </div>
        </div>
    </div>

    {{-- PHASE 2C: Include SelectiveAspectsModal Component --}}
    @livewire('components.selective-aspects-modal')

    {{-- PHASE 2C: Inline Edit Modals --}}

    {{-- Edit Category Weights Modal (Potensi + Kompetensi) --}}
    @if ($showEditCategoryWeightsModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/75 dark:bg-gray-900/90 transition-opacity"
            wire:click="closeCategoryWeightsModal">
        </div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-lg bg-white dark:bg-gray-800 rounded-lg shadow-xl">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Edit Bobot Kategori</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total bobot harus 100%</p>
                </div>
                <div class="px-6 py-4 space-y-4">
                    {{-- Potensi Weight --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Bobot Potensi (%):
                        </label>
                        <input type="number" wire:model.live="editingPotensiWeight" min="0" max="100"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Nilai asli: {{ $originalPotensiWeight }}%
                        </p>
                    </div>

                    {{-- Kompetensi Weight --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Bobot Kompetensi (%):
                        </label>
                        <input type="number" wire:model.live="editingKompetensiWeight" min="0" max="100"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Nilai asli: {{ $originalKompetensiWeight }}%
                        </p>
                    </div>

                    {{-- Total Display --}}
                    <div
                        class="p-4 rounded-lg {{ $editingPotensiWeight + $editingKompetensiWeight === 100 ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }}">
                        <div class="flex items-center justify-between">
                            <span
                                class="font-semibold {{ $editingPotensiWeight + $editingKompetensiWeight === 100 ? 'text-green-800 dark:text-green-300' : 'text-red-800 dark:text-red-300' }}">
                                Total:
                            </span>
                            <span
                                class="font-bold text-lg {{ $editingPotensiWeight + $editingKompetensiWeight === 100 ? 'text-green-800 dark:text-green-300' : 'text-red-800 dark:text-red-300' }}">
                                {{ $editingPotensiWeight + $editingKompetensiWeight }}%
                            </span>
                        </div>
                        @if ($editingPotensiWeight + $editingKompetensiWeight !== 100)
                        <p class="text-sm text-red-700 dark:text-red-400 mt-2">
                            ⚠️ Total harus 100%
                        </p>
                        @endif
                    </div>
                </div>
                <div class="px-6 py-4 flex items-center justify-end gap-3">
                    <button wire:click="closeCategoryWeightsModal" type="button"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600">
                        Batal
                    </button>
                    <button wire:click="saveCategoryWeights" type="button"
                        {{ $editingPotensiWeight + $editingKompetensiWeight !== 100 ? 'disabled' : '' }}
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Edit Aspect Rating Modal --}}
    @if ($showEditRatingModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/75 dark:bg-gray-900/90 transition-opacity" wire:click="closeModal">
        </div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-lg shadow-xl">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Edit Rating Aspek</h3>
                </div>
                <div class="px-6 py-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Rating (1-5):
                    </label>
                    <input type="number" wire:model="editingValue" min="1" max="5"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Nilai asli: {{ $editingOriginalValue }}
                    </p>
                </div>
                <div class="px-6 py-4 flex items-center justify-end gap-3">
                    <button wire:click="closeModal" type="button"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600">
                        Batal
                    </button>
                    <button wire:click="saveAspectRating" type="button"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if (count($chartData['labels']) > 0)
    <script>
        (function() {
                if (window['ratingChartSetup_{{ $chartId }}']) return;
                window['ratingChartSetup_{{ $chartId }}'] = true;

                function setupRatingChart() {
                    if (window.ratingChart_{{ $chartId }}) {
                        window.ratingChart_{{ $chartId }}.destroy();
                    }

                    let chartInstance = null;
                    let chartLabels = @js($chartData['labels']);
                    let chartRatings = @js($chartData['ratings']);
                    let templateName = @js($selectedTemplate?->name ?? 'Standard');

                    function getChartColors() {
                        const isDarkMode = document.documentElement.classList.contains('dark');
                        return {
                            borderColor: '#16a34a', // ← UBAH dari '#1e40af' (biru) ke hijau
                            backgroundColor: isDarkMode ? 'rgba(34, 197, 94, 0.2)' :
                            'rgba(22, 163, 74, 0.2)', // ← UBAH dari biru ke hijau
                            pointBackgroundColor: '#16a34a', // ← UBAH dari '#1e40af' (biru) ke hijau
                            pointBorderColor: '#16a34a', // ← UBAH dari '#1e40af' (biru) ke hijau
                            textColor: isDarkMode ? '#e5e7eb' : '#374151',
                            gridColor: isDarkMode ? 'rgba(229, 231, 235, 0.2)' : 'rgba(55, 65, 81, 0.2)'
                        };
                    }

                    function updateChartColors() {
                        const colors = getChartColors();
                        if (window.ratingChart_{{ $chartId }}) {
                            const chart = window.ratingChart_{{ $chartId }};
                            chart.data.datasets[0].borderColor = colors.borderColor;
                            chart.data.datasets[0].backgroundColor = colors.backgroundColor;
                            chart.data.datasets[0].pointBackgroundColor = colors.pointBackgroundColor;
                            chart.data.datasets[0].pointBorderColor = colors.pointBorderColor;
                            chart.options.plugins.legend.labels.color = colors.textColor;
                            chart.options.scales.r.ticks.color = colors.textColor;
                            chart.options.scales.r.pointLabels.color = colors.textColor;
                            chart.options.scales.r.grid.color = colors.gridColor;
                            chart.update('active');
                        }
                    }

                    function initChart() {
                        const canvas = document.getElementById('chartRating-{{ $chartId }}');
                        if (!canvas) return;

                        const ctx = canvas.getContext('2d');
                        const colors = getChartColors();

                        chartInstance = new Chart(ctx, {
                            type: 'radar',
                            data: {
                                labels: chartLabels,
                                datasets: [{
                                    label: templateName,
                                    data: chartRatings,
                                    borderColor: colors.borderColor,
                                    backgroundColor: colors.backgroundColor,
                                    pointBackgroundColor: colors.pointBackgroundColor,
                                    pointBorderColor: colors.pointBorderColor,
                                    pointRadius: 4,
                                    borderWidth: 2
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'bottom',
                                        labels: {
                                            font: {
                                                size: 11
                                            },
                                            color: colors.textColor
                                        }
                                    },
                                    datalabels: {
                                        display: false
                                    }
                                },
                                scales: {
                                    r: {
                                        beginAtZero: true,
                                        min: 0,
                                        max: 5,
                                        ticks: {
                                            stepSize: 0.5,
                                            font: {
                                                size: 10
                                            },
                                            color: colors.textColor,
                                            backdropColor: 'transparent',
                                            showLabelBackdrop: false
                                        },
                                        pointLabels: {
                                            font: {
                                                size: 10
                                            },
                                            color: colors.textColor,
                                            backdropColor: 'transparent',
                                            showLabelBackdrop: false
                                        },
                                        grid: {
                                            color: colors.gridColor
                                        }
                                    }
                                }
                            }
                        });

                        window.ratingChart_{{ $chartId }} = chartInstance;
                    }

                    function waitForLivewire(callback) {
                        if (window.Livewire) callback();
                        else setTimeout(() => waitForLivewire(callback), 100);
                    }

                    waitForLivewire(function() {
                        initChart();

                        // Listen for dark mode changes
                        const observer = new MutationObserver(() => {
                            updateChartColors();
                        });
                        observer.observe(document.documentElement, {
                            attributes: true,
                            attributeFilter: ['class']
                        });

                        Livewire.on('chartDataUpdated', function(data) {
                            let chartData = Array.isArray(data) && data.length > 0 ? data[0] : data;
                            if (window.ratingChart_{{ $chartId }} && chartData) {
                                chartLabels = chartData.labels;
                                chartRatings = chartData.ratings;
                                templateName = chartData.templateName || 'Standard';

                                const chart = window.ratingChart_{{ $chartId }};
                                chart.data.labels = chartLabels;
                                chart.data.datasets[0].label = templateName;
                                chart.data.datasets[0].data = chartRatings;
                                updateChartColors(); // Apply colors based on current mode
                                chart.options.scales.r.ticks.backdropColor = 'transparent';
                                chart.options.scales.r.ticks.showLabelBackdrop = false;
                                chart.options.scales.r.pointLabels.backdropColor = 'transparent';
                                chart.options.scales.r.pointLabels.showLabelBackdrop = false;
                                chart.update('active');
                            }
                        });
                    });
                }

                setupRatingChart();
            })();
    </script>

    <script>
        (function() {
                if (window['skorChartSetup_{{ $chartId }}']) return;
                window['skorChartSetup_{{ $chartId }}'] = true;

                function setupSkorChart() {
                    if (window.skorChart_{{ $chartId }}) {
                        window.skorChart_{{ $chartId }}.destroy();
                    }

                    let chartInstance = null;
                    let chartLabels = @js($chartData['labels']);
                    let chartScores = @js($chartData['scores']);
                    let templateName = @js($selectedTemplate?->name ?? 'Standard');
                    let maxScore = @js($maxScore);

                    function getChartColors() {
                        const isDarkMode = document.documentElement.classList.contains('dark');
                        return {
                            borderColor: '#16a34a', // ← UBAH dari '#dc2626' (merah) ke hijau
                            backgroundColor: isDarkMode ? 'rgba(34, 197, 94, 0.15)' :
                            'rgba(22, 163, 74, 0.15)', // ← UBAH dari merah ke hijau
                            pointBackgroundColor: '#16a34a', // ← UBAH dari '#dc2626' (merah) ke hijau
                            pointBorderColor: '#16a34a', // ← UBAH dari '#dc2626' (merah) ke hijau
                            textColor: isDarkMode ? '#e5e7eb' : '#374151',
                            gridColor: isDarkMode ? 'rgba(229, 231, 235, 0.2)' : 'rgba(55, 65, 81, 0.2)'
                        };
                    }

                    function updateChartColors() {
                        const colors = getChartColors();
                        if (window.skorChart_{{ $chartId }}) {
                            const chart = window.skorChart_{{ $chartId }};
                            chart.data.datasets[0].borderColor = colors.borderColor;
                            chart.data.datasets[0].backgroundColor = colors.backgroundColor;
                            chart.data.datasets[0].pointBackgroundColor = colors.pointBackgroundColor;
                            chart.data.datasets[0].pointBorderColor = colors.pointBorderColor;
                            chart.options.plugins.legend.labels.color = colors.textColor;
                            chart.options.scales.r.ticks.color = colors.textColor;
                            chart.options.scales.r.pointLabels.color = colors.textColor;
                            chart.options.scales.r.grid.color = colors.gridColor;
                            chart.update('active');
                        }
                    }

                    function initChart() {
                        const canvas = document.getElementById('chartSkor-{{ $chartId }}');
                        if (!canvas) return;

                        const ctx = canvas.getContext('2d');
                        const colors = getChartColors();

                        chartInstance = new Chart(ctx, {
                            type: 'radar',
                            data: {
                                labels: chartLabels,
                                datasets: [{
                                    label: templateName,
                                    data: chartScores,
                                    borderColor: colors.borderColor,
                                    backgroundColor: colors.backgroundColor,
                                    pointBackgroundColor: colors.pointBackgroundColor,
                                    pointBorderColor: colors.pointBorderColor,
                                    pointRadius: 4,
                                    borderWidth: 2
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'bottom',
                                        labels: {
                                            font: {
                                                size: 11
                                            },
                                            color: colors.textColor
                                        }
                                    },
                                    datalabels: {
                                        display: false
                                    }
                                },
                                scales: {
                                    r: {
                                        beginAtZero: true,
                                        min: 0,
                                        max: maxScore,
                                        ticks: {
                                            stepSize: 10,
                                            font: {
                                                size: 10
                                            },
                                            color: colors.textColor,
                                            backdropColor: 'transparent',
                                            showLabelBackdrop: false
                                        },
                                        pointLabels: {
                                            font: {
                                                size: 10
                                            },
                                            color: colors.textColor,
                                            backdropColor: 'transparent',
                                            showLabelBackdrop: false
                                        },
                                        grid: {
                                            color: colors.gridColor
                                        }
                                    }
                                }
                            }
                        });

                        window.skorChart_{{ $chartId }} = chartInstance;
                    }

                    function waitForLivewire(callback) {
                        if (window.Livewire) callback();
                        else setTimeout(() => waitForLivewire(callback), 100);
                    }

                    waitForLivewire(function() {
                        initChart();

                        // Listen for dark mode changes
                        const observer = new MutationObserver(() => {
                            updateChartColors();
                        });
                        observer.observe(document.documentElement, {
                            attributes: true,
                            attributeFilter: ['class']
                        });

                        Livewire.on('chartDataUpdated', function(data) {
                            let chartData = Array.isArray(data) && data.length > 0 ? data[0] : data;
                            if (window.skorChart_{{ $chartId }} && chartData) {
                                chartLabels = chartData.labels;
                                chartScores = chartData.scores;
                                templateName = chartData.templateName || 'Standard';

                                const chart = window.skorChart_{{ $chartId }};
                                chart.data.labels = chartLabels;
                                chart.data.datasets[0].label = templateName;
                                chart.data.datasets[0].data = chartScores;
                                updateChartColors(); // Apply colors based on current mode
                                chart.options.scales.r.ticks.backdropColor = 'transparent';
                                chart.options.scales.r.ticks.showLabelBackdrop = false;
                                chart.options.scales.r.pointLabels.backdropColor = 'transparent';
                                chart.options.scales.r.pointLabels.showLabelBackdrop = false;
                                chart.update('active');
                            }
                        });
                    });
                }

                setupSkorChart();
            })();
    </script>
    @endif
</div>