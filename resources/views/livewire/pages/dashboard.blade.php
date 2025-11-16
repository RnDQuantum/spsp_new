<div>
    @if (session('force_reload'))
        <script>
            window.location.reload();
        </script>
    @endif
    <div class="bg-white dark:bg-gray-900 mx-auto my-8 shadow-lg dark:shadow-gray-800/50 overflow-hidden"
        style="max-width: 1400px;">
        <!-- Header - DARK MODE READY -->
        <div class="border-b-4 border-black dark:border-gray-300 py-3 bg-gray-300 dark:bg-gray-600">
            <h1 class="text-center text-lg font-bold uppercase tracking-wide text-gray-900 dark:text-gray-100">
                SPIDER PLOT ANALYSIS - DASHBOARD
            </h1>
            @if ($participant)
                <p class="text-center text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">
                    {{ $participant->name }}
                </p>
                <p class="text-center text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">
                    {{ $participant->assessmentEvent->name }}
                </p>
                <p class="text-center text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">
                    {{ $participant->positionFormation->name }} - {{ $participant->positionFormation->template->name }}
                </p>
            @else
                <p class="text-center text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">
                    Tampilan Standar Proyek
                </p>
            @endif
        </div>

        <!-- Filters Section - DARK MODE READY -->
        <div class="p-6 bg-gray-50 dark:bg-gray-800 border-b-2 border-gray-200 dark:border-gray-600">
            <div class="max-w-6xl mx-auto space-y-4">
                <!-- Event Selector -->
                @livewire('components.event-selector', ['showLabel' => true])

                <!-- Position Selector -->
                @livewire('components.position-selector', ['showLabel' => true])

                <!-- Participant Selector -->
                @livewire('components.participant-selector', ['showLabel' => true])
            </div>
        </div>

        <!-- Tolerance Selector Component -->
        @if (count($allAspectsData) > 0)
            @php
                $summary = $this->getPassingSummary();
            @endphp
            @livewire('components.tolerance-selector', [
                'passing' => $summary['passing'],
                'total' => $summary['total'],
            ])
        @endif

        <!-- Charts Grid - DARK MODE READY -->
        @if (count($allAspectsData) > 0)
            <div class="p-6">
                <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-gray-100 mb-8">Static Pribadi Spider
                    Plot (SPSP)</h1>

                <!-- Charts - Vertical Layout -->
                <div class="space-y-6 mt-8">
                    <!-- Chart Potensi (Pentagon) -->
                    @if (count($potensiLabels) > 0)
                        <div
                            class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg dark:shadow-gray-700/50 border border-gray-200 dark:border-gray-600">
                            <h3 class="text-lg text-center font-semibold text-gray-800 dark:text-gray-100 mb-4">
                                Potential Mapping (Rating)
                            </h3>
                            <div class="relative" style="height: 600px;" wire:ignore>
                                <canvas id="potensiChart-{{ $potensiChartId }}"></canvas>
                            </div>
                        </div>
                    @endif

                    <!-- Chart Kompetensi (Nonagon) -->
                    @if (count($kompetensiLabels) > 0)
                        <div
                            class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg dark:shadow-gray-700/50 border border-gray-200 dark:border-gray-600">
                            <h3 class="text-lg text-center font-semibold text-gray-800 dark:text-gray-100 mb-4">
                                Managerial Potency Mapping
                                (Rating)
                            </h3>
                            <div class="relative" style="height: 600px;" wire:ignore>
                                <canvas id="kompetensiChart-{{ $kompetensiChartId }}"></canvas>
                            </div>
                        </div>
                    @endif

                    <!-- Chart General (Tetradecagon) -->
                    @if (count($generalLabels) > 0)
                        <div
                            class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg dark:shadow-gray-700/50 border border-gray-200 dark:border-gray-600">
                            <h3 class="text-lg text-center font-semibold text-gray-800 dark:text-gray-100 mb-4">General
                                Mapping (Rating)
                            </h3>
                            <div class="relative" style="height: 600px;" wire:ignore>
                                <canvas id="generalChart-{{ $generalChartId }}"></canvas>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="p-6">
                <div class="text-center text-gray-500 dark:text-gray-400">
                    <p class="text-lg">Silakan pilih Event dan Jabatan untuk melihat data standar.</p>
                    <p class="text-sm mt-2">Pilih Peserta untuk melihat perbandingan dengan standar.</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Chart Scripts - DARK MODE READY -->
    @if (count($allAspectsData) > 0)
        @push('scripts')
            <script>
                (function() {
                    'use strict';

                    // ========================================
                    // STEP 1: DEFINE ALL CONSTANTS FIRST
                    // ========================================

                    // Dark mode colors
                    const darkModeColors = {
                        background: '#1f2937', // gray-800
                        text: '#ffffff', // gray-50
                        grid: '#898989', // gray-600
                        pointLabels: '#d1d5db', // gray-300
                        legend: '#ffffff', // gray-50
                    };

                    // Light mode colors (default)
                    const lightModeColors = {
                        background: '#ffffff',
                        text: '#000000', // gray-900
                        grid: '#898989', // gray-300
                        pointLabels: '#000000', // gray-900
                        legend: '#000000', // gray-900
                    };

                    // Get current theme
                    function getCurrentTheme() {
                        return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
                    }

                    // ========================================
                    // STEP 2: UTILITY FUNCTIONS
                    // ========================================

                    // Wait for Chart.js to be available
                    function waitForChartJs(callback) {
                        if (typeof Chart !== 'undefined') {
                            callback();
                        } else {
                            setTimeout(() => waitForChartJs(callback), 50);
                        }
                    }

                    function waitForLivewire(callback) {
                        if (window.Livewire) {
                            callback();
                        } else {
                            setTimeout(() => waitForLivewire(callback), 100);
                        }
                    }

                    // Cleanup function to destroy all charts
                    function cleanupCharts() {
                        console.log('[Dashboard] Cleaning up charts...');
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
                    // POTENSI CHART - URUTAN: PESERTA → TOLERANSI → STANDARD
                    // ========================================
                    function initializePotensiChart() {
                        // Destroy existing chart if exists
                        if (window.potensiChart_{{ $potensiChartId }}) {
                            window.potensiChart_{{ $potensiChartId }}.destroy();
                        }

                        const ctxPotensi = document.getElementById('potensiChart-{{ $potensiChartId }}');
                        if (!ctxPotensi) return;

                        const hasParticipantData = @js($participant !== null);
                        const theme = getCurrentTheme();
                        const colors = theme === 'dark' ? darkModeColors : lightModeColors;

                        window.potensiChart_{{ $potensiChartId }} = new Chart(ctxPotensi.getContext('2d'), {
                            type: 'radar',
                            data: {
                                labels: @js($potensiLabels),
                                datasets: hasParticipantData ? [{
                                        // === LAYER 1: PESERTA (PASTI BAWAH) ===
                                        label: @js($participant ? $participant->name : 'Peserta'),
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
                                            display: false,
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
                                        // === LAYER 2: STANDARD (TENGAH) ===
                                        label: 'Standard', // UBAH DARI: 'Tolerance {{ $tolerancePercentage }}%'
                                        data: @js($potensiStandardRatings),
                                        borderColor: '#b50505', // MERAH SOLID
                                        backgroundColor: '#b50505',
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
                                        // === LAYER 3: TOLERANCE (PASTI ATAS) ===
                                        label: 'Tolerance {{ $tolerancePercentage }}%', // UBAH DARI: 'Standard'
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
                                            display: false,
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
                                ] : [
                                    // Standar (hijau) - tidak ada perubahan
                                    {
                                        label: 'Standard',
                                        data: @js($potensiStandardRatings),
                                        fill: true,
                                        backgroundColor: '#5db010', // HIJAU SOLID
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
                                    },
                                    // Toleransi (kuning) - tidak ada perubahan
                                    {
                                        label: 'Tolerance {{ $tolerancePercentage }}%',
                                        data: @js($potensiOriginalStandardRatings),
                                        fill: true,
                                        backgroundColor: '#fafa05', // KUNING SOLID
                                        borderColor: '#e6d105',
                                        pointBackgroundColor: '#e6d105',
                                        pointBorderColor: '#fff',
                                        borderWidth: 2,
                                        pointRadius: 3,
                                        pointBorderWidth: 2,
                                        datalabels: {
                                            display: false,
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
                                                size: 9
                                            },
                                            anchor: 'end',
                                            align: 'start',
                                            offset: 0,
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
                                            display: false,
                                            stepSize: 1,
                                            color: colors.text,
                                            font: {
                                                size: 16
                                            },
                                            backdropColor: 'transparent',
                                            showLabelBackdrop: false,
                                            z: 2
                                        },
                                        grid: {
                                            color: colors.grid,
                                            z: 1
                                        },
                                        pointLabels: {
                                            color: colors.pointLabels,
                                            font: {
                                                size: 16
                                            },
                                            z: 3
                                        },
                                        angleLines: {
                                            color: colors.grid,
                                            z: 1
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'top',
                                        labels: {
                                            color: colors.legend,
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
                                maintainAspectRatio: false,
                                backgroundColor: colors.background
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
                                    ctx.font = scale.options.ticks.font.size + 'px sans-serif';
                                    ctx.fillStyle = scale.options.ticks.color || '#000';
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle';

                                    // Geser sedikit ke kanan (misalnya +5)
                                    const offsetX = 10;
                                    const offsetY = 0; // tetap tidak digeser ke atas/bawah

                                    ticks.forEach((tick) => {
                                        const value = tick.value;
                                        const radius = scale.getDistanceFromCenterForValue(value);
                                        const labelY = yCenter - radius - offsetY;
                                        const labelX = xCenter + offsetX; // geser sedikit ke kanan
                                        ctx.fillText(value, labelX, labelY);
                                    });

                                    ctx.restore();
                                }
                            }]

                        });
                    }

                    // ========================================
                    // KOMPETENSI CHART - URUTAN SAMA
                    // ========================================
                    function initializeKompetensiChart() {
                        if (window.kompetensiChart_{{ $kompetensiChartId }}) {
                            window.kompetensiChart_{{ $kompetensiChartId }}.destroy();
                        }

                        const ctxKompetensi = document.getElementById('kompetensiChart-{{ $kompetensiChartId }}');
                        if (!ctxKompetensi) return;

                        const hasParticipantData = @js($participant !== null);
                        const theme = getCurrentTheme();
                        const colors = theme === 'dark' ? darkModeColors : lightModeColors;

                        window.kompetensiChart_{{ $kompetensiChartId }} = new Chart(ctxKompetensi.getContext('2d'), {
                            type: 'radar',
                            data: {
                                labels: @js($kompetensiLabels),
                                datasets: hasParticipantData ? [{
                                    // Peserta (hijau)
                                    label: @js($participant ? $participant->name : 'Peserta'),
                                    data: @js($kompetensiIndividualRatings),
                                    fill: true,
                                    backgroundColor: '#5db010',
                                    borderColor: '#8fd006',
                                    pointBackgroundColor: '#8fd006',
                                    pointBorderColor: '#fff',
                                    pointHoverBackgroundColor: '#fff',
                                    pointHoverBorderColor: '#8fd006',
                                    borderWidth: 2.5,
                                    pointRadius: 4,
                                    pointBorderWidth: 2,
                                    datalabels: {
                                        display: false,
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
                                }, {
                                    // Standard (merah) - PERUBAHAN LABEL
                                    label: 'Standard', // UBAH DARI: 'Tolerance {{ $tolerancePercentage }}%'
                                    data: @js($kompetensiStandardRatings),
                                    borderColor: '#b50505',
                                    backgroundColor: '#b50505',
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
                                }, {
                                    // Tolerance (kuning) - PERUBAHAN LABEL
                                    label: 'Tolerance {{ $tolerancePercentage }}%', // UBAH DARI: 'Standard'
                                    data: @js($kompetensiOriginalStandardRatings),
                                    fill: true,
                                    backgroundColor: '#fafa05',
                                    borderColor: '#e6d105',
                                    pointBackgroundColor: '#e6d105',
                                    pointBorderColor: '#fff',
                                    pointHoverBackgroundColor: '#fff',
                                    pointHoverBorderColor: '#e6d105',
                                    borderWidth: 2.5,
                                    pointRadius: 4,
                                    pointBorderWidth: 2,
                                    datalabels: {
                                        display: false,
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
                                }] : [
                                    // Standar (hijau) - tidak ada perubahan
                                    {
                                        label: 'Standard',
                                        data: @js($kompetensiStandardRatings),
                                        fill: true,
                                        backgroundColor: '#5db010', // HIJAU SOLID
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
                                    },
                                    // Toleransi (kuning) - tidak ada perubahan
                                    {
                                        label: 'Tolerance {{ $tolerancePercentage }}%',
                                        data: @js($kompetensiOriginalStandardRatings),
                                        fill: true,
                                        backgroundColor: '#fafa05', // KUNING SOLID
                                        borderColor: '#e6d105',
                                        pointBackgroundColor: '#e6d105',
                                        pointBorderColor: '#fff',
                                        borderWidth: 2,
                                        pointRadius: 3,
                                        pointBorderWidth: 2,
                                        datalabels: {
                                            display: false,
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
                                                size: 9
                                            },
                                            anchor: 'end',
                                            align: 'start',
                                            offset: 0,
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
                                            display: false,
                                            stepSize: 1,
                                            color: colors.text,
                                            font: {
                                                size: 16
                                            },
                                            backdropColor: 'transparent',
                                            showLabelBackdrop: false,
                                            z: 2
                                        },
                                        grid: {
                                            color: colors.grid,
                                            z: 1
                                        },
                                        pointLabels: {
                                            color: colors.pointLabels,
                                            font: {
                                                size: 16
                                            },
                                            z: 3
                                        },
                                        angleLines: {
                                            color: colors.grid,
                                            z: 1
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'top',
                                        labels: {
                                            color: colors.legend,
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
                                maintainAspectRatio: false,
                                backgroundColor: colors.background
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
                                    ctx.font = scale.options.ticks.font.size + 'px sans-serif';
                                    ctx.fillStyle = scale.options.ticks.color || '#000';
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle';

                                    // Geser sedikit ke kanan (misalnya +5)
                                    const offsetX = 10;
                                    const offsetY = 0; // tetap tidak digeser ke atas/bawah

                                    ticks.forEach((tick) => {
                                        const value = tick.value;
                                        const radius = scale.getDistanceFromCenterForValue(value);
                                        const labelY = yCenter - radius - offsetY;
                                        const labelX = xCenter + offsetX; // geser sedikit ke kanan
                                        ctx.fillText(value, labelX, labelY);
                                    });

                                    ctx.restore();
                                }
                            }]
                        });
                    }

                    // ========================================
                    // GENERAL CHART - URUTAN SAMA
                    // ========================================
                    function initializeGeneralChart() {
                        if (window.generalChart_{{ $generalChartId }}) {
                            window.generalChart_{{ $generalChartId }}.destroy();
                        }

                        const ctxGeneral = document.getElementById('generalChart-{{ $generalChartId }}');
                        if (!ctxGeneral) return;

                        const hasParticipantData = @js($participant !== null);
                        const theme = getCurrentTheme();
                        const colors = theme === 'dark' ? darkModeColors : lightModeColors;

                        window.generalChart_{{ $generalChartId }} = new Chart(ctxGeneral.getContext('2d'), {
                            type: 'radar',
                            data: {
                                labels: @js($generalLabels),
                                datasets: hasParticipantData ? [{
                                    // Peserta (hijau)
                                    label: @js($participant ? $participant->name : 'Peserta'),
                                    data: @js($generalIndividualRatings),
                                    fill: true,
                                    backgroundColor: '#5db010',
                                    borderColor: '#8fd006',
                                    pointBackgroundColor: '#8fd006',
                                    pointBorderColor: '#fff',
                                    pointHoverBackgroundColor: '#fff',
                                    pointHoverBorderColor: '#8fd006',
                                    borderWidth: 2.5,
                                    pointRadius: 4,
                                    pointBorderWidth: 2,
                                    datalabels: {
                                        display: false,
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
                                }, {
                                    // Standard (merah) - PERUBAHAN LABEL
                                    label: 'Standard', // UBAH DARI: 'Tolerance {{ $tolerancePercentage }}%'
                                    data: @js($generalStandardRatings),
                                    borderColor: '#b50505',
                                    backgroundColor: '#b50505',
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
                                }, {
                                    // Tolerance (kuning) - PERUBAHAN LABEL
                                    label: 'Tolerance {{ $tolerancePercentage }}%', // UBAH DARI: 'Standard'
                                    data: @js($generalOriginalStandardRatings),
                                    fill: true,
                                    backgroundColor: '#fafa05',
                                    borderColor: '#e6d105',
                                    pointBackgroundColor: '#e6d105',
                                    pointBorderColor: '#fff',
                                    pointHoverBackgroundColor: '#fff',
                                    pointHoverBorderColor: '#e6d105',
                                    borderWidth: 2.5,
                                    pointRadius: 4,
                                    pointBorderWidth: 2,
                                    datalabels: {
                                        display: false,
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
                                }] : [
                                    // Standar (hijau) - tidak ada perubahan
                                    {
                                        label: 'Standard',
                                        data: @js($generalStandardRatings),
                                        fill: true,
                                        backgroundColor: '#5db010', // HIJAU SOLID
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
                                    },
                                    // Toleransi (kuning) - tidak ada perubahan
                                    {
                                        label: 'Tolerance {{ $tolerancePercentage }}%',
                                        data: @js($generalOriginalStandardRatings),
                                        fill: true,
                                        backgroundColor: '#fafa05', // KUNING SOLID
                                        borderColor: '#e6d105',
                                        pointBackgroundColor: '#e6d105',
                                        pointBorderColor: '#fff',
                                        borderWidth: 2,
                                        pointRadius: 3,
                                        pointBorderWidth: 2,
                                        datalabels: {
                                            display: false,
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
                                                size: 9
                                            },
                                            anchor: 'end',
                                            align: 'start',
                                            offset: 0,
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
                                            display: false,
                                            stepSize: 1,
                                            color: colors.text,
                                            font: {
                                                size: 16
                                            },
                                            backdropColor: 'transparent',
                                            showLabelBackdrop: false,
                                            z: 2
                                        },
                                        grid: {
                                            color: colors.grid,
                                            z: 1
                                        },
                                        pointLabels: {
                                            color: colors.pointLabels,
                                            font: {
                                                size: 16
                                            },
                                            z: 3
                                        },
                                        angleLines: {
                                            color: colors.grid,
                                            z: 1
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'top',
                                        labels: {
                                            color: colors.legend,
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
                                maintainAspectRatio: false,
                                backgroundColor: colors.background
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
                                    ctx.font = scale.options.ticks.font.size + 'px sans-serif';
                                    ctx.fillStyle = scale.options.ticks.color || '#000';
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle';

                                    // Geser sedikit ke kanan (misalnya +5)
                                    const offsetX = 10;
                                    const offsetY = 0; // tetap tidak digeser ke atas/bawah

                                    ticks.forEach((tick) => {
                                        const value = tick.value;
                                        const radius = scale.getDistanceFromCenterForValue(value);
                                        const labelY = yCenter - radius - offsetY;
                                        const labelX = xCenter + offsetX; // geser sedikit ke kanan
                                        ctx.fillText(value, labelX, labelY);
                                    });

                                    ctx.restore();
                                }
                            }]
                        });
                    }

                    // ========================================
                    // DYNAMIC CHART INITIALIZATION WITH DATA
                    // ========================================
                    function initializePotensiChartWithData(data, hasParticipant, tolerancePercentage, participantName) {
                        const ctxPotensi = document.getElementById('potensiChart-{{ $potensiChartId }}');
                        if (!ctxPotensi) return;

                        const theme = getCurrentTheme();
                        const colors = theme === 'dark' ? darkModeColors : lightModeColors;

                        const datasets = hasParticipant ? [
                            // Peserta (hijau)
                            {
                                label: participantName || 'Peserta',
                                data: data.individualRatings,
                                fill: true,
                                backgroundColor: '#5db010',
                                borderColor: '#8fd006',
                                pointBackgroundColor: '#8fd006',
                                pointBorderColor: '#fff',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                pointBorderWidth: 2,
                                datalabels: {
                                    display: false
                                }
                            },
                            // Standard (merah)
                            {
                                label: 'Standard',
                                data: data.standardRatings,
                                borderColor: '#b50505',
                                backgroundColor: '#b50505',
                                borderWidth: 2,
                                pointRadius: 3,
                                pointBackgroundColor: '#9a0404',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                fill: true,
                                datalabels: {
                                    display: false
                                }
                            },
                            // Tolerance (kuning)
                            {
                                label: `Tolerance ${tolerancePercentage}%`,
                                data: data.originalStandardRatings,
                                fill: true,
                                backgroundColor: '#fafa05',
                                borderColor: '#e6d105',
                                pointBackgroundColor: '#e6d105',
                                pointBorderColor: '#fff',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                pointBorderWidth: 2,
                                datalabels: {
                                    display: false
                                }
                            }
                        ] : [
                            // Standard (hijau)
                            {
                                label: 'Standard',
                                data: data.standardRatings,
                                fill: true,
                                backgroundColor: '#5db010',
                                borderColor: '#8fd006',
                                pointBackgroundColor: '#8fd006',
                                pointBorderColor: '#fff',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                pointBorderWidth: 2,
                                datalabels: {
                                    display: false
                                }
                            },
                            // Tolerance (kuning)
                            {
                                label: `Tolerance ${tolerancePercentage}%`,
                                data: data.originalStandardRatings,
                                fill: true,
                                backgroundColor: '#fafa05',
                                borderColor: '#e6d105',
                                pointBackgroundColor: '#e6d105',
                                pointBorderColor: '#fff',
                                borderWidth: 2,
                                pointRadius: 3,
                                pointBorderWidth: 2,
                                datalabels: {
                                    display: false
                                }
                            }
                        ];

                        window.potensiChart_{{ $potensiChartId }} = new Chart(ctxPotensi.getContext('2d'), {
                            type: 'radar',
                            data: {
                                labels: data.labels,
                                datasets: datasets
                            },
                            options: {
                                scales: {
                                    r: {
                                        beginAtZero: true,
                                        min: 0,
                                        max: 5,
                                        ticks: {
                                            display: false,
                                            stepSize: 1, // ✅ FIX 1: Tambah stepSize
                                            color: colors.text,
                                            font: {
                                                size: 16
                                            },
                                            backdropColor: 'transparent',
                                            showLabelBackdrop: false,
                                            z: 2
                                        },
                                        grid: {
                                            color: colors.grid,
                                            z: 1
                                            // ✅ FIX 2: HAPUS circular: true agar chart TEGAS
                                        },
                                        pointLabels: {
                                            color: colors.pointLabels,
                                            font: {
                                                size: 16
                                            },
                                            z: 3
                                        },
                                        angleLines: {
                                            color: colors.grid,
                                            z: 1
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'top',
                                        labels: {
                                            color: colors.legend,
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
                                maintainAspectRatio: false,
                                backgroundColor: colors.background
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
                                    ctx.font = scale.options.ticks.font.size + 'px sans-serif';
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

                    function initializeKompetensiChartWithData(data, hasParticipant, tolerancePercentage, participantName) {
                        const ctxKompetensi = document.getElementById('kompetensiChart-{{ $kompetensiChartId }}');
                        if (!ctxKompetensi) return;

                        const theme = getCurrentTheme();
                        const colors = theme === 'dark' ? darkModeColors : lightModeColors;

                        const datasets = hasParticipant ? [{
                                label: participantName || 'Peserta',
                                data: data.individualRatings,
                                fill: true,
                                backgroundColor: '#5db010',
                                borderColor: '#8fd006',
                                pointBackgroundColor: '#8fd006',
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: '#8fd006',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                pointBorderWidth: 2,
                                datalabels: {
                                    display: false
                                }
                            },
                            {
                                label: 'Standard',
                                data: data.standardRatings,
                                borderColor: '#b50505',
                                backgroundColor: '#b50505',
                                borderWidth: 2,
                                pointRadius: 3,
                                pointBackgroundColor: '#9a0404',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                fill: true,
                                datalabels: {
                                    display: false
                                }
                            },
                            {
                                label: `Tolerance ${tolerancePercentage}%`,
                                data: data.originalStandardRatings,
                                fill: true,
                                backgroundColor: '#fafa05',
                                borderColor: '#e6d105',
                                pointBackgroundColor: '#e6d105',
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: '#e6d105',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                pointBorderWidth: 2,
                                datalabels: {
                                    display: false
                                }
                            }
                        ] : [{
                                label: 'Standard',
                                data: data.standardRatings,
                                fill: true,
                                backgroundColor: '#5db010',
                                borderColor: '#8fd006',
                                pointBackgroundColor: '#8fd006',
                                pointBorderColor: '#fff',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                pointBorderWidth: 2,
                                datalabels: {
                                    display: false
                                }
                            },
                            {
                                label: `Tolerance ${tolerancePercentage}%`,
                                data: data.originalStandardRatings,
                                fill: true,
                                backgroundColor: '#fafa05',
                                borderColor: '#e6d105',
                                pointBackgroundColor: '#e6d105',
                                pointBorderColor: '#fff',
                                borderWidth: 2,
                                pointRadius: 3,
                                pointBorderWidth: 2,
                                datalabels: {
                                    display: false
                                }
                            }
                        ];

                        window.kompetensiChart_{{ $kompetensiChartId }} = new Chart(ctxKompetensi.getContext('2d'), {
                            type: 'radar',
                            data: {
                                labels: data.labels,
                                datasets: datasets
                            },
                            options: {
                                scales: {
                                    r: {
                                        beginAtZero: true,
                                        min: 0,
                                        max: 5,
                                        ticks: {
                                            display: false,
                                            stepSize: 1, // ✅ FIX 1: Tambah stepSize
                                            color: colors.text,
                                            font: {
                                                size: 16
                                            },
                                            backdropColor: 'transparent',
                                            showLabelBackdrop: false,
                                            z: 2
                                        },
                                        grid: {
                                            color: colors.grid,
                                            z: 1
                                            // ✅ FIX 2: HAPUS circular: true
                                        },
                                        pointLabels: {
                                            color: colors.pointLabels,
                                            font: {
                                                size: 16
                                            },
                                            z: 3
                                        },
                                        angleLines: {
                                            color: colors.grid,
                                            z: 1
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: true,
                                        labels: {
                                            color: colors.legend,
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
                                maintainAspectRatio: false,
                                backgroundColor: colors.background
                            },
                            plugins: [{
                                id: 'shiftTicks',
                                afterDraw: (chart) => {
                                    const {
                                        ctx,
                                        scales
                                    } = chart;
                                    const scale = scales.r;
                                    ctx.save();
                                    ctx.font = '16px sans-serif';
                                    ctx.fillStyle = scale.options.ticks.color;
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle';

                                    const offsetX = 10;
                                    const offsetY = 0;

                                    scale.ticks.forEach(tick => {
                                        const radius = scale.getDistanceFromCenterForValue(tick
                                            .value);
                                        const labelY = scale.yCenter - radius - offsetY;
                                        const labelX = scale.xCenter + offsetX;
                                        ctx.fillText(tick.value, labelX, labelY);
                                    });
                                    ctx.restore();
                                }
                            }]
                        });
                    }

                    function initializeGeneralChartWithData(data, hasParticipant, tolerancePercentage, participantName) {
                        const ctxGeneral = document.getElementById('generalChart-{{ $generalChartId }}');
                        if (!ctxGeneral) return;

                        const theme = getCurrentTheme();
                        const colors = theme === 'dark' ? darkModeColors : lightModeColors;

                        const datasets = hasParticipant ? [{
                                label: participantName || 'Peserta',
                                data: data.individualRatings,
                                fill: true,
                                backgroundColor: '#5db010',
                                borderColor: '#8fd006',
                                pointBackgroundColor: '#8fd006',
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: '#8fd006',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                pointBorderWidth: 2,
                                datalabels: {
                                    display: false
                                }
                            },
                            {
                                label: 'Standard',
                                data: data.standardRatings,
                                borderColor: '#b50505',
                                backgroundColor: '#b50505',
                                borderWidth: 2,
                                pointRadius: 3,
                                pointBackgroundColor: '#9a0404',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                fill: true,
                                datalabels: {
                                    display: false
                                }
                            },
                            {
                                label: `Tolerance ${tolerancePercentage}%`,
                                data: data.originalStandardRatings,
                                fill: true,
                                backgroundColor: '#fafa05',
                                borderColor: '#e6d105',
                                pointBackgroundColor: '#e6d105',
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: '#e6d105',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                pointBorderWidth: 2,
                                datalabels: {
                                    display: false
                                }
                            }
                        ] : [{
                                label: 'Standard',
                                data: data.standardRatings,
                                fill: true,
                                backgroundColor: '#5db010',
                                borderColor: '#8fd006',
                                pointBackgroundColor: '#8fd006',
                                pointBorderColor: '#fff',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                pointBorderWidth: 2,
                                datalabels: {
                                    display: false
                                }
                            },
                            {
                                label: `Tolerance ${tolerancePercentage}%`,
                                data: data.originalStandardRatings,
                                fill: true,
                                backgroundColor: '#fafa05',
                                borderColor: '#e6d105',
                                pointBackgroundColor: '#e6d105',
                                pointBorderColor: '#fff',
                                borderWidth: 2,
                                pointRadius: 3,
                                pointBorderWidth: 2,
                                datalabels: {
                                    display: false
                                }
                            }
                        ];

                        window.generalChart_{{ $generalChartId }} = new Chart(ctxGeneral.getContext('2d'), {
                            type: 'radar',
                            data: {
                                labels: data.labels,
                                datasets: datasets
                            },
                            options: {
                                scales: {
                                    r: {
                                        beginAtZero: true,
                                        min: 0,
                                        max: 5,
                                        ticks: {
                                            display: false,
                                            stepSize: 1, // ✅ FIX 1: Tambah stepSize
                                            color: colors.text,
                                            font: {
                                                size: 16
                                            },
                                            backdropColor: 'transparent',
                                            showLabelBackdrop: false,
                                            z: 2
                                        },
                                        grid: {
                                            color: colors.grid,
                                            z: 1
                                            // ✅ FIX 2: HAPUS circular: true
                                        },
                                        pointLabels: {
                                            color: colors.pointLabels,
                                            font: {
                                                size: 16
                                            },
                                            z: 3
                                        },
                                        angleLines: {
                                            color: colors.grid,
                                            z: 1
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: true,
                                        labels: {
                                            color: colors.legend,
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
                                maintainAspectRatio: false,
                                backgroundColor: colors.background
                            },
                            plugins: [{
                                id: 'shiftTicks',
                                afterDraw: (chart) => {
                                    const {
                                        ctx,
                                        scales
                                    } = chart;
                                    const scale = scales.r;
                                    ctx.save();
                                    ctx.font = '16px sans-serif';
                                    ctx.fillStyle = scale.options.ticks.color;
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle';

                                    const offsetX = 10;
                                    const offsetY = 0;

                                    scale.ticks.forEach(tick => {
                                        const radius = scale.getDistanceFromCenterForValue(tick
                                            .value);
                                        const labelY = scale.yCenter - radius - offsetY;
                                        const labelX = scale.xCenter + offsetX;
                                        ctx.fillText(tick.value, labelX, labelY);
                                    });
                                    ctx.restore();
                                }
                            }]
                        });
                    }


                    // Store latest chart data globally for reinitialization
                    let latestChartData = null;

                    // ========================================
                    // LIVEWIRE LISTENERS - UPDATE CHARTS
                    // ========================================
                    function setupLivewireListeners() {
                        Livewire.on('chartDataUpdated', function(eventData) {
                            try {
                                const chartData = Array.isArray(eventData) && eventData.length > 0 ? eventData[0] :
                                    eventData;
                                if (!chartData) return;

                                console.log('Chart data updated:', chartData);

                                // Store latest data for potential reinitialization
                                latestChartData = chartData;

                                const hasParticipant = chartData.hasParticipant;
                                const participantName = chartData.participantName;
                                const tolerancePercentage = chartData.tolerancePercentage;

                                // Update Potensi Chart
                                if (chartData.potensi) {
                                    updateChart(
                                        window.potensiChart_{{ $potensiChartId }},
                                        chartData.potensi,
                                        hasParticipant,
                                        tolerancePercentage,
                                        participantName,
                                        'potensi'
                                    );
                                }

                                // Update Kompetensi Chart
                                if (chartData.kompetensi) {
                                    updateChart(
                                        window.kompetensiChart_{{ $kompetensiChartId }},
                                        chartData.kompetensi,
                                        hasParticipant,
                                        tolerancePercentage,
                                        participantName,
                                        'kompetensi'
                                    );
                                }

                                // Update General Chart
                                if (chartData.general) {
                                    updateChart(
                                        window.generalChart_{{ $generalChartId }},
                                        chartData.general,
                                        hasParticipant,
                                        tolerancePercentage,
                                        participantName,
                                        'general'
                                    );
                                }
                            } catch (e) {
                                console.error('Chart update error:', e, eventData);
                            }
                        });
                    }

                    // Helper function to reinitialize specific chart with new data
                    function reinitializeChartWithData(chartType, data, hasParticipant, tolerancePercentage,
                        participantName) {
                        console.log(`[${chartType}] Reinitializing chart with new dataset structure...`, {
                            hasParticipant,
                            participantName,
                            datasetCount: hasParticipant ? 3 : 2
                        });

                        if (chartType === 'potensi') {
                            if (window.potensiChart_{{ $potensiChartId }}) {
                                console.log('[potensi] Destroying old chart...');
                                window.potensiChart_{{ $potensiChartId }}.destroy();
                            }
                            initializePotensiChartWithData(data, hasParticipant, tolerancePercentage, participantName);
                            console.log('[potensi] New chart initialized with', hasParticipant ? 3 : 2, 'datasets');
                        } else if (chartType === 'kompetensi') {
                            if (window.kompetensiChart_{{ $kompetensiChartId }}) {
                                console.log('[kompetensi] Destroying old chart...');
                                window.kompetensiChart_{{ $kompetensiChartId }}.destroy();
                            }
                            initializeKompetensiChartWithData(data, hasParticipant, tolerancePercentage, participantName);
                            console.log('[kompetensi] New chart initialized with', hasParticipant ? 3 : 2, 'datasets');
                        } else if (chartType === 'general') {
                            if (window.generalChart_{{ $generalChartId }}) {
                                console.log('[general] Destroying old chart...');
                                window.generalChart_{{ $generalChartId }}.destroy();
                            }
                            initializeGeneralChartWithData(data, hasParticipant, tolerancePercentage, participantName);
                            console.log('[general] New chart initialized with', hasParticipant ? 3 : 2, 'datasets');
                        }
                    }

                    // Helper function to update chart in-place
                    function updateChart(chart, data, hasParticipant, tolerancePercentage, participantName, chartType) {
                        console.log(`[${chartType}] updateChart called`, {
                            hasChart: !!chart,
                            hasParticipant,
                            dataLabelsCount: data?.labels?.length
                        });

                        // Safety check: if chart not initialized, reinitialize
                        if (!chart || !chart.data) {
                            console.warn(`[${chartType}] Chart not initialized, reinitializing...`);
                            reinitializeChartWithData(chartType, data, hasParticipant, tolerancePercentage,
                                participantName);
                            return;
                        }

                        // Safety check: validate data
                        if (!data || !data.labels || !Array.isArray(data.labels)) {
                            console.error(`[${chartType}] Invalid chart data received:`, data);
                            return;
                        }

                        try {
                            // Check if dataset count changed (2 vs 3)
                            const currentDatasetCount = chart.data.datasets.length;
                            const requiredDatasetCount = hasParticipant ? 3 : 2;

                            console.log(`[${chartType}] Dataset check:`, {
                                current: currentDatasetCount,
                                required: requiredDatasetCount,
                                needsReinit: currentDatasetCount !== requiredDatasetCount
                            });

                            if (currentDatasetCount !== requiredDatasetCount) {
                                // Dataset count changed - must reinitialize chart
                                console.log(
                                    `[${chartType}] Dataset count changed (${currentDatasetCount} → ${requiredDatasetCount}), reinitializing...`
                                );
                                reinitializeChartWithData(chartType, data, hasParticipant, tolerancePercentage,
                                    participantName);
                                return;
                            }

                            // Update chart data (dataset count matches)
                            chart.data.labels = data.labels;

                            if (hasParticipant) {
                                // Update ketiga dataset (peserta, standar, toleransi)
                                if (chart.data.datasets[0]) {
                                    chart.data.datasets[0].label = participantName || 'Peserta';
                                    chart.data.datasets[0].data = data.individualRatings;
                                }
                                if (chart.data.datasets[1]) {
                                    chart.data.datasets[1].label = 'Standard';
                                    chart.data.datasets[1].data = data.standardRatings;
                                }
                                if (chart.data.datasets[2]) {
                                    chart.data.datasets[2].label = `Tolerance ${tolerancePercentage}%`;
                                    chart.data.datasets[2].data = data.originalStandardRatings;
                                }
                            } else {
                                // Update standar dan toleransi (tidak ada peserta)
                                if (chart.data.datasets[0]) {
                                    chart.data.datasets[0].label = 'Standard';
                                    chart.data.datasets[0].data = data.standardRatings;
                                }
                                if (chart.data.datasets[1]) {
                                    chart.data.datasets[1].label = `Tolerance ${tolerancePercentage}%`;
                                    chart.data.datasets[1].data = data.originalStandardRatings;
                                }
                            }

                            // Smooth update with animation
                            chart.update('active');
                            console.log(`${chartType} chart updated successfully`);
                        } catch (error) {
                            console.error(`Error updating ${chartType} chart:`, error);
                            // Fallback: reinitialize chart
                            console.log(`Attempting to reinitialize ${chartType} chart...`);
                            reinitializeChartWithData(chartType, data, hasParticipant, tolerancePercentage,
                                participantName);
                        }
                    }

                    // DARK MODE TOGGLE LISTENER
                    function setupDarkModeListener() {
                        const observer = new MutationObserver(function(mutations) {
                            mutations.forEach(function(mutation) {
                                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                                    const theme = getCurrentTheme();
                                    console.log('Theme changed to:', theme);

                                    // Reinitialize all charts with new theme
                                    initializePotensiChart();
                                    initializeKompetensiChart();
                                    initializeGeneralChart();
                                }
                            });
                        });

                        observer.observe(document.documentElement, {
                            attributes: true,
                            attributeFilter: ['class']
                        });
                    }

                    // ========================================
                    // INITIALIZATION & WIRE:NAVIGATE SUPPORT
                    // ========================================

                    // Initialize all charts
                    function initCharts() {
                        console.log('[Dashboard] Initializing charts...');
                        initializePotensiChart();
                        initializeKompetensiChart();
                        initializeGeneralChart();
                    }

                    // Main initialization function
                    function initDashboard() {
                        console.log('[Dashboard] Dashboard page loaded');

                        // Initialize charts when Chart.js is ready
                        waitForChartJs(initCharts);

                        // Setup Livewire event listeners
                        waitForLivewire(function() {
                            setupLivewireListeners();
                        });

                        // Setup dark mode listener
                        setupDarkModeListener();


                    }

                    // Run on initial page load
                    initDashboard();

                    // Support for wire:navigate - reinitialize when navigated to dashboard
                    document.addEventListener('livewire:navigated', function() {
                        // Check if we're on dashboard page by looking for chart elements
                        const isDashboardPage = document.getElementById('potensiChart-{{ $potensiChartId }}') !==
                            null;

                        if (isDashboardPage) {
                            console.log('[Dashboard] Navigated to dashboard page, reinitializing...');
                            initDashboard();
                        }
                    });

                    // Cleanup charts when navigating away
                    document.addEventListener('livewire:navigating', function() {
                        const isDashboardPage = document.getElementById('potensiChart-{{ $potensiChartId }}') !==
                            null;

                        if (isDashboardPage) {
                            console.log('[Dashboard] Navigating away from dashboard, cleaning up...');
                            cleanupCharts();
                        }
                    });
                })();
            </script>
        @endpush
    @endif
</div>
