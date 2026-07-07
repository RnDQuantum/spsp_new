<div 
    id="radar-container-{{ $chartId }}"
    data-labels="{{ json_encode($labels) }}"
    data-actual="{{ json_encode($actualRatings) }}"
    data-standard="{{ json_encode($standardRatings) }}"
    data-tolerance="{{ json_encode($toleranceRatings) }}"
    class="w-full max-w-5xl mx-auto bg-white border border-warm-border rounded-xl p-8 md:p-12 print:border-none"
>
    <!-- Section Header -->
    <div class="border-b border-warm-border pb-6 mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
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

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center mb-8">
        <div class="lg:col-span-5 flex flex-col items-center text-center lg:text-left lg:items-start space-y-6">
            <div class="relative w-48 h-48 flex items-center justify-center">
                <svg class="w-full h-full transform -rotate-90">
                    <circle cx="96" cy="96" r="80" stroke="#f0ebe4" stroke-width="12" fill="transparent"></circle>
                    <circle cx="96" cy="96" r="80" stroke="#b45309" stroke-width="12" fill="transparent" 
                            stroke-dasharray="502.4" stroke-dashoffset="{{ 502.4 * (1 - $talentIndexPercent / 100) }}" stroke-linecap="round"></circle>
                </svg>
                <div class="absolute flex flex-col items-center justify-center">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Score Index</span>
                    <span class="text-4xl md:text-5xl font-extrabold text-primary-ink leading-none tracking-tight">{{ number_format($talentIndex, 2) }}</span>
                    <span class="text-[11px] font-semibold text-slate-500 mt-1">out of 5.00</span>
                </div>
            </div>
            <div class="space-y-3">
                <div class="inline-block text-xs font-bold text-accent-amber bg-accent-amber/10 border border-accent-amber/20 px-2.5 py-0.5 rounded">
                    Talent Category: {{ $talentCategory }} ({{ $talentIndexPercent }}%)
                </div>
                <p class="text-sm text-slate-600">{{ $desc }}</p>
            </div>
        </div>

        <!-- Right: Radar Chart -->
        <div class="lg:col-span-7 p-6 md:p-8 relative flex flex-col items-center">
            <div class="w-full flex justify-between items-center mb-6">
                <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Visualisasi 5 Pilar</span>
                <!-- Legend: kotak solid, bukan garis, karena chart sekarang solid-filled -->
                <div class="flex items-center gap-3 text-[11px] font-semibold text-slate-600">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm" style="background:#166534"></span>
                        Aktual
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm" style="background:#b91c1c"></span>
                        Standar
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-sm" style="background:#eab308"></span>
                        Toleransi
                    </span>
                </div>
            </div>

            <div class="relative w-full h-[320px]" wire:ignore>
                <canvas id="{{ $chartId }}"></canvas>
            </div>
        </div>
    </div>

    <div class="mt-8">
        <x-hca-table :headers="[
            ['label' => 'Pilar Human Capital', 'class' => 'w-1/3'],
            ['label' => 'Standar Min.', 'class' => 'text-center'],
            ['label' => 'Batas Toleransi', 'class' => 'text-center'],
            ['label' => 'Skor Aktual', 'class' => 'text-center'],
            ['label' => 'Deviasi/Gap', 'class' => 'text-right']
        ]">
            @foreach ($labels as $index => $label)
                <tr class="hover:bg-warm-ivory/50 transition-colors">
                    <td class="py-3 px-4 font-semibold text-primary-ink">{{ $label }}</td>
                    <td class="py-3 px-4 text-center font-mono">{{ number_format($standardRatings[$index], 2) }}</td>
                    <td class="py-3 px-4 text-center font-mono text-slate-400">{{ number_format($toleranceRatings[$index], 2) }}</td>
                    <td class="py-3 px-4 text-center font-mono font-bold text-forest-green bg-emerald-50/30">{{ number_format($actualRatings[$index], 2) }}</td>
                    <td class="py-3 px-4 text-right font-mono font-semibold {{ $actualRatings[$index] >= $standardRatings[$index] ? 'text-forest-green' : 'text-rust-red' }}">
                        @php $gap = $actualRatings[$index] - $standardRatings[$index]; @endphp
                        {{ $gap >= 0 ? '+' : '' }}{{ number_format($gap, 2) }}
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

        const el = document.getElementById('radar-container-' + chartId);
        if (!el) return;

        const labels = JSON.parse(el.dataset.labels);
        const actual = JSON.parse(el.dataset.actual);
        const standard = JSON.parse(el.dataset.standard);   // standar penuh (mis. 3.00) -> jadi lapisan Toleransi (terluar)
        const tolerance = JSON.parse(el.dataset.tolerance); // standar - toleransi% (mis. 2.70) -> jadi lapisan Standar (dalam)

        const existingChart = Chart.getChart(ctx);
        if (existingChart) existingChart.destroy();

        new Chart(ctx, {
    type: 'radar',
    data: {
        labels: labels,
        datasets: [
            {
                label: 'Aktual',
                data: actual,
                fill: true,
                backgroundColor: 'rgba(21, 128, 61, 0.55)',   // hijau, semi-transparan
                borderColor: '#166534',
                pointBackgroundColor: '#166534',
                pointBorderColor: '#ffffff',
                borderWidth: 2.5,
                pointRadius: 4,
                pointBorderWidth: 1.5,
                tension: 0.1
            },
            {
                label: 'Standar',
                data: tolerance,
                fill: true,
                backgroundColor: 'rgba(185, 28, 28, 0.45)',   // merah, semi-transparan
                borderColor: '#991b1b',
                pointBackgroundColor: '#991b1b',
                pointBorderColor: '#ffffff',
                borderWidth: 2,
                pointRadius: 4,
                pointBorderWidth: 1.5,
                tension: 0.1
            },
            {
                label: 'Toleransi',
                data: standard,
                fill: true,
                backgroundColor: 'rgba(234, 179, 8, 0.35)',   // amber, semi-transparan
                borderColor: '#ca8a04',
                pointBackgroundColor: '#ca8a04',
                pointBorderColor: '#ffffff',
                borderWidth: 1.5,
                pointRadius: 4,
                pointBorderWidth: 1.5,
                tension: 0.1
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            datalabels: { display: false },
            tooltip: {
                callbacks: {
                    label: (context) => context.dataset.label + ': ' + context.raw.toFixed(2)
                }
            }
        },
        scales: {
            r: {
                min: 0,
                max: 5,
                ticks: { display: false, stepSize: 1 },
                grid: { color: 'rgba(23, 20, 18, 0.08)' },
                angleLines: { color: 'rgba(23, 20, 18, 0.08)' },
                pointLabels: {
                    color: '#171412',
                    font: { size: 11, weight: '600', family: 'Instrument Sans' }
                }
            }
        }
    },
    plugins: [
        // Plugin 1: gambar angka skala manual (biar tetap kebaca di atas warna)
        {
            id: 'hcaShiftTicks_' + chartId,
            afterDraw: (chart) => {
                const { ctx, scales } = chart;
                const scale = scales.r;
                const yCenter = scale.yCenter;
                const xCenter = scale.xCenter;

                ctx.save();
                ctx.font = "600 9px 'Instrument Sans', sans-serif";
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';

                for (let value = 1; value <= scale.max; value++) {
                    const radius = scale.getDistanceFromCenterForValue(value);
                    const labelY = yCenter - radius;
                    const labelX = xCenter + 12;
                    const text = String(value);
                    const metrics = ctx.measureText(text);
                    ctx.fillStyle = 'rgba(255, 255, 255, 0.8)';
                    ctx.fillRect(labelX - metrics.width / 2 - 2, labelY - 6, metrics.width + 4, 12);
                    ctx.fillStyle = 'rgba(23, 20, 18, 0.6)';
                    ctx.fillText(text, labelX, labelY);
                }
                ctx.restore();
            }
        },
        // Plugin 2: paksa gambar ulang SEMUA titik di lapisan paling atas
        // supaya titik Standar & Toleransi tidak pernah tertutup area Aktual
        {
            id: 'hcaPointsOnTop_' + chartId,
            afterDatasetsDraw: (chart) => {
                const { ctx } = chart;
                chart.data.datasets.forEach((dataset, datasetIndex) => {
                    const meta = chart.getDatasetMeta(datasetIndex);
                    if (!meta.visible) return;
                    meta.data.forEach((point) => {
                        ctx.save();
                        ctx.beginPath();
                        ctx.arc(point.x, point.y, dataset.pointRadius || 3, 0, Math.PI * 2);
                        ctx.fillStyle = dataset.pointBackgroundColor;
                        ctx.strokeStyle = dataset.pointBorderColor || '#ffffff';
                        ctx.lineWidth = dataset.pointBorderWidth || 1.5;
                        ctx.fill();
                        ctx.stroke();
                        ctx.restore();
                    });
                });
            }
        }
    ]
});
    })();
</script>
@endscript