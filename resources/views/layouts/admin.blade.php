<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@push('styles')
  @include('partials._theme')
@endpush

<head>
  <!-- Theme boot (prevents flash) -->
  <script>
    (function () {
      const saved = localStorage.getItem('theme'); // 'light' | 'dark' | null
      const prefersDark = window.matchMedia?.('(prefers-color-scheme: dark)').matches;
      const shouldDark = saved ? (saved === 'dark') : !!prefersDark;
      if (shouldDark) document.documentElement.classList.add('dark');
      window.__theme = saved ?? (prefersDark ? 'dark' : 'light');
    })();
  </script>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>@yield('title', 'Admin') • {{ config('app.name', 'Chokh-e-Dekha') }}</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @stack('styles')

  <style>
    :root{
      /* page bg */
      --page-light: #fffaf5;
      --page-dark:  #0b0e12;
      --page-dark-grad-1: rgba(255,179,0,.06);
      --page-dark-grad-2: rgba(244,63,94,.07);

      /* elevation */
      --shadow-strong: 0 12px 30px rgba(15, 23, 42, .18), 0 4px 12px rgba(15,23,42,.12);
      --shadow-strong-hover: 0 18px 40px rgba(15, 23, 42, .22), 0 6px 16px rgba(15,23,42,.16);

      /* header glass */
      --header-bg-light: linear-gradient(90deg, rgba(254,243,199,.85), rgba(254,215,170,.85) 40%, rgba(254,205,211,.85));
      --header-bg-dark:  linear-gradient(90deg, rgba(24,24,27,.55), rgba(17,24,39,.55) 40%, rgba(30,27,22,.55));
      --header-ring: rgba(120, 53, 15, .15);

      /* nav text colors (consistent across app/admin) */
      --nav-text: #7c2d12;         /* amber-900-ish */
      --nav-text-hover: #5a1e0a;
      --nav-text-active: #1f2937;  /* slate-800 */
      --nav-text-dark: #fde68a;        /* amber-200 */
      --nav-text-dark-hover: #fffbeb;  /* amber-50 */
      --nav-text-dark-active: #ffffff;

      /* sidebar */
      --sidebar-glass-light: rgba(255,255,255,.85);
      --sidebar-glass-dark:  rgba(255,255,255,.06);
    }

    /* page bg */
    body{
      min-height: 100vh;
      background:
        radial-gradient(1200px 400px at -10% -10%, rgba(251,191,36,.12), transparent 40%),
        radial-gradient(1000px 300px at 110% 110%, rgba(244,63,94,.10), transparent 45%),
        var(--page-light);
      color: #0f172a;
    }
    .dark body{
      background:
        radial-gradient(1200px 400px at -10% -10%, var(--page-dark-grad-1), transparent 40%),
        radial-gradient(1000px 300px at 110% 110%, var(--page-dark-grad-2), transparent 45%),
        var(--page-dark);
      color: #e5e7eb;
    }

    /* reduced motion */
    @media (prefers-reduced-motion: reduce) {
      * { animation: none !important; transition: none !important; }
    }

    /* header (fixed glass) */
    .header {
      position: fixed; inset-inline: 0; top: 0; z-index: 50;
      backdrop-filter: saturate(160%) blur(10px);
      -webkit-backdrop-filter: saturate(160%) blur(10px);
      border-bottom: 1px solid var(--header-ring);
      background: var(--header-bg-light);
      box-shadow: 0 10px 28px rgba(15,23,42,.20);
    }
    .dark .header { background: var(--header-bg-dark); border-bottom-color: rgba(255,255,255,.06); }

    .content-offset { padding-top: 5.0rem; } /* keep content below fixed header */

    /* buttons */
    .btn {
      display:inline-flex; align-items:center; gap:.5rem;
      padding:.55rem .9rem; border-radius:.9rem; line-height:1;
      transition: box-shadow .15s ease, transform .08s ease, background-color .15s ease, color .15s ease;
      box-shadow: var(--shadow-strong);
      border: 1px solid rgba(120,53,15,.15);
      backdrop-filter: blur(2px);
    }
    .btn:hover { box-shadow: var(--shadow-strong-hover); transform: translateY(-1px); }
    .btn:active { transform: translateY(0); box-shadow: var(--shadow-strong); }
    .btn-primary { background-image: linear-gradient(90deg, #d97706, #e11d48); color:#fff; border:none; text-shadow:0 1px 0 rgba(0,0,0,.12); }
    .btn-quiet   { background: rgba(255,255,255,.85); color: var(--nav-text); }
    .dark .btn-quiet { background: rgba(255,255,255,.08); color: var(--nav-text-dark); border-color: rgba(255,255,255,.06); }

    /* nav links (top menu) */
    .nav-link {
      position: relative; padding:.5rem .75rem; border-radius:.9rem; font-weight:600;
      color: var(--nav-text); background: rgba(255,255,255,.85);
      border: 1px solid rgba(120,53,15,.12); box-shadow: 0 2px 10px rgba(0,0,0,.04);
      transition: color .15s, background-color .15s, box-shadow .15s, transform .08s;
    }
    .nav-link:hover { color: var(--nav-text-hover); transform: translateY(-1px); box-shadow: 0 10px 20px rgba(0,0,0,.08); }
    .nav-link--active { background: linear-gradient(180deg, rgba(255,255,255,.95), rgba(255,255,255,.85)); color: var(--nav-text-active); }
    .dark .nav-link { color: var(--nav-text-dark); background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.06); }
    .dark .nav-link:hover { color: var(--nav-text-dark-hover); }
    .dark .nav-link--active { background: rgba(255,255,255,.14); color: var(--nav-text-dark-active); }
    .nav-link::after{
      content:""; position:absolute; left:.75rem; right:.75rem; bottom:.45rem; height:2px;
      border-radius:2px; background: currentColor; opacity:0; transform: scaleX(.6);
      transition: transform .18s, opacity .18s;
    }
    .nav-link:hover::after{ opacity:.45; transform: scaleX(1); }
    .nav-link--active::after{ opacity:.7; }

    /* sidebar (glassy) */
    .sidebar {
      background: var(--sidebar-glass-light);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      border: 1px solid rgba(255,255,255,.6);
      box-shadow: var(--shadow-strong);
    }
    .dark .sidebar {
      background: var(--sidebar-glass-dark);
      border-color: rgba(255,255,255,.08);
      box-shadow: 0 12px 30px rgba(0,0,0,.35);
    }

    /* sidebar links */
    .side-link {
      display:flex; align-items:center; gap:.5rem; padding:.55rem .75rem; border-radius:.9rem;
      font-weight:600; color:#7c2d12; transition: background-color .15s, color .15s, transform .08s;
    }
    .side-link:hover { background: rgba(251,191,36,.12); transform: translateY(-1px); }
    .side-link--active { background: rgba(251,191,36,.25); color:#1f2937; }
    .dark .side-link { color:#fde68a; }
    .dark .side-link:hover { background: rgba(255,255,255,.08); }
    .dark .side-link--active { background: rgba(255,255,255,.14); color:#fff; }
  </style>
</head>

<body class="min-h-screen antialiased relative overflow-x-hidden bg-gradient-to-br from-amber-50 via-orange-50 to-rose-50 grain-overlay">
  {{-- Background blobs --}}
  <div class="pointer-events-none absolute -top-28 -left-24 h-[28rem] w-[28rem] rounded-full blur-3xl opacity-20 bg-gradient-to-br from-amber-300 to-rose-300 -z-10"></div>
  <div class="pointer-events-none absolute -bottom-32 -right-24 h-[32rem] w-[32rem] rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-orange-300 to-pink-300 -z-10"></div>

  {{-- Top bar (fixed) --}}
  <header class="header">
    <div class="mx-auto max-w-7xl px-4 py-3 flex items-center justify-between gap-3 flex-wrap">
      <div class="flex items-center gap-3">
        <button id="sidebarToggle" class="md:hidden inline-flex items-center justify-center h-9 w-9 rounded-lg nav-link" aria-label="Toggle sidebar">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M4 7h16v2H4zM4 15h16v2H4z"/></svg>
        </button>

        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
          <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-rose-600 text-white shadow">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5C6.5 5 2 9.5 2 12s4.5 7 10 7 10-4.5 10-7-4.5-7-10-7zm0 11a4 4 0 110-8 4 4 0 010 8z"/></svg>
          </span>
          <span class="font-extrabold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-amber-700 via-amber-900 to-rose-700 dark:from-amber-200 dark:via-amber-100 dark:to-rose-200">
            {{ config('app.name', 'Chokh-e-Dekha') }} <span class="opacity-80">Admin</span>
          </span>
        </a>
      </div>

      <div class="flex items-center gap-2 flex-wrap">
        <button id="themeToggle" type="button" class="btn btn-quiet" aria-label="Toggle dark mode">
          <svg class="h-4 w-4 hidden dark:block" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4V2m0 20v-2m8-8h2M2 12h2m13.657-6.343l1.414-1.414M4.929 19.071l1.414-1.414m0-11.314L4.93 4.929M19.071 19.071l-1.414-1.414M12 8a4 4 0 100 8 4 4 0 000-8z"/></svg>
          <svg class="h-4 w-4 dark:hidden" viewBox="0 0 24 24" fill="currentColor"><path d="M21 12.79A9 9 0 1111.21 3a7 7 0 109.79 9.79z"/></svg>
          <span class="text-sm font-medium dark:hidden">Dark</span>
          <span class="text-sm font-medium hidden dark:inline">Light</span>
        </button>

        <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'nav-link--active' : '' }}">Reports</a>

        {{-- User dropdown --}}
        <div class="relative">
          <button id="userMenuBtn" class="nav-link">
            <span class="hidden sm:inline">{{ auth()->user()->name ?? 'Admin' }}</span>
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M5.5 7l4.5 5 4.5-5"/></svg>
          </button>
          <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 rounded-xl bg-white ring-1 ring-amber-900/10 shadow-2xl p-1 dark:bg-slate-900 dark:ring-white/10">
            <a href="{{ route('admin.profile.edit') }}" class="block px-3 py-2 text-sm rounded-lg hover:bg-amber-50 dark:hover:bg-white/10">Profile</a>
            <a href="{{ route('admin.users.index') }}" class="block px-3 py-2 text-sm rounded-lg hover:bg-amber-50 dark:hover:bg-white/10">Manage Users</a>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button class="w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-rose-50 text-rose-700 dark:hover:bg-rose-500/10 dark:text-rose-300">Logout</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </header>

  {{-- Shell --}}
  <div class="content-offset mx-auto max-w-7xl px-4 py-6 grid grid-cols-1 md:grid-cols-[240px_1fr] gap-6">
    {{-- Sidebar --}}
    <aside id="sidebar" class="sidebar md:sticky md:top-24 h-max md:h-[calc(100vh-8rem)] md:overflow-y-auto rounded-2xl p-4 md:block hidden">
      <nav class="space-y-1 text-sm">
        <a href="{{ route('admin.dashboard') }}" class="side-link {{ request()->routeIs('admin.dashboard') ? 'side-link--active' : '' }}">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3l9 8h-3v9H6v-9H3z"/></svg>
          Dashboard
        </a>
        <a href="{{ route('admin.reports.index') }}" class="side-link {{ request()->routeIs('admin.reports.*') ? 'side-link--active' : '' }}">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M5 4h14v2H5zM5 8h14v12H5z"/></svg>
          Reports
        </a>
        <a href="{{ route('admin.users.index') }}" class="side-link {{ request()->routeIs('admin.users.*') ? 'side-link--active' : '' }}">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11a3 3 0 100-6 3 3 0 000 6zM8 11a3 3 0 100-6 3 3 0 000 6zm8 2c-2.2 0-6 1.1-6 3.3V19h12v-2.7c0-2.2-3.8-3.3-6-3.3zM8 13c-2.3 0-6 1.1-6 3.3V19h6v-2.7c0-1.1.7-2 2-2H8z"/></svg>
          Users
        </a>
        <a href="{{ route('admin.profile.edit') }}" class="side-link {{ request()->routeIs('admin.profile.*') ? 'side-link--active' : '' }}">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zm7 2H5a2 2 0 00-2 2v5h18v-5a2 2 0 00-2-2z"/></svg>
          Profile
        </a>
      </nav>

      <div class="mt-6 p-[2px] rounded-2xl bg-gradient-to-br from-amber-400 via-orange-400 to-rose-400">
        <div class="rounded-2xl bg-white/85 dark:bg-white/5 backdrop-blur p-4 text-xs text-amber-900/80 dark:text-amber-100/90">
          <div class="font-semibold mb-1">Quick tip</div>
          Use the “Reports” page to filter by city and status.
        </div>
      </div>
    </aside>

    {{-- Main --}}
    <main class="min-w-0 relative z-10">
      {{-- Flashes --}}
      @php
        $flashSuccess = session('success');
        $flashError   = session('error') ?? ($errors->any() ? 'There were some problems with your request.' : null);
        $flashStatus  = session('status');
      @endphp
      @if($flashSuccess || $flashError || $flashStatus)
        <div class="mb-4 space-y-2">
          @if($flashSuccess)
            <div class="rounded-xl px-4 py-3 bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-100 dark:ring-emerald-800/40">{{ $flashSuccess }}</div>
          @endif
          @if($flashError)
            <div class="rounded-xl px-4 py-3 bg-rose-50 text-rose-800 ring-1 ring-rose-200 dark:bg-rose-900/20 dark:text-rose-100 dark:ring-rose-800/40">
              {{ $flashError }}
              @if($errors->any())
                <ul class="mt-2 list-disc pl-5 text-sm">
                  @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
              @endif
            </div>
          @endif
          @if($flashStatus)
            <div class="rounded-xl px-4 py-3 bg-amber-50 text-amber-900 ring-1 ring-amber-200 dark:bg-amber-900/20 dark:text-amber-100 dark:ring-amber-800/40">{{ $flashStatus }}</div>
          @endif
        </div>
      @endif

      {{-- Page header slots --}}
      @if(View::hasSection('page_title') || View::hasSection('page_actions'))
        <div class="mb-4 flex items-center justify-between gap-3">
          <div>
            @hasSection('page_title')
              <h1 class="text-xl sm:text-2xl font-extrabold bg-clip-text text-transparent
                         bg-gradient-to-r from-amber-700 via-orange-700 to-rose-700 dark:from-amber-200 dark:via-orange-200 dark:to-rose-100">
                @yield('page_title')
              </h1>
            @endif
            @hasSection('page_subtitle')
              <p class="text-sm text-amber-900/70 dark:text-amber-100/70 mt-0.5">@yield('page_subtitle')</p>
            @endif
          </div>
          <div class="flex items-center gap-2">
            @yield('page_actions')
          </div>
        </div>
      @endif

      @yield('content')
    </main>
  </div>

  @stack('scripts')

  <script>
    // Sidebar & user menu
    (function () {
      const sidebar = document.getElementById('sidebar');
      const toggle  = document.getElementById('sidebarToggle');
      const userBtn = document.getElementById('userMenuBtn');
      const userMenu= document.getElementById('userMenu');

      toggle?.addEventListener('click', () => sidebar?.classList.toggle('hidden'));

      if (userBtn && userMenu) {
        userBtn.addEventListener('click', () => userMenu.classList.toggle('hidden'));
        document.addEventListener('click', (e) => {
          if (!userBtn.contains(e.target) && !userMenu.contains(e.target)) userMenu.classList.add('hidden');
        }, { capture: true });
      }

      // confirm helper
      document.addEventListener('click', (e) => {
        const el = e.target.closest('[data-confirm]');
        if (el) {
          const msg = el.getAttribute('data-confirm') || 'Are you sure?';
          if (!window.confirm(msg)) {
            e.preventDefault();
            e.stopPropagation();
          }
        }
      });
    })();

    // Theme toggle
    (function () {
      const withTrans = (fn) => { const el=document.documentElement; el.style.transition='background-color .25s,color .25s'; fn(); setTimeout(()=>el.style.transition='',300); };
      const btn = document.getElementById('themeToggle');
      btn?.addEventListener('click', () => {
        const html = document.documentElement;
        const isDark = html.classList.contains('dark');
        withTrans(() => {
          if (isDark) { html.classList.remove('dark'); localStorage.setItem('theme', 'light'); }
          else        { html.classList.add('dark');    localStorage.setItem('theme', 'dark'); }
        });
      });
      try {
        const mq = window.matchMedia('(prefers-color-scheme: dark)');
        mq.addEventListener('change', (e) => {
          if (!localStorage.getItem('theme')) {
            withTrans(() => document.documentElement.classList.toggle('dark', e.matches));
          }
        });
      } catch {}
    })();
  </script>
</body>
</html>
