# Section 08 — IQ & Profil Kognitif

* **Nama Visual**: Breakdown Kapasitas Kognitif & Inteligensi
* **Kode Section**: `cognitive`
* **Komponen File**: [ScoreListSection.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/ScoreListSection.php) & [score-list-section.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/score-list-section.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB Sync)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Psychological Science)

1. **General Mental Ability ($g$-factor) & Problem Solving Complexity**:
   * Berdasarkan teori inteligensi Cattell-Horn-Carroll (CHC) dan penelitian Schmidt & Hunter, kemampuan mental umum ($g$-factor / IQ) adalah prediktor tunggal terkuat bagi performa kerja pada jabatan yang memiliki tingkat kompleksitas tinggi.
   * Mengukur kecepatan pemrosesan informasi, kapasitas bernalar abstrak, dan ketajaman dalam memecahkan masalah baru yang belum terstruktur (*fluid intelligence*).

2. **Cognitive Domain Breakdown**:
   * Membedah kapasitas kognitif ke dalam sub-dimensi kemampuan spesifik:
     * **Analytical Thinking**: Kemampuan mengurai masalah multi-sektor menjadi elemen logis penyebab dan dampak.
     * **Numerical Reasoning**: Kecepatan dan ketepatan membaca data statistik, rasio finansial, dan tren kuantitatif.
     * **Verbal Comprehension**: Penguasaan logika bahasa, penalaran argumentatif, dan pemahaman teks regulasi yang kompleks.
     * **Abstract Logic**: Kepekaan mengenali pola tersembunyi (*pattern recognition*) dalam situasi yang ambigu.
     * **Spatial Orientation**: Visualisasi ruang, perencanaan tata letak, dan pemetaan sistemik.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\TestResult`, `App\Models\SubAspectAssessment`, `App\Models\AspectAssessment`, `App\Models\Participant`.
* **Ekstraksi Dual-Source DB**:
  1. **Tabel `test_results`**: Mencari hasil tes kecerdasan resmi kandidat (CFIT Skala 3A/3B dengan `test_code: A.1`/`A.2`, atau IST dengan `test_code: A.5`). Mengambil skor IQ total, kategori klasifikasi baku (*Sangat Superior, Superior, Rata-rata Atas, Rata-rata*), serta interpretasi naratif.
  2. **Tabel `sub_aspect_assessments`**: Mengambil rating individual dan standar minimal pada sub-aspek di bawah aspek *Intelektual / Daya Pikir* (Daya Analisa, Logika Berpikir, Daya Ingat, Daya Konsentrasi, Daya Abstraksi, dsb.).
* **Formula Konversi Fallback**: Jika instrumen mentah belum terisi, IQ diderivasi dari rata-rata rating kognitif ($R$):
  $$\text{IQ Estimasi} = \text{round}(100 + (R - 3.00) \times 15)$$
* **Tampilan UI**: Badge skor IQ komposit dan kategori kecerdasan resmi di header, daftar baris progres sub-dimensi kognitif beserta deviasi standar, serta narasi interpretasi kapasitas berpikir.
