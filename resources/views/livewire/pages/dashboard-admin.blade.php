<div>

    <!-- Main Container based on Template -->
    <div class="bg-white dark:bg-gray-900 mx-auto my-8 shadow-lg dark:shadow-gray-800/50 overflow-hidden"
        style="max-width: 1400px;">

        <!-- Header - DARK MODE READY -->
        <div class="border-b-4 border-black dark:border-gray-300 py-3 bg-gray-300 dark:bg-gray-600">
            <h1 class="text-center text-lg font-bold uppercase tracking-wide text-gray-900 dark:text-gray-100">
                DASHBOARD KLASTERISASI KLIEN
            </h1>
            <p class="text-center text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">
                Analisis Sebaran Instansi & Perusahaan
            </p>
            <p class="text-center text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">
                Tahun Anggaran 2024
            </p>
        </div>

        <!-- Filters Section - DARK MODE READY -->
        <div class="p-6 bg-gray-50 dark:bg-gray-800 border-b-2 border-gray-200 dark:border-gray-600">
            <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Filter 1 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tahun Data</label>
                    <select
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white p-2 border">
                        <option>2024</option>
                        <option>2023</option>
                        <option>2022</option>
                    </select>
                </div>
                <!-- Filter 2 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori
                        Instansi</label>
                    <select
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white p-2 border">
                        <option>Semua Kategori</option>
                        <option>Kementerian</option>
                        <option>BUMN</option>
                        <option>Swasta</option>
                        <option>Pendidikan</option>
                    </select>
                </div>
                <!-- Filter 3 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status Klien</label>
                    <select
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white p-2 border">
                        <option>Semua Status</option>
                        <option>Aktif</option>
                        <option>Selesai</option>
                        <option>Pending</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Summary Cards Section -->
        <div class="p-6 bg-white dark:bg-gray-900">
            <h2
                class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-6 border-l-4 border-gray-800 dark:border-gray-200 pl-3">
                RINGKASAN EKSEKUTIF
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Card 1 -->
                <div
                    class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg dark:shadow-gray-700/50 border border-gray-200 dark:border-gray-600 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase">Total Klien</p>
                        <h3 class="text-3xl font-bold text-gray-800 dark:text-gray-100 mt-1">1,248</h3>
                    </div>
                    <div class="p-3 bg-gray-100 dark:bg-gray-700 rounded-full">
                        <i class="fa-solid fa-users text-2xl text-gray-600 dark:text-gray-300"></i>
                    </div>
                </div>

                <!-- Card 2 -->
                <div
                    class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg dark:shadow-gray-700/50 border border-gray-200 dark:border-gray-600 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase">Kementerian</p>
                        <h3 class="text-3xl font-bold text-gray-800 dark:text-gray-100 mt-1">42</h3>
                    </div>
                    <div class="p-3 bg-gray-100 dark:bg-gray-700 rounded-full">
                        <i class="fa-solid fa-building-flag text-2xl text-gray-600 dark:text-gray-300"></i>
                    </div>
                </div>

                <!-- Card 3 -->
                <div
                    class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg dark:shadow-gray-700/50 border border-gray-200 dark:border-gray-600 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase">Swasta & BUMN</p>
                        <h3 class="text-3xl font-bold text-gray-800 dark:text-gray-100 mt-1">856</h3>
                    </div>
                    <div class="p-3 bg-gray-100 dark:bg-gray-700 rounded-full">
                        <i class="fa-solid fa-briefcase text-2xl text-gray-600 dark:text-gray-300"></i>
                    </div>
                </div>

                <!-- Card 4 -->
                <div
                    class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg dark:shadow-gray-700/50 border border-gray-200 dark:border-gray-600 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase">Pendidikan</p>
                        <h3 class="text-3xl font-bold text-gray-800 dark:text-gray-100 mt-1">150</h3>
                    </div>
                    <div class="p-3 bg-gray-100 dark:bg-gray-700 rounded-full">
                        <i class="fa-solid fa-graduation-cap text-2xl text-gray-600 dark:text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>


        <!-- Table Section -->
        <div class="p-6 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
            <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">
                Daftar Klien Terbaru
            </h3>
            <div
                class="overflow-x-auto bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-600">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                No.</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Nama Instansi</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Kategori</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Ditambahkan Pada</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Status Klien</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @php
                            $clients = [
                                ['name' => 'Kementerian Keuangan', 'category' => 'Kementerian', 'date' => '28 Nov 2024', 'status' => 'Aktif'],
                                ['name' => 'PT. Telkom Indonesia', 'category' => 'BUMN', 'date' => '25 Nov 2024', 'status' => 'Aktif'],
                                ['name' => 'Universitas Indonesia', 'category' => 'Pendidikan', 'date' => '22 Nov 2024', 'status' => 'Pending'],
                                ['name' => 'PT. Bank Mandiri', 'category' => 'BUMN', 'date' => '20 Nov 2024', 'status' => 'Aktif'],
                                ['name' => 'Kementerian BUMN', 'category' => 'Kementerian', 'date' => '18 Nov 2024', 'status' => 'Selesai'],
                                ['name' => 'PT. Pertamina', 'category' => 'BUMN', 'date' => '15 Nov 2024', 'status' => 'Aktif'],
                                ['name' => 'Universitas Gadjah Mada', 'category' => 'Pendidikan', 'date' => '12 Nov 2024', 'status' => 'Pending'],
                                ['name' => 'Kementerian Pendidikan', 'category' => 'Kementerian', 'date' => '10 Nov 2024', 'status' => 'Aktif'],
                                ['name' => 'PT. Unilever Indonesia', 'category' => 'Swasta', 'date' => '08 Nov 2024', 'status' => 'Aktif'],
                                ['name' => 'Institut Teknologi Bandung', 'category' => 'Pendidikan', 'date' => '05 Nov 2024', 'status' => 'Selesai'],
                            ];
                        @endphp

                        @foreach($clients as $index => $client)
                            <tr>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-gray-100">
                                    {{ $index + 1 }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $client['name'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $client['category'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $client['date'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($client['status'] == 'Aktif')
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Aktif</span>
                                    @elseif($client['status'] == 'Pending')
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Pending</span>
                                    @else
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">Selesai</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Tombol Lihat Daftar Klien -->
            <div class="mt-4 text-center">
                <a href="{{ route('daftar-klien') }}"
                    class="inline-flex items-center px-6 py-3 bg-gray-800 dark:bg-gray-700 text-white font-semibold rounded-lg shadow hover:bg-gray-700 dark:hover:bg-gray-600 transition-colors">
                    <i class="fa-solid fa-list mr-2"></i>
                    Lihat Daftar Klien
                </a>
            </div>
        </div>
    </div>
</div>