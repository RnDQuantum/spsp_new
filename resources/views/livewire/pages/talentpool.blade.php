<div class="max-w-6xl mx-auto p-6 bg-white rounded-lg shadow-md mt-10">
    <h1 class="text-center text-2xl font-bold text-gray-800 mb-2">9-Box Performance Matrix</h1>
    <div class="text-center text-gray-600 mb-8 text-sm">Matriks Kinerja dan Potensi Karyawan</div>

    <!-- Event and Position Selectors -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
        <div>
            <livewire:components.event-selector />
        </div>
        <div>
            <livewire:components.position-selector />
        </div>
    </div>

    <!-- Show message if no data -->
    @if (!$selectedEvent || !$selectedPositionId)
        <div class="text-center py-12 bg-gray-50 rounded-lg">
            <div class="text-gray-500 text-lg">Silakan pilih Event dan Position untuk melihat 9-Box Performance Matrix
            </div>
        </div>
    @elseif($totalParticipants === 0)
        <div class="text-center py-12 bg-gray-50 rounded-lg">
            <div class="text-gray-500 text-lg">Tidak ada data peserta untuk Event dan Position yang dipilih</div>
        </div>
    @else
        <div style="height:600px; margin-bottom:30px;">
            <canvas id="nineBoxChart"></canvas>
        </div>
    @endif

    <div>
        <h2 class="text-sm font-semibold mb-1">Keterangan</h2>
        <table class="border-collapse" style="width:auto;">
            <tbody>
                <tr>
                    <td class="py-0.5 pr-4 align-top">
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded-full mr-2" style="background:#00C853"></div>
                            <span class="text-xs text-gray-700">Box 9: Star Performer</span>
                        </div>
                        <div class="flex items-center mt-0.5">
                            <div class="w-4 h-4 rounded-full mr-2" style="background:#2196F3"></div>
                            <span class="text-xs text-gray-700">Box 7: Potential Star</span>
                        </div>
                        <div class="flex items-center mt-0.5">
                            <div class="w-4 h-4 rounded-full mr-2" style="background:#FFC107"></div>
                            <span class="text-xs text-gray-700">Box 5: Core Performer</span>
                        </div>
                        <div class="flex items-center mt-0.5">
                            <div class="w-4 h-4 rounded-full mr-2" style="background:#E91E63"></div>
                            <span class="text-xs text-gray-700">Box 3: Inconsistent</span>
                        </div>
                        <div class="flex items-center mt-0.5">
                            <div class="w-4 h-4 rounded-full mr-2" style="background:#D32F2F"></div>
                            <span class="text-xs text-gray-700">Box 1: Need Attention</span>
                        </div>
                    </td>

                    <td class="py-0.5 pl-2 align-top">
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded-full mr-2" style="background:#00BCD4"></div>
                            <span class="text-xs text-gray-700">Box 8: High Potential</span>
                        </div>
                        <div class="flex items-center mt-0.5">
                            <div class="w-4 h-4 rounded-full mr-2" style="background:#FF5722"></div>
                            <span class="text-xs text-gray-700">Box 6: Enigma</span>
                        </div>
                        <div class="flex items-center mt-0.5">
                            <div class="w-4 h-4 rounded-full mr-2" style="background:#9C27B0"></div>
                            <span class="text-xs text-gray-700">Box 4: Solid Performer</span>
                        </div>
                        <div class="flex items-center mt-0.5">
                            <div class="w-4 h-4 rounded-full mr-2" style="background:#FF9800"></div>
                            <span class="text-xs text-gray-700">Box 2: Steady Performer</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>


    <hr class="mt-6 mb-4 border-t border-2 border-gray-400">


    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
        <div>
            <h2 class="text-sm text-center font-semibold mb-2">Distribusi Peserta per Box</h2>
            <div style="height:280px;">
                <canvas id="boxPieChart"></canvas>
            </div>
        </div>

        <div>
            <table class="text-xs text-gray-700 border-2 border-gray-300">
                <thead>
                    <tr>
                        <th class="text-center py-1 border-2 border-gray-300">Box</th>
                        <th class="text-center py-1 border-2 border-gray-300">Kategori</th>
                        <th class="text-center py-1 border-2 border-gray-300">Jumlah</th>
                        <th class="text-center py-1 border-2 border-gray-300">%</th>
                    </tr>
                </thead>
                <tbody id="boxSummaryBody">
                    <!-- Diisi via JS -->
                </tbody>
            </table>
        </div>
    </div>


