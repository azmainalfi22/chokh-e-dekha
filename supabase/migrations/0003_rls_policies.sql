-- Enable RLS everywhere
alter table public.profiles            enable row level security;
alter table public.reports             enable row level security;
alter table public.report_media        enable row level security;
alter table public.report_comments     enable row level security;
alter table public.report_endorsements enable row level security;
alter table public.report_bookmarks    enable row level security;
alter table public.report_status_logs  enable row level security;
alter table public.notifications        enable row level security;
alter table public.surveys             enable row level security;
alter table public.survey_responses    enable row level security;
alter table public.rti_letters         enable row level security;

-- ---- profiles -------------------------------------------------------
create policy "profiles readable by all"
  on public.profiles for select using (true);
create policy "users update own profile"
  on public.profiles for update using (auth.uid() = id) with check (auth.uid() = id);
create policy "admins update any profile"
  on public.profiles for update using (public.is_admin());

-- ---- reports --------------------------------------------------------
create policy "approved reports are public"
  on public.reports for select
  using (is_approved = true or auth.uid() = user_id or public.is_admin());
create policy "citizens create own reports"
  on public.reports for insert
  with check (auth.uid() = user_id);
create policy "owner edits own pending report"
  on public.reports for update
  using (auth.uid() = user_id and status = 'pending')
  with check (auth.uid() = user_id);
create policy "admins manage all reports"
  on public.reports for all
  using (public.is_admin()) with check (public.is_admin());

-- ---- report_media ---------------------------------------------------
create policy "media follows report visibility"
  on public.report_media for select
  using (exists (
    select 1 from public.reports r
    where r.id = report_id
      and (r.is_approved = true or r.user_id = auth.uid() or public.is_admin())
  ));
create policy "owner adds media to own report"
  on public.report_media for insert
  with check (exists (
    select 1 from public.reports r
    where r.id = report_id and r.user_id = auth.uid()
  ));
create policy "admins manage media"
  on public.report_media for all
  using (public.is_admin()) with check (public.is_admin());

-- ---- comments -------------------------------------------------------
create policy "comments visible on visible reports"
  on public.report_comments for select
  using (exists (
    select 1 from public.reports r
    where r.id = report_id
      and (r.is_approved = true or r.user_id = auth.uid() or public.is_admin())
  ));
create policy "authenticated users comment"
  on public.report_comments for insert
  with check (auth.uid() = user_id);
create policy "users delete own comment, admins any"
  on public.report_comments for delete
  using (auth.uid() = user_id or public.is_admin());

-- ---- endorsements ---------------------------------------------------
create policy "endorsements readable"
  on public.report_endorsements for select using (true);
create policy "users endorse"
  on public.report_endorsements for insert with check (auth.uid() = user_id);
create policy "users remove own endorsement"
  on public.report_endorsements for delete using (auth.uid() = user_id);

-- ---- bookmarks (private to the user) --------------------------------
create policy "users read own bookmarks"
  on public.report_bookmarks for select using (auth.uid() = user_id);
create policy "users add own bookmark"
  on public.report_bookmarks for insert with check (auth.uid() = user_id);
create policy "users remove own bookmark"
  on public.report_bookmarks for delete using (auth.uid() = user_id);

-- ---- status logs (timeline) -----------------------------------------
create policy "status logs follow report visibility"
  on public.report_status_logs for select
  using (exists (
    select 1 from public.reports r
    where r.id = report_id
      and (r.is_approved = true or r.user_id = auth.uid() or public.is_admin())
  ));

-- ---- notifications (private) ----------------------------------------
create policy "users read own notifications"
  on public.notifications for select using (auth.uid() = user_id);
create policy "users update own notifications"
  on public.notifications for update using (auth.uid() = user_id);

-- ---- surveys --------------------------------------------------------
create policy "active surveys public"
  on public.surveys for select using (is_active = true or public.is_admin());
create policy "admins manage surveys"
  on public.surveys for all using (public.is_admin()) with check (public.is_admin());
create policy "users submit responses"
  on public.survey_responses for insert with check (auth.uid() = user_id or user_id is null);
create policy "admins read responses"
  on public.survey_responses for select using (public.is_admin());

-- ---- RTI letters (private to the author) ----------------------------
create policy "users manage own rti letters"
  on public.rti_letters for all
  using (auth.uid() = user_id) with check (auth.uid() = user_id);
