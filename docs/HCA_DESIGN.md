# HCA Report — Design Concept & Phase A Implementation Specification

This document details the completed visual design, technical implementation, and system architecture for the SPSP Human Capital Assessment (HCA) Report (Phase A).

---

## 1. Visual Identity & Design Tokens

To elevate the report from a generic data dashboard to a high-readability executive report, we implemented the **"Executive Journal"** theme.

### Color Palette
- **Dominant UI / Chrome**: Slate/Deep Navy (`#0f172a` / `#1e293b`) — Used for structure, headers, and navigation sidebars.
- **Accents & Branding**: Amber/Gold (`#b45309` / `#d97706`) — Used selectively for the cover highlights, active statuses, large index numbers, and the primary radial progress ring.
- **Backgrounds**: Warm Ivory (`#fafaf9` / `#fdfbf7`) — Used for body backgrounds to reduce screen glare and mimic physical paper texture.
- **Card Backgrounds**: Pure White (`#ffffff`) with subtle slate-100 borders (`#f1f5f9`).

### Semantic Color Scale (Data Visualization)
- **Aktual (Actual Score)**: Forest Green (`#15803d` / `rgba(21, 128, 61, 0.08)`) — Solid line & soft background fill.
- **Standar (Minimum Standard)**: Rust Red/Crimson (`#b91c1c`) — Hard line reference.
- **Toleransi (Tolerance Boundary)**: Slate Gray dashed line (`#6b7280` / `[4, 4]` dash array) — Acts as a secondary reference without clashing with the primary data series.

### Typography
- **Headings & Accents**: `Lora` (Google Font) — An elegant, editorial serif used for headers and descriptive sub-titles.
- **Data & UI Labeling**: `Instrument Sans` (Google Font) — A clean, geometric sans-serif used for charts, tables, badges, numbers, and system navigation.

---

## 2. Layout & Shell Design

### Custom HCA Layout (`hca-layout.blade.php`)
To prevent the main application's sidebar and navbar from compressing the report's content width (which caused horizontal scrollbars and scrunched timelines), we created a custom layout:
- **Location**: `resources/views/components/layouts/hca-layout.blade.php`
- **Implementation**: Bypasses the default dashboard wrapper, enabling a full-bleed grid layout that scales fluidly between viewports.

### Responsive Modes
1. **Web Interactive View**:
   - Sidebar Table of Contents (TOC) with grouping ("Pembuka", "Kompetensi & Potensi", etc.) for lazy loading section-by-section.
   - Top sticky header displaying the active section label and a "Cetak PDF" trigger button.
2. **Cetak PDF (Print Flat View)**:
   - Activates a linear, continuous print layout where all completed sections are rendered in sequence.
   - Embedded `@media print` CSS rules automatically hide the sidebar TOC, header controls, and trigger native `window.print()` with page-break optimizations.

---

## 3. Section Component Implementations (Phase A Samples)

We implemented 5 representative section components, validating different data visualization and narrative needs:

### 01 — Cover Page
- **Visuals**: Full-height warm-ivory block with deep navy branding bands, a gold-accented corporate framing, confidential metadata badge, and participant's identification card.

### 04 — Human Capital Index (HCI)
- **Visuals**: Large Gold circular progress ring showing `4.12 out of 5.00` (82.4% progress) alongside a detailed Chart.js 5-pilar Radar Chart showing actual vs. standard vs. tolerance score deviations. Included a tabular fallback for print readability.

### 06 — Riwayat Karier Timeline
- **Visuals**: A vertical chronological timeline with custom navy dot markers, gold connectives, and narrative boxes tracking job history, divisions, and major promotions.

### 15 — Performance Dashboard
- **Visuals**: 5-year KPI trend line chart (Forest Green gradient fill vs. Target dotted line) paired with a divisional performance metric breakdown table (revenue, retention, savings, automation indexes).

### 20 — Kekuatan Psikologis
- **Visuals**: Grid of qualitative cards with left gold borders, showcasing key psychology highlights, descriptions, and assessment evidence.

---

## 4. Technical Architecture & Lifecycle Management

To support robust data transitions and chart animations in a Livewire v3 context, we implemented the following technical patterns:

### HTML5 Data Attributes for JSON Passing
To prevent quotes in JSON array strings from breaking HTML attributes (e.g. `x-data="{ labels: ['a', 'b'] }"` collapsing due to nested double quotes), we bind all arrays to native HTML5 `data-*` attributes:
```html
<div 
    id="radar-container-{{ $chartId }}"
    data-labels="{{ json_encode($labels) }}"
    data-actual="{{ json_encode($actualRatings) }}"
    ...
>
```
Blade automatically escapes these to valid HTML (`&quot;`), and JavaScript reads them seamlessly using `JSON.parse(el.dataset.labels)`.

### Livewire-Ready Script Execution (IIFE `@script`)
Livewire v3 swaps DOM elements dynamically. Standard window-load events or static script tags fail because they evaluate before/after elements are inserted.
We resolve this by using an Immediately Invoked Function Expression (IIFE) inside Livewire's `@script` block. The script executes exactly when the component lands in the DOM:
```html
@script
<script>
    (function() {
        const chartId = '{{ $chartId }}';
        const ctx = document.getElementById(chartId);
        if (!ctx) return;

        const el = document.getElementById('radar-container-' + chartId);
        const labels = JSON.parse(el.dataset.labels);

        // Destroy previous Chart instance if swapping sections
        const existingChart = Chart.getChart(ctx);
        if (existingChart) existingChart.destroy();

        new Chart(ctx, { ... });
    })();
</script>
@endscript
```

---

## 5. Verification & Test Suite

### Automated Feature Tests
- **Test File**: [HcaReportPageTest.php](file:///c:/laragon/www/spsp_new/tests/Feature/Livewire/HcaReportPageTest.php)
- **Coverage**:
  - Demo route rendering and layout validity.
  - Initial state values (defaulting active section to `cover`).
  - Web interactive section switching state changes.
  - Rejection of switching to inactive/locked section codes.
  - Printing mode toggles.

Run command:
```bash
php artisan test --filter=HcaReportPageTest
```
Result: **PASS (5 tests, 15 assertions)**
