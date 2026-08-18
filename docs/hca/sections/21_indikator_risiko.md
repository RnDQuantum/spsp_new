# Section 21 — Indikator Risiko

* **Nama Visual**: Indikator Risiko Perilaku & Burnout
* **Kode Section**: `risk_indicators`
* **Komponen File**: [RiskIndicators.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/RiskIndicators.php) & [risk-indicators.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/risk-indicators.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB Sync via MMPI Stress Level)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Risk Governance)

1. **Triangulasi Lensa 3 MMPI: Sistem Peringatan Dini Risiko (*Early Warning System / Derailment Risk*)**:
   * Mengidentifikasi potensi faktor risiko psikologis dan kerentanan perilaku yang dapat memicu kegagalan kepemimpinan (*executive derailment*) atau penurunan produktivitas sebelum masalah tersebut berkembang menjadi krisis nyata.
   * Teori stres kerja (*Karasek Job Demand-Control Model & Maslach Burnout Inventory*) membuktikan bahwa beban kerja tinggi tanpa *support system* yang memadai akan memicu saturasi kejenuhan (*burnout*).

2. **4 Indikator Risiko Utama**:
   * **Saturasi Kejenuhan (Burnout Risk)**: Tingkat kelelahan fisik, emosional, dan mental akibat beban kerja operasional harian yang berkepanjangan.
   * **Kerentanan Stres (Stress Susceptibility)**: Pola respon emosional saat menghadapi tenggat waktu beruntun dan situasi bertekanan tinggi.
   * **Indeks Konflik Interpersonal**: Potensi friksi komunikasi atau resistensi dalam interaksi dengan bawahan maupun rekan sejawat.
   * **Risiko Penurunan Produktivitas**: Proyeksi fluktuasi kinerja kandidat dalam masa transisi organisasi atau perubahan target strategis.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\Mmpi` (`$participant->mmpi`), `App\Models\Participant`.
* **Formula DB**:
  * Mengambil nilai `mmpi.tingkat_stres` ("Rendah", "Sedang", "Tinggi", atau "Moderat").
  * Memetakan tingkat stres ke level risiko keseluruhan (*Overall Risk: Rendah / Sedang / Tinggi*) dan 4 sub-indikator.
* **Tampilan Visual UI**: Header status risiko keseluruhan dengan badge warna (*Hijau / Amber / Merah*), 4 baris indikator risiko dengan progress meter dan deskripsi mitigasi resiko manajerial.
