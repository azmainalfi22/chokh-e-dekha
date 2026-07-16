@extends('layouts.app')

@section('title', 'Community Reports')

@push('styles')
<style>
/* Facebook-inspired Newsfeed Styles */
.feed-container {
    max-width: 680px;
    margin: 0 auto;
}

.feed-card {
    background: var(--bg-primary);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    margin-bottom: 1rem;
    transition: all var(--transition-base);
    border: 1px solid var(--border-light);
}

.feed-card:hover {
    box-shadow: var(--shadow-lg);
}

.post-header {
    padding: 1rem 1rem 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
    display: flex;
    align-items: center;
    justify-center: center;
    color: white;
    font-weight: 700;
    font-size: 1rem;
    flex-shrink: 0;
}

.post-meta {
    flex: 1;
    min-width: 0;
}

.post-author {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 0.9375rem;
}

.post-time {
    font-size: 0.8125rem;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.post-content {
    padding: 0 1rem 1rem;
}

.post-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
    line-height: 1.4;
  }

.post-description {
    color: var(--text-secondary);
    font-size: 0.9375rem;
    line-height: 1.6;
    margin-bottom: 0.75rem;
}

.post-meta-tags {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.meta-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.625rem;
    border-radius: var(--radius-full);
    font-size: 0.75rem;
    font-weight: 500;
    background: var(--bg-secondary);
    color: var(--text-secondary);
    border: 1px solid var(--border-light);
}

.post-stats {
    padding: 0.5rem 1rem;
    border-top: 1px solid var(--border-light);
    border-bottom: 1px solid var(--border-light);
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.875rem;
    color: var(--text-muted);
}

.post-actions {
    padding: 0.25rem 0.5rem;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.25rem;
}

.action-btn {
    padding: 0.5rem;
    border: none;
    background: transparent;
    color: var(--text-secondary);
    font-weight: 600;
    font-size: 0.9375rem;
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all var(--transition-fast);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.action-btn:hover {
    background: var(--bg-secondary);
}

.action-btn.liked {
    color: #e11d48;
}

.action-btn svg {
    width: 1.25rem;
    height: 1.25rem;
}

.sidebar-filters {
    position: sticky;
    top: 5rem;
}

.filter-card {
    background: var(--bg-primary);
    border-radius: var(--radius-lg);
    padding: 1rem;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-light);
}

.filter-title {
    font-weight: 700;
    font-size: 1rem;
    margin-bottom: 1rem;
    color: var(--text-primary);
}

.filter-group {
    margin-bottom: 1rem;
}

.filter-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 0.375rem;
    display: block;
}

.compose-card {
    background: var(--bg-primary);
    border-radius: var(--radius-lg);
    padding: 1rem;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-light);
    margin-bottom: 1rem;
}

.compose-prompt {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.compose-input {
    flex: 1;
    padding: 0.625rem 1rem;
    background: var(--bg-secondary);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-full);
    color: var(--text-secondary);
    font-size: 0.9375rem;
    cursor: pointer;
    transition: all var(--transition-fast);
}

.compose-input:hover {
    background: var(--bg-tertiary);
}

.feed-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
    border-radius: var(--radius-full);
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-secondary);
    background: transparent;
    border: 1px solid transparent;
    transition: all var(--transition-fast);
    white-space: nowrap;
    text-decoration: none;
}

.feed-tab:hover {
    background: var(--bg-secondary);
    color: var(--text-primary);
}

