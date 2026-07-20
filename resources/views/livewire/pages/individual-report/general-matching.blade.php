<style>
    /* DARK MODE READY - Using CSS Variables */
    :root {
        --bg-card: #ffffff;
        --bg-header: #faf8f5;
        --bg-row-odd: #faf8f5;
        --bg-row-even: #ffffff;
        --bg-empty: #faf8f5;
        --text-primary: #171412;
        --text-secondary: #171412;
        --border-color: #f0ebe4;
        --gradient-low: #FF0000;
        --gradient-medium: #FFFF00;
        --gradient-high: #00cc00;
        --yellow-light: #fef3c7;
        --yellow-dark: #b45309;
        --green-success: #15803d;
        --red-danger: #dc2626;
    }

    [data-theme="dark"],
    .dark {
        --bg-card: #171412;
        --bg-header: #1f1b18;
        --bg-row-odd: #1f1b18;
        --bg-row-even: #171412;
        --bg-empty: #1f1b18;
        --text-primary: #f5f4f3;
        --text-secondary: #a8a29e;
        --border-color: #25211e;
        --gradient-low: #FF0000;
        --gradient-medium: #FFFF00;
        --gradient-high: #00cc00;
        --yellow-light: #78350f;
        --yellow-dark: #fef3c7;
        --green-success: #22c55e;
        --red-danger: #f87171;
    }

    /* Table Structure */
    .range-scale {
        width: 8%;
    }

    .col-number {
        width: 3%;
    }

    .progress-container {
        position: relative;
        width: 100%;
        height: 1.5rem;
    }

    /* Gradient Bar - DARK MODE READY */
    .gradient-bar-low {
        background: linear-gradient(to right, var(--gradient-low), var(--gradient-medium));
    }

    .gradient-bar-medium {
        background: linear-gradient(to right, var(--gradient-low), var(--gradient-medium), var(--gradient-medium));
    }

    .gradient-bar-high {
        background: linear-gradient(to right, var(--gradient-low), var(--gradient-medium), var(--gradient-high));
    }

    /* Rating Cells - DARK MODE READY */
    .rating-cell-1 {
        background: linear-gradient(90deg, #ff0000, #ff6600);
        color: black;
    }

    .rating-cell-2 {
        background: linear-gradient(90deg, #ff6600, #ffcc00);
        color: black;
    }

    .rating-cell-3 {
        background: linear-gradient(90deg, #ffcc00, #ccff33);
        color: black;
    }

    .rating-cell-4 {
        background: linear-gradient(90deg, #ccff33, #33cc33);
        color: black;
    }

    .rating-cell-5 {
        background: linear-gradient(90deg, #33cc33, #00cc00);
        color: black;
    }

    .rating-cell-empty {
        background-color: var(--bg-empty);
        color: var(--text-primary);
    }

    /* Standard Rating Cells - DARK MODE READY */
    .rating-cell-standard {
        background: #797979;
        color: var(--yellow-dark);
    }

    .dark .rating-cell-standard {
        background: #6b7280;
        color: #f5f4f3;
    }

    /* Cell penuh untuk individual rating */
    .rating-cell-individual {
        font-weight: bold;
        color: white;
    }

    .rating-cell-individual.below-standard {
        background: var(--gradient-low);
    }

    .rating-cell-individual.above-standard {
        background: var(--gradient-high);
    }

    /* Rating Display */
    .rating-display {
        line-height: 1.1;
    }

    .rating-display .percentage {
        font-size: 0.75rem;
        font-weight: bold;
        color: var(--text-primary);
    }

    .rating-display .rating-comparison {
        font-size: 0.6875rem;
        font-weight: bold;
    }

    .rating-display .rating-comparison.above-standard {
        color: var(--green-success);
    }

    .rating-display .rating-comparison.below-standard {
        color: var(--red-danger);
    }
</style>

<div
    class="mx-auto my-8 border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] overflow-hidden rounded-md shadow-xs max-w-7xl">
    <!-- Header - DARK MODE READY -->
    @if ($showHeader)
        <div class="px-8 py-6 bg-white dark:bg-[#171412] border-b border-warm-border dark:border-[#25211e]">
            <h1 class="font-display text-2xl font-bold tracking-tight text-primary-ink dark:text-neutral-100 uppercase text-center">
                General Matching
            </h1>
        </div>
    @endif

    <!-- Info Section - DARK MODE READY -->
    @if ($showInfoSection)
        <div class="grid grid-cols-2 border-b border-warm-border dark:border-[#25211e] bg-warm-ivory dark:bg-[#1f1b18]">
            <!-- Left Column -->
            <div class="border-r border-warm-border dark:border-[#25211e]/40">
                <div class="grid grid-cols-3 border-b border-warm-border dark:border-[#25211e]/40">
                    <div class="px-4 py-2 text-sm text-primary-ink dark:text-neutral-200">Nomor Tes</div>
                    <div class="px-4 py-2 text-sm col-span-2 text-primary-ink dark:text-neutral-200">:
                        {{ $participant->test_number }}
                    </div>
                </div>
                <div class="grid grid-cols-3 border-b border-warm-border dark:border-[#25211e]/40">
                    <div class="px-4 py-2 text-sm text-primary-ink dark:text-neutral-200">Nomor SKB</div>
                    <div class="px-4 py-2 text-sm col-span-2 text-primary-ink dark:text-neutral-200">:
                        {{ $participant->skb_number }}
                    </div>
                </div>
                <div class="grid grid-cols-3 border-b border-warm-border dark:border-[#25211e]/40">
                    <div class="px-4 py-2 text-sm text-primary-ink dark:text-neutral-200">Nama</div>
                    <div class="px-4 py-2 text-sm col-span-2 text-primary-ink dark:text-neutral-200">: {{ $participant->name }}
                    </div>
                </div>
                <div class="grid grid-cols-3 border-b border-warm-border dark:border-[#25211e]/40">
                    <div class="px-4 py-2 text-sm text-primary-ink dark:text-neutral-200">Formasi Jabatan</div>
                    <div class="px-4 py-2 text-sm col-span-2 text-primary-ink dark:text-neutral-200">:
                        {{ $participant->positionFormation->name }}
                    </div>
                </div>
                <div class="grid grid-cols-3">
                    <div class="px-4 py-2 text-sm text-primary-ink dark:text-neutral-200">Tanggal Tes</div>
                    <div class="px-4 py-2 text-sm col-span-2 text-primary-ink dark:text-neutral-200">:
                        {{ \Carbon\Carbon::parse($participant->assessment_date)->translatedFormat('d F Y') }}
                    </div>
                </div>
            </div>

            <!-- Right Column - JOB PERSON MATCH -->
            <div class="flex flex-col">
                <div class="grid grid-cols-2 border-b border-warm-border dark:border-[#25211e]/40">
                    <div class="px-4 py-2 text-sm text-primary-ink dark:text-neutral-200">Standar Penilaian</div>
                    <div class="px-4 py-2 text-sm text-primary-ink dark:text-neutral-200">
                        : {{ $participant->positionFormation->template->name }}
                    </div>
                </div>
                <div class="grid grid-cols-2 border-b border-warm-border dark:border-[#25211e]/40">
                    <div class="px-4 py-2 text-sm text-primary-ink dark:text-neutral-200">Kegiatan</div>
                    <div class="px-4 py-2 text-sm text-primary-ink dark:text-neutral-200">:
                        {{ $participant->assessmentEvent->name }}
                    </div>
                </div>

                {{-- Adjustment Indicators --}}
                <div class="px-4 py-2 border-b border-warm-border dark:border-[#25211e]/40 flex flex-wrap gap-2">
                    <x-adjustment-indicator :template-id="$participant->positionFormation->template_id"
                        category-code="potensi" size="sm" custom-label="Standar Potensi Disesuaikan" />
                    <x-adjustment-indicator :template-id="$participant->positionFormation->template_id"
                        category-code="kompetensi" size="sm" custom-label="Standar Kompetensi Disesuaikan" />
                </div>

                <div
                    class="px-4 py-2 text-center font-bold text-sm border-b border-warm-border dark:border-[#25211e]/40 text-primary-ink dark:text-neutral-100">
                    JOB PERSON MATCH
                </div>
                <div class="flex-grow flex items-center px-4 py-2">
                    <div class="w-full h-8 relative bg-warm-border/60 dark:bg-[#25211e] rounded overflow-hidden">
                        <div class="h-full rounded {{ $jobMatchPercentage <= 40 ? 'gradient-bar-low' : ($jobMatchPercentage <= 70 ? 'gradient-bar-medium' : 'gradient-bar-high') }}"
                            style="width: {{ $jobMatchPercentage }}%;"></div>
                        <div class="absolute right-0 top-0 bottom-0 flex items-center pr-2">
                            <span class="text-sm font-bold text-primary-ink dark:text-neutral-100">{{ $jobMatchPercentage }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Table - DARK MODE READY -->
    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200">
                    <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center text-sm font-bold text-primary-ink dark:text-neutral-100 col-number"
                        rowspan="3">NO.</th>
                    <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center text-sm font-bold text-primary-ink dark:text-neutral-100"
                        rowspan="3">ASPEK</th>
                    <th class="border border-warm-border dark:border-[#25211e] px-2 py-2 text-center text-sm font-bold text-primary-ink dark:text-neutral-100"
                        rowspan="3">
                        STANDAR</th>
                    <th class="border border-warm-border dark:border-[#25211e] px-2 py-2 text-center text-xs font-bold text-primary-ink dark:text-neutral-100 range-scale"
                        colspan="5">RATING</th>
                </tr>
                <tr class="bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200">
                    <th
                        class="border border-warm-border dark:border-[#25211e] px-2 py-1 text-center text-xs text-primary-ink dark:text-neutral-100 ">
                        1</th>
                    <th
                        class="border border-warm-border dark:border-[#25211e] px-2 py-1 text-center text-xs text-primary-ink dark:text-neutral-100 ">
                        2</th>
                    <th
                        class="border border-warm-border dark:border-[#25211e] px-2 py-1 text-center text-xs text-primary-ink dark:text-neutral-100 ">
                        3</th>
                    <th
                        class="border border-warm-border dark:border-[#25211e] px-2 py-1 text-center text-xs text-primary-ink dark:text-neutral-100 ">
                        4</th>
                    <th
                        class="border border-warm-border dark:border-[#25211e] px-2 py-1 text-center text-xs text-primary-ink dark:text-neutral-100 ">
                        5</th>
                </tr>
                <tr class="bg-warm-ivory/50 dark:bg-[#1f1b18]/50 text-primary-ink dark:text-neutral-200">
                    <th
                        class="border border-warm-border dark:border-[#25211e] px-2 py-1 text-center text-xs text-white range-scale rating-cell-1">
                        Rendah</th>
                    <th
                        class="border border-warm-border dark:border-[#25211e] px-2 py-1 text-center text-xs text-white range-scale rating-cell-2">
                        Kurang</th>
                    <th
                        class="border border-warm-border dark:border-[#25211e] px-2 py-1 text-center text-xs text-white range-scale rating-cell-3">
                        Cukup</th>
                    <th
                        class="border border-warm-border dark:border-[#25211e] px-2 py-1 text-center text-xs text-white range-scale rating-cell-4">
                        Baik</th>
                    <th
                        class="border border-warm-border dark:border-[#25211e] px-2 py-1 text-center text-xs text-white range-scale rating-cell-5">
                        Baik Sekali</th>
                </tr>
            </thead>
            <tbody>
                <!-- ASPEK PSIKOLOGI (POTENSI) -->
                @if ($showPotensi && $potensiCategory && count($potensiAspects) > 0)
                    <tr class="bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200">
                        <td class="border border-warm-border dark:border-[#25211e]/40 px-4 py-2 font-bold text-sm text-primary-ink dark:text-neutral-100 uppercase"
                            colspan="8">
                            {{ $potensiCategory->name }}
                        </td>
                    </tr>

                    @foreach ($potensiAspects as $index => $aspect)
                        <!-- Aspect Header with Progress Bar -->
                        <tr class="bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200">
                            <td
                                class="border border-warm-border dark:border-[#25211e]/40 px-4 py-2 text-sm font-bold text-primary-ink dark:text-neutral-100 text-center">
                                {{ $loop->iteration }}.
                            </td>
                            <td
                                class="border border-warm-border dark:border-[#25211e]/40 px-4 py-2 text-sm font-bold text-primary-ink dark:text-neutral-100">
                                {{ $aspect['name'] }}
                            </td>
                            <td
                                class="border border-warm-border dark:border-[#25211e]/40 px-2 py-2 text-sm font-bold text-primary-ink dark:text-neutral-100 text-center">
                                {{ number_format($aspect['standard_rating'], 2, ',', '.') }}
                            </td>
                            <td class="border border-warm-border dark:border-[#25211e]/40 range-scale" colspan="5">
                                <div class="progress-container">
                                    <div class="h-full rounded {{ $aspect['percentage'] <= 40 ? 'gradient-bar-low' : ($aspect['percentage'] <= 70 ? 'gradient-bar-medium' : 'gradient-bar-high') }}"
                                        style="width: {{ $aspect['percentage'] }}%;"></div>
                                    <div class="absolute right-0 top-0 bottom-0 flex items-center pr-2">
                                        <div class="rating-display text-right">
                                            <div class="percentage">{{ $aspect['percentage'] }}%</div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <!-- Sub-Aspects -->
                        @foreach ($aspect['sub_aspects'] as $subIndex => $subAspect)
                            <tr class="bg-warm-ivory dark:bg-[#1f1b18]">
                                <td
                                    class="border border-warm-border dark:border-[#25211e]/40 px-4 py-1 text-xs text-primary-ink dark:text-neutral-200 col-number text-center">
                                    {{ $subIndex + 1 }}.
                                </td>
                                <td
                                    class="border border-warm-border dark:border-[#25211e]/40 px-4 py-1 text-xs text-primary-ink dark:text-neutral-200">
                                    {{ $subAspect['name'] }}
                                </td>
                                <td
                                    class="border border-warm-border dark:border-[#25211e]/40 px-2 py-1 text-xs text-primary-ink dark:text-neutral-200 text-center">
                                    {{ $subAspect['standard_rating'] }}
                                </td>
                                @for ($i = 1; $i <= 5; $i++)
                                    @php
                                        $isStandard = $subAspect['standard_rating'] == $i;
                                        $isIndividual = $subAspect['individual_rating'] == $i;
                                        $isAboveStandard = $subAspect['individual_rating'] >= $subAspect['standard_rating'];

                                        $cellClass = '';
                                        if ($isIndividual) {
                                            $cellClass = 'rating-cell-individual ' . ($isAboveStandard ? 'above-standard' : 'below-standard');
                                        } elseif ($isStandard) {
                                            $cellClass = 'rating-cell-standard';
                                        } else {
                                            $cellClass = 'rating-cell-empty text-primary-ink dark:text-neutral-200';
                                        }
                                    @endphp
                                    <td class="border border-warm-border dark:border-[#25211e]/40 range-scale text-center {{ $cellClass }}">
                                        @if ($isIndividual)
                                            {{ $isAboveStandard ? '✓' : '✗' }}
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @endforeach
                    @endforeach
                @endif

                <!-- ASPEK KOMPETENSI -->
                @if ($showKompetensi && $kompetensiCategory && count($kompetensiAspects) > 0)
                    <tr class="bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200">
                        <td class="border border-warm-border dark:border-[#25211e]/40 px-4 py-2 font-bold text-sm text-primary-ink dark:text-neutral-100 uppercase"
                            colspan="8">
                            {{ $kompetensiCategory->name }}
                        </td>
                    </tr>

                    @foreach ($kompetensiAspects as $index => $aspect)
                        <!-- Aspect Header with Progress Bar -->
                        <tr class="bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200">
                            <td
                                class="border border-warm-border dark:border-[#25211e]/40 px-4 py-2 text-sm font-bold text-primary-ink dark:text-neutral-100 text-center">
                                {{ $loop->iteration }}.
                            </td>
                            <td
                                class="border border-warm-border dark:border-[#25211e]/40 px-4 py-2 text-sm font-bold text-primary-ink dark:text-neutral-100">
                                {{ $aspect['name'] }}
                            </td>
                            <td
                                class="border border-warm-border dark:border-[#25211e]/40 px-2 py-2 text-sm font-bold text-primary-ink dark:text-neutral-100 text-center">
                                {{ number_format($aspect['standard_rating'], 2, ',', '.') }}
                            </td>
                            <td class="border border-warm-border dark:border-[#25211e]/40 range-scale" colspan="5">
                                <div class="progress-container">
                                    <div class="h-full rounded {{ $aspect['percentage'] <= 40 ? 'gradient-bar-low' : ($aspect['percentage'] <= 70 ? 'gradient-bar-medium' : 'gradient-bar-high') }}"
                                        style="width: {{ $aspect['percentage'] }}%;"></div>
                                    <div class="absolute right-0 top-0 bottom-0 flex items-center pr-2">
                                        <div class="rating-display text-right">
                                            <div class="percentage">{{ $aspect['percentage'] }}%</div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <!-- Kompetensi Description -->
                        @if (isset($aspect['description']) && $aspect['description'])
                            <tr class="bg-warm-ivory/50 dark:bg-[#1f1b18]">
                                <td
                                    class="border border-warm-border dark:border-[#25211e]/40 px-4 py-1 text-xs text-primary-ink dark:text-neutral-200 text-center">
                                    1.</td>
                                <td
                                    class="border border-warm-border dark:border-[#25211e]/40 px-4 py-1 text-xs text-primary-ink dark:text-neutral-200">
                                    {{ $aspect['description'] }}
                                </td>
                                <td
                                    class="border border-warm-border dark:border-[#25211e]/40 px-2 py-1 text-xs text-primary-ink dark:text-neutral-200 text-center">
                                    {{ $aspect['standard_rating'] }}
                                </td>
                                @for ($i = 1; $i <= 5; $i++)
                                    @php
                                        $isStandard = $aspect['standard_rating'] == $i;
                                        $isIndividual = round($aspect['individual_rating']) == $i;
                                        $isAboveStandard = round($aspect['individual_rating']) >= $aspect['standard_rating'];

                                        $cellClass = '';
                                        if ($isIndividual) {
                                            $cellClass = 'rating-cell-individual ' . ($isAboveStandard ? 'above-standard' : 'below-standard');
                                        } elseif ($isStandard) {
                                            $cellClass = 'rating-cell-standard';
                                        } else {
                                            $cellClass = 'rating-cell-empty text-primary-ink dark:text-neutral-200';
                                        }
                                    @endphp
                                    <td class="border border-warm-border dark:border-[#25211e]/40 range-scale text-center {{ $cellClass }}">
                                        @if ($isIndividual)
                                            {{ $isAboveStandard ? '✓' : '✗' }}
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @else
                            <tr class="bg-warm-ivory/50 dark:bg-[#1f1b18]">
                                <td
                                    class="border border-warm-border dark:border-[#25211e]/40 px-4 py-1 text-xs text-primary-ink dark:text-neutral-200 text-center">
                                    1.</td>
                                <td
                                    class="border border-warm-border dark:border-[#25211e]/40 px-4 py-1 text-xs text-primary-ink dark:text-neutral-200">
                                    Rating Level</td>
                                <td
                                    class="border border-warm-border dark:border-[#25211e]/40 px-2 py-1 text-xs text-primary-ink dark:text-neutral-200 text-center">
                                    {{ $aspect['standard_rating'] }}
                                </td>
                                @for ($i = 1; $i <= 5; $i++)
                                    @php
                                        $isStandard = $aspect['standard_rating'] == $i;
                                        $isIndividual = round($aspect['individual_rating']) == $i;
                                        $isAboveStandard = round($aspect['individual_rating']) >= $aspect['standard_rating'];

                                        $cellClass = '';
                                        if ($isIndividual) {
                                            $cellClass = 'rating-cell-individual ' . ($isAboveStandard ? 'above-standard' : 'below-standard');
                                        } elseif ($isStandard) {
                                            $cellClass = 'rating-cell-standard';
                                        } else {
                                            $cellClass = 'rating-cell-empty text-primary-ink dark:text-neutral-200';
                                        }
                                    @endphp
                                    <td class="border border-warm-border dark:border-[#25211e]/40 range-scale text-center {{ $cellClass }}">
                                        @if ($isIndividual)
                                            {{ $isAboveStandard ? '✓' : '✗' }}
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @endif
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    {{-- <div class="border-t-2 border-gray-500 dark:border-gray-600 my-4"></div> --}}

    <!-- Legend/Keterangan -->
    <div class="mt-4 px-4 pb-4">
        <div class="text-sm font-bold text-primary-ink dark:text-neutral-100 mb-2">Keterangan:</div>
        <div class="flex flex-wrap gap-4">
            <!-- Abu-abu: Standar -->
            <div class="flex items-center gap-2">
                <div class="w-8 h-6 rating-cell-standard border border-warm-border dark:border-[#25211e]/40 rounded"></div>
                <span class="text-xs text-primary-ink dark:text-neutral-200">Standar</span>
            </div>

            <!-- Hijau: Sesuai/Diatas Standar -->
            <div class="flex items-center gap-2">
                <div
                    class="w-8 h-6 rating-cell-individual above-standard border border-warm-border dark:border-[#25211e]/40 rounded flex items-center justify-center text-white font-bold">
                    ✓</div>
                <span class="text-xs text-primary-ink dark:text-neutral-200">Sesuai atau Diatas Standar</span>
            </div>

            <!-- Merah: Tidak Sesuai Standar -->
            <div class="flex items-center gap-2">
                <div
                    class="w-8 h-6 rating-cell-individual below-standard border border-warm-border dark:border-[#25211e]/40 rounded flex items-center justify-center text-white font-bold">
                    ✗</div>
                <span class="text-xs text-primary-ink dark:text-neutral-200">Tidak Sesuai Standar</span>
            </div>
        </div>
    </div>
</div>