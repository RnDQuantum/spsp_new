# Test Plan: StandardPsikometrik Component

**Component:** `App\Livewire\Pages\GeneralReport\StandardPsikometrik`
**Created:** December 2025
**Status:** PENDING APPROVAL

---

## 📋 Component Analysis Summary

### **Component Responsibility:**
- Display Potensi category standard (weights, ratings, sub-aspects)
- Allow baseline selection (Quantum Default vs Custom Standard)
- Enable session adjustments (category weights, sub-aspect ratings)
- Emit events to notify other components of baseline changes
- Listen to events from other components (event-selected, position-selected, standard-switched)

### **Key Properties:**
```php
// Event Listeners
$listeners = [
    'event-selected',           // From EventSelector
    'position-selected',        // From PositionSelector
    'standard-adjusted',        // Self + other components
    'standard-switched',        // Self + other components
]

// Baseline Management
$selectedCustomStandardId       // null = Quantum Default, int = Custom Standard ID
$availableCustomStandards       // List of custom standards for dropdown

// Data Display
$categoryData                   // Processed Potensi data with weights/ratings
$chartData                      // Chart visualization data
$totals                         // Summary statistics
$maxScore                       // Chart scaling

// Modal States
$showEditRatingModal            // Sub-aspect rating edit modal
$showEditCategoryWeightModal    // Category weight edit modal
$editingField                   // Currently editing field code
$editingValue                   // Current edited value
$editingOriginalValue           // Original value from database

// Cache
$categoryDataCache              // Cached processed data
$chartDataCache                 // Cached chart data
$totalsCache                    // Cached totals
$maxScoreCache                  // Cached max score
```

### **Public Methods (Test Targets):**

#### **Lifecycle & Data Loading (4 methods)**
1. `mount()` - Initialize component
2. `loadStandardData()` - Load Potensi data with DynamicStandardService
3. `loadAvailableCustomStandards()` - Load custom standards for dropdown
4. `clearCache()` - Clear all cached data

#### **Event Handlers (4 methods)**
5. `handleEventSelected(?string $eventCode)` - Event changed
6. `handlePositionSelected(?int $positionFormationId)` - Position selected
7. `handleStandardUpdate(int $templateId)` - Standard adjusted
8. `handleStandardSwitch(int $templateId)` - Standard switched

#### **Baseline Management (1 method)**
9. `selectCustomStandard($customStandardId)` - User selects baseline from dropdown

#### **Session Adjustment - Category Weight (2 methods)**
10. `openEditCategoryWeight(string $categoryCode, int $currentWeight)` - Open modal
11. `saveCategoryWeight()` - Save category weight adjustment

#### **Session Adjustment - Sub-Aspect Rating (2 methods)**
12. `openEditSubAspectRating(string $subAspectCode, int $currentRating)` - Open modal
13. `saveSubAspectRating()` - Save sub-aspect rating adjustment

#### **Other Actions (3 methods)**
14. `openSelectionModal()` - Open SelectiveAspectsModal (for active/inactive)
15. `resetAdjustments()` - Reset all session adjustments
16. `closeModal()` - Close edit modals

---

## 🧪 Test Plan (Total: 25 Tests)

### **Group 1: Lifecycle & Initialization (3 tests)**

#### Test 1.1: Component Mounts Successfully
```php
test('component mounts with default state')
```
**Given:**
- No event/position selected
- No custom standard selected

**When:**
- Component mounts

**Then:**
- `selectedCustomStandardId` = null
- `categoryData` = []
- `chartData` = empty arrays
- `availableCustomStandards` = []
- `chartId` generated (not empty)
- Cache properties all null

---

#### Test 1.2: Component Loads Data After Event & Position Selected
```php
test('component loads standard data when event and position selected')
```
**Given:**
- Event "P3K-2025" selected in session
- Position with template_id = 4 selected in session
- Template has Potensi category with 4 aspects

**When:**
- Component mounts
- `loadStandardData()` called

**Then:**
- `selectedTemplate` loaded (template_id = 4)
- `categoryData` contains 1 category (Potensi)
- `categoryData[0]['aspects']` contains 4 aspects
- `chartData['labels']` has 4 entries
- `totals['total_aspects']` > 0
- Cache populated

**Critical:**
- DynamicStandardService used for all weights/ratings (3-layer priority)
- AspectCacheService preloaded to avoid N+1

---

