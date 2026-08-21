<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use App\Models\Participant;
use App\Services\HcaDataService;
use App\Services\IndividualAssessmentService;
use Illuminate\View\View;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class IndexRadarSection extends Component
{
    #[Reactive]
    public ?int $participantId = null;

    public string $sectionCode = 'hci';

    public string $chartId;

    public function mount(string $sectionCode = 'hci'): void
    {
        $this->sectionCode = $sectionCode;
        $this->chartId = 'hciRadar_'.$sectionCode.'_'.uniqid();
    }

    public function getParticipantProperty(): ?Participant
    {
        return app(HcaDataService::class)->getParticipant($this->participantId);
    }

    private function getHciData(): array
    {
        $participant = $this->participant;

        if (! $participant || ! $participant->positionFormation?->template) {
            return [
                'title' => 'Human Capital Index',
                'subtitle' => 'Dimensi Utama (Layer 1-3)',
                'desc' => 'Profil kompetensi dan potensi kandidat belum dikalkulasi.',
                'talentIndex' => 4.12,
                'talentIndexPercent' => 82.40,
                'talentCategory' => 'Strong Talent',
                'labels' => ['Kompetensi', 'Potensi', 'Kinerja', 'Kepemimpinan', 'Integritas'],
                'actualRatings' => [4.00, 4.25, 4.50, 3.80, 4.30],
                'standardRatings' => [3.00, 3.00, 3.00, 3.00, 3.00],
                'toleranceRatings' => [2.70, 2.70, 2.70, 2.70, 2.70],
            ];
        }

        $templateId = $participant->positionFormation->template_id;
        $hcaDataService = app(HcaDataService::class);
        $service = app(IndividualAssessmentService::class);

        $potensiCat = $hcaDataService->getCategoryByCode($templateId, 'potensi');
        $kompetensiCat = $hcaDataService->getCategoryByCode($templateId, 'kompetensi');

        $potensiAspects = $potensiCat ? $service->getAspectAssessments($participant->id, $potensiCat->id, 0) : collect();
        $kompetensiAspects = $kompetensiCat ? $service->getAspectAssessments($participant->id, $kompetensiCat->id, 0) : collect();

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
        $kompetensiStd = $kompetensiAspects->isNotEmpty() ? (float) $kompetensiAspects->avg('standard_rating') : 3.00;
        $potensiStd = $potensiAspects->isNotEmpty() ? (float) $potensiAspects->avg('standard_rating') : 3.00;
        $kinerjaStd = 3.00;
        $kepemimpinanStd = 3.00;
        $integritasStd = 3.00;

        // Tolerance Ratings (Standard minus 10% tolerance)
        $kompetensiTol = round($kompetensiStd * 0.9, 2);
        $potensiTol = round($potensiStd * 0.9, 2);
        $kinerjaTol = 2.70;
        $kepemimpinanTol = 2.70;
        $integritasTol = 2.70;

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

        $desc = "{$participant->name} memiliki skor Human Capital Index sebesar {$talentIndex} dari 5.00 ({$talentIndexPercent}%). ";
        if ($talentIndex >= 4.00) {
            $desc .= 'Profil kompetensi dan potensi berada di atas rata-rata standar institusi, mengindikasikan kesiapan tinggi untuk peran kepemimpinan masa depan.';
        } elseif ($talentIndex >= 3.00) {
            $desc .= 'Profil kompetensi dan potensi secara umum memenuhi standar institusi dengan beberapa area pengembangan yang disarankan.';
        } else {
            $desc .= 'Profil kandidat memerlukan perhatian khusus pada area pengembangan kompetensi utama.';
        }

        return [
            'title' => 'Human Capital Index',
            'subtitle' => 'Dimensi Utama (Layer 1-3)',
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

        if (! $participant || ! $participant->positionFormation?->template) {
            return [
                'title' => 'Layer 2: Potensi',
                'subtitle' => 'Evaluasi Kapasitas Psikologis Laten',
                'desc' => 'Profil aspek potensi kandidat belum dikalkulasi.',
                'talentIndex' => 4.00,
                'talentIndexPercent' => 80.00,
                'talentCategory' => 'Strong Potential',
                'labels' => ['Intelektual', 'Sikap Kerja', 'Potensi Kerja', 'Sosualitas', 'Kepribadian'],
                'actualRatings' => [4.00, 4.20, 3.80, 4.10, 3.90],
                'standardRatings' => [3.00, 3.00, 3.00, 3.00, 3.00],
                'toleranceRatings' => [2.70, 2.70, 2.70, 2.70, 2.70],
            ];
        }

        $templateId = $participant->positionFormation->template_id;
        $hcaDataService = app(HcaDataService::class);
        $service = app(IndividualAssessmentService::class);
        $potensiCat = $hcaDataService->getCategoryByCode($templateId, 'potensi');

        if (! $potensiCat) {
            return [
                'title' => 'Layer 2: Potensi',
                'subtitle' => 'Evaluasi Kapasitas Psikologis Laten',
                'desc' => 'Kategori potensi tidak ditemukan pada template jabatan ini.',
                'talentIndex' => 4.00,
                'talentIndexPercent' => 80.00,
                'talentCategory' => 'Strong Potential',
                'labels' => ['Intelektual', 'Sikap Kerja', 'Potensi Kerja', 'Sosualitas', 'Kepribadian'],
                'actualRatings' => [4.00, 4.20, 3.80, 4.10, 3.90],
                'standardRatings' => [3.00, 3.00, 3.00, 3.00, 3.00],
                'toleranceRatings' => [2.70, 2.70, 2.70, 2.70, 2.70],
            ];
        }

        $aspectAssessments = $service->getAspectAssessments($participant->id, $potensiCat->id, 0);

        if ($aspectAssessments->isEmpty()) {
            return [
                'title' => 'Layer 2: Potensi',
                'subtitle' => 'Evaluasi Kapasitas Psikologis Laten',
                'desc' => 'Data asesmen aspek potensi belum tersedia.',
                'talentIndex' => 4.00,
                'talentIndexPercent' => 80.00,
                'talentCategory' => 'Strong Potential',
                'labels' => ['Intelektual', 'Sikap Kerja', 'Potensi Kerja', 'Sosualitas', 'Kepribadian'],
                'actualRatings' => [4.00, 4.20, 3.80, 4.10, 3.90],
                'standardRatings' => [3.00, 3.00, 3.00, 3.00, 3.00],
                'toleranceRatings' => [2.70, 2.70, 2.70, 2.70, 2.70],
            ];
        }

        $labels = $aspectAssessments->pluck('name')->toArray();
        $actualRatings = $aspectAssessments->pluck('individual_rating')->map(fn ($v) => round((float) $v, 2))->toArray();
        $standardRatings = $aspectAssessments->pluck('standard_rating')->map(fn ($v) => round((float) $v, 2))->toArray();
        $toleranceRatings = $aspectAssessments->map(fn ($item) => round(((float) $item['standard_rating']) * 0.9, 2))->toArray();

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
            'title' => 'Layer 2: Potensi',
            'subtitle' => 'Evaluasi Kapasitas Psikologis Laten',
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
            'toleranceRatings' => [2.70, 2.70, 2.70, 2.70, 2.70],
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
        ]);
    }
}