</div>

<script>
    (function() {
        // Tunggu Chart.js tersedia
        function initChart() {
            if (typeof Chart === 'undefined') {
                console.error('Chart.js belum dimuat!');
                return;
            }

            const canvas = document.getElementById('nineBoxChart');
            if (!canvas) {
                console.error('Canvas tidak ditemukan!');
                return;
            }

            // Get data from Livewire component
            const pesertaData = @json($chartData);
            const boxBoundaries = @json($boxBoundaries);
            const boxStatistics = @json($boxStatistics);

            if (pesertaData.length === 0) {
                console.log('No participant data available');
                return;
            }

            // Transform data for Chart.js (swap x/y for correct axes)
            const chartData = pesertaData.map(p => {
                return {
                    x: p.potensi, // Horizontal axis = POTENSI
                    y: p.kinerja, // Vertical axis = KINERJA
                    nama: p.nama,
                    box: p.box,
                    color: p.color
                };
            });

            const totalPeserta = chartData.length;


            const ctx = canvas.getContext('2d');

            new Chart(ctx, {
                type: 'scatter',
                data: {
                    datasets: [{
                        label: '',
                        data: chartData,
                        backgroundColor: chartData.map(d => d.color),
                        borderColor: chartData.map(d => d.color),
                        borderWidth: 2,
                        pointRadius: 10,
                        pointHoverRadius: 15,
                        showLine: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: true,
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                title: function(ctx) {
                                    return ctx[0].raw.nama;
                                },
                                label: function(ctx) {
                                    const d = ctx.raw;
                                    return [
                                        'Kinerja: ' + d.y.toFixed(1),
                                        'Potensi: ' + d.x.toFixed(1),
                                        'Box: ' + d.box
                                    ];
                                }
                            }
                        },
                        datalabels: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'POTENSI',
                                font: {
                                    size: 16,
                                    weight: 'bold'
                                }
                            },
                            min: 0,
                            max: 10,
                            ticks: {
                                stepSize: 1
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'KINERJA',
                                font: {
                                    size: 16,
                                    weight: 'bold'
                                }
                            },
                            min: 0,
                            max: 10,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                },
                plugins: [{
                    id: 'nineBoxGrid',
                    beforeDraw: function(chart) {
                        const ctx = chart.ctx;
                        const xScale = chart.scales.x;
                        const yScale = chart.scales.y;
                        ctx.save();

                        // Draw box backgrounds first
                        // Draw box backgrounds first - use dynamic boundaries
                        const potensiLower = boxBoundaries?.potensi?.lower_bound ?? 5.5;
                        const potensiUpper = boxBoundaries?.potensi?.upper_bound ?? 7.5;
                        const kinerjaLower = boxBoundaries?.kinerja?.lower_bound ?? 5.5;
                        const kinerjaUpper = boxBoundaries?.kinerja?.upper_bound ?? 7.5;

                        const boxColors = [{
                                x1: 0,
                                x2: potensiLower,
                                y1: 0,
                                y2: kinerjaLower,
                                color: 'rgba(211,47,47,0.08)' // Box 1
                            },
                            {
                                x1: 0,
                                x2: potensiLower,
                                y1: kinerjaLower,
                                y2: kinerjaUpper,
                                color: 'rgba(255,152,0,0.08)' // Box 2
                            },
                            {
                                x1: potensiLower,
                                x2: potensiUpper,
                                y1: 0,
                                y2: kinerjaLower,
                                color: 'rgba(233,30,99,0.08)' // Box 3
                            },
                            {
                                x1: 0,
                                x2: potensiLower,
                                y1: kinerjaUpper,
                                y2: 10,
                                color: 'rgba(156,39,176,0.08)' // Box 4
                            },
                            {
                                x1: potensiLower,
                                x2: potensiUpper,
                                y1: kinerjaLower,
                                y2: kinerjaUpper,
                                color: 'rgba(255,193,7,0.08)' // Box 5
                            },
                            {
                                x1: potensiUpper,
                                x2: 10,
                                y1: 0,
                                y2: kinerjaLower,
                                color: 'rgba(255,87,34,0.08)' // Box 6
                            },
                            {
                                x1: potensiLower,
                                x2: potensiUpper,
                                y1: kinerjaUpper,
                                y2: 10,
                                color: 'rgba(33,150,243,0.08)' // Box 7
                            },
                            {
                                x1: potensiUpper,
                                x2: 10,
                                y1: kinerjaLower,
                                y2: kinerjaUpper,
                                color: 'rgba(0,188,212,0.08)' // Box 8
                            },
                            {
                                x1: potensiUpper,
                                x2: 10,
                                y1: kinerjaUpper,
                                y2: 10,
                                color: 'rgba(0,200,83,0.08)' // Box 9
                            }
                        ];

                        boxColors.forEach(function(box) {
                            ctx.fillStyle = box.color;
                            ctx.fillRect(
                                xScale.getPixelForValue(box.x1),
                                yScale.getPixelForValue(box.y2),
                                xScale.getPixelForValue(box.x2) - xScale
                                .getPixelForValue(box.x1),
                                yScale.getPixelForValue(box.y1) - yScale
                                .getPixelForValue(box.y2)
                            );
                        });

                        // Draw grid lines - use dynamic boundaries
                        const potensiLower = boxBoundaries?.potensi?.lower_bound ?? 5.5;
                        const potensiUpper = boxBoundaries?.potensi?.upper_bound ?? 7.5;
                        const kinerjaLower = boxBoundaries?.kinerja?.lower_bound ?? 5.5;
                        const kinerjaUpper = boxBoundaries?.kinerja?.upper_bound ?? 7.5;

                        [potensiLower, potensiUpper].forEach(function(v) {
                            const x = xScale.getPixelForValue(v);
                            ctx.beginPath();
                            ctx.moveTo(x, yScale.getPixelForValue(10));
                            ctx.lineTo(x, yScale.getPixelForValue(0));
                            ctx.lineWidth = 3;
                            ctx.strokeStyle = '#333';
                            ctx.stroke();
                        });

                        [kinerjaLower, kinerjaUpper].forEach(function(v) {
                            const y = yScale.getPixelForValue(v);
                            ctx.beginPath();
                            ctx.moveTo(xScale.getPixelForValue(0), y);
                            ctx.lineTo(xScale.getPixelForValue(10), y);
                            ctx.stroke();
                        });

                        // Draw box numbers - use dynamic boundaries
                        ctx.font = 'bold 48px Arial';
                        ctx.fillStyle = 'rgba(0,0,0,0.15)';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';

                        const boxes = [{
                                num: '1',
                                x: potensiLower / 2,
                                y: kinerjaLower / 2
                            },
                            {
                                num: '2',
                                x: potensiLower / 2,
                                y: (kinerjaLower + kinerjaUpper) / 2
                            },
                            {
                                num: '3',
                                x: (potensiLower + potensiUpper) / 2,
                                y: kinerjaLower / 2
                            },
                            {
                                num: '4',
                                x: potensiLower / 2,
                                y: (kinerjaUpper + 10) / 2
                            },
                            {
                                num: '5',
                                x: (potensiLower + potensiUpper) / 2,
                                y: (kinerjaLower + kinerjaUpper) / 2
                            },
                            {
                                num: '6',
                                x: (potensiUpper + 10) / 2,
                                y: kinerjaLower / 2
                            },
                            {
                                num: '7',
                                x: (potensiLower + potensiUpper) / 2,
                                y: (kinerjaUpper + 10) / 2
                            },
                            {
                                num: '8',
                                x: (potensiUpper + 10) / 2,
                                y: (kinerjaLower + kinerjaUpper) / 2
                            },
                            {
                                num: '9',
                                x: (potensiUpper + 10) / 2,
                                y: (kinerjaUpper + 10) / 2
                            }
                        ];

                        boxes.forEach(function(box) {
                            ctx.fillText(box.num, xScale.getPixelForValue(box.x), yScale
                                .getPixelForValue(box.y));
                        });

                        ctx.restore();
                    }
                }]
            });

            console.log('9-Box Chart berhasil dimuat!');

            // --- PIE CHART ---
            const pieCanvas = document.getElementById('boxPieChart');
            if (pieCanvas) {
                const pieCtx = pieCanvas.getContext('2d');

                // Use boxStatistics from service instead of calculating from chartData
                const boxLabels = Object.keys(boxStatistics)
                    .sort((a, b) => b - a) // urut 9 ke 1
                    .map(box => 'Box ' + box);
                const pieData = Object.keys(boxStatistics)
                    .sort((a, b) => b - a)
                    .map(box => boxStatistics[box].count);
                const pieColors = Object.keys(boxStatistics)
                    .sort((a, b) => b - a)
                    .map(box => {
                        const colorMap = {
                            1: '#D32F2F',
                            2: '#FF9800',
                            3: '#E91E63',
                            4: '#9C27B0',
                            5: '#FFC107',
                            6: '#FF5722',
                            7: '#2196F3',
                            8: '#00BCD4',
                            9: '#00C853'
                        };
                        return colorMap[box] || '#9E9E9E';
                    });

                new Chart(pieCtx, {
                    type: 'pie',
                    data: {
                        labels: pieLabels,
                        datasets: [{
                            data: pieData,
                            backgroundColor: pieColors,
                            borderWidth: 1,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        const label = ctx.label || '';
                                        const value = ctx.raw || 0;
                                        const percent = boxStatistics[ctx.label.replace('Box ', '')]
                                            ?.percentage || 0;
                                        return label + ': ' + value + ' orang (' + percent + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            const summaryBody = document.getElementById('boxSummaryBody');
            if (summaryBody) {
                summaryBody.innerHTML = '';

                // Use boxStatistics from service
                const boxLabelsMap = {
                    1: 'Need Attention',
                    2: 'Steady Performer',
                    3: 'Inconsistent',
                    4: 'Solid Performer',
                    5: 'Core Performer',
                    6: 'Enigma',
                    7: 'Potential Star',
                    8: 'High Potential',
                    9: 'Star Performer'
                };

                Object.keys(boxStatistics)
                    .sort((a, b) => b - a) // 9 ke 1
                    .forEach(box => {
                        const tr = document.createElement('tr');

                        const tdBox = document.createElement('td');
                        tdBox.className = 'px-5 py-1 border-2 border-gray-300';
                        tdBox.textContent = 'Box ' + box;

                        const tdLabel = document.createElement('td');
                        tdLabel.className = 'px-5 py-1 border-2 border-gray-300';
                        tdLabel.textContent = boxLabelsMap[box] || 'Unknown';

                        const tdCount = document.createElement('td');
                        tdCount.className = 'text-center px-5 py-1 border-2 border-gray-300';
                        tdCount.textContent = boxStatistics[box].count;

                        const tdPercent = document.createElement('td');
                        tdPercent.className = 'text-center px-5 py-1 border-2 border-gray-300';
                        tdPercent.textContent = boxStatistics[box].percentage + '%';

                        tr.appendChild(tdBox);
                        tr.appendChild(tdLabel);
                        tr.appendChild(tdCount);
                        tr.appendChild(tdPercent);
                        summaryBody.appendChild(tr);
                    });
            }
        }

        // Coba jalankan setelah DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initChart);
        } else {
            // DOM sudah ready, coba delay sedikit untuk Chart.js
            setTimeout(initChart, 100);
        }
    })();
</script>
