# Aspect Bank & Template Decoupling: Custom Standard Flexibility

> **Version**: 1.0
> **Created**: 2025-01-27
> **Status**: 📋 Design Document - Ready for Discussion
> **Priority**: High - Unlocks True Custom Standard Flexibility
> **Estimated Effort**: Phase 1 (1-2 weeks), Phase 2 (2-3 months)

---

## 📋 Table of Contents

1. [Quick Context](#quick-context)
2. [Problem Statement](#problem-statement)
3. [Current Architecture Analysis](#current-architecture-analysis)
4. [Proposed Solutions](#proposed-solutions)
5. [Recommended Approach](#recommended-approach)
6. [Phase 1: Quick Win](#phase-1-quick-win)
7. [Phase 2: Full Bank Implementation](#phase-2-full-bank-implementation)
8. [Migration Strategy](#migration-strategy)
9. [Decision Matrix](#decision-matrix)

---

## Quick Context

### What is SPSP?

**SPSP (Sistem Pemetaan & Statistik Psikologi)** adalah SaaS analytics dashboard untuk assessment center (seperti seleksi CPNS, P3K, promosi internal).

**Key Points:**
- Multi-tenant SaaS (setiap institusi punya data terpisah)
- Data peserta di-import dari sistem Quantum via API (read-only)
- Fokus pada **analitik**: user menganalisis hasil dengan berbagai standar

### Current Data Hierarchy

```
AssessmentTemplate (Blueprint from Quantum)
  └── CategoryType (Potensi 50%, Kompetensi 50%)
      └── Aspect (Kecerdasan 20%, Integritas 15%, dll)
          └── SubAspect (Optional, one-to-many)
```

### Three-Tier Priority System

```
PRIORITY 1: Session Adjustment (Temporary Analysis Tool)
  ↓ (if not found)
PRIORITY 2: Custom Standard (Saved Institution Baseline)
  ↓ (if not found)
PRIORITY 3: Quantum Default (Template Master Data)
```

**Session Adjustment** adalah fitur analisa sementara:
- User bisa eksperimen dengan nilai berbeda
- Data hilang setelah logout (intentional)
- Smart saving: hanya simpan jika berbeda dari baseline
- Event-driven: `'standard-adjusted'` → semua report reload

**Custom Standard** adalah fitur persistent:
- Institution menyimpan standar mereka sendiri
- Shared antar user dalam institusi yang sama
- JSON storage di database
- Selection via session: `Session::get("selected_standard.{$templateId}")`

### Key Relationships (Current)

```
PositionFormation (Jabatan)
  └── template_id → AssessmentTemplate (1:1, LOCKED)

CustomStandard
  └── template_id → AssessmentTemplate (MUST MATCH position's template)
      └── aspect_configs (JSON) → refers to aspects in that template
      └── sub_aspect_configs (JSON) → refers to sub-aspects in that template

Participant
  └── position_formation_id → PositionFormation
      └── template_id determines which custom standards can be used
```

**Related Docs:**
- `docs/SERVICE_ARCHITECTURE.md` - Session adjustment & calculation flow
- `docs/CUSTOM_STANDARD_FEATURE.md` - Custom standard implementation
- `docs/FLEXIBLE_HIERARCHY_REFACTORING.md` - Data-driven aspect structure

---

## Problem Statement

### The Core Issue

**Current State:**
- 1 Jabatan (PositionFormation) = 1 Template (AssessmentTemplate) - **LOCKED**
- 1 Template = Fixed set of Aspects & SubAspects
- Custom Standard **MUST** reference same template as position
- Cannot mix aspects from different templates
- No "aspect bank" concept - aspects tied to specific templates

**Business Requirement:**
> "Seharusnya standar apapun bisa dipakaikan ke jabatan apapun"

**Vision:**
- Quantum should provide "Aspect Bank" (master catalog of all aspects/sub-aspects)
- Institution should be able to pick ANY aspects from bank when creating custom standard
- Custom standard should be usable for ANY position, regardless of template
- True flexibility: mix & match aspects freely

### Current Limitation Example

**Scenario:**
```
Template A "Standar Manajerial L3"
  ├── Aspect: Kecerdasan
  ├── Aspect: Cara Kerja
  └── Aspect: Integritas

Template B "Standar Teknis"
  ├── Aspect: Pemahaman Teknis
  ├── Aspect: Problem Solving
  └── Aspect: Detail Oriented

CustomStandard "Kejaksaan Strict" (template_id = A)
  ├── Can configure: Kecerdasan, Cara Kerja, Integritas ✓
  ├── CANNOT add: Pemahaman Teknis ✗ (from template B)
  └── CANNOT be used for position with template B ✗
```

**What User Wants:**
```
CustomStandard "Kejaksaan Strict" (NO template restriction)
  ├── Picks from bank: Kecerdasan, Pemahaman Teknis, Integritas
  ├── Mixes aspects from template A & B freely ✓
  └── Can be used for ANY position ✓
```

### Why This Matters

| Stakeholder | Pain Point | Business Impact |
|-------------|------------|-----------------|
| **Institution** | "Kami ingin standar yang combine aspek dari berbagai template" | Cannot create desired assessment criteria |
| **Quantum** | "Kami punya ratusan aspek, tapi client hanya lihat yang di template mereka" | Underutilization of aspect catalog |
| **Admin** | "Setiap kali butuh aspek baru, harus buat template baru" | Maintenance overhead |
| **User** | "Custom standard untuk jabatan A tidak bisa dipakai untuk jabatan B" | Poor reusability |

---

## Current Architecture Analysis

### Database Schema

```sql
-- Position tied to template (LOCKED)
position_formations (
    id,
    event_id,
    template_id,  -- ← LOCKED: cannot change after creation
    code,
    name,
    quota
)

-- Custom standard tied to template (LOCKED)
custom_standards (
    id,
    institution_id,
    template_id,  -- ← MUST match position's template
    code,
    name,
    description,
    category_weights JSON,
    aspect_configs JSON,      -- ← Keys must exist in template
    sub_aspect_configs JSON,  -- ← Keys must exist in template
    is_active,
    created_by
)

-- Template defines structure (FIXED)
assessment_templates (id, code, name)
  └── category_types (id, template_id, code, name)
      └── aspects (id, template_id, category_type_id, code, name)
          └── sub_aspects (id, aspect_id, code, name)
```

### Selection Logic (StandardPsikometrik/StandardMc)

**Current Code:**
```php
// Get participant's position
$participant = Participant::find($participantId);
$positionFormation = $participant->positionFormation;
$templateId = $positionFormation->template_id; // ← LOCKED

// Get custom standards for dropdown
$customStandards = CustomStandard::where('institution_id', $institutionId)
    ->where('template_id', $templateId) // ← MUST MATCH
    ->where('is_active', true)
    ->get();

// Result: Only custom standards with SAME template appear in dropdown
```

### Custom Standard JSON Structure

```json
{
  "category_weights": {
    "potensi": 60,
    "kompetensi": 40
  },

  "aspect_configs": {
    "kecerdasan": {  // ← Code MUST exist in template
      "weight": 25,
      "active": true
    },
    "integritas": {
      "weight": 15,
      "rating": 4.0,
      "active": true
    }
  },

  "sub_aspect_configs": {
    "kecerdasan_umum": {  // ← Code MUST exist in template
      "rating": 4,
      "active": true
    }
  }
}
```

**Validation:**
```php
// When creating custom standard
foreach ($aspectConfigs as $aspectCode => $config) {
    $aspect = Aspect::where('template_id', $templateId)
        ->where('code', $aspectCode)
        ->first();

    if (!$aspect) {
        throw new Exception("Aspect {$aspectCode} not found in template");
    }
}
```

### The Coupling Chain

```
User wants to use custom standard
  ↓
Check participant's position → template_id = X
  ↓
Filter custom standards → WHERE template_id = X
  ↓
Load aspect_configs → validate codes exist in template X
  ↓
❌ LOCKED: Cannot use aspects from other templates
❌ LOCKED: Cannot use custom std for position with different template
```

---

## Proposed Solutions

### Option A: Decouple Template from CustomStandard

#### **Concept:**

Remove `template_id` from custom standards entirely. Store aspect/sub-aspect selections by **ID** (not code), referencing a global **Aspect Bank**.

#### **New Architecture:**

```
Aspect Bank (Quantum Master Data)
  ├── AspectBank (id, code, name, category_type, default_weight, default_rating)
  └── SubAspectBank (id, aspect_bank_id, code, name, default_rating)

CustomStandard (NO template_id)
  ├── selected_aspect_ids: [101, 105, 110, 130] (from bank)
  ├── selected_sub_aspect_ids: [201, 207, 215] (from bank)
  └── aspect_configs: {
        "101": { weight: 20, rating: 4.0 },  // ← Aspect ID
        "105": { weight: 15, active: false }
      }
  └── sub_aspect_configs: {
        "201": { rating: 5, active: true }   // ← SubAspect ID
      }

PositionFormation
  └── template_id (kept for Quantum default reference only)
```

#### **Database Changes:**

```sql
-- New tables for aspect bank
CREATE TABLE aspect_bank (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    category_type VARCHAR(50) NOT NULL,
    default_weight_percentage INT NULL,
    default_standard_rating DECIMAL(5,2) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX idx_category (category_type),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE sub_aspect_bank (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    aspect_bank_id BIGINT UNSIGNED NULL,
    code VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    default_standard_rating INT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (aspect_bank_id) REFERENCES aspect_bank(id) ON DELETE SET NULL,
    INDEX idx_parent (aspect_bank_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Update custom_standards
ALTER TABLE custom_standards DROP FOREIGN KEY custom_standards_template_id_foreign;
ALTER TABLE custom_standards DROP COLUMN template_id;

ALTER TABLE custom_standards
ADD COLUMN selected_aspect_ids JSON NOT NULL AFTER institution_id,
ADD COLUMN selected_sub_aspect_ids JSON NULL AFTER selected_aspect_ids;

-- Note: aspect_configs & sub_aspect_configs JSON structure will change
-- from code-based keys to ID-based keys (migration script needed)
```

#### **Pros:**
- ✅ **True Freedom**: Custom standard bisa pilih aspects mana saja dari bank
- ✅ **Reusability**: 1 custom standard bisa dipakai banyak jabatan (any template)
- ✅ **Bank Support**: Memenuhi vision "bank aspects/sub-aspects"
- ✅ **Future-proof**: Quantum tambah aspects → institution langsung bisa pakai
- ✅ **Scalability**: Supports hundreds/thousands of aspects in bank

#### **Cons:**
- ❌ **Migration Complex**: Existing custom standards perlu migrate (code → ID)
- ❌ **UI Overhaul**: Form creation totally different (pick from bank, not template)
- ❌ **Breaking Changes**: Major architecture change
- ❌ **Data Sync**: Perlu sync aspect bank dari Quantum regularly

---

### Option B: Add Cross-Template Usage Flag

#### **Concept:**

Keep `template_id` but add flag to allow custom standard to be used for other templates.

#### **Database Changes:**

```sql
ALTER TABLE custom_standards
ADD COLUMN can_be_used_for_any_position BOOLEAN DEFAULT FALSE AFTER is_active;
```

#### **Selection Logic:**

```php
// Updated dropdown logic
$customStandards = CustomStandard::where('institution_id', $institutionId)
    ->where(function ($query) use ($templateId) {
        $query->where('template_id', $templateId)
              ->orWhere('can_be_used_for_any_position', true);
    })
    ->where('is_active', true)
    ->get();
```

#### **UI Addition:**

```blade
<!-- Create Custom Standard Form -->
<div class="form-group">
    <label>
        <input type="checkbox" name="can_be_used_for_any_position">
        Bisa digunakan untuk semua jabatan (tidak terbatas template)
    </label>
</div>
```

#### **Pros:**
- ✅ **Quick Implementation**: 1-2 weeks
- ✅ **Minimal Migration**: No data migration needed
- ✅ **Backward Compatible**: Existing custom standards work as-is
- ✅ **Gradual Adoption**: Users choose which standards are flexible

#### **Cons:**
- ⚠️ **Still Coupled**: Aspects masih terbatas pada template saat creation
- ⚠️ **Not True Bank**: Belum support "pick from bank freely"
- ⚠️ **Partial Solution**: Hanya solve "reusability", not "flexibility"
- ⚠️ **Validation Issues**: Jika dipakai untuk position dengan template berbeda, aspects mungkin tidak exist

---

### Option C: Virtual Bank via Master Template

#### **Concept:**

Create 1 "Master Template" yang berisi ALL aspects/sub-aspects. Semua custom standards reference template ini.

#### **Implementation:**

```sql
-- Create master template
INSERT INTO assessment_templates (id, code, name)
VALUES (1, 'QUANTUM_MASTER', 'Quantum Aspect Bank');

-- Migrate all aspects to master template
UPDATE aspects SET template_id = 1;

-- Update all custom standards to use master
UPDATE custom_standards SET template_id = 1;
```

#### **Pros:**
- ✅ **Simple Migration**: Just update template_id
- ✅ **Minimal Code Changes**: Existing logic works
- ✅ **Bank Effect**: Master template acts as bank

#### **Cons:**
- ❌ **Semantic Confusion**: `template_id` doesn't mean "template" anymore
- ❌ **Master Bloat**: Single template with hundreds of aspects
- ❌ **Maintenance**: Must update master whenever Quantum adds aspects
- ❌ **Not Clean Architecture**: Workaround, not proper solution

---

## Recommended Approach

### Hybrid Strategy: Two Phases

Kombinasi **Option B (short-term)** + **Option A (long-term)** untuk smooth transition.

---

## Phase 1: Quick Win (1-2 weeks)

### Goal
Immediate relief: Allow custom standards to be used across positions without breaking existing functionality.

### Implementation

#### Step 1: Database Migration

**Create:** `database/migrations/2025_01_27_add_cross_template_flag_to_custom_standards.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_standards', function (Blueprint $table) {
            $table->boolean('can_be_used_for_any_position')
                ->default(false)
                ->after('is_active')
                ->comment('Allow this custom standard to be used for positions with different templates');
        });
    }

    public function down(): void
    {
        Schema::table('custom_standards', function (Blueprint $table) {
            $table->dropColumn('can_be_used_for_any_position');
        });
    }
};
```

#### Step 2: Update Model

**File:** `app/Models/CustomStandard.php`

```php
protected $fillable = [
    'institution_id',
    'template_id',
    'code',
    'name',
    'description',
    'category_weights',
    'aspect_configs',
    'sub_aspect_configs',
    'is_active',
    'can_be_used_for_any_position', // ← ADD THIS
    'created_by',
];

protected function casts(): array
{
    return [
        'category_weights' => 'array',
        'aspect_configs' => 'array',
        'sub_aspect_configs' => 'array',
        'is_active' => 'boolean',
        'can_be_used_for_any_position' => 'boolean', // ← ADD THIS
    ];
}
```

#### Step 3: Update CustomStandardService

**File:** `app/Services/CustomStandardService.php`

**Add method:**

```php
/**
 * Get custom standards for dropdown (supports cross-template)
 *
 * Returns:
 * 1. Custom standards with matching template_id
 * 2. Custom standards with can_be_used_for_any_position = true
 *
 * @param int $institutionId
 * @param int $templateId Current position's template
 * @return \Illuminate\Support\Collection
 */
public function getAvailableStandards(int $institutionId, int $templateId): Collection
{
    return CustomStandard::where('institution_id', $institutionId)
        ->where('is_active', true)
        ->where(function ($query) use ($templateId) {
            $query->where('template_id', $templateId)
                  ->orWhere('can_be_used_for_any_position', true);
        })
        ->orderBy('name')
        ->get();
}
```

#### Step 4: Update StandardPsikometrik & StandardMc Components

**Files:**
- `app/Livewire/Pages/GeneralReport/StandardPsikometrik.php`
- `app/Livewire/Pages/GeneralReport/StandardMc.php`

**Update `mount()` method:**

```php
public function mount(int $participantId, string $categoryCode): void
{
    // ... existing code ...

    // Load available custom standards (with cross-template support)
    $customStandardService = app(CustomStandardService::class);

    $this->availableCustomStandards = $customStandardService
        ->getAvailableStandards(
            auth()->user()->institution_id,
            $this->templateId
        );

    // ... rest of code ...
}
```

#### Step 5: Update Custom Standard Creation Form

**Add checkbox to form:**

```blade
<!-- In custom standard create/edit form -->
<div class="mb-4">
    <label class="flex items-center">
        <input
            type="checkbox"
            wire:model="can_be_used_for_any_position"
            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
        >
        <span class="ml-2 text-sm text-gray-700">
            Bisa digunakan untuk semua jabatan
            <span class="text-gray-500 block text-xs mt-1">
                Jika dicentang, standar ini bisa dipilih untuk jabatan dengan template apapun
            </span>
        </span>
    </label>
</div>
```

#### Step 6: Add Validation Warning

**When using cross-template standard:**

```php
// In DynamicStandardService or validation layer
public function validateCustomStandard(CustomStandard $customStandard, int $currentTemplateId): array
{
    $warnings = [];

    if ($customStandard->can_be_used_for_any_position
        && $customStandard->template_id !== $currentTemplateId) {

        // Check if aspects in custom standard exist in current template
        $missingAspects = [];

        foreach (array_keys($customStandard->aspect_configs) as $aspectCode) {
            $exists = Aspect::where('template_id', $currentTemplateId)
                ->where('code', $aspectCode)
                ->exists();

            if (!$exists) {
                $missingAspects[] = $aspectCode;
            }
        }

        if (!empty($missingAspects)) {
            $warnings[] = "Beberapa aspek tidak tersedia di template saat ini: "
                . implode(', ', $missingAspects);
        }
    }

    return $warnings;
}
```

### Testing Phase 1

- [ ] Create custom standard dengan flag ON
- [ ] Create custom standard dengan flag OFF
- [ ] Test dropdown di StandardPsikometrik untuk position A (template 1)
  - [ ] Shows custom std with template 1 ✓
  - [ ] Shows custom std with flag ON ✓
  - [ ] Does NOT show custom std with template 2 and flag OFF ✓
- [ ] Test session adjustment works dengan cross-template custom std
- [ ] Test report calculations correct

---

## Phase 2: Full Bank Implementation (2-3 months)

### Goal
True flexibility: Aspect bank dengan ability to pick any aspects freely.

### Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    ASPECT BANK (Quantum)                     │
│  - AspectBank: Global catalog of all aspects                │
│  - SubAspectBank: Global catalog of all sub-aspects         │
│  - Maintained by Quantum, synced to SPSP                    │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ↓ (references by ID)
┌─────────────────────────────────────────────────────────────┐
│                    CUSTOM STANDARD                           │
│  - NO template_id                                            │
│  - selected_aspect_ids: [101, 105, 110]                      │
│  - selected_sub_aspect_ids: [201, 207]                       │
│  - aspect_configs keyed by ID (not code)                     │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ↓ (can be used for)
┌─────────────────────────────────────────────────────────────┐
│                  POSITION FORMATION                          │
│  - template_id (kept for Quantum default only)              │
│  - Custom standard selection independent of template         │
└─────────────────────────────────────────────────────────────┘
```

### Implementation Steps

#### 2.1: Create Aspect Bank Tables

**Migration:** `2025_02_XX_create_aspect_bank_tables.php`

```php
public function up(): void
{
    // Global aspect bank
    Schema::create('aspect_bank', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique();
        $table->string('name');
        $table->text('description')->nullable();
        $table->string('category_type', 50); // 'potensi', 'kompetensi', etc.
        $table->integer('default_weight_percentage')->nullable();
        $table->decimal('default_standard_rating', 5, 2)->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();

        $table->index('category_type');
        $table->index('is_active');
    });

    // Global sub-aspect bank
    Schema::create('sub_aspect_bank', function (Blueprint $table) {
        $table->id();
        $table->foreignId('aspect_bank_id')->nullable()->constrained('aspect_bank')->onDelete('set null');
        $table->string('code')->unique();
        $table->string('name');
        $table->text('description')->nullable();
        $table->integer('default_standard_rating')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();

        $table->index('aspect_bank_id');
        $table->index('is_active');
    });
}
```

#### 2.2: Seed Aspect Bank from Existing Data

**Seeder:** `database/seeders/AspectBankSeeder.php`

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Aspect, SubAspect, AspectBank, SubAspectBank};

class AspectBankSeeder extends Seeder
{
    public function run(): void
    {
        // Collect unique aspects from all templates
        $aspects = Aspect::select('code', 'name', 'description')
            ->distinct()
            ->get();

        foreach ($aspects as $aspect) {
            // Determine category type (from most common usage)
            $categoryType = Aspect::where('code', $aspect->code)
                ->with('categoryType')
                ->first()
                ->categoryType
                ->code;

            AspectBank::create([
                'code' => $aspect->code,
                'name' => $aspect->name,
                'description' => $aspect->description,
                'category_type' => $categoryType,
                'default_weight_percentage' => 10, // Default
                'default_standard_rating' => null, // Calculated from subs or set manually
                'is_active' => true,
            ]);
        }

        // Collect unique sub-aspects
        $subAspects = SubAspect::select('code', 'name', 'description', 'standard_rating')
            ->distinct()
            ->get();

        foreach ($subAspects as $subAspect) {
            // Find parent aspect in bank
            $aspect = Aspect::whereHas('subAspects', function ($q) use ($subAspect) {
                $q->where('code', $subAspect->code);
            })->first();

            $aspectBankId = AspectBank::where('code', $aspect->code)->value('id');

            SubAspectBank::create([
                'aspect_bank_id' => $aspectBankId,
                'code' => $subAspect->code,
                'name' => $subAspect->name,
                'description' => $subAspect->description,
                'default_standard_rating' => $subAspect->standard_rating,
                'is_active' => true,
            ]);
        }
    }
}
```

#### 2.3: Update Custom Standard Schema

**Migration:** `2025_02_XX_decouple_custom_standard_from_template.php`

```php
public function up(): void
{
    Schema::table('custom_standards', function (Blueprint $table) {
        // Add new columns
        $table->json('selected_aspect_ids')->after('institution_id');
        $table->json('selected_sub_aspect_ids')->nullable()->after('selected_aspect_ids');

        // Keep template_id temporarily for migration
        // Will drop after data migration complete
    });
}
```

#### 2.4: Migrate Existing Custom Standards

**Command:** `php artisan migrate:custom-standards-to-bank`

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\{CustomStandard, AspectBank, SubAspectBank};

class MigrateCustomStandardsToBank extends Command
{
    protected $signature = 'migrate:custom-standards-to-bank';
    protected $description = 'Migrate custom standards from template-based to bank-based';

    public function handle(): int
    {
        $customStandards = CustomStandard::all();
        $bar = $this->output->createProgressBar($customStandards->count());

        foreach ($customStandards as $std) {
            // Convert aspect codes to IDs
            $aspectIds = [];
            $newAspectConfigs = [];

            foreach ($std->aspect_configs as $aspectCode => $config) {
                $aspectBank = AspectBank::where('code', $aspectCode)->first();

                if ($aspectBank) {
                    $aspectIds[] = $aspectBank->id;
                    $newAspectConfigs[$aspectBank->id] = $config;
                } else {
                    $this->warn("Aspect {$aspectCode} not found in bank for custom standard {$std->id}");
                }
            }

            // Convert sub-aspect codes to IDs
            $subAspectIds = [];
            $newSubAspectConfigs = [];

            foreach ($std->sub_aspect_configs ?? [] as $subCode => $config) {
                $subAspectBank = SubAspectBank::where('code', $subCode)->first();

                if ($subAspectBank) {
                    $subAspectIds[] = $subAspectBank->id;
                    $newSubAspectConfigs[$subAspectBank->id] = $config;
                } else {
                    $this->warn("SubAspect {$subCode} not found in bank for custom standard {$std->id}");
                }
            }

            // Update custom standard
            $std->update([
                'selected_aspect_ids' => $aspectIds,
                'selected_sub_aspect_ids' => $subAspectIds,
                'aspect_configs' => $newAspectConfigs,
                'sub_aspect_configs' => $newSubAspectConfigs,
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Migration complete!');

        return 0;
    }
}
```

#### 2.5: Update DynamicStandardService

**File:** `app/Services/DynamicStandardService.php`

**Update methods to work with IDs instead of codes:**

```php
// OLD: Code-based
private function getOriginalAspectRating(int $templateId, string $aspectCode): float
{
    $aspect = Aspect::where('template_id', $templateId)
        ->where('code', $aspectCode)
        ->first();
    // ...
}

// NEW: ID-based (with backward compatibility)
private function getOriginalAspectRating(int $templateId, string|int $aspectIdentifier): float
{
    // Support both code (legacy) and ID (new)
    if (is_numeric($aspectIdentifier)) {
        // ID-based (from bank)
        $aspectBank = AspectBank::find($aspectIdentifier);
        // ...
    } else {
        // Code-based (legacy)
        $aspect = Aspect::where('template_id', $templateId)
            ->where('code', $aspectIdentifier)
            ->first();
        // ...
    }
}
```

#### 2.6: Create New UI for Custom Standard Creation

**Component:** `app/Livewire/CustomStandard/BankSelector.php`

```php
<?php

namespace App\Livewire\CustomStandard;

use Livewire\Component;
use App\Models\{AspectBank, SubAspectBank};

class BankSelector extends Component
{
    public $searchQuery = '';
    public $categoryFilter = 'all';
    public $selectedAspectIds = [];

    public function render()
    {
        $aspects = AspectBank::query()
            ->when($this->searchQuery, function ($q) {
                $q->where('name', 'like', "%{$this->searchQuery}%")
                  ->orWhere('code', 'like', "%{$this->searchQuery}%");
            })
            ->when($this->categoryFilter !== 'all', function ($q) {
                $q->where('category_type', $this->categoryFilter);
            })
            ->where('is_active', true)
            ->orderBy('category_type')
            ->orderBy('name')
            ->get();

        return view('livewire.custom-standard.bank-selector', [
            'aspects' => $aspects,
        ]);
    }

    public function toggleAspect($aspectId)
    {
        if (in_array($aspectId, $this->selectedAspectIds)) {
            $this->selectedAspectIds = array_diff($this->selectedAspectIds, [$aspectId]);
        } else {
            $this->selectedAspectIds[] = $aspectId;
        }

        $this->dispatch('aspectsSelected', $this->selectedAspectIds);
    }
}
```

**View:** `resources/views/livewire/custom-standard/bank-selector.blade.php`

```blade
<div>
    <div class="mb-4 flex gap-4">
        <!-- Search -->
        <input
            type="text"
            wire:model.live="searchQuery"
            placeholder="Cari aspek..."
            class="flex-1 rounded border-gray-300"
        >

        <!-- Category Filter -->
        <select wire:model.live="categoryFilter" class="rounded border-gray-300">
            <option value="all">Semua Kategori</option>
            <option value="potensi">Potensi</option>
            <option value="kompetensi">Kompetensi</option>
        </select>
    </div>

    <!-- Aspect List -->
    <div class="space-y-2 max-h-96 overflow-y-auto">
        @foreach($aspects as $aspect)
            <div class="flex items-center p-3 border rounded hover:bg-gray-50">
                <input
                    type="checkbox"
                    wire:click="toggleAspect({{ $aspect->id }})"
                    @checked(in_array($aspect->id, $selectedAspectIds))
                    class="rounded text-blue-600"
                >
                <div class="ml-3 flex-1">
                    <div class="font-medium">{{ $aspect->name }}</div>
                    <div class="text-sm text-gray-500">
                        {{ $aspect->code }} · {{ ucfirst($aspect->category_type) }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Selected Count -->
    <div class="mt-4 text-sm text-gray-600">
        {{ count($selectedAspectIds) }} aspek dipilih
    </div>
</div>
```

### Testing Phase 2

- [ ] Aspect bank seeder runs successfully
- [ ] Migration command converts all custom standards
- [ ] Custom standard creation dengan bank selector works
- [ ] Can select aspects from different original templates
- [ ] Report calculations work with ID-based configs
- [ ] Session adjustment works with new structure
- [ ] No breaking changes for existing reports

---

## Migration Strategy

### Timeline

```
Month 1: Phase 1 (Quick Win)
  Week 1-2: Implement cross-template flag
  Week 3: Testing & deployment
  Week 4: Monitor & gather feedback

Month 2-3: Phase 2 Prep
  Week 5-6: Design bank schema & UI
  Week 7-8: Build aspect bank & migration tools
  Week 9-10: Build new custom standard creation UI
  Week 11: Internal testing

Month 4: Phase 2 Rollout
  Week 12: Deploy to staging
  Week 13: User acceptance testing
  Week 14: Production deployment
  Week 15-16: Support & bug fixes
```

### Risk Mitigation

| Risk | Likelihood | Impact | Mitigation |
|------|------------|--------|------------|
| **Data loss during migration** | Low | Critical | Full backup before migration, dry-run testing |
| **Aspect bank sync issues** | Medium | High | Implement robust API sync, fallback to local cache |
| **Performance degradation** | Low | Medium | Index optimization, query analysis |
| **User confusion** | Medium | Low | Clear documentation, training materials |
| **Validation edge cases** | Medium | Medium | Comprehensive testing, graceful error handling |

---

## Decision Matrix

### Comparison: Current vs Phase 1 vs Phase 2

| Feature | Current | Phase 1 | Phase 2 |
|---------|---------|---------|---------|
| **Cross-template usage** | ❌ No | ✅ Yes (flag) | ✅ Yes (unlimited) |
| **Aspect bank** | ❌ No | ❌ No | ✅ Yes |
| **Mix aspects freely** | ❌ No | ⚠️ Limited | ✅ Yes |
| **Reusability** | Low | Medium | High |
| **Implementation effort** | - | Low | High |
| **Migration complexity** | - | None | Medium |
| **Breaking changes** | - | None | Managed |
| **User experience** | Rigid | Better | Excellent |
| **Maintenance** | Low | Low | Medium |
| **Quantum dependency** | High | High | Medium |

### When to Use Which Phase

**Use Phase 1 if:**
- Need quick solution (1-2 weeks)
- Want minimal risk
- Existing templates mostly cover needs
- Budget/time constraints

**Proceed to Phase 2 if:**
- Need true flexibility
- Want to unlock full potential
- Ready for architectural improvement
- Can invest 2-3 months

---

## Summary & Recommendations

### Current Problem

```
❌ Custom Standard tied to Template
❌ Cannot mix aspects from different templates
❌ Cannot reuse custom standard across positions with different templates
❌ No aspect bank concept
```

### Recommended Solution

**Phase 1 (Now):** Add `can_be_used_for_any_position` flag
- ✅ Quick win in 1-2 weeks
- ✅ No breaking changes
- ✅ Immediate value for users

**Phase 2 (Later):** Implement full aspect bank
- ✅ True flexibility
- ✅ Future-proof architecture
- ✅ Unlocks full potential

### Next Steps

1. **Review & Approve** this design document
2. **Implement Phase 1** migration & code changes
3. **Test Phase 1** thoroughly
4. **Deploy Phase 1** to production
5. **Gather Feedback** from users
6. **Plan Phase 2** based on feedback & priorities

---

**Document Status**: Ready for Review
**Decision Required**: Approve Phase 1 implementation?
**Estimated Timeline**: Phase 1 (1-2 weeks), Phase 2 (2-3 months)

---

**End of Document**
