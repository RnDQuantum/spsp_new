# Case Study: Ranking Performance Optimization

**Date**: December 2024
**Target**: `RankingPsyMapping` Livewire Component & `RankingService`
**Goal**: Reduce load time from ~30s to <2s.

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

## 📝 Checklist for Optimizing Other Livewire Components

If you see high memory usage or slow speeds in other 'Rekap' or 'Ranking' pages, check for:

1.  **Select ***: Are you loading all columns when you only need IDs?
2.  **Eager Loading**: Are you loading relationships that are conditional?
3.  **Hydration**: Are you hydrating 1000+ Eloquent models just to count them or sort them? Use `toBase()` or `pluck()`.
4.  **Pagination**: Are you hydrating the full list specific details (Pivot tables, User relationships) *before* the pagination slice?
