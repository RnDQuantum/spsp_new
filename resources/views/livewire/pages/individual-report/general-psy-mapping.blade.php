<div>
    <div class="mx-auto my-8 shadow overflow-hidden" style="max-width: 1400px;" class="bg-white dark:bg-gray-800">

        @if ($showHeader)
            <!-- Header - DARK MODE READY -->
            <div class="border-b-4 border-black py-3 bg-gray-300 dark:bg-gray-600">
                <h1
                    class="text-center text-lg font-bold uppercase tracking-wide
                           text-gray-900 dark:text-white">
                    PSYCHOLOGY MAPPING
                </h1>
                <p
                    class="text-center text-sm font-semibold
                         text-gray-700 dark:text-gray-200 mt-1">
                    {{ $participant->name }}
                </p>
                <p
                    class="text-center text-sm font-semibold
                         text-gray-700 dark:text-gray-200 mt-1">
                    {{ $participant->event->name }}
                </p>
                <p
                    class="text-center text-sm font-semibold
                         text-gray-700 dark:text-gray-200 mt-1">
                    {{ $participant->positionFormation->name }} - {{ $participant->positionFormation->template->name }}
                </p>
            </div>
        @endif

        @if ($showInfoSection)
            <!-- Tolerance Selector Component -->
            @php
                $summary = $this->getPassingSummary();
            @endphp
            @livewire('components.tolerance-selector', [
                'passing' => $summary['passing'],
                'total' => $summary['total'],
            ])
        @endif

        @if ($showTable)
            <!-- Table Section - DARK MODE READY -->
            <div class="p-4 overflow-x-auto bg-white dark:bg-gray-800">
                <table class="min-w-full border border-black text-xs">
                    <thead>
                        <tr class="bg-sky-200 dark:bg-gray-700 text-gray-900 dark:text-white">
                            <th class="border border-black px-3 py-2 font-semibold 
                                   bg-gray-300 dark:bg-gray-600"
                                rowspan="2">
                                No</th>
                            <th class="border border-black px-3 py-2 font-semibold 
                                   bg-gray-300 dark:bg-gray-600"
                                rowspan="2">
                                Atribut/Attribute</th>
                            <th class="border border-black px-3 py-2 font-semibold 
                                   bg-gray-300 dark:bg-gray-600"
                                rowspan="2">
                                Bobot %<br>100</th>
                            <th class="border border-black px-3 py-2 font-semibold 
                                   bg-gray-300 dark:bg-gray-600"
                                colspan="2">
                                <span x-data
                                    x-text="$wire.tolerancePercentage > 0 ? 'Standard (-' + $wire.tolerancePercentage + '%)' : 'Standard'"
                                    class="text-gray-900 dark:text-white"></span>
                            </th>
                            <th class="border border-black px-3 py-2 font-semibold 
                                   bg-gray-300 dark:bg-gray-600"
                                colspan="2">Individu</th>
                            <th class="border border-black px-3 py-2 font-semibold 
                                   bg-gray-300 dark:bg-gray-600"
                                colspan="2">Gap</th>
                            <th class="border border-black px-3 py-2 font-semibold 
                                   bg-gray-300 dark:bg-gray-600"
                                rowspan="2">
                                Prosentase<br>Kesesuaian</th>
                            <th class="border border-black px-3 py-2 font-semibold 
                                   bg-gray-300 dark:bg-gray-600"
                                rowspan="2">
                                Kesimpulan/Conclusion</th>
                        </tr>
                        <tr class="bg-sky-200 dark:bg-gray-700 text-gray-900 dark:text-white">
                            <th
                                class="border border-black px-3 py-1 font-semibold 
                                   bg-gray-300 dark:bg-gray-600">
                                Rating/<br>Level</th>
                            <th
                                class="border border-black px-3 py-1 font-semibold 
                                   bg-gray-300 dark:bg-gray-600">
                                Score</th>
                            <th
                                class="border border-black px-3 py-1 font-semibold 
                                   bg-gray-300 dark:bg-gray-600">
                                Rating/<br>Level</th>
                            <th
                                class="border border-black px-3 py-1 font-semibold 
                                   bg-gray-300 dark:bg-gray-600">
                                Score</th>
                            <th
                                class="border border-black px-3 py-1 font-semibold 
                                   bg-gray-300 dark:bg-gray-600">
                                Rating/<br>Level</th>
                            <th
                                class="border border-black px-3 py-0 font-semibold 
                                   bg-gray-300 dark:bg-gray-600">
                                Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($aspectsData as $index => $aspect)
                            <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td
                                    class="border border-black px-3 py-2 text-center 
                                       text-gray-900 dark:text-white">
                                    {{ ['I', 'II', 'III', 'IV', 'V'][$index] }}
                                </td>
                                <td
                                    class="border border-black px-3 py-2 
                                       text-gray-900 dark:text-white">
                                    {{ $aspect['name'] }}</td>
                                <td
                                    class="border border-black px-3 py-2 text-center 
                                       text-gray-900 dark:text-white">
                                    {{ $aspect['weight_percentage'] }}</td>
                                <td
                                    class="border border-black px-3 py-2 text-center 
                                       text-gray-900 dark:text-white">
                                    {{ number_format($aspect['standard_rating'], 2) }}</td>
                                <td
                                    class="border border-black px-3 py-2 text-center 
                                       text-gray-900 dark:text-white">
                                    {{ number_format($aspect['standard_score'], 2) }}</td>
                                <td
                                    class="border border-black px-3 py-2 text-center 
                                       text-gray-900 dark:text-white">
                                    {{ number_format($aspect['individual_rating'], 2) }}</td>
                                <td
                                    class="border border-black px-3 py-2 text-center 
                                       text-gray-900 dark:text-white">
                                    {{ number_format($aspect['individual_score'], 2) }}</td>
                                <td
                                    class="border border-black px-3 py-2 text-center 
                                       text-gray-900 dark:text-white">
                                    {{ number_format($aspect['gap_rating'], 2) }}</td>
                                <td
                                    class="border border-black px-3 py-2 text-center 
                                       text-gray-900 dark:text-white">
                                    {{ number_format($aspect['gap_score'], 2) }}</td>
                                <td
                                    class="border border-black px-3 py-2 text-center 
                                       text-gray-900 dark:text-white">
                                    @php
                                        $percentage =
                                            $aspect['standard_score'] > 0
                                                ? round(($aspect['individual_score'] / $aspect['standard_score']) * 100)
                                                : 0;
                                    @endphp
                                    {{ $percentage }}%
                                </td>
                                <td
                                    class="border border-black px-3 py-2 text-center
                                @php
