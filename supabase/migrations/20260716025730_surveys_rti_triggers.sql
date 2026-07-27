-- ---- surveys --------------------------------------------------------
create table public.surveys (
  id          bigint generated always as identity primary key,
  title       text not null,
  description text,
  questions   jsonb not null default '[]'::jsonb,   -- [{id,label,type,options[]}]
  is_active   boolean not null default true,
  created_by  uuid references public.profiles (id) on delete set null,
  created_at  timestamptz not null default now()
);

create table public.survey_responses (
  id         bigint generated always as identity primary key,
  survey_id  bigint not null references public.surveys (id) on delete cascade,
  user_id    uuid references public.profiles (id) on delete set null,
  answers    jsonb not null default '{}'::jsonb,
  created_at timestamptz not null default now()
);
create index survey_responses_survey_idx on public.survey_responses (survey_id);

-- ---- RTI letters (persisted so citizens can revisit / reprint) ------
create table public.rti_letters (
  id          bigint generated always as identity primary key,
  user_id     uuid not null references public.profiles (id) on delete cascade,
  report_id   bigint references public.reports (id) on delete set null,
  authority   text not null,
  subject     text not null,
  body        text not null,
  language    text not null default 'en' check (language in ('en','bn')),
  created_at  timestamptz not null default now()
);
create index rti_letters_user_idx on public.rti_letters (user_id);

-- =====================================================================
-- Triggers & functions
-- =====================================================================

-- Auto-create a profile row when a new auth user signs up
create or replace function public.handle_new_user()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
  insert into public.profiles (id, display_name, phone)
  values (
    new.id,
    coalesce(new.raw_user_meta_data ->> 'display_name', split_part(new.email, '@', 1)),
    new.raw_user_meta_data ->> 'phone'
  )
  on conflict (id) do nothing;
  return new;
end;
$$;

create trigger on_auth_user_created
  after insert on auth.users
  for each row execute function public.handle_new_user();

-- keep updated_at fresh
create or replace function public.touch_updated_at()
returns trigger language plpgsql
set search_path = '' as $$
begin
  new.updated_at = now();
  return new;
end;
$$;

create trigger reports_touch_updated_at
  before update on public.reports
  for each row execute function public.touch_updated_at();

create trigger profiles_touch_updated_at
  before update on public.profiles
  for each row execute function public.touch_updated_at();

-- Maintain endorsement / comment counters
create or replace function public.sync_endorse_count()
returns trigger language plpgsql
set search_path = public as $$
begin
  if (tg_op = 'INSERT') then
    update public.reports set endorse_count = endorse_count + 1 where id = new.report_id;
  elsif (tg_op = 'DELETE') then
    update public.reports set endorse_count = greatest(endorse_count - 1, 0) where id = old.report_id;
  end if;
  return null;
end;
$$;
create trigger endorsements_count_trg
  after insert or delete on public.report_endorsements
  for each row execute function public.sync_endorse_count();

create or replace function public.sync_comment_count()
returns trigger language plpgsql
set search_path = public as $$
begin
  if (tg_op = 'INSERT') then
    update public.reports set comment_count = comment_count + 1 where id = new.report_id;
  elsif (tg_op = 'DELETE') then
    update public.reports set comment_count = greatest(comment_count - 1, 0) where id = old.report_id;
  end if;
  return null;
end;
$$;
create trigger comments_count_trg
  after insert or delete on public.report_comments
  for each row execute function public.sync_comment_count();

-- Log status transitions + notify the reporter
create or replace function public.log_status_change()
returns trigger language plpgsql
security definer set search_path = public as $$
begin
  if (new.status is distinct from old.status) then
    new.status_updated_at = now();

    insert into public.report_status_logs (report_id, actor_id, from_status, to_status, note)
    values (new.id, auth.uid(), old.status, new.status, new.admin_note);

    if (new.user_id is not null) then
      insert into public.notifications (user_id, report_id, type, title, body)
      values (
        new.user_id, new.id, 'status_change',
        'Report status updated',
        'Your report "' || new.title || '" is now ' || new.status || '.'
      );
    end if;
  end if;
  return new;
end;
$$;
create trigger reports_status_change_trg
  before update on public.reports
  for each row execute function public.log_status_change();

-- Trigger-only SECURITY DEFINER functions are not exposed as RPC
revoke execute on function public.handle_new_user()   from anon, authenticated, public;
revoke execute on function public.log_status_change() from anon, authenticated, public;
