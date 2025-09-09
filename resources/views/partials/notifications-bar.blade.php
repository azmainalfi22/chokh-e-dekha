@props([
  // Pass a collection of notifications
  'notes' => collect(),
  // Optional: a title
  'title' => 'Notifications',
  // Optional: unread count (can be passed separately for performance)
  'unreadCount' => null,
])

@php
  // Calculate unread count if not provided
  $unreadCount = $unreadCount ?? $notes->whereNull('read_at')->count();
  
  // Enhanced status color helper with more nuanced detection
  $statusColor = function($s) {
    $s = \Illuminate\Support\Str::lower($s ?? '');
    return match(true) {
      str_contains($s, 'resolve') || str_contains($s, 'complete') || str_contains($s, 'approve') => 
        'bg-emerald-500/15 text-emerald-700 ring-emerald-400/30 dark:bg-emerald-400/10 dark:text-emerald-300',
      str_contains($s, 'progress') || str_contains($s, 'review') || str_contains($s, 'process') => 
        'bg-sky-500/15 text-sky-700 ring-sky-400/30 dark:bg-sky-400/10 dark:text-sky-300',
      str_contains($s, 'reject') || str_contains($s, 'decline') || str_contains($s, 'cancel') => 
        'bg-rose-500/15 text-rose-700 ring-rose-400/30 dark:bg-rose-400/10 dark:text-rose-300',
      str_contains($s, 'urgent') || str_contains($s, 'critical') => 
        'bg-orange-500/15 text-orange-700 ring-orange-400/30 dark:bg-orange-400/10 dark:text-orange-300',
      default => 'bg-amber-500/15 text-amber-700 ring-amber-400/30 dark:bg-amber-400/10 dark:text-amber-300',
    };
  };

  // Icon helper for different notification types
  $getIcon = function($data) {
    $status = \Illuminate\Support\Str::lower($data['status'] ?? '');
    $type = \Illuminate\Support\Str::lower($data['type'] ?? '');
    
    if (str_contains($status, 'resolve') || str_contains($status, 'complete')) {
      return '<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>';
    } elseif (str_contains($status, 'progress') || str_contains($status, 'review')) {
      return '<path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>';
    } elseif (str_contains($status, 'reject') || str_contains($status, 'decline')) {
      return '<path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>';
    } elseif (str_contains($type, 'comment') || str_contains($type, 'message')) {
      return '<path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>';
    } else {
      return '<path d="M15 17h5l-5 5v-5zM4 4h7v7H4V4zm9 9h7v7h-7v-7z"/>';
    }
  };
@endphp

