-- =====================================================================
-- Chokh-e-Dekha demo seed
--
-- Self-contained: creates its own accounts and fills every screen an official
-- would be walked through — the public feed and map, the citizen's dashboard,
-- the moderation queue, an officer's patch, the RTI lifecycle at each stage,
-- SLA breaches and escalations, duplicate grouping, and the audit trail.
--
-- Re-runnable. Everything it creates is removed first, so `supabase db reset`
-- or a second run gives the same result rather than doubling it.
--
-- Sign in with any of:
--   citizen@chokhedekha.test  / demo1234
--   officer@chokhedekha.test  / demo1234
--   admin@chokhedekha.test    / demo1234
-- =====================================================================

-- ---------------------------------------------------------------------
-- Accounts
-- ---------------------------------------------------------------------
-- Written straight into auth.users because there is no signup API during a
-- seed. handle_new_user() creates the matching profile from raw_user_meta_data.

do $$
declare
  v_citizen uuid := '00000000-0000-4000-a000-00000000c171';
  v_citizen2 uuid := '00000000-0000-4000-a000-00000000c172';
  v_officer uuid := '00000000-0000-4000-a000-00000000f1ce';
  v_admin   uuid := '00000000-0000-4000-a000-0000000ad111';
begin
  delete from auth.users where email like '%@chokhedekha.test';

  -- The four token columns must be empty strings rather than NULL. They are
  -- nullable in the schema but GoTrue scans them into non-nullable strings, and
  -- a NULL makes every sign-in fail with "Database error querying schema".
  insert into auth.users (
    instance_id, id, aud, role, email, encrypted_password, email_confirmed_at,
    raw_app_meta_data, raw_user_meta_data, created_at, updated_at,
    confirmation_token, recovery_token, email_change_token_new, email_change
  )
  select
    '00000000-0000-0000-0000-000000000000',
    u.id, 'authenticated', 'authenticated', u.email,
    extensions.crypt('demo1234', extensions.gen_salt('bf')),
    now() - interval '60 days',
    '{"provider":"email","providers":["email"]}'::jsonb,
    jsonb_build_object('display_name', u.name, 'phone', u.phone),
    now() - interval '60 days', now() - interval '60 days',
    '', '', '', ''
  from (values
    (v_citizen,  'citizen@chokhedekha.test',  'Rahim Uddin',     '+8801711000001'),
    (v_citizen2, 'nasrin@chokhedekha.test',   'Nasrin Akter',    '+8801711000002'),
    (v_officer,  'officer@chokhedekha.test',  'Kamrul Hasan',    '+8801711000003'),
    (v_admin,    'admin@chokhedekha.test',    'Farhana Rahman',  '+8801711000004')
  ) as u(id, email, name, phone);

  update public.profiles set role = 'admin',   city = 'Dhaka North City Corporation' where id = v_admin;
  update public.profiles set role = 'officer', city = 'Dhaka North City Corporation' where id = v_officer;
  update public.profiles set city = 'Dhaka North City Corporation' where id in (v_citizen, v_citizen2);
end $$;

-- ---------------------------------------------------------------------
-- Wards, so the officer has a real patch and the scorecards have depth
-- ---------------------------------------------------------------------
-- The migration seeds divisions, districts and city corporations but stops
-- short of wards, because real ward numbering should not be invented in a
-- migration. These are clearly demo wards and live only in the seed.

do $$
declare
  v_dncc bigint;
  n int;
begin
  select id into v_dncc from public.territories
   where type = 'city_corporation' and name = 'Dhaka North City Corporation';

  if v_dncc is null then return; end if;

  delete from public.territories where type = 'ward' and parent_id = v_dncc;

  for n in 1..6 loop
    insert into public.territories (type, name, name_bn, slug, parent_id, code)
    values ('ward', 'Ward ' || n, 'ওয়ার্ড ' || n, 'ward-dncc-' || n, v_dncc, n::text);
  end loop;
end $$;

