-- Priority-tiered response deadlines.
--
-- Every report got the same seven days, whatever it was about. A gas leak and a
-- complaint about a park shared a deadline, which makes the deadline useless as
-- a signal: an officer cannot tell from the queue what will hurt someone first,
-- and a breach figure that mixes the two says nothing about whether the
-- dangerous things are being dealt with.
--
-- Priority is derived from the category rather than accepted from the client.
-- enforce_publish_policy already nulls priority on insert for exactly that
-- reason; it now fills it in instead of discarding it. An admin can still
-- override afterwards, and that override is respected.
--
-- The windows live here rather than in TypeScript because the database is the
-- only place both paths meet: reports auto-published by the trusted-reporter
-- trigger and reports approved by an admin have to get the same answer. The app
-- reads the computed sla_due_at back rather than working it out a second time.

-- ---------------------------------------------------------------------------
-- The mapping
-- ---------------------------------------------------------------------------

-- Categories come from CATEGORIES in lib/constants.ts.
--
-- high   — can injure someone today: live wires, gas, no drinking water, and
--          waterlogging, which in Dhaka means both drowning risk and disease.
-- medium — degrades daily life and is the bulk of the queue. Seven days, which
--          is what everything used to get, so the common case is unchanged.
-- low    — genuine but not urgent.
create or replace function public.priority_for_category(p_category text)
returns text
language sql
immutable
set search_path = ''
as $$
  select case p_category
    when 'Public Safety'           then 'high'
    when 'Electricity'             then 'high'
    when 'Gas'                     then 'high'
    when 'Water Supply'            then 'high'
    when 'Drainage / Waterlogging' then 'high'
    when 'Road'                    then 'medium'
    when 'Sewerage'                then 'medium'
    when 'Traffic'                 then 'medium'
    when 'Streetlight'             then 'medium'
    when 'Garbage / Waste'         then 'medium'
    when 'Illegal Construction'    then 'medium'
    when 'Parks'                   then 'low'
    else 'medium'
  end;
$$;

-- Wall-clock days, not working days. A blocked drain does not stop flooding a
-- street because it is Friday, and a citizen watching the countdown should not
-- have to reason about the office calendar to know whether their report is late.
-- (RTI statutory deadlines are a different calculation and do use working days.)
create or replace function public.sla_days_for_priority(p_priority text)
returns integer
language sql
immutable
set search_path = ''
as $$
  select case p_priority
    when 'high'   then 3
    when 'medium' then 7
    when 'low'    then 14
    else 7
  end;
$$;

create or replace function public.sla_due_from(p_priority text, p_from timestamptz)
returns timestamptz
language sql
immutable
set search_path = ''
as $$
  select p_from + make_interval(days => public.sla_days_for_priority(p_priority));
$$;

grant execute on function public.priority_for_category(text) to anon, authenticated;
grant execute on function public.sla_days_for_priority(text) to anon, authenticated;
grant execute on function public.sla_due_from(text, timestamptz) to anon, authenticated;

-- ---------------------------------------------------------------------------
-- Set the deadline when a report is published
-- ---------------------------------------------------------------------------

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

  if coalesce(current_setting('app.trusted_report_write', true), '') = 'on' then
    return new;
  end if;

  if tg_op = 'INSERT' then
    new.status      := 'pending';
    new.admin_note  := null;
    new.assigned_to := null;
    new.assigned_at := null;

    -- Derived, not accepted: a reporter cannot mark their own pothole urgent.
    new.priority    := public.priority_for_category(new.category);

    if new.user_id is not null and public.is_trusted_reporter(new.user_id) then
      new.is_approved    := true;
      new.approved_at    := now();
      new.sla_due_at     := public.sla_due_from(new.priority, now());
      new.auto_published := true;
      new.status         := 'in_progress';
    else
      new.is_approved    := false;
      new.approved_at    := null;
      new.sla_due_at     := null;
      new.auto_published := false;
    end if;

    return new;
  end if;

  -- UPDATE by a citizen on their own report: allowlist, everything else reverts.
  new.id                   := old.id;
  new.user_id              := old.user_id;
  new.status               := old.status;
  new.is_approved          := old.is_approved;
  new.approved_at          := old.approved_at;
  new.auto_published       := old.auto_published;
  new.admin_note           := old.admin_note;
  new.priority             := old.priority;
  new.assigned_to          := old.assigned_to;
  new.assigned_at          := old.assigned_at;
  new.status_updated_at    := old.status_updated_at;
  new.sla_due_at           := old.sla_due_at;
  new.resolution_state     := old.resolution_state;
  new.resolved_at          := old.resolved_at;
  new.dispute_reason       := old.dispute_reason;
  new.routed_authority_key := old.routed_authority_key;
  new.view_count           := old.view_count;
  new.share_count          := old.share_count;
  new.endorse_count        := old.endorse_count;
  new.comment_count        := old.comment_count;
  new.corroboration_count  := old.corroboration_count;
  new.created_at           := old.created_at;

  return new;
end;
$$;

revoke execute on function public.enforce_publish_policy() from anon, authenticated, public;

-- Approval is the other way a report becomes public. This runs for admins too,
-- who are waved past the guard above, so it is a trigger of its own rather than
-- a branch inside one.
--
-- Named so it sorts after reports_publish_policy_upd_trg and before
-- reports_status_change_trg: the guard settles the row, this sets the deadline,
-- then the status logger records what happened.
create or replace function public.set_sla_on_approval()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
  if old.is_approved = false and new.is_approved = true then
    if new.priority is null then
      new.priority := public.priority_for_category(new.category);
    end if;

    -- Only if the caller has not set one explicitly, so an admin can still give
    -- a specific report a hand-picked deadline.
    if new.sla_due_at is null then
      new.sla_due_at := public.sla_due_from(new.priority, now());
    end if;
  end if;

  return new;
end;
$$;

revoke execute on function public.set_sla_on_approval() from anon, authenticated, public;

drop trigger if exists reports_sla_on_approval_trg on public.reports;
create trigger reports_sla_on_approval_trg
  before update on public.reports
  for each row execute function public.set_sla_on_approval();

-- ---------------------------------------------------------------------------
-- Backfill
-- ---------------------------------------------------------------------------
-- Existing reports have no priority. Give them one from their category so the
-- queue is sorted sensibly from the first day this ships. Deadlines already set
-- are left exactly as they are: moving a deadline that a citizen has already
-- been told about would be worse than an inconsistent one.

update public.reports
   set priority = public.priority_for_category(category)
 where priority is null;
