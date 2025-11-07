<?php

namespace App\Livewire\Pages\GeneralReport\Training;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\AssessmentEvent;
use App\Models\CategoryType;
use App\Models\Participant;
use App\Services\DynamicStandardService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Training Recommendation'])]
class TrainingRecommendation extends Component
{
    use WithPagination;


    public ?AssessmentEvent $selectedEvent = null;

    public ?Aspect $selectedAspect = null;

    public ?int $aspectId = null;

    // Add missing eventCode property
    public ?string $eventCode = null;

    // Tolerance percentage (loaded from session)
    public int $tolerancePercentage = 10;

    // Pagination
    public string $perPage = '10';

    // Summary data
    public int $totalParticipants = 0;

    public int $recommendedCount = 0;

    public int $notRecommendedCount = 0;

    public float $averageRating = 0;

    public float $standardRating = 0;

    public float $originalStandardRating = 0;

    // CACHE PROPERTIES - untuk menyimpan hasil kalkulasi
    private ?float $adjustedStandardsCache = null;
    private ?\Illuminate\Support\Collection $aspectPriorityCache = null;

    /**
     * Listen to filter component events
     */
    protected $listeners = [
        'event-selected' => 'handleEventSelected',
        'position-selected' => 'handlePositionSelected',
        'aspect-selected' => 'handleAspectSelected',
        'tolerance-updated' => 'handleToleranceUpdate',
    ];

    public function mount(): void
    {
        // Load tolerance from session
        $this->tolerancePercentage = session('training_recommendation.tolerance', 10);

        // Load aspect from session
        $this->aspectId = session('filter.aspect_id');

        // Load eventCode from session
        $this->eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if ($this->eventCode && $positionFormationId && $this->aspectId) {
            $this->loadEventAndAspect();
            $this->calculateSummaryData();
        }
    }

    /**
     * Handle event selection
     */
    public function handleEventSelected(?string $eventCode): void
    {
        $this->resetPage();
        $this->clearCache(); // Clear cache saat event berubah

        // Reset data
        $this->reset(['selectedEvent', 'aspectId', 'selectedAspect', 'totalParticipants', 'recommendedCount', 'notRecommendedCount', 'averageRating', 'standardRating', 'originalStandardRating']);
    }

    /**
     * Handle position selection
     */
    public function handlePositionSelected(?int $positionFormationId): void
    {
        $this->resetPage();
        $this->clearCache(); // Clear cache saat position berubah

        // Reset data (aspect will auto-reset)
        $this->reset(['aspectId', 'selectedAspect', 'totalParticipants', 'recommendedCount', 'notRecommendedCount', 'averageRating', 'standardRating', 'originalStandardRating']);
    }

    /**
     * Handle aspect selection
     */
    public function handleAspectSelected(?int $aspectId): void
    {
        $this->aspectId = $aspectId;

        if (! $aspectId) {
            $this->reset(['selectedAspect', 'totalParticipants', 'recommendedCount', 'notRecommendedCount', 'averageRating', 'standardRating', 'originalStandardRating']);

            return;
        }

        $this->loadEventAndAspect();
        $this->resetPage();
        $this->clearCache(); // Clear cache saat aspect berubah
        $this->calculateSummaryData();
    }

    /**
     * Handle tolerance update from ToleranceSelector component
     */
    public function handleToleranceUpdate(int $tolerance): void
    {
        $this->tolerancePercentage = $tolerance;

        // Persist to session
        session(['training_recommendation.tolerance' => $tolerance]);

        // Clear cache when tolerance changes
        $this->clearCache();

        // Recalculate summary data with new tolerance
        if ($this->eventCode && $this->aspectId) {
            $this->calculateSummaryData();
        }

        // Dispatch event to update summary statistics in ToleranceSelector
        $this->dispatch('summary-updated', [
            'passing' => $this->notRecommendedCount,
            'total' => $this->totalParticipants,
        ]);
    }

