# Testing Guide - SPSP Assessment System

> **Version**: 1.3
> **Last Updated**: 2025-01-28
> **Status**: 🚧 **IN DEVELOPMENT** - Tests uncover bugs in production code
> **Purpose**: Quick reference untuk testing strategy dengan PHPUnit

---

## ⚠️ Development Notice

**Project ini masih dalam tahap development**. Testing dilakukan untuk:
- ✅ **Validate business logic** - Memastikan logic sesuai requirements
- 🐛 **Discover bugs early** - Menemukan bug sebelum production
- 📚 **Document behavior** - Test sebagai dokumentasi hidup

**Expected Outcome**: Tests bisa mengungkap bugs di production code yang perlu diperbaiki!

---

## 🎯 Quick Start

### Current Status

| Service | Tests Done | Remaining | Priority | Status | Test File |
|---------|------------|-----------|----------|--------|-----------|
| **DynamicStandardService** | ✅ **52/52** | 0 | ⭐⭐⭐ | **✅ COMPLETE (100%)** | `tests/Unit/Services/DynamicStandardServiceTest.php` |
| **IndividualAssessmentService** | ✅ **69/69** | 0 | ⭐⭐⭐ | **✅ COMPLETE (100%)** | `tests/Unit/Services/IndividualAssessmentServiceTest.php` |
| **CustomStandardService** | ✅ **69/69** | 0 | ⭐⭐ | **✅ COMPLETE (100%)** | `tests/Unit/Services/CustomStandardServiceTest.php` |
| **RankingService** | 0/40 | 40 | ⭐⭐ | PENDING | `tests/Unit/Services/RankingServiceTest.php` |
| TrainingRecommendationService | 0/25 | 25 | ⭐ | OPTIONAL | Can be covered via Livewire tests |
| StatisticService | 0/20 | 20 | ⭐ | OPTIONAL | Can be covered via Livewire tests |

**Progress**: 190/227 tests (84%) 🎉 **+69 tests today!**

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
│       ├── DynamicStandardServiceTest.php            # ✅ COMPLETE (52/52)
│       ├── IndividualAssessmentServiceTest.php       # ✅ COMPLETE (69/69)
│       ├── CustomStandardServiceTest.php             # ✅ COMPLETE (69/69)
│       └── RankingServiceTest.php                    # ⏳ PENDING (0/40)
│
└── Feature/                       # Integration testing (SLOWER, realistic)
    └── Livewire/
        ├── IndividualReport/
        └── PositionMapping/
```

### Key Principles

1. **Unit Tests FIRST** (Services) - Test pure logic, isolated, fast
2. **Feature Tests AFTER** (Livewire) - Test integration, realistic workflows
3. **PHPUnit ONLY** - NOT Pest! Use `public function test_*(): void`
4. **Use Factories** - Always use factories for model creation
5. **RefreshDatabase** - Always use `RefreshDatabase` trait

---

## 🔄 Testing Strategy: Two Phases

### **Phase 1: Unit Tests (Services) - DO THIS FIRST** ⭐⭐⭐

**Target**: Test **pure business logic** in isolation

**Why First?**
- ⚡ **Fast** (milliseconds per test)
- 🎯 **Focused** (one service, one method)
- 🐛 **Find bugs early** (before UI integration)
- 📚 **Document behavior** (tests as specs)

**Services to Test** (in order):
1. ✅ **DynamicStandardService** (COMPLETE - 52/52 tests)
2. 🔥 **IndividualAssessmentService** (IN PROGRESS - 29/70 tests)
3. ⏳ **CustomStandardService** (PENDING - 0/20 tests)
4. ⏳ **RankingService** (PENDING - 0/40 tests)

**Example Focus Areas**:
- 3-layer priority chain (session → custom → quantum)
- Data-driven rating calculation (WITH/WITHOUT sub-aspects)
- Tolerance application
- Active/inactive filtering
- Gap calculation
- Weighted scores

### **Phase 2: Feature Tests (Livewire) - DO THIS AFTER** ⭐⭐

**Target**: Test **user workflows** and **UI integration**

**Why After?**
- Services already validated (fewer bugs)
- Focus on integration, not calculation logic
- Test real user journeys

**Components to Test**:
- Individual Assessment Report
- Position Mapping (General Psy)
- Ranking & Comparison
- Custom Standard Management

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

**Methods that use this**:
- `getAspectWeight()`, `getAspectRating()`, `getSubAspectRating()`
- `getCategoryWeight()`, `isAspectActive()`, `isSubAspectActive()`

### Data-Driven Rating

**WITH sub-aspects** (Potensi):
- Rating = Average dari ACTIVE sub-aspects
- Example: [3, 4, 5] → 4.0

**WITHOUT sub-aspects** (Kompetensi):
- Rating = Direct value dari aspect
- Example: 4.0 → 4.0

**⚠️ CRITICAL Testing Implication**:
```php
// ❌ WRONG for aspects without sub-aspects
$aspectAssessment->update(['standard_rating' => 4.0]);

