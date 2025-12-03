# Livewire Testing Guide - SPSP Assessment System

> **Version**: 1.3
> **Last Updated**: 2025-12-03
> **Status**: 🚧 **IN PROGRESS** - 11/45 tests complete (24.4%)
> **Purpose**: Testing strategy untuk Livewire components dengan PHPUnit
>
> **Changes in v1.3**: ✅ SelectiveAspectsModal complete (11 tests, 46 assertions)

---

## 📋 Prerequisites

**Service Layer Testing**: ✅ **COMPLETE** (247/247 tests)
- All core services fully tested with 3-layer priority chain coverage
- See [TESTING_GUIDE.md](TESTING_GUIDE.md) for service testing details

**Now Ready For**: Livewire component feature testing

### Critical Context from Service Layer

**3-Layer Priority System** (MUST understand for Livewire tests):
```
Session Adjustment (Layer 1 - temporary, logout → hilang)
         ↓ if not found
Custom Standard (Layer 2 - persistent, saved to DB)
         ↓ if not found
Quantum Default (Layer 3 - from aspects/sub_aspects table)
```

**Key Services Used by Livewire Components**:
- `DynamicStandardService` - Manages 3-layer priority chain for all getters
- `CustomStandardService` - CRUD for custom standards (Layer 2)
- `IndividualAssessmentService` - Calculate individual participant assessments
- `RankingService` - Calculate rankings across participants
- `TrainingRecommendationService` - Generate training recommendations
- `StatisticService` - Statistical distribution analysis

**Testing Philosophy**:
- ✅ Use **PHPUnit** (NOT Pest!)
- ✅ Always use `RefreshDatabase` trait
- ✅ Always use **Factories** for model creation
- ✅ Test method naming: `test_{what}_{condition}_{expected}`
- ✅ Follow Arrange-Act-Assert pattern

---

## 💡 How to Use This Guide

### For New Chat Sessions

**Quick Start Prompt** (copy-paste this to Claude):
```
I need to implement Livewire component tests for the SPSP Assessment System.
Please read docs/LIVEWIRE_TESTING_GUIDE.md for the testing strategy and implementation plan.

Start with Phase 1, Component: [ComponentName]
```

**What This Guide Contains**:
- ✅ **Component Structure** - All Livewire components with priorities
- ✅ **Test Strategy** - 3 phases with implementation order
- ✅ **Code Examples** - Copy-paste patterns for each test type
- ✅ **Critical Context** - 3-layer priority system, service integration
- ✅ **Common Pitfalls** - What to avoid and how to fix
- ✅ **Quick Reference** - Cheat sheet for Livewire testing

**Recommended Reading Order**:
1. Read "Critical Context from Service Layer" section (MUST understand 3-layer priority)
2. Review "Component Structure Overview" to see what you're testing
3. Check "Testing Strategy" for implementation order
4. Use "Test Conventions" section for code patterns
5. Reference "Common Pitfalls" when stuck

---

## 🎯 Quick Start

### Current Status

| Component Category | Tests Done | Priority | Status |
|-------------------|------------|----------|--------|
| **Dynamic Standard Editing** | 11/12 | ⭐⭐⭐ | 🚧 IN PROGRESS |
| **Custom Standard Management** | 0/9 | ⭐⭐⭐ | PENDING |
| **Selector Components** | 0/6 | ⭐⭐⭐ | PENDING |
| **General Reports** | 0/6 | ⭐⭐ | PENDING |
| **Individual Reports** | 0/9 | ⭐⭐ | PENDING |
| **Helper Components** | 0/3 | ⭐ | PENDING |

**Progress**: 11/45 component tests (24.4%)

**Latest**: ✅ SelectiveAspectsModal (11 tests, 46 assertions) - 2025-12-03

---

## 📁 Component Structure Overview

