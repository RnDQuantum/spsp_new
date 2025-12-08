# Performance Optimization Guide - SPSP Assessment System

> **Version**: 2.0
> **Last Updated**: 2025-12-08
> **Status**: 🔴 **CRITICAL PERFORMANCE ISSUES IDENTIFIED**

---

## 📊 Executive Summary

### Current Performance Status (20K Participants, 3 Positions)

| Page Category | Page Name | Current Duration | Status | Priority |
|---------------|-----------|------------------|--------|----------|
| **Individual Report** | GeneralMatching | 514ms | ✅ OK | Low |
| **Individual Report** | GeneralPsyMapping | 8.31s | 🔴 CRITICAL | High |
| **Individual Report** | GeneralMcMapping | 5.51s | 🟡 WARNING | Medium |
| **Individual Report** | GeneralMapping | 15.1s | 🔴 CRITICAL | High |
| **Individual Report** | RingkasanMcMapping | 393ms | ✅ OK | Low |
| **Individual Report** | RingkasanAssessment | 383ms | ✅ OK | Low |
| **General Report** | RankingPsyMapping | 10.71s | 🔴 CRITICAL | High |
| **General Report** | RankingMcMapping | 7.32s | 🔴 CRITICAL | High |
| **General Report** | RekapRankingAssessment | **30.78s** | 🔴 **WORST** | **CRITICAL** |
| **General Report** | Statistic | 3.59s | 🟡 WARNING | Medium |
| **General Report** | TrainingRecommendation | 4.25s | 🟡 WARNING | Medium |

**Total Slow Pages: 8 of 11** (73% of pages need optimization)

---

## 🔍 Root Cause Analysis

### Problem 1: ❌ NO REDIS CACHING

**Current State:**
- `AspectCacheService.php` exists but is **only in-memory cache per-request**
- All services (`RankingService`, `StatisticService`, `IndividualAssessmentService`) recalculate everything from scratch on every request
- No persistent caching layer

**Evidence:**
```php
// AspectCacheService.php - Line 24-32
private static array $aspectCache = [];        // ← PHP static array, NOT Redis
private static array $subAspectCache = [];     // ← Cleared after request ends
private static array $categoryCache = [];      // ← No persistence
```

**Impact:**
- Every page load = full database query + calculation
- Changing tolerance from 10% → 20% = recalculate everything (15-30s)
- Same data queried multiple times per request

---

### Problem 2: 🔥 MASSIVE N+1 QUERY - RekapRankingAssessment (30.78s)

**The Killer Query:**
```sql
-- This query executes in 464-566ms and is called MULTIPLE times
select * from `sub_aspect_assessments`
where `aspect_assessment_id` in (
    14, 15, 16, 17, 18, 39, 40, 41, ..., 249802, 249803, 249804
)
-- That's 20,000+ IDs in a single WHERE IN clause!
```

**Why This Happens:**

**File:** `app/Services/RankingService.php` (Line 55-63)
```php
// Load ALL 20K participants at once
$assessments = AspectAssessment::query()
    ->with(['aspect.subAspects', 'subAspectAssessments.subAspect'])  // ← Eager loading triggers N+1
    ->join('participants', 'participants.id', '=', 'aspect_assessments.participant_id')
    ->where('aspect_assessments.event_id', $eventId)
    ->where('aspect_assessments.position_formation_id', $positionFormationId)
    ->whereIn('aspect_assessments.aspect_id', $activeAspectIds)
    ->get();  // ← Loads everything into memory
```

**Execution Flow:**
1. Load 20,000 `aspect_assessments` records (~300ms) ✅
2. Laravel eager loads `subAspectAssessments` for ALL 20K records
3. Generates query: `WHERE aspect_assessment_id IN (14, 15, ..., 249804)`
4. MySQL executes query with **20,000+ IDs** in IN clause (**464-566ms**) ❌
5. Query executed **6 times** (for different categories) = **2.7-3.4 seconds just on sub_aspect queries**
6. Then loop through 20K records in PHP memory (line 72-107) = **additional 5-10 seconds**

**Total: 30.78 seconds** 🔥

---

### Problem 3: 🔄 DUPLICATE QUERIES

**Example: GeneralMapping (15.1s)**

**Debugbar Output:**
- Total queries: 71 statements
- Duplicates: 49 statements (69% duplication!)

