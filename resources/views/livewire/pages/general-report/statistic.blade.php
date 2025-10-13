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
<script>
    (function() {
        if (window.__statisticChartSetup) return;
        window.__statisticChartSetup = true;

        let chartInstance = null;

        function coerceNumbers(arr) {
            return (arr || []).map(v => Number(v) || 0);
        }

        function renderChart(labels, data, label) {
            const canvas = document.getElementById('frekuensiChart');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');

            const coerced = coerceNumbers(data);
            const allZero = coerced.every(v => v === 0);

            if (chartInstance) chartInstance.destroy();
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
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: true
                        }
                    },
                    scales: {
                        x: {
                            border: {
                                display: false
                            },
                        },
                        y: {
                            min: 0,
                            suggestedMax: allZero ? 1 : undefined,
                            border: {
                                display: false
                            },
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
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
            renderChart(initialLabels, initialData, aspectName);
        }

        function waitForLivewire(callback) {
            if (window.Livewire) callback();
            else setTimeout(() => waitForLivewire(callback), 100);
        }

        function onDistributionUpdated(payload) {
            try {
                const labels = payload.labels || ['I', 'II', 'III', 'IV', 'V'];
                const data = Array.isArray(payload.data) ? payload.data : [];
                const label = payload.aspectName || '';
                renderChart(labels, data, label);
            } catch (e) {
                console.error('distribution-updated render error', e, payload);
            }
        }

        waitForLivewire(function() {
            initialRenderFromServer();
            Livewire.on('distribution-updated', onDistributionUpdated);
        });

        window.addEventListener('distribution-updated', function(e) {
            const payload = e.detail || {};
            onDistributionUpdated(payload);
        });
    })();
</script>
