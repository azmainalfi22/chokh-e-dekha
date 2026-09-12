-- Category suggestions, held until a human confirms them.
--
-- The pattern worth copying from the benchmarked projects is not the model, it
-- is where the output lands: the suggestion sits in a column and changes
-- nothing until an admin accepts it. A classifier that quietly refiles reports
-- is one nobody can argue with, and a misfiled report is one that reaches the
-- wrong desk and misses its deadline.
--
-- So the suggestion is stored beside the report, never applied to it. Accepting
-- is an explicit act, recorded with who did it and when.

alter table public.reports
  -- { category, priority, confidence, rationale, provider }
  add column if not exists ai_suggestion jsonb,
  add column if not exists ai_suggested_at timestamptz,
  add column if not exists ai_confirmed_at timestamptz,
  add column if not exists ai_confirmed_by uuid references public.profiles (id) on delete set null;

-- Unreviewed suggestions are what the admin queue filters on.
create index if not exists reports_ai_pending_idx
  on public.reports (ai_suggested_at)
  where ai_suggestion is not null and ai_confirmed_at is null;

/**
 * Attach a suggestion to a report.
 *
 * Separate from the report's own update path because a suggestion is not an
 * edit: it never touches category or priority. Trusted-write marked, since the
 * columns sit behind the update guard.
 */
create or replace function public.set_report_suggestion(
  p_report_id bigint,
  p_suggestion jsonb
)
returns void
language plpgsql
security definer
set search_path = public
as $$
begin
  perform set_config('app.trusted_report_write', 'on', true);

  update public.reports
     set ai_suggestion   = p_suggestion,
         ai_suggested_at = now(),
         ai_confirmed_at = null,
         ai_confirmed_by = null
   where id = p_report_id;
end;
$$;

revoke execute on function public.set_report_suggestion(bigint, jsonb) from anon, public;
grant execute on function public.set_report_suggestion(bigint, jsonb) to authenticated;

/**
 * Accept or reject a suggestion. Admins only.
 *
 * Accepting copies the suggested category and priority onto the report and
 * recomputes nothing else — in particular it does not touch the SLA deadline of
 * a report that is already published, because a citizen has been told that date.
 * Rejecting records that a human looked and disagreed, which is the more useful
 * signal of the two.
 */
create or replace function public.resolve_report_suggestion(
  p_report_id bigint,
  p_accept boolean
)
returns void
language plpgsql
security definer
set search_path = public
as $$
declare
  v_suggestion jsonb;
begin
  if not public.is_admin() then
    raise exception 'Admin access required';
  end if;

  select ai_suggestion into v_suggestion from public.reports where id = p_report_id;

  if v_suggestion is null then
    raise exception 'That report has no suggestion to resolve';
  end if;

  perform set_config('app.trusted_report_write', 'on', true);

  if p_accept then
    update public.reports
       set category        = coalesce(v_suggestion ->> 'category', category),
           priority        = coalesce(v_suggestion ->> 'priority', priority),
           ai_confirmed_at = now(),
           ai_confirmed_by = auth.uid()
     where id = p_report_id;
  else
    update public.reports
       set ai_confirmed_at = now(),
           ai_confirmed_by = auth.uid()
     where id = p_report_id;
  end if;

  perform audit.record(
    case when p_accept then 'report.suggestion_accepted' else 'report.suggestion_rejected' end,
    'report', p_report_id::text,
    v_suggestion
  );
end;
$$;

revoke execute on function public.resolve_report_suggestion(bigint, boolean) from anon, public;
grant execute on function public.resolve_report_suggestion(bigint, boolean) to authenticated;

-- The new columns join the guard's protected list, so neither a citizen nor an
-- officer can write a suggestion or mark one confirmed.
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
    new.priority    := public.priority_for_category(new.category);

    new.duplicate_of_id      := null;
    new.duplicate_confidence := null;
    new.duplicate_checked_at := null;
    new.ai_suggestion        := null;
    new.ai_suggested_at      := null;
    new.ai_confirmed_at      := null;
    new.ai_confirmed_by      := null;

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

  if public.officer_covers_territory(old.territory_id) then
    new.id                   := old.id;
    new.user_id              := old.user_id;
    new.title                := old.title;
    new.description          := old.description;
    new.category             := old.category;
    new.city_corporation     := old.city_corporation;
    new.territory_id         := old.territory_id;
    new.location_text        := old.location_text;
    new.latitude             := old.latitude;
    new.longitude            := old.longitude;
    new.is_approved          := old.is_approved;
    new.approved_at          := old.approved_at;
    new.auto_published       := old.auto_published;
    new.sla_due_at           := old.sla_due_at;
    new.routed_authority_key := old.routed_authority_key;
    new.view_count           := old.view_count;
    new.share_count          := old.share_count;
    new.endorse_count        := old.endorse_count;
    new.comment_count        := old.comment_count;
    new.corroboration_count  := old.corroboration_count;
    new.created_at           := old.created_at;
    new.duplicate_of_id      := old.duplicate_of_id;
    new.duplicate_confidence := old.duplicate_confidence;
    new.duplicate_checked_at := old.duplicate_checked_at;
    new.ai_suggestion        := old.ai_suggestion;
    new.ai_suggested_at      := old.ai_suggested_at;
    new.ai_confirmed_at      := old.ai_confirmed_at;
    new.ai_confirmed_by      := old.ai_confirmed_by;

    return new;
  end if;

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
  new.territory_id         := old.territory_id;
  new.view_count           := old.view_count;
  new.share_count          := old.share_count;
  new.endorse_count        := old.endorse_count;
  new.comment_count        := old.comment_count;
  new.corroboration_count  := old.corroboration_count;
  new.created_at           := old.created_at;
  new.escalation_level     := old.escalation_level;
  new.escalated_at         := old.escalated_at;
  new.duplicate_of_id      := old.duplicate_of_id;
  new.duplicate_confidence := old.duplicate_confidence;
  new.duplicate_checked_at := old.duplicate_checked_at;
  new.ai_suggestion        := old.ai_suggestion;
  new.ai_suggested_at      := old.ai_suggested_at;
  new.ai_confirmed_at      := old.ai_confirmed_at;
  new.ai_confirmed_by      := old.ai_confirmed_by;

  return new;
end;
$$;

revoke execute on function public.enforce_publish_policy() from anon, authenticated, public;
