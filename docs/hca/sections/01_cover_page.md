# Section 01 — Cover Page

* **Nama Visual**: Halaman Sampul Laporan HCA
* **Kode Section**: `cover`
* **Komponen File**: [Cover.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/Cover.php) & [cover.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/cover.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB Sync)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Assessment Science)

1. **Formal Executive Identification & Legal Validity**:
   * Halaman sampul merupakan identitas resmi dokumen penilaian individu yang menandai validitas hukum, otentisitas pelaksanaan, dan aspek kerahasiaan (*confidentiality classification*) dari hasil evaluasi psikometri dan kompetensi berdasarkan kode etik Himpunan Psikologi Indonesia (HIMPSI) dan standar internasional asesmen SDM.
   * Memberikan kejelasan status dokumen sebagai berkas rahasia (*Strictly Confidential*) yang ditujukan khusus bagi pihak berwenang (Direksi, Baperjakat, Panitia Seleksi, dan Pejabat Pembina Kepegawaian).

2. **Contextual Institutional Framing & Branding**:
   * Menampilkan metadata asesmen resmi: nama institusi klien, nama kegiatan/event asesmen, formasi jabatan target (*target role*), nomor tes peserta, dan tanggal pelaksanaan asesmen.
   * Menjamin keterlacakan (*traceability*) dokumen dalam arsip portofolio karir pejabat/pegawai untuk keperluan audit talenta, verifikasi kenaikan jenjang, atau sidang suksesi.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\Participant` (dengan eager loading relasi `assessmentEvent.institution`, `positionFormation.template`, `batch`, `institution`).
* **Field yang Ditampilkan**:
  * **Nama Lengkap Kandidat**: `$participant->name` (lengkap dengan gelar depan dan gelar belakang).
  * **Nomor Tes / SKB**: `$participant->test_number` dan `$participant->skb_number`.
  * **Formasi Jabatan Target**: `$participant->positionFormation->name` dan level jabatan.
  * **Event & Tanggal Asesmen**: `$participant->assessmentEvent->name` dan format tanggal terlokalisasi (`assessment_date` / `start_date`).
  * **Institusi Penyelenggara & Klien**: `$participant->institution->name` atau `$participant->assessmentEvent->institution->name`.
  * **Visual Avatar / Foto**: `$participant->photo_path` (dengan fallback generator inisial nama dua huruf kapital).
