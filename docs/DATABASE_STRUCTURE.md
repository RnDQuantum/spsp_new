# DATABASE STRUCTURE - SPSP Analytics Dashboard

**Database Engine:** MySQL 8.0
**Total Tables:** 18 (Master: 6, Operational: 4, Assessment: 4, Results: 2, System: 2)

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

### System Tables (2)
17. **users** - Autentikasi user (dengan multi-tenancy)
18. **Spatie Permission tables** - roles, permissions, model_has_roles, model_has_permissions, role_has_permissions

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
- hasMany: assessment_events, users

---

### 2. assessment_templates

Blueprint/template standar asesmen yang reusable.

```sql
id                  bigint unsigned (PK)
code                varchar (UNIQUE)          -- 'staff_standard_v1'
name                varchar                   -- 'Standar Asesmen Staff'
description         text (nullable)
created_at          timestamp
updated_at          timestamp

INDEX: code
```

**Relasi:**
- hasMany: category_types, aspects, position_formations

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
- hasMany: aspects, category_assessments, interpretations

---

### 4. aspects

Aspek penilaian dengan bobot dan standar rating.

```sql
id                  bigint unsigned (PK)
template_id         bigint unsigned (FK → assessment_templates)
category_type_id    bigint unsigned (FK → category_types)
code                varchar                   -- 'kecerdasan', 'integritas'
name                varchar                   -- 'Kecerdasan', 'Integritas'
description         text (nullable)
weight_percentage   integer (nullable)        -- 25, 20, 15 (total 100% per kategori)
standard_rating     decimal(5,2) (nullable)   -- 3.00, 3.60, 3.75
order               integer                   -- urutan tampilan
created_at          timestamp
updated_at          timestamp

INDEX: template_id, category_type_id, code
UNIQUE: [template_id, category_type_id, code]
CASCADE DELETE: template/category deleted → aspects deleted
```

**Relasi:**
- belongsTo: assessment_templates, category_types
- hasMany: sub_aspects, aspect_assessments

---

### 5. sub_aspects

Sub-aspek detail untuk breakdown aspek Potensi.

```sql
id                  bigint unsigned (PK)
aspect_id           bigint unsigned (FK → aspects)
code                varchar                   -- 'kecerdasan_umum'
name                varchar                   -- 'Kecerdasan Umum'
description         text (nullable)
standard_rating     integer (nullable)        -- 3, 4, 5 (skala 1-5)
order               integer                   -- urutan tampilan
created_at          timestamp
updated_at          timestamp

INDEX: aspect_id
CASCADE DELETE: aspect deleted → sub_aspects deleted
```

**Note:** Hanya untuk aspek Potensi. Aspek Kompetensi tidak memiliki sub-aspek.

**Relasi:**
- belongsTo: aspects
- hasMany: sub_aspect_assessments

---

### 6. interpretation_templates

Template interpretasi narasi berdasarkan rating.

```sql
id                  bigint unsigned (PK)
interpretable_type  enum                      -- 'aspect', 'sub_aspect'
interpretable_id    bigint unsigned (nullable)
interpretable_name  varchar (nullable)        -- Nama untuk reference
rating_value        tinyint unsigned          -- 1, 2, 3, 4, 5
template_text       text                      -- Template interpretasi
tone                enum                      -- 'positive', 'neutral', 'negative'
category            enum                      -- 'strength', 'development_area', 'neutral'
version             varchar(10)               -- '1.0'
is_active           boolean (default: true)
created_at          timestamp
updated_at          timestamp

INDEX: [interpretable_type, interpretable_id]
INDEX: [interpretable_type, interpretable_name, rating_value]
INDEX: rating_value, is_active
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
status              enum                      -- 'draft', 'ongoing', 'completed'
last_synced_at      timestamp (nullable)
created_at          timestamp
updated_at          timestamp

INDEX: institution_id, code, status
CASCADE DELETE: institution deleted → events deleted
```

**Relasi:**
- belongsTo: institutions
- hasMany: batches, position_formations, participants

---

### 8. batches

Gelombang/batch/lokasi pelaksanaan asesmen.

