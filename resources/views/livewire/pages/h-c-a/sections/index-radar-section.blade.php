<div 
    id="radar-container-{{ $chartId }}"
    class="w-full max-w-5xl mx-auto bg-white border border-warm-border rounded-xl p-8 md:p-12 shadow-xs print:border-none print:shadow-none space-y-8"
>
    <!-- Section Header -->
    <div class="border-b border-warm-border pb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 block mb-1">{{ $subtitle }}</span>
            <h2 class="font-display text-2xl md:text-3xl text-primary-ink font-semibold">
                {{ explode(' ', $title)[0] }} <span class="text-accent-amber italic">{{ count(explode(' ', $title)) > 1 ? implode(' ', array_slice(explode(' ', $title), 1)) : '' }}</span>
            </h2>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Status:</span>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-forest-green border border-emerald-100">
                <i class="fas fa-circle-check mr-1.5 text-xs"></i> {{ $talentCategory }}
            </span>
        </div>
    </div>

    <!-- 1. Executive Metric Banner (Horizontal Full-Width Row) -->
    <div class="bg-warm-ivory/50 rounded-xl border border-warm-border p-6 flex flex-col md:flex-row items-center gap-6">
        <div class="flex items-center gap-4 shrink-0 pr-0 md:pr-6 border-b md:border-b-0 md:border-r border-warm-border pb-4 md:pb-0 w-full md:w-auto justify-center md:justify-start">
            <div class="relative w-20 h-20 flex items-center justify-center shrink-0">
                <svg class="w-full h-full transform -rotate-90">
                    <circle cx="40" cy="40" r="32" stroke="#e7e2db" stroke-width="6" fill="transparent"></circle>
                    <circle cx="40" cy="40" r="32" stroke="#5db010" stroke-width="6" fill="transparent" 
                            stroke-dasharray="201.06" stroke-dashoffset="{{ 201.06 * (1 - $talentIndexPercent / 100) }}" stroke-linecap="round"></circle>
                </svg>
                <div class="absolute flex flex-col items-center justify-center">
                    <span class="text-lg font-extrabold text-primary-ink leading-none">{{ number_format($talentIndex, 2) }}</span>
                    <span class="text-[9px] font-semibold text-slate-400">/ 5.00</span>
                </div>
            </div>
            <div class="space-y-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Score Index</span>
                <div class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-accent-amber/15 text-accent-amber border border-accent-amber/30">
                    {{ $talentCategory }} ({{ $talentIndexPercent }}%)
                </div>
            </div>
        </div>
        <div class="flex-1 text-xs md:text-sm text-slate-600 leading-relaxed text-center md:text-left">
            {{ $desc }}
        </div>
    </div>

    <!-- 2. Spacious Full-Width Radar Chart Card (100% Width) -->
    <div class="bg-white rounded-xl border border-warm-border p-6 md:p-8 shadow-xs">
        <!-- Chart Header & Interactive SPSP Legend -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 pb-4 border-b border-warm-border/60">
            <div class="flex items-center gap-2">
                <i class="fas fa-chart-pie text-accent-amber text-sm"></i>
                <h3 class="font-display text-base font-bold text-primary-ink">
                    Visualisasi Pemetaan Radar
                </h3>
            </div>

            <!-- Custom SPSP Legend matching Spider Plot -->
            <div class="flex flex-wrap items-center gap-2.5 text-xs font-semibold">
                <!-- Dataset 0: Peserta / Skor Aktual (Hijau) -->
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-md bg-white border border-warm-border shadow-2xs">
                    <span class="w-3 h-3 rounded-full shrink-0" style="background-color: #5db010;"></span>
                    <span class="text-primary-ink font-medium">{{ $participant->name ?? 'Skor Aktual' }}</span>
                </div>
                <!-- Dataset 1: Batas Toleransi (Merah) -->
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-md bg-white border border-warm-border shadow-2xs">
                    <span class="w-3 h-3 rounded-full shrink-0" style="background-color: #b50505;"></span>
                    <span class="text-primary-ink font-medium">Batas Toleransi <span class="hca-tol-label-{{ str_replace('-', '_', $chartId) }}">{{ (int) $tolerancePercentage }}%</span></span>
                </div>
                <!-- Dataset 2: Standar Formasi (Kuning) -->
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-md bg-white border border-warm-border shadow-2xs">
                    <span class="w-3 h-3 rounded-full shrink-0" style="background-color: #fafa05; border: 1px solid #e6d105;"></span>
                    <span class="text-primary-ink font-medium">Standar Formasi</span>
                </div>
            </div>
        </div>

        <!-- Radar Canvas with wire:ignore -->
        <div class="relative w-full h-[460px] md:h-[500px]" wire:ignore>
            <canvas id="{{ $chartId }}"></canvas>
        </div>
    </div>

    <!-- 3. Detailed Assessment Table & Deviation Gap (100% Width) -->
    <div class="space-y-4">
        <x-hca-table :headers="[
            ['label' => 'Pilar / Dimensi Evaluasi', 'class' => 'w-1/3'],
            ['label' => 'Standar Min.', 'class' => 'text-center'],
            ['label' => 'Batas Toleransi', 'class' => 'text-center'],
            ['label' => 'Skor Aktual', 'class' => 'text-center'],
            ['label' => 'Deviasi/Gap', 'class' => 'text-right']
        ]">
            @foreach ($labels as $index => $label)
                <tr class="hover:bg-warm-ivory/50 transition-colors">
                    <td class="py-3 px-4 font-semibold text-primary-ink">{{ $label }}</td>
                    <td class="py-3 px-4 text-center font-mono">{{ number_format($standardRatings[$index], 2) }}</td>
                    <td class="py-3 px-4 text-center font-mono text-slate-500 font-medium bg-amber-50/20" id="tol-cell-{{ str_replace('-', '_', $chartId) }}-{{ $index }}">
                        {{ number_format($toleranceRatings[$index], 2) }}
                    </td>
                    <td class="py-3 px-4 text-center font-mono font-bold text-forest-green bg-emerald-50/30">{{ number_format($actualRatings[$index], 2) }}</td>
                    <td class="py-3 px-4 text-right font-mono font-semibold {{ $actualRatings[$index] >= $standardRatings[$index] ? 'text-forest-green' : 'text-rust-red' }}">
                        @php $gap = $actualRatings[$index] - $standardRatings[$index]; @endphp
                        {{ $gap >= 0 ? '+' : '' }}{{ number_format($gap, 2) }}
                    </td>
                </tr>
            @endforeach
        </x-hca-table>

        @php
            $activeTol = (int) ($tolerancePercentage ?? 0);
        @endphp
        <div class="p-3.5 rounded-lg bg-warm-ivory/60 border border-warm-border/80 text-[11px] text-slate-600 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div class="flex items-center gap-2">
                <i class="fas fa-scale-balanced text-accent-amber text-xs"></i>
                <span id="tol-audit-text-{{ str_replace('-', '_', $chartId) }}">
                    @if ($activeTol > 0)
                        Penilaian menerapkan Ambang Toleransi <strong>{{ $activeTol }}%</strong> (Ambang kelulusan: ≥ <strong>{{ 100 - $activeTol }}%</strong> Standar Formasi Jabatan).
                    @else
                        Penilaian menerapkan <strong>Standar Murni (0% Toleransi)</strong> tanpa penyesuaian ambang batas.
                    @endif
                </span>
            </div>
            <div class="font-mono text-[10px] text-slate-500 font-medium shrink-0">
                Formula Gap: Skor Aktual − Standar Jabatan
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        const chartId = '{{ $chartId }}';
        const sectionCode = '{{ $sectionCode }}';
        const participantName = @json($participant->name ?? 'Skor Aktual');
        const labels = @json($labels);
        const actual = @json($actualRatings);
        const standard = @json($standardRatings);
        const tolerance = @json($toleranceRatings);
        const tolPercent = {{ (int) $tolerancePercentage }};
        let initialStandard = standard;

        function initRadarChart_{{ str_replace('-', '_', $chartId) }}() {
            const ctx = document.getElementById(chartId);
            if (!ctx) return;
            if (typeof Chart === 'undefined') {
                setTimeout(initRadarChart_{{ str_replace('-', '_', $chartId) }}, 50);
                return;
            }

            const existingChart = Chart.getChart(ctx);
            if (existingChart) {
                existingChart.destroy();
            }

            new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            // LAYER 1: PESERTA (HIJAU) - Dataset 0
                            label: participantName,
                            data: actual,
                            fill: true,
                            backgroundColor: '#5db010', // SPSP Green Layer (Solid)
                            borderColor: '#8fd006',
                            pointBackgroundColor: '#8fd006',
                            pointBorderColor: '#ffffff',
                            borderWidth: 2.5,
                            pointRadius: 4,
                            pointBorderWidth: 2,
                            pointHoverRadius: 6,
                            tension: 0.1
                        },
                        {
                            // LAYER 2: BATAS TOLERANSI (MERAH) - Dataset 1
                            label: 'Batas Toleransi ' + tolPercent + '%',
                            data: tolerance,
                            fill: true,
                            backgroundColor: '#b50505', // SPSP Red Layer (Solid)
                            borderColor: '#b50505',
                            pointBackgroundColor: '#9a0404',
                            pointBorderColor: '#ffffff',
                            borderWidth: 2,
                            pointRadius: 3,
                            pointBorderWidth: 2,
                            pointHoverRadius: 5.5,
                            tension: 0.1
                        },
                        {
                            // LAYER 3: STANDAR FORMASI (KUNING) - Dataset 2
                            label: 'Standar Formasi',
                            data: standard,
                            fill: true,
                            backgroundColor: '#fafa05', // SPSP Yellow Layer (Solid)
                            borderColor: '#e6d105',
                            pointBackgroundColor: '#e6d105',
                            pointBorderColor: '#ffffff',
                            borderWidth: 2.5,
                            pointRadius: 4,
                            pointBorderWidth: 2,
                            pointHoverRadius: 5.5,
                            tension: 0.1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 600,
                        easing: 'easeOutQuart'
                    },
                    devicePixelRatio: Math.max(window.devicePixelRatio || 1, 3),
                    plugins: {
                        legend: { display: false },
                        datalabels: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) => context.dataset.label + ': ' + Number(context.raw).toFixed(2)
                            }
                        }
                    },
                    scales: {
                        r: {
                            min: 0,
                            max: 5,
                            ticks: {
                                display: true,
                                stepSize: 1,
                                backdropColor: 'transparent',
                                color: '#64748b',
                                font: {
                                    size: 11,
                                    weight: '600',
                                    family: "'Instrument Sans', system-ui, sans-serif"
                                }
                            },
                            pointLabels: {
                                color: '#171412',
                                font: {
                                    size: 13,
                                    weight: '700',
                                    family: "'Instrument Sans', system-ui, sans-serif"
                                },
                                padding: 14
                            },
                            grid: {
                                color: 'rgba(23, 20, 18, 0.10)'
                            },
                            angleLines: {
                                color: 'rgba(23, 20, 18, 0.10)'
                            }
                        }
                    }
                }
            });
        }

        // Initialize immediately or on load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initRadarChart_{{ str_replace('-', '_', $chartId) }});
        } else {
            initRadarChart_{{ str_replace('-', '_', $chartId) }}();
        }

        // Handle tab switches in interactive web report
        window.addEventListener('hca-tab-switched', function(e) {
            if (e.detail?.section === sectionCode) {
                setTimeout(() => {
                    const ctx = document.getElementById(chartId);
                    if (ctx) {
                        const chart = Chart.getChart(ctx);
                        if (chart) {
                            chart.resize();
                            chart.update();
                        } else {
                            initRadarChart_{{ str_replace('-', '_', $chartId) }}();
                        }
                    }
                }, 40);
            }
        });

        // Handle dynamic tolerance updates in interactive web report
        window.addEventListener('tolerance-updated', function(e) {
            const tol = typeof e.detail === 'object' && e.detail !== null && 'tolerance' in e.detail 
                ? Number(e.detail.tolerance) 
                : Number(e.detail || 0);

            const ctx = document.getElementById(chartId);
            if (!ctx) return;
            let chart = Chart.getChart(ctx);
            if (!chart) {
                initRadarChart_{{ str_replace('-', '_', $chartId) }}();
                chart = Chart.getChart(ctx);
            }
            if (!chart) return;

            const factor = tol > 0 ? (1 - (tol / 100)) : 1.0;
            const newTolData = initialStandard.map(val => Number((val * factor).toFixed(2)));

            // Dataset 1 is Batas Toleransi (Merah)
            chart.data.datasets[1].label = 'Batas Toleransi ' + tol + '%';
            chart.data.datasets[1].data = newTolData;

            // Dataset 2 is Standar Formasi (Kuning)
            chart.data.datasets[2].data = initialStandard;
            chart.update();

            // Update legend label
            const tolLabels = document.querySelectorAll('.hca-tol-label-{{ str_replace('-', '_', $chartId) }}');
            tolLabels.forEach(el => { el.textContent = tol + '%'; });

            // Update table tolerance column
            newTolData.forEach((val, idx) => {
                const cell = document.getElementById('tol-cell-{{ str_replace('-', '_', $chartId) }}-' + idx);
                if (cell) {
                    cell.textContent = Number(val).toFixed(2);
                }
            });

            // Update audit text
            const auditEl = document.getElementById('tol-audit-text-{{ str_replace('-', '_', $chartId) }}');
            if (auditEl) {
                if (tol > 0) {
                    auditEl.innerHTML = 'Penilaian menerapkan Ambang Toleransi <strong>' + tol + '%</strong> (Ambang kelulusan: ≥ <strong>' + (100 - tol) + '%</strong> Standar Formasi Jabatan).';
                } else {
                    auditEl.innerHTML = 'Penilaian menerapkan <strong>Standar Murni (0% Toleransi)</strong> tanpa penyesuaian ambang batas.';
                }
            }
        });
    })();
</script>