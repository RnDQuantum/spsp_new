<?php

namespace App\Livewire\Pages\IndividualReport;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\CategoryAssessment;
use App\Models\CategoryType;
use App\Models\Participant;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Spider Plot'])]
class SpiderPlot extends Component
{
    public ?Participant $participant = null;

    public ?CategoryType $potensiCategory = null;

    public ?CategoryType $kompetensiCategory = null;

    public ?CategoryAssessment $potensiAssessment = null;

    public ?CategoryAssessment $kompetensiAssessment = null;

    // Tolerance percentage (loaded from session)
    public int $tolerancePercentage = 10;

    // Chart IDs for unique identification
    public string $potensiChartId = '';

    public string $kompetensiChartId = '';

    public string $generalChartId = '';

    // Potensi Chart Data (5 aspects)
    public $potensiLabels = [];

    public $potensiStandardRatings = [];

    public $potensiIndividualRatings = [];

    // Kompetensi Chart Data (9 aspects)
    public $kompetensiLabels = [];

    public $kompetensiStandardRatings = [];

    public $kompetensiIndividualRatings = [];

    // General Chart Data (all 13+ aspects)
    public $generalLabels = [];

    public $generalStandardRatings = [];

    public $generalIndividualRatings = [];

    // Aspect data for calculations
    public $potensiAspectsData = [];

    public $kompetensiAspectsData = [];

    public $allAspectsData = [];

    public function mount($eventCode, $testNumber): void
    {
        // Generate unique chart IDs
        $this->potensiChartId = 'potensiSpider'.uniqid();
        $this->kompetensiChartId = 'kompetensiSpider'.uniqid();
        $this->generalChartId = 'generalSpider'.uniqid();

        // Load tolerance from session
        $this->tolerancePercentage = session('individual_report.tolerance', 10);

        // Load participant
        $this->participant = Participant::with([
            'assessmentEvent.template',
            'batch',
            'positionFormation',
        ])
            ->whereHas('assessmentEvent', function ($query) use ($eventCode) {
                $query->where('code', $eventCode);
            })
            ->where('test_number', $testNumber)
            ->firstOrFail();

        $template = $this->participant->assessmentEvent->template;

        // Get category types
        $this->potensiCategory = CategoryType::where('template_id', $template->id)
            ->where('code', 'potensi')
            ->first();

        $this->kompetensiCategory = CategoryType::where('template_id', $template->id)
            ->where('code', 'kompetensi')
            ->first();

        // Get category assessments
        if ($this->potensiCategory) {
            $this->potensiAssessment = CategoryAssessment::where('participant_id', $this->participant->id)
                ->where('category_type_id', $this->potensiCategory->id)
                ->first();
        }

        if ($this->kompetensiCategory) {
            $this->kompetensiAssessment = CategoryAssessment::where('participant_id', $this->participant->id)
                ->where('category_type_id', $this->kompetensiCategory->id)
                ->first();
        }

        // Load all aspects data
        $this->loadAspectsData();

        // Prepare chart data
        $this->prepareChartData();
    }

    private function loadAspectsData(): void
    {
        // Load Potensi aspects
        if ($this->potensiCategory) {
            $this->potensiAspectsData = $this->loadCategoryAspects($this->potensiCategory->id);
        }

        // Load Kompetensi aspects
        if ($this->kompetensiCategory) {
            $this->kompetensiAspectsData = $this->loadCategoryAspects($this->kompetensiCategory->id);
        }

        // Combine all aspects
        $this->allAspectsData = array_merge($this->potensiAspectsData, $this->kompetensiAspectsData);
    }

    private function loadCategoryAspects(int $categoryTypeId): array
    {
        $aspectIds = Aspect::where('category_type_id', $categoryTypeId)
            ->orderBy('order')
            ->pluck('id')
            ->toArray();

        $aspectAssessments = AspectAssessment::with('aspect')
            ->where('participant_id', $this->participant->id)
            ->whereIn('aspect_id', $aspectIds)
            ->orderBy('aspect_id')
            ->get();

        return $aspectAssessments->map(fn ($assessment) => [
            'name' => $assessment->aspect->name,
            'standard_rating' => $assessment->standard_rating,
            'individual_rating' => $assessment->individual_rating,
        ])->toArray();
    }

