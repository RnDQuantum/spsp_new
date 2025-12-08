# Performance Optimization V3 - SPSP Ranking System

**Status**: Final Strategy (After Root Cause Analysis)
**Date**: 2025-12-08
**Previous Versions**: V1 (Redis - FAILED), V2 (Pre-compute - INSUFFICIENT)

## Executive Summary

**Current Problem**: RekapRankingAssessment page loads in **30 seconds** with 5,131 participants.

**Root Cause Identified**: **Cartesian Product Computation Bottleneck**
- Loading 338,917 Eloquent models (215K SubAspectAssessment + 123K AspectAssessment)
- PHP loop: 5,131 participants × 5 aspects × 10 sub-aspects = ~256,550 iterations
- Eloquent hydration overhead consuming memory and CPU

**Solution Strategy**: Hybrid approach combining SQL Aggregation + Alpine.js
- **Phase 2A (SQL Aggregation)**: Solve initial load 30s → **2-5s** (85-93% improvement)
- **Phase 2B (Alpine.js)**: Solve parameter changes 30s → **0.1s** (instant, 99.7% improvement)

---

## Why This Solution? (The "User Flow" Logic)

To understand why **SQL Aggregation** + **Alpine.js** is the best practice, let's look at the two main user scenarios:

### Case 1: Initial Load (The "Heavy Lift")
**Scenario**: User opens the "Rekap Ranking" page.
*   ❌ **Old Way**: PHP loads 340,000 rows, hydrates models, loops 250,000 times. **Result: 30s load.**
*   ✅ **V3 Way (SQL Aggregation)**: We send a smart query to MySQL: "Calculate (Rating * Weight) for everyone, sum it up, and just give me the top 10 results." Database engines (C++) are 100x faster at math than PHP loops. **Result: 2-3s load.**

### Case 2: Interactive Analysis (The "Exploration")
**Scenario**: User slides Tolerance from 0% to 5% to see who passes.
*   ❌ **Old Way**: Browser refreshes, server repeats the heavy calculation above. **Result: Another 30s wait.**
*   ✅ **V3 Way (Alpine.js)**: 
    1. Server sends lightweight JSON payload to browser on load.
    2. When User slides to 5%, **Alpine.js** recalculates the "MS/TMS" status locally in the browser's memory.
    3. No server request at all. **Result: 0.1s (Instant).**

**The Best Practice Philosophy**:
1.  **"Move computation to the data"** (SQL) for the heavy initial crunching.
2.  **"Reactive UI for Parameter Tuning"** (JS) for the instant feedback loop.

---

## Application Context & Business Logic (Crucial)

### What is SPSP?
**SPSP (Sistem Pemetaan & Statistik Psikologi)** is a SaaS analytics dashboard for psychological assessment results. Unlike a read-heavy CRUD application (like a news site), SPSP is an **analytics tool** where users actively experiment with data parameters to find insights.

### 100% Dynamic Nature
Users frequently change parameters, and each change requires a fresh calculation of rankings.
- **Tolerance**: 0% vs 5% vs 10%
- **Weights**: Potensi (60%) vs Kompetensi (40%)
- **Standards**: "Quantum Default" vs "Custom Standard A" vs "Custom Standard B"

### The 3-Layer Priority System
This is the core business logic that complicates caching and SQL optimization. Every time the system needs a value (e.g., "Weight for Aspect A"), it must check 3 layers in order:

```
Layer 1: Session Adjustment (Temporary)
    Examples: User adjusts weight via slider, changes tolerance
    Storage: PHP Session
    Scope: Unique per user, lost on logout
    ↓ if not found

Layer 2: Custom Standard (Persistent)
    Examples: "Standar Kejaksaan v1", "Standar BNN Strict"
    Storage: Database (custom_standards table)
    Scope: Per institution
    ↓ if not found

Layer 3: Quantum Default (Master)
    Examples: Original template values
    Storage: Database (aspects table)
    Scope: Global
```

