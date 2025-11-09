# Debugging Active Aspects di Ranking Pages

**Purpose:** Guide untuk debugging dan memverifikasi bahwa hanya aspek aktif yang digunakan dalam kalkulasi.

---

## Variabel Kunci untuk Di-Debug

### Di RankingPsyMapping (line 302-332)

```php
private function getAggregatesData(): ?array
{
    // ... kode lainnya ...

    // 🔍 VARIABEL 1: All Potensi Aspect IDs (dari database)
    $potensiAspectIds = Aspect::query()
        ->where('category_type_id', $potensiCategory->id)
        ->orderBy('order')
        ->pluck('id')
        ->all();

    // 🔍 VARIABEL 2: Active Potensi Aspect IDs (filtered)
    $standardService = app(DynamicStandardService::class);
    $activePotensiIds = $standardService->getActiveAspectIds($position->template_id, 'potensi');

    // 🔍 VARIABEL 3: Fallback logic
    if (empty($activePotensiIds)) {
        $activePotensiIds = $potensiAspectIds;
    }

    // 🔍 VARIABEL 4: Query result (harus pakai $activePotensiIds)
    $aggregates = AspectAssessment::query()
        ->whereIn('aspect_assessments.aspect_id', $activePotensiIds) // ✅
        ->get();
}
```

---

## Method 1: Debugging dengan dd() (Development)

### Step 1: Tambahkan dd() di getAggregatesData()

**File:** `app/Livewire/Pages/GeneralReport/Ranking/RankingPsyMapping.php`

**Lokasi:** Setelah line 320 (sebelum query aggregates)

```php
// CRITICAL FIX: Get ONLY active aspect IDs to filter individual scores
$standardService = app(DynamicStandardService::class);
$activePotensiIds = $standardService->getActiveAspectIds($position->template_id, 'potensi');

// Fallback to all IDs if no adjustments (performance optimization)
if (empty($activePotensiIds)) {
    $activePotensiIds = $potensiAspectIds;
}

// 🔍 DEBUG: Verify active aspects
dd([
    'template_id' => $position->template_id,
    'template_name' => $position->template->name,
    'all_potensi_count' => count($potensiAspectIds),
    'active_potensi_count' => count($activePotensiIds),
    'all_potensi_ids' => $potensiAspectIds,
    'active_potensi_ids' => $activePotensiIds,
    'disabled_aspect_ids' => array_diff($potensiAspectIds, $activePotensiIds),
    'has_adjustments' => $standardService->hasCategoryAdjustments($position->template_id, 'potensi'),
]);

// Get aggregates - FILTER by active aspect IDs only
$aggregates = AspectAssessment::query()
    // ... rest of query
```

### Step 2: Akses Halaman Ranking Psy Mapping

1. Buka browser: `/general-report/ranking/psy-mapping`
2. Pilih Event & Position
3. Page akan stop di dd() dan show debug info

### Expected Output (Tanpa Disabled Aspects)

```php
[
    'template_id' => 2,
    'template_name' => 'Standar Asesmen Supervisor',
    'all_potensi_count' => 4,
    'active_potensi_count' => 4,
    'all_potensi_ids' => [14, 13, 15, 16],
    'active_potensi_ids' => [14, 13, 15, 16], // ✅ Sama dengan all
    'disabled_aspect_ids' => [], // ✅ Kosong
    'has_adjustments' => false,
]
```

### Expected Output (Dengan 1 Disabled Aspect)

```php
[
    'template_id' => 2,
    'template_name' => 'Standar Asesmen Supervisor',
    'all_potensi_count' => 4,
    'active_potensi_count' => 3, // ✅ Berkurang 1
    'all_potensi_ids' => [14, 13, 15, 16],
    'active_potensi_ids' => [13, 15, 16], // ✅ Aspect 14 tidak ada
    'disabled_aspect_ids' => [14], // ✅ ID yang di-disable
    'has_adjustments' => true, // ✅ Ada adjustment
]
```

---

## Method 2: Debugging dengan Log (Production Safe)

### Step 1: Tambahkan Logging di getAggregatesData()

**File:** `app/Livewire/Pages/GeneralReport/Ranking/RankingPsyMapping.php`

```php
// CRITICAL FIX: Get ONLY active aspect IDs to filter individual scores
$standardService = app(DynamicStandardService::class);
$activePotensiIds = $standardService->getActiveAspectIds($position->template_id, 'potensi');

// Fallback to all IDs if no adjustments (performance optimization)
if (empty($activePotensiIds)) {
    $activePotensiIds = $potensiAspectIds;
}

// 🔍 LOG: Verify active aspects
\Log::info('RankingPsyMapping - Active Aspects Debug', [
    'template_id' => $position->template_id,
    'all_count' => count($potensiAspectIds),
    'active_count' => count($activePotensiIds),
    'disabled_count' => count($potensiAspectIds) - count($activePotensiIds),
    'disabled_ids' => array_diff($potensiAspectIds, $activePotensiIds),
]);
```

### Step 2: Lihat Log File

```bash
tail -f storage/logs/laravel.log | grep "Active Aspects Debug"
```

