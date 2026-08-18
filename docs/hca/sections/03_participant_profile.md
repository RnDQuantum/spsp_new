# Section 03 — Identitas Peserta (Participant Profile)

* **Nama Visual**: Profil Biodata Lengkap Peserta
* **Kode Section**: `participant_id`
* **Komponen File**: [ParticipantProfile.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/ParticipantProfile.php) & [participant-profile.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/participant-profile.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB Sync)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Assessment Science)

1. **Demographic Context & Psychometric Control Variables**:
   * Rincian demografis (jenis kelamin, usia kronologis, tanggal lahir, status pernikahan, pendidikan terakhir) berfungsi sebagai variabel kontrol ilmiah (*moderating variables*) dalam menginterpretasikan dinamika psikologis kandidat.
   * Sebagai contoh, usia dan masa kerja memberikan konteks terhadap kematangan emosional dan stabilitas karir (*career stage theory* Super & Levinson), sedangkan latar belakang pendidikan memvalidasi kesesuaian keahlian teknis (*educational fit*).

2. **Employment Track Record & Organizational Fit**:
   * Memetakan riwayat formal kepegawaian (status kepegawaian PNS/PPPK/BUMN/Swasta, pangkat, golongan, unit kerja, jabatan pelaksana, jabatan fungsional, jabatan struktural, dan pengalaman kerja) untuk memvalidasi rekam jejak linieritas karir.
   * Membantu komite suksesi memverifikasi syarat administratif kenaikan jenjang jabatan sebelum melangkah ke analisis kompetensi.

3. **Person-Job Fit & Target Role Baseline**:
   * Menyelaraskan profil kandidat saat ini dengan formasi jabatan target, level eselon/jenjang struktural, serta minat penempatan yang dinyatakan.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\Participant` (dengan eager loading relasi `positionFormation.template`, `assessmentEvent.institution`, `institution`, `batch`, `finalAssessment`).
* **Pengelompokan 3 Klaster Data**:
  1. **Informasi Pribadi & Kependudukan**:
     * `name`, `gelar_depan`, `gelar_belakang` (diformat secara presisi menghindari duplikasi gelar),
     * `nik`, `test_number`, `skb_number`, `no_kjg`,
     * `tempat_lahir`, `tanggal_lahir` (dengan penghitungan otomatis usia presisi berbasis `Carbon::age`),
     * `gender` (dikonversi dari kode sistem ke label baku "Laki-Laki" / "Perempuan"), `agama`, `status_perkawinan`, `pendidikan`,
     * `email`, `phone` (kontak aktif).
  2. **Profil Kepegawaian & Posisi Saat Ini**:
     * `status_kepegawaian`,
     * `pangkat` & `golongan` (contoh: "Penata Tk. I / Gol. III/d"),
     * `unit_kerja`, `institution.name`,
     * `jabatan_pelaksana`, `jbt_fungsional`, `jbt_struktural`, `pengalaman_kerja`.
  3. **Konteks Asesmen & Formasi Target**:
     * `positionFormation.name` (nama formasi posisi target),
     * `positionFormation.level_jabatan` (level eselon/manajerial target),
     * `minat_penempatan`,
     * `assessmentEvent.name`, `batch.name`,
     * `assessment_date` / `assessmentEvent.start_date`.
