# Section 12 — Leadership Potential

* **Nama Visual**: Breakdown Potensi Kepemimpinan Masa Depan
* **Kode Section**: `leadership_potential`
* **Komponen File**: [ScoreListSection.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/ScoreListSection.php) & [score-list-section.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/score-list-section.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB Sync)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Leadership Science)

1. **Strategic & Managerial Pipeline Readiness (Charan, Drotter, & Noel - The Leadership Pipeline)**:
   * Menilai kesiapan kandidat dalam bertransisi dari memimpin diri sendiri (*Managing Self*) menuju memimpin orang lain (*Managing Others*), memimpin manajer (*Managing Managers*), hingga memimpin fungsi bisnis (*Enterprise Leadership*).
   * Membantu memproyeksikan apakah kandidat mampu melepaskan ketergantungan teknis operasional dan beralih ke pola pikir strategis makro.

2. **6 Core Leadership Dimensions**:
   * **Visioning**: Kemampuan merumuskan arah masa depan, menyusun peta jalan strategis, dan mengartikulasikan sasaran unit secara inspiratif.
   * **Decision Making**: Kecepatan, ketegasan, dan akuntabilitas dalam mengambil keputusan sulit di tengah keterbatasan informasi.
   * **Strategic Influence**: Kekuatan persuasi, negosiasi tingkat tinggi, dan kemampuan merangkul pemangku kepentingan (*stakeholder management*).
   * **Execution Control**: Disiplin mengawal rencana kerja, pengawasan sistemik (*governance*), dan pengendalian mutu hasil.
   * **Coaching & Developing Others**: Komitmen membina kader penerus, mendelegasikan wewenang, dan mempromosikan talenta terbaik.
   * **Strategic Thinking**: Kemampuan membaca tren industri eksternal, analisis dampak jangka panjang, dan respon proaktif terhadap dinamika pasar/regulasi.

3. **Konsep Keilmuan: Indeks Sintesis Tematik (*Synthesized Meta-Construct*)**:
   * Mengintegrasikan sub-aspek manajerial dan kepemimpinan yang tersebar di database SPSP ke dalam 6 pilar kepemimpinan strategis tanpa menambah beban instrumen baru.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\SubAspectAssessment`, `App\Models\AspectAssessment`, `App\Models\Participant`.
* **Formula Pemetaan Sub-Aspek SPSP ke 6 Dimensi Kepemimpinan**:
  1. **Visioning**: Rata-rata skor sub-aspek `['Vision Clarity', 'Direction Setting', 'Perencanaan']`.
  2. **Decision Making**: Rata-rata skor sub-aspek `['Pembuatan Keputusan', 'Pemecahan Masalah', 'Identifikasi Masalah']`.
  3. **Strategic Influence**: Rata-rata skor sub-aspek `['Mempengaruhi', 'Communication', 'Komunikasi']`.
  4. **Execution Control**: Rata-rata skor sub-aspek `['Planning & Organizing', 'Measurement', 'Pengendalian', 'Sistematika Kerja']`.
  5. **Coaching & Developing**: Rata-rata skor sub-aspek `['Mengarahkan', 'Koordinasi', 'Kerjasama']`.
  6. **Strategic Thinking**: Rata-rata skor sub-aspek `['Kepemimpinan', 'Agen Perubahan', 'Analisa dan Sintesa', 'Result Focus']`.
* **Tampilan UI**: Daftar baris progres skor 6 dimensi kepemimpinan, standar formasi, gap, kesimpulan evaluasi, badge konteks *Indeks Sintesis Tematik*, serta rata-rata skor kepemimpinan komposit.
