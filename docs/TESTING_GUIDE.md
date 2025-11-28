# Testing Guide - SPSP Assessment System

> **Version**: 1.0
> **Last Updated**: 2025-01-28
> **Purpose**: Quick reference untuk testing strategy dengan PHPUnit

---

## 🎯 Quick Start

### Current Status

| Service | Tests Done | Remaining | Priority | Test File |
|---------|------------|-----------|----------|-----------|
| **DynamicStandardService** | 0/50 | 50 | ⭐⭐⭐ **START HERE** | `tests/Unit/Services/DynamicStandardServiceTest.php` |
| **IndividualAssessmentService** | 14/70 | 56 | ⭐⭐⭐ CRITICAL | `tests/Unit/Services/IndividualAssessmentServiceTest.php` |
| **CustomStandardService** | 0/20 | 20 | ⭐⭐ HIGH | `tests/Unit/Services/CustomStandardServiceTest.php` |
| **RankingService** | 0/40 | 40 | ⭐⭐ HIGH | `tests/Unit/Services/RankingServiceTest.php` |
| TrainingRecommendationService | 0/25 | 25 | ⭐ OPTIONAL | Can be covered via Livewire tests |
| StatisticService | 0/20 | 20 | ⭐ OPTIONAL | Can be covered via Livewire tests |

### Why This Order?

**DynamicStandardService FIRST** karena:
- Semua service lain depend on it
- Implement 3-layer priority chain (session → custom → quantum)
- Data-driven rating calculation
- Paling complex logic

---

## 📁 Test File Structure

```
tests/
├── Unit/                          # Pure logic testing (FAST, isolated)
│   └── Services/
│       ├── DynamicStandardServiceTest.php            # ⭐⭐⭐ Priority #1 (40-50 tests)
│       ├── IndividualAssessmentServiceTest.php       # ⭐⭐⭐ (70 tests total)
│       ├── CustomStandardServiceTest.php             # ⭐⭐ (15-20 tests)
│       ├── RankingServiceTest.php                    # ⭐⭐ (30-40 tests)
│       ├── ConclusionServiceTest.php                 # ⭐ (10-15 tests)
│       ├── TrainingRecommendationServiceTest.php     # ⭐ Optional (20-25 tests)
│       └── StatisticServiceTest.php                  # ⭐ Optional (15-20 tests)
│
└── Feature/                       # Integration testing (SLOWER, realistic)
    └── Livewire/
        ├── IndividualReport/
        │   ├── GeneralPsyMappingTest.php         # 10-15 tests
        │   ├── GeneralMcMappingTest.php          # 10-15 tests
        │   ├── GeneralMatchingTest.php           # 8-10 tests
        │   └── FinalReportTest.php               # 8-10 tests
        │
        └── GeneralReport/
            ├── RankingPsyMappingTest.php         # 8-10 tests
            ├── RankingMcMappingTest.php          # 8-10 tests
            └── StandardPsikometrikTest.php       # 10-12 tests
```

### Key Principles

**1 Service = 1 Test File**
- ✅ `DynamicStandardServiceTest.php` tests ALL methods of DynamicStandardService
- ✅ All 40-50 tests in ONE file (organized by phases)

**1 Livewire Component = 1 Test File**
- ✅ `GeneralPsyMappingTest.php` tests GeneralPsyMapping component
- ✅ Focus on integration, events, UI updates (NOT calculations)

**Service Tests >> Livewire Tests**
- Service: 40-70 tests per file (detailed logic)
- Livewire: 10-15 tests per file (workflow)

---

## 🔄 Testing Strategy: Two Phases

### **Phase 1: Unit Tests (Services) - DO THIS FIRST** ⭐⭐⭐

**Why Service Tests First?**
- ✅ Services = **pure logic**, no UI/session/HTTP
- ✅ **FAST** execution (milliseconds per test)
- ✅ **Easy to debug** (clear input → output)
- ✅ **Foundation** for Livewire tests
- ✅ Test **calculations**, not workflows

**What to Test:**
- Calculations (ratings, scores, gaps, percentages)
- Priority chain logic (session → custom → quantum)
- Data-driven logic (with/without sub-aspects)
- Validation rules
- Edge cases (empty data, zero values, nulls)
- Business rules

