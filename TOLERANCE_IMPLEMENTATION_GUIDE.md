# Tolerance Feature Implementation Guide

## Overview

Fitur toleransi memungkinkan pengguna untuk menyesuaikan standar penilaian dengan mengurangi nilai standar secara persentase. Toleransi diterapkan secara real-time tanpa mengubah data di database.

**Status Implementasi:**

-   ✅ **GeneralMapping** - Fully implemented
-   ✅ **GeneralPsyMapping** - Fully implemented
-   ✅ **GeneralMcMapping** - Fully implemented
-   ✅ **SpiderPlot** - Fully implemented
-   ✅ **RingkasanAssessment** - Fully implemented

---

## Core Concepts

### 1. **Data Storage vs Display**

| Layer                  | Original Standard  | Adjusted Standard | Notes                                  |
| ---------------------- | ------------------ | ----------------- | -------------------------------------- |
| **Database**           | ✅ Stored          | ❌ Not stored     | Original values never change           |
| **Backend (Livewire)** | ✅ Calculated      | ✅ Calculated     | Both available in component            |
| **Frontend (View)**    | Depends on context | ✅ Displayed      | Table shows adjusted, chart shows both |

### 2. **Tolerance Calculation Formula**

```php
$toleranceFactor = 1 - ($tolerancePercentage / 100);
$adjustedStandard = $originalStandard * $toleranceFactor;
```

**Examples:**

-   Tolerance 0% → Factor 1.0 → Adjusted = Original × 1.0 (no change)
-   Tolerance 10% → Factor 0.9 → Adjusted = Original × 0.9 (90% of original)
-   Tolerance 20% → Factor 0.8 → Adjusted = Original × 0.8 (80% of original)

### 3. **Gap Calculation**

```php
$adjustedGap = $individual - $adjustedStandard;
```

Gap is **always** calculated against the adjusted standard, not the original.

### 4. **Percentage Calculation**

```php
$adjustedPercentage = ($individualScore / $adjustedStandardScore) * 100;
```

Percentage represents: "Individual as percentage of adjusted standard"

### 5. **Conclusion Logic**

```php
if ($percentageScore >= 110) {
    return 'Lebih Memenuhi/More Requirement';
} elseif ($percentageScore >= 100) {
    return 'Memenuhi/Meet Requirement';
} elseif ($percentageScore >= 90) {
    return 'Kurang Memenuhi/Below Requirement';
} else {
    return 'Belum Memenuhi/Under Perform';
}
```

---

## Implementation Steps

### **Step 1: Add Tolerance Property to Component**

```php
// Tolerance percentage (loaded from session)
public int $tolerancePercentage = 10;

public function mount($eventCode, $testNumber): void
{
    // Load tolerance from session
    $this->tolerancePercentage = session('individual_report.tolerance', 10);

    // ... rest of mount logic
}
```

### **Step 2: Add Original Standard Fields for Charts**

```php
// Data for charts
public $chartLabels = [];
public $chartOriginalStandardRatings = [];  // ← NEW
public $chartStandardRatings = [];
public $chartIndividualRatings = [];
public $chartOriginalStandardScores = [];   // ← NEW
public $chartStandardScores = [];
public $chartIndividualScores = [];
```

### **Step 3: Calculate Adjusted Values in Data Loading**