    public function updatedPerPage()
    {
        $this->perPage = $this->perPage === 'all' ? 999999 : (int) $this->perPage;
        $this->resetPage();
        $this->clearCache(); // Clear cache saat perPage berubah
    }

    /**
     * Load event and aspect from database
     */
    private function loadEventAndAspect(): void
    {
        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if ($eventCode) {
            $this->selectedEvent = AssessmentEvent::with('positionFormations.template')
                ->where('code', $eventCode)
                ->first();
        }

        if ($this->aspectId && $this->selectedEvent && $positionFormationId) {
            // Get selected position with template
            $position = $this->selectedEvent->positionFormations()
                ->with('template')
                ->find($positionFormationId);

            if ($position?->template) {
                $this->selectedAspect = Aspect::where('id', $this->aspectId)
                    ->where('template_id', $position->template_id)
                    ->first();
            }
        }
    }

    /**
     * Clear all caches
     */
    private function clearCache(): void
    {
        $this->adjustedStandardsCache = null;
        $this->aspectPriorityCache = null;
    }

    /**
     * Calculate summary data (total, recommended count, average rating)
     */
    private function calculateSummaryData(): void
    {
        $positionFormationId = session('filter.position_formation_id');

        if (! $this->selectedEvent || ! $this->selectedAspect || ! $positionFormationId) {
            return;
        }

        // Get adjusted standard rating from session or database
        $adjustedStandardRating = $this->getAdjustedStandardRating($this->selectedAspect->id, $positionFormationId);

        // Get original standard rating from session
        $this->originalStandardRating = $this->getOriginalStandardRating($this->selectedAspect->id, $positionFormationId);

        $this->standardRating = $adjustedStandardRating;

        // Get all aspect assessments for this event, position, and aspect
        $assessments = AspectAssessment::where('event_id', $this->selectedEvent->id)
            ->where('position_formation_id', $positionFormationId)
            ->where('aspect_id', $this->selectedAspect->id)
            ->get();

        $this->totalParticipants = $assessments->count();
        $this->recommendedCount = 0;
        $this->notRecommendedCount = 0;
        $totalRating = 0;

        // Calculate summary statistics
        foreach ($assessments as $assessment) {
            $individualRating = (float) $assessment->individual_rating;
            $totalRating += $individualRating;

            // Participant is recommended for training if individual rating < adjusted standard
            if ($individualRating < $adjustedStandardRating) {
                $this->recommendedCount++;
            } else {
                $this->notRecommendedCount++;
            }
        }

        // Calculate average rating
        $this->averageRating = $this->totalParticipants > 0
            ? round($totalRating / $this->totalParticipants, 2)
            : 0;
    }

    /**
     * Get original standard rating from session or database
     * For Potensi category, calculate average from active sub-aspects
     */
    private function getOriginalStandardRating(int $aspectId, int $positionFormationId): float
    {
        // Get selected position with template
        $position = $this->selectedEvent->positionFormations()
            ->with('template')
            ->find($positionFormationId);

        if (!$position || !$position->template) {
            return (float) $this->selectedAspect->standard_rating;
        }

        $templateId = $position->template_id;
        $standardService = app(DynamicStandardService::class);

        // Check if aspect belongs to Potensi category and has sub-aspects
        $aspect = Aspect::with('categoryType', 'subAspects')->find($aspectId);
        if ($aspect && $aspect->categoryType->code === 'potensi' && $aspect->subAspects->count() > 0) {
            // Calculate average from active sub-aspects
            $subAspectRatingSum = 0;
            $activeSubAspectsCount = 0;

            foreach ($aspect->subAspects as $subAspect) {
                // Check if sub-aspect is active
                if (!$standardService->isSubAspectActive($templateId, $subAspect->code)) {
                    continue; // Skip inactive sub-aspects
                }

                // Get adjusted sub-aspect rating from session
                $subRating = $standardService->getSubAspectRating($templateId, $subAspect->code);
                $subAspectRatingSum += $subRating;
                $activeSubAspectsCount++;
            }

            if ($activeSubAspectsCount > 0) {
                return $subAspectRatingSum / $activeSubAspectsCount;
            }
        }

        // For Kompetensi or aspects without sub-aspects, use aspect rating
        return $standardService->getAspectRating($templateId, $this->selectedAspect->code);
    }