```
app/Livewire/
├── Pages/
│   ├── GeneralReport/
│   │   ├── StandardPsikometrik.php        # ⭐⭐⭐ Session edit (Potensi)
│   │   ├── StandardMc.php                 # ⭐⭐⭐ Session edit (Kompetensi)
│   │   ├── Statistic.php                  # ⭐⭐ Uses StatisticService
│   │   ├── Training/
│   │   │   └── TrainingRecommendation.php # ⭐⭐ Uses TrainingRecommendationService
│   │   └── Ranking/
│   │       ├── RankingPsyMapping.php      # ⭐⭐ Uses RankingService (Potensi)
│   │       ├── RankingMcMapping.php       # ⭐⭐ Uses RankingService (Kompetensi)
│   │       └── RekapRankingAssessment.php # ⭐⭐ Uses RankingService (Combined)
│   │
│   ├── CustomStandards/
│   │   ├── Index.php                      # ⭐⭐⭐ List & delete custom standards
│   │   ├── Create.php                     # ⭐⭐⭐ Create custom standard
│   │   └── Edit.php                       # ⭐⭐⭐ Edit custom standard
│   │
│   └── IndividualReport/
│       ├── RingkasanAssessment.php        # ⭐⭐ Uses IndividualAssessmentService
│       ├── GeneralMapping.php             # ⭐⭐ Uses IndividualAssessmentService
│       ├── GeneralPsyMapping.php          # ⭐⭐ Uses IndividualAssessmentService (Potensi)
│       ├── GeneralMcMapping.php           # ⭐⭐ Uses IndividualAssessmentService (Kompetensi)
│       ├── GeneralMatching.php            # ⭐⭐ Job matching percentages
│       ├── RingkasanMcMapping.php         # ⭐ Report component
│       ├── SpiderPlot.php                 # ⭐ Visualization
│       ├── InterpretationSection.php      # ⭐ Display component
│       └── FinalReport.php                # ⭐ PDF export
│
└── Components/
    ├── EventSelector.php                  # ⭐⭐⭐ Core selector (event selection)
    ├── PositionSelector.php               # ⭐⭐⭐ Core selector (position selection)
    ├── AspectSelector.php                 # ⭐⭐ Filter component (aspect selection)
    ├── ParticipantSelector.php            # ⭐⭐ Filter component (participant selection)
    ├── ToleranceSelector.php              # ⭐⭐ Filter component (tolerance adjustment)
    ├── SelectiveAspectsModal.php          # ⭐⭐⭐ Session adjustment modal (bulk edit)
    └── CategoryWeightEditor.php           # ⭐ Helper component (weight editor)
```

**Structure Notes:**
- `GeneralReport/` contains direct files: StandardPsikometrik, StandardMc, Statistic
- `GeneralReport/Training/` subfolder: TrainingRecommendation (uses TrainingRecommendationService)
- `GeneralReport/Ranking/` subfolder: 3 ranking components (all use RankingService)
- `IndividualReport/` contains individual participant reports
- `Components/` contains reusable components (selectors, modals, editors)

---

## 🔄 Testing Strategy

### Phase 1: Core Infrastructure (Priority ⭐⭐⭐)

**Test core functionality that all other components depend on**

#### 1.1 Dynamic Standard Editing Components (4 tests)

**StandardPsikometrik.php** - Session adjustment for Potensi category
- ✅ Component loads with correct initial state from session
- ✅ Category weight editing via modal (inline edit)
- ✅ Sub-aspect rating editing via modal (inline edit)
- ✅ Opens SelectiveAspectsModal for bulk editing
- ✅ Reset adjustments clears session and reverts to defaults
- ✅ Custom standard dropdown selection

**StandardMc.php** - Session adjustment for Kompetensi category
- ✅ Component loads with correct initial state from session
- ✅ Category weight editing via modal (inline edit)
- ✅ Aspect rating editing via modal (inline edit - Kompetensi)
- ✅ Opens SelectiveAspectsModal for bulk editing
- ✅ Reset adjustments clears session and reverts to defaults
- ✅ Custom standard dropdown selection

**Total**: 12 tests

#### 1.2 Custom Standard Management (9 tests)

**CustomStandards/Index.php** - List & Delete
- ✅ Displays custom standards for user's institution
- ✅ Confirms delete with modal
- ✅ Deletes custom standard successfully
- ✅ Authorization check (only own institution)

**CustomStandards/Create.php** - Create
- ✅ Loads template defaults when template selected
- ✅ Validates code uniqueness
- ✅ Creates custom standard with all fields
- ✅ Redirects to index after successful creation

**CustomStandards/Edit.php** - Edit
- ✅ Loads existing custom standard data
- ✅ Updates custom standard successfully
- ✅ Validates code uniqueness (excluding current)
- ✅ Authorization check

**Total**: 9 tests

#### 1.3 Selector Components (6 tests)

**EventSelector.php** - Event selection
- ✅ Loads events from database
- ✅ Persists selection to session
- ✅ Dispatches 'event-selected' event
- ✅ Loads default from session on mount