**Expected Output:**
```
[2025-01-09 10:30:00] local.INFO: RankingPsyMapping - Active Aspects Debug
{
    "template_id": 2,
    "all_count": 4,
    "active_count": 3,
    "disabled_count": 1,
    "disabled_ids": [14]
}
```

---

## Method 3: Debugging dengan Ray (Recommended)

### Step 1: Install Ray (if not installed)

```bash
composer require spatie/laravel-ray --dev
```

### Step 2: Tambahkan ray() di getAggregatesData()

```php
// CRITICAL FIX: Get ONLY active aspect IDs to filter individual scores
$standardService = app(DynamicStandardService::class);
$activePotensiIds = $standardService->getActiveAspectIds($position->template_id, 'potensi');

// Fallback to all IDs if no adjustments (performance optimization)
if (empty($activePotensiIds)) {
    $activePotensiIds = $potensiAspectIds;
}

// 🔍 RAY: Verify active aspects
ray('RankingPsyMapping - Active Aspects')->purple();
ray([
    'All Aspects' => $potensiAspectIds,
    'Active Aspects' => $activePotensiIds,
    'Disabled Aspects' => array_diff($potensiAspectIds, $activePotensiIds),
])->table();

// Get aspect details
$disabledAspectIds = array_diff($potensiAspectIds, $activePotensiIds);
if (!empty($disabledAspectIds)) {
    $disabledAspects = Aspect::whereIn('id', $disabledAspectIds)->get(['id', 'name']);
    ray($disabledAspects, 'Disabled Aspects Details')->red();
}
```

### Step 3: Buka Ray Desktop App

Akan muncul real-time debugging info dengan table format.

---

## Method 4: Debugging dengan Browser DevTools

### Step 1: Tambahkan Data ke View

**File:** `app/Livewire/Pages/GeneralReport/Ranking/RankingPsyMapping.php`

Modify method `render()`:

```php
public function render()
{
    $rankings = $this->buildRankings();
    $conclusionSummary = $this->getConclusionSummary();
    $standardInfo = $this->getStandardInfo();

    // 🔍 DEBUG INFO (for development only)
    $debugInfo = null;
    if (config('app.debug')) {
        $data = $this->getAggregatesData();
        if ($data) {
            $standardService = app(DynamicStandardService::class);
            $allAspectIds = Aspect::where('category_type_id', $data['potensiCategory']->id)
                ->pluck('id')->toArray();
            $activeAspectIds = $standardService->getActiveAspectIds(
                $data['position']->template_id,
                'potensi'
            );

            $debugInfo = [
                'template' => $data['position']->template->name,
                'all_aspects' => count($allAspectIds),
                'active_aspects' => count($activeAspectIds),
                'disabled_aspects' => count($allAspectIds) - count($activeAspectIds),
                'disabled_aspect_ids' => array_diff($allAspectIds, $activeAspectIds),
            ];
        }
    }

    return view('livewire.pages.general-report.ranking.ranking-psy-mapping', [
        'rankings' => $rankings,
        'conclusionSummary' => $conclusionSummary,
        'standardInfo' => $standardInfo,
        'debugInfo' => $debugInfo, // ✅ Pass to view
    ]);
}
```

### Step 2: Tampilkan di View (Development Only)

**File:** `resources/views/livewire/pages/general-report/ranking/ranking-psy-mapping.blade.php`

Tambahkan di bagian atas (setelah header):

```blade
@if(config('app.debug') && $debugInfo)
<div class="mb-4 p-4 bg-yellow-100 border border-yellow-400 rounded dark:bg-yellow-900 dark:border-yellow-600">
    <h3 class="font-bold text-yellow-800 dark:text-yellow-200">🔍 Debug: Active Aspects Filter</h3>
    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
        <p><strong>Template:</strong> {{ $debugInfo['template'] }}</p>
        <p><strong>All Aspects:</strong> {{ $debugInfo['all_aspects'] }}</p>
        <p><strong>Active Aspects:</strong> {{ $debugInfo['active_aspects'] }}</p>
        <p><strong>Disabled Aspects:</strong> {{ $debugInfo['disabled_aspects'] }}</p>
        @if(!empty($debugInfo['disabled_aspect_ids']))
            <p><strong>Disabled IDs:</strong> {{ implode(', ', $debugInfo['disabled_aspect_ids']) }}</p>
        @endif
    </div>
</div>
@endif
```

### Expected Output di Browser

**Tanpa Disabled Aspects:**
```
🔍 Debug: Active Aspects Filter
Template: Standar Asesmen Supervisor
All Aspects: 4
Active Aspects: 4
Disabled Aspects: 0
```

**Dengan Disabled Aspects:**
```
🔍 Debug: Active Aspects Filter
Template: Standar Asesmen Supervisor
All Aspects: 4
Active Aspects: 3
Disabled Aspects: 1
Disabled IDs: 14
```

---

## Checklist Verification

Saat melakukan debugging, verify hal-hal berikut:

### ✅ Checklist 1: Aspect IDs

