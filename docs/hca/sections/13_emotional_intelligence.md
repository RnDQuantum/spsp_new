# Section 13 — Emotional Intelligence (EQ)

* **Nama Visual**: Profil Kecerdasan Emosional (EQ)
* **Kode Section**: `eq`
* **Komponen File**: [IndexRadarSection.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/IndexRadarSection.php) & [index-radar-section.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/index-radar-section.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic UI Active & DB Sync)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Psychological Science)

1. **Emotional Intelligence Framework (Daniel Goleman)**:
   * Mengukur kematangan emosional dan efektivitas hubungan interpersonal yang menjadi penentu $80-90\%$ keberhasilan kepemimpinan eksekutif (*executive derailment vs success*):
     * **Self Awareness (Kesadaran Diri)**: Mengenali emosi diri sendiri, memahami kekuatan dan keterbatasan pribadi, serta memiliki rasa percaya diri yang realistis.
     * **Self Regulation (Pengendalian Diri)**: Mengelola dorongan emosi impulsif, tetap tenang di bawah tekanan krisis, dan mempertahankan integritas sikap.
     * **Motivation (Motivasi Intrinsik)**: Hasrat berprestasi demi kepuasan berkontribusi dan standar keunggulan, bukan semata imbalan materi eksternal.
     * **Empathy (Empati Sosial)**: Kepekaan memahami perasaan, perspektif, dan dinamika kebutuhan orang lain tanpa harus setuju.
     * **Social Skills (Keterampilan Sosial)**: Kemahiran membangun jejaring, memimpin kolaborasi tim, menyelesaikan konflik, dan menginspirasi orang lain.

2. **Interpersonal Stress Buffer**:
   * EQ tinggi berfungsi sebagai peredam gesekan interpersonal (*interpersonal shock absorber*) saat menghadapi situasi kerja yang sarat konflik dan tekanan politis organisasi.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\AspectAssessment`, `App\Models\SubAspectAssessment`, `App\Models\TestResult` (`test_code: F.1` Emotional Intelligence).
* **Formula & Ekstraksi**:
  * Mengambil nilai rating aspek *Emotional Intelligence* dan sub-aspek 5 domain Goleman.
  * Menghitung nilai komposit indeks kematangan emosional dan persentase kesiapan.
* **Tampilan UI**: Poligon radar chart 5 pilar EQ, score ring indicator, lencana kategori (*Highly Mature / Mature / Developing*), dan tabel perbandingan rating aktual vs standar minimal.
