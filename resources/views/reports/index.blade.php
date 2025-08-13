@extends(auth()->user()->is_admin ? 'layouts.admin' : 'layouts.app')

@section('title', 'All Reports')

@push('styles')
<style>
  /* Soft grain overlay (subtle) */
  .grainy::before{
    content:"";
    position:absolute; inset:0; pointer-events:none; z-index:0;
    opacity:.18; mix-blend:multiply;
    background-size: 220px 220px;
    background-repeat: repeat;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='180' height='180'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3CfeColorMatrix type='saturate' values='0'/%3E%3C/feTurbulence%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.20'/%3E%3C/svg%3E");
  }
</style>
@endpush

@section('content')
<div class="relative grainy">
  {{-- colored blobs --}}
  <div class="pointer-events-none absolute -top-20 -right-24 h-80 w-80 rounded-full blur-3xl opacity-30 bg-gradient-to-br from-amber-300 to-rose-300"></div>
  <div class="pointer-events-none absolute -bottom-24 -left-24 h-96 w-80 rounded-full blur-3xl opacity-30 bg-gradient-to-tr from-orange-300 to-pink-300"></div>

  <div class="max-w-7xl mx-auto p-4 md:p-8 relative z-[1]">
    <header class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="min-w-0">
          <h1 class="text-3xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-amber-700 via-orange-700 to-rose-700">
            All Reports
          </h1>
          <p class="text-sm text-amber-900/70">Search and filter reports submitted across all city corporations.</p>
        </div>

        @if(Route::has('report.create') && !auth()->user()->is_admin)
          <a href="{{ route('report.create') }}"
             class="inline-flex items-center gap-2 px-4 py-2 rounded-xl shadow hover:shadow-md bg-amber-600 text-white hover:bg-amber-700 transition self-start md:self-auto">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11 5h2v14h-2zM5 11h14v2H5z"/></svg>
            New Report
          </a>
        @endif
      </div>
    </header>

    {{-- Flash --}}
    @if(session('success'))
      <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 shadow-sm">
        {{ session('success') }}
      </div>
    @endif

    {{-- Filters + Actions (one box) --}}
    <div class="mb-6 rounded-2xl bg-white/85 backdrop-blur ring-1 ring-amber-900/10 shadow p-4">
      <form method="GET" action="{{ route('reports.index') }}"
            class="grid gap-3 items-center
                   grid-cols-1
                   sm:grid-cols-2
                   lg:grid-cols-6
                   xl:grid-cols-8">

        {{-- Search --}}
        <div class="flex items-center gap-2 col-span-1 sm:col-span-2 lg:col-span-3 xl:col-span-3">
          <svg class="h-5 w-5 flex-none text-amber-900/60" viewBox="0 0 24 24" fill="currentColor"><path d="M10 4a6 6 0 104.47 10.03l3.75 3.75 1.41-1.41-3.75-3.75A6 6 0 0010 4zm0 2a4 4 0 110 8 4 4 0 010-8z"/></svg>
          <input type="search" name="q" value="{{ $q ?? '' }}" placeholder="Search title or description…"
                 class="w-full rounded-xl border border-amber-200 px-3 py-2 focus:ring-2 focus:ring-amber-300">
        </div>

        {{-- City --}}
        <select name="city_corporation"
                class="w-full rounded-xl border border-amber-200 px-3 py-2 focus:ring-2 focus:ring-amber-300">
          <option value="">All cities</option>
          @foreach(($cities ?? collect()) as $c)
            <option value="{{ $c }}" @selected(($city ?? '') === $c)>{{ $c }}</option>
          @endforeach
        </select>

        {{-- Category --}}
        <select name="category"
                class="w-full rounded-xl border border-amber-200 px-3 py-2 focus:ring-2 focus:ring-amber-300">
          <option value="">All categories</option>
          @foreach(($categories ?? collect()) as $categ)
            <option value="{{ $categ }}" @selected((($category ?? $cat ?? '') === $categ))>{{ $categ }}</option>
          @endforeach
        </select>

        {{-- Status --}}
        <select name="status"
                class="w-full rounded-xl border border-amber-200 px-3 py-2 focus:ring-2 focus:ring-amber-300">
          <option value="">All statuses</option>
          @foreach(($statuses ?? ['pending','in_progress','resolved','rejected']) as $s)
            <option value="{{ $s }}" @selected(($status ?? '') === $s)>{{ \Illuminate\Support\Str::headline($s) }}</option>
          @endforeach
        </select>

        {{-- Actions cluster --}}
        <div class="col-span-1 sm:col-span-2 lg:col-span-3 xl:col-span-3 flex flex-wrap items-center justify-end gap-2">
          {{-- Per page --}}
          <label for="per_page" class="sr-only">Results per page</label>
          <select id="per_page" name="per_page"
                  class="w-full sm:w-auto rounded-xl border border-amber-200 px-3 py-2 focus:ring-2 focus:ring-amber-300">
            @foreach([12,18,24,30,36,48] as $pp)
              <option value="{{ $pp }}" @selected((int)request('per_page',12)===$pp)>Show {{ $pp }}</option>
            @endforeach
          </select>

          {{-- Sort --}}
          @php $sort = request('sort','newest'); @endphp
          <label for="sort" class="sr-only">Sort by</label>
          <select id="sort" name="sort"
                  class="w-full sm:w-auto rounded-xl border border-amber-200 px-3 py-2 focus:ring-2 focus:ring-amber-300">
            <option value="newest"   @selected($sort==='newest')>Newest first</option>
            <option value="oldest"   @selected($sort==='oldest')>Oldest first</option>
            <option value="status"   @selected($sort==='status')>Status (A→Z)</option>
            <option value="city"     @selected($sort==='city')>City (A→Z)</option>
            <option value="category" @selected($sort==='category')>Category (A→Z)</option>
          </select>

          {{-- Apply --}}
          <button type="submit" name="apply" value="1"
                  class="flex-none rounded-xl bg-gradient-to-r from-amber-600 to-rose-600 text-white font-semibold px-4 py-2 shadow hover:shadow-lg">
            Apply
          </button>

          {{-- Clear --}}
          <a href="{{ route('reports.index') }}"
             class="flex-none rounded-xl px-3 py-2 bg-white ring-1 ring-amber-900/10 text-amber-900/90 shadow hover:shadow-md">
            Clear
          </a>

          {{-- Copy filtered link --}}
          <button type="button" id="copyLinkBtn"
                  class="flex-none rounded-xl px-3 py-2 bg-white ring-1 ring-amber-900/10 text-amber-900/90 shadow hover:shadow-md">
            Copy link
          </button>
        </div>

        {{-- Active chips (full width row) --}}
        @if(($q ?? '') || ($city ?? '') || (($category ?? $cat ?? '') !== '') || (($status ?? '') !== ''))
          <div class="sm:col-span-2 lg:col-span-6 xl:col-span-8 mt-1 flex flex-wrap items-center gap-2 text-sm">
            <span class="text-amber-900/70">Active:</span>
            @if($q)    <span class="px-2 py-1 rounded-lg bg-amber-100 text-amber-900">Search: “{{ $q }}”</span>@endif
            @if($city) <span class="px-2 py-1 rounded-lg bg-amber-100 text-amber-900">City: {{ $city }}</span>@endif
            @php $showCat = ($category ?? $cat ?? ''); @endphp
            @if($showCat !== '') <span class="px-2 py-1 rounded-lg bg-amber-100 text-amber-900">Category: {{ $showCat }}</span>@endif
            @if(($status ?? '') !== '') <span class="px-2 py-1 rounded-lg bg-amber-100 text-amber-900">Status: {{ \Illuminate\Support\Str::headline($status) }}</span>@endif
          </div>
        @endif
      </form>
    </div>

    @php
      $badge = function($status) {
        $map = [
          'pending'     => 'bg-amber-100 text-amber-800 ring-amber-200',
          'in_progress' => 'bg-blue-100 text-blue-800 ring-blue-200',
          'resolved'    => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
          'rejected'    => 'bg-rose-100 text-rose-800 ring-rose-200',
        ];
        $cls = $map[$status] ?? 'bg-gray-100 text-gray-800 ring-gray-200';
        return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ring-1 ring-inset '.$cls.'">'.\Illuminate\Support\Str::headline($status).'</span>';
      };
    @endphp

    {{-- List --}}
    @if($reports->isEmpty())
      <div class="rounded-2xl border border-dashed border-amber-300 bg-white/60 backdrop-blur px-6 py-12 text-center shadow">
        <div class="mx-auto mb-3 inline-flex h-12 w-12 items-center justify-center rounded-full ring-1 ring-amber-200 bg-amber-50">
          <svg class="h-5 w-5 text-amber-700" viewBox="0 0 24 24" fill="currentColor"><path d="M3 5h18v2H3zM3 10h18v2H3zM3 15h12v2H3z"/></svg>
        </div>
        <h3 class="text-lg font-semibold text-amber-800">No reports match your filters</h3>
        <p class="text-sm text-amber-900/70 mt-1">Try adjusting search terms or clearing filters.</p>
        <a href="{{ route('reports.index') }}" class="mt-4 inline-flex px-4 py-2 rounded-xl bg-amber-600 text-white hover:bg-amber-700">Reset filters</a>
      </div>
    @else
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($reports as $report)
          <div class="group rounded-2xl bg-white/80 backdrop-blur shadow hover:shadow-lg transition overflow-hidden ring-1 ring-amber-100">
            <div class="p-5 flex flex-col gap-3">
              <div class="flex items-start justify-between gap-3">
                <h3 class="text-lg font-bold text-gray-900 leading-snug line-clamp-2">
                  {{ $report->title }}
                </h3>
                {!! $badge($report->status ?? 'pending') !!}
              </div>

              <ul class="text-sm text-gray-700 space-y-1">
                <li class="flex items-center gap-2">
                  <svg class="h-4 w-4 text-amber-800/80" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8 2 5 5 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-4-3-7-7-7zm0 9.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z"/></svg>
                  <span class="font-medium">{{ $report->location ?? 'N/A' }}</span>
                </li>
                <li class="flex items-center gap-2">
                  <svg class="h-4 w-4 text-amber-800/80" viewBox="0 0 24 24" fill="currentColor"><path d="M4 6h16v2H4zM4 10h16v8H4z"/></svg>
                  <span>{{ $report->category ?? 'General' }}</span>
                </li>
                <li class="flex items-center gap-2">
                  <svg class="h-4 w-4 text-amber-800/80" viewBox="0 0 24 24" fill="currentColor"><path d="M5 4h14v2H5zM5 8h14v12H5z"/></svg>
                  <span>{{ $report->city_corporation ?? '—' }}</span>
                </li>
                <li class="flex items-center gap-2">
                  <svg class="h-4 w-4 text-amber-800/80" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zM4 22v-2c0-2.2 3.8-3.3 6-3.3s6 1.1 6 3.3v2H4z"/></svg>
                  <span>{{ $report->user->name ?? 'Unknown' }}</span>
                </li>
                <li class="flex items-center gap-2">
                  <svg class="h-4 w-4 text-amber-800/80" viewBox="0 0 24 24" fill="currentColor"><path d="M7 2h10v2H7zM5 6h14v14H5zM9 8h6v6H9z"/></svg>
                  <span>{{ optional($report->created_at)->format('M d, Y h:i a') }}</span>
                </li>
              </ul>

              <div class="mt-3 flex items-center justify-between">
                <a href="{{ route('reports.show', $report) }}"
                   class="inline-flex items-center gap-1 text-amber-700 hover:text-amber-800 font-medium">
                  View details
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M10 6l6 6-6 6-1.4-1.4L12.2 12 8.6 7.4z"/></svg>
                </a>
                @if(!auth()->user()->is_admin && Route::has('reports.my'))
                  <a href="{{ route('reports.my') }}" class="text-sm text-gray-500 hover:text-gray-700">My reports</a>
                @endif
              </div>
            </div>
          </div>
        @endforeach
      </div>

      @if(method_exists($reports,'links'))
        <div class="mt-6">
          {{ $reports->links() }}
        </div>
      @endif
    @endif
  </div>
</div>

@push('scripts')
<script>
  (function () {
    const form = document.querySelector('form[action="{{ route('reports.index') }}"]');
    ['per_page','sort'].forEach(id => {
      const el = document.getElementById(id);
      if (el && form) el.addEventListener('change', () => form.requestSubmit());
    });

    const copyBtn = document.getElementById('copyLinkBtn');
    copyBtn?.addEventListener('click', async () => {
      try {
        await navigator.clipboard.writeText(location.href);
        const old = copyBtn.textContent;
        copyBtn.textContent = 'Copied!';
        setTimeout(() => (copyBtn.textContent = old), 1200);
      } catch {}
    });
  })();
</script>
@endpush
@endsection
