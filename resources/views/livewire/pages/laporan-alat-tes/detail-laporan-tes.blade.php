<div class="max-w-12xl mx-auto py-6">
    {{-- Judul halaman --}}
    <div class="mb-4 flex items-center justify-start space-x-4">
        <a href="{{ route('laporan-alat-tes') }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md
              border border-gray-300 dark:border-gray-600
              text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800
              hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <i class="fa-solid fa-arrow-left mr-2 text-xs"></i>
            Kembali
        </a>

        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
            Ringkasan Hasil Per Alat Tes Psikologis
        </h1>
    </div>


    {{-- Tabel biodata --}}
    <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
            {{-- Kolom kiri: biodata teks --}}
            <div class="md:col-span-2">
                <table class="w-full text-xs text-gray-700 dark:text-gray-200">
                    <tbody class="align-middle">
                        <tr>
                            <td class="py-1 pr-4 font-semibold">No Tes</td>
                            <td class="py-1">: {{ $noTes ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4 font-semibold w-32">Nama</td>
                            <td class="py-1">: {{ $nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4 font-semibold">Tanggal Ujian</td>
                            <td class="py-1">: {{ $tanggalUjian ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4 font-semibold">Tanggal Lahir</td>
                            <td class="py-1">: {{ $tanggalLahir ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4 font-semibold">Asal Kota</td>
                            <td class="py-1">: {{ $jenisKelamin ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4 font-semibold">Jenis Kelamin</td>
                            <td class="py-1">: {{ $jenisKelamin ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Kolom kanan: foto peserta (merge) --}}
            <div class="flex justify-end">
                <div
                    class="w-32 h-40 rounded-md border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 flex items-center justify-center overflow-hidden">
                    {{-- Nanti ganti src dengan foto asli --}}
                    @if(!empty($fotoUrl))
                        <img src="{{ $fotoUrl }}" alt="Foto Peserta" class="w-full h-full object-cover">
                    @else
                        <span class="text-[10px] text-gray-500 dark:text-gray-300 text-center px-2">
                            Foto peserta
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>


    {{-- Card besar penampung report per alat tes --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        @php
            $tests = [
                'CFIT 3A',
                'CFIT 3B',
                'PAPI Kostick',
                '16PF',
                'Big Five Personality',
                'DASS',
                'DISC 3',
                'MMPI Mini (180)',
                'MMPI-2 (567)',
            ];
        @endphp

        <div class="space-y-5">
            @foreach ($tests as $test)
                <section class="border-b last:border-b-0 border-gray-100 dark:border-gray-700 pb-4 last:pb-0">
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-1">
                        {{ $test }}
                    </h2>
                    <p class="text-xs text-gray-600 dark:text-gray-300">
                        {{-- Tempatkan ringkasan / tabel laporan {{ strtolower($test) }} di sini. --}}
                        (Belum ada data laporan. Nantinya area ini diisi oleh report untuk alat tes ini.)
                    </p>
                </section>
            @endforeach
        </div>
    </div>
</div>