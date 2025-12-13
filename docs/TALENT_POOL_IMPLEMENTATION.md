# Talent Pool - 9-Box Performance Matrix Implementation

## 📋 Overview

Talent Pool adalah fitur baru untuk visualisasi **9-Box Performance Matrix** yang memetakan peserta berdasarkan performa (kompetensi) dan potensi mereka. Fitur ini menggunakan **statistik dinamis** untuk menentukan batas area, bukan nilai fixed.

## 🎯 Fitur Utama

### 1. **Dynamic 9-Box Matrix**

-   **Sumbu X (Horizontal)**: POTENSI (rata-rata rating aspek kategori potensi)
-   **Sumbu Y (Vertikal)**: KINERJA (rata-rata rating aspek kategori kompetensi)
-   **Pembagian Area**: Menggunakan **rata-rata ± standar deviasi** untuk masing-masing sumbu
-   **9 Area Klasifikasi**:
    -   Box 1: Need Attention (Potensi Rendah, Kinerja Rendah)
    -   Box 2: Steady Performer (Potensi Rendah, Kinerja Sedang)
    -   Box 3: Inconsistent (Potensi Rendah, Kinerja Tinggi)
    -   Box 4: Solid Performer (Potensi Sedang, Kinerja Rendah)
    -   Box 5: Core Performer (Potensi Sedang, Kinerja Sedang)
    -   Box 6: Enigma (Potensi Sedang, Kinerja Tinggi)
    -   Box 7: Potential Star (Potensi Tinggi, Kinerja Rendah)
    -   Box 8: High Potential (Potensi Tinggi, Kinerja Sedang)
    -   Box 9: Star Performer (Potensi Tinggi, Kinerja Tinggi)

### 2. **Event & Position Filtering**

-   **EventSelector**: Memilih event assessment
-   **PositionSelector**: Memilih jabatan/posisi
-   **Real-time Update**: Chart otomatis update saat filter berubah

### 3. **3-Layer Priority Integration**

-   **Layer 1**: Session Adjustment (temporary exploration)
-   **Layer 2**: Custom Standard (institution baseline)
-   **Layer 3**: Quantum Default (system baseline)
-   **Automatic Cache Invalidation**: Cache otomatis invalid saat ada perubahan

## 🏗️ Arsitektur Teknis

### Core Components

#### 1. **TalentPoolService** (`app/Services/TalentPoolService.php`)

```php
// Method utama:
- getNineBoxMatrixData($eventId, $positionFormationId)
- getParticipantsPositionData($eventId, $positionFormationId)
- calculateBoxBoundaries($participants)
- classifyParticipantsToBoxes($participants, $boundaries)
```

#### 2. **TalentPool Component** (`app/Livewire/Pages/TalentPool.php`)

```php
// Properties:
- $selectedEvent
- $selectedPositionId
- $matrixData
- $totalParticipants

// Methods:
- handleEventSelected()
- handlePositionSelected()
- handleStandardUpdate()
- getChartDataProperty()
- getBoxBoundariesProperty()
```

#### 3. **TalentPool View** (`resources/views/livewire/pages/talentpool.blade.php`)

-   **EventSelector & PositionSelector** components
-   **9-Box Scatter Chart** dengan Chart.js
-   **Pie Chart** untuk distribusi peserta
-   **Summary Table** dengan statistik per box

### Data Flow

```
User selects Event & Position
    ↓
TalentPoolComponent::handlePositionSelected()
    ↓
TalentPoolService::getNineBoxMatrixData()
    ↓
┌─────────────────────────────────────────┐
│ 1. Load aspect assessments          │
│ 2. Group by participant            │
│ 3. Calculate category averages       │
│ 4. Calculate statistics (avg ± std)  │
│ 5. Determine box boundaries         │
│ 6. Classify participants           │
└─────────────────────────────────────────┘
    ↓
Return: Matrix Data + Statistics + Boundaries
    ↓
JavaScript Chart Rendering
```

## 📊 Algoritma Kalkulasi

### 1. **Rating per Kategori**

```php
// Untuk setiap peserta:
$potensiRating = rata_rata(aspek_di_kategori_potensi);
$kinerjaRating = rata_rata(aspek_di_kategori_kompetensi);
```

### 2. **Box Boundaries (Dinamis)**

```php
// Hitung statistik untuk semua peserta:
$potensiAvg = avg(semua_potensi_rating);
$potensiStdDev = std_dev(semua_potensi_rating);
$kinerjaAvg = avg(semua_kinerja_rating);
$kinerjaStdDev = std_dev(semua_kinerja_rating);

// Tentukan batas:
$potensiLower = $potensiAvg - $potensiStdDev;
$potensiUpper = $potensiAvg + $potensiStdDev;
$kinerjaLower = $kinerjaAvg - $kinerjaStdDev;
$kinerjaUpper = $kinerjaAvg + $kinerjaStdDev;
```

### 3. **Klasifikasi Box**

```php
function determineBox($potensi, $kinerja, $boundaries) {
    $potensiLevel = determineLevel($potensi, $boundaries['potensi']);
    $kinerjaLevel = determineLevel($kinerja, $boundaries['kinerja']);

    return mapLevelsToBox($potensiLevel, $kinerjaLevel);
}

function determineLevel($value, $lower, $upper) {
    if ($value < $lower) return 'rendah';
    if ($value > $upper) return 'tinggi';
    return 'sedang';
}
```

