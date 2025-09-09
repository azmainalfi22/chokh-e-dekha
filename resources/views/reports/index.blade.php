@extends(auth()->user()?->is_admin ? 'layouts.admin' : 'layouts.app')

@section('title', 'All Reports')

@push('styles')
<style>
  /* ---------- PAGE-SPECIFIC STYLES ---------- */
  /* Only styles unique to this page go here. The global theme is included by the layout. */

  /* Report Cards */
  .report-card {
    background: var(--surface);
    border: 1px solid var(--ring);
    box-shadow: var(--shadow-lg);
    transition: all var(--duration-normal) var(--ease-out);
    backdrop-filter: blur(8px);
    position: relative;
    overflow: visible;
  }
  .report-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-xl);
    border-color: var(--accent);
  }

  .report-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: var(--space-3);
  }
  .report-head h3 {
    color: var(--text);
    line-height: 1.2;
  }

  .meta {
    color: var(--muted);
    font-size: var(--text-sm);
  }
  .meta li {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    line-height: 1.4;
  }

  a {
    color: var(--link);
    transition: all var(--duration-fast) ease;
  }
  a:hover {
    text-decoration: underline;
    color: var(--accent);
  }

  /* Badges */
  .badge {
    display: inline-flex;
    align-items: center;
    gap: var(--space-1);
    padding: var(--space-1) var(--space-3);
    border-radius: var(--radius-full);
    font-size: var(--text-xs);
    font-weight: 600;
    line-height: 1;
  }
  .badge-category{
    background: rgba(245,158,11,.15);
    color: var(--accent-700);
    border: 1px solid rgba(245,158,11,.3);
  }

  /* Status Pills (uses tokens from _theme) */
  .status-pill{
    display: inline-flex;
    align-items: center;
    gap: var(--space-1);
    padding: var(--space-1) var(--space-3);
    border-radius: var(--radius-full);
    font-size: var(--text-xs);
    font-weight: 700;
    line-height: 1;
    border: 1px solid transparent;
    white-space: nowrap;
    box-shadow: var(--shadow-sm);
  }
  .status-pending{
    background: var(--status-pending-bg);
    color: var(--status-pending-text);
    border-color: var(--status-pending-border);
  }
  .status-in_progress{
    background: var(--status-in-progress-bg);
    color: var(--status-in-progress-text);
    border-color: var(--status-in-progress-border);
  }
  .status-resolved{
    background: var(--status-resolved-bg);
    color: var(--status-resolved-text);
    border-color: var(--status-resolved-border);
  }
  .status-rejected{
    background: var(--status-rejected-bg);
    color: var(--status-rejected-text);
    border-color: var(--status-rejected-border);
  }

  /* Animations */
  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  .appear { animation: fadeInUp var(--duration-slower) var(--ease-out) both; }

  @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
  .loading { animation: pulse 1.5s ease-in-out infinite; }

  /* Filters Panel */
  .filters-panel{
    background: var(--surface);
    border: 1px solid var(--ring);
    box-shadow: var(--shadow-lg);
    backdrop-filter: blur(12px);
  }
  .filters-panel input[type="search"],
  .filters-panel select,
  .filters-panel .btn{
    background: var(--surface-muted) !important;
    color: var(--text) !important;
    border-color: var(--ring) !important;
    transition: all var(--duration-fast) ease !important;
  }
  .filters-panel input[type="search"]:focus,
  .filters-panel select:focus,
  .filters-panel .btn:focus{
    border-color: var(--ring-focus) !important;
    box-shadow: 0 0 0 3px rgba(245,158,11,.1) !important;
    outline: none !important;
  }
  .filters-panel .btn-apply{
    background: linear-gradient(135deg, var(--accent), #f97316) !important;
    color:#fff !important; border-color: transparent !important; font-weight:600 !important;
  }
  .filters-panel .btn-apply:hover{
    background: linear-gradient(135deg, var(--accent-600), #ea580c) !important;
    transform: translateY(-1px) !important; box-shadow: var(--shadow-md) !important;
  }
  .filters-panel .btn-ghost{
    background: var(--surface) !important; color: var(--text) !important; border-color: var(--ring) !important;
  }
  .filters-panel .btn-ghost:hover{
    background: var(--surface-muted) !important; border-color: var(--accent) !important;
  }

  /* Loading shimmer */
  .skeleton{
    background: linear-gradient(90deg, var(--surface-muted) 25%, var(--surface) 50%, var(--surface-muted) 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: var(--radius-lg);
  }
  @keyframes shimmer { 0% {background-position:-200% 0} 100% {background-position:200% 0} }

  /* Toast */
  .toast{
    position: fixed; left:50%; transform: translateX(-50%); bottom: var(--space-8);
    z-index: var(--z-toast); padding: var(--space-3) var(--space-6);
    border-radius: var(--radius-xl); font-size: var(--text-sm); font-weight:500; color:#fff;
    background: linear-gradient(135deg, var(--accent), #f97316);
    box-shadow: var(--shadow-lg); backdrop-filter: blur(8px);
    transition: all var(--duration-slow) var(--ease-out);
  }
  .toast.hide{ opacity:0; transform: translate(-50%, 1rem); }

  /* Responsive */
  @media (max-width: 768px){
    .report-card{ margin-bottom: var(--space-6); }
    .report-head{ flex-direction: column; gap: var(--space-2); }
    .status-pill{ align-self: flex-start; }
    .filters-panel form{ grid-template-columns: 1fr !important; }
    .filters-panel .btn-cluster{ flex-direction: column; gap: var(--space-2); }
  }
</style>
@endpush

@section('content')
@php
  use Illuminate\Support\Facades\Route;
  use Illuminate\Support\Str;
@endphp

<div class="relative grainy min-h-screen">
  {{-- Enhanced background blobs --}}
  <div class="pointer-events-none fixed -top-32 -right-32 h-96 w-96 rounded-full blur-3xl opacity-20 bg-gradient-to-br from-amber-200 via-orange-200 to-rose-200 "></div>
  <div class="pointer-events-none fixed -bottom-32 -left-32 h-96 w-96 rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-orange-200 via-amber-200 to-pink-200 " style="animation-delay:1s;"></div>

  <div class="max-w-7xl mx-auto p-4 md:p-8 relative z-[1]">
    <header class="mb-8">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
        <div class="min-w-0">
          <h1 class="text-3xl md:text-4xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-amber-700 via-orange-700 to-rose-700">
            All Reports
          </h1>
          <p class="text-base md:text-lg" style="color:var(--muted)">
            Search and filter reports submitted across all city corporations.
          </p>
        </div>

        @if(Route::has('report.create') && !auth()->user()?->is_admin)
          <a href="{{ route('report.create') }}"
             class="inline-flex items-center gap-2 px-6 py-3 rounded-xl shadow-lg hover:shadow-xl font-semibold text-white transition-all duration-200 self-start lg:self-auto"
             style="background: linear-gradient(135deg, var(--accent), #f97316);">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
              <path d="M11 5h2v14h-2zM5 11h14v2H5z"/>
            </svg>
            New Report
          </a>
        @endif
      </div>
    </header>

    {{-- Enhanced Flash Messages --}}
    @if(session('success'))
      <div class="mb-8 rounded-xl border border-emerald-300 bg-emerald-50 px-6 py-4 text-emerald-800 shadow-md backdrop-blur-sm">
        <div class="flex items-center gap-3">
          <svg class="h-5 w-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
          </svg>
          {{ session('success') }}
        </div>
      </div>
    @endif

    {{-- Enhanced Filters Panel --}}
    <div class="mb-8 rounded-2xl filters-panel p-6 shadow-xl">
      <form method="GET" action="{{ route('reports.index') }}" id="filtersForm"
            class="grid gap-4 items-center grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 xl:grid-cols-9">

        {{-- Enhanced Search --}}
        <div class="flex items-center gap-3 col-span-1 sm:col-span-2 lg:col-span-3 xl:col-span-3">
          <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 flex-none" style="color:var(--muted)" viewBox="0 0 24 24" fill="currentColor">
              <path d="M10 4a6 6 0 104.47 10.03l3.75 3.75 1.41-1.41-3.75-3.75A6 6 0 0010 4zm0 2a4 4 0 110 8 4 4 0 010-8z"/>
            </svg>
            <input type="search" name="q" value="{{ $q ?? '' }}" placeholder="Search title, description, or address…"
                   class="w-full rounded-xl border pl-10 pr-4 py-3 text-sm focus:ring-2 transition-all duration-200" 
                   style="border-color:var(--ring)">
          </div>
        </div>

        {{-- Enhanced Selects --}}
        <select name="city_corporation"
                class="w-full rounded-xl border px-4 py-3 text-sm focus:ring-2 transition-all duration-200"
                style="border-color:var(--ring)">
          <option value="">All cities</option>
          @foreach(($cities ?? collect()) as $c)
            <option value="{{ $c }}" @selected(($city ?? '') === $c)>{{ $c }}</option>
          @endforeach
        </select>

        <select name="category"
                class="w-full rounded-xl border px-4 py-3 text-sm focus:ring-2 transition-all duration-200"
                style="border-color:var(--ring)">
          <option value="">All categories</option>
          @foreach(($categories ?? collect()) as $categ)
            <option value="{{ $categ }}" @selected((($category ?? $cat ?? '') === $categ))>{{ $categ }}</option>
          @endforeach
        </select>

        <select name="status"
                class="w-full rounded-xl border px-4 py-3 text-sm focus:ring-2 transition-all duration-200"
                style="border-color:var(--ring)">
          <option value="">All statuses</option>
          @foreach(($statuses ?? ['pending','in_progress','resolved','rejected']) as $s)
            <option value="{{ $s }}" @selected(($status ?? '') === $s)>{{ Str::headline($s) }}</option>
          @endforeach
        </select>

        {{-- Enhanced Near Me Section --}}
        <div class="col-span-1 sm:col-span-2 xl:col-span-3 flex flex-wrap items-center gap-3">
          <button type="button" id="nearMeBtn"
                  class="inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold text-white shadow-md hover:shadow-lg transition-all duration-200 hover:-translate-y-0.5"
                  style="background: linear-gradient(135deg, var(--accent), #f97316);">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2l1.8 4.2L18 8l-4.2 1.8L12 14l-1.8-4.2L6 8l4.2-1.8z"/>
            </svg>
            <span>Near me</span>
          </button>

          <div class="flex items-center gap-2">
            <label for="radius_km" class="text-sm font-medium" style="color:var(--muted)">Radius:</label>
            @php $radiusKm = (int)($radiusKm ?? request('radius_km', 0)); @endphp
            <select id="radius_km" name="radius_km"
                    class="rounded-xl border px-3 py-2 text-sm focus:ring-2 transition-all duration-200" 
                    style="border-color:var(--ring)">
              <option value="0"  @selected($radiusKm===0)>Any</option>
              <option value="3"  @selected($radiusKm===3)>3 km</option>
              <option value="5"  @selected($radiusKm===5)>5 km</option>
              <option value="10" @selected($radiusKm===10)>10 km</option>
              <option value="20" @selected($radiusKm===20)>20 km</option>
            </select>
          </div>

          {{-- Hidden near-me fields --}}
          <input type="hidden" id="near_lat" name="near_lat" value="{{ $nearLat ?? request('near_lat') }}">
          <input type="hidden" id="near_lng" name="near_lng" value="{{ $nearLng ?? request('near_lng') }}">
        </div>

        {{-- Enhanced Actions --}}
        <div class="col-span-1 sm:col-span-2 lg:col-span-3 xl:col-span-3 flex flex-wrap items-center justify-end gap-3 btn-cluster">
          <select id="per_page" name="per_page"
                  class="rounded-xl border px-3 py-2 text-sm focus:ring-2 btn transition-all duration-200" 
                  style="border-color:var(--ring)">
            @foreach([12,18,24,30,36,48] as $pp)
              <option value="{{ $pp }}" @selected((int)request('per_page',12)===$pp)>Show {{ $pp }}</option>
            @endforeach
          </select>

          @php $sort = request('sort', ($nearLat ?? false) && ($nearLng ?? false) ? 'nearest' : 'newest'); @endphp
          <select id="sort" name="sort"
                  class="rounded-xl border px-3 py-2 text-sm focus:ring-2 btn transition-all duration-200" 
                  style="border-color:var(--ring)">
            <option value="newest"   @selected($sort==='newest')>Newest first</option>
            <option value="oldest"   @selected($sort==='oldest')>Oldest first</option>
            <option value="popular"  @selected($sort==='popular')>Most liked</option>
            <option value="discussed" @selected($sort==='discussed')>Most discussed</option>
            <option value="status"   @selected($sort==='status')>Status (A→Z)</option>
            <option value="city"     @selected($sort==='city')>City (A→Z)</option>
            <option value="category" @selected($sort==='category')>Category (A→Z)</option>
            <option value="nearest"  @selected($sort==='nearest')>Nearest</option>
          </select>

          <button type="submit" name="apply" value="1"
                  class="flex-none rounded-xl font-semibold px-6 py-2 shadow-md hover:shadow-lg btn-apply transition-all duration-200">
            Apply
          </button>

          <a href="{{ route('reports.index') }}"
             class="flex-none rounded-xl px-4 py-2 border shadow-sm hover:shadow-md btn-ghost transition-all duration-200">
            Clear
          </a>

          <button type="button" id="copyLinkBtn"
                  class="flex-none rounded-xl px-4 py-2 border shadow-sm hover:shadow-md btn-ghost transition-all duration-200">
            Copy link
          </button>
        </div>

        {{-- Enhanced Active Filters --}}
        @if(($q ?? '') || ($city ?? '') || (($category ?? $cat ?? '') !== '') || (($status ?? '') !== '') || (($nearLat ?? request('near_lat')) && ($nearLng ?? request('near_lng'))))
          <div class="col-span-full mt-4 pt-4 border-t" style="border-color:var(--ring)">
            <div class="flex flex-wrap items-center gap-2 text-sm" style="color:var(--muted)">
              <span class="font-semibold">Active filters:</span>
              @if($q)    
                <span class="px-3 py-1.5 rounded-lg font-medium" style="background:rgba(245,158,11,.15); color:var(--text); border:1px solid rgba(245,158,11,.3);">
                  Search: "{{ $q }}"
                </span>
              @endif
              @if($city) 
                <span class="px-3 py-1.5 rounded-lg font-medium" style="background:rgba(245,158,11,.15); color:var(--text); border:1px solid rgba(245,158,11,.3);">
                  City: {{ $city }}
                </span>
              @endif
              @php $showCat = ($category ?? $cat ?? ''); @endphp
              @if($showCat !== '') 
                <span class="px-3 py-1.5 rounded-lg font-medium" style="background:rgba(245,158,11,.15); color:var(--text); border:1px solid rgba(245,158,11,.3);">
                  Category: {{ $showCat }}
                </span>
              @endif
              @if(($status ?? '') !== '') 
                <span class="px-3 py-1.5 rounded-lg font-medium" style="background:rgba(245,158,11,.15); color:var(--text); border:1px solid rgba(245,158,11,.3);">
                  Status: {{ Str::headline($status) }}
                </span>
              @endif
              @if(($nearLat ?? request('near_lat')) && ($nearLng ?? request('near_lng')))
                <span class="px-3 py-1.5 rounded-lg font-medium" style="background:rgba(245,158,11,.15); color:var(--text); border:1px solid rgba(245,158,11,.3);">
                  Near me{{ $radiusKm ? " ({$radiusKm} km)" : '' }}
                </span>
              @endif
            </div>
          </div>
        @endif
      </form>
    </div>

    @php
      $pill = function($status) {
        $s = $status ?? 'pending';
        $label = Str::headline($s);
        return '<span class="status-pill status-'.e($s).'">● '.$label.'</span>';
      };
    @endphp

    {{-- Enhanced Reports List --}}
    @if($reports->isEmpty())
      <div class="rounded-2xl border border-amber-300 bg-white/80 backdrop-blur-sm px-8 py-16 text-center shadow-lg">
        <div class="mx-auto mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full ring-2 ring-amber-200 bg-amber-50">
          <svg class="h-8 w-8 text-amber-700" viewBox="0 0 24 24" fill="currentColor">
            <path d="M3 5h18v2H3zM3 10h18v2H3zM3 15h12v2H3z"/>
          </svg>
        </div>
        <h3 class="text-xl font-bold mb-2" style="color:var(--text)">No reports match your filters</h3>
        <p class="text-base mb-6" style="color:var(--muted)">Try adjusting your search terms or clearing active filters to see more results.</p>
        <a href="{{ route('reports.index') }}" 
           class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-semibold text-white shadow-md hover:shadow-lg transition-all duration-200"
           style="background: linear-gradient(135deg, var(--accent), #f97316);">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
            <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
          </svg>
          Reset all filters
        </a>
      </div>
    @else
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
        @foreach($reports as $report)
          <article class="report-card appear rounded-2xl overflow-visible">
            {{-- Enhanced Map Header --}}
            @if($report->static_map_url)
              <a href="@if($report->has_coords) https://www.google.com/maps/search/?api=1&query={{ $report->latitude }},{{ $report->longitude }} @else {{ route('reports.show', $report) }} @endif"
                 target="_blank" rel="noopener"
                 class="block relative group overflow-hidden">
                <img src="{{ $report->static_map_url }}"
                     alt="Map preview for {{ $report->title }}"
                     class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105" 
                     loading="lazy">
                <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                  <span class="text-white font-semibold bg-black/50 px-3 py-1 rounded-full text-sm">
                    View on map
                  </span>
                </div>
              </a>
            @endif

            <div class="p-6 flex flex-col gap-4 relative">
              {{-- Enhanced Header --}}
              <div class="report-head">
                <div class="min-w-0 flex-1">
                  <h3 class="text-xl font-bold leading-tight line-clamp-2 mb-2">{{ $report->title }}</h3>
                  <div class="flex items-center gap-2">
                    <span class="badge badge-category">{{ $report->category ?? 'General' }}</span>
                  </div>
                </div>
                <div class="flex-shrink-0">
                  {!! $pill($report->status) !!}
                </div>
              </div>

              {{-- Enhanced Meta Information --}}
              <ul class="meta space-y-2">
                <li class="flex items-start gap-2">
                  <svg class="h-4 w-4 mt-0.5 flex-shrink-0" style="color:var(--accent-700)" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C8 2 5 5 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-4-3-7-7-7zm0 9.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z"/>
                  </svg>
                  <div class="flex-1 min-w-0">
                    <span class="font-medium block" style="color:var(--text)">
                      {{ $report->short_address ?? $report->location ?? 'Address not specified' }}
                    </span>
                    @if($report->has_coords)
                      <a href="https://www.google.com/maps/search/?api=1&query={{ $report->latitude }},{{ $report->longitude }}"
                         target="_blank" rel="noopener"
                         class="text-xs hover:underline inline-flex items-center gap-1 mt-1">
                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor">
                          <path d="M14,3V5H17.59L7.76,14.83L9.17,16.24L19,6.41V10H21V3M19,19H5V5H12V3H5C3.89,3 3,3.9 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V12H19V19Z"/>
                        </svg>
                        Open in Maps
                      </a>
                    @endif
                  </div>
                </li>
                <li>
                  <svg class="h-4 w-4" style="color:var(--accent-700)" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M5 4h14v2H5zM5 8h14v12H5z"/>
                  </svg>
                  <span class="font-medium">{{ $report->city_corporation ?? 'Not specified' }}</span>
                </li>
                <li>
                  <svg class="h-4 w-4" style="color:var(--accent-700)" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 12a5 5 0 100-10 5 5 0 000 10zM4 22v-2c0-2.2 3.8-3.3 6-3.3s6 1.1 6 3.3v2H4z"/>
                  </svg>
                  <span>{{ $report->user->name ?? 'Anonymous' }}</span>
                </li>
                <li>
                  <svg class="h-4 w-4" style="color:var(--accent-700)" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7 2h10v2H7zM5 6h14v14H5zM9 8h6v6H9z"/>
                  </svg>
                  <span>{{ optional($report->created_at)->format('M d, Y \a\t h:i A') }}</span>
                </li>
              </ul>

              {{-- Engagement Bar --}}
              @include('partials.engagement._bar', ['report' => $report])
            </div>
          </article>
        @endforeach
      </div>

      {{-- Enhanced Pagination --}}
      @if(method_exists($reports,'links'))
        <div class="mt-12">
          <div class="flex justify-center">
            {{ $reports->appends(request()->query())->links() }}
          </div>
        </div>
      @endif
    @endif
  </div>
</div>

@endsection

@push('scripts')
<script>
(function(){
  'use strict';
  
  const getCsrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
  const form = document.getElementById('filtersForm');

  /* ===== Enhanced Toast System ===== */
  function toast(message, type = 'success') {
    // Remove any existing toasts
    document.querySelectorAll('.toast').forEach(t => t.remove());
    
    const toast = document.createElement('div');
    toast.role = 'status';
    toast.ariaLive = 'polite';
    toast.textContent = message;
    toast.className = 'toast';
    
    // Different styles for different types
    if (type === 'error') {
      toast.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
    }
    
    document.body.appendChild(toast);
    
    // Auto hide
    setTimeout(() => toast.classList.add('hide'), 2500);
    setTimeout(() => toast.remove(), 3000);
  }

  /* ===== Notification System Integration ===== */
  // Mark single notification as read
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.notif-read');
    if (!btn) return;

    const id = btn.dataset.id;
    const card = btn.closest('[data-notif-id]');

    try {
      const res = await fetch(`{{ route('notifications.read', ':id') }}`.replace(':id', id), {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': getCsrf(),
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });
      
      if (res.ok) {
        card?.remove();

        // Decrease badge count
        const badge = document.getElementById('notifCount');
        if (badge) {
          const currentCount = parseInt(badge.textContent || '1', 10);
          const newCount = Math.max(0, currentCount - 1);
          if (newCount > 0) {
            badge.textContent = newCount;
          } else {
            badge.remove();
          }
        }

        // Show empty state if no notifications left
        const notifList = document.getElementById('notifList');
        if (notifList && !notifList.querySelector('[data-notif-id]')) {
          notifList.innerHTML = '<div class="p-4 text-sm text-slate-600 dark:text-slate-300">No notifications yet.</div>';
        }
      }
    } catch (error) {
      console.error('Failed to mark notification as read:', error);
      toast('Failed to mark notification as read', 'error');
    }
  });

  // Mark all notifications as read
  document.getElementById('notifMarkAll')?.addEventListener('click', async () => {
    try {
      const res = await fetch(`{{ route('notifications.readAll') }}`, { 
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': getCsrf(),
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });
      
      if (res.ok) {
        // Clear all notifications from the list
        const notifList = document.getElementById('notifList');
        if (notifList) {
          notifList.innerHTML = '<div class="p-4 text-sm text-slate-600 dark:text-slate-300">No notifications yet.</div>';
        }

        // Remove the badge
        document.getElementById('notifCount')?.remove();
      }
    } catch (error) {
      console.error('Failed to mark all notifications as read:', error);
      toast('Failed to mark all notifications as read', 'error');
    }
  });

  // Close notification bar
  document.getElementById('notifClose')?.addEventListener('click', () => {
    const bar = document.getElementById('notifBar');
    if (bar) {
      bar.classList.add('hidden');
    }
  });

  // Collapse/expand notification list
  document.getElementById('notifCollapse')?.addEventListener('click', () => {
    const list = document.getElementById('notifList');
    if (list) {
      list.classList.toggle('hidden');
    }
  });

  /* ===== Enhanced Auto-appear Animation ===== */
  const observerOptions = {
    rootMargin: '0px 0px -10% 0px',
    threshold: 0.1
  };
  
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('appear');
        observer.unobserve(entry.target);
      }
    });
  }, observerOptions);

  // Observe all report cards with staggered animation
  document.querySelectorAll('.report-card').forEach((card, index) => {
    card.style.animationDelay = `${index * 0.1}s`;
    observer.observe(card);
  });

  /* ===== Enhanced Form Auto-submission ===== */
  if (form) {
    // Auto-submit on select changes
    ['per_page', 'sort', 'radius_km', 'city_corporation', 'category', 'status'].forEach(id => {
      const element = document.getElementById(id) || form.querySelector(`select[name="${id}"]`);
      if (element) {
        element.addEventListener('change', () => {
          form.classList.add('loading');
          form.requestSubmit();
        });
      }
    });

    // Enhanced search with debouncing
    const searchInput = form.querySelector('input[name="q"]');
    if (searchInput) {
      let searchTimeout;
      let lastSearchValue = searchInput.value;
      
      searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        const currentValue = e.target.value.trim();
        
        // Only trigger if value changed and meets criteria
        if (currentValue !== lastSearchValue && (currentValue.length >= 2 || currentValue.length === 0)) {
          searchTimeout = setTimeout(() => {
            lastSearchValue = currentValue;
            form.classList.add('loading');
            form.requestSubmit();
          }, 400);
        }
      });
      
      // Handle enter key
      searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          clearTimeout(searchTimeout);
          form.classList.add('loading');
          form.requestSubmit();
        }
      });
    }
  }

  /* ===== Enhanced Copy Link Function ===== */
  const copyBtn = document.getElementById('copyLinkBtn');
  if (copyBtn) {
    copyBtn.addEventListener('click', async () => {
      try {
        await navigator.clipboard.writeText(window.location.href);
        toast('Link copied to clipboard!');
        
        // Visual feedback
        copyBtn.style.background = 'var(--accent)';
        copyBtn.style.color = 'white';
        setTimeout(() => {
          copyBtn.style.background = '';
          copyBtn.style.color = '';
        }, 1000);
      } catch (error) {
        toast('Failed to copy link', 'error');
      }
    });
  }

  /* ===== Enhanced Geolocation ===== */
  const nearBtn = document.getElementById('nearMeBtn');
  const nearLat = document.getElementById('near_lat');
  const nearLng = document.getElementById('near_lng');
  const sortSelect = document.getElementById('sort');
  
  if (nearBtn) {
    nearBtn.addEventListener('click', () => {
      if (!navigator.geolocation) {
        toast('Geolocation is not supported by this browser', 'error');
        return;
      }
      
      // Loading state
      nearBtn.disabled = true;
      nearBtn.classList.add('loading');
      const originalContent = nearBtn.innerHTML;
      nearBtn.innerHTML = `
        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2v4m0 12v4m10-10h-4M6 12H2m15.364-6.364l-2.828 2.828M9.464 14.536L6.636 17.364m12.728 0l-2.828-2.828M9.464 9.464L6.636 6.636"/>
        </svg>
        <span>Locating...</span>
      `;
      
      const options = {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 60000
      };
      
      navigator.geolocation.getCurrentPosition(
        (position) => {
          nearLat.value = position.coords.latitude.toFixed(6);
          nearLng.value = position.coords.longitude.toFixed(6);
          
          if (sortSelect && sortSelect.value !== 'nearest') {
            sortSelect.value = 'nearest';
          }
          
          form.classList.add('loading');
          form.requestSubmit();
          
          toast('Location found! Showing nearby reports.');
        },
        (error) => {
          let message = 'Unable to get your location. ';
          switch(error.code) {
            case error.PERMISSION_DENIED:
              message += 'Please allow location access and try again.';
              break;
            case error.POSITION_UNAVAILABLE:
              message += 'Location information is unavailable.';
              break;
            case error.TIMEOUT:
              message += 'Location request timed out.';
              break;
            default:
              message += 'An unknown error occurred.';
              break;
          }
          
          toast(message, 'error');
          
          // Reset button
          nearBtn.disabled = false;
          nearBtn.classList.remove('loading');
          nearBtn.innerHTML = originalContent;
        },
        options
      );
    });
  }

  /* ===== Enhanced Loading States ===== */
  window.addEventListener('beforeunload', () => {
    if (form) {
      form.classList.add('loading');
    }
  });

  /* ===== Handle Form Submission Feedback ===== */
  if (form) {
    form.addEventListener('submit', () => {
      form.classList.add('loading');
      
      // Add loading overlay
      const loadingOverlay = document.createElement('div');
      loadingOverlay.className = 'fixed inset-0 bg-black/20 backdrop-blur-sm z-50 flex items-center justify-center';
      loadingOverlay.innerHTML = `
        <div class="bg-white rounded-xl p-6 shadow-xl flex items-center gap-3">
          <svg class="h-6 w-6 animate-spin text-amber-600" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2v4m0 12v4m10-10h-4M6 12H2m15.364-6.364l-2.828 2.828M9.464 14.536L6.636 17.364m12.728 0l-2.828-2.828M9.464 9.464L6.636 6.636"/>
          </svg>
          <span class="font-medium text-gray-900">Loading reports...</span>
        </div>
      `;
      document.body.appendChild(loadingOverlay);
      
      // Remove overlay after a timeout (fallback)
      setTimeout(() => {
        loadingOverlay?.remove();
      }, 10000);
    });
  }

  /* ===== Enhanced Keyboard Navigation ===== */
  document.addEventListener('keydown', (e) => {
    // Close any open overlays with Escape
    if (e.key === 'Escape') {
      // Close loading overlays or any modal-like elements
      document.querySelectorAll('.fixed.inset-0').forEach(overlay => {
        if (overlay.textContent.includes('Loading')) {
          overlay.remove();
        }
      });
    }
  });

  /* ===== Intersection Observer for Lazy Loading ===== */
  const lazyImageObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const img = entry.target;
        if (img.dataset.src) {
          img.src = img.dataset.src;
          img.removeAttribute('data-src');
          lazyImageObserver.unobserve(img);
        }
      }
    });
  }, {
    rootMargin: '50px 0px',
    threshold: 0.1
  });

  // Observe lazy images
  document.querySelectorAll('img[data-src]').forEach(img => {
    lazyImageObserver.observe(img);
  });

  /* ===== Enhanced Error Handling ===== */
  window.addEventListener('error', (e) => {
    console.error('Global error:', e.error);
  });

  window.addEventListener('unhandledrejection', (e) => {
    console.error('Unhandled promise rejection:', e.reason);
  });

  /* ===== Performance Monitoring ===== */
  if ('performance' in window && 'measure' in performance) {
    window.addEventListener('load', () => {
      setTimeout(() => {
        try {
          const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
          console.log(`Page loaded in ${loadTime}ms`);
          
          if (loadTime > 3000) {
            console.warn('Slow page load detected');
          }
        } catch (e) {
          console.log('Performance measurement failed:', e);
        }
      }, 0);
    });
  }

  /* ===== Accessibility Enhancements ===== */
  // Add ARIA live region for dynamic updates
  const liveRegion = document.createElement('div');
  liveRegion.setAttribute('aria-live', 'polite');
  liveRegion.setAttribute('aria-atomic', 'true');
  liveRegion.className = 'sr-only';
  liveRegion.id = 'live-region';
  document.body.appendChild(liveRegion);

  // Announce filter changes
  const originalToast = toast;
  window.toast = function(message, type = 'success') {
    originalToast(message, type);
    
    // Also announce to screen readers
    const liveRegion = document.getElementById('live-region');
    if (liveRegion) {
      liveRegion.textContent = message;
    }
  };

  /* ===== Initialize ===== */
  console.log('Reports index initialized successfully');
  
  // Remove any existing loading states
  document.querySelectorAll('.loading').forEach(el => {
    el.classList.remove('loading');
  });
  
  // Remove loading overlay if it exists
  document.querySelectorAll('.fixed.inset-0').forEach(overlay => {
    if (overlay.textContent.includes('Loading')) {
      overlay.remove();
    }
  });

})();
</script>
@endpush