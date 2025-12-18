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
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $batches->count() }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Total Batch</div>
                        </div>

                        <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg border border-purple-200 dark:border-purple-800">
                            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $event->participants()->count() }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Total Peserta</div>
                        </div>

                        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $positionFormations->count() }}</div>
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

        <!-- Position Formations Section -->
        @if($positionFormations->count() > 0)
            <div class="p-6 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Daftar Formasi Jabatan</h2>
                <div class="space-y-4">
                    @foreach($positionFormations as $formation)
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600 overflow-hidden">
                            <div class="bg-gradient-to-r from-green-600 to-green-700 dark:from-green-700 dark:to-green-800 p-4 text-white cursor-pointer hover:from-green-700 hover:to-green-800 dark:hover:from-green-800 dark:hover:to-green-900 transition-colors"
                                 wire:click="toggleFormation({{ $formation->id }})">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h3 class="font-bold text-lg">{{ $formation->name }}</h3>
                                        <p class="text-sm text-green-100 mt-1">Kode: {{ $formation->code }}</p>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <div class="text-right">
                                            <div class="text-2xl font-bold">{{ $formation->participants_count }}</div>
                                            <div class="text-xs text-green-100">dari {{ $formation->quota }} kuota</div>
                                        </div>
                                        <div class="text-2xl">
                                            <i class="fa-solid {{ $expandedFormation === $formation->id ? 'fa-chevron-up' : 'fa-chevron-down' }}"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($expandedFormation === $formation->id)
                                <div class="p-4 bg-gray-100 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                                    <div class="relative">
                                        <input type="text"
                                               wire:model.live.debounce.300ms="searchFormation"
                                               placeholder="Cari nama atau nomor test..."
                                               class="w-full px-4 py-2 pl-10 pr-4 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:focus:ring-green-600 focus:border-transparent bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                            <i class="fa-solid fa-search text-gray-400 dark:text-gray-500"></i>
                                        </div>
                                        @if($searchFormation)
                                            <button wire:click="$set('searchFormation', '')"
                                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">
                                                <i class="fa-solid fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                @if($formationParticipants && $formationParticipants->total() > 0)
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm">
                                            <thead class="bg-gray-100 dark:bg-gray-700">
                                                <tr>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                        No
                                                    </th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                        Foto
                                                    </th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                        Nama Peserta
                                                    </th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                        No. Test
                                                    </th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                        Batch
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                                @foreach($formationParticipants as $index => $participant)
                                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                                        <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                                                            {{ $formationParticipants->firstItem() + $index }}
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            @if($participant->photo_path)
                                                                <img src="{{ Storage::url($participant->photo_path) }}"
                                                                     alt="{{ $participant->name }}"
                                                                     class="w-10 h-10 rounded-full object-cover">
                                                            @else
                                                                <div class="w-10 h-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                                                    <i class="fa-solid fa-user text-gray-600 dark:text-gray-400 text-xs"></i>
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
                                                            {{ $participant->name }}
                                                        </td>
                                                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                                            {{ $participant->test_number }}
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            @if($participant->batch)
                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                                    <i class="fa-solid fa-layer-group mr-1"></i>{{ $participant->batch->name }}
                                                                </span>
                                                            @else
                                                                <span class="text-gray-400 dark:text-gray-500 text-xs">-</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="p-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                                        {{ $formationParticipants->links(data: ['scrollTo' => false]) }}
                                    </div>
                                @else
                                    <div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        @if($searchFormation)
                                            <i class="fa-solid fa-search mb-3 text-3xl text-gray-400 dark:text-gray-500"></i>
                                            <p class="text-base font-medium mb-1">Tidak ada hasil ditemukan</p>
                                            <p class="text-sm mb-3">Pencarian "{{ $searchFormation }}" tidak menemukan peserta yang cocok</p>
                                            <button wire:click="$set('searchFormation', '')"
                                                    class="inline-flex items-center px-3 py-1.5 text-sm text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300 border border-green-300 dark:border-green-700 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                                                <i class="fa-solid fa-times mr-1"></i> Hapus pencarian
                                            </button>
                                        @else
                                            <i class="fa-solid fa-users mb-3 text-3xl text-gray-400 dark:text-gray-500"></i>
                                            <p>Belum ada peserta terdaftar</p>
                                        @endif
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Batches Section -->
        @if($batches->count() > 0)
            <div class="p-6 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-600">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4">Daftar Batch</h2>
                <div class="space-y-4">
                    @foreach($batches as $batch)
                        <div class="bg-white dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-600 to-blue-700 dark:from-blue-700 dark:to-blue-800 p-4 text-white cursor-pointer hover:from-blue-700 hover:to-blue-800 dark:hover:from-blue-800 dark:hover:to-blue-900 transition-colors"
                                 wire:click="toggleBatch({{ $batch->id }})">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h3 class="font-bold text-lg">{{ $batch->name }}</h3>
                                        <p class="text-sm text-blue-100 mt-1">Kode: {{ $batch->code }}</p>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <div class="text-2xl font-bold">
                                            {{ $batch->participants_count }}
                                            <span class="text-sm text-blue-100">peserta</span>
                                        </div>
                                        <div class="text-2xl">
                                            <i class="fa-solid {{ $expandedBatch === $batch->id ? 'fa-chevron-up' : 'fa-chevron-down' }}"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($expandedBatch === $batch->id)
                                <div class="p-4 bg-gray-100 dark:bg-gray-600 border-b border-gray-200 dark:border-gray-500">
                                    <div class="relative">
                                        <input type="text"
                                               wire:model.live.debounce.300ms="searchBatch"
                                               placeholder="Cari nama atau nomor test..."
                                               class="w-full px-4 py-2 pl-10 pr-4 text-sm border border-gray-300 dark:border-gray-500 rounded-lg focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-600 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                            <i class="fa-solid fa-search text-gray-400 dark:text-gray-500"></i>
                                        </div>
                                        @if($searchBatch)
                                            <button wire:click="$set('searchBatch', '')"
                                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">
                                                <i class="fa-solid fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <div class="p-4 space-y-3">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        @if($batch->location)
                                            <div class="flex items-start gap-2">
                                                <i class="fa-solid fa-map-marker-alt text-gray-400 mt-1"></i>
                                                <div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">Lokasi</div>
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $batch->location }}</div>
                                                </div>
                                            </div>
                                        @endif

                                        @if($batch->start_date)
                                            <div class="flex items-start gap-2">
                                                <i class="fa-solid fa-calendar-alt text-gray-400 mt-1"></i>
                                                <div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">Periode</div>
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $batch->start_date->translatedFormat('d M Y') }}
                                                        @if($batch->end_date)
                                                            - {{ $batch->end_date->translatedFormat('d M Y') }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="flex items-start gap-2">
                                            <i class="fa-solid fa-hashtag text-gray-400 mt-1"></i>
                                            <div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">Nomor Batch</div>
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $batch->batch_number }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    @if($batchParticipants && $batchParticipants->total() > 0)
                                        <div class="pt-3 border-t border-gray-200 dark:border-gray-600">
                                            <div class="overflow-x-auto">
                                                <table class="w-full text-sm">
                                                    <thead class="bg-gray-100 dark:bg-gray-600">
                                                        <tr>
                                                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                                No
                                                            </th>
                                                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                                Nama Peserta
                                                            </th>
                                                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                                No. Test
                                                            </th>
                                                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                                                Formasi Jabatan
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                                                        @foreach($batchParticipants as $index => $participant)
                                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                                                <td class="px-4 py-2 text-gray-900 dark:text-gray-100">
                                                                    {{ $batchParticipants->firstItem() + $index }}
                                                                </td>
                                                                <td class="px-4 py-2 font-medium text-gray-900 dark:text-gray-100">
                                                                    {{ $participant->name }}
                                                                </td>
                                                                <td class="px-4 py-2 text-gray-600 dark:text-gray-400">
                                                                    {{ $participant->test_number }}
                                                                </td>
                                                                <td class="px-4 py-2">
                                                                    @if($participant->positionFormation)
                                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                                            <i class="fa-solid fa-briefcase mr-1"></i>{{ $participant->positionFormation->name }}
                                                                        </span>
                                                                    @else
                                                                        <span class="text-gray-400 dark:text-gray-500 text-xs">-</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="mt-3">
                                                {{ $batchParticipants->links(data: ['scrollTo' => false]) }}
                                            </div>
                                        </div>
                                    @else
                                        <div class="pt-8 pb-8 border-t border-gray-200 dark:border-gray-600 text-center text-sm text-gray-500 dark:text-gray-400">
                                            @if($searchBatch)
                                                <i class="fa-solid fa-search mb-3 text-3xl text-gray-400 dark:text-gray-500"></i>
                                                <p class="text-base font-medium mb-1">Tidak ada hasil ditemukan</p>
                                                <p class="text-sm mb-3">Pencarian "{{ $searchBatch }}" tidak menemukan peserta yang cocok</p>
                                                <button wire:click="$set('searchBatch', '')"
                                                        class="inline-flex items-center px-3 py-1.5 text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 border border-blue-300 dark:border-blue-700 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                                                    <i class="fa-solid fa-times mr-1"></i> Hapus pencarian
                                                </button>
                                            @else
                                                <i class="fa-solid fa-users mb-3 text-3xl text-gray-400 dark:text-gray-500"></i>
                                                <p>Belum ada peserta terdaftar</p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endif
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
