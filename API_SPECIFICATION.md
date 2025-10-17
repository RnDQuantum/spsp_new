# 📡 API SPECIFICATION - SPSP Assessment Data Sync

**Project:** SPSP Analytics Dashboard
**Purpose:** API contract between CI3 Application (source) and Laravel Dashboard (analytics)
**Version:** 1.2
**Last Updated:** 2025-10-17

**Breaking Changes in v1.2:**
- ✅ Templates now assigned per position, not per event
- ✅ Each position must include `template_code` field
- ✅ Event no longer requires template selection

---

## 📚 RELATED DOCUMENTATION

- 👉 **[DATABASE_AND_ASSESSMENT_LOGIC.md](./DATABASE_AND_ASSESSMENT_LOGIC.md)** - Database structure & assessment overview
- 👉 **[ASSESSMENT_CALCULATION_FLOW.md](./ASSESSMENT_CALCULATION_FLOW.md)** - Calculation logic & formulas

---

## 🎯 OVERVIEW

### **Purpose**

API ini digunakan untuk **sync data assessment** dari aplikasi CI3 (source of truth) ke Laravel Dashboard (analytics).

### **Key Principles**

1. ✅ **Minimal Data Transfer:** API hanya mengirim data DASAR (raw ratings)
2. ✅ **Laravel Calculates:** Semua derived values (scores, gaps, percentages) dihitung oleh Laravel
3. ✅ **Fully Dynamic:** Support multiple templates dengan struktur berbeda
4. ✅ **Snapshot Pattern:** Standard ratings dikirim untuk historical integrity
5. ✅ **Upsert-Friendly:** Menggunakan unique codes untuk upsert (tidak duplikat)

### **What CI3 API MUST Send**

| Data Element | Required | Notes |
|--------------|----------|-------|
| **Master Data** (Template structure) | ✅ YES | Template, categories, aspects, sub-aspects dengan weights & standards |
| **Event Data** | ✅ YES | Event, batches, position formations |
| **Participant Data** | ✅ YES | Basic info (test_number, name, etc.) |
| **RAW Individual Ratings** | ✅ YES | Sub-aspect ratings (Potensi) + Aspect ratings (Kompetensi) |
| **Psychological Test Results** | ✅ YES | For individual report |
| **Interpretations Text** | ✅ YES | Narrative for reports |

### **What Laravel Will Calculate (NO NEED TO SEND)**

| Data Element | Data Type | Calculated By Laravel |
|--------------|-----------|----------------------|
| Aspect `individual_rating` (Potensi) | DECIMAL | ✅ AVG from sub-aspects `individual_rating` (INTEGER) |
| All scores (`standard_score`, `individual_score`) | DECIMAL | ✅ `rating × weight_percentage` |
| All gaps (`gap_rating`, `gap_score`) | DECIMAL | ✅ individual - standard |
| Percentage scores (spider chart) | INTEGER | ✅ (rating / 5) × 100 |
| Category totals | DECIMAL | ✅ SUM from aspects |
| Final assessment | DECIMAL | ✅ Weighted calculation |
| All conclusion codes & texts | STRING | ✅ Based on thresholds |

### **Visual Flow: What API Sends vs What Laravel Calculates**

