# Failure Log

Track mistakes to never repeat them.

---

## Session: 2026-01-28

### 1. Button text wrapping on mobile
**Issue:** Added buttons with text that wrapped to 2 lines inside each button on mobile.
**Fix:** Always add `white-space: nowrap` to buttons.
**Rule:** Buttons should NEVER have multi-line text.

### 2. Buttons stacking vertically on mobile
**Issue:** Used `flex-wrap: wrap` on button container causing buttons to stack.
**Fix:** Use `flex-wrap: nowrap` and reduce padding/font-size to fit on one row.
**Rule:** Hero CTA buttons should stay on same row on mobile.

### 3. Oval/pill buttons when user wanted slight rounding
**Issue:** Used `border-radius: 100px` making buttons fully oval.
**Fix:** Use `border-radius: 8px` for subtle rounding.
**Rule:** Ask or use subtle rounding (8-12px) unless specifically told to use pill shape.

### 4. Filled white button when user wanted outline style
**Issue:** Used solid white background on hero button.
**Fix:** Use `background: transparent` with `border: 1px solid var(--teal)`.
**Rule:** Default to outline/ghost buttons on dark backgrounds unless told otherwise.

### 5. Added hover effects user didn't want
**Issue:** Added hover state changes (color fills, transforms) without being asked.
**Fix:** Removed all hover effects.
**Rule:** Don't add hover effects unless explicitly requested.

### 6. Grey text unreadable on hero
**Issue:** Used `var(--gray-400)` for hero subtitle - too low contrast.
**Fix:** Changed to brand teal `#00AFC9`.
**Rule:** Text on dark image backgrounds needs high contrast - use brand colors, not grey.

### 7. Low-effort card/icon designs
**Issue:** Created generic boxy cards with emoji icons - looked cheap.
**Fix:** Redesigned with editorial magazine layout, no cards, giant typography.
**Rule:** Avoid generic card grids and emoji icons. Use editorial layouts, full-bleed sections, typography as visual element.

### 8. SEO copy that doesn't match search intent
**Issue:** Used marketing fluff like "Escape the Terminal. Experience LA." for H1.
**Fix:** Changed to "LAX Layover Ideas" - what people actually search.
**Rule:** H1 and titles must match real search queries, not creative marketing copy.

---

## Rules Summary

1. Buttons: `white-space: nowrap`, stay on one row, subtle border-radius (8px), outline style on dark BGs
2. No hover effects unless asked
3. High contrast text on image backgrounds - never grey
4. Editorial layouts over generic cards
5. No emoji icons
6. SEO titles match actual search queries
