<x-layouts.app title="Dashboard Overview">

    <h1 class="text-3xl text-center font-bold text-gray-900 dark:text-gray-100">Static Pribadi Spider Plot (SPSP)</h1>
    <p class="text-center text-gray-700 dark:text-gray-300 mt-2">Selamat Datang!</p>

    <!-- Charts - Susunan Vertikal -->
    <div class="flex flex-col gap-6 mt-8 max-w-4xl mx-auto">
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

        // Function untuk update warna chart berdasarkan dark mode
        function updateChartsColor() {
            const isDarkMode = document.documentElement.classList.contains('dark');

            const textColor = isDarkMode ? '#f3f4f6' : '#000000'; // gray-100 : black
            const gridColor = isDarkMode ? '#6b7280' : '#4b5563'; // gray-500 : gray-600
            const backdropColor = isDarkMode ? 'rgba(31, 41, 55, 0.8)' : 'rgba(255, 255, 255, 0.9)';

            // Update semua chart
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
                        data: [4.2, 3.8, 4.5, 3.2, 4.0],
                        fill: true,
                        backgroundColor: 'rgba(220, 38, 38, 0.3)',
                        borderColor: 'rgb(220, 38, 38)',
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
                            display: true,
                            position: 'top',
                            labels: {
                                color: textColor,
                                font: {
                                    size: 16,
                                    weight: 'bold',
                                    family: 'Arial'
                                },
                                padding: 15
                            }
                        },
                        tooltip: {
                            enabled: true
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
                        data: [4.3, 3.9, 4.7, 3.5, 4.2, 3.8, 4.5, 3.6, 4.1],
                        fill: true,
                        backgroundColor: 'rgba(220, 38, 38, 0.3)',
                        borderColor: 'rgb(220, 38, 38)',
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
                            display: true,
                            position: 'top',
                            labels: {
                                color: textColor,
                                font: {
                                    size: 16,
                                    weight: 'bold',
                                    family: 'Arial'
                                },
                                padding: 15
                            }
                        },
                        tooltip: {
                            enabled: true
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
                        data: [4.5, 3.2, 4.8, 3.9, 4.1, 3.5, 4.3, 3.8, 4.6, 3.7, 4.2, 3.4, 4.0,
                            4.0],
                        fill: true,
                        backgroundColor: 'rgba(220, 38, 38, 0.3)',
                        borderColor: 'rgb(220, 38, 38)',
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
                            display: true,
                            position: 'top',
                            labels: {
                                color: textColor,
                                font: {
                                    size: 16,
                                    weight: 'bold',
                                    family: 'Arial'
                                },
                                padding: 15
                            }
                        },
                        tooltip: {
                            enabled: true
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

            // Mulai observe perubahan pada HTML element
            observer.observe(document.documentElement, {
                attributes: true
            });
        });
    </script>
</x-layouts.app>
