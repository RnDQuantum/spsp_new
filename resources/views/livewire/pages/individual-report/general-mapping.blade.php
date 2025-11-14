<div>
    <div class="bg-white dark:bg-gray-800 mx-auto my-8 shadow overflow-hidden" style="max-width: 1400px;">
        <!-- Header -->
        <div class="border-b-4 border-black dark:border-gray-300 py-3 bg-gray-300 dark:bg-gray-600">
            <h1 class="text-center text-lg font-bold uppercase tracking-wide text-gray-900 dark:text-gray-100 italic">
                GENERAL MAPPING
            </h1>
            <p class="text-center text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">
                {{ $participant->name }}
            </p>
            <p class="text-center text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">
                {{ $participant->event->name }}<br>
                {{ $participant->positionFormation->name }} -
                {{ $participant->positionFormation->template->name }}
            </p>
            {{-- <p class="text-center text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">
            </p> --}}
        </div>

        <!-- Tolerance Selector Component -->
        @php
            $summary = $this->getPassingSummary();
        @endphp
        @livewire('components.tolerance-selector', [
            'passing' => $summary['passing'],
            'total' => $summary['total'],
        ])

        <!-- Table Section -->
        <div class="p-4 overflow-x-auto">
            <table class="min-w-full border border-black dark:border-gray-300 text-xs text-gray-900 dark:text-gray-100">
                <thead>
                    <tr class="bg-gray-300 dark:bg-gray-600 text-gray-900 dark:text-gray-100">
                        <th class="border border-black dark:border-gray-300 px-3 py-2 font-semibold" rowspan="2">No
                        </th>
                        <th class="border border-black dark:border-gray-300 px-3 py-2 font-semibold" rowspan="2">
                            Atribut
                        </th>
                        <th class="border border-black dark:border-gray-300 px-3 py-2 font-semibold" rowspan="2">
                            Bobot %<br>200</th>
                        <th class="border border-black dark:border-gray-300 px-3 py-2 font-semibold" colspan="2">
                            <span x-data
                                x-text="$wire.tolerancePercentage > 0 ? 'Standar (-' + $wire.tolerancePercentage + '%)' : 'Standar'"></span>
                        </th>
                        <th class="border border-black dark:border-gray-300 px-3 py-2 font-semibold" colspan="2">
                            Individu</th>
                        <th class="border border-black dark:border-gray-300 px-3 py-2 font-semibold" colspan="2">Gap
                        </th>
                        <th class="border border-black dark:border-gray-300 px-3 py-2 font-semibold" rowspan="2">
                            Persentase<br>Kesesuaian</th>
                        <th class="border border-black dark:border-gray-300 px-3 py-2 font-semibold" rowspan="2">
                            Kesimpulan</th>
                    </tr>
                    <tr class="bg-gray-300 dark:bg-gray-600 text-gray-900 dark:text-gray-100">
                        <th class="border border-black dark:border-gray-300 px-3 py-1 font-semibold">Rating
                        </th>
                        <th class="border border-black dark:border-gray-300 px-3 py-1 font-semibold">Skor</th>
                        <th class="border border-black dark:border-gray-300 px-3 py-1 font-semibold">Rating
                        </th>
                        <th class="border border-black dark:border-gray-300 px-3 py-1 font-semibold">Skor</th>
                        <th class="border border-black dark:border-gray-300 px-3 py-1 font-semibold">Rating
                        </th>
                        <th class="border border-black dark:border-gray-300 px-3 py-0 font-semibold">Skor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($aspectsData as $aspect)
                        <tr>
                            <td class="border border-black dark:border-gray-300 px-3 py-2 text-center">
                                {{ $loop->iteration }}</td>
                            <td class="border border-black dark:border-gray-300 px-3 py-2">{{ $aspect['name'] }}</td>
                            <td class="border border-black dark:border-gray-300 px-3 py-2 text-center">
                                {{ $aspect['weight_percentage'] }}</td>
                            <td class="border border-black dark:border-gray-300 px-3 py-2 text-center">
                                {{ number_format($aspect['standard_rating'], 2) }}</td>
                            <td class="border border-black dark:border-gray-300 px-3 py-2 text-center">
                                {{ number_format($aspect['standard_score'], 2) }}</td>
                            <td class="border border-black dark:border-gray-300 px-3 py-2 text-center">
                                {{ number_format($aspect['individual_rating'], 2) }}</td>
                            <td class="border border-black dark:border-gray-300 px-3 py-2 text-center">
                                {{ number_format($aspect['individual_score'], 2) }}</td>
                            <td class="border border-black dark:border-gray-300 px-3 py-2 text-center">
                                {{ number_format($aspect['gap_rating'], 2) }}</td>
                            <td class="border border-black dark:border-gray-300 px-3 py-2 text-center">
                                {{ number_format($aspect['gap_score'], 2) }}</td>
                            <td class="border border-black dark:border-gray-300 px-3 py-2 text-center">
                                @php
                                    $percentage =
                                        $aspect['standard_score'] > 0
                                            ? round(($aspect['individual_score'] / $aspect['standard_score']) * 100)
                                            : 0;
                                @endphp
                                {{ $percentage }}%
                            </td>
                            <td class="border border-white px-3 py-2 text-center font-bold {{ $conclusionConfig[$aspect['conclusion_text']]['tailwindClass'] ?? 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white' }}">
                                {{ $aspect['conclusion_text'] }}
                            </td>
                        </tr>
                    @endforeach

                    <!-- Total Rating Row -->
                    <tr class="font-bold bg-gray-300 dark:bg-gray-600">
                        <td class="border border-black dark:border-gray-300 px-3 py-2 text-center" colspan="3">Total
                            Rating</td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2 text-center">
                            {{ number_format($totalStandardRating, 2) }}</td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2 bg-black"></td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2 text-center">
                            {{ number_format($totalIndividualRating, 2) }}</td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2 bg-black"></td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2 text-center">
                            {{ number_format($totalGapRating, 2) }}</td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2 bg-black"></td>
                        <td class="border border-white px-3 py-2 text-center font-bold {{ $conclusionConfig[$overallConclusion]['tailwindClass'] ?? 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white' }}" colspan="2">
                            {{ $overallConclusion }}
                        </td>
                    </tr>

                    <!-- Total Score Row -->
                    <tr class="font-bold bg-gray-300 dark:bg-gray-600">
                        <td class="border border-black dark:border-gray-300 px-3 py-2 text-center" colspan="3">Total
                            Skor</td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2 bg-black"></td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2 text-center">
                            {{ number_format($totalStandardScore, 2) }}</td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2 bg-black"></td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2 text-center">
                            {{ number_format($totalIndividualScore, 2) }}</td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2 bg-black"></td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2 text-center">
                            {{ number_format($totalGapScore, 2) }}</td>
                        <td class="border border-white px-3 py-2 text-center font-bold {{ $conclusionConfig[$overallConclusion]['tailwindClass'] ?? 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white' }}" colspan="2">
                            {{ $overallConclusion }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Ranking Information Section -->
        @if ($showRankingInfo)
            @php
                $rankingInfo = $this->getParticipantRanking();
            @endphp

            @if ($rankingInfo)
                <div class="px-6 py-4 bg-gray-300 dark:bg-gray-600 border-t-2 border-black dark:border-gray-600">
                    <div class="max-w-5xl mx-auto">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-circle-check"></i>
                            Ranking Peserta - Skor Gabungan (Weighted)
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Ranking Card -->
                            <div
                                class="bg-white dark:bg-gray-800 border-2 border-blue-300 dark:border-blue-600 rounded-lg p-4 text-center">
                                <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Ranking</div>
                                <div class="text-4xl font-bold text-blue-600 dark:text-blue-400">
                                    #{{ $rankingInfo['rank'] }}
                                </div>
                            </div>

                            <!-- Total Participants Card -->
                            <div
                                class="bg-white dark:bg-gray-800 border-2 border-blue-300 dark:border-blue-600 rounded-lg p-4 text-center">
                                <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Total Peserta</div>
                                <div class="text-4xl font-bold text-blue-600 dark:text-blue-400">
                                    {{ $rankingInfo['total'] }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $participant->positionFormation->name }}
                                </div>
                            </div>

                            <!-- Weights Info Card -->
                            <div
                                class="bg-white dark:bg-gray-800 border-2 border-blue-300 dark:border-blue-600 rounded-lg p-4 text-center">
                                <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Bobot Penilaian</div>
                                <div class="text-sm font-bold text-blue-600 dark:text-blue-400">
                                    Potensi: {{ $rankingInfo['potensi_weight'] }}%
                                </div>
                                <div class="text-sm font-bold text-blue-600 dark:text-blue-400">
                                    Kompetensi: {{ $rankingInfo['kompetensi_weight'] }}%
                                </div>
                            </div>

                            <!-- Conclusion Card -->
                            @php
                                $conclusionText = $rankingInfo['conclusion'];
                                $borderClass = match($conclusionText) {
                                    'Di Atas Standar' => 'border-green-300 dark:border-green-600',
                                    'Memenuhi Standar' => 'border-yellow-300 dark:border-yellow-600',
                                    'Di Bawah Standar' => 'border-red-300 dark:border-red-600',
                                    default => 'border-gray-300 dark:border-gray-600'
                                };
                            @endphp
                            <div class="bg-white dark:bg-gray-800 border-2 rounded-lg p-4 text-center {{ $borderClass }}">
                                <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Status</div>
                                <div class="text-base font-bold px-3 py-2 rounded-lg {{ $conclusionConfig[$conclusionText]['tailwindClass'] ?? 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white' }}">
                                    {{ $rankingInfo['conclusion'] }}
                                </div>
                            </div>
                        </div>

                        <!-- Additional Info -->
                        <div class="mt-4 bg-gray-300 dark:bg-gray-600 rounded-lg p-1">
                            <div class="text-sm text-gray-800 dark:text-gray-200">
                                <strong>Keterangan:</strong> Ranking berdasarkan <strong>Total Weighted Individual
                                    Score</strong> dari kombinasi:
                                <br>
                                • <strong>Potensi ({{ $rankingInfo['potensi_weight'] }}%)</strong>: Psychology Mapping
                                <br>
                                • <strong>Kompetensi ({{ $rankingInfo['kompetensi_weight'] }}%)</strong>: Managerial
                                Competency Mapping
                                <br>
                                Dibandingkan dengan peserta lain di event yang sama pada posisi
                                <strong>{{ $participant->positionFormation->name }}</strong>.
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        <!-- Chart Section Rating -->
        <div class="p-6 border-t-2 border-black dark:border-gray-300 bg-white dark:bg-gray-800" wire:ignore
            id="chart-rating-{{ $chartId }}">
            <div class="text-center text-base font-bold mb-6 text-gray-900 dark:text-gray-100">Profil Pribadi Spider
                Plot Chart (Rating)</div>


            <!-- Legend -->
            <div class="flex justify-center text-sm gap-8 text-gray-900 dark:text-gray-100 mb-2"
                id="rating-legend-{{ $chartId }}">
                <!-- GANTI URUTAN LEGEND INI -->
                <span class="legend-item ... " data-chart="rating" data-dataset="0">
                    <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #5db010;"></span>
                    <span class="font-semibold ...">{{ $participant->name }}</span>
                </span>
                <span class="legend-item ... " data-chart="rating" data-dataset="1">
                    <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #b50505;"></span>
                    <span class="font-semibold ...">Standard</span>
                </span>
                <span class="legend-item ... " data-chart="rating" data-dataset="2">
                    <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #fafa05;"></span>
                    <span class="font-semibold ..." x-data
                        x-text="'Tolerance ' + $wire.tolerancePercentage + '%'"></span>
                </span>
            </div>


            <div class="flex justify-center mb-6">
                <div style="width: 900px; height: 900px; position: relative;">
                    <canvas id="spiderRatingChart-{{ $chartId }}"></canvas>
                </div>
            </div>



            <script>
                (function() {
                    if (window['ratingChartSetup_{{ $chartId }}']) return;
                    window['ratingChartSetup_{{ $chartId }}'] = true;

                    window.ratingVisibility_{{ $chartId }} = {
                        0: false,
                        1: false,
                        2: false
                    };

                    function getGridColors() {
                        const isDark = document.documentElement.classList.contains('dark');
                        return {
                            grid: isDark ? 'rgba(255, 255, 255, 0.5)' : 'rgba(0, 0, 0, 0.5)',
                            angleLines: isDark ? 'rgba(255, 255, 255, 0.5)' : 'rgba(0, 0, 0, 0.5)',
                            ticks: isDark ? '#ffffff' : '#000000', // Warna solid untuk ticks
                            pointLabels: isDark ? '#ffffff' : '#000000' // Warna solid untuk label
                        };
                    }

                    window.toggleDataset_{{ $chartId }} = function(chartType, datasetIndex) {
                        const chart = window[`${chartType}Chart_{{ $chartId }}`];
                        if (!chart || !chart.data.datasets[datasetIndex]) return;
                        const wasHidden = chart.data.datasets[datasetIndex].hidden || false;
                        chart.data.datasets[datasetIndex].hidden = !wasHidden;
                        window[`${chartType}Visibility_{{ $chartId }}`][datasetIndex] = !wasHidden;
                        chart.update('active');
                        updateLegendVisual(chartType);
                    };

                    function updateLegendVisual(chartType) {
                        const legendContainer = document.getElementById(`${chartType}-legend-{{ $chartId }}`);
                        if (!legendContainer) return;
                        const chart = window[`${chartType}Chart_{{ $chartId }}`];
                        legendContainer.querySelectorAll('.legend-item').forEach(item => {
                            const idx = parseInt(item.dataset.dataset);
                            const isHidden = chart?.data.datasets[idx]?.hidden || false;
                            if (isHidden) {
                                item.classList.add('opacity-50', 'line-through');
                                item.classList.remove('bg-white', 'shadow-sm');
                                item.classList.add('bg-gray-50', 'dark:bg-gray-600');
                            } else {
                                item.classList.remove('opacity-50', 'line-through', 'bg-gray-50', 'dark:bg-gray-600');
                                item.classList.add('bg-white', 'dark:bg-gray-800', 'shadow-sm');
                            }
                        });
                    }

                    function setupLegendListeners(chartType) {
                        const legendContainer = document.getElementById(`${chartType}-legend-{{ $chartId }}`);
                        if (!legendContainer) return;
                        legendContainer.onclick = (e) => {
                            const item = e.target.closest('.legend-item');
                            if (!item) return;
                            window.toggleDataset_{{ $chartId }}(chartType, parseInt(item.dataset.dataset));
                        };
                    }

                    function setupRatingChart() {
                        if (window.ratingChart_{{ $chartId }}) window.ratingChart_{{ $chartId }}.destroy();

                        const chartLabels = @js($chartLabels);
                        const originalStandardRatings = @js($chartOriginalStandardRatings);
                        const standardRatings = @js($chartStandardRatings);
                        const individualRatings = @js($chartIndividualRatings);
                        const participantName = @js($participant->name);
                        const tolerancePercentage = @js($tolerancePercentage);

                        const canvas = document.getElementById('spiderRatingChart-{{ $chartId }}');
                        if (!canvas) return;
                        const ctx = canvas.getContext('2d');
                        const colors = getGridColors();

                        const datasets = [{
                                // LAYER 1: PESERTA (HIJAU)
                                label: participantName,
                                data: individualRatings,
                                fill: true,
                                backgroundColor: '#5db010', // ← UBAH dari rgba(..., 0.7) ke solid
                                borderColor: '#8fd006',
                                pointBackgroundColor: '#8fd006',
                                pointBorderColor: '#fff',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                hidden: window.ratingVisibility_{{ $chartId }}[0],
                                datalabels: {
                                    display: false,
                                    color: '#000000',
                                    backgroundColor: '#5db010',
                                    borderRadius: 4,
                                    padding: 6,
                                    font: {
                                        weight: 'bold',
                                        size: 10
                                    },
                                    anchor: 'end',
                                    align: 'end',
                                    offset: 6,
                                    formatter: (v) => v.toFixed(2)
                                }
                            },
                            {
                                label: 'Standard', // ← UBAH dari 'Tolerance {{ $tolerancePercentage }}%'
                                data: standardRatings, // ← data tetap standardRatings
                                fill: true,
                                backgroundColor: '#b50505', // ← UBAH dari rgba(..., 0.7) ke solid
                                borderColor: '#b50505',
                                borderWidth: 2,
                                pointRadius: 3,
                                pointBackgroundColor: '#9a0404',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                hidden: window.ratingVisibility_{{ $chartId }}[1],
                                datalabels: {
                                    display: false,
                                    color: '#FFFFFF',
                                    backgroundColor: '#b50505',
                                    borderRadius: 4,
                                    padding: 6,
                                    font: {
                                        weight: 'bold',
                                        size: 9
                                    },
                                    anchor: 'end',
                                    align: 'start',
                                    formatter: (v) => v.toFixed(2)
                                }
                            },
                            {
                                label: `Tolerance ${tolerancePercentage}%`, // ← UBAH dari 'Standard'
                                data: originalStandardRatings, // ← data tetap originalStandardRatings
                                fill: true,
                                backgroundColor: '#fafa05', // ← UBAH dari rgba(..., 0.7) ke solid
                                borderColor: '#e6d105',
                                pointBackgroundColor: '#e6d105',
                                pointBorderColor: '#fff',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                hidden: window.ratingVisibility_{{ $chartId }}[2],
                                datalabels: {
                                    display: false,
                                    color: '#000000',
                                    backgroundColor: '#fafa05',
                                    borderRadius: 4,
                                    padding: 6,
                                    font: {
                                        weight: 'bold',
                                        size: 10
                                    },
                                    anchor: 'center',
                                    align: 'center',
                                    offset: 6,
                                    formatter: (v) => v.toFixed(2)
                                }
                            }
                        ];

                        window.ratingChart_{{ $chartId }} = new Chart(ctx, {
                            type: 'radar',
                            data: {
                                labels: chartLabels,
                                datasets
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    datalabels: {
                                        display: ctx => !ctx.dataset.hidden
                                    }
                                },
                                scales: {
                                    r: {
                                        beginAtZero: true,
                                        min: 0,
                                        max: 5,
                                        ticks: {
                                            stepSize: 1,
                                            color: colors.ticks,
                                            backdropColor: 'transparent',
                                            showLabelBackdrop: false,
                                            display: false, // SEMBUNYIKAN TICKS DEFAULT
                                            font: {
                                                size: 12,
                                                weight: 'bold'
                                            },
                                            z: 2
                                        },
                                        pointLabels: {
                                            font: {
                                                size: 11,
                                                weight: '600'
                                            },
                                            color: colors.pointLabels,
                                            padding: 15,
                                            z: 3
                                        },
                                        grid: {
                                            color: colors.grid,
                                            z: 1
                                        },
                                        angleLines: {
                                            color: colors.angleLines,
                                            z: 1
                                        }
                                    }
                                }
                            },
                            plugins: [{
                                id: 'shiftTicks',
                                afterDraw: (chart) => {
                                    const {
                                        ctx,
                                        scales
                                    } = chart;
                                    const scale = scales.r;
                                    const ticks = scale.ticks;
                                    const yCenter = scale.yCenter;
                                    const xCenter = scale.xCenter;

                                    ctx.save();
                                    ctx.font = `bold ${scale.options.ticks.font.size}px sans-serif`;
                                    ctx.fillStyle = scale.options.ticks.color || '#000';
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle';

                                    // Offset untuk menggeser posisi
                                    const offsetX = 10; // geser ke kanan
                                    const offsetY = 0; // tidak geser vertikal

                                    ticks.forEach((tick) => {
                                        const value = tick.value;
                                        const radius = scale.getDistanceFromCenterForValue(value);
                                        const labelY = yCenter - radius - offsetY;
                                        const labelX = xCenter + offsetX;
                                        ctx.fillText(value, labelX, labelY);
                                    });

                                    ctx.restore();
                                }
                            }]
                        });

                        console.log('✅ Rating chart initialized');
                    }

                    function waitForLivewire(callback) {
                        if (window.Livewire) callback();
                        else setTimeout(() => waitForLivewire(callback), 100);
                    }

                    waitForLivewire(function() {
                        setupRatingChart();
                        setupLegendListeners('rating');
                        updateLegendVisual('rating');

                        Livewire.on('chartDataUpdated', function(data) {
                            let chartData = Array.isArray(data) && data.length > 0 ? data[0] : data;
                            if (!chartData || !window.ratingChart_{{ $chartId }}) return;

                            const chart = window.ratingChart_{{ $chartId }};

                            // UBAH BAGIAN INI:
                            chart.data.datasets[0].data = chartData.individualRatings; // Peserta
                            chart.data.datasets[1].label = 'Standard'; // ← UBAH label
                            chart.data.datasets[1].data = chartData.standardRatings; // Standard
                            chart.data.datasets[2].label = `Tolerance ${chartData.tolerance}%`; // ← UBAH label
                            chart.data.datasets[2].data = chartData.originalStandardRatings; // Tolerance

                            const colors = getGridColors();
                            chart.options.scales.r.ticks.color = colors.ticks;
                            chart.options.scales.r.pointLabels.color = colors.pointLabels;
                            chart.options.scales.r.grid.color = colors.grid;
                            chart.options.scales.r.angleLines.color = colors.angleLines;
                            chart.options.scales.r.ticks.backdropColor = 'transparent';
                            chart.options.scales.r.ticks.showLabelBackdrop = false;

                            chart.update('active');
                            updateLegendVisual('rating');
                        });
                    });

                    const observer = new MutationObserver(() => {
                        if (window.ratingChart_{{ $chartId }}) {
                            const colors = getGridColors();
                            const chart = window.ratingChart_{{ $chartId }};
                            chart.options.scales.r.ticks.color = colors.ticks;
                            chart.options.scales.r.pointLabels.color = colors.pointLabels;
                            chart.options.scales.r.grid.color = colors.grid;
                            chart.options.scales.r.angleLines.color = colors.angleLines;
                            chart.options.scales.r.ticks.backdropColor = 'transparent';
                            chart.options.scales.r.ticks.showLabelBackdrop = false;
                            chart.update('active');
                        }
                    });
                    observer.observe(document.documentElement, {
                        attributes: true,
                        attributeFilter: ['class']
                    });
                })();
            </script>
        </div>

        <!-- Chart Section Score -->
        <div class="p-6 border-t-2 border-black dark:border-gray-300 bg-white dark:bg-gray-800" wire:ignore
            id="chart-score-{{ $chartId }}">
            <div class="text-center text-base font-bold mb-6 text-gray-900 dark:text-gray-100">Profil Pribadi Spider
                Plot Chart (Score)</div>

            <!-- Legend -->
            <div class="flex justify-center text-sm gap-8 text-gray-900 dark:text-gray-100 mb-0"
                id="score-legend-{{ $chartId }}">
                <!-- GANTI URUTAN LEGEND INI -->
                <span class="legend-item ... " data-chart="rating" data-dataset="0">
                    <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #5db010;"></span>
                    <span class="font-semibold ...">{{ $participant->name }}</span>
                </span>
                <span class="legend-item ... " data-chart="rating" data-dataset="1">
                    <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #b50505;"></span>
                    <span class="font-semibold ...">Standard</span>
                </span>
                <span class="legend-item ... " data-chart="rating" data-dataset="2">
                    <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #fafa05;"></span>
                    <span class="font-semibold ..." x-data
                        x-text="'Tolerance ' + $wire.tolerancePercentage + '%'"></span>
                </span>
            </div>

            <div class="flex justify-center mb-6">
                <div style="width: 900px; height: 900px; position: relative;">
                    <canvas id="spiderScoreChart-{{ $chartId }}"></canvas>
                </div>
            </div>

            <script>
                (function() {
                    if (window['scoreChartSetup_{{ $chartId }}']) return;
                    window['scoreChartSetup_{{ $chartId }}'] = true;

                    window.scoreVisibility_{{ $chartId }} = {
                        0: false,
                        1: false,
                        2: false
                    };

                    function getGridColors() {
                        const isDark = document.documentElement.classList.contains('dark');
                        return {
                            grid: isDark ? 'rgba(255, 255, 255, 0.5)' : 'rgba(0, 0, 0, 0.5)',
                            angleLines: isDark ? 'rgba(255, 255, 255, 0.5)' : 'rgba(0, 0, 0, 0.5)',
                            ticks: isDark ? '#ffffff' : '#000000', // Warna solid untuk ticks
                            pointLabels: isDark ? '#ffffff' : '#000000' // Warna solid untuk label
                        };
                    }

                    window.toggleDataset_{{ $chartId }} = function(chartType, datasetIndex) {
                        const chart = window[`${chartType}Chart_{{ $chartId }}`];
                        if (!chart || !chart.data.datasets[datasetIndex]) return;
                        const wasHidden = chart.data.datasets[datasetIndex].hidden || false;
                        chart.data.datasets[datasetIndex].hidden = !wasHidden;
                        window[`${chartType}Visibility_{{ $chartId }}`][datasetIndex] = !wasHidden;
                        chart.update('active');
                        updateLegendVisual(chartType);
                    };

                    function updateLegendVisual(chartType) {
                        const legendContainer = document.getElementById(`${chartType}-legend-{{ $chartId }}`);
                        if (!legendContainer) return;
                        const chart = window[`${chartType}Chart_{{ $chartId }}`];
                        legendContainer.querySelectorAll('.legend-item').forEach(item => {
                            const idx = parseInt(item.dataset.dataset);
                            const isHidden = chart?.data.datasets[idx]?.hidden || false;
                            if (isHidden) {
                                item.classList.add('opacity-50', 'line-through');
                                item.classList.remove('bg-white', 'shadow-sm');
                                item.classList.add('bg-gray-50', 'dark:bg-gray-600');
                            } else {
                                item.classList.remove('opacity-50', 'line-through', 'bg-gray-50', 'dark:bg-gray-600');
                                item.classList.add('bg-white', 'dark:bg-gray-800', 'shadow-sm');
                            }
                        });
                    }

                    function setupLegendListeners(chartType) {
                        const legendContainer = document.getElementById(`${chartType}-legend-{{ $chartId }}`);
                        if (!legendContainer) return;
                        legendContainer.onclick = (e) => {
                            const item = e.target.closest('.legend-item');
                            if (!item) return;
                            window.toggleDataset_{{ $chartId }}(chartType, parseInt(item.dataset.dataset));
                        };
                    }

                    function setupScoreChart() {
                        if (window.scoreChart_{{ $chartId }}) window.scoreChart_{{ $chartId }}.destroy();

                        const chartLabels = @js($chartLabels);
                        const originalStandardScores = @js($chartOriginalStandardScores);
                        const standardScores = @js($chartStandardScores);
                        const individualScores = @js($chartIndividualScores);
                        const participantName = @js($participant->name);
                        const tolerancePercentage = @js($tolerancePercentage);

                        const canvas = document.getElementById('spiderScoreChart-{{ $chartId }}');
                        if (!canvas) return;
                        const ctx = canvas.getContext('2d');
                        const colors = getGridColors();
                        const maxScore = Math.max(...originalStandardScores, ...individualScores, ...standardScores) * 1.2;

                        const datasets = [{
                                label: participantName,
                                data: individualScores,
                                fill: true,
                                backgroundColor: '#5db010', // Semi-transparan untuk visibilitas grid
                                borderColor: '#8fd006',
                                pointBackgroundColor: '#8fd006',
                                pointBorderColor: '#fff',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                hidden: window.scoreVisibility_{{ $chartId }}[0],
                                datalabels: {
                                    display: false,
                                    color: '#000000',
                                    backgroundColor: '#5db010',
                                    borderRadius: 4,
                                    padding: 6,
                                    font: {
                                        weight: 'bold',
                                        size: 10
                                    },
                                    anchor: 'end',
                                    align: 'end',
                                    offset: 6,
                                    formatter: (v) => v.toFixed(2)
                                }
                            },
                            {
                                label: 'Standard', // ← UBAH dari 'Tolerance ...'
                                data: standardScores,
                                fill: true,
                                backgroundColor: '#b50505', // Semi-transparan untuk visibilitas grid
                                borderColor: '#b50505',
                                borderWidth: 2,
                                pointRadius: 3,
                                hidden: window.scoreVisibility_{{ $chartId }}[1],
                                datalabels: {
                                    display: false,
                                    color: '#FFFFFF',
                                    backgroundColor: '#b50505',
                                    borderRadius: 4,
                                    padding: 6,
                                    font: {
                                        weight: 'bold',
                                        size: 9
                                    },
                                    anchor: 'end',
                                    align: 'start',
                                    formatter: (v) => v.toFixed(2)
                                }
                            },
                            {
                                label: `Tolerance ${tolerancePercentage}%`, // ← UBAH dari 'Standard'
                                data: originalStandardScores,
                                fill: true,
                                backgroundColor: '#fafa05', // Semi-transparan untuk visibilitas grid
                                borderColor: '#e6d105',
                                pointBackgroundColor: '#e6d105',
                                pointBorderColor: '#fff',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                hidden: window.scoreVisibility_{{ $chartId }}[2],
                                datalabels: {
                                    display: false,
                                    color: '#000000',
                                    backgroundColor: '#fafa05',
                                    borderRadius: 4,
                                    padding: 6,
                                    font: {
                                        weight: 'bold',
                                        size: 10
                                    },
                                    anchor: 'center',
                                    align: 'center',
                                    offset: 6,
                                    formatter: (v) => v.toFixed(2)
                                }
                            }
                        ];

                        window.scoreChart_{{ $chartId }} = new Chart(ctx, {
                            type: 'radar',
                            data: {
                                labels: chartLabels,
                                datasets
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    datalabels: {
                                        display: ctx => !ctx.dataset.hidden
                                    }
                                },
                                scales: {
                                    r: {
                                        beginAtZero: true,
                                        min: 0,
                                        max: maxScore,
                                        ticks: {
                                            stepSize: 20,
                                            color: colors.ticks,
                                            backdropColor: 'transparent',
                                            showLabelBackdrop: false,
                                            display: false, // SEMBUNYIKAN TICKS DEFAULT
                                            font: {
                                                size: 11,
                                                weight: 'bold'
                                            },
                                            z: 2
                                        },
                                        pointLabels: {
                                            font: {
                                                size: 14,
                                                weight: '600',
                                            },
                                            color: colors.pointLabels,
                                            padding: 15,
                                            z: 3
                                        },
                                        grid: {
                                            color: colors.grid,
                                            z: 1
                                        },
                                        angleLines: {
                                            color: colors.angleLines,
                                            z: 1
                                        }
                                    }
                                }
                            },
                            plugins: [{
                                id: 'shiftTicks',
                                afterDraw: (chart) => {
                                    const {
                                        ctx,
                                        scales
                                    } = chart;
                                    const scale = scales.r;
                                    const ticks = scale.ticks;
                                    const yCenter = scale.yCenter;
                                    const xCenter = scale.xCenter;

                                    ctx.save();
                                    ctx.font = `bold ${scale.options.ticks.font.size}px sans-serif`;
                                    ctx.fillStyle = scale.options.ticks.color || '#000';
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle';

                                    // Offset untuk menggeser posisi
                                    const offsetX = 10; // geser ke kanan
                                    const offsetY = 0; // tidak geser vertikal

                                    ticks.forEach((tick) => {
                                        const value = tick.value;
                                        const radius = scale.getDistanceFromCenterForValue(value);
                                        const labelY = yCenter - radius - offsetY;
                                        const labelX = xCenter + offsetX;
                                        ctx.fillText(value, labelX, labelY);
                                    });

                                    ctx.restore();
                                }
                            }]
                        });

                        console.log('✅ Score chart initialized');
                    }

                    function waitForLivewire(callback) {
                        if (window.Livewire) callback();
                        else setTimeout(() => waitForLivewire(callback), 100);
                    }

                    waitForLivewire(function() {
                        setupScoreChart();
                        setupLegendListeners('score');
                        updateLegendVisual('score');

                        Livewire.on('chartDataUpdated', function(data) {
                            let chartData = Array.isArray(data) && data.length > 0 ? data[0] : data;
                            if (!chartData || !window.scoreChart_{{ $chartId }}) return;

                            const chart = window.scoreChart_{{ $chartId }};
                            chart.data.datasets[0].data = chartData.individualScores;
                            chart.data.datasets[1].label = 'Standard'; // ← UBAH
                            chart.data.datasets[1].data = chartData.standardScores;
                            chart.data.datasets[2].label = `Tolerance ${chartData.tolerance}%`; // ← UBAH
                            chart.data.datasets[2].data = chartData.originalStandardScores;

                            const colors = getGridColors();
                            chart.options.scales.r.ticks.color = colors.ticks;
                            chart.options.scales.r.pointLabels.color = colors.pointLabels;
                            chart.options.scales.r.grid.color = colors.grid;
                            chart.options.scales.r.angleLines.color = colors.angleLines;
                            chart.options.scales.r.ticks.backdropColor = 'transparent';
                            chart.options.scales.r.ticks.showLabelBackdrop = false;

                            chart.update('active');
                            updateLegendVisual('score');
                        });
                    });

                    const observer = new MutationObserver(() => {
                        if (window.scoreChart_{{ $chartId }}) {
                            const colors = getGridColors();
                            const chart = window.scoreChart_{{ $chartId }};
                            chart.options.scales.r.ticks.color = colors.ticks;
                            chart.options.scales.r.pointLabels.color = colors.pointLabels;
                            chart.options.scales.r.grid.color = colors.grid;
                            chart.options.scales.r.angleLines.color = colors.angleLines;
                            chart.options.scales.r.ticks.backdropColor = 'transparent';
                            chart.options.scales.r.ticks.showLabelBackdrop = false;
                            chart.update('active');
                        }
                    });
                    observer.observe(document.documentElement, {
                        attributes: true,
                        attributeFilter: ['class']
                    });
                })();
            </script>
        </div>

        <!-- CSS FORCE FIX KOTAK PUTIH -->
        <style>
            .chartjs-render-monitor text,
            canvas text,
            .tick text {
                background: transparent !important;
                background-color: transparent !important;
            }
        </style>
    </div>
</div>
