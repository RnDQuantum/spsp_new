<div class="bg-white mx-auto my-8 shadow overflow-hidden" style="max-width: 1200px;">
    <!-- Header -->
    <div class="border-b-4 border-black py-4 bg-white">
        <h1 class="text-center text-2xl font-bold uppercase tracking-wide text-black">
            RINGKASAN HASIL ASSESSMENT
        </h1>
    </div>

    <!-- Tolerance Selector Component -->
    @php
        $summary = $this->getPassingSummary();
    @endphp
    @livewire('components.tolerance-selector', [
        'passing' => $summary['passing'],
        'total' => $summary['total'],
    ])

    <!-- Info Section -->
    <div class="p-6 bg-gray-100">
        <table class="w-full text-sm text-gray-900">
            <tr>
                <td class="py-2 font-semibold" style="width: 150px;">Nomor Tes</td>
                <td class="py-2" style="width: 20px;">:</td>
                <td class="py-2">{{ $participant->test_number }}</td>
            </tr>
            <tr>
                <td class="py-2 font-semibold">Nomor SKB</td>
                <td class="py-2">:</td>
                <td class="py-2">{{ $participant->skb_number }}</td>
            </tr>
            <tr>
                <td class="py-2 font-semibold">Nama</td>
                <td class="py-2">:</td>
                <td class="py-2">{{ $participant->name }}</td>
            </tr>
        </table>

        <div class="mt-4"></div>

        <table class="w-full text-sm text-gray-900">
            <tr>
                <td class="py-2 font-semibold" style="width: 150px;">Formasi Jabatan</td>
                <td class="py-2" style="width: 20px;">:</td>
                <td class="py-2">{{ $participant->positionFormation->name }}</td>
            </tr>
        </table>

        <div class="mt-4"></div>

        <table class="w-full text-sm text-gray-900">
            <tr>
                <td class="py-2 font-semibold" style="width: 150px;">Standar Penilaian</td>
                <td class="py-2" style="width: 20px;">:</td>
                <td class="py-2">{{ $participant->positionFormation->template->name }}</td>
            </tr>
            <tr>
                <td class="py-2 font-semibold">Tanggal Tes</td>
                <td class="py-2">:</td>
                <td class="py-2">{{ $participant->assessment_date->format('d F Y') }}</td>
            </tr>
        </table>
    </div>

    <!-- Table Section -->
    <div class="px-6 pb-6 bg-white overflow-x-auto">
        <table class="min-w-full border-2 border-black text-sm text-gray-900 mt-6">
            <thead>
                <tr class="bg-cyan-200">
                    <th class="border-2 border-black px-3 py-3 font-bold text-center" style="width: 40px;">NO</th>
                    <th class="border-2 border-black px-3 py-3 font-bold text-center" style="width: 180px;">
                        ASPEK PENILAIAN
                    </th>
                    <th class="border-2 border-black px-3 py-3 font-bold text-center" style="width: 100px;">
                        Standar Rating
                    </th>
                    <th class="border-2 border-black px-3 py-3 font-bold text-center" style="width: 100px;">
                        Rating Individu
                    </th>
                    <th class="border-2 border-black px-3 py-3 font-bold text-center" style="width: 100px;">
                        Bobot Penilaian
                    </th>
                    <th class="border-2 border-black px-3 py-3 font-bold text-center" style="width: 100px;">
                        <span x-data x-text="$wire.tolerancePercentage > 0 ? 'Standar Skor Akhir (-' + $wire.tolerancePercentage + '%)' : 'Standar Skor Akhir'"></span>
                    </th>
                    <th class="border-2 border-black px-3 py-3 font-bold text-center" style="width: 100px;">
                        Skor Individu Akhir
                    </th>
                    <th class="border-2 border-black px-3 py-3 font-bold text-center" style="width: 80px;">
                        GAP
                    </th>
                    <th class="border-2 border-black px-3 py-3 font-bold text-center" style="width: 180px;">
                        Kesimpulan
                    </th>
                </tr>
            </thead>
            <tbody>
                <!-- Potensi Row -->
                @if ($potensiAssessment && $potensiCategory)
                    <tr>
                        <td class="border-2 border-black px-3 py-3 text-center bg-white">1</td>
                        <td class="border-2 border-black px-3 py-3 bg-white">{{ $potensiCategory->name }}</td>
                        <td class="border-2 border-black px-3 py-3 text-center bg-white">
                            {{ number_format($potensiAssessment->total_standard_rating, 2, ',', '.') }}
                        </td>
                        <td class="border-2 border-black px-3 py-3 text-center bg-white">
                            {{ number_format($potensiAssessment->total_individual_rating, 2, ',', '.') }}
                        </td>
                        <td class="border-2 border-black px-3 py-3 text-center bg-white">
                            {{ $potensiCategory->weight_percentage }}%
                        </td>
                        <td class="border-2 border-black px-3 py-3 text-center bg-white">
                            {{ number_format($this->getAdjustedPotensiStandardScore(), 2, ',', '.') }}
                        </td>
                        <td class="border-2 border-black px-3 py-3 text-center bg-white">
                            {{ number_format($potensiAssessment->total_individual_score, 2, ',', '.') }}
                        </td>
                        <td class="border-2 border-black px-3 py-3 text-center bg-white">
                            {{ number_format($this->getAdjustedPotensiGap(), 2, ',', '.') }}
                        </td>
                        <td class="border-2 border-black px-3 py-3 text-center font-bold {{ $this->getConclusionColorClass($this->getPotensiConclusion()) }}">
                            {{ $this->getPotensiConclusion() }}
                        </td>
                    </tr>
                @endif

                <!-- Kompetensi Row -->
                @if ($kompetensiAssessment && $kompetensiCategory)
                    <tr>
                        <td class="border-2 border-black px-3 py-3 text-center bg-white">2</td>
                        <td class="border-2 border-black px-3 py-3 bg-white">{{ $kompetensiCategory->name }}</td>
                        <td class="border-2 border-black px-3 py-3 text-center bg-white">
                            {{ number_format($kompetensiAssessment->total_standard_rating, 2, ',', '.') }}
                        </td>
                        <td class="border-2 border-black px-3 py-3 text-center bg-white">
                            {{ number_format($kompetensiAssessment->total_individual_rating, 2, ',', '.') }}
                        </td>
                        <td class="border-2 border-black px-3 py-3 text-center bg-white">
                            {{ $kompetensiCategory->weight_percentage }}%
                        </td>
                        <td class="border-2 border-black px-3 py-3 text-center bg-white">
                            {{ number_format($this->getAdjustedKompetensiStandardScore(), 2, ',', '.') }}
                        </td>
                        <td class="border-2 border-black px-3 py-3 text-center bg-white">
                            {{ number_format($kompetensiAssessment->total_individual_score, 2, ',', '.') }}
                        </td>
                        <td class="border-2 border-black px-3 py-3 text-center bg-white">
                            {{ number_format($this->getAdjustedKompetensiGap(), 2, ',', '.') }}
                        </td>
                        <td class="border-2 border-black px-3 py-3 text-center font-bold {{ $this->getConclusionColorClass($this->getKompetensiConclusion()) }}">
                            {{ $this->getKompetensiConclusion() }}
                        </td>
                    </tr>
                @endif

                <!-- Total Row -->
                @if ($finalAssessment)
                    <tr class="bg-black text-white">
                        <td class="border-2 border-black px-3 py-3 text-center" colspan="2"><strong>TOTAL
                                SKOR</strong></td>
                        <td class="border-2 border-black px-3 py-3 text-center">
                            <strong>
                                {{ number_format(($potensiAssessment?->total_standard_rating ?? 0) + ($kompetensiAssessment?->total_standard_rating ?? 0), 2, ',', '.') }}
                            </strong>
                        </td>
                        <td class="border-2 border-black px-3 py-3 text-center">
                            <strong>
                                {{ number_format(($potensiAssessment?->total_individual_rating ?? 0) + ($kompetensiAssessment?->total_individual_rating ?? 0), 2, ',', '.') }}
                            </strong>
                        </td>
                        <td class="border-2 border-black px-3 py-3 text-center"><strong>100%</strong></td>
                        <td class="border-2 border-black px-3 py-3 text-center">
                            <strong>{{ number_format($this->getAdjustedTotalStandardScore(), 2, ',', '.') }}</strong>
                        </td>
                        <td class="border-2 border-black px-3 py-3 text-center">
                            <strong>{{ number_format($finalAssessment->total_individual_score, 2, ',', '.') }}</strong>
                        </td>
                        <td class="border-2 border-black px-3 py-3 text-center">
                            <strong>
                                {{ number_format($this->getAdjustedTotalGap(), 2, ',', '.') }}
                            </strong>
                        </td>
                        <td class="border-2 border-black px-3 py-3 text-center font-bold {{ $this->getTotalConclusionColorClass() }}">
                            {{ $this->getTotalConclusionInTable() }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Conclusion Section -->
    @if ($finalAssessment)
        <div class="px-6 pb-6 bg-white">
            <table class="min-w-full border-2 border-black">
                <tr>
                    <td class="border-2 border-black px-4 py-4 font-bold text-center text-gray-900 bg-white"
                        style="width: 200px;">
                        KESIMPULAN :
                    </td>
                    <td class="border-2 border-black px-4 py-4 text-center font-bold text-lg {{ $this->getFinalConclusionColorClassByGap() }}">
                        {{ $this->getFinalConclusionText() }}
                    </td>
                </tr>
            </table>
        </div>
    @endif
</div>
