<div 
    id="radar-container-{{ $chartId }}"
    data-labels="{{ json_encode($labels) }}"
    data-actual="{{ json_encode($actualRatings) }}"
    data-standard="{{ json_encode($standardRatings) }}"
    data-tolerance="{{ json_encode($toleranceRatings) }}"
    class="w-full max-w-4xl mx-auto bg-white border border-slate-200 shadow-xl rounded-2xl p-8 md:p-12 print:border-none print:shadow-none"
>
    
    <!-- Section Header -->
    <div class="border-b border-slate-100 pb-6 mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 block mb-1">Dimensi Utama (Layer 1–3)</span>
            <h2 class="font-display text-2xl md:text-3xl text-slate-charcoal font-semibold">
                Human Capital <span class="text-forest-green italic">Index</span>
            </h2>
        </div>
        <!-- Metric Snapshot -->
        <div class="flex items-center gap-3">
            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Status Kesiapan:</span>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-forest-green border border-emerald-100">
                <i class="fas fa-circle-check mr-1.5 text-xs"></i> Ready for Promotion
            </span>
        </div>
    </div>

    <!-- Main Content Layout (Split Columns) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center mb-8">
        
        <!-- Left: Index Ring & Narrative (5 cols) -->
        <div class="lg:col-span-5 flex flex-col items-center text-center lg:text-left lg:items-start space-y-6">
            
            <!-- Index Radial Ring -->
            <div class="relative w-48 h-48 flex items-center justify-center">
                <!-- SVG Circle Track -->
                <svg class="w-full h-full transform -rotate-90">
                    <circle cx="96" cy="96" r="80" stroke="#f1f5f9" stroke-width="12" fill="transparent"></circle>
                    <circle cx="96" cy="96" r="80" stroke="#15803d" stroke-width="12" fill="transparent" 
                            stroke-dasharray="502.4" stroke-dashoffset="88.4" stroke-linecap="round"></circle> <!-- 82.40% progress -->
                </svg>
                <!-- Inner Score Content -->
                <div class="absolute flex flex-col items-center justify-center">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Score Index</span>
                    <span class="text-4xl md:text-5xl font-extrabold text-slate-charcoal leading-none tracking-tight">4.12</span>
                    <span class="text-[10px] font-semibold text-slate-500 mt-1">out of 5.00</span>
                </div>
            </div>

            <!-- Narrative / Interpretation -->
            <div class="space-y-3">
                <div class="inline-block text-xs font-bold text-forest-green bg-emerald-50 border border-emerald-100 px-2.5 py-0.5 rounded">
                    Talent Category: {{ $talentCategory }} ({{ $talentIndexPercent }}%)
                </div>
                <p class="text-sm text-slate-600">
                    Budi Santoso menunjukkan profil kompetensi dan potensi yang sangat kokoh. Indeks Kinerja dan Potensi berada jauh di atas standar institusi, menandakan kesiapan tinggi untuk peran kepemimpinan berikutnya.
                </p>
            </div>
        </div>

        <!-- Right: Radar Chart (7 cols) -->
        <div class="lg:col-span-7 bg-slate-50 border border-slate-100 rounded-xl p-6 relative flex flex-col items-center">
            
            <!-- Chart Header -->
            <div class="w-full flex justify-between items-center mb-6">
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Visualisasi 5 Pilar</span>
                <!-- Custom SVG Legend -->
                <div class="flex items-center gap-3 text-[10px] font-semibold text-slate-600">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-2" viewBox="0 0 16 8">
                            <line x1="0" y1="4" x2="16" y2="4" stroke="#15803d" stroke-width="2"></line>
                            <circle cx="8" cy="4" r="2" fill="#15803d"></circle>
                        </svg>
                        Aktual
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-2" viewBox="0 0 16 8">
                            <line x1="0" y1="4" x2="16" y2="4" stroke="#b91c1c" stroke-width="1.5"></line>
                        </svg>
                        Standar
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-2" viewBox="0 0 16 8">
                            <line x1="0" y1="4" x2="16" y2="4" stroke="#4b5563" stroke-width="1.5" stroke-dasharray="3 2"></line>
                        </svg>
                        Toleransi
                    </span>
                </div>
            </div>

            <!-- Chart Canvas Container -->
            <div class="relative w-full h-[320px]" wire:ignore>
                <canvas id="{{ $chartId }}"></canvas>
            </div>
        </div>
    </div>

    <!-- Values / Metrics Grid Table (For absolute readability & print fallback) -->
    <div class="mt-8 overflow-hidden border border-slate-200 rounded-xl">
        <table class="w-full border-collapse text-left text-xs">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                    <th class="py-3 px-4 w-1/3">Pilar Human Capital</th>
                    <th class="py-3 px-4 text-center">Standar Min.</th>
                    <th class="py-3 px-4 text-center">Batas Toleransi</th>
                    <th class="py-3 px-4 text-center">Skor Aktual</th>
                    <th class="py-3 px-4 text-right">Deviasi/Gap</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                @foreach ($labels as $index => $label)
                    <tr class="hover:bg-slate-50/55 transition-colors">
                        <td class="py-3 px-4 font-semibold text-slate-800">{{ $label }}</td>
                        <td class="py-3 px-4 text-center font-mono">{{ number_format($standardRatings[$index], 2) }}</td>
                        <td class="py-3 px-4 text-center font-mono text-slate-400">{{ number_format($toleranceRatings[$index], 2) }}</td>
                        <td class="py-3 px-4 text-center font-mono font-bold text-forest-green bg-emerald-50/30">{{ number_format($actualRatings[$index], 2) }}</td>
                        <td class="py-3 px-4 text-right font-mono font-semibold {{ $actualRatings[$index] >= $standardRatings[$index] ? 'text-forest-green' : 'text-rust-red' }}">
                            @php
                                $gap = $actualRatings[$index] - $standardRatings[$index];
                            @endphp
                            {{ $gap >= 0 ? '+' : '' }}{{ number_format($gap, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

@script
<script>
    (function() {
        const chartId = '{{ $chartId }}';
        const ctx = document.getElementById(chartId);
        if (!ctx) return;

        const el = document.getElementById('radar-container-' + chartId);
        if (!el) return;

        const labels = JSON.parse(el.dataset.labels);
        const actual = JSON.parse(el.dataset.actual);
        const standard = JSON.parse(el.dataset.standard);
        const tolerance = JSON.parse(el.dataset.tolerance);

        // Destroy previous instance if it exists
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
                        label: 'Aktual',
                        data: actual,
                        backgroundColor: 'rgba(21, 128, 61, 0.08)',
                        borderColor: '#15803d',
                        borderWidth: 2.5,
                        pointBackgroundColor: '#15803d',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 1.5,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.1,
                        z: 3
                    },
                    {
                        label: 'Standar',
                        data: standard,
                        backgroundColor: 'transparent',
                        borderColor: '#b91c1c',
                        borderWidth: 1.5,
                        pointBackgroundColor: '#b91c1c',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 1,
                        pointRadius: 3.5,
                        tension: 0.1,
                        z: 2
                    },
                    {
                        label: 'Toleransi',
                        data: tolerance,
                        backgroundColor: 'transparent',
                        borderColor: '#6b7280',
                        borderWidth: 1.5,
                        borderDash: [4, 4],
                        pointBackgroundColor: 'transparent',
                        pointBorderColor: 'transparent',
                        pointRadius: 0,
                        tension: 0.1,
                        z: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    r: {
                        min: 0,
                        max: 5,
                        ticks: {
                            stepSize: 1,
                            display: true,
                            color: '#94a3b8',
                            font: {
                                size: 9,
                                family: 'Instrument Sans'
                            },
                            backdropColor: 'transparent'
                        },
                        grid: {
                            color: '#e2e8f0'
                        },
                        angleLines: {
                            color: '#e2e8f0'
                        },
                        pointLabels: {
                            color: '#1e293b',
                            font: {
                                size: 11,
                                weight: '600',
                                family: 'Instrument Sans'
                            }
                        }
                    }
                }
            }
        });
    })();
</script>
@endscript