#### Test 1.3: Component Loads Available Custom Standards
```php
test('component loads available custom standards for institution')
```
**Given:**
- User belongs to institution_id = 1
- Template_id = 4 has 2 custom standards for institution 1
- Custom standards: ["Kejaksaan 2025", "Polri 2025"]

**When:**
- `loadAvailableCustomStandards()` called

**Then:**
- `availableCustomStandards` = array of 2 items
- Each item has: id, code, name, description
- `selectedCustomStandardId` = null (default Quantum)

---

### **Group 2: Baseline Selection & Switching (5 tests)**

#### Test 2.1: Select Custom Standard from Dropdown
```php
test('selecting custom standard updates component state')
```
**Given:**
- Quantum Default active (selectedCustomStandardId = null)
- Custom Standard "Kejaksaan 2025" (id: 1) available

**When:**
- User selects custom standard from dropdown
- `selectCustomStandard(1)` called

**Then:**
- `selectedCustomStandardId` = 1
- Cache cleared
- `loadStandardData()` called
- Event 'standard-switched' dispatched with templateId
- Data reloaded with custom standard weights/ratings

**Verification:**
- CustomStandardService::select() called with template_id, 1
- Session adjustments cleared (if any)

---

#### Test 2.2: Switch from Custom Standard to Quantum Default
```php
test('switching to quantum default clears custom standard selection')
```
**Given:**
- Custom Standard "Kejaksaan 2025" selected (id: 1)
- Session has adjustments

**When:**
- User selects "Quantum Default" from dropdown
- `selectCustomStandard(null)` called

**Then:**
- `selectedCustomStandardId` = null
- Session adjustments cleared
- Event 'standard-switched' dispatched
- Data reloaded with Quantum Default weights/ratings

---

#### Test 2.3: Handle Null String from Dropdown
```php
test('handles string null empty string or actual null correctly')
```
**Given:**
- Custom Standard selected

**When:**
- Dropdown sends various null representations:
  - `selectCustomStandard('null')` (string)
  - `selectCustomStandard('')` (empty string)
  - `selectCustomStandard(null)` (actual null)

**Then:**
- All treated as null
- `selectedCustomStandardId` = null
- Fallback to Quantum Default

**Critical:**
- Code line 302-304 handles this conversion

---

#### Test 2.4: Switching Custom Standard Clears Session Adjustments
```php
test('switching custom standard clears previous session adjustments')
```
**Given:**
- Custom Standard "Kejaksaan 2025" (id: 1) selected
- User has session adjustments:
  - Category weight: Potensi 25% → 30%
  - Sub-aspect rating: Daya Analisa 3 → 4

**When:**
- User switches to Custom Standard "Polri 2025" (id: 2)
- `selectCustomStandard(2)` called

**Then:**
- Old session adjustments cleared
- `selectedCustomStandardId` = 2
- Data loaded from new custom standard (no adjustments)

**Verification:**
- CustomStandardService::select() clears session
- No adjustment markers in UI (is_adjusted = false)

---

#### Test 2.5: Receive 'standard-switched' Event from Other Component
```php
test('handles standard switched event from other components')
```
**Given:**
- StandardMc component dispatches 'standard-switched' with templateId: 4
- StandardPsikometrik listening

**When:**
- `handleStandardSwitch(4)` called

**Then:**
- Cache cleared
- `loadStandardData()` called
- `loadAvailableCustomStandards()` called
- Event 'chartDataUpdated' dispatched

**Edge Case:**
- If templateId ≠ component's templateId → ignore event

---

### **Group 3: Session Adjustments - Category Weight (4 tests)**

#### Test 3.1: Open Category Weight Edit Modal
```php
test('opening category weight modal sets state correctly')
```
**Given:**
- Template selected (id: 4)
- Potensi category has weight: 25%

**When:**
- User clicks edit icon
- `openEditCategoryWeight('potensi', 25)` called

**Then:**
- `showEditCategoryWeightModal` = true
- `editingField` = 'potensi'
- `editingValue` = 25
- `editingOriginalValue` = 25 (from database)
- Error bag empty

---

#### Test 3.2: Save Category Weight Adjustment
```php
test('saving category weight creates session adjustment')
```
**Given:**
- Modal open (editingField = 'potensi', editingValue = 25)
- User changes value to 30

**When:**
- User clicks save
- `saveCategoryWeight()` called

