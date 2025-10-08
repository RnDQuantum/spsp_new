# 📊 ASSESSMENT CALCULATION FLOW & LOGIC

**Project:** SPSP Analytics Dashboard
**Purpose:** Document complete calculation logic from raw data to final score
**Last Updated:** 2025-10-08

**Recent Updates:**
- ✅ Fixed `percentage_score` formula: now uses `(individual_rating / 5) × 100` instead of `(score / standardScore) × 100`
- ✅ Clarified Kompetensi `individual_rating` MUST be INTEGER 1-5 (not decimal)
- ✅ Fixed Final Assessment `totalStandardScore` formula
- ✅ Updated all examples to reflect correct calculations

---

## 📚 RELATED DOCUMENTATION

- 👉 **[DATABASE_AND_ASSESSMENT_LOGIC.md](./DATABASE_AND_ASSESSMENT_LOGIC.md)** - Complete database design, structure, relationships & assessment overview
- 👉 **[DATABASE_QC_AND_PERFORMANCE.md](./DATABASE_QC_AND_PERFORMANCE.md)** - QC progress tracking & performance optimization

---

## 🎯 OVERVIEW

Assessment calculation mengikuti pola **Bottom-Up Aggregation** dari level terkecil (sub-aspect) hingga menghasilkan final score.

### **Calculation Flow:**

```
Level 1: Sub-Aspect Individual Ratings (Raw Data from CI3)
    ↓ AGGREGATE (Average)
Level 2: Aspect Individual Ratings (Calculated or Direct)
    ↓ AGGREGATE (Sum with weights)
Level 3: Category Ratings (Potensi 40% + Kompetensi 60%)
    ↓ WEIGHTED CALCULATION
Level 4: Final Assessment Score + Achievement Percentage
```

### **Key Principles:**

1. ✅ **Sub-Aspects → Aspects:** Aggregation (average)
2. ✅ **Aspects → Categories:** Aggregation (sum with weights)
3. ✅ **Categories → Final:** Weighted calculation (40% + 60%)
4. ✅ **Gap Calculation:** Individual vs Standard at every level
5. ✅ **Snapshot Pattern:** Standard ratings preserved for historical integrity

---

## 📊 LEVEL 1: SUB-ASPECT ASSESSMENT

**Table:** `sub_aspect_assessments`

### **Data Structure:**

```sql
├─ sub_aspect_id (FK → sub_aspects)
├─ standard_rating (integer) ← Snapshot dari master table
├─ individual_rating (integer) ← Nilai aktual peserta dari CI3
└─ rating_label (string) ← "Baik", "Cukup", "Sangat Baik"
```

### **Karakteristik:**

| Aspect | Sub-Aspects | Source |
|--------|-------------|--------|
| **Potensi** | ✅ ADA (23 total) | WAJIB dari API |
| **Kompetensi** | ❌ KOSONG | N/A (direct assessment) |

### **Purpose:**

- ✅ **Raw assessment data** dari CI3
- ✅ **Detail breakdown** untuk laporan individual (Tujuan 2)
- ✅ **Gap comparison** per sub-aspect
- ✅ **Snapshot** standard_rating untuk historical integrity

### **Example Data:**

```
Aspect: Kecerdasan (Potensi)
├─ Sub-Aspect: Kecerdasan Umum
│   ├─ Standard Rating: 3 (dari master → snapshot)
│   ├─ Individual Rating: 3 (dari CI3)
│   └─ Rating Label: "Cukup"
│
├─ Sub-Aspect: Daya Tangkap
│   ├─ Standard Rating: 4 (dari master → snapshot)
│   ├─ Individual Rating: 4 (dari CI3)
│   └─ Rating Label: "Baik"
│
└─ ... (6 sub-aspects total untuk Kecerdasan)
```

---

## 📊 LEVEL 2: ASPECT ASSESSMENT

**Table:** `aspect_assessments`

### **Data Structure:**

