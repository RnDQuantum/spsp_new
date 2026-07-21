<div>
    <div class="max-w-[1400px] mx-auto p-3 md:p-4 font-sans text-primary-ink dark:text-neutral-100">
        <div class="bg-white dark:bg-[#171412] p-4 md:p-6 rounded-xl border border-warm-border dark:border-[#25211e] shadow-xs">
            
            {{-- Header Editorial Executive Journal --}}
            <div class="mb-4 pb-4 border-b border-warm-border dark:border-[#25211e]">
                <span class="font-mono-data text-amber-700 dark:text-amber-500 font-bold uppercase tracking-widest text-xs block mb-1">
                    ADMINISTRATION / DETAIL ASSESSMENT EVENT
                </span>
                <h1 class="font-display text-xl md:text-2xl font-bold tracking-tight text-primary-ink dark:text-neutral-100">
                    {{ $event->code }} &mdash; {{ $event->name }}
                </h1>
            </div>

            {{-- Breadcrumb --}}
            <div class="mb-6 p-3 bg-warm-ivory dark:bg-[#1f1b18] border border-warm-border dark:border-[#25211e] rounded-lg">
                <nav class="flex items-center text-xs font-medium text-primary-ink/70 dark:text-neutral-400">
                    <a href="{{ route('dashboard-admin') }}" class="hover:text-amber-700 dark:hover:text-amber-400 transition-colors">
                        <i class="fa-solid fa-home mr-1"></i>Dashboard
                    </a>
                    <span class="mx-2 text-primary-ink/40 dark:text-neutral-600">/</span>
                    <a href="{{ route('events.index') }}" class="hover:text-amber-700 dark:hover:text-amber-400 transition-colors">
                        Daftar Event
                    </a>
                    <span class="mx-2 text-primary-ink/40 dark:text-neutral-600">/</span>
                    <span class="text-primary-ink dark:text-neutral-100 font-semibold">{{ $event->code }}</span>
                </nav>
            </div>

            {{-- Event Info & Statistics --}}
            <div class="mb-6 pb-6 border-b border-warm-border dark:border-[#25211e]">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Left Column - Basic Info -->
                    <div class="bg-warm-ivory/60 dark:bg-[#1f1b18]/60 p-5 rounded-xl border border-warm-border dark:border-[#25211e] space-y-3">
                        <h2 class="font-display text-base font-bold text-primary-ink dark:text-neutral-100 pb-2 border-b border-warm-border dark:border-[#25211e]">
                            Informasi Event
                        </h2>

                        <div class="flex items-start text-sm">
                            <span class="text-xs font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400 w-36 shrink-0 mt-0.5">Kode Event:</span>
                            <span class="font-mono-data font-semibold text-primary-ink dark:text-neutral-100">{{ $event->code }}</span>
                        </div>

                        <div class="flex items-start text-sm">
                            <span class="text-xs font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400 w-36 shrink-0 mt-0.5">Nama Event:</span>
                            <span class="font-semibold text-primary-ink dark:text-neutral-100">{{ $event->name }}</span>
                        </div>

                        <div class="flex items-start text-sm">
                            <span class="text-xs font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400 w-36 shrink-0 mt-0.5">Institusi:</span>
                            <a href="{{ route('institutions.show', $event->institution->id) }}"
                                class="font-semibold text-primary-ink hover:text-amber-700 dark:text-neutral-100 dark:hover:text-amber-400 hover:underline transition-colors">
                                {{ $event->institution->name }}
                            </a>
                        </div>

                        <div class="flex items-start text-sm">
                            <span class="text-xs font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400 w-36 shrink-0 mt-0.5">Kategori:</span>
                            <span class="text-primary-ink/90 dark:text-neutral-200">{{ $primaryCategory?->name ?? '-' }}</span>
                        </div>

                        <div class="flex items-start text-sm">
                            <span class="text-xs font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400 w-36 shrink-0 mt-0.5">Tahun:</span>
                            <span class="font-mono-data text-primary-ink/90 dark:text-neutral-200">{{ $event->year }}</span>
                        </div>

                        <div class="flex items-start text-sm">
                            <span class="text-xs font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400 w-36 shrink-0 mt-0.5">Periode:</span>
                            <span class="font-mono-data text-primary-ink/90 dark:text-neutral-200">
                                {{ $event->start_date->translatedFormat('d F Y') }} - {{ $event->end_date->translatedFormat('d F Y') }}
                            </span>
                        </div>

                        <div class="flex items-start text-sm">
                            <span class="text-xs font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400 w-36 shrink-0 mt-0.5">Status:</span>
                            <span>
                                @if($event->status === 'ongoing')
                                    <span class="px-2.5 py-0.5 inline-flex text-xs font-semibold rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800/50">
                                        Aktif
                                    </span>
                                @elseif($event->status === 'completed')
                                    <span class="px-2.5 py-0.5 inline-flex text-xs font-semibold rounded-md bg-warm-border/60 text-primary-ink border border-warm-border dark:bg-[#25211e] dark:text-neutral-300 dark:border-[#25211e]">
                                        Selesai
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 inline-flex text-xs font-semibold rounded-md bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800/50">
                                        Draft
                                    </span>
                                @endif
                            </span>
                        </div>

                        @if($event->description)
                            <div class="flex items-start text-sm pt-2 border-t border-warm-border/60 dark:border-[#25211e]/60">
                                <span class="text-xs font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400 w-36 shrink-0 mt-0.5">Deskripsi:</span>
                                <span class="text-primary-ink/90 dark:text-neutral-200">{{ $event->description }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Right Column - Statistics -->
                    <div>
                        <h2 class="font-display text-base font-bold text-primary-ink dark:text-neutral-100 mb-3">
                            Statistik
                        </h2>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Total Batch -->
                            <div class="bg-warm-ivory dark:bg-[#1f1b18] p-4 rounded-xl border border-warm-border dark:border-[#25211e]">
                                <div class="text-2xl font-bold font-display text-primary-ink dark:text-neutral-100">{{ $batches->count() }}</div>
                                <div class="text-xs font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400 mt-1">Total Batch</div>
                            </div>

                            <!-- Total Peserta -->
                            <div class="bg-warm-ivory dark:bg-[#1f1b18] p-4 rounded-xl border border-warm-border dark:border-[#25211e]">
                                <div class="text-2xl font-bold font-display text-amber-700 dark:text-amber-500">{{ $event->participants()->count() }}</div>
                                <div class="text-xs font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400 mt-1">Total Peserta</div>
                            </div>

                            <!-- Formasi Jabatan -->
                            <div class="bg-warm-ivory dark:bg-[#1f1b18] p-4 rounded-xl border border-warm-border dark:border-[#25211e]">
                                <div class="text-2xl font-bold font-display text-emerald-700 dark:text-emerald-400">{{ $positionFormations->count() }}</div>
                                <div class="text-xs font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400 mt-1">Formasi Jabatan</div>
                            </div>

                            <!-- Durasi -->
                            <div class="bg-warm-ivory dark:bg-[#1f1b18] p-4 rounded-xl border border-warm-border dark:border-[#25211e]">
                                <div class="text-2xl font-bold font-display text-primary-ink/80 dark:text-neutral-200">
                                    {{ $event->start_date->diffInDays($event->end_date) }}
                                </div>
                                <div class="text-xs font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400 mt-1">Durasi (Hari)</div>
                            </div>
                        </div>

                        @if($event->last_synced_at)
                            <div class="mt-4 p-3.5 bg-warm-ivory/80 dark:bg-[#1f1b18]/80 rounded-xl border border-warm-border dark:border-[#25211e] flex items-center justify-between">
                                <span class="text-xs font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400">Terakhir Sinkron:</span>
                                <span class="text-xs font-mono-data font-semibold text-primary-ink dark:text-neutral-200">
                                    {{ $event->last_synced_at->translatedFormat('d F Y H:i') }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Position Formations Section --}}
            @if($positionFormations->count() > 0)
                <div class="mb-6 pb-6 border-b border-warm-border dark:border-[#25211e]">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-1 h-5 bg-amber-600 rounded-full"></span>
                        <h2 class="font-display text-lg font-bold tracking-tight text-primary-ink dark:text-neutral-100">
                            Daftar Formasi Jabatan
                        </h2>
                    </div>

                    <div class="space-y-4">
                        @foreach($positionFormations as $formation)
                            <div class="bg-white dark:bg-[#171412] rounded-xl border border-warm-border dark:border-[#25211e] overflow-hidden">
                                <div class="bg-warm-ivory dark:bg-[#1f1b18] p-4 cursor-pointer hover:bg-warm-border/40 dark:hover:bg-[#25211e]/40 transition-colors border-b border-warm-border dark:border-[#25211e]"
                                     wire:click="toggleFormation({{ $formation->id }})">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <h3 class="font-bold text-base text-primary-ink dark:text-neutral-100">{{ $formation->name }}</h3>
                                            <p class="text-xs font-mono-data text-primary-ink/60 dark:text-neutral-400 mt-0.5">Kode: {{ $formation->code }}</p>
                                        </div>
                                        <div class="flex items-center gap-4">
                                            <div class="text-right">
                                                <div class="text-xl font-bold font-mono-data text-primary-ink dark:text-neutral-100">{{ $formation->participants_count }}</div>
                                                <div class="text-xs text-primary-ink/60 dark:text-neutral-400">dari {{ $formation->quota }} kuota</div>
                                            </div>
                                            <div class="w-8 h-8 rounded-lg bg-white dark:bg-[#171412] border border-warm-border dark:border-[#25211e] flex items-center justify-center text-primary-ink dark:text-neutral-300">
                                                <i class="fa-solid {{ $expandedFormation === $formation->id ? 'fa-chevron-up' : 'fa-chevron-down' }} text-xs"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if($expandedFormation === $formation->id)
                                    <div class="p-4 bg-warm-ivory/40 dark:bg-[#1f1b18]/40 border-b border-warm-border dark:border-[#25211e]">
                                        <div class="relative">
                                            <input type="text"
                                                   wire:model.live.debounce.300ms="searchFormation"
                                                   placeholder="Cari nama atau nomor test..."
                                                   class="w-full px-4 py-2.5 pl-10 pr-4 text-sm border border-warm-border dark:border-[#25211e] rounded-lg bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-100 placeholder-primary-ink/40 dark:placeholder-neutral-500 focus:border-amber-500 focus:ring-amber-500 transition-colors">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                                <i class="fa-solid fa-search text-primary-ink/40 dark:text-neutral-500 text-sm"></i>
                                            </div>
                                            @if($searchFormation)
                                                <button wire:click="$set('searchFormation', '')"
                                                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-primary-ink/40 hover:text-primary-ink dark:text-neutral-500 dark:hover:text-neutral-300">
                                                    <i class="fa-solid fa-times"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    @if($formationParticipants && $formationParticipants->total() > 0)
                                        <div class="overflow-x-auto">
                                            <table class="w-full min-w-full divide-y divide-warm-border dark:divide-[#25211e]">
                                                <thead class="bg-warm-ivory dark:bg-[#1f1b18]">
                                                    <tr>
                                                        <th class="px-4 py-2.5 text-center text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider w-12">
                                                            No
                                                        </th>
                                                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider w-16">
                                                            Foto
                                                        </th>
                                                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider">
                                                            Nama Peserta
                                                        </th>
                                                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider">
                                                            No. Test
                                                        </th>
                                                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider">
                                                            Batch
                                                        </th>
                                                        <th class="px-4 py-2.5 text-center text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider w-24">
                                                            Aksi
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white dark:bg-[#171412] divide-y divide-warm-border dark:divide-[#25211e]">
                                                    @foreach($formationParticipants as $index => $participant)
                                                        <tr class="hover:bg-warm-ivory/50 dark:hover:bg-[#1f1b18] transition-colors">
                                                            <td class="px-4 py-2.5 text-center text-sm font-normal text-primary-ink dark:text-neutral-300">
                                                                {{ $formationParticipants->firstItem() + $index }}
                                                            </td>
                                                            <td class="px-4 py-2.5">
                                                                @if($participant->photo_path)
                                                                    <img src="{{ Storage::url($participant->photo_path) }}"
                                                                         alt="{{ $participant->name }}"
                                                                         class="w-9 h-9 rounded-full object-cover border border-warm-border dark:border-[#25211e]">
                                                                @else
                                                                    <div class="w-9 h-9 rounded-full bg-warm-ivory dark:bg-[#1f1b18] border border-warm-border dark:border-[#25211e] flex items-center justify-center">
                                                                        <i class="fa-solid fa-user text-primary-ink/40 dark:text-neutral-500 text-xs"></i>
                                                                    </div>
                                                                @endif
                                                            </td>
                                                            <td class="px-4 py-2.5 text-sm font-semibold text-primary-ink dark:text-neutral-100">
                                                                {{ $participant->name }}
                                                            </td>
                                                            <td class="px-4 py-2.5 text-sm font-mono-data text-primary-ink/75 dark:text-neutral-300">
                                                                {{ $participant->test_number }}
                                                            </td>
                                                            <td class="px-4 py-2.5 text-sm">
                                                                @if($participant->batch)
                                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800/50">
                                                                        <i class="fa-solid fa-layer-group mr-1.5 text-xs"></i>{{ $participant->batch->name }}
                                                                    </span>
                                                                @else
                                                                    <span class="text-primary-ink/40 dark:text-neutral-500 text-xs">-</span>
                                                                @endif
                                                            </td>
                                                            <td class="px-4 py-2.5 text-center">
                                                                <a href="{{ route('participant_detail', ['eventCode' => $event->code, 'testNumber' => $participant->test_number]) }}"
                                                                   class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold text-white bg-[#171412] hover:bg-black dark:bg-[#25211e] dark:hover:bg-[#1f1b18] rounded-lg transition-colors shadow-xs">
                                                                    <i class="fa-solid fa-eye mr-1.5 text-amber-500"></i>
                                                                    Detail
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="p-4 bg-warm-ivory/40 dark:bg-[#1f1b18]/40 border-t border-warm-border dark:border-[#25211e]">
                                            {{ $formationParticipants->links(data: ['scrollTo' => false]) }}
                                        </div>
                                    @else
                                        <div class="p-8 text-center text-sm text-primary-ink/60 dark:text-neutral-400">
                                            @if($searchFormation)
                                                <i class="fa-solid fa-search mb-3 text-2xl text-primary-ink/30 dark:text-neutral-500"></i>
                                                <p class="text-sm font-semibold mb-1">Tidak ada hasil ditemukan</p>
                                                <p class="text-xs mb-3">Pencarian "{{ $searchFormation }}" tidak menemukan peserta yang cocok</p>
                                                <button wire:click="$set('searchFormation', '')"
                                                        class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-amber-700 dark:text-amber-400 border border-amber-300 dark:border-amber-800 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-950/20 transition-colors">
                                                    <i class="fa-solid fa-times mr-1"></i> Hapus pencarian
                                                </button>
                                            @else
                                                <i class="fa-solid fa-users mb-3 text-2xl text-primary-ink/30 dark:text-neutral-500"></i>
                                                <p class="text-xs">Belum ada peserta terdaftar</p>
                                            @endif
                                        </div>
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Batches Section --}}
            @if($batches->count() > 0)
                <div class="mb-6 pb-6 border-b border-warm-border dark:border-[#25211e]">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-1 h-5 bg-amber-600 rounded-full"></span>
                        <h2 class="font-display text-lg font-bold tracking-tight text-primary-ink dark:text-neutral-100">
                            Daftar Batch
                        </h2>
                    </div>

                    <div class="space-y-4">
                        @foreach($batches as $batch)
                            <div class="bg-white dark:bg-[#171412] rounded-xl border border-warm-border dark:border-[#25211e] overflow-hidden">
                                <div class="bg-warm-ivory dark:bg-[#1f1b18] p-4 cursor-pointer hover:bg-warm-border/40 dark:hover:bg-[#25211e]/40 transition-colors border-b border-warm-border dark:border-[#25211e]"
                                     wire:click="toggleBatch({{ $batch->id }})">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <h3 class="font-bold text-base text-primary-ink dark:text-neutral-100">{{ $batch->name }}</h3>
                                            <p class="text-xs font-mono-data text-primary-ink/60 dark:text-neutral-400 mt-0.5">Kode: {{ $batch->code }}</p>
                                        </div>
                                        <div class="flex items-center gap-4">
                                            <div class="text-right">
                                                <div class="text-xl font-bold font-mono-data text-primary-ink dark:text-neutral-100">
                                                    {{ $batch->participants_count }}
                                                </div>
                                                <div class="text-xs text-primary-ink/60 dark:text-neutral-400">peserta</div>
                                            </div>
                                            <div class="w-8 h-8 rounded-lg bg-white dark:bg-[#171412] border border-warm-border dark:border-[#25211e] flex items-center justify-center text-primary-ink dark:text-neutral-300">
                                                <i class="fa-solid {{ $expandedBatch === $batch->id ? 'fa-chevron-up' : 'fa-chevron-down' }} text-xs"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if($expandedBatch === $batch->id)
                                    <div class="p-4 bg-warm-ivory/40 dark:bg-[#1f1b18]/40 border-b border-warm-border dark:border-[#25211e]">
                                        <div class="relative mb-4">
                                            <input type="text"
                                                   wire:model.live.debounce.300ms="searchBatch"
                                                   placeholder="Cari nama atau nomor test..."
                                                   class="w-full px-4 py-2.5 pl-10 pr-4 text-sm border border-warm-border dark:border-[#25211e] rounded-lg bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-100 placeholder-primary-ink/40 dark:placeholder-neutral-500 focus:border-amber-500 focus:ring-amber-500 transition-colors">
                                            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                                <i class="fa-solid fa-search text-primary-ink/40 dark:text-neutral-500 text-sm"></i>
                                            </div>
                                            @if($searchBatch)
                                                <button wire:click="$set('searchBatch', '')"
                                                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-primary-ink/40 hover:text-primary-ink dark:text-neutral-500 dark:hover:text-neutral-300">
                                                    <i class="fa-solid fa-times"></i>
                                                </button>
                                            @endif
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-3 bg-white dark:bg-[#171412] rounded-lg border border-warm-border dark:border-[#25211e] mb-4">
                                            @if($batch->location)
                                                <div class="flex items-start gap-2">
                                                    <i class="fa-solid fa-map-marker-alt text-amber-600 mt-0.5 text-xs"></i>
                                                    <div>
                                                        <div class="text-[11px] font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400">Lokasi</div>
                                                        <div class="text-xs font-semibold text-primary-ink dark:text-neutral-100">{{ $batch->location }}</div>
                                                    </div>
                                                </div>
                                            @endif

                                            @if($batch->start_date)
                                                <div class="flex items-start gap-2">
                                                    <i class="fa-solid fa-calendar-alt text-amber-600 mt-0.5 text-xs"></i>
                                                    <div>
                                                        <div class="text-[11px] font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400">Periode</div>
                                                        <div class="text-xs font-mono-data font-semibold text-primary-ink dark:text-neutral-100">
                                                            {{ $batch->start_date->translatedFormat('d M Y') }}
                                                            @if($batch->end_date)
                                                                - {{ $batch->end_date->translatedFormat('d M Y') }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="flex items-start gap-2">
                                                <i class="fa-solid fa-hashtag text-amber-600 mt-0.5 text-xs"></i>
                                                <div>
                                                    <div class="text-[11px] font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400">Nomor Batch</div>
                                                    <div class="text-xs font-mono-data font-semibold text-primary-ink dark:text-neutral-100">{{ $batch->batch_number }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        @if($batchParticipants && $batchParticipants->total() > 0)
                                            <div class="overflow-x-auto rounded-lg border border-warm-border dark:border-[#25211e]">
                                                <table class="w-full min-w-full divide-y divide-warm-border dark:divide-[#25211e]">
                                                    <thead class="bg-warm-ivory dark:bg-[#1f1b18]">
                                                        <tr>
                                                            <th class="px-4 py-2.5 text-center text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider w-12">
                                                                No
                                                            </th>
                                                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider">
                                                                Nama Peserta
                                                            </th>
                                                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider">
                                                                No. Test
                                                            </th>
                                                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider">
                                                                Formasi Jabatan
                                                            </th>
                                                            <th class="px-4 py-2.5 text-center text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider w-24">
                                                                Aksi
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white dark:bg-[#171412] divide-y divide-warm-border dark:divide-[#25211e]">
                                                        @foreach($batchParticipants as $index => $participant)
                                                            <tr class="hover:bg-warm-ivory/50 dark:hover:bg-[#1f1b18] transition-colors">
                                                                <td class="px-4 py-2.5 text-center text-sm font-normal text-primary-ink dark:text-neutral-300">
                                                                    {{ $batchParticipants->firstItem() + $index }}
                                                                </td>
                                                                <td class="px-4 py-2.5 text-sm font-semibold text-primary-ink dark:text-neutral-100">
                                                                    {{ $participant->name }}
                                                                </td>
                                                                <td class="px-4 py-2.5 text-sm font-mono-data text-primary-ink/75 dark:text-neutral-300">
                                                                    {{ $participant->test_number }}
                                                                </td>
                                                                <td class="px-4 py-2.5 text-sm">
                                                                    @if($participant->positionFormation)
                                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800/50">
                                                                            <i class="fa-solid fa-briefcase mr-1.5 text-xs"></i>{{ $participant->positionFormation->name }}
                                                                        </span>
                                                                    @else
                                                                        <span class="text-primary-ink/40 dark:text-neutral-500 text-xs">-</span>
                                                                    @endif
                                                                </td>
                                                                <td class="px-4 py-2.5 text-center">
                                                                    <a href="{{ route('participant_detail', ['eventCode' => $event->code, 'testNumber' => $participant->test_number]) }}"
                                                                       class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold text-white bg-[#171412] hover:bg-black dark:bg-[#25211e] dark:hover:bg-[#1f1b18] rounded-lg transition-colors shadow-xs">
                                                                        <i class="fa-solid fa-eye mr-1.5 text-amber-500"></i>
                                                                        Detail
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="mt-3">
                                                {{ $batchParticipants->links(data: ['scrollTo' => false]) }}
                                            </div>
                                        @else
                                            <div class="py-8 text-center text-sm text-primary-ink/60 dark:text-neutral-400">
                                                @if($searchBatch)
                                                    <i class="fa-solid fa-search mb-3 text-2xl text-primary-ink/30 dark:text-neutral-500"></i>
                                                    <p class="text-sm font-semibold mb-1">Tidak ada hasil ditemukan</p>
                                                    <p class="text-xs mb-3">Pencarian "{{ $searchBatch }}" tidak menemukan peserta yang cocok</p>
                                                    <button wire:click="$set('searchBatch', '')"
                                                            class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-amber-700 dark:text-amber-400 border border-amber-300 dark:border-amber-800 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-950/20 transition-colors">
                                                        <i class="fa-solid fa-times mr-1"></i> Hapus pencarian
                                                    </button>
                                                @else
                                                    <i class="fa-solid fa-users mb-3 text-2xl text-primary-ink/30 dark:text-neutral-500"></i>
                                                    <p class="text-xs">Belum ada peserta terdaftar</p>
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

            {{-- Quick Actions --}}
            <div class="pt-4 border-t border-warm-border dark:border-[#25211e] flex flex-wrap gap-3">
                <a href="{{ route('events.index') }}"
                    class="inline-flex items-center px-4 py-2.5 bg-[#171412] dark:bg-[#25211e] text-white hover:bg-black dark:hover:bg-[#1f1b18] font-semibold text-sm rounded-lg border border-[#171412] dark:border-[#25211e] transition-colors shadow-xs">
                    <i class="fa-solid fa-arrow-left mr-2"></i>
                    Kembali ke Daftar Event
                </a>

                <a href="{{ route('institutions.show', $event->institution->id) }}"
                    class="inline-flex items-center px-4 py-2.5 bg-amber-700 dark:bg-amber-600 text-white hover:bg-amber-800 dark:hover:bg-amber-500 font-semibold text-sm rounded-lg transition-colors shadow-xs">
                    <i class="fa-solid fa-building mr-2"></i>
                    Lihat Institusi
                </a>
            </div>
        </div>
    </div>
</div>
