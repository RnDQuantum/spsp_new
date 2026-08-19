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
                    Laporan Hasil Alat Tes Psikometri
                </h2>
                <p class="text-xs text-primary-ink/70 mt-1 max-w-3xl leading-relaxed">
                    Lampiran rincian skor matang (*raw scores*), skor terstandar (*Standard Score / Sten / T-Score*), dan sub-komponen dari seluruh instrumen psikometri yang diikuti peserta. Berfungsi sebagai bukti audit ilmiah yang melandasi evaluasi pada Layer 1 (Kompetensi) dan Layer 2 (Potensi).
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
        <div class="space-y-6">
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
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="bg-warm-ivory border border-warm-border p-6 rounded-xl text-center flex flex-col justify-center items-center">
                                    <span class="text-xs uppercase font-bold tracking-wider text-primary-ink/60 block">Skor Total IQ</span>
                                    <span class="font-serif text-5xl font-extrabold text-primary-ink my-2">
                                        {{ is_array($fmt['iq'] ?? null) ? ($fmt['iq']['iq'] ?? ($fmt['iq']['nilai'] ?? '100')) : ($fmt['iq'] ?? 100) }}
                                    </span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-forest-green border border-emerald-200">
                                        Kategori: {{ is_array($fmt['kategori'] ?? null) ? ($fmt['kategori']['IQ'] ?? implode(', ', $fmt['kategori'])) : ($fmt['kategori'] ?? 'Rata-rata') }}
                                    </span>
                                </div>

                                <div class="md:col-span-2 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-primary-ink/80">
                                            Rincian Standard Score (SS) Subtes
                                        </h4>
                                        <span class="text-xs text-primary-ink/60">Skala Standar Psikometri</span>
                                    </div>
                                    <div class="grid grid-cols-3 sm:grid-cols-3 md:grid-cols-5 gap-2.5">
                                        @foreach ($fmt['subtests'] ?? [] as $subName => $subVal)
                                            <div class="bg-warm-ivory/60 border border-warm-border p-3 rounded-lg text-center">
                                                <span class="font-bold text-primary-ink/60 text-xs block uppercase">{{ $subName }}</span>
                                                <span class="font-mono text-base font-bold text-primary-ink mt-0.5 block">
                                                    {{ is_array($subVal) ? ($subVal['nilai'] ?? ($subVal['sw'] ?? json_encode($subVal))) : $subVal }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            @if (! empty($fmt['interpretasi']) && is_array($fmt['interpretasi']))
                                <div class="pt-4 border-t border-warm-border">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-primary-ink/80 mb-2">
                                        Interpretasi Kapasitas Kognitif
                                    </h4>
                                    <div class="bg-warm-ivory/40 border border-warm-border p-4 rounded-xl space-y-2 text-xs text-primary-ink/80 leading-relaxed">
                                        @foreach ($fmt['interpretasi'] as $iTitle => $iDesc)
                                            <div>
                                                <span class="font-semibold text-primary-ink">{{ $iTitle }}:</span>
                                                <span>{{ is_array($iDesc) ? implode(', ', $iDesc) : $iDesc }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if (! empty($fmt['saran']) && is_array($fmt['saran']))
                                <div class="pt-4 border-t border-warm-border">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-primary-ink/80 mb-2">
                                        Saran Pengembangan Berpikir
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

                        @elseif (in_array($code, ['B.1', 'D.1']))
                            {{-- 2. PAPI Kostik / Kompetensi Karakter --}}
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-primary-ink/80">
                                        20 Skala Kebutuhan & Peran Kerja PAPI Kostik
                                    </h4>
                                    <span class="text-xs text-primary-ink/60">Rentang Standar Sten 0–9</span>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-2.5">
                                    @foreach ($fmt['factors'] ?? [] as $fKey => $fVal)
                                        @php
                                            $fName = str_replace('hasil_', '', (string) $fKey);
                                            $valNum = is_array($fVal) ? (int) ($fVal['nilai'] ?? 0) : (int) $fVal;
                                            $badgeColor = $valNum >= 7 ? 'text-forest-green bg-emerald-50 border-emerald-200' : ($valNum <= 3 ? 'text-amber-700 bg-amber-50 border-amber-200' : 'text-primary-ink bg-warm-ivory border-warm-border');
                                        @endphp
                                        <div class="border {{ $badgeColor }} p-3 rounded-lg text-center transition">
                                            <span class="font-bold text-primary-ink/60 text-xs block uppercase truncate" title="{{ $fmt['labels'][$fKey] ?? $fName }}">
                                                {{ $fmt['labels'][$fKey] ?? $fName }} ({{ strtoupper($fName) }})
                                            </span>
                                            <span class="font-mono text-xl font-bold mt-1 block">
                                                {{ $valNum }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>

                                @if (! empty($fmt['narratives']))
                                    <div class="pt-4 border-t border-warm-border">
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-primary-ink/80 mb-2">
                                            Dinamika Perilaku & Interaksi Kerja
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
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-primary-ink/80">
                                        Sten Scores 16 Faktor Kepribadian Cattell
                                    </h4>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                        Distorsi Motivasi (MD Score): {{ $fmt['md_score'] ?? 5 }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-8 gap-2.5">
                                    @foreach ($fmt['sten_scores'] ?? [] as $stKey => $stVal)
                                        @php
                                            $stNum = (int) $stVal;
                                            $color = $stNum >= 8 ? 'text-purple-700 bg-purple-50 border-purple-200' : ($stNum <= 3 ? 'text-amber-700 bg-amber-50 border-amber-200' : 'text-primary-ink bg-warm-ivory border-warm-border');
                                        @endphp
                                        <div class="border {{ $color }} p-3 rounded-lg text-center">
                                            <span class="font-bold text-primary-ink/60 text-xs block uppercase">Faktor {{ $stKey }}</span>
                                            <span class="font-mono text-xl font-bold mt-1 block">{{ $stVal }}</span>
                                        </div>
                                    @endforeach
                                </div>

                                @if (! empty($fmt['descriptions']) && is_array($fmt['descriptions']))
                                    <div class="pt-4 border-t border-warm-border">
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-primary-ink/80 mb-2">
                                            Deskripsi Profil Kepribadian
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs text-primary-ink/80">
                                            @foreach ($fmt['descriptions'] as $fDescKey => $fDescVal)
                                                <div class="bg-warm-ivory/40 border border-warm-border p-3 rounded-lg">
                                                    <span class="font-bold text-primary-ink block mb-0.5">Faktor {{ $fDescKey }}</span>
                                                    <span>{{ is_array($fDescVal) ? implode(', ', $fDescVal) : $fDescVal }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                        @elseif ($code === 'D.2')
                            {{-- 4. Kraepelin Test (Sikap Kerja & Ketahanan) --}}
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-primary-ink/80">
                                        4 Parameter Sikap Kerja & Ritme Kraepelin
                                    </h4>
                                    <span class="text-xs text-primary-ink/60">Standar Norma Pendidikan: {{ $fmt['pendidikan'] ?? 'S1/D4' }}</span>
                                </div>

                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div class="bg-warm-ivory border border-warm-border p-4 rounded-xl text-center">
                                        <span class="text-xs font-bold uppercase tracking-wider text-primary-ink/60 block">Kecepatan (PANKER)</span>
                                        <span class="font-mono text-3xl font-extrabold text-primary-ink my-1 block">{{ $fmt['pspeed'] }}</span>
                                        <span class="text-xs text-primary-ink/60">Kecepatan tempo kerja</span>
                                    </div>
                                    <div class="bg-warm-ivory border border-warm-border p-4 rounded-xl text-center">
                                        <span class="text-xs font-bold uppercase tracking-wider text-primary-ink/60 block">Ketelitian (JANKER)</span>
                                        <span class="font-mono text-3xl font-extrabold text-primary-ink my-1 block">{{ $fmt['pacc'] }}</span>
                                        <span class="text-xs text-primary-ink/60">Rentang deviasi: {{ $fmt['janker_range'] ?? 0 }}</span>
                                    </div>
                                    <div class="bg-warm-ivory border border-warm-border p-4 rounded-xl text-center">
                                        <span class="text-xs font-bold uppercase tracking-wider text-primary-ink/60 block">Kestabilan (HANKER)</span>
                                        <span class="font-mono text-3xl font-extrabold text-primary-ink my-1 block">{{ $fmt['pstab'] }}</span>
                                        <span class="text-xs text-primary-ink/60">Konsistensi ritme grafik</span>
                                    </div>
                                    <div class="bg-warm-ivory border border-warm-border p-4 rounded-xl text-center">
                                        <span class="text-xs font-bold uppercase tracking-wider text-primary-ink/60 block">Ketahanan (TIANKER)</span>
                                        <span class="font-mono text-3xl font-extrabold text-primary-ink my-1 block">{{ $fmt['pstn'] }}</span>
                                        <span class="text-xs text-primary-ink/60">Daya tahan saat jenuh</span>
                                    </div>
                                </div>
                            </div>

                        @elseif ($code === 'F.1')
                            {{-- 5. Typical EQ (Kecerdasan Emosional) --}}
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div class="bg-warm-ivory border border-warm-border p-6 rounded-xl text-center flex flex-col justify-center items-center">
                                        <span class="text-xs uppercase font-bold tracking-wider text-primary-ink/60 block">Total Indeks EQ</span>
                                        <span class="font-serif text-5xl font-extrabold text-primary-ink my-2">{{ $fmt['eq_score'] }}</span>
                                        <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-semibold bg-teal-50 text-teal-800 border border-teal-200">
                                            Kategori: {{ $fmt['kategori'] }}
                                        </span>
                                    </div>
                                    <div class="md:col-span-2 space-y-3">
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-primary-ink/80">
                                            Rincian Dimensi Kecerdasan Emosional
                                        </h4>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                            @foreach ($fmt['dimensions'] ?? [] as $dId => $dData)
                                                <div class="bg-warm-ivory/60 border border-warm-border p-3 rounded-lg">
                                                    <span class="font-bold text-primary-ink/70 text-xs block truncate">{{ $dData['nama'] ?? "Dimensi {$dId}" }}</span>
                                                    <span class="font-mono text-base font-bold text-primary-ink mt-0.5 block">
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
                                <div class="bg-warm-ivory border border-warm-border p-4 rounded-xl flex items-center justify-between">
                                    <div>
                                        <span class="text-xs font-bold uppercase tracking-wider text-primary-ink/60 block">Kecenderungan Perilaku Dominan</span>
                                        <h4 class="font-serif text-xl font-bold text-primary-ink mt-0.5">{{ $fmt['tipe'] }}</h4>
                                    </div>
                                    <span class="px-3 py-1 rounded-md text-xs font-semibold bg-accent-amber/10 text-accent-amber border border-accent-amber/30">
                                        Profile Active
                                    </span>
                                </div>
                                @if (! empty($fmt['interpretasi']))
                                    <div class="bg-warm-ivory/40 border border-warm-border p-4 rounded-xl text-xs text-primary-ink/80 leading-relaxed">
                                        <span class="font-semibold text-primary-ink block mb-1">Interpretasi Perilaku Kerja:</span>
                                        {{ $fmt['interpretasi'] }}
                                    </div>
                                @endif
                            </div>

                        @elseif ($code === 'H.1')
                            {{-- 7. RMIB (Minat Jabatan) --}}
                            <div class="space-y-4">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-primary-ink/80">
                                    Top 3 Orientasi Minat Bidang Pekerjaan (RMIB)
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    @foreach ($fmt['top_interests'] ?? [] as $idx => $interest)
                                        <div class="bg-warm-ivory border border-warm-border p-4 rounded-xl flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-accent-amber text-white font-mono font-bold text-xs flex items-center justify-center shrink-0">
                                                #{{ $idx + 1 }}
                                            </div>
                                            <div class="min-w-0">
                                                <span class="text-xs text-primary-ink/60 block">Pilihan Utama</span>
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
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="bg-warm-ivory border border-warm-border p-4 rounded-xl text-center">
                                <span class="text-xs font-bold uppercase tracking-wider text-primary-ink/60 block">Skala Validitas (L-F-K)</span>
                                <span class="font-mono text-xl font-bold text-primary-ink my-1 block">{{ $mmpi->validitas ?? 'Valid' }}</span>
                                <span class="text-xs text-primary-ink/60">Indikator konsistensi jawaban</span>
                            </div>
                            <div class="bg-warm-ivory border border-warm-border p-4 rounded-xl text-center">
                                <span class="text-xs font-bold uppercase tracking-wider text-primary-ink/60 block">Tingkat Stres</span>
                                <span class="font-mono text-xl font-bold {{ strtolower((string) $mmpi->tingkat_stres) === 'tinggi' ? 'text-red-700' : 'text-primary-ink' }} my-1 block">
                                    {{ $mmpi->tingkat_stres ?? 'Rendah' }}
                                </span>
                                <span class="text-xs text-primary-ink/60">Kapasitas koping tekanan</span>
                            </div>
                            <div class="bg-warm-ivory border border-warm-border p-4 rounded-xl text-center">
                                <span class="text-xs font-bold uppercase tracking-wider text-primary-ink/60 block">Status Kelaikan</span>
                                <span class="font-mono text-xl font-bold text-forest-green my-1 block">{{ $mmpi->kesimpulan ?? 'Memenuhi Syarat' }}</span>
                                <span class="text-xs text-primary-ink/60">Diagnosis klinis kerja</span>
                            </div>
                        </div>

                        @if ($mmpi->klinik || $mmpi->internal || $mmpi->interpersonal)
                            <div class="bg-warm-ivory/40 border border-warm-border p-4 rounded-xl space-y-2 text-xs text-primary-ink/80 leading-relaxed">
                                <h4 class="font-bold text-primary-ink uppercase tracking-wider text-xs">Catatan Klinis Asesor</h4>
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
