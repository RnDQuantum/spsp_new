# Testing Checklist - SPSP Baseline 3-Layer System

**Purpose:** Quick reference checklist for test implementation progress
**Source:** [TESTING_SCENARIOS_BASELINE_3LAYER.md](./TESTING_SCENARIOS_BASELINE_3LAYER.md)
**Last Updated:** December 2025

---

## 📊 Overall Progress

- **Total Scenarios:** ~110+ tests (⚠️ ESTIMATED - Final count determined after component analysis)
- **Completed:** 89 tests (Service Layer: 64, Livewire Layer: 25)
- **In Progress:** 0 tests
- **Remaining:** ~21+ tests (Livewire Layer - StandardMc + consumer components)

---

## ✅ Scenario Group 1: 3-Layer Priority (0/6)

- [ ] Test 1.1: Layer 3 (Quantum Default) Baseline
- [ ] Test 1.2: Layer 2 (Custom Standard) Overrides Layer 3
- [ ] Test 1.3: Layer 1 (Session) Overrides Layer 2
- [ ] Test 1.4: Layer 1 Partial Override (Mix of Layers)
- [ ] Test 1.5: Reset Session Returns to Layer 2
- [ ] Test 1.6: Switch Custom Standard Clears Session

---

## ✅ Scenario Group 2: Data Immutability (3/3)

- [x] Test 2.1: Database Individual Rating NEVER Changes
- [x] Test 2.2: Calculation Logic Recalculates When Sub-Aspects Inactive
- [x] Test 2.3: Weight Change Does NOT Alter Stored Data

**Status:** ✅ **COMPLETE** - Covered by RankingServiceTest

---

## ✅ Scenario Group 3: Active/Inactive Logic (5/5)

- [x] Test 3.1: Inactive Aspect Excluded from Ranking
- [x] Test 3.2: Inactive Sub-Aspect Triggers Fair Recalculation
- [x] Test 3.3: All Sub-Aspects Inactive (Edge Case)
- [x] Test 3.4: Mixed Active/Inactive in Custom Standard
- [x] Test 3.5: Sub-Aspect Recalculation Impact on Statistics

**Status:** ✅ **COMPLETE** - Covered by RankingServiceTest & IndividualAssessmentServiceTest

---

## ✅ Scenario Group 4: Calculation Accuracy (7/7)

- [x] Test 4.1: Standard Rating Calculation (No Sub-Aspects)
- [x] Test 4.2: Standard Rating Calculation (With Sub-Aspects, All Active)
- [x] Test 4.3: Standard Rating with Inactive Sub-Aspect
- [x] Test 4.4: Individual Rating Recalculation with Inactive Sub-Aspect
- [x] Test 4.5: Individual Score Calculation
- [x] Test 4.6: Total Score Calculation (Combined)
- [x] Test 4.7: Tolerance Adjustment

**Status:** ✅ **COMPLETE** - Covered by RankingServiceTest & IndividualAssessmentServiceTest

---

## ✅ Scenario Group 5: Cache Invalidation (5/5)

- [x] Test 5.1: Cache Hit on Repeated Load (Same Config)
- [x] Test 5.2: Cache Miss on Baseline Change
- [x] Test 5.3: Cache Miss on Session Adjustment
- [x] Test 5.4: Cache Persists Across Tolerance Changes
- [x] Test 5.5: Cache Expiration (TTL)

**Status:** ✅ **COMPLETE** - Covered by RankingServiceTest

---

## ⚠️ Scenario Group 6: Edge Cases (0/10)

- [ ] Test 6.1: Zero Participants
- [ ] Test 6.2: Single Participant
- [ ] Test 6.3: All Participants Same Score (Tie)
- [ ] Test 6.4: Participant with No Assessment Data
- [ ] Test 6.5: Aspect with No Participants
- [ ] Test 6.6: Rating Exactly on Boundary
- [ ] Test 6.7: Negative Gap (Below Standard)
- [ ] Test 6.8: Extreme Values (Max Rating)
- [ ] Test 6.9: Extreme Values (Min Rating)

