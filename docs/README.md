# SPSP Documentation Index

Welcome to SPSP (Sistem Pemetaan & Statistik Psikologi) documentation!

---

## 📚 Documentation Structure

### 1. Business & Concepts
Start here if you're new to SPSP or want to understand the business logic.

**[SPSP_BUSINESS_CONCEPTS.md](./SPSP_BUSINESS_CONCEPTS.md)**
- What is SPSP? (BI System, not CRUD)
- Core business concepts (Assessment structure, Standards, Rankings)
- **3-Layer Priority System** (Session → Custom → Quantum)
- User flows & scenarios
- Data architecture
- Key principles for developers

**Target Audience:** Product owners, new developers, business analysts

---

### 2. Architecture Decisions
Understand WHY the system is built the way it is.

**[ARCHITECTURE_DECISION_RECORDS.md](./ARCHITECTURE_DECISION_RECORDS.md)**
- ADR-001: Why `individual_rating` is Pre-Calculated
- ADR-002: 3-Layer Priority System
- ADR-003: Cache Strategy for BI Workloads
- ADR-004: Why RankingService Uses `toBase()`
- ADR-005: Data-Driven vs Hard-Coded Logic
- ADR-006: Why RankingService ≠ IndividualAssessmentService

**Target Audience:** Senior developers, architects, code reviewers

---

### 3. Visual Flow Diagrams
Quick visual reference for understanding system flows.

**[SYSTEM_FLOW_DIAGRAMS.md](./SYSTEM_FLOW_DIAGRAMS.md)**
- Overall System Architecture
- Ranking Calculation Flow (step-by-step)
- 3-Layer Priority Resolution
- Baseline Switch Flow
- Session Adjustment Flow
- Individual Report Flow
- Cache Invalidation Scenarios

**Target Audience:** All developers, especially visual learners

---

### 4. Performance Optimizations
Deep dives into specific optimization work.

**[CRITICAL_FIX_CUSTOM_STANDARD_PERFORMANCE.md](./CRITICAL_FIX_CUSTOM_STANDARD_PERFORMANCE.md)**
- Problem: Custom Standard 10x slower (10.3s vs 1.5s)
- Root cause: Unnecessary eager loading of 133K models
- Solution: Always use `toBase()` for ranking
- Results: 87% faster, 99.92% fewer models
- **Status:** ✅ Implemented (December 2025)

**[OPTIMASI_STANDARDMC_PERFORMANCE.md](./OPTIMASI_STANDARDMC_PERFORMANCE.md)**
- Problem: StandardMc component N+1 queries
- Solution: AspectCacheService preloading
- Results: Eliminated duplicate queries
- **Status:** ✅ Implemented (2024-2025)

**[OPTIMASI_TRAINING_RECOMMENDATION_PERFORMANCE.md](./OPTIMASI_TRAINING_RECOMMENDATION_PERFORMANCE.md)**
- Problem: Training recommendation slow with large datasets
- Solution: Selective column loading, preloading
- Results: Significant performance improvement
- **Status:** ✅ Implemented (2024-2025)

**Target Audience:** Performance engineers, optimization reviewers

---

## 🚀 Quick Start Guides

### For New Developers

1. **Read:** [SPSP_BUSINESS_CONCEPTS.md](./SPSP_BUSINESS_CONCEPTS.md) - Section "Apa itu SPSP?"
2. **Read:** [SYSTEM_FLOW_DIAGRAMS.md](./SYSTEM_FLOW_DIAGRAMS.md) - Section 1 & 2
3. **Read:** [SPSP_BUSINESS_CONCEPTS.md](./SPSP_BUSINESS_CONCEPTS.md) - Section "3-Layer Priority System"
4. **Explore:** Laravel Boost MCP tools (`application-info`, `search-docs`)
5. **Code:** Start with simple bug fixes, read existing code

### For Feature Development

