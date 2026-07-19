-- Public-read buckets for report evidence and avatars.
-- (The broad public-read SELECT policies here are tightened in the
-- 20260716025924_security_hardening migration.)
insert into storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
values
  ('report-media', 'report-media', true, 10485760,
     array['image/jpeg','image/png','image/webp','image/gif','video/mp4','video/quicktime']),
  ('avatars', 'avatars', true, 2097152,
     array['image/jpeg','image/png','image/webp'])
on conflict (id) do nothing;

create policy "public read report media"
  on storage.objects for select
  using (bucket_id = 'report-media');
create policy "public read avatars"
  on storage.objects for select
  using (bucket_id = 'avatars');

create policy "authed upload report media"
  on storage.objects for insert to authenticated
  with check (bucket_id = 'report-media');
create policy "authed manage own report media"
  on storage.objects for update to authenticated
  using (bucket_id = 'report-media' and owner = auth.uid());
create policy "authed delete own report media"
  on storage.objects for delete to authenticated
  using (bucket_id = 'report-media' and owner = auth.uid());

create policy "authed upload avatar"
  on storage.objects for insert to authenticated
  with check (bucket_id = 'avatars');
create policy "authed update own avatar"
  on storage.objects for update to authenticated
  using (bucket_id = 'avatars' and owner = auth.uid());
