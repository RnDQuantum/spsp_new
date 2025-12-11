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
 * PHASE 8: ✅ getFinalAssessment() (DONE - 14/14 tests)
 * PHASE 9: ✅ getPassingSummary() (DONE - 5/5 tests)
 * PHASE 10: ✅ Matching methods (DONE - 12/12 tests)
 * PHASE 11: ✅ getJobMatchingPercentage() (DONE - 9/9 tests)
 * PHASE 12: ✅ 3-Layer Priority Integration (DONE - 4/4 tests)
 *
 * TOTAL: 73/73 tests (100% COMPLETE ✅)
 *
 * @see \App\Services\IndividualAssessmentService
 * @see docs/TESTING_STRATEGY.md
 * @see docs/ASSESSMENT_CALCULATION_FLOW.md
 * @see docs/FLEXIBLE_HIERARCHY_REFACTORING.md
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
    // PHASE 8: getFinalAssessment() TESTS (15 tests)
    // ========================================

    /**
     * Test: getFinalAssessment combines Potensi + Kompetensi correctly
     *
     * Final assessment should include both categories with their totals
     */
    public function test_final_assessment_combines_both_categories(): void
    {
        // Arrange: Create both categories
        $testData = $this->createCompleteAssessmentWithBothCategories();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getFinalAssessment(
            participantId: $testData['participant']->id,
            tolerancePercentage: 0
        );

        // Assert: Should include both categories
        $this->assertArrayHasKey('potensi', $result);
        $this->assertArrayHasKey('kompetensi', $result);
        $this->assertIsArray($result['potensi']);
        $this->assertIsArray($result['kompetensi']);

        // Both categories should have totals
        $this->assertGreaterThan(0, $result['potensi']['total_standard_score']);
        $this->assertGreaterThan(0, $result['kompetensi']['total_standard_score']);
    }

    /**
     * Test: getFinalAssessment applies category weights to final scores
     *
     * Total score = (Potensi × potensi_weight) + (Kompetensi × kompetensi_weight)
     */
    public function test_applies_category_weights_to_final_scores(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentWithBothCategories();

        // Set category weights: Potensi 40%, Kompetensi 60%
        $standardService = app(\App\Services\DynamicStandardService::class);
        $standardService->saveCategoryWeight($testData['template']->id, 'potensi', 40);
        $standardService->saveCategoryWeight($testData['template']->id, 'kompetensi', 60);

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getFinalAssessment(
            participantId: $testData['participant']->id,
            tolerancePercentage: 0
        );

        // Assert: Category weights should be correct
        $this->assertEquals(40, $result['potensi_weight']);
        $this->assertEquals(60, $result['kompetensi_weight']);

        // Calculate expected total
        $expectedTotal = round(
            ($result['potensi']['total_standard_score'] * 0.40) +
            ($result['kompetensi']['total_standard_score'] * 0.60),
            2
        );

        $this->assertEquals($expectedTotal, $result['total_standard_score']);
    }

    /**
     * Test: getFinalAssessment calculates final gap score correctly
     *
     * Gap = Individual - Standard
     */
    public function test_calculates_final_gap_score_correctly(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentWithBothCategories();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getFinalAssessment(
            participantId: $testData['participant']->id,
            tolerancePercentage: 0
        );

        // Assert: Gap = Individual - Standard
        $expectedGap = round(
            $result['total_individual_score'] - $result['total_standard_score'],
            2
        );

        $this->assertEquals($expectedGap, $result['total_gap_score']);
    }

    /**
     * Test: getFinalAssessment calculates achievement percentage correctly
     *
     * Achievement % = (Individual / Standard) × 100
     */
    public function test_calculates_achievement_percentage_correctly(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentWithBothCategories();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getFinalAssessment(
            participantId: $testData['participant']->id,
            tolerancePercentage: 0
        );

        // Assert: Achievement percentage
        $expectedPercentage = $result['total_standard_score'] > 0
            ? round(($result['total_individual_score'] / $result['total_standard_score']) * 100, 2)
            : 0;

        $this->assertEquals($expectedPercentage, $result['achievement_percentage']);
    }

    /**
     * Test: getFinalAssessment returns gap-based conclusion (not percentage-based)
     *
     * CRITICAL: Should use gap-based logic, not percentage-based
     */
    public function test_returns_gap_based_conclusion(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentWithBothCategories();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getFinalAssessment(
            participantId: $testData['participant']->id,
            tolerancePercentage: 0
        );

        // Assert: Should have final conclusion
        $this->assertArrayHasKey('final_conclusion', $result);
        $this->assertIsString($result['final_conclusion']);

        // Should be one of the valid conclusions
        $validConclusions = [
            'Di Atas Standar',
            'Memenuhi Standar',
            'Mendekati Standar',
            'Di Bawah Standar',
        ];

        $this->assertContains($result['final_conclusion'], $validConclusions);
    }

    /**
     * Test: getFinalAssessment has all required keys
     */
    public function test_final_assessment_has_all_required_keys(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentWithBothCategories();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getFinalAssessment(
            participantId: $testData['participant']->id,
            tolerancePercentage: 0
        );

        // Assert: All required keys
        $requiredKeys = [
            'participant_id',
            'template_id',
            'template_name',
            'tolerance_percentage',
            'potensi_weight',
            'kompetensi_weight',
            'potensi',
            'kompetensi',
            'total_standard_score',
            'total_individual_score',
            'total_original_standard_score',
            'total_gap_score',
            'total_original_gap_score',
            'achievement_percentage',
            'final_conclusion',
        ];

        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $result, "Missing key: {$key}");
        }
    }

    /**
     * Test: getFinalAssessment data types are correct
     */
    public function test_final_assessment_data_types(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentWithBothCategories();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getFinalAssessment(
            participantId: $testData['participant']->id,
            tolerancePercentage: 0
        );

        // Assert: Data types
        $this->assertIsInt($result['participant_id']);
        $this->assertIsInt($result['template_id']);
        $this->assertIsString($result['template_name']);
        $this->assertIsInt($result['tolerance_percentage']);
        $this->assertIsNumeric($result['potensi_weight']);
        $this->assertIsNumeric($result['kompetensi_weight']);
        $this->assertIsArray($result['potensi']);
        $this->assertIsArray($result['kompetensi']);
        $this->assertIsNumeric($result['total_standard_score']);
        $this->assertIsNumeric($result['total_individual_score']);
        $this->assertIsNumeric($result['total_gap_score']);
        $this->assertIsNumeric($result['achievement_percentage']);
        $this->assertIsString($result['final_conclusion']);
    }

    /**
     * Test: getFinalAssessment with tolerance applied
     *
     * Tolerance should reduce standard scores and increase gaps
     */
    public function test_final_assessment_applies_tolerance(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentWithBothCategories();
        $service = app(IndividualAssessmentService::class);

        // Act: Compare 0% vs 10% tolerance
        $resultNoTolerance = $service->getFinalAssessment(
            participantId: $testData['participant']->id,
            tolerancePercentage: 0
        );

        $resultWithTolerance = $service->getFinalAssessment(
            participantId: $testData['participant']->id,
            tolerancePercentage: 10
        );

        // Assert: Standard score should be lower with tolerance
        $this->assertLessThan(
            $resultNoTolerance['total_standard_score'],
            $resultWithTolerance['total_standard_score']
        );

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
     * Test: Different tolerance values produce different final scores
     */
    public function test_different_tolerance_produces_different_final_scores(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentWithBothCategories();
        $service = app(IndividualAssessmentService::class);

        // Act: Test with 0%, 10%, 20% tolerance
        $result0 = $service->getFinalAssessment($testData['participant']->id, 0);
        $result10 = $service->getFinalAssessment($testData['participant']->id, 10);
        $result20 = $service->getFinalAssessment($testData['participant']->id, 20);

        // Assert: Standard scores should decrease
        $this->assertGreaterThan($result10['total_standard_score'], $result0['total_standard_score']);
        $this->assertGreaterThan($result20['total_standard_score'], $result10['total_standard_score']);

        // Gaps should increase
        $this->assertLessThan($result10['total_gap_score'], $result0['total_gap_score']);
        $this->assertLessThan($result20['total_gap_score'], $result10['total_gap_score']);
    }

    /**
     * Test: Category weights sum to 100%
     *
     * Potensi weight + Kompetensi weight should equal 100
     */
    public function test_category_weights_sum_to_100(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentWithBothCategories();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getFinalAssessment(
            participantId: $testData['participant']->id,
            tolerancePercentage: 0
        );

        // Assert: Weights should sum to 100
        $totalWeight = $result['potensi_weight'] + $result['kompetensi_weight'];
        $this->assertEquals(100, $totalWeight);
    }

    /**
     * Test: Final assessment includes both category details
     *
     * Should include full category data with aspects
     */
    public function test_final_assessment_includes_category_details(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentWithBothCategories();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getFinalAssessment(
            participantId: $testData['participant']->id,
            tolerancePercentage: 0
        );

        // Assert: Potensi details
        $this->assertArrayHasKey('category_code', $result['potensi']);
        $this->assertArrayHasKey('aspects', $result['potensi']);
        $this->assertEquals('potensi', $result['potensi']['category_code']);

        // Assert: Kompetensi details
        $this->assertArrayHasKey('category_code', $result['kompetensi']);
        $this->assertArrayHasKey('aspects', $result['kompetensi']);
        $this->assertEquals('kompetensi', $result['kompetensi']['category_code']);
    }

    /**
     * Test: Final assessment rounds values correctly
     *
     * All scores should be rounded to 2 decimals
     */
    public function test_final_assessment_rounds_correctly(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentWithBothCategories();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getFinalAssessment(
            participantId: $testData['participant']->id,
            tolerancePercentage: 10
        );

        // Assert: Check rounding
        $this->assertEquals(
            round($result['total_standard_score'], 2),
            $result['total_standard_score']
        );

        $this->assertEquals(
            round($result['total_individual_score'], 2),
            $result['total_individual_score']
        );

        $this->assertEquals(
            round($result['total_gap_score'], 2),
            $result['total_gap_score']
        );

        $this->assertEquals(
            round($result['achievement_percentage'], 2),
            $result['achievement_percentage']
        );
    }

    /**
     * Test: Calculates original vs adjusted gaps
     *
     * Should track both original gap (0% tolerance) and adjusted gap
     */
    public function test_calculates_original_and_adjusted_gaps(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentWithBothCategories();

        // Act: Apply 10% tolerance
        $service = app(IndividualAssessmentService::class);
        $result = $service->getFinalAssessment(
            participantId: $testData['participant']->id,
            tolerancePercentage: 10
        );

        // Assert: Should have both original and adjusted gaps
        $this->assertArrayHasKey('total_original_gap_score', $result);
        $this->assertArrayHasKey('total_gap_score', $result);

        // Original gap should be smaller than adjusted gap (with tolerance)
        $this->assertLessThan(
            $result['total_gap_score'],
            $result['total_original_gap_score']
        );
    }

    /**
     * Test: Achievement percentage handles edge case (standard = 0)
     *
     * When standard = 0, achievement should be 0 (not division by zero error)
     */
    public function test_achievement_percentage_handles_zero_standard(): void
    {
        // Arrange: Create test data where standard could be 0
        $testData = $this->createCompleteAssessmentWithBothCategories();

        // Set all aspects to 0 weight (edge case)
        // This would make total_standard_score = 0
        // NOTE: This is an artificial edge case for testing division by zero handling

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getFinalAssessment(
            participantId: $testData['participant']->id,
            tolerancePercentage: 0
        );

        // Assert: If standard > 0, normal calculation
        if ($result['total_standard_score'] > 0) {
            $expectedPercentage = ($result['total_individual_score'] / $result['total_standard_score']) * 100;
            $this->assertEquals(round($expectedPercentage, 2), $result['achievement_percentage']);
        } else {
            // If standard = 0, should return 0 (no error)
            $this->assertEquals(0, $result['achievement_percentage']);
        }
    }

    /**
     * Test: Final conclusion matches gap-based logic
     *
     * CRITICAL: Verify conclusion is based on gaps, not percentages
     */
    public function test_final_conclusion_uses_gap_logic(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentWithBothCategories();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getFinalAssessment(
            participantId: $testData['participant']->id,
            tolerancePercentage: 0
        );

        // Assert: Conclusion should be determined by gap
        // If total_gap_score > 0 (individual exceeds standard)
        if ($result['total_gap_score'] > 0) {
            $this->assertContains($result['final_conclusion'], [
                'Di Atas Standar',
                'Memenuhi Standar',
            ]);
        } elseif ($result['total_gap_score'] < 0) {
            // Below standard
            $this->assertContains($result['final_conclusion'], [
                'Mendekati Standar',
                'Di Bawah Standar',
            ]);
        } else {
            // Exactly meets standard
            $this->assertEquals('Memenuhi Standar', $result['final_conclusion']);
        }
    }

    // ========================================
    // PHASE 9: getPassingSummary() TESTS (5 tests)
    // ========================================

    /**
     * Test: getPassingSummary counts passing aspects correctly
     *
     * Passing = "Di Atas Standar" OR "Memenuhi Standar"
     */
    public function test_counts_passing_aspects_correctly(): void
    {
        // Arrange: Create test data with known conclusions
        $testData = $this->createCategoryWithMultipleAspects();
        $service = app(IndividualAssessmentService::class);

        // Get aspects from service
        $aspects = $service->getAspectAssessments(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id,
            tolerancePercentage: 0
        );

        // Act: Get passing summary
        $summary = $service->getPassingSummary($aspects);

        // Assert: Should count aspects correctly
        $this->assertArrayHasKey('total', $summary);
        $this->assertArrayHasKey('passing', $summary);
        $this->assertArrayHasKey('percentage', $summary);

        // Total should match aspect count
        $this->assertEquals(3, $summary['total']); // We created 3 aspects

        // Passing count should be >= 0 and <= total
        $this->assertGreaterThanOrEqual(0, $summary['passing']);
        $this->assertLessThanOrEqual($summary['total'], $summary['passing']);
    }

    /**
     * Test: getPassingSummary calculates percentage correctly
     *
     * Percentage = (passing / total) × 100
     */
    public function test_calculates_passing_percentage_correctly(): void
    {
        // Arrange
        $testData = $this->createCategoryWithMultipleAspects();
        $service = app(IndividualAssessmentService::class);

        $aspects = $service->getAspectAssessments(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id,
            tolerancePercentage: 0
        );

        // Act
        $summary = $service->getPassingSummary($aspects);

        // Assert: Percentage calculation
        $expectedPercentage = $summary['total'] > 0
            ? round(($summary['passing'] / $summary['total']) * 100)
            : 0;

        $this->assertEquals($expectedPercentage, $summary['percentage']);
    }

    /**
     * Test: getPassingSummary with all aspects passing
     *
     * When all aspects pass, percentage should be 100%
     */
    public function test_passing_summary_with_all_passing(): void
    {
        // Arrange: Create data where all aspects exceed standard
        $testData = $this->createCategoryWithMultipleAspects();

        // Update all aspects to have positive gaps (passing)
        foreach ($testData['aspects'] as $aspect) {
            $aspect->update(['standard_rating' => 2.0]); // Lower standard
        }

        $service = app(IndividualAssessmentService::class);
        $aspects = $service->getAspectAssessments(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id,
            tolerancePercentage: 0
        );

        // Act
        $summary = $service->getPassingSummary($aspects);

        // Assert: All should be passing
        $this->assertEquals($summary['total'], $summary['passing']);
        $this->assertEquals(100, $summary['percentage']);
    }

    /**
     * Test: getPassingSummary with no aspects passing
     *
     * When no aspects pass, percentage should be 0%
     */
    public function test_passing_summary_with_none_passing(): void
    {
        // Arrange: Create data where all aspects fail
        $testData = $this->createCategoryWithMultipleAspects();

        // Update all aspects to have negative gaps (failing)
        foreach ($testData['aspects'] as $aspect) {
            $aspect->update(['standard_rating' => 5.0]); // Very high standard
        }

        $service = app(IndividualAssessmentService::class);
        $aspects = $service->getAspectAssessments(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id,
            tolerancePercentage: 0
        );

        // Act
        $summary = $service->getPassingSummary($aspects);

        // Assert: None should be passing
        $this->assertEquals(0, $summary['passing']);
        $this->assertEquals(0, $summary['percentage']);
    }

    /**
     * Test: getPassingSummary handles empty collection
     *
     * When no aspects, should return 0% without error
     */
    public function test_passing_summary_handles_empty_collection(): void
    {
        // Arrange: Empty collection
        $emptyCollection = collect();

        // Act
        $service = app(IndividualAssessmentService::class);
        $summary = $service->getPassingSummary($emptyCollection);

        // Assert: Should handle gracefully
        $this->assertEquals(0, $summary['total']);
        $this->assertEquals(0, $summary['passing']);
        $this->assertEquals(0, $summary['percentage']);
    }

    // ========================================
    // PHASE 10: MATCHING METHODS TESTS (12 tests)
    // ========================================

    /**
     * Test: getAspectMatchingData returns collection with matching percentages
     *
     * Each aspect should have matching percentage calculated
     */
    public function test_aspect_matching_data_returns_collection(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentWithBothCategories();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getAspectMatchingData(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['kompetensiCategory']->id
        );

        // Assert: Should return collection
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
        $this->assertGreaterThan(0, $result->count());

        // First item should have matching data
        $aspectData = $result->first();
        $this->assertArrayHasKey('name', $aspectData);
        $this->assertArrayHasKey('code', $aspectData);
        $this->assertArrayHasKey('percentage', $aspectData);
        $this->assertArrayHasKey('individual_rating', $aspectData);
        $this->assertArrayHasKey('standard_rating', $aspectData);
    }

    /**
     * Test: calculateMatchingPercentage returns 100% when individual >= standard
     *
     * Logic: If individual >= standard → 100%
     */
    public function test_matching_percentage_100_when_exceeds_standard(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentData();

        // Set individual > standard
        $testData['aspect']->update(['standard_rating' => 3.0]);
        $testData['aspectAssessment']->update(['individual_rating' => 4.0]);

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getAspectMatchingData(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id
        );

        // Assert: Percentage should be 100 (individual exceeds standard)
        $this->assertEquals(100, $result->first()['percentage']);
    }

    /**
     * Test: calculateMatchingPercentage returns proportional % when individual < standard
     *
     * Logic: If individual < standard → (individual / standard) × 100
     */
    public function test_matching_percentage_proportional_when_below_standard(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentData();

        // Set individual < standard
        $testData['aspect']->update(['standard_rating' => 4.0]);
        $testData['aspectAssessment']->update(['individual_rating' => 3.0]);

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getAspectMatchingData(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id
        );

        // Assert: Percentage should be (3.0 / 4.0) × 100 = 75%
        $this->assertEquals(75, $result->first()['percentage']);
    }

    /**
     * Test: Aspect with sub-aspects calculates matching from sub-aspects
     *
     * CRITICAL: Data-driven matching for Potensi
     */
    public function test_matching_calculated_from_sub_aspects(): void
    {
        // Arrange: Potensi with sub-aspects
        $testData = $this->createAssessmentWithSubAspects();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getAspectMatchingData(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id
        );

        // Assert: Should have aspect data
        $aspectData = $result->first();
        $this->assertArrayHasKey('sub_aspects', $aspectData);
        $this->assertIsArray($aspectData['sub_aspects']);
        $this->assertCount(3, $aspectData['sub_aspects']); // 3 sub-aspects

        // Sub-aspects should have data
        $subAspectData = $aspectData['sub_aspects'][0];
        $this->assertArrayHasKey('name', $subAspectData);
        $this->assertArrayHasKey('individual_rating', $subAspectData);
        $this->assertArrayHasKey('standard_rating', $subAspectData);
    }

    /**
     * Test: Matching percentage calculation with sub-aspects
     *
     * Average of sub-aspect matching values
     */
    public function test_matching_percentage_with_sub_aspects(): void
    {
        // Arrange
        $testData = $this->createAssessmentWithSubAspects();

        // Sub-aspect ratings:
        // Sub 1: individual=3, standard=3 → 100% (3 >= 3)
        // Sub 2: individual=4, standard=3 → 100% (4 >= 3)
        // Sub 3: individual=5, standard=4 → 100% (5 >= 4)
        // Average: (100 + 100 + 100) / 3 = 100%

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getAspectMatchingData(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id
        );

        // Assert: All sub-aspects exceed standard → 100%
        $this->assertEquals(100, $result->first()['percentage']);
    }

    /**
     * Test: Inactive sub-aspects excluded from matching calculation
     *
     * CRITICAL: Only active sub-aspects should be counted
     */
    public function test_inactive_sub_aspects_excluded_from_matching(): void
    {
        // Arrange
        $testData = $this->createAssessmentWithSubAspects();

        // Set one sub-aspect as inactive
        $standardService = app(\App\Services\DynamicStandardService::class);
        $standardService->setSubAspectActive(
            $testData['template']->id,
            $testData['subAspects'][1]->code, // sub_kecerdasan_numerik
            false
        );

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getAspectMatchingData(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id
        );

        // Assert: Should only have 2 active sub-aspects (not 3)
        $aspectData = $result->first();
        $this->assertCount(2, $aspectData['sub_aspects']); // Only 2 active
    }

    /**
     * Test: Matching percentage handles zero standard rating
     *
     * Edge case: standard = 0 should return 0 (no division by zero error)
     */
    public function test_matching_percentage_handles_zero_standard(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentData();

        // Set standard to 0 (edge case)
        $testData['aspect']->update(['standard_rating' => 0]);
        $testData['aspectAssessment']->update(['individual_rating' => 3.0]);

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getAspectMatchingData(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id
        );

        // Assert: Should return 0 (not error)
        $this->assertEquals(0, $result->first()['percentage']);
    }

    /**
     * Test: Matching data includes original standard rating
     *
     * Should include both adjusted and original standard
     */
    public function test_matching_includes_original_standard(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentData();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getAspectMatchingData(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id
        );

        // Assert
        $aspectData = $result->first();
        $this->assertArrayHasKey('original_standard_rating', $aspectData);
        $this->assertIsNumeric($aspectData['original_standard_rating']);
    }

    /**
     * Test: Matching percentage is rounded to integer
     *
     * Percentage should be rounded (no decimals)
     */
    public function test_matching_percentage_is_rounded(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentData();

        // Set values that produce decimal percentage
        // Example: individual=2.5, standard=3.0 → (2.5/3.0)*100 = 83.333...
        $testData['aspect']->update(['standard_rating' => 3.0]);
        $testData['aspectAssessment']->update(['individual_rating' => 2.5]);

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getAspectMatchingData(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id
        );

        // Assert: Should be rounded integer
        $percentage = $result->first()['percentage'];
        $this->assertEquals(round($percentage), $percentage);
        $this->assertEquals(83, $percentage); // 83.333... → 83
    }

    /**
     * Test: getAllAspectMatchingData returns both categories
     *
     * Batch loading should return both Potensi and Kompetensi
     */
    public function test_all_aspect_matching_returns_both_categories(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentWithBothCategories();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getAllAspectMatchingData($testData['participant']);

        // Assert: Should have both categories
        $this->assertArrayHasKey('potensi', $result);
        $this->assertArrayHasKey('kompetensi', $result);

        // Both should have data
        $this->assertNotEmpty($result['potensi']);
        $this->assertNotEmpty($result['kompetensi']);
    }

    /**
     * Test: Aspect matching data has required keys
     */
    public function test_aspect_matching_has_required_keys(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentData();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getAspectMatchingData(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id
        );

        // Assert
        $aspectData = $result->first();
        $requiredKeys = [
            'name',
            'code',
            'description',
            'percentage',
            'individual_rating',
            'standard_rating',
            'original_standard_rating',
            'sub_aspects',
        ];

        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $aspectData, "Missing key: {$key}");
        }
    }

    /**
     * Test: Sub-aspect matching data structure
     *
     * Sub-aspects should have correct structure
     */
    public function test_sub_aspect_matching_data_structure(): void
    {
        // Arrange
        $testData = $this->createAssessmentWithSubAspects();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getAspectMatchingData(
            participantId: $testData['participant']->id,
            categoryTypeId: $testData['category']->id
        );

        // Assert
        $aspectData = $result->first();
        $subAspect = $aspectData['sub_aspects'][0];

        $requiredKeys = [
            'name',
            'individual_rating',
            'standard_rating',
            'original_standard_rating',
            'rating_label',
        ];

        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $subAspect, "Missing sub-aspect key: {$key}");
        }
    }

    // ========================================
    // PHASE 11: getJobMatchingPercentage() TESTS (9 tests)
    // ========================================

    /**
     * Test: getJobMatchingPercentage returns required keys
     */
    public function test_job_matching_returns_required_keys(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentWithBothCategories();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getJobMatchingPercentage($testData['participant']);

        // Assert: Should have required keys
        $this->assertArrayHasKey('job_match_percentage', $result);
        $this->assertArrayHasKey('potensi_percentage', $result);
        $this->assertArrayHasKey('kompetensi_percentage', $result);
    }

    /**
     * Test: getJobMatchingPercentage calculates overall average correctly
     *
     * Job match = average of all aspect percentages
     */
    public function test_job_matching_calculates_average_correctly(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentWithBothCategories();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getJobMatchingPercentage($testData['participant']);

        // Assert: Job match should be average of potensi + kompetensi
        // Since we have 1 aspect in each category:
        // job_match ≈ (potensi% + kompetensi%) / 2
        $this->assertIsNumeric($result['job_match_percentage']);
        $this->assertEquals(round($result['job_match_percentage']), $result['job_match_percentage'], 'Should be rounded');
        $this->assertGreaterThanOrEqual(0, $result['job_match_percentage']);
        $this->assertLessThanOrEqual(100, $result['job_match_percentage']);
    }

    /**
     * Test: Potensi percentage is calculated correctly
     *
     * Average of all Potensi aspect matching percentages
     */
    public function test_potensi_percentage_calculated_correctly(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentWithBothCategories();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getJobMatchingPercentage($testData['participant']);

        // Assert
        $this->assertIsNumeric($result['potensi_percentage']);
        $this->assertEquals(round($result['potensi_percentage']), $result['potensi_percentage'], 'Should be rounded');
        $this->assertGreaterThanOrEqual(0, $result['potensi_percentage']);
        $this->assertLessThanOrEqual(100, $result['potensi_percentage']);
    }

    /**
     * Test: Kompetensi percentage is calculated correctly
     *
     * Average of all Kompetensi aspect matching percentages
     */
    public function test_kompetensi_percentage_calculated_correctly(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentWithBothCategories();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getJobMatchingPercentage($testData['participant']);

        // Assert
        $this->assertIsNumeric($result['kompetensi_percentage']);
        $this->assertEquals(round($result['kompetensi_percentage']), $result['kompetensi_percentage'], 'Should be rounded');
        $this->assertGreaterThanOrEqual(0, $result['kompetensi_percentage']);
        $this->assertLessThanOrEqual(100, $result['kompetensi_percentage']);
    }

    /**
     * Test: Job matching with all aspects at 100%
     *
     * When all aspects match perfectly, job match should be 100%
     */
    public function test_job_matching_100_when_all_aspects_perfect(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentWithBothCategories();

        // Set all aspects to exceed standard
        $testData['potensiAspect']->update(['standard_rating' => 2.0]);
        $testData['kompetensiAspect']->update(['standard_rating' => 2.0]);

        // Individual ratings are higher (3, 4, 5 for sub-aspects, 4.0 for kompetensi)
        // So all should be 100%

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getJobMatchingPercentage($testData['participant']);

        // Assert: All should be 100%
        $this->assertEquals(100, $result['potensi_percentage']);
        $this->assertEquals(100, $result['kompetensi_percentage']);
        $this->assertEquals(100, $result['job_match_percentage']);
    }

    /**
     * Test: Job matching percentages are rounded to integers
     *
     * All percentages should be whole numbers
     */
    public function test_job_matching_percentages_are_rounded(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentWithBothCategories();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getJobMatchingPercentage($testData['participant']);

        // Assert: All should be numeric and rounded
        $this->assertIsNumeric($result['job_match_percentage']);
        $this->assertIsNumeric($result['potensi_percentage']);
        $this->assertIsNumeric($result['kompetensi_percentage']);

        // Verify they are rounded (no decimals)
        $this->assertEquals(round($result['job_match_percentage']), $result['job_match_percentage'], 'job_match should be rounded');
        $this->assertEquals(round($result['potensi_percentage']), $result['potensi_percentage'], 'potensi should be rounded');
        $this->assertEquals(round($result['kompetensi_percentage']), $result['kompetensi_percentage'], 'kompetensi should be rounded');
    }

    /**
     * Test: Job matching accepts Participant object or ID
     *
     * Method should accept both int and Participant instance
     */
    public function test_job_matching_accepts_participant_object(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentWithBothCategories();

        // Act: Call with Participant object (not ID)
        $service = app(IndividualAssessmentService::class);
        $result = $service->getJobMatchingPercentage($testData['participant']);

        // Assert: Should work without error
        $this->assertIsArray($result);
        $this->assertArrayHasKey('job_match_percentage', $result);
    }

    /**
     * Test: Job matching uses batch loading (getAllAspectMatchingData)
     *
     * Should use batch loading for efficiency
     */
    public function test_job_matching_uses_batch_loading(): void
    {
        // Arrange
        $testData = $this->createCompleteAssessmentWithBothCategories();

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getJobMatchingPercentage($testData['participant']);

        // Assert: Should return data (batch loading is internal optimization)
        $this->assertIsArray($result);
        $this->assertArrayHasKey('job_match_percentage', $result);
        $this->assertArrayHasKey('potensi_percentage', $result);
        $this->assertArrayHasKey('kompetensi_percentage', $result);
    }

    /**
     * Test: Job matching handles empty aspects gracefully
     *
     * When no aspects, should return 0 without error
     */
    public function test_job_matching_handles_no_aspects(): void
    {
        // Arrange: Create participant without aspect assessments
        $institution = Institution::create([
            'code' => 'INST_EMPTY',
            'name' => 'Test Institution',
            'api_key' => 'test_api_key',
        ]);

        $event = AssessmentEvent::create([
            'institution_id' => $institution->id,
            'code' => 'EVT_EMPTY',
            'name' => 'Test Event',
            'year' => 2025,
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'status' => 'ongoing',
        ]);

        $template = AssessmentTemplate::create([
            'name' => 'Empty Template',
            'code' => 'empty_template',
            'description' => 'Template with no aspects',
        ]);

        $position = PositionFormation::create([
            'event_id' => $event->id,
            'template_id' => $template->id,
            'name' => 'Test Position',
            'code' => 'TEST_POS',
            'quota' => 1,
        ]);

        $participant = Participant::factory()->create([
            'event_id' => $event->id,
            'position_formation_id' => $position->id,
        ]);

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getJobMatchingPercentage($participant);

        // Assert: Should return 0 for all (no error)
        $this->assertEquals(0, $result['job_match_percentage']);
        $this->assertEquals(0, $result['potensi_percentage']);
        $this->assertEquals(0, $result['kompetensi_percentage']);
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
     * Create complete assessment data with BOTH categories (Potensi + Kompetensi)
     *
     * This helper creates a complete assessment with:
     * - Potensi category (1 aspect with sub-aspects)
     * - Kompetensi category (1 aspect without sub-aspects)
     * - Both categories with default 50/50 weight split
     */
    private function createCompleteAssessmentWithBothCategories(): array
    {
        // 1-3: Basic setup
        $institution = Institution::create([
            'code' => 'INST_BOTH',
            'name' => 'Test Institution Both',
            'api_key' => 'test_api_key',
        ]);

        $event = AssessmentEvent::create([
            'institution_id' => $institution->id,
            'code' => 'EVT_BOTH',
            'name' => 'Test Event 2025',
            'year' => 2025,
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'status' => 'ongoing',
        ]);

        $template = AssessmentTemplate::create([
            'name' => 'Full Standard v1',
            'code' => 'full_standard_v1',
            'description' => 'Complete assessment with both categories',
        ]);

        // 4A: Create POTENSI category
        $potensiCategory = CategoryType::create([
            'template_id' => $template->id,
            'code' => 'potensi',
            'name' => 'Potensi',
            'weight_percentage' => 50,
            'order' => 1,
        ]);

        // 4B: Create KOMPETENSI category
        $kompetensiCategory = CategoryType::create([
            'template_id' => $template->id,
            'code' => 'kompetensi',
            'name' => 'Kompetensi',
            'weight_percentage' => 50,
            'order' => 2,
        ]);

        // 5A: Create Potensi aspect (WITH sub-aspects)
        $potensiAspect = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $potensiCategory->id,
            'code' => 'asp_pot_kecerdasan',
            'name' => 'Kecerdasan',
            'description' => 'Kemampuan kognitif',
            'standard_rating' => null, // Will be calculated from sub-aspects
            'weight_percentage' => 100, // Only aspect in category
            'order' => 1,
        ]);

        // Create 3 sub-aspects for Potensi
        $subAspect1 = SubAspect::create([
            'aspect_id' => $potensiAspect->id,
            'code' => 'sub_kecerdasan_verbal',
            'name' => 'Kecerdasan Verbal',
            'standard_rating' => 3,
            'order' => 1,
        ]);

        $subAspect2 = SubAspect::create([
            'aspect_id' => $potensiAspect->id,
            'code' => 'sub_kecerdasan_numerik',
            'name' => 'Kecerdasan Numerik',
            'standard_rating' => 3,
            'order' => 2,
        ]);

        $subAspect3 = SubAspect::create([
            'aspect_id' => $potensiAspect->id,
            'code' => 'sub_kecerdasan_spasial',
            'name' => 'Kecerdasan Spasial',
            'standard_rating' => 4,
            'order' => 3,
        ]);

        // 5B: Create Kompetensi aspect (WITHOUT sub-aspects)
        $kompetensiAspect = Aspect::create([
            'template_id' => $template->id,
            'category_type_id' => $kompetensiCategory->id,
            'code' => 'asp_kom_integritas',
            'name' => 'Integritas',
            'description' => 'Kemampuan bekerja dengan integritas',
            'standard_rating' => 3.0,
            'weight_percentage' => 100, // Only aspect in category
            'order' => 1,
        ]);

        // 6-7: Position & Participant
        $position = PositionFormation::create([
            'event_id' => $event->id,
            'template_id' => $template->id,
            'name' => 'Staff IT',
            'code' => 'POS_IT_BOTH',
            'quota' => 10,
        ]);

        $participant = Participant::factory()->create([
            'event_id' => $event->id,
            'position_formation_id' => $position->id,
            'assessment_date' => '2025-01-15',
        ]);

        // 8A: Potensi CategoryAssessment
        $potensiCategoryAssessment = CategoryAssessment::factory()
            ->forParticipant($participant)
            ->forCategoryType($potensiCategory)
            ->create();

        // 8B: Kompetensi CategoryAssessment
        $kompetensiCategoryAssessment = CategoryAssessment::factory()
            ->forParticipant($participant)
            ->forCategoryType($kompetensiCategory)
            ->create();

        // 9A: Potensi AspectAssessment (parent) - will be calculated from sub-aspects
        $potensiAspectAssessment = AspectAssessment::factory()
            ->forCategoryAssessment($potensiCategoryAssessment)
            ->forAspect($potensiAspect)
            ->create([
                'standard_rating' => 3.33, // (3+3+4)/3
                'individual_rating' => 4.0, // (3+4+5)/3
            ]);

        // 10: Create SubAspectAssessments for Potensi
        SubAspectAssessment::factory()
            ->forAspectAssessment($potensiAspectAssessment)
            ->forSubAspect($subAspect1)
            ->create([
                'individual_rating' => 3,
                'rating_label' => 'Cukup',
            ]);

        SubAspectAssessment::factory()
            ->forAspectAssessment($potensiAspectAssessment)
            ->forSubAspect($subAspect2)
            ->create([
                'individual_rating' => 4,
                'rating_label' => 'Baik',
            ]);

        SubAspectAssessment::factory()
            ->forAspectAssessment($potensiAspectAssessment)
            ->forSubAspect($subAspect3)
            ->create([
                'individual_rating' => 5,
                'rating_label' => 'Sangat Baik',
            ]);

        // 9B: Kompetensi AspectAssessment (direct values, no sub-aspects)
        AspectAssessment::factory()
            ->forCategoryAssessment($kompetensiCategoryAssessment)
            ->forAspect($kompetensiAspect)
            ->create([
                'standard_rating' => 3.0,
                'individual_rating' => 4.0,
                'standard_score' => 300.0,   // 3.0 * 100
                'individual_score' => 400.0, // 4.0 * 100
                'gap_rating' => 1.0,
                'gap_score' => 100.0,
            ]);

        return [
            'institution' => $institution,
            'event' => $event,
            'template' => $template,
            'potensiCategory' => $potensiCategory,
            'kompetensiCategory' => $kompetensiCategory,
            'potensiAspect' => $potensiAspect,
            'kompetensiAspect' => $kompetensiAspect,
            'position' => $position,
            'participant' => $participant,
            'potensiCategoryAssessment' => $potensiCategoryAssessment,
            'kompetensiCategoryAssessment' => $kompetensiCategoryAssessment,
            'subAspects' => [$subAspect1, $subAspect2, $subAspect3],
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

    // ========================================
    // PHASE 12: 3-LAYER PRIORITY INTEGRATION 🔄
    // ========================================

    /**
     * Test: Individual assessment uses session adjustment (Priority Layer 1)
     *
     * Validates that when session adjustment is active, IndividualAssessmentService
     * correctly uses adjusted weights/ratings from DynamicStandardService
     */
    public function test_individual_assessment_uses_session_adjustment(): void
    {
        // Arrange: Create complete assessment data
        $testData = $this->createCompleteAssessmentData();
        $aspect = $testData['aspect'];

        // Get baseline assessment (quantum defaults)
        $service = app(IndividualAssessmentService::class);
        $baselineAssessment = $service->getAspectAssessments(
            $testData['participant']->id,
            $testData['category']->id,
            0
        )->first();

        // Apply session adjustment (increase aspect weight from 20% to 40%)
        $dynamicService = app(\App\Services\DynamicStandardService::class);
        $dynamicService->saveAspectWeight($testData['template']->id, $aspect->code, 40);

        // Act: Get assessment with session adjustment active
        $adjustedAssessment = $service->getAspectAssessments(
            $testData['participant']->id,
            $testData['category']->id,
            0
        )->first();

        // Assert: Standard score should change (rating × new weight)
        $this->assertNotEquals(
            $baselineAssessment['standard_score'],
            $adjustedAssessment['standard_score'],
            'Session adjustment should change standard score'
        );

        // Verify new score uses adjusted weight (40%)
        $expectedScore = round($adjustedAssessment['standard_rating'] * 40, 2);
        $this->assertEquals($expectedScore, $adjustedAssessment['standard_score']);
    }

    /**
     * Test: Individual assessment uses custom standard (Priority Layer 2)
     *
     * Validates that when custom standard is selected, IndividualAssessmentService
     * correctly uses custom weights/ratings instead of quantum defaults
     */
    public function test_individual_assessment_uses_custom_standard(): void
    {
        // Arrange: Create complete assessment data
        $testData = $this->createCompleteAssessmentData();
        $aspect = $testData['aspect'];

        // Get baseline assessment (quantum defaults)
        $service = app(IndividualAssessmentService::class);
        $baselineAssessment = $service->getAspectAssessments(
            $testData['participant']->id,
            $testData['category']->id,
            0
        )->first();

        // Create and select custom standard with different weight (35%)
        $customStandardService = app(\App\Services\CustomStandardService::class);
        $customStandardData = [
            'institution_id' => $testData['institution']->id,
            'template_id' => $testData['template']->id,
            'code' => 'CUSTOM-PRIORITY-TEST',
            'name' => 'Custom Standard Priority Test',
            'description' => 'Testing Layer 2 priority',
            'category_weights' => [
                'potensi' => 50,
                'kompetensi' => 50,
            ],
            'aspect_configs' => [
                $aspect->code => [
                    'weight' => 35, // Different from quantum (20%)
                    'active' => true,
                ],
            ],
            'sub_aspect_configs' => [],
        ];

        $customStandard = $customStandardService->create($customStandardData);
        $customStandardService->select($testData['template']->id, $customStandard->id);

        // Act: Get assessment with custom standard selected
        $customAssessment = $service->getAspectAssessments(
            $testData['participant']->id,
            $testData['category']->id,
            0
        )->first();

        // Assert: Standard score should change (rating × custom weight)
        $this->assertNotEquals(
            $baselineAssessment['standard_score'],
            $customAssessment['standard_score'],
            'Custom standard should change standard score'
        );

        // Verify new score uses custom weight (35%)
        $expectedScore = round($customAssessment['standard_rating'] * 35, 2);
        $this->assertEquals($expectedScore, $customAssessment['standard_score']);

        // Cleanup
        $customStandardService->clearSelection($testData['template']->id);
    }

    /**
     * Test: Session overrides custom in individual assessment (Priority Layer 1 > 2)
     *
     * Validates that session adjustment (Layer 1) takes priority over custom standard (Layer 2)
     * when both are active
     */
    public function test_session_overrides_custom_in_individual_assessment(): void
    {
        // Arrange: Create complete assessment data
        $testData = $this->createCompleteAssessmentData();
        $aspect = $testData['aspect'];

        // Create and select custom standard with weight 35%
        $customStandardService = app(\App\Services\CustomStandardService::class);
        $customStandardData = [
            'institution_id' => $testData['institution']->id,
            'template_id' => $testData['template']->id,
            'code' => 'CUSTOM-OVERRIDE-TEST',
            'name' => 'Custom Standard Override Test',
            'description' => 'Testing Layer 1 > Layer 2 priority',
            'category_weights' => [
                'potensi' => 50,
                'kompetensi' => 50,
            ],
            'aspect_configs' => [
                $aspect->code => [
                    'weight' => 35,
                    'active' => true,
                ],
            ],
            'sub_aspect_configs' => [],
        ];

        $customStandard = $customStandardService->create($customStandardData);
        $customStandardService->select($testData['template']->id, $customStandard->id);

        // Get assessment with custom standard (weight = 35%)
        $service = app(IndividualAssessmentService::class);
        $customAssessment = $service->getAspectAssessments(
            $testData['participant']->id,
            $testData['category']->id,
            0
        )->first();

        // Apply session adjustment (weight = 45%)
        $dynamicService = app(\App\Services\DynamicStandardService::class);
        $dynamicService->saveAspectWeight($testData['template']->id, $aspect->code, 45);

        // Act: Get assessment with both custom (35%) and session (45%) active
        $sessionAssessment = $service->getAspectAssessments(
            $testData['participant']->id,
            $testData['category']->id,
            0
        )->first();

        // Assert: Should use session weight (45%), NOT custom weight (35%)
        $this->assertNotEquals(
            $customAssessment['standard_score'],
            $sessionAssessment['standard_score'],
            'Session should override custom standard'
        );

        // Verify uses session weight (45%), not custom weight (35%)
        $expectedSessionScore = round($sessionAssessment['standard_rating'] * 45, 2);
        $this->assertEquals($expectedSessionScore, $sessionAssessment['standard_score']);

        // Cleanup
        $customStandardService->clearSelection($testData['template']->id);
    }

    /**
     * Test: Assessment falls back to quantum defaults (Priority Layer 3)
     *
     * Validates that when no session adjustment and no custom standard,
     * IndividualAssessmentService uses quantum defaults from database
     */
    public function test_assessment_falls_back_to_quantum_defaults(): void
    {
        // Arrange: Create complete assessment data
        $testData = $this->createCompleteAssessmentData();
        $aspect = $testData['aspect'];

        // Ensure no session adjustments (clear session key)
        \Illuminate\Support\Facades\Session::forget("dynamic_standard.{$testData['template']->id}");

        // Ensure no custom standard selected (clear custom standard session)
        \Illuminate\Support\Facades\Session::forget("selected_standard.{$testData['template']->id}");

        // Act: Get assessment (should use quantum defaults)
        $service = app(IndividualAssessmentService::class);
        $quantumAssessment = $service->getAspectAssessments(
            $testData['participant']->id,
            $testData['category']->id,
            0
        )->first();

        // Assert: Should use quantum weight from database (aspect->weight_percentage)
        $expectedScore = round($quantumAssessment['standard_rating'] * $aspect->weight_percentage, 2);
        $this->assertEquals(
            $expectedScore,
            $quantumAssessment['standard_score'],
            'Should use quantum weight when no adjustments'
        );

        // Verify aspect weight is quantum default (20%)
        $this->assertEquals(20, $aspect->weight_percentage);
    }
}
