# CSS Scoping with Unique IDs and Classes

This guide outlines how to implement CSS scoping by utilizing unique identifiers (IDs) and classes within your HTML structure.

When building web pages, **"CSS Scoping"** is the practice of ensuring styles only apply to specific elements and don't "leak" or conflict with other parts of the site. The most common way to achieve this without modern frameworks is through naming conventions.

---

## 1. Using Unique IDs (`#`)

IDs are unique identifiers. Each ID must only appear **once** on a single page. This makes them the strongest way to scope styles, though they are difficult to override due to high specificity.

### Example Structure

```html
<div id="tcb-hero-2025">
    <h1>Welcome to The Cannabar</h1>
</div>
```

### Scoped CSS

```css
#tcb-hero-2025 {
    background-color: #122017;
    padding: 1.5rem 2.5rem; /* Use rem per 2025 guidelines */
}

#tcb-hero-2025 h1 {
    font-size: clamp(3rem, 10vw, 6rem); /* Hero title scale */
    color: white;
    line-height: 1.1;
}
```

---

## 2. Using Unique Class Names (`.`)

Classes can be used multiple times, but you can make them "unique" to a component by using a **prefix** or a naming convention like **BEM** (Block Element Modifier).

### The BEM Strategy

BEM helps create unique, readable class names that describe the relationship between elements.

| Part | Description | Example |
| --- | --- | --- |
| **Block** | Standalone entity | `.tcb-card` |
| **Element** | Part of a block | `.tcb-card__title` |
| **Modifier** | A variation | `.tcb-card--featured` |

### Example Structure

```html
<div class="tcb-profile-card">
    <h2 class="tcb-profile-card__name">Jane Doe</h2>
    <p class="tcb-profile-card__bio">Cannabis enthusiast based in New Jersey.</p>
    <button class="tcb-profile-card__button tcb-profile-card__button--active">
        Follow
    </button>
</div>
```

### Scoped CSS

```css
/* Block: Only affects this specific component */
.tcb-profile-card {
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 0.75rem;
    padding: 1.5rem;
    background: rgba(18, 32, 23, 0.8);
}

/* Element: Child of the block */
.tcb-profile-card__name {
    font-size: clamp(1.25rem, 3.5vw, 1.75rem); /* Section heading scale */
    font-weight: 700;
    color: white;
}

.tcb-profile-card__bio {
    font-size: clamp(0.875rem, 2vw, 1.125rem); /* Body text scale */
    color: rgba(255, 255, 255, 0.7);
    line-height: 1.5;
}

/* Modifier: Variation of the button */
.tcb-profile-card__button--active {
    background-color: #1A4314;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    font-size: 1.125rem;
    font-weight: 600;
}
```

---

## 3. Comparison Table

| Feature | ID (`#`) | Class (`.`) |
| --- | --- | --- |
| **Uniqueness** | Must be unique per page | Can be reused |
| **Specificity** | High (Hard to override) | Medium (Flexible) |
| **Best For** | Major layout sections (Hero, Footer) | Reusable components (Buttons, Cards) |
| **Scoping Power** | Excellent | Excellent (if named correctly) |

---

## 4. The Cannabar Prefix Standard

For this project, **always use the `tcb-` prefix** (The Cannabar) for all custom classes and IDs.

| Context | Example |
| --- | --- |
| **Hero Section** | `#tcb-hero-2025`, `.tcb-hero-header` |
| **Navigation** | `.tcb-nav-pill`, `.tcb-desktop-nav` |
| **Cards** | `.tcb-brand-card`, `.tcb-product-card` |
| **Buttons** | `.tcb-caption-cta`, `.tcb-hero-link` |
| **Modifiers** | `.tcb-nav-pill--active`, `.tcb-card--featured` |

---

## 5. Tips for Generating Unique Names

To ensure your names don't clash with third-party libraries:

1. **Prefixing**: Always use the project-specific prefix (`tcb-` for The Cannabar).
2. **Namespacing**: Use the context (e.g., `tcb-hero-slider-arrow` instead of just `arrow`).
3. **Year/Version Suffix**: For major sections, include the year (e.g., `#tcb-hero-2025`).
4. **Avoid Generic Names**: Never use `.container`, `.button`, `.card` without a prefix.

---

## 6. 2025 Typography Reference (Quick Reference)

When styling text inside your scoped components, use these `clamp()` values:

| Element | `clamp()` Formula |
| --- | --- |
| Hero Title | `clamp(3rem, 10vw, 6rem)` |
| Intro Text | `clamp(2.25rem, 6vw, 3.5rem)` |
| Slide Title | `clamp(1.5rem, 4vw, 2.5rem)` |
| Section Heading | `clamp(1.25rem, 3.5vw, 1.75rem)` |
| Body / Nav | `clamp(0.875rem, 2vw, 1.125rem)` |

> [!IMPORTANT]
> Always use `rem` for padding, margins, and border-radius. The only exception is `1px` borders.