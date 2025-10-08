<x-layouts.app title="Shortlist Peserta">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Header Table -->
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Data Peserta Assessment</h2>
            <!-- Filter Dropdown Kode Proyek -->
            <div x-data="{
                open: false,
                search: '',
                selected: null,
                projects: [
                    { code: 'PRJ-001', name: 'Proyek A' },
                    { code: 'PRJ-002', name: 'Proyek B' },
                    { code: 'PRJ-003', name: 'Proyek C' },
                    { code: 'PRJ-004', name: 'Proyek D' },
                    { code: 'PRJ-005', name: 'Proyek E' }
                ],
                get filtered() {
                    if (this.search === '') return this.projects
                    return this.projects.filter(
                        p => p.code.toLowerCase().includes(this.search.toLowerCase()) ||
                        p.name.toLowerCase().includes(this.search.toLowerCase())
                    )
                }
            }" class="relative w-60">
                <button @click="open = !open"
                    class="w-full rounded border border-gray-300 px-4 py-2 text-left text-gray-700 bg-white shadow-sm focus:outline-none">
                    <span x-text="selected ? selected.code + ' - ' + selected.name : 'Pilih Kode Proyek'"></span>
                    <svg class="inline-block float-right w-4 h-4 mt-1" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open" @click.away="open = false"
                    class="absolute mt-1 w-full bg-white border border-gray-300 z-10 rounded shadow-lg">
                    <input x-model="search"
                        class="w-full px-3 py-2 border-b border-gray-700 text-gray-800 focus:outline-none"
                        placeholder="Cari kode proyek..." />

                    <ul class="max-h-48 overflow-y-auto">
                        <template x-for="project in filtered" :key="project.code">
                            <li @click="selected = project; open = false; search = ''"
                                class="cursor-pointer px-4 py-2 hover:bg-gray-100 text-gray-900"
                                x-text="project.code + ' - ' + project.name"></li>
                        </template>
                        <li x-show="filtered.length === 0" class="px-4 py-2 text-gray-400">Tidak ditemukan</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No.
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIP
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis
                            Kelamin</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Jabatan Saat Ini</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Pangkat/Golongan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl
                            Test</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Matrix</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">1</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">198501012010011001</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Ahmad Suryanto</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Laki-laki</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Kepala Bidang</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Pembina / IV-a</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">15 Jan 2025</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">1</td>
                    </tr>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">198703152012012002</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Siti Nurhaliza</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Perempuan</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Kasubag Umum</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Penata / III-c</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">15 Jan 2025</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">1</td>
                    </tr>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">3</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">199001202015011003</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Budi Santoso</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Laki-laki</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Staf Ahli</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Penata Muda / III-a</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">16 Jan 2025</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">1</td>
                    </tr>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">4</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">198805252013012004</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Rina Wijaya</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Perempuan</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Kepala Seksi</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Penata Tk. I / III-d</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">16 Jan 2025</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">1</td>
                    </tr>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">5</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">199203102017011005</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Dedi Kurniawan</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Laki-laki</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Analis Kebijakan</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Penata Muda Tk. I / III-b</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">17 Jan 2025</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">1</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
