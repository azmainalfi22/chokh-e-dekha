-- Resolution verification & auto-reopen ("prove it's actually fixed").
-- When an admin marks a report Resolved it enters 'pending_confirmation'.
-- The reporter can confirm the fix or dispute it; a dispute reopens the
-- report (back to in_progress) and resumes the SLA clock. This keeps the
-- public "resolved" numbers honest.

alter table public.reports
  add column if not exists resolution_state text
    check (resolution_state in ('pending_confirmation', 'confirmed', 'disputed')),
  add column if not exists resolved_at timestamptz,
  add column if not exists dispute_reason text;

create index if not exists reports_resolution_state_idx
  on public.reports (resolution_state);

-- Reporter confirms the fix. Owner-only; only from pending_confirmation.
create or replace function public.confirm_resolution(p_report_id bigint)
returns void
language plpgsql
security definer
set search_path = public
as $$
begin
  update public.reports
     set resolution_state = 'confirmed'
   where id = p_report_id
     and user_id = auth.uid()
     and status = 'resolved'
     and resolution_state = 'pending_confirmation';
  if not found then
    raise exception 'not allowed';
  end if;
end;
$$;

-- Reporter disputes the fix → reopen + resume SLA. Owner-only.
create or replace function public.dispute_resolution(
  p_report_id bigint,
  p_reason text
)
returns void
language plpgsql
security definer
set search_path = public
as $$
begin
  update public.reports
     set status = 'in_progress',
         resolution_state = 'disputed',
         dispute_reason = nullif(btrim(p_reason), ''),
         admin_note = null,
         sla_due_at = now() + interval '7 days'
   where id = p_report_id
     and user_id = auth.uid()
     and status = 'resolved'
     and resolution_state = 'pending_confirmation';
  if not found then
    raise exception 'not allowed';
  end if;
end;
$$;

grant execute on function public.confirm_resolution(bigint) to authenticated;
grant execute on function public.dispute_resolution(bigint, text) to authenticated;
