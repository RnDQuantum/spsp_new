<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use App\Models\Participant;
use App\Services\HcaDataService;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class SuccessionReadiness extends Component
{
    #[Reactive]
    public ?int $participantId = null;

    #[On('hca-data-updated')]
    public function onDataUpdated(): void
    {
        // Re-renders component on data update
    }

    public function getParticipantProperty(): ?Participant
    {
        return app(HcaDataService::class)->getParticipant($this->participantId);
    }

    public function render(): View
    {
        $participant = $this->participant;
        $profile = $participant?->personalProfile;

        $posName = $participant?->positionFormation?->name
            ?? $participant?->current_position
            ?? 'Pejabat Struktural / Fungsional';

        $name = $participant?->name ?? 'Kandidat';

        // 1. Calculate Potential & Performance Level (Default automatic)
        $potScore = (float) ($participant?->finalAssessment?->potensi_individual_score ?? 3.85);
        $latestKpi = (float) ($participant?->performanceRecords?->last()?->kpi_score ?? 95.50);

        $isStarOrHigh = ($potScore >= 3.50 && $latestKpi >= 90.00);

        if ($isStarOrHigh) {
            $primaryTargetRole = 'VP / Kepala Divisi '.$posName;
            $horizons = [
                [
                    'timeframe' => 'Siap Sekarang',
                    'status' => 'Ready Now',
                    'percentage' => min(98, (int) round($latestKpi * 0.98)),
                    'role' => 'VP / Kepala Divisi '.$posName,
                    'desc' => "Kandidat {$name} menunjukkan kesiapan penuh secara kompetensi manajerial dan rekam jejak capaian KPI. Direkomendasikan untuk langsung mengemban peran pimpinan unit tanpa masa transisi panjang.",
                ],
                [
                    'timeframe' => 'Kesiapan 1 Tahun',
                    'status' => 'Ready in 12 Months',
                    'percentage' => 82,
                    'role' => 'Direktur Terkait / Deputy Director '.$posName,
                    'desc' => 'Kandidat diproyeksikan siap promosi ke jenjang pimpinan tingkat tinggi dalam 12 bulan mendatang setelah menyelesaikan penugasan program kepemimpinan strategis korporat.',
                ],
                [
                    'timeframe' => 'Kesiapan 2 Tahun',
                    'status' => 'Ready in 24 Months',
                    'percentage' => 65,
                    'role' => 'Chief / Pimpinan Eksekutif Korporasi',
                    'desc' => 'Proyeksi suksesi jangka panjang menuju pucuk pimpinan organisasi. Membutuhkan rotasi lintas direktorat untuk memperkaya wawasan tata kelola eksekutif.',
                ],
            ];
        } else {
            $primaryTargetRole = 'Koordinator / Manager '.$posName;
            $horizons = [
                [
                    'timeframe' => 'Siap Sekarang',
                    'status' => 'Ready Now',
                    'percentage' => min(92, (int) round($latestKpi * 0.90)),
                    'role' => 'Koordinator / Manager '.$posName,
                    'desc' => "Kandidat {$name} memiliki kesiapan operasional yang memadai pada level jabatan saat ini dan siap diperluas lingkup tanggung jawab timnya secara terukur.",
                ],
                [
                    'timeframe' => 'Kesiapan 1-2 Tahun',
                    'status' => 'Ready in 18 Months',
                    'percentage' => 74,
                    'role' => 'Senior Manager / Sub-Direktur '.$posName,
                    'desc' => 'Dapat dipromosikan ke jenjang manajerial yang lebih tinggi setelah menyelesaikan pendampingan kompetensi strategis dan pemantapan eksekusi inisiatif.',
                ],
                [
                    'timeframe' => 'Kesiapan 3 Tahun',
                    'status' => 'Ready in 36 Months',
                    'percentage' => 55,
                    'role' => 'Head of Division / Pimpinan Madya',
                    'desc' => 'Proyeksi suksesi jangka menengah dengan sasaran pengembangan kepemimpinan lintas fungsi dan penguasaan manajemen risiko unit kerja.',
                ],
            ];
        }

        // 2. Apply Human-in-the-loop Committee Overrides (if set)
        $isCurated = false;
        $successionNotes = null;

        if ($profile) {
            if (! empty($profile->succession_target_role)) {
                $primaryTargetRole = $profile->succession_target_role;
                $horizons[0]['role'] = $profile->succession_target_role;
                $isCurated = true;
            }

            if (! empty($profile->readiness_horizon)) {
                $isCurated = true;
                if ($profile->readiness_horizon === 'ready_now') {
                    $horizons[0]['timeframe'] = 'Siap Sekarang';
                    $horizons[0]['status'] = 'Ready Now';
                } elseif ($profile->readiness_horizon === '1_year') {
                    $horizons[0]['timeframe'] = 'Kesiapan 1 Tahun';
                    $horizons[0]['status'] = 'Ready in 12 Months';
                } elseif ($profile->readiness_horizon === '2_year') {
                    $horizons[0]['timeframe'] = 'Kesiapan 2 Tahun';
                    $horizons[0]['status'] = 'Ready in 24 Months';
                }
            }

            if ($profile->readiness_percentage !== null) {
                $horizons[0]['percentage'] = (int) $profile->readiness_percentage;
                $isCurated = true;
            }

            if (! empty($profile->succession_notes)) {
                $successionNotes = $profile->succession_notes;
                $isCurated = true;
            }
        }

        return view('livewire.pages.h-c-a.sections.succession-readiness', [
            'horizons' => $horizons,
            'primaryTargetRole' => $primaryTargetRole,
            'isCurated' => $isCurated,
            'successionNotes' => $successionNotes,
            'participant' => $participant,
        ]);
    }
}
