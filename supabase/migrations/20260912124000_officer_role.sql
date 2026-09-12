-- The officer role.
--
-- There were two kinds of account: citizens and admins, told apart by
-- profiles.role. That is not enough to run a pilot with a real city corporation,
-- because the person who should be closing cases in Ward 12 is not someone you
-- can hand the whole platform to.
--
-- An officer works a defined patch of the map and nothing else. Inside it they
-- can see reports before they are public, take ownership, move a report through
-- its lifecycle and leave an official note. Outside it they are an ordinary
-- citizen. They cannot promote anyone, cannot change anyone's role, and — the
-- deliberate omission — cannot approve reports. Moderation is the gate this
-- codebase has just spent two migrations hardening, and handing it to a
-- territory-scoped account would widen it again.
--
-- Coverage is by subtree, so assigning a city corporation covers every ward
-- beneath it without anyone maintaining a list. That is what the territory
-- hierarchy was built for.

-- ---------------------------------------------------------------------------
-- The role
-- ---------------------------------------------------------------------------

alter table public.profiles
  drop constraint if exists profiles_role_check;

alter table public.profiles
  add constraint profiles_role_check
  check (role in ('citizen', 'officer', 'admin'));

-- Where each officer works. An officer may cover several areas and an area may
-- have several officers, so this is a join table rather than a column.
create table public.officer_territories (
  id           bigint generated always as identity primary key,
  user_id      uuid   not null references public.profiles (id) on delete cascade,
  territory_id bigint not null references public.territories (id) on delete cascade,
  -- Free text: "Ward Councillor's office", "DNCC Sanitation". Worth recording in
  -- a queue header; not worth a lookup table yet.
  designation  text,
  created_at   timestamptz not null default now(),
  unique (user_id, territory_id)
);

create index officer_territories_user_idx      on public.officer_territories (user_id);
create index officer_territories_territory_idx on public.officer_territories (territory_id);

-- ---------------------------------------------------------------------------
-- Helpers
-- ---------------------------------------------------------------------------
-- SECURITY DEFINER throughout, the same reason is_admin() is: these are called
-- from policies on tables that would otherwise re-enter their own RLS.

create or replace function public.is_officer()
returns boolean
language sql
stable
security definer
set search_path = public
as $$
  select exists (
    select 1 from public.profiles
     where id = auth.uid() and role = 'officer'
  );
$$;

grant execute on function public.is_officer() to authenticated;

/**
 * LIKE patterns matching every territory the current officer covers.
 *
 * One pattern per assigned area: its materialised path followed by '%', which
 * matches the area itself and everything beneath it. Returning patterns rather
 * than ids is what keeps coverage a subtree test instead of an exact match.
 */
create or replace function public.officer_territory_patterns()
returns text[]
language sql
stable
security definer
set search_path = public
as $$
  select coalesce(array_agg(t.path || '%'), '{}')
    from public.officer_territories ot
    join public.territories t on t.id = ot.territory_id
   where ot.user_id = auth.uid()
     and t.path is not null;
$$;

grant execute on function public.officer_territory_patterns() to authenticated;

/** Whether the current officer's patch includes the given territory. */
create or replace function public.officer_covers_territory(p_territory_id bigint)
returns boolean
language sql
stable
security definer
set search_path = public
as $$
  select p_territory_id is not null
     and public.is_officer()
     and exists (
       select 1 from public.territories t
        where t.id = p_territory_id
          and t.path like any (public.officer_territory_patterns())
     );
$$;

grant execute on function public.officer_covers_territory(bigint) to authenticated;

-- ---------------------------------------------------------------------------
-- What an officer can see
-- ---------------------------------------------------------------------------
-- An officer has to see reports in their patch before they are public, because
-- that is most of the work. Everything outside the patch stays exactly as
-- visible as it is to any citizen.

drop policy if exists "approved reports are public" on public.reports;
create policy "approved reports are public"
  on public.reports for select
  using (
    is_approved = true
    or auth.uid() = user_id
    or public.is_admin()
    or public.officer_covers_territory(territory_id)
  );

-- ---------------------------------------------------------------------------
-- What an officer can change
-- ---------------------------------------------------------------------------
-- A separate policy from the citizen one. The column guard lives in the trigger,
-- as it does for citizens, because RLS cannot express "these columns only".

create policy "officers work reports in their patch"
  on public.reports for update
  using (public.officer_covers_territory(territory_id))
  with check (public.officer_covers_territory(territory_id));

-- The guard gains an officer branch. Reproduced whole because the function has
-- to be replaced wholesale; the citizen and insert branches are unchanged.
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

  -- An officer working a report inside their own patch.
  --
  -- They may move it through its lifecycle, take ownership, set priority and
  -- leave an official note. They may NOT publish it: approval is the moderation
  -- gate, and an officer is scoped to a place rather than trusted with the
  -- platform. Nor may they edit the citizen's own words, which are the evidence.
  if public.officer_covers_territory(old.territory_id) then
    new.id                   := old.id;
    new.user_id              := old.user_id;
    new.title                := old.title;
    new.description          := old.description;
    new.category             := old.category;
    new.city_corporation     := old.city_corporation;
    new.territory_id         := old.territory_id;
    new.location_text        := old.location_text;
    new.latitude             := old.latitude;
    new.longitude            := old.longitude;
    new.is_approved          := old.is_approved;
    new.approved_at          := old.approved_at;
    new.auto_published       := old.auto_published;
    new.sla_due_at           := old.sla_due_at;
    new.routed_authority_key := old.routed_authority_key;
    new.view_count           := old.view_count;
    new.share_count          := old.share_count;
    new.endorse_count        := old.endorse_count;
    new.comment_count        := old.comment_count;
    new.corroboration_count  := old.corroboration_count;
    new.created_at           := old.created_at;
    new.duplicate_of_id      := old.duplicate_of_id;
    new.duplicate_confidence := old.duplicate_confidence;
    new.duplicate_checked_at := old.duplicate_checked_at;

    -- Left editable: status, admin_note, priority, assigned_to, assigned_at,
    -- resolution_state, resolved_at, escalation_level, escalated_at.

    return new;
  end if;

  -- A citizen updating their own report.
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
  new.territory_id         := old.territory_id;
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

-- ---------------------------------------------------------------------------
-- Notes on a report
-- ---------------------------------------------------------------------------
-- report_status_logs records who moved a report and when. Officers need to
-- appear there like anyone else; the trigger already stamps auth.uid(), so
-- nothing changes beyond them now being able to cause a transition.

-- ---------------------------------------------------------------------------
-- Access to the assignment table itself
-- ---------------------------------------------------------------------------
-- Who covers which area is not a secret — it is the answer to "who is
-- responsible for my street", which this platform exists to make answerable.
-- Changing it is an admin act.

alter table public.officer_territories enable row level security;

create policy "officer assignments are readable"
  on public.officer_territories for select using (true);

create policy "admins manage officer assignments"
  on public.officer_territories for all
  using (public.is_admin()) with check (public.is_admin());

-- Role changes were already guarded: guard_role_change reverts any role edit not
-- made by an admin, so a citizen cannot make themselves an officer any more than
-- they could make themselves an admin. That guard needs no change — it compares
-- old and new rather than enumerating roles.
