-- Proof that the audit chain detects tampering.
--
-- Same contract as the other test scripts: run AFTER applying the migrations,
-- against a local stack or a branch database, never production. Raises on the
-- first failure and rolls back.
--
--   psql "$DATABASE_URL" -f supabase/tests/audit_trail.sql
--
-- The tampering below disables the append-only triggers first, deliberately.
-- Those triggers stop accidents; they cannot stop someone with rights over the
-- table, and the whole point of the hash chain is that such a person still
-- cannot do it quietly. Testing through the triggers would only prove the
-- triggers work.

begin;

do $$
declare
  v_first  bigint;
  v_second bigint;
  v_third  bigint;
  v_break  record;
  v_count  integer;
begin
  -- A clean chain of three.
  perform audit.record('test.one',   'thing', '1', '{"n":1}'::jsonb);
  perform audit.record('test.two',   'thing', '2', '{"n":2}'::jsonb);
  perform audit.record('test.three', 'thing', '3', '{"n":3}'::jsonb);

  select count(*) into v_count from audit.verify();
  if v_count <> 0 then
    raise exception 'FAIL: a freshly written chain does not verify';
  end if;

  -- Each entry must carry the hash of the one before it.
  select e.id into v_first  from audit.events e order by e.seq asc limit 1;
  select e.id into v_second from audit.events e order by e.seq asc offset 1 limit 1;

  if (select previous_hash from audit.events where id = v_second)
     <> (select hash from audit.events where id = v_first) then
    raise exception 'FAIL: entries are not chained';
  end if;

  if (select previous_hash from audit.events where id = v_first) <> audit.genesis_hash() then
    raise exception 'FAIL: the first entry does not start from the genesis hash';
  end if;

  -- The append-only guard must refuse an ordinary update.
  begin
    update audit.events set action = 'tampered' where id = v_first;
    raise exception 'FAIL: audit.events accepted an update';
  exception
    when others then
      if sqlerrm like 'FAIL:%' then raise; end if;
  end;

  -- ---------------------------------------------------------------------
  -- Editing an entry, the way someone with table rights would.
  -- ---------------------------------------------------------------------
  alter table audit.events disable trigger audit_events_no_update;

  update audit.events set action = 'nothing.happened' where id = v_second;

  select * into v_break from audit.verify() limit 1;
  if v_break is null then
    raise exception 'FAIL: an edited entry went undetected';
  end if;
  if v_break.reason not like '%altered%' then
    raise exception 'FAIL: an edit was detected but misdiagnosed as: %', v_break.reason;
  end if;

  -- Put it back and confirm the chain is whole again, so the next check starts
  -- from a known-good state.
  update audit.events set action = 'test.two' where id = v_second;

  select count(*) into v_count from audit.verify();
  if v_count <> 0 then
    raise exception 'FAIL: restoring the entry did not restore the chain';
  end if;

  -- ---------------------------------------------------------------------
  -- Re-signing a forged entry, the way a careful attacker would.
  -- ---------------------------------------------------------------------
  update audit.events e
     set action = 'nothing.happened',
         hash = audit.compute_hash(
           e.seq, e.occurred_at, e.actor_id, e.actor_label, 'nothing.happened',
           e.subject_type, e.subject_id, e.payload, e.previous_hash
         )
   where e.id = v_second;

  select * into v_break from audit.verify() limit 1;
  if v_break is null then
    raise exception 'FAIL: recomputing the hash repaired the chain — the entries are not linked';
  end if;

  -- The break must surface at the FOLLOWING entry, whose previous_hash still
  -- records the original value.
  select e.seq into v_third from audit.events e where e.id = v_second;
  if v_break.broken_seq <> v_third + 1 then
    raise exception 'FAIL: expected the break at sequence %, reported at %',
      v_third + 1, v_break.broken_seq;
  end if;

  -- ---------------------------------------------------------------------
  -- Deleting an entry from the middle.
  -- ---------------------------------------------------------------------
  alter table audit.events enable trigger audit_events_no_update;
  alter table audit.events disable trigger audit_events_no_delete;

  delete from audit.events where id = v_second;

  select * into v_break from audit.verify() limit 1;
  if v_break is null then
    raise exception 'FAIL: a deleted entry went undetected';
  end if;
  if v_break.reason not like '%missing or out of order%' then
    raise exception 'FAIL: a deletion was detected but misdiagnosed as: %', v_break.reason;
  end if;

  alter table audit.events enable trigger audit_events_no_delete;

  raise notice 'All audit trail assertions passed.';
end;
$$;

rollback;
