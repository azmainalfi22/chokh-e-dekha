@extends(auth()->user()->is_admin ? 'layouts.admin' : 'layouts.app')

@section('title', __('app.dashboard'))

@push('styles')
<style>
/* Modern Dashboard Styling */
.stat-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid #e2e8f0;
    border-left: 4px solid #006A4E;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    border-left-width: 6px;
}

.stat-card.danger { border-left-color: #ef4444; }
.stat-card.warning { border-left-color: #f59e0b; }
.stat-card.info { border-left-color: #06b6d4; }
.stat-card.success { border-left-color: #10b981; }

.quick-action {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.2s ease;
    cursor: pointer;
}

.quick-action:hover {
    border-color: #006A4E;
    background: linear-gradient(135deg, #006A4E, #004d38);
    color: white;
    transform: scale(1.05);
    box-shadow: 0 10px 20px rgba(0, 106, 78, 0.2);
}

.quick-action:hover .action-icon {
    background: white;
    color: #006A4E;
}

.action-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.75rem;
    background: linear-gradient(135deg, #006A4E, #004d38);
    color: white;
    transition: all 0.2s ease;
}

.report-item {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 0.75rem;
    transition: all 0.2s ease;
}

.report-item:hover {
    border-color: #006A4E;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transform: translateX(4px);
}

.badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-pending { background: rgba(245, 158, 11, 0.1); color: #d97706; }
.badge-in_progress { background: rgba(6, 182, 212, 0.1); color: #0891b2; }
.badge-resolved { background: rgba(16, 185, 129, 0.1); color: #059669; }
.badge-rejected { background: rgba(239, 68, 68, 0.1); color: #dc2626; }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Header Section --}}
    <div class="bg-gradient-to-r from-blue-900 to-green-700 text-white py-8 mb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">
                        {{ __('app.welcome') }}, {{ auth()->user()->name }}
                    </h1>
                    <p class="text-gray-200">{{ __('app.citizen_portal') }} - {{ __('app.tagline') }}</p>
                </div>
                <div class="hidden md:block">
                    <div class="bg-white/10 backdrop-blur-lg rounded-xl p-4 border border-white/20">
                        <p class="text-sm mb-1">{{ __('app.created_at') }}</p>
                        <p class="font-bold">{{ auth()->user()->created_at->format('d M, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        {{-- Statistics Overview --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            {{-- Total Reports --}}
            <div class="stat-card">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold mb-1">{{ __('app.total_reports') }}</p>
                        <p class="text-4xl font-bold text-blue-900">{{ $stats['total'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500">All time</p>
            </div>

            {{-- Pending --}}
            <div class="stat-card warning">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold mb-1">{{ __('app.pending') }}</p>
                        <p class="text-4xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500">Awaiting review</p>
            </div>

            {{-- In Progress --}}
            <div class="stat-card info">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold mb-1">{{ __('app.in_progress') }}</p>
                        <p class="text-4xl font-bold text-cyan-600">{{ $stats['in_progress'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-cyan-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500">Being addressed</p>
            </div>

            {{-- Resolved --}}
            <div class="stat-card success">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-gray-600 text-sm font-semibold mb-1">{{ __('app.resolved') }}</p>
                        <p class="text-4xl font-bold text-green-600">{{ $stats['resolved'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500">Successfully closed</p>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('app.quick_actions') }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ route('reports.create') }}" class="quick-action">
                    <div class="action-icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <h3 class="font-bold mb-1">{{ __('app.create_report') }}</h3>
                    <p class="text-xs opacity-75">File a new issue</p>
                </a>

                <a href="{{ route('reports.index') }}" class="quick-action">
                    <div class="action-icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                    </div>
                    <h3 class="font-bold mb-1">{{ __('app.my_reports') }}</h3>
                    <p class="text-xs opacity-75">View all reports</p>
                </a>

                <a href="{{ route('legal.rti.form') }}" class="quick-action">
                    <div class="action-icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                        </svg>
                    </div>
                    <h3 class="font-bold mb-1">RTI Request</h3>
                    <p class="text-xs opacity-75">Right to Information</p>
                </a>

                <a href="{{ route('profile.edit') }}" class="quick-action">
                    <div class="action-icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold mb-1">{{ __('app.profile') }}</h3>
                    <p class="text-xs opacity-75">Manage account</p>
                </a>
            </div>
        </div>

        {{-- Recent Reports --}}
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900">{{ __('app.recent_reports') }}</h2>
                <a href="{{ route('reports.index') }}" class="text-green-700 hover:text-green-800 font-semibold text-sm">
                    View All →
                </a>
            </div>

            @forelse($recentReports as $report)
                <a href="{{ route('reports.show', $report) }}" class="report-item block">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="badge badge-{{ $report->status }}">
                                    {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                                </span>
                                <span class="text-xs text-gray-500">{{ $report->created_at->diffForHumans() }}</span>
                            </div>
                            <h3 class="font-semibold text-gray-900 mb-1">{{ $report->title }}</h3>
                            <div class="flex items-center gap-4 text-xs text-gray-600">
                                @if($report->location)
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        </svg>
                                        {{ $report->location }}
                                    </span>
                                @endif
                                @if($report->category)
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                        {{ ucfirst($report->category) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="text-center py-12">
                    <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-gray-600 mb-4">{{ __('app.no_data') }}</p>
                    <a href="{{ route('reports.create') }}" class="inline-flex items-center px-4 py-2 bg-green-700 text-white rounded-lg hover:bg-green-800 transition">
                        Submit Your First Report
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

