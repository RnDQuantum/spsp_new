# Section 22 — Rekomendasi Pengembangan (IDP)

* **Nama Visual**: Rekomendasi Program Pelatihan & Rencana Pengembangan Individu
* **Kode Section**: `development_rec`
* **Komponen File**: [DevelopmentRecommendation.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/DevelopmentRecommendation.php) & [development-recommendation.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/development-recommendation.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB Sync via `AspectAssessment` & 70-20-10 IDP)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Talent Development)

1. **Targeted Individual Development Plan (IDP) & 70-20-10 Learning Framework (Center for Creative Leadership)**:
   * Merumuskan intervensi pengembangan yang terarah secara presisi untuk menutup kesenjangan (*closing competency gaps*) terbesar yang teridentifikasi dari Layer 1 dan Layer 2.
   * Menyelaraskan rekomendasi dengan prinsip 70-20-10:
     * **70% Pengalaman Kerja Nyata (*Experiential Learning*)**: Penugasan khusus (*stretch assignments*), rotasi proyek lintas unit, atau memimpin inisiatif percontohan.
     * **20% Pembelajaran Sosial (*Social Learning / Coaching & Mentoring*)**: Pendampingan intensif oleh pimpinan puncak/mentor eksekutif dan *peer coaching*.
     * **10% Pelatihan Formal (*Formal Education & Training*)**: Kursus eksekutif, sertifikasi kompetensi, dan lokakarya manajerial terakreditasi.

2. **Dual-Lens Strength & Gap Formulation**:
   * Merumuskan rekomendasi dengan pendekatan berimbang: mengkapitalisasi 3 kekuatan utama kandidat (*Key Strengths*) sekaligus memitigasi 3 area pengembangan prioritas (*Critical Development Gaps*).

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Terkait**: `App\Models\AspectAssessment`, `App\Models\Participant`, `App\Models\PositionFormation`.
* **Logika Query & Kalkulasi Dinamis**:
  1. Mengidentifikasi 3 aspek dengan skor/gap tertinggi untuk dirumuskan strategi kapitalisasi (*multiplier strengths*).
  2. Mengidentifikasi 3 aspek dengan gap defisit terbesar ($\text{individual\_rating} < \text{standard\_rating}$) sebagai sasaran IDP.
  3. Menyusun rencana aksi konkret per aspek kesenjangan ke dalam 3 level intervensi (70% On-the-job, 20% Coaching, 10% Pelatihan Formal).
* **Tampilan Visual UI**: Dua kolom kartu komparasi elegan: *Pilar Kekuatan Utama (Key Strengths)* beraksen hijau di sisi kiri, dan *Area Prioritas Pengembangan (IDP 70-20-10)* di sisi kanan lengkap dengan badge persentase.
