@extends('layouts.admin')
@section('title', 'Reports')

@section('content')
<div class="relative space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div class="min-w-0">
            <h1 class="text-2xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-amber-700 via-orange-700 to-rose-700">
                Reports
            </h1>
            <p class="text-sm text-amber-600">Search, filter and review all incoming reports.</p>
        </div>
    </div>

    {{-- Filters --}}
<form method="GET" action="{{ route('admin.reports.index') }}"
      class="bg-white/85 backdrop-blur rounded-2xl ring-1 ring-amber-900/10 shadow p-4">

  {{-- responsive auto-fit grid; every control becomes a “tile” --}}
  <div class="grid gap-3 grid-cols-[repeat(auto-fit,minmax(220px,1fr))]">

    {{-- Search --}}
    <div class="flex items-center gap-2">
      <svg class="h-5 w-5 text-amber-900/60" viewBox="0 0 24 24" fill="currentColor"><path d="M10 4a6 6 0 104.47 10.03l3.75 3.75 1.41-1.41-3.75-3.75A6 6 0 0010 4zm0 2a4 4 0 110 8 4 4 0 010-8z"/></svg>
      <input type="search" name="q" value="{{ request('q','') }}"
             placeholder="Search title, description, or user…"
             class="w-full rounded-xl border border-amber-200 px-3 py-2 focus:ring-2 focus:ring-amber-300">
    </div>

    {{-- City --}}
    <select name="city"
            class="rounded-xl border border-amber-200 px-3 py-2 focus:ring-2 focus:ring-amber-300">
      <option value="">All cities</option>
      @foreach(($cities ?? []) as $c)
        <option value="{{ $c }}" @selected(request('city')===$c)>{{ $c }}</option>
      @endforeach
    </select>

    {{-- Category --}}
    <select name="category"
            class="rounded-xl border border-amber-200 px-3 py-2 focus:ring-2 focus:ring-amber-300">
      <option value="">All categories</option>
      @foreach(($categories ?? []) as $cat)
        <option value="{{ $cat }}" @selected(request('category')===$cat)>{{ $cat }}</option>
      @endforeach
    </select>

    {{-- Status --}}
    <select name="status"
            class="rounded-xl border border-amber-200 px-3 py-2 focus:ring-2 focus:ring-amber-300">
      <option value="">All statuses</option>
      @php $statuses = ['pending','in_progress','resolved','rejected']; @endphp
      @foreach($statuses as $s)
        <option value="{{ $s }}" @selected(request('status')===$s)>{{ \Illuminate\Support\Str::headline($s) }}</option>
      @endforeach
    </select>

    {{-- Date range --}}
    <input type="date" name="from" value="{{ request('from') }}"
           class="rounded-xl border border-amber-200 px-3 py-2 focus:ring-2 focus:ring-amber-300">
    <input type="date" name="to" value="{{ request('to') }}"
           class="rounded-xl border border-amber-200 px-3 py-2 focus:ring-2 focus:ring-amber-300">

    {{-- Sort --}}
    @php $sort = request('sort','newest'); @endphp
    <select id="sort" name="sort"
            class="rounded-xl border border-amber-200 px-3 py-2 focus:ring-2 focus:ring-amber-300">
      <option value="newest"   @selected($sort==='newest')>Newest first</option>
      <option value="oldest"   @selected($sort==='oldest')>Oldest first</option>
      <option value="status"   @selected($sort==='status')>Status (A→Z)</option>
      <option value="city"     @selected($sort==='city')>City (A→Z)</option>
      <option value="category" @selected($sort==='category')>Category (A→Z)</option>
    </select>

    {{-- Actions: ALWAYS inside the box --}}
    <div class="col-span-full flex flex-wrap items-center justify-end gap-2 pt-1">
      <label for="per_page" class="sr-only">Results per page</label>
      <select id="per_page" name="per_page"
              class="rounded-xl border border-amber-200 px-3 py-2 focus:ring-2 focus:ring-amber-300">
        @foreach([12,18,24,30,36,48] as $pp)
          <option value="{{ $pp }}" @selected((int)request('per_page',12)===$pp)>Show {{ $pp }}</option>
        @endforeach
      </select>

      <button type="submit"
              class="rounded-xl bg-gradient-to-r from-amber-600 to-rose-600 text-white font-semibold px-4 py-2 shadow hover:shadow-lg">
        Apply
      </button>

      <a href="{{ route('admin.reports.index') }}"
         class="rounded-xl px-3 py-2 bg-white ring-1 ring-amber-900/10 text-amber-900/90 shadow hover:shadow-md">
        Clear
      </a>

      <button type="button" id="copyLinkBtn"
              class="rounded-xl px-3 py-2 bg-white ring-1 ring-amber-900/10 text-amber-900/90 shadow hover:shadow-md">
        Copy link
      </button>
    </div>
  </div>
</form>


    {{-- Table --}}
    <div class="rounded-2xl bg-white/85 backdrop-blur ring-1 ring-amber-900/10 shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-amber-100 text-amber-900 uppercase text-xs tracking-wider">
                        <th class="px-4 py-3 text-left">Title</th>
                        <th class="px-4 py-3 text-left">User</th>
                        <th class="px-4 py-3 text-left">City</th>
                        <th class="px-4 py-3 text-left">Category</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Created</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber-900/10">
                    @php
                        $badge = fn($s) => match($s){
                            'resolved'    => 'bg-emerald-200 text-emerald-800',
                            'in_progress' => 'bg-blue-200 text-blue-800',
                            'pending'     => 'bg-yellow-200 text-yellow-800',
                            'rejected'    => 'bg-rose-200 text-rose-800',
                            default       => 'bg-gray-200 text-gray-800',
                        };
                    @endphp
                    @forelse($reports as $report)
                        <tr class="hover:bg-amber-50/50">
                            <td class="px-4 py-3 font-medium max-w-[360px] truncate">
                                <a href="{{ route('admin.reports.show', $report) }}" class="text-rose-700 hover:underline">
                                    {{ $report->title }}
                                </a>
                            </td>
                            <td class="px-4 py-3">{{ $report->user->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ $report->city_corporation ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $report->category ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full font-semibold {{ $badge($report->status) }}">
                                    {{ \Illuminate\Support\Str::headline($report->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ optional($report->created_at)->format('M d, Y h:i a') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.reports.show', $report) }}"
                                   class="inline-flex items-center gap-2 rounded-xl px-3 py-1.5 text-sm text-white
                                          bg-gradient-to-r from-amber-600 to-rose-600 shadow hover:shadow-lg transition">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-amber-900/70">No reports found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if(method_exists($reports, 'links'))
            <div class="px-4 py-3 border-t border-amber-900/10">
                {{ $reports->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
(function () {
  const form = document.querySelector('form[action="{{ route('admin.reports.index') }}"]');
  // auto-submit on simple controls
  ['per_page','sort'].forEach(id => {
    const el = form?.querySelector('#'+id);
    if (el) el.addEventListener('change', () => form.requestSubmit());
  });

  const copyBtn = document.getElementById('copyLinkBtn');
  copyBtn?.addEventListener('click', async () => {
    try {
      await navigator.clipboard.writeText(location.href);
      copyBtn.textContent = 'Copied!';
      setTimeout(() => copyBtn.textContent = 'Copy link', 1200);
    } catch {}
  });
})();
</script>
@endpush
@endsection
