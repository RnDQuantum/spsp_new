<?php

namespace App\Livewire\Pages\GeneralReport\Training;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\AssessmentEvent;
use App\Models\Participant;
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
    public int $perPage = 10;

    // Summary data
    public int $totalParticipants = 0;

    public int $recommendedCount = 0;

    public int $notRecommendedCount = 0;

    public float $averageRating = 0;

    public float $standardRating = 0;

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

        // Reset data
        $this->reset(['selectedEvent', 'aspectId', 'selectedAspect', 'totalParticipants', 'recommendedCount', 'notRecommendedCount', 'averageRating', 'standardRating']);
    }

    /**
     * Handle position selection
     */
    public function handlePositionSelected(?int $positionFormationId): void
    {
        $this->resetPage();

        // Reset data (aspect will auto-reset)
        $this->reset(['aspectId', 'selectedAspect', 'totalParticipants', 'recommendedCount', 'notRecommendedCount', 'averageRating', 'standardRating']);
    }

    /**
     * Handle aspect selection
     */
    public function handleAspectSelected(?int $aspectId): void
    {
        $this->aspectId = $aspectId;

        if (! $aspectId) {
            $this->reset(['selectedAspect', 'totalParticipants', 'recommendedCount', 'notRecommendedCount', 'averageRating', 'standardRating']);

            return;
        }

        $this->loadEventAndAspect();
        $this->resetPage();
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
     * Calculate summary data (total, recommended count, average rating)
     */
    private function calculateSummaryData(): void
    {
        $positionFormationId = session('filter.position_formation_id');

        if (! $this->selectedEvent || ! $this->selectedAspect || ! $positionFormationId) {
            return;
        }

        // Get standard rating from selected aspect
        $originalStandardRating = (float) $this->selectedAspect->standard_rating;

        // Calculate adjusted standard based on tolerance
        $toleranceFactor = 1 - ($this->tolerancePercentage / 100);
        $adjustedStandardRating = $originalStandardRating * $toleranceFactor;

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
     * Build participants paginated list with tolerance calculation
     */
    private function buildParticipantsPaginated()
    {
        $positionFormationId = session('filter.position_formation_id');

        if (! $this->selectedEvent || ! $this->selectedAspect || ! $positionFormationId) {
            return null;
        }

        // Get standard rating from selected aspect
        $originalStandardRating = (float) $this->selectedAspect->standard_rating;

        // Calculate adjusted standard based on tolerance
        $toleranceFactor = 1 - ($this->tolerancePercentage / 100);
        $adjustedStandardRating = $originalStandardRating * $toleranceFactor;

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
     */
    private function buildAspectPriorityData(): ?\Illuminate\Support\Collection
    {
        $positionFormationId = session('filter.position_formation_id');

        if (! $this->selectedEvent || ! $positionFormationId) {
            return null;
        }

        // Calculate tolerance factor
        $toleranceFactor = 1 - ($this->tolerancePercentage / 100);

        // Get selected position with template
        $position = $this->selectedEvent->positionFormations()
            ->with('template')
            ->find($positionFormationId);

        if (! $position?->template) {
            return collect([]);
        }

        // Get all aspects for the selected position's template
        $aspects = Aspect::where('template_id', $position->template_id)
            ->with('categoryType')
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

            // Get original standard rating
            $originalStandardRating = (float) $aspect->standard_rating;

            // Apply tolerance to standard rating
            $adjustedStandardRating = $originalStandardRating * $toleranceFactor;

            // Calculate gap using adjusted standard
            $gap = $averageRating - $adjustedStandardRating;

            // Determine action (Pelatihan if gap < 0, Dipertahankan if gap >= 0)
            $action = $gap < 0 ? 'Pelatihan' : 'Dipertahankan';

            $aspectData[] = [
                'aspect_name' => $aspect->name,
                'original_standard_rating' => $originalStandardRating,
                'adjusted_standard_rating' => round($adjustedStandardRating, 2),
                'average_rating' => round($averageRating, 2),
                'gap' => round($gap, 2),
                'action' => $action,
            ];
        }

        // Sort by gap (ascending - most negative first)
        $collection = collect($aspectData)->sortBy('gap')->values();

        // Add priority number
        return $collection->map(function ($item, $index) {
            $item['priority'] = $index + 1;

            return $item;
        });
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
