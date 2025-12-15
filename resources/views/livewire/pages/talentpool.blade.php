<div class="max-w-6xl mx-auto p-6 bg-white rounded-lg shadow-md mt-10">
    <h1 class="text-center text-2xl font-bold text-gray-800 mb-2">9-Box Performance Matrix</h1>
    <div class="text-center text-gray-600 mb-8 text-sm">Matriks Kinerja dan Potensi Karyawan</div>

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
            <div class="text-gray-500 text-lg">Silakan pilih Event dan Position untuk melihat 9-Box Performance Matrix
            </div>
        </div>
    @elseif($this->isLoading)
        <!-- 🚀 PERFORMANCE: Loading state untuk UX yang lebih baik -->
        <div class="text-center py-12 bg-gray-50 rounded-lg">
            <div class="flex flex-col items-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
                <div class="text-gray-600 text-lg">Memuat data Talent Pool...</div>
                <div class="text-gray-500 text-sm mt-2">Memproses {{ $this->totalParticipants }} peserta</div>
            </div>
        </div>
    @elseif($this->totalParticipants === 0)
        <div class="text-center py-12 bg-gray-50 rounded-lg">
            <div class="text-gray-500 text-lg">Tidak ada data peserta untuk Event dan Position yang dipilih</div>
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
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded-full mr-2" style="background:#00C853"></div>
                            <span class="text-xs text-gray-700">Box 9: Star Performer</span>
                        </div>
                        <div class="flex items-center mt-0.5">
                            <div class="w-4 h-4 rounded-full mr-2" style="background:#2196F3"></div>
                            <span class="text-xs text-gray-700">Box 7: Potential Star</span>
                        </div>
                        <div class="flex items-center mt-0.5">
                            <div class="w-4 h-4 rounded-full mr-2" style="background:#FFC107"></div>
                            <span class="text-xs text-gray-700">Box 5: Core Performer</span>
                        </div>
                        <div class="flex items-center mt-0.5">
                            <div class="w-4 h-4 rounded-full mr-2" style="background:#E91E63"></div>
                            <span class="text-xs text-gray-700">Box 3: Inconsistent</span>
                        </div>
                        <div class="flex items-center mt-0.5">
                            <div class="w-4 h-4 rounded-full mr-2" style="background:#D32F2F"></div>
                            <span class="text-xs text-gray-700">Box 1: Need Attention</span>
                        </div>
                    </td>

                    <td class="py-0.5 pl-2 align-top">
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded-full mr-2" style="background:#00BCD4"></div>
                            <span class="text-xs text-gray-700">Box 8: High Potential</span>
                        </div>
                        <div class="flex items-center mt-0.5">
                            <div class="w-4 h-4 rounded-full mr-2" style="background:#FF5722"></div>
                            <span class="text-xs text-gray-700">Box 6: Enigma</span>
                        </div>
                        <div class="flex items-center mt-0.5">
                            <div class="w-4 h-4 rounded-full mr-2" style="background:#9C27B0"></div>
                            <span class="text-xs text-gray-700">Box 4: Solid Performer</span>
                        </div>
                        <div class="flex items-center mt-0.5">
                            <div class="w-4 h-4 rounded-full mr-2" style="background:#FF9800"></div>
                            <span class="text-xs text-gray-700">Box 2: Steady Performer</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>


    <hr class="mt-6 mb-4 border-t border-2 border-gray-400">


    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
        <div>
            <h2 class="text-sm text-center font-semibold mb-2">Distribusi Peserta per Box</h2>
            <div style="height:280px;">
                <canvas id="boxPieChart"></canvas>
            </div>
        </div>

        <div>
            <table class="text-xs text-gray-700 border-2 border-gray-300">
                <thead>
                    <tr>
                        <th class="text-center py-1 border-2 border-gray-300">Box</th>
                        <th class="text-center py-1 border-2 border-gray-300">Kategori</th>
                        <th class="text-center py-1 border-2 border-gray-300">Jumlah</th>
                        <th class="text-center py-1 border-2 border-gray-300">%</th>
                    </tr>
                </thead>
                <tbody id="boxSummaryBody">
                    <!-- Diisi via JS -->
                </tbody>
            </table>
        </div>
    </div>


</div>

