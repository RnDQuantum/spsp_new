# Architecture Decision Records (ADR)

## Overview
Dokumen ini mencatat keputusan arsitektur penting dalam SPSP dan reasoning di balik setiap keputusan.

---

## ADR-001: Why `individual_rating` is Pre-Calculated and Immutable

**Status:** ✅ Accepted
**Date:** 2024-2025 (Original Design)
**Updated:** December 2025 (Performance Optimization)

### Context
SPSP adalah BI system untuk ranking 4,905+ peserta assessment. Setiap peserta dinilai di 13 aspects, beberapa dengan 3-5 sub-aspects (total 17 sub-aspects).

### Decision
`aspect_assessments.individual_rating` di-calculate **SEKALI** saat assessment selesai dan **TIDAK PERNAH BERUBAH**.

### Reasoning

#### 1. Data Integrity
```
Assessment Day (2024):
Peserta "WINDA FUJIATI" mengerjakan tes
├─ Daya Analisa: Rating 4
├─ Kreativitas: Rating 5
├─ Fleksibilitas: Rating 4
└─ → Aspect "Daya Pikir" individual_rating = 4.33 (avg) ✅ STORED

Analysis Phase (2025):
Admin mengubah bobot "Daya Pikir" 5% → 8%
├─ individual_rating tetap 4.33 ✅ (data historis)
└─ individual_score berubah 21.65 → 34.64 (4.33 × 8)
```

**Prinsip:** Individual rating adalah **fakta** (hasil tes), bukan **opinion** (standar institusi).

#### 2. Performance
```
BEFORE (Calculate on-the-fly):
For ranking 4,905 participants:
├─ Query: 49,340 aspect_assessments
├─ Query: 83,878 sub_aspect_assessments ❌
├─ Loop: 49,340 × calculate_rating_from_sub_aspects()
└─ Time: ~10 seconds ❌

AFTER (Pre-calculated):
For ranking 4,905 participants:
├─ Query: 49,340 aspect_assessments ✅
├─ Query: 0 sub_aspect_assessments ✅
├─ Loop: 49,340 × read_rating_from_db()
└─ Time: ~1.5 seconds ✅
```

#### 3. Business Logic Separation
```
IMMUTABLE (Test Results):           MUTABLE (Analysis Config):
├─ individual_rating                ├─ weights
├─ participant name                 ├─ standard_rating
├─ test date                        └─ active/inactive aspects
└─ raw answers
```

### Consequences

**Positive:**
- ✅ Fast ranking (no recalculation needed)
- ✅ Historical accuracy (data tidak berubah seiring waktu)
- ✅ Audit trail (bisa trace back ke tes asli)

**Negative:**
- ⚠️ Tidak bisa "re-score" peserta retroaktively
- ⚠️ Jika ada kesalahan input, harus manual correction

**Mitigation:**
- Input validation ketat saat assessment
- Approval workflow untuk finalisasi rating

---

## ADR-002: 3-Layer Priority System for Standards

**Status:** ✅ Accepted
**Date:** 2024-2025 (Phase 2 & 3)

### Context
User perlu:
1. Gunakan standar umum (Quantum Default)
2. Gunakan standar institusi (Custom Standard)
3. Temporary adjust standar untuk eksplorasi (Session Adjustment)

### Decision
Implementasi 3-layer priority dengan precedence:
```
Session Adjustment > Custom Standard > Quantum Default
```

### Reasoning

#### Use Case Support
```
Scenario 1: New institution (no custom standard)
User → Uses Quantum Default automatically ✅

Scenario 2: Institution with custom standard
User → Pilih Custom Standard "Kejaksaan 2025"
     → All weights & ratings dari database ✅

Scenario 3: What-if analysis
User → Base: Custom Standard "Kejaksaan 2025"
     → Adjust "Kepemimpinan" weight 10% → 15% (session)
     → See impact immediately ✅
     → Reset → Back to Custom Standard ✅
```

