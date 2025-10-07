# 🗄️ DATABASE DESIGN - SPSP Analytics Dashboard

**Project:** Dashboard Analytics Asesmen
**Database:** MySQL/MariaDB
**Total Tables:** 16
**Last Updated:** 2025-10-06

---

## 📚 RELATED DOCUMENTATION

- 👉 **[PROJECT_DOCUMENTATION.md](./PROJECT_DOCUMENTATION.md)** - High-level project overview
- 👉 **[ASSESSMENT_CALCULATION_FLOW.md](./ASSESSMENT_CALCULATION_FLOW.md)** - Calculation logic & formulas
- 👉 **[DATABASE_QC_PROGRESS.md](./DATABASE_QC_PROGRESS.md)** - QC progress tracking

---

## 📊 DATABASE OVERVIEW

### **Table Categories:**

- **MASTER TABLES (5):** institutions, assessment_templates, category_types, aspects, sub_aspects
- **EVENT & EXECUTION (3):** assessment_events, batches, position_formations
- **PARTICIPANT DATA (1):** participants
- **ASSESSMENT SCORES (3):** category_assessments, aspect_assessments, sub_aspect_assessments
- **FINAL RESULTS (3):** final_assessments, psychological_tests, interpretations
- **AUTH (1):** users

---

## 🔑 KEY DESIGN CONCEPTS

### **1. "HOW vs WHO" Paradigm**

```
Template = "HOW to Assess"
├─ Defines assessment structure (categories, aspects, sub-aspects)
├─ Defines weights & standard ratings
└─ Reusable blueprint

Event = "WHO to Assess"
├─ Uses specific template
├─ Belongs to specific institution
├─ Has specific participants, batches, positions
└─ Execution instance
```

### **2. Snapshot Pattern**

**Purpose:** Preserve historical data integrity

```
Master Tables (Current/Blueprint):
- aspects.standard_rating = 3.20 (current value)
- sub_aspects.standard_rating = 3 (current value)

Assessment Tables (Historical Snapshot):
- aspect_assessments.standard_rating = 3.20 (snapshot at assessment time)
- sub_aspect_assessments.standard_rating = 3 (snapshot at assessment time)

Benefits:
✅ Accurate gap comparison at assessment time
✅ Historical data integrity preserved
✅ Template standards can evolve over time
✅ Audit trail for compliance
✅ Performance optimization (no recalculation needed)
```

### **3. Dynamic Template Structure**

Templates can have **different structures**:
- Different number of aspects
- Different aspect weights
- Different standard ratings
- Different sub-aspects

**Database Support:**
- `aspects.template_id` (DUAL FK for multi-template)
- `aspects.weight_percentage` (per template dapat berbeda)
- UNIQUE constraint: `(template_id, category_type_id, code)`

---

## 📋 DETAILED TABLE STRUCTURES

### **MASTER TABLES (5)**

#### **1. institutions**

```sql
├─ id (PK, bigint unsigned)
├─ code (string, UNIQUE) - 'kejaksaan', 'kemenkeu'
├─ name (string)
├─ logo_path (string, nullable)
├─ api_key (string, UNIQUE) - untuk validasi API
└─ timestamps

INDEX: code
```

**Purpose:** Store institution/organization data
**Relationship:** 1 institution → N assessment_events

---

#### **2. assessment_templates**

```sql
├─ id (PK, bigint unsigned)
├─ code (string, UNIQUE) - 'p3k_standard_2025'
├─ name (string)
├─ description (text, nullable)
└─ timestamps

INDEX: code
```

**Purpose:** Define assessment structure blueprints
**Relationship:** 1 template → N category_types, N aspects, N assessment_events

---

#### **3. category_types** (Potensi / Kompetensi)

```sql
├─ id (PK, bigint unsigned)
├─ template_id (FK → assessment_templates)
├─ code (string) - 'potensi', 'kompetensi'
├─ name (string)
├─ weight_percentage (integer) - 40, 60
├─ order (integer)
└─ timestamps

INDEX: template_id
UNIQUE: template_id + code
CASCADE DELETE: template deleted → categories deleted
```

**Purpose:** Define main assessment categories per template
**Example:** Potensi 40%, Kompetensi 60%

---

#### **4. aspects**

