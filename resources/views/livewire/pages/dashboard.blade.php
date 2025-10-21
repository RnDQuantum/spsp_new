<div>
    @if (session('force_reload'))
        <script>
            window.location.reload();
        </script>
    @endif
    <div class="bg-white mx-auto my-8 shadow overflow-hidden" style="max-width: 1400px;">
        <!-- Header -->
        <div class="border-b-4 border-black py-3 bg-sky-200">
            <h1 class="text-center text-lg font-bold uppercase tracking-wide text-gray-900">
                SPIDER PLOT ANALYSIS - DASHBOARD
            </h1>
            @if ($participant)
                <p class="text-center text-sm font-semibold text-gray-700 mt-1">
                    {{ $participant->name }}
                </p>
                <p class="text-center text-sm font-semibold text-gray-700 mt-1">
                    {{ $participant->assessmentEvent->name }}
                </p>
                <p class="text-center text-sm font-semibold text-gray-700 mt-1">
                    {{ $participant->positionFormation->name }} - {{ $participant->positionFormation->template->name }}
                </p>
            @else
                <p class="text-center text-sm font-semibold text-gray-700 mt-1">
                    Tampilan Standar Proyek
                </p>
            @endif
        </div>

        <!-- Filters Section -->
        <div class="p-6 bg-gray-50 border-b-2 border-gray-200">
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

        <!-- Charts Grid -->
        @if (count($allAspectsData) > 0)
            <div class="p-6">
                <h1 class="text-3xl text-center font-bold text-gray-800 mb-8">Static Pribadi Spider Plot (SPSP)</h1>

                <!-- Charts - Vertical Layout -->
                <div class="space-y-6 mt-8">
                    <!-- Chart Potensi (Pentagon) -->
                    @if (count($potensiLabels) > 0)
                        <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                            <h3 class="text-lg text-center font-semibold text-gray-800 mb-4">Potential Mapping (Rating)
                            </h3>
                            <div class="relative" style="height: 600px;" wire:ignore>
                                <canvas id="potensiChart-{{ $potensiChartId }}"></canvas>
                            </div>
                        </div>
                    @endif

                    <!-- Chart Kompetensi (Nonagon) -->
                    @if (count($kompetensiLabels) > 0)
                        <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                            <h3 class="text-lg text-center font-semibold text-gray-800 mb-4">Managerial Potency Mapping
                                (Rating)
                            </h3>
                            <div class="relative" style="height: 600px;" wire:ignore>
                                <canvas id="kompetensiChart-{{ $kompetensiChartId }}"></canvas>
                            </div>
                        </div>
                    @endif

                    <!-- Chart General (Tetradecagon) -->
                    @if (count($generalLabels) > 0)
                        <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                            <h3 class="text-lg text-center font-semibold text-gray-800 mb-4">General Mapping (Rating)
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
                <div class="text-center text-gray-500">
                    <p class="text-lg">Silakan pilih Event dan Jabatan untuk melihat data standar.</p>
                    <p class="text-sm mt-2">Pilih Peserta untuk melihat perbandingan dengan standar.</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Chart Scripts -->
    @if (count($allAspectsData) > 0)
        @push('scripts')
            <script>
                (function() {
                    // Wait for Chart.js to be available
                    function waitForChartJs(callback) {
                        if (typeof Chart !== 'undefined') {
                            callback();
                        } else {
                            setTimeout(() => waitForChartJs(callback), 50);
                        }
                    }

                    // Initialize all charts
                    function initCharts() {
                        console.log('Initializing charts...');
                        initializePotensiChart();
                        initializeKompetensiChart();
                        initializeGeneralChart();
                    }

                    function waitForLivewire(callback) {
                        if (window.Livewire) {
                            callback();
                        } else {
                            setTimeout(() => waitForLivewire(callback), 100);
                        }
                    }

                    // Initialize charts on page load
                    waitForChartJs(initCharts);

                    // Setup Livewire event listeners
                    waitForLivewire(function() {
                        setupLivewireListeners();
                    });

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
                                        // === LAYER 3: STANDARD (PASTI ATAS) ===
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
                                ] : [
                                    // Only show standard when no participant
                                    {
                                        label: 'Standard',
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
                        if (window.kompetensiChart_{{ $kompetensiChartId }}) {
                            window.kompetensiChart_{{ $kompetensiChartId }}.destroy();
                        }

                        const ctxKompetensi = document.getElementById('kompetensiChart-{{ $kompetensiChartId }}');
                        if (!ctxKompetensi) return;

                        const hasParticipantData = @js($participant !== null);

                        window.kompetensiChart_{{ $kompetensiChartId }} = new Chart(ctxKompetensi.getContext('2d'), {
                            type: 'radar',
                            data: {
                                labels: @js($kompetensiLabels),
                                datasets: hasParticipantData ? [{
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
                                        label: 'Tolerance {{ $tolerancePercentage }}%',
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
                                        label: 'Standard',
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
                                ] : [{
                                    label: 'Standard',
                                    data: @js($kompetensiOriginalStandardRatings),
                                    fill: true,
                                    backgroundColor: '#fafa05',
                                    borderColor: '#e6d105',
                                    pointBackgroundColor: '#e6d105',
                                    pointBorderColor: '#fff',
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

                        window.generalChart_{{ $generalChartId }} = new Chart(ctxGeneral.getContext('2d'), {
                            type: 'radar',
                            data: {
                                labels: @js($generalLabels),
                                datasets: hasParticipantData ? [{
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
                                        label: 'Tolerance {{ $tolerancePercentage }}%',
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
                                        label: 'Standard',
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
                                ] : [{
                                    label: 'Standard',
                                    data: @js($generalOriginalStandardRatings),
                                    fill: true,
                                    backgroundColor: '#fafa05',
                                    borderColor: '#e6d105',
                                    pointBackgroundColor: '#e6d105',
                                    pointBorderColor: '#fff',
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

                                const hasParticipant = chartData.hasParticipant;
                                const participantName = chartData.participantName;
                                const tolerancePercentage = chartData.tolerancePercentage;

                                // Update Potensi Chart
                                if (window.potensiChart_{{ $potensiChartId }} && chartData.potensi) {
                                    updateChart(
                                        window.potensiChart_{{ $potensiChartId }},
                                        chartData.potensi,
                                        hasParticipant,
                                        tolerancePercentage,
                                        participantName
                                    );
                                }

                                // Update Kompetensi Chart
                                if (window.kompetensiChart_{{ $kompetensiChartId }} && chartData.kompetensi) {
                                    updateChart(
                                        window.kompetensiChart_{{ $kompetensiChartId }},
                                        chartData.kompetensi,
                                        hasParticipant,
                                        tolerancePercentage,
                                        participantName
                                    );
                                }

                                // Update General Chart
                                if (window.generalChart_{{ $generalChartId }} && chartData.general) {
                                    updateChart(
                                        window.generalChart_{{ $generalChartId }},
                                        chartData.general,
                                        hasParticipant,
                                        tolerancePercentage,
                                        participantName
                                    );
                                }
                            } catch (e) {
                                console.error('Chart update error:', e, eventData);
                            }
                        });
                    }

                    // Helper function to update chart in-place
                    function updateChart(chart, data, hasParticipant, tolerancePercentage, participantName) {
                        chart.data.labels = data.labels;

                        if (hasParticipant) {
                            // Update all 3 datasets (participant, tolerance, standard)
                            chart.data.datasets[0].label = participantName || 'Peserta';
                            chart.data.datasets[0].data = data.individualRatings;
                            chart.data.datasets[1].label = `Tolerance ${tolerancePercentage}%`;
                            chart.data.datasets[1].data = data.standardRatings;
                            chart.data.datasets[2].data = data.originalStandardRatings;
                        } else {
                            // Update only standard dataset
                            chart.data.datasets[0].data = data.originalStandardRatings;
                        }

                        chart.update('active');
                    }
                })();
            </script>
        @endpush
    @endif
</div>
