# DATABASE STRUCTURE - SPSP Analytics Dashboard

**Database Engine:** MySQL/MariaDB
**Total Tables:** 17 (Master: 6, Operational: 4, Assessment: 4, Results: 2, System: 1)

---

## TABLE OVERVIEW

### Master Data Tables (6)
1. **institutions** - Institusi/Organisasi
2. **assessment_templates** - Blueprint standar asesmen
3. **category_types** - Kategori Potensi/Kompetensi per template
4. **aspects** - Aspek penilaian dengan bobot
5. **sub_aspects** - Sub-aspek detail (untuk Potensi)
6. **interpretation_templates** - Template interpretasi narasi

### Operational Tables (4)
7. **assessment_events** - Event/kegiatan asesmen
8. **batches** - Gelombang/batch peserta
9. **position_formations** - Formasi jabatan
10. **participants** - Data peserta

### Assessment Data Tables (4)
11. **category_assessments** - Nilai per kategori (Potensi/Kompetensi)
12. **aspect_assessments** - Nilai per aspek
13. **sub_aspect_assessments** - Nilai per sub-aspek
14. **interpretations** - Interpretasi narasi hasil

### Result Tables (2)
15. **final_assessments** - Hasil akhir gabungan
16. **psychological_tests** - Data tes psikologi

### System Table (1)
17. **users** - Autentikasi user

---

## MASTER DATA TABLES

### 1. institutions

Menyimpan data institusi/organisasi yang menggunakan sistem.

```sql
id                  bigint unsigned (PK)
code                varchar (UNIQUE)          -- 'kejaksaan', 'bnn'
name                varchar                   -- 'Kejaksaan Agung RI'
logo_path           varchar (nullable)
api_key             varchar (UNIQUE)          -- untuk autentikasi API sync
created_at          timestamp
updated_at          timestamp

INDEX: code
```

**Relasi:**
- hasMany: assessment_events

---

### 2. assessment_templates

Blueprint/template standar asesmen yang reusable.

```sql
id                  bigint unsigned (PK)
code                varchar (UNIQUE)          -- 'staff_standard_v1'
name                varchar                   -- 'Standar Asesmen Staff'
description         text (nullable)           -- Deskripsi lengkap template
created_at          timestamp
updated_at          timestamp

INDEX: code
```

**Contoh Template:**
- `staff_standard_v1` - Potensi 50%, Kompetensi 50%
- `supervisor_standard_v1` - Potensi 30%, Kompetensi 70%
- `manager_standard_v1` - Potensi 40%, Kompetensi 60%
- `professional_standard_v1` - Potensi 45%, Kompetensi 55%
- `p3k_standard_2025` - Potensi 40%, Kompetensi 60%

**Relasi:**
- hasMany: category_types
- hasMany: aspects
- hasMany: position_formations

---

### 3. category_types

Kategori utama dalam template (Potensi & Kompetensi).

```sql
id                  bigint unsigned (PK)
template_id         bigint unsigned (FK → assessment_templates)
code                varchar                   -- 'potensi', 'kompetensi'
name                varchar                   -- 'Potensi', 'Kompetensi'
weight_percentage   integer                   -- 40, 60 (total harus 100%)
order               integer                   -- urutan tampilan
created_at          timestamp
updated_at          timestamp

INDEX: template_id
UNIQUE: [template_id, code]
CASCADE DELETE: template deleted → categories deleted
```

**Relasi:**
- belongsTo: assessment_templates
- hasMany: aspects
- hasMany: category_assessments
- hasMany: interpretations

---

### 4. aspects

Aspek penilaian dengan bobot dan standar rating.

```sql
id                  bigint unsigned (PK)
template_id         bigint unsigned (FK → assessment_templates)
category_type_id    bigint unsigned (FK → category_types)
code                varchar                   -- 'kecerdasan', 'integritas'
name                varchar                   -- 'Kecerdasan', 'Integritas'
description         text (nullable)           -- Penjelasan aspek
weight_percentage   integer (nullable)        -- 25, 20, 15 (total 100% per kategori)
standard_rating     decimal(5,2) (nullable)   -- 3.00, 3.60, 3.75
order               integer                   -- urutan tampilan
created_at          timestamp
updated_at          timestamp

INDEX: template_id, category_type_id, code
UNIQUE: [template_id, category_type_id, code]
CASCADE DELETE: template/category deleted → aspects deleted
```

