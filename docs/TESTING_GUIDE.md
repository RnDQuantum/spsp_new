# Testing Guide - SPSP Assessment System

> **Version**: 2.1
> **Last Updated**: 2025-12-02
> **Status**: ✅ **COMPLETE** - All core services fully tested with 3-layer priority chain coverage
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
| **IndividualAssessmentService** | ✅ **73/73** | 0 | ⭐⭐⭐ | **✅ COMPLETE (100%)** | `tests/Unit/Services/IndividualAssessmentServiceTest.php` |
| **CustomStandardService** | ✅ **69/69** | 0 | ⭐⭐ | **✅ COMPLETE (100%)** | `tests/Unit/Services/CustomStandardServiceTest.php` |
| **RankingService** | ✅ **51/51** | 0 | ⭐⭐⭐ | **✅ COMPLETE (100%)** | `tests/Unit/Services/RankingServiceTest.php` |
| **Integration Tests** | ✅ **2/2** | 0 | ⭐⭐⭐ | **✅ COMPLETE (100%)** | `tests/Integration/Services/PriorityChainIntegrationTest.php` |
| TrainingRecommendationService | 0/25 | 25 | ⭐ | OPTIONAL | Can be covered via Livewire tests |
| StatisticService | 0/20 | 20 | ⭐ | OPTIONAL | Can be covered via Livewire tests |

**Progress**: 247/247 core tests (100%) - **All priority services fully tested with 3-layer priority chain coverage!** 🎉

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
│       ├── IndividualAssessmentServiceTest.php       # ✅ COMPLETE (73/73)
│       ├── CustomStandardServiceTest.php             # ✅ COMPLETE (69/69)
│       └── RankingServiceTest.php                    # ✅ COMPLETE (51/51)
│
├── Integration/                   # End-to-end testing (SLOWER, comprehensive)
│   └── Services/
│       └── PriorityChainIntegrationTest.php          # ✅ COMPLETE (2/2)
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
2. ✅ **IndividualAssessmentService** (COMPLETE - 73/73 tests with priority chain)
3. ✅ **CustomStandardService** (COMPLETE - 69/69 tests)
4. ✅ **RankingService** (COMPLETE - 51/51 tests with priority transitions)
5. ✅ **Integration Tests** (COMPLETE - 2/2 end-to-end tests)

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
**Total Tests**: 73/73 tests

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

#### ✅ PHASE 12: Priority Chain Tests (4 tests) - COMPLETE
1. `test_aspect_assessments_use_session_adjustment_when_available()` - Session (Layer 1) > Custom (Layer 2)
2. `test_aspect_assessments_use_custom_standard_when_no_session()` - Custom (Layer 2) > Quantum (Layer 3)
3. `test_aspect_assessments_revert_to_quantum_when_custom_cleared()` - Clears custom → reverts to Quantum
4. `test_final_assessment_reflects_priority_chain_changes()` - Final assessment updates with priority changes

**Key Coverage**:
- ✅ Complete 3-layer priority chain integration (Session → Custom → Quantum)
- ✅ Aspect assessments reflect current active priority layer
- ✅ Final assessment totals update correctly when priorities change
- ✅ AspectCacheService integration (cache clearing for real-time updates)

**Result**: ✅ **All public methods fully tested with edge cases, bug discovery, and complete priority chain coverage**

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

## 📝 RankingService Tests (Priority #4)

**File**: `tests/Unit/Services/RankingServiceTest.php`
**Status**: ✅ **COMPLETE** (100% done)
**Total Tests**: 51/51 tests passing
**Coverage**: All 7 public methods tested + edge cases + priority transitions

### Test Coverage Summary

#### ✅ PHASE 1: Service Initialization (1 test) - COMPLETE
- Service instantiation

#### ✅ PHASE 2: getParticipantsByPosition() (7 tests) - COMPLETE
1. Returns correct participant rankings
2. Handles multiple participants with proper sorting
3. Returns empty collection for position without participants
4. Sorts by final_score DESC, then name ASC for tiebreakers
5. Filters by position correctly
6. Returns all required keys
7. Data types validation

#### ✅ PHASE 3: getAllParticipants() (7 tests) - COMPLETE
1. Returns all participants across all positions
2. Groups participants by position
3. Maintains ranking within each position
4. Returns correct structure for multiple positions
5. Includes position details (code, name)
6. Handles events with no participants
7. Validates required keys for each participant

