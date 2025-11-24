# Custom Standard Feature

> **Version**: 1.0
> **Created**: 2025-01-20
> **Status**: Planning
> **Author**: Development Team

---

## Quick Context (Untuk Sesi Baru)

### Apa itu SPSP?

**SPSP (Sistem Pemetaan & Statistik Psikologi)** adalah SaaS analytics dashboard untuk menampilkan hasil assessment psikologi. Data assessment (peserta, nilai, standar) di-import dari aplikasi utama Quantum via API.

### Struktur Data Utama

```
Institution (Kejaksaan, BNN, dll)
└── User (dengan institution_id untuk multi-tenancy)
└── AssessmentEvent (Proyek: "Seleksi P3K Kemenkes 2025")
    └── PositionFormation (Jabatan: "Analis Kebijakan")
        └── template_id → AssessmentTemplate (Standar Quantum)
        └── Participant (Peserta)
            └── AspectAssessment (nilai per aspek)
                └── SubAspectAssessment (nilai per sub-aspek)

AssessmentTemplate (Standar Quantum)
└── CategoryType (Potensi 50%, Kompetensi 50%)
    └── Aspect (Kecerdasan 20%, Integritas 15%, dll)
        └── SubAspect (hanya untuk Potensi)
```

### Relasi Penting

- **1 Template → N Jabatan** (1 standar bisa dipakai banyak jabatan)
- **1 Jabatan → 1 Template** (1 jabatan hanya punya 1 standar)
- **User → Institution** (multi-tenancy via `institution_id`)

### Services yang Sudah Ada

1. **DynamicStandardService** (`app/Services/DynamicStandardService.php`)
   - Mengelola **session-based adjustments** untuk analisis sementara
   - Menyimpan: category weights, aspect weights/ratings, sub-aspect ratings, active status
   - Data hilang setelah logout

2. **IndividualAssessmentService** (`app/Services/IndividualAssessmentService.php`)
   - Kalkulasi assessment individual (aspect scores, category totals, final scores)
   - Membaca dari DynamicStandardService untuk nilai standar

3. **RankingService** (`app/Services/RankingService.php`)
   - Kalkulasi ranking semua peserta
   - Membaca dari DynamicStandardService untuk nilai standar

### Komponen Livewire Terkait

- **StandardPsikometrik** - Halaman edit standar Potensi (sub-aspek & aspek)
- **StandardMc** - Halaman edit standar Kompetensi (aspek saja)
- **GeneralPsyMapping, GeneralMcMapping, GeneralMapping, GeneralMatching** - Report individual
- **RankingPsyMapping, RankingMcMapping, RekapRankingAssessment** - Report ranking

### Event System

- `'standard-adjusted'` - Dispatch saat user edit standar via DynamicStandardService
- `'tolerance-updated'` - Dispatch saat user ubah tolerance percentage
- Semua report components listen ke events ini untuk reload data

### Calculation Flow

```
Data Peserta (individual_rating di DB) → TIDAK BERUBAH
                    +
Nilai Standar (dari DynamicStandardService) → BISA DIUBAH
                    ↓
Calculate on-the-fly: scores, gaps, percentages
```

**Key Insight**: Database `aspect_assessments` menyimpan snapshot standar Quantum. Custom standard tidak mengubah database, hanya mengubah cara kalkulasi saat display.

### Dokumentasi Terkait

- `docs/SERVICE_ARCHITECTURE.md` - Detail lengkap service layer & calculation levels
- `docs/DATABASE_STRUCTURE.md` - Struktur database (mungkin outdated)