```php
private function loadCategoryAspects(int $categoryTypeId): array
{
    $aspectAssessments = AspectAssessment::with('aspect')
        ->where('participant_id', $this->participant->id)
        ->whereIn('aspect_id', $aspectIds)
        ->get();

    return $aspectAssessments->map(function ($assessment) {
        // 1. Get original values from database
        $originalStandardRating = (float) $assessment->standard_rating;
        $originalStandardScore = (float) $assessment->standard_score;
        $individualRating = (float) $assessment->individual_rating;
        $individualScore = (float) $assessment->individual_score;

        // 2. Calculate adjusted standard based on tolerance
        $toleranceFactor = 1 - ($this->tolerancePercentage / 100);
        $adjustedStandardRating = $originalStandardRating * $toleranceFactor;
        $adjustedStandardScore = $originalStandardScore * $toleranceFactor;

        // 3. Recalculate gap based on adjusted standard
        $adjustedGapRating = $individualRating - $adjustedStandardRating;
        $adjustedGapScore = $individualScore - $adjustedStandardScore;

        // 4. Calculate percentage based on adjusted standard
        $adjustedPercentage = $adjustedStandardScore > 0
            ? ($individualScore / $adjustedStandardScore) * 100
            : 0;

        return [
            'name' => $assessment->aspect->name,
            'weight_percentage' => $assessment->aspect->weight_percentage,
            'original_standard_rating' => $originalStandardRating,  // ← NEW
            'original_standard_score' => $originalStandardScore,    // ← NEW
            'standard_rating' => $adjustedStandardRating,
            'standard_score' => $adjustedStandardScore,
            'individual_rating' => $individualRating,
            'individual_score' => $individualScore,
            'gap_rating' => $adjustedGapRating,
            'gap_score' => $adjustedGapScore,
            'percentage_score' => $adjustedPercentage,              // ← UPDATED
            'conclusion_text' => $this->getConclusionText($adjustedPercentage),
        ];
    })->toArray();
}
```

### **Step 4: Reset Totals Before Recalculation**

```php
private function calculateTotals(): void
{
    // ⚠️ CRITICAL: Reset totals before recalculating
    $this->totalStandardRating = 0;
    $this->totalStandardScore = 0;
    $this->totalIndividualRating = 0;
    $this->totalIndividualScore = 0;
    $this->totalGapRating = 0;
    $this->totalGapScore = 0;

    foreach ($this->aspectsData as $aspect) {
        $this->totalStandardRating += $aspect['standard_rating'];
        $this->totalStandardScore += $aspect['standard_score'];
        $this->totalIndividualRating += $aspect['individual_rating'];
        $this->totalIndividualScore += $aspect['individual_score'];
        $this->totalGapRating += $aspect['gap_rating'];
        $this->totalGapScore += $aspect['gap_score'];
    }

    $this->overallConclusion = $this->getOverallConclusion($this->totalGapScore);
}
```

### **Step 5: Prepare Chart Data with Original Values**

```php
private function prepareChartData(): void
{
    // Reset chart data arrays before repopulating
    $this->chartLabels = [];
    $this->chartOriginalStandardRatings = [];  // ← NEW
    $this->chartStandardRatings = [];
    $this->chartIndividualRatings = [];
    $this->chartOriginalStandardScores = [];   // ← NEW
    $this->chartStandardScores = [];
    $this->chartIndividualScores = [];

    foreach ($this->aspectsData as $aspect) {
        $this->chartLabels[] = $aspect['name'];
        $this->chartOriginalStandardRatings[] = round($aspect['original_standard_rating'], 2);
        $this->chartStandardRatings[] = round($aspect['standard_rating'], 2);
        $this->chartIndividualRatings[] = round($aspect['individual_rating'], 2);
        $this->chartOriginalStandardScores[] = round($aspect['original_standard_score'], 2);
        $this->chartStandardScores[] = round($aspect['standard_score'], 2);
        $this->chartIndividualScores[] = round($aspect['individual_score'], 2);
    }
}
```

### **Step 6: Update Conclusion Logic**

```php
private function getConclusionText(float $percentageScore): string
{
    // Conclusion based on percentage score relative to adjusted standard
    if ($percentageScore >= 110) {
        return 'Lebih Memenuhi/More Requirement';
    } elseif ($percentageScore >= 100) {
        return 'Memenuhi/Meet Requirement';
    } elseif ($percentageScore >= 90) {
        return 'Kurang Memenuhi/Below Requirement';
    } else {
        return 'Belum Memenuhi/Under Perform';
    }
}
```

### **Step 7: Add Tolerance Update Listener**

