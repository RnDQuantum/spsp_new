<div x-data="{ show: @entangle('showModal') }">
    {{-- Modal Overlay --}}
    <div x-cloak x-show="show" x-transition.opacity.duration.150ms
        x-on:keydown.esc.window="$wire.closeModal()" x-on:click.self="$wire.closeModal()"
        class="fixed inset-0 z-30 flex items-end justify-center bg-black/50 p-4 pb-8 backdrop-blur-md sm:items-center lg:p-8"
        role="dialog" aria-modal="true" aria-labelledby="modalTitle"
        style="overflow-y: auto;">

        {{-- Modal Dialog --}}
        <div x-show="show"
            x-transition:enter="transition ease-out duration-100 motion-reduce:transition-opacity"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="flex w-full max-w-4xl flex-col overflow-hidden rounded-md border border-warm-border bg-white text-primary-ink dark:border-[#25211e] dark:bg-[#171412] dark:text-neutral-100 shadow-xl"
            x-trap="show">

            {{-- Dialog Header --}}
            <div
                class="flex items-center justify-between border-b border-warm-border bg-warm-ivory px-6 py-4 dark:border-[#25211e] dark:bg-[#1f1b18]">
                <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full shadow-lg"
                        style="background: {{ $boxInfo['color'] ?? '#6B7280' }}">
                        <span class="text-sm font-bold text-white">{{ $boxInfo['code'] ?? '' }}</span>
                    </div>
                    <div>
                        <h3 id="modalTitle" class="font-display text-lg font-bold tracking-tight text-primary-ink dark:text-neutral-100">
                            {{ $boxInfo['label'] ?? 'Daftar Peserta' }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Total: {{ $paginatedData['total'] }} peserta
                        </p>
                    </div>
                </div>
                <button x-on:click="$wire.closeModal()" aria-label="close modal" class="text-primary-ink/60 dark:text-neutral-400 hover:text-primary-ink dark:hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" stroke="currentColor"
                        fill="none" stroke-width="1.4" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Search Bar --}}
            <div class="bg-warm-ivory/40 px-6 py-3 border-b border-warm-border dark:border-[#25211e] dark:bg-[#1f1b18]/50">
                <div class="flex items-center gap-4">
                    <div class="relative flex-1">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Cari nama atau nomor tes..."
                            class="w-full rounded-md border border-warm-border dark:border-[#25211e] pl-10 pr-4 py-2 text-xs font-medium text-primary-ink dark:text-neutral-100 bg-white dark:bg-[#171412] focus:outline-none focus:border-accent-amber transition-all">
                        <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-500 dark:text-gray-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Showing {{ $paginatedData['data']->count() }} of {{ $paginatedData['total'] }}
                    </div>
                </div>
            </div>

            {{-- Dialog Body / Table --}}
            <div class="max-h-96 overflow-x-auto bg-white dark:bg-[#171412] relative">
                {{-- Loading Overlay --}}
                <div wire:loading wire:target="search,sortBy,nextPage,previousPage,gotoPage"
                    class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 z-20 flex items-center justify-center">
                    <div class="flex flex-col items-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-2"></div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Memuat...</div>
                    </div>
                </div>

                <table class="w-full text-sm">
                    <thead class="sticky top-0 z-10 bg-warm-ivory dark:bg-[#1f1b18] border-b border-warm-border dark:border-[#25211e] text-primary-ink dark:text-neutral-200">
                        <tr>
                            <th
                                class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-primary-ink dark:text-neutral-200">
                                No
                            </th>
                            <th class="cursor-pointer px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-primary-ink hover:bg-warm-border/50 dark:text-neutral-200 dark:hover:bg-[#25211e]/50"
                                wire:click="sortBy('test_number')">
                                <div class="flex items-center gap-1">
                                    Nomor Tes
                                    @if ($sortBy === 'test_number')
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                            @if ($sortDirection === 'asc')
                                                <path
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                            @else
                                                <path
                                                    d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" />
                                            @endif
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th class="cursor-pointer px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-primary-ink hover:bg-warm-border/50 dark:text-neutral-200 dark:hover:bg-[#25211e]/50"
                                wire:click="sortBy('name')">
                                <div class="flex items-center gap-1">
                                    Nama
                                    @if ($sortBy === 'name')
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                            @if ($sortDirection === 'asc')
                                                <path
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                            @else
                                                <path
                                                    d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" />
                                            @endif
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th class="cursor-pointer px-6 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-800/80"
                                wire:click="sortBy('potensi_rating')">
                                <div class="flex items-center justify-center gap-1">
                                    Potensi
                                    @if ($sortBy === 'potensi_rating')
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                            @if ($sortDirection === 'asc')
                                                <path
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                            @else
                                                <path
                                                    d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" />
                                            @endif
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th class="cursor-pointer px-6 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-800/80"
                                wire:click="sortBy('kinerja_rating')">
                                <div class="flex items-center justify-center gap-1">
                                    Kinerja
                                    @if ($sortBy === 'kinerja_rating')
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                            @if ($sortDirection === 'asc')
                                                <path
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                            @else
                                                <path
                                                    d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" />
                                            @endif
                                        </svg>
                                    @endif
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($paginatedData['data'] as $index => $participant)
                            <tr class="transition-colors hover:bg-gray-50/50 dark:hover:bg-gray-900/50">
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    {{ ($paginatedData['current_page'] - 1) * $paginatedData['per_page'] + $index + 1 }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $participant['test_number'] }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $participant['name'] }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-center text-sm">
                                    <span
                                        class="inline-flex items-center rounded-full bg-warm-ivory dark:bg-[#25211e] border border-warm-border dark:border-[#25211e] px-2.5 py-0.5 text-xs font-semibold text-primary-ink dark:text-neutral-200">
                                        {{ number_format($participant['potensi_rating'], 2) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-center text-sm">
                                    <span
                                        class="inline-flex items-center rounded-full bg-warm-ivory dark:bg-[#25211e] border border-warm-border dark:border-[#25211e] px-2.5 py-0.5 text-xs font-semibold text-primary-ink dark:text-neutral-200">
                                        {{ number_format($participant['kinerja_rating'], 2) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-600 dark:text-gray-400">
                                    @if ($search)
                                        Tidak ada hasil untuk "{{ $search }}"
                                    @else
                                        Tidak ada data peserta
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($paginatedData['last_page'] > 1)
                <div class="border-t border-warm-border bg-warm-ivory px-6 py-4 dark:border-[#25211e] dark:bg-[#1f1b18]">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-900 dark:text-gray-100">
                            Showing {{ ($paginatedData['current_page'] - 1) * $paginatedData['per_page'] + 1 }}
                            to
                            {{ min($paginatedData['current_page'] * $paginatedData['per_page'], $paginatedData['total']) }}
                            of {{ $paginatedData['total'] }} results
                        </div>
                        <div class="flex gap-2">
                            {{-- Previous Button --}}
                            <button wire:click="previousPage" @if ($paginatedData['current_page'] <= 1) disabled @endif
                                class="rounded-md border border-warm-border bg-white px-3 py-1 text-xs font-semibold text-primary-ink transition hover:bg-warm-ivory disabled:cursor-not-allowed disabled:opacity-50 dark:border-[#25211e] dark:bg-[#171412] dark:text-neutral-200 dark:hover:bg-[#1f1b18]">
                                ← Prev
                            </button>

                            {{-- Page Numbers --}}
                            @for ($i = 1; $i <= $paginatedData['last_page']; $i++)
                                @if ($i == 1 || $i == $paginatedData['last_page'] || abs($i - $paginatedData['current_page']) <= 2)
                                    <button wire:click="gotoPage({{ $i }})"
                                        class="rounded-md border px-3 py-1 text-sm font-medium transition-colors
                                        @if ($i == $paginatedData['current_page']) border-accent-amber bg-primary-ink text-warm-ivory dark:bg-amber-600 dark:text-white
                                        @else border-warm-border bg-white text-primary-ink hover:bg-warm-ivory dark:border-[#25211e] dark:bg-[#171412] dark:text-neutral-200 dark:hover:bg-[#1f1b18] @endif">
                                        {{ $i }}
                                    </button>
                                @elseif (abs($i - $paginatedData['current_page']) == 3)
                                    <span class="px-2 py-1 text-gray-400 dark:text-gray-600">...</span>
                                @endif
                            @endfor

                            {{-- Next Button --}}
                            <button wire:click="nextPage" @if ($paginatedData['current_page'] >= $paginatedData['last_page']) disabled @endif
                                class="rounded-md border border-warm-border bg-white px-3 py-1 text-xs font-semibold text-primary-ink transition hover:bg-warm-ivory disabled:cursor-not-allowed disabled:opacity-50 dark:border-[#25211e] dark:bg-[#171412] dark:text-neutral-200 dark:hover:bg-[#1f1b18]">
                                Next →
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Dialog Footer --}}
            <div
                class="flex flex-col-reverse justify-between gap-2 border-t border-warm-border bg-warm-ivory px-6 py-3 dark:border-[#25211e] dark:bg-[#1f1b18] sm:flex-row sm:items-center md:justify-end">
                <button x-on:click="$wire.closeModal()" type="button"
                    class="whitespace-nowrap rounded-md border border-warm-border px-4 py-2 text-center text-xs font-semibold tracking-wide text-warm-ivory bg-primary-ink hover:bg-[#2c2724] dark:bg-amber-600 dark:hover:bg-amber-700 transition-colors shadow-xs">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
