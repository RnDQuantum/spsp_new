# 📖 Panduan Operasional Integrasi & Sinkronisasi Data LSP ke SPSP

[← Kembali ke Indeks Dokumentasi Integrasi LSP](./README.md)

- **Modul**: Integrasi LSP (Quantum HRMI) $\rightarrow$ Native SPSP System
- **File Service**:
  - [LspNormEngineService.php](file:///c:/laragon/www/spsp_new/app/Services/Lsp/LspNormEngineService.php) (Engine Perhitungan Norma Psikometri)
  - [LspDataTransformerService.php](file:///c:/laragon/www/spsp_new/app/Services/Lsp/LspDataTransformerService.php) (Transformer Data Impor LSP)
  - [LspDataImporterService.php](file:///c:/laragon/www/spsp_new/app/Services/Lsp/LspDataImporterService.php) (Importer Data ke DB Native SPSP)
  - [TestReportService.php](file:///c:/laragon/www/spsp_new/app/Services/TestReportService.php) (Fondasi Laporan Alat Tes Native)
- **File Command**: `TestLspIndividualReport.php` & `ImportLspData.php`
- **Lokasi Norma**: `resources/data/lsp_norms/` (`ist.json`, `kostik.json`, `personality.json`)
- **Tanggal Pembaruan**: 29 Juli 2026

---

> [!NOTE]
> **Ikhtisar Pipeline Integrasi**:
> Modul integrasi bertugas mengambil data mentah dari clone database LSP (`DB_LSP_LOCAL` / koneksi `lsp`), mengolah norma psikometri presisi via `LspNormEngineService`, mentransformasi data via `LspDataTransformerService`, dan menyinkronkannya ke tabel native SPSP via `LspDataImporterService`.

---

## 🏗️ 1. Diagram Alur Data Pipeline

```mermaid
flowchart TD
    subgraph LSP_DATABASE [DB_LSP_LOCAL / DB LSP]
        A1[peserta_produksi]
        A2[ujian_peserta_produksi]
        A3[rekapmmpi_p3kkjg]
        A4[hasil_aspek_yang_digali]
        A5[kamus_potensi & kamus_kompetensi]
    end

    subgraph SPSP_ENGINE [Laravel SPSP Engine]
        B1[LspNormEngineService]
        B2[LspDataTransformerService]
        B3[LspDataImporterService]
        B4[Norm JSON: ist, kostik, personality]
    end

    subgraph SPSP_DATABASE [Database Native SPSP]
        C1[participants]
        C2[psychological_tests]
        C3[interpretations]
        C4[aspect_assessments & sub_aspect_assessments]
        C5[category_assessments]
        C6[final_assessments]
        C7[test_results]
    end

    LSP_DATABASE --> B2
    B4 --> B1
    B1 --> B2
    B2 --> B3
    B3 --> SPSP_DATABASE
```

---

## 💻 2. Penggunaan Command Artisan CLI

> [!TIP]
> **Dua Command Utama**:
> 1. `lsp:test-report`: Menguji transformer tanpa menulis ke database SPSP (Read-Only Test).
> 2. `lsp:import`: Mengimpor & menyinkronkan data ke tabel-tabel native SPSP.

### A. Uji Coba Transformer & Laporan Individu (Dry-Run)
Menguji kalkulasi norma & transformasi data peserta secara instan dan menampilkan DTO/tabel pada terminal tanpa menyimpan ke database SPSP:
```bash
php artisan lsp:test-report <username_peserta> <kode_proyek>
```

*Contoh*:
```bash
php artisan lsp:test-report bntn01-001 PR-A-313
```

---

### B. Impor / Sinkronisasi Data LSP ke Database Native SPSP
Mengimpor data peserta dari database LSP dan menyimpan/menyinkronkannya ke tabel-tabel native SPSP:

```bash
# 1. Impor seluruh peserta dalam 1 proyek LSP
php artisan lsp:import <kode_proyek>

# 2. Impor spesifik 1 username peserta saja
php artisan lsp:import <kode_proyek> --username=<username_peserta>

# 3. Impor dengan menentukan ID Instansi SPSP spesifik
php artisan lsp:import <kode_proyek> --institution=<institution_id>
```

*Contoh*:
```bash
php artisan lsp:import PR-A-313 --username=bntn01-001 --institution=1
```

---

## 🧪 3. Eksekusi Automated Test Suite

Untuk memastikan seluruh pengujian integrasi LSP berjalan tanpa error dan validasi norma 100% presisi:

| Test Target | Perintah Artisan Test | Status Assertions |
| :--- | :--- | :-: |
| **Seluruh Integrasi LSP** | `php artisan test --compact --filter=Lsp` | 🟢 **2 Passed (27 Assertions)** |
| **LspNormEngineService** | `php artisan test --compact --filter=LspNormEngineServiceTest` | 🟢 **Passed** |
| **LspDataTransformerService** | `php artisan test --compact --filter=LspIndividualReportServiceTest` | 🟢 **Passed** |
| **LspDataImporterService** | `php artisan test --compact --filter=LspDataImporterServiceTest` | 🟢 **Passed** |

---

## 📁 4. Lokasi & Struktur File Norma JSON

File norma psikometri disimpan pada direktori:
`resources/data/lsp_norms/`

| Nama File Norma | Deskripsi & Kegunaan |
| :--- | :--- |
| **`ist.json`** | Norma konversi 9 subtest IST & kalkulasi total IQ berdasarkan tingkat pendidikan dan norma usia. |
| **`kostik.json`** | Norma konversi 20 faktor kepribadian kerja PAPI Kostik. |
| **`personality.json`** | Norma konversi Sten Score (1–10) 16PF dengan penyesuaian koreksi Motivasi Manipulasi (MD). |
