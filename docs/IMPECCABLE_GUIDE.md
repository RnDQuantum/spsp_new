# Panduan Siklus Penggunaan Impeccable

Dokumen ini menjelaskan alur kerja (*workflow*) lengkap cara menggunakan **Impeccable** untuk mendeteksi anti-pattern desain (*AI slop*), memoles UI secara interaktif dari browser, hingga membersihkan kode sebelum masuk ke tahap produksi (*production/release*).

---

## 1. Tahap Persiapan & Konfigurasi (Setup)

Sebelum menjalankan pemolesan visual, pastikan file spesifikasi desain dan pemetaan tata letak telah dikonfigurasi:

*   **`PRODUCT.md`**: Menentukan target pengguna, karakter brand (misal: *Executive Journal*), prinsip desain, dan kriteria anti-desain (*anti-criteria*).
*   **`DESIGN.md`**: Menentukan palet warna kustom, tipografi, struktur border, dan aturan *Do's & Don'ts*.
*   **.impeccable/design.json**: Sidecar token warna dan komponen visual kustom.
*   **.impeccable/live/config.json**: Pemetaan berkas tata letak (HTML/Blade layout) yang akan diinjeksi skrip pembantu.

---

## 2. Tahap Pengembangan Interaktif (Live Mode)

Tahap ini digunakan untuk memvisualisasikan temuan secara langsung di browser dan memberikan perintah langsung ke AI agent untuk merefaktor kode sumber.

### Langkah 1: Jalankan Server Lokal Impeccable
Jalankan perintah berikut di terminal Anda untuk mengaktifkan server di port `8400` dan menginjeksi tag script pembantu ke berkas tata letak secara otomatis:
```bash
node .agents/skills/impeccable/scripts/live.mjs
```

### Langkah 2: Buka Browser & Refresh Halaman
Buka halaman web yang sedang Anda kembangkan (misalnya `http://127.0.0.1:8000/hca-report-demo`). Anda akan melihat bar hitam/hijau **Impeccable** di bagian bawah halaman.
*   *Catatan*: Jika bar bertuliskan **Agent Disconnected**, lakukan Langkah 3 untuk menghubungkannya.

### Langkah 3: Aktifkan Koneksi Agent (Polling)
Di terminal pendukung AI agent, jalankan skrip polling berikut agar perubahan yang Anda lakukan dari browser langsung diterima oleh AI:
```bash
node .agents/skills/impeccable/scripts/live-poll.mjs
```
*Setelah dijalankan, silakan refresh browser Anda.* Status bar di browser kini akan berubah menjadi hijau (**Agent Connected**).

---

## 3. Cara Menggunakan Fitur di Browser

Setelah terhubung, Anda dapat memoles halaman menggunakan *toolbar* Impeccable:

### A. Fitur Detect (Deteksi Slop)
*   Klik tombol **Detect** pada toolbar.
*   Sistem akan menandai elemen UI yang bermasalah dengan kotak kuning (seperti teks terlalu kecil, kartu bersarang, animasi membal, atau penggunaan warna non-standar).

### B. Fitur Pick & Steer (Modifikasi Kode Instan)
1.  Klik **Pick** di toolbar browser.
2.  Arahkan kursor dan **klik elemen** yang ingin Anda perbaiki (misalnya tombol atau header).
3.  Ketik perintah perubahan dengan bahasa alami pada kolom **Steer...** (misalnya: *"Ubah teks ini agar tidak all-caps dan perbaiki kontrasnya"*).
4.  Tekan **Enter**. AI Agent akan otomatis menerima potongan kode tersebut, memperbaikinya di file proyek, dan browser Anda akan langsung menerapkan perubahan (*Hot Reload*) secara instan!

---

## 4. Tahap Pembersihan (Cleanup & Commit)

Setelah semua tampilan dirasa cukup prima dan bebas dari peringatan slop, lakukan pembersihan script agar berkas tata letak bersih dari kode *development*.

### Langkah 1: Cabut Injeksi Script Tag
Jalankan perintah pencabutan otomatis berikut di terminal proyek:
```bash
node .agents/skills/impeccable/scripts/live-inject.mjs --remove
```
*Skrip ini akan menghapus tag `<script src="http://localhost:8400/live.js"></script>` dari seluruh file layout Blade secara otomatis.*

### Langkah 2: Hentikan Server Lokal
*   Tekan **`Ctrl + C`** pada jendela terminal yang menjalankan `live.mjs` (port 8400) untuk mematikan server pembantu.

### Langkah 3: Verifikasi Tes Akhir
Jalankan pengujian unit lokal untuk memastikan modifikasi tampilan tidak merusak fungsi aplikasi:
```bash
php artisan test
```

### Langkah 4: Git Commit
Sekarang kode Anda sudah bersih dan siap untuk di-commit ke repositori Git:
```bash
git add .
git commit -m "style: visual refinement and slop cleanup using impeccable"
```