```sql
├─ id (PK, bigint unsigned)
├─ template_id (FK → assessment_templates) ← ADDED 2025-10-06
├─ category_type_id (FK → category_types)
├─ code (string) - 'kecerdasan', 'integritas'
├─ name (string)
├─ weight_percentage (integer) - 30, 20, 12, 11
├─ standard_rating (decimal 5,2, nullable) - 3.50, 3.20, 3.75
├─ order (integer)
└─ timestamps

INDEX: template_id, category_type_id, code
UNIQUE: template_id + category_type_id + code
CASCADE DELETE: template/category deleted → aspects deleted
```

**Purpose:** Define assessment aspects with weights & standards
**DUAL Relationship:**
- `template_id`: Direct FK (for multi-template support)
- `category_type_id`: Grouping FK (Potensi/Kompetensi)

**Snapshot Pattern:** `standard_rating` will be copied to `aspect_assessments`

**Example:**
```
Template P3K: Kecerdasan (30% of Potensi)
Template CPNS: Kecerdasan (50% of Potensi) ← Different weight!
```

---

#### **5. sub_aspects**

```sql
├─ id (PK, bigint unsigned)
├─ aspect_id (FK → aspects)
├─ code (string) - 'kecerdasan_umum'
├─ name (string)
├─ standard_rating (integer, nullable) - 3, 4
├─ description (text, nullable)
├─ order (integer)
└─ timestamps

INDEX: aspect_id
CASCADE DELETE: aspect deleted → sub_aspects deleted
```

**Purpose:** Define detail breakdown of aspects (for Potensi only)
**Note:** Kompetensi aspects do NOT have sub-aspects (empty relation)

**Snapshot Pattern:** `standard_rating` will be copied to `sub_aspect_assessments`

---

### **EVENT & EXECUTION (3)**

#### **6. assessment_events**

```sql
├─ id (PK, bigint unsigned)
├─ institution_id (FK → institutions)
├─ template_id (FK → assessment_templates)
├─ code (string, UNIQUE) - 'P3K-KEJAKSAAN-2025'
├─ name (string)
├─ description (text, nullable) ← ADDED 2025-10-06
├─ year (integer)
├─ start_date (date)
├─ end_date (date)
├─ status (enum) - 'draft', 'ongoing', 'completed'
├─ last_synced_at (timestamp, nullable)
└─ timestamps

INDEX: institution_id, code, status
UNIQUE: code
CASCADE DELETE: institution/template deleted → events deleted
```

**Purpose:** Store assessment event/execution data
**Key Concept:** Event CHOOSES which template to use

---

#### **7. batches** (Gelombang/Lokasi)

```sql
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
CASCADE DELETE: event deleted → batches deleted
```

**Purpose:** Group participants by batch/location/wave
**Design:** Event-specific (not template-specific)

---

#### **8. position_formations** (Formasi Jabatan)

```sql
├─ id (PK, bigint unsigned)
├─ event_id (FK → assessment_events)
├─ code (string) - 'fisikawan_medis'
├─ name (string)
├─ quota (integer, nullable)
└─ timestamps

INDEX: event_id
UNIQUE: event_id + code
CASCADE DELETE: event deleted → positions deleted
```

**Purpose:** Define job positions for assessment
**Design Decision:** Event-specific (not template-specific)

**Rationale:**
- Different events need different positions
- Quota specific per event
- Template defines "HOW", Event defines "WHO"

---

### **PARTICIPANT DATA (1)**

#### **9. participants**

```sql
├─ id (PK, bigint unsigned)
├─ event_id (FK → assessment_events)
├─ batch_id (FK → batches, nullable) ← SET NULL on delete
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
INDEX: event_id, batch_id, position_formation_id, name
CASCADE DELETE: event/position deleted → participants deleted
SET NULL: batch deleted → batch_id = NULL (participant remains)
```

**Purpose:** Store participant/peserta data
**Business Key:** `test_number` (unique identifier)

---

### **ASSESSMENT SCORES (3)**

#### **10. category_assessments** (Nilai per Kategori)

```sql
├─ id (PK, bigint unsigned)
├─ participant_id (FK → participants)
├─ category_type_id (FK → category_types)
├─ total_standard_rating (decimal 8,2)
├─ total_standard_score (decimal 8,2)
├─ total_individual_rating (decimal 8,2)
├─ total_individual_score (decimal 8,2)
├─ gap_rating (decimal 8,2) ← can be negative
├─ gap_score (decimal 8,2) ← can be negative
├─ conclusion_code (string) - 'DBS', 'MS', 'K', 'SK'
├─ conclusion_text (string) - 'DI BAWAH STANDARD', 'SANGAT KOMPETEN'
└─ timestamps

UNIQUE INDEX: participant_id + category_type_id
INDEX: category_type_id, conclusion_code
CASCADE DELETE: participant/category deleted → assessments deleted
```