**Example:**
```php
// tests/Unit/Services/DynamicStandardServiceTest.php
public function test_returns_session_adjustment_when_exists(): void
{
    // Pure logic test - No UI, no HTTP, no Livewire
    $service->saveAspectWeight(1, 'asp_01', 15);

    $result = $service->getAspectWeight(1, 'asp_01');

    $this->assertEquals(15, $result); // Session wins
}
```

### **Phase 2: Feature Tests (Livewire) - DO THIS AFTER** ⭐⭐

**Why Livewire Tests After?**
- ✅ Livewire **depends on services** (integration)
- ✅ Test **user interactions** & **data flow**
- ✅ SLOWER (need to boot Livewire, Laravel)
- ✅ Higher level (workflows, NOT calculations)

**What to Test:**
- Component renders correctly
- Events dispatched/received (`tolerance-updated`, `standard-switched`)
- Data reloads after events
- UI updates (charts, tables)
- User interactions (clicks, dropdown changes)
- Session state management

**Example:**
```php
// tests/Feature/Livewire/IndividualReport/GeneralPsyMappingTest.php
public function test_updates_data_when_tolerance_changed(): void
{
    // Integration test - User workflow
    Livewire::test(GeneralPsyMapping::class)
        ->set('tolerancePercentage', 10)
        ->assertSet('aspectsData.0.standard_rating', 3.6)

        ->dispatch('tolerance-updated', tolerance: 20)
        ->assertSet('aspectsData.0.standard_rating', 3.2)
        ->assertDispatched('chartDataUpdated');
}
```

---

## 📊 What to Test Where?

### Unit Tests (Services) ✅

| What | Where | Why |
|------|-------|-----|
| Calculation formulas | Service Test | Pure logic |
| Priority chain (session → custom → quantum) | Service Test | Core logic |
| Data-driven rating (with/without sub-aspects) | Service Test | Business rule |
| Validation (weights = 100%, rating 1-5) | Service Test | Rules |
| Edge cases (null, empty, zero) | Service Test | Safety |

### Feature Tests (Livewire) ✅

| What | Where | Why |
|------|-------|-----|
| Component renders | Livewire Test | UI integration |
| Event handling | Livewire Test | Communication |
| Data reload after events | Livewire Test | State sync |
| Chart updates | Livewire Test | UI update |
| Dropdown selection | Livewire Test | User interaction |

### Test Count Comparison

```
Service Test Example:
DynamicStandardServiceTest.php → 40-50 tests
├── Priority chain tests (15-20)
├── Data-driven tests (10-15)
├── Session management (8-10)
├── Active/inactive (8-10)
└── Validation (5-8)

Livewire Test Example:
GeneralPsyMappingTest.php → 10-15 tests
├── Component loads (2-3)
├── Event handling (3-4)
├── Data reload (2-3)
├── UI updates (2-3)
└── User interactions (2-3)
```

**Why different?**
- Service = Test **every calculation path** (many edge cases)
- Livewire = Test **main user workflows** (fewer scenarios)

---

## 🏗️ Critical Architecture (MUST UNDERSTAND)

### 3-Layer Priority System

```
Session Adjustment (temporary, logout → hilang)
         ↓ if not found
Custom Standard (persistent, saved to DB)
         ↓ if not found
Quantum Default (from aspects/sub_aspects table)
```

**Code:**
```php
// DynamicStandardService::getAspectWeight()
// 1. Check session → return if exists
// 2. Check custom standard → return if exists
// 3. Return quantum default (always ada)
```

### Data-Driven Rating

**WITH sub-aspects** (Potensi):
```php
// Rating = Average dari ACTIVE sub-aspects
// Example: [3, 4, 5] → 4.0
```

**WITHOUT sub-aspects** (Kompetensi):
```php
// Rating = Direct value dari aspect
// Example: 4.0 → 4.0
```

**⚠️ CRITICAL Testing Implication:**
```php
// ❌ WRONG for aspects without sub-aspects
$aspectAssessment->update(['standard_rating' => 4.0]);

// ✅ CORRECT
$aspect->update(['standard_rating' => 4.0]);
// Service reads from Aspect model via DynamicStandardService
```

