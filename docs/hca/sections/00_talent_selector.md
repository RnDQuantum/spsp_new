# Section 00 — Active Talent Selector & Sidebar

* **Nama Visual**: Navigasi Utama & Modal Filter Peserta
* **Kode Section**: `00_talent_selector`
* **Komponen File**: [HcaReportPage.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/HcaReportPage.php) & [hca-report-page.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/hca-report-page.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB Sync)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Assessment Science)

1. **Contextual Navigation & Multi-Talent Comparison (Komparasi Kohort & Formasi)**:
   * Dalam evaluasi bakat skala organisasi dan sidang dewan suksesi, asesor, pimpinan pansel, maupun HR eksekutif perlu melakukan perbandingan profil antar kandidat (*head-to-head talent calibration*) pada formasi jabatan target yang sama.
   * Modul pemilih talenta aktif ini menyediakan transisi konteks instan tanpa mereset posisi section yang sedang dibaca, memungkinkan pengambil keputusan membandingkan indikator spesifik (misal membandingkan nilai *Strategic Thinking* atau *9-Box Placement* kandidat A vs kandidat B) secara langsung.

2. **Hierarchical Assessment Scope & Data Integrity**:
   * Menegakkan hierarki 3-tingkat (*Cascading Filter: Event Asesmen $\rightarrow$ Formasi Jabatan Target $\rightarrow$ Peserta Asesmen*).
   * Menjamin bahwa standar baseline kompetensi, bobot aspek, dan norma psikometri yang diterapkan pada laporan selalu selaras dengan formasi posisi target peserta yang bersangkutan.

3. **Session State Persistence (Konsistensi Analisis SPSP)**:
   * Memastikan filter peserta terpilih tersimpan secara atomik dalam sesi pengguna (`session(['filter.participant_id' => ...])`), sehingga saat pengguna berpindah dari laporan HCA ke General Report, Shortlist, atau Individual Report lainnya di SPSP, konteks peserta tetap terjaga secara konsisten tanpa anomali data.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\Participant`, `App\Models\AssessmentEvent`, `App\Models\PositionFormation`.
* **Kriteria Query & Relasi**:
  * **Event Dropdown**: `AssessmentEvent::query()->orderByDesc('start_date')->get(['code', 'name'])`.
  * **Jabatan Dropdown**: `PositionFormation::whereHas('assessmentEvent', fn($q) => $q->where('code', $selectedEventCode))->orderBy('name')`.
  * **Participant Search**: `Participant::with(['positionFormation', 'assessmentEvent'])` dengan multi-kolom debounced filter pada nama peserta dan nomor tes (`test_number`).
* **Session Synchronization**: Menyimpan nilai ke `filter.event_code`, `filter.position_formation_id`, dan `filter.participant_id`.
