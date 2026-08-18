# Section 15 — Performance Dashboard

* **Nama Visual**: Dashboard Histori Kinerja & Tren KPI Multi-Tahun
* **Kode Section**: `performance`
* **Komponen File**: [PerformanceDashboard.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/PerformanceDashboard.php) & [performance-dashboard.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/performance-dashboard.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB Sync & Auto-Scale Buffer)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Performance Management)

1. **Past Performance as Predictor of Future Success (Bukti Deliverables Nyata)**:
   * Menyelaraskan kapasitas psikologis laten (apa yang *mampu* dilakukan kandidat di atas kertas) dengan bukti kontribusi operasional nyata (apa yang *benar-benar telah dicapai* kandidat di lapangan).
   * Teori manajemen kinerja (*Cascio & Aguinis Performance Management*) menegaskan bahwa konsistensi pencapaian target kerja selama 3–5 tahun berturut-turut adalah indikator terbaik dari keandalan eksekusi (*execution reliability*).

2. **Trend & Stability Analysis (Analisis Pertumbuhan Kinerja)**:
   * Menganalisis kurva tren time-series multi-tahun (apakah kinerja kandidat stabil di atas target, terus meningkat (*positive trajectory*), atau mengalami penurunan performa).
   * Menghitung rata-rata capaian KPI multi-tahun dan laju pertumbuhan tahunan (*Growth per Year*) untuk memvalidasi kesiapan memikul target bisnis/organisasi yang lebih besar.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\ParticipantPerformanceRecord`, `App\Models\Participant`.
* **Tabel Database**: `participant_performance_records` (menyimpan kolom `year`, `kpi_score`, `benchmark_score`, `rating`, `kpi_breakdown`).
* **Formula Perhitungan**:
  * **Rata-rata KPI**: $\text{Avg KPI} = \text{round}\left(\frac{\sum \text{kpi\_score}}{n}, 2\right)$.
  * **Pertumbuhan per Tahun**: $\text{Growth/Year} = \text{round}\left(\frac{\text{Skor Akhir} - \text{Skor Awal}}{n - 1}, 2\right)$.
* **Auto-Scale Dynamic Scaling (Perbaikan Visual)**:
  * Batas bawah ($\text{min}$) dan batas atas ($\text{max}$) sumbu Y dihitung otomatis dari nilai aktual dan target dengan bantalan kelipatan 5:
    $$\text{dynamicMin} = \max\left(0, \left\lfloor \frac{\min(\text{data}) - 3}{5} \right\rfloor \times 5\right)$$
    $$\text{dynamicMax} = \left\lceil \frac{\max(\text{data}) + 3}{5} \right\rceil \times 5$$
* **Tampilan Visual UI**: Line chart time-series Chart.js (Forest Green) vs garis putus-putus benchmark target institusi, pill badge persentase bersih di atas setiap titik data, ringkasan rata-rata dan pertumbuhan per tahun, serta tabel rincian metrik KPI tahun buku aktif.
