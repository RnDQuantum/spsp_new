# SPSP - Sistem Pemetaan & Statistik Psikologi

## 📋 Table of Contents
1. [Apa itu SPSP?](#apa-itu-spsp)
2. [Core Business Concepts](#core-business-concepts)
3. [3-Layer Priority System](#3-layer-priority-system)
4. [User Flow & Scenarios](#user-flow--scenarios)
5. [Data Architecture](#data-architecture)
6. [Key Principles](#key-principles)

---

## 🎯 Apa itu SPSP?

**SPSP (Sistem Pemetaan & Statistik Psikologi)** adalah aplikasi **Business Intelligence (BI)** untuk analisis penilaian psikologi, **BUKAN sistem CRUD biasa**.

### Perbedaan dengan Sistem CRUD:
| Aspek | Sistem CRUD | SPSP (BI System) |
|-------|-------------|------------------|
| **Tujuan** | Input & manage data | **Eksplorasi & analisis data** |
| **Data** | User creates/edits | **Pre-loaded, historical** |
| **Performance** | Real-time changes | **Caching, pre-calculation** |
| **User Interaction** | Form submissions | **Dynamic filtering, what-if analysis** |

### Use Case Utama:
```
Skenario: Institusi ingin merekrut 100 pegawai
├─ 4,905 peserta mengikuti tes psikologi
├─ Setiap peserta dinilai di 13 aspek (potensi + kompetensi)
└─ Institusi perlu: RANKING untuk memilih 100 terbaik

Pertanyaan Bisnis:
❓ Siapa top 100 kandidat berdasarkan standar institusi?
❓ Bagaimana jika kita ubah bobot "kepemimpinan" dari 10% → 15%?
❓ Bagaimana jika kita longgarkan standar passing 10%?
❓ Berapa banyak kandidat yang "Memenuhi Standar"?
```

**SPSP menjawab pertanyaan ini dalam hitungan DETIK, bukan jam.**

---

## 🧩 Core Business Concepts

### 1. Assessment Structure (Hierarki Penilaian)

```
AssessmentTemplate (Contoh: "P3K Kejaksaan 2025")
│
├─ CategoryType: POTENSI (25% bobot)
│  ├─ Aspect: Daya Pikir (weight: 5%)
│  │  ├─ SubAspect: Daya Analisa (rating: 3)
│  │  ├─ SubAspect: Kreativitas (rating: 4)
│  │  └─ SubAspect: Fleksibilitas (rating: 3)
│  │
│  ├─ Aspect: Sikap Kerja (weight: 7%)
│  │  ├─ SubAspect: Tanggung Jawab (rating: 4)
│  │  └─ SubAspect: Ketekunan (rating: 3)
│  │
│  └─ ... (total 4 aspects)
│
└─ CategoryType: KOMPETENSI (75% bobot)
   ├─ Aspect: Integritas (weight: 15%, rating: 4)
   ├─ Aspect: Kepemimpinan (weight: 10%, rating: 3)
   └─ ... (total 7 aspects, no sub-aspects)

ATURAN DATA-DRIVEN:
✅ Aspect dengan sub-aspects: Rating calculated dari sub-aspects
✅ Aspect tanpa sub-aspects: Rating langsung dari aspect
```

### 2. Assessment Results (Data Historis)

Setiap **Participant** mengikuti tes dan menghasilkan data **FINAL**:

```php
AspectAssessment {
    participant_id: 18576,
    aspect_id: 40,  // "Daya Pikir"
    individual_rating: 4.2,  // ✅ PRE-CALCULATED dari sub-aspects
    individual_score: 105.0, // = 4.2 * 25 (weight)
    // ... metadata lainnya
}

SubAspectAssessment {
    aspect_assessment_id: 2,
    sub_aspect_id: 65,  // "Daya Analisa"
    individual_rating: 4,  // Rating dari assessor
}
```

**🔑 KUNCI PENTING:**
- `individual_rating` adalah **DATA HISTORIS** yang **TIDAK PERNAH BERUBAH**
- Data ini di-store saat peserta **menyelesaikan tes**
- BI System hanya **MEMBACA** data ini untuk analisis

### 3. Standards (Baseline untuk Perbandingan)

**Standard** = Nilai minimal yang diharapkan dari kandidat

```
Contoh:
Aspect "Integritas" memiliki standard_rating = 4
Artinya: Institusi mengharapkan kandidat memiliki Integritas minimal rating 4

Participant A: individual_rating = 5 → ✅ "Di Atas Standar"
Participant B: individual_rating = 4 → ✅ "Memenuhi Standar"
Participant C: individual_rating = 3 → ❌ "Di Bawah Standar"
```

---

## 🏗️ 3-Layer Priority System

Sistem SPSP memiliki **3 lapisan prioritas** untuk menentukan **baseline standard** dan **weights** yang digunakan dalam perhitungan ranking.

### Layer Prioritization:
```
┌─────────────────────────────────────────────────────────────┐
│  Layer 1: SESSION ADJUSTMENT (Temporary Exploration)        │
│  Priority: HIGHEST                                           │
│  Storage: Session (per-user, temporary)                      │
│  Use Case: "Bagaimana jika saya ubah bobot X dari 10% → 15%"│
│  Lifetime: Sampai user close browser / reset adjustment      │
└─────────────────────────────────────────────────────────────┘
                          ↓ (if not exists)
┌─────────────────────────────────────────────────────────────┐
│  Layer 2: CUSTOM STANDARD (Institution Baseline)            │
│  Priority: MEDIUM                                            │
│  Storage: Database (custom_standards table)                  │
│  Use Case: "Institusi Kejaksaan punya standar khusus"       │
│  Lifetime: Permanent, bisa di-edit admin                     │
└─────────────────────────────────────────────────────────────┘
                          ↓ (if not exists)
┌─────────────────────────────────────────────────────────────┐
│  Layer 3: QUANTUM DEFAULT (System Baseline)                 │
│  Priority: LOWEST (fallback)                                 │
│  Storage: Database (aspects/sub_aspects table)               │
│  Use Case: Standar umum sistem (baseline awal)              │
│  Lifetime: Permanent, jarang berubah                         │
└─────────────────────────────────────────────────────────────┘
```

### Implementasi di Code:

```php
// DynamicStandardService.php
public function getAspectWeight(int $templateId, string $aspectCode): int
{
    // ✅ LAYER 1: Check session adjustment first
    $adjustments = Session::get("standard_adjustment.{$templateId}", []);
    if (isset($adjustments['aspect_weights'][$aspectCode])) {
        return $adjustments['aspect_weights'][$aspectCode];
    }

    // ✅ LAYER 2: Check custom standard if selected
    $customStandardId = Session::get("selected_standard.{$templateId}");
    if ($customStandardId) {
        $customStandard = CustomStandard::find($customStandardId);
        if ($customStandard && isset($customStandard->aspect_configs[$aspectCode]['weight'])) {
            return $customStandard->aspect_configs[$aspectCode]['weight'];
        }
    }

    // ✅ LAYER 3: Fallback to quantum default
    $aspect = Aspect::where('template_id', $templateId)
        ->where('code', $aspectCode)
        ->first();
    return $aspect ? $aspect->weight_percentage : 0;
}
```

### Key Concepts:

#### **Baseline Selection**
User memilih baseline di halaman `StandardPsikometrik.php` atau `StandardMc.php`:
- **Quantum Default**: Standar umum sistem
- **Custom Standard**: Standar khusus institusi (dari database)

#### **Session Adjustment**
User bisa **temporary adjust** baseline yang dipilih:
```
User memilih: Custom Standard "Kejaksaan 2025"
↓
Custom Standard memiliki:
- Integritas: weight 15%, rating 4
- Kepemimpinan: weight 10%, rating 3

User adjust sementara (session):
- Kepemimpinan: weight 15% (was 10%)  ← LAYER 1 override

Hasil akhir yang digunakan:
- Integritas: 15%, rating 4  ← dari Custom Standard (LAYER 2)
- Kepemimpinan: 15%, rating 3  ← dari Session Adjustment (LAYER 1)
```

---

## 👤 User Flow & Scenarios

### Scenario 1: Analisis dengan Quantum Default

```
User Journey:
1. [EventSelector] Pilih event: "P3K Kejaksaan 2025"
2. [PositionSelector] Pilih posisi: "Jaksa Penuntut Umum"
3. [StandardPsikometrik] Default baseline: "Quantum Default" ✅
4. [RekapRankingAssessment] Lihat ranking 4,905 peserta
   ├─ Top 1: WINDA FUJIATI (score: 422.5)
   ├─ Top 2: ALMIRA ISWAHYUDI (score: 417.5)
   └─ ...

Timeline: ~1.5 detik
```

### Scenario 2: Eksplorasi dengan Custom Standard

```
User Journey:
1. [EventSelector] Pilih event: "P3K Kejaksaan 2025"
2. [PositionSelector] Pilih posisi: "Jaksa Penuntut Umum"
3. [StandardPsikometrik] Switch baseline: "Custom Standard: Kejaksaan" ✅
   ├─ System loads: custom_standards table (id: 1)
   ├─ Weights berubah: Integritas 10% → 15%
   └─ Ratings berubah: Kepemimpinan 3 → 4
4. [RekapRankingAssessment] Ranking RECALCULATED
   ├─ Cache invalidated (config hash berubah)
   ├─ New top 1: Might be different!
   └─ ...

Timeline: ~1.5 detik (after optimization!)
```

### Scenario 3: What-If Analysis (Session Adjustment)

```
User Journey:
1. Baseline aktif: "Custom Standard: Kejaksaan"
2. [CategoryWeightEditor] User adjust:
   ├─ Potensi: 25% → 30%
   └─ Kompetensi: 75% → 70%
3. [AspectSelector] User adjust:
   ├─ Kepemimpinan: weight 10% → 15%
   └─ Integritas: rating 4 → 5
4. [ToleranceSelector] User adjust tolerance: 0% → 10%

Effect:
├─ Session stores temporary adjustments (LAYER 1)
├─ Cache invalidated automatically
├─ All ranking pages recalculate instantly
└─ User sees: "Bagaimana jika standar seperti ini?"

User dapat:
✅ Save adjustments → Keep for this session
✅ Reset → Back to Custom Standard baseline
✅ Create Custom Standard → Permanent save to database
```

### Scenario 4: Individual Report

```
User Journey:
1. [RekapRankingAssessment] Click participant: "WINDA FUJIATI"
2. [GeneralMapping] Tampil individual report:
   ├─ Overall conclusion: "Memenuhi Standar"
   ├─ Ranking: 1 of 4905
   ├─ Aspect breakdown:
   │  ├─ Daya Pikir: 4.2 (Standard: 3.5) ✅
   │  ├─ Sikap Kerja: 3.8 (Standard: 3.0) ✅
   │  └─ ...
   └─ Charts: Rating vs Standard comparison

Data Source:
✅ IndividualAssessmentService (NOT RankingService)
✅ Loads sub-aspect details untuk breakdown
✅ Respects same 3-layer priority for standards
```

---

## 🗄️ Data Architecture

### Key Tables:

```sql
-- ============================================
-- STRUCTURE (Template & Configuration)
-- ============================================

assessment_templates (id, name, code)
├─ P3K Kejaksaan 2025
├─ CPNS Kemenkumham 2024
└─ ...

category_types (id, template_id, code, name, weight_percentage)
├─ potensi (25%)
└─ kompetensi (75%)

aspects (id, category_type_id, code, name, weight_percentage, standard_rating)
├─ daya-pikir (5%, rating: N/A - has sub-aspects)
├─ sikap-kerja (7%, rating: N/A - has sub-aspects)
├─ integritas (15%, rating: 4)
└─ kepemimpinan (10%, rating: 3)

sub_aspects (id, aspect_id, code, name, standard_rating)
├─ daya-analisa (rating: 3)
├─ kreativitas (rating: 4)
└─ fleksibilitas (rating: 3)


-- ============================================
-- ASSESSMENT DATA (Historical Results)
-- ============================================

participants (id, event_id, position_formation_id, name, test_number)
├─ 18576: WINDA FUJIATI
├─ 6736: ALMIRA ISWAHYUDI
└─ ... (4,905 participants)

aspect_assessments (id, participant_id, aspect_id, individual_rating, individual_score)
├─ Participant 18576, Aspect "Daya Pikir": rating 4.2, score 105.0
├─ Participant 18576, Aspect "Integritas": rating 5.0, score 75.0
└─ ... (4,905 * 13 = 63,765 records)

sub_aspect_assessments (id, aspect_assessment_id, sub_aspect_id, individual_rating)
├─ Aspect Assessment 2, SubAspect "Daya Analisa": rating 4
├─ Aspect Assessment 2, SubAspect "Kreativitas": rating 5
└─ ... (for aspects with sub-aspects only)


-- ============================================
-- CUSTOM STANDARDS (Institution Baseline)
-- ============================================

custom_standards (id, institution_id, template_id, code, name,
                 category_weights, aspect_configs, sub_aspect_configs)
Example data:
{
  "id": 1,
  "code": "KEJAKSAAN-2025",
  "name": "Standar Khusus Kejaksaan 2025",
  "category_weights": {
    "potensi": 30,      // Override dari 25%
    "kompetensi": 70    // Override dari 75%
  },
  "aspect_configs": {
    "integritas": {
      "weight": 15,     // Override dari 10%
      "rating": 5,      // Override dari 4
      "active": true
    },
    "kepemimpinan": {
      "weight": 12,     // Override dari 10%
      "active": true    // No rating karena punya sub-aspects
    }
  },
  "sub_aspect_configs": {
    "daya-analisa": {
      "rating": 4,      // Override dari 3
      "active": true
    },
    "kreativitas": {
      "rating": 5,      // Override dari 4
      "active": false   // ❌ Disabled in custom standard
    }
  }
}
```

### Data Flow:

```
[Assessment Day - Data Creation]
Assessor memberikan rating → SubAspectAssessment created
                          → AspectAssessment created (individual_rating calculated)
                          → CategoryAssessment created
                          ↓
                    [Data FINAL & IMMUTABLE]

[Analysis Phase - Data Reading]
User pilih Event & Position
                          ↓
User pilih Baseline (Quantum/Custom)
                          ↓
RankingService reads:
- aspect_assessments.individual_rating (NEVER CHANGES)
- Applies weights from baseline (CAN CHANGE)
- Applies standards from baseline (CAN CHANGE)
                          ↓
Rankings calculated → Display to user
                          ↓
User adjust session → Recalculate instantly
```

---

## 🎯 Key Principles

### 1. **BI System, NOT CRUD**
```
❌ WRONG: User menginput data peserta baru
✅ RIGHT: User mengeksplorasi data existing dengan filter berbeda

❌ WRONG: User mengubah individual_rating peserta
✅ RIGHT: User mengubah baseline untuk re-rank peserta

❌ WRONG: Real-time data updates
✅ RIGHT: Historical data + dynamic analysis
```

### 2. **Separation of Concerns**

```
DATA (Immutable)                    BASELINE (Configurable)
├─ individual_rating (from test)    ├─ weights (how important)
├─ participant name                 ├─ standard_rating (minimum expected)
└─ test date                        └─ active/inactive aspects

Rankings = DATA × BASELINE
```

### 3. **Cache Invalidation Strategy**

```php
// Cache key includes configuration hash
$configHash = md5(json_encode([
    'aspect_weights' => $aspectWeightsForHash,
    'session' => session()->getId(),
]));

// Automatic invalidation scenarios:
✅ User adjusts weight → Hash changes → Cache miss
✅ User switches baseline → Hash changes → Cache miss
✅ User changes tolerance → Applied AFTER cache (instant)
⏱️ Admin updates custom standard → Max 60s delay (acceptable for BI)
```

### 4. **Performance Optimization Philosophy**

```
Optimization Target: "Exploration Speed"
NOT: "Real-time accuracy"

Acceptable Trade-offs:
✅ 60s cache TTL (faster exploration, minor delay on admin changes)
✅ Pre-calculated data (faster ranking, data locked after assessment)
✅ Component-level caching (faster UI, refresh on baseline change)

Unacceptable:
❌ 10+ second load times (kills user exploration flow)
❌ Inconsistent ranking order (data integrity issue)
❌ Lost session adjustments (user loses work)
```

### 5. **Data-Driven Architecture**

```php
// ✅ GOOD: Let data structure determine logic
if ($aspect->subAspects->isNotEmpty()) {
    // Has sub-aspects → Calculate rating from them
    $rating = $this->calculateFromSubAspects($aspect);
} else {
    // No sub-aspects → Use direct rating
    $rating = $aspect->standard_rating;
}

// ❌ BAD: Hard-coded aspect codes
if ($aspectCode === 'daya-pikir' || $aspectCode === 'sikap-kerja') {
    // Breaks when new aspects added
}
```

### 6. **3-Layer Priority is Sacred**

```
NEVER bypass the priority system:

❌ BAD: Direct database read
$weight = $aspect->weight_percentage;

✅ GOOD: Through DynamicStandardService
$weight = $dynamicStandardService->getAspectWeight($templateId, $aspectCode);
// This respects: Session → Custom → Quantum

Why?
- User expects adjustments to work
- Custom standards must override defaults
- Session exploration must be temporary
```

---

## 📚 Related Documentation

- [CRITICAL_FIX_CUSTOM_STANDARD_PERFORMANCE.md](./CRITICAL_FIX_CUSTOM_STANDARD_PERFORMANCE.md) - Performance optimization details
- [OPTIMASI_STANDARDMC_PERFORMANCE.md](./OPTIMASI_STANDARDMC_PERFORMANCE.md) - StandardMc component optimization
- [OPTIMASI_TRAINING_RECOMMENDATION_PERFORMANCE.md](./OPTIMASI_TRAINING_RECOMMENDATION_PERFORMANCE.md) - Training recommendation optimization

---

## 🔧 For Developers

### When Adding New Features:

**Checklist:**
- [ ] Does it respect 3-layer priority?
- [ ] Does it use DynamicStandardService for standards?
- [ ] Does it cache properly?
- [ ] Does it invalidate cache on baseline change?
- [ ] Is it data-driven (not hard-coded)?
- [ ] Does it work with both Quantum Default & Custom Standard?
- [ ] Have you tested with 4,905 participants scale?

### Common Pitfalls:

```php
// ❌ WRONG: Recalculating historical data
$individualRating = $this->calculateFromSubAspects($assessment);
// individual_rating is PRE-CALCULATED, never recalculate for ranking!

// ✅ RIGHT: Use pre-calculated data
$individualRating = (float) $assessment->individual_rating;

// ❌ WRONG: Eager loading everything
$assessments = AspectAssessment::with(['aspect.subAspects', 'subAspectAssessments'])->get();
// Loads 133K+ models unnecessarily

// ✅ RIGHT: Only load what you need
$assessments = AspectAssessment::query()->toBase()->get();
// Lightweight for ranking

// ❌ WRONG: Ignoring cache invalidation
$rankings = $this->calculateRankings();
Cache::forever('rankings', $rankings);
// Will show stale data after baseline change!

// ✅ RIGHT: Config-based cache key
$cacheKey = "rankings:{$configHash}";
Cache::remember($cacheKey, 60, fn() => $this->calculateRankings());
```

---

**Last Updated:** December 2025
**Maintainer:** SPSP Development Team
