<div class="bg-white dark:bg-[#171412] mx-auto my-8 border border-warm-border dark:border-[#25211e] rounded-lg shadow-xs overflow-hidden max-w-[1400px] font-sans">
    <!-- Header Section -->
    <div class="border-b border-warm-border dark:border-[#25211e] py-6 bg-warm-ivory dark:bg-[#1f1b18]">
        <h1 class="font-display text-center text-2xl font-bold tracking-tight text-primary-ink dark:text-neutral-100 uppercase">
            Rekomendasi Program Pelatihan
        </h1>
    </div>

    <div class="p-6 bg-white dark:bg-[#171412]">
        <!-- Tolerance Selector Component -->
        @if ($selectedEvent && $selectedAspect)
            @php
                $summary = $this->getPassingSummary();
            @endphp
            <div class="mb-6">
                @livewire('components.tolerance-selector', [
                    'passing' => $summary['passing'],
                    'total' => $summary['total'],
                    'showSummary' => false,
                ])
            </div>
        @endif

        <!-- Filter Dropdowns Section -->
        <div class="mb-6 bg-warm-ivory/50 dark:bg-[#1f1b18]/50 p-5 rounded-lg border border-warm-border dark:border-[#25211e]">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <!-- Event Filter -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-primary-ink/80 dark:text-neutral-300 mb-2">
                        📅 Pilih Event Assessment
                    </label>
                    @livewire('components.event-selector', ['showLabel' => false])
                </div>

                <!-- Position Filter -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-primary-ink/80 dark:text-neutral-300 mb-2">
                        💼 Pilih Jabatan
                    </label>
                    @livewire('components.position-selector', ['showLabel' => false])
                </div>

                <!-- Aspect Filter -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-primary-ink/80 dark:text-neutral-300 mb-2">
                        🎯 Pilih Aspek untuk Analisis Training
                    </label>
                    @livewire('components.aspect-selector', ['showLabel' => false])
                </div>
            </div>
        </div>

        @if ($selectedEvent && $selectedAspect)
            @php
                $chartId = 'training-rec-chart-' . $selectedEvent->id . '-' . $selectedAspect->id;
            @endphp

            <!-- SECTION 1: Donut Chart + Executive Summary Cards Layout -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-6">
                <!-- Left Column: Donut Chart (5 cols) -->
                <div class="md:col-span-5 bg-warm-ivory/50 dark:bg-[#1f1b18]/50 border border-warm-border dark:border-[#25211e] rounded-lg p-5 flex flex-col items-center justify-center relative">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-primary-ink/70 dark:text-neutral-400 mb-3 text-center">
                        Proporsi Rekomendasi Pelatihan
                    </h3>
                    <div class="w-full max-w-[260px] h-[220px] relative" wire:ignore id="container-{{ $chartId }}">
                        <canvas id="{{ $chartId }}" x-init="$nextTick(() => { if (typeof window.runTrainingChart_{{ $selectedEvent->id }}_{{ $selectedAspect->id }} === 'function') { window.runTrainingChart_{{ $selectedEvent->id }}_{{ $selectedAspect->id }}(); } })"></canvas>
                    </div>
                </div>

                <!-- Right Column: Executive Summary Cards (7 cols) -->
                <div class="md:col-span-7 flex flex-col justify-between gap-4">
                    <!-- Event Name Header Bar -->
                    <div class="bg-warm-ivory dark:bg-[#1f1b18] border border-warm-border dark:border-[#25211e] p-4 rounded-lg flex flex-wrap justify-between items-center gap-2">
                        <div>
                            <div class="font-display font-bold text-lg text-primary-ink dark:text-neutral-100">
                                {{ $selectedEvent->name }}
                            </div>
                            <div class="text-xs text-primary-ink/60 dark:text-neutral-400">Analisis Rekomendasi Training</div>
                        </div>
                        <div class="px-3 py-1 rounded-full text-xs font-bold font-mono-data bg-amber-100 dark:bg-amber-900/60 text-amber-900 dark:text-amber-200">
                            Rating Rata-rata: {{ number_format($averageRating, 2, ',', '.') }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Recommended Stat Box (Red-600 Consistent) -->
                        <div class="bg-red-50/70 dark:bg-red-950/30 border border-red-300 dark:border-red-900/50 p-4 rounded-lg">
                            <div class="flex items-center justify-between mb-1">
                                <span class="inline-block px-2.5 py-0.5 rounded text-xs font-bold uppercase tracking-wider bg-red-600 text-white">
                                    Recommended
                                </span>
                                <span class="text-xs font-bold font-mono-data text-red-600 dark:text-red-400">
                                    {{ number_format($this->recommendedPercentage, 2, ',', '.') }}%
                                </span>
                            </div>
                            <div class="text-3xl font-bold font-mono-data text-red-600 dark:text-red-400 mt-2">
                                {{ $recommendedCount }} <span class="text-xs text-primary-ink/70 dark:text-neutral-400 font-normal font-sans">peserta</span>
                            </div>
                            <div class="text-xs text-primary-ink/60 dark:text-neutral-400 mt-1">Perlu Mengikuti Pelatihan</div>
                        </div>

                        <!-- Not Recommended Stat Box (Green-600 Consistent) -->
                        <div class="bg-green-50/70 dark:bg-green-950/30 border border-green-300 dark:border-green-900/50 p-4 rounded-lg">
                            <div class="flex items-center justify-between mb-1">
                                <span class="inline-block px-2.5 py-0.5 rounded text-xs font-bold uppercase tracking-wider bg-green-600 text-white">
                                    Not Recommended
                                </span>
                                <span class="text-xs font-bold font-mono-data text-green-600 dark:text-green-400">
                                    {{ number_format($this->notRecommendedPercentage, 2, ',', '.') }}%
                                </span>
                            </div>
                            <div class="text-3xl font-bold font-mono-data text-green-600 dark:text-green-400 mt-2">
                                {{ $notRecommendedCount }} <span class="text-xs text-primary-ink/70 dark:text-neutral-400 font-normal font-sans">peserta</span>
                            </div>
                            <div class="text-xs text-primary-ink/60 dark:text-neutral-400 mt-1">Memenuhi / Melebihi Standar</div>
                        </div>
                    </div>

                    <!-- Adjustment Indicators -->
                    @if ($selectedTemplate)
                        <div class="px-4 py-2.5 bg-warm-ivory/60 dark:bg-[#1f1b18]/60 border border-warm-border dark:border-[#25211e] rounded-lg flex flex-wrap gap-2">
                            <x-adjustment-indicator :template-id="$selectedTemplate->id" category-code="potensi" size="sm" custom-label="Standar Potensi Disesuaikan" />
                            <x-adjustment-indicator :template-id="$selectedTemplate->id" category-code="kompetensi" size="sm" custom-label="Standar Kompetensi Disesuaikan" />
                        </div>
                    @endif
                </div>
            </div>

            <!-- Chart Script - Standardized Green-600 (#16a34a) & Red-600 (#dc2626) -->
            <script>
                (function() {
                    function initTrainingChart(retries = 10) {
                        const canvas = document.getElementById('{{ $chartId }}');
                        if (!canvas) {
                            if (retries > 0) {
                                setTimeout(() => initTrainingChart(retries - 1), 50);
                            }
                            return;
                        }

                        // Wait for container layout reflow if dimensions are zero
                        if ((canvas.offsetWidth === 0 || canvas.offsetHeight === 0) && retries > 0) {
                            setTimeout(() => initTrainingChart(retries - 1), 50);
                            return;
                        }

                        if (canvas.chartInstance) {
                            canvas.chartInstance.destroy();
                        }

                        const getIsDark = () => document.documentElement.classList.contains('dark') || localStorage.theme === 'dark';
                        const isDark = getIsDark();
                        const ctx = canvas.getContext('2d');

                        canvas.chartInstance = new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: ['Recommended', 'Not Recommended'],
                                datasets: [{
                                    data: [{{ (int) $recommendedCount }}, {{ (int) $notRecommendedCount }}],
                                    backgroundColor: ['#dc2626', '#16a34a'],
                                    borderColor: isDark ? '#1f1b18' : '#ffffff',
                                    borderWidth: 2,
                                    hoverOffset: 4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: '68%',
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            font: { family: "'Instrument Sans', sans-serif", size: 11, weight: 'bold' },
                                            color: isDark ? '#f5f5f5' : '#171412',
                                            padding: 12,
                                            usePointStyle: true,
                                            pointStyle: 'circle'
                                        }
                                    },
                                    tooltip: {
                                        backgroundColor: 'rgba(23, 20, 18, 0.9)',
                                        padding: 10,
                                        cornerRadius: 6,
                                        callbacks: {
                                            label: function(ctx) {
                                                const val = ctx.parsed;
                                                const total = {{ (int) $recommendedCount + (int) $notRecommendedCount }};
                                                const pct = total > 0 ? (val / total * 100).toFixed(1) : 0;
                                                return ` ${ctx.label}: ${val} orang (${pct}%)`;
                                            }
                                        }
                                    },
                                    datalabels: {
                                        formatter: (value, ctx) => {
                                            const total = {{ (int) $recommendedCount + (int) $notRecommendedCount }};
                                            if (total === 0 || value === 0) return '';
                                            return (value / total * 100).toFixed(1) + '%';
                                        },
                                        color: '#ffffff',
                                        font: { weight: 'bold', size: 11, family: "'Instrument Sans', sans-serif" }
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]
                        });

                        setTimeout(() => {
                            if (canvas.chartInstance) {
                                canvas.chartInstance.resize();
                            }
                        }, 60);
                    }

                    function waitForChartJs(callback) {
                        if (typeof Chart !== 'undefined' && typeof ChartDataLabels !== 'undefined') {
                            callback();
                        } else {
                            setTimeout(() => waitForChartJs(callback), 50);
                        }
                    }

                    window.runTrainingChart_{{ $selectedEvent->id }}_{{ $selectedAspect->id }} = function() {
                        waitForChartJs(() => initTrainingChart());
                    };

                    waitForChartJs(() => initTrainingChart());

                    if (!window.trainingChartNavigatedSetup) {
                        window.trainingChartNavigatedSetup = true;
                        document.addEventListener('livewire:navigated', function() {
                            const activeCanvas = document.querySelector('canvas[id^="training-rec-chart-"]');
                            if (activeCanvas) {
                                const idParts = activeCanvas.id.replace('training-rec-chart-', '').split('-');
                                if (idParts.length === 2) {
                                    const fnName = 'runTrainingChart_' + idParts[0] + '_' + idParts[1];
                                    if (typeof window[fnName] === 'function') {
                                        window[fnName]();
                                    }
                                }
                            }
                        });
                    }
                })();
            </script>

            <!-- SECTION 2: Selected Aspect Info Bar -->
            <div class="bg-warm-ivory/60 dark:bg-[#1f1b18]/60 border border-warm-border dark:border-[#25211e] rounded-lg p-4 mb-4 flex flex-wrap justify-between items-center gap-3">
                <div class="text-sm text-primary-ink dark:text-neutral-200">
                    Training recommended untuk aspek: <span class="font-bold font-display text-accent-amber underline ml-1">{{ $selectedAspect->name }}</span>
                </div>
                <div class="inline-flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-bold font-mono-data bg-amber-100 dark:bg-amber-900/60 text-amber-900 dark:text-amber-200">
                        Std. Rating: {{ number_format($originalStandardRating, 2, ',', '.') }}
                    </span>
                    <span x-show="$wire.tolerancePercentage > 0" class="text-xs text-primary-ink/70 dark:text-neutral-400">
                        → <span class="font-bold font-mono-data text-accent-amber">{{ number_format($standardRating, 2, ',', '.') }}</span>
                        <span class="text-xs italic">(toleransi <span x-text="$wire.tolerancePercentage"></span>%)</span>
                    </span>
                </div>
            </div>

            <div class="mb-4 flex flex-wrap items-center justify-between gap-3 text-sm">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-primary-ink/70 dark:text-neutral-400">Tampilkan:</span>
                    <select wire:model.live="perPage"
                        class="px-3 py-1 text-sm border border-warm-border dark:border-[#25211e] rounded bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-100">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="all">Semua</option>
                    </select>
                </div>
            </div>

            <!-- TABLE DATA: Participants -->
            <div class="overflow-x-auto rounded-lg border border-warm-border dark:border-[#25211e] relative mb-6">
                <!-- Loading Indicator -->
                <div wire:loading wire:target="eventCode,aspectId,handleToleranceUpdate"
                    class="absolute inset-0 bg-white/75 dark:bg-[#171412]/75 flex items-center justify-center z-10 rounded-lg backdrop-blur-xs">
                    <div class="bg-warm-ivory dark:bg-[#1f1b18] rounded-lg p-4 shadow-lg border border-warm-border dark:border-[#25211e]">
                        <div class="flex items-center space-x-3">
                            <svg class="animate-spin h-5 w-5 text-accent-amber" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm font-bold text-primary-ink dark:text-neutral-100">Memuat data...</span>
                        </div>
                    </div>
                </div>

                <table class="w-full border-collapse text-sm text-primary-ink dark:text-neutral-200">
                    <thead>
                        <tr class="bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-100 font-bold">
                            <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold">Priority</th>
                            <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold">No. Test</th>
                            <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-left font-bold">Nama</th>
                            <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-left font-bold">Jabatan</th>
                            <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold">Rating</th>
                            <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold">Statement</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-[#171412]">
                        @if ($participants && count($participants) > 0)
                            @foreach ($participants as $participant)
                                <tr class="hover:bg-warm-ivory/50 dark:hover:bg-[#1f1b18]/50 transition-colors duration-150">
                                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">
                                        #{{ $participant['priority'] }}
                                    </td>
                                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">
                                        {{ $participant['test_number'] }}
                                    </td>
                                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-semibold text-primary-ink dark:text-neutral-100">
                                        {{ $participant['name'] }}
                                    </td>
                                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2">
                                        {{ $participant['position'] }}
                                    </td>
                                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data font-bold {{ $participant['is_recommended'] ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                        {{ number_format($participant['rating'], 2, ',', '.') }}
                                    </td>
                                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center">
                                        @if ($participant['is_recommended'])
                                            <span class="inline-block px-2.5 py-0.5 rounded text-xs font-bold uppercase tracking-wider bg-red-600 text-white">
                                                Recommended
                                            </span>
                                        @else
                                            <span class="inline-block px-2.5 py-0.5 rounded text-xs font-bold uppercase tracking-wider bg-green-600 text-white">
                                                Not Recommended
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="border border-warm-border dark:border-[#25211e] px-4 py-6 text-center text-primary-ink/60 dark:text-neutral-400">
                                    Tidak ada data peserta untuk aspek ini
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($participants && $participants->hasPages())
                <div class="mb-6">
                    {{ $participants->links(data: ['scrollTo' => false]) }}
                </div>
            @endif

            <!-- Legend Card -->
            <div class="p-4 bg-warm-ivory dark:bg-[#1f1b18] rounded-lg border border-warm-border dark:border-[#25211e]">
                <h4 class="font-bold text-xs uppercase tracking-wider text-primary-ink dark:text-neutral-100 mb-2">Keterangan:</h4>
                <ul class="list-disc list-inside text-sm text-primary-ink/80 dark:text-neutral-300 space-y-1">
                    <li><strong>Statement:</strong>
                        <ul class="ml-6 mt-1 space-y-1">
                            <li><span class="px-2 py-0.5 rounded bg-red-600 text-white font-semibold text-xs">Recommended</span>: Peserta dengan rating di bawah standar, direkomendasikan untuk mengikuti pelatihan</li>
                            <li><span class="px-2 py-0.5 rounded bg-green-600 text-white font-semibold text-xs">Not Recommended</span>: Peserta dengan rating memenuhi atau melebihi standar</li>
                        </ul>
                    </li>
                    <li><strong>Priority:</strong> Urutan berdasarkan rating terendah (prioritas tertinggi)</li>
                </ul>
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="text-5xl mb-3">📊</div>
                <h3 class="text-xl font-bold font-display text-primary-ink dark:text-neutral-100 mb-2">Training Recommendation Analysis</h3>
                <p class="text-sm text-primary-ink/70 dark:text-neutral-400">Silakan pilih Event dan Aspek untuk melihat rekomendasi training</p>
            </div>
        @endif

        @if ($selectedEvent && $aspectPriorities && $aspectPriorities->isNotEmpty())
            <div class="mt-10 pt-6 border-t border-warm-border dark:border-[#25211e]">
                <!-- Section Header -->
                <div class="mb-5">
                    <h2 class="font-display text-xl font-bold text-primary-ink dark:text-neutral-100 uppercase">
                        Prioritas Perbaikan Atribut Mapping
                    </h2>
                    <p class="text-xs font-semibold text-accent-amber font-sans mt-0.5">
                        {{ $selectedEvent->name }} ({{ $selectedEvent->year }}) • <span class="text-primary-ink/60 dark:text-neutral-400 font-normal italic">Klik nama atribut pada tabel di bawah untuk melihat daftar peserta</span>
                    </p>
                </div>

                <!-- Table Section -->
                <div class="overflow-x-auto rounded-lg border border-warm-border dark:border-[#25211e] mb-6">
                    <table class="w-full border-collapse text-sm text-primary-ink dark:text-neutral-200">
                        <thead>
                            <tr class="bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-100 font-bold">
                                <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold">Prioritas</th>
                                <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-left font-bold">Atribut</th>
                                <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold">Std. Rating</th>
                                <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold">Rata-rata Rating</th>
                                <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold">Gap</th>
                                <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold">Tindak Lanjut</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-[#171412]">
                            @foreach ($aspectPriorities as $priority)
                                <tr class="hover:bg-warm-ivory/50 dark:hover:bg-[#1f1b18]/50 transition-colors duration-150">
                                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">
                                        #{{ $priority['priority'] }}
                                    </td>
                                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 cursor-pointer hover:bg-warm-ivory/80 dark:hover:bg-[#1f1b18]/80 transition-colors group"
                                        wire:click="openAttributeModal({{ $priority['aspect_id'] }})"
                                        title="Klik untuk melihat daftar peserta">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-accent-amber font-semibold group-hover:underline">
                                                {{ $priority['aspect_name'] }}
                                            </span>
                                            <div class="flex items-center gap-1.5">
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-accent-amber/10 text-accent-amber border border-accent-amber/20 group-hover:bg-accent-amber group-hover:text-white transition-colors duration-150">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    Detail
                                                </span>
                                                <span wire:loading wire:target="openAttributeModal({{ $priority['aspect_id'] }})"
                                                    class="inline-flex items-center">
                                                    <svg class="animate-spin h-3.5 w-3.5 text-accent-amber" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">
                                        {{ number_format($priority['adjusted_standard_rating'], 2, ',', '.') }}
                                    </td>
                                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">
                                        {{ number_format($priority['average_rating'], 2, ',', '.') }}
                                    </td>
                                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data font-bold {{ $priority['gap'] < 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                        {{ number_format($priority['gap'], 2, ',', '.') }}
                                    </td>
                                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center">
                                        @if ($priority['action'] === 'Pelatihan')
                                            <span class="inline-block px-2.5 py-0.5 rounded text-xs font-bold uppercase tracking-wider bg-red-600 text-white">
                                                Pelatihan
                                            </span>
                                        @else
                                            <span class="inline-block px-2.5 py-0.5 rounded text-xs font-bold uppercase tracking-wider bg-green-600 text-white">
                                                Dipertahankan
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Summary Info Card -->
                <div class="p-4 bg-warm-ivory dark:bg-[#1f1b18] rounded-lg border border-warm-border dark:border-[#25211e]">
                    <h4 class="font-bold text-xs uppercase tracking-wider text-primary-ink dark:text-neutral-100 mb-2">Keterangan:</h4>
                    <ul class="list-disc list-inside text-sm text-primary-ink/80 dark:text-neutral-300 space-y-1">
                        <li><strong>Gap:</strong> Selisih antara rata-rata rating dengan standar rating (Rata-rata - Standar)</li>
                        <li><strong>Tindak Lanjut:</strong>
                            <ul class="ml-6 mt-1 space-y-1">
                                <li><span class="px-2 py-0.5 rounded bg-red-600 text-white font-semibold text-xs">Pelatihan</span>: Gap negatif, perlu pelatihan untuk meningkatkan kompetensi</li>
                                <li><span class="px-2 py-0.5 rounded bg-green-600 text-white font-semibold text-xs">Dipertahankan</span>: Gap positif atau nol, kompetensi sudah memenuhi atau melebihi standar</li>
                            </ul>
                        </li>
                        <li><strong>Prioritas:</strong> Diurutkan berdasarkan gap terkecil (paling negatif) sebagai prioritas tertinggi</li>
                    </ul>
                </div>
            </div>
        @endif
    </div>

    <!-- Attribute Participant List Modal (Pure Alpine — no Livewire round-trip) -->
    @include('livewire.pages.general-report.training.attribute-participant-list-modal')
</div>