.feed-tab.active {
    background: var(--color-primary);
    color: white;
    border-color: var(--color-primary);
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

.feed-tab svg {
    flex-shrink: 0;
  }
</style>
@endpush

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Sidebar - Filters -->
            <aside class="lg:col-span-3 hidden lg:block">
                <div class="sidebar-filters">
                    <div class="filter-card">
                        <h3 class="filter-title">Filter Reports</h3>
                        
                        <form method="GET" action="{{ route('reports.index') }}" id="filterForm">
                            <div class="filter-group">
                                <label class="filter-label">Search</label>
                                <input type="text" name="q" value="{{ request('q') }}" 
                                       placeholder="Search reports..." 
                                       class="form-input text-sm">
        </div>

                            <div class="filter-group">
                                <label class="filter-label">Status</label>
                                <select name="status" class="form-select text-sm">
                                    <option value="">All Status</option>
                                    @foreach($statuses as $stat)
                                        <option value="{{ $stat }}" {{ request('status') == $stat ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $stat)) }}
                                        </option>
          @endforeach
        </select>
                            </div>

                            <div class="filter-group">
                                <label class="filter-label">Category</label>
                                <select name="category" class="form-select text-sm">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                                            {{ ucfirst($cat) }}
                                        </option>
          @endforeach
        </select>
                            </div>

                            <div class="filter-group">
                                <label class="filter-label">City</label>
                                <select name="city_corporation" class="form-select text-sm">
                                    <option value="">All Cities</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city }}" {{ request('city_corporation') == $city ? 'selected' : '' }}>
                                            {{ $city }}
                                        </option>
          @endforeach
            </select>
          </div>

                            <div class="flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm flex-1">Apply</button>
                                <a href="{{ route('reports.index') }}" class="btn btn-outline btn-sm">Clear</a>
                            </div>
                        </form>
                    </div>
        </div>
            </aside>

            <!-- Main Feed -->
            <main class="lg:col-span-6">
                <!-- Feed Tabs -->
                <div class="feed-card" style="margin-bottom: 1rem; padding: 0.5rem 1rem;">
                    <div style="display: flex; gap: 0.5rem; overflow-x: auto;">
                        <a href="{{ route('reports.index', array_merge(request()->except('sort'), ['sort' => 'newest'])) }}" 
                           class="feed-tab {{ request('sort', 'newest') === 'newest' ? 'active' : '' }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Latest
                        </a>
                        <a href="{{ route('reports.index', array_merge(request()->except('sort'), ['sort' => 'popular'])) }}" 
                           class="feed-tab {{ request('sort') === 'popular' ? 'active' : '' }}">
                            <svg fill="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            Trending
                        </a>
                        <a href="{{ route('reports.index', array_merge(request()->except('sort'), ['sort' => 'discussed'])) }}" 
                           class="feed-tab {{ request('sort') === 'discussed' ? 'active' : '' }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            Discussed
                        </a>
                        @if(request('near_lat') && request('near_lng'))
                        <a href="{{ route('reports.index', array_merge(request()->except('sort'), ['sort' => 'nearest'])) }}" 
                           class="feed-tab {{ request('sort') === 'nearest' ? 'active' : '' }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                            Nearby
                        </a>
                        @endif
                    </div>
                </div>

                <!-- Compose Box -->
                <div class="compose-card">
                    <div class="compose-prompt">
                        <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                        <a href="{{ route('reports.create') }}" class="compose-input">
                            What issue would you like to report?
                        </a>
                    </div>
                </div>

                <!-- Feed Items -->
                <div class="feed-container">
                    @forelse($reports as $report)
                        <article class="feed-card animate-fade-in-up" style="animation-delay: {{ $loop->index * 0.05 }}s;">
                            <!-- Post Header -->
                            <div class="post-header">
                                <div class="avatar">
                                    {{ $report->user ? strtoupper(substr($report->user->name, 0, 1)) : 'A' }}
                                </div>
                                <div class="post-meta">
                                    <div class="post-author">{{ $report->user->name ?? 'Anonymous' }}</div>
                                    <div class="post-time">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $report->created_at->diffForHumans() }}
                                        <span style="margin: 0 0.25rem;">·</span>
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $report->city_corporation ?? 'Bangladesh' }}
                                    </div>
                                </div>
                                <button class="btn btn-outline btn-sm">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                    </svg>
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
                                    <span class="badge badge-{{ $report->status }}">
                                        {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                </span>
                                    @if($report->category)
                                        <span class="meta-tag">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 12px; height: 12px;">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                            </svg>
                                            {{ ucfirst($report->category) }}
                </span>
              @endif
                                    @if($report->location)
                                        <span class="meta-tag">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 12px; height: 12px;">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            </svg>
                                            {{ Str::limit($report->location, 30) }}
                </span>
              @endif
            </div>
                            </div>

                            <!-- Post Image (if exists) -->
                            @if($report->photo)
                                <div style="padding: 0;">
                                    <img src="{{ Storage::url($report->photo) }}" 
                                         alt="{{ $report->title }}"
                                         style="width: 100%; height: auto; display: block; max-height: 500px; object-fit: cover;">
          </div>
        @endif

                            <!-- Post Stats -->
                            <div class="post-stats">
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="display: {{ (isset($report->likes_count) && $report->likes_count > 0) ? 'flex' : 'none' }}; align-items: center; gap: 0.25rem;">
                                        <div style="width: 18px; height: 18px; background: linear-gradient(135deg, #e11d48, #f43f5e); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                            <svg fill="white" viewBox="0 0 20 20" style="width: 10px; height: 10px;">
                                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
          </svg>
        </div>
                                        <span data-likes>{{ $report->likes_count ?? 0 }}</span>
                  </div>
                </div>
                                <div style="display: flex; gap: 1rem;">
                                    @if(isset($report->comments_count) && $report->comments_count > 0)
                                        <span>{{ $report->comments_count }} {{ Str::plural('comment', $report->comments_count) }}</span>
                                    @endif
                </div>
              </div>

                            <!-- Post Actions -->
                            <div class="post-actions" data-report-id="{{ $report->id }}">
                                <button class="action-btn {{ isset($report->liked_by_user) && $report->liked_by_user ? 'liked' : '' }}" 
                                        data-action="like" 
                                        data-report-id="{{ $report->id }}">
                                    <svg fill="{{ isset($report->liked_by_user) && $report->liked_by_user ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                  </svg>
                                    <span>Like</span>
                                </button>
                                <button class="action-btn" 
                                        data-action="comment" 
                                        data-report-id="{{ $report->id }}">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                                    <span>Comment</span>
                                </button>
                                <button class="action-btn" 
                                        data-action="share" 
                                        data-report-id="{{ $report->id }}"
                                        data-title="{{ $report->title }}">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                  </svg>
                                    <span>Share</span>
                                </button>
            </div>
          </article>
                    @empty
                        <div class="feed-card" style="padding: 3rem; text-center;">
                            <div style="width: 80px; height: 80px; margin: 0 auto 1.5rem; background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(147, 51, 234, 0.1)); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 40px; height: 40px; color: var(--text-muted);">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-primary);">No reports found</h3>
                            <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">Be the first to report an issue in your community</p>
                            <a href="{{ route('reports.create') }}" class="btn btn-primary">Create Report</a>
                        </div>
                    @endforelse
      </div>

                <!-- Pagination -->
                @if($reports->hasPages())
                    <div style="margin-top: 1.5rem;">
                        {{ $reports->links() }}
                    </div>
                @endif
            </main>

            <!-- Right Sidebar - Info -->
            <aside class="lg:col-span-3 hidden lg:block">
                <div class="sidebar-filters">
                    <div class="filter-card">
                        <h3 class="filter-title">About</h3>
                        <p style="font-size: 0.875rem; color: var(--text-secondary); line-height: 1.6;">
                            Community reports platform for Bangladesh. Report issues, track progress, and make a difference.
                        </p>
                    </div>
          </div>
            </aside>
        </div>
  </div>
</div>
@endsection
