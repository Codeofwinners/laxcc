# Button Styling Guide

## View All Hydro Button

**Location:** Both `hydro.html` and `home/hydro.html` files

**CSS Class:** `.ph-footer__cta`

### Specifications

| Property | Value |
|----------|-------|
| Background Color | `#F89459` (Orange) |
| Font Color | `#fff` (White) |
| Font Size | 14px |
| Font Weight | 800 (Bold) |
| Padding | 12px 32px |
| Border Radius | 10px (var(--ph-radius-md)) |
| Text Transform | UPPERCASE |
| Letter Spacing | 0.05em |
| **Hover Effects** | **NONE - No color change, no animations** |

### CSS Code

```css
.ph-footer__cta {
    background: #F89459;
    color: #fff;
    font-size: 14px;
    font-weight: 800;
    padding: 12px 32px;
    border-radius: var(--ph-radius-md);
    text-decoration: none;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
```

**Important:** There should be NO `.ph-footer__cta:hover` rule. The button has zero hover effects.

### Files to Update

- `/Users/vibecoder/projects/LAXCC/hydro.html` (lines 246-252)
- `/Users/vibecoder/projects/LAXCC/home/hydro.html` (lines 246-252)
