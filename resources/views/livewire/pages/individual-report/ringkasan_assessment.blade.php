<x-layouts.app title="Ringkasan Hasil Assessment">
    <div class="bg-white mx-auto my-8 shadow overflow-hidden" style="max-width: 1200px;">
        <!-- Header -->
        <div class="border-b-4 border-black py-4 bg-white">
            <h1 class="text-center text-2xl font-bold uppercase tracking-wide text-black">
                RINGKASAN HASIL ASSESSMENT
            </h1>
        </div>

        <!-- Info Section -->
        <div class="p-6 bg-gray-100">
            <table class="w-full text-sm text-gray-900">
                <tr>
                    <td class="py-2 font-semibold" style="width: 150px;">Nomor Tes</td>
                    <td class="py-2" style="width: 20px;">:</td>
                    <td class="py-2">1</td>
                </tr>
                <tr>
                    <td class="py-2 font-semibold">NIP</td>
                    <td class="py-2">:</td>
                    <td class="py-2">19780923 199803 1 003</td>
                </tr>
                <tr>
                    <td class="py-2 font-semibold">Nama</td>
                    <td class="py-2">:</td>
                    <td class="py-2">Badrus Samsu Darusi, S.Stp., M.Si.</td>
                </tr>
            </table>

            <div class="mt-4"></div>

            <table class="w-full text-sm text-gray-900">
                <tr>
                    <td class="py-2 font-semibold" style="width: 150px;">Jabatan Saat Ini</td>
                    <td class="py-2" style="width: 20px;">:</td>
                    <td class="py-2">Sekretaris Inspektorat Daerah</td>
                </tr>
            </table>

            <div class="mt-4"></div>

            <table class="w-full text-sm text-gray-900">
                <tr>
                    <td class="py-2 font-semibold" style="width: 150px;">Standar Penilaian</td>
                    <td class="py-2" style="width: 20px;">:</td>
                    <td class="py-2">JPT Pratama</td>
                </tr>
                <tr>
                    <td class="py-2 font-semibold">Tanggal Tes</td>
                    <td class="py-2">:</td>
                    <td class="py-2">15 Maret 2023</td>
                </tr>
            </table>
        </div>

        <!-- Table Section -->
        <div class="px-6 pb-6 bg-white overflow-x-auto">
            <table class="min-w-full border-2 border-black text-sm text-gray-900 mt-6">
                <thead>
                    <tr class="bg-cyan-200">
                        <th class="border-2 border-black px-3 py-3 font-bold text-center" style="width: 40px;">NO</th>
                        <th class="border-2 border-black px-3 py-3 font-bold text-center" style="width: 180px;">
                            ASPEK PENILAIAN
                        </th>
                        <th class="border-2 border-black px-3 py-3 font-bold text-center" style="width: 100px;">
                            Standar Skor
                        </th>
                        <th class="border-2 border-black px-3 py-3 font-bold text-center" style="width: 100px;">
                            Skor Individu
                        </th>
                        <th class="border-2 border-black px-3 py-3 font-bold text-center" style="width: 100px;">
                            Bobot Penilaian
                        </th>
                        <th class="border-2 border-black px-3 py-3 font-bold text-center" style="width: 100px;">
                            Standar Skor Akhir
                        </th>
                        <th class="border-2 border-black px-3 py-3 font-bold text-center" style="width: 100px;">
                            Skor Individu Akhir
                        </th>
                        <th class="border-2 border-black px-3 py-3 font-bold text-center" style="width: 80px;">
                            GAP
                        </th>
                        <th class="border-2 border-black px-3 py-3 font-bold text-center" style="width: 180px;">
                            Kesimpulan
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border-2 border-black px-3 py-3 text-center bg-white">1</td>
                        <td class="border-2 border-black px-3 py-3 bg-white">Aspek Psikologi</td>
                        <td class="border-2 border-black px-3 py-3 text-center bg-white">328,00</td>
                        <td class="border-2 border-black px-3 py-3 text-center bg-white">333,67</td>
                        <td class="border-2 border-black px-3 py-3 text-center bg-white">30%</td>
                        <td class="border-2 border-black px-3 py-3 text-center bg-white">98,40</td>
                        <td class="border-2 border-black px-3 py-3 text-center bg-white">100,10</td>
                        <td class="border-2 border-black px-3 py-3 text-center bg-white">1,70</td>
                        <td class="border-2 border-black px-3 py-3 text-center bg-green-500 font-bold">Di Atas Standar
                        </td>
                    </tr>
                    <tr>
                        <td class="border-2 border-black px-3 py-3 text-center bg-white">2</td>
                        <td class="border-2 border-black px-3 py-3 bg-white">Aspek Kompetensi</td>
                        <td class="border-2 border-black px-3 py-3 text-center bg-white">400,00</td>
                        <td class="border-2 border-black px-3 py-3 text-center bg-white">356,00</td>
                        <td class="border-2 border-black px-3 py-3 text-center bg-white">70%</td>
                        <td class="border-2 border-black px-3 py-3 text-center bg-white">280,00</td>
                        <td class="border-2 border-black px-3 py-3 text-center bg-white">249,20</td>
                        <td class="border-2 border-black px-3 py-3 text-center bg-white">-30,80</td>
                        <td class="border-2 border-black px-3 py-3 text-center bg-red-600 font-bold text-white">Belum
                            Kompeten</td>
                    </tr>
                    <tr class="bg-black text-white">
                        <td class="border-2 border-black px-3 py-3 text-center" colspan="2"><strong>TOTAL
                                SKOR</strong></td>
                        <td class="border-2 border-black px-3 py-3 text-center"><strong>728,00</strong></td>
                        <td class="border-2 border-black px-3 py-3 text-center"><strong>689,67</strong></td>
                        <td class="border-2 border-black px-3 py-3 text-center"><strong>100%</strong></td>
                        <td class="border-2 border-black px-3 py-3 text-center"><strong>378,40</strong></td>
                        <td class="border-2 border-black px-3 py-3 text-center"><strong>349,30</strong></td>
                        <td class="border-2 border-black px-3 py-3 text-center"><strong>-29,10</strong></td>
                        <td class="border-2 border-black px-3 py-3 text-center bg-yellow-400 font-bold text-black">
                            Memenuhi Standar</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Conclusion Section -->
        <div class="px-6 pb-6 bg-white">
            <table class="min-w-full border-2 border-black">
                <tr>
                    <td class="border-2 border-black px-4 py-4 font-bold text-center text-gray-900 bg-white"
                        style="width: 200px;">
                        KESIMPULAN :
                    </td>
                    <td
                        class="border-2 border-black px-4 py-4 text-center bg-yellow-400 text-gray-900 font-bold text-lg">
                        Potensi Dengan Catatan
                    </td>
                </tr>
            </table>
        </div>
    </div>
</x-layouts.app>
