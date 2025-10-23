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

    {{-- General Matching Section --}}
    <div class="mb-8">
        <livewire:pages.individual-report.general-matching :eventCode="$eventCode" :testNumber="$testNumber"
            :isStandalone="false" :showHeader="false" :showInfoSection="false" :showPotensi="true"
            :showKompetensi="false" :key="'potensi-only-' . $eventCode . '-' . $testNumber" />
    </div>
    <div class="mb-8">
        <livewire:pages.individual-report.general-matching :eventCode="$eventCode" :testNumber="$testNumber"
            :isStandalone="false" :showHeader="false" :showInfoSection="false" :showPotensi="false"
            :showKompetensi="true" :key="'kompetensi-only-' . $eventCode . '-' . $testNumber" />
    </div>

    {{-- Psychology Mapping Section --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Psychology Mapping</h2>
        <livewire:pages.individual-report.general-psy-mapping :eventCode="$eventCode" :testNumber="$testNumber"
            :isStandalone="false" :showHeader="false" :showInfoSection="false" :showTable="true"
            :showRatingChart="true" :showScoreChart="true" :key="'psy-mapping-' . $eventCode . '-' . $testNumber" />
    </div>

    {{-- Interpretation Section --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Interpretasi Hasil Assessment</h2>
        <livewire:pages.individual-report.interpretation-section :eventCode="$eventCode" :testNumber="$testNumber"
            :isStandalone="false" :showHeader="false" :showPotensi="true" :showKompetensi="true"
            :key="'interpretation-' . $eventCode . '-' . $testNumber" />
    </div>
</div>