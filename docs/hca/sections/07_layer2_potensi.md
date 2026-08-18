# Section 07 — Layer 2: Potensi

* **Nama Visual**: Evaluasi Potensi Psikologis
* **Kode Section**: `potential`
* **Komponen File**: [IndexRadarSection.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/IndexRadarSection.php) & [index-radar-section.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/index-radar-section.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Assessment Science)

1. **Psychological Capacity & Latent Capability**:
   * Mengukur aspek-aspek psikologis laten yang relatif stabil (misal: Daya Pikir / Intelektual, Sikap & Cara Kerja, Potensi Kerja, Sosualitas, Kepribadian) yang memprediksi kapasitas kandidat untuk berkembang di masa depan (*growth ceiling*).
2. **Predictive Performance Foundation**:
   * Potensi psikologis menjadi fondasi utama dalam pembentukan kompetensi; kandidat dengan potensi tinggi lebih cepat menyerap pelatihan baru (*high learnability*).

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\AspectAssessment`, `App\Models\CategoryType` (Code: `potensi`), `App\Models\Participant`.
* **Query Service**: `App\Services\IndividualAssessmentService::getAspectAssessments($participantId, $potensiCategoryId, 0)`.
* **Visualisasi UI**: Radar Chart dinamis per aspek potensi psikologis, ring circular score gauge, dan tabel perbandingan rating aktual vs standar minimal & toleransi.
