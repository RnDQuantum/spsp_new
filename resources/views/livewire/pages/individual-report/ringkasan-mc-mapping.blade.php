<div>
    <div class="mx-auto my-8 shadow overflow-hidden max-w-6xl bg-white dark:bg-gray-800">

        <!-- Header - DARK MODE READY -->
        <div class="border-b-4 border-black py-4 bg-gray-200 dark:bg-gray-700">
            <h1 class="text-center text-2xl font-bold uppercase tracking-wide text-black dark:text-white">
                RINGKASAN KOMPETENSI
            </h1>
        </div>

        <!-- Info Section - DARK MODE READY -->
        <div class="p-6 bg-white dark:bg-gray-800">
            <table class="w-full text-sm">
                <tr class="dark:text-gray-200">
                    <td class="py-1 font-semibold text-black dark:text-gray-200 w-36">Nomor Tes</td>
                    <td class="py-1 text-black dark:text-gray-200 w-4">:</td>
                    <td class="py-1 text-black dark:text-gray-200">{{ $participant->test_number }}</td>
                </tr>
                <tr class="dark:text-gray-200">
                    <td class="py-1 font-semibold text-black dark:text-gray-200">NIP</td>
                    <td class="py-1 text-black dark:text-gray-200">:</td>
                    <td class="py-1 text-black dark:text-gray-200">{{ $participant->skb_number ?? '-' }}</td>
                </tr>
                <tr class="dark:text-gray-200">
                    <td class="py-1 font-semibold text-black dark:text-gray-200">Nama</td>
                    <td class="py-1 text-black dark:text-gray-200">:</td>
                    <td class="py-1 text-black dark:text-gray-200">{{ $participant->name }}</td>
                </tr>
                <tr class="dark:text-gray-200">
                    <td class="py-1 font-semibold text-black dark:text-gray-200">Jabatan Saat Ini</td>
                    <td class="py-1 text-black dark:text-gray-200">:</td>
                    <td class="py-1 text-black dark:text-gray-200">{{ $participant->positionFormation->name ?? '-' }}
                    </td>
                </tr>
                <tr class="dark:text-gray-200">
                    <td class="py-1 font-semibold text-black dark:text-gray-200">Standar Penilaian</td>
                    <td class="py-1 text-black dark:text-gray-200">:</td>
                    <td class="py-1 text-black dark:text-gray-200">
                        {{ $participant->positionFormation->template->name ?? '-' }}</td>
                </tr>
                <tr class="dark:text-gray-200">
                    <td class="py-1 font-semibold text-black dark:text-gray-200">Tanggal Tes</td>
                    <td class="py-1 text-black dark:text-gray-200">:</td>
                    <td class="py-1 text-black dark:text-gray-200">
                        {{ $participant->assessmentEvent->start_date?->format('d F Y') ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <!-- Table Section - DARK MODE READY -->
        <div class="px-6 pb-6 bg-white dark:bg-gray-800">
            <table class="min-w-full border-2 border-black text-sm">
                <thead>
                    <tr class="bg-gray-200 dark:bg-gray-700">
                        <th class="border-2 border-black px-3 py-3 font-bold text-center text-black dark:text-white w-10"
                            rowspan="2">
                            NO</th>
                        <th class="border-2 border-black px-3 py-3 font-bold text-center text-black dark:text-white w-48"
                            rowspan="2">
                            AKTIFITAS/KOMPETENSI</th>
                        <th class="border-2 border-black px-3 py-3 font-bold text-center text-black dark:text-white"
                            colspan="5" style="width: 250px;">RATING</th>
                        <th class="border-2 border-black px-3 py-3 font-bold text-center text-black dark:text-white"
                            rowspan="2">
                            Kesimpulan</th>
                    </tr>
                    <tr class="bg-gray-200 dark:bg-gray-700">
                        <th
                            class="border-2 border-black px-3 py-2 font-bold text-center text-black dark:text-white w-12">
                            1</th>
                        <th
                            class="border-2 border-black px-3 py-2 font-bold text-center text-black dark:text-white w-12">
                            2</th>
                        <th
                            class="border-2 border-black px-3 py-2 font-bold text-center text-black dark:text-white w-12">
                            3</th>
                        <th
                            class="border-2 border-black px-3 py-2 font-bold text-center text-black dark:text-white w-12">
                            4</th>
                        <th
                            class="border-2 border-black px-3 py-2 font-bold text-center text-black dark:text-white w-12">
                            5</th>
                    </tr>
                    <tr>
                        <td class="border-2 border-black px-3 py-2 font-bold bg-gray-100 dark:bg-gray-600 text-black dark:text-white"
                            colspan="8">
                            Aspek Kompetensi
                        </td>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($aspectsData as $aspect)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="border-2 border-black px-3 py-3 text-center text-black dark:text-white">
                                {{ $aspect['number'] }}</td>
                            <td class="border-2 border-black px-3 py-3 text-black dark:text-white">{{ $aspect['name'] }}
                            </td>
                            @php
                                $roundedIndividualRating = round($aspect['individual_rating']);
                                $roundedStandardRating = round($aspect['standard_rating']);
                            @endphp
                            @for ($i = 1; $i <= 5; $i++)
                                @php
                                    $isStandard = $i == $roundedStandardRating;
                                    $isIndividual = $i == $roundedIndividualRating;
                                @endphp
                                <td
                                    class="border-2 border-black px-3 py-3 text-center relative
                                    @if ($isIndividual) @if ($i == 1) bg-red-500 text-white font-bold text-lg
                                        @elseif($i == 2) bg-orange-500 text-white font-bold text-lg
                                        @elseif($i == 3) bg-amber-500 text-white font-bold text-lg
                                        @elseif($i == 4) bg-green-500 text-white font-bold text-lg
                                        @else bg-green-700 text-white font-bold text-lg @endif
@elseif ($isStandard)
bg-gray-500 dark:bg-gray-500 font-bold text-white
                                    @endif">
                                    @if ($isIndividual)
                                        √
                                    @endif
                                </td>
                            @endfor
                            <td class="border-2 border-black px-3 py-3 text-xs text-black dark:text-gray-200">
                                <strong>{{ $aspect['conclusion']['title'] }}</strong><br>
                                {{ $aspect['conclusion']['description'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Legend - DARK MODE READY -->
        <div class="px-6 pb-6 bg-white dark:bg-gray-800">
            <div class="text-sm font-bold mb-2 text-black dark:text-white">Note :</div>
            <div class="grid grid-cols-1 gap-2 text-sm text-black dark:text-gray-200">
                <div class="flex items-center gap-3">
                    <div
                        class="w-8 h-6 bg-gray-500 border-2 border-black flex items-center justify-center font-bold text-gray-100">
                        S
                    </div>
                    <span><em>Standar</em></span>
                </div>
                <div class="flex items-center gap-3">
                    <div
                        class="w-8 h-6 bg-red-500 border-2 border-black flex items-center justify-center font-bold text-white">
                        √</div>
                    <span><em>Rating 1 - Rendah</em></span>
                </div>
                <div class="flex items-center gap-3">
                    <div
                        class="w-8 h-6 bg-orange-500 border-2 border-black flex items-center justify-center font-bold text-white">
                        √</div>
                    <span><em>Rating 2 - Kurang</em></span>
                </div>
                <div class="flex items-center gap-3">
                    <div
                        class="w-8 h-6 bg-amber-500 border-2 border-black flex items-center justify-center font-bold text-white">
                        √</div>
                    <span><em>Rating 3 - Cukup</em></span>
                </div>
                <div class="flex items-center gap-3">
                    <div
                        class="w-8 h-6 bg-green-500 border-2 border-black flex items-center justify-center font-bold text-white">
                        √</div>
                    <span><em>Rating 4 - Baik</em></span>
                </div>
                <div class="flex items-center gap-3">
                    <div
                        class="w-8 h-6 bg-green-700 border-2 border-black flex items-center justify-center font-bold text-white">
                        √</div>
                    <span><em>Rating 5 - Baik Sekali</em></span>
                </div>
            </div>
        </div>
    </div>
</div>
