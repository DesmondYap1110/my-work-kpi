# MyKPI — Staff KPI & Performance Appraisal System

MyKPI is a web app for tracking how staff perform. It works out each person's
KPI score from two things:

1. **The work they deliver.** Tasks in projects carry points.
2. **How they are appraised.** Staff fill in a self-assessment, and a reviewer
   marks each objective.

Admins get reports, charts and CSV exports. Staff can see their own score,
tasks and appraisals. A built-in **AI assistant** answers questions about the
data, such as "What is my KPI score?" or "Who is due for review?". It runs on a
**local model**, so it needs no API key.

It works on desktop, iPad and phone. On a phone, a menu bar sits at the bottom
of the screen.

> **Stack:** Laravel 13 · PHP 8.3 · MySQL 8 · Blade + Bootstrap 5 + jQuery ·
> DataTables · ApexCharts · Select2 · laravel/ai + Ollama

<!--
Screenshots: add images to docs/screenshots/ and link them here, e.g.
![Dashboard](docs/screenshots/dashboard.png)
-->

---

## Contents

- [Features](#features)
- [How the KPI score is calculated](#how-the-kpi-score-is-calculated)
- [Roles](#roles)
- [Requirements](#requirements)
- [Installation](#installation)
- [KPI Assistant (local AI)](#kpi-assistant-local-ai)
- [Configuration](#configuration)
- [Testing](#testing)
- [Project structure](#project-structure)

---

## Features

### Human Resource
- Manage **teams**, **positions** and **members**, with profile photos and
  active or inactive status.
- Open each member's **KPI page** to see their score broken down into project
  and objective points.

### KPI Setting (per position)
- Set how much of the score comes from projects and how much from objectives,
  for example **70 / 30**, **50 / 50**, or **0 / 100** for roles with no
  project work.
- Set a **project points target** for each position.
- Choose which **project tags** the position uses.
- Build the objective tree: **categories → objectives → scored items**, each
  with the marks it allows.

### Projects & Tasks
- Projects belong to a team. Each task has an **assignee**, **priority**, due
  date and **progress**, and can have **subtasks** or be marked as a
  **milestone**.
- A task moves through these statuses: **To Do → In Progress → Review →
  Blocked → Done**.
- **Tags** give tasks their points. Only the admin can tag a task, so staff
  cannot set their own score.
- Tasks can have files attached. The admin approves or rejects finished work.

### Appraisals
- The admin starts an appraisal for any period.
- The **member fills in the Employee column** as a self-assessment. The
  **reviewer** then adds their marks.
- **Generate** finalises the appraisal and shares it with the member.
  **Reopen** unlocks it for changes.
- **Review Schedule:** each member can be reviewed weekly, monthly, every few
  months or yearly. The admin is notified a set number of days before a review
  is due.
- **Performance bands** turn a score into a rating, such as *Excellent* or
  *Needs Improvement*. You can edit the bands, the scale and the form parts in
  Settings.
- The appraisal list can be filtered by team and position (multi-select).
- Each review form can be **exported to CSV** and **printed**.

### KPI Report
- **Summary cards:** highest, lowest and average score, and total projects.
- **Performance trend:** monthly KPI chart.
- **Team performance:** teams compared, with each team's top performer.
- **Member ranking.**
- **Project report:** completed tasks, points earned and completion rate.
- **Task breakdown:** points by work category.
- Filter by period, team, position or member. The report is **exported to CSV**
  (by section) or **printed**.
- Composite database indexes keep report queries fast as data grows.

### Settings
- **Theme Setting:** choose a colour preset or set your own colours, and pick
  the login background image. Changes apply straight away without a code
  change or rebuild.
- **Project Tag Setting** and **Project Form Setup**.
- **Change password.** Users can also reset a forgotten password by email.

### KPI Assistant
- A chat bubble on every page. You ask in plain English and the assistant
  looks up the answer in the database.
- It **only reads data**. Its tools can look up members, KPI scores, team
  performance, appraisals (including who is due), tasks and projects. It can
  also explain how to use the app.
- **Access control is enforced in code, not left to the AI:**
  - An admin can ask about anyone.
  - A member can only get answers about themselves.

---

## How the KPI score is calculated

```
Project score   = points from tagged tasks completed in the period
                  ÷ the position's project target

Objective score = marks from generated appraisals that overlap the period
                  (the reviewer's mark counts; if there is none, the employee's
                  own mark is used)

KPI score       = Project score × project weight
                + Objective score × (100% − project weight)
```

If a member has no project work in the period, their objective score counts
for 100%. If a position has no objectives, the project score counts for 100%.

---

## Roles

| Role | Can do |
| --- | --- |
| **Admin** | Everything: set up HR, KPIs, tags, projects and appraisals, see reports, change the theme, ask the assistant about anyone |
| **Member** | See their own KPI, tasks and appraisals, fill in self-assessments, create and plan projects, ask the assistant about themselves |

Admin-only pages are blocked on the server by route middleware. Hiding them in
the menu is not the only protection.

---

## Requirements

- PHP **8.3+** with `openssl`, `pdo_mysql`, `mbstring`, `fileinfo` and `gd`
- Composer 2.7+
- MySQL 8 (or MariaDB 10.6+)
- *(Optional, for the assistant)* [Ollama](https://ollama.com)

No Node or frontend build is needed. The CSS and JS are served as static files
from `public/`.

---

## Installation

```bash
git clone https://github.com/DesmondYap1110/my-work-kpi.git
cd my-work-kpi

composer install
cp .env.example .env
php artisan key:generate
```

Edit `.env` to point at your database:

```dotenv
APP_NAME="MyKPI"
APP_URL=http://localhost:8000

DB_DATABASE=mykpi
DB_USERNAME=root
DB_PASSWORD=
```

Choose the first admin login. If you skip this, the account is
`admin@mykpi.test` and a random password is printed once:

```dotenv
ADMIN_EMAIL=you@example.com
ADMIN_PASSWORD=choose-a-strong-password
```

Then run:

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Open http://localhost:8000 and sign in.

The seeder creates only the admin account, the default positions and the
appraisal form, so you start with no sample data. There are optional demo
seeders:

```bash
php artisan db:seed --class=TeamSeeder
php artisan db:seed --class=KpiObjectiveInfoSeeder
```

---

## KPI Assistant (local AI)

The assistant uses [`laravel/ai`](https://github.com/laravel/ai) with
**Ollama**, so everything runs on your own machine. There is no API key and
nothing is sent to a cloud service.

```bash
# 1. Install Ollama from https://ollama.com, then pull the model
ollama pull qwen2.5:7b

# 2. Make sure it is running (it listens on port 11434)
ollama serve
```

Optional `.env` settings (defaults shown):

```dotenv
ASSISTANT_ENABLED=true
ASSISTANT_PROVIDER=ollama
ASSISTANT_MODEL=qwen2.5:7b
ASSISTANT_TIMEOUT=180
OLLAMA_URL=http://localhost:11434
```

- **Speed:** on an ordinary CPU, an answer takes about 5–25 seconds.
  A GPU makes it much faster.
- **Limit:** each user can ask up to 15 questions per minute.
- **Hiding it:** set `ASSISTANT_ENABLED=false` to remove the chat bubble.
- **Guide text:** its answers about using the app come from
  [`resources/ai/guide.md`](resources/ai/guide.md). Edit that file to change
  them.

---

## Configuration

| What | Where |
| --- | --- |
| Theme colours, login background | **Settings → Theme Setting** (stored in the database) |
| Colour presets | `config/branding.php` → `presets` |
| App name, logo, favicon, fonts | `.env` (`APP_NAME`, `APP_LOGO`, …), see [`docs/BRANDING.md`](docs/BRANDING.md) |
| Appraisal form parts, scale, bands | **Settings → Project Form Setup** |
| Review due notice (days before) | **Appraisal → Schedule** |
| Assistant model and timeout | `config/assistant.php` / `.env` |
| Password reset email | `MAIL_*` in `.env` |

By default `MAIL_MAILER=log`, so password reset emails are written to
`storage/logs/laravel.log` and not actually sent. To send real email, set up
SMTP:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your@gmail.com
```

Run `php artisan config:clear` after changing `.env`.

---

## Testing

```bash
php artisan test
```

The tests cover:

- admin-only access
- KPI score blending and project targets
- appraisal scoring and the review schedule
- theme colours and CSV export
- the assistant's access rules

The assistant tests use a fake AI, so Ollama does not need to be running.

---

## Project structure

```
app/
  Ai/Agents/KpiAssistant.php     the assistant: instructions + tools per role
  Ai/Tools/                      read-only, role-scoped database tools
  Components/Datatables/         one class per AJAX list table
  Http/Controllers/
    Hr/                          teams, positions, members
    Kpi/                         KPI setting, objectives, KPI Report
    Project/                     projects, tasks, tags
    Appraisal/                   appraisals, self-assessment, form setup
    Settings/                    theme setting
  Services/
    StaffKpiScoreService.php     final KPI score (project + objective blend)
    AssessmentScoreService.php   objective marks from appraisals
    KpiReportService.php         report figures and charts
    AppraisalScheduleService.php who is due for review
  Support/Branding.php           theme colours → CSS variables
  Support/Csv.php                safe CSV export (UTF-8 BOM, formula guard)
config/
  assistant.php  branding.php  datatables.php
resources/
  ai/guide.md                    what the assistant knows about using the app
  views/                         Blade views, grouped by module
public/
  assets/                        theme CSS, icons, images
  js/core.js, js/modules/        small JS modules, no bundler
database/
  migrations/  seeders/
tests/
  Feature/  Unit/
```

---

## License

Released under the [MIT License](https://opensource.org/licenses/MIT).
