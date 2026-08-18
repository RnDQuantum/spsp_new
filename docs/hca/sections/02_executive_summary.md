# Section 02 — Ringkasan Eksekutif (Executive Summary)

* **Nama Visual**: Ringkasan Eksekutif & Snapshot Evaluasi Utama
* **Kode Section**: `exec_summary`
* **Komponen File**: [ExecutiveSummary.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/ExecutiveSummary.php) & [executive-summary.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/executive-summary.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB Sync)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Assessment Science)

1. **Executive Decision Support (Prinsip 3-Detik C-Level)**:
   * Eksekutif C-Level, Direksi, dan Anggota Tim Pansel memiliki keterbatasan waktu dalam menelaah puluhan halaman rincian psikogram teknis. Section ini dirancang secara khusus untuk memberikan kesimpulan makro yang komprehensif hanya dalam 3 detik pembacaan.
   * Menampilkan predikat kesiapan penugasan definitif (*SANGAT DISARANKAN / DISARANKAN / DISARANKAN DENGAN CATATAN / TIDAK DISARANKAN*) yang langsung menjawab pertanyaan utama: *"Apakah kandidat ini siap ditempatkan pada jabatan target sekarang?"*.

2. **5 Pillars Multi-Dimensional Assessment (Integrasi Holistik SDM)**:
   * Menghilangkan bias penilaian satu dimensi (hanya melihat psikotes atau hanya melihat capaian KPI) dengan menggabungkan 5 pilar utama evaluasi modal manusia (*Human Capital Evaluation Framework*):
     1. **Pilar Kompetensi (Layer 1)**: Kapabilitas perilaku teramati (*observable behaviors*) sesuai standar jabatan.
     2. **Pilar Potensi (Layer 2)**: Kapasitas psikologis laten dan batas atas daya tumbuh (*latent cognitive & psychological capacity*).
     3. **Pilar Kinerja (Layer 3)**: Rekam jejak kontribusi nyata dan bukti capaian output operasional (*actual past deliverables*).
     4. **Pilar Kepemimpinan**: Kesiapan mengarahkan visi, membuat keputusan kritis, dan mempengaruhi pemangku kepentingan (*leadership readiness*).
     5. **Pilar Integritas**: Keselarasan nilai moral, kepatuhan etika, dan benteng tata kelola organisasi (*ethical risk safeguard*).

3. **Composite Talent Index (Skala Terstandar 1.00 – 5.00)**:
   * Mengintegrasikan ke-5 pilar menjadi satu indeks kuantitatif terstandar untuk mempermudah pemeringkatan (*talent pool ranking*) dan kalibrasi antar kandidat lintas divisi/instansi.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\Participant`, `App\Models\FinalAssessment`, `App\Models\CategoryAssessment`, `App\Models\AspectAssessment`.
* **Formula Perhitungan 5 Pilar**:
  1. **Pilar Kompetensi**: Rata-rata rating aspek pada kategori `kompetensi` via `IndividualAssessmentService::getAspectAssessments($participantId, $kompetensiCatId)`. Jika belum ada, fallback ke `$participant->finalAssessment->kompetensi_individual_score`.
  2. **Pilar Potensi**: Rata-rata rating aspek pada kategori `potensi` via `IndividualAssessmentService`. Fallback ke `$participant->finalAssessment->potensi_individual_score`.
  3. **Pilar Kinerja**: Normalisasi persentase capaian target kerja ke skala 5.00:
     $$\text{Rating Kinerja} = \min\left(5.00, \text{round}\left(\frac{\text{achievement\_percentage}}{100} \times 5.00, 2\right)\right)$$
  4. **Pilar Kepemimpinan**: Rating individual aspek yang mengandung kata kunci "kepemimpinan" / "leadership". Jika tidak ada, diderivasi dari rata-rata aspek manajerial ($\frac{\text{Potensi} + \text{Kompetensi}}{2}$).
  5. **Pilar Integritas**: Rating individual aspek "integritas" / "integrity" / "etika". Jika tidak ada, diderivasi dari rata-rata aspek sikap kerja ($\frac{\text{Potensi} + \text{Kompetensi}}{2}$).
  6. **Talent Index Score**:
     $$\text{Talent Index} = \text{round}\left(\frac{\text{Kompetensi} + \text{Potensi} + \text{Kinerja} + \text{Kepemimpinan} + \text{Integritas}}{5}, 2\right)$$
  7. **Kategori Talenta (Talent Category Thresholds)**:
     * $\ge 4.50$: **Top Talent**
     * $4.00 - 4.49$: **Strong Talent**
     * $3.50 - 3.99$: **Promising Talent**
     * $3.00 - 3.49$: **Developing Talent**
     * $< 3.00$: **Needs Focus**
  8. **Status Kesiapan**: Diambil langsung dari kesimpulan resmi asesor pada tabel `final_assessments.conclusion_text` atau `conclusion_code`.