// ✅ CORRECT - Service reads from Aspect model
$aspect->update(['standard_rating' => 4.0]);
```

---

## 📝 DynamicStandardService Tests (Priority #1)

**File**: `tests/Unit/Services/DynamicStandardServiceTest.php`
**Status**: ✅ **COMPLETE** - All 27 public methods tested
**Total Tests**: 52 tests (100% method coverage)

### Test Coverage by Phase

1. **Priority Chain Tests** (15 tests)
   - Session > Custom > Quantum for all getters
   - Test each layer independently
   - Test layer fallback behavior

2. **Data-Driven Rating Tests** (10 tests)
   - Aspects WITH sub-aspects (average calculation)
   - Aspects WITHOUT sub-aspects (direct value)
   - Empty sub-aspects handling

3. **Session Management Tests** (8 tests)
   - Save adjustments
   - Clear adjustments
   - Only save if different from baseline

4. **Active/Inactive Tests** (10 tests)
   - Default to active
   - Set inactive → weight becomes 0
   - Inactive sub-aspects excluded from average

5. **Validation Tests** (9 tests)
   - Category weights sum to 100
   - Rating range 1-5
   - Weight range 0-100

**Key Learnings**:
- ALL tests passing ✅
- Production code working correctly
- Test patterns reusable for other services

---

## 📝 IndividualAssessmentService Tests (Priority #2)

**File**: `tests/Unit/Services/IndividualAssessmentServiceTest.php`
**Status**: ✅ **COMPLETE** (100% done)
**Total Tests**: 69/69 tests

### Test Coverage Summary

#### ✅ PHASE 1-6: Basic Tests (14 tests) - COMPLETE
- Service instantiation (1)
- Data loading with factories (1)
- Data-driven calculation (2)
- Tolerance application (3)
- Column validation (3)
- Matching percentage (4)

#### ✅ PHASE 7: getCategoryAssessment() (15 tests) - COMPLETE
1. Aggregates aspect scores correctly
2. Applies category weight to totals
3. Excludes inactive aspects from category totals
4. Calculates category gaps correctly
5. Applies tolerance to category totals
6. Returns correct overall conclusion
7. Category assessment has all required keys
8. Category assessment data types
9. Category assessment with single aspect
10. Category assessment with potensi sub aspects
11. Category assessment rounds correctly
12. Calculates weighted gap correctly
13. Throws exception for nonexistent category
14. Category assessment with different tolerances
15. Helper: `createCategoryWithMultipleAspects()`

#### ✅ PHASE 8: getFinalAssessment() (14 tests) - COMPLETE
- Combines Potensi + Kompetensi
- Calculates final weighted scores
- Achievement percentage calculation
- Gap-based conclusion logic
- Tolerance application
- All required keys & data types
- Helper: `createCompleteAssessmentWithBothCategories()`

#### ✅ PHASE 9: getPassingSummary() (5 tests) - COMPLETE
- Count passing aspects correctly
- Calculate passing percentage
- Handle all passing / none passing scenarios
- Empty collection handling

#### ✅ PHASE 10: Matching Methods (12 tests) - COMPLETE
- getAspectMatchingData() collection structure
- calculateMatchingPercentage() logic (100% when exceeds, proportional when below)
- Data-driven matching with sub-aspects
- Inactive sub-aspects exclusion
- Zero standard handling
- Matching percentage rounding
- getAllAspectMatchingData() batch loading
- Required keys validation

#### ✅ PHASE 11: getJobMatchingPercentage() (9 tests) - COMPLETE
- Overall job matching average calculation
- Potensi percentage calculation
- Kompetensi percentage calculation
- Perfect match (100%) scenarios
- Percentage rounding
- Participant object/ID acceptance
- Batch loading optimization
- Empty aspects handling

**Result**: ✅ **All public methods fully tested with edge cases and bug discovery**

---

## 📝 CustomStandardService Tests (Priority #3)

**File**: `tests/Unit/Services/CustomStandardServiceTest.php`
**Status**: ✅ **COMPLETE** (100% done)
**Total Tests**: 69/69 tests
**Coverage**: All 20 public methods fully tested

### Test Coverage Summary

#### ✅ PHASE 1: Service Initialization (1 test) - COMPLETE
- Service instantiation

#### ✅ PHASE 2: Query Methods (10 tests) - COMPLETE
1. getForInstitution() - filters by institution & template, active only, ordered by name
2. getAllForInstitution() - all templates for institution, eager loads relationships
3. getAvailableTemplatesForInstitution() - only templates used in events, distinct, ordered

#### ✅ PHASE 3: CRUD Operations (12 tests) - COMPLETE
1. create() - stores all data, uses auth()->id() fallback, handles null description
2. update() - modifies data, keeps original values when not provided, returns fresh instance
3. delete() - removes standard, returns false when fails (🐛 **BUG FIXED**: null → false coercion)
4. JSON field casting validation

#### ✅ PHASE 4: Template Defaults (8 tests) - COMPLETE
1. getTemplateDefaults() - returns all required keys
2. **DATA-DRIVEN logic**: adds rating ONLY for aspects WITHOUT sub-aspects
3. Includes category weights, aspect weights & active status
4. Includes sub-aspect ratings & active status
5. Eager loads relationships
6. Handles empty templates, throws exception for nonexistent template

#### ✅ PHASE 5: Session Management (12 tests) - COMPLETE
1. select() - stores in session, can store null, clears adjustments, allows switching
2. getSelected() - retrieves from session, returns null when no selection
3. getSelectedStandard() - returns model, handles null/nonexistent
4. clearSelection() - removes from session, also clears adjustments
5. Session keys are template-specific

#### ✅ PHASE 6: Getter Methods (15 tests) - COMPLETE
1. getAspectWeight() - returns weight, null for nonexistent
2. getAspectRating() - returns as float, null when no rating field
3. getSubAspectRating() - returns rating, null for nonexistent
4. getCategoryWeight() - returns weight, null for nonexistent
5. isAspectActive() - returns status, defaults to true
6. isSubAspectActive() - returns status, defaults to true

#### ✅ PHASE 7: Validation Methods (8 tests) - COMPLETE
1. validate() - category weights sum to 100%
2. Rating range validation (1-5) for aspects & sub-aspects
3. Accepts valid boundaries, passes when no rating field
4. Returns empty array for empty data

#### ✅ PHASE 8: Code Uniqueness (5 tests) - COMPLETE
1. isCodeUnique() - returns true/false correctly
2. Scoped to institution (same code allowed in different institutions)
3. Excludes current standard when updating
4. Detects duplicates correctly

### Bugs Discovered & Fixed

🐛 **Bug #1**: `is_active` default value was NULL instead of TRUE
- **Fix**: Added `protected $attributes = ['is_active' => true]` in CustomStandard model

🐛 **Bug #2**: `delete()` method returned NULL when deleting already-deleted model
- **Fix**: Changed `return $customStandard->delete();` to `return $customStandard->delete() ?: false;`

🐛 **Bug #3**: AssessmentEvent factory used invalid enum value ('active' instead of 'ongoing')
- **Fix**: Updated factory to use correct enum values

### Helper Methods Created
- `makeStandardData()` - Creates test data array for CustomStandard
- `createTemplateWithCategories()` - Creates complete template with categories, aspects, sub-aspects

### Factories Created
- InstitutionFactory ✅
- AssessmentTemplateFactory ✅
- CategoryTypeFactory ✅
- AspectFactory ✅
- SubAspectFactory ✅
- AssessmentEventFactory ✅
- PositionFormationFactory ✅

**Result**: ✅ **All public methods fully tested with comprehensive coverage including edge cases, data-driven logic, and bug discovery**

---

## 🧪 Test Conventions

### PHPUnit Style (NOT Pest)

**IMPORTANT**: Proyek ini menggunakan **PHPUnit**, BUKAN Pest!

```php
// ✅ CORRECT: PHPUnit
public function test_descriptive_name_in_snake_case(): void
{
    // Arrange
    $aspect = Aspect::create([...]);

    // Act
    $result = $service->getAspectWeight(1, 'asp_01');

    // Assert
    $this->assertEquals(10, $result);
}

