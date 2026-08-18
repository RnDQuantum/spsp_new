# Section 19 — Kesehatan Jiwa (Mental Health)

* **Nama Visual**: Evaluasi Kesehatan Jiwa & Penyesuaian Diri
* **Kode Section**: `mental_health`
* **Komponen File**: [MentalHealthSection.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/MentalHealthSection.php) & [mental-health-section.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/mental-health-section.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB Sync via MMPI)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Clinical Psychology)

1. **Triangulasi Lensa 1 MMPI: Skrining Klinis & Hygiene Factor (Go / No-Go Gatekeeper)**:
   * Menggunakan Minnesota Multiphasic Personality Inventory (MMPI-2 / MMPI-180) sebagai instrumen psikologi klinis terstandar untuk mengevaluasi kebugaran mental (*workplace mental fitness*) dan stabilitas psikologis.
   * **Prinsip Etika Psikologi & Pengukuran**: Kesehatan jiwa berfungsi sebagai *hygiene factor / gatekeeper* (indikator kelayakan dasar), sehingga **TIDAK BOLEH dirata-ratakan ke dalam skor positif Talent Index** agar tidak mengaburkan interpretasi kompetensi.
   * Memastikan kandidat tidak memiliki distres psikologis berat, gangguan kepribadian dekompensasi, atau psikopatologi yang dapat membahayakan keselamatan kerja dan stabilitas organisasi.

2. **Dimensi Well-Being & Workplace Adaptability**:
   * **Kesehatan Emosional**: Stabilitas suasana hati dan kematangan koping stres.
   * **Resiliensi Diri**: Daya lentur bangkit dari tekanan krisis kerja (*bounce-back capacity*).
   * **Kepuasan Kerja & Penyesuaian Sosial**: Harmoni interaksi dan integrasi sosial di lingkungan kerja.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\Mmpi` (`$participant->mmpi` / tabel `mmpi`), `App\Models\TestResult` (`test_code: E.1`/`E.2`).
* **Field DB yang Digunakan**:
  * `validitas`: Skala validitas L, F, K (memverifikasi kejujuran dan ketidakteraturan pengisian tes).
  * `internal` & `interpersonal`: Narasi evaluasi dinamika kepribadian internal dan hubungan relasional.
  * `kap_kerja`: Kapasitas kerja fungsional.
  * `klinik` & `kesimpulan`: Diagnosis kelaikan psikologis.
  * `nilai_pq` & `tingkat_stres`: Nilai numerik kualitatif tingkat stres (Rendah / Sedang / Tinggi).
* **Tampilan Visual UI**: Split grid layout dengan indeks well-being horizontal bar, rincian breakdown dimensi well-being di sisi kiri, serta kotak resmi *Catatan Psikolog Klinis SPSP* di sisi kanan.
