<div class="max-w-6xl mx-auto p-6 bg-white rounded-lg shadow-md mt-10 relative">

    {{-- Loading overlay untuk standard adjustment (live update, no reload) --}}
    <div wire:loading wire:target="handleStandardUpdate" 
         class="absolute inset-0 bg-white/80 z-50 rounded-lg flex items-center justify-center">
        <div class="flex flex-col items-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
            <div class="text-gray-600 font-medium">Memproses data...</div>
        </div>
    </div>

    <h1 class="text-center text-2xl font-bold text-gray-800 mb-2">Matriks 9-Kotak Kinerja dan Potensi</h1>
    <div class="text-center text-gray-600 mb-8 text-sm">9-Box Performance Matrix: Kinerja dan Potensi Karyawan</div>

    <!-- Event and Position Selectors -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
        <div>
            <livewire:components.event-selector />
        </div>
        <div>
            <livewire:components.position-selector />
        </div>
    </div>

    <!-- Adjustment Indicators -->
    @if ($selectedTemplate)
        <div
            class="px-4 mb-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-500 dark:border-gray-600 flex flex-wrap gap-2">
            <x-adjustment-indicator :template-id="$selectedTemplate->id" category-code="potensi" size="sm"
                custom-label="Standar Potensi Disesuaikan" />
            <x-adjustment-indicator :template-id="$selectedTemplate->id" category-code="kompetensi" size="sm"
                custom-label="Standar Kompetensi Disesuaikan" />
        </div>
    @endif

    <!-- Show message if no data -->
    @if (!$this->selectedEvent || !$this->selectedPositionId)
        <div class="text-center py-12 bg-gray-50 rounded-lg">
            <div class="text-gray-500 text-lg">Silakan pilih Kegiatan dan Posisi untuk melihat Matriks 9-Kotak
            </div>
        </div>
    @elseif($this->totalParticipants === 0)
        <div class="text-center py-12 bg-gray-50 rounded-lg">
            <div class="text-gray-500 text-lg">Tidak ada data peserta untuk Kegiatan dan Posisi yang dipilih</div>
        </div>
    @else
        <div style="height:600px; margin-bottom:30px;">
            <canvas id="nineBoxChart"></canvas>
        </div>
    @endif

    <div>
        <h2 class="text-sm font-semibold mb-1">Keterangan</h2>
        <table class="border-collapse" style="width:auto;">
            <tbody>
                <tr>
                    <td class="py-0.5 pr-4 align-top">
                        @foreach ([9, 7, 5, 3, 1] as $boxNumber)
                            <div class="flex items-center @if(!$loop->first) mt-0.5 @endif">
                                <div class="w-4 h-4 rounded-full mr-2" style="background:{{ $this->boxConfig[$boxNumber]['color'] }}"></div>
                                <span class="text-xs text-gray-700">{{ $this->boxConfig[$boxNumber]['code'] }}: {{ $this->boxConfig[$boxNumber]['label'] }}</span>
                            </div>
                        @endforeach
                    </td>

                    <td class="py-0.5 pl-2 align-top">
                        @foreach ([8, 6, 4, 2] as $boxNumber)
                            <div class="flex items-center @if(!$loop->first) mt-0.5 @endif">
                                <div class="w-4 h-4 rounded-full mr-2" style="background:{{ $this->boxConfig[$boxNumber]['color'] }}"></div>
                                <span class="text-xs text-gray-700">{{ $this->boxConfig[$boxNumber]['code'] }}: {{ $this->boxConfig[$boxNumber]['label'] }}</span>
                            </div>
                        @endforeach
                    </td>
                </tr>
            </tbody>
        </table>
    </div>


    <hr class="mt-6 mb-4 border-t border-2 border-gray-400">

    <div class="mt-8 border-t-2 border-gray-400 pt-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6 text-center italic">Distribusi Talent Pool 9-Box Matrix</h3>

        <!-- Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
            <!-- Chart Section -->
            <div class="border border-gray-300 dark:border-gray-600 p-4 rounded-lg bg-gray-50 dark:bg-gray-800 transition-shadow duration-300 hover:shadow-xl" wire:ignore style="min-height: 400px;">
                <canvas id="boxPieChart" class="w-full h-full"></canvas>
            </div>

            <!-- Table Section -->
            <div class="rounded-md overflow-hidden">
                <table class="w-full text-sm text-gray-900 dark:text-gray-100">
                    <thead>
                        <tr class="bg-gray-200 dark:bg-gray-700">
                            <th class="border-2 border-gray-400 dark:border-gray-500 px-4 py-3 text-center font-bold">KOTAK</th>
                            <th class="border-2 border-gray-400 dark:border-gray-500 px-4 py-3 text-center font-bold">KATEGORI</th>
                            <th class="border-2 border-gray-400 dark:border-gray-500 px-4 py-3 text-center font-bold">JUMLAH</th>
                            <th class="border-2 border-gray-400 dark:border-gray-500 px-4 py-3 text-center font-bold">PERSENTASE</th>
                        </tr>
                    </thead>
                    <tbody id="boxSummaryBody" class="bg-white dark:bg-gray-800">
                        <!-- Diisi via JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>


