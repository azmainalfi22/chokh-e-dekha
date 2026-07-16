<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Admin') • {{ config('app.name', 'Chokh-e-Dekha') }}</title>

  <script>
    (function(){
      const saved = localStorage.getItem('theme');
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      if (saved === 'dark' || (!saved && prefersDark)) document.documentElement.classList.add('dark');
    })();
  </script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @stack('styles')

  <style>
    body { font-family: 'Inter', 'Noto Sans Bengali', sans-serif; }

    /* Sidebar links */
    .nav-item {
      display: flex; align-items: center; gap: .625rem;
      padding: .5rem .75rem; border-radius: .625rem;
      font-size: .875rem; font-weight: 500; color: #94a3b8;
      transition: background .15s, color .15s;
      text-decoration: none;
    }
    .nav-item:hover { background: rgba(255,255,255,.07); color: #e2e8f0; }
    .nav-item.active { background: rgba(16,185,129,.15); color: #6ee7b7; }
    .nav-item.active:hover { background: rgba(16,185,129,.2); }

    .nav-section {
      font-size: .65rem; font-weight: 700; letter-spacing: .08em;
      text-transform: uppercase; color: #475569; padding: .75rem .75rem .25rem;
    }

    /* Top bar action buttons */
    .topbar-btn {
      display: inline-flex; align-items: center; gap: .4rem;
      padding: .45rem .75rem; border-radius: .625rem; font-size: .8125rem; font-weight: 500;
      transition: background .15s, color .15s;
    }
    .topbar-btn-outline {
      color: #64748b; border: 1px solid #e2e8f0;
    }
    .topbar-btn-outline:hover { background: #f8fafc; color: #0f172a; }
    .dark .topbar-btn-outline { color: #94a3b8; border-color: #334155; }
    .dark .topbar-btn-outline:hover { background: #1e293b; color: #e2e8f0; }

    .topbar-btn-primary {
      background: #059669; color: #fff;
    }
    .topbar-btn-primary:hover { background: #047857; }

    /* Stat cards */
    .stat-card {
      background: #fff; border-radius: 1rem;
      border: 1px solid #f1f5f9;
      box-shadow: 0 1px 3px rgba(15,23,42,.06), 0 1px 2px rgba(15,23,42,.04);
      padding: 1.25rem 1.5rem;
      transition: box-shadow .15s;
    }
    .stat-card:hover { box-shadow: 0 4px 12px rgba(15,23,42,.1); }
    .dark .stat-card { background: #1e293b; border-color: #334155; }

    /* Status badges */
    .badge { display:inline-flex; align-items:center; gap:.25rem; padding:.2rem .6rem; border-radius:9999px; font-size:.75rem; font-weight:600; }
    .badge-pending     { background:#fef3c7; color:#92400e; }
    .badge-in_progress { background:#dbeafe; color:#1e40af; }
    .badge-resolved    { background:#d1fae5; color:#065f46; }
    .badge-rejected    { background:#fee2e2; color:#991b1b; }
    .dark .badge-pending     { background:rgba(245,158,11,.15); color:#fcd34d; }
    .dark .badge-in_progress { background:rgba(59,130,246,.15); color:#93c5fd; }
    .dark .badge-resolved    { background:rgba(16,185,129,.15); color:#6ee7b7; }
    .dark .badge-rejected    { background:rgba(239,68,68,.15);  color:#fca5a5; }
  </style>
</head>

<body class="h-full bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white antialiased">
<div class="flex h-screen overflow-hidden">

  {{-- ─── SIDEBAR ─────────────────────────────────── --}}
  <aside id="sidebar"
    class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-slate-900 dark:bg-slate-950
           border-r border-slate-800 transition-transform duration-300
           -translate-x-full lg:static lg:translate-x-0">

    {{-- Brand --}}
    <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-800">
      <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-emerald-500 shadow-lg shadow-emerald-500/30">
        <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 5C6.5 5 2 9 2 12s4.5 7 10 7 10-4 10-7-4.5-7-10-7zm0 11a4 4 0 110-8 4 4 0 010 8z"/>
        </svg>
      </div>
      <div class="min-w-0">
        <div class="text-sm font-bold text-white leading-tight">Chokh-e-Dekha</div>
        <div class="text-xs text-emerald-400 font-medium">Admin Panel</div>
      </div>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">
      <span class="nav-section">Main</span>

      <a href="{{ route('admin.dashboard') }}"
         class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM14 5a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM14 12a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7z"/>
        </svg>
        Dashboard
      </a>

      <a href="{{ route('admin.reports.index') }}"
         class="nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Reports
        @php $pending = \App\Models\Report::where('status','pending')->count(); @endphp
        @if($pending > 0)
          <span class="ml-auto text-xs font-bold bg-amber-500 text-white px-2 py-0.5 rounded-full">{{ $pending }}</span>
        @endif
      </a>

      <a href="{{ route('admin.users.index') }}"
         class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        Users
      </a>

      <span class="nav-section mt-3">Quick Links</span>

      <a href="{{ route('reports.index') }}" target="_blank"
         class="nav-item">
        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
        </svg>
        View Public Site
      </a>
    </nav>

    {{-- User footer --}}
    <div class="border-t border-slate-800 px-4 py-3">
      <div class="flex items-center gap-3">
        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white text-sm font-bold">
          {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0">
          <div class="text-sm font-semibold text-white truncate">{{ auth()->user()->name ?? 'Admin' }}</div>
          <div class="text-xs text-slate-400 truncate">{{ auth()->user()->email ?? '' }}</div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" title="Logout"
                  class="text-slate-500 hover:text-rose-400 transition-colors p-1 rounded">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
          </button>
        </form>
      </div>
    </div>
  </aside>

  {{-- Sidebar overlay (mobile) --}}
  <div id="sidebarOverlay" class="fixed inset-0 z-40 bg-black/60 hidden lg:hidden backdrop-blur-sm"></div>

  {{-- ─── MAIN ─────────────────────────────────────── --}}
  <div class="flex flex-1 flex-col min-w-0 overflow-hidden">

    {{-- Top bar --}}
    <header class="flex-shrink-0 flex items-center justify-between gap-4
                   bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800
                   px-6 h-16 z-30">

      {{-- Mobile menu --}}
      <button id="sidebarToggle" class="lg:hidden p-2 rounded-lg text-slate-500 hover:text-slate-900
                                         hover:bg-slate-100 dark:hover:text-white dark:hover:bg-slate-800 transition-colors">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>

      {{-- Page title --}}
      <div class="flex-1 min-w-0 hidden sm:block">
        @hasSection('page_title')
          <h1 class="text-base font-semibold text-slate-900 dark:text-white truncate">
            @yield('page_title')
          </h1>
          @hasSection('page_subtitle')
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">@yield('page_subtitle')</p>
          @endif
        @endif
      </div>

      {{-- Actions --}}
      <div class="flex items-center gap-2">
        @yield('page_actions')

        <button id="themeToggle"
                class="p-2 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-100
                       dark:hover:text-white dark:hover:bg-slate-800 transition-colors"
                aria-label="Toggle theme">
          <svg class="h-5 w-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
          </svg>
          <svg class="h-5 w-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
          </svg>
        </button>
      </div>
    </header>

    {{-- Page content --}}
    <main class="flex-1 overflow-y-auto p-6 bg-slate-50 dark:bg-slate-950">

      {{-- Flash messages --}}
      @if(session('success'))
        <div class="mb-5 flex items-center gap-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20
                    border border-emerald-200 dark:border-emerald-800
                    px-4 py-3 text-sm text-emerald-800 dark:text-emerald-200">
          <svg class="h-5 w-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
          </svg>
          {{ session('success') }}
        </div>
      @endif
      @if(session('error') || $errors->any())
        <div class="mb-5 flex items-start gap-3 rounded-xl bg-rose-50 dark:bg-rose-900/20
                    border border-rose-200 dark:border-rose-800
                    px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
          <svg class="h-5 w-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
          </svg>
          <div>
            {{ session('error') ?? 'There were some problems with your request.' }}
            @if($errors->any())
              <ul class="mt-1 list-disc pl-4 space-y-0.5">
                @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
              </ul>
            @endif
          </div>
        </div>
      @endif

      @yield('content')
    </main>
  </div>
</div>

@stack('scripts')

<script>
  // Sidebar mobile toggle
  (function() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggle  = document.getElementById('sidebarToggle');
    const open  = () => { sidebar.classList.remove('-translate-x-full'); overlay.classList.remove('hidden'); };
    const close = () => { sidebar.classList.add('-translate-x-full');    overlay.classList.add('hidden'); };
    toggle?.addEventListener('click', () => sidebar.classList.contains('-translate-x-full') ? open() : close());
    overlay?.addEventListener('click', close);
  })();

  // Theme toggle
  (function() {
    document.getElementById('themeToggle')?.addEventListener('click', () => {
      const isDark = document.documentElement.classList.toggle('dark');
      localStorage.setItem('theme', isDark ? 'dark' : 'light');
    });
  })();

  // Confirm dialogs
  document.addEventListener('click', (e) => {
    const el = e.target.closest('[data-confirm]');
    if (el && !confirm(el.getAttribute('data-confirm') || 'Are you sure?')) {
      e.preventDefault(); e.stopPropagation();
    }
  });
</script>
</body>
</html>
