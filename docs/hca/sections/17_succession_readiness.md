# Section 17 — Succession Readiness

* **Nama Visual**: Indikator Kesiapan Suksesi Kepemimpinan
* **Kode Section**: `succession`
* **Komponen File**: [SuccessionReadiness.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/SuccessionReadiness.php) & [succession-readiness.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/succession-readiness.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Assessment Science)

1. **Succession Pipeline Timeline**:
   * Menilai estimasi waktu kesiapan kandidat untuk menduduki posisi pimpinan kunci di masa mendatang: *Ready Now* (siap promosi segera), *Ready 1 Year* (siap dalam 12 bulan setelah akselerasi), atau *Ready 2-3 Years* (suksesi jangka panjang).
2. **Business Continuity Risk Mitigation**:
   * Mencegah kekosongan kepemimpinan (*leadership vacuum*) pada posisi-posisi krusial organisasi melalui pemetaan peran target yang jelas.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\Participant`, `App\Models\PositionFormation`, `App\Models\FinalAssessment`, `App\Models\ParticipantPerformanceRecord`.
* **Formula DB**:
  * **Jabatan Target Utama**: Diderivasi dari formasi jabatan aktif peserta (`PositionFormation::name` / `current_position`).
  * **Horizon 1 (Siap Sekarang)**: Persentase kesiapan berdasarkan skor capaian KPI aktual dan potensi (&le; 98%).
  * **Horizon 2 (Kesiapan 1-2 Tahun)**: Target peran manajerial yang lebih tinggi (Direktur / Senior Manager).
  * **Horizon 3 (Kesiapan 2-3 Tahun)**: Target peran eksekutif puncak (Chief Executive / Head of Division).
* **Tampilan UI**: Timeline bertingkat (Horizon 1, 2, 3) dengan status kesiapan badge, persentase keyakinan, dan rencana pengembangan spesifik tiap horizon.