**Implication for V3**:
We cannot *just* use a static SQL query. We must first resolve these 3 layers in PHP to get the final "Effective Weights" for the current user, and *then* inject those weights into the SQL query or Alpine.js logic.

### Data Scale (The Challenge)
- ** Participants**: 5,000 - 35,000 per event
- ** Aspects**: 5+ categories
- ** Sub-Aspects**: 15-20 discrete items
- ** Assessments**: ~340,000 individual data points per page load

---

## Why Previous Approaches Failed

### V1: Redis Caching (FAILED - Wrong Approach)
```
❌ Why it failed:
- Cached RESULT data (final rankings)
- Stale data problem for analytics application
- Users need real-time exploration with different parameters
- Not suitable for analytics use case
```

### V2: Pre-compute Standards Cache (INSUFFICIENT)
```
⚠️ Why insufficient:
- Only optimized DynamicStandardService calls (minor bottleneck)
- Still loading 338K Eloquent models (major bottleneck)
- Still looping 256K times in PHP
- Improvement: 0% (no visible impact)
```

**Conclusion**: Pre-compute cache is good practice but doesn't address the real bottleneck!

---

## The Real Bottleneck: Cartesian Product Computation

### Current Code Flow (30 seconds)

```php
// RankingService.php - Current implementation
public function getRankings(...): Collection
{
    // 1. PRE-COMPUTE: Good, but not enough (5ms saved)
    $standardsCache = $this->precomputeStandards($templateId, $activeAspectIds);

    // 2. LOAD MODELS: THIS IS THE KILLER! (25 seconds)
    $assessments = AspectAssessment::query()
        ->with(['aspect.subAspects', 'subAspectAssessments.subAspect'])
        ->get();
    // Loads 338,917 Eloquent models!

    // 3. PHP LOOP: THIS IS ALSO A KILLER! (5 seconds)
    foreach ($assessments as $assessment) {
        foreach ($aspect->subAspects as $subAspect) {
            // 256,550 iterations!
        }
    }

    return $rankings; // After 30 seconds...
}
```

### Performance Breakdown

```
Total Time: 30 seconds

Database Query Time: 6.61s (22%)
├─ 60 SQL statements
├─ 45 duplicate queries
└─ Not optimized with aggregation

Eloquent Hydration: ~18s (60%)
├─ Loading 123,144 AspectAssessment models
├─ Loading 215,502 SubAspectAssessment models
├─ Loading relationships (aspects, sub-aspects)
└─ Converting DB rows → PHP objects

PHP Loop Computation: ~5s (17%)
├─ 256,550 iterations
├─ Calculating ratings
├─ Calculating scores
└─ Sorting results

DynamicStandardService Calls: ~0.4s (1%)
└─ Already optimized by pre-compute cache
```

**Key Insight**:
- 82% of time spent on Eloquent hydration + PHP loops
- Pre-compute cache only optimized the 1% bottleneck
- Need to attack the 82% bottleneck!

---

## Solution Architecture: Hybrid Approach

### Phase 2A: SQL Aggregation (Server-side)
**Target**: Initial load 30s → **2-5s** (85-93% improvement)

**Strategy**: Replace PHP loops + Eloquent models with SQL aggregation

```sql
-- Instead of loading 338K models, aggregate in MySQL directly
SELECT
    p.id as participant_id,
    p.name as participant_name,
    -- Aggregate scores using SQL (fast!)
    SUM(
        CASE
            WHEN sa.sub_aspect_id IS NOT NULL THEN
                -- Calculate from sub-aspects
                (SELECT AVG(saa.individual_rating)
                 FROM sub_aspect_assessments saa
                 WHERE saa.aspect_assessment_id = aa.id) * ?  -- weight from session
            ELSE
                -- Direct aspect rating
                aa.individual_rating * ?  -- weight from session
        END
    ) as total_score
FROM participants p
JOIN aspect_assessments aa ON aa.participant_id = p.id
LEFT JOIN sub_aspect_assessments saa ON saa.aspect_assessment_id = aa.id
WHERE aa.event_id = ?
  AND aa.position_formation_id = ?
GROUP BY p.id, p.name
ORDER BY total_score DESC;
```

