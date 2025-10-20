# Filter Components Implementation Guide

> **📋 Context for Claude Code**: Dokumen ini menjelaskan sistem reusable filter components yang sudah diimplementasikan di aplikasi SPSP. Gunakan pattern ini ketika user meminta membuat halaman baru dengan filter event, jabatan, atau tolerance.

---

## 🎯 Kapan Menggunakan Pattern Ini?

Gunakan pattern ini ketika user meminta:
- "Buatkan halaman dengan filter event dan jabatan"
- "Tambahkan filter tolerance"
- "Buatkan report dengan pilihan event"
- Atau halaman apapun yang butuh filter event/position/tolerance

---

## 📦 Komponen yang Tersedia

### 1. EventSelector
**Path**: `app/Livewire/Components/EventSelector.php`

**Fungsi**: Dropdown untuk memilih Assessment Event
**Session Key**: `filter.event_code` (string)
**Event Dispatched**: `event-selected` dengan parameter `eventCode`

### 2. PositionSelector
**Path**: `app/Livewire/Components/PositionSelector.php`

**Fungsi**: Dropdown untuk memilih Position Formation (Jabatan)
**Session Key**: `filter.position_formation_id` (int)
**Event Dispatched**: `position-selected` dengan parameter `positionFormationId`
**Event Listened**: `event-selected` (auto-reset ketika event berubah)

### 3. ToleranceSelector
**Path**: `app/Livewire/Components/ToleranceSelector.php`

**Fungsi**: Dropdown untuk memilih tolerance percentage (0%, 5%, 10%, 15%, 20%)
**Session Key**: `individual_report.tolerance` (int, default: 10)
**Event Dispatched**: `tolerance-updated` dengan parameter `tolerance`

---

## 🚀 Implementation Steps

### Step 1: Tambahkan Komponen di Blade View

```blade
<div class="border-b-4 border-black py-4 bg-white">
    <h1 class="text-center text-2xl font-bold uppercase tracking-wide text-black">
        JUDUL HALAMAN ANDA
    </h1>

    <!-- Event Filter -->
    <div class="flex justify-center items-center gap-4 mt-3 px-6">
        <div class="w-full max-w-md">
            @livewire('components.event-selector', ['showLabel' => false])
        </div>
    </div>

    <!-- Position Filter -->
    <div class="flex justify-center items-center gap-4 mt-3 px-6">
        <div class="w-full max-w-md">
            @livewire('components.position-selector', ['showLabel' => false])
        </div>
    </div>
</div>

<!-- Tolerance Filter (jika dibutuhkan) -->
@php $summary = $this->getPassingSummary(); @endphp
@livewire('components.tolerance-selector', [
    'passing' => $summary['passing'],
    'total' => $summary['total'],
    'showSummary' => false,
])
```

### Step 2: Setup Listeners di Component

```php
<?php

namespace App\Livewire\Pages\YourNamespace;

use Livewire\Component;
use Livewire\WithPagination;

class YourComponent extends Component
{
    use WithPagination;

    protected $listeners = [
        'event-selected' => 'handleEventSelected',
        'position-selected' => 'handlePositionSelected',
        'tolerance-updated' => 'handleToleranceUpdate', // optional
    ];

    public function handleEventSelected(?string $eventCode): void
    {
        $this->resetPage();

        // JANGAN refresh data di sini!
        // Position akan auto-reset dan trigger handlePositionSelected
    }

    public function handlePositionSelected(?int $positionFormationId): void
    {
        $this->resetPage();

        // Refresh data di sini
        // Karena event dan position sudah keduanya valid
    }

    public function handleToleranceUpdate(int $tolerance): void
    {
        // Update calculation dengan tolerance baru
        // Refresh data jika perlu
    }

    public function render()
    {
        return view('livewire.your-view', [
            'data' => $this->getData(),
        ]);
    }

    private function getData()
    {
        // Read from session
        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');

        if (!$eventCode || !$positionFormationId) {
            return null;
        }

        $event = AssessmentEvent::where('code', $eventCode)->first();

        if (!$event) {
            return null;
        }

        // Query data dengan filter
        return YourModel::query()
            ->where('event_id', $event->id)
            ->where('position_formation_id', $positionFormationId)
            ->paginate(10);
    }
}
```

### Step 3: Handle Empty State di Blade

```blade
<tbody>
    @if ($data && $data->count() > 0)
        @foreach ($data as $row)
            <tr>
                <td class="border-2 border-black px-3 py-2">{{ $row->column1 }}</td>
                <td class="border-2 border-black px-3 py-2">{{ $row->column2 }}</td>
            </tr>
        @endforeach
    @else
        <tr>
            <td colspan="12" class="border-2 border-black px-3 py-4 text-center text-gray-500">
                Tidak ada data untuk ditampilkan. Silakan pilih event dan jabatan.
            </td>
        </tr>
    @endif
</tbody>

@if ($data?->hasPages())
    <div class="mt-4">
        {{ $data->links(data: ['scrollTo' => false]) }}
    </div>
@endif
```

