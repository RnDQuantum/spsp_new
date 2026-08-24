# 📑 Human Capital Assessment (HCA) Report — Documentation Index

Selamat datang di indeks dokumentasi **Human Capital Assessment (HCA) Report** pada Sistem Pemetaan & Statistik Psikologi (SPSP).

HCA Report adalah laporan eksekutif berstandar *Executive Journal* yang mengintegrasikan data psikometri, kompetensi manajerial, profil perilaku, hingga indikator suksesi kepemimpinan kandidat ke dalam satu media visual yang interaktif dan *print-ready*.

---

> [!NOTE]
> **Catatan Kedinamisan & Penyesuaian Section**:
> Spesifikasi, susunan, maupun sumber data pada section-section HCA Report ini bersifat dinamis dan adaptif. Dalam perjalanannya (*development roadmap* & integrasi bertahap), section-section ini dapat mengalami perubahan, penyesuaian logika, penggabungan, atau penyempurnaan sesuai keputusan bisnis, kebutuhan HR institusi klien, dan masukan pengguna (*user feedback*).

---

## 📚 Struktur Dokumentasi HCA Report

Dokumentasi HCA Report dibagi menjadi 4 dokumen utama dan 1 folder spesifikasi per-section:

### 1. Dokumentasi Utama (Master Files)

* **[DYNAMIC_INTEGRATION_TRACKER.md](./DYNAMIC_INTEGRATION_TRACKER.md)**
  * Tracker kemajuan migrasi data dinamis per-halaman/section (9 Dynamic DB Sync + 14 Component Active / 100% Active UI).
  * Menghubungkan ringkasan status dengan file spesifikasi individual di folder `sections/`.

* **[DATA_MAPPING_SPEC.md](./DATA_MAPPING_SPEC.md)**
  * Spesifikasi pemetaan data backend SPSP vs kebutuhan HCA Report.
  * Pengelompokan status data: 🟢 **Reuse**, 🟡 **Partial**, dan 🔴 **New**.

* **[DESIGN_AND_UI_SPEC.md](./DESIGN_AND_UI_SPEC.md)**
  * Spesifikasi visual identity theme *"Executive Journal"*, token warna, tipografi (`Lora` & `Instrument Sans`), serta arsitektur komponen Livewire.

* **[NAVIGATION_OPTIMIZATION_SPEC.md](./NAVIGATION_OPTIMIZATION_SPEC.md)**
  * Arsitektur optimasi navigasi 0ms Instant SPA, Livewire Deep-Linking (`#[Url]`), memoisasi query `HcaDataService`, dan sinkronisasi Chart.js.

### 2. Spesifikasi Per-Halaman & Section ([docs/hca/sections/](./sections/))

Setiap section memiliki dokumentasi mandiri yang menjelaskan **Tujuan & Maksud Keilmuan (HR & Psychological Science)** serta **Sumber Data DB SPSP & Logic Calculation**:

