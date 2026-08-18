# Section 21 — Indikator Risiko

* **Nama Visual**: Indikator Risiko Perilaku & Burnout
* **Kode Section**: `risk_indicators`
* **Komponen File**: [RiskIndicators.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/RiskIndicators.php) & [risk-indicators.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/risk-indicators.blade.php)
* **Status Dynamic**: 📋 **PLANNED** (Data SPSP 🔴 New Data Source)

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Assessment Science)

1. **Workplace Risk Early Warning System**:
   * Mengidentifikasi potensi faktor risiko perilaku yang dapat menghambat kinerja eksekutif (misal: *Burnout Risk*, *Resilience Vulnerability*, *Turnover Intention*, atau *Derailment Risk*).
2. **Proactive Intervention**:
   * Memberikan masukan awal bagi manajemen untuk memberikan *support system* atau intervensi kesehatan kerja sebelum masalah berkembang.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Konsep Keilmuan (Lensa 3 Triangulasi MMPI)**: **Sistem Peringatan Dini Risiko (*Early Warning System*)** — Mengidentifikasi potensi gesekan perilaku, kejenuhan (*burnout risk*), dan kerentanan stres kerja untuk intervensi manajerial proaktif.
* **Model Utama**: `App\Models\Mmpi` (`tingkat_stres` & skor klinis), `App\Models\Participant`.
* **Formula DB**: Pemetaan tingkat stres MMPI dan ketahanan kerja instrumen psikometri ke dalam 4 indikator risiko: *Burnout Risk, Stress Susceptibility, Indeks Konflik Interpersonal, dan Risiko Penurunan Produktivitas*.
* **Tampilan UI**: Badge level risiko keseluruhan (*Rendah / Sedang / Tinggi*), baris indikator risiko bergradasi warna, dan deskripsi mitigasi resiko.