**Most Duplicated Queries:**
```sql
-- Executed 4-6 times per request:
select * from `sub_aspects` where `aspect_id` in (1, 2, 3, 4, 5)
select * from `category_types` where `template_id` = 1 and `code` = 'potensi'
select * from `participants` where `id` = 1154
```

**Why:**
- `RankingService` doesn't use `AspectCacheService`
- `StatisticService` doesn't use `AspectCacheService`
- `IndividualAssessmentService` uses it but gets called multiple times
- Each service queries the same data independently

---

### Problem 4: 🐌 LOAD ALL RECORDS WITHOUT PAGINATION

**StatisticService.php** (Line 190-194):
```php
// Load ALL 20K aspect_assessments for ONE aspect
$assessments = AspectAssessment::with('subAspectAssessments.subAspect')
    ->where('event_id', $eventId)
    ->where('position_formation_id', $positionFormationId)
    ->where('aspect_id', $aspect->id)
    ->get();  // ← No pagination, no chunking
```

Then loop through all 20K records **twice** (for distribution + average calculation).

---

## 🎯 Optimization Strategy

### Architecture Overview

```
┌─────────────────────────────────────────────────────────┐
│                    OPTIMIZATION LAYERS                   │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  Layer 1: Redis Cache - Master Data (24h TTL)          │
│  ├─ Aspects, SubAspects, CategoryTypes                  │
│  ├─ Participants metadata                               │
│  └─ Template configurations                             │
│                                                          │
│  Layer 2: Redis Cache - Calculation Results (15-60m)   │
│  ├─ Ranking results (per event+position+tolerance)      │
│  ├─ Statistics (per event+position+aspect)              │
│  └─ Individual assessments (per participant)            │
│                                                          │
│  Layer 3: Query Optimization                            │
│  ├─ Fix N+1 queries (chunk processing)                  │
│  ├─ Database indexes                                     │
│  └─ SQL aggregation instead of PHP loops                │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

---

## 📋 Implementation Plan

### ✅ Phase 0: Prerequisites (COMPLETED)

- [x] Redis installed via Docker
- [x] Predis package installed (`composer require predis/predis`)
- [x] `.env` configured with Redis connection
- [x] Connection tested and working

---

### 🔥 Phase 1: Master Data Redis Caching (PRIORITY 1)

**Goal:** Replace in-memory `AspectCacheService` with Redis-backed caching

**Estimated Time:** 2-3 hours
**Expected Improvement:** 30-50% reduction across all pages
**Risk Level:** Low (easy to rollback)

#### 1.1 Create New Service: `MasterDataCacheService`

**File:** `app/Services/Cache/MasterDataCacheService.php`

**Responsibilities:**
- Cache all master data with Redis (not just in-memory)
- TTL: 24 hours for mostly-static data
- Cache invalidation when data changes
- Backward compatible with existing code

**Cache Keys Pattern:**
```
master:template:{templateId}:aspects           → Collection of Aspect models
master:template:{templateId}:sub_aspects       → Collection of SubAspect models
master:template:{templateId}:categories        → Collection of CategoryType models
master:aspect:{aspectId}                       → Single Aspect model
master:category:{categoryId}                   → Single CategoryType model
master:participant:{participantId}             → Single Participant model
```

**Methods to Implement:**
```php
class MasterDataCacheService
{
    private const TTL = 86400; // 24 hours

    public static function preloadTemplate(int $templateId): void;
    public static function getAspectByCode(int $templateId, string $code): ?Aspect;
    public static function getSubAspectByCode(int $templateId, string $code): ?SubAspect;
    public static function getCategoryByCode(int $templateId, string $code): ?CategoryType;
    public static function getAspectsByCategory(int $templateId, string $categoryCode): Collection;
    public static function invalidateTemplate(int $templateId): void;
    public static function clearAll(): void;
}
```

#### 1.2 Update Services to Use New Cache

**Files to Modify:**
- `app/Services/RankingService.php`
- `app/Services/StatisticService.php`
- `app/Services/IndividualAssessmentService.php`
- `app/Services/DynamicStandardService.php`

**Changes:**
```php
// OLD: Direct database query
$aspect = Aspect::where('code', $code)->first();

