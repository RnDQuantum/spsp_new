# ConclusionService - Usage Guide

## Overview

`ConclusionService` adalah **single source of truth** untuk semua logic pengkategorian kesimpulan dalam sistem assessment. Service ini menggantikan duplicate methods yang tersebar di berbagai komponen.

## Location

```
app/Services/ConclusionService.php
```

## Jenis Kesimpulan

### 1. Gap-Based Conclusion (Standard - 99% use cases)

Digunakan untuk **hampir semua laporan** kecuali RingkasanAssessment.

**Logic:**
```php
if ($originalGap >= 0) → 'Di Atas Standar'
elseif ($adjustedGap >= 0) → 'Memenuhi Standar'
else → 'Di Bawah Standar'
```

**Digunakan di:**
- Individual Reports: GeneralPsyMapping, GeneralMcMapping, GeneralMapping
- Ranking Reports: RankingPsyMapping, RankingMcMapping, RekapRankingAssessment

**Warna:**
- 🟢 "Di Atas Standar" = `#16a34a` (green-600)
- 🟡 "Memenuhi Standar" = `#facc15` (yellow-400)
- 🔴 "Di Bawah Standar" = `#dc2626` (red-600)

### 2. Potensial-Based Conclusion (Special - RingkasanAssessment only)

Mapping dari gap-based ke user-friendly text.

**Mapping:**
```php
'Di Atas Standar' → 'Sangat Potensial'
'Memenuhi Standar' → 'Potensial'
'Di Bawah Standar' → 'Kurang Potensial'
```

---

## Implementasi di Livewire Component

### Step 1: Import ConclusionService

```php
<?php

namespace App\Livewire\Pages\IndividualReport;

use App\Services\ConclusionService; // ← Add this
use Livewire\Component;
```

### Step 2: Gunakan untuk Gap-Based Conclusion

#### ❌ **BEFORE (Duplicate Method - JANGAN GUNAKAN)**

```php
class GeneralPsyMapping extends Component
{
    // ❌ DUPLICATE METHOD - REMOVE THIS
    private function getConclusionText(float $originalGap, float $adjustedGap): string
    {
        if ($originalGap >= 0) {
            return 'Di Atas Standar';
        } elseif ($adjustedGap >= 0) {
            return 'Memenuhi Standar';
        } else {
            return 'Di Bawah Standar';
        }
    }

    private function calculateTotals(): void
    {
        // ...
        $this->overallConclusion = $this->getConclusionText($originalGap, $adjustedGap);
    }
}
```

#### ✅ **AFTER (Use ConclusionService - GUNAKAN INI)**

```php
class GeneralPsyMapping extends Component
{
    // ✅ NO DUPLICATE METHOD - Use service directly

    private function calculateTotals(): void
    {
        // ...
        $this->overallConclusion = ConclusionService::getGapBasedConclusion(
            $this->totalOriginalGapScore,
            $this->totalGapScore
        );
    }
}
```

### Step 3: Gunakan Helper Methods untuk Styling

#### Get Chart Color

```php
// In Livewire component
$conclusion = 'Di Atas Standar';
$chartColor = ConclusionService::getChartColor($conclusion);
// Returns: '#16a34a'
```

#### Get Tailwind Class

```php
// In Livewire component
$conclusion = 'Memenuhi Standar';
$tailwindClass = ConclusionService::getTailwindClass($conclusion);
// Returns: 'bg-yellow-400 text-gray-900'
```

#### Get All Configuration

```php
// In Livewire component - for pie charts
public function prepareChartData(): void
{
    $config = ConclusionService::getGapConclusionConfig();

    $this->chartLabels = array_keys($config);
    $this->chartColors = array_column($config, 'chartColor');

    // chartLabels = ['Di Atas Standar', 'Memenuhi Standar', 'Di Bawah Standar']
    // chartColors = ['#16a34a', '#facc15', '#dc2626']
}
```

---

## Implementasi di Blade View

### Direct Usage in Blade

```blade
@php
    use App\Services\ConclusionService;
@endphp

{{-- Display conclusion with color --}}
<span class="{{ ConclusionService::getTailwindClass($aspect['conclusion_text']) }}">
    {{ $aspect['conclusion_text'] }}
</span>

{{-- Loop through all conclusion types --}}
@foreach(ConclusionService::getConclusionTypes() as $conclusionType)
    <div class="{{ ConclusionService::getTailwindClass($conclusionType) }}">
        {{ $conclusionType }}
    </div>
@endforeach
```

### Using Component Property (Recommended)

```blade
{{-- In Livewire component property --}}
<span class="{{ $conclusionConfig[$aspect['conclusion_text']]['tailwindClass'] ?? '' }}">
    {{ $aspect['conclusion_text'] }}
</span>
```

