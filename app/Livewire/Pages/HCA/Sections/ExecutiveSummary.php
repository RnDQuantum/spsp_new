<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use App\Models\Participant;
use App\Services\HcaDataService;
use App\Services\IndividualAssessmentService;
use Illuminate\View\View;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class ExecutiveSummary extends Component
{
    #[Reactive]
    public ?int $participantId = null;

    public function render(): View
    {
        $participant = app(HcaDataService::class)->getParticipant($this->participantId);
        $summaryData = $this->calculateExecutiveSummary($participant);

        return view('livewire.pages.h-c-a.sections.executive-summary', $summaryData);
    }

    /**
     * Calculate 5 pillars, Talent Index, and readiness status for participant
     */
    private function calculateExecutiveSummary(?Participant $participant): array
    {
        if (! $participant || ! $participant->positionFormation?->template) {
            return [
                'talentIndex' => 4.12,
                'talentCategory' => 'Strong Talent',
                'readinessStatus' => 'DISARANKAN',
                'pillars' => [
                    ['name' => 'Kompetensi', 'rating' => 4.00, 'label' => 'Sangat Baik'],
                    ['name' => 'Potensi', 'rating' => 4.25, 'label' => 'Sangat Baik'],
                    ['name' => 'Kinerja', 'rating' => 4.50, 'label' => 'Istimewa'],
                    ['name' => 'Kepemimpinan', 'rating' => 3.80, 'label' => 'Baik'],
                    ['name' => 'Integritas', 'rating' => 4.30, 'label' => 'Sangat Baik'],
                ],
            ];
        }

        $templateId = $participant->positionFormation->template_id;
        $hcaDataService = app(HcaDataService::class);
        $service = app(IndividualAssessmentService::class);

        $potensiCat = $hcaDataService->getCategoryByCode($templateId, 'potensi');
        $kompetensiCat = $hcaDataService->getCategoryByCode($templateId, 'kompetensi');

        $potensiAspects = $potensiCat ? $service->getAspectAssessments($participant->id, $potensiCat->id) : collect();
        $kompetensiAspects = $kompetensiCat ? $service->getAspectAssessments($participant->id, $kompetensiCat->id) : collect();

        // 1. Pillar 1: Kompetensi Average
        $kompetensiRating = $kompetensiAspects->isNotEmpty()
            ? (float) $kompetensiAspects->avg('individual_rating')
            : (float) ($participant->finalAssessment->kompetensi_individual_score ?? 4.00);

        // 2. Pillar 2: Potensi Average
        $potensiRating = $potensiAspects->isNotEmpty()
            ? (float) $potensiAspects->avg('individual_rating')
            : (float) ($participant->finalAssessment->potensi_individual_score ?? 4.00);

        // 3. Pillar 3: Kinerja (converted from achievement_percentage, or fallback)
        $achievementPercentage = (float) ($participant->finalAssessment->achievement_percentage ?? 80.00);
        $kinerjaRating = min(5.00, round(($achievementPercentage / 100) * 5.00, 2));

        // 4. Pillar 4: Kepemimpinan
        $leadershipAspect = $potensiAspects->concat($kompetensiAspects)->first(function ($item) {
            $name = strtolower($item['aspect_name'] ?? '');
            $code = strtolower($item['aspect_code'] ?? '');

            return str_contains($name, 'kepemimpinan') || str_contains($code, 'kepemimpinan') || str_contains($name, 'leadership') || str_contains($code, 'leadership');
        });
        $kepemimpinanRating = $leadershipAspect
            ? (float) $leadershipAspect['individual_rating']
            : round(($potensiRating + $kompetensiRating) / 2, 2);

        // 5. Pillar 5: Integritas
        $integrityAspect = $potensiAspects->concat($kompetensiAspects)->first(function ($item) {
            $name = strtolower($item['aspect_name'] ?? '');
            $code = strtolower($item['aspect_code'] ?? '');

            return str_contains($name, 'integritas') || str_contains($code, 'integritas') || str_contains($name, 'integrity') || str_contains($code, 'integrity');
        });
        $integritasRating = $integrityAspect
            ? (float) $integrityAspect['individual_rating']
            : round(($potensiRating + $kompetensiRating) / 2, 2);

        // Calculate Talent Index
        $talentIndex = round(($kompetensiRating + $potensiRating + $kinerjaRating + $kepemimpinanRating + $integritasRating) / 5, 2);

        // Determine Talent Category
        $talentCategory = match (true) {
            $talentIndex >= 4.50 => 'Top Talent',
            $talentIndex >= 4.00 => 'Strong Talent',
            $talentIndex >= 3.50 => 'Promising Talent',
            $talentIndex >= 3.00 => 'Developing Talent',
            default => 'Needs Focus',
        };

        // Readiness status from SPSP final assessment
        $readinessStatus = $participant->finalAssessment->conclusion_text
            ?? ($participant->finalAssessment->conclusion_code ?? 'DISARANKAN');

        $pillars = [
            [
                'name' => 'Kompetensi',
                'rating' => round($kompetensiRating, 2),
                'label' => $this->getPillarLabel($kompetensiRating),
            ],
            [
                'name' => 'Potensi',
                'rating' => round($potensiRating, 2),
                'label' => $this->getPillarLabel($potensiRating),
            ],
            [
                'name' => 'Kinerja',
                'rating' => round($kinerjaRating, 2),
                'label' => $this->getPillarLabel($kinerjaRating),
            ],
            [
                'name' => 'Kepemimpinan',
                'rating' => round($kepemimpinanRating, 2),
                'label' => $this->getPillarLabel($kepemimpinanRating),
            ],
            [
                'name' => 'Integritas',
                'rating' => round($integritasRating, 2),
                'label' => $this->getPillarLabel($integritasRating),
            ],
        ];

        return [
            'talentIndex' => $talentIndex,
            'talentCategory' => $talentCategory,
            'readinessStatus' => strtoupper($readinessStatus),
            'pillars' => $pillars,
        ];
    }

    /**
     * Get predicate label for pillar rating
     */
    private function getPillarLabel(float $rating): string
    {
        return match (true) {
            $rating >= 4.50 => 'Istimewa',
            $rating >= 3.75 => 'Sangat Baik',
            $rating >= 3.00 => 'Baik',
            $rating >= 2.25 => 'Cukup',
            default => 'Perlu Pengembangan',
        };
    }
}
