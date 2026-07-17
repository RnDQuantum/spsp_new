<div class="mt-8 mb-8 px-6 border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] shadow-xs rounded-lg">
    <div class="px-8 py-6 bg-white dark:bg-[#171412] border-b border-warm-border dark:border-[#25211e] mb-8 -mx-6">
        <h1 class="font-display text-2xl font-bold tracking-tight text-primary-ink dark:text-neutral-100 uppercase text-center">
            Laporan Individu
        </h1>
        <div class="text-center mt-2 text-xs font-semibold text-primary-ink/77 dark:text-neutral-400">
            {{ $eventName }} - {{ $institutionName }}
        </div>
    </div>

    {{-- Tolerance Selector Component --}}
    <div class="mb-8">
        @livewire('components.tolerance-selector', ['showSummary' => false])
    </div>

    {{-- Adjustment Indicators --}}
    <div class="mb-6 flex flex-wrap gap-2">
        <x-adjustment-indicator :template-id="$participant->positionFormation->template_id" category-code="potensi"
            size="sm" custom-label="Standar Potensi Disesuaikan" />
        <x-adjustment-indicator :template-id="$participant->positionFormation->template_id" category-code="kompetensi"
            size="sm" custom-label="Standar Kompetensi Disesuaikan" />
    </div>

    {{-- Biodata Section --}}
    <div class="mb-8">
        <h2 class="text-xl font-display font-bold text-primary-ink dark:text-neutral-100 mb-4 pl-4 border-l-4 border-accent-amber">Biodata Peserta</h2>

        <div
            class="bg-warm-ivory dark:bg-[#1f1b18] border border-warm-border dark:border-[#25211e] rounded-md p-6 flex flex-col md:flex-row md:items-start md:justify-between">
            {{-- Tabel Biodata --}}
            <div class="w-full md:w-2/3">
                <table class="w-full border-collapse">
                    <tbody>
                        <tr class="border-b border-warm-border/60 dark:border-[#25211e]/40">
                            <td class="py-2 text-sm font-semibold text-primary-ink/75 dark:text-neutral-400 w-1/3">Nomor Tes</td>
                            <td class="py-2 text-sm text-primary-ink dark:text-neutral-100">{{ $participant->test_number }}</td>
                        </tr>
                        <tr class="border-b border-warm-border/60 dark:border-[#25211e]/40">
                            <td class="py-2 text-sm font-semibold text-primary-ink/75 dark:text-neutral-400">Nomor SKB</td>
                            <td class="py-2 text-sm text-primary-ink dark:text-neutral-100">{{ $participant->skb_number }}</td>
                        </tr>
                        <tr class="border-b border-warm-border/60 dark:border-[#25211e]/40">
                            <td class="py-2 text-sm font-semibold text-primary-ink/75 dark:text-neutral-400">Nama</td>
                            <td class="py-2 text-sm text-primary-ink dark:text-neutral-100">{{ $participant->name }}</td>
                        </tr>
                        <tr class="border-b border-warm-border/60 dark:border-[#25211e]/40">
                            <td class="py-2 text-sm font-semibold text-primary-ink/75 dark:text-neutral-400">Email</td>
                            <td class="py-2 text-sm text-primary-ink dark:text-neutral-100">{{ $participant->email }}</td>
                        </tr>
                        <tr class="border-b border-warm-border/60 dark:border-[#25211e]/40">
                            <td class="py-2 text-sm font-semibold text-primary-ink/75 dark:text-neutral-400">Telepon</td>
                            <td class="py-2 text-sm text-primary-ink dark:text-neutral-100">{{ $participant->phone }}</td>
                        </tr>
                        <tr class="border-b border-warm-border/60 dark:border-[#25211e]/40">
                            <td class="py-2 text-sm font-semibold text-primary-ink/75 dark:text-neutral-400">Jenis Kelamin</td>
                            <td class="py-2 text-sm text-primary-ink dark:text-neutral-100">
                                {{ $participant->gender == 'M' ? 'Laki-laki' : 'Perempuan' }}
                            </td>
                        </tr>
                        <tr class="border-b border-warm-border/60 dark:border-[#25211e]/40">
                            <td class="py-2 text-sm font-semibold text-primary-ink/75 dark:text-neutral-400">Formasi Jabatan</td>
                            <td class="py-2 text-sm text-primary-ink dark:text-neutral-100">{{ $participant->positionFormation->name }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 text-sm font-semibold text-primary-ink/75 dark:text-neutral-400">Tanggal Asesmen</td>
                            <td class="py-2 text-sm text-primary-ink dark:text-neutral-100">
                                {{ \Carbon\Carbon::parse($participant->assessment_date)->translatedFormat('d F Y') }}

                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Placeholder Foto --}}
            <div class="w-full md:w-1/3 flex justify-center md:justify-end mt-6 md:mt-0">
                <div
                    class="w-47 h-65 bg-warm-border dark:bg-[#2c2724] border border-warm-border dark:border-[#25211e] rounded-md overflow-hidden flex items-center justify-center">
                    @if ($participant->photo)
                    <img src="{{ asset('storage/' . $participant->photo) }}" alt="Foto Peserta"
                        class="object-cover w-full h-full">
                    @else
                    <span class="text-gray-500 dark:text-gray-400 text-sm">Foto<br>Tidak Tersedia</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- General Matching Section potensi --}}
    <div class="mb-8">
        <livewire:pages.individual-report.general-matching :eventCode="$eventCode" :testNumber="$testNumber"
            :isStandalone="false" :showHeader="false" :showInfoSection="false" :showPotensi="true"
            :showKompetensi="false" :key="'potensi-only-' . $eventCode . '-' . $testNumber" />
    </div>

    {{-- Psychology Mapping Section --}}
    <div class="mb-8">
        <h2 class="text-xl font-display font-bold text-primary-ink dark:text-neutral-100 mb-4 pl-4 border-l-4 border-accent-amber">Psychology Mapping</h2>
        <livewire:pages.individual-report.general-psy-mapping :eventCode="$eventCode" :testNumber="$testNumber"
            :isStandalone="false" :showHeader="false" :showInfoSection="false" :showTable="true" :showRatingChart="true"
            :showScoreChart="true" :showRankingInfo="false" :key="'psy-mapping-' . $eventCode . '-' . $testNumber" />
    </div>

    {{-- Interpretation Section potensi --}}
    <div class="mb-8">
        {{-- <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Interpretasi Hasil Assessment</h2> --}}
        <livewire:pages.individual-report.interpretation-section :eventCode="$eventCode" :testNumber="$testNumber"
            :isStandalone="false" :showHeader="false" :showPotensi="true" :showKompetensi="false"
            :key="'interpretation-' . $eventCode . '-' . $testNumber" />
    </div>

    {{-- General Matching Section kompetensi --}}
    <div class="mb-8">
        <livewire:pages.individual-report.general-matching :eventCode="$eventCode" :testNumber="$testNumber"
            :isStandalone="false" :showHeader="false" :showInfoSection="false" :showPotensi="false"
            :showKompetensi="true" :key="'kompetensi-only-' . $eventCode . '-' . $testNumber" />
    </div>

    {{-- Managerial Competency Mapping Section --}}
    <div class="mb-8">
        <h2 class="text-xl font-display font-bold text-primary-ink dark:text-neutral-100 mb-4 pl-4 border-l-4 border-accent-amber">Managerial Competency Mapping</h2>
        <livewire:pages.individual-report.general-mc-mapping :eventCode="$eventCode" :testNumber="$testNumber"
            :isStandalone="false" :showHeader="false" :showInfoSection="false" :showTable="true" :showRatingChart="true"
            :showScoreChart="true" :showRankingInfo="false" :key="'mc-mapping-' . $eventCode . '-' . $testNumber" />
    </div>

    {{-- Interpretation Section kompetensi --}}
    <div class="mb-8">
        {{-- <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Interpretasi Kompetensi</h2> --}}
        <livewire:pages.individual-report.interpretation-section :eventCode="$eventCode" :testNumber="$testNumber"
            :isStandalone="false" :showHeader="false" :showPotensi="false" :showKompetensi="true"
            :key="'interpretation-kompetensi-' . $eventCode . '-' . $testNumber" />
    </div>

    {{-- Ringkasan Asesmen Section - Table Only --}}
    <div class="mb-8">
        <h2 class="text-xl font-display font-bold text-primary-ink dark:text-neutral-100 mb-4 pl-4 border-l-4 border-accent-amber">Ringkasan Hasil Asesmen</h2>
        <livewire:pages.individual-report.ringkasan-assessment :eventCode="$eventCode" :testNumber="$testNumber"
            :isStandalone="false" :showHeader="false" :showBiodata="false" :showInfoSection="false" :showTable="true"
            :key="'ringkasan-assessment-' . $eventCode . '-' . $testNumber" />
    </div>

    {{-- Hasil Rekomendasi Section --}}
    <div class="mb-8">
        <div class="mx-auto border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] overflow-hidden rounded-md shadow-xs">
            <!-- Header -->
            <div class="bg-warm-ivory dark:bg-[#1f1b18] border-b border-warm-border dark:border-[#25211e] py-4">
                <h2 class="text-center font-display text-lg font-bold text-primary-ink dark:text-neutral-100 uppercase tracking-wide">
                    Hasil Rekomendasi
                </h2>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <!-- Header Row -->
                    <thead>
                        <tr class="bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200">
                            <th
                                class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-semibold text-primary-ink dark:text-neutral-200 w-16">
                                NO.</th>
                            <th
                                class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-semibold text-primary-ink dark:text-neutral-200 w-48">
                                ASPEK
                                PENILAIAN</th>
                            <th
                                class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-semibold text-primary-ink dark:text-neutral-200">
                                SKOR/ HASIL
                            </th>
                            <th
                                class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-semibold text-primary-ink dark:text-neutral-200 w-64">
                                KESIMPULAN
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Row 1: Psikotest -->
                        <tr class="hover:bg-warm-ivory/50 dark:hover:bg-[#1f1b18]/50 transition-colors duration-150 text-sm text-primary-ink dark:text-neutral-200">
                            <td
                                class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-normal align-top">
                                1
                            </td>
                            <td
                                class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-semibold text-primary-ink dark:text-neutral-100 align-top">
                                Potensi dan Kompetensi
                            </td>
                            <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 align-top">
                                <div class="space-y-2">
                                    <div class="flex">
                                        <span class="font-semibold w-32">1. IQ</span>
                                        <span class="flex-1">: 97</span>
                                    </div>
                                    <div class="flex">
                                        <span class="font-semibold w-32">2. Total Score</span>
                                        <span class="flex-1">:
                                            @php
                                            $finalData = $this->loadFinalAssessmentData();
                                            @endphp
                                            {{ $finalData ? number_format($finalData['total_individual_score'], 2, ',',
                                            '.') : '0,00' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td
                                class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-bold text-base align-middle {{ $this->getFinalConclusionColorClass() }}">
                                {{ $this->getFinalConclusionText() }}
                            </td>
                        </tr>

                        <!-- Row 2: Tes Kejiwaan (Header) -->
                        <tr class="bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200">
                            <th
                                class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-semibold text-primary-ink dark:text-neutral-200">
                                NO.</th>
                            <th
                                class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-semibold text-primary-ink dark:text-neutral-200">
                                ASPEK PENILAIAN
                            </th>
                            <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-semibold text-primary-ink dark:text-neutral-200"
                                colspan="2">
                                SKOR/ HASIL</th>
                        </tr>

                        <!-- Row 3: Tes Kejiwaan (Content) -->
                        @if ($psychologicalTest)
                        <tr class="hover:bg-warm-ivory/50 dark:hover:bg-[#1f1b18]/50 transition-colors duration-150 text-sm text-primary-ink dark:text-neutral-200">
                            <td
                                class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-normal align-top">
                                2
                            </td>
                            <td
                                class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-semibold text-primary-ink dark:text-neutral-100 align-top">
                                Tes Kejiwaan
                            </td>
                            <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 align-top"
                                colspan="2">
                                <div class="space-y-3 text-sm">
                                    <!-- 1. Validitas -->
                                    <div class="border-b border-warm-border dark:border-[#25211e]/40 pb-2">
                                        <div class="font-semibold mb-1">1. Validitas</div>
                                        <div class="pl-4 text-primary-ink/80 dark:text-neutral-300">
                                            {{ $psychologicalTest->validitas ?? '-' }}
                                        </div>
                                    </div>

                                    <!-- 2. Internal Pribadi -->
                                    <div class="border-b border-warm-border dark:border-[#25211e]/40 pb-2">
                                        <div class="font-semibold mb-1">2. Internal Pribadi</div>
                                        <div class="pl-4 text-primary-ink/80 dark:text-neutral-300 whitespace-pre-line">
                                            {{ $psychologicalTest->internal ?? '-' }}
                                        </div>
                                    </div>

                                    <!-- 3. Interpersonal -->
                                    <div class="border-b border-warm-border dark:border-[#25211e]/40 pb-2">
                                        <div class="font-semibold mb-1">3. Interpersonal</div>
                                        <div class="pl-4 text-primary-ink/80 dark:text-neutral-300 whitespace-pre-line">
                                            {{ $psychologicalTest->interpersonal ?? '-' }}
                                        </div>
                                    </div>

                                    <!-- 4. Kapasitas Kerja -->
                                    <div class="border-b border-warm-border dark:border-[#25211e]/40 pb-2">
                                        <div class="font-semibold mb-1">4. Kapasitas Kerja</div>
                                        <div class="pl-4 text-primary-ink/80 dark:text-neutral-300 whitespace-pre-line">
                                            {{ $psychologicalTest->kap_kerja ?? '-' }}
                                        </div>
                                    </div>

                                    <!-- 5. Klinis -->
                                    <div class="border-b border-warm-border dark:border-[#25211e]/40 pb-2">
                                        <div class="font-semibold mb-1">5. Klinis</div>
                                        <div class="pl-4 text-primary-ink/80 dark:text-neutral-300 whitespace-pre-line">
                                            {{ $psychologicalTest->klinik ?? '-' }}
                                        </div>
                                    </div>

                                    <!-- 6. Kesimpulan -->
                                    <div class="border-b border-warm-border dark:border-[#25211e]/40 pb-2">
                                        <div class="font-semibold mb-1">6. Kesimpulan</div>
                                        <div class="pl-4 text-primary-ink/80 dark:text-neutral-300 whitespace-pre-line">
                                            {{ $psychologicalTest->kesimpulan ?? '-' }}
                                        </div>
                                    </div>

                                    <!-- 7. Psikogram -->
                                    <div class="border-b border-warm-border dark:border-[#25211e]/40 pb-2">
                                        <div class="font-semibold mb-1">7. Psikogram</div>
                                        <div class="pl-4 text-primary-ink/80 dark:text-neutral-300 whitespace-pre-line">
                                            {{ $psychologicalTest->psikogram_formatted }}
                                        </div>
                                    </div>

                                    <!-- 8. Nilai PQ -->
                                    <div class="border-b border-warm-border dark:border-[#25211e]/40 pb-2">
                                        <div class="font-semibold mb-1">8. Nilai PQ</div>
                                        <div class="pl-4 text-primary-ink/80 dark:text-neutral-300">
                                            {{ $psychologicalTest->nilai_pq ?? '-' }}
                                        </div>
                                    </div>

                                    <!-- 9. Tingkat Stres -->
                                    <div>
                                        <div class="font-semibold mb-1">9. Tingkat Stres</div>
                                        <div class="pl-4 text-primary-ink/80 dark:text-neutral-300">
                                            {{ $psychologicalTest->tingkat_stres ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @else
                        <tr class="hover:bg-warm-ivory/50 dark:hover:bg-[#1f1b18]/50 transition-colors duration-150 text-sm text-primary-ink dark:text-neutral-200">
                            <td
                                class="border border-warm-border dark:border-[#25211e] px-4 py-3 text-center font-semibold text-gray-900 dark:text-white">
                                4
                            </td>
                            <td class="border border-warm-border dark:border-[#25211e] px-4 py-3 font-semibold text-gray-900 dark:text-white">
                                Tes Kejiwaan
                            </td>
                            <td class="border border-warm-border dark:border-[#25211e] px-4 py-3 text-center text-gray-500 dark:text-gray-400"
                                colspan="2">
                                Data Tes Kejiwaan Tidak Tersedia
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>