<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') • {{ config('app.name', 'Chokh-e-Dekha') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen antialiased relative overflow-x-hidden bg-gradient-to-br from-amber-50 via-orange-50 to-rose-50 grain-overlay">


    {{-- Background blobs --}}
    <div class="pointer-events-none absolute -top-24 -left-24 h-[26rem] w-[26rem] rounded-full blur-3xl opacity-20 bg-gradient-to-br from-amber-300 to-rose-300"></div>
    <div class="pointer-events-none absolute -bottom-28 -right-24 h-[30rem] w-[30rem] rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-orange-300 to-pink-300"></div>

    {{-- Topbar --}}
    <header class= 

"w-full sticky top-0 z-40 backdrop-blur 
               bg-gradient-to-r from-amber-100 via-orange-100 to-rose-200
               ring-1 ring-amber-900/20 shadow-md">

        <div class="mx-auto max-w-7xl px-4 py-3 flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-rose-600 text-white shadow">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5C6.5 5 2 9.5 2 12s4.5 7 10 7 10-4.5 10-7-4.5-7-10-7zm0 11a 4 4 0 110-8 4 4 0 010 8z"/></svg>
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

    {{-- Main container --}}
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
    </script>
</body>
</html>
