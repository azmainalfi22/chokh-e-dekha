@php
    $likesCount = $report->likes_count ?? 0;
    $commentsCount = $report->comments_count ?? 0;
    $isLiked = auth()->check() && ($report->liked_by_user ?? false);
@endphp

{{-- Engagement Bar --}}
<div class="engagement-bar border-t pt-4 mt-4" style="border-color: var(--ring, #e5e7eb)">
    <div class="flex items-center justify-between flex-wrap gap-3">
        {{-- Left: Engagement Actions --}}
        <div class="flex items-center gap-2">
            {{-- Like Button --}}
            @auth
                <button type="button" 
                        class="engagement-btn like-btn {{ $isLiked ? 'active' : '' }}"
                        data-report-id="{{ $report->id }}"
                        data-liked="{{ $isLiked ? '1' : '0' }}"
                        aria-label="{{ $isLiked ? 'Unlike' : 'Like' }} this report">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="{{ $isLiked ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    <span class="likes-count">{{ $likesCount }}</span>
                    <span class="hidden sm:inline ml-1">{{ Str::plural('Like', $likesCount) }}</span>
                </button>
            @else
                <a href="{{ route('login') }}" class="engagement-btn">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    <span>{{ $likesCount }}</span>
                    <span class="hidden sm:inline ml-1">{{ Str::plural('Like', $likesCount) }}</span>
                </a>
            @endauth

            {{-- Comment Button --}}
            @auth
                <button type="button" 
                        class="engagement-btn comment-btn {{ $commentsCount > 0 ? 'has-comments' : '' }}"
                        data-report-id="{{ $report->id }}"
                        aria-label="Toggle comments"
                        aria-expanded="false">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span class="comments-count">{{ $commentsCount }}</span>
                    <span class="hidden sm:inline ml-1">{{ Str::plural('Comment', $commentsCount) }}</span>
                </button>
            @else
                <a href="{{ route('login') }}" class="engagement-btn">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span>{{ $commentsCount }}</span>
                    <span class="hidden sm:inline ml-1">{{ Str::plural('Comment', $commentsCount) }}</span>
                </a>
            @endauth
        </div>

        {{-- Right: View Details Button --}}
        <a href="{{ route('reports.show', $report) }}"
           class="view-details-btn">
            <span>View Details</span>
            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
            </svg>
        </a>
    </div>
</div>

{{-- Comments Section (Hidden by default) --}}
@auth
    <div id="comments-{{ $report->id }}" 
         class="comments-dropdown hidden mt-4 p-4 rounded-xl border transition-all duration-300"
         style="background: var(--surface-elevated, #ffffff); border-color: var(--ring, #e5e7eb)">
        
        {{-- Comment Form --}}
        <form class="comment-form mb-4" data-report-id="{{ $report->id }}">
            @csrf
            <div class="flex gap-3">
                <div class="comment-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1">
                    <textarea name="body" 
                              rows="2" 
                              placeholder="Add a comment..."
                              class="comment-textarea w-full rounded-lg border px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                              style="border-color: var(--ring, #e5e7eb); background: var(--surface, #ffffff)"
                              maxlength="1000"></textarea>
                    <div class="flex justify-between items-center mt-2">
                        <span class="text-xs text-gray-500">
                            <span class="char-count">0</span>/1000
                        </span>
                        <button type="submit" 
                                class="comment-submit-btn px-4 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium text-sm transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                disabled>
                            Post Comment
                        </button>
                    </div>
                </div>
            </div>
        </form>

        {{-- Comments List --}}
        <div class="comments-list" data-report-id="{{ $report->id }}">
            {{-- Comments will be loaded here dynamically --}}
        </div>
    </div>
@endauth

{{-- Consolidated Styles --}}
<style>
/* Engagement Button Base Styles */
.engagement-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.5rem 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s ease;
    border: 1px solid var(--ring, #e5e7eb);
    background: var(--surface, #ffffff);
    color: var(--text-secondary, #6b7280);
    cursor: pointer;
    text-decoration: none;
    line-height: 1;
}

.engagement-btn:hover {
    background: var(--surface-hover, #f9fafb);
    border-color: var(--border-hover, #d1d5db);
    color: var(--text, #111827);
    transform: translateY(-1px);
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

/* Active/Liked State */
.engagement-btn.like-btn.active {
    background: rgba(239, 68, 68, 0.05);
    border-color: rgba(239, 68, 68, 0.3);
    color: #ef4444;
}

.engagement-btn.like-btn.active svg {
    fill: #ef4444;
    color: #ef4444;
}

/* Has Comments State */
.engagement-btn.comment-btn.has-comments {
    background: rgba(59, 130, 246, 0.05);
    border-color: rgba(59, 130, 246, 0.3);
    color: #3b82f6;
}

/* View Details Button */
.view-details-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #f59e0b, #f97316);
    color: white;
    font-weight: 600;
    font-size: 0.875rem;
    border-radius: 0.5rem;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    white-space: nowrap;
    group: relative;
}

.view-details-btn:hover {
    background: linear-gradient(135deg, #ea580c, #ea580c);
    transform: translateY(-1px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

/* Comment Avatar */
.comment-avatar {
    width: 2rem;
    height: 2rem;
    min-width: 2rem;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.75rem;
    flex-shrink: 0;
}

/* Comments Dropdown Animation */
.comments-dropdown {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Processing State */
.engagement-btn.processing {
    pointer-events: none;
    opacity: 0.7;
}

/* Responsive Adjustments */
@media (max-width: 640px) {
    .engagement-bar > div {
        gap: 0.75rem;
    }
    
    .engagement-btn {
        padding: 0.375rem 0.625rem;
        font-size: 0.8125rem;
    }
    
    .view-details-btn {
        padding: 0.375rem 0.875rem;
        font-size: 0.8125rem;
    }
}
</style>