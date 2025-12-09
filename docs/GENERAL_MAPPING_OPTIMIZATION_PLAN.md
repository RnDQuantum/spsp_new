# General Mapping Performance Optimization Plan

**Date**: December 2024
**Component**: `GeneralMapping` Livewire Component
**Current Performance**: 1.88s request time, 509ms query time
**Target**: <1s request time, <250ms query time
**Status**: 🟡 Planning Phase

---

## 📊 Current Performance Analysis

### Debug Bar Metrics
- **Request Time**: 1.88s
- **Total Query Time**: 509ms (27% of request time)
- **Total Queries**: 57 statements (38 duplicates = 66%)
- **Models Hydrated**: 300 instances

### Slowest Operations
1. **RankingService Queries**: 472ms (93% of query time) ⚠️ **CRITICAL**
   - Potensi query: 192ms - fetches ALL aspect_assessments for ALL participants
   - Kompetensi query: 280ms - fetches ALL aspect_assessments for ALL participants

2. **Duplicate IndividualAssessmentService Calls**: ~80ms
   - `getAspectAssessments()` called 3 times with same parameters
   - Each call performs 5-7 separate queries

3. **DynamicStandardService Queries**: ~30ms
   - `getActiveAspectIds()` called multiple times for same template
   - Redundant category_types queries (6 times)

---

## 🔥 Root Cause Analysis

### Problem 1: Inefficient Ranking Lookup (CRITICAL - 472ms)

**Current Flow**:
```php
GeneralMapping::getParticipantRanking()
  → RankingService::getParticipantCombinedRank($participantId)
    → RankingService::getCombinedRankings() // ⚠️ Gets ALL participants
      → getRankings($eventId, ..., 'potensi')    // 192ms - ALL participants
      → getRankings($eventId, ..., 'kompetensi') // 280ms - ALL participants
    → Collection::firstWhere('participant_id', $participantId) // Find 1 from 1000+
```

**Why This is Bad**:
- To display ranking for **1 participant**, we process data for **ALL participants** (could be 1000+)
- Fetches aspect_assessments for all participants: `SELECT * FROM aspect_assessments WHERE event_id = 1 AND position_formation_id = 2`
- Hydrates hundreds of AspectAssessment models
- Performs calculations for all participants, then discards 99.9% of the results

**Impact**: 472ms wasted querying and processing data that's immediately discarded

### Problem 2: Duplicate Service Calls (80ms)

**Line 180-195** in `GeneralMapping.php`:
```php
// Called TWICE with same parameters
$potensiAspects = $service->getAspectAssessments(
    $this->participant->id,
    $this->potensiCategory->id,
    $this->tolerancePercentage
); // 7 queries

$kompetensiAspects = $service->getAspectAssessments(
    $this->participant->id,
    $this->kompetensiCategory->id,
    $this->tolerancePercentage
); // 7 queries
```

Each call triggers:
- `AspectAssessment::with(['aspect.subAspects', 'subAspectAssessments.subAspect'])->get()`
- Multiple relationship queries for aspects, sub_aspects, etc.

**Solution Exists**: `IndividualAssessmentService::getAllAspectMatchingData()` already batches this!

### Problem 3: Category Type Queries (6x duplicate)

The query `SELECT * FROM category_types WHERE template_id = 1 AND code = 'potensi'` runs **6 times**:
- Line 127, 131 in GeneralMapping
- Line 297 (x2) in IndividualAssessmentService
- Line 613 (x2) in DynamicStandardService

---

## 🚀 Optimization Strategies

### Strategy 1: Optimize Ranking Lookup (Target: 472ms → 50ms)

**Priority**: 🔴 CRITICAL (saves ~420ms)

**Approach A: Single Participant Query** (Recommended)
Create optimized method that only fetches data for target participant:

