# Dokumentasi Integrasi API Tes Online (Tahap 1: Fondasi Penyimpanan)

> **Versi**: 1.0 (Draft Fondasi)  
> **Terakhir Diperbarui**: 2026-07-08  
> **Tujuan**: Referensi arsitektur penyimpanan data mentah hasil ujian online dari API Quantum HRMI ke sistem SPSP.

---

## 📌 1. Gambaran Umum & Latar Belakang

SPSP merupakan sistem Business Intelligence (BI) yang menganalisis hasil asesmen. Untuk menghubungkan SPSP dengan aplikasi ujian online (**Quantum HRMI API**), kita memerlukan pipa integrasi. 

Karena format data dari API ujian online berisi data mentah dengan berbagai macam struktur dan skala nilai yang berbeda-beda, sistem SPSP **tidak langsung mengonversi data tersebut ke rating 1-5 saat di-import**. 

Sebagai gantinya, kita menerapkan **Opsi A: Simpan Data Mentah Terlebih Dahulu** sebagai fondasi (Single Source of Truth), baru kemudian diproses oleh engine konversi ke rating SPSP pada tahap berikutnya.

---

## 🗄️ 2. Struktur Database

Penyimpanan data menggunakan satu tabel generik bernama `test_results`. Ini meminimalkan perubahan skema database ketika ada penambahan jenis alat tes baru di masa mendatang.

### Skema Tabel `test_results`

| Kolom | Tipe | Nullable | Deskripsi |
| :--- | :--- | :--- | :--- |
| `id` | bigint | No | Primary Key |
| `participant_id` | bigint | No | Foreign Key ke `participants.id` |
| `event_id` | bigint | No | Foreign Key ke `assessment_events.id` |
| `test_code` | varchar(50) | No | Kode unik alat tes dari API (e.g., `A.1`, `B.2`, `D.2`) |
| `test_name` | varchar(255) | No | Nama tampilan alat tes (e.g., "Typical CFIT3A") |
| `test_category` | varchar(100) | No | Kategori kelompok tes (e.g., "Kecerdasan / IQ") |
| `status` | varchar(20) | No | Status pelaksanaan tes (`completed` / `incomplete`) |
| `test_started_at` | timestamp | Yes | Waktu peserta mulai mengerjakan tes |
| `summary_data` | json | No | Skor akhir kuantitatif/numerik hasil parser |
| `interpretation_data` | json | Yes | Teks interpretasi deskriptif & saran pengembangan |
| `raw_response` | json | No | Backup respons asli API (berguna untuk audit trail) |
| `conversion_status` | enum | No | Status konversi ke rating SPSP (`pending`, `converted`, `skipped`, `not_applicable`) |
| `converted_at` | timestamp | Yes | Waktu ketika data sukses dikonversi ke rating SPSP |
| `created_at` / `updated_at` | timestamp | Yes | Audit timestamps |

**Aturan Integritas Data (Constraints):**
1. **Unique Constraint (`participant_id`, `event_id`, `test_code`)**: Mencegah adanya data duplikat untuk satu peserta pada alat tes yang sama di event tertentu. Operasi bersifat *idempotent* (menggunakan `updateOrCreate`).
2. **Index `(event_id, test_code)`**: Mempercepat proses penarikan data per kelompok tes dalam satu event.
3. **Index `conversion_status`**: Mempercepat pencarian data antrean konversi yang statusnya masih `pending`.

---

## ⚙️ 3. Logika Parsing & Penanganan Alat Tes (In-Scope)

Sistem memproses **9 alat tes** yang memiliki data dari API dengan aturan pemisahan data sebagai berikut:

### A. Pembagian Kolom JSON

*   **`summary_data`**: Menyimpan hanya parameter numerik/skor akhir penting yang nantinya akan dibaca oleh service konversi rating.
*   **`interpretation_data`**: Menyimpan narasi interpretasi psikologis dan saran pengembangan (jika disediakan oleh instrumen).
*   **`raw_response`**: Salinan respons asli dari API (minus field administrasi seperti `status` dan `nama_alat_tes` agar tidak mubazir, serta pengecualian khusus di bawah ini).

### B. Aturan Khusus per Alat Tes

#### 1. Kraeplin (`D.2`) — Sikap Kerja
> [!WARNING]
> Respons Kraeplin dari API menyertakan field `detail` berisi data log jawaban per soal yang sangat besar (bisa mencapai 200KB+ per peserta).
> **Aturan**: Field `detail` **wajib dihapus (di-strip)** dari respons sebelum disimpan ke database guna menghindari pembengkakan ukuran database (Database Bloat). Data yang disimpan di `summary_data` cukup rangkuman nilai akhir (`kesimpulan`, `kesimpulan_akhir`, `persamaan_*`, dan `skor_*`).