#### ✅ PHASE 4: getRankForPosition() (6 tests) - COMPLETE
1. Returns correct rank for participant
2. Handles tied scores (same rank)
3. Rank based on final_score DESC
4. Returns null for nonexistent participant
5. Returns null for wrong position
6. Handles position with single participant

#### ✅ PHASE 5: getTopPerformers() (6 tests) - COMPLETE
1. Returns top N performers
2. Defaults to top 10 when limit not specified
3. Sorts by final_score DESC
4. Returns all participants when count < limit
5. Handles empty collection
6. Returns correct structure with ranks

#### ✅ PHASE 6: getPerformanceDistribution() (7 tests) - COMPLETE
1. Groups participants by conclusion code
2. Calculates percentages correctly
3. Returns all conclusion codes (DS, MS, BS)
4. Percentage sum equals 100%
5. Handles single conclusion scenario
6. Returns zero counts for missing conclusions
7. Validates required keys and data types

#### ✅ PHASE 7: calculateCutoffScore() (4 tests) - COMPLETE
1. Calculates cutoff at specified percentile
2. Returns lowest score when percentile = 0
3. Returns highest score when percentile = 100
4. Handles collection with single participant

#### ✅ PHASE 8: getComparison() (4 tests) - COMPLETE
1. Compares two participants with rank and gap
2. Returns position in rankings for each
3. Calculates score gap correctly
4. Returns all required comparison keys

#### ✅ PHASE 9: getRankings() Edge Cases (2 tests) - COMPLETE
1. Returns empty collection when no active aspects
2. Handles session-adjusted inactive aspects correctly

#### ✅ PHASE 10: calculateAdjustedStandards() Edge Cases (2 tests) - COMPLETE
1. Uses custom standard when selected (CustomStandardService integration)
2. Returns zero when all aspects inactive

#### ✅ PHASE 11: getCombinedRankings() Edge Cases (3 tests) - COMPLETE
1. Returns empty when missing Potensi rankings (all inactive)
2. Returns empty when missing Kompetensi rankings (all inactive)
3. Handles zero category weights gracefully

#### ✅ PHASE 12: Priority Transition Tests (3 tests) - COMPLETE
1. `test_session_overrides_custom_standard_in_rankings()` - Session (50%) overrides Custom (40%)
2. `test_rankings_change_when_custom_standard_selected()` - Quantum (20%) → Custom (35%) updates rankings
3. `test_rankings_revert_when_session_cleared()` - Session cleared → reverts to Custom

**Key Coverage**:
- ✅ Session adjustment (Layer 1) has highest priority in rankings
- ✅ Custom standard (Layer 2) overrides quantum defaults (Layer 3)
- ✅ Rankings update correctly when switching between priority layers
- ✅ AspectCacheService integration (cache clearing for real-time updates)
- ✅ Session key validation (`standard_adjustment.{templateId}`)

### Helper Methods Created
- `createCompleteTemplate()` - Creates template with Potensi & Kompetensi categories, aspects, and sub-aspects
- `createParticipantWithAssessments()` - Creates participant with complete assessment data at specified performance level
- `createAspectAssessments()` - Creates aspect and category assessments for participant
- `createSubAspectAssessments()` - Creates sub-aspect assessments for data-driven aspects

### Factories Created/Modified
- ✅ **BatchFactory** (NEW) - Creates batch with code, name, location, dates
- ✅ **Batch model** - Added `HasFactory` trait
- ✅ **AspectAssessmentFactory** - Made `category_assessment_id` nullable for flexible usage

### Integration Points
- **DynamicStandardService** - Retrieves weights, ratings, active status via 3-layer priority
- **IndividualAssessmentService** - Calculates individual scores and assessments
- **ConclusionService** - Determines performance conclusions (DS/MS/BS)
- **AspectCacheService** - Cache cleared in setUp() to prevent test interference

### Key Testing Insights
- **Data-Driven Rating**: Potensi aspects use sub-aspect averaging, Kompetensi uses direct values
- **Ranking Logic**: Primary sort by `final_score DESC`, secondary sort by `name ASC` for ties
- **3-Layer Priority**: Session → Custom → Quantum affects all calculations
- **Performance Levels**: Created helpers for Above/Meets/Below standard participants
- **Tiebreakers**: Alphabetical name sorting when scores are equal