**Status:** ⚠️ **PARTIAL** - Some edge cases covered, needs dedicated tests

---

## ✅ Scenario Group 7: Cross-Service Consistency (4/4)

- [x] Test 7.1: Same Participant, Same Result Across Services
- [x] Test 7.2: Statistic Average Matches Ranking Average
- [x] Test 7.3: Training Recommendation Matches Ranking Order
- [x] Test 7.4: Standard Rating Consistency Across Services

**Status:** ✅ **COMPLETE** - Covered by CrossServiceConsistencyTest

---

## ⚠️ Scenario Group 8: Performance Regression (0/4)

- [ ] Test 8.1: Quantum Default Performance (Baseline)
- [ ] Test 8.2: Custom Standard Performance (Should Match)
- [ ] Test 8.3: Session Adjustment Performance
- [ ] Test 8.4: Large Dataset Scalability

**Status:** ⚠️ **NOT STARTED** - Performance tests not implemented

---

## 🎯 Scenario Group 9: Livewire Component Integration (25/33) ⭐ **IN PROGRESS**

### StandardPsikometrik Component (25/25 tests - 100% PASS) ✅ **COMPLETE**

**✅ Group 1: Lifecycle & Initialization (3/3)**
- [x] Test 9.1a: Component mounts with default state
- [x] Test 9.1b: Component loads standard data when event and position selected
- [x] Test 9.1c: Component loads available custom standards for institution

**✅ Group 2: Baseline Selection & Switching (5/5)**
- [x] Test 9.2a: Selecting custom standard updates component state
- [x] Test 9.2b: Switch from custom standard to Quantum Default
- [x] Test 9.2c: Handle string null/empty string/actual null correctly
- [x] Test 9.2d: Switching custom standard clears previous session adjustments ✅ FIXED
- [x] Test 9.2e: Handles standard-switched event from other components

**✅ Group 3: Category Weight Adjustments (4/4)**
- [x] Test 9.3a: Opening category weight modal sets state correctly
- [x] Test 9.3b: Saving category weight creates session adjustment
- [x] Test 9.3c: Closing modal without saving discards changes
- [x] Test 9.3d: Category weight modal handles invalid template gracefully

**✅ Group 4: Sub-Aspect Rating Adjustments (5/5)**
- [x] Test 9.4a: Opening sub-aspect rating modal sets state correctly
- [x] Test 9.4b: Saving sub-aspect rating creates session adjustment
- [x] Test 9.4c: Sub-aspect rating validation rejects values below 1
- [x] Test 9.4d: Sub-aspect rating validation rejects values above 5
- [x] Test 9.4e: Sub-aspect rating modal handles null template gracefully

**✅ Group 5: Reset Adjustments (2/2)**
- [x] Test 9.5a: Reset adjustments clears all session adjustments ✅ FIXED
- [x] Test 9.5b: Reset adjustments handles null template gracefully

**✅ Group 6: Event Handling (3/3)**
- [x] Test 9.6a: Handles event-selected clears cache and waits for position
- [x] Test 9.6b: Handles position-selected loads data and dispatches chart update
- [x] Test 9.6c: Handles standard-adjusted event from other components

**✅ Group 7: Cache Management (2/2)**
- [x] Test 9.7a: Cache prevents redundant data processing
- [x] Test 9.7b: Cache cleared on baseline changes

**✅ Group 8: 3-Layer Priority Integration (1/1)**
- [x] Test 9.8a: Loaded data respects 3-layer priority system

**Status:** ✅ **100% COMPLETE** - All 25 tests passing (77 assertions)

**Test File:** `tests/Feature/Livewire/StandardPsikometrikTest.php`

**Fixes Applied:**
- Added `AspectCacheService::preloadByTemplate()` before `hasCategoryAdjustments()` calls
- Created fresh `DynamicStandardService` instances after session clear to avoid stale cache
- Added `AspectCacheService::clearCache()` in `tearDown()` to prevent cache pollution
- Fixed test data to use values different from custom standard baseline
- Converted all `@test` annotations to `#[Test]` attributes
- Added assertion to risky test (`assertNotDispatched`)

