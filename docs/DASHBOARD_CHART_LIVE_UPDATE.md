# Dashboard Chart Live Update Implementation

## Masalah Awal

Dashboard assessment system memiliki UX yang "kasar" karena memerlukan full page reload setiap kali:
1. User memilih/deselect participant
2. User mengganti posisi/jabatan (yang otomatis reset participant)
3. User mengganti event

### Kondisi Sebelum Fix
- Chart tidak bisa live update tanpa reload
- Menggunakan modal loading untuk menutupi reload penuh (`window.location.reload()`)
- Dashboard tidak support `wire:navigate` (SPA-like) seperti halaman lain
- Menggunakan session `force_reload` hack untuk trigger reload

### Case Chart
Dashboard menampilkan 3 radar chart (Potensi, Kompetensi, General) dengan 2 mode:

**Mode 1: Tanpa Participant (Standard View)**
- 2 datasets: Standard (hijau) + Tolerance (kuning)

**Mode 2: Dengan Participant Selected**
- 3 datasets: Participant (hijau) + Standard (merah) + Tolerance (kuning)

## Root Cause Analysis

### Issue #1: Unnecessary Full Reload
File: `app/Livewire/Pages/Dashboard.php`

Handler methods menggunakan `window.location.reload()`:
```php
// BEFORE - Line ~117-194
public function handleParticipantSelected(?int $participantId): void
{
    if ($wasNull && $willBeSelected) {
        $this->showLoading('...');
        $this->js('setTimeout(() => window.location.reload(), 1000)');
    }
}
```

### Issue #2: Session Force Reload Hack
File: `resources/views/livewire/pages/dashboard.blade.php`

```blade
<!-- BEFORE - Line ~2-6 -->
@if (session('force_reload'))
    <script>
        window.location.reload();
    </script>
@endif
```

### Issue #3: Chart Tidak Reset Property Participant
File: `app/Livewire/Pages/Dashboard.php` line 307-310

```php
// BEFORE
} else {
    // No participant selected
    $this->loadStandardAspectsData();
    $this->prepareStandardChartData();
    // ❌ Missing: $this->participant = null;
}
```

### Issue #4: Participant Selector Preserve Logic
File: `app/Livewire/Components/ParticipantSelector.php` line 84-95

```php
// BEFORE
public function handlePositionSelected(?int $positionFormationId): void
{
    $previousParticipantId = $this->participantId;
    $this->loadAvailableParticipants();

    // ❌ Preserved participant if still valid in new position
    if ($previousParticipantId && $this->isValidParticipantId($previousParticipantId)) {
        $this->participantId = $previousParticipantId;
    }
}
```

### Issue #5: Grid Lines Tertutup Dataset
Chart.js default z-index tidak prioritas grid elements, sehingga garis radar abu-abu tertutup oleh dataset berwarna.

## Solusi

