# Section 23 — Rekomendasi Peran Berikutnya

* **Nama Visual**: Career Pathing & Action Plan Peran Masa Depan
* **Kode Section**: `next_role_rec`
* **Komponen File**: [NextRoleRecommendation.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/NextRoleRecommendation.php) & [next-role-recommendation.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/next-role-recommendation.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB Sync)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Succession Planning)

1. **Strategic Mobility & Career Pathing**:
   * Memberikan arahan karir konkrit (*actionable career trajectory*) untuk horizon 1–3 tahun ke depan berdasarkan integrasi profil potensi, kompetensi, dan capaian KPI historis.
   * Menjawab kebutuhan suksesi organisasi dengan merekomendasikan peran definitif yang paling sesuai dengan tingkat kematangan kandidat.

2. **Rantai Keputusan Talenta (*Talent Progression Chain*)**:
   * Menjadi muara akhir dari rangkaian kausal 3 section suksesi:
     ```
     [Section 16: 9-Box Matrix] ──► [Section 17: Succession Horizon] ──► [Section 23: Target Role & 3-Phase Action Plan]
     ```
   * **Jika Box 9/8 (Star / High Potential) & Horizon 1 (Ready Now)**: Direkomendasikan untuk promosi langsung ke level pimpinan setingkat lebih tinggi (Direktur / VP / Senior Executive).
   * **Jika Box 5/6 (Core Player / High Performer) & Horizon 2 (Ready 1–2 Years)**: Direkomendasikan untuk pengayaan peran (*Job Enrichment*), memimpin proyek strategis lintas unit bisnis, dan mentoring intensif.
   * **Jika Box 1–4/7 & Horizon 3**: Direkomendasikan untuk konsolidasi fungsional, pendampingan eksekusi kerja, dan stabilisasi performa.

3. **3-Phase Action Plan Roadmap**:
   * Menyediakan panduan transisi jabatan bertahap:
     * **Fase 1 (Bulan 1–3)**: *Onboarding*, transisi kepemimpinan, dan perumusan target inisiatif kuartalan.
     * **Fase 2 (Bulan 4–6)**: Eksekusi inisiatif lintas fungsi, memimpin satuan tugas, dan rotasi proyek.
     * **Fase 3 (Bulan 7+)**: Kemandirian penuh, akuntabilitas unit kerja, dan pembinaan kader suksesi.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\Participant`, `App\Models\PositionFormation`, `App\Models\FinalAssessment`, `App\Models\ParticipantPerformanceRecord`.
* **Formula Derivasi Peran**:
  * Mengambil nama jabatan saat ini dari `$participant->positionFormation->name`.
  * Memetakan ke level jabatan target setingkat lebih tinggi berdasarkan kata kunci ("Kepala/Head" $\rightarrow$ "Direktur / VP", "Manager" $\rightarrow$ "Senior VP / GM", "Analis/Ahli" $\rightarrow$ "Lead Specialist / Sub-Division Head").
* **Tampilan Visual UI**: Kartu peran target utama di header dengan badge kesiapan suksesi, serta kartu 3 fase transisi berurutan (Bulan 1–3, 4–6, 7+) lengkap dengan rincian tindakan spesifik.
