<div class="max-w-[1300px] mx-auto p-3 md:p-4 font-sans text-primary-ink dark:text-neutral-100">
    <div class="bg-white dark:bg-[#171412] p-4 md:p-5 rounded-xl border border-warm-border dark:border-[#25211e] shadow-xs">
        
        {{-- Header Editorial Executive Journal --}}
        <div class="mb-4 pb-4 border-b border-warm-border dark:border-[#25211e]">
            <span class="font-mono-data text-accent-amber font-bold uppercase tracking-widest text-xs block mb-1">
                GENERAL REPORT / EVENT STATISTICS
            </span>
            <h1 class="font-display text-xl md:text-2xl font-bold tracking-tight text-primary-ink dark:text-neutral-100">
                Kurva Distribusi Frekuensi
            </h1>
            <div class="mt-1 text-sm font-bold text-accent-amber font-mono-data">
                Aspek: {{ $aspectName ?: '—' }}
            </div>
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

                {{-- Aspect Filter --}}
                <div class="p-2.5 bg-white dark:bg-[#171412] border border-warm-border dark:border-[#25211e] rounded-lg shadow-xs">
                    @livewire('components.aspect-selector', ['showLabel' => true])
                </div>
            </div>
        </div>

        {{-- Adjustment Indicators --}}
        @if ($selectedTemplate)
            <div class="mb-6 px-4 py-2.5 bg-warm-ivory/60 dark:bg-[#1f1b18]/60 border border-warm-border dark:border-[#25211e] rounded-lg flex flex-wrap justify-center gap-2">
                <x-adjustment-indicator :template-id="$selectedTemplate->id" category-code="potensi" size="sm"
                    custom-label="Standar Potensi Disesuaikan" />
                <x-adjustment-indicator :template-id="$selectedTemplate->id" category-code="kompetensi" size="sm"
                    custom-label="Standar Kompetensi Disesuaikan" />
            </div>
        @endif

        <!-- Full Width Line Chart Area -->
        <div class="w-full h-[480px] p-4 border border-warm-border dark:border-[#25211e] rounded-lg bg-warm-ivory/30 dark:bg-[#1f1b18]/40 mb-6 relative" wire:ignore id="distribution-chart-{{ $chartId }}">
            <canvas id="frekuensiChart-{{ $chartId }}" class="w-full h-full"></canvas>
        </div>

        <!-- Bottom Grid: Tabel Kelas (Kiri) & Metric Cards (Kanan) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center max-w-4xl mx-auto mb-2">
            <!-- Tabel Kelas dan Rentang Nilai -->
            <div class="w-full">
                <div class="rounded-lg overflow-hidden border border-warm-border dark:border-[#25211e]">
                    <table class="w-full border-collapse text-sm text-primary-ink dark:text-neutral-200">
                        <thead>
                            <tr class="bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-100 font-bold">
                                <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-left font-bold">Kelas</th>
                                <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold">Rentang Nilai</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-[#171412]">
                            <tr class="hover:bg-warm-ivory/50 dark:hover:bg-[#1f1b18]/50 transition-colors duration-150">
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-semibold text-primary-ink dark:text-neutral-100">I (Rendah)</td>
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">1.00 - 1.80</td>
                            </tr>
                            <tr class="hover:bg-warm-ivory/50 dark:hover:bg-[#1f1b18]/50 transition-colors duration-150">
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-semibold text-primary-ink dark:text-neutral-100">II (Kurang)</td>
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">1.80 - 2.60</td>
                            </tr>
                            <tr class="hover:bg-warm-ivory/50 dark:hover:bg-[#1f1b18]/50 transition-colors duration-150">
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-semibold text-primary-ink dark:text-neutral-100">III (Cukup)</td>
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">2.60 - 3.40</td>
                            </tr>
                            <tr class="hover:bg-warm-ivory/50 dark:hover:bg-[#1f1b18]/50 transition-colors duration-150">
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-semibold text-primary-ink dark:text-neutral-100">IV (Baik)</td>
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">3.40 - 4.20</td>
                            </tr>
                            <tr class="hover:bg-warm-ivory/50 dark:hover:bg-[#1f1b18]/50 transition-colors duration-150">
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-semibold text-primary-ink dark:text-neutral-100">V (Baik Sekali)</td>
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">4.20 - 5.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Area Standar & Rata-rata Rating -->
            <div class="flex flex-col gap-4 w-full">
                <div class="bg-warm-ivory dark:bg-[#1f1b18] border border-warm-border dark:border-[#25211e] p-5 rounded-lg text-center">
                    <div class="text-xs font-bold uppercase tracking-wider text-primary-ink/70 dark:text-neutral-400">Standar Rating</div>
                    <div class="text-3xl font-bold font-mono-data text-accent-amber mt-2">
                        {{ (int) $standardRating }}
                    </div>
                </div>
                <div class="bg-warm-ivory dark:bg-[#1f1b18] border border-warm-border dark:border-[#25211e] p-5 rounded-lg text-center">
                    <div class="text-xs font-bold uppercase tracking-wider text-primary-ink/70 dark:text-neutral-400">Rata-rata Rating</div>
                    <div class="text-3xl font-bold font-mono-data text-forest-green mt-2">
                        {{ number_format($averageRating, 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        let chartInstances = {};

        function coerceNumbers(arr) {
            return (arr || []).map(v => Number(v) || 0);
        }

        function getIsDark() {
            if (document.documentElement.classList.contains('dark')) return true;
            if (document.documentElement.getAttribute('data-theme') === 'dark') return true;
            if (localStorage.theme === 'light') return false;
            if (localStorage.theme === 'dark') return true;
            return false;
        }

        // 🌙 Dark mode observer
        const observer = new MutationObserver(() => {
            const chartId = `{{ $chartId }}`;
            if (chartInstances[chartId]) {
                chartInstances[chartId].update('none');
            }
        });
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class', 'data-theme'] });

        function renderChart(chartId, labels, data, label, standardRating, averageRating) {
            const canvas = document.getElementById(`frekuensiChart-${chartId}`);
            if (!canvas) {
                return;
            }
            const ctx = canvas.getContext('2d');

            const coerced = coerceNumbers(data);
            const allZero = coerced.every(v => v === 0);

            // Calculate total for percentage
            const total = coerced.reduce((sum, val) => sum + val, 0);

            if (!chartInstances[chartId]) {
                chartInstances[chartId] = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: label,
                            data: coerced,
                            borderColor: '#9a3412',
                            backgroundColor: 'rgba(154, 52, 18, 0.15)',
                            tension: 0.35,
                            fill: true,
                            pointRadius: 6,
                            pointHoverRadius: 8,
                            pointBorderWidth: 2,
                            pointBackgroundColor: '#9a3412',
                            pointBorderColor: '#ffffff',
                            borderWidth: 3,
                            datalabels: {
                                align: 'top',
                                anchor: 'end',
                                offset: 6,
                                formatter: function (value) {
                                    if (total === 0) return '0,00%';
                                    const percentage = (value / total * 100).toFixed(2);
                                    return percentage.replace('.', ',') + '%';
                                }
                            }
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: {
                            padding: {
                                top: 50,
                                right: 30,
                                bottom: 20,
                                left: 20
                            }
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
                                    label: function (context) {
                                        const value = context.parsed.y;
                                        const percentage = total === 0 ? '0,00' : (value / total * 100)
                                            .toFixed(2).replace('.', ',');
                                        return ` Jumlah: ${value} orang (${percentage}%)`;
                                    }
                                }
                            },
                            datalabels: {
                                backgroundColor: '#9a3412',
                                borderRadius: 5,
                                color: '#ffffff',
                                font: {
                                    weight: 'bold',
                                    size: 12,
                                    family: "'Instrument Sans', sans-serif"
                                },
                                padding: {
                                    top: 4,
                                    bottom: 4,
                                    left: 7,
                                    right: 7
                                }
                            }
                        },
                        scales: {
                            x: {
                                border: {
                                    display: true,
                                    color: () => getIsDark() ? 'rgba(255, 255, 255, 0.25)' : 'rgba(23, 20, 18, 0.2)',
                                    width: 1.5
                                },
                                offset: true,
                                grid: {
                                    offset: true,
                                    display: true,
                                    color: () => getIsDark() ? 'rgba(255, 255, 255, 0.18)' : 'rgba(23, 20, 18, 0.12)'
                                },
                                ticks: {
                                    color: () => getIsDark() ? '#f5f5f5' : '#171412',
                                    font: {
                                        size: 13,
                                        weight: '600',
                                        family: "'Instrument Sans', sans-serif"
                                    },
                                    callback: function (value, index) {
                                        const labels = ['I', 'II', 'III', 'IV', 'V'];
                                        const descriptions = ['Rendah', 'Kurang', 'Cukup',
                                            'Baik', 'Baik Sekali'
                                        ];
                                        return [labels[index], descriptions[index]];
                                    }
                                }
                            },
                            y: {
                                min: 0,
                                suggestedMax: allZero ? 1 : Math.ceil(Math.max(...coerced) * 1.2),
                                border: {
                                    display: true,
                                    color: () => getIsDark() ? 'rgba(255, 255, 255, 0.25)' : 'rgba(23, 20, 18, 0.2)',
                                    width: 1.5
                                },
                                grid: {
                                    display: true,
                                    color: () => getIsDark() ? 'rgba(255, 255, 255, 0.18)' : 'rgba(23, 20, 18, 0.12)'
                                },
                                ticks: {
                                    stepSize: 1,
                                    color: () => getIsDark() ? '#f5f5f5' : '#171412',
                                    font: {
                                        size: 12,
                                        family: "'Instrument Sans', sans-serif"
                                    },
                                    callback: function (value) {
                                        return value;
                                    }
                                }
                            }
                        }
                    },
                    plugins: [ChartDataLabels]
                });
            } else {
                const chart = chartInstances[chartId];
                chart.data.labels = labels;
                chart.data.datasets[0].label = label;
                chart.data.datasets[0].data = coerced;
                chart.options.scales.y.suggestedMax = allZero ? 1 : Math.ceil(Math.max(...coerced) * 1.2);
                chart.update('active');
                return;
            }
        }

        function initialRenderFromServer() {
            const initialLabels = ['I', 'II', 'III', 'IV', 'V'];
            const initialData = [
                {{ (int) ($distribution[1] ?? 0) }},
                {{ (int) ($distribution[2] ?? 0) }},
                {{ (int) ($distribution[3] ?? 0) }},
                {{ (int) ($distribution[4] ?? 0) }},
                {{ (int) ($distribution[5] ?? 0) }}
            ];
            const aspectName = `{{ $aspectName }}`;
            const standardRating = {{ $standardRating }};
            const averageRating = {{ $averageRating }};

            renderChart(`{{ $chartId }}`, initialLabels, initialData, aspectName, standardRating,
                averageRating);
        }

        function waitForLivewire(callback) {
            if (window.Livewire) {
                callback();
            } else {
                setTimeout(() => waitForLivewire(callback), 100);
            }
        }

        function onDistributionUpdated(eventData) {
            try {
                const payload = Array.isArray(eventData) && eventData.length > 0 ? eventData[0] : eventData;

                const chartId = payload.chartId;
                if (!chartId || chartId !== `{{ $chartId }}`) {
                    return;
                }

                const labels = payload.labels || ['I', 'II', 'III', 'IV', 'V'];
                const data = Array.isArray(payload.data) ? payload.data : [];
                const label = payload.aspectName || '';
                const standardRating = payload.standardRating || 0;
                const averageRating = payload.averageRating || 0;

                renderChart(chartId, labels, data, label, standardRating, averageRating);
            } catch (e) {
                console.error('distribution-updated render error:', e, eventData);
            }
        }

        waitForLivewire(function () {
            initialRenderFromServer();
            Livewire.on('chartDataUpdated', onDistributionUpdated);
        });

        document.addEventListener('livewire:navigated', function () {
            waitForLivewire(function () {
                initialRenderFromServer();
            });
        });

        document.addEventListener('livewire:navigating', function () {
            const chartId = `{{ $chartId }}`;
            if (chartInstances[chartId]) {
                chartInstances[chartId].destroy();
                delete chartInstances[chartId];
            }
        });
    })();
</script>