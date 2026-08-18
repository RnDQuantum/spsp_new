# Section 10 — DISC Profile

* **Nama Visual**: Grafik Gaya Perilaku Peran DISC
* **Kode Section**: `disc`
* **Komponen File**: [DiscProfile.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/DiscProfile.php) & [disc-profile.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/disc-profile.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB Sync)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Psychological Science)

1. **Behavioral Style & Communication Tendencies (William Moulton Marston)**:
   * Mengidentifikasi preferensi gaya perilaku kerja sehari-hari dan gaya komunikasi yang muncul secara alami di lingkungan kerja:
     * **Dominance (D)**: Berorientasi pada hasil cepat, kekuasaan, tantangan, ketegasan mengatasi hambatan, dan pengambilan keputusan berani.
     * **Influence (I)**: Berorientasi pada persuasi, hubungan antarpribadi yang hangat, antusiasme, kolaborasi sosial, dan motivasi verbal.
     * **Steadiness (S)**: Berorientasi pada konsistensi, kesabaran, loyalitas tim, stabilitas ritme kerja, dan keharmonisan lingkungan kerja.
     * **Compliance (C)**: Berorientasi pada ketepatan data, kepatuhan prosedur baku (SOP), kontrol kualitas, logika sistematis, dan kehati-hatian risiko.

2. **Team Role Fit & Supervisory Alignment**:
   * Membantu pimpinan organisasi memahami bagaimana cara terbaik mendelegasikan tugas, memberikan umpan balik (*feedback delivery*), dan menempatkan kandidat dalam komposisi tim yang saling melengkapi (*complementary team dynamics*).

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\TestResult` (`test_code: D.1` PAPI Kostik / `test_code: G.1` Behavior Tendencies), `App\Models\Participant`.
* **Formula Pemetaan Psikometri (PAPI Kostik 20 Skala $\rightarrow$ 4 Kuadran DISC)**:
  Skor kebutuhan dan peran PAPI Kostik dikelompokkan ke dalam 4 dimensi:
  1. $\text{Skor D} = L (\text{Leadership}) + P (\text{Need to Control}) + G (\text{Hard Intense Worker})$
  2. $\text{Skor I} = S (\text{Social Extension}) + X (\text{Need to be Noticed}) + V (\text{Vigorous Type})$
  3. $\text{Skor S} = K (\text{Need to be Forceful - Inverted}) + C (\text{Need for Closeness}) + E (\text{Emotional Restraint})$
  4. $\text{Skor C} = D (\text{Interest in Working with Details}) + R (\text{Theoretical Type}) + W (\text{Need for Rules & Supervision})$
* **Penentuan Gaya Dominan**: Nilai kuadran tertinggi (`max(D, I, S, C)`) ditetapkan sebagai gaya perilaku dominan (*Primary Style*).
* **Tampilan Visual UI**: Matriks 2x2 interaktif 4 kuadran DISC dengan penandaan visual emas pada kuadran dominan, lencana gaya dominan di header, dan narasi interpretasi gaya kepemimpinan personal.
