# SPSP System Flow Diagrams

Quick visual reference untuk memahami alur sistem SPSP.

---

## 1. Overall System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         USER INTERFACE                          │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│  │ EventSelector│  │PositionSelect│  │ToleranceSelect│         │
│  └──────────────┘  └──────────────┘  └──────────────┘         │
│                                                                  │
│  ┌────────────────────────────────────────────────────────┐    │
│  │          StandardPsikometrik / StandardMc              │    │
│  │  (Baseline Selection & Session Adjustment)             │    │
│  └────────────────────────────────────────────────────────┘    │
│                            ↓                                     │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│  │RekapRanking  │  │RankingPsy    │  │RankingMc     │         │
│  │Assessment    │  │Mapping       │  │Mapping       │         │
│  └──────────────┘  └──────────────┘  └──────────────┘         │
│                            ↓                                     │
│  ┌────────────────────────────────────────────────────────┐    │
│  │          GeneralMapping (Individual Report)            │    │
│  └────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│                      SERVICE LAYER                              │
│  ┌──────────────────┐  ┌──────────────────────────────────┐   │
│  │ RankingService   │  │ IndividualAssessmentService      │   │
│  │ (Bulk ranking)   │  │ (Single participant detail)      │   │
│  └──────────────────┘  └──────────────────────────────────┘   │
│             ↓                          ↓                        │
│  ┌─────────────────────────────────────────────────────────┐  │
│  │         DynamicStandardService                          │  │
│  │         (3-Layer Priority Resolution)                   │  │
│  └─────────────────────────────────────────────────────────┘  │
│             ↓                                                   │
│  ┌──────────────────┐  ┌──────────────────┐                   │
│  │ConclusionService │  │AspectCacheService│                   │
│  └──────────────────┘  └──────────────────┘                   │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│                      DATA LAYER                                 │
│  ┌──────────────────┐  ┌──────────────────┐                   │
│  │ aspect_          │  │ sub_aspect_      │                   │
│  │ assessments      │  │ assessments      │                   │
│  │ (Individual data)│  │ (Breakdown)      │                   │
│  └──────────────────┘  └──────────────────┘                   │
│  ┌──────────────────┐  ┌──────────────────┐                   │
│  │ aspects          │  │ custom_standards │                   │
│  │ (Quantum Default)│  │ (Institution)    │                   │
│  └──────────────────┘  └──────────────────┘                   │
│  ┌──────────────────┐                                          │
│  │ Session          │                                          │
│  │ (Adjustments)    │                                          │
│  └──────────────────┘                                          │
└─────────────────────────────────────────────────────────────────┘
```

---

## 2. Ranking Calculation Flow

```
┌─────────────────────────────────────────────────────────────────┐
│  USER ACTION: View RekapRankingAssessment                       │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  RankingService::getRankings(eventId, positionId, templateId,   │
│                              categoryCode, tolerance)            │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 1: Get Active Aspect IDs                                  │
│  ├─ DynamicStandardService::getActiveAspectIds()                │
│  └─ Returns: [40, 42, 43, 41] (for Potensi)                     │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 2: Build Config Hash (for cache invalidation)             │
│  ├─ For each aspect: getAspectWeight() ← 3-layer check          │
│  ├─ Hash includes: weights, session ID                          │
│  └─ configHash = md5([aspect_weights, session_id])              │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 3: Check Cache                                            │
│  cacheKey = "rankings:potensi:1:1:4:{configHash}"               │
│                                                                  │
│  ┌──────────────────┐         ┌──────────────────┐             │
│  │  Cache HIT       │         │  Cache MISS      │             │
│  │  Return cached   │         │  Calculate new   │             │
│  │  (701ms)         │         │  (1,540ms)       │             │
│  └──────────────────┘         └──────────────────┘             │
└─────────────────────────────────────────────────────────────────┘
                            ↓ (Cache MISS)
