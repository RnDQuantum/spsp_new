# Performance Optimization V3 - SPSP Ranking System

**Status**: Strategy & Implementation Guide
**Scope**: Full Context for AI Development Agent

## 📊 Executive Summary

The **SPSP (Sistem Pemetaan & Statistik Psikologi)** system is experiencing critical performance issues on large datasets. This document provides the full context, problem analysis, core concepts, and the required solution strategy for the AI agent tasked with the optimization.

### Current Performance Status (20K Participants, 3 Positions)

| Page Category | Page Name | Current Duration | Status | Priority |
|---------------|-----------|------------------|--------|----------|
| **Individual Report** | GeneralMatching | 514ms | ✅ OK | Low |
| **Individual Report** | GeneralPsyMapping | 8.31s | 🔴 CRITICAL | High |
| **Individual Report** | GeneralMcMapping | 5.51s | 🟡 WARNING | Medium |
| **Individual Report** | GeneralMapping | 15.1s | 🔴 CRITICAL | High |
| **Individual Report** | RingkasanMcMapping | 393ms | ✅ OK | Low |
| **Individual Report** | RingkasanAssessment | 383ms | ✅ OK | Low |
| **General Report** | RankingPsyMapping | 10.71s | 🔴 CRITICAL | High |
| **General Report** | RankingMcMapping | 7.32s | 🔴 CRITICAL | High |
| **General Report** | RekapRankingAssessment | **30.78s** | 🔴 **WORST** | **CRITICAL** |
| **General Report** | Statistic | 3.59s | 🟡 WARNING | Medium |
| **General Report** | TrainingRecommendation | 4.25s | 🟡 WARNING | Medium |

**Total Slow Pages: 8 of 11** (73% need optimization)

---

## 🌍 Application Context: Analytics vs CRUD

**CRITICAL**: SPSP is an **ANALYTICS APPLICATION**, NOT a typical CRUD application.

### The Fundamental Difference
*   **❌ Typical CRUD (e.g., CMS, E-commerce)**:
    *   Simple reads by ID.
    *   Data rarely changes during a view session.
    *   Caching is easy (Cache Key = ID).
*   **✅ SPSP (Analytics)**:
    *   **Heavy Computation**: Calculates scores from 340,000+ data points per page load.
    *   **Dynamic Parameters**: Users constantly tweak weights, tolerance, and standards.
    *   **Exploratory**: Users want "What-If" analysis (e.g., "What if I change tolerance to 5%?").
    *   **Caching Difficulty**: Every parameter change creates a unique combination, making simple key-value caching ineffective.

**Goal**: The system must behave like a snappy BI tool (Tableau/PowerBI), not a sluggish web form.

---

## ⚛️ Core Concepts (The "Laws" of SPSP)

### 1. The 3-Layer Priority System
This is the most critical logic governing how scores are calculated. The AI must ensure this precedence is respected **strictly**.

```
┌─────────────────────────────────────────────────────────────┐
│              3-LAYER PRIORITY SYSTEM                         │
│  (Determines the "Effective Weight" & "Standard" to use)    │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  Layer 1: SESSION ADJUSTMENT (Highest Priority)              │
│  ├─ Scope: Temporary, per user session.                     │
│  ├─ Use Case: User tweaking a slider to "explore".          │
│  └─ Storage: PHP Session (`individual_report.tolerance`, etc)│
│       ↓ if not found                                         │
│                                                              │
│  Layer 2: CUSTOM STANDARD                                   │
│  ├─ Scope: Saved configuration for an institution.          │
│  ├─ Use Case: "Kejaksaan Standard 2024".                    │
│  └─ Storage: `custom_standards` table.                      │
│       ↓ if not found                                         │
│                                                              │
│  Layer 3: QUANTUM DEFAULT (Lowest Priority)                 │
│  ├─ Scope: Global system defaults.                          │
│  ├─ Use Case: Baseline when nothing else is defined.        │
│  └─ Storage: `aspects` / `sub_aspects` tables.              │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

**Implementation Gap**: Previous attempts failed because they didn't correctly implement the fallback logic, especially when moving calculation to SQL.

### 2. User Analyst Exploration Workflow
The system optimization must support this specific workflow without page reloads:

1.  **Initial Load**: User sees the "Baseline" (Layer 3 or 2). [Target: < 5s]
2.  **Hypothesis**: "I think the passing grade is too strict."
3.  **Action**: User changes **Tolerance** from 0% → 5% (Layer 1).
4.  **Reaction**: The UI **instantly** updates the "Recommended" vs "Not Recommended" status. [Target: < 0.1s]
5.  **Refinement**: User checks the **Charts** to see the distribution shift.
6.  **Decision**: User saves or exports the report.

---

## 🛑 Current Problems & Requirements

### 1. Bottlenecks
*   **RekapRankingAssessment (30s+)**:
    *   Loads 338,917 Eloquent models (215K SubAspect + 123K Aspect).
    *   Performs N+1 queries.
    *   PHP loops over 256,000 times for score calculation.
    *   **Requirement**: Move calculation to Database Layer (SQL).

### 2. Tolerance Selector Issues (`app\Livewire\Components\ToleranceSelector.php`)
*   **Requirement**: MUST remain a Dropdown UI.
*   **Storage**: MUST store value in **Session** so it applies globally across all reports.
*   **Impact**: Changing this value must trigger updates in:
    *   Grids/Tables (Rows change color/status).
    *   Summary Cards (Counts update).
    *   **Charts** (Visualizations must redraw).

---

## 🚀 The Recommended Solution Strategy

Based on the nature of the data and user workflow, a **Hybrid Approach** is required.

### Phase 1: SQL Aggregation (Solve the 30s Load)
**Concept**: "Move computation to the data."

Instead of loading thousands of models into PHP, use optimized SQL queries to calculate the weighted scores directly in the database.

*   **Mechanism**:
    1.  PHP resolves the **3-Layer Priority** to determine the final weights (e.g., Potensi=60%, Kompetensi=40%).
    2.  PHP injects these weights into a single complex SQL query.
    3.  SQL performs the `SUM(rating * weight)` aggregation.
    4.  SQL returns only the final 50 rows (paginated) + Totals.

**Why**: Database engines (C++) are 100x faster at math than PHP loops.

### Phase 2: Alpine.js for Reactivity (Solve the Exploration)
**Concept**: "Reactive UI for Parameter Tuning."

For simple parameters like **Tolerance** (which acts as a threshold on the final score), we should NOT reload from the server.

*   **Mechanism**:
    1.  The SQL query returns the raw `total_score` and `standard_score` to the frontend.
    2.  **Alpine.js** holds the Tolerance variable state.
    3.  When user changes Tolerance:
        *   Alpine.js calculates: `limit = standard_score - (standard_score * tolerance / 100)`
        *   Alpine.js updates the "Status" text (MS/TMS) and row styling **instantly** in the browser.
        *   Livewire is only pinged to update the Session (background) or if complex re-fetching is needed for new pages.

**Why**: Instant feedback (< 100ms) for the analyst workflow.

---

## 📝 Next Steps for AI Agent

1.  **Refactor `RankingService`**: Implement the `getRankingsOptimized` method using the SQL Aggregation strategy. Ensure it accepts "Effective Weights" resolved from the 3-Layer Priority system.
2.  **Fix `ToleranceSelector`**: Ensure it writes to Session `individual_report.tolerance`.
3.  **Update `RekapRankingAssessment`**: Replace the heavy PHP loop with the new Service call.
4.  **Wire up Alpine.js**: Implement the frontend logic to handle dynamic "MS/TMS" calculation based on the SQL-provided raw scores.
