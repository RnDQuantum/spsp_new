# 📊 APLIKASI DASHBOARD ANALYTICS ASESMEN - LARAVEL

## 🎯 TUJUAN APLIKASI

### **Primary Purpose:**

Aplikasi dashboard analytics untuk menampilkan dan menganalisis data hasil asesmen secara berkelompok (per instansi, event, batch, formasi jabatan).

### **Secondary Purpose:**

Menampilkan laporan individual per peserta yang mirip dengan format PDF dari aplikasi utama.

### **Key Features:**

-   ✅ Dashboard analytics dengan visualisasi chart (spider chart, bar chart)
-   ✅ Perbandingan statistik (per batch, per formasi, per aspek)
-   ✅ Detail report individual (seperti PDF)
-   ✅ Manual sync data dari aplikasi utama (CI3)
-   ✅ Read-only application (tidak ada aksi edit/delete)
-   ✅ Simple authentication (optional)

---

## 🏗️ ARSITEKTUR SISTEM

### **Stack Teknologi:**

-   pakai projek ini

### **Integrasi:**

-   **Aplikasi Utama:** CodeIgniter 3 (sudah production)
-   **Data Flow:** Manual sync via button (tidak realtime)
-   **API:** REST API dari CI3 → Laravel

---

## 📊 STRUKTUR DATA

### **Hierarki Data:**

**IMPORTANT: Database hierarchy mengikuti konsep "HOW vs WHO"**
- **Template** = "HOW to Assess" (Blueprint - struktur penilaian universal)
- **Event** = "WHO to Assess" (Execution - pelaksanaan konkret dengan peserta spesifik)

```
┌─────────────────────────────────────────────────────────────────────┐
│ MASTER LAYER (Blueprint/Template Definitions) - "HOW TO ASSESS"    │
└─────────────────────────────────────────────────────────────────────┘

Assessment Templates (Independent Master - Reusable Blueprint)
    ├─ Category Types (Potensi 40%, Kompetensi 60%)
    │   └─ Aspects (dengan weight per category)
    │       └─ Sub-Aspects (detail per aspect, optional)
    │
    └─ [Referenced by Assessment Events via template_id FK]

Institutions (Independent Master - Standalone)
    └─ [Referenced by Assessment Events via institution_id FK]

┌─────────────────────────────────────────────────────────────────────┐
│ EXECUTION LAYER (Transaction/Operational Data) - "WHO TO ASSESS"   │
└─────────────────────────────────────────────────────────────────────┘

Assessment Events (Pelaksanaan Asesmen)
    ├─ Uses Template (FK → assessment_templates) ← Event MEMILIH template
    ├─ Belongs to Institution (FK → institutions)
    ├─ Batches (Gelombang/Lokasi)
    ├─ Position Formations (Formasi Jabatan - specific to event)
    └─ Participants (Peserta)
        ├─ Category Assessments (Potensi & Kompetensi)
        │   └─ Aspect Assessments
        │       └─ Sub-Aspect Assessments
        ├─ Final Assessment (Hasil Akhir)
        ├─ Psychological Test (Tes Kejiwaan)
        └─ Interpretations (Narasi)
```

**Key Design Principles:**
1. **Template = Blueprint** (reusable, defines assessment structure)
2. **Event = Instance** (uses specific template, has specific participants)
3. **Separation**: Template defines "HOW", Event defines "WHO"
4. **Flexibility**: Multiple events can use same template with different participants/positions

### **Kategori Penilaian:**

1. **POTENSI (40%)**

    - Kecerdasan (30%)
    - Sikap Kerja (20%)
    - Hubungan Sosial (20%)
    - Kepribadian (30%)

2. **KOMPETENSI (60%)**
    - Integritas (12%)
    - Kerjasama (11%)
    - Komunikasi (11%)
    - Orientasi Pada Hasil (11%)
    - Pelayanan Publik (11%)
    - Pengembangan Diri & Orang Lain (11%)
    - Mengelola Perubahan (11%)
    - Pengambilan Keputusan (11%)
    - Perekat Bangsa (11%)

---

## 🗄️ DATABASE DESIGN

### **Total Tables: 16**

---

### **MASTER TABLES (5)**

#### **1. institutions**

```
├─ id (PK, bigint unsigned)
├─ code (string, UNIQUE) - 'kejaksaan', 'kemenkeu'
├─ name (string)
├─ logo_path (string, nullable)
├─ api_key (string, UNIQUE) - untuk validasi API
└─ timestamps

INDEX: code
```

#### **2. assessment_templates**

```
├─ id (PK, bigint unsigned)
├─ code (string, UNIQUE) - 'p3k_standard_2025'
├─ name (string)
├─ description (text, nullable)
└─ timestamps

INDEX: code
```

#### **3. category_types** (Potensi / Kompetensi)

```
├─ id (PK, bigint unsigned)
├─ template_id (FK → assessment_templates)
├─ code (string) - 'potensi', 'kompetensi'
├─ name (string)
├─ weight_percentage (integer) - 40, 60
├─ order (integer)
└─ timestamps

INDEX: template_id
UNIQUE: template_id + code
```

#### **4. aspects**

