# Section 07 — Layer 2: Potensi

* **Nama Visual**: Evaluasi Potensi Psikologis
* **Kode Section**: `potential`
* **Komponen File**: [IndexRadarSection.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/IndexRadarSection.php) & [index-radar-section.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/index-radar-section.blade.php)
* **Status Dynamic**: 📋 **PLANNED** (Data SPSP 🟢 Reuse)

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Assessment Science)

1. **Psychological Capacity & Latent Capability**:
   * Mengukur aspek-aspek psikologis laten yang relatif stabil (misal: Daya Pikir, Sikap Kerja, Adaptabilitas, Kestabilan Emosi) yang memprediksi kapasitas kandidat untuk berkembang di masa depan (*growth ceiling*).
2. **Predictive Performance Foundation**:
   * Potensi psikologis menjadi fondasi utama dalam pembentukan kompetensi; kandidat dengan potensi tinggi lebih cepat menyerap pelatihan baru (*high learnability*).

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\AspectAssessment`, `App\Models\CategoryType` (Code: `potensi`).
* **Query Service**: `IndividualAssessmentService::getAspectAssessments($participantId, $potensiCategoryId)`.
* **Visualisasi UI**: Radar Chart per aspek potensi & tabel breakdown skor individual vs standar.
