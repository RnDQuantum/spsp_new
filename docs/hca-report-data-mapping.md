# HCA Report — Data & Component Mapping (untuk BE Integration)

> Dokumen ini adalah referensi internal tim BE, terpisah dari `hca-report-design-spec.md` (yang ditujukan untuk AI agent pembangun UI). Isinya: section HCA Report dipetakan ke sumber data/komponen existing di SPSP, status ketersediaan, dan pekerjaan yang dibutuhkan untuk wiring data asli. Selama fase desain, semua section tetap pakai data statis — dokumen ini disiapkan untuk fase integrasi berikutnya.

## Legenda status

- 🟢 **Reuse** — komponen/data sudah ada, tinggal dipanggil ulang (kemungkinan perlu restyle tampilan saja)
- 🟡 **Partial** — data induk/aspek sudah ada, tapi breakdown/agregasi yang dibutuhkan HCA belum ada
- 🔴 **New** — sumber data belum ada sama sekali di SPSP, butuh instrumen/tabel/integrasi baru

---

## 🟢 Reuse

| Section              | Sumber existing                                                                | Catatan                                                                                                                                                                                      |
| -------------------- | ------------------------------------------------------------------------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Identitas Peserta    | Biodata section pada blade `Laporan Individu` (`$participant`)                 | Field sudah lengkap: nomor tes, SKB, nama, email, telepon, gender, formasi jabatan, tanggal asesmen, foto                                                                                    |
| Layer 1 — Kompetensi | `general-mc-mapping` / `general-matching` (`showKompetensi=true`)              | Tinggal panggil component dengan props kompetensi-only, sama seperti pola di `final_report`                                                                                                  |
| Layer 2 — Potensi    | `general-psy-mapping` (`showPotensi=true`)                                     | Sama, tinggal panggil dengan props potensi-only                                                                                                                                              |
| IQ & Profil Kognitif | Sub-aspect dari `general-psy-mapping`, kemungkinan di bawah aspek "Daya Pikir" | **Perlu verifikasi**: cek apakah nama sub-aspek di template sekarang (Analytical Thinking, Numerical, Verbal, Abstract, Spatial) sudah persis ada, atau perlu relabeling di layer presentasi |

## 🟡 Partial — data induk ada, breakdown/agregasi belum

| Section                        | Data induk yang sudah ada                                                                                                                                                 | Yang perlu ditambahkan                                                                                                                                                                                                                          |
| ------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Ringkasan Eksekutif            | Skor per-aspek dari aspect_assessments                                                                                                                                    | Formula agregasi 5 pilar (Competency/Potential/Performance/Leadership/Integrity) → 1 Talent Index, + mapping angka ke kategori kualitatif ("High Potential", dst)                                                                               |
| Human Capital Index (HCI)      | Sama seperti di atas                                                                                                                                                      | Sama — kemungkinan bisa 1 service yang dipakai bersama Ringkasan Eksekutif & HCI agar konsisten                                                                                                                                                 |
| Learning Agility               | Aspek "Learning Agility" (di kategori Potensi)                                                                                                                            | Perlu 4 sub-aspek baru: Mental/People/Change/Result Agility — cek apakah bisa jadi sub_aspects baru di bawah aspect existing, atau butuh aspect terpisah                                                                                        |
| Leadership Potential           | Aspek "Leadership Potential"                                                                                                                                              | Perlu breakdown 6 sub-aspek: visioning, decision making, influence, execution, coaching & developing, strategic thinking                                                                                                                        |
| Emotional Intelligence (EQ)    | Aspek "Emotional Intelligence"                                                                                                                                            | Perlu breakdown 5 sub-aspek: self awareness, self regulation, social skills, empathy, motivation                                                                                                                                                |
| Values & Integrity             | Aspek "Integritas"                                                                                                                                                        | Perlu breakdown 5 sub-aspek: honesty, ethics, accountability, compliance, consistency                                                                                                                                                           |
| Kesehatan Jiwa (Mental Health) | `$psychologicalTest` sudah punya field: `validitas`, `internal`, `interpersonal`, `kap_kerja`, `klinik`, `kesimpulan`, `psikogram_formatted`, `nilai_pq`, `tingkat_stres` | Field-field ini berupa **teks kualitatif**, HCA butuh representasi numerik (0-100) untuk gauge/index — perlu mapping/scoring dari teks ke angka, atau ubah pendekatan visual HCA agar mengakomodasi teks langsung tanpa konversi paksa ke angka |
| Kekuatan Psikologis            | Field `internal`/`interpersonal` di `$psychologicalTest`                                                                                                                  | Perlu ekstraksi jadi 4-6 poin ringkas (saat ini bentuknya paragraf bebas, bukan list terstruktur)                                                                                                                                               |
| Rekomendasi Pengembangan       | Fitur "Training Recommendation" sudah ada di `general_report`                                                                                                             | Perlu adaptasi output existing ke format HCA (kekuatan vs area pengembangan side-by-side)                                                                                                                                                       |

