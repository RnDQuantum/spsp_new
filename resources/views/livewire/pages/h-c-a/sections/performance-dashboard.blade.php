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
            <span class="text-xs font-semibold text-slate-500">Skor Terakhir ({{ $latestYear }}):</span>
            <span class="text-sm font-bold text-forest-green bg-emerald-50 border border-emerald-100 px-3 py-1 rounded-md font-mono shadow-xs">
                {{ number_format($latestScore, 2) }}%
            </span>
        </div>
    </div>

    <!-- Charts & Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center mb-10">
        <!-- Left: Line Chart (8 cols) -->
        <div class="md:col-span-8 p-6 md:p-8 relative flex flex-col items-center">
            <div class="w-full flex justify-between items-center mb-6">
                <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Tren KPI {{ count($years) }} Tahun</span>
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
                {{ $analysisDesc }}
            </p>
            <div class="p-4 bg-emerald-50/50 border border-emerald-100 rounded-lg text-xs space-y-1.5 shadow-xs">
                <div class="flex justify-between font-semibold">
                    <span class="text-slate-500">Rata-rata {{ count($years) }} Tahun:</span>
                    <span class="text-primary-ink font-mono">{{ number_format($avgKpi, 2) }}%</span>
                </div>
                <div class="flex justify-between font-semibold">
                    <span class="text-slate-500">Pertumbuhan/Tahun:</span>
                    <span class="text-forest-green font-mono">{{ $growthPerYear >= 0 ? '+' : '' }}{{ number_format($growthPerYear, 2) }}%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Snapshot Grid Table (Details of current year) -->
    <div>
        <h3 class="font-display font-semibold text-primary-ink text-sm mb-4">Breakdown Metrik Kinerja (Tahun Buku {{ $latestYear }})</h3>
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

<script>
    (function() {
        function initPerformanceChart_{{ str_replace('-', '_', $chartId) }}() {
            const chartId = '{{ $chartId }}';
            const ctx = document.getElementById(chartId);
            if (!ctx) return;
            if (typeof Chart === 'undefined') return;

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

            // Calculate dynamic min and max based on data values
            const allValues = [...trends, ...benchmarks].map(Number).filter(v => !isNaN(v) && v !== null);
            let dynamicMin = 80;
            let dynamicMax = 100;

            if (allValues.length > 0) {
                const minVal = Math.min(...allValues);
                const maxVal = Math.max(...allValues);

                // Berikan buffer 3-4% ke bawah dan ke atas, dibulatkan ke kelipatan 5 terdekat
                dynamicMin = Math.max(0, Math.floor((minVal - 3) / 5) * 5);
                dynamicMax = Math.ceil((maxVal + 3) / 5) * 5;

                // Pastikan rentang minimal 15% agar visual kurva proporsional dan tidak flat
                if (dynamicMax - dynamicMin < 15) {
                    dynamicMin = Math.max(0, dynamicMin - 5);
                    dynamicMax = dynamicMax + 5;
                }
            }

            const range = dynamicMax - dynamicMin;
            let stepSize = 5;
            if (range > 30 && range <= 60) {
                stepSize = 10;
            } else if (range > 60) {
                stepSize = 20;
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
                            backgroundColor: 'rgba(21, 128, 61, 0.08)',
                            borderWidth: 3,
                            pointBackgroundColor: '#15803d',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            fill: true,
                            tension: 0.25,
                            z: 2,
                            datalabels: {
                                display: true,
                                align: 'top',
                                anchor: 'end',
                                offset: 6,
                                color: '#15803d',
                                backgroundColor: 'rgba(255, 255, 255, 0.92)',
                                borderColor: 'rgba(21, 128, 61, 0.25)',
                                borderWidth: 1,
                                borderRadius: 4,
                                padding: {
                                    top: 2,
                                    bottom: 1,
                                    left: 5,
                                    right: 5
                                },
                                font: {
                                    family: 'Instrument Sans',
                                    weight: '700',
                                    size: 10
                                },
                                formatter: function(value) {
                                    return Number(value).toFixed(2) + '%';
                                }
                            }
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
                            z: 1,
                            datalabels: {
                                display: false
                            }
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    devicePixelRatio: Math.max(window.devicePixelRatio || 1, 3),
                    layout: {
                        padding: {
                            top: 28,
                            bottom: 8,
                            left: 8,
                            right: 12
                        }
                    },
                    plugins: {
                        legend: {
                            display: false // Menggunakan custom SVG legend di header
                        },
                        tooltip: {
                            backgroundColor: 'rgba(23, 20, 18, 0.9)',
                            titleFont: {
                                family: 'Instrument Sans',
                                weight: '600'
                            },
                            bodyFont: {
                                family: 'Instrument Sans'
                            },
                            padding: 10,
                            cornerRadius: 6,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + Number(context.raw).toFixed(2) + '%';
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
                                    weight: '600',
                                    size: 11
                                },
                                padding: 6
                            }
                        },
                        y: {
                            min: dynamicMin,
                            max: dynamicMax,
                            grid: {
                                color: '#f0ebe4',
                                drawBorder: false
                            },
                            ticks: {
                                stepSize: stepSize,
                                color: '#94a3b8',
                                font: {
                                    family: 'Instrument Sans',
                                    size: 10,
                                    weight: '500'
                                },
                                padding: 8,
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initPerformanceChart_{{ str_replace('-', '_', $chartId) }});
        } else {
            initPerformanceChart_{{ str_replace('-', '_', $chartId) }}();
        }

        window.addEventListener('hca-tab-switched', function(e) {
            if (e.detail?.section === 'performance') {
                setTimeout(initPerformanceChart_{{ str_replace('-', '_', $chartId) }}, 30);
            }
        });
    })();
</script>