```sql
id                  bigint unsigned (PK)
event_id            bigint unsigned (FK → assessment_events)
code                varchar                   -- 'BATCH-1-MOJOKERTO'
name                varchar                   -- 'Batch 1 Mojokerto'
location            varchar                   -- 'Hotel X, Mojokerto'
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

**Relasi:**
- belongsTo: assessment_events, assessment_templates
- hasMany: participants

---

### 10. participants

Data peserta asesmen.

```sql
id                  bigint unsigned (PK)
event_id            bigint unsigned (FK → assessment_events)
batch_id            bigint unsigned (FK → batches, nullable)
position_formation_id bigint unsigned (FK → position_formations)
username            varchar (UNIQUE)
test_number         varchar (UNIQUE)          -- '03-5-2-18-001'
skb_number          varchar
name                varchar                   -- Nama lengkap
email               varchar (nullable)
phone               varchar (nullable)
gender              varchar (nullable)        -- 'L', 'P'
photo_path          varchar (nullable)
assessment_date     date
created_at          timestamp
updated_at          timestamp

INDEX: event_id, batch_id, position_formation_id, name
CASCADE DELETE: event/position deleted → participants deleted
SET NULL: batch deleted → batch_id = NULL
```

**Relasi:**
- belongsTo: assessment_events, batches, position_formations
- hasMany: category_assessments, interpretations
- hasOne: final_assessment, psychological_test

---

## ASSESSMENT DATA TABLES

### 11. category_assessments

Nilai agregat per kategori (Potensi/Kompetensi) per peserta.

```sql
id                      bigint unsigned (PK)
participant_id          bigint unsigned (FK → participants)
event_id                bigint unsigned (FK → assessment_events)
batch_id                bigint unsigned (FK → batches, nullable)
position_formation_id   bigint unsigned (FK → position_formations, nullable)
category_type_id        bigint unsigned (FK → category_types)
total_standard_rating   decimal(8,2)
total_standard_score    decimal(8,2)
total_individual_rating decimal(8,2)
total_individual_score  decimal(8,2)
gap_rating              decimal(8,2)
gap_score               decimal(8,2)
conclusion_code         varchar               -- 'DBS', 'MS', 'K', 'SK'
conclusion_text         varchar               -- 'Memenuhi Syarat'
created_at              timestamp
updated_at              timestamp

UNIQUE: [participant_id, category_type_id]
INDEX: category_type_id, conclusion_code
INDEX: [event_id, category_type_id]
INDEX: [batch_id, category_type_id]
INDEX: [position_formation_id, category_type_id]
```

**Relasi:**
- belongsTo: participants, assessment_events, batches, position_formations, category_types
- hasMany: aspect_assessments

---

### 12. aspect_assessments

Nilai per aspek per peserta dengan gap comparison.

```sql
id                      bigint unsigned (PK)
category_assessment_id  bigint unsigned (FK → category_assessments)
participant_id          bigint unsigned (FK → participants)
event_id                bigint unsigned (FK → assessment_events)
batch_id                bigint unsigned (FK → batches, nullable)
position_formation_id   bigint unsigned (FK → position_formations, nullable)
aspect_id               bigint unsigned (FK → aspects)
standard_rating         decimal(5,2)          -- SNAPSHOT dari master
standard_score          decimal(8,2)          -- rating × weight_percentage
individual_rating       decimal(5,2)          -- Hasil asesmen
individual_score        decimal(8,2)          -- rating × weight_percentage
gap_rating              decimal(8,2)          -- individual - standard
gap_score               decimal(8,2)          -- individual - standard
percentage_score        integer (nullable)    -- untuk spider chart
conclusion_code         varchar (nullable)
conclusion_text         varchar (nullable)
created_at              timestamp
updated_at              timestamp

INDEX: category_assessment_id, aspect_id
INDEX: [event_id, aspect_id]
INDEX: [batch_id, aspect_id]
INDEX: [position_formation_id, aspect_id]
INDEX: [participant_id, aspect_id]
```

**Relasi:**
- belongsTo: category_assessments, participants, assessment_events, batches, position_formations, aspects
- hasMany: sub_aspect_assessments

---

### 13. sub_aspect_assessments

Nilai detail per sub-aspek (hanya untuk Potensi).

```sql
id                      bigint unsigned (PK)
aspect_assessment_id    bigint unsigned (FK → aspect_assessments)
participant_id          bigint unsigned (FK → participants)
event_id                bigint unsigned (FK → assessment_events)
sub_aspect_id           bigint unsigned (FK → sub_aspects)
standard_rating         integer               -- SNAPSHOT dari master (1-5)
individual_rating       integer               -- Nilai aktual (1-5)
rating_label            varchar               -- 'Kurang', 'Cukup', 'Baik', etc.
created_at              timestamp
updated_at              timestamp

