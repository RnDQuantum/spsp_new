<div class="max-w-full mx-auto bg-white p-6 rounded-lg shadow-md">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        <h1 class="text-2xl font-bold text-gray-800 uppercase tracking-wider">
            Laporan Hasil Tes MMPI
        </h1>

        <!-- Search and Filter -->
        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
            <div class="relative flex-grow">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" placeholder="Cari berdasarkan no test, kode proyek..."
                    class="block w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" />
            </div>

            <select
                class="form-select pl-3 pr-10 py-2 text-base border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                <option value="">Semua Tingkat Stres</option>
                <option value="normal">Normal</option>
                <option value="ringan">Ringan</option>
                <option value="sedang">Sedang</option>
                <option value="berat">Berat</option>
                <option value="sangat berat">Sangat Berat</option>
            </select>

            <button
                class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                </svg>
                Filter
            </button>
        </div>
    </div>

    <!-- Tabel Data -->
    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg mb-8">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-300 table-fixed border-collapse">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" style="width: 60px;"
                            class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 border border-gray-300">
                            ID</th>
                        <th scope="col" style="width: 120px;"
                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 border border-gray-300">
                            KODE PROYEK</th>
                        <th scope="col" style="width: 120px;"
                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 border border-gray-300">NO.
                            TEST</th>
                        <th scope="col" style="width: 200px;"
                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 border border-gray-300">
                            VALIDITAS</th>
                        <th scope="col" style="width: 300px;"
                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 border border-gray-300">
                            INTERNAL</th>
                        <th scope="col" style="width: 300px;"
                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 border border-gray-300">
                            INTERPERSONAL</th>
                        <th scope="col" style="width: 300px;"
                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 border border-gray-300">
                            KAP. KERJA</th>
                        <th scope="col" style="width: 200px;"
                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 border border-gray-300">
                            KLINIK</th>
                        <th scope="col" style="width: 300px;"
                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 border border-gray-300">
                            KESIMPULAN</th>
                        <th scope="col" style="width: 300px;"
                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 border border-gray-300">
                            PSIKOGRAM</th>
                        <th scope="col" style="width: 80px;"
                            class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900 border border-gray-300">
                            NILAI PQ</th>
                        <th scope="col" style="width: 120px;"
                            class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900 border border-gray-300">
                            TINGKAT STRES</th>
                        <th scope="col" style="width: 80px;"
                            class="relative py-3.5 pl-3 pr-4 sm:pr-6 text-center text-sm font-semibold text-gray-900 border border-gray-300">
                            AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @if ($mmpiResults->count() > 0)
                        @foreach ($mmpiResults as $result)
                            <tr class="hover:bg-gray-50">
                                <td style="width: 60px;"
                                    class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 border border-gray-300">
                                    {{ $result->id }}</td>
                                <td style="width: 120px;"
                                    class="px-3 py-4 text-sm text-gray-500 border border-gray-300">
                                    {{ $result->kode_proyek }}</td>
                                <td style="width: 120px;"
                                    class="px-3 py-4 text-sm text-gray-900 font-medium border border-gray-300">
                                    {{ $result->no_test }}</td>
                                <td style="width: 200px;"
                                    class="px-3 py-4 text-sm text-gray-500 border border-gray-300">
                                    <div class="whitespace-pre-line">{{ $result->validitas }}</div>
                                </td>
                                <td style="width: 300px;"
                                    class="px-3 py-4 text-sm text-gray-500 border border-gray-300">
                                    <div class="whitespace-pre-line">{{ $result->internal }}</div>
                                </td>
                                <td style="width: 300px;"
                                    class="px-3 py-4 text-sm text-gray-500 border border-gray-300">
                                    <div class="whitespace-pre-line">{{ $result->interpersonal }}</div>
                                </td>
                                <td style="width: 300px;"
                                    class="px-3 py-4 text-sm text-gray-500 border border-gray-300">
                                    <div class="whitespace-pre-line">{{ $result->kap_kerja }}</div>
                                </td>
                                <td style="width: 200px;"
                                    class="px-3 py-4 text-sm text-gray-500 border border-gray-300">
                                    <div class="whitespace-pre-line">{{ $result->klinik }}</div>
                                </td>
                                <td style="width: 300px;"
                                    class="px-3 py-4 text-sm text-gray-500 border border-gray-300">
                                    <div class="whitespace-pre-line">{{ $result->kesimpulan }}</div>
                                </td>
                                <td style="width: 300px;"
                                    class="px-3 py-4 text-sm text-gray-500 border border-gray-300">
                                    <div class="whitespace-pre-line">{{ $result->psikogram }}</div>
                                </td>
                                <td style="width: 80px;" class="px-3 py-4 text-sm text-center border border-gray-300">
                                    <div
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if ($result->nilai_pq >= 80) bg-green-100 text-green-800
                                        @elseif($result->nilai_pq >= 70) bg-green-50 text-green-700
                                        @elseif($result->nilai_pq >= 60) bg-blue-100 text-blue-800
                                        @elseif($result->nilai_pq >= 50) bg-blue-50 text-blue-700
                                        @elseif($result->nilai_pq >= 40) bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ $result->nilai_pq }}
                                    </div>
                                </td>
                                <td style="width: 120px;"
                                    class="px-3 py-4 text-sm text-center border border-gray-300">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if ($result->tingkat_stres == 'normal') bg-green-100 text-green-800
                                        @elseif($result->tingkat_stres == 'ringan') bg-blue-100 text-blue-800
                                        @elseif($result->tingkat_stres == 'sedang') bg-yellow-100 text-yellow-800
                                        @elseif($result->tingkat_stres == 'berat') bg-orange-100 text-orange-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($result->tingkat_stres) }}
                                    </span>
                                </td>
                                <td style="width: 80px;"
                                    class="relative py-4 pl-3 pr-4 text-center text-sm font-medium sm:pr-6 border border-gray-300">
                                    <div class="flex justify-center space-x-2">
                                        <button type="button" class="text-blue-600 hover:text-blue-900"
                                            title="Lihat Detail">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                <path fill-rule="evenodd"
                                                    d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                        <button type="button" class="text-blue-600 hover:text-blue-900"
                                            title="Export PDF">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="13"
                                class="px-6 py-10 text-center text-sm text-gray-500 border border-gray-300">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                        </path>
                                    </svg>
                                    <p class="text-base font-medium">Tidak ada data ditemukan</p>
                                    <p class="text-sm text-gray-400 mt-1">Pastikan tabel mmpi_results ada dan
                                        berisi data.</p>
                                    <button
                                        class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                                        Muat Ulang
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-between">
        <div class="flex-1 flex justify-between sm:hidden">
            <!-- Mobile pagination -->
            {{ $mmpiResults->links() }}
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Menampilkan
                    <span class="font-medium">{{ $mmpiResults->firstItem() ?? 0 }}</span>
                    sampai
                    <span class="font-medium">{{ $mmpiResults->lastItem() ?? 0 }}</span>
                    dari
                    <span class="font-medium">{{ $mmpiResults->total() }}</span>
                    hasil
                </p>
            </div>
            <div>
                {{ $mmpiResults->links() }}
            </div>
        </div>
    </div>
</div>
