-- Proof that the Phase 0 fixes actually hold.
--
-- These migrations were written without a database to run them against, so this
-- exists to close that gap in one command. Run it AFTER applying the migrations,
-- against a local stack or a branch database — never production, because it
-- creates and deletes rows.
--
--   supabase db reset && psql "$DATABASE_URL" -f supabase/tests/phase0_security.sql
--
-- or paste it into the Supabase SQL editor.
--
-- It raises an exception on the first failure and rolls everything back, so a
-- silent completion means every assertion passed. Nothing is left behind either
-- way.
--
-- auth.uid() reads the sub claim out of request.jwt.claims, so setting that GUC
-- is how a test pretends to be a particular signed-in citizen.

begin;

do $$
declare
  v_citizen  uuid := '11111111-1111-1111-1111-111111111111';
  v_other    uuid := '22222222-2222-2222-2222-222222222222';
  v_report   bigint;
  v_approved boolean;
  v_status   text;
  v_title    text;
begin
  -- ---------------------------------------------------------------------
  -- Fixtures. Created as the table owner, so RLS is not in the way yet.
  -- ---------------------------------------------------------------------
  insert into auth.users (id, email)
  values (v_citizen, 'citizen@example.test'), (v_other, 'other@example.test')
  on conflict (id) do nothing;

  insert into public.profiles (id, display_name, role)
  values (v_citizen, 'Test Citizen', 'citizen'), (v_other, 'Other Citizen', 'citizen')
  on conflict (id) do update set role = excluded.role;

  insert into public.reports (user_id, title, description, category, city_corporation, latitude, longitude)
  values (v_citizen, 'Broken streetlight on the test road',
          'The light has been dark for a week and the crossing is unsafe.',
          'Streetlight', 'Dhaka North City Corporation', 23.78, 90.40)
  returning id into v_report;

  -- The insert trigger must have held it back for moderation.
  select is_approved, status into v_approved, v_status
    from public.reports where id = v_report;

  if v_approved is not false then
    raise exception 'FAIL: a new report should not be approved (got is_approved=%)', v_approved;
  end if;
  if v_status <> 'pending' then
    raise exception 'FAIL: a new report should be pending (got %)', v_status;
  end if;

  -- ---------------------------------------------------------------------
  -- 0.1 The author must not be able to publish their own report.
  -- ---------------------------------------------------------------------
  perform set_config('request.jwt.claims', json_build_object('sub', v_citizen)::text, true);
  perform set_config('role', 'authenticated', true);

  update public.reports
     set is_approved = true,
         status      = 'resolved',
         admin_note  = 'approved by me',
         priority    = 'high',
         sla_due_at  = now() + interval '400 days'
   where id = v_report;

  perform set_config('role', 'postgres', true);

  select is_approved, status, admin_note into v_approved, v_status, v_title
    from public.reports where id = v_report;

  if v_approved then
    raise exception 'FAIL: the author published their own report — the update guard did not hold';
  end if;
  if v_status <> 'pending' then
    raise exception 'FAIL: the author changed status to % — the update guard did not hold', v_status;
  end if;
  if v_title is not null then
    raise exception 'FAIL: the author set admin_note — the update guard did not hold';
  end if;

  -- ---------------------------------------------------------------------
  -- The author MUST still be able to correct their own wording.
  -- ---------------------------------------------------------------------
  perform set_config('role', 'authenticated', true);

  update public.reports set title = 'Corrected title' where id = v_report;

  perform set_config('role', 'postgres', true);

  select title into v_title from public.reports where id = v_report;
  if v_title <> 'Corrected title' then
    raise exception 'FAIL: the author can no longer correct their own pending report (title=%)', v_title;
  end if;

  -- ---------------------------------------------------------------------
  -- 0.2 Once approved, the report must stop being author-editable.
  -- ---------------------------------------------------------------------
  perform set_config('request.jwt.claims', null, true);
  update public.reports
     set is_approved = true, approved_at = now(), status = 'in_progress'
   where id = v_report;

  select status into v_status from public.reports where id = v_report;
  if v_status = 'pending' then
    raise exception 'FAIL: approval left status at pending, so the owner-edit policy still matches';
  end if;

  -- The SLA trigger should have set a deadline from the report's priority.
  if (select sla_due_at from public.reports where id = v_report) is null then
    raise exception 'FAIL: approval did not set an SLA deadline';
  end if;

  perform set_config('request.jwt.claims', json_build_object('sub', v_citizen)::text, true);
  perform set_config('role', 'authenticated', true);

  update public.reports set title = 'Rewritten after approval' where id = v_report;

  perform set_config('role', 'postgres', true);

  select title into v_title from public.reports where id = v_report;
  if v_title = 'Rewritten after approval' then
    raise exception 'FAIL: the author rewrote a published report';
  end if;

  -- ---------------------------------------------------------------------
  -- A stranger must not be able to touch it at all.
  -- ---------------------------------------------------------------------
  perform set_config('request.jwt.claims', json_build_object('sub', v_other)::text, true);
  perform set_config('role', 'authenticated', true);

  update public.reports set title = 'Not mine' where id = v_report;

  perform set_config('role', 'postgres', true);

  select title into v_title from public.reports where id = v_report;
  if v_title = 'Not mine' then
    raise exception 'FAIL: a stranger edited someone else''s report';
  end if;

  -- ---------------------------------------------------------------------
  -- Phase 1: the SLA tier and the sweep.
  -- ---------------------------------------------------------------------
  perform set_config('request.jwt.claims', null, true);

  if public.priority_for_category('Gas') <> 'high' then
    raise exception 'FAIL: a gas report should be high priority';
  end if;
  if public.sla_days_for_priority('high') >= public.sla_days_for_priority('low') then
    raise exception 'FAIL: SLA windows are not ordered by urgency';
  end if;

  -- Backdate the deadline and check the sweep escalates exactly once.
  update public.reports set sla_due_at = now() - interval '1 hour' where id = v_report;
  perform public.sweep_sla_breaches();
  perform public.sweep_sla_breaches();
  perform public.sweep_sla_breaches();

  if (select escalation_level from public.reports where id = v_report) <> 1 then
    raise exception 'FAIL: expected escalation level 1 after repeated sweeps, got %',
      (select escalation_level from public.reports where id = v_report);
  end if;

  update public.reports set sla_due_at = now() - interval '100 hours' where id = v_report;
  perform public.sweep_sla_breaches();

  if (select escalation_level from public.reports where id = v_report) <> 2 then
    raise exception 'FAIL: a long-overdue report should reach level 2';
  end if;

  perform public.sweep_sla_breaches();
  if (select escalation_level from public.reports where id = v_report) <> 2 then
    raise exception 'FAIL: escalation went past level 2';
  end if;

  raise notice 'All Phase 0 and Phase 1 database assertions passed.';
end;
$$;

-- Nothing is kept: this is a test, not a seed.
rollback;
