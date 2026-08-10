# 🗺️ Pemetaan Data CI3 Legacy vs Struktur Database Native SPSP

[← Kembali ke Indeks Dokumentasi Integrasi LSP](./README.md)

- **Dokumen Referensi**: [docs/DATABASE_STRUCTURE.md](../DATABASE_STRUCTURE.md) & legacy view `report_individu_p3k_kjg_2025.php`
- **Tanggal Pembaruan**: 29 Juli 2026

---

> [!NOTE]
> **Ikhtisar Pemetaan Data**:
> Dokumen ini menganalisis tingkat kompatibilitas antara struktur data mentah yang diproses pada aplikasi legacy CodeIgniter 3 (LSP) dengan skema database native SPSP. Sebagian besar data utama SPSP (Potensi, Kompetensi, Sub-aspek, Rekap Skor, MMPI) **sudah 90% kompatibel (Direct Match)** dengan skema native SPSP, sedangkan 10% sisanya ditangani via DTO payload & metadata.

---

## 🟢 1. Tabel Kompatibilitas Data (90% Direct Match)

Data berikut dari aplikasi legacy LSP telah dipetakan 1:1 ke tabel dan kolom native SPSP:

| Data di View CI3 Legacy | Tabel & Kolom Target SPSP | Status Kompatibilitas | Keterangan Pemetaan |
| :--- | :--- | :-: | :--- |
| Identitas Peserta (`no_test`, `no_kjg`, `nama_lengkap`, `gender`, `tanggal_pelaksanaan`) | `participants` (`test_number`, `skb_number`, `name`, `gender`, `assessment_date`) | 🟢 **Reuse / Direct Match** | `no_kjg` dipetakan ke `skb_number`. |
| Gelombang & Formasi Jabatan | `batches` (`name`, `location`) & `position_formations` (`name`) | 🟢 **Reuse / Direct Match** | Dibuat otomatis jika belum ada. |
| Sub-Aspek Potensi (Rating 1-5, Standard, Label) | `sub_aspect_assessments` (`individual_rating`, `standard_rating`, `rating_label`) | 🟢 **Reuse / Direct Match** | Menyimpan rating atribut 1–5. |
| Aspek Potensi & Kompetensi (Rating, Skor, Gap, Persentase, Kesimpulan) | `aspect_assessments` (`individual_rating`, `standard_score`, `individual_score`, `gap_rating`, `conclusion_text`) | 🟢 **Reuse / Direct Match** | Rekap nilai aspek potensi & kompetensi. |
| Total Skor Kategori (Potensi & Kompetensi) | `category_assessments` (`total_individual_score`, `total_standard_score`, `conclusion_code`) | 🟢 **Reuse / Direct Match** | Rekap agregat 40% Potensi & 60% Kompetensi. |
| Skor Final & Rekomendasi Akhir Psikotes (Achievement %, Conclusion Code) | `final_assessments` (`total_individual_score`, `achievement_percentage`, `conclusion_code`, `conclusion_text`) | 🟢 **Reuse / Direct Match** | Kesimpulan akhir psikotes & wawancara. |
| Data Hasil MMPI / Tes Kejiwaan (9 domain: Validitas, Internal, Interpersonal, Kapasitas Kerja, Klinik, Kesimpulan, Psikogram, PQ, Stres) | `psychological_tests` (`validitas`, `internal`, `interpersonal`, `kap_kerja`, `klinik`, `kesimpulan`, `psikogram`, `nilai_pq`, `tingkat_stres`) | 🟢 **Reuse / Direct Match** | Mirroring 1:1 dari `rekapmmpi_p3kkjg`. |
| Narasi Interpretasi Potensi & Kompetensi | `interpretations` (`interpretation_text`) | 🟢 **Reuse / Direct Match** | Teks kamus interpretasi otomatis. |
| Backup Respons Mentah Ujian Online | `test_results` (`test_code`, `summary_data`, `raw_response`) | 🟢 **Reuse / Direct Match** | Backup JSON skor mentah alat tes. |

---

## 🟡 2. Data Tambahan Legacy (10% Handling Khusus)