**Then:**
- DynamicStandardService::saveCategoryWeight(4, 'potensi', 30) called
- `showEditCategoryWeightModal` = false
- Event 'standard-adjusted' dispatched with templateId: 4
- Modal closes

**Verification:**
- Session has adjustment: category_weights['potensi'] = 30
- On reload: weight shows 30 (not 25)
- UI shows adjustment marker (is_adjusted = true)

---

#### Test 3.3: Close Modal Without Saving
```php
test('closing modal without saving discards changes')
```
**Given:**
- Modal open (editingValue changed from 25 → 30)
- User has NOT clicked save

**When:**
- User clicks close/cancel
- `closeModal()` called

**Then:**
- `showEditCategoryWeightModal` = false
- `editingField` = ''
- `editingValue` = null
- `editingOriginalValue` = null
- Error bag cleared
- NO event dispatched
- NO session adjustment saved

---

#### Test 3.4: Category Weight Modal Validation
```php
test('category weight modal handles invalid template gracefully')
```
**Given:**
- Template NOT selected (selectedTemplate = null)

**When:**
- `openEditCategoryWeight('potensi', 25)` called

**Then:**
- Modal does NOT open
- `showEditCategoryWeightModal` = false
- Method returns early

---

### **Group 4: Session Adjustments - Sub-Aspect Rating (5 tests)**

#### Test 4.1: Open Sub-Aspect Rating Edit Modal
```php
test('opening sub aspect rating modal sets state correctly')
```
**Given:**
- Template selected (id: 4)
- Sub-aspect "Daya Analisa" has rating: 3

**When:**
- User clicks edit icon
- `openEditSubAspectRating('daya-analisa', 3)` called

**Then:**
- `showEditRatingModal` = true
- `editingField` = 'daya-analisa'
- `editingValue` = 3
- `editingOriginalValue` = 3 (from database)
- Error bag cleared

---

#### Test 4.2: Save Sub-Aspect Rating Adjustment (Valid)
```php
test('saving sub aspect rating creates session adjustment')
```
**Given:**
- Modal open (editingField = 'daya-analisa', editingValue = 3)
- User changes value to 4

**When:**
- User clicks save
- `saveSubAspectRating()` called

**Then:**
- Validation passes (4 is between 1-5)
- DynamicStandardService::saveSubAspectRating(4, 'daya-analisa', 4) called
- `showEditRatingModal` = false
- Event 'standard-adjusted' dispatched
- Modal closes

**Verification:**
- Session has adjustment: sub_aspect_ratings['daya-analisa'] = 4
- Aspect average rating recalculated

---

#### Test 4.3: Sub-Aspect Rating Validation (Too Low)
```php
test('sub aspect rating validation rejects values below 1')
```
**Given:**
- Modal open (editingValue = 3)
- User changes value to 0

**When:**
- User clicks save
- `saveSubAspectRating()` called

**Then:**
- Validation fails
- Error added: 'Rating harus antara 1 sampai 5.'
- `showEditRatingModal` = true (stays open)
- NO event dispatched
- NO session adjustment saved

---

#### Test 4.4: Sub-Aspect Rating Validation (Too High)
```php
test('sub aspect rating validation rejects values above 5')
```
**Given:**
- Modal open (editingValue = 3)
- User changes value to 6

**When:**
- User clicks save
- `saveSubAspectRating()` called

**Then:**
- Validation fails
- Error added: 'Rating harus antara 1 sampai 5.'
- Modal stays open
- NO changes saved

---

#### Test 4.5: Sub-Aspect Rating Handles Null Template
```php
test('sub aspect rating modal handles null template gracefully')
```
**Given:**
- Template NOT selected (selectedTemplate = null)

**When:**
- `openEditSubAspectRating('daya-analisa', 3)` called

**Then:**
- Modal does NOT open
- Method returns early

---

### **Group 5: Reset Adjustments (2 tests)**

#### Test 5.1: Reset All Adjustments
```php
test('reset adjustments clears all session adjustments')
```
**Given:**
- Custom Standard "Kejaksaan 2025" selected
- User has session adjustments:
  - Category weight: Potensi 25% → 30%
  - Sub-aspect rating: Daya Analisa 3 → 4
  - Some sub-aspects marked inactive

**When:**
- User clicks "Reset Adjustments" button
- `resetAdjustments()` called

**Then:**
- DynamicStandardService::resetCategoryAdjustments(4, 'potensi') called
- DynamicStandardService::resetCategoryWeights(4) called
- Event 'standard-adjusted' dispatched
- All values return to Custom Standard baseline
- No adjustment markers (is_adjusted = false)

