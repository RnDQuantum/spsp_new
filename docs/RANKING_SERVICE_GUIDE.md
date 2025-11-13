# RANKING SERVICE GUIDE

Dokumentasi lengkap untuk **RankingService** - Single Source of Truth untuk semua perhitungan ranking.

---

## OVERVIEW

**RankingService** adalah service yang menyediakan:
- ✅ **Consistent ranking logic** di semua komponen
- ✅ **DynamicStandardService integration** untuk session adjustments
- ✅ **Standardized ordering**: Score DESC → Name ASC
- ✅ **Accurate recalculation** dari active aspects & sub-aspects
- ✅ **Tolerance support** untuk adjusted standards

---

## AFFECTED COMPONENTS

RankingService digunakan oleh **6 komponen utama**:

### General Report (Ranking Section)
1. **RankingPsyMapping** - Ranking Potensi
2. **RankingMcMapping** - Ranking Kompetensi

### Individual Report (Ranking Info)
3. **GeneralPsyMapping** - Individual Potensi
4. **GeneralMcMapping** - Individual Kompetensi
5. **GeneralMapping** - Individual Combined (Potensi + Kompetensi)

### Recap/Summary
6. **RekapRankingAssessment** - Recap ranking semua category

---

## KEY FEATURES

### 1. Consistent Ordering

**Rule:** Primary: Score DESC → Secondary: Name ASC (case-insensitive)

```php
// SQL ordering
->orderByDesc('sum_individual_score')
->orderByRaw('LOWER(participants.name) ASC')
```

**Why Name ASC?**
- Deterministic: Same input → same output
- User-friendly: Alphabetical order easy to understand
- No ambiguity: Works even when scores are identical

---

### 2. DynamicStandardService Integration

**Recalculates standards based on session adjustments:**

| Adjustment | Impact |
|------------|--------|
| Disable aspect | Excluded from calculation |
| Disable sub-aspect | Recalculates aspect rating (Potensi only) |
| Adjust aspect weight | Recalculates aspect score |
| Adjust sub-aspect rating | Recalculates aspect rating (Potensi only) |
| Adjust aspect rating | Uses new rating (Kompetensi only) |

---

### 3. Category-Specific Logic

#### Potensi (with sub-aspects)

```
Calculation Flow:
1. Get active aspects
2. For each active aspect:
   - Get active sub-aspects
   - Get adjusted sub-aspect ratings
   - Calculate: aspectRating = AVG(active sub-aspects)
   - Get adjusted aspect weight
   - Calculate: aspectScore = aspectRating × weight
3. Sum all aspect scores
```

#### Kompetensi (without sub-aspects)

```
Calculation Flow:
1. Get active aspects
2. For each active aspect:
   - Get adjusted aspect rating (from session)
   - Get adjusted aspect weight
   - Calculate: aspectScore = aspectRating × weight
3. Sum all aspect scores
```

---

## PUBLIC METHODS

### 1. `getRankings()`

Get all participant rankings for a category.

**Signature:**
```php
public function getRankings(
    int $eventId,
    int $positionFormationId,
    int $templateId,
    string $categoryCode,  // 'potensi' or 'kompetensi'
    int $tolerancePercentage = 10
): Collection
```

**Returns:**
```php
[
    [
        'rank' => 1,
        'participant_id' => 123,
        'individual_rating' => 20.50,
        'individual_score' => 350.25,
        'original_standard_rating' => 18.00,
        'original_standard_score' => 320.00,
        'adjusted_standard_rating' => 16.20,  // With 10% tolerance
        'adjusted_standard_score' => 288.00,
        'original_gap_rating' => 2.50,
        'original_gap_score' => 30.25,
        'adjusted_gap_rating' => 4.30,
        'adjusted_gap_score' => 62.25,
        'percentage' => 121.60,
        'conclusion' => 'Di Atas Standar',
    ],
    // ... more participants
]
```

**Usage Example:**
```php
// In Livewire component (e.g., RankingPsyMapping)
$rankingService = app(RankingService::class);

$rankings = $rankingService->getRankings(
    $eventId,
    $positionFormationId,
    $templateId,
    'potensi',
    $this->tolerancePercentage
);

foreach ($rankings as $ranking) {
    echo "Rank {$ranking['rank']}: {$ranking['conclusion']}\n";
}
```

