# Section 15 — Performance Dashboard

* **Nama Visual**: Dashboard Histori Kinerja & Tren KPI
* **Kode Section**: `performance`
* **Komponen File**: [PerformanceDashboard.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/PerformanceDashboard.php) & [performance-dashboard.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/performance-dashboard.blade.php)
* **Status Dynamic**: 📋 **PLANNED** (Data SPSP 🔴 New Data Source)

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Assessment Science)

1. **Past Performance & KPI Consistency**:
   * Evaluasi ketercapaian target kinerja aktual kandidat dalam beberapa tahun terakhir (misal: pencapaian KPI bulanan/tahunan, revenue growth, efisiensi operasional).
2. **Actual Contribution vs Potential**:
   * Menyelaraskan kapasitas potensi psikologis kandidat dengan bukti kontribusi nyata (*actual deliverables*) di tempat kerja.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Sumber Data**: Data capaian KPI kinerja historis (misal 3-5 tahun terakhir) dari sistem HR Performance Management institusi klien.
* **Visualisasi UI**: Line Chart tren KPI tahunan (Forest Green fill) vs target institusi (dotted line) & tabel metrik kinerja.
