# Progress

Working through BENCHMARK-PLAN.md.

---

## Summary

Every phase of the plan is implemented except Phase 6, which needs credentials
only you can supply. Eight migrations, three SQL test scripts, 37 TypeScript
tests, and a clean `build` / `typecheck` / `lint` / `test`.

**Do this first, before anything else:** none of the SQL has been executed. Apply
the migrations to a branch database and run the three scripts in `supabase/tests/`.
They assert the security fixes actually hold and take about a minute. Details in
the caveat below.

| | |
|---|---|
| Phase 0 — security remediation | done |
| Phase 1 — SLA tiers, breach sweep, duplicate grouping | done |
| Phase 2 — territory hierarchy | done |
| Phase 3 — hash-chained audit log | done |
| Phase 4 — officer role | done |
| Phase 5 — RTI deadline calendar | done, one part needs you |
| Phase 6 — classifier, GRS/333 hardening | not started, needs credentials |

Three things I would want to know if I were reading this cold:

1. **This checkout was on the wrong codebase** for the first part of the night.
   See the next section. Nothing was lost.
2. **The self-publish hole was real and is closed**, along with something the
   study did not mention: the storage bucket accepted uploads into any user's
   folder, and photo GPS reached public URLs because stripping happened only in
   the browser.
3. **`ANNOUNCED_HOLIDAYS` in `lib/holidays.ts` is empty and needs you.** Until a
   year's Eid dates are entered from the gazette, RTI deadlines in that year are
   flagged as not holiday-adjusted rather than silently wrong.

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

## Phase 3 — Hash-chained audit log — **done, unexecuted SQL**

`report_status_logs` records who moved a report and when, but it is an ordinary
table: anything that can write to the database can rewrite it and nothing would
show. It answers *what happened*. This answers *can we prove what happened*, which
is the question that matters when the record concerns an official who would
prefer it said something else.

The trail lives in its own `audit` schema, which is **not** in PostgREST's exposed
schemas, so there is no API surface to it and a bad application migration cannot
take the evidence with it. Every entry hashes its own contents together with the
hash of the entry before it, so an edit breaks that entry and a deletion breaks
the one that follows. `audit.verify()` names the first break and why.

This does not stop someone with superuser rights from rewriting history — nothing
inside the database can — but it does mean they cannot do it quietly.

Recorded: approval, status changes, assignment, deletion, and role changes. Not a
change log of every column: a trail nobody can read is as useless as one nobody
can trust.

---

## Phases 2 and 4 — Territory hierarchy and the officer role — **done, unexecuted SQL**

Done together because the second needs the first: an officer has to be scoped to
somewhere.

**The tree.** One self-referencing table covers division, district, city
corporation, upazila and ward. Bangladesh's geography is not uniform — a city
corporation sits under a district but so does an upazila, and both hold wards —
so one table with a type beats one per level. A materialised path makes "every
report beneath this territory" one indexed query, which is what a scorecard at any
depth is built on. Moving a territory rewrites the subtree; a move that would put
a territory beneath its own descendant is refused.

Seeded with the eight divisions and the places already in `CITIES`, under their
exact names so the backfill is an exact match. **Wards are deliberately not
seeded** — ward numbering is a real administrative fact that changes when
redrawn, and an invented list would put fabricated civic data in front of
citizens. The level is ready and waiting for a real source.

**The officer.** There were two kinds of account, citizen and admin, which is not
enough to run a pilot: the person who should close cases in Ward 12 is not someone
you can hand the whole platform to. An officer works a defined patch — inside it
they see reports before they are public, take ownership, move a report through its
lifecycle and leave an official note. Coverage is by subtree, so assigning a city
covers every ward beneath it.

Two deliberate omissions. An officer **cannot approve** reports: moderation is the
gate Phase 0 spent its time hardening, and handing it to a territory-scoped
account would widen it again. And an officer **cannot edit the citizen's words**,
which are the evidence.

---

## RTI deadlines — **done** (taken out of order)

The item the study flagged as blocking, and the only part of the plan that could
be fully verified tonight rather than only reviewed, so it was done before the
remaining schema phases.

`addWorkingDays` skipped Friday and Saturday and nothing else — its own comment
said *"Public holidays aren't modelled"*. Offices close for national holidays, and
around Eid for several consecutive days, so a 20-working-day deadline computed
without them lands **early**. An appeal under s.24 only opens once the authority's
time has run out, so an appeal filed on an early deadline is premature, can be
rejected, and the citizen starts again.

`lib/holidays.ts` splits the two kinds, because they cannot be handled the same
way:

- **Fixed-date** holidays are applied automatically. Kept to dates that do not
  move and are not in political dispute — several days have been added to and
  removed from Bangladesh's national list over the years, and entering a revoked
  one would push deadlines *later* than they really are.
- **Announced** holidays (both Eids, Ashura, Durga Puja, Shab-e-Barat) move with
  the lunar calendar and are fixed by government notification after a moon
  sighting. They cannot be computed, only looked up.

`ANNOUNCED_HOLIDAYS` is **deliberately empty**. Guessing Eid dates would trade one
wrong deadline for another, and a wrong deadline is the entire problem. Instead the
calculation reports when it has run past the end of the known calendar, and the RTI
screen says the date accounts for weekends but not holidays and that the real
deadline is likely later. A visible caveat beats a confident wrong answer.

