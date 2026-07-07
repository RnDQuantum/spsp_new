<div class="w-full max-w-5xl mx-auto bg-white border border-warm-border rounded-xl p-8 md:p-12 print:border-none">
    
    <!-- Section Header -->
    <div class="border-b border-warm-border pb-6 mb-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 block mb-1">Rencana Tindak Lanjut Talenta</span>
            <h2 class="font-display text-2xl md:text-3xl text-primary-ink font-semibold">
                Rekomendasi <span class="text-accent-amber italic">Pengembangan</span>
            </h2>
        </div>
        <!-- Progress Icon -->
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500">Kokus Utama:</span>
            <span class="text-xs font-bold text-accent-amber bg-accent-amber/10 border border-accent-amber/20 px-3 py-1 rounded-md">
                AKSELERASI KEPEMIMPINAN
            </span>
        </div>
    </div>

    <!-- Description Paragraph -->
    <p class="text-xs text-slate-500 leading-relaxed mb-8">
        Analisis komparatif di bawah memetakan modal kekuatan utama yang harus dipertahankan dan area kesenjangan (*gaps*) kompetensi yang perlu diakselerasi melalui program pengembangan taktis.
    </p>

    <!-- Two-Column Split Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-start">
        
        <!-- Left: Strengths -->
        <div class="space-y-6">
            <h3 class="text-xs font-bold uppercase tracking-wider text-primary-ink flex items-center gap-2 pb-3 border-b border-warm-border">
                <i class="fas fa-circle-check text-accent-amber"></i>
                Kekuatan Utama (Keunggulan)
            </h3>
            
            <ul class="space-y-4">
                @foreach ($strengths as $str)
                    <li class="flex items-start gap-3">
                        <span class="w-5 h-5 rounded-full bg-accent-amber/10 text-accent-amber flex items-center justify-center shrink-0 text-[10px] font-bold mt-0.5">
                            ✓
                        </span>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            {!! $str !!}
                        </p>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Right: Development Gaps -->
        <div class="space-y-6 border-t lg:border-t-0 lg:border-l lg:border-warm-border pt-6 lg:pt-0 lg:pl-12">
            <h3 class="text-xs font-bold uppercase tracking-wider text-primary-ink flex items-center gap-2 pb-3 border-b border-warm-border">
                <i class="fas fa-circle-exclamation text-slate-400"></i>
                Area Pengembangan (Peluang)
            </h3>
            
            <ul class="space-y-4">
                @foreach ($gaps as $gap)
                    <li class="flex items-start gap-3">
                        <span class="w-5 h-5 rounded-full bg-warm-ivory border border-warm-border text-slate-500 flex items-center justify-center shrink-0 text-[10px] font-bold mt-0.5">
                            !
                        </span>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            {!! $gap !!}
                        </p>
                    </li>
                @endforeach
            </ul>
        </div>

    </div>
</div>
