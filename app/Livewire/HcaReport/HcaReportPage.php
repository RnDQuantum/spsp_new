<?php

declare(strict_types=1);

namespace App\Livewire\HcaReport;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\View\View;

#[Layout('components.layouts.hca-layout', ['title' => 'Human Capital Assessment Report'])]
class HcaReportPage extends Component
{
    /**
     * Active section code
     */
    public string $activeSection = 'cover';

    /**
     * Print mode flag
     */
    public bool $printMode = false;

    /**
     * Available sections grouping for sidebar TOC
     */
    public array $menuGroups = [
        [
            'title' => 'Pembuka',
            'icon' => 'fa-file-invoice',
            'sections' => [
                ['code' => 'cover', 'label' => '01 — Cover Page', 'active' => true],
                ['code' => 'exec_summary', 'label' => '02 — Ringkasan Eksekutif', 'active' => true],
                ['code' => 'participant_id', 'label' => '03 — Identitas Peserta', 'active' => true],
                ['code' => 'hci', 'label' => '04 — Human Capital Index', 'active' => true],
            ]
        ],
        [
            'title' => 'Kompetensi & Potensi (Layer 1–2)',
            'icon' => 'fa-layer-group',
            'sections' => [
                ['code' => 'competency', 'label' => '05 — Layer 1: Kompetensi', 'active' => true],
                ['code' => 'career', 'label' => '06 — Riwayat Karier', 'active' => true],
                ['code' => 'potential', 'label' => '07 — Layer 2: Potensi', 'active' => true],
                ['code' => 'cognitive', 'label' => '08 — IQ & Profil Kognitif', 'active' => true],
            ]
        ],
        [
            'title' => 'Kepribadian & Perilaku',
            'icon' => 'fa-brain',
            'sections' => [
                ['code' => 'big_five', 'label' => '09 — Big Five Personality', 'active' => true],
                ['code' => 'disc', 'label' => '10 — DISC Profile', 'active' => true],
                ['code' => 'learning_agility', 'label' => '11 — Learning Agility', 'active' => true],
                ['code' => 'leadership_potential', 'label' => '12 — Leadership Potential', 'active' => true],
                ['code' => 'eq', 'label' => '13 — Emotional Intelligence (EQ)', 'active' => true],
                ['code' => 'integrity', 'label' => '14 — Values & Integrity', 'active' => true],
            ]
        ],
        [
            'title' => 'Kinerja & Kesiapan (Layer 3)',
            'icon' => 'fa-chart-line',
            'sections' => [
                ['code' => 'performance', 'label' => '15 — Performance Dashboard', 'active' => true],
                ['code' => 'nine_box', 'label' => '16 — Talent 9-Box Matrix', 'active' => true],
                ['code' => 'succession', 'label' => '17 — Succession Readiness', 'active' => true],
            ]
        ],
        [
            'title' => 'Kesehatan & Risiko',
            'icon' => 'fa-heart-circle-check',
            'sections' => [
                ['code' => 'personal_profile', 'label' => '18 — Profil Personal (Pelengkap)', 'active' => true],
                ['code' => 'mental_health', 'label' => '19 — Kesehatan Jiwa', 'active' => true],
                ['code' => 'strengths', 'label' => '20 — Kekuatan Psikologis', 'active' => true],
                ['code' => 'risk_indicators', 'label' => '21 — Indikator Risiko', 'active' => true],
            ]
        ],
        [
            'title' => 'Rekomendasi',
            'icon' => 'fa-lightbulb',
            'sections' => [
                ['code' => 'development_rec', 'label' => '22 — Rekomendasi Pengembangan', 'active' => true],
                ['code' => 'next_role_rec', 'label' => '23 — Rekomendasi Peran Berikutnya', 'active' => true],
            ]
        ]
    ];

    /**
     * Switch active section
     */
    public function setSection(string $sectionCode): void
    {
        // Check if section is active (Phase A)
        foreach ($this->menuGroups as $group) {
            foreach ($group['sections'] as $sec) {
                if ($sec['code'] === $sectionCode && $sec['active']) {
                    $this->activeSection = $sectionCode;
                    $this->printMode = false;
                    return;
                }
            }
        }
    }

    /**
     * Toggle print mode
     */
    public function togglePrintMode(bool $state): void
    {
        $this->printMode = $state;
    }

    /**
     * Render component
     */
    public function render(): View
    {
        return view('livewire.hca-report.hca-report-page');
    }
}