┌─────────────────────────────────────────────────────────────────┐
│  STEP 4: Pre-Compute Standards (5-10 calls, not 100K!)          │
│  ├─ precomputeStandards(templateId, activeAspectIds)            │
│  ├─ For each aspect:                                            │
│  │   ├─ getAspectWeight() ← 3-layer priority                   │
│  │   ├─ getAspectRating() ← 3-layer priority                   │
│  │   ├─ isAspectActive() ← 3-layer priority                    │
│  │   └─ For each sub-aspect: rating & active                   │
│  └─ Store in $standardsCache array (request-scoped)            │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 5: Query Assessment Data (LIGHTWEIGHT!)                   │
│  ├─ Query: AspectAssessment::query()                            │
│  │         ->join('participants')                               │
│  │         ->whereIn('aspect_id', [40,42,43,41])                │
│  │         ->select('id','participant_id','aspect_id',          │
│  │                  'individual_rating','participant_name')     │
│  │         ->toBase()->get()  ← NO MODEL HYDRATION              │
│  │                                                               │
│  ├─ Result: 19,620 stdClass objects (4,905 × 4 aspects)         │
│  ├─ Time: ~109ms for Potensi, ~212ms for Kompetensi            │
│  └─ NO sub_aspect_assessments query! (Saved 846ms + 710ms)     │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 6: Calculate Participant Scores                           │
│  foreach ($assessments as $assessment) {                        │
│    ├─ Get aspectCode from aspectIdToCode lookup                │
│    ├─ Get weight from $standardsCache (pre-computed)           │
│    ├─ Get individualRating from DB (pre-calculated)            │
│    ├─ Calculate: score = rating × weight                       │
│    └─ Accumulate per participant                               │
│  }                                                               │
│                                                                  │
│  Output: [                                                       │
│    18576 => [name: "WINDA", rating: 16.9, score: 422.5],       │
│    6736 => [name: "ALMIRA", rating: 16.7, score: 417.5],       │
│    ...                                                           │
│  ]                                                               │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 7: Calculate Adjusted Standards                           │
│  ├─ Use $standardsCache (already computed)                      │
│  ├─ For each active aspect:                                     │
│  │   ├─ rating × weight = aspect_score                         │
│  │   └─ Sum all aspect_scores = standard_score                 │
│  └─ Store: original_standard_rating, original_standard_score    │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 8: Sort & Cache                                           │
│  ├─ Sort by: score DESC, name ASC                              │
│  ├─ Cache::put(cacheKey, [rankings, standards], 60)            │
│  └─ Return to caller                                            │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 9: Apply Tolerance (After Cache!)                         │
│  ├─ toleranceFactor = 1 - (tolerance / 100)                    │
│  ├─ adjustedStandard = originalStandard × toleranceFactor      │
│  ├─ gap = individual - adjustedStandard                        │
│  └─ conclusion = ConclusionService::getConclusion(gap)         │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  OUTPUT: Collection of Rankings                                 │
│  [                                                               │
│    {rank: 1, participant_id: 18576, name: "WINDA",             │
│     individual_score: 422.5, standard_score: 333.75,           │
│     gap: 88.75, conclusion: "Di Atas Standar"},                │
│    {rank: 2, ...},                                              │
│    ...                                                           │
│  ]                                                               │
└─────────────────────────────────────────────────────────────────┘
```

---

## 3. 3-Layer Priority Resolution

```
┌─────────────────────────────────────────────────────────────────┐
│  CALL: DynamicStandardService::getAspectWeight($templateId,    │
│                                                 'integritas')    │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  LAYER 1: Check Session Adjustment                              │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ $adjustments = Session::get("standard_adjustment.4")    │  │
│  │ if (isset($adjustments['aspect_weights']['integritas'])) │  │
│  │     return $adjustments['aspect_weights']['integritas']; │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                  │
│  IF FOUND: return 18  ← User adjusted 15% → 18%                │
│  IF NOT FOUND: ↓ Continue to Layer 2                           │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  LAYER 2: Check Custom Standard                                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ $customStdId = Session::get("selected_standard.4")      │  │
│  │ if ($customStdId) {                                      │  │
│  │     $custom = CustomStandard::find($customStdId);       │  │
│  │     if ($custom->aspect_configs['integritas']['weight'])│  │
│  │         return $custom->aspect_configs[...]['weight'];  │  │
│  │ }                                                        │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                  │
│  IF FOUND: return 15  ← Custom Standard "Kejaksaan 2025"       │
│  IF NOT FOUND: ↓ Continue to Layer 3                           │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  LAYER 3: Fallback to Quantum Default                           │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ $aspect = Aspect::where('template_id', 4)               │  │
│  │                 ->where('code', 'integritas')            │  │
│  │                 ->first();                               │  │
│  │ return $aspect->weight_percentage;                       │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                  │
│  ALWAYS FOUND: return 10  ← System default                     │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  RETURN: Final Weight Value                                     │
│  ├─ Used in ranking calculation                                │
│  ├─ Used in config hash for cache invalidation                 │
│  └─ Respects user's exploration context                        │
└─────────────────────────────────────────────────────────────────┘
```

---

## 4. Baseline Switch Flow

```
┌─────────────────────────────────────────────────────────────────┐
│  USER: StandardPsikometrik.php                                  │
│  Click: "Gunakan Custom Standard: Kejaksaan 2025"              │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  CustomStandardService::select($templateId, $customStandardId)  │
│  ├─ Session::put("selected_standard.4", 1)                     │
│  └─ Session::forget("standard_adjustment.4")  ← Reset session  │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  Dispatch Event: 'standard-switched', templateId: 4             │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  ALL LISTENING COMPONENTS:                                       │
│  ├─ RekapRankingAssessment::handleStandardSwitch()             │
│  ├─ RankingPsyMapping::handleStandardSwitch()                  │
│  ├─ RankingMcMapping::handleStandardSwitch()                   │
│  ├─ GeneralMapping::handleStandardSwitch()                     │
│  └─ ... (all ranking pages)                                     │
│                                                                  │
│  Each calls: $this->clearCache()                               │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  USER: Navigate to RekapRankingAssessment                       │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  RankingService::getRankings(...)                               │
│  ├─ Build config hash:                                         │
│  │   ├─ getAspectWeight('integritas')                          │
│  │   │   ↓ Layer 2 check                                       │
│  │   │   ↓ Session::get("selected_standard.4") = 1  ✓         │
│  │   │   ↓ CustomStandard::find(1)                            │
│  │   │   ↓ return 15  ← Custom weight                         │
│  │   │                                                          │
│  │   └─ aspect_weights = [                                     │
│  │       'integritas' => 15,  (was 10 in Quantum)             │
│  │       'kepemimpinan' => 12, (was 10 in Quantum)            │
│  │       ...                                                    │
│  │     ]                                                        │
│  │                                                              │
│  ├─ configHash = md5(aspect_weights + session_id)             │
│  │               = "4febff24..."  ← DIFFERENT from before!    │
│  │                                                              │
│  └─ cacheKey = "rankings:potensi:1:1:4:4febff24..."           │
│                 ↑ Different hash = Cache MISS                  │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  Calculate with NEW baseline:                                   │
│  ├─ Use Custom Standard weights (15%, 12%, etc.)               │
│  ├─ Use Custom Standard ratings (5, 4, etc.)                   │
│  ├─ individual_rating UNCHANGED (historical data)              │
│  ├─ Ranking order MAY CHANGE (different weights!)              │
│  └─ Cache result with new cacheKey                             │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  DISPLAY: Rankings with Custom Standard applied                 │
│  └─ User sees impact of institution-specific baseline           │
└─────────────────────────────────────────────────────────────────┘
```

---

## 5. Session Adjustment Flow

```
┌─────────────────────────────────────────────────────────────────┐
│  USER: CategoryWeightEditor.php                                 │
│  Adjust: Potensi 25% → 30%                                     │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  DynamicStandardService::saveCategoryWeight($templateId,        │
│                                             'potensi', 30)       │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 1: Get Original Value (3-layer check)                     │
│  ├─ Check Layer 2: Custom Standard = 25%                       │
│  └─ originalValue = 25                                          │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 2: Compare with New Value                                 │
│  ├─ newValue = 30                                               │
│  ├─ 30 ≠ 25  ← Different from baseline                         │
│  └─ Save to session                                             │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 3: Save to Session                                        │
│  ├─ $adjustments = Session::get("standard_adjustment.4")       │
│  ├─ $adjustments['category_weights']['potensi'] = 30           │
│  ├─ $adjustments['adjusted_at'] = now()                        │
│  └─ Session::put("standard_adjustment.4", $adjustments)        │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  Dispatch Event: 'standard-adjusted', templateId: 4             │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  ALL LISTENING COMPONENTS: clearCache()                         │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  Next RankingService call:                                      │
│  ├─ Build config hash:                                         │
│  │   ├─ getCategoryWeight('potensi')                           │
│  │   │   ↓ Layer 1 check FIRST                                │
│  │   │   ↓ Session has 'potensi' = 30  ✓                      │
│  │   │   ↓ return 30  ← Session overrides Custom Standard     │
│  │   │                                                          │
│  │   └─ configHash = md5([potensi: 30, ...])                  │
│  │                  = "new_hash..."  ← DIFFERENT!              │
│  │                                                              │
│  └─ Cache MISS → Recalculate with adjusted weight              │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  USER can:                                                       │
│  ├─ Continue adjusting → All changes in session                │
│  ├─ Reset → Clears session, back to Custom Standard            │
│  └─ Create Custom Standard → Saves to database permanently     │
└─────────────────────────────────────────────────────────────────┘
```

---

## 6. Individual Report Flow

```
┌─────────────────────────────────────────────────────────────────┐
│  USER: Click participant "WINDA FUJIATI" from ranking           │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  GeneralMapping.php::mount($eventCode, $testNumber)             │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  IndividualAssessmentService::getParticipantFullAssessment(     │
│      participantId, potensiCategoryId, kompetensiCategoryId)    │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 1: Load Aspect Assessments (WITH sub-aspects!)            │
│  ├─ AspectAssessment::with(['aspect.subAspects',               │
│  │                           'subAspectAssessments.subAspect']) │
│  │         ->where('participant_id', $participantId)            │
│  │         ->get()  ← FULL ELOQUENT MODELS                     │
│  │                                                               │
│  ├─ Result: 13 AspectAssessment models                          │
│  ├─        + ~30 SubAspectAssessment models                     │
│  └─ Time: ~50ms (Only 1 participant, acceptable)               │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 2: Process Each Aspect                                    │
│  foreach ($aspects as $aspect) {                                │
│    ├─ Get standard values (3-layer priority):                  │
│    │   ├─ standardWeight = DynamicStandardService::             │
│    │   │                   getAspectWeight($aspect->code)       │
│    │   └─ standardRating = DynamicStandardService::             │
│    │                       getAspectRating($aspect->code)       │
│    │                                                             │
│    ├─ Individual values:                                        │
│    │   ├─ individualRating = $aspect->individual_rating        │
│    │   └─ individualScore = individualRating × standardWeight  │
│    │                                                             │
│    ├─ Standard score:                                           │
│    │   └─ standardScore = standardRating × standardWeight      │
│    │                                                             │
│    ├─ Gap & Conclusion:                                         │
│    │   ├─ gap = individualScore - standardScore                │
│    │   └─ conclusion = ConclusionService::getConclusion(gap)   │
│    │                                                             │
│    └─ Sub-aspect breakdown (for detail):                        │
│        foreach ($aspect->subAspectAssessments as $subAssess) { │
│          ├─ subRating = $subAssess->individual_rating          │
│          ├─ subStandard = DynamicStandardService::             │
│          │               getSubAspectRating($subAspect->code)  │
│          └─ subGap = subRating - subStandard                   │
│        }                                                         │
│  }                                                               │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  STEP 3: Get Participant Ranking (from RankingService)          │
│  ├─ RankingService::getParticipantCombinedRank($participantId) │
│  ├─ Returns: rank, total, conclusion                            │
│  └─ Display: "Ranking 1 dari 4905"                             │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│  OUTPUT: Individual Report                                       │
│  ├─ Overall: "Memenuhi Standar" (Rank 1/4905)                  │
│  ├─ Aspect Breakdown:                                           │
│  │   ├─ Daya Pikir: 4.2 vs 3.5 (✅ +0.7)                      │
│  │   │   ├─ Daya Analisa: 4 vs 3 (✅)                          │
│  │   │   ├─ Kreativitas: 5 vs 4 (✅)                           │
│  │   │   └─ Fleksibilitas: 4 vs 3 (✅)                         │
│  │   ├─ Integritas: 5.0 vs 4.0 (✅ +1.0)                      │
│  │   └─ ...                                                     │
│  └─ Charts: Visual comparison                                   │
└─────────────────────────────────────────────────────────────────┘
```

---

## 7. Cache Invalidation Scenarios

```
┌────────────────────────────────────────────────────────────┐
│  Scenario 1: User Adjusts Weight                           │
├────────────────────────────────────────────────────────────┤
│  User: Change "Integritas" 15% → 18%                      │
│    ↓                                                        │
│  Session: aspect_weights['integritas'] = 18               │
│    ↓                                                        │
│  Config Hash: Changes from "bbf2a5..." to "new_hash..."   │
│    ↓                                                        │
│  Cache: MISS (different key)                               │
│    ↓                                                        │
│  Result: ✅ Immediate recalculation with new weight       │
└────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────┐
│  Scenario 2: User Switches Baseline                        │
├────────────────────────────────────────────────────────────┤
│  User: Quantum Default → Custom Standard "Kejaksaan"      │
│    ↓                                                        │
│  Session: selected_standard.4 = 1                          │
│    ↓                                                        │
│  3-Layer: Now reads from CustomStandard (Layer 2)         │
│    ↓                                                        │
│  Config Hash: Changes (different weights from custom std) │
│    ↓                                                        │
│  Cache: MISS (different key)                               │
│    ↓                                                        │
│  Result: ✅ Immediate recalculation with custom baseline  │
└────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────┐
│  Scenario 3: User Changes Tolerance                        │
├────────────────────────────────────────────────────────────┤
│  User: Tolerance 0% → 10%                                  │
│    ↓                                                        │
│  Tolerance: NOT in config hash                             │
│    ↓                                                        │
│  Cache: HIT (same key)                                     │
│    ↓                                                        │
│  Apply tolerance AFTER cache:                              │
│    adjustedStd = originalStd × (1 - 0.1)                  │
│    ↓                                                        │
│  Result: ✅ INSTANT update (no recalculation needed)      │
└────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────┐
│  Scenario 4: Admin Updates Custom Standard in DB           │
├────────────────────────────────────────────────────────────┤
│  Admin: Update CustomStandard.aspect_configs in database  │
│    ↓                                                        │
│  Users: Still have old cache (max 60s)                    │
│    ↓                                                        │
│  After 60s: Cache expires                                  │
│    ↓                                                        │
│  Next request: Reads NEW values from DB                    │
│    ↓                                                        │
│  Result: ⏱️ Max 60s delay (acceptable for BI)            │
└────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────┐
│  Scenario 5: User Navigates Between Pages                  │
├────────────────────────────────────────────────────────────┤
│  User: RekapRanking → RankingPsy → RankingMc              │
│    ↓                                                        │
│  Each page: Calls RankingService with SAME config          │
│    ↓                                                        │
│  Config Hash: SAME (no adjustments between pages)          │
│    ↓                                                        │
│  Cache: HIT for all pages                                  │
│    ↓                                                        │
│  Result: ✅ Fast navigation (701ms per page)              │
└────────────────────────────────────────────────────────────┘
```

---

**Last Updated:** December 2025
**Purpose:** Quick visual reference for developers
