# LIVEWIRE REFACTORING GUIDE

Panduan refactoring Livewire components untuk integrasi dengan **DynamicStandardService**.

---

## CRITICAL RULES - DYNAMIC STANDARD SERVICE

### 1. **ALWAYS Filter by Active Aspects**

❌ **WRONG:**
```php
$aspectIds = Aspect::where('category_type_id', $categoryId)->pluck('id');

AspectAssessment::where('participant_id', $id)
    ->whereIn('aspect_id', $aspectIds) // ← Includes disabled aspects!
    ->get();
```

✅ **CORRECT:**
```php
$standardService = app(DynamicStandardService::class);
$activeAspectIds = $standardService->getActiveAspectIds($templateId, $categoryCode);

// Fallback to all if no adjustments
if (empty($activeAspectIds)) {
    $activeAspectIds = Aspect::where('category_type_id', $categoryId)->pluck('id');
}

AspectAssessment::where('participant_id', $id)
    ->whereIn('aspect_id', $activeAspectIds) // ✅ Filter active only
    ->get();
```

**Why?** Disabled aspects must be excluded from BOTH standard AND individual calculations.

---

### 2. **ALWAYS Use Adjusted Weights**

❌ **WRONG:**
```php
$weight = $aspect->weight_percentage; // From database
$score = $rating × $weight;
```

✅ **CORRECT:**
```php
$standardService = app(DynamicStandardService::class);
$adjustedWeight = $standardService->getAspectWeight($templateId, $aspect->code);
$score = round($rating × $adjustedWeight, 2);
```

**Impact:** Score calculation must use session-adjusted weights, not database values.

---

### 3. **Recalculate Potensi Aspect Rating from Active Sub-Aspects**

❌ **WRONG:**
```php
// Using database value directly
$aspectRating = $assessment->standard_rating; // ← Includes disabled sub-aspects!
```

✅ **CORRECT:**
```php
$standardService = app(DynamicStandardService::class);

if ($aspect->subAspects && $aspect->subAspects->count() > 0) {
    $activeSum = 0;
    $activeCount = 0;

    foreach ($assessment->subAspectAssessments as $subAssessment) {
        // Check if sub-aspect is active
        if (!$standardService->isSubAspectActive($templateId, $subAssessment->subAspect->code)) {
            continue; // Skip inactive
        }

        // Get adjusted rating
        $adjustedRating = $standardService->getSubAspectRating(
            $templateId,
            $subAssessment->subAspect->code
        );

        $activeSum += $adjustedRating;
        $activeCount++;
    }

    if ($activeCount > 0) {
        $aspectRating = round($activeSum / $activeCount, 2);
    }
}
```

**Formula:** `Aspect Rating = AVG(active sub-aspects only)`

---

### 4. **Check Adjustments Exist Before Complex Logic**

✅ **OPTIMIZATION:**
```php
$standardService = app(DynamicStandardService::class);

// Check if adjustments exist
if (!$standardService->hasCategoryAdjustments($templateId, $categoryCode)) {
    // No adjustments, use database values directly
    return $originalValues;
}

// Adjustments exist, proceed with complex logic
// ... getAspectWeight(), isAspectActive(), etc.
```

**Why?** Skip unnecessary service calls when no adjustments exist (performance).

---

## CATEGORY DIFFERENCES

### Potensi (with Sub-Aspects)

```
Structure:
Category → Aspects → Sub-Aspects

Adjustments:
- Active/inactive aspects
- Active/inactive sub-aspects
- Aspect weights
- Sub-aspect ratings

Calculation:
1. Filter active aspects
2. For each aspect:
   - Filter active sub-aspects
   - Get adjusted sub-aspect ratings
   - Calculate: aspectRating = AVG(active sub-aspects)
   - Get adjusted aspect weight
   - Calculate: aspectScore = aspectRating × adjustedWeight
```

### Kompetensi (without Sub-Aspects)

```
Structure:
Category → Aspects (flat, no sub-aspects)

Adjustments:
- Active/inactive aspects
- Aspect weights
- Aspect ratings (direct)

Calculation:
1. Filter active aspects
2. For each aspect:
   - Get adjusted aspect rating (from session)
   - Get adjusted aspect weight
   - Calculate: aspectScore = aspectRating × adjustedWeight
```

---

## MANDATORY PATTERNS

### 1. Eager Loading

✅ **Load sub-aspects when needed:**
```php
// For Potensi
AspectAssessment::with([
    'aspect.subAspects',
    'subAspectAssessments.subAspect',
])
    ->whereIn('aspect_id', $activeAspectIds)
    ->get();

// For Kompetensi (no sub-aspects needed)
AspectAssessment::with('aspect')
    ->whereIn('aspect_id', $activeAspectIds)
    ->get();
```

---

### 2. Consistent Rounding

