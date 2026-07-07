<div class="w-full max-w-5xl mx-auto bg-white border border-warm-border rounded-xl p-8 md:p-12 print:border-none">
    
    <!-- Section Header -->
    <div class="border-b border-warm-border pb-6 mb-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 block mb-1">Deteksi Dini & Mitigasi Kepemimpinan</span>
            <h2 class="font-display text-2xl md:text-3xl text-primary-ink font-semibold">
                Indikator <span class="text-accent-amber italic">Risiko</span>
            </h2>
        </div>
        <!-- Overall Callout -->
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500">Tingkat Risiko Total:</span>
            <span class="text-xs font-bold text-slate-600 bg-warm-ivory border border-warm-border px-3 py-1 rounded-md">
                RENDAH (AMBIL PERAN)
            </span>
        </div>
    </div>

    <!-- Description Paragraph -->
    <p class="text-xs text-slate-500 leading-relaxed mb-8">
        Pemantauan indikator risiko kerja ini ditujukan untuk mendeteksi hambatan non-teknis secara berkala demi menjamin stabilitas kepemimpinan di tingkat strategis.
    </p>

    <!-- Indicators List Layout -->
    <div class="space-y-6 mb-8">
        @foreach ($indicators as $ind)
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 py-4 border-b border-warm-border/50 last:border-0">
                <!-- Left text -->
                <div class="space-y-1 max-w-xl">
                    <h3 class="text-xs font-bold text-primary-ink flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                        {{ $ind['label'] }}
                    </h3>
                    <p class="text-xs text-slate-500 leading-normal pl-3.5">
                        {{ $ind['desc'] }}
                    </p>
                </div>

                <!-- Right level badge -->
                <div class="sm:text-right pl-3.5 sm:pl-0">
                    @if ($ind['level'] === 'Rendah')
                        <span class="inline-flex items-center px-3 py-1 rounded text-xs font-bold bg-warm-ivory text-slate-500 border border-warm-border">
                            RENDAH
                        </span>
                    @elseif ($ind['level'] === 'Sedang')
                        <span class="inline-flex items-center px-3 py-1 rounded text-xs font-bold bg-accent-amber/10 text-accent-amber border border-accent-amber/20">
                            SEDANG
                        </span>
                    @else
                        <!-- Muted terracotta/rust red for High risk -->
                        <span class="inline-flex items-center px-3 py-1 rounded text-xs font-bold bg-amber-950/10 text-[#b91c1c] border border-[#b91c1c]/20">
                            TINGGI
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Section Bottom Info (Ethics and validation) -->
    <div class="bg-warm-ivory border border-warm-border rounded-xl p-6 text-xs text-slate-500 leading-relaxed flex items-start gap-3">
        <i class="fas fa-circle-check text-accent-amber text-sm mt-0.5"></i>
        <span>
            <strong>Rekomendasi Penugasan:</strong> Mengingat tingkat risiko total berada pada kategori **Rendah**, kandidat tidak memiliki hambatan psikologis operasional yang berarti. Penugasan baru dapat segera dilaksanakan dengan pendampingan orientasi awal yang standar.
        </span>
    </div>
</div>
