# Performance Optimization Analysis - Phase 2
## Ranking System Deep Dive

**Date**: December 2024
**Focus**: Service-Layer & Database Optimization
**Previous Phase**: [CASE_STUDY_RANKING_OPTIMIZATION.md](CASE_STUDY_RANKING_OPTIMIZATION.md)

---

## 📊 Current State Analysis

### **Database Statistics**
- **aspect_assessments**: 439,817 rows
- **participants**: 35,000 rows
- **Target query**: Fetches ~5,000-8,000 rows per category

### **Performance Metrics (After Phase 1)**

| Component | Request Time | Query Time | Total Queries | Duplicates |
|-----------|--------------|------------|---------------|------------|
| RankingPsyMapping | 1.26s | 388ms | 28 | 9 |
| RankingMcMapping | 1.33s | 552ms | 28 | 9 |
| **RekapRankingAssessment** | **2.32s** | **946ms** | **40** | **19** |

### **Critical Bottleneck Identified**

**97.6% of query time** is consumed by just **2 queries**:

```sql
-- Query 1: Potensi Category (378ms - 40% of total)
SELECT aspect_assessments.*, participants.name as participant_name
FROM aspect_assessments
INNER JOIN participants ON participants.id = aspect_assessments.participant_id
WHERE aspect_assessments.event_id = 1
  AND aspect_assessments.position_formation_id = 4
  AND aspect_assessments.aspect_id IN (2, 4, 1, 5, 3)
ORDER BY participants.name ASC

-- Query 2: Kompetensi Category (545ms - 58% of total)
SELECT aspect_assessments.*, participants.name as participant_name
FROM aspect_assessments
INNER JOIN participants ON participants.id = aspect_assessments.participant_id
WHERE aspect_assessments.event_id = 1
  AND aspect_assessments.position_formation_id = 4
  AND aspect_assessments.aspect_id IN (6, 7, 8, 12, 9, 10, 11)
ORDER BY participants.name ASC
```

**Total Bottleneck**: 923ms / 946ms (97.6%)

---

## 🔍 Database Index Analysis

### **Current Indexes on `aspect_assessments`**

| Index Name | Columns | Purpose | Cardinality |
|------------|---------|---------|-------------|
| PRIMARY | id | Primary key | 402,420 |
| idx_asp_event_aspect | event_id, aspect_id | Event + aspect lookup | Low (69) |
| idx_asp_position_aspect | position_formation_id, aspect_id | **Used by current query** | Low (89) |
| idx_asp_participant_aspect | participant_id, aspect_id | Participant lookup | High (403,002) |

### **Current Indexes on `participants`**

| Index Name | Columns | Purpose | Cardinality |
|------------|---------|---------|-------------|
| PRIMARY | id | Primary key | 34,672 |
| participants_name_index | name | Name sorting | 33,027 |
| idx_participants_event_position_name | event_id, position_formation_id, name | Composite | 34,672 |

---

## 🚨 Performance Issues Identified

### **Issue 1: Suboptimal Index Usage**
```
EXPLAIN output shows:
- Using index: idx_asp_position_aspect
- Rows examined: 44,880 (too many!)
- Extra: Using temporary; Using filesort (⚠️ expensive!)
```

**Problem**: Current index doesn't cover all WHERE conditions efficiently.

**Current index**: `(position_formation_id, aspect_id)`
**Query needs**: `(event_id, position_formation_id, aspect_id)`

**Impact**: MySQL can't use the index optimally and must:
1. Create temporary table for JOIN
2. Use filesort for ORDER BY

---

### **Issue 2: ORDER BY Filesort**
```sql
ORDER BY participants.name ASC
```

**Problem**: Sorting 5,000+ rows in memory using filesort.

**Impact**: Adds ~100-200ms per query.

---

### **Issue 3: Fetching Unnecessary Columns**
```sql
SELECT aspect_assessments.*, participants.name
```

**Problem**: Fetches 18 columns when only ~8 are needed.

**Columns fetched**: id, category_assessment_id, participant_id, event_id, batch_id, position_formation_id, aspect_id, standard_rating, standard_score, individual_rating, individual_score, gap_rating, gap_score, percentage_score, conclusion_code, conclusion_text, created_at, updated_at

**Columns needed**: participant_id, aspect_id, individual_rating, individual_score (+ participant.name)

**Impact**: Higher I/O, larger memory footprint.

---

## 🎯 Optimization Strategies

### **Strategy A: Composite Index Optimization** ⚡ **HIGHEST IMPACT**

**Create**: New composite index covering all WHERE conditions + JOIN column

```sql
CREATE INDEX idx_asp_event_pos_aspect_participant
ON aspect_assessments(event_id, position_formation_id, aspect_id, participant_id);
```

**Benefits**:
- ✅ Index covers all WHERE clause conditions
- ✅ Includes participant_id for efficient JOIN
- ✅ Eliminates "Using temporary"
- ✅ Enables "Using index condition"

**Expected Improvement**: **60-70% faster** (378ms + 545ms → 150-200ms)

**Trade-off**:
- Additional ~50-100MB storage for index
- Slight INSERT/UPDATE overhead (~5-10%)

---

### **Strategy B: Remove ORDER BY from Query**

**Change**: Remove `ORDER BY participants.name` from database query, sort in PHP instead.

