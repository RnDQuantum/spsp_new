<div>
    <!-- Main Container -->
    <div class="bg-white dark:bg-gray-900 mx-auto my-8 shadow-lg dark:shadow-gray-800/50 overflow-hidden"
        style="max-width: 1400px;">

        <!-- Header -->
        <div class="border-b-4 border-black dark:border-gray-300 py-3 bg-gray-300 dark:bg-gray-600">
            <h1 class="text-center text-lg font-bold uppercase tracking-wide text-gray-900 dark:text-gray-100">
                Daftar Assessment Event
            </h1>
            <p class="text-center text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">
                Manajemen Event Penilaian
            </p>
        </div>

        <!-- Statistics Cards -->
        <div class="p-6 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-600">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['total'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Event</div>
                </div>
                <div
                    class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['ongoing'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Aktif</div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                    <div class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ $stats['completed'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Selesai</div>
                </div>
                <div
                    class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg border border-yellow-200 dark:border-yellow-800">
                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['draft'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Draft</div>
                </div>
            </div>
        </div>

        <!-- Filters & Search Section -->
        <div class="p-6 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pencarian</label>
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white p-2 pl-10 border"
                            placeholder="Cari kode atau nama event...">
                        <i
                            class="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <!-- Filter Institusi -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Institusi</label>
                    <select wire:model.live="institutionFilter"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white p-2 border">
                        <option value="all">Semua Institusi</option>
                        @foreach($institutions as $institution)
                            <option value="{{ $institution->id }}">{{ $institution->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Kategori -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
                    <select wire:model.live="categoryFilter"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white p-2 border">
                        <option value="all">Semua Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->code }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Tahun -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tahun</label>
                    <select wire:model.live="yearFilter"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white p-2 border">
                        <option value="all">Semua Tahun</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select wire:model.live="statusFilter"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white p-2 border">
                        <option value="all">Semua Status</option>
                        <option value="draft">Draft</option>
                        <option value="ongoing">Aktif</option>
                        <option value="completed">Selesai</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="p-6 bg-white dark:bg-gray-900">
            <!-- Table Info -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-2">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Menampilkan {{ $events->firstItem() ?? 0 }} - {{ $events->lastItem() ?? 0 }} dari
                    {{ $events->total() }} data
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600 dark:text-gray-400">Tampilkan:</label>
                    <select wire:model.live="perPage"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white p-1 border text-sm">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div
                class="overflow-x-auto bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-600">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                wire:click="sortBy('code')">
                                Kode Event
                                @if($sortField === 'code')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @else
                                    <i class="fa-solid fa-sort ml-1"></i>
                                @endif
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                wire:click="sortBy('name')">
                                Nama Event
                                @if($sortField === 'name')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @else
                                    <i class="fa-solid fa-sort ml-1"></i>
                                @endif
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Institusi
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                wire:click="sortBy('year')">
                                Tahun
                                @if($sortField === 'year')
                                    <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @else
                                    <i class="fa-solid fa-sort ml-1"></i>
                                @endif
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Batch
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Peserta
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Periode
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($events as $event)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $event->code }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $event->name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    <a href="{{ route('institutions.show', $event->institution->id) }}"
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 hover:underline">
                                        {{ $event->institution->name }}
                                    </a>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                    {{ $event->year }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                    {{ $event->batches_count }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                    {{ $event->participants_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $event->start_date->format('d M Y') }} - {{ $event->end_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($event->status === 'ongoing')
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Aktif
                                        </span>
                                    @elseif($event->status === 'completed')
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                            Selesai
                                        </span>
                                    @else
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                            Draft
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                    <a href="{{ route('events.show', $event->code) }}"
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3"
                                        title="Lihat Detail">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Tidak ada event yang ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($events->hasPages())
                <div class="mt-4">
                    {{ $events->links() }}
                </div>
            @endif
        </div>
    </div>
</div>