**Production Improvements:**
- Enhanced `DynamicStandardService::hasCategoryAdjustments()` with comprehensive documentation
- Added fail-fast guard to catch missing cache preload in local/testing environments
- Prevents silent failures from hidden AspectCacheService dependency

### StandardMc Component (0/8 tests - NOT STARTED)

---

## 🔄 Scenario Group 10: Baseline Switching (0/4)

- [ ] Test 10.1: Rapid Baseline Switching
- [ ] Test 10.2: Null/Empty String Custom Standard ID Handling
- [ ] Test 10.3: Switch Baseline During Modal Open
- [ ] Test 10.4: Switch from Custom Standard with Session Adjustments

**Status:** 🔴 **NOT STARTED**

---

## 📡 Scenario Group 11: Event Communication (0/~15) ⚠️ EXPANDED

### A. Event Producers (StandardPsikometrik / StandardMc)
- [ ] Test 11.1: 'standard-switched' Event Propagation
- [ ] Test 11.2: 'standard-adjusted' Event Propagation
- [ ] Test 11.3: 'event-selected' Listener
- [ ] Test 11.4: 'position-selected' Listener
- [ ] Test 11.5: Multiple Events in Sequence

### B. Event Consumers - General Report Components ⭐ NEW
- [ ] Test 11.6: RekapRankingAssessment receives 'standard-switched' and clears cache
- [ ] Test 11.7: RekapRankingAssessment receives 'standard-adjusted' and updates summary
- [ ] Test 11.8: Statistic receives events and refreshes distribution data
- [ ] Test 11.9: TrainingRecommendation receives events and updates training summary
- [ ] Test 11.10: RankingPsyMapping/RankingMcMapping handle baseline events

### C. Event Consumers - Individual Report Components ⭐ NEW
- [ ] Test 11.11: GeneralMapping responds to baseline changes
- [ ] Test 11.12: SpiderPlot updates chart on standard adjustment
- [ ] Test 11.13: RingkasanAssessment refreshes data on event
- [ ] Test 11.14: GeneralPsyMapping/GeneralMcMapping handle events
- [ ] Test 11.15: Multiple consumers receive same event simultaneously (integration)

**Status:** 🔴 **NOT STARTED** - **PRIORITY 2** (after Group 9)

**Note:** ⚠️ Test count (~15) is ESTIMATED. Actual test count will be determined after analyzing each component's methods and event listeners. All methods handling baseline events MUST be covered.

---

## ✅ Scenario Group 12: Cache Key Completeness (6/6)

- [x] Test 12.1: Sub-Aspect Active Status in Cache Key
- [x] Test 12.2: Aspect Active Status in Cache Key
- [x] Test 12.3: Session ID Isolation in Cache Key
- [x] Test 12.4: Custom Standard Selection in Cache Key
- [x] Test 12.5: Category Weight in Cache Key
- [x] Test 12.6: Tolerance NOT in Cache Key

**Status:** ✅ **COMPLETE** - Covered by RankingServiceTest

---

## 🔄 Scenario Group 13: Cross-Component Integration (0/~8) ⭐ NEW SECTION

### End-to-End Baseline Change Propagation
- [ ] Test 13.1: Baseline change from StandardPsikometrik propagates to all listening components
- [ ] Test 13.2: Session adjustment triggers cache clear + data reload across all consumers
- [ ] Test 13.3: Switching Custom Standard updates all components simultaneously
- [ ] Test 13.4: Event isolation - different templates don't interfere with each other
- [ ] Test 13.5: Multiple users with different baselines see correct isolated data
- [ ] Test 13.6: Cache invalidation cascades correctly to all dependent components
- [ ] Test 13.7: Chart updates propagate correctly (chartDataUpdated events)
- [ ] Test 13.8: Summary updates propagate correctly (summary-updated events)

