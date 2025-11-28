<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Aspect;
use App\Models\AspectAssessment;
use App\Models\AssessmentEvent;
use App\Models\AssessmentTemplate;
use App\Models\CategoryAssessment;
use App\Models\CategoryType;
use App\Models\Institution;
use App\Models\Participant;
use App\Models\PositionFormation;
use App\Services\IndividualAssessmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * IndividualAssessmentService Unit Tests
 *
 * PHASE 1: ✅ Basic service instantiation (DONE)
 * PHASE 2: 🔄 Data loading with factories (IN PROGRESS)
 * PHASE 3: ⏳ Calculation validation (FUTURE)
 *
 * @see \App\Services\IndividualAssessmentService
 * @see docs/TESTING_STRATEGY.md
 * @see docs/ASSESSMENT_CALCULATION_FLOW.md
 */
class IndividualAssessmentServiceTest extends TestCase
{
    use RefreshDatabase;

    // ========================================
    // PHASE 1: BASIC SERVICE TESTS ✅
    // ========================================

    /**
     * Test that service can be instantiated
     */
    public function test_service_can_be_instantiated(): void
    {
        $service = app(IndividualAssessmentService::class);

        $this->assertInstanceOf(IndividualAssessmentService::class, $service);
    }

    // ========================================
    // PHASE 2: DATA LOADING TESTS 🔄
    // ========================================

    /**
     * Test: Service returns collection with basic structure
     *
     * This test validates:
     * - Service can load aspect assessments from database
     * - Returns Collection type
     * - Collection contains expected keys
     */
    public function test_returns_collection_with_basic_structure(): void
    {
        // Arrange: Create complete assessment data using factories
        $testData = $this->createCompleteAssessmentData();

        // Act: Call service
        $service = app(IndividualAssessmentService::class);
        $result = $service->getAspectAssessments(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id,
            tolerancePercentage: 0
        );

        // Assert: Basic structure validations
        $this->assertNotNull($result);
        $this->assertGreaterThan(0, $result->count());
        $this->assertIsArray($result->first());

        // Verify required keys exist
        $aspectData = $result->first();
        $this->assertArrayHasKey('aspect_id', $aspectData);
        $this->assertArrayHasKey('name', $aspectData);
        $this->assertArrayHasKey('individual_rating', $aspectData);
        $this->assertArrayHasKey('standard_rating', $aspectData);
        $this->assertArrayHasKey('gap_rating', $aspectData);
        $this->assertArrayHasKey('conclusion_text', $aspectData);
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Create complete assessment data structure for testing
     *
     * Returns array with all necessary models for testing
     */
    private function createCompleteAssessmentData(): array
    {
        // 1. Create Institution
        $institution = Institution::create([
            'code' => 'INST_TEST',
            'name' => 'Test Institution',
            'api_key' => 'test_api_key',
        ]);

        // 2. Create AssessmentEvent
        $event = AssessmentEvent::create([
            'institution_id' => $institution->id,
            'code' => 'EVT_TEST',
            'name' => 'Test Event 2025',
            'year' => 2025,
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'status' => 'ongoing',
        ]);

        // 3. Create AssessmentTemplate
        $template = AssessmentTemplate::create([
            'name' => 'Staff Standard v1',
            'code' => 'staff_standard_v1',
            'description' => 'Standard assessment for testing',
        ]);

        // 4. Create CategoryType (Kompetensi)
        $category = CategoryType::create([
            'template_id' => $template->id,
            'code' => 'kompetensi',
            'name' => 'Kompetensi',
            'weight_percentage' => 50,
            'order' => 2,
        ]);

        // 5. Create Aspect (without sub-aspects for simplicity)
        $aspect = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $category->id,
            'code' => 'asp_kom_integritas',
            'name' => 'Integritas',
            'description' => 'Kemampuan bekerja dengan integritas',
            'standard_rating' => 3.0,
            'weight_percentage' => 20,
            'order' => 1,
        ]);

        // 6. Create PositionFormation
        $position = PositionFormation::create([
            'event_id' => $event->id,
            'template_id' => $template->id,
            'name' => 'Staff IT',
            'code' => 'POS_IT',
            'quota' => 10,
        ]);

        // 7. Create Participant using factory
        $participant = Participant::factory()
            ->create([
                'event_id' => $event->id,
                'position_formation_id' => $position->id,
                'assessment_date' => '2025-01-15',
            ]);

        // 8. Create CategoryAssessment using factory
        $categoryAssessment = CategoryAssessment::factory()
            ->forParticipant($participant)
            ->forCategoryType($category)
            ->meetsStandard()
            ->create();

        // 9. Create AspectAssessment using factory
        $aspectAssessment = AspectAssessment::factory()
            ->forCategoryAssessment($categoryAssessment)
            ->forAspect($aspect)
            ->meetsStandard()
            ->create();

        return [
            'institution' => $institution,
            'event' => $event,
            'template' => $template,
            'category' => $category,
            'aspect' => $aspect,
            'position' => $position,
            'participant' => $participant,
            'categoryAssessment' => $categoryAssessment,
            'aspectAssessment' => $aspectAssessment,
        ];
    }
}
