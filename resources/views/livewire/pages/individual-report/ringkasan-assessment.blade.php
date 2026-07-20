<div class="mx-auto my-8 border border-warm-border dark:border-[#25211e] bg-white dark:bg-[#171412] overflow-hidden shadow-xs rounded-lg max-w-6xl font-sans">

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
        <table class="w-full text-sm">
            <tr>
                <td class="py-1 font-semibold text-primary-ink dark:text-neutral-200 w-36">Nomor Tes</td>
                <td class="py-1 text-primary-ink dark:text-neutral-200 w-4">:</td>
                <td class="py-1 font-mono-data text-primary-ink dark:text-neutral-200">{{ $participant->test_number }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold text-primary-ink dark:text-neutral-200">Nomor SKB</td>
                <td class="py-1 text-primary-ink dark:text-neutral-200">:</td>
                <td class="py-1 font-mono-data text-primary-ink dark:text-neutral-200">{{ $participant->skb_number }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold text-primary-ink dark:text-neutral-200">Nama</td>
                <td class="py-1 text-primary-ink dark:text-neutral-200">:</td>
                <td class="py-1 font-semibold text-primary-ink dark:text-neutral-200">{{ $participant->name }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold text-primary-ink dark:text-neutral-200">Formasi Jabatan</td>
                <td class="py-1 text-primary-ink dark:text-neutral-200">:</td>
                <td class="py-1 text-primary-ink dark:text-neutral-200">{{ $participant->positionFormation->name }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold text-primary-ink dark:text-neutral-200">Standar Penilaian</td>
                <td class="py-1 text-primary-ink dark:text-neutral-200">:</td>
                <td class="py-1 text-primary-ink dark:text-neutral-200">{{ $participant->positionFormation->template->name }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold text-primary-ink dark:text-neutral-200">Tanggal Tes</td>
                <td class="py-1 text-primary-ink dark:text-neutral-200">:</td>
                <td class="py-1 text-primary-ink dark:text-neutral-200">
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
    <div class="p-6 bg-white dark:bg-[#171412] overflow-x-auto">
        <table class="min-w-full border border-warm-border dark:border-[#25211e] text-sm">
            <thead>
                <tr class="bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-100 font-bold">
                    <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-bold text-center w-10">
                        No
                    </th>
                    <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-bold text-left w-48">
                        Aspek Penilaian
                    </th>
                    <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-bold text-center w-28">
                        Standar Skor
                    </th>
                    <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-bold text-center w-28">
                        Skor Individu
                    </th>
                    <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-bold text-center w-28">
                        Bobot Penilaian
                    </th>
                    <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-bold text-center w-36">
                        <span x-data
                            x-text="$wire.tolerancePercentage > 0 ? 'Standar Skor Akhir (-' + $wire.tolerancePercentage + '%)' : 'Standar Skor Akhir'"></span>
                    </th>
                    <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-bold text-center w-36">
                        Skor Individu Akhir
                    </th>
                    <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-bold text-center w-24">
                        Gap
                    </th>
                    <th class="border border-warm-border dark:border-[#25211e] px-4 py-2 font-bold text-center w-44">
                        Kesimpulan
                    </th>
                </tr>
            </thead>
            <tbody>
                <!-- Potensi Row -->
                @if ($potensiData)
                <tr class="hover:bg-warm-ivory/50 dark:hover:bg-[#1f1b18]/50 transition-colors duration-150 text-sm text-primary-ink dark:text-neutral-200">
                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-normal">
                        1
                    </td>
                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-left font-semibold text-primary-ink dark:text-neutral-100">
                        {{ $potensiData['category_name'] }}
                    </td>
                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">
                        {{ number_format($potensiData['total_original_standard_score'], 2, ',', '.') }}
                    </td>
                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">
                        {{ number_format($potensiData['total_individual_score'], 2, ',', '.') }}
                    </td>
                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">
                        {{ $potensiData['category_weight'] }}%
                    </td>
                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">
                        {{ number_format($potensiData['weighted_standard_score'], 2, ',', '.') }}
                    </td>
                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">
                        {{ number_format($potensiData['weighted_individual_score'], 2, ',', '.') }}
                    </td>
                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">
                        {{ number_format($potensiData['weighted_gap_score'], 2, ',', '.') }}
                    </td>
                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-semibold text-xs uppercase tracking-wider">
                        <span class="inline-block px-2.5 py-1 rounded {{ \App\Services\ConclusionService::getTailwindClass($potensiData['overall_conclusion']) }}">
                            {{ $potensiData['overall_conclusion'] }}
                        </span>
                    </td>
                </tr>
                @endif

                <!-- Kompetensi Row -->
                @if ($kompetensiData)
                <tr class="hover:bg-warm-ivory/50 dark:hover:bg-[#1f1b18]/50 transition-colors duration-150 text-sm text-primary-ink dark:text-neutral-200">
                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-normal">
                        2
                    </td>
                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-left font-semibold text-primary-ink dark:text-neutral-100">
                        {{ $kompetensiData['category_name'] }}
                    </td>
                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">
                        {{ number_format($kompetensiData['total_original_standard_score'], 2, ',', '.') }}
                    </td>
                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">
                        {{ number_format($kompetensiData['total_individual_score'], 2, ',', '.') }}
                    </td>
                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">
                        {{ $kompetensiData['category_weight'] }}%
                    </td>
                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">
                        {{ number_format($kompetensiData['weighted_standard_score'], 2, ',', '.') }}
                    </td>
                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">
                        {{ number_format($kompetensiData['weighted_individual_score'], 2, ',', '.') }}
                    </td>
                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">
                        {{ number_format($kompetensiData['weighted_gap_score'], 2, ',', '.') }}
                    </td>
                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-semibold text-xs uppercase tracking-wider">
                        <span class="inline-block px-2.5 py-1 rounded {{ \App\Services\ConclusionService::getTailwindClass($kompetensiData['overall_conclusion']) }}">
                            {{ $kompetensiData['overall_conclusion'] }}
                        </span>
                    </td>
                </tr>
                @endif

                <!-- Total Row - DARK MODE READY -->
                @if ($finalAssessmentData)
                @php
                $totalStandardSkorSebelumBobot = ($potensiData['total_original_standard_score'] ?? 0) +
                ($kompetensiData['total_original_standard_score'] ?? 0);
                $totalIndividuSkorSebelumBobot = ($potensiData['total_individual_score'] ?? 0) +
                ($kompetensiData['total_individual_score'] ?? 0);
                @endphp
                <tr class="font-bold bg-warm-ivory dark:bg-[#1f1b18] text-primary-ink dark:text-neutral-100 text-sm">
                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center" colspan="2">
                        Total Skor
                    </td>
                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">
                        {{ number_format($totalStandardSkorSebelumBobot, 2, ',', '.') }}
                    </td>
                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">
                        {{ number_format($totalIndividuSkorSebelumBobot, 2, ',', '.') }}
                    </td>
                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">
                        100%
                    </td>
                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">
                        {{ number_format($finalAssessmentData['total_standard_score'], 2, ',', '.') }}
                    </td>
                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">
                        {{ number_format($finalAssessmentData['total_individual_score'], 2, ',', '.') }}
                    </td>
                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-mono-data">
                        {{ number_format($finalAssessmentData['total_gap_score'], 2, ',', '.') }}
                    </td>
                    <td class="border border-warm-border dark:border-[#25211e] px-4 py-2 text-center font-semibold text-xs uppercase tracking-wider">
                        <span class="inline-block px-2.5 py-1 rounded {{ \App\Services\ConclusionService::getTailwindClass($finalAssessmentData['final_conclusion']) }}">
                            {{ $finalAssessmentData['final_conclusion'] }}
                        </span>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Conclusion Section - DARK MODE READY -->
    @if ($finalAssessmentData)
    <div class="p-6 bg-white dark:bg-[#171412] border-t border-warm-border dark:border-[#25211e]">
        <div class="border border-warm-border dark:border-[#25211e] rounded-lg p-5 bg-warm-ivory dark:bg-[#1f1b18] flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-xs font-bold uppercase tracking-wider text-primary-ink/70 dark:text-neutral-400 flex items-center gap-2">
                <i class="fa-solid fa-award text-accent-amber"></i>
                Kesimpulan Akhir Asesmen
            </div>
            <div>
                <span class="inline-block px-4 py-2 text-xs uppercase tracking-wider font-bold rounded {{ \App\Services\ConclusionService::getTailwindClass($this->getFinalConclusionText(), 'potensial') }}">
                    {{ $this->getFinalConclusionText() }}
                </span>
            </div>
        </div>
    </div>
    @endif
    @endif
</div>