<?php

namespace App\Livewire\Pages\IndividualReport;

use App\Models\CategoryType;
use App\Models\Participant;
use App\Services\IndividualAssessmentService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Ringkasan Kompetensi Manajerial'])]
class RingkasanMcMapping extends Component
{
    public ?Participant $participant = null;

    public ?CategoryType $kompetensiCategory = null;

    public $aspectsData = [];

    // Cache property untuk menyimpan hasil service call
    private ?array $aspectsDataCache = null;

    // Event listeners
    protected $listeners = [
        'standard-adjusted' => 'handleStandardUpdate',
    ];

    public function mount($eventCode, $testNumber): void
    {
        // Load participant
        $this->participant = Participant::with([
            'assessmentEvent',
            'batch',
            'positionFormation.template',
        ])
            ->whereHas('assessmentEvent', function ($query) use ($eventCode) {
                $query->where('code', $eventCode);
            })
            ->where('test_number', $testNumber)
            ->firstOrFail();

        $template = $this->participant->positionFormation->template;

        // Get kompetensi category type only
        $this->kompetensiCategory = CategoryType::where('template_id', $template->id)
            ->where('code', 'kompetensi')
            ->first();

        // Load aspects data
        $this->loadAspectsData();
    }

    /**
     * Handle standard adjustment event
     */
    public function handleStandardUpdate(int $templateId): void
    {
        // Validate same template
        if ($this->participant->positionFormation->template_id !== $templateId) {
            return;
        }

        // Clear cache & reload
        $this->clearCache();
        $this->loadAspectsData();
    }

    /**
     * Clear all caches
     */
    private function clearCache(): void
    {
        $this->aspectsDataCache = null;
    }

    /**
     * Load aspects data using IndividualAssessmentService
     */
    private function loadAspectsData(): void
    {
        if (! $this->kompetensiCategory) {
            $this->aspectsData = [];

            return;
        }

        // Check cache first
        if ($this->aspectsDataCache !== null) {
            $this->aspectsData = $this->aspectsDataCache;

            return;
        }

        // Call service to get aspect assessments (with adjusted values from session)
        $service = app(IndividualAssessmentService::class);

        $aspectAssessments = $service->getAspectAssessments(
            $this->participant->id,
            $this->kompetensiCategory->id,
            0 // No tolerance for this report (simple comparison)
        );

        // Map to view format with conclusion logic
        $data = $aspectAssessments->map(function ($assessment, $index) {
            return [
                'number' => $index + 1,
                'name' => $assessment['name'] ?? $assessment['aspect_name'] ?? 'Unknown',
                'individual_rating' => $assessment['individual_rating'],
                'standard_rating' => $assessment['standard_rating'],
                'conclusion' => $this->getConclusionData(
                    (float) $assessment['individual_rating'],
                    (float) $assessment['standard_rating']
                ),
            ];
        })->toArray();

        // Store in cache and property
        $this->aspectsData = $data;
        $this->aspectsDataCache = $data;
    }

    /**
     * Get conclusion data based on gap between individual and standard rating
     * This is specific to Ringkasan MC Mapping report format
     */
    private function getConclusionData(float $individualRating, float $standardRating): array
    {
        $gap = $individualRating - $standardRating;

        // Determine conclusion based on gap
        if ($gap >= 1) {
            return [
                'title' => 'Sangat Kompeten',
                'description' => 'Perilaku yang ditunjukkan melampaui tingkatan yang diperlukan',
                'color' => 'blue',
            ];
        } elseif ($gap >= 0) {
            return [
                'title' => 'Kompeten',
                'description' => 'Perilaku yang ditunjukkan sesuai dengan tingkat yang diperlukan',
                'color' => 'green',
            ];
        } elseif ($gap >= -0.5) {
            return [
                'title' => 'Cukup Kompeten',
                'description' => 'Perilaku yang ditunjukkan cukup sesuai dengan tingkatan yang diperlukan, dengan tetap memerlukan pengembangan perilaku',
                'color' => 'yellow',
            ];
        } elseif ($gap >= -1) {
            return [
                'title' => 'Kurang Kompeten',
                'description' => 'Perilaku yang ditunjukkan kurang sesuai dengan tingkatan yang diperlukan dan memerlukan pengembangan perilaku',
                'color' => 'red',
            ];
        } else {
            return [
                'title' => 'Belum Kompeten',
                'description' => 'Perilaku yang ditunjukkan belum sesuai dengan tingkatan yang diperlukan dan sangat memerlukan pengembangan perilaku',
                'color' => 'pink',
            ];
        }
    }

    public function render()
    {
        return view('livewire.pages.individual-report.ringkasan-mc-mapping');
    }
}