```sql
├─ aspect_id (FK → aspects)
├─ standard_rating (decimal) ← Snapshot OR aggregated
├─ standard_score (decimal) ← Rating × weight percentage
├─ individual_rating (decimal) ← Aggregated OR direct
├─ individual_score (decimal) ← Rating × weight percentage
├─ gap_rating (decimal) ← Individual - Standard
├─ gap_score (decimal) ← Individual Score - Standard Score
├─ percentage_score (integer) ← For spider chart: (individual_rating / 5) × 100
│                                  NOTE: Using rating (1-5 scale), NOT score!
│                                  This ensures percentage is 0-100% for visualization
├─ conclusion_code (string) ← "below_standard", "meets_standard", "exceeds_standard"
└─ conclusion_text (string) ← "Kurang Memenuhi Standard"
```

### **Calculation Logic:**

#### **POTENSI (with sub-aspects):**

```php
<?php

namespace App\Services;

use App\Models\AspectAssessment;
use App\Models\SubAspectAssessment;
use App\Models\Aspect;

class AspectCalculationService
{
    /**
     * Calculate aspect assessment from sub-aspects
     */
    public function calculatePotensiAspect(AspectAssessment $assessment): void
    {
        // 1. Get all sub-aspect assessments
        $subAssessments = SubAspectAssessment::where(
            'aspect_assessment_id',
            $assessment->id
        )->get();

        if ($subAssessments->isEmpty()) {
            return; // Skip if no sub-aspects
        }

        // 2. Calculate individual_rating = AVERAGE of sub-aspects
        $individualRating = $subAssessments->avg('individual_rating');

        // 3. Get aspect weight from master
        $aspect = Aspect::find($assessment->aspect_id);

        // 4. Calculate individual_score = rating × weight
        $individualScore = $individualRating * ($aspect->weight_percentage / 100);

        // 5. Calculate standard_score = standard_rating × weight
        $standardScore = $assessment->standard_rating * ($aspect->weight_percentage / 100);

        // 6. Calculate gaps
        $gapRating = $individualRating - $assessment->standard_rating;
        $gapScore = $individualScore - $standardScore;

        // 7. Calculate percentage for spider chart (rating out of max scale 5)
        $percentageScore = round(($individualRating / 5) * 100);

        // 8. Determine conclusion
        $conclusionCode = $this->determineConclusion($gapRating);

        // 9. Update assessment
        $assessment->update([
            'individual_rating' => round($individualRating, 2),
            'individual_score' => round($individualScore, 2),
            'standard_score' => round($standardScore, 2),
            'gap_rating' => round($gapRating, 2),
            'gap_score' => round($gapScore, 2),
            'percentage_score' => $percentageScore,
            'conclusion_code' => $conclusionCode,
            'conclusion_text' => $this->getConclusionText($conclusionCode),
        ]);
    }

    private function determineConclusion(float $gapRating): string
    {
        if ($gapRating < -0.5) {
            return 'below_standard';
        } elseif ($gapRating < 0.5) {
            return 'meets_standard';
        } else {
            return 'exceeds_standard';
        }
    }

    private function getConclusionText(string $code): string
    {
        return match($code) {
            'below_standard' => 'Kurang Memenuhi Standard',
            'meets_standard' => 'Memenuhi Standard',
            'exceeds_standard' => 'Melebihi Standard',
        };
    }
}
```

#### **KOMPETENSI (no sub-aspects):**

