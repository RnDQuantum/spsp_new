<div class="max-w-6xl mx-auto p-6 bg-white rounded-lg shadow-md mt-10">
    <h1 class="text-center text-2xl font-bold text-gray-800 mb-2">9-Box Performance Matrix</h1>
    <div class="text-center text-gray-600 mb-8 text-sm">Matriks Kinerja dan Potensi Karyawan</div>

    <div style="height:600px; margin-bottom:30px;">
        <canvas id="nineBoxChart"></canvas>
    </div>

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

            const pesertaData = [{
                    nama: "Ahmad Rizki",
                    kinerja: 8.5,
                    potensi: 8.0
                },
                {
                    nama: "Dewi Lestari",
                    kinerja: 9.0,
                    potensi: 9.5
                },
                {
                    nama: "Tini Kartini",
                    kinerja: 3.8,
                    potensi: 4.0
                },
                {
                    nama: "Budi Santoso",
                    kinerja: 7.8,
                    potensi: 6.5
                },
                {
                    nama: "Siti Nurhaliza",
                    kinerja: 6.5,
                    potensi: 8.2
                },
                {
                    nama: "Andi Wijaya",
                    kinerja: 5.0,
                    potensi: 5.0
                },
                {
                    nama: "Rina Kusuma",
                    kinerja: 8.8,
                    potensi: 7.2
                },
                {
                    nama: "Doni Prasetyo",
                    kinerja: 4.5,
                    potensi: 6.8
                },
                {
                    nama: "Maya Sari",
                    kinerja: 6.8,
                    potensi: 5.8
                },
                {
                    nama: "Eko Saputra",
                    kinerja: 9.2,
                    potensi: 8.8
                }
            ];

            function getBoxInfo(kinerja, potensi) {
                const kinerjaLevel = kinerja >= 7.5 ? 'atas' : kinerja >= 5.5 ? 'sesuai' : 'bawah';
                const potensiLevel = potensi >= 7.5 ? 'tinggi' : potensi >= 5.5 ? 'menengah' : 'rendah';

                if (kinerjaLevel === 'atas' && potensiLevel === 'tinggi') return {
                    box: 9,
                    color: '#00C853'
                };
                if (kinerjaLevel === 'atas' && potensiLevel === 'menengah') return {
                    box: 7,
                    color: '#2196F3'
                };
                if (kinerjaLevel === 'atas' && potensiLevel === 'rendah') return {
                    box: 4,
                    color: '#9C27B0'
                };
                if (kinerjaLevel === 'sesuai' && potensiLevel === 'tinggi') return {
                    box: 8,
                    color: '#00BCD4'
                };
                if (kinerjaLevel === 'sesuai' && potensiLevel === 'menengah') return {
                    box: 5,
                    color: '#FFC107'
                };
                if (kinerjaLevel === 'sesuai' && potensiLevel === 'rendah') return {
                    box: 2,
                    color: '#FF9800'
                };
                if (kinerjaLevel === 'bawah' && potensiLevel === 'tinggi') return {
                    box: 6,
                    color: '#FF5722'
                };
                if (kinerjaLevel === 'bawah' && potensiLevel === 'menengah') return {
                    box: 3,
                    color: '#E91E63'
                };
                return {
                    box: 1,
                    color: '#D32F2F'
                };
            }

            const chartData = pesertaData.map(p => {
                const info = getBoxInfo(p.kinerja, p.potensi);
                return {
                    x: p.potensi,
                    y: p.kinerja,
                    nama: p.nama,
                    box: info.box,
                    color: info.color
                };
            });

            // Hitung jumlah peserta per box
            const boxMeta = {
                9: {
                    label: 'Star Performer',
                    color: '#00C853'
                },
                8: {
                    label: 'High Potential',
                    color: '#00BCD4'
                },
                7: {
                    label: 'Potential Star',
                    color: '#2196F3'
                },
                6: {
                    label: 'Enigma',
                    color: '#FF5722'
                },
                5: {
                    label: 'Core Performer',
                    color: '#FFC107'
                },
                4: {
                    label: 'Solid Performer',
                    color: '#9C27B0'
                },
                3: {
                    label: 'Inconsistent',
                    color: '#E91E63'
                },
                2: {
                    label: 'Steady Performer',
                    color: '#FF9800'
                },
                1: {
                    label: 'Need Attention',
                    color: '#D32F2F'
                },
            };

            const boxCounts = {};
            chartData.forEach(d => {
                boxCounts[d.box] = (boxCounts[d.box] || 0) + 1;
            });

            const totalPeserta = chartData.length;

            const boxPercent = {};
            Object.keys(boxCounts).forEach(box => {
                boxPercent[box] = (boxCounts[box] / totalPeserta) * 100;
            });


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
                        const boxColors = [{
                                x1: 0,
                                x2: 5.5,
                                y1: 0,
                                y2: 5.5,
                                color: 'rgba(211,47,47,0.08)'
                            },
                            {
                                x1: 0,
                                x2: 5.5,
                                y1: 5.5,
                                y2: 7.5,
                                color: 'rgba(255,152,0,0.08)'
                            },
                            {
                                x1: 5.5,
                                x2: 7.5,
                                y1: 0,
                                y2: 5.5,
                                color: 'rgba(233,30,99,0.08)'
                            },
                            {
                                x1: 0,
                                x2: 5.5,
                                y1: 7.5,
                                y2: 10,
                                color: 'rgba(156,39,176,0.08)'
                            },
                            {
                                x1: 5.5,
                                x2: 7.5,
                                y1: 5.5,
                                y2: 7.5,
                                color: 'rgba(255,193,7,0.08)'
                            },
                            {
                                x1: 7.5,
                                x2: 10,
                                y1: 0,
                                y2: 5.5,
                                color: 'rgba(255,87,34,0.08)'
                            },
                            {
                                x1: 5.5,
                                x2: 7.5,
                                y1: 7.5,
                                y2: 10,
                                color: 'rgba(33,150,243,0.08)'
                            },
                            {
                                x1: 7.5,
                                x2: 10,
                                y1: 5.5,
                                y2: 7.5,
                                color: 'rgba(0,188,212,0.08)'
                            },
                            {
                                x1: 7.5,
                                x2: 10,
                                y1: 7.5,
                                y2: 10,
                                color: 'rgba(0,200,83,0.08)'
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

                        // Draw grid lines
                        [5.5, 7.5].forEach(function(v) {
                            const x = xScale.getPixelForValue(v);
                            ctx.beginPath();
                            ctx.moveTo(x, yScale.getPixelForValue(10));
                            ctx.lineTo(x, yScale.getPixelForValue(0));
                            ctx.lineWidth = 3;
                            ctx.strokeStyle = '#333';
                            ctx.stroke();

                            const y = yScale.getPixelForValue(v);
                            ctx.beginPath();
                            ctx.moveTo(xScale.getPixelForValue(0), y);
                            ctx.lineTo(xScale.getPixelForValue(10), y);
                            ctx.stroke();
                        });

                        // Draw box numbers
                        ctx.font = 'bold 48px Arial';
                        ctx.fillStyle = 'rgba(0,0,0,0.15)';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';

                        const boxes = [{
                                num: '1',
                                x: 2.75,
                                y: 2.75
                            },
                            {
                                num: '2',
                                x: 2.75,
                                y: 6.5
                            },
                            {
                                num: '3',
                                x: 6.5,
                                y: 2.75
                            },
                            {
                                num: '4',
                                x: 2.75,
                                y: 8.75
                            },
                            {
                                num: '5',
                                x: 6.5,
                                y: 6.5
                            },
                            {
                                num: '6',
                                x: 8.75,
                                y: 2.75
                            },
                            {
                                num: '7',
                                x: 6.5,
                                y: 8.75
                            },
                            {
                                num: '8',
                                x: 8.75,
                                y: 6.5
                            },
                            {
                                num: '9',
                                x: 8.75,
                                y: 8.75
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

                const pieLabels = Object.keys(boxCounts)
                    .sort((a, b) => b - a) // urut 9 ke 1
                    .map(box => 'Box ' + box);
                const pieData = Object.keys(boxCounts)
                    .sort((a, b) => b - a)
                    .map(box => boxCounts[box]);
                const pieColors = Object.keys(boxCounts)
                    .sort((a, b) => b - a)
                    .map(box => boxMeta[box].color);

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
                                        const percent = (value / totalPeserta * 100).toFixed(1);
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

                Object.keys(boxCounts)
                    .sort((a, b) => b - a) // 9 ke 1
                    .forEach(box => {
                        const tr = document.createElement('tr');

                        const tdBox = document.createElement('td');
                        tdBox.className = 'px-5 py-1 border-2 border-gray-300';
                        tdBox.textContent = 'Box ' + box;

                        const tdLabel = document.createElement('td');
                        tdLabel.className = 'px-5 py-1 border-2 border-gray-300';
                        tdLabel.textContent = boxMeta[box].label;

                        const tdCount = document.createElement('td');
                        tdCount.className = 'text-center px-5 py-1 border-2 border-gray-300';
                        tdCount.textContent = boxCounts[box];

                        const tdPercent = document.createElement('td');
                        tdPercent.className = 'text-center px-5 py-1 border-2 border-gray-300';
                        tdPercent.textContent = boxPercent[box].toFixed(1) + '%';

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
