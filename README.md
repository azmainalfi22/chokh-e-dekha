# Chokh-e-Dekha · চোখে দেখা

A civic-issue reporting platform for Bangladesh. Citizens post photo- and
location-based complaints; admins triage them with SLA visibility; an RTI wizard
helps citizens escalate under the Right to Information Act 2009.

**Your Eyes, Your Voice, Your City.**

## Stack

Next.js 15 (App Router, TypeScript) · Tailwind CSS v4 + shadcn/ui · Supabase
(Postgres / Auth / Storage / Realtime) · Leaflet + OpenStreetMap · Recharts ·
Resend · deployed on Vercel.

## Features

- **Citizens** — multi-step report submission (map pin + GPS + client-compressed
  photos), public feed with filters & search, report detail with a visual
  status tracker and timeline, endorsements, threaded comments, bookmarks,
  sharing, realtime in-app notifications, and a bilingual RTI letter wizard
  (print-to-PDF).
- **Admins** — a dedicated Command Centre with KPIs, an SLA-breach panel,
  submission-trend and city/category charts, a live map with heatmap, a
  moderation queue with bulk approve/reject/status/notes, self-assignment, CSV
  export, survey management, and user role administration.
- **Everywhere** — light & dark themes, responsive 375px→1920px, Bengali +
  Latin typography, row-level security on every table.

## Getting started (local)

```bash
npm install
cp .env.example .env.local     # fill in the values below
npm run dev                     # http://localhost:3000
```

### Environment variables

| Variable | Required | Notes |
|---|---|---|
| `NEXT_PUBLIC_SUPABASE_URL` | ✅ | Supabase project URL |
| `NEXT_PUBLIC_SUPABASE_ANON_KEY` | ✅ | Publishable/anon key (browser-safe) |
| `SUPABASE_SERVICE_ROLE_KEY` | ✅ (server) | Admin/email server actions. **Never** expose to the client |
| `RESEND_API_KEY` | optional | Enables status-change emails; the app works without it |
| `EMAIL_FROM` | optional | Sender for Resend emails |
| `NEXT_PUBLIC_SITE_URL` | ✅ (prod) | Public site URL, used in emails |

## Database

The schema lives in [`supabase/migrations/`](./supabase/migrations) and is
already applied to the live project. To recreate it on a fresh Supabase project,
run the migrations in order, then optionally seed demo data:

```bash
# with the Supabase CLI linked to your project
supabase db push
psql "$DATABASE_URL" -f supabase/seed.sql   # optional demo data
```

Regenerate types after any schema change:

```bash
supabase gen types typescript --project-id <ref> > lib/database.types.ts
```

### Making a user an admin

Sign the user up through the app, then in the Supabase SQL editor:

```sql
update public.profiles set role = 'admin' where id = '<auth-user-id>';
```

(Role changes are guarded — only existing admins or trusted server contexts can
change roles, so a citizen cannot self-promote.)

## Deploying to Vercel

1. Push this repo to GitHub and import it into Vercel.
2. Add the environment variables above in the Vercel project settings.
3. Deploy — the default build command (`next build`) is all that's needed.
4. In Supabase → Authentication → URL Configuration, add your Vercel URL to the
   allowed redirect URLs.

### Recommended production hardening

- Enable **leaked-password protection** in Supabase → Authentication → Policies.
- Keep email confirmation on (it is, by default) so accounts are verified.
- Set a real `RESEND_API_KEY` and a verified `EMAIL_FROM` domain for emails.

## Documentation

- [`SPEC.md`](./SPEC.md) — the full build blueprint (design system, data model,
  feature spec mapped to the functional requirements).
- `supabase/migrations/` — the applied database schema, RLS, triggers, storage.
- `supabase/seed.sql` — demo reports + a sample survey.
- `docs/reference/` — original design screenshots and requirement PDFs.