**Rationale**:
- We already fetch ALL rows for ranking calculation
- Sorting in PHP (using Laravel Collections) is fast for 5,000 rows
- Avoids expensive MySQL filesort

**Implementation**:
```php
// RankingService.php line 75
->orderBy('participants.name'); // ❌ REMOVE THIS

// Sorting is already done in line 153-158:
$rankings = collect($participantScores)
    ->sortBy([
        ['individual_score', 'desc'],
        ['participant_name', 'asc'],
    ]);
```

**Expected Improvement**: **15-25% faster** (saves ~50-100ms per query)

---

### **Strategy C: Selective Column Fetching**

**Change**: Only SELECT columns that are actually used.

**Before**:
```php
->select('aspect_assessments.*', 'participants.name as participant_name')
```

**After**:
```php
->select(
    'aspect_assessments.participant_id',
    'aspect_assessments.aspect_id',
    'aspect_assessments.individual_rating',
    'aspect_assessments.individual_score',
    'participants.name as participant_name'
)
```

**Expected Improvement**: **10-15% faster** (reduces I/O and memory)

---

### **Strategy D: Combined Query for RekapRankingAssessment** 💡

**Concept**: Instead of 2 separate queries (potensi + kompetensi), fetch both categories in 1 query.

**Before**:
```sql
-- Query 1: Potensi (378ms)
WHERE aspect_id IN (2, 4, 1, 5, 3)

-- Query 2: Kompetensi (545ms)
WHERE aspect_id IN (6, 7, 8, 12, 9, 10, 11)
```

**After**:
```sql
-- Single Query (400-500ms estimated)
WHERE aspect_id IN (2, 4, 1, 5, 3, 6, 7, 8, 12, 9, 10, 11)
```

Then split in PHP:
```php
$potensiIds = [2, 4, 1, 5, 3];
$kompetensiIds = [6, 7, 8, 12, 9, 10, 11];

$potensi = $results->whereIn('aspect_id', $potensiIds);
$kompetensi = $results->whereIn('aspect_id', $kompetensiIds);
```

**Benefits**:
- ✅ Only 1 query instead of 2
- ✅ Reduced connection overhead
- ✅ Simpler caching strategy

**Expected Improvement**: **40-50% faster** for RekapRankingAssessment (923ms → 400-500ms)

---

## 📈 Expected Combined Impact

### **Conservative Estimate**:

| Strategy | Individual Impact | Cumulative |
|----------|-------------------|------------|
| **A: Composite Index** | 60-70% | 923ms → 300ms |
| **B: Remove ORDER BY** | 15-25% on top | 300ms → 225ms |
| **C: Selective Columns** | 10-15% on top | 225ms → 190ms |
| **D: Combined Query** | 40-50% for Rekap | 923ms → 400ms (single query) |

### **Projected Results (Strategies A+B+C)**:

| Component | Current | Optimized | Improvement |
|-----------|---------|-----------|-------------|
| RankingPsyMapping | 1.26s | **~0.65s** | **48% faster** |
| RankingMcMapping | 1.33s | **~0.70s** | **47% faster** |
| RekapRankingAssessment | 2.32s | **~1.0s** | **57% faster** |

### **With Combined Query (Strategy D) for Rekap**:

| Component | Current | Optimized | Improvement |
|-----------|---------|-----------|-------------|
| RekapRankingAssessment | 2.32s | **~0.8s** | **65% faster** |

---

## ⚠️ Risks & Considerations

### **Risk 1: Index Size**
- New index will add ~50-100MB to database
- **Mitigation**: Monitor disk space, acceptable trade-off for 60% performance gain

### **Risk 2: Write Performance**
- INSERT/UPDATE on aspect_assessments may slow by 5-10%
- **Mitigation**: Assessments are written once, read thousands of times (read-heavy)

### **Risk 3: Query Plan Changes**
- MySQL might choose different execution plan
- **Mitigation**: Test with EXPLAIN, add FORCE INDEX if needed

### **Risk 4: Breaking Changes**
- Removing ORDER BY might affect edge cases
- **Mitigation**: Thorough testing, ensure PHP sorting is identical

---

## 🔄 Rollback Plan

If optimization causes issues:

1. **Drop new index**:
   ```sql
   DROP INDEX idx_asp_event_pos_aspect_participant ON aspect_assessments;
   ```

2. **Revert code changes**: Git revert to previous commit

3. **Restore ORDER BY**: Add back to RankingService

Expected rollback time: < 5 minutes

---

## 📋 Implementation Checklist

- [ ] Create database migration for new index
- [ ] Update RankingService to remove ORDER BY
- [ ] Update RankingService to use selective columns
- [ ] Create getCombinedRankingsOptimized() method
- [ ] Update RekapRankingAssessment to use combined query
- [ ] Run tests to ensure correctness
- [ ] Test with debug bar to measure improvements
- [ ] Update documentation with results
- [ ] Deploy to staging environment
- [ ] Monitor production performance

---

## 📚 References

- [MySQL Composite Index Best Practices](https://dev.mysql.com/doc/refman/8.0/en/multiple-column-indexes.html)
- [Laravel Query Builder Optimization](https://laravel.com/docs/12.x/queries#select-statements)
- Phase 1: [CASE_STUDY_RANKING_OPTIMIZATION.md](CASE_STUDY_RANKING_OPTIMIZATION.md)

---

**Next Steps**: Implement optimizations in order A → B → C → D, measuring each step independently.
