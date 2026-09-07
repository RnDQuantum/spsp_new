# People/Talent Analytics Platform — Spesifikasi Modul & Arsitektur

## Metadata Dokumen

| Field                  | Nilai                                                                                                                                                                                                                                                                                                                                                       |
| ---------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Jenis dokumen          | Spesifikasi konsep & arsitektur modul (living document)                                                                                                                                                                                                                                                                                                     |
| Status                 | Draft — hasil diskusi eksploratif, belum final                                                                                                                                                                                                                                                                                                              |
| Cakupan                | Konsolidasi dari (1) konsep dashboard "Talent Assessment System" existing, dan (2) modul "HCA Report" (SPSP) yang sudah dikerjakan sebagian                                                                                                                                                                                                                 |
| Cara pakai dokumen ini | Gunakan sebagai referensi tunggal saat membahas pengembangan lanjutan platform ini. Dokumen ini mendefinisikan: daftar modul lengkap dengan kode ID, status tiap modul (sudah ada / sebagian / belum ada), serta aturan kepemilikan data (data ownership) antar modul untuk mencegah duplikasi.                                                             |
| Pemilik produk         | HR Manager (non-technical), dibantu AI sebagai design partner                                                                                                                                                                                                                                                                                               |
| Catatan penting        | Konsep ini dikerjakan tanpa latar belakang formal HR/psikologi — beberapa istilah dan kerangka kerja (9-box, CCL 70-20-10, dsb) mengikuti praktik industri umum, tapi perlu divalidasi oleh psikolog/praktisi HR bersertifikat sebelum dipakai untuk keputusan nyata (promosi, terminasi, dsb), terutama untuk modul yang menyentuh data psikologis klinis. |

---

## 1. Ringkasan Eksekutif

Platform yang dituju adalah **People/Talent Analytics Platform** — sistem untuk melihat sebaran karyawan dari berbagai dimensi (hasil asesmen, KPI, sertifikasi/pelatihan, potensi, kompetensi) secara agregat (populasi) maupun individual (per orang).

Ada dua aset yang sudah dikerjakan secara terpisah dan perlu disatukan:

1. **Dashboard "Talent Assessment System"** (Quantum HRM Internasional) — tampilan level populasi: KPI cards, tren asesmen, sebaran level potensi, top competency, radar kompetensi, piramida talent classification, dan daftar program assessment.
2. **HCA Report** (bagian dari sistem SPSP — Sistem Pemetaan & Statistik Psikologi) — laporan level individu, 24 section, print-ready PDF, menyatukan data psikometri, kompetensi, KPI, riwayat karier, hingga rekomendasi suksesi dalam satu dokumen per karyawan.

Temuan utama: **HCA Report bukan modul yang bersaing dengan modul-modul manajemen data** yang dibahas di dokumen ini — ia adalah **lapisan laporan (report/output layer)**. Namun beberapa bagiannya (KPI, Succession) saat ini punya jalur input data sendiri yang berisiko menjadi sumber data ganda (duplicate source of truth) jika modul manajemen KPI dan Succession yang sesungguhnya dibangun terpisah nanti.

---

## 2. Visi & Tujuan Platform

- Memberi HR Manager satu sistem untuk melihat kondisi talenta dari level populasi (semua karyawan) hingga level individu (satu orang, mendalam).
- Menyatukan empat pilar data: **Assessment/Kompetensi**, **KPI/Kinerja**, **Sertifikasi/Pelatihan**, **Potensi & Kesiapan Suksesi**.
- Setiap domain data punya **satu sumber kebenaran (single source of truth)** — modul laporan/analitik hanya boleh membaca, tidak boleh punya salinan data sendiri yang terpisah.

---

## 3. Master Module List

Setiap modul diberi kode ID unik untuk memudahkan rujukan silang di seluruh dokumen ini dan diskusi berikutnya.

### 3.1 Core Data & Foundation (`CDF`)

| Kode     | Modul                        | Deskripsi                                                                                   |
| -------- | ---------------------------- | ------------------------------------------------------------------------------------------- |
| `CDF-01` | Employee Master Data         | Profil karyawan, struktur organisasi, org chart, status kepegawaian, riwayat jabatan/karier |
| `CDF-02` | Job/Role Profile Library     | Deskripsi jabatan, level, jalur karier per posisi                                           |
| `CDF-03` | Skills & Competency Taxonomy | Daftar baku kompetensi/skill per role (bukan generik)                                       |
| `CDF-04` | Data Integration Hub         | Konektor ke HRIS, payroll, sistem assessment eksternal                                      |

