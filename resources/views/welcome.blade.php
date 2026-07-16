<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Chokh-e-Dekha — Bangladesh's civic reporting platform. Submit geo-tagged reports, track resolutions, and hold local government accountable.">
  <title>Chokh-e-Dekha — People's Civic Platform | চোখে দেখা</title>
  <script>
    if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches))
      document.documentElement.classList.add('dark');
  </script>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-white dark:bg-slate-950 text-slate-800 dark:text-slate-100">

{{-- ── Navbar ──────────────────────────────────────────────────────── --}}
<header class="sticky top-0 z-50 bg-white/95 dark:bg-slate-900/95 backdrop-blur border-b border-slate-200 dark:border-slate-800">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
    {{-- Logo --}}
    <a href="/" class="flex items-center gap-2.5">
      <div class="w-9 h-9 rounded-xl bg-emerald-600 flex items-center justify-center text-white font-bold text-base shadow-sm">চ</div>
      <div class="leading-none">
        <div class="font-bold text-slate-900 dark:text-white text-base">Chokh-e-Dekha</div>
        <div class="text-[10px] text-slate-400 dark:text-slate-500 tracking-wide">চোখে দেখা</div>
      </div>
    </a>

    {{-- Desktop nav --}}
    <nav class="hidden md:flex items-center gap-1">
      <a href="#how-it-works" class="px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition">How It Works</a>
      <a href="#features" class="px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition">Features</a>
      <a href="{{ route('reports.index') }}" class="px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition">Browse Reports</a>
    </nav>

    {{-- Auth buttons --}}
    <div class="flex items-center gap-2">
      @auth
        <a href="{{ route('dashboard') }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-sm">
          Dashboard
        </a>
      @else
        <a href="{{ route('login') }}" class="px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition">Sign in</a>
        <a href="{{ route('register') }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-sm">
          Get Started
        </a>
      @endauth
    </div>
  </div>
</header>

