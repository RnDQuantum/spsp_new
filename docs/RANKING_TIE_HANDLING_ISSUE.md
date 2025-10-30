# Ranking Tie Handling Issue

## Problem Description

Sistem ranking saat ini menggunakan **Sequential Ranking** (1, 2, 3, 4, 5...) yang tidak menangani kasus tie (participants dengan score dan rating yang sama) dengan benar.

## Current Behavior (Sequential Ranking)

Ketika ada participants dengan score dan rating yang sama, mereka mendapat ranking yang berbeda berdasarkan urutan dalam database:

```
Rank #47: Participant A (Score: 378, Rating: 34)
Rank #48: Participant B (Score: 378, Rating: 34)  ❌ Seharusnya juga #47
Rank #49: Participant C (Score: 378, Rating: 34)  ❌ Seharusnya juga #47
Rank #50: Participant D (Score: 377, Rating: 34)
```

## Expected Behavior (Standard Competition Ranking)

Participants dengan score dan rating yang sama **seharusnya mendapat ranking yang sama**:

```
Rank #47: Participant A (Score: 378, Rating: 34)
Rank #47: Participant B (Score: 378, Rating: 34)  ✅ Correct
Rank #47: Participant C (Score: 378, Rating: 34)  ✅ Correct
Rank #50: Participant D (Score: 377, Rating: 34)  (skip rank 48 & 49)
```

## Types of Ranking Methods

1. **Sequential Ranking** (Current): `1, 2, 3, 4, 5, 6...`
   - Setiap participant mendapat ranking berbeda
   - Tidak fair untuk ties

2. **Standard Competition Ranking** (Recommended): `1, 2, 2, 4, 5, 5, 7...`
   - Participants dengan score sama mendapat ranking sama
   - Skip ranking setelah tie
   - Ini yang digunakan di kompetisi olahraga (Olympic, etc.)

3. **Dense Ranking**: `1, 2, 2, 3, 4, 4, 5...`
   - Participants dengan score sama mendapat ranking sama
   - Tidak skip ranking setelah tie

## Impact

### 1. Inconsistency Between Components

**Problem:** GeneralMcMapping dan RankingMcMapping menunjukkan ranking berbeda untuk participant yang sama.

**Cause:**
- Pagination di RankingMcMapping bisa mengubah urutan karena tidak ada deterministic ORDER BY
- Sequential ranking membuat urutan bergantung pada `participant_id` tiebreaker

### 2. Unfair Ranking

Participants dengan performa identik mendapat ranking berbeda, yang tidak adil dan tidak akurat.

## Affected Components

1. `app/Livewire/Pages/GeneralReport/Ranking/RankingMcMapping.php`
   - Method: `buildRankings()`
   - Line: ~229 (`'rank' => $startRank + $index + 1`)

2. `app/Livewire/Pages/GeneralReport/Ranking/RankingPsyMapping.php`
   - Method: `buildRankings()`
   - Line: ~220 (`'rank' => $startRank + $index + 1`)

3. `app/Livewire/Pages/IndividualReport/GeneralMcMapping.php`
   - Method: `getParticipantRanking()`
   - Line: ~412 (`$rank = $index + 1`)

4. `app/Livewire/Pages/IndividualReport/GeneralPsyMapping.php`
   - Method: `getParticipantRanking()`
   - Line: ~411 (`$rank = $index + 1`)

## Solution (To Be Implemented)

### Algorithm for Standard Competition Ranking

```php
$currentRank = 0;
$previousScore = null;
$previousRating = null;

foreach ($allParticipants as $index => $participant) {
    $score = (float) $participant->sum_individual_score;
    $rating = (float) $participant->sum_individual_rating;

    // Check if score/rating changed
    if ($score !== $previousScore || $rating !== $previousRating) {
        // New rank group - use actual position (index + 1)
        $currentRank = $index + 1;
    }
    // else: keep the same rank as previous

    // Assign rank
    $participant->rank = $currentRank;

    $previousScore = $score;
    $previousRating = $rating;
}
```

### Implementation Steps

1. **Update RankingMcMapping::buildRankings()**
   - Implement tie detection logic
   - Calculate proper rank before mapping

2. **Update RankingPsyMapping::buildRankings()**
   - Same as above

3. **Update GeneralMcMapping::getParticipantRanking()**
   - Implement tie detection in loop
   - Return proper rank

4. **Update GeneralPsyMapping::getParticipantRanking()**
   - Same as above

5. **Add Tests**
   - Test tie handling with multiple participants having same scores
   - Verify ranking consistency across components

## Current Workaround (Temporary)

Untuk sementara, ranking menggunakan sequential method dengan `participant_id` sebagai tiebreaker untuk memastikan:
- **Deterministic ordering**: Urutan selalu konsisten
- **No pagination bugs**: Tidak ada duplicate/missing participants

Query pattern yang digunakan:
```php
->orderByDesc('sum_individual_score')
->orderByDesc('sum_individual_rating')
->orderBy('participant_id')  // Tiebreaker untuk konsistensi
```

## Priority

**Medium-High** - Issue ini mempengaruhi fairness dan akurasi ranking system, tapi tidak critical karena:
- Data masih menggunakan seeder
- Sistem berfungsi, hanya tidak optimal untuk tie cases
- Workaround sudah diterapkan untuk konsistensi

## References

- [Wikipedia: Ranking](https://en.wikipedia.org/wiki/Ranking)
- [Standard Competition Ranking (1224 ranking)](https://en.wikipedia.org/wiki/Ranking#Standard_competition_ranking_(%221224%22_ranking))

---

**Created:** 2025-01-30
**Status:** To Be Fixed
**Assigned:** TBD