#### 2. MMPI 180 (`E.1`) & MMPI 567 (`E.2`) — Tes Klinis
> [!IMPORTANT]
> Kedua alat tes ini **dikecualikan (di-skip)** dari impor ke tabel `test_results`. Data MMPI memiliki tabel khusus tersendiri di SPSP yaitu `psychological_tests` dan tidak dikirimkan melalui jalur pipa API ini.

#### 3. Ringkasan Mapping Impor 9 Alat Tes:

| Kode | Nama Alat Tes | Kategori | Skor di `summary_data` | Sudah Rating 1-5? |
| :--- | :--- | :--- | :--- | :--- |
| **A.1** | Typical CFIT3A | Kecerdasan / IQ | IQ, Kategori, hasil 4 sub-tes | ✅ Ya (sub-tes skala 1-5) |
| **A.2** | Typical CFIT3B | Kecerdasan / IQ | IQ, Kategori, hasil 4 sub-tes | ✅ Ya (sub-tes skala 1-5) |
| **A.5** | Typical IST | Kecerdasan / IQ | IQ, Kategori, 9 sub-tes (`label_values`) | ❌ Tidak (Skala IQ & skor mentah) |
| **B.1** | KOMPETENSI KARAKTER | Kepribadian / Karakter | 20 skor `hasil_*` (A-Z) | ✅ Ya (Skala 1-5) |
| **B.2** | Typical 16PF | Kepribadian / Psikometri | 16 skor `nilaiAspek` | ❌ Tidak (Skala sten 1-10) |
| **D.2** | Typical Kraeplin | Sikap Kerja | `kesimpulan_akhir` (rating 1-4 per jenjang), `kesimpulan` (panker, janker, dll) | ⚠️ Parsial (Skala 1-4) |
| **F.1** | Typical EQ | Kecerdasan Emosional | `hasil_akhir` (rating 1-4 per 14 skala), `skor_akhir` | ⚠️ Parsial (Skala 1-4) |
| **G.1** | Typical Behavior Tendencies | Kecenderungan Perilaku | `iman`, `pikiran`, `perasaan`, `hasil_kecenderungan` | ❌ Tidak (Skor absolut & label) |
| **H.1** | RMIB | Minat Jabatan | `nilai_1/2/3`, `nilai` (ranking) | ❌ Tidak (Kategoris) |

---

## 🛠️ 4. Penggunaan Command CLI (Testing & Impor Manual)

Untuk membantu proses development dan verifikasi data hasil analisis API, telah disediakan Artisan Command:

### A. Perintah Dasar
```bash
php artisan test-results:import --dir="<path_folder_json>" --event=<id> --participant=<id>
```
*   `--dir`: Path ke folder yang berisi file-file JSON per alat tes (hasil output script analisis Python).
*   `--event`: ID event target di tabel `assessment_events`.
*   `--participant`: ID peserta target di tabel `participants`.

### B. Opsi Tambahan

#### 1. Dry Run (Simulasi tanpa menyimpan ke DB)
Tambahkan flag `--dry-run` untuk melihat data apa saja yang terdeteksi, kategori mapping-nya, dan status apakah alat tes tersebut akan di-impor atau di-skip:
```bash
php artisan test-results:import --dir="D:\sample_json" --event=1 --participant=15436 --dry-run
```

#### 2. Impor dari Satu File JSON
Gunakan opsi `--file` jika Anda hanya ingin meng-impor satu file alat tes tertentu:
```bash
php artisan test-results:import --file="D:\sample_json\A.1.json" --event=1 --participant=15436
```

---

## 🔮 5. Aliran Pengembangan Selanjutnya (Tahap 2)

Setelah fondasi penyimpanan data mentah ini selesai, langkah selanjutnya adalah membangun **Service Konversi Rating**:

```
[test_results] (conversion_status = pending)
       │
       ▼ (Dibaca oleh Converter Service)
[Normalisasi Nilai]
 ├─ CFIT / B.1   --> Pakai nilai rating asli (1-5)
 ├─ 16PF (sten)  --> Konversi skala 1-10 ke 1-5
 ├─ IST (IQ)     --> Konversi rentang IQ ke rating 1-5
 ├─ Kraeplin/EQ  --> Konversi skala 1-4 ke 1-5
 └─ Behavior/RMIB--> Di-skip (not_applicable) atau diolah khusus
       │
       ▼ (Disimpan)
[sub_aspect_assessments] / [aspect_assessments] (SPSP Existing)
```

Dengan arsitektur dua tahap ini, apabila rumus konversi nilai di kemudian hari berubah, kita cukup mengubah formula konversi di SPSP dan menjalankan ulang perintah rekalkulasi tanpa perlu memanggil ulang API Quantum HRMI.