{{-- ── Hero ─────────────────────────────────────────────────────────── --}}
<section class="relative overflow-hidden bg-slate-900 dark:bg-slate-950 text-white">
  {{-- Subtle background grid --}}
  <div class="absolute inset-0 opacity-[0.04]"
       style="background-image: linear-gradient(rgba(255,255,255,.6) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.6) 1px, transparent 1px); background-size: 40px 40px;"></div>
  {{-- Emerald glow blobs --}}
  <div class="absolute -top-32 -left-32 w-96 h-96 bg-emerald-600 rounded-full blur-[100px] opacity-20 pointer-events-none"></div>
  <div class="absolute -bottom-32 right-0 w-96 h-96 bg-amber-500 rounded-full blur-[100px] opacity-10 pointer-events-none"></div>

  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
    <div class="grid lg:grid-cols-2 gap-14 items-center">

      {{-- Left: headline + CTA --}}
      <div>
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-900/60 border border-emerald-700/50 text-emerald-300 text-xs font-semibold mb-6">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
          Bangladesh's National Civic Platform
        </div>

        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black leading-[1.08] mb-6">
          Your city.<br>
          Your voice.<br>
          <span class="text-emerald-400">Your power.</span>
        </h1>

        <p class="text-lg text-slate-300 leading-relaxed mb-8 max-w-lg">
          Report road damage, corruption, or public service failures — with a photo, a location pin, and your voice. We route it to the right authority and track every update.
        </p>

        <div class="flex flex-wrap gap-3 mb-10">
          <a href="{{ route('register') }}"
             class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-bold text-base
                    bg-emerald-500 text-white hover:bg-emerald-400 shadow-lg shadow-emerald-900/40 transition">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Submit a Report
          </a>
          <a href="{{ route('reports.index') }}"
             class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-semibold text-base
                    bg-white/10 text-white hover:bg-white/15 border border-white/10 transition">
            Browse Public Reports →
          </a>
        </div>

        {{-- Live stats --}}
        @php
          $totalReports   = \App\Models\Report::count();
          $resolvedCount  = \App\Models\Report::where('status','resolved')->count();
          $totalUsers     = \App\Models\User::count();
        @endphp
        <div class="flex flex-wrap gap-8">
          <div>
            <div class="text-3xl font-black text-white counter" data-target="{{ $totalReports }}">{{ $totalReports }}</div>
            <div class="text-sm text-slate-400 mt-0.5">Reports filed</div>
          </div>
          <div>
            <div class="text-3xl font-black text-emerald-400 counter" data-target="{{ $resolvedCount }}">{{ $resolvedCount }}</div>
            <div class="text-sm text-slate-400 mt-0.5">Issues resolved</div>
          </div>
          <div>
            <div class="text-3xl font-black text-amber-400 counter" data-target="{{ $totalUsers }}">{{ $totalUsers }}</div>
            <div class="text-sm text-slate-400 mt-0.5">Active citizens</div>
          </div>
        </div>
      </div>

      {{-- Right: live activity feed --}}
      <div class="hidden lg:block">
        <div class="rounded-2xl bg-slate-800/70 border border-slate-700/60 backdrop-blur-sm overflow-hidden shadow-2xl">
          <div class="flex items-center justify-between px-5 py-4 border-b border-slate-700/50">
            <div class="text-sm font-semibold text-slate-200">Live Reports</div>
            <span class="flex items-center gap-1.5 text-xs text-emerald-400 font-medium">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
              Real-time
            </span>
          </div>
          <div class="divide-y divide-slate-700/40">
            @php $recent = \App\Models\Report::latest()->take(5)->get(['id','title','city_corporation','status','created_at']); @endphp
            @forelse($recent as $r)
              @php
                $color = match($r->status) {
                  'resolved'    => ['bg-emerald-900/60','text-emerald-300'],
                  'in_progress' => ['bg-blue-900/60','text-blue-300'],
                  'rejected'    => ['bg-red-900/60','text-red-300'],
                  default       => ['bg-amber-900/60','text-amber-300'],
                };
              @endphp
              <a href="{{ route('reports.show', $r) }}"
                 class="flex items-start gap-3 px-5 py-3.5 hover:bg-white/5 transition">
                <div class="w-8 h-8 rounded-lg bg-emerald-800/50 flex-shrink-0 flex items-center justify-center mt-0.5">
                  <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                  </svg>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="text-sm font-medium text-slate-200 truncate">{{ $r->title }}</div>
                  <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $color[0] }} {{ $color[1] }} font-medium">
                      {{ \Illuminate\Support\Str::headline($r->status) }}
                    </span>
                    <span class="text-xs text-slate-500">{{ $r->city_corporation ?: 'Unknown' }}</span>
                    <span class="text-xs text-slate-600">{{ $r->created_at->diffForHumans() }}</span>
                  </div>
                </div>
              </a>
            @empty
              <div class="px-5 py-10 text-center text-slate-500 text-sm">
                Be the first to report an issue.
              </div>
            @endforelse
          </div>
          <div class="px-5 py-3 border-t border-slate-700/50">
            <a href="{{ route('reports.index') }}" class="text-sm text-emerald-400 hover:text-emerald-300 font-medium transition">
              View all reports →
            </a>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

