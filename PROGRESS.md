# Progress

Working through BENCHMARK-PLAN.md.

---

## Read this first

**This checkout was on the wrong codebase for most of the night.** The local
`main` was 32 commits behind and sat on the old Laravel application; `origin/main`
had moved to the Next.js and Supabase rewrite, which is the codebase the benchmark
report describes. Work started against the stale tree before that was established.

Nothing was lost. The Laravel work is preserved on the branch
**`laravel-legacy-benchmark-work`** (10 commits, including uncommitted work in
progress captured as a final WIP commit). Local `main` now tracks `origin/main`.
Nothing was pushed, nothing was force-anything, no branch was deleted.

Whether any of that Laravel work is worth porting is a judgement call for you.
Most of it was closing holes that do not exist here; the parts that might carry
over are the metadata stripper (already reimplemented here in TypeScript) and the
hash-chained audit design.

---

## Phase 0 — Security remediation — **done, but unexecuted**

All four items the report named were real and are addressed. One caveat governs
the whole phase, below.

### 0.1 An author could publish their own report

```sql
create policy "owner edits own pending report"
  on public.reports for update
  using      (auth.uid() = user_id and status = 'pending')
  with check (auth.uid() = user_id);
```

The `using` clause said which rows could be targeted. The `with check` said only
that the row must still belong to them — nothing about which **columns** could
change. So:

```sql
update reports set is_approved = true where id = <my own pending report>;
```

passed both clauses and published the report, bypassing the moderation
`enforce_publish_policy` applies so carefully on insert. That trigger was declared
`before insert` only, so it never saw the update. The same gap allowed setting
`priority`, `assigned_to`, `admin_note`, `sla_due_at` and `auto_published`.

Fixed two ways, deliberately overlapping:

- `enforce_publish_policy` now also runs `before update`, where it reverts every
  column a citizen has no business changing. It is an **allowlist** — a citizen
  may change title, description, category, city, location text and coordinates,
  and everything else keeps its previous value. A column added later is protected
  until someone deliberately adds it to the list, which is the right way round.
  The list was checked against the table: 22 guarded plus 8 editable is exactly
  the 30 columns `reports` has.
- The policy gained the column guard it was missing, so a direct attempt fails
  even if the trigger were ever dropped.

Platform-controlled writes are exempted through a transaction-local marker rather
than by widening the column list. This matters: `dispute_resolution` is called by
the *citizen* and must set `status`, `admin_note` and `sla_due_at` — precisely
what a citizen must not set by hand. Only the route can tell them apart. All
seven functions that write to `reports` now set the marker. `set_config` is in
`pg_catalog`, so it is not exposed as a PostgREST RPC and a client cannot set it
for itself.

### 0.2 A published report stayed editable by its author

`approveReports` set `is_approved` but left `status` at `pending`, and the
owner-edit policy keys on `status`. So an approved, public report remained
editable by its author — they could rewrite the text of a live report an officer
was already working from. The trusted-reporter auto-publish path had the same
shape.

Approval now also sets `status` to `in_progress`, in both paths. Since that makes
approval a status change, `log_status_change` would have fired a second
notification on top of the "Report approved" one; it now suppresses its own
notification for the approving statement while still writing the status log.

### 0.3 Uploads were not confined to the uploader's folder

`authed upload report media` checked only `bucket_id`, so any signed-in user could
write anywhere in the bucket, including under another citizen's folder. Evidence
is served from those paths, so that is a way to attach material to someone else's
name. The wizard's `${uid}/${batch}/${n}.jpg` layout was a convention enforced by
the client, and the client is the one part of this we do not control.

Avatars now require the first path segment to equal the caller's uid. Report media
goes further — see below. Update and delete policies now check the prefix as well
as `owner`, so the two cannot disagree.

### 0.4 Photo EXIF (GPS) reached public URLs

A phone photograph carries an Exif block, and that block routinely carries GPS.
For a photo taken before leaving the house, that is the reporter's home address.

Metadata removal previously depended on `browser-image-compression` re-encoding
through a canvas. That works, but it is a side effect, it runs in the browser, and
anything posting straight at the storage API skipped it entirely.

