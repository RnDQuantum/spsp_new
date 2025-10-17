# 📊 ASSESSMENT CALCULATION FLOW & LOGIC

**Project:** SPSP Analytics Dashboard
**Purpose:** Document complete calculation logic from raw data to final score
**Last Updated:** 2025-10-17

**Recent Updates:**
- ✅ **ARCHITECTURE CHANGE (2025-10-17):** Templates now per position, weights are dynamic per position
- ✅ **CRITICAL FIX:** Corrected score calculation formula to `rating × weight_percentage` (not `(rating/5) × 100 × weight%`)
- ✅ Fixed `percentage_score` formula: now uses `(individual_rating / 5) × 100` instead of `(score / standardScore) × 100`
- ✅ Clarified Kompetensi `individual_rating` MUST be INTEGER 1-5 (not decimal)
- ✅ Updated all examples to reflect correct calculations
- ✅ Added description field to aspects table (from template designer)

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
├─ standard_rating (decimal) ← Snapshot OR aggregated from sub-aspects
├─ standard_score (decimal) ← standard_rating × weight_percentage
├─ individual_rating (decimal) ← AVG(sub-aspects) OR direct from API
├─ individual_score (decimal) ← individual_rating × weight_percentage
├─ gap_rating (decimal) ← individual_rating - standard_rating
├─ gap_score (decimal) ← individual_score - standard_score
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

        // 4. Calculate scores
        // Formula: score = rating × weight_percentage
        $standardScore = $assessment->standard_rating * $aspect->weight_percentage;
        $individualScore = $individualRating * $aspect->weight_percentage;

        // 5. Calculate gaps
        $gapRating = $individualRating - $assessment->standard_rating;
        $gapScore = $individualScore - $standardScore;

        // 6. Calculate percentage for spider chart (rating out of max scale 5)
        $percentageScore = round(($individualRating / 5) * 100);

        // 7. Determine conclusion
        $conclusionCode = $this->determineConclusion($gapRating);

        // 8. Update assessment
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
    // Formula: score = rating × weight_percentage
    $standardScore = $assessment->standard_rating * $aspect->weight_percentage;
    $individualScore = $individualRating * $aspect->weight_percentage;

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
├─ Standard Rating: 3.50 (snapshot dari master)
├─ Standard Score: 105.00 (3.50 × 30)
├─ Individual Rating: 4.00 (aggregated dari 6 sub-aspects: AVG)
├─ Individual Score: 120.00 (4.00 × 30)
├─ Gap Rating: +0.50 (exceeds standard)
├─ Gap Score: +15.00
├─ Percentage: 80% (4.00/5 × 100)
└─ Conclusion: "Melebihi Standard" (exceeds_standard)

KOMPETENSI - Aspect: Integritas (12% weight)
├─ Standard Rating: 3.50 (snapshot dari master)
├─ Standard Score: 42.00 (3.50 × 12)
├─ Individual Rating: 4 (INTEGER 1-5, direct dari API, no aggregation)
├─ Individual Score: 48.00 (4 × 12)
├─ Gap Rating: +0.50 (exceeds standard)
├─ Gap Score: +6.00
├─ Percentage: 80% (4/5 × 100)
└─ Conclusion: "Melebihi Standard" (exceeds_standard)
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
├─ Kecerdasan (30%): Individual 120.00 vs Standard 105.00 (+15.00)
├─ Sikap Kerja (20%): Individual 71.43 vs Standard 64.00 (+7.43)
├─ Hubungan Sosial (20%): Individual 90.00 vs Standard 75.00 (+15.00)
├─ Kepribadian (30%): Individual 130.00 vs Standard 110.10 (+19.90)
│
├─ Total Standard Rating: 14.12
├─ Total Standard Score: 354.10 (105+64+75+110.10)
├─ Total Individual Rating: 16.40
├─ Total Individual Score: 411.43 (120+71.43+90+130)
├─ Gap Rating: +2.28
├─ Gap Score: +57.33 (exceeds standard > 20)
└─ Conclusion: "SANGAT KOMPETEN" (SK)

