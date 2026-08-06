# Section 22 — Rekomendasi Pengembangan

* **Nama Visual**: Rekomendasi Program Pelatihan & Pengembangan
* **Kode Section**: `development_rec`
* **Komponen File**: [DevelopmentRecommendation.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/DevelopmentRecommendation.php) & [development-recommendation.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/development-recommendation.blade.php)
* **Status Dynamic**: 📋 **PLANNED** (Data SPSP 🟡 Partial)

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Assessment Science)

1. **Targeted Individual Development Plan (IDP)**:
   * Merumuskan rekomendasi intervensi pengembangan (pelatihan, *coaching*, *mentoring*, atau penugasan khusus) yang disesuaikan secara spesifik dengan aspek-aspek yang memiliki *gap* terbesar terhadap standar.
2. **70-20-10 Learning Framework Alignment**:
   * Menyelaraskan rekomendasi pelatihan berbasis pengayaan pengalaman kerja (70%), mentoring/coaching (20%), dan pelatihan formal (10%).

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Service Utama**: `App\Services\TrainingRecommendationService`.
* **Logic Query**: Menjaring aspek-aspek kompetensi/potensi kandidat yang memiliki skor gap negatif, lalu memetakan ke katalog program pelatihan SPSP yang sesuai.
