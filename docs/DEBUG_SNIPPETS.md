# Debug Code Snippets - Quick Copy-Paste

Copy-paste snippets ini ke file yang relevan untuk debugging active aspects filter.

---

## 🔍 Snippet 1: Basic Debug dengan dd()

**Location:** `app/Livewire/Pages/GeneralReport/Ranking/RankingPsyMapping.php`
**Line:** Setelah line 320 (di method `getAggregatesData()`)

```php
// CRITICAL FIX: Get ONLY active aspect IDs to filter individual scores
$standardService = app(DynamicStandardService::class);
$activePotensiIds = $standardService->getActiveAspectIds($position->template_id, 'potensi');

// Fallback to all IDs if no adjustments
if (empty($activePotensiIds)) {
    $activePotensiIds = $potensiAspectIds;
}

// 🔍 DEBUG START - Remove after testing
dd([
    '🏷️ Template Info' => [
        'id' => $position->template_id,
        'name' => $position->template->name,
    ],
    '📊 Aspect Counts' => [
        'all_potensi' => count($potensiAspectIds),
        'active_potensi' => count($activePotensiIds),
        'disabled_count' => count($potensiAspectIds) - count($activePotensiIds),
    ],
    '🔢 Aspect IDs' => [
        'all_ids' => $potensiAspectIds,
        'active_ids' => $activePotensiIds,
        'disabled_ids' => array_values(array_diff($potensiAspectIds, $activePotensiIds)),
    ],
    '⚙️ Service Status' => [
        'has_adjustments' => $standardService->hasCategoryAdjustments($position->template_id, 'potensi'),
    ],
]);
// 🔍 DEBUG END

// Get aggregates - FILTER by active aspect IDs only
$aggregates = AspectAssessment::query()
```

**Remove after testing!**

---

## 🔍 Snippet 2: Debug dengan Aspect Details

**Location:** Same as above

```php
// CRITICAL FIX: Get ONLY active aspect IDs to filter individual scores
$standardService = app(DynamicStandardService::class);
$activePotensiIds = $standardService->getActiveAspectIds($position->template_id, 'potensi');

if (empty($activePotensiIds)) {
    $activePotensiIds = $potensiAspectIds;
}

// 🔍 DEBUG START - Remove after testing
$disabledIds = array_diff($potensiAspectIds, $activePotensiIds);
$allAspects = Aspect::whereIn('id', $potensiAspectIds)->get(['id', 'name', 'code', 'weight_percentage']);
$activeAspects = $allAspects->whereIn('id', $activePotensiIds);
$disabledAspects = $allAspects->whereIn('id', $disabledIds);

dd([
    'ACTIVE ASPECTS' => $activeAspects->map(fn($a) => [
        'id' => $a->id,
        'name' => $a->name,
        'weight' => $a->weight_percentage,
        'is_active_in_service' => $standardService->isAspectActive($position->template_id, $a->code),
    ]),
    'DISABLED ASPECTS' => $disabledAspects->map(fn($a) => [
        'id' => $a->id,
        'name' => $a->name,
        'weight' => $a->weight_percentage,
        'is_active_in_service' => $standardService->isAspectActive($position->template_id, $a->code),
    ]),
]);
// 🔍 DEBUG END
```

**Remove after testing!**

---

## 🔍 Snippet 3: Debug dengan Score Comparison

**Location:** Same as above, AFTER query aggregates (line ~332)