## 🚀 Optimasi Performa

### 1. **Caching Strategy**

```php
// Cache key includes configuration hash
$cacheKey = "talent_pool:{$eventId}:{$positionId}:{$configHash}";

// 60s TTL untuk balance antara freshness & performance
Cache::remember($cacheKey, 60, $callback);
```

### 2. **Lightweight Queries**

```php
// Select only necessary columns
AspectAssessment::query()
    ->select('participant_id', 'aspect_id', 'individual_rating')
    ->toBase() // Skip model hydration
    ->get();
```

### 3. **Batch Processing**

```php
// Group by participant untuk mengurangi loops
$assessmentsByParticipant = $assessments->groupBy('participant_id');
```

## 📱 UI/UX Features

### 1. **Interactive Scatter Chart**

-   **Hover Tooltip**: Menampilkan nama, rating, dan box
-   **Color Coding**: Setiap box memiliki warna berbeda
-   **Dynamic Grid Lines**: Garis pembatas menyesuaikan dengan statistik
-   **Box Numbers**: Nomor box terlihat jelas di background

### 2. **Responsive Design**

-   **Mobile Friendly**: Layout menyesuaikan dengan ukuran screen
-   **Loading States**: Indikator loading saat proses data
-   **Empty States**: Pesan informatif saat tidak ada data

### 3. **Real-time Updates**

-   **Session Integration**: Otomatis update saat ada perubahan session
-   **Component Events**: Mendengarkan perubahan dari selector components
-   **Cache Invalidation**: Otomatis refresh saat ada perubahan standard

## 🧪 Testing

### 1. **Unit Tests** (`tests/Feature/Livewire/TalentPoolTest.php`)

-   **Large Dataset Test**: Testing dengan 100+ peserta
-   **Boundary Calculation Test**: Validasi perhitungan batas area
-   **Classification Test**: Validasi klasifikasi peserta ke box
-   **Cache Test**: Validasi cache mechanism
-   **Performance Test**: Validasi execution time < 2 detik

### 2. **Integration Tests**

-   **Component Rendering**: Validasi component render tanpa error
-   **Event Handling**: Validasi event dari selector components
-   **Data Flow**: Validasi alur data dari service ke UI

### 3. **Performance Tests**

-   **Load Testing**: Testing dengan 4,905 peserta (skala produksi)
-   **Memory Usage**: Validasi memory consumption
-   **Query Optimization**: Validasi query efficiency

## 📈 Monitoring & Analytics

### 1. **Performance Metrics**

-   **Execution Time**: Target < 2 detik untuk 4,905 peserta
-   **Memory Usage**: Monitor memory consumption
-   **Cache Hit Rate**: Target > 80% untuk subsequent requests

### 2. **Business Metrics**

-   **User Engagement**: Track usage patterns
-   **Data Distribution**: Analisis distribusi peserta per box
-   **Filter Usage**: Track filter combinations

## 🔧 Configuration

### 1. **Environment Variables**

```env
TALENT_POOL_CACHE_TTL=60
TALENT_POOL_MAX_PARTICIPANTS=10000
```

### 2. **Chart Configuration**

```javascript
// Default chart settings
const chartConfig = {
    pointRadius: 10,
    pointHoverRadius: 15,
    animationDuration: 750,
    responsive: true,
    maintainAspectRatio: false,
};
```

## 🚀 Deployment

### 1. **Database Migrations**

-   Tidak ada migration baru (menggunakan tabel existing)
-   Memanfaatkan relasi existing: `aspect_assessments`, `participants`, dll.

### 2. **Asset Compilation**

```bash
npm run build
php artisan view:clear
php artisan cache:clear
```

### 3. **Cache Warming**

```bash
php artisan cache:warm --talent-pool
```

## 📚 Related Documentation

-   [SPSP_BUSINESS_CONCEPTS.md](./SPSP_BUSINESS_CONCEPTS.md) - Konsep bisnis SPSP
-   [TESTING_SCENARIOS_BASELINE_3LAYER.md](./TESTING_SCENARIOS_BASELINE_3LAYER.md) - Testing scenarios
-   [TrainingRecommendationService.php](../app/Services/TrainingRecommendationService.php) - Reference implementation

## 🔄 Future Enhancements

### 1. **Advanced Features**

-   **Historical Comparison**: Bandingkan matrix antar periode
-   **Individual Development Plans**: Rekomendasi pengembangan per box
-   **Export Functionality**: PDF/Excel export dengan branding
-   **Drill-down Capability**: Klik box untuk detail peserta

### 2. **Analytics Features**

-   **Trend Analysis**: Perubahan distribusi dari waktu ke waktu
-   **Predictive Analytics**: Prediksi performa berdasarkan pola
-   **Benchmarking**: Perbandingan antar departemen/posisi

### 3. **Integration Features**

-   **HRIS Integration**: Sync dengan sistem HR existing
-   **Learning Management**: Integrasi dengan platform training
-   **Succession Planning**: Tools untuk perencanaan suksesi

---

**Last Updated:** December 2025  
**Maintainer:** SPSP Development Team  
**Version:** 1.0.0
