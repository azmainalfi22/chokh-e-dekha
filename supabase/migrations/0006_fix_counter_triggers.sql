-- The endorse/comment counter triggers execute as the acting user, whose
-- RLS policies forbid updating public.reports — the counter UPDATE silently
-- matched 0 rows. Run them as definer like the status-change trigger.

alter function public.sync_endorse_count() security definer;
alter function public.sync_comment_count() security definer;

revoke execute on function public.sync_endorse_count() from anon, authenticated, public;
revoke execute on function public.sync_comment_count() from anon, authenticated, public;
