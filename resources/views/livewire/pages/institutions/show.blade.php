<div>
    <div class="max-w-[1400px] mx-auto p-3 md:p-4 font-sans text-primary-ink dark:text-neutral-100">
        <div class="bg-white dark:bg-[#171412] p-4 md:p-6 rounded-xl border border-warm-border dark:border-[#25211e] shadow-xs">
            
            {{-- Header Editorial Executive Journal --}}
            <div class="mb-4 pb-4 border-b border-warm-border dark:border-[#25211e]">
                <span class="font-mono-data text-amber-700 dark:text-amber-500 font-bold uppercase tracking-widest text-xs block mb-1">
                    ADMINISTRATION / DETAIL INSTITUSI
                </span>
                <h1 class="font-display text-xl md:text-2xl font-bold tracking-tight text-primary-ink dark:text-neutral-100">
                    {{ $institution->name }}
                </h1>
            </div>

            {{-- Breadcrumb --}}
            <div class="mb-6 p-3 bg-warm-ivory dark:bg-[#1f1b18] border border-warm-border dark:border-[#25211e] rounded-lg">
                <nav class="flex items-center text-xs font-medium text-primary-ink/70 dark:text-neutral-400">
                    <a href="{{ route('dashboard-admin') }}" class="hover:text-amber-700 dark:hover:text-amber-400 transition-colors">
                        <i class="fa-solid fa-home mr-1"></i>Dashboard
                    </a>
                    <span class="mx-2 text-primary-ink/40 dark:text-neutral-600">/</span>
                    <a href="{{ route('daftar-klien') }}" class="hover:text-amber-700 dark:hover:text-amber-400 transition-colors">
                        Daftar Klien
                    </a>
                    <span class="mx-2 text-primary-ink/40 dark:text-neutral-600">/</span>
                    <span class="text-primary-ink dark:text-neutral-100 font-semibold">{{ $institution->name }}</span>
                </nav>
            </div>

            {{-- Institution Info & Stats --}}
            <div class="mb-6 pb-6 border-b border-warm-border dark:border-[#25211e]">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Left Column - Basic Info -->
                    <div class="bg-warm-ivory/60 dark:bg-[#1f1b18]/60 p-5 rounded-xl border border-warm-border dark:border-[#25211e] space-y-3">
                        <h2 class="font-display text-base font-bold text-primary-ink dark:text-neutral-100 pb-2 border-b border-warm-border dark:border-[#25211e]">
                            Informasi Institusi
                        </h2>

                        <div class="flex items-start text-sm">
                            <span class="text-xs font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400 w-36 shrink-0 mt-0.5">Kode:</span>
                            <span class="font-mono-data font-semibold text-primary-ink dark:text-neutral-100">{{ $institution->code }}</span>
                        </div>

                        <div class="flex items-start text-sm">
                            <span class="text-xs font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400 w-36 shrink-0 mt-0.5">Nama:</span>
                            <span class="font-semibold text-primary-ink dark:text-neutral-100">{{ $institution->name }}</span>
                        </div>

                        <div class="flex items-start text-sm">
                            <span class="text-xs font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400 w-36 shrink-0 mt-0.5">Kategori Utama:</span>
                            <span class="text-primary-ink/90 dark:text-neutral-200">{{ $primaryCategory?->name ?? '-' }}</span>
                        </div>

                        <div class="flex items-start text-sm">
                            <span class="text-xs font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400 w-36 shrink-0 mt-0.5">Semua Kategori:</span>
                            <span class="text-primary-ink/90 dark:text-neutral-200">
                                {{ $institution->categories->pluck('name')->join(', ') }}
                            </span>
                        </div>

                        <div class="flex items-start text-sm">
                            <span class="text-xs font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400 w-36 shrink-0 mt-0.5">Terdaftar:</span>
                            <span class="font-mono-data text-primary-ink/90 dark:text-neutral-200">
                                {{ $institution->created_at->translatedFormat('d F Y') }}
                            </span>
                        </div>
                    </div>

                    <!-- Right Column - Statistics -->
                    <div>
                        <h2 class="font-display text-base font-bold text-primary-ink dark:text-neutral-100 mb-3">
                            Statistik
                        </h2>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Total Event -->
                            <div class="bg-warm-ivory dark:bg-[#1f1b18] p-4 rounded-xl border border-warm-border dark:border-[#25211e]">
                                <div class="text-2xl font-bold font-display text-primary-ink dark:text-neutral-100">{{ $stats['total_events'] }}</div>
                                <div class="text-xs font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400 mt-1">Total Event</div>
                            </div>

                            <!-- Event Aktif -->
                            <div class="bg-warm-ivory dark:bg-[#1f1b18] p-4 rounded-xl border border-warm-border dark:border-[#25211e]">
                                <div class="text-2xl font-bold font-display text-emerald-700 dark:text-emerald-400">{{ $stats['active_events'] }}</div>
                                <div class="text-xs font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400 mt-1">Event Aktif</div>
                            </div>

                            <!-- Event Selesai -->
                            <div class="bg-warm-ivory dark:bg-[#1f1b18] p-4 rounded-xl border border-warm-border dark:border-[#25211e]">
                                <div class="text-2xl font-bold font-display text-primary-ink/75 dark:text-neutral-300">{{ $stats['completed_events'] }}</div>
                                <div class="text-xs font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400 mt-1">Event Selesai</div>
                            </div>

                            <!-- Total Peserta -->
                            <div class="bg-warm-ivory dark:bg-[#1f1b18] p-4 rounded-xl border border-warm-border dark:border-[#25211e]">
                                <div class="text-2xl font-bold font-display text-amber-700 dark:text-amber-500">{{ $stats['total_participants'] }}</div>
                                <div class="text-xs font-semibold uppercase tracking-wider text-primary-ink/60 dark:text-neutral-400 mt-1">Total Peserta</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Events Section --}}
            <div class="mb-6">
                <div class="flex items-center gap-2 mb-4">
                    <span class="w-1 h-5 bg-amber-600 rounded-full"></span>
                    <h2 class="font-display text-lg font-bold tracking-tight text-primary-ink dark:text-neutral-100">
                        Daftar Assessment Event
                    </h2>
                </div>

                <!-- Filters -->
                <div class="mb-6 bg-warm-ivory dark:bg-[#1f1b18] p-4 rounded-xl border border-warm-border dark:border-[#25211e]">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-primary-ink/70 dark:text-neutral-400 mb-1.5">
                                Filter Tahun
                            </label>
                            <select wire:model.live="yearFilter"
                                class="w-full rounded-lg border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-200 text-sm p-2.5 focus:border-amber-500 focus:ring-amber-500 transition-colors">
                                <option value="all">Semua Tahun</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-primary-ink/70 dark:text-neutral-400 mb-1.5">
                                Filter Status
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

                <!-- Events Table -->
                <div class="overflow-x-auto bg-white dark:bg-[#171412] rounded-xl border border-warm-border dark:border-[#25211e]">
                    <table class="min-w-full divide-y divide-warm-border dark:divide-[#25211e]">
                        <thead class="bg-warm-ivory dark:bg-[#1f1b18]">
                            <tr>
                                <th scope="col" class="px-4 py-2.5 text-left text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider">
                                    Kode Event
                                </th>
                                <th scope="col" class="px-4 py-2.5 text-left text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider">
                                    Nama Event
                                </th>
                                <th scope="col" class="px-4 py-2.5 text-center text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider">
                                    Tahun
                                </th>
                                <th scope="col" class="px-4 py-2.5 text-center text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider">
                                    Batch
                                </th>
                                <th scope="col" class="px-4 py-2.5 text-center text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider">
                                    Peserta
                                </th>
                                <th scope="col" class="px-4 py-2.5 text-left text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider">
                                    Periode
                                </th>
                                <th scope="col" class="px-4 py-2.5 text-center text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-4 py-2.5 text-center text-xs font-semibold text-primary-ink dark:text-neutral-300 uppercase tracking-wider w-20">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-warm-border dark:divide-[#25211e] bg-white dark:bg-[#171412]">
                            @forelse($events as $event)
                                <tr class="hover:bg-warm-ivory/50 dark:hover:bg-[#1f1b18] transition-colors">
                                    <td class="px-4 py-2.5 whitespace-nowrap text-sm font-mono-data font-semibold text-primary-ink dark:text-neutral-100">
                                        {{ $event->code }}
                                    </td>
                                    <td class="px-4 py-2.5 text-sm font-semibold text-primary-ink dark:text-neutral-100">
                                        {{ $event->name }}
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
                                            title="Lihat Detail Event">
                                            <i class="fa-solid fa-eye text-xs"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-sm text-primary-ink/60 dark:text-neutral-400">
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
            <div class="pt-4 border-t border-warm-border dark:border-[#25211e]">
                <a href="{{ route('daftar-klien') }}"
                    class="inline-flex items-center px-4 py-2.5 bg-[#171412] dark:bg-[#25211e] text-white hover:bg-black dark:hover:bg-[#1f1b18] font-semibold text-sm rounded-lg border border-[#171412] dark:border-[#25211e] transition-colors shadow-xs">
                    <i class="fa-solid fa-arrow-left mr-2"></i>
                    Kembali ke Daftar Klien
                </a>
            </div>
        </div>
    </div>
</div>
