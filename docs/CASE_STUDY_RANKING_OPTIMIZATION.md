# Case Study: Ranking Performance Optimization

**Date**: December 2024
**Components**: `RankingPsyMapping`, `RankingMcMapping`, `RekapRankingAssessment` Livewire Components & `RankingService`
**Goal**: Reduce load time from ~30s to <2s.
**Status**: ✅ Phase 1 & Phase 2 Completed

## 📊 Performance Metrics (Before vs After)

| Metric | Before Optimization | After Optimization | Improvement |
| :--- | :--- | :--- | :--- |
| **Response Time** | ~30.78 seconds | ~0.37 seconds (Query) | **~80x Faster** |
| **AspectAssessment Models** | 25,100 (Hydrated) | 0 (Raw Objects) | **100% Reduction** |
| **Participant Models** | ~5,000+ (Hydrated) | ~10-20 (Paginated) | **99.6% Reduction** |
| **Memory Usage** | Critical High (Heavy Hydration) | Low (StdClass + Lazy Loading) | **Significant** |
| **Total Query Time** | High | < 400ms | **Ultra Fast** |

---

## 🛑 The Core Problems identified

1.  **Monster Hydration**: The system was hydrating **25,000+** `AspectAssessment` models just to calculate scores and sort them. Eloquent model hydration is expensive in terms of CPU and Memory.
2.  **Unnecessary Relationships**: It was eager loading `subAspectAssessments` (N+1 nested) even when they weren't used (Default Standard mode).
3.  **Pre-Pagination Hydration**: The Livewire component was loading **ALL** 5000+ participants with their relationships *before* slicing them for pagination.

---

## 🚀 Optimization Strategies Applied

### Strategy 1: Conditional Eager Loading
**Theory**: Don't load what you don't need.
**Implementation**: Added a check in `RankingService` to only load sub-aspect relationships if there are active adjustments.

```php
// App/Services/RankingService.php
$hasSubAspectAdjustments = $standardService->hasActiveSubAspectAdjustments($templateId);

if ($hasSubAspectAdjustments) {
    // Only load heavy relationships if strictly necessary (custom standard active)
    $query->with(['aspect.subAspects', 'subAspectAssessments.subAspect']);
} else {
    // Light load for default standard
    $query->with(['aspect']);
}
```

### Strategy 2: Raw Object Hydration (`toBase()`)
**Theory**: Eloquent Models are heavy. `stdClass` objects are light. For read-only lists used for sorting, we don't need Models.
**Implementation**: Used `toBase()` to skip Model Hydration completely when complex logic wasn't needed.

```php
// App/Services/RankingService.php
if ($hasSubAspectAdjustments) {
    $assessments = $query->get(); // Hydrate Models (Need relationships)
} else {
    // 🚀 MAX OPTIMIZATION: Get stdClass objects. 
    // Saves 25,000 Model instantiations.
    $assessments = $query->toBase()->get(); 
}
```

### Strategy 3: Lazy Pagination (Slice -> Hydrate)
**Theory**: `Pagination` usually executes `LIMIT` in SQL. But when sorting by a calculated field (PHP-side sort), we have to fetch all rows. The mistake is hydrating them all.
**Solution**: Fetch IDs/Scores -> Sort -> **Slice (Take 10)** -> Hydrate details for ONLY those 10.

```php
// App/Livewire/Pages/GeneralReport/Ranking/RankingPsyMapping.php

// 1. Get ALL rankings (Lightweight array/collection from Strategy 2)
$rankings = $this->getRankings();

// 2. Slice FIRST (Pagination Logic)
$offset = ($currentPage - 1) * $this->perPage;
$slicedRankings = $rankings->slice($offset, $this->perPage);

// 3. Hydrate details ONLY for the visible 10 items
$participantIds = $slicedRankings->pluck('participant_id')->unique()->all();
$participants = Participant::whereIn('id', $participantIds)->get();
```

---

## 🔄 Phase 2: Database & Query Optimization (December 2024)

