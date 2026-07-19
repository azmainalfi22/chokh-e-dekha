-- Share/view counters must be incrementable by viewers who cannot UPDATE
-- reports directly under RLS — expose narrow SECURITY DEFINER RPCs instead.

create or replace function public.increment_share_count(p_report_id bigint)
returns void
language sql
security definer
set search_path = public
as $$
  update public.reports
  set share_count = share_count + 1
  where id = p_report_id and is_approved = true;
$$;

create or replace function public.increment_view_count(p_report_id bigint)
returns void
language sql
security definer
set search_path = public
as $$
  update public.reports
  set view_count = view_count + 1
  where id = p_report_id and is_approved = true;
$$;

grant execute on function public.increment_share_count(bigint) to anon, authenticated;
grant execute on function public.increment_view_count(bigint) to anon, authenticated;

-- Realtime delivery for the in-app notification bell (FR-05)
alter publication supabase_realtime add table public.notifications;