$c = trim(strtoupper($aspect['conclusion_text'])); @endphp

                                @if ($c === 'DI ATAS STANDAR') bg-green-600 text-white font-bold
                                @elseif ($c === 'MEMENUHI STANDAR') bg-yellow-400 text-gray-900 font-bold
                                @elseif ($c === 'DI BAWAH STANDAR') bg-red-600 text-white font-bold
                                @else bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white @endif">
                                    {{ $aspect['conclusion_text'] }}
                                </td>
                            </tr>
                        @endforeach

                        <!-- Total Rating Row -->
                        <tr class="font-bold bg-gray-300 dark:bg-gray-600">
                            <td class="border border-black px-3 py-2 text-center 
                                   text-gray-900 dark:text-white"
                                colspan="3">Total Rating</td>
                            <td
                                class="border border-black px-3 py-2 text-center 
                                   text-gray-900 dark:text-white">
                                {{ number_format($totalStandardRating, 2) }}
                            </td>
                            <td class="border border-black px-3 py-2 bg-black"></td>
                            <td
                                class="border border-black px-3 py-2 text-center 
                                   text-gray-900 dark:text-white">
                                {{ number_format($totalIndividualRating, 2) }}</td>
                            <td class="border border-black px-3 py-2 bg-black"></td>
                            <td
                                class="border border-black px-3 py-2 text-center
                                   text-gray-900 dark:text-white">
                                {{ number_format($totalGapRating, 2) }}</td>
                            <td class="border border-black px-3 py-2 bg-black"></td>
                            <td class="border border-black px-3 py-2 text-center
                                @php
$c = trim(strtoupper($overallConclusion)); @endphp
                                @if ($c === 'DI ATAS STANDAR') bg-green-600 text-white font-bold
                                @elseif ($c === 'MEMENUHI STANDAR') bg-yellow-400 text-gray-900 font-bold
                                @elseif ($c === 'DI BAWAH STANDAR') bg-red-600 text-white font-bold
                                @else bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white @endif"
                                colspan="2">
                                {{ $overallConclusion }}
                            </td>
                        </tr>

                        <!-- Total Score Row -->
                        <tr class="font-bold bg-gray-300 dark:bg-gray-600">
                            <td class="border border-black px-3 py-2 text-center 
                                   text-gray-900 dark:text-white"
                                colspan="3">Total Score</td>
                            <td class="border border-black px-3 py-2 bg-black"></td>
                            <td
                                class="border border-black px-3 py-2 text-center 
                                   text-gray-900 dark:text-white">
                                {{ number_format($totalStandardScore, 2) }}
                            </td>
                            <td class="border border-black px-3 py-2 bg-black"></td>
                            <td
                                class="border border-black px-3 py-2 text-center 
                                   text-gray-900 dark:text-white">
                                {{ number_format($totalIndividualScore, 2) }}</td>
                            <td class="border border-black px-3 py-2 bg-black"></td>
                            <td
                                class="border border-black px-3 py-2 text-center
                                   text-gray-900 dark:text-white">
                                {{ number_format($totalGapScore, 2) }}</td>
                            <td class="border border-black px-3 py-2 text-center
                                @php