**PositionSelector.php** - Position selection
- ✅ Loads positions for selected event
- ✅ Resets when event changes
- ✅ Persists selection to session
- ✅ Dispatches 'position-selected' event

**SelectiveAspectsModal.php** - Bulk aspect/sub-aspect editing ✅ **COMPLETE**
- ✅ Opens modal with current session state (Potensi & Kompetensi)
- ✅ Select/deselect aspects
- ✅ Edit aspect weights
- ✅ Toggle sub-aspects (Potensi only)
- ✅ Validates total weight = 100%
- ✅ Validates minimum 3 active aspects
- ✅ Saves to session via DynamicStandardService
- ✅ Auto-distribute weights functionality
- ✅ Close modal without saving

**File**: `tests/Feature/Livewire/DynamicStandard/SelectiveAspectsModalTest.php`
**Tests**: 11 tests, 46 assertions
**Status**: ✅ All tests passing

**Total**: 11 tests (SelectiveAspectsModal complete, EventSelector & PositionSelector pending)

---

### Phase 2: Report Components (Priority ⭐⭐)

**Test components that use services for data display**

#### 2.1 General Reports (6 tests)

**TrainingRecommendation.php** - Uses TrainingRecommendationService
- ✅ Displays participants recommendation table
- ✅ Shows aspect priority for training
- ✅ Tolerance adjustment updates recommendations
- ✅ Standard adjustment updates recommendations
- ✅ Summary statistics display correctly

**Statistic.php** - Uses StatisticService
- ✅ Displays frequency distribution chart
- ✅ Standard adjustment updates distribution
- ✅ Aspect selection updates chart
- ✅ Shows standard rating vs average rating

**Ranking Components** - Uses RankingService
- ✅ RankingPsyMapping displays Potensi rankings
- ✅ RankingMcMapping displays Kompetensi rankings
- ✅ RekapRankingAssessment shows combined rankings
- ✅ Rankings update when standard adjusted
- ✅ Custom standard selection updates rankings

**Total**: 6 tests

#### 2.2 Individual Reports (9 tests)

**RingkasanAssessment.php** - Uses IndividualAssessmentService
- ✅ Displays final assessment for participant
- ✅ Shows category assessments (Potensi + Kompetensi)
- ✅ Reflects session adjustments in calculations
- ✅ Custom standard selection updates assessment

**GeneralMapping.php** - Assessment mapping display
- ✅ Displays aspect assessments
- ✅ Shows gaps correctly
- ✅ Applies tolerance to conclusions

**GeneralPsyMapping.php** - Potensi specific mapping
- ✅ Shows Potensi aspects with sub-aspects
- ✅ Data-driven rating calculation (average of sub-aspects)
- ✅ Session adjustments reflected

**GeneralMcMapping.php** - Kompetensi specific mapping
- ✅ Shows Kompetensi aspects (no sub-aspects)
- ✅ Direct rating values
- ✅ Session adjustments reflected

**GeneralMatching.php** - Job matching
- ✅ Displays matching percentages
- ✅ Calculates overall job matching
- ✅ Potensi vs Kompetensi breakdown

**Total**: 9 tests

---

### Phase 3: Helper Components (Priority ⭐)

**Optional tests for display-only or simple helper components**

#### 3.1 Filter & Helper Components (3 tests)

**AspectSelector.php** - Aspect filter
- ✅ Loads aspects for selected template
- ✅ Filters by category (Potensi/Kompetensi)
- ✅ Persists to session

**ToleranceSelector.php** - Tolerance percentage selector
- ✅ Displays tolerance slider
- ✅ Persists to session
- ✅ Dispatches update event

**CategoryWeightEditor.php** - Category weight inline editor
- ✅ Displays current weights
- ✅ Validates total = 100%
- ✅ Saves to session

**Total**: 3 tests

---

## 📝 Test File Structure

