<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') • {{ config('app.name', 'Chokh-e-Dekha') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen antialiased relative overflow-hidden bg-gradient-to-br from-amber-50 via-orange-50 to-rose-50">

    {{-- Background blobs --}}
    <div class="pointer-events-none absolute -top-28 -left-24 h-[28rem] w-[28rem] rounded-full blur-3xl opacity-20
                bg-gradient-to-br from-amber-300 to-rose-300"></div>
    <div class="pointer-events-none absolute -bottom-32 -right-24 h-[32rem] w-[32rem] rounded-full blur-3xl opacity-20
                bg-gradient-to-tr from-orange-300 to-pink-300"></div>

    {{-- Top bar --}}
    <header class="sticky top-0 z-40 backdrop-blur bg-white/70 ring-1 ring-white/60 shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button id="sidebarToggle" class="md:hidden inline-flex items-center justify-center h-9 w-9 rounded-lg
                        ring-1 ring-amber-900/10 bg-white hover:shadow transition">
                    {{-- menu --}}
                    <svg class="h-5 w-5 text-amber-900/80" viewBox="0 0 24 24" fill="currentColor"><path d="M4 7h16v2H4zM4 15h16v2H4z"/></svg>
                </button>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl
                                 bg-gradient-to-br from-amber-500 to-rose-600 text-white shadow">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5C6.5 5 2 9.5 2 12s4.5 7 10 7 10-4.5 10-7-4.5-7-10-7zm0 11a4 4 0 110-8 4 4 0 010 8z"/></svg>
                    </span>
                    <span class="font-extrabold bg-clip-text text-transparent
                                 bg-gradient-to-r from-amber-700 via-orange-700 to-rose-700">
                        {{ config('app.name', 'Chokh-e-Dekha') }} Admin
                    </span>
                </a>
            </div>

            <div class="flex items-center gap-2">
                {{-- Quick action --}}
                <a href="{{ route('admin.reports.index') }}"
                   class="hidden sm:inline-flex items-center gap-2 rounded-xl px-3 py-2 text-white font-medium
                          bg-gradient-to-r from-amber-600 to-rose-600 shadow hover:shadow-md hover:-translate-y-0.5 transition">
                    Reports
                </a>

                {{-- User dropdown (simple) --}}
                <div class="relative">
                    <button id="userMenuBtn" class="inline-flex items-center gap-2 rounded-xl px-3 py-2
                            bg-white ring-1 ring-amber-900/10 text-amber-900/90 shadow hover:shadow-md transition">
                        <span class="hidden sm:inline">{{ auth()->user()->name ?? 'Admin' }}</span>
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M5.5 7l4.5 5 4.5-5"/></svg>
                    </button>
                    <div id="userMenu"
                         class="hidden absolute right-0 mt-2 w-44 rounded-xl bg-white ring-1 ring-amber-900/10 shadow-lg p-1">
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

    {{-- Shell: sidebar + content --}}
    <div class="mx-auto max-w-7xl px-4 py-6 grid grid-cols-1 md:grid-cols-[240px_1fr] gap-6">
        {{-- Sidebar --}}
        <aside id="sidebar"
               class="md:sticky md:top-20 h-max md:h-[calc(100vh-7rem)] md:overflow-y-auto
                      bg-white/80 backdrop-blur rounded-2xl ring-1 ring-white/60 shadow
                      p-4 md:block hidden">
            <nav class="space-y-1 text-sm">
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-2 px-3 py-2 rounded-xl
                          {{ request()->routeIs('admin.dashboard') ? 'bg-amber-100 text-amber-900' : 'text-amber-900/80 hover:bg-amber-50' }}">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3l9 8h-3v9H6v-9H3z"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.reports.index') }}"
                   class="flex items-center gap-2 px-3 py-2 rounded-xl
                          {{ request()->routeIs('admin.reports.*') ? 'bg-amber-100 text-amber-900' : 'text-amber-900/80 hover:bg-amber-50' }}">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M5 4h14v2H5zM5 8h14v12H5z"/></svg>
                    Reports
                </a>
                <a href="{{ route('admin.users.index') }}"
                   class="flex items-center gap-2 px-3 py-2 rounded-xl
                          {{ request()->routeIs('admin.users.*') ? 'bg-amber-100 text-amber-900' : 'text-amber-900/80 hover:bg-amber-50' }}">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.7 0 3-1.3 3-3s-1.3-3-3-3-3 1.3-3 3 1.3 3 3 3zM8 11c1.7 0 3-1.3 3-3S9.7 5 8 5 5 6.3 5 8s1.3 3 3 3zm8 2c-2.2 0-6 1.1-6 3.3V19h12v-2.7c0-2.2-3.8-3.3-6-3.3zM8 13c-2.3 0-6 1.1-6 3.3V19h6v-2.7c0-1.1.7-2 2-2h-2z"/></svg>
                    Users
                </a>
                <a href="{{ route('admin.profile.edit') }}"
                   class="flex items-center gap-2 px-3 py-2 rounded-xl
                          {{ request()->routeIs('admin.profile.*') ? 'bg-amber-100 text-amber-900' : 'text-amber-900/80 hover:bg-amber-50' }}">
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

        {{-- Main content --}}
        <main class="min-w-0">
            {{-- Flash --}}
            @if (session('status'))
                <div class="mb-4 rounded-xl px-4 py-3 bg-amber-50 text-amber-900 ring-1 ring-amber-200">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')

    <script>
    // Mobile sidebar + dropdown
    (function () {
        const sidebar = document.getElementById('sidebar');
        const toggle  = document.getElementById('sidebarToggle');
        const userBtn = document.getElementById('userMenuBtn');
        const userMenu= document.getElementById('userMenu');

        if (toggle && sidebar) {
            toggle.addEventListener('click', () => {
                sidebar.classList.toggle('hidden');
            });
        }
        if (userBtn && userMenu) {
            userBtn.addEventListener('click', () => userMenu.classList.toggle('hidden'));
            document.addEventListener('click', (e) => {
                if (!userBtn.contains(e.target) && !userMenu.contains(e.target)) {
                    userMenu.classList.add('hidden');
                }
            });
        }
    })();
    </script>
</body>
</html>
