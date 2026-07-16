@extends('layouts.app')
@section('title', 'Community Reports')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

  {{-- ── Page Header ─────────────────────────────────────────────────── --}}
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div>
      <h1 class="text-xl font-bold text-slate-900 dark:text-white">Community Reports</h1>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Civic issues reported across Bangladesh</p>
    </div>
    <a href="{{ route('reports.create') }}"
       class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold
              bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm transition-colors self-start sm:self-auto">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
      </svg>
      New Report
    </a>
  </div>

  <div class="flex gap-6">

    {{-- ── Filter Sidebar ─────────────────────────────────────────────── --}}
    <aside class="hidden lg:block w-64 flex-shrink-0">
      <div class="sticky top-20 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-4">Filter Reports</h3>
        <form method="GET" action="{{ route('reports.index') }}" id="filterForm" class="space-y-4">

          <div>
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Search</label>
            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="Search reports..."
                   class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg
                          text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500
                          focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Status</label>
            <select name="status"
                    class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg
                           text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
              <option value="">All Statuses</option>
              @foreach($statuses as $stat)
                <option value="{{ $stat }}" {{ request('status') === $stat ? 'selected' : '' }}>
                  {{ \Illuminate\Support\Str::headline($stat) }}
                </option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Category</label>
            <select name="category"
                    class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg
                           text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
              <option value="">All Categories</option>
              @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>
                  {{ ucfirst($cat) }}
                </option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">City</label>
            <select name="city_corporation"
                    class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg
                           text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
              <option value="">All Cities</option>
              @foreach($cities as $city)
                <option value="{{ $city }}" {{ request('city_corporation') === $city ? 'selected' : '' }}>
                  {{ $city }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="flex gap-2 pt-1">
            <button type="submit"
                    class="flex-1 px-4 py-2 text-sm font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition-colors">
              Apply
            </button>
            <a href="{{ route('reports.index') }}"
               class="flex-1 px-4 py-2 text-sm font-semibold text-center rounded-lg border border-slate-200 dark:border-slate-600
                      text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
              Clear
            </a>
          </div>
        </form>
      </div>
    </aside>

    {{-- ── Main Feed ───────────────────────────────────────────────────── --}}
    <div class="flex-1 min-w-0">

      {{-- Sort tabs --}}
      <div class="flex items-center gap-1.5 mb-4 overflow-x-auto pb-1">
        @php
          $tabs = [
            ['sort'=>'newest',   'label'=>'Latest',    'icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['sort'=>'popular',  'label'=>'Trending',  'icon'=>'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
            ['sort'=>'discussed','label'=>'Discussed', 'icon'=>'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
          ];
          $currentSort = request('sort', 'newest');
        @endphp
        @foreach($tabs as $tab)
          <a href="{{ route('reports.index', array_merge(request()->except('sort','page'), ['sort'=>$tab['sort']])) }}"
             class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold whitespace-nowrap transition-colors
                    {{ $currentSort === $tab['sort']
                       ? 'bg-emerald-600 text-white shadow-sm'
                       : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:border-emerald-300 hover:text-emerald-600' }}">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="{{ $tab['icon'] }}"/>
            </svg>
            {{ $tab['label'] }}
          </a>
        @endforeach

        {{-- Mobile filter trigger --}}
        <button type="button"
                onclick="document.getElementById('mobileFilters').classList.toggle('hidden')"
                class="lg:hidden ml-auto inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold
                       bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M7 8h10M11 12h2"/>
          </svg>
          Filters
        </button>
      </div>

      {{-- Mobile filters panel --}}
      <div id="mobileFilters" class="lg:hidden hidden mb-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm">
        <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-2 gap-3">
          <input type="text" name="q" value="{{ request('q') }}" placeholder="Search..."
                 class="col-span-2 px-3 py-2 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500">
          <select name="status" class="px-3 py-2 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <option value="">All Statuses</option>
            @foreach($statuses as $stat)
              <option value="{{ $stat }}" {{ request('status') === $stat ? 'selected' : '' }}>{{ \Illuminate\Support\Str::headline($stat) }}</option>
            @endforeach
          </select>
          <select name="city_corporation" class="px-3 py-2 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <option value="">All Cities</option>
            @foreach($cities as $city)
              <option value="{{ $city }}" {{ request('city_corporation') === $city ? 'selected' : '' }}>{{ $city }}</option>
            @endforeach
          </select>
          <button type="submit" class="col-span-2 py-2 text-sm font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">Apply Filters</button>
        </form>
      </div>

      {{-- Active filter chips --}}
      @if(request()->hasAny(['q','status','category','city_corporation']))
        <div class="flex flex-wrap gap-2 mb-4">
          @if(request('q'))
            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
              Search: "{{ request('q') }}"
              <a href="{{ route('reports.index', request()->except('q','page')) }}" class="ml-1 text-slate-400 hover:text-red-500">×</a>
            </span>
          @endif
          @if(request('status'))
            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">
              {{ \Illuminate\Support\Str::headline(request('status')) }}
              <a href="{{ route('reports.index', request()->except('status','page')) }}" class="ml-1 text-emerald-400 hover:text-red-500">×</a>
            </span>
          @endif
          @if(request('city_corporation'))
            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300">
              {{ request('city_corporation') }}
              <a href="{{ route('reports.index', request()->except('city_corporation','page')) }}" class="ml-1 text-blue-400 hover:text-red-500">×</a>
            </span>
          @endif
          @if(request('category'))
            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-violet-100 dark:bg-violet-900/40 text-violet-700 dark:text-violet-300">
              {{ ucfirst(request('category')) }}
              <a href="{{ route('reports.index', request()->except('category','page')) }}" class="ml-1 text-violet-400 hover:text-red-500">×</a>
            </span>
          @endif
        </div>
      @endif

      {{-- Report count --}}
      <p class="text-xs text-slate-400 dark:text-slate-500 mb-3">
        {{ number_format($reports->total()) }} {{ Str::plural('report', $reports->total()) }} found
      </p>

      {{-- Report Cards --}}
      @php
        $badge = fn($s) => match($s) {
          'resolved'    => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
          'in_progress' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
          'pending'     => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
          'rejected'    => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
          default       => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
        };
      @endphp

      <div class="space-y-3">
        @forelse($reports as $report)
          <article class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:border-slate-300 dark:hover:border-slate-600 hover:shadow-md transition-all overflow-hidden">

            {{-- Report image --}}
            @if($report->photo)
              <a href="{{ route('reports.show', $report) }}">
                <img src="{{ Storage::url($report->photo) }}"
                     alt="{{ $report->title }}"
                     class="w-full h-48 object-cover">
              </a>
            @endif

            <div class="p-5">
              {{-- Header: author + meta + badge --}}
              <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex items-center gap-2.5">
                  <div class="w-9 h-9 rounded-full bg-emerald-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                    {{ strtoupper(substr($report->user->name ?? 'A', 0, 1)) }}
                  </div>
                  <div>
                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $report->user->name ?? 'Anonymous' }}</div>
                    <div class="flex items-center gap-1.5 text-xs text-slate-400">
                      <span>{{ $report->created_at->diffForHumans() }}</span>
                      @if($report->city_corporation)
                        <span>·</span>
                        <span>{{ $report->city_corporation }}</span>
                      @endif
                    </div>
                  </div>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium flex-shrink-0 {{ $badge($report->status) }}">
                  {{ \Illuminate\Support\Str::headline($report->status) }}
                </span>
              </div>

              {{-- Title & description --}}
              <a href="{{ route('reports.show', $report) }}" class="block group">
                <h2 class="text-base font-semibold text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors mb-1.5 leading-snug">
                  {{ $report->title }}
                </h2>
              </a>
              @if($report->description)
                <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed line-clamp-2 mb-3">
                  {{ $report->description }}
                </p>
              @endif

              {{-- Tags --}}
              <div class="flex items-center gap-2 flex-wrap">
                @if($report->category)
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    {{ ucfirst($report->category) }}
                  </span>
                @endif
                @if($report->location)
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    {{ Str::limit($report->location, 35) }}
                  </span>
                @endif
              </div>

              {{-- Actions row --}}
              <div class="flex items-center justify-between mt-4 pt-3 border-t border-slate-100 dark:border-slate-700">
                <div class="flex items-center gap-4">
                  <button class="flex items-center gap-1.5 text-xs font-medium text-slate-400 hover:text-red-500 dark:hover:text-red-400 transition-colors"
                          data-action="like" data-report-id="{{ $report->id }}">
                    <svg class="w-4 h-4 {{ isset($report->liked_by_user) && $report->liked_by_user ? 'fill-red-500 text-red-500' : '' }}"
                         fill="{{ isset($report->liked_by_user) && $report->liked_by_user ? 'currentColor' : 'none' }}"
                         stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    <span data-likes>{{ $report->likes_count ?? 0 }}</span>
                  </button>
                  <a href="{{ route('reports.show', $report) }}#comments"
                     class="flex items-center gap-1.5 text-xs font-medium text-slate-400 hover:text-blue-500 dark:hover:text-blue-400 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span>{{ $report->comments_count ?? 0 }}</span>
                  </a>
                </div>
                <a href="{{ route('reports.show', $report) }}"
                   class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition-colors">
                  View Details →
                </a>
              </div>
            </div>
          </article>
        @empty
          <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-16 text-center">
            <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mx-auto mb-4">
              <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </div>
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">No reports found</h3>
            <p class="text-xs text-slate-400 mb-5">
              @if(request()->hasAny(['q','status','category','city_corporation']))
                Try adjusting your filters to see more results.
              @else
                Be the first to report an issue in your community.
              @endif
            </p>
            @if(request()->hasAny(['q','status','category','city_corporation']))
              <a href="{{ route('reports.index') }}"
                 class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                Clear Filters
              </a>
            @else
              <a href="{{ route('reports.create') }}"
                 class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Submit First Report
              </a>
            @endif
          </div>
        @endforelse
      </div>

      {{-- Pagination --}}
      @if($reports->hasPages())
        <div class="mt-6">
          {{ $reports->links() }}
        </div>
      @endif

    </div>
  </div>
</div>
@endsection
