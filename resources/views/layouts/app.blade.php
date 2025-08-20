<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <!-- Theme boot (no flash) -->
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
  <title>@yield('title', 'Dashboard') • {{ config('app.name', 'Chokh-e-Dekha') }}</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @stack('styles')

  <style>
    :root{
      --page-light: #fffaf5;          /* warm light */
      --page-dark:  #0b0e12;          /* deep slate/ink */
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
  <div class="pointer-events-none absolute -top-24 -left-24 h-[26rem] w-[26rem] rounded-full blur-3xl opacity-20 bg-gradient-to-br from-amber-300 to-rose-300"></div>
  <div class="pointer-events-none absolute -bottom-28 -right-24 h-[30rem] w-[30rem] rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-orange-300 to-pink-300"></div>

  {{-- Topbar --}}
  <header class="w-full sticky top-0 z-40 backdrop-blur
                 bg-gradient-to-r from-amber-100 via-orange-100 to-rose-200
                 ring-1 ring-amber-900/20 shadow-md">
    <div class="mx-auto max-w-7xl px-4 py-3 flex items-center justify-between">
      <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-rose-600 text-white shadow">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5C6.5 5 2 9.5 2 12s4.5 7 10 7 10-4.5 10-7-4.5-7-10-7zm0 11a4 4 0 110-8 4 4 0 010 8z"/></svg>
        </span>
        <span class="font-extrabold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-amber-700 via-orange-700 to-rose-700">
          {{ config('app.name', 'Chokh-e-Dekha') }}
        </span>
      </a>

      <nav class="hidden md:flex items-center gap-2 text-sm">
        <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-xl {{ request()->routeIs('dashboard') ? 'bg-amber-100 text-amber-900' : 'text-amber-900/80 hover:bg-amber-50' }}">Dashboard</a>
        <a href="{{ route('reports.my') }}" class="px-3 py-2 rounded-xl {{ request()->routeIs('reports.my') ? 'bg-amber-100 text-amber-900' : 'text-amber-900/80 hover:bg-amber-50' }}">My Reports</a>
        <a href="{{ route('reports.index') }}" class="px-3 py-2 rounded-xl {{ request()->routeIs('reports.index') ? 'bg-amber-100 text-amber-900' : 'text-amber-900/80 hover:bg-amber-50' }}">All Issues</a>
      </nav>

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

        <a href="{{ route('report.create') }}" class="hidden sm:inline-flex items-center gap-2 rounded-xl px-3 py-2 text-white font-medium bg-gradient-to-r from-amber-600 to-rose-600 shadow hover:shadow-md hover:-translate-y-0.5 transition">
          New Report
        </a>

        {{-- User dropdown --}}
        <div class="relative">
          <button id="userBtn" class="inline-flex items-center gap-2 rounded-xl px-3 py-2 bg-white ring-1 ring-amber-900/10 text-amber-900/90 shadow hover:shadow-md transition">
            <span class="hidden sm:inline">{{ auth()->user()->name ?? 'User' }}</span>
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M5.5 7l4.5 5 4.5-5"/></svg>
          </button>
          <div id="userDrop" class="hidden absolute right-0 mt-2 w-44 rounded-xl bg-white ring-1 ring-amber-900/10 shadow-lg p-1">
            <a href="{{ route('profile.edit') }}" class="block px-3 py-2 text-sm rounded-lg hover:bg-amber-50">Profile</a>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button class="w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-rose-50 text-rose-700">Logout</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </header>

  {{-- Main --}}
  <main class="mx-auto max-w-7xl px-4 py-6 relative z-10">
    @if (session('status'))
      <div class="mb-4 rounded-xl px-4 py-3 bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200">
        {{ session('status') }}
      </div>
    @endif
    @yield('content')
  </main>

  @stack('scripts')

  <script>
    // User dropdown
    (function () {
      const btn = document.getElementById('userBtn');
      const drop= document.getElementById('userDrop');
      if (btn && drop) {
        btn.addEventListener('click', () => drop.classList.toggle('hidden'));
        document.addEventListener('click', (e) => {
          if (!btn.contains(e.target) && !drop.contains(e.target)) drop.classList.add('hidden');
        }, { capture: true });
      }
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
