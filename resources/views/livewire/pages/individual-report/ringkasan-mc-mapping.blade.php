<style>
    /* Standard rating cell styling */
    .rating-cell-standard {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        color: #92400e;
        font-weight: bold;
    }

    /* Rating scale colors */
    .rating-1 {
        background-color: #ef4444;
        color: white;
    }

    .rating-2 {
        background-color: #f97316;
        color: white;
    }

    .rating-3 {
        background-color: #f59e0b;
        color: white;
    }

    .rating-4 {
        background-color: #10b981;
        color: white;
    }

    .rating-5 {
        background-color: #059669;
        color: white;
    }
</style>

<div>
    <div class="bg-white mx-auto my-8 shadow overflow-hidden" style="max-width: 1200px;">
        <!-- Header -->
        <div class="border-b-4 border-black py-4 bg-white">
            <h1 class="text-center text-2xl font-bold uppercase tracking-wide text-black">
                RINGKASAN KOMPETENSI
            </h1>
        </div>

        <!-- Info Section -->
        <div class="p-6 bg-white">
            <table class="w-full text-sm text-black">
                <tr>
                    <td class="py-1 font-semibold" style="width: 150px;">Nomor Tes</td>
                    <td class="py-1" style="width: 20px;">:</td>
                    <td class="py-1">{{ $participant->test_number }}</td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold">NIP</td>
                    <td class="py-1">:</td>
                    <td class="py-1">{{ $participant->skb_number ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold">Nama</td>
                    <td class="py-1">:</td>
                    <td class="py-1">{{ $participant->name }}</td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold">Jabatan Saat Ini</td>
                    <td class="py-1">:</td>
                    <td class="py-1">{{ $participant->positionFormation->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold">Standar Penilaian</td>
                    <td class="py-1">:</td>
                    <td class="py-1">{{ $participant->positionFormation->template->name ?? '-' }}
                    </td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold">Tanggal Tes</td>
                    <td class="py-1">:</td>
                    <td class="py-1">{{ $participant->assessmentEvent->start_date?->format('d F Y') ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <!-- Table Section -->
        <div class="px-6 pb-6">
            <table class="min-w-full border-2 border-black text-sm text-gray-900">
                <thead>
                    <tr class="bg-cyan-200">
                        <th class="border-2 border-black px-3 py-3 font-bold text-center" style="width: 40px;">NO</th>
                        <th class="border-2 border-black px-3 py-3 font-bold text-center" style="width: 200px;">
                            AKTIFITAS/KOMPETENSI</th>
                        <th class="border-2 border-black px-3 py-3 font-bold text-center" colspan="5"
                            style="width: 250px;">RATING</th>
                        <th class="border-2 border-black px-3 py-3 font-bold text-center">Kesimpulan</th>
                    </tr>
                    <tr class="bg-cyan-200">
                        <th class="border-2 border-black px-3 py-2"></th>
                        <th class="border-2 border-black px-3 py-2"></th>
                        <th class="border-2 border-black px-3 py-2 font-bold text-center" style="width: 50px;">1</th>
                        <th class="border-2 border-black px-3 py-2 font-bold text-center" style="width: 50px;">2</th>
                        <th class="border-2 border-black px-3 py-2 font-bold text-center" style="width: 50px;">3</th>
                        <th class="border-2 border-black px-3 py-2 font-bold text-center" style="width: 50px;">4</th>
                        <th class="border-2 border-black px-3 py-2 font-bold text-center" style="width: 50px;">5</th>
                        <th class="border-2 border-black px-3 py-2"></th>
                    </tr>
                    <tr>
                        <td class="border-2 border-black px-3 py-2 font-bold bg-gray-100" colspan="8">Aspek
                            Kompetensi</td>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($aspectsData as $aspect)
                        <tr>
                            <td class="border-2 border-black px-3 py-3 text-center">{{ $aspect['number'] }}</td>
                            <td class="border-2 border-black px-3 py-3">{{ $aspect['name'] }}</td>
                            @php
                                $roundedIndividualRating = round($aspect['individual_rating']);
                                $roundedStandardRating = round($aspect['standard_rating']);
                            @endphp
                            @for ($i = 1; $i <= 5; $i++)
                                @php
                                    $isStandard = $i == $roundedStandardRating;
                                    $isIndividual = $i == $roundedIndividualRating;
                                    $ratingClass = "rating-{$i}";
                                @endphp
                                <td
                                    class="border-2 border-black px-3 py-3 text-center relative {{ $isStandard ? 'rating-cell-standard' : '' }} {{ $isIndividual && !$isStandard ? "{$ratingClass} font-bold text-lg" : '' }}">
                                    @if ($isIndividual && $isStandard)
                                        {{-- Both standard and individual are the same --}}
                                        <span class="{{ $ratingClass }} font-bold text-lg px-2 py-1 rounded">√</span>
                                    @elseif ($isIndividual)
                                        √
                                    @elseif ($isStandard)
                                        S
                                    @endif
                                </td>
                            @endfor
                            <td class="border-2 border-black px-3 py-3 text-xs">
                                <strong>{{ $aspect['conclusion']['title'] }}</strong><br>
                                {{ $aspect['conclusion']['description'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Legend -->
        <div class="px-6 pb-6">
            <div class="text-sm font-bold mb-2 text-gray-900">Note :</div>
            <div class="grid grid-cols-1 gap-2 text-sm text-gray-900">
                <div class="flex items-center gap-3">
                    <div
                        class="w-8 h-6 rating-cell-standard border border-black flex items-center justify-center font-bold">
                        S</div>
                    <span><em>Standar</em></span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-8 h-6 rating-1 border border-black flex items-center justify-center font-bold">
                        √
                    </div>
                    <span><em>Rating 1 - Rendah</em></span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-8 h-6 rating-2 border border-black flex items-center justify-center font-bold">
                        √
                    </div>
                    <span><em>Rating 2 - Kurang</em></span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-8 h-6 rating-3 border border-black flex items-center justify-center font-bold">
                        √
                    </div>
                    <span><em>Rating 3 - Cukup</em></span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-8 h-6 rating-4 border border-black flex items-center justify-center font-bold">
                        √
                    </div>
                    <span><em>Rating 4 - Baik</em></span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-8 h-6 rating-5 border border-black flex items-center justify-center font-bold">
                        √
                    </div>
                    <span><em>Rating 5 - Baik Sekali</em></span>
                </div>
            </div>
        </div>
    </div>
</div>
