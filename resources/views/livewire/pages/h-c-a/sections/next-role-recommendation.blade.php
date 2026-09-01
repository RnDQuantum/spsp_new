<div class="w-full max-w-5xl mx-auto bg-white border border-warm-border rounded-xl p-8 md:p-12 print:border-none">
    
    <!-- Section Header -->
    <div class="border-b border-warm-border pb-6 mb-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1 flex-wrap">
                <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 block">Rekomendasi Posisi Akhir</span>
                <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-amber-700 bg-amber-50 border border-amber-200/80 px-2 py-0.5 rounded-md">
                    <i class="fas fa-route text-[9px]"></i> Rantai Suksesi 9-Box
                </span>
            </div>
            <h2 class="font-display text-2xl md:text-3xl text-primary-ink font-semibold">
                Rekomendasi <span class="text-accent-amber italic">Peran Berikutnya</span>
            </h2>
        </div>
        <!-- Target Role & Readiness Badges -->
        <div class="flex flex-col sm:items-end gap-1.5">
            <div class="flex items-center gap-2 flex-wrap sm:justify-end">
                @if($isCurated ?? false)
                    <span class="text-[10px] bg-accent-amber/15 text-accent-amber border border-accent-amber/30 px-2 py-0.5 rounded font-bold flex items-center gap-1">
                        <i class="fas fa-stamp text-[9px]"></i> Kurasi Suksesi
                    </span>
                @endif
                <span class="text-xs font-semibold text-slate-500">Target Peran:</span>
                <span class="text-xs font-bold text-accent-amber bg-accent-amber/10 border border-accent-amber/20 px-3 py-1 rounded-md">
                    {{ $recommendedRole }}
                </span>
            </div>
            <span class="text-[11px] font-medium text-slate-500">
                Status: <strong class="text-primary-ink">{{ $readinessStatus }}</strong>
            </span>
        </div>
    </div>

    <!-- Description Paragraph -->
    <p class="text-xs text-slate-500 leading-relaxed mb-8">
        Berdasarkan integrasi pemetaan potensi, kinerja (Talent 9-Box Matrix), dan kesiapan suksesi, kandidat <strong>{{ $participant?->name ?? 'Peserta' }}</strong> diproyeksikan untuk mengemban peran strategis berikut. Peta jalan tindak lanjut di bawah memandu fase transisi agar terarah dan akuntabel.
    </p>

    <!-- Transition Phases Roadmap -->
    <div class="space-y-6">
        @foreach ($phases as $index => $phase)
            <div class="flex flex-col md:flex-row gap-4 md:gap-8 items-stretch">
                <!-- Left: Phase number and timeframe (3 cols) -->
                <div class="w-full md:w-1/4 flex flex-col justify-center bg-warm-ivory border border-warm-border rounded-xl p-6 shrink-0">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Langkah {{ $index + 1 }}</span>
                    <h3 class="font-display font-bold text-xs text-primary-ink mt-0.5">{{ $phase['title'] }}</h3>
                    <span class="text-[11px] font-mono font-bold text-accent-amber mt-2 block">
                        {{ $phase['timeframe'] }}
                    </span>
                </div>

                <!-- Connector Arrow for desktop -->
                <div class="hidden md:flex items-center justify-center text-slate-300">
                    <i class="fas fa-chevron-right text-sm"></i>
                </div>

                <!-- Right: Description (8 cols) -->
                <div class="flex-1 bg-white border border-warm-border rounded-xl p-6 flex flex-col justify-center">
                    <p class="text-xs text-slate-600 leading-relaxed">
                        {{ $phase['desc'] }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>
</div>