---

## 📝 DynamicStandardService Tests (Priority #1)

**File**: `tests/Unit/Services/DynamicStandardServiceTest.php`
**Framework**: PHPUnit (NOT Pest)
**Estimated**: 40-50 tests

### Test Structure

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Aspect;
use App\Models\CustomStandard;
use App\Models\SubAspect;
use App\Services\DynamicStandardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class DynamicStandardServiceTest extends TestCase
{
    use RefreshDatabase;

    // PHASE 1: Priority Chain (15-20 tests)
    // PHASE 2: Data-Driven Rating (10-15 tests)
    // PHASE 3: Session Management (8-10 tests)
    // PHASE 4: Active/Inactive (8-10 tests)
    // PHASE 5: Validation (5-8 tests)
}
```

### PHASE 1: Priority Chain Tests (HIGHEST PRIORITY)

```php
// Test 1: Session > Custom > Quantum
public function test_returns_session_adjustment_when_exists(): void
{
    // Setup quantum default
    $aspect = Aspect::create([
        'template_id' => 1,
        'code' => 'asp_01',
        'weight_percentage' => 10, // Quantum default
    ]);

    // Setup custom standard
    $customStandard = CustomStandard::create([
        'template_id' => 1,
        'aspect_configs' => ['asp_01' => ['weight' => 12]], // Custom value
    ]);
    Session::put('selected_standard.1', $customStandard->id);

    // Setup session adjustment (highest priority)
    $service = app(DynamicStandardService::class);
    $service->saveAspectWeight(1, 'asp_01', 15);

    // Assert: Session wins
    $result = $service->getAspectWeight(1, 'asp_01');
    $this->assertEquals(15, $result);
}

// Test 2: Custom > Quantum (no session)
public function test_returns_custom_standard_when_no_session_adjustment(): void
{
    $aspect = Aspect::create([
        'template_id' => 1,
        'code' => 'asp_01',
        'weight_percentage' => 10, // Quantum
    ]);

    $customStandard = CustomStandard::create([
        'template_id' => 1,
        'aspect_configs' => ['asp_01' => ['weight' => 12]], // Custom
    ]);
    Session::put('selected_standard.1', $customStandard->id);

    $service = app(DynamicStandardService::class);
    $result = $service->getAspectWeight(1, 'asp_01');

    $this->assertEquals(12, $result); // Custom wins
}

// Test 3: Quantum fallback
public function test_returns_quantum_default_when_no_adjustments(): void
{
    $aspect = Aspect::create([
        'template_id' => 1,
        'code' => 'asp_01',
        'weight_percentage' => 10, // Quantum
    ]);

    $service = app(DynamicStandardService::class);
    $result = $service->getAspectWeight(1, 'asp_01');

    $this->assertEquals(10, $result); // Quantum
}
```

**Repeat for:**
- `getAspectRating()` - dengan data-driven logic
- `getSubAspectRating()`
- `getCategoryWeight()`
- `isAspectActive()`
- `isSubAspectActive()`

### PHASE 2: Data-Driven Rating Tests

```php
// Test: Aspect WITH sub-aspects (Potensi)
public function test_calculates_aspect_rating_from_sub_aspects(): void
{
    $aspect = Aspect::create([
        'template_id' => 1,
        'code' => 'asp_kecerdasan',
        'standard_rating' => null, // NULL because has sub-aspects
    ]);

    SubAspect::create([
        'aspect_id' => $aspect->id,
        'code' => 'sub_01',
        'standard_rating' => 3,
    ]);
    SubAspect::create([
        'aspect_id' => $aspect->id,
        'code' => 'sub_02',
        'standard_rating' => 4,
    ]);
    SubAspect::create([
        'aspect_id' => $aspect->id,
        'code' => 'sub_03',
        'standard_rating' => 5,
    ]);

    $service = app(DynamicStandardService::class);
    $result = $service->getAspectRating(1, 'asp_kecerdasan');

    // (3 + 4 + 5) / 3 = 4.0
    $this->assertEquals(4.0, $result);
}

