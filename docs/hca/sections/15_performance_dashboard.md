# Section 15 — Performance Dashboard

* **Nama Visual**: Dashboard Histori Kinerja & Tren KPI
* **Kode Section**: `performance`
* **Komponen File**: [PerformanceDashboard.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/PerformanceDashboard.php) & [performance-dashboard.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/performance-dashboard.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Assessment Science)

1. **Past Performance & KPI Consistency**:
   * Evaluasi ketercapaian target kinerja aktual kandidat dalam beberapa tahun terakhir (misal: pencapaian KPI tahunan 2022–2026, efisiensi anggaran, pengawalan inisiatif strategis).
2. **Actual Contribution vs Potential**:
   * Menyelaraskan kapasitas potensi psikologis kandidat dengan bukti kontribusi nyata (*actual deliverables*) di tempat kerja sebagai pilar ke-3 Human Capital Index.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\ParticipantPerformanceRecord`, `App\Models\Participant`.
* **Tabel Database**: `participant_performance_records` (menyimpan riwayat tahunan, skor KPI, target, benchmark, rating, dan breakdown metrik KPI).
* **Seeder Generator**: `PerformanceRecordGenerator.php` dan `ParticipantPerformanceRecordSeeder.php`.
* **Visualisasi UI**: Line Chart tren time-series KPI multi-tahun (Forest Green) vs benchmark target institusi (garis putus-putus), ringkasan pertumbuhan tahunan, serta tabel rincian metrik evaluasi kinerja tahun buku aktif.
