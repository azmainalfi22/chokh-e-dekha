<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <!-- Theme boot (prevents flash) -->
  <script>
    (function () {
      const saved = localStorage.getItem('theme'); // 'light' | 'dark' | null
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      const shouldDark = saved ? (saved === 'dark') : prefersDark;
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
      --page-light: #fffaf5;
      --page-dark:  #0b0e12;
      --page-dark-grad-1: rgba(255,179,0,.06);
      --page-dark-grad-2: rgba(244,63,94,.07);
    }
    body{
      min-height: 100vh;
      background:
        radial-gradient(1200px 400px at -10% -10%, rgba(251,191,36,.12), transparent 40%),
        radial-gradient(1000px 300px at 110% 110%, rgba(244,63,94,.10), transparent 45%),
        var(--page-light);
      color: #0f172a; /* keep readable text in both themes */
    }
    .dark body{
      background:
        radial-gradient(1200px 400px at -10% -10%, var(--page-dark-grad-1), transparent 40%),
        radial-gradient(1000px 300px at 110% 110%, var(--page-dark-grad-2), transparent 45%),
        var(--page-dark);
    }
  </style>
</head>
<body class="min-h-screen antialiased relative overflow-x-hidden bg-gradient-to-br from-amber-50 via-orange-50 to-rose-50 grain-overlay">

  {{-- Background blobs --}}
  <div class="pointer-events-none absolute -top-28 -left-24 h-[28rem] w-[28rem] rounded-full blur-3xl opacity-20 bg-gradient-to-br from-amber-300 to-rose-300"></div>
  <div class="pointer-events-none absolute -bottom-32 -right-24 h-[32rem] w-[32rem] rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-orange-300 to-pink-300"></div>

  {{-- Top bar --}}
  <header class="w-full sticky top-0 z-40 backdrop-blur
                 bg-gradient-to-r from-amber-100 via-orange-100 to-rose-200
                 ring-1 ring-amber-900/20 shadow-md">
    <div class="mx-auto max-w-7xl px-4 py-3 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <button id="sidebarToggle" class="md:hidden inline-flex items-center justify-center h-9 w-9 rounded-lg ring-1 ring-amber-900/10 bg-white hover:shadow transition" aria-label="Toggle sidebar">
          <svg class="h-5 w-5 text-amber-900/80" viewBox="0 0 24 24" fill="currentColor"><path d="M4 7h16v2H4zM4 15h16v2H4z"/></svg>
        </button>
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
          <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-rose-600 text-white shadow">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5C6.5 5 2 9.5 2 12s4.5 7 10 7 10-4.5 10-7-4.5-7-10-7zm0 11a4 4 0 110-8 4 4 0 010 8z"/></svg>
          </span>
          <span class="font-extrabold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-amber-700 via-orange-700 to-rose-700">
            {{ config('app.name', 'Chokh-e-Dekha') }} <span class="opacity-70">Admin</span>
          </span>
        </a>
      </div>

      <div class="flex items-center gap-2">
        <!-- Theme toggle -->
        <button id="themeToggle" type="button"
                class="inline-flex items-center gap-2 rounded-xl px-3 py-2 ring-1 ring-amber-900/10 bg-white/80 dark:bg-white/90 text-amber-900/90 hover:shadow transition"
                aria-label="Toggle dark mode">
          <!-- sun shows in dark -->
          <svg class="h-4 w-4 hidden dark:block" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M12 4V2m0 20v-2m8-8h2M2 12h2m13.657-6.343l1.414-1.414M4.929 19.071l1.414-1.414m0-11.314L4.93 4.929M19.071 19.071l-1.414-1.414M12 8a4 4 0 100 8 4 4 0 000-8z"/>
          </svg>
          <!-- moon shows in light -->
          <svg class="h-4 w-4 dark:hidden" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M21 12.79A9 9 0 1111.21 3a7 7 0 109.79 9.79z"/>
          </svg>
          <span class="text-sm font-medium dark:hidden">Dark</span>
          <span class="text-sm font-medium hidden dark:inline">Light</span>
        </button>

        <a href="{{ route('admin.reports.index') }}" class="hidden sm:inline-flex items-center gap-2 rounded-xl px-3 py-2 text-white font-medium bg-gradient-to-r from-amber-600 to-rose-600 shadow hover:shadow-md hover:-translate-y-0.5 transition">
          Reports
        </a>

        {{-- User dropdown --}}
        <div class="relative">
          <button id="userMenuBtn" class="inline-flex items-center gap-2 rounded-xl px-3 py-2 bg-white ring-1 ring-amber-900/10 text-amber-900/90 shadow hover:shadow-md transition">
            <span class="hidden sm:inline">{{ auth()->user()->name ?? 'Admin' }}</span>
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M5.5 7l4.5 5 4.5-5"/></svg>
          </button>
          <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 rounded-xl bg-white ring-1 ring-amber-900/10 shadow-lg p-1">
            <a href="{{ route('admin.profile.edit') }}" class="block px-3 py-2 text-sm rounded-lg hover:bg-amber-50">Profile</a>
            <a href="{{ route('admin.users.index') }}" class="block px-3 py-2 text-sm rounded-lg hover:bg-amber-50">Manage Users</a>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button class="w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-rose-50 text-rose-700">Logout</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </header>

  {{-- Shell --}}
  <div class="mx-auto max-w-7xl px-4 py-6 grid grid-cols-1 md:grid-cols-[240px_1fr] gap-6">
    {{-- Sidebar --}}
    <aside id="sidebar" class="md:sticky md:top-20 h-max md:h-[calc(100vh-7rem)] md:overflow-y-auto bg-white/80 backdrop-blur rounded-2xl ring-1 ring-white/60 shadow p-4 md:block hidden">
      <nav class="space-y-1 text-sm">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-3 py-2 rounded-xl {{ request()->routeIs('admin.dashboard') ? 'bg-amber-100 text-amber-900' : 'text-amber-900/80 hover:bg-amber-50' }}">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3l9 8h-3v9H6v-9H3z"/></svg>
          Dashboard
        </a>
        <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-xl {{ request()->routeIs('admin.reports.*') ? 'bg-amber-100 text-amber-900' : 'text-amber-900/80 hover:bg-amber-50' }}">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M5 4h14v2H5zM5 8h14v12H5z"/></svg>
          Reports
        </a>
        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-xl {{ request()->routeIs('admin.users.*') ? 'bg-amber-100 text-amber-900' : 'text-amber-900/80 hover:bg-amber-50' }}">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11a3 3 0 100-6 3 3 0 000 6zM8 11a3 3 0 100-6 3 3 0 000 6zm8 2c-2.2 0-6 1.1-6 3.3V19h12v-2.7c0-2.2-3.8-3.3-6-3.3zM8 13c-2.3 0-6 1.1-6 3.3V19h6v-2.7c0-1.1.7-2 2-2H8z"/></svg>
          Users
        </a>
        <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-2 px-3 py-2 rounded-xl {{ request()->routeIs('admin.profile.*') ? 'bg-amber-100 text-amber-900' : 'text-amber-900/80 hover:bg-amber-50' }}">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zm7 2H5a2 2 0 00-2 2v5h18v-5a2 2 0 00-2-2z"/></svg>
          Profile
        </a>
      </nav>

      <div class="mt-6 p-[2px] rounded-2xl bg-gradient-to-br from-amber-400 via-orange-400 to-rose-400">
        <div class="rounded-2xl bg-white/85 backdrop-blur p-4 text-xs text-amber-900/80">
          <div class="font-semibold mb-1">Quick tip</div>
          Use the “Reports” page to filter by city and status.
        </div>
      </div>
    </aside>

    {{-- Main --}}
    <main class="min-w-0 relative z-10">
      {{-- Global flashes --}}
      @php
        $flashSuccess = session('success');
        $flashError   = session('error') ?? ($errors->any() ? 'There were some problems with your request.' : null);
        $flashStatus  = session('status');
      @endphp
      @if($flashSuccess || $flashError || $flashStatus)
        <div class="mb-4 space-y-2">
          @if($flashSuccess)
            <div class="rounded-xl px-4 py-3 bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200">{{ $flashSuccess }}</div>
          @endif
          @if($flashError)
            <div class="rounded-xl px-4 py-3 bg-rose-50 text-rose-800 ring-1 ring-rose-200">
              {{ $flashError }}
              @if($errors->any())
                <ul class="mt-2 list-disc pl-5 text-sm">
                  @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
              @endif
            </div>
          @endif
          @if($flashStatus)
            <div class="rounded-xl px-4 py-3 bg-amber-50 text-amber-900 ring-1 ring-amber-200">{{ $flashStatus }}</div>
          @endif
        </div>
      @endif

      {{-- Page header slots --}}
      @if(View::hasSection('page_title') || View::hasSection('page_actions'))
        <div class="mb-4 flex items-center justify-between gap-3">
          <div>
            @hasSection('page_title')
              <h1 class="text-xl sm:text-2xl font-extrabold bg-clip-text text-transparent
                         bg-gradient-to-r from-amber-700 via-orange-700 to-rose-700">
                @yield('page_title')
              </h1>
            @endif
            @hasSection('page_subtitle')
              <p class="text-sm text-amber-900/70 mt-0.5">@yield('page_subtitle')</p>
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
      function withThemeTransition(fn) {
        const el = document.documentElement;
        el.style.transition = 'background-color .25s ease, color .25s ease';
        fn();
        setTimeout(() => { el.style.transition = ''; }, 300);
      }

      const btn = document.getElementById('themeToggle');
      if (btn) {
        btn.addEventListener('click', () => {
          const html = document.documentElement;
          const isDark = html.classList.contains('dark');
          withThemeTransition(() => {
            if (isDark) {
              html.classList.remove('dark');
              localStorage.setItem('theme', 'light');
            } else {
              html.classList.add('dark');
              localStorage.setItem('theme', 'dark');
            }
          });
        });
      }

      // Follow OS if no manual choice
      try {
        const mq = window.matchMedia('(prefers-color-scheme: dark)');
        mq.addEventListener('change', (e) => {
          if (!localStorage.getItem('theme')) {
            withThemeTransition(() => {
              document.documentElement.classList.toggle('dark', e.matches);
            });
          }
        });
      } catch {}
    })();
  </script>
</body>
</html>
