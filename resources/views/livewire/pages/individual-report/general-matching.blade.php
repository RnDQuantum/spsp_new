<style>
    /* DARK MODE READY - Using CSS Variables */
    :root {
        --bg-card: #ffffff;
        --bg-header: #dbeafe;
        --bg-row-odd: #f3f4f6;
        --bg-row-even: #f9fafb;
        --bg-empty: #e5e7eb;
        --text-primary: #000000;
        --text-secondary: #374151;
        --border-color: #d1d5db;
        --gradient-low: #ef4444;
        --gradient-medium: #f59e0b;
        --gradient-high: #10b981;
        --yellow-light: #fef3c7;
        --yellow-dark: #92400e;
        --green-success: #15803d;
        --red-danger: #dc2626;
    }

    [data-theme="dark"],
    .dark {
        --bg-card: #1f2937;
        --bg-header: #1e40af;
        --bg-row-odd: #374151;
        --bg-row-even: #4b5563;
        --bg-empty: #6b7280;
        --text-primary: #f9fafb;
        --text-secondary: #d1d5db;
        --border-color: #4b5563;
        --gradient-low: #dc2626;
        --gradient-medium: #d97706;
        --gradient-high: #059669;
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
    .gradient-bar {
        background: linear-gradient(to right, var(--gradient-low), var(--gradient-medium), var(--gradient-high));
        height: 100%;
        border-radius: 0.25rem;
        position: relative;
    }

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
        background: linear-gradient(135deg, var(--gradient-low), #b91c1c);
        color: white;
    }

    .rating-cell-2 {
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: white;
    }

    .rating-cell-3 {
        background: linear-gradient(135deg, var(--gradient-medium), #b45309);
        color: white;
    }

    .rating-cell-4 {
        background: linear-gradient(135deg, var(--gradient-high), #047857);
        color: white;
    }

    .rating-cell-5 {
        background: linear-gradient(135deg, #047857, #065f46);
        color: white;
    }

    .rating-cell-empty {
        background-color: var(--bg-empty);
        color: var(--text-primary);
    }

    /* Standard Rating Cells - DARK MODE READY */
    .rating-cell-standard-1,
    .rating-cell-standard-2,
    .rating-cell-standard-3,
    .rating-cell-standard-4,
    .rating-cell-standard-5 {
        background: linear-gradient(135deg, var(--yellow-light), #fde047);
        color: var(--yellow-dark);
    }

    /* Rating X Markers */
    .rating-x {
        font-weight: bold;
        padding: 0.125rem 0.25rem;
        border-radius: 0.125rem;
        display: inline-block;
    }

    .rating-x.below-standard {
        background-color: var(--gradient-low);
        color: white;
    }

    .rating-x.above-standard {
        background-color: var(--gradient-high);
        color: white;
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

<div class="bg-white dark:bg-gray-900 rounded-lg shadow-md overflow-hidden max-w-7xl mx-auto my-8">
    <!-- Header - DARK MODE READY -->
    <div class="border-b-4 border-black dark:border-gray-300 py-3 bg-blue-50 dark:bg-gray-800">
        <h1 class="text-center text-lg font-bold uppercase tracking-wide text-black dark:text-white">
            GENERAL MATCHING - ASPEK PSIKOLOGI
        </h1>
    </div>

    <!-- Info Section - DARK MODE READY -->
    <div class="grid grid-cols-2 border-b border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800">
        <!-- Left Column -->
        <div class="border-r border-gray-300 dark:border-gray-600">
            <div class="grid grid-cols-3 border-b border-gray-300 dark:border-gray-600">
                <div class="px-4 py-2 text-sm text-black dark:text-gray-200">Nomor Tes</div>
                <div class="px-4 py-2 text-sm col-span-2 text-black dark:text-gray-200">: {{ $participant->test_number
                    }}
                </div>
            </div>
            <div class="grid grid-cols-3 border-b border-gray-300 dark:border-gray-600">
                <div class="px-4 py-2 text-sm text-black dark:text-gray-200">Nomor SKB</div>
                <div class="px-4 py-2 text-sm col-span-2 text-black dark:text-gray-200">: {{ $participant->skb_number }}
                </div>
            </div>
            <div class="grid grid-cols-3 border-b border-gray-300 dark:border-gray-600">
                <div class="px-4 py-2 text-sm text-black dark:text-gray-200">Nama</div>
                <div class="px-4 py-2 text-sm col-span-2 text-black dark:text-gray-200">: {{ $participant->name }}</div>
            </div>
            <div class="grid grid-cols-3 border-b border-gray-300 dark:border-gray-600">
                <div class="px-4 py-2 text-sm text-black dark:text-gray-200">Formasi Jabatan</div>
                <div class="px-4 py-2 text-sm col-span-2 text-black dark:text-gray-200">:
                    {{ $participant->positionFormation->name }}</div>
            </div>
            <div class="grid grid-cols-3">
                <div class="px-4 py-2 text-sm text-black dark:text-gray-200">Tanggal Tes</div>
                <div class="px-4 py-2 text-sm col-span-2 text-black dark:text-gray-200">:
                    {{ $participant->assessment_date->format('d F Y') }}</div>
            </div>
        </div>

        <!-- Right Column - JOB PERSON MATCH -->
        <div class="flex flex-col">
            <div class="grid grid-cols-2 border-b border-gray-300 dark:border-gray-600">
                <div class="px-4 py-2 text-sm text-black dark:text-gray-200">Standar/Standard</div>
                <div class="px-4 py-2 text-sm text-black dark:text-gray-200">:
                    {{ $participant->positionFormation->template->name }}</div>
            </div>
            <div class="grid grid-cols-2 border-b border-gray-300 dark:border-gray-600">
                <div class="px-4 py-2 text-sm text-black dark:text-gray-200">Event</div>
                <div class="px-4 py-2 text-sm text-black dark:text-gray-200">: {{ $participant->assessmentEvent->name }}
                </div>
            </div>
            <div
                class="px-4 py-2 text-center font-bold text-sm border-b border-gray-300 dark:border-gray-600 text-black dark:text-white">
                JOB PERSON MATCH
            </div>
            <div class="flex-grow flex items-center px-4 py-2">
                <div class="w-full h-8 relative">
                    <div class="h-full rounded {{ $jobMatchPercentage <= 40 ? 'gradient-bar-low' : ($jobMatchPercentage <= 70 ? 'gradient-bar-medium' : 'gradient-bar-high') }}"
                        style="width: {{ $jobMatchPercentage }}%;"></div>
                    <div class="absolute right-0 top-0 bottom-0 flex items-center pr-2">
                        <span class="text-sm font-bold text-black dark:text-white">{{ $jobMatchPercentage }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table - DARK MODE READY -->
    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-blue-100 dark:bg-gray-700">
                    <th class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-center text-sm font-bold text-black dark:text-white col-number"
                        rowspan="2">NO.</th>
                    <th class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-center text-sm font-bold text-black dark:text-white"
                        rowspan="2">ASPEK</th>
                    <th
                        class="border border-gray-300 dark:border-gray-600 px-2 py-2 text-center text-sm font-bold text-black dark:text-white">
                        STANDARD</th>
                    <th class="border border-gray-300 dark:border-gray-600 px-2 py-2 text-center text-xs font-bold text-black dark:text-white range-scale"
                        colspan="5">RATING</th>
                </tr>
                <tr class="bg-blue-100 dark:bg-gray-700">
                    <th
                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-xs rating-cell-standard-1">
                    </th>
                    <th
                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-xs text-white range-scale rating-cell-1">
                        1 Rendah</th>
                    <th
                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-xs text-white range-scale rating-cell-2">
                        2 Kurang</th>
                    <th
                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-xs text-white range-scale rating-cell-3">
                        3 Cukup</th>
                    <th
                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-xs text-white range-scale rating-cell-4">
                        4 Baik</th>
                    <th
                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-center text-xs text-white range-scale rating-cell-5">
                        5 Baik Sekali</th>
                </tr>
            </thead>
            <tbody>
                <!-- ASPEK PSIKOLOGI (POTENSI) -->
                @if ($potensiCategory && count($potensiAspects) > 0)
                <tr class="bg-gray-100 dark:bg-gray-600">
                    <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 font-bold text-sm text-black dark:text-white uppercase"
                        colspan="8">
                        {{ $potensiCategory->name }}
                    </td>
                </tr>

                @foreach ($potensiAspects as $index => $aspect)
                <!-- Aspect Header with Progress Bar -->
                <tr class="bg-gray-100 dark:bg-gray-600">
                    <td
                        class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-bold text-black dark:text-white text-center">
                        {{ $loop->iteration }}.</td>
                    <td
                        class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-bold text-black dark:text-white">
                        {{ $aspect['name'] }}</td>
                    <td
                        class="border border-gray-300 dark:border-gray-600 px-2 py-2 text-sm font-bold text-black dark:text-white text-center">
                        {{ number_format($aspect['standard_rating'], 2, ',', '.') }}
                    </td>
                    <td class="border border-gray-300 dark:border-gray-600 range-scale" colspan="5">
                        <div class="progress-container">
                            <div class="h-full rounded {{ $aspect['percentage'] <= 40 ? 'gradient-bar-low' : ($aspect['percentage'] <= 70 ? 'gradient-bar-medium' : 'gradient-bar-high') }}"
                                style="width: {{ $aspect['percentage'] }}%;"></div>
                            <div class="absolute right-0 top-0 bottom-0 flex items-center pr-2">
                                <div class="rating-display text-right">
                                    <div class="percentage">{{ $aspect['percentage'] }}%</div>
                                    <div
                                        class="rating-comparison {{ $aspect['individual_rating'] >= $aspect['standard_rating'] ? 'above-standard' : 'below-standard' }}">
                                        {{ number_format($aspect['individual_rating'], 2, ',', '.') }} /
                                        {{ number_format($aspect['standard_rating'], 2, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>

                <!-- Sub-Aspects -->
                @foreach ($aspect['sub_aspects'] as $subIndex => $subAspect)
                <tr class="bg-gray-50 dark:bg-gray-700">
                    <td
                        class="border border-gray-300 dark:border-gray-600 px-4 py-1 text-xs text-black dark:text-gray-200 col-number text-center">
                        {{ $subIndex + 1 }}.</td>
                    <td
                        class="border border-gray-300 dark:border-gray-600 px-4 py-1 text-xs text-black dark:text-gray-200">
                        {{ $subAspect['name'] }}</td>
                    <td
                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-xs text-black dark:text-gray-200 text-center">
                        {{ $subAspect['standard_rating'] }}</td>
                    @for ($i = 1; $i <= 5; $i++) <td
                        class="border border-gray-300 dark:border-gray-600 range-scale text-center {{ $subAspect['standard_rating'] == $i ? 'rating-cell-standard-' . $i : 'rating-cell-empty text-black dark:text-gray-200' }}">
                        @if ($subAspect['individual_rating'] == $i)
                        <span
                            class="rating-x {{ $subAspect['individual_rating'] >= $subAspect['standard_rating'] ? 'above-standard' : 'below-standard' }}">X</span>
                        @endif
                        </td>
                        @endfor
                </tr>
                @endforeach
                @endforeach
                @endif

                <!-- ASPEK KOMPETENSI -->
                @if ($kompetensiCategory && count($kompetensiAspects) > 0)
                <tr class="bg-gray-100 dark:bg-gray-600">
                    <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 font-bold text-sm text-black dark:text-white uppercase"
                        colspan="8">
                        {{ $kompetensiCategory->name }}
                    </td>
                </tr>

                @foreach ($kompetensiAspects as $index => $aspect)
                <!-- Aspect Header with Progress Bar -->
                <tr class="bg-gray-100 dark:bg-gray-600">
                    <td
                        class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-bold text-black dark:text-white text-center">
                        {{ $loop->iteration }}.</td>
                    <td
                        class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-bold text-black dark:text-white">
                        {{ $aspect['name'] }}</td>
                    <td
                        class="border border-gray-300 dark:border-gray-600 px-2 py-2 text-sm font-bold text-black dark:text-white text-center">
                        {{ number_format($aspect['standard_rating'], 2, ',', '.') }}
                    </td>
                    <td class="border border-gray-300 dark:border-gray-600 range-scale" colspan="5">
                        <div class="progress-container">
                            <div class="h-full rounded {{ $aspect['percentage'] <= 40 ? 'gradient-bar-low' : ($aspect['percentage'] <= 70 ? 'gradient-bar-medium' : 'gradient-bar-high') }}"
                                style="width: {{ $aspect['percentage'] }}%;"></div>
                            <div class="absolute right-0 top-0 bottom-0 flex items-center pr-2">
                                <div class="rating-display text-right">
                                    <div class="percentage">{{ $aspect['percentage'] }}%</div>
                                    <div
                                        class="rating-comparison {{ $aspect['individual_rating'] >= $aspect['standard_rating'] ? 'above-standard' : 'below-standard' }}">
                                        {{ number_format($aspect['individual_rating'], 2, ',', '.') }} /
                                        {{ number_format($aspect['standard_rating'], 2, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>

                <!-- Kompetensi Description -->
                @if (isset($aspect['description']) && $aspect['description'])
                <tr class="bg-gray-50 dark:bg-gray-700">
                    <td
                        class="border border-gray-300 dark:border-gray-600 px-4 py-1 text-xs text-black dark:text-gray-200 text-center">
                        1.</td>
                    <td
                        class="border border-gray-300 dark:border-gray-600 px-4 py-1 text-xs text-black dark:text-gray-200">
                        {{ $aspect['description'] }}</td>
                    <td
                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-xs text-black dark:text-gray-200 text-center">
                        {{ $aspect['standard_rating'] }}</td>
                    @for ($i = 1; $i <= 5; $i++) <td
                        class="border border-gray-300 dark:border-gray-600 range-scale text-center {{ $aspect['standard_rating'] == $i ? 'rating-cell-standard-' . $i : 'rating-cell-empty text-black dark:text-gray-200' }}">
                        @if (round($aspect['individual_rating']) == $i)
                        <span
                            class="rating-x {{ round($aspect['individual_rating']) >= $aspect['standard_rating'] ? 'above-standard' : 'below-standard' }}">X</span>
                        @endif
                        </td>
                        @endfor
                </tr>
                @else
                <tr class="bg-gray-50 dark:bg-gray-700">
                    <td
                        class="border border-gray-300 dark:border-gray-600 px-4 py-1 text-xs text-black dark:text-gray-200 text-center">
                        1.</td>
                    <td
                        class="border border-gray-300 dark:border-gray-600 px-4 py-1 text-xs text-black dark:text-gray-200">
                        Rating Level</td>
                    <td
                        class="border border-gray-300 dark:border-gray-600 px-2 py-1 text-xs text-black dark:text-gray-200 text-center">
                        {{ $aspect['standard_rating'] }}</td>
                    @for ($i = 1; $i <= 5; $i++) <td
                        class="border border-gray-300 dark:border-gray-600 range-scale text-center {{ $aspect['standard_rating'] == $i ? 'rating-cell-standard-' . $i : 'rating-cell-empty text-black dark:text-gray-200' }}">
                        @if (round($aspect['individual_rating']) == $i)
                        <span
                            class="rating-x {{ round($aspect['individual_rating']) >= $aspect['standard_rating'] ? 'above-standard' : 'below-standard' }}">X</span>
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
</div>