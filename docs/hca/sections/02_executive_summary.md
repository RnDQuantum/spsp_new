# Section 02 — Ringkasan Eksekutif (Executive Summary)

* **Nama Visual**: Ringkasan Eksekutif & Snapshot Evaluasi Utama
* **Kode Section**: `exec_summary`
* **Komponen File**: [ExecutiveSummary.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/ExecutiveSummary.php) & [executive-summary.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/executive-summary.blade.php)
* **Status Dynamic**: ✅ **DONE**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Assessment Science)

1. **Executive Decision Support (Macro View)**:
   * Dirancang khusus untuk C-Level / Direksi / Tim Pansel agar dapat memahami tingkat kesiapan (*readiness*) dan potensi kandidat secara serentak hanya dalam 3 detik tanpa harus menelaah puluhan indikator aspek psikologi yang mendalam.
2. **5 Pillars Multi-Dimensional Assessment**:
   * Mengintegrasikan 5 dimensi evaluasi SDM modern (*Kompetensi Manajerial, Potensi Psikologis, Kinerja Aktual, Potensi Kepemimpinan, dan Integritas/Nilai Kerja*) menjadi 1 angka komposit **Talent Index Score** (Skala 1.00 – 5.00) yang terstandar untuk analisis talent pool.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\Participant`, `App\Models\FinalAssessment`, `App\Models\CategoryAssessment`, `App\Models\AspectAssessment`.
* **Logic Calculations**:
  1. **Status Kesiapan**: Diambil dari `final_assessments.conclusion_text` ("SANGAT DISARANKAN", "DISARANKAN", "DISARANKAN DENGAN CATATAN", "TIDAK DISARANKAN").
  2. **Pilar Kompetensi**: Rata-rata rating aspek pada kategori `kompetensi` dari `IndividualAssessmentService`.
  3. **Pilar Potensi**: Rata-rata rating aspek pada kategori `potensi` dari `IndividualAssessmentService`.
  4. **Pilar Kinerja**: Konversi `final_assessments.achievement_percentage` ke skala 5.00 (`achievement_percentage / 100 * 5`).
  5. **Pilar Kepemimpinan & Integritas**: Rating individual aspek *Leadership* / *Integrity* jika ada pada template posisi, atau fallback rata-rata aspek manajerial/sikap kerja.
  6. **Talent Index Score**: Rata-rata terbobot dari 5 Pilar (skala 1.00 – 5.00).
  7. **Talent Category**: `>= 4.5` (Top Talent), `>= 4.0` (Strong Talent), `>= 3.5` (Promising Talent), `>= 3.0` (Developing Talent), `< 3.0` (Needs Focus).
