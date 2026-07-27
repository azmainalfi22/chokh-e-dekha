-- =====================================================================
-- Chokh-e-Dekha core schema
-- Applied to Supabase project kgkgyhezyhikttlnqbyj (chokh-e-dekha).
-- =====================================================================

-- ---- profiles (extends auth.users) ----------------------------------
create table public.profiles (
  id           uuid primary key references auth.users (id) on delete cascade,
  display_name text not null default 'Citizen',
  phone        text,                     -- private, never exposed on public feed
  avatar_url   text,
  city         text,
  role         text not null default 'citizen' check (role in ('citizen','admin')),
  created_at   timestamptz not null default now(),
  updated_at   timestamptz not null default now()
);
comment on table public.profiles is 'Public-facing citizen/admin profile. phone is private.';

-- Admin check helper (SECURITY DEFINER avoids RLS recursion on profiles)
create or replace function public.is_admin()
returns boolean
language sql
stable
security definer
set search_path = public
as $$
  select exists (
    select 1 from public.profiles
    where id = auth.uid() and role = 'admin'
  );
$$;

-- ---- reports --------------------------------------------------------
create table public.reports (
  id                 bigint generated always as identity primary key,
  user_id            uuid references public.profiles (id) on delete set null,
  title              text not null,
  description        text not null,
  category           text not null,
  city_corporation   text not null,
  location_text      text,
  latitude           double precision,
  longitude          double precision,
  status             text not null default 'pending'
                       check (status in ('pending','in_progress','resolved','rejected')),
  priority           text check (priority in ('low','medium','high')),
  is_approved        boolean not null default false,
  approved_at        timestamptz,
  admin_note         text,
  assigned_to        uuid references public.profiles (id) on delete set null,
  assigned_at        timestamptz,
  status_updated_at  timestamptz,
  sla_due_at         timestamptz,
  view_count         integer not null default 0,
  share_count        integer not null default 0,
  endorse_count      integer not null default 0,
  comment_count      integer not null default 0,
  created_at         timestamptz not null default now(),
  updated_at         timestamptz not null default now()
);

create index reports_status_idx        on public.reports (status);
create index reports_category_idx      on public.reports (category);
create index reports_city_idx          on public.reports (city_corporation);
create index reports_created_at_idx    on public.reports (created_at desc);
create index reports_is_approved_idx   on public.reports (is_approved);
create index reports_sla_due_idx       on public.reports (sla_due_at);
create index reports_assigned_idx      on public.reports (assigned_to);
create index reports_geo_idx           on public.reports (latitude, longitude);
create index reports_user_idx          on public.reports (user_id);

-- ---- report_media ---------------------------------------------------
create table public.report_media (
  id           bigint generated always as identity primary key,
  report_id    bigint not null references public.reports (id) on delete cascade,
  storage_path text not null,
  media_type   text not null default 'image' check (media_type in ('image','video')),
  created_at   timestamptz not null default now()
);
create index report_media_report_idx on public.report_media (report_id);

-- ---- report_comments (threaded) -------------------------------------
create table public.report_comments (
  id         bigint generated always as identity primary key,
  report_id  bigint not null references public.reports (id) on delete cascade,
  user_id    uuid not null references public.profiles (id) on delete cascade,
  parent_id  bigint references public.report_comments (id) on delete cascade,
  body       text not null,
  created_at timestamptz not null default now()
);
create index report_comments_report_idx on public.report_comments (report_id);

-- ---- endorsements (upvotes) -----------------------------------------
create table public.report_endorsements (
  report_id  bigint not null references public.reports (id) on delete cascade,
  user_id    uuid not null references public.profiles (id) on delete cascade,
  created_at timestamptz not null default now(),
  primary key (report_id, user_id)
);

-- ---- bookmarks ------------------------------------------------------
create table public.report_bookmarks (
  report_id  bigint not null references public.reports (id) on delete cascade,
  user_id    uuid not null references public.profiles (id) on delete cascade,
  created_at timestamptz not null default now(),
  primary key (report_id, user_id)
);

-- ---- status audit log (feeds the visual timeline) -------------------
create table public.report_status_logs (
  id          bigint generated always as identity primary key,
  report_id   bigint not null references public.reports (id) on delete cascade,
  actor_id    uuid references public.profiles (id) on delete set null,
  from_status text,
  to_status   text not null,
  note        text,
  created_at  timestamptz not null default now()
);
create index report_status_logs_report_idx on public.report_status_logs (report_id);

-- ---- notifications --------------------------------------------------
create table public.notifications (
  id         bigint generated always as identity primary key,
  user_id    uuid not null references public.profiles (id) on delete cascade,
  report_id  bigint references public.reports (id) on delete cascade,
  type       text not null default 'status_change',
  title      text not null,
  body       text,
  is_read    boolean not null default false,
  created_at timestamptz not null default now()
);
create index notifications_user_idx on public.notifications (user_id, is_read);
