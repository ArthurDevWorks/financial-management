# Design System

> **Last Updated:** [date]
> This document defines the visual language of the project. All UI implementations MUST follow these tokens and conventions.
> See `PROJECT_CONTEXT.md §8` for Figma file reference and primary styling configuration.

---

## Colors

| Token | Usage | Value |
|-------|-------|-------|
| `--color-primary` | Primary actions, links | [#color] |
| `--color-secondary` | Secondary actions | [#color] |
| `--color-success` | Success states, confirmations | [#color] |
| `--color-warning` | Warning states, alerts | [#color] |
| `--color-danger` | Error states, destructive actions | [#color] |
| `--color-background` | Page background | [#color] |
| `--color-surface` | Card/section background | [#color] |
| `--color-text` | Primary text | [#color] |
| `--color-text-muted` | Secondary/muted text | [#color] |
| `--color-border` | Borders, dividers | [#color] |

---

## Typography

| Token | Usage | Font | Size | Weight | Line Height |
|-------|-------|------|------|--------|------------|
| `--font-heading-1` | Page title | [font] | [32px] | [Bold] | [1.2] |
| `--font-heading-2` | Section title | [font] | [24px] | [Bold] | [1.3] |
| `--font-heading-3` | Subsection title | [font] | [20px] | [SemiBold] | [1.4] |
| `--font-subtitle` | Subtitle/description | [font] | [16px] | [Medium] | [1.5] |
| `--font-body` | Body text | [font] | [14px] | [Regular] | [1.5] |
| `--font-caption` | Caption, metadata | [font] | [12px] | [Regular] | [1.4] |

**Primary Font:** [Inter / Geist / DM Sans / System default]

---

## Spacing Scale

| Token | Value |
|-------|-------|
| `--space-1` | 4px |
| `--space-2` | 8px |
| `--space-3` | 12px |
| `--space-4` | 16px |
| `--space-5` | 24px |
| `--space-6` | 32px |
| `--space-7` | 48px |
| `--space-8` | 64px |

---

## Border Radius

| Token | Value |
|-------|-------|
| `--radius-sm` | 4px |
| `--radius-md` | 8px |
| `--radius-lg` | 12px |
| `--radius-full` | 9999px |

---

## Shadows

| Token | Usage | Value |
|-------|-------|-------|
| `--shadow-sm` | Cards, small surfaces | [e.g., `0 1px 2px rgba(0,0,0,0.05)`] |
| `--shadow-md` | Dropdowns, modals | [e.g., `0 4px 6px rgba(0,0,0,0.1)`] |
| `--shadow-lg` | Modals, drawers | [e.g., `0 10px 15px rgba(0,0,0,0.15)`] |

---

## Component Library

### Inputs

| Component | States | Usage |
|-----------|--------|-------|
| Text Input | default, hover, focus, error, disabled, readonly | Single-line text entry |
| Select | default, hover, focus, error, disabled | Dropdown selection |
| Checkbox | unchecked, checked, indeterminate, disabled | Multiple selection |
| Radio | unchecked, checked, disabled | Single selection |
| Textarea | default, hover, focus, error, disabled | Multi-line text entry |

### Feedback

| Component | States | Usage |
|-----------|--------|-------|
| Alert | info, success, warning, danger | Inline messages |
| Toast | info, success, warning, danger | Temporary notifications |
| Modal | open, closing | Confirmation, forms, detail views |
| Tooltip | visible, hidden | Contextual hints |

### Data Display

| Component | States | Usage |
|-----------|--------|-------|
| Table | default, hover, selected, empty | Tabular data |
| Pagination | default, active, disabled | Page navigation |
| Card | default, hover, selected | Content containers |
| Badge | default, info, success, warning, danger | Labels, statuses |
| Progress Bar | determinate, indeterminate | Loading progress |

### Navigation

| Component | States | Usage |
|-----------|--------|-------|
| Sidebar | collapsed, expanded | Main navigation |
| Tabs | active, inactive, disabled | Section switching |
| Breadcrumb | default, active | Page hierarchy |
| Navbar | default, scrolled | Top navigation |
| Pagination | default, active, disabled | Page navigation |

### Buttons

| Component | Variants | States |
|-----------|----------|--------|
| Button | primary, secondary, outline, ghost, danger | default, hover, active, focus, disabled, loading |

---

## Responsive Breakpoints

| Breakpoint | Width | Target |
|------------|-------|--------|
| `sm` | 640px | Mobile landscape |
| `md` | 768px | Tablet |
| `lg` | 1024px | Desktop |
| `xl` | 1280px | Wide desktop |

---

## Accessibility Rules

- Color contrast: minimum 4.5:1 for normal text, 3:1 for large text (WCAG AA)
- Focus indicators visible on all interactive elements
- Keyboard navigation with logical tab order
- Semantic HTML (`<nav>`, `<main>`, `<section>`, `<article>`, etc.)
- ARIA labels and roles on custom interactive components
- Touch targets minimum 44×44px on mobile
