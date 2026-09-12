-- The administrative geography reports are filed against.
--
-- A report's location is a bare string in reports.city_corporation, chosen from
-- a fixed list in lib/constants.ts. That cannot answer "how is Dhaka division
-- doing", cannot roll a ward up into a city corporation, and cannot be corrected
-- without a deploy. Ward scorecards are impossible without this, which is why it
-- comes before the officer role — an officer has to be scoped to somewhere.
--
-- One self-referencing table covers every level: division, district, city
-- corporation, upazila, ward. Bangladesh's geography is not uniform — a city
-- corporation sits under a district but so does an upazila, and both hold wards —
-- so one table with a type beats one table per level.
--
-- Alongside parent_id each row stores a materialised path ("/1/9/40/"). That
-- makes "every report anywhere beneath this territory" a single indexed LIKE,
-- which is what a scorecard at any depth is built on. A recursive CTE would also
-- work, but these trees are shallow and change rarely, so paying once on write
-- beats paying on every read.
--
-- city_corporation is left in place rather than dropped: every filter, export and
-- public query reads it, and removing it is a separate change that should not
-- ride along with this one.

create table public.territories (
  id         bigint generated always as identity primary key,
  parent_id  bigint references public.territories (id) on delete restrict,
  type       text not null check (type in ('division','district','city_corporation','upazila','ward')),
  name       text not null,
  name_bn    text,
  slug       text not null unique,
  -- Official identifier where one exists (BBS geocode, ward number).
  code       text,
  -- Centroid, for framing a map. Not a boundary: this table holds no polygons.
  latitude   double precision,
  longitude  double precision,
  -- Every ancestor id, this row's own id last.
  path       text,
  depth      smallint not null default 0,
  is_active  boolean not null default true,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),
  unique (parent_id, type, name)
);

create index territories_parent_idx on public.territories (parent_id);
create index territories_type_idx   on public.territories (type);
create index territories_path_idx   on public.territories (path text_pattern_ops);

alter table public.reports
  add column if not exists territory_id bigint
    -- A report outlives a redrawn boundary. Losing the link is better than
    -- losing the report.
    references public.territories (id) on delete set null;

create index if not exists reports_territory_idx on public.reports (territory_id);

-- ---------------------------------------------------------------------------
-- Path maintenance
-- ---------------------------------------------------------------------------
-- Kept in the database rather than asked of callers, so path can never drift out
-- of step with parent_id.

create or replace function public.territory_set_lineage()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
declare
  v_parent public.territories;
begin
  -- A subtree rewrite already computed this row's path by string surgery; do not
  -- recompute it from the parent and do not let the two triggers re-enter each
  -- other.
  if coalesce(current_setting('app.territory_rewrite', true), '') = 'on' then
    return new;
  end if;

  if new.parent_id is not null then
    if new.parent_id = new.id then
      raise exception 'A territory cannot be its own parent.';
    end if;

    select * into v_parent from public.territories where id = new.parent_id;

    if not found then
      raise exception 'Parent territory % does not exist.', new.parent_id;
    end if;

    -- Refuse to create a cycle: the intended parent must not sit beneath this row.
    if v_parent.path is not null and old.path is not null
       and v_parent.path like old.path || '%' then
      raise exception 'That move would put % beneath itself.', new.name;
    end if;

    new.depth := v_parent.depth + 1;
    new.path  := rtrim(coalesce(v_parent.path, '/'), '/') || '/' || new.id || '/';
  else
    new.depth := 0;
    new.path  := '/' || new.id || '/';
  end if;

  return new;
end;
$$;

revoke execute on function public.territory_set_lineage() from anon, authenticated, public;

-- On insert the row has no id until after the write, so the path is completed
-- afterwards. The update trigger keeps it correct from then on.
create or replace function public.territory_fill_path()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
declare
  v_parent_path text;
  v_depth       smallint;
begin
  select coalesce(path, '/'), coalesce(depth, -1) + 1
    into v_parent_path, v_depth
    from public.territories where id = new.parent_id;

  update public.territories
     set path  = rtrim(coalesce(v_parent_path, '/'), '/') || '/' || new.id || '/',
         depth = coalesce(v_depth, 0)
   where id = new.id;

  return null;
