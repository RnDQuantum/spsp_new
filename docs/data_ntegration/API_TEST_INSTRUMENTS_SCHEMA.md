# 📊 Spesifikasi Skema Data & Katalog Alat Tes Online (REST API)

[← Kembali ke Indeks Dokumentasi Integrasi LSP](./README.md)

- **Modul**: Integrasi REST API Tes Online (`psikotes.qhrmi.id` - Jalur B `≥ PR-A-338`) $\rightarrow$ Native SPSP System
- **Sumber Data Referensi Riset**: `D:\bima\RND\SPSP\collect data api tes online\output_analisis\hasil fix\`
- **Folder Sampel JSON per Tes**: `sample_per_tes_20260701_121933/` (36 file .json)
- **Terkait Kode**:
  - [QuantumApiClient.php](file:///c:/laragon/www/spsp_new/app/Services/Api/QuantumApiClient.php) (REST API Client)
  - [ApiDataTransformerService.php](file:///c:/laragon/www/spsp_new/app/Services/Api/ApiDataTransformerService.php) (Transformer DTO SPSP)
  - [TestResult.php](file:///c:/laragon/www/spsp_new/app/Models/TestResult.php) (Model & Single Source of Truth `test_results`)
  - [TestReportService.php](file:///c:/laragon/www/spsp_new/app/Services/TestReportService.php) (Formatter UI Laporan Alat Tes)
- **Tanggal Pembaruan**: 18 Agustus 2026

---

> [!IMPORTANT]
> **Konteks & Fungsi Dokumen**:
> Dokumen ini memuat spesifikasi struktur payload JSON mentah dari endpoint REST API `/api/ambil_semua` (`psikotes.qhrmi.id`), taksonomi 35 instrumen tes, skema per alat tes yang terisi, serta pemetaannya ke kolom `summary_data` pada tabel `test_results` di database native SPSP.

---

## 🗂️ 1. Katalog & Taksonomi 35 Alat Tes API Online

Berdasarkan hasil ekstraksi komprehensif pada 31 proyek asesmen online (Direktori Riset: `D:\bima\RND\SPSP\collect data api tes online\output_analisis\hasil fix\ringkasan_20260701_121933.txt`), teridentifikasi **35 instrumen alat tes unik** yang dikelompokkan ke dalam 11 kategori:

| Kategori | Kode Tes | Nama Alat Tes | Total Proyek | Status Data API |
| :--- | :--- | :--- | :-: | :-: |
| **Kecerdasan / IQ** | `A.1` | Typical CFIT3A | 18 Proyek | 🟢 **Terisi** |
| | `A.2` | Typical CFIT3B | 19 Proyek | 🟢 **Terisi** |
| | `A.5` | Typical IST | 10 Proyek | 🟢 **Terisi** |
| **Kepribadian / Karakter** | `B.1` | KOMPETENSI KARAKTER (PAPI Kostik) | 27 Proyek | 🟢 **Terisi** |
| **Kepribadian / Psikometri** | `B.2` | Typical 16PF | 26 Proyek | 🟢 **Terisi** |
| **Sikap Kerja** | `D.2` | Typical Kraepelin | 5 Proyek | 🟢 **Terisi** |
| **Kemampuan Khusus / Klinis** | `E.1` | Typical MMPI 180 | 5 Proyek | 🟢 **Terisi** |
| | `E.2` | Typical MMPI 567 | 10 Proyek | 🟢 **Terisi** |
| | `E.4` | Typical DASS | 2 Proyek | ⚪ *Kosong* |
| | `E.TBN` | TEKNIS ESSAY TBN | 1 Proyek | ⚪ *Kosong* |
| | `E.TWK` | TEST ESSAY WAWASAN KEBANGSAAN | 1 Proyek | ⚪ *Kosong* |
| | `E.Z.6` | TEKNIS ESSAY INTEGRITAS | 2 Proyek | ⚪ *Kosong* |
| | `E.Z.7` | TEKNIS ESSAY MORALITI | 2 Proyek | ⚪ *Kosong* |
| **Kecerdasan Emosional (EQ)** | `F.1` | Typical EQ | 3 Proyek | 🟢 **Terisi** |
| **Kecenderungan Perilaku** | `G.1` | Typical Behavior Tendencies | 1 Proyek | 🟢 **Terisi** |
| **Minat Jabatan** | `H.1` | RMIB | 3 Proyek | 🟢 **Terisi** |
| **Kepribadian / Big Five** | `BIG.5` | BIG FIVE INVENTORY | 11 Proyek | ⚪ *Kosong* |
| **Problem Analysis Simulation** | `PAS.1` | PROBLEM ANALYSIS | 9 Proyek | ⚪ *Kosong* |
| **Project Assignment** | `PA.1` | PROJECT ASSIGNMENT | 1 Proyek | ⚪ *Kosong* |
| | `PA.359_1` | PROJECT ASSIGNMENT - PR-A-359 | 1 Proyek | ⚪ *Kosong* |
| | `PA.359_2` | PROJECT ASSIGNMENT - PR-A-359 | 1 Proyek | ⚪ *Kosong* |
| **Self Asesmen & Mandiri** | `SA.1` s.d `SA.GARAM` | TES SELF ASESMEN & KOMPETENSI MANDIRI | 23 Proyek | ⚪ *Kosong* |
| **Simulasi Manajerial** | `MS.1` s.d `MS.359_2` | MANAGERIAL SIMULATION | 5 Proyek | ⚪ *Kosong* |
| **Lainnya & Teknis MC** | `TBN`, `TWK`, `Z.6`, `Z.7` | TES TEKNIS MC & WAWASAN | 6 Proyek | ⚪ *Kosong* |

---

## 🔬 2. Rincian Skema JSON 11 Alat Tes Terisi

Di bawah ini adalah rincian struktur schema payload `response_utuh` yang diterima dari API untuk setiap instrumen yang terisi (berdasarkan file sampel pada `sample_per_tes_20260701_121933/`):

### 1. `A.1` & `A.2` — Typical CFIT 3A / 3B (Kecerdasan / IQ)
* **Sampel File**: `A.1.json` & `A.2.json`
* **Struktur Payload Utama**:
```json
{
  "status": true,
  "mulai_tes": "2025-11-28 08:23:27",
  "total": 12,
  "iq": 70,
  "kategori": "Borderline",
  "umur_format": "23_6",
  "index_umur": "ge17",
  "index_kecerdasan_umum": 2,
  "hasil_sub": {
    "sub1": { "nilai": 4, "total_soal": 13, "persentase": 30.76, "rating": 2, "deskripsi": "Kurang" },
    "sub2": { "nilai": 2, "total_soal": 14, "persentase": 14.28, "rating": 1, "deskripsi": "Rendah" },
    "sub3": { "nilai": 5, "total_soal": 13, "persentase": 38.46, "rating": 2, "deskripsi": "Kurang" },
    "sub4": { "nilai": 1, "total_soal": 10, "persentase": 10.00, "rating": 1, "deskripsi": "Rendah" }
  },
  "INTERPRETASI_HASIL": {
    "Kecerdasan Umum": "...",
    "Subtes 1 (Series/Serial Reasoning)": "...",
    "Subtes 2 (Classification/Grouping)": "...",
    "Subtes 3 (Matrices/Analisis Visual)": "...",
    "Subtes 4 (Conditions/Topologi)": "..."
  },
  "SARAN_PENGEMBANGAN": [
    "Saran pengembangan poin 1...",
    "Saran pengembangan poin 2..."
  ]
}
```

---

### 2. `A.5` — Typical IST (Intelligenz Struktur Test)
* **Sampel File**: `A.5.json`
* **Struktur Payload Utama**:
```json
{
  "status": true,
  "hasil_ist": {
    "se": { "rs": 9, "sw": 97, "kategori": "Rata-rata" },
    "wa": { "rs": 12, "sw": 105, "kategori": "Rata-rata" },
    "an": { "rs": 7, "sw": 92, "kategori": "Rata-rata Bawah" },
    "ge": { "rs": 7, "sw": 95, "kategori": "Rata-rata" },
    "ra": { "rs": 5, "sw": 88, "kategori": "Rata-rata Bawah" },
    "zr": { "rs": 4, "sw": 85, "kategori": "Rata-rata Bawah" },
    "fa": { "rs": 8, "sw": 99, "kategori": "Rata-rata" },
    "wu": { "rs": 3, "sw": 82, "kategori": "Rata-rata Bawah" },
    "me": { "rs": 16, "sw": 118, "kategori": "Rata-rata Atas" }
  },
  "rs": 71,
  "iq": 96,
  "kategori": "Rata-rata",
  "hasil_kategori": { "IQ": "Rata-rata" }
}
```

---

### 3. `B.1` — KOMPETENSI KARAKTER (PAPI Kostik)
* **Sampel File**: `B.1.json`
* **Struktur Payload Utama**:
```json
{
  "status": true,
  "mulai_tes": "2026-05-19 09:30:00",
  "hasil_G": 7, "hasil_L": 5, "hasil_I": 4, "hasil_T": 6, "hasil_V": 5,
  "hasil_S": 8, "hasil_R": 3, "hasil_D": 6, "hasil_C": 5, "hasil_E": 7,
  "hasil_N": 4, "hasil_A": 6, "hasil_P": 5, "hasil_X": 6, "hasil_B": 4,
  "hasil_O": 7, "hasil_Z": 5, "hasil_K": 6, "hasil_F": 4, "hasil_W": 6,
  "arah_kerja_1": "...", "arah_kerja_2": "...", "arah_kerja_3": "...",
  "gaya_kerja_1": "...", "gaya_kerja_2": "...", "gaya_kerja_3": "...",
  "activity_1": "...", "activity_2": "...",
  "leadership_1": "...", "leadership_2": "...", "leadership_3": "...",
  "followership_1": "...", "followership_2": "...",
  "social_1": "...", "social_2": "...", "social_3": "...", "social_4": "...",
  "temprament_1": "...", "temprament_2": "...", "temprament_3": "..."
}
```

---

### 4. `B.2` — Typical 16PF (Sixteen Personality Factor)
* **Sampel File**: `B.2.json`
* **Struktur Payload Utama**:
```json
{
  "status": true,
  "kode": "16PF",
  "kategori": "Normal",
  "MDStenScore": 6,
  "standart_final": {
    "A": 6, "B": 5, "C": 7, "E": 6, "F": 4, "G": 8, "H": 5,
    "I": 4, "L": 6, "M": 5, "N": 7, "O": 5, "Q1": 6, "Q2": 5, "Q3": 7, "Q4": 4
  },
  "WS": {
    "A": 12, "B": 8, "C": 15, "E": 11, "F": 9, "G": 16, "H": 10,
    "I": 8, "L": 11, "M": 9, "N": 13, "O": 10, "Q1": 11, "Q2": 9, "Q3": 14, "Q4": 7
  },
  "nilaiAspek": {
    "Hangat": 6, "Cerdas": 5, "Stabil": 7, "Dominan": 6, "Ceria": 4,
    "Sadar Aturan": 8, "Pemberani": 5, "Peka": 4, "Waspada": 6,
    "Imajinatif": 5, "Cerdik": 7, "Khawatir": 5, "Terbuka": 6,
    "Mandiri": 5, "Perfeksionis": 7, "Tegang": 4
  }
}
```

---

### 5. `D.2` — Typical Kraepelin (Sikap Kerja)
* **Sampel File**: `D.2.json`
* **Struktur Payload Utama**:
```json
{
  "status": true,
  "mulai_tes": "2026-05-19 10:32:27",
  "skor_b": 0.0408,
  "skor_a": 6.4979,
  "skor_X45": 8.5411,
  "skor_X0": 6.4979,
  "pendidikan": "S1",
  "kesimpulan": {
    "panker": 7.54,
    "janker_range": 8,
    "janker_average": 1.5616,
    "tianker": 61,
    "hanker": 2.0432
  },
  "kesimpulan_akhir": {
    "panker": 1,
    "janker_range": 3,
    "janker_average": 2,
    "tianker": 1,
    "hanker": 4
  }
}
```
* **Keterangan Dimensi**:
  - `panker`: Kecepatan Kerja
  - `janker`: Ketelitian Kerja (Range & Average)
  - `tianker`: Ketahanan Kerja
  - `hanker`: Kestabilan Kerja

---

### 6. `E.1` & `E.2` — Typical MMPI 180 / 567 (Klinis / Kejiwaan)
* **Sampel File**: `E.1.json` & `E.2.json`
* **Struktur Payload Utama**:
```json
{
  "status": true,
  "datafix": {
    "no_tes": "004",
    "nama_peserta": "Wanda Anggi Pangestu",
    "json": {
      "peserta": { "nama": "...", "umur": "24", "pendidikan": "S1" },
      "skore_bro": {
        "L": { "raw_score": 3, "t_score": 45 },
        "F": { "raw_score": 4, "t_score": 50 },
        "K": { "raw_score": 14, "t_score": 55 },
        "Hs": { "raw_score": 2, "t_score": 48 },
        "D": { "raw_score": 18, "t_score": 52 },
        "Hy": { "raw_score": 15, "t_score": 50 },
        "Pd": { "raw_score": 14, "t_score": 54 },
        "Mf": { "raw_score": 22, "t_score": 46 },
        "Pa": { "raw_score": 8, "t_score": 50 },
        "Pt": { "raw_score": 10, "t_score": 48 },
        "Sc": { "raw_score": 12, "t_score": 52 },
        "Ma": { "raw_score": 16, "t_score": 55 },
        "Si": { "raw_score": 20, "t_score": 49 }
      },
      "rekapitulasi": {
        "validitas": "Valid & Konsisten",
        "tingkat_stres": "Normal",
        "nilai_pq": 85.5,
        "kesimpulan": "Bebas dari Gejala Klinis Berat"
      }
    }
  }
}
```

---

### 7. `F.1` — Typical EQ (Kecerdasan Emosional)
* **Sampel File**: `F.1.json`
* **Struktur Payload Utama**:
```json
{
  "status": true,
  "skor_akhir": 346,
  "kategori": "Istimewa",
  "dimensi": {
    "4": { "nama": "Kesadaran Emosi Diri", "skor": 28 },
    "5": { "nama": "Pengungkapan Emosi", "skor": 24 },
    "6": { "nama": "Kesadaran Emosi Orang Lain", "skor": 26 },
    "7": { "nama": "Keluwesan", "skor": 32 },
    "8": { "nama": "Kemandirian", "skor": 25 },
    "9": { "nama": "Penghargaan Diri", "skor": 27 },
    "10": { "nama": "Hubungan Antar Pribadi", "skor": 30 },
    "11": { "nama": "Tanggung Jawab Sosial", "skor": 22 },
    "12": { "nama": "Penyelesaian Masalah", "skor": 29 },
    "13": { "nama": "Uji Realitas", "skor": 31 },
    "14": { "nama": "Pengendalian Dorongan Hati", "skor": 18 },
    "15": { "nama": "Ketahanan Terhadap Stres", "skor": 27 },
    "16": { "nama": "Daya Pribadi", "skor": 30 },
    "17": { "nama": "Integritas", "skor": 19 }
  },
  "hasil_akhir": {
    "4": 3, "5": 2, "6": 3, "7": 4, "8": 3, "9": 3, "10": 3,
    "11": 2, "12": 3, "13": 4, "14": 1, "15": 3, "16": 3, "17": 3
  }
}
```

---

### 8. `G.1` — Typical Behavior Tendencies (Kecenderungan Perilaku / DISC)
* **Sampel File**: `G.1.json`
* **Struktur Payload Utama**:
```json
{
  "status": true,
  "iman": "17",
  "pikiran": "36",
  "perasaan": "14",
  "hasil_kecenderungan": "ILMUWAN",
  "interpretasi_kebiasaan": "Jenis kecenderungan perilaku tersebut akan muncul pada diri seseorang manakala nilai pikirannya lebih dominan..."
}
```

---

### 9. `H.1` — RMIB (Rothwell Miller Interest Blank)
* **Sampel File**: `H.1.json`
* **Struktur Payload Utama**:
```json
{
  "status": true,
  "nilai_1": "Clerical",
  "nilai_2": "Musical",
  "nilai_3": "Computational",
  "nilai": "10,8,3"
}
```

---

## 🗄️ 3. Pemetaan ke Database Native SPSP (`test_results`)

Tabel `test_results` bertindak sebagai *single source of truth* untuk seluruh rincian instrumen tes.

| Field Database | Sumber dari Payload API | Contoh Isi Nilai |
| :--- | :--- | :--- |
| `participant_id` | Foreign Key ke tabel `participants` | `101` |
| `assessment_event_id`| Foreign Key ke tabel `assessment_events` | `5` |
| `test_code` | Kode unik instrumen tes | `'A.1'`, `'A.5'`, `'B.1'`, `'B.2'`, `'D.2'`, `'E.2'`, `'F.1'`, `'G.1'`, `'H.1'` |
| `test_name` | Nama resmi instrumen | `'Typical CFIT3A'`, `'Typical 16PF'`, `'Typical Kraeplin'` |
| `test_category` | Kategori tes | `'Kecerdasan / IQ'`, `'Kepribadian'`, `'Sikap Kerja'`, `'Klinis'` |
| `source` | Sumber data | `'api'` (untuk Jalur B) atau `'lsp_db'` (untuk Jalur A) |
| `score` | Nilai skor utama / agregat | IQ (`70`, `115`), Sten MD (`6`), Total EQ (`346`), Kraepelin Panker (`7.54`) |
| `category` | Label kategori verbal | `'Rata-rata'`, `'Borderline'`, `'Istimewa'`, `'Normal'` |
| `summary_data` | JSON lengkap terstruktur | Payload utuh instrumen termasuk `hasil_sub`, `INTERPRETASI_HASIL`, `SARAN_PENGEMBANGAN`, dsb. |

---

## 🛠️ 4. Panduan Developer: Integrasi & Formatter UI

1. **Konsumsi di `ApiDataTransformerService`**:
   - Ekstraksi MMPI langsung diarahkan ke model `Mmpi` (`validitas`, `tingkat_stres`, `nilai_pq`, `kesimpulan_kejiwaan`).
   - Seluruh payload alat tes disimpan dalam format array standar `tes[]` pada DTO hasil transformasi.

2. **Konsumsi di `TestReportService` & Livewire**:
   - Method `formatTestDataForDisplay($testResult)` membaca kolom `summary_data` berdasarkan `test_code`.
   - Livewire component `DetailLaporanTes` merender komponen UI visual (Card IQ, Radar Chart Kepribadian, Bar Chart Kraepelin, Accordion Interpretasi) secara dinamis sesuai skema di atas.
