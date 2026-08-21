<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use App\Models\Participant;
use App\Services\HcaDataService;
use Illuminate\View\View;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class NineBoxMatrix extends Component
{
    #[Reactive]
    public ?int $participantId = null;

    public array $grid = [
        // Format: [pot_level, perf_level, label, desc, box_number]
        [3, 1, 'Enigma', 'Potensi Tinggi, Kinerja Rendah', 7],
        [3, 2, 'High Potential', 'Potensi Tinggi, Kinerja Sedang', 8],
        [3, 3, 'Star Talent', 'Potensi Tinggi, Kinerja Tinggi', 9],

        [2, 1, 'Dilemma', 'Potensi Sedang, Kinerja Rendah', 4],
        [2, 2, 'Core Player', 'Potensi Sedang, Kinerja Sedang', 5],
        [2, 3, 'High Performer', 'Potensi Sedang, Kinerja Tinggi', 6],

        [1, 1, 'Underperformer', 'Potensi Rendah, Kinerja Rendah', 1],
        [1, 2, 'Effective Organiser', 'Potensi Rendah, Kinerja Sedang', 2],
        [1, 3, 'Solid Professional', 'Potensi Rendah, Kinerja Tinggi', 3],
    ];

    public function getParticipantProperty(): ?Participant
    {
        return app(HcaDataService::class)->getParticipant($this->participantId);
    }

    public function render(): View
    {
        $participant = $this->participant;

        // 1. Calculate Potential Level (1: Low, 2: Medium, 3: High)
        $potScore = (float) ($participant?->finalAssessment?->potensi_individual_score ?? 3.85);
        $activePotential = match (true) {
            $potScore >= 3.60 => 3,
            $potScore >= 2.80 => 2,
            default => 1,
        };

        // 2. Calculate Performance Level (1: Low, 2: Medium, 3: High)
        $latestKpi = (float) ($participant?->performanceRecords?->last()?->kpi_score ?? 95.50);
        $activePerformance = match (true) {
            $latestKpi >= 95.00 => 3,
            $latestKpi >= 85.00 => 2,
            default => 1,
        };

        // 3. Find Matching Box
        $activeCell = collect($this->grid)->first(
            fn ($item) => $item[0] === $activePotential && $item[1] === $activePerformance
        ) ?? [3, 3, 'Star Talent', 'Potensi Tinggi, Kinerja Tinggi', 9];

        $boxNumber = $activeCell[4];
        $boxLabel = $activeCell[2];
        $boxDesc = $activeCell[3];

        // 4. Generate Narrative Interpretation
        $name = $participant?->name ?? 'Kandidat';
        $placementNarrative = match ($boxNumber) {
            9 => "Kandidat {$name} berada di kuadran Star Talent (Box 9) dengan kapasitas potensi kepemimpinan tinggi dan kinerja aktual yang konsisten melampaui standar. Direkomendasikan untuk promosi kepemimpinan akselerasi (fast track) dan penugasan pada proyek strategis korporasi.",
            8 => "Kandidat {$name} memiliki potensi kepemimpinan tinggi (Box 8) dengan kinerja kerja stabil memenuhi standar target. Direkomendasikan untuk peningkatan target kerja strategis, mentoring intensif, dan penugasan manajerial lebih luas.",
            7 => "Kandidat {$name} memiliki potensi intelektual dan analitis tinggi (Box 7) namun kinerja operasional masih memerlukan optimasi. Direkomendasikan untuk evaluasi kesesuaian peran (job fit) dan pendampingan eksekusi kerja.",
            6 => "Kandidat {$name} memiliki rekam jejak kinerja sangat kuat (Box 6) dan sangat handal dalam eksekusi operasional. Direkomendasikan untuk penguatan wawasan kepemimpinan strategis dan program pengembangan kapabilitas manajerial.",
            5 => "Kandidat {$name} berada di kuadran Core Player (Box 5) dengan kontribusi kerja dan potensi yang selaras memenuhi standar unit. Direkomendasikan untuk pengayaan peran (job enrichment) dan pelatihan kompetensi bertahap.",
            4 => "Kandidat {$name} menunjukkan potensi memadai (Box 4) namun capaian target kerja belum optimal. Direkomendasikan untuk pendampingan performa terstruktur dan penetapan sasaran kerja jangka pendek.",
            3 => "Kandidat {$name} memiliki kinerja operasional sangat baik (Box 3) pada lingkup tugas saat ini. Direkomendasikan untuk pemanfaatan keahlian sebagai spesialis fungsional dan penguatan adaptabilitas organisasi.",
            2 => "Kandidat {$name} menjalankan tugas-tugas terstruktur dengan stabil (Box 2). Direkomendasikan untuk pelatihan keterampilan operasional dan peningkatan keterlibatan tim.",
            default => "Kandidat {$name} memerlukan program pemulihan kinerja (Performance Improvement Plan / PIP) dan penataan ulang sasaran kerja prioritas.",
        };

        return view('livewire.pages.h-c-a.sections.nine-box-matrix', [
            'activePotential' => $activePotential,
            'activePerformance' => $activePerformance,
            'boxNumber' => $boxNumber,
            'boxLabel' => $boxLabel,
            'boxDesc' => $boxDesc,
            'placementNarrative' => $placementNarrative,
            'participant' => $participant,
        ]);
    }
}
