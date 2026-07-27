-- Allow trusted server-side contexts (service role / superuser, where there is
-- no JWT so auth.uid() is null) to set roles; block only authenticated
-- non-admins from changing any role.
create or replace function public.guard_role_change()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
  if (new.role is distinct from old.role)
     and auth.uid() is not null
     and not public.is_admin() then
    new.role := old.role;
  end if;
  return new;
end;
$$;
