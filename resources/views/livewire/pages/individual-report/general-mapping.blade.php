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
                        <th class="border border-black dark:border-gray-300 px-3 py-2 font-semibold">No</th>
                        <th class="border border-black dark:border-gray-300 px-3 py-2 font-semibold">Atribut/Attribute
                        </th>
                        <th class="border border-black dark:border-gray-300 px-3 py-2 font-semibold">Bobot %<br>200</th>
                        <th class="border border-black dark:border-gray-300 px-3 py-2 font-semibold" colspan="2">
                            <span x-data
                                x-text="$wire.tolerancePercentage > 0 ? 'Standard (-' + $wire.tolerancePercentage + '%)' : 'Standard'"></span>
                        </th>
                        <th class="border border-black dark:border-gray-300 px-3 py-2 font-semibold" colspan="2">
                            Individu</th>
                        <th class="border border-black dark:border-gray-300 px-3 py-2 font-semibold" colspan="2">Gap
                        </th>
                        <th class="border border-black dark:border-gray-300 px-3 py-2 font-semibold">
                            Prosentase<br>Kesesuaian</th>
                        <th class="border border-black dark:border-gray-300 px-3 py-2 font-semibold">
                            Kesimpulan/Conclusion</th>
                    </tr>
                    <tr class="bg-sky-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <th class="border border-black dark:border-gray-300 px-3 py-1"></th>
                        <th class="border border-black dark:border-gray-300 px-3 py-1"></th>
                        <th class="border border-black dark:border-gray-300 px-3 py-1"></th>
                        <th class="border border-black dark:border-gray-300 px-3 py-1 font-semibold">Rating/<br>Level
                        </th>
                        <th class="border border-black dark:border-gray-300 px-3 py-1 font-semibold">Score</th>
                        <th class="border border-black dark:border-gray-300 px-3 py-1 font-semibold">Rating/<br>Level
                        </th>
                        <th class="border border-black dark:border-gray-300 px-3 py-1 font-semibold">Score</th>
                        <th class="border border-black dark:border-gray-300 px-3 py-1 font-semibold">Rating/<br>Level
                        </th>
                        <th class="border border-black dark:border-gray-300 px-3 py-0 font-semibold">Score</th>
                        <th class="border border-black dark:border-gray-300 px-3 py-1"></th>
                        <th class="border border-black dark:border-gray-300 px-3 py-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($aspectsData as $aspect)
                        <tr>
                            <td class="border border-black dark:border-gray-300 px-3 py-2 text-center">
                                {{ $aspect['order'] }}
                            </td>
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
                            <td class="border border-black dark:border-gray-300 px-3 py-2">
                                {{ $aspect['conclusion_text'] }}</td>
                        </tr>
                    @endforeach

                    <!-- Total Rating Row -->
                    <tr class="font-bold bg-sky-100 dark:bg-gray-600">
                        <td class="border border-black dark:border-gray-300 px-3 py-2 text-right" colspan="3">Total
                            Rating</td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2 text-center">
                            {{ number_format($totalStandardRating, 2) }}
                        </td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2"></td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2 text-center">
                            {{ number_format($totalIndividualRating, 2) }}</td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2"></td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2 text-center">
                            {{ number_format($totalGapRating, 2) }}</td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2"></td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2" colspan="2">
                            {{ $overallConclusion }}</td>
                    </tr>

                    <!-- Total Score Row -->
                    <tr class="font-bold bg-sky-100 dark:bg-gray-600">
                        <td class="border border-black dark:border-gray-300 px-3 py-2 text-right" colspan="3">Total
                            Score</td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2"></td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2 text-center">
                            {{ number_format($totalStandardScore, 2) }}
                        </td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2"></td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2 text-center">
                            {{ number_format($totalIndividualScore, 2) }}</td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2"></td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2 text-center">
                            {{ number_format($totalGapScore, 2) }}</td>
                        <td class="border border-black dark:border-gray-300 px-3 py-2" colspan="2">
                            {{ $overallConclusion }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

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

            <!-- Legend dengan klik functionality -->
            <div class="flex justify-center text-sm gap-8 text-gray-900 dark:text-gray-100 mb-8"
                id="rating-legend-{{ $chartId }}">
                <!-- Standard (Index 2 - KUNING) -->
                <span
                    class="legend-item flex items-center gap-2 cursor-pointer select-none hover:bg-gray-100 dark:hover:bg-gray-700 px-3 py-2 rounded-lg transition-all duration-200 border border-gray-300 dark:border-gray-600 shadow-sm"
                    data-chart="rating" data-dataset="2">
                    <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #fafa05;"></span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200">Standard</span>
                </span>
                <!-- Participant (Index 0 - HIJAU) -->
                <span
                    class="legend-item flex items-center gap-2 cursor-pointer select-none hover:bg-gray-100 dark:hover:bg-gray-700 px-3 py-2 rounded-lg transition-all duration-200 border border-gray-300 dark:border-gray-600 shadow-sm"
                    data-chart="rating" data-dataset="0">
                    <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #5db010;"></span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200"
                        style="color: #5db010;">{{ $participant->name }}</span>
                </span>
                <!-- Tolerance (Index 1 - MERAH) -->
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

                    // Initialize visibility state
                    window.ratingVisibility_{{ $chartId }} = {
                        0: false,
                        1: false,
                        2: false
                    }; // false = visible

                    // Toggle dataset function
                    window.toggleDataset_{{ $chartId }} = function(chartType, datasetIndex) {
                        console.log(`🔄 Toggling ${chartType} dataset ${datasetIndex}`);

                        const chart = window[`${chartType}Chart_{{ $chartId }}`];
                        if (!chart) {
                            console.error('❌ Chart not found:', chartType);
                            return;
                        }

                        const dataset = chart.data.datasets[datasetIndex];
                        if (!dataset) {
                            console.error('❌ Dataset not found:', datasetIndex);
                            return;
                        }

                        // Toggle hidden state
                        const wasHidden = dataset.hidden || false;
                        dataset.hidden = !wasHidden;
                        window[`${chartType}Visibility_{{ $chartId }}`][datasetIndex] = !wasHidden;

                        console.log(`✅ ${chartType} dataset ${datasetIndex} ${wasHidden ? 'shown' : 'hidden'}`);

                        chart.update('active');
                        updateLegendVisual(chartType);
                    };

                    // Update legend visual state
                    function updateLegendVisual(chartType) {
                        const legendContainer = document.getElementById(`${chartType}-legend-{{ $chartId }}`);
                        if (!legendContainer) return;

                        const chart = window[`${chartType}Chart_{{ $chartId }}`];
                        if (!chart) return;

                        const legendItems = legendContainer.querySelectorAll('.legend-item');
                        legendItems.forEach(item => {
                            const datasetIndex = parseInt(item.dataset.dataset);
                            const isHidden = chart.data.datasets[datasetIndex]?.hidden || false;

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

                    // Setup legend click listeners
                    function setupLegendListeners(chartType) {
                        const legendContainer = document.getElementById(`${chartType}-legend-{{ $chartId }}`);
                        if (!legendContainer) {
                            console.warn(`⚠️ Legend container not found: ${chartType}-legend-{{ $chartId }}`);
                            return;
                        }

                        legendContainer.removeEventListener('click', handleLegendClick); // Prevent duplicate listeners
                        legendContainer.addEventListener('click', handleLegendClick);

                        function handleLegendClick(e) {
                            const legendItem = e.target.closest('.legend-item');
                            if (!legendItem) return;

                            e.stopPropagation();
                            const datasetIndex = parseInt(legendItem.dataset.dataset);
                            const chartTypeAttr = legendItem.dataset.chart;

                            console.log(`🖱️ Legend clicked: ${chartTypeAttr} dataset ${datasetIndex}`);
                            window.toggleDataset_{{ $chartId }}(chartTypeAttr, datasetIndex);
                        }

                        console.log(`✅ ${chartType} legend listeners setup`);
                    }

                    // Chart setup
                    function setupRatingChart() {
                        if (window.ratingChart_{{ $chartId }}) {
                            window.ratingChart_{{ $chartId }}.destroy();
                        }

                        const chartLabels = @js($chartLabels);
                        let originalStandardRatings = @js($chartOriginalStandardRatings);
                        let standardRatings = @js($chartStandardRatings);
                        const individualRatings = @js($chartIndividualRatings);
                        const participantName = @js($participant->name);
                        let tolerancePercentage = @js($tolerancePercentage);

                        function initChart() {
                            const canvas = document.getElementById('spiderRatingChart-{{ $chartId }}');
                            if (!canvas) {
                                console.error('❌ Canvas not found: spiderRatingChart-{{ $chartId }}');
                                return;
                            }

                            const ctx = canvas.getContext('2d');

                            const datasets = [{
                                    label: participantName,
                                    data: individualRatings,
                                    fill: true,
                                    backgroundColor: '#5db010',
                                    borderColor: '#8fd006',
                                    pointBackgroundColor: '#8fd006',
                                    pointBorderColor: '#fff',
                                    pointHoverBackgroundColor: '#fff',
                                    pointHoverBorderColor: '#8fd006',
                                    borderWidth: 2.5,
                                    pointRadius: 4,
                                    pointBorderWidth: 2,
                                    hidden: window.ratingVisibility_{{ $chartId }}[0],
                                    datalabels: {
                                        color: '#000000',
                                        backgroundColor: '#5db010',
                                        borderRadius: 4,
                                        padding: {
                                            top: 4,
                                            bottom: 4,
                                            left: 6,
                                            right: 6
                                        },
                                        font: {
                                            weight: 'bold',
                                            size: 10
                                        },
                                        anchor: 'end',
                                        align: 'end',
                                        offset: 6,
                                        formatter: (value) => value.toFixed(2)
                                    }
                                },
                                {
                                    label: `Tolerance ${tolerancePercentage}%`,
                                    data: standardRatings,
                                    fill: true,
                                    backgroundColor: '#b50505',
                                    borderColor: '#b50505',
                                    borderWidth: 2,
                                    pointRadius: 3,
                                    pointBackgroundColor: '#9a0404',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2,
                                    hidden: window.ratingVisibility_{{ $chartId }}[1],
                                    datalabels: {
                                        color: '#FFFFFF',
                                        backgroundColor: '#b50505',
                                        borderRadius: 4,
                                        padding: {
                                            top: 4,
                                            bottom: 4,
                                            left: 6,
                                            right: 6
                                        },
                                        font: {
                                            weight: 'bold',
                                            size: 9
                                        },
                                        anchor: 'end',
                                        align: 'start',
                                        offset: 0,
                                        formatter: (value) => value.toFixed(2)
                                    }
                                },
                                {
                                    label: 'Standard',
                                    data: originalStandardRatings,
                                    fill: true,
                                    backgroundColor: '#fafa05',
                                    borderColor: '#e6d105',
                                    pointBackgroundColor: '#e6d105',
                                    pointBorderColor: '#fff',
                                    pointHoverBackgroundColor: '#fff',
                                    pointHoverBorderColor: '#e6d105',
                                    borderWidth: 2.5,
                                    pointRadius: 4,
                                    pointBorderWidth: 2,
                                    hidden: window.ratingVisibility_{{ $chartId }}[2],
                                    datalabels: {
                                        color: '#000000',
                                        backgroundColor: '#fafa05',
                                        borderRadius: 4,
                                        padding: {
                                            top: 4,
                                            bottom: 4,
                                            left: 6,
                                            right: 6
                                        },
                                        font: {
                                            weight: 'bold',
                                            size: 10
                                        },
                                        anchor: 'center',
                                        align: 'center',
                                        offset: 6,
                                        formatter: (value) => value.toFixed(2)
                                    }
                                }
                            ];

                            const chartInstance = new Chart(ctx, {
                                type: 'radar',
                                data: {
                                    labels: chartLabels,
                                    datasets: datasets
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            display: false
                                        },
                                        tooltip: {
                                            enabled: true
                                        },
                                        datalabels: {
                                            display: function(context) {
                                                return context.dataset.hidden !== true;
                                            }
                                        }
                                    },
                                    scales: {
                                        r: {
                                            beginAtZero: true,
                                            min: 0,
                                            max: 5,
                                            ticks: {
                                                stepSize: 1,
                                                color: '#000000',
                                                font: {
                                                    size: 11,
                                                    weight: 'bold'
                                                }
                                            },
                                            pointLabels: {
                                                font: {
                                                    size: 11,
                                                    weight: '600'
                                                },
                                                color: '#000000'
                                            },
                                            grid: {
                                                color: 'rgba(0, 0, 0, 0.15)'
                                            },
                                            angleLines: {
                                                color: 'rgba(0, 0, 0, 0.15)'
                                            }
                                        }
                                    }
                                }
                            });

                            window.ratingChart_{{ $chartId }} = chartInstance;
                            console.log('✅ Rating chart initialized');

                            // Setup legend listeners after chart is ready
                            setTimeout(() => {
                                setupLegendListeners('rating');
                                updateLegendVisual('rating');
                            }, 200);
                        }

                        function waitForLivewire(callback) {
                            if (window.Livewire) {
                                callback();
                            } else {
                                setTimeout(() => waitForLivewire(callback), 100);
                            }
                        }

                        waitForLivewire(function() {
                            initChart();

                            Livewire.on('chartDataUpdated', function(data) {
                                let chartData = Array.isArray(data) && data.length > 0 ? data[0] : data;
                                if (window.ratingChart_{{ $chartId }} && chartData) {
                                    console.log('📊 Updating rating chart data');
                                    tolerancePercentage = chartData.tolerance;
                                    originalStandardRatings = chartData.originalStandardRatings;
                                    standardRatings = chartData.standardRatings;

                                    const chart = window.ratingChart_{{ $chartId }};
                                    chart.data.datasets[1].label = `Tolerance ${tolerancePercentage}%`;
                                    chart.data.datasets[1].data = chartData.standardRatings;
                                    chart.data.datasets[2].data = chartData.originalStandardRatings;

                                    // Preserve visibility state
                                    chart.data.datasets[0].hidden = window
                                        .ratingVisibility_{{ $chartId }}[0];
                                    chart.data.datasets[1].hidden = window
                                        .ratingVisibility_{{ $chartId }}[1];
                                    chart.data.datasets[2].hidden = window
                                        .ratingVisibility_{{ $chartId }}[2];

                                    chart.update('active');
                                    setTimeout(() => updateLegendVisual('rating'), 100);
                                }
                            });
                        });
                    }

                    setupRatingChart();
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

            <!-- Legend dengan klik functionality -->
            <div class="flex justify-center text-sm gap-8 text-gray-900 dark:text-gray-100 mb-8"
                id="score-legend-{{ $chartId }}">
                <!-- Standard (Index 2 - KUNING) -->
                <span
                    class="legend-item flex items-center gap-2 cursor-pointer select-none hover:bg-gray-100 dark:hover:bg-gray-700 px-3 py-2 rounded-lg transition-all duration-200 border border-gray-300 dark:border-gray-600 shadow-sm"
                    data-chart="score" data-dataset="2">
                    <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #fafa05;"></span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200">Standard</span>
                </span>
                <!-- Participant (Index 0 - HIხ

System: Apologies, it seems the response was cut off due to length. I'll continue from where it left off, ensuring dark mode support is added without changing the code structure.

```html
                <!-- Participant (Index 0 - HIJAU) -->
                <span
                    class="legend-item flex items-center gap-2 cursor-pointer select-none hover:bg-gray-100 dark:hover:bg-gray-700 px-3 py-2 rounded-lg transition-all duration-200 border border-gray-300 dark:border-gray-600 shadow-sm"
                    data-chart="score" data-dataset="0">
                    <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #5db010;"></span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200"
                        style="color: #5db010;">{{ $participant->name }}</span>
                </span>
                <!-- Tolerance (Index 1 - MERAH) -->
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
                    if (window['chartSetup_{{ $chartId }}']) return;
                    window['chartSetup_{{ $chartId }}'] = true;

                    // 🌙 DYNAMIC DARK MODE
                    function getGridColors() {
                        const isDark = document.documentElement.classList.contains('dark');
                        return {
                            grid: isDark ? 'rgba(255, 255, 255, 0.15)' : 'rgba(0, 0, 0, 0.15)',
                            angleLines: isDark ? 'rgba(255, 255, 255, 0.15)' : 'rgba(0, 0, 0, 0.15)',
                            ticks: isDark ? '#e5e7eb' : '#000000',
                            pointLabels: isDark ? '#f9fafb' : '#000000'
                        };
                    }

                    // VISIBILITY STATE
                    window.ratingVisibility_{{ $chartId }} = {
                        0: false,
                        1: false,
                        2: false
                    };
                    window.scoreVisibility_{{ $chartId }} = {
                        0: false,
                        1: false,
                        2: false
                    };

                    // TOGGLE DATASET (SHARED)
                    window.toggleDataset_{{ $chartId }} = function(chartType, datasetIndex) {
                        const chart = window[`${chartType}Chart_{{ $chartId }}`];
                        if (!chart || !chart.data.datasets[datasetIndex]) return;
                        const wasHidden = chart.data.datasets[datasetIndex].hidden || false;
                        chart.data.datasets[datasetIndex].hidden = !wasHidden;
                        window[`${chartType}Visibility_{{ $chartId }}`][datasetIndex] = !wasHidden;
                        chart.update('active');
                        updateLegendVisual(chartType);
                    };

                    // UPDATE LEGEND VISUAL (SHARED)
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

                    // SETUP LEGEND LISTENERS (SHARED)
                    function setupLegendListeners(chartType) {
                        const legendContainer = document.getElementById(`${chartType}-legend-{{ $chartId }}`);
                        if (!legendContainer) return;
                        legendContainer.onclick = (e) => {
                            const item = e.target.closest('.legend-item');
                            if (!item) return;
                            window.toggleDataset_{{ $chartId }}(chartType, parseInt(item.dataset.dataset));
                        };
                    }

                    // RATING CHART
                    function setupRatingChart() {
                        if (window.ratingChart_{{ $chartId }}) window.ratingChart_{{ $chartId }}.destroy();

                        const chartLabels = @js($chartLabels);
                        let originalStandardRatings = @js($chartOriginalStandardRatings);
                        let standardRatings = @js($chartStandardRatings);
                        const individualRatings = @js($chartIndividualRatings);
                        const participantName = @js($participant->name);
                        let tolerancePercentage = @js($tolerancePercentage);

                        const canvas = document.getElementById('spiderRatingChart-{{ $chartId }}');
                        if (!canvas) return;
                        const ctx = canvas.getContext('2d');
                        const colors = getGridColors();

                        const datasets = [{
                                label: participantName,
                                data: individualRatings,
                                fill: true,
                                backgroundColor: '#5db010',
                                borderColor: '#8fd006',
                                pointBackgroundColor: '#8fd006',
                                pointBorderColor: '#fff',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                hidden: false,
                                datalabels: {
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
                                backgroundColor: '#b50505',
                                borderColor: '#b50505',
                                borderWidth: 2,
                                pointRadius: 3,
                                hidden: false,
                                datalabels: {
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
                                backgroundColor: '#fafa05',
                                borderColor: '#e6d105',
                                pointBackgroundColor: '#e6d105',
                                pointBorderColor: '#fff',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                hidden: false,
                                datalabels: {
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
                                            font: {
                                                size: 11,
                                                weight: 'bold'
                                            }
                                        },
                                        pointLabels: {
                                            font: {
                                                size: 11,
                                                weight: '600'
                                            },
                                            color: colors.pointLabels
                                        },
                                        grid: {
                                            color: colors.grid
                                        },
                                        angleLines: {
                                            color: colors.angleLines
                                        }
                                    }
                                }
                            }
                        });

                        setTimeout(() => {
                            setupLegendListeners('rating');
                            updateLegendVisual('rating');
                        }, 200);
                    }

                    // SCORE CHART
                    function setupScoreChart() {
                        if (window.scoreChart_{{ $chartId }}) window.scoreChart_{{ $chartId }}.destroy();

                        const chartLabels = @js($chartLabels);
                        let originalStandardScores = @js($chartOriginalStandardScores);
                        let standardScores = @js($chartStandardScores);
                        const individualScores = @js($chartIndividualScores);
                        const participantName = @js($participant->name);
                        let tolerancePercentage = @js($tolerancePercentage);

                        const canvas = document.getElementById('spiderScoreChart-{{ $chartId }}');
                        if (!canvas) return;
                        const ctx = canvas.getContext('2d');
                        const colors = getGridColors();
                        const maxScore = Math.max(...originalStandardScores, ...individualScores, ...standardScores) * 1.2;

                        const datasets = [{
                                label: participantName,
                                data: individualScores,
                                fill: true,
                                backgroundColor: '#5db010',
                                borderColor: '#8fd006',
                                pointBackgroundColor: '#8fd006',
                                pointBorderColor: '#fff',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                hidden: false,
                                datalabels: {
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
                                backgroundColor: '#b50505',
                                borderColor: '#b50505',
                                borderWidth: 2,
                                pointRadius: 3,
                                hidden: false,
                                datalabels: {
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
                                backgroundColor: '#fafa05',
                                borderColor: '#e6d105',
                                pointBackgroundColor: '#e6d105',
                                pointBorderColor: '#fff',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                hidden: false,
                                datalabels: {
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
                                            font: {
                                                size: 11,
                                                weight: 'bold'
                                            }
                                        },
                                        pointLabels: {
                                            font: {
                                                size: 11,
                                                weight: '600'
                                            },
                                            color: colors.pointLabels
                                        },
                                        grid: {
                                            color: colors.grid
                                        },
                                        angleLines: {
                                            color: colors.angleLines
                                        }
                                    }
                                }
                            }
                        });

                        setTimeout(() => {
                            setupLegendListeners('score');
                            updateLegendVisual('score');
                        }, 200);
                    }

                    // LIVEWIRE INTEGRATION
                    function waitForLivewire(callback) {
                        if (window.Livewire) callback();
                        else setTimeout(() => waitForLivewire(callback), 100);
                    }

                    waitForLivewire(function() {
                        setupRatingChart();
                        setupScoreChart();

                        Livewire.on('chartDataUpdated', function(data) {
                            let chartData = Array.isArray(data) && data.length > 0 ? data[0] : data;
                            if (!chartData) return;

                            // UPDATE RATING
                            if (window.ratingChart_{{ $chartId }}) {
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

                                chart.update('active');
                                updateLegendVisual('rating');
                            }

                            // UPDATE SCORE
                            if (window.scoreChart_{{ $chartId }}) {
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

                                chart.update('active');
                                updateLegendVisual('score');
                            }
                        });
                    });

                    // SINGLE DARK MODE LISTENER UNTUK KEDUA CHART
                    const observer = new MutationObserver(() => {
                        const colors = getGridColors();

                        // UPDATE RATING
                        if (window.ratingChart_{{ $chartId }}) {
                            const chart = window.ratingChart_{{ $chartId }};
                            chart.options.scales.r.ticks.color = colors.ticks;
                            chart.options.scales.r.pointLabels.color = colors.pointLabels;
                            chart.options.scales.r.grid.color = colors.grid;
                            chart.options.scales.r.angleLines.color = colors.angleLines;
                            chart.update('active');
                        }

                        // UPDATE SCORE
                        if (window.scoreChart_{{ $chartId }}) {
                            const chart = window.scoreChart_{{ $chartId }};
                            chart.options.scales.r.ticks.color = colors.ticks;
                            chart.options.scales.r.pointLabels.color = colors.pointLabels;
                            chart.options.scales.r.grid.color = colors.grid;
                            chart.options.scales.r.angleLines.color = colors.angleLines;
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
    </div>
</div>
