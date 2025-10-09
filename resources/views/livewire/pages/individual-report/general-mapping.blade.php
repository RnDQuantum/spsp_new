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

        <!-- Table Section -->
        <div class="p-4 overflow-x-auto">
            <table class="min-w-full border border-black text-xs text-gray-900">
                <thead>
                    <tr class="bg-sky-200 text-gray-900">
                        <th class="border border-black px-3 py-2 font-semibold">No</th>
                        <th class="border border-black px-3 py-2 font-semibold">Atribut/Attribute</th>
                        <th class="border border-black px-3 py-2 font-semibold">Bobot %<br>200</th>
                        <th class="border border-black px-3 py-2 font-semibold" colspan="2">Standard</th>
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
                            <td class="border border-black px-3 py-2 text-center">{{ $aspect['weight_percentage'] }}</td>
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
                                    $percentage = $aspect['standard_score'] > 0
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
                        <td class="border border-black px-3 py-2 text-center">{{ number_format($totalStandardRating, 2) }}
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
                        <td class="border border-black px-3 py-2 text-center">{{ number_format($totalStandardScore, 2) }}
                        </td>
                        <td class="border border-black px-3 py-2"></td>
                        <td class="border border-black px-3 py-2 text-center">
                            {{ number_format($totalIndividualScore, 2) }}</td>
                        <td class="border border-black px-3 py-2"></td>
                        <td class="border border-black px-3 py-2 text-center">{{ number_format($totalGapScore, 2) }}</td>
                        <td class="border border-black px-3 py-2" colspan="2">{{ $overallConclusion }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Chart Section Rating -->
        <div class="p-6 border-t-2 border-black bg-white">
            <div class="text-center text-base font-bold mb-6 text-gray-900">Profil Pribadi Spider Plot Chart (Rating)
            </div>
            <div class="flex justify-center mb-6">
                <div style="width: 700px; height: 700px; position: relative;">
                    <canvas id="spiderRatingChart"></canvas>
                </div>
            </div>
            <div class="flex justify-center text-sm gap-8 text-gray-900 mb-8">
                <span class="flex items-center gap-2">
                    <span class="inline-block w-10 border-b-2 border-black"></span>
                    <span class="font-semibold">Standar</span>
                </span>
                <span class="flex items-center gap-2">
                    <span class="inline-block w-10 border-b-2 border-red-600"></span>
                    <span class="text-red-600 font-bold">{{ $participant->name }}</span>
                </span>
                <span class="flex items-center gap-2">
                    <span class="inline-block w-10" style="border-bottom: 1px dashed #6B7280;"></span>
                    <span>Toleransi 10%</span>
                </span>
            </div>
        </div>

        <!-- Chart Section Score -->
        <div class="p-6 border-t-2 border-black bg-white">
            <div class="text-center text-base font-bold mb-6 text-gray-900">Profil Pribadi Spider Plot Chart (Score)
            </div>
            <div class="flex justify-center mb-6">
                <div style="width: 700px; height: 700px; position: relative;">
                    <canvas id="spiderScoreChart"></canvas>
                </div>
            </div>
            <div class="flex justify-center text-sm gap-8 text-gray-900 mb-8">
                <span class="flex items-center gap-2">
                    <span class="inline-block w-10 border-b-2 border-black"></span>
                    <span class="font-semibold">Standar</span>
                </span>
                <span class="flex items-center gap-2">
                    <span class="inline-block w-10 border-b-2 border-red-600"></span>
                    <span class="text-red-600 font-bold">{{ $participant->name }}</span>
                </span>
                <span class="flex items-center gap-2">
                    <span class="inline-block w-10" style="border-bottom: 1px dashed #6B7280;"></span>
                    <span>Toleransi 10%</span>
                </span>
            </div>
        </div>
    </div>

    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        const LABELS = @js($chartLabels);

        const ratingData = {
            labels: LABELS,
            datasets: [{
                label: 'Standar',
                data: @js($chartStandardRatings),
                borderColor: '#000000',
                backgroundColor: 'rgba(0, 0, 0, 0.05)',
                borderWidth: 2,
                pointRadius: 3,
                pointBackgroundColor: '#000000'
            }, {
                label: @js($participant->name),
                data: @js($chartIndividualRatings),
                borderColor: '#DC2626',
                backgroundColor: 'rgba(220, 38, 38, 0.05)',
                borderWidth: 2,
                pointRadius: 3,
                pointBackgroundColor: '#DC2626'
            }]
        };

        const scoreData = {
            labels: LABELS,
            datasets: [{
                label: 'Standar',
                data: @js($chartStandardScores),
                borderColor: '#000000',
                backgroundColor: 'rgba(0, 0, 0, 0.05)',
                borderWidth: 2,
                pointRadius: 3,
                pointBackgroundColor: '#000000'
            }, {
                label: @js($participant->name),
                data: @js($chartIndividualScores),
                borderColor: '#DC2626',
                backgroundColor: 'rgba(220, 38, 38, 0.05)',
                borderWidth: 2,
                pointRadius: 3,
                pointBackgroundColor: '#DC2626'
            }]
        };

        const radarOptions = {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: true,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 10
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
                        backdropColor: 'transparent',
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
                        color: '#000000',
                        padding: 20
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.15)'
                    },
                    angleLines: {
                        color: 'rgba(0, 0, 0, 0.15)'
                    }
                }
            }
        };

        const scoreOptions = {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: true,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 10
                }
            },
            scales: {
                r: {
                    beginAtZero: true,
                    min: 0,
                    max: Math.max(...@js($chartStandardScores), ...@js($chartIndividualScores)) * 1.2,
                    ticks: {
                        stepSize: 20,
                        color: '#000000',
                        backdropColor: 'transparent',
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
                        color: '#000000',
                        padding: 20
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.15)'
                    },
                    angleLines: {
                        color: 'rgba(0, 0, 0, 0.15)'
                    }
                }
            }
        };

        new Chart(document.getElementById('spiderRatingChart'), {
            type: 'radar',
            data: ratingData,
            options: radarOptions
        });

        new Chart(document.getElementById('spiderScoreChart'), {
            type: 'radar',
            data: scoreData,
            options: scoreOptions
        });
    </script>
</div>