    /**
     * Get adjusted standard rating from session or database
     * OPTIMIZED: Cache result untuk menghindari kalkulasi berulang
     */
    private function getAdjustedStandardRating(int $aspectId, int $positionFormationId): float
    {
        // Gunakan cache jika sudah ada
        if ($this->adjustedStandardsCache !== null) {
            return $this->adjustedStandardsCache;
        }

        // Get original standard rating
        $originalStandardRating = $this->getOriginalStandardRating($aspectId, $positionFormationId);

        // Calculate adjusted standard based on tolerance
        $toleranceFactor = 1 - ($this->tolerancePercentage / 100);
        $adjustedStandardRating = $originalStandardRating * $toleranceFactor;

        // Cache result
        $this->adjustedStandardsCache = $adjustedStandardRating;

        return $adjustedStandardRating;
    }

    /**
     * Build participants paginated list with tolerance calculation
     */
    private function buildParticipantsPaginated()
    {
        $positionFormationId = session('filter.position_formation_id');

        if (! $this->selectedEvent || ! $this->selectedAspect || ! $positionFormationId) {
            return null;
        }

        // Get adjusted standard rating from session or database
        $adjustedStandardRating = $this->getAdjustedStandardRating($this->selectedAspect->id, $positionFormationId);

        // Query with pagination, sorted by rating (lowest first) - FILTER by position
        $query = AspectAssessment::query()
            ->with(['participant.positionFormation'])
            ->where('event_id', $this->selectedEvent->id)
            ->where('position_formation_id', $positionFormationId)
            ->where('aspect_id', $this->selectedAspect->id)
            ->orderBy('individual_rating', 'asc');

        $paginator = $query->paginate($this->perPage, pageName: 'page')->withQueryString();

        // Calculate priority number based on pagination
        $currentPage = (int) $paginator->currentPage();
        $perPage = (int) $paginator->perPage();
        $startPriority = ($currentPage - 1) * $perPage;

        // Transform items
        $items = collect($paginator->items())->values()->map(function ($assessment, int $index) use ($adjustedStandardRating, $startPriority) {
            $individualRating = (float) $assessment->individual_rating;

            // Participant is recommended for training if individual rating < adjusted standard
            $isRecommended = $individualRating < $adjustedStandardRating;

            return [
                'priority' => $startPriority + $index + 1,
                'test_number' => $assessment->participant->test_number,
                'name' => $assessment->participant->name,
                'position' => $assessment->participant->positionFormation->name ?? '-',
                'rating' => $individualRating,
                'is_recommended' => $isRecommended,
                'statement' => $isRecommended ? 'Recommended' : 'Not Recommended',
            ];
        })->all();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            ['path' => $paginator->path(), 'query' => request()->query()]
        );
    }

    /**
     * Get passing summary for ToleranceSelector
     */
    public function getPassingSummary(): array
    {
        return [
            'passing' => $this->notRecommendedCount,
            'total' => $this->totalParticipants,
            'percentage' => $this->totalParticipants > 0
                ? round(($this->notRecommendedCount / $this->totalParticipants) * 100, 2)
                : 0,
        ];
    }

    /**
     * Get recommended percentage
     */
    public function getRecommendedPercentageProperty(): float
    {
        return $this->totalParticipants > 0
            ? round(($this->recommendedCount / $this->totalParticipants) * 100, 2)
            : 0;
    }

    /**
     * Get not recommended percentage
     */
    public function getNotRecommendedPercentageProperty(): float
    {
        return $this->totalParticipants > 0
            ? round(($this->notRecommendedCount / $this->totalParticipants) * 100, 2)
            : 0;
    }

    /**
     * Build aspect priority data with gap analysis
     * OPTIMIZED: Cache result untuk menghindari kalkulasi berulang
     */
    private function buildAspectPriorityData(): ?\Illuminate\Support\Collection
    {
        // Gunakan cache jika sudah ada
        if ($this->aspectPriorityCache !== null) {
            return $this->aspectPriorityCache;
        }

        $positionFormationId = session('filter.position_formation_id');

        if (! $this->selectedEvent || ! $positionFormationId) {
            return null;
        }

        // Get selected position with template
        $position = $this->selectedEvent->positionFormations()
            ->with('template')
            ->find($positionFormationId);

        if (! $position?->template) {
            return collect([]);
        }

        $templateId = $position->template_id;
        $standardService = app(DynamicStandardService::class);

        // Get all aspects for the selected position's template
        $aspects = Aspect::where('template_id', $templateId)
            ->with('categoryType', 'subAspects')
            ->orderBy('category_type_id', 'asc')
            ->orderBy('order', 'asc')
            ->get();

        $aspectData = [];

        foreach ($aspects as $aspect) {
            // Get all assessments for this aspect in this event and position
            $assessments = AspectAssessment::where('event_id', $this->selectedEvent->id)
                ->where('position_formation_id', $positionFormationId)
                ->where('aspect_id', $aspect->id)
                ->get();

            if ($assessments->isEmpty()) {
                continue;
            }

            // Calculate average rating for this aspect
            $totalRating = 0;
            foreach ($assessments as $assessment) {
                $totalRating += (float) $assessment->individual_rating;
            }
            $averageRating = $totalRating / $assessments->count();

            // Get adjusted standard rating from session or database
            // For Potensi category, calculate average from active sub-aspects
            if ($aspect->categoryType->code === 'potensi' && $aspect->subAspects->count() > 0) {
                $subAspectRatingSum = 0;
                $activeSubAspectsCount = 0;

                foreach ($aspect->subAspects as $subAspect) {
                    // Check if sub-aspect is active
                    if (!$standardService->isSubAspectActive($templateId, $subAspect->code)) {
                        continue; // Skip inactive sub-aspects
                    }

                    // Get adjusted sub-aspect rating from session
                    $subRating = $standardService->getSubAspectRating($templateId, $subAspect->code);
                    $subAspectRatingSum += $subRating;
                    $activeSubAspectsCount++;
                }

                $aspectRating = $activeSubAspectsCount > 0 ? $subAspectRatingSum / $activeSubAspectsCount : 0;
            } else {
                // For Kompetensi or aspects without sub-aspects, use aspect rating
                $aspectRating = $standardService->getAspectRating($templateId, $aspect->code);
            }

            // Calculate adjusted standard based on tolerance
            $toleranceFactor = 1 - ($this->tolerancePercentage / 100);
            $adjustedStandardRating = $aspectRating * $toleranceFactor;

            // Calculate gap using adjusted standard
            $gap = $averageRating - $adjustedStandardRating;

            // Determine action (Pelatihan if gap < 0, Dipertahankan if gap >= 0)
            $action = $gap < 0 ? 'Pelatihan' : 'Dipertahankan';

            $aspectData[] = [
                'aspect_name' => $aspect->name,
                'original_standard_rating' => $aspectRating,
                'adjusted_standard_rating' => round($adjustedStandardRating, 2),
                'average_rating' => round($averageRating, 2),
                'gap' => round($gap, 2),
                'action' => $action,
            ];
        }

        // Sort by gap (ascending - most negative first)
        $collection = collect($aspectData)->sortBy('gap')->values();

        // Add priority number
        $result = $collection->map(function ($item, $index) {
            $item['priority'] = $index + 1;

            return $item;
        });

        // Cache result
        $this->aspectPriorityCache = $result;

        return $result;
    }

    public function render()
    {
        $participants = $this->buildParticipantsPaginated();
        $aspectPriorities = $this->buildAspectPriorityData();

        return view('livewire.pages.general-report.training.training-recommendation', [
            'participants' => $participants,
            'aspectPriorities' => $aspectPriorities,
        ]);
    }
}
