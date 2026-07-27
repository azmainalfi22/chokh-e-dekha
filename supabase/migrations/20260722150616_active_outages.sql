-- Utility outage alerts (roadmap #8).
-- Clusters recent, still-open utility reports (water / power / gas) by
-- category + city corporation into live "outages", so citizens can see
-- whether their area is affected and how many neighbours reported it.
-- Reads only already-public (approved) reports; SECURITY DEFINER keeps the
-- filter authoritative and lets anonymous visitors see the board.
create or replace function public.active_outages(p_hours integer default 48)
returns table (
  category         text,
  city_corporation text,
  report_count     bigint,
  first_reported   timestamptz,
  last_reported    timestamptz
)
language sql
stable
security definer
set search_path = public
as $$
  select
    r.category,
    r.city_corporation,
    count(*)::bigint            as report_count,
    min(r.created_at)           as first_reported,
    max(r.created_at)           as last_reported
  from public.reports r
  where r.is_approved = true
    and r.status in ('pending', 'in_progress')
    and r.category in ('Water Supply', 'Electricity', 'Gas')
    and r.created_at >= now() - make_interval(hours => greatest(p_hours, 1))
  group by r.category, r.city_corporation
  order by count(*) desc, max(r.created_at) desc;
$$;

grant execute on function public.active_outages(integer) to anon, authenticated;

-- Count of active same-category reports in a city — powers the inline
-- "N active reports in your area" alert on submit and report pages.
create or replace function public.area_outage_count(
  p_category text,
  p_city     text,
  p_hours    integer default 48
)
returns integer
language sql
stable
security definer
set search_path = public
as $$
  select count(*)::int
  from public.reports r
  where r.is_approved = true
    and r.status in ('pending', 'in_progress')
    and r.category = p_category
    and r.city_corporation = p_city
    and r.created_at >= now() - make_interval(hours => greatest(p_hours, 1));
$$;

grant execute on function public.area_outage_count(text, text, integer) to anon, authenticated;
