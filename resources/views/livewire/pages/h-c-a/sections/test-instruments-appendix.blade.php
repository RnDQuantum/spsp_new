<div class="space-y-8">
    {{-- Header & Evidence Layer Context Banner --}}
    <div class="bg-white border border-warm-border rounded-xl p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold uppercase tracking-wider bg-accent-amber/10 text-accent-amber border border-accent-amber/20">
                        Section 24 &bull; Technical Appendix
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-forest-green border border-emerald-100">
                        <i class="fas fa-shield-halved mr-1 text-[11px]"></i> Level 1 Evidence Layer
                    </span>
                </div>
                <h2 class="font-serif text-2xl font-bold text-primary-ink tracking-tight">
                    Laporan Hasil Alat Tes Psikometri & Psikogram
                </h2>
                <p class="text-xs text-primary-ink/70 mt-1 max-w-3xl leading-relaxed">
                    Menyajikan rincian data mentah (*raw scores*), skor terstandar (*Standard Score, Sten, T-Score*), serta deskripsi dimensi dari setiap instrumen psikologi yang diikuti peserta. Dokumen ini berfungsi sebagai bukti audit ilmiah (*audit-proof layer*) yang melandasi evaluasi pada Layer 1 (Kompetensi) dan Layer 2 (Potensi).
                </p>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <div class="bg-warm-ivory border border-warm-border rounded-lg px-4 py-2.5 text-center">
                    <span class="text-xs font-bold uppercase tracking-wider text-primary-ink/60 block">Total Instrumen</span>
                    <span class="font-mono text-xl font-extrabold text-primary-ink">
                        {{ $categoryCounts['all'] }} <span class="text-xs font-normal text-primary-ink/60">Alat Tes</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Interactive Category Filter Tabs (Web View Only) --}}
    <div class="no-print flex flex-wrap gap-2 border-b border-warm-border pb-3">
        @foreach ($categories as $catKey => $catInfo)
            @php
                $count = $categoryCounts[$catKey] ?? 0;
                $isActive = $selectedCategory === $catKey;
            @endphp
            <button
                type="button"
                wire:click="setCategory('{{ $catKey }}')"
                class="px-4 py-2 rounded-lg text-xs font-semibold transition-all duration-200 flex items-center gap-2 cursor-pointer border {{ $isActive ? 'bg-primary-ink text-white border-primary-ink shadow-sm' : 'bg-white text-primary-ink/70 border-warm-border hover:bg-warm-ivory hover:text-primary-ink' }}"
            >
                <i class="fas {{ $catInfo['icon'] }} {{ $isActive ? 'text-accent-amber' : 'opacity-60' }}"></i>
                <span>{{ $catInfo['label'] }}</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-mono {{ $isActive ? 'bg-white/20 text-white' : 'bg-warm-ivory text-primary-ink/70 border border-warm-border' }}">
                    {{ $count }}
                </span>
            </button>
        @endforeach
    </div>

    {{-- List of Instruments --}}
    @if (empty($testReports) && ! $showMmpi)
        {{-- Empty State --}}
        <div class="bg-white border border-warm-border rounded-xl p-12 text-center space-y-4">
            <div class="w-16 h-16 rounded-full bg-warm-ivory border border-warm-border flex items-center justify-center mx-auto text-accent-amber">
                <i class="fas fa-folder-open text-2xl"></i>
            </div>
            <div class="max-w-md mx-auto">
                <h3 class="font-serif font-bold text-lg text-primary-ink">
                    Data Alat Tes Belum Tersedia
                </h3>
                <p class="text-xs text-primary-ink/60 mt-1 leading-relaxed">
                    Tidak ditemukan rekaman instrumen alat tes untuk kategori yang dipilih pada peserta ini. Silakan pilih tab kategori lain atau lakukan sinkronisasi data asesmen.
                </p>
            </div>
            @if ($selectedCategory !== 'all')
                <button
                    type="button"
                    wire:click="setCategory('all')"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-warm-ivory hover:bg-warm-border/40 text-primary-ink text-xs font-semibold border border-warm-border transition"
                >
                    <i class="fas fa-rotate-left"></i>
                    Tampilkan Semua Alat Tes
                </button>
            @endif
        </div>
    @else
        <div class="space-y-8">
            {{-- Render Each Test Report from test_results --}}
            @foreach ($testReports as $code => $report)
                @php
                    $fmt = $report['formatted'] ?? [];
                    $testName = $report['test_name'] ?? 'Instrumen Asesmen';
                    $testCat = $report['test_category'] ?? 'Psikometri';
                @endphp

                <div class="bg-white border border-warm-border rounded-xl shadow-sm overflow-hidden transition-all duration-200">
                    {{-- Card Header --}}
                    <div class="bg-[#1f1b18] px-6 py-4 border-b border-warm-border/10 flex flex-wrap items-center justify-between gap-3 text-white">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-mono font-bold bg-accent-amber text-white shadow-sm">
                                {{ $code }}
                            </span>
                            <div>
                                <h3 class="font-serif font-bold text-base text-white">
                                    {{ $testName }}
                                </h3>
                                <span class="text-xs text-slate-400 font-medium">
                                    Kategori: {{ $testCat }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 text-xs">
                            <span class="px-2.5 py-1 rounded-md text-xs font-semibold uppercase bg-emerald-900/60 text-emerald-300 border border-emerald-700/60">
                                <i class="fas fa-circle-check text-[11px] mr-1"></i> Terverifikasi
                            </span>
                        </div>
                    </div>

                    {{-- Card Body by Instrument Code --}}
                    <div class="p-6 space-y-6">
                        @if (in_array($code, ['A.1', 'A.2', 'A.5']))
                            {{-- 1. IST / CFIT Kognitif --}}
                            <div class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div class="bg-warm-ivory border border-warm-border p-6 rounded-xl text-center flex flex-col justify-center items-center">
                                        <span class="text-xs font-bold uppercase tracking-wider text-primary-ink/60 block">Skor Total IQ</span>
                                        <span class="font-serif text-5xl font-extrabold text-primary-ink my-2">
                                            {{ is_array($fmt['iq'] ?? null) ? ($fmt['iq']['iq'] ?? ($fmt['iq']['nilai'] ?? '100')) : ($fmt['iq'] ?? 100) }}
                                        </span>
                                        <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-forest-green border border-emerald-200">
                                            Kategori: {{ is_array($fmt['kategori'] ?? null) ? ($fmt['kategori']['IQ'] ?? implode(', ', $fmt['kategori'])) : ($fmt['kategori'] ?? 'Rata-rata') }}
                                        </span>
                                        <p class="text-xs text-primary-ink/60 mt-3 leading-relaxed">
                                            @if ($code === 'A.5')
                                                Intelligenz Struktur Test (IST) mengukur profil struktur kecerdasan melalui 9 sub-dimensi kemampuan berpikir.
                                            @else
                                                Culture Fair Intelligence Test (CFIT) mengukur kapasitas inteligensi umum (*fluid intelligence*) non-verbal yang bebas bias budaya dan bahasa.
                                            @endif
                                        </p>
                                    </div>

                                    <div class="md:col-span-2 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-xs font-bold uppercase tracking-wider text-primary-ink/80">
                                                Rincian Komponen Subtes & Penjelasan Kemampuan
                                            </h4>
                                            <span class="text-xs text-primary-ink/60">Skor Terstandar Psikometri</span>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                            @foreach ($fmt['subtests'] ?? [] as $subKey => $subData)
                                                @php
                                                    $isArr = is_array($subData);
                                                    $subCode = $isArr ? ($subData['code'] ?? strtoupper((string) $subKey)) : strtoupper((string) $subKey);
                                                    $subName = $isArr ? ($subData['name'] ?? "Subtes {$subKey}") : "Subtes {$subKey}";
                                                    $subDesc = $isArr ? ($subData['desc'] ?? 'Mengukur dimensi kapasitas kognitif.') : 'Mengukur dimensi kapasitas kognitif.';
                                                    $subScore = $isArr ? ($subData['score'] ?? '-') : $subData;
                                                @endphp
                                                <div class="bg-warm-ivory/50 border border-warm-border p-3.5 rounded-lg flex flex-col justify-between hover:border-warm-border/80 transition">
                                                    <div>
                                                        <div class="flex items-center justify-between mb-1">
                                                            <span class="font-mono text-xs font-bold text-accent-amber uppercase">{{ $subCode }}</span>
                                                            <span class="font-mono text-base font-extrabold text-primary-ink">{{ $subScore }}</span>
                                                        </div>
                                                        <h5 class="font-bold text-xs text-primary-ink leading-tight mb-1">{{ $subName }}</h5>
                                                        <p class="text-xs text-primary-ink/70 leading-relaxed">{{ $subDesc }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                @if (! empty($fmt['interpretasi']) && is_array($fmt['interpretasi']))
                                    <div class="pt-4 border-t border-warm-border">
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-primary-ink/80 mb-2">
                                            Interpretasi Naratif Kapasitas Kognitif
                                        </h4>
                                        <div class="bg-warm-ivory/40 border border-warm-border p-4 rounded-xl space-y-2.5 text-xs text-primary-ink/80 leading-relaxed">
                                            @foreach ($fmt['interpretasi'] as $iTitle => $iDesc)
                                                <div>
                                                    <span class="font-bold text-primary-ink">{{ $iTitle }}:</span>
                                                    <span>{{ is_array($iDesc) ? implode(', ', $iDesc) : $iDesc }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if (! empty($fmt['saran']) && is_array($fmt['saran']))
                                    <div class="pt-4 border-t border-warm-border">
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-primary-ink/80 mb-2">
                                            Saran Pengembangan Pola Pikir & Strategi Berpikir
                                        </h4>
                                        <div class="bg-emerald-50/40 border border-emerald-100 p-4 rounded-xl space-y-2 text-xs text-primary-ink/80 leading-relaxed">
                                            @foreach ($fmt['saran'] as $saranText)
                                                <div class="flex items-start gap-2">
                                                    <i class="fas fa-lightbulb text-accent-amber mt-0.5 text-xs"></i>
                                                    <span>{{ is_array($saranText) ? implode(', ', $saranText) : $saranText }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                        @elseif (in_array($code, ['B.1', 'D.1']))
                            {{-- 2. PAPI Kostik / Kompetensi Karakter (Grouped by 7 Work Domains) --}}
                            <div class="space-y-6">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-warm-border pb-3">
                                    <div>
                                        <h4 class="font-serif font-bold text-lg text-primary-ink">
                                            20 Skala Perilaku Kerja PAPI Kostik (7 Klaster Dimensi)
                                        </h4>
                                        <p class="text-xs text-primary-ink/70">
                                            Skor Sten 0–9 memetakan kebutuhan (*needs*) dan peran (*roles*) perilaku kerja kandidat di lingkungan profesional.
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-3 text-xs shrink-0">
                                        <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Tinggi (7–9)</span>
                                        <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Sedang (4–6)</span>
                                        <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Rendah (0–3)</span>
                                    </div>
                                </div>

                                @if (! empty($fmt['grouped_domains']))
                                    <div class="space-y-5">
                                        @foreach ($fmt['grouped_domains'] as $domainTitle => $factorItems)
                                            <div class="bg-warm-ivory/30 border border-warm-border rounded-xl p-4 space-y-3">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-2 h-2 rounded-full bg-accent-amber"></span>
                                                    <h5 class="font-bold text-xs uppercase tracking-wider text-primary-ink">
                                                        {{ $domainTitle }}
                                                    </h5>
                                                </div>

                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                    @foreach ($factorItems as $fCode => $fInfo)
                                                        @php
                                                            $valNum = (int) $fInfo['score'];
                                                            $badgeStyle = $valNum >= 7 ? 'bg-emerald-50 border-emerald-200 text-forest-green' : ($valNum <= 3 ? 'bg-amber-50 border-amber-200 text-amber-800' : 'bg-white border-warm-border text-primary-ink');
                                                            $scorePill = $valNum >= 7 ? 'bg-emerald-600 text-white' : ($valNum <= 3 ? 'bg-amber-500 text-white' : 'bg-slate-700 text-white');
                                                        @endphp
                                                        <div class="border {{ $badgeStyle }} rounded-lg p-3 flex flex-col justify-between transition">
                                                            <div>
                                                                <div class="flex items-center justify-between mb-1">
                                                                    <span class="font-mono text-xs font-bold uppercase tracking-wider text-primary-ink/70">
                                                                        Skala {{ $fInfo['code'] }}
                                                                    </span>
                                                                    <span class="font-mono text-xs font-extrabold px-2 py-0.5 rounded {{ $scorePill }}">
                                                                        Skor: {{ $valNum }}
                                                                    </span>
                                                                </div>
                                                                <h6 class="font-bold text-xs text-primary-ink leading-snug mb-1">
                                                                    {{ $fInfo['name'] }}
                                                                </h6>
                                                                <p class="text-xs text-primary-ink/70 leading-relaxed">
                                                                    {{ $fInfo['desc'] }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if (! empty($fmt['narratives']))
                                    <div class="pt-4 border-t border-warm-border">
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-primary-ink/80 mb-2">
                                            Dinamika Interaksi & Pola Komunikasi Kerja
                                        </h4>
                                        <div class="bg-warm-ivory/40 border border-warm-border p-4 rounded-xl space-y-2.5 text-xs text-primary-ink/80 leading-relaxed">
                                            @foreach ($fmt['narratives'] as $nKey => $nText)
                                                <div class="flex items-start gap-2">
                                                    <i class="fas fa-circle-check text-accent-amber mt-0.5 text-xs"></i>
                                                    <span>{{ is_array($nText) ? implode(' ', $nText) : $nText }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                        @elseif ($code === 'B.2')
                            {{-- 3. 16PF (Sixteen Personality Factor Questionnaire) --}}
                            <div class="space-y-6">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-warm-border pb-3">
                                    <div>
                                        <h4 class="font-serif font-bold text-lg text-primary-ink">
                                            Profil 16 Faktor Kepribadian Cattell (Sten 1–10)
                                        </h4>
                                        <p class="text-xs text-primary-ink/70">
                                            Menggambarkan kecenderungan trait kepribadian stabil yang mempengaruhi respon dan adaptasi dalam organisasi.
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-semibold bg-amber-50 text-amber-900 border border-amber-200">
                                            Distorsi Motivasi (MD Score): {{ $fmt['md_score'] ?? 5 }} / 10
                                        </span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                                    @foreach ($fmt['enriched_sten'] ?? [] as $fCode => $fData)
                                        @php
                                            $stNum = (int) $fData['score'];
                                            $cardBorder = $stNum >= 8 ? 'border-purple-200 bg-purple-50/40' : ($stNum <= 3 ? 'border-amber-200 bg-amber-50/40' : 'border-warm-border bg-white');
                                            $badgeLevel = $stNum >= 8 ? 'bg-purple-100 text-purple-800' : ($stNum <= 3 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700');
                                        @endphp
                                        <div class="border {{ $cardBorder }} p-3.5 rounded-lg flex flex-col justify-between">
                                            <div>
                                                <div class="flex items-center justify-between mb-1.5">
                                                    <span class="font-mono text-xs font-bold text-purple-700">Faktor {{ $fCode }}</span>
                                                    <span class="font-mono text-sm font-extrabold text-primary-ink">Sten: {{ $stNum }}</span>
                                                </div>
                                                <h5 class="font-bold text-xs text-primary-ink leading-tight mb-1">{{ $fData['name'] }}</h5>
                                                <span class="inline-block px-1.5 py-0.5 rounded text-[11px] font-semibold {{ $badgeLevel }} mb-2">
                                                    {{ $fData['level'] }}
                                                </span>
                                                <p class="text-xs text-primary-ink/70 leading-relaxed">{{ $fData['interpretation'] }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                        @elseif ($code === 'D.2')
                            {{-- 4. Kraepelin Test (Sikap Kerja & Ketahanan) --}}
                            <div class="space-y-6">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-warm-border pb-3">
                                    <div>
                                        <h4 class="font-serif font-bold text-lg text-primary-ink">
                                            4 Parameter Sikap Kerja & Ritme Kraepelin
                                        </h4>
                                        <p class="text-xs text-primary-ink/70">
                                            Evaluasi ritme kerja, ketelitian, kestabilan emosi kerja, dan daya tahan mental di bawah tekanan waktu.
                                        </p>
                                    </div>
                                    <span class="text-xs text-primary-ink/60">Norma Standar: {{ $fmt['pendidikan'] ?? 'S1/D4' }}</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                    @php
                                        $kraepelinParams = [
                                            ['key' => 'pspeed', 'val' => $fmt['pspeed'], 'name' => 'Kecepatan Kerja (PANKER)', 'sub' => 'Output item/menit', 'desc' => 'Mengukur tempo kecepatan output kerja per satuan waktu di bawah target.'],
                                            ['key' => 'pacc', 'val' => $fmt['pacc'], 'name' => 'Ketelitian Kerja (JANKER)', 'sub' => 'Range deviasi: '.($fmt['janker_range'] ?? 0), 'desc' => 'Mengukur derajat akurasi kerja dan tingkat kekebalan terhadap kekeliruan (error rate).'],
                                            ['key' => 'pstab', 'val' => $fmt['pstab'], 'name' => 'Kestabilan Kerja (HANKER)', 'sub' => 'Konsistensi grafik ritme', 'desc' => 'Mengukur konsistensi ritme kerja tanpa fluktuasi grafik energi yang ekstrem.'],
                                            ['key' => 'pstn', 'val' => $fmt['pstn'], 'name' => 'Ketahanan Kerja (TIANKER)', 'sub' => 'Daya tahan saat jenuh', 'desc' => 'Mengukur daya tahan stamina kerja dan pemeliharaan fokus saat mengalami kejenuhan mental.'],
                                        ];
                                    @endphp
                                    @foreach ($kraepelinParams as $kp)
                                        <div class="bg-warm-ivory border border-warm-border p-4 rounded-xl flex flex-col justify-between">
                                            <div>
                                                <span class="text-xs font-bold uppercase tracking-wider text-primary-ink/60 block">{{ $kp['name'] }}</span>
                                                <span class="font-mono text-3xl font-extrabold text-primary-ink my-1.5 block">{{ $kp['val'] }}</span>
                                                <span class="text-xs font-semibold text-accent-amber block mb-2">{{ $kp['sub'] }}</span>
                                                <p class="text-xs text-primary-ink/70 leading-relaxed">{{ $kp['desc'] }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                        @elseif ($code === 'F.1')
                            {{-- 5. Typical EQ (Kecerdasan Emosional) --}}
                            <div class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div class="bg-warm-ivory border border-warm-border p-6 rounded-xl text-center flex flex-col justify-center items-center">
                                        <span class="text-xs uppercase font-bold tracking-wider text-primary-ink/60 block">Total Indeks EQ</span>
                                        <span class="font-serif text-5xl font-extrabold text-primary-ink my-2">{{ $fmt['eq_score'] }}</span>
                                        <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-semibold bg-teal-50 text-teal-800 border border-teal-200">
                                            Kategori: {{ $fmt['kategori'] }}
                                        </span>
                                        <p class="text-xs text-primary-ink/60 mt-3 leading-relaxed">
                                            Mengukur kematangan kecerdasan emosional personal dan sosial dalam mengelola hubungan kerja.
                                        </p>
                                    </div>
                                    <div class="md:col-span-2 space-y-3">
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-primary-ink/80">
                                            Rincian Dimensi Kecerdasan Emosional
                                        </h4>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                                            @foreach ($fmt['dimensions'] ?? [] as $dId => $dData)
                                                <div class="bg-warm-ivory/60 border border-warm-border p-3 rounded-lg flex items-center justify-between">
                                                    <span class="font-bold text-primary-ink/80 text-xs truncate mr-2">{{ $dData['nama'] ?? "Dimensi {$dId}" }}</span>
                                                    <span class="font-mono text-sm font-bold text-primary-ink shrink-0">
                                                        {{ $dData['nilai'] ?? ($dData['skor'] ?? '-') }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @elseif ($code === 'G.1')
                            {{-- 6. Typical Behavior Tendencies --}}
                            <div class="space-y-4">
                                <div class="bg-warm-ivory border border-warm-border p-5 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div>
                                        <span class="text-xs font-bold uppercase tracking-wider text-primary-ink/60 block">Kecenderungan Perilaku Dominan</span>
                                        <h4 class="font-serif text-2xl font-bold text-primary-ink mt-0.5">{{ $fmt['tipe'] }}</h4>
                                    </div>
                                    <span class="px-3 py-1.5 rounded-md text-xs font-semibold bg-accent-amber/10 text-accent-amber border border-accent-amber/30 shrink-0">
                                        Profil Perilaku Aktif
                                    </span>
                                </div>
                                @if (! empty($fmt['interpretasi']))
                                    <div class="bg-warm-ivory/40 border border-warm-border p-4 rounded-xl text-xs text-primary-ink/80 leading-relaxed">
                                        <span class="font-bold text-primary-ink block mb-1">Interpretasi Dinamika Kebiasaan & Sikap Kerja:</span>
                                        {{ $fmt['interpretasi'] }}
                                    </div>
                                @endif
                            </div>

                        @elseif ($code === 'H.1')
                            {{-- 7. RMIB (Rothwell Miller Interest Blank - Minat Jabatan) --}}
                            <div class="space-y-4">
                                <div>
                                    <h4 class="font-serif font-bold text-base text-primary-ink">
                                        Top 3 Orientasi Minat Bidang Pekerjaan (RMIB)
                                    </h4>
                                    <p class="text-xs text-primary-ink/70 mt-0.5">
                                        Memetakan minat okupasional intrinsik kandidat untuk penempatan peran dan jalur pengembangan karier yang selaras.
                                    </p>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    @foreach ($fmt['top_interests'] ?? [] as $idx => $interest)
                                        <div class="bg-warm-ivory border border-warm-border p-4 rounded-xl flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-accent-amber text-white font-mono font-bold text-xs flex items-center justify-center shrink-0 shadow-sm">
                                                #{{ $idx + 1 }}
                                            </div>
                                            <div class="min-w-0">
                                                <span class="text-xs text-primary-ink/60 block">Prioritas Minat</span>
                                                <span class="font-bold text-xs text-primary-ink truncate block">{{ $interest }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                        @else
                            {{-- Default Generic Payload Inspector --}}
                            <div class="bg-warm-ivory/40 border border-warm-border p-4 rounded-xl text-xs font-mono text-primary-ink/80 overflow-x-auto">
                                <pre>{{ json_encode($fmt, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            {{-- Optional MMPI Card if active and matches category filter --}}
            @if ($showMmpi && $participant?->mmpi)
                @php
                    $mmpi = $participant->mmpi;
                @endphp
                <div class="bg-white border border-warm-border rounded-xl shadow-sm overflow-hidden">
                    <div class="bg-[#1f1b18] px-6 py-4 border-b border-warm-border/10 flex flex-wrap items-center justify-between gap-3 text-white">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-mono font-bold bg-purple-600 text-white shadow-sm">
                                MMPI
                            </span>
                            <div>
                                <h3 class="font-serif font-bold text-base text-white">
                                    Minnesota Multiphasic Personality Inventory (MMPI-2)
                                </h3>
                                <span class="text-xs text-slate-400 font-medium">
                                    Kategori: Psikologi Klinis & Skrining Kebugaran Jiwa
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 text-xs">
                            <span class="px-2.5 py-1 rounded-md text-xs font-semibold uppercase bg-purple-900/60 text-purple-300 border border-purple-700/60">
                                <i class="fas fa-heart-pulse text-[11px] mr-1"></i> Hygiene Factor
                            </span>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <div>
                            <p class="text-xs text-primary-ink/70 leading-relaxed">
                                Evaluasi kelaikan klinis kerja untuk menyaring risiko psikopatologi berat, dinamika stabilitas emosional, dan kapasitas koping stres kerja.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="bg-warm-ivory border border-warm-border p-4 rounded-xl text-center">
                                <span class="text-xs font-bold uppercase tracking-wider text-primary-ink/60 block">Skala Validitas (L-F-K)</span>
                                <span class="font-mono text-xl font-bold text-primary-ink my-1 block">{{ $mmpi->validitas ?? 'Valid' }}</span>
                                <span class="text-xs text-primary-ink/60">Memverifikasi kejujuran & konsistensi respon</span>
                            </div>
                            <div class="bg-warm-ivory border border-warm-border p-4 rounded-xl text-center">
                                <span class="text-xs font-bold uppercase tracking-wider text-primary-ink/60 block">Tingkat Stres</span>
                                <span class="font-mono text-xl font-bold {{ strtolower((string) $mmpi->tingkat_stres) === 'tinggi' ? 'text-red-700' : 'text-primary-ink' }} my-1 block">
                                    {{ $mmpi->tingkat_stres ?? 'Rendah' }}
                                </span>
                                <span class="text-xs text-primary-ink/60">Indeks ketahanan beban kerja</span>
                            </div>
                            <div class="bg-warm-ivory border border-warm-border p-4 rounded-xl text-center">
                                <span class="text-xs font-bold uppercase tracking-wider text-primary-ink/60 block">Status Kelaikan</span>
                                <span class="font-mono text-xl font-bold text-forest-green my-1 block">{{ $mmpi->kesimpulan ?? 'Memenuhi Syarat' }}</span>
                                <span class="text-xs text-primary-ink/60">Diagnosis kelaikan tugas institusi</span>
                            </div>
                        </div>

                        @if (! empty($mmpi->clinical_scales) || ! empty($mmpi->validity_scales))
                            <div class="space-y-4 pt-2">
                                <h4 class="font-bold text-primary-ink uppercase tracking-wider text-xs flex items-center justify-between">
                                    <span>Profil T-Score Skala MMPI</span>
                                    <span class="text-[11px] font-normal text-slate-400">Mean: 50 | Ambang Elevasi: T &ge; 65</span>
                                </h4>

                                @if (! empty($mmpi->validity_scales))
                                    <div>
                                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-2">Skala Validitas</span>
                                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                                            @foreach ($mmpi->validity_scales as $sCode => $sScore)
                                                <div class="bg-warm-ivory border border-warm-border/60 rounded-lg p-2.5 text-center">
                                                    <span class="text-[10px] font-bold font-mono text-slate-500 block">{{ $sCode }}</span>
                                                    <span class="font-mono text-base font-bold {{ $sScore >= 65 ? 'text-amber-700 font-extrabold' : 'text-primary-ink' }}">
                                                        {{ $sScore }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if (! empty($mmpi->clinical_scales))
                                    <div>
                                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-2">Skala Klinis</span>
                                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                                            @foreach ($mmpi->clinical_scales as $sCode => $sScore)
                                                <div class="bg-warm-ivory border border-warm-border/60 rounded-lg p-2.5 text-center">
                                                    <span class="text-[10px] font-bold font-mono text-slate-500 block">{{ $sCode }}</span>
                                                    <span class="font-mono text-base font-bold {{ $sScore >= 65 ? 'text-red-700 font-extrabold' : 'text-primary-ink' }}">
                                                        {{ $sScore }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if ($mmpi->klinik || $mmpi->internal || $mmpi->interpersonal)
                            <div class="bg-warm-ivory/40 border border-warm-border p-4 rounded-xl space-y-2 text-xs text-primary-ink/80 leading-relaxed">
                                <h4 class="font-bold text-primary-ink uppercase tracking-wider text-xs">Catatan Klinis Asesor Psikologi SPSP</h4>
                                @if ($mmpi->internal)
                                    <div><span class="font-semibold text-primary-ink">Dinamika Internal:</span> {{ $mmpi->internal }}</div>
                                @endif
                                @if ($mmpi->interpersonal)
                                    <div><span class="font-semibold text-primary-ink">Relasi Interpersonal:</span> {{ $mmpi->interpersonal }}</div>
                                @endif
                                @if ($mmpi->klinik)
                                    <div><span class="font-semibold text-primary-ink">Evaluasi Klinis:</span> {{ $mmpi->klinik }}</div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