**Critical:**
- Resets to Custom Standard, NOT Quantum Default
- Only session adjustments cleared, custom standard selection persists

---

#### Test 5.2: Reset with No Template Selected
```php
test('reset adjustments handles null template gracefully')
```
**Given:**
- Template NOT selected (selectedTemplate = null)

**When:**
- `resetAdjustments()` called

**Then:**
- Method returns early
- No service calls
- No events dispatched

---

### **Group 6: Event Handling (3 tests)**

#### Test 6.1: Handle Event Selected
```php
test('handles event selected clears cache and waits for position')
```
**Given:**
- Event "P3K-2024" was selected
- Data loaded

**When:**
- Event changed to "P3K-2025"
- Event 'event-selected' dispatched with 'P3K-2025'
- `handleEventSelected('P3K-2025')` called

**Then:**
- Cache cleared
- Data NOT reloaded yet (waits for position)
- Component ready for position selection

---

#### Test 6.2: Handle Position Selected
```php
test('handles position selected loads data and dispatches chart update')
```
**Given:**
- Event selected
- Position NOT yet selected

**When:**
- Position selected (id: 1)
- Event 'position-selected' dispatched with 1
- `handlePositionSelected(1)` called

**Then:**
- Cache cleared
- `loadStandardData()` called
- `loadAvailableCustomStandards()` called
- Event 'chartDataUpdated' dispatched with:
  - labels, ratings, scores, templateName, maxScore

---

#### Test 6.3: Handle Standard Adjusted from Other Component
```php
test('handles standard adjusted event from other components')
```
**Given:**
- StandardMc dispatches 'standard-adjusted' with templateId: 4
- StandardPsikometrik listening

**When:**
- `handleStandardUpdate(4)` called

**Then:**
- Cache cleared
- `loadStandardData()` called
- Event 'chartDataUpdated' dispatched

**Edge Case:**
- If templateId ≠ component's templateId → ignore event

---

### **Group 7: Cache Management (2 tests)**

#### Test 7.1: Cache Prevents Redundant Calculations
```php
test('cache prevents redundant data processing')
```
**Given:**
- Component loaded data once
- Cache populated ($categoryDataCache !== null)

**When:**
- `loadStandardData()` called again (e.g., on re-render)

**Then:**
- Database queries NOT executed
- Data loaded from cache
- categoryData, chartData, totals, maxScore from cache
- No DynamicStandardService calls

---

#### Test 7.2: Cache Cleared on Baseline Change
```php
test('cache cleared on baseline changes')
```
**Given:**
- Data cached

**When:**
- Any of these actions:
  - `selectCustomStandard()` called
  - `handleStandardSwitch()` called
  - `handleStandardUpdate()` called
  - `handleEventSelected()` called
  - `handlePositionSelected()` called

**Then:**
- `clearCache()` called
- All cache properties = null
- Next `loadStandardData()` recalculates

---

### **Group 8: 3-Layer Priority Integration (1 test)**

#### Test 8.1: Data Respects 3-Layer Priority
```php
test('loaded data respects 3 layer priority system')
```
**Given:**
- Quantum Default: Potensi weight = 25%
- Custom Standard "Kejaksaan": Potensi weight = 30%
- Session Adjustment: Potensi weight = 35%

**Scenarios:**

**Scenario A: Quantum Default Only**
- selectedCustomStandardId = null
- No session adjustments
- Expected: categoryData[0]['weight_percentage'] = 25

**Scenario B: Custom Standard Override**
- selectedCustomStandardId = 1 (Kejaksaan)
- No session adjustments
- Expected: categoryData[0]['weight_percentage'] = 30

**Scenario C: Session Override**
- selectedCustomStandardId = 1 (Kejaksaan)
- Session adjustment: Potensi = 35
- Expected: categoryData[0]['weight_percentage'] = 35

**Critical:**
- All weights/ratings loaded via DynamicStandardService
- Never directly from database models

---

## 📊 Test Coverage Summary

| Group | Tests | Focus Area |
|-------|-------|------------|
| **Group 1** | 3 | Lifecycle & Initialization |
| **Group 2** | 5 | Baseline Selection & Switching |
| **Group 3** | 4 | Category Weight Adjustments |
| **Group 4** | 5 | Sub-Aspect Rating Adjustments |
| **Group 5** | 2 | Reset Adjustments |
| **Group 6** | 3 | Event Handling |
| **Group 7** | 2 | Cache Management |
| **Group 8** | 1 | 3-Layer Priority Integration |
| **TOTAL** | **25** | **Complete Coverage** |