**Status:** 🔴 **NOT STARTED** - **PRIORITY 3** (Integration tests after unit tests complete)

**Note:** ⚠️ Test count (~8) is ESTIMATED. These are integration tests that verify end-to-end behavior across multiple components. Additional tests may be added based on discovered edge cases.

---

## 📋 Test File Locations

### ✅ Completed Tests

| Test File | Location | Tests | Status |
|-----------|----------|-------|--------|
| RankingServiceTest | `tests/Unit/Services/RankingServiceTest.php` | 60/60 | ✅ PASS |
| CrossServiceConsistencyTest | `tests/Integration/Services/CrossServiceConsistencyTest.php` | 4/4 | ✅ PASS |
| IndividualAssessmentServiceTest | `tests/Unit/Services/IndividualAssessmentServiceTest.php` | 75/75 | ✅ PASS |
| CustomStandardServiceTest | `tests/Unit/Services/CustomStandardServiceTest.php` | 70/70 | ✅ PASS |
| DynamicStandardServiceTest | `tests/Unit/Services/DynamicStandardServiceTest.php` | 49/50 | ⚠️ 1 FAIL |

### 🔴 Pending Tests - Livewire Layer

**⚠️ IMPORTANT:** Test counts below are ESTIMATED. Actual count will be determined after analyzing each component's:
- Public methods that interact with baseline system
- Event listeners (`$listeners` array)
- Methods that call DynamicStandardService
- Cache management methods
- Modal interactions and validations

**Coverage Goal:** 100% of all methods related to baseline/standard management

| Test File | Location | Tests (Est.) | Status | Priority |
|-----------|----------|--------------|--------|----------|
| **Producer Components** | | | | |
| StandardPsikometrikTest | `tests/Feature/Livewire/StandardPsikometrikTest.php` | ~12-15 | 🔴 NOT STARTED | **P1** |
| StandardMcTest | `tests/Feature/Livewire/StandardMcTest.php` | ~8-10 | 🔴 NOT STARTED | **P1** |
| BaselineSwitchingTest | `tests/Feature/Livewire/BaselineSwitchingTest.php` | ~4-6 | 🔴 NOT STARTED | **P1** |
| **Consumer Components** | | | | |
| RekapRankingAssessmentTest | `tests/Feature/Livewire/RekapRankingAssessmentTest.php` | ~6-8 | 🔴 NOT STARTED | **P2** |
| StatisticTest | `tests/Feature/Livewire/StatisticTest.php` | ~4-6 | 🔴 NOT STARTED | **P2** |
| TrainingRecommendationTest | `tests/Feature/Livewire/TrainingRecommendationTest.php` | ~6-8 | 🔴 NOT STARTED | **P2** |
| RankingComponentsTest | `tests/Feature/Livewire/RankingComponentsTest.php` | ~4-6 | 🔴 NOT STARTED | **P2** |
| IndividualReportComponentsTest | `tests/Feature/Livewire/IndividualReportComponentsTest.php` | ~6-8 | 🔴 NOT STARTED | **P2** |
| **Integration Tests** | | | | |
| CrossComponentIntegrationTest | `tests/Feature/Livewire/CrossComponentIntegrationTest.php` | ~8-10 | 🔴 NOT STARTED | **P3** |
| **Other Tests** | | | | |
| EdgeCasesTest | `tests/Unit/EdgeCasesTest.php` | ~10 | 🔴 NOT STARTED | **P3** |
| PerformanceTest | `tests/Performance/PerformanceTest.php` | ~4 | 🔴 NOT STARTED | **P4** |

**Total Estimated:** ~72-91 additional tests (to be finalized during implementation)

---

## 🎯 Next Steps & Implementation Strategy

### **CRITICAL WORKFLOW** ⚠️

Before implementing ANY test file, Claude Code MUST:

1. **📖 Read & Analyze Component File**
   - Read full component source code
   - Identify ALL public methods
   - Map ALL event listeners in `$listeners` array
   - Find ALL DynamicStandardService calls
   - Identify ALL cache management logic
   - List ALL modal interactions