### 3.2 Assessment & Talent Profiling (`ATP`)

| Kode     | Modul                                                   | Deskripsi                                                                                                                               |
| -------- | ------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------- |
| `ATP-01` | Assessment Management                                   | Input/pengelolaan hasil tes psikologi, kompetensi, leadership; histori assessment per karyawan                                          |
| `ATP-02` | Competency Gap Analysis                                 | Radar hasil vs target kompetensi per role                                                                                               |
| `ATP-03` | Talent Mapping / 9-Box Grid (populasi, norm-referenced) | Matrix Potensi × Kompetensi berbasis kurva normal populasi — dipakai untuk ranking rekrutmen/seleksi antar kandidat dalam satu angkatan |
| `ATP-04` | Talent Pool Management                                  | Pengelompokan talenta per kategori/kebutuhan bisnis                                                                                     |

### 3.3 KPI & Performance (`KPI`)

| Kode     | Modul                    | Deskripsi                                                            |
| -------- | ------------------------ | -------------------------------------------------------------------- |
| `KPI-01` | Goal/OKR Management      | Cascading target perusahaan → divisi → individu, realisasi vs target |
| `KPI-02` | Performance Review Cycle | Siklus review (annual/quarterly), form penilaian                     |
| `KPI-03` | Continuous Feedback      | Feedback rutin, bukan cuma periodik                                  |
| `KPI-04` | 360-Degree Feedback      | Feedback dari atasan, rekan, bawahan                                 |
| `KPI-05` | Calibration              | Penyelarasan penilaian antar manajer/divisi                          |

### 3.4 Learning & Certification (`LNC`)

| Kode     | Modul                           | Deskripsi                                        |
| -------- | ------------------------------- | ------------------------------------------------ |
| `LNC-01` | Training & Development Tracking | Status program yang diikuti, jam pelatihan       |
| `LNC-02` | Certification Management        | Sertifikat + expiry tracking, reminder renewal   |
| `LNC-03` | Learning Catalog                | Daftar program tersedia, enrollment self-service |

### 3.5 Development & Career (`DVC`)

| Kode     | Modul                             | Deskripsi                                                                                       |
| -------- | --------------------------------- | ----------------------------------------------------------------------------------------------- |
| `DVC-01` | Individual Development Plan (IDP) | Rencana pengembangan personal berbasis gap, **termasuk tracking realisasi dari waktu ke waktu** |
| `DVC-02` | Career Development Path           | Jalur karier per role                                                                           |
| `DVC-03` | Mentoring Program Tracking        | Pelacakan program mentoring                                                                     |

### 3.6 Succession & Workforce Planning (`SWP`)

| Kode     | Modul                                      | Deskripsi                                                                                                                                                                    |
| -------- | ------------------------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `SWP-01` | Succession Planning (criterion-referenced) | 9-box berbasis ambang kinerja mutlak (KPI aktual × potensi masa depan), horizon kesiapan (Ready Now / 1–2 thn / 2–3 thn), kurasi dewan suksesi, rekomendasi peran berikutnya |
| `SWP-02` | Workforce Planning/Forecasting             | Proyeksi kebutuhan talenta ke depan                                                                                                                                          |
| `SWP-03` | Critical Position Identification           | Identifikasi posisi kunci yang butuh backup plan                                                                                                                             |

### 3.7 Analytics & Reporting (`ANR`)

| Kode     | Modul                             | Deskripsi                                                                    |
| -------- | --------------------------------- | ---------------------------------------------------------------------------- |
| `ANR-01` | Distribution Dashboard (populasi) | Sebaran multi-dimensi seluruh karyawan, filterable                           |
| `ANR-02` | Cross-Analysis                    | Kombinasi antar data (mis. KPI tinggi tapi belum training leadership)        |
| `ANR-03` | Predictive Analytics              | Attrition risk, forecasting kebutuhan talenta                                |
| `ANR-04` | Benchmark & Comparison            | Antar divisi + benchmark industri eksternal                                  |
| `ANR-05` | Custom Report Builder             | Drag-drop, tanpa perlu IT                                                    |
| `ANR-06` | Filter & Drill-Down Global        | Per departemen/level/lokasi, berlaku di semua modul                          |
| `ANR-07` | Individual Deep-Dive Report       | **Ini adalah HCA Report** — laporan 24 section per individu, print-ready PDF |