**Dual Foreign Key:**
- `template_id` - Relasi langsung ke template (multi-template support)
- `category_type_id` - Grouping Potensi/Kompetensi

**Contoh Potensi (Staff):**
- Kecerdasan (25%) - standard_rating: 3.00
- Cara Kerja (20%) - standard_rating: 3.60
- Potensi Kerja (20%) - standard_rating: 3.00
- Hubungan Sosial (20%) - standard_rating: 3.75
- Kepribadian (15%) - standard_rating: 3.00

**Contoh Kompetensi (Staff):**
- Integritas (15%) - standard_rating: 3.00
- Kerjasama (14%) - standard_rating: 3.00
- Komunikasi (14%) - standard_rating: 3.00
- Orientasi Pada Hasil (14%) - standard_rating: 3.00
- Pelayanan Publik (14%) - standard_rating: 3.00
- Pengembangan Diri (14%) - standard_rating: 3.00
- Mengelola Perubahan (15%) - standard_rating: 3.00

**Relasi:**
- belongsTo: assessment_templates
- belongsTo: category_types
- hasMany: sub_aspects
- hasMany: aspect_assessments

---

### 5. sub_aspects

Sub-aspek detail untuk breakdown aspek Potensi.

```sql
id                  bigint unsigned (PK)
aspect_id           bigint unsigned (FK → aspects)
code                varchar                   -- 'kecerdasan_umum'
name                varchar                   -- 'Kecerdasan Umum'
description         text (nullable)           -- Penjelasan sub-aspek
standard_rating     integer (nullable)        -- 3, 4, 5 (skala 1-5)
order               integer                   -- urutan tampilan
created_at          timestamp
updated_at          timestamp

INDEX: aspect_id
CASCADE DELETE: aspect deleted → sub_aspects deleted
```

**Note:** Hanya untuk aspek Potensi. Aspek Kompetensi tidak memiliki sub-aspek.

**Contoh Sub-Aspek Kecerdasan:**
- Kecerdasan Umum - standard_rating: 3
- Daya Tangkap - standard_rating: 3
- Daya Analisa - standard_rating: 3
- Kemampuan Logika - standard_rating: 3

**Relasi:**
- belongsTo: aspects
- hasMany: sub_aspect_assessments

---

### 6. interpretation_templates

Template interpretasi narasi berdasarkan rating.

```sql
id                  bigint unsigned (PK)
interpretable_type  enum                      -- 'aspect', 'sub_aspect'
interpretable_id    bigint unsigned           -- ID dari aspect atau sub_aspect
interpretable_name  varchar                   -- Nama untuk reference
rating_value        tinyint                   -- 1, 2, 3, 4, 5
template_text       text                      -- Template interpretasi
tone                enum (nullable)           -- 'positive', 'neutral', 'negative'
category            enum (nullable)           -- 'strength', 'development', 'concern'
version             varchar (nullable)        -- Versi template
is_active           boolean (default: true)   -- Status aktif
created_at          timestamp
updated_at          timestamp

INDEX: interpretable_type, interpretable_id, rating_value, is_active
```

**Polymorphic Relation:** Bisa untuk Aspect atau SubAspect.

**Contoh:**
```
interpretable_type: 'sub_aspect'
interpretable_id: 1 (Kecerdasan Umum)
rating_value: 4
template_text: "Memiliki kemampuan intelektual yang baik..."
```

**Relasi:**
- morphTo: interpretable (Aspect atau SubAspect)

---

## OPERATIONAL TABLES

### 7. assessment_events

Event/kegiatan asesmen.

```sql
id                  bigint unsigned (PK)
institution_id      bigint unsigned (FK → institutions)
code                varchar (UNIQUE)          -- 'P3K-KEJAKSAAN-2025'
name                varchar                   -- 'Asesmen P3K Kejaksaan 2025'
description         text (nullable)
year                integer                   -- 2025
start_date          date
end_date            date
status              enum                      -- 'planning', 'active', 'completed', 'cancelled'
last_synced_at      timestamp (nullable)      -- Terakhir sync dari CI3
created_at          timestamp
updated_at          timestamp

INDEX: institution_id, code, status
UNIQUE: code
CASCADE DELETE: institution deleted → events deleted
```