// Test: Aspect WITHOUT sub-aspects (Kompetensi)
public function test_uses_direct_rating_when_no_sub_aspects(): void
{
    $aspect = Aspect::create([
        'template_id' => 1,
        'code' => 'asp_integritas',
        'standard_rating' => 4.0, // Direct value
    ]);

    $service = app(DynamicStandardService::class);
    $result = $service->getAspectRating(1, 'asp_integritas');

    $this->assertEquals(4.0, $result);
}

// Test: Filters inactive sub-aspects
public function test_filters_inactive_sub_aspects_from_calculation(): void
{
    // Sub 1: 3 (active), Sub 2: 4 (INACTIVE), Sub 3: 5 (active)
    // Expected: (3 + 5) / 2 = 4.0

    // ... create aspect and sub-aspects ...

    $service = app(DynamicStandardService::class);
    $service->setSubAspectActive(1, 'sub_02', false); // Set inactive

    $result = $service->getAspectRating(1, 'asp_kecerdasan');
    $this->assertEquals(4.0, $result); // Skip sub_02
}
```

### PHASE 3: Session Management Tests

```php
// Test: Save only if different from original
public function test_saves_adjustment_when_different_from_original(): void
{
    Aspect::create([
        'template_id' => 1,
        'code' => 'asp_01',
        'weight_percentage' => 10, // Original
    ]);

    $service = app(DynamicStandardService::class);
    $service->saveAspectWeight(1, 'asp_01', 15); // Different

    $adjustments = $service->getAdjustments(1);
    $this->assertArrayHasKey('aspect_weights', $adjustments);
    $this->assertEquals(15, $adjustments['aspect_weights']['asp_01']);
}

// Test: Don't save if equals original
public function test_does_not_save_when_equals_original(): void
{
    Aspect::create([
        'template_id' => 1,
        'code' => 'asp_01',
        'weight_percentage' => 10, // Original
    ]);

    $service = app(DynamicStandardService::class);
    $service->saveAspectWeight(1, 'asp_01', 10); // Same

    $adjustments = $service->getAdjustments(1);
    $this->assertArrayNotHasKey('aspect_weights', $adjustments);
}

// Test: Compare against custom standard when selected
public function test_compares_against_custom_standard_when_selected(): void
{
    // Quantum = 10, Custom = 12 (selected), Save = 12
    // Expected: NOT saved (equals baseline)
}
```

### PHASE 4: Active/Inactive Tests

```php
// Test: Default to active
public function test_defaults_to_active_when_no_adjustments(): void
{
    $service = app(DynamicStandardService::class);
    $result = $service->isAspectActive(1, 'asp_01');

    $this->assertTrue($result);
}

// Test: Set inactive → weight becomes 0
public function test_sets_weight_to_zero_when_aspect_set_inactive(): void
{
    Aspect::create([
        'template_id' => 1,
        'code' => 'asp_01',
        'weight_percentage' => 10,
    ]);

    $service = app(DynamicStandardService::class);
    $service->setAspectActive(1, 'asp_01', false);

    $this->assertEquals(0, $service->getAspectWeight(1, 'asp_01'));
    $this->assertFalse($service->isAspectActive(1, 'asp_01'));
}
```

### PHASE 5: Validation Tests

```php
// Test: Category weights must sum to 100
public function test_validates_category_weights_sum_to_100(): void
{
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Total bobot kategori harus 100%');

    $service = app(DynamicStandardService::class);
    $service->saveBothCategoryWeights(
        1,
        'potensi', 60,
        'kompetensi', 30 // Total: 90 (invalid)
    );
}

// Test: Rating range 1-5
public function test_validates_rating_between_1_and_5(): void
{
    $service = app(DynamicStandardService::class);
    $errors = $service->validateAdjustments([
        'aspect_ratings' => ['asp_01' => 6], // Invalid
    ]);

    $this->assertNotEmpty($errors);
    $this->assertArrayHasKey('aspect_ratings.asp_01', $errors);
}
```

---

## 📝 IndividualAssessmentService Tests (Priority #2)

**Status**: ✅ 14/70 done
**Next**: Test remaining public methods

### Remaining Public Methods

```php
// ✅ DONE
getAspectAssessments($participantId, $categoryTypeId, $tolerancePercentage)

