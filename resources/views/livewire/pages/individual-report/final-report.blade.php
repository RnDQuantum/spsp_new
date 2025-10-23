<div>
    <div class="mb-8">
        <h1 class="text-3xl text-center font-bold text-gray-900 dark:text-white">LAPORAN INDIVIDU</h1>
        <div class="text-center mt-4">
            <p class="text-lg text-gray-700 dark:text-gray-300">
                {{ $eventName }} - {{ $institutionName }}
            </p>
        </div>
    </div>

    {{-- Biodata Section --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">BIODATA PESERTA</h2>

        <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 flex flex-col md:flex-row md:items-start md:justify-between">
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
                            <td class="py-1 font-medium text-gray-700 dark:text-gray-300">Tanggal Assessment</td>
                            <td class="py-1 text-gray-900 dark:text-white">
                                {{ $participant->assessment_date->format('d F Y') }}
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
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Psychology Mapping</h2>
        <livewire:pages.individual-report.general-psy-mapping :eventCode="$eventCode" :testNumber="$testNumber" :isStandalone="false"
            :showHeader="false" :showInfoSection="false" :showTable="true" :showRatingChart="true" :showScoreChart="true"
            :key="'psy-mapping-' . $eventCode . '-' . $testNumber" />
    </div>

    {{-- Interpretation Section potensi --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Interpretasi Hasil Assessment</h2>
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
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Managerial Competency Mapping</h2>
        <livewire:pages.individual-report.general-mc-mapping :eventCode="$eventCode" :testNumber="$testNumber" :isStandalone="false"
            :showHeader="false" :showInfoSection="false" :showTable="true" :showRatingChart="true" :showScoreChart="true"
            :key="'mc-mapping-' . $eventCode . '-' . $testNumber" />
    </div>

    {{-- Interpretation Section kompetensi --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Interpretasi Kompetensi</h2>
        <livewire:pages.individual-report.interpretation-section :eventCode="$eventCode" :testNumber="$testNumber"
            :isStandalone="false" :showHeader="false" :showPotensi="false" :showKompetensi="true" :key="'interpretation-kompetensi-' . $eventCode . '-' . $testNumber" />
    </div>

    {{-- Ringkasan Assessment Section - Table Only --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Ringkasan Hasil Assessment</h2>
        <livewire:pages.individual-report.ringkasan-assessment :eventCode="$eventCode" :testNumber="$testNumber" :isStandalone="false"
            :showHeader="false" :showBiodata="false" :showInfoSection="false" :showTable="true" :key="'ringkasan-assessment-' . $eventCode . '-' . $testNumber" />
    </div>

    {{-- Hasil Rekomendasi Section --}}
    <div class="mb-8">
        <div class="mx-auto shadow overflow-hidden bg-white dark:bg-gray-800">
            <!-- Header -->
            <div class="bg-teal-600 dark:bg-teal-700 py-3">
                <h2 class="text-center text-xl font-bold uppercase tracking-wide text-white">
                    HASIL REKOMENDASI
                </h2>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <!-- Header Row -->
                    <thead>
                        <tr class="bg-green-500">
                            <th class="border-2 border-black px-4 py-3 text-center font-bold text-black w-16">NO.</th>
                            <th class="border-2 border-black px-4 py-3 text-center font-bold text-black w-48">ASPEK
                                PENILAIAN</th>
                            <th class="border-2 border-black px-4 py-3 text-center font-bold text-black">SKOR/ HASIL
                            </th>
                            <th class="border-2 border-black px-4 py-3 text-center font-bold text-black w-64">KESIMPULAN
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
                                Psikotest
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
                                class="border-2 border-black px-4 py-3 text-center font-bold text-lg align-middle
                    @if ($finalAssessment && $finalAssessment->conclusion_code == 'MS') bg-green-500 text-black
                    @elseif($finalAssessment && $finalAssessment->conclusion_code == 'MMS')
                        bg-yellow-400 text-black
                    @elseif($finalAssessment && $finalAssessment->conclusion_code == 'TMS')
                        bg-red-500 text-white
                    @else
                        bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white @endif">
                                {{ $finalAssessment ? $finalAssessment->conclusion_text : 'Tidak Ikut Assessment' }}
                            </td>
                        </tr>

                        <!-- Row 2: Tes Kejiwaan (Header) -->
                        <tr class="bg-green-500">
                            <th class="border-2 border-black px-4 py-3 text-center font-bold text-black">NO.</th>
                            <th class="border-2 border-black px-4 py-3 text-center font-bold text-black">ASPEK PENILAIAN
                            </th>
                            <th class="border-2 border-black px-4 py-3 text-center font-bold text-black" colspan="2">
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
