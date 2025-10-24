<div>
    <div class="mx-auto my-8 shadow overflow-hidden" style="max-width: 1400px;" class="bg-white dark:bg-gray-800">

        @if ($showHeader)
            <!-- Header - DARK MODE READY -->
            <div class="border-b-4 border-black py-3 bg-sky-200 dark:bg-gray-700">
                <h1
                    class="text-center text-lg font-bold uppercase tracking-wide
                           text-gray-900 dark:text-white">
                    PSYCHOLOGY MAPPING
                </h1>
                <p
                    class="text-center text-sm font-semibold
                         text-gray-700 dark:text-gray-200 mt-1">
                    {{ $participant->name }}
                </p>
                <p
                    class="text-center text-sm font-semibold
                         text-gray-700 dark:text-gray-200 mt-1">
                    {{ $participant->event->name }}
                </p>
                <p
                    class="text-center text-sm font-semibold
                         text-gray-700 dark:text-gray-200 mt-1">
                    {{ $participant->positionFormation->name }} - {{ $participant->positionFormation->template->name }}
                </p>
            </div>
        @endif

        @if ($showInfoSection)
            <!-- Tolerance Selector Component -->
            @php
                $summary = $this->getPassingSummary();
            @endphp
            @livewire('components.tolerance-selector', [
                'passing' => $summary['passing'],
                'total' => $summary['total'],
            ])
        @endif

        @if ($showTable)
            <!-- Table Section - DARK MODE READY -->
            <div class="p-4 overflow-x-auto bg-white dark:bg-gray-800">
                <table class="min-w-full border border-black text-xs">
                    <thead>
                        <tr class="bg-sky-200 dark:bg-gray-700 text-gray-900 dark:text-white">
                            <th
                                class="border border-black px-3 py-2 font-semibold 
                                   bg-white dark:bg-gray-800">
                                No</th>
                            <th
                                class="border border-black px-3 py-2 font-semibold 
                                   bg-white dark:bg-gray-800">
                                Atribut/Attribute</th>
                            <th
                                class="border border-black px-3 py-2 font-semibold 
                                   bg-white dark:bg-gray-800">
                                Bobot %<br>100</th>
                            <th class="border border-black px-3 py-2 font-semibold 
                                   bg-white dark:bg-gray-800"
                                colspan="2">
                                <span x-data
                                    x-text="$wire.tolerancePercentage > 0 ? 'Standard (-' + $wire.tolerancePercentage + '%)' : 'Standard'"
                                    class="text-gray-900 dark:text-white"></span>
                            </th>
                            <th class="border border-black px-3 py-2 font-semibold 
                                   bg-white dark:bg-gray-800"
                                colspan="2">Individu</th>
                            <th class="border border-black px-3 py-2 font-semibold 
                                   bg-white dark:bg-gray-800"
                                colspan="2">Gap</th>
                            <th
                                class="border border-black px-3 py-2 font-semibold 
                                   bg-white dark:bg-gray-800">
                                Prosentase<br>Kesesuaian</th>
                            <th
                                class="border border-black px-3 py-2 font-semibold 
                                   bg-white dark:bg-gray-800">
                                Kesimpulan/Conclusion</th>
                        </tr>
                        <tr class="bg-sky-200 dark:bg-gray-700 text-gray-900 dark:text-white">
                            <th
                                class="border border-black px-3 py-1 
                                   bg-white dark:bg-gray-800">
                            </th>
                            <th
                                class="border border-black px-3 py-1 
                                   bg-white dark:bg-gray-800">
                            </th>
                            <th
                                class="border border-black px-3 py-1 
                                   bg-white dark:bg-gray-800">
                            </th>
                            <th
                                class="border border-black px-3 py-1 font-semibold 
                                   bg-white dark:bg-gray-800">
                                Rating/<br>Level</th>
                            <th
                                class="border border-black px-3 py-1 font-semibold 
                                   bg-white dark:bg-gray-800">
                                Score</th>
                            <th
                                class="border border-black px-3 py-1 font-semibold 
                                   bg-white dark:bg-gray-800">
                                Rating/<br>Level</th>
                            <th
                                class="border border-black px-3 py-1 font-semibold 
                                   bg-white dark:bg-gray-800">
                                Score</th>
                            <th
                                class="border border-black px-3 py-1 font-semibold 
                                   bg-white dark:bg-gray-800">
                                Rating/<br>Level</th>
                            <th
                                class="border border-black px-3 py-0 font-semibold 
                                   bg-white dark:bg-gray-800">
                                Score</th>
                            <th
                                class="border border-black px-3 py-1 
                                   bg-white dark:bg-gray-800">
                            </th>
                            <th
                                class="border border-black px-3 py-1 
                                   bg-white dark:bg-gray-800">
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($aspectsData as $index => $aspect)
                            <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td
                                    class="border border-black px-3 py-2 text-center 
                                       text-gray-900 dark:text-white">
                                    {{ ['I', 'II', 'III', 'IV', 'V'][$index] }}
                                </td>
                                <td
                                    class="border border-black px-3 py-2 
                                       text-gray-900 dark:text-white">
                                    {{ $aspect['name'] }}</td>
                                <td
                                    class="border border-black px-3 py-2 text-center 
                                       text-gray-900 dark:text-white">
                                    {{ $aspect['weight_percentage'] }}</td>
                                <td
                                    class="border border-black px-3 py-2 text-center 
                                       text-gray-900 dark:text-white">
                                    {{ number_format($aspect['standard_rating'], 2) }}</td>
                                <td
                                    class="border border-black px-3 py-2 text-center 
                                       text-gray-900 dark:text-white">
                                    {{ number_format($aspect['standard_score'], 2) }}</td>
                                <td
                                    class="border border-black px-3 py-2 text-center 
                                       text-gray-900 dark:text-white">
                                    {{ number_format($aspect['individual_rating'], 2) }}</td>
                                <td
                                    class="border border-black px-3 py-2 text-center 
                                       text-gray-900 dark:text-white">
                                    {{ number_format($aspect['individual_score'], 2) }}</td>
                                <td
                                    class="border border-black px-3 py-2 text-center 
                                       text-gray-900 dark:text-white">
                                    {{ number_format($aspect['gap_rating'], 2) }}</td>
                                <td
                                    class="border border-black px-3 py-2 text-center 
                                       text-gray-900 dark:text-white">
                                    {{ number_format($aspect['gap_score'], 2) }}</td>
                                <td
                                    class="border border-black px-3 py-2 text-center 
                                       text-gray-900 dark:text-white">
                                    @php
                                        $percentage =
                                            $aspect['standard_score'] > 0
                                                ? round(($aspect['individual_score'] / $aspect['standard_score']) * 100)
                                                : 0;
                                    @endphp
                                    {{ $percentage }}%
                                </td>
                                <td
                                    class="border border-black px-3 py-2 text-center
                                @php
