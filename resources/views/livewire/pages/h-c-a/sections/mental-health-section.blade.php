<div class="w-full max-w-5xl mx-auto bg-white border border-warm-border rounded-xl p-8 md:p-12 print:border-none">
    
    <!-- Section Header -->
    <div class="border-b border-warm-border pb-6 mb-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 block mb-1">Evaluasi Well-Being & Kesehatan Psikologis</span>
            <h2 class="font-display text-2xl md:text-3xl text-primary-ink font-semibold">
                Kesehatan <span class="text-accent-amber italic">Jiwa</span>
            </h2>
        </div>
        <!-- Status Badge -->
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500">Status Kesehatan:</span>
            <span class="text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-100 px-3 py-1 rounded-md">
                PRIMA & ADAPTIF
            </span>
        </div>
    </div>

    <!-- Description Paragraph -->
    <p class="text-xs text-slate-500 leading-relaxed mb-8">
        Evaluasi ini memantau tingkat well-being psikologis kandidat untuk menjamin kelancaran penugasan jangka panjang dan mendeteksi adanya indikator kelelahan mental (*burnout*).
    </p>

    <!-- Split Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
        
        <!-- Left Side: Wellbeing Score & Sub-scales (7 cols) -->
        <div class="lg:col-span-7 flex flex-col justify-between space-y-6">
            <!-- Overall wellbeing index horizontal bar -->
            <div class="bg-warm-ivory border border-warm-border rounded-xl p-6">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Mental Well-Being Index</span>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xl font-bold text-primary-ink">{{ $wellbeingCategory }}</span>
                    <span class="text-sm font-mono font-bold text-accent-amber">{{ number_format($wellbeingIndex, 2) }} / 5.00</span>
                </div>
                <!-- Track -->
                <div class="w-full h-3 bg-white border border-warm-border rounded-full overflow-hidden">
                    <div class="h-full bg-accent-amber rounded-full" style="width: {{ ($wellbeingIndex / 5) * 100 }}%"></div>
                </div>
            </div>

            <!-- Sub-aspects list -->
            <div class="space-y-4">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Breakdown Dimensi Well-Being</span>
                @foreach ($aspects as $asp)
                    <div class="space-y-1.5 py-2 border-b border-warm-border/30 last:border-0">
                        <div class="flex items-center justify-between text-xs font-bold text-primary-ink">
                            <span>{{ $asp['label'] }}</span>
                            <span class="font-mono text-slate-500">{{ number_format($asp['value'], 2) }}</span>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-normal">{{ $asp['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Right Side: Narrative / Clinical Notes (5 cols) -->
        <div class="lg:col-span-5 flex flex-col justify-center bg-warm-ivory border border-warm-border rounded-xl p-8">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-3 flex items-center gap-1.5">
                <i class="fas fa-file-medical text-accent-amber text-xs"></i>
                Catatan Psikolog
            </span>
            <p class="text-xs text-slate-600 leading-relaxed">
                {{ $clinicalComment }}
            </p>
            
            <div class="mt-6 border-t border-warm-border pt-4 text-[11px] text-slate-400">
                <span>Diperiksa & divalidasi oleh:</span>
                <span class="block font-bold text-slate-500 mt-0.5">SPSP Clinical Psychology Division</span>
            </div>
        </div>

    </div>
</div>
