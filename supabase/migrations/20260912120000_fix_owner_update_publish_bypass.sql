-- Close the self-publish hole on the report UPDATE path.
--
-- "owner edits own pending report" restricted WHICH rows an author could target
-- (their own, still pending) but its WITH CHECK only required that the row still
-- belonged to them:
--
--   using      (auth.uid() = user_id and status = 'pending')
--   with check (auth.uid() = user_id)
--
-- Nothing said which COLUMNS could change. An author could therefore run
--
--   update reports set is_approved = true where id = <their own pending report>
--
-- and publish it themselves, bypassing the moderation that enforce_publish_policy
-- carefully applies on insert. That trigger was declared BEFORE INSERT only, so it
-- never saw the update. The same gap let an author set priority, assigned_to,
-- admin_note, sla_due_at and auto_published on their own report.
--
-- Two fixes, deliberately overlapping:
--
--   1. enforce_publish_policy now also runs BEFORE UPDATE, where it reverts every
--      column a citizen has no business changing back to its previous value. This
--      is the real guard, because it enumerates what may change rather than trying
--      to enumerate every way in.
--   2. The policy's WITH CHECK gains the column guard it was missing, so a direct
--      attempt still fails even if the trigger were ever dropped.
--
-- Platform-controlled writes are exempted through a transaction-local marker
-- rather than by widening the column list, because the sensitive columns are
-- exactly the ones those functions legitimately write. dispute_resolution, for
-- instance, is called by the citizen and must be able to set status, admin_note
-- and sla_due_at -- which is precisely what a citizen must not be able to set by
-- hand. Only the route can tell them apart.

-- About the marker used below:
--
-- set_config lives in pg_catalog, so it is not exposed as a PostgREST RPC and a
-- client cannot set it for itself. No exposed function takes a setting name from
-- user input either. The third argument scopes it to the transaction, so it
-- cannot leak into the next request on a pooled connection.

-- ---------------------------------------------------------------------------
-- 1. The guard
-- ---------------------------------------------------------------------------

create or replace function public.enforce_publish_policy()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
  -- Service-role contexts (auth.uid() is null, e.g. seeds and server-side jobs)
  -- and admins pass through untouched, as before.
  if auth.uid() is null or public.is_admin() then
    return new;
  end if;

  -- A write from one of the platform's own functions rather than from a client
  -- statement. confirm_resolution, dispute_resolution, the counter triggers and
  -- the share/view RPCs all mark themselves this way.
  if coalesce(current_setting('app.trusted_report_write', true), '') = 'on' then
    return new;
  end if;

  if tg_op = 'INSERT' then
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
      -- A published report is being worked on, not awaiting moderation. Leaving
      -- it 'pending' was the second half of the self-publish problem: the owner
      -- edit policy keys on status, so a live report stayed editable by its
      -- author. Admin approval does the same in approveReports().
      new.status         := 'in_progress';
    else
      new.is_approved    := false;
      new.approved_at    := null;
      new.sla_due_at     := null;
      new.auto_published := false;
    end if;

    return new;
  end if;

  -- UPDATE by a citizen on their own report.
  --
  -- An allowlist, not a blocklist: a citizen may correct what they wrote, and
  -- everything else keeps the value it already had. Adding a column to this
  -- table in future therefore fails closed -- the new column is protected until
  -- someone deliberately adds it here -- which is the right way round.
  new.id                := old.id;
  new.user_id           := old.user_id;
  new.status            := old.status;
  new.is_approved       := old.is_approved;
  new.approved_at       := old.approved_at;
  new.auto_published    := old.auto_published;
  new.admin_note        := old.admin_note;
  new.priority          := old.priority;
  new.assigned_to       := old.assigned_to;
  new.assigned_at       := old.assigned_at;
  new.status_updated_at := old.status_updated_at;
  new.sla_due_at        := old.sla_due_at;
  new.resolution_state  := old.resolution_state;
  new.resolved_at       := old.resolved_at;
  new.dispute_reason    := old.dispute_reason;
  new.routed_authority_key := old.routed_authority_key;
  new.view_count        := old.view_count;
  new.share_count       := old.share_count;
  new.endorse_count     := old.endorse_count;
  new.comment_count     := old.comment_count;
  new.corroboration_count := old.corroboration_count;
  new.created_at        := old.created_at;

  -- Left editable: title, description, category, city_corporation,
  -- location_text, latitude, longitude, updated_at.

  return new;
end;
$$;

revoke execute on function public.enforce_publish_policy() from anon, authenticated, public;

-- The insert trigger already exists and keeps pointing at the same function.
-- Fired before reports_status_change_trg, which sorts later by name, so the
-- status logger sees the values this guard settled on.
drop trigger if exists reports_publish_policy_upd_trg on public.reports;
create trigger reports_publish_policy_upd_trg
  before update on public.reports
  for each row execute function public.enforce_publish_policy();

-- ---------------------------------------------------------------------------
-- 2. The policy's missing column guard
-- ---------------------------------------------------------------------------

