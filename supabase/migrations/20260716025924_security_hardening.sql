-- Security hardening (resolves advisor warnings):
--  - pin function search_path
--  - keep trigger-only SECURITY DEFINER functions off the public RPC surface
--  - drop the broad public-read storage policies (public buckets serve object
--    URLs without them; this prevents clients from listing every file).

alter function public.touch_updated_at()   set search_path = '';
alter function public.sync_endorse_count()  set search_path = public;
alter function public.sync_comment_count()  set search_path = public;

revoke execute on function public.handle_new_user()   from anon, authenticated, public;
revoke execute on function public.log_status_change() from anon, authenticated, public;

drop policy "public read report media" on storage.objects;
drop policy "public read avatars"      on storage.objects;