✅ **ALWAYS round to 2 decimals:**
```php
$aspectRating = round($sum / $count, 2);
$aspectScore = round($rating × $weight, 2);
$gap = round($individual - $standard, 2);
$percentage = round(($score / $standardScore) × 100, 2);
```

---

### 3. Caching Strategy

✅ **Implement two-level cache:**
```php
// Cache properties
private ?array $dataCache = null;
private ?array $rankingCache = null;

// Check cache first
if ($this->dataCache !== null) {
    return $this->dataCache;
}

// Calculate + cache
$this->dataCache = $calculatedData;
return $this->dataCache;

// Clear cache when needed
private function clearCache(): void {
    $this->dataCache = null;
    $this->rankingCache = null;
}
```

**Clear cache when:**
- Event changed
- Position changed
- Tolerance changed
- Standard adjusted (via `standard-adjusted` event)

---

## DATA STRUCTURE REQUIREMENTS

### Enhanced Data with Metadata

```php
return [
    'name' => $aspect->name,

    // Weight
    'weight_percentage' => $adjustedWeight, // ✅ Use adjusted
    'original_weight' => $aspect->weight_percentage, // For comparison
    'is_weight_adjusted' => $adjustedWeight !== $aspect->weight_percentage,

    // Ratings & Scores
    'original_standard_rating' => $recalculatedRating,
    'original_standard_score' => $recalculatedScore,
    'standard_rating' => $adjustedStandardRating, // With tolerance
    'standard_score' => $adjustedStandardScore,
    'individual_rating' => $individualRating,
    'individual_score' => $individualScore,

    // Gaps
    'gap_rating' => $adjustedGapRating,
    'gap_score' => $adjustedGapScore,
    'original_gap_rating' => $originalGapRating,
    'original_gap_score' => $originalGapScore,

    // Others
    'percentage_score' => $adjustedPercentage,
    'conclusion_text' => $this->getConclusionText($originalGap, $adjustedGap),
];
```

---

## COMMON MISTAKES

### ❌ Mistake 1: Not filtering query by active aspects

```php
// WRONG: Query gets ALL aspects from database
$aspects = AspectAssessment::where('participant_id', $id)->get();
```

**Impact:** Disabled aspects included in calculation → wrong totals.

---

### ❌ Mistake 2: Using database weight instead of adjusted

```php
// WRONG: Weight from database
$score = $rating × $aspect->weight_percentage;
```

**Impact:** Score calculation ignores user adjustments.

---

### ❌ Mistake 3: Not recalculating aspect rating for Potensi

```php
// WRONG: Using database rating (includes disabled sub-aspects)
$aspectRating = $assessment->standard_rating;
```

**Impact:** Disabled sub-aspects still affect aspect rating.

---

### ❌ Mistake 4: Inconsistent rounding

```php
// WRONG: Some values rounded, some not
$total = $value1 + $value2; // No rounding
```

**Impact:** Display inconsistency, floating point errors.

---

## REFACTORING CHECKLIST

Use this checklist for every Livewire component refactoring:

- [ ] **Import DynamicStandardService**
- [ ] **Add cache properties** (`private ?array $cache = null`)
- [ ] **Add clearCache() method**
- [ ] **Get active aspect IDs** (use `getActiveAspectIds()`)
- [ ] **Filter queries** with active IDs (`whereIn('aspect_id', $activeAspectIds)`)
- [ ] **Use adjusted weights** (call `getAspectWeight()`)
- [ ] **Recalculate Potensi ratings** (AVG of active sub-aspects)
- [ ] **Check sub-aspect active status** (use `isSubAspectActive()`)
- [ ] **Round all calculations** to 2 decimals
- [ ] **Eager load relationships** (avoid N+1)
- [ ] **Check adjustments exist** before complex logic (`hasCategoryAdjustments()`)
- [ ] **Cache results** after calculation
- [ ] **Clear cache on events** (event-selected, position-selected, tolerance-updated)
- [ ] **Run Laravel Pint** for formatting
- [ ] **Test with disabled aspects** and sub-aspects
- [ ] **Verify weight changes** reflected in UI

---

## EXAMPLE: COMPLETE REFACTORED METHOD

