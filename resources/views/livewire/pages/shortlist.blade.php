<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <!-- Header Table -->
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800">Data Peserta Assessment</h2>

        <!-- Filter Controls -->
        <div class="flex items-center space-x-4">
            <!-- Filter Kode Proyek -->
            <div class="relative">
                <select wire:model.live="selectedEventId"
                    class="rounded border border-gray-300 px-4 py-2 text-gray-700 bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 min-w-[200px]">
                    <option value="">Semua Proyek</option>
                    @foreach ($this->assessmentEvents as $event)
                        <option value="{{ $event->id }}">{{ $event->code }} - {{ $event->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Search -->
            <div class="relative">
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari nama atau NIP..."
                    class="rounded border border-gray-300 px-4 py-2 text-gray-700 bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 min-w-[250px]">
                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- Clear Filters Button -->
            <button wire:click="clearFilters"
                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                Clear
            </button>
        </div>
    </div>

    <!-- Filter Summary -->
    <div class="px-6 py-2 border-b border-gray-200">
        <div class="flex items-center space-x-4 text-sm text-gray-600">
            @if ($selectedEventId)
                @php $selectedEvent = $this->assessmentEvents->find($selectedEventId); @endphp
                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">
                    Proyek: {{ $selectedEvent->code }} - {{ $selectedEvent->name }}
                </span>
            @endif
            @if ($search)
                <span class="px-2 py-1 bg-green-100 text-green-800 rounded">
                    Pencarian: "{{ $search }}"
                </span>
            @endif
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No.
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIP
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Telepon</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Jenis Kelamin</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Kode Proyek</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Posisi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl
                        Assessment</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No.
                        Test</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($this->participants as $index => $participant)
                    <tr class="hover:bg-gray-50 transition" wire:key="participant-{{ $participant->id }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $this->participants->firstItem() + $index }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $participant->skb_number ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $participant->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $participant->email ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $participant->phone ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $participant->gender ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">
                                {{ $participant->assessmentEvent->code ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $participant->batch->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $participant->positionFormation->name ?? '-' }}
                            @if ($participant->positionFormation?->code)
                                <br><small class="text-gray-500">{{ $participant->positionFormation->code }}</small>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $participant->assessment_date?->format('d M Y') ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $participant->test_number ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                    </path>
                                </svg>
                                <p class="text-lg font-medium">Tidak ada peserta ditemukan</p>
                                <p class="text-sm">Coba ubah filter atau kata kunci pencarian</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if ($this->participants->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $this->participants->links() }}
        </div>
    @endif

    <!-- Loading State -->
    <div wire:loading class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center">
        <div class="flex items-center space-x-2">
            <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <span class="text-gray-600">Memuat data...</span>
        </div>
    </div>
</div>
