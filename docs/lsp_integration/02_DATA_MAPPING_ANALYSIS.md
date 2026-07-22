# Pemetaan Data CI3 Legacy vs Struktur Database SPSP

- **Dokumen Referensi**: `docs/DATABASE_STRUCTURE.md` & `D:\bima\RND\SPSP\legacy\report_individu_p3k_kjg_2025.php`
- **Tanggal**: 22 Juli 2026

---

## 1. Analisis Kompatibilitas Data

Berdasarkan perbandingan antara data yang diproses pada view legacy CI3 (`report_individu_p3k_kjg_2025.php`) dan skema database SPSP (`docs/DATABASE_STRUCTURE.md`), data dapat dikelompokkan menjadi dua kategori: **Data yang Sudah Tercover** dan **Data Tambahan yang Membutuhkan Pemetaan Khusus**.

---

## 2. Tabel Kompatibilitas Data (Sudah Tercover di SPSP)

| Data di View CI3 Legacy | Tabel & Kolom Target SPSP | Keterangan / Status |
|:---|:---|:---|
| Identitas Peserta (`no_test`, `no_kjg`, `nama_lengkap`, `gender`, `tanggal_pelaksanaan`) | `participants` (`test_number`, `skb_number`, `name`, `gender`, `assessment_date`) | **Sudah Tercover** (`no_kjg` $\rightarrow$ `skb_number`) |
| Gelombang & Formasi Jabatan | `batches` (`name`, `location`) & `position_formations` (`name`) | **Sudah Tercover** |
| Sub-Aspek Potensi (Rating 1-5, Standard, Label) | `sub_aspect_assessments` (`individual_rating`, `standard_rating`, `rating_label`) | **Sudah Tercover** |
| Aspek Potensi & Kompetensi (Rating, Skor, Gap, Persentase, Kesimpulan) | `aspect_assessments` (`individual_rating`, `standard_score`, `individual_score`, `gap_rating`, `conclusion_text`) | **Sudah Tercover** |
| Total Skor Kategori (Potensi & Kompetensi) | `category_assessments` (`total_individual_score`, `total_standard_score`, `conclusion_code`) | **Sudah Tercover** |
| Skor Final & Rekomendasi Akhir Psikotes (Achievement %, Conclusion Code) | `final_assessments` (`total_individual_score`, `achievement_percentage`, `conclusion_code`, `conclusion_text`) | **Sudah Tercover** |
| Data Hasil MMPI / Tes Kejiwaan (9 domain: Validitas, Internal, Interpersonal, Kapasitas Kerja, Klinik, Kesimpulan, Psikogram, PQ, Stres) | `psychological_tests` (`validitas`, `internal`, `interpersonal`, `kap_kerja`, `klinik`, `kesimpulan`, `psikogram`, `nilai_pq`, `tingkat_stres`) | **Sangat Sangat Cocok** (Mirroring 1:1 dari `rekapmmpi`) |
| Narasi Interpretasi Potensi & Kompetensi | `interpretations` (`interpretation_text`) | **Sudah Tercover** |
| Backup Respons API Mentah Ujian Online | `test_results` (`test_code`, `summary_data`, `raw_response`) | **Sudah Tercover** |

---

## 3. Data Tambahan CI3 Legacy yang Perlu Penanganan Khusus di SPSP

Berikut adalah data penting dari view legacy yang dikonsumsi oleh **Laporan Individu**, namun belum memiliki kolom dedicated di skema bawaan SPSP atau membutuhkan mekanisme penampungan/ekstensi:

### A. Data Kualitatif Wawancara Asesor (Technical Advisor)
View CI3 mengambil data deskriptif wawancara dari tabel `hasil_aspek_kelebihan` dan `hasil_rekomendasi`:
1. **Kekuatan / Kelebihan Peserta** (`aspek_kelebihan`)
2. **Kelemahan / Kekurangan Peserta** (`aspek_kelemahan`)
3. **Catatan Khusus / Catatan Wajib** (`catatan_wajib`)
4. **Saran Pengembangan** (`saran_pengembangan`)
5. **Rekomendasi Wawancara Asesor** (`rekomendasi` MS / MMS / TMS)
6. **Aspek Tambahan Wawancara** (`aspek_tambahan` + `hasil_aspek_tambahan`: nilai 1–5 & keterangan)

> **Opsi Solusi di SPSP**:
> Data ini dapat disimpan pada tabel `interpretations` (menggunakan tipe/kategori polimorfik) atau ditambahkan sebagai JSON payload / kolom tambahan di `final_assessments`.

---

### B. Detail Asesor Penanggung Jawab (Technical Advisor / TA)
View CI3 mengambil data penandatangan laporan dari `users_personil` & `penugasan`:
- `nama_lengkap_ta` (Nama Lengkap Asesor + Gelar Depan/Belakang)
- `jabatan_ta` (Jabatan Asesor Penanggung Jawab)

> **Opsi Solusi di SPSP**:
> Diperlukan untuk footer legalitas laporan individu di SPSP. Dapat ditarik langsung dari `DB_LSP_LOCAL` saat ekspor/load laporan atau disimpan di metadata `final_assessments`.

---

### C. Legalisasi Dokumen & Validasi TTD Digital
View CI3 menggenerate data legalitas dari `validasi_ttd_report`:
- `no_dokumen` (Nomor Dokumen Resmi, misal: `001-Batch1/LI-QHRM-KEJAKSAAN-01/IX/2025`)
- `kode_validasi` (Token unik TTD elektronik)
- `qr_code` (Path / URL verifikasi QR Code)

> **Opsi Solusi di SPSP**:
> Penting jika SPSP akan menghasilkan output cetak PDF yang identik secara hukum dengan versi legacy.

---

### D. Skor Mentah & Nilai IQ (IST / CFIT)
View CI3 menghitung IQ peserta (misal: IQ = 110) dari subtest IST untuk menentukan prasyarat kelulusan ($IQ \ge 90$).
- Di SPSP, skor IQ berada di JSON `test_results.summary_data`.
- **Kebutuhan**: Engine kalkulasi SPSP harus membaca `iq` dari `test_results` atau `DB_LSP_LOCAL` untuk menegakkan aturan kelulusan psikotes.

---

### E. Skor Persentase Sub-Sistem Header Report
View CI3 memuat 3 nilai persentase pada Box Header Laporan Individu:
1. **Hasil Psikotest %**: $\left(\frac{\text{Skor Individu Potensi}}{\text{Skor Standar Potensi}} \times 100\right) - 30$
2. **Hasil Wawancara %**: $\left(\frac{\text{Skor Individu Kompetensi}}{\text{Skor Standar Kompetensi}} \times 100\right) - 20$
3. **Hasil Tes Kejiwaan (MMPI)**: Skor numerik $90 / 77.5 / 65 / 52.5 / 40$

---

## 4. Kesimpulan & Rekomendasi Integrasi

Sebagian besar data utama SPSP (Potensi, Kompetensi, Sub-aspek, Rekap Skor, MMPI) **sudah sangat kompatibel (90% match)** dengan struktur tabel yang didefinisikan pada `docs/DATABASE_STRUCTURE.md`.

Untuk 10% sisanya (Data Kualitatif Wawancara, Detail Asesor, Legalisasi TTD QR, Skor Header %), SPSP dapat menampungnya melalui **Service Integrasi (LspDataImporter / LspIndividualReportService)** yang memetakan data mentah dari `DB_LSP_LOCAL` ke DTO / Model SPSP secara transparan.
