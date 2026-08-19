<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use App\Models\Participant;
use Illuminate\View\View;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class RiskIndicators extends Component
{
    #[Reactive]
    public ?int $participantId = null;

    public function getParticipantProperty(): ?Participant
    {
        if (! $this->participantId) {
            return Participant::with('mmpi')->first();
        }

        return Participant::with('mmpi')->find($this->participantId);
    }

    public function render(): View
    {
        $participant = $this->participant;
        $mmpi = $participant?->mmpi;

        $stresLevel = 'Rendah';
        if ($mmpi?->tingkat_stres) {
            $rawStres = strtolower($mmpi->tingkat_stres);
            if (str_contains($rawStres, 'tinggi') || str_contains($rawStres, 'berat')) {
                $stresLevel = 'Tinggi';
            } elseif (str_contains($rawStres, 'sedang') || str_contains($rawStres, 'moderat')) {
                $stresLevel = 'Sedang';
            } else {
                $stresLevel = 'Rendah';
            }
        }

        $elevated = $mmpi?->elevated_scales ?? [];
        $hasInterpersonalRisk = ! empty(array_intersect($elevated, ['ANG', 'CYN', 'Pd', 'RC3', 'RC4', 'SOD', 'Ho']));
        $hasBurnoutRisk = ! empty(array_intersect($elevated, ['D', 'Hs', 'EID', 'RC1', 'RC2', 'HEA', 'GIC', 'HPC']));
        $hasStressRisk = $stresLevel === 'Tinggi' || ! empty(array_intersect($elevated, ['A', 'PK', 'Pt', 'RC7', 'ANX', 'FRS']));
        $hasProductivityRisk = ! empty(array_intersect($elevated, ['WRK', 'TPA', 'DISC-r', 'BXD', 'OBS', 'TRT']));

        $overallRisk = (count($elevated) >= 3 || $stresLevel === 'Tinggi')
            ? 'Perlu Perhatian'
            : (count($elevated) > 0 ? 'Sedang' : 'Rendah / Aman');

        $indicators = [
            [
                'label' => 'Saturasi Kejenuhan (Burnout Risk)',
                'level' => $hasBurnoutRisk ? 'Sedang' : ($stresLevel === 'Tinggi' ? 'Sedang' : 'Rendah'),
                'desc' => 'Tingkat kelelahan fisik, emosional, dan mental akibat beban kerja harian dan dinamika tugas operasional.',
            ],
            [
                'label' => 'Kerentanan Stres (Stress Susceptibility)',
                'level' => $hasStressRisk ? 'Tinggi' : $stresLevel,
                'desc' => 'Respon emosional kandidat saat berhadapan dengan tenggat waktu ketat secara beruntun dan situasi bertekanan tinggi.',
            ],
            [
                'label' => 'Indeks Konflik Interpersonal',
                'level' => $hasInterpersonalRisk ? 'Sedang' : 'Rendah',
                'desc' => 'Kecenderungan kandidat untuk mengalami gesekan komunikasi dengan rekan sejawat atau bawahan.',
            ],
            [
                'label' => 'Risiko Penurunan Produktivitas',
                'level' => $hasProductivityRisk ? 'Sedang' : ($stresLevel === 'Tinggi' ? 'Sedang' : 'Rendah'),
                'desc' => 'Proyeksi fluktuasi kinerja kandidat dalam masa transisi organisasi atau perubahan target strategis.',
            ],
        ];

        return view('livewire.pages.h-c-a.sections.risk-indicators', [
            'participant' => $participant,
            'overallRisk' => $overallRisk,
            'indicators' => $indicators,
            'elevatedScales' => $elevated,
        ]);
    }
}