### 3.8 Psychological Risk & Wellbeing (`PRW`) — _Kategori baru, ditemukan dari HCA Report_

Kategori ini tidak ada di platform generik seperti Visier/SuccessFactors karena mereka tidak menyentuh data psikologis klinis. Ini adalah **kekuatan unik** platform Anda karena berbasis assessment psikologi.

| Kode     | Modul                                 | Deskripsi                                                                                                     |
| -------- | ------------------------------------- | ------------------------------------------------------------------------------------------------------------- |
| `PRW-01` | Mental Health Screening / Gatekeeping | Skrining kelaikan mental (skala validitas), bersifat pass/fail, **tidak dirata-ratakan ke skor talent index** |
| `PRW-02` | Psychological Strengths Profiling     | Pilar keunggulan karakter alami individu                                                                      |
| `PRW-03` | Early Warning System                  | Deteksi risiko burnout, stres kerja, friksi hubungan kerja                                                    |

> ⚠️ **Catatan etik & privasi**: Data di modul ini bersifat sangat sensitif (data psikologis klinis). Perlu kontrol akses (`ADS-02`) paling ketat dibanding modul lain, dan agregasi ke level populasi (jika ada) harus dianonimkan agar tidak bisa ditelusuri balik ke individu tanpa otorisasi.

### 3.9 Administrasi & Sistem (`ADS`)

| Kode     | Modul                               | Deskripsi                                                                                    |
| -------- | ----------------------------------- | -------------------------------------------------------------------------------------------- |
| `ADS-01` | Notification & Alert                | Reminder assessment jatuh tempo, sertifikat expired, review cycle dimulai                    |
| `ADS-02` | Access Control & Role-Based View    | Level akses berbeda: HR Manager (semua), Line Manager (tim sendiri), Employee (diri sendiri) |
| `ADS-03` | Audit Trail                         | Log perubahan data sensitif                                                                  |
| `ADS-04` | Multi-language/Multi-entity Support | Untuk perusahaan multi-cabang/negara                                                         |

---

## 4. Existing Asset: HCA Report (`ANR-07`)

### 4.1 Apa Itu

Modul laporan eksekutif berstandar _Executive Journal_ yang menyatukan data psikometri, kompetensi manajerial, profil perilaku kerja, rekam jejak karier, tren KPI, hingga indikator suksesi kepemimpinan ke dalam satu media visual interaktif sekaligus print-ready.

### 4.2 Prinsip Desain

- **30-Detik C-Level Verdict** — kesimpulan makro actionable di bagian pembuka.
- **Evidence Separation** — narasi strategis di depan, bukti teknis skor instrumen di lampiran (Section 24).
- **Data Immutability** — hasil rating individu bersifat permanen; standar formasi jabatan bersifat fleksibel per institusi.

### 4.3 Arsitektur Teknis (ringkas)

- Stack: Laravel + Livewire 4 + Alpine.js ("Instant SPA" — perpindahan tab <1ms, tanpa roundtrip HTTP).
- Query dimoisasi di memori (`HcaDataService`) agar 24 section tidak menimbulkan query SQL ganda.
- Dua mode tampilan: Web Interactive (sidebar + filter) dan Print PDF (flat, `@media print`).

### 4.4 Struktur 24 Section

