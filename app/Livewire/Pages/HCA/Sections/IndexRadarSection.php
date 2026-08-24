<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use App\Models\Participant;
use App\Services\HcaDataService;
use App\Services\IndividualAssessmentService;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class IndexRadarSection extends Component
{
    #[Reactive]
    public ?int $participantId = null;

    public string $sectionCode = 'hci';

    public string $chartId;

    /**
     * Listen to tolerance update events to re-render component
     */
    #[On('tolerance-updated')]
    public function onToleranceUpdated(): void
    {
        // Re-renders component
    }

    public function mount(string $sectionCode = 'hci'): void
    {
        $this->sectionCode = $sectionCode;
        $this->chartId = 'hciRadar_'.$sectionCode.'_'.uniqid();
    }

    public function getParticipantProperty(): ?Participant
    {
        return app(HcaDataService::class)->getParticipant($this->participantId);
    }

    /**
     * Get active tolerance percentage from session
     */
    public function getTolerancePercentage(): int
    {
        return (int) session('individual_report.tolerance', 0);
    }

    private function getHciData(): array
    {
        $participant = $this->participant;
        $tolerancePercentage = $this->getTolerancePercentage();
        $toleranceFactor = $tolerancePercentage > 0 ? (1 - ($tolerancePercentage / 100)) : 1.0;

        if (! $participant || ! $participant->positionFormation?->template) {
            return [
                'title' => 'Human Capital Index',
                'subtitle' => 'Evaluasi Keseimbangan 5 Dimensi',
                'desc' => 'Profil kompetensi dan potensi kandidat belum dikalkulasi.',
                'talentIndex' => 4.12,
                'talentIndexPercent' => 82.40,
                'talentCategory' => 'Strong Talent',
                'labels' => ['Kompetensi', 'Potensi', 'Kinerja', 'Kepemimpinan', 'Integritas'],
                'actualRatings' => [4.00, 4.25, 4.50, 3.80, 4.30],
                'standardRatings' => [3.00, 3.00, 3.00, 3.00, 3.00],
                'toleranceRatings' => [round(3.00 * $toleranceFactor, 2), round(3.00 * $toleranceFactor, 2), round(3.00 * $toleranceFactor, 2), round(3.00 * $toleranceFactor, 2), round(3.00 * $toleranceFactor, 2)],
            ];
        }

        $templateId = $participant->positionFormation->template_id;
        $hcaDataService = app(HcaDataService::class);
        $service = app(IndividualAssessmentService::class);

        $potensiCat = $hcaDataService->getCategoryByCode($templateId, 'potensi');
        $kompetensiCat = $hcaDataService->getCategoryByCode($templateId, 'kompetensi');

        $potensiAspects = $potensiCat ? $service->getAspectAssessments($participant->id, $potensiCat->id, $tolerancePercentage) : collect();
        $kompetensiAspects = $kompetensiCat ? $service->getAspectAssessments($participant->id, $kompetensiCat->id, $tolerancePercentage) : collect();

        // 1. Pilar Kompetensi
        $kompetensiRating = $kompetensiAspects->isNotEmpty()
            ? (float) $kompetensiAspects->avg('individual_rating')
            : (float) ($participant->finalAssessment->kompetensi_individual_score ?? 4.00);

        // 2. Pilar Potensi
        $potensiRating = $potensiAspects->isNotEmpty()
            ? (float) $potensiAspects->avg('individual_rating')
            : (float) ($participant->finalAssessment->potensi_individual_score ?? 4.00);

        // 3. Pilar Kinerja
        $achievementPercentage = (float) ($participant->finalAssessment->achievement_percentage ?? 80.00);
        $kinerjaRating = min(5.00, round(($achievementPercentage / 100) * 5.00, 2));

        // 4. Pilar Kepemimpinan
        $leadershipAspect = $potensiAspects->concat($kompetensiAspects)->first(function ($item) {
            $name = strtolower($item['name'] ?? $item['aspect_name'] ?? '');
            $code = strtolower($item['aspect_code'] ?? '');

            return str_contains($name, 'kepemimpinan') || str_contains($code, 'kepemimpinan') || str_contains($name, 'leadership') || str_contains($code, 'leadership');
        });
        $kepemimpinanRating = $leadershipAspect
            ? (float) $leadershipAspect['individual_rating']
            : round(($potensiRating + $kompetensiRating) / 2, 2);

        // 5. Pilar Integritas
        $integrityAspect = $potensiAspects->concat($kompetensiAspects)->first(function ($item) {
            $name = strtolower($item['name'] ?? $item['aspect_name'] ?? '');
            $code = strtolower($item['aspect_code'] ?? '');

            return str_contains($name, 'integritas') || str_contains($code, 'integritas') || str_contains($name, 'integrity') || str_contains($code, 'integrity');
        });
        $integritasRating = $integrityAspect
            ? (float) $integrityAspect['individual_rating']
            : round(($potensiRating + $kompetensiRating) / 2, 2);

        // Standard Ratings (Full Standard Baseline)
        $kompetensiStd = $kompetensiAspects->isNotEmpty() ? (float) ($kompetensiAspects->avg('original_standard_rating') ?? $kompetensiAspects->avg('standard_rating')) : 3.00;
        $potensiStd = $potensiAspects->isNotEmpty() ? (float) ($potensiAspects->avg('original_standard_rating') ?? $potensiAspects->avg('standard_rating')) : 3.00;
        $kinerjaStd = 3.00;
        $kepemimpinanStd = 3.00;
        $integritasStd = 3.00;

        // Tolerance Ratings (Standard multiplied by tolerance factor)
        $kompetensiTol = round($kompetensiStd * $toleranceFactor, 2);
        $potensiTol = round($potensiStd * $toleranceFactor, 2);
        $kinerjaTol = round($kinerjaStd * $toleranceFactor, 2);
        $kepemimpinanTol = round($kepemimpinanStd * $toleranceFactor, 2);
        $integritasTol = round($integritasStd * $toleranceFactor, 2);

        $actualRatings = [
            round($kompetensiRating, 2),
            round($potensiRating, 2),
            round($kinerjaRating, 2),
            round($kepemimpinanRating, 2),
            round($integritasRating, 2),
        ];

        $standardRatings = [
            round($kompetensiStd, 2),
            round($potensiStd, 2),
            round($kinerjaStd, 2),
            round($kepemimpinanStd, 2),
            round($integritasStd, 2),
        ];

        $toleranceRatings = [
            $kompetensiTol,
            $potensiTol,
            $kinerjaTol,
            $kepemimpinanTol,
            $integritasTol,
        ];

        $talentIndex = round(array_sum($actualRatings) / 5, 2);
        $talentIndexPercent = round(($talentIndex / 5.00) * 100, 2);

        $talentCategory = match (true) {
            $talentIndex >= 4.50 => 'Top Talent',
            $talentIndex >= 4.00 => 'Strong Talent',
            $talentIndex >= 3.50 => 'Promising Talent',
            $talentIndex >= 3.00 => 'Developing Talent',
            default => 'Needs Focus',
        };

        $desc = "Evaluasi komprehensif 5 pilar modal manusia menghasilkan Talent Index {$talentIndex} dari skala 5.00 ({$talentIndexPercent}%) dengan kategori {$talentCategory}. Mengintegrasikan kapabilitas manajerial, kapasitas psikologis laten, pembuktian kinerja, efektivitas kepemimpinan, dan benteng etika.";

        return [
            'title' => 'Human Capital Index',
            'subtitle' => 'Evaluasi Keseimbangan 5 Dimensi',
            'desc' => $desc,
            'talentIndex' => $talentIndex,
            'talentIndexPercent' => $talentIndexPercent,
            'talentCategory' => $talentCategory,
            'labels' => ['Kompetensi', 'Potensi', 'Kinerja', 'Kepemimpinan', 'Integritas'],
            'actualRatings' => $actualRatings,
            'standardRatings' => $standardRatings,
            'toleranceRatings' => $toleranceRatings,
        ];
    }

    /**
     * Get dynamic Layer 2: Potensi data from SPSP database.
     */
    private function getPotentialData(): array
    {
        $participant = $this->participant;
        $tolerancePercentage = $this->getTolerancePercentage();
        $toleranceFactor = $tolerancePercentage > 0 ? (1 - ($tolerancePercentage / 100)) : 1.0;

        if (! $participant || ! $participant->positionFormation?->template) {
            return [
                'title' => 'Evaluasi Potensi Psikologis',
                'subtitle' => 'Kapasitas Kognitif & Sikap Kerja Laten',
                'desc' => 'Profil aspek potensi kandidat belum dikalkulasi.',
                'talentIndex' => 4.00,
                'talentIndexPercent' => 80.00,
                'talentCategory' => 'Strong Potential',
                'labels' => ['Intelektual', 'Sikap Kerja', 'Potensi Kerja', 'Sosualitas', 'Kepribadian'],
                'actualRatings' => [4.00, 4.20, 3.80, 4.10, 3.90],
                'standardRatings' => [3.00, 3.00, 3.00, 3.00, 3.00],
                'toleranceRatings' => [round(3.00 * $toleranceFactor, 2), round(3.00 * $toleranceFactor, 2), round(3.00 * $toleranceFactor, 2), round(3.00 * $toleranceFactor, 2), round(3.00 * $toleranceFactor, 2)],
            ];
        }

        $templateId = $participant->positionFormation->template_id;
        $hcaDataService = app(HcaDataService::class);
        $service = app(IndividualAssessmentService::class);
        $potensiCat = $hcaDataService->getCategoryByCode($templateId, 'potensi');

        if (! $potensiCat) {
            return [
                'title' => 'Evaluasi Potensi Psikologis',
                'subtitle' => 'Kapasitas Kognitif & Sikap Kerja Laten',
                'desc' => 'Kategori potensi tidak ditemukan pada template jabatan ini.',
                'talentIndex' => 4.00,
                'talentIndexPercent' => 80.00,
                'talentCategory' => 'Strong Potential',
                'labels' => ['Intelektual', 'Sikap Kerja', 'Potensi Kerja', 'Sosualitas', 'Kepribadian'],
                'actualRatings' => [4.00, 4.20, 3.80, 4.10, 3.90],
                'standardRatings' => [3.00, 3.00, 3.00, 3.00, 3.00],
                'toleranceRatings' => [round(3.00 * $toleranceFactor, 2), round(3.00 * $toleranceFactor, 2), round(3.00 * $toleranceFactor, 2), round(3.00 * $toleranceFactor, 2), round(3.00 * $toleranceFactor, 2)],
            ];
        }

        $aspectAssessments = $service->getAspectAssessments($participant->id, $potensiCat->id, $tolerancePercentage);

        if ($aspectAssessments->isEmpty()) {
            return [
                'title' => 'Evaluasi Potensi Psikologis',
                'subtitle' => 'Kapasitas Kognitif & Sikap Kerja Laten',
                'desc' => 'Data asesmen aspek potensi belum tersedia.',
                'talentIndex' => 4.00,
                'talentIndexPercent' => 80.00,
                'talentCategory' => 'Strong Potential',
                'labels' => ['Intelektual', 'Sikap Kerja', 'Potensi Kerja', 'Sosualitas', 'Kepribadian'],
                'actualRatings' => [4.00, 4.20, 3.80, 4.10, 3.90],
                'standardRatings' => [3.00, 3.00, 3.00, 3.00, 3.00],
                'toleranceRatings' => [round(3.00 * $toleranceFactor, 2), round(3.00 * $toleranceFactor, 2), round(3.00 * $toleranceFactor, 2), round(3.00 * $toleranceFactor, 2), round(3.00 * $toleranceFactor, 2)],
            ];
        }

        $labels = $aspectAssessments->pluck('name')->toArray();
        $actualRatings = $aspectAssessments->pluck('individual_rating')->map(fn ($v) => round((float) $v, 2))->toArray();
        $standardRatings = $aspectAssessments->map(fn ($item) => round((float) ($item['original_standard_rating'] ?? $item['standard_rating'] ?? 3.00), 2))->toArray();
        $toleranceRatings = $aspectAssessments->map(fn ($item) => round((float) ($item['standard_rating'] ?? 3.00), 2))->toArray();

        $avgRating = round((float) $aspectAssessments->avg('individual_rating'), 2);
        $talentIndexPercent = round(($avgRating / 5.00) * 100, 2);

        $talentCategory = match (true) {
            $avgRating >= 4.50 => 'Top Potential',
            $avgRating >= 4.00 => 'High Potential',
            $avgRating >= 3.50 => 'Strong Potential',
            $avgRating >= 3.00 => 'Developing Potential',
            default => 'Needs Focus',
        };

        $desc = "Evaluasi aspek potensi psikologis {$participant->name} menghasilkan rata-rata skor {$avgRating} dari skala 5.00 ({$talentIndexPercent}%). Mengukur kapasitas daya pikir, sikap kerja, stabilitas emosi, dan orientasi prestasi laten sebagai fondasi adaptabilitas kerja jangka panjang.";

        return [
            'title' => 'Evaluasi Potensi Psikologis',
            'subtitle' => 'Kapasitas Kognitif & Sikap Kerja Laten',
            'desc' => $desc,
            'talentIndex' => $avgRating,
            'talentIndexPercent' => $talentIndexPercent,
            'talentCategory' => $talentCategory,
            'labels' => $labels,
            'actualRatings' => $actualRatings,
            'standardRatings' => $standardRatings,
            'toleranceRatings' => $toleranceRatings,
        ];
    }

    /**
     * Get EQ data.
     */
    private function getEqData(): array
    {
        $tolerancePercentage = $this->getTolerancePercentage();
        $toleranceFactor = $tolerancePercentage > 0 ? (1 - ($tolerancePercentage / 100)) : 1.0;

        return [
            'title' => 'Emotional Intelligence (EQ)',
            'subtitle' => 'Kematangan Emosional & Hubungan Kerja',
            'desc' => 'Evaluasi aspek pengenalan diri, pengendalian emosi, keterampilan sosial, empati, dan motivasi intrinsik kandidat.',
            'talentIndex' => 4.35,
            'talentIndexPercent' => 87.00,
            'talentCategory' => 'Highly Mature',
            'labels' => ['Self Awareness', 'Self Regulation', 'Social Skills', 'Empathy', 'Motivation'],
            'actualRatings' => [4.20, 4.50, 4.10, 4.60, 4.35],
            'standardRatings' => [3.00, 3.00, 3.00, 3.00, 3.00],
            'toleranceRatings' => [round(3.00 * $toleranceFactor, 2), round(3.00 * $toleranceFactor, 2), round(3.00 * $toleranceFactor, 2), round(3.00 * $toleranceFactor, 2), round(3.00 * $toleranceFactor, 2)],
        ];
    }

    public function render(): View
    {
        $data = match ($this->sectionCode) {
            'hci' => $this->getHciData(),
            'potential' => $this->getPotentialData(),
            'eq' => $this->getEqData(),
            default => $this->getHciData(),
        };

        return view('livewire.pages.h-c-a.sections.index-radar-section', [
            'title' => $data['title'],
            'subtitle' => $data['subtitle'],
            'desc' => $data['desc'],
            'talentIndex' => $data['talentIndex'],
            'talentIndexPercent' => $data['talentIndexPercent'],
            'talentCategory' => $data['talentCategory'],
            'labels' => $data['labels'],
            'actualRatings' => $data['actualRatings'],
            'standardRatings' => $data['standardRatings'],
            'toleranceRatings' => $data['toleranceRatings'],
            'tolerancePercentage' => $this->getTolerancePercentage(),
        ]);
    }
}
