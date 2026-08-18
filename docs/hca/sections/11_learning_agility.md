# Section 11 — Learning Agility

* **Nama Visual**: Ketangkasan Belajar & Adaptabilitas (Learning Agility)
* **Kode Section**: `learning_agility`
* **Komponen File**: [ScoreListSection.php](file:///c:/laragon/www/spsp_new/app/Livewire/Pages/HCA/Sections/ScoreListSection.php) & [score-list-section.blade.php](file:///c:/laragon/www/spsp_new/resources/views/livewire/pages/h-c-a/sections/score-list-section.blade.php)
* **Status Dynamic**: ✅ **DONE (Dynamic DB Sync)**

---

## 🧬 Tujuan & Maksud Keilmuan (HR & Psychological Science)

1. **VUCA World Readiness & Learnability (Lombardo & Eichinger / Center for Creative Leadership)**:
   * *Learning Agility* didefinisikan sebagai kemampuan dan kemauan seseorang untuk secara cepat belajar dari pengalaman terdahulu, lalu menerapkannya secara sukses dalam kondisi baru yang belum pernah dialami sebelumnya (*unfamiliar or first-time situations*).
   * Menjadi faktor pembeda utama (*key differentiator*) antara individu yang mampu bertransisi menjadi pemimpin eksekutif hebat vs individu yang mandek (*stalled career*) saat menghadapi disrupsi industri.

2. **4 Dimensions of Agility**:
   * **Mental Agility**: Berpikir kritis, nyaman dengan ambiguitas, mampu menyederhanakan masalah rumit, dan menemukan korelasi baru yang tidak terpikirkan orang lain.
   * **People Agility**: Kepekaan membaca dinamika sosial, terbuka terhadap masukan konstruktif, dan mampu menggerakkan orang dari berbagai latar belakang budaya.
   * **Change Agility**: Kesiapan bereksperimen dengan pendekatan baru, tidak terjebak zona nyaman, dan memimpin inisiatif transformasi.
   * **Result Agility**: Ketangguhan memberikan hasil prima dalam situasi transisi atau tekanan tenggat waktu yang ketat.

3. **Konsep Keilmuan: Indeks Sintesis Tematik (*Synthesized Meta-Construct*)**:
   * Section ini **bukan instrumen tes terpisah yang membebani peserta**, melainkan sintesis terbobot dari sub-aspek kompetensi dan potensi psikologis yang sudah diukur di SPSP.

---

## 📊 Sumber Data DB SPSP & Logic Calculation

* **Model Utama**: `App\Models\SubAspectAssessment`, `App\Models\AspectAssessment`, `App\Models\Participant`.
* **Formula Pemetaan Sub-Aspek SPSP ke 4 Dimensi Agility**:
  1. **Mental Agility**: Rata-rata skor sub-aspek `['Daya Analisa', 'Logika Berpikir', 'Kreativitas', 'Daya Abstraksi']`.
  2. **People Agility**: Rata-rata skor sub-aspek `['Sosualitas', 'Komunikasi Sosial', 'Kontak Sosial', 'Kepekaan Interpersonal']`.
  3. **Change Agility**: Rata-rata skor sub-aspek `['Penyesuaian Diri', 'Agen Perubahan', 'Mobilitas', 'Inisiatif']`.
  4. **Result Agility**: Rata-rata skor sub-aspek `['Hasrat Berprestasi', 'Daya Tahan Kerja', 'Semangat Kerja', 'Result Focus']`.
* **Tampilan UI**: Daftar baris progres skor 4 pilar agility, standar formasi minimal, nilai gap deviasi, kesimpulan evaluasi, badge konteks *Indeks Sintesis Tematik*, serta nilai rata-rata agility komposit.
