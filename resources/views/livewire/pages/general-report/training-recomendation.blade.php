<x-layouts.app title="Halaman Rekomendasi Training">
    <div class="max-w-7xl mx-auto mt-10 bg-white p-6 rounded-lg shadow-lg text-gray-900">
        <!-- SECTION 1 -->
        <table class="w-full border border-black text-gray-900" style="table-layout: fixed;">
            <tr>
                <td class="border border-black font-bold align-top bg-cyan-100 w-56 p-2" rowspan="3">
                    <select id="dropdownKategori1"
                        class="border border-black rounded px-3 py-2 bg-cyan-50 text-gray-900 w-full">
                        <option>Aspek Kecerdasan</option>
                        <option>Aspek Cara Kerja</option>
                        <option>Aspek Potensi Kerja</option>
                        <option>Aspek Hubungan Sosial</option>
                        <option>Aspek Kepribadian</option>
                        <option>Integritas</option>
                        <option>Kerjasama</option>
                        <option>Komunikasi</option>
                        <option>Orientasi pada Hasil</option>
                        <option>Pelayanan Publik</option>
                        <option>Pengambilan Diri dan Orang Lain</option>
                        <option>Mengelola Perubahan</option>
                        <option>Pengambilan Keputusan</option>
                        <option>Perkasa Bangsa</option>
                    </select>
                </td>
                <td class="border border-black font-semibold px-4 py-2 align-top bg-white" colspan="2">JPT Pratama</td>
                <td class="border border-black font-normal px-4 py-2 align-top bg-white text-right" colspan="2">
                    (Rating Rata-rata: 3,56)
                </td>
            </tr>
            <tr>
                <td class="border border-black font-normal px-4 py-2 bg-white" colspan="2">
                    <div class="flex flex-col justify-between items-start">
                        <div class="flex flex-row justify-between w-full">
                            <span>Recommended</span>
                            <span class="ml-4">0</span>
                        </div>
                        <div class="flex flex-row justify-between w-full mt-2">
                            <span>Not Recommended</span>
                            <span class="ml-4">5</span>
                        </div>
                        <div class="flex flex-row justify-between w-full mt-2">
                            <span>&nbsp;</span>
                            <span class="ml-4">100,00%</span>
                        </div>
                    </div>
                </td>
                <td class="border border-black font-normal px-4 py-2 bg-white" colspan="2">
                    &nbsp;
                </td>
            </tr>
        </table>
        <!-- SECTION 2 -->
        <table class="w-full border border-black text-gray-900 mt-4" style="table-layout: fixed;">
            <tr>
                <td class="border border-black font-bold align-top bg-cyan-100 w-56 p-2">
                    <select id="dropdownKategori2"
                        class="border border-black rounded px-3 py-2 bg-cyan-50 text-gray-900 w-full">
                        <option>Aspek Kecerdasan</option>
                        <option>Aspek Cara Kerja</option>
                        <option>Aspek Potensi Kerja</option>
                        <option>Aspek Hubungan Sosial</option>
                        <option>Aspek Kepribadian</option>
                        <option>Integritas</option>
                        <option>Kerjasama</option>
                        <option>Komunikasi</option>
                        <option>Orientasi pada Hasil</option>
                        <option>Pelayanan Publik</option>
                        <option>Pengambilan Diri dan Orang Lain</option>
                        <option>Mengelola Perubahan</option>
                        <option>Pengambilan Keputusan</option>
                        <option>Perkasa Bangsa</option>
                    </select>
                </td>
                <td class="border border-black font-normal px-4 py-2 align-top bg-white w-3/5">
                    Training recommended : <span class="font-bold underline ml-2">Cara Kerja</span>
                </td>
                <td class="border border-black px-4 py-2 text-right bg-yellow-100" style="min-width:180px;">
                    (Std. Rating: 3,50)
                </td>
                <td class="border border-black align-middle px-2 py-2 text-center bg-yellow-300 font-bold"
                    style="width:110px;">
                    Toleransi
                </td>
                <td class="border border-black align-middle px-2 py-2 text-center bg-yellow-300 font-bold"
                    style="width:60px;">
                    10%
                </td>
            </tr>
        </table>
        <!-- TABLE DATA -->
        <div class="overflow-x-auto mt-4">
            <table class="min-w-full border border-black text-sm text-gray-900 bg-white">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border border-black px-4 py-2 text-center">Priority</th>
                        <th class="border border-black px-4 py-2 text-center">NIP</th>
                        <th class="border border-black px-4 py-2 text-center">Nama</th>
                        <th class="border border-black px-4 py-2 text-center">Jabatan</th>
                        <th class="border border-black px-4 py-2 text-center">Rating</th>
                        <th class="border border-black px-4 py-2 text-center">Statement</th>
                        <th class="border border-black px-4 py-2 text-center">Matrix</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border border-black px-4 py-2 text-center">1</td>
                        <td class="border border-black px-4 py-2 text-center">19780923 199803 1 003</td>
                        <td class="border border-black px-4 py-2">Badrus Samsu Darusi, S.Stp., M.Si.</td>
                        <td class="border border-black px-4 py-2">Sekretaris Inspektorat Daerah</td>
                        <td class="border border-black px-4 py-2 text-center">3,83</td>
                        <td class="border border-black px-4 py-2 text-center">Not Recommended</td>
                        <td class="border border-black px-4 py-2 text-center">1</td>
                    </tr>
                    <tr>
                        <td class="border border-black px-4 py-2 text-center">2</td>
                        <td class="border border-black px-4 py-2 text-center">19760526 200312 1 009</td>
                        <td class="border border-black px-4 py-2">Heri Adi Prabowo, S.E.,M.Si.</td>
                        <td class="border border-black px-4 py-2">Pengawas Penyelenggaraan Urusan Pemerintahan Daerah
                            Ahli Madya</td>
                        <td class="border border-black px-4 py-2 text-center">3,33</td>
                        <td class="border border-black px-4 py-2 text-center">Not Recommended</td>
                        <td class="border border-black px-4 py-2 text-center">1</td>
                    </tr>
                    <tr>
                        <td class="border border-black px-4 py-2 text-center">3</td>
                        <td class="border border-black px-4 py-2 text-center">19721204 199703 1 005</td>
                        <td class="border border-black px-4 py-2">Joko Sunaryo, S.E., M.M.</td>
                        <td class="border border-black px-4 py-2">Inspektur Pembantu Khusus Inspektorat Daerah</td>
                        <td class="border border-black px-4 py-2 text-center">2,50</td>
                        <td class="border border-black px-4 py-2 text-center">Not Recommended</td>
                        <td class="border border-black px-4 py-2 text-center">1</td>
                    </tr>
                    <tr>
                        <td class="border border-black px-4 py-2 text-center">4</td>
                        <td class="border border-black px-4 py-2 text-center">19710514 200312 1 004</td>
                        <td class="border border-black px-4 py-2">Pancagus Suharno, S.T.,M.Eng.</td>
                        <td class="border border-black px-4 py-2">Pengawas Penyelenggaraan Urusan Pemerintahan Daerah
                            Ahli Madya</td>
                        <td class="border border-black px-4 py-2 text-center">3,17</td>
                        <td class="border border-black px-4 py-2 text-center">Not Recommended</td>
                        <td class="border border-black px-4 py-2 text-center">1</td>
                    </tr>
                    <tr>
                        <td class="border border-black px-4 py-2 text-center">5</td>
                        <td class="border border-black px-4 py-2 text-center">19730724 2003121 005</td>
                        <td class="border border-black px-4 py-2">Subronto, S.E., M.Si.</td>
                        <td class="border border-black px-4 py-2">Pengawas Penyelenggaraan Urusan Pemerintahan Daerah
                            Ahli Madya</td>
                        <td class="border border-black px-4 py-2 text-center">3,17</td>
                        <td class="border border-black px-4 py-2 text-center">Not Recommended</td>
                        <td class="border border-black px-4 py-2 text-center">1</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