- `lib/image-metadata.ts` strips metadata at the container level — walking JPEG
  marker segments, PNG chunks and WebP RIFF chunks, copying everything except the
  metadata. Lossless, no image library, and it cannot silently recompress a
  citizen's evidence, which matters when the photograph is the whole point of the
  report. JFIF density and ICC colour profiles survive. Written from scratch
  against the container specs.
- `POST /api/report-media` strips before writing and derives the whole storage
  path server-side.
- **Direct client inserts into `report-media` are revoked entirely.** A prefix
  check alone would still have let a client upload an untouched photograph into
  its own folder and attach it. Revoking the policy is what turns the route from
  the polite way in into the only way in. The upload therefore runs with the
  service role; every part of the path is server-derived.
- A file whose metadata cannot be stripped is **refused**, not stored untouched.

Nine tests cover the stripper, including that the GPS IFD pointer is gone, that
entropy-coded picture data is untouched, and that a truncated file is refused
rather than guessed at.

### The caveat that applies to all of Phase 0

**The two migrations have not been executed anywhere.** There is no local
Postgres, no Supabase CLI, and Docker Desktop was not running — and I was not
going to start a desktop application or touch a hosted database while you were
asleep. They are verified by review and by an exhaustive column audit, not by
running. **Run them against a branch database before trusting them.**

What *is* verified by execution: `npm run build`, `npm run typecheck`,
`npm run lint` and `npm test` all pass.

---

## Decisions taken

- **Moved to `origin/main` rather than continuing on the stale tree.** Preserved
  the old work on a branch first.
- **Allowlist, not blocklist, in the update guard.** New columns fail closed.
- **A transaction-local marker for trusted writes.** The alternative — widening
  the allowed column list — would have re-opened the hole, because the sensitive
  columns are exactly the ones the legitimate RPCs write.
- **Revoked direct uploads instead of only adding a prefix check.** The prefix
  check alone would have left the EXIF stripping advisory.
- **Narrowed accepted upload types to JPEG, PNG and WebP.** `gif`, `video/mp4`
  and `video/quicktime` were in the bucket's allowed list; nothing can strip them
  yet, and an MP4 carries GPS in its user-data atom exactly as a JPEG carries
  Exif. The submit wizard already only offers images, so nothing that worked stops
  working.
- **Tests use Node's built-in runner** (`node --experimental-strip-types --test`)
  rather than adding a test framework. The repo had none, and this adds no
  dependency.

---

## Housekeeping done to this working tree

- Stale Laravel directories (`vendor`, `bootstrap`, `database`, `storage`,
  `backup`, `public/build`) were left behind by the branch switch and broke
  `tsc` and `eslint`. **Moved, not deleted**, to `node_modules/.laravel-residue/`,
  which is gitignored and excluded from compilation. Move them back if you want
  them. The loose `*.md` files and screenshots from the old repo are untouched.
- `.env.local` was created with **placeholder** Supabase values so the build could
  run offline. It is gitignored and points nowhere. Replace it with real values,
  or delete it, before running the app.
- `npm install` replaced the Laravel-era `node_modules`.

---

## Gaps and follow-ups

- **The migrations are unexecuted.** See the caveat above. This is the first
  thing to do.
- **Video and GIF evidence uploads are disabled** by the narrowed mime list.
  Re-enabling needs an MP4 and GIF metadata stripper. This narrows what citizens
  can submit, which is a product call, so it is flagged rather than decided.
- **`avatars` has no upload path in the app yet**, so its prefix policy is
  pre-emptive and untested by any real traffic.
- **No end-to-end test of the RLS fix.** Proving the exploit is closed needs a
  live Postgres. Worth doing against a Supabase branch database.

---

## Next

Phase 1 (SLA tiers, breach sweep, duplicate grouping), then the territory
hierarchy, the hash-chained audit log, the officer role, and the RTI deadline
calendar.

On the RTI item the report flagged as blocking: it is correct, and the code says
so itself. `lib/rti.ts` skips Friday and Saturday and carries the comment *"Public
holidays aren't modelled"*. Around Eid the computed date is early, and an appeal
filed on it is premature.
