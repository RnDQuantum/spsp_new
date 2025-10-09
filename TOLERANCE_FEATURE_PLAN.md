# 📊 TOLERANCE FEATURE IMPLEMENTATION PLAN

**Project:** SPSP Analytics Dashboard - General Mapping
**Feature:** Dynamic Tolerance for Assessment Analysis
**Created:** 2025-10-09
**Status:** 📋 Planning Phase

---

## 🎯 OBJECTIVE

Menambahkan fitur **toleransi dinamis** pada halaman General Mapping untuk keperluan **analisis dan presentasi**, tanpa mengubah data asli di database.

### **Use Case:**

User ingin melihat "what-if scenario":
- **Toleransi 0%** → Standar penuh, 10 peserta lulus
- **Toleransi 10%** → Standar diturunkan 10%, 15 peserta lulus
- **Toleransi 15%** → Standar diturunkan 15%, 20 peserta lulus

**PENTING:** Data asli di database tetap sama, hanya threshold penilaian yang berubah untuk analisis.

---

## 📋 BUSINESS REQUIREMENTS

### **1. Konsep Toleransi**

Toleransi adalah **persentase penurunan standar** untuk analisis:

```
Standard Rating: 4.0
Toleransi 10%: 4.0 × 10% = 0.4

Individual Rating: 3.7
Gap: 3.7 - 4.0 = -0.3

Evaluasi dengan Toleransi 10%:
- Batas toleransi: -0.4
- Gap (-0.3) >= -0.4 → ✅ LULUS (Memenuhi Standard)

Evaluasi dengan Toleransi 0%:
- Batas toleransi: 0
- Gap (-0.3) < 0 → ❌ TIDAK LULUS (Kurang Memenuhi)
```

### **2. Scope Toleransi**

✅ **Yang Dipengaruhi Toleransi:**
- Conclusion text per aspek (tabel)
- Garis toleransi di spider chart
- Summary total kelulusan (berapa peserta lulus)
- Visual comparison

❌ **Yang TIDAK Berubah:**
- Data di database (tetap original)
- Rating & Score values
- Gap calculation (tetap: individual - standard)
- Standard rating values

### **3. User Interface Requirements**

- Dropdown/slider untuk pilih toleransi (0%, 5%, 10%, 15%, 20%)
- Real-time update saat toleransi berubah
- Indicator: "Analisis menggunakan toleransi X%"
- Warning: "Data asli tidak berubah, toleransi hanya untuk analisis"
- Summary: "Dengan toleransi X%, jumlah peserta yang memenuhi standard: Y orang"

---

## 🏗️ TECHNICAL IMPLEMENTATION

### **File yang Akan Dimodifikasi:**

1. ✅ `app/Livewire/Pages/IndividualReport/GeneralMapping.php`
2. ✅ `resources/views/livewire/pages/individual-report/general-mapping.blade.php`

### **Step-by-Step Implementation:**

---

### **STEP 1: Update Livewire Component**

**File:** `app/Livewire/Pages/IndividualReport/GeneralMapping.php`

#### **1.1 Tambah Property untuk Toleransi**

```php
// Tolerance percentage (default 10%)
public $tolerancePercentage = 10;

// Available tolerance options
public $toleranceOptions = [
    0 => '0% (Standard Penuh)',
    5 => '5%',
    10 => '10%',
    15 => '15%',
    20 => '20%',
];
```

#### **1.2 Update Method `getConclusionText()`**

**Before:**
```php
private function getConclusionText(float $gapRating): string
{
    if ($gapRating > 0.5) {
        return 'Lebih Memenuhi/More Requirement';
    } elseif ($gapRating >= -0.5) {
        return 'Memenuhi/Meet Requirement';
    } elseif ($gapRating >= -1.0) {
        return 'Kurang Memenuhi/Below Requirement';
    } else {
        return 'Belum Memenuhi/Under Perform';
    }
}
```