**Relasi:**
- belongsTo: institutions
- hasMany: batches
- hasMany: position_formations
- hasMany: participants

---

### 8. batches

Gelombang/batch/lokasi pelaksanaan asesmen.

```sql
id                  bigint unsigned (PK)
event_id            bigint unsigned (FK → assessment_events)
code                varchar                   -- 'BATCH-1-MOJOKERTO'
name                varchar                   -- 'Batch 1 Mojokerto'
location            varchar (nullable)        -- 'Hotel X, Mojokerto'
batch_number        integer                   -- 1, 2, 3
start_date          date
end_date            date
created_at          timestamp
updated_at          timestamp

INDEX: event_id
UNIQUE: [event_id, code]
CASCADE DELETE: event deleted → batches deleted
```

**Relasi:**
- belongsTo: assessment_events
- hasMany: participants

---

### 9. position_formations

Formasi jabatan yang diasesmen.

```sql
id                  bigint unsigned (PK)
event_id            bigint unsigned (FK → assessment_events)
template_id         bigint unsigned (FK → assessment_templates)
code                varchar                   -- 'fisikawan_medis'
name                varchar                   -- 'Fisikawan Medis'
quota               integer (nullable)        -- Kuota formasi
created_at          timestamp
updated_at          timestamp

INDEX: event_id, template_id
UNIQUE: [event_id, code]
CASCADE DELETE: event/template deleted → positions deleted
```

**Konsep:** Setiap formasi jabatan dalam satu event bisa menggunakan template berbeda.

**Contoh:**
```
Event: P3K Kejaksaan 2025
├─ Auditor → template: supervisor_standard_v1 (Potensi 30%, Kompetensi 70%)
├─ Analis Kebijakan → template: staff_standard_v1 (Potensi 50%, Kompetensi 50%)
└─ Fisikawan Medis → template: professional_standard_v1 (Potensi 45%, Kompetensi 55%)
```

**Relasi:**
- belongsTo: assessment_events
- belongsTo: assessment_templates
- hasMany: participants

---

### 10. participants

Data peserta asesmen.

```sql
id                  bigint unsigned (PK)
event_id            bigint unsigned (FK → assessment_events)
batch_id            bigint unsigned (FK → batches, nullable)
position_formation_id bigint unsigned (FK → position_formations)
username            varchar (UNIQUE, nullable)
test_number         varchar (UNIQUE)          -- '03-5-2-18-001'
skb_number          varchar (nullable)
name                varchar                   -- Nama lengkap
email               varchar (nullable)
phone               varchar (nullable)
gender              varchar (nullable)        -- 'L', 'P'
photo_path          varchar (nullable)
assessment_date     date (nullable)
created_at          timestamp
updated_at          timestamp

INDEX: event_id, batch_id, position_formation_id, name
UNIQUE: username, test_number
CASCADE DELETE: event/position deleted → participants deleted
SET NULL: batch deleted → batch_id = NULL (participant tetap ada)
```

**Business Key:** `test_number` adalah identifier utama peserta.

**Relasi:**
- belongsTo: assessment_events
- belongsTo: batches
- belongsTo: position_formations
- hasMany: category_assessments
- hasMany: interpretations
- hasOne: final_assessment
- hasOne: psychological_test

---

## ASSESSMENT DATA TABLES

### 11. category_assessments

Nilai agregat per kategori (Potensi/Kompetensi) per peserta.

```sql
id                      bigint unsigned (PK)
participant_id          bigint unsigned (FK → participants)
event_id                bigint unsigned (FK → assessment_events)      -- Denormalisasi untuk performa
batch_id                bigint unsigned (FK → batches, nullable)      -- Denormalisasi untuk performa
position_formation_id   bigint unsigned (FK → position_formations, nullable) -- Denormalisasi
category_type_id        bigint unsigned (FK → category_types)
total_standard_rating   decimal(8,2)          -- Total rating standar
total_standard_score    decimal(8,2)          -- Total score standar
total_individual_rating decimal(8,2)          -- Total rating individu
total_individual_score  decimal(8,2)          -- Total score individu
gap_rating              decimal(8,2)          -- individual - standard (bisa negatif)
gap_score               decimal(8,2)          -- individual - standard (bisa negatif)
conclusion_code         varchar               -- 'DBS', 'MS', 'K', 'SK'
conclusion_text         varchar               -- 'Memenuhi Syarat'
created_at              timestamp
updated_at              timestamp

INDEX: category_type_id, conclusion_code
INDEX: [event_id, category_type_id]
INDEX: [batch_id, category_type_id]
INDEX: [position_formation_id, category_type_id]
UNIQUE: [participant_id, category_type_id]
CASCADE DELETE: participant/category deleted → assessments deleted
```