// ⏳ TODO (10-15 tests)
getCategoryAssessment($participantId, $categoryCode, $tolerancePercentage)

// ⏳ TODO (10-15 tests)
getFinalAssessment($participantId, $tolerancePercentage)

// ⏳ TODO (3-5 tests)
getPassingSummary($aspectAssessments)

// ⏳ TODO (10-12 tests)
getAspectMatchingData($participantId, $categoryTypeId)

// ⏳ TODO (5-8 tests)
getJobMatchingPercentage($participantId)
```

### Example: getCategoryAssessment Tests

```php
public function test_aggregates_aspect_scores_correctly(): void
{
    // Setup: Category dengan 3 aspects
    // Aspect 1: score = 20
    // Aspect 2: score = 30
    // Aspect 3: score = 40
    // Expected total: 90

    $testData = $this->createCategoryWithAspects();

    $service = app(IndividualAssessmentService::class);
    $result = $service->getCategoryAssessment(
        $testData['participant']->id,
        'potensi',
        0 // No tolerance
    );

    $this->assertEquals(90, $result['total_individual_score']);
}

public function test_applies_category_weight_to_totals(): void
{
    // Total score: 100, Category weight: 60%
    // Expected weighted: 60
}

public function test_excludes_inactive_aspects_from_totals(): void
{
    // 5 aspects, 2 inactive
    // Expected: Sum only 3 active aspects
}
```

---

## 📝 CustomStandardService Tests (Priority #3)

**File**: `tests/Unit/Services/CustomStandardServiceTest.php`
**Estimated**: 15-20 tests

### Test Categories

```php
// CRUD Tests (5-8 tests)
test_can_create_custom_standard()
test_can_update_custom_standard()
test_can_delete_custom_standard()
test_can_list_standards_for_institution()

// Session Selection Tests (4-6 tests)
test_can_select_custom_standard()
test_selection_stored_in_session()
test_can_clear_selection()
test_selection_resets_adjustments() // IMPORTANT!

// Validation Tests (4-6 tests)
test_validates_category_weights_sum_to_100()
test_validates_rating_range()
test_validates_unique_code_per_institution()

// Template Defaults Tests (2-3 tests)
test_generates_correct_defaults_from_template()
test_data_driven_defaults_for_aspects_with_sub_aspects()
```

---

## 🧪 Test Conventions

### PHPUnit Style (NOT Pest)

**IMPORTANT**: Proyek ini menggunakan **PHPUnit**, BUKAN Pest!

```php
// ✅ CORRECT: PHPUnit
public function test_descriptive_name_in_snake_case(): void
{
    // Arrange
    $data = $this->createTestData();

    // Act
    $result = $service->doSomething($data);

    // Assert
    $this->assertEquals($expected, $result);
}

// ❌ WRONG: Pest style
test('descriptive name', function () {
    expect($result)->toBe($expected);
});
```

### Test Naming Convention

```
test_<what_it_does>_<expected_outcome>_<when_condition>

Examples:
✅ test_returns_session_adjustment_when_exists
✅ test_calculates_aspect_rating_from_sub_aspects
✅ test_validates_category_weights_sum_to_100
✅ test_filters_inactive_aspects_from_calculation

Pattern breakdown:
- test_ (prefix - required)
- returns/calculates/validates/filters (action)
- session_adjustment/aspect_rating (what)
- when_exists/from_sub_aspects (condition)
```

### Test File Template

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Aspect;
use App\Services\DynamicStandardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class DynamicStandardServiceTest extends TestCase
{
    use RefreshDatabase;

    private DynamicStandardService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(DynamicStandardService::class);
    }

    // PHASE 1: Priority Chain Tests
    public function test_returns_session_adjustment_when_exists(): void
    {
        // Test implementation
    }

    // PHASE 2: Data-Driven Tests
    public function test_calculates_aspect_rating_from_sub_aspects(): void
    {
        // Test implementation
    }

    // Helper methods
    private function createTestTemplate(): array
    {
        // Helper implementation
    }
}
```

### Data Setup Best Practices

