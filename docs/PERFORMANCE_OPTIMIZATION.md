# Performance Optimization Guide - SPSP Assessment System

> **Version**: 1.0
> **Created**: 2025-12-03
> **Status**: 🚨 **CRITICAL** - Performance issues identified with 35K+ participants
> **Purpose**: Strategy dan implementasi untuk mengoptimalkan performa aplikasi dengan data besar

---

## 📊 Current Performance Issues

### Test Environment
- **Participants**: 35,000 (20,000 + 15,000 dari 2 institution codes)
- **Assessment Records**: ~350,000 (35K participants × ~10 aspects)
- **Seeder**: `database/seeders/DynamicAssessmentSeeder.php`

### Measured Problems

| Operation | Current Performance | Target | Severity |
|-----------|-------------------|--------|----------|
| **Change Position** | ❌ **APPLICATION CRASH** | < 1s | 🔴 CRITICAL |
| **Change Tolerance** | ⏱️ 15+ seconds | < 2s | 🔴 CRITICAL |
| **Ranking Pagination** | ⏱️ 20+ seconds | < 2s | 🔴 CRITICAL |
| **Dashboard Load** | ⏱️ 10+ seconds | < 2s | 🟡 HIGH |

---

## 🔍 Root Cause Analysis

### 1. ParticipantSelector - CRASH (CRITICAL) 🔴

**File**: `app/Livewire/Components/ParticipantSelector.php:117-121`

**Problem**:
```php
// Loads ALL 35K participants into memory at once
$participants = Participant::query()
    ->where('event_id', $event->id)
    ->where('position_formation_id', $positionFormationId)
    ->orderBy('name')
    ->get(['id', 'test_number', 'name']); // 35,000 records loaded!

$this->availableParticipants = $participants->map(fn ($p) => [
    'id' => $p->id,
    'test_number' => $p->test_number,
    'name' => $p->name,
])->all(); // Array with 35K elements in Livewire component property
```

**Why it crashes**:
- Livewire serializes all component properties to JavaScript
- 35K participants = ~3.5MB of JSON data
- Browser runs out of memory rendering dropdown with 35K options
- DOM manipulation with 35K `<option>` elements freezes browser

**Impact**: Application crashes when changing position

---

### 2. RankingService - SLOW (CRITICAL) 🔴

**File**: `app/Services/RankingService.php:72-88`

**Problem**:
```php
// Loops through 350K assessment records in PHP
foreach ($assessments as $assessment) {
    $participantId = $assessment->participant_id;
    $aspect = $assessment->aspect;

    // Recalculate individual rating for EACH record
    $adjustedWeight = $standardService->getAspectWeight($templateId, $aspect->code);
    // ... complex calculations in PHP loop ...
}
```

**Why it's slow**:
- Fetches 350K `Assessment` records with eager loading
- Loops through ALL records in PHP (not database)
- Performs calculations per-row instead of database aggregation
- No result caching - recalculates on EVERY request

**Impact**:
- 20+ seconds for ranking pagination
- 15+ seconds for tolerance changes
- Blocks entire request (no async loading)

---

### 3. Cache Strategy - INEFFICIENT 🟡

**Current Setup**:

| Cache Type | Current Driver | Optimal? | Usage |
|------------|---------------|----------|-------|
| **Application Cache** | `database` | ❌ NO | Cache facade (`Cache::get()`) |
| **Aspect Cache** | In-memory (static array) | ✅ YES | AspectCacheService (metadata only) |

**File**: `config/cache.php` & `.env`

**Problem**:
```env
CACHE_STORE=database
```

**Why it's inefficient**:
- `database` cache driver still queries MySQL
- No performance benefit over direct queries
- Cannot handle 35K+ participant data efficiently
- No cross-request persistence for expensive calculations

**Impact**: Cannot cache ranking results effectively

---

## 🛠️ Optimization Strategies

### Strategy 1: Fix ParticipantSelector (URGENT) 🚨

