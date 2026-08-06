# Section 01 — Cover Page

* **Nama Visual**: Halaman Sampul Laporan HCA
* **Kode Section**: `cover`
* **Komponen File**: [Cover.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/Cover.php) & [cover.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/cover.blade.php)
* **Status Dynamic**: ✅ **DONE**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Assessment Science)

1. **Formal Executive Identification**:
   * Halaman sampul merupakan identitas resmi dokumen penilaian individu yang menandai validitas hukum dan aspek kerahasiaan (*confidentiality*) dari hasil evaluasi psikometri dan kompetensi.
2. **Branding & Institutional Framing**:
   * Menampilkan metadata asesmen (nama institusi klien, nama event, tanggal pelaksanaan, dan formasi posisi target) agar laporan siap dilampirkan dalam sidang suksesi atau portofolio karir pejabat/pegawai.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\Participant` (dengan relasi `assessmentEvent.institution`, `positionFormation.template`, `batch`).
* **Field yang Ditampilkan**:
  * Nama Lengkap Kandidat: `$participant->name`
  * Nomor Tes: `$participant->test_number`
  * Formasi Posisi Target: `$participant->positionFormation->name`
  * Tanggal Asesmen: `$participant->assessmentEvent->start_date`
  * Nama Institusi: `$participant->institution->name`
  * Foto / Inisial: `$participant->photo_path` atau generator inisial nama.
