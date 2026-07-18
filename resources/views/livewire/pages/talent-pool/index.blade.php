<div>
    {{-- Include Participant List Modal --}}
    <livewire:pages.talent-pool.participant-list-modal />

    <div class="max-w-6xl mx-auto p-6 border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] rounded-md shadow-xs mt-10 relative">

        {{-- Loading overlay untuk dynamic updates (live update, no reload) --}}
        <div wire:loading wire:target="handleStandardUpdate, handleEventSelected, handlePositionSelected"
            class="absolute inset-0 bg-white/80 dark:bg-gray-900/80 z-50 rounded-lg flex items-center justify-center">
            <div class="flex flex-col items-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
                <div class="text-gray-600 dark:text-gray-300 font-medium">Memproses data...</div>
            </div>
        </div>

        <h1 class="font-display text-center text-2xl font-bold tracking-tight text-primary-ink dark:text-neutral-100 mb-2">Matriks 9-Kotak Kinerja dan Potensi</h1>
        <div class="text-center text-gray-600 dark:text-gray-400 mb-8 text-sm">9-Box Performance Matrix: Kinerja dan
            Potensi Karyawan</div>

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
            <div class="text-center py-12 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <div class="text-gray-500 dark:text-gray-400 text-lg">Silakan pilih Kegiatan dan Posisi untuk melihat
                    Matriks 9-Kotak
                </div>
            </div>
        @elseif($this->totalParticipants === 0)
            <div class="text-center py-12 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <div class="text-gray-500 dark:text-gray-400 text-lg">Tidak ada data peserta untuk Kegiatan dan Posisi
                    yang dipilih</div>
            </div>
        @else
            <div wire:ignore style="height:600px; margin-bottom:30px;">
                <canvas id="nineBoxChart"></canvas>
            </div>
        @endif

        <div>
            <h2 class="font-display text-base font-bold mb-3 text-primary-ink dark:text-neutral-100">Keterangan Kotak</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3"
                style="grid-auto-flow: column; grid-template-rows: repeat(3, auto);">
                @foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9] as $boxNumber)
                    <div
                        class="flex items-center gap-3 p-2 rounded-lg bg-warm-ivory/50 dark:bg-[#1f1b18]/60 border border-warm-border dark:border-[#25211e] transition-shadow duration-200">
                        <div class="w-6 h-6 rounded-full flex-shrink-0 shadow-sm"
                            style="background:{{ $this->boxConfig[$boxNumber]['color'] }}"></div>
                        <div class="flex-1">
                            <span
                                class="font-bold text-sm text-gray-900 dark:text-gray-100">{{ $this->boxConfig[$boxNumber]['code'] }}</span>
                            <span
                                class="text-sm text-gray-700 dark:text-gray-300 ml-1">{{ $this->boxConfig[$boxNumber]['label'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Tabel Statistik --}}
        @if ($this->boxBoundaries)
            <div class="mt-6">
                <h2 class="font-display text-sm font-bold mb-2 text-primary-ink dark:text-neutral-100">Statistik Distribusi</h2>
                <table class="min-w-full border-collapse border border-warm-border dark:border-[#25211e]">
                    <thead>
                        <tr class="bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-100">
                            <th
                                class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-bold text-sm">
                                Kategori</th>
                            <th
                                class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-bold text-sm">
                                Rata-rata (μ)</th>
                            <th
                                class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-bold text-sm">
                                Standar Deviasi (σ)</th>
                            <th
                                class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-bold text-sm">
                                Batas Bawah (μ - σ)</th>
                            <th
                                class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-bold text-sm">
                                Batas Atas (μ + σ)</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-100">
                        <tr>
                            <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-semibold text-sm">
                                Potensi</td>
                            <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center text-sm">
                                {{ number_format($this->boxBoundaries['potensi']['avg'], 2) }}</td>
                            <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center text-sm">
                                {{ number_format($this->boxBoundaries['potensi']['std_dev'], 2) }}</td>
                            <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center text-sm">
                                {{ number_format($this->boxBoundaries['potensi']['lower_bound'], 2) }}</td>
                            <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center text-sm">
                                {{ number_format($this->boxBoundaries['potensi']['upper_bound'], 2) }}</td>
                        </tr>
                        <tr class="bg-warm-ivory/30 dark:bg-[#1f1b18]/40">
                            <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-semibold text-sm">
                                Kompetensi</td>
                            <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center text-sm">
                                {{ number_format($this->boxBoundaries['kinerja']['avg'], 2) }}</td>
                            <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center text-sm">
                                {{ number_format($this->boxBoundaries['kinerja']['std_dev'], 2) }}</td>
                            <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center text-sm">
                                {{ number_format($this->boxBoundaries['kinerja']['lower_bound'], 2) }}</td>
                            <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center text-sm">
                                {{ number_format($this->boxBoundaries['kinerja']['upper_bound'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif


        <hr class="mt-6 mb-4 border-t border-warm-border dark:border-[#25211e]">

        <div class="mt-8 border-t border-warm-border dark:border-[#25211e] pt-6">
            <h3 class="font-display text-lg font-bold text-primary-ink dark:text-neutral-100 mb-6 text-center">Distribusi Talent Pool 9-Box Matrix</h3>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                <!-- Chart Section -->
                <div class="border border-warm-border dark:border-[#25211e] p-4 rounded-lg bg-gray-50 dark:bg-gray-800 transition-shadow duration-300 hover:shadow-xl"
                    wire:ignore style="min-height: 400px;">
                    <div class="text-center text-xs text-gray-500 dark:text-gray-400 mb-2 italic">
                        💡 Klik pada chart untuk melihat detail peserta
                    </div>
                    <canvas id="boxPieChart" class="w-full h-full"></canvas>
                </div>

                <!-- Table Section -->
                <div class="rounded-md overflow-hidden" wire:ignore>
                    <table class="w-full text-sm text-gray-900 dark:text-gray-100">
                        <thead>
                            <tr class="bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-100 border-b border-warm-border dark:border-[#25211e]">
                                <th
                                    class="border border-warm-border dark:border-[#25211e] px-4 py-3 text-center font-bold">
                                    KOTAK</th>
                                <th
                                    class="border border-warm-border dark:border-[#25211e] px-4 py-3 text-center font-bold">
                                    KATEGORI</th>
                                <th
                                    class="border border-warm-border dark:border-[#25211e] px-4 py-3 text-center font-bold">
                                    JUMLAH</th>
                                <th
                                    class="border border-warm-border dark:border-[#25211e] px-4 py-3 text-center font-bold">
                                    PERSENTASE</th>
                                <th
                                    class="border border-warm-border dark:border-[#25211e] px-4 py-3 text-center font-bold">
                                    AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="boxSummaryBody" class="bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-100 divide-y divide-warm-border dark:divide-[#25211e]/40 text-sm">
                            <!-- Diisi via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @script
        <script>
            let chartInstances = {};
            let isProcessing = false;

            // 🎨 CENTRALIZED CONFIG: Single source of truth from PHP
            const BOX_CONFIG = @json($this->boxConfig);

            // Helper for Dark Mode
            const isDark = () => document.documentElement.classList.contains('dark');
            const getGridColor = () => isDark() ? 'rgba(255, 255, 255, 0.3)' : '#333';
            const getTextColor = () => isDark() ? '#e5e7eb' : '#374151'; // gray-200 vs gray-700
            const getNumberColor = () => isDark() ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0,0,0,0.15)';

            // 🌓 Dark Mode Observer: Update chart otomatis saat ganti mode
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.attributeName === 'class') {
                        const canvas = document.getElementById('nineBoxChart');
                        if (!canvas) return;

                        const chart = Chart.getChart(canvas);
                        if (chart) {
                            const textColor = getTextColor();

                            // Update Axes Colors
                            if (chart.options.scales.x.title) chart.options.scales.x.title.color =
                                textColor;
                            if (chart.options.scales.y.title) chart.options.scales.y.title.color =
                                textColor;
                            if (chart.options.scales.x.ticks) chart.options.scales.x.ticks.color =
                                textColor;
                            if (chart.options.scales.y.ticks) chart.options.scales.y.ticks.color =
                                textColor;

                            // Grid & Numbers will update automatically in beforeDraw because isDark() is checked live
                            chart.update();
                        }
                    }
                });
            });

            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class']
            });

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

                const sampledData = pesertaData;
                console.log(`Processing ${pesertaData.length} participants`);

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

                // 🎯 Helper: Find all participants at same/nearby position
                function findNearbyParticipants(currentPoint, allData, threshold = 0.05) {
                    return allData.filter(point => {
                        const distance = Math.sqrt(
                            Math.pow(point.x - currentPoint.x, 2) +
                            Math.pow(point.y - currentPoint.y, 2)
                        );
                        return distance <= threshold;
                    });
                }

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
                                backgroundColor: 'rgba(0,0,0,0.9)',
                                padding: 14,
                                displayColors: false,
                                titleFont: {
                                    size: 13,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 12
                                },
                                callbacks: {
                                    title: function(ctx) {
                                        const currentPoint = ctx[0].raw;
                                        const nearbyPoints = findNearbyParticipants(currentPoint,
                                            chartData);

                                        if (nearbyPoints.length > 1) {
                                            return `${nearbyPoints.length} Peserta di Titik Ini`;
                                        }
                                        return currentPoint.nama;
                                    },
                                    afterTitle: function(ctx) {
                                        const currentPoint = ctx[0].raw;
                                        return [
                                            'Kinerja: ' + currentPoint.y.toFixed(2),
                                            'Potensi: ' + currentPoint.x.toFixed(2),
                                            'Kotak: ' + BOX_CONFIG[currentPoint.box].code
                                        ];
                                    },
                                    beforeBody: function(ctx) {
                                        const currentPoint = ctx[0].raw;
                                        const nearbyPoints = findNearbyParticipants(currentPoint,
                                            chartData);

                                        if (nearbyPoints.length > 1) {
                                            return '\nDaftar Peserta:';
                                        }
                                        return '';
                                    },
                                    label: function(ctx) {
                                        const currentPoint = ctx.raw;
                                        const nearbyPoints = findNearbyParticipants(currentPoint,
                                            chartData);

                                        if (nearbyPoints.length > 1) {
                                            // Return empty, we'll use afterBody for the list
                                            return '';
                                        }
                                        // Single participant - no label needed
                                        return '';
                                    },
                                    afterBody: function(ctx) {
                                        const currentPoint = ctx[0].raw;
                                        const nearbyPoints = findNearbyParticipants(currentPoint,
                                            chartData);

                                        if (nearbyPoints.length > 1) {
                                            // Multiple participants - show all names
                                            return nearbyPoints.map((p, index) =>
                                                `${index + 1}. ${p.nama}`
                                            ).join('\n');
                                        }
                                        return '';
                                    },
                                    labelTextColor: function() {
                                        return '#ffffff';
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
                                    color: getTextColor(),
                                    font: {
                                        size: 16,
                                        weight: 'bold'
                                    }
                                },
                                grid: {
                                    color: isDark() ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)'
                                },
                                ticks: {
                                    color: getTextColor(),
                                    stepSize: 1
                                },
                                min: 0,
                                max: 5
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'KOMPETENSI',
                                    color: getTextColor(),
                                    font: {
                                        size: 16,
                                        weight: 'bold'
                                    }
                                },
                                grid: {
                                    color: isDark() ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)'
                                },
                                ticks: {
                                    color: getTextColor(),
                                    stepSize: 1
                                },
                                min: 0,
                                max: 5
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
                                ctx.strokeStyle = getGridColor();
                                ctx.stroke();
                            });

                            [kinerjaLower, kinerjaUpper].forEach(function(v) {
                                const y = yScale.getPixelForValue(v);
                                ctx.beginPath();
                                ctx.moveTo(xScale.getPixelForValue(0), y);
                                ctx.lineTo(xScale.getPixelForValue(5), y);
                                ctx.strokeStyle = getGridColor();
                                ctx.stroke();
                            });

                            // Draw box numbers - use dynamic boundaries
                            ctx.font = 'bold 48px Arial';
                            ctx.fillStyle = getNumberColor();
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
                            event.native.target.style.cursor = activeElements.length > 0 ? 'pointer' :
                                'default';
                        },
                        // Click handler to open modal
                        onClick: (event, activeElements) => {
                            if (activeElements.length > 0) {
                                const clickedElement = activeElements[0];
                                const label = chartData.labels[clickedElement.index];
                                // Extract box number from label (K-1, K-2, etc)
                                const boxNumber = label.replace('K-', '');

                                // Call the same function used by table buttons
                                openParticipantModal(boxNumber);
                            }
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
                                        return `${label}: ${value} orang (${percentage}%) - Klik untuk detail`;
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
                        tdBox.className =
                            'border border-warm-border dark:border-[#25211e] px-4 py-3 text-center font-bold';
                        const bgColor = config?.color || '#9E9E9E';
                        tdBox.style.backgroundColor = bgColor;
                        tdBox.style.color = getContrastColor(bgColor);
                        tdBox.textContent = config?.code || 'K-' + box;

                        const tdLabel = document.createElement('td');
                        tdLabel.className = 'border border-warm-border dark:border-[#25211e] px-4 py-3';
                        tdLabel.textContent = config?.label || 'Unknown';

                        const tdCount = document.createElement('td');
                        tdCount.className = 'border border-warm-border dark:border-[#25211e] px-4 py-3 text-center';
                        tdCount.textContent = boxStatistics[box].count + ' orang';

                        const tdPercent = document.createElement('td');
                        tdPercent.className = 'border border-warm-border dark:border-[#25211e] px-4 py-3 text-center';
                        tdPercent.textContent = boxStatistics[box].percentage + '%';

                        // Add Action column with button
                        const tdAction = document.createElement('td');
                        tdAction.className = 'border border-warm-border dark:border-[#25211e] px-4 py-3 text-center';

                        const button = document.createElement('button');
                        button.className =
                            'inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-primary-ink dark:text-neutral-200 bg-warm-ivory dark:bg-[#1f1b18] border border-warm-border dark:border-[#25211e] rounded-md hover:bg-primary-ink hover:text-warm-ivory dark:hover:bg-amber-600 dark:hover:text-white transition-colors shadow-xs disabled:opacity-50 disabled:cursor-not-allowed';
                        button.onclick = () => openParticipantModal(box);
                        button.disabled = boxStatistics[box].count === 0;
                        button.innerHTML = `
                    <svg class="w-3.5 h-3.5 text-accent-amber" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <span>Lihat</span>
                `;

                        tdAction.appendChild(button);

                        tr.appendChild(tdBox);
                        tr.appendChild(tdLabel);
                        tr.appendChild(tdCount);
                        tr.appendChild(tdPercent);
                        tr.appendChild(tdAction);
                        summaryBody.appendChild(tr);
                    });

                // Add total row
                const totalRow = document.createElement('tr');
                totalRow.className = 'bg-warm-ivory dark:bg-[#1f1b18] font-semibold text-primary-ink dark:text-neutral-100';

                const tdTotalLabel = document.createElement('td');
                tdTotalLabel.className = 'border border-warm-border dark:border-[#25211e] px-4 py-3';
                tdTotalLabel.colSpan = 2;
                tdTotalLabel.textContent = 'Total Peserta';

                const tdTotalCount = document.createElement('td');
                tdTotalCount.className = 'border border-warm-border dark:border-[#25211e] px-4 py-3 text-center';
                tdTotalCount.textContent = totalCount + ' orang';

                const tdTotalPercent = document.createElement('td');
                tdTotalPercent.className = 'border border-warm-border dark:border-[#25211e] px-4 py-3 text-center';
                tdTotalPercent.textContent = '100.00%';

                const tdTotalAction = document.createElement('td');
                tdTotalAction.className = 'border border-warm-border dark:border-[#25211e] px-4 py-3 text-center';
                tdTotalAction.textContent = '';

                totalRow.appendChild(tdTotalLabel);
                totalRow.appendChild(tdTotalCount);
                totalRow.appendChild(tdTotalPercent);
                totalRow.appendChild(tdTotalAction);
                summaryBody.appendChild(totalRow);
            }

            // 🚀 PERFORMANCE: Prevent modal from opening multiple times
            let isModalOpening = false;

            // Function to open participant modal
            function openParticipantModal(boxNumber) {
                // 🚀 Debounce: prevent rapid clicks
                if (isModalOpening) {
                    return;
                }

                isModalOpening = true;

                // Let the Livewire component retrieve the participants and trigger the modal opening
                $wire.openBoxModal(parseInt(boxNumber));

                // 🚀 Reset debounce flag after modal transition completes
                setTimeout(() => {
                    isModalOpening = false;
                }, 200);
            }

            // 🎨 DATA: Initial data passed directly from PHP to avoid AJAX on first load
            let initialChartData = @json($this->getChartInitializationData());

            // Initialize chart with data (either local or fetched via AJAX)
            function renderChartWithData(data) {
                if (!data) return;
                try {
                    const pesertaData = data.pesertaData || [];
                    const boxBoundaries = data.boxBoundaries;
                    const boxStatistics = data.boxStatistics || {};

                    // Initialize scatter chart
                    updateScatterChart(pesertaData, boxBoundaries);

                    // Initialize pie chart and summary table
                    updatePieChart(
                        Object.keys(boxStatistics).sort((a, b) => b - a).map(box => 'K-' + box),
                        Object.keys(boxStatistics).sort((a, b) => b - a).map(box => boxStatistics[box].count),
                        'Distribusi Talent Pool'
                    );

                    updateSummaryTable(boxStatistics);
                } catch (e) {
                    console.error('Error rendering chart:', e);
                }
            }

            // Initialize chart on page load
            async function initializeChart() {
                renderChartWithData(initialChartData);
            }

            // 🚀 Disable full-screen loading overlay and page reload
            function showLoadingAndReload() {
                // No-op: handled dynamically by wire:loading on the component card
            }

            // 🚀 Expose globally for components (keeps choice components click handler safe)
            window.showLoadingOverlay = showLoadingAndReload;

            $wire.on('chartDataNeedsUpdate', async () => {
                try {
                    const data = await $wire.getChartInitializationData();
                    renderChartWithData(data);
                } catch (e) {
                    console.error('Error fetching updated chart data:', e);
                }
            });

            // Delay slightly to ensure DOM is fully ready
            setTimeout(initializeChart, 100);

            // Cleanup on navigation using $cleanup
            $cleanup(() => {
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
        </script>
        @endscript

    </div>
</div>