```
┌─────────────────────────────────────────────────────────────┐
│ CI3 API SENDS (RAW DATA)                                    │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ POTENSI:                                                    │
│   Aspect: Kecerdasan                                        │
│   ├─ ❌ NO individual_rating at aspect level               │
│   └─ Sub-aspects:                                           │
│       ├─ Kecerdasan Umum: individual_rating = 3 (INTEGER)  │
│       ├─ Daya Tangkap: individual_rating = 4 (INTEGER)     │
│       └─ ... (6 sub-aspects)                                │
│                                                             │
│ KOMPETENSI:                                                 │
│   Aspect: Integritas                                        │
│   ├─ ✅ individual_rating = 3 (INTEGER)                    │
│   └─ ❌ NO sub-aspects                                     │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ LARAVEL CALCULATES                                          │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ POTENSI:                                                    │
│   Aspect: Kecerdasan (weight 30)                           │
│   ✅ individual_rating = 3.50 (DECIMAL)                    │
│      ↑ Calculated as AVG(3, 4, 3, 4, 3, 4) = 3.50         │
│   ✅ standard_score = 3.50 × 30 = 105.00                   │
│   ✅ individual_score = 3.50 × 30 = 105.00                 │
│   ✅ gap_rating = 3.50 - 3.50 = 0.00                       │
│   ✅ percentage_score = (3.50 / 5) × 100 = 70%             │
│                                                             │
│ KOMPETENSI:                                                 │
│   Aspect: Integritas (weight 12)                           │
│   ✅ individual_rating = 4 (from API, stored as-is)        │
│   ✅ standard_score = 3.50 × 12 = 42.00                    │
│   ✅ individual_score = 4 × 12 = 48.00                     │
│   ✅ gap_rating = 4 - 3.50 = +0.50                         │
│   ✅ percentage_score = (4 / 5) × 100 = 80%                │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔐 AUTHENTICATION

### **API Key Authentication**

```http
POST /api/sync-assessment
Authorization: Bearer {institution_api_key}
Content-Type: application/json
```

**institution_api_key:** Stored in `institutions.api_key` table

---

## 📊 ENDPOINT: Sync Assessment Data

### **Endpoint**

```
POST /api/sync-assessment
```

### **Headers**

```http
Authorization: Bearer {institution_api_key}
Content-Type: application/json
```

### **Request Body Structure**

```json
{
  "institution": {
    "code": "string",
    "name": "string",
    "logo_path": "string|null"
  },
  "templates": [
    {
      "code": "string",
      "name": "string",
      "description": "string|null",
      "category_types": [
        {
          "code": "string",
          "name": "string",
          "weight_percentage": "integer",
          "order": "integer",
          "aspects": [
            {
              "code": "string",
              "name": "string",
              "weight_percentage": "integer",
              "standard_rating": "decimal",
              "order": "integer",
              "sub_aspects": [
                {
                  "code": "string",
                  "name": "string",
                  "standard_rating": "integer",
                  "description": "string|null",
                  "order": "integer"
                }
              ]
            }
          ]
        }
      ]
    }
  ],
  "event": {
    "code": "string",
    "name": "string",
    "description": "string|null",
    "year": "integer",
    "start_date": "date (YYYY-MM-DD)",
    "end_date": "date (YYYY-MM-DD)",
    "status": "enum: draft|ongoing|completed"
  },
  "batches": [
    {
      "code": "string",
      "name": "string",
      "location": "string",
      "batch_number": "integer",
      "start_date": "date (YYYY-MM-DD)",
      "end_date": "date (YYYY-MM-DD)"
    }
  ],
  "position_formations": [
    {
      "code": "string",
      "name": "string",
      "quota": "integer|null",
      "template_code": "string"
    }
  ],
  "participants": [
    {
      "test_number": "string (UNIQUE)",
      "batch_code": "string",
      "position_formation_code": "string",
      "skb_number": "string",
      "name": "string",
      "email": "string|null",
      "phone": "string|null",
      "photo_path": "string|null",
      "assessment_date": "date (YYYY-MM-DD)",
      "assessments": {
        "potensi": [
          {
            "aspect_code": "string",
            "sub_aspects": [
              {
                "sub_aspect_code": "string",
                "individual_rating": "integer (1-5)"
              }
            ]
          }
        ],
        "kompetensi": [
          {
            "aspect_code": "string",
            "individual_rating": "integer (1-5)"
          }
        ]
      },
      "psychological_test": {
        "raw_score": "decimal",
        "iq_score": "integer|null",
        "validity_status": "string",
        "internal_status": "string",
        "interpersonal_status": "string",
        "work_capacity_status": "string",
        "clinical_status": "string",
        "conclusion_code": "string",
        "conclusion_text": "string",
        "notes": "string|null"
      },
      "interpretations": [
        {
          "category_type_code": "string|null",
          "interpretation_text": "text"
        }
      ]
    }
  ]
}
```

---

## 📋 DETAILED FIELD SPECIFICATIONS

### **1. Institution Object**

```json
{
  "code": "kejaksaan",          // UNIQUE identifier (lowercase, no spaces)
  "name": "Kejaksaan Republik Indonesia",
  "logo_path": "/uploads/logos/kejaksaan.png"  // Optional, nullable
}
```

**Validation Rules:**
- `code`: required, string, unique, max:50, lowercase, no spaces
- `name`: required, string, max:255
- `logo_path`: nullable, string, max:500

---

### **2. Templates Array**

**BREAKING CHANGE v1.2:** Changed from single `template` object to `templates` array since multiple templates may be used in one event!

```json
[
  {
    "code": "supervisor_standard_v1",  // UNIQUE identifier
    "name": "Standar Asesmen Supervisor",
    "description": "Template untuk posisi supervisor dengan fokus kompetensi",
    "category_types": [...]  // Array of category types
  },
  {
    "code": "staff_standard_v1",
    "name": "Standar Asesmen Staff",
    "description": "Template untuk posisi staff dengan bobot seimbang",
    "category_types": [...]
  }
]
```

**Validation Rules:**
- `templates`: required, array, min:1
- Each template:
  - `code`: required, string, unique, max:100
  - `name`: required, string, max:255
  - `description`: nullable, text
  - `category_types`: required, array, min:1

**Note:** Send all templates that will be used by positions in this event

---

### **3. Category Types Array**

**CRITICAL:** Weights MUST be dynamic (not always 40/60)!

```json
{
  "code": "potensi",              // UNIQUE per template
  "name": "POTENSI",
  "weight_percentage": 40,        // ⚠️ DYNAMIC! Could be 30, 40, 50, etc.
  "order": 1,
  "aspects": [...]                // Array of aspects
}
```

**Example - Different Template:**
```json
[
  {
    "code": "potensi",
    "weight_percentage": 30      // ← Different weight!
  },
  {
    "code": "kompetensi",
    "weight_percentage": 70      // ← Different weight!
  }
]
```

**Validation Rules:**
- `code`: required, string, max:50
- `name`: required, string, max:255
- `weight_percentage`: required, integer, min:0, max:100
- `order`: required, integer
- SUM of all `weight_percentage` in category_types MUST = 100

---

### **4. Aspects Array**

**CRITICAL:** Aspects structure is DYNAMIC per template!

```json
{
  "code": "kecerdasan",           // UNIQUE per template + category
  "name": "KECERDASAN",
  "weight_percentage": 30,        // ⚠️ DYNAMIC! Different per template
  "standard_rating": 3.20,        // ⚠️ For snapshot pattern
  "order": 1,
  "sub_aspects": [...]            // Array (can be empty for Kompetensi)
}
```

**Potensi vs Kompetensi:**

```json
// POTENSI - HAS sub_aspects
{
  "code": "kecerdasan",
  "weight_percentage": 30,
  "standard_rating": 3.20,
  "sub_aspects": [                // ✅ NOT EMPTY
    {
      "code": "kecerdasan_umum",
      "standard_rating": 3
    }
  ]
}

