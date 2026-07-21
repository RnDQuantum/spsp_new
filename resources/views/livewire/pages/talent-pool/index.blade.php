<div class="max-w-7xl mx-auto p-3 md:p-4 font-sans text-primary-ink dark:text-neutral-100">
    {{-- Include Participant List Modal --}}
    @include('livewire.pages.talent-pool.participant-list-modal')

    <div class="bg-white dark:bg-[#171412] p-4 md:p-5 rounded-xl border border-warm-border dark:border-[#25211e] shadow-xs relative">

        {{-- Loading overlay untuk dynamic updates --}}
        <div wire:loading wire:target="handleStandardUpdate, handleEventSelected, handlePositionSelected"
            class="absolute inset-0 bg-white/80 dark:bg-[#171412]/80 z-50 rounded-xl flex items-center justify-center backdrop-blur-xs">
            <div class="flex flex-col items-center">
                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-accent-amber mb-3"></div>
                <div class="text-primary-ink dark:text-neutral-300 font-medium text-sm font-mono-data">Memproses data talent pool...</div>
            </div>
        </div>

        {{-- Header Editorial Executive Journal --}}
        <div class="mb-4 pb-4 border-b border-warm-border dark:border-[#25211e]">
            <span class="font-mono-data text-accent-amber font-bold uppercase tracking-widest text-xs block mb-1">
                GENERAL REPORT / TALENT MANAGEMENT
            </span>
            <h1 class="font-display text-xl md:text-2xl font-bold tracking-tight text-primary-ink dark:text-neutral-100">
                Matriks 9-Kotak Kinerja dan Potensi
            </h1>
            <p class="text-xs text-primary-ink/75 dark:text-neutral-400 mt-1">
                Pemetaan distribusi kualifikasi peserta berdasarkan kombinasi penilaian potensi dan kompetensi manajerial.
            </p>
        </div>

        <!-- Event and Position Selectors (1 Kolom - 2 Baris Full Width) -->
        <div class="mb-6 bg-warm-ivory dark:bg-[#1f1b18] p-4 rounded-xl border border-warm-border dark:border-[#25211e]">
            <div class="flex flex-col gap-3.5">
                <div class="p-2.5 bg-white dark:bg-[#171412] border border-warm-border dark:border-[#25211e] rounded-lg shadow-xs">
                    <livewire:components.event-selector :showLabel="true" />
                </div>
                <div class="p-2.5 bg-white dark:bg-[#171412] border border-warm-border dark:border-[#25211e] rounded-lg shadow-xs">
                    <livewire:components.position-selector :showLabel="true" />
                </div>
            </div>
        </div>

        <!-- Adjustment Indicators -->
        @if ($selectedTemplate)
            <div class="px-4 py-2.5 mb-6 bg-warm-ivory dark:bg-[#1f1b18] border border-warm-border dark:border-[#25211e] rounded-lg flex flex-wrap items-center justify-between gap-2">
                <div class="flex flex-wrap gap-2">
                    <x-adjustment-indicator :template-id="$selectedTemplate->id" category-code="potensi" size="sm"
                        custom-label="Standar Potensi Disesuaikan" />
                    <x-adjustment-indicator :template-id="$selectedTemplate->id" category-code="kompetensi" size="sm"
                        custom-label="Standar Kompetensi Disesuaikan" />
                </div>
            </div>
        @endif

        <!-- Show message if no data -->
        @if (!$this->selectedEvent || !$this->selectedPositionId)
            <div class="text-center py-16 bg-warm-ivory/40 dark:bg-[#1f1b18]/40 border border-warm-border dark:border-[#25211e] rounded-xl">
                <div class="text-primary-ink/70 dark:text-neutral-400 text-base font-medium">Silakan pilih Kegiatan dan Posisi untuk melihat Matriks 9-Kotak</div>
            </div>
        @elseif($this->totalParticipants === 0)
            <div class="text-center py-16 bg-warm-ivory/40 dark:bg-[#1f1b18]/40 border border-warm-border dark:border-[#25211e] rounded-xl">
                <div class="text-primary-ink/70 dark:text-neutral-400 text-base font-medium">Tidak ada data peserta untuk Kegiatan dan Posisi yang dipilih</div>
            </div>
        @else
            <div wire:ignore class="relative p-4 bg-warm-ivory/30 dark:bg-[#1f1b18]/30 border border-warm-border dark:border-[#25211e] rounded-xl mb-8" style="height:600px;">
                <canvas id="nineBoxChart"></canvas>
            </div>
        @endif

        <div>
            <h2 class="font-display text-base font-bold mb-3 text-primary-ink dark:text-neutral-100 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-accent-amber"></span>
                Keterangan Kotak (Box Legend)
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3"
                style="grid-auto-flow: column; grid-template-rows: repeat(3, auto);">
                @foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9] as $boxNumber)
                    <div
                        class="flex items-center gap-3 p-2.5 rounded-lg bg-warm-ivory/60 dark:bg-[#1f1b18]/70 border border-warm-border dark:border-[#25211e] transition-shadow duration-200">
                        <div class="w-6 h-6 rounded-full flex-shrink-0 shadow-xs border border-white/20"
                            style="background:{{ $this->boxConfig[$boxNumber]['color'] }}"></div>
                        <div class="flex-1 min-w-0">
                            <span
                                class="font-bold text-xs font-mono-data text-primary-ink dark:text-neutral-100 bg-white dark:bg-[#171412] px-1.5 py-0.5 rounded border border-warm-border dark:border-[#25211e]">{{ $this->boxConfig[$boxNumber]['code'] }}</span>
                            <span
                                class="text-xs text-primary-ink/80 dark:text-neutral-300 ml-1.5 line-clamp-1 font-medium">{{ $this->boxConfig[$boxNumber]['label'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Tabel Statistik --}}
        @if ($this->boxBoundaries)
            <div class="mt-8">
                <h2 class="font-display text-base font-bold mb-3 text-primary-ink dark:text-neutral-100 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-accent-amber"></span>
                    Statistik Distribusi Perhitungan
                </h2>
                <div class="overflow-x-auto rounded-lg border border-warm-border dark:border-[#25211e]">
                    <table class="min-w-full border-collapse border border-warm-border dark:border-[#25211e] text-sm">
                        <thead>
                            <tr class="bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-100">
                                <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold text-xs uppercase tracking-wider">
                                    Kategori</th>
                                <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold text-xs uppercase tracking-wider">
                                    Rata-rata (μ)</th>
                                <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold text-xs uppercase tracking-wider">
                                    Standar Deviasi (σ)</th>
                                <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold text-xs uppercase tracking-wider">
                                    Batas Bawah (μ - σ)</th>
                                <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold text-xs uppercase tracking-wider">
                                    Batas Atas (μ + σ)</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-100">
                            <tr>
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-semibold text-sm">
                                    Potensi</td>
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center text-sm font-mono-data">
                                    {{ number_format($this->boxBoundaries['potensi']['avg'], 2) }}</td>
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center text-sm font-mono-data">
                                    {{ number_format($this->boxBoundaries['potensi']['std_dev'], 2) }}</td>
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center text-sm font-mono-data">
                                    {{ number_format($this->boxBoundaries['potensi']['lower_bound'], 2) }}</td>
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center text-sm font-mono-data">
                                    {{ number_format($this->boxBoundaries['potensi']['upper_bound'], 2) }}</td>
                            </tr>
                            <tr class="bg-warm-ivory/30 dark:bg-[#1f1b18]/40">
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-semibold text-sm">
                                    Kompetensi</td>
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center text-sm font-mono-data">
                                    {{ number_format($this->boxBoundaries['kinerja']['avg'], 2) }}</td>
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center text-sm font-mono-data">
                                    {{ number_format($this->boxBoundaries['kinerja']['std_dev'], 2) }}</td>
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center text-sm font-mono-data">
                                    {{ number_format($this->boxBoundaries['kinerja']['lower_bound'], 2) }}</td>
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center text-sm font-mono-data">
                                    {{ number_format($this->boxBoundaries['kinerja']['upper_bound'], 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="mt-10 border-t border-warm-border dark:border-[#25211e] pt-8">
            <h3 class="font-display text-lg font-bold text-primary-ink dark:text-neutral-100 mb-6 text-center">Distribusi Talent Pool 9-Box Matrix</h3>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                <!-- Chart Section -->
                <div class="border border-warm-border dark:border-[#25211e] p-5 rounded-xl bg-warm-ivory/30 dark:bg-[#1f1b18]/30 transition-shadow duration-300"
                    wire:ignore style="min-height: 400px;">
                    <div class="text-center text-xs text-primary-ink/60 dark:text-neutral-400 mb-3 italic">
                        💡 Klik pada slice chart untuk melihat detail daftar peserta
                    </div>
                    <canvas id="boxPieChart" class="w-full h-full"></canvas>
                </div>

                <!-- Table Section -->
                <div class="rounded-xl border border-warm-border dark:border-[#25211e] overflow-hidden" wire:ignore>
                    <table class="w-full text-sm text-primary-ink dark:text-neutral-100">
                        <thead>
                            <tr class="bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-100 border-b border-warm-border dark:border-[#25211e]">
                                <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold text-xs uppercase tracking-wider">
                                    KOTAK</th>
                                <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold text-xs uppercase tracking-wider">
                                    KATEGORI</th>
                                <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold text-xs uppercase tracking-wider">
                                    JUMLAH</th>
                                <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold text-xs uppercase tracking-wider">
                                    PERSENTASE</th>
                                <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold text-xs uppercase tracking-wider">
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
            const getGridColor = () => isDark() ? 'rgba(255, 255, 255, 0.2)' : 'rgba(0, 0, 0, 0.15)';
            const getTextColor = () => isDark() ? '#e5e7eb' : '#171412';
            const getNumberColor = () => isDark() ? 'rgba(255, 255, 255, 0.12)' : 'rgba(0,0,0,0.12)';

            // Dark Mode Observer
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.attributeName === 'class') {
                        const canvas = document.getElementById('nineBoxChart');
                        if (!canvas) return;

                        const chart = Chart.getChart(canvas);
                        if (chart) {
                            const textColor = getTextColor();
                            if (chart.options.scales.x.title) chart.options.scales.x.title.color = textColor;
                            if (chart.options.scales.y.title) chart.options.scales.y.title.color = textColor;
                            if (chart.options.scales.x.ticks) chart.options.scales.x.ticks.color = textColor;
                            if (chart.options.scales.y.ticks) chart.options.scales.y.ticks.color = textColor;
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
                if (isProcessing) return;
                isProcessing = true;
                const canvas = document.getElementById('nineBoxChart');
                if (!canvas) {
                    isProcessing = false;
                    return;
                }

                const ctx = canvas.getContext('2d');

                if (pesertaData.length === 0) {
                    isProcessing = false;
                    return;
                }

                const sampledData = pesertaData;

                const chartData = sampledData.map(p => {
                    return {
                        x: p.potensi,
                        y: p.kinerja,
                        nama: p.nama,
                        box: p.box,
                        color: p.color,
                        originalData: p
                    };
                });

                function findNearbyParticipants(currentPoint, allData, threshold = 0.05) {
                    return allData.filter(point => {
                        const distance = Math.sqrt(
                            Math.pow(point.x - currentPoint.x, 2) +
                            Math.pow(point.y - currentPoint.y, 2)
                        );
                        return distance <= threshold;
                    });
                }

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
                            pointRadius: 4,
                            pointHoverRadius: 12,
                            showLine: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                enabled: true,
                                backgroundColor: 'rgba(23, 20, 18, 0.95)',
                                borderColor: 'rgba(240, 235, 228, 0.2)',
                                borderWidth: 1,
                                padding: 14,
                                cornerRadius: 8,
                                displayColors: false,
                                titleFont: { size: 13, weight: 'bold', family: 'Instrument Sans' },
                                bodyFont: { size: 12, family: 'Instrument Sans' },
                                callbacks: {
                                    title: function(ctx) {
                                        const currentPoint = ctx[0].raw;
                                        const nearbyPoints = findNearbyParticipants(currentPoint, chartData);
                                        if (nearbyPoints.length > 1) {
                                            return `${nearbyPoints.length} Peserta di Titik Ini`;
                                        }
                                        return currentPoint.nama;
                                    },
                                    afterTitle: function(ctx) {
                                        const currentPoint = ctx[0].raw;
                                        return [
                                            'Kompetensi: ' + currentPoint.y.toFixed(2),
                                            'Potensi: ' + currentPoint.x.toFixed(2),
                                            'Kotak: ' + (BOX_CONFIG[currentPoint.box]?.code || ('K-' + currentPoint.box))
                                        ];
                                    },
                                    beforeBody: function(ctx) {
                                        const currentPoint = ctx[0].raw;
                                        const nearbyPoints = findNearbyParticipants(currentPoint, chartData);
                                        return nearbyPoints.length > 1 ? '\nDaftar Peserta:' : '';
                                    },
                                    label: function() { return ''; },
                                    afterBody: function(ctx) {
                                        const currentPoint = ctx[0].raw;
                                        const nearbyPoints = findNearbyParticipants(currentPoint, chartData);
                                        if (nearbyPoints.length > 1) {
                                            return nearbyPoints.map((p, index) => `${index + 1}. ${p.nama}`).join('\n');
                                        }
                                        return '';
                                    },
                                    labelTextColor: function() { return '#ffffff'; }
                                }
                            },
                            datalabels: { display: false }
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'POTENSI',
                                    color: getTextColor(),
                                    font: { size: 14, weight: 'bold', family: 'Lora' }
                                },
                                grid: { color: isDark() ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.08)' },
                                ticks: { color: getTextColor(), stepSize: 1, font: { family: 'Instrument Sans', weight: '600' } },
                                min: 0,
                                max: 5
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'KOMPETENSI',
                                    color: getTextColor(),
                                    font: { size: 14, weight: 'bold', family: 'Lora' }
                                },
                                grid: { color: isDark() ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.08)' },
                                ticks: { color: getTextColor(), stepSize: 1, font: { family: 'Instrument Sans', weight: '600' } },
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

                            const potensiLower = boxBoundaries?.potensi?.lower_bound ?? 5.5;
                            const potensiUpper = boxBoundaries?.potensi?.upper_bound ?? 7.5;
                            const kinerjaLower = boxBoundaries?.kinerja?.lower_bound ?? 5.5;
                            const kinerjaUpper = boxBoundaries?.kinerja?.upper_bound ?? 7.5;

                            const boxColors = [
                                { x1: 0, x2: potensiLower, y1: 0, y2: kinerjaLower, color: BOX_CONFIG[1].overlay_color },
                                { x1: 0, x2: potensiLower, y1: kinerjaLower, y2: kinerjaUpper, color: BOX_CONFIG[2].overlay_color },
                                { x1: potensiLower, x2: potensiUpper, y1: 0, y2: kinerjaLower, color: BOX_CONFIG[3].overlay_color },
                                { x1: 0, x2: potensiLower, y1: kinerjaUpper, y2: 5, color: BOX_CONFIG[4].overlay_color },
                                { x1: potensiLower, x2: potensiUpper, y1: kinerjaLower, y2: kinerjaUpper, color: BOX_CONFIG[5].overlay_color },
                                { x1: potensiUpper, x2: 5, y1: 0, y2: kinerjaLower, color: BOX_CONFIG[6].overlay_color },
                                { x1: potensiLower, x2: potensiUpper, y1: kinerjaUpper, y2: 5, color: BOX_CONFIG[7].overlay_color },
                                { x1: potensiUpper, x2: 5, y1: kinerjaLower, y2: kinerjaUpper, color: BOX_CONFIG[8].overlay_color },
                                { x1: potensiUpper, x2: 5, y1: kinerjaUpper, y2: 5, color: BOX_CONFIG[9].overlay_color }
                            ];

                            boxColors.forEach(function(box) {
                                ctx.fillStyle = box.color;
                                ctx.fillRect(
                                    xScale.getPixelForValue(box.x1),
                                    yScale.getPixelForValue(box.y2),
                                    xScale.getPixelForValue(box.x2) - xScale.getPixelForValue(box.x1),
                                    yScale.getPixelForValue(box.y1) - yScale.getPixelForValue(box.y2)
                                );
                            });

                            [potensiLower, potensiUpper].forEach(function(v) {
                                const x = xScale.getPixelForValue(v);
                                ctx.beginPath();
                                ctx.moveTo(x, yScale.getPixelForValue(5));
                                ctx.lineTo(x, yScale.getPixelForValue(0));
                                ctx.lineWidth = 2.5;
                                ctx.strokeStyle = getGridColor();
                                ctx.stroke();
                            });

                            [kinerjaLower, kinerjaUpper].forEach(function(v) {
                                const y = yScale.getPixelForValue(v);
                                ctx.beginPath();
                                ctx.moveTo(xScale.getPixelForValue(0), y);
                                ctx.lineTo(xScale.getPixelForValue(5), y);
                                ctx.lineWidth = 2.5;
                                ctx.strokeStyle = getGridColor();
                                ctx.stroke();
                            });

                            ctx.font = 'bold 44px Lora, serif';
                            ctx.fillStyle = getNumberColor();
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';

                            const boxes = [
                                { num: '1', x: potensiLower / 2, y: kinerjaLower / 2 },
                                { num: '2', x: potensiLower / 2, y: (kinerjaLower + kinerjaUpper) / 2 },
                                { num: '3', x: (potensiLower + potensiUpper) / 2, y: kinerjaLower / 2 },
                                { num: '4', x: potensiLower / 2, y: (kinerjaUpper + 5) / 2 },
                                { num: '5', x: (potensiLower + potensiUpper) / 2, y: (kinerjaLower + kinerjaUpper) / 2 },
                                { num: '6', x: (potensiUpper + 5) / 2, y: kinerjaLower / 2 },
                                { num: '7', x: (potensiLower + potensiUpper) / 2, y: (kinerjaUpper + 5) / 2 },
                                { num: '8', x: (potensiUpper + 5) / 2, y: (kinerjaLower + kinerjaUpper) / 2 },
                                { num: '9', x: (potensiUpper + 5) / 2, y: (kinerjaUpper + 5) / 2 }
                            ];

                            boxes.forEach(function(box) {
                                ctx.fillText(box.num, xScale.getPixelForValue(box.x), yScale.getPixelForValue(box.y));
                            });

                            ctx.restore();
                        }
                    }]
                });

                isProcessing = false;
            }

            function updatePieChart(labels, data, label) {
                const pieCanvas = document.getElementById('boxPieChart');
                if (!pieCanvas) return;

                const pieColors = labels.map((lbl) => {
                    const boxNumber = parseInt(lbl.replace('K-', ''));
                    return BOX_CONFIG[boxNumber]?.color || '#9E9E9E';
                });

                const existingChart = Chart.getChart(pieCanvas);
                if (existingChart) {
                    existingChart.destroy();
                }

                pieCanvas.style.width = '';
                pieCanvas.style.height = '';
                pieCanvas.width = pieCanvas.offsetWidth;
                pieCanvas.height = pieCanvas.offsetHeight;

                const chartData = {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: pieColors,
                        borderColor: isDark() ? '#1f1b18' : '#ffffff',
                        borderWidth: 2,
                        datalabels: { display: false },
                        hoverBackgroundColor: pieColors.map(color => color + 'dd'),
                        hoverBorderColor: '#d97706',
                        hoverBorderWidth: 3,
                        hoverOffset: 16
                    }]
                };

                const config = {
                    type: 'pie',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        layout: { padding: { top: 15, bottom: 15, left: 15, right: 15 } },
                        animation: { animateRotate: true, animateScale: true, duration: 600 },
                        onHover: (event, activeElements) => {
                            event.native.target.style.cursor = activeElements.length > 0 ? 'pointer' : 'default';
                        },
                        onClick: (event, activeElements) => {
                            if (activeElements.length > 0) {
                                const clickedElement = activeElements[0];
                                const lbl = chartData.labels[clickedElement.index];
                                const boxNumber = parseInt(lbl.replace('K-', ''));
                                openParticipantModal(boxNumber);
                            }
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                enabled: true,
                                backgroundColor: 'rgba(23, 20, 18, 0.95)',
                                borderColor: 'rgba(240, 235, 228, 0.2)',
                                borderWidth: 1,
                                padding: 12,
                                cornerRadius: 8,
                                titleFont: { size: 14, weight: 'bold', family: 'Lora' },
                                bodyFont: { size: 13, family: 'Instrument Sans' },
                                displayColors: true,
                                boxWidth: 16,
                                boxHeight: 16,
                                callbacks: {
                                    label: function(context) {
                                        const lbl = context.label || '';
                                        const value = context.parsed;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(2) : 0;
                                        return `${lbl}: ${value} orang (${percentage}%) — Klik untuk detail`;
                                    }
                                }
                            }
                        }
                    }
                };

                new Chart(pieCanvas, config);
            }

            function getContrastColor(hexColor) {
                if (!hexColor || hexColor.charAt(0) !== '#') return '#ffffff';
                const r = parseInt(hexColor.substr(1, 2), 16);
                const g = parseInt(hexColor.substr(3, 2), 16);
                const b = parseInt(hexColor.substr(5, 2), 16);
                const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
                return luminance > 0.55 ? '#171412' : '#ffffff';
            }

            function updateSummaryTable(boxStatistics) {
                const summaryBody = document.getElementById('boxSummaryBody');
                if (!summaryBody) return;

                summaryBody.innerHTML = '';

                let totalCount = 0;
                Object.values(boxStatistics).forEach(stat => {
                    totalCount += stat.count;
                });

                Object.keys(boxStatistics)
                    .sort((a, b) => b - a)
                    .forEach(box => {
                        const tr = document.createElement('tr');
                        tr.className = 'hover:bg-warm-ivory/40 dark:hover:bg-[#1f1b18]/40 transition-colors';
                        const config = BOX_CONFIG[box];

                        const tdBox = document.createElement('td');
                        tdBox.className = 'border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-bold font-mono-data text-xs';
                        const bgColor = config?.color || '#9E9E9E';
                        tdBox.style.backgroundColor = bgColor;
                        tdBox.style.color = getContrastColor(bgColor);
                        tdBox.textContent = config?.code || 'K-' + box;

                        const tdLabel = document.createElement('td');
                        tdLabel.className = 'border border-warm-border dark:border-[#25211e] px-4 py-2 font-medium text-xs';
                        tdLabel.textContent = config?.label || 'Unknown';

                        const tdCount = document.createElement('td');
                        tdCount.className = 'border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data text-xs';
                        tdCount.textContent = boxStatistics[box].count + ' orang';

                        const tdPercent = document.createElement('td');
                        tdPercent.className = 'border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data text-xs';
                        tdPercent.textContent = boxStatistics[box].percentage + '%';

                        const tdAction = document.createElement('td');
                        tdAction.className = 'border border-warm-border dark:border-[#25211e] px-4 py-2 text-center';

                        const button = document.createElement('button');
                        button.className =
                            'inline-flex items-center gap-1 px-3 py-1 text-xs font-bold text-primary-ink dark:text-neutral-200 bg-warm-ivory dark:bg-[#1f1b18] border border-warm-border dark:border-[#25211e] rounded-md hover:bg-accent-amber hover:text-white dark:hover:bg-amber-600 dark:hover:text-white transition-colors shadow-xs disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer';
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

                const totalRow = document.createElement('tr');
                totalRow.className = 'bg-warm-ivory dark:bg-[#1f1b18] font-bold text-primary-ink dark:text-neutral-100';

                const tdTotalLabel = document.createElement('td');
                tdTotalLabel.className = 'border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-xs uppercase tracking-wider font-display';
                tdTotalLabel.colSpan = 2;
                tdTotalLabel.textContent = 'Total Peserta';

                const tdTotalCount = document.createElement('td');
                tdTotalCount.className = 'border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-mono-data text-xs';
                tdTotalCount.textContent = totalCount + ' orang';

                const tdTotalPercent = document.createElement('td');
                tdTotalPercent.className = 'border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-mono-data text-xs';
                tdTotalPercent.textContent = '100.00%';

                const tdTotalAction = document.createElement('td');
                tdTotalAction.className = 'border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center';
                tdTotalAction.textContent = '';

                totalRow.appendChild(tdTotalLabel);
                totalRow.appendChild(tdTotalCount);
                totalRow.appendChild(tdTotalPercent);
                totalRow.appendChild(tdTotalAction);
                summaryBody.appendChild(totalRow);
            }

            let isModalOpening = false;

            function openParticipantModal(boxNumber) {
                if (isModalOpening) return;
                isModalOpening = true;

                boxNumber = parseInt(boxNumber);
                const config = BOX_CONFIG[boxNumber] || {};

                let participants = [];
                if (initialChartData && initialChartData.pesertaData) {
                    participants = initialChartData.pesertaData
                        .filter(p => parseInt(p.box) === boxNumber)
                        .map(p => ({
                            name: p.nama,
                            test_number: p.test_number,
                            potensi_rating: p.potensi,
                            kinerja_rating: p.kinerja
                        }));
                }

                window.dispatchEvent(new CustomEvent('open-talent-box-modal', {
                    detail: {
                        boxNumber: boxNumber,
                        boxCode: config.code || ('K-' + boxNumber),
                        boxLabel: config.label || 'Daftar Peserta',
                        boxColor: config.color || '#9E9E9E',
                        participants: participants
                    }
                }));

                setTimeout(() => {
                    isModalOpening = false;
                }, 150);
            }

            let initialChartData = @json($this->getChartInitializationData());

            function renderChartWithData(data) {
                if (!data) return;
                try {
                    const pesertaData = data.pesertaData || [];
                    const boxBoundaries = data.boxBoundaries;
                    const boxStatistics = data.boxStatistics || {};

                    updateScatterChart(pesertaData, boxBoundaries);
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

            async function initializeChart() {
                renderChartWithData(initialChartData);
            }

            function showLoadingAndReload() {}
            window.showLoadingOverlay = showLoadingAndReload;

            $wire.on('chartDataNeedsUpdate', async () => {
                try {
                    const data = await $wire.getChartInitializationData();
                    initialChartData = data;
                    renderChartWithData(data);
                } catch (e) {
                    console.error('Error fetching updated chart data:', e);
                }
            });

            setTimeout(initializeChart, 100);

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
