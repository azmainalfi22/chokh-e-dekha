# Chokh-e-Dekha — Build Specification

> চোখে দেখা — "seen with the eyes." A civic-issue reporting platform for
> Bangladesh. Citizens post photo + location complaints; admins triage them
> with SLA visibility; an RTI wizard helps citizens escalate under the Right
> to Information Act 2009.

This document is the single source of truth for the rebuild. It is written so
that a coding agent (Fable 5) can implement the app feature-by-feature without
re-deriving decisions. The database and Supabase project already exist (see
[Backend, already provisioned](#backend-already-provisioned)).

---

## 1. Stack

| Layer | Choice |
|---|---|
| Framework | **Next.js 15** (App Router) + **TypeScript** |
| Styling | **Tailwind CSS** + **shadcn/ui** (Radix primitives) |
| Backend | **Supabase** — Postgres, Auth, Storage, Realtime |
| Data access | `@supabase/ssr` (server + browser clients) |
| Maps | **Leaflet + OpenStreetMap** via `react-leaflet` (no Google billing) |
| Charts | **Recharts** |
| Forms | **react-hook-form** + **zod** |
| Image compression | `browser-image-compression` (client-side, before upload) |
| Email | **Resend** (server, transactional) |
| Icons | `lucide-react` |
| Deployment | **Vercel** (app) + **Supabase** (backend) |

Node 20+. Package manager: npm (lockfile committed).

---

## 2. Backend, already provisioned

A live Supabase project is set up and the full schema is applied. Do **not**
recreate it — build against it.

- **Project ref:** `kgkgyhezyhikttlnqbyj`
- **URL:** `https://kgkgyhezyhikttlnqbyj.supabase.co`
- **Region:** ap-south-1 (Mumbai — closest to Bangladesh)
- **Publishable (anon) key:** in `.env.local` (`NEXT_PUBLIC_SUPABASE_ANON_KEY`)
- **Service-role key:** NOT committed. Pull it from the Supabase dashboard
  (Project Settings → API) into `.env.local` before running server actions
  that need elevated access (admin RPC, email jobs).

Schema lives in `supabase/migrations/` (already applied):

- `0001_core_schema.sql` — profiles, reports, media, comments, endorsements,
  bookmarks, status logs, notifications; all filter columns indexed.
- `0002_surveys_rti_triggers.sql` — surveys, survey_responses, rti_letters;
  triggers: auto-profile on signup, `updated_at`, endorse/comment counters,
  status-change logging + notification fan-out.
- `0003_rls_policies.sql` — row-level security on every table.
- `0004_storage_buckets.sql` — `report-media` and `avatars` public buckets.

Regenerate TypeScript types any time the schema changes:
`supabase gen types typescript --project-id kgkgyhezyhikttlnqbyj > lib/database.types.ts`
(or via the Supabase MCP `generate_typescript_types`).

### Data model summary

- **profiles** — extends `auth.users`. `role` ∈ {citizen, admin}. `phone` is
  private and must never be rendered on public surfaces.
- **reports** — the core entity. Lifecycle status ∈ {pending, in_progress,
  resolved, rejected}. `is_approved` gates public visibility (moderation).
  `sla_due_at` drives breach detection. Counters (`endorse_count`,
  `comment_count`, `view_count`, `share_count`) are denormalised and kept in
  sync by triggers where applicable.
- **report_media** — 1‑to‑many photos/videos → Supabase Storage paths.
- **report_comments** — threaded (`parent_id`).
- **report_endorsements / report_bookmarks** — composite PK (report, user).
- **report_status_logs** — audit trail; feeds the visual status timeline.
- **notifications** — per-user, `is_read`; delivered live via Realtime.
- **surveys / survey_responses** — questions stored as JSONB.
- **rti_letters** — persisted generated letters (en/bn) for reprint.

### Security model (RLS — do not bypass on the client)

- Public can read a report only when `is_approved = true`. Owners and admins
  see their own/all rows.
- Citizens insert reports as themselves; may edit only while `pending`.
- Admins (`is_admin()`) manage everything.
- Bookmarks, notifications, RTI letters are private to the user.
- Anything needing elevation (rare) goes through a **server action** using the
  service-role key — never the browser.

---

## 3. Design system

The visual identity from the original build is kept and formalised. It reads as
a warm, Bangladeshi civic brand with a genuine dark mode.

### Brand & palette

Warm orange→red as primary, with semantic status colors.

```
Primary gradient   #F97316 (orange-500) → #EF4444 (red-500)
Amber accent       #F59E0B   (pending / warnings)
Green              #10B981   (resolved / success)
Purple             #7C3AED   (users / secondary accent)
Blue               #3B82F6   (in-progress)
Rose/Red           #EF4444   (rejected / SLA breach)
```

Status → color map (use everywhere consistently):
`pending` = amber, `in_progress` = blue, `resolved` = green,
`rejected` = rose, `SLA breached` = red/pulsing.

Expose these as CSS variables / Tailwind theme tokens so light and dark both
resolve correctly (NFR-08). Light mode = warm cream surfaces (`#FFF7ED`-ish)
on white cards; dark mode = near-black (`#0A0A0B`) with soft glows behind the
gradient cards (see reference screenshots in `docs/reference/`).

### Typography (NFR-07)

- Latin UI: **Inter** (`next/font`).
- Bengali: **Noto Sans Bengali** or **Hind Siliguri** — used for the wordmark
  (চোখে দেখা), Bangla RTI output, and any Bangla UI. Ship a Bengali-capable
  font stack so bilingual text renders cleanly.

### Core components (build once, reuse)

- **KPI card** — gradient background, big number, label (see dashboard shots).
- **StatusBadge** — pill; color by status.
- **StatusTracker** — horizontal Pending → In Progress → Resolved progress bar,
  filled to the current stage (FR-04).
- **ReportCard** — feed item: photo, title, category/city chips, status,
  endorse/comment/share/bookmark actions.
- **SlaBadge** — shows time remaining / "Overdue by N" when breached.
- **FilterBar** — status/category/city selects + search (debounced).
- **MapView** — Leaflet map with markers + optional heatmap layer.
- **Stepper** — multi-step wizard shell (submit report, RTI).
- **EmptyState, Toast, ConfirmDialog, DataTable** (admin), **ThemeToggle**.

### Non-functional targets

- Responsive 375 → 1920 px (NFR-02); mobile-first.
- Semantic HTML, ARIA labels, WCAG-AA contrast (NFR-03).
- Reports index < 3 s for 200 rows — server components + indexed queries +
  enforced pagination (NFR-01/05).

---

## 4. Reference constants

Seed these as app constants (`lib/constants.ts`) and, where useful, as data.

- **City corporations / cities** (from the original data): Dhaka North, Dhaka
  South, Dhaka North City Corporation, Dhaka South City Corporation,
  Chattogram / Chittagong City Corporation, Sylhet City Corporation, Rangpur
  City Corporation, Rajshahi, Khulna, Barishal, Narayanganj, Gazipur,
  Mymensingh, Bogura City Corporation, Cumilla. (Normalise duplicates.)
- **Categories:** Road, Garbage / Waste, Streetlight, Drainage / Waterlogging,
  Water Supply, Sewerage, Illegal Construction, Traffic, Public Safety,
  Electricity, Parks, Other.
- **Default SLA:** configurable; default **7 days** from approval →
  `sla_due_at = approved_at + interval '7 days'`.

---

## 5. Feature spec (mapped to functional requirements)

Public routes are under `(public)`, authed citizen area under `(app)`, admin
under `(admin)`.

### Auth & roles — FR-01, NFR-04
- Supabase email/password auth. On signup, a `profiles` row is auto-created
  (trigger). Collect display name + optional phone (private).
- Middleware refreshes the session and guards `(app)` and `(admin)`. Admin
  guard checks `profiles.role = 'admin'`.
- Reporter safety: public surfaces show `display_name` only — never phone.

### Submit report — FR-02
- Multi-step wizard (`Stepper`): **1** category + city + title + description →
  **2** location (Leaflet pin + "Use my location" geolocation; store
  `location_text`, `latitude`, `longitude`) + evidence (drag-drop, client
  compress, upload to `report-media`) → **3** review + submit.
- New reports start `is_approved = false, status = 'pending'`.

### Public feed — FR-03, FR-04
- Paginated list of approved reports; filter by status/category/city + search.
- Each card shows the `StatusTracker`. Detail page shows full timeline from
  `report_status_logs`, media gallery, and engagement.

### Notifications — FR-05
- In-app: subscribe to `notifications` via Supabase Realtime; bell with unread
  count; mark-as-read. (Trigger already inserts a notification on status
  change.)
- Email: a server action / edge function sends via Resend on status change.

### Engagement — FR-06
- Endorse (upvote), threaded comments, bookmark, share (Web Share API + copy
  link). Counters kept live by triggers.

### Admin panel — FR-07, FR-08, FR-09, FR-15, FR-16
- Separate `(admin)` shell (sidebar like the reference dark dashboard).
- Report queue (`DataTable`): approve / reject / change status / add public
  `admin_note`; **bulk actions** on selected rows; self-assign (`assigned_to`);
  **CSV export** of the filtered set.
- On approval, set `is_approved`, `approved_at`, and compute `sla_due_at`.

### SLA — FR-10
- `sla_due_at` set at approval. A scheduled job (Supabase cron / edge function)
  flags overdue open reports; UI highlights breaches (red/pulsing `SlaBadge`).

### Command Centre — FR-11
- KPI cards (total / pending / resolved / users), SLA-breach panel, weekly
  submission trend (Recharts line/bar), city & category breakdowns.

### Map — FR-12
- Leaflet/OSM map of geo-tagged reports with markers; optional heatmap toggle;
  filter by status/category/date.

### RTI wizard — FR-13, FR-14
- Multi-step: pick authority (presets for common Bangladeshi public bodies),
  subject, details (optionally prefilled from a report), applicant info.
- Generate an RTI Act 2009-compliant letter in **English or Bangla**.
- Save to `rti_letters`; **print-to-PDF** via a print-styled route
  (`window.print()`), no server PDF dependency.

---

## 6. Suggested directory layout

```
app/
  (public)/            landing, feed, report detail, auth
  (app)/               dashboard, submit, my-reports, bookmarks, rti, profile
  (admin)/             dashboard, reports, users, surveys, map
  api/ or actions      server actions (email, csv, admin ops)
components/
  ui/                  shadcn primitives
  reports/  admin/  map/  charts/  rti/
lib/
  supabase/            server.ts, client.ts, middleware.ts
  database.types.ts    generated
  constants.ts  sla.ts  utils.ts
supabase/migrations/   (already applied — keep in sync)
```

---

## 7. Phased build order

1. **Scaffold** — create-next-app (TS, Tailwind, App Router), shadcn init,
   Supabase clients + middleware, design tokens & fonts, base layout + theme
   toggle, generate `database.types.ts`.
2. **Auth** — signup/login/logout, session middleware, profile bootstrap,
   role-gated route groups.
3. **Citizen core** — submit wizard (map + upload), public feed + filters +
   pagination, report detail + status tracker + timeline.
4. **Engagement + notifications** — endorse/comment/bookmark/share; realtime
   bell; Resend email on status change.
5. **Admin command centre** — queue, moderation, bulk actions, assignment,
   SLA compute + breach flagging, KPIs + charts + map, CSV export.
6. **RTI wizard** — bilingual generator + print-to-PDF; save/reprint.
7. **Surveys** — admin create, public respond, admin view results.
8. **Landing page** — public hero + how-it-works + live stats.
9. **Polish & deploy** — a11y/responsive/dark-mode pass, seed demo data, deploy
   to Vercel; set env vars; smoke-test.

---

## 8. Design tooling note

The design is implemented **inside the build** as a coded Tailwind/shadcn design
system (tokens above), not imported from an external design tool. This app is
component-heavy and stateful, so coded components are the right medium. The one
optional exception is the marketing landing hero, where an AI/Claude-generated
visual could seed the layout — but it is equally buildable in-code and is
included in phase 8.

---

## 9. Out of scope for v1

- Encrypted whistleblower / anonymous disclosure module (acknowledged gap;
  schema seam can be added later). Reports require an authenticated author;
  public display uses display name only.