{{-- ── How it works ─────────────────────────────────────────────────── --}}
<section id="how-it-works" class="py-20 bg-slate-50 dark:bg-slate-900">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-14">
      <span class="text-xs font-bold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">Simple process</span>
      <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white mt-2">Report in 3 steps</h2>
      <p class="mt-3 text-slate-500 dark:text-slate-400 max-w-xl mx-auto">No bureaucracy. No phone trees. Just open the app, describe the problem, and we handle the rest.</p>
    </div>

    <div class="grid md:grid-cols-3 gap-8 relative">
      {{-- Connector line (desktop) --}}
      <div class="hidden md:block absolute top-10 left-1/3 right-1/3 h-0.5 bg-gradient-to-r from-emerald-200 via-emerald-400 to-emerald-200 dark:from-emerald-900 dark:via-emerald-600 dark:to-emerald-900"></div>

      @php
        $steps = [
          ['n'=>'01','icon'=>'M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z','title'=>'Capture the problem','desc'=>'Take a photo, write a short description, and choose a category. Works from any device.'],
          ['n'=>'02','icon'=>'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z','title'=>'Pin the location','desc'=>'Tap your location on the map or let GPS auto-detect. The exact pin reaches the right local authority.'],
          ['n'=>'03','icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z','title'=>'Track until resolved','desc'=>'Get notified on every status change — Pending → In Progress → Resolved. Full transparency, every step.'],
        ];
      @endphp
      @foreach($steps as $s)
        <div class="relative bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-8 shadow-sm">
          <div class="w-12 h-12 rounded-xl bg-emerald-600 flex items-center justify-center mb-5 shadow-sm">
            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="{{ $s['icon'] }}"/>
            </svg>
          </div>
          <div class="text-xs font-black text-emerald-600 dark:text-emerald-400 mb-2 tracking-widest">STEP {{ $s['n'] }}</div>
          <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">{{ $s['title'] }}</h3>
          <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">{{ $s['desc'] }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── 4 Pillars / Features ─────────────────────────────────────────── --}}
<section id="features" class="py-20 bg-white dark:bg-slate-950">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-14">
      <span class="text-xs font-bold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">Platform features</span>
      <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white mt-2">Everything a citizen needs</h2>
      <p class="mt-3 text-slate-500 dark:text-slate-400 max-w-xl mx-auto">Four pillars built for Bangladesh's civic ecosystem — from street-level reports to legal information access.</p>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
      @php
        $pillars = [
          ['color'=>'emerald','icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z','title'=>'Report & Track','desc'=>'Geo-tagged photo reports with real-time status tracking and SLA timers. No more lost complaints.'],
          ['color'=>'red','icon'=>'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z','title'=>'Anti-Corruption','desc'=>'Pseudonymous whistleblower submissions protected from retaliation. Report corruption safely.'],
          ['color'=>'violet','icon'=>'M3 6l3 1m0 0l-3 9a5 5 0 006 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5 5 0 006 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3','title'=>'RTI & Legal Aid','desc'=>'Generate Right to Information letters for any government ministry. Know your rights, use them.'],
          ['color'=>'amber','icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5 5 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z','title'=>'Governance Polls','desc'=>'Micro-surveys attached to local issues so communities can vote on priorities and see aggregated results.'],
        ];
        $bg = ['emerald'=>'bg-emerald-50 dark:bg-emerald-950/40','red'=>'bg-red-50 dark:bg-red-950/40','violet'=>'bg-violet-50 dark:bg-violet-950/40','amber'=>'bg-amber-50 dark:bg-amber-950/40'];
        $ic = ['emerald'=>'bg-emerald-600','red'=>'bg-red-600','violet'=>'bg-violet-600','amber'=>'bg-amber-500'];
        $bd = ['emerald'=>'border-emerald-200 dark:border-emerald-800','red'=>'border-red-200 dark:border-red-800','violet'=>'border-violet-200 dark:border-violet-800','amber'=>'border-amber-200 dark:border-amber-800'];
      @endphp
      @foreach($pillars as $p)
        <div class="rounded-2xl border {{ $bd[$p['color']] }} {{ $bg[$p['color']] }} p-7">
          <div class="w-11 h-11 rounded-xl {{ $ic[$p['color']] }} flex items-center justify-center mb-5 shadow-sm">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="{{ $p['icon'] }}"/>
            </svg>
          </div>
          <h3 class="font-bold text-slate-900 dark:text-white text-base mb-2">{{ $p['title'] }}</h3>
          <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed">{{ $p['desc'] }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── Impact Stats Banner ───────────────────────────────────────────── --}}
