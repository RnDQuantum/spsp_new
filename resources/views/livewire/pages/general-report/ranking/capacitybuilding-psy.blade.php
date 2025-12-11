<x-layouts.app title="Capacity Building Psychology Mapping">
    <div class="max-w-7xl mx-auto mt-10 bg-white p-8 rounded-lg shadow-lg">
        <!-- Title Section -->
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Capacity Building Psychology Mapping</h1>
            <h2 class="text-xl font-semibold text-gray-800">Standar JPT Pratama</h2>
        </div>

        <!-- Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
            <!-- Chart Section -->
            <div class="border border-gray-300 p-6 rounded-lg bg-gray-50">
                <canvas id="psychologyChart" class="w-full" style="max-height: 400px;"></canvas>
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
                            <td class="border border-gray-400 px-4 py-3">Di Atas Standar</td>
                            <td class="border border-gray-400 px-4 py-3 text-center">3 orang</td>
                            <td class="border border-gray-400 px-4 py-3 text-center">60.00%</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-400 px-4 py-3">Memenuhi Standar</td>
                            <td class="border border-gray-400 px-4 py-3 text-center">1 orang</td>
                            <td class="border border-gray-400 px-4 py-3 text-center">20.00%</td>
                        </tr>
                        <tr>
                            <td class="border border-gray-400 px-4 py-3">Di Bawah Standar</td>
                            <td class="border border-gray-400 px-4 py-3 text-center">1 orang</td>
                            <td class="border border-gray-400 px-4 py-3 text-center">20.00%</td>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('psychologyChart').getContext('2d');

            const data = {
                labels: ['Di Atas Standar', 'Memenuhi Standar', 'Di Bawah Standar'],
                datasets: [{
                    data: [3, 1, 1],
                    backgroundColor: [
                        '#10b981', // Green for Di Atas Standar
                        '#fbbf24', // Yellow for Memenuhi Standar
                        '#ef4444' // Red for Di Bawah Standar
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
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(
                                2) : '0.00';

                            const label = `${value} orang; ${percentage}%`;

                            ctx.fillStyle = '#000';
                            ctx.font = 'bold 11px Arial';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';

                            // Position label outside the slice
                            const angle = (element.startAngle + element.endAngle) / 2;
                            const radius = element.outerRadius + 40;
                            const x = element.x + Math.cos(angle) * radius;
                            const y = element.y + Math.sin(angle) * radius;

                            ctx.fillText(label, x, y);
                        });
                    }
                }]
            };

            new Chart(ctx, config);
        });
    </script>
</x-layouts.app>
