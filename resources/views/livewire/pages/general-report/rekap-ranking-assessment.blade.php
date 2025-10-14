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

        <!-- Summary Statistics Section -->
        @if (!empty($conclusionSummary))
            <div class="px-6 pb-6 bg-gray-50 border-t-2 border-black">
                <h3 class="text-lg font-bold text-gray-900 mb-4 mt-4">Ringkasan Kesimpulan</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    @foreach ($conclusionSummary as $conclusion => $count)
                        @php
                            $totalParticipants = array_sum($conclusionSummary);
                            $percentage = $totalParticipants > 0 ? round(($count / $totalParticipants) * 100, 1) : 0;

                            // Determine color based on conclusion
                            $bgColor = match ($conclusion) {
                                'Di Atas Standar' => 'bg-green-100 border-green-300',
                                'Memenuhi Standar' => 'bg-blue-100 border-blue-300',
                                'Di Bawah Standar' => 'bg-red-100 border-red-300',
                                default => 'bg-gray-100 border-gray-300',
                            };
                        @endphp

                        <div class="border-2 {{ $bgColor }} rounded-lg p-4 text-center">
                            <div class="text-3xl font-bold text-gray-900">{{ $count }}</div>
                            <div class="text-sm text-gray-600 mb-2">{{ $percentage }}%</div>
                            <div class="text-sm text-gray-700 font-semibold leading-tight mb-2">{{ $conclusion }}</div>
                            <div class="text-xs text-gray-500 font-medium">
                                @switch($conclusion)
                                    @case('Di Atas Standar')
                                        Gap > 0
                                    @break

                                    @case('Memenuhi Standar')
                                        Gap ≥ Threshold
                                    @break

                                    @case('Di Bawah Standar')
                                        Gap < Threshold
                                @break @endswitch </div>
                        </div>
                    @endforeach
                </div>

                <!-- Overall Statistics -->
                @php
                    $totalParticipants = array_sum($conclusionSummary);
                    $passingCount =
                        ($conclusionSummary['Di Atas Standar'] ?? 0) + ($conclusionSummary['Memenuhi Standar'] ?? 0);
                    $passingPercentage = $totalParticipants > 0 ? round(($passingCount / $totalParticipants) * 100, 1) : 0;
                @endphp

                <div class="bg-white border-2 border-black rounded-lg p-4">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <div class="text-lg font-bold text-gray-900">{{ $totalParticipants }}</div>
                            <div class="text-sm text-gray-600">Total Peserta</div>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-green-600">{{ $passingCount }}</div>
                            <div class="text-sm text-gray-600">Memenuhi Standar</div>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-blue-600">{{ $passingPercentage }}%</div>
                            <div class="text-sm text-gray-600">Tingkat Kelulusan</div>
                        </div>
                    </div>
                </div>

                <!-- Keterangan Rentang Nilai -->
                <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <div class="text-sm text-blue-800">
                        <strong>Keterangan:</strong> Kesimpulan berdasarkan Gap (Total Weighted Individual - Total
                        Weighted Standard) dan threshold toleransi
                        <span x-data
                            x-text="$wire.tolerancePercentage > 0 ? '(' + $wire.tolerancePercentage + '%)' : '(0%)'"></span>.
                        <br>
                        <strong>Rumus:</strong>
                        <ul class="list-disc ml-6 mt-1">
                            <li>Gap = Total Weighted Individual - Total Weighted Standard</li>
                            <li>Threshold = -Total Weighted Standard × (Tolerance / 100)</li>
                            <li><strong>Di Atas Standar:</strong> Gap > 0</li>
                            <li><strong>Memenuhi Standar:</strong> Gap ≥ Threshold (dalam range toleransi)</li>
                            <li><strong>Di Bawah Standar:</strong> Gap < Threshold</li>
                        </ul>
                    </div>
                </div>
            </div>
        @endif
    </div>
