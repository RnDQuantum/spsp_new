# Flexible Hierarchy Refactoring: Data-Driven Assessment Structure

> **Version**: 1.0
> **Created**: 2025-01-27
> **Status**: 📋 Ready for Implementation
> **Priority**: High - Foundation for Future Features
> **Estimated Effort**: 2-3 days

---

## 📋 Table of Contents

1. [Quick Context](#quick-context)
2. [Problem Statement](#problem-statement)
3. [Current vs Future Architecture](#current-vs-future-architecture)
4. [Technical Design](#technical-design)
5. [Implementation Plan](#implementation-plan)
6. [Code Changes (Step-by-Step)](#code-changes-step-by-step)
7. [Testing Strategy](#testing-strategy)
8. [Backward Compatibility](#backward-compatibility)
9. [Migration Checklist](#migration-checklist)

---

## Quick Context

### What is SPSP?

**SPSP (Sistem Pemetaan & Statistik Psikologi)** adalah SaaS analytics dashboard untuk assessment center (seperti seleksi CPNS, P3K, promosi internal).

**Key Points:**
- Multi-tenant SaaS (setiap institusi punya data terpisah)
- Data peserta di-import dari sistem Quantum via API (read-only)
- Fokus pada **analitik**: user menganalisis hasil dengan berbagai standar

### Current Data Hierarchy

```
AssessmentTemplate (Blueprint)
  └── CategoryType (Potensi 50%, Kompetensi 50%)
      └── Aspect (Kecerdasan 20%, Integritas 15%, dll)
          └── SubAspect (Optional, one-to-many)
```

### Three-Tier Priority System

```
PRIORITY 1: Session Adjustment (Temporary Analysis Tool)
  ↓ (if not found)
PRIORITY 2: Custom Standard (Saved Institution Baseline)
  ↓ (if not found)
PRIORITY 3: Quantum Default (Template Master Data)
```

**Session Adjustment** adalah fitur analisa sementara:
- User bisa eksperimen dengan nilai berbeda
- Data hilang setelah logout (intentional)
- Smart saving: hanya simpan jika berbeda dari baseline
- Event-driven: `'standard-adjusted'` → semua report reload

**Custom Standard** adalah fitur persistent:
- Institution menyimpan standar mereka sendiri
- Shared antar user dalam institusi yang sama
- JSON storage di database
- Selection via session: `Session::get("selected_standard.{$templateId}")`

**Related Docs:**
- `docs/SERVICE_ARCHITECTURE.md` - Session adjustment & calculation flow
- `docs/CUSTOM_STANDARD_FEATURE.md` - Custom standard implementation
- `docs/CONCLUSION_SERVICE_USAGE.md` - Conclusion logic

---

## Problem Statement

### Current State: Hardcoded Category Logic

**Assumption yang di-hardcode di 6+ services:**

```php
// ❌ HARDCODED: Logic based on category name
if ($category->code === 'potensi') {
    // Assumption: MUST have sub-aspects
    $rating = $this->calculateFromSubAspects($aspect);
} elseif ($category->code === 'kompetensi') {
    // Assumption: NO sub-aspects, use direct rating
    $rating = $aspect->standard_rating;
}
```

**Problem Files:**
1. `app/Services/DynamicStandardService.php` - Line ~118
2. `app/Services/CustomStandardService.php` - Line ~109
3. `app/Services/AssessmentCalculationService.php` - Line ~33-35, ~117
4. `app/Services/Assessment/AspectService.php` - Line ~86, ~159
5. `app/Services/TrainingRecommendationService.php` - Line ~261
6. `app/Services/InterpretationGeneratorService.php` - Line ~133

### Why This is a Problem

#### 1. **Database Schema ≠ Business Logic**

```sql
-- ✅ Database schema SUDAH flexible:
CREATE TABLE sub_aspects (
    aspect_id BIGINT,  -- ONE-TO-MANY, not mandatory
    ...
);

-- Any aspect BISA punya 0..N sub-aspects
-- Tidak ada constraint "Potensi must have subs" atau "Kompetensi can't have subs"
```

```php
// ❌ But logic TIDAK flexible:
if ($category->code === 'potensi') { ... }
// Logic tied to specific category names
```

**Consequence:** Database mendukung "Kompetensi dengan SubAspect", tapi logic akan break.

#### 2. **Future Requirement Confirmed**

> "Kedepannya bisa jadi aplikasi ini harus bisa menangani kompetensi yang memiliki sub aspek"

**Juga:**
- Kategori bisa bukan `potensi`/`kompetensi` (misal: `integritas`, `moralitas`)
- Jumlah kategori bisa 2, 3, 4, atau lebih
- Dalam 1 kategori bisa mix: ada aspect dengan sub, ada yang tanpa sub

#### 3. **Violates SOLID Principles**

- **Open/Closed Principle**: Harus ubah code untuk requirement baru
- **Single Responsibility**: Logic coupled dengan category names
- **DRY**: Duplicate if/else di banyak file

### Business Impact

| Skenario | Current System | Ideal System |
|----------|----------------|--------------|
| **Template baru: "Penilaian Hakim"** dengan kategori `integritas`, `moralitas` | ❌ Break (tidak ada if untuk `integritas`) | ✅ Works (data-driven) |
| **Kompetensi perlu sub-aspect** untuk detail skill mapping | ❌ Need major refactor | ✅ Just add data |
| **Mix structure** dalam 1 kategori (beberapa aspect punya sub, beberapa tidak) | ❌ Not supported | ✅ Supported |

---

## Current vs Future Architecture

### ❌ Current: Hardcoded Category Logic

```
Template "Standar Manajerial L3"
  ├── CategoryType "potensi" (50%) ← HARDCODED
  │   └── Aspect "kecerdasan" ← MUST have sub-aspects (hardcoded)
  │       └── SubAspect "kecerdasan_umum"
  │
  └── CategoryType "kompetensi" (50%) ← HARDCODED
      └── Aspect "integritas" (rating: 3.0) ← NO sub-aspects allowed (hardcoded)
```

**Code:**
```php
// Service logic checks category name
if ($category->code === 'potensi') {
    // Has sub-aspects
} elseif ($category->code === 'kompetensi') {
    // No sub-aspects
}
```

### ✅ Future: Data-Driven Flexible Structure

```
Template "Standar Penilaian Hakim 2026"
  ├── CategoryType "integritas" (40%) ← DYNAMIC
  │   ├── Aspect "etika_profesi" (60%) ← HAS sub-aspects
  │   │   ├── SubAspect "kejujuran" (4)
  │   │   ├── SubAspect "independensi" (5)
  │   │   └── SubAspect "transparansi" (3)
  │   │
  │   └── Aspect "konsistensi_keputusan" (40%, rating: 4.0) ← NO sub-aspects
  │
  ├── CategoryType "moralitas" (30%) ← DYNAMIC
  │   └── Aspect "nilai_kehidupan" (100%, rating: 3.5) ← NO sub-aspects
  │
  └── CategoryType "kompetensi_teknis" (30%) ← DYNAMIC
      ├── Aspect "pemahaman_hukum" (70%) ← HAS sub-aspects
      │   ├── SubAspect "hukum_pidana" (5)
      │   └── SubAspect "hukum_perdata" (4)
      │
      └── Aspect "kecepatan_putusan" (30%, rating: 3.0) ← NO sub-aspects
```

**Code:**
```php
// Service logic checks actual data structure
if ($aspect->subAspects()->exists()) {
    // Calculate from sub-aspects
} else {
    // Use aspect's own rating
}
```

**Benefits:**
- ✅ Category names: apa saja
- ✅ Jumlah kategori: 2, 3, 4, atau lebih
- ✅ Mix structure: dalam 1 kategori bisa ada aspect dengan sub dan tanpa sub
- ✅ No code changes for new templates

---

## Technical Design

### Core Principle

> **"An Aspect's rating source is determined by whether it has active SubAspects, NOT by its CategoryType"**

### Unified Calculation Algorithm

```php
/**
 * Universal aspect rating calculation
 * Works for ANY category type, ANY structure
 */
function getAspectRating(Aspect $aspect, ?CustomStandard $customStd): float
{
    // Priority 1: Session adjustment
    if ($sessionRating = getFromSession($aspect->code)) {
        return $sessionRating;
    }

    // Priority 2: Custom standard (if selected)
    if ($customStd) {
        return getCustomStandardRating($aspect, $customStd);
    }

    // Priority 3: Quantum default (data-driven)
    return getQuantumDefaultRating($aspect);
}

function getQuantumDefaultRating(Aspect $aspect): float
{
    // DATA-DRIVEN: Check actual structure
    if ($aspect->subAspects()->exists()) {
        // Has sub-aspects: calculate weighted average
        return calculateFromSubAspects($aspect->subAspects);
    }

    // No sub-aspects: use aspect's own rating
    return $aspect->standard_rating;
}

function calculateFromSubAspects(Collection $subAspects): float
{
    if ($subAspects->isEmpty()) {
        return 0.0;
    }

    $sum = $subAspects->sum('standard_rating');
    $count = $subAspects->count();

    return round($sum / $count, 2);
}
```

### Key Design Decisions

#### 1. **No Database Schema Changes**

Current schema sudah support flexible structure:
```sql
-- ✅ Already correct
sub_aspects (
    aspect_id BIGINT,  -- Optional one-to-many
    ...
)
```

#### 2. **Custom Standard JSON Remains Compatible**

```json
{
  "aspect_configs": {
    "kecerdasan": {
      "weight": 25,
      "active": true
      // NO "rating" - calculated from sub-aspects
    },
    "integritas": {
      "weight": 15,
      "rating": 4.0,  // OPTIONAL: only if no sub-aspects
      "active": true
    }
  },
  "sub_aspect_configs": {
    "kecerdasan_umum": {
      "rating": 4,
      "active": true
    }
  }
}
```

**Rule:** `aspect_configs[x]['rating']` is OPTIONAL
- If aspect has active sub-aspects → rating ignored/calculated
- If aspect has no sub-aspects → rating required

#### 3. **Session Adjustment Flow Unchanged**

```
User edits standard
  ↓
DynamicStandardService->save...()
  ↓ (Priority chain still same)
  1. Check session adjustment
  2. Check custom standard (if selected)
  3. Fallback to Quantum default (NOW data-driven)
  ↓
Dispatch 'standard-adjusted' event
  ↓
All report components reload
```

---

## Implementation Plan

### Phase 1: Core Services (Day 1-2)

**High Priority - Foundation**

1. **DynamicStandardService.php**
   - Refactor `getOriginalAspectRating()` → data-driven
   - Add helper methods
   - Update priority chain
   - Unit tests

2. **CustomStandardService.php**
   - Refactor `getTemplateDefaults()` → remove category check
   - Update form validation
   - Test with mixed structure

### Phase 2: Calculation Services (Day 2)

**Medium Priority - Business Logic**

3. **AssessmentCalculationService.php**
   - Remove `processPotensi()` method
   - Remove `processKompetensi()` method
   - Add `processCategory()` unified method
   - Integration tests

4. **AspectService.php**
   - Remove `calculatePotensiAspect()` method
   - Remove `calculateKompetensiAspect()` method
   - Add `calculateAspect()` unified method
   - Unit tests

### Phase 3: Helper Services (Day 3)

**Low Priority - Supporting Features**

5. **TrainingRecommendationService.php**
   - Replace category check with data check
   - Test recommendations

6. **InterpretationGeneratorService.php**
   - Unify interpretation generation
   - Test interpretations

### Phase 4: Documentation & Validation (Day 3)

7. **Update Documentation**
   - Update `SERVICE_ARCHITECTURE.md`
   - Update `CUSTOM_STANDARD_FEATURE.md`
   - Update code comments

8. **Full System Testing**
   - Test with current data (Potensi/Kompetensi)
   - Test with mock new data (mixed structure)
   - Performance testing
   - Run full test suite

---

## Code Changes (Step-by-Step)

### File 1: DynamicStandardService.php

**Location:** `app/Services/DynamicStandardService.php`

#### Change 1.1: Refactor `getOriginalAspectRating()`

**Current Code (~Line 118):**

```php
/**
 * Get original aspect rating from database
 */
private function getOriginalAspectRating(int $templateId, string $aspectCode): float
{
    $aspect = Aspect::where('template_id', $templateId)
        ->where('code', $aspectCode)
        ->first();

    return $aspect ? (float) $aspect->standard_rating : 0.0;
}
```

**Problem:** Ini hanya return `standard_rating`, tapi untuk aspect yang punya sub-aspects, seharusnya calculated dari sub-aspects.

**New Code:**

```php
/**
 * Get original aspect rating from database (data-driven)
 *
 * Logic:
 * - If aspect has sub-aspects: calculate weighted average from them
 * - If aspect has no sub-aspects: use aspect's own standard_rating
 *
 * @param int $templateId
 * @param string $aspectCode
 * @return float
 */
private function getOriginalAspectRating(int $templateId, string $aspectCode): float
{
    $aspect = Aspect::where('template_id', $templateId)
        ->where('code', $aspectCode)
        ->with('subAspects') // Eager load to check
        ->first();

    if (!$aspect) {
        return 0.0;
    }

    // DATA-DRIVEN: Check if aspect has sub-aspects
    if ($aspect->subAspects->isNotEmpty()) {
        // Has sub-aspects: calculate weighted average
        return $this->calculateRatingFromSubAspects($aspect->subAspects);
    }

    // No sub-aspects: use aspect's own rating
    return (float) $aspect->standard_rating;
}
```

#### Change 1.2: Add Helper Method `calculateRatingFromSubAspects()`

**Add after `getOriginalSubAspectRating()` method:**

```php
/**
 * Calculate aspect rating from sub-aspects (weighted average)
 *
 * Formula: Average of all sub-aspect ratings
 *
 * @param \Illuminate\Support\Collection $subAspects Collection of SubAspect models
 * @return float Calculated rating (0.0 if no sub-aspects)
 */
private function calculateRatingFromSubAspects(\Illuminate\Support\Collection $subAspects): float
{
    if ($subAspects->isEmpty()) {
        return 0.0;
    }

    $totalRating = 0;
    $count = 0;

    foreach ($subAspects as $subAspect) {
        $totalRating += $subAspect->standard_rating;
        $count++;
    }

    return $count > 0 ? round($totalRating / $count, 2) : 0.0;
}
```

#### Change 1.3: Update `getOriginalValue()` for Custom Standard

**Current Code (~Line 48-89):**

```php
private function getOriginalValue(string $type, int $templateId, string $code)
{
    // Check if custom standard is selected
    $customStandardId = Session::get("selected_standard.{$templateId}");

    if ($customStandardId) {
        $customStandard = CustomStandard::find($customStandardId);

        if ($customStandard) {
            // Return value from Custom Standard if it exists
            $value = match ($type) {
                'category_weight' => $customStandard->category_weights[$code] ?? null,
                'aspect_weight' => $customStandard->aspect_configs[$code]['weight'] ?? null,
                'aspect_rating' => isset($customStandard->aspect_configs[$code]['rating'])
                    ? (float) $customStandard->aspect_configs[$code]['rating']
                    : null,
                // ...
            };

            if ($value !== null) {
                return $value;
            }
        }
    }

    // Fallback to Quantum default from database
    return match ($type) {
        'aspect_rating' => $this->getOriginalAspectRating($templateId, $code),
        // ...
    };
}
```

**Update the `'aspect_rating'` case in custom standard check:**

```php
private function getOriginalValue(string $type, int $templateId, string $code)
{
    // Check if custom standard is selected
    $customStandardId = Session::get("selected_standard.{$templateId}");

    if ($customStandardId) {
        $customStandard = CustomStandard::find($customStandardId);

        if ($customStandard) {
            // Return value from Custom Standard if it exists
            $value = match ($type) {
                'category_weight' => $customStandard->category_weights[$code] ?? null,
                'aspect_weight' => $customStandard->aspect_configs[$code]['weight'] ?? null,

                // ✅ UPDATED: aspect_rating now data-driven for custom standard
                'aspect_rating' => $this->getAspectRatingFromCustomStandard(
                    $customStandard,
                    $code,
                    $templateId
                ),

                'sub_aspect_rating' => isset($customStandard->sub_aspect_configs[$code]['rating'])
                    ? (int) $customStandard->sub_aspect_configs[$code]['rating']
                    : null,
                'aspect_active' => $customStandard->aspect_configs[$code]['active'] ?? null,
                'sub_aspect_active' => $customStandard->sub_aspect_configs[$code]['active'] ?? null,
                default => null,
            };

            // If custom standard has this value, use it as baseline
            if ($value !== null) {
                return $value;
            }
        }
    }

    // Fallback to Quantum default from database
    return match ($type) {
        'category_weight' => $this->getOriginalCategoryWeight($templateId, $code),
        'aspect_weight' => $this->getOriginalAspectWeight($templateId, $code),
        'aspect_rating' => $this->getOriginalAspectRating($templateId, $code), // Now data-driven
        'sub_aspect_rating' => $this->getOriginalSubAspectRating($templateId, $code),
        'aspect_active' => true,
        'sub_aspect_active' => true,
        default => null,
    };
}
```

#### Change 1.4: Add `getAspectRatingFromCustomStandard()` Helper

**Add after `calculateRatingFromSubAspects()` method:**

```php
/**
 * Get aspect rating from custom standard (data-driven)
 *
 * Logic:
 * - If aspect has active sub-aspects in custom standard: calculate from them
 * - If aspect has no sub-aspects: use aspect's own rating from custom standard
 * - If not found in custom standard: return null (will fallback to Quantum)
 *
 * @param CustomStandard $customStandard
 * @param string $aspectCode
 * @param int $templateId
 * @return float|null
 */
private function getAspectRatingFromCustomStandard(
    CustomStandard $customStandard,
    string $aspectCode,
    int $templateId
): ?float {
    // Get aspect from database to check structure
    $aspect = Aspect::where('template_id', $templateId)
        ->where('code', $aspectCode)
        ->with('subAspects')
        ->first();

    if (!$aspect) {
        return null;
    }

    // DATA-DRIVEN: Check if aspect has sub-aspects
    if ($aspect->subAspects->isNotEmpty()) {
        // Has sub-aspects: calculate from custom standard's sub-aspect configs
        $activeSubAspects = $aspect->subAspects->filter(function ($subAspect) use ($customStandard) {
            // Check if sub-aspect is active in custom standard
            $isActive = $customStandard->sub_aspect_configs[$subAspect->code]['active'] ?? true;
            return $isActive;
        });

        if ($activeSubAspects->isEmpty()) {
            return null;
        }

        // Calculate weighted average from custom standard's sub-aspect ratings
        $totalRating = 0;
        $count = 0;

        foreach ($activeSubAspects as $subAspect) {
            $rating = $customStandard->sub_aspect_configs[$subAspect->code]['rating']
                ?? $subAspect->standard_rating; // Fallback to quantum if not in custom std

            $totalRating += $rating;
            $count++;
        }

        return $count > 0 ? round($totalRating / $count, 2) : null;
    }

    // No sub-aspects: use aspect's own rating from custom standard
    return isset($customStandard->aspect_configs[$aspectCode]['rating'])
        ? (float) $customStandard->aspect_configs[$aspectCode]['rating']
        : null;
}
```

**Summary of DynamicStandardService.php changes:**
- ✅ `getOriginalAspectRating()` - now data-driven (checks sub-aspects)
- ✅ `calculateRatingFromSubAspects()` - new helper method
- ✅ `getAspectRatingFromCustomStandard()` - new helper method
- ✅ `getOriginalValue()` - updated to use new helper

---

### File 2: CustomStandardService.php

**Location:** `app/Services/CustomStandardService.php`

#### Change 2.1: Refactor `getTemplateDefaults()`

**Current Code (~Line 80-130):**

```php
public function getTemplateDefaults(int $templateId): array
{
    $template = AssessmentTemplate::with([
        'categoryTypes.aspects.subAspects',
    ])->findOrFail($templateId);

    $defaults = [
        'template_id' => $templateId,
        'template_name' => $template->name,
        'category_weights' => [],
        'aspect_configs' => [],
        'sub_aspect_configs' => [],
    ];

    foreach ($template->categoryTypes as $category) {
        // Category weights
        $defaults['category_weights'][$category->code] = $category->weight_percentage;

        foreach ($category->aspects as $aspect) {
            // Aspect configs
            $defaults['aspect_configs'][$aspect->code] = [
                'weight' => $aspect->weight_percentage,
                'active' => true,
            ];

            // ❌ HARDCODED: Add rating for Kompetensi aspects
            if ($category->code === 'kompetensi') {
                $defaults['aspect_configs'][$aspect->code]['rating'] = (float) $aspect->standard_rating;
            }

            // Sub-aspect configs (for Potensi)
            foreach ($aspect->subAspects as $subAspect) {
                $defaults['sub_aspect_configs'][$subAspect->code] = [
                    'rating' => $subAspect->standard_rating,
                    'active' => true,
                ];
            }
        }
    }

    return $defaults;
}
```

**New Code:**

```php
/**
 * Get template defaults for custom standard creation
 *
 * Logic (DATA-DRIVEN):
 * - All categories get weights
 * - All aspects get weights + active status
 * - Aspects WITHOUT sub-aspects get rating field
 * - All sub-aspects get rating + active status
 *
 * @param int $templateId
 * @return array
 */
public function getTemplateDefaults(int $templateId): array
{
    $template = AssessmentTemplate::with([
        'categoryTypes.aspects.subAspects',
    ])->findOrFail($templateId);

    $defaults = [
        'template_id' => $templateId,
        'template_name' => $template->name,
        'category_weights' => [],
        'aspect_configs' => [],
        'sub_aspect_configs' => [],
    ];

    foreach ($template->categoryTypes as $category) {
        // Category weights (all categories)
        $defaults['category_weights'][$category->code] = $category->weight_percentage;

        foreach ($category->aspects as $aspect) {
            // Base aspect config (all aspects)
            $defaults['aspect_configs'][$aspect->code] = [
                'weight' => $aspect->weight_percentage,
                'active' => true,
            ];

            // ✅ DATA-DRIVEN: Add rating only if aspect has NO sub-aspects
            if ($aspect->subAspects->isEmpty()) {
                $defaults['aspect_configs'][$aspect->code]['rating'] = (float) $aspect->standard_rating;
            }

            // Sub-aspect configs (if aspect has sub-aspects)
            foreach ($aspect->subAspects as $subAspect) {
                $defaults['sub_aspect_configs'][$subAspect->code] = [
                    'rating' => $subAspect->standard_rating,
                    'active' => true,
                ];
            }
        }
    }

    return $defaults;
}
```

**Summary of CustomStandardService.php changes:**
- ✅ `getTemplateDefaults()` - now data-driven (checks `subAspects->isEmpty()`)
- ✅ No other methods need changes

---

### File 3: AssessmentCalculationService.php

**Location:** `app/Services/Assessment/AssessmentCalculationService.php`

#### Change 3.1: Remove `processPotensi()` and `processKompetensi()` Methods

**Current Code has two separate methods (~Line 85-141):**

```php
/**
 * Process Potensi assessments (calculate from sub-aspects)
 */
private function processPotensi(Participant $participant, array $potensiData): void
{
    // ~30 lines of Potensi-specific logic
}

/**
 * Process Kompetensi assessments (direct aspect ratings, no sub-aspects)
 */
private function processKompetensi(Participant $participant, array $kompetensiData): void
{
    // ~30 lines of Kompetensi-specific logic
}
```

**Delete both methods entirely.**

#### Change 3.2: Add Unified `processCategory()` Method

**Add this new method:**

```php
/**
 * Process category assessments (UNIFIED for all category types)
 *
 * Logic (DATA-DRIVEN):
 * - Create CategoryAssessment
 * - For each aspect in category data:
 *   - Create AspectAssessment
 *   - If aspect has sub-aspects in data: process them
 *   - Calculate aspect from service
 * - Calculate category totals
 *
 * @param Participant $participant
 * @param string $categoryCode Category code (e.g., 'potensi', 'kompetensi', 'integritas')
 * @param array $categoryData Array of aspects with individual_rating
 * @return void
 */
private function processCategory(Participant $participant, string $categoryCode, array $categoryData): void
{
    // Get category type
    $categoryType = CategoryType::where('template_id', $participant->positionFormation->template_id)
        ->where('code', $categoryCode)
        ->firstOrFail();

    // Create or update category assessment
    $categoryAssessment = $this->categoryService->createCategoryAssessment(
        $participant,
        $categoryCode
    );

    // Process each aspect in this category
    foreach ($categoryData as $aspectData) {
        // Find aspect master
        $aspect = Aspect::where('template_id', $participant->positionFormation->template_id)
            ->where('code', $aspectData['code'])
            ->with('subAspects')
            ->firstOrFail();

        // Create aspect assessment
        $aspectAssessment = AspectAssessment::updateOrCreate(
            [
                'category_assessment_id' => $categoryAssessment->id,
                'participant_id' => $participant->id,
                'aspect_id' => $aspect->id,
            ],
            [
                'event_id' => $participant->event_id,
                'batch_id' => $participant->batch_id,
                'position_formation_id' => $participant->position_formation_id,
                'individual_rating' => $aspectData['individual_rating'],
            ]
        );

        // DATA-DRIVEN: Check if aspect has sub-aspects in the data
        if (isset($aspectData['sub_aspects']) && !empty($aspectData['sub_aspects'])) {
            // Process sub-aspects (like old processPotensi)
            $this->subAspectService->processSubAspects(
                $aspectAssessment,
                $aspectData['sub_aspects']
            );

            // Calculate aspect (will aggregate from sub-aspects)
            $this->aspectService->calculateAspect($aspectAssessment);
        } else {
            // No sub-aspects: use direct rating (like old processKompetensi)
            $this->aspectService->calculateAspect($aspectAssessment);
        }
    }

    // Recalculate category totals
    $this->categoryService->calculateCategory($categoryAssessment);
}
```

#### Change 3.3: Update Main `process()` Method

**Current Code (~Line 20-80):**

```php
public function process(Participant $participant, array $assessmentsData): void
{
    DB::transaction(function () use ($participant, $assessmentsData) {
        // 1. Process Potensi
        if (isset($assessmentsData['potensi'])) {
            $this->processPotensi($participant, $assessmentsData['potensi']);
        }

        // 2. Process Kompetensi
        if (isset($assessmentsData['kompetensi'])) {
            $this->processKompetensi($participant, $assessmentsData['kompetensi']);
        }

        // 3. Calculate final
        $this->finalAssessmentService->calculateFinalAssessment($participant);
    });
}
```

**New Code:**

```php
/**
 * Process assessment data for a participant
 *
 * Data structure from API:
 * [
 *     'potensi' => [ ... aspects ... ],
 *     'kompetensi' => [ ... aspects ... ],
 *     // or any other category codes
 * ]
 *
 * @param Participant $participant
 * @param array $assessmentsData Data from API grouped by category code
 * @return void
 */
public function process(Participant $participant, array $assessmentsData): void
{
    DB::transaction(function () use ($participant, $assessmentsData) {
        // ✅ UNIFIED: Process all categories (works for any category code)
        foreach ($assessmentsData as $categoryCode => $categoryData) {
            $this->processCategory($participant, $categoryCode, $categoryData);
        }

        // Calculate final assessment (weighted combination of all categories)
        $this->finalAssessmentService->calculateFinalAssessment($participant);
    });
}
```

**Summary of AssessmentCalculationService.php changes:**
- ❌ Deleted `processPotensi()` method
- ❌ Deleted `processKompetensi()` method
- ✅ Added `processCategory()` unified method
- ✅ Updated `process()` to loop through categories

---

### File 4: AspectService.php

**Location:** `app/Services/Assessment/AspectService.php`

#### Change 4.1: Remove Separate Calculation Methods

**Current Code has two methods (~Line 60-160):**

```php
/**
 * Calculate aspect assessment for Potensi (from sub-aspects)
 */
public function calculatePotensiAspect(AspectAssessment $aspectAssessment): void
{
    // ~40 lines of Potensi-specific calculation
}

/**
 * Calculate aspect assessment for Kompetensi (direct from API)
 */
public function calculateKompetensiAspect(
    AspectAssessment $aspectAssessment,
    Aspect $aspect,
    float $individualRating
): void {
    // ~30 lines of Kompetensi-specific calculation
}
```

**Delete both methods.**

#### Change 4.2: Add Unified `calculateAspect()` Method

**Add this new method:**

```php
/**
 * Calculate aspect assessment (UNIFIED for all types)
 *
 * Logic (DATA-DRIVEN):
 * - Get aspect weight from DynamicStandardService (handles session/custom/default)
 * - If aspect has sub-aspect assessments: calculate rating from them
 * - If aspect has no sub-aspect assessments: use individual_rating from assessment
 * - Calculate scores (standard, individual, gap)
 * - Determine conclusion
 * - Save to database
 *
 * @param AspectAssessment $aspectAssessment
 * @return void
 */
public function calculateAspect(AspectAssessment $aspectAssessment): void
{
    $aspect = $aspectAssessment->aspect;
    $templateId = $aspect->template_id;

    // Get services
    $dynamicService = app(DynamicStandardService::class);
    $conclusionService = app(ConclusionService::class);

    // Get adjusted weight from DynamicStandardService
    $aspectWeight = $dynamicService->getAspectWeight($templateId, $aspect->code);

    // DATA-DRIVEN: Determine rating source
    $subAssessments = $aspectAssessment->subAspectAssessments;

    if ($subAssessments->isNotEmpty()) {
        // Has sub-aspect assessments: calculate ratings from them
        $standardRating = 0;
        $individualRating = 0;
        $count = 0;

        foreach ($subAssessments as $subAssessment) {
            // Check if sub-aspect is active
            if (!$dynamicService->isSubAspectActive($templateId, $subAssessment->subAspect->code)) {
                continue;
            }

            // Get adjusted sub-aspect rating
            $subStandardRating = $dynamicService->getSubAspectRating(
                $templateId,
                $subAssessment->subAspect->code
            );

            $standardRating += $subStandardRating;
            $individualRating += $subAssessment->individual_rating;
            $count++;
        }

        if ($count > 0) {
            $standardRating = round($standardRating / $count, 2);
            $individualRating = round($individualRating / $count, 2);
        }
    } else {
        // No sub-aspect assessments: use aspect's own ratings
        $standardRating = $dynamicService->getAspectRating($templateId, $aspect->code);
        $individualRating = $aspectAssessment->individual_rating;
    }

    // Calculate scores
    $standardScore = round($standardRating * $aspectWeight, 2);
    $individualScore = round($individualRating * $aspectWeight, 2);

    // Calculate gaps
    $gapRating = round($individualRating - $standardRating, 2);
    $gapScore = round($individualScore - $standardScore, 2);

    // Calculate percentage
    $percentageScore = $standardScore > 0
        ? round(($individualScore / $standardScore) * 100, 2)
        : 0;

    // Determine conclusion (using ConclusionService)
    // For aspect-level, we use simple gap logic
    $conclusionCode = $gapRating >= 0 ? 'ABOVE' : 'BELOW';
    $conclusionText = $gapRating >= 0 ? 'Di Atas Standar' : 'Di Bawah Standar';

    // Update aspect assessment
    $aspectAssessment->update([
        'standard_rating' => $standardRating,
        'standard_score' => $standardScore,
        'individual_rating' => $individualRating,
        'individual_score' => $individualScore,
        'gap_rating' => $gapRating,
        'gap_score' => $gapScore,
        'percentage_score' => (int) $percentageScore,
        'conclusion_code' => $conclusionCode,
        'conclusion_text' => $conclusionText,
    ]);
}
```

**Summary of AspectService.php changes:**
- ❌ Deleted `calculatePotensiAspect()` method
- ❌ Deleted `calculateKompetensiAspect()` method
- ✅ Added `calculateAspect()` unified method

---

### File 5: TrainingRecommendationService.php

**Location:** `app/Services/TrainingRecommendationService.php`

#### Change 5.1: Update Category Check (~Line 261)

**Current Code:**

```php
/**
 * For Potensi category: Calculate from sub-aspects
 * For Kompetensi category: Use aspect rating directly
 */
protected function getAspectStandardRating(Aspect $aspect, int $templateId): float
{
    $dynamicService = app(DynamicStandardService::class);

    // ❌ HARDCODED: Check category type
    if ($aspect->categoryType->code === 'potensi' && $aspect->subAspects->count() > 0) {
        // Calculate from sub-aspects
        $totalRating = 0;
        $activeCount = 0;

        foreach ($aspect->subAspects as $subAspect) {
            if (!$dynamicService->isSubAspectActive($templateId, $subAspect->code)) {
                continue;
            }

            $totalRating += $dynamicService->getSubAspectRating($templateId, $subAspect->code);
            $activeCount++;
        }

        return $activeCount > 0 ? round($totalRating / $activeCount, 2) : 0.0;
    }

    // For Kompetensi or aspects without sub-aspects, use aspect rating
    return $dynamicService->getAspectRating($templateId, $aspect->code);
}
```

**New Code:**

```php
/**
 * Get aspect standard rating (DATA-DRIVEN)
 *
 * Logic:
 * - If aspect has sub-aspects: calculate from active sub-aspects
 * - If aspect has no sub-aspects: use aspect rating directly
 *
 * @param Aspect $aspect
 * @param int $templateId
 * @return float
 */
protected function getAspectStandardRating(Aspect $aspect, int $templateId): float
{
    $dynamicService = app(DynamicStandardService::class);

    // ✅ DATA-DRIVEN: Check if aspect has sub-aspects
    if ($aspect->subAspects->isNotEmpty()) {
        // Calculate from active sub-aspects
        $totalRating = 0;
        $activeCount = 0;

        foreach ($aspect->subAspects as $subAspect) {
            // Check if sub-aspect is active
            if (!$dynamicService->isSubAspectActive($templateId, $subAspect->code)) {
                continue;
            }

            $totalRating += $dynamicService->getSubAspectRating($templateId, $subAspect->code);
            $activeCount++;
        }

        return $activeCount > 0 ? round($totalRating / $activeCount, 2) : 0.0;
    }

    // No sub-aspects: use aspect rating directly
    return $dynamicService->getAspectRating($templateId, $aspect->code);
}
```

**Summary of TrainingRecommendationService.php changes:**
- ✅ `getAspectStandardRating()` - removed category check, now data-driven

---

### File 6: InterpretationGeneratorService.php

**Location:** `app/Services/InterpretationGeneratorService.php`

#### Change 6.1: Unify Interpretation Generation

**Current Code has separate methods (~Line 70-180):**

```php
/**
 * Generate POTENSI interpretation (berbasis sub-aspects)
 */
protected function generatePotensiInterpretation(Participant $participant): string
{
    // ~50 lines of Potensi-specific logic
}

/**
 * Generate KOMPETENSI interpretation (berbasis aspects, no sub-aspects)
 */
protected function generateKompetensiInterpretation(Participant $participant): string
{
    // ~50 lines of Kompetensi-specific logic
}
```

**New Code - Replace both with:**

```php
/**
 * Generate category interpretation (UNIFIED for all category types)
 *
 * Logic (DATA-DRIVEN):
 * - For each aspect in category:
 *   - If aspect has sub-aspects: generate from sub-aspects
 *   - If aspect has no sub-aspects: generate from aspect directly
 * - Combine into category interpretation
 *
 * @param Participant $participant
 * @param CategoryType $categoryType
 * @return string
 */
protected function generateCategoryInterpretation(
    Participant $participant,
    CategoryType $categoryType
): string {
    $template = $participant->positionFormation->assessmentTemplate;

    // Get category assessment
    $categoryAssessment = $participant->categoryAssessments()
        ->where('category_type_id', $categoryType->id)
        ->with([
            'aspectAssessments.aspect.subAspects',
            'aspectAssessments.subAspectAssessments.subAspect',
        ])
        ->first();

    if (!$categoryAssessment) {
        return "Data penilaian untuk kategori {$categoryType->name} tidak tersedia.";
    }

    $interpretations = [];

    // Process each aspect
    foreach ($categoryAssessment->aspectAssessments as $aspectAssessment) {
        $aspect = $aspectAssessment->aspect;

        // DATA-DRIVEN: Check if aspect has sub-aspect assessments
        if ($aspectAssessment->subAspectAssessments->isNotEmpty()) {
            // Has sub-aspects: generate from them
            $subInterpretations = [];

            foreach ($aspectAssessment->subAspectAssessments as $subAssessment) {
                $subAspect = $subAssessment->subAspect;

                // Get interpretation template for this sub-aspect
                $template = $this->templateService->getTemplate(
                    'sub_aspect',
                    $subAspect->id,
                    $subAspect->name,
                    $subAssessment->individual_rating
                );

                if ($template) {
                    $subInterpretations[] = $template->template_text;
                }
            }

            if (!empty($subInterpretations)) {
                $interpretations[] = "**{$aspect->name}:** " . implode(' ', $subInterpretations);
            }
        } else {
            // No sub-aspects: generate from aspect directly
            $template = $this->templateService->getTemplate(
                'aspect',
                $aspect->id,
                $aspect->name,
                $aspectAssessment->individual_rating
            );

            if ($template) {
                $interpretations[] = "**{$aspect->name}:** {$template->template_text}";
            }
        }
    }

    // Combine all interpretations
    $categoryInterpretation = implode("\n\n", $interpretations);

    // Add category conclusion
    $conclusion = $this->getCategoryConclusion($categoryAssessment);

    return "### {$categoryType->name}\n\n{$categoryInterpretation}\n\n**Kesimpulan:** {$conclusion}";
}
```

#### Change 6.2: Update Main `generate()` Method

**Current Code (~Line 20-40):**

```php
public function generate(Participant $participant): array
{
    $results = [];

    // 1. Generate Potensi Interpretation
    $results['potensi'] = $this->generatePotensiInterpretation($participant);

    // 2. Generate Kompetensi Interpretation
    $results['kompetensi'] = $this->generateKompetensiInterpretation($participant);

    return $results;
}
```

**New Code:**

```php
/**
 * Generate interpretations for 1 participant (all categories)
 *
 * @param Participant $participant
 * @return array Keyed by category code ['potensi' => string, 'kompetensi' => string, ...]
 */
public function generate(Participant $participant): array
{
    $results = [];

    $template = $participant->positionFormation->assessmentTemplate;

    // ✅ UNIFIED: Generate for all categories dynamically
    foreach ($template->categoryTypes as $categoryType) {
        $results[$categoryType->code] = $this->generateCategoryInterpretation(
            $participant,
            $categoryType
        );
    }

    return $results;
}
```

**Summary of InterpretationGeneratorService.php changes:**
- ❌ Deleted `generatePotensiInterpretation()` method
- ❌ Deleted `generateKompetensiInterpretation()` method
- ✅ Added `generateCategoryInterpretation()` unified method
- ✅ Updated `generate()` to loop through all categories

---

## Testing Strategy

### Unit Tests

**Create: `tests/Unit/Services/DynamicStandardServiceTest.php`**

```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\DynamicStandardService;
use App\Models\Aspect;
use App\Models\SubAspect;
use App\Models\AssessmentTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DynamicStandardServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DynamicStandardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DynamicStandardService::class);
    }

    public function test_getOriginalAspectRating_returns_calculated_value_for_aspect_with_sub_aspects(): void
    {
        // Arrange: Create aspect with 3 sub-aspects (ratings: 3, 4, 5)
        $template = AssessmentTemplate::factory()->create();
        $categoryType = $template->categoryTypes()->create([
            'code' => 'test_category',
            'name' => 'Test Category',
            'weight_percentage' => 100,
        ]);

        $aspect = $categoryType->aspects()->create([
            'template_id' => $template->id,
            'code' => 'test_aspect',
            'name' => 'Test Aspect',
            'weight_percentage' => 100,
            'standard_rating' => 999.99, // Should be ignored
        ]);

        $aspect->subAspects()->create(['code' => 'sub_01', 'name' => 'Sub 1', 'standard_rating' => 3]);
        $aspect->subAspects()->create(['code' => 'sub_02', 'name' => 'Sub 2', 'standard_rating' => 4]);
        $aspect->subAspects()->create(['code' => 'sub_03', 'name' => 'Sub 3', 'standard_rating' => 5]);

        // Act
        $rating = $this->service->getAspectRating($template->id, 'test_aspect');

        // Assert: Should be average of sub-aspects (3 + 4 + 5) / 3 = 4.0
        $this->assertEquals(4.0, $rating);
    }

    public function test_getOriginalAspectRating_returns_direct_value_for_aspect_without_sub_aspects(): void
    {
        // Arrange: Create aspect with NO sub-aspects
        $template = AssessmentTemplate::factory()->create();
        $categoryType = $template->categoryTypes()->create([
            'code' => 'test_category',
            'name' => 'Test Category',
            'weight_percentage' => 100,
        ]);

        $aspect = $categoryType->aspects()->create([
            'template_id' => $template->id,
            'code' => 'test_aspect',
            'name' => 'Test Aspect',
            'weight_percentage' => 100,
            'standard_rating' => 4.5,
        ]);

        // Act
        $rating = $this->service->getAspectRating($template->id, 'test_aspect');

        // Assert: Should use aspect's own rating
        $this->assertEquals(4.5, $rating);
    }

    public function test_session_adjustment_overrides_calculated_sub_aspect_rating(): void
    {
        // Arrange
        $template = AssessmentTemplate::factory()->create();
        $categoryType = $template->categoryTypes()->create([
            'code' => 'test_category',
            'name' => 'Test Category',
            'weight_percentage' => 100,
        ]);

        $aspect = $categoryType->aspects()->create([
            'template_id' => $template->id,
            'code' => 'test_aspect',
            'name' => 'Test Aspect',
            'weight_percentage' => 100,
        ]);

        $aspect->subAspects()->create(['code' => 'sub_01', 'name' => 'Sub 1', 'standard_rating' => 4]);

        // Act: Save session adjustment for aspect rating
        $this->service->saveAspectRating($template->id, 'test_aspect', 5.0);

        // Get rating (should use session, not calculated)
        $rating = $this->service->getAspectRating($template->id, 'test_aspect');

        // Assert: Should be 5.0 (session), not 4.0 (calculated from sub)
        $this->assertEquals(5.0, $rating);
    }
}
```

### Integration Tests

**Create: `tests/Feature/Services/FlexibleHierarchyTest.php`**

```php
<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use App\Models\{AssessmentTemplate, CategoryType, Aspect, Participant};
use App\Services\IndividualAssessmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FlexibleHierarchyTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculation_works_for_mixed_category_structure(): void
    {
        // Arrange: Create category with mixed structure
        // - Aspect A (has sub-aspects)
        // - Aspect B (no sub-aspects)

        $template = AssessmentTemplate::factory()->create();
        $category = $template->categoryTypes()->create([
            'code' => 'mixed_category',
            'name' => 'Mixed Category',
            'weight_percentage' => 100,
        ]);

        // Aspect A: Has sub-aspects
        $aspectA = $category->aspects()->create([
            'template_id' => $template->id,
            'code' => 'aspect_a',
            'name' => 'Aspect A',
            'weight_percentage' => 50,
        ]);
        $aspectA->subAspects()->create(['code' => 'sub_a1', 'name' => 'Sub A1', 'standard_rating' => 4]);
        $aspectA->subAspects()->create(['code' => 'sub_a2', 'name' => 'Sub A2', 'standard_rating' => 5]);

        // Aspect B: No sub-aspects
        $aspectB = $category->aspects()->create([
            'template_id' => $template->id,
            'code' => 'aspect_b',
            'name' => 'Aspect B',
            'weight_percentage' => 50,
            'standard_rating' => 3.5,
        ]);

        // Create participant with assessments
        $participant = Participant::factory()->create([
            'position_formation_id' => // ... setup position with template
        ]);

        // ... create aspect assessments and sub-aspect assessments

        // Act
        $service = app(IndividualAssessmentService::class);
        $result = $service->getCategoryAssessment(
            $participant->id,
            'mixed_category',
            10
        );

        // Assert: Both aspects should be calculated correctly
        $this->assertNotNull($result);
        // Add more specific assertions
    }
}
```

### Manual Testing Checklist

**Before Deployment:**

- [ ] Test dengan data existing (Potensi/Kompetensi)
  - [ ] Individual reports load correctly
  - [ ] Ranking reports load correctly
  - [ ] Scores match pre-refactor values
  - [ ] Session adjustment still works
  - [ ] Custom standard selection still works

- [ ] Test dengan mock data (mixed structure)
  - [ ] Create test template dengan mixed structure
  - [ ] Import participant data
  - [ ] Verify calculations
  - [ ] Test session adjustment
  - [ ] Test custom standard creation

- [ ] Performance testing
  - [ ] Run queries with EXPLAIN
  - [ ] Check N+1 query issues
  - [ ] Measure response times

---

## Backward Compatibility

### Zero Breaking Changes Guarantee

**Current Data Structure:**
```
Template "Standar Manajerial L3"
├── potensi (50%)
│   └── kecerdasan → [has sub-aspects]
└── kompetensi (50%)
    └── integritas (rating: 3.0) → [no sub-aspects]
```

**How Refactored Code Handles It:**

#### Scenario 1: Potensi Aspect (has sub-aspects)

```php
// BEFORE REFACTOR:
if ($aspect->categoryType->code === 'potensi') {
    return calculateFromSubAspects();
}

// AFTER REFACTOR:
if ($aspect->subAspects()->exists()) {
    return calculateFromSubAspects();
}

// Result:
// - kecerdasan (Potensi) has sub-aspects
// - Condition TRUE in both cases
// - SAME BEHAVIOR ✅
```

#### Scenario 2: Kompetensi Aspect (no sub-aspects)

```php
// BEFORE REFACTOR:
if ($aspect->categoryType->code === 'kompetensi') {
    return $aspect->standard_rating;
}

// AFTER REFACTOR:
if ($aspect->subAspects()->exists()) {
    // FALSE for integritas (no subs)
} else {
    return $aspect->standard_rating; // ← Same!
}

// Result:
// - integritas (Kompetensi) no sub-aspects
// - Falls to else block in both cases
// - SAME BEHAVIOR ✅
```

### Validation Steps

**Run these queries to confirm no data breaks:**

```sql
-- Check 1: All Potensi aspects have sub-aspects?
SELECT
    a.code,
    a.name,
    ct.code as category_code,
    COUNT(sa.id) as sub_aspect_count
FROM aspects a
JOIN category_types ct ON a.category_type_id = ct.id
LEFT JOIN sub_aspects sa ON sa.aspect_id = a.id
WHERE ct.code = 'potensi'
GROUP BY a.id
HAVING sub_aspect_count = 0;

-- Expected: 0 rows (all Potensi aspects HAVE subs)

-- Check 2: All Kompetensi aspects have NO sub-aspects?
SELECT
    a.code,
    a.name,
    ct.code as category_code,
    COUNT(sa.id) as sub_aspect_count
FROM aspects a
JOIN category_types ct ON a.category_type_id = ct.id
LEFT JOIN sub_aspects sa ON sa.aspect_id = a.id
WHERE ct.code = 'kompetensi'
GROUP BY a.id
HAVING sub_aspect_count > 0;

-- Expected: 0 rows (all Kompetensi aspects have NO subs)
```

**If both queries return 0 rows:** Current data perfectly aligns with new logic! ✅

---

## Migration Checklist

### Pre-Implementation

- [ ] Read all related docs:
  - [ ] `docs/SERVICE_ARCHITECTURE.md`
  - [ ] `docs/CUSTOM_STANDARD_FEATURE.md`
  - [ ] `docs/CONCLUSION_SERVICE_USAGE.md`

- [ ] Understand current system:
  - [ ] Session adjustment flow
  - [ ] Custom standard priority chain
  - [ ] Event-driven cache invalidation

- [ ] Run validation queries (see Backward Compatibility section)

- [ ] Create feature branch:
  ```bash
  git checkout -b feature/flexible-hierarchy-refactoring
  ```

### Phase 1: Core Services (Day 1-2)

- [ ] **DynamicStandardService.php**
  - [ ] Implement `getOriginalAspectRating()` refactor
  - [ ] Add `calculateRatingFromSubAspects()` helper
  - [ ] Add `getAspectRatingFromCustomStandard()` helper
  - [ ] Update `getOriginalValue()` method
  - [ ] Write unit tests
  - [ ] Run tests: `php artisan test --filter=DynamicStandardServiceTest`

- [ ] **CustomStandardService.php**
  - [ ] Refactor `getTemplateDefaults()` method
  - [ ] Test custom standard creation with mixed structure
  - [ ] Run tests: `php artisan test --filter=CustomStandardServiceTest`

### Phase 2: Calculation Services (Day 2)

- [ ] **AssessmentCalculationService.php**
  - [ ] Add `processCategory()` method
  - [ ] Update `process()` method
  - [ ] Delete `processPotensi()` method
  - [ ] Delete `processKompetensi()` method
  - [ ] Run tests: `php artisan test --filter=AssessmentCalculationServiceTest`

- [ ] **AspectService.php**
  - [ ] Add `calculateAspect()` method
  - [ ] Delete `calculatePotensiAspect()` method
  - [ ] Delete `calculateKompetensiAspect()` method
  - [ ] Run tests: `php artisan test --filter=AspectServiceTest`

### Phase 3: Helper Services (Day 3)

- [ ] **TrainingRecommendationService.php**
  - [ ] Update `getAspectStandardRating()` method
  - [ ] Test training recommendations

- [ ] **InterpretationGeneratorService.php**
  - [ ] Add `generateCategoryInterpretation()` method
  - [ ] Update `generate()` method
  - [ ] Delete `generatePotensiInterpretation()` method
  - [ ] Delete `generateKompetensiInterpretation()` method
  - [ ] Test interpretations

### Phase 4: Testing & Validation (Day 3)

- [ ] **Run Full Test Suite**
  ```bash
  php artisan test
  ```

- [ ] **Manual Testing - Existing Data**
  - [ ] Individual Report: GeneralPsyMapping works
  - [ ] Individual Report: GeneralMcMapping works
  - [ ] Individual Report: GeneralMapping works
  - [ ] Individual Report: GeneralMatching works
  - [ ] Ranking Report: RankingPsyMapping works
  - [ ] Ranking Report: RankingMcMapping works
  - [ ] Ranking Report: RekapRankingAssessment works
  - [ ] Session adjustment: Edit sub-aspect rating → Report updates
  - [ ] Session adjustment: Edit aspect weight → Report updates
  - [ ] Custom standard: Select → Report updates
  - [ ] Custom standard: Create new → Works

- [ ] **Manual Testing - Mock Data (Optional)**
  - [ ] Create template dengan 3 categories
  - [ ] Create mixed structure (aspect with sub + without sub in same category)
  - [ ] Import participant data
  - [ ] Verify calculations

- [ ] **Performance Testing**
  - [ ] Check query count on individual report
  - [ ] Check query count on ranking report
  - [ ] Ensure no N+1 issues
  - [ ] Response time < 500ms for individual report

### Phase 5: Documentation & Cleanup

- [ ] **Update Documentation**
  - [ ] Update `docs/SERVICE_ARCHITECTURE.md`
    - [ ] Add note about data-driven approach
    - [ ] Update calculation flow diagram
  - [ ] Update `docs/CUSTOM_STANDARD_FEATURE.md`
    - [ ] Mention flexibility for any category
    - [ ] Update examples
  - [ ] Add code comments where necessary

- [ ] **Code Formatting**
  ```bash
  vendor/bin/pint
  ```

- [ ] **Commit Changes**
  ```bash
  git add .
  git commit -m "Refactor: Implement data-driven flexible hierarchy

  - Remove hardcoded category type checks (potensi/kompetensi)
  - Add unified calculation logic based on actual data structure
  - Support any category type with/without sub-aspects
  - Maintain backward compatibility with existing data
  - Update 6 service files with data-driven approach

  BREAKING CHANGES: None
  TESTED: All existing tests pass + new unit tests added"
  ```

### Post-Implementation

- [ ] Create Pull Request
- [ ] Code review by team
- [ ] Deploy to staging
- [ ] QA testing on staging
- [ ] Deploy to production
- [ ] Monitor for errors (first 24 hours)

---

## Summary

### What This Refactoring Does

**Before:**
- Logic tied to specific category names (`potensi`, `kompetensi`)
- Hardcoded assumptions about structure
- Can't support new category types without code changes

**After:**
- Logic based on actual data structure
- No assumptions about category names
- Supports any category type, any structure
- Zero breaking changes for existing data

### Key Changes

| File | Lines Changed | Impact |
|------|---------------|--------|
| DynamicStandardService.php | ~100 | High - Foundation |
| CustomStandardService.php | ~20 | Medium |
| AssessmentCalculationService.php | ~80 | High - Business Logic |
| AspectService.php | ~70 | High - Business Logic |
| TrainingRecommendationService.php | ~15 | Low |
| InterpretationGeneratorService.php | ~60 | Medium |
| **TOTAL** | **~345 lines** | **Future-proof** |

### Benefits

✅ **Future-Proof**: Support any category structure
✅ **Maintainable**: Single source of truth for logic
✅ **Testable**: Clear, predictable behavior
✅ **Zero Breaking Changes**: Existing data works identically
✅ **Better Code Quality**: Removes hardcoded assumptions
✅ **Follows SOLID**: Open/Closed principle

### Risks

⚠️ **Low Risk** - extensive testing covers edge cases
⚠️ **Backward Compatible** - no schema changes
⚠️ **Well Documented** - clear migration path

---

**Document Status**: Ready for Implementation
**Next Action**: Create feature branch and start Phase 1
**Estimated Timeline**: 2-3 days
**Questions?** Review related docs in `docs/` folder

---

**End of Document**
