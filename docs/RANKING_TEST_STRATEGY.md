# RankingService Test Strategy

> **Version**: 2.0
> **Last Updated**: 2025-12-01
> **Purpose**: Complete testing guide for RankingService with 48 comprehensive tests
> **Status**: ✅ All 7 public methods covered with edge cases

---

## Table of Contents

1. [Service Overview](#service-overview)
2. [Testing Approach](#testing-approach)
3. [Test Plan (48 Tests)](#test-plan-48-tests)
4. [Test Data Setup](#test-data-setup)
5. [Helper Methods](#helper-methods)
6. [Common Test Patterns](#common-test-patterns)
7. [Dead Code Alert](#dead-code-alert)

---

## Service Overview

### What is RankingService?

**Purpose**: Single Source of Truth for ranking calculations across all components.

**Key Features**:
1. Integrates with `DynamicStandardService` for **3-layer priority** (session → custom standard → quantum default)
2. Integrates with `ConclusionService` for gap-based conclusions
3. Recalculates standards from active aspects/sub-aspects
4. Supports tolerance adjustment (e.g., 10% tolerance)
5. Consistent ordering: **Score DESC → Name ASC**
6. **Data-driven**: Potensi (sub-aspects) vs Kompetensi (direct)

**Used By**:
- `RankingPsyMapping`, `RankingMcMapping`
- `GeneralPsyMapping`, `GeneralMcMapping`, `GeneralMapping`
- `RekapRankingAssessment`

### Public Methods (7 total)

1. `getRankings()` - Get all rankings for a category
2. `getParticipantRank()` - Get specific participant rank
3. `calculateAdjustedStandards()` - Recalculate standards with adjustments
4. `getCombinedRankings()` - Combined Potensi + Kompetensi rankings
5. `getParticipantCombinedRank()` - Specific participant combined rank
6. `getPassingSummary()` - Statistics (passing count & percentage)
7. `getConclusionSummary()` - Conclusion breakdown

---

## Testing Approach

### Test Strategy

**Total Tests**: **48 tests** covering all 7 public methods + edge cases

### Why 48 Tests? (Updated from 40)

After thorough audit, we found **8 additional critical test cases**:

| Category | Added Tests | Reason |
|----------|-------------|--------|
| **getRankings()** | +3 | All required keys validation, zero tolerance, 100% tolerance |
| **calculateAdjustedStandards()** | +2 | Custom standard priority, all inactive aspects |
| **getCombinedRankings()** | +1 | Zero category weights edge case |
| **getPassingSummary()** | +1 | All participants failing scenario |
| **getConclusionSummary()** | +1 | Homogeneous conclusion data |

### Coverage Goals

- ✅ **100% public method coverage** (7/7 methods)
- ✅ **Data-driven behavior** (Potensi vs Kompetensi)
- ✅ **Integration testing** (DynamicStandardService + ConclusionService)
- ✅ **Edge cases** (empty data, single participant, ties, extreme tolerance)
- ✅ **3-layer priority system** (session → custom → quantum)

---

## Test Plan (48 Tests)

### Phase 1: Service Instantiation (1 test)

**Test Coverage**: Ensure service can be instantiated.

#### Test 1: `test_service_can_be_instantiated`
```php
public function test_service_can_be_instantiated(): void
{
    $service = app(RankingService::class);
    $this->assertInstanceOf(RankingService::class, $service);
}
```

---

### Phase 2: getRankings() - Single Category Rankings (15 tests)

**Method Signature**:
```php
public function getRankings(
    int $eventId,
    int $positionFormationId,
    int $templateId,
    string $categoryCode, // 'potensi' or 'kompetensi'
    int $tolerancePercentage = 10
): Collection
```

**Returns**: Collection of ranking items with keys:
```php
[
    'rank' => 1,
    'participant_id' => 123,
    'individual_rating' => 12.5,
    'individual_score' => 350.0,
    'original_standard_rating' => 10.0,
    'original_standard_score' => 300.0,
    'adjusted_standard_rating' => 9.0,  // with 10% tolerance
    'adjusted_standard_score' => 270.0, // with 10% tolerance
    'original_gap_rating' => 2.5,
    'original_gap_score' => 50.0,
    'adjusted_gap_rating' => 3.5,
    'adjusted_gap_score' => 80.0,
    'percentage' => 129.63,
    'conclusion' => 'Di Atas Standar',
]
```

#### Test 2: `test_returns_empty_collection_when_no_participants`
**Scenario**: Event has no participants
**Expected**: Empty collection

#### Test 3: `test_returns_empty_collection_when_no_active_aspects`
**Scenario**: All aspects are marked inactive
**Expected**: Empty collection

#### Test 4: `test_calculates_rankings_for_potensi_category`
**Scenario**: 3 participants, Potensi category (WITH sub-aspects)
**Expected**:
- Rankings ordered by score DESC
- Aspect ratings calculated from sub-aspects
- All required keys present

#### Test 5: `test_calculates_rankings_for_kompetensi_category`
**Scenario**: 3 participants, Kompetensi category (NO sub-aspects)
**Expected**:
- Rankings ordered by score DESC
- Aspect ratings from direct `standard_rating`
- All required keys present

#### Test 6: `test_applies_tolerance_to_standard_scores`
**Scenario**: 10% tolerance
**Expected**:
- `adjusted_standard_score = original * 0.9`
- Gap calculations use adjusted values

#### Test 7: `test_ranks_by_score_desc_then_name_asc`
**Scenario**: 3 participants with same score
**Expected**: Tiebreaker uses alphabetical name order

#### Test 8: `test_recalculates_with_session_adjustments`
**Scenario**: DynamicStandardService has session adjustments (weight/rating)
**Expected**:
- Uses adjusted weights
- Uses adjusted ratings
- Recalculated scores reflect adjustments

#### Test 9: `test_excludes_inactive_aspects_from_rankings`
**Scenario**: 1 aspect marked inactive via DynamicStandardService
**Expected**:
- Inactive aspect not included in totals
- Rankings only reflect active aspects

#### Test 10: `test_excludes_inactive_sub_aspects_from_calculation`
**Scenario**: 1 sub-aspect marked inactive
**Expected**:
- Sub-aspect excluded from aspect rating average
- Aspect rating = AVG(active sub-aspects only)

#### Test 11: `test_handles_single_participant`
**Scenario**: Only 1 participant in ranking
**Expected**:
- Rank = 1
- All calculations correct

#### Test 12: `test_percentage_calculation_is_correct`
**Scenario**: Various score values
**Expected**:
- `percentage = (individual_score / adjusted_standard_score) * 100`
- Rounded to 2 decimals

#### Test 13: `test_uses_conclusion_service_for_conclusions`
**Scenario**: 3 participants with different gap scores
**Expected**:
- 'Di Atas Standar' when exceeds
- 'Memenuhi Standar' when meets (with tolerance)
- 'Di Bawah Standar' when below

#### Test 14: `test_ranking_items_include_all_required_keys`
**Scenario**: Valid rankings returned
**Expected**: Each ranking item has ALL required keys:
```php
[
    'rank', 'participant_id', 'individual_rating', 'individual_score',
    'original_standard_rating', 'original_standard_score',
    'adjusted_standard_rating', 'adjusted_standard_score',
    'original_gap_rating', 'original_gap_score',
    'adjusted_gap_rating', 'adjusted_gap_score',
    'percentage', 'conclusion'
]
```

#### Test 15: `test_handles_zero_tolerance`
**Scenario**: 0% tolerance (no adjustment)
**Expected**:
- `adjusted_standard_score = original_standard_score`
- No tolerance reduction applied

#### Test 16: `test_handles_extreme_tolerance`
**Scenario**: 100% tolerance (standard becomes zero)
**Expected**:
- `adjusted_standard_score = 0`
- All participants show 'Di Atas Standar' (if individual_score > 0)

---

### Phase 3: getParticipantRank() - Single Participant Rank (5 tests)

**Method Signature**:
```php
public function getParticipantRank(
    int $participantId,
    int $eventId,
    int $positionFormationId,
    int $templateId,
    string $categoryCode,
    int $tolerancePercentage = 10
): ?array
```

**Returns**:
```php
[
    'rank' => 3,
    'total' => 10,
    'conclusion' => 'Memenuhi Standar',
    'percentage' => 95.5,
    'individual_score' => 350.0,
    'adjusted_standard_score' => 270.0,
    'adjusted_gap_score' => 80.0,
]
```

#### Test 17: `test_returns_participant_rank_for_potensi`
**Scenario**: Participant exists, Potensi category
**Expected**: Correct rank, total, conclusion

#### Test 18: `test_returns_participant_rank_for_kompetensi`
**Scenario**: Participant exists, Kompetensi category
**Expected**: Correct rank, total, conclusion

#### Test 19: `test_returns_null_when_participant_not_found`
**Scenario**: Invalid participant ID
**Expected**: `null`

#### Test 20: `test_returns_null_when_no_rankings_exist`
**Scenario**: Event has no participants
**Expected**: `null`

#### Test 21: `test_participant_rank_includes_all_required_keys`
**Scenario**: Valid participant
**Expected**: All 7 keys present (rank, total, conclusion, percentage, individual_score, adjusted_standard_score, adjusted_gap_score)

---

### Phase 4: calculateAdjustedStandards() - Standard Recalculation (10 tests)

**Method Signature**:
```php
public function calculateAdjustedStandards(
    int $templateId,
    string $categoryCode,
    array $aspectIds
): array
```

**Returns**:
```php
[
    'standard_rating' => 10.0,
    'standard_score' => 300.0,
]
```

**Purpose**: Recalculates standards based on:
1. Active aspects/sub-aspects
2. Adjusted weights from DynamicStandardService
3. Adjusted ratings from DynamicStandardService

#### Test 22: `test_calculates_adjusted_standards_for_potensi`
**Scenario**: Potensi category with sub-aspects
**Expected**:
- Rating = SUM(aspect ratings calculated from sub-aspects)
- Score = SUM(rating * adjusted_weight)

#### Test 23: `test_calculates_adjusted_standards_for_kompetensi`
**Scenario**: Kompetensi category without sub-aspects
**Expected**:
- Rating = SUM(direct aspect ratings)
- Score = SUM(rating * adjusted_weight)

#### Test 24: `test_uses_session_adjusted_weights`
**Scenario**: DynamicStandardService has weight adjustments
**Expected**: Uses adjusted weights in score calculation

#### Test 25: `test_uses_session_adjusted_ratings`
**Scenario**: DynamicStandardService has rating adjustments
**Expected**: Uses adjusted ratings in calculation

#### Test 26: `test_excludes_inactive_aspects_from_standards`
**Scenario**: 1 aspect marked inactive
**Expected**: Inactive aspect not included in totals

#### Test 27: `test_excludes_inactive_sub_aspects_from_standards`
**Scenario**: 1 sub-aspect marked inactive
**Expected**: Sub-aspect excluded from aspect rating average

#### Test 28: `test_handles_empty_aspect_ids_array`
**Scenario**: Empty `$aspectIds` array
**Expected**:
```php
[
    'standard_rating' => 0.0,
    'standard_score' => 0.0,
]
```

#### Test 29: `test_rounds_standard_values_to_two_decimals`
**Scenario**: Fractional calculations
**Expected**: Both rating and score rounded to 2 decimals

#### Test 30: `test_uses_custom_standard_when_selected`
**Scenario**: Custom standard selected via CustomStandardService (3-layer priority)
**Expected**:
- Uses custom standard values (not quantum defaults)
- Respects 3-layer priority: session → custom → quantum

#### Test 31: `test_returns_zero_when_all_aspects_inactive`
**Scenario**: All aspects marked inactive
**Expected**:
```php
[
    'standard_rating' => 0.0,
    'standard_score' => 0.0,
]
```

---

### Phase 5: getCombinedRankings() - Potensi + Kompetensi (8 tests)

**Method Signature**:
```php
public function getCombinedRankings(
    int $eventId,
    int $positionFormationId,
    int $templateId,
    int $tolerancePercentage = 10
): Collection
```

**Returns**: Combined rankings with both categories weighted.

**Calculation**:
```php
$totalScore = (potensi_score * potensi_weight%) + (kompetensi_score * kompetensi_weight%)
```

#### Test 32: `test_combines_potensi_and_kompetensi_rankings`
**Scenario**: 3 participants with both categories
**Expected**:
- Weighted total scores calculated correctly
- Ranked by total_individual_score DESC

#### Test 33: `test_applies_category_weights_correctly`
**Scenario**: Template with 40% Potensi, 60% Kompetensi
**Expected**:
- Total score = (potensi_score * 0.4) + (kompetensi_score * 0.6)

#### Test 34: `test_combined_rankings_sorted_by_score_and_name`
**Scenario**: 3 participants with ties
**Expected**: Sort by score DESC, then name ASC

#### Test 35: `test_returns_empty_when_missing_potensi_rankings`
**Scenario**: Potensi rankings empty
**Expected**: Empty collection

#### Test 36: `test_returns_empty_when_missing_kompetensi_rankings`
**Scenario**: Kompetensi rankings empty
**Expected**: Empty collection

#### Test 37: `test_combined_rankings_include_all_required_keys`
**Scenario**: Valid combined rankings
**Expected**: Keys include: rank, participant_id, participant_name, total_individual_score, total_standard_score, total_original_standard_score, total_gap_score, total_original_gap_score, percentage, conclusion, potensi_weight, kompetensi_weight

#### Test 38: `test_combined_rankings_use_conclusion_service`
**Scenario**: Various gap scores
**Expected**: Conclusion based on `ConclusionService::getGapBasedConclusion()`

#### Test 39: `test_handles_zero_category_weights`
**Scenario**: Category weights sum to 0 (edge case)
**Expected**: Empty collection (no valid rankings possible)

---

### Phase 6: getParticipantCombinedRank() - Single Participant Combined Rank (3 tests)

**Method Signature**:
```php
public function getParticipantCombinedRank(
    int $participantId,
    int $eventId,
    int $positionFormationId,
    int $templateId,
    int $tolerancePercentage = 10
): ?array
```

**Returns**:
```php
[
    'rank' => 5,
    'total' => 20,
    'conclusion' => 'Memenuhi Standar',
    'percentage' => 92.5,
    'potensi_weight' => 40,
    'kompetensi_weight' => 60,
]
```

#### Test 40: `test_returns_participant_combined_rank`
**Scenario**: Valid participant with both categories
**Expected**: Correct rank from combined rankings

#### Test 41: `test_returns_null_when_participant_not_in_combined_rankings`
**Scenario**: Participant missing from either category
**Expected**: `null`

#### Test 42: `test_combined_rank_includes_category_weights`
**Scenario**: Valid combined rank
**Expected**: Includes `potensi_weight` and `kompetensi_weight`

---

### Phase 7: getPassingSummary() - Statistics (3 tests)

**Method Signature**:
```php
public function getPassingSummary(Collection $rankings): array
```

**Returns**:
```php
[
    'total' => 20,
    'passing' => 15,  // Di Atas Standar + Memenuhi Standar
    'percentage' => 75,
]
```

#### Test 43: `test_calculates_passing_summary_correctly`
**Scenario**: 10 participants (3 above, 5 meets, 2 below)
**Expected**:
- total = 10
- passing = 8 (above + meets)
- percentage = 80

#### Test 44: `test_passing_summary_handles_empty_rankings`
**Scenario**: Empty rankings collection
**Expected**:
```php
[
    'total' => 0,
    'passing' => 0,
    'percentage' => 0,
]
```

#### Test 45: `test_passing_summary_when_all_participants_fail`
**Scenario**: 10 participants all 'Di Bawah Standar'
**Expected**:
- total = 10
- passing = 0
- percentage = 0

---

### Phase 8: getConclusionSummary() - Conclusion Breakdown (3 tests)

**Method Signature**:
```php
public function getConclusionSummary(Collection $rankings): array
```

**Returns**:
```php
[
    'Di Atas Standar' => 3,
    'Memenuhi Standar' => 5,
    'Di Bawah Standar' => 2,
]
```

#### Test 46: `test_groups_rankings_by_conclusion`
**Scenario**: 10 participants with various conclusions
**Expected**: Correct counts for each conclusion type

#### Test 47: `test_conclusion_summary_handles_empty_rankings`
**Scenario**: Empty rankings collection
**Expected**:
```php
[
    'Di Atas Standar' => 0,
    'Memenuhi Standar' => 0,
    'Di Bawah Standar' => 0,
]
```

#### Test 48: `test_conclusion_summary_with_homogeneous_data`
**Scenario**: All 10 participants have 'Memenuhi Standar'
**Expected**:
```php
[
    'Di Atas Standar' => 0,
    'Memenuhi Standar' => 10,
    'Di Bawah Standar' => 0,
]
```

---

## Test Data Setup

### Recommended Approach

**Use Database Seeder** for realistic, complete test data:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class RankingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected RankingService $service;
    protected $event;
    protected $position;
    protected $template;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed complete assessment structure
        $this->seed([
            InstitutionSeeder::class,
            AssessmentTemplateSeeder::class,
        ]);

        // Create event with participants
        $this->createEventWithParticipants();

        $this->service = app(RankingService::class);
    }
}
```

### Required Data Structure

For comprehensive RankingService tests, you need:

1. **Template Structure** (from seeder or factory):
   - AssessmentTemplate
   - CategoryType (Potensi + Kompetensi)
   - Aspect (3-7 per category)
   - SubAspect (ONLY for Potensi, 3-5 per aspect)

2. **Event Structure**:
   - Institution
   - AssessmentEvent
   - Batch (1-2 batches)
   - PositionFormation (with template_id)

3. **Participants** (minimum 3 for ranking tests):
   - Participant records
   - AspectAssessment (for each participant × aspect)
   - SubAspectAssessment (for Potensi aspects only)
   - CategoryAssessment (Potensi + Kompetensi)
   - FinalAssessment

---

## Helper Methods

### 1. createEventWithParticipants()

Creates a complete event with participants and assessments.

```php
private function createEventWithParticipants(): void
{
    $this->event = AssessmentEvent::factory()->create();
    $this->template = AssessmentTemplate::first(); // Use seeded template

    $this->position = PositionFormation::factory()->create([
        'event_id' => $this->event->id,
        'template_id' => $this->template->id,
    ]);

    // Create 3 participants with varying performance
    $this->createParticipantWithAssessments('High Performer', 'high');
    $this->createParticipantWithAssessments('Medium Performer', 'medium');
    $this->createParticipantWithAssessments('Low Performer', 'low');
}
```

### 2. createParticipantWithAssessments()

Creates a participant with complete assessment data.

```php
private function createParticipantWithAssessments(
    string $name,
    string $performanceLevel
): Participant
{
    $participant = Participant::factory()->create([
        'event_id' => $this->event->id,
        'position_formation_id' => $this->position->id,
        'name' => $name,
    ]);

    // Use AssessmentCalculationService to generate assessments
    $assessmentService = app(AssessmentCalculationService::class);

    // Generate assessments based on performance level
    $ratingMultiplier = match($performanceLevel) {
        'high' => 1.2,    // 20% above standard
        'medium' => 1.0,  // at standard
        'low' => 0.8,     // 20% below standard
    };

    // Create aspect assessments for both categories
    $this->createAspectAssessments($participant, $ratingMultiplier);

    return $participant;
}
```

### 3. createAspectAssessments()

Creates aspect assessments for a participant.

```php
private function createAspectAssessments(
    Participant $participant,
    float $ratingMultiplier
): void
{
    $aspects = Aspect::where('template_id', $this->template->id)->get();

    foreach ($aspects as $aspect) {
        $standardRating = $this->getAspectStandardRating($aspect);
        $individualRating = min(5, $standardRating * $ratingMultiplier);

        AspectAssessment::factory()->create([
            'participant_id' => $participant->id,
            'event_id' => $this->event->id,
            'position_formation_id' => $this->position->id,
            'aspect_id' => $aspect->id,
            'standard_rating' => $standardRating,
            'individual_rating' => $individualRating,
            'standard_score' => $standardRating * $aspect->weight_percentage,
            'individual_score' => $individualRating * $aspect->weight_percentage,
        ]);

        // Create sub-aspect assessments if aspect has sub-aspects
        if ($aspect->subAspects->isNotEmpty()) {
            $this->createSubAspectAssessments($participant, $aspect, $ratingMultiplier);
        }
    }
}
```

### 4. getAspectStandardRating()

Gets standard rating based on aspect structure (data-driven).

```php
private function getAspectStandardRating(Aspect $aspect): float
{
    if ($aspect->subAspects->isNotEmpty()) {
        // Calculate from sub-aspects
        return $aspect->subAspects->avg('standard_rating');
    }

    // Use direct rating
    return (float) $aspect->standard_rating;
}
```

### 5. setSessionAdjustments()

Helper to set DynamicStandardService session adjustments.

```php
private function setSessionAdjustments(int $templateId, array $adjustments): void
{
    $standardService = app(DynamicStandardService::class);

    foreach ($adjustments as $code => $adjustment) {
        if (isset($adjustment['weight'])) {
            $standardService->saveAspectWeight($templateId, $code, $adjustment['weight']);
        }
        if (isset($adjustment['rating'])) {
            $standardService->saveAspectRating($templateId, $code, $adjustment['rating']);
        }
        if (isset($adjustment['active'])) {
            $standardService->saveAspectActive($templateId, $code, $adjustment['active']);
        }
    }
}
```

---

## Common Test Patterns

### Pattern 1: Basic Ranking Test

```php
public function test_calculates_rankings_for_category(): void
{
    // Act
    $rankings = $this->service->getRankings(
        $this->event->id,
        $this->position->id,
        $this->template->id,
        'potensi',
        10
    );

    // Assert
    $this->assertCount(3, $rankings);
    $this->assertEquals(1, $rankings->first()['rank']);
    $this->assertEquals(3, $rankings->last()['rank']);

    // Verify ordering (score DESC)
    $scores = $rankings->pluck('individual_score')->toArray();
    $sortedScores = collect($scores)->sortDesc()->values()->toArray();
    $this->assertEquals($sortedScores, $scores);
}
```

### Pattern 2: Tolerance Test

```php
public function test_applies_tolerance_to_standard_scores(): void
{
    // Act
    $rankings = $this->service->getRankings(
        $this->event->id,
        $this->position->id,
        $this->template->id,
        'potensi',
        10  // 10% tolerance
    );

    // Assert
    $first = $rankings->first();
    $expectedAdjusted = round($first['original_standard_score'] * 0.9, 2);
    $this->assertEquals($expectedAdjusted, $first['adjusted_standard_score']);
}
```

### Pattern 3: Session Adjustment Test

```php
public function test_recalculates_with_session_adjustments(): void
{
    // Arrange
    $this->setSessionAdjustments($this->template->id, [
        'kecerdasan' => ['weight' => 30], // Changed from 25
    ]);

    // Act
    $rankings = $this->service->getRankings(
        $this->event->id,
        $this->position->id,
        $this->template->id,
        'potensi',
        10
    );

    // Assert
    // Scores should reflect new weight (30 instead of 25)
    $this->assertNotNull($rankings->first());
    // Additional assertions...
}
```

### Pattern 4: Empty Data Test

```php
public function test_returns_empty_collection_when_no_participants(): void
{
    // Arrange
    Participant::query()->delete();

    // Act
    $rankings = $this->service->getRankings(
        $this->event->id,
        $this->position->id,
        $this->template->id,
        'potensi',
        10
    );

    // Assert
    $this->assertInstanceOf(Collection::class, $rankings);
    $this->assertCount(0, $rankings);
}
```

### Pattern 5: Conclusion Test

```php
public function test_uses_conclusion_service_for_conclusions(): void
{
    // Act
    $rankings = $this->service->getRankings(
        $this->event->id,
        $this->position->id,
        $this->template->id,
        'potensi',
        10
    );

    // Assert
    foreach ($rankings as $ranking) {
        $expectedConclusion = ConclusionService::getGapBasedConclusion(
            $ranking['original_gap_score'],
            $ranking['adjusted_gap_score']
        );
        $this->assertEquals($expectedConclusion, $ranking['conclusion']);
    }
}
```

---

## Key Testing Considerations

### 1. Data-Driven Testing

**Potensi vs Kompetensi behave differently**:
- Potensi: Rating from sub-aspects → Need sub-aspect test data
- Kompetensi: Direct rating → Simpler test data

### 2. Integration Testing

RankingService depends on:
- `DynamicStandardService` → Test with session adjustments
- `ConclusionService` → Verify conclusion logic
- Database data → Use seeder for realistic scenarios

### 3. Performance Considerations

- Use database transactions for test isolation
- Minimize factory calls (use seeded data when possible)
- Test with 3-5 participants (not 100) for speed

### 4. Edge Cases to Test

- Empty participant list
- Single participant
- All participants with same score (tiebreaker)
- Inactive aspects/sub-aspects
- Zero tolerance vs high tolerance (50%)
- Missing category data

---

## Running Tests

```bash
# Run all RankingService tests
php artisan test tests/Unit/Services/RankingServiceTest.php

# Run specific test
php artisan test --filter=test_calculates_rankings_for_potensi_category

# Run with verbose output
php artisan test tests/Unit/Services/RankingServiceTest.php --verbose

# Run specific phase
php artisan test --filter=test_returns  # All getParticipantRank tests
php artisan test --filter=test_calculates  # All calculation tests
```

---

## Dead Code Alert

### ⚠️ `calculateOriginalStandards()` - UNUSED METHOD

**Location**: [RankingService.php:293](../app/Services/RankingService.php#L293)

```php
private function calculateOriginalStandards(array $aspectIds): array
```

**Status**: 🔴 **NOT CALLED ANYWHERE** in the codebase

**Analysis**:
- This method was likely used in an earlier version
- Current implementation uses `calculateAdjustedStandards()` exclusively
- `calculateAdjustedStandards()` already handles both session/custom adjustments AND quantum defaults

**Recommendation**:
- ❌ **DO NOT TEST** this method (dead code)
- 🗑️ **Consider removing** from codebase in future refactor
- 📝 If kept for backward compatibility, add `@deprecated` annotation

**Impact on Tests**: None - we test only the 7 active public methods.

---

## Test Summary Table

| Phase | Method | Tests | Complexity | Priority |
|-------|--------|-------|------------|----------|
| 1 | Service Instantiation | 1 | Low | ⭐ |
| 2 | `getRankings()` | **15** | ⭐⭐⭐ Very High | ⭐⭐⭐ |
| 3 | `getParticipantRank()` | 5 | Medium | ⭐⭐ |
| 4 | `calculateAdjustedStandards()` | **10** | ⭐⭐⭐ High | ⭐⭐⭐ |
| 5 | `getCombinedRankings()` | **8** | ⭐⭐⭐ High | ⭐⭐⭐ |
| 6 | `getParticipantCombinedRank()` | 3 | Medium | ⭐⭐ |
| 7 | `getPassingSummary()` | **3** | Low | ⭐ |
| 8 | `getConclusionSummary()` | **3** | Low | ⭐ |
| **TOTAL** | **7 public methods** | **48** | - | - |

### Implementation Order (Recommended)

1. **Phase 1** (1 test) - Warmup ✅
2. **Phase 3** (5 tests) - Simple lookup, quick wins ✅
3. **Phase 7-8** (6 tests) - Statistics, straightforward ✅
4. **Phase 4** (10 tests) - Core calculation logic ⭐⭐⭐
5. **Phase 2** (15 tests) - Main ranking method ⭐⭐⭐
6. **Phase 5** (8 tests) - Combined rankings ⭐⭐⭐
7. **Phase 6** (3 tests) - Combined lookup ✅

**Estimated Time**: 4-5 hours total implementation

---

**Version**: 2.0
**Last Updated**: 2025-12-01
**Total Tests**: 48 (updated from 40)
**Coverage**: 100% of public methods (7/7)
**Estimated Time**: 4-5 hours to implement
**Next Review**: After initial test implementation
**Maintainer**: Development Team
