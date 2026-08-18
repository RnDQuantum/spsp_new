# Section 06 — Riwayat Karier

* **Nama Visual**: Timeline Histori Jabatan & Pencapaian
* **Kode Section**: `career`
* **Komponen File**: [TimelineSection.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/TimelineSection.php) & [timeline-section.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/timeline-section.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Assessment Science)

1. **Career Velocity & Trajectory Analysis**:
   * Menganalisis kecepatan promosi, rotasi penugasan lintas unit (*cross-functional experience*), dan masa jabatan (*tenure*) kandidat untuk mengevaluasi kesiapan kepemimpinan berjangka panjang.
2. **Track Record Verification**:
   * Memberikan bukti rekam jejak pencapaian konkret yang mendukung skor potensi psikologis hasil tes.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Tabel Database**: `participant_career_histories`
* **Model Eloquent**: `App\Models\ParticipantCareerHistory` berelasi dengan `App\Models\Participant::careerHistories()`.
* **Field yang Digunakan**: `position_title`, `company_or_institution`, `start_year`, `end_year`, `is_current`, `achievements` (JSON array), `order_index`.
* **Seeder Generator**: `Database\Seeders\Support\CareerHistoryGenerator` yang terintegrasi di `DynamicAssessmentSeeder` dan `ParticipantCareerHistorySeeder`.
* **Tampilan UI**: Timeline vertikal asimetris dengan indikator posisi aktif (*pulse ping*), estimasi total masa kerja efektif, dan rincian poin pencapaian kerja per periode jabatan.