2. **📋 List Required Tests**
   - Create comprehensive test list based on actual code
   - Ensure 100% coverage of baseline-related methods
   - Document expected behavior for each test
   - Get user approval on test list

3. **✅ Implement Tests**
   - Write tests based on approved list
   - Follow Livewire testing best practices
   - Use TESTING_SCENARIOS as reference for patterns

---

## ✅ **COMPLETED IMPLEMENTATIONS**

### **StandardPsikometrik Component** ✅
- **File:** `tests/Feature/Livewire/StandardPsikometrikTest.php`
- **Tests:** 25 total (22 passing, 2 failing, 1 risky)
- **Coverage:** 88% baseline-related methods
- **Status:** ⚠️ Needs fix for 2 tests related to `hasCategoryAdjustments()`
- **Issues:**
  - Test 9.2d: Session adjustment detection after switching custom standard
  - Test 9.5a: Session adjustment detection after reset
- **Created Files:**
  - `tests/Feature/Livewire/StandardPsikometrikTest.php`
  - `database/factories/CustomStandardFactory.php`
  - `docs/TEST_PLAN_STANDARD_PSIKOMETRIK.md`
  - Updated `app/Models/CustomStandard.php` (added HasFactory)

### Immediate Priority (P1 - Producer Components)

**Step 1: Analyze & Test StandardPsikometrik**
```bash
# Claude will:
1. Read app/Livewire/Pages/GeneralReport/StandardPsikometrik.php
2. List all methods requiring tests
3. Create comprehensive test plan (~12-15 tests)
4. Implement tests
5. Run tests and verify 100% coverage
```

**Step 2: Analyze & Test StandardMc**
```bash
# Similar process for StandardMc component (~8-10 tests)
```

**Step 3: Analyze & Test Baseline Switching Edge Cases**
```bash
# Integration tests for switching behavior (~4-6 tests)
```

### Priority 2 (P2 - Consumer Components)

**Step 4-8: Analyze & Test Each Consumer Component**
- RekapRankingAssessment
- Statistic
- TrainingRecommendation
- Ranking Components (Psy/Mc)
- Individual Report Components

Each follows same workflow: Analyze → List → Approve → Implement

### Priority 3 (P3 - Integration & Edge Cases)

**Step 9: Cross-Component Integration Tests**
- End-to-end baseline propagation
- Multi-component event handling

**Step 10: Edge Cases**
- Zero participants, ties, boundaries, etc.

### Priority 4 (P4 - Performance)

**Step 11: Performance Regression Tests**
- Baseline performance benchmarks

---

## 📝 Notes

- **Service Layer**: 83% complete (64/77 tests passing)
- **Livewire Layer**: 88% complete for StandardPsikometrik (22/25 tests passing)
- **Total Progress**: ~63% complete (86/~136-154 total estimated tests)
- **Critical Bugs Fixed**:
  - ✅ Individual rating recalculation
  - ✅ Cache key completeness
- **Known Issues**:
  - ⚠️ 1 test failing in DynamicStandardServiceTest (non-critical)
  - ⚠️ 2 tests failing in StandardPsikometrikTest (session adjustment detection)
  - ⚠️ 1 test risky in StandardPsikometrikTest

### Important Reminders

⚠️ **Test Count Flexibility:**
- All test counts marked with `~` are ESTIMATES
- Actual count determined after component analysis
- Goal: 100% coverage of baseline-related functionality
- Better to have MORE thorough tests than hit exact estimate

⚠️ **Before Writing Tests:**
- ALWAYS read component source code first
- ALWAYS list all methods requiring tests
- ALWAYS get user approval on test plan
- NEVER assume test count from estimate

---

**For detailed test scenarios and expected behavior, see:**
[TESTING_SCENARIOS_BASELINE_3LAYER.md](./TESTING_SCENARIOS_BASELINE_3LAYER.md)

---

**Last Updated:** December 2025 (Updated with consumer components and flexible test counts)
