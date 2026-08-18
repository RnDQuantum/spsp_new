<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use App\Models\Participant;
use Illuminate\View\View;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class TimelineSection extends Component
{
    #[Reactive]
    public ?int $participantId = null;

    /**
     * Get the active participant with career histories.
     */
    public function getParticipantProperty(): ?Participant
    {
        if (! $this->participantId) {
            return Participant::with(['careerHistories', 'positionFormation', 'assessmentEvent.institution'])->first();
        }

        return Participant::with(['careerHistories', 'positionFormation', 'assessmentEvent.institution'])->find($this->participantId);
    }

    public function render(): View
    {
        $participant = $this->participant;
        $careerHistories = $participant?->careerHistories ?? collect();

        $timeline = [];
        $oldestYear = null;
        $latestYear = (int) date('Y');

        if ($careerHistories->isNotEmpty()) {
            foreach ($careerHistories as $history) {
                $startYear = $history->start_year;
                $endYear = $history->end_year;
                $isCurrent = (bool) $history->is_current;

                if ($oldestYear === null || $startYear < $oldestYear) {
                    $oldestYear = $startYear;
                }

                $period = $isCurrent
                    ? "{$startYear} — Sekarang"
                    : "{$startYear} — {$endYear}";

                $achievements = is_array($history->achievements)
                    ? $history->achievements
                    : (is_string($history->achievements) ? json_decode($history->achievements, true) : []);

                $timeline[] = [
                    'period' => $period,
                    'role' => $history->position_title,
                    'unit' => $history->company_or_institution,
                    'is_current' => $isCurrent,
                    'achievements' => $achievements ?: ['Melaksanakan tugas dan fungsi jabatan sesuai target kinerja operasional unit kerja.'],
                ];
            }
        } else {
            // Fallback gracefully jika data belum tersedia
            $currentPos = $participant?->current_position ?? $participant?->positionFormation?->name ?? 'Pejabat Fungsional / Struktural';
            $instName = $participant?->assessmentEvent?->institution?->name ?? 'Instansi Pemerintahan / Korporasi';
            $currentYear = (int) date('Y');
            $oldestYear = $currentYear - 10;

            $timeline = [
                [
                    'period' => ($currentYear - 2).' — Sekarang',
                    'role' => $currentPos,
                    'unit' => 'Kantor Pusat / Unit Strategis, '.$instName,
                    'is_current' => true,
                    'achievements' => [
                        'Memimpin implementasi program strategis unit kerja dan koordinasi lintas sektor.',
                        'Meningkatkan akurasi capaian kinerja operasional secara konsisten di atas target.',
                    ],
                ],
                [
                    'period' => ($currentYear - 5).' — '.($currentYear - 2),
                    'role' => 'Senior Specialist / Sub-Koordinator — '.$currentPos,
                    'unit' => 'Divisi Operasional & Pengembangan, '.$instName,
                    'is_current' => false,
                    'achievements' => [
                        'Mengawal eksekusi program prioritas organisasi dengan tingkat ketercapaian target 100%.',
                        'Menyusun telaah staf dan rekomendasi teknis untuk optimalisasi proses bisnis.',
                    ],
                ],
            ];
        }

        $effectiveTenureYears = $oldestYear ? max(1, $latestYear - $oldestYear) : 10;

        return view('livewire.pages.h-c-a.sections.timeline-section', [
            'timeline' => $timeline,
            'effectiveTenureYears' => $effectiveTenureYears,
            'participant' => $participant,
        ]);
    }
}
