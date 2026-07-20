<div>
    <div class="mx-auto my-8 border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] overflow-hidden shadow-xs rounded-lg max-w-6xl font-sans">

        <div class="px-8 py-6 bg-white dark:bg-[#171412] border-b border-warm-border dark:border-[#25211e]">
            <h1 class="font-display text-2xl font-bold tracking-tight text-primary-ink dark:text-neutral-100 uppercase text-center">
                Ringkasan Kompetensi Manajerial
            </h1>
        </div>

        <!-- Info Section - DARK MODE READY -->
        <div class="p-6 bg-warm-ivory dark:bg-[#1f1b18] border-b border-warm-border dark:border-[#25211e]">
            <table class="w-full text-sm">
                <tr>
                    <td class="py-1 font-semibold text-primary-ink dark:text-neutral-200 w-36">Nomor Tes</td>
                    <td class="py-1 text-primary-ink dark:text-neutral-200 w-4">:</td>
                    <td class="py-1 font-mono-data text-primary-ink dark:text-neutral-200">{{ $participant->test_number }}</td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold text-primary-ink dark:text-neutral-200">NIP</td>
                    <td class="py-1 text-primary-ink dark:text-neutral-200">:</td>
                    <td class="py-1 font-mono-data text-primary-ink dark:text-neutral-200">{{ $participant->skb_number ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold text-primary-ink dark:text-neutral-200">Nama</td>
                    <td class="py-1 text-primary-ink dark:text-neutral-200">:</td>
                    <td class="py-1 font-semibold text-primary-ink dark:text-neutral-200">{{ $participant->name }}</td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold text-primary-ink dark:text-neutral-200">Jabatan Saat Ini</td>
                    <td class="py-1 text-primary-ink dark:text-neutral-200">:</td>
                    <td class="py-1 text-primary-ink dark:text-neutral-200">{{ $participant->positionFormation->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold text-primary-ink dark:text-neutral-200">Standar Penilaian</td>
                    <td class="py-1 text-primary-ink dark:text-neutral-200">:</td>
                    <td class="py-1 text-primary-ink dark:text-neutral-200">{{ $participant->positionFormation->template->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold text-primary-ink dark:text-neutral-200">Tanggal Tes</td>
                    <td class="py-1 text-primary-ink dark:text-neutral-200">:</td>
                    <td class="py-1 text-primary-ink dark:text-neutral-200">
                        {{ \Carbon\Carbon::parse($participant->assessment_date)->translatedFormat('d F Y') ?? ($participant->assessmentEvent->start_date?->format('d F Y') ?? '-') }}
                    </td>
                </tr>
            </table>

            {{-- Adjustment Indicator --}}
            <div class="mt-4">
                <x-adjustment-indicator
                    :template-id="$participant->positionFormation->template_id"
                    category-code="kompetensi"
                    size="sm"
                />
            </div>
        </div>

        <!-- Table Section - DARK MODE READY -->
        <div class="p-6 bg-white dark:bg-[#171412] overflow-x-auto">
            <table class="min-w-full border border-warm-border dark:border-[#25211e] text-sm">
                <thead>
                    <tr class="bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-100 font-bold">
                        <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-bold text-center w-12" rowspan="2">
                            No
                        </th>
                        <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-bold text-left w-56" rowspan="2">
                            Aktifitas / Kompetensi
                        </th>
                        <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-bold text-center" colspan="5" style="width: 250px;">
                            Rating
                        </th>
                        <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-bold text-center" rowspan="2">
                            Kesimpulan
                        </th>
                    </tr>
                    <tr class="bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-100 font-bold">
                        <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-bold text-center w-12 font-mono-data">1</th>
                        <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-bold text-center w-12 font-mono-data">2</th>
                        <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-bold text-center w-12 font-mono-data">3</th>
                        <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-bold text-center w-12 font-mono-data">4</th>
                        <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-bold text-center w-12 font-mono-data">5</th>
                    </tr>
                    <tr>
                        <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-bold text-left bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-100" colspan="8">
                            Aspek Kompetensi
                        </td>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($aspectsData as $aspect)
                        <tr class="hover:bg-warm-ivory/50 dark:hover:bg-[#1f1b18]/50 transition-colors duration-150 text-sm text-primary-ink dark:text-neutral-200">
                            <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-normal">
                                {{ $aspect['number'] }}
                            </td>
                            <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-left font-semibold text-primary-ink dark:text-neutral-100">
                                {{ $aspect['name'] }}
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
                                    class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center relative font-mono-data
                                    @if ($isIndividual)
                                        @if ($i == 1) bg-red-500 text-white font-bold text-base
                                        @elseif($i == 2) bg-orange-500 text-white font-bold text-base
                                        @elseif($i == 3) bg-amber-500 text-white font-bold text-base
                                        @elseif($i == 4) bg-green-500 text-white font-bold text-base
                                        @else bg-green-700 text-white font-bold text-base
                                        @endif
                                    @elseif ($isStandard)
                                        bg-gray-500 dark:bg-gray-600 font-bold text-white
                                    @endif">
                                    @if ($isIndividual)
                                        √
                                    @endif
                                </td>
                            @endfor
                            <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-xs text-primary-ink dark:text-neutral-200">
                                <strong class="text-primary-ink dark:text-neutral-100 font-bold">{{ $aspect['conclusion']['title'] }}</strong><br>
                                <span class="text-primary-ink/80 dark:text-neutral-300">{{ $aspect['conclusion']['description'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Legend - DARK MODE READY -->
        <div class="px-6 pb-6 bg-white dark:bg-[#171412]">
            <div class="text-sm font-bold mb-3 text-primary-ink dark:text-neutral-100">Catatan :</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 text-xs text-primary-ink dark:text-neutral-200">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-5 bg-gray-500 dark:bg-gray-600 rounded flex items-center justify-center font-bold text-white shadow-xs"></div>
                    <span class="font-medium">Standar</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-7 h-5 bg-red-500 rounded flex items-center justify-center font-bold text-white shadow-xs">√</div>
                    <span class="font-medium">Rating 1 - Rendah</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-7 h-5 bg-orange-500 rounded flex items-center justify-center font-bold text-white shadow-xs">√</div>
                    <span class="font-medium">Rating 2 - Kurang</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-7 h-5 bg-amber-500 rounded flex items-center justify-center font-bold text-white shadow-xs">√</div>
                    <span class="font-medium">Rating 3 - Cukup</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-7 h-5 bg-green-500 rounded flex items-center justify-center font-bold text-white shadow-xs">√</div>
                    <span class="font-medium">Rating 4 - Baik</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-7 h-5 bg-green-700 rounded flex items-center justify-center font-bold text-white shadow-xs">√</div>
                    <span class="font-medium">Rating 5 - Baik Sekali</span>
                </div>
            </div>
        </div>
    </div>
</div>
