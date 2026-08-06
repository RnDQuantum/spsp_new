# Section 03 — Identitas Peserta (Participant Profile)

* **Nama Visual**: Profil Biodata Lengkap Peserta
* **Kode Section**: `participant_id`
* **Komponen File**: [ParticipantProfile.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/ParticipantProfile.php) & [participant-profile.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/participant-profile.blade.php)
* **Status Dynamic**: ✅ **DONE** (Data SPSP 🟢 Reuse)

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Assessment Science)

1. **Comprehensive Demographic & Administrative Context**:
   * Menyediakan rincian demografis (jenis kelamin, usia, tingkat pendidikan, kontak, dan riwayat pekerjaan) yang berfungsi sebagai variabel kontrol dalam menginterpretasikan hasil asesmen psikologis.
2. **Role Fit Baseline**:
   * Menghubungkan latar belakang akademik dan formasi jabatan yang dilamar/diduduki saat ini sebagai konteks analisis keselarasan peran (*person-job fit*).

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\Participant` (dengan relasi `positionFormation`, `institution`, `assessmentEvent`).
* **Field DB yang Dipakai**: `name`, `test_number`, `nip_nik`, `email`, `phone`, `gender`, `birth_place`, `birth_date`, `last_education`, `major`, `current_position`, `position_formation_id`.
