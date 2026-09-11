# Laravel Admin Template

A Laravel 10 admin starter built on a Bootstrap 5 (Velzon) theme, with
server-side AJAX datatables, a component-based Blade layout, and a
**centralised branding layer** so a new project can be rebranded without
touching application code.

- **Backend:** Laravel 10, PHP 8.1+
- **Frontend:** Blade + Bootstrap 5 + jQuery + DataTables (no build step required)
- **Datatables:** `app/Components/Datatables` — one list class per module behind a shared AJAX endpoint
- **Filters:** `app/Components/Filters` — filter fields as objects

---

## Starting a new project

```bash
git clone <this-repo> new-project
cd new-project

composer install
cp .env.example .env
php artisan key:generate

# point .env at your database, then:
php artisan migrate --seed
php artisan storage:link

php artisan serve
```

There is **no frontend build step**. `npm install` / `npm run build` are not
required — the theme ships as static CSS in `public/assets/`, and branding is
injected at render time. (Vite is present but unused; wire it up only if you
add your own JS/CSS pipeline.)

---

## Rebranding This Template

> **Full step-by-step runbook: [`docs/BRANDING.md`](docs/BRANDING.md)** — includes
> verification steps, a troubleshooting table, and the contrast guidance for
> picking sidebar colours. The section below is the summary.

Everything below is driven by `config/branding.php`, which reads from `.env`.
**You should not need to edit a single Blade file or stylesheet to rebrand.**

After changing any value:

```bash
php artisan config:clear
```

### How it fits together

```
.env  →  config/branding.php  →  App\Support\Branding  →  <x-branding-styles />
      →  :root { --brand-*: … }  →  public/assets/css/*.css  →  <x-*> components
```

Stylesheets consume tokens as `var(--brand-primary, #1896bd)`. The fallback is
the original theme value, so each sheet still renders correctly on its own and
an unset token simply keeps the shipped default.

### Application name and company

```dotenv
APP_NAME="Acme Portal"
APP_COMPANY_NAME="Acme Sdn Bhd"
APP_TAGLINE="Operations dashboard"
APP_SUPPORT_EMAIL="support@acme.com"
```

The footer line is generated as `© Copyright {year} {company}. All Rights
Reserved.` Override it wholesale with `APP_COPYRIGHT="…"`.

### Logo and favicon

Drop your files into `public/images/` and point the keys at them. Paths are
relative to `public/`, or absolute URLs if you serve assets from a CDN.

```dotenv
APP_LOGO="images/acme-logo.svg"
APP_LOGO_DARK="images/acme-logo-dark.svg"
APP_LOGO_SMALL="images/acme-mark.svg"
APP_FAVICON="images/favicon.ico"
APP_APPLE_TOUCH_ICON="images/apple-touch-icon.png"
APP_OG_IMAGE="images/og-image.png"
```

Only `APP_LOGO` is required. Each slot falls back down a chain
(`login → light → logo`, `sidebar → light → logo`, `mobile → small → logo`),
so one file is enough to get started. Set a slot explicitly when a surface
needs something different:

```dotenv
APP_LOGO_SIDEBAR="images/acme-logo-white.svg"
APP_LOGO_MOBILE="images/acme-mark.svg"
```

### Colours

Pick a preset:

```dotenv
APP_THEME=indigo     # default | indigo | forest | slate
```

Or override individual tokens — these always beat the preset:

```dotenv
APP_PRIMARY_COLOR="#6366F1"
APP_SECONDARY_COLOR="#312E81"
APP_ACCENT_COLOR="#EC4899"
APP_BODY_COLOR="#F8FAFC"
APP_SIDEBAR_COLOR="#1E1B4B"
APP_BUTTON_COLOR="#6366F1"
APP_BUTTON_HOVER_COLOR="#4F46E5"
APP_DANGER_COLOR="#EF4444"
```

**Available tokens** (each becomes `--brand-<name>`):

| Group | Tokens |
| --- | --- |
| Brand | `primary`, `primary-hover`, `secondary`, `secondary-hover`, `accent`, `highlight` |
| Surfaces | `background`, `surface`, `sidebar`, `navbar` |
| Text | `text`, `text-muted`, `label`, `link` |
| Controls | `button`, `button-hover`, `button-text`, `border` |
| Inputs | `input-bg`, `input-text`, `input-border`, `input-focus` |
| Status | `success`, `warning`, `danger`, `danger-hover`, `info`, `pink` |

### Adding a theme preset

