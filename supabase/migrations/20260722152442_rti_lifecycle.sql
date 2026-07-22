-- RTI lifecycle tracker (roadmap #11).
-- Turns a one-off letter into an accountability tool: track submission, run
-- the 20-working-day statutory clock (RTI Act 2009, s.9), record the
-- authority's response, and generate the appeal to the Appellate Authority
-- when they miss the deadline or refuse.
alter table public.rti_letters
  add column status text not null default 'drafted'
    check (status in ('drafted','submitted','responded','appealed','closed')),
  add column submitted_at  timestamptz,
  add column deadline_at   timestamptz,
  add column responded_at  timestamptz,
  add column outcome text
    check (outcome in ('received','partial','refused','no_response')),
  add column appeal_body   text,
  add column updated_at    timestamptz not null default now();

create index rti_letters_status_idx on public.rti_letters (user_id, status);

create trigger rti_letters_touch_trg
  before update on public.rti_letters
  for each row execute function public.touch_updated_at();