```php
/**
 * Calculate kompetensi aspect (direct from API, no aggregation)
 *
 * IMPORTANT: individualRating MUST be INTEGER 1-5 from API
 */
public function calculateKompetensiAspect(
    AspectAssessment $assessment,
    int $individualRating // From API - MUST BE INTEGER 1-5
): void
{
    // 1. Get aspect weight from master
    $aspect = Aspect::find($assessment->aspect_id);

    // 2. Calculate scores
    $individualScore = $individualRating * ($aspect->weight_percentage / 100);
    $standardScore = $assessment->standard_rating * ($aspect->weight_percentage / 100);

    // 3. Calculate gaps
    $gapRating = $individualRating - $assessment->standard_rating;
    $gapScore = $individualScore - $standardScore;

    // 4. Calculate percentage (rating out of max scale 5)
    $percentageScore = round(($individualRating / 5) * 100);

    // 5. Determine conclusion
    $conclusionCode = $this->determineConclusion($gapRating);

    // 6. Update assessment
    $assessment->update([
        'individual_rating' => $individualRating, // Already integer from API
        'individual_score' => round($individualScore, 2),
        'standard_score' => round($standardScore, 2),
        'gap_rating' => round($gapRating, 2),
        'gap_score' => round($gapScore, 2),
        'percentage_score' => $percentageScore,
        'conclusion_code' => $conclusionCode,
        'conclusion_text' => $this->getConclusionText($conclusionCode),
    ]);
}
```

### **Example Data:**

```
POTENSI - Aspect: Kecerdasan (30% weight)
├─ Standard Rating: 3.20 (snapshot dari master)
├─ Standard Score: 96.00 (3.20 × 30%)
├─ Individual Rating: 3.50 (aggregated dari 6 sub-aspects: AVG)
├─ Individual Score: 105.00 (3.50 × 30%)
├─ Gap Rating: +0.30 (exceeds standard)
├─ Gap Score: +9.00
├─ Percentage: 70% (3.50/5 × 100)
└─ Conclusion: "Melebihi Standard" (exceeds_standard)

KOMPETENSI - Aspect: Integritas (12% weight)
├─ Standard Rating: 3.50 (snapshot dari master)
├─ Standard Score: 42.00 (3.50 × 12%)
├─ Individual Rating: 3 (INTEGER 1-5, direct dari API, no aggregation)
├─ Individual Score: 36.00 (3 × 12%)
├─ Gap Rating: -0.50 (below standard)
├─ Gap Score: -6.00
├─ Percentage: 60% (3/5 × 100)
└─ Conclusion: "Kurang Memenuhi Standard" (below_standard)
```

---

## 📊 LEVEL 3: CATEGORY ASSESSMENT

**Table:** `category_assessments`

### **Data Structure:**

```sql
├─ category_type_id (FK → category_types)
├─ total_standard_rating (decimal) ← SUM of aspect standard_ratings
├─ total_standard_score (decimal) ← SUM of aspect standard_scores
├─ total_individual_rating (decimal) ← SUM of aspect individual_ratings
├─ total_individual_score (decimal) ← SUM of aspect individual_scores
├─ gap_rating (decimal) ← Total Individual - Total Standard
├─ gap_score (decimal) ← Total Individual Score - Total Standard Score
├─ conclusion_code (string) ← "DBS", "MS", "K", "SK"
└─ conclusion_text (string) ← "DI BAWAH STANDARD", "SANGAT KOMPETEN"
```

### **Calculation Logic:**

