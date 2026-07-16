@extends(auth()->user()->is_admin ? 'layouts.admin' : 'layouts.app')

@section('title', 'ড্যাশবোর্ড')

@push('styles')
<style>
/* Dashboard-specific professional styling */
.stat-card {
    background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-xl);
    padding: var(--space-6);
    box-shadow: var(--shadow-md);
    transition: all var(--transition-normal);
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, var(--bd-green) 0%, transparent 50%);
    opacity: 0.05;
    border-radius: 0 var(--radius-xl) 0 50%;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-xl);
    border-color: var(--bd-green);
}

.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: var(--space-4);
}

.quick-action-card {
    background: white;
    border: 2px solid var(--border-light);
    border-radius: var(--radius-lg);
    padding: var(--space-6);
    text-align: center;
    transition: all var(--transition-normal);
    cursor: pointer;
}

.quick-action-card:hover {
    border-color: var(--bd-green);
    background: linear-gradient(135deg, var(--bd-green) 0%, var(--bd-green-dark) 100%);
    color: white;
    transform: scale(1.05);
    box-shadow: var(--shadow-xl);
}

.quick-action-card:hover .action-icon {
    background: white;
    color: var(--bd-green);
}

.action-icon {
    width: 64px;
    height: 64px;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto var(--space-4);
    background: linear-gradient(135deg, var(--bd-green) 0%, var(--bd-green-dark) 100%);
    color: white;
    transition: all var(--transition-normal);
}

.report-item {
    background: white;
    border: 1px solid var(--border-light);
    border-radius: var(--radius-lg);
    padding: var(--space-4);
    margin-bottom: var(--space-3);
    transition: all var(--transition-fast);
}

.report-item:hover {
    border-color: var(--bd-green);
    box-shadow: var(--shadow-md);
    transform: translateX(4px);
}

.progress-ring {
    transform: rotate(-90deg);
}

.progress-ring-circle {
    transition: stroke-dashoffset 0.5s ease;
}
</style>
@endpush