// Normalisasi: trim + uppercase → aman dari spasi & case
                                    $c = trim(strtoupper($aspect['conclusion_text'])); @endphp

                                @if ($c === 'LEBIH MEMENUHI/MORE REQUIREMENT') bg-green-500 text-black font-bold
                                @elseif ($c === 'MEMENUHI/MEET REQUIREMENT')
                                    bg-yellow-400 text-black font-bold
                                @elseif ($c === 'KURANG MEMENUHI/BELOW REQUIREMENT')
                                    bg-orange-500 text-black font-bold
                                @elseif ($c === 'BELUM MEMENUHI/UNDER PERFORM')
                                    bg-red-600 text-black font-bold
                                @else
                                    bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white @endif">
                                    {{ $aspect['conclusion_text'] }}
                                </td>
                            </tr>
                        @endforeach

                        <!-- Total Rating Row -->
                        <tr class="font-bold bg-sky-100 dark:bg-gray-600">
                            <td class="border border-black px-3 py-2 text-center 
                                   text-gray-900 dark:text-white"
                                colspan="3">Total Rating</td>
                            <td
                                class="border border-black px-3 py-2 text-center 
                                   text-gray-900 dark:text-white">
                                {{ number_format($totalStandardRating, 2) }}
                            </td>
                            <td class="border border-black px-3 py-2 bg-black"></td>
                            <td
                                class="border border-black px-3 py-2 text-center 
                                   text-gray-900 dark:text-white">
                                {{ number_format($totalIndividualRating, 2) }}</td>
                            <td class="border border-black px-3 py-2 bg-black"></td>
                            <td
                                class="border border-black px-3 py-2 text-center 
                                   text-gray-900 dark:text-white">
                                {{ number_format($totalGapRating, 2) }}</td>
                            <td class="border border-black px-3 py-2 bg-black"></td>
                            <td class="border border-black px-3 py-2 
                                text-black
                                @php