#### Implementation in Code
```php
// DynamicStandardService.php
private function getOriginalValue(string $type, int $templateId, string $code)
{
    // LAYER 1: Check session first
    $adjustments = Session::get("standard_adjustment.{$templateId}");
    if (isset($adjustments[$type][$code])) {
        return $adjustments[$type][$code];  // Highest priority
    }

    // LAYER 2: Check custom standard if selected
    $customStandardId = Session::get("selected_standard.{$templateId}");
    if ($customStandardId) {
        $value = $this->getFromCustomStandard($customStandardId, $type, $code);
        if ($value !== null) {
            return $value;  // Medium priority
        }
    }

    // LAYER 3: Fallback to Quantum default
    return $this->getFromDatabase($type, $templateId, $code);  // Lowest priority
}
```

### Consequences

**Positive:**
- ✅ Flexible analysis (user bisa explore berbagai scenario)
- ✅ Non-destructive (session adjustment tidak mengubah database)
- ✅ Shareable (custom standard bisa digunakan multiple users)

**Negative:**
- ⚠️ Complexity (harus maintain 3 sources of truth)
- ⚠️ Cache invalidation (harus detect layer changes)

**Mitigation:**
- Centralized via `DynamicStandardService`
- Config hash untuk automatic cache invalidation

---

## ADR-003: Cache Strategy for BI Workloads

**Status:** ✅ Accepted
**Date:** December 2025 (Performance Optimization Sprint)

### Context
Ranking 4,905 participants takes 1-2 seconds. User sering melakukan:
- Multiple page navigation (Rekap → Psy → Mc → Individual)
- Tolerance adjustments (0% → 10% → 20%)
- Baseline switching

### Decision
Implement **60-second TTL cache** dengan **config-based invalidation**.

```php
$configHash = md5(json_encode([
    'aspect_weights' => $weights,  // Changes when baseline changes
    'session' => session()->getId()  // Isolates per-user
]));

$cacheKey = "rankings:{$categoryCode}:...:{$configHash}";
Cache::remember($cacheKey, 60, fn() => $this->calculateRankings());
```

### Reasoning

#### Why NOT Real-Time?
```
BI Use Case: Exploration, NOT monitoring
├─ User explores "what-if" scenarios
├─ Data is historical (not changing)
└─ Minor delay acceptable for MASSIVE speed gain

Real-time would require:
├─ Cache invalidation on EVERY standard change
├─ No caching benefit (always miss)
└─ 1.5s load × 10 pages = 15s total ❌

With 60s cache:
├─ First load: 1.5s (cache miss)
├─ Next 9 loads: 0.7s (cache hit)
└─ 1.5s + (9 × 0.7s) = 7.8s total ✅ (48% faster)
```

#### Why 60 Seconds?
```
Too Short (10s):
├─ Frequent cache misses
└─ Minimal benefit

Too Long (300s):
├─ Stale data after admin updates
└─ User confusion

60s Sweet Spot:
├─ Multiple page views cached
├─ Admin updates visible within 1 minute
└─ BI standard (Tableau: minutes, GA: hours)
```

#### Config Hash Invalidation
```
Automatic invalidation on:
✅ Baseline switch (Custom ↔ Quantum)
✅ Weight adjustment (session)
✅ Aspect active/inactive toggle

NO invalidation on:
⏩ Tolerance change (applied after cache)
⏩ Page navigation (uses same cache)
```

### Consequences

**Positive:**
- ✅ 48% faster multi-page navigation
- ✅ Instant tolerance adjustment (applied after cache)
- ✅ Automatic invalidation (no manual cache clear)

**Negative:**
- ⚠️ Max 60s delay for admin custom standard updates
- ⚠️ Memory usage (5MB per event × category)

**Mitigation:**
- Acceptable delay for BI use case
- Cache cleanup scheduled daily
- Can reduce TTL to 10s if needed

