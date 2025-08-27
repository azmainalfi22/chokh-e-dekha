@extends('layouts.admin')

@section('title', 'Users Management')

@push('styles')
<style>
  /* ---------- ADMIN “NEW STYLE” FOR USERS ---------- */

  /* Panels & cards */
  .admin-card {
    background: var(--surface);
    border: 1px solid var(--ring);
    border-radius: var(--radius-2xl);
    box-shadow: var(--shadow-lg);
    backdrop-filter: blur(8px);
  }

  /* Stats */
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: var(--space-4);
    margin-bottom: var(--space-8);
  }
  .stat-card {
    position: relative;
    padding: var(--space-6);
    border: 1px solid var(--ring);
    border-radius: var(--radius-2xl);
    background: var(--surface);
    box-shadow: var(--shadow-lg);
    transition: transform var(--duration-normal) var(--ease-out), box-shadow var(--duration-normal) var(--ease-out);
    overflow: hidden;
  }
  .stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-xl); }
  .stat-card::before {
    content: '';
    position: absolute; inset: 0 0 auto 0; height: 3px;
    background: linear-gradient(135deg, var(--accent), #f97316);
  }
  .stat-value { font-size: 1.75rem; font-weight: 800; color: var(--text); line-height: 1; }
  .stat-label { font-size: var(--text-sm); color: var(--muted); font-weight: 600; margin-top: .25rem; }
  .stat-icon { position: absolute; top: .85rem; right: .85rem; width: 2rem; height: 2rem; opacity: .65; color: var(--accent); }

  /* Filters */
  .admin-filters-panel {
    background: var(--surface);
    border: 1px solid var(--ring);
    border-radius: var(--radius-2xl);
    box-shadow: var(--shadow-xl);
    padding: var(--space-6);
    backdrop-filter: blur(12px);
  }
  .admin-filters-panel input[type="search"],
  .admin-filters-panel input[type="date"],
  .admin-filters-panel select {
    background: var(--surface-muted) !important;
    color: var(--text) !important;
    border-color: var(--ring) !important;
    transition: all var(--duration-fast) ease !important;
  }
  .admin-filters-panel input:focus,
  .admin-filters-panel select:focus {
    border-color: var(--ring-focus) !important;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, .12) !important;
    outline: none !important;
  }

  /* Buttons */
  .admin-btn {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .5rem .75rem; border-radius: var(--radius-lg);
    font-size: var(--text-sm); font-weight: 600; border: 1px solid var(--ring);
    background: var(--surface); color: var(--text-secondary);
    transition: all var(--duration-fast) ease; white-space: nowrap;
  }
  .admin-btn:hover { background: var(--surface-muted); color: var(--text); border-color: var(--accent); }
  .admin-btn-primary {
    background: linear-gradient(135deg, var(--accent), #f97316);
    color: #fff; border-color: transparent; box-shadow: var(--shadow-md);
  }
  .admin-btn-primary:hover { transform: translateY(-1px); box-shadow: var(--shadow-lg); }

  /* Table */
  .table-wrap { overflow-x: auto; }
  .table-view { width: 100%; border-collapse: collapse; }
  .table-view th {
    text-align: left; font-weight: 800; font-size: var(--text-xs);
    text-transform: uppercase; letter-spacing: .06em;
    padding: var(--space-4); background: var(--surface-muted); color: var(--text);
    border-bottom: 1px solid var(--ring);
  }
  .table-view td {
    padding: var(--space-4); border-bottom: 1px solid var(--ring);
    color: var(--text); font-size: var(--text-sm);
  }
  .table-row:hover { background: var(--surface-muted); }

  /* Badges */
  .role-badge, .verify-badge {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .25rem .5rem; border-radius: 999px; font-size: 11px; font-weight: 800;
    border: 1px solid transparent;
  }
  .role-admin { background: rgba(99, 102, 241, .12); color: #4338ca; border-color: rgba(99, 102, 241, .28); }
  .role-user  { background: rgba(16, 185, 129, .12); color: #047857; border-color: rgba(16, 185, 129, .28); }
  .ver-yes    { background: rgba(59, 130, 246, .12); color: #1d4ed8; border-color: rgba(59, 130, 246, .28); }
  .ver-no     { background: rgba(239, 68, 68, .12); color: #b91c1c; border-color: rgba(239, 68, 68, .28); }

  /* Loading overlay */
  .loading-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.45);
    backdrop-filter: blur(4px); z-index: 60; display: none; align-items: center; justify-content: center;
  }
  .loading-spinner {
    width: 2.5rem; height: 2.5rem; border: 4px solid var(--surface);
    border-top: 4px solid var(--accent); border-radius: 50%; animation: spin 1s linear infinite;
  }
  @keyframes spin { to { transform: rotate(360deg); } }

  /* Responsive */
  @media (max-width: 768px) {
    .filters-grid { grid-template-columns: 1fr !important; }
  }
</style>
@endpush

@section('content')
@php
  // We’ll compute simple, result-set based stats
  $totalUsers    = method_exists($users, 'total') ? $users->total() : (is_countable($users) ? count($users) : 0);
  $pageCollection = collect($users instanceof \Illuminate\Pagination\AbstractPaginator ? $users->items() : $users);
  $adminsInResults   = $pageCollection->where('is_admin', true)->count();
  $verifiedInResults = $pageCollection->filter(fn($u) => !empty($u->email_verified_at))->count();
  $newTodayInResults = $pageCollection->filter(fn($u) => optional($u->created_at)?->isToday())->count();
@endphp

<div class="relative">
  {{-- Ambient blobs --}}
  <div class="pointer-events-none fixed -top-28 -right-28 h-80 w-80 rounded-full blur-3xl opacity-20 bg-gradient-to-br from-amber-200 via-orange-200 to-rose-200"></div>
  <div class="pointer-events-none fixed -bottom-28 -left-28 h-80 w-80 rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-orange-200 via-amber-200 to-pink-200"></div>

  <div class="max-w-7xl mx-auto p-4 md:p-8 relative z-[1]">
    {{-- Header --}}
    <header class="mb-8">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
        <div>
          <h1 class="text-3xl md:text-4xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-amber-700 via-orange-700 to-rose-700">
            Users Management
          </h1>
          <p class="text-base md:text-lg" style="color: var(--muted);">
            Search, filter and review all registered users.
          </p>
        </div>

        {{-- Quick search (top-right) --}}
        <form id="quickSearchForm" method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2">
          <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5" style="color:var(--muted)" viewBox="0 0 24 24" fill="currentColor">
              <path d="M10 4a6 6 0 104.47 10.03l3.75 3.75 1.41-1.41-3.75-3.75A6 6 0 0010 4zm0 2a4 4 0 110 8 4 4 0 010-8z"/>
            </svg>
            <input type="search" name="q" value="{{ request('q', $search ?? '') }}" placeholder="Search name, email, ID…"
                   class="w-64 rounded-xl border pl-10 pr-3 py-2 text-sm focus:ring-2 transition-all duration-200"
                   style="border-color: var(--ring)">
          </div>
          <button class="admin-btn admin-btn-primary" type="submit">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M10 4a6 6 0 104.47 10.03l3.75 3.75 1.41-1.41-3.75-3.75A6 6 0 0010 4z"/></svg>
            Search
          </button>
        </form>
      </div>
    </header>

    {{-- Stats (based on current filters, shows totals for this result set) --}}
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-value">{{ number_format($totalUsers) }}</div>
        <div class="stat-label">Total (results)</div>
        <svg class="stat-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2z"/></svg>
      </div>
      <div class="stat-card">
        <div class="stat-value">{{ $adminsInResults }}</div>
        <div class="stat-label">Admins (results)</div>
        <svg class="stat-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3 6 6 .9-4.5 4.4 1 6.2L12 17l-5.5 2.5 1-6.2L3 8.9 9 8l3-6z"/></svg>
      </div>
      <div class="stat-card">
        <div class="stat-value">{{ $verifiedInResults }}</div>
        <div class="stat-label">Verified (results)</div>
        <svg class="stat-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm-1 15l-5-5 1.4-1.4L11 14.2l6.6-6.6L19 9l-8 8z"/></svg>
      </div>
      <div class="stat-card">
        <div class="stat-value">{{ $newTodayInResults }}</div>
        <div class="stat-label">New Today (results)</div>
        <svg class="stat-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3h-1V1h-2v2H8V1H6v2H5a2 2 0 00-2 2v12a4 4 0 004 4h10a4 4 0 004-4V5a2 2 0 00-2-2z"/></svg>
      </div>
    </div>

    {{-- Filters --}}
    <div class="admin-filters-panel mb-8">
      <form id="userFiltersForm" method="GET" action="{{ route('admin.users.index') }}">
        <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 filters-grid">
          {{-- Search (wide) --}}
          <div class="sm:col-span-2 lg:col-span-2">
            <div class="relative">
              <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5" style="color:var(--muted)" viewBox="0 0 24 24" fill="currentColor">
                <path d="M10 4a6 6 0 104.47 10.03l3.75 3.75 1.41-1.41-3.75-3.75A6 6 0 0010 4z"/>
              </svg>
              <input type="search" name="q" value="{{ request('q', $search ?? '') }}" placeholder="Search name, email, ID…"
                     class="w-full rounded-xl border pl-10 pr-3 py-3 text-sm focus:ring-2 transition-all duration-200"
                     style="border-color: var(--ring)">
            </div>
          </div>

          {{-- Role --}}
          @php $role = request('role'); @endphp
          <select name="role" class="rounded-xl border px-4 py-3 text-sm focus:ring-2 transition-all duration-200" style="border-color:var(--ring)">
            <option value="">All roles</option>
            <option value="admin" @selected($role==='admin')>Admins</option>
            <option value="user"  @selected($role==='user')>Users</option>
          </select>

          {{-- Verified --}}
          @php $verified = request('verified'); @endphp
          <select name="verified" class="rounded-xl border px-4 py-3 text-sm focus:ring-2 transition-all duration-200" style="border-color:var(--ring)">
            <option value="">Any verification</option>
            <option value="yes" @selected($verified==='yes')>Verified only</option>
            <option value="no"  @selected($verified==='no')>Unverified only</option>
          </select>

          {{-- From / To (joined date) --}}
          <input type="date" name="from" value="{{ request('from') }}"
                 class="rounded-xl border px-4 py-3 text-sm focus:ring-2 transition-all duration-200"
                 style="border-color:var(--ring)" placeholder="From">
          <input type="date" name="to" value="{{ request('to') }}"
                 class="rounded-xl border px-4 py-3 text-sm focus:ring-2 transition-all duration-200"
                 style="border-color:var(--ring)" placeholder="To">
        </div>

        {{-- Second row --}}
        <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 mt-4">
          {{-- Sort --}}
          @php $sort = request('sort','newest'); @endphp
          <select name="sort" class="rounded-xl border px-4 py-3 text-sm focus:ring-2 transition-all duration-200" style="border-color:var(--ring)">
            <option value="newest" @selected($sort==='newest')>Newest first</option>
            <option value="oldest" @selected($sort==='oldest')>Oldest first</option>
            <option value="name"   @selected($sort==='name')>Name (A→Z)</option>
            <option value="email"  @selected($sort==='email')>Email (A→Z)</option>
            <option value="role"   @selected($sort==='role')>Role (Admin→User)</option>
            <option value="verified" @selected($sort==='verified')>Verified first</option>
          </select>

          {{-- Per page --}}
          <select name="per_page" class="rounded-xl border px-4 py-3 text-sm focus:ring-2 transition-all duration-200" style="border-color:var(--ring)">
            @foreach([12,18,24,30,36,48,60] as $pp)
              <option value="{{ $pp }}" @selected((int)request('per_page',18)===$pp)>Show {{ $pp }}</option>
            @endforeach
          </select>

          {{-- Actions --}}
          <div class="flex items-center gap-2">
            <button type="submit" class="admin-btn admin-btn-primary">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M10 4a6 6 0 104.47 10.03l3.75 3.75 1.41-1.41-3.75-3.75A6 6 0 0010 4z"/></svg>
              Apply
            </button>
            <a href="{{ route('admin.users.index') }}" class="admin-btn">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19h12v2H6zM7 4h10v2H7zM5 8h14v2H5zM5 12h14v2H5z"/></svg>
              Clear
            </a>
          </div>

          {{-- Copy link --}}
          <div class="flex items-center">
            <button type="button" id="copyLinkBtn" class="admin-btn w-full">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4a2 2 0 00-2 2v14h2V3h12V1zm3 4H8a2 2 0 00-2 2v14a2 2 0 002 2h11a2 2 0 002-2V7a2 2 0 00-2-2z"/></svg>
              Copy link
            </button>
          </div>
        </div>

        {{-- Active filters --}}
        @if(request()->hasAny(['q','role','verified','from','to']))
          <div class="mt-4 pt-4 border-t" style="border-color:var(--ring)">
            <div class="flex flex-wrap items-center gap-2 text-sm" style="color: var(--muted);">
              <span class="font-semibold">Active filters:</span>
              @if(request('q'))
                <span class="px-3 py-1.5 rounded-lg font-medium" style="background:rgba(245,158,11,.15); border:1px solid rgba(245,158,11,.3); color:var(--text)">Search: "{{ request('q') }}"</span>
              @endif
              @if(request('role'))
                <span class="px-3 py-1.5 rounded-lg font-medium" style="background:rgba(245,158,11,.15); border:1px solid rgba(245,158,11,.3); color:var(--text)">Role: {{ ucfirst(request('role')) }}</span>
              @endif
              @if(request('verified'))
                <span class="px-3 py-1.5 rounded-lg font-medium" style="background:rgba(245,158,11,.15); border:1px solid rgba(245,158,11,.3); color:var(--text)">Verified: {{ request('verified')==='yes'?'Yes':'No' }}</span>
              @endif
              @if(request('from') || request('to'))
                <span class="px-3 py-1.5 rounded-lg font-medium" style="background:rgba(245,158,11,.15); border:1px solid rgba(245,158,11,.3); color:var(--text)">
                  Date: {{ request('from') ?? 'Start' }} → {{ request('to') ?? 'End' }}
                </span>
              @endif
            </div>
          </div>
        @endif
      </form>
    </div>

    {{-- Users table --}}
    <div class="admin-card">
      <div class="table-wrap">
        <table class="table-view">
          <thead>
            <tr>
              <th>ID</th>
              <th>User</th>
              <th>Email</th>
              <th>Role</th>
              <th>Verified</th>
              <th>Joined</th>
            </tr>
          </thead>
          <tbody>
            @forelse($users as $u)
              <tr class="table-row">
                <td class="font-semibold text-slate-700">#{{ $u->id }}</td>

                <td>
                  <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-full flex items-center justify-center border" style="border-color:var(--ring); background:var(--surface-muted)">
                      <span class="text-xs font-bold" style="color:var(--text)">
                        {{ strtoupper(mb_substr($u->name ?? 'U',0,1)) }}
                      </span>
                    </div>
                    <div class="min-w-0">
                      <div class="font-semibold truncate" style="color:var(--text)">{{ $u->name ?? '—' }}</div>
                      <div class="text-xs" style="color:var(--muted)">ID: {{ $u->id }}</div>
                    </div>
                  </div>
                </td>

                <td>
                  <div class="truncate">
                    <a href="mailto:{{ $u->email }}" class="hover:underline" style="color:var(--text)">{{ $u->email }}</a>
                  </div>
                </td>

                <td>
                  @if($u->is_admin)
                    <span class="role-badge role-admin">● Admin</span>
                  @else
                    <span class="role-badge role-user">● User</span>
                  @endif
                </td>

                <td>
                  @if($u->email_verified_at)
                    <span class="verify-badge ver-yes">✔ Verified</span>
                  @else
                    <span class="verify-badge ver-no">✖ Unverified</span>
                  @endif
                </td>

                <td class="whitespace-nowrap">
                  <div>{{ optional($u->created_at)->format('M d, Y') }}</div>
                  <div class="text-xs" style="color:var(--muted)">{{ optional($u->created_at)->format('h:i A') }}</div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center py-10" style="color:var(--muted)">No users found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      @if(method_exists($users,'links'))
        <div class="p-4 sm:p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
          <div class="text-sm" style="color:var(--muted)">
            Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() ?? 0 }} results
          </div>
          <div class="flex justify-center">
            {{ $users->appends(request()->query())->links() }}
          </div>
        </div>
      @endif
    </div>
  </div>
</div>

{{-- Loading overlay --}}
<div id="loadingOverlay" class="loading-overlay">
  <div class="bg-white rounded-xl p-6 shadow-xl flex items-center gap-4">
    <div class="loading-spinner"></div>
    <div>
      <div class="font-semibold text-gray-900">Processing…</div>
      <div class="text-sm text-gray-600">Please wait while we fetch the users.</div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  'use strict';

  const form = document.getElementById('userFiltersForm');
  const quickForm = document.getElementById('quickSearchForm');
  const overlay = document.getElementById('loadingOverlay');

  const showLoading = () => overlay.style.display = 'flex';
  const hideLoading = () => overlay.style.display = 'none';

  // Debounced search (both forms)
  function wireDebouncedSearch(input) {
    if (!input) return;
    let t, lastVal = input.value;
    input.addEventListener('input', (e) => {
      clearTimeout(t);
      const v = e.target.value.trim();
      if (v !== lastVal && (v.length >= 2 || v.length === 0)) {
        t = setTimeout(() => {
          lastVal = v;
          showLoading();
          (form || quickForm).submit();
        }, 500);
      }
    });
  }

  // Auto-submit on select/date change
  function autosubmitOnChange(selector) {
    (form?.querySelectorAll(selector) || []).forEach(el => {
      el.addEventListener('change', () => { showLoading(); form.submit(); });
    });
  }

  // Keyboard shortcut: Ctrl/Cmd + K focuses main search
  document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault();
      const search = form?.querySelector('input[name="q"]') || quickForm?.querySelector('input[name="q"]');
      if (search) { search.focus(); search.select(); }
    }
  });

  // Copy link button
  const copyBtn = document.getElementById('copyLinkBtn');
  if (copyBtn) {
    copyBtn.addEventListener('click', async () => {
      try {
        await navigator.clipboard.writeText(window.location.href);
        const orig = copyBtn.innerHTML;
        copyBtn.innerHTML = `
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
          Copied!
        `;
        setTimeout(() => copyBtn.innerHTML = orig, 1800);
      } catch (e) {}
    });
  }

  // Wire inputs
  wireDebouncedSearch(form?.querySelector('input[name="q"]'));
  wireDebouncedSearch(quickForm?.querySelector('input[name="q"]'));
  autosubmitOnChange('select[name="role"], select[name="verified"], select[name="sort"], select[name="per_page"]');
  autosubmitOnChange('input[name="from"], input[name="to"]');

  // Hide overlay on load
  window.addEventListener('load', hideLoading);
})();
</script>
@endpush
