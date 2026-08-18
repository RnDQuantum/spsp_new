# Section 04 — Human Capital Index (HCI)

* **Nama Visual**: Human Capital Index Radar & Composite Gauge
* **Kode Section**: `hci`
* **Komponen File**: [IndexRadarSection.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/IndexRadarSection.php) & [index-radar-section.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/index-radar-section.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB Sync)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Assessment Science)

1. **Holistic Pentagon Profiling (Talent Equilibrium & Balance)**:
   * Visualisasi radar chart 5 pilar HCI memberikan gambaran geometris mengenai keseimbangan bakat (*talent equilibrium*).
   * Teori manajemen bakat modern (*Ulrich & Brockbank Human Resource Value Proposition*) menegaskan bahwa kepemimpinan puncak memerlukan keunggulan yang seimbang di seluruh dimensi, bukan hanya skor tinggi pada satu aspek namun defisit parah pada aspek lainnya (misal: potensi tinggi tapi integritas rendah, atau kinerja tinggi tapi kepemimpinan rapuh).

2. **Dominance & Archetype Detection**:
   * Membantu asesor dan dewan komite mengidentifikasi pola arketipe dominan kandidat:
     * *The High-Impact Executor* (Dominan Kinerja & Kompetensi).
     * *The Strategic Visionary* (Dominan Potensi & Kepemimpinan).
     * *The Culture & Governance Anchor* (Dominan Integritas & Kematangan Emosional).

3. **Multi-Layer Benchmark Overlay (Aktual vs Standar vs Toleransi)**:
   * Menampilkan visualisasi 3 lapis poligon berlapis:
     1. **Lapisan Hijau (Skor Aktual)**: Nilai riil yang dicapai kandidat.
     2. **Lapisan Merah/Netral (Standar Minimum)**: Batas standar kompetensi yang dipersyaratkan formasi jabatan.
     3. **Lapisan Amber (Batas Toleransi)**: Ambang toleransi minimal ($90\%$ dari standar) yang masih dapat diterima dengan program penguatan.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\AspectAssessment`, `App\Models\CategoryAssessment`, `App\Models\FinalAssessment`, `App\Models\PositionFormation`.
* **Formula Agregasi 5 Pilar**:
  * Menggunakan kalkulasi yang selaras dengan Section 02 (*Kompetensi, Potensi, Kinerja, Kepemimpinan, Integritas*).
  * **Standar Baseline Formasi**: Diambil dari rata-rata `standard_rating` aspek pada template formasi jabatan (default $3.00$).
  * **Batas Toleransi**: Dihitung sebesar $90\%$ dari standar formasi ($\text{Standard} \times 0.90$).
  * **Render Visual**: Menggunakan Chart.js Radar Canvas dengan polygon fill transparan dan custom ticks scale rendering untuk memastikan keterbacaan kontras tinggi.
