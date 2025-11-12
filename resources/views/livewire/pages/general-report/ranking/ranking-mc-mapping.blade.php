<div class="bg-white dark:bg-gray-900 mx-auto my-8 shadow overflow-hidden" style="max-width: 1200px;">
    <!-- Header Section -->
    <div class="border-b-4 border-black dark:border-gray-600 py-4 bg-white dark:bg-gray-800">
        <h1 class="text-center text-2xl font-bold uppercase tracking-wide text-black dark:text-white">
            PERINGKAT SKOR <i>MANAGERIAL COMPETENCY MAPPING</i>
        </h1>

        <!-- Event Filter -->
        <div class="flex justify-center items-center gap-4 mt-3 px-6">
            <div class="w-full max-w-md">
                @livewire('components.event-selector', ['showLabel' => true])
            </div>
        </div>

        <!-- Position Filter -->
        <div class="flex justify-center items-center gap-4 mt-3 px-6">
            <div class="w-full max-w-md">
                @livewire('components.position-selector', ['showLabel' => true])
            </div>
        </div>
    </div>

    @php $summary = $this->getPassingSummary(); @endphp
    @livewire('components.tolerance-selector', [
    'passing' => $summary['passing'],
    'total' => $summary['total'],
    'showSummary' => false,
    ])

    <!-- Per Page Selector -->
    <div class="px-6 pt-4 bg-white dark:bg-gray-900">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Lihat Data:
                </label>
                <select wire:model.live="perPage"
                    class="border-2 border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="0">All</option>
                </select>
            </div>

            @if ($rankings && $rankings->total() > 0)
            <div class="text-sm text-gray-600 dark:text-gray-400">
                Menampilkan
                <span class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ $rankings->firstItem() ?? 0 }}
                </span>
                -
                <span class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ $rankings->lastItem() ?? 0 }}
                </span>
                dari
                <span class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ $rankings->total() }}
                </span>
                Data
            </div>
            @endif
        </div>
    </div>

    <!-- Table Section -->
    <div class="px-6 pb-6 bg-white dark:bg-gray-900 overflow-x-auto">
        <table
            class="min-w-full border-2 border-black dark:border-gray-600 text-sm text-gray-900 dark:text-gray-100 mt-6">
            <thead>
                <tr class="bg-gray-200 dark:bg-gray-700">
                    <th class="border-2 border-black dark:border-gray-600 px-3 py-3 text-center" rowspan="2">
                        Peringkat
                    </th>
                    <th class="border-2 border-black dark:border-gray-600 px-3 py-3 text-center" rowspan="2">NIP</th>
                    <th class="border-2 border-black dark:border-gray-600 px-3 py-3 text-center" rowspan="2">Nama
                    </th>
                    <th class="border-2 border-black dark:border-gray-600 px-3 py-3 text-center" rowspan="2">Jabatan
                    </th>
                    <th class="border-2 border-black dark:border-gray-600 px-3 py-3 text-center" colspan="2">
                        <span x-data
                            x-text="$wire.tolerancePercentage > 0 ? 'Standard (-' + $wire.tolerancePercentage + '%)' : 'Standard'"></span>
                    </th>
                    <th class="border-2 border-black dark:border-gray-600 px-3 py-3 text-center" colspan="2">Individu
                    </th>
                    <th class="border-2 border-black dark:border-gray-600 px-3 py-3 text-center" colspan="2">Gap</th>
                    <th class="border-2 border-black dark:border-gray-600 px-3 py-3 text-center" rowspan="2">
                        Persentase<br>Kesesuaian</th>
                    <th class="border-2 border-black dark:border-gray-600 px-3 py-3 text-center" rowspan="2">
                        Kesimpulan</th>
                </tr>
                <tr class="bg-gray-200 dark:bg-gray-700">
                    <th class="border-2 border-black dark:border-gray-600 px-3 py-1 font-semibold">Rating</th>
                    <th class="border-2 border-black dark:border-gray-600 px-3 py-1 font-semibold">Skor</th>
                    <th class="border-2 border-black dark:border-gray-600 px-3 py-1 font-semibold">Rating</th>
                    <th class="border-2 border-black dark:border-gray-600 px-3 py-1 font-semibold">Skor</th>
                    <th class="border-2 border-black dark:border-gray-600 px-3 py-1 font-semibold">Rating</th>
                    <th class="border-2 border-black dark:border-gray-600 px-3 py-1 font-semibold">Skor</th>
                </tr>
            </thead>
            <tbody>
                @if ($rankings && $rankings->count() > 0)
                @foreach ($rankings as $row)
                <tr class="bg-white dark:bg-gray-800">
                    <td class="border-2 border-black dark:border-gray-600 px-3 py-2 text-center">
                        {{ $row['rank'] }}</td>
                    <td class="border-2 border-black dark:border-gray-600 px-3 py-2 text-center">
                        {{ $row['nip'] }}</td>
                    <td class="border-2 border-black dark:border-gray-600 px-3 py-2">{{ $row['name'] }}</td>
                    <td class="border-2 border-black dark:border-gray-600 px-3 py-2">{{ $row['position'] }}</td>
                    <td class="border-2 border-black dark:border-gray-600 px-3 py-2 text-center">
                        {{ number_format($row['standard_rating'], 2) }}</td>
                    <td class="border-2 border-black dark:border-gray-600 px-3 py-2 text-center">
                        {{ number_format($row['standard_score'], 2) }}</td>
                    <td class="border-2 border-black dark:border-gray-600 px-3 py-2 text-center">
                        {{ number_format($row['individual_rating'], 2) }}</td>
                    <td class="border-2 border-black dark:border-gray-600 px-3 py-2 text-center">
                        {{ number_format($row['individual_score'], 2) }}</td>
                    <td class="border-2 border-black dark:border-gray-600 px-3 py-2 text-center">
                        {{ number_format($row['gap_rating'], 2) }}</td>
                    <td class="border-2 border-black dark:border-gray-600 px-3 py-2 text-center">
                        {{ number_format($row['gap_score'], 2) }}</td>
                    <td class="border-2 border-black dark:border-gray-600 px-3 py-2 text-center">
                        {{ number_format($row['percentage_score'], 2) }}%</td>
                    <td class="border-2 border-black dark:border-gray-600 px-3 py-2 text-center
                        @php
