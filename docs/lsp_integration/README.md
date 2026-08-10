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
| **[INTEGRATION_GUIDE.md](./INTEGRATION_GUIDE.md)** | **Panduan Operasional**: CLI execution (`lsp:test-report` & `lsp:import`), pengujian otomatis (`php artisan test`), dan lokasi norma JSON. | ✅ **DOCUMENTED** |
| **[FALLBACK_AND_SAFETY_MECHANISMS.md](./FALLBACK_AND_SAFETY_MECHANISMS.md)** | **Spesifikasi Keamanan**: Matriks *fallback* data mentah, pembatasan *error*, dan mekanisme isolasi transaksi impor 100-chunk. | ✅ **DOCUMENTED** |
| **[SERVICES_ARCHITECTURE.md](./SERVICES_ARCHITECTURE.md)** | **Arsitektur Service Codebase**: Penjelasan multi-service SRP (`LspNormEngineService`, `LspDataTransformerService`, `LspDataImporterService`). | ✅ **DOCUMENTED** |

---

## 🏗️ Ringkasan Arsitektur Service Pipeline

Integrasi LSP menerapkan arsitektur *Single Responsibility Principle* (SRP) yang memisahkan tugas pengolahan norma, ekstraksi data legacy, dan sinkronisasi database SPSP:

```mermaid
flowchart TD
    subgraph LSP_SOURCE [Koneksi DB LSP / DB_LSP_LOCAL]
        DB1[(peserta_produksi)]
        DB2[(ujian_peserta_produksi)]
        DB3[(hasil_aspek_yang_digali)]
        DB4[(rekapmmpi_p3kkjg)]
    end

    subgraph LSP_SERVICES [Layer Pipeline Integrasi LSP]
        S1[LspNormEngineService]
        S2[LspDataTransformerService]
        S3[LspDataImporterService]
        S4[5 Norm JSONs: ist, kostik, personality, cfit3a, cfit3b]
    end

    subgraph SPSP_NATIVE [Layer System & Database Native SPSP]
        DB_SPSP[(Database Native SPSP)]
        S5[TestReportService]
        S6[IndividualAssessmentService]
    end

    DB2 --> S1
    S4 --> S1
    DB1 & DB3 & DB4 --> S2
    S1 -->|Standard Scores & Ratings| S2
    S2 -->|Payload DTO| S3
    S3 -->|Bulk Upsert| DB_SPSP

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
> # 2. Impor / sinkronisasi seluruh peserta dalam 1 proyek LSP ke SPSP
> php artisan lsp:import <kode_proyek>
> 
> # 3. Impor single peserta spesifik dari proyek LSP
> php artisan lsp:import <kode_proyek> --username=<username_peserta>
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