```php
// Get aggregates - FILTER by active aspect IDs only
$aggregates = AspectAssessment::query()
    ->selectRaw('aspect_assessments.participant_id, SUM(standard_rating) as sum_original_standard_rating, SUM(standard_score) as sum_original_standard_score, SUM(individual_rating) as sum_individual_rating, SUM(individual_score) as sum_individual_score')
    ->join('participants', 'participants.id', '=', 'aspect_assessments.participant_id')
    ->where('aspect_assessments.event_id', $event->id)
    ->where('aspect_assessments.position_formation_id', $positionFormationId)
    ->whereIn('aspect_assessments.aspect_id', $activePotensiIds)
    ->groupBy('aspect_assessments.participant_id', 'participants.name')
    ->orderByDesc('sum_individual_score')
    ->orderByRaw('LOWER(participants.name) ASC')
    ->get();

// 🔍 DEBUG START - Compare scores with/without filter
if ($aggregates->isNotEmpty()) {
    $firstParticipant = $aggregates->first();

    // Query WITHOUT filter (includes disabled aspects)
    $unfilteredScore = AspectAssessment::query()
        ->where('event_id', $event->id)
        ->where('position_formation_id', $positionFormationId)
        ->where('participant_id', $firstParticipant->participant_id)
        ->whereIn('aspect_id', $potensiAspectIds) // ALL aspects
        ->sum('individual_score');

    // Query WITH filter (excludes disabled aspects)
    $filteredScore = AspectAssessment::query()
        ->where('event_id', $event->id)
        ->where('position_formation_id', $positionFormationId)
        ->where('participant_id', $firstParticipant->participant_id)
        ->whereIn('aspect_id', $activePotensiIds) // ACTIVE only
        ->sum('individual_score');

    dd([
        'participant_id' => $firstParticipant->participant_id,
        'BEFORE FIX (all aspects)' => [
            'score' => (float) $unfilteredScore,
            'note' => 'Includes disabled aspects (BUG)',
        ],
        'AFTER FIX (active only)' => [
            'score' => (float) $filteredScore,
            'note' => 'Excludes disabled aspects (CORRECT)',
        ],
        'IMPACT' => [
            'difference' => (float) ($unfilteredScore - $filteredScore),
            'percentage' => $unfilteredScore > 0
                ? round((($unfilteredScore - $filteredScore) / $unfilteredScore) * 100, 2) . '%'
                : '0%',
        ],
        'VERIFICATION' => [
            'fix_working' => $unfilteredScore >= $filteredScore,
            'has_impact' => $unfilteredScore != $filteredScore,
        ],
    ]);
}
// 🔍 DEBUG END
```

**Remove after testing!**

---

## 🔍 Snippet 4: Persistent Debug (Log - Production Safe)

**Location:** Same as above (after getting $activePotensiIds)

```php
// CRITICAL FIX: Get ONLY active aspect IDs to filter individual scores
$standardService = app(DynamicStandardService::class);
$activePotensiIds = $standardService->getActiveAspectIds($position->template_id, 'potensi');

if (empty($activePotensiIds)) {
    $activePotensiIds = $potensiAspectIds;
}

// 🔍 LOG: Production-safe debugging
\Log::channel('daily')->info('RankingPsyMapping - Active Aspects Filter', [
    'template_id' => $position->template_id,
    'template_name' => $position->template->name,
    'all_count' => count($potensiAspectIds),
    'active_count' => count($activePotensiIds),
    'disabled_count' => count($potensiAspectIds) - count($activePotensiIds),
    'disabled_ids' => array_values(array_diff($potensiAspectIds, $activePotensiIds)),
    'has_adjustments' => $standardService->hasCategoryAdjustments($position->template_id, 'potensi'),
    'timestamp' => now()->toDateTimeString(),
]);

// Continue with normal flow...
```

**Can stay in production** (safe for production, logs to `storage/logs/laravel.log`)

**View logs:**
```bash
tail -f storage/logs/laravel.log | grep "Active Aspects Filter"
```

---

## 🔍 Snippet 5: Debug in View (Visual Feedback)

**Location:** `resources/views/livewire/pages/general-report/ranking/ranking-psy-mapping.blade.php`
**Position:** After header, before table

