<?php

namespace App\Livewire\Pages\IndividualReport;

use App\Models\CategoryType;
use App\Models\FinalAssessment;
use App\Models\Participant;
use App\Services\Cache\AspectCacheService;
use App\Services\IndividualAssessmentService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => '<i>General Matching</i>'])]
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

    private ?array $allMatchingDataCache = null; // Cache for batch loaded data

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
        'standard-switched' => 'handleStandardSwitch',
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

        // Load participant with comprehensive relations to eliminate N+1 queries
        $this->participant = Participant::with([
            'assessmentEvent',
            'batch',
            'positionFormation.template',
            'positionFormation', // Include position formation itself
        ])
            ->whereHas('assessmentEvent', function ($query) {
                $query->where('code', $this->eventCode)
                    ->where('institution_id', auth()->user()->institution_id ?? 4); // Add institution filter
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
     * PHASE 3: Handle custom standard switch event
     */
    public function handleStandardSwitch(int $templateId): void
    {
        // Reuse the same logic as handleStandardUpdate
        $this->handleStandardUpdate($templateId);
    }

    /**
     * Clear all caches
     */
    private function clearCache(): void
    {
        $this->potensiAspectsCache = null;
        $this->kompetensiAspectsCache = null;
        AspectCacheService::clearCache();
    }

    /**
     * Load matching data using IndividualAssessmentService (BATCH LOADING)
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

        // Single batch call to get both Potensi and Kompetensi data
        $allData = $service->getAllAspectMatchingData($this->participant);

        // Cache the batch data to avoid duplicate calls
        $this->allMatchingDataCache = $allData;

        // Set Potensi data (convert Collection to array for cache compatibility)
        $this->potensiAspects = $allData['potensi'] ?? [];
        $this->potensiAspectsCache = is_array($this->potensiAspects) ? $this->potensiAspects : $this->potensiAspects->toArray();

        // Set Kompetensi data (convert Collection to array for cache compatibility)
        $this->kompetensiAspects = $allData['kompetensi'] ?? [];
        $this->kompetensiAspectsCache = is_array($this->kompetensiAspects) ? $this->kompetensiAspects : $this->kompetensiAspects->toArray();

        // Calculate job match percentage using cached batch data
        $jobMatching = $this->calculateJobMatchPercentage($allData);
        $this->jobMatchPercentage = $jobMatching['job_match_percentage'];
    }

    /**
     * Calculate job match percentage from cached batch data (no additional queries)
     */
    private function calculateJobMatchPercentage(array $allData): array
    {
        $allPercentages = [];
        $potensiPercentages = [];
        $kompetensiPercentages = [];

        // Extract percentages from batch data
        if (isset($allData['potensi'])) {
            foreach ($allData['potensi'] as $aspect) {
                $allPercentages[] = $aspect['percentage'];
                $potensiPercentages[] = $aspect['percentage'];
            }
        }

        if (isset($allData['kompetensi'])) {
            foreach ($allData['kompetensi'] as $aspect) {
                $allPercentages[] = $aspect['percentage'];
                $kompetensiPercentages[] = $aspect['percentage'];
            }
        }

        // Calculate averages
        $jobMatchPercentage = count($allPercentages) > 0
            ? round(array_sum($allPercentages) / count($allPercentages))
            : 0;

        $potensiAverage = count($potensiPercentages) > 0
            ? round(array_sum($potensiPercentages) / count($potensiPercentages))
            : 0;

        $kompetensiAverage = count($kompetensiPercentages) > 0
            ? round(array_sum($kompetensiPercentages) / count($kompetensiPercentages))
            : 0;

        return [
            'job_match_percentage' => $jobMatchPercentage,
            'potensi_percentage' => $potensiAverage,
            'kompetensi_percentage' => $kompetensiAverage,
        ];
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
        return view('livewire.pages.individual-report.general-matching');
    }
}
