{{-- Government-Grade Professional Header with BD Branding --}}
<header class="header govt-header">
    {{-- Top Government Branding Bar --}}
    <div class="govt-brand-bar-small">
        <div class="max-w-7xl mx-auto px-4 flex items-center justify-between text-xs">
            <span>🇧🇩 Government of Bangladesh | গণপ্রজাতন্ত্রী বাংলাদেশ সরকার</span>
            <div class="flex items-center gap-4">
                <span>{{ now()->timezone('Asia/Dhaka')->format('d M, Y h:i A') }}</span>
                <button id="lang-toggle" class="flex items-center gap-1 hover:text-white transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                    </svg>
                    <span>বাং/EN</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Main Navigation --}}
    <div class="bg-white/95 backdrop-blur-lg shadow-lg border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                {{-- Logo & Brand --}}
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                    <div class="relative">
                        <div class="w-12 h-12 bg-gradient-to-br from-bd-green to-bd-green-dark rounded-full flex items-center justify-center text-white font-bold text-xl shadow-lg transition-transform group-hover:scale-110">
                            চ
                        </div>
                        <div class="absolute -top-1 -right-1 w-4 h-4 bg-bd-red rounded-full flex items-center justify-center">
                            <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-govt-navy group-hover:text-bd-green transition">Chokh-e-Dekha</h1>
                        <p class="text-xs text-text-muted leading-tight">{{ config('app.tagline') }}</p>
                    </div>
                </a>

                {{-- Desktop Navigation --}}
                <nav class="hidden md:flex items-center gap-1">
                    <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'nav-item-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span>ড্যাশবোর্ড</span>
                    </a>

                    <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.*') ? 'nav-item-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>আমার রিপোর্ট</span>
                    </a>

                    <a href="{{ route('reports.create') }}" class="btn-primary-govt-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>নতুন রিপোর্ট</span>
                    </a>

                    <div class="relative group">
                        <button class="nav-item flex items-center gap-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            <span>সেবাসমূহ</span>
                            <svg class="w-4 h-4 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="absolute top-full left-0 mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                            <div class="p-2 space-y-1">
                                <a href="{{ route('legal.rti.form') }}" class="dropdown-item">
                                    <svg class="w-5 h-5 text-bd-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                    </svg>
                                    <div>
                                        <div class="font-semibold text-sm">তথ্য অধিকার (RTI)</div>
                                        <div class="text-xs text-text-muted">আবেদন দায়ের করুন</div>
                                    </div>
                                </a>
                                <a href="/api/v1/surveys" class="dropdown-item">
                                    <svg class="w-5 h-5 text-govt-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                    </svg>
                                    <div>
                                        <div class="font-semibold text-sm">জনমত জরিপ</div>
                                        <div class="text-xs text-text-muted">আপনার মতামত দিন</div>
                                    </div>
                                </a>
                                <a href="#" class="dropdown-item">
                                    <svg class="w-5 h-5 text-bd-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <div>
                                        <div class="font-semibold text-sm">গোপনীয় প্রতিবেদন</div>
                                        <div class="text-xs text-text-muted">দুর্নীতি রিপোর্ট</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- User Menu --}}
                    <div class="relative group ml-4">
                        <button class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 transition">
                            <div class="w-8 h-8 bg-gradient-to-br from-bd-green to-govt-navy rounded-full flex items-center justify-center text-white font-bold text-sm">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div class="text-left">
                                <p class="text-sm font-semibold text-govt-navy">{{ Str::limit(auth()->user()->name, 15) }}</p>
                                <p class="text-xs text-text-muted">নাগরিক</p>
                            </div>
                            <svg class="w-4 h-4 text-text-muted transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="absolute top-full right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                            <div class="p-2 space-y-1">
                                <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span>প্রোফাইল সম্পাদনা</span>
                                </a>
                                <div class="border-t border-gray-200 my-1"></div>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item w-full text-left text-bd-red">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        <span>প্রস্থান</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </nav>

                {{-- Mobile Menu Button --}}
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-6 h-6 text-govt-navy" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Menu (Hidden by default) --}}
    <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-200 shadow-xl">
        <div class="px-4 py-4 space-y-2">
            <a href="{{ route('dashboard') }}" class="mobile-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>ড্যাশবোর্ড</span>
            </a>
            <a href="{{ route('reports.index') }}" class="mobile-nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>আমার রিপোর্ট</span>
            </a>
            <a href="{{ route('reports.create') }}" class="mobile-nav-item bg-bd-green text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>নতুন রিপোর্ট দায়ের করুন</span>
            </a>
            <div class="border-t border-gray-200 my-2"></div>
            <a href="{{ route('legal.rti.form') }}" class="mobile-nav-item">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                </svg>
                <span>তথ্য অধিকার (RTI)</span>
            </a>
            <a href="/api/v1/surveys" class="mobile-nav-item">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <span>জনমত জরিপ</span>
            </a>
            <div class="border-t border-gray-200 my-2"></div>
            <a href="{{ route('profile.edit') }}" class="mobile-nav-item">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span>প্রোফাইল</span>
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="mobile-nav-item w-full text-left text-bd-red">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span>প্রস্থান</span>
                </button>
            </form>
        </div>
    </div>
</header>

<style>
.govt-brand-bar-small {
    background: linear-gradient(90deg, var(--bd-green) 0%, var(--bd-green-dark) 100%);
    color: white;
    padding: 0.375rem 0;
    font-weight: 500;
}

.nav-item {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: var(--radius-md);
    font-weight: 600;
    color: var(--text-secondary);
    transition: all var(--transition-fast);
}

.nav-item:hover {
    background: var(--bg-secondary);
    color: var(--govt-navy);
}

.nav-item-active {
    background: linear-gradient(135deg, var(--bd-green), var(--bd-green-dark));
    color: white;
}

.btn-primary-govt-sm {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: var(--radius-md);
    font-weight: 600;
    background: linear-gradient(135deg, var(--bd-red), var(--bd-red-dark));
    color: white;
    box-shadow: var(--shadow-md);
    transition: all var(--transition-fast);
}

.btn-primary-govt-sm:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-1px);
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: var(--radius-md);
    transition: all var(--transition-fast);
    color: var(--text-primary);
}

.dropdown-item:hover {
    background: var(--bg-secondary);
    color: var(--bd-green);
}

.mobile-nav-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-radius: var(--radius-md);
    font-weight: 600;
    color: var(--text-primary);
    transition: all var(--transition-fast);
}

.mobile-nav-item:hover {
    background: var(--bg-secondary);
    color: var(--bd-green);
}

.mobile-nav-item.active {
    background: linear-gradient(135deg, var(--bd-green), var(--bd-green-dark));
    color: white;
}
</style>

<script>
document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
    const menu = document.getElementById('mobile-menu');
    menu.classList.toggle('hidden');
});

document.getElementById('lang-toggle')?.addEventListener('click', function() {
    // Language toggle functionality - implement based on your i18n strategy
    console.log('Language toggle clicked');
});
</script>