drop policy if exists "owner edits own pending report" on public.reports;
create policy "owner edits own pending report"
  on public.reports for update
  using (auth.uid() = user_id and status = 'pending' and is_approved = false)
  with check (
    auth.uid() = user_id
    and status = 'pending'
    and is_approved = false
  );

-- ---------------------------------------------------------------------------
-- 3. Mark the platform's own report writers
-- ---------------------------------------------------------------------------
-- Bodies are otherwise unchanged from the migrations that introduced them.

create or replace function public.confirm_resolution(p_report_id bigint)
returns void
language plpgsql
security definer
set search_path = public
as $$
begin
  perform set_config('app.trusted_report_write', 'on', true);

  update public.reports
     set resolution_state = 'confirmed'
   where id = p_report_id
     and user_id = auth.uid()
     and status = 'resolved'
     and resolution_state = 'pending_confirmation';
  if not found then
    raise exception 'not allowed';
  end if;
end;
$$;

create or replace function public.dispute_resolution(
  p_report_id bigint,
  p_reason text
)
returns void
language plpgsql
security definer
set search_path = public
as $$
begin
  perform set_config('app.trusted_report_write', 'on', true);

  update public.reports
     set status = 'in_progress',
         resolution_state = 'disputed',
         dispute_reason = nullif(btrim(p_reason), ''),
         admin_note = null,
         sla_due_at = now() + interval '7 days'
   where id = p_report_id
     and user_id = auth.uid()
     and status = 'resolved'
     and resolution_state = 'pending_confirmation';
  if not found then
    raise exception 'not allowed';
  end if;
end;
$$;

-- Counter RPCs: plpgsql now, so the marker can be set alongside the update.
create or replace function public.increment_share_count(p_report_id bigint)
returns void
language plpgsql
security definer
set search_path = public
as $$
begin
  perform set_config('app.trusted_report_write', 'on', true);

  update public.reports
     set share_count = share_count + 1
   where id = p_report_id and is_approved = true;
end;
$$;

create or replace function public.increment_view_count(p_report_id bigint)
returns void
language plpgsql
security definer
set search_path = public
as $$
begin
  perform set_config('app.trusted_report_write', 'on', true);

  update public.reports
     set view_count = view_count + 1
   where id = p_report_id and is_approved = true;
end;
$$;

grant execute on function public.increment_share_count(bigint) to anon, authenticated;
grant execute on function public.increment_view_count(bigint)  to anon, authenticated;

-- Counter triggers.
create or replace function public.sync_endorse_count()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
  perform set_config('app.trusted_report_write', 'on', true);

  if (tg_op = 'INSERT') then
    update public.reports set endorse_count = endorse_count + 1 where id = new.report_id;
  elsif (tg_op = 'DELETE') then
    update public.reports set endorse_count = greatest(endorse_count - 1, 0) where id = old.report_id;
  end if;
  return null;
end;
$$;

create or replace function public.sync_comment_count()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
  perform set_config('app.trusted_report_write', 'on', true);

  if (tg_op = 'INSERT') then
    update public.reports set comment_count = comment_count + 1 where id = new.report_id;
  elsif (tg_op = 'DELETE') then
    update public.reports set comment_count = greatest(comment_count - 1, 0) where id = old.report_id;
  end if;
  return null;
end;
$$;

revoke execute on function public.sync_endorse_count() from anon, authenticated, public;
revoke execute on function public.sync_comment_count() from anon, authenticated, public;
revoke execute on function public.confirm_resolution(bigint) from anon, public;
revoke execute on function public.dispute_resolution(bigint, text) from anon, public;
grant execute on function public.confirm_resolution(bigint) to authenticated;
grant execute on function public.dispute_resolution(bigint, text) to authenticated;

create or replace function public.sync_corroboration_count()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
  perform set_config('app.trusted_report_write', 'on', true);

  if (tg_op = 'INSERT') then
    update public.reports set corroboration_count = corroboration_count + 1 where id = new.report_id;
  elsif (tg_op = 'DELETE') then
    update public.reports set corroboration_count = greatest(corroboration_count - 1, 0) where id = old.report_id;
  end if;
  return null;
end;
$$;

revoke execute on function public.sync_corroboration_count() from anon, authenticated, public;

-- ---------------------------------------------------------------------------
-- 4. Keep approval from notifying twice
-- ---------------------------------------------------------------------------
-- Now that approving a report also advances its status, this trigger fires on
-- the same statement that approveReports() already announces as "Report
-- approved". The status log is still written -- that is the audit record -- but
-- the duplicate in-app notification is suppressed. Otherwise the reporter gets
-- "approved" and "now in_progress" a moment apart for one event.

create or replace function public.log_status_change()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
  if (new.status is distinct from old.status) then
    new.status_updated_at = now();

    insert into public.report_status_logs (report_id, actor_id, from_status, to_status, note)
    values (new.id, auth.uid(), old.status, new.status, new.admin_note);

    if (new.user_id is not null
        and not (old.is_approved = false and new.is_approved = true)) then
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

revoke execute on function public.log_status_change() from anon, authenticated, public;
