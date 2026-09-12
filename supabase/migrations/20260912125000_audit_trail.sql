-- A tamper-evident audit trail.
--
-- report_status_logs already records who moved a report and when, but it is an
-- ordinary table in the application's own schema: anything that can write to the
-- database can rewrite it, and nothing would show. It answers "what happened".
-- This answers a different question — "can we prove what happened" — which is the
-- one that matters when the record concerns an official who would prefer it said
-- something else.
--
-- Two things protect it.
--
-- It lives in its own schema. `audit` is not in PostgREST's exposed schemas, so
-- there is no API surface to it at all, and an application migration that goes
-- wrong cannot take the evidence with it. Writes come only through
-- audit.record(), which is SECURITY DEFINER; the table itself grants nothing to
-- anon or authenticated.
--
-- And every entry hashes its own contents together with the hash of the entry
-- before it. Altering an entry breaks its own hash; deleting or reordering one
-- breaks the link in the entry that follows. audit.verify() walks the chain and
-- names the first break. That does not stop someone with database superuser
-- rights from rewriting history — nothing in the database can — but it does mean
-- they cannot do it quietly.

create schema if not exists audit;

-- No ambient access. Everything goes through the functions below.
revoke all on schema audit from public;
grant usage on schema audit to postgres;

create table audit.events (
  id            bigint generated always as identity primary key,

  -- Position in the chain, separate from the primary key so the chain stays
  -- meaningful even if the table is ever restored or merged.
  seq           bigint not null unique,

  occurred_at   timestamptz not null default now(),

  -- Not foreign keys on purpose: the trail has to survive the deletion of the
  -- user and the record it describes. An entry about a deleted report is exactly
  -- the entry most worth keeping.
  actor_id      uuid,
  actor_label   text,

  action        text not null,
  subject_type  text,
  subject_id    text,

  payload       jsonb not null default '{}'::jsonb,

  previous_hash text not null,
  hash          text not null unique
);

create index audit_events_subject_idx on audit.events (subject_type, subject_id);
create index audit_events_action_idx  on audit.events (action);
create index audit_events_actor_idx   on audit.events (actor_id);

-- Append-only. Revoking the privileges is the real control; the trigger is there
-- so that an accidental update through a privileged role fails loudly rather
-- than silently rewriting the record.
revoke all on audit.events from public;

create or replace function audit.refuse_mutation()
returns trigger
language plpgsql
as $$
begin
  raise exception 'audit.events is append-only (attempted %)', tg_op;
end;
$$;

create trigger audit_events_no_update
  before update on audit.events
  for each row execute function audit.refuse_mutation();

create trigger audit_events_no_delete
  before delete on audit.events
  for each row execute function audit.refuse_mutation();

-- The predecessor of the very first entry. Fixed rather than random so a chain
-- can be verified from scratch on any machine.
create or replace function audit.genesis_hash()
returns text language sql immutable set search_path = '' as $$
  select repeat('0', 64);
$$;

/**
 * The hash an entry should carry, computed from its own contents.
 *
 * Fields are joined with the ASCII unit separator, which cannot occur in any of
 * the encoded values. Without a separator that cannot appear in the data, an
 * attacker could move characters between adjacent fields and leave the hash
 * unchanged.
 *
 * sha256() is built into Postgres, so this needs no extension. jsonb renders
 * canonically — keys sorted, whitespace normalised — so the same payload always
 * produces the same text.
 */
create or replace function audit.compute_hash(
  p_seq           bigint,
  p_occurred_at   timestamptz,
  p_actor_id      uuid,
  p_actor_label   text,
  p_action        text,
  p_subject_type  text,
  p_subject_id    text,
  p_payload       jsonb,
  p_previous_hash text
)
returns text
language sql
immutable
set search_path = ''
as $$
  select encode(
    sha256(
      convert_to(
        concat_ws(
          chr(31),
          p_seq::text,
          to_char(p_occurred_at at time zone 'UTC', 'YYYY-MM-DD"T"HH24:MI:SS.USOF'),
          coalesce(p_actor_id::text, ''),
          coalesce(p_actor_label, ''),
          p_action,
          coalesce(p_subject_type, ''),
          coalesce(p_subject_id, ''),
          coalesce(p_payload, '{}'::jsonb)::text,
          p_previous_hash
        ),
        'UTF8'
      )
    ),
    'hex'
  );
$$;

/**
 * Append an entry to the trail.
 *
 * The last row is read FOR UPDATE inside the caller's transaction, because two
 * concurrent writers that both believed they were entry 41 would produce a chain
 * that could never verify again.
 */
create or replace function audit.record(
  p_action       text,
  p_subject_type text default null,
  p_subject_id   text default null,
  p_payload      jsonb default '{}'::jsonb,
  p_actor_id     uuid default null,
  p_actor_label  text default null
)
returns bigint
language plpgsql
security definer
set search_path = public
as $$
declare
  v_prev_seq  bigint;
  v_prev_hash text;
  v_seq       bigint;
  v_now       timestamptz := now();
  v_actor     uuid := coalesce(p_actor_id, auth.uid());
  v_label     text := p_actor_label;
  v_hash      text;
  v_id        bigint;
