    <div class="bg-white mx-auto my-8 shadow overflow-hidden" style="max-width: 1200px;">
        <!-- Header Section -->
        <div class="border-b-4 border-black py-4 bg-white">
            <h1 class="text-center text-2xl font-bold uppercase tracking-wide text-black">
                RANKING SKOR PSYCHOLOGY MAPPING
            </h1>
            <div class="flex justify-center items-center gap-4 mt-3 px-6">
                <div class="w-full max-w-md">
                    @livewire('components.event-selector', ['showLabel' => false])
                </div>
            </div>
            <div class="flex justify-center items-center gap-4 mt-3 px-6">
                <div class="w-full max-w-md">
                    @livewire('components.position-selector', ['showLabel' => false])
                </div>
            </div>
        </div>

        @php $summary = $this->getPassingSummary(); @endphp
        @livewire('components.tolerance-selector', [
            'passing' => $summary['passing'],
            'total' => $summary['total'],
            'showSummary' => false,
        ])

        <!-- Table Section -->
        <div class="px-6 pb-6 bg-white overflow-x-auto">
            <table class="min-w-full border-2 border-black text-sm text-gray-900 mt-6">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border-2 border-black px-3 py-3 text-center">Ranking</th>
                        <th class="border-2 border-black px-3 py-3 text-center">NIP</th>
                        <th class="border-2 border-black px-3 py-3 text-center">Nama</th>
                        <th class="border-2 border-black px-3 py-3 text-center">Jabatan</th>
                        <th class="border-2 border-black px-3 py-3 text-center" colspan="2">
                            <span x-data
                                x-text="$wire.tolerancePercentage > 0 ? 'Standard (-' + $wire.tolerancePercentage + '%)' : 'Standard'"></span>
                        </th>
                        <th class="border-2 border-black px-3 py-3 text-center" colspan="2">Individu</th>
                        <th class="border-2 border-black px-3 py-3 text-center" colspan="2">Gap</th>
                        <th class="border-2 border-black px-3 py-3 text-center">Prosentase<br>Kesesuaian</th>
                        <th class="border-2 border-black px-3 py-3 text-center">Kesimpulan/Conclusion</th>
                    </tr>
                    <tr class="bg-gray-200">
                        <th class="border-2 border-black px-3 py-1"></th>
                        <th class="border-2 border-black px-3 py-1"></th>
                        <th class="border-2 border-black px-3 py-1"></th>
                        <th class="border-2 border-black px-3 py-1"></th>
                        <th class="border-2 border-black px-3 py-1 font-semibold">Rating/<br>Level</th>
                        <th class="border-2 border-black px-3 py-1 font-semibold">Score</th>
                        <th class="border-2 border-black px-3 py-1 font-semibold">Rating/<br>Level</th>
                        <th class="border-2 border-black px-3 py-1 font-semibold">Score</th>
                        <th class="border-2 border-black px-3 py-1 font-semibold">Rating/<br>Level</th>
                        <th class="border-2 border-black px-3 py-1 font-semibold">Score</th>
                        <th class="border-2 border-black px-3 py-1"></th>
                        <th class="border-2 border-black px-3 py-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @if ($rankings && $rankings->count() > 0)
                        @foreach ($rankings as $row)
                            <tr>
                                <td class="border-2 border-black px-3 py-2 text-center">{{ $row['rank'] }}</td>
                                <td class="border-2 border-black px-3 py-2 text-center">{{ $row['nip'] }}</td>
                                <td class="border-2 border-black px-3 py-2">{{ $row['name'] }}</td>
                                <td class="border-2 border-black px-3 py-2">{{ $row['position'] }}</td>
                                <td class="border-2 border-black px-3 py-2 text-center">
                                    {{ number_format($row['standard_rating'], 2) }}</td>
                                <td class="border-2 border-black px-3 py-2 text-center">
                                    {{ number_format($row['standard_score'], 2) }}</td>
                                <td class="border-2 border-black px-3 py-2 text-center">
                                    {{ number_format($row['individual_rating'], 2) }}</td>
                                <td class="border-2 border-black px-3 py-2 text-center">
                                    {{ number_format($row['individual_score'], 2) }}</td>
                                <td class="border-2 border-black px-3 py-2 text-center">
                                    {{ number_format($row['gap_rating'], 2) }}</td>
                                <td class="border-2 border-black px-3 py-2 text-center">
                                    {{ number_format($row['gap_score'], 2) }}</td>
                                <td class="border-2 border-black px-3 py-2 text-center">
                                    {{ number_format($row['percentage_score'], 2) }}%</td>
                                <td class="border-2 border-black px-3 py-2 text-center">{{ $row['conclusion'] }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="12" class="border-2 border-black px-3 py-4 text-center text-gray-500">
                                Tidak ada data untuk ditampilkan. Silakan pilih event dan jabatan.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
            @if ($rankings?->hasPages())
                <div class="mt-4">
                    {{ $rankings->links(data: ['scrollTo' => false]) }}
                </div>
            @endif
        </div>

        <!-- Standard & Threshold Info Box -->
        @if ($standardInfo)
            <div class="px-6 pb-6 bg-white">
                <div
                    class="bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-300 rounded-lg p-4 shadow-sm">
                    <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                            </path>
                        </svg>
                        Informasi Standar
                        <span x-data
                            x-text="$wire.tolerancePercentage > 0 ? '(Toleransi -' + $wire.tolerancePercentage + '%)' : '(Tanpa Toleransi)'"
                            class="text-sm font-normal text-blue-600"></span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Original Standard -->
                        <div class="bg-white border border-blue-200 rounded-lg p-3">
                            <div class="text-xs text-gray-500 mb-1">Standar Original (100%)</div>
                            <div class="text-2xl font-bold text-gray-900">
                                {{ number_format($standardInfo['original_standard'], 2) }}</div>
                        </div>

                        <!-- Adjusted Standard -->
                        <div class="bg-white border border-indigo-300 rounded-lg p-3">
                            <div class="text-xs text-gray-500 mb-1">Standar Adjusted
                                <span x-data
                                    x-text="$wire.tolerancePercentage > 0 ? '(-' + $wire.tolerancePercentage + '%)' : ''"></span>
                            </div>
                            <div class="text-2xl font-bold text-indigo-600">
                                {{ number_format($standardInfo['adjusted_standard'], 2) }}</div>
                        </div>

                        <!-- Threshold -->
                        <div class="bg-white border border-orange-300 rounded-lg p-3">
                            <div class="text-xs text-gray-500 mb-1">Threshold (Batas Toleransi)</div>
                            <div class="text-2xl font-bold text-orange-600">
                                {{ number_format($standardInfo['threshold'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Summary Statistics Section -->
        @if (!empty($conclusionSummary))
            <div class="px-6 pb-6 bg-gray-50 border-t-2 border-black">
                <h3 class="text-lg font-bold text-gray-900 mb-4 mt-4">Ringkasan Kesimpulan</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    @foreach ($conclusionSummary as $conclusion => $count)
                        @php
                            $totalParticipants = array_sum($conclusionSummary);
                            $percentage = $totalParticipants > 0 ? round(($count / $totalParticipants) * 100, 1) : 0;

                            // Get color and range from conclusionConfig
                            $config = $this->conclusionConfig[$conclusion] ?? null;
                            $bgColor = $config['tailwindClass'] ?? 'bg-gray-100 border-gray-300';
                            $rangeText = $config['rangeText'] ?? '-';
                        @endphp

                        <div class="border-2 {{ $bgColor }} rounded-lg p-4 text-center">
                            <div class="text-3xl font-bold text-gray-900">{{ $count }}</div>
                            <div class="text-sm text-gray-600 mb-2">{{ $percentage }}%</div>
                            <div class="text-sm text-gray-700 font-semibold leading-tight mb-2">{{ $conclusion }}
                            </div>
                            <div class="text-xs text-gray-500 font-medium">{{ $rangeText }}</div>
                        </div>
                    @endforeach
                </div>

                <!-- Overall Statistics -->
                @php
                    $totalParticipants = array_sum($conclusionSummary);
                    $passingCount =
                        ($conclusionSummary['Di Atas Standar'] ?? 0) + ($conclusionSummary['Memenuhi Standar'] ?? 0);
                    $passingPercentage =
                        $totalParticipants > 0 ? round(($passingCount / $totalParticipants) * 100, 1) : 0;
                @endphp

                <div class="bg-white border-2 border-black rounded-lg p-4">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <div class="text-lg font-bold text-gray-900">{{ $totalParticipants }}</div>
                            <div class="text-sm text-gray-600">Total Peserta</div>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-green-600">{{ $passingCount }}</div>
                            <div class="text-sm text-gray-600">Memenuhi Standar</div>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-blue-600">{{ $passingPercentage }}%</div>
                            <div class="text-sm text-gray-600">Tingkat Kelulusan</div>
                        </div>
                    </div>
                </div>

                <!-- Keterangan Rentang Nilai -->
                <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <div class="text-sm text-blue-800">
                        <strong>Keterangan:</strong> Kesimpulan berdasarkan Gap (Individual Score - Adjusted Standard)
                        dan
                        threshold toleransi
                        <span x-data
                            x-text="$wire.tolerancePercentage > 0 ? '(' + $wire.tolerancePercentage + '%)' : '(0%)'"></span>.
                        <br>
                        <strong>Rumus:</strong>
                        <ul class="list-disc ml-6 mt-1">
                            <li>Gap = Individual Score - Adjusted Standard</li>
                            <li>Threshold = -Adjusted Standard × (Tolerance / 100)</li>
                            <li><strong>Di Atas Standar:</strong> Gap > 0</li>
                            <li><strong>Memenuhi Standar:</strong> Gap ≥ Threshold (dalam range toleransi)</li>
                            <li><strong>Di Bawah Standar:</strong> Gap < Threshold</li>
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Pie Chart Section -->
        @if (!empty($conclusionSummary))
            <div class="mt-8 border-t-2 border-black pt-6 bg-white" x-data="{
                refreshChart() {
                    const labels = @js($chartLabels);
                    const data = @js($chartData);
                    const colors = @js($chartColors);
                    if (labels.length > 0 && data.length > 0) {
                        createConclusionChart(labels, data, colors);
                    }
                }
            }" x-init="$nextTick(() => refreshChart())">
                <div class="px-6 pb-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 text-center">Capacity Building Psychology Mapping
                    </h3>

                    <!-- Content Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                        <!-- Chart Section -->
                        <div class="border border-gray-300 p-6 rounded-lg bg-gray-50 transition-shadow duration-300 hover:shadow-xl"
                            wire:ignore>
                            <canvas id="conclusionPieChart" class="w-full h-full"></canvas>
                        </div>

                        <!-- Table Section -->
                        <div class="border-2 border-gray-300 rounded-lg overflow-hidden">
                            <table class="w-full text-sm text-gray-900">
                                <thead>
                                    <tr class="bg-gray-200">
                                        <th class="border-2 border-gray-400 px-4 py-3 text-center font-bold">KETERANGAN
                                        </th>
                                        <th class="border-2 border-gray-400 px-4 py-3 text-center font-bold">JUMLAH
                                        </th>
                                        <th class="border-2 border-gray-400 px-4 py-3 text-center font-bold">PROSENTASE
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    @php
                                        $totalParticipants = array_sum($conclusionSummary);
                                    @endphp
                                    @foreach ($conclusionSummary as $conclusion => $count)
                                        @php
                                            $percentage =
                                                $totalParticipants > 0
                                                    ? round(($count / $totalParticipants) * 100, 2)
                                                    : 0;

                                            // Get color from conclusionConfig
                                            $config = $this->conclusionConfig[$conclusion] ?? null;
                                            $bgColor = $config['tailwindClass'] ?? 'bg-gray-100 border-gray-300';
                                        @endphp
                                        <tr>
                                            <td class="border-2 border-gray-400 px-4 py-3 {{ $bgColor }}">
                                                {{ $conclusion }}</td>
                                            <td class="border-2 border-gray-400 px-4 py-3 text-center">
                                                {{ $count }}
                                                orang
                                            </td>
                                            <td class="border-2 border-gray-400 px-4 py-3 text-center">
                                                {{ $percentage }}%
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-gray-100 font-semibold">
                                        <td class="border-2 border-gray-400 px-4 py-3">Jumlah Responden</td>
                                        <td class="border-2 border-gray-400 px-4 py-3 text-center">
                                            {{ $totalParticipants }} orang</td>
                                        <td class="border-2 border-gray-400 px-4 py-3 text-center">100.00%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        let conclusionPieChart = null;

        function createConclusionChart(labels, data, colors) {
            const canvas = document.getElementById('conclusionPieChart');
            if (!canvas) return;

            // Destroy existing chart if exists to prevent cropping issues
            if (conclusionPieChart) {
                conclusionPieChart.destroy();
                conclusionPieChart = null;
            }

            // Reset canvas dimensions to ensure proper sizing
            canvas.style.width = '';
            canvas.style.height = '';
            canvas.width = canvas.offsetWidth;
            canvas.height = canvas.offsetHeight;

            const chartData = {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    // Hover effects
                    hoverBackgroundColor: colors.map(color => {
                        // Lighten color on hover
                        return color + 'dd'; // Add transparency
                    }),
                    hoverBorderColor: '#333',
                    hoverBorderWidth: 3,
                    hoverOffset: 25 // Push slice out on hover
                }]
            };

            const config = {
                type: 'pie',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    // Add padding to prevent label clipping
                    layout: {
                        padding: {
                            top: 50,
                            bottom: 50,
                            left: 100,
                            right: 100
                        }
                    },
                    // Smooth animations
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 800,
                        easing: 'easeInOutQuart'
                    },
                    // Hover mode settings
                    hover: {
                        mode: 'nearest',
                        intersect: true,
                        animationDuration: 400
                    },
                    // Cursor pointer on hover
                    onHover: (event, activeElements) => {
                        event.native.target.style.cursor = activeElements.length > 0 ? 'pointer' : 'default';
                    },
                    plugins: {
                        legend: {
                            display: false,

                        },
                        tooltip: {
                            enabled: true,
                            backgroundColor: 'rgba(0, 0, 0, 0.85)',
                            padding: 14,
                            cornerRadius: 8,
                            titleFont: {
                                size: 15,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 14
                            },
                            displayColors: true,
                            boxWidth: 20,
                            boxHeight: 20,
                            boxPadding: 8,
                            caretSize: 8,
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(2) : 0;
                                    return `${label}: ${value} orang (${percentage}%)`;
                                }
                            }
                        },
                        // Menggunakan chartjs-plugin-datalabels untuk label yang lebih jelas
                        datalabels: {
                            // Posisikan label di luar chart
                            anchor: 'end',
                            align: 'end',
                            offset: 10,

                            // Style untuk background label
                            backgroundColor: function(context) {
                                return 'rgba(255, 255, 255, 0.95)';
                            },
                            borderColor: function(context) {
                                return context.dataset.backgroundColor[context.dataIndex];
                            },
                            borderWidth: 2,
                            borderRadius: 4,

                            // Style text
                            color: '#000',
                            font: {
                                weight: 'bold',
                                size: 13
                            },

                            // Padding di dalam box label
                            padding: 6,

                            // Formatter untuk menampilkan nilai dan persentase
                            formatter: function(value, context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(2) : '0.00';

                                // Skip label jika value = 0 (kecuali index pertama)
                                if (value === 0 && context.dataIndex !== 0) {
                                    return '';
                                }

                                return `${value} orang\n${percentage}%`;
                            },

                            // Tampilkan hanya jika value > 0 atau index pertama
                            display: function(context) {
                                return context.dataset.data[context.dataIndex] > 0 || context.dataIndex === 0;
                            },

                            // Style text alignment
                            textAlign: 'center',

                            // Tambahkan line connector dari chart ke label
                            listeners: {
                                enter: function(context) {
                                    // Optional: bisa tambahkan interaktivitas
                                }
                            }
                        }
                    }
                }
            };

            conclusionPieChart = new Chart(canvas, config);
        }

        // Listen for Livewire events to recreate chart
        // This listener is set up once and will work for all updates
        document.addEventListener('DOMContentLoaded', function() {
            Livewire.on('pieChartDataUpdated', function(data) {
                let chartData = Array.isArray(data) && data.length > 0 ? data[0] : data;

                // Only recreate chart if we have valid data (not empty)
                if (chartData && chartData.labels && chartData.data &&
                    chartData.labels.length > 0 && chartData.data.length > 0) {
                    // Recreate chart completely to avoid cropping issues
                    createConclusionChart(chartData.labels, chartData.data, chartData.colors);
                }
            });
        });
    </script>
