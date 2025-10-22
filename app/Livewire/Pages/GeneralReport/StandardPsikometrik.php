<?php

namespace App\Livewire\Pages\GeneralReport;

use App\Models\AssessmentEvent;
use App\Models\AssessmentTemplate;
use App\Models\CategoryType;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Standar Pemetaan Potensi Individu'])]
class StandardPsikometrik extends Component
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

    public function mount(): void
    {
        // Generate unique chart ID
        $this->chartId = 'standardPsikometrik' . uniqid();

        $this->loadStandardData();
    }

    public function handleEventSelected(?string $eventCode): void
    {
        // Event changed, position will be auto-reset by PositionSelector
        // Just reload data (position will be null, so data will be empty)
        $this->loadStandardData();
    }

    public function handlePositionSelected(?int $positionFormationId): void
    {
        // Position selected, now we have both event and position
        // Load data with the new filters
        $this->loadStandardData();
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

        // Load ONLY Potensi category type with aspects and sub-aspects from selected position's template
        $categories = CategoryType::query()
            ->where('template_id', $this->selectedTemplate->id)
            ->where('code', 'potensi')
            ->with([
                'aspects' => fn($q) => $q->orderBy('order'),
                'aspects.subAspects' => fn($q) => $q->orderBy('order'),
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
            $categoryAspectsCount = 0;
            $categoryWeightSum = 0;
            $categoryStandardRatingSum = 0.0;
            $categoryScoreSum = 0.0;
            $categorySubAspectStandardSum = 0.0;

            foreach ($category->aspects as $aspect) {
                $subAspectsData = [];
                $subAspectsCount = $aspect->subAspects->count();
                $subAspectsStandardSum = 0;

                foreach ($aspect->subAspects as $subAspect) {
                    $subAspectsData[] = [
                        'name' => $subAspect->name,
                        'standard_rating' => $subAspect->standard_rating,
                        'description' => $subAspect->description,
                    ];
                    $subAspectsStandardSum += $subAspect->standard_rating;
                }

                // Calculate aspect average rating from sub-aspects
                $aspectAvgRating = $subAspectsCount > 0
                    ? round($subAspectsStandardSum / $subAspectsCount, 2)
                    : $aspect->standard_rating;

                // Calculate aspect score: rating × weight
                $aspectScore = round($aspectAvgRating * $aspect->weight_percentage, 2);

                $aspectsData[] = [
                    'name' => $aspect->name,
                    'weight_percentage' => $aspect->weight_percentage,
                    'standard_rating' => $aspectAvgRating,
                    'sub_aspects_count' => $subAspectsCount,
                    'sub_aspects' => $subAspectsData,
                    'sub_aspects_standard_sum' => $subAspectsStandardSum,
                    'score' => $aspectScore,
                ];

                // For chart data - each aspect is a point on the chart
                $this->chartData['labels'][] = $aspect->name;
                $this->chartData['ratings'][] = $aspectAvgRating;
                $this->chartData['scores'][] = $aspectScore;

                $categoryAspectsCount += $subAspectsCount;
                $categoryWeightSum += $aspect->weight_percentage;
                $categoryStandardRatingSum += $aspectAvgRating;
                $categoryScoreSum += $aspectScore;
                $categorySubAspectStandardSum += $subAspectsStandardSum;
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
                'aspects_count' => $categoryAspectsCount,
                'standard_rating' => $categoryAvgRating,
                'score' => $categoryScoreSum,
                'aspects' => $aspectsData,
            ];

            $totalAspects += $categoryAspectsCount;
            $totalWeight += $categoryWeightSum;
            $totalStandardRatingSum += $categorySubAspectStandardSum;
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
        return view('livewire.pages.general-report.standard-psikometrik');
    }
}