---

## ADR-004: Why RankingService Uses `toBase()` Not Eloquent Models

**Status:** ✅ Accepted
**Date:** December 2025 (Custom Standard Performance Fix)

### Context
Custom Standard was 10x slower than Quantum Default (10.3s vs 1.5s) karena eager loading 133K+ Eloquent models.

### Problem Analysis
```
BEFORE FIX:
if ($hasSubAspectAdjustments) {
    // Custom Standard path
    $query->with(['aspect.subAspects', 'subAspectAssessments.subAspect']);
    $assessments = $query->get();  // ❌ 133K models hydrated
} else {
    // Quantum Default path
    $assessments = $query->toBase()->get();  // ✅ 178 objects
}

Why Custom Standard triggered slow path?
├─ hasActiveSubAspectAdjustments() checks if any sub-aspect inactive
├─ Custom Standard HAS inactive sub-aspects (by design)
└─ Assumption: "Need sub-aspects to recalculate rating" ❌ WRONG!

Reality:
├─ individual_rating already pre-calculated in DB
├─ Sub-aspects only needed for INDIVIDUAL REPORTS
└─ Ranking only needs aspect-level data
```

### Decision
**ALWAYS use `toBase()`** untuk ranking, regardless of baseline.

```php
// AFTER FIX (Simplified):
$assessments = $query->toBase()->get();  // Always lightweight
$individualRating = (float) $assessment->individual_rating;  // Pre-calculated
```

### Reasoning

#### What `toBase()` Does
```php
// Normal Eloquent Query:
$assessments = AspectAssessment::with(['aspect'])->get();
// Returns: Collection<AspectAssessment> (Eloquent models)
// Cost: Model hydration, relation loading, casts, mutators, etc.

// toBase() Query:
$assessments = AspectAssessment::query()->toBase()->get();
// Returns: Collection<stdClass> (Plain objects)
// Cost: Just fetch data, no ORM overhead
```

#### Performance Comparison
```
For 4,905 participants × 13 aspects = 63,765 records:

Eloquent Models:
├─ Hydration: ~500ms
├─ Relations: ~1,000ms (if eager loaded)
├─ Memory: ~50MB
└─ Total: ~1,500ms ❌

toBase() Objects:
├─ Fetch: ~150ms
├─ Relations: None
├─ Memory: ~5MB
└─ Total: ~150ms ✅ (10x faster)
```

#### When to Use Each

```
Use Eloquent Models:
✅ Individual reports (need sub-aspect breakdown)
✅ CRUD operations (need save/update/delete)
✅ Complex business logic (need model methods)

Use toBase():
✅ Ranking (only need read values)
✅ Export (simple data output)
✅ Statistics (aggregation only)
```

### Consequences

**Positive:**
- ✅ 87% faster ranking with Custom Standard
- ✅ 99.92% fewer objects hydrated (133K → 104)
- ✅ Consistent performance (Quantum = Custom)

**Negative:**
- ⚠️ Cannot use model methods on results
- ⚠️ Manual type casting needed (`(float)`)

**Mitigation:**
- Well-documented in code comments
- Type casting handled in service layer
- Individual reports still use full models

---

## ADR-005: Data-Driven vs Hard-Coded Logic

**Status:** ✅ Accepted
**Date:** 2024 (Original Design)

### Context
Aspects bisa punya atau tidak punya sub-aspects. Logic harus adaptif.

### Decision
Use **data structure** to determine behavior, NOT hard-coded checks.

### Examples

#### ✅ GOOD: Data-Driven
```php
if ($aspect->subAspects->isNotEmpty()) {
    // Has sub-aspects → Calculate from them
    $rating = $this->calculateFromSubAspects($aspect);
} else {
    // No sub-aspects → Use direct rating
    $rating = $aspect->standard_rating;
}
```

