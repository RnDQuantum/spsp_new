# HCA Report — Design Concept & UI Specification

Document ini merinci visual design system, token warna, layout responsive, dan arsitektur komponen Livewire v3 pada **SPSP Human Capital Assessment (HCA) Report**.

---

## 1. Visual Identity & Design Tokens ("Executive Journal")

Untuk mengubah dashboard data generik menjadi laporan eksekutif berstandar majalah/jurnal ilmiah ber-readability tinggi, kita menerapkan tema **"Executive Journal"**.

### Color Palette
- **Dominant UI / Chrome**: Deep Espresso Charcoal (`#171412`) — Struktur layout, header, navigation sidebar, dan teks utama.
- **Accents & Branding**: Amber/Gold (`#b45309`) — Status aktif, angka indeks utama, dan progress ring.
- **Backgrounds**: Warm Ivory Paper (`#faf8f5`) — Latar belakang body untuk mengurangi silau layar dan memberikan tekstur kertas fisik.
- **Card Backgrounds**: Pure White (`#ffffff`) dengan Warm Beige Border (`#f0ebe4`).

### Semantic Color Scale (Data Visualization)
- **Aktual (Actual Rating)**: Forest Green (`#15803d` / `rgba(21, 128, 61, 0.08)`) — Garis solid & soft fill.
- **Standar (Minimum Standard)**: Rust Red/Crimson (`#b91c1c`) — Hard reference line.
- **Toleransi (Tolerance Boundary)**: Slate Gray dashed line (`#94a3b8` / `[4, 4]` dash array).

### Tipografi
- **Headings & Accents**: `Lora` (Google Font) — Serif editorial bermartabat untuk judul utama dan sub-title.
- **Data & UI Labeling**: `Instrument Sans` (Google Font) — Geometric sans-serif bersih untuk chart, tabel, angka, dan navigasi.

---

## 2. Layout & Responsive Modes

### Custom HCA Layout (`hca-layout.blade.php`)
- **Lokasi**: `resources/views/components/layouts/hca-layout.blade.php`
- **Fitur**: Membypass wrapper dashboard default SPSP sehingga menghasilkan kontainer full-bleed grid yang responsif.

### Responsive Modes
1. **Web Interactive View**:
   - Sidebar Table of Contents (TOC) dengan 6 grup menu.
   - Sticky header dengan label section aktif dan tombol trigger "Cetak PDF".
   - Talent Switcher Modal dengan 3-level cascading filter (*Event &rarr; Position &rarr; Participant*).
2. **Cetak PDF (Print Flat View)**:
   - Mode cetak linier di mana seluruh section dirender secara berurutan.
   - `@media print` otomatis menyembunyikan sidebar TOC & header, lalu memicu `window.print()` dengan optimasi page-break.

---

## 3. Technical Architecture & Component Lifecycle

### HTML5 Data Attributes untuk JSON Passing
Menggunakan `data-*` attributes pada HTML element untuk mengirimkan array PHP/Blade ke JavaScript secara aman dari nested quote errors:
```html
<div 
    id="radar-container-{{ $chartId }}"
    data-labels="{{ json_encode($labels) }}"
    data-actual="{{ json_encode($actualRatings) }}"
>
```

### Livewire-Ready Script Execution (IIFE `@script`)
Gunakan Immediately Invoked Function Expression (IIFE) di dalam block `@script` Livewire v3 untuk memastikan re-initialization Chart.js yang aman saat perpindahan section:
```html
@script
<script>
    (function() {
        const chartId = '{{ $chartId }}';
        const ctx = document.getElementById(chartId);
        if (!ctx) return;

        const el = document.getElementById('radar-container-' + chartId);
        const labels = JSON.parse(el.dataset.labels);

        const existingChart = Chart.getChart(ctx);
        if (existingChart) existingChart.destroy();

        new Chart(ctx, { ... });
    })();
</script>
@endscript
```
