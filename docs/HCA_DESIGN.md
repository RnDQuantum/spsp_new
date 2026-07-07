# HCA Report — Design Concept & Phase A Implementation Specification

This document details the completed visual design, technical implementation, and system architecture for the SPSP Human Capital Assessment (HCA) Report (Phase A).

---

## 1. Visual Identity & Design Tokens

To elevate the report from a generic data dashboard to a high-readability executive report, we implemented the **"Executive Journal"** theme.

### Color Palette
- **Dominant UI / Chrome**: Deep Espresso Charcoal (`#171412`) — Used for structure, headers, navigation sidebars, and primary text.
- **Accents & Branding**: Amber/Gold (`#b45309`) — Used selectively for active statuses, large index numbers, and the primary radial progress ring.
- **Backgrounds**: Warm Ivory Paper (`#faf8f5`) — Used for body backgrounds to reduce screen glare and mimic physical paper texture.
- **Card Backgrounds**: Pure White (`#ffffff`) with custom Warm Beige Border (`#f0ebe4`).

### Semantic Color Scale (Data Visualization)
- **Aktual (Actual Score)**: Forest Green (`#15803d` / `rgba(21, 128, 61, 0.08)`) — Solid line & soft background fill.
- **Standar (Minimum Standard)**: Rust Red/Crimson (`#b91c1c`) — Hard line reference.
- **Toleransi (Tolerance Boundary)**: Slate Gray dashed line (`#94a3b8` / `[4, 4]` dash array) — Acts as a secondary reference without clashing with the primary data series.

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

We implemented 5 representative section components, validating different data visualization and narrative needs (all optimized at `max-w-5xl` container width):

### 01 — Cover Page
- **Visuals**: Centered warm-ivory page sheet with subtle Amber Gold branding accents, sentence-case metadata details, and a flat border-t/b candidate profile details area to avoid nested cards.

### 04 — Human Capital Index (HCI)
- **Visuals**: Large Gold circular progress ring showing `4.12 out of 5.00` (82.4% progress) alongside a borderless radar chart with expanded padding (p-8) for readability, and a flat tabular breakdown with top/bottom border dividers.

### 06 — Riwayat Karier Timeline
- **Visuals**: A vertical chronological timeline with a custom active Amber Gold pulse dot marker, neutral-colored historical timeline markers, Title Case department labels, and achievements list.

### 15 — Performance Dashboard
- **Visuals**: 5-year KPI trend line chart (Forest Green gradient fill vs. Target dotted line) on a borderless, wide layout container, paired with a flat, border-t/b performance metric breakdown table.

### 20 — Kekuatan Psikologis
- **Visuals**: Grid of qualitative cards with full Warm Beige borders, gold-tinted top icon highlights, and a subtle scale hover transition, avoiding side-stripe accent borders.

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

---

## 6. Phase B Specification: Component & Pattern Rollout

This phase expands our refined visual layout across all remaining 18 sections by using unified component templates to prevent code duplication, while creating specialized components for unique data visual structures.

### A. Pattern Re-use Mapping

1. **Index + Radar Component (`IndexRadarSection`)**:
   - **Target Sections**: `07 — Layer 2: Potensi`, `13 — Emotional Intelligence (EQ)`.
   - **Design Guidelines**: Parameterized dimensions and thresholds. Keep grid color to Warm Beige (`#f0ebe4`), actual values in Forest Green, and standard boundaries in Rust Red.
2. **Score List Component (`ScoreListSection`)**:
   - **Target Sections**: `05 — Layer 1: Kompetensi`, `08 — IQ & Profil Kognitif`, `11 — Learning Agility`, `12 — Leadership Potential`, `14 — Values & Integrity`.
   - **Design Guidelines**: Use horizontal progress bars. Employ a single-hue sequential scale (e.g. Amber/Gold tints) to represent values instead of colorful rainbow lines. Align all bars to a clean grid with textual value markers on the right.
3. **Qualitative Cards Component (`QualitativeListSection`)**:
   - **Target Sections**: `18 — Profil Personal (Pelengkap)`.
   - **Design Guidelines**: Flat cards with Warm Beige borders. Clearly mark this section with a disclaimer label indicating its informal supplemental nature.

### B. Specialized Layout Components (Unique Sections)

1. **02 — Ringkasan Eksekutif (Executive Summary)**:
   - **Layout**: High-density snapshot card.
   - **Visual**: Display a single composite Talent Index score in large prominent typography, next to a 5-pillar key rating indicator.
2. **03 — Identitas Peserta (Participant Profile)**:
   - **Layout**: Factual data table.
   - **Visual**: A highly readable 2-column metadata list. Optimize for high-contrast reading with zero charts.
3. **10 — DISC Profile**:
   - **Layout**: 2x2 DISC Grid.
   - **Visual**: Graphically highlight the dominant quadrant (Dominance, Influence, Steadiness, Compliance) using gold accent boundaries, paired with narrative columns for behaviors.
4. **16 — Talent 9-Box Matrix**:
   - **Layout**: Standard 3x3 HR grid.
   - **Visual**: Highlight the candidate's active box (e.g. "Future Leader") using Amber Gold backgrounds and high-contrast text, while keeping other boxes in subtle Warm Beige lines.
5. **17 — Succession Readiness**:
   - **Layout**: Horizon timeline.
   - **Visual**: Segment kesiapan into 3 timeframes (Siap sekarang, <1 tahun, <2 tahun) utilizing a linear sequence.
6. **19 — Kesehatan Jiwa (Mental Health)**:
   - **Layout**: Metric + Narrative split.
   - **Visual**: Gauge indicator for overall well-being score, paired with text blocks for psychologist comments.
7. **21 — Indikator Risiko**:
   - **Layout**: Non-alarmist gauge card.
   - **Visual**: Low/Medium/High indicator with subtle warm colors, avoiding bright warning colors if the risk is low.
8. **22 — Rekomendasi Pengembangan**:
   - **Layout**: Two-column contrast grid.
   - **Visual**: High-contrast list separating "Kekuatan Utama" (Strengths) and "Area Pengembangan" (Development Areas).
9. **23 — Rekomendasi Peran Berikutnya**:
   - **Layout**: Progressive action roadmap.
   - **Visual**: 3-phase chronological steps (Fase 1/2/3) with horizontal paths, showing transition target positions.

---

## 7. Phase C Specification: Integration & Print Assembly

This phase stitches all 23 sections together into a single, cohesive web application and designs the flat print rendering pipeline.

### A. Navigation & Lazy Loading
- **Sidebar TOC**: Handles active state swapping. Use Livewire conditional rendering (`@if($activeSection === 'code')`) to load components lazily, preventing the DOM from loading all 23 sections at once in web view.
- **Section Swapping**: Add subtle fade-in transitions (`transition-all duration-200`) when swapping active views to improve user experience.

### B. Print Mode (Cetak PDF)
- **Flattened Layout**: When `printMode` is toggled true:
  - Hide the sidebar TOC and top sticky header controls using utility CSS classes (`print:hidden`).
  - Render all 23 sections in sequence.
  - Apply CSS page-break rules (`page-break-after: always;`) to each major section wrapper to guarantee clean page boundaries during PDF export.
  - Enforce `max-w-full` on printing view layouts so they span the entire paper width dynamically.

