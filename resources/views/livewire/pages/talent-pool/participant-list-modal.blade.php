<div x-data="{ show: @entangle('showModal') }">
    {{-- Modal Overlay --}}
    <div x-cloak x-show="show"
        x-transition.opacity.duration.200ms
        x-trap.inert.noscroll="show"
        x-on:keydown.esc.window="$wire.closeModal()"
        x-on:click.self="$wire.closeModal()"
        class="fixed inset-0 z-30 flex items-end justify-center bg-black/20 p-4 pb-8 backdrop-blur-md sm:items-center lg:p-8"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modalTitle">

        {{-- Modal Dialog --}}
        <div x-show="show"
            x-transition:enter="transition ease-out duration-200 delay-100 motion-reduce:transition-opacity"
            x-transition:enter-start="opacity-0 scale-50"
            x-transition:enter-end="opacity-100 scale-100"
            class="flex w-full max-w-4xl flex-col gap-0 overflow-hidden rounded-radius border border-outline bg-surface text-on-surface dark:border-outline-dark dark:bg-surface-dark-alt dark:text-on-surface-dark">

            {{-- Dialog Header --}}
            <div class="flex items-center justify-between border-b border-outline bg-surface-alt/60 px-6 py-4 dark:border-outline-dark dark:bg-surface-dark/20">
                <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full shadow-lg"
                        style="background: {{ $boxInfo['color'] ?? '#6B7280' }}">
                        <span class="text-sm font-bold text-white">{{ $boxInfo['code'] ?? '' }}</span>
                    </div>
                    <div>
                        <h3 id="modalTitle" class="text-xl font-semibold tracking-wide text-on-surface-strong dark:text-on-surface-dark-strong">
                            {{ $boxInfo['label'] ?? 'Daftar Peserta' }}
                        </h3>
                        <p class="text-sm text-on-surface/70 dark:text-on-surface-dark/70">
                            Total: {{ $paginatedData['total'] }} peserta
                        </p>
                    </div>
                </div>
                <button x-on:click="$wire.closeModal()" aria-label="close modal">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="1.4" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Search Bar --}}
            <div class="border-b border-outline bg-surface-alt/60 px-6 py-4 dark:border-outline-dark dark:bg-surface-dark/20">
                <div class="flex items-center gap-4">
                    <div class="relative flex-1">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Cari nama atau nomor tes..."
                            class="w-full rounded-lg border border-outline bg-surface px-4 py-2 pl-10 text-on-surface focus:ring-2 focus:ring-primary dark:border-outline-dark dark:bg-surface-dark dark:text-on-surface-dark dark:focus:ring-primary-dark">
                        <svg class="absolute left-3 top-2.5 h-5 w-5 text-on-surface/50 dark:text-on-surface-dark/50" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <div class="text-sm text-on-surface/70 dark:text-on-surface-dark/70">
                        Showing {{ $paginatedData['data']->count() }} of {{ $paginatedData['total'] }}
                    </div>
                </div>
            </div>

            {{-- Dialog Body / Table --}}
            <div class="max-h-96 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 z-10 bg-surface-alt dark:bg-surface-dark">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-on-surface-strong dark:text-on-surface-dark-strong">
                                No
                            </th>
                            <th class="cursor-pointer px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-on-surface-strong hover:bg-surface-alt/80 dark:text-on-surface-dark-strong dark:hover:bg-surface-dark/80"
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
                            <th class="cursor-pointer px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-on-surface-strong hover:bg-surface-alt/80 dark:text-on-surface-dark-strong dark:hover:bg-surface-dark/80"
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
                            <th class="cursor-pointer px-6 py-3 text-center text-xs font-bold uppercase tracking-wider text-on-surface-strong hover:bg-surface-alt/80 dark:text-on-surface-dark-strong dark:hover:bg-surface-dark/80"
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
                            <th class="cursor-pointer px-6 py-3 text-center text-xs font-bold uppercase tracking-wider text-on-surface-strong hover:bg-surface-alt/80 dark:text-on-surface-dark-strong dark:hover:bg-surface-dark/80"
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
                    <tbody class="divide-y divide-outline bg-surface dark:divide-outline-dark dark:bg-surface-dark">
                        @forelse ($paginatedData['data'] as $index => $participant)
                            <tr class="transition-colors hover:bg-surface-alt/50 dark:hover:bg-surface-dark/50">
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-on-surface dark:text-on-surface-dark">
                                    {{ ($paginatedData['current_page'] - 1) * $paginatedData['per_page'] + $index + 1 }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-on-surface dark:text-on-surface-dark">
                                    {{ $participant['test_number'] }}
                                </td>
                                <td class="px-6 py-4 text-sm text-on-surface dark:text-on-surface-dark">
                                    {{ $participant['name'] }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-center text-sm">
                                    <span
                                        class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ number_format($participant['potensi_rating'], 2) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-center text-sm">
                                    <span
                                        class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200">
                                        {{ number_format($participant['kinerja_rating'], 2) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-on-surface/60 dark:text-on-surface-dark/60">
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
                <div class="border-t border-outline bg-surface-alt/60 px-6 py-4 dark:border-outline-dark dark:bg-surface-dark/20">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-on-surface dark:text-on-surface-dark">
                            Showing {{ ($paginatedData['current_page'] - 1) * $paginatedData['per_page'] + 1 }}
                            to
                            {{ min($paginatedData['current_page'] * $paginatedData['per_page'], $paginatedData['total']) }}
                            of {{ $paginatedData['total'] }} results
                        </div>
                        <div class="flex gap-2">
                            {{-- Previous Button --}}
                            <button wire:click="previousPage" @if ($paginatedData['current_page'] <= 1) disabled @endif
                                class="rounded-md border border-outline bg-surface px-3 py-1 text-sm font-medium text-on-surface transition hover:bg-surface-alt disabled:cursor-not-allowed disabled:opacity-50 dark:border-outline-dark dark:bg-surface-dark dark:text-on-surface-dark dark:hover:bg-surface-dark-alt">
                                ← Prev
                            </button>

                            {{-- Page Numbers --}}
                            @for ($i = 1; $i <= $paginatedData['last_page']; $i++)
                                @if ($i == 1 || $i == $paginatedData['last_page'] || abs($i - $paginatedData['current_page']) <= 2)
                                    <button wire:click="gotoPage({{ $i }})"
                                        class="rounded-md border px-3 py-1 text-sm font-medium transition-colors
                                        @if ($i == $paginatedData['current_page']) border-primary bg-primary text-on-primary dark:border-primary-dark dark:bg-primary-dark dark:text-on-primary-dark
                                        @else border-outline bg-surface text-on-surface hover:bg-surface-alt dark:border-outline-dark dark:bg-surface-dark dark:text-on-surface-dark dark:hover:bg-surface-dark-alt @endif">
                                        {{ $i }}
                                    </button>
                                @elseif (abs($i - $paginatedData['current_page']) == 3)
                                    <span class="px-2 py-1 text-on-surface/50 dark:text-on-surface-dark/50">...</span>
                                @endif
                            @endfor

                            {{-- Next Button --}}
                            <button wire:click="nextPage"
                                @if ($paginatedData['current_page'] >= $paginatedData['last_page']) disabled @endif
                                class="rounded-md border border-outline bg-surface px-3 py-1 text-sm font-medium text-on-surface transition hover:bg-surface-alt disabled:cursor-not-allowed disabled:opacity-50 dark:border-outline-dark dark:bg-surface-dark dark:text-on-surface-dark dark:hover:bg-surface-dark-alt">
                                Next →
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Dialog Footer --}}
            <div class="flex flex-col-reverse justify-between gap-2 border-t border-outline bg-surface-alt/60 p-4 dark:border-outline-dark dark:bg-surface-dark/20 sm:flex-row sm:items-center md:justify-end">
                <button x-on:click="$wire.closeModal()" type="button"
                    class="whitespace-nowrap rounded-radius border border-primary px-4 py-2 text-center text-sm font-medium tracking-wide text-on-primary transition hover:opacity-75 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary active:opacity-100 active:outline-offset-0 dark:border-primary-dark dark:text-on-surface-dark dark:focus-visible:outline-primary-dark bg-primary dark:bg-primary-dark">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