```php
/**
 * Listen to tolerance updates from ToleranceSelector component
 */
protected $listeners = ['tolerance-updated' => 'handleToleranceUpdate'];

/**
 * Handle tolerance update from child component
 */
public function handleToleranceUpdate(int $tolerance): void
{
    $this->tolerancePercentage = $tolerance;

    // Reload aspects data with new tolerance
    $this->loadAspectsData();

    // Recalculate totals
    $this->calculateTotals();

    // Update chart data
    $this->prepareChartData();

    // Get updated summary statistics
    $summary = $this->getPassingSummary();

    // Dispatch event to update charts
    $this->dispatch('chartDataUpdated', [
        'tolerance' => $tolerance,
        'labels' => $this->chartLabels,
        'originalStandardRatings' => $this->chartOriginalStandardRatings,  // ← NEW
        'standardRatings' => $this->chartStandardRatings,
        'individualRatings' => $this->chartIndividualRatings,
        'originalStandardScores' => $this->chartOriginalStandardScores,    // ← NEW
        'standardScores' => $this->chartStandardScores,
        'individualScores' => $this->chartIndividualScores,
    ]);

    // Dispatch event to update summary statistics in ToleranceSelector
    $this->dispatch('summary-updated', [
        'passing' => $summary['passing'],
        'total' => $summary['total'],
        'percentage' => $summary['percentage'],
    ]);
}
```

### **Step 8: Update View - Table Header**

```blade
<th class="border border-black px-3 py-2 font-semibold" colspan="2">
    <span x-data x-text="$wire.tolerancePercentage > 0 ? 'Standard (-' + $wire.tolerancePercentage + '%)' : 'Standard'"></span>
</th>
```

### **Step 9: Update View - Chart with 3 Lines**

```javascript
// Chart setup
const chartLabels = @js($chartLabels);
let originalStandardRatings = @js($chartOriginalStandardRatings);
let standardRatings = @js($chartStandardRatings);
const individualRatings = @js($chartIndividualRatings);
let tolerancePercentage = @js($tolerancePercentage);

chartInstance = new Chart(ctx, {
    type: 'radar',
    data: {
        labels: chartLabels,
        datasets: [{
            label: 'Standar',
            data: originalStandardRatings,  // Original (solid black)
            borderColor: '#000000',
            backgroundColor: 'rgba(0, 0, 0, 0.05)',
            borderWidth: 2,
            pointRadius: 3,
            pointBackgroundColor: '#000000'
        }, {
            label: `Toleransi ${tolerancePercentage}%`,
            data: standardRatings,  // Adjusted (dashed gray)
            borderColor: '#6B7280',
            backgroundColor: 'transparent',
            borderWidth: 1.5,
            borderDash: [5, 5],
            pointRadius: 2,
            pointBackgroundColor: '#6B7280'
        }, {
            label: participantName,
            data: individualRatings,  // Individual (solid red)
            borderColor: '#DC2626',
            backgroundColor: 'rgba(220, 38, 38, 0.05)',
            borderWidth: 2,
            pointRadius: 3,
            pointBackgroundColor: '#DC2626'
        }]
    },
    // ... options
});
```

### **Step 10: Update View - Chart Legend**

```blade
<div class="flex justify-center text-sm gap-8 text-gray-900 mb-8">
    <span class="flex items-center gap-2">
        <span class="inline-block w-10 border-b-2 border-black"></span>
        <span class="font-semibold">Standar</span>
    </span>
    <span class="flex items-center gap-2">
        <span class="inline-block w-10" style="border-bottom: 2px dashed #6B7280;"></span>
        <span x-data x-text="'Toleransi ' + $wire.tolerancePercentage + '%'"></span>
    </span>
    <span class="flex items-center gap-2">
        <span class="inline-block w-10 border-b-2 border-red-600"></span>
        <span class="text-red-600 font-bold">{{ $participant->name }}</span>
    </span>
</div>
```

### **Step 11: Update View - Chart Data Update Listener**

