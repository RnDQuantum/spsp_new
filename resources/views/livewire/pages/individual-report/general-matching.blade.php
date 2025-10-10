<style>
    /* Ensure equal width for Range Scale columns */
    .range-scale {
        width: 8%;
        /* Equal width for columns 1-5 (5 columns, 40% total, 8% each) */
    }

    .col-number {
        width: 3%;
    }

    /* Ensure progress bar spans the full Range Scale section */
    .progress-container {
        position: relative;
        width: 100%;
        /* Span all 5 columns (40% of table width) */
        height: 1.5rem;
        /* Match h-6 */
    }

    /* Gradient bar styling */
    .gradient-bar {
        background: linear-gradient(to right, #ef4444, #f59e0b, #10b981);
        height: 100%;
        border-radius: 0.25rem;
        position: relative;
    }

    /* Dynamic gradient bar based on percentage */
    .gradient-bar-low {
        background: linear-gradient(to right, #ef4444, #f97316);
    }

    .gradient-bar-medium {
        background: linear-gradient(to right, #ef4444, #f59e0b, #f97316);
    }

    .gradient-bar-high {
        background: linear-gradient(to right, #ef4444, #f59e0b, #10b981);
    }

    /* Rating cell styling with gradient backgrounds */
    .rating-cell-1 {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
    }

    .rating-cell-2 {
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: white;
    }

    .rating-cell-3 {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
    }

    .rating-cell-4 {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
    }

    .rating-cell-5 {
        background: linear-gradient(135deg, #059669, #047857);
        color: white;
    }

    .rating-cell-empty {
        background-color: #e5e7eb;
        position: relative;
    }

    /* Standard rating cell styling */
    .rating-cell-standard-1 {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        color: #92400e;
    }

    .rating-cell-standard-2 {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        color: #92400e;
    }

    .rating-cell-standard-3 {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        color: #92400e;
    }

    .rating-cell-standard-4 {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        color: #92400e;
    }

    .rating-cell-standard-5 {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        color: #92400e;
    }

    .rating-x {
        font-weight: bold;
        padding: 0.125rem 0.25rem;
        border-radius: 0.125rem;
        display: inline-block;
    }

    .rating-x.below-standard {
        background-color: #ef4444;
        color: white;
    }

    .rating-x.above-standard {
        background-color: #10b981;
        color: white;
    }
</style>
<div class="bg-white rounded-lg shadow-md overflow-hidden max-w-7xl mx-auto my-8">
    <!-- Header -->
    <div class="border-b-4 border-black py-3">
        <h1 class="text-center text-lg font-bold uppercase tracking-wide text-black">GENERAL MATCHING - ASPEK
            PSIKOLOGI</h1>
    </div>

    <!-- Info Section -->
    <div class="grid grid-cols-2 border-b border-gray-300">
        <!-- Left Column -->
        <div class="border-r border-gray-300">
            <div class="grid grid-cols-3 border-b border-gray-300">
                <div class="px-4 py-2 text-sm text-black">Nomor Tes</div>
                <div class="px-4 py-2 text-sm col-span-2 text-black">: {{ $participant->test_number }}</div>
            </div>
            <div class="grid grid-cols-3 border-b border-gray-300">
                <div class="px-4 py-2 text-sm text-black">Nomor SKB</div>
                <div class="px-4 py-2 text-sm col-span-2 text-black">: {{ $participant->skb_number }}</div>
            </div>
            <div class="grid grid-cols-3 border-b border-gray-300">
                <div class="px-4 py-2 text-sm text-black">Nama</div>
                <div class="px-4 py-2 text-sm col-span-2 text-black">: {{ $participant->name }}</div>
            </div>
            <div class="grid grid-cols-3 border-b border-gray-300">
                <div class="px-4 py-2 text-sm text-black">Formasi Jabatan</div>
                <div class="px-4 py-2 text-sm col-span-2 text-black">: {{ $participant->positionFormation->name }}
                </div>
            </div>
            <div class="grid grid-cols-3">
                <div class="px-4 py-2 text-sm text-black">Tanggal Tes</div>
                <div class="px-4 py-2 text-sm col-span-2 text-black">:
                    {{ $participant->assessment_date->format('d F Y') }}</div>
            </div>
        </div>

        <!-- Right Column - JOB PERSON MATCH -->
        <div class="flex flex-col">
            <div class="grid grid-cols-2 border-b border-gray-300">
                <div class="px-4 py-2 text-sm text-black">Standar/Standard</div>
                <div class="px-4 py-2 text-sm text-black">: {{ $participant->assessmentEvent->template->name }}</div>
            </div>
            <div class="grid grid-cols-2 border-b border-gray-300">
                <div class="px-4 py-2 text-sm text-black">Event</div>
                <div class="px-4 py-2 text-sm text-black">: {{ $participant->assessmentEvent->name }}</div>
            </div>
            <div class="px-4 py-2 text-center font-bold text-sm border-b border-gray-300 text-black">
                JOB PERSON MATCH
            </div>
            <div class="flex-grow flex items-center px-4 py-2">
                <div class="w-full h-8 relative">
                    <div class="h-full rounded {{ $jobMatchPercentage <= 40 ? 'gradient-bar-low' : ($jobMatchPercentage <= 70 ? 'gradient-bar-medium' : 'gradient-bar-high') }}"
                        style="width: {{ $jobMatchPercentage }}%;"></div>
                    <div class="absolute right-0 top-0 bottom-0 flex items-center pr-2">
                        <span class="text-sm font-bold text-black">{{ $jobMatchPercentage }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table -->
    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-blue-100">
                    <th class="border border-gray-300 px-4 py-2 text-center text-sm font-bold text-black col-number">NO.
                    </th>
                    <th class="border border-gray-300 px-4 py-2 text-left text-sm font-bold text-black">ASPEK</th>
                    <th class="border border-gray-300 px-2 py-2 text-center text-sm font-bold text-black">STANDARD</th>
                    <th class="border border-gray-300 px-2 py-2 text-center text-xs font-bold text-black range-scale"
                        colspan="5">RATING</th>
                </tr>
                <tr class="bg-blue-100">
                    <th class="border border-gray-300 px-4 py-2 text-center text-sm font-bold text-black col-number">
                    </th>
                    <th class="border border-gray-300 px-4 py-2 text-left text-sm font-bold text-black"></th>
                    <th
                        class="border border-gray-300 px-2 py-1 text-center text-xs font-bold text-black rating-cell-standard-1">
                    </th>
                    <th
                        class="border border-gray-300 px-2 py-1 text-center text-xs text-white range-scale rating-cell-1">
                        1 Rendah
                    </th>
                    <th
                        class="border border-gray-300 px-2 py-1 text-center text-xs text-white range-scale rating-cell-2">
                        2 Kurang
                    </th>
                    <th
                        class="border border-gray-300 px-2 py-1 text-center text-xs text-white range-scale rating-cell-3">
                        3 Cukup</th>
                    <th
                        class="border border-gray-300 px-2 py-1 text-center text-xs text-white range-scale rating-cell-4">
                        4 Baik</th>
                    <th
                        class="border border-gray-300 px-2 py-1 text-center text-xs text-white range-scale rating-cell-5">
                        5 Baik
                        Sekali</th>
                </tr>
            </thead>
            <tbody>
                <!-- ASPEK PSIKOLOGI (POTENSI) -->
                @if ($potensiCategory && count($potensiAspects) > 0)
                    <tr class="bg-gray-100">
                        <td class="border border-gray-300 px-4 py-2 font-bold text-sm text-black uppercase"
                            colspan="8">
                            {{ $potensiCategory->name }}</td>
                    </tr>

                    @foreach ($potensiAspects as $index => $aspect)
                        <!-- Aspect Header with Progress Bar -->
                        <tr class="bg-gray-100">
                            <td class="border border-gray-300 px-4 py-2 text-sm font-bold text-black">
                                {{ $loop->iteration }}.</td>
                            <td class="border border-gray-300 px-4 py-2 text-sm font-bold text-black">
                                {{ $aspect['name'] }}</td>
                            <td class="border border-gray-300 px-2 py-2 text-sm font-bold text-black text-center">
                                {{ number_format($aspect['standard_rating'], 2, ',', '.') }}</td>
                            <td class="border border-gray-300 range-scale" colspan="5">
                                <div class="progress-container">
                                    <div class="h-full rounded {{ $aspect['percentage'] <= 40 ? 'gradient-bar-low' : ($aspect['percentage'] <= 70 ? 'gradient-bar-medium' : 'gradient-bar-high') }}"
                                        style="width: {{ $aspect['percentage'] }}%;"></div>
                                    <div class="absolute right-0 top-0 bottom-0 flex items-center pr-2">
                                        <span class="text-xs font-bold text-black">{{ $aspect['percentage'] }}%</span>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <!-- Sub-Aspects -->
                        @foreach ($aspect['sub_aspects'] as $subIndex => $subAspect)
                            <tr class="bg-gray-50">
                                <td class="border border-gray-300 px-4 py-1 text-xs text-black col-number">
                                    {{ $subIndex + 1 }}.</td>
                                <td class="border border-gray-300 px-4 py-1 text-xs text-black">
                                    {{ $subAspect['name'] }}</td>
                                <td class="border border-gray-300 px-2 py-1 text-xs text-black text-center">
                                    {{ $subAspect['standard_rating'] }}</td>
                                @for ($i = 1; $i <= 5; $i++)
                                    <td
                                        class="border border-gray-300 range-scale text-center {{ $subAspect['standard_rating'] == $i ? 'rating-cell-standard-' . $i : 'rating-cell-empty text-black' }}">
                                        @if ($subAspect['individual_rating'] == $i)
                                            <span
                                                class="rating-x {{ $subAspect['individual_rating'] >= $subAspect['standard_rating'] ? 'above-standard' : 'below-standard' }}">
                                                X
                                            </span>
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @endforeach
                    @endforeach
                @endif

                <!-- ASPEK KOMPETENSI -->
                @if ($kompetensiCategory && count($kompetensiAspects) > 0)
                    <tr class="bg-gray-100">
                        <td class="border border-gray-300 px-4 py-2 font-bold text-sm text-black uppercase"
                            colspan="8">
                            {{ $kompetensiCategory->name }}</td>
                    </tr>

                    @foreach ($kompetensiAspects as $index => $aspect)
                        <!-- Aspect Header with Progress Bar -->
                        <tr class="bg-gray-100">
                            <td class="border border-gray-300 px-4 py-2 text-sm font-bold text-black">
                                {{ $loop->iteration }}.</td>
                            <td class="border border-gray-300 px-4 py-2 text-sm font-bold text-black">
                                {{ $aspect['name'] }}</td>
                            <td class="border border-gray-300 px-2 py-2 text-sm font-bold text-black text-center">
                                {{ number_format($aspect['standard_rating'], 2, ',', '.') }}</td>
                            <td class="border border-gray-300 range-scale" colspan="5">
                                <div class="progress-container">
                                    <div class="h-full rounded {{ $aspect['percentage'] <= 40 ? 'gradient-bar-low' : ($aspect['percentage'] <= 70 ? 'gradient-bar-medium' : 'gradient-bar-high') }}"
                                        style="width: {{ $aspect['percentage'] }}%;"></div>
                                    <div class="absolute right-0 top-0 bottom-0 flex items-center pr-2">
                                        <span class="text-xs font-bold text-black">{{ $aspect['percentage'] }}%</span>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <!-- Kompetensi Description (no sub-aspects, just description) -->
                        @if (isset($aspect['description']) && $aspect['description'])
                            <tr class="bg-gray-50">
                                <td class="border border-gray-300 px-4 py-1 text-xs text-black">1.</td>
                                <td class="border border-gray-300 px-4 py-1 text-xs text-black">
                                    {{ $aspect['description'] }}</td>
                                <td class="border border-gray-300 px-2 py-1 text-xs text-black text-center">
                                    {{ $aspect['standard_rating'] }}</td>
                                @for ($i = 1; $i <= 5; $i++)
                                    <td
                                        class="border border-gray-300 range-scale text-center {{ $aspect['standard_rating'] == $i ? 'rating-cell-standard-' . $i : 'rating-cell-empty text-black' }}">
                                        @if (round($aspect['individual_rating']) == $i)
                                            <span
                                                class="rating-x {{ round($aspect['individual_rating']) >= $aspect['standard_rating'] ? 'above-standard' : 'below-standard' }}">
                                                X
                                            </span>
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @else
                            <!-- If no description, show rating directly -->
                            <tr class="bg-gray-50">
                                <td class="border border-gray-300 px-4 py-1 text-xs text-black">1.</td>
                                <td class="border border-gray-300 px-4 py-1 text-xs text-black">Rating Level</td>
                                <td class="border border-gray-300 px-2 py-1 text-xs text-black text-center">
                                    {{ $aspect['standard_rating'] }}</td>
                                @for ($i = 1; $i <= 5; $i++)
                                    <td
                                        class="border border-gray-300 range-scale text-center {{ $aspect['standard_rating'] == $i ? 'rating-cell-standard-' . $i : 'rating-cell-empty text-black' }}">
                                        @if (round($aspect['individual_rating']) == $i)
                                            <span
                                                class="rating-x {{ round($aspect['individual_rating']) >= $aspect['standard_rating'] ? 'above-standard' : 'below-standard' }}">
                                                X
                                            </span>
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
