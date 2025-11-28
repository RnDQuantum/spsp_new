# Testing Strategy - Assessment Calculation System

> **Version**: 1.0
> **Last Updated**: 2025-01-28
> **Purpose**: Comprehensive testing documentation untuk validasi sistem assessment
> **Important**: Dokumen ini adalah **Single Source of Truth** untuk semua testing efforts

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Testing Philosophy](#testing-philosophy)
3. [Service Testing (Unit Tests)](#service-testing-unit-tests)
4. [Livewire Testing (Feature Tests)](#livewire-testing-feature-tests)
5. [Test Data Setup](#test-data-setup)
6. [Test Coverage Matrix](#test-coverage-matrix)
7. [Running Tests](#running-tests)
8. [Troubleshooting](#troubleshooting)

---

## Overview

### Kenapa Testing Penting?

**Problem Statement**:
- ❓ Apakah perhitungan yang ditampilkan sudah benar?
- ❓ Apakah konsisten antara individual report dan ranking report?
- ❓ Apakah logic tetap valid setelah aspek dihilangkan/diubah?
- ❓ Apakah tolerance calculation sudah tepat?
- ❓ Apakah matching percentage logic benar?

**Solution: Automated Testing**
- ✅ Validasi semua nilai kolom
- ✅ Deteksi regression bugs
- ✅ Dokumentasi living (tests as specification)
- ✅ Confidence untuk refactoring
- ✅ Fast feedback loop

### Testing Pyramid

```
           ┌─────────────┐
           │  E2E Tests  │ ← Minimal (mahal, lambat, flaky)
           └─────────────┘
         ┌─────────────────┐
         │ Feature Tests   │ ← Moderate (integration testing)
         │  (Livewire)     │    ~30% coverage
         └─────────────────┘
       ┌───────────────────────┐
       │   Unit Tests          │ ← Maximum (cepat, murah, reliable)
       │   (Services)          │    ~70% coverage
       └───────────────────────┘
```

**Prinsip**:
1. **Unit tests (Services) = Majority** - Test logic murni, cepat, mudah debug
2. **Feature tests (Livewire) = Integration** - Test workflow, data flow
3. **E2E tests = Minimal** - Test critical user journeys (manual/automated)

---

## Testing Philosophy

### Best Practices

1. **Test Behavior, Not Implementation**
   - ✅ GOOD: Test output values, conclusions, formulas
   - ❌ BAD: Test private methods, implementation details

2. **AAA Pattern** (Arrange-Act-Assert)
   ```php
   test('calculates gap correctly with tolerance', function () {
       // Arrange
       $participant = createParticipantWithAssessment([
           'standard_rating' => 4.0,
           'individual_rating' => 4.5,
       ]);

       // Act
       $result = app(IndividualAssessmentService::class)
           ->getAspectAssessments($participant->id, $categoryId, 10);

       // Assert
       expect($result->first()['gap_rating'])->toBe(0.9); // 4.5 - (4.0 * 0.9)
   });
   ```

3. **One Test, One Concept**
   - Each test validates ONE specific scenario
   - Clear, focused test names

4. **Test Data Independence**
   - Each test creates its own data
   - No shared state between tests
   - Use factories for data creation

5. **Fast Tests**
   - Unit tests should run in milliseconds
   - Use in-memory SQLite for speed
   - Mock external dependencies

---

## Service Testing (Unit Tests)

### Priority Matrix

| Service | Priority | Complexity | Impact | Est. Tests |
|---------|----------|------------|--------|------------|
| **IndividualAssessmentService** | ⭐⭐⭐ CRITICAL | High | Very High | 40-50 |
| **RankingService** | ⭐⭐⭐ CRITICAL | High | Very High | 30-40 |
| **DynamicStandardService** | ⭐⭐ HIGH | Medium | High | 25-30 |
| **ConclusionService** | ⭐ MEDIUM | Low | Medium | 10-15 |
| **StatisticService** | ⭐ MEDIUM | Medium | Medium | 15-20 |
| **InterpretationGeneratorService** | ⭐ LOW | High | Low | 10-15 |
| **TrainingRecommendationService** | ⭐ LOW | Medium | Low | 10-15 |

**Start with**: IndividualAssessmentService (paling critical)

---

### 1. IndividualAssessmentService Tests

**File**: `tests/Unit/Services/IndividualAssessmentServiceTest.php`

#### Test Categories

##### A. Aspect Assessment Calculation

**Test Scenarios**:

1. **Data-Driven Rating Calculation**
   ```php
   test('calculates aspect rating from sub-aspects when present', function () {
       // Aspect HAS sub-aspects (e.g., Kecerdasan)
       // Sub-aspects: [3, 4, 5] → Aspect rating = 4.0
   });

   test('uses direct rating when aspect has no sub-aspects', function () {
       // Aspect NO sub-aspects (e.g., Integritas)
       // Direct rating: 4 → Aspect rating = 4.0
   });

   test('skips inactive sub-aspects in calculation', function () {
       // Sub-aspects: [3 (active), 4 (inactive), 5 (active)]
       // Expected: (3 + 5) / 2 = 4.0 (skip 4)
   });
   ```

2. **Score Calculation**
   ```php
   test('calculates aspect score correctly', function () {
       // Rating: 4.5, Weight: 15%
       // Expected score: 4.5 × 15 = 67.5
   });

   test('uses adjusted weight from session', function () {
       // Original weight: 10%, Adjusted weight: 15%
       // Expected: Use 15% for calculation
   });
   ```

3. **Tolerance Application**
   ```php
   test('applies tolerance to standard rating', function () {
       // Standard: 4.0, Tolerance: 10%
       // Expected adjusted standard: 4.0 × 0.9 = 3.6
   });

   test('calculates gap with tolerance correctly', function () {
       // Individual: 4.5, Standard: 4.0, Tolerance: 10%
       // Adjusted standard: 3.6
       // Expected gap: 4.5 - 3.6 = 0.9
   });

   test('keeps original values for reference', function () {
       // Must return both original and adjusted values
       expect($result)->toHaveKeys([
           'original_standard_rating',
           'original_gap_rating',
           'standard_rating', // adjusted
           'gap_rating', // adjusted
       ]);
   });
   ```

4. **Column Value Validation**
   ```php
   test('returns all required columns', function () {
       $result = $service->getAspectAssessments($participantId, $categoryId, 10);

       expect($result->first())->toHaveKeys([
           'aspect_id',
           'aspect_code',
           'name',
           'description',
           'weight_percentage',
           'original_weight',
           'is_weight_adjusted',
           'original_standard_rating',
           'original_standard_score',
           'standard_rating',
           'standard_score',
           'individual_rating',
           'individual_score',
           'gap_rating',
           'gap_score',
           'original_gap_rating',
           'original_gap_score',
           'percentage_score',
           'conclusion_text',
       ]);
   });

   test('validates numeric ranges', function () {
       $aspect = $result->first();

       // Ratings should be 0-5
       expect($aspect['individual_rating'])
           ->toBeGreaterThanOrEqual(0)
           ->toBeLessThanOrEqual(5);

       // Scores should match formula
       $expectedScore = round(
           $aspect['individual_rating'] * $aspect['weight_percentage'],
           2
       );
       expect($aspect['individual_score'])->toBe($expectedScore);
   });
   ```

5. **Conclusion Logic**
   ```php
   test('returns correct conclusion for di atas standar', function () {
       // Gap > 0.5 → "Di Atas Standar"
   });

   test('returns correct conclusion for memenuhi standar', function () {
       // -0.5 <= Gap <= 0.5 → "Memenuhi Standar"
   });

   test('returns correct conclusion for di bawah standar', function () {
       // Gap < -0.5 → "Di Bawah Standar"
   });
   ```

##### B. Matching Percentage Logic

**Test Scenarios**:

1. **Matching Calculation**
   ```php
   test('returns 100% when individual meets or exceeds standard', function () {
       // Individual: 4, Standard: 3 → 100%
       // Individual: 4, Standard: 4 → 100%
   });

   test('calculates percentage when individual below standard', function () {
       // Individual: 3, Standard: 4
       // Expected: (3 / 4) × 100 = 75%
   });

   test('calculates sub-aspect matching for potensi', function () {
       // Sub-aspects with mixed results
       // Sub 1: 5/4 → 100%
       // Sub 2: 3/4 → 75%
       // Aspect average: (100 + 75) / 2 = 87.5%
   });
   ```

2. **Job Matching**
   ```php
   test('calculates overall job matching percentage', function () {
       // Average of all aspect matching percentages
   });

   test('separates potensi and kompetensi percentages', function () {
       expect($result)->toHaveKeys([
           'job_match_percentage',
           'potensi_percentage',
           'kompetensi_percentage',
       ]);
   });
   ```

##### C. Category Assessment

**Test Scenarios**:

1. **Aggregation**
   ```php
   test('aggregates aspect ratings correctly', function () {
       // Sum of all active aspect ratings
   });

   test('aggregates aspect scores correctly', function () {
       // Sum of all active aspect scores
   });

   test('excludes inactive aspects from totals', function () {
       // Only sum active aspects
   });
   ```

2. **Category Weighting**
   ```php
   test('applies category weight to totals', function () {
       // Total score: 300, Category weight: 60%
       // Weighted score: 300 × 0.6 = 180
   });

   test('uses adjusted category weight from session', function () {
       // Original: 50%, Adjusted: 60%
       // Use: 60%
   });
   ```

##### D. Final Assessment

**Test Scenarios**:

1. **Combined Calculation**
   ```php
   test('combines potensi and kompetensi with weights', function () {
       // Potensi: 180 (60%), Kompetensi: 120 (40%)
       // Total: 180 + 120 = 300
   });

   test('calculates achievement percentage correctly', function () {
       // Individual: 320, Standard: 300
       // Achievement: (320 / 300) × 100 = 106.67%
   });

   test('allows achievement over 100%', function () {
       // Achievement > 100% is valid (exceeds standard)
   });
   ```

2. **Final Conclusion**
   ```php
   test('uses gap-based conclusion not percentage-based', function () {
       // Based on gap_score, not achievement_percentage
   });
   ```

---

### 2. RankingService Tests

**File**: `tests/Unit/Services/RankingServiceTest.php`

#### Test Categories

##### A. Single Category Ranking

**Test Scenarios**:

1. **Ranking Order**
   ```php
   test('ranks participants by score descending', function () {
       // Create 3 participants with different scores
       // Verify rank 1 has highest score
   });

   test('uses name as tiebreaker for same scores', function () {
       // Create 2 participants with same score
       // Different names: "ANDI" vs "BUDI"
       // Verify "ANDI" gets better rank (alphabetical)
   });

   test('maintains consistent ranking across calls', function () {
       // Call getRankings() twice
       // Verify same order
   });
   ```

2. **Adjusted Standards**
   ```php
   test('recalculates standard from session adjustments', function () {
       // Original standard: 400
       // Adjust weight: 10% → 15%
       // Verify new standard calculated
   });

   test('filters inactive aspects in standard calculation', function () {
       // 5 aspects, 1 inactive
       // Verify standard only from 4 active
   });

   test('recalculates individual scores with adjusted weights', function () {
       // Original: Rating 4.0 × Weight 10 = 40
       // Adjusted: Rating 4.0 × Weight 15 = 60
   });
   ```

3. **Tolerance in Ranking**
   ```php
   test('applies tolerance to standard in ranking', function () {
       // Standard: 400, Tolerance: 10%
       // Adjusted: 360
   });

   test('different tolerance changes ranking', function () {
       // Tolerance 10% vs 20%
       // May change conclusion categories
   });
   ```

##### B. Combined Ranking (Potensi + Kompetensi)

**Test Scenarios**:

1. **Weighted Combination**
   ```php
   test('combines categories with correct weights', function () {
       // Potensi: 180 (60%), Kompetensi: 120 (40%)
       // Verify weighted total
   });

   test('ranks by combined score not individual categories', function () {
       // Participant A: High potensi, low kompetensi
       // Participant B: Low potensi, high kompetensi
       // Rank by total weighted score
   });
   ```

2. **Passing Summary**
   ```php
   test('counts passing participants correctly', function () {
       // Create 5 participants: 2 above, 2 meets, 1 below
       // Passing count: 4 (above + meets)
   });

   test('calculates passing percentage correctly', function () {
       // 4 passing out of 5 total = 80%
   });
   ```

---

### 3. DynamicStandardService Tests

**File**: `tests/Unit/Services/DynamicStandardServiceTest.php`

#### Test Categories

##### A. Session Management

**Test Scenarios**:

1. **Save & Retrieve**
   ```php
   test('saves adjustment to session', function () {
       $service->saveAspectWeight(1, 'asp_01', 15);
       expect($service->getAspectWeight(1, 'asp_01'))->toBe(15);
   });

   test('does not save if value equals original', function () {
       // Original: 10, Save: 10
       // Session should remain empty
   });

   test('removes from session when reset to original', function () {
       // Save: 15, then Save: 10 (original)
       // Session should be cleaned up
   });
   ```

2. **Priority Chain**
   ```php
   test('prioritizes session over custom standard', function () {
       // Custom standard: 12
       // Session: 15
       // Expected: 15
   });

   test('prioritizes custom standard over quantum default', function () {
       // Quantum default: 10
       // Custom standard: 12
       // No session
       // Expected: 12
   });

   test('fallbacks to quantum default when no adjustments', function () {
       // No session, no custom standard
       // Expected: 10 (from database)
   });
   ```

3. **Data-Driven Aspect Rating**
   ```php
   test('calculates aspect rating from sub-aspects in quantum', function () {
       // Aspect has sub-aspects in DB
       // Verify calculated average
   });

   test('uses direct rating when no sub-aspects in quantum', function () {
       // Aspect has no sub-aspects
       // Verify uses aspect.standard_rating
   });

   test('calculates aspect rating from sub-aspects in custom standard', function () {
       // Custom standard has sub-aspect ratings
       // Verify calculated average
   });
   ```

##### B. Active/Inactive Status

**Test Scenarios**:

1. **Aspect Status**
   ```php
   test('defaults to active when no session', function () {
       expect($service->isAspectActive(1, 'asp_01'))->toBeTrue();
   });

   test('respects inactive status from session', function () {
       $service->setAspectActive(1, 'asp_01', false);
       expect($service->isAspectActive(1, 'asp_01'))->toBeFalse();
   });

   test('sets weight to 0 when inactive', function () {
       $service->setAspectActive(1, 'asp_01', false);
       expect($service->getAspectWeight(1, 'asp_01'))->toBe(0);
   });
   ```

2. **Sub-Aspect Status**
   ```php
   test('filters inactive sub-aspects in calculation', function () {
       // Similar tests for sub-aspects
   });
   ```

##### C. Validation

**Test Scenarios**:

1. **Category Weight Validation**
   ```php
   test('validates category weights sum to 100', function () {
       expect(fn() => $service->saveBothCategoryWeights(
           1, 'potensi', 60, 'kompetensi', 30 // Total: 90
       ))->toThrow(InvalidArgumentException::class);
   });

   test('allows valid category weights', function () {
       $service->saveBothCategoryWeights(
           1, 'potensi', 60, 'kompetensi', 40 // Total: 100
       );
       expect($service->getCategoryWeight(1, 'potensi'))->toBe(60);
   });
   ```

2. **Rating Range Validation**
   ```php
   test('validates rating between 1-5', function () {
       $errors = $service->validateAdjustments([
           'aspect_ratings' => ['asp_01' => 6] // Invalid
       ]);
       expect($errors)->not->toBeEmpty();
   });
   ```

---

## Livewire Testing (Feature Tests)

### Priority Matrix

| Component/Page | Priority | Test Type | Est. Tests |
|----------------|----------|-----------|------------|
| **GeneralPsyMapping** | ⭐⭐⭐ HIGH | Integration | 10-15 |
| **GeneralMcMapping** | ⭐⭐⭐ HIGH | Integration | 10-15 |
| **GeneralMatching** | ⭐⭐⭐ HIGH | Integration | 10-15 |
| **RankingPsyMapping** | ⭐⭐ MEDIUM | Integration | 8-10 |
| **RankingMcMapping** | ⭐⭐ MEDIUM | Integration | 8-10 |
| **RekapRankingAssessment** | ⭐⭐ MEDIUM | Integration | 8-10 |
| **ToleranceSelector** | ⭐ LOW | Component | 5-8 |
| **EventSelector** | ⭐ LOW | Component | 5-8 |
| **PositionSelector** | ⭐ LOW | Component | 5-8 |

---

### 1. Individual Report Pages

**File**: `tests/Feature/Livewire/IndividualReport/GeneralPsyMappingTest.php`

#### Test Scenarios

##### A. Data Loading

```php
test('loads aspect data from service', function () {
    $participant = createParticipant();

    Volt::test('pages.individual-report.general-psy-mapping', [
        'participant' => $participant
    ])
        ->assertSet('aspectsData', fn($data) =>
            count($data) > 0 && isset($data[0]['aspect_code'])
        );
});

test('displays aspect names correctly', function () {
    Volt::test('pages.individual-report.general-psy-mapping')
        ->assertSee('Kecerdasan')
        ->assertSee('Cara Kerja');
});
```

##### B. Event Handling

```php
test('updates data when tolerance changes', function () {
    Volt::test('pages.individual-report.general-psy-mapping')
        ->set('tolerancePercentage', 10)
        ->assertSet('aspectsData.0.standard_rating', 3.6) // With 10%

        ->dispatch('tolerance-updated', tolerance: 20)
        ->assertSet('aspectsData.0.standard_rating', 3.2) // With 20%
        ->assertDispatched('chartDataUpdated');
});

test('updates data when standard adjusted', function () {
    $participant = createParticipant();

    Volt::test('pages.individual-report.general-psy-mapping', [
        'participant' => $participant
    ])
        ->dispatch('standard-adjusted', templateId: 1)
        ->call('handleStandardUpdate', 1)
        ->assertDispatched('chartDataUpdated');
});
```

##### C. Cache Behavior

```php
test('clears cache on standard adjustment', function () {
    // Verify cache invalidation
    // Load data → Edit standard → Verify new data loaded
});

test('maintains cache within same request', function () {
    // Verify cache reuse
    // Load data twice → Verify service called once
});
```

---

### 2. Matching Reports

**File**: `tests/Feature/Livewire/IndividualReport/GeneralMatchingTest.php`

#### Test Scenarios

##### A. UI Rendering

```php
test('displays green checkmark when individual meets standard', function () {
    $participant = createParticipantWithHighScore();

    Volt::test('pages.individual-report.general-matching', [
        'participant' => $participant
    ])
        ->assertSee('bg-green-100')
        ->assertSee('text-green-600')
        ->assertSee('✓');
});

test('displays red cross when individual below standard', function () {
    $participant = createParticipantWithLowScore();

    Volt::test('pages.individual-report.general-matching')
        ->assertSee('bg-red-100')
        ->assertSee('text-red-600')
        ->assertSee('✗');
});

test('displays gray background for standard column', function () {
    Volt::test('pages.individual-report.general-matching')
        ->assertSee('bg-gray-100');
});
```

##### B. Matching Percentage Display

```php
test('displays 100% when individual meets standard', function () {
    Volt::test('pages.individual-report.general-matching')
        ->assertSee('100%');
});

test('displays correct percentage when below standard', function () {
    // Individual: 3, Standard: 4 → 75%
    Volt::test('pages.individual-report.general-matching')
        ->assertSee('75%');
});
```

---

### 3. Ranking Pages

**File**: `tests/Feature/Livewire/GeneralReport/Ranking/RankingPsyMappingTest.php`

#### Test Scenarios

##### A. Ranking Display

```php
test('displays participants in correct rank order', function () {
    createParticipants(5); // With different scores

    Volt::test('pages.general-report.ranking.ranking-psy-mapping')
        ->assertSeeInOrder(['1', '2', '3', '4', '5']); // Rank numbers
});

test('filters by event correctly', function () {
    Volt::test('pages.general-report.ranking.ranking-psy-mapping')
        ->call('handleEventSelected', eventId: 1)
        ->assertCount('rankings', 10); // 10 participants for event 1
});

test('filters by position correctly', function () {
    Volt::test('pages.general-report.ranking.ranking-psy-mapping')
        ->call('handlePositionSelected', positionId: 1)
        ->assertCount('rankings', 5); // 5 participants for position 1
});
```

##### B. Conclusion Summary

```php
test('displays passing summary correctly', function () {
    Volt::test('pages.general-report.ranking.ranking-psy-mapping')
        ->assertSee('Lulus: 8 dari 10 (80%)');
});
```

---

## Test Data Setup

### Factories Needed

**Priority**:

1. **ParticipantFactory** ✅ (exists)
2. **AssessmentTemplateFactory** (need to create)
3. **CategoryTypeFactory** (need to create)
4. **AspectFactory** (need to create)
5. **SubAspectFactory** (need to create)
6. **AspectAssessmentFactory** (need to create)
7. **SubAspectAssessmentFactory** (need to create)

### Helper Functions

Create `tests/Helpers/AssessmentTestHelpers.php`:

```php
<?php

namespace Tests\Helpers;

use App\Models\Participant;
use App\Models\AssessmentTemplate;
use App\Models\CategoryType;
use App\Models\Aspect;
use App\Models\SubAspect;
use App\Models\AspectAssessment;
use App\Models\SubAspectAssessment;

/**
 * Helper functions for creating test data
 */
class AssessmentTestHelpers
{
    /**
     * Create complete assessment template with default structure
     *
     * Structure:
     * - Potensi (50%): 5 aspects, each with 4-6 sub-aspects
     * - Kompetensi (50%): 7 aspects, no sub-aspects
     */
    public static function createDefaultTemplate(): AssessmentTemplate
    {
        $template = AssessmentTemplate::factory()->create([
            'name' => 'Staff Standard v1',
            'code' => 'staff_standard_v1',
        ]);

        // Create Potensi category
        $potensi = CategoryType::factory()->create([
            'template_id' => $template->id,
            'code' => 'potensi',
            'name' => 'Potensi',
            'weight_percentage' => 50,
        ]);

        // Create Kompetensi category
        $kompetensi = CategoryType::factory()->create([
            'template_id' => $template->id,
            'code' => 'kompetensi',
            'name' => 'Kompetensi',
            'weight_percentage' => 50,
        ]);

        // Create Potensi aspects (with sub-aspects)
        self::createPotensiAspects($template, $potensi);

        // Create Kompetensi aspects (no sub-aspects)
        self::createKompetensiAspects($template, $kompetensi);

        return $template->fresh(['categoryTypes.aspects.subAspects']);
    }

    /**
     * Create participant with complete assessment data
     */
    public static function createParticipantWithAssessment(array $overrides = []): Participant
    {
        $template = self::createDefaultTemplate();

        $participant = Participant::factory()->create([
            'template_id' => $template->id,
        ]);

        // Create aspect assessments
        foreach ($template->aspects as $aspect) {
            $assessment = AspectAssessment::factory()->create([
                'participant_id' => $participant->id,
                'aspect_id' => $aspect->id,
                'standard_rating' => $overrides['standard_rating'] ?? $aspect->standard_rating,
                'individual_rating' => $overrides['individual_rating'] ?? 4,
            ]);

            // Create sub-aspect assessments if aspect has sub-aspects
            if ($aspect->subAspects->isNotEmpty()) {
                foreach ($aspect->subAspects as $subAspect) {
                    SubAspectAssessment::factory()->create([
                        'aspect_assessment_id' => $assessment->id,
                        'sub_aspect_id' => $subAspect->id,
                        'standard_rating' => $overrides['sub_standard_rating'] ?? $subAspect->standard_rating,
                        'individual_rating' => $overrides['sub_individual_rating'] ?? 4,
                    ]);
                }
            }
        }

        return $participant->fresh([
            'positionFormation.template.categoryTypes.aspects.subAspects',
            'aspectAssessments.aspect.subAspects',
            'aspectAssessments.subAspectAssessments.subAspect',
        ]);
    }

    /**
     * Create multiple participants for ranking tests
     */
    public static function createParticipantsWithScores(array $scores): array
    {
        $participants = [];

        foreach ($scores as $score) {
            $participants[] = self::createParticipantWithAssessment([
                'individual_rating' => $score,
            ]);
        }

        return $participants;
    }

    // Private helper methods...
    private static function createPotensiAspects($template, $category) { /* ... */ }
    private static function createKompetensiAspects($template, $category) { /* ... */ }
}
```

---

## Test Coverage Matrix

### Current Status

| Area | Unit Tests | Feature Tests | Total Coverage | Status |
|------|------------|---------------|----------------|--------|
| **Services** | 0% | N/A | 0% | 🔴 Not Started |
| **Livewire Pages** | N/A | 0% | 0% | 🔴 Not Started |
| **Components** | N/A | 0% | 0% | 🔴 Not Started |

### Target Coverage

| Area | Unit Tests | Feature Tests | Total Coverage | Priority |
|------|------------|---------------|----------------|----------|
| **Services** | 80% | N/A | 80% | ⭐⭐⭐ |
| **Livewire Pages** | N/A | 60% | 60% | ⭐⭐ |
| **Components** | N/A | 40% | 40% | ⭐ |

---

## Running Tests

### Commands

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Unit/Services/IndividualAssessmentServiceTest.php

# Run with filter (specific test name)
php artisan test --filter="calculates aspect rating from sub-aspects"

# Run with coverage (requires Xdebug)
php artisan test --coverage

# Run in parallel (faster)
php artisan test --parallel
```

### PHPUnit Configuration

Ensure `phpunit.xml` is configured:

```xml
<phpunit>
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory suffix="Test.php">./tests/Feature</directory>
        </testsuite>
    </testsuites>

    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
        <env name="CACHE_DRIVER" value="array"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="QUEUE_DRIVER" value="sync"/>
    </php>
</phpunit>
```

---

## Troubleshooting

### Common Issues

**Issue 1: Tests slow**
- **Cause**: Using MySQL instead of SQLite
- **Solution**: Check `phpunit.xml` has `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:`

**Issue 2: Factory errors**
- **Cause**: Missing required fields
- **Solution**: Check factory definitions, add default values

**Issue 3: Session data persists between tests**
- **Cause**: Session not cleared
- **Solution**: Add `Session::flush()` in test setup or use `RefreshDatabase` trait

**Issue 4: Livewire component not found**
- **Cause**: Incorrect component path
- **Solution**: Check Volt component path matches file location

---

## Next Steps

### Phase 1: Foundation (Week 1)
- [ ] Create missing factories
- [ ] Setup test helpers
- [ ] Write first 10 tests for IndividualAssessmentService
- [ ] Validate test data setup works

### Phase 2: Core Services (Week 2-3)
- [ ] Complete IndividualAssessmentService tests (40-50 tests)
- [ ] Complete RankingService tests (30-40 tests)
- [ ] Complete DynamicStandardService tests (25-30 tests)

### Phase 3: Livewire Integration (Week 4)
- [ ] GeneralPsyMapping tests (10-15 tests)
- [ ] GeneralMcMapping tests (10-15 tests)
- [ ] GeneralMatching tests (10-15 tests)

### Phase 4: Coverage & Refinement (Week 5)
- [ ] Achieve 80% service coverage
- [ ] Achieve 60% Livewire coverage
- [ ] Document edge cases
- [ ] Setup CI/CD pipeline

---

## Appendix

### Useful Testing Packages

```json
{
    "require-dev": {
        "pestphp/pest": "^2.0",
        "pestphp/pest-plugin-laravel": "^2.0",
        "pestphp/pest-plugin-livewire": "^2.0"
    }
}
```

### Test Naming Conventions

**Pattern**: `test('<action> <expected_result> <conditions>')`

**Examples**:
- ✅ `test('calculates gap correctly with tolerance')`
- ✅ `test('returns 100% when individual meets standard')`
- ✅ `test('skips inactive sub-aspects in calculation')`
- ❌ `test('gap calculation')` (too vague)
- ❌ `test('test_tolerance')` (unclear action/result)

### References

- [Laravel Testing Docs](https://laravel.com/docs/testing)
- [Pest PHP Docs](https://pestphp.com/docs)
- [Livewire Testing Docs](https://livewire.laravel.com/docs/testing)
- [ASSESSMENT_CALCULATION_FLOW.md](./ASSESSMENT_CALCULATION_FLOW.md)
- [SERVICE_ARCHITECTURE.md](./SERVICE_ARCHITECTURE.md)

---

**Version**: 1.0
**Last Updated**: 2025-01-28
**Maintainer**: Development Team
