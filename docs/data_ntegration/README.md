# 📑 LSP Integration & Legacy Data Synchronization — Documentation Index

Selamat datang di indeks dokumentasi **LSP Integration & Legacy Data Synchronization** pada Sistem Pemetaan & Statistik Psikologi (SPSP).

Modul ini bertanggung jawab untuk melakukan ekstraksi, transformasi norma psikometri, dan sinkronisasi data historis/real-time dari clone database LSP (Quantum HRMI / CodeIgniter 3 legacy) ke skema database native SPSP.

---

> [!NOTE]
> **Arsitektur Utama & Dual-Path Data Ingestion**:
> Untuk memahami fondasi utama SPSP dan pemisahan Jalur A (Legacy DB `< PR-A-338`) vs Jalur B (API Online `≥ PR-A-338`), baca dokumen **[FUNDAMENTAL_ARCHITECTURE.md](./FUNDAMENTAL_ARCHITECTURE.md)**.
> Seluruh *pipeline* integrasi LSP beroperasi secara **100% dinamis dan generic** berbasis variabel proyek (`$kodeProyek`) dan nama pengguna (`$username`).

---

## 📚 Struktur Dokumentasi Integrasi LSP

Dokumentasi Integrasi LSP terstruktur secara ringkas dan efektif ke dalam 4 dokumen utama:

| Dokumen Spesifikasi | Deskripsi & Cakupan Utama | Status Pipeline |
| :--- | :--- | :-: |
| **[FUNDAMENTAL_ARCHITECTURE.md](./FUNDAMENTAL_ARCHITECTURE.md)** | **Master Dokumentasi**: Arsitektur fondasi SPSP, Dual-Path Ingestion (Legacy vs API Baru), 5 file norma resmi, dan 2-Level Konversi. | 🟢 **MASTER DOC** |
| **[API_TEST_INSTRUMENTS_SCHEMA.md](./API_TEST_INSTRUMENTS_SCHEMA.md)** | **Spesifikasi Skema API & Katalog Alat Tes**: Taksonomi 35 alat tes API online (`psikotes.qhrmi.id`), rincian schema JSON 11 alat tes terisi (CFIT, IST, 16PF, PAPI, Kraepelin, MMPI, EQ, DISC, RMIB), serta mapping `test_results.summary_data`. | 🟢 **ACTIVE SCHEMA** |
| **[INTEGRATION_GUIDE.md](./INTEGRATION_GUIDE.md)** | **Panduan Operasional**: CLI execution (`lsp:test-report` & `lsp:import`), pengujian otomatis (`php artisan test`), dan lokasi norma JSON. | ✅ **DOCUMENTED** |
| **[FALLBACK_AND_SAFETY_MECHANISMS.md](./FALLBACK_AND_SAFETY_MECHANISMS.md)** | **Spesifikasi Keamanan**: Matriks *fallback* data mentah, pembatasan *error*, dan mekanisme isolasi transaksi impor 100-chunk. | ✅ **DOCUMENTED** |
| **[SERVICES_ARCHITECTURE.md](./SERVICES_ARCHITECTURE.md)** | **Arsitektur Service Codebase**: Penjelasan multi-service SRP (`LspNormEngineService`, `LspDataTransformerService`, `LspDataImporterService`). | ✅ **DOCUMENTED** |

---

## 🏗️ Ringkasan Arsitektur Service Pipeline

Integrasi LSP menerapkan arsitektur *Single Responsibility Principle* (SRP) yang memisahkan tugas pengolahan norma, ekstraksi data legacy (Jalur A), konsumsi REST API (Jalur B), dan sinkronisasi database SPSP:

```mermaid
flowchart TD
    subgraph INGESTION_SOURCES [Dual Ingestion Sources]
        DB1[(peserta_produksi\nDB_LSP_LOCAL: Proyek < PR-A-338)]
        API1[REST API: psikotes.qhrmi.id\nProyek ≥ PR-A-338]
    end

    subgraph LSP_SERVICES [Layer Pipeline Integrasi LSP]
        S1[LspNormEngineService + 5 Norm JSONs]
        S2[LspDataTransformerService: Jalur A]
        S3[ApiDataTransformerService + QuantumApiClient: Jalur B]
        S4[LspDataImporterService: Core Importer]
    end

    subgraph SPSP_NATIVE [Layer System & Database Native SPSP]
        DB_SPSP[(Database Native SPSP)]
        S5[TestReportService]
        S6[IndividualAssessmentService]
    end

    DB1 --> S2
    S1 -->|Standard Scores & Ratings| S2
    API1 --> S3
    S2 -->|Payload DTO| S4
    S3 -->|Payload DTO Matang| S4
    S4 -->|Bulk Upsert| DB_SPSP

    DB_SPSP --> S5
    DB_SPSP --> S6
```

---

## ⚡ Panduan Ringkas Operasional (Quick Start)

> [!TIP]
> **Perintah CLI Utama Integrasi**:
> ```bash
> # 1. Uji coba kalkulasi norma & transformer 1 peserta (Tanpa simpan DB SPSP)
> php artisan lsp:test-report <username_peserta> <kode_proyek>
> 
> # 2. Impor / sinkronisasi seluruh peserta dalam 1 proyek ke SPSP
> php artisan project:import <kode_proyek>
> 
> # 3. Impor single peserta spesifik dari proyek
> php artisan project:import <kode_proyek> --username=<username_peserta>
> 
> # 4. Menjalankan automated test suite integrasi LSP
> php artisan test --compact --filter=Lsp
> ```

---

## 🎯 Prinsip Utama Integrasi

1. **SRP Multi-Service Architecture**: Memisahkan kalkulasi norma psikometri ([LspNormEngineService.php](file:///c:/laragon/www/spsp_new/app/Services/Lsp/LspNormEngineService.php)), transformasi data ([LspDataTransformerService.php](file:///c:/laragon/www/spsp_new/app/Services/Lsp/LspDataTransformerService.php)), dan impor native ([LspDataImporterService.php](file:///c:/laragon/www/spsp_new/app/Services/Lsp/LspDataImporterService.php)).
2. **Dynamic Project & User Scoping**: Setiap query database tersambung dengan pengisolasian `kode_proyek` dan `username` tanpa hardcoding instansi.
3. **Fault-Tolerant & Error Isolation**: Impor dilakukan dengan transaksi 100-chunk. Jika ditemukan data korup, sistem melakukan *rollback chunk* dan mengisolasinya ke per-peserta sehingga peserta sehat lainnya tetap berhasil terimpor.
4. **Data Immutability**: Menjaga data hasil rating individual dan tes peserta bersifat historis (immutable) sesuai kondisi asesmen.