// ❌ WRONG: Pest syntax
test('descriptive name', function () { ... });
it('does something', function () { ... });
```

### Test Naming Convention

```php
// Pattern: test_{what}_{condition}_{expected}
test_returns_quantum_default_when_no_adjustments()
test_calculates_aspect_rating_from_sub_aspects()
test_excludes_inactive_aspects_from_category_totals()
test_throws_exception_for_nonexistent_category()
```

### Data Setup Best Practices

```php
// ✅ GOOD: Use factories
$participant = Participant::factory()->create([
    'event_id' => $event->id,
]);

// ✅ GOOD: Explicit values in tests
$aspect->update(['standard_rating' => 3.0]);
$this->assertEquals(3.0, $service->getAspectRating(1, 'asp_01'));

// ❌ BAD: Magic numbers without context
$this->assertEquals(42, $result); // What is 42?

// ✅ GOOD: Calculated with comments
// (3 + 4 + 5) / 3 = 4.0
$this->assertEquals(4.0, $result);
```

---

## 🏃 Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Unit/Services/DynamicStandardServiceTest.php

# Run specific test method
php artisan test --filter=test_returns_session_adjustment_first

# Run with coverage (if xdebug installed)
php artisan test --coverage
```

---

## ⚠️ Common Pitfalls

### 1. Testing Aspects Without Sub-Aspects

