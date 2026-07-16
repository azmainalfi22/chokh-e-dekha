@extends('layouts.app')

@section('title', 'Saved Reports')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="card animate-fade-in-up mb-6">
        <div class="flex items-center justify-between p-6 border-b border-[var(--border-light)]">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-primary-dark dark:text-white">Saved Reports</h1>
                    <p class="text-secondary text-sm">{{ $bookmarks->total() }} {{ Str::plural('report', $bookmarks->total()) }} saved</p>
                </div>
            </div>
            
            @if($bookmarks->total() > 0)
                <a href="{{ route('reports.index') }}" class="btn btn-outline">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Browse More
                </a>
            @endif
        </div>
    </div>

    @forelse($bookmarks as $report)
        <article class="feed-card animate-fade-in-up mb-4" style="animation-delay: {{ $loop->index * 0.05 }}s;">
            <!-- Post Header -->
            <div class="post-header">
                <a href="{{ route('user.profile', $report->user) }}" class="avatar hover:opacity-80 transition-opacity">
                    {{ strtoupper(substr($report->user->name, 0, 1)) }}
                </a>
                <div class="post-meta">
                    <a href="{{ route('user.profile', $report->user) }}" class="post-author hover:underline">
                        {{ $report->user->name }}
                    </a>
                    <div class="post-time">
                        {{ $report->created_at->diffForHumans() }}
                        <span class="mx-1">•</span>
                        <span class="text-xs text-muted">Saved {{ \Carbon\Carbon::parse($report->bookmarked_at)->diffForHumans() }}</span>
                    </div>
                </div>
                
                <!-- Remove Bookmark Button -->
                <button class="btn btn-outline btn-sm bookmark-report" data-report-id="{{ $report->id }}">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/>
                    </svg>
                    <span class="ml-1">Saved</span>
                </button>
            </div>

            <!-- Post Content -->
            <div class="post-content">
                <h2 class="post-title">
                    <a href="{{ route('reports.show', $report) }}" class="hover:underline">
                        {{ $report->title }}
                    </a>
                </h2>
                <p class="post-description">
                    {{ Str::limit($report->description, 280) }}
                </p>
                <div class="post-meta-tags">
                    @if($report->category)
                        <span class="meta-tag">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            {{ $report->category }}
                        </span>
                    @endif
                    <span class="badge badge-{{ $report->status }}">
                        {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                    </span>
                </div>
                @if($report->photo)
                    <div class="post-image">
                        <img src="{{ Storage::url($report->photo) }}" alt="{{ $report->title }}">
                    </div>
                @endif
            </div>

            <!-- Post Stats -->
            <div class="post-stats">
                <div style="display: {{ (isset($report->likes_count) && $report->likes_count > 0) ? 'flex' : 'none' }}; align-items: center; gap: 0.25rem;">
                    <div style="width: 18px; height: 18px; background: linear-gradient(135deg, #e11d48, #f43f5e); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <svg fill="white" viewBox="0 0 20 20" style="width: 10px; height: 10px;">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <span data-likes>{{ $report->likes_count ?? 0 }}</span>
                </div>
                <div style="display: flex; gap: 0.75rem; font-size: 0.875rem; color: var(--text-muted);">
                    @if(isset($report->comments_count) && $report->comments_count > 0)
                        <span>{{ $report->comments_count }} {{ Str::plural('comment', $report->comments_count) }}</span>
                    @endif
                </div>
            </div>

            <!-- Post Actions -->
            <div class="post-actions">
                <button class="action-btn {{ $report->liked_by_user ?? false ? 'liked' : '' }}"
                        data-action="like"
                        data-report-id="{{ $report->id }}">
                    <svg fill="{{ $report->liked_by_user ?? false ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                    <span>Like</span>
                </button>
                <button class="action-btn" data-action="comment" data-report-id="{{ $report->id }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <span>Comment</span>
                </button>
                <button class="action-btn" data-action="share" data-report-id="{{ $report->id }}" data-title="{{ $report->title }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                    </svg>
                    <span>Share</span>
                </button>
            </div>
        </article>
    @empty
        <div class="card text-center py-16">
            <div class="inline-flex h-20 w-20 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/20 mb-6 mx-auto">
                <svg class="h-10 w-10 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-primary-dark dark:text-white mb-3">No Saved Reports</h2>
            <p class="text-secondary mb-6 max-w-md mx-auto">
                You haven't saved any reports yet. Click the bookmark icon on any report to save it for later.
            </p>
            <a href="{{ route('reports.index') }}" class="btn btn-primary inline-flex">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Browse Reports
            </a>
        </div>
    @endforelse

    @if($bookmarks->hasPages())
        <div class="mt-8">
            {{ $bookmarks->links() }}
        </div>
    @endif
</div>

<script>
// Handle bookmark removals
document.addEventListener('click', async (e) => {
    const bookmarkBtn = e.target.closest('.bookmark-report');
    if (!bookmarkBtn) return;
    
    const reportId = bookmarkBtn.dataset.reportId;
    const card = bookmarkBtn.closest('.feed-card');
    
    try {
        const response = await fetch(`/reports/${reportId}/bookmark`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });

        const data = await response.json();

        if (response.ok && data.success) {
            // Animate and remove card
            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '0';
            card.style.transform = 'translateX(-20px)';
            
            setTimeout(() => {
                card.remove();
                
                // Check if page is empty
                if (document.querySelectorAll('.feed-card').length === 0) {
                    location.reload(); // Reload to show empty state
                }
            }, 300);
            
            if (window.socialInteractions) {
                window.socialInteractions.showToast('Report removed from bookmarks', 'success');
            }
        }
    } catch (error) {
        console.error('Bookmark error:', error);
        if (window.socialInteractions) {
            window.socialInteractions.showToast('Failed to remove bookmark', 'error');
        }
    }
});
</script>
@endsection

