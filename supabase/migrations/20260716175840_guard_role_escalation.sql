-- SECURITY FIX: the "users update own profile" policy permits updating every
-- column, so a citizen could set their own role to 'admin'. Column-level
-- restrictions aren't expressible in an RLS policy, so guard role changes with
-- a trigger. (Refined in 20260716180020 to allow trusted server contexts.)

create or replace function public.guard_role_change()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
  if (new.role is distinct from old.role) and not public.is_admin() then
    new.role := old.role;
  end if;
  return new;
end;
$$;

revoke execute on function public.guard_role_change() from anon, authenticated, public;

create trigger profiles_guard_role
  before update on public.profiles
  for each row execute function public.guard_role_change();
