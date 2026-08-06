# Section 16 — Talent 9-Box Matrix

* **Nama Visual**: Matriks Talenta 9-Box (Potensi vs Kinerja)
* **Kode Section**: `nine_box`
* **Komponen File**: [NineBoxMatrix.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/NineBoxMatrix.php) & [nine-box-matrix.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/nine-box-matrix.blade.php)
* **Status Dynamic**: 📋 **PLANNED** (Data SPSP 🔴 New Data Source)

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Assessment Science)

1. **Strategic Talent Classification**:
   * Memetakan posisi strategis kandidat ke dalam matriks 9 kuadran standar industri (Sumbu X: Performance, Sumbu Y: Potential).
2. **Talent Segmentation Categories**:
   * Kuadran 9 (Star / High Potential & High Performance), Kuadran 8 (High Performer), Kuadran 7 (High Potential), hingga Kuadran 1 (Underperformer).
3. **Actionable HR Strategy**:
   * Menentukan tindakan manajerial yang tepat (misal: *Fast-track promotion* untuk Star Talent, *Skill Development* untuk High Potential, atau *Performance Coaching*).

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: Kombinasi `potensi_individual_score` (dari SPSP) & Skor Kinerja Ketenagakerjaan (dari HRIS Performance Dashboard).