---

### 2. `getParticipantRank()`

Get specific participant's rank and conclusion.

**Signature:**
```php
public function getParticipantRank(
    int $participantId,
    int $eventId,
    int $positionFormationId,
    int $templateId,
    string $categoryCode,
    int $tolerancePercentage = 10
): ?array
```

**Returns:**
```php
[
    'rank' => 5,
    'total' => 50,
    'conclusion' => 'Memenuhi Standar',
    'percentage' => 95.50,
    'individual_score' => 340.00,
    'adjusted_standard_score' => 288.00,
    'adjusted_gap_score' => 52.00,
]
```

**Returns `null` if:** Participant not found or no data available.

**Usage Example:**
```php
// In Livewire component (e.g., GeneralPsyMapping)
$rankingService = app(RankingService::class);

$ranking = $rankingService->getParticipantRank(
    $this->participant->id,
    $event->id,
    $positionFormationId,
    $template->id,
    'potensi',
    $this->tolerancePercentage
);

if ($ranking) {
    echo "Rank: {$ranking['rank']} / {$ranking['total']}\n";
    echo "Conclusion: {$ranking['conclusion']}\n";
}
```

---

### 3. `calculateAdjustedStandards()`

Calculate adjusted standard values from session adjustments.

**Signature:**
```php
public function calculateAdjustedStandards(
    int $templateId,
    string $categoryCode,
    array $aspectIds
): array
```

**Returns:**
```php
[
    'standard_rating' => 18.00,  // Sum of aspect ratings
    'standard_score' => 320.00,  // Sum of aspect scores
]
```

**Usage Example:**
```php
$rankingService = app(RankingService::class);

$adjustedStandards = $rankingService->calculateAdjustedStandards(
    $templateId,
    'potensi',
    [1, 2, 3, 4, 5]  // Aspect IDs
);

echo "Standard Score: {$adjustedStandards['standard_score']}\n";
```

---

### 4. `getPassingSummary()`

Get summary statistics for passing participants.

**Signature:**
```php
public function getPassingSummary(Collection $rankings): array
```

**Input:** Rankings collection from `getRankings()`

**Returns:**
```php
[
    'total' => 50,      // Total participants
    'passing' => 42,    // Count of "Di Atas Standar" + "Memenuhi Standar"
    'percentage' => 84, // (42 / 50) × 100 = 84%
]
```

**Usage Example:**
```php
$rankings = $rankingService->getRankings(...);
$summary = $rankingService->getPassingSummary($rankings);

echo "{$summary['passing']} / {$summary['total']} passed ({$summary['percentage']}%)\n";
```

---

### 5. `getConclusionSummary()`

Get counts by conclusion type.

**Signature:**
```php
public function getConclusionSummary(Collection $rankings): array
```

**Returns:**
```php
[
    'Di Atas Standar' => 15,
    'Memenuhi Standar' => 27,
    'Di Bawah Standar' => 8,
]
```

**Usage Example:**
```php
$rankings = $rankingService->getRankings(...);
$summary = $rankingService->getConclusionSummary($rankings);

echo "Di Atas Standar: {$summary['Di Atas Standar']}\n";
echo "Memenuhi Standar: {$summary['Memenuhi Standar']}\n";
echo "Di Bawah Standar: {$summary['Di Bawah Standar']}\n";
```

---

## MIGRATION GUIDE

### Before (GeneralPsyMapping - Old Implementation)

```php
// Complex query with database values
$allParticipants = AspectAssessment::query()
    ->selectRaw('participant_id, SUM(standard_score) as sum_original_standard_score, ...')
    ->whereIn('aspect_id', $potensiAspectIds)
    ->groupBy('participant_id')
    ->orderByDesc('sum_individual_score')
    ->orderByDesc('sum_individual_rating')  // ❌ Inconsistent
    ->orderBy('participant_id')             // ❌ Inconsistent
    ->get();

// Manual calculation
foreach ($allParticipants as $index => $participant) {
    if ($participant->participant_id === $this->participant->id) {
        $rank = $index + 1;
        // ... manual gap calculation
    }
}
```

**Problems:**
- ❌ Uses database snapshot values (not session adjustments)
- ❌ Inconsistent ordering (Rating DESC → ID ASC)
- ❌ Duplicated logic across components
- ❌ Hard to maintain