**Purpose:** Aggregated scores per category (Potensi/Kompetensi)
**Business Rule:** Each participant has exactly 2 category assessments

---

#### **11. aspect_assessments** (Nilai per Aspek)

```sql
├─ id (PK, bigint unsigned)
├─ category_assessment_id (FK → category_assessments)
├─ aspect_id (FK → aspects)
├─ standard_rating (decimal 5,2) ← SNAPSHOT from master
├─ standard_score (decimal 8,2) ← rating × weight
├─ individual_rating (decimal 5,2) ← aggregated OR direct
├─ individual_score (decimal 8,2) ← rating × weight
├─ gap_rating (decimal 8,2) ← individual - standard
├─ gap_score (decimal 8,2) ← individual score - standard score
├─ percentage_score (integer) ← for spider chart
├─ conclusion_code (string) - 'below_standard', 'meets_standard', 'exceeds_standard'
├─ conclusion_text (string)
├─ description_text (text, nullable) ← for Kompetensi details
└─ timestamps

INDEX: category_assessment_id, aspect_id
CASCADE DELETE: category_assessment deleted → aspect_assessments deleted
```

**Purpose:** Scores per aspect with gap comparison
**Calculation:**
- **Potensi:** `individual_rating` = AVG(sub_aspect_assessments)
- **Kompetensi:** `individual_rating` = Direct from API

---

#### **12. sub_aspect_assessments** (Nilai per Sub-Aspek)

```sql
├─ id (PK, bigint unsigned)
├─ aspect_assessment_id (FK → aspect_assessments)
├─ sub_aspect_id (FK → sub_aspects)
├─ standard_rating (integer) ← SNAPSHOT from master
├─ individual_rating (integer) ← actual score from CI3
├─ rating_label (string) - 'Cukup', 'Baik', 'Baik Sekali'
└─ timestamps

INDEX: aspect_assessment_id, sub_aspect_id
CASCADE DELETE: aspect_assessment deleted → sub_aspect_assessments deleted
```

**Purpose:** Raw assessment data (detail for Potensi aspects)
**Note:** Only exists for Potensi aspects (Kompetensi has no sub-aspects)

---

### **FINAL RESULTS (3)**

#### **13. final_assessments**

```sql
├─ id (PK, bigint unsigned)
├─ participant_id (FK → participants, UNIQUE)
├─ potensi_weight (integer) - 40
├─ potensi_standard_score (decimal 8,2)
├─ potensi_individual_score (decimal 8,2)
├─ kompetensi_weight (integer) - 60
├─ kompetensi_standard_score (decimal 8,2)
├─ kompetensi_individual_score (decimal 8,2)
├─ total_standard_score (decimal 8,2) ← weighted sum
├─ total_individual_score (decimal 8,2) ← weighted sum
├─ achievement_percentage (decimal 5,2) ← (individual/standard) × 100
├─ final_conclusion_code (string) - 'TMS', 'MMS', 'MS'
├─ final_conclusion_text (string)
└─ timestamps

UNIQUE INDEX: participant_id
INDEX: final_conclusion_code, achievement_percentage
CASCADE DELETE: participant deleted → final_assessment deleted
```

**Purpose:** Final weighted scores & conclusion
**Calculation:** (Potensi × 40%) + (Kompetensi × 60%)

---

#### **14. psychological_tests**

```sql
├─ id (PK, bigint unsigned)
├─ participant_id (FK → participants, UNIQUE)
├─ raw_score (decimal 5,2)
├─ iq_score (integer, nullable)
├─ validity_status (string)
├─ internal_status (string)
├─ interpersonal_status (string)
├─ work_capacity_status (string)
├─ clinical_status (string)
├─ conclusion_code (string) - 'MS', 'TMS'
├─ conclusion_text (string)
├─ notes (text, nullable)
└─ timestamps

UNIQUE INDEX: participant_id
INDEX: conclusion_code
CASCADE DELETE: participant deleted → psych_test deleted
```

**Purpose:** Psychological test results (separate from assessment scores)

---

#### **15. interpretations**

```sql
├─ id (PK, bigint unsigned)
├─ participant_id (FK → participants)
├─ category_type_id (FK → category_types, nullable)
├─ interpretation_text (text)
└─ timestamps

INDEX: participant_id, category_type_id
CASCADE DELETE: participant/category deleted → interpretations deleted
```