**Goal**: Prevent crash when changing position

**Approach**: Search-based selector instead of loading all participants

**Implementation**:

#### 1.1 Update Component Logic

**File**: `app/Livewire/Components/ParticipantSelector.php`

**Changes**:
```php
<?php

namespace App\Livewire\Components;

use App\Models\AssessmentEvent;
use App\Models\Participant;
use Livewire\Component;

class ParticipantSelector extends Component
{
    public ?int $participantId = null;

    // ADD: Search input property
    public string $search = '';

    /** @var array<int, array{id: int, test_number: string, name: string}> */
    public array $availableParticipants = [];

    // ADD: Limit results to prevent memory issues
    private const MAX_RESULTS = 50;

    public bool $showLabel = true;

    protected $listeners = [
        'event-selected' => 'handleEventSelected',
        'position-selected' => 'handlePositionSelected',
    ];

    public function mount(?bool $showLabel = null): void
    {
        if ($showLabel !== null) {
            $this->showLabel = $showLabel;
        }

        // Don't auto-load participants on mount
        $this->availableParticipants = [];
    }

    // ADD: Watch for search input changes
    public function updatedSearch(): void
    {
        $this->loadAvailableParticipants();
    }

    public function updatedParticipantId(?int $value): void
    {
        if ($value && !$this->isValidParticipantId($value)) {
            $this->participantId = null;
            session()->forget('filter.participant_id');
            $this->dispatch('participant-selected', participantId: null);
            return;
        }

        if ($value) {
            session(['filter.participant_id' => $value]);
        } else {
            session()->forget('filter.participant_id');
        }

        $this->dispatch('participant-selected', participantId: $value);
    }

    public function handleEventSelected(?string $eventCode): void
    {
        $this->participantId = null;
        $this->search = '';
        session()->forget('filter.participant_id');

        $this->availableParticipants = [];

        $this->dispatch('participant-selected', participantId: $this->participantId);
    }

    public function handlePositionSelected(?int $positionFormationId): void
    {
        $this->participantId = null;
        $this->search = '';
        session()->forget('filter.participant_id');

        $this->availableParticipants = [];

        $this->dispatch('participant-selected', participantId: $this->participantId);
    }

    // UPDATED: Load participants with search filter and limit
    private function loadAvailableParticipants(): void
    {
        $this->availableParticipants = [];

        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if (!$eventCode || !$positionFormationId) {
            return;
        }

        // Require minimum 2 characters for search
        if (strlen($this->search) < 2) {
            return;
        }

        $event = AssessmentEvent::where('code', $eventCode)->first();

        if (!$event) {
            return;
        }

        // OPTIMIZED: Search-based query with LIMIT
        $participants = Participant::query()
            ->where('event_id', $event->id)
            ->where('position_formation_id', $positionFormationId)
            ->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('test_number', 'like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->limit(self::MAX_RESULTS)
            ->get(['id', 'test_number', 'name']);

        $this->availableParticipants = $participants->map(fn ($p) => [
            'id' => $p->id,
            'test_number' => $p->test_number,
            'name' => $p->name,
        ])->all();

        // Load participant from session if in results
        $sessionParticipantId = session('filter.participant_id');

        if ($sessionParticipantId && $this->isValidParticipantId($sessionParticipantId)) {
            $this->participantId = $sessionParticipantId;
        } else {
            $this->participantId = null;
        }
    }

    private function isValidParticipantId(int $id): bool
    {
        return collect($this->availableParticipants)->contains('id', $id);
    }

    public function resetParticipant(): void
    {
        $this->participantId = null;
        $this->search = '';
        session()->forget('filter.participant_id');

        $this->dispatch('participant-reset');
    }

    public function render()
    {
        return view('livewire.components.participant-selector');
    }
}
```

#### 1.2 Update Blade View

**File**: `resources/views/livewire/components/participant-selector.blade.php`

