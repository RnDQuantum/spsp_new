<x-layouts.app title="Dashboard Overview">
    @if (session('force_reload'))
        <script>
            window.location.reload();
        </script>
    @endif

    <h1 class="text-3xl text-center font-bold text-gray-900 dark:text-gray-100">Static Pribadi Spider Plot (SPSP)</h1>
    <p class="text-center text-gray-700 dark:text-gray-300 mt-2">Selamat Datang!</p>

    <!-- Tolerance Toggle -->
    <div class="flex justify-center items-center gap-4 mt-6 max-w-4xl mx-auto">
        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Toleransi:</label>
        <div class="flex gap-2">
            <button onclick="setTolerance(0)" id="tolerance-0"
                class="px-4 py-2 rounded-lg bg-blue-500 text-white font-semibold hover:bg-blue-600 transition">
                0%
            </button>
            <button onclick="setTolerance(5)" id="tolerance-5"
                class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                5%
            </button>
            <button onclick="setTolerance(10)" id="tolerance-10"
                class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                10%
            </button>
        </div>
    </div>

    <!-- Charts - Susunan Vertikal -->
    <div class="flex flex-col gap-6 mt-6 max-w-4xl mx-auto">
        <!-- Chart Segi 5 (Pentagon) -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-lg text-center font-semibold text-gray-900 dark:text-gray-100 mb-4">Potential Mapping
                (Rating)</h3>
            <div class="relative h-96">
                <canvas id="pentagonChart"></canvas>
            </div>
        </div>

        <!-- Chart Segi 9 (Nonagon) -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-lg text-center font-semibold text-gray-900 dark:text-gray-100 mb-4">Managerial Potency
                Mapping (Rating)</h3>
            <div class="relative h-96">
                <canvas id="nonagonChart"></canvas>
            </div>
        </div>

        <!-- Chart Segi 13 (Tetradecagon) -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h3 class="text-lg text-center font-semibold text-gray-900 dark:text-gray-100 mb-4">General Mapping (Rating)
            </h3>
            <div class="relative h-96">
                <canvas id="tetradecagonChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        let pentagonChart, nonagonChart, tetradecagonChart;
        let currentTolerance = 0;

        // Data asli (standar)
        const originalData = {
            pentagon: [4.2, 3.8, 4.5, 3.2, 4.0],
            nonagon: [4.3, 3.9, 4.7, 3.5, 4.2, 3.8, 4.5, 3.6, 4.1],
            tetradecagon: [4.5, 3.2, 4.8, 3.9, 4.1, 3.5, 4.3, 3.8, 4.6, 3.7, 4.2, 3.4, 4.0, 4.0]
        };

        // Function untuk calculate tolerance data
        function calculateToleranceData(data, tolerancePercent) {
            return data.map(value => {
                const tolerance = value * (tolerancePercent / 100);
                return Math.max(0, value - tolerance); // Tidak boleh negatif
            });
        }

        // Function untuk set tolerance
        function setTolerance(percent) {
            currentTolerance = percent;

            // Update button styles
            ['0', '5', '10'].forEach(p => {
                const btn = document.getElementById(`tolerance-${p}`);
                if (p === percent.toString()) {
                    btn.className =
                        'px-4 py-2 rounded-lg bg-blue-500 text-white font-semibold hover:bg-blue-600 transition';
                } else {
                    btn.className =
                        'px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-300 dark:hover:bg-gray-600 transition';
                }
            });

            // Update charts
            updateChartsWithTolerance();
        }

        // Function untuk update charts dengan tolerance
        // Function untuk update charts dengan tolerance
        function updateChartsWithTolerance() {
            if (currentTolerance === 0) {
                // Hanya dataset standar (kuning SOLID #faea05)
                pentagonChart.data.datasets = [{
                    label: 'Rating',
                    data: originalData.pentagon,
                    fill: true,
                    backgroundColor: '#faea05', // KUNING SOLID
                    borderColor: '#e6d105',
                    borderWidth: 3,
                    pointRadius: 0,
                    pointHoverRadius: 0
                }];

                nonagonChart.data.datasets = [{
                    label: 'Rating',
                    data: originalData.nonagon,
                    fill: true,
                    backgroundColor: '#faea05', // KUNING SOLID
                    borderColor: '#e6d105',
                    borderWidth: 3,
                    pointRadius: 0,
                    pointHoverRadius: 0
                }];

                tetradecagonChart.data.datasets = [{
                    label: 'Rating',
                    data: originalData.tetradecagon,
                    fill: true,
                    backgroundColor: '#faea05', // KUNING SOLID
                    borderColor: '#e6d105',
                    borderWidth: 3,
                    pointRadius: 0,
                    pointHoverRadius: 0
                }];
            } else {
                // Rating kuning SOLID + Toleransi merah solid (layered)
                const toleranceDataPentagon = calculateToleranceData(originalData.pentagon, currentTolerance);
                const toleranceDataNonagon = calculateToleranceData(originalData.nonagon, currentTolerance);
                const toleranceDataTetradecagon = calculateToleranceData(originalData.tetradecagon, currentTolerance);

                // **PENTAGON**: Rating kuning solid di atas, toleransi merah di bawah
                pentagonChart.data.datasets = [{
                        label: `Toleransi ${currentTolerance}%`,
                        data: toleranceDataPentagon,
                        fill: true,
                        backgroundColor: '#b50505', // MERAH SOLID (layer bawah)
                        borderColor: '#9a0404',
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 0
                    },
                    {
                        label: 'Rating',
                        data: originalData.pentagon,
                        fill: true,
                        backgroundColor: '#faea05', // KUNING SOLID (layer atas)
                        borderColor: '#e6d105',
                        borderWidth: 4, // Border tebal untuk highlight
                        pointRadius: 0,
                        pointHoverRadius: 0
                    }
                ];

                // **NONAGON**: Rating kuning solid di atas, toleransi merah di bawah
                nonagonChart.data.datasets = [{
                        label: `Toleransi ${currentTolerance}%`,
                        data: toleranceDataNonagon,
                        fill: true,
                        backgroundColor: '#b50505', // MERAH SOLID (layer bawah)
                        borderColor: '#9a0404',
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 0
                    },
                    {
                        label: 'Rating',
                        data: originalData.nonagon,
                        fill: true,
                        backgroundColor: '#faea05', // KUNING SOLID (layer atas)
                        borderColor: '#e6d105',
                        borderWidth: 4,
                        pointRadius: 0,
                        pointHoverRadius: 0
                    }
                ];

                // **TETRADECAGON**: Rating kuning solid di atas, toleransi merah di bawah
                tetradecagonChart.data.datasets = [{
                        label: `Toleransi ${currentTolerance}%`,
                        data: toleranceDataTetradecagon,
                        fill: true,
                        backgroundColor: '#b50505', // MERAH SOLID (layer bawah)
                        borderColor: '#9a0404',
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHoverRadius: 0
                    },
                    {
                        label: 'Rating',
                        data: originalData.tetradecagon,
                        fill: true,
                        backgroundColor: '#faea05', // KUNING SOLID (layer atas)
                        borderColor: '#e6d105',
                        borderWidth: 4,
                        pointRadius: 0,
                        pointHoverRadius: 0
                    }
                ];
            }

            pentagonChart.update();
            nonagonChart.update();
            tetradecagonChart.update();
        }
        // Function untuk update warna chart berdasarkan dark mode
        function updateChartsColor() {
            const isDarkMode = document.documentElement.classList.contains('dark');

            const textColor = isDarkMode ? '#f3f4f6' : '#000000';
            const gridColor = isDarkMode ? '#6b7280' : '#4b5563';
            const backdropColor = isDarkMode ? 'rgba(31, 41, 55, 0.8)' : 'rgba(255, 255, 255, 0.9)';

            [pentagonChart, nonagonChart, tetradecagonChart].forEach(chart => {
                if (chart) {
                    chart.options.scales.r.ticks.color = textColor;
                    chart.options.scales.r.ticks.backdropColor = backdropColor;
                    chart.options.scales.r.pointLabels.color = textColor;
                    chart.options.scales.r.grid.color = gridColor;
                    chart.options.scales.r.angleLines.color = gridColor;
                    chart.options.plugins.legend.labels.color = textColor;
                    chart.update();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const isDarkMode = document.documentElement.classList.contains('dark');
            const textColor = isDarkMode ? '#f3f4f6' : '#000000';
            const gridColor = isDarkMode ? '#6b7280' : '#4b5563';
            const backdropColor = isDarkMode ? 'rgba(31, 41, 55, 0.8)' : 'rgba(255, 255, 255, 0.9)';

            // Chart Segi 5 (Pentagon)
            const ctxPentagon = document.getElementById('pentagonChart').getContext('2d');
            pentagonChart = new Chart(ctxPentagon, {
                type: 'radar',
                data: {
                    labels: ['Kecerdasan', 'Cara Kerja', 'Potensi Kerja', 'Hubungan Sosial', 'Kepribadian'],
                    datasets: [{
                        label: 'Rating',
                        data: originalData.pentagon,
                        fill: true,
                        backgroundColor: '#faea05', // KUNING SOLID
                        borderColor: '#e6d105',
                        borderWidth: 3,
                        pointRadius: 0,
                        pointHoverRadius: 0
                    }]
                },
                options: {
                    scales: {
                        r: {
                            beginAtZero: true,
                            min: 0,
                            max: 5,
                            ticks: {
                                stepSize: 1,
                                color: textColor,
                                backdropColor: backdropColor,
                                font: {
                                    size: 14,
                                    weight: 'bold',
                                    family: 'Arial'
                                }
                            },
                            pointLabels: {
                                color: textColor,
                                font: {
                                    size: 15,
                                    weight: 'bold',
                                    family: 'Arial'
                                }
                            },
                            grid: {
                                color: gridColor,
                                lineWidth: 2
                            },
                            angleLines: {
                                color: gridColor,
                                lineWidth: 2
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: false
                        },
                        datalabels: {
                            display: false
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Chart Segi 9 (Nonagon)
            const ctxNonagon = document.getElementById('nonagonChart').getContext('2d');
            nonagonChart = new Chart(ctxNonagon, {
                type: 'radar',
                data: {
                    labels: [
                        'Integritas', 'Kerjasama', 'Komunikasi', 'Oriantasi Pada Hasil',
                        'Pelayanan Publik',
                        'Pengembangan Diri dan Orang Lain', 'Mengelola Perubahan',
                        'Pengambilan Keputusan', 'Perekat Bangsa'
                    ],
                    datasets: [{
                        label: 'Rating',
                        data: originalData.nonagon,
                        fill: true,
                        backgroundColor: '#faea05', // KUNING SOLID
                        borderColor: '#e6d105',
                        borderWidth: 3,
                        pointRadius: 0,
                        pointHoverRadius: 0
                    }]
                },
                options: {
                    scales: {
                        r: {
                            beginAtZero: true,
                            min: 0,
                            max: 5,
                            ticks: {
                                stepSize: 1,
                                color: textColor,
                                backdropColor: backdropColor,
                                font: {
                                    size: 14,
                                    weight: 'bold',
                                    family: 'Arial'
                                }
                            },
                            pointLabels: {
                                color: textColor,
                                font: {
                                    size: 15,
                                    weight: 'bold',
                                    family: 'Arial'
                                }
                            },
                            grid: {
                                color: gridColor,
                                lineWidth: 2
                            },
                            angleLines: {
                                color: gridColor,
                                lineWidth: 2
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: false
                        },
                        datalabels: {
                            display: false
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Chart Segi 13 (tetradecagon)
            const ctxtetradecagon = document.getElementById('tetradecagonChart').getContext('2d');
            tetradecagonChart = new Chart(ctxtetradecagon, {
                type: 'radar',
                data: {
                    labels: [
                        'Kecerdasan', 'Cara Kerja', 'Potensi Kerja', 'Hubungan Sosial', 'Kepribadian',
                        'Integritas', 'Kerjasama', 'Komunikasi', 'Orientasi Pada Hasil',
                        'Pelayanan Publik',
                        'Pengembangan Diri dan Orang Lain', 'Mengelola Perubahan',
                        'Pengambilan Keputusan', 'Perekat Bangsa'
                    ],
                    datasets: [{
                        label: 'Rating',
                        data: originalData.tetradecagon,
                        fill: true,
                        backgroundColor: '#faea05', // KUNING SOLID
                        borderColor: '#e6d105',
                        borderWidth: 3,
                        pointRadius: 0,
                        pointHoverRadius: 0
                    }]
                },
                options: {
                    scales: {
                        r: {
                            beginAtZero: true,
                            min: 0,
                            max: 5,
                            ticks: {
                                stepSize: 1,
                                color: textColor,
                                backdropColor: backdropColor,
                                font: {
                                    size: 14,
                                    weight: 'bold',
                                    family: 'Arial'
                                }
                            },
                            pointLabels: {
                                color: textColor,
                                font: {
                                    size: 15,
                                    weight: 'bold',
                                    family: 'Arial'
                                }
                            },
                            grid: {
                                color: gridColor,
                                lineWidth: 2
                            },
                            angleLines: {
                                color: gridColor,
                                lineWidth: 2
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: false
                        },
                        datalabels: {
                            display: false
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Observer untuk mendeteksi perubahan class 'dark' pada HTML element
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.attributeName === 'class') {
                        updateChartsColor();
                    }
                });
            });

            observer.observe(document.documentElement, {
                attributes: true
            });
        });
    </script>
</x-layouts.app>