**Purpose:** Narrative interpretations for reports
**Note:** 1 participant can have 0-2 interpretations (Potensi, Kompetensi, or general)

---

### **AUTH (1)**

#### **16. users** (Laravel default, simplified)

```sql
├─ id (PK, bigint unsigned)
├─ name (string)
├─ email (string, UNIQUE)
├─ password (string)
├─ remember_token (string, nullable)
└─ timestamps

INDEX: email
```

**Purpose:** Simple authentication (no roles)
**Note:** All users have same access level

---

## 🔗 DATABASE RELATIONSHIPS

### **Master Layer:**

```
Institution (1) ──< (N) AssessmentEvent
AssessmentTemplate (1) ──< (N) AssessmentEvent
AssessmentTemplate (1) ──< (N) CategoryType
AssessmentTemplate (1) ──< (N) Aspect ← DUAL FK (direct relation)
CategoryType (1) ──< (N) Aspect ← DUAL FK (grouping)
Aspect (1) ──< (N) SubAspect (0-N, optional)
```

### **Execution Layer:**

```
AssessmentEvent (1) ──< (N) Batch
AssessmentEvent (1) ──< (N) PositionFormation
AssessmentEvent (1) ──< (N) Participant
```

### **Assessment Layer:**

```
Participant (1) ──< (N) CategoryAssessment (always 2: Potensi + Kompetensi)
Participant (1) ──── (1) FinalAssessment
Participant (1) ──── (1) PsychologicalTest
Participant (1) ──< (N) Interpretation (0-2 records)

CategoryAssessment (1) ──< (N) AspectAssessment
AspectAssessment (1) ──< (N) SubAspectAssessment (0-N, depends on aspect)
```

### **IMPORTANT: Aspects DUAL Relationship**

```
Aspect has TWO foreign keys:
├─ template_id → assessment_templates (defines weight per template)
└─ category_type_id → category_types (grouping Potensi/Kompetensi)

This allows:
- Template P3K: Kecerdasan (30% of Potensi)
- Template CPNS: Kecerdasan (50% of Potensi) ← Same aspect, different weight!
```

---

## 🔑 KEY UNIQUE IDENTIFIERS

| Entity | Unique Identifier | Format |
|--------|------------------|--------|
| **Institution** | `code` | 'kejaksaan', 'kemenkeu' |
| **Template** | `code` | 'p3k_standard_2025' |
| **Event** | `code` | 'P3K-KEJAKSAAN-2025' |
| **Participant** | `test_number` | '03-5-2-18-001' |

---

## 📈 INDEXES STRATEGY

### **Performance Optimization:**

1. ✅ **Primary Keys:** All tables have auto-increment PK
2. ✅ **Foreign Keys:** Auto-indexed by Laravel
3. ✅ **Business Keys:** Unique indexes on code fields
4. ✅ **Search Fields:** Index on participants.name
5. ✅ **Filter Fields:** Index on status, conclusion_code
6. ✅ **Analytics Fields:** Index on achievement_percentage

---

## 🎯 DESIGN VALIDATION

### **Supports Application Goals:**

| Goal | Database Support | Status |
|------|------------------|--------|
| Dashboard analytics per instansi/event/batch/formasi | ✅ All FK relationships + indexes | PASS |
| Laporan individual per peserta | ✅ Complete assessment hierarchy | PASS |
| Multi-template support | ✅ Template as independent master + DUAL FK | PASS |
| Manual sync dari CI3 | ✅ Upsert-friendly (UNIQUE on codes) | PASS |
| Historical data integrity | ✅ Snapshot pattern implemented | PASS |
| Spider chart visualization | ✅ percentage_score + proper grouping | PASS |
| Comparison analytics | ✅ FK + indexes on batch_id, position_formation_id | PASS |
| Read-only analytics | ✅ No blocking constraints | PASS |

---

## 📝 RELATED DOCUMENTATION

- [PROJECT_DOCUMENTATION.md](./PROJECT_DOCUMENTATION.md) - Main project overview
- [ASSESSMENT_CALCULATION_FLOW.md](./ASSESSMENT_CALCULATION_FLOW.md) - Calculation logic & formulas
- [DATABASE_QC_PROGRESS.md](./DATABASE_QC_PROGRESS.md) - QC tracking & progress
- [API_SPECIFICATION.md](./API_SPECIFICATION.md) - API contract with CI3

---

**Version:** 1.0
**Status:** ✅ Production-Ready Structure
**Last Reviewed:** 2025-10-06
