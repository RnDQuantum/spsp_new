# Testing Scenarios: 3-Layer Priority System & Baseline Management

## 📋 Table of Contents
1. [Purpose & Philosophy](#purpose--philosophy)
2. [Critical Concepts to Test](#critical-concepts-to-test)
3. [3-Layer Priority Test Scenarios](#3-layer-priority-test-scenarios)
4. [Data Immutability Test Scenarios](#data-immutability-test-scenarios)
5. [Active/Inactive Aspect Test Scenarios](#activeinactive-aspect-test-scenarios)
6. [Calculation Accuracy Test Scenarios](#calculation-accuracy-test-scenarios)
7. [Cache Invalidation Test Scenarios](#cache-invalidation-test-scenarios)
8. [Edge Cases & Boundary Conditions](#edge-cases--boundary-conditions)
9. [Cross-Service Consistency Tests](#cross-service-consistency-tests)
10. [Performance Regression Tests](#performance-regression-tests)

---

## 🎯 Purpose & Philosophy

### Why This Document Exists

**Purpose:**
- Define ALL test scenarios needed to verify SPSP's core business logic
- Focus on **finding bugs**, not just confirming existing behavior
- Ensure 100% coverage of critical paths and edge cases
- Document **expected behavior** for each scenario

**Philosophy:**
```
❌ BAD TEST: "Does code work as currently written?"
✅ GOOD TEST: "Does code handle all possible scenarios correctly?"

❌ BAD TEST: Confirms current implementation
✅ GOOD TEST: Discovers bugs in current implementation
```

### SPSP-Specific Testing Challenges

**Key Complexities:**
1. **3-Layer Priority System** (Session → Custom Standard → Quantum Default)
2. **Data Immutability Principle** (individual_rating NEVER recalculated for ranking/stats)
3. **Active/Inactive Aspects** (Dynamic filtering based on session/custom standard)
4. **Multi-Service Consistency** (Same input must produce same output across services)
5. **Cache Invalidation** (Config changes must invalidate correctly)

---

## 🔑 Critical Concepts to Test

### 1. The 3-Layer Priority System

**Concept:**
```
Priority Order (Highest to Lowest):
┌─────────────────────────────────────┐
│ Layer 1: SESSION ADJUSTMENT         │ ← User's temporary exploration
│ Priority: HIGHEST                    │
│ Storage: Session (temporary)         │
├─────────────────────────────────────┤
│ Layer 2: CUSTOM STANDARD            │ ← Institution's baseline
│ Priority: MEDIUM                     │
│ Storage: Database (permanent)        │
├─────────────────────────────────────┤
│ Layer 3: QUANTUM DEFAULT            │ ← System baseline
│ Priority: LOWEST (fallback)         │
│ Storage: Database (permanent)        │
└─────────────────────────────────────┘
```

**Critical Test Questions:**
- Does Layer 1 override Layer 2?
- Does Layer 2 override Layer 3?
- Does clearing Layer 1 fall back to Layer 2?
- Does clearing Layer 2 fall back to Layer 3?
- Do services respect this priority consistently?

### 2. Data Immutability Principle

**Concept:**
```php
// IMMUTABLE (Historical Assessment Data):
$individual_rating  // Pre-calculated on assessment day
$participant_name   // Participant identity
$test_date         // Assessment timestamp

// CONFIGURABLE (Baseline Settings):
$weight_percentage  // How important each aspect is
$standard_rating   // Minimum expected value
$active_status     // Which aspects to include
```

**Critical Test Questions:**
- Does `individual_rating` EVER change after assessment?
- Do baseline changes affect stored data?
- Are rankings recalculated correctly without changing source data?

### 3. Active/Inactive Logic

**Concept:**
```
User can mark aspects/sub-aspects as inactive:
- Inactive aspects: Excluded from ranking calculations
- Inactive sub-aspects: Excluded from aspect rating calculation

CRITICAL DISTINCTION:
├─ For RANKING: Use stored individual_rating (ignore sub-aspect status)
└─ For INDIVIDUAL REPORTS: Recalculate from active sub-aspects only
```

**Critical Test Questions:**
- Does disabling a sub-aspect recalculate individual_rating for reports?
- Does disabling a sub-aspect affect ranking scores?
- **⚠️ POTENTIAL BUG AREA**: Are we using correct logic in each context?

---

## 🧪 3-Layer Priority Test Scenarios

### Scenario Group 1: Basic Layer Priority

#### Test 1.1: Layer 3 (Quantum Default) Baseline
```yaml
Given:
  - No Custom Standard selected
  - No Session adjustments
  - Aspect "Integritas" in database has:
    - weight: 15%
    - standard_rating: 4

When:
  - User loads ranking page

Then:
  - Aspect weight should be: 15%
  - Aspect standard_rating should be: 4
  - Source: Layer 3 (Quantum Default)

Services to Test:
  - RankingService.getRankings()
  - StatisticService.getDistributionData()
  - TrainingRecommendationService.getTrainingSummary()
  - IndividualAssessmentService.getParticipantAssessment()
```

#### Test 1.2: Layer 2 (Custom Standard) Overrides Layer 3
```yaml
Given:
  - Custom Standard "Kejaksaan 2025" exists with:
    - Integritas weight: 20% (different from Layer 3: 15%)
    - Integritas rating: 5 (different from Layer 3: 4)
  - User selects Custom Standard "Kejaksaan 2025"
  - No Session adjustments

When:
  - User loads ranking page

Then:
  - Aspect weight should be: 20% (NOT 15%)
  - Aspect standard_rating should be: 5 (NOT 4)
  - Source: Layer 2 (Custom Standard)

Services to Test:
  - DynamicStandardService.getAspectWeight()
  - DynamicStandardService.getAspectRating()
  - RankingService.calculateAdjustedStandards()
```

#### Test 1.3: Layer 1 (Session) Overrides Layer 2
```yaml
Given:
  - Custom Standard "Kejaksaan 2025" selected:
    - Integritas weight: 20%
    - Integritas rating: 5
  - User makes session adjustment:
    - Integritas weight: 25%
    - Integritas rating: 4

When:
  - User loads ranking page

Then:
  - Aspect weight should be: 25% (NOT 20% from Custom Standard)
  - Aspect standard_rating should be: 4 (NOT 5 from Custom Standard)
  - Source: Layer 1 (Session Adjustment)

Services to Test:
  - DynamicStandardService.getAspectWeight()
  - DynamicStandardService.getAspectRating()
```

#### Test 1.4: Layer 1 Partial Override (Mix of Layers)
```yaml
Given:
  - Custom Standard "Kejaksaan 2025" selected:
    - Integritas weight: 20%, rating: 5
    - Kepemimpinan weight: 12%, rating: 4
  - User adjusts ONLY Integritas in session:
    - Integritas weight: 25%
    - (Kepemimpinan NOT adjusted)

When:
  - User loads ranking page

Then:
  - Integritas weight: 25% (Layer 1 override)
  - Integritas rating: 5 (Layer 2, not overridden)
  - Kepemimpinan weight: 12% (Layer 2, no override)
  - Kepemimpinan rating: 4 (Layer 2, no override)

Critical: Test that ONLY adjusted values use Layer 1
```

#### Test 1.5: Reset Session Returns to Layer 2
```yaml
Given:
  - Custom Standard "Kejaksaan 2025" selected
  - User had session adjustments active
  - User clicks "Reset Adjustments"

When:
  - Session adjustments cleared

Then:
  - All values should return to Custom Standard (Layer 2)
  - NO values should come from Layer 1
  - Cache should invalidate

Services to Test:
  - DynamicStandardService.clearAdjustments()
```

#### Test 1.6: Switch Custom Standard Clears Session
```yaml
Given:
  - Custom Standard "Kejaksaan 2025" selected
  - User has session adjustments active
  - User switches to Custom Standard "Polri 2025"

When:
  - Custom Standard changed

Then:
  - Session adjustments should be cleared
  - All values from new Custom Standard (Layer 2)
  - Cache should invalidate

Critical: Old session adjustments must NOT apply to new standard
```

---

## 🔒 Data Immutability Test Scenarios

### Scenario Group 2: Individual Rating Immutability

#### Test 2.1: Baseline Change Does NOT Alter Stored Data
```yaml
Given:
  - Participant "John Doe" has:
    - Aspect "Integritas" individual_rating: 4.5 (in database)
  - Quantum Default baseline selected

When:
  - User switches to Custom Standard
  - Custom Standard has different weight and standard_rating

Then:
  - Database individual_rating should STILL be: 4.5
  - Ranking score may change (due to weight change)
  - But source individual_rating MUST NOT change

SQL Verification:
  SELECT individual_rating
  FROM aspect_assessments
  WHERE participant_id = X AND aspect_id = Y
  -- Should be UNCHANGED
```

#### Test 2.2: Session Adjustment Does NOT Alter Stored Data
```yaml
Given:
  - Participant has individual_rating: 4.5
  - User adjusts aspect weight in session

When:
  - Weight changed from 15% → 25%

Then:
  - individual_rating in database: STILL 4.5
  - individual_score recalculated: 4.5 * 25 = 112.5 (was 67.5)
  - But individual_rating itself: UNCHANGED

Critical: Score changes, rating does NOT
```

#### Test 2.3: Disabling Sub-Aspect Does NOT Alter Stored Data
```yaml
Given:
  - Aspect "Sikap Kerja" has 5 sub-aspects
  - Stored individual_rating: 3.6 (average of 5 sub-aspects)
  - User disables 1 sub-aspect in session

When:
  - Sub-aspect "Kedisiplinan" marked inactive

Then:
  - Database individual_rating: STILL 3.6
  - For ranking: Use stored 3.6
  - For individual report: Recalculate from 4 active sub-aspects

Critical Bug Test: Are we using correct value in each context?
```

---

## 🎚️ Active/Inactive Aspect Test Scenarios

### Scenario Group 3: Aspect Active/Inactive Logic

#### Test 3.1: Inactive Aspect Excluded from Ranking
```yaml
Given:
  - Aspect "Integritas" exists in template
  - User marks Integritas as inactive (in session or custom standard)

When:
  - User views ranking

Then:
  - Integritas should NOT appear in aspect list
  - Integritas score should NOT contribute to total
  - Total score = sum of ACTIVE aspects only

Services to Test:
  - DynamicStandardService.getActiveAspectIds()
  - RankingService.getRankings()
```

#### Test 3.2: Inactive Sub-Aspect in Ranking (CRITICAL BUG POTENTIAL)
```yaml
Given:
  - Aspect "Sikap Kerja" has 5 sub-aspects
  - Participant's stored individual_rating: 3.6
  - User disables 1 sub-aspect in session

When:
  - User views ranking

Then:
  - ❓ QUESTION: Should ranking use 3.6 or recalculate?

Expected Behavior (CRITICAL TO VERIFY):
  OPTION A (Current RankingService):
    - Use stored individual_rating: 3.6
    - Reason: Ranking shows historical performance

  OPTION B (Current IndividualAssessmentService):
    - Recalculate from active sub-aspects: 4.0
    - Reason: Shows performance against adjusted baseline

CRITICAL TEST:
  - Verify which option each service uses
  - Ensure consistency within same service type
  - Document the intended behavior
```

#### Test 3.3: All Sub-Aspects Inactive (Edge Case)
```yaml
Given:
  - Aspect "Sikap Kerja" has 5 sub-aspects
  - User disables ALL 5 sub-aspects

When:
  - Calculate aspect rating

Then:
  - Standard rating should be: ???
  - Individual rating should be: ???

Expected Behavior Options:
  OPTION A: Use aspect's direct rating (if exists)
  OPTION B: Return 0
  OPTION C: Mark entire aspect as inactive

CRITICAL: Document and test actual behavior
```

#### Test 3.4: Mixed Active/Inactive in Custom Standard
```yaml
Given:
  - Custom Standard "Kejaksaan 2025" has:
    - Integritas: active
    - Kepemimpinan: inactive
    - Kerjasama: active
  - User adds session adjustment:
    - Reactivate Kepemimpinan

When:
  - Calculate rankings

Then:
  - Kepemimpinan should be: ACTIVE (Layer 1 override)
  - Should contribute to total score

Critical: Test that session can reactivate custom standard exclusions
```

---

## 🧮 Calculation Accuracy Test Scenarios

### Scenario Group 4: Mathematical Correctness

#### Test 4.1: Standard Rating Calculation (No Sub-Aspects)
```yaml
Given:
  - Aspect "Integritas" has NO sub-aspects
  - standard_rating in database: 4.0

When:
  - Calculate standard rating

Then:
  - Should return: 4.0 (direct from database)

Services to Test:
  - DynamicStandardService.getAspectRating()
  - RankingService.calculateAdjustedStandards()
```

#### Test 4.2: Standard Rating Calculation (With Sub-Aspects)
```yaml
Given:
  - Aspect "Sikap Kerja" has 5 sub-aspects:
    - Sistematika Kerja: rating 5
    - Ketekunan: rating 4
    - Kerjasama: rating 3
    - Kedisiplinan: rating 2
    - Tanggung Jawab: rating 4
  - All sub-aspects active

When:
  - Calculate aspect standard rating

Then:
  - Should return: (5 + 4 + 3 + 2 + 4) / 5 = 3.6

Services to Test:
  - DynamicStandardService.getAspectRating()
  - StatisticService.calculateStandardRating()
```

#### Test 4.3: Standard Rating with Inactive Sub-Aspect
```yaml
Given:
  - Aspect "Sikap Kerja" has 5 sub-aspects (same as Test 4.2)
  - User disables "Kedisiplinan" (rating: 2)

When:
  - Calculate aspect standard rating

Then:
  - Should return: (5 + 4 + 3 + 4) / 4 = 4.0
  - NOT: 3.6 (original average)

Critical: Only ACTIVE sub-aspects included in calculation
```

#### Test 4.4: Individual Score Calculation
```yaml
Given:
  - Participant "John Doe" has:
    - individual_rating: 4.5
    - aspect_weight: 15

When:
  - Calculate individual_score

Then:
  - individual_score = 4.5 * 15 = 67.5

Critical: Verify rounding rules (2 decimal places)
```

#### Test 4.5: Total Score Calculation (Combined)
```yaml
Given:
  - Category "Potensi" (weight: 25%)
    - Participant score: 100
  - Category "Kompetensi" (weight: 75%)
    - Participant score: 300

When:
  - Calculate total score

Then:
  - Total = (100 * 0.25) + (300 * 0.75) = 25 + 225 = 250

Services to Test:
  - RankingService.getCombinedRankings()
```

#### Test 4.6: Tolerance Adjustment
```yaml
Given:
  - Original standard_score: 100
  - Tolerance: 10%

When:
  - Apply tolerance

Then:
  - Adjusted standard_score = 100 * (1 - 0.10) = 90
  - Gap calculation uses adjusted value

Services to Test:
  - RankingService.getRankings() (applies tolerance after cache)
  - TrainingRecommendationService.getTrainingSummary()
```

---

## 💾 Cache Invalidation Test Scenarios

### Scenario Group 5: Cache Behavior

#### Test 5.1: Cache Hit on Repeated Load (Same Config)
```yaml
Given:
  - User loads ranking with Quantum Default
  - Cache TTL: 60 seconds

When:
  - User reloads page within 60 seconds
  - NO config changes

Then:
  - Should use cached result
  - No database queries for rankings
  - Response time: <100ms

Verification:
  - Check cache key exists
  - Verify no SQL queries executed
```

#### Test 5.2: Cache Miss on Baseline Change
```yaml
Given:
  - Rankings cached for Quantum Default
  - Cache TTL: 60 seconds (not expired)

When:
  - User switches to Custom Standard

Then:
  - Cache key should change (different config hash)
  - Should trigger new calculation
  - New cache created for Custom Standard

Critical: Old cache should NOT be used
```

#### Test 5.3: Cache Miss on Session Adjustment
```yaml
Given:
  - Rankings cached for Custom Standard
  - User adjusts aspect weight in session

When:
  - Page reloads

Then:
  - Config hash should change
  - Cache should miss
  - New calculation triggered

Services to Test:
  - RankingService (check config hash includes session ID)
  - StatisticService (check adjustment hash)
```

#### Test 5.4: Cache Persists Across Tolerance Changes
```yaml
Given:
  - Rankings cached
  - User changes tolerance from 10% → 15%

When:
  - Tolerance slider moved

Then:
  - Should use SAME cache
  - Tolerance applied client-side or after cache
  - No recalculation needed

Critical: Tolerance NOT in cache key
```

#### Test 5.5: Cache Expiration (TTL)
```yaml
Given:
  - Rankings cached at T=0
  - Cache TTL: 60 seconds
  - Current time: T=61 seconds

When:
  - User loads ranking

Then:
  - Cache expired
  - Should trigger recalculation
  - New cache created

Verification:
  - Check timestamp in cache metadata
```

---

## 🔺 Edge Cases & Boundary Conditions

### Scenario Group 6: Unusual Situations

#### Test 6.1: Zero Participants
```yaml
Given:
  - Event exists but has NO participants

When:
  - Load ranking page

Then:
  - Should return empty collection
  - Should NOT throw exception
  - UI should show "No data" message

Services to Test:
  - All ranking-related services
```

#### Test 6.2: Single Participant
```yaml
Given:
  - Event has exactly 1 participant

When:
  - Calculate ranking

Then:
  - Rank should be: 1
  - Total should be: 1
  - Conclusion should calculate correctly

Edge Case: Division by zero checks
```

#### Test 6.3: All Participants Same Score (Tie)
```yaml
Given:
  - 10 participants all have score: 100.0

When:
  - Calculate ranking

Then:
  - All should have rank: 1
  OR
  - Ranks 1-10 with tiebreaker (name alphabetical)

Critical: Document tiebreaker logic
```

#### Test 6.4: Participant with No Assessment Data
```yaml
Given:
  - Participant exists but has NO aspect_assessments

When:
  - Load ranking

Then:
  - Should exclude from ranking
  OR
  - Should show with score: 0

Critical: Document expected behavior
```

#### Test 6.5: Aspect with No Participants
```yaml
Given:
  - Aspect exists in template
  - NO participants have assessment for this aspect

When:
  - Calculate statistics

Then:
  - Distribution: all buckets = 0
  - Average: 0.0
  - Should NOT throw exception
```

#### Test 6.6: Rating Exactly on Boundary
```yaml
Given:
  - Bucket boundaries:
    - Bucket 2: 1.80 - 2.60
    - Bucket 3: 2.60 - 3.40
  - Participant rating: exactly 2.60

When:
  - Categorize into bucket

Then:
  - Should be in: Bucket 3 (not Bucket 2)

Critical: Test boundary inclusivity (>= vs >)
```

#### Test 6.7: Negative Gap (Below Standard)
```yaml
Given:
  - Individual score: 50
  - Standard score: 75
  - Gap: -25 (below standard)

When:
  - Calculate conclusion

Then:
  - Should be: "Di Bawah Standar"
  - Gap sign should be preserved (negative)

Services to Test:
  - ConclusionService.getGapBasedConclusion()
```

#### Test 6.8: Extreme Values (Max Rating)
```yaml
Given:
  - Individual rating: 5.0 (maximum)
  - Standard rating: 5.0 (maximum)

When:
  - Calculate gap and conclusion

Then:
  - Gap: 0.0
  - Conclusion: "Memenuhi Standar"

Edge Case: No mathematical errors at boundaries
```

#### Test 6.9: Extreme Values (Min Rating)
```yaml
Given:
  - Individual rating: 1.0 (minimum)
  - Standard rating: 5.0 (maximum)

When:
  - Calculate gap

Then:
  - Gap: -4.0 (largest negative possible)
  - Conclusion: "Di Bawah Standar"
```

---

## 🔄 Cross-Service Consistency Tests

### Scenario Group 7: Multi-Service Agreement

#### Test 7.1: Same Participant, Same Result Across Services
```yaml
Given:
  - Participant "John Doe"
  - Event, Position, Baseline configured

When:
  - Query from:
    - RankingService.getRankings()
    - IndividualAssessmentService.getParticipantAssessment()

Then:
  - Both should return SAME:
    - individual_rating
    - individual_score (for same aspect)
    - conclusion (for same tolerance)

Critical: Cross-service consistency
```

#### Test 7.2: Statistic Average Matches Ranking Average
```yaml
Given:
  - 100 participants with known ratings
  - Calculate average in two ways:
    - StatisticService.calculateAverageRating()
    - Manual average from RankingService.getRankings()

When:
  - Compare results

Then:
  - Averages should MATCH (within rounding tolerance)

Critical: Different calculation paths should agree
```

#### Test 7.3: Training Recommendation Matches Ranking Order
```yaml
Given:
  - Aspect "Integritas"
  - Participants sorted by rating (ascending)

When:
  - Compare:
    - TrainingRecommendationService (priority order)
    - RankingService (filtered for this aspect)

Then:
  - Order should MATCH
  - Priority 1 = Lowest rating
  - Recommendation logic consistent

Critical: Different services should agree on order
```

#### Test 7.4: Standard Rating Consistency Across Services
```yaml
Given:
  - Aspect "Integritas"
  - Baseline configured (any layer)

When:
  - Get standard_rating from:
    - RankingService.calculateAdjustedStandards()
    - StatisticService.calculateStandardRating()
    - TrainingRecommendationService.getOriginalStandardRating()

Then:
  - All should return SAME value

Critical: All services use DynamicStandardService consistently
```

---

## ⚡ Performance Regression Tests

### Scenario Group 8: Performance Benchmarks

#### Test 8.1: Quantum Default Performance (Baseline)
```yaml
Given:
  - 5,000 participants
  - Quantum Default baseline

When:
  - Load ranking

Then:
  - Request time: <1 second (cold cache)
  - Request time: <300ms (warm cache)
  - Models loaded: <100
  - Queries: <30

Critical: Establish baseline performance
```

#### Test 8.2: Custom Standard Performance (Should Match)
```yaml
Given:
  - 5,000 participants
  - Custom Standard baseline

When:
  - Load ranking

Then:
  - Request time: <1 second (should match Quantum)
  - Models loaded: <100 (should match Quantum)

Critical: No performance degradation for Custom Standard
```

#### Test 8.3: Session Adjustment Performance
```yaml
Given:
  - 5,000 participants
  - Session adjustments active

When:
  - Load ranking

Then:
  - Request time: <1.2 seconds (slight overhead acceptable)
  - Cache invalidation works correctly

Critical: Session adjustments should not kill performance
```

#### Test 8.4: Large Dataset Scalability
```yaml
Given:
  - 10,000 participants (2x normal)

When:
  - Load ranking

Then:
  - Request time: <2 seconds (linear scaling)
  - No memory errors
  - No timeout errors

Critical: System scales linearly, not exponentially
```

---

## 📊 Test Coverage Matrix

### Required Coverage by Service

| Service | Layer Priority | Immutability | Active/Inactive | Cache | Calculation | Edge Cases |
|---------|---------------|--------------|-----------------|-------|-------------|------------|
| **DynamicStandardService** | ✅ CRITICAL | ✅ N/A | ✅ CRITICAL | ❌ N/A | ✅ High | ✅ High |
| **RankingService** | ✅ CRITICAL | ✅ CRITICAL | ⚠️ VERIFY | ✅ High | ✅ High | ✅ Medium |
| **StatisticService** | ✅ CRITICAL | ⚠️ VERIFY | ⚠️ VERIFY | ✅ High | ✅ High | ✅ Medium |
| **TrainingRecommendationService** | ✅ High | ✅ High | ⚠️ VERIFY | ✅ High | ✅ High | ✅ Medium |
| **IndividualAssessmentService** | ✅ High | ⚠️ VERIFY | ✅ CRITICAL | ✅ Medium | ✅ High | ✅ Medium |
| **ConclusionService** | ❌ N/A | ❌ N/A | ❌ N/A | ❌ N/A | ✅ CRITICAL | ✅ High |

**Legend:**
- ✅ CRITICAL: Must have 100% test coverage
- ✅ High: Should have >80% test coverage
- ✅ Medium: Should have >60% test coverage
- ⚠️ VERIFY: Potential bug area - needs investigation before testing
- ❌ N/A: Not applicable to this service

---

## 🐛 Known Bug Potential Areas (Require Investigation)

### Area 1: Sub-Aspect Active/Inactive Logic

**Issue:**
```
StatisticService may be using stored individual_rating even when
sub-aspects are disabled, while IndividualAssessmentService recalculates.

Inconsistency between:
- Ranking context: Use stored rating (IMMUTABLE)
- Report context: Recalculate from active sub-aspects

NEEDS VERIFICATION:
- Is this intentional design?
- Or is it a bug?
- Should be consistent or context-dependent?
```

**Test to Write:**
```yaml
Test: Sub-Aspect Disable Impact
Given:
  - Aspect with 5 sub-aspects
  - Disable 1 sub-aspect in session

When:
  - Compare:
    - StatisticService.calculateAverageRating()
    - IndividualAssessmentService (for same participant)

Then:
  - Document which uses stored vs recalculated
  - Verify if difference is intentional
  - If bug, fix; if intentional, document why
```

### Area 2: Cache Key Completeness

**Issue:**
```
Cache keys might not include ALL config that affects results.

Potential missing factors:
- Active/inactive sub-aspect status
- Custom standard sub_aspect_configs
- Category active status

NEEDS VERIFICATION:
- Are all config factors in cache key?
- Can cache return stale data?
```

**Test to Write:**
```yaml
Test: Cache Key Completeness
Given:
  - Rankings cached
  - User changes sub-aspect active status (in session or custom standard)

When:
  - Reload page

Then:
  - Cache should MISS (config changed)
  - NOT return old results

Critical: Verify cache key includes sub-aspect status
```

### Area 3: Tolerance Application Consistency

**Issue:**
```
Tolerance should be applied AFTER cache for instant UX.
But does every service do this correctly?

NEEDS VERIFICATION:
- Is tolerance in cache key for any service?
- Is tolerance applied consistently?
```

**Test to Write:**
```yaml
Test: Tolerance Not in Cache Key
Given:
  - Rankings cached with tolerance 10%
  - User changes tolerance to 15%

When:
  - Check if cache key changed

Then:
  - Cache key should be SAME
  - Only tolerance calculation different
  - No database query triggered
```

---

## 🎯 Test Writing Guidelines

### When Writing Tests for These Scenarios

**DO:**
1. ✅ Test the CONCEPT, not the implementation
2. ✅ Use realistic data (5,000 participants, not 3)
3. ✅ Test edge cases explicitly
4. ✅ Verify cross-service consistency
5. ✅ Check performance regressions
6. ✅ Document expected vs actual behavior

**DON'T:**
1. ❌ Assume current code is correct
2. ❌ Skip edge cases because "it should work"
3. ❌ Test only happy paths
4. ❌ Ignore performance in tests
5. ❌ Write tests that confirm bugs

### Test Naming Convention

```php
// ✅ GOOD: Describes scenario and expected outcome
test_layer1_session_adjustment_overrides_custom_standard()
test_inactive_subaspect_excluded_from_standard_rating_calculation()
test_cache_invalidates_when_baseline_switched()

// ❌ BAD: Describes implementation
test_getRankings_returns_collection()
test_calculateAverageRating_uses_db_query()
```

---

## 📚 Related Documentation

- [SPSP_BUSINESS_CONCEPTS.md](./SPSP_BUSINESS_CONCEPTS.md) - Core business logic
- [TESTING_GUIDE.md](./TESTING_GUIDE.md) - How to write tests
- [LIVEWIRE_TESTING_GUIDE.md](./LIVEWIRE_TESTING_GUIDE.md) - Livewire-specific testing

---

**Last Updated:** December 2025
**Purpose:** Test Scenario Definition for Bug Discovery
**Status:** 🚧 Ready for Test Implementation
**Maintainer:** SPSP Development Team