Copy a block in the `presets` array of `config/branding.php`, rename it, change
the values, then select it with `APP_THEME=yourpreset`. Presets live in PHP
rather than separate CSS files so they compose with per-token env overrides.

### Background image

```dotenv
APP_BACKGROUND_IMAGE="images/login-bg.jpg"
APP_BACKGROUND_POSITION="center"
APP_BACKGROUND_SIZE="cover"
APP_BACKGROUND_OVERLAY="rgba(0, 0, 0, 0.55)"   # darken the photo
```

Disable it and fall back to a flat colour:

```dotenv
APP_BACKGROUND_IMAGE=""
APP_BACKGROUND_COLOR="#0b0a1f"
```

### Fonts

```dotenv
APP_FONT_FAMILY="'Inter', sans-serif"
APP_FONT_HEADING="'Poppins', sans-serif"
APP_GOOGLE_FONTS="Inter:wght@400;500;600;700|Poppins:wght@600;700"
APP_FONT_SIZE="13px"
```

Separate multiple Google families with `|`. Set `APP_GOOGLE_FONTS=""` to load
nothing at all (self-hosted or system fonts) — no stray request is emitted.

---

## UI components

Components live in `resources/views/components/` and render the theme's own
markup, so they inherit the branding tokens automatically.

```blade
<x-button variant="primary" icon="ri-add-fill">Save</x-button>
<x-button variant="secondary" :href="route('teams.index')">Cancel</x-button>

<x-add-button modal="addTeamModal">Add Team</x-add-button>
<x-add-button :href="route('staff.create')">Add Member</x-add-button>

<x-card title="Filter" padded> … </x-card>

<x-modal id="addTeamModal" title="Add Team" :action="route('teams.store')">
    <x-form.input name="team_name" label="Team Name" required />
</x-modal>

<x-form.select name="team_id" label="Team" :options="$teams" placeholder="Select Team" required />
<x-form.textarea name="remark" label="Remark" :rows="4" />

{{-- Circular image picker with live preview; several per page is fine --}}
<x-form.image-upload name="photo" :src="$photoUrl" />

<x-alert type="success">Saved.</x-alert>
```

Button variants map to the theme's button classes:
`primary`→`btn1`, `secondary`→`btn2`, `success`→`btn3`, `danger`→`btn4`,
`dark`→`btn5`, `muted`→`btn6` (disabled look).

`resources/views/teams/index.blade.php` is the reference implementation — it
went from 63 lines of raw markup to 18 using these components.

### Layout structure

```
resources/views/layouts/
    app.blade.php                       orchestrator only
    guest.blade.php                     login/auth shell
    admin/master/master-style.blade.php CSS imports + branding tokens
    admin/master/master-script.blade.php JS imports
    admin/header/header-main.blade.php
    admin/header/breadcrumbs-main.blade.php
    admin/sidebar/sidebar-main.blade.php
    admin/content/content-main.blade.php
    admin/footer/footer-main.blade.php
    admin/footer/mobile-menu.blade.php
```

Breadcrumbs are controller-driven: implement `App\Interfaces\BreadcrumbInterfaces`
and return the trail from `getBreadcrumbs()`. A view composer in
`AppServiceProvider` feeds it to the layout, so no page declares its own.

---

## Stylesheet layers

| File | Role | Edit it? |
| --- | --- | --- |
| `bootstrap.min.css`, `app.min.css`, `icons.min.css` | Vendor theme | No |
| `theme-dashboard.css` | Structure (spacing, sizing) | Rarely |
| `theme-colors.css` | Skin — reads `var(--brand-*)` | No, use branding config |
| `theme-login.css` | Login page | No |
| `app-custom.css` | **Your deliberate overrides** | Yes |

`app-custom.css` loads last and is the intended home for project-specific CSS.
Keeping the theme sheets untouched means you can still diff them against the
upstream theme.

---

## Configuration dashboard (not implemented — by design)

Branding here is **deploy-time** configuration, which suits a template: it is
version-controlled, reviewable, and identical across every environment of a
given project.

A database-backed settings screen makes sense once non-developers need to
change branding at runtime. The seam already exists — every lookup goes through
`App\Support\Branding::get()`. Adding a `settings` table and checking it there
would make the whole UI runtime-configurable without touching any view. That is
the recommended path if you need it; it was left out to avoid shipping an
unused migration, model, upload handler and permission check in a starter.

Keep the split clear if you add it:

- **`.env`** — infrastructure: `APP_KEY`, database, mail, API credentials
- **Database settings** — appearance: logo, colours, background, display name