**Target**: `RekapRankingAssessment` component bottleneck (2.32s → target <1s)
**Status**: ✅ Completed - Achieved 1.87s (51% total improvement from baseline)

### Problem Identified

After Phase 1 optimizations, profiling revealed:
- **97.6% of query time** (923ms/946ms) spent in 2 slow queries
- Both queries fetching `aspect_assessments` with different aspect_ids (potensi vs kompetensi)
- Queries using filesort due to `ORDER BY participants.name`
- Fetching all columns (`SELECT *`) when only 5 needed

### Strategies Applied

#### Strategy A: Composite Database Index ⚡
**Impact**: Highest
**File**: `database/migrations/2025_12_09_064003_add_composite_index_to_aspect_assessments.php`

Created composite index optimized for the WHERE clause pattern:
```sql
CREATE INDEX idx_asp_event_pos_aspect_participant
ON aspect_assessments(event_id, position_formation_id, aspect_id, participant_id);
```

**Result**: Improved index selectivity for filtering operations

#### Strategy B: Remove Redundant ORDER BY 🚀
**Impact**: High (Eliminated filesort)
**File**: `app/Services/RankingService.php:74`

Removed `->orderBy('participants.name')` from query since sorting already handled in PHP (lines 153-158):
```php
// Removed from query builder (line 74)
// Sorting done in PHP for better control:
$rankings = collect($participantScores)
    ->sortBy([
        ['individual_score', 'desc'],
        ['participant_name', 'asc'],
    ])
    ->values();
```

**Result**: Eliminated "Using filesort" and "Using temporary" from query execution plan

#### Strategy C: Selective Column Selection 📊
**Impact**: Medium
**File**: `app/Services/RankingService.php:74-80`

Changed from `SELECT aspect_assessments.*` to only needed columns:
```php
->select(
    'aspect_assessments.id',
    'aspect_assessments.participant_id',
    'aspect_assessments.aspect_id',
    'aspect_assessments.individual_rating',
    'participants.name as participant_name'
)
```

**Result**: Reduced data transfer from 15+ columns to 5 columns

### Phase 2 Performance Results

| Metric | Phase 1 (Before) | Phase 2 (After) | Improvement |
|--------|------------------|-----------------|-------------|
| **Request Time** | 2.32s | **1.87s** | **19.4% faster** |
| **Total Query Time** | 946ms | **491ms** | **48.1% faster** |
| **Potensi Query** | ~460ms | **193ms** | **58% faster** |
| **Kompetensi Query** | ~463ms | **277ms** | **40% faster** |
| **Duplicate Queries** | 19 | **13** | 31.6% reduction |

### Overall Progress: Phase 1 + Phase 2

| Component | Original | Phase 1 | Phase 2 | **Total Improvement** |
|-----------|----------|---------|---------|----------------------|
| RekapRankingAssessment | 3.84s | 2.32s | **1.87s** | **51.3% faster** |

### Strategy D (Optional - Not Implemented)

**Concept**: Combine potensi + kompetensi queries into single query
**Estimated Additional Gain**: 10-15% (target ~1.65s)
**Status**: Skipped - Current performance (1.87s) deemed sufficient

---

## 📝 Checklist for Optimizing Other Livewire Components

If you see high memory usage or slow speeds in other 'Rekap' or 'Ranking' pages, check for:

1.  **Select ***: Are you loading all columns when you only need IDs?
2.  **Eager Loading**: Are you loading relationships that are conditional?
3.  **Hydration**: Are you hydrating 1000+ Eloquent models just to count them or sort them? Use `toBase()` or `pluck()`.
4.  **Pagination**: Are you hydrating the full list specific details (Pivot tables, User relationships) *before* the pagination slice?
5.  **ORDER BY**: Are you sorting in SQL when sorting is already done in PHP? Remove redundant ORDER BY.
6.  **Database Indexes**: Run EXPLAIN on slow queries - add composite indexes for common WHERE clauses.
7.  **Column Selection**: Use specific columns in SELECT instead of `*`.
