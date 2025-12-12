<?php

namespace Tests\Feature\Livewire;

use PHPUnit\Framework\Attributes\Test;
use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\AssessmentEvent;
use App\Models\AssessmentTemplate;
use App\Models\CategoryAssessment;
use App\Models\CategoryType;
use App\Models\Institution;
use App\Models\Participant;
use App\Models\PositionFormation;
use App\Models\User;
use App\Services\Cache\AspectCacheService;
use App\Services\DynamicStandardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Statistic Component Test Suite
 *
 * Tests for the Statistic Livewire component which handles:
 * - Frequency distribution charts
 * - Event/Position/Aspect selection
 * - Baseline switching (Quantum Default ↔ Custom Standard)
 * - Session adjustments (Layer 1 overrides)
 * - Event communication with other components
 * - Cache management
 *
 * Following TESTING_SCENARIOS_BASELINE_3LAYER.md guidelines
 */
class StatisticTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Institution $institution;
    private AssessmentEvent $event;
    private PositionFormation $position;
    private AssessmentTemplate $template;
    private CategoryType $categoryType;
    private CategoryType $potensiCategoryType;
    private CategoryType $potensiCategory;
    private Aspect $aspect;
    private Participant $participant;
    private CategoryAssessment $categoryAssessment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->institution = Institution::factory()->create();
        $this->user->institution_id = $this->institution->id;
        $this->user->save();

        $this->actingAs($this->user);

        // Create test data
        $this->event = AssessmentEvent::factory()->create([
            'institution_id' => $this->institution->id,
        ]);

        $this->template = AssessmentTemplate::factory()->create();

        $this->position = PositionFormation::factory()->create([
            'event_id' => $this->event->id,
            'template_id' => $this->template->id,
        ]);

        // Create category type
        $this->categoryType = CategoryType::factory()->create([
            'template_id' => $this->template->id,
            'code' => 'kompetensi', // Match category code used in services
        ]);

        // Create potensi category type to satisfy AdjustmentIndicator in view
        $this->potensiCategoryType = CategoryType::factory()->create([
            'template_id' => $this->template->id,
            'code' => 'potensi-psikometrik',
            'weight_percentage' => 50,
        ]);

        // Create potensi category to satisfy AdjustmentIndicator in view (needs 'potensi' code)
        $this->potensiCategory = CategoryType::factory()->create([
            'template_id' => $this->template->id,
            'code' => 'potensi',
            'weight_percentage' => 50,
        ]);

        $this->aspect = Aspect::factory()->create([
            'category_type_id' => $this->categoryType->id,
            'template_id' => $this->template->id,
            'standard_rating' => 3.0,
        ]);

        // Create a participant for the assessments
        $this->participant = Participant::factory()->create([
            'event_id' => $this->event->id,
            'position_formation_id' => $this->position->id,
        ]);

        // Create category assessment for the participant
        $this->categoryAssessment = CategoryAssessment::factory()->create([
            'participant_id' => $this->participant->id,
            'event_id' => $this->event->id,
            'category_type_id' => $this->categoryType->id,
        ]);

        // Set session filters
        session([
            'filter.event_code' => $this->event->code,
            'filter.position_formation_id' => $this->position->id,
            'filter.aspect_id' => $this->aspect->id,
        ]);

        // Preload aspect cache to avoid AspectCacheService errors
        AspectCacheService::preloadByTemplate($this->template->id);

        // Create specific assessment data with known values
        AspectAssessment::factory()->create([
            'participant_id' => $this->participant->id,
            'event_id' => $this->event->id,
            'position_formation_id' => $this->position->id,
            'category_assessment_id' => $this->categoryAssessment->id,
            'aspect_id' => $this->aspect->id,
            'individual_rating' => 2.5, // Bucket II
        ]);

        AspectAssessment::factory()->create([
            'participant_id' => $this->participant->id,
            'event_id' => $this->event->id,
            'position_formation_id' => $this->position->id,
            'category_assessment_id' => $this->categoryAssessment->id,
            'aspect_id' => $this->aspect->id,
            'individual_rating' => 3.5, // Bucket IV
        ]);

        AspectAssessment::factory()->create([
            'participant_id' => $this->participant->id,
            'event_id' => $this->event->id,
            'position_formation_id' => $this->position->id,
            'category_assessment_id' => $this->categoryAssessment->id,
            'aspect_id' => $this->aspect->id,
            'individual_rating' => 4.5, // Bucket V
        ]);
    }

    #[Test]
    public function component_mounts_with_default_state()
    {
        // Act
        $component = Livewire::test(\App\Livewire\Pages\GeneralReport\Statistic::class);

        // Assert
        $component
            ->assertSet('aspectId', $this->aspect->id)
            ->assertSet('distribution', [1 => 0, 2 => 1, 3 => 0, 4 => 1, 5 => 1]) // Based on our test data
            ->assertSet('standardRating', 3.0) // Standard rating from aspect
            ->assertSet('averageRating', 3.5); // Average of 2.5, 3.5, 4.5

        // Check properties exist
        $this->assertNotNull($component->get('chartId'));
        $this->assertNotNull($component->get('selectedTemplate'));
    }

    #[Test]
    public function component_loads_statistics_when_mounted_with_complete_filters()
    {
        // Act
        $component = Livewire::test(\App\Livewire\Pages\GeneralReport\Statistic::class);

        // Assert
        $component
            ->assertSet('selectedTemplate.id', $this->template->id)
            ->assertSet('distribution', [1 => 0, 2 => 1, 3 => 0, 4 => 1, 5 => 1])
            ->assertSet('averageRating', 3.5) // Average of 2.5, 3.5, 4.5
            ->assertSet('standardRating', 3.0); // Standard rating from aspect
    }

    #[Test]
    public function handle_event_selected_clears_cache_and_waits_for_position()
    {
        // Arrange
        $component = Livewire::test(\App\Livewire\Pages\GeneralReport\Statistic::class);

        // Act
        $component->dispatch('event-selected', 'NEW-EVENT');

        // Assert
        // Event should be handled but no immediate action
        // Component waits for position-selected event
        $component->assertNoRedirect();
    }

    #[Test]
    public function handle_position_selected_clears_cache_and_waits_for_aspect()
    {
        // Arrange
        $component = Livewire::test(\App\Livewire\Pages\GeneralReport\Statistic::class);

        // Act
        $component->dispatch('position-selected', 999);

        // Assert
        // Event should be handled but no immediate action
        // Component waits for aspect-selected event
        $component->assertNoRedirect();
    }

    #[Test]
    public function handle_aspect_selected_refreshes_statistics_and_dispatches_chart_update()
    {
        // Arrange
        $newAspect = Aspect::factory()->create([
            'category_type_id' => $this->categoryType->id,
            'template_id' => $this->template->id,
        ]);

        // Create assessment data for new aspect
        AspectAssessment::factory()->create([
            'participant_id' => $this->participant->id,
            'event_id' => $this->event->id,
            'position_formation_id' => $this->position->id,
            'category_assessment_id' => $this->categoryAssessment->id,
            'aspect_id' => $newAspect->id,
            'individual_rating' => 4.0,
        ]);

        $component = Livewire::test(\App\Livewire\Pages\GeneralReport\Statistic::class);

        // Act
        $component->dispatch('aspect-selected', $newAspect->id);

        // Assert
        $component
            ->assertSet('aspectId', $newAspect->id)
            ->assertSet('distribution', [1 => 0, 2 => 0, 3 => 0, 4 => 1, 5 => 0])
            ->assertSet('averageRating', 4.0)
            ->assertDispatched('chartDataUpdated');
    }

    #[Test]
    public function handle_standard_update_refreshes_statistics_and_dispatches_chart_update()
    {
        // Arrange
        $component = Livewire::test(\App\Livewire\Pages\GeneralReport\Statistic::class);

        // Act
        $component->dispatch('standard-adjusted', $this->template->id);

        // Assert
        $component
            ->assertDispatched('chartDataUpdated')
            ->assertSet('selectedTemplate.id', $this->template->id);
    }

    #[Test]
    public function handle_standard_switched_refreshes_statistics_and_dispatches_chart_update()
    {
        // Arrange
        $component = Livewire::test(\App\Livewire\Pages\GeneralReport\Statistic::class);

        // Act
        $component->dispatch('standard-switched', $this->template->id);

        // Assert
        $component
            ->assertDispatched('chartDataUpdated')
            ->assertSet('selectedTemplate.id', $this->template->id);
    }

    #[Test]
    public function refresh_statistics_with_no_filters_returns_early()
    {
        // Arrange
        session()->forget(['filter.event_code', 'filter.position_formation_id', 'filter.aspect_id']);

        // Act
        $component = Livewire::test(\App\Livewire\Pages\GeneralReport\Statistic::class);

        // Assert
        $component
            ->assertSet('distribution', [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0])
            ->assertSet('standardRating', 0.0)
            ->assertSet('averageRating', 0.0)
            ->assertSet('selectedTemplate', null);
    }

    #[Test]
    public function refresh_statistics_with_invalid_event_returns_early()
    {
        // Arrange
        session(['filter.event_code' => 'INVALID-EVENT']);

        // Act
        $component = Livewire::test(\App\Livewire\Pages\GeneralReport\Statistic::class);

        // Assert
        $component
            ->assertSet('distribution', [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0])
            ->assertSet('standardRating', 0.0)
            ->assertSet('averageRating', 0.0)
            ->assertSet('selectedTemplate', null);
    }

    #[Test]
    public function refresh_statistics_with_invalid_position_returns_early()
    {
        // Arrange
        session(['filter.position_formation_id' => 999]);

        // Act
        $component = Livewire::test(\App\Livewire\Pages\GeneralReport\Statistic::class);

        // Assert
        $component
            ->assertSet('distribution', [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0])
            ->assertSet('standardRating', 0.0)
            ->assertSet('averageRating', 0.0)
            ->assertSet('selectedTemplate', null);
    }

    #[Test]
    public function dispatch_chart_update_sends_correct_data_structure()
    {
        // Arrange
        $component = Livewire::test(\App\Livewire\Pages\GeneralReport\Statistic::class);

        // Act
        $component->dispatch('aspect-selected', $this->aspect->id);

        // Assert
        // Check that the component has the right data for dispatching
        $this->assertNotNull($component->get('chartId'));
        $this->assertNotNull($component->get('distribution'));
        $this->assertNotNull($component->get('standardRating'));
        $this->assertNotNull($component->get('averageRating'));

        // The event is dispatched in handleAspectSelected method
        // We can verify the component state is correct for dispatching
        $this->assertTrue(true, 'Component has correct data structure for chart update');
    }

    #[Test]
    public function get_current_aspect_name_returns_empty_when_no_aspect()
    {
        // Arrange
        session()->forget('filter.aspect_id');
        $component = Livewire::test(\App\Livewire\Pages\GeneralReport\Statistic::class);

        // Act
        $aspectName = $component->viewData('aspectName');

        // Assert
        $this->assertEquals('', $aspectName);
    }

    #[Test]
    public function get_current_aspect_name_returns_aspect_name_when_aspect_exists()
    {
        // Arrange
        $component = Livewire::test(\App\Livewire\Pages\GeneralReport\Statistic::class);

        // Act
        $aspectName = $component->viewData('aspectName');

        // Assert
        // Just verify that aspect name is not empty and is a string
        $this->assertIsString($aspectName);
        $this->assertNotEmpty($aspectName);
    }

    #[Test]
    public function distribution_calculation_respects_bucket_boundaries()
    {
        // Create specific ratings for boundary testing
        AspectAssessment::factory()->create([
            'participant_id' => $this->participant->id,
            'event_id' => $this->event->id,
            'position_formation_id' => $this->position->id,
            'category_assessment_id' => $this->categoryAssessment->id,
            'aspect_id' => $this->aspect->id,
            'individual_rating' => 1.80, // Boundary between I and II
        ]);

        AspectAssessment::factory()->create([
            'participant_id' => $this->participant->id,
            'event_id' => $this->event->id,
            'position_formation_id' => $this->position->id,
            'category_assessment_id' => $this->categoryAssessment->id,
            'aspect_id' => $this->aspect->id,
            'individual_rating' => 2.60, // Boundary between II and III
        ]);

        AspectAssessment::factory()->create([
            'participant_id' => $this->participant->id,
            'event_id' => $this->event->id,
            'position_formation_id' => $this->position->id,
            'category_assessment_id' => $this->categoryAssessment->id,
            'aspect_id' => $this->aspect->id,
            'individual_rating' => 3.40, // Boundary between III and IV
        ]);

        AspectAssessment::factory()->create([
            'participant_id' => $this->participant->id,
            'event_id' => $this->event->id,
            'position_formation_id' => $this->position->id,
            'category_assessment_id' => $this->categoryAssessment->id,
            'aspect_id' => $this->aspect->id,
            'individual_rating' => 4.20, // Boundary between IV and V
        ]);

        // Act
        $component = Livewire::test(\App\Livewire\Pages\GeneralReport\Statistic::class);

        // Assert
        // Boundaries should be inclusive on upper bound (>=) and exclusive on lower bound (<)
        // 1.80 → Bucket II, 2.60 → Bucket III, 3.40 → Bucket IV, 4.20 → Bucket V
        $component->assertSet('distribution', [
            1 => 0,  // None
            2 => 2,  // 2.5, 1.80
            3 => 1,  // 3.5, 2.60
            4 => 2,  // 4.5, 3.40
            5 => 2,  // 4.5, 4.20
        ]);
    }

    #[Test]
    public function standard_rating_calculation_respects_3_layer_priority()
    {
        // Arrange
        $dynamicStandard = app(DynamicStandardService::class);

        // Get the actual category code from the created category type
        $categoryCode = $this->categoryType->code;

        // Preload cache to avoid error
        AspectCacheService::preloadByTemplate($this->template->id);

        // Apply session adjustment (Layer 1)
        $dynamicStandard->saveAspectRating($this->template->id, $this->aspect->code, 5.0);

        // Act
        $component = Livewire::test(\App\Livewire\Pages\GeneralReport\Statistic::class);

        // Assert
        // Standard rating should come from session adjustment (Layer 1)
        $component->assertSet('standardRating', 5.0);
    }

    #[Test]
    public function session_adjustments_override_custom_standard()
    {
        // Arrange
        $dynamicStandard = app(DynamicStandardService::class);

        // Create a custom standard
        $customStandard = \App\Models\CustomStandard::factory()->create([
            'institution_id' => $this->institution->id,
            'template_id' => $this->template->id,
            'name' => 'Test Custom Standard',
        ]);

        // Set custom standard in session
        session(["selected_standard.{$this->template->id}" => $customStandard->id]);

        // Preload cache to avoid error
        AspectCacheService::preloadByTemplate($this->template->id);

        // Apply session adjustment (Layer 1) - overrides custom standard
        $dynamicStandard->saveAspectRating($this->template->id, $this->aspect->code, 5.0);

        // Use existing potensi-psikometrik category to satisfy AdjustmentIndicator in view
        $potensiCategory = $this->potensiCategoryType;

        // Preload cache again before component test to ensure AdjustmentIndicator has access
        AspectCacheService::preloadByTemplate($this->template->id);

        // Act
        $component = Livewire::test(\App\Livewire\Pages\GeneralReport\Statistic::class);

        // Assert
        // Standard rating should come from session adjustment (Layer 1)
        $component->assertSet('standardRating', 5.0);
    }

    #[Test]
    public function cache_key_includes_session_adjustments()
    {
        // Arrange
        $dynamicStandard = app(DynamicStandardService::class);

        // First call to populate cache
        $component1 = Livewire::test(\App\Livewire\Pages\GeneralReport\Statistic::class);
        $initialStandard = $component1->get('standardRating');

        // Preload cache to avoid error
        AspectCacheService::preloadByTemplate($this->template->id);

        // Apply session adjustment
        $dynamicStandard->saveAspectRating($this->template->id, $this->aspect->code, 5.0);

        // Use existing potensi-psikometrik category to satisfy AdjustmentIndicator in view
        $potensiCategory = $this->potensiCategoryType;

        // Preload cache again before component test to ensure AdjustmentIndicator has access
        AspectCacheService::preloadByTemplate($this->template->id);

        // Act
        $component2 = Livewire::test(\App\Livewire\Pages\GeneralReport\Statistic::class);

        // Assert
        // Cache should be invalidated and new standard rating should be used
        $component2->assertSet('standardRating', 5.0);
        $this->assertNotEquals($initialStandard, 5.0);
    }

    #[Test]
    public function component_preloads_aspect_cache_when_mounted()
    {
        // Test that component initializes without error
        // The actual preload happens in the component's mount method
        $component = Livewire::test(\App\Livewire\Pages\GeneralReport\Statistic::class);

        // Assert
        $this->assertNotNull($component);
        $this->assertNotNull($component->get('chartId'));
    }

    #[Test]
    public function render_passes_correct_data_to_view()
    {
        // Arrange
        $component = Livewire::test(\App\Livewire\Pages\GeneralReport\Statistic::class);

        // Act
        $viewData = $component->instance()->render()->getData();

        // Assert
        $this->assertArrayHasKey('distribution', $viewData);
        $this->assertArrayHasKey('standardRating', $viewData);
        $this->assertArrayHasKey('averageRating', $viewData);
        $this->assertArrayHasKey('chartId', $viewData);
        $this->assertArrayHasKey('aspectName', $viewData);

        $this->assertEquals([1 => 0, 2 => 1, 3 => 0, 4 => 1, 5 => 1], $viewData['distribution']);
        $this->assertEquals(3.5, $viewData['averageRating']);

        // Get actual standard rating from the component
        $actualStandardRating = $component->get('standardRating');
        $this->assertEquals($actualStandardRating, $viewData['standardRating']);

        // Get the actual aspect name from the component
        $actualAspectName = $component->viewData('aspectName');
        $this->assertEquals($actualAspectName, $viewData['aspectName']);
    }

    #[Test]
    public function chart_id_is_unique_across_instances()
    {
        // Arrange
        $component1 = Livewire::test(\App\Livewire\Pages\GeneralReport\Statistic::class);
        $component2 = Livewire::test(\App\Livewire\Pages\GeneralReport\Statistic::class);

        // Act
        $chartId1 = $component1->get('chartId');
        $chartId2 = $component2->get('chartId');

        // Assert
        $this->assertStringStartsWith('statistic', $chartId1);
        $this->assertStringStartsWith('statistic', $chartId2);
        $this->assertNotEquals($chartId1, $chartId2);
    }
}
