-- GRS / 333 escalation bridge (roadmap #4).
-- When a report is stuck (SLA breach or a disputed "fix"), the reporter can
-- escalate through Bangladesh's official rails: the national Grievance
-- Redress System (grs.gov.bd), the 333 helpline, or a written complaint.
-- We generate the complaint, and this table tracks the official reference
-- number and outcome — publicly, for accountability.

create table public.report_escalations (
  id             bigint generated always as identity primary key,
  report_id      bigint not null references public.reports (id) on delete cascade,
  user_id        uuid   not null references public.profiles (id) on delete cascade,
  channel        text   not null check (channel in ('grs','helpline_333','written')),
  language       text   not null default 'en' check (language in ('en','bn')),
  complaint_body text   not null,
  reference_no   text,
  outcome        text   not null default 'drafted'
                   check (outcome in ('drafted','filed','acknowledged','resolved','no_response')),
  filed_at       timestamptz,
  created_at     timestamptz not null default now(),
  updated_at     timestamptz not null default now(),
  unique (report_id, user_id, channel)
);
create index report_escalations_report_idx on public.report_escalations (report_id);

alter table public.report_escalations enable row level security;

-- Public read: an escalation is part of the report's public accountability trail.
create policy "escalations readable"
  on public.report_escalations for select using (true);
-- Only the report's owner can escalate their own approved report.
create policy "owner escalates own report"
  on public.report_escalations for insert
  with check (
    auth.uid() = user_id
    and exists (
      select 1 from public.reports r
      where r.id = report_id and r.user_id = auth.uid() and r.is_approved = true
    )
  );
create policy "owner updates own escalation"
  on public.report_escalations for update
  using (auth.uid() = user_id) with check (auth.uid() = user_id);
create policy "owner deletes own escalation"
  on public.report_escalations for delete using (auth.uid() = user_id);

create trigger report_escalations_touch_trg
  before update on public.report_escalations
  for each row execute function public.touch_updated_at();
