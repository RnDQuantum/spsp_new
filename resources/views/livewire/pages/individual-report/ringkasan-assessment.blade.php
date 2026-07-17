<div class="mx-auto my-8 border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] overflow-hidden shadow-xs rounded-lg max-w-6xl">

    @if ($showHeader)
    <div class="px-8 py-6 bg-white dark:bg-[#171412] border-b border-warm-border dark:border-[#25211e]">
        <h1 class="font-display text-2xl font-bold tracking-tight text-primary-ink dark:text-neutral-100 uppercase text-center">
            Ringkasan Hasil Asesmen
        </h1>
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

    @if ($showBiodata)
    <!-- Info Section - DARK MODE READY -->
    <div class="p-6 bg-warm-ivory dark:bg-[#1f1b18] border-b border-warm-border dark:border-[#25211e]">
        <table class="w-full text-sm text-primary-ink dark:text-neutral-200">
            <tr>
                <td class="py-1 font-semibold text-primary-ink dark:text-neutral-200" style="width: 150px;">Nomor Tes</td>
                <td class="py-1 text-primary-ink dark:text-neutral-200" style="width: 20px;">:</td>
                <td class="py-1 text-primary-ink dark:text-neutral-200">{{ $participant->test_number }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold text-primary-ink dark:text-neutral-200">Nomor SKB</td>
                <td class="py-1 text-primary-ink dark:text-neutral-200">:</td>
                <td class="py-1 text-primary-ink dark:text-neutral-200">{{ $participant->skb_number }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold text-primary-ink dark:text-neutral-200">Nama</td>
                <td class="py-1 text-primary-ink dark:text-neutral-200">:</td>
                <td class="py-1 text-primary-ink dark:text-neutral-200">{{ $participant->name }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold text-primary-ink dark:text-neutral-200" style="width: 150px;">Formasi
                    Jabatan
                </td>
                <td class="py-1 text-primary-ink dark:text-neutral-200" style="width: 20px;">:</td>
                <td class="py-1 text-primary-ink dark:text-neutral-200">{{ $participant->positionFormation->name }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold text-primary-ink dark:text-neutral-200" style="width: 150px;">Standar
                    Penilaian
                </td>
                <td class="py-1 text-primary-ink dark:text-neutral-200" style="width: 20px;">:</td>
                <td class="py-1 text-primary-ink dark:text-neutral-200">
                    {{ $participant->positionFormation->template->name }}
                </td>
            </tr>
            <tr>
                <td class="font-semibold text-primary-ink dark:text-neutral-200">Tanggal Tes</td>
                <td class=" text-primary-ink dark:text-neutral-200">:</td>
                <td class=" text-primary-ink dark:text-neutral-200">
                    {{ \Carbon\Carbon::parse($participant->assessment_date)->translatedFormat('d F Y') }}

                </td>
            </tr>
        </table>

        {{-- Adjustment Indicators --}}
        <div class="mt-4 flex flex-wrap gap-2">
            <x-adjustment-indicator :template-id="$participant->positionFormation->template_id" category-code="potensi"
                size="sm" custom-label="Standar Potensi Disesuaikan" />
            <x-adjustment-indicator :template-id="$participant->positionFormation->template_id"
                category-code="kompetensi" size="sm" custom-label="Standar Kompetensi Disesuaikan" />
        </div>
    </div>
    @endif

    @if ($showTable)
    <!-- Table Section - DARK MODE READY -->
    <div class=" bg-black dark:bg-gray-800 overflow-x-auto">
        <table class="min-w-full border border-warm-border dark:border-[#25211e]/40 text-sm text-primary-ink dark:text-neutral-200">
            <thead>
                <tr class="bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-200">
                    <th class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 font-bold text-center text-black dark:text-white"
                        style="width: 40px;">NO</th>
                    <th class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 font-bold text-center text-black dark:text-white"
                        style="width: 180px;">
                        ASPEK PENILAIAN
                    </th>
                    <th class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 font-bold text-center text-black dark:text-white"
                        style="width: 100px;">
                        Standar Skor
                    </th>
                    <th class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 font-bold text-center text-black dark:text-white"
                        style="width: 100px;">
                        Skor Individu
                    </th>
                    <th class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 font-bold text-center text-black dark:text-white"
                        style="width: 100px;">
                        Bobot Penilaian
                    </th>
                    <th class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 font-bold text-center text-black dark:text-white"
                        style="width: 100px;">
                        <span x-data
                            x-text="$wire.tolerancePercentage > 0 ? 'Standar Skor Akhir (-' + $wire.tolerancePercentage + '%)' : 'Standar Skor Akhir'"></span>
                    </th>
                    <th class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 font-bold text-center text-black dark:text-white"
                        style="width: 100px;">
                        Skor Individu Akhir
                    </th>
                    <th class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 font-bold text-center text-black dark:text-white"
                        style="width: 80px;">
                        GAP
                    </th>
                    <th class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 font-bold text-center text-black dark:text-white"
                        style="width: 180px;">
                        Kesimpulan
                    </th>
                </tr>
            </thead>
            <tbody>
                <!-- Potensi Row -->
                @if ($potensiData)
                <tr class="hover:bg-warm-ivory/50 dark:hover:bg-[#1f1b18]/50 transition-colors duration-150 text-sm text-primary-ink dark:text-neutral-200">
                    <td
                        class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 text-center text-primary-ink dark:text-neutral-200 bg-white dark:bg-[#171412]">
                        1</td>
                    <td
                        class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 text-primary-ink dark:text-neutral-200 bg-white dark:bg-[#171412]">
                        {{ $potensiData['category_name'] }}</td>
                    <td
                        class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 text-center text-primary-ink dark:text-neutral-200 bg-white dark:bg-[#171412]">
                        {{ number_format($potensiData['total_original_standard_score'], 2, ',', '.') }}
                    </td>
                    <td
                        class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 text-center text-primary-ink dark:text-neutral-200 bg-white dark:bg-[#171412]">
                        {{ number_format($potensiData['total_individual_score'], 2, ',', '.') }}
                    </td>
                    <td
                        class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 text-center text-primary-ink dark:text-neutral-200 bg-white dark:bg-[#171412]">
                        {{ $potensiData['category_weight'] }}%
                    </td>
                    <td
                        class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 text-center text-primary-ink dark:text-neutral-200 bg-white dark:bg-[#171412]">
                        {{ number_format($potensiData['weighted_standard_score'], 2, ',', '.') }}
                    </td>
                    <td
                        class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 text-center text-primary-ink dark:text-neutral-200 bg-white dark:bg-[#171412]">
                        {{ number_format($potensiData['weighted_individual_score'], 2, ',', '.') }}
                    </td>
                    <td
                        class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 text-center text-primary-ink dark:text-neutral-200 bg-white dark:bg-[#171412]">
                        {{ number_format($potensiData['weighted_gap_score'], 2, ',', '.') }}
                    </td>
                    <td
                        class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 text-center font-bold {{ \App\Services\ConclusionService::getTailwindClass($potensiData['overall_conclusion']) }}">
                        {{ $potensiData['overall_conclusion'] }}
                    </td>
                </tr>
                @endif

                <!-- Kompetensi Row -->
                @if ($kompetensiData)
                <tr class="hover:bg-warm-ivory/50 dark:hover:bg-[#1f1b18]/50 transition-colors duration-150 text-sm text-primary-ink dark:text-neutral-200">
                    <td
                        class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 text-center text-primary-ink dark:text-neutral-200 bg-white dark:bg-[#171412]">
                        2</td>
                    <td
                        class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 text-primary-ink dark:text-neutral-200 bg-white dark:bg-[#171412]">
                        {{ $kompetensiData['category_name'] }}</td>
                    <td
                        class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 text-center text-primary-ink dark:text-neutral-200 bg-white dark:bg-[#171412]">
                        {{ number_format($kompetensiData['total_original_standard_score'], 2, ',', '.') }}
                    </td>
                    <td
                        class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 text-center text-primary-ink dark:text-neutral-200 bg-white dark:bg-[#171412]">
                        {{ number_format($kompetensiData['total_individual_score'], 2, ',', '.') }}
                    </td>
                    <td
                        class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 text-center text-primary-ink dark:text-neutral-200 bg-white dark:bg-[#171412]">
                        {{ $kompetensiData['category_weight'] }}%
                    </td>
                    <td
                        class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 text-center text-primary-ink dark:text-neutral-200 bg-white dark:bg-[#171412]">
                        {{ number_format($kompetensiData['weighted_standard_score'], 2, ',', '.') }}
                    </td>
                    <td
                        class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 text-center text-primary-ink dark:text-neutral-200 bg-white dark:bg-[#171412]">
                        {{ number_format($kompetensiData['weighted_individual_score'], 2, ',', '.') }}
                    </td>
                    <td
                        class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 text-center text-primary-ink dark:text-neutral-200 bg-white dark:bg-[#171412]">
                        {{ number_format($kompetensiData['weighted_gap_score'], 2, ',', '.') }}
                    </td>
                    <td
                        class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 text-center font-bold {{ \App\Services\ConclusionService::getTailwindClass($kompetensiData['overall_conclusion']) }}">
                        {{ $kompetensiData['overall_conclusion'] }}
                    </td>
                </tr>
                @endif

                <!-- Total Row - DARK MODE READY -->
                @if ($finalAssessmentData)
                @php
                // Total = Sum of weighted scores (already calculated in service)
                $totalStandardSkorSebelumBobot = ($potensiData['total_original_standard_score'] ?? 0) +
                ($kompetensiData['total_original_standard_score'] ?? 0);
                $totalIndividuSkorSebelumBobot = ($potensiData['total_individual_score'] ?? 0) +
                ($kompetensiData['total_individual_score'] ?? 0);
                @endphp
                <tr class="bg-gray-300 dark:bg-gray-600 text-black dark:text-white">
                    <td class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 text-center" colspan="2"><strong>TOTAL
                            SKOR</strong></td>
                    <td class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 text-center">
                        <strong>
                            {{ number_format($totalStandardSkorSebelumBobot, 2, ',', '.') }}
                        </strong>
                    </td>
                    <td class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 text-center">
                        <strong>
                            {{ number_format($totalIndividuSkorSebelumBobot, 2, ',', '.') }}
                        </strong>
                    </td>
                    <td class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 text-center"><strong>100%</strong></td>
                    <td class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 text-center">
                        <strong>{{ number_format($finalAssessmentData['total_standard_score'], 2, ',', '.') }}</strong>
                    </td>
                    <td class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 text-center">
                        <strong>{{ number_format($finalAssessmentData['total_individual_score'], 2, ',', '.')
                            }}</strong>
                    </td>
                    <td class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 text-center">
                        <strong>
                            {{ number_format($finalAssessmentData['total_gap_score'], 2, ',', '.') }}
                        </strong>
                    </td>
                    <td
                        class="border border-warm-border dark:border-[#25211e]/40 px-3 py-3 text-center font-bold {{ \App\Services\ConclusionService::getTailwindClass($finalAssessmentData['final_conclusion']) }}">
                        {{ $finalAssessmentData['final_conclusion'] }}
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Conclusion Section - DARK MODE READY -->
    @if ($finalAssessmentData)
    <div class="mt-6 bg-white dark:bg-[#171412]">
        <table class="min-w-full border border-warm-border dark:border-[#25211e]/40">
            <tr>
                <td class="border border-warm-border dark:border-[#25211e]/40 px-4 py-4 font-bold text-center text-primary-ink dark:text-neutral-200 bg-white dark:bg-[#171412]"
                    style="width: 200px;">
                    KESIMPULAN :
                </td>
                <td
                    class="border border-warm-border dark:border-[#25211e]/40 px-4 py-4 text-center font-bold text-lg {{ \App\Services\ConclusionService::getTailwindClass($this->getFinalConclusionText(), 'potensial') }}">
                    {{ $this->getFinalConclusionText() }}
                </td>
            </tr>
        </table>
    </div>
    @endif
    @endif
</div>