</div>

<script>
    (function() {
        let chartInstances = {};
        let isProcessing = false;

        // 🎨 CENTRALIZED CONFIG: Single source of truth from PHP
        const BOX_CONFIG = @json($this->boxConfig);

        // 🚀 PERFORMANCE: Smart data sampling untuk large datasets
        function sampleData(data, maxPoints = 500) {
            if (data.length <= maxPoints) return data;

            const step = Math.ceil(data.length / maxPoints);
            return data.filter((_, index) => index % step === 0);
        }

        function updateScatterChart(pesertaData, boxBoundaries) {
            // 🚀 PERFORMANCE: Prevent concurrent processing
            if (isProcessing) return;
            isProcessing = true;
            const canvas = document.getElementById('nineBoxChart');
            if (!canvas) {
                isProcessing = false;
                return;
            }

            const ctx = canvas.getContext('2d');

            if (pesertaData.length === 0) {
                console.log('No participant data available');
                isProcessing = false;
                return;
            }

            // 🚀 PERFORMANCE: Sample data for better performance with large datasets
            const sampledData = sampleData(pesertaData, 500);
            console.log(`Processing ${pesertaData.length} participants, showing ${sampledData.length} points`);

            // Transform data for Chart.js (swap x/y for correct axes)
            const chartData = sampledData.map(p => {
                return {
                    x: p.potensi, // Horizontal axis = POTENSI
                    y: p.kinerja, // Vertical axis = KINERJA
                    nama: p.nama,
                    box: p.box,
                    color: p.color,
                    originalData: p // Keep reference for detailed view
                };
            });

            // Destroy existing chart if it exists
            const existingChart = Chart.getChart(canvas);
            if (existingChart) {
                existingChart.destroy();
            }

            new Chart(ctx, {
                type: 'scatter',
                data: {
                    datasets: [{
                        label: '',
                        data: chartData,
                        backgroundColor: chartData.map(d => d.color),
                        borderColor: chartData.map(d => d.color),
                        borderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 15,
                        showLine: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: true,
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                title: function(ctx) {
                                    return ctx[0].raw.nama;
                                },
                                label: function(ctx) {
                                    const d = ctx.raw;
                                    return [
                                        'Kinerja: ' + d.y.toFixed(1),
                                        'Potensi: ' + d.x.toFixed(1),
                                        'Kotak: K-' + d.box
                                    ];
                                }
                            }
                        },
                        datalabels: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'POTENSI',
                                font: {
                                    size: 16,
                                    weight: 'bold'
                                }
                            },
                            min: 0,
                            max: 5,
                            ticks: {
                                stepSize: 1
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'KINERJA',
                                font: {
                                    size: 16,
                                    weight: 'bold'
                                }
                            },
                            min: 0,
                            max: 5,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                },
                plugins: [{
                    id: 'nineBoxGrid',
                    beforeDraw: function(chart) {
                        const ctx = chart.ctx;
                        const xScale = chart.scales.x;
                        const yScale = chart.scales.y;
                        ctx.save();

                        // Draw box backgrounds first - use dynamic boundaries
                        const potensiLower = boxBoundaries?.potensi?.lower_bound ?? 5.5;
                        const potensiUpper = boxBoundaries?.potensi?.upper_bound ?? 7.5;
                        const kinerjaLower = boxBoundaries?.kinerja?.lower_bound ?? 5.5;
                        const kinerjaUpper = boxBoundaries?.kinerja?.upper_bound ?? 7.5;

                        const boxColors = [{
                                x1: 0,
                                x2: potensiLower,
                                y1: 0,
                                y2: kinerjaLower,
                                color: BOX_CONFIG[1].overlay_color
                            },
                            {
                                x1: 0,
                                x2: potensiLower,
                                y1: kinerjaLower,
                                y2: kinerjaUpper,
                                color: BOX_CONFIG[2].overlay_color
                            },
                            {
                                x1: potensiLower,
                                x2: potensiUpper,
                                y1: 0,
                                y2: kinerjaLower,
                                color: BOX_CONFIG[3].overlay_color
                            },
                            {
                                x1: 0,
                                x2: potensiLower,
                                y1: kinerjaUpper,
                                y2: 5,
                                color: BOX_CONFIG[4].overlay_color
                            },
                            {
                                x1: potensiLower,
                                x2: potensiUpper,
                                y1: kinerjaLower,
                                y2: kinerjaUpper,
                                color: BOX_CONFIG[5].overlay_color
                            },
                            {
                                x1: potensiUpper,
                                x2: 5,
                                y1: 0,
                                y2: kinerjaLower,
                                color: BOX_CONFIG[6].overlay_color
                            },
                            {
                                x1: potensiLower,
                                x2: potensiUpper,
                                y1: kinerjaUpper,
                                y2: 5,
                                color: BOX_CONFIG[7].overlay_color
                            },
                            {
                                x1: potensiUpper,
                                x2: 5,
                                y1: kinerjaLower,
                                y2: kinerjaUpper,
                                color: BOX_CONFIG[8].overlay_color
                            },
                            {
                                x1: potensiUpper,
                                x2: 5,
                                y1: kinerjaUpper,
                                y2: 5,
                                color: BOX_CONFIG[9].overlay_color
                            }
                        ];

                        boxColors.forEach(function(box) {
                            ctx.fillStyle = box.color;
                            ctx.fillRect(
                                xScale.getPixelForValue(box.x1),
                                yScale.getPixelForValue(box.y2),
                                xScale.getPixelForValue(box.x2) - xScale
                                .getPixelForValue(box.x1),
                                yScale.getPixelForValue(box.y1) - yScale
                                .getPixelForValue(box.y2)
                            );
                        });

                        // Draw grid lines - use dynamic boundaries
                        [potensiLower, potensiUpper].forEach(function(v) {
                            const x = xScale.getPixelForValue(v);
                            ctx.beginPath();
                            ctx.moveTo(x, yScale.getPixelForValue(5));
                            ctx.lineTo(x, yScale.getPixelForValue(0));
                            ctx.lineWidth = 3;
                            ctx.strokeStyle = '#333';
                            ctx.stroke();
                        });

                        [kinerjaLower, kinerjaUpper].forEach(function(v) {
                            const y = yScale.getPixelForValue(v);
                            ctx.beginPath();
                            ctx.moveTo(xScale.getPixelForValue(0), y);
                            ctx.lineTo(xScale.getPixelForValue(5), y);
                            ctx.stroke();
                        });

                        // Draw box numbers - use dynamic boundaries
                        ctx.font = 'bold 48px Arial';
                        ctx.fillStyle = 'rgba(0,0,0,0.15)';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';

                        const boxes = [{
                                num: '1',
                                x: potensiLower / 2,
                                y: kinerjaLower / 2
                            },
                            {
                                num: '2',
                                x: potensiLower / 2,
                                y: (kinerjaLower + kinerjaUpper) / 2
                            },
                            {
                                num: '3',
                                x: (potensiLower + potensiUpper) / 2,
                                y: kinerjaLower / 2
                            },
                            {
                                num: '4',
                                x: potensiLower / 2,
                                y: (kinerjaUpper + 5) / 2
                            },
                            {
                                num: '5',
                                x: (potensiLower + potensiUpper) / 2,
                                y: (kinerjaLower + kinerjaUpper) / 2
                            },
                            {
                                num: '6',
                                x: (potensiUpper + 5) / 2,
                                y: kinerjaLower / 2
                            },
                            {
                                num: '7',
                                x: (potensiLower + potensiUpper) / 2,
                                y: (kinerjaUpper + 5) / 2
                            },
                            {
                                num: '8',
                                x: (potensiUpper + 5) / 2,
                                y: (kinerjaLower + kinerjaUpper) / 2
                            },
                            {
                                num: '9',
                                x: (potensiUpper + 5) / 2,
                                y: (kinerjaUpper + 5) / 2
                            }
                        ];

                        boxes.forEach(function(box) {
                            ctx.fillText(box.num, xScale.getPixelForValue(box.x), yScale
                                .getPixelForValue(box.y));
                        });

                        ctx.restore();
                    }
                }]
            });

            console.log('9-Box Chart berhasil dimuat dengan ' + chartData.length + ' points!');
            isProcessing = false;
        }

        function updatePieChart(labels, data, label) {
            const pieCanvas = document.getElementById('boxPieChart');
            if (!pieCanvas) return;

            const pieColors = labels.map((label, index) => {
                const boxNumber = parseInt(label.replace('K-', ''));
                return BOX_CONFIG[boxNumber]?.color || '#9E9E9E';
            });

            // Destroy existing chart if it exists
            const existingChart = Chart.getChart(pieCanvas);
            if (existingChart) {
                existingChart.destroy();
            }

            // Reset canvas dimensions to ensure proper sizing
            pieCanvas.style.width = '';
            pieCanvas.style.height = '';
            pieCanvas.width = pieCanvas.offsetWidth;
            pieCanvas.height = pieCanvas.offsetHeight;

            const chartData = {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: pieColors,
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    datalabels: {
                        display: false
                    },
                    // Hover effects
                    hoverBackgroundColor: pieColors.map(color => {
                        // Lighten color on hover
                        return color + 'dd'; // Add transparency
                    }),
                    hoverBorderColor: '#333',
                    hoverBorderWidth: 3,
                    hoverOffset: 25 // Push slice out on hover
                }]
            };

            const config = {
                type: 'pie',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    // Reduce padding to make chart bigger
                    layout: {
                        padding: {
                            top: 20,
                            bottom: 20,
                            left: 20,
                            right: 20
                        }
                    },
                    // Smooth animations
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 800,
                        easing: 'easeInOutQuart'
                    },
                    // Hover mode settings
                    hover: {
                        mode: 'nearest',
                        intersect: true,
                        animationDuration: 400
                    },
                    // Cursor pointer on hover
                    onHover: (event, activeElements) => {
                        event.native.target.style.cursor = activeElements.length > 0 ? 'pointer' : 'default';
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: true,
                            backgroundColor: 'rgba(0, 0, 0, 0.85)',
                            padding: 14,
                            cornerRadius: 8,
                            titleFont: {
                                size: 15,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 14
                            },
                            displayColors: true,
                            boxWidth: 20,
                            boxHeight: 20,
                            boxPadding: 8,
                            caretSize: 8,
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(2) : 0;
                                    return `${label}: ${value} orang (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            };

            new Chart(pieCanvas, config);
        }

        // Helper function to determine if text should be dark or light based on background color
        function getContrastColor(hexColor) {
            // Convert hex to RGB
            const r = parseInt(hexColor.substr(1, 2), 16);
            const g = parseInt(hexColor.substr(3, 2), 16);
            const b = parseInt(hexColor.substr(5, 2), 16);

            // Calculate luminance
            const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;

            // Return black for light backgrounds, white for dark backgrounds
            return luminance > 0.5 ? '#000000' : '#ffffff';
        }

        function updateSummaryTable(boxStatistics) {
            const summaryBody = document.getElementById('boxSummaryBody');
            if (!summaryBody) return;

            summaryBody.innerHTML = '';

            // Calculate total for final row
            let totalCount = 0;
            Object.values(boxStatistics).forEach(stat => {
                totalCount += stat.count;
            });

            Object.keys(boxStatistics)
                .sort((a, b) => b - a) // 9 ke 1
                .forEach(box => {
                    const tr = document.createElement('tr');
                    const config = BOX_CONFIG[box];

                    const tdBox = document.createElement('td');
                    tdBox.className = 'border-2 border-gray-400 dark:border-gray-500 px-4 py-3 text-center font-bold';
                    const bgColor = config?.color || '#9E9E9E';
                    tdBox.style.backgroundColor = bgColor;
                    tdBox.style.color = getContrastColor(bgColor);
                    tdBox.textContent = config?.code || 'K-' + box;

                    const tdLabel = document.createElement('td');
                    tdLabel.className = 'border-2 border-gray-400 dark:border-gray-500 px-4 py-3';
                    tdLabel.textContent = config?.label || 'Unknown';

                    const tdCount = document.createElement('td');
                    tdCount.className = 'border-2 border-gray-400 dark:border-gray-500 px-4 py-3 text-center';
                    tdCount.textContent = boxStatistics[box].count + ' orang';

                    const tdPercent = document.createElement('td');
                    tdPercent.className = 'border-2 border-gray-400 dark:border-gray-500 px-4 py-3 text-center';
                    tdPercent.textContent = boxStatistics[box].percentage + '%';

                    tr.appendChild(tdBox);
                    tr.appendChild(tdLabel);
                    tr.appendChild(tdCount);
                    tr.appendChild(tdPercent);
                    summaryBody.appendChild(tr);
                });

            // Add total row
            const totalRow = document.createElement('tr');
            totalRow.className = 'bg-gray-100 dark:bg-gray-700 font-semibold';

            const tdTotalLabel = document.createElement('td');
            tdTotalLabel.className = 'border-2 border-gray-400 dark:border-gray-500 px-4 py-3';
            tdTotalLabel.colSpan = 2;
            tdTotalLabel.textContent = 'Total Peserta';

            const tdTotalCount = document.createElement('td');
            tdTotalCount.className = 'border-2 border-gray-400 dark:border-gray-500 px-4 py-3 text-center';
            tdTotalCount.textContent = totalCount + ' orang';

            const tdTotalPercent = document.createElement('td');
            tdTotalPercent.className = 'border-2 border-gray-400 dark:border-gray-500 px-4 py-3 text-center';
            tdTotalPercent.textContent = '100.00%';

            totalRow.appendChild(tdTotalLabel);
            totalRow.appendChild(tdTotalCount);
            totalRow.appendChild(tdTotalPercent);
            summaryBody.appendChild(totalRow);
        }

        // Initialize chart on page load
        function initializeChart() {
            // Get data from Livewire component
            const pesertaData = @json($this->chart);
            const boxBoundaries = @json($this->boxBoundaries);
            const boxStatistics = @json($this->boxStatistics);

            // Initialize scatter chart
            updateScatterChart(pesertaData, boxBoundaries);

            // Initialize pie chart and summary table
            updatePieChart(
                Object.keys(boxStatistics).sort((a, b) => b - a).map(box => 'K-' + box),
                Object.keys(boxStatistics).sort((a, b) => b - a).map(box => boxStatistics[box].count),
                'Distribusi Talent Pool'
            );

            updateSummaryTable(boxStatistics);
        }

        // 🚀 Handle trigger-reload event with loading overlay
        function showLoadingAndReload() {
            // Create loading overlay dynamically
            const overlay = document.createElement('div');
            overlay.style.cssText = 'position:fixed; inset:0; background:rgba(255,255,255,0.9); z-index:99999; display:flex; align-items:center; justify-content:center;';
            overlay.innerHTML = `
                <div class="flex flex-col items-center">
                    <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-blue-600 mb-4"></div>
                    <div class="text-gray-700 font-semibold text-lg">Memuat ulang halaman...</div>
                </div>
            `;
            document.body.appendChild(overlay);

            // Small delay to ensure session is saved before reload
            setTimeout(() => {
                window.location.reload();
            }, 50);
        }

        // 🚀 Expose globally for components
        window.showLoadingOverlay = showLoadingAndReload;

        // Handle Livewire initialization and navigation
        document.addEventListener('livewire:init', () => {
            // Listen for trigger-reload event
            Livewire.on('trigger-reload', () => {
                showLoadingAndReload();
            });

            Livewire.on('chartDataUpdated', function(eventData) {
                try {
                    const payload = Array.isArray(eventData) && eventData.length > 0 ? eventData[0] : eventData;

                    const chartId = payload.chartId;
                    if (!chartId || chartId !== 'talentPoolChart') {
                        return; // ignore events for other instances
                    }

                    const labels = payload.labels || ['Box 1', 'Box 2', 'Box 3', 'Box 4', 'Box 5',
                        'Box 6', 'Box 7', 'Box 8', 'Box 9'
                    ];
                    const data = Array.isArray(payload.data) ? payload.data : [];
                    const label = payload.aspectName || 'Talent Pool Distribution';

                    console.log('Updating charts with data:', {
                        participants: payload.pesertaData?.length || 0,
                        statistics: payload.boxStatistics
                    });

                    // Update scatter chart
                    updateScatterChart(payload.pesertaData || [], payload.boxBoundaries || {});

                    // Update pie chart
                    updatePieChart(labels, data, label);

                    // Update summary table
                    updateSummaryTable(payload.boxStatistics || {});

                } catch (e) {
                    console.error('chartDataUpdated render error:', e, eventData);
                }
            });
        });

        document.addEventListener('livewire:navigated', () => {
            // Delay slightly to ensure DOM is fully ready
            setTimeout(initializeChart, 100);
        });

        // Cleanup on navigation
        document.addEventListener('livewire:navigating', () => {
            const canvas = document.getElementById('nineBoxChart');
            if (canvas) {
                const chart = Chart.getChart(canvas);
                if (chart) chart.destroy();
            }

            const pieCanvas = document.getElementById('boxPieChart');
            if (pieCanvas) {
                const pieChart = Chart.getChart(pieCanvas);
                if (pieChart) pieChart.destroy();
            }
        });
    })();
</script>