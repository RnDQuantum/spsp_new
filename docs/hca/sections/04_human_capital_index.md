# Section 04 — Human Capital Index (HCI)

* **Nama Visual**: Human Capital Index Radar & Composite Gauge
* **Kode Section**: `hci`
* **Komponen File**: [IndexRadarSection.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/IndexRadarSection.php) & [index-radar-section.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/index-radar-section.blade.php)
* **Status Dynamic**: 📋 **PLANNED** (Data SPSP 🟡 Partial)

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Assessment Science)

1. **Holistic Pentagon Profiling**:
   * Visualisasi radar chart 5 pilar HCI memberikan analisis persebaran keseimbangan bakat kandidat (*talent balance*).
2. **Gap & Strength Visualizer**:
   * Memudahkan pimpinan melihat kecenderungan dominansi kandidat (apakah tipe *Executor*, *Strategic Thinker*, *People Leader*, atau *High Integrity Operator*).

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\AspectAssessment`, `App\Models\CategoryAssessment`, `App\Models\FinalAssessment`.
* **Logic Calculation**:
  * Menggunakan service agregasi 5 pilar dari `ExecutiveSummary` (Kompetensi, Potensi, Kinerja, Kepemimpinan, Integritas).
  * Data dirender ke dalam Chart.js Radar Canvas via `data-*` HTML5 attributes.