---

## 📊 Pattern: Halaman dengan Chart

Jika halaman memiliki chart yang perlu auto-refresh saat filter berubah:

### Component PHP (tambahkan properties & methods):

```php
// Chart data
public array $chartLabels = [];
public array $chartData = [];
public array $chartColors = [];

public function mount(): void
{
    $this->prepareChartData();
}

public function handlePositionSelected(?int $positionFormationId): void
{
    $this->resetPage();
    $this->prepareChartData();
}

private function prepareChartData(): void
{
    $summary = $this->getSummary();

    if (empty($summary)) {
        $this->chartLabels = [];
        $this->chartData = [];
        $this->chartColors = [];
        return;
    }

    $this->chartLabels = array_keys($summary);
    $this->chartData = array_values($summary);
    $this->chartColors = ['#10b981', '#3b82f6', '#ef4444'];
}
```

### Blade View (Alpine.js untuk auto-refresh):

```blade
@if (!empty($chartData))
    <div x-data="{
        refreshChart() {
            const labels = @js($chartLabels);
            const data = @js($chartData);
            const colors = @js($chartColors);
            if (labels.length > 0 && data.length > 0) {
                createMyChart(labels, data, colors);
            }
        }
    }" x-init="$nextTick(() => refreshChart())">
        <div wire:ignore>
            <canvas id="myChart" class="w-full h-full"></canvas>
        </div>
    </div>
@endif

<script>
    let myChart = null;

    function createMyChart(labels, data, colors) {
        const canvas = document.getElementById('myChart');
        if (!canvas) return;

        // Destroy existing chart
        if (myChart) {
            myChart.destroy();
            myChart = null;
        }

        // Create new chart
        myChart = new Chart(canvas, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
            }
        });
    }
</script>
```

**Key Points**:
- `x-data`: Define Alpine component
- `x-init="$nextTick(() => refreshChart())"`: Auto-execute saat render/re-render
- `@js($variable)`: Pass PHP variable ke JS (auto-updates on re-render)
- `wire:ignore`: Prevent Livewire dari memanipulasi canvas

---

## ⚠️ Common Mistakes (JANGAN LAKUKAN INI!)

### ❌ SALAH: Store filters di component properties

```php
// WRONG - Jangan lakukan ini!
class YourComponent extends Component
{
    public ?string $eventCode = null;
    public ?int $positionFormationId = null;
    public array $availableEvents = [];
    public array $availablePositions = [];

    public function mount(): void
    {
        $this->availableEvents = AssessmentEvent::all();
        $this->eventCode = $this->availableEvents[0]->code ?? null;
    }
}
```

### ✅ BENAR: Baca dari session

```php
// CORRECT - Lakukan ini!
class YourComponent extends Component
{
    protected $listeners = [
        'event-selected' => 'handleEventSelected',
        'position-selected' => 'handlePositionSelected',
    ];

    private function getData()
    {
        $eventCode = session('filter.event_code');
        $positionFormationId = session('filter.position_formation_id');
        // ...
    }
}
```

---

### ❌ SALAH: Update data di handleEventSelected

```php
// WRONG - Jangan update data di sini!
public function handleEventSelected(?string $eventCode): void
{
    $this->resetPage();
    $this->prepareChartData(); // ❌ JANGAN!
    $this->refreshData();      // ❌ JANGAN!
}
```

### ✅ BENAR: Update data di handlePositionSelected

```php
// CORRECT - Update data di sini!
public function handleEventSelected(?string $eventCode): void
{
    $this->resetPage();
    // Hanya reset, jangan update data
    // PositionSelector akan auto-reset dan trigger handlePositionSelected
}

public function handlePositionSelected(?int $positionFormationId): void
{
    $this->resetPage();
    $this->prepareChartData(); // ✅ Update di sini
    $this->refreshData();      // ✅ Update di sini
}
```

**Alasan**: Ketika event berubah, position akan di-reset ke null oleh PositionSelector. Jika update data di `handleEventSelected`, data akan kosong karena position masih null. Tunggu sampai `handlePositionSelected` dipanggil (setelah position auto-selected).

---

### ❌ SALAH: Lupa handle empty state

```blade
<!-- WRONG - Akan error jika $data null -->
@foreach ($data as $row)
    <tr>...</tr>
@endforeach
```

### ✅ BENAR: Selalu check null/empty

```blade
<!-- CORRECT - Handle null/empty -->
@if ($data && $data->count() > 0)
    @foreach ($data as $row)
        <tr>...</tr>
    @endforeach
@else
    <tr>
        <td colspan="X">Tidak ada data untuk ditampilkan.</td>
    </tr>
@endif
```

---