```
├─ id (PK, bigint unsigned)
├─ template_id (FK → assessment_templates) ← ADDED 2025-10-06
├─ category_type_id (FK → category_types)
├─ code (string) - 'kecerdasan', 'integritas'
├─ name (string)
├─ weight_percentage (integer) - 30, 20, 12, 11
├─ standard_rating (decimal 5,2, nullable) - 3.50, 3.20, 3.75 ← FILLED 2025-10-06
├─ order (integer)
└─ timestamps

INDEX: template_id
INDEX: category_type_id
INDEX: code
UNIQUE: template_id + category_type_id + code

NOTE: template_id ditambahkan untuk mendukung template berbeda
      yang bisa punya aspek dengan bobot berbeda.
      Contoh: Template 1 (4 aspek Potensi) vs Template 2 (2 aspek Potensi)
      akan punya weight_percentage berbeda untuk aspek yang sama.

      standard_rating: Nilai standar per aspek (master/blueprint).
      Akan di-snapshot ke aspect_assessments saat assessment untuk
      historical data integrity (Snapshot Pattern).
```

#### **5. sub_aspects**

```
├─ id (PK, bigint unsigned)
├─ aspect_id (FK → aspects)
├─ code (string) - 'kecerdasan_umum'
├─ name (string)
├─ standard_rating (integer, nullable) - 3, 4 ← FILLED 2025-10-06
├─ description (text, nullable)
├─ order (integer)
└─ timestamps

INDEX: aspect_id

NOTE: Untuk kompetensi yang tidak punya sub-aspect,
      table ini tidak perlu diisi (empty relation)

      standard_rating: Nilai standar per sub-aspect (master/blueprint).
      Akan di-snapshot ke sub_aspect_assessments saat assessment untuk
      historical data integrity (Snapshot Pattern).
```

---

### **EVENT & EXECUTION (3)**

#### **6. assessment_events**

```
├─ id (PK, bigint unsigned)
├─ institution_id (FK → institutions)
├─ template_id (FK → assessment_templates)
├─ code (string, UNIQUE) - 'P3K-KEJAKSAAN-2025'
├─ name (string)
├─ year (integer)
├─ start_date (date)
├─ end_date (date)
├─ status (enum) - 'draft', 'ongoing', 'completed'
├─ last_synced_at (timestamp, nullable) - track terakhir sync
└─ timestamps

INDEX: institution_id
INDEX: code
INDEX: status
```

#### **7. batches** (Gelombang/Lokasi)

```
├─ id (PK, bigint unsigned)
├─ event_id (FK → assessment_events)
├─ code (string) - 'BATCH-1-MOJOKERTO'
├─ name (string)
├─ location (string)
├─ batch_number (integer)
├─ start_date (date)
├─ end_date (date)
└─ timestamps

INDEX: event_id
UNIQUE: event_id + code
```

#### **8. position_formations** (Formasi Jabatan)

```
├─ id (PK, bigint unsigned)
├─ event_id (FK → assessment_events)
├─ code (string) - 'fisikawan_medis'
├─ name (string)
├─ quota (integer, nullable)
└─ timestamps

INDEX: event_id
UNIQUE: event_id + code
```

---

### **PARTICIPANT DATA (1)**

#### **9. participants**

```
├─ id (PK, bigint unsigned)
├─ event_id (FK → assessment_events)
├─ batch_id (FK → batches, nullable)
├─ position_formation_id (FK → position_formations)
├─ test_number (string, UNIQUE) - '03-5-2-18-001'
├─ skb_number (string)
├─ name (string)
├─ email (string, nullable)
├─ phone (string, nullable)
├─ photo_path (string, nullable)
├─ assessment_date (date)
└─ timestamps

UNIQUE INDEX: test_number
INDEX: event_id
INDEX: batch_id
INDEX: position_formation_id
INDEX: name (untuk search)
```

---

### **ASSESSMENT SCORES (3)**

#### **10. category_assessments** (Nilai per Kategori)

```
├─ id (PK, bigint unsigned)
├─ participant_id (FK → participants)
├─ category_type_id (FK → category_types)
├─ total_standard_rating (decimal 8,2) - 11.94, 24.30
├─ total_standard_score (decimal 8,2) - 300.21, 270.00
├─ total_individual_rating (decimal 8,2) - 11.83, 27.48
├─ total_individual_score (decimal 8,2) - 294.25, 305.36
├─ gap_rating (decimal 8,2) - -0.11, 3.18
├─ gap_score (decimal 8,2) - -5.97, 35.36
├─ conclusion_code (string) - 'below_standard', 'competent', 'very_competent'
├─ conclusion_text (string) - 'DI BAWAH STANDARD', 'SANGAT KOMPETEN'
└─ timestamps

UNIQUE INDEX: participant_id + category_type_id
INDEX: category_type_id
INDEX: conclusion_code (untuk filtering dashboard)
```

#### **11. aspect_assessments** (Nilai per Aspek)

```
├─ id (PK, bigint unsigned)
├─ category_assessment_id (FK → category_assessments)
├─ aspect_id (FK → aspects)
├─ standard_rating (decimal 5,2) - 3.15
├─ standard_score (decimal 8,2) - 94.50
├─ individual_rating (decimal 5,2) - 2.58
├─ individual_score (decimal 8,2) - 77.29
├─ gap_rating (decimal 8,2) - -0.57
├─ gap_score (decimal 8,2) - -17.21
├─ percentage_score (integer) - 78 (untuk display & chart)
├─ conclusion_code (string) - 'below_standard', 'meets_standard', 'exceeds_standard'
├─ conclusion_text (string) - 'Kurang Memenuhi Standard'
├─ description_text (text, nullable) - khusus untuk kompetensi
└─ timestamps

INDEX: category_assessment_id
INDEX: aspect_id (untuk aggregate by aspect)
```

