# Elementor CSS Rules - Don't Repeat These Mistakes

## Rule 1: NO GLOBAL SELECTORS
```css
/* WRONG */
body { background: #fbf8f2; }

/* CORRECT */
#unique-id-2025 .class-name { background: #fbf8f2; }
```

## Rule 2: ALWAYS USE `#unique-id .class` PATTERN
Every CSS rule must start with the scoped container ID followed by a class.

## Rule 3: CHECK WHAT'S ACTUALLY VISIBLE
Before changing background colors, trace which element is actually showing:
- Container has background? Check.
- But is a panel/child sitting ON TOP with a different color? **That's the one to change.**

## Rule 4: CSS VARIABLES - TRACE THEM
If something uses `var(--panel-light)`, change the variable definition, not random elements.

```css
/* The variable that controls visible panels */
#laxcc-prerolls-info-2025 {
    --panel-light: #fbf8f2;  /* <-- THIS is what you change */
}
```

## Rule 5: READ EXISTING CODE FIRST
Before making changes:
1. Read `HOW TO CODE ELEMENTOR.txt`
2. Find which CSS variable or class controls the visible element
3. Make ONE targeted change

## Rule 6: ADDING LINKS TO CARDS WITHOUT BREAKING STYLING
Use `display:contents` to wrap elements in anchors without affecting layout:

```html
<!-- CORRECT - display:contents makes anchor invisible to layout -->
<a href="..." style="display:contents;">
<div class="laxcc-story-card purple-bg">
    <!-- content stays exactly the same -->
</div>
</a>

<!-- WRONG - changing div to anchor breaks CSS selectors -->
<a class="laxcc-story-card purple-bg">...</a>
```

## Quick Checklist
- [ ] No `body`, `html`, or `:root` selectors
- [ ] All rules scoped to `#unique-id .class`
- [ ] Traced which element is actually visible on screen
- [ ] Changed the correct variable/class
- [ ] Used `!important` only when needed for Elementor overrides
- [ ] When adding links, use `display:contents` wrapper - DON'T change element types
