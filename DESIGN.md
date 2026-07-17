---
name: Human Capital Assessment & SPSP Reports
description: Executive-grade report dashboard using the Executive Journal design system.
colors:
  primary: "#171412"
  accent: "#b45309"
  neutral-bg: "#faf8f5"
  neutral-card: "#ffffff"
  neutral-border: "#f0ebe4"
  # HCA-specific Chart Colors (transparent fills)
  actual-hca: "#15803d"
  standard-hca: "#b91c1c"
  tolerance-hca: "#6b7280"
  # SPSP-specific Chart Colors (solid colors for business philosophy)
  actual-spsp: "#5db010"
  standard-spsp: "#b50505"
  tolerance-spsp: "#fafa05"
typography:
  display:
    fontFamily: "Lora, Georgia, serif"
    fontSize: "clamp(2rem, 5vw, 3.5rem)"
    fontWeight: 700
    lineHeight: 1.2
    letterSpacing: "-0.02em"
  headline:
    fontFamily: "Lora, Georgia, serif"
    fontSize: "1.75rem"
    fontWeight: 600
    lineHeight: 1.3
  title:
    fontFamily: "Instrument Sans, sans-serif"
    fontSize: "1.25rem"
    fontWeight: 600
    lineHeight: 1.4
  body:
    fontFamily: "Instrument Sans, sans-serif"
    fontSize: "1rem"
    fontWeight: 400
    lineHeight: 1.6
  label:
    fontFamily: "Instrument Sans, sans-serif"
    fontSize: "0.875rem"
    fontWeight: 500
    lineHeight: 1.5
    letterSpacing: "0.05em"
rounded:
  sm: "4px"
  md: "8px"
spacing:
  xs: "4px"
  sm: "8px"
  md: "16px"
  lg: "24px"
  xl: "32px"
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.neutral-bg}"
    rounded: "{rounded.sm}"
    padding: "8px 16px"
  button-primary-hover:
    backgroundColor: "#2c2724"
  card:
    backgroundColor: "{colors.neutral-card}"
    rounded: "{rounded.md}"
    padding: "24px"
---

# Design System: Human Capital Assessment & SPSP Reports

## 1. Overview

**Creative North Star: "Executive Journal"**

The "Executive Journal" theme elevates Human Capital Assessment (HCA) and SPSP reports from standard data dashboards to premium, high-readability printed and digital journals. It utilizes high-contrast editorial typography, wide margins, warm background surfaces that prevent screen glare, and precise borders instead of heavy drop shadows.

Aesthetics in this system are deliberately refined and restrained. There are no decorative grid lines, heavy drop shadows, or text gradients. Success is defined by clean lines, clear hierarchy, and data visualization that feels both expert and trustworthy.

**Key Characteristics:**
- Editorial serif headings (Lora) paired with clean geometric sans-serif UI elements (Instrument Sans).
- Warm ivory backgrounds with white content surfaces to mimic premium paper.
- Warm beige borders replacing cool/blue slates for a structured, non-digital look.
- Amber-gold accents used sparingly to guide focus on key statistics and indexes.

## 2. Colors

All colors are chosen to balance a premium corporate tone with excellent readability and compliance with accessibility guidelines.

### Primary
- **Deep Espresso Charcoal** (#171412): Used for dominant structural chrome, sidebars, headers, and major layout borders.

### Secondary
- **Amber Gold** (#b45309): Used sparingly (≤10% of any view) for active states, key highlight numbers, and main indicators.

### Neutral
- **Warm Ivory Paper** (#faf8f5): Used as the primary background color for layouts and bodies to mimic physical paper.
- **Pure White** (#ffffff): Used for content cards, tables, and nested areas to establish depth.
- **Warm Beige Border** (#f0ebe4): Used for subtle borders defining sections and boundaries.

### Chart Colors & Fills
- **HCA Charts**: Uses Forest Green (#15803d) for Actual, Rust Red (#b91c1c) for Standard, and Slate Grey (#6b7280) for Tolerance with transparent fills for visual layering.
- **SPSP Charts**: Retains legacy **solid color overlays** without transparency to represent the core business philosophy:
  - Participant (Actual): `#5db010` (Hijau)
  - Standard: `#b50505` (Merah)
  - Tolerance: `#fafa05` (Kuning)

---

## 3. Typography

**Display Font:** Lora (serif)
**Body & UI Label Font:** Instrument Sans (sans-serif)

The system pairs Lora (an elegant, editorial serif) for Display and Headline styles with Instrument Sans (a precise, modern geometric sans-serif) for body text, numbers, and UI labels.

### Hierarchy
- **Display** (Bold (700), clamp(2rem, 5vw, 3.5rem), 1.2, letter-spacing -0.02em): Used for large page titles and hero metrics.
- **Headline** (Semi-Bold (600), 1.75rem, 1.3): Used for section titles.
- **Title** (Semi-Bold (600), 1.25rem, 1.4): Used for card titles and subheadings.
- **Body** (Regular (400), 1rem, 1.6, max line length 75ch): Used for paragraphs, explanations, and descriptions.
- **Label** (Medium (500), 0.875rem, 1.5, letter-spacing 0.05em): Used for badges, tables, chart labels, and micro-copy.

---

## 4. Elevation

The elevation philosophy is strictly **Flat & Layered**. We do not use heavy shadows or neon glow effects. Depth is established through flat, stacked containers using different background shades and clean, thin borders.

### Shadow Vocabulary
- **None**: Shadows are not used for elevation.
- **Micro Hover** (`box-shadow: 0 2px 4px rgba(0,0,0,0.02)`): Only used to provide tiny haptic feedback when a user hovers over interactive cards.

---

## 5. Components

### SPSP Data Tables
SPSP reports display dense multidimensional metrics. The table design balances high information density with elegant legibility:
- **Table Container**: Pure White (`#ffffff`) background in light mode or Deep Charcoal (`#171717`) in dark mode, bounded by a 1px Warm Beige border (`#f0ebe4`).
- **Cell Padding**: Vertical padding must be **`py-2` (8px)**, and horizontal padding **`px-4` (16px)** to stay compact yet breathable.
- **Headers & Totals**: Lighter warm-ivory background (`bg-warm-ivory` / `dark:bg-neutral-900`) with bold text and solid high contrast.
- **Font-Weights**:
  - Aspect names: `font-semibold text-primary-ink dark:text-neutral-100`.
  - Values & numbers: `font-normal text-primary-ink dark:text-neutral-200` to prevent visual clutter.
  - Text sizes: set to **`text-sm` (14px)** for readability on both standard screens and print/PDF exports.

### Spider Plot Charts (SPSP)
- **Point Labels & Ticks Font**: Family must be set explicitly to `"'Instrument Sans', sans-serif"` with size `14px` or `16px`.
- **Legend Bullet Styles**: Small circular dots (using Tailwind `rounded-full w-3 h-3`) displaying matching solid colors rather than legacy thick line rectangles.

---

## 6. Do's and Don'ts

### Do:
- **Do** use Lora for display headings and Instrument Sans for body, tables, and chart labels.
- **Do** maintain a high contrast ratio (4.5:1) for all text against backgrounds.
- **Do** use solid, clean borders instead of drop shadows.
- **Do** keep the `py-2` compact padding on SPSP reports for consistent data density.

### Don't:
- **Don't** use neon or high-saturation colors for core UI containers.
- **Don't** apply overall bold weights to entire table bodies.
- **Don't** use opacity or muted values (e.g. `text-gray-400`) on small body fonts or numbers.