## 🔴 New — belum ada sumber data sama sekali

| Section                                    | Catatan                                                                                                                                                                                                                                                               |
| ------------------------------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Riwayat Karier                             | Ini data histori jabatan/karir (bukan hasil tes psikologi) — kemungkinan perlu integrasi ke sistem HRIS instansi klien, di luar cakupan SPSP saat ini. Perlu diklarifikasi: apakah field ini akan diinput manual per institusi, atau memang butuh integrasi eksternal |
| Big Five Personality                       | Instrumen psikometri terpisah (model OCEAN), belum ada di struktur 13 aspek saat ini. Perlu keputusan: tambah sebagai CategoryType baru, atau tetap di luar struktur assessment_templates yang ada                                                                    |
| DISC Profile                               | Instrumen terpisah juga (4 dimensi D/I/S/C) — sama seperti Big Five, perlu keputusan struktur data                                                                                                                                                                    |
| Layer 3 — Performance Dashboard            | Ini data kinerja kerja aktual (KPI, revenue growth, dll), bukan hasil assessment psikologi — kemungkinan perlu integrasi ke sistem performance management terpisah                                                                                                    |
| Talent 9-Box Matrix                        | Gabungan Potensi (sudah ada) × Performance (belum ada, lihat baris di atas) — begitu Performance tersedia, ini tinggal kombinasi logic, bukan sumber data baru tersendiri                                                                                             |
| Succession Readiness                       | Perlu business rule/model skoring readiness baru — belum ada logic ini di SPSP                                                                                                                                                                                        |
| Profil Personal (zodiak/shio/weton/hobi)   | Field baru yang trivial secara teknis, tapi perlu diklarifikasi dulu ke product owner apakah section ini memang tetap dipertahankan untuk rilis (mengingat sifatnya non-formal)                                                                                       |
| Indikator Risiko (Burnout, Depresi, dll)   | Instrumen risiko klinis numerik terpisah — kemungkinan perlu instrumen psikologi tambahan yang belum diakomodasi struktur assessment_templates saat ini                                                                                                               |
| Rekomendasi Peran Berikutnya + Action Plan | Perlu definisi career pathing/jenjang jabatan per posisi — logic ini belum ada, butuh input business rule dari HR/product                                                                                                                                             |

---

## Rekomendasi urutan pengerjaan integrasi

1. **Tahap 1 (cepat):** Identitas Peserta, Layer 1 Kompetensi, Layer 2 Potensi, IQ/Kognitif — tinggal reuse component existing dengan props berbeda.
2. **Tahap 2 (butuh kerja backend sedang):** Ringkasan Eksekutif, HCI, Learning Agility, Leadership Potential, EQ, Values & Integrity, Kesehatan Jiwa, Kekuatan Psikologis, Rekomendasi Pengembangan — data induk sudah ada, perlu service/breakdown tambahan.
3. **Tahap 3 (butuh keputusan produk dulu, baru bisa dikerjakan):** Riwayat Karier, Big Five, DISC, Performance Dashboard, 9-Box, Succession Readiness, Profil Personal, Indikator Risiko, Rekomendasi Peran Berikutnya — perlu keputusan sumber data/instrumen sebelum ada pekerjaan teknis yang bisa dimulai.

## Pertanyaan terbuka untuk product owner

- Apakah Big Five & DISC akan jadi instrumen tes baru yang dikembangkan sendiri, atau integrasi ke alat tes pihak ketiga yang sudah ada?
- Apakah data Performance (KPI, revenue, dll) akan diinput manual oleh admin instansi, atau perlu integrasi ke sistem HR performance existing klien?
- Apakah Profil Personal (zodiak, dll) tetap masuk cakupan rilis pertama, mengingat sifatnya non-esensial?
- Siapa yang menentukan business rule untuk Succession Readiness dan Career Pathing (Rekomendasi Peran Berikutnya)?
