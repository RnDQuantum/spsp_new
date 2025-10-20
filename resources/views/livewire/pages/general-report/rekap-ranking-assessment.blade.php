    <div class="bg-white mx-auto my-8 shadow overflow-hidden" style="max-width: 1300px;">
        <!-- Header Section -->
        <div class="border-b-4 border-black py-4 bg-white">
            <h1 class="text-center text-2xl font-bold uppercase tracking-wide text-black">
                RANKING REKAP SKOR PENILAIAN AKHIR ASSESSMENT
            </h1>

            <!-- Event Filter -->
            <div class="flex justify-center items-center gap-4 mt-3 px-6">
                <div class="w-full max-w-md">
                    @livewire('components.event-selector', ['showLabel' => false])
                </div>
            </div>

            <!-- Position Filter -->
            <div class="flex justify-center items-center gap-4 mt-3 px-6">
                <div class="w-full max-w-md">
                    @livewire('components.position-selector', ['showLabel' => false])
                </div>
            </div>
        </div>
        <!-- Toleransi Section -->
        @php $summary = $this->getPassingSummary(); @endphp
        @livewire('components.tolerance-selector', [
            'passing' => $summary['passing'],
            'total' => $summary['total'],
            'showSummary' => false,
        ])
        <!-- Enhanced Table Section -->
        <div class="px-6 pb-6 bg-white overflow-x-auto">
            <table class="min-w-full border-2 border-black text-sm text-gray-900 mt-6">
                <thead>
                    <tr class="bg-cyan-200">
                        <th class="border-2 border-black px-2 py-2 text-center align-middle" rowspan="2">NO</th>
                        <th class="border-2 border-black px-2 py-2 text-center align-middle" rowspan="2">NAMA</th>
                        <th class="border-2 border-black px-2 py-2 text-center" colspan="2">SKOR INDIVIDU</th>
                        <th class="border-2 border-black px-2 py-2 text-center align-middle" rowspan="2">TOTAL SKOR
                        </th>
                        <th class="border-2 border-black px-2 py-2 text-center" colspan="2">SKOR PENILAIAN AKHIR</th>
                        <th class="border-2 border-black px-2 py-2 text-center align-middle" rowspan="2">TOTAL SKOR
                        </th>
                        <th class="border-2 border-black px-2 py-2 text-center align-middle" rowspan="2">GAP</th>
                        <th class="border-2 border-black px-2 py-2 text-center align-middle" rowspan="2">KESIMPULAN
                        </th>
                    </tr>
                    <tr class="bg-cyan-200">
                        <th class="border-2 border-black px-2 py-2 text-center">PSYCHOLOGY</th>
                        <th class="border-2 border-black px-2 py-2 text-center">KOMPETENSI MANAGERIAL</th>
                        <th class="border-2 border-black px-2 py-2 text-center">PSYCHOLOGY {{ $potensiWeight }}%</th>
                        <th class="border-2 border-black px-2 py-2 text-center">KOMPETENSI MANAGERIAL
                            {{ $kompetensiWeight }}%</th>
                    </tr>
                </thead>
                <tbody>
                    @if (isset($rows) && $rows)
                        @foreach ($rows as $row)
                            <tr>
                                <td class="border-2 border-black px-2 py-2 text-center">{{ $row['rank'] }}</td>
                                <td class="border-2 border-black px-2 py-2">{{ $row['name'] }}</td>
                                <td class="border-2 border-black px-2 py-2 text-center">
                                    {{ number_format($row['psy_individual'], 2) }}</td>
                                <td class="border-2 border-black px-2 py-2 text-center">
                                    {{ number_format($row['mc_individual'], 2) }}</td>
                                <td class="border-2 border-black px-2 py-2 text-center">
                                    {{ number_format($row['total_individual'], 2) }}</td>
                                <td class="border-2 border-black px-2 py-2 text-center">
                                    {{ number_format($row['psy_weighted'], 2) }}</td>
                                <td class="border-2 border-black px-2 py-2 text-center">
                                    {{ number_format($row['mc_weighted'], 2) }}</td>
                                <td class="border-2 border-black px-2 py-2 text-center">
                                    {{ number_format($row['total_weighted_individual'], 2) }}</td>
                                <td class="border-2 border-black px-2 py-2 text-center">
                                    {{ number_format($row['gap'], 2) }}</td>
                                <td class="border-2 border-black px-2 py-2 text-center">{{ $row['conclusion'] }}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
            @if (isset($rows) && $rows && $rows->hasPages())
                <div class="mt-4">
                    {{ $rows->links(data: ['scrollTo' => false]) }}
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

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Psychology Standard -->
                        <div class="bg-white border border-blue-200 rounded-lg p-3">
                            <div class="text-xs text-gray-500 mb-1">Standar Psychology {{ $potensiWeight }}%</div>
                            <div class="text-2xl font-bold text-gray-900">
                                {{ number_format($standardInfo['psy_standard'], 2) }}</div>
                        </div>

                        <!-- Management Competency Standard -->
                        <div class="bg-white border border-blue-200 rounded-lg p-3">
                            <div class="text-xs text-gray-500 mb-1">Standar Kompetensi {{ $kompetensiWeight }}%</div>
                            <div class="text-2xl font-bold text-gray-900">
                                {{ number_format($standardInfo['mc_standard'], 2) }}</div>
                        </div>

                        <!-- Total Standard -->
                        <div class="bg-white border border-indigo-300 rounded-lg p-3">
                            <div class="text-xs text-gray-500 mb-1">Total Standar</div>
                            <div class="text-2xl font-bold text-indigo-600">
                                {{ number_format($standardInfo['total_standard'], 2) }}</div>
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

                            // Determine color based on conclusion
                            $bgColor = match ($conclusion) {
                                'Di Atas Standar' => 'bg-green-100 border-green-300',
                                'Memenuhi Standar' => 'bg-blue-100 border-blue-300',
                                'Di Bawah Standar' => 'bg-red-100 border-red-300',
                                default => 'bg-gray-100 border-gray-300',
                            };
                        @endphp

                        <div class="border-2 {{ $bgColor }} rounded-lg p-4 text-center">
                            <div class="text-3xl font-bold text-gray-900">{{ $count }}</div>
                            <div class="text-sm text-gray-600 mb-2">{{ $percentage }}%</div>
                            <div class="text-sm text-gray-700 font-semibold leading-tight mb-2">{{ $conclusion }}
                            </div>
                            <div class="text-xs text-gray-500 font-medium">
                                @switch($conclusion)
                                    @case('Di Atas Standar')
                                        Gap > 0
                                    @break

                                    @case('Memenuhi Standar')
                                        Gap ≥ Threshold
                                    @break

                                    @case('Di Bawah Standar')
                                Gap < Threshold @break @endswitch </div>
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
                        <strong>Keterangan:</strong> Kesimpulan berdasarkan Gap (Total Weighted Individual - Total
                        Weighted Standard) dan threshold toleransi
                        <span x-data
                            x-text="$wire.tolerancePercentage > 0 ? '(' + $wire.tolerancePercentage + '%)' : '(0%)'"></span>.
                        <br>
                        <strong>Rumus:</strong>
                        <ul class="list-disc ml-6 mt-1">
                            <li>Gap = Total Weighted Individual - Total Weighted Standard</li>
                            <li>Threshold = -Total Weighted Standard × (Tolerance / 100)</li>
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
            <div class="mt-8 border-t-2 border-black pt-6 bg-white">
                <div class="px-6 pb-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 text-center">Capacity Building Rekap Assessment</h3>

                    <!-- Content Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                        <!-- Chart Section -->
                        <div x-data="{
                            refreshChart() {
                                const labels = @js($chartLabels);
                                const data = @js($chartData);
                                const colors = @js($chartColors);
                                if (labels.length > 0 && data.length > 0) {
                                    createConclusionChart(labels, data, colors);
                                }
                            }
                        }" x-init="$nextTick(() => refreshChart())"
                            class="border border-gray-300 p-6 rounded-lg bg-gray-50 transition-shadow duration-300 hover:shadow-xl">
                            <div wire:ignore>
                                <canvas id="conclusionPieChart" class="w-full h-full"></canvas>
                            </div>
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

                                            // Determine color based on conclusion
                                            $bgColor = match ($conclusion) {
                                                'Di Atas Standar' => 'bg-green-100 border-green-300',
                                                'Memenuhi Standar' => 'bg-blue-100 border-blue-300',
                                                'Di Bawah Standar' => 'bg-red-100 border-red-300',
                                                default => 'bg-gray-100 border-gray-300',
                                            };
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
