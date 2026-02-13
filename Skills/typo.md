# Typography and Layout Guidelines (2025)

This document defines the 2025 visual standards for The Cannabar, focusing on high-impact, premium aesthetics and optimal display on large-format screens.

## 1. Layout & Grid
To accommodate modern high-resolution displays, we use an ultra-wide container system.

| Property | Value | Description |
| :--- | :--- | :--- |
| **Max Content Width** | `118.75rem` (1900px) | Standard for all main sections (Hero, Products, etc.) |
| **Gutter/Padding** | `1.5rem` to `2.5rem` | Side padding for mobile and desktop safety |
| **Desktop Columns** | 2-3 Column Grid | Prefer asymmetric layouts (e.g., `3fr 2fr`) for Hero sections |

## 2. Typography System
We use **'Space Grotesk'** as the primary typeface. All text must use `clamp()` for fluid scaling between mobile and desktop.

| Category | Mobile Min | Desktop Max | Logic / `clamp()` |
| :--- | :--- | :--- | :--- |
| **Hero Title** | `3rem` (48px) | `6rem` (96px) | `clamp(3rem, 10vw, 6rem)` |
| **Intro / Hero Text** | `2.25rem` (36px) | `3.5rem` (56px) | `clamp(2.25rem, 6vw, 3.5rem)` |
| **Slide Title** | `1.5rem` (24px) | `2.5rem` (40px) | `clamp(1.5rem, 4vw, 2.5rem)` |
| **Slide Description** | `1.5rem` (24px) | `2.25rem` (36px) | `clamp(1.5rem, 4vw, 2.25rem)` |
| **Section Headings** | `1.25rem` (20px) | `1.75rem` (28px) | `clamp(1.25rem, 3.5vw, 1.75rem)` |

> [!IMPORTANT]
> Line height for large text should be tight (`1.1` to `1.3`) to maintain a premium, modern feel.

## 3. Button Specifications
Buttons are bold and high-visibility.

| Button Type | Font Size | Desktop Padding | Border/Shape |
| :--- | :--- | :--- | :--- |
| **Primary CTA** | `1.5rem` (24px) | `1.25rem 4rem` | 3px solid / 0.75rem radius |
| **Secondary CTA** | `1.125rem` (18px) | `1rem 2.5rem` | 2px solid / 0.5rem radius |
| **Small/Action Add** | `1.125rem` (18px) | `0.75rem 1.5rem`| Gradient / 0.5rem radius |

## 4. Design Principles
- **Units**: ALWAYS use `rem` for measurements (1rem = 16px default).
- **Spacing**: Use tight vertical gaps between headers and images on desktop (**1rem - 2rem**) to keep content cohesive.
- **Micro-interactions**: Use subtle `translateY` and `scale` on hover.
- **Glassmorphism**: Use `backdrop-filter: blur(12px)` with semi-transparent backgrounds for overlays.
- **Responsive**: Mobile-first approach is mandatory. Desktop styles must be scoped within `@media (min-width: 48rem)`.
