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

            <!-- Charts - Vertical Layout -->
            <div class="space-y-6 mt-8">
                <!-- Chart Potensi (Pentagon) -->
                <div class="bg-white p-6 rounded-lg shadow border border-gray-200" wire:ignore>
                    <h3 class="text-lg text-center font-semibold text-gray-800 mb-4">Potential Mapping (Rating)</h3>
                    <div class="relative" style="height: 600px;">
                        <canvas id="potensiChart-{{ $potensiChartId }}"></canvas>
                    </div>
                </div>

                <!-- Chart Kompetensi (Nonagon) -->
                <div class="bg-white p-6 rounded-lg shadow border border-gray-200" wire:ignore>
                    <h3 class="text-lg text-center font-semibold text-gray-800 mb-4">Managerial Potency Mapping (Rating)
                    </h3>
                    <div class="relative" style="height: 600px;">
                        <canvas id="kompetensiChart-{{ $kompetensiChartId }}"></canvas>
                    </div>
                </div>

                <!-- Chart General (Tetradecagon) -->
                <div class="bg-white p-6 rounded-lg shadow border border-gray-200" wire:ignore>
                    <h3 class="text-lg text-center font-semibold text-gray-800 mb-4">General Mapping (Rating)</h3>
                    <div class="relative" style="height: 600px;">
                        <canvas id="generalChart-{{ $generalChartId }}"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Chart Scripts -->
    <script>
        (function() {
            // Prevent multiple initializations
            if (window['spiderChartSetup_{{ $potensiChartId }}']) return;
            window['spiderChartSetup_{{ $potensiChartId }}'] = true;

            // Wait for Chart.js to be available
            function waitForChartJs(callback) {
                if (typeof Chart !== 'undefined') {
                    callback();
                } else {
                    setTimeout(() => waitForChartJs(callback), 50);
                }
            }

            // Wait for DOM and Chart.js
            function init() {
                initializePotensiChart();
                initializeKompetensiChart();
                initializeGeneralChart();
                setupLivewireListeners();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => waitForChartJs(init));
            } else {
                waitForChartJs(init);
            }
        })();

        function initializePotensiChart() {
            // Destroy existing chart if exists
            if (window.potensiChart_{{ $potensiChartId }}) {
                window.potensiChart_{{ $potensiChartId }}.destroy();
            }

            const ctxPotensi = document.getElementById('potensiChart-{{ $potensiChartId }}');
            if (!ctxPotensi) return;

            window.potensiChart_{{ $potensiChartId }} = new Chart(ctxPotensi.getContext('2d'), {
                type: 'radar',
                data: {
                    labels: @js($potensiLabels),
                    datasets: [{
                        label: 'Standard',
                        data: @js($potensiOriginalStandardRatings),
                        fill: true,
                        backgroundColor: 'rgba(99, 102, 241, 0.08)',
                        borderColor: 'rgba(99, 102, 241, 0.9)',
                        pointBackgroundColor: 'rgba(99, 102, 241, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(99, 102, 241, 1)',
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointBorderWidth: 2,
                        datalabels: {
                            color: '#FFFFFF',
                            backgroundColor: 'rgba(99, 102, 241, 1)',
                            borderRadius: 4,
                            padding: {
                                top: 4,
                                bottom: 4,
                                left: 6,
                                right: 6
                            },
                            font: {
                                weight: 'bold',
                                size: 10
                            },
                            anchor: 'end',
                            align: 'end',
                            offset: 6,
                            formatter: (value) => value.toFixed(2)
                        }
                    }, {
                        label: 'Tolerance {{ $tolerancePercentage }}%',
                        data: @js($potensiStandardRatings),
                        borderColor: 'rgba(16, 185, 129, 0.8)',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [8, 4],
                        pointRadius: 3,
                        pointBackgroundColor: 'rgba(16, 185, 129, 0.9)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        datalabels: {
                            color: '#FFFFFF',
                            backgroundColor: 'rgba(16, 185, 129, 0.9)',
                            borderRadius: 4,
                            padding: {
                                top: 4,
                                bottom: 4,
                                left: 6,
                                right: 6
                            },
                            font: {
                                weight: 'bold',
                                size: 9
                            },
                            anchor: 'center',
                            align: 'center',
                            offset: 0,
                            formatter: (value) => value.toFixed(2)
                        }
                    }, {
                        label: '{{ $participant->name }}',
                        data: @js($potensiIndividualRatings),
                        fill: true,
                        backgroundColor: 'rgba(236, 72, 153, 0.12)',
                        borderColor: 'rgba(236, 72, 153, 1)',
                        pointBackgroundColor: 'rgba(236, 72, 153, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(236, 72, 153, 1)',
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointBorderWidth: 2,
                        datalabels: {
                            color: '#FFFFFF',
                            backgroundColor: 'rgba(236, 72, 153, 1)',
                            borderRadius: 4,
                            padding: {
                                top: 4,
                                bottom: 4,
                                left: 6,
                                right: 6
                            },
                            font: {
                                weight: 'bold',
                                size: 10
                            },
                            anchor: 'end',
                            align: 'start',
                            offset: 6,
                            formatter: (value) => value.toFixed(2)
                        }
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
                        },
                        datalabels: {
                            display: true
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        function initializeKompetensiChart() {
            // Destroy existing chart if exists
            if (window.kompetensiChart_{{ $kompetensiChartId }}) {
                window.kompetensiChart_{{ $kompetensiChartId }}.destroy();
            }

            const ctxKompetensi = document.getElementById('kompetensiChart-{{ $kompetensiChartId }}');
            if (!ctxKompetensi) return;

            window.kompetensiChart_{{ $kompetensiChartId }} = new Chart(ctxKompetensi.getContext('2d'), {
                type: 'radar',
                data: {
                    labels: @js($kompetensiLabels),
                    datasets: [{
                        label: 'Standard',
                        data: @js($kompetensiOriginalStandardRatings),
                        fill: true,
                        backgroundColor: 'rgba(6, 182, 212, 0.08)',
                        borderColor: 'rgba(6, 182, 212, 0.9)',
                        pointBackgroundColor: 'rgba(6, 182, 212, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(6, 182, 212, 1)',
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointBorderWidth: 2,
                        datalabels: {
                            color: '#FFFFFF',
                            backgroundColor: 'rgba(6, 182, 212, 1)',
                            borderRadius: 4,
                            padding: {
                                top: 4,
                                bottom: 4,
                                left: 6,
                                right: 6
                            },
                            font: {
                                weight: 'bold',
                                size: 10
                            },
                            anchor: 'end',
                            align: 'end',
                            offset: 6,
                            formatter: (value) => value.toFixed(2)
                        }
                    }, {
                        label: 'Tolerance {{ $tolerancePercentage }}%',
                        data: @js($kompetensiStandardRatings),
                        borderColor: 'rgba(251, 146, 60, 0.8)',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [8, 4],
                        pointRadius: 3,
                        pointBackgroundColor: 'rgba(251, 146, 60, 0.9)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        datalabels: {
                            color: '#FFFFFF',
                            backgroundColor: 'rgba(251, 146, 60, 0.9)',
                            borderRadius: 4,
                            padding: {
                                top: 4,
                                bottom: 4,
                                left: 6,
                                right: 6
                            },
                            font: {
                                weight: 'bold',
                                size: 9
                            },
                            anchor: 'center',
                            align: 'center',
                            offset: 0,
                            formatter: (value) => value.toFixed(2)
                        }
                    }, {
                        label: '{{ $participant->name }}',
                        data: @js($kompetensiIndividualRatings),
                        fill: true,
                        backgroundColor: 'rgba(245, 158, 11, 0.12)',
                        borderColor: 'rgba(245, 158, 11, 1)',
                        pointBackgroundColor: 'rgba(245, 158, 11, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(245, 158, 11, 1)',
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointBorderWidth: 2,
                        datalabels: {
                            color: '#FFFFFF',
                            backgroundColor: 'rgba(245, 158, 11, 1)',
                            borderRadius: 4,
                            padding: {
                                top: 4,
                                bottom: 4,
                                left: 6,
                                right: 6
                            },
                            font: {
                                weight: 'bold',
                                size: 10
                            },
                            anchor: 'end',
                            align: 'start',
                            offset: 6,
                            formatter: (value) => value.toFixed(2)
                        }
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
                        },
                        datalabels: {
                            display: true
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        function initializeGeneralChart() {
            // Destroy existing chart if exists
            if (window.generalChart_{{ $generalChartId }}) {
                window.generalChart_{{ $generalChartId }}.destroy();
            }

            const ctxGeneral = document.getElementById('generalChart-{{ $generalChartId }}');
            if (!ctxGeneral) return;

            window.generalChart_{{ $generalChartId }} = new Chart(ctxGeneral.getContext('2d'), {
                type: 'radar',
                data: {
                    labels: @js($generalLabels),
                    datasets: [{
                        label: 'Standard',
                        data: @js($generalOriginalStandardRatings),
                        fill: true,
                        backgroundColor: 'rgba(16, 185, 129, 0.08)',
                        borderColor: 'rgba(16, 185, 129, 0.9)',
                        pointBackgroundColor: 'rgba(16, 185, 129, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointBorderWidth: 2,
                        datalabels: {
                            color: '#FFFFFF',
                            backgroundColor: 'rgba(16, 185, 129, 1)',
                            borderRadius: 4,
                            padding: {
                                top: 4,
                                bottom: 4,
                                left: 6,
                                right: 6
                            },
                            font: {
                                weight: 'bold',
                                size: 10
                            },
                            anchor: 'end',
                            align: 'end',
                            offset: 6,
                            formatter: (value) => value.toFixed(2)
                        }
                    }, {
                        label: 'Tolerance {{ $tolerancePercentage }}%',
                        data: @js($generalStandardRatings),
                        borderColor: 'rgba(239, 68, 68, 0.8)',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [8, 4],
                        pointRadius: 3,
                        pointBackgroundColor: 'rgba(239, 68, 68, 0.9)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        datalabels: {
                            color: '#FFFFFF',
                            backgroundColor: 'rgba(239, 68, 68, 0.9)',
                            borderRadius: 4,
                            padding: {
                                top: 4,
                                bottom: 4,
                                left: 6,
                                right: 6
                            },
                            font: {
                                weight: 'bold',
                                size: 9
                            },
                            anchor: 'center',
                            align: 'center',
                            offset: 0,
                            formatter: (value) => value.toFixed(2)
                        }
                    }, {
                        label: '{{ $participant->name }}',
                        data: @js($generalIndividualRatings),
                        fill: true,
                        backgroundColor: 'rgba(168, 85, 247, 0.12)',
                        borderColor: 'rgba(168, 85, 247, 1)',
                        pointBackgroundColor: 'rgba(168, 85, 247, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(168, 85, 247, 1)',
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointBorderWidth: 2,
                        datalabels: {
                            color: '#FFFFFF',
                            backgroundColor: 'rgba(168, 85, 247, 1)',
                            borderRadius: 4,
                            padding: {
                                top: 4,
                                bottom: 4,
                                left: 6,
                                right: 6
                            },
                            font: {
                                weight: 'bold',
                                size: 10
                            },
                            anchor: 'end',
                            align: 'start',
                            offset: 6,
                            formatter: (value) => value.toFixed(2)
                        }
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
                        },
                        datalabels: {
                            display: true
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
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

                    const tolerancePercentage = chartData.tolerance;

                    // Update Potensi Chart
                    if (chartData.potensi && window.potensiChart_{{ $potensiChartId }}) {
                        window.potensiChart_{{ $potensiChartId }}.data.labels = chartData.potensi.labels;
                        window.potensiChart_{{ $potensiChartId }}.data.datasets[0].data = chartData.potensi
                            .originalStandardRatings;
                        window.potensiChart_{{ $potensiChartId }}.data.datasets[1].label =
                            `Tolerance ${tolerancePercentage}%`;
                        window.potensiChart_{{ $potensiChartId }}.data.datasets[1].data = chartData.potensi
                            .standardRatings;
                        // Dataset[2] (individual) doesn't change
                        window.potensiChart_{{ $potensiChartId }}.update('active');
                    }

                    // Update Kompetensi Chart
                    if (chartData.kompetensi && window.kompetensiChart_{{ $kompetensiChartId }}) {
                        window.kompetensiChart_{{ $kompetensiChartId }}.data.labels = chartData.kompetensi
                            .labels;
                        window.kompetensiChart_{{ $kompetensiChartId }}.data.datasets[0].data = chartData
                            .kompetensi.originalStandardRatings;
                        window.kompetensiChart_{{ $kompetensiChartId }}.data.datasets[1].label =
                            `Tolerance ${tolerancePercentage}%`;
                        window.kompetensiChart_{{ $kompetensiChartId }}.data.datasets[1].data = chartData
                            .kompetensi.standardRatings;
                        // Dataset[2] (individual) doesn't change
                        window.kompetensiChart_{{ $kompetensiChartId }}.update('active');
                    }

                    // Update General Chart
                    if (chartData.general && window.generalChart_{{ $generalChartId }}) {
                        window.generalChart_{{ $generalChartId }}.data.labels = chartData.general.labels;
                        window.generalChart_{{ $generalChartId }}.data.datasets[0].data = chartData.general
                            .originalStandardRatings;
                        window.generalChart_{{ $generalChartId }}.data.datasets[1].label =
                            `Tolerance ${tolerancePercentage}%`;
                        window.generalChart_{{ $generalChartId }}.data.datasets[1].data = chartData.general
                            .standardRatings;
                        // Dataset[2] (individual) doesn't change
                        window.generalChart_{{ $generalChartId }}.update('active');
                    }
                });
            });
        }
    </script>
</div>
