    <div class="bg-white mx-auto my-8 shadow overflow-hidden" style="max-width: 1300px;">
        <!-- Header Section -->
        <div class="border-b-4 border-black py-4 bg-white">
            <h1 class="text-center text-2xl font-bold uppercase tracking-wide text-black">
                RANKING REKAP SKOR PENILAIAN AKHIR ASSESSMENT
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
        <!-- Toleransi Section -->
        @php $summary = $this->getPassingSummary(); @endphp
        @livewire('components.tolerance-selector', [
            'passing' => $summary['passing'],
            'total' => $summary['total'],
            'showSummary' => false,
        ])
        <!-- Enhanced Table Section -->
        <div class="px-6 pb-6 bg-white overflow-x-auto">
            <table class="min-w-full border-2 border-black text-sm text-gray-900 mt-6">
                <thead>
                    <tr class="bg-cyan-200">
                        <th class="border-2 border-black px-2 py-2 text-center align-middle" rowspan="2">NO</th>
                        <th class="border-2 border-black px-2 py-2 text-center align-middle" rowspan="2">NAMA</th>
                        <th class="border-2 border-black px-2 py-2 text-center" colspan="2">SKOR INDIVIDU</th>
                        <th class="border-2 border-black px-2 py-2 text-center align-middle" rowspan="2">TOTAL SKOR
                        </th>
                        <th class="border-2 border-black px-2 py-2 text-center" colspan="2">SKOR PENILAIAN AKHIR</th>
                        <th class="border-2 border-black px-2 py-2 text-center align-middle" rowspan="2">TOTAL SKOR
                        </th>
                        <th class="border-2 border-black px-2 py-2 text-center align-middle" rowspan="2">GAP</th>
                        <th class="border-2 border-black px-2 py-2 text-center align-middle" rowspan="2">KESIMPULAN
                        </th>
                    </tr>
                    <tr class="bg-cyan-200">
                        <th class="border-2 border-black px-2 py-2 text-center">PSYCHOLOGY</th>
                        <th class="border-2 border-black px-2 py-2 text-center">KOMPETENSI MANAGERIAL</th>
                        <th class="border-2 border-black px-2 py-2 text-center">PSYCHOLOGY {{ $potensiWeight }}%</th>
                        <th class="border-2 border-black px-2 py-2 text-center">KOMPETENSI MANAGERIAL
                            {{ $kompetensiWeight }}%</th>
                    </tr>
                </thead>
                <tbody>
                    @if (isset($rows) && $rows)
                        @foreach ($rows as $row)
                            <tr>
                                <td class="border-2 border-black px-2 py-2 text-center">{{ $row['rank'] }}</td>
                                <td class="border-2 border-black px-2 py-2">{{ $row['name'] }}</td>
                                <td class="border-2 border-black px-2 py-2 text-center">
                                    {{ number_format($row['psy_individual'], 2) }}</td>
                                <td class="border-2 border-black px-2 py-2 text-center">
                                    {{ number_format($row['mc_individual'], 2) }}</td>
                                <td class="border-2 border-black px-2 py-2 text-center">
                                    {{ number_format($row['total_individual'], 2) }}</td>
                                <td class="border-2 border-black px-2 py-2 text-center">
                                    {{ number_format($row['psy_weighted'], 2) }}</td>
                                <td class="border-2 border-black px-2 py-2 text-center">
                                    {{ number_format($row['mc_weighted'], 2) }}</td>
                                <td class="border-2 border-black px-2 py-2 text-center">
                                    {{ number_format($row['total_weighted_individual'], 2) }}</td>
                                <td class="border-2 border-black px-2 py-2 text-center">
                                    {{ number_format($row['gap'], 2) }}</td>
                                <td class="border-2 border-black px-2 py-2 text-center">{{ $row['conclusion'] }}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
            @if (isset($rows) && $rows && $rows->hasPages())
                <div class="mt-4">
                    {{ $rows->links(data: ['scrollTo' => false]) }}
                </div>
            @endif
        </div>
    </div>
