<div class="w-full max-w-5xl mx-auto bg-white border border-warm-border rounded-xl p-8 md:p-12 print:border-none shadow-sm">
    
    <!-- Section Header -->
    <div class="border-b border-warm-border pb-6 mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 block mb-1">Keputusan & Snapshot Hasil Asesmen</span>
            <h2 class="font-display text-2xl md:text-3xl text-primary-ink font-semibold">
                Ringkasan <span class="text-accent-amber italic">Eksekutif</span>
            </h2>
        </div>
        <!-- Total Score Snapshot -->
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500">Status Kesiapan:</span>
            @php
                $statusUpper = strtoupper($readinessStatus ?? 'DISARANKAN');
                $badgeClass = match (true) {
                    str_contains($statusUpper, 'SANGAT') || ($statusUpper === 'DISARANKAN') => 'text-emerald-700 bg-emerald-50 border-emerald-200',
                    str_contains($statusUpper, 'CATATAN') => 'text-amber-700 bg-amber-50 border-amber-200',
                    default => 'text-rose-700 bg-rose-50 border-rose-200',
                };
            @endphp
            <span class="text-xs font-bold border px-3 py-1 rounded-md {{ $badgeClass }}">
                {{ $readinessStatus }}
            </span>
        </div>
    </div>

    <!-- Official Assessment Verdict Banner -->
    <div class="mb-8 p-6 bg-gradient-to-r from-warm-ivory via-white to-warm-ivory border border-warm-border rounded-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6 shadow-2xs">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-accent-amber text-white flex items-center justify-center font-display shadow-sm shrink-0 mt-0.5">
                <i class="fas fa-stamp text-xl"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Keputusan Resmi Asesor</span>
                <h3 class="font-display font-bold text-lg text-primary-ink mt-0.5">
                    Rekomendasi Penugasan: <span class="text-accent-amber">{{ $readinessStatus }}</span>
                </h3>
                <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                    {{ $fitSummary ?? 'Berdasarkan integrasi hasil asesmen kompetensi dan potensi, kandidat memenuhi prasyarat untuk formasi jabatan yang ditargetkan.' }}
                </p>
            </div>
        </div>
        <div class="shrink-0 border-t md:border-t-0 md:border-l border-warm-border pt-4 md:pt-0 md:pl-6 w-full md:w-auto">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Formasi Target</span>
            <span class="text-xs font-bold text-primary-ink block mt-0.5">{{ $targetPosition ?? 'Jabatan Target' }}</span>
        </div>
    </div>

    <!-- 2-Column Strategic Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch mb-8">
        
        <!-- Left: Composite Rating & Person-Job Fit (5 cols) -->
        <div class="lg:col-span-5 flex flex-col justify-between p-8 bg-warm-ivory border border-warm-border rounded-xl text-center shadow-2xs">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-2 block">Composite Talent Index</span>
                <div class="text-6xl font-extrabold text-primary-ink font-display tracking-tight leading-none mb-2">
                    {{ number_format($talentIndex, 2) }}
                </div>
                <span class="text-xs font-semibold text-slate-500 block mb-4">Skala Penilaian: 1.00 – 5.00</span>
                
                <div class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold bg-accent-amber text-white shadow-sm mb-6">
                    <i class="fas fa-award mr-1.5 text-xs"></i> {{ $talentCategory }}
                </div>
            </div>
            
            <div class="pt-6 border-t border-warm-border/60 text-left">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Pedoman Skala Talenta</span>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Indeks komposit merefleksikan perpaduan kapasitas potensi kognitif, kematangan perilaku manajerial, dan rekam jejak capaian kinerja kandidat terhadap standar formasi jabatan.
                </p>
            </div>
        </div>

        <!-- Right: 3 Key Strategic Takeaways (7 cols) -->
        <div class="lg:col-span-7 flex flex-col justify-between space-y-4">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-2">
                    <i class="fas fa-compass text-accent-amber"></i>
                    3 Poin Kunci Pengambilan Keputusan (Executive Takeaways)
                </h3>

                <!-- Takeaway 1: Top Key Strength -->
                <div class="p-4 rounded-xl border border-emerald-200/80 bg-emerald-50/40 mb-3.5">
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            <span class="text-xs font-bold text-emerald-900 uppercase tracking-wider">Kekuatan Utama (Primary Asset)</span>
                        </div>
                        <span class="text-xs font-mono font-bold text-emerald-700 bg-emerald-100/80 px-2 py-0.5 rounded">
                            Skor: {{ number_format($topStrength['score'] ?? 4.00, 2) }}
                        </span>
                    </div>
                    <div class="text-sm font-bold text-primary-ink">
                        {{ $topStrength['name'] ?? 'Kompetensi Manajerial & Orientasi Pelayanan' }}
                    </div>
                    <p class="text-xs text-slate-600 mt-1 leading-normal">
                        Menjadi modalitas terbaik kandidat yang dapat langsung dioptimalkan sebagai keunggulan eksekusi dan teladan tim kerja.
                    </p>
                </div>

                <!-- Takeaway 2: Critical Growth Area -->
                <div class="p-4 rounded-xl border border-amber-200/80 bg-amber-50/40 mb-3.5">
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                            <span class="text-xs font-bold text-amber-900 uppercase tracking-wider">Area Perhatian Kritis (Priority Growth)</span>
                        </div>
                        <span class="text-xs font-mono font-bold text-amber-700 bg-amber-100/80 px-2 py-0.5 rounded">
                            Skor: {{ number_format($criticalGap['score'] ?? 3.00, 2) }}
                        </span>
                    </div>
                    <div class="text-sm font-bold text-primary-ink">
                        {{ $criticalGap['name'] ?? 'Sistematika Kerja & Mitigasi Risiko' }}
                    </div>
                    <p class="text-xs text-slate-600 mt-1 leading-normal">
                        Kebutuhan pengembangan prioritas yang perlu ditindaklanjuti melalui program pendampingan atau penugasan terstruktur (IDP 70-20-10).
                    </p>
                </div>

                <!-- Takeaway 3: Succession Outlook -->
                <div class="p-4 rounded-xl border border-blue-200/80 bg-blue-50/40">
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                            <span class="text-xs font-bold text-blue-900 uppercase tracking-wider">Prospek Suksesi & Mobilitas</span>
                        </div>
                        <span class="text-xs font-bold text-blue-700 bg-blue-100/80 px-2 py-0.5 rounded">
                            Pipeline
                        </span>
                    </div>
                    <div class="text-sm font-bold text-primary-ink">
                        {{ $successionHorizon ?? 'Horizon 1 — Ready Now (Siap Promosi Segera)' }}
                    </div>
                    <p class="text-xs text-slate-600 mt-1 leading-normal">
                        Kesiapan memikul tanggung jawab penugasan peran berikutnya sesuai diagnosa Matriks Talenta (9-Box Grid).
                    </p>
                </div>
            </div>
        </div>

    </div>

    <!-- Cross-Section Reference Footer -->
    <div class="pt-6 border-t border-warm-border flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 text-xs text-slate-500">
        <div class="flex items-center gap-2">
            <i class="fas fa-chart-pie text-accent-amber text-xs"></i>
            <span>
                Visualisasi radar 5 pilar dan keseimbangan modal manusia tersedia secara terperinci pada <strong>Section 04 — Human Capital Index</strong>.
            </span>
        </div>
        <div class="text-slate-400 italic">
            Dokumen Rahasia — SPSP Human Capital Assessment
        </div>
    </div>
</div>
