# ASSESSMENT CALCULATION FLOW

Dokumentasi lengkap perhitungan asesmen dari raw data hingga final score.

---

## RELATED DOCUMENTATION

- [DATABASE_STRUCTURE.md](./DATABASE_STRUCTURE.md) - Database schema & relationships
- [API_SPECIFICATION.md](./API_SPECIFICATION.md) - API contract with CI3

---

## CALCULATION OVERVIEW

Assessment mengikuti pola **Bottom-Up Aggregation** dari level terkecil ke terbesar:

```
Level 1: Sub-Aspect Individual Ratings (Raw data dari CI3)
    ↓ AGGREGATE (Average)
Level 2: Aspect Individual Ratings (Calculated atau Direct)
    ↓ AGGREGATE (Sum with weights)
Level 3: Category Ratings (Potensi + Kompetensi)
    ↓ WEIGHTED CALCULATION
Level 4: Final Assessment (Achievement percentage + Conclusion)
```

**Key Principles:**
1. Sub-Aspects → Aspects: Aggregation (average) - **hanya untuk Potensi**
2. Aspects → Categories: Aggregation (sum with weights)
3. Categories → Final: Weighted calculation (dynamic weights per template)
4. Gap Calculation: Individual vs Standard di setiap level
5. Snapshot Pattern: Standard ratings dicopy untuk integritas historis

---

## LEVEL 1: SUB-ASPECT ASSESSMENT

**Table:** `sub_aspect_assessments`

### Data Structure

```sql
sub_aspect_id          FK → sub_aspects
standard_rating        integer (1-5)         -- Snapshot dari master
individual_rating      integer (1-5)         -- Nilai dari CI3
rating_label           varchar               -- 'Kurang', 'Cukup', 'Baik', 'Baik Sekali', 'Istimewa'
```

### Karakteristik

| Category | Sub-Aspects | Source |
|----------|-------------|--------|
| **Potensi** | ✅ Ada (20-25 total) | Wajib dari API |
| **Kompetensi** | ❌ Tidak ada | N/A |

### Purpose

- Raw assessment data dari sistem CI3
- Detail breakdown untuk laporan individual
- Gap comparison per sub-aspect
- Snapshot standard_rating untuk integritas historis

### Example Data

```
Aspect: Kecerdasan (Potensi)
├─ Kecerdasan Umum
│   ├─ Standard Rating: 3 (snapshot dari master)
│   ├─ Individual Rating: 4 (dari API)
│   └─ Rating Label: "Baik"
│
├─ Daya Tangkap
│   ├─ Standard Rating: 4
│   ├─ Individual Rating: 5
│   └─ Rating Label: "Baik Sekali"
│
└─ ... (4-6 sub-aspects per aspect)
```

---

## LEVEL 2: ASPECT ASSESSMENT

**Table:** `aspect_assessments`

### Data Structure

```sql
aspect_id              FK → aspects
standard_rating        decimal(5,2)          -- Snapshot atau aggregated
standard_score         decimal(8,2)          -- rating × weight_percentage
individual_rating      decimal(5,2)          -- AVG(sub) atau direct
individual_score       decimal(8,2)          -- rating × weight_percentage
gap_rating             decimal(8,2)          -- individual - standard
gap_score              decimal(8,2)          -- individual_score - standard_score
percentage_score       integer               -- (rating/5) × 100 untuk chart
conclusion_code        varchar               -- 'below_standard', 'meets_standard', 'exceeds_standard'
conclusion_text        varchar               -- 'Memenuhi Standard'
```

### Calculation Logic

#### POTENSI (dengan sub-aspects)

**Service:** `AspectService::calculatePotensiAspect()`

