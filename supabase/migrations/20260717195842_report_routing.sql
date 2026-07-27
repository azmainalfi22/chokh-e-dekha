-- Ward + department routing (roadmap #3).
-- Pin the responsible authority (slug, see lib/routing.ts) at report creation.
alter table public.reports
  add column if not exists routed_authority_key text;

create index if not exists reports_routed_authority_idx
  on public.reports (routed_authority_key);

comment on column public.reports.routed_authority_key is
  'Responsible authority slug (see lib/routing.ts), pinned at report creation. Not user-spoofable: set server-side from validated category + city.';
