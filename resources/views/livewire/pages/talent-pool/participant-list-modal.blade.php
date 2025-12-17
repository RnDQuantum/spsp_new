<div x-data="{ show: @entangle('showModal') }">
    {{-- Modal Overlay --}}
    <div x-show="show" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        x-transition:enter="transition ease-in-out duration-400"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in-out duration-400"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        {{-- Background Overlay --}}
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal"></div>

        {{-- Modal Container --}}
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative w-full max-w-4xl bg-white dark:bg-gray-800 rounded-xl shadow-2xl"
                x-transition:enter="transition ease-in-out duration-400"
                x-transition:enter-start="opacity-0 scale-95 -translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in-out duration-400"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 -translate-y-4"
                @click.away="$wire.closeModal()">

                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between border-b-2 border-gray-200 dark:border-gray-700 px-6 py-4"
                        style="border-color: {{ $boxInfo['color'] ?? '#6B7280' }}20">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center shadow-lg"
                                style="background: {{ $boxInfo['color'] ?? '#6B7280' }}">
                                <span
                                    class="text-white font-bold text-sm">{{ $boxInfo['code'] ?? '' }}</span>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ $boxInfo['label'] ?? 'Daftar Peserta' }}
                                </h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Total: {{ $paginatedData['total'] }} peserta
                                </p>
                            </div>
                        </div>
                        <button wire:click="closeModal"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Search Bar --}}
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-750">
                        <div class="flex gap-4 items-center">
                            <div class="flex-1 relative">
                                <input type="text" wire:model.live.debounce.300ms="search"
                                    placeholder="Cari nama atau nomor tes..."
                                    class="w-full px-4 py-2 pl-10 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100">
                                <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none"
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

                    {{-- Table --}}
                    <div class="overflow-x-auto max-h-96">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-100 dark:bg-gray-700 sticky top-0 z-10">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                        No
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600"
                                        wire:click="sortBy('test_number')">
                                        <div class="flex items-center gap-1">
                                            Nomor Tes
                                            @if ($sortBy === 'test_number')
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
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
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600"
                                        wire:click="sortBy('name')">
                                        <div class="flex items-center gap-1">
                                            Nama
                                            @if ($sortBy === 'name')
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
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
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600"
                                        wire:click="sortBy('potensi_rating')">
                                        <div class="flex items-center justify-center gap-1">
                                            Potensi
                                            @if ($sortBy === 'potensi_rating')
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
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
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600"
                                        wire:click="sortBy('kinerja_rating')">
                                        <div class="flex items-center justify-center gap-1">
                                            Kinerja
                                            @if ($sortBy === 'kinerja_rating')
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
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
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($paginatedData['data'] as $index => $participant)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ ($paginatedData['current_page'] - 1) * $paginatedData['per_page'] + $index + 1 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $participant['test_number'] }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $participant['name'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                {{ number_format($participant['potensi_rating'], 2) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                {{ number_format($participant['kinerja_rating'], 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
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
                        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-750 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-700 dark:text-gray-300">
                                    Showing {{ ($paginatedData['current_page'] - 1) * $paginatedData['per_page'] + 1 }}
                                    to
                                    {{ min($paginatedData['current_page'] * $paginatedData['per_page'], $paginatedData['total']) }}
                                    of {{ $paginatedData['total'] }} results
                                </div>
                                <div class="flex gap-2">
                                    {{-- Previous Button --}}
                                    <button wire:click="previousPage" @if ($paginatedData['current_page'] <= 1) disabled @endif
                                        class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                        ← Prev
                                    </button>

                                    {{-- Page Numbers --}}
                                    @for ($i = 1; $i <= $paginatedData['last_page']; $i++)
                                        @if ($i == 1 || $i == $paginatedData['last_page'] || abs($i - $paginatedData['current_page']) <= 2)
                                            <button wire:click="gotoPage({{ $i }})"
                                                class="px-3 py-1 border rounded-md text-sm font-medium transition-colors
                                                @if ($i == $paginatedData['current_page']) bg-blue-600 text-white border-blue-600
                                                @else border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 @endif">
                                                {{ $i }}
                                            </button>
                                        @elseif (abs($i - $paginatedData['current_page']) == 3)
                                            <span class="px-2 py-1 text-gray-500">...</span>
                                        @endif
                                    @endfor

                                    {{-- Next Button --}}
                                    <button wire:click="nextPage"
                                        @if ($paginatedData['current_page'] >= $paginatedData['last_page']) disabled @endif
                                        class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                        Next →
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                {{-- Footer --}}
                <div class="px-6 py-4 bg-gray-100 dark:bg-gray-750 rounded-b-xl flex justify-end gap-3">
                    <button wire:click="closeModal"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors font-medium">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
