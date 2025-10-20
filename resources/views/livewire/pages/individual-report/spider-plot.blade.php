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
            <p class="text-center text-sm font-semibold text-gray-700 mt-1">
                {{ $participant->event->name }}
            </p>
            <p class="text-center text-sm font-semibold text-gray-700 mt-1">
                {{ $participant->positionFormation->name }} - {{ $participant->positionFormation->template->name }}
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

            // ========================================
            // POTENSI CHART - URUTAN: PESERTA → TOLERANSI → STANDARD
            // ========================================
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
                                // === LAYER 1: PESERTA (PASTi BAWAH) ===
                                label: '{{ $participant->name }}',
                                data: @js($potensiIndividualRatings),
                                fill: true,
                                backgroundColor: '#5db010', // HIJAU SOLID
                                borderColor: '#8fd006',
                                pointBackgroundColor: '#8fd006',
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: '#8fd006',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                pointBorderWidth: 2,
                                datalabels: {
                                    color: '#000000',
                                    backgroundColor: '#5db010',
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
                            },
                            {
                                // === LAYER 2: TOLERANSI (TENGAH) ===
                                label: 'Tolerance {{ $tolerancePercentage }}%',
                                data: @js($potensiStandardRatings),
                                borderColor: '#b50505', // MERAH SOLID
                                backgroundColor: '#b50505',
                                borderWidth: 2,
                                // borderDash: [8, 4],,
                                pointRadius: 3,
                                pointBackgroundColor: '#9a0404',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                fill: true,
                                datalabels: {
                                    color: '#FFFFFF',
                                    backgroundColor: '#b50505',
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
                                    anchor: 'end',
                                    align: 'start',
                                    offset: 0,
                                    formatter: (value) => value.toFixed(2)
                                }
                            },
                            {
                                // === LAYER 3: STANDARD (PASTi ATAS) ===
                                label: 'Standard',
                                data: @js($potensiOriginalStandardRatings),
                                fill: true,
                                backgroundColor: '#fafa05', // KUNING SOLID
                                borderColor: '#e6d105',
                                pointBackgroundColor: '#e6d105',
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: '#e6d105',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                pointBorderWidth: 2,
                                datalabels: {
                                    color: '#000000',
                                    backgroundColor: '#fafa05',
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
                                    anchor: 'center',
                                    align: 'center',
                                    offset: 6,
                                    formatter: (value) => value.toFixed(2)
                                }
                            }
                        ]
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

            // ========================================
            // KOMPETENSI CHART - URUTAN SAMA
            // ========================================
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
                                // === LAYER 1: PESERTA (PASTi BAWAH) ===
                                label: '{{ $participant->name }}',
                                data: @js($kompetensiIndividualRatings),
                                fill: true,
                                backgroundColor: '#5db010', // HIJAU SOLID
                                borderColor: '#8fd006',
                                pointBackgroundColor: '#8fd006',
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: '#8fd006',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                pointBorderWidth: 2,
                                datalabels: {
                                    color: '#000000',
                                    backgroundColor: '#5db010',
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
                            },
                            {
                                // === LAYER 2: TOLERANSI (TENGAH) ===
                                label: 'Tolerance {{ $tolerancePercentage }}%',
                                data: @js($kompetensiStandardRatings),
                                borderColor: '#b50505', // MERAH SOLID
                                backgroundColor: '#b50505',
                                borderWidth: 2,
                                // borderDash: [8, 4],,
                                pointRadius: 3,
                                pointBackgroundColor: '#9a0404',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                fill: true,
                                datalabels: {
                                    color: '#FFFFFF',
                                    backgroundColor: '#b50505',
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
                                    anchor: 'end',
                                    align: 'start',
                                    offset: 0,
                                    formatter: (value) => value.toFixed(2)
                                }
                            },
                            {
                                // === LAYER 3: STANDARD (PASTi ATAS) ===
                                label: 'Standard',
                                data: @js($kompetensiOriginalStandardRatings),
                                fill: true,
                                backgroundColor: '#fafa05', // KUNING SOLID
                                borderColor: '#e6d105',
                                pointBackgroundColor: '#e6d105',
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: '#e6d105',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                pointBorderWidth: 2,
                                datalabels: {
                                    color: '#000000',
                                    backgroundColor: '#fafa05',
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
                                    anchor: 'center',
                                    align: 'center',
                                    offset: 6,
                                    formatter: (value) => value.toFixed(2)
                                }
                            }
                        ]
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

            // ========================================
            // GENERAL CHART - URUTAN SAMA
            // ========================================
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
                                // === LAYER 1: PESERTA (PASTi BAWAH) ===
                                label: '{{ $participant->name }}',
                                data: @js($generalIndividualRatings),
                                fill: true,
                                backgroundColor: '#5db010', // HIJAU SOLID
                                borderColor: '#8fd006',
                                pointBackgroundColor: '#8fd006',
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: '#8fd006',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                pointBorderWidth: 2,
                                datalabels: {
                                    color: '#000000',
                                    backgroundColor: '#5db010',
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
                            },
                            {
                                // === LAYER 2: TOLERANSI (TENGAH) ===
                                label: 'Tolerance {{ $tolerancePercentage }}%',
                                data: @js($generalStandardRatings),
                                borderColor: '#b50505', // MERAH SOLID
                                backgroundColor: '#b50505',
                                borderWidth: 2,
                                // borderDash: [8, 4],,
                                pointRadius: 3,
                                pointBackgroundColor: '#9a0404',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                fill: true,
                                datalabels: {
                                    color: '#FFFFFF',
                                    backgroundColor: '#b50505',
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
                                    anchor: 'end',
                                    align: 'start',
                                    offset: 0,
                                    formatter: (value) => value.toFixed(2)
                                }
                            },
                            {
                                // === LAYER 3: STANDARD (PASTi ATAS) ===
                                label: 'Standard',
                                data: @js($generalOriginalStandardRatings),
                                fill: true,
                                backgroundColor: '#fafa05', // KUNING SOLID
                                borderColor: '#e6d105',
                                pointBackgroundColor: '#e6d105',
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: '#e6d105',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                pointBorderWidth: 2,
                                datalabels: {
                                    color: '#000000',
                                    backgroundColor: '#fafa05',
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
                                    anchor: 'center',
                                    align: 'center',
                                    offset: 6,
                                    formatter: (value) => value.toFixed(2)
                                }
                            }
                        ]
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

            // ========================================
            // LIVEWIRE LISTENERS - UPDATE TOLERANSI
            // ========================================
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

                        // **POTENSI CHART**: [0=PESERTA, 1=TOLERANSI, 2=STANDARD]
                        if (chartData.potensi && window.potensiChart_{{ $potensiChartId }}) {
                            const chart = window.potensiChart_{{ $potensiChartId }};
                            chart.data.datasets[1].label =
                                `Tolerance ${tolerancePercentage}%`; // TOLERANSI
                            chart.data.datasets[1].data = chartData.potensi
                                .standardRatings; // TOLERANSI
                            chart.data.datasets[2].data = chartData.potensi
                                .originalStandardRatings; // STANDARD
                            chart.update('active');
                        }

                        // **KOMPETENSI CHART**: [0=PESERTA, 1=TOLERANSI, 2=STANDARD]
                        if (chartData.kompetensi && window.kompetensiChart_{{ $kompetensiChartId }}) {
                            const chart = window.kompetensiChart_{{ $kompetensiChartId }};
                            chart.data.datasets[1].label =
                                `Tolerance ${tolerancePercentage}%`; // TOLERANSI
                            chart.data.datasets[1].data = chartData.kompetensi
                                .standardRatings; // TOLERANSI
                            chart.data.datasets[2].data = chartData.kompetensi
                                .originalStandardRatings; // STANDARD
                            chart.update('active');
                        }

                        // **GENERAL CHART**: [0=PESERTA, 1=TOLERANSI, 2=STANDARD]
                        if (chartData.general && window.generalChart_{{ $generalChartId }}) {
                            const chart = window.generalChart_{{ $generalChartId }};
                            chart.data.datasets[1].label =
                                `Tolerance ${tolerancePercentage}%`; // TOLERANSI
                            chart.data.datasets[1].data = chartData.general
                                .standardRatings; // TOLERANSI
                            chart.data.datasets[2].data = chartData.general
                                .originalStandardRatings; // STANDARD
                            chart.update('active');
                        }
                    });
                });
            }
        })();
    </script>
</div>