**After:**
```php
private function getConclusionText(float $gapRating, float $standardRating): string
{
    // Calculate tolerance threshold based on standard rating
    $toleranceThreshold = -($standardRating * ($this->tolerancePercentage / 100));

    if ($gapRating >= 0) {
        return 'Lebih Memenuhi/More Requirement';
    } elseif ($gapRating >= $toleranceThreshold) {
        return 'Memenuhi/Meet Requirement';
    } elseif ($gapRating >= ($toleranceThreshold * 2)) {
        return 'Kurang Memenuhi/Below Requirement';
    } else {
        return 'Belum Memenuhi/Under Perform';
    }
}
```

#### **1.3 Update Method `loadCategoryAspects()`**

```php
private function loadCategoryAspects(int $categoryTypeId): array
{
    // ... existing code ...

    return $aspectAssessments->map(function ($assessment) {
        return [
            'name' => $assessment->aspect->name,
            'weight_percentage' => $assessment->aspect->weight_percentage,
            'standard_rating' => $assessment->standard_rating,
            'standard_score' => $assessment->standard_score,
            'individual_rating' => $assessment->individual_rating,
            'individual_score' => $assessment->individual_score,
            'gap_rating' => $assessment->gap_rating,
            'gap_score' => $assessment->gap_score,
            'percentage_score' => $assessment->percentage_score,
            'conclusion_text' => $this->getConclusionText(
                $assessment->gap_rating,
                $assessment->standard_rating  // ← Pass standard rating
            ),
        ];
    })->toArray();
}
```

#### **1.4 Tambah Method untuk Recalculate saat Toleransi Berubah**

```php
/**
 * Called when tolerance percentage is updated
 */
public function updatedTolerancePercentage(): void
{
    // Reload aspects data with new tolerance
    $this->loadAspectsData();

    // Recalculate totals
    $this->calculateTotals();

    // Update chart data
    $this->prepareChartData();
}
```

#### **1.5 Tambah Method untuk Summary Statistics**

```php
/**
 * Get summary statistics based on tolerance
 */
public function getPassingSummary(): array
{
    $totalAspects = count($this->aspectsData);
    $passingAspects = 0;

    foreach ($this->aspectsData as $aspect) {
        if (str_contains($aspect['conclusion_text'], 'Memenuhi') ||
            str_contains($aspect['conclusion_text'], 'Lebih Memenuhi')) {
            $passingAspects++;
        }
    }

    return [
        'total' => $totalAspects,
        'passing' => $passingAspects,
        'percentage' => $totalAspects > 0 ? round(($passingAspects / $totalAspects) * 100) : 0,
    ];
}
```

---

### **STEP 2: Update Blade Template**

**File:** `resources/views/livewire/pages/individual-report/general-mapping.blade.php`

#### **2.1 Tambah Tolerance Selector di Header**

Insert setelah header, sebelum tabel:

```blade
<!-- Tolerance Selector Section -->
<div class="p-4 bg-yellow-50 border-b-2 border-yellow-300">
    <div class="flex items-center justify-between max-w-4xl mx-auto">
        <div class="flex items-center gap-4">
            <label class="font-semibold text-gray-700">
                🔍 Toleransi Analisis:
            </label>
            <select wire:model.live="tolerancePercentage"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                @foreach ($toleranceOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        @php
            $summary = $this->getPassingSummary();
        @endphp

        <div class="text-sm">
            <span class="font-semibold text-green-600">
                {{ $summary['passing'] }} dari {{ $summary['total'] }} aspek
            </span>
            <span class="text-gray-600">
                ({{ $summary['percentage'] }}%)
            </span>
            memenuhi standard
        </div>
    </div>

    <div class="mt-2 text-center">
        <p class="text-xs text-gray-600">
            <span class="inline-block w-3 h-3 bg-yellow-400 rounded-full mr-1"></span>
            <strong>Catatan:</strong> Toleransi hanya untuk keperluan analisis dan presentasi.
            Data asli tidak berubah.
        </p>
    </div>
</div>
```