$c = trim(strtoupper($overallConclusion)); @endphp
                                @if ($c === 'MEMENUHI STANDAR/MEET REQUIREMENT STANDARD') bg-green-500 text-black
                                @elseif ($c === 'KURANG MEMENUHI STANDAR/BELOW REQUIREMENT STANDARD')
                                    bg-red-600 text-black @endif"
                                colspan="2">
                                {{ $overallConclusion }}
                            </td>
                        </tr>

                        <!-- Total Score Row -->
                        <tr class="font-bold bg-sky-100 dark:bg-gray-600">
                            <td class="border border-black px-3 py-2 text-center 
                                   text-gray-900 dark:text-white"
                                colspan="3">Total Score</td>
                            <td class="border border-black px-3 py-2 bg-black"></td>
                            <td
                                class="border border-black px-3 py-2 text-center 
                                   text-gray-900 dark:text-white">
                                {{ number_format($totalStandardScore, 2) }}
                            </td>
                            <td class="border border-black px-3 py-2 bg-black"></td>
                            <td
                                class="border border-black px-3 py-2 text-center 
                                   text-gray-900 dark:text-white">
                                {{ number_format($totalIndividualScore, 2) }}</td>
                            <td class="border border-black px-3 py-2 bg-black"></td>
                            <td
                                class="border border-black px-3 py-2 text-center 
                                   text-gray-900 dark:text-white">
                                {{ number_format($totalGapScore, 2) }}</td>
                            <td class="border border-black px-3 py-2 
                                text-black
                                @php