begin
  select seq, hash into v_prev_seq, v_prev_hash
    from audit.events
   order by seq desc
   limit 1
     for update;

  v_seq := coalesce(v_prev_seq, 0) + 1;
  v_prev_hash := coalesce(v_prev_hash, audit.genesis_hash());

  if v_label is null and v_actor is not null then
    select email into v_label from auth.users where id = v_actor;
  end if;

  v_label := coalesce(v_label, case when v_actor is null then 'system' else null end);

  v_hash := audit.compute_hash(
    v_seq, v_now, v_actor, v_label, p_action,
    p_subject_type, p_subject_id, coalesce(p_payload, '{}'::jsonb), v_prev_hash
  );

  insert into audit.events (
    seq, occurred_at, actor_id, actor_label, action,
    subject_type, subject_id, payload, previous_hash, hash
  )
  values (
    v_seq, v_now, v_actor, v_label, p_action,
    p_subject_type, p_subject_id, coalesce(p_payload, '{}'::jsonb), v_prev_hash, v_hash
  )
  returning id into v_id;

  return v_id;
end;
$$;

revoke execute on function audit.record(text, text, text, jsonb, uuid, text)
  from anon, authenticated, public;

/**
 * Walk the chain and report the first entry that does not verify.
 *
 * Returns no rows when the trail is intact. Three things are checked per entry:
 * that the sequence has no gap, which catches a deleted or reordered entry; that
 * previous_hash matches the entry before it, which catches a cut; and that the
 * stored hash still matches the entry's own contents, which catches an edit.
 */
create or replace function audit.verify()
returns table (broken_seq bigint, broken_id bigint, reason text)
language plpgsql
stable
security definer
set search_path = public
as $$
declare
  e                 record;
  v_expected_seq    bigint := 1;
  v_expected_prev   text := audit.genesis_hash();
begin
  for e in select * from audit.events order by seq loop
    if e.seq <> v_expected_seq then
      return query select e.seq, e.id,
        format('expected sequence %s, found %s: an entry is missing or out of order',
               v_expected_seq, e.seq);
      return;
    end if;

    if e.previous_hash <> v_expected_prev then
      return query select e.seq, e.id,
        'this entry does not follow the one before it: the chain has been cut'::text;
      return;
    end if;

    if e.hash <> audit.compute_hash(
         e.seq, e.occurred_at, e.actor_id, e.actor_label, e.action,
         e.subject_type, e.subject_id, e.payload, e.previous_hash
       ) then
      return query select e.seq, e.id,
        'this entry has been altered since it was written'::text;
      return;
    end if;

    v_expected_prev := e.hash;
    v_expected_seq  := v_expected_seq + 1;
  end loop;
end;
$$;

revoke execute on function audit.verify() from anon, authenticated, public;

-- ---------------------------------------------------------------------------
-- What gets recorded
-- ---------------------------------------------------------------------------
-- Only the decisions worth being able to prove. This is not a change log of
-- every column; a trail nobody can read is as useless as one nobody can trust.

create or replace function public.audit_report_changes()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
  if tg_op = 'DELETE' then
    -- A deleted report leaves no other trace, so the substance is kept here.
    perform audit.record(
      'report.deleted', 'report', old.id::text,
      jsonb_build_object(
        'title', old.title,
        'category', old.category,
        'status', old.status,
        'was_approved', old.is_approved,
        'author_id', old.user_id,
        'filed_at', old.created_at
      )
    );
    return old;
  end if;

  if old.is_approved = false and new.is_approved = true then
    perform audit.record(
      'report.approved', 'report', new.id::text,
      jsonb_build_object('sla_due_at', new.sla_due_at, 'auto', new.auto_published)
    );
  end if;

  if new.status is distinct from old.status then
    perform audit.record(
      'report.status_changed', 'report', new.id::text,
      jsonb_build_object('from', old.status, 'to', new.status, 'note', new.admin_note)
    );
  end if;

  if new.assigned_to is distinct from old.assigned_to then
    perform audit.record(
      'report.assigned', 'report', new.id::text,
      jsonb_build_object('from', old.assigned_to, 'to', new.assigned_to)
    );
  end if;

  return new;
end;
$$;

revoke execute on function public.audit_report_changes() from anon, authenticated, public;

create trigger reports_audit_trg
  after update or delete on public.reports
  for each row execute function public.audit_report_changes();

create or replace function public.audit_role_changes()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
  if new.role is distinct from old.role then
    perform audit.record(
      'profile.role_changed', 'profile', new.id::text,
      jsonb_build_object('from', old.role, 'to', new.role)
    );
  end if;

  return new;
end;
$$;

revoke execute on function public.audit_role_changes() from anon, authenticated, public;

-- Fires after profiles_guard_role has had its say, so what is recorded is the
-- role the account actually ended up with rather than the one someone asked for.
create trigger profiles_audit_role_trg
  after update on public.profiles
  for each row execute function public.audit_role_changes();

comment on schema audit is
  'Tamper-evident trail. Not exposed through PostgREST. Writes go through audit.record(); check integrity with select * from audit.verify().';
