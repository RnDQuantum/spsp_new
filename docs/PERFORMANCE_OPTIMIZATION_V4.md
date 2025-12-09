# SPSP Performance Optimization Guide V4

**Target Audience**: AI Development Agent (Claude Code)  
**Purpose**: Understand system context before code scanning  
**Approach**: Problem-first, solution-agnostic

---

## 🎯 What is SPSP?

**SPSP (Sistem Pemetaan & Statistik Psikologi)** is a **Business Intelligence application** for psychological assessment analytics, NOT a typical CRUD system.

### Key Characteristics
- **Data Volume**: 20,000+ participants per batch, 3-6 positions each
- **Computation Heavy**: Each report calculates from 340,000+ data points
- **User Type**: Data analysts exploring "what-if" scenarios
- **Usage Pattern**: Parameter tweaking (weights, tolerance) → Immediate feedback expected
- **Tech Stack**: TALL Stack (Tailwind, Alpine.js, Livewire, Laravel) + PostgreSQL/MySQL

---

## 🔴 Current Problem

### Performance Metrics (20K participants, 3 positions)

| Page | Duration | Status | Root Cause |
|------|----------|--------|------------|
| RekapRankingAssessment | **30.78s** | 🔴 CRITICAL | 338K+ Eloquent models loaded, N+1 queries |
| GeneralMapping | 15.1s | 🔴 CRITICAL | Heavy PHP loops |
| RankingPsyMapping | 10.71s | 🔴 CRITICAL | Inefficient aggregation |
| RankingMcMapping | 7.32s | 🔴 CRITICAL | Same as above |

**User Impact**: 
- Initial page load takes 30+ seconds (target: < 1s)
- Every parameter change requires full page reload (target: < 100ms reactivity)

---

## ⚙️ Core Business Logic (CRITICAL - Must Preserve)

### 1. The 3-Layer Priority System

This determines which **weights** and **standards** to use for score calculation:

```
Priority 1: SESSION ADJUSTMENT (Temporary user exploration)
    ↓ if exists: Use session values
    ↓ if not exists: Check Layer 2
    
Priority 2: CUSTOM STANDARD (Institution-specific config)
    ↓ if exists: Use custom_standards table
    ↓ if not exists: Check Layer 3
    
Priority 3: QUANTUM DEFAULT (System baseline)
    ↓ Always exists: Use aspects/sub_aspects defaults
```

**Storage Locations:**
- Layer 1: `$_SESSION['individual_report']['tolerance']`, `$_SESSION['individual_report']['weights']`
- Layer 2: `custom_standards` table (DB)
- Layer 3: `aspects.default_weight`, `sub_aspects.default_weight` (DB)

**Critical Rule**: The system MUST check in this order. Previous optimization attempts failed because they skipped layers or checked in wrong order.

---

### 2. Score Calculation Formula

```
Final Score = Σ(Aspect Rating × Effective Weight)

Where:
- Aspect Rating: From participant assessment results
- Effective Weight: Determined by 3-Layer Priority System
- Categories: "Potensi" and "Kompetensi" (each can have custom weights)
```

**Recommendation Logic:**
```
Passing Threshold = Standard Score × (1 - Tolerance / 100)

Status = {
    "MS" (Memenuhi Syarat)     if Final Score >= Passing Threshold
    "TMS" (Tidak Memenuhi)      if Final Score < Passing Threshold
}
```

---

### 3. User Workflow (Must Support)

```
1. [Initial Load] 
   → User opens ranking page
   → System shows baseline with default/custom standard
   → EXPECTATION: < 1 second

2. [Exploration - Tolerance Change]
   → User adjusts tolerance slider: 0% → 5% → 10%
   → MS/TMS status updates
   → Table row colors change
   → Summary cards recalculate (count MS vs TMS)
   → Charts redraw
   → EXPECTATION: < 100ms (INSTANT, no server round-trip)

3. [Exploration - Weight Change]
   → User adjusts Potensi:Kompetensi ratio (60:40 → 70:30)
   → Rankings reorder
   → Scores recalculate
   → EXPECTATION: 1-2 seconds (acceptable to hit server)

4. [Save/Export]
   → User saves as Custom Standard or exports report
```

---

## 🎯 Performance Requirements

### Success Criteria
| Metric | Current | Target | Priority |
|--------|---------|--------|----------|
| Initial page load (6K participants) | 30s | < 1s | 🔴 CRITICAL |
| Tolerance change reactivity | 30s (reload) | < 100ms | 🔴 CRITICAL |
| Weight change response | 30s (reload) | < 2s | 🟡 HIGH |
| Pagination (next 50 rows) | N/A | < 200ms | 🟢 MEDIUM |

### Constraints
1. ✅ **MUST** preserve 3-Layer Priority System logic
2. ✅ **MUST** maintain TALL Stack (no framework changes)
3. ✅ **MUST** support real-time parameter exploration
4. ✅ **MUST** handle up to 20K participants per batch
5. ✅ **MUST** keep ToleranceSelector as dropdown UI (existing component)

---

## 🧩 Technical Context

### Database Schema (Simplified)

