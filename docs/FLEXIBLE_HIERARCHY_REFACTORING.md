# Flexible Hierarchy Refactoring: Data-Driven Assessment Structure

> **Version**: 2.0
> **Created**: 2025-01-27
> **Completed**: 2025-01-27
> **Status**: ✅ Completed
> **Priority**: High - Foundation for Future Features

---

## 📋 Table of Contents

1. [Quick Context](#quick-context)
2. [Problem Statement](#problem-statement)
3. [Current vs Future Architecture](#current-vs-future-architecture)
4. [Technical Design](#technical-design)
5. [Implementation Summary](#implementation-summary)
6. [Backward Compatibility](#backward-compatibility)
7. [Files Changed](#files-changed)

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
```

---

## Implementation Summary

### Refactoring Completed

All 9 service files have been successfully refactored to use data-driven approach instead of hardcoded category checks.

### Core Pattern Change

**Before:**
```php
if ($categoryCode === 'potensi') {
    // Has sub-aspects logic
} elseif ($categoryCode === 'kompetensi') {
    // No sub-aspects logic
}
```

**After:**
```php
if ($aspect->subAspects->isNotEmpty()) {
    // Has sub-aspects logic
} else {
    // No sub-aspects logic
}
```

---

## Files Changed

### 1. DynamicStandardService.php

**Location:** `app/Services/DynamicStandardService.php`

**Changes:**
- ✅ Refactored `getOriginalAspectRating()` to check `$aspect->subAspects->isNotEmpty()`
- ✅ Added `calculateRatingFromSubAspects()` helper method
- ✅ Added `getAspectRatingFromCustomStandard()` helper method
- ✅ Updated `getOriginalValue()` to use new helper for custom standard

**Key Logic:**
- If aspect has sub-aspects: calculate average from sub-aspect ratings
- If aspect has no sub-aspects: use aspect's own `standard_rating`

---

### 2. CustomStandardService.php

**Location:** `app/Services/CustomStandardService.php`

**Changes:**
- ✅ Refactored `getTemplateDefaults()` to check `$aspect->subAspects->isEmpty()`

**Key Logic:**
- Aspects WITHOUT sub-aspects get `rating` field in custom standard JSON
- Aspects WITH sub-aspects skip `rating` field (calculated from sub-aspects)

---

### 3. AssessmentCalculationService.php

**Location:** `app/Services/Assessment/AssessmentCalculationService.php`

**Changes:**
- ❌ Deleted `processPotensi()` method (category-specific)
- ❌ Deleted `processKompetensi()` method (category-specific)
- ✅ Added `processCategory()` unified method (works for any category)
- ✅ Updated `calculateParticipant()` to loop through all categories dynamically

**Key Logic:**
```php
foreach ($assessmentsData as $categoryCode => $categoryData) {
    $this->processCategory($participant, $categoryCode, $categoryData);
}
```

---

### 4. AspectService.php

**Location:** `app/Services/Assessment/AspectService.php`

**Changes:**
- ❌ Deleted `calculatePotensiAspect()` method
- ❌ Deleted `calculateKompetensiAspect()` method
- ✅ Added `calculateAspect()` unified method
- ✅ Updated `createAspectAssessment()` to be data-driven

**Key Logic:**
```php
if ($subAssessments->isNotEmpty()) {
    // Calculate individual_rating from AVERAGE of active sub-aspects
    $individualRating = $activeSubAssessments->avg('individual_rating');
} elseif ($individualRating === null) {
    return; // Skip if no rating source
}
```

---

### 5. TrainingRecommendationService.php

**Location:** `app/Services/TrainingRecommendationService.php`

**Changes:**
- ✅ Updated `getOriginalStandardRating()` to check `$aspect->subAspects->isNotEmpty()`

**Key Logic:**
- If aspect has sub-aspects: calculate from active sub-aspects
- If aspect has no sub-aspects: use aspect rating from DynamicStandardService

---

### 6. InterpretationGeneratorService.php

**Location:** `app/Services/InterpretationGeneratorService.php`

**Changes:**
- ❌ Deleted `generatePotensiInterpretation()` method
- ❌ Deleted `generateKompetensiInterpretation()` method
- ✅ Added `generateCategoryInterpretation()` unified method
- ✅ Updated `generateForParticipant()` to loop through all categories
- ✅ Updated display versions with same pattern

**Key Logic:**
```php
foreach ($template->categoryTypes as $categoryType) {
    $results[$categoryType->code] = $this->generateCategoryInterpretation(
        $participant,
        $categoryType
    );
}
```

---

### 7. RankingService.php

**Location:** `app/Services/RankingService.php`

**Changes:**
- ✅ Line 90: Changed `if ($category->code === 'potensi')` → `if ($aspect->subAspects->isNotEmpty())`
- ✅ Line 263: Same change
- ✅ Renamed `calculatePotensiAspectRating()` → `calculateAspectRatingFromSubAspects()`
- ✅ Renamed `calculatePotensiIndividualRating()` → `calculateIndividualRatingFromSubAspects()`

---

### 8. StatisticService.php

**Location:** `app/Services/StatisticService.php`

**Changes:**
- ✅ Line 102, 162, 241: Changed hardcoded checks to `$aspect->subAspects->isNotEmpty()`
- ✅ Renamed `calculatePotensiIndividualRating()` → `calculateIndividualRatingFromSubAspects()`

**Key Logic:**
- For standard rating: check if aspect has sub-aspects
- For distribution: recalculate from active sub-aspects if aspect has them
- For average rating: same data-driven approach

---

### 9. IndividualAssessmentService.php

**Location:** `app/Services/IndividualAssessmentService.php`

**Changes:**
- ✅ Line 116, 497, 513: Changed hardcoded checks to data-driven
- ✅ Renamed `calculatePotensiRatings()` → `calculateRatingsFromSubAspects()`
- ✅ Renamed `calculatePotensiMatching()` → `calculateMatchingFromSubAspects()`

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

### Database Validation

**Validation Queries:**

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

**Result:** Both queries returned 0 rows → Current data perfectly aligns with new logic! ✅

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

| File | Changes | Impact |
|------|---------|--------|
| DynamicStandardService.php | ~100 lines | High - Foundation |
| CustomStandardService.php | ~20 lines | Medium |
| AssessmentCalculationService.php | ~80 lines | High - Business Logic |
| AspectService.php | ~70 lines | High - Business Logic |
| TrainingRecommendationService.php | ~15 lines | Low |
| InterpretationGeneratorService.php | ~60 lines | Medium |
| RankingService.php | ~40 lines | Medium |
| StatisticService.php | ~35 lines | Medium |
| IndividualAssessmentService.php | ~40 lines | Medium |
| **TOTAL** | **~460 lines** | **Future-proof** |

### Benefits

✅ **Future-Proof**: Support any category structure
✅ **Maintainable**: Single source of truth for logic
✅ **Testable**: Clear, predictable behavior
✅ **Zero Breaking Changes**: Existing data works identically
✅ **Better Code Quality**: Removes hardcoded assumptions
✅ **Follows SOLID**: Open/Closed principle

---

**Document Status**: ✅ Completed
**Completed Date**: 2025-01-27
**Files Changed**: 9 service files
**Lines Changed**: ~460 lines
**Breaking Changes**: None

---

**End of Document**
