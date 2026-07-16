@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

  {{-- ── Welcome header ──────────────────────────────────────────────── --}}
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
      <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
        Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 18 ? 'afternoon' : 'evening') }},
        <span class="text-emerald-600 dark:text-emerald-400">{{ auth()->user()->name }}</span>
      </h1>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Here's a summary of your reports and activity.</p>
    </div>
    <a href="{{ route('reports.create') }}"
       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm
              bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm transition self-start sm:self-auto">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
      </svg>
      New Report
    </a>
  </div>

  {{-- ── Stat cards ───────────────────────────────────────────────────── --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    @php
      $cards = [
        ['label'=>'Total','value'=>$stats['total'],'sub'=>'All submissions','icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z','ibg'=>'bg-blue-50 dark:bg-blue-900/30','ic'=>'text-blue-600 dark:text-blue-400','val'=>'text-blue-700 dark:text-blue-300'],
        ['label'=>'Pending','value'=>$stats['pending'],'sub'=>'Awaiting review','icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z','ibg'=>'bg-amber-50 dark:bg-amber-900/30','ic'=>'text-amber-600 dark:text-amber-400','val'=>'text-amber-700 dark:text-amber-300'],
        ['label'=>'In Progress','value'=>$stats['in_progress'],'sub'=>'Being addressed','icon'=>'M13 10V3L4 14h7v7l9-11h-7z','ibg'=>'bg-indigo-50 dark:bg-indigo-900/30','ic'=>'text-indigo-600 dark:text-indigo-400','val'=>'text-indigo-700 dark:text-indigo-300'],
        ['label'=>'Resolved','value'=>$stats['resolved'],'sub'=>'Successfully closed','icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z','ibg'=>'bg-emerald-50 dark:bg-emerald-900/30','ic'=>'text-emerald-600 dark:text-emerald-400','val'=>'text-emerald-700 dark:text-emerald-300'],
      ];
    @endphp
    @foreach($cards as $c)
      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-3">
          <div class="w-9 h-9 rounded-lg {{ $c['ibg'] }} flex items-center justify-center">
            <svg class="w-5 h-5 {{ $c['ic'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="{{ $c['icon'] }}"/>
            </svg>
          </div>
        </div>
        <div class="text-2xl font-black {{ $c['val'] }}">{{ $c['value'] }}</div>
        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mt-0.5">{{ $c['label'] }}</div>
        <div class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $c['sub'] }}</div>
      </div>
    @endforeach
  </div>

  {{-- ── Quick actions ────────────────────────────────────────────────── --}}
  <div class="mb-8">
    <h2 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-4">Quick Actions</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
      @php
        $actions = [
          ['href'=>route('reports.create'),'label'=>'New Report','sub'=>'File an issue','icon'=>'M12 4v16m8-8H4','c'=>'text-emerald-600','bg'=>'bg-emerald-50 dark:bg-emerald-900/20'],
          ['href'=>route('reports.index'),'label'=>'All Reports','sub'=>'Browse public feed','icon'=>'M4 6h16M4 10h16M4 14h16M4 18h16','c'=>'text-blue-600','bg'=>'bg-blue-50 dark:bg-blue-900/20'],
          ['href'=>Route::has('reports.my') ? route('reports.my') : route('reports.index'),'label'=>'My Reports','sub'=>'Track your reports','icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2','c'=>'text-violet-600','bg'=>'bg-violet-50 dark:bg-violet-900/20'],
          ['href'=>route('legal.rti.form'),'label'=>'RTI Request','sub'=>'Right to Information','icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z','c'=>'text-rose-600','bg'=>'bg-rose-50 dark:bg-rose-900/20'],
          ['href'=>route('bookmarks.index'),'label'=>'Saved','sub'=>'Bookmarked reports','icon'=>'M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z','c'=>'text-amber-600','bg'=>'bg-amber-50 dark:bg-amber-900/20'],
          ['href'=>route('profile.edit'),'label'=>'Profile','sub'=>'Account settings','icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z','c'=>'text-slate-600','bg'=>'bg-slate-100 dark:bg-slate-700'],
        ];
      @endphp
      @foreach($actions as $a)
        <a href="{{ $a['href'] }}"
           class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 flex items-center gap-3 hover:border-slate-300 dark:hover:border-slate-600 hover:shadow-sm transition group">
          <div class="w-10 h-10 rounded-lg {{ $a['bg'] }} flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
            <svg class="w-5 h-5 {{ $a['c'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="{{ $a['icon'] }}"/>
            </svg>
          </div>
          <div>
            <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $a['label'] }}</div>
            <div class="text-xs text-slate-400 dark:text-slate-500">{{ $a['sub'] }}</div>
          </div>
        </a>
      @endforeach
    </div>
  </div>

  {{-- ── Recent reports ───────────────────────────────────────────────── --}}
  <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700">
      <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">My Recent Reports</h2>
      <a href="{{ route('reports.index') }}" class="text-xs font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">
        View all →
      </a>
    </div>

    @php
      $badge = fn($s) => match($s) {
        'resolved'    => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
        'in_progress' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
        'pending'     => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
        'rejected'    => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
        default       => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
      };
    @endphp

    <div class="divide-y divide-slate-100 dark:divide-slate-700">
      @forelse($recentReports as $report)
        <a href="{{ route('reports.show', $report) }}"
           class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
          <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <div class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">{{ $report->title }}</div>
            <div class="flex items-center gap-2 mt-0.5">
              @if($report->city_corporation)
                <span class="text-xs text-slate-400">{{ $report->city_corporation }}</span>
                <span class="text-slate-300 dark:text-slate-600">·</span>
              @endif
              <span class="text-xs text-slate-400">{{ $report->created_at->diffForHumans() }}</span>
            </div>
          </div>
          <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $badge($report->status) }} flex-shrink-0">
            {{ \Illuminate\Support\Str::headline($report->status) }}
          </span>
        </a>
      @empty
        <div class="px-6 py-16 text-center">
          <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
          <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">No reports yet</h3>
          <p class="text-xs text-slate-400 mb-5">Start making a difference in your community.</p>
          <a href="{{ route('reports.create') }}"
             class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold
                    bg-emerald-600 text-white hover:bg-emerald-700 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Submit Your First Report
          </a>
        </div>
      @endforelse
    </div>
  </div>

</div>
@endsection
