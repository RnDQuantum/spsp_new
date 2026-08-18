# Section 09 — Big Five Personality (OCEAN)

* **Nama Visual**: Profil Kepribadian Big Five Model OCEAN
* **Kode Section**: `big_five`
* **Komponen File**: [ScoreListSection.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/ScoreListSection.php) & [score-list-section.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/score-list-section.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB Sync)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Psychological Science)

1. **Five-Factor Model (FFM) Trait Profiling (Costa & McCrae)**:
   * Mengukur kepribadian berbasis model taksonomi kepribadian yang paling valid dan teruji secara empiris di dunia psikologi industri:
     * **Openness to Experience (O)**: Keterbukaan terhadap ide inovatif, wawasan konseptual, imajinasi kreatif, dan keragaman perspektif.
     * **Conscientiousness (C)**: Derajat kehati-hatian, keteraturan kerja, kedisiplinan diri, orientasi pencapaian tugas (*achievement striving*), dan keandalan eksekusi.
     * **Extraversion (E)**: Tingkat kenyamanan dalam interaksi sosial, energi asertif, antusiasme, dan inisiatif komunikasi terbuka.
     * **Agreeableness (A)**: Kecenderungan kooperatif, orientasi membangun kepercayaan, empati interpersonal, dan pemeliharaan iklim tim yang harmonis.
     * **Emotional Stability / Low Neuroticism (N)**: Kapasitas mengelola kecemasan, ketenangan di bawah tekanan tinggi, dan daya lentur psikologis (*psychological resilience*).

2. **Workplace Trait-Performance Prediction**:
   * *Conscientiousness* adalah prediktor konsisten terhadap produktivitas dan kualitas kerja; *Openness* memprediksi adaptasi transformasi & inovasi; *Extraversion* memprediksi pengaruh kepemimpinan publik; *Agreeableness* memprediksi kekompakan lintas fungsi; *Emotional Stability* memprediksi ketahanan saat krisis organisasi.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\TestResult` (`test_code: B.2` 16PF / Sixteen Personality Factor Questionnaire), `App\Models\Participant`.
* **Formula Pemetaan Psikometri (16PF $\rightarrow$ OCEAN Skala 1.00 – 5.00)**:
  Skor Sten (1–10) dari 16 faktor Cattell dikonversikan secara matematis:
  1. $\text{Openness} = \text{round}\left(\frac{(M + Q1 + I) / 3}{2}, 2\right)$
  2. $\text{Conscientiousness} = \text{round}\left(\frac{(G + Q3) / 2}{2}, 2\right)$
  3. $\text{Extraversion} = \text{round}\left(\frac{(A + F + H) / 3}{2}, 2\right)$
  4. $\text{Agreeableness} = \text{round}\left(\frac{(A + (11 - L) + (11 - E)) / 3}{2}, 2\right)$
  5. $\text{Emotional Stability} = \text{round}\left(\frac{(C + (11 - O) + (11 - Q4)) / 3}{2}, 2\right)$
  *Seluruh nilai dibatasi dalam rentang valid $[1.00, 5.00]$.*
* **Tampilan UI**: Baris progres 5 dimensi OCEAN, indikator standar minimum jabatan, nilai gap deviasi, label kesimpulan perilaku terarah, dan indeks rata-rata kestabilan kepribadian.