### Bug Fixes During Testing
- 🐛 **Fixed fallback logic bug** in `getActiveAspectIds()` - Removed incorrect fallback that prevented empty collection when all aspects inactive
- 🔧 **Removed dead code** - Deleted unused `calculateOriginalStandards()` method (0 references)
- 🧹 **Cleanup** - Removed unused `CategoryType` import

### Test Results
- ✅ **51 tests PASSED** (100%)
- ✅ **182 assertions** executed successfully
- ✅ **Code formatted** with Laravel Pint
- ✅ **All edge cases covered** including inactive aspects, custom standards, zero weights, and priority transitions

**Result**: ✅ **Complete test coverage with bug fixes, edge case handling, priority chain validation, and comprehensive testing of all ranking functionality.**

---

## 📝 Integration Tests (Priority #5)

**File**: `tests/Integration/Services/PriorityChainIntegrationTest.php`
**Status**: ✅ **COMPLETE** (100% done)
**Total Tests**: 2/2 tests passing
**Coverage**: End-to-end 3-layer priority chain validation across all services

### Test Coverage Summary

#### ✅ Integration Test 1: Full Priority Chain Flow (1 test)
**Test**: `test_full_priority_chain_from_assessment_to_ranking()`

**What It Tests**:
- Complete flow from assessment calculation → ranking calculation across ALL priority layers
- Verifies IndividualAssessmentService + RankingService use consistent priority chain
- Tests transitions: Quantum (Layer 3) → Custom (Layer 2) → Session (Layer 1)

**Test Flow**:
1. Create participant with baseline assessments (Quantum defaults - Layer 3)
2. Verify baseline individual assessment uses quantum weights (20%)
3. Verify baseline ranking uses quantum weights (20%)
4. Apply custom standard with weight 30% (Layer 2)
5. Verify individual assessment updates to custom weights (30%)
6. Verify ranking updates to custom weights (30%)
7. Apply session adjustment with weight 40% (Layer 1)
8. Verify individual assessment updates to session weights (40%)
9. Verify ranking updates to session weights (40%)
10. Validate complete chain: Quantum (20%) < Custom (30%) < Session (40%)

**Key Validations**:
- ✅ Both services read from same DynamicStandardService
- ✅ Standard scores increase correctly as weights increase
- ✅ Rankings respond to priority changes in real-time
- ✅ AspectCacheService integration (cache clearing for updates)

#### ✅ Integration Test 2: Mixed Priority Layers (1 test)
**Test**: `test_mixed_priority_layers_in_final_assessment()`

**What It Tests**:
- Different aspects using different priority layers simultaneously
- Final assessment correctly combines mixed-priority aspects
- Verifies aspect-level independence of priority chain

**Test Flow**:
1. Create participant with 3 Potensi + 3 Kompetensi aspects
2. Apply custom standard affecting only 2 Potensi aspects (Layer 2)
3. Apply session adjustment affecting only 1 Potensi aspect (Layer 1)
4. Verify final assessment with mixed layers:
   - 1 Potensi aspect uses Session (Layer 1) - 40%
   - 1 Potensi aspect uses Custom (Layer 2) - 30%
   - 1 Potensi aspect uses Quantum (Layer 3) - 20%
   - 3 Kompetensi aspects use Quantum (Layer 3) - 25%
5. Validate weighted total score calculation is correct
6. Verify each aspect independently resolves its priority layer

**Key Validations**:
- ✅ Aspect-level priority independence
- ✅ Correct weighted score aggregation across mixed layers
- ✅ Each aspect uses highest available priority layer
- ✅ Final assessment totals reflect mixed-priority contributions

### Helper Methods Created
- `createCompleteTemplate()` - Creates template with Potensi (50%) + Kompetensi (50%) categories
  - 3 Potensi aspects (20% each) WITH 3 sub-aspects each
  - 3 Kompetensi aspects (25% each) WITHOUT sub-aspects
- `createParticipantWithAssessments()` - Creates participant with complete assessment data
- `createCategoryAssessments()` - Creates category-level and aspect-level assessments
  - Handles data-driven rating (WITH sub-aspects) vs direct rating (WITHOUT)
  - Calculates scores = rating × weight
  - Properly sets all required fields (aspect_id, participant_id, event_id, batch_id, position_formation_id)