// NEW: Redis-cached
$aspect = MasterDataCacheService::getAspectByCode($templateId, $code);
```

#### 1.3 Add Cache Warming

**File:** `app/Console/Commands/WarmMasterDataCache.php`

```bash
# Run this command to pre-warm cache after deployment
php artisan cache:warm-master-data
```

---

### 🚀 Phase 2: Calculation Results Caching (PRIORITY 1)

**Goal:** Cache expensive calculation results

**Estimated Time:** 3-4 hours
**Expected Improvement:** 70-90% reduction for ranking & statistics pages
**Risk Level:** Low-Medium (requires cache invalidation logic)

#### 2.1 Cache RankingService Results

**File:** `app/Services/RankingService.php`

**Method:** `getRankings()`

**Cache Key Pattern:**
```
ranking:{eventId}:{positionId}:{categoryCode}:{tolerance}
```

**Implementation:**
```php
public function getRankings(
    int $eventId,
    int $positionFormationId,
    int $templateId,
    string $categoryCode,
    int $tolerancePercentage = 10
): Collection {
    $cacheKey = "ranking:{$eventId}:{$positionFormationId}:{$categoryCode}:{$tolerancePercentage}";

    return Cache::remember($cacheKey, now()->addMinutes(30), function () use (
        $eventId,
        $positionFormationId,
        $templateId,
        $categoryCode,
        $tolerancePercentage
    ) {
        // Existing calculation logic here...
        return $rankings;
    });
}
```

**TTL:** 30 minutes (configurable)

**Invalidation Triggers:**
- When user adjusts standards via `DynamicStandardService`
- When tolerance slider changes (different cache key automatically)
- Manual clear button

#### 2.2 Cache StatisticService Results

**File:** `app/Services/StatisticService.php`

**Method:** `getDistributionData()`

**Cache Key Pattern:**
```
statistics:{eventId}:{positionId}:{aspectId}:{templateId}
```

**TTL:** 30 minutes

#### 2.3 Cache IndividualAssessmentService Results

**File:** `app/Services/IndividualAssessmentService.php`

**Methods to Cache:**
- `getAspectAssessments()`
- `getCategoryAssessment()`
- `getFinalAssessment()`

**Cache Key Pattern:**
```
individual:{participantId}:{categoryCode}:{tolerance}
final:{participantId}:{tolerance}
```

**TTL:** 15 minutes (shorter because user-specific)

#### 2.4 Implement Cache Invalidation

**File:** `app/Services/DynamicStandardService.php`

Add cache invalidation to all save methods:

```php
public function saveBulkSelection(int $templateId, array $data): void
{
    // ... existing save logic ...

    // Invalidate calculation caches
    $this->invalidateCalculationCaches($templateId);
}

private function invalidateCalculationCaches(int $templateId): void
{
    // Clear all ranking caches for this template
    Cache::tags(["rankings:template:{$templateId}"])->flush();

    // Clear all statistics caches for this template
    Cache::tags(["statistics:template:{$templateId}"])->flush();

    // Clear all individual assessment caches for this template
    Cache::tags(["individual:template:{$templateId}"])->flush();
}
```

**Note:** Requires Redis (not file/database cache driver) for tag support.

---

### ⚡ Phase 3: Query Optimization (PRIORITY 2)

**Goal:** Fix N+1 queries and optimize database access

**Estimated Time:** 4-5 hours
**Expected Improvement:** Additional 30-40% on first-load (uncached)
**Risk Level:** Medium (requires code refactoring)

#### 3.1 Fix N+1 in RankingService

**Problem:** Loading 20K records with eager loading causes massive WHERE IN queries

**Solution 1: Chunk Processing**

```php
// OLD: Load everything at once
$assessments = AspectAssessment::query()
    ->with(['aspect.subAspects', 'subAspectAssessments.subAspect'])
    ->get(); // ← 20K records

// NEW: Process in chunks
$participantScores = [];

AspectAssessment::query()
    ->with(['aspect.subAspects', 'subAspectAssessments.subAspect'])
    ->where('event_id', $eventId)
    ->where('position_formation_id', $positionFormationId)
    ->whereIn('aspect_id', $activeAspectIds)
    ->chunk(1000, function ($assessments) use (&$participantScores, $standardService, $templateId) {
        foreach ($assessments as $assessment) {
            // Process each chunk...
        }
    });
