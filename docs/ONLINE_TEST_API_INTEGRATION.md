# Dokumentasi Integrasi Data Mentah Ujian (API Tes Online & Database LSP)

> **Versi**: 2.0 (Penyatuan Dual-Source Data Mentah)  
> **Terakhir Diperbarui**: 29 Juli 2026  
> **Tujuan**: Referensi arsitektur penyimpanan data mentah hasil ujian dari API Quantum HRMI dan Koneksi Database LSP ke tabel `test_results` SPSP.

---

## 📌 1. Latar Belakang & Analisis Masalah

SPSP (Sistem Pemetaan & Statistik Psikologi) merupakan sistem Business Intelligence (BI) yang menganalisis hasil asesmen. 

### Masalah Awal (Keterbatasan Implementasi Awal)
1. **Persepsi Terisolasi**: Implementasi awal mengasumsikan data mentah ujian hanya datang dari **API Tes Online Quantum HRMI** melalui file dump JSON lokal (`--file` / `--dir`).
2. **Inkonsistensi Sumber Data**: SPSP memiliki sumber data utama kedua dari **Koneksi Database LSP (`DB_LSP_LOCAL`)** melalui tabel `ujian_peserta_produksi`. Sebelumnya, proses impor LSP langsung mengolah nilai tanpa mencatat skor mentahnya di tabel penampung.
3. **Audit Trail yang Terpisah**: Ketiadaan penanda asal data (*data source tracking*) menyulitkan audit asal data mentah di database.

### Solusi Arsitektur Penyatuan (*Single Source of Truth*)
Seluruh data mentah hasil ujian—baik yang ditarik via **API REST Quantum HRMI**, di-import via **File JSON**, maupun disinkronkan dari **Koneksi Database LSP**—diarahkan dan disimpankan ke satu tabel penampung generik **`test_results`** dengan kolom **`source`** sebagai penanda asal data.

```
┌──────────────────────────────────────────┐
│  Source 1: API Tes Online Quantum HRMI   │
│  (HTTP REST API Client / JSON Dump File) │
└────────────────────┬─────────────────────┘
                     │
                     ├───────────────────► ┌──────────────────────────┐     ┌────────────────────────┐
                     │                     │    Tabel test_results    │     │ Converter Engine SPSP  │
┌────────────────────┴─────────────────────┐ │  (Single Source of Truth)│────►│ (Tahap 2: Rating 1-5)  │
│  Source 2: Koneksi Database LSP          │ │                          │     │                        │
│  (DB_LSP_LOCAL: ujian_peserta_produksi)  │ └──────────────────────────┘     └────────────────────────┘
└──────────────────────────────────────────┘
```

---

## 🗄️ 2. Skema Database `test_results`

Tabel generik `test_results` menyimpan data mentah hasil tes sebelum di-konversi ke rating 1–5 SPSP.

| Kolom | Tipe | Nullable | Deskripsi |
| :--- | :--- | :--- | :--- |
| `id` | bigint | No | Primary Key |
| `participant_id` | bigint | No | Foreign Key ke `participants.id` |
| `event_id` | bigint | No | Foreign Key ke `assessment_events.id` |
| `test_code` | varchar(50) | No | Kode unik alat tes (e.g., `A.1`, `A.5`, `B.2`, `D.1`, `D.2`) |
| `test_name` | varchar(255) | No | Nama instrumen tes (e.g., "Typical CFIT3A", "PAPI Kostik") |
| `test_category` | varchar(100) | No | Kategori tes (e.g., "Kecerdasan / IQ", "Sikap Kerja") |
| `status` | varchar(20) | No | Status pelaksanaan tes (`completed` / `incomplete`) |
| `source` | varchar(50) | No | **Asal Sumber Data**: `api`, `lsp_db`, atau `file_import` |
| `test_started_at` | timestamp | Yes | Waktu mulai tes |
| `summary_data` | json | No | Skor ringkasan kuantitatif (JSON) |
| `interpretation_data` | json | Yes | Teks interpretasi psikologis & saran pengembangan (JSON) |
| `raw_response` | json | No | Backup respons asli API / DB mentah (JSON) |
| `conversion_status` | enum | No | Status konversi ke rating SPSP (`pending`, `converted`, `skipped`, `not_applicable`) |
| `converted_at` | timestamp | Yes | Waktu sukses dikonversi ke rating 1–5 SPSP |
| `created_at` / `updated_at` | timestamp | Yes | Audit timestamps |

