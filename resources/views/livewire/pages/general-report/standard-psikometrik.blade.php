<div class="max-w-7xl mx-auto">
    <div class="bg-white p-6 rounded shadow text-gray-900">

        {{-- Filter Section --}}
        <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-300">
            <div class="flex items-center gap-4">
                <label class="text-sm font-semibold text-gray-700 min-w-[120px]">
                    Pilih Event:
                </label>
                <select wire:model.live="eventCode" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @foreach($availableEvents as $evt)
                        <option value="{{ $evt['code'] }}">{{ $evt['name'] }} - {{ $evt['institution'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Template Info (Display Only) --}}
            @if($selectedTemplate)
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <div class="flex items-start gap-2 text-sm text-gray-600">
                        <span class="font-semibold">📄 Template:</span>
                        <div>
                            <div class="font-semibold text-gray-900">{{ $selectedTemplate->name }}</div>
                            @if($selectedTemplate->description)
                                <div class="text-xs text-gray-500 mt-1">{{ $selectedTemplate->description }}</div>
                            @endif
                        </div>
                    </div>
                    @if($selectedEvent)
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
            STANDAR PEMETAAN POTENSI INDIVIDU "STATIC PRIBADI SPIDER PLOT"
        </div>

        {{-- Identitas --}}
        @if($selectedEvent && $selectedTemplate)
        <table class="w-full border border-black text-xs mb-4">
            <tr>
                <td class="border border-black px-2 py-1 bg-blue-100 font-semibold w-1/5">Perusahaan/Lembaga</td>
                <td class="border border-black px-2 py-1 w-2/5">{{ strtoupper($selectedEvent->institution->name ?? 'N/A') }}</td>
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
        @if(count($categoryData) > 0)
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
                    @php $romanNumerals = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X']; @endphp

                    @foreach($categoryData as $catIndex => $category)
                        {{-- Aspects within Potensi Category --}}
                        @foreach($category['aspects'] as $aspectIndex => $aspect)
                            {{-- Aspect Row (if has sub-aspects, show as header) --}}
                            @if(count($aspect['sub_aspects']) > 0)
                                <tr>
                                    <td class="border border-black px-2 py-2 text-center font-bold">{{ $romanNumerals[$aspectIndex] ?? ($aspectIndex + 1) }}</td>
                                    <td class="border border-black px-2 py-2 font-bold">{{ $aspect['name'] }}</td>
                                    <td class="border border-black px-2 py-2 text-center"></td>
                                    <td class="border border-black px-2 py-2 text-center font-bold">{{ $aspect['sub_aspects_count'] }}</td>
                                    <td class="border border-black px-2 py-2 text-center font-bold">{{ $aspect['weight_percentage'] }}</td>
                                    <td class="border border-black px-2 py-2 text-center font-bold">{{ number_format($aspect['standard_rating'], 2) }}</td>
                                    <td class="border border-black px-2 py-2 text-center font-bold">{{ number_format($aspect['score'], 2) }}</td>
                                </tr>

                                {{-- Sub-Aspects --}}
                                @foreach($aspect['sub_aspects'] as $subIndex => $subAspect)
                                    <tr>
                                        <td class="border border-black px-2 py-2 text-center">{{ $subIndex + 1 }}</td>
                                        <td class="border border-black px-2 py-2 pl-8">{{ $subAspect['name'] }}</td>
                                        <td class="border border-black px-2 py-2 text-center">{{ $subAspect['standard_rating'] }}</td>
                                        <td class="border border-black px-2 py-2"></td>
                                        <td class="border border-black px-2 py-2"></td>
                                        <td class="border border-black px-2 py-2"></td>
                                        <td class="border border-black px-2 py-2"></td>
                                    </tr>
                                @endforeach
                            @else
                                {{-- Aspect without sub-aspects (Kompetensi) --}}
                                <tr>
                                    <td class="border border-black px-2 py-2 text-center">{{ $aspectIndex + 1 }}</td>
                                    <td class="border border-black px-2 py-2 pl-4">{{ $aspect['name'] }}</td>
                                    <td class="border border-black px-2 py-2 text-center">{{ number_format($aspect['standard_rating'], 2) }}</td>
                                    <td class="border border-black px-2 py-2"></td>
                                    <td class="border border-black px-2 py-2 text-center">{{ $aspect['weight_percentage'] }}</td>
                                    <td class="border border-black px-2 py-2"></td>
                                    <td class="border border-black px-2 py-2"></td>
                                </tr>
                            @endif
                        @endforeach
                    @endforeach

                    {{-- TOTAL --}}
                    <tr class="bg-blue-100 font-bold">
                        <td class="border border-black px-2 py-2 text-center" colspan="2">JUMLAH</td>
                        <td class="border border-black px-2 py-2 text-center">{{ number_format($totals['total_standard_rating_sum'], 2) }}</td>
                        <td class="border border-black px-2 py-2 text-center">{{ $totals['total_aspects'] }}</td>
                        <td class="border border-black px-2 py-2 text-center">{{ $totals['total_weight'] }}</td>
                        <td class="border border-black px-2 py-2 text-center">{{ number_format($totals['total_rating_sum'], 2) }}</td>
                        <td class="border border-black px-2 py-2 text-center">{{ number_format($totals['total_score'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif

        {{-- CHART SATU - Rating --}}
        @if(count($chartData['labels']) > 0)
        <div class="border border-black mb-6 mt-6 p-4 bg-gray-50">
            <div class="text-center text-xs font-bold mb-4">
                Gambar Rating Standar Atribut Potensi Mapping Static Pribadi Spider Plot
            </div>
            <div class="flex justify-center">
                <canvas id="chartRating" style="max-width:600px; max-height:400px;"></canvas>
            </div>
        </div>

        {{-- Tabel Mapping Summary --}}
        <div class="mb-2 mt-6 font-bold text-xs bg-blue-100 border border-black px-2 py-2 text-center">
            ATRIBUT POTENSI MAPPING
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
                @foreach($categoryData as $catIndex => $category)
                    @foreach($category['aspects'] as $aspectIndex => $aspect)
                    <tr>
                        <td class="border border-black px-2 py-1 text-center">{{ $romanNumerals[$aspectIndex] ?? ($aspectIndex + 1) }}</td>
                        <td class="border border-black px-2 py-1">{{ $aspect['name'] }}</td>
                        <td class="border border-black px-2 py-1 text-center">{{ $aspect['sub_aspects_count'] }}</td>
                        <td class="border border-black px-2 py-1 text-center">{{ $aspect['weight_percentage'] }}</td>
                        <td class="border border-black px-2 py-1 text-center">{{ number_format($aspect['standard_rating'], 2) }}</td>
                        <td class="border border-black px-2 py-1 text-center">{{ number_format($aspect['score'], 2) }}</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
            <tfoot>
                <tr class="font-bold bg-blue-100">
                    <td class="border border-black px-2 py-2 text-center" colspan="2">Jumlah</td>
                    <td class="border border-black px-2 py-2 text-center">{{ $totals['total_aspects'] }}</td>
                    <td class="border border-black px-2 py-2 text-center">{{ $totals['total_weight'] }}</td>
                    <td class="border border-black px-2 py-2 text-center">{{ number_format($totals['total_rating_sum'], 2) }}</td>
                    <td class="border border-black px-2 py-2 text-center">{{ number_format($totals['total_score'], 2) }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- CHART DUA - Skor --}}
        <div class="border border-black mb-8 mt-6 p-4 bg-gray-50">
            <div class="text-center text-xs font-bold mb-4">
                Gambar Skor Standar Atribut Potensi Mapping Static Pribadi Spider Plot
            </div>
            <div class="flex justify-center">
                <canvas id="chartSkor" style="max-width:600px; max-height:400px;"></canvas>
            </div>
        </div>
        @endif

        {{-- Footer TTD dan Catatan --}}
        @if($selectedEvent)
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
        @endif
    </div>

    @if(count($chartData['labels']) > 0)
    @script
    <script>
        let chartRating = null;
        let chartSkor = null;

        function initCharts() {
            const labels = @js($chartData['labels']);
            const ratings = @js($chartData['ratings']);
            const scores = @js($chartData['scores']);
            const templateName = @js($selectedTemplate?->name ?? 'Standard');

            // Destroy existing charts if they exist
            if (chartRating) {
                chartRating.destroy();
            }
            if (chartSkor) {
                chartSkor.destroy();
            }

            // Radar Chart 1 (Rating)
            const ctx1 = document.getElementById('chartRating');
            if (ctx1) {
                chartRating = new Chart(ctx1, {
                    type: 'radar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: templateName,
                            data: ratings,
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
            }

            // Radar Chart 2 (Skor)
            const ctx2 = document.getElementById('chartSkor');
            if (ctx2) {
                chartSkor = new Chart(ctx2, {
                    type: 'radar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: templateName,
                            data: scores,
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
                            }
                        },
                        scales: {
                            r: {
                                beginAtZero: true,
                                min: 0,
                                max: 100,
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
            }
        }

        // Initialize charts on page load
        initCharts();
    </script>
    @endscript
    @endif
</div>