```

**Solution 2: Database Aggregation (Advanced)**

Move calculations to SQL:

```php
$rankings = DB::select("
    SELECT
        p.id as participant_id,
        p.name as participant_name,
        SUM(aa.individual_rating) as total_rating,
        SUM(aa.individual_rating * a.weight_percentage / 100) as total_score
    FROM aspect_assessments aa
    JOIN participants p ON p.id = aa.participant_id
    JOIN aspects a ON a.id = aa.aspect_id
    WHERE aa.event_id = ?
    AND aa.position_formation_id = ?
    AND aa.aspect_id IN (?)
    GROUP BY p.id, p.name
    ORDER BY total_score DESC, p.name ASC
", [$eventId, $positionFormationId, implode(',', $activeAspectIds)]);
```

**Trade-off:** SQL aggregation is faster but harder to maintain with dynamic weights from session.

**Recommendation:** Use **Chunk Processing + Redis Cache** (Phase 1+2 first), then consider SQL aggregation if still slow.

#### 3.2 Add Database Indexes

**File:** Create new migration

```bash
php artisan make:migration add_performance_indexes_to_assessments
```

**Migration Content:**

```php
public function up(): void
{
    Schema::table('aspect_assessments', function (Blueprint $table) {
        // Composite index for common query pattern
        $table->index(['event_id', 'position_formation_id', 'aspect_id'],
                      'idx_aspect_assess_event_pos_aspect');
    });

    Schema::table('sub_aspect_assessments', function (Blueprint $table) {
        // Index for eager loading
        $table->index('aspect_assessment_id', 'idx_sub_aspect_assess_aspect_id');
    });

    Schema::table('participants', function (Blueprint $table) {
        // Index for ranking queries (if not exists)
        $table->index(['event_id', 'position_formation_id'],
                      'idx_participants_event_position');
    });
}
```

**Expected Impact:** 20-30% faster queries

#### 3.3 Optimize StatisticService

**Problem:** Loading 20K records twice (distribution + average)

**Solution:** Combine calculations in single loop

```php
// OLD: Two separate queries + loops
$distribution = $this->calculateDistribution(...);  // Loop 1
$averageRating = $this->calculateAverageRating(...); // Loop 2

// NEW: Single query + single loop
$assessments = AspectAssessment::with('subAspectAssessments.subAspect')
    ->where('event_id', $eventId)
    ->where('position_formation_id', $positionFormationId)
    ->where('aspect_id', $aspect->id)
    ->get();

$distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
$totalRating = 0;
$count = 0;

foreach ($assessments as $assessment) {
    $recalculatedRating = $this->calculateIndividualRatingFromSubAspects(...);

    // Calculate distribution
    $bucket = $this->getRatingBucket($recalculatedRating);
    $distribution[$bucket]++;

    // Calculate average
    $totalRating += $recalculatedRating;
    $count++;
}

$averageRating = $count > 0 ? $totalRating / $count : 0;
```

---

## 📊 Expected Performance After Optimization

### Before vs After (20K Participants)

| Page Name | Before | After Phase 1+2 | After Phase 3 | Total Improvement |
|-----------|--------|-----------------|---------------|-------------------|
| **RekapRankingAssessment** | 30.78s | **< 1s** (cached) | < 3s (uncached) | **30-97% faster** |
| **GeneralMapping** | 15.1s | **< 1s** (cached) | < 2s (uncached) | **87-93% faster** |
| **RankingPsyMapping** | 10.71s | **< 0.5s** (cached) | < 1.5s (uncached) | **85-95% faster** |
| **GeneralPsyMapping** | 8.31s | **< 0.5s** (cached) | < 1.2s (uncached) | **85-94% faster** |
| **RankingMcMapping** | 7.32s | **< 0.5s** (cached) | < 1s (uncached) | **86-93% faster** |
| **GeneralMcMapping** | 5.51s | **< 0.4s** (cached) | < 0.8s (uncached) | **85-93% faster** |
| **TrainingRecommendation** | 4.25s | **< 0.3s** (cached) | < 0.6s (uncached) | **86-93% faster** |
| **Statistic** | 3.59s | **< 0.3s** (cached) | < 0.5s (uncached) | **86-92% faster** |

**Cache Hit Ratio Estimate:** 80-90% (most users view same data repeatedly)

---

## 🛠️ Implementation Checklist

### Phase 1: Master Data Redis Caching

- [ ] Create `MasterDataCacheService.php`
- [ ] Implement cache methods with Redis backend
- [ ] Update `RankingService` to use new cache
- [ ] Update `StatisticService` to use new cache
- [ ] Update `IndividualAssessmentService` to use new cache
- [ ] Update `DynamicStandardService` to use new cache
- [ ] Create `WarmMasterDataCache` command
- [ ] Test cache hit/miss with debugbar
- [ ] Verify data consistency

### Phase 2: Calculation Results Caching

- [ ] Add caching to `RankingService::getRankings()`
- [ ] Add caching to `RankingService::getCombinedRankings()`
- [ ] Add caching to `StatisticService::getDistributionData()`
- [ ] Add caching to `IndividualAssessmentService::getAspectAssessments()`
- [ ] Add caching to `IndividualAssessmentService::getCategoryAssessment()`
- [ ] Add caching to `IndividualAssessmentService::getFinalAssessment()`
- [ ] Implement cache invalidation in `DynamicStandardService`
- [ ] Add cache tags support (requires Redis)
- [ ] Add manual cache clear button in UI
- [ ] Test cache invalidation triggers
- [ ] Monitor cache memory usage

### Phase 3: Query Optimization

- [ ] Implement chunk processing in `RankingService::getRankings()`
- [ ] Combine distribution + average loops in `StatisticService`
- [ ] Create performance indexes migration
- [ ] Run migration on production database
- [ ] Test query performance with EXPLAIN
- [ ] Monitor slow query log
- [ ] Consider SQL aggregation for rankings (optional)

### Testing & Monitoring

- [ ] Load test with 20K participants
- [ ] Load test with 50K participants
- [ ] Verify cache invalidation works correctly
- [ ] Monitor Redis memory usage
- [ ] Add performance monitoring logs
- [ ] Create cache statistics dashboard
- [ ] Document cache key patterns
- [ ] Create runbook for cache issues

---

## 🔧 Cache Management

### Cache Key Patterns Reference

```
# Master Data (TTL: 24 hours)
master:template:{templateId}:aspects
master:template:{templateId}:sub_aspects
master:template:{templateId}:categories
master:aspect:{aspectId}
master:sub_aspect:{subAspectId}
master:category:{categoryId}
master:participant:{participantId}

# Calculation Results (TTL: 15-30 minutes)
ranking:{eventId}:{positionId}:{categoryCode}:{tolerance}
combined_ranking:{eventId}:{positionId}:{tolerance}
statistics:{eventId}:{positionId}:{aspectId}:{templateId}
individual:{participantId}:{categoryCode}:{tolerance}
final:{participantId}:{tolerance}

# Cache Tags (for invalidation)
rankings:template:{templateId}
statistics:template:{templateId}
individual:template:{templateId}
```

### Manual Cache Operations

```bash
# Clear all caches
php artisan cache:clear

# Clear specific tags (requires Redis)
php artisan tinker
>>> Cache::tags(['rankings:template:1'])->flush()

# Warm master data cache
php artisan cache:warm-master-data

# Monitor Redis
redis-cli
> INFO memory
> KEYS master:*
> KEYS ranking:*
> TTL ranking:1:2:potensi:10
> GET master:template:1:aspects
```

### Cache Invalidation Triggers

| Event | Cache to Invalidate | Method |
|-------|---------------------|--------|
| User adjusts standards | All ranking/stats/individual for template | `Cache::tags(["rankings:template:{id}"])->flush()` |
| User changes tolerance | None (different cache key) | Automatic |
| New assessment created | None (not cached yet) | N/A |
| Assessment deleted | All caches for template | Full invalidation |
| Template modified | All caches for template | Full invalidation |
| Manual clear button | Everything | `Cache::flush()` |

---

## 📈 Monitoring & Debugging

### Performance Metrics to Track

1. **Response Time:**
   - Monitor with Laravel Debugbar
   - Track P50, P95, P99 percentiles
   - Alert if > 2s on cached requests

2. **Cache Hit Ratio:**
   - Target: 80-90%
   - Monitor with Redis INFO stats
   - Track per page/service

3. **Memory Usage:**
   - Redis memory consumption
   - Alert if > 80% of available memory
   - Implement LRU eviction policy

4. **Query Count:**
   - Track SQL query count per request
   - Target: < 30 queries per page
   - Monitor duplicate queries

### Debugging Tools

**Laravel Debugbar:**
```php
// Check cache hits/misses
Debugbar::info('Cache hit', ['key' => $cacheKey]);
Debugbar::info('Cache miss', ['key' => $cacheKey]);

// Log performance
$start = microtime(true);
// ... operation ...
$duration = (microtime(true) - $start) * 1000;
Debugbar::info('Ranking calculation', ['duration_ms' => $duration]);
```

**Redis Monitoring:**
```bash
# Real-time monitor
redis-cli monitor

# Stats
redis-cli INFO stats

# Memory usage
redis-cli INFO memory

# Keyspace analysis
redis-cli --bigkeys
```

---

## ⚠️ Important Notes

### Cache Consistency

**Problem:** User A adjusts standards, User B still sees old cached results.

**Solution:** Use template-scoped cache tags:
- When User A saves adjustments → invalidate all caches for that template
- All users get fresh calculations on next request
- Acceptable trade-off: < 1% of requests are cache misses

### Session-Based Adjustments

**Problem:** Standards can differ per user session (DynamicStandardService uses session).

**Current Behavior:** Cache is shared across users, ignoring session differences.

**Impact Analysis:**
- Most users use default standards (90%+)
- Admin users adjust standards temporarily for analysis
- Cache invalidation happens when standards are saved

**Mitigation:**
1. **Short TTL** for calculation caches (15-30 min)
2. **Session-aware caching** (optional, more complex):
   ```php
   $sessionHash = md5(json_encode(Session::get("standard_adjustment.{$templateId}", [])));
   $cacheKey = "ranking:{eventId}:{positionId}:{categoryCode}:{tolerance}:{sessionHash}";
   ```

**Recommendation:** Start with shared cache + short TTL, then add session-aware caching if needed.

### Redis Memory Management

**Estimate:**
- 20K participants × 3 positions = 60K participant records
- Each cached ranking result ≈ 500KB (serialized Collection)
- Total per event: ~30MB
- With 10 events: ~300MB
- Add master data: ~50MB
- **Total: ~350MB**

**Redis Config:**
```
maxmemory 1gb
maxmemory-policy allkeys-lru
```

This ensures Redis automatically evicts least-recently-used keys when memory is full.

---

## 📚 Additional Resources

### Files to Review

**Services (Core Logic):**
- `app/Services/RankingService.php` - Ranking calculations (30.78s issue)
- `app/Services/StatisticService.php` - Statistics distribution (3.59s)
- `app/Services/IndividualAssessmentService.php` - Individual assessments (15.1s)
- `app/Services/DynamicStandardService.php` - Session-based adjustments
- `app/Services/Cache/AspectCacheService.php` - Current in-memory cache

**Livewire Components (Entry Points):**
- `app/Livewire/Pages/GeneralReport/RekapRankingAssessment.php`
- `app/Livewire/Pages/GeneralReport/RankingPsyMapping.php`
- `app/Livewire/Pages/GeneralReport/RankingMcMapping.php`
- `app/Livewire/Pages/IndividualReport/GeneralMapping.php`
- `app/Livewire/Pages/GeneralReport/Statistic.php`

### Laravel Caching Documentation

- [Cache Documentation](https://laravel.com/docs/12.x/cache)
- [Redis Configuration](https://laravel.com/docs/12.x/redis)
- [Cache Tags](https://laravel.com/docs/12.x/cache#cache-tags)

---

## 🎯 Success Criteria

### Phase 1+2 Completion:
- ✅ All 8 slow pages < 1s on cached requests
- ✅ Cache hit ratio > 80%
- ✅ Redis memory usage < 500MB
- ✅ No cache inconsistency issues
- ✅ Cache invalidation working correctly

### Phase 3 Completion:
- ✅ All pages < 3s even on cache miss
- ✅ Query count < 30 per page
- ✅ No N+1 queries in debugbar
- ✅ Database indexes improving query speed by 20%+

---

**Version**: 2.0
**Status**: 🔴 Ready for Implementation
**Next Step**: Implement Phase 1 - Master Data Redis Caching
**Owner**: Development Team
**Review Date**: After Phase 1+2 completion