```javascript
Livewire.on('chartDataUpdated', function(data) {
    let chartData = Array.isArray(data) && data.length > 0 ? data[0] : data;
    if (window.ratingChart_{{ $chartId }} && chartData) {
        tolerancePercentage = chartData.tolerance;
        originalStandardRatings = chartData.originalStandardRatings;
        standardRatings = chartData.standardRatings;

        // Update all three datasets
        window.ratingChart_{{ $chartId }}.data.datasets[0].data = chartData.originalStandardRatings;
        window.ratingChart_{{ $chartId }}.data.datasets[1].label = `Toleransi ${tolerancePercentage}%`;
        window.ratingChart_{{ $chartId }}.data.datasets[1].data = chartData.standardRatings;
        // Dataset[2] (individual) doesn't change
        window.ratingChart_{{ $chartId }}.update('active');
    }
});
```

### **Step 12: Include ToleranceSelector Component**

```blade
<!-- Tolerance Selector Component -->
@php
    $summary = $this->getPassingSummary();
@endphp
@livewire('components.tolerance-selector', [
    'passing' => $summary['passing'],
    'total' => $summary['total']
])
```

---

## Visual Design Specs

### **Chart Styling**

| Element              | Color             | Style        | Width |
| -------------------- | ----------------- | ------------ | ----- |
| **Standar Original** | `#000000` (Black) | Solid        | 2px   |
| **Standar Adjusted** | `#6B7280` (Gray)  | Dashed [5,5] | 1.5px |
| **Individual**       | `#DC2626` (Red)   | Solid        | 2px   |

### **Table Header Dynamic Text**

-   Tolerance 0%: `"Standard"`
-   Tolerance 10%: `"Standard (-10%)"`
-   Tolerance 20%: `"Standard (-20%)"`

---

## Common Pitfalls & Solutions

### ❌ **Problem 1: Total Values Keep Increasing**

**Symptom:** Total Individual/Standard keeps growing when tolerance changes.

**Cause:** Forgot to reset totals before recalculating.

**Solution:**

```php
private function calculateTotals(): void
{
    // ✅ MUST reset before loop
    $this->totalStandardRating = 0;
    $this->totalIndividualRating = 0;
    // ... reset all totals

    foreach ($this->aspectsData as $aspect) {
        $this->totalStandardRating += $aspect['standard_rating'];
        // ...
    }
}
```

### ❌ **Problem 2: Chart Data Duplicates**

**Symptom:** Chart labels/data accumulate on tolerance change.

**Cause:** Forgot to reset chart arrays.

**Solution:**

```php
private function prepareChartData(): void
{
    // ✅ MUST reset arrays
    $this->chartLabels = [];
    $this->chartStandardRatings = [];
    // ... reset all arrays

    foreach ($this->aspectsData as $aspect) {
        $this->chartLabels[] = $aspect['name'];
        // ...
    }
}
```

### ❌ **Problem 3: Wrong Conclusion Logic**

**Symptom:** Aspect shows "Memenuhi" but percentage < 100%.

**Cause:** Using gap instead of percentage for conclusion.

**Solution:**

```php
// ❌ WRONG - using gap
if ($gapRating >= 0) {
    return 'Memenuhi';
}

// ✅ CORRECT - using percentage
if ($percentageScore >= 100) {
    return 'Memenuhi/Meet Requirement';
}
```

### ❌ **Problem 4: Chart Only Shows 2 Lines**

**Symptom:** Original standard line is missing.

**Cause:** Not storing/passing original standard values.

**Solution:**

```php
// ✅ Store both original and adjusted
return [
    'original_standard_rating' => $originalStandardRating,  // For chart
    'standard_rating' => $adjustedStandardRating,           // For table
    // ...
];
```

---

## Testing Checklist

When implementing tolerance on a new component, verify:

-   [ ] **Tolerance 0%**: Values match database exactly
-   [ ] **Tolerance 10%**: Standard values reduced to 90%
-   [ ] **Tolerance 20%**: Standard values reduced to 80%
-   [ ] **Total Individual**: Never changes when tolerance changes
-   [ ] **Total Standard**: Decreases as tolerance increases
-   [ ] **Total Gap**: Changes based on adjusted standard
-   [ ] **Percentage**: Increases as tolerance increases (denominator decreases)
-   [ ] **Conclusion**: Updates correctly based on percentage thresholds
-   [ ] **Chart**: Shows 3 lines (original, adjusted, individual)
-   [ ] **Chart Legend**: Labels update dynamically
-   [ ] **Table Header**: Shows tolerance indicator
-   [ ] **Session Persistence**: Tolerance value persists across page reloads

