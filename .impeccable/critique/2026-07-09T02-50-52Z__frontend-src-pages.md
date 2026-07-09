---
target: all frontend pages
total_score: 24
p0_count: 2
p1_count: 2
timestamp: 2026-07-09T02-50-52Z
slug: frontend-src-pages
---
## Design Health Score

| # | Heuristic | Score | Key Issue |
|---|-----------|-------|-----------|
| 1 | Visibility of System Status | 3 | Broadcast feedback is a tiny `<small>` — easy to miss |
| 2 | Match System / Real World | 2 | Missing accents (Contraseña, Administración, Próximos) |
| 3 | User Control and Freedom | 2 | No cancel on forms, no undo after tenant creation |
| 4 | Consistency and Standards | 3 | Strong utility classes, but inconsistent severity usage |
| 5 | Error Prevention | 2 | Timezone is free-text, no password toggle, no confirm on create |
| 6 | Recognition Rather Than Recall | 3 | Labels present, but no active-route indicator in nav |
| 7 | Flexibility and Efficiency | 2 | No keyboard shortcuts, no batch ops, no column sorting |
| 8 | Aesthetic and Minimalist Design | 3 | Cohesive palette, but "Proximos modulos" is filler |
| 9 | Error Recovery | 3 | Error messages include guidance, form preserves values |
| 10 | Help and Documentation | 1 | Zero inline help, no tooltips, no contextual hints |
| **Total** | | **24/40** | **Acceptable — functional but underdesigned** |

## Anti-Patterns Verdict

**Does this look AI-generated? Borderline.**

The dark blue palette (#07111f → #0c1729 with #60a5fa accents) is stock AI dark-theme DNA. The body radial gradient (`radial-gradient(circle at top left, rgba(37, 99, 235, 0.35), transparent 32%)`) is the single most cliché AI dark-theme artifact — it decorates nothing. The nav pill treatment with semi-transparent backgrounds and border is frosted-glass-adjacent. The `Turnero` eyebrow label above every heading is the "eyebrow-every-section" pattern applied globally via shell.

What saves it: semantic utility classes (`stack-sm`, `field-stack`, `metric-row`) are intentional. Grids vary (3-col dashboard, 2-col admin, centered auth). No gradient text, no side-stripe borders, no glassmorphism, no numbered sections, no identical card grids. The restraint is real.

**Deterministic scan**: 1 finding — Inter flagged as overused font. No false positives.

**Deterministic manual**: 2 P1 issues (eyebrow contrast fails WCAG AA, missing :focus-visible on nav links), 2 P2 issues (eyebrow inconsistency, field-error contrast borderline), 1 P3 (Inter overused).

## Overall Impression

This is a competent foundation with real design infrastructure — the utility class system and responsive grid are genuine. But it reads as a well-structured template, not a product with identity. The biggest opportunity: transforming the dashboard from a developer demo into an operational surface that earns an admin's trust on first login.

## What's Working

1. **Semantic utility class system** (`stack-sm`, `stack-md`, `field-stack`, `metric-row`): Well-named, appropriately sized, used consistently across all pages. This is real design infrastructure that makes the codebase maintainable.

2. **Graceful error handling in AdminTenantsPage**: The `createTenant` function manages `errorMessage`, `successMessage`, `fieldErrors`, resets on success, preserves on failure, and shows inline field errors. The most thoughtfully implemented interaction in the codebase.

3. **Responsive grid fallback**: The single `@media (max-width: 900px)` breakpoint correctly collapses all grid layouts to single-column and stacks the topbar. Clean and sufficient.

## Priority Issues

### P0 — No active-route indicator in navigation
**What**: All nav links look identical. No `router-link-active` style. Users cannot tell where they are.
**Why**: Fundamental orientation failure. Violates heuristics 1 and 6. On mobile where topbar stacks, this is disorienting.
**Fix**: Add `.nav-links a.router-link-active` with a visible indicator (underline, background tint, or border).
**Suggested command**: `$impeccable typeset` or `$impeccable polish`

### P0 — Timezone field is free-text
**What**: The timezone InputText accepts any string. No validation, no dropdown. "American/Bogota" (missing 'e') silently creates a broken tenant.
**Why**: Data integrity hazard. Timezone mismatches cascade into queue scheduling, display timestamps, reporting.
**Fix**: Replace with `Select` or `Dropdown` of IANA timezones. Minimum: add regex validation and format hint.
**Suggested command**: `$impeccable harden`

### P1 — No :focus-visible styles
**What**: No custom focus styles defined anywhere. PrimeVue dark theme may override defaults. Keyboard users see no focus ring.
**Why**: Accessibility failure. Sam (screen reader) and keyboard-only users cannot navigate. WCAG 2.4.7 failure.
**Fix**: Add global `:focus-visible { outline: 2px solid #60a5fa; outline-offset: 2px; }` on interactive elements.
**Suggested command**: `$impeccable audit` or `$impeccable polish`

### P1 — Eyebrow contrast fails WCAG AA
**What**: `.eyebrow` uses `color: #93c5fd` at 0.75rem uppercase on `#07111f`. Contrast ~4.3:1 — fails AA for small text.
**Why**: Low-vision users cannot read the brand label. Also flagged by deterministic scan.
**Fix**: Bump to `#a5d4fd` or increase size to ≥1rem.
**Suggested command**: `$impeccable colorize` or `$impeccable polish`

### P2 — Login page lacks visual trust
**What**: Auth card is visually identical to operational cards. No logo, no lock icon, no brand presence. Disclaimer reads as defensive.
**Why**: Authentication is high-stakes. Users need reassurance they're in the right place.
**Fix**: Add brand mark or lock icon above form. Replace disclaimer with confident helper text.
**Suggested command**: `$impeccable delight` or `$impeccable onboard`

## Persona Red Flags

**Alex (Power User)**: No keyboard shortcuts, no batch operations, no column sorting on DataTable, no edit/delete on tenants. Trapped in read-only mode. Will abandon fast.

**Jordan (First-Timer)**: "Foundation" label is meaningless. Admin login disclaimer explains future features instead of guiding current use. No onboarding, no empty-state guidance after login.

**Sam (Accessibility-Dependent)**: No skip-to-content link. No :focus-visible on nav. Eyebrow fails WCAG AA. No aria-live regions for dynamic content updates (broadcast messages, error states).

## Minor Observations

- Missing accents: "Contrasena" → "Contraseña", "administracion" → "administración", "Proximos" → "Próximos", "Modulo" → "Módulo"
- Hardcoded demo data (A-101, Modulo 4) — should look realistic or be removed from dashboard
- `muted` class serves both helper text and status text — semantically different, same treatment
- No page transition animations — instant route changes
- No favicon or meta description in rendered output
- `display-panels` uses `2fr 1fr` but the smaller-content panel gets more space

## Questions to Consider

1. Who is the primary dashboard user — developers, admins, or end users? Should it be role-gated?
2. What happens after tenant creation? No "go to tenant" or "configure queues" follow-up.
3. Is the public display meant for projection? If so, the Card wrapper and grid layout are wrong — it should be full-bleed.
4. Should the "Foundation listo" card exist at all? It becomes obsolete once real features ship.
5. What's the retention strategy for the eyebrow label — brand value or visual noise?
