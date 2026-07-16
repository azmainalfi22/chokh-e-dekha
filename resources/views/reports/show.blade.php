@extends('layouts.app')
@section('title', $report->title)

@section('content')
@php
  $badge = fn($s) => match($s) {
    'resolved'    => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
    'in_progress' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
    'pending'     => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    'rejected'    => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
    default       => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
  };
@endphp

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

  {{-- ── Back nav ────────────────────────────────────────────────────── --}}
  <a href="{{ route('reports.index') }}"
     class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors mb-5">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
    </svg>
    Back to Reports
  </a>

  {{-- ── Status Progress Tracker ─────────────────────────────────────── --}}
  @php
    $statusOrder = ['pending'=>0,'in_progress'=>1,'resolved'=>2];
    $currentOrder = $statusOrder[$report->status] ?? 0;
    $isRejected = $report->status === 'rejected';
    $slaDue = $report->sla_due_at ?? $report->created_at->addDays(7);
    $daysUntilSla = (int) now()->diffInDays($slaDue, false);
    if ($daysUntilSla < 0 && !$slaBreached) $daysUntilSla = 0;
    $slaBreached = $slaDue->isPast() && !in_array($report->status, ['resolved','rejected']);
  @endphp
  @if(!$isRejected)
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5 mb-5">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-bold text-slate-700 dark:text-slate-300">Report Progress</h2>
        @if($slaBreached)
          <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Response Overdue
          </span>
        @elseif($report->status !== 'resolved')
          <span class="text-xs text-slate-400 dark:text-slate-500">Expected response within {{ max(0, $daysUntilSla) }} {{ max(0, $daysUntilSla) === 1 ? 'day' : 'days' }}</span>
        @endif
      </div>

      {{-- Stepper --}}
      <div class="flex items-center">
        @php
          $steps = [
            ['label'=>'Submitted', 'icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['label'=>'In Progress','icon'=>'M13 10V3L4 14h7v7l9-11h-7z'],
            ['label'=>'Resolved',  'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
          ];
        @endphp
        @foreach($steps as $i => $step)
          @php $done = $currentOrder >= $i; $active = $currentOrder === $i; @endphp
          <div class="flex items-center {{ $loop->last ? 'flex-shrink-0' : 'flex-1' }}">
            <div class="flex flex-col items-center gap-1.5">
              <div class="w-9 h-9 rounded-full flex items-center justify-center transition-all
                {{ $done ? ($active ? 'bg-emerald-600 ring-4 ring-emerald-100 dark:ring-emerald-900/40' : 'bg-emerald-600') : 'bg-slate-100 dark:bg-slate-700' }}">
                <svg class="w-4 h-4 {{ $done ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="{{ $step['icon'] }}"/>
                </svg>
              </div>
              <span class="text-xs font-semibold whitespace-nowrap {{ $done ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500' }}">
                {{ $step['label'] }}
              </span>
            </div>
            @if(!$loop->last)
              <div class="flex-1 h-0.5 mx-2 mb-5 {{ $currentOrder > $i ? 'bg-emerald-400 dark:bg-emerald-700' : 'bg-slate-200 dark:bg-slate-700' }}"></div>
            @endif
          </div>
        @endforeach
      </div>
    </div>
  @else
    <div class="bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl p-4 mb-5 flex items-center gap-3">
      <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <div>
        <div class="text-sm font-semibold text-rose-700 dark:text-rose-300">Report Rejected</div>
        <div class="text-xs text-rose-500 dark:text-rose-400">This report did not meet the criteria for review.</div>
      </div>
    </div>
  @endif

  {{-- ── Report Header ─────────────────────────────────────────────── --}}
  <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 mb-4">

    {{-- Status + Category badges --}}
    <div class="flex items-center gap-2 flex-wrap mb-4">
      <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $badge($report->status) }}">
        {{ \Illuminate\Support\Str::headline($report->status) }}
      </span>
      @if($report->category)
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300">
          {{ ucfirst($report->category) }}
        </span>
      @endif
      <span class="text-xs text-slate-400 dark:text-slate-500 ml-auto">
        {{ $report->created_at->format('M d, Y • h:i A') }}
      </span>
    </div>

    <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-snug mb-4">{{ $report->title }}</h1>

    {{-- Meta info --}}
    <div class="flex flex-wrap gap-4 text-sm text-slate-500 dark:text-slate-400">
      @if($report->user)
        <span class="flex items-center gap-1.5">
          <div class="w-6 h-6 rounded-full bg-emerald-600 flex items-center justify-center text-white font-bold text-xs">
            {{ strtoupper(substr($report->user->name, 0, 1)) }}
          </div>
          {{ $report->user->name }}
        </span>
      @endif
      @if($report->location)
        <span class="flex items-center gap-1.5">
          <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          {{ $report->location }}
        </span>
      @endif
      @if($report->city_corporation)
        <span class="flex items-center gap-1.5">
          <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
          {{ $report->city_corporation }}
        </span>
      @endif
    </div>
  </div>

  {{-- ── Description ──────────────────────────────────────────────────── --}}
  <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 mb-4">
    <h2 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-3">Description</h2>
    <p class="text-slate-700 dark:text-slate-300 leading-relaxed text-[15px]">{{ $report->description }}</p>
  </div>

  {{-- ── Photo Gallery ─────────────────────────────────────────────────── --}}
  @if($report->photo || (isset($report->media) && $report->media->count() > 0))
    @php
      $allPhotos = collect();
      if ($report->photo) $allPhotos->push((object)['path' => $report->photo, 'is_main' => true]);
      if (isset($report->media)) {
        foreach ($report->media as $media) {
          if (str_starts_with($media->file_type, 'image/')) {
            $allPhotos->push((object)['path' => $media->file_path, 'is_main' => false]);
          }
        }
      }
    @endphp
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden mb-4">
      <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
        <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
          <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          Evidence Photos ({{ $allPhotos->count() }})
        </h2>
      </div>

      @if($allPhotos->count() === 1)
        <div class="relative group cursor-pointer overflow-hidden" onclick="openLightbox('{{ Storage::url($allPhotos[0]->path) }}')">
          <img src="{{ Storage::url($allPhotos[0]->path) }}" alt="{{ $report->title }}"
               class="w-full max-h-[480px] object-cover group-hover:scale-105 transition-transform duration-300">
          <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 flex items-center justify-center transition-all duration-300">
            <svg class="w-12 h-12 text-white opacity-0 group-hover:opacity-100 transition-opacity drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/>
            </svg>
          </div>
        </div>
      @else
        <div class="grid grid-cols-2 md:grid-cols-3 gap-1 p-1">
          @foreach($allPhotos->take(6) as $index => $photo)
            <div class="relative group cursor-pointer overflow-hidden rounded-lg {{ $index === 0 && $allPhotos->count() > 2 ? 'col-span-2 row-span-2' : '' }}"
                 style="{{ ($index === 0 && $allPhotos->count() > 2) ? 'min-height: 320px;' : 'min-height: 160px;' }}"
                 onclick="openLightbox('{{ Storage::url($photo->path) }}')">
              <img src="{{ Storage::url($photo->path) }}" alt="{{ $report->title }}"
                   class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300 absolute inset-0">
              @if($index === 5 && $allPhotos->count() > 6)
                <div class="absolute inset-0 bg-black/60 flex items-center justify-center">
                  <span class="text-white text-3xl font-bold">+{{ $allPhotos->count() - 6 }}</span>
                </div>
              @endif
              <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all duration-300 flex items-center justify-center">
                <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/>
                </svg>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  @endif

  {{-- ── Engagement Stats ─────────────────────────────────────────────── --}}
  <div class="grid grid-cols-4 gap-3 mb-4">
    @foreach([
      ['val'=>$report->likes_count??0,    'label'=>'Likes',    'color'=>'text-red-500',    'bg'=>'bg-red-50 dark:bg-red-900/20',    'icon'=>'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
      ['val'=>$report->comments_count??0, 'label'=>'Comments', 'color'=>'text-blue-500',   'bg'=>'bg-blue-50 dark:bg-blue-900/20',  'icon'=>'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
      ['val'=>number_format($report->views_count??0), 'label'=>'Views', 'color'=>'text-emerald-500', 'bg'=>'bg-emerald-50 dark:bg-emerald-900/20', 'icon'=>'M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'],
      ['val'=>$report->shares_count??0,   'label'=>'Shares',   'color'=>'text-violet-500', 'bg'=>'bg-violet-50 dark:bg-violet-900/20', 'icon'=>'M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z'],
    ] as $stat)
      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4 text-center">
        <div class="w-9 h-9 rounded-lg {{ $stat['bg'] }} flex items-center justify-center mx-auto mb-2">
          <svg class="w-5 h-5 {{ $stat['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $stat['icon'] }}"/>
          </svg>
        </div>
        <div class="text-xl font-bold text-slate-800 dark:text-slate-100">{{ $stat['val'] }}</div>
        <div class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $stat['label'] }}</div>
      </div>
    @endforeach
  </div>

  {{-- ── Official Update (admin note) ─────────────────────────────────── --}}
  @if($report->admin_note)
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-5 mb-4 flex gap-3">
      <div class="w-9 h-9 rounded-lg bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center flex-shrink-0">
        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <div>
        <h3 class="text-sm font-bold text-blue-700 dark:text-blue-300 mb-1">Official Update</h3>
        <p class="text-sm text-blue-600 dark:text-blue-400">{{ $report->admin_note }}</p>
      </div>
    </div>
  @endif

  {{-- ── Action Buttons ────────────────────────────────────────────────── --}}
  <div class="flex items-center gap-2 flex-wrap mb-4">
    @if(auth()->id() === $report->user_id)
      <a href="{{ route('reports.edit', $report) }}"
         class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        Edit
      </a>
    @endif
    <button class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold transition-colors
                   {{ ($report->liked_by_user ?? false) ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400' : 'border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:border-red-200 hover:text-red-500' }}"
            data-action="like" data-report-id="{{ $report->id }}">
      <svg class="w-4 h-4" fill="{{ ($report->liked_by_user ?? false) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
      </svg>
      <span data-likes>{{ $report->likes_count ?? 0 }}</span> Like
    </button>
    <button class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
            data-action="share" data-report-id="{{ $report->id }}" data-title="{{ $report->title }}">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
      </svg>
      Share
    </button>
  </div>

  {{-- ── Activity Timeline ─────────────────────────────────────────────── --}}
  @if(isset($report->logs) && $report->logs && $report->logs->isNotEmpty())
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm mb-4 overflow-hidden">
      <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
        <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Activity Timeline</h2>
      </div>
      <div class="p-6">
        <div class="relative">
          <div class="absolute left-3.5 top-0 bottom-0 w-px bg-slate-200 dark:bg-slate-700"></div>
          <div class="space-y-5">
            @foreach($report->logs as $log)
              <div class="relative pl-10">
                <div class="absolute left-0 w-7 h-7 rounded-full flex items-center justify-center
                  {{ $log->event === 'status_changed' ? 'bg-indigo-100 dark:bg-indigo-900/40' : ($log->event === 'created' ? 'bg-emerald-100 dark:bg-emerald-900/40' : 'bg-amber-100 dark:bg-amber-900/40') }}">
                  <svg class="w-3.5 h-3.5 {{ $log->event === 'status_changed' ? 'text-indigo-600 dark:text-indigo-400' : ($log->event === 'created' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400') }}"
                       fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    @if($log->event === 'status_changed')
                      <path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                    @elseif($log->event === 'created')
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    @else
                      <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    @endif
                  </svg>
                </div>
                <div class="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-4">
                  <div class="flex items-start justify-between gap-2 mb-1">
                    <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                      @if($log->event === 'status_changed') Status Updated
                      @elseif($log->event === 'created') Report Created
                      @else {{ \Illuminate\Support\Str::headline($log->event) }}
                      @endif
                    </h4>
                    <time class="text-xs text-slate-400 dark:text-slate-500 whitespace-nowrap">
                      {{ $log->created_at->diffForHumans() }}
                    </time>
                  </div>
                  @if($log->description)
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $log->description }}</p>
                  @endif
                  @php
                    $meta = isset($log->metadata) ? (is_string($log->metadata) ? json_decode($log->metadata, true) : $log->metadata) : null;
                  @endphp
                  @if($meta && isset($meta['old_status']) && isset($meta['new_status']))
                    <div class="flex items-center gap-2 mt-2">
                      <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badge($meta['old_status']) }}">
                        {{ \Illuminate\Support\Str::headline($meta['old_status']) }}
                      </span>
                      <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                      </svg>
                      <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badge($meta['new_status']) }}">
                        {{ \Illuminate\Support\Str::headline($meta['new_status']) }}
                      </span>
                    </div>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  @endif

  {{-- ── Related Reports ───────────────────────────────────────────────── --}}
  @if(isset($relatedReports) && $relatedReports->isNotEmpty())
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm mb-4 overflow-hidden">
      <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
        <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Related Reports</h2>
      </div>
      <div class="divide-y divide-slate-100 dark:divide-slate-700">
        @foreach($relatedReports as $related)
          <a href="{{ route('reports.show', $related) }}"
             class="flex items-center gap-3 px-6 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors group">
            @if($related->photo)
              <img src="{{ Storage::url($related->photo) }}" alt="{{ $related->title }}"
                   class="w-12 h-12 rounded-lg object-cover flex-shrink-0">
            @else
              <div class="w-12 h-12 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
              </div>
            @endif
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-slate-800 dark:text-slate-100 truncate group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                {{ $related->title }}
              </p>
              <div class="flex items-center gap-2 mt-0.5">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badge($related->status) }}">
                  {{ \Illuminate\Support\Str::headline($related->status) }}
                </span>
                <span class="text-xs text-slate-400">{{ $related->likes_count ?? 0 }} likes</span>
              </div>
            </div>
            <svg class="w-4 h-4 text-slate-300 dark:text-slate-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
          </a>
        @endforeach
      </div>
    </div>
  @endif

  {{-- ── Comments Section ─────────────────────────────────────────────── --}}
  <div id="comments" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
      <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        Comments (<span id="comments-count">{{ $report->comments_count ?? 0 }}</span>)
      </h2>
    </div>

    @auth
      <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/30">
        <form id="comment-form" data-report-id="{{ $report->id }}">
          @csrf
          <div class="flex gap-3">
            <div class="w-9 h-9 rounded-full bg-emerald-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0 mt-0.5">
              {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="flex-1">
              <textarea name="body" id="comment-body" rows="2"
                        placeholder="Write a comment..."
                        class="w-full px-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent resize-none transition"
                        required></textarea>
              <div class="flex justify-end gap-2 mt-2">
                <button type="button" id="cancel-comment"
                        class="hidden px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-200 dark:border-slate-600 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                  Cancel
                </button>
                <button type="submit" id="submit-comment"
                        class="inline-flex items-center gap-1.5 px-4 py-1.5 text-xs font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition-colors">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                  </svg>
                  Post Comment
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    @else
      <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 text-center text-sm text-slate-400">
        <a href="{{ route('login') }}" class="text-emerald-600 dark:text-emerald-400 font-semibold hover:underline">Log in</a>
        to join the discussion
      </div>
    @endauth

    <div class="divide-y divide-slate-100 dark:divide-slate-700" id="comments-container">
      @forelse($report->comments()->with('user:id,name')->latest()->get() as $comment)
        @include('partials.comments._item', ['comment' => $comment, 'report' => $report])
      @empty
        <div class="px-6 py-12 text-center" id="no-comments-msg">
          <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
          </svg>
          <p class="text-sm font-medium text-slate-500 dark:text-slate-400">No comments yet</p>
          <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Be the first to comment on this report</p>
        </div>
      @endforelse
    </div>
  </div>

</div>

{{-- ── Lightbox ──────────────────────────────────────────────────────── --}}
<div id="lightbox" class="fixed inset-0 z-50 hidden bg-black/90 flex items-center justify-center" onclick="closeLightbox()">
  <button class="absolute top-4 right-4 text-white hover:text-slate-300 transition-colors" onclick="closeLightbox()">
    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
    </svg>
  </button>
  <img id="lightbox-img" src="" alt="" class="max-w-full max-h-full object-contain rounded-lg" onclick="event.stopPropagation()">
</div>

@push('scripts')
<script>
function openLightbox(src) {
  document.getElementById('lightbox-img').src = src;
  document.getElementById('lightbox').classList.remove('hidden');
  document.getElementById('lightbox').classList.add('flex');
  document.body.style.overflow = 'hidden';
}
function closeLightbox() {
  document.getElementById('lightbox').classList.add('hidden');
  document.getElementById('lightbox').classList.remove('flex');
  document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

// Comments
document.addEventListener('DOMContentLoaded', () => {
  const commentForm = document.getElementById('comment-form');
  const commentBody = document.getElementById('comment-body');
  const submitBtn   = document.getElementById('submit-comment');
  const container   = document.getElementById('comments-container');
  const countEl     = document.getElementById('comments-count');

  if (!commentForm) return;

  commentForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const body = commentBody.value.trim();
    if (!body) return;

    const reportId = commentForm.dataset.reportId;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Posting…';

    try {
      const res = await fetch(`/reports/${reportId}/comments`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json',
        },
        body: JSON.stringify({ body }),
      });
      const data = await res.json();

      if (res.ok && data.success) {
        commentBody.value = '';
        if (countEl) countEl.textContent = data.comments_count;

        const noMsg = document.getElementById('no-comments-msg');
        if (noMsg) noMsg.remove();

        const tmp = document.createElement('div');
        tmp.innerHTML = data.comment;
        const node = tmp.firstElementChild;
        node.style.opacity = '0';
        container.insertBefore(node, container.firstChild);
        requestAnimationFrame(() => {
          node.style.transition = 'opacity 0.2s ease';
          node.style.opacity = '1';
        });
      } else {
        alert(data.message || 'Failed to post comment');
      }
    } catch {
      alert('Failed to post comment. Please try again.');
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<svg class="w-3.5 h-3.5 inline -mt-0.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg> Post Comment';
    }
  });

  container.addEventListener('click', async (e) => {
    const btn = e.target.closest('.delete-comment-btn');
    if (!btn) return;
    if (!confirm('Delete this comment?')) return;

    const commentId = btn.dataset.commentId;
    const reportId  = btn.dataset.reportId;
    const commentEl = document.getElementById(`comment-${commentId}`);

    try {
      const res = await fetch(`/reports/${reportId}/comments/${commentId}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json',
        },
      });
      const data = await res.json();

      if (res.ok && data.success) {
        commentEl.style.transition = 'opacity 0.2s ease';
        commentEl.style.opacity = '0';
        setTimeout(() => {
          commentEl.remove();
          if (countEl) countEl.textContent = data.comments_count;
          if (data.comments_count === 0 && container.children.length === 0) {
            container.innerHTML = '<div class="px-6 py-12 text-center"><p class="text-sm text-slate-400">No comments yet</p></div>';
          }
        }, 200);
      }
    } catch { /* silent */ }
  });
});
</script>
@endpush
@endsection