| No  | Section                      | Kode HCA                    | Klaster               | Sumber Data (tabel)                                   |
| --- | ---------------------------- | --------------------------- | --------------------- | ----------------------------------------------------- |
| 00  | Active Talent Selector       | `filter`                    | Navigasi              | `Participant`, `AssessmentEvent`, `PositionFormation` |
| 01  | Cover Page                   | `cover`                     | 1. Pembuka            | `Participant`, `PositionFormation`                    |
| 02  | Ringkasan Eksekutif          | `exec_summary`              | 1. Pembuka            | `FinalAssessment`                                     |
| 03  | Identitas Peserta            | `participant_id`            | 1. Pembuka            | `Participant`                                         |
| 04  | Human Capital Index          | `hci`                       | 1. Pembuka            | `AspectAssessment`, `CategoryAssessment`              |
| 05  | Kompetensi Manajerial        | `competency`                | 2. Kapabilitas        | `AspectAssessment`                                    |
| 06  | Riwayat Karier               | `career`                    | 2. Kapabilitas        | `participant_career_histories`                        |
| 07  | Potensi Psikologis           | `potential`                 | 2. Kapabilitas        | `AspectAssessment`                                    |
| 08  | IQ & Kognitif                | `cognitive`                 | 2. Kapabilitas        | `TestResult` (CFIT/IST)                               |
| 09  | Big Five Personality         | `big_five`                  | 3. Kepribadian        | `TestResult` (16PF)                                   |
| 10  | DISC Profile                 | `disc`                      | 3. Kepribadian        | `TestResult` (PAPI Kostik)                            |
| 11  | Learning Agility             | `learning_agility`          | 3. Kepribadian        | `SubAspectAssessment` (sintesis)                      |
| 12  | Leadership Potential         | `leadership_potential`      | 3. Kepribadian        | `SubAspectAssessment` (sintesis)                      |
| 13  | Emotional Intelligence       | `eq`                        | 3. Kepribadian        | `AspectAssessment`                                    |
| 14  | Values & Integrity           | `integrity`                 | 3. Kepribadian        | `SubAspectAssessment` (sintesis)                      |
| 15  | Dashboard KPI                | `performance`               | 4. Kinerja & Suksesi  | `participant_performance_records`                     |
| 16  | 9-Box Matrix (criterion)     | `nine_box`                  | 4. Kinerja & Suksesi  | `FinalAssessment` × KPI                               |
| 17  | Kesiapan Suksesi             | `succession`                | 4. Kinerja & Suksesi  | `PositionFormation`, `participant_personal_profiles`  |
| 18  | Profil Personal              | `personal_profile`          | 5. Kesehatan & Risiko | `participant_personal_profiles`                       |
| 19  | Kesehatan Jiwa               | `mental_health`             | 5. Kesehatan & Risiko | `Mmpi`                                                |
| 20  | Kekuatan Psikologis          | `strengths`                 | 5. Kesehatan & Risiko | Rating tertinggi SPSP + MMPI                          |
| 21  | Indikator Risiko             | `risk_indicators`           | 5. Kesehatan & Risiko | `Mmpi`                                                |
| 22  | Rekomendasi Pengembangan     | `development_rec`           | 6. Rekomendasi        | Gap rating + CCL 70-20-10                             |
| 23  | Rekomendasi Peran Berikutnya | `next_role_rec`             | 6. Rekomendasi        | `PositionFormation`, 9-box, suksesi                   |
| 24  | Lampiran Alat Tes            | `test_instruments_appendix` | 7. Lampiran           | `test_results`                                        |

### 4.5 Metodologi Keilmuan yang Digunakan

- **Sintesis tematik** (Section 11, 12, 14) — indeks dibentuk dari agregasi terbobot sub-aspek yang sudah ada, bukan tes baru.
- **Talent Progression Chain**: Section 16 (diagnosa 9-box) → 17 (horizon kesiapan) → 23 (peran & roadmap 3 fase).
- **Dualitas 9-Box**: model _General Report_ (norm-referenced, potensi × kompetensi, untuk seleksi) vs model _HCA Section 16_ (criterion-referenced, KPI aktual × potensi, ambang mutlak ala McKinsey-GE, untuk executive review).
- **Triangulasi MMPI**: gatekeeper kelaikan mental (19, pass/fail, tidak masuk index) + kekuatan personal (20) + early warning (21).
- **Kerangka CCL 70-20-10** untuk IDP: 70% on-the-job, 20% social learning, 10% formal education.
- **Pemetaan taksonomi**: 16PF → Big Five OCEAN; PAPI Kostik 20 skala → 4 kuadran DISC.

---

## 5. Pemetaan HCA Report → Master Module (Traceability Matrix)

