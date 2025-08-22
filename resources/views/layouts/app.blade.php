<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  {{-- CSRF + user name (for optimistic comments) --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @auth
    <meta name="user-name" content="{{ auth()->user()->name }}">
  @endauth

  {{-- Theme boot (no flash) --}}
  <script>
    (() => {
      const saved = localStorage.getItem('theme'); // 'light' | 'dark' | null
      const prefersDark = window.matchMedia?.('(prefers-color-scheme: dark)')?.matches;
      const shouldDark = saved ? (saved === 'dark') : !!prefersDark;
      if (shouldDark) document.documentElement.classList.add('dark');
      window.__theme = saved ?? (prefersDark ? 'dark' : 'light');
    })();
  </script>

  <title>@yield('title', 'Dashboard') • {{ config('app.name', 'Chokh-e-Dekha') }}</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  {{-- Include global theme tokens/utilities once for all pages --}}
  @include('partials._theme')

  {{-- Page-level style stacks --}}
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

      /* header */
      --header-bg-light: linear-gradient(90deg, rgba(254,243,199,.85), rgba(254,215,170,.85) 40%, rgba(254,205,211,.85));
      --header-bg-dark:  linear-gradient(90deg, rgba(30,27,22,.55), rgba(28,25,23,.55) 40%, rgba(31,26,23,.55));
      --header-ring: rgba(120, 53, 15, .15);

      /* nav text */
      --nav-text: #7c2d12;         /* amber-900-ish */
      --nav-text-hover: #5a1e0a;   /* darker */
      --nav-text-active: #1f2937;  /* slate-800 */

      --nav-text-dark: #fde68a;        /* amber-200 */
      --nav-text-dark-hover: #fffbeb;  /* amber-50 */
      --nav-text-dark-active: #fff;    /* white */

      --ring-soft: 1px solid rgba(120, 53, 15, .15);
    }

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

    @media (prefers-reduced-motion: reduce) { * { animation: none !important; transition: none !important; } }

    .btn {
      display:inline-flex; align-items:center; gap:.5rem;
      padding:.55rem .9rem; border-radius:.9rem; line-height:1;
      transition: box-shadow .15s ease, transform .08s ease, background-color .15s ease, color .15s ease, opacity .15s ease;
      box-shadow: var(--shadow-strong); border: var(--ring-soft); backdrop-filter: blur(2px);
    }
    .btn:hover { box-shadow: var(--shadow-strong-hover); transform: translateY(-1px); }
    .btn:active { transform: translateY(0); box-shadow: var(--shadow-strong); }
    .btn-primary { background-image: linear-gradient(90deg, #d97706, #e11d48); color:#fff; border:none; text-shadow:0 1px 0 rgba(0,0,0,.12); }
    .btn-quiet   { background: rgba(255,255,255,.85); color: var(--nav-text); }
    .dark .btn-quiet { background: rgba(255,255,255,.08); color: var(--nav-text-dark); border-color: rgba(255,255,255,.06); }

    .header {
      position: fixed; inset-inline: 0; top: 0; z-index: 50;
      backdrop-filter: saturate(160%) blur(10px);
      -webkit-backdrop-filter: saturate(160%) blur(10px);
      border-bottom: 1px solid var(--header-ring);
      box-shadow: 0 10px 28px rgba(15,23,42,.20);
      background: var(--header-bg-light);
    }
    .dark .header { background: var(--header-bg-dark); border-bottom-color: rgba(255,255,255,.06); }

    .nav-link {
      position: relative;
      padding: .5rem .75rem;
      border-radius: .9rem;
      font-weight: 600;
      color: var(--nav-text);
      background: rgba(255,255,255,.85);
      border: 1px solid rgba(120,53,15,.12);
      transition: color .15s ease, background-color .15s ease, box-shadow .15s ease, transform .08s ease;
      box-shadow: 0 2px 10px rgba(0,0,0,.04);
    }
    .nav-link:hover { color: var(--nav-text-hover); transform: translateY(-1px); box-shadow: 0 10px 20px rgba(0,0,0,.08); }
    .nav-link--active { background: linear-gradient(180deg, rgba(255,255,255,.95), rgba(255,255,255,.85)); color: var(--nav-text-active); }
    .dark .nav-link { color: var(--nav-text-dark); background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.06); }
    .dark .nav-link:hover { color: var(--nav-text-dark-hover); }
    .dark .nav-link--active { background: rgba(255,255,255,.14); color: var(--nav-text-dark-active); }

    .nav-link::after{
      content:""; position:absolute; left:.75rem; right:.75rem; bottom:.45rem;
      height:2px; border-radius:2px; background: currentColor; opacity:0; transform: scaleX(.6);
      transition: transform .18s ease, opacity .18s ease;
    }
    .nav-link:hover::after{ opacity:.45; transform: scaleX(1); }
    .nav-link--active::after{ opacity:.7; }

    .card-elevated { box-shadow: var(--shadow-strong); }
    .card-elevated:hover { box-shadow: var(--shadow-strong-hover); }

    .content-offset { padding-top: 5.0rem; } /* ~header height */
  </style>
</head>

<body class="min-h-screen antialiased relative overflow-x-hidden bg-gradient-to-br from-amber-50 via-orange-50 to-rose-50 grainy">
  {{-- Background blobs (behind everything) --}}
  <div class="pointer-events-none absolute -top-24 -left-24 h-[26rem] w-[26rem] rounded-full blur-3xl opacity-20 bg-gradient-to-br from-amber-300 to-rose-300 -z-10"></div>
  <div class="pointer-events-none absolute -bottom-28 -right-24 h-[30rem] w-[30rem] rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-orange-300 to-pink-300 -z-10"></div>

  {{-- Topbar --}}
  <header class="header">
    <div class="mx-auto max-w-7xl px-4 py-3 flex items-center justify-between gap-3 flex-wrap">
      {{-- Brand --}}
      <a href="{{ Route::has('dashboard') ? route('dashboard') : (Route::has('home') ? route('home') : url('/')) }}" class="flex items-center gap-2">
        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-rose-600 text-white shadow">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5C6.5 5 2 9.5 2 12s4.5 7 10 7 10-4.5 10-7-4.5-7-10-7zm0 11a4 4 0 110-8 4 4 0 010 8z"/></svg>
        </span>
        <span class="font-extrabold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-amber-700 via-orange-900 to-rose-700 dark:from-amber-300 dark:via-amber-200 dark:to-rose-200">
          {{ config('app.name', 'Chokh-e-Dekha') }}
        </span>
      </a>

      {{-- Nav --}}
      <nav class="flex items-center gap-2 text-sm flex-wrap">
        @if (Route::has('dashboard'))
          <a href="{{ route('dashboard') }}"
             class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link--active' : '' }}">Dashboard</a>
        @endif

        @if (Route::has('reports.my'))
          <a href="{{ route('reports.my') }}"
             class="nav-link {{ request()->routeIs('reports.my') ? 'nav-link--active' : '' }}">My Reports</a>
        @endif

        @if (Route::has('reports.index'))
          <a href="{{ route('reports.index') }}"
             class="nav-link {{ request()->routeIs('reports.index') ? 'nav-link--active' : '' }}">All Issues</a>
        @endif

        @stack('nav-actions')
      </nav>

      {{-- Right controls --}}
      <div class="flex items-center gap-2 flex-wrap">
        {{-- Theme toggle --}}
        <button id="themeToggle" type="button" class="btn btn-quiet" aria-label="Toggle dark mode">
          <svg class="h-4 w-4 hidden dark:block" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M12 4V2m0 20v-2m8-8h2M2 12h2m13.657-6.343l1.414-1.414M4.929 19.071l1.414-1.414m0-11.314L4.93 4.929M19.071 19.071l-1.414-1.414M12 8a4 4 0 100 8 4 4 0 000-8z"/>
          </svg>
          <svg class="h-4 w-4 dark:hidden" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M21 12.79A9 9 0 1111.21 3a7 7 0 109.79 9.79z"/>
          </svg>
          <span class="text-sm font-medium dark:hidden">Dark</span>
          <span class="text-sm font-medium hidden dark:inline">Light</span>
        </button>

        {{-- Primary CTA --}}
        @if(Route::has('report.create'))
          <a href="{{ route('report.create') }}" class="btn btn-primary">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11 5h2v14h-2zM5 11h14v2H5z"/></svg>
            New Report
          </a>
        @endif

        @stack('header-actions')

        {{-- Auth-aware user controls --}}
        @auth
          <div class="relative">
            <button id="userBtn" class="btn btn-quiet">
              <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
              <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M5.5 7l4.5 5 4.5-5"/></svg>
            </button>
            <div id="userDrop" class="hidden absolute right-0 mt-2 w-44 rounded-xl bg-white ring-1 ring-amber-900/10 shadow-2xl p-1 dark:bg-slate-900 dark:ring-white/10">
              @if (Route::has('profile.edit'))
                <a href="{{ route('profile.edit') }}" class="block px-3 py-2 text-sm rounded-lg hover:bg-amber-50 dark:hover:bg-white/10">Profile</a>
              @endif
              @if (Route::has('logout'))
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button class="w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-rose-50 text-rose-700 dark:hover:bg-rose-500/10 dark:text-rose-300">Logout</button>
                </form>
              @endif
            </div>
          </div>
        @endauth

        @guest
          @if (Route::has('login'))
            <a class="btn btn-quiet" href="{{ route('login') }}">Login</a>
          @endif
          @if (Route::has('register'))
            <a class="btn btn-primary" href="{{ route('register') }}">Register</a>
          @endif
        @endguest
      </div>
    </div>
  </header>

  {{-- Main (offset for fixed header) --}}
  <main class="mx-auto max-w-7xl px-4 py-6 relative z-10 content-offset">
    @if (session('status'))
      <div class="mb-4 rounded-xl px-4 py-3 bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200 card-elevated dark:bg-emerald-900/20 dark:text-emerald-100 dark:ring-emerald-800/40">
        {{ session('status') }}
      </div>
    @endif

    @hasSection('actions')
      <section class="mb-4 flex items-center gap-2 flex-wrap">
        @yield('actions')
      </section>
    @endif

    @yield('content')
  </main>

  {{-- ===== Global helpers ===== --}}
  <script>
    // CSRF helper + AJAX wrapper
    window.csrf = () => (document.querySelector('meta[name="csrf-token"]')?.content || '');
    window.ajax = (url, options = {}) => {
      const headers = new Headers(options.headers || {});
      if (!headers.has('X-CSRF-TOKEN')) headers.set('X-CSRF-TOKEN', window.csrf());
      if (!headers.has('X-Requested-With')) headers.set('X-Requested-With', 'XMLHttpRequest');
      if (!headers.has('Accept')) headers.set('Accept', 'application/json');
      return fetch(url, { ...options, headers });
    };

    // Event delegation helpers
    window.closestData = (target, name) => target.closest(`[data-${name}]`);
    window.onClick = (selector, handler, opts = {capture:false}) => {
      document.addEventListener('click', (e) => {
        const el = e.target.closest(selector);
        if (el) handler(e, el);
      }, opts);
    };

    // Dropdown
    const initUserDropdown = () => {
      const btn  = document.getElementById('userBtn');
      const drop = document.getElementById('userDrop');
      if (!btn || !drop) return;
      btn.addEventListener('click', () => drop.classList.toggle('hidden'));
      document.addEventListener('click', (e) => {
        if (!btn.contains(e.target) && !drop.contains(e.target)) drop.classList.add('hidden');
      }, { capture: true });
    };

    // Theme toggle
    const initThemeToggle = () => {
      const btn = document.getElementById('themeToggle');
      if (!btn) return;
      const withTrans = (fn)=>{ const el=document.documentElement; el.style.transition='background-color .25s,color .25s'; fn(); setTimeout(()=>el.style.transition='',300); };
      btn.addEventListener('click', () => {
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
            const html = document.documentElement;
            e.matches ? html.classList.add('dark') : html.classList.remove('dark');
          }
        });
      } catch {}
    };

    const initLayout = () => { initUserDropdown(); initThemeToggle(); };
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initLayout, {once:true});
    } else {
      initLayout();
    }
    window.addEventListener?.('turbo:load', initLayout);
  </script>

  {{-- Page-level scripts --}}
  @stack('scripts')
</body>
</html>
