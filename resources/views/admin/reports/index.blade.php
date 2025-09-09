@extends('layouts.admin')

@section('title', 'Reports Management')

@push('styles')
<style>
  /* ---------- ADMIN-SPECIFIC ENHANCEMENTS ---------- */

  /* Report Cards - Admin Style */
  .admin-report-card {
    background: var(--surface);
    border: 1px solid var(--ring);
    box-shadow: var(--shadow-lg);
    transition: all var(--duration-normal) var(--ease-out);
    backdrop-filter: blur(8px);
    position: relative;
    overflow: visible;
  }
  .admin-report-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-xl);
    border-color: var(--accent);
  }

  .admin-report-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: var(--space-3);
  }
  .admin-report-head h3 {
    color: var(--text);
    line-height: 1.2;
  }

  .admin-meta {
    color: var(--muted);
    font-size: var(--text-sm);
  }
  .admin-meta li {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    line-height: 1.4;
  }

  /* Priority Badges */
  .priority-badge {
    display: inline-flex;
    align-items: center;
    gap: var(--space-1);
    padding: var(--space-1) var(--space-3);
    border-radius: var(--radius-full);
    font-size: var(--text-xs);
    font-weight: 700;
    line-height: 1;
    border: 1px solid transparent;
  }
  .priority-high { background: rgba(239,68,68,.15); color:#dc2626; border-color: rgba(239,68,68,.3);}
  .priority-medium{ background: rgba(245,158,11,.15); color:#d97706; border-color: rgba(245,158,11,.3);}
  .priority-low { background: rgba(34,197,94,.15); color:#16a34a; border-color: rgba(34,197,94,.3); }

  /* Status Pills */
  .admin-status-pill{
    display:inline-flex; align-items:center; gap:var(--space-1);
    padding:var(--space-1) var(--space-3); border-radius:var(--radius-full);
    font-size:var(--text-xs); font-weight:700; line-height:1;
    border:1px solid transparent; white-space:nowrap; box-shadow:var(--shadow-sm);
    cursor:pointer; transition:all var(--duration-fast) ease;
  }
  .admin-status-pill:hover { transform: scale(1.05); }
  .status-pending{ background: var(--status-pending-bg); color: var(--status-pending-text); border-color: var(--status-pending-border);}
  .status-in_progress{ background: var(--status-in-progress-bg); color: var(--status-in-progress-text); border-color: var(--status-in-progress-border);}
  .status-resolved{ background: var(--status-resolved-bg); color: var(--status-resolved-text); border-color: var(--status-resolved-border);}
  .status-rejected{ background: var(--status-rejected-bg); color: var(--status-rejected-text); border-color: var(--status-rejected-border);}

  /* Admin Action Bar */
  .admin-actions {
    display:flex; align-items:center; gap:var(--space-2); flex-wrap:wrap;
    border-top:1px solid var(--ring); padding-top:var(--space-4); margin-top:var(--space-4);
  }

  .admin-btn {
    display:inline-flex; align-items:center; gap:var(--space-1);
    padding:var(--space-2) var(--space-3); border-radius:var(--radius-lg);
    font-size:var(--text-sm); font-weight:600; text-decoration:none;
    transition:all var(--duration-fast) ease; border:1px solid var(--ring); white-space:nowrap;
    cursor: pointer;
  }
  .admin-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }
  .admin-btn-primary { background:linear-gradient(135deg,var(--accent),#f97316); color:#fff; border-color:transparent; box-shadow:var(--shadow-md); }
  .admin-btn-primary:hover:not(:disabled) { background:linear-gradient(135deg,var(--accent-600),#ea580c); transform:translateY(-1px); box-shadow:var(--shadow-lg);}
  .admin-btn-secondary { background:var(--surface); color:var(--text-secondary); border-color:var(--ring); }
  .admin-btn-secondary:hover:not(:disabled) { background:var(--surface-muted); color:var(--text); border-color:var(--accent);}
  .admin-btn-danger{ background:rgba(239,68,68,.1); color:#dc2626; border-color:rgba(239,68,68,.3);}
  .admin-btn-danger:hover:not(:disabled){ background:rgba(239,68,68,.2); border-color:#dc2626;}

  /* Filters Panel */
  .admin-filters-panel{ background:var(--surface); border:1px solid var(--ring); box-shadow:var(--shadow-xl); backdrop-filter:blur(12px); }
  .admin-filters-panel input[type="search"],
  .admin-filters-panel input[type="date"],
  .admin-filters-panel select{ background:var(--surface-muted)!important; color:var(--text)!important; border-color:var(--ring)!important; transition:all var(--duration-fast) ease!important;}
  .admin-filters-panel input:focus, .admin-filters-panel select:focus{ border-color:var(--ring-focus)!important; box-shadow:0 0 0 3px rgba(245,158,11,.1)!important; outline:none!important; }

  /* Stats Cards */
  .stats-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:var(--space-4); margin-bottom:var(--space-8);}
  .stat-card{ background:var(--surface); border:1px solid var(--ring); border-radius:var(--radius-2xl); padding:var(--space-6); box-shadow:var(--shadow-lg); backdrop-filter:blur(8px); transition:all var(--duration-normal) ease; position:relative; overflow:hidden;}
  .stat-card:hover{ transform:translateY(-2px); box-shadow:var(--shadow-xl);}
  .stat-card::before{ content:''; position:absolute; top:0; left:0; right:0; height:3px; background:linear-gradient(135deg,var(--accent),#f97316);}
  .stat-value{ font-size:2rem; font-weight:800; line-height:1; color:var(--text); margin-bottom:var(--space-1);}
  .stat-label{ font-size:var(--text-sm); color:var(--muted); font-weight:600;}
  .stat-icon{ position:absolute; top:var(--space-4); right:var(--space-4); width:2.5rem; height:2.5rem; opacity:.6; color:var(--accent);}

  /* Quick Actions */
  .quick-actions{ background:var(--surface); border:1px solid var(--ring); border-radius:var(--radius-2xl); padding:var(--space-6); box-shadow:var(--shadow-lg); backdrop-filter:blur(8px); margin-bottom:var(--space-8);}
  .quick-actions h3{ color:var(--text); font-size:var(--text-lg); font-weight:700; margin-bottom:var(--space-4);}
  .quick-actions-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:var(--space-3);}
  .quick-action-btn{ display:flex; flex-direction:column; align-items:center; gap:var(--space-2); padding:var(--space-4); border:1px solid var(--ring); border-radius:var(--radius-xl); background:var(--surface-muted); text-decoration:none; transition:all var(--duration-fast) ease; color:var(--text); cursor: pointer;}
  .quick-action-btn:hover{ background:var(--accent); color:#fff; border-color:var(--accent); transform:translateY(-2px); box-shadow:var(--shadow-md);}
  .quick-action-icon{ width:2rem; height:2rem; }
  .quick-action-label{ font-size:var(--text-sm); font-weight:600; text-align:center;}

  /* View Toggle */
  .view-toggle{ display:flex; align-items:center; gap:var(--space-2); background:var(--surface-muted); border:1px solid var(--ring); border-radius:var(--radius-xl); padding:var(--space-1);}
  .view-toggle-btn{ display:inline-flex; align-items:center; gap:var(--space-1); padding:var(--space-2) var(--space-3); border-radius:var(--radius-lg); font-size:var(--text-sm); font-weight:600; background:transparent; border:none; color:var(--text-secondary); cursor:pointer; transition:all var(--duration-fast) ease;}
  .view-toggle-btn.active{ background:var(--accent); color:#fff; box-shadow:var(--shadow-sm);}

  /* Bulk Actions */
  .bulk-actions{ background:var(--surface); border:1px solid var(--ring); border-radius:var(--radius-xl); padding:var(--space-4); margin-bottom:var(--space-6); display:none; align-items:center; gap:var(--space-4); box-shadow:var(--shadow-lg); }
  .bulk-actions.show{ display:flex; }
  .bulk-checkbox{ width:1.25rem; height:1.25rem; border-radius:var(--radius); border:2px solid var(--ring); background:var(--surface); cursor:pointer; transition:all var(--duration-fast) ease;}
  .bulk-checkbox:checked{ background:var(--accent); border-color:var(--accent);}

  /* Table View */
  .table-view{ background:var(--surface); border:1px solid var(--ring); border-radius:var(--radius-2xl); overflow:hidden; box-shadow:var(--shadow-lg);}
  .table-view table{ width:100%; border-collapse:collapse;}
  .table-view th{ background:var(--surface-muted); color:var(--text); font-weight:700; font-size:var(--text-sm); padding:var(--space-4); text-align:left; border-bottom:1px solid var(--ring);}
  .table-view td{ padding:var(--space-4); border-bottom:1px solid var(--ring); color:var(--text); font-size:var(--text-sm);}
  .table-view tr:hover{ background:var(--surface-muted);}

  /* Animations */
  @keyframes slideInUp{ from{ opacity:0; transform:translateY(30px);} to{ opacity:1; transform:translateY(0);} }
  .slide-in{ animation:slideInUp var(--duration-slower) var(--ease-out) both; }
  @keyframes fadeInScale{ from{ opacity:0; transform:scale(.95);} to{ opacity:1; transform:scale(1);} }
  .fade-scale{ animation:fadeInScale var(--duration-normal) var(--ease-out) both; }

  /* Responsive */
  @media (max-width:768px){
    .admin-report-card{ margin-bottom:var(--space-6);}
    .admin-report-head{ flex-direction:column; gap:var(--space-2);}
    .admin-actions{ flex-direction:column; align-items:stretch;}
    .admin-btn{ justify-content:center;}
    .stats-grid{ grid-template-columns:repeat(2,1fr);}
    .quick-actions-grid{ grid-template-columns:repeat(2,1fr);}
  }
  @media (max-width:480px){
    .stats-grid{ grid-template-columns:1fr;}
    .quick-actions-grid{ grid-template-columns:1fr;}
  }

  /* Loading */
  .loading-overlay{ position:fixed; inset:0; background:rgba(0,0,0,.5); backdrop-filter:blur(4px); z-index:9999; display:flex; align-items:center; justify-content:center;}
  .loading-spinner{ width:3rem; height:3rem; border:4px solid var(--surface); border-top:4px solid var(--accent); border-radius:50%; animation:spin 1s linear infinite;}
  @keyframes spin{ 0%{transform:rotate(0)} 100%{transform:rotate(360deg)} }

  @media (prefers-color-scheme: dark){ .stat-card::before{ opacity:.8; } }

  @media print{
    .admin-filters-panel, .quick-actions, .admin-actions, .view-toggle, .bulk-actions{ display:none!important; }
  }
</style>
@endpush

@section('content')
@php
  // Accurate stats: prefer server-provided $statusCounts (Collection: status => count)
  $hasCounts        = isset($statusCounts) && $statusCounts instanceof \Illuminate\Support\Collection;
  $totalReports     = $hasCounts ? $statusCounts->sum() : ($reports->total() ?? $reports->count() ?? 0);
  $pendingReports   = $hasCounts ? ($statusCounts['pending'] ?? 0)     : collect($reports)->where('status','pending')->count();
  $inProgressReports= $hasCounts ? ($statusCounts['in_progress'] ?? 0): collect($reports)->where('status','in_progress')->count();
  $resolvedReports  = $hasCounts ? ($statusCounts['resolved'] ?? 0)   : collect($reports)->where('status','resolved')->count();
@endphp

<div class="relative grainy min-h-screen">
  {{-- background blobs --}}
  <div class="pointer-events-none fixed -top-32 -right-32 h-96 w-96 rounded-full blur-3xl opacity-20 bg-gradient-to-br from-amber-200 via-orange-200 to-rose-200"></div>
  <div class="pointer-events-none fixed -bottom-32 -left-32 h-96 w-96 rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-orange-200 via-amber-200 to-pink-200" style="animation-delay:1s;"></div>

  <div class="max-w-7xl mx-auto p-4 md:p-8 relative z-[1]">

    {{-- Header --}}
    <header class="mb-8">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
        <div class="min-w-0">
          <h1 class="text-3xl md:text-4xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-amber-700 via-orange-700 to-rose-700">
            Reports Management
          </h1>
          <p class="text-base md:text-lg" style="color:var(--muted)">Monitor, review, and manage all incoming reports across city corporations.</p>
        </div>

        <div class="flex items-center gap-4">
          {{-- View Toggle --}}
          <div class="view-toggle">
            <button type="button" class="view-toggle-btn active" data-view="cards">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z"/></svg>
              Cards
            </button>
            <button type="button" class="view-toggle-btn" data-view="table">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M3 3h18v18H3V3zm2 2v14h14V5H5zm2 2h10v2H7V7zm0 4h10v2H7v-2zm0 4h10v2H7v-2z"/></svg>
              Table
            </button>
          </div>

          {{-- Export Button --}}
          <button type="button" class="admin-btn admin-btn-secondary" id="exportBtn">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z"/><path d="M14 2v6h6M16 13a1 1 0 01-1 1h-2v2a1 1 0 01-2 0v-2H9a1 1 0 010-2h2v-2a1 1 0 012 0v2h2a1 1 0 011 1z"/></svg>
            Export
          </button>
        </div>
      </div>
    </header>

    {{-- Statistics Cards --}}
    <div class="stats-grid">
      <div class="stat-card fade-scale">
        <div class="stat-value">{{ number_format($totalReports) }}</div>
        <div class="stat-label">Total Reports</div>
        <svg class="stat-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>
      </div>

      <div class="stat-card fade-scale" style="animation-delay:.1s;">
        <div class="stat-value">{{ number_format($pendingReports) }}</div>
        <div class="stat-label">Pending Review</div>
        <svg class="stat-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
      </div>

      <div class="stat-card fade-scale" style="animation-delay:.2s;">
        <div class="stat-value">{{ number_format($inProgressReports) }}</div>
        <div class="stat-label">In Progress</div>
        <svg class="stat-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
      </div>

      <div class="stat-card fade-scale" style="animation-delay:.3s;">
        <div class="stat-value">{{ number_format($resolvedReports) }}</div>
        <div class="stat-label">Resolved</div>
        <svg class="stat-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
      </div>
    </div>

    {{-- Quick Actions --}}
    <div class="quick-actions slide-in">
      <h3>Quick Actions</h3>
      <div class="quick-actions-grid">
        <button type="button" class="quick-action-btn" onclick="AdminReports.filterByStatus('pending')">
          <svg class="quick-action-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11h-6V7h2v4h4v2z"/></svg>
          <span class="quick-action-label">View Pending</span>
        </button>

        <button type="button" class="quick-action-btn" onclick="AdminReports.filterByStatus('in_progress')">
          <svg class="quick-action-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <span class="quick-action-label">In Progress</span>
        </button>

        <button type="button" class="quick-action-btn" onclick="AdminReports.filterByPriority()">
          <svg class="quick-action-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <span class="quick-action-label">High Priority</span>
        </button>

        <button type="button" class="quick-action-btn" onclick="AdminReports.filterByDate('today')">
          <svg class="quick-action-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>
          <span class="quick-action-label">Today's Reports</span>
        </button>

        <button type="button" class="quick-action-btn" id="bulkToggleBtn">
          <svg class="quick-action-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
          <span class="quick-action-label">Bulk Actions</span>
        </button>

        <button type="button" class="quick-action-btn" onclick="AdminReports.generateReport()">
          <svg class="quick-action-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z"/></svg>
          <span class="quick-action-label">Generate Report</span>
        </button>
      </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
      <div class="mb-8 rounded-xl border border-emerald-300 bg-emerald-50 px-6 py-4 text-emerald-800 shadow-md backdrop-blur-sm">
        <div class="flex items-center gap-3">
          <svg class="h-5 w-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
          {{ session('success') }}
        </div>
      </div>
    @endif

    {{-- Bulk Actions Bar --}}
    <div class="bulk-actions" id="bulkActions">
      <div class="flex items-center gap-4">
        <span class="font-semibold" style="color: var(--text);">
          <span id="selectedCount">0</span> reports selected
        </span>

        <div class="flex gap-2">
          <button type="button" class="admin-btn admin-btn-secondary" onclick="AdminReports.bulkAction('approve')">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
            Approve Selected
          </button>
          <button type="button" class="admin-btn admin-btn-secondary" onclick="AdminReports.bulkAction('reject')">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            Reject Selected
          </button>
          <button type="button" class="admin-btn admin-btn-danger" onclick="AdminReports.bulkAction('delete')">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
            Delete Selected
          </button>
        </div>
      </div>
    </div>

    {{-- Filters Panel --}}
    <div class="mb-8 rounded-2xl admin-filters-panel p-6 shadow-xl">
      <form method="GET" action="{{ route('admin.reports.index') }}" id="adminFiltersForm">

        <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
          {{-- Search --}}
          <div class="col-span-1 sm:col-span-2 lg:col-span-2 xl:col-span-2">
            <div class="relative">
              <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 flex-none" style="color:var(--muted)" viewBox="0 0 24 24" fill="currentColor"><path d="M10 4a6 6 0 104.47 10.03l3.75 3.75 1.41-1.41-3.75-3.75A6 6 0 0010 4zm0 2a4 4 0 110 8 4 4 0 010-8z"/></svg>
              <input type="search" name="q" value="{{ request('q','') }}" placeholder="Search title, description, user, or ID..."
                     class="w-full rounded-xl border pl-10 pr-4 py-3 text-sm focus:ring-2 transition-all duration-200" style="border-color:var(--ring)">
            </div>
          </div>

          {{-- City --}}
          <select name="city" class="rounded-xl border px-4 py-3 text-sm focus:ring-2 transition-all duration-200" style="border-color:var(--ring)">
            <option value="">All cities</option>
            @foreach(($cities ?? []) as $c)
              <option value="{{ $c }}" @selected(request('city')===$c)>{{ $c }}</option>
            @endforeach
          </select>

          {{-- Category --}}
          <select name="category" class="rounded-xl border px-4 py-3 text-sm focus:ring-2 transition-all duration-200" style="border-color:var(--ring)">
            <option value="">All categories</option>
            @foreach(($categories ?? []) as $cat)
              <option value="{{ $cat }}" @selected(request('category')===$cat)>{{ $cat }}</option>
            @endforeach
          </select>

          {{-- Status --}}
          <select name="status" class="rounded-xl border px-4 py-3 text-sm focus:ring-2 transition-all duration-200" style="border-color:var(--ring)">
            <option value="">All statuses</option>
            @php $statuses = ['pending','in_progress','resolved','rejected']; @endphp
            @foreach($statuses as $s)
              <option value="{{ $s }}" @selected(request('status')===$s)>{{ \Illuminate\Support\Str::headline($s) }}</option>
            @endforeach
          </select>
        </div>

        {{-- Second row --}}
        <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 mt-4">
          {{-- Date range --}}
          <div class="sm:col-span-2">
            <label class="block text-sm font-medium mb-1" style="color:var(--text)">Date range</label>

            <div class="mt-1 flex flex-wrap items-center gap-2">
              <input
                type="date"
                name="from"
                id="from"
                value="{{ request('from') }}"
                autocomplete="off"
                class="w-full sm:w-44 md:w-52 rounded-xl border px-3 py-2 text-sm focus:ring-2 focus:ring-amber-300 flex-none relative z-10"
                style="border-color:var(--ring);background:var(--surface);color:var(--text)"
              >
              <span class="px-2 text-sm opacity-70 whitespace-nowrap">to</span>
              <input
                type="date"
                name="to"
                id="to"
                value="{{ request('to') }}"
                autocomplete="off"
                class="w-full sm:w-44 md:w-52 rounded-xl border px-3 py-2 text-sm focus:ring-2 focus:ring-amber-300 flex-none relative z-10"
                style="border-color:var(--ring);background:var(--surface);color:var(--text)"
              >
            </div>
          </div>

          {{-- Priority --}}
          <select name="priority" class="rounded-xl border px-4 py-3 text-sm focus:ring-2 transition-all duration-200" style="border-color:var(--ring)">
            <option value="">All priorities</option>
            <option value="high" @selected(request('priority')==='high')>High Priority</option>
            <option value="medium" @selected(request('priority')==='medium')>Medium Priority</option>
            <option value="low" @selected(request('priority')==='low')>Low Priority</option>
          </select>

          {{-- Assignment --}}
          <select name="assigned_to" class="rounded-xl border px-4 py-3 text-sm focus:ring-2 transition-all duration-200" style="border-color:var(--ring)">
            <option value="">All assignments</option>
            <option value="me" @selected(request('assigned_to')==='me')>Assigned to me</option>
            <option value="unassigned" @selected(request('assigned_to')==='unassigned')>Unassigned</option>
            <option value="others" @selected(request('assigned_to')==='others')>Assigned to others</option>
          </select>

          {{-- Sort --}}
          @php $sort = request('sort','newest'); @endphp
          <select id="sort" name="sort" class="rounded-xl border px-4 py-3 text-sm focus:ring-2 transition-all duration-200" style="border-color:var(--ring)">
            <option value="newest" @selected($sort==='newest')>Newest first</option>
            <option value="oldest" @selected($sort==='oldest')>Oldest first</option>
            <option value="priority" @selected($sort==='priority')>Priority (High→Low)</option>
            <option value="status" @selected($sort==='status')>Status (A→Z)</option>
            <option value="city" @selected($sort==='city')>City (A→Z)</option>
            <option value="category" @selected($sort==='category')>Category (A→Z)</option>
            <option value="updated" @selected($sort==='updated')>Recently updated</option>
          </select>

          {{-- Per-page --}}
          <select id="per_page" name="per_page" class="rounded-xl border px-4 py-3 text-sm focus:ring-2 transition-all duration-200" style="border-color:var(--ring)">
            @foreach([12,18,24,30,36,48,60] as $pp)
              <option value="{{ $pp }}" @selected((int)request('per_page',18)===$pp)>Show {{ $pp }}</option>
            @endforeach
          </select>
        </div>

        {{-- Actions --}}
        <div class="flex flex-wrap items-center justify-between gap-4 mt-6 pt-4 border-t" style="border-color:var(--ring)">
          <div class="flex flex-wrap gap-2">
            <button type="submit" class="admin-btn admin-btn-primary">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M10 4a6 6 0 104.47 10.03l3.75 3.75 1.41-1.41-3.75-3.75A6 6 0 0010 4zm0 2a4 4 0 110 8 4 4 0 010-8z"/></svg>
              Apply Filters
            </button>

            <a href="{{ route('admin.reports.index') }}" class="admin-btn admin-btn-secondary">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
              Clear All
            </a>

            <button type="button" id="saveFiltersBtn" class="admin-btn admin-btn-secondary">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4z"/></svg>
              Save Filter
            </button>
          </div>

          <div class="flex flex-wrap gap-2">
            <button type="button" id="copyLinkBtn" class="admin-btn admin-btn-secondary">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2z"/></svg>
              Copy Link
            </button>

            <button type="button" id="refreshBtn" class="admin-btn admin-btn-secondary">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
              Refresh
            </button>
          </div>
        </div>

        {{-- Active filters --}}
        @if(request()->hasAny(['q','city','category','status','priority','assigned_to','from','to']))
          <div class="mt-4 pt-4 border-t" style="border-color:var(--ring)">
            <div class="flex flex-wrap items-center gap-2 text-sm" style="color:var(--muted)">
              <span class="font-semibold">Active filters:</span>
              @if(request('q'))
                <span class="px-3 py-1.5 rounded-lg font-medium" style="background:rgba(245,158,11,.15); color:var(--text); border:1px solid rgba(245,158,11,.3);">Search: "{{ request('q') }}"</span>
              @endif
              @if(request('city'))
                <span class="px-3 py-1.5 rounded-lg font-medium" style="background:rgba(245,158,11,.15); color:var(--text); border:1px solid rgba(245,158,11,.3);">City: {{ request('city') }}</span>
              @endif
              @if(request('category'))
                <span class="px-3 py-1.5 rounded-lg font-medium" style="background:rgba(245,158,11,.15); color:var(--text); border:1px solid rgba(245,158,11,.3);">Category: {{ request('category') }}</span>
              @endif
              @if(request('status'))
                <span class="px-3 py-1.5 rounded-lg font-medium" style="background:rgba(245,158,11,.15); color:var(--text); border:1px solid rgba(245,158,11,.3);">Status: {{ \Illuminate\Support\Str::headline(request('status')) }}</span>
              @endif
              @if(request('priority'))
                <span class="px-3 py-1.5 rounded-lg font-medium" style="background:rgba(245,158,11,.15); color:var(--text); border:1px solid rgba(245,158,11,.3);">Priority: {{ \Illuminate\Support\Str::headline(request('priority')) }}</span>
              @endif
              @if(request('from') || request('to'))
                <span class="px-3 py-1.5 rounded-lg font-medium" style="background:rgba(245,158,11,.15); color:var(--text); border:1px solid rgba(245,158,11,.3);">Date: {{ request('from') ?? 'Start' }} → {{ request('to') ?? 'End' }}</span>
              @endif
            </div>
          </div>
        @endif
      </form>
    </div>

    @php
      // Helpers
      $adminPill = function($status, $reportId = null) {
        $s = $status ?? 'pending';
        $label = \Illuminate\Support\Str::headline($s);
        $clickable = $reportId ? 'onclick="AdminReports.quickStatusChange(this, '.$reportId.', \''.$s.'\')"' : '';
        return '<span class="admin-status-pill status-'.e($s).'" '.$clickable.'>● '.$label.'</span>';
      };
      $priorityBadge = function($priority) {
        $p = $priority ?? 'medium';
        $icons = ['high'=>'🔴','medium'=>'🟡','low'=>'🟢'];
        return '<span class="priority-badge priority-'.$p.'">'.($icons[$p] ?? '⚪').' '.ucfirst($p).' Priority</span>';
      };
    @endphp

    {{-- Cards View --}}
    <div id="cardsView" class="view-content active">
      @if($reports->isEmpty())
        <div class="rounded-2xl border border-amber-300 bg-white/80 backdrop-blur-sm px-8 py-16 text-center shadow-lg">
          <div class="mx-auto mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full ring-2 ring-amber-200 bg-amber-50">
            <svg class="h-8 w-8 text-amber-700" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>
          </div>
          <h3 class="text-xl font-bold mb-2" style="color:var(--text)">No reports found</h3>
          <p class="text-base mb-6" style="color:var(--muted)">Try adjusting your search criteria or clearing active filters.</p>
          <a href="{{ route('admin.reports.index') }}" class="admin-btn admin-btn-primary">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            Reset Filters
          </a>
        </div>
      @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
          @foreach($reports as $report)
            @php
              $daysSinceCreated = optional($report->created_at)->diffInDays(now()) ?? 0;
              $priority = $daysSinceCreated > 7 ? 'high' : ($daysSinceCreated > 3 ? 'medium' : 'low');
              if ($report->status === 'pending' && $daysSinceCreated > 3) $priority = 'high';
              $commentsCount = method_exists($report,'comments') ? $report->comments()->count() : 0;
              $endorsementsCount = method_exists($report,'endorsements') ? $report->endorsements()->count() : 0;
            @endphp

            <article class="admin-report-card slide-in rounded-2xl overflow-visible" style="animation-delay: {{ $loop->index * 0.05 }}s;" data-report-id="{{ $report->id }}">
              {{-- Bulk checkbox --}}
              <div class="absolute top-4 left-4 z-10">
                <input type="checkbox" class="bulk-checkbox" value="{{ $report->id }}" onchange="AdminReports.updateBulkSelection()" style="display:none;">
              </div>

              {{-- Map header --}}
              @if($report->static_map_url)
                <div class="relative">
                  <a href="@if($report->has_coords) https://www.google.com/maps/search/?api=1&query={{ $report->latitude }},{{ $report->longitude }} @else {{ route('admin.reports.show', $report) }} @endif"
                     target="_blank" rel="noopener" class="block relative group overflow-hidden">
                    <img src="{{ $report->static_map_url }}" alt="Map preview for {{ $report->title }}" class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy">
                    <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                      <span class="text-white font-semibold bg-black/50 px-3 py-1 rounded-full text-sm">View location</span>
                    </div>
                  </a>
                  <div class="absolute top-3 right-3">{!! $priorityBadge($priority) !!}</div>
                </div>
              @endif

              <div class="p-6 flex flex-col gap-4 relative">
                <div class="admin-report-head">
                  <div class="min-w-0 flex-1">
                    <h3 class="text-xl font-bold leading-tight line-clamp-2 mb-2">
                      <a href="{{ route('admin.reports.show', $report) }}" class="hover:text-amber-600 transition-colors">{{ $report->title }}</a>
                    </h3>
                    <div class="flex items-center gap-2 mb-2">
                      <span class="badge badge-category">{{ $report->category ?? 'General' }}</span>
                      @if(!$report->static_map_url)
                        {!! $priorityBadge($priority) !!}
                      @endif
                    </div>
                  </div>
                  <div class="flex-shrink-0">{!! $adminPill($report->status, $report->id) !!}</div>
                </div>

                {{-- Meta --}}
                <ul class="admin-meta space-y-2">
                  <li class="flex items-start gap-2">
                    <svg class="h-4 w-4 mt-0.5 flex-shrink-0" style="color:var(--accent-700)" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zM4 22v-2c0-2.2 3.8-3.3 6-3.3s6 1.1 6 3.3v2H4z"/></svg>
                    <div class="flex-1 min-w-0">
                      <span class="font-medium block" style="color:var(--text)">{{ $report->user->name ?? 'Anonymous User' }}</span>
                      <span class="text-xs" style="color:var(--muted)">ID: #{{ $report->id }} • {{ $report->user->email ?? 'No email' }}</span>
                    </div>
                  </li>

                  <li class="flex items-start gap-2">
                    <svg class="h-4 w-4 mt-0.5 flex-shrink-0" style="color:var(--accent-700)" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8 2 5 5 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-4-3-7-7-7zm0 9.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z"/></svg>
                    <div class="flex-1 min-w-0">
                      <span class="font-medium block" style="color:var(--text)">{{ $report->short_address ?? $report->location ?? 'Address not specified' }}</span>
                      <span class="text-xs block" style="color:var(--muted)">{{ $report->city_corporation ?? 'City not specified' }}</span>
                    </div>
                  </li>

                  <li>
                    <svg class="h-4 w-4" style="color:var(--accent-700)" viewBox="0 0 24 24" fill="currentColor"><path d="M7 2h10v2H7zM5 6h14v14H5zM9 8h6v6H9z"/></svg>
                    <span>{{ optional($report->created_at)->format('M d, Y \a\t h:i A') }}</span>
                    @if($daysSinceCreated > 0)
                      <span class="text-xs ml-2 px-2 py-1 rounded-full" style="background: rgba(245,158,11,.1); color: var(--accent-700);">
                        {{ $daysSinceCreated }} day{{ $daysSinceCreated !== 1 ? 's' : '' }} ago
                      </span>
                    @endif
                  </li>

                  <li class="flex items-center gap-4 text-xs" style="color:var(--muted)">
                    <span class="flex items-center gap-1">
                      <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor"><path d="M20 2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h4l4 4 4-4h4c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
                      {{ $commentsCount }}
                    </span>
                    <span class="flex items-center gap-1">
                      <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                      {{ $endorsementsCount }}
                    </span>
                  </li>
                </ul>

                {{-- Actions --}}
                <div class="admin-actions">
                  <div class="flex items-center gap-2 flex-1">
                    <a href="{{ route('admin.reports.show', $report) }}" class="admin-btn admin-btn-primary flex-1 justify-center">
                      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5z"/></svg>
                      Review
                    </a>

                    <div class="flex gap-1" id="action-buttons-{{ $report->id }}">
                      @if($report->status === 'pending')
                        <button type="button" onclick="AdminReports.quickAction(event, {{ $report->id }}, 'approve')" class="admin-btn admin-btn-secondary" title="Approve">
                          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                        </button>
                        <button type="button" onclick="AdminReports.quickAction(event, {{ $report->id }}, 'reject')" class="admin-btn admin-btn-danger" title="Reject">
                          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                        </button>
                      @endif

                      <button type="button" onclick="AdminReports.assignToMe(event, {{ $report->id }})" class="admin-btn admin-btn-secondary" title="Assign to me">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zM4 22v-2c0-2.2 3.8-3.3 6-3.3s6 1.1 6 3.3v2H4z"/></svg>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </article>
          @endforeach
        </div>
      @endif
    </div>

    {{-- Table View --}}
    <div id="tableView" class="view-content table-view" style="display:none;">
      <div class="overflow-x-auto">
        <table>
          <thead>
            <tr>
              <th class="w-8"><input type="checkbox" class="bulk-checkbox" id="selectAll" onchange="AdminReports.toggleSelectAll()"></th>
              <th>Report</th><th>User</th><th>Location</th><th>Status</th><th>Priority</th><th>Created</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($reports as $report)
              @php
                $daysSinceCreated = optional($report->created_at)->diffInDays(now()) ?? 0;
                $priority = $daysSinceCreated > 7 ? 'high' : ($daysSinceCreated > 3 ? 'medium' : 'low');
                if ($report->status === 'pending' && $daysSinceCreated > 3) $priority = 'high';
              @endphp
              <tr data-report-id="{{ $report->id }}">
                <td><input type="checkbox" class="bulk-checkbox" value="{{ $report->id }}" onchange="AdminReports.updateBulkSelection()"></td>
                <td>
                  <div class="max-w-xs">
                    <a href="{{ route('admin.reports.show', $report) }}" class="font-medium hover:text-amber-600 transition-colors">{{ $report->title }}</a>
                    <div class="text-xs mt-1" style="color:var(--muted)">ID: #{{ $report->id }} • {{ $report->category ?? 'General' }}</div>
                  </div>
                </td>
                <td>
                  <div>
                    <div class="font-medium">{{ $report->user->name ?? 'Anonymous' }}</div>
                    <div class="text-xs" style="color:var(--muted)">{{ $report->user->email ?? 'No email' }}</div>
                  </div>
                </td>
                <td>
                  <div class="max-w-xs">
                    <div class="font-medium">{{ $report->city_corporation ?? '—' }}</div>
                    <div class="text-xs truncate" style="color:var(--muted)">{{ $report->short_address ?? $report->location ?? 'Address not specified' }}</div>
                  </div>
                </td>
                <td>{!! $adminPill($report->status, $report->id) !!}</td>
                <td>{!! $priorityBadge($priority) !!}</td>
                <td>
                  <div class="whitespace-nowrap">
                    <div>{{ optional($report->created_at)->format('M d, Y') }}</div>
                    <div class="text-xs" style="color:var(--muted)">{{ optional($report->created_at)->format('h:i A') }}</div>
                    @if($daysSinceCreated > 0)
                      <div class="text-xs px-1 py-0.5 rounded mt-1" style="background:rgba(245,158,11,.1); color:var(--accent-700);">{{ $daysSinceCreated }}d ago</div>
                    @endif
                  </div>
                </td>
                <td>
                  <div class="flex gap-1" id="table-action-buttons-{{ $report->id }}">
                    <a href="{{ route('admin.reports.show', $report) }}" class="admin-btn admin-btn-primary">
                      <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5z"/></svg>
                    </a>
                    @if($report->status === 'pending')
                      <button type="button" onclick="AdminReports.quickAction(event, {{ $report->id }}, 'approve')" class="admin-btn admin-btn-secondary" title="Approve">
                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                      </button>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="8" class="text-center py-8" style="color:var(--muted)">No reports found matching your criteria.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- Pagination --}}
    @if(method_exists($reports,'links'))
      <div class="mt-12">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
          <div class="text-sm" style="color:var(--muted)">
            Showing {{ $reports->firstItem() ?? 0 }} to {{ $reports->lastItem() ?? 0 }} of {{ $reports->total() }} results
          </div>
          <div class="flex justify-center">{{ $reports->appends(request()->query())->links() }}</div>
        </div>
      </div>
    @endif
  </div>
</div>

{{-- Loading Overlay --}}
<div id="loadingOverlay" class="loading-overlay" style="display:none;">
  <div class="bg-white rounded-xl p-6 shadow-xl flex items-center gap-4">
    <div class="loading-spinner"></div>
    <div>
      <div class="font-semibold text-gray-900">Processing...</div>
      <div class="text-sm text-gray-600">Please wait while we update the reports.</div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
// Define CSRF token meta tag check
document.addEventListener('DOMContentLoaded', function() {
  // Ensure CSRF token meta tag exists
  if (!document.querySelector('meta[name="csrf-token"]')) {
    const metaTag = document.createElement('meta');
    metaTag.name = 'csrf-token';
    metaTag.content = '{{ csrf_token() }}';
    document.head.appendChild(metaTag);
  }
});

window.ADMIN_ROUTES = {
  status: '{{ route("admin.reports.status", ["report" => "__ID__"]) }}',
  assign: '{{ route("admin.reports.assign", ["report" => "__ID__"]) }}',
  bulk:   '{{ route("admin.reports.bulk") }}',
  approve: '{{ route("admin.reports.approve", ["report" => "__ID__"]) }}',
  reject:  '{{ route("admin.reports.reject", ["report" => "__ID__"]) }}',
};

// Enhanced Admin Reports Management System
window.AdminReports = (function(){
  'use strict';

  const state = {
    selectedReports: new Set(),
    bulkMode: false,
    isLoading: false
  };

  const elements = {
    form: null,
    loadingOverlay: null,
    bulkActions: null,
    selectedCount: null,
    bulkCheckboxes: null,
    selectAllCheckbox: null
  };

  function init() {
    initializeElements();
    bindEvents();
    setupAutoSubmit();
    setupViewToggle();
    setupAnimations();
    setupKeyboardShortcuts();
    
    console.log('AdminReports initialized successfully');
  }

  function initializeElements() {
    elements.form = document.getElementById('adminFiltersForm');
    elements.loadingOverlay = document.getElementById('loadingOverlay');
    elements.bulkActions = document.getElementById('bulkActions');
    elements.selectedCount = document.getElementById('selectedCount');
    elements.bulkCheckboxes = document.querySelectorAll('.bulk-checkbox:not(#selectAll)');
    elements.selectAllCheckbox = document.getElementById('selectAll');
  }

  function getCsrfToken() {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    if (!metaTag) {
      console.error('CSRF token meta tag not found');
      return '';
    }
    return metaTag.content;
  }

  function showLoading() {
    if (elements.loadingOverlay) {
      elements.loadingOverlay.style.display = 'flex';
    }
    state.isLoading = true;
  }

  function hideLoading() {
    if (elements.loadingOverlay) {
      elements.loadingOverlay.style.display = 'none';
    }
    state.isLoading = false;
  }

  function toast(message, type = 'success') {
    // Remove existing toasts
    document.querySelectorAll('.toast').forEach(t => t.remove());
    
    const el = document.createElement('div');
    el.role = 'status';
    el.ariaLive = 'polite';
    el.textContent = message;
    el.className = 'toast fixed left-1/2 transform -translate-x-1/2 bottom-8 z-50 px-6 py-3 rounded-xl font-medium text-white shadow-lg backdrop-blur-sm transition-all duration-300';
    
    const bgColor = type === 'error' ? 'linear-gradient(135deg,#ef4444,#dc2626)' :
                   type === 'warning' ? 'linear-gradient(135deg,#f59e0b,#d97706)' :
                                        'linear-gradient(135deg,#10b981,#059669)';
    el.style.background = bgColor;
    
    document.body.appendChild(el);
    
    setTimeout(() => {
      el.style.opacity = '0';
      el.style.transform = 'translate(-50%,1rem)';
    }, 2500);
    
    setTimeout(() => el.remove(), 3000);
  }

  function bindEvents() {
    // Export button
    const exportBtn = document.getElementById('exportBtn');
    if (exportBtn) {
      exportBtn.addEventListener('click', handleExport);
    }

    // Save filters button
    const saveFiltersBtn = document.getElementById('saveFiltersBtn');
    if (saveFiltersBtn) {
      saveFiltersBtn.addEventListener('click', handleSaveFilters);
    }

    // Copy link button
    const copyLinkBtn = document.getElementById('copyLinkBtn');
    if (copyLinkBtn) {
      copyLinkBtn.addEventListener('click', handleCopyLink);
    }

    // Refresh button
    const refreshBtn = document.getElementById('refreshBtn');
    if (refreshBtn) {
      refreshBtn.addEventListener('click', () => window.location.reload());
    }

    // Bulk toggle button
    const bulkToggleBtn = document.getElementById('bulkToggleBtn');
    if (bulkToggleBtn) {
      bulkToggleBtn.addEventListener('click', toggleBulkMode);
    }
  }

  function setupAutoSubmit() {
    if (!elements.form) return;

    // Auto-submit for select elements
    const selectElements = ['per_page', 'sort', 'city', 'category', 'status', 'priority', 'assigned_to'];
    selectElements.forEach(name => {
      const select = elements.form.querySelector(`select[name="${name}"]`);
      if (select) {
        select.addEventListener('change', () => {
          showLoading();
          setTimeout(() => elements.form.submit(), 100);
        });
      }
    });

    // Auto-submit for date inputs
    ['from', 'to'].forEach(name => {
      const input = elements.form.querySelector(`input[name="${name}"]`);
      if (input) {
        input.addEventListener('change', () => {
          showLoading();
          setTimeout(() => elements.form.submit(), 100);
        });
      }
    });

    // Debounced search input
    const searchInput = elements.form.querySelector('input[name="q"]');
    if (searchInput) {
      let timeout;
      let lastValue = searchInput.value;
      
      searchInput.addEventListener('input', (e) => {
        clearTimeout(timeout);
        const value = e.target.value.trim();
        
        if (value !== lastValue && (value.length >= 2 || value.length === 0)) {
          timeout = setTimeout(() => {
            lastValue = value;
            showLoading();
            elements.form.submit();
          }, 500);
        }
      });
    }
  }

  function setupViewToggle() {
    const viewToggleButtons = document.querySelectorAll('.view-toggle-btn');
    const cardsView = document.getElementById('cardsView');
    const tableView = document.getElementById('tableView');

    viewToggleButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const view = btn.dataset.view;
        
        // Update button states
        viewToggleButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        
        // Toggle views
        if (view === 'cards') {
          cardsView.style.display = 'block';
          tableView.style.display = 'none';
          localStorage.setItem('adminViewPreference', 'cards');
        } else {
          cardsView.style.display = 'none';
          tableView.style.display = 'block';
          localStorage.setItem('adminViewPreference', 'table');
        }
      });
    });

    // Restore saved view preference
    const savedView = localStorage.getItem('adminViewPreference');
    if (savedView) {
      const btn = document.querySelector(`[data-view="${savedView}"]`);
      if (btn) btn.click();
    }
  }

  function setupAnimations() {
    const observer = new IntersectionObserver((entries, obs) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.animationPlayState = 'running';
          obs.unobserve(entry.target);
        }
      });
    }, { rootMargin: '0px 0px -5% 0px', threshold: 0.1 });

    document.querySelectorAll('.slide-in, .fade-scale').forEach(el => {
      el.style.animationPlayState = 'paused';
      observer.observe(el);
    });
  }

  function setupKeyboardShortcuts() {
    document.addEventListener('keydown', (e) => {
      // Ctrl+K or Cmd+K to focus search
      if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = elements.form?.querySelector('input[name="q"]');
        if (searchInput) {
          searchInput.focus();
          searchInput.select();
        }
      }

      // Escape to clear search
      if (e.key === 'Escape') {
        const activeElement = document.activeElement;
        if (activeElement?.name === 'q') {
          activeElement.value = '';
          activeElement.blur();
          if (elements.form) elements.form.submit();
        }
      }
    });
  }

  // Public methods
  function updateBulkSelection() {
    state.selectedReports.clear();
    elements.bulkCheckboxes.forEach(cb => {
      if (cb.checked) {
        state.selectedReports.add(cb.value);
      }
    });

    if (elements.selectedCount) {
      elements.selectedCount.textContent = state.selectedReports.size;
    }

    if (elements.bulkActions) {
      elements.bulkActions.classList.toggle('show', state.selectedReports.size > 0);
    }

    // Update select all checkbox state
    if (elements.selectAllCheckbox) {
      const total = elements.bulkCheckboxes.length;
      const selected = state.selectedReports.size;
      
      elements.selectAllCheckbox.checked = selected > 0 && selected === total;
      elements.selectAllCheckbox.indeterminate = selected > 0 && selected < total;
    }
  }

  function toggleSelectAll() {
    const checked = elements.selectAllCheckbox?.checked || false;
    elements.bulkCheckboxes.forEach(cb => {
      cb.checked = checked;
    });
    updateBulkSelection();
  }

  function toggleBulkMode() {
    state.bulkMode = !state.bulkMode;
    const bulkToggleBtn = document.getElementById('bulkToggleBtn');
    
    // Toggle visibility of bulk checkboxes
    document.querySelectorAll('.bulk-checkbox').forEach(cb => {
      cb.style.display = state.bulkMode ? 'block' : 'none';
    });

    // Update button state
    if (bulkToggleBtn) {
      bulkToggleBtn.classList.toggle('active', state.bulkMode);
    }

    // Clear selection if disabling bulk mode
    if (!state.bulkMode) {
      state.selectedReports.clear();
      if (elements.bulkActions) {
        elements.bulkActions.classList.remove('show');
      }
      document.querySelectorAll('.bulk-checkbox').forEach(cb => {
        cb.checked = false;
      });
    }
  }

  async function quickAction(event, reportId, action) {
    const btn = event?.currentTarget;
    const originalContent = btn?.innerHTML;
    const csrfToken = getCsrfToken();

    if (!csrfToken) {
      toast('Security token missing. Please refresh the page.', 'error');
      return;
    }

    try {
      let url, method, body, newStatus;

      if (action === 'approve') {
        url = window.ADMIN_ROUTES.approve.replace('__ID__', reportId);
        method = 'POST';
        body = null;
        newStatus = 'in_progress';
      } else if (action === 'reject') {
        url = window.ADMIN_ROUTES.reject.replace('__ID__', reportId);
        method = 'DELETE';
        body = null;
        newStatus = 'rejected';
      } else {
        url = window.ADMIN_ROUTES.status.replace('__ID__', reportId);
        method = 'PATCH';
        body = new URLSearchParams({ status: action });
        newStatus = action;
      }

      // Show loading state on button
      if (btn?.tagName === 'BUTTON') {
        btn.disabled = true;
        btn.innerHTML = '<div class="loading-spinner" style="width:1rem;height:1rem"></div>';
      }

      const response = await fetch(url, {
        method,
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          ...(body ? { 'Content-Type': 'application/x-www-form-urlencoded' } : {})
        },
        body
      });

      const data = await response.json().catch(() => ({}));

      if (!response.ok) {
        throw new Error(data.message || `HTTP ${response.status}: Request failed`);
      }

      // Update UI
      updateReportStatus(reportId, newStatus, data.deleted);

      // Show success message
      if (data.deleted) {
        toast('Report rejected and deleted successfully');
      } else {
        toast(`Report ${action}d successfully`);
      }

      // Remove approve/reject buttons after action
      if (action === 'approve' || action === 'reject') {
        removeActionButtons(reportId);
      }

    } catch (error) {
      console.error('Quick action failed:', error);
      toast(error.message || 'Action failed. Please try again.', 'error');
    } finally {
      if (btn?.tagName === 'BUTTON' && btn.parentNode) {
        btn.disabled = false;
        btn.innerHTML = originalContent;
      }
    }
  }

  function updateReportStatus(reportId, newStatus, deleted = false) {
    const reportCard = document.querySelector(`[data-report-id="${reportId}"]`);
    
    if (deleted) {
      if (reportCard) {
        reportCard.style.transition = 'all 0.3s ease';
        reportCard.style.opacity = '0';
        reportCard.style.transform = 'translateY(-20px)';
        setTimeout(() => reportCard.remove(), 300);
      }
      return;
    }

    // Update status pills
    const statusPills = document.querySelectorAll(`[data-report-id="${reportId}"] .admin-status-pill`);
    statusPills.forEach(pill => {
      pill.className = `admin-status-pill status-${newStatus}`;
      pill.textContent = `● ${newStatus.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}`;
    });
  }

  function removeActionButtons(reportId) {
    // Remove from card view
    const cardButtons = document.querySelector(`#action-buttons-${reportId}`);
    if (cardButtons) {
      cardButtons.querySelectorAll('button[onclick*="approve"], button[onclick*="reject"]').forEach(btn => {
        btn.style.transition = 'all 0.2s ease';
        btn.style.opacity = '0';
        btn.style.transform = 'scale(0.8)';
        setTimeout(() => btn.remove(), 200);
      });
    }

    // Remove from table view
    const tableButtons = document.querySelector(`#table-action-buttons-${reportId}`);
    if (tableButtons) {
      tableButtons.querySelectorAll('button[onclick*="approve"], button[onclick*="reject"]').forEach(btn => {
        btn.style.transition = 'all 0.2s ease';
        btn.style.opacity = '0';
        btn.style.transform = 'scale(0.8)';
        setTimeout(() => btn.remove(), 200);
      });
    }
  }

  async function assignToMe(event, reportId) {
    const btn = event?.currentTarget;
    const originalContent = btn?.innerHTML;
    
    if (btn) btn.disabled = true;

    try {
      const url = window.ADMIN_ROUTES.assign.replace('__ID__', reportId);
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': getCsrfToken(),
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: Assignment failed`);
      }

      toast('Report assigned to you successfully');
    } catch (error) {
      console.error('Assignment failed:', error);
      toast('Assignment failed. Please try again.', 'error');
    } finally {
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = originalContent;
      }
    }
  }

  async function bulkAction(action) {
    if (state.selectedReports.size === 0) {
      toast('Please select reports first', 'warning');
      return;
    }

    const confirmMessage = `Are you sure you want to ${action} ${state.selectedReports.size} report(s)?`;
    if (!confirm(confirmMessage)) return;

    showLoading();

    try {
      const response = await fetch(window.ADMIN_ROUTES.bulk, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': getCsrfToken(),
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          action,
          report_ids: Array.from(state.selectedReports)
        })
      });

      const data = await response.json().catch(() => ({}));

      if (!response.ok) {
        throw new Error(data.message || 'Bulk action failed');
      }

      toast(`Bulk action completed: ${data.processed} reports updated`);
      
      // Reload page after a short delay
      setTimeout(() => window.location.reload(), 1000);

    } catch (error) {
      console.error('Bulk action failed:', error);
      toast(error.message || 'Bulk action failed', 'error');
    } finally {
      hideLoading();
    }
  }

  // Filter functions
  function filterByStatus(status) {
    if (!elements.form) return;
    
    const statusSelect = elements.form.querySelector('select[name="status"]');
    if (statusSelect) {
      statusSelect.value = status;
      showLoading();
      elements.form.submit();
    }
  }

  function filterByPriority() {
    if (!elements.form) return;
    
    // Set high priority and date range (last 7 days)
    const fromInput = elements.form.querySelector('input[name="from"]');
    const toInput = elements.form.querySelector('input[name="to"]');
    const prioritySelect = elements.form.querySelector('select[name="priority"]');
    
    const today = new Date();
    const weekAgo = new Date(today);
    weekAgo.setDate(today.getDate() - 7);
    
    if (fromInput) fromInput.value = weekAgo.toISOString().split('T')[0];
    if (toInput) toInput.value = today.toISOString().split('T')[0];
    if (prioritySelect) prioritySelect.value = 'high';
    
    showLoading();
    elements.form.submit();
  }

  function filterByDate(period) {
    if (!elements.form) return;
    
    const fromInput = elements.form.querySelector('input[name="from"]');
    const toInput = elements.form.querySelector('input[name="to"]');
    const today = new Date().toISOString().split('T')[0];
    
    if (period === 'today') {
      if (fromInput) fromInput.value = today;
      if (toInput) toInput.value = today;
    }
    
    showLoading();
    elements.form.submit();
  }

  function generateReport() {
    const exportBtn = document.getElementById('exportBtn');
    if (exportBtn) exportBtn.click();
  }

  function quickStatusChange(element, reportId, currentStatus) {
    const statuses = ['pending', 'in_progress', 'resolved', 'rejected'];
    const labels = ['Pending', 'In Progress', 'Resolved', 'Rejected'];
    
    const choice = prompt(
      `Change status to:\n${labels.map((l, i) => `${i + 1}. ${l}`).join('\n')}\n\nEnter number (1-4):`
    );
    
    const index = parseInt(choice, 10) - 1;
    
    if (index >= 0 && index < statuses.length && statuses[index] !== currentStatus) {
      quickAction({ currentTarget: element }, reportId, statuses[index]);
    }
  }

  // Event handlers
  async function handleExport() {
    const exportBtn = document.getElementById('exportBtn');
    if (!exportBtn) return;

    const originalContent = exportBtn.innerHTML;
    exportBtn.disabled = true;
    exportBtn.innerHTML = '<div class="loading-spinner" style="width:1rem;height:1rem;"></div> Exporting...';

    try {
      const params = new URLSearchParams(window.location.search);
      params.set('export', 'csv');
      
      const response = await fetch(`${window.location.pathname}?${params}`);
      
      if (!response.ok) {
        throw new Error('Export failed');
      }
      
      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      
      a.href = url;
      a.download = `reports_export_${new Date().toISOString().split('T')[0]}.csv`;
      document.body.appendChild(a);
      a.click();
      a.remove();
      window.URL.revokeObjectURL(url);
      
      toast('Export completed successfully!');
    } catch (error) {
      console.error('Export failed:', error);
      toast('Export failed. Please try again.', 'error');
    } finally {
      exportBtn.disabled = false;
      exportBtn.innerHTML = originalContent;
    }
  }

  function handleSaveFilters() {
    const name = prompt('Enter a name for this filter set:');
    if (name) {
      const filterData = new URLSearchParams(window.location.search).toString();
      localStorage.setItem(`adminFilter_${name}`, filterData);
      toast(`Filter "${name}" saved!`);
    }
  }

  async function handleCopyLink() {
    const copyBtn = document.getElementById('copyLinkBtn');
    if (!copyBtn) return;

    try {
      await navigator.clipboard.writeText(window.location.href);
      toast('Link copied to clipboard!');
      
      const originalContent = copyBtn.innerHTML;
      copyBtn.innerHTML = '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>Copied!';
      setTimeout(() => {
        copyBtn.innerHTML = originalContent;
      }, 2000);
    } catch (error) {
      console.error('Copy failed:', error);
      toast('Failed to copy link', 'error');
    }
  }

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Hide loading overlay on window load
  window.addEventListener('load', hideLoading);

  // Public API
  return {
    updateBulkSelection,
    toggleSelectAll,
    quickAction,
    assignToMe,
    bulkAction,
    filterByStatus,
    filterByPriority,
    filterByDate,
    generateReport,
    quickStatusChange
  };
})();
</script>
@endpush