KOMPETENSI (60% weight - 9 aspects)
├─ Integritas (12%): Individual 48.00 vs Standard 42.00 (+6.00)
├─ Kerjasama (11%): Individual 44.00 vs Standard 35.75 (+8.25)
├─ Komunikasi (11%): Individual 41.25 vs Standard 41.25 (0.00)
├─ Orientasi Pada Hasil (11%): Individual 38.50 vs Standard 38.50 (0.00)
├─ Pelayanan Publik (11%): Individual 39.60 vs Standard 39.60 (0.00)
├─ Pengembangan Diri & Orang Lain (11%): Individual 37.40 vs Standard 37.40 (0.00)
├─ Mengelola Perubahan (11%): Individual 36.30 vs Standard 36.30 (0.00)
├─ Pengambilan Keputusan (11%): Individual 37.95 vs Standard 37.95 (0.00)
├─ Perekat Bangsa (11%): Individual 39.05 vs Standard 39.05 (0.00)
│
├─ Total Standard Rating: 31.30
├─ Total Standard Score: 348.00 (sum of all aspects)
├─ Total Individual Rating: 36.00
├─ Total Individual Score: 362.05 (sum of all aspects)
├─ Gap Rating: +4.70
├─ Gap Score: +14.05 (kompeten: 0 < gap < 20)
└─ Conclusion: "KOMPETEN" (K)
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
     *
     * IMPORTANT (2025-10-17): Weights are now DYNAMIC per position!
     * Each position has its own template with different category weights.
     */
    public function calculateFinal(Participant $participant): FinalAssessment
    {
        // 1. Load participant with position and template relationship
        $participant->loadMissing('positionFormation.template');

        // 2. Get template from participant's position
        $template = $participant->positionFormation->template;

        // 3. Get category types for this template (DYNAMIC WEIGHTS!)
        $potensiCategory = CategoryType::where('template_id', $template->id)
            ->where('code', 'potensi')
            ->firstOrFail();

        $kompetensiCategory = CategoryType::where('template_id', $template->id)
            ->where('code', 'kompetensi')
            ->firstOrFail();

        // 4. Get category assessments
        $potensiAssessment = CategoryAssessment::where('participant_id', $participant->id)
            ->where('category_type_id', $potensiCategory->id)
            ->first();

        $kompetensiAssessment = CategoryAssessment::where('participant_id', $participant->id)
            ->where('category_type_id', $kompetensiCategory->id)
            ->first();

        // 5. Get weights from template (DYNAMIC - not hardcoded!)
        // Could be 30/70, 40/60, 50/50, etc. depending on position's template
        $potensiWeight = $potensiCategory->weight_percentage; // e.g., 30, 40, 50
        $kompetensiWeight = $kompetensiCategory->weight_percentage; // e.g., 70, 60, 50

        // 6. Calculate weighted scores
        $totalStandardScore =
            ($potensiAssessment->total_standard_score * ($potensiWeight / 100)) +
            ($kompetensiAssessment->total_standard_score * ($kompetensiWeight / 100));

        $totalIndividualScore =
            ($potensiAssessment->total_individual_score * ($potensiWeight / 100)) +
            ($kompetensiAssessment->total_individual_score * ($kompetensiWeight / 100));

        // 7. Calculate achievement percentage
        $achievementPercentage = ($totalIndividualScore / $totalStandardScore) * 100;

        // 8. Determine final conclusion
        $conclusionCode = $this->determineFinalConclusion($achievementPercentage);

        // 9. Create or update final assessment (with dynamic weights!)
        return FinalAssessment::updateOrCreate(
            ['participant_id' => $participant->id],
            [
                'potensi_weight' => $potensiWeight, // DYNAMIC from template
                'potensi_standard_score' => round($potensiAssessment->total_standard_score, 2),
                'potensi_individual_score' => round($potensiAssessment->total_individual_score, 2),
                'kompetensi_weight' => $kompetensiWeight, // DYNAMIC from template
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

**Example 1: Staff Position (Balanced Weights)**

```
Participant: ANDI WIJAYA, S.KOM
Position: Analis Kebijakan
Template: "Staff Standard v1"

POTENSI (50% - from template):
├─ Standard Score: 354.10
├─ Individual Score: 411.43
└─ Weighted: 411.43 × 50% = 205.72

KOMPETENSI (50% - from template):
├─ Standard Score: 348.00
├─ Individual Score: 362.05
└─ Weighted: 362.05 × 50% = 181.03

FINAL CALCULATION:
├─ Total Standard Score: (354.10 × 0.50) + (348.00 × 0.50) = 351.05
├─ Total Individual Score: 205.72 + 181.03 = 386.75
├─ Achievement Percentage: (386.75 / 351.05) × 100 = 110.17%
├─ Threshold: 110.17% >= 90%
└─ Conclusion: "MEMENUHI SYARAT (MS)"
```

**Example 2: Supervisor Position (Competency-Heavy Weights)**

```
Participant: BUDI SANTOSO, S.E.
Position: Auditor
Template: "Supervisor Standard v1"

POTENSI (30% - from template):
├─ Standard Score: 354.10
├─ Individual Score: 411.43
└─ Weighted: 411.43 × 30% = 123.43

KOMPETENSI (70% - from template):
├─ Standard Score: 348.00
├─ Individual Score: 362.05
└─ Weighted: 362.05 × 70% = 253.44

FINAL CALCULATION:
├─ Total Standard Score: (354.10 × 0.30) + (348.00 × 0.70) = 349.83
├─ Total Individual Score: 123.43 + 253.44 = 376.87
├─ Achievement Percentage: (376.87 / 349.83) × 100 = 107.73%
├─ Threshold: 107.73% >= 90%
└─ Conclusion: "MEMENUHI SYARAT (MS)"

NOTE: Different position (Auditor vs Analis) = different template = different weights!
```

**Key Takeaways:**
- ✅ Achievement percentage > 100% adalah NORMAL dan menunjukkan peserta EXCEED standard
- ✅ Weights are DYNAMIC per position (not always 40/60)
- ✅ Same participant data, different position = different final score due to different weights

---

## 🔄 TEMPLATE STANDARD ROLE & SNAPSHOT PATTERN

### **Architecture: Template Per Position (Updated 2025-10-17)**

```
Event: P3K Kejaksaan 2025
│
├─ Position: Auditor
│   └─ Template: "Supervisor Standard v1"
│       ├─ Potensi: 30% weight (different from other positions!)
│       └─ Kompetensi: 70% weight
│
├─ Position: Analis Kebijakan
│   └─ Template: "Staff Standard v1"
│       ├─ Potensi: 50% weight (balanced)
│       └─ Kompetensi: 50% weight
│
└─ Position: Fisikawan Medis
    └─ Template: "Professional Standard v1"
        ├─ Potensi: 45% weight
        └─ Kompetensi: 55% weight

Participant inherits template from their position:
Participant → Position → Template
```

### **Template Defines Standards at EACH Level:**

```
Template: "Supervisor Standard v1"
│
├─ Category Types (DYNAMIC WEIGHTS!):
│   ├─ Potensi: 30% weight (not always 40%!)
│   └─ Kompetensi: 70% weight (not always 60%!)
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

### **Key Concept (Updated 2025-10-17):**

Template structure is **DYNAMIC** and **PER POSITION** - different templates can have:
- Different number of aspects
- Different aspect weights
- Different standard ratings
- Different sub-aspects structure
- **Different category weights (Potensi/Kompetensi ratios)**

### **Examples:**

```
Template: "Staff Standard v1" (for entry-level positions)
├─ Potensi (50%) ← BALANCED
│   ├─ Kecerdasan (30%) ← 6 sub-aspects
│   ├─ Sikap Kerja (20%) ← 7 sub-aspects
│   ├─ Hubungan Sosial (20%) ← 4 sub-aspects
│   └─ Kepribadian (30%) ← 6 sub-aspects
└─ Kompetensi (50%) ← BALANCED
    └─ 9 aspects (no sub-aspects)

Template: "Supervisor Standard v1" (for supervisory positions)
├─ Potensi (30%) ← LOWER emphasis on potential
│   ├─ Kecerdasan (40%) ← DIFFERENT WEIGHT! Same aspects
│   ├─ Sikap Kerja (20%)
│   ├─ Hubungan Sosial (20%)
│   └─ Kepribadian (20%) ← DIFFERENT WEIGHT!
└─ Kompetensi (70%) ← HIGHER emphasis on competency
    └─ 9 aspects (same as Staff, different weights possible)

Template: "Professional Standard v1" (for specialized positions)
├─ Potensi (45%) ← MODERATE
│   └─ 4 aspects with different weights
└─ Kompetensi (55%) ← MODERATE
    └─ 9 aspects

Usage in Event:
Event: P3K Kejaksaan 2025
├─ Position: Auditor → uses "Supervisor Standard v1" (30/70)
├─ Position: Analis Kebijakan → uses "Staff Standard v1" (50/50)
└─ Position: Fisikawan Medis → uses "Professional Standard v1" (45/55)
```

### **Database Support:**

- ✅ `position_formations.template_id` (NEW 2025-10-17: positions link to templates)
- ✅ `category_types.template_id` (category weights per template)
- ✅ `aspects.template_id` (DUAL FK for multi-template support)
- ✅ `aspects.weight_percentage` (per template dapat berbeda)
- ✅ UNIQUE constraint: `(template_id, category_type_id, code)`

### **Key Architecture Change:**

```
BEFORE (v1.1):
Event → Template (1 template for entire event)

AFTER (v1.2):
Event → Positions → Templates (each position has its own template)
Participant → Position → Template (participant inherits from position)
```

This design allows:
- ✅ Same aspect code (e.g., "Kecerdasan") with different weights per template
- ✅ Different positions in same event with different assessment standards
- ✅ Template reusability across events and institutions

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

## 🧮 KEY CALCULATION FORMULAS - QUICK REFERENCE

### **LEVEL 1: Sub-Aspect Assessment**
```
individual_rating = INTEGER 1-5 (dari API CI3)
standard_rating = INTEGER 1-5 (snapshot dari master)
```

### **LEVEL 2: Aspect Assessment**

**Potensi (dengan sub-aspects):**
```
individual_rating = AVG(sub_aspect_ratings)
```

**Kompetensi (tanpa sub-aspects):**
```
individual_rating = INTEGER 1-5 (langsung dari API)
```

**Score Calculation (SEMUA ASPECT - POTENSI & KOMPETENSI):**
```
standard_score = standard_rating × weight_percentage
individual_score = individual_rating × weight_percentage

Contoh:
  Rating: 3.50, Weight: 30
  Score = 3.50 × 30 = 105.00 ✓

  BUKAN (3.50/5) × 100 × (30/100) = 21.00 ❌
```

**Gap & Percentage:**
```
gap_rating = individual_rating - standard_rating
gap_score = individual_score - standard_score
percentage_score = (individual_rating / 5) × 100  // untuk spider chart
```

### **LEVEL 3: Category Assessment**
```
total_standard_score = SUM(aspect_standard_scores)
total_individual_score = SUM(aspect_individual_scores)
gap_score = total_individual_score - total_standard_score
```

### **LEVEL 4: Final Assessment**
```
// Get weights from participant's position template (DYNAMIC!)
$potensiWeight = $participant->positionFormation->template
    ->categoryTypes->where('code', 'potensi')->weight_percentage;
$kompetensiWeight = $participant->positionFormation->template
    ->categoryTypes->where('code', 'kompetensi')->weight_percentage;

// Calculate weighted scores using DYNAMIC weights
total_standard_score =
    (potensi_standard_score × ($potensiWeight / 100)) +
    (kompetensi_standard_score × ($kompetensiWeight / 100))

total_individual_score =
    (potensi_individual_score × ($potensiWeight / 100)) +
    (kompetensi_individual_score × ($kompetensiWeight / 100))

achievement_percentage =
    (total_individual_score / total_standard_score) × 100

Examples:
  Staff Position: (score × 50%) + (score × 50%)  // Balanced
  Supervisor Position: (score × 30%) + (score × 70%)  // Competency-heavy
  Professional Position: (score × 45%) + (score × 55%)  // Moderate
```

**IMPORTANT NOTES:**
- ✅ Achievement percentage > 100% adalah **NORMAL** jika peserta exceed standard
- ✅ Score = rating × weight (BUKAN rating × weight% / 100)
- ✅ Percentage score untuk chart menggunakan rating (1-5), bukan score
- ✅ **Weights are DYNAMIC per position** (not hardcoded 40/60)

---

**Version:** 1.2
**Status:** ✅ Complete & Production-Ready
**Last Updated:** 2025-10-17

**Breaking Changes from v1.1:**
- ✅ Category weights (Potensi/Kompetensi) now DYNAMIC per position template
- ✅ Template accessed via `participant->positionFormation->template` (not `event->template`)
- ✅ Each position in an event can have different assessment standards