#### **12. sub_aspect_assessments** (Nilai per Sub-Aspek)

```
├─ id (PK, bigint unsigned)
├─ aspect_assessment_id (FK → aspect_assessments)
├─ sub_aspect_id (FK → sub_aspects)
├─ standard_rating (integer) - 3
├─ individual_rating (integer) - 3
├─ rating_label (string) - 'Cukup', 'Baik', 'Baik Sekali'
└─ timestamps

INDEX: aspect_assessment_id
INDEX: sub_aspect_id
```

---

### **FINAL RESULTS (3)**

#### **13. final_assessments**

```
├─ id (PK, bigint unsigned)
├─ participant_id (FK → participants, UNIQUE)
├─ potensi_weight (integer) - 40
├─ potensi_standard_score (decimal 8,2) - 133.43
├─ potensi_individual_score (decimal 8,2) - 117.70
├─ kompetensi_weight (integer) - 60
├─ kompetensi_standard_score (decimal 8,2) - 180.00
├─ kompetensi_individual_score (decimal 8,2) - 183.22
├─ total_standard_score (decimal 8,2) - 313.43
├─ total_individual_score (decimal 8,2) - 300.91
├─ achievement_percentage (decimal 5,2) - 96.01 (calculated: individual/standard*100)
├─ final_conclusion_code (string) - 'mms', 'ms', 'tms'
├─ final_conclusion_text (string) - 'MASIH MEMENUHI SYARAT (MMS)'
└─ timestamps

UNIQUE INDEX: participant_id
INDEX: final_conclusion_code
INDEX: achievement_percentage (untuk ranking)
```

#### **14. psychological_tests**

```
├─ id (PK, bigint unsigned)
├─ participant_id (FK → participants, UNIQUE)
├─ raw_score (decimal 5,2) - 40.00
├─ iq_score (integer, nullable) - 97
├─ validity_status (string)
├─ internal_status (string)
├─ interpersonal_status (string)
├─ work_capacity_status (string)
├─ clinical_status (string)
├─ conclusion_code (string) - 'ms', 'tms'
├─ conclusion_text (string)
├─ notes (text, nullable)
└─ timestamps

UNIQUE INDEX: participant_id
INDEX: conclusion_code
```

#### **15. interpretations**

```
├─ id (PK, bigint unsigned)
├─ participant_id (FK → participants)
├─ category_type_id (FK → category_types, nullable)
├─ interpretation_text (text)
└─ timestamps

INDEX: participant_id
INDEX: category_type_id

NOTE: 1 peserta bisa punya 2 interpretations:
      - 1 untuk Potensi (category_type_id = potensi)
      - 1 untuk Kompetensi (category_type_id = kompetensi)
      Atau bisa general (category_type_id = null)
```

---

### **AUTH (1)**

#### **16. users** (Laravel default, simplified)

```
├─ id (PK, bigint unsigned)
├─ name (string)
├─ email (string, UNIQUE)
├─ password (string)
├─ remember_token (string, nullable)
└─ timestamps

NOTE: Simple auth, no roles
      Semua user punya akses sama
```

---

### **DATABASE RELATIONSHIPS**

```
Institution (1) ──< (N) AssessmentEvent
AssessmentTemplate (1) ──< (N) AssessmentEvent
AssessmentTemplate (1) ──< (N) CategoryType
AssessmentTemplate (1) ──< (N) Aspect ← ADDED 2025-10-06 (direct relation)
CategoryType (1) ──< (N) Aspect
Aspect (1) ──< (N) SubAspect (optional, bisa 0)

AssessmentEvent (1) ──< (N) Batch
AssessmentEvent (1) ──< (N) PositionFormation
AssessmentEvent (1) ──< (N) Participant

Participant (1) ──< (N) CategoryAssessment (selalu 2: Potensi + Kompetensi)
Participant (1) ──── (1) FinalAssessment
Participant (1) ──── (1) PsychologicalTest
Participant (1) ──< (N) Interpretation (0-2 records)

CategoryAssessment (1) ──< (N) AspectAssessment
AspectAssessment (1) ──< (N) SubAspectAssessment (0-N, tergantung aspek)
```

**IMPORTANT NOTE (2025-10-06):**
Aspects now have DUAL relationship:
- template_id: Direct FK to template (for defining weight per template)
- category_type_id: FK to category (for grouping Potensi/Kompetensi)

This allows same aspect code to have different weights in different templates.
Example:
- Template P3K: Kecerdasan (30% of Potensi)
- Template CPNS: Kecerdasan (50% of Potensi)
```

---

### **Key Unique Identifiers:**

-   **Institution:** `code` (kejaksaan, kemenkeu)
-   **Event:** `code` (P3K-KEJAKSAAN-2025)
-   **Participant:** `test_number` (03-5-2-18-001) ← PRIMARY KEY

---

## 🔄 DATA FLOW & SYNC MECHANISM

### **Sync Process:**

```
┌─────────────────────────────────────┐
│   UI LARAVEL (Manual Button)       │
│   Input: Event Code                 │
│   Click: [Sync Data]                │
└──────────────┬──────────────────────┘
               │
               ↓ HTTP GET + API Key