Berikut adalah data penting dari view legacy yang dikonsumsi oleh **Laporan Individu**, namun membutuhkan penampungan DTO payload / metadata pada skema native SPSP:

### A. Data Kualitatif Wawancara Asesor (Technical Advisor)
View CI3 mengambil data deskriptif wawancara dari tabel `hasil_aspek_kelebihan` dan `hasil_rekomendasi`:
1. **Kekuatan / Kelebihan Peserta** (`aspek_kelebihan`)
2. **Kelemahan / Kekurangan Peserta** (`aspek_kelemahan`)
3. **Catatan Khusus / Catatan Wajib** (`catatan_wajib`)
4. **Saran Pengembangan** (`saran_pengembangan`)
5. **Rekomendasi Wawancara Asesor** (`rekomendasi` MS / MMS / TMS)
6. **Aspek Tambahan Wawancara** (`aspek_tambahan` + `hasil_aspek_tambahan`: nilai 1–5 & keterangan)

> [!TIP]
> **Solusi Implementasi SPSP**:
> Data kualitatif ini disimpan pada tabel `interpretations` (menggunakan kategori polimorfik) serta ditampung pada payload JSON `final_assessments`.

---

### B. Detail Asesor Penanggung Jawab (Technical Advisor / TA)
View CI3 mengambil data penandatangan laporan dari `users_personil` & `penugasan`:
- `nama_lengkap_ta` (Nama Lengkap Asesor + Gelar Depan/Belakang)
- `jabatan_ta` (Jabatan Asesor Penanggung Jawab)

> [!TIP]
> **Solusi Implementasi SPSP**:
> Disimpan pada kolom metadata `final_assessments` untuk menyajikan footer legalitas penandatangan pada cetak Laporan Individu.

---

### C. Legalisasi Dokumen & Validasi TTD Digital
View CI3 menggenerate data legalitas dari `validasi_ttd_report`:
- `no_dokumen` (Nomor Dokumen Resmi, contoh: `001-Batch1/LI-QHRM-KEJAKSAAN-01/IX/2025`)
- `kode_validasi` (Token unik TTD elektronik)
- `qr_code` (Path / URL verifikasi QR Code)

> [!TIP]
> **Solusi Implementasi SPSP**:
> Ditampung pada metadata `final_assessments` untuk memastikan hasil cetak PDF SPSP identik secara hukum dengan versi legacy.

---

### D. Skor Mentah & Nilai IQ (IST / CFIT)
View CI3 menghitung IQ peserta dari subtest IST untuk menentukan prasyarat kelulusan ($IQ \ge 90$).
- Di SPSP, skor IQ berada di JSON `test_results.summary_data` dan diolah ulang oleh `LspNormEngineService`.

---

### E. Skor Persentase Sub-Sistem Header Report
View CI3 memuat 3 nilai persentase pada Box Header Laporan Individu:
1. **Hasil Psikotes %**: $\left(\frac{\text{Skor Individu Potensi}}{\text{Skor Standar Potensi}} \times 100\right) - 30$
2. **Hasil Wawancara %**: $\left(\frac{\text{Skor Individu Kompetensi}}{\text{Skor Standar Kompetensi}} \times 100\right) - 20$
3. **Hasil Tes Kejiwaan (MMPI)**: Skor numerik $90 / 77.5 / 65 / 52.5 / 40$

---

## 🎯 3. Kesimpulan Integrasi

Sebagian besar data utama SPSP (Potensi, Kompetensi, Sub-aspek, Rekap Skor, MMPI) **sudah sangat kompatibel (90% match)** dengan struktur tabel yang didefinisikan pada `docs/DATABASE_STRUCTURE.md`.

Untuk 10% sisanya (Data Kualitatif Wawancara, Detail Asesor, Legalisasi TTD QR, Skor Header %), SPSP menampungnya secara transparan via **[LspDataTransformerService.php](file:///c:/laragon/www/spsp_new/app/Services/Lsp/LspDataTransformerService.php)** dan **[LspDataImporterService.php](file:///c:/laragon/www/spsp_new/app/Services/Lsp/LspDataImporterService.php)**.
