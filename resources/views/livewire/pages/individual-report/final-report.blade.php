<div class="mt-8 mb-8 px-6 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-2xl rounded-lg">
    <div class="mb-8">
        <h1 class="text-3xl text-center font-bold text-gray-900 dark:text-white mt-8">LAPORAN INDIVIDU</h1>
        <div class="text-center mt-4">
            <p class="text-lg text-gray-700 dark:text-gray-300">
                {{ $eventName }} - {{ $institutionName }}
            </p>
        </div>
    </div>

    {{-- Tolerance Selector Component --}}
    <div class="mb-8">
        <div wire:loading.delay.long class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-8 shadow-2xl">
                <div class="flex flex-col items-center gap-4">
                    <svg class="animate-spin h-12 w-12 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">Memperbarui laporan...</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Mohon tunggu sebentar</p>
                </div>
            </div>
        </div>
        @livewire('components.tolerance-selector', ['showSummary' => false])
    </div>

    {{-- Biodata Section --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 pl-5">BIODATA PESERTA</h2>

        <div
            class="bg-white dark:bg-gray-800 rounded-lg p-6 flex flex-col md:flex-row md:items-start md:justify-between">
            {{-- Tabel Biodata --}}
            <div class="w-full md:w-2/3">
                <table class="w-full border-collapse">
                    <tbody>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <td class="py-1 font-medium text-gray-700 dark:text-gray-300 w-1/3">Nomor Tes</td>
                            <td class="py-1 text-gray-900 dark:text-white">{{ $participant->test_number }}</td>
                        </tr>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <td class="py-1 font-medium text-gray-700 dark:text-gray-300">Nomor SKB</td>
                            <td class="py-1 text-gray-900 dark:text-white">{{ $participant->skb_number }}</td>
                        </tr>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <td class="py-1 font-medium text-gray-700 dark:text-gray-300">Nama</td>
                            <td class="py-1 text-gray-900 dark:text-white">{{ $participant->name }}</td>
                        </tr>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <td class="py-1 font-medium text-gray-700 dark:text-gray-300">Email</td>
                            <td class="py-1 text-gray-900 dark:text-white">{{ $participant->email }}</td>
                        </tr>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <td class="py-1 font-medium text-gray-700 dark:text-gray-300">Telepon</td>
                            <td class="py-1 text-gray-900 dark:text-white">{{ $participant->phone }}</td>
                        </tr>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <td class="py-1 font-medium text-gray-700 dark:text-gray-300">Jenis Kelamin</td>
                            <td class="py-1 text-gray-900 dark:text-white">
                                {{ $participant->gender == 'M' ? 'Laki-laki' : 'Perempuan' }}
                            </td>
                        </tr>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <td class="py-1 font-medium text-gray-700 dark:text-gray-300">Formasi Jabatan</td>
                            <td class="py-1 text-gray-900 dark:text-white">{{ $participant->positionFormation->name }}
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 font-medium text-gray-700 dark:text-gray-300">Tanggal Asesmen</td>
                            <td class="py-1 text-gray-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($participant->assessment_date)->translatedFormat('d F Y') }}

                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Placeholder Foto --}}
            <div class="w-full md:w-1/3 flex justify-center md:justify-end mt-6 md:mt-0">
                <div
                    class="w-47 h-65 bg-gray-200 dark:bg-gray-700 rounded-md overflow-hidden flex items-center justify-center">
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
        <livewire:pages.individual-report.general-matching :eventCode="$eventCode" :testNumber="$testNumber" :isStandalone="false"
            :showHeader="false" :showInfoSection="false" :showPotensi="true" :showKompetensi="false" :key="'potensi-only-' . $eventCode . '-' . $testNumber" />
    </div>

    {{-- Psychology Mapping Section --}}
    <div class="mb-8">
        <h2 class="text-2xl text-center font-bold text-gray-900 dark:text-white italic">Psychology Mapping</h2>
        <livewire:pages.individual-report.general-psy-mapping :eventCode="$eventCode" :testNumber="$testNumber" :isStandalone="false"
            :showHeader="false" :showInfoSection="false" :showTable="true" :showRatingChart="true" :showScoreChart="true"
            :showRankingInfo="false" :key="'psy-mapping-' . $eventCode . '-' . $testNumber" />
    </div>

    {{-- Interpretation Section potensi --}}
    <div class="mb-8">
        {{-- <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Interpretasi Hasil Assessment</h2> --}}
        <livewire:pages.individual-report.interpretation-section :eventCode="$eventCode" :testNumber="$testNumber"
            :isStandalone="false" :showHeader="false" :showPotensi="true" :showKompetensi="false" :key="'interpretation-' . $eventCode . '-' . $testNumber" />
    </div>

    {{-- General Matching Section kompetensi --}}
    <div class="mb-8">
        <livewire:pages.individual-report.general-matching :eventCode="$eventCode" :testNumber="$testNumber" :isStandalone="false"
            :showHeader="false" :showInfoSection="false" :showPotensi="false" :showKompetensi="true" :key="'kompetensi-only-' . $eventCode . '-' . $testNumber" />
    </div>

    {{-- Managerial Competency Mapping Section --}}
    <div class="mb-8">
        <h2 class="text-2xl text-center font-bold text-gray-900 dark:text-white italic">Managerial Competency
            Mapping</h2>
        <livewire:pages.individual-report.general-mc-mapping :eventCode="$eventCode" :testNumber="$testNumber" :isStandalone="false"
            :showHeader="false" :showInfoSection="false" :showTable="true" :showRatingChart="true" :showScoreChart="true"
            :showRankingInfo="false" :key="'mc-mapping-' . $eventCode . '-' . $testNumber" />
    </div>

    {{-- Interpretation Section kompetensi --}}
    <div class="mb-8">
        {{-- <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Interpretasi Kompetensi</h2> --}}
        <livewire:pages.individual-report.interpretation-section :eventCode="$eventCode" :testNumber="$testNumber"
            :isStandalone="false" :showHeader="false" :showPotensi="false" :showKompetensi="true" :key="'interpretation-kompetensi-' . $eventCode . '-' . $testNumber" />
    </div>

    {{-- Ringkasan Asesmen Section - Table Only --}}
    <div class="mb-8">
        <h2 class="text-2xl text-center font-bold text-gray-900 dark:text-white mb-4">Ringkasan Hasil Asesmen</h2>
        <livewire:pages.individual-report.ringkasan-assessment :eventCode="$eventCode" :testNumber="$testNumber" :isStandalone="false"
            :showHeader="false" :showBiodata="false" :showInfoSection="false" :showTable="true" :key="'ringkasan-assessment-' . $eventCode . '-' . $testNumber" />
    </div>

    {{-- Hasil Rekomendasi Section --}}
    <div class="mb-8">
        <div class="mx-auto shadow overflow-hidden bg-white dark:bg-gray-800">
            <!-- Header -->
            <div class="bg-gray-600 dark:bg-gray-600 border border-black py-3">
                <h2 class="text-center text-xl font-bold uppercase tracking-wide text-white">
                    HASIL REKOMENDASI
                </h2>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <!-- Header Row -->
                    <thead>
                        <tr class="bg-gray-300 dark:bg-gray-600">
                            <th
                                class="border-2 border-black px-4 py-3 text-center font-bold text-black dark:text-gray-200 w-16">
                                NO.</th>
                            <th
                                class="border-2 border-black px-4 py-3 text-center font-bold text-black dark:text-gray-200 w-48">
                                ASPEK
                                PENILAIAN</th>
                            <th
                                class="border-2 border-black px-4 py-3 text-center font-bold text-black dark:text-gray-200">
                                SKOR/ HASIL
                            </th>
                            <th
                                class="border-2 border-black px-4 py-3 text-center font-bold text-black dark:text-gray-200 w-64">
                                KESIMPULAN
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Row 1: Psikotest -->
                        <tr class="bg-white dark:bg-gray-800">
                            <td
                                class="border-2 border-black px-4 py-3 text-center font-semibold text-gray-900 dark:text-white align-top">
                                1
                            </td>
                            <td
                                class="border-2 border-black px-4 py-3 font-semibold text-gray-900 dark:text-white align-top">
                                Potensi dan Kompetensi
                            </td>
                            <td class="border-2 border-black px-4 py-3 text-gray-900 dark:text-white align-top">
                                <div class="space-y-2">
                                    <div class="flex">
                                        <span class="font-semibold w-32">1. IQ</span>
                                        <span class="flex-1">: 97</span>
                                    </div>
                                    <div class="flex">
                                        <span class="font-semibold w-32">2. Total Score</span>
                                        <span class="flex-1">:
                                            {{ $finalAssessment ? number_format($finalAssessment->total_individual_score, 2) : '0.00' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td
                                class="border-2 border-black px-4 py-3 text-center font-bold text-lg align-middle {{ $this->getFinalConclusionColorClass() }}">
                                {{ $this->getFinalConclusionText() }}
                            </td>
                        </tr>

                        <!-- Row 2: Tes Kejiwaan (Header) -->
                        <tr class="bg-gray-300 dark:bg-gray-600">
                            <th
                                class="border-2 border-black px-4 py-3 text-center font-bold text-black dark:text-gray-200">
                                NO.</th>
                            <th
                                class="border-2 border-black px-4 py-3 text-center font-bold text-black dark:text-gray-200">
                                ASPEK PENILAIAN
                            </th>
                            <th class="border-2 border-black px-4 py-3 text-center font-bold text-black dark:text-gray-200"
                                colspan="2">
                                SKOR/ HASIL</th>
                        </tr>

                        <!-- Row 3: Tes Kejiwaan (Content) -->
                        @if ($psychologicalTest)
                            <tr class="bg-white dark:bg-gray-800">
                                <td
                                    class="border-2 border-black px-4 py-3 text-center font-semibold text-gray-900 dark:text-white align-top">
                                    2
                                </td>
                                <td
                                    class="border-2 border-black px-4 py-3 font-semibold text-gray-900 dark:text-white align-top">
                                    Tes Kejiwaan
                                </td>
                                <td class="border-2 border-black px-4 py-3 text-gray-900 dark:text-white align-top"
                                    colspan="2">
                                    <div class="space-y-3 text-sm">
                                        <!-- 1. Validitas -->
                                        <div class="border-b border-gray-300 pb-2">
                                            <div class="font-semibold mb-1">1. Validitas</div>
                                            <div class="pl-4 text-gray-700 dark:text-gray-300">
                                                {{ $psychologicalTest->validitas ?? '-' }}
                                            </div>
                                        </div>

                                        <!-- 2. Internal Pribadi -->
                                        <div class="border-b border-gray-300 pb-2">
                                            <div class="font-semibold mb-1">2. Internal Pribadi</div>
                                            <div class="pl-4 text-gray-700 dark:text-gray-300 whitespace-pre-line">
                                                {{ $psychologicalTest->internal ?? '-' }}
                                            </div>
                                        </div>

                                        <!-- 3. Interpersonal -->
                                        <div class="border-b border-gray-300 pb-2">
                                            <div class="font-semibold mb-1">3. Interpersonal</div>
                                            <div class="pl-4 text-gray-700 dark:text-gray-300 whitespace-pre-line">
                                                {{ $psychologicalTest->interpersonal ?? '-' }}
                                            </div>
                                        </div>

                                        <!-- 4. Kapasitas Kerja -->
                                        <div class="border-b border-gray-300 pb-2">
                                            <div class="font-semibold mb-1">4. Kapasitas Kerja</div>
                                            <div class="pl-4 text-gray-700 dark:text-gray-300 whitespace-pre-line">
                                                {{ $psychologicalTest->kap_kerja ?? '-' }}
                                            </div>
                                        </div>

                                        <!-- 5. Klinis -->
                                        <div class="border-b border-gray-300 pb-2">
                                            <div class="font-semibold mb-1">5. Klinis</div>
                                            <div class="pl-4 text-gray-700 dark:text-gray-300 whitespace-pre-line">
                                                {{ $psychologicalTest->klinik ?? '-' }}
                                            </div>
                                        </div>

                                        <!-- 6. Kesimpulan -->
                                        <div class="border-b border-gray-300 pb-2">
                                            <div class="font-semibold mb-1">6. Kesimpulan</div>
                                            <div class="pl-4 text-gray-700 dark:text-gray-300 whitespace-pre-line">
                                                {{ $psychologicalTest->kesimpulan ?? '-' }}
                                            </div>
                                        </div>

                                        <!-- 7. Psikogram -->
                                        <div class="border-b border-gray-300 pb-2">
                                            <div class="font-semibold mb-1">7. Psikogram</div>
                                            <div class="pl-4 text-gray-700 dark:text-gray-300 whitespace-pre-line">
                                                {{ $psychologicalTest->psikogram ?? '-' }}
                                            </div>
                                        </div>

                                        <!-- 8. Nilai PQ -->
                                        <div class="border-b border-gray-300 pb-2">
                                            <div class="font-semibold mb-1">8. Nilai PQ</div>
                                            <div class="pl-4 text-gray-700 dark:text-gray-300">
                                                {{ $psychologicalTest->nilai_pq ?? '-' }}
                                            </div>
                                        </div>

                                        <!-- 9. Tingkat Stres -->
                                        <div>
                                            <div class="font-semibold mb-1">9. Tingkat Stres</div>
                                            <div class="pl-4 text-gray-700 dark:text-gray-300">
                                                {{ $psychologicalTest->tingkat_stres ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @else
                            <tr class="bg-white dark:bg-gray-800">
                                <td
                                    class="border-2 border-black px-4 py-3 text-center font-semibold text-gray-900 dark:text-white">
                                    4
                                </td>
                                <td
                                    class="border-2 border-black px-4 py-3 font-semibold text-gray-900 dark:text-white">
                                    Tes Kejiwaan
                                </td>
                                <td class="border-2 border-black px-4 py-3 text-center text-gray-500 dark:text-gray-400"
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
