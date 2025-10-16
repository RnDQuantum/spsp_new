<x-layouts.app title="Capacity Building General Competency Mapping">
    <div class="max-w-7xl mx-auto mt-10 bg-white p-8 rounded-lg shadow-lg">
        <!-- Title Section -->
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Capacity Building General Competency Mapping</h1>
            <h2 class="text-xl font-semibold text-gray-800">Standar JPT Pratama</h2>
        </div>

        <!-- Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
            <!-- Chart Section -->
            <div class="border border-gray-300 p-6 rounded-lg bg-gray-50">
                <canvas id="capacityChart" class="w-full" style="max-height: 400px;"></canvas>
            </div>

            <!-- Table Section -->
            <div class="border border-gray-300 rounded-lg overflow-hidden">
                <table class="w-full text-sm text-gray-900">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="border border-gray-400 px-4 py-3 text-center font-bold">KETERANGAN</th>
                            <th class="border border-gray-400 px-4 py-3 text-center font-bold">JUMLAH</th>
                            <th class="border border-gray-400 px-4 py-3 text-center font-bold">PROSENTASE</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        <tr>
                            <td class="border border-gray-400 px-4 py-3">Sangat Kompeten</td>
                            <td class="border border-gray-400 px-4 py-3 text-center">0 orang</td>
                            <td class="border border-gray-400 px-4 py-3 text-center">0.00%</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-400 px-4 py-3">Kompeten</td>
                            <td class="border border-gray-400 px-4 py-3 text-center">1 orang</td>
                            <td class="border border-gray-400 px-4 py-3 text-center">20.00%</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-400 px-4 py-3">Belum Kompeten</td>
                            <td class="border border-gray-400 px-4 py-3 text-center">4 orang</td>
                            <td class="border border-gray-400 px-4 py-3 text-center">80.00%</td>
                        </tr>
                        <tr class="bg-gray-100 font-semibold">
                            <td class="border border-gray-400 px-4 py-3">Jumlah Responden</td>
                            <td class="border border-gray-400 px-4 py-3 text-center">5 orang</td>
                            <td class="border border-gray-400 px-4 py-3 text-center">100.00%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Chart.js Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('capacityChart').getContext('2d');

            const data = {
                labels: ['Sangat Kompeten', 'Kompeten', 'Belum Kompeten'],
                datasets: [{
                    data: [0, 1, 4],
                    backgroundColor: [
                        '#10b981', // Green for Sangat Kompeten
                        '#fbbf24', // Yellow for Kompeten
                        '#ef4444' // Red for Belum Kompeten
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            };

            const config = {
                type: 'pie',
                data: data,
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
                                        const value = data.datasets[0].data[i];
                                        const total = data.datasets[0].data.reduce((a, b) => a + b,
                                            0);
                                        const percentage = total > 0 ? ((value / total) * 100)
                                            .toFixed(2) : 0;

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
                        },
                        datalabels: {
                            display: true,
                            color: '#000',
                            font: {
                                weight: 'bold',
                                size: 14
                            },
                            formatter: function(value, context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(2) : 0;
                                return value > 0 ? `${value} orang; ${percentage}%` :
                                    `${value} orang; ${percentage}%`;
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
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(
                                2) : '0.00';

                            const position = element.tooltipPosition();
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

            new Chart(ctx, config);
        });
    </script>
</x-layouts.app>
