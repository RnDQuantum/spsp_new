# Section 23 — Rekomendasi Peran Berikutnya

* **Nama Visual**: Career Pathing & Action Plan Peran Masa Depan
* **Kode Section**: `next_role_rec`
* **Komponen File**: [NextRoleRecommendation.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/NextRoleRecommendation.php) & [next-role-recommendation.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/next-role-recommendation.blade.php)
* **Status Dynamic**: 📋 **PLANNED** (Data SPSP 🔴 New Data Source)

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Assessment Science)

1. **Strategic Mobility & Career Pathing**:
   * Memberikan arahan konkrit mengenai pilihan jalur karir (*career pathing*) yang paling cocok untuk kandidat dalam 1–3 tahun ke depan berdasarkan kecenderungan minat, potensi, dan kompetensi yang dimiliki.
2. **Action Plan & Transition Roadmap**:
   * Menyediakan langkah-langkah aksi strategis (*milestone action plan*) yang perlu ditempuh kandidat sebelum menduduki jabatan target berikutnya.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Rantai Keputusan Talenta (*Talent Progression Chain*)**:
  * Mengambil hasil klasifikasi kuadran dari **Section 16 (Talent 9-Box Matrix)** dan horizon kesiapan dari **Section 17 (Succession Readiness)**.
* **Model Utama**: `App\Models\Participant`, `App\Models\PositionFormation`, `App\Models\FinalAssessment`, `App\Models\ParticipantPerformanceRecord`.
* **Logika Penentuan Peran Rekomendasi**:
  * Jika kandidat berada di kuadran *Star Talent / High Potential* (Box 9/8) & *Horizon 1 Ready Now*: Merekomendasikan promosi langsung ke level jabatan setingkat lebih tinggi (contoh: *Senior Manager / Head of Division / Director*).
  * Jika kandidat berada di *Core Player / High Performer* (Box 5/6) & *Horizon 2 Ready 1-2 Years*: Merekomendasikan pengayaan peran spesialis atau proyek strategis lintas divisi (*Job Enrichment & Cross-Functional Project Lead*).
  * Jika kandidat membutuhkan penguatan kompetensi (Box 1-4/7) & *Horizon 3*: Merekomendasikan konsolidasi peran fungsional dan pendampingan eksekusi kerja (*Performance Stabilization & Mentoring*).
* **Tampilan UI**: Kartu peran target utama, badge status kesiapan, dan roadmap 3 fase transisi (*Fase 1: Transisi & Pendampingan, Fase 2: Rotasi Proyek Lintas Divisi, Fase 3: Kemandirian Penuh*).

