<div class="w-full max-w-5xl mx-auto bg-white border border-warm-border rounded-xl p-8 md:p-12 print:border-none">
    
    <!-- Section Header -->
    <div class="border-b border-warm-border pb-6 mb-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 block mb-1">Konteks Historis & Pengalaman</span>
            <h2 class="font-display text-2xl md:text-3xl text-primary-ink font-semibold">
                Riwayat <span class="text-accent-amber italic">Karier</span>
            </h2>
        </div>
        <!-- Total Years Callout -->
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500">Masa Kerja Efektif:</span>
            <span class="text-sm font-bold text-primary-ink bg-warm-ivory border border-warm-border px-3 py-1 rounded-md">
                11 Tahun
            </span>
        </div>
    </div>

    <!-- Vertical Timeline Flow -->
    <div class="relative pl-6 md:pl-8 border-l border-warm-border space-y-12">
        @foreach ($timeline as $index => $item)
            <!-- Timeline Item -->
            <div class="relative">
                <!-- Bullet Marker -->
                <div class="absolute -left-[31px] md:-left-[39px] top-1.5 w-4 h-4 rounded-full bg-white border-4 {{ $index === 0 ? 'border-accent-amber' : 'border-slate-300' }} shadow-sm flex items-center justify-center">
                    @if ($index === 0)
                        <!-- Active Pulse for Current Position -->
                        <span class="absolute w-6 h-6 rounded-full bg-accent-amber/20 animate-ping"></span>
                    @endif
                </div>

                <!-- Content Block (Asymmetrical Period & Details) -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <!-- Left: Period & Department (3 cols) -->
                    <div class="md:col-span-3 space-y-1">
                        <span class="inline-block text-xs font-bold px-2.5 py-0.5 rounded-full font-mono {{ $index === 0 ? 'text-accent-amber bg-accent-amber/10 border border-accent-amber/20' : 'text-slate-500 bg-warm-ivory border border-warm-border' }}">
                            {{ $item['period'] }}
                        </span>
                        <p class="text-[11px] text-slate-500 font-semibold block mt-0.5">
                            {{ $item['unit'] }}
                        </p>
                    </div>

                    <!-- Right: Title & Bullet Achievements (9 cols) -->
                    <div class="md:col-span-9 space-y-3">
                        <h3 class="font-display font-semibold text-primary-ink text-lg">
                            {{ $item['role'] }}
                        </h3>
                        
                        <!-- Achievements List -->
                        <ul class="space-y-2">
                            @foreach ($item['achievements'] as $achievement)
                                <li class="text-xs text-slate-600 flex items-start gap-2.5 leading-relaxed">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400 mt-2 shrink-0"></span>
                                    <span>{{ $achievement }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Timeline Footer (Historical context summary) -->
    <div class="mt-12 bg-warm-ivory border border-warm-border rounded-xl p-6 text-xs text-slate-500 leading-relaxed flex items-start gap-3">
        <i class="fas fa-circle-info text-accent-amber text-sm mt-0.5"></i>
        <span>
            <strong>Catatan Verifikasi:</strong> Seluruh riwayat karier di atas telah diverifikasi secara formal melalui Sistem Manajemen SDM internal. Pencapaian yang dicantumkan bersumber dari hasil Penilaian Kinerja Tahunan (Key Performance Indicators) pada periode terkait.
        </span>
    </div>

</div>