- [ ] `$potensiAspectIds` berisi ALL aspect IDs dari database
- [ ] `$activePotensiIds` berisi FILTERED aspect IDs (exclude disabled)
- [ ] Jika ada disabled aspects: `count($activePotensiIds) < count($potensiAspectIds)`
- [ ] Jika tidak ada disabled aspects: `count($activePotensiIds) === count($potensiAspectIds)`

### ✅ Checklist 2: Query Filter

- [ ] Query menggunakan `whereIn('aspect_assessments.aspect_id', $activePotensiIds)`
- [ ] TIDAK menggunakan `whereIn('aspect_assessments.aspect_id', $potensiAspectIds)`

### ✅ Checklist 3: Disabled Aspect Details

Untuk setiap disabled aspect ID, verify:

```php
$disabledId = 14; // Example
$aspect = Aspect::find($disabledId);

// Check in session
$service = app(\App\Services\DynamicStandardService::class);
$isActive = $service->isAspectActive($templateId, $aspect->code);

// Should be FALSE if properly disabled
assert($isActive === false, "Aspect should be disabled");
```

### ✅ Checklist 4: Score Calculation

Verify bahwa individual scores TIDAK include disabled aspects:

```php
// Get individual score for disabled aspect
$disabledAspectScore = DB::table('aspect_assessments')
    ->where('participant_id', $participantId)
    ->where('aspect_id', $disabledId)
    ->value('individual_score');

// Get total individual score (from query)
$totalScore = $aggregates->where('participant_id', $participantId)
    ->first()
    ->sum_individual_score;

// Verify disabled aspect score is NOT included in total
// If it's included (BUG), total would be higher
```

---

## Debugging RankingMcMapping & RekapRankingAssessment

Sama pattern-nya, hanya berbeda di variabel:

### RankingMcMapping (Kompetensi)

```php
// Line 278-296
$kompetensiAspectIds = Aspect::query()->where(...)->pluck('id')->all();
$activeKompetensiIds = $standardService->getActiveAspectIds($position->template_id, 'kompetensi');

dd([
    'all_kompetensi_count' => count($kompetensiAspectIds),
    'active_kompetensi_count' => count($activeKompetensiIds),
    'disabled_aspect_ids' => array_diff($kompetensiAspectIds, $activeKompetensiIds),
]);
```

### RekapRankingAssessment (Potensi + Kompetensi)

```php
// Line 307-317
$activePotensiIds = $standardService->getActiveAspectIds($position->template_id, 'potensi');
$activeKompetensiIds = $standardService->getActiveAspectIds($position->template_id, 'kompetensi');

dd([
    'potensi' => [
        'all' => count($potensiAspectIds),
        'active' => count($activePotensiIds),
        'disabled' => array_diff($potensiAspectIds, $activePotensiIds),
    ],
    'kompetensi' => [
        'all' => count($kompetensiAspectIds),
        'active' => count($activeKompetensiIds),
        'disabled' => array_diff($kompetensiAspectIds, $activeKompetensiIds),
    ],
]);
```

---

## Common Issues & Solutions

### Issue 1: $activePotensiIds always equals $potensiAspectIds

**Cause:** No aspects are disabled in session.

**Solution:**
1. Go to Standar Pemetaan page
2. Use SelectiveAspectsModal to disable some aspects
3. Verify in session: `session('standard_adjustment.{templateId}.active_aspects')`

---

### Issue 2: $activePotensiIds is empty

**Cause 1:** Template has no aspects for the category.

**Solution:** Check database:
```php
Aspect::where('template_id', $templateId)
    ->whereHas('categoryType', fn($q) => $q->where('code', 'potensi'))
    ->count(); // Should be > 0
```

**Cause 2:** All aspects are disabled (validation should prevent this).

**Solution:** Reset adjustments or enable at least 3 aspects.

---

### Issue 3: Session data not found

**Cause:** Session expired or not set.

**Solution:**
```php
// Check session data
dd(session('standard_adjustment'));

// Should return array with template_id as key
```

---

## Quick Debug Script (Tinker)

```php
// Quick check if active filtering is working
$service = app(\App\Services\DynamicStandardService::class);
$templateId = 2; // Change to your template ID

// Disable 1 aspect
$aspect = \App\Models\Aspect::where('template_id', $templateId)
    ->whereHas('categoryType', fn($q) => $q->where('code', 'potensi'))
    ->first();

$service->setAspectActive($templateId, $aspect->code, false);

// Verify it's excluded
$activeIds = $service->getActiveAspectIds($templateId, 'potensi');

return [
    'disabled_aspect' => [
        'id' => $aspect->id,
        'name' => $aspect->name,
        'is_excluded' => !in_array($aspect->id, $activeIds),
    ],
    'test_result' => !in_array($aspect->id, $activeIds) ? '✅ PASS' : '❌ FAIL',
];
```

---

## References

- [TESTING_ACTIVE_ASPECTS_FILTER.md](./TESTING_ACTIVE_ASPECTS_FILTER.md)
- [DynamicStandardService.php](../app/Services/DynamicStandardService.php)
- [RankingPsyMapping.php](../app/Livewire/Pages/GeneralReport/Ranking/RankingPsyMapping.php)