$c = trim(strtoupper($overallConclusion)); @endphp
                                @if ($c === 'DI ATAS STANDAR') bg-green-600 text-white font-bold
                                @elseif ($c === 'MEMENUHI STANDAR') bg-yellow-400 text-gray-900 font-bold
                                @elseif ($c === 'DI BAWAH STANDAR') bg-red-600 text-white font-bold
                                @else bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white @endif"
                                colspan="2">
                                {{ $overallConclusion }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Ranking Information Section -->
        @if ($showRankingInfo)
            @php
                $rankingInfo = $this->getParticipantRanking();
            @endphp

            @if ($rankingInfo)
                <div class="px-6 py-4 bg-gray-300 dark:bg-gray-600 border-t-2 border-black dark:border-gray-600">
                    <div class="max-w-4xl mx-auto">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z">
                                </path>
                            </svg>
                            Ranking Peserta - Kategori Potensi
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Ranking Card -->
                            <div
                                class="bg-white dark:bg-gray-800 border-2 border-blue-300 dark:border-blue-600 rounded-lg p-4 text-center">
                                <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Ranking</div>
                                <div class="text-4xl font-bold text-blue-600 dark:text-blue-400">
                                    #{{ $rankingInfo['rank'] }}
                                </div>
                            </div>

                            <!-- Total Participants Card -->
                            <div
                                class="bg-white dark:bg-gray-800 border-2 border-indigo-300 dark:border-indigo-600 rounded-lg p-4 text-center">
                                <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Total Peserta</div>
                                <div class="text-4xl font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ $rankingInfo['total'] }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $participant->positionFormation->name }}
                                </div>
                            </div>

                            <!-- Conclusion Card -->
                            <div
                                class="bg-white dark:bg-gray-800 border-2 rounded-lg p-4 text-center
                                @php
$conclusion = strtoupper(trim($rankingInfo['conclusion'])); @endphp
                                @if ($conclusion === 'DI ATAS STANDAR') border-green-300 dark:border-green-600
                                @elseif ($conclusion === 'MEMENUHI STANDAR')
                                    border-yellow-300 dark:border-yellow-600
                                @else
                                    border-red-300 dark:border-red-600 @endif
                            ">
                                <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Status</div>
                                <div
                                    class="text-lg font-bold px-4 py-2 rounded-lg
                                    @if ($conclusion === 'DI ATAS STANDAR') bg-green-600 dark:bg-green-600 text-white
                                    @elseif ($conclusion === 'MEMENUHI STANDAR')
                                        bg-yellow-400 text-gray-900
                                    @else
                                        bg-red-600 dark:bg-red-600 text-white @endif
                                ">
                                    {{ $rankingInfo['conclusion'] }}
                                </div>
                            </div>
                        </div>

                        <!-- Additional Info -->
                        <div class="mt-4 bg-gray-300 dark:bg-gray-600 rounded-lg p-1">
                            <div class="text-sm text-gray-800 dark:text-gray-200">
                                <strong>Keterangan:</strong> Ranking berdasarkan total Individual Score dari semua aspek
                                dalam
                                kategori Potensi,
                                dibandingkan dengan peserta lain di proyek yang sama pada posisi
                                <strong>{{ $participant->positionFormation->name }}</strong>.
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        @if ($showRatingChart)
            <!-- Chart Section Rating - DARK MODE READY -->
            <div class="p-6 border-t-2 border-black bg-white dark:bg-gray-800" wire:ignore
                id="chart-rating-{{ $chartId }}">
                <div class="text-center text-base font-bold mb-6 
                       text-gray-900 dark:text-white">
                    Profil Potensi Spider Plot Chart (Rating)</div>
                <div class="flex justify-center text-sm gap-8 mb-8" id="rating-legend-{{ $chartId }}">
                    <!-- Dataset 0: Peserta - HIJAU -->
                    <span
                        class="legend-item flex items-center gap-2 cursor-pointer select-none 
               hover:bg-gray-100 dark:hover:bg-gray-600 
               px-3 py-2 rounded-lg transition-all duration-200 
               border border-gray-300 dark:border-gray-600 
               shadow-sm bg-white dark:bg-gray-700 
               text-gray-900 dark:text-white"
                        data-chart="rating" data-dataset="0">
                        <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #5db010;"></span>
                        <span class="font-semibold" style="color: #5db010;">{{ $participant->name }}</span>
                    </span>

                    <!-- Dataset 1: Standard - MERAH -->
                    <span
                        class="legend-item flex items-center gap-2 cursor-pointer select-none 
               hover:bg-gray-100 dark:hover:bg-gray-600 
               px-3 py-2 rounded-lg transition-all duration-200 
               border border-gray-300 dark:border-gray-600 
               shadow-sm bg-white dark:bg-gray-700 
               text-gray-900 dark:text-white"
                        data-chart="rating" data-dataset="1">
                        <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #b50505;"></span>
                        <span class="font-semibold">Standard</span>
                    </span>

                    <!-- Dataset 2: Tolerance - KUNING -->
                    <span
                        class="legend-item flex items-center gap-2 cursor-pointer select-none 
               hover:bg-gray-100 dark:hover:bg-gray-600 
               px-3 py-2 rounded-lg transition-all duration-200 
               border border-gray-300 dark:border-gray-600 
               shadow-sm bg-white dark:bg-gray-700 
               text-gray-900 dark:text-white"
                        data-chart="rating" data-dataset="2">
                        <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #fafa05;"></span>
                        <span class="font-semibold" x-data
                            x-text="'Tolerance ' + $wire.tolerancePercentage + '%'"></span>
                    </span>
                </div>
                <div class="flex justify-center mb-6">
                    <div style="width: 700px; height: 700px; position: relative;">
                        <canvas id="spiderRatingChart-{{ $chartId }}"></canvas>
                    </div>
                </div>

                <!-- Legend Rating - DARK MODE READY -->
                <!-- GANTI URUTAN LEGEND INI -->
            </div>
        @endif

        @if ($showScoreChart)
            <!-- Chart Section Score - DARK MODE READY -->
            <div class="p-6 border-t-2 border-black bg-white dark:bg-gray-800" wire:ignore
                id="chart-score-{{ $chartId }}">
                <div
                    class="text-center text-base font-bold mb-6 
                       text-gray-900 dark:text-white">
                    Profil Potensi Spider Plot Chart (Score)</div>
                <div class="flex justify-center mb-6">
                    <div style="width: 700px; height: 700px; position: relative;">
                        <canvas id="spiderScoreChart-{{ $chartId }}"></canvas>
                    </div>
                </div>

                <!-- Legend Score - DARK MODE READY -->
                <!-- GANTI URUTAN LEGEND INI (sama seperti rating) -->
                <div class="flex justify-center text-sm gap-8 mb-8" id="score-legend-{{ $chartId }}">
                    <!-- Dataset 0: Peserta - HIJAU -->
                    <span
                        class="legend-item flex items-center gap-2 cursor-pointer select-none 
           hover:bg-gray-100 dark:hover:bg-gray-600 
           px-3 py-2 rounded-lg transition-all duration-200 
           border border-gray-300 dark:border-gray-600 
           shadow-sm bg-white dark:bg-gray-700 
           text-gray-900 dark:text-white"
                        data-chart="score" data-dataset="0">
                        <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #5db010;"></span>
                        <span class="font-semibold" style="color: #5db010;">{{ $participant->name }}</span>
                    </span>

                    <!-- Dataset 1: Standard - MERAH -->
                    <span
                        class="legend-item flex items-center gap-2 cursor-pointer select-none 
           hover:bg-gray-100 dark:hover:bg-gray-600 
           px-3 py-2 rounded-lg transition-all duration-200 
           border border-gray-300 dark:border-gray-600 
           shadow-sm bg-white dark:bg-gray-700 
           text-gray-900 dark:text-white"
                        data-chart="score" data-dataset="1">
                        <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #b50505;"></span>
                        <span class="font-semibold">Standard</span>
                    </span>

                    <!-- Dataset 2: Tolerance - KUNING -->
                    <span
                        class="legend-item flex items-center gap-2 cursor-pointer select-none 
           hover:bg-gray-100 dark:hover:bg-gray-600 
           px-3 py-2 rounded-lg transition-all duration-200 
           border border-gray-300 dark:border-gray-600 
           shadow-sm bg-white dark:bg-gray-700 
           text-gray-900 dark:text-white"
                        data-chart="score" data-dataset="2">
                        <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #fafa05;"></span>
                        <span class="font-semibold" x-data
                            x-text="'Tolerance ' + $wire.tolerancePercentage + '%'"></span>
                    </span>
                </div>
            </div>
        @endif

        @if ($showRatingChart || $showScoreChart)
            <!-- 1 SCRIPT FULL - DARK MODE CHARTS (PSYCHOLOGY) -->
            <!-- 1 SCRIPT FULL - DARK MODE CHARTS (PSYCHOLOGY) - FIXED TICK LABELS -->
            <script>
                (function() {
                    if (window['chartSetup_{{ $chartId }}']) return;
                    window['chartSetup_{{ $chartId }}'] = true;

                    // VISIBILITY STATE
                    window.ratingVisibility_{{ $chartId }} = {
                        0: false,
                        1: false,
                        2: false
                    };
                    window.scoreVisibility_{{ $chartId }} = {
                        0: false,
                        1: false,
                        2: false
                    };

                    // 🌙 DARK MODE COLORS - FIXED TRANSPARENT BG
                    const getColors = () => {
                        const dark = document.documentElement.classList.contains('dark');
                        return {
                            grid: dark ? 'rgba(255, 255, 255, 0.5)' : 'rgba(0, 0, 0, 0.5)', // Lebih pekat (0.15 -> 0.5)
                            angleLines: dark ? 'rgba(255, 255, 255, 0.5)' :
                            'rgba(0, 0, 0, 0.5)', // Lebih pekat (0.15 -> 0.5)
                            ticks: dark ? '#ffffff' : '#000000', // Warna solid
                            labels: dark ? '#ffffff' : '#000000', // Warna solid
                            tickBg: 'transparent' // Tetap transparent
                        };
                    };

                    // TOGGLE FUNCTION
                    window.toggleDataset_{{ $chartId }} = function(chartType, datasetIndex) {
                        const chart = window[`${chartType}Chart_{{ $chartId }}`];
                        if (!chart || !chart.data.datasets[datasetIndex]) return;

                        const wasHidden = chart.data.datasets[datasetIndex].hidden || false;
                        chart.data.datasets[datasetIndex].hidden = !wasHidden;
                        window[`${chartType}Visibility_{{ $chartId }}`][datasetIndex] = !wasHidden;

                        chart.update('active');
                        updateLegendVisual(chartType);
                    };

                    // UPDATE LEGEND VISUAL
                    function updateLegendVisual(chartType) {
                        const legendContainer = document.getElementById(`${chartType}-legend-{{ $chartId }}`);
                        if (!legendContainer) return;

                        const chart = window[`${chartType}Chart_{{ $chartId }}`];
                        legendContainer.querySelectorAll('.legend-item').forEach(item => {
                            const idx = parseInt(item.dataset.dataset);
                            const isHidden = chart?.data.datasets[idx]?.hidden || false;
                            if (isHidden) {
                                item.classList.add('opacity-50', 'line-through', 'bg-gray-50', 'dark:bg-gray-600');
                                item.classList.remove('bg-white', 'dark:bg-gray-700', 'shadow-sm');
                            } else {
                                item.classList.remove('opacity-50', 'line-through', 'bg-gray-50', 'dark:bg-gray-600');
                                item.classList.add('bg-white', 'dark:bg-gray-700', 'shadow-sm');
                            }
                        });
                    }

                    // SETUP LEGEND LISTENERS
                    function setupLegendListeners(chartType) {
                        const legendContainer = document.getElementById(`${chartType}-legend-{{ $chartId }}`);
                        if (!legendContainer) return;
                        legendContainer.onclick = (e) => {
                            const item = e.target.closest('.legend-item');
                            if (!item) return;
                            window.toggleDataset_{{ $chartId }}(chartType, parseInt(item.dataset.dataset));
                        };
                    }

                    // RATING CHART SETUP - FIXED TICK LABELS
                    function setupRatingChart() {
                        if (window.ratingChart_{{ $chartId }}) window.ratingChart_{{ $chartId }}.destroy();

                        const chartLabels = @js($chartLabels);
                        const originalStandardRatings = @js($chartOriginalStandardRatings);
                        const standardRatings = @js($chartStandardRatings);
                        const individualRatings = @js($chartIndividualRatings);
                        const participantName = @js($participant->name);
                        const tolerancePercentage = @js($tolerancePercentage);

                        const canvas = document.getElementById('spiderRatingChart-{{ $chartId }}');
                        if (!canvas) return;
                        const ctx = canvas.getContext('2d');
                        const colors = getColors();

                        const datasets = [{
                                // LAYER 1: PESERTA (HIJAU) - Dataset 0
                                label: participantName,
                                data: individualRatings,
                                fill: true,
                                backgroundColor: '#5db010', // ← UBAH dari rgba(..., 0.7) ke SOLID
                                borderColor: '#8fd006',
                                pointBackgroundColor: '#8fd006',
                                pointBorderColor: '#fff',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                pointBorderWidth: 2,
                                hidden: window.ratingVisibility_{{ $chartId }}[0],
                                datalabels: {
                                    display: false,
                                    color: '#000000',
                                    backgroundColor: '#5db010',
                                    borderRadius: 4,
                                    padding: 6,
                                    font: {
                                        weight: 'bold',
                                        size: 10
                                    },
                                    anchor: 'end',
                                    align: 'end',
                                    offset: 6,
                                    formatter: (v) => v.toFixed(2)
                                }
                            },
                            {
                                // LAYER 2: STANDARD (MERAH) - Dataset 1
                                label: 'Standard', // ← UBAH dari `Tolerance ${tolerancePercentage}%`
                                data: standardRatings,
                                fill: true,
                                backgroundColor: '#b50505', // ← UBAH dari rgba(..., 0.7) ke SOLID
                                borderColor: '#b50505',
                                borderWidth: 2,
                                pointRadius: 3,
                                pointBackgroundColor: '#9a0404',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                hidden: window.ratingVisibility_{{ $chartId }}[1],
                                datalabels: {
                                    display: false,
                                    color: '#FFFFFF',
                                    backgroundColor: '#b50505',
                                    borderRadius: 4,
                                    padding: 6,
                                    font: {
                                        weight: 'bold',
                                        size: 9
                                    },
                                    anchor: 'end',
                                    align: 'start',
                                    formatter: (v) => v.toFixed(2)
                                }
                            },
                            {
                                // LAYER 3: TOLERANCE (KUNING) - Dataset 2
                                label: `Tolerance ${tolerancePercentage}%`, // ← UBAH dari 'Standard'
                                data: originalStandardRatings,
                                fill: true,
                                backgroundColor: '#fafa05', // ← UBAH dari rgba(..., 0.7) ke SOLID
                                borderColor: '#e6d105',
                                pointBackgroundColor: '#e6d105',
                                pointBorderColor: '#fff',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                pointBorderWidth: 2,
                                hidden: window.ratingVisibility_{{ $chartId }}[2],
                                datalabels: {
                                    display: false,
                                    color: '#000000',
                                    backgroundColor: '#fafa05',
                                    borderRadius: 4,
                                    padding: 6,
                                    font: {
                                        weight: 'bold',
                                        size: 10
                                    },
                                    anchor: 'center',
                                    align: 'center',
                                    offset: 6,
                                    formatter: (v) => v.toFixed(2)
                                }
                            }
                        ];

                        window.ratingChart_{{ $chartId }} = new Chart(ctx, {
                            type: 'radar',
                            data: {
                                labels: chartLabels,
                                datasets
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    datalabels: {
                                        display: ctx => !ctx.dataset.hidden
                                    }
                                },
                                // Dalam setupRatingChart()
                                scales: {
                                    r: {
                                        beginAtZero: true,
                                        min: 0,
                                        max: 5,
                                        ticks: {
                                            display: false,
                                            stepSize: 1,
                                            color: colors.ticks,
                                            font: {
                                                size: 11,
                                                weight: 'bold'
                                            },
                                            backdropColor: colors.tickBg,
                                            showLabelBackdrop: false,
                                            z: 2 // Tambahkan z-index
                                        },
                                        pointLabels: {
                                            font: {
                                                size: 11,
                                                weight: '600'
                                            },
                                            color: colors.labels,
                                            z: 3 // Tambahkan z-index
                                        },
                                        grid: {
                                            color: colors.grid,
                                            z: 1 // Tambahkan z-index
                                        },
                                        angleLines: {
                                            color: colors.grid,
                                            z: 1 // Tambahkan z-index
                                        }
                                    }
                                }
                            },
                            plugins: [{
                                id: 'shiftTicks',
                                afterDraw: (chart) => {
                                    const {
                                        ctx,
                                        scales
                                    } = chart;
                                    const scale = scales.r;
                                    const ticks = scale.ticks;
                                    const yCenter = scale.yCenter;
                                    const xCenter = scale.xCenter;

                                    ctx.save();
                                    ctx.font = `bold ${scale.options.ticks.font.size}px sans-serif`;
                                    ctx.fillStyle = scale.options.ticks.color || '#000';
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle';

                                    // Offset untuk menggeser posisi
                                    const offsetX = 10; // geser ke kanan
                                    const offsetY = 0; // tidak geser vertikal

                                    ticks.forEach((tick) => {
                                        const value = tick.value;
                                        const radius = scale.getDistanceFromCenterForValue(value);
                                        const labelY = yCenter - radius - offsetY;
                                        const labelX = xCenter + offsetX;
                                        ctx.fillText(value, labelX, labelY);
                                    });

                                    ctx.restore();
                                }
                            }]
                        });

                        setTimeout(() => {
                            setupLegendListeners('rating');
                            updateLegendVisual('rating');
                        }, 200);
                    }

                    // SCORE CHART SETUP - FIXED TICK LABELS
                    function setupScoreChart() {
                        if (window.scoreChart_{{ $chartId }}) window.scoreChart_{{ $chartId }}.destroy();

                        const chartLabels = @js($chartLabels);
                        const originalStandardScores = @js($chartOriginalStandardScores);
                        const standardScores = @js($chartStandardScores);
                        const individualScores = @js($chartIndividualScores);
                        const participantName = @js($participant->name);
                        const tolerancePercentage = @js($tolerancePercentage);
                        const maxScore = Math.max(...originalStandardScores, ...individualScores, ...standardScores) * 1.2;

                        const canvas = document.getElementById('spiderScoreChart-{{ $chartId }}');
                        if (!canvas) return;
                        const ctx = canvas.getContext('2d');
                        const colors = getColors();

                        const datasets = [{
                                // LAYER 1: PESERTA (HIJAU) - Dataset 0
                                label: participantName,
                                data: individualScores,
                                fill: true,
                                backgroundColor: '#5db010', // ← UBAH dari rgba
                                borderColor: '#8fd006',
                                pointBackgroundColor: '#8fd006',
                                pointBorderColor: '#fff',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                pointBorderWidth: 2,
                                hidden: window.scoreVisibility_{{ $chartId }}[0],
                                datalabels: {
                                    display: false,
                                    color: '#000000',
                                    backgroundColor: '#5db010',
                                    borderRadius: 4,
                                    padding: 6,
                                    font: {
                                        weight: 'bold',
                                        size: 10
                                    },
                                    anchor: 'end',
                                    align: 'end',
                                    offset: 6,
                                    formatter: (v) => v.toFixed(2)
                                }
                            },
                            {
                                // LAYER 2: STANDARD (MERAH) - Dataset 1
                                label: 'Standard', // ← UBAH dari `Tolerance ${tolerancePercentage}%`
                                data: standardScores,
                                fill: true,
                                backgroundColor: '#b50505', // ← UBAH dari rgba
                                borderColor: '#b50505',
                                borderWidth: 2,
                                pointRadius: 3,
                                pointBackgroundColor: '#9a0404',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                hidden: window.scoreVisibility_{{ $chartId }}[1],
                                datalabels: {
                                    display: false,
                                    color: '#FFFFFF',
                                    backgroundColor: '#b50505',
                                    borderRadius: 4,
                                    padding: 6,
                                    font: {
                                        weight: 'bold',
                                        size: 9
                                    },
                                    anchor: 'end',
                                    align: 'start',
                                    formatter: (v) => v.toFixed(2)
                                }
                            },
                            {
                                // LAYER 3: TOLERANCE (KUNING) - Dataset 2
                                label: `Tolerance ${tolerancePercentage}%`, // ← UBAH dari 'Standard'
                                data: originalStandardScores,
                                fill: true,
                                backgroundColor: '#fafa05', // ← UBAH dari rgba
                                borderColor: '#e6d105',
                                pointBackgroundColor: '#e6d105',
                                pointBorderColor: '#fff',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                pointBorderWidth: 2,
                                hidden: window.scoreVisibility_{{ $chartId }}[2],
                                datalabels: {
                                    display: false,
                                    color: '#000000',
                                    backgroundColor: '#fafa05',
                                    borderRadius: 4,
                                    padding: 6,
                                    font: {
                                        weight: 'bold',
                                        size: 10
                                    },
                                    anchor: 'center',
                                    align: 'center',
                                    offset: 6,
                                    formatter: (v) => v.toFixed(2)
                                }
                            }
                        ];

                        window.scoreChart_{{ $chartId }} = new Chart(ctx, {
                            type: 'radar',
                            data: {
                                labels: chartLabels,
                                datasets
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    datalabels: {
                                        display: ctx => !ctx.dataset.hidden
                                    }
                                },
                                // Dalam setupScoreChart()
                                scales: {
                                    r: {
                                        beginAtZero: true,
                                        min: 0,
                                        max: maxScore,
                                        ticks: {
                                            display: false,
                                            stepSize: 20,
                                            color: colors.ticks,
                                            font: {
                                                size: 11,
                                                weight: 'bold'
                                            },
                                            backdropColor: colors.tickBg,
                                            showLabelBackdrop: false,
                                            z: 2 // Tambahkan z-index
                                        },
                                        pointLabels: {
                                            font: {
                                                size: 11,
                                                weight: '600'
                                            },
                                            color: colors.labels,
                                            z: 3 // Tambahkan z-index
                                        },
                                        grid: {
                                            color: colors.grid,
                                            z: 1 // Tambahkan z-index
                                        },
                                        angleLines: {
                                            color: colors.grid,
                                            z: 1 // Tambahkan z-index
                                        }
                                    }
                                }
                            },
                            plugins: [{
                                id: 'shiftTicks',
                                afterDraw: (chart) => {
                                    const {
                                        ctx,
                                        scales
                                    } = chart;
                                    const scale = scales.r;
                                    const ticks = scale.ticks;
                                    const yCenter = scale.yCenter;
                                    const xCenter = scale.xCenter;

                                    ctx.save();
                                    ctx.font = `bold ${scale.options.ticks.font.size}px sans-serif`;
                                    ctx.fillStyle = scale.options.ticks.color || '#000';
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle';

                                    // Offset untuk menggeser posisi
                                    const offsetX = 10; // geser ke kanan
                                    const offsetY = 0; // tidak geser vertikal

                                    ticks.forEach((tick) => {
                                        const value = tick.value;
                                        const radius = scale.getDistanceFromCenterForValue(value);
                                        const labelY = yCenter - radius - offsetY;
                                        const labelX = xCenter + offsetX;
                                        ctx.fillText(value, labelX, labelY);
                                    });

                                    ctx.restore();
                                }
                            }]
                        });

                        setTimeout(() => {
                            setupLegendListeners('score');
                            updateLegendVisual('score');
                        }, 200);
                    }

                    // LIVEWIRE INIT
                    function waitForLivewire(callback) {
                        if (window.Livewire) callback();
                        else setTimeout(() => waitForLivewire(callback), 100);
                    }

                    waitForLivewire(function() {
                        @if ($showRatingChart)
                            setupRatingChart();
                        @endif
                        @if ($showScoreChart)
                            setupScoreChart();
                        @endif

                        // TOLERANCE UPDATE
                        Livewire.on('chartDataUpdated', function(data) {
                            let chartData = Array.isArray(data) && data.length > 0 ? data[0] : data;
                            if (!chartData) return;

                            // UPDATE RATING
                            if (window.ratingChart_{{ $chartId }}) {
                                const chart = window.ratingChart_{{ $chartId }};

                                // UBAH BAGIAN INI:
                                chart.data.datasets[0].data = chartData.individualRatings; // Peserta
                                chart.data.datasets[1].label = 'Standard'; // ← UBAH label
                                chart.data.datasets[1].data = chartData.standardRatings; // Standard
                                chart.data.datasets[2].label =
                                    `Tolerance ${chartData.tolerance}%`; // ← UBAH label
                                chart.data.datasets[2].data = chartData.originalStandardRatings; // Tolerance

                                chart.data.datasets.forEach((d, i) => d.hidden = window
                                    .ratingVisibility_{{ $chartId }}[i]);

                                const colors = getColors();
                                chart.options.scales.r.ticks.color = colors.ticks;
                                chart.options.scales.r.ticks.backdropColor = colors.tickBg;
                                chart.options.scales.r.pointLabels.color = colors.labels;
                                chart.options.scales.r.grid.color = colors.grid;
                                chart.options.scales.r.angleLines.color = colors.grid;

                                chart.update('active');
                                updateLegendVisual('rating');
                            }

                            // UPDATE SCORE
                            // UPDATE SCORE
                            if (window.scoreChart_{{ $chartId }}) {
                                const chart = window.scoreChart_{{ $chartId }};

                                // UBAH BAGIAN INI:
                                chart.data.datasets[0].data = chartData.individualScores; // Peserta
                                chart.data.datasets[1].label = 'Standard'; // ← UBAH label
                                chart.data.datasets[1].data = chartData.standardScores; // Standard
                                chart.data.datasets[2].label =
                                    `Tolerance ${chartData.tolerance}%`; // ← UBAH label
                                chart.data.datasets[2].data = chartData.originalStandardScores; // Tolerance

                                chart.data.datasets.forEach((d, i) => d.hidden = window
                                    .scoreVisibility_{{ $chartId }}[i]);

                                const colors = getColors();
                                chart.options.scales.r.ticks.color = colors.ticks;
                                chart.options.scales.r.ticks.backdropColor = colors.tickBg;
                                chart.options.scales.r.pointLabels.color = colors.labels;
                                chart.options.scales.r.grid.color = colors.grid;
                                chart.options.scales.r.angleLines.color = colors.grid;

                                chart.update('active');
                                updateLegendVisual('score');
                            }
                        });
                    });

                    // 🌙 DARK MODE LISTENER - FIXED TICK LABELS
                    new MutationObserver(() => {
                        const colors = getColors();

                        if (window.ratingChart_{{ $chartId }}) {
                            const chart = window.ratingChart_{{ $chartId }};
                            chart.options.scales.r.ticks.color = colors.ticks;
                            chart.options.scales.r.ticks.backdropColor = colors.tickBg;
                            chart.options.scales.r.pointLabels.color = colors.labels;
                            chart.options.scales.r.grid.color = colors.grid;
                            chart.options.scales.r.angleLines.color = colors.grid;
                            chart.update('active');
                        }

                        if (window.scoreChart_{{ $chartId }}) {
                            const chart = window.scoreChart_{{ $chartId }};
                            chart.options.scales.r.ticks.color = colors.ticks;
                            chart.options.scales.r.ticks.backdropColor = colors.tickBg;
                            chart.options.scales.r.pointLabels.color = colors.labels;
                            chart.options.scales.r.grid.color = colors.grid;
                            chart.options.scales.r.angleLines.color = colors.grid;
                            chart.update('active');
                        }
                    }).observe(document.documentElement, {
                        attributes: true,
                        attributeFilter: ['class']
                    });
                })();
            </script>
        @endif
    </div>
</div>