<style>
  /* Enhanced animations and micro-interactions */
  @keyframes slideInUp {
    from {
      opacity: 0;
      transform: translateY(20px) scale(0.95);
    }
    to {
      opacity: 1;
      transform: translateY(0) scale(1);
    }
  }

  @keyframes fadeOut {
    from {
      opacity: 1;
      transform: scale(1);
    }
    to {
      opacity: 0;
      transform: scale(0.95);
    }
  }

  @keyframes pulse {
    0%, 100% {
      opacity: 1;
    }
    50% {
      opacity: 0.7;
    }
  }

  @keyframes bounce {
    0%, 20%, 53%, 80%, 100% {
      transform: translate3d(0, 0, 0);
    }
    40%, 43% {
      transform: translate3d(0, -8px, 0);
    }
    70% {
      transform: translate3d(0, -4px, 0);
    }
    90% {
      transform: translate3d(0, -2px, 0);
    }
  }

  .notif-bar {
    animation: slideInUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
  }

  .notif-item {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .notif-item:hover {
    transform: translateY(-1px);
  }

  .notif-removing {
    animation: fadeOut 0.3s ease-out forwards;
  }

  .unread-dot {
    animation: pulse 2s infinite;
  }

  .notif-badge {
    animation: bounce 0.6s ease-out;
  }

  .notif-icon {
    transition: transform 0.2s ease;
  }

  .notif-item:hover .notif-icon {
    transform: scale(1.05);
  }

  .glass-effect {
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
  }
</style>

{{-- Enhanced Floating Notification Bar --}}
<div
  id="notifBar"
  class="notif-bar fixed bottom-6 right-6 z-50 w-[28rem] max-w-[90vw] max-h-[75vh] overflow-hidden
         rounded-2xl border border-white/20 dark:border-white/10
         shadow-2xl glass-effect
         bg-white/80 dark:bg-slate-900/80
         transform transition-all duration-300 ease-out"
  style="transform-origin: bottom right;"
>
  {{-- Enhanced Header with Better Visual Hierarchy --}}
  <div class="flex items-center justify-between gap-3 px-5 py-4
              border-b border-white/20 dark:border-white/10
              bg-gradient-to-r from-white/50 to-white/30 dark:from-slate-800/50 dark:to-slate-800/30">
    <div class="flex items-center gap-3">
      <div class="relative">
        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl
                     bg-gradient-to-br from-amber-500 via-orange-500 to-rose-600 
                     text-white shadow-lg transform transition-transform duration-200 hover:scale-105">
          <svg class="h-5 w-5 notif-icon" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="0.5">
            <path d="M12 2a2 2 0 00-2 2v1.1A7.001 7.001 0 005 12v4l-2 2v1h18v-1l-2-2v-4a7.001 7.001 0 00-5-6.9V4a2 2 0 00-2-2zM9 20a3 3 0 006 0H9z"/>
          </svg>
        </span>
        @if($unreadCount > 0)
          <span id="notifCount" class="notif-badge absolute -top-1 -right-1 inline-flex items-center justify-center 
                       h-5 w-5 text-xs font-bold rounded-full 
                       bg-rose-600 text-white shadow-lg ring-2 ring-white dark:ring-slate-900">
            {{ min($unreadCount, 99) }}{{ $unreadCount > 99 ? '+' : '' }}
          </span>
        @endif
      </div>
      
      <div>
        <div class="font-semibold text-slate-800 dark:text-slate-100 text-sm">{{ $title }}</div>
        @if($unreadCount > 0)
          <div class="text-xs text-slate-600 dark:text-slate-400">
            {{ $unreadCount }} unread {{ $unreadCount === 1 ? 'notification' : 'notifications' }}
          </div>
        @endif
      </div>
    </div>

    <div class="flex items-center gap-2">
      <button id="notifCollapse"
              class="px-3 py-1.5 text-xs font-medium rounded-lg 
                     bg-white/60 dark:bg-white/10 hover:bg-white/80 dark:hover:bg-white/15
                     text-slate-700 dark:text-slate-300
                     border border-white/40 dark:border-white/20
                     transition-all duration-200 hover:scale-105 active:scale-95"
              title="Minimize notifications">
        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M18 15l-6-6-6 6"/>
        </svg>
      </button>
      
      @if($unreadCount > 0)
        <button id="notifMarkAll"
                class="px-3 py-1.5 text-xs font-medium rounded-lg 
                       bg-emerald-500/15 hover:bg-emerald-500/25 
                       text-emerald-700 dark:text-emerald-300
                       border border-emerald-500/30
                       transition-all duration-200 hover:scale-105 active:scale-95"
                title="Mark all as read">
          <svg class="h-3.5 w-3.5 mr-1 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Mark all read
        </button>
      @endif

      @if($notes->count() > 0)
        <button id="notifClearAll"
                class="px-3 py-1.5 text-xs font-medium rounded-lg 
                       bg-rose-500/15 hover:bg-rose-500/25 
                       text-rose-700 dark:text-rose-300
                       border border-rose-500/30
                       transition-all duration-200 hover:scale-105 active:scale-95"
                title="Clear all notifications">
          <svg class="h-3.5 w-3.5 mr-1 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 6h18m-2 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
          </svg>
          Clear all
        </button>
      @endif
    </div>
  </div>

  {{-- Enhanced Notification List with Better Spacing and Interactions --}}
  <div id="notifList" class="max-h-[60vh] overflow-y-auto overflow-x-hidden p-3 space-y-3
                             scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600 
                             scrollbar-track-transparent">
    @forelse($notes as $n)
      @php
        $data   = (array) $n->data;
        $title  = $data['title']  ?? 'Report Update';
        $status = $data['status'] ?? 'Updated';
        $unread = is_null($n->read_at);
        $timeAgo = $n->created_at->diffForHumans();
      @endphp

      <div
        class="notif-item group relative rounded-xl p-4
               bg-white/60 dark:bg-white/5 hover:bg-white/80 dark:hover:bg-white/8
               border border-white/40 dark:border-white/10 hover:border-white/60 dark:hover:border-white/20
               shadow-sm hover:shadow-lg transition-all duration-200
               flex items-start gap-4"
        data-notif-id="{{ $n->id }}"
      >
        {{-- Enhanced Unread Indicator --}}
        @if($unread)
          <span class="unread-dot absolute -left-1.5 top-4 inline-block h-3 w-3 rounded-full 
                       bg-rose-500 ring-4 ring-rose-500/20 dark:ring-rose-500/30"></span>
        @endif

        {{-- Dynamic Icon Based on Notification Type --}}
        <div class="shrink-0">
          <div class="notif-icon h-11 w-11 rounded-xl 
                      bg-gradient-to-br from-amber-500 via-orange-500 to-rose-600 
                      text-white flex items-center justify-center shadow-lg
                      group-hover:shadow-xl transition-all duration-200">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              {!! $getIcon($data) !!}
            </svg>
          </div>
        </div>

        {{-- Enhanced Content Area --}}
        <div class="min-w-0 flex-1">
          <div class="flex items-start justify-between gap-2 mb-1">
            <h4 class="font-semibold text-slate-800 dark:text-slate-100 text-sm leading-tight">
              {{ $title }}
            </h4>
            <span class="flex-shrink-0 text-[10px] px-2 py-1 rounded-full ring-1 font-medium {{ $statusColor($status) }}">
              {{ \Illuminate\Support\Str::headline($status) }}
            </span>
          </div>

          {{-- Enhanced Description --}}
          @if(isset($data['message']) && $data['message'])
            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed mb-2 line-clamp-2">
              {{ $data['message'] }}
            </p>
          @endif

          {{-- Enhanced Metadata --}}
          <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400 mb-3">
            <span class="flex items-center gap-1">
              <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
              </svg>
              {{ $timeAgo }}
            </span>
            @if(isset($data['city']) && $data['city'])
              <span class="flex items-center gap-1">
                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                </svg>
                {{ $data['city'] }}
              </span>
            @endif
          </div>

          {{-- Enhanced Action Buttons --}}
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              @if(isset($data['report_id']) && Route::has('reports.show'))
                <a href="{{ route('reports.show', $data['report_id']) }}"
                   class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-lg 
                          bg-slate-100/80 dark:bg-white/10 hover:bg-slate-200/80 dark:hover:bg-white/15
                          text-slate-700 dark:text-slate-300 
                          border border-slate-200/50 dark:border-white/20
                          transition-all duration-200 hover:scale-105 active:scale-95">
                  <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                  View details
                </a>
              @endif

              @if($unread)
                <button
                  class="notif-read inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-lg 
                         bg-emerald-100/80 dark:bg-emerald-500/15 hover:bg-emerald-200/80 dark:hover:bg-emerald-500/25
                         text-emerald-700 dark:text-emerald-300
                         border border-emerald-200/50 dark:border-emerald-500/30
                         transition-all duration-200 hover:scale-105 active:scale-95"
                  data-id="{{ $n->id }}"
                  title="Mark as read">
                  <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 12l2 2 4-4"/>
                  </svg>
                  Mark read
                </button>
              @endif
            </div>

            {{-- Individual Clear Button --}}
            <button
              class="notif-clear inline-flex items-center justify-center w-7 h-7 rounded-lg 
                     bg-rose-100/60 dark:bg-rose-500/10 hover:bg-rose-200/80 dark:hover:bg-rose-500/20
                     text-rose-600 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300
                     border border-rose-200/50 dark:border-rose-500/20 hover:border-rose-300/60 dark:hover:border-rose-500/30
                     transition-all duration-200 hover:scale-105 active:scale-95 opacity-0 group-hover:opacity-100"
              data-id="{{ $n->id }}"
              title="Remove notification">
              <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 6L6 18M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>
      </div>
    @empty
      <div id="notifEmpty" class="p-8 text-center">
        <div class="inline-flex h-16 w-16 items-center justify-center rounded-full 
                    bg-slate-100/80 dark:bg-white/10 mb-4">
          <svg class="h-8 w-8 text-slate-400 dark:text-slate-500" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2a2 2 0 00-2 2v1.1A7.001 7.001 0 005 12v4l-2 2v1h18v-1l-2-2v-4a7.001 7.001 0 00-5-6.9V4a2 2 0 00-2-2z"/>
          </svg>
        </div>
        <div class="text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">All caught up!</div>
        <div class="text-xs text-slate-500 dark:text-slate-500">No new notifications to show.</div>
      </div>
    @endforelse
  </div>

  {{-- Enhanced Footer with Better Visual Balance --}}
  <div class="px-4 py-3 border-t border-white/20 dark:border-white/10 
              bg-gradient-to-r from-white/30 to-white/20 dark:from-slate-800/30 dark:to-slate-800/20
              flex items-center justify-between">
    <a href="{{ Route::has('notifications.index') ? route('notifications.index') : '#' }}"
       class="inline-flex items-center gap-1 text-xs font-medium text-slate-600 dark:text-slate-400 
              hover:text-slate-800 dark:hover:text-slate-200 transition-colors duration-200">
      <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M9 18l6-6-6-6"/>
      </svg>
      View all notifications
    </a>
    
    <button id="notifClose"
            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg 
                   text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200
                   hover:bg-white/40 dark:hover:bg-white/10 border border-transparent hover:border-white/40
                   transition-all duration-200"
            title="Close notifications">
      <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M18 6L6 18M6 6l12 12"/>
      </svg>
      Close
    </button>
  </div>
</div>