---

### After (GeneralPsyMapping - New Implementation)

```php
// Clean and consistent
$rankingService = app(RankingService::class);

$ranking = $rankingService->getParticipantRank(
    $this->participant->id,
    $event->id,
    $positionFormationId,
    $template->id,
    'potensi',
    $this->tolerancePercentage
);

// Done! 🎉
```

**Benefits:**
- ✅ Uses session-adjusted values
- ✅ Consistent ordering (Score DESC → Name ASC)
- ✅ Single source of truth
- ✅ Easy to maintain

---

## TESTING CHECKLIST

After implementing RankingService:

### Test 1: Ranking Consistency
- [ ] RankingPsyMapping rank = GeneralPsyMapping rank
- [ ] RankingMcMapping rank = GeneralMcMapping rank
- [ ] Same participant → same rank in both pages

### Test 2: Ordering Consistency
- [ ] When scores are identical, participants sorted by name (A-Z)
- [ ] Case-insensitive name sorting (Andi before Budi)

### Test 3: Session Adjustments
- [ ] Disable aspect → rank changes (excluded from calculation)
- [ ] Adjust weight → rank changes (score recalculated)
- [ ] Disable sub-aspect → rank changes (Potensi only)
- [ ] Adjust rating → rank changes

### Test 4: Tolerance Effects
- [ ] Tolerance 0% → Rank based on original standard
- [ ] Tolerance 10% → Rank may change (adjusted standard lower)
- [ ] Tolerance 20% → More participants pass

### Test 5: Conclusion Logic
- [ ] Original gap >= 0 → "Di Atas Standar"
- [ ] Original gap < 0 && Adjusted gap >= 0 → "Memenuhi Standar"
- [ ] Adjusted gap < 0 → "Di Bawah Standar"

---

## PERFORMANCE TIPS

### 1. Cache Rankings in Component

```php
// In Livewire component
private ?Collection $rankingsCache = null;

public function getRankings(): Collection
{
    if ($this->rankingsCache !== null) {
        return $this->rankingsCache;
    }

    $rankingService = app(RankingService::class);
    $this->rankingsCache = $rankingService->getRankings(...);

    return $this->rankingsCache;
}

private function clearCache(): void
{
    $this->rankingsCache = null;
}
```

### 2. Clear Cache on Events

```php
protected $listeners = [
    'event-selected' => 'handleEventSelected',
    'position-selected' => 'handlePositionSelected',
    'tolerance-updated' => 'handleToleranceUpdate',
    'standard-adjusted' => 'handleStandardUpdate',  // Important!
];

public function handleEventSelected(): void
{
    $this->clearCache();
    // Reload data...
}
```

### 3. Minimize getRankings() Calls

```php
// ❌ BAD: Multiple calls
$rankings1 = $rankingService->getRankings(...);
$summary = $rankingService->getPassingSummary($rankings1);

$rankings2 = $rankingService->getRankings(...);  // Duplicate!
$conclusions = $rankingService->getConclusionSummary($rankings2);

// ✅ GOOD: Single call
$rankings = $rankingService->getRankings(...);
$summary = $rankingService->getPassingSummary($rankings);
$conclusions = $rankingService->getConclusionSummary($rankings);
```

---

## TROUBLESHOOTING

### Issue 1: Ranking tidak sama antara pages

**Symptom:** RankingPsyMapping rank = 5, GeneralPsyMapping rank = 7

**Cause:** Component belum menggunakan RankingService

**Solution:** Refactor component untuk menggunakan RankingService

---

### Issue 2: Ranking tidak reflect session adjustments

**Symptom:** User disable aspect, tapi ranking tetap sama

**Cause:** Cache tidak di-clear saat `standard-adjusted` event

**Solution:**
```php
protected $listeners = [
    'standard-adjusted' => 'handleStandardUpdate',
];

public function handleStandardUpdate(): void
{
    $this->clearCache();
    // Reload rankings...
}
```

---

### Issue 3: Ranking berubah saat reload page

**Symptom:** Same participant, different rank after refresh

**Cause:** Inconsistent ordering (tidak pakai LOWER(name))

**Solution:** RankingService sudah handle ini dengan `LOWER(participants.name) ASC`

