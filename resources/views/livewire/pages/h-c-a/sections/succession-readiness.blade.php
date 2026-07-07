<div class="w-full max-w-5xl mx-auto bg-white border border-warm-border rounded-xl p-8 md:p-12 print:border-none">
    
    <!-- Section Header -->
    <div class="border-b border-warm-border pb-6 mb-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 block mb-1">Horizon Kesiapan Suksesi</span>
            <h2 class="font-display text-2xl md:text-3xl text-primary-ink font-semibold">
                Succession <span class="text-accent-amber italic">Readiness</span>
            </h2>
        </div>
        <!-- Target Role Header -->
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500">Jabatan Target Utama:</span>
            <span class="text-xs font-bold text-accent-amber bg-accent-amber/10 border border-accent-amber/20 px-3 py-1 rounded-md">
                VP OF HC DEVELOPMENT
            </span>
        </div>
    </div>

    <!-- Description Paragraph -->
    <p class="text-xs text-slate-500 leading-relaxed mb-8">
        Horizon suksesi memetakan estimasi waktu kesiapan kandidat untuk memegang tanggung jawab yang lebih tinggi, serta posisi spesifik yang ditargetkan beserta tingkat keyakinan kesiapan.
    </p>

    <!-- Succession Horizon Stack (Progressive) -->
    <div class="relative pl-6 md:pl-8 border-l border-warm-border space-y-12">
        @foreach ($horizons as $index => $horizon)
            <div class="relative">
                <!-- Timeline Dot Indicator -->
                <div class="absolute -left-[31px] md:-left-[39px] top-1.5 w-4 h-4 rounded-full bg-white border-2 border-accent-amber flex items-center justify-center">
                    <span class="w-1.5 h-1.5 rounded-full bg-accent-amber"></span>
                </div>

                <!-- Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">
                    
                    <!-- Left: Timeframe and Confidence Percent (4 cols) -->
                    <div class="lg:col-span-4">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Horizon {{ $index + 1 }}</span>
                        <h3 class="font-display font-bold text-sm text-primary-ink mt-0.5">{{ $horizon['timeframe'] }}</h3>
                        
                        <div class="flex items-center gap-3 mt-2">
                            <span class="text-[11px] font-mono font-bold text-accent-amber bg-accent-amber/10 px-2 py-0.5 rounded border border-accent-amber/20">
                                {{ $horizon['status'] }}
                            </span>
                            <span class="text-[11px] font-mono font-bold text-primary-ink">
                                {{ $horizon['percentage'] }}% Kesiapan
                            </span>
                        </div>
                    </div>

                    <!-- Right: Target Position & Specific Plan (8 cols) -->
                    <div class="lg:col-span-8 bg-warm-ivory border border-warm-border rounded-xl p-6">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Target Jabatan Suksesi</span>
                        <h4 class="font-display font-bold text-primary-ink text-sm mb-3">
                            {{ $horizon['role'] }}
                        </h4>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            {{ $horizon['desc'] }}
                        </p>
                    </div>

                </div>
            </div>
        @endforeach
    </div>
</div>