```
tests/
└── Feature/                       # Livewire feature tests
    └── Livewire/
        ├── DynamicStandard/
        │   ├── StandardPsikometrikTest.php           # 6 tests
        │   ├── StandardMcTest.php                    # 6 tests
        │   └── SelectiveAspectsModalTest.php         # 8 tests
        │
        ├── CustomStandards/
        │   ├── IndexTest.php                         # 4 tests
        │   ├── CreateTest.php                        # 4 tests
        │   └── EditTest.php                          # 3 tests
        │
        ├── Selectors/
        │   ├── EventSelectorTest.php                 # 2 tests
        │   └── PositionSelectorTest.php              # 2 tests
        │
        ├── GeneralReports/
        │   ├── TrainingRecommendationTest.php        # 3 tests
        │   ├── StatisticTest.php                     # 2 tests
        │   └── RankingTest.php                       # 3 tests
        │
        ├── IndividualReports/
        │   ├── RingkasanAssessmentTest.php          # 3 tests
        │   ├── GeneralMappingTest.php                # 2 tests
        │   ├── GeneralPsyMappingTest.php             # 2 tests
        │   ├── GeneralMcMappingTest.php              # 2 tests
        │   └── GeneralMatchingTest.php               # 2 tests
        │
        └── Helpers/
            ├── AspectSelectorTest.php                # 1 test
            ├── ToleranceSelectorTest.php             # 1 test
            └── CategoryWeightEditorTest.php          # 1 test
```

---

## 🧪 Test Conventions

### Livewire Testing with PHPUnit (NOT Pest)

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\DynamicStandard;

use App\Livewire\Pages\GeneralReport\StandardPsikometrik;
use App\Models\{AssessmentEvent, AssessmentTemplate, PositionFormation, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StandardPsikometrikTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_loads_with_session_filters(): void
    {
        // Arrange: Create test data
        $user = User::factory()->create();
        $template = AssessmentTemplate::factory()->create();
        $event = AssessmentEvent::factory()->create();
        $position = PositionFormation::factory()->create([
            'template_id' => $template->id,
        ]);
        $event->positionFormations()->attach($position->id);

        // Set session filters
        session([
            'filter.event_code' => $event->code,
            'filter.position_formation_id' => $position->id,
        ]);

        // Act & Assert: Component renders with correct data
        Livewire::actingAs($user)
            ->test(StandardPsikometrik::class)
            ->assertSet('selectedEvent.code', $event->code)
            ->assertSet('selectedTemplate.id', $template->id)
            ->assertStatus(200);
    }

    public function test_category_weight_modal_opens_and_saves(): void
    {
        // Arrange
        $user = User::factory()->create();
        $template = AssessmentTemplate::factory()
            ->hasCategories(1, ['code' => 'potensi', 'weight_percentage' => 50])
            ->create();

        session(['filter.event_code' => '...', 'filter.position_formation_id' => 1]);

        // Act & Assert: Open modal
        Livewire::actingAs($user)
            ->test(StandardPsikometrik::class)
            ->call('openEditCategoryWeight', 'potensi', 50)
            ->assertSet('showEditCategoryWeightModal', true)
            ->assertSet('editingValue', 50)

            // Change weight
            ->set('editingValue', 60)

            // Save
            ->call('saveCategoryWeight')
            ->assertSet('showEditCategoryWeightModal', false)
            ->assertDispatched('standard-adjusted');

        // Assert: Session updated
        $this->assertEquals(60, session('standard_adjustment.{templateId}.category_weights.potensi'));
    }
}
```

### Key Testing Patterns

#### 1. Component Initialization
```php
public function test_component_mounts_successfully(): void
{
    Livewire::test(MyComponent::class)
        ->assertStatus(200)
        ->assertSet('propertyName', expectedValue);
}
```

#### 2. Livewire Actions
```php
public function test_action_performs_correctly(): void
{
    Livewire::test(MyComponent::class)
        ->call('methodName', $param1, $param2)
        ->assertSet('propertyName', newValue)
        ->assertDispatched('eventName');
}
```

#### 3. Event Listening
```php
public function test_listens_to_event(): void
{
    Livewire::test(MyComponent::class)
        ->dispatch('event-name', eventData: 'value')
        ->assertSet('propertyName', updatedValue);
}
```

#### 4. Session Integration
```php
public function test_reads_from_session(): void
{
    session(['filter.event_code' => 'EVT001']);

    Livewire::test(MyComponent::class)
        ->assertSet('eventCode', 'EVT001');
}

