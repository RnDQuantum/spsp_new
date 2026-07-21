<div class="max-w-[1400px] mx-auto p-3 md:p-4 font-sans text-primary-ink dark:text-neutral-100">
    <div class="bg-white dark:bg-[#171412] p-4 md:p-5 rounded-xl border border-warm-border dark:border-[#25211e] shadow-xs">

        {{-- Header Editorial Executive Journal --}}
        <div class="mb-4 pb-4 border-b border-warm-border dark:border-[#25211e]">
            <span class="font-mono-data text-accent-amber font-bold uppercase tracking-widest text-xs block mb-1">
                INDIVIDUAL REPORT / SPIDER PLOT
            </span>
            <h1 class="font-display text-xl md:text-2xl font-bold tracking-tight text-primary-ink dark:text-neutral-100">
                Analisa Spider Plot Profil Asesmen
            </h1>
            <div class="mt-2 flex flex-wrap gap-x-6 gap-y-2 text-xs font-mono-data text-primary-ink/75 dark:text-neutral-400">
                <span class="flex items-center gap-1.5"><i class="fa-regular fa-user text-accent-amber"></i> {{ $participant->name }}</span>
                <span class="flex items-center gap-1.5"><i class="fa-regular fa-calendar text-accent-amber"></i> {{ $participant->event->name }}</span>
                <span class="flex items-center gap-1.5"><i class="fa-regular fa-address-card text-accent-amber"></i> {{ $participant->positionFormation->name }} - {{ $participant->positionFormation->template->name }}</span>
            </div>
        </div>

        <!-- Tolerance Selector Component -->
        @php
            $summary = $this->getPassingSummary();
        @endphp
        @livewire('components.tolerance-selector', [
            'passing' => $summary['passing'],
            'total' => $summary['total'],
        ])

        {{-- Adjustment Indicators --}}
        <div
            class="px-6 py-3 bg-warm-ivory dark:bg-[#1f1b18] border-b border-warm-border dark:border-[#25211e] flex flex-wrap gap-2">
            <x-adjustment-indicator :template-id="$participant->positionFormation->template_id" category-code="potensi" size="sm"
                custom-label="Standar Potensi Disesuaikan" />
            <x-adjustment-indicator :template-id="$participant->positionFormation->template_id" category-code="kompetensi" size="sm"
                custom-label="Standar Kompetensi Disesuaikan" />
        </div>

        <!-- Charts Grid - DARK MODE READY -->
        <div class="p-8 bg-white dark:bg-[#171412]">
            <h2 class="text-center font-display text-xl font-bold text-primary-ink dark:text-neutral-100 mb-8">Static Pribadi Spider Plot (SPSP)</h2>

            <!-- Charts - Vertical Layout -->
            <div class="space-y-6 mt-8">
                <!-- Chart Potensi (Pentagon) -->
                <div class="bg-white dark:bg-[#171412] p-8 border border-warm-border dark:border-[#25211e] rounded-md shadow-xs"
                    wire:ignore>
                    <h3 class="text-center font-display text-lg font-bold text-primary-ink dark:text-neutral-100 mb-4">Potential Mapping (Rating)</h3>
                    <div class="relative" style="height: 600px;">
                        <canvas id="potensiChart-{{ $potensiChartId }}"></canvas>
                    </div>
                </div>

                <!-- Chart Kompetensi (Nonagon) -->
                <div class="bg-white dark:bg-[#171412] p-8 border border-warm-border dark:border-[#25211e] rounded-md shadow-xs"
                    wire:ignore>
                    <h3 class="text-center font-display text-lg font-bold text-primary-ink dark:text-neutral-100 mb-4">Managerial Competency Mapping (Rating)</h3>
                    <div class="relative" style="height: 600px;">
                        <canvas id="kompetensiChart-{{ $kompetensiChartId }}"></canvas>
                    </div>
                </div>

                <!-- Chart General (Tetradecagon) -->
                <div class="bg-white dark:bg-[#171412] p-8 border border-warm-border dark:border-[#25211e] rounded-md shadow-xs"
                    wire:ignore>
                    <h3 class="text-center font-display text-lg font-bold text-primary-ink dark:text-neutral-100 mb-4">General Mapping (Rating)</h3>
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
            // 🌙 DARK MODE COLORS - MASTER BENCHMARK
            const getColors = () => {
                const isDark = document.documentElement.classList.contains('dark');
                return {
                    grid: isDark ? 'rgba(255, 255, 255, 0.15)' : 'rgba(23, 20, 18, 0.15)',
                    angleLines: isDark ? 'rgba(255, 255, 255, 0.15)' : 'rgba(23, 20, 18, 0.15)',
                    ticks: isDark ? '#e5e5e5' : '#171412',
                    pointLabels: isDark ? '#e5e5e5' : '#171412',
                    legend: isDark ? '#e5e5e5' : '#171412'
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

            // Main initialization function
            function initSpiderPlots() {
                console.log('[Spider Plot] Initializing charts...');

                waitForChartJs(function() {
                    initializePotensiChart();
                    initializeKompetensiChart();
                    initializeGeneralChart();
                });

                setupLivewireListeners();

                // Setup dark mode listener only once
                if (!window.spiderPlotDarkModeSetup) {
                    setupDarkModeListener();
                    window.spiderPlotDarkModeSetup = true;
                }
            }

            // Cleanup function
            function cleanupSpiderCharts() {
                console.log('[Spider Plot] Cleaning up charts...');

                if (window.potensiChart_{{ $potensiChartId }}) {
                    window.potensiChart_{{ $potensiChartId }}.destroy();
                    window.potensiChart_{{ $potensiChartId }} = null;
                }
                if (window.kompetensiChart_{{ $kompetensiChartId }}) {
                    window.kompetensiChart_{{ $kompetensiChartId }}.destroy();
                    window.kompetensiChart_{{ $kompetensiChartId }} = null;
                }
                if (window.generalChart_{{ $generalChartId }}) {
                    window.generalChart_{{ $generalChartId }}.destroy();
                    window.generalChart_{{ $generalChartId }} = null;
                }
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
                                backgroundColor: '#5db010',
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
                                label: 'Standard',
                                data: @js($potensiStandardRatings),
                                backgroundColor: '#b50505',
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
                                label: 'Tolerance {{ $tolerancePercentage }}%',
                                data: @js($potensiOriginalStandardRatings),
                                fill: true,
                                backgroundColor: '#fafa05',
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
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    font: {
                                        size: 13,
                                        weight: '600',
                                        family: "'Instrument Sans', sans-serif"
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
                                        size: 15,
                                        weight: '600',
                                        family: "'Instrument Sans', sans-serif"
                                    },
                                    backdropColor: 'transparent',
                                    showLabelBackdrop: false,
                                    z: 2
                                },
                                pointLabels: {
                                    color: colors.pointLabels,
                                    font: {
                                        size: 14,
                                        weight: '600',
                                        family: "'Instrument Sans', sans-serif"
                                    },
                                    z: 3
                                },
                                grid: {
                                    color: colors.grid,
                                    z: 1
                                },
                                angleLines: {
                                    color: colors.angleLines,
                                    z: 1
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
                            ctx.font = "600 15px 'Instrument Sans', sans-serif";
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
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    font: {
                                        size: 13,
                                        weight: '600',
                                        family: "'Instrument Sans', sans-serif"
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
                                        size: 15,
                                        weight: '600',
                                        family: "'Instrument Sans', sans-serif"
                                    },
                                    backdropColor: 'transparent',
                                    showLabelBackdrop: false,
                                    z: 2
                                },
                                pointLabels: {
                                    color: colors.pointLabels,
                                    font: {
                                        size: 14,
                                        weight: '600',
                                        family: "'Instrument Sans', sans-serif"
                                    },
                                    z: 3
                                },
                                grid: {
                                    color: colors.grid,
                                    z: 1
                                },
                                angleLines: {
                                    color: colors.angleLines,
                                    z: 1
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
                            ctx.font = "600 15px 'Instrument Sans', sans-serif";
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
                                backgroundColor: '#5db010',
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
                                label: 'Standard',
                                data: @js($generalStandardRatings),
                                backgroundColor: '#b50505',
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
                                label: 'Tolerance {{ $tolerancePercentage }}%',
                                data: @js($generalOriginalStandardRatings),
                                backgroundColor: '#fafa05',
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
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    font: {
                                        size: 13,
                                        weight: '600',
                                        family: "'Instrument Sans', sans-serif"
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
                                        size: 15,
                                        weight: '600',
                                        family: "'Instrument Sans', sans-serif"
                                    },
                                    backdropColor: 'transparent',
                                    showLabelBackdrop: false,
                                    z: 2
                                },
                                pointLabels: {
                                    color: colors.pointLabels,
                                    font: {
                                        size: 14,
                                        weight: '600',
                                        family: "'Instrument Sans', sans-serif"
                                    },
                                    z: 3
                                },
                                grid: {
                                    color: colors.grid,
                                    z: 1
                                },
                                angleLines: {
                                    color: colors.angleLines,
                                    z: 1
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
                            ctx.font = "600 15px 'Instrument Sans', sans-serif";
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

            // 🌙 DARK MODE LISTENER SETUP
            function setupDarkModeListener() {
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
                            chart.options.scales.r.angleLines.color = colors.angleLines;
                            chart.options.plugins.legend.labels.color = colors.legend;
                            chart.update('active');
                        }
                    });
                }).observe(document.documentElement, {
                    attributes: true,
                    attributeFilter: ['class']
                });
            }

            // ========================================
            // INITIALIZATION & WIRE:NAVIGATE SUPPORT
            // ========================================

            // Initialize on first load
            initSpiderPlots();

            // Reinitialize when navigated to this page
            document.addEventListener('livewire:navigated', function() {
                const isSpiderPlotPage = document.getElementById('potensiChart-{{ $potensiChartId }}') !== null;

                if (isSpiderPlotPage) {
                    console.log('[Spider Plot] Navigated to page, reinitializing...');
                    initSpiderPlots();
                }
            });

            // Cleanup when navigating away
            document.addEventListener('livewire:navigating', function() {
                const isSpiderPlotPage = document.getElementById('potensiChart-{{ $potensiChartId }}') !== null;

                if (isSpiderPlotPage) {
                    console.log('[Spider Plot] Navigating away, cleaning up...');
                    cleanupSpiderCharts();
                }
            });
        })();
    </script>
    </div>
</div>
