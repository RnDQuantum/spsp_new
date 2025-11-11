<div class="max-w-full mx-auto bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white uppercase tracking-wider">
            Laporan Hasil Tes MMPI
        </h1>

        {{-- <div class="flex items-center">
            <label for="perPage" class="text-sm font-medium text-gray-700 dark:text-gray-300 mr-2">Tampilkan</label>
            <select id="perPage" wire:model.live="perPage"
                class="text-sm bg-gray-50 border border-gray-300 text-gray-900 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="-1">All</option>
            </select>
        </div> --}}
    </div>

    <!-- Simple Search Box -->
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        <div class="w-full md:w-1/2 relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 20 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                </svg>
            </div>
            <input wire:model.live.debounce.300ms="search" type="search" id="search"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                placeholder="Cari berdasarkan kode proyek, nomor test, atau tingkat stres..." autocomplete="off">
            @if ($search)
                <button wire:click="resetSearch"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                    title="Hapus pencarian">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            @endif
        </div>

        <!-- Per Page Selector di sebelah kanan -->
        <div class="flex items-center">
            <label for="perPage" class="text-sm font-medium text-gray-700 dark:text-gray-300 mr-2">Tampilkan</label>
            <select id="perPage" wire:model.live="perPage"
                class="text-sm bg-gray-50 border border-gray-300 text-gray-900 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="-1">Semua</option>
            </select>
        </div>
    </div>

    <!-- Search Results Info -->
    @if ($search)
        <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 mb-4">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>
                Menampilkan hasil untuk pencarian "{{ $search }}"
            </span>
            <button wire:click="resetSearch"
                class="ml-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-xs font-medium">
                Hapus pencarian
            </button>
        </div>
    @endif

    <!-- Tabel Data -->
    <div class="overflow-hidden shadow rounded-lg mb-8 border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700 border-collapse">
                <thead class="bg-gray-300 dark:bg-gray-600">
                    <tr>
                        <th
                            class="py-3 px-4 text-center text-sm font-semibold text-gray-900 dark:text-white  border border-gray-200 dark:border-gray-500 w-16">
                            ID
                        </th>

                        <!-- KODE PROYEK - Sortable -->
                        <th
                            class="py-3 px-4 text-center text-sm font-semibold text-gray-900 dark:text-white border border-gray-200 dark:border-gray-500 w-32">
                            <button wire:click="sortBy('kode_proyek')"
                                class="flex items-center justify-center w-full hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                <span>KODE PROYEK</span>
                                <span class="ml-1">
                                    @if ($sortField === 'kode_proyek')
                                        @if ($sortDirection === 'asc')
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" />
                                            </svg>
                                        @endif
                                    @else
                                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M5 10l5-5 5 5H5z M5 12l5 5 5-5H5z" fill-rule="evenodd"
                                                clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </span>
                            </button>
                        </th>

                        <!-- NO. TEST - Sortable -->
                        <th
                            class="py-3 px-4 text-center text-sm font-semibold text-gray-900 dark:text-white border border-gray-200 dark:border-gray-500 w-32">
                            <button wire:click="sortBy('no_test')"
                                class="flex items-center justify-center w-full hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                <span>NO. TEST</span>
                                <span class="ml-1">
                                    @if ($sortField === 'no_test')
                                        @if ($sortDirection === 'asc')
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" />
                                            </svg>
                                        @endif
                                    @else
                                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M5 10l5-5 5 5H5z M5 12l5 5 5-5H5z" fill-rule="evenodd"
                                                clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </span>
                            </button>
                        </th>

                        <th
                            class="py-3 px-4 text-center text-sm font-semibold text-gray-900 dark:text-white border border-gray-200 dark:border-gray-500 min-w-[150px]">
                            VALIDITAS
                        </th>

                        <th
                            class="py-3 px-4 text-center text-sm font-semibold text-gray-900 dark:text-white border border-gray-200 dark:border-gray-500 min-w-[320px]">
                            INTERNAL
                        </th>

                        <th
                            class="py-3 px-4 text-center text-sm font-semibold text-gray-900 dark:text-white border border-gray-200 dark:border-gray-500 min-w-[350px]">
                            INTERPERSONAL
                        </th>

                        <th
                            class="py-3 px-4 text-center text-sm font-semibold text-gray-900 dark:text-white border border-gray-200 dark:border-gray-500 min-w-[350px]">
                            KAP. KERJA
                        </th>

                        <th
                            class="py-3 px-4 text-center text-sm font-semibold text-gray-900 dark:text-white border border-gray-200 dark:border-gray-500 min-w-[200px]">
                            KLINIK
                        </th>

                        <th
                            class="py-3 px-4 text-center text-sm font-semibold text-gray-900 dark:text-white border border-gray-200 dark:border-gray-500 min-w-[350px]">
                            KESIMPULAN
                        </th>

                        <th
                            class="py-3 px-4 text-center text-sm font-semibold text-gray-900 dark:text-white border border-gray-200 dark:border-gray-500 min-w-[250px]">
                            PSIKOGRAM
                        </th>

                        <!-- NILAI PQ - Sortable -->
                        <th
                            class="py-3 px-4 text-center text-sm font-semibold text-gray-900 dark:text-white border border-gray-200 dark:border-gray-500 min-w-[100px]">
                            <button wire:click="sortBy('nilai_pq')"
                                class="flex items-center justify-center w-full hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                <span>NILAI PQ</span>
                                <span class="ml-1">
                                    @if ($sortField === 'nilai_pq')
                                        @if ($sortDirection === 'asc')
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" />
                                            </svg>
                                        @endif
                                    @else
                                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M5 10l5-5 5 5H5z M5 12l5 5 5-5H5z" fill-rule="evenodd"
                                                clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </span>
                            </button>
                        </th>

                        <!-- TINGKAT STRES - Sortable -->
                        <th
                            class="py-3 px-4 text-center text-sm font-semibold text-gray-900 dark:text-white border border-gray-200 dark:border-gray-500 w-32">
                            <button wire:click="sortBy('tingkat_stres')"
                                class="flex items-center justify-center w-full hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                <span>TINGKAT STRES</span>
                                <span class="ml-1">
                                    @if ($sortField === 'tingkat_stres')
                                        @if ($sortDirection === 'asc')
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" />
                                            </svg>
                                        @endif
                                    @else
                                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M5 10l5-5 5 5H5z M5 12l5 5 5-5H5z" fill-rule="evenodd"
                                                clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </span>
                            </button>
                        </th>

                        {{-- <th
                            class="py-3 px-4 text-center text-sm font-semibold text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600 w-24">
                            AKSI
                        </th> --}}
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @if ($mmpiResults->count() > 0)
                        @foreach ($mmpiResults as $result)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <!-- ID: Berubah sesuai paginasi atau All -->
                                <td
                                    class="py-4 px-4 text-center text-sm text-gray-900 dark:text-gray-200 border border-gray-200 dark:border-gray-600 w-16">
                                    @if ($mmpiResults instanceof \Illuminate\Pagination\AbstractPaginator)
                                        {{ $loop->iteration + ($mmpiResults->currentPage() - 1) * $mmpiResults->perPage() }}
                                    @else
                                        {{ $loop->iteration }}
                                    @endif
                                </td>

                                <!-- KODE PROYEK -->
                                <td
                                    class="py-4 px-4 text-center text-sm text-gray-500 dark:text-gray-300 border border-gray-200 dark:border-gray-600 w-32">
                                    {{ $result->event->code ?? '-' }}
                                </td>

                                <!-- NO. TEST -->
                                <td
                                    class="py-4 px-4 text-center text-sm text-gray-900 dark:text-gray-200 font-medium border border-gray-200 dark:border-gray-600 w-32">
                                    {{ $result->no_test ?? '-' }}
                                </td>

                                <!-- VALIDITAS -->
                                <td
                                    class="py-4 px-4 text-sm text-gray-500 dark:text-gray-300 border border-gray-200 dark:border-gray-600 w-56">
                                    <div class="whitespace-pre-line">{{ $result->validitas ?? '-' }}</div>
                                </td>

                                <!-- INTERNAL -->
                                <td
                                    class="py-4 px-4 text-sm text-gray-500 dark:text-gray-300 border border-gray-200 dark:border-gray-600 w-72">
                                    <div class="whitespace-pre-line">{{ $result->internal ?? '-' }}</div>
                                </td>

                                <!-- INTERPERSONAL -->
                                <td
                                    class="py-4 px-4 text-sm text-gray-500 dark:text-gray-300 border border-gray-200 dark:border-gray-600 w-72">
                                    <div class="whitespace-pre-line">{{ $result->interpersonal ?? '-' }}</div>
                                </td>

                                <!-- KAP. KERJA -->
                                <td
                                    class="py-4 px-4 text-sm text-gray-500 dark:text-gray-300 border border-gray-200 dark:border-gray-600 w-72">
                                    <div class="whitespace-pre-line">{{ $result->kap_kerja ?? '-' }}</div>
                                </td>

                                <!-- KLINIK -->
                                <td
                                    class="py-4 px-4 text-sm text-gray-500 dark:text-gray-300 border border-gray-200 dark:border-gray-600 w-56">
                                    <div class="whitespace-pre-line">{{ $result->klinik ?? '-' }}</div>
                                </td>

                                <!-- KESIMPULAN -->
                                <td
                                    class="py-4 px-4 text-sm text-gray-500 dark:text-gray-300 border border-gray-200 dark:border-gray-600 w-72">
                                    <div class="whitespace-pre-line">{{ $result->kesimpulan ?? '-' }}</div>
                                </td>

                                <!-- PSIKOGRAM -->
                                <td
                                    class="py-4 px-4 text-sm text-gray-500 dark:text-gray-300 border border-gray-200 dark:border-gray-600 w-72">
                                    <div class="whitespace-pre-line">{{ $result->psikogram ?? '-' }}</div>
                                </td>

                                <!-- NILAI PQ -->
                                <td class="py-4 px-4 text-center border border-gray-200 dark:border-gray-600 w-24">
                                    <div
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if ($result->nilai_pq >= 80) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                        @elseif($result->nilai_pq >= 70) bg-green-50 text-green-700 dark:bg-green-800 dark:text-green-300
                                        @elseif($result->nilai_pq >= 60) bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                        @elseif($result->nilai_pq >= 50) bg-blue-50 text-blue-700 dark:bg-blue-800 dark:text-blue-300
                                        @elseif($result->nilai_pq >= 40) bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                        @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                                        {{ $result->nilai_pq ?? '-' }}
                                    </div>
                                </td>

                                <!-- TINGKAT STRES -->
                                <td class="py-4 px-4 text-center border border-gray-200 dark:border-gray-600 w-32">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if ($result->tingkat_stres == 'normal') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                        @elseif($result->tingkat_stres == 'ringan') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                        @elseif($result->tingkat_stres == 'sedang') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                        @elseif($result->tingkat_stres == 'berat') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                        @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                                        {{ ucfirst($result->tingkat_stres ?? '-') }}
                                    </span>
                                </td>

                                <!-- AKSI -->
                                {{-- <td class="py-4 px-4 text-center border border-gray-200 dark:border-gray-600 w-24">
                                    <div class="flex justify-center space-x-2">
                                        <button type="button"
                                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                            title="Lihat Detail">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                <path fill-rule="evenodd"
                                                    d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                        <button type="button"
                                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                            title="Export PDF">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </td> --}}
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="13"
                                class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mb-4" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                        </path>
                                    </svg>
                                    <p class="text-base font-medium dark:text-gray-300">Tidak ada data ditemukan</p>
                                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Pastikan tabel
                                        mmpi_results
                                        ada dan berisi data.</p>
                                    <button type="button" wire:click="$refresh"
                                        class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600">
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

    <!-- Pagination: Hanya muncul jika paginated -->
    @if ($mmpiResults instanceof \Illuminate\Pagination\AbstractPaginator)
        <div class="flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
                {{ $mmpiResults->links() }}
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        Menampilkan
                        <span class="font-medium">{{ $mmpiResults->firstItem() }}</span>
                        sampai
                        <span class="font-medium">{{ $mmpiResults->lastItem() }}</span>
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
    @else
        <div
            class="text-center text-sm text-gray-500 dark:text-gray-400 py-2 border-t border-gray-200 dark:border-gray-700">
            Menampilkan semua {{ $mmpiResults->count() }} data
        </div>
    @endif
</div>
