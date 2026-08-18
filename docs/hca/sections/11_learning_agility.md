# Section 11 — Learning Agility

* **Nama Visual**: Ketangkasan Belajar & Adaptabilitas
* **Kode Section**: `learning_agility`
* **Komponen File**: [ScoreListSection.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/ScoreListSection.php) & [score-list-section.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/score-list-section.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Psychological Science)

1. **VUCA World Readiness**:
   * *Learning Agility* adalah kemampuan individu untuk secara cepat belajar dari pengalaman dan mengaplikasikan pelajaran tersebut untuk berhasil dalam situasi baru yang belum pernah dihadapi sebelumnya (*first-time or unfamiliar conditions*).
2. **4 Dimensions of Agility**:
   * *Mental Agility* (pemikiran kritis & analisis logika di tengah ketidakpastian).
   * *People Agility* (kemampuan berkolaborasi & kepekaan dinamika sosial).
   * *Change Agility* (kesiapan beradaptasi & fleksibilitas perubahan).
   * *Result Agility* (daya dorong mencapai hasil prima dalam masa transisi).

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\SubAspectAssessment`, `App\Models\AspectAssessment`, `App\Models\Participant`.
* **Formula DB**: Agregasi skor sub-aspek *Mental Agility* (Daya Analisa, Logika Berpikir, Kreativitas), *People Agility* (Sosualitas, Komunikasi Sosial, Kepekaan Interpersonal), *Change Agility* (Penyesuaian Diri, Agen Perubahan, Mobilitas), dan *Result Agility* (Hasrat Berprestasi, Daya Tahan Kerja, Semangat Kerja).
* **Tampilan UI**: Daftar baris progres skor 4 pilar agility, standar formasi, gap, kesimpulan (*Memenuhi Standar* / *Perlu Penguatan*), dan rata-rata skor agility.
