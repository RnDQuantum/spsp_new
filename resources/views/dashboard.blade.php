<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dashboard</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>

    <body class="bg-gray-100 min-h-screen" x-data="{ sidebarMinimized: false }"
        @sidebar-toggled.window="sidebarMinimized = $event.detail.minimized">

        <livewire:components.sidebar />

        <!-- Navbar Sticky -->
        <nav :class="sidebarMinimized ? 'md:left-20' : 'md:left-64'"
            class="fixed top-0 right-0 left-0 z-30 bg-white border-b border-gray-200 shadow-sm transition-all duration-300">
            <div class="px-4 py-3 lg:px-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <h2 class="text-xl font-semibold text-gray-800 ml-12 md:ml-0">Dashboard Overview</h2>
                    </div>

                    <div class="flex items-center space-x-4">
                        <!-- Notification -->
                        <button class="relative p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                                </path>
                            </svg>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>

                        <!-- User Profile -->
                        <div class="flex items-center space-x-3">
                            <div class="hidden md:block text-right">
                                <p class="text-sm font-medium text-gray-800">John Doe</p>
                                <p class="text-xs text-gray-500">Administrator</p>
                            </div>
                            <div
                                class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold">
                                JD
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content dengan padding yang menyesuaikan -->
        <div :class="sidebarMinimized ? 'md:ml-20' : 'md:ml-64'"
            class="transition-all duration-300 pt-20 p-8 min-h-screen">
            <h1 class="text-3xl text-center font-bold text-gray-800">Static Pribadi Spider Plot (SPSP)</h1>
            <p class="text-center text-gray-600 mt-2">Selamat Datang!</p>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
                <!-- Chart Segi 5 (Pentagon) -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg text-center font-semibold text-gray-800 mb-4">Potential Mapping (Rating)</h3>
                    <div class="relative h-80">
                        <canvas id="pentagonChart"></canvas>
                    </div>
                </div>

                <!-- Chart Segi 9 (Nonagon) -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg text-center font-semibold text-gray-800 mb-4">Managerial Potency Mapping (Rating)
                    </h3>
                    <div class="relative h-80">
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
                            <canvas id="tridecagonChart"></canvas>
                        </div>
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
    </body>

</html>
