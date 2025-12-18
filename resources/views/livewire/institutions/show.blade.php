<div>
    <!-- Main Container -->
    <div class="bg-white dark:bg-gray-900 mx-auto my-8 shadow-lg dark:shadow-gray-800/50 overflow-hidden"
        style="max-width: 1400px;">

        <!-- Header -->
        <div class="border-b-4 border-black dark:border-gray-300 py-3 bg-gray-300 dark:bg-gray-600">
            <h1 class="text-center text-lg font-bold uppercase tracking-wide text-gray-900 dark:text-gray-100">
                Detail Institusi
            </h1>
            <p class="text-center text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">
                {{ $institution->name }}
            </p>
        </div>

        <!-- Breadcrumb -->
        <div class="p-4 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-600">
            <nav class="flex text-sm text-gray-600 dark:text-gray-400">
                <a href="{{ route('dashboard-admin') }}" class="hover:text-gray-900 dark:hover:text-gray-200">
                    <i class="fa-solid fa-home mr-1"></i>Dashboard
                </a>
                <span class="mx-2">/</span>
                <a href="{{ route('daftar-klien') }}" class="hover:text-gray-900 dark:hover:text-gray-200">
                    Daftar Klien
                </a>
                <span class="mx-2">/</span>
                <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ $institution->name }}</span>
            </nav>
        </div>

        <!-- Institution Info -->
        <div class="p-6 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left Column - Basic Info -->
                <div class="space-y-4">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Informasi Institusi</h2>

                    <div class="flex items-start">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-32">Kode:</span>
                        <span class="text-sm text-gray-900 dark:text-gray-100 font-semibold">{{ $institution->code }}</span>
                    </div>

                    <div class="flex items-start">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-32">Nama:</span>
                        <span class="text-sm text-gray-900 dark:text-gray-100">{{ $institution->name }}</span>
                    </div>

                    <div class="flex items-start">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-32">Kategori Utama:</span>
                        <span class="text-sm text-gray-900 dark:text-gray-100">{{ $primaryCategory?->name ?? '-' }}</span>
                    </div>

                    <div class="flex items-start">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-32">Semua Kategori:</span>
                        <span class="text-sm text-gray-900 dark:text-gray-100">
                            {{ $institution->categories->pluck('name')->join(', ') }}
                        </span>
                    </div>

                    <div class="flex items-start">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-32">Terdaftar:</span>
                        <span class="text-sm text-gray-900 dark:text-gray-100">
                            {{ $institution->created_at->translatedFormat('d F Y') }}
                        </span>
                    </div>
                </div>

                <!-- Right Column - Statistics -->
                <div class="space-y-4">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Statistik</h2>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['total_events'] }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Total Event</div>
                        </div>

                        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['active_events'] }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Event Aktif</div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                            <div class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ $stats['completed_events'] }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Event Selesai</div>
                        </div>

                        <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg border border-purple-200 dark:border-purple-800">
                            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['total_participants'] }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Total Peserta</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Events Section -->
        <div class="p-6 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-600">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Daftar Assessment Event</h2>
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filter Tahun</label>
                    <select wire:model.live="yearFilter"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white p-2 border">
                        <option value="all">Semua Tahun</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filter Status</label>
                    <select wire:model.live="statusFilter"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white p-2 border">
                        <option value="all">Semua Status</option>
                        <option value="draft">Draft</option>
                        <option value="ongoing">Aktif</option>
                        <option value="completed">Selesai</option>
                    </select>
                </div>
            </div>

            <!-- Events Table -->
            <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-600">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Kode Event
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Nama Event
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Tahun
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Batch
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Peserta
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Periode
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($events as $event)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $event->code }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $event->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                    {{ $event->year }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                    {{ $event->batches_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                    {{ $event->participants_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $event->start_date->format('d M Y') }} - {{ $event->end_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($event->status === 'ongoing')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Aktif
                                        </span>
                                    @elseif($event->status === 'completed')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                            Selesai
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                            Draft
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                    <a href="{{ route('events.show', $event->code) }}"
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                        title="Lihat Detail Event">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
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

        <!-- Back Button -->
        <div class="p-6 bg-white dark:bg-gray-900">
            <a href="{{ route('daftar-klien') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-700 text-white font-semibold rounded-lg shadow hover:bg-gray-700 dark:hover:bg-gray-600 transition-colors">
                <i class="fa-solid fa-arrow-left mr-2"></i>
                Kembali ke Daftar Klien
            </a>
        </div>
    </div>
</div>
