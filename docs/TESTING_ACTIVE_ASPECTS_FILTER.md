# Testing Active Aspects Filter

**Purpose:** Dokumentasi untuk memverifikasi bahwa aspek yang di-disable tidak dikalkulasi dalam scoring peserta.

**Critical Issue:** Saat aspek/sub-aspek di-disable via `SelectiveAspectsModal`, nilai individual peserta HARUS exclude aspek yang disabled agar match dengan standard score yang sudah adjusted.

---

## Table of Contents

1. [Background](#background)
2. [Testing Method](#testing-method)
3. [Step-by-Step Testing Guide](#step-by-step-testing-guide)
4. [Expected Results](#expected-results)
5. [Affected Pages](#affected-pages)
6. [Troubleshooting](#troubleshooting)

---

## Background

### The Problem

Sebelum fix, terjadi **data mismatch**:

```
Standard Score Calculation:
✅ CORRECT: Excludes disabled aspects

Individual Score Calculation:
❌ WRONG: Includes disabled aspects (BUG)

Result: Gap calculation INFLATED, ranking TIDAK AKURAT
```

### The Solution

Method `DynamicStandardService::getActiveAspectIds()` digunakan untuk filter aspect IDs yang aktif:

```php
// Get ONLY active aspect IDs
$activePotensiIds = $standardService->getActiveAspectIds($templateId, 'potensi');
$activeKompetensiIds = $standardService->getActiveAspectIds($templateId, 'kompetensi');

// Query MUST use active IDs only
->whereIn('aspect_assessments.aspect_id', $activePotensiIds)
```

**Location:** [app/Services/DynamicStandardService.php:774-790](../app/Services/DynamicStandardService.php#L774-L790)

---

## Testing Method

### Prerequisites

1. Database dengan assessment data (event, participants, aspect_assessments)
2. Akses ke Laravel Tinker
3. Template dengan minimal 5 aspek Potensi dan 7 aspek Kompetensi

### Testing Tools

**Menggunakan Laravel Tinker** (recommended for quick testing):

```bash
php artisan tinker
```

---

## Step-by-Step Testing Guide

### Test 1: Verify `getActiveAspectIds()` Returns All Aspects When No Adjustments

**Purpose:** Memastikan method return semua aspects saat tidak ada yang di-disable.

```php
$service = app(\App\Services\DynamicStandardService::class);
$templateId = 1; // Ganti dengan template ID yang ada

// Get active aspect IDs
$activePotensiIds = $service->getActiveAspectIds($templateId, 'potensi');
$activeKompetensiIds = $service->getActiveAspectIds($templateId, 'kompetensi');

// Compare with total aspects
$totalPotensi = \App\Models\Aspect::where('template_id', $templateId)
    ->whereHas('categoryType', fn($q) => $q->where('code', 'potensi'))
    ->count();

$totalKompetensi = \App\Models\Aspect::where('template_id', $templateId)
    ->whereHas('categoryType', fn($q) => $q->where('code', 'kompetensi'))
    ->count();

return [
    'active_potensi_count' => count($activePotensiIds),
    'total_potensi_count' => $totalPotensi,
    'active_kompetensi_count' => count($activeKompetensiIds),
    'total_kompetensi_count' => $totalKompetensi,
    'test_passed' => count($activePotensiIds) === $totalPotensi
        && count($activeKompetensiIds) === $totalKompetensi,
];
```

**Expected Result:**
```php
[
    'active_potensi_count' => 5,
    'total_potensi_count' => 5,
    'active_kompetensi_count' => 7,
    'total_kompetensi_count' => 7,
    'test_passed' => true,
]
```

---

### Test 2: Verify Disabled Aspects Are Excluded

**Purpose:** Memastikan aspek yang di-disable tidak masuk dalam active IDs.

```php
$service = app(\App\Services\DynamicStandardService::class);
$templateId = 1;

// Get aspect to disable
$potensiAspect = \App\Models\Aspect::where('template_id', $templateId)
    ->whereHas('categoryType', fn($q) => $q->where('code', 'potensi'))
    ->first();

$kompetensiAspect = \App\Models\Aspect::where('template_id', $templateId)
    ->whereHas('categoryType', fn($q) => $q->where('code', 'kompetensi'))
    ->first();

// Disable them
$service->setAspectActive($templateId, $potensiAspect->code, false);
$service->setAspectActive($templateId, $kompetensiAspect->code, false);

// Get active IDs
$activePotensiIds = $service->getActiveAspectIds($templateId, 'potensi');
$activeKompetensiIds = $service->getActiveAspectIds($templateId, 'kompetensi');

return [
    'disabled_potensi' => [
        'id' => $potensiAspect->id,
        'name' => $potensiAspect->name,
        'is_excluded' => !in_array($potensiAspect->id, $activePotensiIds),
    ],
    'disabled_kompetensi' => [
        'id' => $kompetensiAspect->id,
        'name' => $kompetensiAspect->name,
        'is_excluded' => !in_array($kompetensiAspect->id, $activeKompetensiIds),
    ],
    'test_passed' => !in_array($potensiAspect->id, $activePotensiIds)
        && !in_array($kompetensiAspect->id, $activeKompetensiIds),
];
```

**Expected Result:**
```php
[
    'disabled_potensi' => [
        'id' => 2,
        'name' => 'Cara Kerja',
        'is_excluded' => true, // ✅ MUST BE TRUE
    ],
    'disabled_kompetensi' => [
        'id' => 6,
        'name' => 'Integritas',
        'is_excluded' => true, // ✅ MUST BE TRUE
    ],
    'test_passed' => true,
]
```

---

### Test 3: Verify Individual Scores Are Filtered Correctly

**Purpose:** Memastikan query ranking pages filter individual scores dengan benar.

```php
$service = app(\App\Services\DynamicStandardService::class);
$templateId = 2; // Template yang ada data assessment
$eventId = 1;
$positionId = 3; // Position yang ada data

// Find participant with data
$participant = DB::table('aspect_assessments')
    ->where('event_id', $eventId)
    ->where('position_formation_id', $positionId)
    ->select('participant_id')
    ->groupBy('participant_id')
    ->first();

if (!$participant) {
    return "No assessment data found";
}

$participantId = $participant->participant_id;

// Disable some aspects
$potensiAspects = \App\Models\Aspect::where('template_id', $templateId)
    ->whereHas('categoryType', fn($q) => $q->where('code', 'potensi'))
    ->take(1)
    ->get();

$kompetensiAspects = \App\Models\Aspect::where('template_id', $templateId)
    ->whereHas('categoryType', fn($q) => $q->where('code', 'kompetensi'))
    ->take(2)
    ->get();

foreach ($potensiAspects as $aspect) {
    $service->setAspectActive($templateId, $aspect->code, false);
}

foreach ($kompetensiAspects as $aspect) {
    $service->setAspectActive($templateId, $aspect->code, false);
}

// Get ALL aspect IDs (for comparison)
$allPotensiIds = \App\Models\Aspect::where('template_id', $templateId)
    ->whereHas('categoryType', fn($q) => $q->where('code', 'potensi'))
    ->pluck('id')->toArray();

$allKompetensiIds = \App\Models\Aspect::where('template_id', $templateId)
    ->whereHas('categoryType', fn($q) => $q->where('code', 'kompetensi'))
    ->pluck('id')->toArray();

// Get ACTIVE aspect IDs (filtered)
$activePotensiIds = $service->getActiveAspectIds($templateId, 'potensi');
$activeKompetensiIds = $service->getActiveAspectIds($templateId, 'kompetensi');

// BEFORE FIX: Query with ALL aspects (includes disabled)
$beforeFix = DB::table('aspect_assessments as aa')
    ->join('aspects as a', 'a.id', '=', 'aa.aspect_id')
    ->where('aa.event_id', $eventId)
    ->where('aa.position_formation_id', $positionId)
    ->where('aa.participant_id', $participantId)
    ->whereIn('aa.aspect_id', array_merge($allPotensiIds, $allKompetensiIds))
    ->selectRaw('SUM(CASE WHEN a.id IN ('.implode(',', $allPotensiIds).') THEN aa.individual_score ELSE 0 END) as potensi')
    ->selectRaw('SUM(CASE WHEN a.id IN ('.implode(',', $allKompetensiIds).') THEN aa.individual_score ELSE 0 END) as kompetensi')
    ->first();

// AFTER FIX: Query with ACTIVE aspects only (excludes disabled)
$afterFix = DB::table('aspect_assessments as aa')
    ->join('aspects as a', 'a.id', '=', 'aa.aspect_id')
    ->where('aa.event_id', $eventId)
    ->where('aa.position_formation_id', $positionId)
    ->where('aa.participant_id', $participantId)
    ->whereIn('aa.aspect_id', array_merge($activePotensiIds, $activeKompetensiIds))
    ->selectRaw('SUM(CASE WHEN a.id IN ('.implode(',', $activePotensiIds ?: [0]).') THEN aa.individual_score ELSE 0 END) as potensi')
    ->selectRaw('SUM(CASE WHEN a.id IN ('.implode(',', $activeKompetensiIds ?: [0]).') THEN aa.individual_score ELSE 0 END) as kompetensi')
    ->first();

$beforeTotal = $beforeFix->potensi + $beforeFix->kompetensi;
$afterTotal = $afterFix->potensi + $afterFix->kompetensi;
$scoreDiff = $beforeTotal - $afterTotal;

return [
    'participant_id' => $participantId,
    'disabled_aspects' => [
        'potensi_count' => $potensiAspects->count(),
        'kompetensi_count' => $kompetensiAspects->count(),
        'names' => $potensiAspects->merge($kompetensiAspects)->pluck('name')->toArray(),
    ],
    'BEFORE_FIX (includes disabled)' => [
        'potensi_score' => (float) $beforeFix->potensi,
        'kompetensi_score' => (float) $beforeFix->kompetensi,
        'total_score' => (float) $beforeTotal,
    ],
    'AFTER_FIX (excludes disabled)' => [
        'potensi_score' => (float) $afterFix->potensi,
        'kompetensi_score' => (float) $afterFix->kompetensi,
        'total_score' => (float) $afterTotal,
    ],
    'IMPACT' => [
        'score_reduction' => round($scoreDiff, 2),
        'percentage_reduction' => round(($scoreDiff / $beforeTotal) * 100, 2) . '%',
    ],
    'test_passed' => $beforeTotal > $afterTotal && $afterTotal > 0,
];
```

**Expected Result:**
```php
[
    'participant_id' => 6,
    'disabled_aspects' => [
        'potensi_count' => 1,
        'kompetensi_count' => 2,
        'names' => ['Cara Kerja', 'Integritas', 'Mengelola Perubahan'],
    ],
    'BEFORE_FIX (includes disabled)' => [
        'potensi_score' => 196.25,
        'kompetensi_score' => 234.0,
        'total_score' => 430.25,
    ],
    'AFTER_FIX (excludes disabled)' => [
        'potensi_score' => 156.25, // ✅ REDUCED (40 points from disabled aspect)
        'kompetensi_score' => 176.0, // ✅ REDUCED (58 points from 2 disabled aspects)
        'total_score' => 332.25,   // ✅ REDUCED (98 points total)
    ],
    'IMPACT' => [
        'score_reduction' => 98.0,
        'percentage_reduction' => '22.78%',
    ],
    'test_passed' => true, // ✅ MUST BE TRUE
]
```

---

### Test 4: Verify Reset Works Correctly

**Purpose:** Memastikan reset adjustments mengembalikan semua aspects ke active.

```php
$service = app(\App\Services\DynamicStandardService::class);
$templateId = 1;

// Disable some aspects
$aspects = \App\Models\Aspect::where('template_id', $templateId)->take(3)->get();
foreach ($aspects as $aspect) {
    $service->setAspectActive($templateId, $aspect->code, false);
}

// Verify they are disabled
$beforeReset = $service->getActiveAspectIds($templateId, 'potensi');

// Reset adjustments
$service->resetCategoryAdjustments($templateId, 'potensi');
$service->resetCategoryAdjustments($templateId, 'kompetensi');

// Verify all are active again
$afterReset = $service->getActiveAspectIds($templateId, 'potensi');

$totalPotensi = \App\Models\Aspect::where('template_id', $templateId)
    ->whereHas('categoryType', fn($q) => $q->where('code', 'potensi'))
    ->count();

return [
    'before_reset_count' => count($beforeReset),
    'after_reset_count' => count($afterReset),
    'total_aspects' => $totalPotensi,
    'test_passed' => count($afterReset) === $totalPotensi,
];
```

**Expected Result:**
```php
[
    'before_reset_count' => 2,  // Some disabled
    'after_reset_count' => 5,   // ✅ All active again
    'total_aspects' => 5,
    'test_passed' => true,
]
```

---

## Expected Results

### ✅ Success Criteria

1. **No Adjustments:** `getActiveAspectIds()` returns ALL aspect IDs
2. **With Disabled Aspects:** `getActiveAspectIds()` excludes disabled aspect IDs
3. **Score Calculation:** Individual scores are LOWER when aspects are disabled
4. **Reset:** After reset, all aspects become active again

### ❌ Failure Indicators

1. Disabled aspects still appear in active IDs → **BUG in `getActiveAspectIds()`**
2. Score sama sebelum dan sesudah disable aspects → **BUG in query filter**
3. Score INCREASE setelah disable aspects → **CRITICAL BUG**

---

## Affected Pages

Files yang menggunakan `getActiveAspectIds()` dan HARUS filter individual scores:

### Ranking Pages (✅ FIXED)

1. **RekapRankingAssessment**
   - File: [app/Livewire/Pages/GeneralReport/Ranking/RekapRankingAssessment.php:307-309](../app/Livewire/Pages/GeneralReport/Ranking/RekapRankingAssessment.php#L307-L309)
   - Categories: Potensi + Kompetensi
   - Query: Line 320-331

2. **RankingMcMapping**
   - File: [app/Livewire/Pages/GeneralReport/Ranking/RankingMcMapping.php:290-291](../app/Livewire/Pages/GeneralReport/Ranking/RankingMcMapping.php#L290-L291)
   - Categories: Kompetensi only
   - Query: Line 299-308

3. **RankingPsyMapping**
   - File: [app/Livewire/Pages/GeneralReport/Ranking/RankingPsyMapping.php:314-315](../app/Livewire/Pages/GeneralReport/Ranking/RankingPsyMapping.php#L314-L315)
   - Categories: Potensi only
   - Query: Line 323-332

### Individual Report Pages (⚠️ NEEDS FIX)

4. **GeneralMapping**
   - File: `app/Livewire/Pages/IndividualReport/GeneralMapping.php`
   - Status: ⚠️ **Needs same fix**

5. **GeneralPsyMapping**
   - File: `app/Livewire/Pages/IndividualReport/GeneralPsyMapping.php`
   - Status: ⚠️ **Needs same fix**

6. **GeneralMcMapping**
   - File: `app/Livewire/Pages/IndividualReport/GeneralMcMapping.php`
   - Status: ⚠️ **Needs same fix**

---

## Troubleshooting

### Problem: `getActiveAspectIds()` returns empty array

**Possible Cause:** No aspects found for the category.

**Solution:** Verify template has aspects for the category:
```php
\App\Models\Aspect::where('template_id', $templateId)
    ->whereHas('categoryType', fn($q) => $q->where('code', 'potensi'))
    ->get();
```

---

### Problem: Disabled aspects still in active IDs

**Possible Cause:** Session not persisting or `setAspectActive()` not called.

**Solution:** Verify aspect is disabled in session:
```php
$service = app(\App\Services\DynamicStandardService::class);
$service->isAspectActive($templateId, 'aspect_code'); // Should return false
```

---

### Problem: Score tidak berubah setelah disable aspects

**Possible Cause:** Query tidak menggunakan `$activeIds`, masih pakai `$allIds`.

**Solution:** Pastikan query menggunakan active IDs:
```php
// ❌ WRONG
->whereIn('aspect_assessments.aspect_id', $allAspectIds)

// ✅ CORRECT
->whereIn('aspect_assessments.aspect_id', $activeAspectIds)
```

---

### Problem: Test di tinker tidak persist antar calls

**Possible Cause:** Session reset setiap tinker call.

**Solution:** Run semua test dalam 1 tinker call (copy-paste seluruh script).

---

## Quick Test Script

Copy-paste script ini ke tinker untuk quick verification:

```php
// Quick Test: Verify Active Aspects Filter
$service = app(\App\Services\DynamicStandardService::class);
$templateId = 2;

// 1. Get baseline (no adjustments)
$baselinePotensi = count($service->getActiveAspectIds($templateId, 'potensi'));
$baselineKompetensi = count($service->getActiveAspectIds($templateId, 'kompetensi'));

// 2. Disable 1 aspect from each category
$potensiAspect = \App\Models\Aspect::where('template_id', $templateId)
    ->whereHas('categoryType', fn($q) => $q->where('code', 'potensi'))
    ->first();
$kompetensiAspect = \App\Models\Aspect::where('template_id', $templateId)
    ->whereHas('categoryType', fn($q) => $q->where('code', 'kompetensi'))
    ->first();

$service->setAspectActive($templateId, $potensiAspect->code, false);
$service->setAspectActive($templateId, $kompetensiAspect->code, false);

// 3. Verify counts decreased
$afterPotensi = count($service->getActiveAspectIds($templateId, 'potensi'));
$afterKompetensi = count($service->getActiveAspectIds($templateId, 'kompetensi'));

// 4. Results
return [
    '✅ Test Results' => [
        'baseline_potensi' => $baselinePotensi,
        'after_disable_potensi' => $afterPotensi,
        'potensi_decreased' => $afterPotensi < $baselinePotensi,

        'baseline_kompetensi' => $baselineKompetensi,
        'after_disable_kompetensi' => $afterKompetensi,
        'kompetensi_decreased' => $afterKompetensi < $baselineKompetensi,
    ],
    '🎯 Overall Test' => ($afterPotensi < $baselinePotensi && $afterKompetensi < $baselineKompetensi)
        ? '✅ PASSED - Filter works correctly!'
        : '❌ FAILED - Filter not working!',
];
```

**Expected Output:**
```php
[
    '✅ Test Results' => [
        'baseline_potensi' => 4,
        'after_disable_potensi' => 3,
        'potensi_decreased' => true, // ✅

        'baseline_kompetensi' => 9,
        'after_disable_kompetensi' => 8,
        'kompetensi_decreased' => true, // ✅
    ],
    '🎯 Overall Test' => '✅ PASSED - Filter works correctly!',
]
```

---

## Changelog

| Date | Author | Changes |
|------|--------|---------|
| 2025-01-09 | Claude | Initial documentation created |
| 2025-01-09 | Claude | Added Test 3 (Individual Scores verification) |
| 2025-01-09 | Claude | Added Quick Test Script |

---

## References

- [DynamicStandardService.php](../app/Services/DynamicStandardService.php)
- [DATABASE_STRUCTURE.md](./DATABASE_STRUCTURE.md)
- [ASSESSMENT_CALCULATION_FLOW.md](./ASSESSMENT_CALCULATION_FLOW.md)
