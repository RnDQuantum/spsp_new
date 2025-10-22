<div class="max-w-7xl mx-auto">
    <div class="bg-white p-6 rounded shadow text-gray-900">

        {{-- Filter Section --}}
        <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-300">
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
            <div class="mt-3 pt-3 border-t border-gray-200">
                <div class="flex items-start gap-2 text-sm text-gray-600">
                    <span class="font-semibold">📄 Template:</span>
                    <div>
                        <div class="font-semibold text-gray-900">{{ $selectedTemplate->name }}</div>
                        @if ($selectedTemplate->description)
                        <div class="text-xs text-gray-500 mt-1">{{ $selectedTemplate->description }}</div>
                        @endif
                    </div>
                </div>
                @if ($selectedEvent)
                <div class="flex items-center gap-2 text-sm text-gray-600 mt-2">
                    <span class="font-semibold">🏢 Institusi:</span>
                    <span class="text-gray-900">{{ $selectedEvent->institution->name ?? 'N/A' }}</span>
                </div>
                @endif
            </div>
            @endif
        </div>

        {{-- Header Title --}}
        <div class="text-center font-bold uppercase mb-4 text-sm bg-blue-200 py-2 border border-black">
            STANDAR PEMETAAN KOMPETENSI INDIVIDU "STATIC PRIBADI SPIDER PLOT"
        </div>

        {{-- Empty State --}}
        @if (!$selectedEvent || !$selectedTemplate)
        <div class="text-center py-12">
            <div class="text-gray-500 text-lg mb-2">Tidak ada data untuk ditampilkan</div>
            <div class="text-gray-400 text-sm">Silakan pilih event dan jabatan terlebih dahulu.</div>
        </div>
        @else
        <table class="w-full border border-black text-xs mb-4">
            <tr>
                <td class="border border-black px-2 py-1 bg-blue-100 font-semibold w-1/5">Perusahaan/Lembaga</td>
                <td class="border border-black px-2 py-1 w-2/5">
                    {{ strtoupper($selectedEvent->institution->name ?? 'N/A') }}</td>
                <td class="border border-black px-2 py-1 bg-blue-100 font-semibold w-1/6"></td>
                <td class="border border-black px-2 py-1 w-1/6"></td>
            </tr>
            <tr>
                <td class="border border-black px-2 py-1 bg-blue-100 font-semibold">Standard Penilaian</td>
                <td class="border border-black px-2 py-1">{{ $selectedTemplate->name }}</td>
                <td class="border border-black px-2 py-1 bg-blue-100 font-semibold">Kode:</td>
                <td class="border border-black px-2 py-1 text-center">{{ $selectedTemplate->code }}</td>
            </tr>
        </table>
        @endif

        {{-- Tabel Detail dengan Sub-Aspects --}}
        @if (count($categoryData) > 0)
        <div class="mt-4 mb-4">
            <table class="w-full border border-black text-xs">
                <thead>
                    <tr class="bg-blue-100">
                        <th class="border border-black px-2 py-2 w-12">No.</th>
                        <th class="border border-black px-2 py-2">ATRIBUT</th>
                        <th class="border border-black px-2 py-2 w-24">NILAI STANDAR</th>
                        <th class="border border-black px-2 py-2 w-24">JUMLAH ATRIBUT</th>
                        <th class="border border-black px-2 py-2 w-20">BOBOT (%)</th>
                        <th class="border border-black px-2 py-2 w-24">RATING Rata-Rata</th>
                        <th class="border border-black px-2 py-2 w-20">SKOR</th>
                    </tr>
                </thead>
                <tbody>
                    @php $romanNumerals = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII',
                    'XIII', 'XIV', 'XV', 'XVI', 'XVII', 'XVIII', 'XIX', 'XX']; @endphp

                    @foreach ($categoryData as $catIndex => $category)
                    {{-- Aspects within Kompetensi Category --}}
                    @foreach ($category['aspects'] as $aspectIndex => $aspect)
                    {{-- Aspect without sub-aspects (Kompetensi) --}}
                    <tr>
                        <td class="border border-black px-2 py-2 text-center">{{ $aspectIndex + 1 }}
                        </td>
                        <td class="border border-black px-2 py-2 pl-4 ">
                            <p class="font-bold mb-1">{{ $aspect['name'] }}</p>
                            <p class="text-xs text-gray-500">{{ $aspect['description'] }}</p>
                        </td>
                        <td class="border border-black px-2 py-2 text-center">
                            {{ number_format($aspect['standard_rating'], 2) }}</td>
                        <td class="border border-black px-2 py-2 text-center">
                            {{ $aspect['attribute_count'] }}</td>
                        <td class="border border-black px-2 py-2 text-center">
                            {{ $aspect['weight_percentage'] }}</td>
                        <td class="border border-black px-2 py-2 text-center">
                            {{ $aspect['average_rating'] }}</td>
                        <td class="border border-black px-2 py-2 text-center">
                            {{ number_format($aspect['score'], 2) }}</td>
                    </tr>
                    @endforeach
                    @endforeach

                    {{-- TOTAL --}}
                    <tr class="bg-blue-100 font-bold">
                        <td class="border border-black px-2 py-2 text-center" colspan="2">JUMLAH</td>
                        <td class="border border-black px-2 py-2 text-center">
                            {{ number_format($totals['total_standard_rating_sum'], 2) }}</td>
                        <td class="border border-black px-2 py-2 text-center">{{ $totals['total_aspects'] }}</td>
                        <td class="border border-black px-2 py-2 text-center">{{ $totals['total_weight'] }}</td>
                        <td class="border border-black px-2 py-2 text-center">
                            {{ number_format($totals['total_rating_sum'], 2) }}</td>
                        <td class="border border-black px-2 py-2 text-center">
                            {{ number_format($totals['total_score'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif

        {{-- CHART SATU - Rating --}}
        @if (count($chartData['labels']) > 0)
        <div class="border border-black mb-6 mt-6 p-4 bg-gray-50" wire:ignore id="chart-rating-{{ $chartId }}">
            <div class="text-center text-xs font-bold mb-4">
                Gambar Rating Standar Atribut Managerial Kompetensi Mapping Static Pribadi Spider Plot
            </div>
            <div class="flex justify-center">
                <canvas id="chartRating-{{ $chartId }}" style="max-width:600px; max-height:400px;"></canvas>
            </div>
        </div>

        {{-- Tabel Mapping Summary --}}
        <div class="mb-2 mt-6 font-bold text-xs bg-blue-100 border border-black px-2 py-2 text-center">
            ATRIBUT MANAGERIAL KOMPETENSI MAPPING
        </div>
        <table class="w-full border border-black text-xs mb-4">
            <thead>
                <tr class="bg-blue-100">
                    <th class="border border-black px-2 py-2 w-12">No.</th>
                    <th class="border border-black px-2 py-2">ATRIBUT</th>
                    <th class="border border-black px-2 py-2 w-24">JUMLAH ATRIBUT</th>
                    <th class="border border-black px-2 py-2 w-20">BOBOT (%)</th>
                    <th class="border border-black px-2 py-2 w-24">RATING Rata-Rata</th>
                    <th class="border border-black px-2 py-2 w-20">SKOR</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categoryData as $catIndex => $category)
                @foreach ($category['aspects'] as $aspectIndex => $aspect)
                <tr>
                    <td class="border border-black px-2 py-1 text-center">
                        {{ $romanNumerals[$aspectIndex] ?? $aspectIndex + 1 }}</td>
                    <td class="border border-black px-2 py-1">{{ $aspect['name'] }}</td>
                    <td class="border border-black px-2 py-1 text-center">
                        {{ $aspect['attribute_count'] }}</td>
                    <td class="border border-black px-2 py-1 text-center">
                        {{ $aspect['weight_percentage'] }}</td>
                    <td class="border border-black px-2 py-1 text-center">
                        {{ number_format($aspect['standard_rating'], 2) }}</td>
                    <td class="border border-black px-2 py-1 text-center">
                        {{ number_format($aspect['score'], 2) }}</td>
                </tr>
                @endforeach
                @endforeach
            </tbody>
            <tfoot>
                <tr class="font-bold bg-blue-100">
                    <td class="border border-black px-2 py-2 text-center" colspan="2">Jumlah</td>
                    <td class="border border-black px-2 py-2 text-center">{{ $totals['total_aspects'] }}</td>
                    <td class="border border-black px-2 py-2 text-center">{{ $totals['total_weight'] }}</td>
                    <td class="border border-black px-2 py-2 text-center">
                        {{ number_format($totals['total_rating_sum'], 2) }}</td>
                    <td class="border border-black px-2 py-2 text-center">
                        {{ number_format($totals['total_score'], 2) }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- CHART DUA - Skor --}}
        <div class="border border-black mb-8 mt-6 p-4 bg-gray-50" wire:ignore id="chart-skor-{{ $chartId }}">
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
                <div class="border-b border-black w-3/4 mb-1"></div>
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

                    function initChart() {
                        const canvas = document.getElementById('chartRating-{{ $chartId }}');
                        if (!canvas) return;

                        const ctx = canvas.getContext('2d');

                        chartInstance = new Chart(ctx, {
                            type: 'radar',
                            data: {
                                labels: chartLabels,
                                datasets: [{
                                    label: templateName,
                                    data: chartRatings,
                                    borderColor: '#1e40af',
                                    backgroundColor: 'rgba(30,64,175,0.2)',
                                    pointBackgroundColor: '#1e40af',
                                    pointBorderColor: '#1e40af',
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
                                            }
                                        }
                                    },
                                    datalabels: {
                                        backgroundColor: 'rgba(139, 69, 19, 0.85)',
                                        borderRadius: 4,
                                        color: 'white',
                                        font: {
                                            weight: 'bold',
                                            size: 11
                                        },
                                        padding: 6
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
                                            }
                                        },
                                        pointLabels: {
                                            font: {
                                                size: 10
                                            }
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
                                chartLabels = chartData.labels;
                                chartRatings = chartData.ratings;
                                templateName = chartData.templateName || 'Standard';

                                // Update chart data
                                window.ratingChart_{{ $chartId }}.data.labels = chartLabels;
                                window.ratingChart_{{ $chartId }}.data.datasets[0].label =
                                    templateName;
                                window.ratingChart_{{ $chartId }}.data.datasets[0].data =
                                    chartRatings;
                                window.ratingChart_{{ $chartId }}.update('active');
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

                    function initChart() {
                        const canvas = document.getElementById('chartSkor-{{ $chartId }}');
                        if (!canvas) return;

                        const ctx = canvas.getContext('2d');

                        chartInstance = new Chart(ctx, {
                            type: 'radar',
                            data: {
                                labels: chartLabels,
                                datasets: [{
                                    label: templateName,
                                    data: chartScores,
                                    borderColor: '#dc2626',
                                    backgroundColor: 'rgba(220,38,38,0.15)',
                                    pointBackgroundColor: '#dc2626',
                                    pointBorderColor: '#dc2626',
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
                                            }
                                        }
                                    },
                                    datalabels: {
                                        backgroundColor: 'rgba(139, 69, 19, 0.85)',
                                        borderRadius: 4,
                                        color: 'white',
                                        font: {
                                            weight: 'bold',
                                            size: 11
                                        },
                                        padding: 6
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
                                            }
                                        },
                                        pointLabels: {
                                            font: {
                                                size: 10
                                            }
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

                        Livewire.on('chartDataUpdated', function(data) {
                            let chartData = Array.isArray(data) && data.length > 0 ? data[0] : data;
                            if (window.skorChart_{{ $chartId }} && chartData) {
                                chartLabels = chartData.labels;
                                chartScores = chartData.scores;
                                templateName = chartData.templateName || 'Standard';

                                // Update chart data
                                window.skorChart_{{ $chartId }}.data.labels = chartLabels;
                                window.skorChart_{{ $chartId }}.data.datasets[0].label = templateName;
                                window.skorChart_{{ $chartId }}.data.datasets[0].data = chartScores;
                                window.skorChart_{{ $chartId }}.update('active');
                            }
                        });
                    });
                }

                setupSkorChart();
            })();
    </script>
    @endif
</div>