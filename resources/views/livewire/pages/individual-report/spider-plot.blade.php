<div>
    <div class="mx-auto my-8 shadow overflow-hidden max-w-6xl bg-white dark:bg-gray-800" style="max-width: 1400px;">

        <!-- Header - DARK MODE READY -->
        <div class="border-b-4 border-black py-3 bg-gray-300 dark:bg-gray-600">
            <h1 class="text-center text-lg font-bold uppercase tracking-wide text-gray-900 dark:text-white">
                SPIDER PLOT ANALYSIS
            </h1>
            <p class="text-center text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">
                {{ $participant->name }}
            </p>
            <p class="text-center text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">
                {{ $participant->event->name }}
            </p>
            <p class="text-center text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">
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

        <!-- Charts Grid - DARK MODE READY -->
        <div class="p-6 bg-white dark:bg-gray-800">
            <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mb-8">Static Pribadi Spider Plot
                (SPSP)</h1>

            <!-- Charts - Vertical Layout -->
            <div class="space-y-6 mt-8">
                <!-- Chart Potensi (Pentagon) -->
                <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow border border-gray-200 dark:border-gray-600"
                    wire:ignore>
                    <h3 class="text-lg text-center font-semibold text-gray-800 dark:text-white mb-4">Potential Mapping
                        (Rating)</h3>
                    <div class="relative" style="height: 600px;">
                        <canvas id="potensiChart-{{ $potensiChartId }}"></canvas>
                    </div>
                </div>

                <!-- Chart Kompetensi (Nonagon) -->
                <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow border border-gray-200 dark:border-gray-600"
                    wire:ignore>
                    <h3 class="text-lg text-center font-semibold text-gray-800 dark:text-white mb-4">Managerial Potency
                        Mapping (Rating)</h3>
                    <div class="relative" style="height: 600px;">
                        <canvas id="kompetensiChart-{{ $kompetensiChartId }}"></canvas>
                    </div>
                </div>

                <!-- Chart General (Tetradecagon) -->
                <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow border border-gray-200 dark:border-gray-600"
                    wire:ignore>
                    <h3 class="text-lg text-center font-semibold text-gray-800 dark:text-white mb-4">General Mapping
                        (Rating)</h3>
                    <div class="relative" style="height: 600px;">
                        <canvas id="generalChart-{{ $generalChartId }}"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Scripts - DARK MODE FIXED -->
    <script>
        (function() {
            // Prevent multiple initializations
            if (window['spiderChartSetup_{{ $potensiChartId }}']) return;
            window['spiderChartSetup_{{ $potensiChartId }}'] = true;

            // 🌙 DARK MODE COLORS
            // 🌙 DARK MODE COLORS
            const getColors = () => {
                const dark = document.documentElement.classList.contains('dark');
                return {
                    grid: dark ? 'rgba(255, 255, 255, 0.5)' : 'rgba(0, 0, 0, 0.5)', // Lebih pekat (0.15 -> 0.5)
                    angleLines: dark ? 'rgba(255, 255, 255, 0.5)' :
                    'rgba(0, 0, 0, 0.5)', // Lebih pekat (0.15 -> 0.5)
                    ticks: dark ? '#ffffff' : '#000000', // Warna solid
                    pointLabels: dark ? '#ffffff' : '#000000', // Warna solid
                    legend: dark ? '#ffffff' : '#000000' // Warna solid
                };
            };

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
            // POTENSI CHART - DARK MODE READY
            // ========================================
            function initializePotensiChart() {
                if (window.potensiChart_{{ $potensiChartId }}) {
                    window.potensiChart_{{ $potensiChartId }}.destroy();
                }

                const ctxPotensi = document.getElementById('potensiChart-{{ $potensiChartId }}');
                if (!ctxPotensi) return;

                const colors = getColors();

                window.potensiChart_{{ $potensiChartId }} = new Chart(ctxPotensi.getContext('2d'), {
                    type: 'radar',
                    data: {
                        labels: @js($potensiLabels),
                        datasets: [{
                                // LAYER 1: PESERTA (HIJAU) - Dataset 0
                                label: '{{ $participant->name }}',
                                data: @js($potensiIndividualRatings),
                                fill: true,
                                backgroundColor: '#5db010', // ← UBAH dari rgba(..., 0.7) ke SOLID
                                borderColor: '#8fd006',
                                pointBackgroundColor: '#8fd006',
                                pointBorderColor: '#fff',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                pointBorderWidth: 2,
                                datalabels: {
                                    display: false,
                                    color: '#000000',
                                    backgroundColor: '#5db010',
                                    borderRadius: 4,
                                    padding: 6,
                                    font: {
                                        weight: 'bold',
                                        size: 10
                                    },
                                    anchor: 'end',
                                    align: 'end',
                                    offset: 6,
                                    formatter: (v) => v.toFixed(2)
                                }
                            },
                            {
                                // LAYER 2: STANDARD (MERAH) - Dataset 1
                                label: 'Standard', // ← UBAH dari 'Tolerance {{ $tolerancePercentage }}%'
                                data: @js($potensiStandardRatings),
                                backgroundColor: '#b50505', // ← UBAH dari rgba(..., 0.7) ke SOLID
                                borderColor: '#b50505',
                                borderWidth: 2,
                                pointRadius: 3,
                                pointBackgroundColor: '#9a0404',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                fill: true,
                                datalabels: {
                                    display: false,
                                    color: '#FFFFFF',
                                    backgroundColor: '#b50505',
                                    borderRadius: 4,
                                    padding: 6,
                                    font: {
                                        weight: 'bold',
                                        size: 9
                                    },
                                    anchor: 'end',
                                    align: 'start',
                                    formatter: (v) => v.toFixed(2)
                                }
                            },
                            {
                                // LAYER 3: TOLERANCE (KUNING) - Dataset 2
                                label: 'Tolerance {{ $tolerancePercentage }}%', // ← UBAH dari 'Standard'
                                data: @js($potensiOriginalStandardRatings),
                                fill: true,
                                backgroundColor: '#fafa05', // ← UBAH dari rgba(..., 0.7) ke SOLID
                                borderColor: '#e6d105',
                                pointBackgroundColor: '#e6d105',
                                pointBorderColor: '#fff',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                pointBorderWidth: 2,
                                datalabels: {
                                    display: false,
                                    color: '#000000',
                                    backgroundColor: '#fafa05',
                                    borderRadius: 4,
                                    padding: 6,
                                    font: {
                                        weight: 'bold',
                                        size: 10
                                    },
                                    anchor: 'center',
                                    align: 'center',
                                    offset: 6,
                                    formatter: (v) => v.toFixed(2)
                                }
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    color: colors.legend,
                                    font: {
                                        size: 14
                                    }
                                }
                            },
                            datalabels: {
                                display: true
                            }
                        },
                        scales: {
                            r: {
                                beginAtZero: true,
                                min: 0,
                                max: 5,
                                ticks: {
                                    display: false,
                                    stepSize: 1,
                                    color: colors.ticks,
                                    font: {
                                        size: 16,
                                        weight: 'bold'
                                    },
                                    backdropColor: 'transparent',
                                    showLabelBackdrop: false,
                                    z: 2 // Gunakan z-index 2 bukan 1000
                                },
                                pointLabels: {
                                    color: colors.pointLabels,
                                    font: {
                                        size: 12,
                                        weight: '600'
                                    },
                                    z: 3 // Gunakan z-index 3 bukan 1000
                                },
                                grid: {
                                    color: colors.grid,
                                    z: 1 // Tambahkan z-index 1
                                },
                                angleLines: {
                                    color: colors.angleLines,
                                    z: 1 // Tambahkan z-index 1
                                }
                            }
                        }
                    },
                    plugins: [{
                        id: 'shiftTicks',
                        afterDraw: (chart) => {
                            const {
                                ctx,
                                scales
                            } = chart;
                            const scale = scales.r;
                            const ticks = scale.ticks;
                            const yCenter = scale.yCenter;
                            const xCenter = scale.xCenter;

                            ctx.save();
                            ctx.font = `bold ${scale.options.ticks.font.size}px sans-serif`;
                            ctx.fillStyle = scale.options.ticks.color || '#000';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';

                            const offsetX = 10;
                            const offsetY = 0;

                            ticks.forEach((tick) => {
                                const value = tick.value;
                                const radius = scale.getDistanceFromCenterForValue(value);
                                const labelY = yCenter - radius - offsetY;
                                const labelX = xCenter + offsetX;
                                ctx.fillText(value, labelX, labelY);
                            });

                            ctx.restore();
                        }
                    }]
                });
            }

            // ========================================
            // KOMPETENSI CHART - DARK MODE READY
            // ========================================
            function initializeKompetensiChart() {
                if (window.kompetensiChart_{{ $kompetensiChartId }}) {
                    window.kompetensiChart_{{ $kompetensiChartId }}.destroy();
                }

                const ctxKompetensi = document.getElementById('kompetensiChart-{{ $kompetensiChartId }}');
                if (!ctxKompetensi) return;

                const colors = getColors();

                window.kompetensiChart_{{ $kompetensiChartId }} = new Chart(ctxKompetensi.getContext('2d'), {
                    type: 'radar',
                    data: {
                        labels: @js($kompetensiLabels),
                        datasets: [{
                                label: '{{ $participant->name }}',
                                data: @js($kompetensiIndividualRatings),
                                fill: true,
                                backgroundColor: '#5db010', // ← SOLID
                                borderColor: '#8fd006',
                                pointBackgroundColor: '#8fd006',
                                pointBorderColor: '#fff',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                pointBorderWidth: 2,
                                datalabels: {
                                    display: false,
                                    color: '#000000',
                                    backgroundColor: '#5db010',
                                    borderRadius: 4,
                                    padding: 6,
                                    font: {
                                        weight: 'bold',
                                        size: 10
                                    },
                                    anchor: 'end',
                                    align: 'end',
                                    offset: 6,
                                    formatter: (v) => v.toFixed(2)
                                }
                            },
                            {
                                label: 'Standard', // ← UBAH
                                data: @js($kompetensiStandardRatings),
                                backgroundColor: '#b50505', // ← SOLID
                                borderColor: '#b50505',
                                borderWidth: 2,
                                pointRadius: 3,
                                pointBackgroundColor: '#9a0404',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                fill: true,
                                datalabels: {
                                    display: false,
                                    color: '#FFFFFF',
                                    backgroundColor: '#b50505',
                                    borderRadius: 4,
                                    padding: 6,
                                    font: {
                                        weight: 'bold',
                                        size: 9
                                    },
                                    anchor: 'end',
                                    align: 'start',
                                    formatter: (v) => v.toFixed(2)
                                }
                            },
                            {
                                label: 'Tolerance {{ $tolerancePercentage }}%', // ← UBAH
                                data: @js($kompetensiOriginalStandardRatings),
                                fill: true,
                                backgroundColor: '#fafa05', // ← SOLID
                                borderColor: '#e6d105',
                                pointBackgroundColor: '#e6d105',
                                pointBorderColor: '#fff',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                pointBorderWidth: 2,
                                datalabels: {
                                    display: false,
                                    color: '#000000',
                                    backgroundColor: '#fafa05',
                                    borderRadius: 4,
                                    padding: 6,
                                    font: {
                                        weight: 'bold',
                                        size: 10
                                    },
                                    anchor: 'center',
                                    align: 'center',
                                    offset: 6,
                                    formatter: (v) => v.toFixed(2)
                                }
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    color: colors.legend,
                                    font: {
                                        size: 14
                                    }
                                }
                            },
                            datalabels: {
                                display: true
                            }
                        },
                        scales: {
                            r: {
                                beginAtZero: true,
                                min: 0,
                                max: 5,
                                ticks: {
                                    display: false,
                                    stepSize: 1,
                                    color: colors.ticks,
                                    font: {
                                        size: 16,
                                        weight: 'bold'
                                    },
                                    backdropColor: 'transparent',
                                    showLabelBackdrop: false,
                                    z: 2 // Gunakan z-index 2 bukan 1000
                                },
                                pointLabels: {
                                    color: colors.pointLabels,
                                    font: {
                                        size: 12,
                                        weight: '600'
                                    },
                                    z: 3 // Gunakan z-index 3 bukan 1000
                                },
                                grid: {
                                    color: colors.grid,
                                    z: 1 // Tambahkan z-index 1
                                },
                                angleLines: {
                                    color: colors.angleLines,
                                    z: 1 // Tambahkan z-index 1
                                }
                            }
                        }
                    },
                    plugins: [{
                        id: 'shiftTicks',
                        afterDraw: (chart) => {
                            const {
                                ctx,
                                scales
                            } = chart;
                            const scale = scales.r;
                            const ticks = scale.ticks;
                            const yCenter = scale.yCenter;
                            const xCenter = scale.xCenter;

                            ctx.save();
                            ctx.font = `bold ${scale.options.ticks.font.size}px sans-serif`;
                            ctx.fillStyle = scale.options.ticks.color || '#000';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';

                            const offsetX = 10;
                            const offsetY = 0;

                            ticks.forEach((tick) => {
                                const value = tick.value;
                                const radius = scale.getDistanceFromCenterForValue(value);
                                const labelY = yCenter - radius - offsetY;
                                const labelX = xCenter + offsetX;
                                ctx.fillText(value, labelX, labelY);
                            });

                            ctx.restore();
                        }
                    }]
                });
            }

            // ========================================
            // GENERAL CHART - DARK MODE READY
            // ========================================
            function initializeGeneralChart() {
                if (window.generalChart_{{ $generalChartId }}) {
                    window.generalChart_{{ $generalChartId }}.destroy();
                }

                const ctxGeneral = document.getElementById('generalChart-{{ $generalChartId }}');
                if (!ctxGeneral) return;

                const colors = getColors();

                window.generalChart_{{ $generalChartId }} = new Chart(ctxGeneral.getContext('2d'), {
                    type: 'radar',
                    data: {
                        labels: @js($generalLabels),
                        datasets: [{
                                label: '{{ $participant->name }}',
                                data: @js($generalIndividualRatings),
                                fill: true,
                                backgroundColor: '#5db010', // Semi-transparan (green)
                                borderColor: '#8fd006',
                                pointBackgroundColor: '#8fd006',
                                pointBorderColor: '#fff',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                pointBorderWidth: 2,
                                datalabels: {
                                    display: false,
                                    color: '#000000',
                                    backgroundColor: '#5db010',
                                    borderRadius: 4,
                                    padding: 6,
                                    font: {
                                        weight: 'bold',
                                        size: 10
                                    },
                                    anchor: 'end',
                                    align: 'end',
                                    offset: 6,
                                    formatter: (v) => v.toFixed(2)
                                }
                            },
                            {
                                label: 'Standard', // ← UBAH
                                data: @js($generalStandardRatings),
                                backgroundColor: '#b50505', // Semi-transparan (red)
                                borderColor: '#b50505',
                                borderWidth: 2,
                                pointRadius: 3,
                                pointBackgroundColor: '#9a0404',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                fill: true,
                                datalabels: {
                                    display: false,
                                    color: '#FFFFFF',
                                    backgroundColor: '#b50505',
                                    borderRadius: 4,
                                    padding: 6,
                                    font: {
                                        weight: 'bold',
                                        size: 9
                                    },
                                    anchor: 'end',
                                    align: 'start',
                                    formatter: (v) => v.toFixed(2)
                                }
                            },
                            {
                                label: 'Tolerance {{ $tolerancePercentage }}%', // ← UBAH
                                data: @js($generalOriginalStandardRatings),
                                backgroundColor: '#fafa05', // Semi-transparan (yellow)
                                borderColor: '#e6d105',
                                pointBackgroundColor: '#e6d105',
                                pointBorderColor: '#fff',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                pointBorderWidth: 2,
                                datalabels: {
                                    display: false,
                                    color: '#000000',
                                    backgroundColor: '#fafa05',
                                    borderRadius: 4,
                                    padding: 6,
                                    font: {
                                        weight: 'bold',
                                        size: 10
                                    },
                                    anchor: 'center',
                                    align: 'center',
                                    offset: 6,
                                    formatter: (v) => v.toFixed(2)
                                }
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    color: colors.legend,
                                    font: {
                                        size: 14
                                    }
                                }
                            },
                            datalabels: {
                                display: true
                            }
                        },
                        scales: {
                            r: {
                                beginAtZero: true,
                                min: 0,
                                max: 5,
                                ticks: {
                                    display: false,
                                    stepSize: 1,
                                    color: colors.ticks,
                                    font: {
                                        size: 16,
                                        weight: 'bold'
                                    },
                                    backdropColor: 'transparent',
                                    showLabelBackdrop: false,
                                    z: 2 // Gunakan z-index 2 bukan 1000
                                },
                                pointLabels: {
                                    color: colors.pointLabels,
                                    font: {
                                        size: 12,
                                        weight: '600'
                                    },
                                    z: 3 // Gunakan z-index 3 bukan 1000
                                },
                                grid: {
                                    color: colors.grid,
                                    z: 1 // Tambahkan z-index 1
                                },
                                angleLines: {
                                    color: colors.angleLines,
                                    z: 1 // Tambahkan z-index 1
                                }
                            }
                        }
                    },
                    plugins: [{
                        id: 'shiftTicks',
                        afterDraw: (chart) => {
                            const {
                                ctx,
                                scales
                            } = chart;
                            const scale = scales.r;
                            const ticks = scale.ticks;
                            const yCenter = scale.yCenter;
                            const xCenter = scale.xCenter;

                            ctx.save();
                            ctx.font = `bold ${scale.options.ticks.font.size}px sans-serif`;
                            ctx.fillStyle = scale.options.ticks.color || '#000';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';

                            const offsetX = 10;
                            const offsetY = 0;

                            ticks.forEach((tick) => {
                                const value = tick.value;
                                const radius = scale.getDistanceFromCenterForValue(value);
                                const labelY = yCenter - radius - offsetY;
                                const labelX = xCenter + offsetX;
                                ctx.fillText(value, labelX, labelY);
                            });

                            ctx.restore();
                        }
                    }]
                });
            }

            // ========================================
            // LIVEWIRE LISTENERS - UPDATE TOLERANSI
            // ========================================
            function setupLivewireListeners() {
                function waitForLivewire(callback) {
                    if (window.Livewire) callback();
                    else setTimeout(() => waitForLivewire(callback), 100);
                }

                waitForLivewire(function() {
                    Livewire.on('chartDataUpdated', function(data) {
                        let chartData = Array.isArray(data) && data.length > 0 ? data[0] : data;
                        if (!chartData) return;

                        const tolerancePercentage = chartData.tolerance;
                        const colors = getColors();

                        // **POTENSI CHART**
                        if (chartData.potensi && window.potensiChart_{{ $potensiChartId }}) {
                            const chart = window.potensiChart_{{ $potensiChartId }};

                            // UBAH BAGIAN INI:
                            chart.data.datasets[0].data = chartData.potensi
                                .individualRatings; // Peserta
                            chart.data.datasets[1].label = 'Standard'; // ← UBAH label
                            chart.data.datasets[1].data = chartData.potensi
                                .standardRatings; // Standard
                            chart.data.datasets[2].label =
                                `Tolerance ${tolerancePercentage}%`; // ← UBAH label
                            chart.data.datasets[2].data = chartData.potensi
                                .originalStandardRatings; // Tolerance

                            chart.options.scales.r.ticks.color = colors.ticks;
                            chart.options.scales.r.pointLabels.color = colors.pointLabels;
                            chart.options.scales.r.grid.color = colors.grid;
                            chart.options.scales.r.angleLines.color = colors.grid;
                            chart.options.plugins.legend.labels.color = colors.legend;

                            chart.update('active');
                        }

                        // **KOMPETENSI CHART**
                        if (chartData.kompetensi && window.kompetensiChart_{{ $kompetensiChartId }}) {
                            const chart = window.kompetensiChart_{{ $kompetensiChartId }};

                            // UBAH BAGIAN INI:
                            chart.data.datasets[0].data = chartData.kompetensi.individualRatings;
                            chart.data.datasets[1].label = 'Standard'; // ← UBAH label
                            chart.data.datasets[1].data = chartData.kompetensi.standardRatings;
                            chart.data.datasets[2].label =
                                `Tolerance ${tolerancePercentage}%`; // ← UBAH label
                            chart.data.datasets[2].data = chartData.kompetensi.originalStandardRatings;

                            chart.options.scales.r.ticks.color = colors.ticks;
                            chart.options.scales.r.pointLabels.color = colors.pointLabels;
                            chart.options.scales.r.grid.color = colors.grid;
                            chart.options.scales.r.angleLines.color = colors.grid;
                            chart.options.plugins.legend.labels.color = colors.legend;

                            chart.update('active');
                        }

                        // **GENERAL CHART**
                        if (chartData.general && window.generalChart_{{ $generalChartId }}) {
                            const chart = window.generalChart_{{ $generalChartId }};

                            // UBAH BAGIAN INI:
                            chart.data.datasets[0].data = chartData.general.individualRatings;
                            chart.data.datasets[1].label = 'Standard'; // ← UBAH label
                            chart.data.datasets[1].data = chartData.general.standardRatings;
                            chart.data.datasets[2].label =
                                `Tolerance ${tolerancePercentage}%`; // ← UBAH label
                            chart.data.datasets[2].data = chartData.general.originalStandardRatings;

                            chart.options.scales.r.ticks.color = colors.ticks;
                            chart.options.scales.r.pointLabels.color = colors.pointLabels;
                            chart.options.scales.r.grid.color = colors.grid;
                            chart.options.scales.r.angleLines.color = colors.grid;
                            chart.options.plugins.legend.labels.color = colors.legend;

                            chart.update('active');
                        }
                    });
                });
            }

            // 🌙 DARK MODE LISTENER
            new MutationObserver(() => {
                const colors = getColors();

                [window.potensiChart_{{ $potensiChartId }},
                    window.kompetensiChart_{{ $kompetensiChartId }},
                    window.generalChart_{{ $generalChartId }}
                ].forEach(chart => {
                    if (chart) {
                        chart.options.scales.r.ticks.color = colors.ticks;
                        chart.options.scales.r.pointLabels.color = colors.pointLabels;
                        chart.options.scales.r.grid.color = colors.grid;
                        chart.options.scales.r.angleLines.color = colors.grid;
                        chart.options.plugins.legend.labels.color = colors.legend;
                        chart.update('active');
                    }
                });
            }).observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class']
            });
        })();
    </script>
</div>