### ❌ SALAH: Chart tidak check data kosong

```javascript
// WRONG - Chart akan blank jika data kosong
function refreshChart() {
    const labels = @js($chartLabels);
    const data = @js($chartData);
    createMyChart(labels, data); // ❌ Langsung create
}
```

### ✅ BENAR: Check data sebelum create chart

```javascript
// CORRECT - Check dulu sebelum create
function refreshChart() {
    const labels = @js($chartLabels);
    const data = @js($chartData);
    const colors = @js($chartColors);

    // ✅ Check data tidak kosong
    if (labels.length > 0 && data.length > 0) {
        createMyChart(labels, data, colors);
    }
}
```

---

## 🔍 Debugging Tips

### Problem: Data tidak update saat ganti filter

**Check**:
1. Apakah `protected $listeners` sudah ada dan benar?
2. Apakah method handler dipanggil? (tambahkan `logger('handlePositionSelected called')`)
3. Apakah data dibaca dari session? (bukan dari component properties)

### Problem: Chart tidak muncul setelah ganti filter

**Check**:
1. Apakah menggunakan Alpine.js `x-init` pattern?
2. Apakah ada check `if (labels.length > 0)` sebelum create chart?
3. Apakah `wire:ignore` ada di chart container?
4. Apakah chart lama di-destroy sebelum create yang baru?

### Problem: Position tidak reset saat ganti event

**Check**:
1. Apakah `PositionSelector` ada di blade view?
2. Apakah session `filter.position_formation_id` di-reset? (check browser console)

### Problem: Error "foreach() argument must be of type array|object, null given"

**Solution**: Tambahkan check `@if ($data && $data->count() > 0)` sebelum foreach

---

## 📖 Reference Implementation

**PENTING**: Gunakan file ini sebagai reference utama ketika implement halaman baru:

**Component**: `app/Livewire/Pages/GeneralReport/Ranking/RankingPsyMapping.php`
**View**: `resources/views/livewire/pages/general-report/ranking/ranking-psy-mapping.blade.php`

File ini sudah implement semua pattern dengan benar:
- ✅ EventSelector + PositionSelector + ToleranceSelector
- ✅ Chart dengan Alpine.js auto-refresh
- ✅ Empty state handling
- ✅ Pagination
- ✅ Session-based filter reading

**COPY pattern dari file ini untuk consistency!**

---

## 🎯 Checklist untuk Claude Code

Ketika implement halaman baru dengan filter, pastikan:

- [ ] Component **TIDAK** memiliki properties: `$eventCode`, `$positionFormationId`, `$availableEvents`, `$availablePositions`
- [ ] Component memiliki `protected $listeners` dengan events yang sesuai
- [ ] Handler methods ada: `handleEventSelected()`, `handlePositionSelected()`
- [ ] `handleEventSelected()` hanya `$this->resetPage()`, TIDAK update data
- [ ] `handlePositionSelected()` update data (karena event dan position sudah valid)
- [ ] Data methods membaca dari session: `session('filter.event_code')`, `session('filter.position_formation_id')`
- [ ] Blade view menggunakan `@livewire('components.event-selector')` dan `@livewire('components.position-selector')`
- [ ] Empty state handling: `@if ($data && $data->count() > 0)`
- [ ] Pagination check: `@if ($data?->hasPages())`
- [ ] Chart menggunakan Alpine.js `x-init` pattern (jika ada chart)
- [ ] Chart check data tidak kosong sebelum create

---

## 💡 Quick Command Reference

```php
// Baca filter dari session
$eventCode = session('filter.event_code');
$positionFormationId = session('filter.position_formation_id');
$tolerance = session('individual_report.tolerance', 10);

// Check filter valid
if (!$eventCode || !$positionFormationId) {
    return null;
}

// Get event object
$event = AssessmentEvent::where('code', $eventCode)->first();

// Query dengan filter
YourModel::query()
    ->where('event_id', $event->id)
    ->where('position_formation_id', $positionFormationId)
    ->get();
```

---

## 🆘 When User Asks...

**"Buatkan halaman dengan filter event dan jabatan"**
→ Copy pattern dari `RankingPsyMapping.php`

**"Chart tidak muncul saat ganti filter"**
→ Check Alpine.js `x-init` pattern + check `if (labels.length > 0)`

**"Error: foreach() argument must be of type array"**
→ Tambahkan `@if ($data && $data->count() > 0)` sebelum foreach

**"Filter tidak tersimpan saat pindah halaman"**
→ Sudah otomatis tersimpan di session oleh komponen

**"Position tidak reset saat ganti event"**
→ Sudah otomatis ter-reset oleh `PositionSelector`

**"Data kosong saat pertama load"**
→ Check database, apakah ada data untuk event & position yang terpilih

---

**🚀 Happy Coding!**

*Last updated: 2025* | *Reference: RankingPsyMapping.php*