| No | Section / Halaman | Dokumen Spesifikasi | Status UI / Dynamic |
| :-: | :--- | :--- | :-: |
| **00** | **Active Talent Selector & Sidebar** | [00_talent_selector.md](./sections/00_talent_selector.md) | ✅ **DONE (Dynamic)** |
| **01** | **Cover Page** | [01_cover_page.md](./sections/01_cover_page.md) | ✅ **DONE (Dynamic)** |
| **02** | **Ringkasan Eksekutif** | [02_executive_summary.md](./sections/02_executive_summary.md) | ✅ **DONE (Dynamic)** |
| **03** | **Identitas Peserta** | [03_participant_profile.md](./sections/03_participant_profile.md) | ✅ **DONE (Dynamic)** |
| **04** | **Human Capital Index (HCI)** | [04_human_capital_index.md](./sections/04_human_capital_index.md) | ✅ **DONE (Dynamic)** |
| **05** | **Evaluasi Kompetensi Manajerial** | [05_layer1_kompetensi.md](./sections/05_layer1_kompetensi.md) | ✅ **DONE (Dynamic)** |
| **06** | **Riwayat Karier & Rekam Jejak** | [06_riwayat_karier.md](./sections/06_riwayat_karier.md) | ✅ **DONE (Dynamic)** |
| **07** | **Evaluasi Potensi Psikologis** | [07_layer2_potensi.md](./sections/07_layer2_potensi.md) | ✅ **DONE (Dynamic)** |
| **08** | **IQ & Profil Kognitif** | [08_iq_kognitif.md](./sections/08_iq_kognitif.md) | ✅ **DONE (Dynamic)** |
| **09** | **Big Five Personality** | [09_big_five_personality.md](./sections/09_big_five_personality.md) | ✅ **DONE (Dynamic)** |
| **10** | **DISC Profile** | [10_disc_profile.md](./sections/10_disc_profile.md) | ✅ **DONE (Dynamic)** |
| **11** | **Learning Agility** | [11_learning_agility.md](./sections/11_learning_agility.md) | ✅ **DONE (Dynamic)** |
| **12** | **Leadership Potential** | [12_leadership_potential.md](./sections/12_leadership_potential.md) | ✅ **DONE (Dynamic)** |
| **13** | **Emotional Intelligence (EQ)** | [13_emotional_intelligence.md](./sections/13_emotional_intelligence.md) | ✅ **DONE (Dynamic)** |
| **14** | **Values & Integrity** | [14_values_integrity.md](./sections/14_values_integrity.md) | ✅ **DONE (Dynamic)** |
| **15** | **Dashboard Kinerja & KPI** | [15_performance_dashboard.md](./sections/15_performance_dashboard.md) | ✅ **DONE (Dynamic)** |
| **16** | **Matriks Talenta (9-Box Grid)** | [16_talent_9box_matrix.md](./sections/16_talent_9box_matrix.md) | ✅ **DONE (Dynamic)** |
| **17** | **Kesiapan Suksesi Kepemimpinan** | [17_succession_readiness.md](./sections/17_succession_readiness.md) | ✅ **DONE (Dynamic)** |
| **18** | **Profil Personal (Pelengkap)** | [18_profil_personal.md](./sections/18_profil_personal.md) | ✅ **DONE (Dynamic)** |
| **19** | **Kesehatan Jiwa** | [19_kesehatan_jiwa.md](./sections/19_kesehatan_jiwa.md) | ✅ **DONE (Dynamic)** |
| **20** | **Kekuatan Psikologis** | [20_kekuatan_psikologis.md](./sections/20_kekuatan_psikologis.md) | ✅ **DONE (Dynamic)** |
| **21** | **Indikator Risiko** | [21_indikator_risiko.md](./sections/21_indikator_risiko.md) | ✅ **DONE (Dynamic)** |
| **22** | **Rekomendasi Pengembangan** | [22_rekomendasi_pengembangan.md](./sections/22_rekomendasi_pengembangan.md) | ✅ **DONE (Dynamic)** |
| **23** | **Rekomendasi Peran Berikutnya** | [23_rekomendasi_peran_berikutnya.md](./sections/23_rekomendasi_peran_berikutnya.md) | ✅ **DONE (Dynamic)** |
| **24** | **Laporan Hasil Alat Tes (Technical Appendix)** | [24_laporan_alat_tes.md](./sections/24_laporan_alat_tes.md) | ✅ **DONE (Dynamic)** |

---

## 🎯 Prinsip Pengerjaan

1. **User-Guided Page-by-Page Integration**: Integrasi dilakukan bertahap 1 per 1 section untuk menjamin keakuratan data dan performa query.
2. **Standard HCA Layout (`hca-layout.blade.php`)**: Seluruh komponen dirender dalam kontainer full-bleed tanpa tertekan oleh sidebar utama SPSP.
3. **Data Immutability**: Hasil rating individual peserta bersifat historis (immutable), sedangkan standar kompetensi/potensi bersifat dinamis sesuai konfigurasi institusi.
