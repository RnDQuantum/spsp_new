<div class="w-full max-w-5xl mx-auto bg-white border border-warm-border rounded-xl p-8 md:p-12 print:border-none shadow-sm">
    
    <!-- Section Header -->
    <div class="border-b border-warm-border pb-6 mb-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 block mb-1">Konteks Historis & Rekam Jejak</span>
            <h2 class="font-display text-2xl md:text-3xl text-primary-ink font-semibold">
                Riwayat <span class="text-accent-amber italic">Karier</span>
            </h2>
        </div>
        <!-- Total Years Callout -->
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500">Estimasi Masa Kerja Efektif:</span>
            <span class="text-sm font-bold text-primary-ink bg-warm-ivory border border-warm-border px-3 py-1 rounded-md shadow-xs">
                {{ $effectiveTenureYears }} Tahun
            </span>
        </div>
    </div>

    <!-- Vertical Timeline Flow -->
    <div class="relative pl-6 md:pl-8 border-l border-warm-border space-y-12">
        @foreach ($timeline as $index => $item)
            <!-- Timeline Item -->
            <div class="relative">
                <!-- Bullet Marker -->
                <div class="absolute -left-[31px] md:-left-[39px] top-1.5 w-4 h-4 rounded-full bg-white border-4 {{ ($item['is_current'] ?? false) ? 'border-accent-amber' : 'border-slate-300' }} shadow-sm flex items-center justify-center">
                    @if ($item['is_current'] ?? false)
                        <!-- Active Pulse for Current Position -->
                        <span class="absolute w-6 h-6 rounded-full bg-accent-amber/20 animate-ping"></span>
                    @endif
                </div>

                <!-- Content Block (Asymmetrical Period & Details) -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <!-- Left: Period & Department (4 cols) -->
                    <div class="md:col-span-4 space-y-1">
                        <span class="inline-block text-xs font-bold px-2.5 py-0.5 rounded-full font-mono {{ ($item['is_current'] ?? false) ? 'text-accent-amber bg-accent-amber/10 border border-accent-amber/20' : 'text-slate-500 bg-warm-ivory border border-warm-border' }}">
                            {{ $item['period'] }}
                        </span>
                        <p class="text-[11px] text-slate-500 font-semibold block mt-1 leading-snug">
                            {{ $item['unit'] }}
                        </p>
                    </div>

                    <!-- Right: Title & Bullet Achievements (8 cols) -->
                    <div class="md:col-span-8 space-y-3">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-display font-semibold text-primary-ink text-lg">
                                {{ $item['role'] }}
                            </h3>
                            @if ($item['is_current'] ?? false)
                                <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-md">
                                    Posisi Saat Ini
                                </span>
                            @endif
                        </div>
                        
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
        <i class="fas fa-circle-info text-accent-amber text-sm mt-0.5 shrink-0"></i>
        <span>
            <strong>Catatan Verifikasi:</strong> Seluruh riwayat karier di atas diverifikasi melalui Sistem Manajemen SDM instansi. Pencapaian yang dicantumkan merupakan representasi rekam jejak kerja nyata yang mendukung hasil asesmen kompetensi dan potensi.
        </span>
    </div>

</div>