// KOMPETENSI - NO sub_aspects
{
  "code": "integritas",
  "weight_percentage": 12,
  "standard_rating": 3.50,
  "sub_aspects": []               // ✅ EMPTY ARRAY
}
```

**Validation Rules:**
- `code`: required, string, max:100
- `name`: required, string, max:255
- `weight_percentage`: required, integer, min:0, max:100
- `standard_rating`: required, decimal (5,2), min:0, max:5
- `order`: required, integer
- `sub_aspects`: required, array (can be empty for Kompetensi)
- SUM of `weight_percentage` per category MUST = 100

---

### **5. Sub-Aspects Array**

**Only for Potensi aspects!**

```json
{
  "code": "kecerdasan_umum",      // UNIQUE per aspect
  "name": "Kecerdasan Umum",
  "standard_rating": 3,           // ⚠️ INTEGER 1-5 (for snapshot)
  "description": "Kemampuan berpikir logis",  // Optional
  "order": 1
}
```

**Validation Rules:**
- `code`: required, string, max:100
- `name`: required, string, max:255
- `standard_rating`: required, integer, min:1, max:5
- `description`: nullable, text
- `order`: required, integer

---

### **6. Event Object**

```json
{
  "code": "P3K-KEJAKSAAN-2025",   // UNIQUE identifier
  "name": "Seleksi P3K Kejaksaan 2025",
  "description": "Seleksi PPPK untuk Kejaksaan tahun 2025",
  "year": 2025,
  "start_date": "2025-01-15",     // ISO 8601 format
  "end_date": "2025-03-30",
  "status": "ongoing"             // draft, ongoing, completed
}
```

**Validation Rules:**
- `code`: required, string, unique, max:100
- `name`: required, string, max:255
- `description`: nullable, text
- `year`: required, integer, min:2020, max:2100
- `start_date`: required, date (YYYY-MM-DD)
- `end_date`: required, date (YYYY-MM-DD), after:start_date
- `status`: required, enum (draft, ongoing, completed)

---

### **7. Batches Array**

```json
{
  "code": "BATCH-1-MOJOKERTO",    // UNIQUE per event
  "name": "Batch 1 - Mojokerto",
  "location": "Mojokerto, Jawa Timur",
  "batch_number": 1,
  "start_date": "2025-01-15",
  "end_date": "2025-01-17"
}
```

**Validation Rules:**
- `code`: required, string, max:100, unique per event
- `name`: required, string, max:255
- `location`: required, string, max:255
- `batch_number`: required, integer, min:1
- `start_date`: required, date
- `end_date`: required, date, after_or_equal:start_date

---

### **8. Position Formations Array**

**BREAKING CHANGE v1.2:** Added `template_code` field - REQUIRED!

```json
{
  "code": "fisikawan_medis",      // UNIQUE per event
  "name": "Fisikawan Medis",
  "quota": 5,                     // Optional, nullable
  "template_code": "professional_standard_v1"  // REQUIRED - links to template
}
```

**Architecture:**
- Each position MUST specify which template to use
- Different positions in same event can use different templates
- Template code must exist in the `templates` array

**Example - Multiple Positions with Different Templates:**
```json
[
  {
    "code": "auditor",
    "name": "Auditor",
    "quota": 25,
    "template_code": "supervisor_standard_v1"  // Potensi 30%, Kompetensi 70%
  },
  {
    "code": "analis_kebijakan",
    "name": "Analis Kebijakan",
    "quota": 30,
    "template_code": "staff_standard_v1"  // Potensi 50%, Kompetensi 50%
  }
]
```

**Validation Rules:**
- `code`: required, string, max:100, unique per event
- `name`: required, string, max:255
- `quota`: nullable, integer, min:0
- `template_code`: **REQUIRED**, string, must exist in templates array

---

### **9. Participants Array**

```json
{
  "test_number": "03-5-2-18-001",     // UNIQUE globally
  "batch_code": "BATCH-1-MOJOKERTO",  // FK reference
  "position_formation_code": "fisikawan_medis",  // FK reference
  "skb_number": "123456789",
  "name": "EKA FEBRIYANI, S.Si",
  "email": "eka.febriyani@example.com",  // Optional
  "phone": "08123456789",                // Optional
  "photo_path": "/uploads/photos/eka.jpg",  // Optional
  "assessment_date": "2025-01-15",
  "assessments": {...},           // Assessment data (see below)
  "psychological_test": {...},    // Psych test (see below)
  "interpretations": [...]        // Interpretations (see below)
}
```

**Validation Rules:**
- `test_number`: required, string, unique globally, max:50
- `batch_code`: required, string, exists in batches array
- `position_formation_code`: required, string, exists in position_formations array
- `skb_number`: required, string, max:50
- `name`: required, string, max:255
- `email`: nullable, email, max:255
- `phone`: nullable, string, max:20
- `photo_path`: nullable, string, max:500
- `assessment_date`: required, date

---

### **10. Assessments Object (RAW RATINGS ONLY)**

**CRITICAL:** Only send RAW individual_rating values!

#### **📌 IMPORTANT CLARIFICATION: `individual_rating` Field**

The term `individual_rating` appears in BOTH sub-aspects and aspects, but they have **DIFFERENT data types**:

| Location | Field Name | Data Type | Source | Stored In Table |
|----------|-----------|-----------|--------|-----------------|
| **Sub-Aspect (Potensi)** | `individual_rating` | **INTEGER 1-5** | ✅ **FROM API** (must send) | `sub_aspect_assessments.individual_rating` |
| **Aspect (Potensi)** | `individual_rating` | **DECIMAL** | ❌ **CALCULATED by Laravel** (AVG from sub-aspects) | `aspect_assessments.individual_rating` |
| **Aspect (Kompetensi)** | `individual_rating` | **INTEGER 1-5** | ✅ **FROM API** (must send) | `aspect_assessments.individual_rating` |

**Summary:**
- **API sends:** Sub-aspect ratings (INTEGER) + Kompetensi aspect ratings (INTEGER)
- **Laravel calculates:** Potensi aspect ratings (DECIMAL) from sub-aspects average

```json
{
  "potensi": [
    {
      "aspect_code": "kecerdasan",      // Must exist in template
      // ❌ NO "individual_rating" here - will be calculated by Laravel!
      "sub_aspects": [                  // ✅ MUST NOT BE EMPTY for Potensi
        {
          "sub_aspect_code": "kecerdasan_umum",  // Must exist
          "individual_rating": 3        // ✅ INTEGER 1-5 (RAW from test) - SEND THIS
        },
        {
          "sub_aspect_code": "daya_tangkap",
          "individual_rating": 4        // ✅ INTEGER 1-5 - SEND THIS
        }
        // ... all sub-aspects for this aspect
      ]
    },
    {
      "aspect_code": "sikap_kerja",
      // ❌ NO "individual_rating" here - will be calculated!
      "sub_aspects": [
        // ... sub-aspects with individual_rating (INTEGER)
      ]
    }
    // ... all Potensi aspects
  ],
  "kompetensi": [
    {
      "aspect_code": "integritas",      // Must exist in template
      "individual_rating": 3            // ✅ INTEGER 1-5 (RAW, NO sub-aspects) - SEND THIS
    },
    {
      "aspect_code": "kerjasama",
      "individual_rating": 4            // ✅ INTEGER 1-5 - SEND THIS
    }
    // ... all Kompetensi aspects
  ]
}
```

**Validation Rules:**

**Potensi:**
- `aspect_code`: required, string, must exist in template
- `sub_aspects`: required, array, min:1 (MUST NOT BE EMPTY)
  - `sub_aspect_code`: required, string, must exist in template
  - `individual_rating`: required, **INTEGER**, min:1, max:5 ← **FROM API**
- ❌ **DO NOT send** `individual_rating` at aspect level (will be calculated by Laravel as DECIMAL)

**Kompetensi:**
- `aspect_code`: required, string, must exist in template
- `individual_rating`: required, **INTEGER**, min:1, max:5 ← **FROM API**
- ❌ **NO `sub_aspects` field** (Kompetensi has no sub-aspects)

**Important Notes:**
1. ✅ **API only sends:** sub-aspect `individual_rating` (Potensi) + aspect `individual_rating` (Kompetensi)
2. ✅ **Laravel calculates:** aspect `individual_rating` for Potensi (as DECIMAL average)
3. ✅ **Potensi structure:** aspect → sub_aspects (with ratings) → Laravel calculates aspect rating
4. ✅ **Kompetensi structure:** aspect (with rating) → no sub-aspects
5. ✅ **All individual_rating from API MUST be INTEGER 1-5** (not decimal, not float)
6. ✅ **All aspects from template MUST be present** in assessments

---

### **11. Psychological Test Object**

```json
{
  "raw_score": 85.50,
  "iq_score": 120,                // Optional
  "validity_status": "Valid",
  "internal_status": "Stabil",
  "interpersonal_status": "Baik",
  "work_capacity_status": "Tinggi",
  "clinical_status": "Normal",
  "conclusion_code": "MS",        // MS, TMS
  "conclusion_text": "Memenuhi Syarat",
  "notes": "Tidak ada catatan khusus"  // Optional
}
```

**Validation Rules:**
- `raw_score`: required, decimal (5,2), min:0
- `iq_score`: nullable, integer, min:0
- `validity_status`: required, string, max:100
- `internal_status`: required, string, max:100
- `interpersonal_status`: required, string, max:100
- `work_capacity_status`: required, string, max:100
- `clinical_status`: required, string, max:100
- `conclusion_code`: required, string, max:50
- `conclusion_text`: required, string, max:255
- `notes`: nullable, text

---

### **12. Interpretations Array**

```json
[
  {
    "category_type_code": "potensi",  // Optional, can be null for general
    "interpretation_text": "Peserta menunjukkan kemampuan kognitif yang baik..."
  },
  {
    "category_type_code": "kompetensi",
    "interpretation_text": "Peserta memiliki integritas dan kerjasama yang tinggi..."
  }
]
```

**Validation Rules:**
- `category_type_code`: nullable, string, must exist in template if provided
- `interpretation_text`: required, text

---

## 📝 COMPLETE EXAMPLE REQUEST

<details>
<summary><strong>Click to expand full example</strong></summary>

```json
{
  "institution": {
    "code": "kejaksaan",
    "name": "Kejaksaan Republik Indonesia",
    "logo_path": "/uploads/logos/kejaksaan.png"
  },
  "templates": [
    {
      "code": "p3k_standard_2025",
      "name": "P3K Standard 2025",
      "description": "Template standar untuk P3K tahun 2025",
      "category_types": [
      {
        "code": "potensi",
        "name": "POTENSI",
        "weight_percentage": 40,
        "order": 1,
        "aspects": [
          {
            "code": "kecerdasan",
            "name": "KECERDASAN",
            "weight_percentage": 30,
            "standard_rating": 3.20,
            "order": 1,
            "sub_aspects": [
              {
                "code": "kecerdasan_umum",
                "name": "Kecerdasan Umum",
                "standard_rating": 3,
                "description": "Kemampuan berpikir logis dan analitis",
                "order": 1
              },
              {
                "code": "daya_tangkap",
                "name": "Daya Tangkap",
                "standard_rating": 4,
                "description": "Kemampuan memahami informasi dengan cepat",
                "order": 2
              },
              {
                "code": "ketelitian",
                "name": "Ketelitian",
                "standard_rating": 3,
                "description": "Kemampuan bekerja dengan detail dan akurat",
                "order": 3
              },
              {
                "code": "daya_nalar",
                "name": "Daya Nalar",
                "standard_rating": 3,
                "description": "Kemampuan berpikir logis",
                "order": 4
              },
              {
                "code": "kecepatan_berpikir",
                "name": "Kecepatan Berpikir",
                "standard_rating": 3,
                "description": "Kemampuan berpikir cepat",
                "order": 5
              },
              {
                "code": "fleksibilitas_berpikir",
                "name": "Fleksibilitas Berpikir",
                "standard_rating": 4,
                "description": "Kemampuan berpikir fleksibel",
                "order": 6
              }
            ]
          },
          {
            "code": "sikap_kerja",
            "name": "SIKAP KERJA",
            "weight_percentage": 20,
            "standard_rating": 3.50,
            "order": 2,
            "sub_aspects": [
              {
                "code": "ketekunan",
                "name": "Ketekunan",
                "standard_rating": 3,
                "order": 1
              },
              {
                "code": "keuletan",
                "name": "Keuletan",
                "standard_rating": 4,
                "order": 2
              },
              {
                "code": "daya_tahan",
                "name": "Daya Tahan",
                "standard_rating": 3,
                "order": 3
              },
              {
                "code": "motivasi_berprestasi",
                "name": "Motivasi Berprestasi",
                "standard_rating": 4,
                "order": 4
              },
              {
                "code": "sikap_terhadap_atasan",
                "name": "Sikap Terhadap Atasan",
                "standard_rating": 4,
                "order": 5
              },
              {
                "code": "sikap_terhadap_pekerjaan",
                "name": "Sikap Terhadap Pekerjaan",
                "standard_rating": 4,
                "order": 6
              },
              {
                "code": "inisiatif",
                "name": "Inisiatif",
                "standard_rating": 3,
                "order": 7
              }
            ]
          },
          {
            "code": "hubungan_sosial",
            "name": "HUBUNGAN SOSIAL",
            "weight_percentage": 20,
            "standard_rating": 3.75,
            "order": 3,
            "sub_aspects": [
              {
                "code": "kemampuan_beradaptasi",
                "name": "Kemampuan Beradaptasi",
                "standard_rating": 4,
                "order": 1
              },
              {
                "code": "kemampuan_bekerja_sama",
                "name": "Kemampuan Bekerja Sama",
                "standard_rating": 4,
                "order": 2
              },
              {
                "code": "kepemimpinan",
                "name": "Kepemimpinan",
                "standard_rating": 3,
                "order": 3
              },
              {
                "code": "kemampuan_komunikasi",
                "name": "Kemampuan Komunikasi",
                "standard_rating": 4,
                "order": 4
              }
            ]
          },
          {
            "code": "kepribadian",
            "name": "KEPRIBADIAN",
            "weight_percentage": 30,
            "standard_rating": 3.17,
            "order": 4,
            "sub_aspects": [
              {
                "code": "stabilitas_emosi",
                "name": "Stabilitas Emosi",
                "standard_rating": 3,
                "order": 1
              },
              {
                "code": "kontrol_diri",
                "name": "Kontrol Diri",
                "standard_rating": 3,
                "order": 2
              },
              {
                "code": "kepercayaan_diri",
                "name": "Kepercayaan Diri",
                "standard_rating": 3,
                "order": 3
              },
              {
                "code": "kesadaran_diri",
                "name": "Kesadaran Diri",
                "standard_rating": 3,
                "order": 4
              },
              {
                "code": "tanggung_jawab",
                "name": "Tanggung Jawab",
                "standard_rating": 4,
                "order": 5
              },
              {
                "code": "kejujuran",
                "name": "Kejujuran",
                "standard_rating": 3,
                "order": 6
              }
            ]
          }
        ]
      },
      {
        "code": "kompetensi",
        "name": "KOMPETENSI",
        "weight_percentage": 60,
        "order": 2,
        "aspects": [
          {
            "code": "integritas",
            "name": "INTEGRITAS",
            "weight_percentage": 12,
            "standard_rating": 3.50,
            "order": 1,
            "sub_aspects": []
          },
          {
            "code": "kerjasama",
            "name": "KERJASAMA",
            "weight_percentage": 11,
            "standard_rating": 3.00,
            "order": 2,
            "sub_aspects": []
          },
          {
            "code": "komunikasi",
            "name": "KOMUNIKASI",
            "weight_percentage": 10,
            "standard_rating": 3.00,
            "order": 3,
            "sub_aspects": []
          },
          {
            "code": "orientasi_pada_hasil",
            "name": "ORIENTASI PADA HASIL",
            "weight_percentage": 11,
            "standard_rating": 3.50,
            "order": 4,
            "sub_aspects": []
          },
          {
            "code": "pelayanan_publik",
            "name": "PELAYANAN PUBLIK",
            "weight_percentage": 11,
            "standard_rating": 3.00,
            "order": 5,
            "sub_aspects": []
          },
          {
            "code": "pengembangan_diri_dan_orang_lain",
            "name": "PENGEMBANGAN DIRI DAN ORANG LAIN",
            "weight_percentage": 11,
            "standard_rating": 3.00,
            "order": 6,
            "sub_aspects": []
          },
          {
            "code": "mengelola_perubahan",
            "name": "MENGELOLA PERUBAHAN",
            "weight_percentage": 11,
            "standard_rating": 3.00,
            "order": 7,
            "sub_aspects": []
          },
          {
            "code": "pengambilan_keputusan",
            "name": "PENGAMBILAN KEPUTUSAN",
            "weight_percentage": 11,
            "standard_rating": 3.00,
            "order": 8,
            "sub_aspects": []
          },
          {
            "code": "perekat_bangsa",
            "name": "PEREKAT BANGSA",
            "weight_percentage": 12,
            "standard_rating": 3.00,
            "order": 9,
            "sub_aspects": []
          }
        ]
      }
    ]
    }
  ],
  "event": {
    "code": "P3K-KEJAKSAAN-2025",
    "name": "Seleksi P3K Kejaksaan 2025",
    "description": "Seleksi PPPK untuk Kejaksaan tahun 2025",
    "year": 2025,
    "start_date": "2025-01-15",
    "end_date": "2025-03-30",
    "status": "ongoing"
  },
  "batches": [
    {
      "code": "BATCH-1-MOJOKERTO",
      "name": "Batch 1 - Mojokerto",
      "location": "Mojokerto, Jawa Timur",
      "batch_number": 1,
      "start_date": "2025-01-15",
      "end_date": "2025-01-17"
    },
    {
      "code": "BATCH-2-SURABAYA",
      "name": "Batch 2 - Surabaya",
      "location": "Surabaya, Jawa Timur",
      "batch_number": 2,
      "start_date": "2025-01-20",
      "end_date": "2025-01-22"
    }
  ],
  "position_formations": [
    {
      "code": "fisikawan_medis",
      "name": "Fisikawan Medis",
      "quota": 5,
      "template_code": "p3k_standard_2025"
    },
    {
      "code": "analis_kesehatan",
      "name": "Analis Kesehatan",
      "quota": 10,
      "template_code": "p3k_standard_2025"
    }
  ],
  "participants": [
    {
      "test_number": "03-5-2-18-001",
      "batch_code": "BATCH-1-MOJOKERTO",
      "position_formation_code": "fisikawan_medis",
      "skb_number": "123456789",
      "name": "EKA FEBRIYANI, S.Si",
      "email": "eka.febriyani@example.com",
      "phone": "08123456789",
      "photo_path": "/uploads/photos/eka.jpg",
      "assessment_date": "2025-01-15",
      "assessments": {
        "potensi": [
          {
            "aspect_code": "kecerdasan",
            "sub_aspects": [
              {
                "sub_aspect_code": "kecerdasan_umum",
                "individual_rating": 3
              },
              {
                "sub_aspect_code": "daya_tangkap",
                "individual_rating": 4
              },
              {
                "sub_aspect_code": "ketelitian",
                "individual_rating": 3
              },
              {
                "sub_aspect_code": "daya_nalar",
                "individual_rating": 4
              },
              {
                "sub_aspect_code": "kecepatan_berpikir",
                "individual_rating": 3
              },
              {
                "sub_aspect_code": "fleksibilitas_berpikir",
                "individual_rating": 4
              }
            ]
          },
          {
            "aspect_code": "sikap_kerja",
            "sub_aspects": [
              {
                "sub_aspect_code": "ketekunan",
                "individual_rating": 4
              },
              {
                "sub_aspect_code": "keuletan",
                "individual_rating": 4
              },
              {
                "sub_aspect_code": "daya_tahan",
                "individual_rating": 3
              },
              {
                "sub_aspect_code": "motivasi_berprestasi",
                "individual_rating": 4
              },
              {
                "sub_aspect_code": "sikap_terhadap_atasan",
                "individual_rating": 4
              },
              {
                "sub_aspect_code": "sikap_terhadap_pekerjaan",
                "individual_rating": 3
              },
              {
                "sub_aspect_code": "inisiatif",
                "individual_rating": 4
              }
            ]
          },
          {
            "aspect_code": "hubungan_sosial",
            "sub_aspects": [
              {
                "sub_aspect_code": "kemampuan_beradaptasi",
                "individual_rating": 3
              },
              {
                "sub_aspect_code": "kemampuan_bekerja_sama",
                "individual_rating": 4
              },
              {
                "sub_aspect_code": "kepemimpinan",
                "individual_rating": 3
              },
              {
                "sub_aspect_code": "kemampuan_komunikasi",
                "individual_rating": 4
              }
            ]
          },
          {
            "aspect_code": "kepribadian",
            "sub_aspects": [
              {
                "sub_aspect_code": "stabilitas_emosi",
                "individual_rating": 4
              },
              {
                "sub_aspect_code": "kontrol_diri",
                "individual_rating": 3
              },
              {
                "sub_aspect_code": "kepercayaan_diri",
                "individual_rating": 4
              },
              {
                "sub_aspect_code": "kesadaran_diri",
                "individual_rating": 3
              },
              {
                "sub_aspect_code": "tanggung_jawab",
                "individual_rating": 4
              },
              {
                "sub_aspect_code": "kejujuran",
                "individual_rating": 4
              }
            ]
          }
        ],
        "kompetensi": [
          {
            "aspect_code": "integritas",
            "individual_rating": 3
          },
          {
            "aspect_code": "kerjasama",
            "individual_rating": 4
          },
          {
            "aspect_code": "komunikasi",
            "individual_rating": 3
          },
          {
            "aspect_code": "orientasi_pada_hasil",
            "individual_rating": 4
          },
          {
            "aspect_code": "pelayanan_publik",
            "individual_rating": 3
          },
          {
            "aspect_code": "pengembangan_diri_dan_orang_lain",
            "individual_rating": 3
          },
          {
            "aspect_code": "mengelola_perubahan",
            "individual_rating": 4
          },
          {
            "aspect_code": "pengambilan_keputusan",
            "individual_rating": 3
          },
          {
            "aspect_code": "perekat_bangsa",
            "individual_rating": 4
          }
        ]
      },
      "psychological_test": {
        "raw_score": 85.50,
        "iq_score": 120,
        "validity_status": "Valid",
        "internal_status": "Stabil",
        "interpersonal_status": "Baik",
        "work_capacity_status": "Tinggi",
        "clinical_status": "Normal",
        "conclusion_code": "MS",
        "conclusion_text": "Memenuhi Syarat",
        "notes": null
      },
      "interpretations": [
        {
          "category_type_code": "potensi",
          "interpretation_text": "Peserta menunjukkan kemampuan kognitif yang baik dengan kecerdasan umum dan daya tangkap di atas rata-rata. Kemampuan berpikir logis dan analitis cukup baik untuk menunjang tugas-tugas kompleks."
        },
        {
          "category_type_code": "kompetensi",
          "interpretation_text": "Peserta memiliki integritas dan kerjasama yang baik. Orientasi pada hasil dan kemampuan mengelola perubahan menunjukkan potensi untuk berkembang dalam organisasi."
        }
      ]
    }
  ]
}
```

</details>

---

## ✅ RESPONSE FORMAT

### **Success Response (200 OK)**

```json
{
  "success": true,
  "message": "Assessment data synced successfully",
  "data": {
    "institution_id": 1,
    "event_id": 1,
    "participants_synced": 2000,
    "assessments_calculated": 2000,
    "synced_at": "2025-10-08T10:30:00Z"
  }
}
```

### **Error Response (422 Unprocessable Entity)**

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "template.category_types.0.weight_percentage": [
      "The sum of category weights must equal 100"
    ],
    "participants.0.assessments.potensi.0.sub_aspects": [
      "Sub-aspects cannot be empty for Potensi aspects"
    ],
    "participants.5.test_number": [
      "The test number has already been taken"
    ]
  }
}
```