┌──────────────┴──────────────────────┐
│   CI3 API Controller                │
│   GET /api/events/{code}/export     │
│   - Validate API Key                │
│   - Get event + template structure  │
│   - Get all participants + scores   │
│   - Return JSON (complete data)     │
└──────────────┬──────────────────────┘
               │
               ↓ JSON Response
┌──────────────┴──────────────────────┐
│   Laravel SyncService               │
│   - Validate structure              │
│   - Begin DB Transaction            │
│   - Upsert institution              │
│   - Upsert template (dynamic)       │
│   - Upsert event                    │
│   - Upsert batches & positions      │
│   - Loop participants:              │
│     • Upsert participant            │
│     • Upsert assessments            │
│     • Upsert final result           │
│     • Upsert psych test             │
│   - Commit transaction              │
│   - Update last_synced_at           │
└──────────────┬──────────────────────┘
               │
               ↓
       [Data Ready to Display]
```

### **Karakteristik Sync:**

-   ✅ **Manual trigger** - tidak otomatis
-   ✅ **Idempotent** - bisa sync berulang tanpa duplikasi
-   ✅ **Upsert pattern** - update if exists, insert if not
-   ✅ **Transaction-based** - all or nothing
-   ✅ **Error handling** - log & continue untuk participant gagal
-   ✅ **Progress tracking** - log setiap 10 peserta

---

## 📡 API SPECIFICATION

### **Endpoint CI3 (yang harus dibuat):**

**GET** `/api/events/{event_code}/export`

**Headers:**

```
X-API-Key: {shared_secret_key}
```

**Note:** Untuk detail lengkap API specification termasuk full JSON structure, error responses, dan testing guide, lihat file [API_SPECIFICATION.md](./API_SPECIFICATION.md)

**Response Summary:**

```json
{
  "success": true,
  "data": {
    "institution": {...},
    "template": {
      "categories": [
        {
          "code": "potensi",
          "aspects": [...with sub_aspects]
        },
        {
          "code": "kompetensi",
          "aspects": [...no sub_aspects]
        }
      ]
    },
    "event": {...},
    "batches": [...],
    "positions": [...],
    "participants": [...]
  },
  "meta": {...}
}
```

---

## 🎨 UI/UX STRUCTURE

### **Page Hierarchy:**

1. **Home/Dashboard** (`/`)

    - List semua events
    - Button: Sync Event Baru
    - Button per event: View Dashboard, Re-sync

2. **Event Dashboard** (`/events/{code}`)

    - Overview statistics
    - Spider charts (Potensi & Kompetensi rata-rata)
    - Distribution charts
    - Comparison by batch
    - Comparison by position
    - Top performers table
    - All participants table (searchable)

3. **Batch Detail** (`/events/{code}/batches/{id}`)

    - Stats khusus batch
    - Comparison dengan batch lain
    - List participants in batch

4. **Position Detail** (`/events/{code}/positions/{id}`)

    - Stats khusus formasi
    - Comparison dengan formasi lain
    - Ranking participants

5. **Participant Detail** (`/participants/{test_number}`)

    - Info peserta
    - Spider charts (Individual vs Standard)
    - Table Profil Potensi
    - Table Profil Kompetensi
    - Interpretasi text
    - Psych test result
    - Final conclusion
    - (Future: Export PDF button)

6. **Sync Page** (`/sync`)
    - Form input event code
    - Button: Fetch & Sync
    - Progress indicator
    - Success/Error message

---

## 📝 IMPLEMENTATION CHECKLIST

### **PHASE 1: Project Setup** ⏳

-   [ ] Install Laravel 11
-   [ ] Setup database connection
-   [ ] Configure .env (CI3_API_URL, CI3_API_KEY)
-   [ ] Install dependencies:
    -   [ ] Tailwind CSS
    -   [ ] Alpine.js
    -   [ ] Chart.js / ApexCharts
-   [ ] Setup Git repository

### **PHASE 2: Database & Models** ✅

-   [x] Create all migrations (16 tables)
    -   [x] Master tables (5)
    -   [x] Event & execution (3)
    -   [x] Participant data (1)
    -   [x] Assessment scores (3)
    -   [x] Final results (3)
    -   [x] Auth table (1)
-   [x] Test migrations (up & down)
-   [x] Create all Eloquent models (15 models)
-   [x] Define relationships
-   [x] Test relationships via Tinker
-   [x] Create seeders for testing
    -   [x] InstitutionSeeder (4 institutions)
    -   [x] AssessmentTemplateSeeder (3 templates)
    -   [x] MasterDataSeeder (categories, aspects, sub-aspects)
    -   [x] SampleDataSeeder (4 participants with full assessments)

### **PHASE 3: API Integration (CI3 Side)** ⏭️ SKIPPED

> **Note:** Phase ini ditunda karena API CI3 belum ready. Akan dikerjakan setelah UI selesai (parallel development).

-   [ ] Create API controller in CI3
-   [ ] Implement authentication (API key validation)
-   [ ] Create method to get event data
-   [ ] Create method to get all participants with scores
-   [ ] Structure JSON response
-   [ ] Test endpoint with Postman/Insomnia
-   [ ] Handle edge cases (event not found, no participants, etc)

### **PHASE 4: Sync Service (Laravel)** ⏭️ SKIPPED

> **Note:** Phase ini ditunda karena tergantung API CI3. Sementara development UI menggunakan seeder data.

-   [ ] Create SyncService class
-   [ ] Implement HTTP client to CI3 API
-   [ ] Implement validation methods
-   [ ] Implement sync methods:
    -   [ ] syncInstitution()
    -   [ ] syncTemplate() - dynamic structure
    -   [ ] syncEvent()
    -   [ ] syncBatches()
    -   [ ] syncPositions()
    -   [ ] syncParticipants() - with loop
    -   [ ] syncCategoryAssessment()
    -   [ ] syncAspectAssessment()
    -   [ ] syncSubAspectAssessment()
    -   [ ] syncFinalAssessment()
    -   [ ] syncPsychologicalTest()
    -   [ ] syncInterpretations()
-   [ ] Implement helper methods (mapping, calculation)
-   [ ] Implement error handling & logging
-   [ ] Test with sample data

### **PHASE 5: Analytics Service** ⏳

-   [ ] Create AnalyticsService class
-   [ ] Implement query methods:
    -   [ ] getEventOverview()
    -   [ ] getAverageScoresByAspect() - untuk spider chart
    -   [ ] getRatingDistribution() - untuk bar chart
    -   [ ] getComparisonByBatch()
    -   [ ] getComparisonByPosition()
    -   [ ] getTopPerformers()
-   [ ] Optimize queries with indexes
-   [ ] Test queries dengan large dataset

### **PHASE 6: Controllers & Routes** ⏳

-   [ ] Create SyncController
    -   [ ] index() - show sync form
    -   [ ] sync() - process sync
    -   [ ] resync() - re-sync existing event
-   [ ] Create EventController
    -   [ ] index() - list events
    -   [ ] dashboard() - event analytics
    -   [ ] batchDetail()
    -   [ ] positionDetail()
-   [ ] Create ParticipantController
    -   [ ] show() - individual report
    -   [ ] downloadPdf() (future)
-   [ ] Create DashboardController
    -   [ ] home() - landing page
-   [ ] Setup routes in web.php
-   [ ] Setup API routes (future)

### **PHASE 7: Views & Blade Templates** ⏳

-   [ ] Setup Tailwind CSS config
-   [ ] Create main layout template
    -   [ ] Header/navbar
    -   [ ] Sidebar (optional)
    -   [ ] Footer
    -   [ ] Flash messages component
-   [ ] Create sync views:
    -   [ ] sync/index.blade.php - form
-   [ ] Create event views:
    -   [ ] events/index.blade.php - list
    -   [ ] events/dashboard.blade.php - analytics
    -   [ ] events/batch-detail.blade.php
    -   [ ] events/position-detail.blade.php
-   [ ] Create participant views:
    -   [ ] participants/show.blade.php - individual report
-   [ ] Create home view:
    -   [ ] home.blade.php

### **PHASE 8: Chart Implementation** ⏳

-   [ ] Install & setup Chart.js / ApexCharts
-   [ ] Create chart components:
    -   [ ] Spider chart component (reusable)
    -   [ ] Bar chart component
    -   [ ] Line chart component (optional)
-   [ ] Implement Potensi spider chart
-   [ ] Implement Kompetensi spider chart
-   [ ] Implement distribution charts
-   [ ] Implement comparison charts
-   [ ] Make charts responsive
-   [ ] Add chart legends & tooltips

### **PHASE 9: Authentication (Optional)** ⏳

-   [ ] Setup Laravel Breeze/UI
-   [ ] Disable registration
-   [ ] Create default user seeder
-   [ ] Add auth middleware to sensitive routes
-   [ ] Customize login view

### **PHASE 10: Testing & Refinement** ⏳

-   [ ] Test full sync flow
    -   [ ] New event sync
    -   [ ] Re-sync existing event
    -   [ ] Error handling
-   [ ] Test with multiple events
-   [ ] Test with large dataset (100+ participants)
-   [ ] Test all analytics queries
-   [ ] Test all visualizations
-   [ ] Cross-browser testing
-   [ ] Mobile responsive testing
-   [ ] Performance optimization:
    -   [ ] Add database indexes
    -   [ ] Optimize N+1 queries
    -   [ ] Add pagination where needed
-   [ ] UI/UX improvements
-   [ ] Add loading indicators
-   [ ] Add empty states
-   [ ] Add error states

### **PHASE 11: Documentation** ⏳

-   [ ] Write README.md
-   [ ] Document API integration
-   [ ] Document sync process
-   [ ] Document deployment steps
-   [ ] Create user guide (optional)
-   [ ] Add inline code comments

### **PHASE 12: Deployment Preparation** ⏳

-   [ ] Setup production .env
-   [ ] Configure production database
-   [ ] Setup HTTPS for API calls
-   [ ] Setup queue worker (if using jobs)
-   [ ] Setup error monitoring (Sentry, etc)
-   [ ] Setup backup strategy
-   [ ] Create deployment script
-   [ ] Test in staging environment

---

## ⚠️ KNOWN ISSUES & CONSIDERATIONS

### **Database:**

1. **Dynamic Template Structure**

    - Setiap event bisa punya struktur aspect berbeda
    - Solution: Template system dengan master tables
    - Status: ✅ Designed

2. **Large Dataset Performance**

    - Event bisa punya ratusan/ribuan peserta
    - Solution: Proper indexing, pagination, lazy loading
    - Status: ⚠️ Need testing

3. **Data Consistency**
    - Data bisa berubah di CI3 setelah sync
    - Solution: Manual re-sync, show last_synced_at
    - Status: ✅ Handled

### **API Integration:**

1. **Network Timeout**

    - Export data besar bisa lama
    - Solution: Increase timeout, add retry mechanism
    - Status: ⚠️ Set 120s timeout

2. **API Authentication**

    - Shared API key perlu secure
    - Solution: HTTPS, environment variable
    - Status: ⚠️ Need HTTPS in production

3. **CI3 Server Availability**
    - Jika CI3 down, sync gagal
    - Solution: Graceful error handling, retry later
    - Status: ✅ Error handling implemented

### **Performance:**

1. **Spider Chart Rendering**

    - Multiple charts per page bisa lambat
    - Solution: Lazy load, optimize data
    - Status: ⏳ Belum ditest

2. **Analytics Queries**
    - Complex aggregate queries bisa lambat
    - Solution: Proper indexes, caching (future)
    - Status: ⚠️ Need optimization testing

### **UI/UX:**

1. **Mobile Responsive**

    - Charts sulit di mobile
    - Solution: Responsive design, touch-friendly
    - Status: ⏳ Need implementation

2. **Data Presentation**
    - Banyak data, bisa overwhelming
    - Solution: Good hierarchy, filters, search
    - Status: ⏳ Need design

---

## 🐛 POTENTIAL PROBLEMS & SOLUTIONS

### **Problem 1: Sync Timeout untuk Event Besar**

**Scenario:** Event dengan 1000+ peserta, JSON response besar, timeout.

**Solutions:**

-   ✅ Increase HTTP timeout (120s)
-   🔄 Implement pagination di API CI3 (batch 100 peserta)
-   🔄 Use queue jobs untuk background sync
-   🔄 Show progress bar dengan AJAX polling

**Priority:** Medium (jika dataset > 500 peserta)

---

### **Problem 2: Data Tidak Konsisten Setelah Re-sync**

**Scenario:** Data di CI3 berubah, re-sync override data lama.

**Solutions:**

-   ✅ Upsert pattern (update existing)
-   ✅ Show last_synced_at di UI
-   🔄 Add audit log (track changes)
-   🔄 Soft delete untuk historical data

**Priority:** Low (acceptable behavior)

---

### **Problem 3: Template Structure Berubah**

**Scenario:** CI3 update structure (tambah aspek baru), existing data jadi incompatible.

**Solutions:**

-   ✅ Template versioning system
-   ✅ Dynamic template from API
-   🔄 Migration tool untuk update old data
-   🔄 Support multiple template versions

**Priority:** Medium (future-proof)

---

### **Problem 4: Spider Chart Tidak Muncul**

**Scenario:** Chart library gagal load atau data format salah.

**Solutions:**

-   ✅ Validate data format sebelum render
-   ✅ Add fallback (show table if chart fails)
-   ✅ Console error logging
-   🔄 Use reliable chart library (ApexCharts)

**Priority:** High (core feature)

---

### **Problem 5: Slow Analytics Queries**

**Scenario:** Dashboard load lama karena complex queries.

**Solutions:**

-   ✅ Add proper database indexes
-   ✅ Use eager loading (with())
-   🔄 Implement query result caching
-   🔄 Add pre-calculated statistics table
-   🔄 Use database views for complex queries

**Priority:** High (UX critical)

---

### **Problem 6: API Key Exposed**

**Scenario:** API key tercantum di client-side atau version control.

**Solutions:**

-   ✅ Always use .env file
-   ✅ Add .env to .gitignore
-   ✅ Server-side API calls only
-   ✅ Use HTTPS in production
-   🔄 Implement key rotation mechanism

**Priority:** Critical (security)

---

## 📈 FUTURE ENHANCEMENTS

### **Short Term (1-3 months):**

-   [ ] Export individual report to PDF
-   [ ] Export analytics to Excel
-   [ ] Add data filtering (by date range, score range)
-   [ ] Add search functionality (global search)
-   [ ] Email notification setelah sync berhasil
-   [ ] Batch comparison chart (side-by-side)

### **Medium Term (3-6 months):**

-   [ ] Caching layer untuk improve performance
-   [ ] Real-time sync dengan webhook (push dari CI3)
-   [ ] Multi-tenancy support (jika ada banyak instansi)
-   [ ] Custom report builder
-   [ ] Data visualization customization
-   [ ] API untuk third-party integration

### **Long Term (6-12 months):**

-   [ ] Machine learning untuk prediksi passing rate
-   [ ] Automated anomaly detection
-   [ ] Advanced analytics (correlation, regression)
-   [ ] Mobile app (Flutter/React Native)
-   [ ] White-label solution untuk instansi lain

---

## 📞 CONTACT & SUPPORT

### **Development Team:**

-   Lead Developer: [Your Name]
-   Backend: Laravel 11 + MySQL
-   Frontend: Blade + Tailwind + Alpine.js
-   Integration: CodeIgniter 3 (existing)

### **Repository:**

-   Git: [repository URL]
-   Branch Strategy: main (production), develop (development)
-   Commit Convention: [conventional commits]

### **Environment:**

-   Local Development: http://localhost:8000
-   CI3 App: http://localhost/aplikasi-utama
-   Production: [TBD]

---

## 📅 PROJECT TIMELINE

### **Estimated Timeline: 4-6 weeks**

**Week 1:** Database & Models (Phase 1-2)
**Week 2:** API Integration & Sync (Phase 3-4)
**Week 3:** Analytics & Controllers (Phase 5-6)
**Week 4:** Views & Charts (Phase 7-8)
**Week 5:** Testing & Refinement (Phase 9-10)
**Week 6:** Documentation & Deployment (Phase 11-12)

---

## ✅ ACCEPTANCE CRITERIA

### **Minimum Viable Product (MVP):**

-   ✅ Dapat sync data dari CI3 via button
-   ✅ Tampil event dashboard dengan statistics
-   ✅ Tampil spider chart Potensi & Kompetensi
-   ✅ Tampil list participants dengan search
-   ✅ Tampil individual report lengkap
-   ✅ Responsive design (desktop & tablet)
-   ✅ Error handling yang baik

### **Success Metrics:**

-   Sync 150 peserta < 2 menit
-   Dashboard load < 3 detik
-   Individual report load < 2 detik
-   Mobile usability score > 80%
-   Zero critical bugs

---

## 📝 NOTES & DECISIONS

### **Design Decisions:**

1. **Manual Sync vs Real-time**

    - Decision: Manual sync via button
    - Reason: Tidak membebani aplikasi utama, data tidak perlu real-time

2. **Database: MySQL vs PostgreSQL**

    - Decision: Support both (using Laravel migrations)
    - Reason: Flexibility untuk production environment

3. **Charts: Chart.js vs ApexCharts**

    - Decision: ApexCharts (recommended)
    - Reason: Better untuk spider chart, more features, modern UI

4. **Auth: Simple vs Role-based**

    - Decision: Simple auth, no roles
    - Reason: Semua user akses sama, tidak perlu complexity

5. **Caching: Now vs Later**
    - Decision: Later (Phase 10+)
    - Reason: Fokus functionality dulu, optimize kemudian

### **Technical Debt:**

-   None yet (greenfield project)

### **Open Questions:**

-   [ ] Apakah perlu support multiple institutions dalam 1 instance?
-   [ ] Apakah perlu archive/soft delete untuk old events?
-   [ ] Apakah perlu audit trail untuk tracking changes?
-   [ ] Apakah perlu role management di future?

---

---

## 📝 DEVELOPMENT PROGRESS LOG

### **2025-10-06 PM (3) - Documentation Hierarchy Correction ✅**

**Issue Identified:**
During database QC session, discovered that hierarchy diagram in PROJECT_DOCUMENTATION.md was **misleading**:
- Showed `Template` as child of `Assessment Event` ❌
- Actual implementation: `Template` is independent master, `Event` references it ✅

**Correction Made:**
1. ✅ Updated "Hierarki Data" section with correct structure
2. ✅ Added "HOW vs WHO" concept explanation:
   - Template = "HOW to Assess" (Blueprint - universal structure)
   - Event = "WHO to Assess" (Execution - specific participants)
3. ✅ Added clear separation between MASTER LAYER and EXECUTION LAYER
4. ✅ Added Key Design Principles
5. ✅ Updated DATABASE_QC_PROGRESS.md with position_formations rationale

**Key Clarification: Why `position_formations` uses `event_id` not `template_id`?**
- Position formations are EVENT-SPECIFIC (operational decisions)
- Template defines assessment structure, NOT job positions
- Different events can use SAME template but need DIFFERENT positions/quotas
- Example: Event A needs Fisikawan (10), Event B needs Analis (15) - both use same template

**Files Modified:**
- `PROJECT_DOCUMENTATION.md` - Hierarki Data section
- `DATABASE_QC_PROGRESS.md` - Added position_formations QC report + "HOW vs WHO" concept

**Database QC Progress:**
- ✅ position_formations (8/16) - 50% COMPLETE! 🎉

---

### **2025-10-06 PM (2) - Master Tables Standard Rating Implementation ✅**

**Issue Identified:**
During database QC, discovered that `standard_rating` fields were NULL in master tables:
- `aspects.standard_rating` - NULL for all 13 aspects
- `sub_aspects.standard_rating` - NULL for all 23 sub-aspects

**Discussion Points:**
1. **Best Practice Question:** Should standard_rating be stored in BOTH master tables AND assessment tables?
2. **Answer:** YES - This is called "Snapshot Pattern"
3. **Reasoning:**
   - Master table = Current/blueprint value (can change over time)
   - Assessment table = Historical snapshot (never changes after assessment)
   - Purpose: Historical data integrity, audit trail, performance
   - Trade-off: Data redundancy acceptable for data integrity

**Example Scenario Why Snapshot is Needed:**
```
Jan 2025: aspect.standard_rating = 3.50
- Peserta A tested → aspect_assessment.standard_rating = 3.50 (snapshot)
- Gap = individual_rating - 3.50

