<div>
    <div class="max-w-[1400px] mx-auto p-3 md:p-4 font-sans text-primary-ink dark:text-neutral-100">
        <div class="bg-white dark:bg-[#171412] p-4 md:p-6 rounded-xl border border-warm-border dark:border-[#25211e] shadow-xs">
            
            {{-- Header Editorial Executive Journal --}}
            <div class="mb-6 pb-4 border-b border-warm-border dark:border-[#25211e]">
                <span class="font-mono-data text-amber-700 dark:text-amber-500 font-bold uppercase tracking-widest text-xs block mb-1">
                    ADMINISTRATION / DASHBOARD KLASTERISASI KLIEN
                </span>
                <h1 class="font-display text-xl md:text-2xl font-bold tracking-tight text-primary-ink dark:text-neutral-100">
                    Analisis Sebaran Instansi & Perusahaan
                </h1>
                <p class="text-sm font-medium text-primary-ink/70 dark:text-neutral-400 mt-1">
                    Tahun Anggaran 2024
                </p>
            </div>

            {{-- Filters Section --}}
            <div class="mb-6 bg-warm-ivory dark:bg-[#1f1b18] p-4 rounded-xl border border-warm-border dark:border-[#25211e]">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Filter 1: Tahun Data -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-primary-ink/70 dark:text-neutral-400 mb-1.5">
                            Tahun Data
                        </label>
                        <select wire:model.live="selectedYear"
                            class="w-full rounded-lg border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-200 text-sm p-2.5 focus:border-amber-500 focus:ring-amber-500 transition-colors">
                            <option value="all">Semua Tahun</option>
                            @foreach($years as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter 2: Kategori Instansi -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-primary-ink/70 dark:text-neutral-400 mb-1.5">
                            Kategori Instansi
                        </label>
                        <select wire:model.live="selectedCategory"
                            class="w-full rounded-lg border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-200 text-sm p-2.5 focus:border-amber-500 focus:ring-amber-500 transition-colors">
                            <option value="all">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->code }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter 3: Status Klien -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-primary-ink/70 dark:text-neutral-400 mb-1.5">
                            Status Klien
                        </label>
                        <select wire:model.live="selectedStatus"
                            class="w-full rounded-lg border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-200 text-sm p-2.5 focus:border-amber-500 focus:ring-amber-500 transition-colors">
                            <option value="all">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="completed">Selesai</option>
                            <option value="draft">Pending</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Summary Cards Section --}}
            <div class="mb-6">
                <div class="flex items-center gap-2 mb-4">
                    <span class="w-1 h-5 bg-amber-600 rounded-full"></span>
                    <h2 class="font-display text-lg font-bold tracking-tight text-primary-ink dark:text-neutral-100">
                        Ringkasan Eksekutif
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Card Total Klien -->
                    <div class="bg-warm-ivory dark:bg-[#1f1b18] p-5 rounded-xl border border-warm-border dark:border-[#25211e] flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400">Total Klien</p>
                            <h3 class="text-2xl md:text-3xl font-bold font-display text-primary-ink dark:text-neutral-100 mt-1">
                                {{ number_format($stats['total']) }}
                            </h3>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-white dark:bg-[#171412] border border-warm-border dark:border-[#25211e] flex items-center justify-center text-amber-700 dark:text-amber-500 shadow-xs">
                            <i class="fa-solid fa-users text-lg"></i>
                        </div>
                    </div>

                    @foreach($categories->take(3) as $category)
                    <!-- Card {{ $category->name }} -->
                    <div class="bg-warm-ivory dark:bg-[#1f1b18] p-5 rounded-xl border border-warm-border dark:border-[#25211e] flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400 truncate max-w-[140px]" title="{{ $category->name }}">
                                {{ $category->name }}
                            </p>
                            <h3 class="text-2xl md:text-3xl font-bold font-display text-primary-ink dark:text-neutral-100 mt-1">
                                {{ number_format($stats['categories'][$category->code] ?? 0) }}
                            </h3>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-white dark:bg-[#171412] border border-warm-border dark:border-[#25211e] flex items-center justify-center text-amber-700 dark:text-amber-500 shadow-xs">
                            <i class="fa-solid {{ $category->icon ?? 'fa-building' }} text-lg"></i>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Table Section --}}
            <div class="pt-4 border-t border-warm-border dark:border-[#25211e]">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-display text-lg font-bold tracking-tight text-primary-ink dark:text-neutral-100">
                        Daftar Klien Terbaru
                    </h3>
                </div>

                <div class="overflow-x-auto bg-white dark:bg-[#171412] rounded-xl border border-warm-border dark:border-[#25211e]">
                    <table class="min-w-full divide-y divide-warm-border dark:divide-[#25211e]">
                        <thead class="bg-warm-ivory dark:bg-[#1f1b18]">
                            <tr>
                                <th scope="col" class="px-4 py-2.5 text-center text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider w-16">
                                    No.
                                </th>
                                <th scope="col" class="px-4 py-2.5 text-left text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider">
                                    Nama Instansi
                                </th>
                                <th scope="col" class="px-4 py-2.5 text-left text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider">
                                    Kategori
                                </th>
                                <th scope="col" class="px-4 py-2.5 text-left text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider">
                                    Ditambahkan Pada
                                </th>
                                <th scope="col" class="px-4 py-2.5 text-left text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider">
                                    Status Klien
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-warm-border dark:divide-[#25211e] bg-white dark:bg-[#171412]">
                            @forelse($recentClients as $index => $client)
                            <tr class="hover:bg-warm-ivory/50 dark:hover:bg-[#1f1b18] transition-colors">
                                <td class="px-4 py-2.5 whitespace-nowrap text-sm text-center font-normal text-primary-ink dark:text-neutral-300">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-4 py-2.5 text-sm font-semibold text-primary-ink dark:text-neutral-100">
                                    {{ $client['name'] }}
                                </td>
                                <td class="px-4 py-2.5 text-sm text-primary-ink/75 dark:text-neutral-300" title="{{ $client['categories'] }}">
                                    {{ $client['category'] }}
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap text-sm text-primary-ink/75 dark:text-neutral-300 font-mono-data">
                                    {{ $client['date'] }}
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    @if($client['status_class'] == 'green')
                                        <span class="px-2.5 py-0.5 inline-flex text-xs font-semibold rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800/50">
                                            {{ $client['status'] }}
                                        </span>
                                    @elseif($client['status_class'] == 'yellow')
                                        <span class="px-2.5 py-0.5 inline-flex text-xs font-semibold rounded-md bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800/50">
                                            {{ $client['status'] }}
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 inline-flex text-xs font-semibold rounded-md bg-warm-border/60 text-primary-ink border border-warm-border dark:bg-[#25211e] dark:text-neutral-300 dark:border-[#25211e]">
                                            {{ $client['status'] }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-primary-ink/60 dark:text-neutral-400">
                                    Tidak ada data klien yang ditemukan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Action Navigation Buttons -->
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('daftar-klien') }}"
                        class="inline-flex items-center px-4 py-2.5 bg-[#171412] dark:bg-[#25211e] text-white hover:bg-black dark:hover:bg-[#1f1b18] font-semibold text-sm rounded-lg border border-[#171412] dark:border-[#25211e] transition-colors shadow-xs">
                        <i class="fa-solid fa-building mr-2 text-amber-500"></i>
                        Lihat Daftar Klien
                    </a>
                    <a href="{{ route('events.index') }}"
                        class="inline-flex items-center px-4 py-2.5 bg-amber-700 dark:bg-amber-600 text-white hover:bg-amber-800 dark:hover:bg-amber-500 font-semibold text-sm rounded-lg transition-colors shadow-xs">
                        <i class="fa-solid fa-calendar-check mr-2"></i>
                        Lihat Daftar Event
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>