-- ---------------------------------------------------------------------
-- Reports
-- ---------------------------------------------------------------------

do $$
declare
  v_citizen  uuid;
  v_citizen2 uuid;
  v_officer  uuid;
  v_admin    uuid;
  v_ward1    bigint;
  v_ward2    bigint;
  v_ward3    bigint;
  v_dncc     bigint;
  v_dscc     bigint;
  v_id       bigint;
  v_dup      bigint;
begin
  select id into v_citizen  from auth.users where email = 'citizen@chokhedekha.test';
  select id into v_citizen2 from auth.users where email = 'nasrin@chokhedekha.test';
  select id into v_officer  from auth.users where email = 'officer@chokhedekha.test';
  select id into v_admin    from auth.users where email = 'admin@chokhedekha.test';

  select id into v_dncc from public.territories where type='city_corporation' and name='Dhaka North City Corporation';
  select id into v_dscc from public.territories where type='city_corporation' and name='Dhaka South City Corporation';
  select id into v_ward1 from public.territories where slug='ward-dncc-1';
  select id into v_ward2 from public.territories where slug='ward-dncc-2';
  select id into v_ward3 from public.territories where slug='ward-dncc-3';

  -- Give the officer a patch: two wards, not the whole city, so the boundary
  -- of the role is visible in the demo.
  delete from public.officer_territories where user_id = v_officer;
  insert into public.officer_territories (user_id, territory_id, designation)
  values (v_officer, v_ward1, 'DNCC Ward 1 — Conservancy'),
         (v_officer, v_ward2, 'DNCC Ward 2 — Conservancy');

  delete from public.reports where title like '[Demo]%' or user_id in (v_citizen, v_citizen2);

  -- The seed writes as the table owner, so the publish trigger passes rows
  -- through untouched and each report can be placed exactly where the demo
  -- needs it in the lifecycle.
  insert into public.reports
    (user_id, title, description, category, city_corporation, territory_id, location_text,
     latitude, longitude, status, priority, is_approved, approved_at, sla_due_at,
     status_updated_at, admin_note, assigned_to, escalation_level, escalated_at,
     routed_authority_key, endorse_count, comment_count, view_count, corroboration_count,
     resolution_state, resolved_at, created_at)
  values
    -- Breached and escalated: the case the SLA and escalation screens exist for.
    (v_citizen, 'Open manhole beside Mohakhali school gate',
     'The cover has been missing for over a week directly outside the school gate. Children walk past it twice a day and at night it is invisible. Someone will fall in.',
     'Public Safety', 'Dhaka North City Corporation', v_ward1, 'Mohakhali, beside Ideal School gate',
     23.7806, 90.4072, 'in_progress', 'high', true, now() - interval '16 days', now() - interval '13 days',
     now() - interval '12 days', 'Barricade placed. Permanent cover requisitioned from stores.', v_officer,
     2, now() - interval '9 days', 'dncc', 148, 12, 1043, 6, null, null, now() - interval '17 days'),

    (v_citizen2, 'Gas smell in Banani lane 11 for three days',
     'A strong smell of gas along the whole lane since Sunday. Residents are afraid to light stoves. Titas was called twice with no visit.',
     'Gas', 'Dhaka North City Corporation', v_ward2, 'Banani Road 11',
     23.7936, 90.4043, 'in_progress', 'high', true, now() - interval '9 days', now() - interval '6 days',
     now() - interval '5 days', 'Referred to Titas Gas emergency cell.', v_officer,
     1, now() - interval '5 days', 'titas', 96, 8, 720, 4, null, null, now() - interval '10 days'),

    -- Comfortably inside its window.
    (v_citizen, 'Waterlogging at Jatrabari after every shower',
     'Even twenty minutes of rain floods this intersection knee-deep. Rickshaws stall and shops shut. The drains have not been cleared this season.',
     'Drainage / Waterlogging', 'Dhaka South City Corporation', v_dscc, 'Jatrabari intersection',
     23.7104, 90.4340, 'in_progress', 'high', true, now() - interval '2 days', now() + interval '1 day',
     now() - interval '1 day', 'Drain-cleaning crew scheduled for Thursday.', v_admin,
     0, null, 'dscc', 61, 5, 402, 3, null, null, now() - interval '3 days'),

    -- Resolved, awaiting the citizen's confirmation: the trust loop.
    (v_citizen, 'Streetlights dead along Dhanmondi 27',
     'The entire stretch between road 27 and the lake is dark after sunset. Women walking home from the bus stop have stopped using the path.',
     'Streetlight', 'Dhaka North City Corporation', v_ward3, 'Dhanmondi Road 27',
     23.7566, 90.3720, 'resolved', 'medium', true, now() - interval '21 days', now() - interval '14 days',
     now() - interval '3 days', 'All eleven fixtures replaced and tested on site.', v_officer,
     0, null, 'dncc', 212, 19, 1580, 9, 'pending_confirmation', now() - interval '3 days', now() - interval '22 days'),

    -- Confirmed fixed: the happy ending the dashboard should show.
    (v_citizen2, 'No water supply in Mohammadpur block C',
     'Zero supply since Monday with no notice from WASA. Four buildings affected, families buying drinking water by the jar.',
     'Water Supply', 'Dhaka North City Corporation', v_ward1, 'Mohammadpur, Sher Shah Suri Road',
     23.7599, 90.3596, 'resolved', 'high', true, now() - interval '25 days', now() - interval '22 days',
     now() - interval '18 days', 'Supply main repaired; pressure restored and verified.', v_officer,
     0, null, 'wasa', 74, 6, 510, 2, 'confirmed', now() - interval '18 days', now() - interval '26 days'),

    -- Disputed: the authority said fixed, the citizen says otherwise.
    (v_citizen, 'Sewage overflow in Shakhari Bazar lane',
     'Raw sewage running down a residential lane in Old Dhaka. Children play in this lane. It was declared fixed but it is exactly as it was.',
     'Sewerage', 'Dhaka South City Corporation', v_dscc, 'Shakhari Bazar, Old Dhaka',
     23.7104, 90.4074, 'in_progress', 'medium', true, now() - interval '14 days', now() + interval '4 days',
     now() - interval '2 days', null, v_admin,
     0, null, 'dscc', 133, 22, 890, 7, 'disputed', null, now() - interval '15 days'),

    -- Awaiting moderation: what the admin queue is for.
    (v_citizen2, 'Illegal construction has taken the footpath in Uttara',
     'A building extension now covers the entire footpath, pushing pedestrians into traffic on a busy road. Work continues at night.',
     'Illegal Construction', 'Dhaka North City Corporation', v_ward2, 'Uttara Sector 7, Road 12',
     23.8759, 90.3795, 'pending', 'medium', false, null, null,
     null, null, null, 0, null, 'rajuk', 0, 0, 4, 0, null, null, now() - interval '6 hours'),

    (v_citizen, 'Traffic signal dark at Shahbagh since Tuesday',
     'The signal at this junction has been out for two days. Traffic police are managing by hand at rush hour and it is chaos the rest of the day.',
     'Traffic', 'Dhaka South City Corporation', v_dscc, 'Shahbagh intersection',
     23.7389, 90.3958, 'pending', 'medium', false, null, null,
     null, null, null, 0, null, 'dscc', 0, 0, 11, 0, null, null, now() - interval '3 hours'),

    (v_citizen2, 'Rubbish piled outside Karwan Bazar kitchen market',
     'The community bin beside the kitchen market has not been emptied in days. Waste has spread across the footpath and the smell carries a block.',
     'Garbage / Waste', 'Dhaka North City Corporation', v_ward1, 'Karwan Bazar kitchen market',
     23.7509, 90.3935, 'pending', 'medium', false, null, null,
     null, null, null, 0, null, 'dncc', 0, 0, 7, 0, null, null, now() - interval '1 hour'),

    -- Published and quiet, for a full-looking public feed.
    (v_citizen, 'Broken road divider on Airport Road',
     'The central divider is broken across several metres near Banani and vehicles are cutting across it at speed.',
     'Road', 'Dhaka North City Corporation', v_ward3, 'Airport Road, Banani',
     23.7936, 90.4043, 'in_progress', 'medium', true, now() - interval '4 days', now() + interval '3 days',
     now() - interval '3 days', 'Inspection done; barrier sections ordered.', null,
     0, null, 'rhd', 45, 3, 260, 1, null, null, now() - interval '5 days'),

    (v_citizen2, 'Fallen tree blocking a lane in Gulshan 2',
     'A large tree came down in the storm and is blocking one lane completely. Traffic is backed up towards the roundabout.',
     'Other', 'Dhaka North City Corporation', v_ward3, 'Gulshan 2 roundabout',
     23.7925, 90.4152, 'resolved', 'medium', true, now() - interval '6 days', now() - interval '1 day',
     now() - interval '1 day', 'Cleared by the corporation crew within 24 hours.', v_officer,
     0, null, 'dncc', 88, 4, 430, 2, 'confirmed', now() - interval '1 day', now() - interval '7 days'),

    (v_citizen, 'পুরান ঢাকায় রাস্তার বাতি নষ্ট হয়ে আছে',
     'নবাবপুর রোডের মোড়ে রাস্তার বাতিগুলো প্রায় দুই সপ্তাহ ধরে জ্বলছে না। সন্ধ্যার পর পুরো এলাকা অন্ধকার হয়ে থাকে এবং চলাচল করা কঠিন।',
     'Streetlight', 'Dhaka South City Corporation', v_dscc, 'নবাবপুর রোড, পুরান ঢাকা',
     23.7194, 90.4106, 'in_progress', 'medium', true, now() - interval '3 days', now() + interval '4 days',
     now() - interval '2 days', 'সরেজমিন পরিদর্শন সম্পন্ন হয়েছে।', v_officer,
     0, null, 'dscc', 57, 7, 318, 3, null, null, now() - interval '4 days');

  -- A duplicate pair, so the grouping is visible rather than theoretical.
  select id into v_id from public.reports where title = 'Rubbish piled outside Karwan Bazar kitchen market';

  insert into public.reports
    (user_id, title, description, category, city_corporation, territory_id, location_text,
     latitude, longitude, status, priority, is_approved, routed_authority_key,
     duplicate_of_id, duplicate_confidence, duplicate_checked_at, created_at)
  values
    (v_citizen, 'Garbage not collected at Karwan Bazar market for days',
     'The bin by the kitchen market is overflowing again and rubbish is all over the footpath. It has not been emptied for several days.',
     'Garbage / Waste', 'Dhaka North City Corporation', v_ward1, 'Karwan Bazar, near the kitchen market',
     23.7511, 90.3937, 'pending', 'medium', false, 'dncc',
     v_id, 0.71, now(), now() - interval '30 minutes')
  returning id into v_dup;

  -- Category suggestions awaiting an admin decision.
  update public.reports
     set ai_suggestion = jsonb_build_object(
           'category', 'Garbage / Waste', 'priority', 'medium', 'confidence', 0.88,
           'rationale', 'Wording matches Garbage / Waste.', 'provider', 'local-heuristic'),
         ai_suggested_at = now() - interval '25 minutes'
   where id = v_dup;

  update public.reports
     set ai_suggestion = jsonb_build_object(
           'category', 'Public Safety', 'priority', 'high', 'confidence', 0.41,
           'rationale', 'Wording matches Public Safety. Also looks like Illegal Construction.',
           'provider', 'local-heuristic'),
         ai_suggested_at = now() - interval '5 hours'
   where title = 'Illegal construction has taken the footpath in Uttara';

  update public.reports
     set ai_suggestion = jsonb_build_object(
           'category', 'Traffic', 'priority', 'medium', 'confidence', 0.93,
           'rationale', 'Wording matches Traffic.', 'provider', 'local-heuristic'),
         ai_suggested_at = now() - interval '2 hours'
   where title = 'Traffic signal dark at Shahbagh since Tuesday';

  -- Engagement, so the public feed is not a wall of zeroes.
  delete from public.report_endorsements where user_id in (v_citizen, v_citizen2, v_officer, v_admin);
  insert into public.report_endorsements (report_id, user_id)
  select r.id, v_citizen2 from public.reports r where r.user_id = v_citizen and r.is_approved
  on conflict do nothing;
  insert into public.report_endorsements (report_id, user_id)
  select r.id, v_citizen from public.reports r where r.user_id = v_citizen2 and r.is_approved
  on conflict do nothing;

  delete from public.report_comments where user_id in (v_citizen, v_citizen2, v_officer, v_admin);
  insert into public.report_comments (report_id, user_id, body)
  select r.id, v_citizen2,
         'Same problem on our side of the road. Happy to show anyone from the office where it is.'
    from public.reports r
   where r.title = 'Open manhole beside Mohakhali school gate';

  insert into public.report_comments (report_id, user_id, body)
  select r.id, v_officer,
         'Barricade is up as of this morning. The permanent cover is ordered and I will update here when it is fitted.'
    from public.reports r
   where r.title = 'Open manhole beside Mohakhali school gate';

  insert into public.report_comments (report_id, user_id, body)
  select r.id, v_citizen,
         'এখনো ঠিক হয়নি। গতকাল রাতেও পুরো রাস্তা অন্ধকার ছিল।'
    from public.reports r
   where r.title = 'পুরান ঢাকায় রাস্তার বাতি নষ্ট হয়ে আছে';

  -- ---------------------------------------------------------------------
  -- Escalations, at each stage of the official rails
  -- ---------------------------------------------------------------------
  delete from public.report_escalations where user_id in (v_citizen, v_citizen2);

  insert into public.report_escalations
    (report_id, user_id, channel, language, complaint_body, reference_no, outcome, filed_at, created_at)
  select r.id, v_citizen, 'grs', 'en',
    'To the Grievance Redress Officer,' || chr(10) || chr(10) ||
    'I reported an open manhole directly outside a school gate in Mohakhali on ' ||
    to_char(r.created_at, 'DD Month YYYY') ||
    ' through Chokh-e-Dekha. The statutory response window has passed without a resolution. ' ||
    'I request that the responsible office be directed to fit a permanent cover without further delay.',
    'GRS-' || extract(year from now())::text || '-472913', 'acknowledged', now() - interval '6 days',
    now() - interval '7 days'
  from public.reports r where r.title = 'Open manhole beside Mohakhali school gate';

  insert into public.report_escalations
    (report_id, user_id, channel, language, complaint_body, reference_no, outcome, filed_at, created_at)
  select r.id, v_citizen2, 'helpline_333', 'bn',
    '৩৩৩ হেল্পলাইনে জানানোর জন্য: বনানী ১১ নম্বর রোডে তিন দিন ধরে তীব্র গ্যাসের গন্ধ পাওয়া যাচ্ছে। ' ||
    'তিতাস গ্যাসকে দুইবার জানানো হলেও কেউ আসেনি। জরুরি ভিত্তিতে ব্যবস্থা নেওয়া প্রয়োজন।',
    '333-' || to_char(now(), 'YYYYMM') || '-58204', 'filed', now() - interval '2 days',
    now() - interval '2 days'
  from public.reports r where r.title = 'Gas smell in Banani lane 11 for three days';

  insert into public.report_escalations
    (report_id, user_id, channel, language, complaint_body, outcome, created_at)
  select r.id, v_citizen, 'written', 'en',
    'To the Chief Executive Officer,' || chr(10) || chr(10) ||
    'The sewage overflow reported in Shakhari Bazar was recorded as resolved, but the condition is unchanged. ' ||
    'I am writing to request a re-inspection and a written account of what work was carried out.',
    'drafted', now() - interval '1 day'
  from public.reports r where r.title = 'Sewage overflow in Shakhari Bazar lane';

  -- ---------------------------------------------------------------------
  -- RTI lifecycle, one letter at each stage
  -- ---------------------------------------------------------------------
  delete from public.rti_letters where user_id in (v_citizen, v_citizen2);

  insert into public.rti_letters
    (user_id, report_id, authority, subject, body, language, status,
     submitted_at, deadline_at, responded_at, outcome, created_at)
  select
    v_citizen, r.id, 'Dhaka North City Corporation',
    'Maintenance records for streetlights on Dhanmondi Road 27',
    'To,' || chr(10) || 'The Designated Officer' || chr(10) || 'Dhaka North City Corporation' || chr(10) || chr(10) ||
    'Under Section 8 of the Right to Information Act, 2009, I request the maintenance and inspection records for the streetlights on Dhanmondi Road 27 for the last twelve months, together with the contractor responsible.',
    'en', 'responded', now() - interval '40 days', now() - interval '12 days',
    now() - interval '15 days', 'received', now() - interval '42 days'
  from public.reports r where r.title = 'Streetlights dead along Dhanmondi 27';

  insert into public.rti_letters
    (user_id, report_id, authority, subject, body, language, status,
     submitted_at, deadline_at, created_at)
  select
    v_citizen, r.id, 'Dhaka North City Corporation',
    'Action taken on the open manhole at Mohakhali',
    'To,' || chr(10) || 'The Designated Officer' || chr(10) || 'Dhaka North City Corporation' || chr(10) || chr(10) ||
    'Under Section 8 of the Right to Information Act, 2009, I request copies of all correspondence and work orders relating to the missing manhole cover outside the school gate at Mohakhali.',
    'en', 'submitted', now() - interval '9 days', now() + interval '11 days', now() - interval '10 days'
  from public.reports r where r.title = 'Open manhole beside Mohakhali school gate';

  insert into public.rti_letters
    (user_id, authority, subject, body, language, status, created_at)
  values
    (v_citizen2, 'Dhaka WASA',
     'Water supply interruption notices for Mohammadpur',
     'To,' || chr(10) || 'The Designated Officer' || chr(10) || 'Dhaka WASA' || chr(10) || chr(10) ||
     'Under Section 8 of the Right to Information Act, 2009, I request the notices issued for planned water supply interruptions in Mohammadpur over the past six months.',
     'en', 'drafted', now() - interval '2 days');

  -- An overdue one, so the appeal path is reachable in the demo.
  insert into public.rti_letters
    (user_id, authority, subject, body, language, status,
     submitted_at, deadline_at, outcome, created_at)
  values
    (v_citizen, 'Rajdhani Unnayan Kartripakkha (RAJUK)',
     'Approved building plan for the Uttara Sector 7 extension',
     'To,' || chr(10) || 'The Designated Officer' || chr(10) || 'RAJUK' || chr(10) || chr(10) ||
     'Under Section 8 of the Right to Information Act, 2009, I request a copy of the approved building plan for the construction at Uttara Sector 7, Road 12.',
     'en', 'submitted', now() - interval '45 days', now() - interval '12 days',
     'no_response', now() - interval '46 days');
end $$;

-- ---------------------------------------------------------------------
-- A survey, if none exists
-- ---------------------------------------------------------------------

insert into public.surveys (title, description, questions, is_active, created_by)
select
  'Which civic issues matter most in your neighbourhood?',
  'Two minutes. Your answers help the city corporation decide where to spend next quarter.',
  '[{"id":"q1","type":"single","prompt":"What should be fixed first on your street?","options":["Roads and footpaths","Drainage and waterlogging","Street lighting","Waste collection","Water supply"]},{"id":"q2","type":"single","prompt":"In the last year, has a report of yours been resolved?","options":["Yes, within the deadline","Yes, but late","No","I have not reported anything"]}]'::jsonb,
  true,
  (select id from auth.users where email = 'admin@chokhedekha.test')
where not exists (select 1 from public.surveys);
