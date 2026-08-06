# Section 00 — Active Talent Selector & Sidebar

* **Nama Visual**: Navigasi Utama & Modal Filter Peserta
* **Kode Section**: `00_talent_selector`
* **Komponen File**: [HcaReportPage.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/HcaReportPage.php) & [hca-report-page.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/hca-report-page.blade.php)
* **Status Dynamic**: ✅ **DONE**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Assessment Science)

1. **Contextual Navigation & Multi-Talent Comparison**:
   * Dalam evaluasi bakat skala organisasi, asesor/HR eksekutif perlu berpindah konteks antar kandidat pada event asesmen atau formasi jabatan yang sama secara cepat tanpa kehilangan progres analisis.
2. **Session Persistence**:
   * Memastikan filter terpilih (*Event Asesmen &rarr; Formasi Jabatan &rarr; Peserta*) tersimpan secara aman dalam sesi pengguna sehingga seluruh section HCA Report menampilkan data peserta secara konsisten.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\Participant`, `App\Models\AssessmentEvent`, `App\Models\PositionFormation`.
* **Kritera Query & Filter**:
  * Event Dropdown: `AssessmentEvent::orderByDesc('start_date')`.
  * Jabatan Dropdown: `PositionFormation` yang berelasi dengan `selectedEventCode`.
  * Participant List: `Participant::with(['positionFormation', 'assessmentEvent'])` difilter berdasarkan event, posisi, dan pencarian string nama/nomor tes (`test_number`).
* **Session Sync**: Menyimpan state ke `session(['filter.participant_id' => ...])` untuk konsistensi di seluruh halaman laporan SPSP.
