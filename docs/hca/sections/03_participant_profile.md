# Section 03 — Identitas Peserta (Participant Profile)

* **Nama Visual**: Profil Biodata Lengkap Peserta
* **Kode Section**: `participant_id`
* **Komponen File**: [ParticipantProfile.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/ParticipantProfile.php) & [participant-profile.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/participant-profile.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB Sync)** (Data SPSP 🟢 Reuse)

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Assessment Science)

1. **Comprehensive Demographic & Administrative Context**:
   * Menyediakan rincian demografis lengkap (jenis kelamin, usia, tempat/tanggal lahir, agama, status perkawinan, pendidikan, dan kontak) yang berfungsi sebagai variabel kontrol dalam menginterpretasikan hasil asesmen psikologis.
2. **Employment Profile & Organizational Fit**:
   * Memetakan riwayat kepegawaian (status kepegawaian, pangkat, golongan, unit kerja, jabatan pelaksana, jabatan fungsional, jabatan struktural, dan pengalaman kerja) sebagai basis verifikasi rekam jejak.
3. **Role Fit & Target Assessment Baseline**:
   * Menghubungkan latar belakang profil dengan formasi jabatan target, level jabatan, dan minat penempatan untuk analisis keselarasan peran (*person-job fit*).

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\Participant` (dengan relasi `positionFormation.template`, `assessmentEvent.institution`, `institution`, `batch`, `finalAssessment`).
* **Pengelompokan Field Database**:
  1. **Informasi Pribadi & Kependudukan**:
     * `name`, `gelar_depan`, `gelar_belakang` (diformat lengkap),
     * `nik` (Nomor Induk Kependudukan), `test_number` (No. Tes), `skb_number` (No. SKB), `no_kjg` (No. KJG),
     * `tempat_lahir`, `tanggal_lahir` (dengan kalkulasi usia otomatis),
     * `gender` (Laki-Laki / Perempuan), `agama`, `status_perkawinan`, `pendidikan` (Pendidikan Terakhir),
     * `email`, `phone` (No. Handphone / WhatsApp).
  2. **Profil Kepegawaian & Posisi Saat Ini**:
     * `status_kepegawaian` (PNS / PPPK / Tetap / BUMN / Swasta),
     * `pangkat`, `golongan` (contoh: "Penata Muda / Gol. III/a"),
     * `unit_kerja`, `institution_id` (`institution.name`),
     * `jabatan_pelaksana`, `jbt_fungsional`, `jbt_struktural`, `pengalaman_kerja`.
  3. **Konteks Asesmen & Formasi Target**:
     * `position_formation_id` (`positionFormation.name`),
     * `level_jabatan` (`positionFormation.level_jabatan`),
     * `minat_penempatan`,
     * `event_id` (`assessmentEvent.name`), `batch_id` (`batch.name`),
     * `assessment_date` (Tanggal Asesmen).
* **Tampilan UI**: Card ringkasan profil eksekutif (avatar foto/inisial, badge status kepegawaian & golongan, pill formasi), 3 kelompok kartu detail tabular, dan footer verifikasi kerahasiaan data.
