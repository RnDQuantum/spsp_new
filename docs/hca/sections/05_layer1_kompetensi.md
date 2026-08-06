# Section 05 — Layer 1: Kompetensi

* **Nama Visual**: Evaluasi Kompetensi Manajerial & Teknis
* **Kode Section**: `competency`
* **Komponen File**: [ScoreListSection.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/ScoreListSection.php) & [score-list-section.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/score-list-section.blade.php)
* **Status Dynamic**: 📋 **PLANNED** (Data SPSP 🟢 Reuse)

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Assessment Science)

1. **Managerial & Functional Competency Assessment**:
   * Mengukur tingkat kompetensi perilaku yang dapat diamati (*observable behaviors*) sesuai PermenPANRB / standar kompetensi institusi (misal: Perencanaan, Pelayanan Publik, Komunikasi, Pengambilan Keputusan).
2. **Gap Analysis vs Job Standard**:
   * Membandingkan rating aktual kandidat terhadap standar minimal jabatan (*Job Person Matching*) untuk mengidentifikasi apakah kompetensi memenuhi (*meet standard*), melebihi (*above standard*), atau di bawah standar (*gap*).

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\AspectAssessment`, `App\Models\CategoryType` (Code: `kompetensi`).
* **Query Service**: `IndividualAssessmentService::getAspectAssessments($participantId, $kompetensiCategoryId)`.
* **Field DB yang Dipakai**: `aspect_name`, `aspect_code`, `standard_rating`, `individual_rating`, `gap_rating`, `conclusion_text`.
