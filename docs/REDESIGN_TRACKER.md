# SPSP Redesign Tracker (Executive Journal Theme)

Dokumen ini melacak kemajuan pengerjaan redesain antarmuka (UI) seluruh sistem SPSP agar selaras dengan tema **"Executive Journal"** yang elegan, profesional, dan berdaya baca tinggi, sesuai dengan yang didefinisikan dalam `DESIGN.md` dan `PRODUCT.md`.

---

## 1. Standar Desain "Executive Journal" SPSP

Setiap halaman yang diredesain wajib mengikuti daftar periksa berikut:
- [ ] **Wadah Utama:** Menggunakan warna latar `bg-warm-ivory` / `dark:bg-[#1f1b18]` dengan pembatas halus `border-warm-border` / `dark:border-[#25211e]` dan bayangan minimalis.
- [ ] **Header Editorial:** Rata kiri, judul menggunakan font serif **Lora** (`font-display`), dan metadata terstruktur dengan warna `text-primary-ink/75` / `dark:text-neutral-400`.
- [ ] **Kepadatan & Kontras Tabel:** 
  - Ukuran teks tabel diset ke **`text-sm` (14px)**.
  - Padding vertikal baris tabel diset ke **`py-2` (8px)**, horizontal **`px-4` (16px)**.
  - Ketebalan font diatur berhierarki (nama aspek: `font-semibold`, data angka/nomor: `font-normal`).
  - Menghapus border hitam tebal, diganti dengan `border-warm-border` (`#f0ebe4`) / `dark:border-[#25211e]`.
  - Latar belakang header kolom dan total menggunakan `bg-warm-ivory` / `dark:bg-[#1f1b18]`.
  - Sel kosong baris total menggunakan netral lembut (`bg-warm-border/40` / `dark:bg-[#25211e]/40`).
- [ ] **Grafik Radar (Spider Plot):** 
  - Memakai skema warna solid asli (Hijau `#5db010`, Merah `#b50505`, Kuning `#fafa05`).
  - Legenda berupa dot lingkaran kecil (`rounded-full w-3 h-3`) dengan kartu hangat (`bg-white dark:bg-[#171412]`).
  - Mengatur *font family* ticks (15px semi-bold) dan pointLabels (14px semi-bold) ke `"'Instrument Sans', sans-serif"`, dengan warna Deep Espresso (`#171412`) / Warm Light (`#e5e5e5`).
  - Toggles JS legenda menggunakan warna warm dark (`dark:bg-[#1f1b18]` & `dark:bg-[#171412]`), bukan abu-abu cold gray (`gray-600`/`gray-800`).
- [ ] **Kartu Metrik & Badge:**
  - Layout section membentang *full width* (`w-full`).
  - Menggunakan warna ivory dengan border beige, aksen angka menggunakan warna Charcoal atau Amber Gold.
  - Batas ukuran font mikrocopy/badge diset ke **`text-xs` (12px)** (tidak diperkenankan menggunakan font sub-12px seperti `text-[10px]`).

---

## 2. Tabel Pelacakan Halaman & Status Redesain

Berikut adalah daftar halaman aplikasi yang perlu diselaraskan dengan standar visual "Executive Journal", dibagi berdasarkan area fitur:

### A. Laporan Individual (Individual Reports)
Menampilkan analisis mendalam dan visualisasi radar kompetensi untuk masing-masing peserta.

| Halaman / Fitur | Berkas View (`resources/views/livewire/pages/`) | Pola Legacy / Masalah Desain | Status |
| :--- | :--- | :--- | :--- |
| **General Mapping** | `individual-report/general-mapping.blade.php` | Menggunakan border hitam kaku, header abu-abu tebal, tabel terlalu renggang. | **Selesai** (Master Benchmark 100%) |
| **Spider Plot Analysis** | `individual-report/spider-plot.blade.php` | Container `bg-white`, header hitam/abu-abu pekat, legenda balok tebal, chart font belum disesuaikan. | **Selesai** |
| **Final Report Summary** | `individual-report/final-report.blade.php` | Desain layout lama dengan borders hitam dan padding lebar. | **Selesai** |
| **General Matching** | `individual-report/general-matching.blade.php` | Tabel gap dan ringkasan nilai masih memakai gaya borders hitam kaku. | **Selesai** |
| **Managerial Competency** | `individual-report/general-mc-mapping.blade.php` | Tabel kompetensi manajerial sangat panjang, memerlukan padding py-2 dan hierarchy tebal-tipis teks. | **Selesai** |
| **Psychology Mapping** | `individual-report/general-psy-mapping.blade.php` | Tabel potensi psikologis, memerlukan border beige lembut dan text-sm. | **Selesai** |
| **Ringkasan Assessment** | `individual-report/ringkasan-assessment.blade.php` | Latar belakang tabel, kartu profil, dan teks deskripsi masih menggunakan tema digital gray standar. | **Selesai** |
| **Ringkasan MC Mapping** | `individual-report/ringkasan-mc-mapping.blade.php` | Visualisasi tabel kompetensi ringkas perlu disesuaikan dengan border beige. | **Selesai** |
| **Interpretation Section** | `individual-report/interpretation-section.blade.php` | Area teks interpretasi naratif memerlukan font display serif Lora dan spasi yang elegan. | **Selesai** |