<script>
    (function() {
        let chartInstances = {};
        let isProcessing = false;

        // 🚀 PERFORMANCE: Debounce function untuk rapid updates
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

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
            if (!canvas) return;

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
                                        'Box: ' + d.box
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
                                color: 'rgba(211,47,47,0.08)' // Box 1
                            },
                            {
                                x1: 0,
                                x2: potensiLower,
                                y1: kinerjaLower,
                                y2: kinerjaUpper,
                                color: 'rgba(255,152,0,0.08)' // Box 2
                            },
                            {
                                x1: potensiLower,
                                x2: potensiUpper,
                                y1: 0,
                                y2: kinerjaLower,
                                color: 'rgba(233,30,99,0.08)' // Box 3
                            },
                            {
                                x1: 0,
                                x2: potensiLower,
                                y1: kinerjaUpper,
                                y2: 5,
                                color: 'rgba(156,39,176,0.08)' // Box 4
                            },
                            {
                                x1: potensiLower,
                                x2: potensiUpper,
                                y1: kinerjaLower,
                                y2: kinerjaUpper,
                                color: 'rgba(255,193,7,0.08)' // Box 5
                            },
                            {
                                x1: potensiUpper,
                                x2: 5,
                                y1: 0,
                                y2: kinerjaLower,
                                color: 'rgba(255,87,34,0.08)' // Box 6
                            },
                            {
                                x1: potensiLower,
                                x2: potensiUpper,
                                y1: kinerjaUpper,
                                y2: 5,
                                color: 'rgba(33,150,243,0.08)' // Box 7
                            },
                            {
                                x1: potensiUpper,
                                x2: 5,
                                y1: kinerjaLower,
                                y2: kinerjaUpper,
                                color: 'rgba(0,188,212,0.08)' // Box 8
                            },
                            {
                                x1: potensiUpper,
                                x2: 5,
                                y1: kinerjaUpper,
                                y2: 5,
                                color: 'rgba(0,200,83,0.08)' // Box 9
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

            const pieCtx = pieCanvas.getContext('2d');

            const colorMap = {
                1: '#D32F2F',
                2: '#FF9800',
                3: '#E91E63',
                4: '#9C27B0',
                5: '#FFC107',
                6: '#FF5722',
                7: '#2196F3',
                8: '#00BCD4',
                9: '#00C853'
            };

            const pieColors = labels.map((label, index) => {
                const boxNumber = parseInt(label.replace('Box ', ''));
                return colorMap[boxNumber] || '#9E9E9E';
            });

            // Destroy existing chart if it exists
            const existingChart = Chart.getChart(pieCanvas);
            if (existingChart) {
                existingChart.destroy();
            }

            new Chart(pieCtx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: pieColors,
                        borderWidth: 1,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    const label = ctx.label || '';
                                    const value = ctx.raw || 0;
                                    const total = data.reduce((sum, val) => sum + val, 0);
                                    const percent = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return label + ': ' + value + ' orang (' + percent + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }

        function updateSummaryTable(boxStatistics) {
            const summaryBody = document.getElementById('boxSummaryBody');
            if (!summaryBody) return;

            summaryBody.innerHTML = '';

            const boxLabelsMap = {
                1: 'Need Attention',
                2: 'Steady Performer',
                3: 'Inconsistent',
                4: 'Solid Performer',
                5: 'Core Performer',
                6: 'Enigma',
                7: 'Potential Star',
                8: 'High Potential',
                9: 'Star Performer'
            };

            Object.keys(boxStatistics)
                .sort((a, b) => b - a) // 9 ke 1
                .forEach(box => {
                    const tr = document.createElement('tr');

                    const tdBox = document.createElement('td');
                    tdBox.className = 'px-5 py-1 border-2 border-gray-300';
                    tdBox.textContent = 'Box ' + box;

                    const tdLabel = document.createElement('td');
                    tdLabel.className = 'px-5 py-1 border-2 border-gray-300';
                    tdLabel.textContent = boxLabelsMap[box] || 'Unknown';

                    const tdCount = document.createElement('td');
                    tdCount.className = 'text-center px-5 py-1 border-2 border-gray-300';
                    tdCount.textContent = boxStatistics[box].count;

                    const tdPercent = document.createElement('td');
                    tdPercent.className = 'text-center px-5 py-1 border-2 border-gray-300';
                    tdPercent.textContent = boxStatistics[box].percentage + '%';

                    tr.appendChild(tdBox);
                    tr.appendChild(tdLabel);
                    tr.appendChild(tdCount);
                    tr.appendChild(tdPercent);
                    summaryBody.appendChild(tr);
                });
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
                Object.keys(boxStatistics).sort((a, b) => b - a).map(box => 'Box ' + box),
                Object.keys(boxStatistics).sort((a, b) => b - a).map(box => boxStatistics[box].count),
                'Talent Pool Distribution'
            );

            updateSummaryTable(boxStatistics);
        }

        function waitForLivewire(callback) {
            if (window.Livewire) {
                callback();
            } else {
                setTimeout(() => waitForLivewire(callback), 100);
            }
        }

        // Handle Livewire navigation events
        document.addEventListener('livewire:navigated', function() {
            // Re-initialize chart after navigation
            waitForLivewire(function() {
                setTimeout(initializeChart, 100);
            });
        });

        // Handle Livewire navigate away events
        document.addEventListener('livewire:navigating', function() {
            // Cleanup chart on navigate away
            const canvas = document.getElementById('nineBoxChart');
            if (canvas) {
                const chart = Chart.getChart(canvas);
                if (chart) {
                    chart.destroy();
                }
            }

            const pieCanvas = document.getElementById('boxPieChart');
            if (pieCanvas) {
                const pieChart = Chart.getChart(pieCanvas);
                if (pieChart) {
                    pieChart.destroy();
                }
            }
        });

        // Initialize chart when DOM is ready
        waitForLivewire(function() {
            // 🚀 PERFORMANCE: Debounced chart updates untuk prevent excessive re-renders
            const debouncedChartUpdate = debounce(function(eventData) {
                try {
                    const payload = Array.isArray(eventData) && eventData.length > 0 ? eventData[
                        0] : eventData;

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
            }, 300); // 300ms debounce

            // Handle Livewire events and navigation
            Livewire.on('chartDataUpdated', debouncedChartUpdate);

            // Handle loading state
            Livewire.on('loadMatrixDataDebounced', function(data) {
                console.log('Loading matrix data...', data);
            });

            // Initialize charts after a short delay to ensure DOM is ready
            setTimeout(function() {
                console.log('Initializing charts...');
                initializeChart();
            }, 500);
        });
    })();
</script>
