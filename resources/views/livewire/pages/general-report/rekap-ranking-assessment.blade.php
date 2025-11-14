<div class="bg-white dark:bg-gray-900 mx-auto my-8 shadow overflow-hidden" style="max-width: 1300px;">
    <!-- Header Section -->
    <div class="border-b-4 border-black dark:border-gray-600 py-4 bg-white dark:bg-gray-800">
        <h1 class="text-center text-2xl font-bold uppercase tracking-wide text-black dark:text-white">
            REKAP PERINGKAT SKOR PENILAIAN AKHIR ASESMEN
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

        <!-- Category Weight Editor -->
        @if ($this->selectedTemplate)
        <div class="flex justify-center items-center gap-4 mt-3 px-6">
            <div class="w-full max-w-md">
                @livewire('components.category-weight-editor', [
                'templateId' => $this->selectedTemplate->id,
                'categoryCode1' => 'potensi',
                'categoryCode2' => 'kompetensi',
                ])
            </div>
        </div>
        @endif
    </div>
    <!-- Toleransi Section -->
    @php $summary = $this->getPassingSummary(); @endphp
    @livewire('components.tolerance-selector', [
    'passing' => $summary['passing'],
    'total' => $summary['total'],
    'showSummary' => false,
    ])

    <!-- Enhanced Table Section -->
    <div class="px-6 pb-6 bg-white dark:bg-gray-900 overflow-x-auto">
        <!-- Per Page Selector -->
        <div class="flex justify-between items-center mb-4 mt-6">
            <div class="flex items-center gap-3">
                <label for="perPage" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Tampilkan:
                </label>
                <select wire:model.live="perPage" id="perPage"
                    class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="all">Semua</option>
                </select>
                <span class="text-sm text-gray-600 dark:text-gray-400">Data</span>
            </div>

            @if (isset($rows) && $rows)
            <div class="text-sm text-gray-600 dark:text-gray-400">
                Menampilkan
                <span class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ $rows->firstItem() ?? 0 }}
                </span>
                -
                <span class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ $rows->lastItem() ?? 0 }}
                </span>
                dari
                <span class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ $rows->total() }}
                </span>
                data
            </div>
            @endif
        </div>

        <table class="min-w-full border-2 border-black dark:border-gray-600 text-sm text-gray-900 dark:text-gray-100">
            <thead>
                <tr class="bg-gray-300 dark:bg-gray-600">
                    <th class="border-2 border-black dark:border-gray-500 px-2 py-2 text-center align-middle"
                        rowspan="2">NO</th>
                    <th class="border-2 border-black dark:border-gray-500 px-2 py-2 text-center align-middle"
                        rowspan="2">NAMA</th>
                    <th class="border-2 border-black dark:border-gray-500 px-2 py-2 text-center" colspan="2">SKOR
                        INDIVIDU</th>
                    <th class="border-2 border-black dark:border-gray-500 px-2 py-2 text-center align-middle"
                        rowspan="2">TOTAL SKOR
                    </th>
                    <th class="border-2 border-black dark:border-gray-500 px-2 py-2 text-center" colspan="2">SKOR
                        PENILAIAN AKHIR</th>
                    <th class="border-2 border-black dark:border-gray-500 px-2 py-2 text-center align-middle"
                        rowspan="2">TOTAL SKOR
                    </th>
                    <th class="border-2 border-black dark:border-gray-500 px-2 py-2 text-center align-middle"
                        rowspan="2">GAP</th>
                    <th class="border-2 border-black dark:border-gray-500 px-2 py-2 text-center align-middle"
                        rowspan="2">KESIMPULAN
                    </th>
                </tr>
                <tr class="bg-gray-300 dark:bg-gray-600">
                    <th class="border-2 border-black dark:border-gray-500 px-2 py-2 text-center">PSYCHOLOGY</th>
                    <th class="border-2 border-black dark:border-gray-500 px-2 py-2 text-center">KOMPETENSI MANAGERIAL
                    </th>
                    <th class="border-2 border-black dark:border-gray-500 px-2 py-2 text-center">PSYCHOLOGY
                        {{ $potensiWeight }}%</th>
                    <th class="border-2 border-black dark:border-gray-500 px-2 py-2 text-center">KOMPETENSI MANAGERIAL
                        {{ $kompetensiWeight }}%</th>
                </tr>
            </thead>
            <tbody>
                @if (isset($rows) && $rows)
                @foreach ($rows as $row)
                <tr class="bg-white dark:bg-gray-800">
                    <td class="border-2 border-black dark:border-gray-600 px-2 py-2 text-center">
                        {{ $row['rank'] }}</td>
                    <td class="border-2 border-black dark:border-gray-600 px-2 py-2">{{ $row['name'] }}</td>
                    <td class="border-2 border-black dark:border-gray-600 px-2 py-2 text-center">
                        {{ number_format($row['psy_individual'], 2) }}</td>
                    <td class="border-2 border-black dark:border-gray-600 px-2 py-2 text-center">
                        {{ number_format($row['mc_individual'], 2) }}</td>
                    <td class="border-2 border-black dark:border-gray-600 px-2 py-2 text-center">
                        {{ number_format($row['total_individual'], 2) }}</td>
                    <td class="border-2 border-black dark:border-gray-600 px-2 py-2 text-center">
                        {{ number_format($row['psy_weighted'], 2) }}</td>
                    <td class="border-2 border-black dark:border-gray-600 px-2 py-2 text-center">
                        {{ number_format($row['mc_weighted'], 2) }}</td>
                    <td class="border-2 border-black dark:border-gray-600 px-2 py-2 text-center">
                        {{ number_format($row['total_weighted_individual'], 2) }}</td>
                    <td class="border-2 border-black dark:border-gray-600 px-2 py-2 text-center">
                        {{ number_format($row['gap'], 2) }}</td>
                    <td
                        class="border-2 border-black dark:border-gray-600 px-2 py-2 text-center font-bold {{ $conclusionConfig[$row['conclusion']]['tailwindClass'] ?? 'bg-gray-600 text-white' }}">
                        {{ $row['conclusion'] }}
                    </td>
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
    <div class="px-6 pb-6 bg-white dark:bg-gray-900">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-3">
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-900 dark:text-gray-100" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                    </path>
                </svg>
                Informasi Standar
                <span x-data
                    x-text="$wire.tolerancePercentage > 0 ? '(Toleransi -' + $wire.tolerancePercentage + '%)' : '(Tanpa Toleransi)'"
                    class="text-sm font-normal text-gray-900 dark:text-gray-100"></span>
            </h3>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left Column: STANDAR -->
                <div class="space-y-3">
                    <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-3">
                        Standar (Tanpa Toleransi)
                    </h4>

                    <!-- Psychology Standard -->
                    <div class="bg-white dark:bg-gray-800 border-1 border-gray-400 dark:border-gray-300 rounded-lg p-3">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Psychology</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ number_format($standardInfo['psy_original_standard'], 2) }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                            Bobot {{ $potensiWeight }}% = <span
                                class="font-semibold text-gray-900 dark:text-gray-100">{{
                                number_format(($standardInfo['psy_original_standard'] * $potensiWeight) / 100, 2)
                                }}</span>
                        </div>
                    </div>

                    <!-- Kompetensi Standard -->
                    <div class="bg-white dark:bg-gray-800 border-1 border-gray-400 dark:border-gray-300 rounded-lg p-3">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Kompetensi</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ number_format($standardInfo['mc_original_standard'], 2) }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                            Bobot {{ $kompetensiWeight }}% = <span
                                class="font-semibold text-gray-900 dark:text-gray-100">{{
                                number_format(($standardInfo['mc_original_standard'] * $kompetensiWeight) / 100, 2)
                                }}</span>
                        </div>
                    </div>

                    <!-- Total Standard -->
                    <div class="bg-white dark:bg-gray-800 border-1 border-gray-400 dark:border-gray-300 rounded-lg p-3">
                        <div class="text-xs text-gray-600 dark:text-gray-300 mb-1 font-semibold">TOTAL STANDAR
                        </div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {{ number_format($standardInfo['total_original_standard'], 2) }}
                        </div>
                    </div>
                </div>

                <!-- Right Column: STANDAR ADJUSTED -->
                <div class="space-y-3">
                    <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-3">
                        Standar Adjusted
                        <span x-data
                            x-text="$wire.tolerancePercentage > 0 ? '(Toleransi -' + $wire.tolerancePercentage + '%)' : ''"
                            class="text-gray-900 dark:text-gray-100"></span>
                    </h4>

                    <!-- Psychology Adjusted -->
                    <div class="bg-white dark:bg-gray-800 border-1 border-gray-400 dark:border-gray-300 rounded-lg p-3">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Psychology</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ number_format($standardInfo['psy_adjusted_standard'], 2) }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                            Bobot {{ $potensiWeight }}% = <span
                                class="font-semibold text-gray-900 dark:text-gray-100">{{
                                number_format($standardInfo['psy_standard'], 2) }}</span>
                        </div>
                    </div>

                    <!-- Kompetensi Adjusted -->
                    <div class="bg-white dark:bg-gray-800 border-1 border-gray-400 dark:border-gray-300 rounded-lg p-3">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Kompetensi</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ number_format($standardInfo['mc_adjusted_standard'], 2) }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                            Bobot {{ $kompetensiWeight }}% = <span
                                class="font-semibold text-gray-900 dark:text-gray-100">{{
                                number_format($standardInfo['mc_standard'], 2) }}</span>
                        </div>
                    </div>

                    <!-- Total Adjusted -->
                    <div class="bg-white dark:bg-gray-800 border-1 border-gray-400 dark:border-gray-300 rounded-lg p-3">
                        <div class="text-xs text-gray-600 dark:text-gray-300 mb-1 font-semibold">TOTAL STANDAR
                            ADJUSTED</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {{ number_format($standardInfo['total_standard'], 2) }}
                        </div>
                    </div>
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
            $tailwindClass = $conclusionConfig[$conclusion]['tailwindClass'] ?? 'bg-gray-100 border-gray-300
            text-gray-800';
            @endphp

            <div class="border-2 border-none rounded-lg p-4 text-center {{ $tailwindClass }}">
                <div class="text-3xl font-bold">{{ $count }}</div>
                <div class="text-sm mb-2">{{ $percentage }}%</div>
                <div class="text-sm font-semibold leading-tight mb-2">
                    {{ $conclusion }}
                </div>
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
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Peserta</div>
                </div>
                <div>
                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $passingCount }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Lulus</div>
                </div>
                <div>
                    <div class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $passingPercentage }}%
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Tingkat Kelulusan</div>
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
            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6 text-center">Capacity Building Rekap
                Assessment</h3>

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
                    class="border border-gray-300 dark:border-gray-600 p-2 rounded-lg bg-gray-50 dark:bg-gray-800 transition-shadow duration-300 hover:shadow-xl">
                    <div wire:ignore>
                        <canvas id="conclusionPieChart" class="w-full h-full"></canvas>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="rounded-md overflow-hidden">
                    <table class="w-full text-sm text-gray-900 dark:text-gray-100">
                        <thead>
                            <tr class="bg-gray-200 dark:bg-gray-700">
                                <th
                                    class="border-2 border-gray-400 dark:border-gray-500 px-4 py-3 text-center font-bold">
                                    KETERANGAN
                                </th>
                                <th
                                    class="border-2 border-gray-400 dark:border-gray-500 px-4 py-3 text-center font-bold">
                                    JUMLAH
                                </th>
                                <th
                                    class="border-2 border-gray-400 dark:border-gray-500 px-4 py-3 text-center font-bold">
                                    PERSENTASE
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800">
                            @foreach ($conclusionSummary as $conclusion => $count)
                            @php
                            $percentage = $totalParticipants > 0 ? round(($count / $totalParticipants) * 100, 2) : 0;
                            $tailwindClass = $conclusionConfig[$conclusion]['tailwindClass'] ?? 'bg-gray-100
                            text-gray-800';
                            @endphp
                            <tr>
                                <td
                                    class="border-2 border-gray-400 dark:border-gray-500 px-4 py-3 font-bold {{ $tailwindClass }}">
                                    {{ $conclusion }}
                                </td>
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