```php
<?php

namespace App\Services;

use App\Models\CategoryAssessment;
use App\Models\AspectAssessment;

class CategoryCalculationService
{
    /**
     * Calculate category assessment from aspects
     */
    public function calculateCategory(CategoryAssessment $categoryAssessment): void
    {
        // 1. Get all aspect assessments for this category
        $aspectAssessments = AspectAssessment::where(
            'category_assessment_id',
            $categoryAssessment->id
        )->get();

        // 2. Aggregate all aspects
        $totalStandardRating = $aspectAssessments->sum('standard_rating');
        $totalStandardScore = $aspectAssessments->sum('standard_score');
        $totalIndividualRating = $aspectAssessments->sum('individual_rating');
        $totalIndividualScore = $aspectAssessments->sum('individual_score');

        // 3. Calculate gaps
        $gapRating = $totalIndividualRating - $totalStandardRating;
        $gapScore = $totalIndividualScore - $totalStandardScore;

        // 4. Determine conclusion based on gap score
        $conclusionCode = $this->determineCategoryConclusion($gapScore);

        // 5. Update category assessment
        $categoryAssessment->update([
            'total_standard_rating' => round($totalStandardRating, 2),
            'total_standard_score' => round($totalStandardScore, 2),
            'total_individual_rating' => round($totalIndividualRating, 2),
            'total_individual_score' => round($totalIndividualScore, 2),
            'gap_rating' => round($gapRating, 2),
            'gap_score' => round($gapScore, 2),
            'conclusion_code' => $conclusionCode,
            'conclusion_text' => $this->getCategoryConclusionText($conclusionCode),
        ]);
    }

    private function determineCategoryConclusion(float $gapScore): string
    {
        if ($gapScore < -10) {
            return 'DBS'; // Di Bawah Standard
        } elseif ($gapScore < 0) {
            return 'MS'; // Memenuhi Standard
        } elseif ($gapScore < 20) {
            return 'K'; // Kompeten
        } else {
            return 'SK'; // Sangat Kompeten
        }
    }

    private function getCategoryConclusionText(string $code): string
    {
        return match($code) {
            'DBS' => 'DI BAWAH STANDARD',
            'MS' => 'MEMENUHI STANDARD',
            'K' => 'KOMPETEN',
            'SK' => 'SANGAT KOMPETEN',
        };
    }
}
```

### **Example Data:**

```
POTENSI (40% weight - 4 aspects)
├─ Kecerdasan: Individual 105.00 vs Standard 96.00 (+9.00)
├─ Sikap Kerja: Individual 80.00 vs Standard 75.00 (+5.00)
├─ Hubungan Sosial: Individual 65.00 vs Standard 70.00 (-5.00)
├─ Kepribadian: Individual 85.00 vs Standard 90.00 (-5.00)
│
├─ Total Standard Rating: 11.94
├─ Total Standard Score: 300.21
├─ Total Individual Rating: 11.83
├─ Total Individual Score: 294.25
├─ Gap Rating: -0.11
├─ Gap Score: -5.97 (below standard, but > -10)
└─ Conclusion: "MEMENUHI STANDARD" (MS)

KOMPETENSI (60% weight - 9 aspects)
├─ Integritas: Individual 36.96 vs Standard 42.00 (-5.04)
├─ Kerjasama: Individual 38.50 vs Standard 33.00 (+5.50)
├─ Komunikasi: Individual 35.00 vs Standard 30.00 (+5.00)
├─ ... (9 aspects total)
│
├─ Total Standard Rating: 24.30
├─ Total Standard Score: 270.00
├─ Total Individual Rating: 27.48
├─ Total Individual Score: 305.36
├─ Gap Rating: +3.18
├─ Gap Score: +35.36 (exceeds standard > 20)
└─ Conclusion: "SANGAT KOMPETEN" (SK)
```

---

## 📊 LEVEL 4: FINAL ASSESSMENT

**Table:** `final_assessments`

### **Data Structure:**

```sql
├─ potensi_weight (integer) ← 40 (dari template)
├─ potensi_standard_score (decimal)
├─ potensi_individual_score (decimal)
├─ kompetensi_weight (integer) ← 60 (dari template)
├─ kompetensi_standard_score (decimal)
├─ kompetensi_individual_score (decimal)
├─ total_standard_score (decimal) ← Weighted sum
├─ total_individual_score (decimal) ← Weighted sum
├─ achievement_percentage (decimal) ← (Individual / Standard) × 100%
├─ final_conclusion_code (string) ← "TMS", "MMS", "MS"
└─ final_conclusion_text (string)
```

### **Calculation Formula:**

