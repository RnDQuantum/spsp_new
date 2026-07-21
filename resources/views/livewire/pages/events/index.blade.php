<div>
    <div class="max-w-[1400px] mx-auto p-3 md:p-4 font-sans text-primary-ink dark:text-neutral-100">
        <div class="bg-white dark:bg-[#171412] p-4 md:p-6 rounded-xl border border-warm-border dark:border-[#25211e] shadow-xs">
            
            {{-- Header Editorial Executive Journal --}}
            <div class="mb-6 pb-4 border-b border-warm-border dark:border-[#25211e]">
                <span class="font-mono-data text-amber-700 dark:text-amber-500 font-bold uppercase tracking-widest text-xs block mb-1">
                    ADMINISTRATION / DAFTAR ASSESSMENT EVENT
                </span>
                <h1 class="font-display text-xl md:text-2xl font-bold tracking-tight text-primary-ink dark:text-neutral-100">
                    Manajemen Event Penilaian
                </h1>
            </div>

            {{-- Statistics Cards --}}
            <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-warm-ivory dark:bg-[#1f1b18] p-5 rounded-xl border border-warm-border dark:border-[#25211e] flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400">Total Event</p>
                        <h3 class="text-2xl md:text-3xl font-bold font-display text-primary-ink dark:text-neutral-100 mt-1">
                            {{ $stats['total'] }}
                        </h3>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-white dark:bg-[#171412] border border-warm-border dark:border-[#25211e] flex items-center justify-center text-amber-700 dark:text-amber-500 shadow-xs">
                        <i class="fa-solid fa-calendar-alt text-lg"></i>
                    </div>
                </div>

                <div class="bg-warm-ivory dark:bg-[#1f1b18] p-5 rounded-xl border border-warm-border dark:border-[#25211e] flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400">Aktif</p>
                        <h3 class="text-2xl md:text-3xl font-bold font-display text-emerald-700 dark:text-emerald-400 mt-1">
                            {{ $stats['ongoing'] }}
                        </h3>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-white dark:bg-[#171412] border border-warm-border dark:border-[#25211e] flex items-center justify-center text-emerald-600 dark:text-emerald-400 shadow-xs">
                        <i class="fa-solid fa-circle-check text-lg"></i>
                    </div>
                </div>

                <div class="bg-warm-ivory dark:bg-[#1f1b18] p-5 rounded-xl border border-warm-border dark:border-[#25211e] flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400">Selesai</p>
                        <h3 class="text-2xl md:text-3xl font-bold font-display text-primary-ink/75 dark:text-neutral-300 mt-1">
                            {{ $stats['completed'] }}
                        </h3>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-white dark:bg-[#171412] border border-warm-border dark:border-[#25211e] flex items-center justify-center text-primary-ink/60 dark:text-neutral-400 shadow-xs">
                        <i class="fa-solid fa-flag-checkered text-lg"></i>
                    </div>
                </div>

                <div class="bg-warm-ivory dark:bg-[#1f1b18] p-5 rounded-xl border border-warm-border dark:border-[#25211e] flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400">Draft</p>
                        <h3 class="text-2xl md:text-3xl font-bold font-display text-amber-700 dark:text-amber-500 mt-1">
                            {{ $stats['draft'] }}
                        </h3>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-white dark:bg-[#171412] border border-warm-border dark:border-[#25211e] flex items-center justify-center text-amber-700 dark:text-amber-500 shadow-xs">
                        <i class="fa-solid fa-file-pen text-lg"></i>
                    </div>
                </div>
            </div>

            {{-- Filters & Search Section --}}
            <div class="mb-6 bg-warm-ivory dark:bg-[#1f1b18] p-4 rounded-xl border border-warm-border dark:border-[#25211e]">
                <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    <!-- Search -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-primary-ink/70 dark:text-neutral-400 mb-1.5">
                            Pencarian
                        </label>
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="search"
                                class="w-full rounded-lg border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-200 text-sm p-2.5 pl-10 focus:border-amber-500 focus:ring-amber-500 transition-colors"
                                placeholder="Cari kode atau nama event...">
                            <i class="fa-solid fa-search absolute left-3.5 top-1/2 transform -translate-y-1/2 text-primary-ink/40 dark:text-neutral-500 text-sm"></i>
                        </div>
                    </div>

                    <!-- Filter Institusi -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-primary-ink/70 dark:text-neutral-400 mb-1.5">
                            Institusi
                        </label>
                        <select wire:model.live="institutionFilter"
                            class="w-full rounded-lg border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-200 text-sm p-2.5 focus:border-amber-500 focus:ring-amber-500 transition-colors">
                            <option value="all">Semua Institusi</option>
                            @foreach($institutions as $institution)
                                <option value="{{ $institution->id }}">{{ $institution->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Kategori -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-primary-ink/70 dark:text-neutral-400 mb-1.5">
                            Kategori
                        </label>
                        <select wire:model.live="categoryFilter"
                            class="w-full rounded-lg border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-200 text-sm p-2.5 focus:border-amber-500 focus:ring-amber-500 transition-colors">
                            <option value="all">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->code }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Tahun -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-primary-ink/70 dark:text-neutral-400 mb-1.5">
                            Tahun
                        </label>
                        <select wire:model.live="yearFilter"
                            class="w-full rounded-lg border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-200 text-sm p-2.5 focus:border-amber-500 focus:ring-amber-500 transition-colors">
                            <option value="all">Semua Tahun</option>
                            @foreach($years as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Status -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-primary-ink/70 dark:text-neutral-400 mb-1.5">
                            Status
                        </label>
                        <select wire:model.live="statusFilter"
                            class="w-full rounded-lg border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-200 text-sm p-2.5 focus:border-amber-500 focus:ring-amber-500 transition-colors">
                            <option value="all">Semua Status</option>
                            <option value="draft">Draft</option>
                            <option value="ongoing">Aktif</option>
                            <option value="completed">Selesai</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Table Section --}}
            <div>
                <!-- Table Info -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-2">
                    <div class="text-xs font-medium text-primary-ink/70 dark:text-neutral-400">
                        Menampilkan <span class="font-semibold text-primary-ink dark:text-neutral-200">{{ $events->firstItem() ?? 0 }}</span> - <span class="font-semibold text-primary-ink dark:text-neutral-200">{{ $events->lastItem() ?? 0 }}</span> dari <span class="font-semibold text-primary-ink dark:text-neutral-200">{{ $events->total() }}</span> data
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-medium text-primary-ink/70 dark:text-neutral-400">Tampilkan:</label>
                        <select wire:model.live="perPage"
                            class="rounded-lg border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-200 p-1.5 text-xs focus:border-amber-500 focus:ring-amber-500">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto bg-white dark:bg-[#171412] rounded-xl border border-warm-border dark:border-[#25211e]">
                    <table class="min-w-full divide-y divide-warm-border dark:divide-[#25211e]">
                        <thead class="bg-warm-ivory dark:bg-[#1f1b18]">
                            <tr>
                                <th scope="col"
                                    class="px-4 py-2.5 text-left text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider cursor-pointer hover:bg-warm-border/50 dark:hover:bg-[#25211e] transition-colors"
                                    wire:click="sortBy('code')">
                                    Kode Event
                                    @if($sortField === 'code')
                                        <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-amber-600"></i>
                                    @else
                                        <i class="fa-solid fa-sort ml-1 text-primary-ink/30 dark:text-neutral-500"></i>
                                    @endif
                                </th>
                                <th scope="col"
                                    class="px-4 py-2.5 text-left text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider cursor-pointer hover:bg-warm-border/50 dark:hover:bg-[#25211e] transition-colors"
                                    wire:click="sortBy('name')">
                                    Nama Event
                                    @if($sortField === 'name')
                                        <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-amber-600"></i>
                                    @else
                                        <i class="fa-solid fa-sort ml-1 text-primary-ink/30 dark:text-neutral-500"></i>
                                    @endif
                                </th>
                                <th scope="col"
                                    class="px-4 py-2.5 text-left text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider">
                                    Institusi
                                </th>
                                <th scope="col"
                                    class="px-4 py-2.5 text-center text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider cursor-pointer hover:bg-warm-border/50 dark:hover:bg-[#25211e] transition-colors"
                                    wire:click="sortBy('year')">
                                    Tahun
                                    @if($sortField === 'year')
                                        <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1 text-amber-600"></i>
                                    @else
                                        <i class="fa-solid fa-sort ml-1 text-primary-ink/30 dark:text-neutral-500"></i>
                                    @endif
                                </th>
                                <th scope="col"
                                    class="px-4 py-2.5 text-center text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider">
                                    Batch
                                </th>
                                <th scope="col"
                                    class="px-4 py-2.5 text-center text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider">
                                    Peserta
                                </th>
                                <th scope="col"
                                    class="px-4 py-2.5 text-left text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider">
                                    Periode
                                </th>
                                <th scope="col"
                                    class="px-4 py-2.5 text-center text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col"
                                    class="px-4 py-2.5 text-center text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider w-20">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-warm-border dark:divide-[#25211e] bg-white dark:bg-[#171412]">
                            @forelse($events as $event)
                                <tr class="hover:bg-warm-ivory/50 dark:hover:bg-[#1f1b18] transition-colors">
                                    <td class="px-4 py-2.5 text-sm font-mono-data font-semibold text-primary-ink dark:text-neutral-100">
                                        {{ $event->code }}
                                    </td>
                                    <td class="px-4 py-2.5 text-sm font-semibold text-primary-ink dark:text-neutral-100">
                                        {{ $event->name }}
                                    </td>
                                    <td class="px-4 py-2.5 text-sm text-primary-ink/75 dark:text-neutral-300">
                                        <a href="{{ route('institutions.show', $event->institution->id) }}"
                                            class="hover:text-amber-700 dark:hover:text-amber-400 hover:underline transition-colors">
                                            {{ $event->institution->name }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2.5 whitespace-nowrap text-sm text-center text-primary-ink/75 dark:text-neutral-300 font-mono-data">
                                        {{ $event->year }}
                                    </td>
                                    <td class="px-4 py-2.5 whitespace-nowrap text-sm text-center text-primary-ink/75 dark:text-neutral-300 font-mono-data">
                                        {{ $event->batches_count }}
                                    </td>
                                    <td class="px-4 py-2.5 whitespace-nowrap text-sm text-center text-primary-ink/75 dark:text-neutral-300 font-mono-data">
                                        {{ $event->participants_count }}
                                    </td>
                                    <td class="px-4 py-2.5 whitespace-nowrap text-sm text-primary-ink/75 dark:text-neutral-300 font-mono-data">
                                        {{ $event->start_date->format('d M Y') }} - {{ $event->end_date->format('d M Y') }}
                                    </td>
                                    <td class="px-4 py-2.5 whitespace-nowrap text-center">
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
                                    </td>
                                    <td class="px-4 py-2.5 whitespace-nowrap text-sm text-center">
                                        <a href="{{ route('events.show', $event->code) }}"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-warm-ivory dark:bg-[#1f1b18] border border-warm-border dark:border-[#25211e] text-primary-ink hover:text-amber-700 dark:text-neutral-300 dark:hover:text-amber-400 transition-colors shadow-xs"
                                            title="Lihat Detail">
                                            <i class="fa-solid fa-eye text-xs"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center text-sm text-primary-ink/60 dark:text-neutral-400">
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
</div>