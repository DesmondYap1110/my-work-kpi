# Branding Runbook

How to rebrand this template for a new project. Every step below is done in
`.env` or `config/branding.php` — **you should never need to edit a Blade file
or a stylesheet.**

- [The one rule](#the-one-rule)
- [Quick start: rebrand in 5 minutes](#quick-start-rebrand-in-5-minutes)
- [1. Application name and company](#1-application-name-and-company)
- [2. Logo and favicon](#2-logo-and-favicon)
- [3. Colours](#3-colours)
- [4. Login background](#4-login-background)
- [5. Fonts](#5-fonts)
- [6. Creating a reusable preset](#6-creating-a-reusable-preset)
- [Verifying a change](#verifying-a-change)
- [Troubleshooting](#troubleshooting)
- [Token reference](#token-reference)
- [How it works](#how-it-works)

---

## The one rule

After **every** `.env` change:

```bash
php artisan config:clear
```

Laravel caches config. Skip this and nothing appears to change — this is by far
the most common reason a branding edit "doesn't work".

If you also changed a Blade file or component, add:

```bash
php artisan view:clear
```

---

## Quick start: rebrand in 5 minutes

```bash
# 1. Drop your assets in
cp acme-logo.svg      public/images/
cp acme-logo-dark.svg public/images/
cp favicon.ico        public/images/

# 2. Edit .env (see below)

# 3. Apply
php artisan config:clear
```

Minimum viable `.env` block:

```dotenv
APP_NAME="Acme Portal"
APP_COMPANY_NAME="Acme Sdn Bhd"

APP_LOGO="images/acme-logo.svg"
APP_LOGO_DARK="images/acme-logo-dark.svg"
APP_FAVICON="images/favicon.ico"

APP_THEME=indigo
```

That's a complete rebrand. Everything else is refinement.

---

## 1. Application name and company

```dotenv
APP_NAME="Acme Portal"          # browser title, emails, page titles
APP_SHORT_NAME="Acme"           # optional; defaults to APP_NAME
APP_COMPANY_NAME="Acme Sdn Bhd" # footer copyright
APP_TAGLINE="Operations dashboard"
APP_DESCRIPTION="Internal operations portal for Acme."
APP_SUPPORT_EMAIL="support@acme.com"
APP_WEBSITE_URL="https://acme.com"
```

The footer renders as:

> © Copyright 2026 Acme Sdn Bhd. All Rights Reserved.

To change the wording entirely:

```dotenv
APP_COPYRIGHT="Acme Sdn Bhd — Confidential"
```

`APP_DESCRIPTION` also feeds the `<meta name="description">` and Open Graph tags.

---

## 2. Logo and favicon

Put files in `public/images/`. Paths are relative to `public/`, or absolute URLs
if you serve assets from a CDN.

```dotenv
APP_LOGO="images/acme-logo.svg"
APP_FAVICON="images/favicon.ico"
```

**Only `APP_LOGO` is required.** Every other slot falls back to it:

| Slot | Env key | Falls back to |
| --- | --- | --- |
| Main | `APP_LOGO` | — |
| Login page | `APP_LOGO_LOGIN` | light → main |
| Sidebar (expanded) | `APP_LOGO_SIDEBAR` | light → main |
| Sidebar (collapsed) / mobile | `APP_LOGO_MOBILE` | small → main |
| Navbar | `APP_LOGO_NAVBAR` | light → main |
| Light variant | `APP_LOGO_LIGHT` | main |
| Dark variant | `APP_LOGO_DARK` | main |
| Small / mark | `APP_LOGO_SMALL` | main |

Override a slot only when it genuinely differs. The most common case is a white
logo for the dark sidebar:

```dotenv
APP_LOGO="images/acme-logo.svg"              # dark logo, for light backgrounds
APP_LOGO_SIDEBAR="images/acme-logo-white.svg" # white, for the dark sidebar
APP_LOGO_MOBILE="images/acme-mark.svg"        # square mark for the collapsed rail
```

Icons:

```dotenv
APP_FAVICON="images/favicon.ico"
APP_APPLE_TOUCH_ICON="images/apple-touch-icon.png"   # 180×180 PNG
APP_OG_IMAGE="images/og-image.png"                   # 1200×630 for link previews
```

### Logo sizing gotcha

If your logo looks small or floats oddly in the sidebar, check for dead space
inside the SVG. Crop the `viewBox` to the actual artwork — padding baked into
the file makes the rendered mark smaller and pushes the menu down. Same applies
to whitespace in a PNG.

---

## 3. Colours

### Option A — pick a preset (fastest)

```dotenv
APP_THEME=indigo
```

Available: `default`, `indigo`, `forest`, `slate`.

### Option B — override individual tokens

These always beat the preset, so you can combine both — start from a preset and
adjust:

```dotenv
APP_THEME=indigo
APP_PRIMARY_COLOR="#6366F1"
APP_SIDEBAR_COLOR="#312E81"
APP_BODY_COLOR="#F8FAFC"
```

### The four that matter most

Change these first — they carry most of the visual identity:

| Key | Controls |
| --- | --- |
| `APP_PRIMARY_COLOR` | Buttons, links, focus rings, active states |
| `APP_SECONDARY_COLOR` | Table headers, dashboard tiles |
| `APP_SIDEBAR_COLOR` | Sidebar background |
| `APP_BODY_COLOR` | Page background behind the cards |

### Contrast: read this before going lighter

The sidebar, the active menu pill, and the primary colour all compete. Making
the sidebar lighter moves it *towards* the primary colour, so the active item
starts to disappear.

Measured on the indigo preset:

| `APP_SIDEBAR_COLOR` | Result |
| --- | --- |
| `#1E1B4B` | Near-black — indistinguishable from the default navy |
| `#312E81` | **Clearly indigo, good contrast both ways.** Recommended |
| `#4338CA` | Brighter, but the active pill starts blending in |
| `#4F46E5` | Too close to primary — active item stops standing out |

If you want a lighter sidebar than `#312E81`, darken the accent so the active
pill re-separates instead:

```dotenv
APP_SIDEBAR_COLOR="#4338CA"
APP_ACCENT_COLOR="#1E1B4B"
```

Note that table headers use `secondary`, so by default they match the sidebar.
Set `APP_SECONDARY_COLOR` separately if you want them to differ.

---

## 4. Login background

```dotenv
APP_BACKGROUND_IMAGE="images/login-bg.jpg"
APP_BACKGROUND_POSITION="center"
APP_BACKGROUND_SIZE="cover"
APP_BACKGROUND_ATTACHMENT="fixed"
APP_BACKGROUND_OVERLAY="rgba(0, 0, 0, 0.55)"
```

`APP_BACKGROUND_OVERLAY` tints the photo — essential when a busy image fights
the login card. Use a higher alpha to darken further.

Disable the image and use a flat colour:

```dotenv
APP_BACKGROUND_IMAGE=""
APP_BACKGROUND_COLOR="#0b0a1f"
```

On screens under 992px the theme intentionally drops the image and uses the
sidebar colour instead — that is by design, not a bug.

---

## 5. Fonts

**Two keys, and you need both:**

```dotenv
APP_FONT_FAMILY="'Inter', sans-serif"          # what CSS uses
APP_GOOGLE_FONTS="Inter:wght@400;500;600;700"  # what actually gets loaded
```

Setting only `APP_FONT_FAMILY` silently falls back to the system sans-serif,
because nothing fetches the font. (This exact bug existed in the original app —
`Roboto` was declared but never loaded.)

Separate heading and body fonts:

```dotenv
APP_FONT_FAMILY="'Inter', sans-serif"
APP_FONT_HEADING="'Poppins', sans-serif"
APP_GOOGLE_FONTS="Inter:wght@400;500;600;700|Poppins:wght@600;700"
```

Multiple families are separated with `|`. Self-hosting or using system fonts:

```dotenv
APP_GOOGLE_FONTS=""
```

No request is emitted when this is blank.

---

## 6. Creating a reusable preset

For a brand you'll use more than once, put it in `config/branding.php` rather
than `.env` — it's version-controlled and travels with the repo.

1. Open `config/branding.php`
2. Copy the whole `'default' => [...]` block inside `presets`
3. Rename and edit it:

```php
'acme' => [
    'primary'        => '#6366F1',
    'primary-hover'  => '#4F46E5',
    'secondary'      => '#312E81',
    'sidebar'        => '#312E81',
    'background'     => '#F8FAFC',
    // … keep all keys from 'default'
],
```

4. Select it:

```dotenv
APP_THEME=acme
```

Keep every key the `default` preset has. Missing keys fall through to the
stylesheet default, which may not match your palette.

---

## Verifying a change

```bash
php artisan config:clear

# confirm what the app actually resolved
php artisan tinker --execute="print_r(App\Support\Branding::colors());"
```

Then in the browser: **hard-refresh** (Ctrl+F5) and check the computed value in
DevTools:

```js
getComputedStyle(document.documentElement).getPropertyValue('--brand-sidebar')
```

If the token is right but the UI looks unchanged, the colour is genuinely being
applied — it's a perception problem, not a bug. See the first troubleshooting
row below.

The template also ships tests covering this contract:

```bash
php artisan test --filter=BrandingTest
```

---

## Troubleshooting

| Symptom | Cause | Fix |
| --- | --- | --- |
| Nothing changed at all | Config cache | `php artisan config:clear` |
| Colour "didn't change" but the token is correct | Two dark colours look alike (e.g. `#1E1B4B` vs `#000f2d`) | Test with an obvious colour like `#7C2D12` to confirm wiring, then pick a value with real separation |
| Stale styles after a Blade edit | View cache | `php artisan view:clear` |
| Font ignored | `APP_GOOGLE_FONTS` not set | Set both font keys — see [§5](#5-fonts) |
| Logo looks small / big gap under it | Dead space inside the SVG `viewBox` | Crop the `viewBox` to the artwork |
| Active menu item hard to see | Sidebar too close to primary | See the contrast table in [§3](#3-colours) |
| Background image missing on mobile | Intentional — theme drops it under 992px | Not a bug |
| Change works locally, not in production | Production config cache | `php artisan config:cache` on deploy |

---

## Token reference

Each becomes a `--brand-<name>` CSS variable.

| Group | Token | Env key |
| --- | --- | --- |
| **Brand** | `primary` | `APP_PRIMARY_COLOR` |
| | `primary-hover` | `APP_PRIMARY_HOVER_COLOR` |
| | `secondary` | `APP_SECONDARY_COLOR` |
| | `secondary-hover` | `APP_SECONDARY_HOVER_COLOR` |
| | `accent` | `APP_ACCENT_COLOR` |
| | `highlight` | `APP_HIGHLIGHT_COLOR` |
| **Surfaces** | `background` | `APP_BODY_COLOR` |
| | `surface` | `APP_SURFACE_COLOR` |
| | `sidebar` | `APP_SIDEBAR_COLOR` |
| | `navbar` | `APP_NAVBAR_COLOR` |
| **Text** | `text` | `APP_TEXT_COLOR` |
| | `text-muted` | `APP_TEXT_MUTED_COLOR` |
| | `label` | `APP_LABEL_COLOR` |
| | `link` | `APP_LINK_COLOR` |
| **Controls** | `button` | `APP_BUTTON_COLOR` |
| | `button-hover` | `APP_BUTTON_HOVER_COLOR` |
| | `button-text` | `APP_BUTTON_TEXT_COLOR` |
| | `border` | `APP_BORDER_COLOR` |
| **Inputs** | `input-bg` | `APP_INPUT_BG_COLOR` |
| | `input-text` | `APP_INPUT_TEXT_COLOR` |
| | `input-border` | `APP_INPUT_BORDER_COLOR` |
| | `input-focus` | `APP_INPUT_FOCUS_COLOR` |
| **Status** | `success` | `APP_SUCCESS_COLOR` |
| | `warning` | `APP_WARNING_COLOR` |
| | `danger` | `APP_DANGER_COLOR` |
| | `danger-hover` | `APP_DANGER_HOVER_COLOR` |
| | `info` | `APP_INFO_COLOR` |
| | `pink` | `APP_PINK_COLOR` |

---

## How it works

```
.env
  ↓
config/branding.php          resolution: env → active preset → default preset
  ↓
App\Support\Branding         resolves URLs, colours, background rules
  ↓
<x-branding-styles />        emits :root { --brand-*: … }
  ↓
public/assets/css/*.css      consumes var(--brand-primary, #1896bd)
  ↓
resources/views/components/  <x-button>, <x-card>, <x-modal>, <x-form.*>
```

Two consequences worth knowing:

**No build step.** Tokens are injected server-side at render time, so a colour
change needs `config:clear` — not `npm run build`. Vite exists in the repo but
is unused.

**Fallbacks are built in.** Stylesheets read `var(--brand-primary, #1896bd)`.
An unset token silently keeps the shipped default, and each stylesheet still
renders correctly on its own.

### Which files to edit

| File | Edit it? |
| --- | --- |
| `.env` | Yes — per-project values |
| `config/branding.php` | Yes — defaults and presets |
| `public/assets/css/app-custom.css` | Yes — your own CSS overrides |
| `public/assets/css/theme-*.css` | **No** — theme ports; edit branding config instead |
| `resources/views/layouts/**` | **No** — already fully token-driven |

`app-custom.css` loads last and is the intended home for project-specific CSS.
Keeping the theme sheets untouched means they can still be diffed against the
upstream theme.
