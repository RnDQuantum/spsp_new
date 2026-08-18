# Section 17 — Succession Readiness

* **Nama Visual**: Indikator Kesiapan Suksesi Kepemimpinan (Succession Readiness)
* **Kode Section**: `succession`
* **Komponen File**: [SuccessionReadiness.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/SuccessionReadiness.php) & [succession-readiness.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/succession-readiness.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB Sync)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Succession Planning)

1. **Leadership Pipeline & Business Continuity Risk Mitigation (Rothwell & Charan)**:
   * Mengamankan kelangsungan bisnis (*business continuity*) organisasi dengan mengidentifikasi dan mempersiapkan kandidat pengganti untuk posisi-posisi kunci kepemimpinan sebelum terjadi kekosongan jabatan (*leadership vacuum*).
   * Menilai estimasi horizon waktu kesiapan suksesi (*Succession Horizon*):
     * **Horizon 1 (Ready Now)**: Siap promosi segera tanpa masa transisi panjang ($0-6$ bulan).
     * **Horizon 2 (Ready in 1–2 Years)**: Memiliki potensi tinggi namun membutuhkan akselerasi kompetensi manajerial spesifik ($12-24$ bulan).
     * **Horizon 3 (Ready in 2–3 Years)**: Suksesi jangka menengah dengan fokus pada rotasi lintas fungsi dan pematangan kepemimpinan ($24-36$ bulan).

2. **Rantai Keputusan Talenta (*Talent Progression Chain*)**:
   * Menghubungkan secara langsung diagnosa kuadran 9-Box (Section 16) dengan penetapan peran suksesi:
     * Kandidat di *Star Talent / High Potential* (Box 9 & 8) diproyeksikan langsung untuk peran Horizon 1 & Horizon 2 jenjang eksekutif.
     * Kandidat di *Core Player / High Performer* (Box 5 & 6) diproyeksikan untuk peran Horizon 2 & Horizon 3 pengayaan fungsional/spesialis.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\Participant`, `App\Models\PositionFormation`, `App\Models\FinalAssessment`, `App\Models\ParticipantPerformanceRecord`.
* **Formula Penentuan Horizon & Keyakinan**:
  * **Peran Target Utama**: Diderivasi dari formasi posisi aktif (`PositionFormation::name` / `current_position`).
  * **Persentase Kesiapan Horizon 1**: Dihitung dari kombinasi skor capaian KPI aktual dan skor potensi individual ($\le 98\%$).
  * **Deskripsi Horizon**: Disesuaikan secara otomatis berdasarkan klasifikasi 9-Box untuk memberikan arahan pengembangan spesifik tiap horizon.
* **Tampilan Visual UI**: Kartu peran target utama, timeline bertingkat 3 horizon (Siap Sekarang, 1 Tahun, 2-3 Tahun) dengan persentase keyakinan kesiapan, status badge, dan deskripsi intervensi pendukung.
