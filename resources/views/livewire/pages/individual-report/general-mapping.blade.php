<div>
    <div class="bg-warm-ivory dark:bg-neutral-900 border border-warm-border dark:border-neutral-800 rounded-lg shadow-sm overflow-hidden mx-auto my-8 max-w-[1400px] font-sans">
        <!-- Header -->
        <div class="border-b border-warm-border dark:border-neutral-800 py-6 px-8 bg-white dark:bg-neutral-950">
            <h1 class="font-display text-2xl font-bold uppercase tracking-tight text-primary-ink dark:text-neutral-100">
                GENERAL MAPPING
            </h1>
            <div class="mt-2 text-xs text-primary-ink/75 dark:text-neutral-400 space-y-1">
                <p class="text-sm font-bold text-primary-ink dark:text-neutral-200">{{ $participant->name }}</p>
                <p>{{ $participant->event->name }}</p>
                <p class="font-medium text-accent-amber dark:text-amber-500">
                    {{ $participant->positionFormation->name }} — {{ $participant->positionFormation->template->name }}
                </p>
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
            class="px-8 py-3 bg-white/50 dark:bg-neutral-950/50 border-b border-warm-border dark:border-neutral-800 flex flex-wrap gap-2">
            <x-adjustment-indicator :template-id="$participant->positionFormation->template_id" category-code="potensi"
                size="sm" custom-label="Standar Potensi Disesuaikan" />
            <x-adjustment-indicator :template-id="$participant->positionFormation->template_id"
                category-code="kompetensi" size="sm" custom-label="Standar Kompetensi Disesuaikan" />
        </div>

        <!-- Table Section -->
        <div class="p-6 overflow-x-auto bg-white dark:bg-neutral-950">
            <table class="min-w-full border-collapse border border-warm-border dark:border-neutral-800 text-sm text-primary-ink dark:text-neutral-100 font-medium">
                <thead>
                    <tr class="bg-warm-ivory dark:bg-neutral-900 text-primary-ink dark:text-neutral-100 font-bold">
                        <th class="border border-warm-border dark:border-neutral-800 px-4 py-2 font-semibold text-center" rowspan="2">No</th>
                        <th class="border border-warm-border dark:border-neutral-800 px-4 py-2 font-semibold text-left" rowspan="2">Atribut</th>
                        <th class="border border-warm-border dark:border-neutral-800 px-4 py-2 font-semibold text-center" rowspan="2">Bobot %</th>
                        <th class="border border-warm-border dark:border-neutral-800 px-4 py-2 font-semibold text-center" colspan="2">
                            <span x-data
                                x-text="$wire.tolerancePercentage > 0 ? 'Standar (-' + $wire.tolerancePercentage + '%)' : 'Standar'"></span>
                        </th>
                        <th class="border border-warm-border dark:border-neutral-800 px-4 py-2 font-semibold text-center" colspan="2">Individu</th>
                        <th class="border border-warm-border dark:border-neutral-800 px-4 py-2 font-semibold text-center" colspan="2">Gap</th>
                        <th class="border border-warm-border dark:border-neutral-800 px-4 py-2 font-semibold text-center" rowspan="2">Persentase<br>Kesesuaian</th>
                        <th class="border border-warm-border dark:border-neutral-800 px-4 py-2 font-semibold text-center" rowspan="2">Kesimpulan</th>
                    </tr>
                    <tr class="bg-warm-ivory dark:bg-neutral-900 text-primary-ink dark:text-neutral-100 font-bold">
                        <th class="border border-warm-border dark:border-neutral-800 px-4 py-2 font-semibold text-center">Rating</th>
                        <th class="border border-warm-border dark:border-neutral-800 px-4 py-2 font-semibold text-center">Skor</th>
                        <th class="border border-warm-border dark:border-neutral-800 px-4 py-2 font-semibold text-center">Rating</th>
                        <th class="border border-warm-border dark:border-neutral-800 px-4 py-2 font-semibold text-center">Skor</th>
                        <th class="border border-warm-border dark:border-neutral-800 px-4 py-2 font-semibold text-center">Rating</th>
                        <th class="border border-warm-border dark:border-neutral-800 px-4 py-2 font-semibold text-center">Skor</th>
                    </tr>
                </thead>
                <tbody>
                                        @foreach ($aspectsData as $aspect)
                    <tr class="hover:bg-warm-ivory/50 dark:hover:bg-neutral-900/50 transition-colors duration-150 text-sm text-primary-ink dark:text-neutral-200">
                        <td class="border border-warm-border dark:border-neutral-800 px-4 py-2 text-center font-normal">
                            {{ $loop->iteration }}</td>
                        <td class="border border-warm-border dark:border-neutral-800 px-4 py-2 font-semibold text-primary-ink dark:text-neutral-100">{{ $aspect['name'] }}</td>
                        <td class="border border-warm-border dark:border-neutral-800 px-4 py-2 text-center font-mono-data font-normal">
                            {{ $aspect['weight_percentage'] }}</td>
                        <td class="border border-warm-border dark:border-neutral-800 px-4 py-2 text-center font-mono-data font-normal">
                            {{ number_format($aspect['standard_rating'], 2) }}</td>
                        <td class="border border-warm-border dark:border-neutral-800 px-4 py-2 text-center font-mono-data font-normal">
                            {{ number_format($aspect['standard_score'], 2) }}</td>
                        <td class="border border-warm-border dark:border-neutral-800 px-4 py-2 text-center font-mono-data font-normal">
                            {{ number_format($aspect['individual_rating'], 2) }}</td>
                        <td class="border border-warm-border dark:border-neutral-800 px-4 py-2 text-center font-mono-data font-normal">
                            {{ number_format($aspect['individual_score'], 2) }}</td>
                        <td class="border border-warm-border dark:border-neutral-800 px-4 py-2 text-center font-mono-data font-normal">
                            {{ number_format($aspect['gap_rating'], 2) }}</td>
                        <td class="border border-warm-border dark:border-neutral-800 px-4 py-2 text-center font-mono-data font-normal">
                            {{ number_format($aspect['gap_score'], 2) }}</td>
                        <td class="border border-warm-border dark:border-neutral-800 px-4 py-2 text-center font-mono-data font-semibold text-primary-ink dark:text-neutral-100">
                            @php
                            $percentage =
                            $aspect['standard_score'] > 0
                            ? round(($aspect['individual_score'] / $aspect['standard_score']) * 100)
                            : 0;
                            @endphp
                            {{ $percentage }}%
                        </td>
                        <td
                            class="border border-warm-border dark:border-neutral-800 px-4 py-2 text-center font-semibold text-[10px] uppercase tracking-wider">
                            <span class="inline-block px-2.5 py-1 rounded {{ $conclusionConfig[$aspect['conclusion_text']]['tailwindClass'] ?? 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white' }}">
                                {{ $aspect['conclusion_text'] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach

                                        <!-- Total Rating Row -->
                    <tr class="font-bold bg-warm-ivory dark:bg-neutral-900 text-primary-ink dark:text-neutral-100 text-sm">
                        <td class="border border-warm-border dark:border-neutral-800 px-4 py-2 text-center" colspan="3">Total Rating</td>
                        <td class="border border-warm-border dark:border-neutral-800 px-4 py-2 text-center font-mono-data">
                            {{ number_format($totalStandardRating, 2) }}</td>
                        <td class="border border-warm-border dark:border-neutral-800 px-4 py-2 bg-warm-border/40 dark:bg-neutral-800/40"></td>
                        <td class="border border-warm-border dark:border-neutral-800 px-4 py-2 text-center font-mono-data">
                            {{ number_format($totalIndividualRating, 2) }}</td>
                        <td class="border border-warm-border dark:border-neutral-800 px-4 py-2 bg-warm-border/40 dark:bg-neutral-800/40"></td>
                        <td class="border border-warm-border dark:border-neutral-800 px-4 py-2 text-center font-mono-data">
                            {{ number_format($totalGapRating, 2) }}</td>
                        <td class="border border-warm-border dark:border-neutral-800 px-4 py-2 bg-warm-border/40 dark:bg-neutral-800/40"></td>
                        <td class="border border-warm-border dark:border-neutral-800 px-4 py-2 text-center font-semibold text-[10px] uppercase tracking-wider"
                            colspan="2">
                            <span class="inline-block px-2.5 py-1 rounded {{ $conclusionConfig[$overallConclusion]['tailwindClass'] ?? 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white' }}">
                                {{ $overallConclusion }}
                            </span>
                        </td>
                    </tr>

                    <!-- Total Score Row -->
                    <tr class="font-bold bg-warm-ivory dark:bg-neutral-900 text-primary-ink dark:text-neutral-100 text-sm">
                        <td class="border border-warm-border dark:border-neutral-800 px-4 py-2 text-center" colspan="3">Total Skor</td>
                        <td class="border border-warm-border dark:border-neutral-800 px-4 py-2 bg-warm-border/40 dark:bg-neutral-800/40"></td>
                        <td class="border border-warm-border dark:border-neutral-800 px-4 py-2 text-center font-mono-data">
                            {{ number_format($totalStandardScore, 2) }}</td>
                        <td class="border border-warm-border dark:border-neutral-800 px-4 py-2 bg-warm-border/40 dark:bg-neutral-800/40"></td>
                        <td class="border border-warm-border dark:border-neutral-800 px-4 py-2 text-center font-mono-data">
                            {{ number_format($totalIndividualScore, 2) }}</td>
                        <td class="border border-warm-border dark:border-neutral-800 px-4 py-2 bg-warm-border/40 dark:bg-neutral-800/40"></td>
                        <td class="border border-warm-border dark:border-neutral-800 px-4 py-2 text-center font-mono-data">
                            {{ number_format($totalGapScore, 2) }}</td>
                        <td class="border border-warm-border dark:border-neutral-800 px-4 py-2 text-center font-semibold text-[10px] uppercase tracking-wider"
                            colspan="2">
                            <span class="inline-block px-2.5 py-1 rounded {{ $conclusionConfig[$overallConclusion]['tailwindClass'] ?? 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white' }}">
                                {{ $overallConclusion }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Ranking Information Section -->
        @if ($showRankingInfo)
        @php
        $rankingInfo = $this->getParticipantRanking();
        @endphp

                @if ($rankingInfo)
        <div class="px-8 py-6 bg-white dark:bg-neutral-950 border-t border-warm-border dark:border-neutral-800">
            <div class="max-w-5xl">
                <h3 class="text-xs font-bold uppercase tracking-wider text-primary-ink/70 dark:text-neutral-400 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-accent-amber"></i>
                    Ranking Peserta - Skor Gabungan (Weighted)
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Ranking Card -->
                    <div class="bg-warm-ivory dark:bg-neutral-900 border border-warm-border dark:border-neutral-800 rounded-lg p-5 text-center shadow-xs">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-primary-ink/50 dark:text-neutral-500 mb-1">Ranking</div>
                        <div class="text-3xl font-bold text-accent-amber dark:text-amber-500 font-mono-data">
                            #{{ $rankingInfo['rank'] }}
                        </div>
                    </div>

                    <!-- Total Participants Card -->
                    <div class="bg-warm-ivory dark:bg-neutral-900 border border-warm-border dark:border-neutral-800 rounded-lg p-5 text-center shadow-xs">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-primary-ink/50 dark:text-neutral-500 mb-1">Total Peserta</div>
                        <div class="text-3xl font-bold text-primary-ink dark:text-neutral-200 font-mono-data">
                            {{ $rankingInfo['total'] }}
                        </div>
                        <div class="text-[10px] text-primary-ink/60 dark:text-neutral-400 mt-1 truncate font-medium">
                            {{ $participant->positionFormation->name }}
                        </div>
                    </div>

                    <!-- Weights Info Card -->
                    <div class="bg-warm-ivory dark:bg-neutral-900 border border-warm-border dark:border-neutral-800 rounded-lg p-5 text-center shadow-xs flex flex-col justify-center">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-primary-ink/50 dark:text-neutral-500 mb-2">Bobot Penilaian</div>
                        <div class="text-xs font-semibold text-primary-ink dark:text-neutral-200">
                            Potensi: <span class="font-mono-data font-bold text-accent-amber dark:text-amber-500">{{ $rankingInfo['potensi_weight'] }}%</span>
                        </div>
                        <div class="text-xs font-semibold text-primary-ink dark:text-neutral-200 mt-1">
                            Kompetensi: <span class="font-mono-data font-bold text-accent-amber dark:text-amber-500">{{ $rankingInfo['kompetensi_weight'] }}%</span>
                        </div>
                    </div>

                    <!-- Conclusion Card -->
                    @php
                    $conclusionText = $rankingInfo['conclusion'];
                    $borderClass = match($conclusionText) {
                    'Di Atas Standar' => 'border-forest-green/30 dark:border-forest-green/50',
                    'Memenuhi Standar' => 'border-accent-amber/30 dark:border-accent-amber/50',
                    'Di Bawah Standar' => 'border-rust-red/30 dark:border-rust-red/50',
                    default => 'border-warm-border dark:border-neutral-800'
                    };
                    @endphp
                    <div class="bg-warm-ivory dark:bg-neutral-900 border rounded-lg p-5 text-center shadow-xs flex flex-col justify-center items-center {{ $borderClass }}">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-primary-ink/50 dark:text-neutral-500 mb-2">Status</div>
                        <span class="inline-block text-[10px] uppercase tracking-wider font-bold px-3 py-1.5 rounded {{ $conclusionConfig[$conclusionText]['tailwindClass'] ?? 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white' }}">
                            {{ $rankingInfo['conclusion'] }}
                        </span>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="mt-4 bg-warm-ivory dark:bg-neutral-900 border border-warm-border dark:border-neutral-800 rounded-lg p-4 text-xs text-primary-ink/75 dark:text-neutral-400 leading-relaxed font-sans font-medium">
                    <div>
                        <strong>Keterangan:</strong> Ranking berdasarkan <strong>Total Weighted Individual Score</strong> dari kombinasi:
                        <br>
                        • <strong>Potensi ({{ $rankingInfo['potensi_weight'] }}%)</strong>: Psychology Mapping
                        <br>
                        • <strong>Kompetensi ({{ $rankingInfo['kompetensi_weight'] }}%)</strong>: Managerial Competency Mapping
                        <br>
                        Dibandingkan dengan peserta lain di event yang sama pada posisi <strong>{{ $participant->positionFormation->name }}</strong>.
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endif

                <!-- Chart Section Rating -->
        <div class="p-8 border-t border-warm-border dark:border-neutral-800 bg-white dark:bg-neutral-950" wire:ignore
            id="chart-rating-{{ $chartId }}">
            <h3 class="text-center font-display text-lg font-bold mb-6 text-primary-ink dark:text-neutral-100">Profil Pribadi Spider Plot Chart (Rating)</h3>


            <!-- Legend -->
            <div class="flex justify-center text-sm gap-3 mb-8"
                id="rating-legend-{{ $chartId }}">
                <!-- GANTI URUTAN LEGEND INI -->
                <span class="legend-item flex items-center gap-2 cursor-pointer select-none 
            hover:bg-warm-ivory dark:hover:bg-neutral-900 
            px-3 py-2 rounded-md transition-all duration-150 
            border border-warm-border dark:border-neutral-800 
            shadow-xs bg-white dark:bg-neutral-950 
            text-xs font-semibold text-primary-ink dark:text-neutral-200" data-chart="rating" data-dataset="0">
                    <span class="inline-block w-3 h-3 rounded-full" style="background-color: #5db010;"></span>
                    <span>{{ $participant->name }}</span>
                </span>
                <span class="legend-item flex items-center gap-2 cursor-pointer select-none 
            hover:bg-warm-ivory dark:hover:bg-neutral-900 
            px-3 py-2 rounded-md transition-all duration-150 
            border border-warm-border dark:border-neutral-800 
            shadow-xs bg-white dark:bg-neutral-950 
            text-xs font-semibold text-primary-ink dark:text-neutral-200" data-chart="rating" data-dataset="1">
                    <span class="inline-block w-3 h-3 rounded-full" style="background-color: #b50505;"></span>
                    <span>Standard</span>
                </span>
                <span class="legend-item flex items-center gap-2 cursor-pointer select-none 
            hover:bg-warm-ivory dark:hover:bg-neutral-900 
            px-3 py-2 rounded-md transition-all duration-150 
            border border-warm-border dark:border-neutral-800 
            shadow-xs bg-white dark:bg-neutral-950 
            text-xs font-semibold text-primary-ink dark:text-neutral-200" data-chart="rating" data-dataset="2">
                    <span class="inline-block w-3 h-3 rounded-full" style="background-color: #fafa05;"></span>
                    <span x-data
                        x-text="'Tolerance ' + $wire.tolerancePercentage + '%'"></span>
                </span>
            </div>


            <div class="flex justify-center mb-6">
                <div style="width: 100%; max-width: 1100px; height: 600px; position: relative;">
                    <canvas id="spiderRatingChart-{{ $chartId }}"></canvas>
                </div>
            </div>



            <script>
                (function() {
                    if (window['ratingChartSetup_{{ $chartId }}']) return;
                    window['ratingChartSetup_{{ $chartId }}'] = true;

                    window.ratingVisibility_{{ $chartId }} = {
                        0: false,
                        1: false,
                        2: false
                    };

                    function getGridColors() {
                        const isDark = document.documentElement.classList.contains('dark');
                        return {
                            grid: isDark ? 'rgba(255, 255, 255, 0.15)' : 'rgba(0, 0, 0, 0.15)', // Lembut
                            angleLines: isDark ? 'rgba(255, 255, 255, 0.15)' : 'rgba(0, 0, 0, 0.15)', // Lembut
                            ticks: isDark ? '#ffffff' : '#000000', // Warna solid untuk ticks
                            pointLabels: isDark ? '#d1d5db' : '#000000' // Warna solid untuk label (konsisten dengan dashboard)
                        };
                    }

                    window.toggleDataset_{{ $chartId }} = function(chartType, datasetIndex) {
                        const chart = window[`${chartType}Chart_{{ $chartId }}`];
                        if (!chart || !chart.data.datasets[datasetIndex]) return;
                        const wasHidden = chart.data.datasets[datasetIndex].hidden || false;
                        chart.data.datasets[datasetIndex].hidden = !wasHidden;
                        window[`${chartType}Visibility_{{ $chartId }}`][datasetIndex] = !wasHidden;
                        chart.update('active');
                        updateLegendVisual(chartType);
                    };

                    function updateLegendVisual(chartType) {
                        const legendContainer = document.getElementById(`${chartType}-legend-{{ $chartId }}`);
                        if (!legendContainer) return;
                        const chart = window[`${chartType}Chart_{{ $chartId }}`];
                        legendContainer.querySelectorAll('.legend-item').forEach(item => {
                            const idx = parseInt(item.dataset.dataset);
                            const isHidden = chart?.data.datasets[idx]?.hidden || false;
                            if (isHidden) {
                                item.classList.add('opacity-50', 'line-through');
                                item.classList.remove('bg-white', 'shadow-sm');
                                item.classList.add('bg-gray-50', 'dark:bg-gray-600');
                            } else {
                                item.classList.remove('opacity-50', 'line-through', 'bg-gray-50', 'dark:bg-gray-600');
                                item.classList.add('bg-white', 'dark:bg-gray-800', 'shadow-sm');
                            }
                        });
                    }

                    function setupLegendListeners(chartType) {
                        const legendContainer = document.getElementById(`${chartType}-legend-{{ $chartId }}`);
                        if (!legendContainer) return;
                        legendContainer.onclick = (e) => {
                            const item = e.target.closest('.legend-item');
                            if (!item) return;
                            window.toggleDataset_{{ $chartId }}(chartType, parseInt(item.dataset.dataset));
                        };
                    }

                    function setupRatingChart() {
                        if (window.ratingChart_{{ $chartId }}) window.ratingChart_{{ $chartId }}.destroy();

                        const chartLabels = @js($chartLabels);
                        const originalStandardRatings = @js($chartOriginalStandardRatings);
                        const standardRatings = @js($chartStandardRatings);
                        const individualRatings = @js($chartIndividualRatings);
                        const participantName = @js($participant->name);
                        const tolerancePercentage = @js($tolerancePercentage);

                        const canvas = document.getElementById('spiderRatingChart-{{ $chartId }}');
                        if (!canvas) return;
                        const ctx = canvas.getContext('2d');
                        const colors = getGridColors();

                                                const datasets = [{
                                // LAYER 1: PESERTA (HIJAU)
                                label: participantName,
                                data: individualRatings,
                                fill: true,
                                backgroundColor: '#5db010', // ← UBAH dari rgba(..., 0.7) ke solid
                                borderColor: '#8fd006',
                                pointBackgroundColor: '#8fd006',
                                pointBorderColor: '#fff',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                hidden: window.ratingVisibility_{{ $chartId }}[0],
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
                                label: 'Standard', // ← UBAH dari 'Tolerance {{ $tolerancePercentage }}%'
                                data: standardRatings, // ← data tetap standardRatings
                                fill: true,
                                backgroundColor: '#b50505', // ← UBAH dari rgba(..., 0.7) ke solid
                                borderColor: '#b50505',
                                borderWidth: 2,
                                pointRadius: 3,
                                pointBackgroundColor: '#9a0404',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                hidden: window.ratingVisibility_{{ $chartId }}[1],
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
                                label: `Tolerance ${tolerancePercentage}%`, // ← UBAH dari 'Standard'
                                data: originalStandardRatings, // ← data tetap originalStandardRatings
                                fill: true,
                                backgroundColor: '#fafa05', // ← UBAH dari rgba(..., 0.7) ke solid
                                borderColor: '#e6d105',
                                pointBackgroundColor: '#e6d105',
                                pointBorderColor: '#fff',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                hidden: window.ratingVisibility_{{ $chartId }}[2],
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
                        ];

                        window.ratingChart_{{ $chartId }} = new Chart(ctx, {
                            type: 'radar',
                            data: {
                                labels: chartLabels,
                                datasets
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    datalabels: {
                                        display: ctx => !ctx.dataset.hidden
                                    }
                                },
                                scales: {
                                    r: {
                                        beginAtZero: true,
                                        min: 0,
                                        max: 5,
                                        ticks: {
                                            stepSize: 1,
                                            color: colors.ticks,
                                            backdropColor: 'transparent',
                                            showLabelBackdrop: false,
                                            display: false, // SEMBUNYIKAN TICKS DEFAULT
                                            font: {
                                                size: 16,
                                                family: "'Instrument Sans', sans-serif"
                                            },
                                            z: 2
                                        },
                                        pointLabels: {
                                            font: {
                                                size: 16,
                                                family: "'Instrument Sans', sans-serif"
                                            },
                                            color: colors.pointLabels,
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
                                    ctx.font = scale.options.ticks.font.size + "px 'Instrument Sans', sans-serif";
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

                        console.log('✅ Rating chart initialized');
                    }

                    function waitForLivewire(callback) {
                        if (window.Livewire) callback();
                        else setTimeout(() => waitForLivewire(callback), 100);
                    }

                    waitForLivewire(function() {
                        setupRatingChart();
                        setupLegendListeners('rating');
                        updateLegendVisual('rating');

                        Livewire.on('chartDataUpdated', function(data) {
                            let chartData = Array.isArray(data) && data.length > 0 ? data[0] : data;
                            if (!chartData || !window.ratingChart_{{ $chartId }}) return;

                            const chart = window.ratingChart_{{ $chartId }};

                            // UBAH BAGIAN INI:
                            chart.data.datasets[0].data = chartData.individualRatings; // Peserta
                            chart.data.datasets[1].label = 'Standard'; // ← UBAH label
                            chart.data.datasets[1].data = chartData.standardRatings; // Standard
                            chart.data.datasets[2].label = `Tolerance ${chartData.tolerance}%`; // ← UBAH label
                            chart.data.datasets[2].data = chartData.originalStandardRatings; // Tolerance

                            const colors = getGridColors();
                            chart.options.scales.r.ticks.color = colors.ticks;
                            chart.options.scales.r.pointLabels.color = colors.pointLabels;
                            chart.options.scales.r.grid.color = colors.grid;
                            chart.options.scales.r.angleLines.color = colors.angleLines;
                            chart.options.scales.r.ticks.backdropColor = 'transparent';
                            chart.options.scales.r.ticks.showLabelBackdrop = false;

                            chart.update('active');
                            updateLegendVisual('rating');
                        });
                    });

                    const observer = new MutationObserver(() => {
                        if (window.ratingChart_{{ $chartId }}) {
                            const colors = getGridColors();
                            const chart = window.ratingChart_{{ $chartId }};
                            chart.options.scales.r.ticks.color = colors.ticks;
                            chart.options.scales.r.pointLabels.color = colors.pointLabels;
                            chart.options.scales.r.grid.color = colors.grid;
                            chart.options.scales.r.angleLines.color = colors.angleLines;
                            chart.options.scales.r.ticks.backdropColor = 'transparent';
                            chart.options.scales.r.ticks.showLabelBackdrop = false;
                            chart.update('active');
                        }
                    });
                    observer.observe(document.documentElement, {
                        attributes: true,
                        attributeFilter: ['class']
                    });
                })();
            </script>
        </div>

                <!-- Chart Section Score -->
        <div class="p-8 border-t border-warm-border dark:border-neutral-800 bg-white dark:bg-neutral-950" wire:ignore
            id="chart-score-{{ $chartId }}">
            <h3 class="text-center font-display text-lg font-bold mb-6 text-primary-ink dark:text-neutral-100">Profil Pribadi Spider Plot Chart (Score)</h3>

            <!-- Legend -->
            <div class="flex justify-center text-sm gap-3 mb-8"
                id="score-legend-{{ $chartId }}">
                <!-- GANTI URUTAN LEGEND INI -->
                <span class="legend-item flex items-center gap-2 cursor-pointer select-none 
            hover:bg-warm-ivory dark:hover:bg-neutral-900 
            px-3 py-2 rounded-md transition-all duration-150 
            border border-warm-border dark:border-neutral-800 
            shadow-xs bg-white dark:bg-neutral-950 
            text-xs font-semibold text-primary-ink dark:text-neutral-200" data-chart="score" data-dataset="0">
                    <span class="inline-block w-3 h-3 rounded-full" style="background-color: #5db010;"></span>
                    <span>{{ $participant->name }}</span>
                </span>
                <span class="legend-item flex items-center gap-2 cursor-pointer select-none 
            hover:bg-warm-ivory dark:hover:bg-neutral-900 
            px-3 py-2 rounded-md transition-all duration-150 
            border border-warm-border dark:border-neutral-800 
            shadow-xs bg-white dark:bg-neutral-950 
            text-xs font-semibold text-primary-ink dark:text-neutral-200" data-chart="score" data-dataset="1">
                    <span class="inline-block w-3 h-3 rounded-full" style="background-color: #b50505;"></span>
                    <span>Standard</span>
                </span>
                <span class="legend-item flex items-center gap-2 cursor-pointer select-none 
            hover:bg-warm-ivory dark:hover:bg-neutral-900 
            px-3 py-2 rounded-md transition-all duration-150 
            border border-warm-border dark:border-neutral-800 
            shadow-xs bg-white dark:bg-neutral-950 
            text-xs font-semibold text-primary-ink dark:text-neutral-200" data-chart="score" data-dataset="2">
                    <span class="inline-block w-3 h-3 rounded-full" style="background-color: #fafa05;"></span>
                    <span x-data
                        x-text="'Tolerance ' + $wire.tolerancePercentage + '%'"></span>
                </span>
            </div>

            <div class="flex justify-center mb-6">
                <div style="width: 100%; max-width: 1100px; height: 600px; position: relative;">
                    <canvas id="spiderScoreChart-{{ $chartId }}"></canvas>
                </div>
            </div>

            <script>
                (function() {
                    if (window['scoreChartSetup_{{ $chartId }}']) return;
                    window['scoreChartSetup_{{ $chartId }}'] = true;

                    window.scoreVisibility_{{ $chartId }} = {
                        0: false,
                        1: false,
                        2: false
                    };

                    function getGridColors() {
                        const isDark = document.documentElement.classList.contains('dark');
                        return {
                            grid: isDark ? 'rgba(255, 255, 255, 0.15)' : 'rgba(0, 0, 0, 0.15)', // Lembut
                            angleLines: isDark ? 'rgba(255, 255, 255, 0.15)' : 'rgba(0, 0, 0, 0.15)', // Lembut
                            ticks: isDark ? '#ffffff' : '#000000', // Warna solid untuk ticks
                            pointLabels: isDark ? '#d1d5db' : '#000000' // Warna solid untuk label (konsisten dengan dashboard)
                        };
                    }

                    window.toggleDataset_{{ $chartId }} = function(chartType, datasetIndex) {
                        const chart = window[`${chartType}Chart_{{ $chartId }}`];
                        if (!chart || !chart.data.datasets[datasetIndex]) return;
                        const wasHidden = chart.data.datasets[datasetIndex].hidden || false;
                        chart.data.datasets[datasetIndex].hidden = !wasHidden;
                        window[`${chartType}Visibility_{{ $chartId }}`][datasetIndex] = !wasHidden;
                        chart.update('active');
                        updateLegendVisual(chartType);
                    };

                    function updateLegendVisual(chartType) {
                        const legendContainer = document.getElementById(`${chartType}-legend-{{ $chartId }}`);
                        if (!legendContainer) return;
                        const chart = window[`${chartType}Chart_{{ $chartId }}`];
                        legendContainer.querySelectorAll('.legend-item').forEach(item => {
                            const idx = parseInt(item.dataset.dataset);
                            const isHidden = chart?.data.datasets[idx]?.hidden || false;
                            if (isHidden) {
                                item.classList.add('opacity-50', 'line-through');
                                item.classList.remove('bg-white', 'shadow-sm');
                                item.classList.add('bg-gray-50', 'dark:bg-gray-600');
                            } else {
                                item.classList.remove('opacity-50', 'line-through', 'bg-gray-50', 'dark:bg-gray-600');
                                item.classList.add('bg-white', 'dark:bg-gray-800', 'shadow-sm');
                            }
                        });
                    }

                    function setupLegendListeners(chartType) {
                        const legendContainer = document.getElementById(`${chartType}-legend-{{ $chartId }}`);
                        if (!legendContainer) return;
                        legendContainer.onclick = (e) => {
                            const item = e.target.closest('.legend-item');
                            if (!item) return;
                            window.toggleDataset_{{ $chartId }}(chartType, parseInt(item.dataset.dataset));
                        };
                    }

                    function setupScoreChart() {
                        if (window.scoreChart_{{ $chartId }}) window.scoreChart_{{ $chartId }}.destroy();

                        const chartLabels = @js($chartLabels);
                        const originalStandardScores = @js($chartOriginalStandardScores);
                        const standardScores = @js($chartStandardScores);
                        const individualScores = @js($chartIndividualScores);
                        const participantName = @js($participant->name);
                        const tolerancePercentage = @js($tolerancePercentage);

                        const canvas = document.getElementById('spiderScoreChart-{{ $chartId }}');
                        if (!canvas) return;
                        const ctx = canvas.getContext('2d');
                        const colors = getGridColors();
                        const maxScore = Math.max(...originalStandardScores, ...individualScores, ...standardScores) * 1.2;

                                                const datasets = [{
                                label: participantName,
                                data: individualScores,
                                fill: true,
                                backgroundColor: '#5db010', // Semi-transparan untuk visibilitas grid
                                borderColor: '#8fd006',
                                pointBackgroundColor: '#8fd006',
                                pointBorderColor: '#fff',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                hidden: window.scoreVisibility_{{ $chartId }}[0],
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
                                label: 'Standard', // ← UBAH dari 'Tolerance ...'
                                data: standardScores,
                                fill: true,
                                backgroundColor: '#b50505', // Semi-transparan untuk visibilitas grid
                                borderColor: '#b50505',
                                borderWidth: 2,
                                pointRadius: 3,
                                hidden: window.scoreVisibility_{{ $chartId }}[1],
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
                                label: `Tolerance ${tolerancePercentage}%`, // ← UBAH dari 'Standard'
                                data: originalStandardScores,
                                fill: true,
                                backgroundColor: '#fafa05', // Semi-transparan untuk visibilitas grid
                                borderColor: '#e6d105',
                                pointBackgroundColor: '#e6d105',
                                pointBorderColor: '#fff',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                hidden: window.scoreVisibility_{{ $chartId }}[2],
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
                        ];

                        window.scoreChart_{{ $chartId }} = new Chart(ctx, {
                            type: 'radar',
                            data: {
                                labels: chartLabels,
                                datasets
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    datalabels: {
                                        display: ctx => !ctx.dataset.hidden
                                    }
                                },
                                scales: {
                                    r: {
                                        beginAtZero: true,
                                        min: 0,
                                        max: maxScore,
                                        ticks: {
                                            stepSize: 20,
                                            color: colors.ticks,
                                            backdropColor: 'transparent',
                                            showLabelBackdrop: false,
                                            display: false, // SEMBUNYIKAN TICKS DEFAULT
                                            font: {
                                                size: 16,
                                                family: "'Instrument Sans', sans-serif"
                                            },
                                            z: 2
                                        },
                                        pointLabels: {
                                            font: {
                                                size: 16,
                                                family: "'Instrument Sans', sans-serif"
                                            },
                                            color: colors.pointLabels,
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
                                    ctx.font = scale.options.ticks.font.size + "px 'Instrument Sans', sans-serif";
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

                        console.log('✅ Score chart initialized');
                    }

                    function waitForLivewire(callback) {
                        if (window.Livewire) callback();
                        else setTimeout(() => waitForLivewire(callback), 100);
                    }

                    waitForLivewire(function() {
                        setupScoreChart();
                        setupLegendListeners('score');
                        updateLegendVisual('score');

                        Livewire.on('chartDataUpdated', function(data) {
                            let chartData = Array.isArray(data) && data.length > 0 ? data[0] : data;
                            if (!chartData || !window.scoreChart_{{ $chartId }}) return;

                            const chart = window.scoreChart_{{ $chartId }};
                            chart.data.datasets[0].data = chartData.individualScores;
                            chart.data.datasets[1].label = 'Standard'; // ← UBAH
                            chart.data.datasets[1].data = chartData.standardScores;
                            chart.data.datasets[2].label = `Tolerance ${chartData.tolerance}%`; // ← UBAH
                            chart.data.datasets[2].data = chartData.originalStandardScores;

                            const colors = getGridColors();
                            chart.options.scales.r.ticks.color = colors.ticks;
                            chart.options.scales.r.pointLabels.color = colors.pointLabels;
                            chart.options.scales.r.grid.color = colors.grid;
                            chart.options.scales.r.angleLines.color = colors.angleLines;
                            chart.options.scales.r.ticks.backdropColor = 'transparent';
                            chart.options.scales.r.ticks.showLabelBackdrop = false;

                            chart.update('active');
                            updateLegendVisual('score');
                        });
                    });

                    const observer = new MutationObserver(() => {
                        if (window.scoreChart_{{ $chartId }}) {
                            const colors = getGridColors();
                            const chart = window.scoreChart_{{ $chartId }};
                            chart.options.scales.r.ticks.color = colors.ticks;
                            chart.options.scales.r.pointLabels.color = colors.pointLabels;
                            chart.options.scales.r.grid.color = colors.grid;
                            chart.options.scales.r.angleLines.color = colors.angleLines;
                            chart.options.scales.r.ticks.backdropColor = 'transparent';
                            chart.options.scales.r.ticks.showLabelBackdrop = false;
                            chart.update('active');
                        }
                    });
                    observer.observe(document.documentElement, {
                        attributes: true,
                        attributeFilter: ['class']
                    });
                })();
            </script>
        </div>

        <!-- CSS FORCE FIX KOTAK PUTIH -->
        <style>
            .chartjs-render-monitor text,
            canvas text,
            .tick text {
                background: transparent !important;
                background-color: transparent !important;
            }
        </style>
    </div>
</div>