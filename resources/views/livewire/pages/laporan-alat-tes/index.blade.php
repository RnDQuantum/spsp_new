<div class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
                Data Alat Tes Peserta
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Laporan rincian instrumen hasil tes psikometri per peserta
            </p>
        </div>
        <div class="w-full md:w-72">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama, no tes, SKB..."
                class="w-full text-xs px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
    </div>

    <div class="p-6 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-6xl space-y-4">
            <!-- Event Selector & Position Selector -->
            @livewire('components.event-selector', ['showLabel' => true])
            @livewire('components.position-selector', ['showLabel' => true])
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-xs text-left text-gray-700 dark:text-gray-200">
            <thead class="bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-100">
                <tr>
                    <th class="px-4 py-3 font-semibold">No</th>
                    <th class="px-4 py-3 font-semibold">No Tes / SKB</th>
                    <th class="px-4 py-3 font-semibold">Nama Peserta</th>
                    <th class="px-4 py-3 font-semibold">Instansi & Master Proyek</th>
                    <th class="px-4 py-3 font-semibold">Pelaksanaan (Event)</th>
                    <th class="px-4 py-3 font-semibold">Formasi Jabatan</th>
                    <th class="px-4 py-3 font-semibold text-center">Jumlah Alat Tes</th>
                    <th class="px-4 py-3 font-semibold text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse ($participants as $index => $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/60 transition">
                        <td class="px-4 py-3">{{ $participants->firstItem() + $index }}</td>
                        <td class="px-4 py-3 font-mono font-medium">
                            <div>{{ $row->test_number ?? '-' }}</div>
                            @if($row->skb_number && $row->skb_number !== $row->test_number)
                                <div class="text-[10px] text-gray-400">SKB: {{ $row->skb_number }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
                            {{ $row->name }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800 dark:text-gray-200">
                                {{ $row->assessmentEvent?->institution?->name ?? 'Kejaksaan Agung RI' }}
                            </div>
                            <div class="text-[10px] text-blue-600 dark:text-blue-400 font-semibold">
                                Proyek: {{ $row->assessmentEvent?->project?->code ?? '-' }} ({{ $row->assessmentEvent?->project?->name ?? '-' }})
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                {{ $row->assessmentEvent?->code ?? '-' }}
                            </span>
                            <div class="text-[10px] text-gray-500 truncate max-w-xs">
                                {{ $row->assessmentEvent?->name }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            {{ $row->positionFormation?->name ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $row->test_results_count > 0 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' }}">
                                <i class="fa-solid fa-list-check mr-1.5 text-[10px]"></i>
                                {{ $row->test_results_count }} Alat Tes
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('laporan-alat-tes-detail', ['participantId' => $row->id]) }}"
                                class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/40 dark:text-blue-300 dark:hover:bg-blue-900/60 transition"
                                title="Lihat Detail Laporan Alat Tes">
                                <i class="fa-solid fa-eye mr-1.5 text-xs"></i>
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            <i class="fa-solid fa-folder-open text-2xl mb-2 block text-gray-400"></i>
                            Tidak ada data peserta ditemukan untuk filter saat ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($participants->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
            {{ $participants->links() }}
        </div>
    @endif
</div>