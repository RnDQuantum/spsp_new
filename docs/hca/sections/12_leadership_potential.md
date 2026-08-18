# Section 12 — Leadership Potential

* **Nama Visual**: Breakdown Potensi Kepemimpinan Masa Depan
* **Kode Section**: `leadership_potential`
* **Komponen File**: [ScoreListSection.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/ScoreListSection.php) & [score-list-section.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/score-list-section.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Psychological Science)

1. **Strategic & Managerial Readiness**:
   * Evaluasi kesiapan kandidat dalam mengemban peran kepemimpinan jenjang menengah hingga eksekutif.
2. **6 Core Leadership Dimensions**:
   * *Visioning* (kemampuan merumuskan arah masa depan).
   * *Strategic Thinking* (analisis jangka panjang & dampak organisasi).
   * *Decision Making* (ketegasan & akuntabilitas keputusan).
   * *Strategic Influence* (kemampuan meyakinkan pemangku kepentingan).
   * *Execution Control* (fokus pada eksekusi & hasil).
   * *Coaching & Developing Others* (kaderisasi & pengembangan tim).

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\SubAspectAssessment`, `App\Models\AspectAssessment`, `App\Models\Participant`.
* **Formula DB**: Agregasi skor sub-aspek *Visioning* (Vision Clarity, Direction Setting, Perencanaan), *Decision Making* (Pembuatan Keputusan, Pemecahan Masalah), *Strategic Influence* (Mempengaruhi, Komunikasi), *Execution Control* (Planning & Organizing, Measurement, Pengendalian), *Coaching & Developing* (Mengarahkan, Koordinasi, Kerjasama), dan *Strategic Thinking* (Kepemimpinan, Agen Perubahan, Result Focus).
* **Tampilan UI**: Daftar baris progres skor 6 dimensi kepemimpinan, standar formasi, gap, kesimpulan (*Memenuhi Standar*), dan rata-rata skor kepemimpinan.
