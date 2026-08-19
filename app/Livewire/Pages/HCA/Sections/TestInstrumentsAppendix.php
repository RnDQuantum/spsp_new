<?php

declare(strict_types=1);

namespace App\Livewire\Pages\HCA\Sections;

use App\Models\Participant;
use App\Services\TestReportService;
use Illuminate\View\View;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class TestInstrumentsAppendix extends Component
{
    #[Reactive]
    public ?int $participantId = null;

    /**
     * Selected category for interactive tab filtering
     */
    public string $selectedCategory = 'all';

    /**
     * Map test codes to thematic categories
     */
    public array $categoryMap = [
        'A.1' => 'cognitive',
        'A.2' => 'cognitive',
        'A.5' => 'cognitive',
        'B.1' => 'personality',
        'B.2' => 'personality',
        'D.1' => 'personality',
        'G.1' => 'personality',
        'D.2' => 'work_attitude',
        'E.1' => 'clinical',
        'E.2' => 'clinical',
        'F.1' => 'emotional_interest',
        'H.1' => 'emotional_interest',
    ];

    /**
     * Category labels and icons
     */
    public array $categories = [
        'all' => ['label' => 'Semua Alat Tes', 'icon' => 'fa-layer-group'],
        'cognitive' => ['label' => 'Kognitif & Inteligensi', 'icon' => 'fa-brain'],
        'personality' => ['label' => 'Kepribadian & Karakter', 'icon' => 'fa-masks-theater'],
        'work_attitude' => ['label' => 'Sikap Kerja & Ketahanan', 'icon' => 'fa-bolt'],
        'clinical' => ['label' => 'Kesehatan Jiwa & Klinis', 'icon' => 'fa-heart-pulse'],
        'emotional_interest' => ['label' => 'EQ & Minat Kerja', 'icon' => 'fa-compass'],
    ];

    /**
     * Switch category filter
     */
    public function setCategory(string $category): void
    {
        if (array_key_exists($category, $this->categories)) {
            $this->selectedCategory = $category;
        }
    }

    /**
     * Get active participant model
     */
    public function getParticipantProperty(): ?Participant
    {
        if (! $this->participantId) {
            return Participant::with([
                'assessmentEvent.institution',
                'assessmentEvent.project',
                'positionFormation',
                'batch',
                'mmpi',
            ])->first();
        }

        return Participant::with([
            'assessmentEvent.institution',
            'assessmentEvent.project',
            'positionFormation',
            'batch',
            'mmpi',
        ])->find($this->participantId);
    }

    /**
     * Get all test reports for active participant
     */
    public function getTestReports(TestReportService $testReportService): array
    {
        $participant = $this->participant;
        if (! $participant || ! $participant->event_id) {
            return [];
        }

        return $testReportService->getParticipantAllTestReports($participant->id, $participant->event_id);
    }

    /**
     * Render component
     */
    public function render(TestReportService $testReportService): View
    {
        $participant = $this->participant;
        $allReports = $this->getTestReports($testReportService);

        // Calculate counts per category
        $categoryCounts = [
            'all' => count($allReports) + ($participant?->mmpi ? 1 : 0),
            'cognitive' => 0,
            'personality' => 0,
            'work_attitude' => 0,
            'clinical' => $participant?->mmpi ? 1 : 0,
            'emotional_interest' => 0,
        ];

        foreach ($allReports as $code => $report) {
            $cat = $this->categoryMap[$code] ?? 'personality';
            if (isset($categoryCounts[$cat])) {
                $categoryCounts[$cat]++;
            }
        }

        // Filter reports based on selected category
        $filteredReports = [];
        if ($this->selectedCategory === 'all') {
            $filteredReports = $allReports;
        } else {
            foreach ($allReports as $code => $report) {
                $cat = $this->categoryMap[$code] ?? 'personality';
                if ($cat === $this->selectedCategory) {
                    $filteredReports[$code] = $report;
                }
            }
        }

        $showMmpi = ($this->selectedCategory === 'all' || $this->selectedCategory === 'clinical') && $participant?->mmpi;

        return view('livewire.pages.h-c-a.sections.test-instruments-appendix', [
            'participant' => $participant,
            'testReports' => $filteredReports,
            'allReports' => $allReports,
            'categoryCounts' => $categoryCounts,
            'showMmpi' => $showMmpi,
        ]);
    }
}