```blade
{{-- 🔍 DEBUG: Active Aspects Info (Development Only) --}}
@if(config('app.debug'))
    @php
        $debugData = $this->getAggregatesData();
        if ($debugData) {
            $service = app(\App\Services\DynamicStandardService::class);
            $position = $debugData['position'];
            $allIds = \App\Models\Aspect::where('category_type_id', $debugData['potensiCategory']->id)
                ->pluck('id')->toArray();
            $activeIds = $service->getActiveAspectIds($position->template_id, 'potensi');
            $disabledIds = array_diff($allIds, $activeIds);
            $disabledAspects = \App\Models\Aspect::whereIn('id', $disabledIds)->pluck('name')->toArray();
        }
    @endphp

    @if(isset($debugData) && $debugData)
        <div class="mb-4 p-4 bg-blue-50 border-l-4 border-blue-500 dark:bg-blue-900/20 dark:border-blue-400">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                        🔍 Debug: Active Aspects Filter
                    </h3>
                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                        <ul class="list-disc list-inside space-y-1">
                            <li><strong>Template:</strong> {{ $position->template->name }}</li>
                            <li><strong>Total Aspects:</strong> {{ count($allIds) }}</li>
                            <li><strong>Active Aspects:</strong>
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded dark:bg-green-800 dark:text-green-100">
                                    {{ count($activeIds) }}
                                </span>
                            </li>
                            <li><strong>Disabled Aspects:</strong>
                                <span class="px-2 py-1 {{ count($disabledIds) > 0 ? 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100' }} rounded">
                                    {{ count($disabledIds) }}
                                </span>
                            </li>
                            @if(count($disabledAspects) > 0)
                                <li><strong>Disabled:</strong> {{ implode(', ', $disabledAspects) }}</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif
{{-- 🔍 END DEBUG --}}
```

**Automatically shows/hides based on `APP_DEBUG`**

---

## 🔍 Snippet 6: Quick Tinker Test

**Usage:** Copy-paste ke `php artisan tinker`

```php
// Quick test for RankingPsyMapping active aspects filter
$templateId = 2; // Change to your template ID
$service = app(\App\Services\DynamicStandardService::class);

// Get baseline (no disabled aspects)
$baselineCount = count($service->getActiveAspectIds($templateId, 'potensi'));

// Disable 1 aspect
$aspect = \App\Models\Aspect::where('template_id', $templateId)
    ->whereHas('categoryType', fn($q) => $q->where('code', 'potensi'))
    ->first();

if ($aspect) {
    $service->setAspectActive($templateId, $aspect->code, false);

    // Get active count after disable
    $afterDisableCount = count($service->getActiveAspectIds($templateId, 'potensi'));

    return [
        '✅ Test Result' => [
            'baseline_count' => $baselineCount,
            'after_disable_count' => $afterDisableCount,
            'disabled_aspect' => [
                'id' => $aspect->id,
                'name' => $aspect->name,
            ],
            'count_decreased' => $afterDisableCount < $baselineCount,
            'test_passed' => $afterDisableCount === ($baselineCount - 1),
        ],
        '🎯 Verdict' => $afterDisableCount === ($baselineCount - 1)
            ? '✅ PASS - Filter working correctly!'
            : '❌ FAIL - Filter not working!',
    ];
} else {
    return '❌ No aspects found for template ' . $templateId;
}
```

---

## 🔍 Snippet 7: Comprehensive Debug (All Info)

**Location:** `app/Livewire/Pages/GeneralReport/Ranking/RankingPsyMapping.php`
**Line:** After line 320

