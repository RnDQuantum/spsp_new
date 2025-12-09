# Phase 2 Optimization - TODO List

## ✅ Completed (Phase 1)

- [x] Analyze RekapRankingAssessment performance bottleneck
- [x] Eliminate duplicate service calls in RekapRankingAssessment
- [x] Implement local combined ranking calculation
- [x] Add smart caching for potensi/kompetensi rankings
- [x] Document Phase 1 results (3.84s → 2.32s)
- [x] Analyze database indexes
- [x] Identify bottleneck: 2 slow queries (923ms/946ms)
- [x] Create detailed optimization analysis document

## 🚀 TODO (Phase 2) - In Priority Order

### Strategy A: Database Index (HIGHEST PRIORITY)
- [ ] Create migration file for composite index
- [ ] Add index: `idx_asp_event_pos_aspect_participant`
- [ ] Run migration
- [ ] Test with EXPLAIN to verify index usage
- [ ] Measure improvement with debug bar
- [ ] Expected: 923ms → ~300ms (60-70% faster)

### Strategy B: Remove ORDER BY
- [ ] Modify `RankingService.php` line ~75
- [ ] Remove `->orderBy('participants.name')`
- [ ] Test ranking order consistency
- [ ] Verify filesort is eliminated (check EXPLAIN)
- [ ] Measure improvement with debug bar
- [ ] Expected: Additional 15-25% improvement

### Strategy C: Selective Column Selection
- [ ] Modify `RankingService.php` line ~74
- [ ] Change SELECT to only fetch needed columns
- [ ] Test for any missing data errors
- [ ] Verify all calculations work correctly
- [ ] Measure improvement with debug bar
- [ ] Expected: Additional 10-15% improvement

### Strategy D: Combined Query (OPTIONAL)
- [ ] Create new method `getRankingsCombined()` in RankingService
- [ ] Fetch both categories in single query
- [ ] Split results by aspect_id in PHP
- [ ] Update RekapRankingAssessment to use new method
- [ ] Comprehensive testing
- [ ] Compare results with old implementation
- [ ] Expected: 40-50% improvement for RekapRankingAssessment

## 📋 Testing Checklist (After Each Strategy)

- [ ] Run `vendor/bin/pint --dirty`
- [ ] Check debug bar query count
- [ ] Check debug bar query time
- [ ] Verify ranking order
- [ ] Test different events
- [ ] Test different positions
- [ ] Test pagination
- [ ] Test tolerance adjustment
- [ ] Test standard adjustment
- [ ] Compare with pre-optimization baseline

## 📊 Target Metrics

| Component | Current | Target | Status |
|-----------|---------|--------|--------|
| RankingPsyMapping | 1.26s | ~0.65s | ⏳ Pending |
| RankingMcMapping | 1.33s | ~0.70s | ⏳ Pending |
| RekapRankingAssessment | 2.32s | ~0.8-1.0s | ⏳ Pending |

## 📝 Documentation Updates Needed

- [ ] Update OPTIMIZATION_ANALYSIS_PHASE2.md with actual results
- [ ] Create new case study for Phase 2
- [ ] Document any issues encountered
- [ ] Update CLAUDE.md if patterns change

## ⚠️ Pre-flight Checks

Before starting implementation:
- [ ] Backup database (or ensure can rollback)
- [ ] Check disk space for new index (~50-100MB)
- [ ] Verify no production deployments during testing
- [ ] Ensure debug bar is enabled
- [ ] Git commit current state

## 🎯 Success Criteria

Phase 2 is complete when:
- [ ] RekapRankingAssessment loads in < 1 second
- [ ] Query time reduced by at least 50%
- [ ] No regressions in functionality
- [ ] All tests passing
- [ ] Documentation updated
