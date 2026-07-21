<?php

namespace App\Livewire\Pages\GeneralReport\Training;

use App\Models\Aspect;
use App\Models\AssessmentEvent;
use App\Models\PositionFormation;
use App\Services\TrainingRecommendationService;
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

    // CACHE PROPERTIES - untuk menyimpan hasil service calls
    private ?array $summaryCacheData = null;

    private ?\Illuminate\Support\Collection $participantsCacheData = null;

    private ?\Illuminate\Support\Collection $aspectPriorityCacheData = null;

    /**
     * Listen to filter component events
     */
    protected $listeners = [
        'event-selected' => 'handleEventSelected',
        'position-selected' => 'handlePositionSelected',
        'aspect-selected' => 'handleAspectSelected',
        'tolerance-updated' => 'handleToleranceUpdate',
        'standard-adjusted' => 'handleStandardUpdate',
        'standard-switched' => 'handleStandardUpdate',
    ];

    public function mount(): void
    {
        // Load tolerance from session
        $this->tolerancePercentage = session('training_recommendation.tolerance', 10);

        // Pre-populate default filters if session is empty (e.g. right after login)
        $this->ensureDefaultFilters();

        if ($this->eventCode && $this->aspectId) {
            $this->loadEventAndAspect();
            $this->loadSummaryData();
        }
    }

    /**
     * Ensure default event, position, and aspect are loaded if session filters are empty
     */
    private function ensureDefaultFilters(): void
    {
        // 1. Load eventCode from session or fallback to first event
        $this->eventCode = session('filter.event_code');
        if (! $this->eventCode) {
            $firstEvent = AssessmentEvent::query()->orderByDesc('start_date')->first();
            if ($firstEvent) {
                $this->eventCode = $firstEvent->code;
                session(['filter.event_code' => $this->eventCode]);
            }
        }

        // 2. Load positionFormationId from session or fallback to first position of event
        $positionFormationId = session('filter.position_formation_id');
        if (! $positionFormationId && $this->eventCode) {
            $event = AssessmentEvent::where('code', $this->eventCode)->first();
            if ($event) {
                $firstPosition = PositionFormation::where('event_id', $event->id)->orderBy('name')->first();
                if ($firstPosition) {
                    $positionFormationId = $firstPosition->id;
                    session(['filter.position_formation_id' => $positionFormationId]);
                }
            }
        }

        // 3. Load aspectId from session or fallback to first active aspect
        $this->aspectId = session('filter.aspect_id');
        if (! $this->aspectId && $this->eventCode && $positionFormationId) {
            $position = PositionFormation::with('template')->find($positionFormationId);

            if ($position?->template) {
                $firstAspect = Aspect::query()
                    ->where('template_id', $position->template_id)
                    ->orderBy('order')
                    ->first();

                if ($firstAspect) {
                    $this->aspectId = $firstAspect->id;
                    session(['filter.aspect_id' => $this->aspectId]);
                }
            }
        }
    }

    /**
     * Handle event selection
     */
    public function handleEventSelected(?string $eventCode): void
    {
        $this->resetPage();
        $this->clearCache();

        // Reset data
        $this->reset(['selectedEvent', 'aspectId', 'selectedAspect', 'totalParticipants', 'recommendedCount', 'notRecommendedCount', 'averageRating', 'standardRating', 'originalStandardRating']);
    }

    /**
     * Handle position selection
     */
    public function handlePositionSelected(?int $positionFormationId): void
    {
        $this->resetPage();
        $this->clearCache();

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
        $this->clearCache();
        $this->loadSummaryData();
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
            $this->loadSummaryData();
        }

        // Dispatch event to update summary statistics in ToleranceSelector
        $this->dispatch('summary-updated', [
            'passing' => $this->notRecommendedCount,
            'total' => $this->totalParticipants,
        ]);
    }

    /**
     * Handle standard adjustment from StandardPsikometrik/StandardMc component
     */
    public function handleStandardUpdate(int $templateId): void
    {
        // Validate same template
        $positionFormationId = session('filter.position_formation_id');
        if (! $this->selectedEvent || ! $positionFormationId) {
            return;
        }

        $position = $this->selectedEvent->positionFormations()
            ->find($positionFormationId);

        if (! $position || $position->template_id !== $templateId) {
            return;
        }

        // Clear cache before reload
        $this->clearCache();

        // Reload data (will call service fresh with new session values)
        $this->loadSummaryData();
    }

    public function updatedPerPage()
    {
        $this->perPage = $this->perPage === 'all' ? 999999 : (int) $this->perPage;
        $this->resetPage();
        $this->clearCache();
    }

    /**
     * Load event and aspect from database
     */
    private function loadEventAndAspect(): void
    {
        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if (! $eventCode) {
            return;
        }

        // Load event with position and template in one query
        $this->selectedEvent = AssessmentEvent::query()
            ->where('code', $eventCode)
            ->with([
                'positionFormations' => function ($query) use ($positionFormationId) {
                    $query->where('id', $positionFormationId)
                        ->with('template');
                },
            ])
            ->first();

        if ($this->aspectId && $this->selectedEvent && $positionFormationId) {
            $position = $this->selectedEvent->positionFormations->first();

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
        $this->summaryCacheData = null;
        $this->participantsCacheData = null;
        $this->aspectPriorityCacheData = null;
    }

    /**
     * Load summary data from TrainingRecommendationService
     */
    private function loadSummaryData(): void
    {
        $positionFormationId = session('filter.position_formation_id');

        if (! $this->selectedEvent || ! $this->selectedAspect || ! $positionFormationId) {
            return;
        }

        // Check cache first
        if ($this->summaryCacheData !== null) {
            $this->applySummaryData($this->summaryCacheData);

            return;
        }

        // Call service
        $service = app(TrainingRecommendationService::class);
        $summary = $service->getTrainingSummary(
            $this->selectedEvent->id,
            $positionFormationId,
            $this->selectedAspect->id,
            $this->tolerancePercentage
        );

        // Cache result
        $this->summaryCacheData = $summary;

        // Apply to component properties
        $this->applySummaryData($summary);
    }

    /**
     * Apply summary data to component properties
     */
    private function applySummaryData(array $summary): void
    {
        $this->totalParticipants = $summary['total_participants'];
        $this->recommendedCount = $summary['recommended_count'];
        $this->notRecommendedCount = $summary['not_recommended_count'];
        $this->averageRating = $summary['average_rating'];
        $this->standardRating = $summary['standard_rating'];
        $this->originalStandardRating = $summary['original_standard_rating'];
    }

    /**
     * Get participants recommendation data (paginated)
     */
    private function getParticipantsPaginated()
    {
        $positionFormationId = session('filter.position_formation_id');

        if (! $this->selectedEvent || ! $this->selectedAspect || ! $positionFormationId) {
            return null;
        }

        // Check cache first
        if ($this->participantsCacheData !== null) {
            return $this->paginateCollection($this->participantsCacheData);
        }

        // Call service (returns lightweight data from cache)
        $service = app(TrainingRecommendationService::class);
        $participants = $service->getParticipantsRecommendation(
            $this->selectedEvent->id,
            $positionFormationId,
            $this->selectedAspect->id,
            $this->tolerancePercentage
        );

        // 🚀 OPTIMIZATION: Load position names for ALL participants at once (not lazy)
        // This is acceptable because it's just one query with minimal data
        $positionIds = $participants->pluck('position_formation_id')->unique()->filter()->all();

        if (! empty($positionIds)) {
            $positions = \App\Models\PositionFormation::whereIn('id', $positionIds)
                ->select('id', 'name')
                ->get()
                ->keyBy('id');

            // Attach position names
            $participants = $participants->map(function ($participant) use ($positions) {
                $participant['position'] = $positions->get($participant['position_formation_id'])->name ?? '-';

                return $participant;
            });
        }

        // Cache result
        $this->participantsCacheData = $participants;

        return $this->paginateCollection($participants);
    }

    /**
     * Get aspect priority data from TrainingRecommendationService
     */
    private function getAspectPriorityData(): ?\Illuminate\Support\Collection
    {
        $positionFormationId = session('filter.position_formation_id');

        if (! $this->selectedEvent || ! $positionFormationId) {
            return null;
        }

        // Check cache first
        if ($this->aspectPriorityCacheData !== null) {
            return $this->aspectPriorityCacheData;
        }

        // Get selected position with template
        $position = $this->selectedEvent->positionFormations()
            ->with('template')
            ->find($positionFormationId);

        if (! $position?->template) {
            return collect([]);
        }

        // Call service
        $service = app(TrainingRecommendationService::class);
        $aspectPriorities = $service->getAspectTrainingPriority(
            $this->selectedEvent->id,
            $positionFormationId,
            $position->template_id,
            $this->tolerancePercentage
        );

        // Cache result
        $this->aspectPriorityCacheData = $aspectPriorities;

        return $aspectPriorities;
    }

    /**
     * Paginate a collection
     */
    private function paginateCollection(\Illuminate\Support\Collection $collection)
    {
        $perPage = $this->perPage === 'all' ? $collection->count() : (int) $this->perPage;
        $currentPage = (int) $this->getPage();

        // Slice collection for current page
        $items = $collection->slice(($currentPage - 1) * $perPage, $perPage)->values()->all();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $collection->count(),
            $perPage,
            $currentPage,
            [
                'path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
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
     * Open modal with participants for a specific aspect
     * Load participants and dispatch directly to browser — single round-trip.
     */
    public function openAttributeModal(int $aspectId): void
    {
        $positionFormationId = session('filter.position_formation_id');

        if (! $this->selectedEvent || ! $positionFormationId) {
            return;
        }

        // Find aspect name from priorities data
        $aspectName = '';
        $priorities = $this->getAspectPriorityData();
        if ($priorities) {
            $aspect = $priorities->firstWhere('aspect_id', $aspectId);
            $aspectName = $aspect['aspect_name'] ?? '';
        }

        // Load participants directly here (avoids second Livewire round-trip)
        $service = app(TrainingRecommendationService::class);
        $participants = $service->getParticipantsRecommendation(
            $this->selectedEvent->id,
            $positionFormationId,
            $aspectId,
            $this->tolerancePercentage
        );

        // Filter only recommended participants
        $participants = $participants->filter(fn ($p) => $p['is_recommended'] === true);

        // Hydrate position names in bulk
        $positionIds = $participants->pluck('position_formation_id')->unique()->filter()->all();
        if (! empty($positionIds)) {
            $positions = PositionFormation::whereIn('id', $positionIds)
                ->select('id', 'name')
                ->get()
                ->keyBy('id');

            $participants = $participants->map(function ($p) use ($positions) {
                $p['position'] = $positions->get($p['position_formation_id'])->name ?? '-';
                return $p;
            });
        }

        $participantsArray = $participants->values()->toArray();

        // Single dispatch to browser — modal receives data without a second server request
        $this->dispatch('open-attribute-modal',
            attributeName: $aspectName,
            participants: $participantsArray,
        );
    }

    public function render()
    {
        $participants = $this->getParticipantsPaginated();
        $aspectPriorities = $this->getAspectPriorityData();
        $selectedTemplate = $this->selectedEvent?->positionFormations?->first()?->template;

        return view('livewire.pages.general-report.training.training-recommendation', [
            'participants' => $participants,
            'aspectPriorities' => $aspectPriorities,
            'selectedTemplate' => $selectedTemplate,
        ]);
    }
}