**Business Rule:** Setiap participant memiliki tepat 2 category_assessments (Potensi + Kompetensi).

**Relasi:**
- belongsTo: participants
- belongsTo: assessment_events
- belongsTo: batches
- belongsTo: position_formations
- belongsTo: category_types
- hasMany: aspect_assessments

---

### 12. aspect_assessments

Nilai per aspek per peserta dengan gap comparison.

```sql
id                      bigint unsigned (PK)
category_assessment_id  bigint unsigned (FK → category_assessments)
participant_id          bigint unsigned (FK → participants)           -- Denormalisasi
event_id                bigint unsigned (FK → assessment_events)      -- Denormalisasi
batch_id                bigint unsigned (FK → batches, nullable)      -- Denormalisasi
position_formation_id   bigint unsigned (FK → position_formations, nullable) -- Denormalisasi
aspect_id               bigint unsigned (FK → aspects)
standard_rating         decimal(5,2)          -- SNAPSHOT dari master
standard_score          decimal(8,2)          -- rating × weight_percentage
individual_rating       decimal(5,2)          -- Hasil asesmen (agregasi atau langsung)
individual_score        decimal(8,2)          -- rating × weight_percentage
gap_rating              decimal(8,2)          -- individual - standard
gap_score               decimal(8,2)          -- individual - standard
percentage_score        integer (nullable)    -- (rating/5) × 100 untuk spider chart
conclusion_code         varchar (nullable)
conclusion_text         varchar (nullable)
created_at              timestamp
updated_at              timestamp

INDEX: category_assessment_id, aspect_id
INDEX: [event_id, aspect_id]
INDEX: [batch_id, aspect_id]
INDEX: [position_formation_id, aspect_id]
INDEX: [participant_id, aspect_id]
CASCADE DELETE: category_assessment deleted → aspect_assessments deleted
```

**Snapshot Pattern:** `standard_rating` dicopy dari master `aspects.standard_rating` pada saat asesmen untuk menjaga integritas historis.

**Perhitungan:**
- **Potensi:** `individual_rating` = rata-rata dari sub_aspect_assessments
- **Kompetensi:** `individual_rating` = langsung dari API (tidak ada sub-aspek)

**Relasi:**
- belongsTo: category_assessments
- belongsTo: participants
- belongsTo: assessment_events
- belongsTo: batches
- belongsTo: position_formations
- belongsTo: aspects
- hasMany: sub_aspect_assessments

---

### 13. sub_aspect_assessments

Nilai detail per sub-aspek (hanya untuk Potensi).

```sql
id                      bigint unsigned (PK)
aspect_assessment_id    bigint unsigned (FK → aspect_assessments)
participant_id          bigint unsigned (FK → participants)           -- Denormalisasi
event_id                bigint unsigned (FK → assessment_events)      -- Denormalisasi
sub_aspect_id           bigint unsigned (FK → sub_aspects)
standard_rating         integer                -- SNAPSHOT dari master (1-5)
individual_rating       integer                -- Nilai aktual (1-5)
rating_label            varchar (nullable)     -- 'Kurang', 'Cukup', 'Baik', 'Baik Sekali', 'Istimewa'
created_at              timestamp
updated_at              timestamp

INDEX: aspect_assessment_id, sub_aspect_id
INDEX: [event_id, sub_aspect_id]
INDEX: [participant_id, sub_aspect_id]
CASCADE DELETE: aspect_assessment deleted → sub_aspect_assessments deleted
```

**Note:** Hanya ada untuk aspek Potensi. Aspek Kompetensi tidak memiliki sub-aspek.

**Snapshot Pattern:** `standard_rating` dicopy dari master `sub_aspects.standard_rating`.

**Relasi:**
- belongsTo: aspect_assessments
- belongsTo: participants
- belongsTo: assessment_events
- belongsTo: sub_aspects

---

### 14. interpretations