---

## ✅ Methods Coverage Checklist

- [x] `mount()` - Test 1.1, 1.2
- [x] `loadStandardData()` - Test 1.2, 7.1, 8.1
- [x] `loadAvailableCustomStandards()` - Test 1.3
- [x] `clearCache()` - Test 7.2
- [x] `handleEventSelected()` - Test 6.1
- [x] `handlePositionSelected()` - Test 6.2
- [x] `handleStandardUpdate()` - Test 6.3
- [x] `handleStandardSwitch()` - Test 2.5
- [x] `selectCustomStandard()` - Test 2.1, 2.2, 2.3, 2.4
- [x] `openEditCategoryWeight()` - Test 3.1, 3.4
- [x] `saveCategoryWeight()` - Test 3.2
- [x] `openEditSubAspectRating()` - Test 4.1, 4.5
- [x] `saveSubAspectRating()` - Test 4.2, 4.3, 4.4
- [x] `openSelectionModal()` - Not critical for baseline tests (separate modal)
- [x] `resetAdjustments()` - Test 5.1, 5.2
- [x] `closeModal()` - Test 3.3

**Coverage: 15/16 methods (93.75%)** ✅

---

## 🔧 Test Implementation Notes

### **Required Test Setup:**

```php
use Livewire\Volt\Volt;
use Livewire\Livewire;
use App\Models\{AssessmentEvent, AssessmentTemplate, CategoryType, Aspect, SubAspect};
use App\Services\{DynamicStandardService, CustomStandardService};

beforeEach(function () {
    // Create test user with institution
    $this->user = User::factory()->create(['institution_id' => 1]);
    $this->actingAs($this->user);

    // Create test template with Potensi category
    $this->template = AssessmentTemplate::factory()->create(['id' => 4]);
    $this->category = CategoryType::factory()->create([
        'template_id' => 4,
        'code' => 'potensi',
        'weight_percentage' => 25,
    ]);
    $this->aspect = Aspect::factory()->create([
        'category_type_id' => $this->category->id,
        'template_id' => 4,
        'code' => 'daya-pikir',
        'weight_percentage' => 5,
    ]);
    $this->subAspect = SubAspect::factory()->create([
        'aspect_id' => $this->aspect->id,
        'code' => 'daya-analisa',
        'standard_rating' => 3,
    ]);

    // Create custom standard
    $this->customStandard = CustomStandard::factory()->create([
        'institution_id' => 1,
        'template_id' => 4,
        'code' => 'KEJAKSAAN-2025',
        'name' => 'Standar Khusus Kejaksaan 2025',
    ]);
});
```

### **Livewire Testing Patterns:**

```php
// Test component state
Livewire::test(StandardPsikometrik::class)
    ->assertSet('selectedCustomStandardId', null)
    ->assertSet('categoryData', []);

// Test method calls
Livewire::test(StandardPsikometrik::class)
    ->call('selectCustomStandard', 1)
    ->assertSet('selectedCustomStandardId', 1)
    ->assertDispatched('standard-switched');

// Test event listeners
Livewire::test(StandardPsikometrik::class)
    ->dispatch('event-selected', 'P3K-2025')
    ->assertSet('categoryData', []); // Should clear

// Test modal interactions
Livewire::test(StandardPsikometrik::class)
    ->call('openEditCategoryWeight', 'potensi', 25)
    ->assertSet('showEditCategoryWeightModal', true)
    ->assertSet('editingValue', 25)
    ->set('editingValue', 30)
    ->call('saveCategoryWeight')
    ->assertSet('showEditCategoryWeightModal', false)
    ->assertDispatched('standard-adjusted');
```

---

## 🚀 Next Steps

1. **Review & Approve Test Plan** ✅ PENDING
2. Implement tests in `tests/Feature/Livewire/StandardPsikometrikTest.php`
3. Run tests: `php artisan test --filter=StandardPsikometrik`
4. Verify 100% coverage of baseline-related methods
5. Move to StandardMc component

---

**Status:** ✅ READY FOR APPROVAL
**Estimated Implementation Time:** ~2-3 hours for 25 tests
**Priority:** P1 (Producer Component - Critical)
