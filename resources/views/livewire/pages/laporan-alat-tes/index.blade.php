{{-- Tabel peserta alat tes --}}
<div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
            Data Alat Tes Peserta
        </h2>
    </div>

    <div class="p-6 bg-gray-50 dark:bg-gray-800 border-b-2 border-gray-200 dark:border-gray-600">
        <div class="max-w-6xl mx-auto space-y-4">
            <!-- Event Selector -->
            @livewire('components.event-selector', ['showLabel' => true])

            <!-- Position Selector -->
            @livewire('components.position-selector', ['showLabel' => true])

            <!-- Participant Selector -->
            {{-- @livewire('components.participant-selector', ['showLabel' => true]) --}}
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-xs text-left text-gray-700 dark:text-gray-200">
            <thead class="bg-gray-50 dark:bg-gray-700/60 text-gray-700 dark:text-gray-100">
                <tr>
                    <th class="px-4 py-2 font-semibold">No</th>
                    <th class="px-4 py-2 font-semibold">No Tes</th>
                    <th class="px-4 py-2 font-semibold">Nama</th>
                    <th class="px-4 py-2 font-semibold">E-Mail</th>
                    <th class="px-4 py-2 font-semibold">Jenis Kelamin</th>
                    <th class="px-4 py-2 font-semibold">Pendidikan</th>
                    <th class="px-4 py-2 font-semibold">Jabatan Yang Dituju</th>
                    <th class="px-4 py-2 font-semibold text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                {{-- Contoh 1 baris static, nanti bisa diganti @foreach dari Livewire --}}
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/60">
                    <td class="px-4 py-2">1</td>
                    <td class="px-4 py-2">T-001</td>
                    <td class="px-4 py-2">Ahmad Rizki</td>
                    <td class="px-4 py-2">W9TtA@example.com</td>
                    <td class="px-4 py-2">Laki-laki</td>
                    <td class="px-4 py-2">S1 Psikologi</td>
                    <td class="px-4 py-2">Analis SDM</td>
                    <td class="px-4 py-2 text-center">
                        <a href="{{ route('laporan-alat-tes-detail') }}" class="inline-flex items-center justify-center px-2 py-1 rounded-full
              text-blue-600 hover:text-blue-700 hover:bg-blue-50
              dark:text-blue-400 dark:hover:text-blue-300 dark:hover:bg-blue-900/30" title="Lihat Laporan">
                            <i class="fa-solid fa-eye text-sm"></i>
                        </a>
                    </td>
                </tr>
                {{-- @foreach ($peserta as $index => $row) ... @endforeach --}}
            </tbody>
        </table>
    </div>
</div>