Mar 2025: aspect.standard_rating changed to 4.00
- Peserta B tested → aspect_assessment.standard_rating = 4.00 (snapshot)
- Peserta A's historical gap STILL = individual_rating - 3.50 ✅ (correct)
- If no snapshot, Peserta A's gap would recalculate as 4.00 ❌ (wrong!)
```

**Solution Implemented:**
1. ✅ Updated MasterDataSeeder.php to fill standard_rating for all aspects
   - Potensi: 3.20 - 3.75 range (decimal)
   - Kompetensi: 3.25 - 3.75 range (decimal)
2. ✅ Updated MasterDataSeeder.php to fill standard_rating for all sub-aspects
   - Range: 3-4 (integer)
3. ✅ Added documentation notes about Snapshot Pattern
4. ✅ Ran migrate:fresh --seed successfully
5. ✅ QC verification: sub_aspects table PASSED (5/16 tables)

**Files Modified:**
- `database/seeders/MasterDataSeeder.php`
- `PROJECT_DOCUMENTATION.md` (added standard_rating notes)
- `DATABASE_QC_PROGRESS.md` (updated sub_aspects QC result)

**Database QC Progress:**
- ✅ institutions (1/16)
- ✅ assessment_templates (2/16)
- ✅ category_types (3/16)
- ✅ aspects (4/16)
- ✅ sub_aspects (5/16) ← NEW
- ⏳ assessment_events (6/16) - NEXT
- ⏸️ Remaining 10 tables...

---

### **2025-10-06 PM (1) - Database QC & Structure Improvement ⚙️**

**Issue Identified:**
During database QC session, user identified critical design flaw:
- Different templates can have different number of aspects
- Same aspect (e.g., "kecerdasan") needs different weight in different templates
- Example: Template with 2 aspects needs 50% weight, template with 4 aspects needs 25% weight
- Without template_id in aspects table, this creates data conflict

**Solution Implemented:**
1. ✅ Added `template_id` field to `aspects` table migration
2. ✅ Added `template()` relationship to Aspect model
3. ✅ Updated seeder to populate template_id and weight_percentage for all aspects
4. ✅ Added unique constraint: (template_id, category_type_id, code)
5. ✅ Verified all aspects have correct weights (Potensi: 30,20,20,30 & Kompetensi: 12,11,11,11,11,11,11,11,11)

**Design Decision Confirmed:**
- Weight percentage stored in master tables (best practice)
- Template = fixed blueprint with predefined weights
- Different templates = different structures & weights
- Event chooses appropriate template

**Files Modified:**
- `database/migrations/2025_10_06_034116_create_aspects_table.php`
- `app/Models/Aspect.php`
- `database/seeders/MasterDataSeeder.php`
- `PROJECT_DOCUMENTATION.md` (documentation updated)

**Database QC Progress:**
- ✅ institutions (1/16)
- ✅ assessment_templates (2/16)
- ✅ category_types (3/16)
- ✅ aspects (4/16) - FIXED & VERIFIED
- ⏸️ sub_aspects (5/16) - Will be reviewed next
- ⏳ Continuing QC for remaining tables...

**Tracking File Created:**
- `DATABASE_QC_PROGRESS.md` - Progress tracking document

---

### **2025-10-06 AM - Phase 2 Completed ✅**

**Completed Tasks:**
- ✅ Created all 16 database migrations with proper relationships
- ✅ Fixed migration execution order and nullable fields
- ✅ Created 15 Eloquent models with relationships and type casting
- ✅ Tested all models via Tinker - verified fillable, casts, and relationships
- ✅ Created comprehensive seeders:
  - InstitutionSeeder: 4 institutions (Kejaksaan, BKN, Kemendikbud, Kemenkes)
  - AssessmentTemplateSeeder: 3 templates (P3K 2025, CPNS JPT Pratama, Administrator)
  - MasterDataSeeder: Complete structure from PDF (2 categories, 13 aspects, 23 sub-aspects)
  - SampleDataSeeder: 16 participants across 3 batches and 5 position formations

**Key Changes:**
- Updated institution codes to be more semantic (kejaksaan, bkn, etc)
- Renamed templates for clarity (p3k_standard_2025 instead of SPSP2024)
- Fixed Event → Institution relationship (Kejaksaan event now correctly uses Kejaksaan institution)
- Improved seeder readability with section comments
- Added multiple batches (3 batches: Mojokerto, Surabaya, Jakarta)
- Added multiple position formations (5 formasi jabatan)
- Added interpretations for all participants (2 per participant)

**Data Summary:**
- 4 Institutions seeded
- 3 Assessment Templates
- 2 Category Types (Potensi 40%, Kompetensi 60%)
- 13 Aspects (4 Potensi, 9 Kompetensi) - NOW WITH WEIGHTS ✅
- 23 Sub-Aspects (only for Potensi aspects)
- 1 Event (P3K Kejaksaan 2025)
- 3 Batches (Mojokerto, Surabaya, Jakarta)
- 5 Position Formations
- 16 Participants with complete assessments
- 32 Interpretations (2 per participant)

**Next Steps:**
- ✅ Database QC in progress (8/16 tables verified - 50% COMPLETE! 🎉)
- ⏳ Next: Review table `participants` (Table 9/16)
- Skip Phase 3 & 4 (API Integration) - will do later
- Move to Phase 5-7: UI Development (Controllers, Routes, Views, Livewire)

---

**Last Updated:** 2025-10-06 PM
**Version:** 1.4
**Status:** Phase 2 Complete + Database QC in Progress 🔍 (50% done - HALFWAY THERE!)