---

### B. Dasbor Utama & Daftar Peserta
Dasbor koordinasi utama bagi lembaga klien dan manajemen.

| Halaman / Fitur | Berkas View (`resources/views/livewire/pages/`) | Pola Legacy / Masalah Desain | Status |
| :--- | :--- | :--- | :--- |
| **Main Spider Plot Dashboard** | `dashboard.blade.php` | Dasbor utama yang memuat filter event, posisi, dan radar chart. | **Selesai** |
| **Participant Detail** | `participant-detail.blade.php` | Detail profil peserta, riwayat asesmen, dan kartu identitas peserta masih menggunakan layout kaku. | **Selesai** |
| **Talent Shortlist** | `shortlist.blade.php` | Tabel shortlist kandidat dengan kolom-kolom persentase kesesuaian. Memerlukan badge kecil dan border lembut. | **Selesai** |
| **Talent Pool Grid** | `talent-pool/index.blade.php` | Grid matriks 9-box talent pool. Memerlukan penyesuaian warna box agar selaras dengan palet Executive Journal. | **Selesai** |
| **Talent Pool List Modal** | `talent-pool/participant-list-modal.blade.php`| Modal pop-up daftar peserta dalam box talent pool. Memerlukan border beige dan padding yang pas. | **Selesai** |

---

### C. Laporan Kelompok / Agregat (General Reports)
Menampilkan data kompilasi seluruh peserta dalam satu event/posisi.

| Halaman / Fitur | Berkas View (`resources/views/livewire/pages/`) | Pola Legacy / Masalah Desain | Status |
| :--- | :--- | :--- | :--- |
| **Rekap Ranking Assessment**| `general-report/rekap-ranking-assessment.blade.php`| Tabel besar berisi rekap nilai gabungan semua peserta. Perlu diubah ke text-sm, py-2, dan high contrast. | **Selesai** |
| **Standard Psikometrik** | `general-report/standard-psikometrik.blade.php` | Rekapitulasi nilai psikogram agregat. Perlu border warm beige lembut. | **Selesai** |
| **Standard MC** | `general-report/standard-mc.blade.php` | Rekapitulasi nilai kompetensi manajerial agregat. | **Selesai** |
| **Standard MC Copy** | `general-report/standard-mc-copy.blade.php` | Salinan/variasi rekapitulasi MC. | **Selesai** |
| **MMPI Report** | `general-report/mmpi.blade.php` | Rekap hasil tes kepribadian klinis MMPI. | **Selesai** |
| **Event Statistics** | `general-report/statistic.blade.php` | Statistik distribusi nilai, membutuhkan kartu metrik minimalis. | **Selesai** |

---

### D. Sub-Fitur Ranking & Rekomendasi Training
Fitur spesifik untuk membantu penentuan prioritas pengembangan kapasitas.

| Halaman / Fitur | Berkas View (`resources/views/livewire/pages/`) | Pola Legacy / Masalah Desain | Status |
| :--- | :--- | :--- | :--- |
| **Ranking MC Mapping** | `general-report/ranking/ranking-mc-mapping.blade.php`| Daftar ranking berdasarkan skor kompetensi. | **Selesai** |
| **Ranking Psy Mapping** | `general-report/ranking/ranking-psy-mapping.blade.php`| Daftar ranking berdasarkan skor potensi psikologi. | **Selesai** |
| **Capacity Building MC** | `general-report/ranking/capacitybuilding-mc.blade.php`| Tabel analisis kebutuhan pengembangan kompetensi. | **Selesai** |
| **Capacity Building Psy** | `general-report/ranking/capacitybuilding-psy.blade.php`| Tabel analisis kebutuhan pengembangan potensi. | **Selesai** |
| **Training Recommendation** | `general-report/training/training-recommendation.blade.php`| Rekomendasi program pelatihan bagi peserta yang di bawah standar. | **Selesai** |
| **Training Recommendation Alt**| `general-report/training/training-recomendation.blade.php` | Versi alternatif atau cadangan rekomendasi pelatihan. | **Selesai** |
| **Prioritas Perbaikan Atribut**| `general-report/training/prioritas-perbaikan-atribut.blade.php` | Analisis prioritas perbaikan kompetensi. | [ ] Pending |
| **Training Participant Modal**| `general-report/training/attribute-participant-list-modal.blade.php`| Modal popup daftar peserta pelatihan per atribut. | [ ] Pending |

