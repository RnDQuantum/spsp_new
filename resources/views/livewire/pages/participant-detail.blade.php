<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header dengan tombol kembali - DARK MODE READY -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('shortlist') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium 
                           text-gray-700 dark:text-gray-300 
                           bg-white dark:bg-gray-800 
                           border border-gray-300 dark:border-gray-600 
                           rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 
                           focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 
                           dark:focus:ring-blue-400">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                        </path>
                    </svg>
                    Kembali ke Shortlist
                </a>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Detail Peserta Assessment</h1>
            </div>
        </div>
    </div>

    @if ($participant)
    <!-- Biodata Singkat - DARK MODE READY -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Biodata Peserta</h2>
        </div>

        <div class="px-6 py-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Informasi Personal -->
                <div class="space-y-4">
                    <h3
                        class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-600 pb-2">
                        Informasi Personal</h3>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama
                            Lengkap</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $participant->name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">NIP/SKB
                            Number</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $participant->skb_number ?? '-' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $participant->email ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Telepon</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $participant->phone ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis
                            Kelamin</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $participant->gender ?? '-' }}
                        </p>
                    </div>
                </div>

                <!-- Informasi Assessment -->
                <div class="space-y-4">
                    <h3
                        class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-600 pb-2">
                        Informasi Assessment</h3>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kode
                            Proyek</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                      bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                                {{ $participant->assessmentEvent->code ?? '-' }}
                            </span>
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama
                            Proyek</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $participant->assessmentEvent->name ?? '-' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Batch</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $participant->batch->name ?? '-' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Posisi</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $participant->positionFormation->name ?? '-' }}</p>
                        @if ($participant->positionFormation?->code)
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Kode:
                            {{ $participant->positionFormation->code }}</p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">No. Test</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $participant->test_number ?? '-' }}</p>
                    </div>
                </div>

                <!-- Informasi Tanggal -->
                <div class="space-y-4">
                    <h3
                        class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-600 pb-2">
                        Informasi Tanggal</h3>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal
                            Assessment</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $participant->assessment_date?->format('d M Y') ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal
                            Dibuat</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $participant->created_at?->format('d M Y H:i') ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Terakhir
                            Diupdate</label>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $participant->updated_at?->format('d M Y H:i') ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu Individual Report - DARK MODE READY -->
    <!-- MENU INDIVIDUAL REPORT - SEMUA BUTTON FIXED DI BAWAH -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Individual Report</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Pilih laporan individual yang ingin dilihat
                untuk peserta ini</p>
        </div>

        <div class="px-6 py-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- KARTU 1: General Matching -->
                <div class="h-full">
                    <div
                        class="flex flex-col h-full border border-gray-200 dark:border-gray-600 rounded-lg p-6 hover:shadow-md dark:hover:shadow-gray-700/50 transition-shadow bg-white dark:bg-gray-700/50">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">General
                                        Matching</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Analisis kesesuaian aspek
                                        potensi dan kompetensi</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow mb-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Laporan yang menampilkan analisis mendalam terhadap aspek potensi dan kompetensi
                                peserta, termasuk perbandingan dengan standar yang ditetapkan.
                            </p>
                        </div>
                        <div class="mt-auto">
                            <a href="{{ route('general_matching', ['eventCode' => $participant->assessmentEvent->code, 'testNumber' => $participant->test_number]) }}"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors">
                                Lihat General Matching
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- KARTU 2: General Mapping -->
                <div class="h-full">
                    <div
                        class="flex flex-col h-full border border-gray-200 dark:border-gray-600 rounded-lg p-6 hover:shadow-md dark:hover:shadow-gray-700/50 transition-shadow bg-white dark:bg-gray-700/50">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">General Mapping
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Mapping komprehensif dengan
                                        visualisasi grafik</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow mb-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Laporan yang menampilkan mapping komprehensif dengan visualisasi grafik, analisis
                                gap, dan kesimpulan berdasarkan toleransi yang ditetapkan.
                            </p>
                        </div>
                        <div class="mt-auto">
                            <a href="{{ route('general_mapping', ['eventCode' => $participant->assessmentEvent->code, 'testNumber' => $participant->test_number]) }}"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors">
                                Lihat General Mapping
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- KARTU 3: Managerial Competency Mapping -->
                <div class="h-full">
                    <div
                        class="flex flex-col h-full border border-gray-200 dark:border-gray-600 rounded-lg p-6 hover:shadow-md dark:hover:shadow-gray-700/50 transition-shadow bg-white dark:bg-gray-700/50">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Managerial
                                        Competency Mapping</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Mapping aspek kompetensi
                                        saja</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow mb-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Laporan yang fokus pada aspek kompetensi (Managerial Competency) dengan visualisasi
                                spider plot, analisis gap, dan tolerance selector.
                            </p>
                        </div>
                        <div class="mt-auto">
                            <a href="{{ route('general_mc_mapping', ['eventCode' => $participant->assessmentEvent->code, 'testNumber' => $participant->test_number]) }}"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 dark:focus:ring-orange-400 transition-colors">
                                Lihat MC Mapping
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- KARTU 4: Psychology Mapping -->
                <div class="h-full">
                    <div
                        class="flex flex-col h-full border border-gray-200 dark:border-gray-600 rounded-lg p-6 hover:shadow-md dark:hover:shadow-gray-700/50 transition-shadow bg-white dark:bg-gray-700/50">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Psychology
                                        Mapping</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Mapping aspek potensi saja
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow mb-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Laporan yang fokus pada aspek potensi (Psychological Potential) dengan visualisasi
                                spider plot, analisis gap, dan tolerance selector.
                            </p>
                        </div>
                        <div class="mt-auto">
                            <a href="{{ route('general_psy_mapping', ['eventCode' => $participant->assessmentEvent->code, 'testNumber' => $participant->test_number]) }}"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-colors">
                                Lihat PSY Mapping
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- KARTU 5: Ringkasan MC Mapping -->
                <div class="h-full">
                    <div
                        class="flex flex-col h-full border border-gray-200 dark:border-gray-600 rounded-lg p-6 hover:shadow-md dark:hover:shadow-gray-700/50 transition-shadow bg-white dark:bg-gray-700/50">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 bg-teal-100 dark:bg-teal-900/30 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-teal-600 dark:text-teal-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Ringkasan MC
                                        Mapping</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Ringkasan kompetensi dengan
                                        kesimpulan</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow mb-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Laporan ringkasan aspek kompetensi dengan individual rating, standard rating, dan
                                kesimpulan untuk setiap aspek kompetensi.
                            </p>
                        </div>
                        <div class="mt-auto">
                            <a href="{{ route('ringkasan_mc_mapping', ['eventCode' => $participant->assessmentEvent->code, 'testNumber' => $participant->test_number]) }}"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 dark:focus:ring-teal-400 transition-colors">
                                Lihat Ringkasan MC
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- KARTU 6: Ringkasan Assessment -->
                <div class="h-full">
                    <div
                        class="flex flex-col h-full border border-gray-200 dark:border-gray-600 rounded-lg p-6 hover:shadow-md dark:hover:shadow-gray-700/50 transition-shadow bg-white dark:bg-gray-700/50">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 bg-cyan-100 dark:bg-cyan-900/30 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-cyan-600 dark:text-cyan-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Ringkasan
                                        Assessment</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Ringkasan hasil keseluruhan
                                        assessment</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow mb-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Laporan ringkasan hasil assessment keseluruhan yang menampilkan skor potensi dan
                                kompetensi, bobot penilaian, gap score, dan kesimpulan akhir peserta.
                            </p>
                        </div>
                        <div class="mt-auto">
                            <a href="{{ route('ringkasan_assessment', ['eventCode' => $participant->assessmentEvent->code, 'testNumber' => $participant->test_number]) }}"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 dark:focus:ring-cyan-400 transition-colors">
                                Lihat Ringkasan Assessment
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- KARTU 7: Spider Plot -->
                <div class="h-full">
                    <div
                        class="flex flex-col h-full border border-gray-200 dark:border-gray-600 rounded-lg p-6 hover:shadow-lg dark:hover:shadow-gray-700/50 transition-shadow bg-white dark:bg-gray-700/50">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Spider Plot
                                        Analysis</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Visualisasi radar chart
                                        dengan tolerance selector</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow mb-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Analisis visual dengan spider plot untuk Potensi, Kompetensi, dan General Mapping
                                dengan fitur tolerance selector yang dapat diubah secara real-time.
                            </p>
                        </div>
                        <div class="mt-auto">
                            <a href="{{ route('spider_plot', ['eventCode' => $participant->assessmentEvent->code, 'testNumber' => $participant->test_number]) }}"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 dark:focus:ring-purple-400 transition-colors">
                                Lihat Spider Plot
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- KARTU 8: Gambaran Ringkasan -->
                <div class="h-full">
                    <div
                        class="flex flex-col h-full border border-gray-200 dark:border-gray-600 rounded-lg p-6 hover:shadow-lg dark:hover:shadow-gray-700/50 transition-shadow bg-white dark:bg-gray-700/50">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 bg-pink-100 dark:bg-pink-900/30 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-pink-600 dark:text-pink-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Laporan Individu
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Visualisasi gambaran
                                        ringkasan untuk Potensi, Kompetensi, dan General Mapping</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow mb-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Analisis visual dengan gambaran ringkasan untuk Potensi, Kompetensi, dan General
                                Mapping
                            </p>
                        </div>
                        <div class="mt-auto">
                            <a href="{{ route('final_report', ['eventCode' => $participant->assessmentEvent->code, 'testNumber' => $participant->test_number]) }}"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 dark:focus:ring-pink-400 transition-colors">
                                Lihat Gambaran Ringkasan
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @else
    <!-- Peserta tidak ditemukan - DARK MODE READY -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-8 text-center">
            <div class="flex flex-col items-center">
                <svg class="h-12 w-12 text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z">
                    </path>
                </svg>
                <p class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Peserta tidak ditemukan</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Data peserta dengan kode proyek dan nomor test
                    tersebut tidak ditemukan.</p>
            </div>
        </div>
    </div>
    @endif
</div>