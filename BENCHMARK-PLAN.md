# Chokh-e-Dekha — Benchmark Remediation Plan

Derived from the civic-tech benchmark study (CiviCRM plus four ecosystem
projects). The study was supplied as prose with no phase breakdown on disk, so
the phases below are derived from it. This file is the plan of record.

---

## Licensing constraint (hard)

Civic-Flow, CivicPulse and civicconnect ship **no licence file**, so there is no
permission to copy their code at all. FixMyStreet, Alaveteli and CiviCRM are
**AGPL**. Every item below is therefore implemented as our own pattern, written
from scratch. No code is copied from any of them.

---

## Phase 0 — Security remediation (blocking) — **done**

### 0.1 Owner can publish their own report
`owner edits own pending report` restricted which *rows* an author could target
but its `with check` only required the row still belonged to them. No column
guard, so `update reports set is_approved = true` on one's own pending report
self-published it, bypassing the moderation `enforce_publish_policy` applies on
insert. That trigger was `before insert` only and never saw the update.

### 0.2 Published reports stayed owner-editable
`approveReports` set `is_approved` but left `status` at `pending`, and the
owner-edit policy keys on `status`. An approved, public report therefore stayed
editable by its author — they could rewrite the text an officer was working from.
The trusted-reporter auto-publish path had the same shape.

### 0.3 Storage inserts not confined to the uploader's prefix
`authed upload report media` checked only `bucket_id`. Any signed-in user could
write anywhere in the bucket, including under another citizen's folder. The
wizard's `${uid}/${batch}/${n}.jpg` layout was a convention enforced by the
client.

### 0.4 Photo EXIF (GPS) published untouched
Report evidence is served from public URLs. Metadata removal depended on
`browser-image-compression` re-encoding through a canvas — a side effect, in the
browser, skipped entirely by anything posting straight at the storage API.

---

## Phase 1 — SLA tiers, breach sweep, duplicate detection

Patterns observed in CivicConnect, reimplemented from scratch:

- Priority-tiered SLA windows rather than one flat timer. `computeSlaDueAt`
  currently applies a single 7-day window to everything (`DEFAULT_SLA_DAYS`).
- A scheduled breach sweep with two-level auto-escalation. `report_escalations`
  exists; the sweep does not.
- Text-plus-geo duplicate detection that groups matches into one case. There is
  a `reports_near` RPC and a corroboration mechanism, but no grouping.

## Phase 2 — Territory hierarchy

A self-referencing territory tree (division → district → city corporation →
ward), the precondition for ward scorecards. Today `city_corporation` is a bare
string from a fixed list in `lib/constants.ts`.

## Phase 3 — Tamper-evident audit log

A hash-chained audit trail in its own schema, explicitly so a bad migration
cannot destroy the evidence. `report_status_logs` exists but is an ordinary
table: anything that can write to the database can rewrite it.

## Phase 4 — Officer role

Flagged in the study as the largest item and the one that unblocks the most,
including any real pilot. `profiles.role` today is citizen or admin; an officer
needs to be scoped to a territory, able to act on assigned cases, and unable to
administer the platform.

## Phase 5 — RTI deadline arithmetic

**Flagged as blocking.** The study says our arithmetic skips Friday and Saturday
but ignores Bangladeshi public holidays, so around Eid the computed date is early
and an appeal filed on it is premature. `lib/rti.ts` and the
`20260722152442_rti_lifecycle` migration are where this lives.

## Phase 6 — Deferred, needs the user

- **Classifier with human confirmation.** The one AI pattern worth copying:
  model output sits in a JSON column until an admin confirms it. The column and
  the confirmation UI can be built; the inference call needs a model endpoint and
  credentials.
- **GRS / 333 bridge hardening.** `lib/grs.ts` exists; real endpoint details and
  credentials are needed to go beyond generating a letter.

---

## The differentiation argument

Four of the five benchmarked projects are the same product, and all of them
assume a municipality installed the software. This platform already carries what
none of them has: an RTI Act lifecycle, a GRS and 333 bridge, and thirty real
authorities. It works against an unresponsive state rather than only with a
cooperative one. Almost every recommendation above sharpens that.