### **Error Response (401 Unauthorized)**

```json
{
  "success": false,
  "message": "Invalid API key"
}
```

---

## 🔍 VALIDATION CHECKLIST

Before sending data, please verify:

### **Template Structure**

- [ ] Category weights sum to 100
- [ ] Aspect weights per category sum to 100
- [ ] Potensi aspects HAVE sub_aspects (not empty)
- [ ] Kompetensi aspects DO NOT HAVE sub_aspects (empty array)
- [ ] All standard_rating values are provided

### **Assessments Data**

- [ ] All aspects from template are present in assessments
- [ ] All sub-aspects from template are present for Potensi
- [ ] Individual ratings are INTEGER 1-5 (not decimal)
- [ ] Potensi has sub_aspects with ratings
- [ ] Kompetensi has direct aspect ratings (no sub_aspects)

### **Relationships**

- [ ] batch_code exists in batches array
- [ ] position_formation_code exists in position_formations array
- [ ] position template_code exists in templates array
- [ ] aspect_code exists in corresponding template aspects
- [ ] sub_aspect_code exists in corresponding template sub_aspects
- [ ] category_type_code exists in corresponding template categories

### **Unique Identifiers**

- [ ] institution.code is unique
- [ ] template.code is unique
- [ ] event.code is unique
- [ ] test_number is unique globally
- [ ] batch.code is unique per event
- [ ] position_formation.code is unique per event

