# Optimization Summary: GeneralMapping & Ranking Performance

**Date**: December 2024
**Status**: ✅ Partially Completed, ⚠️ 1 Critical Fix Pending

---

## 📊 Performance Overview

### **Completed Optimizations**

| Component | Before | After | Improvement | Status |
|-----------|--------|-------|-------------|--------|
| GeneralMapping (Quantum Default) | 1.88s | **0.60s** (cached) | **68% faster** | ✅ Done |
| GeneralMapping (Custom Standard) | 1.88s | **1.00s** (cold), **0.60s** (cached) | **47-68% faster** | ✅ Done |
| Ranking Cache | No cache | **60s TTL** | **68% faster** (subsequent) | ✅ Done |
| CustomStandard N+1 | ~50 queries | **1 query** | **98% reduction** | ✅ Done |

### **Critical Issue Found**

| Component | Current | Expected | Issue | Status |
|-----------|---------|----------|-------|--------|
| **Ranking (Custom Standard)** | **~11s** | **~1s** | 133K models hydrated | 🔴 **NEEDS FIX** |

---

## ✅ What's Been Fixed

### **1. Single-Pass Data Loading**
**File:** `IndividualAssessmentService.php`
**Problem:** Loading same data 3x from database
**Solution:** New method `getParticipantFullAssessment()` loads once, reuses everywhere
**Impact:** 20 duplicate queries eliminated

---

### **2. N+1 Query Prevention**
**File:** `IndividualAssessmentService.php`
**Problem:** 38 N+1 queries for SubAspects
**Solution:** Always eager load relationships (removed faulty conditional loading)
**Impact:** 38 queries → 4 queries

---

### **3. Smart Ranking Cache**
**File:** `RankingService.php`
**Problem:** Ranking query (475ms) executed every page load
**Solution:** 60s cache with smart invalidation (respects 3-layer priority)
**Impact:**
- First load: ~1.0s
- Cached: **~0.6s** (68% faster)
- Tolerance changes: Still instant (client-side)

---

### **4. CustomStandard Request Cache**
**File:** `DynamicStandardService.php`
**Problem:** 50+ queries to `custom_standards` table per request
**Solution:** Request-scoped cache `$customStandardCache`
**Impact:** 50 queries → 1 query per request

---

## 🔴 Critical Issue: Custom Standard Ranking Performance

### **The Problem**

When Custom Standard is selected, ranking becomes **10x slower**:

```
Quantum Default: ~1.0s ✅
Custom Standard: ~11.0s 🔴 (10x SLOWER!)
```

**Why?**
- RankingService loads **133,397 models** for Custom Standard
- Includes: 49,340 AspectAssessment + 83,878 SubAspectAssessment + relationships
- Tries to "recalculate" individual ratings from sub-aspects
- **THIS IS UNNECESSARY!** individual_rating is already in DB

---

### **The Fix**

**Principle:** Ranking should ALWAYS use lightweight query, regardless of baseline mode.

**Code Change:**
```php
// BEFORE (RankingService.php:82-91)
if ($hasSubAspectAdjustments) {
    $query->with(['aspect.subAspects', 'subAspectAssessments.subAspect']);
    $assessments = $query->get(); // ❌ 133K models, 11s
} else {
    $assessments = $query->toBase()->get(); // ✅ 0 models, 1s
}

// AFTER
$assessments = $query->toBase()->get();
// ✅ Always fast, both Default & Custom Standard
```

**Expected Result:**
```
Quantum Default: ~1.0s ✅ (same)
Custom Standard: ~1.0s ✅ (10x faster!)
```

**Documentation:** See [CRITICAL_FIX_CUSTOM_STANDARD_PERFORMANCE.md](./CRITICAL_FIX_CUSTOM_STANDARD_PERFORMANCE.md)

---

## 📁 Documentation Files

1. **[CASE_STUDY_RANKING_OPTIMIZATION.md](./CASE_STUDY_RANKING_OPTIMIZATION.md)**
   - Original ranking optimization (Phase 1 & 2)
   - Reduced from ~30s to ~0.37s
   - Techniques: Conditional eager loading, toBase(), lazy pagination

2. **[OPTIMIZATION_GENERAL_MAPPING.md](./OPTIMIZATION_GENERAL_MAPPING.md)**
   - GeneralMapping component optimization
   - Single-pass loading, N+1 prevention, ranking cache
   - 1.88s → 0.60s (68% faster)

3. **[CRITICAL_FIX_CUSTOM_STANDARD_PERFORMANCE.md](./CRITICAL_FIX_CUSTOM_STANDARD_PERFORMANCE.md)** 🔴
   - **CRITICAL:** Custom Standard 10x slowdown
   - **STATUS:** Needs implementation
   - Fix will make Custom Standard as fast as Quantum Default

---

## 🎯 Current Performance Metrics

### **GeneralMapping (Quantum Default)**

| Metric | Value | Status |
|--------|-------|--------|
| Request Time | ~0.60s (cached) | ✅ Excellent |
| Query Time | ~25ms (cached) | ✅ Excellent |
| Total Queries | ~18 (cached) | ✅ Good |
| Models Retrieved | ~178 | ✅ Good |

### **GeneralMapping (Custom Standard)**

