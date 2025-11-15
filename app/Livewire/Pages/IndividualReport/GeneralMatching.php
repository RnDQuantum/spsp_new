<?php

namespace App\Livewire\Pages\IndividualReport;

use App\Models\CategoryType;
use App\Models\FinalAssessment;
use App\Models\Participant;
use App\Services\IndividualAssessmentService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'General Matching'])]
class GeneralMatching extends Component
{
    // Participant info
    public ?Participant $participant = null;

    public ?FinalAssessment $finalAssessment = null;

    // Category types
    public ?CategoryType $potensiCategory = null;

    public ?CategoryType $kompetensiCategory = null;

    // Aspect assessments grouped by category
    public $potensiAspects = [];

    public $kompetensiAspects = [];

    // Job match percentage (from final assessment)
    public $jobMatchPercentage = 0;

    // CACHE PROPERTIES - untuk menyimpan hasil kalkulasi
    private ?array $potensiAspectsCache = null;

    private ?array $kompetensiAspectsCache = null;

    // ADD: Public properties untuk support child component
    public $eventCode;

    public $testNumber;

    // Flag untuk menentukan apakah standalone atau child
    public $isStandalone = true;

    // Dynamic display parameters
    public $showHeader = true;

    public $showInfoSection = true;

    public $showPotensi = true;

    public $showKompetensi = true;

    // Event listeners
    protected $listeners = [
        'standard-adjusted' => 'handleStandardUpdate',
    ];

    public function mount($eventCode, $testNumber, $showHeader = true, $showInfoSection = true, $showPotensi = true, $showKompetensi = true): void
    {
        // Gunakan parameter jika ada (dari route), atau fallback ke property (dari parent)
        $this->eventCode = $eventCode ?? $this->eventCode;
        $this->testNumber = $testNumber ?? $this->testNumber;

        // Set dynamic display parameters
        $this->showHeader = $showHeader;
        $this->showInfoSection = $showInfoSection;
        $this->showPotensi = $showPotensi;
        $this->showKompetensi = $showKompetensi;

        // Tentukan apakah standalone (dari route) atau child (dari parent)
        $this->isStandalone = $eventCode !== null && $testNumber !== null;

        // Validate
        if (! $this->eventCode || ! $this->testNumber) {
            abort(404, 'Event code and test number are required');
        }

        // Load participant with relations based on event code and test number
        $this->participant = Participant::with([
            'assessmentEvent',
            'batch',
            'positionFormation.template',
        ])
            ->whereHas('assessmentEvent', function ($query) {
                $query->where('code', $this->eventCode);
            })
            ->where('test_number', $this->testNumber)
            ->firstOrFail();

        // Load final assessment
        $this->finalAssessment = FinalAssessment::where('participant_id', $this->participant->id)->first();

        // Get template from position
        $template = $this->participant->positionFormation->template;

        // Get category types for this template
        $this->potensiCategory = CategoryType::where('template_id', $template->id)
            ->where('code', 'potensi')
            ->first();

        $this->kompetensiCategory = CategoryType::where('template_id', $template->id)
            ->where('code', 'kompetensi')
            ->first();

        // Load aspect assessments using service
        $this->loadMatchingData();
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
        $this->loadMatchingData();
    }

    /**
     * Clear all caches
     */
    private function clearCache(): void
    {
        $this->potensiAspectsCache = null;
        $this->kompetensiAspectsCache = null;
    }

    /**
     * Load matching data using IndividualAssessmentService
     */
    private function loadMatchingData(): void
    {
        // Check cache first - both must be set to use cache
        if ($this->potensiAspectsCache !== null && $this->kompetensiAspectsCache !== null) {
            $this->potensiAspects = $this->potensiAspectsCache;
            $this->kompetensiAspects = $this->kompetensiAspectsCache;

            return;
        }

        $service = app(IndividualAssessmentService::class);

        // Load Potensi aspects
        if ($this->potensiCategory) {
            $data = $service->getAspectMatchingData(
                $this->participant->id,
                $this->potensiCategory->id
            )->toArray();

            $this->potensiAspects = $data;
            $this->potensiAspectsCache = $data;
        }

        // Load Kompetensi aspects
        if ($this->kompetensiCategory) {
            $data = $service->getAspectMatchingData(
                $this->participant->id,
                $this->kompetensiCategory->id
            )->toArray();

            $this->kompetensiAspects = $data;
            $this->kompetensiAspectsCache = $data;
        }

        // Calculate job match percentage using service
        $jobMatching = $service->getJobMatchingPercentage($this->participant->id);
        $this->jobMatchPercentage = $jobMatching['job_match_percentage'];
    }

    public function getAspectPercentage(string $aspectCode): int
    {
        // Search in potensi
        $potensi = collect($this->potensiAspects)->firstWhere('code', $aspectCode);
        if ($potensi) {
            return $potensi['percentage'];
        }

        // Search in kompetensi
        $kompetensi = collect($this->kompetensiAspects)->firstWhere('code', $aspectCode);
        if ($kompetensi) {
            return $kompetensi['percentage'];
        }

        return 0;
    }

    public function getSubAspectRating(string $aspectCode, string $subAspectName): ?int
    {
        $aspect = collect($this->potensiAspects)->firstWhere('code', $aspectCode)
            ?? collect($this->kompetensiAspects)->firstWhere('code', $aspectCode);

        if (! $aspect) {
            return null;
        }

        $subAspect = collect($aspect['sub_aspects'])->firstWhere('name', $subAspectName);

        return $subAspect ? $subAspect['individual_rating'] : null;
    }

    public function render()
    {
        // Jika standalone, gunakan layout. Jika child, tidak pakai layout
        if ($this->isStandalone) {
            return view('livewire.pages.individual-report.general-matching');
        }

        return view('livewire.pages.individual-report.general-matching');
    }
}