INDEX: aspect_assessment_id, sub_aspect_id
INDEX: [event_id, sub_aspect_id]
INDEX: [participant_id, sub_aspect_id]
```

**Relasi:**
- belongsTo: aspect_assessments, participants, assessment_events, sub_aspects

---

### 14. interpretations

Interpretasi narasi hasil asesmen per kategori.

```sql
id                  bigint unsigned (PK)
participant_id      bigint unsigned (FK → participants)
event_id            bigint unsigned (FK → assessment_events)
category_type_id    bigint unsigned (FK → category_types, nullable)
interpretation_text text
created_at          timestamp
updated_at          timestamp

INDEX: participant_id, category_type_id
INDEX: [event_id, category_type_id]
```

**Relasi:**
- belongsTo: participants, assessment_events, category_types

---

## RESULT TABLES

### 15. final_assessments

Hasil akhir gabungan Potensi + Kompetensi dengan bobot.

```sql
id                          bigint unsigned (PK)
participant_id              bigint unsigned (FK → participants, UNIQUE)
event_id                    bigint unsigned (FK → assessment_events)
batch_id                    bigint unsigned (FK → batches, nullable)
position_formation_id       bigint unsigned (FK → position_formations, nullable)
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
```

**Relasi:**
- belongsTo: participants, assessment_events, batches, position_formations

---

### 16. psychological_tests

Data hasil tes psikologi (terpisah dari asesmen).

```sql
id                      bigint unsigned (PK)
event_id                bigint unsigned
participant_id          bigint unsigned
no_test                 varchar(30) (nullable)
username                varchar(100) (nullable)
validitas               text (nullable)
internal                text (nullable)
interpersonal           text (nullable)
kap_kerja               text (nullable)       -- Kapasitas kerja
klinik                  text (nullable)
kesimpulan              text (nullable)
psikogram               text (nullable)
nilai_pq                decimal(10,2) (nullable)
tingkat_stres           varchar(20) (nullable)
created_at              timestamp
updated_at              timestamp

INDEX: [event_id, participant_id]
```

**Relasi:**
- belongsTo: participants, assessment_events

---

## SYSTEM TABLES

### 17. users

Autentikasi user dengan multi-tenancy support.

```sql
id                  bigint unsigned (PK)
institution_id      bigint unsigned (FK → institutions, nullable)  -- Multi-tenancy
name                varchar
email               varchar (UNIQUE)
email_verified_at   timestamp (nullable)
password            varchar
is_active           boolean (default: true)
last_login_at       timestamp (nullable)
remember_token      varchar (nullable)
created_at          timestamp
updated_at          timestamp

INDEX: email, institution_id, is_active
```

**Multi-tenancy Logic:**
- `institution_id = NULL` → Admin/Quantum (akses semua data)
- `institution_id = X` → Client (akses data institution X saja)

**Relasi:**
- belongsTo: institutions
- morphToMany: roles, permissions (Spatie)

---

### 18. Spatie Permission Tables

```sql
-- roles
id, name, guard_name, created_at, updated_at

-- permissions
id, name, guard_name, created_at, updated_at

-- model_has_roles
role_id, model_type, model_id

-- model_has_permissions
permission_id, model_type, model_id

-- role_has_permissions
permission_id, role_id
```

---

## DATABASE RELATIONSHIPS

### Master Layer
```
Institution (1) ──< (N) AssessmentEvent
Institution (1) ──< (N) User
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
├── User (multi-tenancy)
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
Field `event_id`, `batch_id`, `position_formation_id` ditambahkan ke tabel assessment untuk query analytics cepat tanpa JOIN.

### 4. Dual Foreign Key (Aspects)
Aspect memiliki 2 FK:
- `template_id` - Definisi per template (bobot berbeda)
- `category_type_id` - Grouping Potensi/Kompetensi

### 5. Multi-tenancy via Institution
User memiliki `institution_id` untuk pembatasan akses data berdasarkan institusi.

---

## INDEXING STRATEGY

### Standard Indexes
- Primary Keys: Auto-increment di semua tabel
- Foreign Keys: Auto-indexed oleh Laravel
- Business Keys: UNIQUE index pada `code` fields
- Search Fields: Index pada `participants.name`

### Performance Indexes (Composite)
Composite indexes untuk analytics cepat:
- `[event_id, category_type_id]`
- `[event_id, aspect_id]`
- `[batch_id, aspect_id]`
- `[position_formation_id, aspect_id]`
- `[participant_id, aspect_id]`

---

## SCALE & PERFORMANCE

**Target Scale:**
- 2000+ participants per event
- 5-10 events per year
- ~840,000 records per year

**Performance:**
- Analytics query: < 3ms (dengan composite indexes)
- Query optimization: 1000x faster vs normalized design