end;
$$;

revoke execute on function public.territory_fill_path() from anon, authenticated, public;

-- Moving a territory has to rewrite the subtree beneath it, since every
-- descendant's path embeds this row's ancestry.
create or replace function public.territory_rewrite_descendants()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
  if new.path is not distinct from old.path then
    return null;
  end if;

  -- Already inside a rewrite: the statement below covers the whole subtree in
  -- one go, so a nested pass would only redo work.
  if coalesce(current_setting('app.territory_rewrite', true), '') = 'on' then
    return null;
  end if;

  perform set_config('app.territory_rewrite', 'on', true);

  -- Swap this row's old path prefix for the new one, keeping each descendant's
  -- own tail intact, and shift depth by the same amount this row moved.
  update public.territories d
     set path  = new.path || substring(d.path from length(old.path) + 1),
         depth = d.depth + (new.depth - old.depth)
   where d.path like old.path || '_%';

  perform set_config('app.territory_rewrite', 'off', true);

  return null;
end;
$$;

revoke execute on function public.territory_rewrite_descendants() from anon, authenticated, public;

create trigger territories_lineage_trg
  before update on public.territories
  for each row execute function public.territory_set_lineage();

create trigger territories_fill_path_trg
  after insert on public.territories
  for each row execute function public.territory_fill_path();

create trigger territories_rewrite_descendants_trg
  after update on public.territories
  for each row execute function public.territory_rewrite_descendants();

create trigger territories_touch_trg
  before update on public.territories
  for each row execute function public.touch_updated_at();

-- ---------------------------------------------------------------------------
-- Reading the tree
-- ---------------------------------------------------------------------------

/** Every report filed anywhere beneath a territory, including on it directly. */
create or replace function public.reports_in_territory(p_territory_id bigint)
returns setof public.reports
language sql
stable
security definer
set search_path = public
as $$
  select r.*
    from public.reports r
    join public.territories t on t.id = r.territory_id
   where t.path like (select path from public.territories where id = p_territory_id) || '%';
$$;

grant execute on function public.reports_in_territory(bigint) to anon, authenticated;

-- ---------------------------------------------------------------------------
-- Access
-- ---------------------------------------------------------------------------

alter table public.territories enable row level security;

create policy "territories readable by all"
  on public.territories for select using (true);

create policy "admins manage territories"
  on public.territories for all
  using (public.is_admin()) with check (public.is_admin());

-- ---------------------------------------------------------------------------
-- Seed
-- ---------------------------------------------------------------------------
-- The eight divisions, the districts that hold one of the places already in
-- CITIES (lib/constants.ts), and those places themselves, so existing reports
-- map across cleanly.
--
-- Wards are deliberately NOT seeded. Ward boundaries and numbering are real
-- administrative facts that change when they are redrawn, and a plausible
-- invented list would put fabricated civic data in front of citizens. The level
-- exists and is ready; it needs a real source, not a guess.

do $$
declare
  v_division bigint;
  v_district bigint;
  rec record;
