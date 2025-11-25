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
                            <span
                                class="text-gray-900 dark:text-gray-100">{{ $selectedEvent->institution->name ?? 'N/A' }}</span>
                        </div>
                    @endif

                    {{-- PHASE 3: Custom Standard Selector --}}
                    @if (auth()->user()->institution_id)
                        <div class="mt-3">
                            <label for="customStandardSelect"
                                class="block text-sm font-semibold text-gray-600 dark:text-gray-300 mb-2">
                                Standar Penilaian:
                            </label>
                            <select id="customStandardSelect" wire:model.live="selectedCustomStandardId"
                                wire:change="selectCustomStandard($event.target.value)"
                                class="w-full md:w-96 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Quantum (Default)</option>
                                @foreach ($availableCustomStandards as $standard)
                                    <option value="{{ $standard['id'] }}">
                                        {{ $standard['name'] }} ({{ $standard['code'] }})
                                    </option>
                                @endforeach
                            </select>
                            @if ($selectedCustomStandardId)
                                <p class="mt-1 text-xs text-blue-600 dark:text-blue-400">
                                    Menggunakan custom standard. Adjustment sementara akan direset.
                                </p>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- PHASE 2C: Control Buttons --}}
                <div class="mt-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        {{-- Category Weight Editor Component --}}
                        @livewire('components.category-weight-editor', [
                            'templateId' => $selectedTemplate->id,
                            'categoryCode1' => 'potensi',
                            'categoryCode2' => 'kompetensi',
                        ])
                    </div>

                    <div class="flex items-center gap-3">
                        {{-- Optimized button with instant modal display --}}
                        <x-mary-button x-data
                            @click="
        $dispatch('open-selection-modal-instant');
        $wire.openSelectionModal();
    "
                            class="btn-primary" spinner="openSelectionModal">
                            <span class="flex items-center gap-2">
                                🎯 Pilih Aspek & Sub-Aspek
                            </span>
                        </x-mary-button>

                        <x-mary-button wire:click="resetAdjustments" class="btn-outline" spinner="resetAdjustments">
                            <span class="flex items-center gap-2">
                                ↻ Reset ke Default
                            </span>
                        </x-mary-button>
                    </div>

                    {{-- Adjustment Indicator --}}
                    <x-adjustment-indicator
                        :template-id="$selectedTemplate->id"
                        category-code="potensi"
                    />
                </div>
            @endif
        </div>

        {{-- Header Title --}}
        <div
            class="text-center font-bold uppercase mb-4 text-sm bg-gray-300 dark:bg-gray-600 py-2 border border-black dark:border-gray-600">
            STANDAR PEMETAAN POTENSI INDIVIDU "STATIC PRIBADI SPIDER PLOT"
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
                        class="border border-black dark:border-gray-600 px-2 py-1 bg-gray-300 dark:bg-gray-600 font-semibold w-1/5">
                        Perusahaan/Lembaga</td>
                    <td class="border border-black dark:border-gray-600 px-2 py-1 w-2/5">
                        {{ strtoupper($selectedEvent->institution->name ?? 'N/A') }}</td>
                    <td
                        class="border border-black dark:border-gray-600 px-2 py-1 bg-gray-300 dark:bg-gray-600 font-semibold w-1/6">
                    </td>
                    <td class="border border-black dark:border-gray-600 px-2 py-1 w-1/6"></td>
                </tr>
                <tr>
                    <td
                        class="border border-black dark:border-gray-600 px-2 py-1 bg-gray-300 dark:bg-gray-600 font-semibold">
                        Standard Penilaian</td>
                    <td class="border border-black dark:border-gray-600 px-2 py-1">{{ $selectedTemplate->name }}</td>
                    <td
                        class="border border-black dark:border-gray-600 px-2 py-1 bg-gray-300 dark:bg-gray-600 font-semibold">
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
                        <tr class="bg-gray-300 dark:bg-gray-600">
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
                        @php $romanNumerals = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X']; @endphp

                        @foreach ($categoryData as $catIndex => $category)
                            {{-- Aspects within Potensi Category --}}
                            @foreach ($category['aspects'] as $aspectIndex => $aspect)
                                {{-- Aspect Row (if has sub-aspects, show as header) --}}
                                @if (count($aspect['sub_aspects']) > 0)
                                    <tr>
                                        <td
                                            class="border border-black dark:border-gray-600 px-2 py-2 text-center font-bold">
                                            {{ $romanNumerals[$aspectIndex] ?? $aspectIndex + 1 }}</td>
                                        <td class="border border-black dark:border-gray-600 px-2 py-2 font-bold">
                                            {{ $aspect['name'] }}</td>
                                        <td class="border border-black dark:border-gray-600 px-2 py-2 text-center"></td>
                                        <td
                                            class="border border-black dark:border-gray-600 px-2 py-2 text-center font-bold">
                                            {{ $aspect['sub_aspects_count'] }}</td>
                                        {{-- PHASE 2C: Weight Cell with Visual Indicator (edit via modal only) --}}
                                        <td class="border border-black dark:border-gray-600 px-2 py-2 text-center font-bold {{ $aspect['is_weight_adjusted'] ?? false ? 'bg-amber-100 dark:bg-amber-900/30' : '' }}"
                                            title="{{ $aspect['is_weight_adjusted'] ?? false ? 'Disesuaikan dari ' . $aspect['original_weight'] . '% - Edit via modal Pilih Aspek' : '' }}">
                                            {{ $aspect['weight_percentage'] }}
                                            @if ($aspect['is_weight_adjusted'] ?? false)
                                                <span class="text-amber-600 dark:text-amber-400"></span>
                                            @endif
                                        </td>
                                        <td
                                            class="border border-black dark:border-gray-600 px-2 py-2 text-center font-bold">
                                            {{ number_format($aspect['standard_rating'], 2) }}</td>
                                        <td
                                            class="border border-black dark:border-gray-600 px-2 py-2 text-center font-bold">
                                            {{ number_format($aspect['score'], 2) }}</td>
                                    </tr>

                                    {{-- Sub-Aspects --}}
                                    @foreach ($aspect['sub_aspects'] as $subIndex => $subAspect)
                                        <tr>
                                            <td class="border border-black dark:border-gray-600 px-2 py-2 text-center">
                                                {{ $subIndex + 1 }}</td>
                                            <td class="border border-black dark:border-gray-600 px-2 py-2 pl-8">
                                                {{ $subAspect['name'] }}</td>
                                            {{-- PHASE 2C: Clickable Rating Cell with Visual Indicator --}}
                                            <td wire:click="openEditSubAspectRating('{{ $subAspect['code'] }}', {{ $subAspect['standard_rating'] }})"
                                                class="border border-black dark:border-gray-600 px-2 py-2 text-center cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 {{ $subAspect['is_adjusted'] ?? false ? 'bg-amber-100 dark:bg-amber-900/30' : '' }}"
                                                title="{{ $subAspect['is_adjusted'] ?? false ? 'Disesuaikan dari ' . $subAspect['original_rating'] . ' - Klik untuk edit' : 'Klik untuk edit' }}">
                                                {{ $subAspect['standard_rating'] }}
                                                @if ($subAspect['is_adjusted'] ?? false)
                                                    <span class="text-amber-600 dark:text-amber-400"></span>
                                                @endif
                                            </td>
                                            <td class="border border-black dark:border-gray-600 px-2 py-2"></td>
                                            <td class="border border-black dark:border-gray-600 px-2 py-2"></td>
                                            <td class="border border-black dark:border-gray-600 px-2 py-2"></td>
                                            <td class="border border-black dark:border-gray-600 px-2 py-2"></td>
                                        </tr>
                                    @endforeach
                                @else
                                    {{-- Aspect without sub-aspects (Kompetensi) --}}
                                    <tr>
                                        <td class="border border-black dark:border-gray-600 px-2 py-2 text-center">
                                            {{ $aspectIndex + 1 }}</td>
                                        <td class="border border-black dark:border-gray-600 px-2 py-2 pl-4">
                                            {{ $aspect['name'] }}</td>
                                        <td class="border border-black dark:border-gray-600 px-2 py-2 text-center">
                                            {{ number_format($aspect['standard_rating'], 2) }}</td>
                                        <td class="border border-black dark:border-gray-600 px-2 py-2"></td>
                                        <td class="border border-black dark:border-gray-600 px-2 py-2 text-center">
                                            {{ $aspect['weight_percentage'] }}</td>
                                        <td class="border border-black dark:border-gray-600 px-2 py-2"></td>
                                        <td class="border border-black dark:border-gray-600 px-2 py-2"></td>
                                    </tr>
                                @endif
                            @endforeach
                        @endforeach

                        {{-- TOTAL --}}
                        <tr class="bg-gray-300 dark:bg-gray-600 font-bold">
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
                    Gambar Rating Standar Atribut Potensi Mapping Static Pribadi Spider Plot
                </div>
                <div class="flex justify-center">
                    <canvas id="chartRating-{{ $chartId }}" style="max-width:600px; max-height:400px;"></canvas>
                </div>
            </div>

            {{-- Tabel Mapping Summary --}}
            <div
                class="mb-2 mt-6 font-bold text-xs bg-gray-300 dark:bg-gray-600 border border-black dark:border-gray-600 px-2 py-2 text-center">
                ATRIBUT POTENSI MAPPING
            </div>
            <table class="w-full border border-black dark:border-gray-600 text-xs mb-4">
                <thead>
                    <tr class="bg-gray-300 dark:bg-gray-600">
                        <th class="border border-black dark:border-gray-600 px-2 py-2 w-12">No.</th>
                        <th class="border border-black dark:border-gray-600 px-2 py-2">ATRIBUT</th>
                        <th class="border border-black dark:border-gray-600 px-2 py-2 w-24">JUMLAH ATRIBUT</th>
                        <th class="border border-black dark:border-gray-600 px-2 py-2 w-20">BOBOT (%)</th>
                        <th class="border border-black dark:border-gray-600 px-2 py-2 w-24">RATING <br>Rata-Rata</th>
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
                                    {{ $aspect['sub_aspects_count'] }}</td>
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
                    <tr class="font-bold bg-gray-300 dark:bg-gray-600">
                        <td class="border border-black dark:border-gray-600 px-2 py-2 text-center" colspan="2">
                            Jumlah
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
                    Gambar Skor Standar Atribut Potensi Mapping Static Pribadi Spider Plot
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

    {{-- Edit Sub-Aspect Rating Modal --}}
    <x-mary-modal wire:model="showEditRatingModal" title="Edit Rating Sub-Aspek"
        subtitle="Sesuaikan nilai rating sub-aspek kompetensi" class="backdrop-blur">
        <div class="space-y-4">
            <x-mary-input class="text-xl !outline-none" label="Rating (1-5)" wire:model="editingValue"
                type="number" min="1" max="5" step="1" />
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Nilai asli: {{ $editingOriginalValue }}
            </p>
        </div>
        <x-slot:actions>
            <x-mary-button label="Batal" wire:click="closeModal" class="btn-outline" />
            <x-mary-button label="Simpan" wire:click="saveSubAspectRating" class="btn-primary"
                spinner="saveSubAspectRating" />
        </x-slot:actions>
    </x-mary-modal>

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
                            borderColor: isDarkMode ? '#22c55e' : '#16a34a', // ← UBAH dari biru ke hijau
                            backgroundColor: isDarkMode ? 'rgba(34, 197, 94, 0.2)' :
                            'rgba(22, 163, 74, 0.2)', // ← UBAH dari biru ke hijau
                            pointBackgroundColor: isDarkMode ? '#22c55e' : '#16a34a', // ← UBAH dari biru ke hijau
                            pointBorderColor: isDarkMode ? '#22c55e' : '#16a34a', // ← UBAH dari biru ke hijau
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
                                updateChartColors();
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
                            borderColor: isDarkMode ? '#22c55e' : '#16a34a', // ← UBAH dari merah ke hijau
                            backgroundColor: isDarkMode ? 'rgba(34, 197, 94, 0.15)' :
                            'rgba(22, 163, 74, 0.15)', // ← UBAH dari merah ke hijau
                            pointBackgroundColor: isDarkMode ? '#22c55e' : '#16a34a', // ← UBAH dari merah ke hijau
                            pointBorderColor: isDarkMode ? '#22c55e' : '#16a34a', // ← UBAH dari merah ke hijau
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
                                maxScore = chartData.maxScore || maxScore;

                                const chart = window.skorChart_{{ $chartId }};
                                chart.data.labels = chartLabels;
                                chart.data.datasets[0].label = templateName;
                                chart.data.datasets[0].data = chartScores;
                                chart.options.scales.r.max = maxScore;
                                updateChartColors();
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