```php
// ❌ WRONG: Updating AspectAssessment for aspects without sub-aspects
$aspectAssessment->update(['standard_rating' => 4.0]);
// This won't work! Service reads from Aspect model.

// ✅ CORRECT: Update the Aspect model
$aspect->update(['standard_rating' => 4.0]);
// Service uses DynamicStandardService to get rating
```

### 2. Forgetting to Setup All 3 Layers

When testing priority chain, make sure to test:
1. Session adjustment (highest priority)
2. Custom standard (middle priority)
3. Quantum default (fallback)

### 3. Not Using RefreshDatabase

```php
// ✅ ALWAYS include this trait
class MyServiceTest extends TestCase
{
    use RefreshDatabase;

    // ...
}
```

---

## 📚 Key Files to Read

Before writing tests, read these files:

1. **Service being tested**
   - `app/Services/DynamicStandardService.php`
   - `app/Services/IndividualAssessmentService.php`

2. **Related models**
   - `app/Models/Aspect.php`
   - `app/Models/SubAspect.php`
   - `app/Models/CustomStandard.php`

3. **Existing tests** (for patterns)
   - `tests/Unit/Services/DynamicStandardServiceTest.php`
   - `tests/Unit/Services/IndividualAssessmentServiceTest.php`

4. **Architecture docs**
   - `docs/ASSESSMENT_CALCULATION_FLOW.md`
   - `docs/TESTING_STRATEGY.md`

---

## 📝 Quick Reference Cheat Sheet

### Running Tests

```bash
# All tests
php artisan test

# Specific file
php artisan test tests/Unit/Services/DynamicStandardServiceTest.php

# Filter by name
php artisan test --filter=test_priority_chain
```

### Common Test Patterns

```php
// Arrange-Act-Assert
public function test_example(): void
{
    // Arrange: Setup test data
    $aspect = Aspect::create([...]);

    // Act: Execute method
    $result = $service->getAspectWeight(1, 'asp_01');

    // Assert: Verify result
    $this->assertEquals(10, $result);
}

// Testing exceptions
public function test_throws_exception(): void
{
    $this->expectException(ModelNotFoundException::class);
    $service->getAspectWeight(999, 'invalid');
}

// Testing collections
$this->assertInstanceOf(Collection::class, $result);
$this->assertCount(5, $result);
$this->assertEquals('asp_01', $result->first()['code']);
```

### Debug Tests

```bash
# Run with verbose output
php artisan test --verbose

# Stop on first failure
php artisan test --stop-on-failure

# Show detailed error messages
php artisan test --display-errors
```

---

## 🎯 Next Steps

### Current Priority

1. ⭐⭐ **Test RankingService** (0/40 remaining) - **NEXT**
2. ⭐ **Test ConclusionService** (0/15 remaining)
3. ⭐ **Test TrainingRecommendationService** (0/25 remaining) - OPTIONAL
4. ⭐ **Test StatisticService** (0/20 remaining) - OPTIONAL

---

**Version**: 1.4
**Last Updated**: 2025-12-01
**Next Review**: After RankingService tests complete
**Maintainer**: Development Team
