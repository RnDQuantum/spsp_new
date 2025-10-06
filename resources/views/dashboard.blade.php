<x-layouts.app title="Dashboard Overview">

    <h1 class="text-3xl text-center font-bold text-gray-800">Static Pribadi Spider Plot (SPSP)</h1>
    <p class="text-center text-gray-600 mt-2">Selamat Datang!</p>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
        <!-- Chart Segi 5 (Pentagon) -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg text-center font-semibold text-gray-800 mb-4">Potential Mapping (Rating)</h3>
            <div class="relative h-96">
                <canvas id="pentagonChart"></canvas>
            </div>
        </div>

        <!-- Chart Segi 9 (Nonagon) -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg text-center font-semibold text-gray-800 mb-4">Managerial Potency Mapping (Rating)
            </h3>
            <div class="relative h-96">
                <canvas id="nonagonChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart Segi 13 di tengah dengan ukuran lebih besar -->
    <div class="flex justify-center mt-6">
        <div class="w-full lg:w-2/3">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg text-center font-semibold text-gray-800 mb-4">General Mapping
                    (Rating)</h3>
                <div class="relative h-96">
                    <canvas id="tetradecagonChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Chart Segi 5 (Pentagon)
            const ctxPentagon = document.getElementById('pentagonChart').getContext('2d');
            const pentagonChart = new Chart(ctxPentagon, {
                type: 'radar',
                data: {
                    labels: ['Kecerdasan', 'Cara Kerja', 'Potensi Kerja', 'Hubungan Sosial', 'Kepribadian'],
                    datasets: [{
                        label: 'Rating',
                        data: [4.2, 3.8, 4.5, 3.2, 4.0],
                        fill: true,
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        borderColor: 'rgb(59, 130, 246)',
                        pointBackgroundColor: 'rgb(59, 130, 246)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgb(59, 130, 246)'
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
                                font: {
                                    size: 16
                                }
                            },
                            pointLabels: {
                                font: {
                                    size: 16
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                font: {
                                    size: 16
                                }
                            }
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Chart Segi 9 (Nonagon)
            const ctxNonagon = document.getElementById('nonagonChart').getContext('2d');
            const nonagonChart = new Chart(ctxNonagon, {
                type: 'radar',
                data: {
                    labels: [
                        'Integritas', 'Kerjasama', 'Komunikasi', 'Oriantasi Pada Hasil',
                        'Pelayanan Publik',
                        'Pengembangan Diri dari Orang Lain', 'Mengelola Perubahan',
                        'Pengambilan Keputusan', 'Perekat Bangsa'
                    ],
                    datasets: [{
                        label: 'Rating',
                        data: [4.3, 3.9, 4.7, 3.5, 4.2, 3.8, 4.5, 3.6, 4.1],
                        fill: true,
                        backgroundColor: 'rgba(245, 158, 11, 0.2)',
                        borderColor: 'rgb(245, 158, 11)',
                        pointBackgroundColor: 'rgb(245, 158, 11)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgb(245, 158, 11)'
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
                                font: {
                                    size: 16
                                }
                            },
                            pointLabels: {
                                font: {
                                    size: 16
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                font: {
                                    size: 16
                                }
                            }
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Chart Segi 13 (tetradecagon)
            const ctxtetradecagon = document.getElementById('tetradecagonChart').getContext('2d');
            const tetradecagonChart = new Chart(ctxtetradecagon, {
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
                            4.0
                        ],
                        fill: true,
                        backgroundColor: 'rgba(16, 185, 129, 0.2)',
                        borderColor: 'rgb(16, 185, 129)',
                        pointBackgroundColor: 'rgb(16, 185, 129)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgb(16, 185, 129)'
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
                                font: {
                                    size: 16
                                }
                            },
                            pointLabels: {
                                font: {
                                    size: 16
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                font: {
                                    size: 16
                                }
                            }
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        });
    </script>
</x-layouts.app>
