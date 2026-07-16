# Chokh-e-Dekha · চোখে দেখা

A civic-issue reporting platform for Bangladesh. Citizens post photo- and
location-based complaints; admins triage them with SLA visibility; an RTI wizard
helps citizens escalate under the Right to Information Act 2009.

> **Status: rebuild in progress.** The old Laravel/PHP prototype has been retired
> (still available in git history and on `main`). This branch is a clean rebuild
> on **Next.js + Supabase**. The backend is fully provisioned; the Next.js app is
> built feature-by-feature per [`SPEC.md`](./SPEC.md).

## Stack

Next.js 15 (App Router, TypeScript) · Tailwind + shadcn/ui · Supabase
(Postgres / Auth / Storage / Realtime) · Leaflet + OpenStreetMap · Recharts ·
Resend · deployed on Vercel.

## What's already done

- ✅ Supabase project provisioned (`kgkgyhezyhikttlnqbyj`, region ap-south-1).
- ✅ Full schema + row-level security + storage buckets applied
  (`supabase/migrations/`).
- ✅ Environment wiring (`.env.example`; real values in gitignored `.env.local`).
- ✅ Build specification ([`SPEC.md`](./SPEC.md)) and reference material
  (`docs/reference/`: original screenshots + requirement PDFs).

## Getting started (once the app is scaffolded)

```bash
npm install
cp .env.example .env.local   # already present locally; add the service-role key
npm run dev                  # http://localhost:3000
```

Add `SUPABASE_SERVICE_ROLE_KEY` from the Supabase dashboard
(Project Settings → API) before running admin/email server actions.

## Documentation

- [`SPEC.md`](./SPEC.md) — full build blueprint: stack, design system, data
  model, feature spec (mapped to the functional requirements), phased plan.
- `supabase/migrations/` — the applied database schema (keep in sync).
- `docs/reference/` — original design screenshots and requirement PDFs.
