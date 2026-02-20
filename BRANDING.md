# LAXCC UI Branding Guide
> Single source of truth. Do not repeat these decisions.

---

## Design Tokens (CSS Variables)

```css
--primary-teal: #00AFC9;
--primary-dark: #0f3a5f;
--primary-gradient: linear-gradient(135deg, #0f3a5f 0%, #0e6a78 60%, #1196a7 100%);
--accent-orange: #F89459;
--accent-peach: #FBD1A1;
--accent-green: #3daa6e;

--white: #ffffff;
--cream: #fbf8f2;          /* page body background */
--light-gray: #f8fafc;
--text-main: #0f172a;
--text-muted: #64748b;
```

---

## Page Body Background
**Always `#fbf8f2` (cream).** Never white for the main page body.

---

## Buttons — CRITICAL RULES

### Text color is ALWAYS white
WordPress theme overrides `a { color }` with blue. **Always use both:**
1. `color: #ffffff !important;` on the base class
2. Scoped `:link` / `:visited` selectors for full override:

```css
.laxcc-[scope] a.btn-class,
.laxcc-[scope] a.btn-class:link,
.laxcc-[scope] a.btn-class:visited {
    color: #ffffff !important;
    text-decoration: none !important;
}
```

### Button text must never wrap
Always add `white-space: nowrap` to every button.

### Button colors by context
| Button | Background | Text |
|--------|-----------|------|
| Primary CTA / Add to Cart | `var(--primary-teal)` | `#fff !important` |
| Strava / editorial CTA | `var(--accent-green)` | `#fff !important` |
| Liquid glass card Shop Now | `var(--primary-teal)` | `#fff !important` |
| Dark section hover state | transparent | keep original accent color |

---

## Scoping (WordPress Embed Pattern)
All CSS and HTML must be scoped to a wrapper class to prevent global style leaks.
- Page: `.laxcc-cbd-guide`
- Open Vape: `.laxcc-sponsored-content`
- New pages follow: `.laxcc-[page-slug]`

---

## Hero Section
- **Layout:** 2-col CSS Grid (`1fr 1fr`), `align-items: stretch`
- **Left col:** Text content, H1 with 3 staggered spans, license badge, CTA
- **Right col:** `.hero-image-wrapper` containing `.hero-image-column` (background-image) + `.liquid-glass-card` (absolute on desktop, block below on mobile)
- **H1 rule:** Must always contain the primary local SEO keyword. Example: "Best CBD / Store Near / LAX Airport"
- **Hero image fills column:** `height: 100%; min-height: 80vh` on wrapper

### H1 Typography
```css
#hero-line-1 { color: var(--primary-dark); }
#hero-line-2 { color: #fbf8f2; margin-left: 8vw; -webkit-text-stroke: 2px var(--primary-dark); }
#hero-line-3 { color: var(--primary-dark); margin-left: 3vw; }
```

### Hero License Badge
```css
white-space: nowrap;
font-size: 0.7rem;
letter-spacing: 1px;
```

---

## Liquid Glass Card (Hero Product Feature)
- **Desktop:** `position: absolute; top: 32px; right: 28px; width: 192px`
- **Mobile:** `position: relative` — sits OUTSIDE `.hero-image-column`, stacks below it as a block card
- Mobile card: `background: var(--white)`, no `backdrop-filter`, border `1px solid rgba(15,58,95,0.1)`
- Mobile text: `color: var(--primary-dark) !important` (not white)
- Never hide the card on mobile — it must always be visible

---

## Product Grid Section
- **Position:** Directly below hero (first section after hero)
- **Background:** `#fbf8f2`
- **Desktop columns:** `repeat(7, 1fr)` — all products in 1 row
- **Mobile columns:** `repeat(2, 1fr)`
- **Card image aspect-ratio:** `3 / 4` (portrait/tall)
- **Max-width of wrap:** `1680px`
- Product names should be short in 7-col layout to prevent overflow

---

## Editorial Story Sections
- Alternating 2-col layout with `story-number`, `story-title`, `feature-list`
- **Background:** `#fbf8f2 !important`
- Visual card on right side with accent color per section:
  - 01 Licensed: `--primary-teal`
  - 02 Products: `--accent-green`
  - 03 Location: `--primary-dark`

---

## Sträva Craft Coffee Section (External Recommendation)
- **Full-bleed layout:** No section padding. Grid goes edge-to-edge.
- **Left column:** Image fills 100% height — `position: absolute; inset: 0; object-fit: cover`
- **Right column:** Content with `padding: 80px 64px`, centered vertically
- **Background:** `var(--primary-dark)` (dark navy)
- **Text:** White on dark
- **Image:** `stravacraftcoffee.com/cdn/shop/files/Strava-Coffee-Roasting-Process.jpg`
- **CTA links to:** `https://www.stravacraftcoffee.com/collections/all-products`
- **Disclaimer:** "We're not affiliated with Sträva Craft Coffee — we recommend them as a great CBD coffee option."
- **Mobile:** Image stacks on top at `height: 320px`, content below

---

## External Link Rules
- Sponsored/affiliate external links: `rel="noopener noreferrer"`
- Image credits (e.g. Kiva): `rel="nofollow noopener noreferrer"`
- Internal LAXCC links: no rel attribute

---

## FAQ Section
- 5 questions max
- Focus on local SEO ("CBD near LAX", "licensed dispensary", "is CBD legal")

---

## Final CTA Section
- `background: var(--primary-dark)`
- Links to the full CBD shop URL:
  `https://laxcc.com/shop/?pagination=1&sort=POPULAR_DESC&retailer_id=6ed054f8-19a5-4991-b562-c4df2a287ae3&menu_type=RECREATIONAL&collection_type=PICKUP&products_search=CBD`

---

## Brand Colors Quick Ref
| Use | Color |
|-----|-------|
| Page background | `#fbf8f2` |
| Primary brand | `#00AFC9` (teal) |
| Dark/navy | `#0f3a5f` |
| Action/success | `#3daa6e` (green) |
| Warm accent | `#F89459` (orange) |
| All button text | `#ffffff !important` |

---

## WordPress-Specific Fixes
1. **Blue link text on buttons** → Use `.laxcc-[scope] a.btn:link, :visited { color: #fff !important }` — class alone isn't enough
2. **Global style leaks** → Scope ALL CSS rules with wrapper class prefix
3. **Shortcodes** → If Leafbridge shortcode fails, use custom product card HTML with dutchie.com images