**Benefits**:
- ✅ No Eloquent hydration (0 models loaded)
- ✅ MySQL engine does computation (optimized C++ code)
- ✅ Single query instead of 60 queries
- ✅ Returns only 5,131 rows (not 338K)
- ✅ Still respects 3-layer priority (Session → Custom → Quantum)

### Phase 2B: Alpine.js (Client-side)
**Target**: Parameter changes 30s → **0.1s** (99.7% improvement)

**Strategy**: Client-side recalculation for exploration

```javascript
// After initial load, all data in browser memory
function rankingManager(allParticipants, standardScore, aspectWeights) {
    return {
        participants: allParticipants, // 5,131 participants
        tolerance: 0,

        // ✅ INSTANT: Recalculate when tolerance changes
        recalculateWithTolerance() {
            const adjusted = this.standardScore * (1 - this.tolerance / 100);

            this.participants.forEach(p => {
                p.conclusion = p.score >= adjusted ? 'MS' : 'TMS';
            });

            // Re-rank
            this.participants.sort((a, b) => b.score - a.score);
        },

        // ✅ INSTANT: Recalculate when weight changes
        adjustWeight(aspectCode, newWeight) {
            this.aspectWeights[aspectCode] = newWeight;

            this.participants.forEach(p => {
                p.score = this.recalculateScore(p);
            });

            this.recalculateWithTolerance();
        }
    }
}
```

**Benefits**:
- ✅ No server roundtrip (instant response)
- ✅ JavaScript computation faster than PHP for simple math
- ✅ Perfect for analytics exploration
- ✅ Session still saved for persistence

---

## Implementation Plan

### Step 1: Create New Optimized Method (SQL Aggregation)

```php
// RankingService.php - NEW METHOD
public function getRankingsOptimized(
    int $eventId,
    int $positionFormationId,
    int $templateId,
    string $categoryCode
): array {
    // 1. Pre-compute standards (respects session!)
    $standardsCache = $this->precomputeStandards($templateId, $activeAspectIds);

    // 2. Build SQL aggregation query
    $rankings = $this->calculateRankingsWithSQL(
        $eventId,
        $positionFormationId,
        $standardsCache
    );

    // 3. Calculate adjusted standards (for tolerance calculation)
    $adjustedStandards = $this->calculateAdjustedStandardsWithCache($standardsCache);

    return [
        'participants' => $rankings,
        'standardScore' => $adjustedStandards['standard_score'],
        'aspectWeights' => $standardsCache,
    ];
}

private function calculateRankingsWithSQL(
    int $eventId,
    int $positionFormationId,
    array $standardsCache
): Collection {
    // Build dynamic SQL based on aspects
    $selectClauses = [];
    $bindings = [];

    foreach ($standardsCache as $aspectCode => $aspectData) {
        if (!$aspectData['active']) continue;

        $weight = $aspectData['weight'];
        $aspectId = $aspectData['id'];

        if (!empty($aspectData['sub_aspects'])) {
            // Aspect with sub-aspects: calculate from sub_aspect_assessments
            $selectClauses[] = "
                (SELECT AVG(saa.individual_rating) * ?
                 FROM sub_aspect_assessments saa
                 WHERE saa.aspect_assessment_id = aa_$aspectCode.id)
            ";
            $bindings[] = $weight;
        } else {
            // Direct aspect: use aspect_assessments.individual_rating
            $selectClauses[] = "(aa_$aspectCode.individual_rating * ?)";
            $bindings[] = $weight;
        }
    }

    $scoreCalculation = implode(' + ', $selectClauses);

    // Build main query
    $sql = "
        SELECT
            p.id as participant_id,
            p.name as participant_name,
            ($scoreCalculation) as total_score
        FROM participants p
    ";

    // Join for each aspect
    foreach ($standardsCache as $aspectCode => $aspectData) {
        if (!$aspectData['active']) continue;

        $aspectId = $aspectData['id'];
        $sql .= "
            LEFT JOIN aspect_assessments aa_$aspectCode
                ON aa_$aspectCode.participant_id = p.id
                AND aa_$aspectCode.aspect_id = ?
                AND aa_$aspectCode.event_id = ?
                AND aa_$aspectCode.position_formation_id = ?
        ";
        $bindings[] = $aspectId;
        $bindings[] = $eventId;
        $bindings[] = $positionFormationId;
    }

    $sql .= "
        WHERE p.event_id = ?
          AND p.position_formation_id = ?
        GROUP BY p.id, p.name
        ORDER BY total_score DESC
    ";

    $bindings[] = $eventId;
    $bindings[] = $positionFormationId;

    return collect(DB::select($sql, $bindings));
}
```

