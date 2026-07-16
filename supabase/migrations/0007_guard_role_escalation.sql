-- SECURITY FIX: the "users update own profile" policy permits updating every
-- column, so a citizen could set their own role to 'admin'. Column-level
-- restrictions aren't expressible in an RLS policy, so guard role changes
-- with a trigger: only an existing admin may change any profile's role.

create or replace function public.guard_role_change()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
  -- Block authenticated non-admins from changing any role. Trusted server-side
  -- contexts (service role / superuser) have no JWT (auth.uid() is null) and
  -- are allowed, so admin server actions can still promote/demote users.
  if (new.role is distinct from old.role)
     and auth.uid() is not null
     and not public.is_admin() then
    -- silently keep the previous role instead of erroring the whole update
    new.role := old.role;
  end if;
  return new;
end;
$$;

revoke execute on function public.guard_role_change() from anon, authenticated, public;

create trigger profiles_guard_role
  before update on public.profiles
  for each row execute function public.guard_role_change();