public function test_writes_to_session(): void
{
    Livewire::test(MyComponent::class)
        ->set('eventCode', 'EVT002')
        ->call('saveToSession');

    $this->assertEquals('EVT002', session('filter.event_code'));
}
```

#### 5. Service Integration
```php
public function test_uses_service_correctly(): void
{
    // Arrange: Create test data
    $template = AssessmentTemplate::factory()->create();

    // Mock or use real service
    $service = app(DynamicStandardService::class);

    // Act: Component calls service
    Livewire::test(MyComponent::class)
        ->call('loadData');

    // Assert: Service was called and data loaded
    $this->assertNotEmpty(session("standard_adjustment.{$template->id}"));
}
```

---

## 🏗️ Critical Test Scenarios

### 1. 3-Layer Priority Chain in Livewire

**Components must reflect priority chain from DynamicStandardService**

```php
public function test_component_reflects_session_priority_over_custom(): void
{
    // Arrange: Setup custom standard (Layer 2)
    $customStandard = CustomStandard::factory()->create([
        'aspect_configs' => [
            'asp_01' => ['weight' => 30, 'active' => true],
        ],
    ]);

    // Select custom standard
    $customService = app(CustomStandardService::class);
    $customService->select($template->id, $customStandard->id);

    // Apply session adjustment (Layer 1 - highest priority)
    $dynamicService = app(DynamicStandardService::class);
    $dynamicService->saveAspectWeight($template->id, 'asp_01', 40);

    // Act: Load component
    Livewire::test(StandardPsikometrik::class)
        ->assertSee('40') // Should show session value (Layer 1)
        ->assertDontSee('30'); // Not custom standard value (Layer 2)
}
```

### 2. Real-Time Updates via Events

**Components must update when other components dispatch events**

```php
public function test_component_updates_on_standard_adjusted_event(): void
{
    // Arrange: Component loaded with initial data
    $component = Livewire::test(TrainingRecommendation::class)
        ->assertSet('recommendedCount', 10);

    // Act: Another component adjusts standard
    $dynamicService = app(DynamicStandardService::class);
    $dynamicService->saveAspectRating($template->id, 'asp_01', 4);

    // Dispatch event
    $component->dispatch('standard-adjusted', templateId: $template->id);

    // Assert: Component reloads data
    $component->assertSet('recommendedCount', 8); // Count changed
}
```

### 3. Cache Invalidation

**Components must clear cache when data changes**

```php
public function test_cache_cleared_on_standard_switch(): void
{
    // Arrange: Load component with cached data
    $component = Livewire::test(StandardPsikometrik::class);

    // Act: Switch custom standard
    $component->call('selectCustomStandard', $newCustomStandardId);

    // Assert: Cache cleared and data reloaded
    $component->assertSet('categoryDataCache', null) // Cache cleared
        ->assertNotEmpty('categoryData'); // Fresh data loaded
}
```

---

## 🏃 Running Tests

```bash
# Run all Livewire tests
php artisan test tests/Feature/Livewire

# Run specific test file
php artisan test tests/Feature/Livewire/DynamicStandard/StandardPsikometrikTest.php

# Run specific test method
php artisan test --filter=test_component_loads_with_session_filters

# Run with coverage
php artisan test tests/Feature/Livewire --coverage
```

---

## ⚠️ Common Pitfalls

### 1. Session State Not Set

```php
// ❌ WRONG: Component expects session data but not set
Livewire::test(StandardPsikometrik::class)
    ->assertSet('selectedEvent', null); // Will fail if session empty

// ✅ CORRECT: Set session before testing
session([
    'filter.event_code' => $event->code,
    'filter.position_formation_id' => $position->id,
]);

Livewire::test(StandardPsikometrik::class)
    ->assertSet('selectedEvent.code', $event->code);
```

### 2. Missing actingAs() for Auth

```php
// ❌ WRONG: Component requires auth but not logged in
Livewire::test(CustomStandards\Create::class); // Will fail auth check

// ✅ CORRECT: Use actingAs()
$user = User::factory()->create();
Livewire::actingAs($user)
    ->test(CustomStandards\Create::class);
```

### 3. Not Testing Event Dispatch

```php
// ❌ WRONG: Not verifying event was dispatched
Livewire::test(StandardPsikometrik::class)
    ->call('saveCategoryWeight');

// ✅ CORRECT: Assert event dispatched
Livewire::test(StandardPsikometrik::class)
    ->call('saveCategoryWeight')
    ->assertDispatched('standard-adjusted', ['templateId' => $template->id]);
```

### 4. Forgetting Cache Invalidation

```php
// ❌ WRONG: Expecting fresh data without cache clear
Livewire::test(StandardPsikometrik::class)
    ->call('handleStandardUpdate', $templateId)
    ->assertSet('categoryData', $newData); // May show cached data

// ✅ CORRECT: Verify cache cleared
Livewire::test(StandardPsikometrik::class)
    ->call('handleStandardUpdate', $templateId)
    ->assertSet('categoryDataCache', null) // Cache cleared
    ->assertSet('categoryData', $newData); // Fresh data
