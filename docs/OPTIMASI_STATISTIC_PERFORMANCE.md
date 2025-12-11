# Optimasi Performance: Statistic Component (Custom Standard Baseline)

## 📋 Table of Contents
1. [Problem Statement](#problem-statement)
2. [Root Cause Analysis](#root-cause-analysis)
3. [Solution Overview](#solution-overview)
4. [Technical Implementation](#technical-implementation)
5. [Performance Results](#performance-results)
6. [Key Learnings](#key-learnings)
7. [Related Documentation](#related-documentation)

---

## 🎯 Problem Statement

### Symptoms
Halaman **Statistic** (Kurva Distribusi Frekuensi) mengalami performance degradation yang **sangat signifikan** ketika menggunakan **Custom Standard baseline** dibandingkan dengan **Quantum Default baseline**.

### Performance Gap

| Baseline Type | Request Duration | Models Loaded | Queries | Status |
|--------------|-----------------|---------------|---------|--------|
| **Quantum Default** | 707ms | 22 models | 24 queries | ✅ Fast |
| **Custom Standard** | ~8,000ms | 59,257 models | 33 queries | ❌ Very Slow |

**Impact:**
- **11.3x slower** response time for Custom Standard
- **2,693x more models** loaded into memory
- Poor user experience for BI exploration
- Potential memory exhaustion with larger datasets

---

## 🔍 Root Cause Analysis

### Component Architecture

```
Statistic Component (Livewire)
    ↓
StatisticService::getDistributionData()
    ↓
├─ calculateDistribution()      ← ❌ BOTTLENECK
└─ calculateAverageRating()     ← ❌ BOTTLENECK
```

### The Problem: Conditional Eager Loading

**Before Fix (`calculateDistribution()` method):**

```php
// ❌ BAD: Conditional logic based on hasActiveSubAspectAdjustments()
$needsRecalculation = $aspect->subAspects->isNotEmpty()
    && $standardService->hasActiveSubAspectAdjustments($templateId);

if (!$needsRecalculation) {
    // FAST PATH: Use stored individual_rating
    $rows = DB::table('aspect_assessments')->...
} else {
    // SLOW PATH: Eager load EVERYTHING
    $assessments = AspectAssessment::select([...])
        ->with(['subAspectAssessments', 'subAspectAssessments.subAspect'])
        ->where(...)
        ->get(); // ← Loads 59,257 models!
}
```

### Debug Analysis from Laravel Debugbar

**Quantum Default (Fast):**
```
Queries: 24
Duration: 67.06ms
Models: 22 retrieved
Key Query: SELECT CASE WHEN individual_rating ... COUNT(*) (26.98ms)
```

**Custom Standard (Slow):**
```
Queries: 33
Duration: 347ms
Models: 59,257 retrieved
Key Queries:
1. SELECT id, aspect_id, participant_id, individual_rating (30.22ms)
2. SELECT id, aspect_assessment_id, sub_aspect_id, individual_rating
   WHERE aspect_assessment_id IN (39, 76, 125, ..., 250164) (138ms)
   ↑ Loaded 49,330 SubAspectAssessment models
   ↑ Loaded 9,866 AspectAssessment models
```

### Why Was It Slow?

**Misconception:**
```php
// ❌ WRONG ASSUMPTION:
// "Custom Standard changes sub-aspect configuration,
//  so we need to recalculate individual_rating from sub-aspects"
```

**Reality:**
```php
// ✅ CORRECT UNDERSTANDING:
// Custom Standard ONLY changes:
// - weights (how important each aspect is)
// - standard_rating (minimum expected value)
// - active/inactive status
//
// Custom Standard NEVER changes:
// - individual_rating (historical assessment data)
```

**The Truth:**
- `individual_rating` is **IMMUTABLE** after assessment day
- It's **PRE-CALCULATED** and stored in database
- Custom Standard is just a **different lens** to view the same data
- Distribution is ALWAYS based on stored `individual_rating`

---

## ✅ Solution Overview

### Strategy: Apply RankingService Pattern

The solution was already proven in `RankingService.php` optimization:
- **ALWAYS use stored `individual_rating`** from database
- **NO eager loading** of sub-aspects for distribution/average calculation
- **Single fast SQL queries** instead of model hydration

### Solution Files

**Modified:**
- `app/Services/StatisticService.php`

**Reference (Pattern Source):**
- `app/Services/RankingService.php` (lines 100-146, already optimized)

---

## 🔧 Technical Implementation

### Changes to StatisticService.php

#### 1. Method: `calculateDistribution()` (Lines 252-293)

**Before:**
```php
// ❌ Conditional logic with massive eager loading
$needsRecalculation = $aspect->subAspects->isNotEmpty()
    && $standardService->hasActiveSubAspectAdjustments($templateId);

if (!$needsRecalculation) {
    // Fast path
    $rows = DB::table('aspect_assessments')...
} else {
    // Slow path: Load 59K models
    $assessments = AspectAssessment::with([...])...
}
```

**After:**
```php
// ✅ ALWAYS use fast path
// Custom Standard does NOT change individual_rating (historical data)
// It only changes weights & standard_rating (baseline configuration)
// Therefore, distribution is ALWAYS based on stored individual_rating

$rows = DB::table('aspect_assessments')
    ->where('event_id', $eventId)
    ->where('position_formation_id', $positionFormationId)
    ->where('aspect_id', $aspect->id)
    ->selectRaw('
        CASE
            WHEN individual_rating >= 1.00 AND individual_rating < 1.80 THEN 1
            WHEN individual_rating >= 1.80 AND individual_rating < 2.60 THEN 2
            WHEN individual_rating >= 2.60 AND individual_rating < 3.40 THEN 3
            WHEN individual_rating >= 3.40 AND individual_rating < 4.20 THEN 4
            WHEN individual_rating >= 4.20 AND individual_rating <= 5.00 THEN 5
            ELSE 0
        END as bucket,
        COUNT(*) as total
    ')
    ->groupBy('bucket')
    ->get();
```

**Key Changes:**
- ✅ Removed conditional logic
- ✅ Always use direct SQL query
- ✅ No model hydration
- ✅ Database-level aggregation (CASE + GROUP BY)

#### 2. Method: `calculateAverageRating()` (Lines 319-339)

**Before:**
```php
// ❌ Conditional logic with massive eager loading
$needsRecalculation = $aspect->subAspects->isNotEmpty()
    && $standardService->hasActiveSubAspectAdjustments($templateId);

if (!$needsRecalculation) {
    // Fast path
    $avg = DB::table('aspect_assessments')->avg('individual_rating');
} else {
    // Slow path: Load 59K models and recalculate
    $assessments = AspectAssessment::with([...])...
    foreach ($assessments as $assessment) {
        $recalculatedRating = $this->calculateIndividualRatingFromSubAspects(...);
    }
}
```

**After:**
```php
// ✅ ALWAYS use fast path
// Custom Standard does NOT change individual_rating (historical data)
// It only changes weights & standard_rating (baseline configuration)
// Therefore, average is ALWAYS based on stored individual_rating

$avg = DB::table('aspect_assessments')
    ->where('event_id', $eventId)
    ->where('position_formation_id', $positionFormationId)
    ->where('aspect_id', $aspect->id)
    ->avg('individual_rating');

return (float) ($avg ?? 0);
```

**Key Changes:**
- ✅ Removed conditional logic
- ✅ Always use direct SQL AVG()
- ✅ No model hydration
- ✅ Database-level calculation

#### 3. Code Cleanup

**Removed Unused Code:**
```php
// ❌ REMOVED: No longer needed
private function calculateIndividualRatingFromSubAspects(...) { }
private function getRatingBucket(...) { }
use App\Models\AspectAssessment; // Unused import
```

**Updated Cache TTL:**
```php
// ✅ RESTORED: Re-enabled caching after testing
private const CACHE_TTL = 60; // Was 0 during testing
```

---

## 📊 Performance Results

### Benchmark Comparison

#### Quantum Default Baseline (No Change Expected)

| Metric | Before | After | Change |
|--------|--------|-------|--------|
| Request Duration | 707ms | 707ms | Unchanged ✅ |
| Queries | 24 | 24 | Unchanged |
| Models Retrieved | 22 | 22 | Unchanged |
| Query Duration | 67.06ms | 67.06ms | Unchanged |

#### Custom Standard Baseline (Target of Optimization)

| Metric | Before | After | Improvement |
|--------|--------|-------|-------------|
| **Request Duration** | ~8,000ms | **644ms** | **92% faster** 🚀 |
| **Queries** | 33 | **19** | **42% reduction** |
| **Models Retrieved** | **59,257** | **45** | **99.92% reduction** 🔥 |
| **Query Duration** | 347ms | **65.58ms** | **81% faster** |

### Detailed Query Analysis (After Optimization)

**Critical Queries (Custom Standard):**

```sql
-- Query 1: Distribution calculation (28.12ms)
SELECT
  CASE
    WHEN individual_rating >= 1.00 AND individual_rating < 1.80 THEN 1
    WHEN individual_rating >= 1.80 AND individual_rating < 2.60 THEN 2
    WHEN individual_rating >= 2.60 AND individual_rating < 3.40 THEN 3
    WHEN individual_rating >= 3.40 AND individual_rating < 4.20 THEN 4
    WHEN individual_rating >= 4.20 AND individual_rating <= 5.00 THEN 5
    ELSE 0
  END as bucket,
  COUNT(*) as total
FROM aspect_assessments
WHERE event_id = 1 AND position_formation_id = 2 AND aspect_id = 8
GROUP BY bucket;

-- Query 2: Average calculation (22.82ms)
SELECT AVG(individual_rating) as aggregate
FROM aspect_assessments
WHERE event_id = 1 AND position_formation_id = 2 AND aspect_id = 8;
```

**Total assessment-related queries: ~51ms** (was 250ms+ with eager loading)

### Models Retrieved Breakdown (After)

```
Total: 45 models
├─ SubAspect:           21 models (metadata)
├─ Aspect:              13 models (metadata)
├─ CategoryType:         6 models (metadata)
├─ AssessmentTemplate:   2 models (metadata)
├─ User:                 1 model
├─ AssessmentEvent:      1 model
└─ PositionFormation:    1 model

Assessment Data Models: 0 (was 59,196!)
```

### Performance Parity Achieved ✅

Both baselines now have **equivalent performance**:

| Baseline | Duration | Queries | Models | Status |
|----------|----------|---------|--------|--------|
| Quantum Default | 707ms | 24 | 22 | ✅ Optimal |
| Custom Standard | **644ms** | 19 | 45 | ✅ **Optimal** |

**Note:** Custom Standard is slightly faster because cache keys differ and some metadata queries are optimized away.

---

## 🎓 Key Learnings

### 1. Data Immutability Principle

```php
// ============================================
// CRITICAL UNDERSTANDING FOR BI SYSTEMS
// ============================================

// ✅ IMMUTABLE (Never changes after assessment):
$individual_rating  // Historical assessment data
$participant_name   // Participant identity
$test_date         // Assessment timestamp

// 🔄 CONFIGURABLE (Changes via baseline selection):
$weight_percentage  // How important each aspect is (BASELINE)
$standard_rating   // Minimum expected value (BASELINE)
$active_status     // Which aspects to include (BASELINE)

// FORMULA:
Rankings = IMMUTABLE DATA × CONFIGURABLE BASELINE
```

**Implication for Optimization:**
- ✅ Distribution is **always** based on stored `individual_rating`
- ✅ Average is **always** based on stored `individual_rating`
- ✅ Custom Standard changes the **lens**, not the **data**
- ✅ Therefore, NO need to recalculate from sub-aspects

### 2. Avoid Conditional Eager Loading Based on Baseline Type

**Anti-Pattern (Slow):**
```php
// ❌ BAD: Different query paths for different baselines
if ($isCustomStandard || $hasSubAspectAdjustments) {
    // Eager load everything
    $assessments = AspectAssessment::with(['subAspects'])->get();
} else {
    // Fast SQL
    $rows = DB::table('aspect_assessments')->get();
}
```

**Best Practice (Fast):**
```php
// ✅ GOOD: Same fast path for ALL baselines
$rows = DB::table('aspect_assessments')
    ->where(...)
    ->selectRaw('...')
    ->get();

// Baseline selection only affects:
// - standard_rating calculation (metadata, not assessment data)
// - weights for scoring (metadata, not assessment data)
```

### 3. Trust Pre-Calculated Data in BI Systems

**Context:**
- SPSP is a **BI system**, not a CRUD system
- Assessment data is **finalized** on assessment day
- `individual_rating` is **pre-calculated** and stored
- Sub-aspects are used for **reporting details**, not for recalculation

**Decision Rule:**
```
┌─────────────────────────────────────────────────────┐
│ When should we recalculate individual_rating?       │
├─────────────────────────────────────────────────────┤
│ ❌ NEVER during BI exploration (ranking, statistics)│
│ ✅ ONLY during initial data import/migration        │
│ ✅ ONLY if source assessment data changes           │
└─────────────────────────────────────────────────────┘
```

### 4. Pattern Consistency Across Services

**Before this optimization:**
- ✅ `RankingService`: Optimized (always use `individual_rating`)
- ❌ `StatisticService`: Not optimized (conditional eager loading)
- **Result:** Inconsistent performance across features

**After this optimization:**
- ✅ `RankingService`: Optimized
- ✅ `StatisticService`: Optimized
- **Result:** Consistent fast performance for ALL baseline types

**Benefits:**
- Predictable performance characteristics
- Easier to maintain and debug
- Consistent user experience

### 5. The Cost of Model Hydration

**Concrete Example from This Optimization:**

```
Eager Loading Cost:
├─ 9,866 AspectAssessment models
├─ 49,330 SubAspectAssessment models
├─ ~5 SubAspect models per assessment
└─ Total: 59,257 model instances

Memory Impact:
├─ Each model: ~1-2KB (with relations)
├─ Total memory: ~60-120MB
└─ Garbage collection overhead: Significant

Time Impact:
├─ Query time: 250ms (2 queries)
├─ Hydration time: ~7.5 seconds
└─ Total: ~8 seconds

Alternative (Raw Query):
├─ Query time: 51ms (2 queries)
├─ Hydration time: 0ms (stdClass)
└─ Total: ~600ms

Speedup: 13x faster, 99.92% less memory
```

**Lesson:** For aggregation/statistics, **raw SQL >> Eloquent models**.

---

## 🔄 Implementation Checklist

If you're implementing similar optimization:

- [ ] **Identify** if data is IMMUTABLE or CONFIGURABLE
- [ ] **Verify** that baseline changes don't affect source data
- [ ] **Remove** conditional logic based on baseline type
- [ ] **Use** direct SQL queries for aggregation/counting
- [ ] **Avoid** model hydration when not needed
- [ ] **Test** performance with BOTH baseline types
- [ ] **Verify** results are mathematically identical
- [ ] **Document** the optimization reasoning
- [ ] **Apply** pattern consistently across similar services
- [ ] **Enable** caching after testing (e.g., 60s TTL)

---

## 📚 Related Documentation

### Performance Optimization Docs
- [CRITICAL_FIX_CUSTOM_STANDARD_PERFORMANCE.md](./CRITICAL_FIX_CUSTOM_STANDARD_PERFORMANCE.md)
  - RankingService optimization (same pattern)
  - Explains 3-layer priority system
  - Cache invalidation strategy

- [OPTIMASI_STANDARDMC_PERFORMANCE.md](./OPTIMASI_STANDARDMC_PERFORMANCE.md)
  - StandardMc component optimization
  - Component-level caching
  - Livewire optimization

### Business Concepts
- [SPSP_BUSINESS_CONCEPTS.md](./SPSP_BUSINESS_CONCEPTS.md)
  - 3-Layer Priority System (CRITICAL)
  - Data Immutability Principle
  - BI System vs CRUD System

### Architecture Reference
- `app/Services/RankingService.php` (lines 100-146)
  - Reference implementation of this pattern
  - Comments explain the reasoning
  - Already proven in production

---

## 🎯 Summary

### Problem
Custom Standard baseline was **11.3x slower** than Quantum Default due to unnecessary eager loading of 59K models.

### Root Cause
Conditional logic that assumed Custom Standard required recalculating `individual_rating` from sub-aspects.

### Solution
Remove conditional logic and **always** use fast SQL queries with stored `individual_rating`.

### Result
- ✅ **92% performance improvement** for Custom Standard
- ✅ **99.92% reduction** in models loaded
- ✅ **Performance parity** between all baseline types
- ✅ **Consistent pattern** across ranking-related services

### Key Insight
> **Custom Standard changes the LENS (baseline), not the DATA (individual_rating).**
>
> Therefore, distribution and average calculations should ALWAYS use stored assessment data, regardless of baseline type.

---

**Date Created:** December 2025
**Optimization Type:** Critical Performance Fix
**Impact:** High (11.3x speedup for major use case)
**Status:** ✅ Completed & Tested
**Related Services:** StatisticService, RankingService, DynamicStandardService
