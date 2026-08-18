# Section 08 — IQ & Profil Kognitif

* **Nama Visual**: Breakdown Kapasitas Kognitif & Inteligensi
* **Kode Section**: `cognitive`
* **Komponen File**: [ScoreListSection.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/ScoreListSection.php) & [score-list-section.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/score-list-section.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Psychological Science)

1. **Problem Solving & Complex Decision Making**:
   * Inteligensi kognitif (IQ) adalah prediktor tunggal terkuat bagi pemrosesan informasi kompleks, pemikiran strategis, dan kecepatan pemecahan masalah di lingkungan bisnis yang dinamis.
2. **Cognitive Domain Breakdown**:
   * Memecah kapasitas intelektual ke dalam faktor-faktor utama: *Kecerdasan Umum, Daya Tangkap, Daya Analisa, Logika Berpikir, Daya Ingat, Daya Konsentrasi, Daya Abstraksi, Kemampuan Numerik, dan Kreativitas*.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\SubAspectAssessment`, `App\Models\AspectAssessment`, `App\Models\TestResult`, `App\Models\Participant`.
* **Query Service**: Ekstraksi sub-aspek di bawah aspek *Intelektual / Daya Pikir* (`sub_aspect_assessments`) dan skor instrumen kecerdasan (CFIT / IST) dari tabel `test_results`.
* **Tampilan UI**: Baris progres sub-aspek kognitif, indikator standar dan deviasi, estimasi skor IQ komposit, dan rata-rata rating kognitif.
