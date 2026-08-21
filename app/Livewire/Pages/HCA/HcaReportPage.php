<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA;

use App\Models\AssessmentEvent;
use App\Models\Participant;
use App\Models\PositionFormation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.hca-layout', ['title' => 'Human Capital Assessment Report'])]
class HcaReportPage extends Component
{
    /**
     * Active participant ID
     */
    #[Url(as: 'participant_id')]
    public ?int $participantId = null;

    /**
     * Selected event code for modal filter
     */
    public ?string $selectedEventCode = null;

    /**
     * Selected position formation ID for modal filter
     */
    public ?int $selectedPositionId = null;

    /**
     * Active section code
     */
    public string $activeSection = 'cover';

    /**
     * Print mode flag
     */
    public bool $printMode = false;

    /**
     * Talent selector modal visibility flag
     */
    public bool $showTalentModal = false;

    /**
     * Search query for talent selector modal
     */
    public string $searchParticipant = '';

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
            ],
        ],
        [
            'title' => 'Kompetensi & Potensi (Layer 1–2)',
            'icon' => 'fa-layer-group',
            'sections' => [
                ['code' => 'competency', 'label' => '05 — Layer 1: Kompetensi', 'active' => true],
                ['code' => 'career', 'label' => '06 — Riwayat Karier', 'active' => true],
                ['code' => 'potential', 'label' => '07 — Layer 2: Potensi', 'active' => true],
                ['code' => 'cognitive', 'label' => '08 — IQ & Profil Kognitif', 'active' => true],
            ],
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
            ],
        ],
        [
            'title' => 'Kinerja & Kesiapan (Layer 3)',
            'icon' => 'fa-chart-line',
            'sections' => [
                ['code' => 'performance', 'label' => '15 — Performance Dashboard', 'active' => true],
                ['code' => 'nine_box', 'label' => '16 — Talent 9-Box Matrix', 'active' => true],
                ['code' => 'succession', 'label' => '17 — Succession Readiness', 'active' => true],
            ],
        ],
        [
            'title' => 'Kesehatan & Risiko',
            'icon' => 'fa-heart-circle-check',
            'sections' => [
                ['code' => 'personal_profile', 'label' => '18 — Profil Personal (Pelengkap)', 'active' => true],
                ['code' => 'mental_health', 'label' => '19 — Kesehatan Jiwa', 'active' => true],
                ['code' => 'strengths', 'label' => '20 — Kekuatan Psikologis', 'active' => true],
                ['code' => 'risk_indicators', 'label' => '21 — Indikator Risiko', 'active' => true],
            ],
        ],
        [
            'title' => 'Rekomendasi',
            'icon' => 'fa-lightbulb',
            'sections' => [
                ['code' => 'development_rec', 'label' => '22 — Rekomendasi Pengembangan', 'active' => true],
                ['code' => 'next_role_rec', 'label' => '23 — Rekomendasi Peran Berikutnya', 'active' => true],
            ],
        ],
        [
            'title' => 'Lampiran (Appendix)',
            'icon' => 'fa-folder-open',
            'sections' => [
                ['code' => 'test_instruments_appendix', 'label' => '24 — Laporan Hasil Alat Tes', 'active' => true],
            ],
        ],
    ];

    /**
     * Mount component
     */
    public function mount(string|int|null $participant = null): void
    {
        // Guard demo route in production environment
        if (request()->routeIs('hca-report-demo') && app()->environment('production') && ! auth()->check()) {
            abort(403, 'Akses demo dinonaktifkan di environment produksi.');
        }

        if ($participant) {
            if (is_numeric($participant)) {
                $this->participantId = (int) $participant;
            } else {
                $foundParticipant = Participant::query()->where('test_number', (string) $participant)->first();
                if ($foundParticipant) {
                    $this->participantId = $foundParticipant->id;
                }
            }
        }

        if (! $this->participantId) {
            $this->participantId = session('filter.participant_id');
        }

        if (! $this->participantId) {
            $this->participantId = Participant::query()->first()?->id;
        }

        if ($this->participantId && $p = $this->participant) {
            $this->selectedPositionId = $p->position_formation_id;
            $this->selectedEventCode = $p->assessmentEvent?->code ?? session('filter.event_code');

            session([
                'filter.participant_id' => $p->id,
                'filter.position_formation_id' => $p->position_formation_id,
                'filter.event_code' => $this->selectedEventCode,
                'filter.test_number' => $p->test_number,
            ]);
        }
    }

    /**
     * Select a participant
     */
    public function selectParticipant(int $id): void
    {
        $this->participantId = $id;
        $participant = Participant::with('assessmentEvent')->find($id);
        if ($participant) {
            $this->selectedPositionId = $participant->position_formation_id;
            $this->selectedEventCode = $participant->assessmentEvent?->code ?? session('filter.event_code');

            session([
                'filter.participant_id' => $participant->id,
                'filter.position_formation_id' => $participant->position_formation_id,
                'filter.event_code' => $this->selectedEventCode,
                'filter.test_number' => $participant->test_number,
            ]);
        }
        $this->showTalentModal = false;
    }

    /**
     * Toggle talent selector modal
     */
    public function toggleTalentModal(): void
    {
        $this->showTalentModal = ! $this->showTalentModal;

        if ($this->showTalentModal && $p = $this->participant) {
            $this->selectedEventCode = session('filter.event_code') ?? $p->assessmentEvent?->code;
            $this->selectedPositionId = session('filter.position_formation_id') ?? $p->position_formation_id;
        }
    }

    /**
     * Handle update of selected event code in modal
     */
    public function updatedSelectedEventCode(?string $value): void
    {
        $this->selectedPositionId = null;
        if ($value) {
            session(['filter.event_code' => $value]);
        } else {
            session()->forget('filter.event_code');
        }
        session()->forget('filter.position_formation_id');
    }

    /**
     * Handle update of selected position ID in modal
     */
    public function updatedSelectedPositionId(?int $value): void
    {
        if ($value) {
            session(['filter.position_formation_id' => $value]);
        } else {
            session()->forget('filter.position_formation_id');
        }
    }

    /**
     * Get active participant model property
     */
    public function getParticipantProperty(): ?Participant
    {
        if (! $this->participantId) {
            return null;
        }

        return Participant::with([
            'assessmentEvent.institution',
            'positionFormation.template',
            'batch',
            'finalAssessment',
            'mmpi',
            'institution',
            'personalProfile',
            'careerHistories',
            'performanceRecords',
        ])->find($this->participantId);
    }

    /**
     * Get available assessment events for selector
     */
    public function getEventsProperty(): Collection
    {
        return AssessmentEvent::query()
            ->orderByDesc('start_date')
            ->get(['code', 'name']);
    }

    /**
     * Get available positions for selector based on selected event
     */
    public function getPositionsProperty(): Collection
    {
        if (! $this->selectedEventCode) {
            return new Collection;
        }

        return PositionFormation::query()
            ->whereHas('assessmentEvent', fn ($q) => $q->where('code', $this->selectedEventCode))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Get available participants for talent switcher modal
     */
    public function getAvailableParticipantsProperty(): Collection
    {
        if (! $this->selectedEventCode && ! $this->selectedPositionId && empty(trim($this->searchParticipant))) {
            return new Collection;
        }

        $query = Participant::with(['positionFormation', 'assessmentEvent']);

        if ($this->selectedEventCode) {
            $query->whereHas('assessmentEvent', fn ($q) => $q->where('code', $this->selectedEventCode));
        }

        if ($this->selectedPositionId) {
            $query->where('position_formation_id', $this->selectedPositionId);
        }

        if (! empty(trim($this->searchParticipant))) {
            $search = trim($this->searchParticipant);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('test_number', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name')->take(50)->get();
    }

    /**
     * Helper to get participant initials
     */
    public function getInitials(?string $name): string
    {
        if (! $name) {
            return 'P';
        }

        $cleanName = explode(',', $name)[0];
        $words = array_filter(explode(' ', trim($cleanName)));

        if (count($words) === 0) {
            return 'P';
        }

        if (count($words) === 1) {
            return strtoupper(substr($words[0], 0, 2));
        }

        return strtoupper(substr($words[0], 0, 1).substr(end($words), 0, 1));
    }

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
        return view('livewire.pages.h-c-a.hca-report-page');
    }
}