---

## 🚀 WHAT HAPPENS AFTER API CALL

### **Laravel Processing Steps:**

1. **Validate** incoming data (structure, relationships, constraints)
2. **Upsert Master Data:**
   - Institution
   - All templates (multiple templates can be sent)
   - Template structures (categories, aspects, sub-aspects) for each template
3. **Upsert Event Data:**
   - Event (no longer has template_id)
   - Batches
   - Position formations (each linked to its template via template_code)
4. **Upsert Participants:**
   - Participant basic info
   - Each participant inherits template from their position
5. **Store Raw Assessments:**
   - Sub-aspect assessments (Potensi)
   - Aspect assessments (Kompetensi - direct)
6. **Calculate Derived Values:**
   - Aspect ratings for Potensi (AVG from sub-aspects)
   - All scores (`rating × weight_percentage`) using position's template weights
   - All gaps (individual - standard)
   - All percentages
   - Category totals (SUM from aspects)
   - Final assessment (weighted using position's template: Potensi × weight% + Kompetensi × weight%)
7. **Store Psychological Tests & Interpretations**
8. **Return Success Response**

---

## 📞 SUPPORT & QUESTIONS

If you have questions about this API specification:

1. Review [DATABASE_AND_ASSESSMENT_LOGIC.md](./DATABASE_AND_ASSESSMENT_LOGIC.md) for database structure
2. Review [ASSESSMENT_CALCULATION_FLOW.md](./ASSESSMENT_CALCULATION_FLOW.md) for calculation logic
3. Contact the Laravel Dashboard development team

---

**Version:** 1.2
**Status:** ✅ Complete & Ready for Implementation
**Last Updated:** 2025-10-17

**Breaking Changes from v1.1:**
- Changed `template` (single object) → `templates` (array)
- Added `template_code` field to `position_formations` (REQUIRED)
- Event no longer has template relationship
- Each position now specifies its own template
- Multiple templates can be used within one event
