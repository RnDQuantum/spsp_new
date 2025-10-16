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

    public ?string $eventCode = null;

    public ?int $aspectId = null;

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
     * Listen to tolerance updates from ToleranceSelector component
     */
    protected $listeners = ['tolerance-updated' => 'handleToleranceUpdate'];

    public function mount(?string $eventCode = null, ?int $aspectId = null): void
    {
        // Load tolerance from session
        $this->tolerancePercentage = session('training_recommendation.tolerance', 10);

        // Set initial values
        $this->eventCode = $eventCode;
        $this->aspectId = $aspectId;

        // Load data if both event and aspect are provided
        if ($this->eventCode && $this->aspectId) {
            $this->loadEventAndAspect();
            $this->calculateSummaryData();
        }
    }

    /**
     * Update event selection
     */
    public function updatedEventCode(?string $value): void
    {
        if (! $value) {
            $this->reset(['selectedEvent', 'aspectId', 'selectedAspect', 'totalParticipants', 'recommendedCount', 'notRecommendedCount', 'averageRating', 'standardRating']);

            return;
        }

        $this->eventCode = $value;
        $this->loadEventAndAspect();
        $this->resetPage();

        // Reset aspect selection when event changes
        $this->reset(['aspectId', 'selectedAspect', 'totalParticipants', 'recommendedCount', 'notRecommendedCount', 'averageRating', 'standardRating']);
    }

    /**
     * Update aspect selection
     */
    public function updatedAspectId(?int $value): void
    {
        if (! $value || ! $this->eventCode) {
            $this->reset(['selectedAspect', 'totalParticipants', 'recommendedCount', 'notRecommendedCount', 'averageRating', 'standardRating']);

            return;
        }

        $this->aspectId = $value;
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
        if ($this->eventCode) {
            $this->selectedEvent = AssessmentEvent::with('template')
                ->where('code', $this->eventCode)
                ->first();
        }

        if ($this->aspectId && $this->selectedEvent) {
            $this->selectedAspect = Aspect::where('id', $this->aspectId)
                ->where('template_id', $this->selectedEvent->template_id)
                ->first();
        }
    }

    /**
     * Calculate summary data (total, recommended count, average rating)
     */
    private function calculateSummaryData(): void
    {
        if (! $this->selectedEvent || ! $this->selectedAspect) {
            return;
        }

        // Get standard rating from selected aspect
        $originalStandardRating = (float) $this->selectedAspect->standard_rating;

        // Calculate adjusted standard based on tolerance
        $toleranceFactor = 1 - ($this->tolerancePercentage / 100);
        $adjustedStandardRating = $originalStandardRating * $toleranceFactor;

        $this->standardRating = $adjustedStandardRating;

        // Get all aspect assessments for this event and aspect
        $assessments = AspectAssessment::where('event_id', $this->selectedEvent->id)
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
        if (! $this->selectedEvent || ! $this->selectedAspect) {
            return null;
        }

        // Get standard rating from selected aspect
        $originalStandardRating = (float) $this->selectedAspect->standard_rating;

        // Calculate adjusted standard based on tolerance
        $toleranceFactor = 1 - ($this->tolerancePercentage / 100);
        $adjustedStandardRating = $originalStandardRating * $toleranceFactor;

        // Query with pagination, sorted by rating (lowest first)
        $query = AspectAssessment::query()
            ->with(['participant.positionFormation'])
            ->where('event_id', $this->selectedEvent->id)
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
     * Get all available events
     */
    public function getEventsProperty(): \Illuminate\Support\Collection
    {
        return AssessmentEvent::with('template')
            ->orderBy('year', 'desc')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all available aspects for selected event
     */
    public function getAspectsProperty(): \Illuminate\Support\Collection
    {
        if (! $this->selectedEvent) {
            return collect([]);
        }

        return Aspect::where('template_id', $this->selectedEvent->template_id)
            ->with('categoryType')
            ->orderBy('category_type_id', 'asc')
            ->orderBy('order', 'asc')
            ->get();
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

    public function render()
    {
        $participants = $this->buildParticipantsPaginated();

        return view('livewire.pages.general-report.training.training-recommendation', [
            'participants' => $participants,
        ]);
    }
}