| Section HCA                                                         | Modul Master Terkait         | Status Kepemilikan Data                                                                                                           |
| ------------------------------------------------------------------- | ---------------------------- | --------------------------------------------------------------------------------------------------------------------------------- |
| 01–04 (Cover, Exec Summary, Identitas, HCI)                         | `CDF-01`, `ATP-01`           | ✅ Murni output, aman                                                                                                             |
| 05, 07, 08 (Kompetensi, Potensi, IQ)                                | `ATP-01`, `ATP-02`           | ✅ Murni output, aman                                                                                                             |
| 06 (Karier)                                                         | `CDF-01`                     | ✅ Aman, tapi idealnya jadi sub-entity Employee Master, bukan tabel terpisah di modul assessment                                  |
| 09–14 (Big Five, DISC, Learning Agility, Leadership, EQ, Integrity) | `ATP-01`                     | ✅ Murni output (hasil sintesis dari data assessment)                                                                             |
| **15 (KPI Dashboard)**                                              | `KPI-01`                     | ⚠️ **Berisiko** — data dientri manual ke `participant_performance_records`, belum bersumber dari modul Goal/OKR yang sesungguhnya |
| **16 (9-Box criterion)**                                            | `SWP-01`, `ATP-03`           | ⚠️ **Perlu penamaan jelas** — beda model dengan `ATP-03` (norm-referenced). Jangan disebut "9-box" generik tanpa qualifier        |
| **17 (Succession Readiness)**                                       | `SWP-01`                     | ⚠️ **Berisiko** — kurasi manual via `participant_personal_profiles`, tabel yang sama dipakai Section 18                           |
| 18 (Profil Personal)                                                | `CDF-01` (extension)         | ⚠️ **Tabel bentrok** dengan Section 17 (lihat §6.2)                                                                               |
| 19–21 (Mental Health, Strengths, Risk)                              | `PRW-01`, `PRW-02`, `PRW-03` | ✅ Sudah matang secara konsep, domain baru yang perlu diresmikan jadi modul sendiri                                               |
| **22–23 (Rekomendasi)**                                             | `DVC-01`, `DVC-02`           | ⚠️ Output dihasilkan, tapi **belum ada tracking realisasi** dari waktu ke waktu                                                   |
| 24 (Lampiran Test)                                                  | `ATP-01`                     | ✅ Arsip skor mentah, aman                                                                                                        |

---

## 6. Isu & Risiko Arsitektural yang Teridentifikasi

### 6.1 KPI punya dua jalur input potensial

`participant_performance_records` diisi manual lewat sistem _Two-Tier Hybrid_ HCA (Tier 1: halaman Detail Peserta oleh admin; Tier 2: drawer in-context oleh asesor). Jika `KPI-01` (Goal/OKR Management penuh, dengan cascading target dan approval) dibangun terpisah nanti, akan ada **dua sumber data KPI yang berbeda** untuk konsep yang sama.

**Rekomendasi**: `KPI-01` harus jadi satu-satunya sumber data. Section 15 HCA cukup _membaca_ dari sana. Selama `KPI-01` belum dibangun, `participant_performance_records` boleh berfungsi sebagai staging sementara — tapi ditandai sebagai _migration debt_.

### 6.2 Satu tabel dipakai untuk dua domain berbeda

`participant_personal_profiles` dipakai baik untuk Section 18 (Profil Personal — data pelengkap pribadi) **maupun** Section 17/23 (Kurasi Dewan Suksesi & Peran Target — keputusan strategis). Ini dua domain konseptual berbeda yang bercampur dalam satu tabel.

**Rekomendasi**: Pisahkan jadi dua skema/tabel berbeda — satu untuk data pelengkap personal (`CDF-01` extension), satu untuk kurasi suksesi (`SWP-01`).

### 6.3 Tiga variasi model klasifikasi talenta berpotensi membingungkan

- `ATP-03` — 9-box norm-referenced (populasi, untuk seleksi/rekrutmen)
- `SWP-01` / HCA Section 16 — 9-box criterion-referenced (individu, untuk succession review)
- Dashboard populasi awal — "Sebaran Level Potensi" (donut) & "Talent Classification" (piramida), yang merupakan output visual dari `ATP-03`

**Rekomendasi**: Dokumentasikan dengan tegas kapan model mana dipakai, dan jangan sebut ketiganya dengan istilah generik "9-box" tanpa qualifier (norm-referenced vs criterion-referenced vs population pyramid).

### 6.4 Rekomendasi dibuat, tapi realisasinya tidak dilacak

Section 22 & 23 menghasilkan rekomendasi pengembangan dan peran berikutnya, tapi tidak ada mekanisme melacak apakah rekomendasi tahun lalu sudah dijalankan.

**Rekomendasi**: `DVC-01` harus punya tabel tracking status (belum mulai / sedang berjalan / selesai) yang terhubung ke rekomendasi yang dihasilkan HCA, bukan sekadar generate sekali jalan.

### 6.5 Data psikologis sensitif butuh kontrol akses lebih ketat

