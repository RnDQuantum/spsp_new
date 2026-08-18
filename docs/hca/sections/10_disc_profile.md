# Section 10 — DISC Profile

* **Nama Visual**: Grafik Gaya Perilaku Peran DISC
* **Kode Section**: `disc`
* **Komponen File**: [DiscProfile.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/DiscProfile.php) & [disc-profile.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/disc-profile.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Psychological Science)

1. **Behavioral Style & Communication Preference**:
   * Memahami tipe kecenderungan perilaku kandidat dalam berkomunikasi dan berinteraksi di lingkungan kerja: **D**ominance (orientasi hasil & ketegasan), **I**nfluence (orientasi persuasi & antusiasme), **S**teadiness (orientasi konsistensi & dukungan), dan **C**ompliance (orientasi ketelitian & aturan).
2. **Team Compatibility**:
   * Membantu pemetaan keselarasan budaya tim (*team dynamics*) dan gaya manajemen yang paling efektif untuk membimbing kandidat.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\TestResult` (`test_code: D.1` PAPI Kostik / `test_code: G.1` Behavior Tendencies), `App\Models\Participant`.
* **Formula Psikometri**: Pemetaan 20 skala PAPI Kostik (L, P, G untuk D; S, X, V untuk I; K, C, E untuk S; D, R, W untuk C) untuk menentukan gaya dominan kandidat secara objektif.
* **Tampilan UI**: Matriks 2x2 interaktif 4 kuadran DISC dengan penandaan visual emas pada kuadran dominan, lencana gaya dominan di header, serta narasi interpretasi gaya kerja personal.