---

## IMPLEMENTATION EXAMPLES

### Example 1: RankingPsyMapping (General Report)

```php
class RankingPsyMapping extends Component
{
    use WithPagination;

    public int $tolerancePercentage = 10;
    private ?Collection $rankingsCache = null;

    private function buildRankings(): ?LengthAwarePaginator
    {
        // Check cache
        if ($this->rankingsCache !== null) {
            return $this->paginateRankings($this->rankingsCache);
        }

        // Get rankings from service
        $rankingService = app(RankingService::class);
        $this->rankingsCache = $rankingService->getRankings(
            $eventId,
            $positionFormationId,
            $templateId,
            'potensi',
            $this->tolerancePercentage
        );

        return $this->paginateRankings($this->rankingsCache);
    }

    public function render()
    {
        $rankings = $this->buildRankings();

        // Get summary statistics
        $rankingService = app(RankingService::class);
        $summary = $rankingService->getPassingSummary($this->rankingsCache);
        $conclusions = $rankingService->getConclusionSummary($this->rankingsCache);

        return view('livewire.pages.general-report.ranking.ranking-psy-mapping', [
            'rankings' => $rankings,
            'summary' => $summary,
            'conclusions' => $conclusions,
        ]);
    }
}
```

---

### Example 2: GeneralPsyMapping (Individual Report)

```php
class GeneralPsyMapping extends Component
{
    public ?Participant $participant = null;
    public int $tolerancePercentage = 10;
    private ?array $rankingCache = null;

    public function getParticipantRanking(): ?array
    {
        // Check cache
        if ($this->rankingCache !== null) {
            return $this->rankingCache;
        }

        // Get ranking from service
        $rankingService = app(RankingService::class);
        $this->rankingCache = $rankingService->getParticipantRank(
            $this->participant->id,
            $event->id,
            $positionFormationId,
            $template->id,
            'potensi',
            $this->tolerancePercentage
        );

        return $this->rankingCache;
    }

    public function render()
    {
        $ranking = $this->getParticipantRanking();

        return view('livewire.pages.individual-report.general-psy-mapping', [
            'ranking' => $ranking,
        ]);
    }
}
```

---

### Example 3: GeneralMapping (Combined Ranking)

```php
class GeneralMapping extends Component
{
    public ?Participant $participant = null;
    public int $tolerancePercentage = 10;

    public function getCombinedRanking(): ?array
    {
        $rankingService = app(RankingService::class);

        // Get Potensi ranking
        $potensiRank = $rankingService->getParticipantRank(
            $this->participant->id,
            $event->id,
            $positionFormationId,
            $template->id,
            'potensi',
            $this->tolerancePercentage
        );

        // Get Kompetensi ranking
        $kompetensiRank = $rankingService->getParticipantRank(
            $this->participant->id,
            $event->id,
            $positionFormationId,
            $template->id,
            'kompetensi',
            $this->tolerancePercentage
        );

        // Calculate combined (weighted by category weights)
        $potensiWeight = $template->categoryTypes->where('code', 'potensi')->first()->weight_percentage;
        $kompetensiWeight = $template->categoryTypes->where('code', 'kompetensi')->first()->weight_percentage;

        $combinedScore =
            ($potensiRank['individual_score'] * $potensiWeight / 100) +
            ($kompetensiRank['individual_score'] * $kompetensiWeight / 100);

        return [
            'potensi' => $potensiRank,
            'kompetensi' => $kompetensiRank,
            'combined_score' => round($combinedScore, 2),
        ];
    }
}
```

---

## REFERENCES

- [ASSESSMENT_CALCULATION_FLOW.md](./ASSESSMENT_CALCULATION_FLOW.md) - Calculation formulas
- [LIVEWIRE_REFACTORING_GUIDE.md](./LIVEWIRE_REFACTORING_GUIDE.md) - Refactoring guidelines
- [DynamicStandardService.php](../app/Services/DynamicStandardService.php) - Session adjustments
- [RankingService.php](../app/Services/RankingService.php) - Ranking service implementation

---

**Last Updated:** 2025-01-13
**Refactored Components:** GeneralPsyMapping
**Pending Refactor:** RankingMcMapping, GeneralMcMapping, GeneralMapping, RekapRankingAssessment