<section class="py-16 bg-emerald-600">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center text-white">
      @php
        $statsBar = [
          ['label'=>'Reports Filed','value'=> $totalReports,'suffix'=>'+'],
          ['label'=>'Issues Resolved','value'=> $resolvedCount,'suffix'=>'+'],
          ['label'=>'Active Citizens','value'=> $totalUsers,'suffix'=>'+'],
          ['label'=>'Cities Covered','value'=> \App\Models\Report::distinct('city_corporation')->whereNotNull('city_corporation')->count('city_corporation'),'suffix'=>''],
        ];
      @endphp
      @foreach($statsBar as $s)
        <div>
          <div class="text-4xl font-black counter" data-target="{{ $s['value'] }}">{{ $s['value'] }}{{ $s['suffix'] }}</div>
          <div class="text-emerald-100 text-sm font-medium mt-1">{{ $s['label'] }}</div>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── CTA ─────────────────────────────────────────────────────────── --}}
<section class="py-24 bg-slate-900 dark:bg-slate-950">
  <div class="max-w-3xl mx-auto px-4 text-center">
    <h2 class="text-4xl sm:text-5xl font-black text-white mb-5 leading-tight">
      Ready to make<br>your city better?
    </h2>
    <p class="text-slate-400 text-lg mb-10">
      Join thousands of citizens who are already reporting problems, demanding transparency, and driving change.
    </p>
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
      <a href="{{ route('register') }}"
         class="px-8 py-4 rounded-xl font-bold text-lg bg-emerald-500 text-white hover:bg-emerald-400 shadow-lg shadow-emerald-900/40 transition">
        Create Free Account
      </a>
      <a href="{{ route('reports.index') }}"
         class="px-8 py-4 rounded-xl font-bold text-lg bg-white/10 text-white hover:bg-white/15 border border-white/10 transition">
        Explore Reports
      </a>
    </div>
  </div>
</section>

{{-- ── Footer ──────────────────────────────────────────────────────── --}}
<footer class="bg-slate-950 border-t border-slate-800 py-12">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid md:grid-cols-4 gap-10 mb-10">
      <div class="md:col-span-2">
        <div class="flex items-center gap-2.5 mb-4">
          <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center text-white font-bold text-sm">চ</div>
          <span class="font-bold text-white text-base">Chokh-e-Dekha</span>
        </div>
        <p class="text-sm text-slate-400 leading-relaxed max-w-sm">
          Bangladesh's national civic reporting platform. Report infrastructure problems, demand transparency, and hold authorities accountable.
        </p>
        <p class="text-xs text-slate-600 mt-4">চোখে দেখা মানে দেখে রিপোর্ট করা — দেশের জন্য।</p>
      </div>
      <div>
        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Platform</h4>
        <ul class="space-y-2.5 text-sm">
          <li><a href="{{ route('reports.index') }}" class="text-slate-500 hover:text-white transition">Browse Reports</a></li>
          <li><a href="{{ route('register') }}" class="text-slate-500 hover:text-white transition">Submit a Report</a></li>
          @if(Route::has('legal.rti.form'))
          <li><a href="{{ route('legal.rti.form') }}" class="text-slate-500 hover:text-white transition">RTI Generator</a></li>
          @endif
        </ul>
      </div>
      <div>
        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Account</h4>
        <ul class="space-y-2.5 text-sm">
          <li><a href="{{ route('login') }}" class="text-slate-500 hover:text-white transition">Sign In</a></li>
          <li><a href="{{ route('register') }}" class="text-slate-500 hover:text-white transition">Register</a></li>
          @auth
          <li><a href="{{ route('dashboard') }}" class="text-slate-500 hover:text-white transition">My Dashboard</a></li>
          @endauth
        </ul>
      </div>
    </div>
    <div class="border-t border-slate-800 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
      <p class="text-xs text-slate-600">© {{ date('Y') }} Chokh-e-Dekha. Built for the people of Bangladesh 🇧🇩</p>
      <div class="flex items-center gap-1 text-xs text-slate-700">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
        Platform running live
      </div>
    </div>
  </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const target = document.querySelector(a.getAttribute('href'));
      if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth' }); }
    });
  });
});
</script>

</body>
</html>
