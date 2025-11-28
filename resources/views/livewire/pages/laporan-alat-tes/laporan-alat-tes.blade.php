<div class="max-w-6xl mx-auto py-6">
    {{-- Judul halaman --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Laporan Alat Tes</h1>
        <p class="text-sm text-gray-600 mt-1">
            Ringkasan penggunaan dan hasil alat tes potensi serta kompetensi.
        </p>
    </div>

    {{-- Grid card alat tes --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Card Alat Tes Potensi --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                    Alat Tes Potensi
                </h2>
                <span
                    class="text-xs px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                    Potensi
                </span>
            </div>

            <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                Kumpulan alat tes untuk mengukur kapasitas kognitif, gaya berpikir, dan potensi pengembangan individu.
            </p>

            {{-- Contoh daftar alat tes potensi --}}
            <ul class="text-sm text-gray-700 dark:text-gray-200 space-y-1">
                <li class="flex items-center">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-2"></span>
                    Tes Potensi Akademik (TPA)
                </li>
                <li class="flex items-center">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-2"></span>
                    Tes Logika dan Penalaran
                </li>
                <li class="flex items-center">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-2"></span>
                    Tes Kemampuan Numerik & Verbal
                </li>
            </ul>

            {{-- Aksi / placeholder filter --}}
            <div class="mt-4 flex justify-end">
                <button type="button"
                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                    Lihat Detail Laporan
                </button>
            </div>
        </div>

        {{-- Card Alat Tes Kompetensi --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                    Alat Tes Kompetensi
                </h2>
                <span
                    class="text-xs px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                    Kompetensi
                </span>
            </div>

            <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                Alat tes untuk memetakan perilaku kerja, soft skills, dan kesesuaian kompetensi terhadap role jabatan.
            </p>

            {{-- Contoh daftar alat tes kompetensi --}}
            <ul class="text-sm text-gray-700 dark:text-gray-200 space-y-1">
                <li class="flex items-center">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2"></span>
                    Tes Kompetensi Manajerial
                </li>
                <li class="flex items-center">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2"></span>
                    Tes Kompetensi Fungsional
                </li>
                <li class="flex items-center">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2"></span>
                    Inventori Kepribadian / Perilaku Kerja
                </li>
            </ul>

            <div class="mt-4 flex justify-end">
                <button type="button"
                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1">
                    Lihat Detail Laporan
                </button>
            </div>
        </div>
    </div>
</div>
