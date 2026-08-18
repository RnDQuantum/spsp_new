<div class="w-full max-w-5xl mx-auto bg-white border border-warm-border rounded-xl p-8 md:p-12 print:border-none">
    
    <!-- Section Header -->
    <div class="border-b border-warm-border pb-6 mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 block mb-1">Rencana Tindak Lanjut Talenta (IDP)</span>
            <h2 class="font-display text-2xl md:text-3xl text-primary-ink font-semibold">
                Rekomendasi <span class="text-accent-amber italic">Pengembangan</span>
            </h2>
        </div>
        <!-- Progress Focus Badge -->
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500">Fokus Intervensi:</span>
            <span class="text-[11px] font-bold text-accent-amber bg-accent-amber/10 border border-accent-amber/20 px-3 py-1 rounded-md uppercase tracking-wider">
                {{ $focusTheme }}
            </span>
        </div>
    </div>

    <!-- Description Paragraph -->
    <p class="text-xs text-slate-500 leading-relaxed mb-8">
        Rencana Pengembangan Individu (*Individual Development Plan / IDP*) ini dirumuskan berbasis kerangka kerja <strong>70-20-10 Learning Framework</strong> untuk mengkapitalisasi modal kekuatan unggulan (*Signature Strengths*) serta memitigasi area kesenjangan kompetensi kritis (*Critical Gaps*) secara presisi.
    </p>

    <!-- Two-Column Split Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-start">
        
        <!-- Left: Strengths (Kapitalisasi Keunggulan) -->
        <div class="space-y-6">
            <div class="flex items-center justify-between pb-3 border-b border-warm-border">
                <h3 class="text-xs font-bold uppercase tracking-wider text-primary-ink flex items-center gap-2">
                    <i class="fas fa-circle-check text-forest-green"></i>
                    Pilar Kekuatan Utama (Key Strengths)
                </h3>
                <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded">
                    Untuk Dimaksimalkan
                </span>
            </div>
            
            <div class="space-y-4">
                @foreach ($strengths as $str)
                    <div class="bg-warm-ivory/50 border border-warm-border rounded-xl p-5 hover:border-accent-amber/30 transition shadow-sm">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <h4 class="font-display font-semibold text-primary-ink text-sm">
                                {{ $str['aspect'] }}
                            </h4>
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-mono font-bold text-primary-ink bg-white px-2 py-0.5 rounded border border-warm-border">
                                    {{ $str['score'] }}
                                </span>
                                <span class="text-[10px] font-mono font-bold text-forest-green bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">
                                    {{ $str['gap'] }}
                                </span>
                            </div>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            {{ $str['recommendation'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Right: Development Gaps (70-20-10 Actions) -->
        <div class="space-y-6 border-t lg:border-t-0 lg:border-l lg:border-warm-border pt-6 lg:pt-0 lg:pl-8">
            <div class="flex items-center justify-between pb-3 border-b border-warm-border">
                <h3 class="text-xs font-bold uppercase tracking-wider text-primary-ink flex items-center gap-2">
                    <i class="fas fa-circle-exclamation text-accent-amber"></i>
                    Area Prioritas Pengembangan (IDP 70-20-10)
                </h3>
                <span class="text-[10px] font-semibold text-amber-800 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded">
                    Kesenjangan Kritis
                </span>
            </div>
            
            <div class="space-y-5">
                @foreach ($gaps as $gap)
                    <div class="bg-white border border-warm-border rounded-xl p-5 shadow-sm space-y-3">
                        <div class="flex items-center justify-between gap-2 border-b border-warm-border/60 pb-2.5">
                            <h4 class="font-display font-semibold text-primary-ink text-sm">
                                {{ $gap['aspect'] }}
                            </h4>
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-mono font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                                    {{ $gap['score'] }}
                                </span>
                                <span class="text-[10px] font-mono font-bold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200">
                                    {{ $gap['gap'] }}
                                </span>
                            </div>
                        </div>

                        <!-- 70-20-10 Action Matrix -->
                        <div class="space-y-2 text-xs">
                            <!-- 70% Experiential -->
                            <div class="flex items-start gap-2.5 bg-warm-ivory/60 p-2.5 rounded-lg border border-warm-border/50">
                                <span class="text-[10px] font-bold font-mono px-1.5 py-0.5 rounded bg-slate-800 text-white shrink-0">
                                    70%
                                </span>
                                <span class="text-slate-600 leading-snug">
                                    <strong>On-the-Job:</strong> {{ $gap['action_70'] }}
                                </span>
                            </div>

                            <!-- 20% Social Coaching -->
                            <div class="flex items-start gap-2.5 bg-warm-ivory/60 p-2.5 rounded-lg border border-warm-border/50">
                                <span class="text-[10px] font-bold font-mono px-1.5 py-0.5 rounded bg-accent-amber text-white shrink-0">
                                    20%
                                </span>
                                <span class="text-slate-600 leading-snug">
                                    <strong>Coaching & Mentoring:</strong> {{ $gap['action_20'] }}
                                </span>
                            </div>

                            <!-- 10% Formal Education -->
                            <div class="flex items-start gap-2.5 bg-warm-ivory/60 p-2.5 rounded-lg border border-warm-border/50">
                                <span class="text-[10px] font-bold font-mono px-1.5 py-0.5 rounded bg-forest-green text-white shrink-0">
                                    10%
                                </span>
                                <span class="text-slate-600 leading-snug">
                                    <strong>Pelatihan Formal:</strong> {{ $gap['action_10'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- Section Bottom Framework Note -->
    <div class="mt-10 bg-warm-ivory border border-warm-border rounded-xl p-5 text-xs text-slate-500 leading-relaxed flex items-start gap-3">
        <i class="fas fa-graduation-cap text-accent-amber text-sm mt-0.5"></i>
        <span>
            <strong>Metodologi Center for Creative Leadership (CCL 70-20-10):</strong> 70% penguasaan kompetensi diperoleh dari pengalaman penugasan nyata (*action learning*), 20% dari bimbingan dan interaksi sosial (*mentoring & peer feedback*), serta 10% dari program pelatihan formal dan sertifikasi.
        </span>
    </div>

</div>