```php
// ✅ Use factories when available
$customStandard = CustomStandard::factory()->create([
    'template_id' => $templateId,
    'aspect_configs' => [...],
]);

// ✅ Create manually when needed
$aspect = Aspect::create([
    'template_id' => $templateId,
    'code' => 'asp_01',
    'weight_percentage' => 10,
]);

// ✅ Use helper methods for complex setup
private function createCompleteTemplate(): array
{
    $template = AssessmentTemplate::create([...]);
    $category = CategoryType::create([...]);
    $aspect = Aspect::create([...]);

    return compact('template', 'category', 'aspect');
}

// ✅ Use setUp() for common data
protected function setUp(): void
{
    parent::setUp();

    $this->service = app(DynamicStandardService::class);
    $this->templateId = 1;
}
```

### Assertion Best Practices

```php
// ✅ Use specific assertions
$this->assertEquals(15, $result);
$this->assertTrue($isActive);
$this->assertArrayHasKey('aspect_weights', $adjustments);
$this->assertCount(3, $aspects);
$this->assertNull($result);

// ✅ Use assertSame for strict comparison (type + value)
$this->assertSame(15, $result); // int 15, not string "15"

// ✅ Add custom messages for clarity
$this->assertEquals(4.0, $rating, 'Rating should be average of sub-aspects');

// ❌ Avoid generic assertions
$this->assertTrue($result == 15); // Use assertEquals instead
```

---

## 🏃 Running Tests

```bash
# Run all tests
php artisan test

# Run specific file
php artisan test tests/Unit/Services/DynamicStandardServiceTest.php

# Run specific test
php artisan test --filter=test_returns_session_adjustment_when_exists

# Run with coverage (requires Xdebug)
php artisan test --coverage
```

---

## 🔧 Helper Functions

Create reusable helpers in test class:

```php
private function createCompleteTemplate(): array
{
    return [
        'template' => AssessmentTemplate::create([...]),
        'potensi' => CategoryType::create([...]),
        'kompetensi' => CategoryType::create([...]),
    ];
}

private function createCustomStandardWithData(int $templateId): CustomStandard
{
    return CustomStandard::create([
        'template_id' => $templateId,
        'category_weights' => ['potensi' => 60, 'kompetensi' => 40],
        'aspect_configs' => [...],
        'sub_aspect_configs' => [...],
    ]);
}
```

---

## ⚠️ Common Pitfalls

### 1. Testing Aspects Without Sub-Aspects

```php
// ❌ WRONG
$aspectAssessment->update(['standard_rating' => 4.0]);

// ✅ CORRECT
$aspect->update(['standard_rating' => 4.0]);
// Service reads from Aspect model!
```

### 2. Forgetting to Setup All 3 Layers

```php
// Always consider what layer you're testing
// Layer 1: Session - use $service->saveAspectWeight()
// Layer 2: Custom - create CustomStandard + Session::put()
// Layer 3: Quantum - create Aspect/SubAspect in DB
```

### 3. Not Using RefreshDatabase

```php
class MyServiceTest extends TestCase
{
    use RefreshDatabase; // ← REQUIRED!
}
```

---

## 📚 Key Files to Read

Before writing tests, understand the architecture:

1. **`app/Services/DynamicStandardService.php`** (1185 lines)
   - Priority chain implementation
   - Session management
   - Data-driven rating calculation

2. **`app/Services/IndividualAssessmentService.php`** (723 lines)
   - Uses DynamicStandardService
   - Tolerance application
   - Assessment calculations

3. **`app/Services/CustomStandardService.php`** (334 lines)
   - CRUD operations
   - Session selection
   - Template defaults

4. **`docs/CUSTOM_STANDARD_FEATURE.md`** (1140 lines)
   - Complete architecture documentation
   - Priority chain explanation
   - Event system flow

5. **`docs/TESTING_GUIDE.md`** (this file)
   - Testing strategy
   - Code examples
   - Best practices

---

## 📊 Test Coverage Goals

### Service Tests (Unit)