```php
<?php

namespace App\Services;

use App\Models\FinalAssessment;
use App\Models\CategoryAssessment;
use App\Models\Participant;

class FinalAssessmentService
{
    /**
     * Calculate final assessment from category assessments
     */
    public function calculateFinal(Participant $participant): FinalAssessment
    {
        // 1. Get category assessments
        $potensiAssessment = CategoryAssessment::where('participant_id', $participant->id)
            ->where('category_type_id', 1) // Potensi
            ->first();

        $kompetensiAssessment = CategoryAssessment::where('participant_id', $participant->id)
            ->where('category_type_id', 2) // Kompetensi
            ->first();

        // 2. Get weights from template (via category_types)
        $potensiWeight = 40; // From template
        $kompetensiWeight = 60; // From template

        // 3. Calculate weighted scores
        $totalStandardScore =
            ($potensiAssessment->total_standard_score * ($potensiWeight / 100)) +
            ($kompetensiAssessment->total_standard_score * ($kompetensiWeight / 100));

        $totalIndividualScore =
            ($potensiAssessment->total_individual_score * ($potensiWeight / 100)) +
            ($kompetensiAssessment->total_individual_score * ($kompetensiWeight / 100));

        // 4. Calculate achievement percentage
        $achievementPercentage = ($totalIndividualScore / $totalStandardScore) * 100;

        // 5. Determine final conclusion
        $conclusionCode = $this->determineFinalConclusion($achievementPercentage);

        // 6. Create or update final assessment
        return FinalAssessment::updateOrCreate(
            ['participant_id' => $participant->id],
            [
                'potensi_weight' => $potensiWeight,
                'potensi_standard_score' => round($potensiAssessment->total_standard_score, 2),
                'potensi_individual_score' => round($potensiAssessment->total_individual_score, 2),
                'kompetensi_weight' => $kompetensiWeight,
                'kompetensi_standard_score' => round($kompetensiAssessment->total_standard_score, 2),
                'kompetensi_individual_score' => round($kompetensiAssessment->total_individual_score, 2),
                'total_standard_score' => round($totalStandardScore, 2),
                'total_individual_score' => round($totalIndividualScore, 2),
                'achievement_percentage' => round($achievementPercentage, 2),
                'final_conclusion_code' => $conclusionCode,
                'final_conclusion_text' => $this->getFinalConclusionText($conclusionCode),
            ]
        );
    }

    private function determineFinalConclusion(float $achievementPercentage): string
    {
        if ($achievementPercentage < 80) {
            return 'TMS'; // Tidak Memenuhi Syarat
        } elseif ($achievementPercentage < 90) {
            return 'MMS'; // Masih Memenuhi Syarat
        } else {
            return 'MS'; // Memenuhi Syarat
        }
    }

    private function getFinalConclusionText(string $code): string
    {
        return match($code) {
            'TMS' => 'TIDAK MEMENUHI SYARAT (TMS)',
            'MMS' => 'MASIH MEMENUHI SYARAT (MMS)',
            'MS' => 'MEMENUHI SYARAT (MS)',
        };
    }
}
```

### **Example Data:**

```
Participant: EKA FEBRIYANI, s.si

POTENSI (40%):
├─ Standard Score: 300.21
├─ Individual Score: 294.25
└─ Weighted: 294.25 × 40% = 117.70

KOMPETENSI (60%):
├─ Standard Score: 270.00
├─ Individual Score: 305.36
└─ Weighted: 305.36 × 60% = 183.22

FINAL CALCULATION:
├─ Total Standard Score: (300.21 × 0.40) + (270.00 × 0.60) = 282.08
├─ Total Individual Score: 117.70 + 183.22 = 300.92
├─ Achievement Percentage: (300.92 / 282.08) × 100 = 106.71%
├─ Threshold: 106.71% >= 90%
└─ Conclusion: "MEMENUHI SYARAT (MS)"
```

---

## 🔄 TEMPLATE STANDARD ROLE & SNAPSHOT PATTERN

### **Template Defines Standards at EACH Level:**