---

### E. Manajemen Administrasi (Admin & Events)
Halaman admin untuk melakukan konfigurasi klien, event, dan template standar.

| Halaman / Fitur | Berkas View (`resources/views/livewire/pages/`) | Pola Legacy / Masalah Desain | Status |
| :--- | :--- | :--- | :--- |
| **Admin Dashboard** | `admin/dashboard-admin.blade.php` | Halaman utama administrator. | [ ] Pending |
| **Client List** | `admin/list-klien.blade.php` | Tabel daftar klien institusi. | [ ] Pending |
| **Event List** | `events/index.blade.php` | Manajemen daftar event penilaian. | [ ] Pending |
| **Event Show / Detail** | `events/show.blade.php` | Detail manajemen peserta per event. | [ ] Pending |
| **Custom Standards Index** | `custom-standards/index.blade.php` | Pengaturan template standar kustom. | [ ] Pending |
| **Custom Standards Create** | `custom-standards/create.blade.php` | Form pembuatan standar baru. | [ ] Pending |
| **Custom Standards Edit** | `custom-standards/edit.blade.php` | Form pengeditan standar. | [ ] Pending |
| **Institution Detail** | `institutions/show.blade.php` | Detail informasi institusi klien. | [ ] Pending |

---

### F. Komponen Layout & Shell (Sidebar, Navbar, & Layouts)
Komponen pembungkus utama aplikasi (app shell) yang merender navigasi samping, atas, dan struktur halaman portal.

| Halaman / Fitur | Berkas View (`resources/views/`) | Pola Legacy / Masalah Desain | Status |
| :--- | :--- | :--- | :--- |
| **Parent Portal Layout** | `components/layouts/app.blade.php` | Struktur pembungkus utama, menggunakan transisi lama dan margin default. | **Selesai** |
| **Sidebar Wrapper** | `components/sidebar/index.blade.php` | Menggunakan background putih/gelap standar (`bg-white`/`dark:bg-neutral-950`) alih-alih Espresso Charcoal (`#171412`). | **Selesai** |
| **Sidebar Brand Logo** | `components/sidebar/brand.blade.php` | Desain logo brand samping. | **Selesai** |
| **Sidebar Menu Item** | `components/sidebar/menu-item.blade.php` | Menggunakan aksen aktif merah (`text-red-600`/`bg-red-50`) alih-alih Espresso Charcoal/Amber Gold. | **Selesai** |
| **Sidebar Dropdown** | `components/sidebar/menu-dropdown.blade.php` | Sub-menu dropdown samping, menggunakan hover/active merah. | **Selesai** |
| **Navigation Bar** | `components/navbar/index.blade.php` | Navigasi atas, menggunakan border standar dan font default. | **Selesai** |
| **Navbar User Menu** | `components/navbar/user-menu.blade.php` | Dropdown menu profil pengguna. | **Selesai** |

---

## 3. Langkah Rencana Kerja

Untuk menyelesaikan redesain di atas secara tertib, kami menyarankan urutan pengerjaan sebagai berikut:
1. **Komponen Layout & Shell (`components/sidebar/*`, `components/navbar/*`):** Karena ini adalah bingkai luar (app shell) yang membungkus semua halaman portal. Menyelaraskan ini terlebih dahulu akan memberi dampak visual instan ke seluruh platform.
2. **Dasbor Utama & Filter (`dashboard.blade.php`):** Pintu masuk utama data setelah login.
2. **Laporan Individual (`individual-report/*`):** Seluruh 9 halaman laporan individual telah **Selesai** diredesain ke standar Executive Journal.
3. **Talent Shortlist & Talent Pool (`shortlist.blade.php`, `talent-pool/*`):** Menyelaraskan grid 9-box dan visualisasinya.
4. **Laporan Kelompok & Agregat (`general-report/*`):** Menyesuaikan tabel agregat besar ke format text-sm dan py-2 yang baru.
5. **Manajemen Admin (`admin/*`, `events/*`, dll.):** Menyelaraskan sisa formulir dan tabel dasar.
