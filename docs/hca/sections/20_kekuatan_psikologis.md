# Section 20 — Kekuatan Psikologis (Key Strengths)

* **Nama Visual**: Kekuatan Utama & Karakter Dominan
* **Kode Section**: `strengths`
* **Komponen File**: [QualitativeListSection.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/QualitativeListSection.php) & [qualitative-list-section.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/qualitative-list-section.blade.php)
* **Status Dynamic**: 🟨 **ACTIVE (UI Ready / Qualitative Dataset)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Positive Psychology)

1. **Triangulasi Lensa 2 MMPI: Lensa Positif & Kekuatan Perilaku (*Strength-Based Development*)**:
   * Aliran psikologi positif (*Seligman & Clifton StrengthsFinder*) menekankan bahwa efektivitas kepemimpinan tertinggi dicapai dengan mengkapitalisasi dan melipatgandakan kekuatan utama (*multiplying signature strengths*), bukan sekadar memperbaiki kelemahan minor.
   * Mengekstraksi keunggulan personal dan karakter unggul kandidat yang terbukti dari hasil asesmen psikologis untuk dioptimalkan dalam penugasan proyek-proyek strategis organisasi.

2. **5 Klaster Kekuatan Eksekutif**:
   * **Mental Toughness & Resilience**: Ketenangan dan fokus tinggi dalam memimpin tim keluar dari krisis operasional.
   * **Strategic Visioning**: Ketajaman membaca peluang masa depan dan arah jangka panjang.
   * **Cognitive & Learning Agility**: Kecepatan menyerap domain bisnis baru dan teknologi disrupsi.
   * **Strategic Influence**: Kemahiran artikulasi dan persuasi dalam menyederhanakan data analitik yang rumit.
   * **Core Values & Ethics**: Komitmen tanpa kompromi terhadap integritas dan kode etik.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: Ekstraksi dari deskripsi kualitatif `App\Models\Mmpi` (field `internal` & `interpersonal`) serta aspek rating tertinggi pada `AspectAssessment`.
* **Tampilan Visual UI**: Daftar kartu-kartu kekuatan modular dengan tag tematik (*Mental Toughness, Leadership, Cognitive Agility, Interpersonal, Core Values*) dan penjelasan perilaku kerja konkret.
