# Section 14 — Values & Integrity

* **Nama Visual**: Evaluasi Integritas & Nilai-Nilai Kerja
* **Kode Section**: `integrity`
* **Komponen File**: [ScoreListSection.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/ScoreListSection.php) & [score-list-section.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/score-list-section.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB Sync)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Governance Science)

1. **Moral Compass & Ethical Risk Governance**:
   * Mengukur tingkat komitmen moral, kejujuran faktual, transparansi, serta kepatuhan kandidat terhadap standar etika organisasi, tata kelola yang baik (*Good Corporate Governance / GCG*), dan peraturan perundang-undangan.
   * Teori integritas kepemimpinan (*Palanski & Yammarino Behavioral Integrity*) membuktikan bahwa integritas adalah prasyarat utama terciptanya rasa percaya (*organizational trust*) dari bawahan dan pemangku kepentingan.

2. **Organizational Conduct & Fraud Safeguard**:
   * Menjadi sistem penyaring risiko (*conduct risk filter*) untuk melindungi organisasi dari potensi penyalahgunaan wewenang, benturan kepentingan (*conflict of interest*), atau kecurangan (*fraud*).

3. **4 Dimensions of Values & Integrity**:
   * **Honesty & Transparency**: Keterbukaan menyampaikan fakta objektif tanpa distorsi dan keberanian bersikap jujur.
   * **Ethical Compliance**: Ketaatan total terhadap regulasi, SOP, dan kode etik korporasi/organisasi.
   * **Accountability**: Keberanian memikul tanggung jawab penuh atas hasil keputusan dan tidak melempar kesalahan pada pihak lain.
   * **Consistency & Loyalty**: Keselarasan konsisten antara perkataan dan perbuatan di segala situasi serta loyalitas pada visi organisasi.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Konsep Keilmuan**: **Indeks Sintesis Tematik (*Synthesized Meta-Construct*)** — Mengagregasi sub-aspek nilai moral, ketaatan aturan, dan loyalitas kerja ke dalam 4 pilar integritas etika terstandar.
* **Model Utama**: `App\Models\SubAspectAssessment`, `App\Models\AspectAssessment`, `App\Models\Participant`.
* **Formula Pemetaan Sub-Aspek SPSP**:
  1. **Honesty & Transparency**: Rata-rata skor sub-aspek `['Kejujuran', 'Integritas']`.
  2. **Ethical Compliance**: Rata-rata skor sub-aspek `['Kedisiplinan', 'Sistematika Kerja']`.
  3. **Accountability**: Rata-rata skor sub-aspek `['Tanggung Jawab', 'Commitment']`.
  4. **Consistency**: Rata-rata skor sub-aspek `['Loyalitas', 'Kestabilan Kerja']`.
* **Tampilan UI**: Daftar baris progres skor 4 dimensi integritas, standar formasi, gap, kesimpulan evaluasi (*Memenuhi Standar*), badge *Indeks Sintesis Tematik*, serta nilai rata-rata integritas komposit.