**Changes**:
```blade
<div>
    @if($showLabel)
        <flux:label>Peserta</flux:label>
    @endif

    <!-- ADD: Search input with debounce -->
    <flux:input
        wire:model.live.debounce.300ms="search"
        type="text"
        placeholder="Cari peserta (min. 2 karakter)..."
        class="mb-2"
    />

    <!-- UPDATED: Show search prompt or results -->
    @if(strlen($search) < 2)
        <flux:input.group>
            <flux:select disabled>
                <option>Ketik minimal 2 karakter untuk mencari...</option>
            </flux:select>
        </flux:input.group>
    @elseif(count($availableParticipants) === 0)
        <flux:input.group>
            <flux:select disabled>
                <option>Tidak ada peserta ditemukan</option>
            </flux:select>
        </flux:input.group>
    @else
        <flux:input.group>
            <flux:select wire:model.live="participantId">
                <option value="">-- Pilih Peserta --</option>
                @foreach($availableParticipants as $participant)
                    <option value="{{ $participant['id'] }}">
                        {{ $participant['test_number'] }} - {{ $participant['name'] }}
                    </option>
                @endforeach

                @if(count($availableParticipants) >= 50)
                    <option disabled>-- Maksimal 50 hasil ditampilkan, perkecil pencarian --</option>
                @endif
            </flux:select>
        </flux:input.group>
    @endif

    <!-- Loading indicator -->
    <div wire:loading wire:target="search" class="text-sm text-gray-500 mt-1">
        Mencari...
    </div>
</div>
```

**Benefits**:
- ✅ No crash - maximum 50 participants loaded
- ✅ Fast search with database indexes
- ✅ Better UX with search-as-you-type
- ✅ Works with 35K+ participants

---

### Strategy 2: Optimize RankingService with Caching 🚀

**Goal**: Reduce ranking calculation from 20s to < 2s

**Approach**: Database aggregation + Redis caching

#### 2.1 Install Redis

**Step 1: Install Redis Server**

For Laragon (Windows):
1. Download Redis for Windows: https://github.com/tporadowski/redis/releases
2. Extract to `C:\laragon\bin\redis\`
3. Start Redis: `redis-server.exe`

Or use Laragon Redis module (if available).

**Step 2: Install PHP Redis Client**

```bash
composer require predis/predis
```

**Step 3: Configure Laravel**

**File**: `.env`

```env
CACHE_STORE=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
REDIS_DB=0
```

**Step 4: Test Redis**

```bash
php artisan tinker
```

```php
Cache::put('test', 'Hello Redis!', 60);
Cache::get('test'); // Should return "Hello Redis!"
```

#### 2.2 Add Cache to RankingService

**File**: `app/Services/RankingService.php`

**Strategy**:
- Cache ranking results per event + position + tolerance combination
- Use cache tags for easy invalidation
- Set reasonable TTL (10-15 minutes)

**Updated Method** (example for one method):

```php
use Illuminate\Support\Facades\Cache;

public function getRankedParticipants(
    string $eventCode,
    int $positionFormationId,
    int $tolerancePercentage = 0
): Collection {
    // Generate unique cache key
    $cacheKey = "ranking:{$eventCode}:{$positionFormationId}:{$tolerancePercentage}";

    // Try to get from cache first
    return Cache::remember($cacheKey, now()->addMinutes(10), function () use (
        $eventCode,
        $positionFormationId,
        $tolerancePercentage
    ) {
        // Original expensive calculation here
        // ... existing code ...

        // Return calculated results
        return $rankedParticipants;
    });
}
```

**Cache Invalidation**:

When data changes (e.g., dynamic standard adjusted), clear related cache:

```php
// In DynamicStandardService or wherever standards are modified
public function saveBulkSelection(int $templateId, array $data): void
{
    // ... save logic ...

    // Clear ranking cache for all events using this template
    $this->clearRankingCache($templateId);
}

