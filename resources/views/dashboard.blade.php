<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dashboard</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>

    <body class="bg-gray-100 min-h-screen">

        <livewire:components.sidebar />

        <div class="transition-all duration-300 md:ml-64 p-8 min-h-screen">
            <h1 class="text-3xl font-bold text-gray-800 mt-12 md:mt-0">Dashboard</h1>
            <p class="text-gray-600 mt-2">Selamat datang di dashboard</p>

            <!-- Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-gray-500">Total Users</h3>
                    <p class="text-3xl font-bold mt-2">1,234</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-gray-500">Active</h3>
                    <p class="text-3xl font-bold mt-2">892</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-gray-500">Pending</h3>
                    <p class="text-3xl font-bold mt-2">45</p>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-8">
                <!-- Chart Segi 13 -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg text-center font-semibold text-gray-800 mb-4">General Mapping (Rating)</h3>
                    <div class="relative h-80">
                        <canvas id="tridecagonChart"></canvas>
                    </div>
                </div>

                <!-- Chart Segi 5 (Pentagon) -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg text-center font-semibold text-gray-800 mb-4">Potential Mapping (Rating)</h3>
                    <div class="relative h-80">
                        <canvas id="pentagonChart"></canvas>
                    </div>
                </div>

                <!-- Chart Segi 9 (Nonagon) -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg text-center font-semibold text-gray-800 mb-4">Managerial Potency Mapping(Rating)
                    </h3>
                    <div class="relative h-80">
                        <canvas id="nonagonChart"></canvas>
                    </div>
                </div>

            </div>
        </div>

        @livewireScripts

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Chart Segi 5 (Pentagon)
                const ctxPentagon = document.getElementById('pentagonChart').getContext('2d');
                const pentagonChart = new Chart(ctxPentagon, {
                    type: 'radar',
                    data: {
                        labels: ['Aspek 1', 'Aspek 2', 'Aspek 3', 'Aspek 4', 'Aspek 5'],
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
                                    stepSize: 1
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
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
                            'Aspek 1', 'Aspek 2', 'Aspek 3', 'Aspek 4', 'Aspek 5',
                            'Aspek 6', 'Aspek 7', 'Aspek 8', 'Aspek 9'
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
                                    stepSize: 1
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            }
                        },
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });

                // Chart Segi 13 (Tridecagon)
                const ctxTridecagon = document.getElementById('tridecagonChart').getContext('2d');
                const tridecagonChart = new Chart(ctxTridecagon, {
                    type: 'radar',
                    data: {
                        labels: [
                            'Aspek 1', 'Aspek 2', 'Aspek 3', 'Aspek 4', 'Aspek 5',
                            'Aspek 6', 'Aspek 7', 'Aspek 8', 'Aspek 9', 'Aspek 10',
                            'Aspek 11', 'Aspek 12', 'Aspek 13'
                        ],
                        datasets: [{
                            label: 'Rating',
                            data: [4.5, 3.2, 4.8, 3.9, 4.1, 3.5, 4.3, 3.8, 4.6, 3.7, 4.2, 3.4, 4.0],
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
                                    stepSize: 1
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            }
                        },
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            });
        </script>
    </body>

</html>