#### **2.2 Update Chart JavaScript untuk Toleransi Dinamis**

Update bagian calculation tolerance:

```javascript
// Calculate tolerance (standard - tolerance%)
const tolerancePercentage = {{ $tolerancePercentage }};
const toleranceRatings = standardRatings.map(val => val * (1 - tolerancePercentage / 100));
const toleranceScores = standardScores.map(val => val * (1 - tolerancePercentage / 100));
```

#### **2.3 Update Legend untuk Menampilkan Toleransi Aktif**

```blade
<div class="flex justify-center text-sm gap-8 text-gray-900 mb-8">
    <span class="flex items-center gap-2">
        <span class="inline-block w-10 border-b-2 border-black"></span>
        <span class="font-semibold">Standar</span>
    </span>
    <span class="flex items-center gap-2">
        <span class="inline-block w-10 border-b-2 border-red-600"></span>
        <span class="text-red-600 font-bold">{{ $participant->name }}</span>
    </span>
    <span class="flex items-center gap-2">
        <span class="inline-block w-10" style="border-bottom: 1px dashed #6B7280;"></span>
        <span>Toleransi {{ $tolerancePercentage }}%</span>
    </span>
</div>
```

#### **2.4 Tambah Visual Indicator di Tabel**

Update conclusion text cell untuk highlight toleransi:

```blade
<td class="border border-black px-3 py-2">
    <span class="@if(str_contains($aspect['conclusion_text'], 'Memenuhi')) text-green-600 font-semibold @endif">
        {{ $aspect['conclusion_text'] }}
    </span>

    @if($tolerancePercentage > 0 && $aspect['gap_rating'] < 0 && $aspect['gap_rating'] >= -($aspect['standard_rating'] * ($tolerancePercentage / 100)))
        <span class="text-xs text-yellow-600 block">
            ⚠️ Lulus dengan toleransi
        </span>
    @endif
</td>
```

---

### **STEP 3: Optional Enhancements**

#### **3.1 Export dengan Toleransi**

Tambah button export PDF/Excel dengan toleransi yang dipilih:

```blade
<button wire:click="exportWithTolerance" class="btn btn-primary">
    📄 Export dengan Toleransi {{ $tolerancePercentage }}%
</button>
```

#### **3.2 Comparison Table**

Tambah tabel perbandingan di bawah chart:

```blade
<!-- Tolerance Comparison Table -->
<div class="p-4 bg-gray-50">
    <h3 class="font-bold mb-4">Perbandingan Toleransi</h3>
    <table class="min-w-full text-xs">
        <thead>
            <tr>
                <th>Toleransi</th>
                <th>Aspek Lulus</th>
                <th>Persentase</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>0% (Standard)</td>
                <td>10</td>
                <td>71%</td>
            </tr>
            <tr class="bg-yellow-50">
                <td>10% (Aktif)</td>
                <td>12</td>
                <td>86%</td>
            </tr>
            <tr>
                <td>20%</td>
                <td>14</td>
                <td>100%</td>
            </tr>
        </tbody>
    </table>
</div>
```

#### **3.3 Historical Tolerance Settings**

Simpan riwayat toleransi yang digunakan untuk analisis:

```php
// Optional: Log tolerance usage
Log::info('Tolerance analysis', [
    'participant' => $this->participant->test_number,
    'tolerance' => $this->tolerancePercentage,
    'passing_aspects' => $summary['passing'],
    'timestamp' => now(),
]);
```

---

## 🧪 TESTING CHECKLIST

### **Unit Tests:**

- [ ] Test `getConclusionText()` dengan berbagai tolerance (0%, 10%, 20%)
- [ ] Test `getPassingSummary()` menghitung correctly
- [ ] Test `updatedTolerancePercentage()` trigger recalculation

