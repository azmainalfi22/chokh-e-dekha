-- Scheduled breach sweep with two-level escalation.
--
-- sla_due_at has been computed and displayed, but nothing ever acted on it. A
-- deadline that passes in silence is not a deadline: the report sits in the queue
-- looking exactly as it did the day before, and the only person who might notice
-- is the citizen who filed it, who has no way to tell whether anyone else has.
--
-- The sweep marks overdue reports and tells their reporters. That matters here
-- beyond tidiness, because a breach is what makes a report eligible for the
-- official rails — see isEscalationEligible() in lib/sla.ts. Until the breach is
-- recognised, the citizen's route to GRS and 333 stays shut.
--
-- Two levels, and no more. A queue where everything is at level 47 tells nobody
-- anything; the point of a level is to separate "late" from "badly late".

alter table public.reports
  add column if not exists escalation_level smallint not null default 0
    check (escalation_level between 0 and 2),
  add column if not exists escalated_at timestamptz;

create index if not exists reports_escalation_idx
  on public.reports (escalation_level)
  where escalation_level > 0;

-- Hours past the deadline before a breach counts as badly late.
create or replace function public.sla_second_level_grace_hours()
returns integer
language sql
immutable
set search_path = ''
as $$ select 48; $$;

/**
 * Bring every overdue report to the escalation level it should be at.
 *
 * Written as "what level should this be?" rather than "increment", which is what
 * makes it safe to run as often as you like: running it twice in a minute cannot
 * push a report to level 2, and a missed run is caught up by the next one rather
 * than leaving a report a level behind. Only rows whose level actually changes
 * are touched, so a repeat run writes nothing and notifies nobody.
 *
 * Levels only ever move forward. A report that has been escalated is not
 * de-escalated by a cron job, even if someone extends its deadline: that is a
 * decision for a person to record, not a side effect of the clock.
 */
create or replace function public.sweep_sla_breaches()
returns table (escalated_to_1 integer, escalated_to_2 integer)
language plpgsql
security definer
set search_path = public
as $$
declare
  v_now    timestamptz := now();
  v_grace  interval    := make_interval(hours => public.sla_second_level_grace_hours());
  v_level1 integer     := 0;
  v_level2 integer     := 0;
begin
  -- The counter triggers and the guard both look at this; a sweep is a
  -- platform-controlled write, not a citizen editing their own report.
  perform set_config('app.trusted_report_write', 'on', true);

  with moved as (
    update public.reports r
       set escalation_level = target.level,
           escalated_at     = v_now
      from (
        select id,
               case when v_now >= sla_due_at + v_grace then 2 else 1 end as level
          from public.reports
         where is_approved = true
           and status in ('pending', 'in_progress')
           and sla_due_at is not null
           and v_now >= sla_due_at
      ) as target
     where r.id = target.id
       and target.level > r.escalation_level
    returning r.id, r.user_id, r.title, r.escalation_level
  ),
  notified as (
    insert into public.notifications (user_id, report_id, type, title, body)
    select m.user_id,
           m.id,
           'sla_breach',
           case when m.escalation_level = 1
                then 'Response deadline passed'
                else 'Still no response' end,
           case when m.escalation_level = 1
                then 'The deadline for "' || m.title || '" has passed without a resolution. You can now escalate it through GRS or the 333 helpline.'
                else 'No response on "' || m.title || '" well past its deadline. Escalating through the official channels is likely the fastest route now.' end
      from moved m
     where m.user_id is not null
    returning 1
  )
  select
    count(*) filter (where escalation_level = 1),
    count(*) filter (where escalation_level = 2)
    into v_level1, v_level2
    from moved;

  return query select coalesce(v_level1, 0), coalesce(v_level2, 0);
end;
$$;

-- Callable only by the service role, which is what the cron route uses. A
-- citizen has no business running the sweep.
revoke execute on function public.sweep_sla_breaches() from anon, authenticated, public;

comment on function public.sweep_sla_breaches() is
  'Marks overdue reports at escalation level 1 (late) or 2 (past the grace period). Idempotent: safe to run repeatedly. Invoked by POST /api/cron/sla-sweep.';
