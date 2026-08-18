# Section 07 — Layer 2: Potensi

* **Nama Visual**: Evaluasi Potensi Psikologis Laten
* **Kode Section**: `potential`
* **Komponen File**: [IndexRadarSection.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/IndexRadarSection.php) & [index-radar-section.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/index-radar-section.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB Sync)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Psychological Science)

1. **Latent Psychological Capacity & Growth Ceiling**:
   * Mengukur dimensi psikologis mendasar yang bersifat relatif stabil dalam jangka panjang (*trait-like psychological constructs*).
   * Menilai kapasitas laten (*latent capability*) yang menjadi batas atas daya berkembang (*growth ceiling*) kandidat ketika ditempatkan pada tingkat kompleksitas masalah yang jauh lebih tinggi.
   * Meliputi 5 domain psikologis klasik:
     * **Intelektual / Daya Pikir**: Kapasitas pemrosesan logika, abstraksi, dan kecepatan penalaran.
     * **Sikap Kerja**: Daya tahan, ketelitian, sistematika kerja, dan tempo kerja di bawah target.
     * **Potensi Kerja**: Inisiatif, kemandirian, dan dorongan berprestasi (*need for achievement*).
     * **Sosualitas / Interpersonal**: Keluwesan bergaul, toleransi sosial, dan kepekaan hubungan kerja.
     * **Kepribadian**: Kestabilan emosional, kematangan bersikap, dan integritas diri.

2. **Predictive Performance Foundation**:
   * Dalam psikologi industri dan organisasi (I/O Psychology), potensi psikologis adalah fondasi pembentuk kompetensi (*foundation of competency mastery*). Kandidat dengan skor potensi tinggi akan memiliki kurva belajar yang lebih curam (*high learnability*) dan lebih cepat menguasai keahlian manajerial baru.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\AspectAssessment`, `App\Models\CategoryType` (Code: `potensi`), `App\Models\Participant`.
* **Query Service**: `App\Services\IndividualAssessmentService::getAspectAssessments($participantId, $potensiCategoryId, 0)`.
* **Formula & Standar**:
  * Mengambil nilai rating aktual per aspek kategori `potensi`.
  * Standar formasi diambil dari konfigurasi template formasi jabatan (3-Layer Priority: Session $\rightarrow$ Custom $\rightarrow$ Quantum Default).
  * Menghitung nilai deviasi/gap, rata-rata komposit potensi, dan kategori potensi (*Top / High / Strong / Developing Potential*).
* **Tampilan Visual UI**: Visualisasi radar poligon 5 dimensi, ring circular composite gauge, dan tabel perbandingan rating aktual vs standar minimal & batas toleransi.