```php
// In Livewire component
class RankingPsyMapping extends Component
{
    public function mount(): void
    {
        // Load config once in mount
        $this->conclusionConfig = ConclusionService::getGapConclusionConfig();
    }
}
```

---

## Complete Example: Refactoring Livewire Component

### Before (With Duplicate Methods)

```php
<?php

namespace App\Livewire\Pages\IndividualReport;

use Livewire\Component;

class GeneralPsyMapping extends Component
{
    public $overallConclusion = '';

    // ❌ DUPLICATE METHOD #1
    private function getConclusionText(float $originalGap, float $adjustedGap): string
    {
        if ($originalGap >= 0) {
            return 'Di Atas Standar';
        } elseif ($adjustedGap >= 0) {
            return 'Memenuhi Standar';
        } else {
            return 'Di Bawah Standar';
        }
    }

    // ❌ DUPLICATE METHOD #2
    private function getOverallConclusion(float $totalOriginalGapScore, float $totalAdjustedGapScore): string
    {
        if ($totalOriginalGapScore >= 0) {
            return 'Di Atas Standar';
        } elseif ($totalAdjustedGapScore >= 0) {
            return 'Memenuhi Standar';
        } else {
            return 'Di Bawah Standar';
        }
    }

    private function calculateTotals(): void
    {
        // ... calculate totals

        $this->overallConclusion = $this->getOverallConclusion(
            $this->totalOriginalGapScore,
            $this->totalGapScore
        );
    }
}
```

### After (Using ConclusionService)

```php
<?php

namespace App\Livewire\Pages\IndividualReport;

use App\Services\ConclusionService; // ← Add import
use Livewire\Component;

class GeneralPsyMapping extends Component
{
    public $overallConclusion = '';

    // ✅ NO DUPLICATE METHODS - Use service

    private function calculateTotals(): void
    {
        // ... calculate totals

        // ✅ Use ConclusionService
        $this->overallConclusion = ConclusionService::getGapBasedConclusion(
            $this->totalOriginalGapScore,
            $this->totalGapScore
        );
    }
}
```

---

## API Reference

### Gap-Based Methods

```php
// Get gap-based conclusion text
ConclusionService::getGapBasedConclusion(float $originalGap, float $adjustedGap): string

// Get all gap conclusion configuration
ConclusionService::getGapConclusionConfig(): array

// Get chart color for conclusion
ConclusionService::getChartColor(string $conclusionText, string $type = 'gap'): string

// Get Tailwind class for conclusion
ConclusionService::getTailwindClass(string $conclusionText, string $type = 'gap'): string

// Get all conclusion types
ConclusionService::getConclusionTypes(string $type = 'gap'): array
```

### Potensial-Based Methods (For RingkasanAssessment only)

```php
// Map gap conclusion to potensial conclusion
ConclusionService::getPotensialConclusion(string $gapConclusion): string

// Get potensial conclusion configuration
ConclusionService::getPotensialConclusionConfig(): array

// Example usage:
$gapConclusion = 'Di Atas Standar';
$potensialConclusion = ConclusionService::getPotensialConclusion($gapConclusion);
// Returns: 'Sangat Potensial'
```

---

## Migration Checklist

Saat refactoring komponen lama ke ConclusionService:

- [ ] Import `use App\Services\ConclusionService;`
- [ ] Remove duplicate methods: `getConclusionText()`, `getOverallConclusion()`, `getFinalConclusion()`
- [ ] Replace method calls dengan `ConclusionService::getGapBasedConclusion()`
- [ ] Update chart configuration dengan `ConclusionService::getGapConclusionConfig()`
- [ ] Run `php artisan optimize:clear` untuk clear cache
- [ ] Run `vendor/bin/pint` untuk formatting
- [ ] Test halaman untuk memastikan kesimpulan tampil benar

---

## Testing

Setelah refactoring, pastikan:

1. ✅ Kesimpulan menampilkan text yang benar:
   - "Di Atas Standar" (bukan "Above Standard")
   - "Memenuhi Standar" (bukan "Meets Standard")
   - "Di Bawah Standar" (bukan "Below Standard")

2. ✅ Warna tampil konsisten:
   - Hijau untuk "Di Atas Standar"
   - Kuning untuk "Memenuhi Standar"
   - Merah untuk "Di Bawah Standar"

3. ✅ Tolerance adjustment masih berfungsi (gap berubah sesuai tolerance)

---

## Benefits

✅ **Single Source of Truth** - Logic hanya di 1 tempat
✅ **Konsisten** - Semua komponen menggunakan logic yang sama
✅ **Maintainable** - Ubah sekali, semua komponen update
✅ **Testable** - Mudah di-unit test
✅ **DRY** - Menghilangkan ~200+ lines duplicate code
✅ **Centralized Styling** - Warna dan CSS class terpusat

---

## Support

Jika ada pertanyaan atau menemukan bug, silakan hubungi tim development atau buat issue di repository.
