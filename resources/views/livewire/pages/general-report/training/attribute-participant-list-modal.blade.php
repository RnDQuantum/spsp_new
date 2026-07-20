<div x-data="{ show: @entangle('showModal') }">
    {{-- Modal Overlay --}}
    <div x-cloak x-show="show" x-transition.opacity.duration.150ms
        x-on:keydown.esc.window="$wire.closeModal()" x-on:click.self="$wire.closeModal()"
        class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-4 pb-8 backdrop-blur-xs sm:items-center lg:p-8"
        role="dialog" aria-modal="true" aria-labelledby="modalTitle">

        {{-- Modal Dialog --}}
        <div x-show="show"
            x-transition:enter="transition ease-out duration-100 motion-reduce:transition-opacity"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="flex w-full max-w-4xl flex-col overflow-hidden rounded-lg border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] text-primary-ink dark:text-neutral-100 shadow-2xl font-sans"
            x-trap="show">

            {{-- Dialog Header --}}
            <div class="flex items-center justify-between border-b border-warm-border dark:border-[#25211e] bg-warm-ivory dark:bg-[#1f1b18] px-6 py-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-accent-amber/15 text-accent-amber font-bold">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 id="modalTitle" class="text-xl font-bold font-display tracking-tight text-primary-ink dark:text-neutral-100">
                            Daftar Peserta: {{ $selectedAttributeName ?? 'Atribut' }}
                        </h3>
                        <p class="text-xs text-primary-ink/70 dark:text-neutral-400 font-mono-data">
                            Total: {{ $paginatedData['total'] }} peserta
                        </p>
                    </div>
                </div>
                <button x-on:click="$wire.closeModal()" aria-label="close modal" class="text-primary-ink/70 dark:text-neutral-400 hover:text-accent-amber transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" stroke="currentColor"
                        fill="none" stroke-width="2" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Search Bar --}}
            <div class="bg-white dark:bg-[#171412] border-b border-warm-border dark:border-[#25211e] px-6 py-4">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="relative flex-1 min-w-[240px]">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Cari nama atau nomor tes..."
                            class="w-full rounded-lg border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#1f1b18] px-4 py-2 pl-10 text-sm text-primary-ink dark:text-neutral-100 focus:outline-none focus:border-accent-amber">
                        <svg class="absolute left-3 top-2.5 h-4 w-4 text-primary-ink/50 dark:text-neutral-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <div class="text-xs font-mono-data text-primary-ink/70 dark:text-neutral-400">
                        Menampilkan {{ $paginatedData['data']->count() }} dari {{ $paginatedData['total'] }} peserta
                    </div>
                </div>
            </div>

            {{-- Dialog Body / Table --}}
            <div class="max-h-96 overflow-x-auto bg-white dark:bg-[#171412] relative">
                {{-- Loading Overlay --}}
                <div wire:loading wire:target="search,sortBy,nextPage,previousPage,gotoPage"
                    class="absolute inset-0 bg-white/80 dark:bg-[#171412]/80 z-20 flex items-center justify-center backdrop-blur-xs">
                    <div class="flex flex-col items-center">
                        <div class="animate-spin rounded-full h-7 w-7 border-b-2 border-accent-amber mb-2"></div>
                        <div class="text-xs font-bold text-primary-ink dark:text-neutral-200">Memuat...</div>
                    </div>
                </div>

                <table class="w-full border-collapse text-sm text-primary-ink dark:text-neutral-200">
                    <thead class="sticky top-0 z-10 bg-warm-ivory dark:bg-[#1f1b18] border-b border-warm-border dark:border-[#25211e]">
                        <tr>
                            <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold">
                                No
                            </th>
                            <th class="cursor-pointer border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold hover:bg-warm-ivory/80 dark:hover:bg-[#1f1b18]/80 transition-colors"
                                wire:click="sortBy('priority')">
                                <div class="flex items-center justify-center gap-1">
                                    Priority
                                    @if ($sortBy === 'priority')
                                        <svg class="h-3.5 w-3.5 text-accent-amber" fill="currentColor" viewBox="0 0 20 20">
                                            @if ($sortDirection === 'asc')
                                                <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                            @else
                                                <path d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" />
                                            @endif
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th class="cursor-pointer border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold hover:bg-warm-ivory/80 dark:hover:bg-[#1f1b18]/80 transition-colors"
                                wire:click="sortBy('test_number')">
                                <div class="flex items-center justify-center gap-1">
                                    Nomor Tes
                                    @if ($sortBy === 'test_number')
                                        <svg class="h-3.5 w-3.5 text-accent-amber" fill="currentColor" viewBox="0 0 20 20">
                                            @if ($sortDirection === 'asc')
                                                <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                            @else
                                                <path d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" />
                                            @endif
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th class="cursor-pointer border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-left font-bold hover:bg-warm-ivory/80 dark:hover:bg-[#1f1b18]/80 transition-colors"
                                wire:click="sortBy('name')">
                                <div class="flex items-center gap-1">
                                    Nama
                                    @if ($sortBy === 'name')
                                        <svg class="h-3.5 w-3.5 text-accent-amber" fill="currentColor" viewBox="0 0 20 20">
                                            @if ($sortDirection === 'asc')
                                                <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                            @else
                                                <path d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" />
                                            @endif
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th class="cursor-pointer border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-left font-bold hover:bg-warm-ivory/80 dark:hover:bg-[#1f1b18]/80 transition-colors"
                                wire:click="sortBy('position')">
                                <div class="flex items-center gap-1">
                                    Jabatan
                                    @if ($sortBy === 'position')
                                        <svg class="h-3.5 w-3.5 text-accent-amber" fill="currentColor" viewBox="0 0 20 20">
                                            @if ($sortDirection === 'asc')
                                                <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                            @else
                                                <path d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" />
                                            @endif
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th class="cursor-pointer border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold hover:bg-warm-ivory/80 dark:hover:bg-[#1f1b18]/80 transition-colors"
                                wire:click="sortBy('rating')">
                                <div class="flex items-center justify-center gap-1">
                                    Rating
                                    @if ($sortBy === 'rating')
                                        <svg class="h-3.5 w-3.5 text-accent-amber" fill="currentColor" viewBox="0 0 20 20">
                                            @if ($sortDirection === 'asc')
                                                <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                            @else
                                                <path d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" />
                                            @endif
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th class="border border-warm-border dark:border-[#25211e] px-4 py-2.5 text-center font-bold">
                                Statement
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-[#171412]">
                        @forelse ($paginatedData['data'] as $index => $participant)
                            <tr class="hover:bg-warm-ivory/50 dark:hover:bg-[#1f1b18]/50 transition-colors duration-150">
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data text-xs">
                                    {{ ($paginatedData['current_page'] - 1) * $paginatedData['per_page'] + $index + 1 }}
                                </td>
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data text-xs">
                                    #{{ $participant['priority'] }}
                                </td>
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data text-xs">
                                    {{ $participant['test_number'] }}
                                </td>
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-semibold text-primary-ink dark:text-neutral-100">
                                    {{ $participant['name'] }}
                                </td>
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2">
                                    {{ $participant['position'] ?? '-' }}
                                </td>
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data font-bold text-xs {{ $participant['is_recommended'] ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                    {{ number_format($participant['rating'], 2) }}
                                </td>
                                <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center">
                                    @if ($participant['is_recommended'])
                                        <span class="inline-block px-2.5 py-0.5 rounded text-xs font-bold uppercase tracking-wider bg-red-600 text-white">
                                            Recommended
                                        </span>
                                    @else
                                        <span class="inline-block px-2.5 py-0.5 rounded text-xs font-bold uppercase tracking-wider bg-green-600 text-white">
                                            Not Recommended
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="border border-warm-border dark:border-[#25211e] px-4 py-6 text-center text-primary-ink/60 dark:text-neutral-400">
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
                <div class="border-t border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] px-6 py-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="text-xs font-mono-data text-primary-ink/70 dark:text-neutral-400">
                            Menampilkan {{ ($paginatedData['current_page'] - 1) * $paginatedData['per_page'] + 1 }}
                            sampai
                            {{ min($paginatedData['current_page'] * $paginatedData['per_page'], $paginatedData['total']) }}
                            dari {{ $paginatedData['total'] }} peserta
                        </div>
                        <div class="flex gap-1.5">
                            {{-- Previous Button --}}
                            <button wire:click="previousPage" @if ($paginatedData['current_page'] <= 1) disabled @endif
                                class="rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#1f1b18] px-3 py-1 text-xs font-bold text-primary-ink dark:text-neutral-200 transition hover:bg-warm-ivory disabled:cursor-not-allowed disabled:opacity-40">
                                ← Prev
                            </button>

                            {{-- Page Numbers --}}
                            @for ($i = 1; $i <= $paginatedData['last_page']; $i++)
                                @if ($i == 1 || $i == $paginatedData['last_page'] || abs($i - $paginatedData['current_page']) <= 2)
                                    <button wire:click="gotoPage({{ $i }})"
                                        class="rounded-md border px-3 py-1 text-xs font-bold transition-colors
                                        @if ($i == $paginatedData['current_page']) border-accent-amber bg-accent-amber text-white
                                        @else border-warm-border dark:border-[#25211e] bg-white dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200 hover:bg-warm-ivory @endif">
                                        {{ $i }}
                                    </button>
                                @elseif (abs($i - $paginatedData['current_page']) == 3)
                                    <span class="px-2 py-1 text-xs text-primary-ink/50 dark:text-neutral-500">...</span>
                                @endif
                            @endfor

                            {{-- Next Button --}}
                            <button wire:click="nextPage" @if ($paginatedData['current_page'] >= $paginatedData['last_page']) disabled @endif
                                class="rounded-md border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#1f1b18] px-3 py-1 text-xs font-bold text-primary-ink dark:text-neutral-200 transition hover:bg-warm-ivory disabled:cursor-not-allowed disabled:opacity-40">
                                Next →
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Dialog Footer --}}
            <div class="flex justify-end border-t border-warm-border dark:border-[#25211e] bg-warm-ivory dark:bg-[#1f1b18] p-4">
                <button x-on:click="$wire.closeModal()" type="button"
                    class="rounded-lg bg-accent-amber px-5 py-2 text-center text-xs font-bold uppercase tracking-wider text-white transition hover:bg-amber-700 active:scale-95 cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