private function clearRankingCache(int $templateId): void
{
    // Option 1: Clear all ranking cache
    Cache::flush();

    // Option 2: Clear specific patterns (requires Redis SCAN)
    // More targeted but complex implementation
}
```

**Benefits**:
- ✅ First request: ~15-20s (same as before)
- ✅ Cached requests: < 0.5s (40x faster!)
- ✅ Cache shared across all users
- ✅ Automatic expiration after 10 minutes

---

### Strategy 3: Database Aggregation (Advanced) 🎯

**Goal**: Reduce first-time calculation from 20s to < 5s

**Approach**: Move PHP loops to database aggregation

**Current Problem**:
```php
// PHP loop through 350K records - SLOW
foreach ($assessments as $assessment) {
    // Calculate per row
}
```

**Optimized Solution**:
```php
// Database aggregation - FAST
$rankings = DB::table('assessments')
    ->join('participants', 'assessments.participant_id', '=', 'participants.id')
    ->join('aspects', 'assessments.aspect_id', '=', 'aspects.id')
    ->where('participants.event_id', $eventId)
    ->where('participants.position_formation_id', $positionFormationId)
    ->select(
        'participants.id as participant_id',
        'participants.name',
        'participants.test_number',
        DB::raw('SUM(assessments.rating * aspects.weight / 100) as final_score')
    )
    ->groupBy('participants.id', 'participants.name', 'participants.test_number')
    ->orderByDesc('final_score')
    ->get();
```

**Note**: This requires schema changes to support dynamic weights. Consider this for Phase 2 optimization.

---

## 📋 Implementation Checklist

### Phase 1: Critical Fixes (Do First) 🚨

- [ ] **1.1** Update ParticipantSelector.php with search-based logic
- [ ] **1.2** Update participant-selector.blade.php with search input
- [ ] **1.3** Test with 35K participants - verify no crash
- [ ] **1.4** Add database index on `participants.name` and `participants.test_number`

**SQL for indexes**:
```sql
CREATE INDEX idx_participants_name ON participants(name);
CREATE INDEX idx_participants_test_number ON participants(test_number);
```

### Phase 2: Redis Setup (Performance Boost) 🚀

- [ ] **2.1** Install Redis server (Windows/Linux)
- [ ] **2.2** Install predis/predis composer package
- [ ] **2.3** Update .env with Redis configuration
- [ ] **2.4** Test Redis connection via tinker
- [ ] **2.5** Update RankingService with Cache::remember()
- [ ] **2.6** Add cache invalidation logic
- [ ] **2.7** Test tolerance change - verify < 2s response

### Phase 3: Database Optimization (Advanced) 🎯

- [ ] **3.1** Analyze slow queries with `EXPLAIN`
- [ ] **3.2** Add missing database indexes
- [ ] **3.3** Consider materialized views for rankings
- [ ] **3.4** Evaluate database aggregation approach
- [ ] **3.5** Load testing with 50K+ participants

---

## 🧪 Testing Strategy

### Load Testing

**Test Case 1: ParticipantSelector**
```php
// Test with 35,000 participants
$event = AssessmentEvent::factory()->create();
$position = PositionFormation::factory()->create();

Participant::factory()
    ->count(35000)
    ->create([
        'event_id' => $event->id,
        'position_formation_id' => $position->id,
    ]);

// Test component doesn't crash
Livewire::test(ParticipantSelector::class)
    ->set('search', 'Test')
    ->assertSet('availableParticipants', fn($val) => count($val) <= 50)
    ->assertStatus(200);
```

**Test Case 2: Ranking Performance**
```bash
# Measure response time
time php artisan tinker --execute="
    app(RankingService::class)->getRankedParticipants('EVT-001', 1, 0);
"

# First run: Should complete without timeout
# Cached run: Should be < 1 second
```

### Performance Benchmarks

| Metric | Before Optimization | After Phase 1 | After Phase 2 | Target |
|--------|-------------------|---------------|---------------|--------|
| Position Change | ❌ CRASH | ✅ < 2s | ✅ < 1s | < 1s |
| Ranking Load (First) | 20s | 20s | 18s | < 5s |
| Ranking Load (Cached) | N/A | N/A | < 0.5s | < 1s |
| Tolerance Change | 15s | 15s | < 1s | < 2s |

---

## 🔧 Maintenance & Monitoring

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

### Performance Monitoring

Add logging to measure improvements:

```php
// In RankingService
use Illuminate\Support\Facades\Log;

