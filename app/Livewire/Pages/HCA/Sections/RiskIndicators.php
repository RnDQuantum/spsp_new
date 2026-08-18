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

        $overallRisk = $stresLevel === 'Tinggi' ? 'Sedang' : 'Rendah';

        $indicators = [
            [
                'label' => 'Saturasi Kejenuhan (Burnout Risk)',
                'level' => $stresLevel === 'Tinggi' ? 'Sedang' : 'Rendah',
                'desc' => 'Tingkat kelelahan fisik, emosional, dan mental akibat beban kerja harian dan dinamika tugas operasional.',
            ],
            [
                'label' => 'Kerentanan Stres (Stress Susceptibility)',
                'level' => $stresLevel,
                'desc' => 'Respon emosional kandidat saat berhadapan dengan tenggat waktu ketat secara beruntun dan situasi bertekanan tinggi.',
            ],
            [
                'label' => 'Indeks Konflik Interpersonal',
                'level' => 'Rendah',
                'desc' => 'Kecenderungan kandidat untuk mengalami gesekan komunikasi dengan rekan sejawat atau bawahan.',
            ],
            [
                'label' => 'Risiko Penurunan Produktivitas',
                'level' => $stresLevel === 'Tinggi' ? 'Sedang' : 'Rendah',
                'desc' => 'Proyeksi fluktuasi kinerja kandidat dalam masa transisi organisasi atau perubahan target strategis.',
            ],
        ];

        return view('livewire.pages.h-c-a.sections.risk-indicators', [
            'participant' => $participant,
            'overallRisk' => $overallRisk,
            'indicators' => $indicators,
        ]);
    }
}