```php
// 1. Get all sub-aspect assessments
$subAssessments = SubAspectAssessment::where('aspect_assessment_id', $id)->get();

// 2. Calculate individual_rating = AVERAGE of sub-aspects (returns decimal)
$individualRating = $subAssessments->avg('individual_rating');

// 3. Get aspect weight from master
$aspect = Aspect::find($aspectId);

// 4. Calculate scores
// Formula: score = rating × weight_percentage
$standardScore = $standardRating × $aspect->weight_percentage;
$individualScore = $individualRating × $aspect->weight_percentage;

// 5. Calculate gaps
$gapRating = $individualRating - $standardRating;
$gapScore = $individualScore - $standardScore;

// 6. Calculate percentage for spider chart
$percentageScore = ($individualRating / 5) × 100;

// 7. Determine conclusion
if ($gapRating < -0.5) → 'below_standard'
if ($gapRating < 0.5) → 'meets_standard'
else → 'exceeds_standard'
```

**Example:**
```
Aspect: Kecerdasan (weight 25%)
├─ Sub-aspects average: (3+4+3+4+3+3)/6 = 3.33
├─ Standard Rating: 3.00 (dari master)
├─ Standard Score: 3.00 × 25 = 75.00
├─ Individual Rating: 3.33 (average dari sub-aspects)
├─ Individual Score: 3.33 × 25 = 83.25
├─ Gap Rating: 3.33 - 3.00 = +0.33
├─ Gap Score: 83.25 - 75.00 = +8.25
├─ Percentage: (3.33/5) × 100 = 67%
└─ Conclusion: 'meets_standard' (gap < 0.5)
```

#### KOMPETENSI (tanpa sub-aspects)

**Service:** `AspectService::calculateKompetensiAspect()`

```php
// IMPORTANT: individualRating dari API harus INTEGER 1-5

// 1. Get aspect weight from master
$aspect = Aspect::find($aspectId);

// 2. Calculate scores
// Formula: score = rating × weight_percentage
$standardScore = $standardRating × $aspect->weight_percentage;
$individualScore = $individualRating × $aspect->weight_percentage;

// 3. Calculate gaps
$gapRating = $individualRating - $standardRating;
$gapScore = $individualScore - $standardScore;

// 4. Calculate percentage
$percentageScore = ($individualRating / 5) × 100;

// 5. Determine conclusion (same logic as Potensi)
```

**Example:**
```
Aspect: Integritas (weight 15%)
├─ Standard Rating: 3.00 (dari master)
├─ Standard Score: 3.00 × 15 = 45.00
├─ Individual Rating: 4 (INTEGER dari API)
├─ Individual Score: 4 × 15 = 60.00
├─ Gap Rating: 4 - 3.00 = +1.00
├─ Gap Score: 60.00 - 45.00 = +15.00
├─ Percentage: (4/5) × 100 = 80%
└─ Conclusion: 'exceeds_standard' (gap >= 0.5)
```

### Standard Rating Calculation

**Potensi:** Standard rating di-calculate dari average sub-aspects saat create
```php
// AspectService::createAspectAssessment()
if ($categoryCode === 'potensi') {
    $standardRating = $aspect->subAspects->avg('standard_rating');
}
```

**Kompetensi:** Standard rating langsung dari master
```php
if ($categoryCode === 'kompetensi') {
    $standardRating = $aspect->standard_rating;
}
```

---

## LEVEL 3: CATEGORY ASSESSMENT

**Table:** `category_assessments`

### Data Structure

```sql
category_type_id           FK → category_types
total_standard_rating      decimal(8,2)      -- SUM of aspect standard_ratings
total_standard_score       decimal(8,2)      -- SUM of aspect standard_scores
total_individual_rating    decimal(8,2)      -- SUM of aspect individual_ratings
total_individual_score     decimal(8,2)      -- SUM of aspect individual_scores
gap_rating                 decimal(8,2)      -- total_individual - total_standard
gap_score                  decimal(8,2)      -- total_individual_score - total_standard_score
conclusion_code            varchar           -- 'DBS', 'MS', 'K', 'SK'
conclusion_text            varchar           -- 'MEMENUHI STANDARD'
```

### Calculation Logic

**Service:** `CategoryService::calculateCategory()`

