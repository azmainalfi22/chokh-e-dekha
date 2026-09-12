-- Confine uploads to the uploader's own folder.
--
-- The insert policies only checked which bucket was being written to:
--
--   create policy "authed upload report media"
--     on storage.objects for insert to authenticated
--     with check (bucket_id = 'report-media');
--
-- Any signed-in user could therefore write an object anywhere in the bucket,
-- including under another citizen's folder. The submit wizard already chooses
-- `${user.id}/${batch}/${n}.jpg`, so the convention existed -- but it was only a
-- convention, enforced by the client, and the client is the one part of this we
-- do not control. Someone posting straight at the storage API could plant files
-- under a stranger's prefix, and since evidence is served from those paths, that
-- is a way to attach material to another person's name.
--
-- The update and delete policies were already scoped by `owner = auth.uid()`.
-- Insert is the one that was open, which is also the one that matters: it is how
-- a file gets there in the first place. For avatars that is fixed with a prefix
-- check; for report evidence it is closed off entirely — see below.
--
-- storage.foldername(name) splits an object path on '/', so element 1 is the
-- first folder. Requiring it to equal the caller's uid makes the layout the
-- wizard already uses the only layout the API will accept.

-- Report evidence: no direct client inserts at all.
--
-- A prefix check alone would still have let a client upload an untouched
-- photograph into its own folder, complete with the GPS coordinates of wherever
-- it was taken, and then attach that path to a report. Evidence is served from
-- public URLs, so that publishes the reporter's location.
--
-- Uploads therefore go through POST /api/report-media, which strips metadata
-- before writing and derives the whole path server-side. Revoking the policy is
-- what turns that route from the polite way in into the only way in.
drop policy if exists "authed upload report media" on storage.objects;

-- Only the formats app/lib/image-metadata.ts can actually strip. gif and the two
-- video types were accepted before; nothing can clean them yet, and an MP4
-- carries GPS in its user-data atom exactly as a JPEG carries Exif. The submit
-- wizard already only offers images, so nothing that worked stops working.
update storage.buckets
   set allowed_mime_types = array['image/jpeg', 'image/png', 'image/webp']
 where id = 'report-media';

drop policy if exists "authed upload avatar" on storage.objects;
create policy "authed upload avatar"
  on storage.objects for insert to authenticated
  with check (
    bucket_id = 'avatars'
    and (storage.foldername(name))[1] = auth.uid()::text
  );

-- Bring the update and delete policies onto the same footing. They already
-- checked ownership, but `owner` is metadata storage sets, whereas the prefix is
-- the thing the rest of the system reads. Checking both means the two cannot
-- disagree -- an object whose owner was somehow reassigned still cannot be moved
-- out of the folder it belongs to.

drop policy if exists "authed manage own report media" on storage.objects;
create policy "authed manage own report media"
  on storage.objects for update to authenticated
  using (
    bucket_id = 'report-media'
    and owner = auth.uid()
    and (storage.foldername(name))[1] = auth.uid()::text
  )
  with check (
    bucket_id = 'report-media'
    and (storage.foldername(name))[1] = auth.uid()::text
  );

drop policy if exists "authed delete own report media" on storage.objects;
create policy "authed delete own report media"
  on storage.objects for delete to authenticated
  using (
    bucket_id = 'report-media'
    and owner = auth.uid()
    and (storage.foldername(name))[1] = auth.uid()::text
  );

drop policy if exists "authed update own avatar" on storage.objects;
create policy "authed update own avatar"
  on storage.objects for update to authenticated
  using (
    bucket_id = 'avatars'
    and owner = auth.uid()
    and (storage.foldername(name))[1] = auth.uid()::text
  )
  with check (
    bucket_id = 'avatars'
    and (storage.foldername(name))[1] = auth.uid()::text
  );