public function getRankedParticipants(...): Collection
{
    $startTime = microtime(true);

    $result = Cache::remember($cacheKey, ..., function () {
        // ... calculation ...
    });

    $duration = round((microtime(true) - $startTime) * 1000, 2);

    Log::info("Ranking calculation completed", [
        'event' => $eventCode,
        'position' => $positionFormationId,
        'tolerance' => $tolerancePercentage,
        'duration_ms' => $duration,
        'cached' => Cache::has($cacheKey),
        'participants_count' => $result->count(),
    ]);

    return $result;
}
```

---

## 📚 Additional Resources

### AspectCacheService - Keep Using It! ✅

**File**: `app/Services/Cache/AspectCacheService.php`

Your existing AspectCacheService is **already optimal** for its use case:
- Caches aspect/sub-aspect metadata (~50-100 records)
- Request-scoped in-memory cache
- No need to refactor or change

**Do NOT confuse with Redis caching**:

| Feature | AspectCacheService | Redis Cache |
|---------|-------------------|-------------|
| **Scope** | Single request | Cross-request, cross-user |
| **Data Size** | Small (< 1MB) | Large (GB+) |
| **Use Case** | Metadata caching | Result caching |
| **Storage** | PHP static array | Redis server |
| **Persistence** | Request lifetime | Configurable TTL |

**Keep using both**:
- AspectCacheService for aspect metadata ✅
- Redis cache for ranking results ✅

---

## 🎯 Expected Results

### After Full Implementation

| Scenario | Original | Optimized | Improvement |
|----------|----------|-----------|-------------|
| Dashboard load with 35K participants | ❌ CRASH | ✅ 1-2s | ∞ (fixed) |
| First ranking calculation | 20s | 18s | 10% faster |
| Cached ranking load | N/A | < 0.5s | 40x faster |
| Tolerance change | 15s | < 1s | 15x faster |
| Pagination | 20s | < 1s | 20x faster |
| Search participant | N/A | < 0.3s | New feature |

### User Experience Improvements

1. **No More Crashes**: Application stable with 50K+ participants
2. **Fast Interactions**: Most operations < 2 seconds
3. **Better UX**: Search-based participant selection
4. **Scalability**: Can handle 100K+ participants with same approach

---

## 🚨 Important Notes

### Cache Invalidation

⚠️ **Critical**: When dynamic standards change, ranking cache MUST be cleared!

**Locations to add cache clearing**:
1. `DynamicStandardService::saveBulkSelection()`
2. `CustomStandard` save/update/delete operations
3. Any manual aspect weight adjustments

```php
// Add to all places that modify standards
Cache::flush(); // Simple approach - clears ALL cache
// OR
Cache::tags(['rankings'])->flush(); // Requires Redis (better approach)
```

### Redis Memory

Monitor Redis memory usage:

```bash
redis-cli INFO memory
```

If memory grows too large, reduce cache TTL or implement LRU eviction:

```php
// In config/database.php
'redis' => [
    'client' => env('REDIS_CLIENT', 'predis'),
    'options' => [
        'cluster' => env('REDIS_CLUSTER', 'redis'),
    ],
    'default' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port' => env('REDIS_PORT', 6379),
        'database' => env('REDIS_DB', 0),
        'options' => [
            'maxmemory-policy' => 'allkeys-lru', // Auto-evict old keys
        ],
    ],
],
```

---

**Version**: 1.0
**Created**: 2025-12-03
**Status**: Ready for Implementation
**Priority**: 🚨 CRITICAL - Implement Phase 1 immediately
**Next Review**: After Phase 1 implementation
**Maintainer**: Development Team
