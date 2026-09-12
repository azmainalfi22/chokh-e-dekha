-- Proof that the territory tree and the officer role behave.
--
-- Same contract as phase0_security.sql: run it AFTER applying the migrations,
-- against a local stack or a branch database, never production. It raises on the
-- first failure and rolls back, so it leaves nothing behind and a silent
-- completion means everything passed.
--
--   psql "$DATABASE_URL" -f supabase/tests/territory_and_officer.sql

begin;

do $$
declare
  v_officer   uuid := '33333333-3333-3333-3333-333333333333';
  v_citizen   uuid := '44444444-4444-4444-4444-444444444444';
  v_division  bigint;
  v_district  bigint;
  v_city      bigint;
  v_ward      bigint;
  v_other_div bigint;
  v_report    bigint;
  v_outside   bigint;
  v_path      text;
  v_depth     smallint;
  v_status    text;
  v_title     text;
  v_visible   integer;
begin
  -- ---------------------------------------------------------------------
  -- The tree
  -- ---------------------------------------------------------------------
  insert into public.territories (type, name, slug)
  values ('division', 'Testland', 'division-testland') returning id into v_division;

  insert into public.territories (type, name, slug, parent_id)
  values ('district', 'Testshire', 'district-testshire', v_division) returning id into v_district;

  insert into public.territories (type, name, slug, parent_id)
  values ('city_corporation', 'Test City', 'city-test-city', v_district) returning id into v_city;

  insert into public.territories (type, name, slug, parent_id)
  values ('ward', 'Test Ward 12', 'ward-test-12', v_city) returning id into v_ward;

  select path, depth into v_path, v_depth from public.territories where id = v_ward;

  if v_depth <> 3 then
    raise exception 'FAIL: ward depth should be 3, got %', v_depth;
  end if;
  if v_path <> '/' || v_division || '/' || v_district || '/' || v_city || '/' || v_ward || '/' then
    raise exception 'FAIL: ward path is wrong: %', v_path;
  end if;

  -- Moving the city to another division must drag the ward with it.
  insert into public.territories (type, name, slug)
  values ('division', 'Otherland', 'division-otherland') returning id into v_other_div;

  update public.territories set parent_id = v_other_div where id = v_city;

  select path, depth into v_path, v_depth from public.territories where id = v_ward;

  if v_path not like '/' || v_other_div || '/%' then
    raise exception 'FAIL: moving the city did not rewrite the ward path (%)', v_path;
  end if;
  if v_depth <> 2 then
    raise exception 'FAIL: ward depth should be 2 after the move, got %', v_depth;
  end if;

  -- A cycle must be refused.
  begin
    update public.territories set parent_id = v_ward where id = v_city;
    raise exception 'FAIL: a territory was allowed to move beneath its own descendant';
  exception
    when others then
      if sqlerrm like 'FAIL:%' then raise; end if;
      -- Anything else is the guard doing its job.
  end;

  -- ---------------------------------------------------------------------
  -- Fixtures for the officer
  -- ---------------------------------------------------------------------
  insert into auth.users (id, email)
  values (v_officer, 'officer@example.test'), (v_citizen, 'citizen2@example.test')
  on conflict (id) do nothing;

  insert into public.profiles (id, display_name, role)
  values (v_officer, 'Test Officer', 'officer'), (v_citizen, 'Test Citizen', 'citizen')
  on conflict (id) do update set role = excluded.role;

  insert into public.officer_territories (user_id, territory_id, designation)
  values (v_officer, v_city, 'Test City Sanitation');

  -- One report inside the officer's patch (in the ward beneath their city),
  -- one outside it entirely.
  insert into public.reports (user_id, title, description, category, city_corporation, territory_id)
  values (v_citizen, 'Blocked drain on the test road', 'Standing water for a week.',
          'Drainage / Waterlogging', 'Other', v_ward)
  returning id into v_report;

  insert into public.reports (user_id, title, description, category, city_corporation, territory_id)
  values (v_citizen, 'Something in another division', 'Not this officer''s problem.',
          'Road', 'Other', v_other_div)
  returning id into v_outside;

  -- ---------------------------------------------------------------------
  -- Coverage is by subtree
  -- ---------------------------------------------------------------------
  perform set_config('request.jwt.claims', json_build_object('sub', v_officer)::text, true);

  if not public.officer_covers_territory(v_ward) then
    raise exception 'FAIL: assigning a city should cover the wards beneath it';
  end if;
  if public.officer_covers_territory(v_other_div) then
    raise exception 'FAIL: the officer covers a division they were never assigned';
  end if;

  -- ---------------------------------------------------------------------
  -- What the officer can see
  -- ---------------------------------------------------------------------
  perform set_config('role', 'authenticated', true);

  select count(*) into v_visible from public.reports where id = v_report;
  if v_visible <> 1 then
    raise exception 'FAIL: the officer cannot see an unapproved report in their own patch';
  end if;

  select count(*) into v_visible from public.reports where id = v_outside;
  if v_visible <> 0 then
    raise exception 'FAIL: the officer can see an unapproved report outside their patch';
  end if;

  -- ---------------------------------------------------------------------
  -- What the officer can change
  -- ---------------------------------------------------------------------
  update public.reports
     set status = 'in_progress', admin_note = 'Crew dispatched', assigned_to = v_officer
   where id = v_report;

  perform set_config('role', 'postgres', true);

  select status into v_status from public.reports where id = v_report;
  if v_status <> 'in_progress' then
    raise exception 'FAIL: the officer could not move a report in their patch (status=%)', v_status;
  end if;

  -- ...and what they must not.
  perform set_config('role', 'authenticated', true);

  update public.reports
     set is_approved = true, title = 'Rewritten by the officer'
   where id = v_report;

  perform set_config('role', 'postgres', true);

  select is_approved, title into v_visible, v_title from public.reports where id = v_report;

  if (select is_approved from public.reports where id = v_report) then
    raise exception 'FAIL: an officer published a report — approval is not theirs to give';
  end if;
  if v_title = 'Rewritten by the officer' then
    raise exception 'FAIL: an officer rewrote the citizen''s own words';
  end if;

  -- ---------------------------------------------------------------------
  -- An officer is not an admin
  -- ---------------------------------------------------------------------
  perform set_config('request.jwt.claims', json_build_object('sub', v_officer)::text, true);

  if public.is_admin() then
    raise exception 'FAIL: an officer reports as an admin';
  end if;

  -- And cannot promote themselves: guard_role_change reverts it.
  perform set_config('role', 'authenticated', true);
  update public.profiles set role = 'admin' where id = v_officer;
  perform set_config('role', 'postgres', true);

  if (select role from public.profiles where id = v_officer) <> 'officer' then
    raise exception 'FAIL: an officer promoted themselves to admin';
  end if;

  perform set_config('request.jwt.claims', null, true);
  raise notice 'All territory and officer assertions passed.';
end;
$$;

rollback;
