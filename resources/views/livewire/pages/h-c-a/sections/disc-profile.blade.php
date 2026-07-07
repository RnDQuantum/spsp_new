<div class="w-full max-w-5xl mx-auto bg-white border border-warm-border rounded-xl p-8 md:p-12 print:border-none">
    
    <!-- Section Header -->
    <div class="border-b border-warm-border pb-6 mb-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 block mb-1">Gaya Perilaku & Interaksi Kerja</span>
            <h2 class="font-display text-2xl md:text-3xl text-primary-ink font-semibold">
                Profil <span class="text-accent-amber italic">DISC</span>
            </h2>
        </div>
        <!-- Dominant Style Badge -->
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500">Gaya Dominan:</span>
            <span class="text-xs font-bold text-accent-amber bg-accent-amber/10 border border-accent-amber/20 px-3 py-1 rounded-md uppercase tracking-wider">
                {{ $dominantStyle }} (I)
            </span>
        </div>
    </div>

    <!-- Description Paragraph -->
    <p class="text-xs text-slate-500 leading-relaxed mb-8">
        Model DISC mengklasifikasikan perilaku kerja menjadi 4 gaya utama. Analisis gaya dominan di bawah menggambarkan cara terbaik kandidat dalam berinteraksi, menyelesaikan hambatan, dan memotivasi tim kerja.
    </p>

    <!-- DISC 2x2 Matrix Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        @foreach ($quadrants as $quad)
            @if ($quad['isDominant'])
                <!-- Highlighted Dominant Quadrant -->
                <div class="bg-accent-amber/5 border-2 border-accent-amber rounded-xl p-6 relative shadow-sm transition-all duration-300 hover:scale-[1.01]">
                    <div class="absolute top-6 right-6 font-display font-extrabold text-3xl text-accent-amber opacity-60">
                        {{ $quad['code'] }}
                    </div>
                    
                    <span class="text-[10px] font-bold uppercase tracking-wider text-accent-amber bg-accent-amber/10 border border-accent-amber/20 px-2 py-0.5 rounded">
                        Dominant Style
                    </span>
                    
                    <h3 class="font-display font-bold text-primary-ink text-base mt-3">
                        {{ $quad['name'] }}
                    </h3>
                    <span class="text-xs font-semibold text-slate-500 block mb-3">{{ $quad['label'] }}</span>
                    
                    <p class="text-xs text-slate-600 leading-relaxed">
                        {{ $quad['desc'] }}
                    </p>
                </div>
            @else
                <!-- Muted / Normal Quadrants -->
                <div class="bg-white border border-warm-border rounded-xl p-6 relative transition-all duration-300 hover:scale-[1.01]">
                    <div class="absolute top-6 right-6 font-display font-extrabold text-3xl text-slate-300">
                        {{ $quad['code'] }}
                    </div>
                    
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 bg-warm-ivory border border-warm-border px-2 py-0.5 rounded">
                        Secondary Style
                    </span>
                    
                    <h3 class="font-display font-bold text-primary-ink text-base mt-3">
                        {{ $quad['name'] }}
                    </h3>
                    <span class="text-xs font-semibold text-slate-400 block mb-3">{{ $quad['label'] }}</span>
                    
                    <p class="text-xs text-slate-500 leading-relaxed">
                        {{ $quad['desc'] }}
                    </p>
                </div>
            @endif
        @endforeach
    </div>

    <!-- Section Bottom Info -->
    <div class="bg-warm-ivory border border-warm-border rounded-xl p-6 text-xs text-slate-500 leading-relaxed flex items-start gap-3">
        <i class="fas fa-circle-question text-accent-amber text-sm mt-0.5"></i>
        <span>
            <strong>Interpretasi Gaya Kerja (Influence):</strong> Budi Santoso sangat mahir mempengaruhi lingkungan kerjanya secara persuasif dan membangun jejaring. Gaya ini sangat prima untuk peran kepemimpinan yang membutuhkan kolaborasi dinamis lintas unit kerja.
        </span>
    </div>
</div>