```php
// 1. Get all aspect assessments for this category
$aspectAssessments = AspectAssessment::where('category_assessment_id', $id)->get();

// 2. Aggregate all aspects (SUM)
$totalStandardRating = $aspectAssessments->sum('standard_rating');
$totalStandardScore = $aspectAssessments->sum('standard_score');
$totalIndividualRating = $aspectAssessments->sum('individual_rating');
$totalIndividualScore = $aspectAssessments->sum('individual_score');

// 3. Calculate gaps
$gapRating = $totalIndividualRating - $totalStandardRating;
$gapScore = $totalIndividualScore - $totalStandardScore;

// 4. Determine conclusion
if ($gapScore < -10) → 'DBS' (Di Bawah Standard)
if ($gapScore < 0) → 'MS' (Memenuhi Standard)
if ($gapScore < 20) → 'K' (Kompeten)
else → 'SK' (Sangat Kompeten)
```

### Example Data

**POTENSI** (4 aspects):
```
├─ Kecerdasan (25%): Score 83.25, Standard 75.00 (+8.25)
├─ Cara Kerja (20%): Score 74.00, Standard 72.00 (+2.00)
├─ Potensi Kerja (20%): Score 62.00, Standard 60.00 (+2.00)
├─ Hubungan Sosial (20%): Score 77.00, Standard 75.00 (+2.00)
└─ Kepribadian (15%): Score 47.00, Standard 45.00 (+2.00)

Total Standard Score: 327.00
Total Individual Score: 343.25
Gap Score: +16.25
Conclusion: 'K' (Kompeten, gap 0-20)
```

**KOMPETENSI** (7 aspects):
```
├─ Integritas (15%): Score 60.00, Standard 45.00 (+15.00)
├─ Kerjasama (14%): Score 56.00, Standard 42.00 (+14.00)
├─ Komunikasi (14%): Score 42.00, Standard 42.00 (0.00)
└─ ... (4 more aspects)

Total Standard Score: 343.00
Total Individual Score: 371.00
Gap Score: +28.00
Conclusion: 'SK' (Sangat Kompeten, gap >= 20)
```

---

## LEVEL 4: FINAL ASSESSMENT

**Table:** `final_assessments`

### Data Structure

```sql
potensi_weight              integer           -- Dynamic dari template (30, 40, 45, 50)
potensi_standard_score      decimal(8,2)
potensi_individual_score    decimal(8,2)
kompetensi_weight           integer           -- Dynamic dari template (50, 55, 60, 70)
kompetensi_standard_score   decimal(8,2)
kompetensi_individual_score decimal(8,2)
total_standard_score        decimal(8,2)      -- Weighted sum
total_individual_score      decimal(8,2)      -- Weighted sum
achievement_percentage      decimal(5,2)      -- (individual/standard) × 100
conclusion_code             varchar           -- 'TMS', 'MMS', 'MS'
conclusion_text             varchar           -- 'MEMENUHI SYARAT (MS)'
```

### Calculation Formula

**Service:** `FinalAssessmentService::calculateFinal()`

```php
// 1. Get category types from participant's position template (DYNAMIC!)
$template = $participant->positionFormation->template;
$potensiCategory = CategoryType::where('template_id', $template->id)
    ->where('code', 'potensi')->first();
$kompetensiCategory = CategoryType::where('template_id', $template->id)
    ->where('code', 'kompetensi')->first();

// 2. Get category assessments
$potensiAssessment = CategoryAssessment::where('participant_id', $id)
    ->where('category_type_id', $potensiCategory->id)->first();
$kompetensiAssessment = CategoryAssessment::where('participant_id', $id)
    ->where('category_type_id', $kompetensiCategory->id)->first();

// 3. Get weights from template (DYNAMIC per position!)
$potensiWeight = $potensiCategory->weight_percentage; // 30, 40, 45, 50
$kompetensiWeight = $kompetensiCategory->weight_percentage; // 50, 55, 60, 70

// 4. Calculate weighted scores
$totalStandardScore =
    ($potensiAssessment->total_standard_score × ($potensiWeight / 100)) +
    ($kompetensiAssessment->total_standard_score × ($kompetensiWeight / 100));

$totalIndividualScore =
    ($potensiAssessment->total_individual_score × ($potensiWeight / 100)) +
    ($kompetensiAssessment->total_individual_score × ($kompetensiWeight / 100));

// 5. Calculate achievement percentage
$achievementPercentage = ($totalIndividualScore / $totalStandardScore) × 100;

// 6. Determine conclusion
if ($achievementPercentage < 80) → 'TMS' (Tidak Memenuhi Syarat)
if ($achievementPercentage < 90) → 'MMS' (Masih Memenuhi Syarat)
else → 'MS' (Memenuhi Syarat)
```

