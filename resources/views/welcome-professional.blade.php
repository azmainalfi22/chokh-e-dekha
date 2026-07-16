<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Chokh-e-Dekha - Bangladesh's National Civic Engagement Platform. Report infrastructure problems, track resolution in real-time, and hold authorities accountable.">
    <title>Chokh-e-Dekha - National Civic Engagement Platform | চোখে দেখা</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        .hero-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        
        .stat-card {
            @apply bg-white rounded-xl shadow-lg p-6 transform transition hover:scale-105 hover:shadow-xl;
        }
        
        .feature-card {
            @apply bg-white rounded-2xl shadow-lg p-8 border-2 border-gray-100 transition hover:border-bd-green hover:shadow-2xl;
        }
        
        .trust-indicator {
            @apply inline-flex items-center gap-2 bg-green-50 text-green-700 px-4 py-2 rounded-full text-sm font-semibold border border-green-200;
        }
        
        .process-step {
            @apply relative bg-white rounded-xl p-6 shadow-md border-l-4 border-bd-green;
        }
    </style>
</head>
<body class="antialiased bg-gray-50">
    {{-- Government Certification Bar --}}
    <div class="bg-gradient-to-r from-bd-green to-bd-green-dark text-white text-center py-2 text-sm font-medium">
        <div class="max-w-7xl mx-auto px-4 flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>Certified by Government of Bangladesh | গণপ্রজাতন্ত্রী বাংলাদেশ সরকার কর্তৃক অনুমোদিত</span>
        </div>
    </div>

    {{-- Professional Navigation --}}
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-bd-green via-bd-green-dark to-govt-navy rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg">
                            চ
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-govt-navy">Chokh-e-Dekha</h1>
                            <p class="text-xs text-text-muted">Civic Engagement Platform</p>
                        </div>
                    </div>
                </div>

                <div class="hidden md:flex items-center gap-8">
                    <a href="#how-it-works" class="text-text-secondary hover:text-govt-navy font-medium transition">How It Works</a>
                    <a href="#features" class="text-text-secondary hover:text-govt-navy font-medium transition">Features</a>
                    <a href="#transparency" class="text-text-secondary hover:text-govt-navy font-medium transition">Live Data</a>
                    <a href="{{ route('reports.index') }}" class="text-text-secondary hover:text-govt-navy font-medium transition">Browse Reports</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="bg-bd-green hover:bg-bd-green-dark text-white px-6 py-2.5 rounded-lg font-semibold transition">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-text-secondary hover:text-govt-navy font-medium transition">Sign In</a>
                        <a href="{{ route('register') }}" class="bg-bd-green hover:bg-bd-green-dark text-white px-6 py-2.5 rounded-lg font-semibold transition">Get Started</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Hero Section - Professional Civic Tech Style --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-govt-navy via-govt-navy-dark to-bd-green-dark text-white hero-pattern">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-96 h-96 bg-bd-green rounded-full filter blur-3xl animate-pulse"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-govt-blue rounded-full filter blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    {{-- Trust Badges --}}
                    <div class="flex flex-wrap gap-3 mb-6">
                        <span class="inline-flex items-center gap-1.5 bg-white/10 backdrop-blur-sm border border-white/30 text-white px-3 py-1.5 rounded-full text-xs font-semibold">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Government Certified
                        </span>
                        <span class="inline-flex items-center gap-1.5 bg-white/10 backdrop-blur-sm border border-white/30 text-white px-3 py-1.5 rounded-full text-xs font-semibold">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            Secure & Anonymous
                        </span>
                        <span class="inline-flex items-center gap-1.5 bg-white/10 backdrop-blur-sm border border-white/30 text-white px-3 py-1.5 rounded-full text-xs font-semibold">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                            </svg>
                            Real-Time Updates
                        </span>
                    </div>

                    <h1 class="text-5xl md:text-6xl font-black mb-6 leading-tight">
                        See It.<br>
                        Report It.<br>
                        <span class="text-bd-red">Fix It.</span>
                    </h1>

                    <p class="text-xl md:text-2xl text-gray-200 mb-4 leading-relaxed font-light">
                        Bangladesh's first national civic engagement platform for transparent, trackable infrastructure problem reporting.
                    </p>
                    
                    <p class="text-lg text-gray-300 mb-8">
                        <span class="font-semibold text-white">চোখে দেখা (Chokh-e-Dekha)</span> — Citizens reporting, Government responding, Change happening.
                    </p>

                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-bd-red hover:bg-bd-red-dark text-white px-8 py-4 rounded-xl font-bold text-lg shadow-2xl transition transform hover:scale-105">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Report a Problem
                        </a>
                        <a href="#how-it-works" class="inline-flex items-center gap-2 bg-white text-govt-navy px-8 py-4 rounded-xl font-bold text-lg shadow-xl transition transform hover:scale-105">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            See How It Works
                        </a>
                    </div>

                    {{-- Key Stats Inline --}}
                    <div class="mt-10 flex flex-wrap gap-8 text-sm">
                        <div>
                            <div class="text-3xl font-bold text-bd-red">{{ number_format(\App\Models\Report::count()) }}+</div>
                            <div class="text-gray-300">Issues Reported</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-success">{{ number_format(\App\Models\Report::where('status','resolved')->count()) }}+</div>
                            <div class="text-gray-300">Problems Fixed</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-info">{{ number_format(\App\Models\User::count()) }}+</div>
                            <div class="text-gray-300">Active Citizens</div>
                        </div>
                    </div>
                </div>

                {{-- Live Dashboard Preview --}}
                <div class="hidden md:block">
                    <div class="bg-white/5 backdrop-blur-xl rounded-2xl p-8 border border-white/20 shadow-2xl">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-bold text-white">Live Activity</h3>
                            <span class="flex items-center gap-1.5 text-xs text-green-300">
                                <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                                Live
                            </span>
                        </div>
                        
                        <div class="space-y-4">
                            @php
                                $recentReports = \App\Models\Report::with('user:id,name')->latest()->take(4)->get();
                            @endphp
                            @forelse($recentReports as $report)
                                <div class="bg-white/10 rounded-lg p-4 border border-white/10 hover:bg-white/15 transition">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 bg-bd-green/30 rounded-full flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-white font-medium text-sm truncate">{{ Str::limit($report->title, 40) }}</p>
                                            <p class="text-gray-400 text-xs mt-1">{{ $report->city_corporation ?? $report->location }}</p>
                                            <div class="flex items-center gap-2 mt-2">
                                                <span class="px-2 py-0.5 bg-{{ $report->status === 'resolved' ? 'green' : ($report->status === 'in_progress' ? 'blue' : 'yellow') }}-500/20 text-{{ $report->status === 'resolved' ? 'green' : ($report->status === 'in_progress' ? 'blue' : 'yellow') }}-300 rounded text-xs">
                                                    {{ ucfirst($report->status) }}
                                                </span>
                                                <span class="text-gray-400 text-xs">{{ $report->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-gray-400 py-8">
                                    <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-sm">Be the first to report!</p>
                                </div>
                            @endforelse
                        </div>
                        
                        <a href="{{ route('reports.index') }}" class="block mt-6 text-center text-white hover:text-bd-red font-semibold transition">
                            View All Reports →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Trusted By - Government Partners --}}
    <section class="py-8 bg-white border-b">
        <div class="max-w-7xl mx-auto px-4">
            <p class="text-center text-xs uppercase tracking-wide text-text-muted mb-6 font-bold">Integrated with Government Authorities</p>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-6 items-center">
                @foreach(config('app.govt_entities', []) as $entity)
                    <div class="text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full mx-auto mb-2 flex items-center justify-center">
                            <svg class="w-8 h-8 text-bd-green" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.951 22.951 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"/>
                            </svg>
                        </div>
                        <p class="text-xs text-text-secondary font-medium">{{ Str::limit($entity, 20) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- How It Works --}}
    <section id="how-it-works" class="py-20 bg-gradient-to-br from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <span class="inline-block bg-bd-green/10 text-bd-green px-4 py-2 rounded-full text-sm font-bold mb-4">SIMPLE 4-STEP PROCESS</span>
                <h2 class="text-4xl md:text-5xl font-black text-govt-navy mb-4">How Chokh-e-Dekha Works</h2>
                <p class="text-xl text-text-secondary max-w-3xl mx-auto">
                    From spotting a problem to seeing it fixed — transparent, trackable, and accountable.
                </p>
            </div>

            <div class="grid md:grid-cols-4 gap-8">
                @php
                    $steps = [
                        [
                            'icon' => 'M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9zM9 9a2 2 0 110-4 2 2 0 010 4zm6 3a2 2 0 11-4 0 2 2 0 014 0z',
                            'title' => '1. Spot & Capture',
                            'desc' => 'See a pothole, broken street light, or waste issue? Take a photo with your phone.',
                            'color' => 'bd-red'
                        ],
                        [
                            'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z',
                            'title' => '2. Pin Location',
                            'desc' => 'GPS auto-detects your location. Drag the map pin to correct it if needed.',
                            'color' => 'govt-blue'
                        ],
                        [
                            'icon' => 'M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z',
                            'title' => '3. Track Status',
                            'desc' => 'Receive SMS/email updates. See real-time progress: Pending → In Progress → Resolved.',
                            'color' => 'warning'
                        ],
                        [
                            'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                            'title' => '4. Verify Fix',
                            'desc' => 'Once fixed, verify and rate the response. Help make Bangladesh better!',
                            'color' => 'success'
                        ]
                    ];
                @endphp

                @foreach($steps as $index => $step)
                    <div class="feature-card text-center relative group">
                        <div class="absolute -top-4 -right-4 w-12 h-12 bg-{{ $step['color'] }}/10 rounded-full flex items-center justify-center text-{{ $step['color'] }} font-black text-xl opacity-0 group-hover:opacity-100 transition">
                            {{ $index + 1 }}
                        </div>
                        
                        <div class="w-20 h-20 bg-{{ $step['color'] }}/10 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition transform">
                            <svg class="w-10 h-10 text-{{ $step['color'] }}" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="{{ $step['icon'] }}" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        
                        <h3 class="text-xl font-bold text-govt-navy mb-3">{{ $step['title'] }}</h3>
                        <p class="text-text-secondary">{{ $step['desc'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-12">
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-bd-green hover:bg-bd-green-dark text-white px-10 py-4 rounded-xl font-bold text-lg shadow-xl transition transform hover:scale-105">
                    Get Started Now →
                </a>
            </div>
        </div>
    </section>

    {{-- Continue with Features, Transparency, etc... --}}
    {{-- (I'll create this in the next section to keep it manageable) --}}
    
    <div class="text-center py-20">
        <p class="text-2xl font-bold text-govt-navy">🚧 Professional Platform Under Development 🚧</p>
        <p class="text-text-secondary mt-4">More sections coming soon: Features, Success Stories, Mobile Apps, Open Data, and more...</p>
    </div>

</body>
</html>
