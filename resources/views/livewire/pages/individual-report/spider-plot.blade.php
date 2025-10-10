<div>
    <div class="bg-white mx-auto my-8 shadow overflow-hidden" style="max-width: 1400px;">
        <!-- Header -->
        <div class="border-b-4 border-black py-3 bg-sky-200">
            <h1 class="text-center text-lg font-bold uppercase tracking-wide text-gray-900">
                SPIDER PLOT ANALYSIS
            </h1>
            <p class="text-center text-sm font-semibold text-gray-700 mt-1">
                {{ $participant->name }}
            </p>
        </div>

        <!-- Tolerance Selector Component -->
        @php
            $summary = $this->getPassingSummary();
        @endphp
        @livewire('components.tolerance-selector', [
            'passing' => $summary['passing'],
            'total' => $summary['total'],
        ])

        <!-- Charts Grid -->
        <div class="p-6">
            <h1 class="text-3xl text-center font-bold text-gray-800 mb-8">Static Pribadi Spider Plot (SPSP)</h1>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
                <!-- Chart Potensi (Pentagon) -->
                <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                    <h3 class="text-lg text-center font-semibold text-gray-800 mb-4">Potential Mapping (Rating)</h3>
                    <div class="relative h-96">
                        <canvas id="potensiChart-{{ $potensiChartId }}"></canvas>
                    </div>
                </div>

                <!-- Chart Kompetensi (Nonagon) -->
                <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                    <h3 class="text-lg text-center font-semibold text-gray-800 mb-4">Managerial Potency Mapping (Rating)
                    </h3>
                    <div class="relative h-96">
                        <canvas id="kompetensiChart-{{ $kompetensiChartId }}"></canvas>
                    </div>
                </div>
            </div>

            <!-- Chart General (Tetradecagon) di tengah dengan ukuran lebih besar -->
            <div class="flex justify-center mt-6">
                <div class="w-full lg:w-2/3">
                    <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                        <h3 class="text-lg text-center font-semibold text-gray-800 mb-4">General Mapping (Rating)</h3>
                        <div class="relative h-96">
                            <canvas id="generalChart-{{ $generalChartId }}"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    <!-- Chart Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize all charts
            initializePotensiChart();
            initializeKompetensiChart();
            initializeGeneralChart();

            // Setup Livewire event listeners
            setupLivewireListeners();
        });

        function initializePotensiChart() {
            const ctxPotensi = document.getElementById('potensiChart-{{ $potensiChartId }}').getContext('2d');

            window.potensiChart_{{ $potensiChartId }} = new Chart(ctxPotensi, {
                type: 'radar',
                data: {
                    labels: @js($potensiLabels),
                    datasets: [{
                        label: 'Standard',
                        data: @js($potensiStandardRatings),
                        fill: true,
                        backgroundColor: 'rgba(0, 0, 0, 0.1)',
                        borderColor: '#000000',
                        pointBackgroundColor: '#000000',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#000000'
                    }, {
                        label: 'Tolerance {{ $tolerancePercentage }}%',
                        data: calculateToleranceValues(@js($potensiStandardRatings),
                            {{ $tolerancePercentage }}),
                        borderColor: '#6B7280',
                        backgroundColor: 'transparent',
                        borderWidth: 1.5,
                        borderDash: [5, 5],
                        pointRadius: 2,
                        pointBackgroundColor: '#6B7280'
                    }, {
                        label: '{{ $participant->name }}',
                        data: @js($potensiIndividualRatings),
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
        }

        function initializeKompetensiChart() {
            const ctxKompetensi = document.getElementById('kompetensiChart-{{ $kompetensiChartId }}').getContext('2d');

            window.kompetensiChart_{{ $kompetensiChartId }} = new Chart(ctxKompetensi, {
                type: 'radar',
                data: {
                    labels: @js($kompetensiLabels),
                    datasets: [{
                        label: 'Standard',
                        data: @js($kompetensiStandardRatings),
                        fill: true,
                        backgroundColor: 'rgba(0, 0, 0, 0.1)',
                        borderColor: '#000000',
                        pointBackgroundColor: '#000000',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#000000'
                    }, {
                        label: 'Tolerance {{ $tolerancePercentage }}%',
                        data: calculateToleranceValues(@js($kompetensiStandardRatings),
                            {{ $tolerancePercentage }}),
                        borderColor: '#6B7280',
                        backgroundColor: 'transparent',
                        borderWidth: 1.5,
                        borderDash: [5, 5],
                        pointRadius: 2,
                        pointBackgroundColor: '#6B7280'
                    }, {
                        label: '{{ $participant->name }}',
                        data: @js($kompetensiIndividualRatings),
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
        }

        function initializeGeneralChart() {
            const ctxGeneral = document.getElementById('generalChart-{{ $generalChartId }}').getContext('2d');

            window.generalChart_{{ $generalChartId }} = new Chart(ctxGeneral, {
                type: 'radar',
                data: {
                    labels: @js($generalLabels),
                    datasets: [{
                        label: 'Standard',
                        data: @js($generalStandardRatings),
                        fill: true,
                        backgroundColor: 'rgba(0, 0, 0, 0.1)',
                        borderColor: '#000000',
                        pointBackgroundColor: '#000000',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#000000'
                    }, {
                        label: 'Tolerance {{ $tolerancePercentage }}%',
                        data: calculateToleranceValues(@js($generalStandardRatings),
                            {{ $tolerancePercentage }}),
                        borderColor: '#6B7280',
                        backgroundColor: 'transparent',
                        borderWidth: 1.5,
                        borderDash: [5, 5],
                        pointRadius: 2,
                        pointBackgroundColor: '#6B7280'
                    }, {
                        label: '{{ $participant->name }}',
                        data: @js($generalIndividualRatings),
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
        }

        function calculateToleranceValues(standardRatings, tolerancePercentage) {
            const factor = 1 - (tolerancePercentage / 100);
            return standardRatings.map(val => val * factor);
        }

        function setupLivewireListeners() {
            // Wait for Livewire to be available
            function waitForLivewire(callback) {
                if (window.Livewire) callback();
                else setTimeout(() => waitForLivewire(callback), 100);
            }

            waitForLivewire(function() {
                Livewire.on('chartDataUpdated', function(data) {
                    let chartData = Array.isArray(data) && data.length > 0 ? data[0] : data;
                    if (!chartData) return;

                    const tolerance = chartData.tolerance;
                    const tolerancePercentage = tolerance;

                    // Update Potensi Chart
                    if (chartData.potensi && window.potensiChart_{{ $potensiChartId }}) {
                        const newToleranceRatings = calculateToleranceValues(chartData.potensi
                            .standardRatings, tolerancePercentage);

                        window.potensiChart_{{ $potensiChartId }}.data.labels = chartData.potensi.labels;
                        window.potensiChart_{{ $potensiChartId }}.data.datasets[0].data = chartData.potensi
                            .standardRatings;
                        window.potensiChart_{{ $potensiChartId }}.data.datasets[1].label =
                            `Tolerance ${tolerancePercentage}%`;
                        window.potensiChart_{{ $potensiChartId }}.data.datasets[1].data =
                            newToleranceRatings;
                        window.potensiChart_{{ $potensiChartId }}.data.datasets[2].data = chartData.potensi
                            .individualRatings;
                        window.potensiChart_{{ $potensiChartId }}.update('active');
                    }

                    // Update Kompetensi Chart
                    if (chartData.kompetensi && window.kompetensiChart_{{ $kompetensiChartId }}) {
                        const newToleranceRatings = calculateToleranceValues(chartData.kompetensi
                            .standardRatings, tolerancePercentage);

                        window.kompetensiChart_{{ $kompetensiChartId }}.data.labels = chartData.kompetensi
                            .labels;
                        window.kompetensiChart_{{ $kompetensiChartId }}.data.datasets[0].data = chartData
                            .kompetensi.standardRatings;
                        window.kompetensiChart_{{ $kompetensiChartId }}.data.datasets[1].label =
                            `Tolerance ${tolerancePercentage}%`;
                        window.kompetensiChart_{{ $kompetensiChartId }}.data.datasets[1].data =
                            newToleranceRatings;
                        window.kompetensiChart_{{ $kompetensiChartId }}.data.datasets[2].data = chartData
                            .kompetensi.individualRatings;
                        window.kompetensiChart_{{ $kompetensiChartId }}.update('active');
                    }

                    // Update General Chart
                    if (chartData.general && window.generalChart_{{ $generalChartId }}) {
                        const newToleranceRatings = calculateToleranceValues(chartData.general
                            .standardRatings, tolerancePercentage);

                        window.generalChart_{{ $generalChartId }}.data.labels = chartData.general.labels;
                        window.generalChart_{{ $generalChartId }}.data.datasets[0].data = chartData.general
                            .standardRatings;
                        window.generalChart_{{ $generalChartId }}.data.datasets[1].label =
                            `Tolerance ${tolerancePercentage}%`;
                        window.generalChart_{{ $generalChartId }}.data.datasets[1].data =
                            newToleranceRatings;
                        window.generalChart_{{ $generalChartId }}.data.datasets[2].data = chartData.general
                            .individualRatings;
                        window.generalChart_{{ $generalChartId }}.update('active');
                    }
                });
            });
        }
    </script>
</div>
