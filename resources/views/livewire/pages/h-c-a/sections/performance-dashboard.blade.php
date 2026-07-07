<div 
    id="performance-container-{{ $chartId }}"
    data-years="{{ json_encode($years) }}"
    data-trends="{{ json_encode($kpiTrends) }}"
    data-benchmarks="{{ json_encode($kpiBenchmarks) }}"
    class="w-full max-w-5xl mx-auto bg-white border border-warm-border rounded-xl p-8 md:p-12 print:border-none"
>
    
    <!-- Section Header -->
    <div class="border-b border-warm-border pb-6 mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 block mb-1">Layer 3: Kinerja Aktual</span>
            <h2 class="font-display text-2xl md:text-3xl text-primary-ink font-semibold">
                Performance <span class="text-accent-amber italic">Dashboard</span>
            </h2>
        </div>
        <!-- Latest Score Badge -->
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500">Skor Terakhir (2026):</span>
            <span class="text-sm font-bold text-forest-green bg-emerald-50 border border-emerald-100 px-3 py-1 rounded-md font-mono">
                98.20%
            </span>
        </div>
    </div>

    <!-- Charts & Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center mb-10">
        <!-- Left: Line Chart (8 cols) -->
        <div class="md:col-span-8 p-6 md:p-8 relative flex flex-col items-center">
            <div class="w-full flex justify-between items-center mb-6">
                <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Tren KPI 5 Tahun</span>
                <!-- Custom SVG Legend -->
                <div class="flex items-center gap-3 text-[11px] font-semibold text-slate-600">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-2" viewBox="0 0 16 8">
                            <line x1="0" y1="4" x2="16" y2="4" stroke="#15803d" stroke-width="2.5"></line>
                            <circle cx="8" cy="4" r="2.5" fill="#15803d"></circle>
                        </svg>
                        Aktual KPI
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-2" viewBox="0 0 16 8">
                            <line x1="0" y1="4" x2="16" y2="4" stroke="#6b7280" stroke-width="1.5" stroke-dasharray="3 2"></line>
                        </svg>
                        Target
                    </span>
                </div>
            </div>
            
            <div class="relative w-full h-[240px]" wire:ignore>
                <canvas id="{{ $chartId }}"></canvas>
            </div>
        </div>

        <!-- Right: Trend Summary Text (4 cols) -->
        <div class="md:col-span-4 space-y-4">
            <h3 class="font-display font-semibold text-primary-ink text-base">Analisa Kinerja</h3>
            <p class="text-xs text-slate-600 leading-relaxed">
                Terdapat tren peningkatan kinerja yang stabil dari tahun 2022 hingga 2026. Pencapaian KPI tahun terakhir sebesar <strong class="text-forest-green">98.20%</strong> menandakan konsistensi tinggi dalam mengeksekusi inisiatif strategis di tingkat VP.
            </p>
            <div class="p-4 bg-emerald-50/50 border border-emerald-100 rounded-lg text-xs space-y-1.5">
                <div class="flex justify-between font-semibold">
                    <span class="text-slate-500">Rata-rata 5 Tahun:</span>
                    <span class="text-primary-ink font-mono">95.40%</span>
                </div>
                <div class="flex justify-between font-semibold">
                    <span class="text-slate-500">Pertumbuhan/Tahun:</span>
                    <span class="text-forest-green font-mono">+1.45%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Snapshot Grid Table (Details of current year) -->
    <div>
        <h3 class="font-display font-semibold text-primary-ink text-sm mb-4">Breakdown Metrik Kinerja (Tahun Buku 2026)</h3>
        <x-hca-table :headers="[
            ['label' => 'Metrik KPI', 'class' => 'w-5/12'],
            ['label' => 'Bobot', 'class' => 'text-center'],
            ['label' => 'Target', 'class' => 'text-center'],
            ['label' => 'Realisasi', 'class' => 'text-center'],
            ['label' => 'Status', 'class' => 'text-right']
        ]">
            @foreach ($kpiBreakdown as $row)
                <tr class="hover:bg-warm-ivory/50 transition-colors">
                    <td class="py-3 px-4 font-semibold text-primary-ink">{{ $row['metric'] }}</td>
                    <td class="py-3 px-4 text-center font-mono text-slate-500">{{ $row['weight'] }}</td>
                    <td class="py-3 px-4 text-center font-mono">{{ $row['target'] }}</td>
                    <td class="py-3 px-4 text-center font-mono font-bold text-forest-green">{{ $row['actual'] }}</td>
                    <td class="py-3 px-4 text-right">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border {{ $row['statusClass'] }}">
                            {{ $row['status'] }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </x-hca-table>
    </div>

</div>

@script
<script>
    (function() {
        const chartId = '{{ $chartId }}';
        const ctx = document.getElementById(chartId);
        if (!ctx) return;

        const el = document.getElementById('performance-container-' + chartId);
        if (!el) return;

        const years = JSON.parse(el.dataset.years);
        const trends = JSON.parse(el.dataset.trends);
        const benchmarks = JSON.parse(el.dataset.benchmarks);

        // Destroy previous instance if it exists
        const existingChart = Chart.getChart(ctx);
        if (existingChart) {
            existingChart.destroy();
        }

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: years,
                datasets: [
                    {
                        label: 'Aktual KPI',
                        data: trends,
                        borderColor: '#15803d',
                        backgroundColor: 'rgba(21, 128, 61, 0.05)',
                        borderWidth: 3,
                        pointBackgroundColor: '#15803d',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        fill: true,
                        tension: 0.25,
                        z: 2
                    },
                    {
                        label: 'Target',
                        data: benchmarks,
                        borderColor: '#94a3b8',
                        borderWidth: 1.5,
                        borderDash: [5, 4],
                        pointRadius: 0,
                        fill: false,
                        tension: 0,
                        z: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false // We use our own custom SVG legend
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw.toFixed(2) + '%';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#64748b',
                            font: {
                                family: 'Instrument Sans',
                                weight: '500'
                            }
                        }
                    },
                    y: {
                        min: 85,
                        max: 100,
                        grid: {
                            color: '#f0ebe4'
                        },
                        ticks: {
                            stepSize: 5,
                            color: '#94a3b8',
                            font: {
                                family: 'Instrument Sans',
                                size: 9
                            },
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    })();
</script>
@endscript
