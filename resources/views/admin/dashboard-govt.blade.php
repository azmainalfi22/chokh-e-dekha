@extends('layouts.admin')
@section('title', 'Command Center')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

  {{-- ── Header ───────────────────────────────────────────────── --}}
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
      <div class="flex items-center gap-2 mb-1">
        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
        <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wide">Live Dashboard</span>
      </div>
      <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Command Center</h1>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Real-time civic issue monitoring — {{ now()->format('l, d M Y') }}</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('admin.reports.index') }}"
         class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        All Reports
      </a>
      <button onclick="window.location.reload()"
              class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Refresh
      </button>
    </div>
  </div>

  {{-- ── KPI Cards ────────────────────────────────────────────── --}}
  <div class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-8">
    @foreach([
      ['label'=>'Total',      'value'=>$totals['total'],       'color'=>'slate',   'icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
      ['label'=>'Pending',    'value'=>$totals['pending'],     'color'=>'amber',   'icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
      ['label'=>'In Progress','value'=>$totals['in_progress'], 'color'=>'indigo',  'icon'=>'M13 10V3L4 14h7v7l9-11h-7z'],
      ['label'=>'Resolved',   'value'=>$totals['resolved'],    'color'=>'emerald', 'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
      ['label'=>'Rejected',   'value'=>$totals['rejected'],    'color'=>'red',     'icon'=>'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
      ['label'=>'SLA Breach', 'value'=>$totals['sla_breached'],'color'=>'rose',   'icon'=>'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
    ] as $kpi)
      @php
        $cols = ['slate'=>'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400','amber'=>'bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400','indigo'=>'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400','emerald'=>'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400','red'=>'bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400','rose'=>'bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400'];
        $vals = ['slate'=>'text-slate-800 dark:text-slate-200','amber'=>'text-amber-700 dark:text-amber-400','indigo'=>'text-indigo-700 dark:text-indigo-400','emerald'=>'text-emerald-700 dark:text-emerald-400','red'=>'text-red-700 dark:text-red-400','rose'=>'text-rose-700 dark:text-rose-400'];
      @endphp
      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4">
        <div class="flex items-center justify-between mb-2">
          <div class="w-8 h-8 rounded-lg {{ $cols[$kpi['color']] }} flex items-center justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $kpi['icon'] }}"/></svg>
          </div>
          @if($kpi['label'] === 'SLA Breach' && $kpi['value'] > 0)
            <span class="text-xs px-1.5 py-0.5 rounded-full bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 font-bold">!</span>
          @endif
        </div>
        <div class="text-xl font-black {{ $vals[$kpi['color']] }}">{{ number_format($kpi['value']) }}</div>
        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-0.5">{{ $kpi['label'] }}</div>
      </div>
    @endforeach
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    {{-- ── SLA Breaches ──────────────────────────────────────── --}}
    <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl border border-rose-200 dark:border-rose-800 shadow-sm overflow-hidden">
      <div class="flex items-center justify-between px-5 py-4 border-b border-rose-100 dark:border-rose-800 bg-rose-50 dark:bg-rose-900/20">
        <div class="flex items-center gap-2">
          <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
          <h2 class="text-sm font-bold text-rose-800 dark:text-rose-300">SLA Breaches — Over 7 Days</h2>
        </div>
        <span class="text-xs font-bold text-rose-600 dark:text-rose-400 bg-rose-100 dark:bg-rose-900/40 px-2 py-0.5 rounded-full">{{ $slaBreached->count() }}</span>
      </div>
      @if($slaBreached->isEmpty())
        <div class="flex items-center justify-center py-12 text-sm text-slate-400">
          <svg class="w-5 h-5 mr-2 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
          No SLA breaches — all reports within 7-day window!
        </div>
      @else
        <div class="divide-y divide-slate-100 dark:divide-slate-700">
          @foreach($slaBreached as $r)
            <a href="{{ route('admin.reports.show', $r) }}"
               class="flex items-center gap-4 px-5 py-3.5 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors group">
              <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-rose-100 dark:bg-rose-900/40 flex items-center justify-center">
                <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
              <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-slate-800 dark:text-slate-200 truncate group-hover:text-rose-600 transition-colors">{{ $r->title }}</div>
                <div class="text-xs text-slate-400 mt-0.5">{{ $r->city_corporation ?? 'Unknown City' }} · {{ $r->category ?? 'Uncategorized' }}</div>
              </div>
              <div class="flex-shrink-0 text-right">
                <div class="text-xs font-bold text-rose-600 dark:text-rose-400">{{ $r->created_at->diffInDays(now()) }}d overdue</div>
                <div class="text-xs text-slate-400">{{ $r->created_at->format('M d') }}</div>
              </div>
            </a>
          @endforeach
        </div>
      @endif
    </div>

    {{-- ── Weekly Trend ──────────────────────────────────────── --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
      <h2 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4">7-Day Submission Trend</h2>
      @php $maxCount = $weeklyTrend->max('count') ?: 1; @endphp
      <div class="space-y-2">
        @foreach($weeklyTrend as $day)
          <div class="flex items-center gap-3">
            <div class="text-xs text-slate-400 w-10 flex-shrink-0">{{ $day['date'] }}</div>
            <div class="flex-1 bg-slate-100 dark:bg-slate-700 rounded-full h-5 overflow-hidden">
              <div class="h-full bg-emerald-500 rounded-full transition-all duration-500"
                   style="width: {{ $maxCount > 0 ? round(($day['count'] / $maxCount) * 100) : 0 }}%"></div>
            </div>
            <div class="text-xs font-semibold text-slate-600 dark:text-slate-400 w-4 text-right">{{ $day['count'] }}</div>
          </div>
        @endforeach
      </div>
      <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700 text-xs text-slate-400">
        Total this week: <span class="font-bold text-slate-700 dark:text-slate-300">{{ $weeklyTrend->sum('count') }}</span> reports
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    {{-- ── City Breakdown ────────────────────────────────────── --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
        <h2 class="text-sm font-bold text-slate-700 dark:text-slate-300">By City Corporation</h2>
      </div>
      @if($cityStats->isEmpty())
        <div class="py-10 text-center text-sm text-slate-400">No geo-tagged reports yet.</div>
      @else
        <div class="divide-y divide-slate-100 dark:divide-slate-700">
          @foreach($cityStats as $city)
            @php $pct = $totals['total'] > 0 ? round(($city->total / $totals['total']) * 100) : 0; @endphp
            <div class="px-5 py-3.5">
              <div class="flex items-center justify-between mb-1.5">
                <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 truncate max-w-[160px]">{{ $city->city_corporation ?? 'Unknown' }}</div>
                <div class="text-xs text-slate-500">{{ $city->total }} <span class="text-slate-400">({{ $pct }}%)</span></div>
              </div>
              <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2">
                <div class="h-2 bg-emerald-500 rounded-full" style="width:{{ $pct }}%"></div>
              </div>
              <div class="flex items-center gap-3 mt-1.5 text-xs text-slate-400">
                <span class="text-amber-500">⏳ {{ $city->pending }}</span>
                <span class="text-indigo-500">⚡ {{ $city->in_progress }}</span>
                <span class="text-emerald-500">✓ {{ $city->resolved }}</span>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>

    {{-- ── Category Breakdown ────────────────────────────────── --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
        <h2 class="text-sm font-bold text-slate-700 dark:text-slate-300">By Category</h2>
      </div>
      @if($categoryStats->isEmpty())
        <div class="py-10 text-center text-sm text-slate-400">No categorized reports yet.</div>
      @else
        @php
          $categoryColors = ['bg-blue-500','bg-emerald-500','bg-amber-500','bg-violet-500','bg-rose-500','bg-indigo-500','bg-teal-500','bg-orange-500'];
          $maxCat = $categoryStats->max('total') ?: 1;
        @endphp
        <div class="divide-y divide-slate-100 dark:divide-slate-700">
          @foreach($categoryStats as $i => $cat)
            <div class="px-5 py-3.5 flex items-center gap-3">
              <div class="w-2.5 h-2.5 rounded-full {{ $categoryColors[$i % count($categoryColors)] }} flex-shrink-0"></div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-1">
                  <span class="text-xs font-medium text-slate-700 dark:text-slate-300 capitalize truncate">{{ $cat->category }}</span>
                  <span class="text-xs text-slate-400 ml-2 flex-shrink-0">{{ $cat->total }}</span>
                </div>
                <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-1.5">
                  <div class="{{ $categoryColors[$i % count($categoryColors)] }} h-1.5 rounded-full" style="width:{{ round(($cat->total / $maxCat) * 100) }}%"></div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>

    {{-- ── Recent Activity ───────────────────────────────────── --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
      <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-700">
        <h2 class="text-sm font-bold text-slate-700 dark:text-slate-300">Recent Activity</h2>
        <a href="{{ route('admin.reports.index') }}" class="text-xs text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 font-medium">View all →</a>
      </div>
      <div class="divide-y divide-slate-100 dark:divide-slate-700">
        @forelse($recentActivity as $r)
          @php
            $statusColor = match($r->status) {
              'resolved'    => 'bg-emerald-500',
              'in_progress' => 'bg-indigo-500',
              'pending'     => 'bg-amber-500',
              'rejected'    => 'bg-red-500',
              default       => 'bg-slate-400',
            };
          @endphp
          <a href="{{ route('admin.reports.show', $r) }}"
             class="flex items-center gap-3 px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
            <div class="flex-shrink-0 w-2 h-2 rounded-full {{ $statusColor }}"></div>
            <div class="flex-1 min-w-0">
              <div class="text-xs font-medium text-slate-700 dark:text-slate-300 truncate">{{ $r->title }}</div>
              <div class="text-xs text-slate-400">{{ $r->user->name ?? 'Anon' }} · {{ $r->updated_at->diffForHumans() }}</div>
            </div>
          </a>
        @empty
          <div class="py-8 text-center text-sm text-slate-400">No recent activity.</div>
        @endforelse
      </div>
    </div>
  </div>

  {{-- ── Pending Queue ─────────────────────────────────────────── --}}
  <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden mb-6">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-700">
      <div>
        <h2 class="text-sm font-bold text-slate-700 dark:text-slate-300">Pending Approval Queue</h2>
        <p class="text-xs text-slate-400 mt-0.5">Reports waiting for admin review — act now to meet SLA</p>
      </div>
      <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400">
        {{ $totals['pending'] }} pending
      </span>
    </div>
    @if($pendingQueue->isEmpty())
      <div class="py-10 text-center text-sm text-slate-400">
        <svg class="w-8 h-8 mx-auto mb-2 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Queue is clear — no pending reports!
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="w-full text-sm border-collapse">
          <thead>
            <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
              <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Report</th>
              <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Reporter</th>
              <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">City</th>
              <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Category</th>
              <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Wait</th>
              <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            @foreach($pendingQueue as $r)
              @php $days = $r->created_at->diffInDays(now()); @endphp
              <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                <td class="px-5 py-3.5 max-w-xs">
                  <a href="{{ route('admin.reports.show', $r) }}" class="font-medium text-slate-800 dark:text-slate-200 hover:text-emerald-600 dark:hover:text-emerald-400 line-clamp-1 transition-colors">{{ $r->title }}</a>
                  <div class="text-xs text-slate-400 mt-0.5">#{{ $r->id }}</div>
                </td>
                <td class="px-4 py-3.5 text-xs text-slate-600 dark:text-slate-400">{{ $r->user->name ?? 'Anonymous' }}</td>
                <td class="px-4 py-3.5 text-xs text-slate-500 dark:text-slate-400">{{ $r->city_corporation ?? '—' }}</td>
                <td class="px-4 py-3.5">
                  @if($r->category)
                    <span class="px-2 py-0.5 text-xs rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 capitalize">{{ $r->category }}</span>
                  @else
                    <span class="text-xs text-slate-400">—</span>
                  @endif
                </td>
                <td class="px-4 py-3.5">
                  <span class="text-xs font-semibold {{ $days >= 7 ? 'text-rose-600 dark:text-rose-400' : ($days >= 3 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-500 dark:text-slate-400') }}">
                    {{ $days > 0 ? $days.'d' : 'today' }}
                  </span>
                </td>
                <td class="px-4 py-3.5">
                  <a href="{{ route('admin.reports.show', $r) }}"
                     class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition-colors">
                    Review
                  </a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @if($totals['pending'] > $pendingQueue->count())
        <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-700 text-xs text-slate-400 flex items-center justify-between">
          <span>Showing {{ $pendingQueue->count() }} of {{ $totals['pending'] }} pending reports</span>
          <a href="{{ route('admin.reports.index', ['status'=>'pending']) }}" class="text-emerald-600 dark:text-emerald-400 font-medium hover:underline">View all →</a>
        </div>
      @endif
    @endif
  </div>

  {{-- ── Resolution Rate by City (visual) ─────────────────────── --}}
  @if($cityStats->isNotEmpty())
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
      <h2 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-5">Resolution Rate by City</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($cityStats->take(8) as $city)
          @php $rate = $city->total > 0 ? round(($city->resolved / $city->total) * 100) : 0; @endphp
          <div class="text-center">
            <div class="relative inline-flex items-center justify-center w-16 h-16 mb-2">
              <svg class="w-16 h-16 -rotate-90" viewBox="0 0 36 36">
                <circle cx="18" cy="18" r="15.9" fill="none" stroke="#e2e8f0" stroke-width="2.5" class="dark:stroke-slate-700"/>
                <circle cx="18" cy="18" r="15.9" fill="none"
                        stroke="{{ $rate >= 70 ? '#10b981' : ($rate >= 40 ? '#f59e0b' : '#ef4444') }}"
                        stroke-width="2.5"
                        stroke-dasharray="{{ $rate }}, 100"
                        stroke-linecap="round"/>
              </svg>
              <span class="absolute text-xs font-bold text-slate-800 dark:text-slate-200">{{ $rate }}%</span>
            </div>
            <div class="text-xs font-medium text-slate-700 dark:text-slate-300 truncate max-w-full px-1">{{ $city->city_corporation ?? 'Unknown' }}</div>
            <div class="text-xs text-slate-400">{{ $city->total }} total</div>
          </div>
        @endforeach
      </div>
    </div>
  @endif

</div>
@endsection
