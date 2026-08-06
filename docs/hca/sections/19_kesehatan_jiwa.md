# Section 19 — Kesehatan Jiwa (Mental Health)

* **Nama Visual**: Evaluasi Kesehatan Jiwa & Penyesuaian Diri
* **Kode Section**: `mental_health`
* **Komponen File**: [MentalHealthSection.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/MentalHealthSection.php) & [mental-health-section.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/mental-health-section.blade.php)
* **Status Dynamic**: 📋 **PLANNED** (Data SPSP 🟡 Partial)

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Assessment Science)

1. **Psychological Wellbeing & Adaptability**:
   * Evaluasi kesehatan mental, penyesuaian diri, dan stabilitas emosi kandidat dalam menghadapi tekanan lingkungan kerja (*workplace mental fitness*).
2. **Clinical Screening & Organizational Safety**:
   * Memastikan kandidat tidak memiliki gangguan kecemasan berat, distres emosional, atau masalah kepribadian yang dapat mengganggu keselamatan kerja dan dinamika tim.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\PsychologicalTest` (`$psychologicalTest`).
* **Field DB yang Dipakai**: `validitas`, `internal`, `interpersonal`, `kap_kerja`, `klinik`, `kesimpulan`, `nilai_pq`, `tingkat_stres`.
