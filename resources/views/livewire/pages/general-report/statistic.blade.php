<x-layouts.app title="Kurva Distribusi Frekuensi">
    <div class="max-w-3xl mx-auto mt-10 bg-white p-6 rounded shadow text-gray-900">

        <!-- Dropdown Pilih Atribut -->
        <div class="flex items-center mb-4 gap-3">
            <label class="font-semibold">Pilih Atribut:</label>
            <select id="atributDropdown"
                class="border border-black rounded px-2 py-1 bg-cyan-100 font-mono w-60 text-gray-900">
                <option>1. Aspek Kecerdasan</option>
                <option>2. Aspek Cara Kerja</option>
                <option>3. Aspek Potensi Kerja</option>
                <option>4. Aspek Hubungan Sosial</option>
                <option>5. Aspek Kepribadian</option>
                <option>6. Integritas</option>
                <option>7. Kerjasama</option>
                <option>8. Komunikasi</option>
                <option>9. Orientasi pada Hasil</option>
                <option>10. Pelayanan Publik</option>
                <option>11. Pengambilan Diri dan Orang Lain</option>
                <option>12. Mengelola Perubahan</option>
                <option>13. Pengambilan Keputusan</option>
                <option>14. Perkasa Bangsa</option>
            </select>
        </div>

        <!-- Judul Kurva -->
        <div class="mb-1 text-center font-bold text-lg uppercase">KURVA DISTRIBUSI FREKUENSI</div>
        <div class="mb-1 text-center text-sm font-semibold text-red-800">Kecerdasan</div>

        <!-- Area Chart -->
        <div class="px-4 pb-1">
            <canvas id="frekuensiChart" style="max-height: 270px;"></canvas>
        </div>

        <!-- Tabel Kelas dan Rentang Nilai -->
        <div class="flex justify-end mt-3 text-xs">
            <table class="border border-black text-gray-900">
                <thead>
                    <tr style="background-color: #eee;">
                        <th class="border border-black px-2 py-1">Kelas</th>
                        <th class="border border-black px-2 py-1">Rentang Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border border-black px-2 py-1">I</td>
                        <td class="border border-black px-2 py-1">1.00 - 1.80</td>
                    </tr>
                    <tr>
                        <td class="border border-black px-2 py-1">II</td>
                        <td class="border border-black px-2 py-1">1.80 - 2.60</td>
                    </tr>
                    <tr>
                        <td class="border border-black px-2 py-1">III</td>
                        <td class="border border-black px-2 py-1">2.60 - 3.40</td>
                    </tr>
                    <tr>
                        <td class="border border-black px-2 py-1">IV</td>
                        <td class="border border-black px-2 py-1">3.40 - 4.20</td>
                    </tr>
                    <tr>
                        <td class="border border-black px-2 py-1">V</td>
                        <td class="border border-black px-2 py-1">4.20 - 5.00</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Area Standar & Rata-rata Rating -->
        <div class="flex justify-center gap-4 mt-4">
            <div class="bg-cyan-200 p-4 rounded text-center min-w-[120px]">
                <div class="text-xs font-semibold">Standar Rating</div>
                <div class="text-2xl font-bold text-orange-900 mt-2">3,50</div>
            </div>
            <div class="bg-orange-200 p-4 rounded text-center min-w-[120px]">
                <div class="text-xs font-semibold">Rata-rata Rating</div>
                <div class="text-2xl font-bold text-cyan-900 mt-2">3,20</div>
            </div>
        </div>
    </div>

    <!-- Chart.js CDN & Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('frekuensiChart').getContext('2d');
        const frekuensiChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['I', 'II', 'III', 'IV', 'V'],
                datasets: [{
                    label: 'Kecerdasan',
                    data: [0, 60, 20, 0, 0],
                    borderColor: 'brown',
                    backgroundColor: 'rgba(200,0,0,0.08)',
                    tension: 0.5,
                    fill: false,
                    pointRadius: 4,
                    pointBackgroundColor: 'brown',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 100,
                        ticks: {
                            callback: function(val) {
                                return val + '%';
                            },
                            stepSize: 20
                        }
                    }
                }
            }
        });

        document.getElementById('atributDropdown').addEventListener('change', function() {
            // replace chart data if needed
        });
    </script>
</x-layouts.app>