```
Template: "P3K Standard 2025"
│
├─ Category Types:
│   ├─ Potensi: 40% weight
│   └─ Kompetensi: 60% weight
│
├─ Aspects (with standard_rating & weight):
│   ├─ Kecerdasan: 30% weight, standard_rating: 3.20
│   ├─ Sikap Kerja: 20% weight, standard_rating: 3.50
│   ├─ Integritas: 12% weight, standard_rating: 3.50
│   └─ ... (13 aspects total)
│
└─ Sub-Aspects (with standard_rating):
    ├─ Kecerdasan Umum: standard_rating: 3
    ├─ Daya Tangkap: standard_rating: 4
    └─ ... (23 sub-aspects total untuk Potensi)
```

### **Snapshot Pattern Implementation:**

**Timeline Example:**

```
Jan 2025: Template "P3K 2025" created
├─ aspects.standard_rating = 3.20 (current master value)
│
├─ Participant A assessed (2025-01-15)
│   └─ aspect_assessments.standard_rating = 3.20 (SNAPSHOT) ✅
│   └─ Gap calculated with 3.20
│
├─ Mar 2025: Business decision to update standard
│   └─ aspects.standard_rating UPDATED to 3.50 (new master value)
│
├─ Participant B assessed (2025-03-20)
│   └─ aspect_assessments.standard_rating = 3.50 (NEW SNAPSHOT) ✅
│   └─ Gap calculated with 3.50
│
└─ Historical Integrity Preserved:
    ├─ Participant A gap still shows 3.20 comparison ✅
    └─ Participant B gap shows 3.50 comparison ✅
```

### **Why Snapshot Pattern is Critical:**

1. ✅ **Historical Data Integrity**
   - Assessment results remain accurate to the time they were performed
   - No retroactive changes when standards are updated

2. ✅ **Accurate Gap Comparison**
   - Participants compared against standards applicable at their assessment time
   - Fair comparison across different time periods

3. ✅ **Template Evolution**
   - Standards can be improved over time without breaking historical data
   - Business can adjust standards based on experience

4. ✅ **Audit Trail**
   - Complete history of standard changes
   - Compliance & regulatory requirements

5. ✅ **Performance Optimization**
   - No need to recalculate historical assessments
   - Faster queries (no JOIN to master tables for standards)

---

## 🎨 DYNAMIC TEMPLATE STRUCTURE

### **Key Concept:**

Template structure is **DYNAMIC** - different templates can have:
- Different number of aspects
- Different aspect weights
- Different standard ratings
- Different sub-aspects structure

### **Examples:**

```
Template A: "P3K Standard 2025"
├─ Potensi (40%)
│   ├─ Kecerdasan (30%) ← 6 sub-aspects
│   ├─ Sikap Kerja (20%) ← 7 sub-aspects
│   ├─ Hubungan Sosial (20%) ← 4 sub-aspects
│   └─ Kepribadian (30%) ← 6 sub-aspects
└─ Kompetensi (60%)
    └─ 9 aspects (no sub-aspects)

Template B: "CPNS JPT Pratama 2025" (DIFFERENT!)
├─ Potensi (40%)
│   ├─ Kecerdasan (50%) ← DIFFERENT WEIGHT! 10 sub-aspects
│   └─ Kepribadian (50%) ← DIFFERENT WEIGHT! 8 sub-aspects
└─ Kompetensi (60%)
    ├─ Kepemimpinan (20%) ← NEW ASPECT! 5 sub-aspects
    └─ 7 aspects (different from Template A)

Template C: "Administrator 2025" (TOTALLY DIFFERENT!)
├─ Potensi (30%) ← DIFFERENT CATEGORY WEIGHT!
│   └─ 2 aspects only
└─ Kompetensi (70%) ← DIFFERENT CATEGORY WEIGHT!
    └─ 5 aspects
```

### **Database Support:**

