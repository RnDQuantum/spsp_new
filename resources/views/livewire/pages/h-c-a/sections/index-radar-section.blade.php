<div 
    x-data="{
        chartId: @js($chartId),
        labels: @js($labels),
        actual: @js($actualRatings),
        standard: @js($standardRatings),
        tolerance: @js($toleranceRatings),
        tolerancePercentage: {{ (int) $tolerancePercentage }},
        participantName: @js($participant->name ?? 'Skor Aktual'),
        sectionCode: @js($sectionCode),
        chartInstance: null,

        init() {
            this.$nextTick(() => {
                this.ensureChart();
            });
        },

        ensureChart() {
            const canvas = document.getElementById(this.chartId);
            if (!canvas) return;
            if (typeof Chart === 'undefined') {
                setTimeout(() => this.ensureChart(), 50);
                return;
            }

            const existing = Chart.getChart(canvas);
            if (existing) {
                this.chartInstance = existing;
                this.updateChartData();
                return;
            }

            this.chartInstance = new Chart(canvas, {
                type: 'radar',
                data: {
                    labels: this.labels,
                    datasets: [
                        {
                            label: this.participantName,
                            data: this.actual,
                            fill: true,
                            backgroundColor: 'rgba(93, 176, 16, 0.45)', // SPSP Green Layer
                            borderColor: '#5db010',
                            pointBackgroundColor: '#8fd006',
                            pointBorderColor: '#ffffff',
                            borderWidth: 2.5,
                            pointRadius: 4.5,
                            pointHoverRadius: 6,
                            tension: 0.1
                        },
                        {
                            label: 'Standar Formasi',
                            data: this.standard,
                            fill: true,
                            backgroundColor: 'rgba(181, 5, 5, 0.28)', // SPSP Red Layer
                            borderColor: '#b50505',
                            pointBackgroundColor: '#9a0404',
                            pointBorderColor: '#ffffff',
                            borderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 5.5,
                            tension: 0.1
                        },
                        {
                            label: 'Batas Toleransi ' + this.tolerancePercentage + '%',
                            data: this.tolerance,
                            fill: true,
                            backgroundColor: 'rgba(250, 250, 5, 0.22)', // SPSP Yellow Layer
                            borderColor: '#e6d105',
                            pointBackgroundColor: '#e6d105',
                            pointBorderColor: '#ffffff',
                            borderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 5.5,
                            tension: 0.1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 250
                    },
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
                                    family: '\'Instrument Sans\', system-ui, sans-serif'
                                }
                            },
                            pointLabels: {
                                color: '#171412',
                                font: {
                                    size: 13,
                                    weight: '700',
                                    family: '\'Instrument Sans\', system-ui, sans-serif'
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
        },

        updateChartData() {
            if (!this.chartInstance) return;
            const factor = this.tolerancePercentage > 0 ? (1 - (this.tolerancePercentage / 100)) : 1.0;
            this.tolerance = this.standard.map(val => Number((val * factor).toFixed(2)));

            this.chartInstance.data.labels = this.labels;
            this.chartInstance.data.datasets[0].data = this.actual;
            this.chartInstance.data.datasets[0].label = this.participantName;
            this.chartInstance.data.datasets[1].data = this.standard;
            this.chartInstance.data.datasets[2].label = 'Batas Toleransi ' + this.tolerancePercentage + '%';
            this.chartInstance.data.datasets[2].data = this.tolerance;
            this.chartInstance.update();
        },

        handleToleranceUpdated(detail) {
            const tol = typeof detail === 'object' && detail !== null && 'tolerance' in detail 
                ? Number(detail.tolerance) 
                : Number(detail || 0);
            this.tolerancePercentage = tol;

            const factor = tol > 0 ? (1 - (tol / 100)) : 1.0;
            this.tolerance = this.standard.map(val => Number((val * factor).toFixed(2)));

            const canvas = document.getElementById(this.chartId);
            if (canvas) {
                const chart = Chart.getChart(canvas) || this.chartInstance;
                if (chart) {
                    this.chartInstance = chart;
                    chart.data.datasets[2].label = 'Batas Toleransi ' + tol + '%';
                    chart.data.datasets[2].data = this.tolerance;
                    chart.update();
                } else {
                    this.ensureChart();
                }
            }
        },

        handleTabSwitched(section) {
            if (section === this.sectionCode) {
                setTimeout(() => {
                    const canvas = document.getElementById(this.chartId);
                    if (canvas) {
                        const chart = Chart.getChart(canvas) || this.chartInstance;
                        if (chart) {
                            this.chartInstance = chart;
                            chart.resize();
                            chart.update();
                        } else {
                            this.ensureChart();
                        }
                    }
                }, 40);
            }
        }
    }"
    @tolerance-updated.window="handleToleranceUpdated($event.detail)"
    @hca-tab-switched.window="handleTabSwitched($event.detail.section)"
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
                <!-- Dataset 1: Standar Formasi (Merah) -->
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-md bg-white border border-warm-border shadow-2xs">
                    <span class="w-3 h-3 rounded-full shrink-0" style="background-color: #b50505;"></span>
                    <span class="text-primary-ink font-medium">Standar Formasi</span>
                </div>
                <!-- Dataset 2: Batas Toleransi (Kuning) -->
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-md bg-white border border-warm-border shadow-2xs">
                    <span class="w-3 h-3 rounded-full shrink-0" style="background-color: #fafa05; border: 1px solid #e6d105;"></span>
                    <span class="text-primary-ink font-medium">Batas Toleransi <span x-text="tolerancePercentage + '%'">{{ $tolerancePercentage }}%</span></span>
                </div>
            </div>
        </div>

        <!-- Radar Canvas with wire:ignore to prevent Livewire morph from clearing canvas -->
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
                    <td class="py-3 px-4 text-center font-mono text-slate-500 font-medium bg-amber-50/20">
                        <span x-text="tolerance[{{ $index }}] ? Number(tolerance[{{ $index }}]).toFixed(2) : '{{ number_format($toleranceRatings[$index], 2) }}'">
                            {{ number_format($toleranceRatings[$index], 2) }}
                        </span>
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
                <span>
                    <template x-if="tolerancePercentage > 0">
                        <span>Penilaian menerapkan Ambang Toleransi <strong x-text="tolerancePercentage + '%'">{{ $activeTol }}%</strong> (Ambang kelulusan: ≥ <strong x-text="(100 - tolerancePercentage) + '%'">{{ 100 - $activeTol }}%</strong> Standar Formasi Jabatan).</span>
                    </template>
                    <template x-if="tolerancePercentage === 0">
                        <span>Penilaian menerapkan <strong>Standar Murni (0% Toleransi)</strong> tanpa penyesuaian ambang batas.</span>
                    </template>
                </span>
            </div>
            <div class="font-mono text-[10px] text-slate-500 font-medium shrink-0">
                Formula Gap: Skor Aktual − Standar Jabatan
            </div>
        </div>
    </div>
</div>