$c = trim(strtoupper($row['conclusion'])); @endphp

                        @if ($c === 'DI ATAS STANDAR') bg-green-600 text-white font-bold
                        @elseif ($c === 'MEMENUHI STANDAR')
                            bg-yellow-400 text-gray-900 font-bold  // ← KUNING seperti PsyMapping
                        @elseif ($c === 'DI BAWAH STANDAR')
                            bg-red-600 text-white font-bold
                        @else
                            bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white @endif
                            ">
                        {{ $row['conclusion'] }}
                    </td>
                </tr>
                @endforeach
                @else
                <tr class="bg-white dark:bg-gray-800">
                    <td colspan="12"
                        class="border-2 border-black dark:border-gray-600 px-3 py-4 text-center text-gray-500 dark:text-gray-400">
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
    <div class="px-6 pb-6">
        <div class="rounded-lg p-4 shadow-sm">
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-black dark:text-gray-200" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                    </path>
                </svg>
                Informasi Standar
                <span x-data
                    x-text="$wire.tolerancePercentage > 0 ? '(Toleransi -' + $wire.tolerancePercentage + '%)' : '(Tanpa Toleransi)'"
                    class="text-sm font-normal text-black dark:text-gray-200"></span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Original Standard -->
                <div class="bg-white dark:bg-gray-800 border-1 border-gray-400 dark:border-gray-300 rounded-lg p-3">
                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Standar</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ number_format($standardInfo['original_standard'], 2) }}</div>
                </div>

                <!-- Adjusted Standard -->
                <div class="bg-white dark:bg-gray-800 border-1 border-gray-400 dark:border-gray-300 rounded-lg p-3">
                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Standar yang diberi Toleransi
                        <span x-data
                            x-text="$wire.tolerancePercentage > 0 ? '(-' + $wire.tolerancePercentage + '%)' : ''"></span>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ number_format($standardInfo['adjusted_standard'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Summary Statistics Section -->
    @if (!empty($conclusionSummary))
    <div class="px-6 pb-6 bg-gray-50 dark:bg-gray-800 border-t-2 border-black dark:border-gray-600">
        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 mt-4">Ringkasan Kesimpulan</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            @foreach ($conclusionSummary as $conclusion => $count)
            @php
            $totalParticipants = array_sum($conclusionSummary);
            $percentage = $totalParticipants > 0 ? round(($count / $totalParticipants) * 100, 1) : 0;

            // Get range text from conclusionConfig
            $config = $this->conclusionConfig[$conclusion] ?? null;
            $rangeText = $config['rangeText'] ?? '-';

            // Determine colors based on conclusion (same as table labels)
            $c = trim(strtoupper($conclusion));
            if ($c === 'DI ATAS STANDAR') {
            $bgColor = 'bg-green-600 border-none';
            $textColor = 'text-white';
            } elseif ($c === 'MEMENUHI STANDAR') {
            $bgColor = 'bg-yellow-400 border-none';
            $textColor = 'text-gray-900';
            } elseif ($c === 'DI BAWAH STANDAR') {
            $bgColor = 'bg-red-600 border-none';
            $textColor = 'text-white';
            } else {
            $bgColor = 'bg-gray-100 border-gray-300';
            $textColor = 'text-gray-800';
            }
            @endphp

            <div class="border-2 {{ $bgColor }} rounded-lg p-4 text-center">
                <div class="text-3xl font-bold {{ $textColor }}">{{ $count }}</div>
                <div class="text-sm {{ $textColor }} mb-2">{{ $percentage }}%</div>
                <div class="text-sm {{ $textColor }} font-semibold leading-tight mb-2">
                    {{ $conclusion }}
                </div>
                {{-- <div class="text-xs {{ $textColor }} font-medium">{{ $rangeText }}</div> --}}
            </div>
            @endforeach
        </div>

        <!-- Overall Statistics -->
        @php
        $totalParticipants = array_sum($conclusionSummary);
        $passingCount =
        ($conclusionSummary['Di Atas Standar'] ?? 0) + ($conclusionSummary['Memenuhi Standar'] ?? 0);
        $passingPercentage = $totalParticipants > 0 ? round(($passingCount / $totalParticipants) * 100, 1) : 0;
        @endphp

        <div class="bg-white dark:bg-gray-800 border-2 border-black dark:border-gray-600 rounded-lg p-4">
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $totalParticipants }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-300">Total Peserta</div>
                </div>
                <div>
                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $passingCount }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-300">Lulus</div>
                </div>
                <div>
                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $passingPercentage }}%
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-300">Tingkat Kelulusan</div>
                </div>
            </div>
        </div>

        <!-- Keterangan Rentang Nilai -->
        <div class="mt-4 bg-gray-200 border dark:bg-gray-700 dark:border-gray-400 border-gray-600 rounded-lg p-3">
            <div class="text-sm text-black dark:text-white ">
                {{-- <strong>Keterangan:</strong> Kesimpulan berdasarkan Gap (Individual Score - Standard)
                <span x-data
                    x-text="$wire.tolerancePercentage > 0 ? 'dengan toleransi ' + $wire.tolerancePercentage + '%' : 'tanpa toleransi'"></span>.
                <br> --}}
                <ul class="list-disc ml-6 mt-1 space-y-2">
                    <li><strong class="bg-green-600 text-white px-2 py-0.5 rounded">Di Atas Standar</strong> : Skor
                        Individu ≥ Skor Standar</li>
                    <li><strong class="bg-yellow-400 text-gray-900 px-2 py-0.5 rounded">Memenuhi Standar</strong> : Skor
                        Individu ≥ Skor Standar yang telah diberi toleransi</li>
                    <li><strong class="bg-red-600 text-white px-2 py-0.5 rounded">Di Bawah Standar</strong> : Skor
                        Individu < Skor Standar maupun Skor Standar yang telah diberi toleransi</li>
                </ul>
            </div>
        </div>
    </div>
    @endif

    <!-- Pie Chart Section -->
    @if (!empty($conclusionSummary))
    <div class="mt-8 border-t-2 border-black dark:border-gray-600 pt-6 bg-white dark:bg-gray-900">
        <div class="px-6 pb-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6 text-center italic">Capacity
                Building
                General Competency
                Mapping</h3>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                <!-- Chart Section -->
                <div class="border border-gray-300 dark:border-gray-600 p-2 rounded-lg bg-gray-50 dark:bg-gray-800 transition-shadow duration-300 hover:shadow-xl"
                    x-data="{
                            refreshChart() {
                                const labels = @js($chartLabels);
                                const data = @js($chartData);
                                const colors = @js($chartColors);
                                if (labels.length > 0 && data.length > 0) {
                                    createConclusionChart(labels, data, colors);
                                }
                            }
                        }" x-init="$nextTick(() => refreshChart())">
                    <div wire:ignore>
                        <canvas id="conclusionPieChart" class="w-full h-full"></canvas>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="border-2 border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
                    <table class="w-full text-sm text-gray-900 dark:text-gray-100">
                        <thead>
                            <tr class="bg-gray-200 dark:bg-gray-700">
                                <th
                                    class="border-2 border-gray-400 dark:border-gray-500 px-4 py-3 text-center font-bold">
                                    KETERANGAN
                                </th>
                                <th
                                    class="border-2 border-gray-400 dark:border-gray-500 px-4 py-3 text-center font-bold">
                                    JUMLAH</th>
                                <th
                                    class="border-2 border-gray-400 dark:border-gray-500 px-4 py-3 text-center font-bold">
                                    PERSENTASE
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800">
                            @php
                            $totalParticipants = array_sum($conclusionSummary);
                            @endphp
                            @foreach ($conclusionSummary as $conclusion => $count)
                            @php
                            $percentage =
                            $totalParticipants > 0 ? round(($count / $totalParticipants) * 100, 2) : 0;

                            // Determine colors based on conclusion (same as table labels)
                            $c = trim(strtoupper($conclusion));
                            if ($c === 'DI ATAS STANDAR') {
                            $bgColor = 'bg-green-600 border-none';
                            $textColor = 'text-white';
                            } elseif ($c === 'MEMENUHI STANDAR') {
                            $bgColor = 'bg-yellow-400 border-none';
                            $textColor = 'text-gray-900';
                            } elseif ($c === 'DI BAWAH STANDAR') {
                            $bgColor = 'bg-red-600 border-none';
                            $textColor = 'text-white';
                            } else {
                            $bgColor = 'bg-gray-100';
                            $textColor = 'text-gray-800';
                            }
                            @endphp
                            <tr>
                                <td
                                    class="border-2 border-gray-400 dark:border-gray-500 px-4 py-3 {{ $bgColor }} {{ $textColor }} font-bold">
                                    {{ $conclusion }}</td>
                                <td class="border-2 border-gray-400 dark:border-gray-500 px-4 py-3 text-center">
                                    {{ $count }}
                                    orang
                                </td>
                                <td class="border-2 border-gray-400 dark:border-gray-500 px-4 py-3 text-center">
                                    {{ $percentage }}%
                                </td>
                            </tr>
                            @endforeach
                            <tr class="bg-gray-100 dark:bg-gray-700 font-semibold">
                                <td class="border-2 border-gray-400 dark:border-gray-500 px-4 py-3">Jumlah
                                    Responden</td>
                                <td class="border-2 border-gray-400 dark:border-gray-500 px-4 py-3 text-center">
                                    {{ $totalParticipants }} orang</td>
                                <td class="border-2 border-gray-400 dark:border-gray-500 px-4 py-3 text-center">
                                    100.00%</td>
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
                datalabels: {
                    display: false
                },
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
                    // datalabels: {
                    //     // Posisikan label di luar chart
                    //     anchor: 'end',
                    //     align: 'end',
                    //     offset: 10,

                    //     // Style untuk background label
                    //     backgroundColor: function(context) {
                    //         return 'rgba(255, 255, 255, 0.95)';
                    //     },
                    //     borderColor: function(context) {
                    //         return context.dataset.backgroundColor[context.dataIndex];
                    //     },
                    //     borderWidth: 2,
                    //     borderRadius: 4,

                    //     // Style text
                    //     color: '#000',
                    //     font: {
                    //         weight: 'bold',
                    //         size: 13
                    //     },

                    //     // Padding di dalam box label
                    //     padding: 6,

                    //     // Formatter untuk menampilkan nilai dan persentase
                    //     formatter: function(value, context) {
                    //         const total = context.dataset.data.reduce((a, b) => a + b, 0);
                    //         const percentage = total > 0 ? ((value / total) * 100).toFixed(2) : '0.00';

                    //         // Skip label jika value = 0 (kecuali index pertama)
                    //         if (value === 0 && context.dataIndex !== 0) {
                    //             return '';
                    //         }

                    //         return `${value} orang\n${percentage}%`;
                    //     },

                    //     // Tampilkan hanya jika value > 0 atau index pertama
                    //     display: function(context) {
                    //         return context.dataset.data[context.dataIndex] > 0 || context.dataIndex === 0;
                    //     },

                    //     // Style text alignment
                    //     textAlign: 'center',

                    //     // Tambahkan line connector dari chart ke label
                    //     listeners: {
                    //         enter: function(context) {
                    //             // Optional: bisa tambahkan interaktivitas
                    //         }
                    //     }
                    // }
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