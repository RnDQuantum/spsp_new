<?php

namespace App\Livewire\Pages\GeneralReport;

use App\Models\AssessmentEvent;
use App\Models\AssessmentTemplate;
use App\Models\CategoryType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app', ['title' => 'Standar Pemetaan Potensi Individu'])]
class StandardPsikometrik extends Component
{
    #[Url(as: 'event')]
    public ?string $eventCode = null;

    /** @var array<int, array{code:string,name:string,institution:string}> */
    public array $availableEvents = [];

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
        $this->chartId = 'standardPsikometrik'.uniqid();

        $this->loadAvailableEvents();

        if (! $this->eventCode && count($this->availableEvents) > 0) {
            $this->eventCode = $this->availableEvents[0]['code'] ?? null;
        }

        $this->loadStandardData();
    }

    public function updatedEventCode(): void
    {
        $this->loadStandardData();

        // Dispatch event to update charts
        $this->dispatch('chartDataUpdated', [
            'labels' => $this->chartData['labels'],
            'ratings' => $this->chartData['ratings'],
            'scores' => $this->chartData['scores'],
            'templateName' => $this->selectedTemplate?->name ?? 'Standard',
        ]);
    }

    private function loadAvailableEvents(): void
    {
        $this->availableEvents = AssessmentEvent::query()
            ->with('institution')
            ->orderByDesc('start_date')
            ->get()
            ->map(fn ($e) => [
                'code' => $e->code,
                'name' => $e->name,
                'institution' => $e->institution->name ?? 'N/A',
            ])
            ->all();
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

        if (! $this->eventCode) {
            return;
        }

        $this->selectedEvent = AssessmentEvent::query()
            ->with('institution', 'positionFormations.template')
            ->where('code', $this->eventCode)
            ->first();

        if (! $this->selectedEvent) {
            return;
        }

        // Get all unique template IDs used by positions in this event
        $templateIds = $this->selectedEvent->positionFormations->pluck('template_id')->unique()->all();

        if (empty($templateIds)) {
            return;
        }

        // For now, use the first template (or you can aggregate from all templates)
        $this->selectedTemplate = $this->selectedEvent->positionFormations->first()?->template;

        if (! $this->selectedTemplate) {
            return;
        }

        // Load ONLY Potensi category type with aspects and sub-aspects
        // Note: If event has multiple templates, this will only show the first template's data
        $categories = CategoryType::query()
            ->where('template_id', $this->selectedTemplate->id)
            ->where('code', 'potensi')
            ->with([
                'aspects' => fn ($q) => $q->orderBy('order'),
                'aspects.subAspects' => fn ($q) => $q->orderBy('order'),
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