```php
// New method in RankingService
public function getParticipantCombinedRankOptimized(
    int $participantId,
    int $eventId,
    int $positionFormationId,
    int $templateId,
    int $tolerancePercentage = 10
): ?array {
    // Step 1: Get ONLY target participant's scores (2 fast queries)
    $participantScore = $this->calculateParticipantCombinedScore(
        $participantId, $eventId, $positionFormationId,
        $templateId, $tolerancePercentage
    );

    // Step 2: Count how many participants scored HIGHER (1 query with WHERE clause)
    $higherCount = DB::table('aspect_assessments')
        ->join('participants', ...)
        ->where('event_id', $eventId)
        ->where('position_formation_id', $positionFormationId)
        ->havingRaw('weighted_total_score > ?', [$participantScore['total_score']])
        ->count();

    $rank = $higherCount + 1;

    // Step 3: Count total participants (1 cached query)
    $totalCount = $this->getTotalParticipants($eventId, $positionFormationId);

    return [
        'rank' => $rank,
        'total' => $totalCount,
        'score' => $participantScore['total_score'],
        'conclusion' => $participantScore['conclusion'],
        'percentage' => $participantScore['percentage'],
    ];
}
```

**Expected Result**:
- 3-4 targeted queries instead of processing all participants
- 472ms → ~50ms (89% reduction)

**Approach B: Lazy Ranking** (Alternative if Approach A is complex)
Only call ranking when `showRankingInfo = true`:

```php
public function getParticipantRanking(): ?array
{
    if (!$this->showRankingInfo) {
        return null; // Skip if not displayed
    }

    // Check cache first
    if ($this->participantRankingCache !== null) {
        return $this->participantRankingCache;
    }

    // ... existing code
}
```

**Expected Result**: Conditional load saves 472ms when ranking not displayed

---

### Strategy 2: Batch Load Aspect Assessments (Target: 80ms → 20ms)

**Priority**: 🟠 HIGH (saves ~60ms)

**Current**:
```php
// Line 180
$potensiAspects = $service->getAspectAssessments(...); // 7 queries

// Line 190
$kompetensiAspects = $service->getAspectAssessments(...); // 7 queries
```

**Optimized**:
```php
// Single call loads both categories
$aspectsData = $service->getAllAspectMatchingData($this->participant);

$potensiAspects = collect($aspectsData['potensi'] ?? []);
$kompetensiAspects = collect($aspectsData['kompetensi'] ?? []);
```

**Benefits**:
- Single query fetches all aspect_assessments for both categories
- Relationships loaded once
- 14 queries → 4 queries

---

### Strategy 3: Cache Category Types (Target: 30ms → 5ms)

**Priority**: 🟡 MEDIUM (saves ~25ms)

**Problem**: `CategoryType::where('template_id', 1)->where('code', 'potensi')` runs 6 times

**Solution**: Use `AspectCacheService` which already caches this:

```php
// Before (Line 125-131)
$this->potensiCategory = CategoryType::where('template_id', $template->id)
    ->where('code', 'potensi')
    ->first();

// After
AspectCacheService::preloadByTemplate($template->id);
$this->potensiCategory = AspectCacheService::getCategoryByCode($template->id, 'potensi');
```

**Benefits**: 6 queries → 1 query (with cache)

---

### Strategy 4: Early Return for Hidden Components (Target: 0ms base saving)

**Priority**: 🟢 LOW (improves worst-case scenarios)

Skip expensive operations when components are hidden:

```php
public function mount(...): void
{
    // ... load participant, categories

    // Only load expensive data if displayed
    if ($this->showTable || $this->showRatingChart || $this->showScoreChart) {
        $this->loadAspectsData();
        $this->calculateTotals();
    }

    if ($this->showRatingChart || $this->showScoreChart) {
        $this->prepareChartData();
    }

    // Lazy load ranking only when needed
    // Already implemented in getParticipantRanking()
}
```

---

## 📋 Implementation Checklist

### Phase 1: Critical Performance (Target: 1.88s → 1.0s)
- [ ] **Task 1.1**: Implement `getParticipantCombinedRankOptimized()` in RankingService
  - [ ] Write method that queries only target participant's data
  - [ ] Calculate rank using COUNT query with HAVING clause
  - [ ] Add unit tests (follow existing pattern in RankingServiceTest)
  - [ ] Update GeneralMapping to use optimized method

