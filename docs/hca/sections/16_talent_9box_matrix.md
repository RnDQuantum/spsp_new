# Section 16 — Talent 9-Box Matrix

* **Nama Visual**: Matriks Talenta 9-Box (Potensi vs Kinerja)
* **Kode Section**: `nine_box`
* **Komponen File**: [NineBoxMatrix.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/NineBoxMatrix.php) & [nine-box-matrix.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/nine-box-matrix.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB Sync)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Assessment Science)

1. **Strategic Talent Classification (Model Klasik McKinsey / GE 9-Box Grid)**:
   * Memetakan posisi strategis talenta individu ke dalam matriks 9 kuadran standar industri global (Sumbu Vertikal Y: **Potensi Masa Depan**, Sumbu Horizontal X: **Kinerja Masa Lalu**).
   * Menjadi jembatan kausal dalam *Talent Progression Chain* yang menghubungkan hasil asesmen dengan strategi suksesi (Section 17) dan rekomendasi peran berikutnya (Section 23).

2. **Perbedaan Ilmiah: 9-Box Individu (HCA) vs 9-Box Populasi (General Report)**:
   * **Model HCA Report (Section 16 - Criterion-Referenced)**:
     * Menggunakan pendekatan ambang batas mutlak (*absolute corporate threshold*) untuk menilai kesiapan individu terhadap syarat definitif jabatan eksekutif.
     * Sumbu X menggunakan **Kinerja Nyata (KPI HRIS)** dan Sumbu Y menggunakan **Potensi Psikologis (SPSP)**.
   * **Model General Report (Talent Pool - Norm-Referenced $\mu \pm \sigma$)**:
     * Menggunakan kurva lonceng normal statistik populasi untuk membandingkan peringkat relatif seluruh peserta dalam 1 batch seleksi/formasi (Potensi vs Kompetensi Asesmen).

3. **9 Kuadran Segmentasi Talenta & Tindakan Strategis HR**:
   * **Box 9 (Star Talent — High Pot & High Perf)**: *Fast-track promotion*, penugasan proyek strategis, retensi khusus.
   * **Box 8 (High Potential — High Pot & Med Perf)**: *Mentoring* intensif, peningkatan target strategis, persiapan suksesi 1–2 tahun.
   * **Box 7 (Enigma — High Pot & Low Perf)**: Evaluasi kesesuaian peran (*job-fit*), pendampingan eksekusi operasional.
   * **Box 6 (High Performer — Med Pot & High Perf)**: Andalan eksekusi organisasi, penguatan wawasan manajerial strategis.
   * **Box 5 (Core Player — Med Pot & Med Perf)**: Tulang punggung organisasi, pengayaan peran (*job enrichment*).
   * **Box 4 (Dilemma — Med Pot & Low Perf)**: Pendampingan performa terstruktur, penetapan target jangka pendek.
   * **Box 3 (Solid Professional — Low Pot & High Perf)**: Spesialis fungsional di bidangnya, penguatan adaptabilitas.
   * **Box 2 (Effective Organiser — Low Pot & Med Perf)**: Pelatihan keterampilan operasional, peningkatan keterlibatan tim.
   * **Box 1 (Underperformer — Low Pot & Low Perf)**: *Performance Improvement Plan (PIP)* / penataan ulang sasaran.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\Participant`, `App\Models\FinalAssessment`, `App\Models\ParticipantPerformanceRecord`.
* **Formula Klasifikasi 2 Sumbu**:
  1. **Sumbu Y (Potensi Psikologis)**: Diambil dari `final_assessments.potensi_individual_score` (Skala 1.00 – 5.00):
     * Level 3 (Tinggi): $\ge 3.60$
     * Level 2 (Sedang): $2.80 - 3.59$
     * Level 1 (Rendah): $< 2.80$
  2. **Sumbu X (Kinerja Aktual KPI)**: Diambil dari rekor KPI tahun terakhir pada `participant_performance_records.kpi_score` (%):
     * Level 3 (Tinggi): $\ge 95.00\%$
     * Level 2 (Sedang): $85.00\% - 94.99\%$
     * Level 1 (Rendah): $< 85.00\%$
* **Tampilan Visual UI**: Grid interaktif 3x3 dengan highlight emas pada kuadran aktif peserta (*active cell*), lencana kuadran, serta narasi interpretasi penempatan talenta personal (*Talent Placement Narrative*).
