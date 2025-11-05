<div class="max-w-6xl mx-auto mt-10 bg-white dark:bg-gray-900 p-8 rounded shadow text-gray-900 dark:text-gray-100">

    <!-- Dropdown Event, Position & Aspek -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Event Filter -->
        <div>
            @livewire('components.event-selector', ['showLabel' => true])
        </div>

        <!-- Position Filter -->
        <div>
            @livewire('components.position-selector', ['showLabel' => true])
        </div>

        <!-- Aspect Filter -->
        <div>
            @livewire('components.aspect-selector', ['showLabel' => true])
        </div>
    </div>

    <!-- Judul Kurva -->
    <div class="mb-2 text-center font-bold text-2xl uppercase text-gray-900 dark:text-gray-100">KURVA DISTRIBUSI
        FREKUENSI</div>
    <div class="mb-4 text-center text-lg font-semibold text-red-800 dark:text-red-400">
        {{ $aspectName ?: '—' }}
    </div>

    <!-- Chart dan Tabel Layout -->
    <div class="flex gap-6">
        <!-- Area Chart -->
        <div class="flex-1 px-6 pb-2" wire:ignore id="distribution-chart-{{ $chartId }}">
            <canvas id="frekuensiChart-{{ $chartId }}" style="max-height: 400px;"></canvas>
        </div>

        <!-- Tabel Kelas dan Rentang Nilai - di sebelah kanan chart -->
        <div class="flex-shrink-0 text-sm self-center">
            <table class="border border-black dark:border-gray-600 text-gray-900 dark:text-gray-100">
                <thead>
                    <tr class="dark:bg-gray-700">
                        <th class="border border-black dark:border-gray-600 px-3 py-2 font-semibold">Kelas</th>
                        <th class="border border-black dark:border-gray-600 px-3 py-2 font-semibold">Rentang Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-white dark:bg-gray-800">
                        <td class="border border-black dark:border-gray-600 px-3 py-2 text-center">I (Kurang Baik)</td>
                        <td class="border border-black dark:border-gray-600 px-3 py-2">1.00 - 1.80</td>
                    </tr>
                    <tr class="bg-white dark:bg-gray-800">
                        <td class="border border-black dark:border-gray-600 px-3 py-2 text-center">II (Cukup Baik)</td>
                        <td class="border border-black dark:border-gray-600 px-3 py-2">1.80 - 2.60</td>
                    </tr>
                    <tr class="bg-white dark:bg-gray-800">
                        <td class="border border-black dark:border-gray-600 px-3 py-2 text-center">III (Baik)</td>
                        <td class="border border-black dark:border-gray-600 px-3 py-2">2.60 - 3.40</td>
                    </tr>
                    <tr class="bg-white dark:bg-gray-800">
                        <td class="border border-black dark:border-gray-600 px-3 py-2 text-center">IV (Sangat Baik)</td>
                        <td class="border border-black dark:border-gray-600 px-3 py-2">3.40 - 4.20</td>
                    </tr>
                    <tr class="bg-white dark:bg-gray-800">
                        <td class="border border-black dark:border-gray-600 px-3 py-2 text-center">V (Baik Sekali)</td>
                        <td class="border border-black dark:border-gray-600 px-3 py-2">4.20 - 5.00</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Area Standar & Rata-rata Rating - tepat di bawah chart -->
    <div class="flex justify-center gap-6 mt-6 px-6">
        <div class="bg-cyan-200 dark:bg-cyan-700 p-6 rounded-lg text-center min-w-[160px]">
            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Standar Rating</div>
            <div class="text-3xl font-bold text-orange-900 dark:text-orange-400 mt-3">
                {{ number_format($standardRating, 2) }}</div>
        </div>
        <div class="bg-orange-200 dark:bg-orange-700 p-6 rounded-lg text-center min-w-[160px]">
            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Rata-rata Rating</div>
            <div class="text-3xl font-bold text-cyan-900 dark:text-cyan-400 mt-3">{{ number_format($averageRating, 2) }}
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        let chartInstances = {};

        function coerceNumbers(arr) {
            return (arr || []).map(v => Number(v) || 0);
        }

        function renderChart(chartId, labels, data, label, standardRating, averageRating) {
            const canvas = document.getElementById(`frekuensiChart-${chartId}`);
            if (!canvas) {
                return;
            }
            const ctx = canvas.getContext('2d');

            const coerced = coerceNumbers(data);
            const allZero = coerced.every(v => v === 0);

            // Calculate total for percentage
            const total = coerced.reduce((sum, val) => sum + val, 0);

            if (!chartInstances[chartId]) {
                chartInstances[chartId] = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: label,
                            data: coerced,
                            borderColor: 'rgb(185, 28, 28)',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            tension: 0.4,
                            fill: true,
                            pointRadius: 6,
                            pointHoverRadius: 8,
                            pointBorderWidth: 3,
                            pointBackgroundColor: 'rgb(185, 28, 28)',
                            pointBorderColor: '#fff',
                            borderWidth: 3,
                            datalabels: {
                                align: 'top',
                                anchor: 'end',
                                offset: 6,
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
                                top: 60,
                                right: 20,
                                bottom: 20,
                                left: 12
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
                                        const percentage = total === 0 ? '0,00' : (value / total * 100)
                                            .toFixed(2).replace('.', ',');
                                        return `Jumlah: ${value} (${percentage}%)`;
                                    }
                                }
                            },
                            datalabels: {
                                backgroundColor: 'rgba(185, 28, 28, 0.9)',
                                borderRadius: 5,
                                color: 'white',
                                font: {
                                    weight: 'bold',
                                    size: 13
                                },
                                padding: 8
                            }
                        },
                        scales: {
                            x: {
                                border: {
                                    display: true,
                                    width: 2
                                },
                                offset: true,
                                grid: {
                                    offset: true,
                                    display: true,
                                    color: 'rgba(0, 0, 0, 0.05)'
                                },
                                ticks: {
                                    font: {
                                        size: 14,
                                        weight: 'bold'
                                    }
                                }
                            },
                            y: {
                                min: 0,
                                suggestedMax: allZero ? 1 : undefined,
                                border: {
                                    display: true,
                                    width: 2
                                },
                                grid: {
                                    display: true,
                                    color: 'rgba(0, 0, 0, 0.05)'
                                },
                                ticks: {
                                    stepSize: 1,
                                    font: {
                                        size: 13
                                    },
                                    callback: function(value) {
                                        return value;
                                    }
                                }
                            }
                        }
                    },
                    plugins: [ChartDataLabels]
                });
            } else {
                // Update in-place like GeneralPsyMapping
                const chart = chartInstances[chartId];
                chart.data.labels = labels;
                chart.data.datasets[0].label = label;
                chart.data.datasets[0].data = coerced;
                chart.update('active');
                return;
            }
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
            const aspectName = `{{ $aspectName }}`;
            const standardRating = {{ $standardRating }};
            const averageRating = {{ $averageRating }};

            renderChart(`{{ $chartId }}`, initialLabels, initialData, aspectName, standardRating,
                averageRating);
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

                const chartId = payload.chartId;
                if (!chartId || chartId !== `{{ $chartId }}`) {
                    return; // ignore events for other instances
                }

                const labels = payload.labels || ['I', 'II', 'III', 'IV', 'V'];
                const data = Array.isArray(payload.data) ? payload.data : [];
                const label = payload.aspectName || '';
                const standardRating = payload.standardRating || 0;
                const averageRating = payload.averageRating || 0;

                renderChart(chartId, labels, data, label, standardRating, averageRating);
            } catch (e) {
                console.error('distribution-updated render error:', e, eventData);
            }
        }

        // Initialize chart on page load
        waitForLivewire(function() {
            initialRenderFromServer();
            Livewire.on('chartDataUpdated', onDistributionUpdated);
        });

        // Handle Livewire navigate events
        document.addEventListener('livewire:navigated', function() {
            // Re-render chart after navigation
            waitForLivewire(function() {
                initialRenderFromServer();
            });
        });

        // Cleanup chart on navigate away
        document.addEventListener('livewire:navigating', function() {
            const chartId = `{{ $chartId }}`;
            if (chartInstances[chartId]) {
                chartInstances[chartId].destroy();
                delete chartInstances[chartId];
            }
        });
    })();
</script>
