# Database Structure - SPSP Assessment System

> **Version**: 1.0
> **Last Updated**: 2025-12-01
> **Purpose**: Complete reference for database schema, relationships, and data flow

---

## Table of Contents

1. [Overview](#overview)
2. [Core Assessment Tables](#core-assessment-tables)
3. [Entity Relationships](#entity-relationships)
4. [Data Hierarchy](#data-hierarchy)
5. [Sample Data Examples](#sample-data-examples)
6. [Key Insights for Testing](#key-insights-for-testing)

---

## Overview

The SPSP system uses a **hierarchical assessment structure** with the following key concepts:

- **Template-Based**: Each position uses an assessment template (e.g., Staff, Supervisor, Manager)
- **Dual Category**: All assessments have **Potensi** (Potential) and **Kompetensi** (Competency)
- **Aspect-Based**: Categories contain multiple aspects (e.g., Kecerdasan, Integritas)
- **Sub-Aspect Structure**: Some aspects have sub-aspects (Potensi), some don't (Kompetensi)
- **Weighted Scores**: Category weights + Aspect weights → Final score

---

## Core Assessment Tables

### 1. Master Data Tables (Template & Structure)

#### `assessment_templates`
Defines assessment structures (e.g., Staff, Supervisor, Manager).

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| code | varchar | Unique code (e.g., `staff_standard_v1`) |
| name | varchar | Display name |
| description | text | Template purpose |

**Sample Data**:
```
id=1, code='staff_standard_v1', name='Standar Asesmen Staff'
id=2, code='supervisor_standard_v1', name='Standar Asesmen Supervisor'
id=3, code='manager_standard_v1', name='Standar Asesmen Manager'
```

---

#### `category_types`
Two categories per template: **Potensi** and **Kompetensi**.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| template_id | bigint | FK to assessment_templates |
| code | varchar | `potensi` or `kompetensi` |
| name | varchar | Display name |
| weight_percentage | int | Category weight (sum = 100%) |
| order | int | Display order |

**Sample Data** (template_id=1, Staff Standard):
```
id=1, template_id=1, code='potensi', weight_percentage=50
id=2, template_id=1, code='kompetensi', weight_percentage=50
```

**Key Point**: Category weights vary by template!
- Staff: 50/50
- Supervisor: 30/70 (more Kompetensi)
- Manager: 40/60

---

#### `aspects`
Individual assessment dimensions within a category.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| template_id | bigint | FK to assessment_templates |
| category_type_id | bigint | FK to category_types |
| code | varchar | Unique code (e.g., `kecerdasan`) |
| name | varchar | Display name |
| weight_percentage | int | Aspect weight within category |
| standard_rating | decimal | Default standard rating (1-5) |
| order | int | Display order |

**Sample Data** (Potensi category):
```
id=1, code='kecerdasan', name='Kecerdasan', weight_percentage=25, standard_rating=3.00
id=2, code='cara_kerja', name='Cara Kerja', weight_percentage=20, standard_rating=3.60
id=3, code='potensi_kerja', name='Potensi Kerja', weight_percentage=20, standard_rating=3.00
```

**Sample Data** (Kompetensi category):
```
id=6, code='integritas', name='Integritas', weight_percentage=15, standard_rating=3.00
id=7, code='kerjasama', name='Kerjasama', weight_percentage=14, standard_rating=3.00
```

**CRITICAL DIFFERENCE**:
- **Potensi aspects** have sub-aspects → rating calculated from sub-aspects
- **Kompetensi aspects** NO sub-aspects → use direct `standard_rating`

---

#### `sub_aspects`
Sub-dimensions for Potensi aspects ONLY.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| aspect_id | bigint | FK to aspects |
| code | varchar | Unique code |
| name | varchar | Display name |
| standard_rating | int | Default rating (1-5) |
| order | int | Display order |

**Sample Data** (aspect_id=1, Kecerdasan):
```
id=1, aspect_id=1, code='kecerdasan_umum', name='Kecerdasan Umum', standard_rating=3
id=2, aspect_id=1, code='daya_tangkap', name='Daya Tangkap', standard_rating=3
id=3, aspect_id=1, code='daya_analisa', name='Daya Analisa', standard_rating=3
id=4, aspect_id=1, code='kemampuan_logika', name='Kemampuan Logika', standard_rating=3
```

**Aspect Rating Calculation** (DATA-DRIVEN):
```
Kecerdasan rating = AVG(3, 3, 3, 3) = 3.00
```

---

### 2. Event & Participant Tables

#### `institutions`
Organizations running assessments.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| code | varchar | Unique code (e.g., `kemenkes`) |
| name | varchar | Institution name |

---

#### `assessment_events`
Assessment events for institutions.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| institution_id | bigint | FK to institutions |
| code | varchar | Unique code |
| name | varchar | Event name |
| year | int | Assessment year |
| status | enum | `draft`, `ongoing`, `completed`, `cancelled` |

**Sample Data**:
```
id=1, code='P3K-KEMENKES-2025', name='Seleksi P3K Kementerian Kesehatan 2025', status='ongoing'
```

---

#### `batches`
Assessment batches within an event.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| event_id | bigint | FK to assessment_events |
| code | varchar | Batch code |
| name | varchar | Batch name |
| batch_number | int | Sequence number |

**Sample Data**:
```
id=1, event_id=1, code='BATCH-1-BANDUNG', batch_number=1
id=2, event_id=1, code='BATCH-2-YOGYAKARTA', batch_number=2
```

---

#### `position_formations`
Positions available in an event (each position has a template).

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| event_id | bigint | FK to assessment_events |
| template_id | bigint | FK to assessment_templates |
| code | varchar | Position code |
| name | varchar | Position name |
| quota | int | Available slots |

**Sample Data**:
```
id=1, code='dokter_umum', name='Dokter Umum', template_id=4 (professional_standard_v1), quota=50
id=2, code='perawat', name='Perawat', template_id=1 (staff_standard_v1), quota=100
id=3, code='apoteker', name='Apoteker', template_id=2 (supervisor_standard_v1), quota=50
```

**CRITICAL**: Different positions in the same event can use **different templates**!

---

#### `participants`
Individuals being assessed.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| event_id | bigint | FK to assessment_events |
| batch_id | bigint | FK to batches (nullable) |
| position_formation_id | bigint | FK to position_formations |
| username | varchar | Unique username |
| test_number | varchar | Unique test number |
| name | varchar | Participant name |
| email | varchar | Email address |
| gender | varchar | L/P |

**Sample Data**:
```
id=1, event_id=1, batch_id=2, position_formation_id=3, name='BRENNAN SMITHAM, S.Pd'
id=2, event_id=1, batch_id=1, position_formation_id=2, name='CHRIS ROGAHN, S.Kom'
```

---

### 3. Assessment Result Tables

#### `category_assessments`
Aggregated scores at the category level (Potensi/Kompetensi).

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| participant_id | bigint | FK to participants |
| category_type_id | bigint | FK to category_types |
| total_standard_rating | decimal | Sum of standard ratings |
| total_standard_score | decimal | Weighted standard score |
| total_individual_rating | decimal | Sum of individual ratings |
| total_individual_score | decimal | Weighted individual score |
| gap_rating | decimal | Individual - Standard (rating) |
| gap_score | decimal | Individual - Standard (score) |
| conclusion_code | varchar | `above_standard`, `meets_standard`, `below_standard` |

**Calculation Example**:
```sql
-- Potensi category with 3 aspects (weights: 25, 20, 20)
total_standard_rating = 3.0 + 3.6 + 3.0 = 9.6
total_standard_score = (3.0*25) + (3.6*20) + (3.0*20) = 167.0
```

---

#### `aspect_assessments`
Individual aspect scores for each participant.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| participant_id | bigint | FK to participants |
| aspect_id | bigint | FK to aspects |
| standard_rating | decimal | Standard rating (1-5) |
| standard_score | decimal | standard_rating * weight |
| individual_rating | decimal | Participant's rating (1-5) |
| individual_score | decimal | individual_rating * weight |
| gap_rating | decimal | Individual - Standard |
| gap_score | decimal | Individual - Standard |
| percentage_score | int | (individual/standard) * 100 |
| conclusion_code | varchar | Conclusion code |

**Sample Data**:
```
id=1062, participant_id=85, aspect_id=13,
standard_rating=4.00, standard_score=120.00,
individual_rating=2.50, individual_score=75.00,
gap_rating=-1.50, gap_score=-45.00, percentage_score=50
```

---

#### `sub_aspect_assessments`
Sub-aspect ratings (for Potensi aspects only).

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| aspect_assessment_id | bigint | FK to aspect_assessments |
| participant_id | bigint | FK to participants |
| sub_aspect_id | bigint | FK to sub_aspects |
| standard_rating | int | Standard rating (1-5) |
| individual_rating | int | Participant's rating (1-5) |

**Aspect Rating Calculation**:
```
Kecerdasan individual_rating = AVG(sub_aspect_1, sub_aspect_2, ..., sub_aspect_n)
```

---

#### `final_assessments`
Combined Potensi + Kompetensi final scores.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| participant_id | bigint | FK to participants (UNIQUE) |
| potensi_weight | int | Potensi category weight % |
| kompetensi_weight | int | Kompetensi category weight % |
| potensi_standard_score | decimal | Potensi weighted score |
| kompetensi_standard_score | decimal | Kompetensi weighted score |
| total_standard_score | decimal | Sum of both |
| total_individual_score | decimal | Sum of both |
| achievement_percentage | decimal | (individual/standard) * 100 |
| conclusion_code | varchar | Final conclusion |

---

## Entity Relationships

### Visual Hierarchy

```
Institution
  └─> AssessmentEvent (1 event per institution)
       ├─> Batch (multiple batches per event)
       ├─> PositionFormation (multiple positions per event)
       │    └─> AssessmentTemplate (1 template per position)
       │         └─> CategoryType (2 categories: Potensi + Kompetensi)
       │              └─> Aspect (multiple aspects per category)
       │                   └─> SubAspect (ONLY for Potensi aspects)
       └─> Participant (multiple participants per event)
            ├─> belongs to: Batch
            ├─> belongs to: PositionFormation
            ├─> has: CategoryAssessment (2 per participant: Potensi + Kompetensi)
            │    └─> has: AspectAssessment (multiple per category)
            │         └─> has: SubAspectAssessment (ONLY for Potensi aspects)
            └─> has: FinalAssessment (1 per participant)
```

---

## Data Hierarchy

### Template → Category → Aspect → Sub-Aspect

**Example: Staff Standard V1**

```
Template: staff_standard_v1
  ├── Category: Potensi (50%)
  │   ├── Aspect: Kecerdasan (25%) - standard_rating: 3.0
  │   │   ├── SubAspect: Kecerdasan Umum (rating: 3)
  │   │   ├── SubAspect: Daya Tangkap (rating: 3)
  │   │   ├── SubAspect: Daya Analisa (rating: 3)
  │   │   └── SubAspect: Kemampuan Logika (rating: 3)
  │   ├── Aspect: Cara Kerja (20%) - calculated from sub-aspects
  │   └── Aspect: Potensi Kerja (20%) - calculated from sub-aspects
  │
  └── Category: Kompetensi (50%)
      ├── Aspect: Integritas (15%) - standard_rating: 3.0 (DIRECT)
      ├── Aspect: Kerjasama (14%) - standard_rating: 3.0 (DIRECT)
      ├── Aspect: Komunikasi (14%) - standard_rating: 3.0 (DIRECT)
      └── ... (7 total kompetensi aspects)
```

---

## Sample Data Examples

### Complete Assessment Flow for 1 Participant

**Participant**: BRENNAN SMITHAM, S.Pd (id=1)
- Event: P3K-KEMENKES-2025
- Position: Apoteker (Supervisor template, Potensi 30%, Kompetensi 70%)
- Batch: BATCH-2-YOGYAKARTA

**Step 1: Sub-Aspect Assessments** (Potensi aspects ONLY)
```
Kecerdasan sub-aspects:
  - Kecerdasan Umum: standard=3, individual=4
  - Daya Tangkap: standard=3, individual=3
  - Daya Analisa: standard=3, individual=4
  - Kemampuan Logika: standard=3, individual=3
```

**Step 2: Aspect Assessment** (Calculated)
```
Kecerdasan aspect:
  - standard_rating = AVG(3,3,3,3) = 3.0
  - individual_rating = AVG(4,3,4,3) = 3.5
  - weight = 25%
  - standard_score = 3.0 * 25 = 75.0
  - individual_score = 3.5 * 25 = 87.5
```

**Step 3: Category Assessment** (Aggregated)
```
Potensi category:
  - total_standard_score = SUM(all aspect scores in Potensi)
  - total_individual_score = SUM(all aspect scores in Potensi)
  - gap_score = individual - standard
```

**Step 4: Final Assessment** (Weighted)
```
Final:
  - potensi_score * 30% + kompetensi_score * 70% = total
  - achievement_percentage = (total_individual / total_standard) * 100
```

---

## Key Insights for Testing

### 1. Data-Driven Aspect Rating

**Potensi aspects** (WITH sub-aspects):
```php
// ✅ CORRECT: Calculate from sub-aspects
$rating = $subAspects->avg('individual_rating');
```

**Kompetensi aspects** (NO sub-aspects):
```php
// ✅ CORRECT: Use direct rating from aspects table
$rating = $aspect->standard_rating;
```

### 2. Template Variability

Different positions in same event can have:
- Different category weights (e.g., 50/50 vs 30/70)
- Different aspect weights
- Different standard ratings

**Test Implication**: Always filter by `position_formation_id` to get correct template!

### 3. Active/Inactive Filtering

- DynamicStandardService can mark aspects/sub-aspects as inactive
- Inactive aspects: `weight = 0`
- Inactive sub-aspects: excluded from average calculation

### 4. 3-Layer Priority System

When getting standards:
1. **Session Adjustment** (temporary, highest priority)
2. **Custom Standard** (persistent, saved in DB)
3. **Quantum Default** (from aspects/sub_aspects table, fallback)

### 5. Tolerance Application

RankingService applies tolerance to standards:
```php
$adjustedStandard = $originalStandard * (1 - $tolerancePercentage/100);
// Example: 10% tolerance on 100.0 → 90.0
```

### 6. Ranking Sort Order

**ALWAYS**:
1. Sort by `individual_score` DESC (highest first)
2. Tiebreaker: `participant_name` ASC (alphabetical)

### 7. Database Indexes

**Performance-critical queries**:
- `aspect_assessments`: indexed on `(event_id, aspect_id)`, `(participant_id, aspect_id)`
- `category_assessments`: indexed on `(event_id, category_type_id)`, `(participant_id, category_type_id)`
- `final_assessments`: indexed on `(event_id, achievement_percentage)`

---

## Factory Usage for Testing

### Required Factories

When creating test data, you'll need:

1. **Institution** → `Institution::factory()`
2. **AssessmentTemplate** → `AssessmentTemplate::factory()` (OR use existing seeded data)
3. **CategoryType** → `CategoryType::factory()`
4. **Aspect** → `Aspect::factory()`
5. **SubAspect** → `SubAspect::factory()` (ONLY for Potensi)
6. **AssessmentEvent** → `AssessmentEvent::factory()`
7. **Batch** → `Batch::factory()`
8. **PositionFormation** → `PositionFormation::factory()`
9. **Participant** → `Participant::factory()`
10. **AspectAssessment** → `AspectAssessment::factory()`
11. **SubAspectAssessment** → `SubAspectAssessment::factory()`
12. **CategoryAssessment** → `CategoryAssessment::factory()`
13. **FinalAssessment** → `FinalAssessment::factory()`

### Recommended Approach for RankingService Tests

**Option A: Use Seeded Data** (FASTEST)
```php
// Run seeder first: php artisan db:seed
$event = AssessmentEvent::first();
$position = PositionFormation::first();
// Test with real seeded participants
```

**Option B: Minimal Factory Setup** (FLEXIBLE)
```php
// Create minimal template structure
$template = $this->createTemplateWithCategories();
$event = AssessmentEvent::factory()->create();
$position = PositionFormation::factory()->create([
    'event_id' => $event->id,
    'template_id' => $template->id,
]);
// Create 3-5 participants with assessments
```

---

**Version**: 1.0
**Last Updated**: 2025-12-01
**Next Review**: After RankingService tests complete
**Maintainer**: Development Team