Interpretasi narasi hasil asesmen per kategori.

```sql
id                  bigint unsigned (PK)
participant_id      bigint unsigned (FK → participants)
event_id            bigint unsigned (FK → assessment_events)      -- Denormalisasi
category_type_id    bigint unsigned (FK → category_types)
interpretation_text text                      -- Narasi interpretasi
created_at          timestamp
updated_at          timestamp

INDEX: participant_id, category_type_id, event_id
CASCADE DELETE: participant/category deleted → interpretations deleted
```

**Business Rule:** 1 participant bisa memiliki 0-2 interpretations (untuk Potensi dan/atau Kompetensi).

**Relasi:**
- belongsTo: participants
- belongsTo: assessment_events
- belongsTo: category_types

---

## RESULT TABLES

### 15. final_assessments

Hasil akhir gabungan Potensi + Kompetensi dengan bobot.

```sql
id                          bigint unsigned (PK)
participant_id              bigint unsigned (FK → participants, UNIQUE)
event_id                    bigint unsigned (FK → assessment_events)   -- Denormalisasi
batch_id                    bigint unsigned (FK → batches, nullable)   -- Denormalisasi
position_formation_id       bigint unsigned (FK → position_formations, nullable) -- Denormalisasi
potensi_weight              integer               -- 40, 50 (%)
potensi_standard_score      decimal(8,2)
potensi_individual_score    decimal(8,2)
kompetensi_weight           integer               -- 60, 50 (%)
kompetensi_standard_score   decimal(8,2)
kompetensi_individual_score decimal(8,2)
total_standard_score        decimal(8,2)          -- Weighted total
total_individual_score      decimal(8,2)          -- Weighted total
achievement_percentage      decimal(5,2)          -- (individual/standard) × 100
conclusion_code             varchar               -- 'TMS', 'MMS', 'MS'
conclusion_text             varchar               -- 'Memenuhi Syarat'
created_at                  timestamp
updated_at                  timestamp

INDEX: conclusion_code, achievement_percentage
INDEX: [event_id, achievement_percentage]
INDEX: [batch_id, achievement_percentage]
INDEX: [position_formation_id, achievement_percentage]
UNIQUE: participant_id
CASCADE DELETE: participant deleted → final_assessment deleted
```

**Business Rule:** Setiap participant memiliki tepat 1 final_assessment.

**Perhitungan:**
```
total_standard_score = (potensi_standard × potensi_weight/100) + (kompetensi_standard × kompetensi_weight/100)
total_individual_score = (potensi_individual × potensi_weight/100) + (kompetensi_individual × kompetensi_weight/100)
achievement_percentage = (total_individual_score / total_standard_score) × 100
```

**Relasi:**
- belongsTo: participants
- belongsTo: assessment_events
- belongsTo: batches
- belongsTo: position_formations

---

### 16. psychological_tests

Data hasil tes psikologi (terpisah dari asesmen).

```sql
id                      bigint unsigned (PK)
participant_id          bigint unsigned (FK → participants)
event_id                bigint unsigned (FK → assessment_events)      -- Denormalisasi
no_test                 varchar (nullable)
username                varchar (nullable)
validitas               text (nullable)       -- Data validitas tes
internal                text (nullable)       -- Aspek internal
interpersonal           text (nullable)       -- Aspek interpersonal
kap_kerja               text (nullable)       -- Kapasitas kerja
klinik                  text (nullable)       -- Aspek klinis
kesimpulan              text (nullable)       -- Kesimpulan umum
psikogram               text (nullable)       -- Data psikogram
nilai_pq                decimal(8,2) (nullable) -- Nilai PQ
tingkat_stres           varchar (nullable)    -- Level stress
created_at              timestamp
updated_at              timestamp

INDEX: event_id, participant_id
```

**Note:** Struktur masih menggunakan TEXT untuk data kompleks dari sistem lama.

**Relasi:**
- belongsTo: participants
- belongsTo: assessment_events

---

## SYSTEM TABLE

### 17. users

Autentikasi user (simplified).

```sql
id                  bigint unsigned (PK)
name                varchar
email               varchar (UNIQUE)
email_verified_at   timestamp (nullable)
password            varchar
remember_token      varchar (nullable)
created_at          timestamp
updated_at          timestamp

INDEX: email
```

**Note:** Semua user memiliki akses level sama (no role-based access).