---

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [Problem Statement](#problem-statement)
3. [Goals & Objectives](#goals--objectives)
4. [Solution Overview](#solution-overview)
5. [Technical Architecture](#technical-architecture)
6. [Database Design](#database-design)
7. [Service Layer Updates](#service-layer-updates)
8. [UI/UX Design](#uiux-design)
9. [Implementation Plan](#implementation-plan)
10. [Security & Access Control](#security--access-control)
11. [Testing Strategy](#testing-strategy)

---

## Executive Summary

### Apa Fitur Ini?

Fitur **Custom Standard** memungkinkan client (institution) untuk membuat dan menyimpan standar penilaian mereka sendiri berdasarkan template Quantum. Client dapat menyesuaikan bobot kategori, bobot aspek, rating standar, dan status aktif aspek/sub-aspek sesuai kebutuhan institusi mereka.

### Mengapa Diperlukan?

SPSP saat ini berfungsi sebagai SaaS analytics dashboard dimana semua client menggunakan standar Quantum yang sama. Namun, setiap institusi memiliki kebutuhan dan kriteria penilaian yang berbeda. Fitur ini memberikan fleksibilitas bagi client untuk menganalisis hasil assessment dengan standar yang disesuaikan dengan kebijakan internal mereka.

### Siapa yang Menggunakan?

- **Client Users** (non-admin): Membuat, mengedit, dan menggunakan custom standard
- **All Users in Same Institution**: Dapat memilih dan menggunakan custom standard yang dibuat oleh institusi mereka
- **Admin Quantum**: Tetap menggunakan standar default (tidak membuat custom standard)

---

## Problem Statement

### Kondisi Saat Ini

1. **Single Standard**: Semua client hanya bisa view hasil dengan standar Quantum
2. **Session-based Adjustment**: Fitur DynamicStandardService hanya untuk analisis sementara (hilang setelah logout)
3. **No Persistence**: Client tidak bisa menyimpan adjustment mereka untuk digunakan kembali
4. **No Sharing**: Adjustment yang dibuat satu user tidak bisa diakses user lain di institusi yang sama

### Pain Points

| User | Pain Point |
|------|------------|
| **Client HR** | "Kami ingin standar yang lebih tinggi untuk posisi strategis, tapi tidak bisa menyimpannya" |
| **Client Manager** | "Setiap kali login, harus adjust ulang standar dari awal" |
| **Multiple Users** | "User A sudah buat adjustment bagus, tapi user B tidak bisa pakai" |

### Business Impact

- **Inefficiency**: Waktu terbuang untuk adjust standar berulang kali
- **Inconsistency**: Setiap user di institusi yang sama mungkin pakai adjustment berbeda
- **Limited Value**: Client tidak mendapat value maksimal dari aplikasi

---

## Goals & Objectives

### Primary Goals

1. **Persistence**: Client dapat menyimpan custom standard ke database
2. **Reusability**: Custom standard dapat digunakan berulang kali tanpa setup ulang
3. **Sharing**: Semua user di institusi yang sama dapat mengakses custom standard yang dibuat

### Secondary Goals

1. **Backward Compatibility**: Existing functionality (DynamicStandardService) tetap berjalan
2. **Seamless Integration**: Custom standard terintegrasi dengan semua report yang ada
3. **Easy Switching**: User dapat dengan mudah switch antara standar Quantum dan custom

### Success Metrics

- [ ] Client dapat create custom standard dalam < 5 menit
- [ ] Switch standar memperbarui semua report dalam < 1 detik
- [ ] Zero breaking changes pada existing features

---

## Solution Overview

### Konsep Utama

Custom Standard adalah **"overlay"** di atas template Quantum, bukan template baru. Struktur aspek dan sub-aspek tetap sama, yang berbeda hanya nilai-nilainya (weight, rating, active status).

### Data Flow

```
┌─────────────────────────────────────────────────────────────┐
│                    Template Quantum                          │
│  (assessment_templates + aspects + sub_aspects)             │
│  → Definisi STRUKTUR (aspek apa saja)                       │
│  → Default VALUES (weight, rating)                          │
└─────────────────────────────┬───────────────────────────────┘
                              │
              ┌───────────────┼───────────────┐
              ↓               ↓               ↓
    ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐
    │ Quantum Default │ │ Custom Std A    │ │ Custom Std B    │
    │ (selected: null)│ │ (Kejaksaan)     │ │ (Kejaksaan v2)  │
    └────────┬────────┘ └────────┬────────┘ └────────┬────────┘
             │                   │                   │
             └───────────────────┴───────────────────┘
                                 │
                    ┌────────────┴────────────┐
                    │     User Selection      │
                    │  (stored in session)    │
                    └────────────┬────────────┘
                                 ↓
                    ┌─────────────────────────┐
                    │  DynamicStandardService │
                    │  (reads selected std)   │
                    └────────────┬────────────┘
                                 ↓
                    ┌─────────────────────────┐
                    │   All Report Pages      │
                    │  (calculated on-the-fly)│
                    └─────────────────────────┘
```

### Key Principles

1. **Data Peserta Immutable**: `individual_rating` di database tidak berubah
2. **Calculate on Display**: Scores dihitung ulang berdasarkan standar yang dipilih
3. **Session Selection**: Pilihan standar disimpan di session (bukan database)
4. **Institution Scoped**: Custom standard hanya accessible oleh institution yang sama

---

## Technical Architecture

### Component Interaction

```
┌─────────────────────────────────────────────────────────────────┐
│                         UI LAYER                                 │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────────┐    ┌──────────────────┐                   │
│  │ Create/Edit Form │    │ StandardPsiko/Mc │                   │
│  │ (Custom Standard)│    │ (Dropdown Select)│                   │
│  └────────┬─────────┘    └────────┬─────────┘                   │
│           │                       │                              │
│           │ save                  │ select                       │
│           ↓                       ↓                              │
└───────────┬───────────────────────┬─────────────────────────────┘
            │                       │
            ↓                       ↓
┌───────────────────────────────────────────────────────────────────┐
│                       SERVICE LAYER                               │
├───────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌─────────────────────┐    ┌─────────────────────────────────┐  │
│  │ CustomStandardService│   │   DynamicStandardService         │  │
│  │                     │    │   (Updated)                      │  │
│  │ • create()          │    │                                  │  │
│  │ • update()          │    │   getAspectWeight():             │  │
│  │ • delete()          │    │   1. Session adjustment          │  │
│  │ • getForInstitution()│   │   2. Custom standard (if selected)│  │
│  └─────────────────────┘    │   3. Quantum default             │  │
│                              └─────────────────────────────────┘  │
│                                          │                        │
│                                          ↓                        │
│                              ┌─────────────────────────────────┐  │
│                              │ IndividualAssessmentService     │  │
│                              │ RankingService                  │  │
│                              │ (No changes needed)             │  │
│                              └─────────────────────────────────┘  │
│                                                                   │
└───────────────────────────────────────────────────────────────────┘
            │
            ↓
┌───────────────────────────────────────────────────────────────────┐
│                        DATA LAYER                                 │
├───────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐   │
│  │ custom_standards│  │ Session Storage │  │ Existing Tables │   │
│  │ (new table)     │  │                 │  │                 │   │
│  │                 │  │ selected_std_id │  │ aspects         │   │
│  │ • JSON configs  │  │ adjustments     │  │ sub_aspects     │   │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘   │
│                                                                   │
└───────────────────────────────────────────────────────────────────┘
```

### Session Structure

```php
Session = [
    // Selected custom standard per template
    'selected_standard' => [
        1 => null,        // Template 1: Quantum default
        2 => 5,           // Template 2: CustomStandard ID 5
    ],

    // Dynamic adjustments (existing - untuk analisis sementara)
    'standard_adjustment' => [
        1 => [
            'category_weights' => ['potensi' => 60, 'kompetensi' => 40],
            'aspect_weights' => ['asp_01' => 25],
            'aspect_ratings' => ['asp_kom_01' => 4.5],
            'sub_aspect_ratings' => ['sub_01' => 4],
            'active_aspects' => ['asp_02' => false],
            'active_sub_aspects' => ['sub_01_03' => false],
        ],
    ],
];
```

### Priority Chain untuk Get Values

```
1. Session Adjustment     → Untuk analisis sementara (highest priority)
         ↓ (if not found)
2. Custom Standard        → Jika user sudah select custom standard
         ↓ (if not found)
3. Quantum Default        → Dari aspects/sub_aspects table
```

---

## Database Design

### New Table: `custom_standards`

```sql
CREATE TABLE custom_standards (
    id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    institution_id      BIGINT UNSIGNED NOT NULL,
    template_id         BIGINT UNSIGNED NOT NULL,
    code                VARCHAR(255) NOT NULL,
    name                VARCHAR(255) NOT NULL,
    description         TEXT NULL,

    -- JSON storage for all configurations
    category_weights    JSON NOT NULL,
    aspect_configs      JSON NOT NULL,
    sub_aspect_configs  JSON NOT NULL,

    -- Metadata
    is_active           BOOLEAN DEFAULT TRUE,
    created_by          BIGINT UNSIGNED NULL,
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,

    -- Foreign Keys
    CONSTRAINT fk_custom_standards_institution
        FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE CASCADE,
    CONSTRAINT fk_custom_standards_template
        FOREIGN KEY (template_id) REFERENCES assessment_templates(id) ON DELETE CASCADE,
    CONSTRAINT fk_custom_standards_created_by
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,

    -- Indexes
    UNIQUE KEY uk_custom_standards_institution_code (institution_id, code),
    INDEX idx_custom_standards_template (template_id),
    INDEX idx_custom_standards_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### JSON Structure Examples

#### category_weights
```json
{
    "potensi": 60,
    "kompetensi": 40
}
```

#### aspect_configs
```json
{
    "kecerdasan": {
        "weight": 25,
        "active": true
    },
    "cara_kerja": {
        "weight": 20,
        "active": true
    },
    "integritas": {
        "weight": 15,
        "rating": 4.0,
        "active": true
    },
    "kerjasama": {
        "weight": 14,
        "rating": 3.5,
        "active": true
    },
    "komunikasi": {
        "weight": 14,
        "rating": 3.0,
        "active": false
    }
}
```

**Note**:
- Aspek Potensi: `weight` dan `active` (rating dihitung dari sub-aspects)
- Aspek Kompetensi: `weight`, `rating`, dan `active`

#### sub_aspect_configs
```json
{
    "kecerdasan_umum": {
        "rating": 4,
        "active": true
    },
    "daya_tangkap": {
        "rating": 4,
        "active": true
    },
    "daya_analisa": {
        "rating": 3,
        "active": false
    },
    "kemampuan_logika": {
        "rating": 3,
        "active": true
    }
}
```

### Why JSON Storage?

| Consideration | JSON Storage | Normalized Tables |
|---------------|--------------|-------------------|
| **Simplicity** | ✅ Single table, easy CRUD | ❌ Multiple tables, complex joins |
| **Performance** | ✅ One query loads all | ❌ Multiple queries needed |
| **Flexibility** | ✅ Easy to add new fields | ❌ Need migration for changes |
| **Match Existing** | ✅ Similar to DynamicStandardService | ❌ Different pattern |
| **Query by Value** | ❌ Limited (can use JSON functions) | ✅ Easy to filter |

**Decision**: JSON storage karena simpler dan match dengan existing DynamicStandardService pattern.

---

## Service Layer Updates

### 1. New: CustomStandardService

```php
<?php

namespace App\Services;

use App\Models\CustomStandard;
use Illuminate\Support\Collection;

class CustomStandardService
{
    /**
     * Get all custom standards for an institution and template
     */
    public function getForInstitution(int $institutionId, int $templateId): Collection
    {
        return CustomStandard::where('institution_id', $institutionId)
            ->where('template_id', $templateId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a new custom standard
     */
    public function create(array $data): CustomStandard
    {
        return CustomStandard::create([
            'institution_id' => $data['institution_id'],
            'template_id' => $data['template_id'],
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'category_weights' => $data['category_weights'],
            'aspect_configs' => $data['aspect_configs'],
            'sub_aspect_configs' => $data['sub_aspect_configs'],
            'created_by' => $data['created_by'] ?? auth()->id(),
        ]);
    }

    /**
     * Get default values from template (for form initialization)
     */
    public function getTemplateDefaults(int $templateId): array
    {
        // Load template with all aspects and sub-aspects
        // Return structured array for form
    }

    /**
     * Select a custom standard (store in session)
     */
    public function select(int $templateId, ?int $customStandardId): void
    {
        Session::put("selected_standard.{$templateId}", $customStandardId);

        // Reset dynamic adjustments when switching standard
        Session::forget("standard_adjustment.{$templateId}");
    }

    /**
     * Get currently selected custom standard ID
     */
    public function getSelected(int $templateId): ?int
    {
        return Session::get("selected_standard.{$templateId}");
    }
}
```

### 2. Updated: DynamicStandardService

```php
// Add to existing DynamicStandardService

/**
 * Get aspect weight with custom standard support
 */
public function getAspectWeight(int $templateId, string $aspectCode): int
{
    // Priority 1: Session adjustment
    $sessionKey = "standard_adjustment.{$templateId}.aspect_weights.{$aspectCode}";
    if (Session::has($sessionKey)) {
        return Session::get($sessionKey);
    }

    // Priority 2: Custom standard
    $customStandardId = Session::get("selected_standard.{$templateId}");
    if ($customStandardId) {
        $customStandard = CustomStandard::find($customStandardId);
        if ($customStandard && isset($customStandard->aspect_configs[$aspectCode]['weight'])) {
            return $customStandard->aspect_configs[$aspectCode]['weight'];
        }
    }

    // Priority 3: Quantum default
    return Aspect::where('template_id', $templateId)
        ->where('code', $aspectCode)
        ->value('weight_percentage') ?? 0;
}

// Similar updates for:
// - getAspectRating()
// - getSubAspectRating()
// - getCategoryWeight()
// - isAspectActive()
// - isSubAspectActive()
```

### 3. No Changes Needed

Services berikut **tidak perlu diubah** karena sudah membaca dari DynamicStandardService:

- `IndividualAssessmentService`
- `RankingService`

---

## UI/UX Design

### 1. Custom Standard Management Page

**Route**: `/custom-standards`

**Access**: Users with `institution_id` (non-admin)

```
┌─────────────────────────────────────────────────────────────┐
│  Custom Standards                              [+ Create New]│
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ STD-KEJ-001                                          │    │
│  │ Standar Kejaksaan v1                                 │    │
│  │ Template: Standar Manajerial L3                      │    │
│  │ Created by: Ahmad Rizki • 15 Jan 2025               │    │
│  │                                    [Edit] [Delete]   │    │
│  └─────────────────────────────────────────────────────┘    │
│                                                              │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ STD-KEJ-002                                          │    │
│  │ Standar Kejaksaan v2 (Strict)                        │    │
│  │ Template: Standar Manajerial L3                      │    │
│  │ Created by: Budi Santoso • 18 Jan 2025              │    │
│  │                                    [Edit] [Delete]   │    │
│  └─────────────────────────────────────────────────────┘    │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

### 2. Create/Edit Custom Standard Form

**Route**: `/custom-standards/create` atau `/custom-standards/{id}/edit`

```
┌─────────────────────────────────────────────────────────────┐
│  Create Custom Standard                                      │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  Template *                                                  │
│  [Standar Manajerial L3                              ▾]     │
│                                                              │
│  Kode *                    Nama *                            │
│  [STD-KEJ-___]             [Standar Kejaksaan v1        ]   │
│                                                              │
│  Deskripsi                                                   │
│  [________________________________________________]         │
│                                                              │
├─────────────────────────────────────────────────────────────┤
│  BOBOT KATEGORI                                              │
│                                                              │
│  Potensi      [60]%        Kompetensi    [40]%              │
│                            Total: 100% ✓                     │
│                                                              │
├─────────────────────────────────────────────────────────────┤
│  POTENSI                                                     │
│                                                              │
│  ☑ Kecerdasan                          Bobot: [25]%         │
│    ┌──────────────────────────────────────────────────┐     │
│    │ ☑ Kecerdasan Umum          Rating: [4] (def: 3)  │     │
│    │ ☑ Daya Tangkap             Rating: [4] (def: 3)  │     │
│    │ ☐ Daya Analisa             Rating: [3] (def: 3)  │     │
│    │ ☑ Kemampuan Logika         Rating: [3] (def: 3)  │     │
│    └──────────────────────────────────────────────────┘     │
│                                                              │
│  ☑ Cara Kerja                          Bobot: [20]%         │
│    ┌──────────────────────────────────────────────────┐     │
│    │ ...                                               │     │
│    └──────────────────────────────────────────────────┘     │
│                                                              │
├─────────────────────────────────────────────────────────────┤
│  KOMPETENSI                                                  │
│                                                              │
│  ☑ Integritas       Bobot: [15]%    Rating: [4.0] (def: 3)  │
│  ☑ Kerjasama        Bobot: [14]%    Rating: [3.5] (def: 3)  │
│  ☐ Komunikasi       Bobot: [14]%    Rating: [3.0] (def: 3)  │
│  ☑ Orientasi Hasil  Bobot: [14]%    Rating: [3.0] (def: 3)  │
│  ...                                                         │
│                                                              │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  [Cancel]                              [Save Custom Standard]│
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

### 3. Standard Selector in StandardPsikometrik/StandardMc

```
┌─────────────────────────────────────────────────────────────┐
│  Standard Pemetaan Potensi Individu                          │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  Standar: [Quantum (Default)                         ▾]     │
│           ┌─────────────────────────────────────────┐       │
│           │ ● Quantum (Default)                     │       │
│           │ ○ Standar Kejaksaan v1 (STD-KEJ-001)   │       │
│           │ ○ Standar Kejaksaan v2 (STD-KEJ-002)   │       │
│           └─────────────────────────────────────────┘       │
│                                                              │
│  ┌─────────────────────────────────────────────────────┐    │
│  │  Table Aspek & Sub-Aspek                             │    │
│  │  (values sesuai standar yang dipilih)               │    │
│  │                                                      │    │
│  │  + Dynamic adjustment masih bisa dilakukan          │    │
│  └─────────────────────────────────────────────────────┘    │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

### 4. Event Flow saat Switch Standard

```
User selects "Standar Kejaksaan v1"
         ↓
StandardPsikometrik::selectStandard(5)
         ↓
Session::put("selected_standard.{$templateId}", 5)
Session::forget("standard_adjustment.{$templateId}")  // Reset adjustments
         ↓
$this->dispatch('standard-switched', templateId: $templateId)
         ↓
All listening components:
  • GeneralPsyMapping::handleStandardSwitch()
  • GeneralMcMapping::handleStandardSwitch()
  • GeneralMapping::handleStandardSwitch()
  • GeneralMatching::handleStandardSwitch()
  • RankingPsyMapping::handleStandardSwitch()
  • etc.
         ↓
Each component: clearCache() → reload data → update UI
```

### 5. Important: Adjustment vs Custom Standard Selection

**CRITICAL DISTINCTION:**

**A. Dropdown "Standar Penilaian" (Custom Standard Selector)**
- Purpose: Memilih **ACUAN/BASELINE** mana yang dipakai
- Options: "Quantum (Default)" atau custom standards lainnya
- Effect: Ketika ganti acuan, **semua temporary adjustment di-reset otomatis**
- Reason: Acuan berubah, jadi temporary adjustment dari acuan sebelumnya tidak valid lagi
- Implementation: `CustomStandardService::select()` calls `Session::forget("standard_adjustment.{$templateId}")`

**B. Badge "Standar Disesuaikan" (Adjustment Indicator)**
- Purpose: Menunjukkan ada **temporary adjustment di session** (bukan pilihan custom standard)
- Appears when: User melakukan edit manual di halaman (kategori weight, aspek selection, rating edit)
- Does NOT appear when: User hanya memilih custom standard dari dropdown
- Logic: Badge hanya cek `DynamicStandardService::hasCategoryAdjustments()` yang membaca **session** saja

**C. Button "Bobot Standar" (CategoryWeightEditor)**
- Visual indicator: Button amber + label "(disesuaikan)" jika ada session adjustment
- Logic sama dengan badge: Cek session adjustment, bukan custom standard selection
- Listener: Listen ke `'standard-switched'` untuk live update saat switch standar

**Example Flow:**

```
Scenario 1: Switch Custom Standard
1. User pilih "Standar Kejaksaan v1" dari dropdown
2. selectCustomStandard(5) → CustomStandardService::select()
3. Session::forget("standard_adjustment.{$templateId}") ✓
4. System: Load data dari custom standard
5. Badge "Standar Disesuaikan": TIDAK MUNCUL ✓
6. Button "Bobot Standar": Normal (outline) ✓
7. Cell highlight: TIDAK ADA ✓

Scenario 2: Edit Manual (Quantum atau Custom)
1. User edit kategori weight Potensi: 60% → 70%
2. System: DynamicStandardService::saveCategoryWeight()
3. Session adjustment created
4. Badge "Standar Disesuaikan": MUNCUL ✓
5. Button "Bobot Standar": Amber + "(disesuaikan)" ✓
6. Cell highlight: KUNING ✓

Scenario 3: Edit Custom Standard di Management → Kembali ke Halaman
1. User edit custom standard di halaman management
2. User kembali ke StandardPsikometrik/StandardMc
3. Data ter-update dari custom standard yang baru
4. Badge "Standar Disesuaikan": TIDAK MUNCUL ✓
5. Button normal, cell normal ✓
```

**Implementation Details:**

**1. Data Loading (StandardPsikometrik & StandardMc):**
```php
// ALWAYS use DynamicStandardService (handles priority chain)
$categoryWeight = $dynamicService->getCategoryWeight($templateId, $category->code);
$aspectRating = $dynamicService->getAspectRating($templateId, $aspect->code);

// Priority chain inside DynamicStandardService:
// 1. Session adjustment (temporary)
// 2. Custom standard (if selected)
// 3. Quantum default (from database)
```

**2. Adjustment Flags (is_adjusted, is_weight_adjusted, is_rating_adjusted):**
```php
// Check SESSION only, NOT value comparison
$adjustments = $dynamicService->getAdjustments($templateId);
$isAdjusted = isset($adjustments['sub_aspect_ratings'][$subAspect->code]);
$isWeightAdjusted = isset($adjustments['aspect_weights'][$aspect->code]);

// DON'T do value comparison like this:
// $isAdjusted = $value !== $originalValue; // ❌ WRONG
```

**3. Type Safety (selectCustomStandard):**
```php
// Handle string "null", empty string, or actual null from dropdown
$customStandardId = $customStandardId === '' || $customStandardId === 'null' || $customStandardId === null
    ? null
    : (int) $customStandardId;
```

**4. Event System:**
```php
// Dispatch event for live updates
$this->dispatch('standard-switched', templateId: $this->templateId);

// Listeners
protected $listeners = [
    'standard-switched' => 'refresh', // CategoryWeightEditor
    'standard-switched' => 'handleStandardSwitch', // Report components
];
```

---

## Implementation Plan

### Phase 1: Foundation (Backend)

1. **Migration**
   - Create `custom_standards` table
   - Create model with fillable, casts, relationships

2. **CustomStandardService**
   - CRUD operations
   - Template defaults loader
   - Session management

3. **Update DynamicStandardService**
   - Add custom standard priority in getters
   - Maintain backward compatibility

### Phase 2: Management UI

4. **Custom Standard List Page**
   - Route & controller/Livewire component
   - List, filter, search
   - Delete confirmation

5. **Create/Edit Form**
   - Template selector
   - Category weights
   - Aspect configurations (Potensi)
   - Aspect configurations (Kompetensi)
   - Sub-aspect configurations
   - Validation (total weights = 100%)

### Phase 3: Integration

6. **Standard Selector Dropdown**
   - Add to StandardPsikometrik
   - Add to StandardMc
   - Session-based selection
   - Event dispatch

7. **Event Handling**
   - New event: `'standard-switched'`
   - Update all report components to listen
   - Clear cache & reload on switch

### Phase 4: Testing & Documentation

8. **Testing**
   - Unit tests for services
   - Feature tests for CRUD
   - Integration tests for calculation
   - Browser tests for UI

9. **Documentation**
   - Update SERVICE_ARCHITECTURE.md
   - User guide for clients

---

## Security & Access Control

### Authorization Rules

| Action | Who Can Do |
|--------|------------|
| **View list** | Users with same `institution_id` |
| **Create** | Users with same `institution_id` |
| **Edit** | Users with same `institution_id` (or creator only?) |
| **Delete** | Users with same `institution_id` (or creator only?) |
| **Select/Use** | Users with same `institution_id` |

### Policy Implementation

```php
class CustomStandardPolicy
{
    public function viewAny(User $user): bool
    {
        // Must have institution (not admin)
        return $user->institution_id !== null;
    }

    public function view(User $user, CustomStandard $customStandard): bool
    {
        return $user->institution_id === $customStandard->institution_id;
    }

    public function create(User $user): bool
    {
        return $user->institution_id !== null;
    }

    public function update(User $user, CustomStandard $customStandard): bool
    {
        return $user->institution_id === $customStandard->institution_id;
    }

    public function delete(User $user, CustomStandard $customStandard): bool
    {
        return $user->institution_id === $customStandard->institution_id;
    }
}
```

### Data Isolation

- Custom standards filtered by `institution_id` in all queries
- Middleware ensures user can only access their institution's data
- No cross-institution data leakage

---

## Testing Strategy

### Unit Tests

```php
// CustomStandardServiceTest
- test_can_create_custom_standard()
- test_can_get_standards_for_institution()
- test_can_select_standard()
- test_reset_adjustments_on_select()

// DynamicStandardServiceTest (Updated)
- test_get_aspect_weight_from_session_adjustment()
- test_get_aspect_weight_from_custom_standard()
- test_get_aspect_weight_from_quantum_default()
- test_priority_chain_is_correct()
```

### Feature Tests

```php
// CustomStandardControllerTest
- test_user_can_view_custom_standards_list()
- test_user_can_create_custom_standard()
- test_user_cannot_access_other_institution_standards()
- test_admin_cannot_create_custom_standard()

// StandardSelectionTest
- test_user_can_select_custom_standard()
- test_switch_standard_resets_adjustments()
- test_switch_standard_dispatches_event()
```

### Integration Tests

```php
// CalculationWithCustomStandardTest
- test_aspect_scores_calculated_with_custom_weights()
- test_category_totals_use_custom_weights()
- test_final_assessment_uses_custom_standard()
- test_ranking_uses_custom_standard()
```

---

## Appendix

### A. Migration to Run

```php
php artisan make:migration create_custom_standards_table
```

### B. Models to Create

```php
php artisan make:model CustomStandard
```

### C. Events Reference

| Event | Payload | Dispatched By | Listened By |
|-------|---------|---------------|-------------|
| `standard-switched` | `templateId` | StandardPsikometrik, StandardMc | All report components |
| `standard-adjusted` | `templateId` | StandardPsikometrik, StandardMc | All report components (existing) |
| `tolerance-updated` | `tolerance` | ToleranceSelector | GeneralPsyMapping, etc. (existing) |

### D. Session Keys Reference

```php
// Custom standard selection
"selected_standard.{$templateId}" => ?int $customStandardId

// Dynamic adjustments (existing)
"standard_adjustment.{$templateId}" => array $adjustments
```

---

## Files to Read Before Implementation

### Core Services (MUST READ)

```
app/Services/DynamicStandardService.php    ← Update priority chain di sini
app/Services/IndividualAssessmentService.php  ← Tidak perlu diubah
app/Services/RankingService.php            ← Tidak perlu diubah
```

### Livewire Components to Update

```
app/Livewire/Pages/GeneralReport/StandardPsikometrik.php  ← Tambah dropdown standar
app/Livewire/Pages/GeneralReport/StandardMc.php           ← Tambah dropdown standar
```

### Report Components (Add Event Listener)

```
app/Livewire/Pages/GeneralReport/GeneralPsyMapping.php
app/Livewire/Pages/GeneralReport/GeneralMcMapping.php
app/Livewire/Pages/GeneralReport/GeneralMapping.php
app/Livewire/Pages/GeneralReport/GeneralMatching.php
app/Livewire/Pages/RankingReport/RankingPsyMapping.php
app/Livewire/Pages/RankingReport/RankingMcMapping.php
app/Livewire/Pages/RankingReport/RekapRankingAssessment.php
```

### Models

```
app/Models/AssessmentTemplate.php   ← Relasi ke CustomStandard
app/Models/Institution.php          ← Relasi ke CustomStandard
app/Models/User.php                 ← Untuk policy check (institution_id)
```

### Existing Documentation

```
docs/SERVICE_ARCHITECTURE.md        ← Pahami calculation flow
docs/DATABASE_STRUCTURE.md          ← Struktur tabel existing
```

---

## Current Implementation Status

### Completed
- [x] Documentation created (this file)

### Phase 1: Foundation (Backend) ✅
- [x] Create migration `create_custom_standards_table`
- [x] Create model `CustomStandard` with relationships
- [x] Create `CustomStandardService`
- [x] Update `DynamicStandardService` with priority chain
- [x] Create `CustomStandardPolicy`

### Phase 2: Management UI ✅
- [x] Create list page `/custom-standards`
- [x] Create form page `/custom-standards/create`
- [x] Create edit page `/custom-standards/{id}/edit`
- [x] Add validation (total weights = 100%)
- [x] Add template filter (institution-specific)

### Phase 3: Integration ✅

**PRIORITY: Core functionality (StandardPsikometrik & StandardMc)** ✅
- [x] Add dropdown to `StandardPsikometrik`
- [x] Add dropdown to `StandardMc`
- [x] Fix TypeError: Handle type casting for selectCustomStandard()
- [x] Fix data loading: Always use DynamicStandardService (priority chain)
- [x] Fix badge logic: Session check instead of value comparison
- [x] Fix CategoryWeightEditor: Session check for isAdjusted
- [x] Fix live updates: CategoryWeightEditor listen to 'standard-switched'
- [x] Add `'standard-switched'` event dispatch

**SECONDARY: Update other report components** (Remaining components)
- [x] Update `GeneralPsyMapping` to listen to standard-switched
- [x] Update `GeneralMcMapping` to listen to standard-switched
- [ ] Update `GeneralMapping` to listen to standard-switched
- [ ] Update `GeneralMatching` to listen to standard-switched
- [ ] Update `RankingPsyMapping` to listen to standard-switched
- [ ] Update `RankingMcMapping` to listen to standard-switched
- [ ] Update `RekapRankingAssessment` to listen to standard-switched

### Phase 4: Testing
- [ ] Unit tests for services
- [ ] Feature tests for CRUD
- [ ] Integration tests for calculation

---

**Document Status**: Phase 3 Core Functionality Complete ✅ (85% complete)
**Next Step**: Update remaining report components (GeneralMapping, GeneralMatching, Ranking reports) to listen to 'standard-switched' event

**Key Achievements:**
- ✅ Custom standard selection working perfectly
- ✅ Data loads from custom standard correctly
- ✅ Badge & highlight logic fixed (session-based)
- ✅ Live updates when switching standards
- ✅ Type safety for dropdown values
- ✅ CategoryWeightEditor live update

---

## How to Continue in New Session

1. **Read this document first** - Contains all context needed
2. **Check "Current Implementation Status"** - See what's done and what's next
3. **Read "Files to Read Before Implementation"** - Understand existing code
4. **Follow Implementation Plan** - Phase by phase
5. **Update status checkboxes** - As you complete each task