Modul `PRW` (khususnya `PRW-01` Mental Health) menyimpan data klinis (skala MMPI). Ini beda tingkat sensitivitasnya dibanding data kompetensi/KPI biasa.

**Rekomendasi**: `ADS-02` (Access Control) harus punya level akses terpisah khusus untuk data `PRW` — kemungkinan hanya psikolog/asesor bersertifikat dan HR Director yang boleh akses detail, sementara line manager biasa hanya boleh lihat flag umum (jika perlu) tanpa detail klinis.

---

## 7. Rekomendasi Arsitektur Sistem (Model Kepemilikan Data 3 Lapis)

**Lapis 1 — Core Data (`CDF`)**: fondasi identitas & struktur organisasi.

**Lapis 2 — Modul Transaksional (`ATP`, `KPI`, `LNC`, `DVC`, `SWP`, `PRW`)**: setiap modul di lapis ini adalah **pemilik tunggal (single source of truth)** untuk domainnya. Semua input, workflow, dan perubahan data terjadi di sini.

**Lapis 3 — Analytics & Reporting (`ANR`, termasuk HCA sebagai `ANR-07`)**: hanya **membaca** dari Lapis 2. Tidak boleh punya tabel data primer untuk domain yang sudah dimiliki modul Lapis 2.

**Aturan wajib**: Jika sebuah modul Lapis 2 belum dibangun tapi Lapis 3 sudah butuh datanya (seperti kondisi HCA saat ini untuk KPI & Succession), data boleh ditampung sementara di Lapis 3 sebagai _staging_, dengan syarat ditandai eksplisit sebagai utang migrasi (_migration debt_) di backlog pengembangan — bukan dianggap solusi permanen.

---

## 8. Rekonsiliasi Data Model

| Domain                        | Tabel Saat Ini (HCA/SPSP)                                | Modul Pemilik Seharusnya | Catatan Migrasi                                                         |
| ----------------------------- | -------------------------------------------------------- | ------------------------ | ----------------------------------------------------------------------- |
| KPI/Performance record        | `participant_performance_records`                        | `KPI-01`                 | Staging sementara; migrasikan skema saat `KPI-01` dibangun              |
| Riwayat karier                | `participant_career_histories`                           | `CDF-01` (sub-entity)    | Idealnya bagian dari Employee Master, bukan modul assessment            |
| Profil personal pelengkap     | `participant_personal_profiles`                          | `CDF-01` (extension)     | Pisahkan dari data kurasi suksesi (lihat baris di bawah)                |
| Kurasi suksesi & peran target | `participant_personal_profiles` (tabel sama dgn di atas) | `SWP-01`                 | ⚠️ Wajib dipisah ke skema/tabel baru khusus suksesi                     |
| Rekomendasi pengembangan      | Dihitung on-the-fly, belum ada tabel tracking terpisah   | `DVC-01`                 | Tambahkan tabel status realisasi (belum/sedang/selesai)                 |
| Data psikometri mentah        | `test_results`, `sub_aspect_assessments`, dll            | `ATP-01`                 | Sudah sesuai, tidak perlu migrasi                                       |
| Data klinis (MMPI)            | `Mmpi`                                                   | `PRW-01/02/03`           | Sudah sesuai secara struktur; perlu penguatan access control (`ADS-02`) |

---

## 9. Roadmap Pengembangan Bertahap

**Fase 1 — Sudah ada / existing assets**
`CDF-01` (sebagian), `ATP-01`, `ATP-02`, `ATP-03`, `LNC-01`, `LNC-02`, `ANR-01`, `ANR-07` (HCA), `PRW-01/02/03` (konsep sudah matang di HCA)

**Fase 2 — Prioritas berikutnya**
`KPI-01` (Goal/OKR Management penuh, menggantikan staging), `SWP-01` (Succession Planning penuh, tabel dipisah dari profil personal), `ANR-06` (Filter & drill-down global), `DVC-01` (IDP dengan tracking realisasi)

**Fase 3 — Pelengkap maturity tinggi**
`KPI-03/04/05` (Continuous feedback, 360, Calibration), `ANR-03` (Predictive analytics), `ANR-04` (Benchmark eksternal), `SWP-02/03` (Workforce planning, critical position), `ADS-01/02/03/04` (sistem administrasi lengkap, terutama access control ketat untuk `PRW`)

---

## 10. Glosarium & Penyamaan Istilah

