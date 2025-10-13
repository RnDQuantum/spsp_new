<x-layouts.app title="Halaman Rekomendasi Training">
    <div class="max-w-7xl mx-auto mt-10 bg-white p-6 rounded-lg shadow-lg text-gray-900">
        <!-- SECTION 1 -->
        <table class="w-full border border-black text-gray-900 text-sm">
            <tr>
                <td class="border border-black font-bold text-center bg-gray-200 w-16 px-2 py-2" rowspan="3">1</td>
                <td class="border border-black font-semibold px-4 py-2 bg-white" colspan="3">JPT Pratama</td>
                <td class="border border-black font-normal px-4 py-2 bg-white text-right">
                    (Rating Rata-rata: 3,20)
                </td>
            </tr>
            <tr>
                <td class="border border-black px-4 py-2 bg-white">Recommended</td>
                <td class="border border-black px-4 py-2 bg-white text-center w-20">1</td>
                <td class="border border-black px-4 py-2 bg-white text-right w-24">20,00%</td>
                <td class="border border-black px-4 py-2 bg-white" rowspan="2"></td>
            </tr>
            <tr>
                <td class="border border-black px-4 py-2 bg-white">Not Recommended</td>
                <td class="border border-black px-4 py-2 bg-white text-center">4</td>
                <td class="border border-black px-4 py-2 bg-white text-right">80,00%</td>
            </tr>
        </table>

        <!-- SECTION 2 -->
        <table class="w-full border border-black text-gray-900 text-sm mt-4">
            <tr>
                <td class="border border-black font-bold text-center bg-gray-200 w-16 px-2 py-2 align-top">
                    <select id="dropdownKategori1"
                        class="border border-gray-400 rounded px-2 py-1 bg-white text-gray-900 w-full text-xs">
                        <option value="1">1. Aspek Kecerdasan</option>
                        <option value="2">2. Aspek Cara Kerja</option>
                        <option value="3">3. Aspek Potensi Kerja</option>
                        <option value="4">4. Aspek Hubungan Sosial</option>
                        <option value="5">5. Aspek Kepribadian</option>
                        <option value="6">6. Integritas</option>
                        <option value="7">7. Kerjasama</option>
                        <option value="8">8. Komunikasi</option>
                        <option value="9">9. Orientasi pada Hasil</option>
                        <option value="10">10. Pelayanan Publik</option>
                        <option value="11">11. Pengembangan Diri dan Orang Lain</option>
                        <option value="12">12. Mengelola Perubahan</option>
                        <option value="13">13. Pengambilan Keputusan</option>
                        <option value="14">14. Perekat Bangsa</option>
                    </select>
                </td>
                <td class="border border-black font-normal px-4 py-2 align-middle bg-white" colspan="2">
                    Training recommended : <span class="font-bold underline ml-2">Kecerdasan</span>
                </td>
                <td class="border border-black px-4 py-2 text-right bg-yellow-100 align-middle">
                    (Std. Rating: 3,50)
                </td>
                <td
                    class="border border-black align-middle px-3 py-2 text-center bg-yellow-300 font-bold whitespace-nowrap">
                    <i>Toleransi</i>
                </td>
                <td class="border border-black align-middle px-3 py-2 text-center bg-yellow-300 font-bold w-20">
                    <i>10%</i>
                </td>
            </tr>
        </table>

        <!-- TABLE DATA -->
        <div class="overflow-x-auto mt-4">
            <table class="w-full border border-black text-sm text-gray-900 bg-white">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border border-black px-3 py-2 text-center">Priority</th>
                        <th class="border border-black px-3 py-2 text-center">NIP</th>
                        <th class="border border-black px-3 py-2 text-center">Nama</th>
                        <th class="border border-black px-3 py-2 text-center">Jabatan</th>
                        <th class="border border-black px-3 py-2 text-center">Rating</th>
                        <th class="border border-black px-3 py-2 text-center">Statement</th>
                        <th class="border border-black px-3 py-2 text-center">Matrix</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border border-black px-3 py-2 text-center">1</td>
                        <td class="border border-black px-3 py-2 text-center">19780923 199803 1 003</td>
                        <td class="border border-black px-3 py-2">Badrus Samsu Darusi, S.Stp., M.Si.</td>
                        <td class="border border-black px-3 py-2">Sekretaris Inspektorat Daerah</td>
                        <td class="border border-black px-3 py-2 text-center">3,83</td>
                        <td class="border border-black px-3 py-2 text-center">Not Recommended</td>
                        <td class="border border-black px-3 py-2 text-center">1</td>
                    </tr>
                    <tr>
                        <td class="border border-black px-3 py-2 text-center">2</td>
                        <td class="border border-black px-3 py-2 text-center">19760526 200312 1 009</td>
                        <td class="border border-black px-3 py-2">Heri Adi Prabowo, S.E.,M.Si.</td>
                        <td class="border border-black px-3 py-2">Pengawas Penyelenggaraan Urusan Pemerintahan Daerah
                            Ahli Madya</td>
                        <td class="border border-black px-3 py-2 text-center">3,33</td>
                        <td class="border border-black px-3 py-2 text-center">Not Recommended</td>
                        <td class="border border-black px-3 py-2 text-center">1</td>
                    </tr>
                    <tr>
                        <td class="border border-black px-3 py-2 text-center">3</td>
                        <td class="border border-black px-3 py-2 text-center">19721204 199703 1 005</td>
                        <td class="border border-black px-3 py-2">Joko Sunaryo, S.E., M.M.</td>
                        <td class="border border-black px-3 py-2">Inspektur Pembantu Khusus Inspektorat Daerah</td>
                        <td class="border border-black px-3 py-2 text-center">2,50</td>
                        <td class="border border-black px-3 py-2 text-center">Not Recommended</td>
                        <td class="border border-black px-3 py-2 text-center">1</td>
                    </tr>
                    <tr>
                        <td class="border border-black px-3 py-2 text-center">4</td>
                        <td class="border border-black px-3 py-2 text-center">19710514 200312 1 004</td>
                        <td class="border border-black px-3 py-2">Pancagus Suharno, S.T.,M.Eng.</td>
                        <td class="border border-black px-3 py-2">Pengawas Penyelenggaraan Urusan Pemerintahan Daerah
                            Ahli Madya</td>
                        <td class="border border-black px-3 py-2 text-center">3,17</td>
                        <td class="border border-black px-3 py-2 text-center">Not Recommended</td>
                        <td class="border border-black px-3 py-2 text-center">1</td>
                    </tr>
                    <tr>
                        <td class="border border-black px-3 py-2 text-center">5</td>
                        <td class="border border-black px-3 py-2 text-center">19730724 2003121 005</td>
                        <td class="border border-black px-3 py-2">Subronto, S.E., M.Si.</td>
                        <td class="border border-black px-3 py-2">Pengawas Penyelenggaraan Urusan Pemerintahan Daerah
                            Ahli Madya</td>
                        <td class="border border-black px-3 py-2 text-center">3,17</td>
                        <td class="border border-black px-3 py-2 text-center">Not Recommended</td>
                        <td class="border border-black px-3 py-2 text-center">1</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Optional: JavaScript untuk handle perubahan dropdown
        document.getElementById('dropdownKategori1').addEventListener('change', function() {
            const selectedValue = this.value;
            const selectedText = this.options[this.selectedIndex].text;
            console.log('Selected:', selectedValue, selectedText);
            // Anda bisa menambahkan logic untuk update data berdasarkan pilihan
        });
    </script>
</x-layouts.app>