#### ❌ BAD: Hard-Coded
```php
if (in_array($aspect->code, ['daya-pikir', 'sikap-kerja'])) {
    // Hard-coded list breaks when new aspects added
    $rating = $this->calculateFromSubAspects($aspect);
}
```

### Reasoning

**Flexibility:**
```
Template "P3K 2025":
├─ Daya Pikir: HAS sub-aspects ✅
└─ Integritas: NO sub-aspects ✅

Template "CPNS 2026":
├─ Daya Pikir: NO sub-aspects ✅  (Different structure!)
└─ Integritas: HAS sub-aspects ✅

Data-driven code works for BOTH without changes.
Hard-coded would break.
```

**Maintainability:**
```
Adding new aspect:
Data-driven: Works automatically ✅
Hard-coded: Must update code in 10+ places ❌
```

### Consequences

**Positive:**
- ✅ Template-agnostic (works with any structure)
- ✅ No code changes for new templates
- ✅ Easier to test

**Negative:**
- ⚠️ Slightly more complex logic
- ⚠️ Requires eager loading for structure checks

**Mitigation:**
- AspectCacheService for structure caching
- Clear naming conventions

---

## ADR-006: Why IndividualAssessmentService ≠ RankingService

**Status:** ✅ Accepted
**Date:** 2024-2025

### Context
Individual reports dan ranking calculations kelihatan similar, tapi punya requirements berbeda.

### Decision
Maintain **separate services** untuk ranking vs individual reports.

### Differences

| Aspect | RankingService | IndividualAssessmentService |
|--------|----------------|----------------------------|
| **Scope** | All participants | Single participant |
| **Data Load** | Minimal (aspect-level) | Complete (sub-aspect details) |
| **Query Type** | `toBase()` | Eloquent models |
| **Sub-Aspects** | Not loaded | Eager loaded |
| **Output** | Sorted collection | Detailed breakdown |
| **Performance** | Must be <2s for 5K | Can be 500ms for 1 |

### Why NOT Merge?

```php
// BAD: Merged service
class AssessmentService {
    public function getResults($participantId = null) {
        if ($participantId) {
            // Individual path: Load everything
            return $this->getDetailedResults($participantId);
        } else {
            // Ranking path: Lightweight
            return $this->getRankings();
        }
    }
}
// Issues:
// - Mixed concerns
// - Hard to optimize separately
// - Confusing for developers
```

```php
// GOOD: Separate services
class RankingService {
    // Optimized for bulk operations
    public function getRankings(...): Collection {
        return $query->toBase()->get();  // Lightweight
    }
}

class IndividualAssessmentService {
    // Optimized for detail
    public function getParticipantDetails($id): array {
        return AspectAssessment::with(['subAspectAssessments'])->get();
    }
}
```

### Consequences

**Positive:**
- ✅ Clear separation of concerns
- ✅ Independent optimization
- ✅ Easier testing

**Negative:**
- ⚠️ Some code duplication (3-layer priority checks)
- ⚠️ Two services to maintain

**Mitigation:**
- Shared `DynamicStandardService` for standards
- Clear documentation on when to use which

---

## Summary: Key Architectural Principles

1. **Historical Data is Immutable**
   - Individual ratings never recalculated after assessment
   - Baseline changes only affect comparison, not raw data

2. **3-Layer Priority is Sacred**
   - Session > Custom > Quantum
   - Always go through `DynamicStandardService`

3. **Cache for Exploration Speed**
   - 60s TTL acceptable for BI workloads
   - Config-based invalidation

4. **Right Tool for the Job**
   - `toBase()` for ranking (bulk read)
   - Eloquent models for individual reports (detail)

5. **Data-Driven, Not Hard-Coded**
   - Let data structure determine behavior
   - Template-agnostic design

6. **Separation of Concerns**
   - RankingService ≠ IndividualAssessmentService
   - Each optimized for its use case

---

**Last Updated:** December 2025
**Next Review:** When adding new major features
