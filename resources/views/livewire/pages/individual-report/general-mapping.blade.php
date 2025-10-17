<div>
    <div class="bg-white mx-auto my-8 shadow overflow-hidden" style="max-width: 1400px;">
        <!-- Header -->
        <div class="border-b-4 border-black py-3 bg-sky-200">
            <h1 class="text-center text-lg font-bold uppercase tracking-wide text-gray-900">
                GENERAL MAPPING
            </h1>
            <p class="text-center text-sm font-semibold text-gray-700 mt-1">
                {{ $participant->name }}
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
            <table class="min-w-full border border-black text-xs text-gray-900">
                <thead>
                    <tr class="bg-sky-200 text-gray-900">
                        <th class="border border-black px-3 py-2 font-semibold">No</th>
                        <th class="border border-black px-3 py-2 font-semibold">Atribut/Attribute</th>
                        <th class="border border-black px-3 py-2 font-semibold">Bobot %<br>200</th>
                        <th class="border border-black px-3 py-2 font-semibold" colspan="2">
                            <span x-data
                                x-text="$wire.tolerancePercentage > 0 ? 'Standard (-' + $wire.tolerancePercentage + '%)' : 'Standard'"></span>
                        </th>
                        <th class="border border-black px-3 py-2 font-semibold" colspan="2">Individu</th>
                        <th class="border border-black px-3 py-2 font-semibold" colspan="2">Gap</th>
                        <th class="border border-black px-3 py-2 font-semibold">Prosentase<br>Kesesuaian</th>
                        <th class="border border-black px-3 py-2 font-semibold">Kesimpulan/Conclusion</th>
                    </tr>
                    <tr class="bg-sky-200 text-gray-900">
                        <th class="border border-black px-3 py-1"></th>
                        <th class="border border-black px-3 py-1"></th>
                        <th class="border border-black px-3 py-1"></th>
                        <th class="border border-black px-3 py-1 font-semibold">Rating/<br>Level</th>
                        <th class="border border-black px-3 py-1 font-semibold">Score</th>
                        <th class="border border-black px-3 py-1 font-semibold">Rating/<br>Level</th>
                        <th class="border border-black px-3 py-1 font-semibold">Score</th>
                        <th class="border border-black px-3 py-1 font-semibold">Rating/<br>Level</th>
                        <th class="border border-black px-3 py-0 font-semibold">Score</th>
                        <th class="border border-black px-3 py-1"></th>
                        <th class="border border-black px-3 py-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($aspectsData as $index => $aspect)
                        <tr>
                            <td class="border border-black px-3 py-2 text-center">
                                @if ($index < 4)
                                    {{ ['I', 'II', 'III', 'IV', 'V'][$index] }}
                                @else
                                    {{ ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX'][$index - 4] }}
                                @endif
                            </td>
                            <td class="border border-black px-3 py-2">{{ $aspect['name'] }}</td>
                            <td class="border border-black px-3 py-2 text-center">{{ $aspect['weight_percentage'] }}
                            </td>
                            <td class="border border-black px-3 py-2 text-center">
                                {{ number_format($aspect['standard_rating'], 2) }}</td>
                            <td class="border border-black px-3 py-2 text-center">
                                {{ number_format($aspect['standard_score'], 2) }}</td>
                            <td class="border border-black px-3 py-2 text-center">
                                {{ number_format($aspect['individual_rating'], 2) }}</td>
                            <td class="border border-black px-3 py-2 text-center">
                                {{ number_format($aspect['individual_score'], 2) }}</td>
                            <td class="border border-black px-3 py-2 text-center">
                                {{ number_format($aspect['gap_rating'], 2) }}</td>
                            <td class="border border-black px-3 py-2 text-center">
                                {{ number_format($aspect['gap_score'], 2) }}</td>
                            <td class="border border-black px-3 py-2 text-center">
                                @php
                                    $percentage =
                                        $aspect['standard_score'] > 0
                                            ? round(($aspect['individual_score'] / $aspect['standard_score']) * 100)
                                            : 0;
                                @endphp
                                {{ $percentage }}%
                            </td>
                            <td class="border border-black px-3 py-2">{{ $aspect['conclusion_text'] }}</td>
                        </tr>
                    @endforeach

                    <!-- Total Rating Row -->
                    <tr class="font-bold bg-sky-100">
                        <td class="border border-black px-3 py-2 text-right" colspan="3">Total Rating</td>
                        <td class="border border-black px-3 py-2 text-center">
                            {{ number_format($totalStandardRating, 2) }}
                        </td>
                        <td class="border border-black px-3 py-2"></td>
                        <td class="border border-black px-3 py-2 text-center">
                            {{ number_format($totalIndividualRating, 2) }}</td>
                        <td class="border border-black px-3 py-2"></td>
                        <td class="border border-black px-3 py-2 text-center">{{ number_format($totalGapRating, 2) }}
                        </td>
                        <td class="border border-black px-3 py-2"></td>
                        <td class="border border-black px-3 py-2" colspan="2">{{ $overallConclusion }}</td>
                    </tr>

                    <!-- Total Score Row -->
                    <tr class="font-bold bg-sky-100">
                        <td class="border border-black px-3 py-2 text-right" colspan="3">Total Score</td>
                        <td class="border border-black px-3 py-2"></td>
                        <td class="border border-black px-3 py-2 text-center">
                            {{ number_format($totalStandardScore, 2) }}
                        </td>
                        <td class="border border-black px-3 py-2"></td>
                        <td class="border border-black px-3 py-2 text-center">
                            {{ number_format($totalIndividualScore, 2) }}</td>
                        <td class="border border-black px-3 py-2"></td>
                        <td class="border border-black px-3 py-2 text-center">{{ number_format($totalGapScore, 2) }}
                        </td>
                        <td class="border border-black px-3 py-2" colspan="2">{{ $overallConclusion }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Chart Section Rating -->
        <div class="p-6 border-t-2 border-black bg-white" wire:ignore id="chart-rating-{{ $chartId }}">
            <div class="text-center text-base font-bold mb-6 text-gray-900">Profil Pribadi Spider Plot Chart (Rating)
            </div>
            <div class="flex justify-center mb-6">
                <div style="width: 700px; height: 700px; position: relative;">
                    <canvas id="spiderRatingChart-{{ $chartId }}"></canvas>
                </div>
            </div>
            <div class="flex justify-center text-sm gap-8 text-gray-900 mb-8">
                <span class="flex items-center gap-2">
                    <span class="inline-block w-10 border-b-2 border-black"></span>
                    <span class="font-semibold">Standar</span>
                </span>
                <span class="flex items-center gap-2">
                    <span class="inline-block w-10" style="border-bottom: 2px dashed #6B7280;"></span>
                    <span x-data x-text="'Toleransi ' + $wire.tolerancePercentage + '%'"></span>
                </span>
                <span class="flex items-center gap-2">
                    <span class="inline-block w-10 border-b-2 border-red-600"></span>
                    <span class="text-red-600 font-bold">{{ $participant->name }}</span>
                </span>
            </div>

            <script>
                (function() {
                    if (window['ratingChartSetup_{{ $chartId }}']) return;
                    window['ratingChartSetup_{{ $chartId }}'] = true;

                    function setupRatingChart() {
                        if (window.ratingChart_{{ $chartId }}) {
                            window.ratingChart_{{ $chartId }}.destroy();
                        }

                        let chartInstance = null;
                        const chartLabels = @js($chartLabels);
                        let originalStandardRatings = @js($chartOriginalStandardRatings);
                        let standardRatings = @js($chartStandardRatings);
                        const individualRatings = @js($chartIndividualRatings);
                        const participantName = @js($participant->name);
                        let tolerancePercentage = @js($tolerancePercentage);

                        function initChart() {
                            const canvas = document.getElementById('spiderRatingChart-{{ $chartId }}');
                            if (!canvas) return;

                            const ctx = canvas.getContext('2d');

                            chartInstance = new Chart(ctx, {
                                type: 'radar',
                                data: {
                                    labels: chartLabels,
                                    datasets: [{
                                        label: 'Standar',
                                        data: originalStandardRatings,
                                        borderColor: '#000000',
                                        backgroundColor: 'rgba(0, 0, 0, 0.05)',
                                        borderWidth: 2,
                                        pointRadius: 3,
                                        pointBackgroundColor: '#000000'
                                    }, {
                                        label: `Toleransi ${tolerancePercentage}%`,
                                        data: standardRatings,
                                        borderColor: '#6B7280',
                                        backgroundColor: 'transparent',
                                        borderWidth: 1.5,
                                        borderDash: [5, 5],
                                        pointRadius: 2,
                                        pointBackgroundColor: '#6B7280'
                                    }, {
                                        label: participantName,
                                        data: individualRatings,
                                        borderColor: '#DC2626',
                                        backgroundColor: 'rgba(220, 38, 38, 0.05)',
                                        borderWidth: 2,
                                        pointRadius: 3,
                                        pointBackgroundColor: '#DC2626'
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: true,
                                    plugins: {
                                        legend: {
                                            display: false
                                        },
                                        tooltip: {
                                            enabled: true
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
                        }

                        function waitForLivewire(callback) {
                            if (window.Livewire) callback();
                            else setTimeout(() => waitForLivewire(callback), 100);
                        }

                        waitForLivewire(function() {
                            initChart();

                            Livewire.on('chartDataUpdated', function(data) {
                                let chartData = Array.isArray(data) && data.length > 0 ? data[0] : data;
                                if (window.ratingChart_{{ $chartId }} && chartData) {
                                    tolerancePercentage = chartData.tolerance;
                                    originalStandardRatings = chartData.originalStandardRatings;
                                    standardRatings = chartData.standardRatings;

                                    // Update datasets
                                    window.ratingChart_{{ $chartId }}.data.datasets[0].data = chartData
                                        .originalStandardRatings;
                                    window.ratingChart_{{ $chartId }}.data.datasets[1].label =
                                        `Toleransi ${tolerancePercentage}%`;
                                    window.ratingChart_{{ $chartId }}.data.datasets[1].data = chartData
                                        .standardRatings;
                                    window.ratingChart_{{ $chartId }}.update('active');
                                }
                            });
                        });
                    }

                    setupRatingChart();
                })();
            </script>
        </div>

        <!-- Chart Section Score -->
        <div class="p-6 border-t-2 border-black bg-white" wire:ignore id="chart-score-{{ $chartId }}">
            <div class="text-center text-base font-bold mb-6 text-gray-900">Profil Pribadi Spider Plot Chart (Score)
            </div>
            <div class="flex justify-center mb-6">
                <div style="width: 700px; height: 700px; position: relative;">
                    <canvas id="spiderScoreChart-{{ $chartId }}"></canvas>
                </div>
            </div>
            <div class="flex justify-center text-sm gap-8 text-gray-900 mb-8">
                <span class="flex items-center gap-2">
                    <span class="inline-block w-10 border-b-2 border-black"></span>
                    <span class="font-semibold">Standar</span>
                </span>
                <span class="flex items-center gap-2">
                    <span class="inline-block w-10" style="border-bottom: 2px dashed #6B7280;"></span>
                    <span x-data x-text="'Toleransi ' + $wire.tolerancePercentage + '%'"></span>
                </span>
                <span class="flex items-center gap-2">
                    <span class="inline-block w-10 border-b-2 border-red-600"></span>
                    <span class="text-red-600 font-bold">{{ $participant->name }}</span>
                </span>
            </div>

            <script>
                (function() {
                    if (window['scoreChartSetup_{{ $chartId }}']) return;
                    window['scoreChartSetup_{{ $chartId }}'] = true;

                    function setupScoreChart() {
                        if (window.scoreChart_{{ $chartId }}) {
                            window.scoreChart_{{ $chartId }}.destroy();
                        }

                        let chartInstance = null;
                        const chartLabels = @js($chartLabels);
                        let originalStandardScores = @js($chartOriginalStandardScores);
                        let standardScores = @js($chartStandardScores);
                        const individualScores = @js($chartIndividualScores);
                        const participantName = @js($participant->name);
                        let tolerancePercentage = @js($tolerancePercentage);

                        function initChart() {
                            const canvas = document.getElementById('spiderScoreChart-{{ $chartId }}');
                            if (!canvas) return;

                            const ctx = canvas.getContext('2d');
                            const maxScore = Math.max(...originalStandardScores, ...individualScores) * 1.2;

                            chartInstance = new Chart(ctx, {
                                type: 'radar',
                                data: {
                                    labels: chartLabels,
                                    datasets: [{
                                        label: 'Standar',
                                        data: originalStandardScores,
                                        borderColor: '#000000',
                                        backgroundColor: 'rgba(0, 0, 0, 0.05)',
                                        borderWidth: 2,
                                        pointRadius: 3,
                                        pointBackgroundColor: '#000000'
                                    }, {
                                        label: `Toleransi ${tolerancePercentage}%`,
                                        data: standardScores,
                                        borderColor: '#6B7280',
                                        backgroundColor: 'transparent',
                                        borderWidth: 1.5,
                                        borderDash: [5, 5],
                                        pointRadius: 2,
                                        pointBackgroundColor: '#6B7280'
                                    }, {
                                        label: participantName,
                                        data: individualScores,
                                        borderColor: '#DC2626',
                                        backgroundColor: 'rgba(220, 38, 38, 0.05)',
                                        borderWidth: 2,
                                        pointRadius: 3,
                                        pointBackgroundColor: '#DC2626'
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: true,
                                    plugins: {
                                        legend: {
                                            display: false
                                        },
                                        tooltip: {
                                            enabled: true
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
                                                stepSize: 20,
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

                            window.scoreChart_{{ $chartId }} = chartInstance;
                        }

                        function waitForLivewire(callback) {
                            if (window.Livewire) callback();
                            else setTimeout(() => waitForLivewire(callback), 100);
                        }

                        waitForLivewire(function() {
                            initChart();

                            Livewire.on('chartDataUpdated', function(data) {
                                let chartData = Array.isArray(data) && data.length > 0 ? data[0] : data;
                                if (window.scoreChart_{{ $chartId }} && chartData) {
                                    tolerancePercentage = chartData.tolerance;
                                    originalStandardScores = chartData.originalStandardScores;
                                    standardScores = chartData.standardScores;

                                    // Update datasets
                                    window.scoreChart_{{ $chartId }}.data.datasets[0].data = chartData
                                        .originalStandardScores;
                                    window.scoreChart_{{ $chartId }}.data.datasets[1].label =
                                        `Toleransi ${tolerancePercentage}%`;
                                    window.scoreChart_{{ $chartId }}.data.datasets[1].data = chartData
                                        .standardScores;
                                    window.scoreChart_{{ $chartId }}.update('active');
                                }
                            });
                        });
                    }

                    setupScoreChart();
                })();
            </script>
        </div>
    </div>
</div>