```php
private function loadAspectsData(): void
{
    // 1. Check cache first
    if ($this->aspectsDataCache !== null) {
        $this->aspectsData = $this->aspectsDataCache;
        return;
    }

    $template = $this->participant->positionFormation->template;
    $standardService = app(DynamicStandardService::class);

    // 2. Get ONLY active aspect IDs
    $activeAspectIds = $standardService->getActiveAspectIds($template->id, 'potensi');

    // Fallback to all if no adjustments
    if (empty($activeAspectIds)) {
        $activeAspectIds = Aspect::where('category_type_id', $categoryId)
            ->pluck('id')
            ->toArray();
    }

    // 3. Query with active filter + eager load
    $assessments = AspectAssessment::with([
        'aspect.subAspects',
        'subAspectAssessments.subAspect',
    ])
        ->where('participant_id', $this->participant->id)
        ->whereIn('aspect_id', $activeAspectIds) // ✅ Filter active
        ->get();

    // 4. Process each assessment
    $data = $assessments->map(function ($assessment) use ($template, $standardService) {
        $aspect = $assessment->aspect;

        // Get adjusted weight
        $adjustedWeight = $standardService->getAspectWeight($template->id, $aspect->code);

        // Recalculate rating for Potensi (with sub-aspects)
        $recalculatedRating = null;
        if ($aspect->subAspects && $aspect->subAspects->count() > 0) {
            $activeSum = 0;
            $activeCount = 0;

            foreach ($assessment->subAspectAssessments as $subAssessment) {
                // Check if sub-aspect is active
                if (!$standardService->isSubAspectActive($template->id, $subAssessment->subAspect->code)) {
                    continue;
                }

                // Get adjusted sub-aspect rating
                $adjustedSubRating = $standardService->getSubAspectRating(
                    $template->id,
                    $subAssessment->subAspect->code
                );

                $activeSum += $adjustedSubRating;
                $activeCount++;
            }

            if ($activeCount > 0) {
                $recalculatedRating = round($activeSum / $activeCount, 2);
            }
        }

        // Use recalculated or database value
        $aspectRating = $recalculatedRating ?? (float) $assessment->standard_rating;

        // Calculate score with adjusted weight
        $aspectScore = round($aspectRating × $adjustedWeight, 2);

        return [
            'name' => $aspect->name,
            'weight_percentage' => $adjustedWeight,
            'original_weight' => $aspect->weight_percentage,
            'is_weight_adjusted' => $adjustedWeight !== $aspect->weight_percentage,
            'rating' => $aspectRating,
            'score' => $aspectScore,
            // ... other fields
        ];
    })->toArray();

    // 5. Cache the result
    $this->aspectsDataCache = $data;
    $this->aspectsData = $data;
}
```

---

## TESTING SCENARIOS

After refactoring, test these scenarios:

### Test 1: Disable Aspect
1. Disable aspect "Kepribadian" via StandardPsikometrik
2. Load individual report
3. ✅ Aspect "Kepribadian" should NOT appear
4. ✅ Totals should exclude "Kepribadian"

### Test 2: Disable Sub-Aspect
1. Disable 2 sub-aspects in "Kecerdasan"
2. Load individual report
3. ✅ Aspect "Kecerdasan" rating should recalculate (lower)
4. ✅ Score should recalculate

### Test 3: Adjust Weight
1. Change aspect weight 25% → 30%
2. Load individual report
3. ✅ Weight column shows 30%
4. ✅ Score recalculated with 30%

### Test 4: Ranking Consistency
1. Adjust standards in general report
2. Compare ranking between:
   - RankingPsyMapping (general report)
   - Individual report ranking
3. ✅ Rank should be IDENTICAL

---

## PERFORMANCE TIPS

1. **Use `hasCategoryAdjustments()` first** - Skip complex logic if no adjustments
2. **Cache aggressively** - Calculate once, reuse multiple times
3. **Eager load** - One query instead of N+1
4. **Clear cache smartly** - Only when data structure changes (not tolerance)

---

## REFERENCES

- [ASSESSMENT_CALCULATION_FLOW.md](./ASSESSMENT_CALCULATION_FLOW.md) - Calculation formulas
- [DATABASE_STRUCTURE.md](./DATABASE_STRUCTURE.md) - Database schema
- [DynamicStandardService.php](../app/Services/DynamicStandardService.php) - Service implementation
- [RankingPsyMapping.php](../app/Livewire/Pages/GeneralReport/Ranking/RankingPsyMapping.php) - Reference implementation
- [GeneralPsyMapping.php](../app/Livewire/Pages/IndividualReport/GeneralPsyMapping.php) - Refactored example

---

## QUICK REFERENCE

| Action | Method | Category |
|--------|--------|----------|
| Get active aspects | `getActiveAspectIds($templateId, $categoryCode)` | Both |
| Get active sub-aspects | `isSubAspectActive($templateId, $subAspectCode)` | Potensi only |
| Get adjusted weight | `getAspectWeight($templateId, $aspectCode)` | Both |
| Get adjusted aspect rating | `getAspectRating($templateId, $aspectCode)` | Kompetensi only |
| Get adjusted sub-aspect rating | `getSubAspectRating($templateId, $subAspectCode)` | Potensi only |
| Check if adjustments exist | `hasCategoryAdjustments($templateId, $categoryCode)` | Both |

---

**Last Updated:** 2025-01-13
**Refactored Files:** GeneralPsyMapping.php
