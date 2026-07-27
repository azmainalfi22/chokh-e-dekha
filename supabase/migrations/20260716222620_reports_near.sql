-- Proximity search over approved reports (haversine, no PostGIS needed).
-- Powers duplicate detection on submit and "reports near me". Only exposes
-- approved public reports, so it is safe to call from anon/authenticated.

create or replace function public.reports_near(
  p_lat double precision,
  p_lng double precision,
  p_radius_m integer default 600,
  p_category text default null,
  p_exclude bigint default null
)
returns table (
  id bigint,
  title text,
  category text,
  city_corporation text,
  status text,
  latitude double precision,
  longitude double precision,
  endorse_count integer,
  comment_count integer,
  created_at timestamptz,
  distance_m double precision
)
language sql
stable
security definer
set search_path = public
as $$
  select id, title, category, city_corporation, status, latitude, longitude,
         endorse_count, comment_count, created_at, distance_m
  from (
    select r.id, r.title, r.category, r.city_corporation, r.status,
           r.latitude, r.longitude, r.endorse_count, r.comment_count,
           r.created_at,
           6371000 * 2 * asin(sqrt(
             power(sin(radians(r.latitude - p_lat) / 2), 2) +
             cos(radians(p_lat)) * cos(radians(r.latitude)) *
             power(sin(radians(r.longitude - p_lng) / 2), 2)
           )) as distance_m
    from public.reports r
    where r.is_approved = true
      and r.latitude is not null
      and r.longitude is not null
      and (p_category is null or r.category = p_category)
      and (p_exclude is null or r.id <> p_exclude)
      -- bounding-box prefilter so the geo index can help
      and r.latitude between p_lat - (p_radius_m / 111320.0)
                         and p_lat + (p_radius_m / 111320.0)
      and r.longitude between p_lng - (p_radius_m / (111320.0 * cos(radians(p_lat))))
                          and p_lng + (p_radius_m / (111320.0 * cos(radians(p_lat))))
  ) q
  where q.distance_m <= p_radius_m
  order by q.distance_m asc
  limit 20;
$$;

grant execute on function public.reports_near(
  double precision, double precision, integer, text, bigint
) to anon, authenticated;