begin
  for rec in
    select * from (values
      ('Dhaka',      'ঢাকা',      'Dhaka',       'ঢাকা',       'Dhaka North City Corporation',  'ঢাকা উত্তর সিটি কর্পোরেশন',  23.8103, 90.4125),
      ('Dhaka',      'ঢাকা',      'Dhaka',       'ঢাকা',       'Dhaka South City Corporation',  'ঢাকা দক্ষিণ সিটি কর্পোরেশন', 23.7104, 90.4074),
      ('Dhaka',      'ঢাকা',      'Gazipur',     'গাজীপুর',    'Gazipur City Corporation',      'গাজীপুর সিটি কর্পোরেশন',    23.9999, 90.4203),
      ('Dhaka',      'ঢাকা',      'Narayanganj', 'নারায়ণগঞ্জ', 'Narayanganj City Corporation',  'নারায়ণগঞ্জ সিটি কর্পোরেশন', 23.6238, 90.5000),
      ('Chattogram', 'চট্টগ্রাম',  'Chattogram',  'চট্টগ্রাম',   'Chattogram City Corporation',   'চট্টগ্রাম সিটি কর্পোরেশন',   22.3569, 91.7832),
      ('Chattogram', 'চট্টগ্রাম',  'Cumilla',     'কুমিল্লা',    'Cumilla City Corporation',      'কুমিল্লা সিটি কর্পোরেশন',    23.4607, 91.1809),
      ('Sylhet',     'সিলেট',     'Sylhet',      'সিলেট',      'Sylhet City Corporation',       'সিলেট সিটি কর্পোরেশন',      24.8949, 91.8687),
      ('Rajshahi',   'রাজশাহী',   'Rajshahi',    'রাজশাহী',    'Rajshahi City Corporation',     'রাজশাহী সিটি কর্পোরেশন',    24.3745, 88.6042),
      ('Khulna',     'খুলনা',     'Khulna',      'খুলনা',      'Khulna City Corporation',       'খুলনা সিটি কর্পোরেশন',      22.8456, 89.5403),
      ('Barishal',   'বরিশাল',    'Barishal',    'বরিশাল',     'Barishal City Corporation',     'বরিশাল সিটি কর্পোরেশন',     22.7010, 90.3535),
      ('Rangpur',    'রংপুর',     'Rangpur',     'রংপুর',      'Rangpur City Corporation',      'রংপুর সিটি কর্পোরেশন',      25.7439, 89.2752),
      ('Mymensingh', 'ময়মনসিংহ', 'Mymensingh',  'ময়মনসিংহ',  'Mymensingh City Corporation',   'ময়মনসিংহ সিটি কর্পোরেশন',  24.7471, 90.4203)
    ) as t(division, division_bn, district, district_bn, city, city_bn, lat, lng)
  loop
    -- Division
    select id into v_division from public.territories
     where type = 'division' and name = rec.division and parent_id is null;
    if v_division is null then
      insert into public.territories (type, name, name_bn, slug)
      values ('division', rec.division, rec.division_bn, 'division-' || lower(replace(rec.division, ' ', '-')))
      returning id into v_division;
    end if;

    -- District
    select id into v_district from public.territories
     where type = 'district' and name = rec.district and parent_id = v_division;
    if v_district is null then
      insert into public.territories (type, name, name_bn, slug, parent_id)
      values ('district', rec.district, rec.district_bn,
              'district-' || lower(replace(rec.district, ' ', '-')), v_division)
      returning id into v_district;
    end if;

    -- City corporation. Named exactly as CITIES has it, so the backfill below
    -- is a straight match rather than a fuzzy one.
    insert into public.territories (type, name, name_bn, slug, parent_id, latitude, longitude)
    values ('city_corporation', rec.city, rec.city_bn,
            'city-' || lower(replace(replace(rec.city, ' ', '-'), '.', '')),
            v_district, rec.lat, rec.lng)
    on conflict (parent_id, type, name) do nothing;
  end loop;

  -- Bogura appears in CITIES but is a district town rather than a city
  -- corporation, so it is recorded at the level it actually occupies.
  select id into v_division from public.territories where type = 'division' and name = 'Rajshahi';
  if v_division is not null then
    insert into public.territories (type, name, name_bn, slug, parent_id)
    values ('district', 'Bogura', 'বগুড়া', 'district-bogura', v_division)
    on conflict (parent_id, type, name) do nothing;
  end if;
end;
$$;

-- ---------------------------------------------------------------------------
-- Backfill
-- ---------------------------------------------------------------------------
-- city_corporation holds exactly the names seeded above, so this is an exact
-- match. Reports filed under "Other" keep a null territory: it is not a place,
-- and inventing one for them would be worse than leaving the link empty.

update public.reports r
   set territory_id = t.id
  from public.territories t
 where r.territory_id is null
   and r.city_corporation is not null
   and t.name = r.city_corporation
   and t.type in ('city_corporation', 'district');
