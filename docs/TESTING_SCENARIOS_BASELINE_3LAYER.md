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
11. **[NEW] [Livewire Component Integration Tests](#livewire-component-integration-tests)**
12. **[NEW] [Baseline Switching Tests](#baseline-switching-tests)**
13. **[NEW] [Event Communication Tests](#event-communication-tests)**
14. **[NEW] [Cache Key Completeness Tests](#cache-key-completeness-tests)**

---

## 🎯 Purpose & Philosophy

### Why This Document Exists

**Purpose:**
- Define ALL test scenarios needed to verify SPSP's core business logic
- **Serve as KIBLAT (reference) for Claude Code when writing Livewire layer tests**
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
2. **Context-Dependent Data Immutability** (individual_rating handling varies by context) ⚠️ **UPDATED**
3. **Active/Inactive Aspects** (Dynamic filtering based on session/custom standard)
4. **Multi-Service Consistency** (Same input must produce same output across services)
5. **Cache Invalidation** (Config changes must invalidate correctly)
6. **Livewire Component State Management** (Session, events, modals, dropdowns) ⭐ **NEW**

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

### 2. Context-Dependent Data Immutability Principle ⚠️ **UPDATED - CRITICAL CLARIFICATION**

**Concept:**
```php
// ALWAYS IMMUTABLE (Database Never Changes):
$aspect_assessments->individual_rating  // In DATABASE - NEVER MODIFIED
$participant->name                      // Participant identity
$test_date                             // Assessment timestamp

// CONFIGURABLE (Baseline Settings):
$weight_percentage  // How important each aspect is
$standard_rating   // Minimum expected value
$active_status     // Which aspects to include
```

**⚠️ CRITICAL: Context-Dependent Individual Rating Usage**

```
┌──────────────────────────────────────────────────────────────────────┐
│ CONTEXT 1: NO Active/Inactive Changes (Default Behavior)            │
│────────────────────────────────────────────────────────────────────│
│ Scenario: Quantum Default OR Custom Standard (no sub-aspect changes)│
│ Logic: Use STORED individual_rating (IMMUTABLE - historical data)   │
│ Why: Preserve historical performance as assessed                    │
│                                                                      │
│ Example:                                                             │
│ - Stored individual_rating: 3.6 (from 5 sub-aspects)               │
│ - Standard rating: 3.6 (from 5 sub-aspects)                        │
│ - Comparison: FAIR (same baseline)                                  │
└──────────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────────┐
│ CONTEXT 2: Active/Inactive Sub-Aspects Changed                      │
│────────────────────────────────────────────────────────────────────│
│ Scenario: Session adjustment OR Custom Standard with inactive       │
│           sub-aspects                                                │
│ Logic: RECALCULATE individual_rating from ACTIVE sub-aspects only   │
│ Why: Fair comparison - both standard and individual use same basis  │
│                                                                      │
│ Example:                                                             │
│ - Stored individual_rating: 3.6 (from 5 sub-aspects: [5,4,3,2,4])  │
│ - User disables sub-aspect with rating 2                           │
│ - Recalculated individual_rating: 4.0 (from 4 active: [5,4,3,4])   │
│ - Standard rating: 4.0 (from 4 active sub-aspects)                 │
│ - Comparison: FAIR (same baseline)                                  │
│                                                                      │
│ ⚠️ UNFAIR if NOT recalculated:                                      │
│ - Individual: 3.6 (5 sub-aspects) vs Standard: 4.0 (4 sub-aspects) │
│ - Gap: -0.4 (unfair penalty for disabled sub-aspect)               │
└──────────────────────────────────────────────────────────────────────┘
```

**Critical Test Questions:**
- Does database `individual_rating` EVER change? (Answer: **NEVER**)
- Does **calculation logic** recalculate individual_rating when sub-aspects inactive? (Answer: **YES, for fair comparison**)
- Are rankings using correct logic based on context?
- Is the recalculation **ephemeral** (not persisted to database)?

### 3. Active/Inactive Logic

**Concept:**
```
User can mark aspects/sub-aspects as inactive:
- Inactive aspects: Excluded from ranking calculations entirely
- Inactive sub-aspects: Triggers RECALCULATION of both standard AND individual ratings

CRITICAL DISTINCTION:
├─ Database individual_rating: NEVER CHANGES (immutable)
├─ Calculation logic: RECALCULATES individual_rating (ephemeral, for fair comparison)
└─ Both standard and individual: MUST use same active sub-aspects
```

**Critical Test Questions:**
- Does disabling a sub-aspect recalculate BOTH standard AND individual ratings?
- Are recalculated values **ephemeral** (not persisted)?
- Is comparison FAIR (same sub-aspects used for both)?

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

### Scenario Group 2: Individual Rating Immutability ⚠️ **UPDATED**

#### Test 2.1: Database Individual Rating NEVER Changes
```yaml
Given:
  - Participant "John Doe" has:
    - Aspect "Integritas" individual_rating: 4.5 (in database)
  - Quantum Default baseline selected

When:
  - User switches to Custom Standard
  - Custom Standard has different weight and standard_rating
  - User adjusts weights in session
  - User disables sub-aspects

Then:
  - Database individual_rating should ALWAYS be: 4.5 ✅
  - NO scenario should modify database value

SQL Verification:
  SELECT individual_rating
  FROM aspect_assessments
  WHERE participant_id = X AND aspect_id = Y
  -- Should ALWAYS be UNCHANGED

Critical: Database immutability is ABSOLUTE
```

#### Test 2.2: Calculation Logic Recalculates When Sub-Aspects Inactive ⚠️ **NEW TEST**
```yaml
Given:
  - Aspect "Sikap Kerja" has 5 sub-aspects
  - Participant individual_rating in DATABASE: 3.6
  - Participant sub-aspect ratings: [5, 4, 3, 2, 4]
  - User disables sub-aspect with rating 2

When:
  - Calculate rankings

Then:
  - Database individual_rating: STILL 3.6 ✅ (NEVER changes)
  - Calculation recalculates: (5+4+3+4)/4 = 4.0 ✅ (ephemeral)
  - Standard rating: (5+4+3+4)/4 = 4.0 ✅ (same basis)
  - Gap: 4.0 - 4.0 = 0.0 ✅ (FAIR comparison)

Critical:
  - Database: IMMUTABLE
  - Calculation: CONTEXT-DEPENDENT (recalculates for fairness)
  - Recalculated value: EPHEMERAL (not persisted)
```

#### Test 2.3: Weight Change Does NOT Alter Stored Data
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

---

## 🎚️ Active/Inactive Aspect Test Scenarios

### Scenario Group 3: Aspect Active/Inactive Logic ⚠️ **UPDATED**

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

#### Test 3.2: Inactive Sub-Aspect Triggers Fair Recalculation ⚠️ **UPDATED - DECISION MADE**
```yaml
Given:
  - Aspect "Sikap Kerja" has 5 sub-aspects
  - Participant stored individual_rating: 3.6 (from [5,4,3,2,4])
  - Standard stored rating from 5 sub-aspects: 3.6
  - User disables sub-aspect with rating 2

When:
  - Calculate rankings

Then:
  - Database individual_rating: 3.6 ✅ (IMMUTABLE - never changes)
  - Calculation recalculates:
    - Individual: (5+4+3+4)/4 = 4.0 ✅ (from 4 active sub-aspects)
    - Standard: (5+4+3+4)/4 = 4.0 ✅ (from 4 active sub-aspects)
    - Gap: 0.0 ✅ (FAIR - same basis)

  ❌ WRONG BEHAVIOR (Would be UNFAIR):
    - Individual: 3.6 (from 5 sub-aspects)
    - Standard: 4.0 (from 4 sub-aspects)
    - Gap: -0.4 (UNFAIR - different basis)

DECISION: Recalculate BOTH for fair comparison
REASON: Apple-to-apple comparison requires same sub-aspects

Services to Test:
  - RankingService.getRankings()
  - IndividualAssessmentService.getAspectAssessments()
  - StatisticService.calculateAverageRating()
```

#### Test 3.3: All Sub-Aspects Inactive (Edge Case)
```yaml
Given:
  - Aspect "Sikap Kerja" has 5 sub-aspects
  - User disables ALL 5 sub-aspects

When:
  - Calculate aspect rating

Then:
  - Aspect should be marked as INACTIVE
  - Aspect should be excluded from calculations
  - OR: Use aspect's direct rating (if exists in database)

Expected Behavior:
  OPTION A: Mark entire aspect as inactive ✅ RECOMMENDED
  OPTION B: Use aspect's direct rating (if exists)
  OPTION C: Return 0 (NOT recommended - breaks calculations)

Critical: Document and test actual behavior
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

#### Test 3.5: Sub-Aspect Recalculation Impact on Statistics ⭐ **NEW TEST**
```yaml
Given:
  - 100 participants with Aspect "Sikap Kerja"
  - All have 5 sub-aspects with varying ratings
  - User disables 1 sub-aspect

When:
  - Calculate statistics (average, distribution)

Then:
  - All participants' individual_ratings recalculated from active sub-aspects
  - Statistics calculated from recalculated values
  - Distribution buckets reflect adjusted ratings
  - Average reflects adjusted ratings

Critical:
  - Statistics MUST use recalculated values
  - NOT use stored database values (would be unfair)
```

---

## 🧮 Calculation Accuracy Test Scenarios

### Scenario Group 4: Mathematical Correctness ⚠️ **UPDATED**

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

#### Test 4.2: Standard Rating Calculation (With Sub-Aspects, All Active)
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

#### Test 4.4: Individual Rating Recalculation with Inactive Sub-Aspect ⭐ **NEW TEST**
```yaml
Given:
  - Participant has sub-aspect ratings: [5, 4, 3, 2, 4]
  - Stored individual_rating in database: 3.6
  - User disables sub-aspect with rating 2

When:
  - Calculate ranking (ephemeral calculation, NOT database update)

Then:
  - Database: STILL 3.6 ✅ (immutable)
  - Calculation uses: (5 + 4 + 3 + 4) / 4 = 4.0 ✅ (recalculated)
  - Standard rating: 4.0 ✅ (same basis)
  - Comparison: FAIR ✅

Critical:
  - Verify database NOT modified
  - Verify calculation uses recalculated value
  - Verify standard uses same active sub-aspects
```

#### Test 4.5: Individual Score Calculation
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

#### Test 4.6: Total Score Calculation (Combined)
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

#### Test 4.7: Tolerance Adjustment
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
    - individual_rating (ephemeral, recalculated if sub-aspects inactive)
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

## 🖥️ Livewire Component Integration Tests ⭐ **NEW SECTION**

### Scenario Group 9: Livewire Component Behavior

#### Test 9.1: Baseline Dropdown Selection (Quantum Default)
```yaml
Component: StandardPsikometrik.php / StandardMc.php

Given:
  - User viewing standard page
  - Dropdown shows:
    - "Quantum Default (Standar Umum)" (selected by default)
    - "Custom Standard: Kejaksaan 2025"
    - "Custom Standard: Polri 2025"

When:
  - Component mounts

Then:
  - selectedCustomStandardId should be: null
  - DynamicStandardService uses Layer 3 (Quantum Default)
  - No "Standar Disesuaikan" label shown
  - loadStandardData() uses Quantum Default values

Livewire Assertions:
  - assertSet('selectedCustomStandardId', null)
  - assertDontSee('Standar Disesuaikan')
```

#### Test 9.2: Baseline Dropdown Selection (Custom Standard)
```yaml
Component: StandardPsikometrik.php / StandardMc.php

Given:
  - User viewing standard page
  - Dropdown shows available custom standards

When:
  - User selects "Custom Standard: Kejaksaan 2025" (id: 1)
  - selectCustomStandard(1) called

Then:
  - CustomStandardService::select(templateId, 1) called
  - Session adjustments cleared automatically
  - selectedCustomStandardId = 1
  - Event 'standard-switched' dispatched with templateId
  - Cache cleared
  - loadStandardData() reloads with Custom Standard values
  - UI updates to show Custom Standard name

Livewire Assertions:
  - assertSet('selectedCustomStandardId', 1)
  - assertDispatched('standard-switched')
  - assertDontSee('Standar Disesuaikan') (until user adjusts)
```

#### Test 9.3: Switch from Custom Standard to Quantum Default
```yaml
Component: StandardPsikometrik.php / StandardMc.php

Given:
  - Custom Standard "Kejaksaan 2025" currently selected
  - User has session adjustments active

When:
  - User selects "Quantum Default" from dropdown
  - selectCustomStandard(null) called

Then:
  - CustomStandardService::select(templateId, null) called
  - Session adjustments cleared
  - selectedCustomStandardId = null
  - Event 'standard-switched' dispatched
  - DynamicStandardService falls back to Layer 3
  - UI updates to show "Quantum Default"

Livewire Assertions:
  - assertSet('selectedCustomStandardId', null)
  - assertDispatched('standard-switched')
```

#### Test 9.4: "Standar Disesuaikan" Label Appears After Session Adjustment
```yaml
Component: StandardPsikometrik.php / StandardMc.php

Given:
  - Quantum Default baseline active (selectedCustomStandardId = null)
  - No session adjustments
  - hasCategoryAdjustments() returns false

When:
  - User opens category weight modal
  - openEditCategoryWeight('potensi', 25) called
  - User changes value to 30
  - saveCategoryWeight() called

Then:
  - DynamicStandardService::saveCategoryWeight() called
  - Session adjustment stored (Layer 1)
  - Event 'standard-adjusted' dispatched
  - hasCategoryAdjustments() returns true
  - UI should show: "Standar Disesuaikan" badge/label

Livewire Assertions:
  - assertDispatched('standard-adjusted')
  - assertSee('Standar Disesuaikan')
```

#### Test 9.5: "Standar Disesuaikan" Label Disappears After Baseline Switch
```yaml
Component: StandardPsikometrik.php / StandardMc.php

Given:
  - Quantum Default active with session adjustments
  - hasCategoryAdjustments() returns true
  - UI showing "Standar Disesuaikan"

When:
  - User selects Custom Standard "Kejaksaan 2025"
  - selectCustomStandard(1) called

Then:
  - CustomStandardService::select() clears session adjustments
  - hasCategoryAdjustments() returns false
  - "Standar Disesuaikan" label DISAPPEARS
  - UI shows "Custom Standard: Kejaksaan 2025" instead

Livewire Assertions:
  - assertSet('selectedCustomStandardId', 1)
  - assertDontSee('Standar Disesuaikan')
  - assertSee('Custom Standard: Kejaksaan 2025')
```

#### Test 9.6: Category Weight Edit Modal Flow
```yaml
Component: StandardPsikometrik.php / StandardMc.php

Given:
  - User viewing standard page
  - Category "Potensi" has weight 25%

When:
  - User clicks edit icon next to category weight
  - openEditCategoryWeight('potensi', 25) called

Then:
  - Modal opens
  - showEditCategoryWeightModal = true
  - editingField = 'potensi'
  - editingValue = 25
  - editingOriginalValue = 25 (from database)

When:
  - User changes value to 30
  - User clicks save
  - saveCategoryWeight() called

Then:
  - DynamicStandardService::saveCategoryWeight(templateId, 'potensi', 30)
  - Modal closes
  - Event 'standard-adjusted' dispatched
  - Cache cleared
  - Data reloaded

Livewire Assertions:
  - assertSet('showEditCategoryWeightModal', true)
  - assertSet('editingField', 'potensi')
  - assertSet('editingValue', 25)
  - call('saveCategoryWeight')
  - assertSet('showEditCategoryWeightModal', false)
  - assertDispatched('standard-adjusted')
```

#### Test 9.7: Sub-Aspect Rating Edit Modal Flow (Potensi)
```yaml
Component: StandardPsikometrik.php

Given:
  - User viewing Potensi standard page
  - Sub-aspect "Daya Analisa" has rating 3

When:
  - User clicks edit icon next to sub-aspect rating
  - openEditSubAspectRating('daya-analisa', 3) called

Then:
  - Modal opens
  - showEditRatingModal = true
  - editingField = 'daya-analisa'
  - editingValue = 3
  - editingOriginalValue = 3 (from database)

When:
  - User changes value to 4
  - User clicks save
  - saveSubAspectRating() called

Then:
  - DynamicStandardService::saveSubAspectRating(templateId, 'daya-analisa', 4)
  - Modal closes
  - Event 'standard-adjusted' dispatched
  - Cache cleared
  - Data reloaded
  - Aspect rating recalculated from sub-aspects

Livewire Assertions:
  - assertSet('showEditRatingModal', true)
  - assertSet('editingField', 'daya-analisa')
  - call('saveSubAspectRating')
  - assertSet('showEditRatingModal', false)
  - assertDispatched('standard-adjusted')
```

#### Test 9.8: Aspect Rating Edit Modal Flow (Kompetensi)
```yaml
Component: StandardMc.php

Given:
  - User viewing Kompetensi standard page
  - Aspect "Integritas" has rating 4

When:
  - User clicks edit icon next to aspect rating
  - openEditAspectRating('integritas', 4) called

Then:
  - Modal opens
  - showEditRatingModal = true
  - editingField = 'integritas'
  - editingValue = 4
  - editingOriginalValue = 4 (from database)

When:
  - User changes value to 5
  - User clicks save
  - saveAspectRating() called

Then:
  - DynamicStandardService::saveAspectRating(templateId, 'integritas', 5)
  - Modal closes
  - Event 'standard-adjusted' dispatched
  - Cache cleared
  - Data reloaded

Livewire Assertions:
  - assertSet('showEditRatingModal', true)
  - call('saveAspectRating')
  - assertDispatched('standard-adjusted')
```

#### Test 9.9: Rating Validation (Must be 1-5)
```yaml
Component: StandardPsikometrik.php / StandardMc.php

Given:
  - Rating edit modal open
  - editingValue initially 3

When:
  - User enters invalid values:
    - 0 (too low)
    - 6 (too high)
    - -1 (negative)

Then:
  - Validation error shown
  - addError('editingValue', 'Rating harus antara 1 sampai 5.')
  - Modal stays open
  - Save operation blocked

Livewire Assertions:
  - set('editingValue', 0)
  - call('saveSubAspectRating')
  - assertHasErrors(['editingValue'])
  - assertSet('showEditRatingModal', true) (modal still open)
```

#### Test 9.10: Reset Adjustments Button
```yaml
Component: StandardPsikometrik.php / StandardMc.php

Given:
  - User has active session adjustments
  - hasCategoryAdjustments() returns true
  - UI showing "Standar Disesuaikan"

When:
  - User clicks "Reset Adjustments" button
  - resetAdjustments() called

Then:
  - DynamicStandardService::resetCategoryAdjustments() called
  - DynamicStandardService::resetCategoryWeights() called
  - Event 'standard-adjusted' dispatched
  - hasCategoryAdjustments() returns false
  - "Standar Disesuaikan" label disappears
  - Values return to baseline (Quantum or Custom)

Livewire Assertions:
  - call('resetAdjustments')
  - assertDispatched('standard-adjusted')
  - assertDontSee('Standar Disesuaikan')
```

#### Test 9.11: Modal Close Without Save
```yaml
Component: StandardPsikometrik.php / StandardMc.php

Given:
  - Weight edit modal open
  - editingValue changed from 25 to 30 (unsaved)

When:
  - User clicks close/cancel
  - closeModal() called

Then:
  - Modal closes
  - showEditCategoryWeightModal = false
  - showEditRatingModal = false
  - editingField = ''
  - editingValue = null
  - Error bag cleared
  - NO changes saved to session
  - NO events dispatched

Livewire Assertions:
  - call('closeModal')
  - assertSet('showEditCategoryWeightModal', false)
  - assertSet('editingValue', null)
```

#### Test 9.12: Component Cache Management
```yaml
Component: StandardPsikometrik.php / StandardMc.php

Given:
  - Component loaded with data
  - categoryDataCache populated

When:
  - User triggers any action that changes config:
    - selectCustomStandard()
    - saveCategoryWeight()
    - saveAspectRating()
    - resetAdjustments()

Then:
  - clearCache() called
  - categoryDataCache = null
  - chartDataCache = null
  - totalsCache = null
  - maxScoreCache = null
  - loadStandardData() recalculates

Critical: Cache cleared before data reload
```

---

## 🔄 Baseline Switching Tests ⭐ **NEW SECTION**

### Scenario Group 10: Baseline Switching Edge Cases

#### Test 10.1: Rapid Baseline Switching
```yaml
Given:
  - User on standard page

When:
  - User rapidly switches baselines:
    1. Quantum Default (null)
    2. Custom Standard "Kejaksaan 2025" (id: 1)
    3. Custom Standard "Polri 2025" (id: 2)
    4. Quantum Default (null)
    5. Custom Standard "Kejaksaan 2025" (id: 1)
  - All within 5 seconds

Then:
  - Each switch:
    - Clears session adjustments
    - Updates selectedCustomStandardId
    - Dispatches 'standard-switched'
    - Clears cache
    - Reloads data
  - No race conditions
  - Final state: Custom Standard "Kejaksaan 2025"
  - No stale data displayed
  - No JavaScript errors

Critical: Test concurrency handling
```

#### Test 10.2: Null/Empty String Custom Standard ID Handling
```yaml
Given:
  - Custom Standard "Kejaksaan 2025" selected
  - selectedCustomStandardId = 1

When:
  - User selects "Quantum Default" from dropdown
  - Dropdown may send various null representations:
    - selectCustomStandard(null)
    - selectCustomStandard('null') (string)
    - selectCustomStandard('') (empty string)

Then:
  - All should be handled correctly
  - selectedCustomStandardId = null
  - Session cleared
  - DynamicStandardService falls back to Layer 3
  - No errors thrown

Code Reference: StandardPsikometrik.php:302-304
```

#### Test 10.3: Switch Baseline During Modal Open
```yaml
Given:
  - User editing category weight in modal
  - showEditCategoryWeightModal = true
  - editingValue changed but not saved

When:
  - User switches baseline (e.g., via keyboard shortcut or other UI)
  - selectCustomStandard() called

Then:
  - Modal should stay open (debatable - document decision)
  OR
  - Modal should close automatically
  - Unsaved changes discarded
  - Baseline switch completes successfully

Critical: Document expected UX behavior
```

#### Test 10.4: Switch from Custom Standard with Session Adjustments
```yaml
Given:
  - Custom Standard "Kejaksaan 2025" selected
  - User has session adjustments:
    - Integritas weight: 20% (Custom) → 25% (Session)
    - Kepemimpinan rating: 4 (Custom) → 5 (Session)

When:
  - User switches to Custom Standard "Polri 2025"
  - selectCustomStandard(2) called

Then:
  - Old session adjustments cleared
  - New Custom Standard "Polri 2025" loaded
  - NO session adjustments active
  - hasCategoryAdjustments() = false
  - Values from new Custom Standard only

Critical: Old adjustments must NOT carry over
```

---

## 📡 Event Communication Tests ⭐ **NEW SECTION**

### Scenario Group 11: Livewire Event Dispatching & Listening

#### Test 11.1: 'standard-switched' Event Propagation
```yaml
Given:
  - Multiple components on same page:
    - StandardPsikometrik
    - StandardMc
    - RekapRankingAssessment (if on same page)
  - All listening for 'standard-switched'

When:
  - StandardPsikometrik dispatches 'standard-switched' with templateId: 4
  - selectCustomStandard(1) called

Then:
  - Event received by:
    - StandardPsikometrik::handleStandardSwitch(4)
    - StandardMc::handleStandardSwitch(4)
    - Other listening components
  - Each component:
    - Checks if templateId matches their template
    - If match: clearCache() + loadStandardData()
    - If no match: ignores event

Livewire Assertions:
  - assertDispatched('standard-switched', templateId: 4)
```

#### Test 11.2: 'standard-adjusted' Event Propagation
```yaml
Given:
  - User on StandardPsikometrik page
  - Adjusts category weight

When:
  - saveCategoryWeight() called
  - Event 'standard-adjusted' dispatched with templateId: 4

Then:
  - StandardPsikometrik::handleStandardUpdate(4) called
  - Component:
    - Checks if templateId matches (4 === 4)
    - clearCache()
    - loadStandardData()
    - Re-dispatches 'chartDataUpdated' for charts

Livewire Assertions:
  - call('saveCategoryWeight')
  - assertDispatched('standard-adjusted', templateId: 4)
  - assertDispatched('chartDataUpdated')
```

#### Test 11.3: 'event-selected' Listener
```yaml
Given:
  - StandardPsikometrik component mounted
  - Listening for 'event-selected'

When:
  - EventSelector dispatches 'event-selected' with eventCode: 'P3K-2025'

Then:
  - handleEventSelected('P3K-2025') called
  - clearCache() called
  - Wait for position to be selected (position auto-resets)
  - Data NOT reloaded yet (waits for position)

Livewire Assertions:
  - emit('event-selected', 'P3K-2025')
  - assertMethodWasCalled('handleEventSelected')
```

#### Test 11.4: 'position-selected' Listener
```yaml
Given:
  - Event already selected
  - StandardPsikometrik listening for 'position-selected'

When:
  - PositionSelector dispatches 'position-selected' with positionFormationId: 1

Then:
  - handlePositionSelected(1) called
  - clearCache()
  - loadStandardData() (now both event and position known)
  - loadAvailableCustomStandards()
  - Dispatch 'chartDataUpdated' with chart data

Livewire Assertions:
  - emit('position-selected', 1)
  - assertDispatched('chartDataUpdated')
```

#### Test 11.5: Multiple Events in Sequence
```yaml
Given:
  - StandardPsikometrik component

When:
  - Sequence of events:
    1. 'event-selected' → 'P3K-2025'
    2. 'position-selected' → 1
    3. 'standard-switched' → (Custom Standard)
    4. 'standard-adjusted' → (Session adjustment)

Then:
  - Each event processed correctly
  - Cache cleared at appropriate times
  - Data reloaded when necessary
  - Final state reflects all changes

Critical: Test event ordering and state consistency
```

---

## 🔐 Cache Key Completeness Tests ⭐ **NEW SECTION**

### Scenario Group 12: Cache Key Configuration

#### Test 12.1: Sub-Aspect Active Status in Cache Key
```yaml
Given:
  - Aspect "Sikap Kerja" has 5 sub-aspects
  - All active
  - Rankings cached

When:
  - User disables 1 sub-aspect
  - Page reloads

Then:
  - Config hash MUST change
  - Cache key MUST be different
  - Cache MUST miss
  - Rankings recalculated with new logic
  - NOT return old cached rankings

Verification:
  - Compare config hash before and after
  - Verify cache key includes sub-aspect active status
  - Verify database query executed (not cache hit)

Critical: Sub-aspect status must affect cache key
```

#### Test 12.2: Aspect Active Status in Cache Key
```yaml
Given:
  - All aspects active
  - Rankings cached

When:
  - User disables "Integritas" aspect
  - Page reloads

Then:
  - Config hash MUST change
  - Cache key MUST be different
  - Cache MUST miss
  - Rankings recalculated without Integritas
  - NOT return old cached rankings

Verification:
  - activeAspectIds different
  - aspectWeightsForHash different (Integritas missing)
  - configHash different
  - Cache miss confirmed
```

#### Test 12.3: Session ID Isolation in Cache Key
```yaml
Given:
  - User A makes session adjustments
  - Rankings cached for User A

When:
  - User B loads same page
  - Same event, position, template
  - NO session adjustments (using baseline)

Then:
  - Config hash includes session ID
  - User A cache key ≠ User B cache key
  - User B does NOT see User A's adjusted rankings
  - Each user has isolated cache

Verification:
  - session()->getId() different for each user
  - Cache keys different
  - Rankings different (User A: adjusted, User B: baseline)
```

#### Test 12.4: Custom Standard Selection in Cache Key
```yaml
Given:
  - User A selects Custom Standard "Kejaksaan 2025"
  - Rankings cached for User A

When:
  - User B selects Custom Standard "Polri 2025"
  - Same event, position, template

Then:
  - Config hash different (different aspect weights from different custom standards)
  - Cache keys different
  - User A sees "Kejaksaan 2025" rankings
  - User B sees "Polri 2025" rankings
  - No cache collision

Verification:
  - aspectWeightsForHash different
  - configHash different
  - Cache isolation confirmed
```

#### Test 12.5: Category Weight in Cache Key
```yaml
Given:
  - Rankings cached with Potensi: 25%, Kompetensi: 75%

When:
  - User adjusts Potensi: 30%, Kompetensi: 70%
  - Page reloads

Then:
  - Config hash MUST change
  - Cache MUST miss
  - Rankings recalculated with new category weights

Verification:
  - Category weight changes affect aspect weights
  - aspectWeightsForHash different
  - configHash different
  - Cache miss
```

#### Test 12.6: Tolerance NOT in Cache Key
```yaml
Given:
  - Rankings cached with tolerance 10%

When:
  - User changes tolerance to 15%
  - Page reloads

Then:
  - Config hash MUST be SAME
  - Cache MUST hit
  - Tolerance applied AFTER cache retrieval
  - No database query

Verification:
  - configHash unchanged
  - Cache hit confirmed
  - Only tolerance calculation different
  - No SQL queries for rankings

Critical: Tolerance for instant UX, not in cache key
```

---

## 📊 Test Coverage Matrix ⚠️ **UPDATED**

### Required Coverage by Component Type

| Component/Service | Layer Priority | Immutability | Active/Inactive | Cache | Calculation | Edge Cases | Livewire |
|-------------------|---------------|--------------|-----------------|-------|-------------|------------|----------|
| **DynamicStandardService** | ✅ CRITICAL | ✅ N/A | ✅ CRITICAL | ❌ N/A | ✅ High | ✅ High | ❌ N/A |
| **RankingService** | ✅ CRITICAL | ✅ CRITICAL | ✅ CRITICAL | ✅ High | ✅ High | ✅ Medium | ❌ N/A |
| **StatisticService** | ✅ CRITICAL | ✅ CRITICAL | ✅ CRITICAL | ✅ High | ✅ High | ✅ Medium | ❌ N/A |
| **TrainingRecommendationService** | ✅ High | ✅ High | ✅ CRITICAL | ✅ High | ✅ High | ✅ Medium | ❌ N/A |
| **IndividualAssessmentService** | ✅ High | ✅ CRITICAL | ✅ CRITICAL | ✅ Medium | ✅ High | ✅ Medium | ❌ N/A |
| **ConclusionService** | ❌ N/A | ❌ N/A | ❌ N/A | ❌ N/A | ✅ CRITICAL | ✅ High | ❌ N/A |
| **StandardPsikometrik** | ✅ High | ✅ Medium | ✅ High | ✅ Medium | ✅ Medium | ✅ Medium | ✅ CRITICAL |
| **StandardMc** | ✅ High | ✅ Medium | ✅ High | ✅ Medium | ✅ Medium | ✅ Medium | ✅ CRITICAL |

**Legend:**
- ✅ CRITICAL: Must have 100% test coverage
- ✅ High: Should have >80% test coverage
- ✅ Medium: Should have >60% test coverage
- ❌ N/A: Not applicable to this component/service

---

## 🐛 Known Bug Potential Areas (Require Investigation) ⚠️ **UPDATED**

### Area 1: Individual Rating Recalculation Logic ⚠️ **UPDATED - DECISION MADE**

**Issue:**
```
DECISION: Individual rating MUST be recalculated when sub-aspects are disabled
for FAIR comparison with adjusted standard.

Implementation Required:
- Database individual_rating: IMMUTABLE (never changes)
- Calculation logic: Context-dependent recalculation
- Recalculated value: Ephemeral (not persisted)

VERIFICATION NEEDED:
- All services (Ranking, Statistic, Individual, Training) use recalculation
- Recalculation is ephemeral (not persisted to database)
- Standard and individual use SAME active sub-aspects
```

**Test to Write:**
```yaml
Test: Sub-Aspect Disable Triggers Recalculation
Given:
  - Aspect with 5 sub-aspects: [5,4,3,2,4]
  - Stored individual_rating: 3.6
  - Disable sub-aspect with rating 2

When:
  - Calculate rankings via RankingService
  - Calculate stats via StatisticService
  - View individual report via IndividualAssessmentService

Then:
  - Database: individual_rating still 3.6 ✅
  - All services calculate: (5+4+3+4)/4 = 4.0 ✅
  - Standard rating: 4.0 ✅
  - Gap: 0.0 ✅ (FAIR)

Critical:
  - Verify NO database UPDATE query
  - Verify all services use recalculated value
  - Verify recalculation is consistent across services
```

### Area 2: Cache Key Completeness ⚠️ **ADDRESSED**

**Status:** ✅ NOW COVERED by Scenario Group 12

Tests added:
- Test 12.1: Sub-aspect active status in cache key
- Test 12.2: Aspect active status in cache key
- Test 12.3: Session ID isolation
- Test 12.4: Custom standard selection
- Test 12.5: Category weight changes
- Test 12.6: Tolerance NOT in cache key

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

### Area 4: Livewire Component State Consistency ⭐ **NEW AREA**

**Issue:**
```
Livewire components maintain state across requests.
Potential issues:
- Stale cache after baseline switch
- Modal state not cleared properly
- Event listeners not firing
- Session adjustments not reflecting in UI

NEEDS VERIFICATION:
- Cache cleared at correct times
- Modals close/reset properly
- Events propagate correctly
- UI reflects session state accurately
```

**Test to Write:**
```yaml
Test: Component State After Baseline Switch
Given:
  - StandardPsikometrik with Custom Standard selected
  - Component cache populated
  - Modal open with unsaved changes

When:
  - User switches to different Custom Standard

Then:
  - Component cache cleared
  - Data reloaded
  - Modal state documented behavior (close or stay open)
  - UI reflects new baseline
  - No stale data displayed
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
7. ✅ Test Livewire component state management ⭐ **NEW**
8. ✅ Test event communication between components ⭐ **NEW**
9. ✅ Verify database immutability (no UPDATE queries) ⭐ **NEW**

**DON'T:**
1. ❌ Assume current code is correct
2. ❌ Skip edge cases because "it should work"
3. ❌ Test only happy paths
4. ❌ Ignore performance in tests
5. ❌ Write tests that confirm bugs
6. ❌ Test UI without testing underlying logic
7. ❌ Mock services when integration test needed

### Test Naming Convention

```php
// ✅ GOOD: Describes scenario and expected outcome
test_layer1_session_adjustment_overrides_custom_standard()
test_inactive_subaspect_excluded_from_standard_rating_calculation()
test_cache_invalidates_when_baseline_switched()
test_livewire_shows_standar_disesuaikan_label_after_adjustment()
test_individual_rating_recalculated_when_subaspect_disabled()

// ❌ BAD: Describes implementation
test_getRankings_returns_collection()
test_calculateAverageRating_uses_db_query()
test_component_has_property()
```

### Livewire-Specific Testing Tips ⭐ **NEW**

```php
// Test component state
Livewire::test(StandardPsikometrik::class)
    ->assertSet('selectedCustomStandardId', null)
    ->assertSee('Quantum Default');

// Test component methods
Livewire::test(StandardPsikometrik::class)
    ->call('selectCustomStandard', 1)
    ->assertSet('selectedCustomStandardId', 1)
    ->assertDispatched('standard-switched');

// Test event listening
Livewire::test(StandardPsikometrik::class)
    ->emit('event-selected', 'P3K-2025')
    ->assertMethodWasCalled('handleEventSelected');

// Test modal interactions
Livewire::test(StandardPsikometrik::class)
    ->call('openEditCategoryWeight', 'potensi', 25)
    ->assertSet('showEditCategoryWeightModal', true)
    ->set('editingValue', 30)
    ->call('saveCategoryWeight')
    ->assertSet('showEditCategoryWeightModal', false)
    ->assertDispatched('standard-adjusted');
```

---

## 📚 Related Documentation

- [SPSP_BUSINESS_CONCEPTS.md](./SPSP_BUSINESS_CONCEPTS.md) - Core business logic
- [ARCHITECTURE_DECISION_RECORDS.md](./ARCHITECTURE_DECISION_RECORDS.md) - Architecture decisions
- [TESTING_GUIDE.md](./TESTING_GUIDE.md) - How to write tests
- [LIVEWIRE_TESTING_GUIDE.md](./LIVEWIRE_TESTING_GUIDE.md) - Livewire-specific testing

---

## 📝 Summary of Changes (December 2025 Update)

### Major Additions:
1. ⭐ **Scenario Group 9**: Livewire Component Integration Tests (12 tests)
2. ⭐ **Scenario Group 10**: Baseline Switching Tests (4 tests)
3. ⭐ **Scenario Group 11**: Event Communication Tests (5 tests)
4. ⭐ **Scenario Group 12**: Cache Key Completeness Tests (6 tests)

### Major Clarifications:
1. ⚠️ **Data Immutability Principle**: Updated to context-dependent logic
   - Database: ALWAYS immutable
   - Calculation: Context-dependent recalculation for fairness
2. ⚠️ **Active/Inactive Logic**: Clarified recalculation requirement
   - MUST recalculate both standard AND individual for fair comparison
3. ⚠️ **Test Coverage Matrix**: Added Livewire components
4. ⚠️ **Known Bug Areas**: Updated with decision for Area 1

### Total Test Scenarios:
- **Before Update**: ~50 test scenarios
- **After Update**: ~77 test scenarios (+27 tests)
- **New Coverage**: Livewire integration, baseline switching, events, cache keys

---

**Last Updated:** December 2025
**Purpose:** Comprehensive Test Scenario Definition for Service & Livewire Layer Testing
**Status:** ✅ Ready for Test Implementation (Updated & Expanded)
**Maintainer:** SPSP Development Team
