# DYNAMIC STANDARD ANALYSIS

Dokumentasi implementasi fitur Dynamic Standard Analysis untuk sistem asesmen SPSP.

**Prerequisites:** Baca terlebih dahulu:
- [DATABASE_STRUCTURE.md](./DATABASE_STRUCTURE.md) - Database schema
- [ASSESSMENT_CALCULATION_FLOW.md](./ASSESSMENT_CALCULATION_FLOW.md) - Calculation logic

---

## TABLE OF CONTENTS

1. [Overview](#overview)
2. [Key Concepts](#key-concepts)
3. [Feature Requirements](#feature-requirements)
4. [Session Structure](#session-structure)
5. [DynamicStandardService](#dynamicstandardservice)
6. [UI Components](#ui-components)
7. [Implementation Plan](#implementation-plan)
8. [Affected Components](#affected-components)

---

## OVERVIEW

### What is Dynamic Standard Analysis?

Fitur yang memungkinkan user melakukan **"what-if" analysis** dengan cara adjust parameter standar asesmen **tanpa mengubah data di database**. Semua adjustment bersifat **temporary** (disimpan di session) dan dapat di-reset kapan saja.

### Use Cases

```
HR Manager ingin tahu:
- "Bagaimana jika bobot Potensi diubah dari 50% ke 40%?"
- "Bagaimana jika standard rating Integritas dinaikkan dari 3 ke 4?"
- "Bagaimana jika kita hanya evaluasi 3 aspek utama saja?"
- "Bagaimana ranking berubah jika aspek Cara Kerja tidak dihitung?"
```

### Core Principles

- ✅ Data asli di database **TIDAK BERUBAH**
- ✅ Adjustment disimpan di **SESSION** (per user, per template)
- ✅ Adjustment bersifat **TEMPLATE-LEVEL** (applies to all events dengan template sama)
- ✅ **Real-time recalculation** - semua component auto-update
- ✅ **Reset button** - kembali ke standar asli kapan saja

---

## KEY CONCEPTS

### 1. Template-Level Adjustment

Adjustment dilakukan di **template level**, bukan per event atau per participant.

```
Template: supervisor_standard_v1
    ↓
Used by Position: Auditor
    ↓
Used in Events:
    - Event A (P3K Kejaksaan 2025)
    - Event B (Rekrutmen BNN 2025)

User adjust supervisor_standard_v1:
✅ Event A (Auditor) → pakai adjusted
✅ Event B (Auditor) → pakai adjusted
❌ Event C (Analis - pakai staff_standard_v1) → tidak terpengaruh
```

### 2. Session-Based Storage

Adjustment tersimpan di session dengan structure:

```php
session()->get('standard_adjustment.{templateId}')
```

**Characteristics:**
- Per user (tidak shared antar user)
- Per template (satu template satu adjustment set)
- Survives page refresh
- Lost on logout (by design)

### 3. Snapshot Pattern in Database

Database menggunakan **Snapshot Pattern** - standard ratings di-copy saat assessment dibuat.

**IMPORTANT:** Dynamic Standard Analysis **TIDAK mengubah** snapshot di database. Analysis dilakukan **on-the-fly** dengan nilai dari session.

---

## FEATURE REQUIREMENTS

### Feature 1: Adjust Category Weights

User dapat mengubah bobot kategori (Potensi vs Kompetensi).

**Rules:**
- Total harus 100%
- Applies to final_assessments calculation

**Example:**
```
Original: Potensi 50%, Kompetensi 50%
Adjusted: Potensi 40%, Kompetensi 60%
```

---

### Feature 2: Adjust Aspect Weights

User dapat mengubah bobot aspek dalam kategori.

**Rules:**
- Total per kategori harus 100%
- Applies to category_assessments calculation

**Example:**
```
Original Potensi:
├─ Kecerdasan: 25%
├─ Cara Kerja: 20%
├─ Potensi Kerja: 20%
├─ Hubungan Sosial: 20%
└─ Kepribadian: 15%
Total: 100%

Adjusted:
├─ Kecerdasan: 30% ← changed
├─ Cara Kerja: 15% ← changed
├─ Potensi Kerja: 25% ← changed
├─ Hubungan Sosial: 20%
└─ Kepribadian: 10% ← changed
Total: 100%
```

---

### Feature 3: Adjust Standard Ratings

User dapat mengubah standard rating.

**For Kompetensi Aspects:**
- Standard rating: INTEGER 1-5 (NOT decimal)
- User edits directly

**For Potensi Aspects:**
- Aspect standard rating: CALCULATED (average dari sub-aspects)
- User edits sub-aspect ratings (INTEGER 1-5)
- Aspect rating auto-calculated

**Example:**
```php
// Kompetensi - Direct Edit
Aspect: Integritas
Original standard_rating: 3
Adjusted standard_rating: 4 ← INTEGER

// Potensi - Calculated from Sub-Aspects
Aspect: Kecerdasan
Sub-Aspects Original: [3, 4, 3, 3] → Avg = 3.25
Sub-Aspects Adjusted: [4, 5, 4, 4] → Avg = 4.25
```

---

### Feature 4: Selective Aspects/Sub-Aspects ⭐

User dapat memilih aspek/sub-aspek mana yang dipakai dalam analysis.

**Rules:**
- Minimum **3 aspects active** per category
- Minimum **1 sub-aspect active** per active aspect (Potensi only)
- Disabled aspect weight = 0%
- Total weight of active aspects = 100%
- User **manually redistribute** weights

**Example:**
```
Original (5 aspects):
✅ Kecerdasan (25%)
✅ Cara Kerja (20%)
✅ Potensi Kerja (20%)
✅ Hubungan Sosial (20%)
✅ Kepribadian (15%)

Adjusted (3 aspects - disable Cara Kerja & Hubungan Sosial):
✅ Kecerdasan (35%) ← user manually adjust
❌ Cara Kerja (0%) ← disabled
✅ Potensi Kerja (30%) ← user manually adjust
❌ Hubungan Sosial (0%) ← disabled
✅ Kepribadian (35%) ← user manually adjust
Total: 100%
```

**Impact:**
- Spider chart axes berkurang (5 → 3)
- Calculation hanya gunakan aspects yang aktif
- Rankings bisa berubah

---

### Feature 5: Inline Editing

User klik cell angka (bobot/rating) → modal popup → edit → save → auto-recalculate.

**Visual Indicators:**
- Adjusted values: **amber background** + border + icon ⚡
- Original value shown: `30% ⚡ (asli: 25%)`
- Hover: highlight blue

---

### Feature 6: Real-time Global Update

Saat user save adjustment → dispatch event → **14 components** auto-refresh.

**Components Affected:**
1. StandardPsikometrik (Potensi - edit location)
2. StandardMc (Kompetensi - edit location)
3. Dashboard
4. GeneralMatching
5. GeneralMapping
6. GeneralPsyMapping
7. GeneralMcMapping
8. SpiderPlot (axes change)
9. RingkasanMcMapping
10. RingkasanAssessment
11. RankingPsyMapping
12. RankingMcMapping
13. RekapRankingAssessment
14. TrainingRecommendation

---

## SESSION STRUCTURE

### Complete Structure

```php
'standard_adjustment.{templateId}' => [
    'adjusted_at' => '2025-01-05 10:30:00',

    // Feature 1: Category Weights
    'category_weights' => [
        'potensi' => 45,      // Original: 50
        'kompetensi' => 55,   // Original: 50
    ],

    // Feature 4: Active Aspects/Sub-Aspects
    'active_aspects' => [
        'kecerdasan' => true,
        'cara_kerja' => false,     // disabled
        'potensi_kerja' => true,
        'hubungan_sosial' => false, // disabled
        'kepribadian' => true,
        // ... Kompetensi aspects
    ],

    'active_sub_aspects' => [
        // Potensi only
        'kecerdasan_umum' => true,
        'daya_tangkap' => true,
        'daya_analisa' => false,    // disabled
        'kemampuan_logika' => true,
        // ... other sub-aspects
    ],

    // Feature 2: Aspect Weights
    'aspect_weights' => [
        'kecerdasan' => 35,        // Original: 25
        'cara_kerja' => 0,         // disabled
        'potensi_kerja' => 30,     // Original: 20
        'hubungan_sosial' => 0,    // disabled
        'kepribadian' => 35,       // Original: 15
        // ... Kompetensi aspects
    ],

    // Feature 3: Aspect Ratings (Kompetensi - INTEGER)
    'aspect_ratings' => [
        'integritas' => 4,         // Original: 3, INTEGER 1-5
        'kerjasama' => 3,
        // ... other Kompetensi aspects
    ],

    // Feature 3: Sub-Aspect Ratings (Potensi - INTEGER)
    'sub_aspect_ratings' => [
        'kecerdasan_umum' => 4,    // Original: 3, INTEGER 1-5
        'daya_tangkap' => 5,       // Original: 4
        'daya_analisa' => 3,
        // ... other sub-aspects
    ],
]
```

### Session Helper Methods

```php
// Check if template has adjustments
session()->has('standard_adjustment.' . $templateId)

// Get all adjustments
session()->get('standard_adjustment.' . $templateId)

// Reset adjustments
session()->forget('standard_adjustment.' . $templateId)

// Save adjustments
session()->put('standard_adjustment.' . $templateId, $data)
```

---

## DYNAMICSTANDARDSERVICE

Service class untuk manage session-based adjustments.

### File Location

```
app/Services/DynamicStandardService.php
```

### Core Methods (Already Exist - Phase 1)

```php
// Get category weight (adjusted or original)
public function getCategoryWeight(int $templateId, string $categoryCode): int;

// Get aspect weight (adjusted or original)
public function getAspectWeight(int $templateId, string $aspectCode): int;

// Get aspect standard rating (adjusted or original)
public function getAspectRating(int $templateId, string $aspectCode): float;

// Get sub-aspect standard rating (adjusted or original)
public function getSubAspectRating(int $templateId, string $subAspectCode): int;

// Save adjustments
public function saveCategoryWeight(int $templateId, string $categoryCode, int $weight): void;
public function saveAspectWeight(int $templateId, string $aspectCode, int $weight): void;
public function saveAspectRating(int $templateId, string $aspectCode, float $rating): void;
public function saveSubAspectRating(int $templateId, string $subAspectCode, int $rating): void;

// Reset
public function resetAdjustments(int $templateId): void;

// Check
public function hasAdjustments(int $templateId): bool;
```

### New Methods (Phase 2 - To Be Implemented)

```php
// Feature 4: Selective Aspects/Sub-Aspects
public function isAspectActive(int $templateId, string $aspectCode): bool;
public function isSubAspectActive(int $templateId, string $subAspectCode): bool;
public function setAspectActive(int $templateId, string $aspectCode, bool $active): void;
public function setSubAspectActive(int $templateId, string $subAspectCode, bool $active): void;
public function getActiveAspects(int $templateId): array;
public function getActiveSubAspects(int $templateId): array;

// Bulk operations for modal
public function saveBulkSelection(int $templateId, array $data): void;

// Validation
public function validateSelection(int $templateId, array $data): array;
// Returns: ['valid' => bool, 'errors' => [...]]
```

### Validation Rules

```php
public function validateSelection(int $templateId, array $data): array
{
    $errors = [];

    // Rule 1: Minimum 3 aspects per category
    $potensiActive = array_filter($data['active_aspects']['potensi'] ?? []);
    if (count($potensiActive) < 3) {
        $errors[] = 'Minimal 3 aspek Potensi harus aktif';
    }

    $kompetensiActive = array_filter($data['active_aspects']['kompetensi'] ?? []);
    if (count($kompetensiActive) < 3) {
        $errors[] = 'Minimal 3 aspek Kompetensi harus aktif';
    }

    // Rule 2: Total weight = 100% per category
    $potensiWeightTotal = array_sum($data['aspect_weights']['potensi'] ?? []);
    if ($potensiWeightTotal !== 100) {
        $errors[] = "Total bobot Potensi harus 100% (saat ini: {$potensiWeightTotal}%)";
    }

    $kompetensiWeightTotal = array_sum($data['aspect_weights']['kompetensi'] ?? []);
    if ($kompetensiWeightTotal !== 100) {
        $errors[] = "Total bobot Kompetensi harus 100% (saat ini: {$kompetensiWeightTotal}%)";
    }

    // Rule 3: Each active aspect must have ≥1 active sub-aspect (Potensi only)
    foreach ($data['active_aspects']['potensi'] as $aspectCode => $isActive) {
        if ($isActive) {
            $subAspects = $data['active_sub_aspects'][$aspectCode] ?? [];
            $activeSubAspects = array_filter($subAspects);
            if (count($activeSubAspects) < 1) {
                $errors[] = "Aspek {$aspectCode} harus memiliki minimal 1 sub-aspek aktif";
            }
        }
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors,
    ];
}
```

---

## UI COMPONENTS

### 1. StandardPsikometrik (Potensi - Edit Location)

**Location:** `app/Livewire/Pages/GeneralReport/StandardPsikometrik.php`

**Features to Add:**
- Bobot kategori displayed prominently (clickable)
- Inline editing for aspect weights (click → modal)
- Inline editing for sub-aspect ratings (click → modal)
- Button "Pilih Aspek & Sub-Aspek" → open SelectiveAspectsModal
- Button "Reset ke Default" → reset all adjustments
- Visual indicators (amber bg) for adjusted values
- Dispatch 'standard-adjusted' event after save

**Example Code:**

```php
// Properties
public bool $showEditWeightModal = false;
public bool $showEditRatingModal = false;
public string $editingField = '';
public $editingValue = null;

// Methods
public function openEditWeight(string $aspectCode, int $currentWeight): void
{
    $this->editingField = $aspectCode;
    $this->editingValue = $currentWeight;
    $this->showEditWeightModal = true;
}

public function saveWeight(): void
{
    $this->dynamicStandardService->saveAspectWeight(
        $this->templateId,
        $this->editingField,
        $this->editingValue
    );

    $this->showEditWeightModal = false;
    $this->dispatch('standard-adjusted', templateId: $this->templateId);
}

public function resetAdjustments(): void
{
    $this->dynamicStandardService->resetAdjustments($this->templateId);
    $this->dispatch('standard-adjusted', templateId: $this->templateId);
}
```

**Blade Example:**

```blade
{{-- Category Weight (Prominent Display) --}}
<div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border-2 border-blue-200">
    <span class="text-lg font-semibold">Bobot Kategori Potensi:</span>
    <span
        wire:click="openEditCategoryWeight('potensi', {{ $categoryWeight }})"
        class="text-2xl font-bold cursor-pointer hover:bg-blue-100 px-3 py-1 rounded ml-2
            {{ $hasAdjustment ? 'bg-amber-50 border border-amber-300' : '' }}"
    >
        {{ $categoryWeight }}%
        @if($hasAdjustment)
            <span class="text-amber-600">⚡</span>
            <span class="text-xs text-gray-500">(asli: {{ $originalWeight }}%)</span>
        @endif
    </span>
</div>

{{-- Buttons --}}
<div class="flex gap-3 mb-4">
    <button wire:click="openSelectionModal" class="px-4 py-2 bg-blue-600 text-white rounded-lg">
        🔧 Pilih Aspek & Sub-Aspek ({{ $activeAspectsCount }}/{{ $totalAspects }})
    </button>

    @if($hasAdjustments)
        <button wire:click="resetAdjustments" class="px-4 py-2 bg-red-600 text-white rounded-lg">
            ♻️ Reset ke Default
        </button>
    @endif
</div>

{{-- Table with Inline Editing --}}
<td>
    <span
        wire:click="openEditWeight('{{ $aspect->code }}', {{ $weight }})"
        class="cursor-pointer px-2 py-1 rounded transition-colors
            {{ $isAdjusted ? 'bg-amber-50 border border-amber-300' : 'hover:bg-gray-100' }}"
        title="Klik untuk edit"
    >
        {{ $weight }}%
        @if($isAdjusted)
            <span class="text-amber-600">⚡</span>
            <span class="text-xs text-gray-500">({{ $originalWeight }}%)</span>
        @endif
    </span>
</td>
```

---

### 2. StandardMc (Kompetensi - Edit Location)

**Location:** `app/Livewire/Pages/GeneralReport/StandardMc.php`

**Same as StandardPsikometrik, but:**
- Edit aspect ratings directly (INTEGER 1-5)
- No sub-aspects (simpler)

---

### 3. SelectiveAspectsModal (NEW Component)

**Location:** `app/Livewire/Components/SelectiveAspectsModal.php`

**Reusable component** for both Potensi & Kompetensi.

**Props:**
- `templateId` - Template yang di-adjust
- `categoryCode` - 'potensi' or 'kompetensi'

**UI Structure:**

```
┌────────────────────────────────────────────────────────┐
│ Pilih Aspek & Sub-Aspek Potensi untuk Analisis        │
│                                                         │
│ [✓ Select All] [✗ Deselect All]                       │
├────────────────────────────────────────────────────────┤
│                                                         │
│ ✅ Kecerdasan                          [30]%           │
│    ├─ ✅ Kecerdasan Umum              Std: [3]        │
│    ├─ ✅ Daya Tangkap                 Std: [4]        │
│    ├─ ❌ Daya Analisa                 Std: [3]        │
│    └─ ✅ Kemampuan Logika             Std: [3]        │
│                                                         │
│ ❌ Cara Kerja                          [0]% (disabled) │
│    └─ (All sub-aspects auto-disabled, greyed out)      │
│                                                         │
│ ✅ Potensi Kerja                       [35]%           │
│    └─ [+] Expand to show sub-aspects                   │
│                                                         │
├────────────────────────────────────────────────────────┤
│ Validasi:                                               │
│ ✅ Total Bobot: 100% (valid)                           │
│ ✅ Aspek Aktif: 3/5 (minimal 3 - valid)                │
│ ✅ Sub-Aspek: Semua aspek punya min 1 sub              │
│                                                         │
│ atau jika invalid:                                      │
│ ⚠️ Total Bobot: 85% (kurang 15%)                       │
│ ⚠️ Kecerdasan: Minimal 1 sub-aspek harus aktif        │
├────────────────────────────────────────────────────────┤
│ [Apply Changes] [Cancel]                               │
│ ↑ disabled if validation fails                          │
└────────────────────────────────────────────────────────┘
```

**Key Behaviors:**
- Aspect unchecked → all sub-aspects auto-unchecked + disabled (greyed)
- Aspect unchecked → weight auto-set to 0
- Real-time validation in modal
- Apply button disabled if validation fails
- Tree structure with expand/collapse

**Component Code Skeleton:**

```php
class SelectiveAspectsModal extends Component
{
    public int $templateId;
    public string $categoryCode;
    public bool $show = false;

    public array $selectedAspects = [];
    public array $selectedSubAspects = [];
    public array $aspectWeights = [];

    protected $listeners = ['openSelectionModal'];

    #[Computed]
    public function validationErrors(): array
    {
        $data = [
            'active_aspects' => [$this->categoryCode => $this->selectedAspects],
            'active_sub_aspects' => $this->selectedSubAspects,
            'aspect_weights' => [$this->categoryCode => $this->aspectWeights],
        ];

        return $this->dynamicStandardService->validateSelection($this->templateId, $data);
    }

    #[Computed]
    public function totalWeight(): int
    {
        return array_sum($this->aspectWeights);
    }

    public function toggleAspect(string $aspectCode): void
    {
        $this->selectedAspects[$aspectCode] = !$this->selectedAspects[$aspectCode];

        // If unchecked, auto-uncheck all sub-aspects
        if (!$this->selectedAspects[$aspectCode]) {
            foreach ($this->selectedSubAspects[$aspectCode] ?? [] as $subCode => $val) {
                $this->selectedSubAspects[$aspectCode][$subCode] = false;
            }
            $this->aspectWeights[$aspectCode] = 0;
        }
    }

    public function applySelection(): void
    {
        $validation = $this->validationErrors;
        if (!$validation['valid']) {
            return; // Keep modal open, show errors
        }

        $this->dynamicStandardService->saveBulkSelection($this->templateId, [
            'active_aspects' => [$this->categoryCode => $this->selectedAspects],
            'active_sub_aspects' => $this->selectedSubAspects,
            'aspect_weights' => [$this->categoryCode => $this->aspectWeights],
        ]);

        $this->dispatch('standard-adjusted', templateId: $this->templateId);
        $this->show = false;
    }
}
```

---

### 4. Other Components (Add Listeners)

**12 components** need to listen to 'standard-adjusted' event and auto-refresh.

**Template for each:**

```php
protected $listeners = [
    'standard-adjusted' => 'handleStandardUpdate',
];

public function handleStandardUpdate($templateId)
{
    // Get current template being viewed
    $currentTemplateId = $this->getCurrentTemplateId();

    // Only refresh if same template
    if ($currentTemplateId === $templateId) {
        $this->loadData(); // or $this->dispatch('$refresh');
    }
}

private function getCurrentTemplateId(): ?int
{
    // Logic depends on component
    // Examples:
    // - $this->event->positionFormations->first()->template_id
    // - $this->participant->positionFormation->template_id
    // - etc.
}
```

**Components:**
3. Dashboard
4. GeneralMatching
5. GeneralMapping
6. GeneralPsyMapping
7. GeneralMcMapping
8. SpiderPlot (+ dynamic axes)
9. RingkasanMcMapping
10. RingkasanAssessment
11. RankingPsyMapping
12. RankingMcMapping
13. RekapRankingAssessment
14. TrainingRecommendation

---

## IMPLEMENTATION PLAN

### Phase 2A: Extend DynamicStandardService ⏳

**Tasks:**
1. Add methods untuk selective aspects (isAspectActive, setAspectActive, etc.)
2. Add validateSelection() method dengan rules
3. Add saveBulkSelection() method
4. Update existing methods to respect active/inactive aspects

**Files:**
- `app/Services/DynamicStandardService.php`

---

### Phase 2B: Create SelectiveAspectsModal ⏳

**Tasks:**
1. Create Livewire component
2. Create blade view dengan tree structure
3. Implement real-time validation
4. Implement toggle behaviors
5. Test modal independently

**Files:**
- `app/Livewire/Components/SelectiveAspectsModal.php`
- `resources/views/livewire/components/selective-aspects-modal.blade.php`

---

### Phase 2C: Update StandardPsikometrik & StandardMc ⏳

**Tasks:**
1. Add inline editing (click → modal)
2. Add visual indicators (amber bg untuk adjusted values)
3. Add "Pilih Aspek" button → trigger SelectiveAspectsModal
4. Add "Reset" button
5. Show category weight prominently
6. Dispatch events after save

**Files:**
- `app/Livewire/Pages/GeneralReport/StandardPsikometrik.php`
- `app/Livewire/Pages/GeneralReport/StandardMc.php`
- `resources/views/livewire/pages/general-report/standard-psikometrik.blade.php`
- `resources/views/livewire/pages/general-report/standard-mc.blade.php`

---

### Phase 2D: Add Listeners to 12 Components ⏳

**Tasks:**
1. Add 'standard-adjusted' listener
2. Add handleStandardUpdate() method
3. Add getCurrentTemplateId() helper
4. Test auto-refresh behavior

**Files:** (12 components)
- Dashboard.php
- GeneralMatching.php
- GeneralMapping.php
- GeneralPsyMapping.php
- GeneralMcMapping.php
- SpiderPlot.php (+ dynamic axes logic)
- RingkasanMcMapping.php
- RingkasanAssessment.php
- RankingPsyMapping.php
- RankingMcMapping.php
- RekapRankingAssessment.php
- TrainingRecommendation.php

---

### Phase 2E: Update Calculation Services ⏳

**Tasks:**
1. Update services to use only active aspects/sub-aspects
2. AspectService: average only active sub-aspects
3. CategoryService: sum only active aspects
4. FinalAssessmentService: use adjusted category weights

**Files:**
- `app/Services/Assessment/AspectService.php`
- `app/Services/Assessment/CategoryService.php`
- `app/Services/Assessment/FinalAssessmentService.php`

---

### Phase 2F: Testing ⏳

**Tasks:**
1. Unit tests untuk DynamicStandardService
2. Component tests untuk SelectiveAspectsModal
3. Integration tests untuk real-time updates
4. E2E tests untuk full flow

---

## AFFECTED COMPONENTS

### Components yang Menampilkan/Menghitung Assessment Data

Semua component ini harus auto-update saat standard di-adjust:

| # | Component | Location | Impact |
|---|-----------|----------|--------|
| 1 | StandardPsikometrik | Pages/GeneralReport | **Edit location** - Potensi |
| 2 | StandardMc | Pages/GeneralReport | **Edit location** - Kompetensi |
| 3 | Dashboard | Pages | Statistics recalculate |
| 4 | GeneralMatching | Pages/GeneralReport | Gap/conclusion change |
| 5 | GeneralMapping | Pages/GeneralReport | Mapping recalculate |
| 6 | GeneralPsyMapping | Pages/GeneralReport | Potensi mapping change |
| 7 | GeneralMcMapping | Pages/GeneralReport | Kompetensi mapping change |
| 8 | SpiderPlot | Pages/GeneralReport | **Axes change dynamically** |
| 9 | RingkasanMcMapping | Pages/GeneralReport | Summary recalculate |
| 10 | RingkasanAssessment | Pages/GeneralReport | Assessment summary change |
| 11 | RankingPsyMapping | Pages/GeneralReport | Potensi ranking change |
| 12 | RankingMcMapping | Pages/GeneralReport | Kompetensi ranking change |
| 13 | RekapRankingAssessment | Pages/GeneralReport | Overall ranking change |
| 14 | TrainingRecommendation | Pages/GeneralReport/Training | Recommendations change |

### Spider Chart Special Handling

**SpiderPlot component** needs special logic:

```php
// Get active aspects from session
$activeAspects = $this->dynamicStandardService->getActiveAspects($templateId);

// Filter data to only include active aspects
$chartData = $assessmentData->filter(function ($item) use ($activeAspects) {
    return isset($activeAspects[$item->aspect_code]) && $activeAspects[$item->aspect_code];
});

// Chart will render with fewer axes (e.g., pentagon → triangle)
```

---

## QUICK REFERENCE

### Key Data Types

```php
// Category Weight: INTEGER 0-100
$categoryWeight = 50; // percent

// Aspect Weight: INTEGER 0-100
$aspectWeight = 25; // percent

// Kompetensi Standard Rating: INTEGER 1-5
$kompetensiRating = 4; // NOT 4.0

// Sub-Aspect Standard Rating (Potensi): INTEGER 1-5
$subAspectRating = 3; // NOT 3.0

// Potensi Aspect Rating: DECIMAL (calculated)
$potensiRating = 3.25; // average dari sub-aspects
```

### Important Constraints

```
✅ Category weights total = 100%
✅ Aspect weights per category = 100%
✅ Minimum 3 aspects active per category
✅ Minimum 1 sub-aspect active per active aspect
✅ Disabled aspect weight = 0%
✅ Kompetensi ratings = INTEGER 1-5
✅ Sub-aspect ratings = INTEGER 1-5
```

---

## CONCLUSION

Dynamic Standard Analysis adalah powerful tool untuk "what-if" analysis tanpa risk mengubah data asli. Dengan session-based approach, user dapat experiment dengan berbagai skenario dan selalu bisa reset ke standar asli.

**Key Success Factors:**
- ✅ Data integrity terjaga (no database changes)
- ✅ Real-time updates across all views
- ✅ User-friendly UI dengan inline editing
- ✅ Validation yang strict untuk ensure accuracy
- ✅ Template-level adjustments untuk consistency

**Next Steps:** Mulai implementasi Phase 2A - Extend DynamicStandardService.
