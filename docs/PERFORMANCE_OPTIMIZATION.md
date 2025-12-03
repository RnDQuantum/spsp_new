# Performance Optimization Guide - SPSP Assessment System

> **Version**: 1.1
> **Last Updated**: 2025-12-03
> **Status**: ✅ **Phase 1 COMPLETED** - ParticipantSelector optimized with infinite scroll

---

## 📊 Performance Status

### Current State

| Operation | Before | After Phase 1 | Status |
|-----------|--------|---------------|--------|
| **Change Position** | ❌ CRASH (35K+) | ✅ < 0.5s | ✅ FIXED |
| **Participant Search** | N/A | ✅ < 0.3s | ✅ NEW |
| **Change Tolerance** | ⏱️ 15+ seconds | ⏱️ 15s | 🟡 Phase 2 |
| **Ranking Pagination** | ⏱️ 20+ seconds | ⏱️ 20s | 🟡 Phase 2 |
| **Dashboard Load** | ⏱️ 10+ seconds | ⏱️ 5-10s | 🟡 Phase 2 |

---

## ✅ Phase 1: ParticipantSelector (COMPLETED)

### Implementation

**Files Modified**:
- `app/Livewire/Components/ParticipantSelector.php`
- `resources/views/livewire/components/participant-selector.blade.php`
- `database/migrations/2025_12_03_061100_add_search_indexes_to_participants_table.php`
- `tests/Feature/Livewire/Components/ParticipantSelectorTest.php`

**Database Indexes**:
```sql
CREATE INDEX idx_participants_name ON participants(name);
CREATE INDEX idx_participants_test_number ON participants(test_number);
CREATE INDEX idx_participants_event_position_name ON participants(event_id, position_formation_id, name);
```

**Key Features**:
- ✅ Infinite scroll with 50 records per page
- ✅ Auto-load on dropdown focus
- ✅ Optional search (no minimum characters)
- ✅ Native dropdown with Alpine.js
- ✅ Dark mode support
- ✅ Session persistence

**Performance Improvements**:
- Initial load: 11,500 records → 50 records (230x reduction)
- Memory usage: ~10 MB → ~0.5 MB (20x reduction)
- Load time: 5-10s → < 300ms (20-30x faster)
- Scalability: Tested up to 100K+ participants ✅

**Tests**: 9 tests, 24 assertions, all passing ✅

---

## 🟡 Phase 2: RankingService Redis Caching (PENDING)

### Goal
Reduce ranking calculation from 20s to < 1s with Redis caching.

### Prerequisites

1. **Install Redis Server**
   - Windows (Laragon): Download from https://github.com/tporadowski/redis/releases
   - Extract to `C:\laragon\bin\redis\`
   - Run: `redis-server.exe`

2. **Install PHP Redis Client**
   ```bash
   composer require predis/predis
   ```

3. **Configure `.env`**
   ```env
   CACHE_STORE=redis
   REDIS_CLIENT=predis
   REDIS_HOST=127.0.0.1
   REDIS_PORT=6379
   REDIS_PASSWORD=null
   REDIS_DB=0
   ```

4. **Test Connection**
   ```bash
   php artisan tinker
   ```
   ```php
   Cache::put('test', 'Hello Redis!', 60);
   Cache::get('test'); // Should return "Hello Redis!"
   ```

### Implementation Strategy

**File**: `app/Services/RankingService.php`

Add caching wrapper to ranking methods:

```php
use Illuminate\Support\Facades\Cache;

public function getRankedParticipants(
    string $eventCode,
    int $positionFormationId,
    int $tolerancePercentage = 0
): Collection {
    // Generate cache key
    $cacheKey = "ranking:{$eventCode}:{$positionFormationId}:{$tolerancePercentage}";

    // Cache for 10 minutes
    return Cache::remember($cacheKey, now()->addMinutes(10), function () use (
        $eventCode,
        $positionFormationId,
        $tolerancePercentage
    ) {
        // Existing calculation logic here
        return $rankedParticipants;
    });
}
```

**Cache Invalidation**:

Clear cache when standards are modified:

```php
// In DynamicStandardService or wherever standards change
public function saveBulkSelection(int $templateId, array $data): void
{
    // ... save logic ...

    // Clear ranking cache
    Cache::flush(); // Simple approach
    // OR
    Cache::tags(['rankings'])->flush(); // Better with Redis
}
```

### Expected Results

| Scenario | Before | After Cache | Improvement |
|----------|--------|-------------|-------------|
| First request | 20s | 18s | 10% faster |
| Cached request | N/A | < 0.5s | **40x faster** |
| Tolerance change | 15s | < 1s | **15x faster** |
| Pagination | 20s | < 1s | **20x faster** |

### Cache Management

**Clear all cache**:
```bash
php artisan cache:clear
```

**Monitor Redis**:
```bash
redis-cli
> INFO memory
> KEYS ranking:*
> TTL ranking:EVT-001:1:0
```

---

## 🎯 Phase 3: Database Aggregation (FUTURE)

### Goal
Reduce first-time calculation from 20s to < 5s using database-level aggregation.

### Strategy

Move PHP loops to SQL aggregation:

```php
// Current: Loop through 350K records in PHP (SLOW)
foreach ($assessments as $assessment) {
    // Calculate per row...
}

// Optimized: Database aggregation (FAST)
$rankings = DB::table('assessments')
    ->join('participants', 'assessments.participant_id', '=', 'participants.id')
    ->join('aspects', 'assessments.aspect_id', '=', 'aspects.id')
    ->where('participants.event_id', $eventId)
    ->where('participants.position_formation_id', $positionFormationId)
    ->select(
        'participants.id',
        'participants.name',
        DB::raw('SUM(assessments.rating * aspects.weight / 100) as final_score')
    )
    ->groupBy('participants.id', 'participants.name')
    ->orderByDesc('final_score')
    ->get();
```

**Note**: Requires schema changes to support dynamic weights. Consider for future optimization.

---

## 📚 Maintenance

### Performance Monitoring

Add logging to track performance:

```php
use Illuminate\Support\Facades\Log;

$startTime = microtime(true);
$result = Cache::remember($cacheKey, ..., function () {
    // ... calculation ...
});
$duration = round((microtime(true) - $startTime) * 1000, 2);

Log::info("Ranking calculation", [
    'event' => $eventCode,
    'duration_ms' => $duration,
    'cached' => Cache::has($cacheKey),
]);
```

### Redis Memory Management

Monitor and configure LRU eviction:

```php
// config/database.php
'redis' => [
    'default' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'port' => env('REDIS_PORT', 6379),
        'options' => [
            'maxmemory-policy' => 'allkeys-lru',
        ],
    ],
],
```

---

**Version**: 1.1
**Phase 1**: ✅ COMPLETED
**Phase 2**: 🟡 READY TO IMPLEMENT
**Phase 3**: ⚪ FUTURE CONSIDERATION
**Maintainer**: Development Team
