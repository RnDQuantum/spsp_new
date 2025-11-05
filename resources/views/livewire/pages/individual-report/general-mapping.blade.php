<div>
    <div class="bg-white dark:bg-gray-800 mx-auto my-8 shadow overflow-hidden" style="max-width: 1400px;">
        <!-- Header -->
        <div class="border-b-4 border-black dark:border-gray-300 py-3 bg-sky-200 dark:bg-gray-700">
            <h1 class="text-center text-lg font-bold uppercase tracking-wide text-gray-900 dark:text-gray-100">
                GENERAL MAPPING
            </h1>
            <p class="text-center text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">
                {{ $participant->name }}
            </p>
            <p class="text-center text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">
                {{ $participant->event->name }}
            </p>
            <p class="text-center text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">
                {{ $participant->positionFormation->name }} - {{ $participant->positionFormation->template->name }}
            </p>
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
                    <tr class="bg-sky-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <th class="border border-black dark:border-gray-300 px-3 py-2 font-semibold" rowspan="2">No
                        </th>
                        <th class="border border-black dark:border-gray-300 px-3 py-2 font-semibold" rowspan="2">
                            Atribut/Attribute
                        </th>
                        <th class="border border-black dark:border-gray-300 px-3 py-2 font-semibold" rowspan="2">
                            Bobot %<br>200</th>
                        <th class="border border-black dark:border-gray-300 px-3 py-2 font-semibold" colspan="2">
                            <span x-data
                                x-text="$wire.tolerancePercentage > 0 ? 'Standard (-' + $wire.tolerancePercentage + '%)' : 'Standard'"></span>
                        </th>
                        <th class="border border-black dark:border-gray-300 px-3 py-2 font-semibold" colspan="2">
                            Individu</th>
                        <th class="border border-black dark:border-gray-300 px-3 py-2 font-semibold" colspan="2">Gap
                        </th>
                        <th class="border border-black dark:border-gray-300 px-3 py-2 font-semibold" rowspan="2">
                            Prosentase<br>Kesesuaian</th>
                        <th class="border border-black dark:border-gray-300 px-3 py-2 font-semibold" rowspan="2">
                            Kesimpulan/Conclusion</th>
                    </tr>
                    <tr class="bg-sky-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <th class="border border-black dark:border-gray-300 px-3 py-1 font-semibold">Rating/<br>Level
                        </th>
                        <th class="border border-black dark:border-gray-300 px-3 py-1 font-semibold">Score</th>
                        <th class="border border-black dark:border-gray-300 px-3 py-1 font-semibold">Rating/<br>Level
                        </th>
                        <th class="border border-black dark:border-gray-300 px-3 py-1 font-semibold">Score</th>
                        <th class="border border-black dark:border-gray-300 px-3 py-1 font-semibold">Rating/<br>Level
                        </th>
                        <th class="border border-black dark:border-gray-300 px-3 py-0 font-semibold">Score</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($aspectsData as $aspect)
                        <tr>
                            <td class="border border-black dark:border-gray-300 px-3 py-2 text-center">
                                {{ $aspect['order'] }}</td>
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
                            <td
                                class="border border-white px-3 py-2 text-center
                                @php
$c = trim(strtoupper($aspect['conclusion_text'])); @endphp

                                @if ($c === 'DI ATAS STANDAR') bg-green-600 text-white font-bold
                                @elseif ($c === 'MEMENUHI STANDAR')
                                    bg-yellow-400 text-gray-900 font-bold
                                @elseif ($c === 'DI BAWAH STANDAR')
                                    bg-red-600 text-white font-bold
                                @else
                                    bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white @endif">
                                {{ $aspect['conclusion_text'] }}
                            </td>
                        </tr>
                    @endforeach

                    <!-- Total Rating Row -->
                    <tr class="font-bold bg-sky-100 dark:bg-gray-600">
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
                        <td class="border border-white px-3 py-2 text-center font-bold
                            @php
$c = trim(strtoupper($overallConclusion)); @endphp

                            @if ($c === 'DI ATAS STANDAR') bg-green-600 text-white
                            @elseif ($c === 'MEMENUHI STANDAR') bg-yellow-400 text-gray-900
                            @elseif ($c === 'DI BAWAH STANDAR') bg-red-600 text-white
                            @else bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white @endif"
                            colspan="2">
                            {{ $overallConclusion }}
                        </td>
                    </tr>

                    <!-- Total Score Row -->
                    <tr class="font-bold bg-sky-100 dark:bg-gray-600">
                        <td class="border border-black dark:border-gray-300 px-3 py-2 text-center" colspan="3">Total
                            Score</td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2 bg-black"></td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2 text-center">
                            {{ number_format($totalStandardScore, 2) }}</td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2 bg-black"></td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2 text-center">
                            {{ number_format($totalIndividualScore, 2) }}</td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2 bg-black"></td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2 text-center">
                            {{ number_format($totalGapScore, 2) }}</td>
                        <td class="border border-white px-3 py-2 text-center font-bold
                            @php
