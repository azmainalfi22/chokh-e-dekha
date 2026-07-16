-- Public-read buckets for report evidence and avatars
insert into storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
values
  ('report-media', 'report-media', true, 10485760,
     array['image/jpeg','image/png','image/webp','image/gif','video/mp4','video/quicktime']),
  ('avatars', 'avatars', true, 2097152,
     array['image/jpeg','image/png','image/webp'])
on conflict (id) do nothing;

-- Public buckets serve object URLs without a broad SELECT policy, so we only
-- grant write access. Authenticated users can upload; they manage their own files.
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