- ✅ `aspects.template_id` (DUAL FK for multi-template support)
- ✅ `aspects.weight_percentage` (per template dapat berbeda)
- ✅ UNIQUE constraint: `(template_id, category_type_id, code)`

This design allows same aspect code (e.g., "Kecerdasan") to have different weights in different templates.

---

## 📡 API DATA REQUIREMENTS

### **WAJIB vs OPSIONAL**

**CRITICAL:** Data berikut WAJIB dikirim oleh API CI3:

| Data Element | Status | Alasan | Tujuan |
|--------------|--------|--------|--------|
| **Template Structure Lengkap** | ✅ WAJIB | Snapshot pattern & dynamic template | Tujuan 1 & 2 |
| **Aspects + standard_rating** | ✅ WAJIB | Gap comparison | Tujuan 1 & 2 |
| **Aspects + weight_percentage** | ✅ WAJIB | Calculation & aggregation | Tujuan 1 & 2 |
| **Sub-Aspects + standard_rating** | ✅ WAJIB | Laporan individual detail | **Tujuan 2** |
| **Sub-Aspects + individual_rating (Potensi)** | ✅ WAJIB | Raw assessment data | **Tujuan 2** |
| **Aspects individual_rating (Potensi)** | ❌ OPSIONAL | Bisa di-calculate | Tujuan 1 |
| **Aspects individual_rating (Kompetensi)** | ✅ WAJIB | Direct assessment | Tujuan 1 & 2 |
| **Interpretations Text** | ✅ WAJIB | Narasi laporan | **Tujuan 2** |
| **Psychological Test Results** | ✅ WAJIB | Tes kejiwaan | **Tujuan 2** |

### **Key Points:**

1. **Sub-Aspects TIDAK BOLEH KOSONG untuk Potensi**
   - Sub-aspects WAJIB ada dengan individual_rating
   - Diperlukan untuk laporan individual detail (Tujuan 2)
   - Tanpa sub-aspects, laporan individual tidak bisa menampilkan breakdown

2. **Sub-Aspects KOSONG untuk Kompetensi**
   - Kompetensi tidak punya sub-aspects (by design)
   - Assessment langsung di level aspect

3. **Standard Rating WAJIB di Semua Level**
   - Untuk gap comparison (individual vs standard)
   - Untuk historical data integrity (snapshot pattern)

### **Example API Response:**

```json
{
  "template": {
    "aspects": [
      {
        "code": "kecerdasan",
        "standard_rating": 3.20,
        "weight_percentage": 30,
        "sub_aspects": [
          {
            "code": "kecerdasan_umum",
            "standard_rating": 3
          },
          {
            "code": "daya_tangkap",
            "standard_rating": 4
          }
        ]
      },
      {
        "code": "integritas",
        "standard_rating": 3.50,
        "weight_percentage": 12,
        "sub_aspects": []
      }
    ]
  },
  "participant": {
    "assessments": {
      "potensi": [
        {
          "code": "kecerdasan",
          "individual_rating": null,
          "sub_aspects": [
            {
              "code": "kecerdasan_umum",
              "individual_rating": 3
            },
            {
              "code": "daya_tangkap",
              "individual_rating": 4
            }
          ]
        }
      ],
      "kompetensi": [
        {
          "code": "integritas",
          "individual_rating": 3.08,
          "sub_aspects": []
        }
      ]
    }
  }
}
```

---

## 📝 RELATED DOCUMENTATION

- [PROJECT_DOCUMENTATION.md](./PROJECT_DOCUMENTATION.md) - Main project overview
- [DATABASE_DESIGN.md](./DATABASE_DESIGN.md) - Complete database structure
- [DATABASE_QC_PROGRESS.md](./DATABASE_QC_PROGRESS.md) - QC tracking
- [API_SPECIFICATION.md](./API_SPECIFICATION.md) - Full API contract

---

**Version:** 1.0
**Status:** ✅ Complete & Production-Ready
**Last Updated:** 2025-10-06