### Example Data

**Example 1: Staff Position (Balanced 50-50)**

```
Participant: ANDI WIJAYA
Position: Analis Kebijakan
Template: Staff Standard v1

POTENSI (50%):
├─ Standard Score: 327.00
├─ Individual Score: 343.25
└─ Weighted: 343.25 × 50% = 171.63

KOMPETENSI (50%):
├─ Standard Score: 343.00
├─ Individual Score: 371.00
└─ Weighted: 371.00 × 50% = 185.50

FINAL:
├─ Total Standard: (327 × 0.50) + (343 × 0.50) = 335.00
├─ Total Individual: 171.63 + 185.50 = 357.13
├─ Achievement: (357.13 / 335.00) × 100 = 106.55%
└─ Conclusion: 'MS' (>= 90%)
```

**Example 2: Supervisor Position (Competency-Heavy 30-70)**

```
Participant: BUDI SANTOSO
Position: Auditor
Template: Supervisor Standard v1

POTENSI (30%):
├─ Standard Score: 327.00
├─ Individual Score: 343.25
└─ Weighted: 343.25 × 30% = 102.98

KOMPETENSI (70%):
├─ Standard Score: 343.00
├─ Individual Score: 371.00
└─ Weighted: 371.00 × 70% = 259.70

FINAL:
├─ Total Standard: (327 × 0.30) + (343 × 0.70) = 338.20
├─ Total Individual: 102.98 + 259.70 = 362.68
├─ Achievement: (362.68 / 338.20) × 100 = 107.23%
└─ Conclusion: 'MS' (>= 90%)
```

**Note:** Achievement > 100% adalah normal dan menunjukkan peserta exceed standard.

---

## SNAPSHOT PATTERN

### Purpose

Menjaga integritas data historis saat standard berubah di masa depan.

### How It Works

```
Timeline:

Jan 2025: Template created
├─ aspects.standard_rating = 3.20 (master)
│
├─ Participant A assessed (2025-01-15)
│   ├─ aspect_assessments.standard_rating = 3.20 (SNAPSHOT)
│   └─ Gap calculated with 3.20
│
├─ Mar 2025: Standard updated
│   └─ aspects.standard_rating = 3.50 (master updated)
│
├─ Participant B assessed (2025-03-20)
│   ├─ aspect_assessments.standard_rating = 3.50 (NEW SNAPSHOT)
│   └─ Gap calculated with 3.50
│
└─ Result:
    ├─ Participant A gap tetap 3.20 ✓
    └─ Participant B gap menggunakan 3.50 ✓
```

### Benefits

1. Historical data integrity - hasil tidak berubah retroaktif
2. Accurate gap comparison - peserta dibandingkan dengan standard saat itu
3. Template evolution - standard bisa diupdate tanpa merusak data lama
4. Audit trail - history perubahan standard terdokumentasi
5. Performance - tidak perlu recalculate data lama

---

## DYNAMIC TEMPLATE STRUCTURE

### Concept

Setiap position menggunakan template tertentu. Template berbeda memiliki:
- Bobot kategori berbeda (Potensi vs Kompetensi)
- Bobot aspek berbeda
- Standard rating berbeda
- Struktur sub-aspek berbeda

### Template Examples

```
Staff Standard v1 (Entry Level):
├─ Potensi: 50% (balanced)
└─ Kompetensi: 50% (balanced)

Supervisor Standard v1 (Leadership):
├─ Potensi: 30% (lower emphasis)
└─ Kompetensi: 70% (higher emphasis)

Manager Standard v1 (Strategic):
├─ Potensi: 40%
└─ Kompetensi: 60%

Professional Standard v1 (Technical):
├─ Potensi: 45%
└─ Kompetensi: 55%
```

### Usage in Event

