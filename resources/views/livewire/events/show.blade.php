<div>
    <!-- Main Container -->
    <div class="bg-white dark:bg-gray-900 mx-auto my-8 shadow-lg dark:shadow-gray-800/50 overflow-hidden"
        style="max-width: 1400px;">

        <!-- Header -->
        <div class="border-b-4 border-black dark:border-gray-300 py-3 bg-gray-300 dark:bg-gray-600">
            <h1 class="text-center text-lg font-bold uppercase tracking-wide text-gray-900 dark:text-gray-100">
                Detail Assessment Event
            </h1>
            <p class="text-center text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">
                {{ $event->code }} - {{ $event->name }}
            </p>
        </div>

        <!-- Breadcrumb -->
        <div class="p-4 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-600">
            <nav class="flex text-sm text-gray-600 dark:text-gray-400">
                <a href="{{ route('dashboard-admin') }}" class="hover:text-gray-900 dark:hover:text-gray-200">
                    <i class="fa-solid fa-home mr-1"></i>Dashboard
                </a>
                <span class="mx-2">/</span>
                <a href="{{ route('events.index') }}" class="hover:text-gray-900 dark:hover:text-gray-200">
                    Daftar Event
                </a>
                <span class="mx-2">/</span>
                <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ $event->code }}</span>
            </nav>
        </div>

        <!-- Event Info -->
        <div class="p-6 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left Column - Basic Info -->
                <div class="space-y-4">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Informasi Event</h2>

                    <div class="flex items-start">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-32">Kode Event:</span>
                        <span class="text-sm text-gray-900 dark:text-gray-100 font-semibold">{{ $event->code }}</span>
                    </div>

                    <div class="flex items-start">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-32">Nama Event:</span>
                        <span class="text-sm text-gray-900 dark:text-gray-100">{{ $event->name }}</span>
                    </div>

                    <div class="flex items-start">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-32">Institusi:</span>
                        <a href="{{ route('institutions.show', $event->institution->id) }}"
                            class="text-sm text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 hover:underline">
                            {{ $event->institution->name }}
                        </a>
                    </div>

                    <div class="flex items-start">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-32">Kategori:</span>
                        <span class="text-sm text-gray-900 dark:text-gray-100">{{ $primaryCategory?->name ?? '-' }}</span>
                    </div>

                    <div class="flex items-start">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-32">Tahun:</span>
                        <span class="text-sm text-gray-900 dark:text-gray-100">{{ $event->year }}</span>
                    </div>

                    <div class="flex items-start">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-32">Periode:</span>
                        <span class="text-sm text-gray-900 dark:text-gray-100">
                            {{ $event->start_date->translatedFormat('d F Y') }} - {{ $event->end_date->translatedFormat('d F Y') }}
                        </span>
                    </div>

                    <div class="flex items-start">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-32">Status:</span>
                        <span>
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
                        </span>
                    </div>

                    @if($event->description)
                        <div class="flex items-start">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 w-32">Deskripsi:</span>
                            <span class="text-sm text-gray-900 dark:text-gray-100">{{ $event->description }}</span>
                        </div>
                    @endif
                </div>

                <!-- Right Column - Statistics -->
                <div class="space-y-4">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Statistik</h2>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $event->batches->count() }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Total Batch</div>
                        </div>

                        <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg border border-purple-200 dark:border-purple-800">
                            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $event->participants->count() }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Total Peserta</div>
                        </div>

                        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $event->positionFormations->count() }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Formasi Jabatan</div>
                        </div>

                        <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-lg border border-orange-200 dark:border-orange-800">
                            <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                                {{ $event->start_date->diffInDays($event->end_date) }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Durasi (Hari)</div>
                        </div>
                    </div>

                    @if($event->last_synced_at)
                        <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Terakhir Sinkron:</div>
                            <div class="text-sm text-gray-900 dark:text-gray-100 font-medium">
                                {{ $event->last_synced_at->translatedFormat('d F Y H:i') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Batches Section -->
        @if($event->batches->count() > 0)
            <div class="p-6 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-600">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Daftar Batch</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($event->batches as $batch)
                        <div class="bg-white dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $batch->name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kode: {{ $batch->code }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Quick Actions -->
        <div class="p-6 bg-white dark:bg-gray-900">
            <div class="flex gap-3">
                <a href="{{ route('events.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-700 text-white font-semibold rounded-lg shadow hover:bg-gray-700 dark:hover:bg-gray-600 transition-colors">
                    <i class="fa-solid fa-arrow-left mr-2"></i>
                    Kembali ke Daftar Event
                </a>

                <a href="{{ route('institutions.show', $event->institution->id) }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 dark:bg-blue-700 text-white font-semibold rounded-lg shadow hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors">
                    <i class="fa-solid fa-building mr-2"></i>
                    Lihat Institusi
                </a>
            </div>
        </div>
    </div>
</div>
