@extends('layouts.app')

@section('title', $user->name . ' - Profile')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Profile Header -->
    <div class="card mb-8 animate-fade-in-up">
        <div class="flex items-start gap-6">
            <!-- Avatar -->
            <div class="flex-shrink-0">
                @if($user->profile_photo)
                    <img src="{{ asset('storage/profile_photos/' . $user->profile_photo) }}" 
                         alt="{{ $user->name }}"
                         class="w-32 h-32 rounded-full object-cover border-4 border-gradient-to-r from-purple-500 to-pink-500 shadow-xl">
                @else
                    <div class="w-32 h-32 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white text-4xl font-bold shadow-xl">
                        {{ Str::upper(Str::substr($user->name, 0, 1)) }}
                    </div>
                @endif
            </div>

            <!-- Profile Info -->
            <div class="flex-1 min-w-0">
                <h1 class="text-3xl font-bold mb-2">{{ $user->name }}</h1>
                
                @if($user->address)
                    <div class="flex items-center gap-2 text-secondary mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ $user->address }}
                    </div>
                @endif

                <!-- Stats -->
                <div class="flex items-center gap-6 flex-wrap">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="font-bold text-lg">{{ $reportsCount }}</span>
                        <span class="text-secondary">{{ Str::plural('Report', $reportsCount) }}</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-bold text-lg">{{ $user->likes_given_count ?? 0 }}</span>
                        <span class="text-secondary">Likes Given</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <span class="font-bold text-lg">{{ $user->comments_count ?? 0 }}</span>
                        <span class="text-secondary">Comments</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-secondary">Member since {{ $user->created_at->format('M Y') }}</span>
                    </div>
                </div>

                @if(auth()->check() && auth()->id() === $user->id)
                    <div class="mt-4">
                        <a href="{{ route('profile.edit') }}" class="btn btn-outline btn-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit Profile
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Activity Tabs -->
    <div class="card mb-6 animate-fade-in-up" style="animation-delay: 0.1s;">
        <div class="flex border-b border-[var(--border-light)]">
            <button class="tab-btn active" data-tab="reports">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Reports ({{ $reportsCount }})
            </button>
            <button class="tab-btn" data-tab="activity">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Recent Activity
            </button>
        </div>

        <!-- Reports Tab -->
        <div class="tab-content active" id="reports-tab">
            @forelse($reports as $report)
                <article class="border-b border-[var(--border-light)] last:border-0 p-4 hover:bg-[var(--bg-secondary)] transition-colors">
                    <a href="{{ route('reports.show', $report) }}" class="block group">
                        <div class="flex items-start gap-4">
                            @if($report->photo)
                                <div class="flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden">
                                    <img src="{{ Storage::url($report->photo) }}" 
                                         alt="{{ $report->title }}"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                </div>
                            @endif
                            
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-2 flex-wrap">
                                    <span class="badge badge-{{ $report->status }}">
                                        {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                                    </span>
                                    @if($report->category)
                                        <span class="px-2 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-purple-500 to-pink-500 text-white">
                                            {{ ucfirst($report->category) }}
                                        </span>
                                    @endif
                                    <span class="text-sm text-muted">
                                        {{ $report->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                
                                <h3 class="font-bold text-lg mb-1 group-hover:text-primary transition-colors">
                                    {{ $report->title }}
                                </h3>
                                
                                <p class="text-secondary text-sm mb-2 line-clamp-2">
                                    {{ $report->description }}
                                </p>
                                
                                <div class="flex items-center gap-4 text-sm text-muted">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $report->likes_count ?? 0 }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                        </svg>
                                        {{ $report->comments_count ?? 0 }}
                                    </span>
                                    @if($report->location)
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            </svg>
                                            {{ Str::limit($report->location, 30) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                </article>
            @empty
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto mb-4 text-muted opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-secondary font-medium">No reports yet</p>
                    @if(auth()->check() && auth()->id() === $user->id)
                        <a href="{{ route('reports.create') }}" class="btn btn-primary mt-4">Create Your First Report</a>
                    @endif
                </div>
            @endforelse

            @if($reports->hasPages())
                <div class="p-4">
                    {{ $reports->links() }}
                </div>
            @endif
        </div>

        <!-- Activity Tab -->
        <div class="tab-content" id="activity-tab">
            <div class="p-6">
                <div class="space-y-6">
                    @forelse($recentActivity as $activity)
                        <div class="flex gap-4 animate-fade-in-up">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white">
                                    @if($activity['type'] === 'report')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    @elseif($activity['type'] === 'comment')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                        </svg>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm">
                                    <span class="font-semibold">{{ $user->name }}</span>
                                    {!! $activity['description'] !!}
                                </p>
                                <p class="text-xs text-muted mt-1">{{ $activity['time'] }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto mb-4 text-muted opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <p class="text-secondary font-medium">No recent activity</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.tab-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 1rem;
    font-weight: 600;
    color: var(--text-secondary);
    background: transparent;
    border: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    transition: all var(--transition-fast);
}

.tab-btn:hover {
    color: var(--text-primary);
    background: var(--bg-secondary);
}

.tab-btn.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetTab = btn.dataset.tab;

            // Remove active class from all tabs
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));

            // Add active class to clicked tab
            btn.classList.add('active');
            document.getElementById(`${targetTab}-tab`).classList.add('active');
        });
    });
});
</script>
@endsection