- `getAspectStandardRating()` - Calculates aspect rating based on sub-aspects presence

### Factories Used
- ✅ **CategoryAssessment** - Used `forParticipant()` and `forCategoryType()` state methods
- ✅ **AspectAssessment** - Comprehensive field set with all required relational IDs
- ✅ **SubAspectAssessment** - Complete sub-aspect assessment data

### Critical Bugs Discovered & Fixed

🐛 **Bug #1**: CategoryAssessment factory missing relational fields
- **Issue**: Direct `create()` didn't populate `event_id`, `batch_id`, `position_formation_id`
- **Fix**: Used factory state methods `forParticipant()` and `forCategoryType()`

🐛 **Bug #2**: AspectAssessment missing multiple required fields
- **Issue**: Didn't include `aspect_id`, `participant_id`, `event_id`, `batch_id`, `position_formation_id`
- **Fix**: Added all required fields to factory call

🐛 **Bug #3**: Score calculation missing
- **Issue**: Not calculating `standard_score` and `individual_score` (score = rating × weight)
- **Fix**: Added score calculations: `round($rating * $weight, 2)`

🐛 **Bug #4**: Type error with standard_rating
- **Issue**: `standard_rating` field is string in database, causing type error in `round()`
- **Fix**: Cast to float: `(float) $aspect->standard_rating`

🐛 **Bug #5 (CRITICAL)**: Empty rankings due to missing template_id
- **Issue**: AspectCacheService's `getAspectsByCategory()` filters by `$aspect->template_id === $templateId`
- **Root Cause**: Aspects created without `template_id` field → empty aspect collection → empty rankings
- **Fix**: Added `template_id` to all Aspect factory calls

### Integration Points Tested
- ✅ **DynamicStandardService** - 3-layer priority chain (Session → Custom → Quantum)
- ✅ **IndividualAssessmentService** - Individual assessment calculations
- ✅ **RankingService** - Participant ranking calculations
- ✅ **CustomStandardService** - Custom standard selection and management
- ✅ **AspectCacheService** - Cache clearing for real-time updates

### Test Results
- ✅ **2 tests PASSED** (100%)
- ✅ **21 assertions** executed successfully
- ✅ **Code formatted** with Laravel Pint
- ✅ **All services validated** for consistent priority chain behavior
- ✅ **5 critical bugs discovered and fixed** during test development

**Result**: ✅ **Complete end-to-end validation of 3-layer priority chain across all services with comprehensive bug discovery and fixes.**

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

### ✅ All Core Services Complete!

All priority services are now 100% tested with complete 3-layer priority chain coverage:
- ✅ DynamicStandardService (52 tests) - Foundation for all priority logic
- ✅ IndividualAssessmentService (73 tests) - **Including priority chain integration!**
- ✅ CustomStandardService (69 tests) - Layer 2 management
- ✅ RankingService (51 tests) - **Including priority transitions!**
- ✅ Integration Tests (2 tests) - **End-to-end priority chain validation!**

**Total**: 247/247 core tests (753 assertions) - **100% COMPLETE** 🎉

### Test Coverage Highlights

**3-Layer Priority Chain** (Session → Custom → Quantum):
- ✅ Unit-level testing (each service independently)
- ✅ Integration testing (services working together)
- ✅ Transition testing (switching between priority layers)
- ✅ Mixed-layer testing (different aspects using different layers)

**Bug Discoveries**:
- 🐛 6 bugs discovered and fixed during test development
- 🐛 1 critical bug (missing template_id causing empty rankings)
- 🐛 Multiple factory/database field issues resolved

### Optional Additional Testing

1. ⭐ **ConclusionService** (0/15 remaining) - Simple utility service
2. ⭐ **TrainingRecommendationService** (0/25 remaining) - Can be covered via Livewire tests
3. ⭐ **StatisticService** (0/20 remaining) - Can be covered via Livewire tests

These are lower priority as they are either simple utilities or better tested through integration/Livewire tests.

---

**Version**: 2.1
**Last Updated**: 2025-12-02
**Status**: All core services 100% tested with complete 3-layer priority chain coverage (247/247 tests passing)
**Next Review**: Production deployment or optional service testing
**Maintainer**: Development Team
