-- =====================================================================
-- Chokh-e-Dekha demo seed data
-- Idempotent-ish: clears prior seeded rows (marked with the [seed] tag in
-- admin_note is NOT used; instead we clear by title prefix) then inserts a
-- realistic spread across Dhaka. Author is the demo citizen 'Rahim Uddin'.
-- Run after the two demo users exist (see README).
-- =====================================================================

do $$
declare
  citizen uuid;
  admin uuid;
begin
  select id into citizen from public.profiles where display_name = 'Rahim Uddin' limit 1;
  select id into admin from public.profiles where display_name = 'City Admin' limit 1;
  if citizen is null then
    raise notice 'Demo citizen not found — skipping seed.';
    return;
  end if;

  -- Clear previously seeded demo reports (titles all start with "[Demo] ")
  delete from public.reports where title like '[Demo] %';

  insert into public.reports
    (user_id, title, description, category, city_corporation, location_text,
     latitude, longitude, status, is_approved, approved_at, sla_due_at,
     status_updated_at, admin_note, assigned_to)
  values
    (citizen, '[Demo] Massive pothole on Mirpur Road', 'A deep pothole near Technical crossing has been causing accidents for weeks, especially dangerous for rickshaws and motorcycles at night.', 'Road', 'Dhaka North City Corporation', 'Mirpur Road, near Technical crossing', 23.7925, 90.3540, 'in_progress', true, now() - interval '10 days', now() - interval '3 days', now() - interval '2 days', 'Repair crew scheduled; awaiting bitumen supply.', admin),
    (citizen, '[Demo] Overflowing garbage bin at Karwan Bazar', 'The community bin beside the kitchen market has not been cleared in days. Waste is spilling onto the road and the smell is unbearable.', 'Garbage / Waste', 'Dhaka North City Corporation', 'Karwan Bazar kitchen market', 23.7509, 90.3935, 'pending', true, now() - interval '9 days', now() + interval '5 days', null, null, null),
    (citizen, '[Demo] Broken streetlights in Dhanmondi 27', 'An entire stretch of streetlights between road 27 and the lake is dead, leaving the walkway pitch black after sunset.', 'Streetlight', 'Dhaka North City Corporation', 'Dhanmondi Road 27', 23.7566, 90.3720, 'resolved', true, now() - interval '20 days', now() - interval '13 days', now() - interval '4 days', 'Fixtures replaced and tested. Thank you for reporting.', admin),
    (citizen, '[Demo] Waterlogging after every rain in Jatrabari', 'Even a short shower floods the entire intersection knee-deep. The drains are clearly blocked and need urgent clearing.', 'Drainage / Waterlogging', 'Dhaka South City Corporation', 'Jatrabari intersection', 23.7104, 90.4340, 'in_progress', true, now() - interval '6 days', now() + interval '1 day', now() - interval '1 day', 'Drain-cleaning team assigned to the area.', admin),
    (citizen, '[Demo] Illegal construction blocking footpath in Uttara', 'A building extension has taken over the entire footpath on this road, forcing pedestrians onto a busy street.', 'Illegal Construction', 'Dhaka North City Corporation', 'Uttara Sector 7', 23.8759, 90.3795, 'pending', true, now() - interval '2 days', now() + interval '12 days', null, null, null),
    (citizen, '[Demo] Open manhole near New Market', 'A manhole cover has been missing for over a week right next to a school gate. This is an accident waiting to happen.', 'Public Safety', 'Dhaka South City Corporation', 'New Market, Nilkhet side', 23.7333, 90.3849, 'in_progress', true, now() - interval '15 days', now() - interval '8 days', now() - interval '5 days', 'Temporary barricade placed; permanent cover ordered.', admin),
    (citizen, '[Demo] No water supply for three days in Mohammadpur', 'Our whole block has had zero water supply since Monday with no notice from WASA. Families are struggling.', 'Water Supply', 'Dhaka North City Corporation', 'Mohammadpur, Sher Shah Suri Road', 23.7599, 90.3596, 'resolved', true, now() - interval '18 days', now() - interval '11 days', now() - interval '9 days', 'Supply line repaired and restored.', admin),
    (citizen, '[Demo] Damaged road divider on Airport Road', 'The central divider is broken across several metres, and vehicles are cutting across dangerously.', 'Road', 'Dhaka North City Corporation', 'Airport Road, Banani', 23.7936, 90.4043, 'pending', true, now() - interval '1 day', now() + interval '13 days', null, null, null),
    (citizen, '[Demo] Sewage overflow in Old Dhaka lane', 'Raw sewage is overflowing into a residential lane in Shakhari Bazar. It is a serious health hazard for the whole neighbourhood.', 'Sewerage', 'Dhaka South City Corporation', 'Shakhari Bazar, Old Dhaka', 23.7104, 90.4074, 'in_progress', true, now() - interval '12 days', now() - interval '5 days', now() - interval '3 days', 'Vacuum tanker dispatched; investigating blockage source.', admin),
    (citizen, '[Demo] Fallen tree blocking road in Gulshan', 'A large tree came down in the storm and is completely blocking one lane. Traffic is backed up for a kilometre.', 'Other', 'Dhaka North City Corporation', 'Gulshan 2 roundabout', 23.7925, 90.4152, 'resolved', true, now() - interval '5 days', now() + interval '2 days', now() - interval '1 day', 'Tree cleared by the corporation crew within 24 hours.', admin),
    (citizen, '[Demo] Traffic signal not working at Shahbagh', 'The signal at this major intersection has been dark for two days, causing chaos during rush hour.', 'Traffic', 'Dhaka South City Corporation', 'Shahbagh intersection', 23.7389, 90.3958, 'pending', true, now() - interval '8 hours', now() + interval '14 days', null, null, null);
end $$;

-- A demo survey (only if none exist)
insert into public.surveys (title, description, questions, is_active, created_by)
select
  'Which civic issues matter most in your neighbourhood?',
  'Help us understand local priorities. Takes under a minute.',
  '[
    {"id":"q1","label":"What is the biggest problem in your area right now?","type":"single","options":["Roads","Garbage & waste","Streetlights","Drainage & waterlogging","Water supply"]},
    {"id":"q2","label":"How responsive do you find your city corporation?","type":"rating"},
    {"id":"q3","label":"Anything else you would like the authorities to prioritise?","type":"text"}
  ]'::jsonb,
  true,
  (select id from public.profiles where display_name = 'City Admin' limit 1)
where not exists (select 1 from public.surveys);
