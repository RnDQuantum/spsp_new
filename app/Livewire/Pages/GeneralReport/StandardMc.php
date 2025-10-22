<?php

namespace App\Livewire\Pages\GeneralReport;

use App\Models\AssessmentEvent;
use App\Models\AssessmentTemplate;
use App\Models\CategoryType;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Standar Pemetaan Kompetensi Individu'])]
class StandardMc extends Component
{
    protected $listeners = [
        'event-selected' => 'handleEventSelected',
        'position-selected' => 'handlePositionSelected',
    ];

    public ?AssessmentEvent $selectedEvent = null;

    public ?AssessmentTemplate $selectedTemplate = null;

    /** @var array<int, array{name:string,weight_percentage:int,standard_rating:float,aspects:array}> */
    public array $categoryData = [];

    public array $totals = [
        'total_aspects' => 0,
        'total_weight' => 0,
        'total_standard_rating_sum' => 0.0,
        'total_score' => 0.0,
    ];

    public array $chartData = [
        'labels' => [],
        'ratings' => [],
        'scores' => [],
    ];

    // Unique chart ID
    public string $chartId = '';

    public int $maxScore = 0;

    public function mount(): void
    {
        // Generate unique chart ID
        $this->chartId = 'standardMc' . uniqid();

        $this->loadStandardData();
    }

    public function handleEventSelected(?string $eventCode): void
    {
        // Event changed, position will be auto-reset by PositionSelector
        // Just reload data (position will be null, so data will be empty)
        $this->loadStandardData();

        // Dispatch event to update charts
        $this->dispatch('chartDataUpdated', [
            'labels' => $this->chartData['labels'],
            'ratings' => $this->chartData['ratings'],
            'scores' => $this->chartData['scores'],
            'templateName' => $this->selectedTemplate?->name ?? 'Standard',
            'maxScore' => $this->maxScore,
        ]);
    }

    public function handlePositionSelected(?int $positionFormationId): void
    {
        // Position selected, now we have both event and position
        // Load data with the new filters
        $this->loadStandardData();

        // Dispatch event to update charts
        $this->dispatch('chartDataUpdated', [
            'labels' => $this->chartData['labels'],
            'ratings' => $this->chartData['ratings'],
            'scores' => $this->chartData['scores'],
            'templateName' => $this->selectedTemplate?->name ?? 'Standard',
            'maxScore' => $this->maxScore,
        ]);
    }



    private function loadStandardData(): void
    {
        $this->categoryData = [];
        $this->totals = [
            'total_aspects' => 0,
            'total_weight' => 0,
            'total_standard_rating_sum' => 0.0,
            'total_score' => 0.0,
        ];
        $this->chartData = [
            'labels' => [],
            'ratings' => [],
            'scores' => [],
        ];

        // Read filters from session
        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if (!$eventCode || !$positionFormationId) {
            return;
        }

        $this->selectedEvent = AssessmentEvent::query()
            ->with('institution', 'positionFormations.template')
            ->where('code', $eventCode)
            ->first();

        if (!$this->selectedEvent) {
            return;
        }

        // Get the selected position formation
        $selectedPosition = $this->selectedEvent->positionFormations
            ->where('id', $positionFormationId)
            ->first();

        if (!$selectedPosition) {
            return;
        }

        // Get template from the selected position
        $this->selectedTemplate = $selectedPosition->template;

        if (!$this->selectedTemplate) {
            return;
        }

        // Load ONLY Kompetensi category type with aspects from selected position's template
        $categories = CategoryType::query()
            ->where('template_id', $this->selectedTemplate->id)
            ->where('code', 'kompetensi')
            ->with([
                'aspects' => fn($q) => $q->orderBy('order'),
            ])
            ->orderBy('order')
            ->get();

        $totalAspects = 0;
        $totalWeight = 0;
        $totalStandardRatingSum = 0.0;
        $totalScore = 0.0;
        $totalAspectRatingSum = 0.0; // Sum of aspect average ratings

        foreach ($categories as $category) {
            $aspectsData = [];
            $categoryAspectCount = 0;
            $categoryWeightSum = 0;
            $categoryStandardRatingSum = 0.0;
            $categoryScoreSum = 0.0;

            foreach ($category->aspects as $aspect) {

                // Calculate aspect average rating from sub-aspects
                $aspectAvgRating = $aspect->standard_rating;

                // Calculate aspect score: rating × weight
                $aspectScore = round($aspectAvgRating * $aspect->weight_percentage, 2);

                $aspectsData[] = [
                    'name' => $aspect->name,
                    'weight_percentage' => $aspect->weight_percentage,
                    'standard_rating' => $aspectAvgRating,
                    'score' => $aspectScore,
                    'attribute_count' => 1,
                    'average_rating' => $aspectAvgRating,
                    'description' => $aspect->description,
                ];

                // For chart data - each aspect is a point on the chart
                $this->chartData['labels'][] = $aspect->name;
                $this->chartData['ratings'][] = $aspectAvgRating;
                $this->chartData['scores'][] = $aspectScore;
                $this->maxScore = max($this->maxScore, $aspectScore);

                $categoryWeightSum += $aspect->weight_percentage;
                $categoryStandardRatingSum += $aspectAvgRating;
                $categoryScoreSum += $aspectScore;
                $totalAspectRatingSum += $aspectAvgRating; // Track sum of aspect ratings
            }

            // Calculate category average rating
            $categoryAspectCount = $category->aspects->count();
            $categoryAvgRating = $categoryAspectCount > 0
                ? round($categoryStandardRatingSum / $categoryAspectCount, 2)
                : 0.0;

            $this->categoryData[] = [
                'name' => $category->name,
                'code' => $category->code,
                'weight_percentage' => $category->weight_percentage,
                'attribute_count' => $categoryAspectCount,
                'standard_rating' => $categoryAvgRating,
                'score' => $categoryScoreSum,
                'aspects' => $aspectsData,
            ];

            $totalAspects += $categoryAspectCount;
            $totalWeight += $categoryWeightSum;
            $totalStandardRatingSum += $categoryStandardRatingSum;
            $totalScore += $categoryScoreSum;
        }

        $this->totals = [
            'total_aspects' => $totalAspects,
            'total_weight' => $totalWeight,
            'total_standard_rating_sum' => round($totalStandardRatingSum, 2),
            'total_rating_sum' => round($totalAspectRatingSum, 2), // Sum of aspect average ratings
            'total_score' => round($totalScore, 2),
        ];
    }

    public function render()
    {
        // dd($this->categoryData);

        return view('livewire.pages.general-report.standard-mc');
    }
}
