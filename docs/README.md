# SPSP Documentation Index

Welcome to SPSP (Sistem Pemetaan & Statistik Psikologi) documentation! This index provides a direct guide to the existing code conditions, database schema, architectural decisions, and performance optimizations.

---

## 📚 Documentation Structure & File Organization

### 1. Business & Concepts
Start here to understand the core business logic of the system.
*   **[SPSP_BUSINESS_CONCEPTS.md](./SPSP_BUSINESS_CONCEPTS.md)**
    *   What is SPSP? (BI System, not CRUD)
    *   Core business concepts (Assessment structure, Standards, Rankings)
    *   **3-Layer Priority System** (Session → Custom → Quantum)
    *   User flows, scenarios, and data architecture
*   **[USER_FLOW_AND_MENUS.md](./USER_FLOW_AND_MENUS.md)**
    *   Main User Navigation Flow (Selecting Event, Position, and Participant)
    *   Operational 3-Layer Priority System walkthrough in UI
    *   Detailed menus breakdown (Individual Report, General Report, Talent Pool)

### 2. Database Schema & Relations
Understand the database structure, table definitions, relationships, and calculations.
*   **[DATABASE_STRUCTURE.md](./DATABASE_STRUCTURE.md)**
    *   Comprehensive database schema (20 business & system tables)
    *   Entity Relationship Diagram (ERD) with Mermaid visualizer
    *   Deep dive on 3-Layer Priority system data flow
    *   Sample data and step-by-step scoring calculation walkthroughs

### 3. Architecture Decisions
Understand WHY the system is built the way it is.
*   **[ARCHITECTURE_DECISION_RECORDS.md](./ARCHITECTURE_DECISION_RECORDS.md)**
    *   ADR-001: Why `individual_rating` is Pre-Calculated and Immutable
    *   ADR-002: 3-Layer Priority System
    *   ADR-003: Cache Strategy for BI Workloads
    *   ADR-004: Why RankingService Uses `toBase()`
    *   ADR-005: Data-Driven vs Hard-Coded Logic
    *   ADR-006: Why RankingService ≠ IndividualAssessmentService

### 4. Visual Flow Diagrams
Quick visual reference for understanding system flows.
*   **[SYSTEM_FLOW_DIAGRAMS.md](./SYSTEM_FLOW_DIAGRAMS.md)**
    *   Overall System Architecture
    *   Ranking Calculation Flow (step-by-step)
    *   3-Layer Priority Resolution
    *   Baseline Switch, Session Adjustment, and Individual Report Flows
    *   Cache Invalidation Scenarios

### 5. Performance Optimizations
Deep dives into specific optimization work applied to the codebase.
*   **[CRITICAL_FIX_CUSTOM_STANDARD_PERFORMANCE.md](./CRITICAL_FIX_CUSTOM_STANDARD_PERFORMANCE.md)**
    *   Critical fix for custom standard performance (87% faster ranking via `toBase()`).
*   **[OPTIMASI_STATISTIC_PERFORMANCE.md](./OPTIMASI_STATISTIC_PERFORMANCE.md)**
    *   Performance optimizations applied to the statistical components.
*   **[OPTIMIZATION_STANDARD_MC.md](./OPTIMIZATION_STANDARD_MC.md)**
    *   Component optimization for Standard Competency (N+1 query elimination).
*   **[OPTIMIZATION_STANDARD_PSIKOMETRIK.md](./OPTIMIZATION_STANDARD_PSIKOMETRIK.md)**
    *   Component optimization for Standard Psikometrik.
*   **[OPTIMIZATION_TRAINING_RECOMMENDATION.md](./OPTIMIZATION_TRAINING_RECOMMENDATION.md)**
    *   Optimizations for training recommendation logic on large datasets.

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

**Last Updated:** July 2026
**Maintainers:** SPSP Development Team
