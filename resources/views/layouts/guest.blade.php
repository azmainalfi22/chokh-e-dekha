<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Chokh-e-Dekha'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    {{-- Stronger dotted grain for guest pages (keeps gradients visible) --}}
    <style>
      .grain-overlay-strong::before{
        content:""; position:fixed; inset:0; pointer-events:none; z-index:0;
        background-image: radial-gradient(rgba(17,24,39,.08) 1px, transparent 2px);
        background-size: 5px 6px; mix-blend-mode: multiply; opacity: 10;
      }
      @media (max-width: 480px){
        .grain-overlay-strong::before{ background-size: 8px 8px; opacity: .32; }
      }
    </style>
</head>
<body class="min-h-screen antialiased relative overflow-x-hidden bg-gradient-to-br from-amber-50 via-orange-50 to-rose-50 grain-overlay-strong">

    {{-- Ambient color blobs --}}
    <div class="pointer-events-none absolute -top-28 -left-24 h-[28rem] w-[28rem] rounded-full blur-3xl opacity-40 bg-gradient-to-br from-amber-300 to-rose-300"></div>
    <div class="pointer-events-none absolute -bottom-32 -right-24 h-[32rem] w-[32rem] rounded-full blur-3xl opacity-40 bg-gradient-to-tr from-orange-300 to-pink-300"></div>

    {{-- Skip to content (a11y) --}}
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:z-50 focus:top-3 focus:left-3 focus:px-3 focus:py-2 focus:rounded-lg focus:bg-white focus:ring-2 focus:ring-amber-400">
      Skip to content
    </a>

    {{-- Header --}}
{{-- Header --}}
<header class="w-full sticky top-0 z-40 backdrop-blur 
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

        <nav class="flex items-center gap-3 text-sm">
            @auth
                <a href="{{ auth()->user()->is_admin ? route('admin.dashboard') : route('dashboard') }}"
                   class="px-3 py-2 rounded-xl bg-white/80 ring-1 ring-amber-900/10 text-amber-900/90 shadow hover:shadow-md">
                   Go to app
                </a>
            @else
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="px-3 py-2 rounded-xl text-amber-900/90 hover:bg-amber-100/60">
                        Sign in
                    </a>
                @endif
                @if (Route::has('register'))
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-white font-medium 
                              bg-gradient-to-r from-orange-600 via-amber-500 to-rose-600 
                              shadow hover:shadow-lg hover:-translate-y-0.5 transition">
                        Get started
                    </a>
                @endif
            @endauth
        </nav>
    </div>
</header>



    {{-- Main --}}
    <main id="main" class="relative z-10">
        <div class="mx-auto max-w-7xl px-4 py-10 md:py-16 grid md:grid-cols-2 gap-10 items-start">
            {{-- Hero / copy --}}
            <section class="order-2 md:order-1">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/70 ring-1 ring-white/60 shadow-sm backdrop-blur">
                    <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                    <span class="text-xs font-medium text-amber-700">Welcome</span>
                </div>

                <h1 class="mt-4 text-3xl md:text-5xl font-extrabold leading-tight tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-amber-700 via-orange-700 to-rose-700">
                    See better. Report smarter.
                </h1>
                <p class="mt-3 text-amber-900/80 max-w-xl">
                    Chokh‑e‑Dekha helps you capture, submit, and manage reports with clarity—warm UI, quick actions, zero fuss.
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-rose-600 text-white font-semibold shadow-lg hover:shadow-xl transition hover:-translate-y-0.5">
                            Create account
                        </a>
                    @endif
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/80 backdrop-blur ring-1 ring-amber-900/10 text-amber-900/90 shadow-md hover:shadow-lg transition hover:-translate-y-0.5">
                            Sign in
                        </a>
                    @endif
                </div>

                <ul class="mt-8 grid grid-cols-2 gap-3 max-w-md text-sm">
                    <li class="flex items-center gap-2 px-3 py-2 rounded-xl bg-white/70 ring-1 ring-white/60 shadow-sm">
                        <span class="h-2 w-2 rounded-full bg-rose-500"></span> Clean reporting
                    </li>
                    <li class="flex items-center gap-2 px-3 py-2 rounded-xl bg-white/70 ring-1 ring-white/60 shadow-sm">
                        <span class="h-2 w-2 rounded-full bg-amber-500"></span> Quick actions
                    </li>
                    <li class="flex items-center gap-2 px-3 py-2 rounded-xl bg-white/70 ring-1 ring-white/60 shadow-sm">
                        <span class="h-2 w-2 rounded-full bg-orange-500"></span> Make Your City Better
                    </li>
                    <li class="flex items-center gap-2 px-3 py-2 rounded-xl bg-white/70 ring-1 ring-white/60 shadow-sm">
                        <span class="h-2 w-2 rounded-full bg-pink-500"></span> Citizen’s Platform
                    </li>
                </ul>
            </section>

            {{-- Auth card slot (login/register/forgot) --}}
            <section class="order-1 md:order-2">
                <div class="p-[2px] rounded-2xl bg-gradient-to-br from-amber-400 via-orange-400 to-rose-400 shadow-[0_20px_60px_-15px_rgba(244,114,182,0.35)]">
                    <div class="bg-white/85 backdrop-blur-xl rounded-2xl p-6 md:p-8 ring-1 ring-white/60 shadow-2xl">
                        @yield('slot') {{-- child auth views drop their form here --}}
                        <div class="mt-6 text-center text-xs text-amber-900/60">
                            By continuing you agree to our
                            <a href="{{ url('/terms') }}" class="underline decoration-amber-400 hover:text-amber-800">Terms</a>
                            &amp;
                            <a href="{{ url('/privacy') }}" class="underline decoration-rose-400 hover:text-rose-800">Privacy Policy</a>.
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="py-8">
        <div class="mx-auto max-w-7xl px-4 text-xs text-amber-900/60 flex flex-wrap gap-4 justify-between">
            <span>© {{ date('Y') }} {{ config('app.name', 'Chokh-e-Dekha') }}</span>
            <div class="flex gap-4">
                <a href="{{ url('/about') }}" class="hover:underline">About</a>
                <a href="{{ url('/contact') }}" class="hover:underline">Contact</a>
                <a href="{{ url('/help') }}" class="hover:underline">Help</a>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