@section('content')
<div class="min-h-screen bg-bg-secondary">
    {{-- Header Section --}}
    <div class="bg-gradient-to-r from-govt-navy to-bd-green text-white py-8 mb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">
                        স্বাগতম, {{ auth()->user()->name }}
                    </h1>
                    <p class="text-gray-200">আপনার নাগরিক ড্যাশবোর্ড - আপনার কণ্ঠস্বর, সরকার শুনছে</p>
                </div>
                <div class="hidden md:block">
                    <div class="bg-white/10 backdrop-blur-lg rounded-xl p-4 border border-white/20">
                        <p class="text-sm mb-1">সদস্যতার তারিখ</p>
                        <p class="font-bold">{{ auth()->user()->created_at->format('d M, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        {{-- Statistics Overview --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            @php
                $myReports = \App\Models\Report::where('user_id', auth()->id());
                $totalReports = $myReports->count();
                $pendingReports = $myReports->clone()->where('status', 'pending')->count();
                $inProgressReports = $myReports->clone()->where('status', 'in_progress')->count();
                $resolvedReports = $myReports->clone()->where('status', 'resolved')->count();
            @endphp

            {{-- Total Reports --}}
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-text-muted text-sm font-semibold mb-1">মোট অভিযোগ</p>
                <p class="text-4xl font-bold text-govt-navy">{{ $totalReports }}</p>
            </div>

            {{-- Pending --}}
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-text-muted text-sm font-semibold mb-1">অপেক্ষমান</p>
                <p class="text-4xl font-bold text-warning">{{ $pendingReports }}</p>
            </div>

            {{-- In Progress --}}
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #06b6d4, #0891b2);">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <p class="text-text-muted text-sm font-semibold mb-1">প্রক্রিয়াধীন</p>
                <p class="text-4xl font-bold text-info">{{ $inProgressReports }}</p>
            </div>

            {{-- Resolved --}}
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-text-muted text-sm font-semibold mb-1">সমাধান হয়েছে</p>
                <p class="text-4xl font-bold text-success">{{ $resolvedReports }}</p>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-govt-navy mb-6">দ্রুত কার্যক্রম</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ route('reports.create') }}" class="quick-action-card">
                    <div class="action-icon">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <h3 class="font-bold mb-1">নতুন রিপোর্ট</h3>
                    <p class="text-sm opacity-75">অভিযোগ দায়ের করুন</p>
                </a>

                <a href="{{ route('reports.index') }}" class="quick-action-card">
                    <div class="action-icon">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                    </div>
                    <h3 class="font-bold mb-1">আমার রিপোর্ট</h3>
                    <p class="text-sm opacity-75">সকল অভিযোগ দেখুন</p>
                </a>

                <a href="{{ route('legal.rti.form') }}" class="quick-action-card">
                    <div class="action-icon">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                        </svg>
                    </div>
                    <h3 class="font-bold mb-1">RTI দায়ের</h3>
                    <p class="text-sm opacity-75">তথ্য অধিকার</p>
                </a>

                <a href="{{ route('profile.edit') }}" class="quick-action-card">
                    <div class="action-icon">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold mb-1">প্রোফাইল</h3>
                    <p class="text-sm opacity-75">সম্পাদনা করুন</p>
                </a>
            </div>
        </div>

        {{-- Recent Reports --}}
        <div class="grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-govt-navy">সাম্প্রতিক অভিযোগসমূহ</h2>
                        <a href="{{ route('reports.index') }}" class="text-bd-green hover:text-bd-green-dark font-semibold text-sm">
                            সকল দেখুন →
                        </a>
                    </div>

                    @php
                        $recentReports = \App\Models\Report::where('user_id', auth()->id())
                            ->latest()
                            ->take(5)
                            ->get();
                    @endphp

                    @forelse($recentReports as $report)
                        <a href="{{ route('reports.show', $report) }}" class="report-item block">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="badge-{{ $report->status }} text-xs px-3 py-1 rounded-full font-semibold">
                                            {{ ucfirst($report->status) }}
                                        </span>
                                        <span class="text-xs text-text-muted">{{ $report->created_at->diffForHumans() }}</span>
                                    </div>
                                    <h3 class="font-semibold text-govt-navy mb-1">{{ $report->title }}</h3>
                                    <p class="text-sm text-text-secondary line-clamp-2">{{ Str::limit($report->description, 100) }}</p>
                                    <div class="flex items-center gap-4 mt-2 text-xs text-text-muted">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            {{ $report->location ?? 'N/A' }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                            </svg>
                                            {{ ucfirst($report->category) }}
                                        </span>
                                    </div>
                                </div>
                                @if($report->media()->count() > 0)
                                    <div class="ml-4">
                                        <img src="{{ Storage::url($report->media()->first()->file_path) }}" alt="Report media" class="w-20 h-20 object-cover rounded-lg">
                                    </div>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-12">
                            <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-text-muted mb-4">এখনও কোনো রিপোর্ট নেই</p>
                            <a href="{{ route('reports.create') }}" class="btn-primary-govt">প্রথম রিপোর্ট দায়ের করুন</a>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Progress Card --}}
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-govt-navy mb-4">আপনার প্রভাব</h3>
                    <div class="text-center mb-6">
                        @php
                            $resolutionRate = $totalReports > 0 ? round(($resolvedReports / $totalReports) * 100) : 0;
                        @endphp
                        <div class="relative inline-flex items-center justify-center">
                            <svg class="progress-ring w-32 h-32">
                                <circle class="text-gray-200" stroke-width="8" stroke="currentColor" fill="transparent" r="56" cx="64" cy="64"/>
                                <circle class="progress-ring-circle text-bd-green" stroke-width="8" stroke-dasharray="352" stroke-dashoffset="{{ 352 - (352 * $resolutionRate / 100) }}" stroke-linecap="round" stroke="currentColor" fill="transparent" r="56" cx="64" cy="64"/>
                            </svg>
                            <span class="absolute text-3xl font-bold text-govt-navy">{{ $resolutionRate }}%</span>
                        </div>
                        <p class="text-text-muted mt-4">সমাধান হার</p>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-text-secondary">মোট অবদান</span>
                            <span class="font-bold text-govt-navy">{{ $totalReports }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-text-secondary">সফল সমাধান</span>
                            <span class="font-bold text-success">{{ $resolvedReports }}</span>
                        </div>
                    </div>
                </div>

                {{-- Trust Badge --}}
                <div class="bg-gradient-to-br from-bd-green to-govt-navy rounded-xl shadow-lg p-6 text-white text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg mb-2">যাচাইকৃত নাগরিক</h3>
                    <p class="text-sm text-gray-200">আপনি গণপ্রজাতন্ত্রী বাংলাদেশের একজন নিবন্ধিত সক্রিয় নাগরিক</p>
                </div>

                {{-- Help Card --}}
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-govt-navy mb-4">সাহায্য প্রয়োজন?</h3>
                    <div class="space-y-3">
                        <a href="#" class="flex items-center gap-3 text-sm text-text-secondary hover:text-bd-green transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            কিভাবে রিপোর্ট করবেন?
                        </a>
                        <a href="#" class="flex items-center gap-3 text-sm text-text-secondary hover:text-bd-green transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            সহায়তা কেন্দ্র
                        </a>
                        <a href="#" class="flex items-center gap-3 text-sm text-text-secondary hover:text-bd-green transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            হটলাইন: 16xxx
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
