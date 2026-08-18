# Section 09 — Big Five Personality (OCEAN)

* **Nama Visual**: Profil Kepribadian Big Five
* **Kode Section**: `big_five`
* **Komponen File**: [ScoreListSection.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/ScoreListSection.php) & [score-list-section.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/score-list-section.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Psychological Science)

1. **Five-Factor Model (FFM) Trait Profiling**:
   * Mengukur kepribadian kandidat berdasarkan 5 dimensi universal: *Openness to Experience* (kreativitas/keterbukaan ide), *Conscientiousness* (kedisiplinan/tanggung jawab), *Extraversion* (sifat sosial/energi), *Agreeableness* (keramahan/kerjasama), dan *Neuroticism / Emotional Stability* (kestabilan emosi).
2. **Workplace Behavior Prediction**:
   * *Conscientiousness* memprediksi disiplin & kinerja kerja; *Openness* memprediksi dorongan inovasi; *Extraversion* memprediksi kepemimpinan ekspansif; *Agreeableness* memprediksi kohesi tim; *Emotional Stability* memprediksi ketahanan stres di bawah tekanan.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\TestResult` (`test_code: B.2` 16PF / Kepribadian), `App\Models\Participant`.
* **Formula Psikometri**: Pemetaan skor sten faktor 16PF (A, C, E, F, G, H, I, L, M, O, Q1, Q3, Q4) ke dimensi model OCEAN dalam skala 1.00–5.00.
* **Tampilan UI**: Daftar baris progres skor OCEAN, standar formasi, gap, kesimpulan (*Memenuhi Standar*, *Perlu Penguatan*, dll.), dan rata-rata indeks kepribadian.
