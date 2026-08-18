# Section 16 — Talent 9-Box Matrix

* **Nama Visual**: Matriks Talenta 9-Box (Potensi vs Kinerja)
* **Kode Section**: `nine_box`
* **Komponen File**: [NineBoxMatrix.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/NineBoxMatrix.php) & [nine-box-matrix.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/nine-box-matrix.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Assessment Science)

1. **Strategic Talent Classification**:
   * Memetakan posisi strategis kandidat ke dalam matriks 9 kuadran standar industri (Sumbu X: Performance, Sumbu Y: Potential).
2. **Talent Segmentation Categories**:
   * Kuadran 9 (*Star Talent* / High Potential & High Performance), Kuadran 8 (*High Potential*), Kuadran 6 (*High Performer*), Kuadran 5 (*Core Player*), hingga Kuadran 1 (*Underperformer*).
3. **Actionable HR Strategy**:
   * Menentukan tindakan manajerial yang tepat (*Fast-track promotion* untuk Star Talent, *Strategic Mentoring* untuk High Potential, *Job Enrichment* untuk Core Player, atau *PIP*).

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\Participant`, `App\Models\FinalAssessment`, `App\Models\ParticipantPerformanceRecord`.
* **Formula DB**:
  * **Sumbu Y (Potensi)**: Dinormalisasi dari `$participant->finalAssessment->potensi_individual_score` (Tinggi: &ge; 3.60, Sedang: 2.80–3.59, Rendah: < 2.80).
  * **Sumbu X (Kinerja)**: Dinormalisasi dari skor KPI tahun terakhir pada `participant_performance_records` (Tinggi: &ge; 95.00%, Sedang: 85.00–94.99%, Rendah: < 85.00%).
* **Tampilan UI**: Grid interaktif 9 kuadran dengan penandaan visual emas (*active cell*), lencana kuadran klasifikasi, serta narasi interpretasi penempatan talenta personal (*Talent Placement Narrative*).