| Service | Current | Target | Priority | Est. Duration | Notes |
|---------|---------|--------|----------|---------------|-------|
| DynamicStandardService | 0/50 | 80% | ⭐⭐⭐ | 2-3 days | Core system |
| IndividualAssessmentService | 14/70 | 80% | ⭐⭐⭐ | 2-3 days | Main calculation |
| CustomStandardService | 0/20 | 70% | ⭐⭐ | 1 day | Persistent standard |
| RankingService | 0/40 | 70% | ⭐⭐ | 1-2 days | Ranking logic |
| ConclusionService | 0/15 | 60% | ⭐ | 0.5 day | Conclusion text |
| TrainingRecommendationService | 0/25 | 60% | ⭐ | 1 day | Used in 1 Livewire only |
| StatisticService | 0/20 | 60% | ⭐ | 1 day | Used in 1 Livewire only |

**Total Service Tests**: ~240 tests (14 done, 226 remaining)

**Note**:
- `AssessmentService` tidak perlu test (auto-generate dari seeder)
- `TrainingRecommendationService` & `StatisticService` bisa di-cover via Livewire tests (lower priority)

### Livewire Tests (Feature)

| Component | Target | Priority | Est. Duration |
|-----------|--------|----------|---------------|
| GeneralPsyMapping | 10-15 | ⭐⭐ | 0.5 day |
| GeneralMcMapping | 10-15 | ⭐⭐ | 0.5 day |
| GeneralMatching | 8-10 | ⭐⭐ | 0.5 day |
| RankingPsyMapping | 8-10 | ⭐ | 0.5 day |
| StandardPsikometrik | 10-12 | ⭐ | 0.5 day |

**Total Livewire Tests**: ~60 tests (0 done, 60 remaining)

### Overall Target

- **Total Tests**: ~300 tests (240 service + 60 Livewire)
- **Current**: 14 tests (5%)
- **Core Services**: 180 tests (focus here first)
- **Optional Services**: 60 tests (lower priority)
- **Target Coverage**: 70-80%
- **Est. Completion**: 8-12 days (core only), 12-16 days (full coverage)

---

## 📝 Quick Reference Cheat Sheet

### Running Tests

```bash
# All tests
php artisan test

# Specific file
php artisan test tests/Unit/Services/DynamicStandardServiceTest.php

# Specific test
php artisan test --filter=test_returns_session_adjustment_when_exists

# With coverage
php artisan test --coverage

# Stop on first failure
php artisan test --stop-on-failure

# Parallel execution (faster)
php artisan test --parallel
```

### Common Test Patterns

```php
// Test priority chain
$service->saveAspectWeight($templateId, 'asp_01', 15); // Session
Session::put("selected_standard.{$templateId}", $customStandardId); // Custom
Aspect::create(['weight_percentage' => 10]); // Quantum

// Test data-driven rating
$aspect = Aspect::create(['code' => 'asp_kecerdasan']);
SubAspect::create(['aspect_id' => $aspect->id, 'standard_rating' => 3]);
SubAspect::create(['aspect_id' => $aspect->id, 'standard_rating' => 4]);
SubAspect::create(['aspect_id' => $aspect->id, 'standard_rating' => 5]);
// Expected rating: (3+4+5)/3 = 4.0

// Test Livewire events
Livewire::test(GeneralPsyMapping::class)
    ->dispatch('tolerance-updated', tolerance: 10)
    ->assertDispatched('chartDataUpdated')
    ->assertSet('tolerancePercentage', 10);
```

### Debug Tests

```php
// Dump and die in test
dd($result);

// Dump without stopping
dump($result);

// Print to console
fwrite(STDOUT, print_r($result, true));

// Laravel telescope (if installed)
ray($result);
```

---

## 🎯 Next Steps

**Immediate Actions:**

1. ✅ Read [CUSTOM_STANDARD_FEATURE.md](./CUSTOM_STANDARD_FEATURE.md)
2. ✅ Understand 3-layer priority system
3. ✅ Start with `DynamicStandardServiceTest.php`
4. ✅ Follow phased approach (Priority Chain → Data-Driven → etc.)
5. ✅ Run `vendor/bin/pint --dirty` after writing tests
6. ✅ Update this document with progress

**After DynamicStandardService Complete:**

1. Complete remaining IndividualAssessmentService methods
2. Test CustomStandardService
3. Test RankingService
4. Start Livewire integration tests

---

**Version**: 1.0
**Last Updated**: 2025-01-28
**Next Review**: After DynamicStandardService tests complete
**Maintainer**: Development Team