$c = trim(strtoupper($overallConclusion)); @endphp
                                @if ($c === 'MEMENUHI STANDAR/MEET REQUIREMENT STANDARD') bg-green-500 text-black
                                @elseif ($c === 'KURANG MEMENUHI STANDAR/BELOW REQUIREMENT STANDARD')
                                    bg-red-600 text-black @endif"
                                colspan="2">
                                {{ $overallConclusion }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif

        @if ($showRatingChart)
            <!-- Chart Section Rating - DARK MODE READY -->
            <div class="p-6 border-t-2 border-black bg-white dark:bg-gray-800" wire:ignore
                id="chart-rating-{{ $chartId }}">
                <div class="text-center text-base font-bold mb-6 
                       text-gray-900 dark:text-white">
                    Profil Potensi Spider Plot Chart (Rating)</div>
                <div class="flex justify-center mb-6">
                    <div style="width: 700px; height: 700px; position: relative;">
                        <canvas id="spiderRatingChart-{{ $chartId }}"></canvas>
                    </div>
                </div>

                <!-- Legend Rating - DARK MODE READY -->
                <div class="flex justify-center text-sm gap-8 mb-8" id="rating-legend-{{ $chartId }}">
                    <span
                        class="legend-item flex items-center gap-2 cursor-pointer select-none 
                           hover:bg-gray-100 dark:hover:bg-gray-600 
                           px-3 py-2 rounded-lg transition-all duration-200 
                           border border-gray-300 dark:border-gray-600 
                           shadow-sm bg-white dark:bg-gray-700 
                           text-gray-900 dark:text-white"
                        data-chart="rating" data-dataset="2">
                        <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #fafa05;"></span>
                        <span class="font-semibold">Standard</span>
                    </span>
                    <span
                        class="legend-item flex items-center gap-2 cursor-pointer select-none 
                           hover:bg-gray-100 dark:hover:bg-gray-600 
                           px-3 py-2 rounded-lg transition-all duration-200 
                           border border-gray-300 dark:border-gray-600 
                           shadow-sm bg-white dark:bg-gray-700 
                           text-gray-900 dark:text-white"
                        data-chart="rating" data-dataset="0">
                        <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #5db010;"></span>
                        <span class="font-semibold" style="color: #5db010;">{{ $participant->name }}</span>
                    </span>
                    <span
                        class="legend-item flex items-center gap-2 cursor-pointer select-none 
                           hover:bg-gray-100 dark:hover:bg-gray-600 
                           px-3 py-2 rounded-lg transition-all duration-200 
                           border border-gray-300 dark:border-gray-600 
                           shadow-sm bg-white dark:bg-gray-700 
                           text-gray-900 dark:text-white"
                        data-chart="rating" data-dataset="1">
                        <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #b50505;"></span>
                        <span class="font-semibold" x-data
                            x-text="'Tolerance ' + $wire.tolerancePercentage + '%'"></span>
                    </span>
                </div>
            </div>
        @endif

        @if ($showScoreChart)
            <!-- Chart Section Score - DARK MODE READY -->
            <div class="p-6 border-t-2 border-black bg-white dark:bg-gray-800" wire:ignore
                id="chart-score-{{ $chartId }}">
                <div
                    class="text-center text-base font-bold mb-6 
                       text-gray-900 dark:text-white">
                    Profil Potensi Spider Plot Chart (Score)</div>
                <div class="flex justify-center mb-6">
                    <div style="width: 700px; height: 700px; position: relative;">
                        <canvas id="spiderScoreChart-{{ $chartId }}"></canvas>
                    </div>
                </div>

                <!-- Legend Score - DARK MODE READY -->
                <div class="flex justify-center text-sm gap-8 mb-8" id="score-legend-{{ $chartId }}">
                    <span
                        class="legend-item flex items-center gap-2 cursor-pointer select-none 
                           hover:bg-gray-100 dark:hover:bg-gray-600 
                           px-3 py-2 rounded-lg transition-all duration-200 
                           border border-gray-300 dark:border-gray-600 
                           shadow-sm bg-white dark:bg-gray-700 
                           text-gray-900 dark:text-white"
                        data-chart="score" data-dataset="2">
                        <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #fafa05;"></span>
                        <span class="font-semibold">Standard</span>
                    </span>
                    <span
                        class="legend-item flex items-center gap-2 cursor-pointer select-none 
                           hover:bg-gray-100 dark:hover:bg-gray-600 
                           px-3 py-2 rounded-lg transition-all duration-200 
                           border border-gray-300 dark:border-gray-600 
                           shadow-sm bg-white dark:bg-gray-700 
                           text-gray-900 dark:text-white"
                        data-chart="score" data-dataset="0">
                        <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #5db010;"></span>
                        <span class="font-semibold" style="color: #5db010;">{{ $participant->name }}</span>
                    </span>
                    <span
                        class="legend-item flex items-center gap-2 cursor-pointer select-none 
                           hover:bg-gray-100 dark:hover:bg-gray-600 
                           px-3 py-2 rounded-lg transition-all duration-200 
                           border border-gray-300 dark:border-gray-600 
                           shadow-sm bg-white dark:bg-gray-700 
                           text-gray-900 dark:text-white"
                        data-chart="score" data-dataset="1">
                        <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #b50505;"></span>
                        <span class="font-semibold" x-data
                            x-text="'Tolerance ' + $wire.tolerancePercentage + '%'"></span>
                    </span>
                </div>
            </div>
        @endif

        @if ($showRatingChart || $showScoreChart)
            <!-- 1 SCRIPT FULL - DARK MODE CHARTS (PSYCHOLOGY) -->
            <!-- 1 SCRIPT FULL - DARK MODE CHARTS (PSYCHOLOGY) - FIXED TICK LABELS -->
            <script>
                (function() {
                    if (window['chartSetup_{{ $chartId }}']) return;
                    window['chartSetup_{{ $chartId }}'] = true;

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

                    // 🌙 DARK MODE COLORS - FIXED TRANSPARENT BG
                    const getColors = () => {
                        const dark = document.documentElement.classList.contains('dark');
                        return {
                            grid: dark ? 'rgba(255,255,255,0.15)' : 'rgba(0,0,0,0.15)',
                            ticks: dark ? '#e5e7eb' : '#000',
                            labels: dark ? '#f9fafb' : '#000',
                            tickBg: 'transparent' // ❌ NO GRAY/WHITE BLOCK!
                        };
                    };

                    // TOGGLE FUNCTION
                    window.toggleDataset_{{ $chartId }} = function(chartType, datasetIndex) {
                        const chart = window[`${chartType}Chart_{{ $chartId }}`];
                        if (!chart || !chart.data.datasets[datasetIndex]) return;

                        const wasHidden = chart.data.datasets[datasetIndex].hidden || false;
                        chart.data.datasets[datasetIndex].hidden = !wasHidden;
                        window[`${chartType}Visibility_{{ $chartId }}`][datasetIndex] = !wasHidden;

                        chart.update('active');
                        updateLegendVisual(chartType);
                    };

                    // UPDATE LEGEND VISUAL
                    function updateLegendVisual(chartType) {
                        const legendContainer = document.getElementById(`${chartType}-legend-{{ $chartId }}`);
                        if (!legendContainer) return;

                        const chart = window[`${chartType}Chart_{{ $chartId }}`];
                        legendContainer.querySelectorAll('.legend-item').forEach(item => {
                            const idx = parseInt(item.dataset.dataset);
                            const isHidden = chart?.data.datasets[idx]?.hidden || false;
                            if (isHidden) {
                                item.classList.add('opacity-50', 'line-through', 'bg-gray-50', 'dark:bg-gray-600');
                                item.classList.remove('bg-white', 'dark:bg-gray-700', 'shadow-sm');
                            } else {
                                item.classList.remove('opacity-50', 'line-through', 'bg-gray-50', 'dark:bg-gray-600');
                                item.classList.add('bg-white', 'dark:bg-gray-700', 'shadow-sm');
                            }
                        });
                    }

                    // SETUP LEGEND LISTENERS
                    function setupLegendListeners(chartType) {
                        const legendContainer = document.getElementById(`${chartType}-legend-{{ $chartId }}`);
                        if (!legendContainer) return;
                        legendContainer.onclick = (e) => {
                            const item = e.target.closest('.legend-item');
                            if (!item) return;
                            window.toggleDataset_{{ $chartId }}(chartType, parseInt(item.dataset.dataset));
                        };
                    }

                    // RATING CHART SETUP - FIXED TICK LABELS
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
                        const colors = getColors();

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
                                pointBorderWidth: 2,
                                hidden: window.ratingVisibility_{{ $chartId }}[0],
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
                                pointBackgroundColor: '#9a0404',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                hidden: window.ratingVisibility_{{ $chartId }}[1],
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
                                pointBorderWidth: 2,
                                hidden: window.ratingVisibility_{{ $chartId }}[2],
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
                                            },
                                            backdropColor: colors.tickBg, // ✅ TRANSPARENT!
                                            z: 1000 // ✅ Z-INDEX ATAS!
                                        },
                                        pointLabels: {
                                            font: {
                                                size: 11,
                                                weight: '600'
                                            },
                                            color: colors.labels,
                                            z: 1000 // ✅ Z-INDEX ATAS!
                                        },
                                        grid: {
                                            color: colors.grid
                                        },
                                        angleLines: {
                                            color: colors.grid
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

                    // SCORE CHART SETUP - FIXED TICK LABELS
                    function setupScoreChart() {
                        if (window.scoreChart_{{ $chartId }}) window.scoreChart_{{ $chartId }}.destroy();

                        const chartLabels = @js($chartLabels);
                        const originalStandardScores = @js($chartOriginalStandardScores);
                        const standardScores = @js($chartStandardScores);
                        const individualScores = @js($chartIndividualScores);
                        const participantName = @js($participant->name);
                        const tolerancePercentage = @js($tolerancePercentage);
                        const maxScore = Math.max(...originalStandardScores, ...individualScores, ...standardScores) * 1.2;

                        const canvas = document.getElementById('spiderScoreChart-{{ $chartId }}');
                        if (!canvas) return;
                        const ctx = canvas.getContext('2d');
                        const colors = getColors();

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
                                pointBorderWidth: 2,
                                hidden: window.scoreVisibility_{{ $chartId }}[0],
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
                                pointBackgroundColor: '#9a0404',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                hidden: window.scoreVisibility_{{ $chartId }}[1],
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
                                pointBorderWidth: 2,
                                hidden: window.scoreVisibility_{{ $chartId }}[2],
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
                                            },
                                            backdropColor: colors.tickBg, // ✅ TRANSPARENT!
                                            z: 1000 // ✅ Z-INDEX ATAS!
                                        },
                                        pointLabels: {
                                            font: {
                                                size: 11,
                                                weight: '600'
                                            },
                                            color: colors.labels,
                                            z: 1000 // ✅ Z-INDEX ATAS!
                                        },
                                        grid: {
                                            color: colors.grid
                                        },
                                        angleLines: {
                                            color: colors.grid
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

                    // LIVEWIRE INIT
                    function waitForLivewire(callback) {
                        if (window.Livewire) callback();
                        else setTimeout(() => waitForLivewire(callback), 100);
                    }

                    waitForLivewire(function() {
                        @if ($showRatingChart)
                            setupRatingChart();
                        @endif
                        @if ($showScoreChart)
                            setupScoreChart();
                        @endif

                        // TOLERANCE UPDATE
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

                                const colors = getColors();
                                chart.options.scales.r.ticks.color = colors.ticks;
                                chart.options.scales.r.ticks.backdropColor = colors.tickBg;
                                chart.options.scales.r.pointLabels.color = colors.labels;
                                chart.options.scales.r.grid.color = colors.grid;
                                chart.options.scales.r.angleLines.color = colors.grid;

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

                                const colors = getColors();
                                chart.options.scales.r.ticks.color = colors.ticks;
                                chart.options.scales.r.ticks.backdropColor = colors.tickBg;
                                chart.options.scales.r.pointLabels.color = colors.labels;
                                chart.options.scales.r.grid.color = colors.grid;
                                chart.options.scales.r.angleLines.color = colors.grid;

                                chart.update('active');
                                updateLegendVisual('score');
                            }
                        });
                    });

                    // 🌙 DARK MODE LISTENER - FIXED TICK LABELS
                    new MutationObserver(() => {
                        const colors = getColors();

                        if (window.ratingChart_{{ $chartId }}) {
                            const chart = window.ratingChart_{{ $chartId }};
                            chart.options.scales.r.ticks.color = colors.ticks;
                            chart.options.scales.r.ticks.backdropColor = colors.tickBg;
                            chart.options.scales.r.pointLabels.color = colors.labels;
                            chart.options.scales.r.grid.color = colors.grid;
                            chart.options.scales.r.angleLines.color = colors.grid;
                            chart.update('active');
                        }

                        if (window.scoreChart_{{ $chartId }}) {
                            const chart = window.scoreChart_{{ $chartId }};
                            chart.options.scales.r.ticks.color = colors.ticks;
                            chart.options.scales.r.ticks.backdropColor = colors.tickBg;
                            chart.options.scales.r.pointLabels.color = colors.labels;
                            chart.options.scales.r.grid.color = colors.grid;
                            chart.options.scales.r.angleLines.color = colors.grid;
                            chart.update('active');
                        }
                    }).observe(document.documentElement, {
                        attributes: true,
                        attributeFilter: ['class']
                    });
                })();
            </script>
        @endif
    </div>
</div>
