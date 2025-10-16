<div class="bg-white mx-auto my-8 shadow overflow-hidden" style="max-width: 1200px;">
    <!-- Header Section -->
    <div class="border-b-4 border-black py-4 bg-white">
        <h1 class="text-center text-2xl font-bold uppercase tracking-wide text-black">
            RANKING SKOR GENERAL COMPETENCY MAPPING
        </h1>
        <div class="flex justify-center items-center gap-4 mt-3">
            <label for="eventCode" class="text-black font-semibold">Event</label>
            <select id="eventCode" wire:model.live="eventCode" class="border border-black px-2 py-1 text-black">
                @foreach ($availableEvents as $ev)
                    <option value="{{ $ev['code'] }}">{{ $ev['name'] }}</option>
                @endforeach
            </select>
            <a href="{{ route('cb-mc') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-1 border border-black rounded transition-colors inline-block">
                Lihat Capacity Building
            </a>
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
            </tbody>
        </table>
        @if ($rankings && $rankings->hasPages())
            <div class="mt-4">
                {{ $rankings->links(data: ['scrollTo' => false]) }}
            </div>
        @endif
    </div>

    <!-- Summary Statistics Section -->
    @if (!empty($conclusionSummary))
        <div class="px-6 pb-6 bg-gray-50 border-t-2 border-black">
            <h3 class="text-lg font-bold text-gray-900 mb-4 mt-4">Ringkasan Kesimpulan</h3>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                @foreach ($conclusionSummary as $conclusion => $count)
                    @php
                        $totalParticipants = array_sum($conclusionSummary);
                        $percentage = $totalParticipants > 0 ? round(($count / $totalParticipants) * 100, 1) : 0;

                        // Determine color based on conclusion
                        $bgColor = match ($conclusion) {
                            'Lebih Memenuhi/More Requirement' => 'bg-green-100 border-green-300',
                            'Memenuhi/Meet Requirement' => 'bg-blue-100 border-blue-300',
                            'Kurang Memenuhi/Below Requirement' => 'bg-yellow-100 border-yellow-300',
                            'Belum Memenuhi/Under Perform' => 'bg-red-100 border-red-300',
                            default => 'bg-gray-100 border-gray-300',
                        };
                    @endphp

                    <div class="border-2 {{ $bgColor }} rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-gray-900">{{ $count }}</div>
                        <div class="text-sm text-gray-600 mb-1">{{ $percentage }}%</div>
                        <div class="text-xs text-gray-700 leading-tight mb-2">{{ $conclusion }}</div>
                        <div class="text-xs text-gray-500 font-medium">
                            @switch($conclusion)
                                @case('Lebih Memenuhi/More Requirement')
                                    ≥ 110%
                                @break

                                @case('Memenuhi/Meet Requirement')
                                    100% - 109%
                                @break

                                @case('Kurang Memenuhi/Below Requirement')
                                    90% - 99%
                                @break

                                @case('Belum Memenuhi/Under Perform')
                        < 90% @break @default - @endswitch </div>
                    </div>
            @endforeach
        </div>

        <!-- Overall Statistics -->
        @php
            $totalParticipants = array_sum($conclusionSummary);
            $passingCount =
                ($conclusionSummary['Lebih Memenuhi/More Requirement'] ?? 0) +
                ($conclusionSummary['Memenuhi/Meet Requirement'] ?? 0);
            $passingPercentage = $totalParticipants > 0 ? round(($passingCount / $totalParticipants) * 100, 1) : 0;
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
                <strong>Keterangan:</strong> Rentang nilai di atas berdasarkan persentase skor individu terhadap
                standar yang disesuaikan dengan toleransi
                <span x-data
                    x-text="$wire.tolerancePercentage > 0 ? '(' + $wire.tolerancePercentage + '%)' : '(0%)'"></span>.
                <br>
                <strong>Rumus:</strong> Persentase = (Skor Individu ÷ Standar Adjusted) × 100%
            </div>
        </div>
    </div>
@endif

<!-- Pie Chart Section -->
@if (!empty($conclusionSummary))
    <div class="mt-8 border-t-2 border-black pt-6 bg-white">
        <div class="px-6 pb-6">
            <h3 class="text-xl font-bold text-gray-900 mb-6 text-center">Capacity Building General Competency
                Mapping</h3>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                <!-- Chart Section -->
                <div class="border border-gray-300 p-6 rounded-lg bg-gray-50" wire:ignore>
                    <canvas id="conclusionPieChart" class="w-full" style="max-height: 400px;"></canvas>
                </div>

                <!-- Table Section -->
                <div class="border-2 border-gray-300 rounded-lg overflow-hidden">
                    <table class="w-full text-sm text-gray-900">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="border-2 border-gray-400 px-4 py-3 text-center font-bold">KETERANGAN</th>
                                <th class="border-2 border-gray-400 px-4 py-3 text-center font-bold">JUMLAH</th>
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
                                        $totalParticipants > 0 ? round(($count / $totalParticipants) * 100, 2) : 0;
                                @endphp
                                <tr>
                                    <td class="border-2 border-gray-400 px-4 py-3">{{ $conclusion }}</td>
                                    <td class="border-2 border-gray-400 px-4 py-3 text-center">{{ $count }}
                                        orang
                                    </td>
                                    <td class="border-2 border-gray-400 px-4 py-3 text-center">{{ $percentage }}%
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
                borderWidth: 2
            }]
        };

        const config = {
            type: 'pie',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'right',
                        labels: {
                            font: {
                                size: 12
                            },
                            padding: 15,
                            generateLabels: function(chart) {
                                const data = chart.data;
                                return data.labels.map((label, i) => {
                                    return {
                                        text: label,
                                        fillStyle: data.datasets[0].backgroundColor[i],
                                        hidden: false,
                                        index: i
                                    };
                                });
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(2) : 0;
                                return `${label}: ${value} orang (${percentage}%)`;
                            }
                        }
                    }
                }
            },
            plugins: [{
                id: 'customLabels',
                afterDatasetsDraw: function(chart) {
                    const ctx = chart.ctx;
                    const dataset = chart.data.datasets[0];
                    const meta = chart.getDatasetMeta(0);

                    meta.data.forEach((element, index) => {
                        const value = dataset.data[index];
                        const total = dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = total > 0 ? ((value / total) * 100).toFixed(2) :
                            '0.00';

                        const label = `${value} orang; ${percentage}%`;

                        ctx.fillStyle = '#000';
                        ctx.font = 'bold 12px Arial';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';

                        // Position label outside the slice
                        const angle = (element.startAngle + element.endAngle) / 2;
                        const radius = element.outerRadius + 30;
                        const x = element.x + Math.cos(angle) * radius;
                        const y = element.y + Math.sin(angle) * radius;

                        if (value > 0 || index === 0) {
                            ctx.fillText(label, x, y);
                        }
                    });
                }
            }]
        };

        conclusionPieChart = new Chart(canvas, config);
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initial data from Livewire
        const chartLabels = @js($chartLabels);
        const chartData = @js($chartData);
        const chartColors = @js($chartColors);

        // Create initial chart
        createConclusionChart(chartLabels, chartData, chartColors);

        // Listen for Livewire events to recreate chart
        Livewire.on('pieChartDataUpdated', function(data) {
            let chartData = Array.isArray(data) && data.length > 0 ? data[0] : data;

            if (chartData) {
                // Recreate chart completely to avoid cropping issues
                createConclusionChart(chartData.labels, chartData.data, chartData.colors);
            }
        });
    });
</script>