$c = trim(strtoupper($overallConclusion)); @endphp

                            @if ($c === 'DI ATAS STANDAR') bg-green-600 text-white
                            @elseif ($c === 'MEMENUHI STANDAR') bg-yellow-400 text-gray-900
                            @elseif ($c === 'DI BAWAH STANDAR') bg-red-600 text-white
                            @else bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white @endif"
                            colspan="2">
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
                <div
                    class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-700 border-t-2 border-black dark:border-gray-600">
                    <div class="max-w-5xl mx-auto">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z">
                                </path>
                            </svg>
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
                                <div class="text-4xl font-bold text-indigo-600 dark:text-indigo-400">
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
                                <div class="text-sm font-bold text-indigo-600 dark:text-indigo-400">
                                    Potensi: {{ $rankingInfo['potensi_weight'] }}%
                                </div>
                                <div class="text-sm font-bold text-indigo-600 dark:text-indigo-400">
                                    Kompetensi: {{ $rankingInfo['kompetensi_weight'] }}%
                                </div>
                            </div>

                            <!-- Conclusion Card -->
                            <div
                                class="bg-white dark:bg-gray-800 border-2 rounded-lg p-4 text-center
                            @php
$conclusion = strtoupper(trim($rankingInfo['conclusion'])); @endphp
                            @if ($conclusion === 'DI ATAS STANDAR') border-green-300 dark:border-green-600
                            @elseif ($conclusion === 'MEMENUHI STANDAR')
                                border-yellow-300 dark:border-yellow-600
                            @else
                                border-red-300 dark:border-red-600 @endif
                        ">
                                <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Status</div>
                                <div
                                    class="text-base font-bold px-3 py-2 rounded-lg
                                @if ($conclusion === 'DI ATAS STANDAR') bg-green-600 text-white
                                @elseif ($conclusion === 'MEMENUHI STANDAR')
                                    bg-yellow-400 text-gray-900
                                @else
                                    bg-red-600 text-white @endif
                            ">
                                    {{ $rankingInfo['conclusion'] }}
                                </div>
                            </div>
                        </div>

                        <!-- Additional Info -->
                        <div
                            class="mt-4 bg-blue-100 dark:bg-blue-800 border border-blue-200 dark:border-blue-600 rounded-lg p-3">
                            <div class="text-sm text-blue-800 dark:text-blue-200">
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
            <div class="flex justify-center mb-6">
                <div style="width: 900px; height: 900px; position: relative;">
                    <canvas id="spiderRatingChart-{{ $chartId }}"></canvas>
                </div>
            </div>

            <!-- Legend -->
            <div class="flex justify-center text-sm gap-8 text-gray-900 dark:text-gray-100 mb-8"
                id="rating-legend-{{ $chartId }}">
                <span
                    class="legend-item flex items-center gap-2 cursor-pointer select-none hover:bg-gray-100 dark:hover:bg-gray-700 px-3 py-2 rounded-lg transition-all duration-200 border border-gray-300 dark:border-gray-600 shadow-sm"
                    data-chart="rating" data-dataset="2">
                    <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #fafa05;"></span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200">Standard</span>
                </span>
                <span
                    class="legend-item flex items-center gap-2 cursor-pointer select-none hover:bg-gray-100 dark:hover:bg-gray-700 px-3 py-2 rounded-lg transition-all duration-200 border border-gray-300 dark:border-gray-600 shadow-sm"
                    data-chart="rating" data-dataset="0">
                    <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #5db010;"></span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200"
                        style="color: #5db010;">{{ $participant->name }}</span>
                </span>
                <span
                    class="legend-item flex items-center gap-2 cursor-pointer select-none hover:bg-gray-100 dark:hover:bg-gray-700 px-3 py-2 rounded-lg transition-all duration-200 border border-gray-300 dark:border-gray-600 shadow-sm"
                    data-chart="rating" data-dataset="1">
                    <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #b50505;"></span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200" x-data
                        x-text="'Tolerance ' + $wire.tolerancePercentage + '%'"></span>
                </span>
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
                                label: participantName,
                                data: individualRatings,
                                fill: true,
                                backgroundColor: 'rgba(93, 176, 16, 0.7)', // Semi-transparan untuk visibilitas grid
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
                                label: `Tolerance ${tolerancePercentage}%`,
                                data: standardRatings,
                                fill: true,
                                backgroundColor: 'rgba(181, 5, 5, 0.7)', // Semi-transparan untuk visibilitas grid
                                borderColor: '#b50505',
                                borderWidth: 2,
                                pointRadius: 3,
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
                                label: 'Standard',
                                data: originalStandardRatings,
                                fill: true,
                                backgroundColor: 'rgba(250, 250, 5, 0.7)', // Semi-transparan untuk visibilitas grid
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
                                            font: {
                                                size: 11,
                                                weight: 'bold'
                                            },
                                            z: 2 // Tambahkan z-index untuk ticks
                                        },
                                        pointLabels: {
                                            font: {
                                                size: 11,
                                                weight: '600'
                                            },
                                            color: colors.pointLabels,
                                            z: 3 // Tambahkan z-index untuk point labels
                                        },
                                        grid: {
                                            color: colors.grid,
                                            z: 1 // Tambahkan z-index untuk grid
                                        },
                                        angleLines: {
                                            color: colors.angleLines,
                                            z: 1 // Tambahkan z-index untuk angle lines
                                        }
                                    }
                                }
                            }
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
                            chart.data.datasets[1].label = `Tolerance ${chartData.tolerance}%`;
                            chart.data.datasets[1].data = chartData.standardRatings;
                            chart.data.datasets[2].data = chartData.originalStandardRatings;
                            chart.data.datasets.forEach((d, i) => d.hidden = window
                                .ratingVisibility_{{ $chartId }}[i]);

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
            <div class="flex justify-center mb-6">
                <div style="width: 900px; height: 900px; position: relative;">
                    <canvas id="spiderScoreChart-{{ $chartId }}"></canvas>
                </div>
            </div>

            <!-- Legend -->
            <div class="flex justify-center text-sm gap-8 text-gray-900 dark:text-gray-100 mb-8"
                id="score-legend-{{ $chartId }}">
                <span
                    class="legend-item flex items-center gap-2 cursor-pointer select-none hover:bg-gray-100 dark:hover:bg-gray-700 px-3 py-2 rounded-lg transition-all duration-200 border border-gray-300 dark:border-gray-600 shadow-sm"
                    data-chart="score" data-dataset="2">
                    <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #fafa05;"></span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200">Standard</span>
                </span>
                <span
                    class="legend-item flex items-center gap-2 cursor-pointer select-none hover:bg-gray-100 dark:hover:bg-gray-700 px-3 py-2 rounded-lg transition-all duration-200 border border-gray-300 dark:border-gray-600 shadow-sm"
                    data-chart="score" data-dataset="0">
                    <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #5db010;"></span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200"
                        style="color: #5db010;">{{ $participant->name }}</span>
                </span>
                <span
                    class="legend-item flex items-center gap-2 cursor-pointer select-none hover:bg-gray-100 dark:hover:bg-gray-700 px-3 py-2 rounded-lg transition-all duration-200 border border-gray-300 dark:border-gray-600 shadow-sm"
                    data-chart="score" data-dataset="1">
                    <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #b50505;"></span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200" x-data
                        x-text="'Tolerance ' + $wire.tolerancePercentage + '%'"></span>
                </span>
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
                                backgroundColor: 'rgba(93, 176, 16, 0.7)', // Semi-transparan untuk visibilitas grid
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
                                label: `Tolerance ${tolerancePercentage}%`,
                                data: standardScores,
                                fill: true,
                                backgroundColor: 'rgba(181, 5, 5, 0.7)', // Semi-transparan untuk visibilitas grid
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
                                label: 'Standard',
                                data: originalStandardScores,
                                fill: true,
                                backgroundColor: 'rgba(250, 250, 5, 0.7)', // Semi-transparan untuk visibilitas grid
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
                                            font: {
                                                size: 11,
                                                weight: 'bold'
                                            },
                                            z: 2 // Tambahkan z-index untuk ticks
                                        },
                                        pointLabels: {
                                            font: {
                                                size: 11,
                                                weight: '600'
                                            },
                                            color: colors.pointLabels,
                                            z: 3 // Tambahkan z-index untuk point labels
                                        },
                                        grid: {
                                            color: colors.grid,
                                            z: 1 // Tambahkan z-index untuk grid
                                        },
                                        angleLines: {
                                            color: colors.angleLines,
                                            z: 1 // Tambahkan z-index untuk angle lines
                                        }
                                    }
                                }
                            }
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
                            chart.data.datasets[1].label = `Tolerance ${chartData.tolerance}%`;
                            chart.data.datasets[1].data = chartData.standardScores;
                            chart.data.datasets[2].data = chartData.originalStandardScores;
                            chart.data.datasets.forEach((d, i) => d.hidden = window
                                .scoreVisibility_{{ $chartId }}[i]);

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
