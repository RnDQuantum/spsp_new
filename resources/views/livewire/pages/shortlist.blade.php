<div class="border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] rounded-md shadow-xs overflow-hidden" wire:init="loadParticipants">
    <!-- Header Table -->
    <div class="px-6 py-4 bg-warm-ivory dark:bg-[#1f1b18] border-b border-warm-border dark:border-[#25211e]">
        <div class="flex flex-col gap-4">
            <!-- Header Title -->
            <h2 class="font-display text-xl font-bold tracking-tight text-primary-ink dark:text-neutral-100">Data Peserta Asesmen</h2>

            <!-- Filter Controls - Simple Grid -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                <!-- Filter Proyek - 5 columns -->
                <div class="md:col-span-5">
                    <x-mary-choices label="Filter Proyek" wire:model.live="selectedEventId" :options="$assessmentEventsSearchable" single
                        searchable search-function="searchEvents" debounce="300ms" min-chars="0"
                        no-result-text="Tidak ada proyek ditemukan..." />
                </div>

                <!-- Search - 5 columns -->
                <div class="md:col-span-5">
                    <label class="block text-xs font-semibold text-primary-ink/75 dark:text-neutral-400 mb-1.5">
                        Pencarian
                    </label>
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.400ms="search"
                            placeholder="Cari nama atau NIP..."
                            class="w-full rounded-md border border-warm-border dark:border-[#25211e] pl-10 pr-10 py-2 text-xs font-medium text-primary-ink dark:text-neutral-100 bg-white dark:bg-[#171412] focus:outline-none focus:border-accent-amber transition-all">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <div wire:loading wire:target="search"
                            class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="animate-spin h-4 w-4 text-accent-amber dark:text-amber-500" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Per Page + Clear Button - 2 columns -->
                <div class="md:col-span-2 flex gap-2">
                    <!-- Per Page Dropdown -->
                    <div class="w-16">
                        <label class="block text-xs font-semibold text-primary-ink/75 dark:text-neutral-400 mb-1.5">
                            Tampilkan
                        </label>
                        <select wire:model.live="perPage"
                            class="w-full rounded-md border text-center border-warm-border dark:border-[#25211e] mt-1 px-3 py-2 text-xs font-medium text-primary-ink dark:text-neutral-100 bg-white dark:bg-[#171412] focus:outline-none focus:border-accent-amber transition-all appearance-none cursor-pointer">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="0">Semua</option>
                        </select>
                    </div>

                    <!-- Clear Button -->
                    @if ($selectedEventId || $search || $perPage != 10 || $sortField != 'name' || $sortDirection != 'asc')
                        <div class="flex items-end">
                            <button wire:click="clearFilters"
                                class="inline-flex items-center justify-center h-[34px] w-[34px] text-xs font-semibold text-primary-ink dark:text-neutral-200 bg-warm-ivory dark:bg-[#1f1b18] border border-warm-border dark:border-[#25211e] rounded-md hover:bg-red-50 hover:text-red-600 hover:border-red-200 dark:hover:bg-red-950/40 dark:hover:text-red-400 dark:hover:border-red-800/50 transition-colors shadow-xs"
                                title="Reset Filter">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Summary -->
    @if ($selectedEventId || $search || $sortField != 'name' || $sortDirection != 'asc')
        <div class="px-6 py-3 bg-warm-ivory/80 dark:bg-[#1f1b18] border-b border-warm-border dark:border-[#25211e]">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-primary-ink/75 dark:text-neutral-400 font-semibold">Filter aktif:</span>
                @if ($selectedEventId)
                    @php
                        $selectedEvent = collect($assessmentEventsSearchable)->firstWhere('id', $selectedEventId);
                    @endphp
                    @if ($selectedEvent)
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-warm-ivory dark:bg-[#25211e] border border-warm-border dark:border-[#25211e] text-primary-ink dark:text-neutral-200">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                </path>
                            </svg>
                            {{ $selectedEvent['name'] }}
                        </span>
                    @endif
                @endif
                @if ($search)
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-warm-ivory dark:bg-[#25211e] border border-warm-border dark:border-[#25211e] text-primary-ink dark:text-neutral-200">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        "{{ $search }}"
                    </span>
                @endif
                @if ($sortField != 'name' || $sortDirection != 'asc')
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-warm-ivory dark:bg-[#25211e] border border-warm-border dark:border-[#25211e] text-primary-ink dark:text-neutral-200">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path>
                        </svg>
                        Urut: {{ ucfirst(trans('column.' . $sortField)) }}
                        ({{ $sortDirection === 'asc' ? 'A-Z' : 'Z-A' }})
                    </span>
                @endif
            </div>
        </div>
    @endif

    <!-- Table Container with Loading State -->
    <div class="relative min-h-[400px]">
        <!-- Skeleton Loading (shown on initial load) -->
        @if (!$readyToLoad)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-warm-ivory dark:bg-[#1f1b18] border-b border-warm-border dark:border-[#25211e] text-primary-ink dark:text-neutral-200">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-primary-ink/75 dark:text-neutral-300 uppercase tracking-wider">
                                No.</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-primary-ink/75 dark:text-neutral-300 uppercase tracking-wider">
                                NIP</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-primary-ink/75 dark:text-neutral-300 uppercase tracking-wider">
                                Nama</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-primary-ink/75 dark:text-neutral-300 uppercase tracking-wider">
                                Email</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-primary-ink/75 dark:text-neutral-300 uppercase tracking-wider">
                                Telepon</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-primary-ink/75 dark:text-neutral-300 uppercase tracking-wider">
                                Jenis Kelamin</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-primary-ink/75 dark:text-neutral-300 uppercase tracking-wider">
                                Kode Proyek</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-primary-ink/75 dark:text-neutral-300 uppercase tracking-wider">
                                Batch</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-primary-ink/75 dark:text-neutral-300 uppercase tracking-wider">
                                Posisi</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-primary-ink/75 dark:text-neutral-300 uppercase tracking-wider">
                                Tgl Assessment</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-primary-ink/75 dark:text-neutral-300 uppercase tracking-wider">
                                No. Test</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-[#171412] divide-y divide-warm-border dark:divide-[#25211e]/40 text-sm">
                        @for ($i = 0; $i < 10; $i++)
                            <tr class="animate-pulse">
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <div class="h-4 bg-warm-border dark:bg-[#25211e] rounded w-20"></div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <div class="h-4 bg-warm-border dark:bg-[#25211e] rounded w-24"></div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <div class="h-4 bg-warm-border dark:bg-[#25211e] rounded w-32"></div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <div class="h-4 bg-warm-border dark:bg-[#25211e] rounded w-36"></div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <div class="h-4 bg-warm-border dark:bg-[#25211e] rounded w-28"></div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <div class="h-4 bg-warm-border dark:bg-[#25211e] rounded w-20"></div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <div class="h-6 bg-warm-border dark:bg-[#25211e] rounded w-16"></div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <div class="h-4 bg-warm-border dark:bg-[#25211e] rounded w-24"></div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <div class="h-4 bg-warm-border dark:bg-[#25211e] rounded w-32"></div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <div class="h-4 bg-warm-border dark:bg-[#25211e] rounded w-24"></div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <div class="h-4 bg-warm-border dark:bg-[#25211e] rounded w-20"></div>
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        @else
            <!-- Actual Table Content -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-warm-ivory dark:bg-[#1f1b18] border-b border-warm-border dark:border-[#25211e] text-primary-ink dark:text-neutral-200">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-primary-ink dark:text-neutral-100 uppercase tracking-wider">
                                No.
                            </th>

                            <!-- Sortable Headers -->
                            <th wire:click="sort('skb_number')"
                                class="px-6 py-3 text-left text-xs font-semibold text-primary-ink dark:text-neutral-100 uppercase tracking-wider cursor-pointer hover:bg-warm-border/50 dark:hover:bg-[#25211e]/50 transition-colors">
                                <div class="flex items-center space-x-1 select-none">
                                    <span>NIP</span>
                                    <span class="text-primary-ink/50 dark:text-neutral-400">
                                        @if ($sortField === 'skb_number')
                                            @if ($sortDirection === 'asc')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            @endif
                                        @else
                                            <svg class="w-4 h-4 opacity-0 group-hover:opacity-50" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7"></path>
                                            </svg>
                                        @endif
                                    </span>
                                </div>
                            </th>

                            <th wire:click="sort('name')"
                                class="px-6 py-3 text-left text-xs font-semibold text-primary-ink dark:text-neutral-100 uppercase tracking-wider cursor-pointer hover:bg-warm-border/50 dark:hover:bg-[#25211e]/50 transition-colors">
                                <div class="flex items-center space-x-1 select-none">
                                    <span>Nama</span>
                                    <span class="text-primary-ink/50 dark:text-neutral-400">
                                        @if ($sortField === 'name')
                                            @if ($sortDirection === 'asc')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            @endif
                                        @else
                                            <svg class="w-4 h-4 opacity-0 group-hover:opacity-50" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7"></path>
                                            </svg>
                                        @endif
                                    </span>
                                </div>
                            </th>

                            <th wire:click="sort('email')"
                                class="px-6 py-3 text-left text-xs font-semibold text-primary-ink dark:text-neutral-100 uppercase tracking-wider cursor-pointer hover:bg-warm-border/50 dark:hover:bg-[#25211e]/50 transition-colors">
                                <div class="flex items-center space-x-1 select-none">
                                    <span>Email</span>
                                    <span class="text-primary-ink/50 dark:text-neutral-400">
                                        @if ($sortField === 'email')
                                            @if ($sortDirection === 'asc')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            @endif
                                        @else
                                            <svg class="w-4 h-4 opacity-0 group-hover:opacity-50" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7"></path>
                                            </svg>
                                        @endif
                                    </span>
                                </div>
                            </th>

                            <th wire:click="sort('phone')"
                                class="px-6 py-3 text-left text-xs font-semibold text-primary-ink dark:text-neutral-100 uppercase tracking-wider cursor-pointer hover:bg-warm-border/50 dark:hover:bg-[#25211e]/50 transition-colors">
                                <div class="flex items-center space-x-1 select-none">
                                    <span>Telepon</span>
                                    <span class="text-primary-ink/50 dark:text-neutral-400">
                                        @if ($sortField === 'phone')
                                            @if ($sortDirection === 'asc')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            @endif
                                        @else
                                            <svg class="w-4 h-4 opacity-0 group-hover:opacity-50" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7"></path>
                                            </svg>
                                        @endif
                                    </span>
                                </div>
                            </th>

                            <th wire:click="sort('gender')"
                                class="px-6 py-3 text-left text-xs font-semibold text-primary-ink dark:text-neutral-100 uppercase tracking-wider cursor-pointer hover:bg-warm-border/50 dark:hover:bg-[#25211e]/50 transition-colors">
                                <div class="flex items-center space-x-1 select-none">
                                    <span>Jenis Kelamin</span>
                                    <span class="text-primary-ink/50 dark:text-neutral-400">
                                        @if ($sortField === 'gender')
                                            @if ($sortDirection === 'asc')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            @endif
                                        @else
                                            <svg class="w-4 h-4 opacity-0 group-hover:opacity-50" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7"></path>
                                            </svg>
                                        @endif
                                    </span>
                                </div>
                            </th>

                            <th wire:click="sort('event_code')"
                                class="px-6 py-3 text-left text-xs font-semibold text-primary-ink dark:text-neutral-100 uppercase tracking-wider cursor-pointer hover:bg-warm-border/50 dark:hover:bg-[#25211e]/50 transition-colors">
                                <div class="flex items-center space-x-1 select-none">
                                    <span>Kode Proyek</span>
                                    <span class="text-primary-ink/50 dark:text-neutral-400">
                                        @if ($sortField === 'event_code')
                                            @if ($sortDirection === 'asc')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            @endif
                                        @else
                                            <svg class="w-4 h-4 opacity-0 group-hover:opacity-50" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7"></path>
                                            </svg>
                                        @endif
                                    </span>
                                </div>
                            </th>

                            <th wire:click="sort('batch_name')"
                                class="px-6 py-3 text-left text-xs font-semibold text-primary-ink dark:text-neutral-100 uppercase tracking-wider cursor-pointer hover:bg-warm-border/50 dark:hover:bg-[#25211e]/50 transition-colors">
                                <div class="flex items-center space-x-1 select-none">
                                    <span>Batch</span>
                                    <span class="text-primary-ink/50 dark:text-neutral-400">
                                        @if ($sortField === 'batch_name')
                                            @if ($sortDirection === 'asc')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            @endif
                                        @else
                                            <svg class="w-4 h-4 opacity-0 group-hover:opacity-50" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7"></path>
                                            </svg>
                                        @endif
                                    </span>
                                </div>
                            </th>

                            <th wire:click="sort('position_name')"
                                class="px-6 py-3 text-left text-xs font-semibold text-primary-ink dark:text-neutral-100 uppercase tracking-wider cursor-pointer hover:bg-warm-border/50 dark:hover:bg-[#25211e]/50 transition-colors">
                                <div class="flex items-center space-x-1 select-none">
                                    <span>Posisi</span>
                                    <span class="text-primary-ink/50 dark:text-neutral-400">
                                        @if ($sortField === 'position_name')
                                            @if ($sortDirection === 'asc')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            @endif
                                        @else
                                            <svg class="w-4 h-4 opacity-0 group-hover:opacity-50" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7"></path>
                                            </svg>
                                        @endif
                                    </span>
                                </div>
                            </th>

                            <th wire:click="sort('assessment_date')"
                                class="px-6 py-3 text-left text-xs font-semibold text-primary-ink dark:text-neutral-100 uppercase tracking-wider cursor-pointer hover:bg-warm-border/50 dark:hover:bg-[#25211e]/50 transition-colors">
                                <div class="flex items-center space-x-1 select-none">
                                    <span>Tgl Assessment</span>
                                    <span class="text-primary-ink/50 dark:text-neutral-400">
                                        @if ($sortField === 'assessment_date')
                                            @if ($sortDirection === 'asc')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            @endif
                                        @else
                                            <svg class="w-4 h-4 opacity-0 group-hover:opacity-50" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7"></path>
                                            </svg>
                                        @endif
                                    </span>
                                </div>
                            </th>

                            <th wire:click="sort('test_number')"
                                class="px-6 py-3 text-left text-xs font-semibold text-primary-ink dark:text-neutral-100 uppercase tracking-wider cursor-pointer hover:bg-warm-border/50 dark:hover:bg-[#25211e]/50 transition-colors">
                                <div class="flex items-center space-x-1 select-none">
                                    <span>No. Test</span>
                                    <span class="text-primary-ink/50 dark:text-neutral-400">
                                        @if ($sortField === 'test_number')
                                            @if ($sortDirection === 'asc')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            @endif
                                        @else
                                            <svg class="w-4 h-4 opacity-0 group-hover:opacity-50" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 15l7-7 7 7"></path>
                                            </svg>
                                        @endif
                                    </span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-[#171412] divide-y divide-warm-border dark:divide-[#25211e]/40 text-sm">
                        @forelse($this->participants as $index => $participant)
                            <tr class="hover:bg-warm-ivory/50 dark:hover:bg-[#1f1b18]/50 transition-colors duration-150"
                                wire:key="participant-{{ $participant->id }}">
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-primary-ink dark:text-neutral-100">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="font-medium">{{ $this->participants->firstItem() + $index }}</span>
                                        <button wire:click="handleDetail({{ $participant->id }})"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-primary-ink dark:text-neutral-200 bg-warm-ivory dark:bg-[#1f1b18] border border-warm-border dark:border-[#25211e] rounded-md hover:bg-primary-ink hover:text-warm-ivory dark:hover:bg-amber-600 dark:hover:text-white transition-colors shadow-xs">
                                            <svg class="w-3.5 h-3.5 text-accent-amber" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                            Detail
                                        </button>
                                    </div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-primary-ink dark:text-neutral-100">
                                    {{ $participant->skb_number ?? '-' }}
                                </td>
                                <td
                                    class="px-4 py-2 whitespace-nowrap text-sm font-medium text-primary-ink dark:text-neutral-100">
                                    {{ $participant->name }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-primary-ink dark:text-neutral-100">
                                    {{ $participant->email ?? '-' }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-primary-ink dark:text-neutral-100">
                                    {{ $participant->phone ?? '-' }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-primary-ink dark:text-neutral-100">
                                    {{ $participant->gender ?? '-' }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-primary-ink dark:text-neutral-100">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-warm-ivory dark:bg-[#25211e] border border-warm-border dark:border-[#25211e] text-primary-ink dark:text-neutral-200">
                                        {{ $participant->assessmentEvent->code ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-primary-ink dark:text-neutral-100">
                                    {{ $participant->batch->name ?? '-' }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-primary-ink dark:text-neutral-100">
                                    <div>
                                        {{ $participant->positionFormation->name ?? '-' }}
                                        @if ($participant->positionFormation?->code)
                                            <div class="text-xs text-primary-ink/60 dark:text-neutral-400 mt-0.5">
                                                {{ $participant->positionFormation->code }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-primary-ink dark:text-neutral-100">
                                    {{ $participant->assessment_date?->format('d M Y') ?? '-' }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-primary-ink dark:text-neutral-100">
                                    {{ $participant->test_number ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="h-16 w-16 text-accent-amber mb-4" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                            </path>
                                        </svg>
                                        <p class="text-lg font-semibold text-primary-ink dark:text-neutral-100 mb-1">Tidak
                                            ada peserta
                                            ditemukan
                                        </p>
                                        <p class="text-sm text-primary-ink/75 dark:text-neutral-400">Coba ubah filter atau kata
                                            kunci
                                            pencarian</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($this->participants->hasPages())
                <div class="px-6 py-4 border-t border-warm-border dark:border-[#25211e] bg-warm-ivory dark:bg-[#1f1b18]">
                    {{ $this->participants->links() }}
                </div>
            @endif
        @endif

        <!-- Loading Overlay (for subsequent loads) -->
        <div wire:loading.delay.long wire:target="selectedEventId,search,perPage,gotoPage,previousPage,nextPage,sort"
            class="absolute inset-0 bg-white/80 dark:bg-[#171412]/80 backdrop-blur-xs flex items-center justify-center z-10 transition-opacity duration-200">
            <div class="flex flex-col items-center space-y-3">
                <svg class="animate-spin h-10 w-10 text-accent-amber dark:text-amber-500"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <p class="text-sm font-semibold text-primary-ink dark:text-neutral-200">Memuat data...</p>
            </div>
        </div>
    </div>
</div>