- [ ] **Task 1.2**: Batch load aspect assessments
  - [ ] Replace duplicate `getAspectAssessments()` calls with `getAllAspectMatchingData()`
  - [ ] Verify data structure matches expectations
  - [ ] Update chart preparation to use new data structure

### Phase 2: Query Optimization (Target: 509ms → 250ms)
- [ ] **Task 2.1**: Cache category types using AspectCacheService
  - [ ] Preload cache in mount()
  - [ ] Replace CategoryType queries with cache calls

- [ ] **Task 2.2**: Conditional data loading
  - [ ] Check display flags before expensive operations
  - [ ] Add early returns for hidden components

### Phase 3: Validation & Testing
- [ ] **Task 3.1**: Run existing tests
  - [ ] `php artisan test --filter=GeneralMapping`
  - [ ] Verify all tests pass

- [ ] **Task 3.2**: Profile with debug bar
  - [ ] Verify query count reduction (57 → <30)
  - [ ] Verify duplicate reduction (38 → <10)
  - [ ] Verify request time (<1s)

- [ ] **Task 3.3**: Manual testing
  - [ ] Test with different tolerance values
  - [ ] Test with standard adjustments
  - [ ] Test with hidden components (showRankingInfo=false)

### Phase 4: Code Formatting
- [ ] Run `vendor/bin/pint --dirty` to format code

---

## 🎯 Expected Results

| Metric | Before | After | Improvement |
|--------|--------|-------|-------------|
| **Request Time** | 1.88s | ~0.8s | **57% faster** |
| **Query Time** | 509ms | ~200ms | **61% faster** |
| **Total Queries** | 57 | ~25 | **56% reduction** |
| **Duplicate Queries** | 38 | ~5 | **87% reduction** |
| **RankingService Time** | 472ms | ~50ms | **89% faster** |
| **Models Hydrated** | 300 | ~100 | **67% reduction** |

---

## 🔍 Verification Commands

```bash
# Run debug bar on the page
# Navigate to: /individual-report/{eventCode}/{testNumber}/general-mapping

# Check queries in debug bar:
# - Total queries should be ~25
# - Duplicate queries should be <10
# - RankingService queries should be <100ms
# - Request time should be <1s

# Run tests
php artisan test --filter=GeneralMapping
php artisan test tests/Unit/Services/RankingServiceTest.php

# Format code
vendor/bin/pint --dirty
```

---

## 📚 Related Documents

- [CASE_STUDY_RANKING_OPTIMIZATION.md](./CASE_STUDY_RANKING_OPTIMIZATION.md) - Similar optimization patterns
- [SERVICE_ARCHITECTURE.md](./SERVICE_ARCHITECTURE.md) - Service layer documentation
- [RANKING_TEST_STRATEGY.md](./RANKING_TEST_STRATEGY.md) - Testing guidelines

---

## 🤔 Technical Considerations

### Why Not Cache Rankings?
Caching full rankings has issues:
- Cache invalidation complexity (tolerance changes, standard adjustments)
- Memory usage for large participant lists
- Stale data risks

**Better approach**: Optimize query to be fast enough without caching

### Why Single Participant Query is Better
Traditional pagination approach (fetch all → sort → slice) doesn't work for ranking because:
1. We need to know position BEFORE slicing
2. Sorting by calculated fields requires PHP processing
3. Solution: Calculate target participant's score, then COUNT how many scored higher

### Backward Compatibility
- Existing `getParticipantCombinedRank()` method kept for backward compatibility
- New optimized method is separate
- Components can migrate gradually

---

## 📝 Notes

- This optimization follows the same pattern as RankingService Phase 1 & 2 optimizations
- Focus on reducing unnecessary data processing
- Use database for what it's good at (counting, filtering)
- Use PHP for what it's good at (complex calculations on small datasets)