```
participants (20K rows)
├── id, name, email, batch_id

positions (3-6 rows)
├── id, title, department

aspects (123K rows) - Assessment scores per participant
├── participant_id, aspect_id, rating (0-100)
├── category: 'potensi' or 'kompetensi'

sub_aspects (215K rows) - Detailed scores
├── participant_id, sub_aspect_id, rating (0-100)

custom_standards (10-50 rows) - Saved configurations
├── id, institution, position_id
├── weight_potensi, weight_kompetensi
├── passing_score, tolerance
```

### Current Bottleneck (RekapRankingAssessment)

**Pseudocode of problem:**
```php
// Current implementation (SLOW!)
foreach ($participants as $participant) {        // 6,000 iterations
    foreach ($positions as $position) {          // 3 iterations
        
        $aspects = $participant->aspects;         // N+1 query! Loads 123K models
        $subAspects = $participant->subAspects;   // N+1 query! Loads 215K models
        
        $score = 0;
        foreach ($aspects as $aspect) {           // Nested loop!
            $weight = $this->resolveWeight($aspect); // 3-layer check per aspect
            $score += $aspect->rating * $weight;
        }
        // ... more loops for sub_aspects
        
        $ranking[] = [
            'participant' => $participant,
            'score' => $score,
            'status' => $this->calculateStatus($score, $tolerance)
        ];
    }
}
// Total: 338,917 model instances + 256,000+ PHP iterations
```

---

## 🔍 What Claude Code Should Scan

### Files to Analyze:
```
app/Services/RankingService.php          # Current ranking logic
app/Livewire/RekapRankingAssessment.php  # Slowest page component
app/Livewire/Components/ToleranceSelector.php  # Parameter selector
app/Models/Participant.php               # Check relationships
app/Models/Aspect.php                    # Check eager loading
app/Models/CustomStandard.php            # Layer 2 logic
database/migrations/*_create_aspects_table.php  # Schema understanding
```

### Questions for Claude Code:
1. How is the 3-Layer Priority currently implemented?
2. Where are the N+1 queries happening?
3. Are there existing indexes on score-related columns?
4. How does ToleranceSelector currently update the session?
5. What Eloquent relationships exist between models?

---

## 💡 Optimization Approaches (Guidance, Not Prescription)

Claude Code should consider these strategies and choose the best fit:

### Strategy A: Database-Level Aggregation
- Move calculation from PHP loops to SQL queries
- Use window functions for ranking
- Leverage database indexes

### Strategy B: Pre-computation
- Calculate scores in background jobs
- Store in materialized table
- Trade storage for speed

### Strategy C: Smart Caching
- Cache base scores (without tolerance)
- Apply tolerance client-side
- Invalidate on standard changes

### Strategy D: Frontend Reactivity
- Send raw scores to frontend
- Use Alpine.js for tolerance calculation
- Avoid server round-trips for simple params

### Strategy E: Progressive Loading
- Load summary first (fast)
- Lazy load table/charts
- Use Livewire wire:init

**Note**: A hybrid approach combining multiple strategies is likely optimal.

---

## ⚠️ Common Pitfalls to Avoid

1. ❌ **Breaking 3-Layer Priority**: Don't simplify the weight resolution logic
2. ❌ **Over-caching**: Analytics data changes frequently, cache keys become complex
3. ❌ **Ignoring UX**: Optimization shouldn't sacrifice the exploration workflow
4. ❌ **Premature Optimization**: Profile first, identify actual bottlenecks
5. ❌ **Losing Data Integrity**: Score calculations must remain mathematically correct

---

## 🎬 Success Metrics

### Performance
- [ ] RekapRankingAssessment loads in < 1 second
- [ ] Tolerance slider updates instantly (< 100ms)
- [ ] Can handle 20K participants without timeout
- [ ] Memory usage stays under 512MB

### Functionality
- [ ] 3-Layer Priority System works correctly
- [ ] All existing reports still function
- [ ] ToleranceSelector syncs to session
- [ ] Charts update when parameters change

### Maintainability  
- [ ] Code remains readable and documented
- [ ] Future developers can understand the logic
- [ ] No "magic" queries that are hard to debug

---

## 📚 Additional Context

### Why This is Hard (Not a Typical CRUD)

**Typical CRUD**:
- Simple: `User::find($id)` 
- Cache key: `"user:{$id}"`
- Rarely changes during view

**SPSP (Analytics)**:
- Complex: Aggregate 340K+ records with dynamic weights
- Cache key: `"ranking:{batch}:{position}:{tolerance}:{weight_p}:{weight_k}:{standard}"` (explosive combinations)
- User constantly tweaking parameters ("what-if" analysis)

**Analogy**: This is more like Excel/Tableau than WordPress. Users expect spreadsheet-like responsiveness.

---

## 🚀 Next Steps for Claude Code

1. **Scan** the codebase files listed above
2. **Identify** the exact bottleneck (confirm N+1 queries, missing indexes, etc.)
3. **Propose** optimization strategy with trade-offs explained
4. **Implement** incrementally (start with biggest bottleneck)
5. **Validate** that 3-Layer Priority System still works correctly
6. **Test** with 20K dataset to confirm < 1s load time

---

**Remember**: The goal is not just speed, but **maintaining the analyst exploration workflow** while being fast. A 1-second load that breaks "what-if" exploration is a failure.