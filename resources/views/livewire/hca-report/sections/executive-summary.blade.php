<div class="w-full max-w-5xl mx-auto bg-white border border-warm-border rounded-xl p-8 md:p-12 print:border-none">
    
    <!-- Section Header -->
    <div class="border-b border-warm-border pb-6 mb-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 block mb-1">Snapshot Hasil Evaluasi Utama</span>
            <h2 class="font-display text-2xl md:text-3xl text-primary-ink font-semibold">
                Ringkasan <span class="text-accent-amber italic">Eksekutif</span>
            </h2>
        </div>
        <!-- Total Score Snapshot -->
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500">Status Kesiapan:</span>
            <span class="text-xs font-bold text-accent-amber bg-accent-amber/10 border border-accent-amber/20 px-3 py-1 rounded-md">
                READY FOR PROMOTION
            </span>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch mb-8">
        
        <!-- Left Composite Rating Box (5 cols) -->
        <div class="lg:col-span-5 flex flex-col justify-center items-center p-8 bg-warm-ivory border border-warm-border rounded-xl text-center">
            <span class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">Talent Index Score</span>
            <div class="text-6xl font-extrabold text-primary-ink font-display tracking-tight leading-none mb-2">
                {{ number_format($talentIndex, 2) }}
            </div>
            <span class="text-xs font-semibold text-slate-500 mb-4">Skala Maksimum: 5.00</span>
            
            <div class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold bg-accent-amber text-white shadow-sm">
                {{ $talentCategory }}
            </div>
            
            <p class="text-xs text-slate-500 leading-relaxed mt-6">
                Skor gabungan terbobot yang merepresentasikan keselarasan antara kompetensi manajerial, potensi pertumbuhan kepemimpinan, dan konsistensi kinerja aktual kandidat.
            </p>
        </div>

        <!-- Right 5 Pillars List (7 cols) -->
        <div class="lg:col-span-7 flex flex-col justify-between">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-2">
                <i class="fas fa-list-check text-accent-amber"></i>
                Pilar Evaluasi Asesmen
            </h3>

            <div class="space-y-4">
                @foreach ($pillars as $pillar)
                    <div class="flex items-center justify-between py-3 border-b border-warm-border/50">
                        <div class="flex items-center gap-3">
                            <span class="w-2 h-2 rounded-full bg-accent-amber"></span>
                            <span class="text-xs font-bold text-primary-ink">{{ $pillar['name'] }}</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <!-- Horizontal track -->
                            <div class="hidden sm:block w-32 h-2 bg-warm-ivory border border-warm-border rounded-full overflow-hidden">
                                <div class="h-full bg-accent-amber rounded-full" style="width: {{ ($pillar['rating'] / 5) * 100 }}%"></div>
                            </div>
                            <span class="text-xs font-semibold text-slate-500 bg-warm-ivory px-2 py-0.5 rounded border border-warm-border inline-block w-24 text-center">
                                {{ $pillar['label'] }}
                            </span>
                            <span class="text-xs font-mono font-bold text-primary-ink w-8 text-right">
                                {{ number_format($pillar['rating'], 2) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="text-[11px] text-slate-400 italic leading-normal mt-4">
                * Kategori penomoran didasarkan pada skala penilaian standar institusi. Rincian data dukung pilar selengkapnya dapat diakses pada halaman khusus masing-masing aspek.
            </p>
        </div>

    </div>
</div>