### Constraints & Indexes
1. **Unique Constraint (`participant_id`, `event_id`, `test_code`)**: Menjamin operasi bersifat *idempotent* (`updateOrCreate` / `upsert`).
2. **Index `(event_id, test_code)`**: Mempercepat query filtering data tes per event.
3. **Index `source`**: Mempercepat pencarian & audit trail berdasarkan sumber data.
4. **Index `conversion_status`**: Mempercepat antrean engine konversi rating.

---

## ⚙️ 3. Pipa Data & Komponen Terkait

### A. Pipa 1: API Tes Online Quantum HRMI
* **`App\Services\Api\QuantumApiClient`**: Layanan HTTP Client Laravel yang melakukan request ke Endpoint REST API Quantum HRMI (`/api/v1/test-results`) dengan fallback mock generator otomatis jika URL live API belum dikonfigurasi.
* **`App\Services\TestResultImportService`**: Service parser generik untuk 9 alat tes (CFIT, IST, Karakter, 16PF, Kraeplin, EQ, Behavior, RMIB) yang menerima data payload API dan menyimpannya ke `test_results` dengan `source = 'api'` atau `source = 'file_import'`.
* **Stripping Detail Kraeplin (`D.2`)**: Meng-unset field `detail` per-soal (200KB+) secara otomatis untuk mencegah pembengkakan database (*DB Bloat*).
* **Bypass MMPI (`E.1`, `E.2`)**: Secara otomatis di-skip dari `test_results` karena dikelola di tabel khusus `psychological_tests`.

### B. Pipa 2: Koneksi Database LSP (`DB_LSP_LOCAL`)
* **`App\Services\Lsp\LspDataTransformerService`**: Menyertakan skor mentah (`raw_scores`) dari tabel `ujian_peserta_produksi` (untuk instrumen `ist`, `kostik`, dan `personality`) pada payload laporan individu.
* **`App\Services\Lsp\LspDataImporterService`**: Saat melakukan impor/sinkronisasi proyek LSP (`php artisan lsp:import`), service secara otomatis melakukan bulk `upsert` ke tabel `test_results` dengan penanda **`source = 'lsp_db'`** dan **`conversion_status = 'pending'`**.

---

## 🛠️ 4. Penggunaan CLI Command

### A. Impor Data Mentah API Tes Online
Command `php artisan test-results:import` mendukung penarikan API direct maupun file dump:

```bash
# 1. Tarik data tes peserta langsung dari Client API Quantum HRMI
php artisan test-results:import --fetch-api --event=1 --participant=15436

# 2. Impor dari direktori sampel file JSON
php artisan test-results:import --dir="output_analisis/sample_per_tes/" --event=1 --participant=15436

# 3. Impor dari satu file JSON spesifik
php artisan test-results:import --file="sample_cfit.json" --event=1 --participant=15436

# 4. Dry-run (Preview data tanpa menyimpan ke DB)
php artisan test-results:import --fetch-api --event=1 --participant=15436 --dry-run
```

### B. Impor Data Proyek LSP (Otomatis Mengisi `test_results`)
Command `php artisan lsp:import` otomatis mengisi tabel `participants`, `aspect_assessments`, sekaligus merekam data mentah ke **`test_results`**:

```bash
# Impor seluruh peserta dalam 1 proyek LSP
php artisan lsp:import PR-A-313

# Impor 1 peserta spesifik
php artisan lsp:import PR-A-313 --username=bntn01-001
```

---

## 🔮 5. Aliran Pengembangan Selanjutnya (Tahap 2 Engine Konversi)

Setelah seluruh data mentah dari kedua sumber tersimpan di `test_results`, langkah selanjutnya adalah mengeksekusi **Engine Konversi Rating 1-5**:

```
[test_results] (conversion_status = pending)
       │
       ▼ (Dibaca oleh Rating Converter Service)
[Normalisasi Skala Skor]
 ├─ CFIT / Karakter (B.1) --> Menggunakan skala rating asli (1-5)
 ├─ 16PF (B.2)             --> Konversi skala sten 1-10 ke 1-5
 ├─ IST (A.5)              --> Konversi rentang IQ ke rating 1-5
 ├─ Kraeplin / EQ          --> Konversi skala 1-4 ke 1-5
 └─ Behavior / RMIB        --> Di-skip (not_applicable) / penanganan khusus
       │
       ▼ (Updated)
[sub_aspect_assessments] / [aspect_assessments] (SPSP Ratings)
```

Dengan arsitektur ini, perubahan rumus konversi nilai di kemudian hari tidak memerlukan penarikan ulang dari API Quantum HRMI maupun query ulang ke Database LSP.