```php
// CRITICAL FIX: Get ONLY active aspect IDs to filter individual scores
$standardService = app(DynamicStandardService::class);
$activePotensiIds = $standardService->getActiveAspectIds($position->template_id, 'potensi');

if (empty($activePotensiIds)) {
    $activePotensiIds = $potensiAspectIds;
}

// 🔍 COMPREHENSIVE DEBUG START
$disabledIds = array_diff($potensiAspectIds, $activePotensiIds);
$allAspects = Aspect::whereIn('id', $potensiAspectIds)
    ->with('subAspects')
    ->orderBy('order')
    ->get();

$debugInfo = [
    '📋 OVERVIEW' => [
        'template_id' => $position->template_id,
        'template_name' => $position->template->name,
        'event_id' => $event->id,
        'event_name' => $event->name,
        'position_id' => $position->id,
        'position_name' => $position->name,
    ],
    '📊 COUNTS' => [
        'total_aspects' => count($potensiAspectIds),
        'active_aspects' => count($activePotensiIds),
        'disabled_aspects' => count($disabledIds),
        'filtering_active' => count($disabledIds) > 0,
    ],
    '🔢 ASPECT IDS' => [
        'all_ids' => $potensiAspectIds,
        'active_ids' => $activePotensiIds,
        'disabled_ids' => array_values($disabledIds),
    ],
    '📝 ASPECT DETAILS' => $allAspects->map(function($aspect) use ($standardService, $position, $activePotensiIds) {
        $isActive = in_array($aspect->id, $activePotensiIds);
        return [
            'id' => $aspect->id,
            'name' => $aspect->name,
            'code' => $aspect->code,
            'weight' => $aspect->weight_percentage,
            'is_active' => $isActive,
            'status' => $isActive ? '✅ ACTIVE' : '❌ DISABLED',
            'service_check' => $standardService->isAspectActive($position->template_id, $aspect->code),
            'sub_aspects_count' => $aspect->subAspects ? $aspect->subAspects->count() : 0,
        ];
    }),
    '⚙️ SERVICE STATUS' => [
        'has_adjustments' => $standardService->hasCategoryAdjustments($position->template_id, 'potensi'),
        'session_key' => 'standard_adjustment.' . $position->template_id,
        'session_data_exists' => session()->has('standard_adjustment.' . $position->template_id),
    ],
];

dd($debugInfo);
// 🔍 COMPREHENSIVE DEBUG END
```

**Remove after testing!**

---

## How to Use These Snippets

### For Quick Testing (dd):
1. Copy Snippet 1, 2, or 3
2. Paste into `RankingPsyMapping.php` at specified line
3. Refresh page in browser
4. Page will stop at dd() and show debug info
5. **Remove snippet after testing!**

### For Production Monitoring (Log):
1. Copy Snippet 4
2. Paste into `RankingPsyMapping.php`
3. Can stay in production
4. Monitor with: `tail -f storage/logs/laravel.log | grep "Active Aspects"`

### For Visual Feedback (View):
1. Copy Snippet 5
2. Paste into blade view
3. Only shows when `APP_DEBUG=true`
4. Can stay in code (auto-hides in production)

### For Quick Verification (Tinker):
1. Run: `php artisan tinker`
2. Copy-paste Snippet 6
3. Instant verification
4. No code changes needed

---

## Expected Results

### ✅ Success Indicators

All snippets should show:
- `active_count < all_count` (when aspects are disabled)
- `disabled_count > 0` (when aspects are disabled)
- `disabled_ids` contains aspect IDs that are disabled
- `test_passed => true` or `PASS` verdict

### ❌ Failure Indicators

- `active_count === all_count` (when aspects ARE disabled) → **BUG**
- `disabled_count === 0` (when aspects ARE disabled) → **BUG**
- Empty `disabled_ids` (when aspects ARE disabled) → **BUG**
- `test_passed => false` or `FAIL` verdict → **BUG**

---

## Cleanup Reminder

**IMPORTANT:** Remove all `dd()` snippets after testing!

Snippets safe to keep:
- ✅ Snippet 4 (Log) - Production safe
- ✅ Snippet 5 (View) - Auto-hides in production
- ❌ Snippet 1, 2, 3, 7 - MUST remove (contains dd())

---

## References

- [DEBUGGING_ACTIVE_ASPECTS.md](./DEBUGGING_ACTIVE_ASPECTS.md)
- [TESTING_ACTIVE_ASPECTS_FILTER.md](./TESTING_ACTIVE_ASPECTS_FILTER.md)
