<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header dengan tombol kembali -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('shortlist') }}"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                        </path>
                    </svg>
                    Kembali ke Shortlist
                </a>
                <h1 class="text-3xl font-bold text-gray-900">Detail Peserta Assessment</h1>
            </div>
        </div>
    </div>

    @if ($participant)
        <!-- Biodata Singkat -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Biodata Peserta</h2>
            </div>

            <div class="px-6 py-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Informasi Personal -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">Informasi Personal
                        </h3>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $participant->name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">NIP/SKB Number</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $participant->skb_number ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $participant->email ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Telepon</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $participant->phone ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $participant->gender ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Informasi Assessment -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">Informasi Assessment
                        </h3>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kode Proyek</label>
                            <p class="mt-1 text-sm text-gray-900">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $participant->assessmentEvent->code ?? '-' }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Proyek</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $participant->assessmentEvent->name ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Batch</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $participant->batch->name ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Posisi</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $participant->positionFormation->name ?? '-' }}</p>
                            @if ($participant->positionFormation?->code)
                                <p class="mt-1 text-xs text-gray-500">Kode: {{ $participant->positionFormation->code }}
                                </p>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">No. Test</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $participant->test_number ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Informasi Tanggal -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">Informasi Tanggal
                        </h3>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal Assessment</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $participant->assessment_date?->format('d M Y') ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal Dibuat</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $participant->created_at?->format('d M Y H:i') ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Terakhir Diupdate</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $participant->updated_at?->format('d M Y H:i') ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu Individual Report -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Individual Report</h2>
                <p class="mt-1 text-sm text-gray-600">Pilih laporan individual yang ingin dilihat untuk peserta ini</p>
            </div>

            <div class="px-6 py-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- General Matching -->
                    <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">General Matching</h3>
                                    <p class="text-sm text-gray-600">Analisis kesesuaian aspek potensi dan kompetensi
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <p class="text-sm text-gray-600">
                                Laporan yang menampilkan analisis mendalam terhadap aspek potensi dan kompetensi
                                peserta,
                                termasuk perbandingan dengan standar yang ditetapkan.
                            </p>
                        </div>

                        <a href="{{ route('general_matching', ['eventCode' => $participant->assessmentEvent->code, 'testNumber' => $participant->test_number]) }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            Lihat General Matching
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </a>
                    </div>

                    <!-- General Mapping -->
                    <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">General Mapping</h3>
                                    <p class="text-sm text-gray-600">Mapping komprehensif dengan visualisasi grafik</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <p class="text-sm text-gray-600">
                                Laporan yang menampilkan mapping komprehensif dengan visualisasi grafik,
                                analisis gap, dan kesimpulan berdasarkan toleransi yang ditetapkan.
                            </p>
                        </div>

                        <a href="{{ route('general_mapping', ['eventCode' => $participant->assessmentEvent->code, 'testNumber' => $participant->test_number]) }}"
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                            Lihat General Mapping
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>

                    <!-- Spider Plot Card -->
                    <div class="bg-white rounded-lg border border-gray-200 p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Spider Plot Analysis</h3>
                                <p class="text-sm text-gray-600">Visualisasi radar chart dengan tolerance selector</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <p class="text-sm text-gray-600">
                                Analisis visual dengan spider plot untuk Potensi, Kompetensi, dan General Mapping
                                dengan fitur tolerance selector yang dapat diubah secara real-time.
                            </p>
                        </div>

                        <a href="{{ route('spider_plot', ['eventCode' => $participant->assessmentEvent->code, 'testNumber' => $participant->test_number]) }}"
                            class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors">
                            Lihat Spider Plot
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Peserta tidak ditemukan -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-8 text-center">
                <div class="flex flex-col items-center">
                    <svg class="h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z">
                        </path>
                    </svg>
                    <p class="text-lg font-medium text-gray-900 mb-2">Peserta tidak ditemukan</p>
                    <p class="text-sm text-gray-600">Data peserta dengan kode proyek dan nomor test tersebut tidak
                        ditemukan.</p>
                </div>
            </div>
        </div>
    @endif
</div>
