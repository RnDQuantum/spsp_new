<div>
    <div class="mx-auto my-8 border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] overflow-hidden shadow-xs rounded-lg" style="max-width: 1400px;">

        @if ($showHeader)
            <!-- Header - DARK MODE READY -->
                        <div class="px-8 py-6 bg-white dark:bg-[#171412] border-b border-warm-border dark:border-[#25211e]">
                <h1 class="font-display text-2xl font-bold tracking-tight text-primary-ink dark:text-neutral-100 uppercase">
                    Managerial Competency Mapping
                </h1>
                <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-xs font-semibold text-primary-ink/75 dark:text-neutral-400">
                    <span class="flex items-center gap-1.5"><i class="fa-regular fa-user text-accent-amber"></i> {{ $participant->name }}</span>
                    <span class="flex items-center gap-1.5"><i class="fa-regular fa-calendar text-accent-amber"></i> {{ $participant->event->name }}</span>
                    <span class="flex items-center gap-1.5"><i class="fa-regular fa-address-card text-accent-amber"></i> {{ $participant->positionFormation->name }} - {{ $participant->positionFormation->template->name }}</span>
                </div>
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

            {{-- Adjustment Indicator --}}
            <div class="px-6 py-3 bg-warm-ivory dark:bg-[#1f1b18] border-b border-warm-border dark:border-[#25211e]">
                <x-adjustment-indicator
                    :template-id="$participant->positionFormation->template_id"
                    category-code="kompetensi"
                    size="sm"
                />
            </div>
        @endif

        @if ($showTable)
            <!-- Table Section - DARK MODE READY -->
            <div class="p-6 overflow-x-auto bg-white dark:bg-[#171412]">
                <table class="min-w-full border border-warm-border dark:border-[#25211e] text-sm">
                    <thead>
                        <tr class="bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200 text-primary-ink dark:text-neutral-200">
                            <th class="border border-warm-border dark:border-[#25211e] px-3 py-2 font-semibold 
                                   bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200"
                                rowspan="2">
                                No</th>
                            <th class="border border-warm-border dark:border-[#25211e] px-3 py-2 font-semibold 
                                   bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200"
                                rowspan="2">
                                Atribut</th>
                            <th class="border border-warm-border dark:border-[#25211e] px-3 py-2 font-semibold 
                                   bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200"
                                rowspan="2">
                                Bobot %<br>100</th>
                            <th class="border border-warm-border dark:border-[#25211e] px-3 py-2 font-semibold 
                                   bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200"
                                colspan="2">
                                <span x-data
                                    x-text="$wire.tolerancePercentage > 0 ? 'Standard (-' + $wire.tolerancePercentage + '%)' : 'Standard'"
                                    class="text-primary-ink dark:text-neutral-200"></span>
                            </th>
                            <th class="border border-warm-border dark:border-[#25211e] px-3 py-2 font-semibold 
                                   bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200"
                                colspan="2">Individu</th>
                            <th class="border border-warm-border dark:border-[#25211e] px-3 py-2 font-semibold 
                                   bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200"
                                colspan="2">Gap</th>
                            <th class="border border-warm-border dark:border-[#25211e] px-3 py-2 font-semibold 
                                   bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200"
                                rowspan="2">
                                Persentase<br>Kesesuaian</th>
                            <th class="border border-warm-border dark:border-[#25211e] px-3 py-2 font-semibold 
                                   bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200"
                                rowspan="2">
                                Kesimpulan</th>
                        </tr>
                        <tr class="bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200 text-primary-ink dark:text-neutral-200">
                            <th
                                class="border border-warm-border dark:border-[#25211e] px-3 py-1 font-semibold 
                                   bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200">
                                Rating</th>
                            <th
                                class="border border-warm-border dark:border-[#25211e] px-3 py-1 font-semibold 
                                   bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200">
                                Skor</th>
                            <th
                                class="border border-warm-border dark:border-[#25211e] px-3 py-1 font-semibold 
                                   bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200">
                                Rating</th>
                            <th
                                class="border border-warm-border dark:border-[#25211e] px-3 py-1 font-semibold 
                                   bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200">
                                Skor</th>
                            <th
                                class="border border-warm-border dark:border-[#25211e] px-3 py-1 font-semibold 
                                   bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200">
                                Rating</th>
                            <th
                                class="border border-warm-border dark:border-[#25211e] px-3 py-0 font-semibold 
                                   bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200">
                                Skor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($aspectsData as $index => $aspect)
                            <tr class="bg-white dark:bg-[#171412] hover:bg-warm-ivory/50 dark:hover:bg-[#1f1b18]/50 transition-colors duration-150 text-primary-ink dark:text-neutral-200">
                                <td
                                    class="border border-warm-border dark:border-[#25211e] px-3 py-2 text-center 
                                       text-primary-ink dark:text-neutral-200">
                                    {{ ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII', 'XIII', 'XIV', 'XV', 'XVI', 'XVII', 'XVIII', 'XIX', 'XX'][$index] }}
                                </td>
                                <td
                                    class="border border-warm-border dark:border-[#25211e] px-3 py-2 
                                       text-primary-ink dark:text-neutral-200">
                                    {{ $aspect['name'] }}</td>
                                <td
                                    class="border border-warm-border dark:border-[#25211e] px-3 py-2 text-center 
                                       text-primary-ink dark:text-neutral-200">
                                    {{ $aspect['weight_percentage'] }}</td>
                                <td
                                    class="border border-warm-border dark:border-[#25211e] px-3 py-2 text-center 
                                       text-primary-ink dark:text-neutral-200">
                                    {{ number_format($aspect['standard_rating'], 2) }}</td>
                                <td
                                    class="border border-warm-border dark:border-[#25211e] px-3 py-2 text-center 
                                       text-primary-ink dark:text-neutral-200">
                                    {{ number_format($aspect['standard_score'], 2) }}</td>
                                <td
                                    class="border border-warm-border dark:border-[#25211e] px-3 py-2 text-center 
                                       text-primary-ink dark:text-neutral-200">
                                    {{ number_format($aspect['individual_rating'], 2) }}</td>
                                <td
                                    class="border border-warm-border dark:border-[#25211e] px-3 py-2 text-center 
                                       text-primary-ink dark:text-neutral-200">
                                    {{ number_format($aspect['individual_score'], 2) }}</td>
                                <td
                                    class="border border-warm-border dark:border-[#25211e] px-3 py-2 text-center 
                                       text-primary-ink dark:text-neutral-200">
                                    {{ number_format($aspect['gap_rating'], 2) }}</td>
                                <td
                                    class="border border-warm-border dark:border-[#25211e] px-3 py-2 text-center 
                                       text-primary-ink dark:text-neutral-200">
                                    {{ number_format($aspect['gap_score'], 2) }}</td>
                                <td
                                    class="border border-warm-border dark:border-[#25211e] px-3 py-2 text-center 
                                       text-primary-ink dark:text-neutral-200">
                                    @php
                                        $percentage =
                                            $aspect['standard_score'] > 0
                                                ? round(($aspect['individual_score'] / $aspect['standard_score']) * 100)
                                                : 0;
                                    @endphp
                                    {{ $percentage }}%
                                </td>
                                <td
                                    class="border border-warm-border dark:border-[#25211e] px-3 py-2 text-center
                                @php
$c = trim(strtoupper($aspect['conclusion_text'])); @endphp

                                @if ($c === 'DI ATAS STANDAR') bg-green-600 text-white font-bold
                                @elseif ($c === 'MEMENUHI STANDAR') bg-yellow-400 text-gray-900 font-bold
                                @elseif ($c === 'DI BAWAH STANDAR') bg-red-600 text-white font-bold
                                @else bg-gray-200 dark:bg-gray-600 text-primary-ink dark:text-neutral-200 @endif">
                                    {{ $aspect['conclusion_text'] }}
                                </td>
                            </tr>
                        @endforeach

                        <!-- Total Rating Row -->
                        <tr class="font-bold bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200">
                            <td class="border border-warm-border dark:border-[#25211e] px-3 py-2 text-center 
                                   text-primary-ink dark:text-neutral-200"
                                colspan="3">Total Rating</td>
                            <td
                                class="border border-warm-border dark:border-[#25211e] px-3 py-2 text-center 
                                   text-primary-ink dark:text-neutral-200">
                                {{ number_format($totalStandardRating, 2) }}
                            </td>
                            <td class="border border-warm-border dark:border-[#25211e]/40 px-3 py-2 bg-warm-border/40 dark:bg-[#25211e]/40"></td>
                            <td
                                class="border border-warm-border dark:border-[#25211e] px-3 py-2 text-center 
                                   text-primary-ink dark:text-neutral-200">
                                {{ number_format($totalIndividualRating, 2) }}</td>
                            <td class="border border-warm-border dark:border-[#25211e]/40 px-3 py-2 bg-warm-border/40 dark:bg-[#25211e]/40"></td>
                            <td
                                class="border border-warm-border dark:border-[#25211e] px-3 py-2 text-center 
                                   text-primary-ink dark:text-neutral-200">
                                {{ number_format($totalGapRating, 2) }}</td>
                            <td class="border border-warm-border dark:border-[#25211e]/40 px-3 py-2 bg-warm-border/40 dark:bg-[#25211e]/40"></td>
                            <td class="border border-warm-border dark:border-[#25211e] px-3 py-2 text-center font-bold
                                @php
$c = trim(strtoupper($overallConclusion)); @endphp
                                @if ($c === 'DI ATAS STANDAR') bg-green-600 text-white
                                @elseif ($c === 'MEMENUHI STANDAR') bg-yellow-400 text-gray-900
                                @elseif ($c === 'DI BAWAH STANDAR') bg-red-600 text-white
                                @else bg-gray-200 dark:bg-gray-600 text-primary-ink dark:text-neutral-200 @endif"
                                colspan="2">
                                {{ $overallConclusion }}
                            </td>
                        </tr>

                        <!-- Total Score Row -->
                        <tr class="font-bold bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200">
                            <td class="border border-warm-border dark:border-[#25211e] px-3 py-2 text-center 
                                   text-primary-ink dark:text-neutral-200"
                                colspan="3">Total Skor</td>
                            <td class="border border-warm-border dark:border-[#25211e]/40 px-3 py-2 bg-warm-border/40 dark:bg-[#25211e]/40"></td>
                            <td
                                class="border border-warm-border dark:border-[#25211e] px-3 py-2 text-center 
                                   text-primary-ink dark:text-neutral-200">
                                {{ number_format($totalStandardScore, 2) }}
                            </td>
                            <td class="border border-warm-border dark:border-[#25211e]/40 px-3 py-2 bg-warm-border/40 dark:bg-[#25211e]/40"></td>
                            <td
                                class="border border-warm-border dark:border-[#25211e] px-3 py-2 text-center 
                                   text-primary-ink dark:text-neutral-200">
                                {{ number_format($totalIndividualScore, 2) }}</td>
                            <td class="border border-warm-border dark:border-[#25211e]/40 px-3 py-2 bg-warm-border/40 dark:bg-[#25211e]/40"></td>
                            <td
                                class="border border-warm-border dark:border-[#25211e] px-3 py-2 text-center
                                   text-primary-ink dark:text-neutral-200">
                                {{ number_format($totalGapScore, 2) }}</td>
                            <td class="border border-warm-border dark:border-[#25211e] px-3 py-2 text-center font-bold
                                @php
$c = trim(strtoupper($overallConclusion)); @endphp
                                @if ($c === 'DI ATAS STANDAR') bg-green-600 text-white
                                @elseif ($c === 'MEMENUHI STANDAR') bg-yellow-400 text-gray-900
                                @elseif ($c === 'DI BAWAH STANDAR') bg-red-600 text-white
                                @else bg-gray-200 dark:bg-gray-600 text-primary-ink dark:text-neutral-200 @endif"
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
                <div class="px-6 py-4 bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200 border-t-2 border-warm-border dark:border-[#25211e] dark:border-gray-600">
                    <div class="max-w-4xl mx-auto">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                            <svg class="w-6 h-6 text-accent-amber dark:text-amber-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z">
                                </path>
                            </svg>
                            Peringkat Peserta - Kategori Kompetensi
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Ranking Card -->
                            <div
                                class="bg-white dark:bg-[#171412] border border-warm-border dark:border-[#25211e] rounded-md p-4 text-center">
                                <div class="text-sm text-primary-ink/70 dark:text-neutral-400 mb-2">Ranking</div>
                                <div class="text-4xl font-bold text-accent-amber dark:text-amber-500">
                                    #{{ $rankingInfo['rank'] }}
                                </div>
                            </div>

                            <!-- Total Participants Card -->
                            <div
                                class="bg-white dark:bg-[#171412] border border-warm-border dark:border-[#25211e] rounded-md p-4 text-center">
                                <div class="text-sm text-primary-ink/70 dark:text-neutral-400 mb-2">Total Peserta</div>
                                <div class="text-4xl font-bold text-accent-amber dark:text-amber-500">
                                    {{ $rankingInfo['total'] }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $participant->positionFormation->name }}
                                </div>
                            </div>

                            <!-- Conclusion Card -->
                            <div
                                class="bg-white dark:bg-[#171412] border rounded-md p-4 text-center
                                @php
$conclusion = strtoupper(trim($rankingInfo['conclusion'])); @endphp
                                @if ($conclusion === 'DI ATAS STANDAR') border-green-300 dark:border-green-600
                                @elseif ($conclusion === 'MEMENUHI STANDAR')
                                    border-yellow-300 dark:border-yellow-600
                                @else
                                    border-red-300 dark:border-red-600 @endif
                            ">
                                <div class="text-sm text-primary-ink/70 dark:text-neutral-400 mb-2">Status</div>
                                <div
                                    class="text-base font-bold px-3 py-2 rounded-lg
                                    @if ($conclusion === 'DI ATAS STANDAR') bg-green-600 text-white
                                    @elseif ($conclusion === 'MEMENUHI STANDAR')
                                        bg-yellow-400 text-gray-900
                                    @else
                                        bg-red-600 text-white @endif
                                ">
                                    {{ $rankingInfo['conclusion'] }}
                                </div>
                            </div>
                        </div>

                        <!-- Additional Info -->
                        <div class="mt-4 bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200 rounded-lg p-1">
                            <div class="text-sm text-gray-800 dark:text-gray-200">
                                <strong>Keterangan:</strong> Ranking berdasarkan total Individual Score dari semua aspek
                                dalam kategori Kompetensi,
                                dibandingkan dengan peserta lain di event yang sama pada posisi
                                <strong>{{ $participant->positionFormation->name }}</strong>.
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        @if ($showRatingChart)
            <!-- Chart Section Rating - DARK MODE READY -->
            <div class="p-6 border-t-2 border-warm-border dark:border-[#25211e] bg-white dark:bg-gray-800" wire:ignore
                id="chart-rating-{{ $chartId }}">
                <div class="text-center text-base font-bold mb-6 
                       text-primary-ink dark:text-neutral-200">
                    Profil Kompetensi Spider Plot Chart (Rating)</div>


                <!-- Legend Rating - DARK MODE READY -->
                <!-- GANTI URUTAN LEGEND INI -->
                <div class="flex justify-center text-sm gap-2 mb-8" id="rating-legend-{{ $chartId }}">
                    <!-- Dataset 0: Peserta - HIJAU -->
                    <span
                        class="legend-item flex items-center gap-2 cursor-pointer select-none 
           hover:bg-gray-100 dark:hover:bg-gray-600 
           px-3 py-2 rounded-lg transition-all duration-200 
           border border-gray-300 dark:border-gray-600 
           shadow-sm bg-white dark:bg-gray-700 
           text-primary-ink dark:text-neutral-200"
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
           text-primary-ink dark:text-neutral-200"
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
           text-primary-ink dark:text-neutral-200"
                        data-chart="rating" data-dataset="2">
                        <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #fafa05;"></span>
                        <span class="font-semibold" x-data
                            x-text="'Tolerance ' + $wire.tolerancePercentage + '%'"></span>
                    </span>
                </div>


                <div class="flex justify-center mb-2">
                    <div style="width: 100%; max-width: 1100px; height: 600px; position: relative;">
                        <canvas id="spiderRatingChart-{{ $chartId }}"></canvas>
                    </div>
                </div>


            </div>
        @endif

        @if ($showScoreChart)
            <!-- Chart Section Score - DARK MODE READY -->
            <div class="p-6 border-t-2 border-warm-border dark:border-[#25211e] bg-white dark:bg-gray-800" wire:ignore
                id="chart-score-{{ $chartId }}">
                <div
                    class="text-center text-base font-bold mb-6 
                       text-primary-ink dark:text-neutral-200">
                    Profil Kompetensi Spider Plot Chart (Score)</div>


                <!-- Legend Score - DARK MODE READY -->
                <!-- GANTI URUTAN LEGEND INI -->
                <div class="flex justify-center text-sm gap-2 mb-8" id="score-legend-{{ $chartId }}">
                    <!-- Dataset 0: Peserta - HIJAU -->
                    <span
                        class="legend-item flex items-center gap-2 cursor-pointer select-none 
           hover:bg-gray-100 dark:hover:bg-gray-600 
           px-3 py-2 rounded-lg transition-all duration-200 
           border border-gray-300 dark:border-gray-600 
           shadow-sm bg-white dark:bg-gray-700 
           text-primary-ink dark:text-neutral-200"
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
           text-primary-ink dark:text-neutral-200"
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
           text-primary-ink dark:text-neutral-200"
                        data-chart="score" data-dataset="2">
                        <span class="inline-block w-12 h-3 rounded-sm" style="background-color: #fafa05;"></span>
                        <span class="font-semibold" x-data
                            x-text="'Tolerance ' + $wire.tolerancePercentage + '%'"></span>
                    </span>
                </div>


                <div class="flex justify-center mb-2">
                    <div style="width: 100%; max-width: 1100px; height: 600px; position: relative;">
                        <canvas id="spiderScoreChart-{{ $chartId }}"></canvas>
                    </div>
                </div>


            </div>
        @endif

        @if ($showRatingChart || $showScoreChart)
            <!-- 1 SCRIPT FULL - DARK MODE CHARTS -->
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

                    // 🌙 DARK MODE COLORS
                    // 🌙 DARK MODE COLORS
                    const getColors = () => {
                        const dark = document.documentElement.classList.contains('dark');
                        return {
                            grid: dark ? 'rgba(255, 255, 255, 0.15)' : 'rgba(0, 0, 0, 0.15)', // Lembut
                            angleLines: dark ? 'rgba(255, 255, 255, 0.15)' :
                            'rgba(0, 0, 0, 0.15)', // Lembut
                            ticks: dark ? '#ffffff' : '#000000', // Warna solid
                            labels: dark ? '#d1d5db' : '#000000', // Warna solid (konsisten dengan dashboard)
                            tickBg: 'transparent' // Tambahkan property untuk background label
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

                    // RATING CHART SETUP
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
                                                size: 16,
                                                family: "'Instrument Sans', sans-serif"
                                            },
                                            backdropColor: colors.tickBg,
                                            showLabelBackdrop: false,
                                            z: 2
                                        },
                                        pointLabels: {
                                            font: {
                                                size: 16,
                                                family: "'Instrument Sans', sans-serif"
                                            },
                                            color: colors.labels,
                                            z: 3
                                        },
                                        grid: {
                                            color: colors.grid,
                                            z: 1
                                        },
                                        angleLines: {
                                            color: colors.angleLines,
                                            z: 1
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
                                    ctx.font = scale.options.ticks.font.size + "px 'Instrument Sans', sans-serif";
                                    ctx.fillStyle = scale.options.ticks.color || '#000';
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle';

                                    const offsetX = 10;
                                    const offsetY = 0;

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

                    // SCORE CHART SETUP
                    function setupScoreChart() {
                        if (window.scoreChart_{{ $chartId }}) window.scoreChart_{{ $chartId }}.destroy();

                        const chartLabels = @js($chartLabels);
                        const originalStandardScores = @js($chartOriginalStandardScores);
                        const standardScores = @js($chartStandardScores);
                        const individualScores = @js($chartIndividualScores);
                        const participantName = @js($participant->name);
                        const tolerancePercentage = @js($tolerancePercentage);
                        const rawMaxScore = Math.max(...originalStandardScores, ...individualScores, ...standardScores) * 1.2;
                        const maxScore = Math.ceil(rawMaxScore / 20) * 20;

                        const canvas = document.getElementById('spiderScoreChart-{{ $chartId }}');
                        if (!canvas) return;
                        const ctx = canvas.getContext('2d');
                        const colors = getColors();

                        const datasets = [{
                                // LAYER 1: PESERTA (HIJAU) - Dataset 0
                                label: participantName,
                                data: individualScores,
                                fill: true,
                                backgroundColor: '#5db010', // ← UBAH dari rgba atau sudah solid
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
                                backgroundColor: '#b50505', // ← PASTIKAN SOLID
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
                                backgroundColor: '#fafa05', // ← PASTIKAN SOLID
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
                                                size: 16,
                                                family: "'Instrument Sans', sans-serif"
                                            },
                                            backdropColor: colors.tickBg,
                                            showLabelBackdrop: false,
                                            z: 2,
                                            callback: function(value) {
                                                return Number.isInteger(value) ? value : null;
                                            }
                                        },
                                        pointLabels: {
                                            font: {
                                                size: 16,
                                                family: "'Instrument Sans', sans-serif"
                                            },
                                            color: colors.labels,
                                            z: 3
                                        },
                                        grid: {
                                            color: colors.grid,
                                            z: 1
                                        },
                                        angleLines: {
                                            color: colors.angleLines,
                                            z: 1
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
                                    ctx.font = scale.options.ticks.font.size + "px 'Instrument Sans', sans-serif";
                                    ctx.fillStyle = scale.options.ticks.color || '#000';
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle';

                                    const offsetX = 10;
                                    const offsetY = 0;

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
                                chart.options.scales.r.pointLabels.color = colors.labels;
                                chart.options.scales.r.grid.color = colors.grid;
                                chart.options.scales.r.angleLines.color = colors.grid;

                                chart.update('active');
                                updateLegendVisual('score');
                            }
                        });
                    });

                    // 🌙 DARK MODE LISTENER - BOTH CHARTS
                    new MutationObserver(() => {
                        const colors = getColors();

                        if (window.ratingChart_{{ $chartId }}) {
                            const chart = window.ratingChart_{{ $chartId }};
                            chart.options.scales.r.ticks.color = colors.ticks;
                            chart.options.scales.r.pointLabels.color = colors.labels;
                            chart.options.scales.r.grid.color = colors.grid;
                            chart.options.scales.r.angleLines.color = colors.grid;
                            chart.update('active');
                        }

                        if (window.scoreChart_{{ $chartId }}) {
                            const chart = window.scoreChart_{{ $chartId }};
                            chart.options.scales.r.ticks.color = colors.ticks;
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