| Istilah                      | Definisi                                                                                                                 |
| ---------------------------- | ------------------------------------------------------------------------------------------------------------------------ |
| HCI (Human Capital Index)    | Indeks komposit yang merangkum kapabilitas individu, ditampilkan di Section 04 HCA                                       |
| 9-Box (norm-referenced)      | Model `ATP-03` — ranking relatif antar kandidat dalam satu populasi berdasar kurva normal statistik                      |
| 9-Box (criterion-referenced) | Model `SWP-01`/HCA Section 16 — ambang kinerja mutlak institusi, dipakai untuk executive/succession review               |
| Horizon Kesiapan             | Klasifikasi waktu kesiapan suksesi: Ready Now (0–6 bulan), Horizon 2 (1–2 tahun), Horizon 3 (2–3 tahun)                  |
| Talent Progression Chain     | Alur kausal Section 16 → 17 → 23 di HCA: diagnosa → kesiapan → rekomendasi peran                                         |
| CCL 70-20-10                 | Kerangka Center for Creative Leadership untuk IDP: 70% on-the-job, 20% social learning, 10% formal education             |
| Two-Tier Hybrid Entry        | Sistem input data HCA: Tier 1 (halaman Detail Peserta, oleh admin) + Tier 2 (drawer in-context, oleh asesor)             |
| Single Source of Truth       | Prinsip bahwa satu domain data hanya boleh dimiliki/dikelola oleh satu modul                                             |
| Migration Debt               | Data yang untuk sementara "menumpang" di modul lain karena modul pemiliknya belum dibangun, dan wajib dimigrasikan nanti |

---

## 11. Pertanyaan Terbuka / Keputusan yang Perlu Diambil

1. Apakah tabel `participant_personal_profiles` akan dipecah menjadi dua (profil personal vs kurasi suksesi), dan kapan migrasi ini dilakukan?
2. Apakah `participant_performance_records` akan direfactor menjadi bagian dari `KPI-01` yang lebih lengkap (cascading target, approval), atau tetap sederhana sebagai catatan histori saja?
3. Siapa yang berwenang melakukan kurasi suksesi — HR saja, atau perlu melibatkan dewan/komite kalibrasi formal (mirip modul `KPI-05` Calibration di platform enterprise)?
4. Apakah dua model 9-box (norm-referenced dan criterion-referenced) akan ditampilkan sekaligus ke pengguna di UI, atau salah satu dijadikan satu-satunya "model resmi" untuk menghindari kebingungan?
5. Bagaimana data `PRW` (risiko psikologis) diagregasi ke level populasi (misal untuk dashboard `ANR-01`) tanpa melanggar privasi individu — apakah cukup ditampilkan sebagai persentase agregat tanpa identitas, atau perlu ambang minimum jumlah orang sebelum data ditampilkan?
6. Siapa target pengguna akhir tiap role (`ADS-02`) — apakah line manager akan punya akses ke HCA Report bawahannya secara penuh, atau hanya ringkasan tertentu (mengingat sensitivitas data klinis di dalamnya)?

---

## 12. Riwayat Diskusi (Ringkasan Kronologis)

1. Analisis awal dashboard "Talent Assessment System" (Quantum HRM Internasional) — level populasi.
2. Diskusi positioning dashboard sebagai aplikasi HR — kelebihan & kekurangan UX.
3. Eksplorasi aplikasi HR gratis (ternyata kebutuhan sebenarnya adalah BI/analytics tool, bukan HRIS).
4. Klarifikasi kebutuhan: fokus pada modul/fitur people analytics, bukan tool spesifik.
5. Riset dokumentasi platform existing: Visier People, SAP SuccessFactors, Darwinbox — untuk benchmark modul.
6. Cross-check dashboard awal terhadap benchmark — ditemukan gap: KPI/Goal Management, Succession Planning, Filter global.
7. Penyusunan Master Module List pertama kali.
8. Analisis dokumentasi HCA Report (SPSP) — ditemukan bahwa HCA adalah report layer, bukan modul manajemen data berdiri sendiri; ditemukan juga kategori baru (`PRW`) dan beberapa risiko tumpang tindih data.
9. Dokumen ini disusun untuk konsolidasi seluruh temuan di atas.

---

_Dokumen ini bersifat hidup (living document) — perbarui bagian §11 (Pertanyaan Terbuka) begitu keputusan diambil, dan pindahkan poin yang sudah diputuskan ke bagian §6–§8 sebagai catatan final._
