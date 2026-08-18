# Section 05 — Layer 1: Kompetensi

* **Nama Visual**: Evaluasi Kompetensi Manajerial & Teknis
* **Kode Section**: `competency`
* **Komponen File**: [ScoreListSection.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/ScoreListSection.php) & [score-list-section.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/score-list-section.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB Sync)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Assessment Science)

1. **Behavioral Competency Framework (PermenPANRB & Standar BKN)**:
   * Mengukur tingkat kompetensi perilaku teramati (*observable and measurable behaviors*) yang ditunjukkan kandidat melalui metode simulasi *Assessment Center* (misal: *In-Basket Exercise, Group Discussion, Behavioral Event Interview / BEI, Case Analysis*).
   * Aspek-aspek mengacu pada standar kompetensi manajerial & sosial kultural nasional:
     * **Integritas**: Konsistensi ucapan dan tindakan selaras etika dan hukum.
     * **Kerjasama**: Kemampuan membangun hubungan sinergis dan mengelola dinamika tim.
     * **Komunikasi**: Kejelasan artikulasi, persuasi, dan kemampuan mendengar aktif.
     * **Orientasi pada Hasil**: Dorongan menyelesaikan target kerja dengan mutu tinggi dan gigih mengatasi hambatan.
     * **Pelayanan Publik**: Komitmen melayani pemangku kepentingan internal dan eksternal secara prima.
     * **Pengembangan Diri & Orang Lain**: Kaderisasi dan peningkatan kapabilitas bawahan.
     * **Mengelola Perubahan**: Kelincahan memimpin transisi proses bisnis dan budaya.
     * **Pengambilan Keputusan**: Keberanian dan ketepatan mitigasi risiko operasional.

2. **Job-Person Matching & Gap Analysis**:
   * Melakukan komparasi objektif antara skor aktual kandidat ($X_i$) terhadap standar minimal formasi jabatan ($S_i$):
     $$\text{Gap} = X_i - S_i$$
   * Klasifikasi 3-Status Evaluasi:
     * 🟢 **Di Atas Standar ($\text{Gap} > 0$)**: Memiliki keunggulan kapabilitas yang dapat dimanfaatkan sebagai mentor/role model.
     * ⚪ **Memenuhi Standar ($\text{Gap} = 0.00$)**: Siap menjalankan tugas pokok fungsi tanpa hambatan berarti.
     * 🔴 **Di Bawah Standar ($\text{Gap} < 0$)**: Area kritis yang wajib menjadi sasaran *Individual Development Plan (IDP)*.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\AspectAssessment`, `App\Models\CategoryType` (Code: `kompetensi`), `App\Models\Participant`.
* **Query Service**: `App\Services\IndividualAssessmentService::getAspectAssessments($participantId, $kompetensiCategoryId, 0)`.
* **Field DB yang Digunakan**: `aspect_name`, `aspect_code`, `standard_rating`, `individual_rating`, `gap_rating`, `conclusion_text`, `description`.
* **Tampilan Visual UI**: Progres bar horizontal berskala 1.00–5.00 dilengkapi garis penanda standar jabatan dengan indikator segitiga target (▲), badge gap delta dengan kode warna dinamis, kesimpulan evaluasi non-konfliktif, serta nilai komposit rata-rata kompetensi.
