<div x-data="{ show: @entangle('showModal') }">
    {{-- Modal Overlay --}}
    <div x-cloak x-show="show" x-transition.opacity.duration.200ms x-trap.inert.noscroll="show"
        x-on:keydown.esc.window="$wire.closeModal()" x-on:click.self="$wire.closeModal()"
        class="fixed inset-0 z-30 flex items-end justify-center bg-black/20 p-4 pb-8 backdrop-blur-md sm:items-center lg:p-8"
        role="dialog" aria-modal="true" aria-labelledby="modalTitle">

        {{-- Modal Dialog --}}
        <div x-show="show"
            x-transition:enter="transition ease-out duration-200 delay-100 motion-reduce:transition-opacity"
            x-transition:enter-start="opacity-0 scale-50" x-transition:enter-end="opacity-100 scale-100"
            class="flex w-full max-w-4xl flex-col overflow-hidden rounded-radius border border-gray-200 bg-white text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">

            {{-- Dialog Header --}}
            <div
                class="flex items-center justify-between border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full shadow-lg"
                        style="background: {{ $boxInfo['color'] ?? '#6B7280' }}">
                        <span class="text-sm font-bold text-white">{{ $boxInfo['code'] ?? '' }}</span>
                    </div>
                    <div>
                        <h3 id="modalTitle" class="text-xl font-semibold tracking-wide text-gray-900 dark:text-white">
                            {{ $boxInfo['label'] ?? 'Daftar Peserta' }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Total: {{ $paginatedData['total'] }} peserta
                        </p>
                    </div>
                </div>
                <button x-on:click="$wire.closeModal()" aria-label="close modal">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" stroke="currentColor"
                        fill="none" stroke-width="1.4" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Search Bar --}}
            <div class="bg-white px-6 py-4 dark:bg-gray-800">
                <div class="flex items-center gap-4">
                    <div class="relative flex-1">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Cari nama atau nomor tes..."
                            class="w-full rounded-lg border border-gray-200 bg-white px-4 py-2 pl-10 text-gray-900 focus:ring-2 focus:ring-primary dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:ring-primary-dark">
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
            <div class="max-h-96 overflow-x-auto bg-white dark:bg-gray-800">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-white">
                                No
                            </th>
                            <th class="cursor-pointer px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-800/80"
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
                            <th class="cursor-pointer px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-800/80"
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
                <div class="border-t border-gray-200 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
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
                                class="rounded-md border border-gray-200 bg-white px-3 py-1 text-sm font-medium text-gray-900 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800">
                                ← Prev
                            </button>

                            {{-- Page Numbers --}}
                            @for ($i = 1; $i <= $paginatedData['last_page']; $i++)
                                @if ($i == 1 || $i == $paginatedData['last_page'] || abs($i - $paginatedData['current_page']) <= 2)
                                    <button wire:click="gotoPage({{ $i }})"
                                        class="rounded-md border px-3 py-1 text-sm font-medium transition-colors
                                        @if ($i == $paginatedData['current_page']) border-primary bg-primary text-white dark:border-primary-dark dark:bg-primary-dark dark:text-white
                                        @else border-gray-200 bg-white text-gray-900 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800 @endif">
                                        {{ $i }}
                                    </button>
                                @elseif (abs($i - $paginatedData['current_page']) == 3)
                                    <span class="px-2 py-1 text-gray-400 dark:text-gray-600">...</span>
                                @endif
                            @endfor

                            {{-- Next Button --}}
                            <button wire:click="nextPage" @if ($paginatedData['current_page'] >= $paginatedData['last_page']) disabled @endif
                                class="rounded-md border border-gray-200 bg-white px-3 py-1 text-sm font-medium text-gray-900 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800">
                                Next →
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Dialog Footer --}}
            <div
                class="flex flex-col-reverse justify-between gap-2 border-t border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800 sm:flex-row sm:items-center md:justify-end">
                <button x-on:click="$wire.closeModal()" type="button"
                    class="whitespace-nowrap rounded-radius border border-primary px-4 py-2 text-center text-sm font-medium tracking-wide text-white transition hover:opacity-75 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary active:opacity-100 active:outline-offset-0 dark:border-primary-dark dark:text-gray-100 dark:focus-visible:outline-primary-dark bg-primary dark:bg-primary-dark">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