---

## Example Calculation Walkthrough

**Given:**

-   Original Standard Score: 65.80
-   Individual Score: 62.86
-   Tolerance: 10%

**Step-by-step:**

1. **Calculate tolerance factor:**

    ```
    Factor = 1 - (10 / 100) = 0.9
    ```

2. **Calculate adjusted standard:**

    ```
    Adjusted = 65.80 × 0.9 = 59.22
    ```

3. **Calculate gap:**

    ```
    Gap = 62.86 - 59.22 = +3.64
    ```

4. **Calculate percentage:**

    ```
    Percentage = (62.86 / 59.22) × 100 = 106.15%
    ```

5. **Determine conclusion:**
    ```
    106.15% >= 100 → "Memenuhi/Meet Requirement" ✅
    ```

**Comparison:**

| Tolerance | Adjusted Std | Gap    | %       | Conclusion      |
| --------- | ------------ | ------ | ------- | --------------- |
| 0%        | 65.80        | -2.94  | 95.53%  | Kurang Memenuhi |
| 10%       | 59.22        | +3.64  | 106.15% | Memenuhi ✅     |
| 20%       | 52.64        | +10.22 | 119.40% | Lebih Memenuhi  |

---

## Files Modified in GeneralMapping

### Backend

-   `app/Livewire/Pages/IndividualReport/GeneralMapping.php`
    -   Added `$tolerancePercentage` property
    -   Added original standard fields to chart data
    -   Updated `loadCategoryAspects()` to calculate adjusted values
    -   Updated `calculateTotals()` to reset before recalculating
    -   Updated `prepareChartData()` to include original values
    -   Updated `getConclusionText()` to use percentage logic
    -   Updated `handleToleranceUpdate()` to dispatch chart data

### Frontend

-   `resources/views/livewire/pages/individual-report/general-mapping.blade.php`
    -   Updated table header to show tolerance indicator
    -   Updated chart initialization to include 3 datasets
    -   Updated chart legend to show all 3 lines
    -   Updated chart data listener to handle original values

---

## Related Components

The following components also use tolerance and should follow the same pattern:

1. **GeneralPsyMapping** - Psychology aspects only
2. **GeneralMcMapping** - Management competency only
3. **GeneralMatching** - Detailed sub-aspects view
4. **SpiderPlot** - Simplified chart view
5. **RingkasanAssessment** - Summary with overall conclusion

---

## Session Storage

Tolerance value is stored in session:

```php
session(['individual_report.tolerance' => $value]);
$tolerance = session('individual_report.tolerance', 10); // default 10%
```

This allows tolerance to persist across different pages in the Individual Report section.

---

## Questions for Next Implementation

When implementing on other components, consider:

1. Does this component have sub-aspects? (like GeneralMatching)
2. Does this component need a passing summary? (for ToleranceSelector)
3. Does this component have multiple charts or just one?
4. What is the original conclusion logic? (might differ per component)
5. Are there any component-specific calculations that need adjustment?

---

## Maintenance Notes

-   **Database schema**: No changes needed - all calculations are runtime only
-   **Performance**: Tolerance calculations are lightweight (simple multiplication)
-   **Backwards compatibility**: Setting tolerance to 0% gives exact original behavior
-   **Future enhancements**: Could add tolerance presets, per-aspect tolerance, etc.

---

## Contact & Support

For questions about this implementation, refer to chat history with context about:

-   Original bug: Total values increasing on tolerance change
-   Design decision: Use percentage (not gap) for conclusions
-   Visual design: 3-line charts with proper legends
-   Calculation formulas: Adjusted standard, gap, percentage

Last updated: 2025-01-14 (All components completed: GeneralMapping, GeneralPsyMapping, GeneralMcMapping, SpiderPlot, RingkasanAssessment)
