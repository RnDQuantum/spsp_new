# SPSP - Sistem Pemetaan & Statistik Psikologi

## 📋 Table of Contents
1. [Apa itu SPSP?](#apa-itu-spsp)
2. [Core Business Concepts](#core-business-concepts)
3. [3-Layer Priority System](#3-layer-priority-system)
4. [User Flow & Scenarios](#user-flow--scenarios)
5. [Data Architecture](#data-architecture)
6. [Key Principles](#key-principles)

---

## 🎯 Apa itu SPSP?

### Definisi

**SPSP (Sistem Pemetaan & Statistik Psikologi)** adalah aplikasi **Business Intelligence (BI)** yang dirancang khusus untuk menganalisis hasil penilaian psikologi pada skala besar. SPSP membantu institusi membuat keputusan rekrutmen dan pengembangan talenta berbasis data dengan cepat, akurat, dan objektif.

> **PENTING:** SPSP adalah sistem analisis data (BI), bukan sistem input data (CRUD).

---

### Latar Belakang: Masalah Bisnis yang Diselesaikan

Di Indonesia, rekrutmen massal untuk instansi pemerintah dan BUMN sering melibatkan **ribuan hingga puluhan ribu pelamar**. Setiap pelamar harus dinilai secara objektif berdasarkan **13 aspek psikologi** yang berbeda (aspek potensi dan kompetensi).

#### Tantangan yang Dihadapi Institusi:

```
MASALAH:
┌─────────────────────────────────────────────────────────────┐
│ 1. Skala Besar: 4,000+ pelamar untuk 100 posisi tersedia   │
│ 2. Kompleksitas: 13 aspek penilaian per pelamar            │
│ 3. Subjektivitas: Risiko bias dalam penilaian manual        │
│ 4. Waktu: Analisis manual bisa memakan waktu MINGGUAN      │
│ 5. What-If: Sulit menjawab "bagaimana jika standar berubah" │
└─────────────────────────────────────────────────────────────┘
```

#### Solusi SPSP:

```
✅ Analisis 4,905 peserta dalam 1.5 detik
✅ Ranking berbasis data, bukan perasaan
✅ Eksplorasi skenario tanpa mengubah data asli
✅ Standar khusus per institusi
✅ Visualisasi lengkap (Spider Plot, Charts, Reports)
```

---

### Target Pengguna

SPSP dirancang untuk berbagai jenis institusi yang melakukan penilaian psikologi:

| Pengguna | Kebutuhan | Bagaimana SPSP Membantu |
|----------|-----------|-------------------------|
| **Institusi Pemerintah** | Kejaksaan, Kemenkumham, Kementerian lain | Seleksi P3K/CPNS yang fair dan transparan |
| **Perusahaan BUMN/Swasta** | Mass recruitment untuk karyawan baru | Filter ribuan pelamar secara cepat |
| **Konsultan Psikologi** | Analisis mendalam untuk klien | Laporan komprehensif dengan visualisasi |
| **Tim HR/Recruitment** | Talent selection & development | Identifikasi kandidat terbaik dan gap kompetensi |

---

### Apa yang Bisa Dilakukan SPSP?

SPSP menyediakan berbagai kemampuan analisis yang memberikan nilai nyata bagi pengguna:

#### 1. **Individual Assessment Reports**
Memahami profil psikologi setiap kandidat secara detail:
- **Spider Plot:** Visualisasi radar grafik perbandingan skor vs standar
- **General Mapping:** Analisis menyeluruh aspek Potensi dan Kompetensi
- **MC Mapping:** Fokus pada aspek Kompetensi untuk jabatan tertentu
- **PSY Mapping:** Fokus pada aspek Potensi (Psikometrik)
- **Kesimpulan Otomatis:** "Di Atas Standar", "Memenuhi Standar", "Di Bawah Standar"

#### 2. **Ranking Systems**
Mengidentifikasi kandidat terbaik secara objektif:
- **Ranking Potensi:** Berdasarkan aspek potensi (Daya Pikir, Sikap Kerja, dll)
- **Ranking Kompetensi:** Berdasarkan aspek kompetensi (Integritas, Kepemimpinan, dll)
- **Overall Ranking:** Kombinasi potensi + kompetensi sesuai bobot
- **Filter & Sort:** Berdasarkan standar, toleransi, kategori

#### 3. **What-If Analysis**
Eksplorasi skenario tanpa mengubah data asli:
- "Bagaimana jika bobot Kepemimpinan dinaikkan dari 10% ke 15%?"
- "Bagaimana jika standar Integritas dinaikkan dari 4 ke 5?"
- "Bagaimana jika toleransi pelonggaran 10% diterapkan?"
- Hasil rekalkulasi **real-time** dalam hitungan detik

#### 4. **Custom Standards**
Personalisasi standar sesuai kebutuhan institusi:
- Setiap institusi bisa membuat standar penilaian sendiri
- Override bobot kategori (Potensi/Kompetensi)
- Override bobot aspek individual
- Override rating standar per aspek
- Simpan sebagai baseline untuk analisis berulang

#### 5. **Training Recommendations**
Identifikasi gap kompetensi untuk pengembangan:
- Analisis aspek yang di bawah standar
- Rekomendasi pelatihan berdasarkan gap
- Prioritas development per kandidat

#### 6. **Talent Pool Management**
Kelola kandidat potensial untuk kebutuhan masa depan:
- Tandai kandidat sebagai talent
- Lacak performa historis
- Referensi cepat untuk posisi kosong

---

### Skenario Nyata: Bagaimana SPSP Digunakan

Berikut adalah skenario nyata penggunaan SPSP di institusi:

#### **Skenario 1: Rekrutmen Massal P3K Kejaksaan**

**Konteks:**
- Kejaksaan membuka 100 posisi Jaksa Penuntut Umum
- 4,905 pelamar mengikuti seleksi
- Setiap pelamar dinilai 13 aspek psikologi

**Tantangan:**
> "Siapa 100 kandidat terbaik yang memenuhi standar kami?"

**Bagaimana SPSP Membantu:**
```
1. HR memilih event: "P3K Kejaksaan 2025"
2. Pilih posisi: "Jaksa Penuntut Umum"
3. Lihat ranking 4,905 peserta (ter-load dalam 1.5 detik)
   ├─ Top 1: WINDA FUJIATI (score: 422.5)
   ├─ Top 2: ALMIRA ISWAHYUDI (score: 417.5)
   └─ ... hingga top 4,905
4. Filter kandidat yang "Memenuhi Standar"
5. Klik nama kandidat untuk lihat detail report
```

**Hasil:**
- 100 kandidat terbaik teridentifikasi dalam hitungan detik
- Proses seleksi yang semula memakan waktu berminggu-minggu
- Keputusan berbasis data, transparan, dan dapat dipertanggungjawabkan

---

#### **Skenario 2: Standar Khusus Institusi**

**Konteks:**
- Institusi A (Kejaksaan) punya prioritas berbeda dari Institusi B (Kemenkumham)
- Kejaksaan menilai "Integritas" lebih penting (bobot 15%)
- Kemenkumham menilai "Kepemimpinan" lebih penting (bobot 20%)

**Tantangan:**
> "Bagaimana membuat standar penilaian yang sesuai dengan kultur institusi kami?"

**Bagaimana SPSP Membantu:**
```
1. Admin Kejaksaan membuat Custom Standard:
   - Potensi: 25%, Kompetensi: 75%
   - Integritas: bobot 15%, standar rating 5
   - Kepemimpinan: bobot 10%, standar rating 4
   - ... (konfigurasi lengkap)

2. Custom Standard disimpan di database

3. Saat analisis, HR memilih baseline:
   "Custom Standard: Kejaksaan 2025"

4. Ranking otomatis menggunakan standar Kejaksaan
```

**Hasil:**
- Setiap institusi punya standar yang sesuai dengan kebutuhan
- Ranking mencerminkan prioritas institusi
- Analisis tetap cepat (1.5 detik) meski dengan custom standard

---

#### **Skenario 3: Eksplorasi What-If**

**Konteks:**
- Manajemen ingin melihat dampak perubahan prioritas
- "Bagaimana jika kita lebih menekankan kepemimpinan?"

**Tantangan:**
> "Bagaimana dampaknya jika kita ubah bobot Kepemimpinan dari 10% ke 15%?"

**Bagaimana SPSP Membantu:**
```
1. HR membuka halaman ranking dengan Custom Standard aktif

2. Menggunakan editor bobot:
   - Geser slider Kepemimpinan: 10% → 15%
   - Bobot lain otomatis menyesuaikan

3. Sistem bereaksi dalam hitungan detik:
   - Cache di-invalidasi
   - Ranking dikalkulasi ulang
   - Tampilan update real-time

4. HR bisa membandingkan:
   - "Sebelum: Kandidat A di posisi #5"
   - "Sesudah: Kandidat A di posisi #3"

5. Jika puas, bisa disimpan sebagai Custom Standard baru
```

**Hasil:**
- Manajemen bisa membuat keputusan berbasis data
- Eksplorasi berbagai skenario tanpa risiko
- Transparansi dalam penentuan standar penilaian

---

#### **Skenario 4: Analisis Gap Kompetensi untuk Development**

**Konteks:**
- Perusahaan ingin mengembangkan karyawan existing
- Perlu identifikasi gap kompetensi untuk pelatihan

**Tantangan:**
> "Aspek kompetensi apa yang perlu dikembangkan untuk karyawan X?"

**Bagaimana SPSP Membantu:**
```
1. HR memilih karyawan: "BUDI SANTOSO"

2. Lihat Individual Report:
   ┌────────────────────────────────────┐
   │ KESIMPULAN: Memenuhi Standar        │
   │ Ranking: 45 dari 100                │
   │                                     │
   │ Gap Analysis:                       │
   │ ✅ Integritas: 4.5 (Standar: 4.0)   │
   │ ✅ Kerjasama: 4.2 (Standar: 4.0)    │
   │ ❌ Kepemimpinan: 3.0 (Standar: 4.0) │
   │ ❌ Komunikasi: 3.2 (Standar: 4.0)   │
   └────────────────────────────────────┘

3. Training Recommendation:
   - Prioritas 1: Leadership Workshop
   - Prioritas 2: Communication Skills
   - Prioritas 3: Public Speaking
```

**Hasil:**
- Development plan berbasis data
- ROI pelatihan lebih tinggi ( tepat sasaran)
- Karyawan merasa diperhatikan development-nya

---

### Perbedaan SPSP dengan Sistem CRUD (Critical Understanding)

Memahami perbedaan ini krusial untuk menggunakan SPSP dengan benar:

| Aspek | Sistem CRUD Biasa | SPSP (BI System) |
|-------|------------------|------------------|
| **Tujuan Utama** | Input & manage data | **Eksplorasi & analisis data** |
| **Cara Kerja** | User mengisi form → simpan ke DB | User pilih standar → lihat hasil analisis |
| **Sumber Data** | User input data baru | **Data pre-loaded dari hasil tes** |
| **Performance** | Harus real-time | **Optimasi dengan caching, pre-calculation** |
| **Interaksi User** | Form submissions, CRUD buttons | **Dynamic filtering, what-if analysis, drill-down** |
| **Perubahan Data** | User bisa edit/delete | **Data bersifat immutable (historical)** |
| **Output** | Single record view | **Rankings, charts, aggregate statistics** |
| **Use Case** | Operational (transaksi data) | **Analytical (pengambilan keputusan)** |

#### Mengapa Perbedaan Ini Penting?

❌ **JANGGAN** menganggap SPSP sebagai sistem input data peserta
✅ **GUNAKAN** SPSP untuk menganalisis data yang sudah ada

❌ **JANGGAN** berharap mengubah rating individual peserta
✅ **GUNAKAN** SPSP untuk mengubah standar dan melihat dampaknya

❌ **JANGGAN** berharap data berubah real-time saat tes berlangsung
✅ **GUNAKAN** SPSP untuk analisis setelah data tes selesai di-import

---

### Mengapa SPSP Penting untuk Organisasi?

#### **1. Kecepatan Analisis**
- 4,905 peserta dianalisis dalam **1.5 detik**
- Rekrutmen massal yang semula berminggu-minggu → selesai dalam jam

#### **2. Objektivitas & Transparansi**
- Keputusan berbasis data, bukan perasaan
- Setiap kandidat dinilai dengan standar yang sama
- Dapat dipertanggungjawabkan secara audit

#### **3. Fleksibilitas Eksplorasi**
- Ubah standar → lihat dampak dalam detik
- Eksplorasi berbagai skenario tanpa risiko
- Custom standar per institusi

#### **4. Skalabilitas**
- Dapat menangani ribuan hingga puluhan ribu peserta
- Performance tetap cepat dengan caching optimization
- Siap untuk rekrutmen skala nasional

#### **5. Insight Visual**
- Spider Plot untuk visualisasi cepat
- Charts dan graphs untuk pattern recognition
- Reports siap presentasi untuk manajemen

---

**Kunci Takeaway:** SPSP bukan alat untuk input data, tetapi **alat untuk transformasi data penilaian psikologi menjadi keputusan rekrutmen yang cerdas**.

---

## 🧩 Core Business Concepts

### 1. Assessment Structure (Hierarki Penilaian)

```
AssessmentTemplate (Contoh: "P3K Kejaksaan 2025")
│
├─ CategoryType: POTENSI (25% bobot)
│  ├─ Aspect: Daya Pikir (weight: 5%)
│  │  ├─ SubAspect: Daya Analisa (rating: 3)
│  │  ├─ SubAspect: Kreativitas (rating: 4)
│  │  └─ SubAspect: Fleksibilitas (rating: 3)
│  │
│  ├─ Aspect: Sikap Kerja (weight: 7%)
│  │  ├─ SubAspect: Tanggung Jawab (rating: 4)
│  │  └─ SubAspect: Ketekunan (rating: 3)
│  │
│  └─ ... (total 4 aspects)
│
└─ CategoryType: KOMPETENSI (75% bobot)
   ├─ Aspect: Integritas (weight: 15%, rating: 4)
   ├─ Aspect: Kepemimpinan (weight: 10%, rating: 3)
   └─ ... (total 7 aspects, no sub-aspects)

ATURAN DATA-DRIVEN:
✅ Aspect dengan sub-aspects: Rating calculated dari sub-aspects
✅ Aspect tanpa sub-aspects: Rating langsung dari aspect
```

### 2. Assessment Results (Data Historis)

Setiap **Participant** mengikuti tes dan menghasilkan data **FINAL**:

```php
AspectAssessment {
    participant_id: 18576,
    aspect_id: 40,  // "Daya Pikir"
    individual_rating: 4.2,  // ✅ PRE-CALCULATED dari sub-aspects
    individual_score: 105.0, // = 4.2 * 25 (weight)
    // ... metadata lainnya
}

SubAspectAssessment {
    aspect_assessment_id: 2,
    sub_aspect_id: 65,  // "Daya Analisa"
    individual_rating: 4,  // Rating dari assessor
}
```

**🔑 KUNCI PENTING:**
- `individual_rating` adalah **DATA HISTORIS** yang **TIDAK PERNAH BERUBAH**
- Data ini di-store saat peserta **menyelesaikan tes**
- BI System hanya **MEMBACA** data ini untuk analisis

### 3. Standards (Baseline untuk Perbandingan)

**Standard** = Nilai minimal yang diharapkan dari kandidat

```
Contoh:
Aspect "Integritas" memiliki standard_rating = 4
Artinya: Institusi mengharapkan kandidat memiliki Integritas minimal rating 4

Participant A: individual_rating = 5 → ✅ "Di Atas Standar"
Participant B: individual_rating = 4 → ✅ "Memenuhi Standar"
Participant C: individual_rating = 3 → ❌ "Di Bawah Standar"
```

---

## 🏗️ 3-Layer Priority System

Sistem SPSP memiliki **3 lapisan prioritas** untuk menentukan **baseline standard** dan **weights** yang digunakan dalam perhitungan ranking.

### Layer Prioritization:
```
┌─────────────────────────────────────────────────────────────┐
│  Layer 1: SESSION ADJUSTMENT (Temporary Exploration)        │
│  Priority: HIGHEST                                           │
│  Storage: Session (per-user, temporary)                      │
│  Use Case: "Bagaimana jika saya ubah bobot X dari 10% → 15%"│
│  Lifetime: Sampai user close browser / reset adjustment      │
└─────────────────────────────────────────────────────────────┘
                          ↓ (if not exists)
┌─────────────────────────────────────────────────────────────┐
│  Layer 2: CUSTOM STANDARD (Institution Baseline)            │
│  Priority: MEDIUM                                            │
│  Storage: Database (custom_standards table)                  │
│  Use Case: "Institusi Kejaksaan punya standar khusus"       │
│  Lifetime: Permanent, bisa di-edit admin                     │
└─────────────────────────────────────────────────────────────┘
                          ↓ (if not exists)
┌─────────────────────────────────────────────────────────────┐
│  Layer 3: QUANTUM DEFAULT (System Baseline)                 │
│  Priority: LOWEST (fallback)                                 │
│  Storage: Database (aspects/sub_aspects table)               │
│  Use Case: Standar umum sistem (baseline awal)              │
│  Lifetime: Permanent, jarang berubah                         │
└─────────────────────────────────────────────────────────────┘
```

### Implementasi di Code:

```php
// DynamicStandardService.php
public function getAspectWeight(int $templateId, string $aspectCode): int
{
    // ✅ LAYER 1: Check session adjustment first
    $adjustments = Session::get("standard_adjustment.{$templateId}", []);
    if (isset($adjustments['aspect_weights'][$aspectCode])) {
        return $adjustments['aspect_weights'][$aspectCode];
    }

    // ✅ LAYER 2: Check custom standard if selected
    $customStandardId = Session::get("selected_standard.{$templateId}");
    if ($customStandardId) {
        $customStandard = CustomStandard::find($customStandardId);
        if ($customStandard && isset($customStandard->aspect_configs[$aspectCode]['weight'])) {
            return $customStandard->aspect_configs[$aspectCode]['weight'];
        }
    }

    // ✅ LAYER 3: Fallback to quantum default
    $aspect = Aspect::where('template_id', $templateId)
        ->where('code', $aspectCode)
        ->first();
    return $aspect ? $aspect->weight_percentage : 0;
}
```

### Key Concepts:

#### **Baseline Selection**
User memilih baseline di halaman `StandardPsikometrik.php` atau `StandardMc.php`:
- **Quantum Default**: Standar umum sistem
- **Custom Standard**: Standar khusus institusi (dari database)

#### **Session Adjustment**
User bisa **temporary adjust** baseline yang dipilih:
```
User memilih: Custom Standard "Kejaksaan 2025"
↓
Custom Standard memiliki:
- Integritas: weight 15%, rating 4
- Kepemimpinan: weight 10%, rating 3

User adjust sementara (session):
- Kepemimpinan: weight 15% (was 10%)  ← LAYER 1 override

Hasil akhir yang digunakan:
- Integritas: 15%, rating 4  ← dari Custom Standard (LAYER 2)
- Kepemimpinan: 15%, rating 3  ← dari Session Adjustment (LAYER 1)
```

---

## 👤 User Flow & Scenarios

### Scenario 1: Analisis dengan Quantum Default

```
User Journey:
1. [EventSelector] Pilih event: "P3K Kejaksaan 2025"
2. [PositionSelector] Pilih posisi: "Jaksa Penuntut Umum"
3. [StandardPsikometrik] Default baseline: "Quantum Default" ✅
4. [RekapRankingAssessment] Lihat ranking 4,905 peserta
   ├─ Top 1: WINDA FUJIATI (score: 422.5)
   ├─ Top 2: ALMIRA ISWAHYUDI (score: 417.5)
   └─ ...

Timeline: ~1.5 detik
```

### Scenario 2: Eksplorasi dengan Custom Standard

```
User Journey:
1. [EventSelector] Pilih event: "P3K Kejaksaan 2025"
2. [PositionSelector] Pilih posisi: "Jaksa Penuntut Umum"
3. [StandardPsikometrik] Switch baseline: "Custom Standard: Kejaksaan" ✅
   ├─ System loads: custom_standards table (id: 1)
   ├─ Weights berubah: Integritas 10% → 15%
   └─ Ratings berubah: Kepemimpinan 3 → 4
4. [RekapRankingAssessment] Ranking RECALCULATED
   ├─ Cache invalidated (config hash berubah)
   ├─ New top 1: Might be different!
   └─ ...

Timeline: ~1.5 detik (after optimization!)
```

### Scenario 3: What-If Analysis (Session Adjustment)

```
User Journey:
1. Baseline aktif: "Custom Standard: Kejaksaan"
2. [CategoryWeightEditor] User adjust:
   ├─ Potensi: 25% → 30%
   └─ Kompetensi: 75% → 70%
3. [AspectSelector] User adjust:
   ├─ Kepemimpinan: weight 10% → 15%
   └─ Integritas: rating 4 → 5
4. [ToleranceSelector] User adjust tolerance: 0% → 10%

Effect:
├─ Session stores temporary adjustments (LAYER 1)
├─ Cache invalidated automatically
├─ All ranking pages recalculate instantly
└─ User sees: "Bagaimana jika standar seperti ini?"

User dapat:
✅ Save adjustments → Keep for this session
✅ Reset → Back to Custom Standard baseline
✅ Create Custom Standard → Permanent save to database
```

### Scenario 4: Individual Report

```
User Journey:
1. [RekapRankingAssessment] Click participant: "WINDA FUJIATI"
2. [GeneralMapping] Tampil individual report:
   ├─ Overall conclusion: "Memenuhi Standar"
   ├─ Ranking: 1 of 4905
   ├─ Aspect breakdown:
   │  ├─ Daya Pikir: 4.2 (Standard: 3.5) ✅
   │  ├─ Sikap Kerja: 3.8 (Standard: 3.0) ✅
   │  └─ ...
   └─ Charts: Rating vs Standard comparison

Data Source:
✅ IndividualAssessmentService (NOT RankingService)
✅ Loads sub-aspect details untuk breakdown
✅ Respects same 3-layer priority for standards
```

---

## 🗄️ Data Architecture

### Key Tables:

```sql
-- ============================================
-- STRUCTURE (Template & Configuration)
-- ============================================

assessment_templates (id, name, code)
├─ P3K Kejaksaan 2025
├─ CPNS Kemenkumham 2024
└─ ...

category_types (id, template_id, code, name, weight_percentage)
├─ potensi (25%)
└─ kompetensi (75%)

aspects (id, category_type_id, code, name, weight_percentage, standard_rating)
├─ daya-pikir (5%, rating: N/A - has sub-aspects)
├─ sikap-kerja (7%, rating: N/A - has sub-aspects)
├─ integritas (15%, rating: 4)
└─ kepemimpinan (10%, rating: 3)

sub_aspects (id, aspect_id, code, name, standard_rating)
├─ daya-analisa (rating: 3)
├─ kreativitas (rating: 4)
└─ fleksibilitas (rating: 3)


-- ============================================
-- ASSESSMENT DATA (Historical Results)
-- ============================================

participants (id, event_id, position_formation_id, name, test_number)
├─ 18576: WINDA FUJIATI
├─ 6736: ALMIRA ISWAHYUDI
└─ ... (4,905 participants)

aspect_assessments (id, participant_id, aspect_id, individual_rating, individual_score)
├─ Participant 18576, Aspect "Daya Pikir": rating 4.2, score 105.0
├─ Participant 18576, Aspect "Integritas": rating 5.0, score 75.0
└─ ... (4,905 * 13 = 63,765 records)

sub_aspect_assessments (id, aspect_assessment_id, sub_aspect_id, individual_rating)
├─ Aspect Assessment 2, SubAspect "Daya Analisa": rating 4
├─ Aspect Assessment 2, SubAspect "Kreativitas": rating 5
└─ ... (for aspects with sub-aspects only)


-- ============================================
-- CUSTOM STANDARDS (Institution Baseline)
-- ============================================

custom_standards (id, institution_id, template_id, code, name,
                 category_weights, aspect_configs, sub_aspect_configs)
Example data:
{
  "id": 1,
  "code": "KEJAKSAAN-2025",
  "name": "Standar Khusus Kejaksaan 2025",
  "category_weights": {
    "potensi": 30,      // Override dari 25%
    "kompetensi": 70    // Override dari 75%
  },
  "aspect_configs": {
    "integritas": {
      "weight": 15,     // Override dari 10%
      "rating": 5,      // Override dari 4
      "active": true
    },
    "kepemimpinan": {
      "weight": 12,     // Override dari 10%
      "active": true    // No rating karena punya sub-aspects
    }
  },
  "sub_aspect_configs": {
    "daya-analisa": {
      "rating": 4,      // Override dari 3
      "active": true
    },
    "kreativitas": {
      "rating": 5,      // Override dari 4
      "active": false   // ❌ Disabled in custom standard
    }
  }
}
```

### Data Flow:

```
[Assessment Day - Data Creation]
Assessor memberikan rating → SubAspectAssessment created
                          → AspectAssessment created (individual_rating calculated)
                          → CategoryAssessment created
                          ↓
                    [Data FINAL & IMMUTABLE]

[Analysis Phase - Data Reading]
User pilih Event & Position
                          ↓
User pilih Baseline (Quantum/Custom)
                          ↓
RankingService reads:
- aspect_assessments.individual_rating (NEVER CHANGES)
- Applies weights from baseline (CAN CHANGE)
- Applies standards from baseline (CAN CHANGE)
                          ↓
Rankings calculated → Display to user
                          ↓
User adjust session → Recalculate instantly
```

---

## 🎯 Key Principles

### 1. **BI System, NOT CRUD**
```
❌ WRONG: User menginput data peserta baru
✅ RIGHT: User mengeksplorasi data existing dengan filter berbeda

❌ WRONG: User mengubah individual_rating peserta
✅ RIGHT: User mengubah baseline untuk re-rank peserta

❌ WRONG: Real-time data updates
✅ RIGHT: Historical data + dynamic analysis
```

### 2. **Separation of Concerns**

```
DATA (Immutable)                    BASELINE (Configurable)
├─ individual_rating (from test)    ├─ weights (how important)
├─ participant name                 ├─ standard_rating (minimum expected)
└─ test date                        └─ active/inactive aspects

Rankings = DATA × BASELINE
```

### 3. **Cache Invalidation Strategy**

```php
// Cache key includes configuration hash
$configHash = md5(json_encode([
    'aspect_weights' => $aspectWeightsForHash,
    'session' => session()->getId(),
]));

// Automatic invalidation scenarios:
✅ User adjusts weight → Hash changes → Cache miss
✅ User switches baseline → Hash changes → Cache miss
✅ User changes tolerance → Applied AFTER cache (instant)
⏱️ Admin updates custom standard → Max 60s delay (acceptable for BI)
```

### 4. **Performance Optimization Philosophy**

```
Optimization Target: "Exploration Speed"
NOT: "Real-time accuracy"

Acceptable Trade-offs:
✅ 60s cache TTL (faster exploration, minor delay on admin changes)
✅ Pre-calculated data (faster ranking, data locked after assessment)
✅ Component-level caching (faster UI, refresh on baseline change)

Unacceptable:
❌ 10+ second load times (kills user exploration flow)
❌ Inconsistent ranking order (data integrity issue)
❌ Lost session adjustments (user loses work)
```

### 5. **Data-Driven Architecture**

```php
// ✅ GOOD: Let data structure determine logic
if ($aspect->subAspects->isNotEmpty()) {
    // Has sub-aspects → Calculate rating from them
    $rating = $this->calculateFromSubAspects($aspect);
} else {
    // No sub-aspects → Use direct rating
    $rating = $aspect->standard_rating;
}

// ❌ BAD: Hard-coded aspect codes
if ($aspectCode === 'daya-pikir' || $aspectCode === 'sikap-kerja') {
    // Breaks when new aspects added
}
```

### 6. **3-Layer Priority is Sacred**

```
NEVER bypass the priority system:

❌ BAD: Direct database read
$weight = $aspect->weight_percentage;

✅ GOOD: Through DynamicStandardService
$weight = $dynamicStandardService->getAspectWeight($templateId, $aspectCode);
// This respects: Session → Custom → Quantum

Why?
- User expects adjustments to work
- Custom standards must override defaults
- Session exploration must be temporary
```

---

## 📚 Related Documentation

- [CRITICAL_FIX_CUSTOM_STANDARD_PERFORMANCE.md](./CRITICAL_FIX_CUSTOM_STANDARD_PERFORMANCE.md) - Performance optimization details
- [OPTIMIZATION_STANDARD_MC.md](./OPTIMIZATION_STANDARD_MC.md) - StandardMc component optimization
- [OPTIMIZATION_TRAINING_RECOMMENDATION.md](./OPTIMIZATION_TRAINING_RECOMMENDATION.md) - Training recommendation optimization

---

## 🔧 Common Pitfalls

```php
// ❌ WRONG: Recalculating historical data
$individualRating = $this->calculateFromSubAspects($assessment);
// individual_rating is PRE-CALCULATED, never recalculate for ranking!

// ✅ RIGHT: Use pre-calculated data
$individualRating = (float) $assessment->individual_rating;

// ❌ WRONG: Eager loading everything
$assessments = AspectAssessment::with(['aspect.subAspects', 'subAspectAssessments'])->get();
// Loads 133K+ models unnecessarily

// ✅ RIGHT: Only load what you need
$assessments = AspectAssessment::query()->toBase()->get();
// Lightweight for ranking

// ❌ WRONG: Ignoring cache invalidation
$rankings = $this->calculateRankings();
Cache::forever('rankings', $rankings);
// Will show stale data after baseline change!

// ✅ RIGHT: Config-based cache key
$cacheKey = "rankings:{$configHash}";
Cache::remember($cacheKey, 60, fn() => $this->calculateRankings());
```

---

**Last Updated:** July 2026
**Maintainer:** SPSP Development Team