```
Event: P3K Kejaksaan 2025
│
├─ Position: Auditor
│   └─ Template: Supervisor Standard v1 (30/70)
│
├─ Position: Analis Kebijakan
│   └─ Template: Staff Standard v1 (50/50)
│
└─ Position: Fisikawan Medis
    └─ Template: Professional Standard v1 (45/55)

Each participant inherits template from their position:
Participant → Position → Template
```

---

## API DATA REQUIREMENTS

### Wajib dari API

| Data | Type | Category | Reason |
|------|------|----------|--------|
| Template structure | Object | Both | Dynamic template & snapshot |
| Aspects + standard_rating | Decimal | Both | Gap comparison |
| Aspects + weight_percentage | Integer | Both | Score calculation |
| Sub-aspects + standard_rating | Integer (1-5) | Potensi | Detail report |
| Sub-aspects + individual_rating | Integer (1-5) | Potensi | Raw assessment |
| Aspects individual_rating | Integer (1-5) | Kompetensi | Direct assessment |

### Example API Response

```json
{
  "template": {
    "code": "staff_standard_v1",
    "category_types": [
      {
        "code": "potensi",
        "weight_percentage": 50
      },
      {
        "code": "kompetensi",
        "weight_percentage": 50
      }
    ],
    "aspects": [
      {
        "code": "kecerdasan",
        "category_code": "potensi",
        "standard_rating": 3.00,
        "weight_percentage": 25,
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
        "category_code": "kompetensi",
        "standard_rating": 3.00,
        "weight_percentage": 15,
        "sub_aspects": []
      }
    ]
  },
  "participant": {
    "test_number": "03-5-2-18-001",
    "name": "ANDI WIJAYA",
    "assessments": {
      "potensi": [
        {
          "aspect_code": "kecerdasan",
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
          "aspect_code": "integritas",
          "individual_rating": 4
        }
      ]
    }
  }
}
```

---

## KEY FORMULAS - QUICK REFERENCE

### Level 1: Sub-Aspect
```
individual_rating = INTEGER 1-5 (dari API)
standard_rating = INTEGER 1-5 (snapshot dari master)
```

### Level 2: Aspect

**Potensi (dengan sub-aspects):**
```
individual_rating = AVG(sub_aspect_ratings)  // Hasil: decimal
```

**Kompetensi (tanpa sub-aspects):**
```
individual_rating = INTEGER 1-5 (langsung dari API)
```

**Score Calculation (SEMUA ASPECT):**
```
standard_score = standard_rating × weight_percentage
individual_score = individual_rating × weight_percentage

Example:
  Rating: 3.50, Weight: 30
  Score = 3.50 × 30 = 105.00 ✓

  BUKAN: (3.50/5) × 100 × (30/100) = 21.00 ✗
```

**Gap & Percentage:**
```
gap_rating = individual_rating - standard_rating
gap_score = individual_score - standard_score
percentage_score = (individual_rating / 5) × 100  // untuk spider chart
```

### Level 3: Category
```
total_standard_score = SUM(aspect_standard_scores)
total_individual_score = SUM(aspect_individual_scores)
gap_score = total_individual_score - total_standard_score
```

### Level 4: Final
```
// Get weights from position's template (DYNAMIC!)
$potensiWeight = $position->template->categoryTypes->where('code', 'potensi')->weight_percentage;
$kompetensiWeight = $position->template->categoryTypes->where('code', 'kompetensi')->weight_percentage;

// Calculate weighted scores
total_standard_score =
    (potensi_standard_score × (potensiWeight / 100)) +
    (kompetensi_standard_score × (kompetensiWeight / 100))

total_individual_score =
    (potensi_individual_score × (potensiWeight / 100)) +
    (kompetensi_individual_score × (kompetensiWeight / 100))

achievement_percentage = (total_individual_score / total_standard_score) × 100
```

**Important Notes:**
- Achievement > 100% adalah normal (exceed standard)
- Score = rating × weight (bukan rating × weight% / 100)
- Percentage untuk chart menggunakan rating (1-5), bukan score
- Weights dynamic per position template (bukan hardcoded 40/60)