1. **Read:** [ARCHITECTURE_DECISION_RECORDS.md](./ARCHITECTURE_DECISION_RECORDS.md) - Relevant ADRs
2. **Check:** Does feature need 3-layer priority support?
3. **Check:** Does feature need caching?
4. **Check:** Is it data-driven (not hard-coded)?
5. **Test:** With 4,905 participants scale
6. **Review:** Performance impact (use debugbar)

### For Performance Investigation

1. **Read:** [CRITICAL_FIX_CUSTOM_STANDARD_PERFORMANCE.md](./CRITICAL_FIX_CUSTOM_STANDARD_PERFORMANCE.md)
2. **Tool:** Enable Laravel Debugbar
3. **Look for:**
   - N+1 queries
   - Eager loading sub-aspects unnecessarily
   - Missing cache invalidation
4. **Fix:** Follow patterns from existing optimizations
5. **Measure:** Before/after with real data

---

## 🎯 Key Concepts (Quick Reference)

### SPSP is a BI System
```
❌ NOT: User creates/edits assessment data
✅ YES: User explores pre-loaded historical data

❌ NOT: Real-time data updates
✅ YES: Fast analytical queries on static data

❌ NOT: Form submissions & CRUD
✅ YES: Filtering, ranking, what-if analysis
```

### 3-Layer Priority
```
Layer 1 (Highest): Session Adjustment (temporary)
Layer 2 (Medium):  Custom Standard (institutional)
Layer 3 (Lowest):  Quantum Default (system baseline)

Every standard lookup MUST go through DynamicStandardService!
```

### Data Immutability
```
IMMUTABLE (Never changes):      MUTABLE (Configurable):
├─ individual_rating            ├─ weights
├─ participant name             ├─ standard_rating
└─ test date                    └─ active/inactive aspects

individual_rating is HISTORICAL DATA from assessment day.
Custom Standard changes BASELINE, not participant data.
```

### Performance Philosophy
```
Optimization Target: "Exploration Speed" (BI workload)
NOT: "Real-time accuracy" (OLTP workload)

Acceptable:
✅ 60s cache TTL (faster exploration)
✅ Pre-calculated data (faster ranking)
✅ Component-level caching

Unacceptable:
❌ 10+ second load times (kills flow)
❌ Inconsistent ranking (data integrity)
❌ Lost session adjustments (user work)
```

---

## 🔧 Development Checklist

Before submitting PR, verify:

- [ ] **3-Layer Priority:** Does code use `DynamicStandardService`?
- [ ] **Data-Driven:** No hard-coded aspect codes?
- [ ] **Cache Invalidation:** Config hash includes relevant parameters?
- [ ] **Performance:** Tested with 4,905 participants?
- [ ] **Both Baselines:** Works with Quantum Default AND Custom Standard?
- [ ] **Session Adjustments:** Respects Layer 1 priority?
- [ ] **Code Quality:** Follows Laravel conventions from CLAUDE.md?
- [ ] **Documentation:** Updated if adding new concepts?

---

## 🐛 Common Pitfalls

### 1. Recalculating Historical Data
```php
// ❌ WRONG: Recalculate individual_rating
$rating = $this->calculateFromSubAspects($assessment);

// ✅ RIGHT: Use pre-calculated value
$rating = (float) $assessment->individual_rating;
```

### 2. Bypassing 3-Layer Priority
```php
// ❌ WRONG: Direct database read
$weight = $aspect->weight_percentage;

// ✅ RIGHT: Through DynamicStandardService
$weight = $dynamicStandardService->getAspectWeight($templateId, $aspectCode);
```

### 3. Unnecessary Eager Loading
```php
// ❌ WRONG: Eager load for ranking
$assessments = AspectAssessment::with(['subAspectAssessments'])->get();

// ✅ RIGHT: Lightweight for ranking
$assessments = AspectAssessment::query()->toBase()->get();

// ✅ OK: For individual reports (need detail)
$assessment = AspectAssessment::with(['subAspectAssessments'])->find($id);
```

