# Service Architecture: Assessment Calculation System

> **Version**: 1.1
> **Last Updated**: 2025-01-15
> **Purpose**: Single Source of Truth untuk kalkulasi assessment (Individual & Ranking)

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Core Services](#core-services)
3. [Calculation Levels](#calculation-levels)
4. [Dynamic Standards Integration](#dynamic-standards-integration)
5. [Cache Management](#cache-management)
6. [Standard Adjustment Flow](#standard-adjustment-flow)
7. [Migration Guide](#migration-guide)
8. [Troubleshooting](#troubleshooting)

---

## Overview

### Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                        USER INTERACTION                          │
│  Edit Standard → Save to Session → Dispatch 'standard-adjusted' │
└────────────────────────────┬────────────────────────────────────┘
                             ↓
┌─────────────────────────────────────────────────────────────────┐
│                   DynamicStandardService                         │
│  - Session-based adjustments (tidak langsung ke database)        │
│  - Smart saving (hanya simpan jika berbeda dari original)       │
│  - Auto-cleanup (hapus dari session jika kembali ke original)   │
│                                                                  │
│  Stores:                                                         │
│  • Active aspects/sub-aspects                                    │
│  • Aspect weights                                                │
│  • Aspect ratings (Kompetensi)                                   │
│  • Sub-aspect ratings (Potensi)                                  │
│  • Category weights (Potensi/Kompetensi)                         │
└────────────────────────────┬────────────────────────────────────┘
                             ↓
        ┌────────────────────┴────────────────────┐
        ↓                                          ↓
┌──────────────────────┐              ┌──────────────────────┐
│ IndividualAssessment │              │   RankingService     │
│      Service         │              │                      │
│                      │              │                      │
│ • Aspect-level detail│              │ • Standard calc      │
│ • Category totals    │              │ • Participant ranks  │
│ • Final scores       │              │ • Conclusion summary │
│ • Tolerance support  │              │ • Sorting logic      │
└──────────┬───────────┘              └──────────┬───────────┘
           ↓                                      ↓
┌─────────────────────────────────────────────────────────────────┐
│                      LIVEWIRE COMPONENTS                         │
│                                                                  │
│  Individual Reports:              Ranking Reports:               │
│  • GeneralPsyMapping ✅           • RankingPsyMapping ✅         │
│  • GeneralMcMapping ✅            • RankingMcMapping ✅          │
│  • GeneralMapping ✅              • RekapRankingAssessment       │
│                                                                  │
│  Cache Management:                                               │
│  • Cache data per request                                        │
│  • Clear cache on 'standard-adjusted' event                     │
│  • Clear cache on 'tolerance-updated' event                     │
└─────────────────────────────────────────────────────────────────┘
```

---

## Core Services

### 1. DynamicStandardService

**Location**: `app/Services/DynamicStandardService.php`

**Responsibility**: Mengelola session-based adjustments terhadap template assessment

**Key Methods**:

```php
// GETTERS (with automatic fallback to database)
getCategoryWeight(int $templateId, string $categoryCode): int
getAspectWeight(int $templateId, string $aspectCode): int
getAspectRating(int $templateId, string $aspectCode): float
getSubAspectRating(int $templateId, string $subAspectCode): int
isAspectActive(int $templateId, string $aspectCode): bool
isSubAspectActive(int $templateId, string $subAspectCode): bool

// SETTERS (smart saving - only if different from original)
saveCategoryWeight(int $templateId, string $categoryCode, int $weight): void
saveAspectWeight(int $templateId, string $aspectCode, int $weight): void
saveAspectRating(int $templateId, string $aspectCode, float $rating): void
saveSubAspectRating(int $templateId, string $subAspectCode, int $rating): void
setAspectActive(int $templateId, string $aspectCode, bool $active): void
setSubAspectActive(int $templateId, string $subAspectCode, bool $active): void

// RESET
resetCategoryWeights(int $templateId): void
resetCategoryAdjustments(int $templateId, string $categoryCode): void

// VALIDATION
hasCategoryAdjustments(int $templateId, string $categoryCode): bool
getActiveAspectIds(int $templateId, string $categoryCode): array
```

**Session Storage**:
```php
Session::get("standard_adjustment.{$templateId}") = [
    'category_weights' => ['potensi' => 60, 'kompetensi' => 40],
    'aspect_weights' => ['asp_pot_01' => 15, 'asp_pot_02' => 20],
    'aspect_ratings' => ['asp_kom_01' => 4.5],
    'sub_aspect_ratings' => ['sub_pot_01_01' => 4],
    'active_aspects' => ['asp_pot_01' => false], // false = inactive
    'active_sub_aspects' => ['sub_pot_01_01' => false],
    'adjusted_at' => '2025-01-14 10:30:00',
];
```

---

### 2. IndividualAssessmentService

**Location**: `app/Services/IndividualAssessmentService.php`

**Responsibility**: Single Source of Truth untuk kalkulasi assessment individual participant

**Key Methods**:

```php
// Aspect-level detail
getAspectAssessments(
    int $participantId,
    int $categoryTypeId,
    int $tolerancePercentage = 10
): Collection

// Returns array dengan struktur:
[
    'aspect_id' => 1,
    'aspect_code' => 'asp_pot_01',
    'name' => 'Aspek Potensi 1',
    'weight_percentage' => 15,           // Adjusted
    'original_weight' => 10,             // Original dari DB
    'is_weight_adjusted' => true,
    'original_standard_rating' => 4.0,
    'original_standard_score' => 40.0,
    'standard_rating' => 3.6,            // Adjusted with tolerance
    'standard_score' => 54.0,            // Rating × Weight
    'individual_rating' => 4.5,
    'individual_score' => 67.5,          // Rating × Weight
    'gap_rating' => 0.9,
    'gap_score' => 13.5,
    'original_gap_rating' => 0.5,
    'original_gap_score' => 27.5,
    'percentage_score' => 125.0,
    'conclusion_text' => 'Di Atas Standar',
]

// Category-level totals
getCategoryAssessment(
    int $participantId,
    string $categoryCode,
    int $tolerancePercentage = 10
): array

// Returns:
[
    'category_code' => 'potensi',
    'category_name' => 'Potensi',
    'aspect_count' => 5,
    'total_standard_rating' => 20.0,
    'total_standard_score' => 200.0,
    'total_individual_rating' => 22.5,
    'total_individual_score' => 225.0,
    'total_gap_rating' => 2.5,
    'total_gap_score' => 25.0,
    'overall_conclusion' => 'Di Atas Standar',
    'aspects' => [...], // Array dari getAspectAssessments()
]

// Final assessment (Potensi + Kompetensi combined)
getFinalAssessment(
    int $participantId,
    int $tolerancePercentage = 10
): array

// Returns:
[
    'participant_id' => 123,
    'template_id' => 1,
    'tolerance_percentage' => 10,
    'potensi_weight' => 60,              // Adjusted
    'kompetensi_weight' => 40,           // Adjusted
    'potensi' => [...],                  // getCategoryAssessment('potensi')
    'kompetensi' => [...],               // getCategoryAssessment('kompetensi')
    'total_standard_score' => 400.0,     // Weighted sum
    'total_individual_score' => 450.0,   // Weighted sum
    'total_gap_score' => 50.0,
    'achievement_percentage' => 112.5,
    'final_conclusion' => 'Memenuhi Syarat',
]
```

**Integration dengan DynamicStandardService**:
- ✅ Selalu baca dari `DynamicStandardService` untuk setiap request
- ✅ Tidak cache session data di service level
- ✅ Otomatis fallback ke database jika session kosong

---

### 3. RankingService

**Location**: `app/Services/RankingService.php`

**Responsibility**: Single Source of Truth untuk ranking calculations

**Key Methods**:

```php
// Get all participant rankings
getRankings(
    int $eventId,
    int $positionFormationId,
    int $templateId,
    string $categoryCode,
    int $tolerancePercentage = 10
): Collection

// Returns array dengan struktur:
[
    'rank' => 1,
    'participant_id' => 123,
    'individual_rating' => 45.0,
    'individual_score' => 450.0,
    'original_standard_rating' => 40.0,
    'original_standard_score' => 400.0,
    'adjusted_standard_rating' => 36.0,  // With tolerance
    'adjusted_standard_score' => 360.0,
    'original_gap_score' => 50.0,
    'adjusted_gap_score' => 90.0,
    'percentage' => 125.0,
    'conclusion' => 'Di Atas Standar',
]

// Get specific participant rank
getParticipantRank(
    int $participantId,
    int $eventId,
    int $positionFormationId,
    int $templateId,
    string $categoryCode,
    int $tolerancePercentage = 10
): ?array

// Returns:
[
    'rank' => 5,
    'total' => 20,
    'conclusion' => 'Memenuhi Standar',
    'percentage' => 92.5,
    'individual_score' => 370.0,
    'adjusted_standard_score' => 360.0,
    'adjusted_gap_score' => 10.0,
]

// Helper methods
getPassingSummary(Collection $rankings): array
getConclusionSummary(Collection $rankings): array
```

**Calculation Logic**:
- ✅ Recalculates individual scores with adjusted weights from session (not from database)
- ✅ Filters inactive sub-aspects for Potensi category
- ✅ Consistent with IndividualAssessmentService calculation

**Ordering Logic**:
```php
// CONSISTENT: Score DESC → Name ASC (tiebreaker)
->sortBy([
    ['individual_score', 'desc'],
    ['participant_name', 'asc'],
])
```

---

## Calculation Levels

### Level 1: Sub-Aspect Rating (Potensi Only)

**Formula**:
```
Aspect Rating = Average(Active Sub-Aspect Ratings)
```

**Example**:
```
Sub-Aspects (3 total, 2 active):
- Sub 01: 4 (active)    ← Adjusted dari session
- Sub 02: 5 (active)    ← Adjusted dari session
- Sub 03: 3 (inactive)  ← Skip

Aspect Rating = (4 + 5) / 2 = 4.5
```

**Code** ([IndividualAssessmentService.php:185-227](app/Services/IndividualAssessmentService.php#L185-L227)):
```php
private function calculatePotensiRatings(
    AspectAssessment $assessment,
    int $templateId,
    DynamicStandardService $standardService
): array {
    $activeSubAspectsStandardSum = 0;
    $activeSubAspectsIndividualSum = 0;
    $activeSubAspectsCount = 0;

    foreach ($assessment->subAspectAssessments as $subAssessment) {
        // ✅ Check if sub-aspect is active
        if (!$standardService->isSubAspectActive($templateId, $subAssessment->subAspect->code)) {
            continue; // Skip inactive
        }

        // ✅ Get adjusted rating from session
        $adjustedSubStandardRating = $standardService->getSubAspectRating(
            $templateId,
            $subAssessment->subAspect->code
        );

        $activeSubAspectsStandardSum += $adjustedSubStandardRating;
        $activeSubAspectsIndividualSum += $subAssessment->individual_rating;
        $activeSubAspectsCount++;
    }

    if ($activeSubAspectsCount > 0) {
        return [
            round($activeSubAspectsStandardSum / $activeSubAspectsCount, 2), // Standard
            round($activeSubAspectsIndividualSum / $activeSubAspectsCount, 2), // Individual
        ];
    }

    return [null, null]; // Fallback
}
```

**🔑 Key Points**:
- Only **active** sub-aspects counted
- Ratings adjusted via `DynamicStandardService`
- Applies to **Potensi** category only (Kompetensi has direct aspect rating)

---

### Level 2: Aspect Score

**Formula**:
```
Aspect Score = Aspect Rating × Adjusted Weight
```

**Example**:
```
Aspect Rating: 4.5 (calculated from sub-aspects)
Aspect Weight: 15% (adjusted from 10% original)

Aspect Score = 4.5 × 15 = 67.5
```

**Code** ([IndividualAssessmentService.php:132-134](app/Services/IndividualAssessmentService.php#L132-L134)):
```php
// Get adjusted weight
$adjustedWeight = $standardService->getAspectWeight($templateId, $aspect->code);

// Calculate scores
$originalStandardScore = round($originalStandardRating * $adjustedWeight, 2);
$individualScore = round($individualRating * $adjustedWeight, 2);
```

**🔑 Key Points**:
- Weight always dari `DynamicStandardService` (adjusted or original)
- Applies to both Potensi & Kompetensi

---

### Level 3: Category Totals

**Formula**:
```
Total Category Rating = Sum(Active Aspect Ratings)
Total Category Score = Sum(Active Aspect Scores)
```

**Example**:
```
Potensi (5 aspects, 1 inactive):
- Aspect 01: Rating 4.5, Score 67.5  (active)
- Aspect 02: Rating 4.0, Score 60.0  (active)
- Aspect 03: Rating 3.5, Score 52.5  (active)
- Aspect 04: Rating 5.0, Score 75.0  (active)
- Aspect 05: Rating 3.0, Score 45.0  (inactive) ← Skip

Total Rating = 4.5 + 4.0 + 3.5 + 5.0 = 17.0
Total Score = 67.5 + 60.0 + 52.5 + 75.0 = 255.0
```

**Code** ([IndividualAssessmentService.php:263-282](app/Services/IndividualAssessmentService.php#L263-L282)):
```php
public function getCategoryAssessment(...): array {
    // Get aspect assessments (already filtered for active only)
    $aspectAssessments = $this->getAspectAssessments(
        $participantId,
        $categoryTypeId,
        $tolerancePercentage
    );

    // Sum totals
    foreach ($aspectAssessments as $aspect) {
        $totalStandardRating += $aspect['standard_rating'];
        $totalStandardScore += $aspect['standard_score'];
        $totalIndividualRating += $aspect['individual_rating'];
        $totalIndividualScore += $aspect['individual_score'];
        $totalGapRating += $aspect['gap_rating'];
        $totalGapScore += $aspect['gap_score'];
    }

    return [
        'total_standard_score' => round($totalStandardScore, 2),
        'total_individual_score' => round($totalIndividualScore, 2),
        // ...
    ];
}
```

**🔑 Key Points**:
- Only **active** aspects included (filtered at getAspectAssessments level)
- Simple summation

---

### Level 4: Weighted Category Scores

**Formula**:
```
Weighted Category Score = Total Category Score × (Category Weight / 100)
```

**Example**:
```
Potensi:
- Total Score: 255.0
- Category Weight: 60% (adjusted from 50% original)
- Weighted Score: 255.0 × 0.6 = 153.0

Kompetensi:
- Total Score: 300.0
- Category Weight: 40% (adjusted from 50% original)
- Weighted Score: 300.0 × 0.4 = 120.0
```

**Code** ([IndividualAssessmentService.php:337-347](app/Services/IndividualAssessmentService.php#L337-L347)):
```php
// ✅ Get adjusted category weights from DynamicStandardService
$standardService = app(DynamicStandardService::class);
$potensiWeight = $standardService->getCategoryWeight($template->id, 'potensi');
$kompetensiWeight = $standardService->getCategoryWeight($template->id, 'kompetensi');

// Calculate weighted scores
$totalStandardScore = round(
    ($potensiAssessment['total_standard_score'] * ($potensiWeight / 100)) +
    ($kompetensiAssessment['total_standard_score'] * ($kompetensiWeight / 100)),
    2
);

$totalIndividualScore = round(
    ($potensiAssessment['total_individual_score'] * ($potensiWeight / 100)) +
    ($kompetensiAssessment['total_individual_score'] * ($kompetensiWeight / 100)),
    2
);
```

**🔑 Key Points**:
- Category weights **must** be from `DynamicStandardService` (bug fixed!)
- Total must = 100% (validation in DynamicStandardService)

---

### Level 5: Final Individual Score

**Formula**:
```
Final Score = Weighted Potensi Score + Weighted Kompetensi Score
```

**Example**:
```
Weighted Potensi Score: 153.0
Weighted Kompetensi Score: 120.0

Final Individual Score = 153.0 + 120.0 = 273.0
```

**Code**: Same as Level 4 (already summed)

**Final Conclusion Logic**:
```php
if ($achievementPercentage < 80) {
    return 'Tidak Memenuhi Syarat';
} elseif ($achievementPercentage < 90) {
    return 'Masih Memenuhi Syarat';
} else {
    return 'Memenuhi Syarat';
}

// Achievement % = (Individual Score / Standard Score) × 100
```

---

## Dynamic Standards Integration

### How Dynamic Standards Affect Calculations

**Every calculation level reads from DynamicStandardService**:

| Level | What Changes | How Service Handles It |
|-------|--------------|------------------------|
| **Sub-Aspect** | - Active status<br>- Ratings | `isSubAspectActive()`<br>`getSubAspectRating()` |
| **Aspect** | - Active status<br>- Weights<br>- Ratings (MC) | `isAspectActive()`<br>`getAspectWeight()`<br>`getAspectRating()` |
| **Category** | - Weights | `getCategoryWeight()` |

### Data Flow Diagram

```
DynamicStandardService (Session Storage)
    ↓
┌───────────────────────────────────────────────────────────┐
│  Every Service Call Reads Fresh from Session              │
│                                                            │
│  getAspectAssessments() {                                 │
│      $standardService = app(DynamicStandardService);      │
│      ↓                                                     │
│      foreach aspects:                                      │
│          weight = $standardService->getAspectWeight()     │ ← Read from session
│          rating = $standardService->getAspectRating()     │ ← Read from session
│          isActive = $standardService->isAspectActive()    │ ← Read from session
│          ↓                                                 │
│          if (!isActive) continue; // Skip                 │
│          ↓                                                 │
│          calculate score = rating × weight                │
│  }                                                         │
└───────────────────────────────────────────────────────────┘
    ↓
Result: Fresh calculation dengan adjusted values
```

### Example: Standard Change Impact

**Scenario**: User changes sub-aspect rating

```php
// Before Change (Session empty)
$standardService->getSubAspectRating(1, 'sub_01'); // Returns 4 (from DB)
→ Aspect Rating = 4.0
→ Aspect Score = 4.0 × 10 = 40.0

// User saves new rating
$standardService->saveSubAspectRating(1, 'sub_01', 5);

// After Change (Session has adjustment)
$standardService->getSubAspectRating(1, 'sub_01'); // Returns 5 (from session)
→ Aspect Rating = 5.0
→ Aspect Score = 5.0 × 10 = 50.0

// Impact propagates through all levels:
Level 1: Sub-aspect rating 5.0 (was 4.0)
Level 2: Aspect score 50.0 (was 40.0)
Level 3: Category score +10.0
Level 4: Weighted category score +6.0 (if weight 60%)
Level 5: Final score +6.0
```

---

## Cache Management

### Why Cache is Needed

**Problem**: Service calls are expensive
- Database queries for assessments
- Multiple DynamicStandardService calls per aspect
- Chart data preparation

**Solution**: Cache results per Livewire request

### Cache Levels

```
┌─────────────────────────────────────────────────────────┐
│  SESSION LEVEL (DynamicStandardService)                 │
│  - Stores: Standard adjustments                         │
│  - Lifetime: Until user resets or session expires       │
│  - Cleared by: resetCategoryAdjustments()               │
└─────────────────────────────────────────────────────────┘
                         ↓ (reads from)
┌─────────────────────────────────────────────────────────┐
│  SERVICE LEVEL (IndividualAssessmentService)            │
│  - Cache: NONE (always read fresh from session)         │
│  - Why: Ensures latest adjusted values                  │
└─────────────────────────────────────────────────────────┘
                         ↓ (called by)
┌─────────────────────────────────────────────────────────┐
│  LIVEWIRE LEVEL (GeneralPsyMapping, etc.)               │
│  - Cache: $aspectsDataCache, $participantRankingCache   │
│  - Lifetime: Single request only                        │
│  - Cleared by: clearCache() method                      │
└─────────────────────────────────────────────────────────┘
```

### Livewire Cache Implementation

**Pattern**:
```php
class GeneralPsyMapping extends Component
{
    // Cache properties
    private ?array $aspectsDataCache = null;
    private ?array $participantRankingCache = null;

    /**
     * Clear all caches
     */
    private function clearCache(): void
    {
        $this->aspectsDataCache = null;
        $this->participantRankingCache = null;
    }

    /**
     * Load data with caching
     */
    private function loadAspectsData(): void
    {
        // ✅ Check cache first
        if ($this->aspectsDataCache !== null) {
            $this->aspectsData = $this->aspectsDataCache;
            return;
        }

        // ❌ Cache miss - call service
        $service = app(IndividualAssessmentService::class);
        $data = $service->getAspectAssessments(
            $this->participant->id,
            $this->categoryId,
            $this->tolerancePercentage
        )->toArray();

        // Store in cache
        $this->aspectsData = $data;
        $this->aspectsDataCache = $data;
    }
}
```

### When to Clear Cache

**Event-Driven Cache Invalidation**:

```php
protected $listeners = [
    'tolerance-updated' => 'handleToleranceUpdate',
    'standard-adjusted' => 'handleStandardUpdate', // ← CRITICAL!
];

/**
 * Handle tolerance update
 */
public function handleToleranceUpdate(int $tolerance): void
{
    $this->tolerancePercentage = $tolerance;

    // ✅ Clear cache before reload
    $this->clearCache();

    // Reload data (will call service fresh)
    $this->loadAspectsData();
    $this->calculateTotals();
    $this->prepareChartData();

    // Dispatch UI updates
    $this->dispatch('chartDataUpdated', [...]);
}

/**
 * Handle standard adjustment
 */
public function handleStandardUpdate(int $templateId): void
{
    // Validate same template
    if ($this->participant->positionFormation->template_id !== $templateId) {
        return;
    }

    // ✅ Clear cache before reload
    $this->clearCache();

    // Reload data (will call service fresh with new session values)
    $this->loadAspectsData();
    $this->calculateTotals();
    $this->prepareChartData();

    // Dispatch UI updates
    $this->dispatch('chartDataUpdated', [...]);
}
```

### Cache Invalidation Flow

```
User edits standard
    ↓
DynamicStandardService->save...() → Update session
    ↓
Dispatch 'standard-adjusted' event (with templateId)
    ↓
Livewire listens → handleStandardUpdate()
    ↓
clearCache() → Set cache properties to null
    ↓
loadAspectsData() → Cache miss
    ↓
Call IndividualAssessmentService (reads fresh from session)
    ↓
Store new result in cache
    ↓
Update UI with fresh data ✅
```

### ⚠️ Common Cache Pitfall

**WRONG (Missing cache invalidation)**:
```php
// ❌ BAD: No listener for 'standard-adjusted'
protected $listeners = [
    'tolerance-updated' => 'handleToleranceUpdate',
    // Missing: 'standard-adjusted' => 'handleStandardUpdate'
];

// Result: User edits standard, data NOT updated!
```

**CORRECT**:
```php
// ✅ GOOD: Listen to both events
protected $listeners = [
    'tolerance-updated' => 'handleToleranceUpdate',
    'standard-adjusted' => 'handleStandardUpdate', // ← MUST HAVE
];
```

---

## Standard Adjustment Flow

### Complete Flow from User Action to Data Update

```
┌─────────────────────────────────────────────────────────────┐
│ STEP 1: User Edits Standard                                 │
├─────────────────────────────────────────────────────────────┤
│ Location: StandardPsikometrik.php / StandardMc.php          │
│                                                              │
│ User clicks "Edit Sub-Aspect Rating"                        │
│    ↓                                                         │
│ Modal opens → User inputs new value (e.g., 4 → 5)          │
│    ↓                                                         │
│ saveSubAspectRating()                                        │
└─────────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ STEP 2: Save to Session                                     │
├─────────────────────────────────────────────────────────────┤
│ Location: DynamicStandardService.php                        │
│                                                              │
│ saveSubAspectRating(templateId, 'sub_01', 5)                │
│    ↓                                                         │
│ Compare with original (DB): 5 !== 4                         │
│    ↓                                                         │
│ Save to session: adjustments['sub_aspect_ratings']['sub_01'] = 5 │
│    ↓                                                         │
│ Set adjusted_at timestamp                                   │
└─────────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ STEP 3: Dispatch Event                                      │
├─────────────────────────────────────────────────────────────┤
│ Location: StandardPsikometrik.php                           │
│                                                              │
│ $this->dispatch('standard-adjusted', templateId: 1)         │
└─────────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ STEP 4: Livewire Listens & Clears Cache                    │
├─────────────────────────────────────────────────────────────┤
│ Location: GeneralPsyMapping.php                             │
│                                                              │
│ protected $listeners = [                                     │
│     'standard-adjusted' => 'handleStandardUpdate'           │
│ ];                                                           │
│    ↓                                                         │
│ handleStandardUpdate(templateId: 1)                          │
│    ↓                                                         │
│ Validate: Is this the same template? Yes                    │
│    ↓                                                         │
│ clearCache() → Set $aspectsDataCache = null                 │
└─────────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ STEP 5: Reload Data from Service                           │
├─────────────────────────────────────────────────────────────┤
│ Location: GeneralPsyMapping.php                             │
│                                                              │
│ loadAspectsData()                                            │
│    ↓                                                         │
│ Check cache: null (was cleared)                             │
│    ↓                                                         │
│ Call IndividualAssessmentService->getAspectAssessments()    │
└─────────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ STEP 6: Service Reads Fresh from Session                   │
├─────────────────────────────────────────────────────────────┤
│ Location: IndividualAssessmentService.php                   │
│                                                              │
│ getAspectAssessments()                                       │
│    ↓                                                         │
│ Get active aspect IDs (from DynamicStandardService)         │
│    ↓                                                         │
│ foreach aspect:                                              │
│    ├─ getAspectWeight() → Read from session                │
│    ├─ foreach sub-aspect:                                   │
│    │   ├─ isSubAspectActive() → Read from session          │
│    │   └─ getSubAspectRating() → Read 5 (new!) from session│
│    ├─ Calculate aspect rating = avg(5, ...) = 4.5          │
│    └─ Calculate aspect score = 4.5 × weight                │
│    ↓                                                         │
│ Return fresh calculated data ✅                             │
└─────────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ STEP 7: Cache & Display New Data                           │
├─────────────────────────────────────────────────────────────┤
│ Location: GeneralPsyMapping.php                             │
│                                                              │
│ Store in cache: $aspectsDataCache = [new data]              │
│    ↓                                                         │
│ calculateTotals() → Recalculate with new values             │
│    ↓                                                         │
│ prepareChartData() → Update chart arrays                    │
│    ↓                                                         │
│ dispatch('chartDataUpdated', [...]) → Update UI             │
│    ↓                                                         │
│ User sees updated scores ✅                                 │
└─────────────────────────────────────────────────────────────┘
```

### Factors That Trigger Recalculation

| Factor Changed | Affects | Example |
|----------------|---------|---------|
| **Sub-aspect rating** | Level 1→5 | Rating 4→5: Score +10.0 |
| **Sub-aspect active status** | Level 1→5 | Deactivate: Aspect rating recalculated from remaining |
| **Aspect rating** (MC) | Level 2→5 | Rating 3→4: Score +weight |
| **Aspect weight** | Level 2→5 | Weight 10%→15%: Score ×1.5 |
| **Aspect active status** | Level 3→5 | Deactivate: Category total reduced |
| **Category weight** | Level 4→5 | Potensi 50%→60%: Final score reweighted |
| **Tolerance %** | All levels | 10%→20%: Adjusted standard ×0.8 instead of ×0.9 |

### Data Consistency Guarantee

```
✅ GUARANTEED: Fresh data every time because:
1. Service NEVER caches session data
2. Service reads from DynamicStandardService on EVERY call
3. Livewire cache is cleared on EVERY standard change
4. Event-driven architecture ensures all components update
```

---

## Migration Guide

### Checklist untuk Migrate Component ke Service

**Before Migration**:
- [ ] Identify calculation methods in component
- [ ] Map old data structure to service output
- [ ] Write validation test (old vs new)

**Migration Steps**:

1. **Add Service Call**:
```php
// Old (manual calculation)
private function loadAspectsData(): void
{
    $aspects = AspectAssessment::where(...)
        ->with('aspect', 'subAspectAssessments')
        ->get();

    foreach ($aspects as $assessment) {
        // 100+ lines of manual calculation
        $standardRating = ...;
        $individualScore = ...;
    }
}

// New (use service)
private function loadAspectsData(): void
{
    // ✅ Single line replaces 100+ lines!
    $service = app(IndividualAssessmentService::class);

    $this->aspectsData = $service->getAspectAssessments(
        $this->participant->id,
        $this->categoryTypeId,
        $this->tolerancePercentage
    )->toArray();

    // Cache result
    $this->aspectsDataCache = $this->aspectsData;
}
```

2. **Add Event Listener**:
```php
protected $listeners = [
    'tolerance-updated' => 'handleToleranceUpdate',
    'standard-adjusted' => 'handleStandardUpdate', // ← ADD THIS
];

public function handleStandardUpdate(int $templateId): void
{
    // Validate same template
    if ($this->participant->positionFormation->template_id !== $templateId) {
        return;
    }

    // Clear cache & reload
    $this->clearCache();
    $this->loadAspectsData();
    $this->calculateTotals();
    $this->prepareChartData();

    // Update UI
    $this->dispatch('chartDataUpdated', [...]);
}
```

3. **Update Cache Management**:
```php
// Ensure cache is cleared on:
// 1. Tolerance update
// 2. Standard adjustment
// 3. Event/position change
private function clearCache(): void
{
    $this->aspectsDataCache = null;
    $this->participantRankingCache = null;
    // Add any other cache properties
}
```

4. **Test**:
- [ ] Unit tests pass
- [ ] Feature tests pass
- [ ] Manual testing: Edit standard → Data updates
- [ ] Manual testing: Change tolerance → Data updates

**After Migration**:
- [ ] Remove old calculation methods
- [ ] Update comments/documentation
- [ ] Run `vendor/bin/pint` formatter

---

## Troubleshooting

### Issue: Data not updating after standard change

**Symptoms**: User edits standard, but individual report shows old values

**Diagnosis**:
```php
// Check 1: Does component listen to 'standard-adjusted'?
protected $listeners = [
    'standard-adjusted' => 'handleStandardUpdate', // ← Must exist!
];

// Check 2: Does handler clear cache?
public function handleStandardUpdate(int $templateId): void
{
    $this->clearCache(); // ← Must be called!
    $this->loadAspectsData(); // ← Must reload!
}

// Check 3: Does loadAspectsData check cache correctly?
private function loadAspectsData(): void
{
    if ($this->aspectsDataCache !== null) {
        $this->aspectsData = $this->aspectsDataCache;
        return; // ← If cache not cleared, returns stale data!
    }
}
```

**Solution**: Add listener + clearCache() call

---

### Issue: Service returns unexpected values

**Symptoms**: Scores don't match manual calculation

**Diagnosis**:
```php
// Check 1: Are aspects active?
$standardService = app(DynamicStandardService::class);
$isActive = $standardService->isAspectActive($templateId, 'asp_01');
// If false, aspect is excluded from calculation

// Check 2: What are the adjusted values?
$weight = $standardService->getAspectWeight($templateId, 'asp_01');
$rating = $standardService->getAspectRating($templateId, 'asp_01');
dump([
    'weight' => $weight,
    'rating' => $rating,
    'expected_score' => $weight * $rating,
]);

// Check 3: What's in session?
$adjustments = $standardService->getAdjustments($templateId);
dd($adjustments);
```

**Common Causes**:
- Inactive aspects/sub-aspects
- Adjusted weights different from DB
- Tolerance applied (check tolerance %)

---

### Issue: Cache not clearing

**Symptoms**: Even after clearCache(), old data still shows

**Diagnosis**:
```php
// Check: Are cache properties correctly nulled?
private function clearCache(): void
{
    $this->aspectsDataCache = null;
    $this->participantRankingCache = null;

    // Debug: Force dump to verify
    dump('Cache cleared', [
        'aspectsDataCache' => $this->aspectsDataCache,
        'participantRankingCache' => $this->participantRankingCache,
    ]);
}
```

**Solution**: Ensure ALL cache properties are set to null

---

### Issue: Ranking order inconsistent

**Symptoms**: Same scores get different ranks on refresh

**Diagnosis**:
```php
// Check: Is tiebreaker applied?
// RankingService uses: Score DESC → Name ASC
$rankings = AspectAssessment::query()
    ->orderByDesc('sum_individual_score') // Primary
    ->orderByRaw('LOWER(participants.name) ASC') // Tiebreaker ← Must have!
    ->get();
```

**Solution**: Always use RankingService for consistent ordering

---

## Quick Reference

### When to Use Which Service

| Use Case | Service | Method |
|----------|---------|--------|
| Individual aspect details | IndividualAssessmentService | `getAspectAssessments()` |
| Category totals (Potensi/Kompetensi) | IndividualAssessmentService | `getCategoryAssessment()` |
| Final combined score | IndividualAssessmentService | `getFinalAssessment()` |
| All participants ranking | RankingService | `getRankings()` |
| Single participant rank | RankingService | `getParticipantRank()` |
| Passing summary | RankingService | `getPassingSummary()` |
| Edit standard values | DynamicStandardService | `save...()` methods |
| Check adjustments | DynamicStandardService | `hasCategoryAdjustments()` |

### Event Reference

| Event | Dispatched By | Listened By | Purpose |
|-------|---------------|-------------|---------|
| `'standard-adjusted'` | StandardPsikometrik, StandardMc | GeneralPsyMapping, RankingPsyMapping | Notify of standard changes |
| `'tolerance-updated'` | ToleranceSelector | GeneralPsyMapping, RankingPsyMapping | Notify of tolerance changes |
| `'event-selected'` | EventSelector | RankingPsyMapping | Filter data by event |
| `'position-selected'` | PositionSelector | RankingPsyMapping | Filter data by position |

### Session Keys

```php
// Standard adjustments (per template)
Session::get("standard_adjustment.{$templateId}");

// Filter selections (global)
Session::get("filter.event_code");
Session::get("filter.position_formation_id");

// Tolerance setting (global)
Session::get("individual_report.tolerance");
```

---

## Summary

### ✅ Key Principles

1. **Single Source of Truth**: All calculations via services
2. **Session-Based Standards**: No direct DB updates for adjustments
3. **Event-Driven Updates**: `'standard-adjusted'` triggers recalculation
4. **Smart Caching**: Cache at Livewire level, not service level
5. **Always Fresh**: Service reads fresh from session every call

### 🎯 Migration Status

| Component | Service Used | Listener Added | Code Reduction | Status |
|-----------|--------------|----------------|----------------|--------|
| GeneralPsyMapping | ✅ IndividualAssessmentService | ✅ `standard-adjusted` | ~123 lines | ✅ Done |
| GeneralMcMapping | ✅ IndividualAssessmentService | ✅ `standard-adjusted` | ~161 lines | ✅ Done |
| GeneralMapping | ✅ IndividualAssessmentService + RankingService | ✅ `standard-adjusted` | ~100 lines | ✅ Done |
| RankingPsyMapping | ✅ RankingService | ✅ `standard-adjusted` | ~180 lines | ✅ Done |
| RankingMcMapping | ✅ RankingService | ✅ `standard-adjusted` | ~193 lines | ✅ Done |
| RekapRankingAssessment | ❌ Mixed | ❌ Need listener | - | 🔴 Todo |

**Progress**: 5 of 6 components migrated (83%)
**Total Code Reduction**: ~757 lines removed

### 🚀 Next Steps

1. Migrate RekapRankingAssessment to services
2. Update exports (PDF/Excel) if needed

---

**Document Version**: 1.2
**Last Updated**: 2025-01-14
**Maintainer**: Development Team
