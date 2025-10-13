<div class="max-w-3xl mx-auto mt-10 bg-white p-6 rounded shadow text-gray-900">

    <!-- Dropdown Event & Aspek -->
    <div class="flex items-center mb-4 gap-3">
        <label class="font-semibold">Event:</label>
        <select wire:model.live="eventCode"
            class="border border-black rounded px-2 py-1 bg-cyan-100 font-mono w-60 text-gray-900">
            @foreach ($availableEvents as $e)
                <option value="{{ $e['code'] }}">{{ $e['name'] }}</option>
            @endforeach
        </select>

        <label class="font-semibold ml-4">Aspek:</label>
        <select wire:model.live="aspectId"
            class="border border-black rounded px-2 py-1 bg-cyan-100 font-mono w-72 text-gray-900">
            @foreach ($availableAspects as $a)
                <option value="{{ $a['id'] }}">{{ strtoupper($a['category']) }} — {{ $a['name'] }}</option>
            @endforeach
        </select>

        <div wire:loading wire:target="eventCode, aspectId" class="text-center">
            <svg class="inline w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
        </div>
    </div>

    <!-- Judul Kurva -->
    <div class="mb-1 text-center font-bold text-lg uppercase">KURVA DISTRIBUSI FREKUENSI</div>
    <div class="mb-1 text-center text-sm font-semibold text-red-800">
        {{ collect($availableAspects)->firstWhere('id', (int) $aspectId)['name'] ?? '—' }}
    </div>

    <!-- Area Chart -->
    <div class="px-4 pb-1" wire:ignore id="distribution-chart">
        <canvas id="frekuensiChart" style="max-height: 270px;"></canvas>
    </div>

    <!-- Tabel Kelas dan Rentang Nilai -->
    <div class="flex justify-end mt-3 text-xs">
        <table class="border border-black text-gray-900">
            <thead>
                <tr style="background-color: #eee;">
                    <th class="border border-black px-2 py-1">Kelas</th>
                    <th class="border border-black px-2 py-1">Rentang Nilai</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="border border-black px-2 py-1">I</td>
                    <td class="border border-black px-2 py-1">1.00 - 1.80</td>
                </tr>
                <tr>
                    <td class="border border-black px-2 py-1">II</td>
                    <td class="border border-black px-2 py-1">1.80 - 2.60</td>
                </tr>
                <tr>
                    <td class="border border-black px-2 py-1">III</td>
                    <td class="border border-black px-2 py-1">2.60 - 3.40</td>
                </tr>
                <tr>
                    <td class="border border-black px-2 py-1">IV</td>
                    <td class="border border-black px-2 py-1">3.40 - 4.20</td>
                </tr>
                <tr>
                    <td class="border border-black px-2 py-1">V</td>
                    <td class="border border-black px-2 py-1">4.20 - 5.00</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Area Standar & Rata-rata Rating -->
    <div class="flex justify-center gap-4 mt-4">
        <div class="bg-cyan-200 p-4 rounded text-center min-w-[120px]">
            <div class="text-xs font-semibold">Standar Rating</div>
            <div class="text-2xl font-bold text-orange-900 mt-2">{{ number_format($standardRating, 2) }}</div>
        </div>
        <div class="bg-orange-200 p-4 rounded text-center min-w-[120px]">
            <div class="text-xs font-semibold">Rata-rata Rating</div>
            <div class="text-2xl font-bold text-cyan-900 mt-2">{{ number_format($averageRating, 2) }}</div>
        </div>
    </div>
</div>

<!-- Chart.js CDN & Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script>
    (function() {
        if (window.__statisticChartSetup) return;
        window.__statisticChartSetup = true;

        let chartInstance = null;

        function coerceNumbers(arr) {
            return (arr || []).map(v => Number(v) || 0);
        }

        function renderChart(labels, data, label, standardRating, averageRating) {
            const canvas = document.getElementById('frekuensiChart');
            if (!canvas) {
                return;
            }
            const ctx = canvas.getContext('2d');

            const coerced = coerceNumbers(data);
            const allZero = coerced.every(v => v === 0);

            // Calculate total for percentage
            const total = coerced.reduce((sum, val) => sum + val, 0);

            if (chartInstance) {
                chartInstance.destroy();
            }

            chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: label,
                        data: coerced,
                        borderColor: 'brown',
                        backgroundColor: 'rgba(200,0,0,0.08)',
                        tension: 0.5,
                        fill: false,
                        pointRadius: 5,
                        pointHoverRadius: 6,
                        pointBorderWidth: 2,
                        pointBackgroundColor: 'brown',
                        pointBorderColor: '#5a2a2a',
                        datalabels: {
                            align: 'top',
                            anchor: 'end',
                            offset: 4,
                            formatter: function(value) {
                                if (total === 0) return '0,00%';
                                const percentage = (value / total * 100).toFixed(2);
                                return percentage.replace('.', ',') + '%';
                            }
                        }
                    }]
                },
                options: {
                    responsive: true,
                    layout: {
                        padding: {
                            top: 32,
                            right: 16,
                            bottom: 16,
                            left: 8
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: true,
                            callbacks: {
                                label: function(context) {
                                    const value = context.parsed.y;
                                    const percentage = total === 0 ? '0,00' : (value / total * 100).toFixed(2).replace('.', ',');
                                    return `Jumlah: ${value} (${percentage}%)`;
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
                        x: {
                            border: {
                                display: false
                            },
                            offset: true,
                            grid: {
                                offset: true
                            }
                        },
                        y: {
                            min: 0,
                            suggestedMax: allZero ? 1 : undefined,
                            border: {
                                display: false
                            },
                            ticks: {
                                stepSize: 1,
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });
        }

        function initialRenderFromServer() {
            const initialLabels = ['I', 'II', 'III', 'IV', 'V'];
            const initialData = [
                {{ (int) ($distribution[1] ?? 0) }},
                {{ (int) ($distribution[2] ?? 0) }},
                {{ (int) ($distribution[3] ?? 0) }},
                {{ (int) ($distribution[4] ?? 0) }},
                {{ (int) ($distribution[5] ?? 0) }}
            ];
            const aspectName =
                `{{ collect($availableAspects)->firstWhere('id', (int) $aspectId)['name'] ?? '—' }}`;
            const standardRating = {{ $standardRating }};
            const averageRating = {{ $averageRating }};

            renderChart(initialLabels, initialData, aspectName, standardRating, averageRating);
        }

        function waitForLivewire(callback) {
            if (window.Livewire) {
                callback();
            } else {
                setTimeout(() => waitForLivewire(callback), 100);
            }
        }

        function onDistributionUpdated(eventData) {
            try {
                const payload = Array.isArray(eventData) && eventData.length > 0 ? eventData[0] : eventData;

                const labels = payload.labels || ['I', 'II', 'III', 'IV', 'V'];
                const data = Array.isArray(payload.data) ? payload.data : [];
                const label = payload.aspectName || '';
                const standardRating = payload.standardRating || 0;
                const averageRating = payload.averageRating || 0;

                renderChart(labels, data, label, standardRating, averageRating);
            } catch (e) {
                console.error('distribution-updated render error:', e, eventData);
            }
        }

        waitForLivewire(function() {
            initialRenderFromServer();
            Livewire.on('distribution-updated', onDistributionUpdated);
        });
    })();
</script>