### 4. Hard-Coded Logic
```php
// ❌ WRONG: Hard-coded aspect codes
if ($aspectCode === 'daya-pikir') { ... }

// ✅ RIGHT: Data-driven
if ($aspect->subAspects->isNotEmpty()) { ... }
```

### 5. Missing Cache Invalidation
```php
// ❌ WRONG: Static cache key
Cache::forever('rankings', $rankings);

// ✅ RIGHT: Config-based cache key
$configHash = md5(json_encode($weights));
Cache::remember("rankings:{$configHash}", 60, fn() => $rankings);
```

---

## 📞 Getting Help

### Documentation Issues
If documentation is unclear or incorrect:
1. Create GitHub issue with label `documentation`
2. Suggest improvements in PR

### Technical Questions
1. **Check:** Relevant documentation first
2. **Search:** Existing code for similar patterns
3. **Ask:** Team lead or senior developer
4. **Tool:** Use Laravel Boost MCP `search-docs` for Laravel ecosystem

### Performance Issues
1. **Enable:** Laravel Debugbar
2. **Measure:** Query count, time, memory
3. **Compare:** Against similar pages
4. **Reference:** [CRITICAL_FIX_CUSTOM_STANDARD_PERFORMANCE.md](./CRITICAL_FIX_CUSTOM_STANDARD_PERFORMANCE.md)
5. **Optimize:** Follow proven patterns

---

## 📝 Contributing to Documentation

### When to Update Docs

Update documentation when:
- ✅ Adding new architectural concept
- ✅ Making significant performance optimization
- ✅ Changing 3-layer priority behavior
- ✅ Introducing new service layer
- ✅ Fixing critical bugs that affect understanding

### Documentation Standards

Follow these standards:
- ✅ Use clear headings and table of contents
- ✅ Include code examples for complex concepts
- ✅ Add diagrams for flows
- ✅ Mark status (✅ Implemented, 🚧 In Progress, 📝 Planned)
- ✅ Include "Why" not just "How"
- ✅ Update index (this file) when adding new docs

---

## 🗂️ File Organization

```
docs/
├── README.md                                  ← You are here
├── SPSP_BUSINESS_CONCEPTS.md                  ← Start here for concepts
├── ARCHITECTURE_DECISION_RECORDS.md           ← Understand "why"
├── SYSTEM_FLOW_DIAGRAMS.md                    ← Visual reference
├── CRITICAL_FIX_CUSTOM_STANDARD_PERFORMANCE.md ← Performance fix
├── OPTIMASI_STANDARDMC_PERFORMANCE.md         ← Component optimization
└── OPTIMASI_TRAINING_RECOMMENDATION_PERFORMANCE.md ← Service optimization
```

---

## 📜 Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | December 2025 | Initial documentation structure |
| | | - Business concepts documented |
| | | - Architecture decisions recorded |
| | | - System flows diagrammed |
| | | - Performance optimizations documented |

---

## 🎓 Recommended Reading Order

### For Understanding Business Logic:
1. SPSP_BUSINESS_CONCEPTS.md (Sections 1-3)
2. SYSTEM_FLOW_DIAGRAMS.md (Sections 1-3)
3. ARCHITECTURE_DECISION_RECORDS.md (ADR-002)

### For Performance Work:
1. CRITICAL_FIX_CUSTOM_STANDARD_PERFORMANCE.md (Full read)
2. ARCHITECTURE_DECISION_RECORDS.md (ADR-003, ADR-004)
3. SYSTEM_FLOW_DIAGRAMS.md (Section 2)

### For Feature Development:
1. SPSP_BUSINESS_CONCEPTS.md (Full read)
2. ARCHITECTURE_DECISION_RECORDS.md (All ADRs)
3. SYSTEM_FLOW_DIAGRAMS.md (Relevant sections)

---

**Last Updated:** December 2025
**Maintainers:** SPSP Development Team
**Feedback:** Welcome! Create GitHub issues or PRs.
