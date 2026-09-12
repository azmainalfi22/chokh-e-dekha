-- Group duplicate reports into one case.
--
-- Two people reporting the same broken streetlight currently become two reports:
-- two rows in the moderation queue, two SLA clocks, two sets of statistics, and
-- an officer who fixes it once and closes one of them. reports_near() already
-- finds nearby reports of the same category — its own comment says it "powers
-- duplicate detection on submit" — but nothing calls it, and proximity alone was
-- never going to be enough anyway.
--
-- A duplicate keeps its own row. The citizen who filed it still sees their
-- report, and the number of people who reported a thing is evidence in its own
-- right, not noise to be discarded. It simply points at the report it duplicates;
-- the canonical report is the one whose duplicate_of_id is null.
--
-- Matching is deliberately conservative and needs all four of: the same category,
-- close by, recent, and similar wording. None of those alone is a good signal. A
-- busy intersection collects unrelated complaints, so proximity is not evidence,
-- and "broken road" describes half of Dhaka, so wording is not either. A false
-- positive files a citizen's report underneath a stranger's, which is worse than
-- an officer seeing the same pothole twice — so the thresholds lean towards
-- missing a match.

create extension if not exists pg_trgm with schema extensions;

alter table public.reports
  add column if not exists duplicate_of_id bigint
    references public.reports (id) on delete set null,
  -- How strong the match was, 0 to 1, kept so a moderator reviewing a grouping
  -- can see whether it was borderline.
  add column if not exists duplicate_confidence real,
  -- Distinguishes "examined, no match" from "never examined".
  add column if not exists duplicate_checked_at timestamptz;

create index if not exists reports_duplicate_of_idx
  on public.reports (duplicate_of_id)
  where duplicate_of_id is not null;

-- Trigram index on the text actually compared, so the similarity test has
-- something to lean on as the table grows.
create index if not exists reports_title_trgm_idx
  on public.reports using gin (title extensions.gin_trgm_ops);

-- ---------------------------------------------------------------------------
-- Tunables, in one place
-- ---------------------------------------------------------------------------

-- Roughly a city block: far enough to cover two people standing at different
-- ends of the same problem, close enough not to swallow the next street over.
create or replace function public.duplicate_radius_m()
returns integer language sql immutable set search_path = '' as $$ select 150; $$;

-- A pothole reported in March and again in September is usually a new complaint
-- about an unfixed problem, or a second failure at the same spot. Either way it
-- deserves its own record.
create or replace function public.duplicate_window_days()
returns integer language sql immutable set search_path = '' as $$ select 30; $$;

-- Minimum trigram similarity over title and description together.
create or replace function public.duplicate_similarity_threshold()
returns real language sql immutable set search_path = '' as $$ select 0.35::real; $$;

-- ---------------------------------------------------------------------------
-- Detection
-- ---------------------------------------------------------------------------

/**
 * The report p_report_id most likely duplicates, if any.
 *
 * Returns at most one row: the strongest match. Candidates are narrowed by a
 * bounding box first so the geo index can help, then by exact distance, then by
 * wording.
 *
 * Only ever returns a canonical report. If the best match is itself a duplicate,
 * its own canonical is returned instead, so groups stay one level deep and a
 * chain can never form.
 */
create or replace function public.find_duplicate_match(p_report_id bigint)
returns table (canonical_id bigint, confidence real)
language plpgsql
stable
security definer
set search_path = public
as $$
declare
  r public.reports;
begin
  select * into r from public.reports where id = p_report_id;

  if not found or r.latitude is null or r.longitude is null then
    return;
  end if;

  return query
  with candidate as (
    select c.id,
           coalesce(c.duplicate_of_id, c.id) as canonical,
           6371000 * 2 * asin(sqrt(
             power(sin(radians(c.latitude - r.latitude) / 2), 2) +
             cos(radians(r.latitude)) * cos(radians(c.latitude)) *
             power(sin(radians(c.longitude - r.longitude) / 2), 2)
           )) as distance_m,
           extensions.similarity(
             c.title || ' ' || coalesce(c.description, ''),
             r.title || ' ' || coalesce(r.description, '')
           ) as text_similarity
      from public.reports c
     where c.id <> r.id
       and c.category = r.category
       and c.latitude is not null
       and c.longitude is not null
       and c.status <> 'rejected'
       and c.created_at >= r.created_at - make_interval(days => public.duplicate_window_days())
       and c.created_at <= r.created_at
       -- Bounding box prefilter, same shape as reports_near().
       and c.latitude between r.latitude - (public.duplicate_radius_m() / 111320.0)
                          and r.latitude + (public.duplicate_radius_m() / 111320.0)
       and c.longitude between r.longitude - (public.duplicate_radius_m() / (111320.0 * cos(radians(r.latitude))))
                           and r.longitude + (public.duplicate_radius_m() / (111320.0 * cos(radians(r.latitude))))
  )
  select candidate.canonical, candidate.text_similarity
    from candidate
   where candidate.distance_m <= public.duplicate_radius_m()
     and candidate.text_similarity >= public.duplicate_similarity_threshold()
     -- Never file something under a report that would then point back at it.
     and candidate.canonical <> r.id
   order by candidate.text_similarity desc, candidate.distance_m asc
   limit 1;
end;
$$;

revoke execute on function public.find_duplicate_match(bigint) from anon, public;
grant execute on function public.find_duplicate_match(bigint) to authenticated;

/**
 * Record the outcome of duplicate detection for one report.
 *
 * Always stamps duplicate_checked_at, so a later pass can tell "no match" from
 * "not looked at yet". Marked as a trusted write because it touches columns the
 * update guard protects.
 */
create or replace function public.link_report_duplicate(p_report_id bigint)
returns bigint
language plpgsql
security definer
set search_path = public
as $$
declare
  v_canonical  bigint;
  v_confidence real;
begin
  select canonical_id, confidence
    into v_canonical, v_confidence
    from public.find_duplicate_match(p_report_id);

  perform set_config('app.trusted_report_write', 'on', true);

  update public.reports
     set duplicate_of_id      = v_canonical,
         duplicate_confidence = v_confidence,
         duplicate_checked_at = now()
   where id = p_report_id;

  return v_canonical;
end;
$$;

revoke execute on function public.link_report_duplicate(bigint) from anon, public;
grant execute on function public.link_report_duplicate(bigint) to authenticated;

/**
 * How many people have reported this problem, counting the original.
 *
 * Duplicates are evidence of scale: five reports of one flooded junction is a
 * stronger case than one, and the queue should say so.
 */
create or replace function public.reporter_count(p_report_id bigint)
returns integer
language sql
stable
security definer
set search_path = public
as $$
  select 1 + (
    select count(*)
      from public.reports d
     where d.duplicate_of_id = p_report_id
  )::integer;
$$;

grant execute on function public.reporter_count(bigint) to anon, authenticated;

-- The update guard protects these columns from a citizen editing their own
-- report, so add them to the allowlist's protected side. Reproduced in full
-- because the function has to be replaced wholesale.
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

    -- Detection runs after the row exists, so a client cannot pre-declare
    -- itself a duplicate of anything.
    new.duplicate_of_id      := null;
    new.duplicate_confidence := null;
    new.duplicate_checked_at := null;

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
  new.escalation_level     := old.escalation_level;
  new.escalated_at         := old.escalated_at;
  new.duplicate_of_id      := old.duplicate_of_id;
  new.duplicate_confidence := old.duplicate_confidence;
  new.duplicate_checked_at := old.duplicate_checked_at;

  return new;
end;
$$;

revoke execute on function public.enforce_publish_policy() from anon, authenticated, public;
