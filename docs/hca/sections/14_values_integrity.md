# Section 14 — Values & Integrity

* **Nama Visual**: Evaluasi Integritas & Nilai-Nilai Kerja
* **Kode Section**: `integrity`
* **Komponen File**: [ScoreListSection.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/ScoreListSection.php) & [score-list-section.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/score-list-section.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Psychological Science)

1. **Moral Compass & Ethical Risk Governance**:
   * Mengukur tingkat komitmen moral, kejujuran, transparansi, serta kepatuhan kandidat terhadap standar etika organisasi dan prinsip kejujuran universal.
2. **Organizational Risk Safeguard**:
   * Kandidat dengan integritas tinggi menjadi benteng pertahanan organisasi dari risiko penyalahgunaan wewenang, kecurangan (*fraud*), atau benturan kepentingan.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\SubAspectAssessment`, `App\Models\AspectAssessment`, `App\Models\Participant`.
* **Formula DB**: Agregasi skor sub-aspek *Honesty & Transparency* (Kejujuran, Integritas), *Ethical Compliance* (Kedisiplinan, Sistematika Kerja), *Accountability* (Tanggung Jawab, Komitmen), dan *Consistency & Loyalty* (Loyalitas, Kestabilan Kerja).
* **Tampilan UI**: Daftar baris progres skor 4 dimensi integritas, standar formasi, gap, kesimpulan (*Memenuhi Standar*), dan rata-rata skor integritas.