### Step 2: Update Livewire Component

```php
// RekapRankingAssessment.php
class RekapRankingAssessment extends Component
{
    public array $rankingData = [];
    public int $tolerancePercentage = 0;

    public function mount()
    {
        // Use new optimized method
        $this->rankingData = $this->rankingService->getRankingsOptimized(
            $this->eventId,
            $this->selectedPosition,
            $this->templateId,
            'potensi'
        );
    }

    // Remove updatedTolerancePercentage() - Alpine.js will handle it!

    public function render()
    {
        return view('livewire.pages.general-report.ranking.rekap-ranking-assessment');
    }
}
```

### Step 3: Update Blade View with Alpine.js

```blade
<div x-data="rankingManager(@js($rankingData))">
    <!-- Tolerance Slider -->
    <div class="mb-4">
        <label>Tolerance: <span x-text="tolerance + '%'"></span></label>
        <input type="range"
               x-model.number="tolerance"
               @input="recalculateRankings()"
               min="0" max="20" step="1">
    </div>

    <!-- Rankings Table -->
    <table>
        <thead>
            <tr>
                <th>Rank</th>
                <th>Name</th>
                <th>Score</th>
                <th>Conclusion</th>
            </tr>
        </thead>
        <tbody>
            <template x-for="(participant, index) in displayedParticipants" :key="participant.participant_id">
                <tr>
                    <td x-text="index + 1"></td>
                    <td x-text="participant.participant_name"></td>
                    <td x-text="participant.total_score.toFixed(2)"></td>
                    <td x-text="participant.conclusion"></td>
                </tr>
            </template>
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="mt-4">
        <button @click="previousPage()" :disabled="currentPage === 1">Previous</button>
        <span x-text="`Page ${currentPage} of ${totalPages}`"></span>
        <button @click="nextPage()" :disabled="currentPage === totalPages">Next</button>
    </div>
</div>

<script>
function rankingManager(data) {
    return {
        allParticipants: data.participants,
        standardScore: data.standardScore,
        aspectWeights: data.aspectWeights,
        tolerance: 0,
        currentPage: 1,
        perPage: 10,

        get displayedParticipants() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.allParticipants.slice(start, start + this.perPage);
        },

        get totalPages() {
            return Math.ceil(this.allParticipants.length / this.perPage);
        },

        recalculateRankings() {
            const adjustedStandard = this.standardScore * (1 - this.tolerance / 100);

            this.allParticipants.forEach(p => {
                p.conclusion = p.total_score >= adjustedStandard ? 'MS' : 'TMS';
            });
        },

        nextPage() {
            if (this.currentPage < this.totalPages) this.currentPage++;
        },

        previousPage() {
            if (this.currentPage > 1) this.currentPage--;
        }
    }
}
</script>
```

---

## Expected Results

### Before Optimization

| Operation | Time | Method |
|-----------|------|--------|
| Initial load | 30s | Eloquent + PHP loops |
| Tolerance change | 30s | Server-side recalculation |
| Weight change | 30s | Server-side recalculation |
| Pagination | 0.1s | Client-side (already fast) |
| Refresh | 30s | Eloquent + PHP loops |

**User Experience**: Frustrating, unusable for exploration

### After Phase 2A (SQL Aggregation)

