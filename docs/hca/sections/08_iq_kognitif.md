# Section 08 — IQ & Profil Kognitif

* **Nama Visual**: Breakdown Kapasitas Kognitif & Inteligensi
* **Kode Section**: `cognitive`
* **Komponen File**: [ScoreListSection.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/ScoreListSection.php) & [score-list-section.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/score-list-section.blade.php)
* **Status Dynamic**: 📋 **PLANNED** (Data SPSP 🟢 Reuse)

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Psychological Science)

1. **Problem Solving & Complex Decision Making**:
   * Inteligensi kognitif (IQ) adalah prediktor tunggal terkuat bagi pemrosesan informasi kompleks, pemikiran strategis, dan kecepatan pemecahan masalah di lingkungan bisnis yang dinamis.
2. **Cognitive Domain Breakdown**:
   * Memecah kapasitas intelektual ke dalam 5 faktor utama: *Verbal Reasoning* (kelancaran kosa kata/logika bahasa), *Numerical Reasoning* (kemampuan angka/analisis data), *Analytical Thinking* (logika abstrak/sistematis), *Spatial Reasoning* (pemahaman ruang/konsep visual), dan *Abstract Logic*.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\SubAspectAssessment`, `App\Models\AspectAssessment` (Aspek: "Daya Pikir").
* **Query Service**: Ekstraksi dari `sub_aspect_assessments` di bawah aspek Daya Pikir pada `IndividualAssessmentService`.