Fourteen tests, including the Eid case: with the closure entered, the deadline
moves later, never earlier.

---

## Phase 1 — SLA tiers, breach sweep, duplicate grouping — **done, unexecuted SQL**

**Tiers.** Every report got the same seven days. A gas leak and a park complaint
shared a deadline, which makes the deadline useless as a signal. Priority is now
derived from the category *in the database*, so a reporter cannot mark their own
pothole urgent. High is three days, medium seven, low fourteen; medium is the bulk
of the queue and keeps what everything used to get, so the common case does not
move. Wall-clock days, not working days — a blocked drain does not stop flooding a
street because it is Friday.

The database owns the calculation because it is the only place the two publication
paths meet: trusted-reporter auto-publish and admin approval have to agree.
`approveReports` no longer computes a deadline and reads the computed one back.

**The sweep.** `sla_due_at` was computed and displayed, and nothing ever acted on
it. Beyond the queue looking wrong, that kept a door shut: a breach is what makes a
report eligible for the GRS and 333 rails, so until the breach was recognised the
citizen's route to escalate stayed closed. `sweep_sla_breaches()` marks overdue
reports and notifies their reporters at level 1, and level 2 past a 48-hour grace.
Written as *"what level should this be?"* rather than *"increment"*, so running it
twice in a minute cannot push a report to level 2 and a missed run is caught up by
the next. Two levels and no more. Triggered by `GET /api/cron/sla-sweep`, guarded
by `CRON_SECRET`, scheduled hourly in `vercel.json`.

**Duplicates.** Two people reporting one streetlight became two reports, two SLA
clocks and two sets of statistics. `reports_near()` claims in its own comment to
power duplicate detection on submit, and `findNearbyReports()` exists to call it —
but nothing calls *that*, so it was dead code. Matching now needs all four of same
category, within 150m, within 30 days, and trigram similarity above a threshold,
because none alone is a good signal: a busy intersection collects unrelated
complaints, and "broken road" describes half of Dhaka. Thresholds lean towards
**missing** a match, since a false positive files a citizen's report under a
stranger's. A duplicate keeps its own row and points at the canonical; groups are
always one level deep. Wired into `createReport`, and surfaced in the admin CSV
export so it is usable today without a UI change.

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

**None of the migrations have been executed anywhere.** There is no local
Postgres, no Supabase CLI, and Docker Desktop was not running — and I was not
going to start a desktop application or touch a hosted database while you were
asleep. They are verified by review and by an exhaustive column audit, not by
running.

To close that gap, three scripts in `supabase/tests/` assert the behaviour
actually holds. Each raises on the first failure and rolls back, so they leave
nothing behind and a silent completion means everything passed. Run them against a
local stack or a branch database, **never production**:

```bash
supabase db reset
psql "$DATABASE_URL" -f supabase/tests/phase0_security.sql
psql "$DATABASE_URL" -f supabase/tests/territory_and_officer.sql
psql "$DATABASE_URL" -f supabase/tests/audit_trail.sql
```

- `phase0_security.sql` — tries the self-publish exploit, checks the author can
  still correct a pending report, checks they cannot touch it once published,
  checks a stranger cannot touch it at all, and checks repeated SLA sweeps do not
  escalate past level 2.
- `territory_and_officer.sql` — path and depth, a subtree move, a refused cycle,
  and every boundary of the officer role including that they cannot publish,
  cannot rewrite the citizen's words, and cannot promote themselves.
- `audit_trail.sql` — edits an entry, re-signs a forged entry the way a careful
  attacker would, and deletes one from the middle, asserting each is caught.

What *is* verified by execution here: `npm run build`, `npm run typecheck`,
`npm run lint` and `npm test` (37 tests) all pass.

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
- **No end-to-end test of the RLS fix has been *run*.** The script is written
  (`supabase/tests/phase0_security.sql`); it needs a live Postgres to execute.
- **Each year's announced holidays must be entered** in `lib/holidays.ts` from the
  Cabinet Division's notification. Until a year is entered, RTI deadlines in it
  are flagged as not holiday-adjusted rather than silently wrong. This needs the
  gazette, not a guess, which is why it is left for you.
- **Duplicate groups are not surfaced in the UI.** The linking is real and shows
  in the CSV export, but the admin queue still lists a group as separate rows and
  the report page does not say "also reported by N others". Deliberately left
  rather than half-done.
- **`findNearbyReports()` is still dead code.** It predates this work and would
  make a good "similar reports nearby" step in the submit wizard.

---

## Next

**Phase 6, which needs you.** Both items are blocked on things I cannot supply:

- **The classifier with human confirmation.** The one AI pattern the study says is
  worth copying: model output sits in a JSON column until an admin confirms it.
  The column and the confirmation flow can be built without the model; the
  inference call needs an endpoint and credentials.
- **GRS / 333 bridge hardening.** `lib/grs.ts` generates the complaint today.
  Going further — filing, and reading back a reference number — needs real
  endpoint details and credentials.

Then the follow-ups in the section above, of which the ward data and the holiday
calendar are the two that need a real source rather than a decision.

