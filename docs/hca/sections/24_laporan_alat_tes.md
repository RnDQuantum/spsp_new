# Section 24 — Laporan Hasil Alat Tes (Technical Appendix)

* **Nama Visual**: Lampiran Teknis Hasil Alat Tes Psikometri & Psikogram
* **Kode Section**: `test_instruments_appendix`
* **Komponen File**: [TestInstrumentsAppendix.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/TestInstrumentsAppendix.php) & [test-instruments-appendix.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/test-instruments-appendix.blade.php)
* **Status Dynamic**: 📋 **PLANNED (Data SPSP 🟢 Reuse via `test_results` & `TestReportService`)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Psychological Science)

1. **Evidence-Based HR & Psychometric Traceability (Level 1 Evidence Layer)**:
   * Menyediakan rincian data mentah (*raw scores*) dan skor terstandar (*standard scores, Sten, T-Score, persentase subtes*) dari setiap instrumen alat tes psikologi yang diikuti peserta.
   * Berfungsi sebagai basis pembuktian ilmiah dan bukti audit (*audit-proof evidence layer*) yang melandasi rating skala 1–5 pada Layer 1 (Kompetensi) dan Layer 2 (Potensi).

2. **Executive Journal Integrity (Separation of Strategic Narrative vs Technical Data)**:
   * Menempatkan detail psikometri teknis di bagian lampiran (*Technical Appendix*) di akhir dokumen agar tidak mengaburkan fokus pembaca C-Level/Direksi yang membaca narasi strategis makro di halaman depan laporan.

3. **Deep-Dive Diagnostic Support for Psychologists & Assessors**:
   * Memfasilitasi psikolog, asesor, dan komite talenta untuk melakukan validasi silang (*cross-validation*) terhadap dinamika kepribadian, ketahanan stres, dan kapasitas kognitif spesifik peserta.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\TestResult` (tabel `test_results`, kolom `summary_data` dan `interpretation_data`).
* **Service Pendukung**: `App\Services\TestReportService::getParticipantAllTestReports($participantId, $eventId)`.
* **Cakupan Instrumen Terisi (Sesuai `API_TEST_INSTRUMENTS_SCHEMA.md` & Norm Engine SPSP)**:
  1. **Kecerdasan & Kognitif**:
     * `A.1` & `A.2` — Typical CFIT 3A / 3B (Total IQ, Kategori, Skor Subtes 1–4, Interpretasi Hasil).
     * `A.5` — Typical IST (Total IQ, Kategori, 9 Subtes: SE, WA, AN, GE, RA, ZR, FA, WU, ME).
  2. **Kepribadian & Karakter**:
     * `B.1` — KOMPETENSI KARAKTER / PAPI Kostik (20 Skala Perilaku: G, L, I, T, V, S, R, D, C, E, N, A, P, X, B, O, Z, K, F, W beserta 7 aspek naratif kerja).
     * `B.2` — Typical 16PF (16 Faktor Sten Score, MDSten, WS, dan interpretasi 16 aspek kepribadian Cattell).
  3. **Sikap Kerja & Ketahanan**:
     * `D.2` — Typical Kraepelin (Kecepatan `panker`, Ketelitian `janker`, Ketahanan `tianker`, Kestabilan `hanker`).
  4. **Klinis & Kesehatan Jiwa**:
     * `E.1` & `E.2` — Typical MMPI 180 / 567 (13 Skala Klinis L, F, K, Hs, D, Hy, Pd, Mf, Pa, Pt, Sc, Ma, Si, Nilai PQ, dan Tingkat Stres).
  5. **Kecerdasan Emosional & Perilaku**:
     * `F.1` — Typical EQ (14 Dimensi Kecerdasan Emosional).
     * `G.1` — Typical Behavior Tendencies / Profil Perilaku.
  6. **Minat Jabatan**:
     * `H.1` — RMIB (Rothwell Miller Interest Blank - Ranking 12 Kategori Minat).

---

## 🖼️ Spesifikasi Tampilan & Interaktivitas UI

1. **Web Interactive View**:
   * Berada di **Klaster 7: Lampiran** pada sidebar TOC HCA.
   * Tampilan modular berbasis sub-tab/pills per kategori alat tes (*Kognitif, Kepribadian, Sikap Kerja, Klinis/MMPI, Minat*).
   * Visualisasi grafik Chart.js (Bar/Radar untuk PAPI Kostik, 16PF, Kraepelin) serta tabel rekapitulasi subtes.
2. **Print PDF (Flat Mode)**:
   * Seluruh instrumen yang terisi dirender secara linier per halaman lampiran (*Appendix A, Appendix B, dst.*) dengan page-break terstandar.
3. **Cross-Link Integrasi**:
   * Section 08 (IQ Kognitif), Section 09 (Kepribadian), Section 13 (EQ), dan Section 19 (Kesehatan Jiwa) memiliki tombol lompat cepat (*quick-jump button*) menuju Section 24 untuk mempermudah pembuktian data.
