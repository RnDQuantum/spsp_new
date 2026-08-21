<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use App\Models\Participant;
use App\Services\HcaDataService;
use Illuminate\View\View;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class NextRoleRecommendation extends Component
{
    #[Reactive]
    public ?int $participantId = null;

    public function getParticipantProperty(): ?Participant
    {
        return app(HcaDataService::class)->getParticipant($this->participantId);
    }

    public function render(): View
    {
        $participant = $this->participant;

        // 1. Calculate Potential and Performance Level
        $potScore = (float) ($participant?->finalAssessment?->potensi_individual_score ?? 3.85);
        $kpiScore = (float) ($participant?->performanceRecords?->last()?->kpi_score ?? 95.50);

        $activePotential = match (true) {
            $potScore >= 3.60 => 3,
            $potScore >= 2.80 => 2,
            default => 1,
        };

        $activePerformance = match (true) {
            $kpiScore >= 95.00 => 3,
            $kpiScore >= 85.00 => 2,
            default => 1,
        };

        // Determine 9-Box Placement
        $isStarOrHighPot = ($activePotential === 3 && $activePerformance >= 2);
        $isHighPerformerOrCore = ($activePotential === 2 || $activePerformance === 3);

        // Derive target role name
        $recommendedRole = match (true) {
            $isStarOrHighPot => 'VP / Head of Strategic Division',
            $isHighPerformerOrCore => 'Senior Specialist / Project Lead',
            default => 'Functional Coordinator / Section Head',
        };

        if ($participant?->positionFormation?->name) {
            $posName = $participant->positionFormation->name;
            if (str_contains(strtolower($posName), 'kepala') || str_contains(strtolower($posName), 'head')) {
                $recommendedRole = 'Direktur / VP of Division';
            } elseif (str_contains(strtolower($posName), 'manager') || str_contains(strtolower($posName), 'manajer')) {
                $recommendedRole = 'Senior Vice President / General Manager';
            } elseif (str_contains(strtolower($posName), 'analis') || str_contains(strtolower($posName), 'ahli')) {
                $recommendedRole = 'Lead Specialist / Sub-Division Head';
            }
        }

        // Tailor 3 Phases based on candidate readiness
        $phases = match (true) {
            $isStarOrHighPot => [
                [
                    'title' => 'Fase 1: Akselerasi & Onboarding Strategis',
                    'timeframe' => 'Bulan 1–3',
                    'desc' => 'Menjalani transisi kepemimpinan bertahap dengan pendampingan langsung oleh Direksi/Mentor Eksekutif serta perumusan target inisiatif kuartalan.',
                ],
                [
                    'title' => 'Fase 2: Eksekusi Inisiatif Lintas Divisi',
                    'timeframe' => 'Bulan 4–6',
                    'desc' => 'Memimpin satuan tugas (task force) lintas unit bisnis untuk mengawal transformasi digital dan efisiensi operasional skala organisasi.',
                ],
                [
                    'title' => 'Fase 3: Kemandirian Penuh & Pengawalan Budaya',
                    'timeframe' => 'Bulan 7+',
                    'desc' => 'Mengemban akuntabilitas penuh atas kinerja unit kerja, pembinaan kader penerus (talent pipeline), dan pengambilan keputusan strategis.',
                ],
            ],
            $isHighPerformerOrCore => [
                [
                    'title' => 'Fase 1: Pengayaan Kompetensi Manajerial',
                    'timeframe' => 'Bulan 1–3',
                    'desc' => 'Mengikuti program coaching kepemimpinan intensif untuk memperkuat wawasan perencanaan strategis dan pengaruh komunikasi publik.',
                ],
                [
                    'title' => 'Fase 2: Rotasi Penugasan Proyek',
                    'timeframe' => 'Bulan 4–6',
                    'desc' => 'Diberikan wewenang memimpin proyek percontohan dengan target peningkatan produktivitas tim kerja.',
                ],
                [
                    'title' => 'Fase 3: Evaluasi Kesiapan Promosi',
                    'timeframe' => 'Bulan 7+',
                    'desc' => 'Peninjauan kembali capaian KPI dan kompetensi kepemimpinan untuk penetapan definitif pada jabatan target.',
                ],
            ],
            default => [
                [
                    'title' => 'Fase 1: Konsolidasi & Stabilisasi Performa',
                    'timeframe' => 'Bulan 1–3',
                    'desc' => 'Fokus pada penyelarasan target operasional harian dan pemenuhan standar mutu unit kerja.',
                ],
                [
                    'title' => 'Fase 2: Mentoring & Pelatihan Keterampilan',
                    'timeframe' => 'Bulan 4–6',
                    'desc' => 'Pendampingan langsung oleh atasan serta penyelesaian modul pengembangan teknis fungsional.',
                ],
                [
                    'title' => 'Fase 3: Uji Kapabilitas Berkelanjutan',
                    'timeframe' => 'Bulan 7+',
                    'desc' => 'Evaluasi berkala terhadap konsistensi capaian target sebelum dipertimbangkan dalam suksesi berikutnya.',
                ],
            ],
        };

        $readinessStatus = match (true) {
            $isStarOrHighPot => 'Ready Now (Siap Promosi Segera)',
            $isHighPerformerOrCore => 'Ready 1–2 Years (Akselerasi Terarah)',
            default => 'Ready 2–3 Years (Pengembangan Kapabilitas)',
        };

        return view('livewire.pages.h-c-a.sections.next-role-recommendation', [
            'participant' => $participant,
            'recommendedRole' => $recommendedRole,
            'readinessStatus' => $readinessStatus,
            'phases' => $phases,
        ]);
    }
}