| Operation | Time | Improvement | Method |
|-----------|------|------------|--------|
| Initial load | **2-5s** | 85-93% faster | SQL aggregation |
| Tolerance change | 30s | No change yet | Server-side |
| Weight change | 30s | No change yet | Server-side |
| Pagination | 0.1s | Same | Client-side |
| Refresh | **2-5s** | 85-93% faster | SQL aggregation |

**User Experience**: Better, but exploration still slow

### After Phase 2A + 2B (SQL + Alpine.js)

| Operation | Time | Improvement | Method |
|-----------|------|------------|--------|
| Initial load | **2-5s** | 85-93% faster | SQL aggregation |
| Tolerance change | **0.1s** | 99.7% faster | Alpine.js |
| Weight change | **0.2s** | 99.3% faster | Alpine.js |
| Pagination | **0.01s** | Instant | Alpine.js |
| Refresh | **2-5s** | 85-93% faster | SQL aggregation |

**User Experience**: Excellent! Perfect for analytics exploration

---

## Trade-offs and Limitations

### Advantages
✅ Massive performance improvement (30s → 2-5s initial, 0.1s exploration)
✅ Perfect for analytics use case
✅ Respects 3-layer priority (Session → Custom → Quantum)
✅ No stale cache issues
✅ Instant exploration for users

### Limitations
⚠️ Initial load still 2-5s (acceptable for analytics)
⚠️ SQL query complexity increases with aspects
⚠️ All participant data loaded to browser (~2-5MB)
⚠️ Not suitable for >50K participants (consider pagination threshold)

### When to Use This Approach
- ✅ Analytics applications needing exploration
- ✅ Dataset size: 1K - 50K participants
- ✅ Users need to experiment with parameters
- ✅ Real-time updates not critical (2-5s acceptable)

### When NOT to Use
- ❌ CRUD applications (use traditional pagination)
- ❌ >100K participants (consider different architecture)
- ❌ Real-time collaboration (multiple users editing same data)
- ❌ Sub-second initial load requirement

---

## Testing Strategy

### Phase 2A Testing (SQL Aggregation)

1. **Correctness Test**
   ```bash
   php artisan test --filter=RankingServiceOptimizedTest
   ```
   - Verify SQL results match old Eloquent results
   - Test with session adjustments
   - Test with custom standards
   - Test with quantum defaults

2. **Performance Test**
   - Measure initial load time with 5K, 10K, 20K, 35K participants
   - Target: <5s for 35K participants
   - Use Laravel Debugbar to verify query count

### Phase 2B Testing (Alpine.js)

1. **Browser Testing**
   - Test tolerance slider (should be instant)
   - Test weight adjustment modal
   - Test pagination
   - Verify no server requests for parameter changes

2. **Memory Testing**
   - Monitor browser memory with 35K participants
   - Should be <10MB data size
   - Test in Chrome DevTools

---

## Rollback Plan

If optimization causes issues:

1. **Immediate Rollback**
   ```php
   // RekapRankingAssessment.php - switch back
   $rankings = $this->rankingService->getRankings(...); // Old method
   ```

2. **Keep Pre-compute Cache**
   - Phase 1 (pre-compute) is safe and has no side effects
   - Can stay even if Phase 2 is rolled back

3. **Gradual Rollout**
   - Test with small datasets first (1K participants)
   - Then 5K → 10K → 20K → 35K
   - Monitor error logs

---

## Conclusion

**Root Cause**: Cartesian Product Computation Bottleneck (338K models + 256K loops)

**Solution**: Hybrid SQL Aggregation + Alpine.js
- SQL handles heavy lifting (initial load)
- Alpine.js handles exploration (parameter changes)

**Expected Impact**:
- Initial load: 30s → 2-5s (85-93% improvement)
- Exploration: 30s → 0.1s (99.7% improvement)
- User experience: Unusable → Excellent

**Next Steps**:
1. Implement Phase 2A (SQL Aggregation)
2. Test with real data (35K participants)
3. Implement Phase 2B (Alpine.js)
4. Monitor production performance

---

**Document Version**: V3
**Author**: Claude + User Collaboration
**Last Updated**: 2025-12-08
**Status**: Ready for Implementation
