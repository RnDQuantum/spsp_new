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
use App\Models\SubAspect;
use App\Models\SubAspectAssessment;
use App\Services\IndividualAssessmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * IndividualAssessmentService Unit Tests
 *
 * PHASE 1: ✅ Basic service instantiation (DONE - 1/1 tests)
 * PHASE 2: ✅ Data loading with factories (DONE - 1/1 tests)
 * PHASE 3: ✅ Data-driven calculation (DONE - 2/2 tests)
 * PHASE 4: ✅ Tolerance application (DONE - 3/3 tests)
 * PHASE 5: ✅ Column validation (DONE - 3/3 tests)
 * PHASE 6: ✅ Matching percentage (DONE - 4/4 tests)
 * PHASE 7: ✅ getCategoryAssessment() (DONE - 15/15 tests)
 *
 * TOTAL: 29/70 tests (41% progress)
 * TODO: getFinalAssessment(), getPassingSummary(), getAspectMatchingData(), getJobMatchingPercentage()
 *
 * @see \App\Services\IndividualAssessmentService
 * @see docs/TESTING_STRATEGY.md
 * @see docs/ASSESSMENT_CALCULATION_FLOW.md
 */
class IndividualAssessmentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear cache between tests to prevent interference
        \App\Services\Cache\AspectCacheService::clearCache();
    }

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
    // PHASE 3: DATA-DRIVEN RATING CALCULATION 🔄
    // ========================================

    /**
     * Test: Calculates aspect rating from active sub-aspects when present
     *
     * This validates the DATA-DRIVEN approach:
     * - If aspect HAS sub-aspects → rating = average of sub-aspect ratings
     * - System checks $aspect->subAspects->isNotEmpty()
     *
     * Example: Kecerdasan aspect with 3 sub-aspects [3, 4, 5]
     * Expected: Aspect rating = (3 + 4 + 5) / 3 = 4.0
     */
    public function test_calculates_aspect_rating_from_sub_aspects_when_present(): void
    {
        // Arrange: Create aspect WITH sub-aspects (Potensi category)
        $testData = $this->createAssessmentWithSubAspects();

        // Act: Call service
        $service = app(IndividualAssessmentService::class);
        $result = $service->getAspectAssessments(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id,
            tolerancePercentage: 0
        );

        // Assert: Aspect rating should be calculated from sub-aspects
        $aspectData = $result->first();

        // Individual ratings: [3, 4, 5] → Average = 4.0
        $this->assertEquals(4.0, $aspectData['individual_rating']);

        // Standard ratings: [3, 3, 4] → Average = 3.33 (rounded to 3.33)
        $this->assertEquals(3.33, round($aspectData['standard_rating'], 2));

        // Gap: 4.0 - 3.33 = 0.67
        $this->assertEquals(0.67, round($aspectData['gap_rating'], 2));
    }

    /**
     * Test: Uses direct rating when aspect has NO sub-aspects
     *
     * This validates the DATA-DRIVEN approach:
     * - If aspect has NO sub-aspects → use aspect.individual_rating directly
     * - Typical for Kompetensi category
     *
     * Example: Integritas aspect (no sub-aspects), rating = 4
     * Expected: Use 4 directly
     */
    public function test_uses_direct_rating_when_aspect_has_no_sub_aspects(): void
    {
        // Arrange: Create aspect WITHOUT sub-aspects (already done in createCompleteAssessmentData)
        $testData = $this->createCompleteAssessmentData();

        // Update aspect assessment to specific rating
        $testData['aspectAssessment']->update([
            'individual_rating' => 4.5,
            'standard_rating' => 3.0,
        ]);

        // Act: Call service
        $service = app(IndividualAssessmentService::class);
        $result = $service->getAspectAssessments(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id,
            tolerancePercentage: 0
        );

        // Assert: Should use direct ratings (not calculated)
        $aspectData = $result->first();
        $this->assertEquals(4.5, $aspectData['individual_rating']);
        $this->assertEquals(3.0, $aspectData['standard_rating']);
        $this->assertEquals(1.5, round($aspectData['gap_rating'], 2)); // 4.5 - 3.0
    }

    // ========================================
    // PHASE 4: TOLERANCE APPLICATION 🔄
    // ========================================

    /**
     * Test: Applies tolerance percentage to standard rating
     *
     * Tolerance reduces standard rating by percentage:
     * Adjusted Standard = Original Standard × (1 - tolerance%)
     *
     * Example: Standard = 4.0, Tolerance = 10%
     * Expected: 4.0 × (1 - 0.10) = 4.0 × 0.9 = 3.6
     */
    public function test_applies_tolerance_to_standard_rating(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentData();

        // Update Aspect (template) standard_rating - this is what service reads for aspects without sub-aspects
        $testData['aspect']->standard_rating = 4.0;
        $testData['aspect']->save();

        // Update AspectAssessment individual_rating
        $testData['aspectAssessment']->individual_rating = 4.5;
        $testData['aspectAssessment']->save();

        // Act: Apply 10% tolerance
        $service = app(IndividualAssessmentService::class);
        $result = $service->getAspectAssessments(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id,
            tolerancePercentage: 10
        );

        // Assert
        $aspectData = $result->first();

        // Original standard should be preserved
        $this->assertEquals(4.0, $aspectData['original_standard_rating']);

        // Adjusted standard = 4.0 × 0.9 = 3.6
        $this->assertEquals(3.6, $aspectData['standard_rating']);

        // Individual rating unchanged
        $this->assertEquals(4.5, $aspectData['individual_rating']);
    }

    /**
     * Test: Calculates gap correctly with tolerance applied
     *
     * Gap = Individual - Adjusted Standard
     *
     * Example:
     * - Individual: 4.5
     * - Standard: 4.0, Tolerance 10% → Adjusted: 3.6
     * - Gap: 4.5 - 3.6 = 0.9 (larger gap due to tolerance)
     */
    public function test_calculates_gap_with_tolerance_correctly(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentData();

        // Update Aspect (template) standard_rating - this is what service reads
        $testData['aspect']->standard_rating = 4.0;
        $testData['aspect']->save();

        // Update AspectAssessment individual_rating
        $testData['aspectAssessment']->individual_rating = 4.5;
        $testData['aspectAssessment']->save();

        // Act: Apply 10% tolerance
        $service = app(IndividualAssessmentService::class);
        $result = $service->getAspectAssessments(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id,
            tolerancePercentage: 10
        );

        // Assert
        $aspectData = $result->first();

        // Original gap (without tolerance): 4.5 - 4.0 = 0.5
        $this->assertEquals(0.5, round($aspectData['original_gap_rating'], 2));

        // Adjusted gap (with 10% tolerance): 4.5 - 3.6 = 0.9
        $this->assertEquals(0.9, round($aspectData['gap_rating'], 2));

        // Gap increases when tolerance applied (easier to pass standard)
        $this->assertGreaterThan(
            $aspectData['original_gap_rating'],
            $aspectData['gap_rating']
        );
    }

    /**
     * Test: Different tolerance percentages produce different results
     *
     * Higher tolerance = Lower adjusted standard = Larger gap
     */
    public function test_different_tolerance_percentages_produce_different_gaps(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentData();

        // Update Aspect (template) standard_rating - this is what service reads
        $testData['aspect']->standard_rating = 4.0;
        $testData['aspect']->save();

        // Update AspectAssessment individual_rating
        $testData['aspectAssessment']->individual_rating = 4.0;
        $testData['aspectAssessment']->save();

        $service = app(IndividualAssessmentService::class);

        // Act: Test with 0%, 10%, 20% tolerance
        $result0 = $service->getAspectAssessments(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id,
            tolerancePercentage: 0
        );

        $result10 = $service->getAspectAssessments(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id,
            tolerancePercentage: 10
        );

        $result20 = $service->getAspectAssessments(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id,
            tolerancePercentage: 20
        );

        // Assert: Standard rating decreases as tolerance increases
        // 0%: 4.0 × 1.0 = 4.0
        $this->assertEquals(4.0, $result0->first()['standard_rating']);

        // 10%: 4.0 × 0.9 = 3.6
        $this->assertEquals(3.6, $result10->first()['standard_rating']);

        // 20%: 4.0 × 0.8 = 3.2
        $this->assertEquals(3.2, $result20->first()['standard_rating']);

        // Assert: Gap increases as tolerance increases
        // Individual = 4.0 (constant)
        // 0%: 4.0 - 4.0 = 0.0
        $this->assertEquals(0.0, round($result0->first()['gap_rating'], 2));

        // 10%: 4.0 - 3.6 = 0.4
        $this->assertEquals(0.4, round($result10->first()['gap_rating'], 2));

        // 20%: 4.0 - 3.2 = 0.8
        $this->assertEquals(0.8, round($result20->first()['gap_rating'], 2));
    }

    // ========================================
    // PHASE 5: COLUMN VALIDATION 🔄
    // ========================================

    /**
     * Test: Validates all required columns exist in response
     *
     * Service must return all expected columns for each aspect assessment
     */
    public function test_validates_all_required_columns_exist(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentData();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getAspectAssessments(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id,
            tolerancePercentage: 0
        );

        // Assert: Check all required columns exist
        $aspectData = $result->first();

        // Identification columns
        $this->assertArrayHasKey('aspect_id', $aspectData);
        $this->assertArrayHasKey('aspect_code', $aspectData);
        $this->assertArrayHasKey('name', $aspectData);
        $this->assertArrayHasKey('description', $aspectData);

        // Weight columns
        $this->assertArrayHasKey('weight_percentage', $aspectData);
        $this->assertArrayHasKey('original_weight', $aspectData);
        $this->assertArrayHasKey('is_weight_adjusted', $aspectData);

        // Rating columns (with tolerance)
        $this->assertArrayHasKey('original_standard_rating', $aspectData);
        $this->assertArrayHasKey('standard_rating', $aspectData);
        $this->assertArrayHasKey('individual_rating', $aspectData);

        // Score columns (with tolerance)
        $this->assertArrayHasKey('original_standard_score', $aspectData);
        $this->assertArrayHasKey('standard_score', $aspectData);
        $this->assertArrayHasKey('individual_score', $aspectData);

        // Gap columns (with tolerance)
        $this->assertArrayHasKey('original_gap_rating', $aspectData);
        $this->assertArrayHasKey('gap_rating', $aspectData);
        $this->assertArrayHasKey('original_gap_score', $aspectData);
        $this->assertArrayHasKey('gap_score', $aspectData);

        // Percentage and conclusion
        $this->assertArrayHasKey('percentage_score', $aspectData);
        $this->assertArrayHasKey('conclusion_text', $aspectData);
    }

    /**
     * Test: Validates column data types are correct
     *
     * Each column must have the correct data type
     */
    public function test_validates_column_data_types(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentData();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getAspectAssessments(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id,
            tolerancePercentage: 10
        );

        $aspectData = $result->first();

        // Assert: Check data types
        // IDs and codes should be integers or strings
        $this->assertIsInt($aspectData['aspect_id']);
        $this->assertIsString($aspectData['aspect_code']);
        $this->assertIsString($aspectData['name']);

        // Weight should be numeric
        $this->assertIsNumeric($aspectData['weight_percentage']);
        $this->assertIsNumeric($aspectData['original_weight']);
        $this->assertIsBool($aspectData['is_weight_adjusted']);

        // Ratings should be numeric (float)
        $this->assertIsNumeric($aspectData['original_standard_rating']);
        $this->assertIsNumeric($aspectData['standard_rating']);
        $this->assertIsNumeric($aspectData['individual_rating']);

        // Scores should be numeric (float)
        $this->assertIsNumeric($aspectData['original_standard_score']);
        $this->assertIsNumeric($aspectData['standard_score']);
        $this->assertIsNumeric($aspectData['individual_score']);

        // Gaps should be numeric (float)
        $this->assertIsNumeric($aspectData['original_gap_rating']);
        $this->assertIsNumeric($aspectData['gap_rating']);
        $this->assertIsNumeric($aspectData['original_gap_score']);
        $this->assertIsNumeric($aspectData['gap_score']);

        // Percentage and conclusion
        $this->assertIsNumeric($aspectData['percentage_score']);
        $this->assertIsString($aspectData['conclusion_text']);
    }

    /**
     * Test: Validates calculated values are mathematically correct
     *
     * Verifies:
     * - score = rating × weight
     * - gap = individual - standard
     * - percentage = (individual_score / standard_score) × 100
     */
    public function test_validates_calculated_values_are_correct(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentData();

        // Set predictable values
        $testData['aspect']->standard_rating = 3.0;
        $testData['aspect']->weight_percentage = 20.0;
        $testData['aspect']->save();

        $testData['aspectAssessment']->individual_rating = 4.0;
        $testData['aspectAssessment']->save();

        // Act: No tolerance for easier validation
        $service = app(IndividualAssessmentService::class);
        $result = $service->getAspectAssessments(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id,
            tolerancePercentage: 0
        );

        $aspectData = $result->first();

        // Assert: Verify calculations
        // Score = Rating × Weight
        $expectedStandardScore = 3.0 * 20.0; // 60.0
        $expectedIndividualScore = 4.0 * 20.0; // 80.0

        $this->assertEquals($expectedStandardScore, $aspectData['standard_score']);
        $this->assertEquals($expectedIndividualScore, $aspectData['individual_score']);

        // Gap = Individual - Standard
        $expectedGapRating = 4.0 - 3.0; // 1.0
        $expectedGapScore = 80.0 - 60.0; // 20.0

        $this->assertEquals($expectedGapRating, $aspectData['gap_rating']);
        $this->assertEquals($expectedGapScore, $aspectData['gap_score']);

        // Percentage = (Individual / Standard) × 100
        $expectedPercentage = (80.0 / 60.0) * 100; // 133.33

        $this->assertEquals(round($expectedPercentage, 2), $aspectData['percentage_score']);
    }

    // ========================================
    // PHASE 6: MATCHING PERCENTAGE LOGIC 🔄
    // ========================================

    /**
     * Test: Percentage above 100% when individual exceeds standard
     *
     * When individual_score > standard_score, percentage > 100%
     * Formula: (individual_score / standard_score) × 100
     */
    public function test_percentage_above_100_when_exceeds_standard(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentData();

        // Set values where individual exceeds standard
        $testData['aspect']->standard_rating = 3.0;
        $testData['aspect']->weight_percentage = 20.0;
        $testData['aspect']->save();

        $testData['aspectAssessment']->individual_rating = 4.5; // Higher than standard
        $testData['aspectAssessment']->save();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getAspectAssessments(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id,
            tolerancePercentage: 0
        );

        $aspectData = $result->first();

        // Assert
        // Standard: 3.0 × 20 = 60
        // Individual: 4.5 × 20 = 90
        // Percentage: (90 / 60) × 100 = 150%
        $this->assertEquals(150.0, $aspectData['percentage_score']);
        $this->assertGreaterThan(100, $aspectData['percentage_score']);
    }

    /**
     * Test: Percentage equals 100% when individual matches standard
     *
     * When individual_score = standard_score, percentage = 100%
     */
    public function test_percentage_equals_100_when_meets_standard(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentData();

        // Set equal values
        $testData['aspect']->standard_rating = 3.5;
        $testData['aspect']->weight_percentage = 20.0;
        $testData['aspect']->save();

        $testData['aspectAssessment']->individual_rating = 3.5; // Same as standard
        $testData['aspectAssessment']->save();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getAspectAssessments(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id,
            tolerancePercentage: 0
        );

        $aspectData = $result->first();

        // Assert
        // Standard: 3.5 × 20 = 70
        // Individual: 3.5 × 20 = 70
        // Percentage: (70 / 70) × 100 = 100%
        $this->assertEquals(100.0, $aspectData['percentage_score']);
    }

    /**
     * Test: Percentage below 100% when individual below standard
     *
     * When individual_score < standard_score, percentage < 100%
     */
    public function test_percentage_below_100_when_below_standard(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentData();

        // Set values where individual is below standard
        $testData['aspect']->standard_rating = 4.0;
        $testData['aspect']->weight_percentage = 20.0;
        $testData['aspect']->save();

        $testData['aspectAssessment']->individual_rating = 3.0; // Lower than standard
        $testData['aspectAssessment']->save();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getAspectAssessments(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id,
            tolerancePercentage: 0
        );

        $aspectData = $result->first();

        // Assert
        // Standard: 4.0 × 20 = 80
        // Individual: 3.0 × 20 = 60
        // Percentage: (60 / 80) × 100 = 75%
        $this->assertEquals(75.0, $aspectData['percentage_score']);
        $this->assertLessThan(100, $aspectData['percentage_score']);
    }

    /**
     * Test: Percentage calculation with tolerance applied
     *
     * Percentage should be calculated based on ADJUSTED standard (with tolerance)
     * Not on original standard
     *
     * With tolerance: adjusted_standard is lower → percentage is higher
     */
    public function test_percentage_calculated_with_tolerance(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentData();

        // Set specific values
        $testData['aspect']->standard_rating = 4.0;
        $testData['aspect']->weight_percentage = 20.0;
        $testData['aspect']->save();

        $testData['aspectAssessment']->individual_rating = 3.6;
        $testData['aspectAssessment']->save();

        $service = app(IndividualAssessmentService::class);

        // Act: Compare 0% vs 10% tolerance
        $resultNoTolerance = $service->getAspectAssessments(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id,
            tolerancePercentage: 0
        );

        $resultWithTolerance = $service->getAspectAssessments(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id,
            tolerancePercentage: 10
        );

        // Assert
        // No tolerance:
        // Standard: 4.0 × 20 = 80
        // Individual: 3.6 × 20 = 72
        // Percentage: (72 / 80) × 100 = 90%
        $this->assertEquals(90.0, $resultNoTolerance->first()['percentage_score']);

        // With 10% tolerance:
        // Adjusted Standard: 3.6 × 20 = 72 (4.0 × 0.9 = 3.6)
        // Individual: 3.6 × 20 = 72
        // Percentage: (72 / 72) × 100 = 100%
        $this->assertEquals(100.0, $resultWithTolerance->first()['percentage_score']);

        // Percentage should be higher with tolerance
        $this->assertGreaterThan(
            $resultNoTolerance->first()['percentage_score'],
            $resultWithTolerance->first()['percentage_score']
        );
    }

    // ========================================
    // PHASE 7: getCategoryAssessment() TESTS (15 tests)
    // ========================================

    /**
     * Test: getCategoryAssessment aggregates aspect scores correctly
     *
     * Category total = sum of all active aspect scores
     */
    public function test_aggregates_aspect_scores_correctly(): void
    {
        // Arrange: Create category with 3 aspects
        $testData = $this->createCategoryWithMultipleAspects();

        // Act: Get category assessment
        $service = app(IndividualAssessmentService::class);
        $result = $service->getCategoryAssessment(
            participantId: $testData['participant']->id,
            categoryCode: 'kompetensi',
            tolerancePercentage: 0
        );

        // Assert: Total scores should be sum of all aspects
        // Aspect 1: standard_score = 3.0 * 30 = 90
        // Aspect 2: standard_score = 4.0 * 30 = 120
        // Aspect 3: standard_score = 3.5 * 40 = 140
        // Total: 90 + 120 + 140 = 350
        $this->assertEquals(350.0, $result['total_standard_score']);

        // Individual totals
        // Aspect 1: 4.0 * 30 = 120
        // Aspect 2: 4.5 * 30 = 135
        // Aspect 3: 4.0 * 40 = 160
        // Total: 120 + 135 + 160 = 415
        $this->assertEquals(415.0, $result['total_individual_score']);
    }

    /**
     * Test: getCategoryAssessment applies category weight correctly
     *
     * Weighted score = total_score × (category_weight / 100)
     */
    public function test_applies_category_weight_to_totals(): void
    {
        // Arrange
        $testData = $this->createCategoryWithMultipleAspects();

        // Set category weight to 60% (default is 50%)
        $standardService = app(\App\Services\DynamicStandardService::class);
        $standardService->saveCategoryWeight($testData['template']->id, 'kompetensi', 60);

        // Act: Get category assessment
        $service = app(IndividualAssessmentService::class);
        $result = $service->getCategoryAssessment(
            participantId: $testData['participant']->id,
            categoryCode: 'kompetensi',
            tolerancePercentage: 0
        );

        // Assert: Category weight should be applied
        $this->assertEquals(60, $result['category_weight']);

        // Weighted scores = total × (60 / 100)
        // total_standard_score = 350, weighted = 350 * 0.6 = 210
        $this->assertEquals(210.0, $result['weighted_standard_score']);

        // total_individual_score = 415, weighted = 415 * 0.6 = 249
        $this->assertEquals(249.0, $result['weighted_individual_score']);
    }

    /**
     * Test: getCategoryAssessment excludes inactive aspects from totals
     *
     * CRITICAL: Inactive aspects should NOT be counted in totals
     */
    public function test_excludes_inactive_aspects_from_category_totals(): void
    {
        // Arrange
        $testData = $this->createCategoryWithMultipleAspects();

        // Set aspect 2 as inactive
        $standardService = app(\App\Services\DynamicStandardService::class);
        $standardService->setAspectActive(
            $testData['template']->id,
            $testData['aspects'][1]->code,
            false
        );

        // Act: Get category assessment
        $service = app(IndividualAssessmentService::class);
        $result = $service->getCategoryAssessment(
            participantId: $testData['participant']->id,
            categoryCode: 'kompetensi',
            tolerancePercentage: 0
        );

        // Assert: Should only count aspect 1 and 3 (aspect 2 is inactive)
        // Aspect 1: 90, Aspect 3: 140, Total: 230 (NOT 350)
        $this->assertEquals(230.0, $result['total_standard_score']);

        // Individual: 120 + 160 = 280 (NOT 415)
        $this->assertEquals(280.0, $result['total_individual_score']);

        // Aspect count should be 2 (not 3)
        $this->assertEquals(2, $result['aspect_count']);
    }

    /**
     * Test: getCategoryAssessment calculates gaps correctly
     *
     * Gap = individual - standard
     */
    public function test_calculates_category_gaps_correctly(): void
    {
        // Arrange
        $testData = $this->createCategoryWithMultipleAspects();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getCategoryAssessment(
            participantId: $testData['participant']->id,
            categoryCode: 'kompetensi',
            tolerancePercentage: 0
        );

        // Assert: Gap = Individual - Standard
        // Individual: 415, Standard: 350, Gap: 65
        $this->assertEquals(65.0, $result['total_gap_score']);

        // Rating gaps
        // Individual rating: 4.0 + 4.5 + 4.0 = 12.5
        // Standard rating: 3.0 + 4.0 + 3.5 = 10.5
        // Gap: 2.0
        $this->assertEquals(2.0, $result['total_gap_rating']);
    }

    /**
     * Test: getCategoryAssessment applies tolerance to category totals
     *
     * With tolerance, standard scores should be reduced
     */
    public function test_applies_tolerance_to_category_totals(): void
    {
        // Arrange
        $testData = $this->createCategoryWithMultipleAspects();

        $service = app(IndividualAssessmentService::class);

        // Act: Compare 0% vs 10% tolerance
        $resultNoTolerance = $service->getCategoryAssessment(
            participantId: $testData['participant']->id,
            categoryCode: 'kompetensi',
            tolerancePercentage: 0
        );

        $resultWithTolerance = $service->getCategoryAssessment(
            participantId: $testData['participant']->id,
            categoryCode: 'kompetensi',
            tolerancePercentage: 10
        );

        // Assert: Standard score should be reduced with tolerance
        // Original: 350
        // With 10% tolerance: 350 * 0.9 = 315
        $this->assertEquals(350.0, $resultNoTolerance['total_standard_score']);
        $this->assertEquals(315.0, $resultWithTolerance['total_standard_score']);

        // Individual score should remain the same
        $this->assertEquals(
            $resultNoTolerance['total_individual_score'],
            $resultWithTolerance['total_individual_score']
        );

        // Gap should be larger with tolerance
        $this->assertGreaterThan(
            $resultNoTolerance['total_gap_score'],
            $resultWithTolerance['total_gap_score']
        );
    }

    /**
     * Test: getCategoryAssessment returns correct conclusion
     *
     * Conclusion should be based on gap-based logic (from ConclusionService)
     */
    public function test_returns_correct_overall_conclusion(): void
    {
        // Arrange
        $testData = $this->createCategoryWithMultipleAspects();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getCategoryAssessment(
            participantId: $testData['participant']->id,
            categoryCode: 'kompetensi',
            tolerancePercentage: 0
        );

        // Assert: Should have conclusion
        $this->assertArrayHasKey('overall_conclusion', $result);
        $this->assertIsString($result['overall_conclusion']);

        // Gap is positive (415 > 350), so should be "Di Atas Standar" or "Memenuhi Standar"
        $this->assertContains($result['overall_conclusion'], [
            'Di Atas Standar',
            'Memenuhi Standar',
            'Mendekati Standar',
        ]);
    }

    /**
     * Test: getCategoryAssessment includes all required keys
     */
    public function test_category_assessment_has_all_required_keys(): void
    {
        // Arrange
        $testData = $this->createCategoryWithMultipleAspects();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getCategoryAssessment(
            participantId: $testData['participant']->id,
            categoryCode: 'kompetensi',
            tolerancePercentage: 0
        );

        // Assert: Check all required keys
        $requiredKeys = [
            'category_code',
            'category_name',
            'category_weight',
            'aspect_count',
            'total_standard_rating',
            'total_standard_score',
            'total_individual_rating',
            'total_individual_score',
            'total_gap_rating',
            'total_gap_score',
            'total_original_standard_score',
            'total_original_gap_score',
            'overall_conclusion',
            'weighted_standard_score',
            'weighted_individual_score',
            'weighted_gap_score',
            'aspects',
        ];

        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $result, "Missing key: {$key}");
        }
    }

    /**
     * Test: getCategoryAssessment validates data types
     */
    public function test_category_assessment_data_types(): void
    {
        // Arrange
        $testData = $this->createCategoryWithMultipleAspects();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getCategoryAssessment(
            participantId: $testData['participant']->id,
            categoryCode: 'kompetensi',
            tolerancePercentage: 0
        );

        // Assert: Data types
        $this->assertIsString($result['category_code']);
        $this->assertIsString($result['category_name']);
        $this->assertIsNumeric($result['category_weight']);
        $this->assertIsInt($result['aspect_count']);
        $this->assertIsNumeric($result['total_standard_rating']);
        $this->assertIsNumeric($result['total_standard_score']);
        $this->assertIsNumeric($result['total_individual_score']);
        $this->assertIsString($result['overall_conclusion']);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result['aspects']);
    }

    /**
     * Test: getCategoryAssessment with single aspect
     *
     * Edge case: Category with only 1 aspect
     */
    public function test_category_assessment_with_single_aspect(): void
    {
        // Arrange: Use existing test data (has 1 aspect)
        $testData = $this->createCompleteAssessmentData();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getCategoryAssessment(
            participantId: $testData['participant']->id,
            categoryCode: 'kompetensi',
            tolerancePercentage: 0
        );

        // Assert
        $this->assertEquals(1, $result['aspect_count']);
        $this->assertCount(1, $result['aspects']);
    }

    /**
     * Test: getCategoryAssessment with Potensi category (has sub-aspects)
     *
     * CRITICAL: Test data-driven calculation for Potensi
     */
    public function test_category_assessment_with_potensi_sub_aspects(): void
    {
        // Arrange: Create Potensi category with sub-aspects
        $testData = $this->createAssessmentWithSubAspects();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getCategoryAssessment(
            participantId: $testData['participant']->id,
            categoryCode: 'potensi',
            tolerancePercentage: 0
        );

        // Assert: Should calculate from sub-aspects
        $this->assertGreaterThan(0, $result['total_standard_score']);
        $this->assertGreaterThan(0, $result['total_individual_score']);

        // Category should be 'potensi'
        $this->assertEquals('potensi', $result['category_code']);
        $this->assertEquals('Potensi', $result['category_name']);
    }

    /**
     * Test: getCategoryAssessment rounds decimals correctly
     *
     * All monetary/score values should be rounded to 2 decimals
     */
    public function test_category_assessment_rounds_correctly(): void
    {
        // Arrange
        $testData = $this->createCategoryWithMultipleAspects();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getCategoryAssessment(
            participantId: $testData['participant']->id,
            categoryCode: 'kompetensi',
            tolerancePercentage: 10
        );

        // Assert: All numeric values should be rounded to 2 decimal places
        // Check that when rounded to 2 decimals, value doesn't change
        $this->assertEquals(
            round($result['total_standard_rating'], 2),
            $result['total_standard_rating'],
            'total_standard_rating should be rounded to 2 decimals'
        );

        $this->assertEquals(
            round($result['total_standard_score'], 2),
            $result['total_standard_score'],
            'total_standard_score should be rounded to 2 decimals'
        );
    }

    /**
     * Test: getCategoryAssessment calculates weighted gap correctly
     *
     * weighted_gap = weighted_individual - weighted_standard
     */
    public function test_calculates_weighted_gap_correctly(): void
    {
        // Arrange
        $testData = $this->createCategoryWithMultipleAspects();

        // Set category weight to 60%
        $standardService = app(\App\Services\DynamicStandardService::class);
        $standardService->saveCategoryWeight($testData['template']->id, 'kompetensi', 60);

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getCategoryAssessment(
            participantId: $testData['participant']->id,
            categoryCode: 'kompetensi',
            tolerancePercentage: 0
        );

        // Assert: weighted_gap = weighted_individual - weighted_standard
        // weighted_standard = 210, weighted_individual = 249
        // weighted_gap = 249 - 210 = 39
        $expectedGap = $result['weighted_individual_score'] - $result['weighted_standard_score'];
        $this->assertEquals($expectedGap, $result['weighted_gap_score']);
        $this->assertEquals(39.0, $result['weighted_gap_score']);
    }

    /**
     * Test: getCategoryAssessment throws exception for non-existent category
     *
     * Should fail gracefully when category doesn't exist
     */
    public function test_throws_exception_for_nonexistent_category(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentData();

        // Act & Assert: Should throw ModelNotFoundException
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $service = app(IndividualAssessmentService::class);
        $service->getCategoryAssessment(
            participantId: $testData['participant']->id,
            categoryCode: 'nonexistent_category', // Invalid category
            tolerancePercentage: 0
        );
    }

    /**
     * Test: getCategoryAssessment with different tolerance values
     *
     * Higher tolerance = larger gap
     */
    public function test_category_assessment_with_different_tolerances(): void
    {
        // Arrange
        $testData = $this->createCategoryWithMultipleAspects();
        $service = app(IndividualAssessmentService::class);

        // Act: Test with 0%, 10%, 20% tolerance
        $result0 = $service->getCategoryAssessment(
            participantId: $testData['participant']->id,
            categoryCode: 'kompetensi',
            tolerancePercentage: 0
        );

        $result10 = $service->getCategoryAssessment(
            participantId: $testData['participant']->id,
            categoryCode: 'kompetensi',
            tolerancePercentage: 10
        );

        $result20 = $service->getCategoryAssessment(
            participantId: $testData['participant']->id,
            categoryCode: 'kompetensi',
            tolerancePercentage: 20
        );

        // Assert: Standard score decreases as tolerance increases
        // 0%: 350
        $this->assertEquals(350.0, $result0['total_standard_score']);

        // 10%: 350 * 0.9 = 315
        $this->assertEquals(315.0, $result10['total_standard_score']);

        // 20%: 350 * 0.8 = 280
        $this->assertEquals(280.0, $result20['total_standard_score']);

        // Gap increases as tolerance increases
        // Gap = Individual - Standard
        // 0%: 415 - 350 = 65
        // 10%: 415 - 315 = 100
        // 20%: 415 - 280 = 135
        $this->assertEquals(65.0, $result0['total_gap_score']);
        $this->assertEquals(100.0, $result10['total_gap_score']);
        $this->assertEquals(135.0, $result20['total_gap_score']);

        // Verify gap progression
        $this->assertGreaterThan($result0['total_gap_score'], $result10['total_gap_score'], 'Gap@10% should be larger than Gap@0%');
        $this->assertGreaterThan($result10['total_gap_score'], $result20['total_gap_score'], 'Gap@20% should be larger than Gap@10%');
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Create category with multiple aspects for testing aggregation
     *
     * Creates 3 Kompetensi aspects with different weights
     */
    private function createCategoryWithMultipleAspects(): array
    {
        // 1-3: Basic setup
        $institution = Institution::create([
            'code' => 'INST_MULTI',
            'name' => 'Test Institution',
            'api_key' => 'test_api_key',
        ]);

        $event = AssessmentEvent::create([
            'institution_id' => $institution->id,
            'code' => 'EVT_MULTI',
            'name' => 'Test Event 2025',
            'year' => 2025,
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'status' => 'ongoing',
        ]);

        $template = AssessmentTemplate::create([
            'name' => 'Staff Standard v1',
            'code' => 'staff_standard_multi',
            'description' => 'Standard assessment for testing',
        ]);

        // 4: Create Kompetensi category
        $category = CategoryType::create([
            'template_id' => $template->id,
            'code' => 'kompetensi',
            'name' => 'Kompetensi',
            'weight_percentage' => 50,
            'order' => 2,
        ]);

        // 5: Create 3 aspects with different weights (totaling 100%)
        $aspect1 = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $category->id,
            'code' => 'asp_kom_01',
            'name' => 'Integritas',
            'description' => 'Kemampuan bekerja dengan integritas',
            'standard_rating' => 3.0,
            'weight_percentage' => 30, // 30%
            'order' => 1,
        ]);

        $aspect2 = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $category->id,
            'code' => 'asp_kom_02',
            'name' => 'Kerjasama',
            'description' => 'Kemampuan bekerjasama',
            'standard_rating' => 4.0,
            'weight_percentage' => 30, // 30%
            'order' => 2,
        ]);

        $aspect3 = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $category->id,
            'code' => 'asp_kom_03',
            'name' => 'Orientasi Pelayanan',
            'description' => 'Fokus pada pelayanan',
            'standard_rating' => 3.5,
            'weight_percentage' => 40, // 40%
            'order' => 3,
        ]);

        // 6-7: Position & Participant
        $position = PositionFormation::create([
            'event_id' => $event->id,
            'template_id' => $template->id,
            'name' => 'Staff IT',
            'code' => 'POS_IT_MULTI',
            'quota' => 10,
        ]);

        $participant = Participant::factory()->create([
            'event_id' => $event->id,
            'position_formation_id' => $position->id,
            'assessment_date' => '2025-01-15',
        ]);

        // 8: CategoryAssessment
        $categoryAssessment = CategoryAssessment::factory()
            ->forParticipant($participant)
            ->forCategoryType($category)
            ->create();

        // 9: Create AspectAssessments for all 3 aspects
        AspectAssessment::factory()
            ->forCategoryAssessment($categoryAssessment)
            ->forAspect($aspect1)
            ->create([
                'standard_rating' => 3.0,
                'individual_rating' => 4.0,
                'standard_score' => 90.0,   // 3.0 * 30
                'individual_score' => 120.0, // 4.0 * 30
                'gap_rating' => 1.0,
                'gap_score' => 30.0,
            ]);

        AspectAssessment::factory()
            ->forCategoryAssessment($categoryAssessment)
            ->forAspect($aspect2)
            ->create([
                'standard_rating' => 4.0,
                'individual_rating' => 4.5,
                'standard_score' => 120.0,  // 4.0 * 30
                'individual_score' => 135.0, // 4.5 * 30
                'gap_rating' => 0.5,
                'gap_score' => 15.0,
            ]);

        AspectAssessment::factory()
            ->forCategoryAssessment($categoryAssessment)
            ->forAspect($aspect3)
            ->create([
                'standard_rating' => 3.5,
                'individual_rating' => 4.0,
                'standard_score' => 140.0,  // 3.5 * 40
                'individual_score' => 160.0, // 4.0 * 40
                'gap_rating' => 0.5,
                'gap_score' => 20.0,
            ]);

        return [
            'institution' => $institution,
            'event' => $event,
            'template' => $template,
            'category' => $category,
            'aspects' => [$aspect1, $aspect2, $aspect3],
            'position' => $position,
            'participant' => $participant,
            'categoryAssessment' => $categoryAssessment,
        ];
    }

    // ========================================
    // HELPER METHODS (EXISTING)
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

        // 9. Create AspectAssessment using factory with explicit values
        $aspectAssessment = AspectAssessment::factory()
            ->forCategoryAssessment($categoryAssessment)
            ->forAspect($aspect)
            ->create([
                'standard_rating' => 3.0,
                'standard_score' => 60.0,
                'individual_rating' => 3.5,
                'individual_score' => 70.0,
                'gap_rating' => 0.5,
                'gap_score' => 10.0,
                'percentage_score' => 100,
                'conclusion_code' => 'MS',
                'conclusion_text' => 'Memenuhi Standar',
            ]);

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

    /**
     * Create assessment WITH sub-aspects (Potensi category)
     *
     * Creates Potensi category aspect (e.g., Kecerdasan) with 3 sub-aspects
     * to test data-driven calculation
     *
     * Sub-aspects:
     * - Sub 1: Individual=3, Standard=3
     * - Sub 2: Individual=4, Standard=3
     * - Sub 3: Individual=5, Standard=4
     *
     * Expected Averages:
     * - Individual: (3+4+5)/3 = 4.0
     * - Standard: (3+3+4)/3 = 3.33
     */
    private function createAssessmentWithSubAspects(): array
    {
        // 1. Institution
        $institution = Institution::create([
            'code' => 'INST_TEST_SUB',
            'name' => 'Test Institution',
            'api_key' => 'test_api_key',
        ]);

        // 2. Event
        $event = AssessmentEvent::create([
            'institution_id' => $institution->id,
            'code' => 'EVT_TEST_SUB',
            'name' => 'Test Event 2025',
            'year' => 2025,
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'status' => 'ongoing',
        ]);

        // 3. Template
        $template = AssessmentTemplate::create([
            'name' => 'Staff Standard v1',
            'code' => 'staff_standard_v1_sub',
            'description' => 'Standard assessment for testing',
        ]);

        // 4. POTENSI Category (has sub-aspects)
        $category = CategoryType::create([
            'template_id' => $template->id,
            'code' => 'potensi',
            'name' => 'Potensi',
            'weight_percentage' => 50,
            'order' => 1,
        ]);

        // 5. Aspect WITH sub-aspects (Kecerdasan)
        $aspect = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $category->id,
            'code' => 'asp_pot_kecerdasan',
            'name' => 'Kecerdasan',
            'description' => 'Kemampuan kognitif',
            'standard_rating' => null,
            'weight_percentage' => 25,
            'order' => 1,
        ]);

        // Create 3 sub-aspects
        $subAspect1 = SubAspect::create([
            'aspect_id' => $aspect->id,
            'code' => 'sub_kecerdasan_verbal',
            'name' => 'Kecerdasan Verbal',
            'standard_rating' => 3,
            'order' => 1,
        ]);

        $subAspect2 = SubAspect::create([
            'aspect_id' => $aspect->id,
            'code' => 'sub_kecerdasan_numerik',
            'name' => 'Kecerdasan Numerik',
            'standard_rating' => 3,
            'order' => 2,
        ]);

        $subAspect3 = SubAspect::create([
            'aspect_id' => $aspect->id,
            'code' => 'sub_kecerdasan_spasial',
            'name' => 'Kecerdasan Spasial',
            'standard_rating' => 4,
            'order' => 3,
        ]);

        // 6. Position
        $position = PositionFormation::create([
            'event_id' => $event->id,
            'template_id' => $template->id,
            'name' => 'Staff IT',
            'code' => 'POS_IT_SUB',
            'quota' => 10,
        ]);

        // 7. Participant
        $participant = Participant::factory()->create([
            'event_id' => $event->id,
            'position_formation_id' => $position->id,
            'assessment_date' => '2025-01-15',
        ]);

        // 8. CategoryAssessment
        $categoryAssessment = CategoryAssessment::factory()
            ->forParticipant($participant)
            ->forCategoryType($category)
            ->create();

        // 9. AspectAssessment (parent) - will be calculated from sub-aspects
        $aspectAssessment = AspectAssessment::factory()
            ->forCategoryAssessment($categoryAssessment)
            ->forAspect($aspect)
            ->create([
                'standard_rating' => 3.33, // Will be recalculated by service
                'individual_rating' => 4.0, // Will be recalculated by service
            ]);

        // 10. Create SubAspectAssessments with specific ratings
        SubAspectAssessment::factory()
            ->forAspectAssessment($aspectAssessment)
            ->forSubAspect($subAspect1)
            ->create([
                'individual_rating' => 3,
                'rating_label' => 'Cukup',
            ]);

        SubAspectAssessment::factory()
            ->forAspectAssessment($aspectAssessment)
            ->forSubAspect($subAspect2)
            ->create([
                'individual_rating' => 4,
                'rating_label' => 'Baik',
            ]);

        SubAspectAssessment::factory()
            ->forAspectAssessment($aspectAssessment)
            ->forSubAspect($subAspect3)
            ->create([
                'individual_rating' => 5,
                'rating_label' => 'Sangat Baik',
            ]);

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
            'subAspects' => [$subAspect1, $subAspect2, $subAspect3],
        ];
    }
}
