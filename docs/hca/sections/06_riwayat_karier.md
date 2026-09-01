# Section 06 — Riwayat Karier

* **Nama Visual**: Timeline Histori Jabatan & Pencapaian
* **Kode Section**: `career`
* **Komponen File**: [TimelineSection.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/TimelineSection.php) & [timeline-section.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/timeline-section.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB Sync)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Assessment Science)

1. **Career Velocity & Trajectory Analysis**:
   * Menilai akselerasi karir (*career velocity*), diversitas pengalaman lintas divisi/sektor (*cross-functional experience*), dan masa jabatan (*tenure*) di setiap pos penugasan.
   * Teori kepemimpinan eksekutif (*McCall, Lombardo, & Morrison Lessons of Experience*) menegaskan bahwa kematangan memimpin lahir dari akumulasi penugasan yang menantang (*stretch assignments*), penanganan krisis operasional, dan pengelolaan tim lintas budaya.

2. **Track Record Verification (Bukti Penguat Hasil Psikotes)**:
   * Menghubungkan potensi laten hasil tes psikologis dengan bukti rekam jejak nyata (*concrete evidence of past achievements*).
   * Memberikan keyakinan bagi dewan suksesi bahwa skor kepemimpinan yang tinggi didukung oleh pencapaian nyata di lapangan (misal: efisiensi anggaran, transformasi digital unit, atau penyelesaian proyek strategis).

3. **Effective Organizational Tenure**:
   * Menghitung total masa pengabdian profesional aktif untuk memastikan kandidat telah memiliki kematangan organisasi yang cukup sebelum diangkat ke jenjang eksekutif puncak.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Tabel Database**: `participant_career_histories`.
* **Model Eloquent**: `App\Models\ParticipantCareerHistory` berelasi dengan `App\Models\Participant::careerHistories()`.
* **Field yang Digunakan**:
  * `position_title`: Nama jabatan/peran yang diemban pada periode tersebut.
  * `company_or_institution`: Nama unit kerja, kedinasan, direktorat, atau perusahaan.
  * `start_year` & `end_year`: Periode tahun mulai hingga selesai (format `YYYY`).
  * `is_current`: Boolean penanda apakah posisi tersebut adalah jabatan aktif saat ini.
  * `achievements`: JSON array berisi butir-butir pencapaian kerja (*key milestones & achievements*).
  * `order_index`: Pengurutan kronologis jabatan (terkini ke terlama).
* **Seeder Generator**: Didukung oleh `Database\Seeders\Support\CareerHistoryGenerator` yang terintegrasi di seeder dinamis.
* **Tampilan UI**: Timeline vertikal asimetris dengan indikator posisi aktif (*live pulse ping*), total masa kerja efektif akumulatif, badge periode tahun, dan daftar poin capaian terstruktur per posisi.
* **Pengelolaan & Penginputan Data (Two-Tier Entry)**:
  * **Tier 1 (Base SPSP)**: Diinput melalui menu Detail Peserta SPSP &rarr; Tab *"Data Pelengkap HCA"* &rarr; Subtab *"2. Riwayat Karier & Rekam Jejak"*.
  * **Tier 2 (In-Context HCA)**: Diedit langsung di HCA Report via tombol topbar *"Kelola Data Pelengkap"* (Modal Drawer `HcaDataEditorModal`). Perubahan tersinkronisasi instan via event `hca-data-updated`.

