    <div class="bg-white mx-auto my-8 shadow overflow-hidden" style="max-width: 1200px;">
        <!-- Header Section -->
        <div class="border-b-4 border-black py-4 bg-white">
            <h1 class="text-center text-2xl font-bold uppercase tracking-wide text-black">
                RANKING SKOR PSYCHOLOGY MAPPING
            </h1>
            <div class="flex justify-center items-center gap-4 mt-3">
                <label for="eventCode" class="text-black font-semibold">Event</label>
                <select id="eventCode" wire:model.live="eventCode" class="border border-black px-2 py-1 text-black">
                    @foreach ($availableEvents as $ev)
                        <option value="{{ $ev['code'] }}">{{ $ev['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @php $summary = $this->getPassingSummary(); @endphp
        @livewire('components.tolerance-selector', [
            'passing' => $summary['passing'],
            'total' => $summary['total'],
        ])

        <!-- Table Section -->
        <div class="px-6 pb-6 bg-white overflow-x-auto">
            <table class="min-w-full border-2 border-black text-sm text-gray-900 mt-6">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border-2 border-black px-3 py-3 text-center">Ranking</th>
                        <th class="border-2 border-black px-3 py-3 text-center">NIP</th>
                        <th class="border-2 border-black px-3 py-3 text-center">Nama</th>
                        <th class="border-2 border-black px-3 py-3 text-center">Jabatan</th>
                        <th class="border-2 border-black px-3 py-3 text-center">Rating</th>
                        <th class="border-2 border-black px-3 py-3 text-center">Skor</th>
                        <th class="border-2 border-black px-3 py-3 text-center">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rankings as $row)
                        <tr>
                            <td class="border-2 border-black px-3 py-2 text-center">{{ $row['rank'] }}</td>
                            <td class="border-2 border-black px-3 py-2 text-center">{{ $row['nip'] }}</td>
                            <td class="border-2 border-black px-3 py-2">{{ $row['name'] }}</td>
                            <td class="border-2 border-black px-3 py-2">{{ $row['position'] }}</td>
                            <td class="border-2 border-black px-3 py-2 text-center">
                                {{ number_format($row['rating'], 2) }}</td>
                            <td class="border-2 border-black px-3 py-2 text-center">
                                {{ number_format($row['score'], 2) }}</td>
                            <td class="border-2 border-black px-3 py-2 text-center">{{ $row['conclusion'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if ($rankings && $rankings->hasPages())
                <div class="mt-4">
                    {{ $rankings->links() }}
                </div>
            @endif
        </div>
    </div>