```

---

## 📚 Key Files to Read

Before writing tests, read these files:

1. **Component being tested**
   - `app/Livewire/Pages/GeneralReport/StandardPsikometrik.php`
   - `app/Livewire/Pages/CustomStandards/Index.php`

2. **Service layer tests** (for integration patterns)
   - `tests/Unit/Services/DynamicStandardServiceTest.php`
   - `tests/Unit/Services/CustomStandardServiceTest.php`

3. **Livewire documentation**
   - https://livewire.laravel.com/docs/testing

4. **Architecture docs**
   - `docs/TESTING_GUIDE.md` (Service layer tests)
   - `docs/ASSESSMENT_CALCULATION_FLOW.md`

---

## 📝 Quick Reference Cheat Sheet

### Component Testing Basics

```php
// Basic component test
Livewire::test(ComponentClass::class)
    ->assertStatus(200)
    ->assertSee('Expected Text');

// Test with authentication
Livewire::actingAs($user)
    ->test(ComponentClass::class);

// Test property
Livewire::test(ComponentClass::class)
    ->assertSet('propertyName', 'expectedValue');

// Test method call
Livewire::test(ComponentClass::class)
    ->call('methodName', $param1, $param2)
    ->assertSet('propertyName', 'newValue');

// Test event dispatch
Livewire::test(ComponentClass::class)
    ->call('methodName')
    ->assertDispatched('event-name');

// Test event listening
Livewire::test(ComponentClass::class)
    ->dispatch('event-name', eventData: 'value')
    ->assertSet('propertyName', 'updatedValue');
```

### Session Testing

```php
// Set session before test
session(['key' => 'value']);

// Assert session after action
$this->assertEquals('value', session('key'));
```

### Database Assertions

```php
// Assert database has record
$this->assertDatabaseHas('table_name', ['column' => 'value']);

// Assert database missing record
$this->assertDatabaseMissing('table_name', ['column' => 'value']);
```

---

## 🎯 Implementation Order

### Phase 1: Core Infrastructure (Start Here) ⭐⭐⭐

1. ✅ **SelectiveAspectsModal** (11 tests) - **COMPLETE** - Most complex, used by StandardPsikometrik & StandardMc
2. **StandardPsikometrik** (6 tests) - Session editing for Potensi - **NEXT**
3. **StandardMc** (6 tests) - Session editing for Kompetensi
4. **EventSelector** (2 tests) - Foundation for all filters
5. **PositionSelector** (2 tests) - Foundation for all filters
6. **CustomStandards/Index** (4 tests) - CRUD foundation
7. **CustomStandards/Create** (4 tests) - CRUD foundation
8. **CustomStandards/Edit** (3 tests) - CRUD foundation

**Subtotal**: 11/35 tests complete (31.4%) - Foundation in progress

### Phase 2: Report Components ⭐⭐

9. **TrainingRecommendation** (3 tests) - Service integration
10. **Statistic** (2 tests) - Service integration
11. **Ranking components** (3 tests) - Service integration
12. **Individual report components** (9 tests) - Service integration

**Subtotal**: 17 tests

### Phase 3: Helpers ⭐ (Optional)

13. **AspectSelector** (1 test)
14. **ToleranceSelector** (1 test)
15. **CategoryWeightEditor** (1 test)

**Subtotal**: 3 tests

**TOTAL**: 45 tests

---

## 📊 Test Coverage Goals

| Category | Target Coverage | Rationale |
|----------|----------------|-----------|
| Core Infrastructure | 100% | Critical for system functionality |
| Report Components | 80% | Service layer already tested |
| Helper Components | 60% | Simple display logic |

---

**Version**: 1.3
**Last Updated**: 2025-12-03
**Status**: 🚧 In Progress - 11/45 tests complete (24.4%)
**Next Action**: Implement StandardPsikometrik tests (6 tests)
**Maintainer**: Development Team
**Changelog**:
- v1.3 (2025-12-03): ✅ SelectiveAspectsModal complete (11 tests, 46 assertions) - Updated progress tracking
- v1.2 (2025-12-03): Added "How to Use This Guide" section + Critical Context from Service Layer for self-contained usage in new chat sessions
- v1.1 (2025-12-03): Fixed folder structure - Training & Ranking are subfolders of GeneralReport
- v1.0 (2025-12-03): Initial documentation created