| Metric | Value | Status |
|--------|-------|--------|
| Request Time | ~1.00s (cold), ~0.60s (cached) | ✅ Good |
| Query Time | ~500ms (cold), ~25ms (cached) | ✅ Good |
| Total Queries | ~42 (cold), ~18 (cached) | ✅ Good |
| Models Retrieved | ~178 | ✅ Good |
| CustomStandard Queries | **1** (was 50) | ✅ Optimized |

### **Ranking with Custom Standard** 🔴

| Metric | Current | Expected | Status |
|--------|---------|----------|--------|
| Request Time | **~11.0s** | **~1.0s** | 🔴 **BROKEN** |
| Query Time | ~1.4s | ~500ms | 🔴 Slow |
| Models Retrieved | **~133,397** | **~178** | 🔴 **10x too many** |
| Sub-aspect Query | 610ms | **0ms** (skip) | 🔴 Unnecessary |

---

## 🧪 Testing Guidelines

### **Quick Performance Test**

1. **Clear cache:**
   ```bash
   php artisan cache:clear
   ```

2. **Test Quantum Default:**
   - Select Quantum Default
   - Load GeneralMapping
   - Expected: ~1.0s first load, ~0.6s subsequent

3. **Test Custom Standard:**
   - Select Custom Standard
   - Load GeneralMapping
   - Expected: ~1.0s first load, ~0.6s subsequent
   - ⚠️ **Currently: ~11s first load** (NEEDS FIX)

---

### **Debug Bar Checklist**

**Good Indicators (Optimized):**
- ✅ Request time < 1s (cold) or < 0.7s (cached)
- ✅ Query time < 500ms (cold) or < 50ms (cached)
- ✅ Models < 300
- ✅ No duplicate queries (or < 5)
- ✅ No N+1 patterns

**Bad Indicators (Needs Optimization):**
- 🔴 Request time > 2s
- 🔴 Query time > 1s
- 🔴 Models > 1,000
- 🔴 Duplicate queries > 20
- 🔴 Massive sub_aspect_assessments query (>100ms)

---

## 🚀 Next Steps

### **Immediate (CRITICAL)**

1. **Implement Custom Standard Ranking Fix**
   - File: `app/Services/RankingService.php`
   - Action: Remove conditional eager loading, always use toBase()
   - Impact: Custom Standard 11s → 1s (10x faster)
   - Doc: [CRITICAL_FIX_CUSTOM_STANDARD_PERFORMANCE.md](./CRITICAL_FIX_CUSTOM_STANDARD_PERFORMANCE.md)

### **Optional (Nice to Have)**

2. **Reduce Ranking Cache TTL**
   - Current: 60s
   - Option: 10s (more fresh, slightly slower)
   - Trade-off: Balance freshness vs performance

3. **Add Model Observer for Real-Time Invalidation**
   - Trigger: CustomStandard model update
   - Action: Clear related ranking caches
   - Benefit: Zero delay for admin updates
   - Cost: All users re-compute simultaneously (spike)

4. **Apply Same Optimizations to Other Pages**
   - GeneralPsyMapping
   - GeneralMcMapping
   - StandardPsy, StandardMc, etc.
   - Use same patterns: single-pass loading, conditional eager loading, caching

---

## 📊 Overall Progress

### **Optimization Journey**

```
Original State (Dec 2024):
├─ GeneralMapping: 1.88s
├─ Ranking (Default): ~30s (from case study)
└─ Custom Standard: Same as Default

After Phase 1-2 (Case Study):
├─ GeneralMapping: 1.88s (not yet optimized)
├─ Ranking (Default): 0.37s ✅
└─ Custom Standard: Same as Default

After GeneralMapping Optimization:
├─ GeneralMapping (Default): 0.60s ✅
├─ GeneralMapping (Custom): 1.00s ✅
├─ Ranking (Default): 0.60s (cached) ✅
└─ Ranking (Custom): 11.0s 🔴 (BUG FOUND!)

After Critical Fix (PENDING):
├─ GeneralMapping (Default): 0.60s ✅
├─ GeneralMapping (Custom): 0.60s ✅
├─ Ranking (Default): 0.60s ✅
└─ Ranking (Custom): 0.60s ✅ (WILL BE FIXED)
```

---

## 🎯 Success Criteria

**Definition of Done:**

- [x] ✅ GeneralMapping < 1s (Quantum Default)
- [x] ✅ GeneralMapping < 1s (Custom Standard)
- [x] ✅ Ranking cache implemented
- [x] ✅ CustomStandard N+1 fixed
- [x] ✅ Documentation complete
- [ ] ⚠️ **Ranking < 1s (Custom Standard)** ← **PENDING**
- [ ] ⚠️ All tests passing
- [ ] ⚠️ Production deployment

---

## 🔗 Related Files

**Modified Files:**
- `app/Services/IndividualAssessmentService.php` ✅
- `app/Services/RankingService.php` ✅ (cache added) ⚠️ (fix pending)
- `app/Services/DynamicStandardService.php` ✅
- `app/Livewire/Pages/IndividualReport/GeneralMapping.php` ✅

**Documentation:**
- `docs/CASE_STUDY_RANKING_OPTIMIZATION.md` ✅
- `docs/OPTIMIZATION_GENERAL_MAPPING.md` ✅
- `docs/CRITICAL_FIX_CUSTOM_STANDARD_PERFORMANCE.md` ✅
- `docs/OPTIMIZATION_SUMMARY.md` ✅ (this file)

---

**Last Updated**: December 2024
**Next Review**: After implementing Custom Standard fix
