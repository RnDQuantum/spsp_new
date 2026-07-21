<div class="max-w-[1400px] mx-auto p-3 md:p-4 font-sans text-primary-ink dark:text-neutral-100">
    <div class="bg-white dark:bg-[#171412] p-4 md:p-5 rounded-xl border border-warm-border dark:border-[#25211e] shadow-xs">
        
        {{-- Header Editorial Executive Journal --}}
        <div class="mb-4 pb-4 border-b border-warm-border dark:border-[#25211e]">
            <span class="font-mono-data text-accent-amber font-bold uppercase tracking-widest text-xs block mb-1">
                GENERAL REPORT / RANKING MAPPING
            </span>
            <h1 class="font-display text-xl md:text-2xl font-bold tracking-tight text-primary-ink dark:text-neutral-100">
                Peringkat Skor <i>Psychology Mapping</i>
            </h1>
        </div>

        {{-- Filter Section --}}
        <div class="mb-6 bg-warm-ivory dark:bg-[#1f1b18] p-4 rounded-xl border border-warm-border dark:border-[#25211e]">
            <div class="flex flex-col gap-3.5">
                {{-- Event Filter --}}
                <div class="p-2.5 bg-white dark:bg-[#171412] border border-warm-border dark:border-[#25211e] rounded-lg shadow-xs">
                    @livewire('components.event-selector', ['showLabel' => true])
                </div>

                {{-- Position Filter --}}
                <div class="p-2.5 bg-white dark:bg-[#171412] border border-warm-border dark:border-[#25211e] rounded-lg shadow-xs">
                    @livewire('components.position-selector', ['showLabel' => true])
                </div>
            </div>
        </div>

    @php $summary = $this->getPassingSummary(); @endphp
    @livewire('components.tolerance-selector', [
        'passing' => $summary['passing'],
        'total' => $summary['total'],
        'showSummary' => false,
    ])

    {{-- Adjustment Indicator --}}
    @php
        $eventData = $this->getEventData();
        $templateId = $eventData['position']->template_id ?? null;
    @endphp
    @if ($templateId)
        <div class="px-6 py-2.5 bg-warm-ivory/60 dark:bg-[#1f1b18]/60 border-b border-warm-border dark:border-[#25211e]">
            <x-adjustment-indicator :template-id="$templateId" category-code="potensi" size="sm" />
        </div>
    @endif

    <!-- Per Page Selector -->
    <div class="mb-4">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <label class="text-xs font-bold uppercase tracking-wider text-primary-ink/70 dark:text-neutral-400">
                    Lihat Data:
                </label>
                <select wire:model.live="perPage"
                    class="border border-warm-border dark:border-[#25211e] rounded-md px-3 py-1.5 text-sm bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-100 focus:ring-1 focus:ring-accent-amber font-sans">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="0">Semua</option>
                </select>
            </div>

            @if ($rankings && $rankings->total() > 0)
                <div class="text-xs text-primary-ink/75 dark:text-neutral-400">
                    Menampilkan
                    <span class="font-mono-data font-bold text-primary-ink dark:text-neutral-200">
                        {{ $rankings->firstItem() ?? 0 }}
                    </span>
                    ke
                    <span class="font-mono-data font-bold text-primary-ink dark:text-neutral-200">
                        {{ $rankings->lastItem() ?? 0 }}
                    </span>
                    dari
                    <span class="font-mono-data font-bold text-primary-ink dark:text-neutral-200">
                        {{ $rankings->total() }}
                    </span>
                    Data
                </div>
            @endif
        </div>
    </div>

    <!-- Table Section -->
    <div class="overflow-x-auto mb-6">
        <table
            class="min-w-full border border-warm-border dark:border-[#25211e] text-sm text-primary-ink dark:text-neutral-200">
            <thead>
                <tr class="bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-100 font-bold">
                    <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-bold text-center w-14" rowspan="2">
                        Peringkat
                    </th>
                    <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-bold text-center w-36" rowspan="2">NIP</th>
                    <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-bold text-left w-52" rowspan="2">Nama</th>
                    <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-bold text-left w-48" rowspan="2">Jabatan</th>
                    <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-bold text-center" colspan="2">
                        <span x-data
                            x-text="$wire.tolerancePercentage > 0 ? 'Standar (-' + $wire.tolerancePercentage + '%)' : 'Standar'"></span>
                    </th>
                    <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-bold text-center" colspan="2">
                        Individu
                    </th>
                    <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-bold text-center" colspan="2">Gap</th>
                    <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-bold text-center w-28" rowspan="2">
                        Persentase<br>Kesesuaian
                    </th>
                    <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-bold text-center w-40" rowspan="2">
                        Kesimpulan
                    </th>
                </tr>
                <tr class="bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-100 font-bold">
                    <th class="border border-warm-border dark:border-[#25211e] px-2 py-2 font-semibold text-center w-16">Rating</th>
                    <th class="border border-warm-border dark:border-[#25211e] px-2 py-2 font-semibold text-center w-16">Skor</th>
                    <th class="border border-warm-border dark:border-[#25211e] px-2 py-2 font-semibold text-center w-16">Rating</th>
                    <th class="border border-warm-border dark:border-[#25211e] px-2 py-2 font-semibold text-center w-16">Skor</th>
                    <th class="border border-warm-border dark:border-[#25211e] px-2 py-2 font-semibold text-center w-16">Rating</th>
                    <th class="border border-warm-border dark:border-[#25211e] px-2 py-2 font-semibold text-center w-16">Skor</th>
                </tr>
            </thead>
            <tbody>
                @if ($rankings && $rankings->count() > 0)
                    @foreach ($rankings as $row)
                        <tr class="hover:bg-warm-ivory/50 dark:hover:bg-[#1f1b18]/50 transition-colors duration-150 text-sm text-primary-ink dark:text-neutral-200">
                            <td class="border border-warm-border dark:border-[#25211e] px-3 py-2 text-center font-mono-data font-bold text-accent-amber">
                                #{{ $row['rank'] }}
                            </td>
                            <td class="border border-warm-border dark:border-[#25211e] px-3 py-2 text-center font-mono-data">
                                {{ $row['nip'] }}
                            </td>
                            <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-left font-semibold text-primary-ink dark:text-neutral-100">
                                {{ $row['name'] }}
                            </td>
                            <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-left">
                                {{ $row['position'] }}
                            </td>
                            <td class="border border-warm-border dark:border-[#25211e] px-2 py-2 text-center font-mono-data">
                                {{ number_format($row['standard_rating'], 2) }}
                            </td>
                            <td class="border border-warm-border dark:border-[#25211e] px-2 py-2 text-center font-mono-data">
                                {{ number_format($row['standard_score'], 2) }}
                            </td>
                            <td class="border border-warm-border dark:border-[#25211e] px-2 py-2 text-center font-mono-data">
                                {{ number_format($row['individual_rating'], 2) }}
                            </td>
                            <td class="border border-warm-border dark:border-[#25211e] px-2 py-2 text-center font-mono-data">
                                {{ number_format($row['individual_score'], 2) }}
                            </td>
                            <td class="border border-warm-border dark:border-[#25211e] px-2 py-2 text-center font-mono-data">
                                {{ number_format($row['gap_rating'], 2) }}
                            </td>
                            <td class="border border-warm-border dark:border-[#25211e] px-2 py-2 text-center font-mono-data">
                                {{ number_format($row['gap_score'], 2) }}
                            </td>
                            <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data font-semibold text-primary-ink dark:text-neutral-100">
                                {{ number_format($row['percentage_score'], 2) }}%
                            </td>
                            <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-semibold text-xs uppercase tracking-wider">
                                @php
                                    $cText = $row['conclusion'] ?? '';
                                    $cKey = ucwords(strtolower(trim($cText)));
                                    $badgeStyle = $conclusionConfig[$cText]['tailwindClass']
                                        ?? ($conclusionConfig[$cKey]['tailwindClass']
                                        ?? 'bg-warm-border/60 dark:bg-[#25211e] text-primary-ink dark:text-neutral-200');
                                @endphp
                                <span class="inline-block px-2.5 py-1 rounded {{ $badgeStyle }}">
                                    {{ $row['conclusion'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="12"
                            class="border border-warm-border dark:border-[#25211e] px-4 py-6 text-center text-primary-ink/60 dark:text-neutral-400">
                            Tidak ada data untuk ditampilkan. Silakan pilih event dan jabatan.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
        @if ($rankings?->hasPages())
            <div class="mt-4">
                {{ $rankings->links(data: ['scrollTo' => false]) }}
            </div>
        @endif
    </div>

    <!-- Standard & Threshold Info Box -->
    @if ($standardInfo)
        <div class="mb-6">
            <div class="bg-warm-ivory dark:bg-[#1f1b18] border border-warm-border dark:border-[#25211e] rounded-lg p-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-primary-ink/70 dark:text-neutral-400 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-calculator text-accent-amber"></i>
                    Informasi Standar
                </h3>

                <div x-data="{ tolerance: $wire.entangle('tolerancePercentage') }" class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <!-- Original Standard -->
                    <div class="bg-white dark:bg-[#171412] border border-warm-border dark:border-[#25211e] rounded-lg p-4">
                        <div class="text-xs font-bold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400 mb-1">Standar</div>
                        <div class="text-3xl font-bold font-mono-data text-accent-amber">
                            {{ number_format($standardInfo['original_standard'], 2) }}
                        </div>
                    </div>

                    <!-- Adjusted Standard - Only show if tolerance > 0 -->
                    <div x-show="tolerance > 0" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 transform scale-95"
                        x-transition:enter-end="opacity-100 transform scale-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 transform scale-100"
                        x-transition:leave-end="opacity-0 transform scale-95"
                        class="bg-white dark:bg-[#171412] border border-warm-border dark:border-[#25211e] rounded-lg p-4">
                        <div class="text-xs font-bold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400 mb-1">
                            Standar Toleransi
                            <span x-text="tolerance > 0 ? '(' + tolerance + '%)' : ''"></span>
                        </div>
                        <div class="text-3xl font-bold font-mono-data text-accent-amber">
                            {{ number_format($standardInfo['adjusted_standard'], 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Summary Statistics Section -->
    @if (!empty($conclusionSummary))
        <div class="pt-6 border-t border-warm-border dark:border-[#25211e] mb-6">
            <h3 class="text-xs font-bold uppercase tracking-wider text-primary-ink/70 dark:text-neutral-400 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-accent-amber"></i>
                Ringkasan Kesimpulan
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                @foreach ($conclusionSummary as $conclusion => $count)
                    @php
                        $totalParticipants = array_sum($conclusionSummary);
                        $percentage = $totalParticipants > 0 ? round(($count / $totalParticipants) * 100, 1) : 0;
                        $cKey = ucwords(strtolower(trim($conclusion)));
                        $badgeStyle = $conclusionConfig[$conclusion]['tailwindClass']
                            ?? ($conclusionConfig[$cKey]['tailwindClass']
                            ?? 'bg-warm-border/60 dark:bg-[#25211e] text-primary-ink dark:text-neutral-200');
                    @endphp

                    <div class="bg-warm-ivory dark:bg-[#1f1b18] border border-warm-border dark:border-[#25211e] rounded-lg p-5 text-center">
                        <div class="text-3xl font-bold font-mono-data text-accent-amber mb-1">{{ $count }}</div>
                        <div class="text-xs font-mono-data font-bold text-primary-ink/75 dark:text-neutral-400 mb-3">{{ $percentage }}%</div>
                        <div>
                            <span class="inline-block px-3 py-1 text-xs uppercase tracking-wider font-bold rounded {{ $badgeStyle }}">
                                {{ $conclusion }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Overall Statistics -->
            @php
                $totalParticipants = array_sum($conclusionSummary);
                $passingCount =
                    ($conclusionSummary['Di Atas Standar'] ?? 0) + ($conclusionSummary['Memenuhi Standar'] ?? 0);
                $passingPercentage = $totalParticipants > 0 ? round(($passingCount / $totalParticipants) * 100, 1) : 0;
            @endphp

            <div class="bg-warm-ivory dark:bg-[#1f1b18] border border-warm-border dark:border-[#25211e] rounded-lg p-5">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center">
                    <div>
                        <div class="text-2xl font-bold font-mono-data text-primary-ink dark:text-neutral-100">{{ $totalParticipants }}</div>
                        <div class="text-xs uppercase tracking-wider font-bold text-primary-ink/60 dark:text-neutral-400 mt-1">Total Peserta</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold font-mono-data text-forest-green">{{ $passingCount }}</div>
                        <div class="text-xs uppercase tracking-wider font-bold text-primary-ink/60 dark:text-neutral-400 mt-1">Lulus</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold font-mono-data text-accent-amber">{{ $passingPercentage }}%</div>
                        <div class="text-xs uppercase tracking-wider font-bold text-primary-ink/60 dark:text-neutral-400 mt-1">Tingkat Kelulusan</div>
                    </div>
                </div>
            </div>

            <!-- Keterangan Rentang Nilai -->
            <div class="mt-4 bg-warm-ivory dark:bg-[#1f1b18] border border-warm-border dark:border-[#25211e] rounded-lg p-4">
                <div class="text-xs text-primary-ink dark:text-neutral-200">
                    <ul class="list-disc ml-5 space-y-2">
                        <li><strong class="inline-block px-2 py-0.5 rounded bg-green-600 text-white text-xs">Di Atas Standar</strong> : Skor Individu ≥ Skor Standar</li>
                        <li><strong class="inline-block px-2 py-0.5 rounded bg-yellow-400 text-gray-900 text-xs">Memenuhi Standar</strong> : Skor Individu ≥ Skor Standar yang telah diberi toleransi</li>
                        <li><strong class="inline-block px-2 py-0.5 rounded bg-red-600 text-white text-xs">Di Bawah Standar</strong> : Skor Individu < Skor Standar maupun Skor Standar yang telah diberi toleransi</li>
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Pie Chart Section -->
    @if (!empty($conclusionSummary))
        <div class="pt-6 border-t border-warm-border dark:border-[#25211e]"
            x-data="{
                refreshChart() {
                    const labels = @js($chartLabels);
                    const data = @js($chartData);
                    const colors = @js($chartColors);
                    if (labels.length > 0 && data.length > 0) {
                        createConclusionChart(labels, data, colors);
                    }
                }
            }" x-init="$nextTick(() => refreshChart())">
            <h3 class="text-lg font-bold font-display text-primary-ink dark:text-neutral-100 mb-6 text-center">
                Capacity Building Psychology Mapping
            </h3>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-center">
                <!-- Chart Section -->
                <div class="border border-warm-border dark:border-[#25211e] p-4 rounded-lg bg-warm-ivory dark:bg-[#1f1b18] flex items-center justify-center min-h-[350px]"
                    wire:ignore>
                    <canvas id="conclusionPieChart" class="w-full max-w-[450px]"></canvas>
                </div>

                <!-- Table Section -->
                <div class="rounded-lg overflow-hidden border border-warm-border dark:border-[#25211e]">
                    <table class="w-full text-sm text-primary-ink dark:text-neutral-200">
                        <thead>
                            <tr class="bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-100 font-bold">
                                <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-left font-bold">
                                    Keterangan
                                </th>
                                <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-bold">
                                    Jumlah
                                </th>
                                <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-bold">
                                    Persentase
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-[#171412]">
                            @foreach ($conclusionSummary as $conclusion => $count)
                                @php
                                    $percentage =
                                        $totalParticipants > 0 ? round(($count / $totalParticipants) * 100, 2) : 0;
                                    $cKey = ucwords(strtolower(trim($conclusion)));
                                    $badgeStyle = $conclusionConfig[$conclusion]['tailwindClass']
                                        ?? ($conclusionConfig[$cKey]['tailwindClass']
                                        ?? 'bg-warm-border/60 dark:bg-[#25211e] text-primary-ink dark:text-neutral-200');
                                @endphp
                                <tr class="hover:bg-warm-ivory/50 dark:hover:bg-[#1f1b18]/50 transition-colors duration-150">
                                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-semibold">
                                        <span class="inline-block px-2.5 py-0.5 rounded text-xs {{ $badgeStyle }}">
                                            {{ $conclusion }}
                                        </span>
                                    </td>
                                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">
                                        {{ $count }} orang
                                    </td>
                                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">
                                        {{ $percentage }}%
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-100 font-bold">
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2">
                                    Jumlah Responden
                                </td>
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">
                                    {{ $totalParticipants }} orang
                                </td>
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">
                                    100.00%
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
    </div>
</div>

<script>
    let conclusionPieChart = null;

    function createConclusionChart(labels, data, colors) {
        const canvas = document.getElementById('conclusionPieChart');
        if (!canvas) return;

        if (conclusionPieChart) {
            conclusionPieChart.destroy();
            conclusionPieChart = null;
        }

        canvas.style.width = '';
        canvas.style.height = '';

        const chartData = {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderColor: '#ffffff',
                borderWidth: 2,
                datalabels: {
                    display: false
                },
                hoverBackgroundColor: colors.map(color => color + 'dd'),
                hoverBorderColor: '#333',
                hoverBorderWidth: 3,
                hoverOffset: 15
            }]
        };

        const config = {
            type: 'pie',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: true,
                layout: {
                    padding: 20
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 800,
                    easing: 'easeInOutQuart'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true,
                        backgroundColor: 'rgba(23, 20, 18, 0.9)',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: {
                            size: 14,
                            weight: 'bold',
                            family: "'Instrument Sans', sans-serif"
                        },
                        bodyFont: {
                            size: 13,
                            family: "'Instrument Sans', sans-serif"
                        },
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(2) : 0;
                                return ` ${label}: ${value} orang (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        };

        conclusionPieChart = new Chart(canvas, config);
    }

    document.addEventListener('DOMContentLoaded', function() {
        Livewire.on('pieChartDataUpdated', function(data) {
            let chartData = Array.isArray(data) && data.length > 0 ? data[0] : data;

            if (chartData && chartData.labels && chartData.data &&
                chartData.labels.length > 0 && chartData.data.length > 0) {
                createConclusionChart(chartData.labels, chartData.data, chartData.colors);
            }
        });
    });
</script>
