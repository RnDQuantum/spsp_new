<style>
    /* Ensure equal width for Range Scale columns */
    .range-scale {
        width: 8%;
        /* Equal width for columns 1-5 (5 columns, 40% total, 8% each) */
    }

    /* Ensure progress bar spans the full Range Scale section */
    .progress-container {
        position: relative;
        width: 100%;
        /* Span all 5 columns (40% of table width) */
        height: 1.5rem;
        /* Match h-6 */
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
                    <div class="h-full rounded {{ $jobMatchPercentage <= 20 ? 'bg-red-500' : ($jobMatchPercentage >= 30 && $jobMatchPercentage <= 70 ? 'bg-yellow-500' : 'bg-green-500') }}"
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
                    <th class="border border-gray-300 px-4 py-2 text-left text-sm font-bold text-black" colspan="2">
                        ATRIBUT & INDIKATOR</th>
                    <th class="border border-gray-300 px-2 py-2 text-center text-xs font-bold text-black range-scale"
                        colspan="5">Range Scale</th>
                </tr>
                <tr class="bg-blue-100">
                    <th class="border border-gray-300 px-4 py-2 text-left text-sm font-bold text-black" colspan="2">
                    </th>
                    <th class="border border-gray-300 px-2 py-1 text-center text-xs text-black range-scale">1</th>
                    <th class="border border-gray-300 px-2 py-1 text-center text-xs text-black range-scale">2</th>
                    <th class="border border-gray-300 px-2 py-1 text-center text-xs text-black range-scale">3</th>
                    <th class="border border-gray-300 px-2 py-1 text-center text-xs text-black range-scale">4</th>
                    <th class="border border-gray-300 px-2 py-1 text-center text-xs text-black range-scale">5</th>
                </tr>
            </thead>
            <tbody>
                <!-- ASPEK PSIKOLOGI (POTENSI) -->
                @if ($potensiCategory && count($potensiAspects) > 0)
                    <tr class="bg-gray-100">
                        <td class="border border-gray-300 px-4 py-2 font-bold text-sm text-black uppercase"
                            colspan="7">
                            {{ $potensiCategory->name }}</td>
                    </tr>

                    @foreach ($potensiAspects as $index => $aspect)
                        <!-- Aspect Header with Progress Bar -->
                        <tr>
                            <td class="border border-gray-300 px-4 py-2 text-sm font-bold text-black" colspan="2">
                                {{ $loop->iteration }}.&nbsp;&nbsp;&nbsp;{{ $aspect['name'] }}</td>
                            <td class="border border-gray-300 range-scale" colspan="5">
                                <div class="progress-container">
                                    <div class="h-full rounded {{ $aspect['percentage'] <= 20 ? 'bg-red-500' : ($aspect['percentage'] >= 30 && $aspect['percentage'] <= 70 ? 'bg-yellow-500' : 'bg-green-500') }}"
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
                                <td class="border border-gray-300 px-4 py-1 text-xs text-black">
                                    {{ $subIndex + 1 }}.</td>
                                <td class="border border-gray-300 px-4 py-1 text-xs text-black">
                                    {{ $subAspect['name'] }}</td>
                                @for ($i = 1; $i <= 5; $i++)
                                    <td class="border border-gray-300 range-scale text-center text-black">
                                        @if ($subAspect['individual_rating'] == $i)
                                            X
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
                            colspan="7">
                            {{ $kompetensiCategory->name }}</td>
                    </tr>

                    @foreach ($kompetensiAspects as $index => $aspect)
                        <!-- Aspect Header with Progress Bar -->
                        <tr>
                            <td class="border border-gray-300 px-4 py-2 text-sm font-bold text-black" colspan="2">
                                {{ $loop->iteration }}.&nbsp;&nbsp;&nbsp;{{ $aspect['name'] }}</td>
                            <td class="border border-gray-300 range-scale" colspan="5">
                                <div class="progress-container">
                                    <div class="h-full rounded {{ $aspect['percentage'] <= 20 ? 'bg-red-500' : ($aspect['percentage'] >= 30 && $aspect['percentage'] <= 70 ? 'bg-yellow-500' : 'bg-green-500') }}"
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
                                @for ($i = 1; $i <= 5; $i++)
                                    <td class="border border-gray-300 range-scale text-center text-black">
                                        @if (round($aspect['individual_rating']) == $i)
                                            X
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @else
                            <!-- If no description, show rating directly -->
                            <tr class="bg-gray-50">
                                <td class="border border-gray-300 px-4 py-1 text-xs text-black">1.</td>
                                <td class="border border-gray-300 px-4 py-1 text-xs text-black">Rating Level</td>
                                @for ($i = 1; $i <= 5; $i++)
                                    <td class="border border-gray-300 range-scale text-center text-black">
                                        @if (round($aspect['individual_rating']) == $i)
                                            X
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