### Fix #1: Remove Full Reload - Use Livewire Live Update
File: [`app/Livewire/Pages/Dashboard.php:117-194`](app/Livewire/Pages/Dashboard.php#L117-L194)

```php
// AFTER - Semua handler method
public function handleEventSelected(?string $eventCode): void
{
    Log::info('Dashboard: handleEventSelected called', ['eventCode' => $eventCode]);
    $this->clearCache();
    $this->loadDashboardData();
    $this->dispatchChartUpdate();
    Log::info('Dashboard: Event filter updated successfully');
}

public function handlePositionSelected(?int $positionFormationId): void
{
    Log::info('Dashboard: handlePositionSelected called', ['positionFormationId' => $positionFormationId]);
    $this->clearCache();
    $this->loadDashboardData();
    $this->dispatchChartUpdate();
    Log::info('Dashboard: Position filter updated successfully');
}

public function handleParticipantSelected(?int $participantId): void
{
    Log::info('Dashboard: handleParticipantSelected called', ['participantId' => $participantId]);
    $this->clearCache();
    $this->loadDashboardData();
    $this->dispatchChartUpdate();
    Log::info('Dashboard: Participant filter updated successfully', [
        'participantId' => $participantId,
        'hasParticipant' => $this->participant !== null,
    ]);
}

public function handleParticipantReset(): void
{
    Log::info('Dashboard: handleParticipantReset called');
    $this->clearCache();
    $this->loadDashboardData();
    $this->dispatchChartUpdate();
    Log::info('Dashboard: Participant filter reset, showing standard data');
}
```

**Pattern:** `clearCache()` → `loadDashboardData()` → `dispatchChartUpdate()`

### Fix #2: Remove Session Force Reload
File: [`resources/views/livewire/pages/dashboard.blade.php:1-10`](resources/views/livewire/pages/dashboard.blade.php#L1-L10)

```blade
<!-- Removed session force_reload check -->
<div>
    <!-- Loading Overlay - DARK MODE READY -->
    @if ($isLoading)
        ...
```

### Fix #3: Explicit Participant Reset
File: [`app/Livewire/Pages/Dashboard.php:307-310`](app/Livewire/Pages/Dashboard.php#L307-L310)

```php
} else {
    // No participant selected, reset participant and show only standard data
    $this->participant = null; // ✅ EXPLICIT RESET!
    $this->loadStandardAspectsData();
    $this->prepareStandardChartData();

    Log::info('Dashboard: Standard data loaded', [
        'potensiAspectsCount' => count($this->potensiAspectsData),
        'kompetensiAspectsCount' => count($this->kompetensiAspectsData),
        'totalAspectsCount' => count($this->allAspectsData),
    ]);
}
```

### Fix #4: Force Reset Participant on Position Change
File: [`app/Livewire/Components/ParticipantSelector.php:84-95`](app/Livewire/Components/ParticipantSelector.php#L84-L95)

```php
public function handlePositionSelected(?int $positionFormationId): void
{
    // ✅ Reset participant when position changes (don't preserve)
    $this->participantId = null;
    session()->forget('filter.participant_id');

    $this->loadAvailableParticipants();

    $this->dispatch('participant-selected', participantId: $this->participantId);

    Log::info('ParticipantSelector: Position changed, participant reset', [
        'positionFormationId' => $positionFormationId,
        'participantId' => $this->participantId,
    ]);
}
```

### Fix #5: Chart.js Grid Z-Index
File: [`resources/views/livewire/pages/dashboard.blade.php:1157-1172`](resources/views/livewire/pages/dashboard.blade.php#L1157-L1172)

```javascript
scales: {
    r: {
        beginAtZero: true,
        min: 0,
        max: 5,
        ticks: {
            display: false,
            stepSize: 1,
            color: colors.text,
            font: { size: 16 },
            z: 10  // ✅ Grid ticks on top
        },
        grid: {
            color: colors.grid,
            circular: true,
            z: 10  // ✅ Grid lines on top
        },
        pointLabels: {
            color: colors.pointLabels,
            font: { size: 16 },
            z: 10  // ✅ Point labels on top
        },
        angleLines: {
            color: colors.grid,
            z: 10  // ✅ Angle lines on top
        }
    }
}
```

### Fix #6: Dynamic Chart Reinitialization
File: [`resources/views/livewire/pages/dashboard.blade.php:1480-1556`](resources/views/livewire/pages/dashboard.blade.php#L1480-L1556)

```javascript
function updateChart(chart, data, hasParticipant, tolerancePercentage, participantName, chartType) {
    console.log(`[${chartType}] updateChart called`, {
        hasChart: !!chart,
        hasParticipant,
        dataLabelsCount: data?.labels?.length
    });

    // Safety check: if chart not initialized, reinitialize
    if (!chart || !chart.data) {
        console.warn(`[${chartType}] Chart not initialized, reinitializing...`);
        reinitializeChartWithData(chartType, data, hasParticipant, tolerancePercentage, participantName);
        return;
    }

    // Safety check: validate data
    if (!data || !data.labels || !Array.isArray(data.labels)) {
        console.error(`[${chartType}] Invalid chart data received:`, data);
        return;
    }

    try {
        // Check if dataset count changed (2 vs 3)
        const currentDatasetCount = chart.data.datasets.length;
        const requiredDatasetCount = hasParticipant ? 3 : 2;

        console.log(`[${chartType}] Dataset check:`, {
            current: currentDatasetCount,
            required: requiredDatasetCount,
            needsReinit: currentDatasetCount !== requiredDatasetCount
        });

        if (currentDatasetCount !== requiredDatasetCount) {
            // Dataset count changed - must reinitialize chart
            console.log(`[${chartType}] Dataset count changed (${currentDatasetCount} → ${requiredDatasetCount}), reinitializing...`);
            reinitializeChartWithData(chartType, data, hasParticipant, tolerancePercentage, participantName);
            return;
        }

        // Update chart data (dataset count matches)
        chart.data.labels = data.labels;

        if (hasParticipant) {
            // Update 3 datasets (peserta, standar, toleransi)
            if (chart.data.datasets[0]) {
                chart.data.datasets[0].label = participantName || 'Peserta';
                chart.data.datasets[0].data = data.individualRatings;
            }
            if (chart.data.datasets[1]) {
                chart.data.datasets[1].label = 'Standard';
                chart.data.datasets[1].data = data.standardRatings;
            }
            if (chart.data.datasets[2]) {
                chart.data.datasets[2].label = `Tolerance ${tolerancePercentage}%`;
                chart.data.datasets[2].data = data.originalStandardRatings;
            }
        } else {
            // Update 2 datasets (standar dan toleransi)
            if (chart.data.datasets[0]) {
                chart.data.datasets[0].label = 'Standard';
                chart.data.datasets[0].data = data.standardRatings;
            }
            if (chart.data.datasets[1]) {
                chart.data.datasets[1].label = `Tolerance ${tolerancePercentage}%`;
                chart.data.datasets[1].data = data.originalStandardRatings;
            }
        }

        // Smooth update with animation
        chart.update('active');
        console.log(`${chartType} chart updated successfully`);
    } catch (error) {
        console.error(`Error updating ${chartType} chart:`, error);
        // Fallback: reinitialize chart
        console.log(`Attempting to reinitialize ${chartType} chart...`);
        reinitializeChartWithData(chartType, data, hasParticipant, tolerancePercentage, participantName);
    }
}
```

**Key Logic:**
1. Detect dataset count change (2 vs 3)
2. If count changed → `reinitializeChartWithData()`
3. If count same → `chart.update('active')` (smooth animation)

## Hasil

✅ **Live Chart Updates Tanpa Reload**
- User dapat select/deselect participant dengan smooth transition
- Chart otomatis switch antara 2 dataset ↔ 3 dataset
- Transisi menggunakan Chart.js animation (`update('active')`)

✅ **Correct Color Coding**
- **No participant:** Standard (hijau) + Tolerance (kuning)
- **With participant:** Participant (hijau) + Standard (merah) + Tolerance (kuning)

✅ **Grid Visibility**
- Garis radar abu-abu selalu terlihat di atas dataset (z-index: 10)

✅ **Proper State Management**
- Participant state di-reset explicit ketika position/event berubah
- Session filter konsisten dengan component state

✅ **SPA-like Experience**
- Dashboard sekarang kompatibel dengan `wire:navigate`
- No full page reload required
- Smooth UX seperti halaman lain

## Testing

### Test Case 1: Select Participant
1. Buka dashboard (default: no participant, 2 datasets)
2. Pilih participant dari dropdown
3. **Expected:** Chart live update ke 3 datasets, warna berubah (hijau → hijau/merah/kuning)

### Test Case 2: Reset Participant
1. Dashboard dengan participant selected (3 datasets)
2. Klik tombol reset participant
3. **Expected:** Chart live update ke 2 datasets, warna kembali (hijau/kuning)

### Test Case 3: Change Position
1. Dashboard dengan participant selected
2. Ganti posisi/jabatan
3. **Expected:** Participant auto-reset, chart live update ke 2 datasets

### Test Case 4: Change Event
1. Dashboard dengan participant selected
2. Ganti event
3. **Expected:** Participant auto-reset, chart live update ke 2 datasets

### Test Case 5: Grid Visibility
1. Inspect chart visual dengan participant selected (3 colored datasets)
2. **Expected:** Garis radar abu-abu tetap terlihat di atas dataset berwarna

## Browser Cache
Jika perubahan tidak terlihat setelah implementasi:
```bash
php artisan optimize:clear
```
Lalu hard refresh browser: `Ctrl + Shift + R` (Windows/Linux) atau `Cmd + Shift + R` (Mac)

## Related Files

### Backend
- [`app/Livewire/Pages/Dashboard.php`](app/Livewire/Pages/Dashboard.php) - Main dashboard component
- [`app/Livewire/Components/ParticipantSelector.php`](app/Livewire/Components/ParticipantSelector.php) - Participant filter component

### Frontend
- [`resources/views/livewire/pages/dashboard.blade.php`](resources/views/livewire/pages/dashboard.blade.php) - Dashboard view with Chart.js

### Services (No Changes)
- `app/Services/DynamicStandardService.php`
- `app/Services/IndividualAssessmentService.php`
- `app/Services/RankingService.php`

## Architecture Reference
See [`docs/SERVICE_ARCHITECTURE.md`](docs/SERVICE_ARCHITECTURE.md) for complete service architecture context.