    private function prepareChartData(): void
    {
        // Clear previous data
        $this->potensiLabels = [];
        $this->potensiStandardRatings = [];
        $this->potensiIndividualRatings = [];

        $this->kompetensiLabels = [];
        $this->kompetensiStandardRatings = [];
        $this->kompetensiIndividualRatings = [];

        $this->generalLabels = [];
        $this->generalStandardRatings = [];
        $this->generalIndividualRatings = [];

        // Prepare Potensi chart data
        foreach ($this->potensiAspectsData as $aspect) {
            $this->potensiLabels[] = $aspect['name'];
            $this->potensiStandardRatings[] = round($aspect['standard_rating'], 2);
            $this->potensiIndividualRatings[] = round($aspect['individual_rating'], 2);
        }

        // Prepare Kompetensi chart data
        foreach ($this->kompetensiAspectsData as $aspect) {
            $this->kompetensiLabels[] = $aspect['name'];
            $this->kompetensiStandardRatings[] = round($aspect['standard_rating'], 2);
            $this->kompetensiIndividualRatings[] = round($aspect['individual_rating'], 2);
        }

        // Prepare General chart data (all aspects)
        foreach ($this->allAspectsData as $aspect) {
            $this->generalLabels[] = $aspect['name'];
            $this->generalStandardRatings[] = round($aspect['standard_rating'], 2);
            $this->generalIndividualRatings[] = round($aspect['individual_rating'], 2);
        }
    }

    /**
     * Listen to tolerance updates from ToleranceSelector component
     */
    protected $listeners = ['tolerance-updated' => 'handleToleranceUpdate'];

    /**
     * Handle tolerance update from child component
     */
    public function handleToleranceUpdate(int $tolerance): void
    {
        $this->tolerancePercentage = $tolerance;

        // Reload aspects data with new tolerance
        $this->loadAspectsData();

        // Update chart data
        $this->prepareChartData();

        // Get updated summary statistics
        $summary = $this->getPassingSummary();

        // Dispatch events to update all charts
        $this->dispatch('chartDataUpdated', [
            'chartType' => 'all',
            'tolerance' => $tolerance,
            'potensi' => [
                'labels' => $this->potensiLabels,
                'standardRatings' => $this->potensiStandardRatings,
                'individualRatings' => $this->potensiIndividualRatings,
            ],
            'kompetensi' => [
                'labels' => $this->kompetensiLabels,
                'standardRatings' => $this->kompetensiStandardRatings,
                'individualRatings' => $this->kompetensiIndividualRatings,
            ],
            'general' => [
                'labels' => $this->generalLabels,
                'standardRatings' => $this->generalStandardRatings,
                'individualRatings' => $this->generalIndividualRatings,
            ],
        ]);

        // Dispatch event to update summary statistics in ToleranceSelector
        $this->dispatch('summary-updated', [
            'passing' => $summary['passing'],
            'total' => $summary['total'],
            'percentage' => $summary['percentage'],
        ]);
    }

    /**
     * Get summary statistics for passing aspects
     */
    public function getPassingSummary(): array
    {
        $totalAspects = count($this->allAspectsData);
        $passingAspects = 0;

        foreach ($this->allAspectsData as $aspect) {
            // Calculate tolerance threshold based on standard rating
            $toleranceThreshold = -($aspect['standard_rating'] * ($this->tolerancePercentage / 100));
            $gapRating = $aspect['individual_rating'] - $aspect['standard_rating'];

            // Count as passing if meets or exceeds standard (with tolerance)
            if ($gapRating >= $toleranceThreshold) {
                $passingAspects++;
            }
        }

        return [
            'total' => $totalAspects,
            'passing' => $passingAspects,
            'percentage' => $totalAspects > 0 ? round(($passingAspects / $totalAspects) * 100) : 0,
        ];
    }

    public function render()
    {
        return view('livewire.pages.individual-report.spider-plot');
    }
}
