---
name: Human Capital Assessment Report
description: Executive-grade report dashboard using the Executive Journal design system.
colors:
  primary: "#171412"
  accent: "#b45309"
  neutral-bg: "#faf8f5"
  neutral-card: "#ffffff"
  neutral-border: "#f0ebe4"
  actual: "#15803d"
  standard: "#b91c1c"
  tolerance: "#6b7280"
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

# Design System: Human Capital Assessment Report

## 1. Overview

**Creative North Star: "Executive Journal"**

The "Executive Journal" theme elevates the Human Capital Assessment Report from a standard data dashboard to a premium, high-readability printed and digital journal. It utilizes high-contrast editorial typography, wide margins, warm background surfaces that prevent screen glare, and precise borders instead of heavy drop shadows.

Aesthetics in this system are deliberately refined and restrained. There are no decorative grid lines, heavy drop shadows, or text gradients. Success is defined by clean lines, clear hierarchy, and data visualization that feels both expert and trustworthy.

**Key Characteristics:**
- Editorial serif headings paired with clean geometric sans-serif UI elements.
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

### Named Rules
**The 10% Accent Rule.** The Amber Gold accent is used strictly on 10% or less of any given screen. Its rarity is the point; when everything is highlighted, nothing is.
**The Ink Contrast Rule.** All body and detail text must maintain a minimum contrast ratio of 4.5:1 against their backgrounds. Text must never fade into a light gray.

## 3. Typography

**Display Font:** Lora (serif)
**Body Font:** Instrument Sans (sans-serif)

The system pairs Lora (an elegant, editorial serif) for Display and Headline styles with Instrument Sans (a precise, modern geometric sans-serif) for body text, numbers, and UI labels.

### Hierarchy
- **Display** (Bold (700), clamp(2rem, 5vw, 3.5rem), 1.2, letter-spacing -0.02em): Used for large page titles and hero metrics.
- **Headline** (Semi-Bold (600), 1.75rem, 1.3): Used for section titles.
- **Title** (Semi-Bold (600), 1.25rem, 1.4): Used for card titles and subheadings.
- **Body** (Regular (400), 1rem, 1.6, max line length 75ch): Used for paragraphs, explanations, and descriptions.
- **Label** (Medium (500), 0.875rem, 1.5, letter-spacing 0.05em): Used for badges, tables, chart labels, and micro-copy.

### Named Rules
**The No-Shout Letter Spacing Rule.** Headings must never have letter-spacing less than -0.04em. Tighter spacing causes characters to touch and degrades editorial readability.
**The Balanced Headline Rule.** All h1, h2, and h3 tags must apply `text-wrap: balance` to prevent awkward line breaks.

## 4. Elevation

The elevation philosophy is strictly **Flat & Layered**. We do not use heavy shadows or neon glow effects. Depth is established through flat, stacked containers using different background shades and clean, thin borders.

### Shadow Vocabulary
- **None**: Shadows are not used for elevation.
- **Micro Hover** (`box-shadow: 0 2px 4px rgba(0,0,0,0.02)`): Only used to provide tiny haptic feedback when a user hovers over interactive cards.

### Named Rules
**The Border Depth Rule.** Use borders instead of shadows to differentiate layout containers. Borders must be solid 1px with `#f0ebe4` (Warm Beige).

## 5. Components

Components are styled with a clean, rectangular shape and subtle radii. They feel premium, lightweight, and precise.

### Buttons
- **Shape:** Soft rectangular (4px border-radius)
- **Primary:** Background color `#171412`, text color `#faf8f5`, padding `8px 16px`.
- **Hover:** Background color `#2c2724`, transitions over `150ms` using standard ease.

### Cards / Containers
- **Corner Style:** Rounded (8px border-radius)
- **Background:** `#ffffff` (Pure White)
- **Border:** Solid 1px `#f0ebe4` (Warm Beige)
- **Internal Padding:** `24px` (`p-6`)

### Inputs / Fields
- **Style:** Stroke `#f0ebe4`, background `#ffffff`, radius `4px`.
- **Focus:** Border color `#b45309` (Amber Gold) with no outer rings.

### Navigation
- **Style:** Left sidebar structure. Background `#171412` (Deep Espresso Charcoal), text color `#ffffff` (when active) and `#a8a29e` (when inactive). Hover states should use background color `#2c2724` with 4px border-radius.

## 6. Do's and Don'ts

### Do:
- **Do** use Lora for display headings and Instrument Sans for body and data labels.
- **Do** maintain a high contrast ratio (4.5:1) for all text against backgrounds.
- **Do** wrap large text containers with a maximum width of 75ch.
- **Do** use `@media print` rules to optimize the layout for PDF exports.

### Don't:
- **Don't** use border-left or border-right accents thicker than 1px on cards/callouts.
- **Don't** use gradient text or background-clip text effects.
- **Don't** use decorative glassmorphism or blurs.
- **Don't** use heavy card shadows or box shadows with blur radii greater than 8px.
- **Don't** use rounded corners larger than 12px for cards or inputs.
- **Don't** use neon or high-saturation colors for core UI components.