### **Feature Tests:**

- [ ] User ubah toleransi → conclusion text update
- [ ] User ubah toleransi → chart update
- [ ] User ubah toleransi → summary statistics update
- [ ] Toleransi 0% → hasil sama dengan database asli
- [ ] Toleransi 20% → lebih banyak aspek lulus

### **Browser Tests:**

- [ ] Dropdown toleransi berfungsi
- [ ] Chart update real-time
- [ ] Table update real-time
- [ ] Loading state saat update
- [ ] Mobile responsive

---

## 📊 EXAMPLE CALCULATION

### **Test Case:**

**Aspek:** Integritas
- Standard Rating: 4.0
- Individual Rating: 3.6
- Gap Rating: -0.4

**Evaluasi dengan berbagai toleransi:**

| Toleransi | Threshold | Gap vs Threshold | Conclusion |
|-----------|-----------|------------------|------------|
| 0% | 0.0 | -0.4 < 0.0 | ❌ Kurang Memenuhi |
| 5% | -0.2 | -0.4 < -0.2 | ❌ Kurang Memenuhi |
| 10% | -0.4 | -0.4 >= -0.4 | ✅ Memenuhi (toleransi) |
| 15% | -0.6 | -0.4 >= -0.6 | ✅ Memenuhi (toleransi) |
| 20% | -0.8 | -0.4 >= -0.8 | ✅ Memenuhi (toleransi) |

---

## 🚀 DEPLOYMENT STEPS

1. ✅ Backup database
2. ✅ Update `GeneralMapping.php` component
3. ✅ Update `general-mapping.blade.php` view
4. ✅ Test di local environment
5. ✅ Run Laravel Pint: `vendor/bin/pint`
6. ✅ Test dengan data real
7. ✅ Deploy to staging
8. ✅ User acceptance testing
9. ✅ Deploy to production
10. ✅ Monitor logs

---

## 📝 NOTES & CONSIDERATIONS

### **Important Points:**

1. **Data Integrity:** Toleransi TIDAK mengubah data di database
2. **Performance:** Recalculation saat ubah toleransi harus cepat (<100ms)
3. **User Education:** Perlu dokumentasi/tutorial untuk user
4. **Export:** Pastikan export mencantumkan toleransi yang digunakan
5. **Audit Trail:** Optional: log toleransi yang digunakan untuk analisis

### **Potential Issues:**

1. **Confusion:** User bisa bingung antara data asli vs toleransi
   - **Solution:** Clear labeling dan warning message

2. **Misuse:** User bisa misinterpret toleransi sebagai data asli
   - **Solution:** Watermark "Analisis dengan toleransi X%" di export

3. **Performance:** Recalculation untuk banyak data bisa lambat
   - **Solution:** Loading indicator & optimize calculation

---

## 🎯 SUCCESS CRITERIA

✅ User bisa ubah toleransi dengan mudah
✅ Conclusion text update sesuai toleransi
✅ Chart menampilkan garis toleransi dengan benar
✅ Summary statistics akurat
✅ Performance tetap cepat (<100ms)
✅ Data asli di database tidak berubah
✅ Clear indication bahwa ini analisis mode

---

## 📚 RELATED DOCUMENTATION

- [ASSESSMENT_CALCULATION_FLOW.md](./ASSESSMENT_CALCULATION_FLOW.md) - Logika perhitungan assessment
- [DATABASE_AND_ASSESSMENT_LOGIC.md](./DATABASE_AND_ASSESSMENT_LOGIC.md) - Struktur database
- [API_SPECIFICATION.md](./API_SPECIFICATION.md) - API specification

---

**Status:** 📋 Ready for Implementation
**Estimated Time:** 4-6 hours
**Priority:** Medium
**Assigned To:** [To be assigned]
**Review Required:** Yes

---

**Created by:** Claude Code Assistant
**Last Updated:** 2025-10-09
