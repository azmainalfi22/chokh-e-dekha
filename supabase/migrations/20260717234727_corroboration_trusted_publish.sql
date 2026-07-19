-- Community corroboration + trusted-reporter auto-publish (roadmap #10).
-- Corroboration = "I see this too": an eyewitness signal from OTHER citizens,
-- distinct from endorsement (support). Trusted reporters (proven history)
-- skip the moderation queue — the scalable answer to a manual-approval
-- bottleneck. The same trigger closes a hole where a citizen could insert
-- a report with is_approved=true directly against the API.

-- ---- corroborations -------------------------------------------------
create table public.report_corroborations (
  report_id  bigint not null references public.reports (id) on delete cascade,
  user_id    uuid   not null references public.profiles (id) on delete cascade,
  created_at timestamptz not null default now(),
  primary key (report_id, user_id)
);
create index report_corroborations_user_idx on public.report_corroborations (user_id);

alter table public.report_corroborations enable row level security;

create policy "corroborations readable"
  on public.report_corroborations for select using (true);
-- Only on approved reports, and never your own — you can't corroborate yourself.
create policy "users corroborate others' approved reports"
  on public.report_corroborations for insert
  with check (
    auth.uid() = user_id
    and exists (
      select 1 from public.reports r
      where r.id = report_id
        and r.is_approved = true
        and r.user_id is distinct from auth.uid()
    )
  );
create policy "users remove own corroboration"
  on public.report_corroborations for delete using (auth.uid() = user_id);

alter table public.reports
  add column corroboration_count integer not null default 0,
  add column auto_published      boolean not null default false;

create or replace function public.sync_corroboration_count()
returns trigger language plpgsql
security definer
set search_path = public as $$
begin
  if (tg_op = 'INSERT') then
    update public.reports set corroboration_count = corroboration_count + 1 where id = new.report_id;
  elsif (tg_op = 'DELETE') then
    update public.reports set corroboration_count = greatest(corroboration_count - 1, 0) where id = old.report_id;
  end if;
  return null;
end;
$$;
revoke execute on function public.sync_corroboration_count() from anon, authenticated, public;

create trigger corroborations_count_trg
  after insert or delete on public.report_corroborations
  for each row execute function public.sync_corroboration_count();

-- ---- trusted reporters ----------------------------------------------
-- Earned, not granted: at least 3 approved reports and no rejections.
create or replace function public.is_trusted_reporter(p_user_id uuid)
returns boolean
language sql
stable
security definer
set search_path = public
as $$
  select
    (select count(*) from public.reports
      where user_id = p_user_id and is_approved = true) >= 3
    and not exists (
      select 1 from public.reports
      where user_id = p_user_id and status = 'rejected'
    );
$$;
grant execute on function public.is_trusted_reporter(uuid) to anon, authenticated;

-- ---- publish policy at insert ---------------------------------------
-- Trusted reporters auto-publish with the SLA clock started; everyone else
-- gets moderation defaults FORCED server-side (no client-set is_approved /
-- status / admin fields). Service-role contexts (auth.uid() is null, e.g.
-- seeds) and admins pass through untouched.
create or replace function public.enforce_publish_policy()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
  if auth.uid() is null or public.is_admin() then
    return new;
  end if;

  new.status      := 'pending';
  new.admin_note  := null;
  new.priority    := null;
  new.assigned_to := null;
  new.assigned_at := null;

  if new.user_id is not null and public.is_trusted_reporter(new.user_id) then
    new.is_approved    := true;
    new.approved_at    := now();
    new.sla_due_at     := now() + interval '7 days';
    new.auto_published := true;
  else
    new.is_approved    := false;
    new.approved_at    := null;
    new.sla_due_at     := null;
    new.auto_published := false;
  end if;
  return new;
end;
$$;
revoke execute on function public.enforce_publish_policy() from anon, authenticated, public;

create trigger reports_publish_policy_trg
  before insert on public.reports
  for each row execute function public.enforce_publish_policy();