---

## DATABASE RELATIONSHIPS

### Master Layer
```
Institution (1) ──< (N) AssessmentEvent
AssessmentTemplate (1) ──< (N) CategoryType
AssessmentTemplate (1) ──< (N) Aspect
AssessmentTemplate (1) ──< (N) PositionFormation
CategoryType (1) ──< (N) Aspect
Aspect (1) ──< (0-N) SubAspect
```

### Operational Layer
```
AssessmentEvent (1) ──< (N) Batch
AssessmentEvent (1) ──< (N) PositionFormation
AssessmentEvent (1) ──< (N) Participant
Batch (1) ──< (N) Participant
PositionFormation (1) ──< (N) Participant
```

### Assessment Layer
```
Participant (1) ──< (2) CategoryAssessment
Participant (1) ──── (1) FinalAssessment
Participant (1) ──── (0-1) PsychologicalTest
Participant (1) ──< (0-2) Interpretation

CategoryAssessment (1) ──< (N) AspectAssessment
AspectAssessment (1) ──< (0-N) SubAspectAssessment
```

---

## DATA HIERARCHY

```
AssessmentTemplate
├── CategoryType (Potensi & Kompetensi)
│   └── Aspect (5-9 aspek per kategori)
│       └── SubAspect (0-5 sub per aspek, hanya Potensi)
└── PositionFormation

Institution
└── AssessmentEvent
    ├── Batch
    ├── PositionFormation
    └── Participant
        ├── CategoryAssessment (2: Potensi + Kompetensi)
        │   └── AspectAssessment (5-9 per kategori)
        │       └── SubAspectAssessment (0-5 per aspek)
        ├── FinalAssessment (1)
        ├── PsychologicalTest (0-1)
        └── Interpretation (0-2)
```

---

## KEY DESIGN PATTERNS

### 1. Template Pattern
Template mendefinisikan "BAGAIMANA menilai" (struktur, bobot, standar). Setiap position formation menggunakan template tertentu. Template bersifat reusable.

### 2. Snapshot Pattern
`standard_rating` dari master (aspects, sub_aspects) di-copy ke tabel assessment pada saat asesmen. Ini menjaga integritas historis jika standar berubah di masa depan.

### 3. Denormalization for Performance
Field `event_id`, `batch_id`, `position_formation_id` ditambahkan ke tabel assessment untuk query analytics cepat tanpa JOIN. Trade-off: +7MB storage per event untuk 1000x faster queries.

### 4. Dual Foreign Key (Aspects)
Aspect memiliki 2 FK:
- `template_id` - Definisi per template (bobot berbeda)
- `category_type_id` - Grouping Potensi/Kompetensi

Ini memungkinkan aspect yang sama (misal: "Kecerdasan") memiliki bobot berbeda di template berbeda.

---

## INDEXING STRATEGY

### Standard Indexes
- Primary Keys: Auto-increment di semua tabel
- Foreign Keys: Auto-indexed oleh Laravel
- Business Keys: UNIQUE index pada `code` fields
- Search Fields: Index pada `participants.name`

### Performance Indexes (Composite)
18 composite indexes untuk analytics cepat:
- `[event_id, category_type_id]` - Filter kategori per event
- `[event_id, aspect_id]` - Filter aspek per event
- `[batch_id, aspect_id]` - Filter aspek per batch
- `[position_formation_id, aspect_id]` - Filter aspek per formasi
- `[participant_id, aspect_id]` - Data peserta per aspek
- Dan lainnya...

**Performance:** Query analytics 3ms untuk 26,000 records (2000 participants × 13 aspects).

---

## UNIQUE IDENTIFIERS

| Entity | Business Key | Format Example |
|--------|--------------|----------------|
| Institution | `code` | 'kejaksaan', 'bnn' |
| Template | `code` | 'staff_standard_v1' |
| Event | `code` | 'P3K-KEJAKSAAN-2025' |
| Participant | `test_number` | '03-5-2-18-001' |

---

## SCALE & PERFORMANCE

**Target Scale:**
- 2000+ participants per event
- 5-10 events per year
- ~840,000 records per year

**Performance:**
- Analytics query: < 3ms (dengan composite indexes)
- Storage per event: ~7MB additional (denormalization)
- Query optimization: 1000x faster vs normalized design
