<div class="min-h-screen bg-warm-ivory font-sans text-primary-ink leading-relaxed">
    @if ($printMode)
        <!-- PRINT FLAT VIEW -->
        <div class="print-container bg-white p-0">
            <!-- 01 Cover -->
            <div class="page-break">
                <livewire:hca-report.sections.cover />
            </div>

            <!-- 04 HCI -->
            <div class="page-break p-8">
                <livewire:hca-report.sections.index-radar-section />
            </div>

            <!-- 06 Riwayat Karier -->
            <div class="page-break p-8">
                <livewire:hca-report.sections.timeline-section />
            </div>

            <!-- 15 Performance Dashboard -->
            <div class="page-break p-8">
                <livewire:hca-report.sections.performance-dashboard />
            </div>

            <!-- 20 Kekuatan Psikologis -->
            <div class="p-8">
                <livewire:hca-report.sections.qualitative-list-section />
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Auto trigger print when printMode is active
                setTimeout(() => {
                    window.print();
                }, 800);
            });

            // Restore normal view after printing
            window.onafterprint = function() {
                @this.togglePrintMode(false);
            };
        </script>
    @else
        <!-- WEB INTERACTIVE VIEW -->
        <div class="flex flex-col md:flex-row min-h-screen">
            <!-- Left Sidebar (TOC) -->
            <aside class="w-full md:w-80 bg-primary-ink text-slate-200 flex flex-col border-r border-warm-border/10 shrink-0">
                <!-- Branded Header -->
                <div class="p-6 border-b border-warm-border/10 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-accent-amber flex items-center justify-center font-display font-bold text-white text-xl shadow-md">
                        HC
                    </div>
                    <div>
                        <h1 class="font-display font-semibold tracking-wide text-lg text-white">HCA Report</h1>
                        <p class="text-xs text-slate-400 font-medium uppercase tracking-widest">SPSP Assessment</p>
                    </div>
                </div>

                <!-- Participant Brief Profile -->
                <div class="p-6 border-b border-warm-border/10 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-slate-700 overflow-hidden border-2 border-accent-amber flex items-center justify-center font-display font-bold text-white text-lg">
                        BS
                    </div>
                    <div class="overflow-hidden">
                        <h2 class="font-semibold text-white text-sm truncate">Budi Santoso, M.B.A.</h2>
                        <p class="text-xs text-slate-400 truncate">VP of Talent Development</p>
                        <span class="inline-flex mt-1 items-center px-1.5 py-0.5 rounded text-xs font-medium bg-accent-amber/20 text-accent-amber border border-accent-amber/30">
                            Active Talent
                        </span>
                    </div>
                </div>

                <!-- TOC Sections list -->
                <nav class="flex-1 overflow-y-auto p-4 space-y-6 scrollbar-hidden">
                    @foreach ($menuGroups as $group)
                        <div>
                            <span class="px-3 text-[11px] font-semibold tracking-wide text-slate-400 flex items-center gap-2 mb-2">
                                <i class="fas {{ $group['icon'] }} text-accent-amber text-xs w-4"></i>
                                {{ $group['title'] }}
                            </span>
                            <ul class="space-y-1">
                                @foreach ($group['sections'] as $section)
                                    <li>
                                        @if ($section['active'])
                                            <button 
                                                wire:click="setSection('{{ $section['code'] }}')"
                                                class="w-full text-left px-3 py-2 rounded-md text-xs font-medium transition-all duration-200 flex items-center justify-between {{ $activeSection === $section['code'] ? 'bg-accent-amber text-white shadow-sm' : 'text-slate-300 hover:bg-[#2c2724] hover:text-white' }}"
                                            >
                                                <span>{{ $section['label'] }}</span>
                                                 <i class="fas fa-chevron-right text-xs opacity-60"></i>
                                            </button>
                                        @else
                                            <div 
                                                class="w-full text-left px-3 py-2 rounded-md text-xs font-medium text-slate-500 cursor-not-allowed flex items-center justify-between group"
                                                title="Tersedia di fase berikutnya (Fase B/C)"
                                            >
                                                <span>{{ $section['label'] }}</span>
                                                <div class="flex items-center gap-1">
                                                    <span class="text-[11px] bg-slate-700 text-slate-400 px-1.5 py-0.5 rounded font-mono scale-90 group-hover:block hidden">DRAFT</span>
                                                    <i class="fas fa-lock text-xs opacity-40"></i>
                                                </div>
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </nav>

                <!-- Footer details -->
                <div class="p-4 border-t border-warm-border/10 text-center text-xs text-slate-500">
                    <p class="font-mono">CONFIDENTIAL &copy; {{ date('Y') }} SPSP</p>
                </div>
            </aside>

            <!-- Right Content Area -->
            <main class="flex-1 flex flex-col min-h-0 bg-warm-ivory">
                <!-- Top Toolbar (Sticky) -->
                <header class="bg-white border-b border-warm-border px-8 py-4 flex items-center justify-between shrink-0 sticky top-0 z-30">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Section Aktif</span>
                        <span class="text-xs font-semibold text-primary-ink/80 bg-warm-ivory px-2.5 py-1 rounded-md border border-warm-border">
                            @switch($activeSection)
                                @case('cover') 01 — Cover Page @break
                                @case('hci') 04 — Human Capital Index @break
                                @case('career') 06 — Riwayat Karier @break
                                @case('performance') 15 — Performance Dashboard @break
                                @case('strengths') 20 — Kekuatan Psikologis @break
                            @endswitch
                        </span>
                    </div>

                    <div class="flex items-center gap-3">
                        <button 
                            wire:click="togglePrintMode(true)"
                            class="bg-[#171412] hover:bg-[#2c2724] text-warm-ivory font-medium text-xs px-4 py-2 rounded-md shadow-sm transition-all duration-200 flex items-center gap-2 cursor-pointer"
                        >
                            <i class="fas fa-print"></i>
                            Cetak PDF
                        </button>
                    </div>
                </header>

                <!-- Scrollable Content Frame -->
                <div class="flex-1 overflow-y-auto p-8 md:p-12 scrollbar-hidden">
                    <div class="max-w-5xl mx-auto">
                        <!-- Transition wrapper -->
                        <div class="transition-all duration-300">
                            @switch($activeSection)
                                @case('cover')
                                    <livewire:hca-report.sections.cover />
                                    @break
                                @case('hci')
                                    <livewire:hca-report.sections.index-radar-section />
                                    @break
                                @case('career')
                                    <livewire:hca-report.sections.timeline-section />
                                    @break
                                @case('performance')
                                    <livewire:hca-report.sections.performance-dashboard />
                                    @break
                                @case('strengths')
                                    <livewire:hca-report.sections.qualitative-list-section />
                                    @break
                            @endswitch
                        </div>
                    </div>
                </div>
            </main>
        </div>
    @endif

    <style>
        /* Print Stylesheet */
        @media print {
            .page-break {
                page-break-after: always;
                break-after: page;
            }
            body {
                background: white !important;
                color: black !important;
            }
            main, aside, header, .no-print {
                display: none !important;
            }
            .print-container {
                display: block !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
</div>
