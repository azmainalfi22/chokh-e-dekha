@extends(auth()->user()->is_admin ? 'layouts.admin' : 'layouts.app')
@section('title', 'Report #' . $report->id)

@push('styles')
<style>
  .cd-chip{ display:inline-flex; align-items:center; gap:.4rem; padding:.25rem .65rem; border-radius:999px;
            font-weight:600; font-size:.72rem; line-height:1; border:1px solid transparent; }
  .st-pending     { background:#fef3c7; color:#92400e; border-color:#fde68a; }
  .st-in_progress { background:#dbeafe; color:#1e40af; border-color:#bfdbfe; }
  .st-resolved    { background:#dcfce7; color:#065f46; border-color:#bbf7d0; }
  .st-rejected    { background:#ffe4e6; color:#9f1239; border-color:#fecdd3; }
  .dark .st-pending     { background:#78350f33; color:#fcd34d; border-color:#78350f66; }
  .dark .st-in_progress { background:#1e3a5f33; color:#93c5fd; border-color:#1e40af66; }
  .dark .st-resolved    { background:#065f4633; color:#6ee7b7; border-color:#05966966; }
  .dark .st-rejected    { background:#9f123933; color:#fda4af; border-color:#9f123966; }
  .progress-step{ display:flex; align-items:center; gap:.75rem; padding:.5rem 0; }
  .progress-step .dot{ width:1.75rem; height:1.75rem; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
  .progress-step .line{ position:absolute; left:.875rem; top:2.25rem; width:2px; height:calc(100% - 1.75rem); }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

  {{-- Flash messages --}}
  @if(session('success'))
    <div class="mb-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 px-4 py-3 text-sm text-emerald-800 dark:text-emerald-200 flex items-center gap-2">
      <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      {{ session('success') }}
    </div>
  @endif
  @if($errors->any())
    <div class="mb-4 rounded-xl bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
      <ul class="list-disc space-y-1 pl-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  @php
    $status = $report->status ?? 'pending';
    $statusMap = ['pending'=>'st-pending','in_progress'=>'st-in_progress','resolved'=>'st-resolved','rejected'=>'st-rejected'];
    $statusClass = $statusMap[$status] ?? 'st-pending';

    $daysOld = (int) $report->created_at->diffInDays(now());
    $slaDue  = $report->sla_due_at ?? $report->created_at->addDays(7);
    $slaBreached = $slaDue->isPast() && !in_array($status, ['resolved','rejected']);
    $daysUntilSla = (int) max(0, now()->diffInDays($slaDue, false));
  @endphp

  {{-- ── Page Header ──────────────────────────────────────────────────── --}}
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex items-center gap-2">
      <a href="{{ route('admin.reports.index') }}"
         class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
      </a>
      <div>
        <div class="text-xs text-slate-400 dark:text-slate-500 font-medium">Reports / #{{ $report->id }}</div>
        <h1 class="text-lg font-bold text-slate-900 dark:text-white leading-tight">{{ $report->title }}</h1>
      </div>
    </div>
    <div class="flex items-center gap-2">
      <span class="cd-chip {{ $statusClass }}">
        <svg width="7" height="7" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4" fill="currentColor"/></svg>
        {{ \Illuminate\Support\Str::headline($status) }}
      </span>
      @if($slaBreached)
        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          SLA Breached
        </span>
      @endif
      <button type="button" id="copyLinkBtn"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
        Copy Link
      </button>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── LEFT COLUMN ───────────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-5">

      {{-- Report info card --}}
      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
        <h2 class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-4">Report Details</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm mb-5">
          <div>
            <div class="text-xs text-slate-400 dark:text-slate-500 mb-0.5">Reporter</div>
            <div class="font-semibold text-slate-800 dark:text-slate-100">{{ $report->user->name ?? 'Anonymous' }}</div>
            @if($report->user?->email)
              <div class="text-xs text-slate-400">{{ $report->user->email }}</div>
            @endif
          </div>
          <div>
            <div class="text-xs text-slate-400 dark:text-slate-500 mb-0.5">Category</div>
            <div class="font-semibold text-slate-800 dark:text-slate-100">{{ ucfirst($report->category ?? '—') }}</div>
          </div>
          <div>
            <div class="text-xs text-slate-400 dark:text-slate-500 mb-0.5">City Corporation</div>
            <div class="font-semibold text-slate-800 dark:text-slate-100">{{ $report->city_corporation ?? '—' }}</div>
          </div>
          <div>
            <div class="text-xs text-slate-400 dark:text-slate-500 mb-0.5">Location</div>
            <div class="font-semibold text-slate-800 dark:text-slate-100 text-xs leading-snug">{{ $report->location ?? '—' }}</div>
          </div>
          <div>
            <div class="text-xs text-slate-400 dark:text-slate-500 mb-0.5">Submitted</div>
            <div class="font-semibold text-slate-800 dark:text-slate-100">{{ $report->created_at->format('M d, Y') }}</div>
            <div class="text-xs text-slate-400">{{ $report->created_at->diffForHumans() }}</div>
          </div>
          <div>
            <div class="text-xs text-slate-400 dark:text-slate-500 mb-0.5">Age</div>
            <div class="font-semibold {{ $daysOld >= 7 ? 'text-rose-600 dark:text-rose-400' : ($daysOld >= 3 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-800 dark:text-slate-100') }}">
              {{ $daysOld }} {{ $daysOld === 1 ? 'day' : 'days' }} old
            </div>
          </div>
        </div>

        <div class="border-t border-slate-100 dark:border-slate-700 pt-4">
          <div class="text-xs text-slate-400 dark:text-slate-500 mb-1.5">Description</div>
          <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-line">{{ $report->description ?? 'No description.' }}</p>
        </div>
      </div>

      {{-- Photo / Attachment --}}
      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-6 py-3 border-b border-slate-100 dark:border-slate-700 flex items-center gap-2">
          <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Evidence / Attachments</h2>
        </div>
        <div class="p-4">
          @php
            $files = $report->attachments ?? [];
            $files = is_array($files) ? $files : (empty($files) ? [] : [$files]);
          @endphp
          @if(!empty($files))
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              @foreach($files as $file)
                <div class="flex items-center justify-between gap-3 p-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/40">
                  <div class="min-w-0">
                    <div class="text-sm font-medium text-slate-800 dark:text-slate-100 truncate">{{ basename($file) }}</div>
                    <div class="text-xs text-slate-400 truncate">{{ $file }}</div>
                  </div>
                  <a href="{{ \Illuminate\Support\Facades\Storage::url($file) }}" target="_blank"
                     class="flex-shrink-0 text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 transition-colors">View</a>
                </div>
              @endforeach
            </div>
          @elseif(!empty($report->photo))
            <img src="{{ asset('storage/'.$report->photo) }}" alt="Report photo"
                 class="w-full max-h-96 object-cover rounded-xl" loading="lazy">
          @else
            <div class="flex items-center justify-center h-32 rounded-xl border-2 border-dashed border-slate-200 dark:border-slate-700 text-slate-400 text-sm">
              No attachments submitted
            </div>
          @endif
        </div>
      </div>

      {{-- Admin Notes --}}
      <div id="notes" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-6 py-3 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
          <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Admin Notes
          </h2>
          <span class="text-xs text-slate-400 dark:text-slate-500">Visible to the public</span>
        </div>

        @if(auth()->user()->is_admin && Route::has('admin.reports.notes.store'))
          <form method="POST" action="{{ route('admin.reports.notes.store', $report) }}" class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/30">
            @csrf
            <textarea name="body" rows="3"
                      class="w-full px-3 py-2 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent resize-none transition"
                      placeholder="Write an official update visible to the public…" required></textarea>
            <div class="flex justify-end mt-2">
              <button class="inline-flex items-center gap-1.5 px-4 py-1.5 text-sm font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition-colors">
                Publish Note
              </button>
            </div>
          </form>
        @endif

        <div class="divide-y divide-slate-100 dark:divide-slate-700">
          @forelse($report->notes as $note)
            <div class="px-6 py-4">
              <div class="flex items-start justify-between gap-3">
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-1">
                    <div class="w-6 h-6 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                      {{ strtoupper(substr($note->admin?->name ?? 'A', 0, 1)) }}
                    </div>
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $note->admin?->name ?? 'Admin' }}</span>
                    <span class="text-xs text-slate-400">{{ $note->created_at->diffForHumans() }}</span>
                  </div>
                  <p class="text-sm text-slate-700 dark:text-slate-300 pl-8">{{ $note->body }}</p>
                </div>
                @if(auth()->user()->is_admin && Route::has('admin.reports.notes.destroy'))
                  <form method="POST" action="{{ route('admin.reports.notes.destroy', [$report, $note]) }}"
                        onsubmit="return confirm('Delete this note?');">
                    @csrf @method('DELETE')
                    <button class="text-xs text-rose-500 dark:text-rose-400 hover:text-rose-700 transition-colors">Delete</button>
                  </form>
                @endif
              </div>
            </div>
          @empty
            <div class="px-6 py-8 text-center text-sm text-slate-400 dark:text-slate-500">
              No notes yet. Add one above to post an official update.
            </div>
          @endforelse
        </div>
      </div>
    </div>

    {{-- ── RIGHT COLUMN ─────────────────────────────────────────────── --}}
    <aside class="space-y-4">

      {{-- Status Progress --}}
      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
        <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-4">Case Progress</h3>
        <div class="space-y-0">
          @php
            $steps = [
              ['key'=>'pending',     'label'=>'Filed',      'sub'=>'Report submitted by citizen', 'color'=>'text-amber-600 dark:text-amber-400',  'bg'=>'bg-amber-100 dark:bg-amber-900/40'],
              ['key'=>'in_progress', 'label'=>'In Progress','sub'=>'Assigned & being addressed',  'color'=>'text-indigo-600 dark:text-indigo-400', 'bg'=>'bg-indigo-100 dark:bg-indigo-900/40'],
              ['key'=>'resolved',    'label'=>'Resolved',   'sub'=>'Issue fixed & verified',      'color'=>'text-emerald-600 dark:text-emerald-400','bg'=>'bg-emerald-100 dark:bg-emerald-900/40'],
            ];
            $statusOrder = ['pending'=>0,'in_progress'=>1,'resolved'=>2,'rejected'=>-1];
            $currentOrder = $statusOrder[$status] ?? 0;
          @endphp
          @if($status === 'rejected')
            <div class="flex items-center gap-3 p-3 rounded-lg bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800">
              <div class="w-7 h-7 rounded-full bg-rose-100 dark:bg-rose-900/40 flex items-center justify-center flex-shrink-0">
                <svg class="w-3.5 h-3.5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </div>
              <div>
                <div class="text-sm font-semibold text-rose-700 dark:text-rose-300">Rejected</div>
                <div class="text-xs text-rose-500">Report was not approved</div>
              </div>
            </div>
          @else
            @foreach($steps as $i => $step)
              @php
                $stepOrder = $statusOrder[$step['key']] ?? 0;
                $done = $currentOrder >= $stepOrder;
                $current = $status === $step['key'];
              @endphp
              <div class="relative {{ !$loop->last ? 'pb-4' : '' }}">
                @if(!$loop->last)
                  <div class="absolute left-3.5 top-7 bottom-0 w-0.5 {{ $done ? 'bg-emerald-200 dark:bg-emerald-800' : 'bg-slate-200 dark:bg-slate-700' }}"></div>
                @endif
                <div class="flex items-start gap-3">
                  <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 {{ $done ? $step['bg'] : 'bg-slate-100 dark:bg-slate-700' }}">
                    @if($done && !$current)
                      <svg class="w-3.5 h-3.5 {{ $step['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                      </svg>
                    @elseif($current)
                      <div class="w-2.5 h-2.5 rounded-full {{ str_replace(['text-','dark:text-'], ['bg-','dark:bg-'], explode(' ', $step['color'])[0]) }}"></div>
                    @else
                      <div class="w-2 h-2 rounded-full bg-slate-300 dark:bg-slate-600"></div>
                    @endif
                  </div>
                  <div class="pt-0.5">
                    <div class="text-sm font-semibold {{ $done ? $step['color'] : 'text-slate-400 dark:text-slate-500' }}">{{ $step['label'] }}</div>
                    <div class="text-xs text-slate-400 dark:text-slate-500">{{ $step['sub'] }}</div>
                  </div>
                </div>
              </div>
            @endforeach
          @endif
        </div>
      </div>

      {{-- SLA Panel --}}
      <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
        <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-3">SLA / Timeline</h3>
        <div class="space-y-2 text-sm">
          <div class="flex items-center justify-between">
            <span class="text-slate-500 dark:text-slate-400">Submitted</span>
            <span class="font-medium text-slate-800 dark:text-slate-100">{{ $report->created_at->format('M d, Y') }}</span>
          </div>
          @if($report->status_updated_at)
            <div class="flex items-center justify-between">
              <span class="text-slate-500 dark:text-slate-400">Status Changed</span>
              <span class="font-medium text-slate-800 dark:text-slate-100">{{ $report->status_updated_at->format('M d, Y') }}</span>
            </div>
          @endif
          <div class="flex items-center justify-between">
            <span class="text-slate-500 dark:text-slate-400">SLA Target</span>
            <span class="font-medium {{ $slaBreached ? 'text-rose-600 dark:text-rose-400' : 'text-slate-800 dark:text-slate-100' }}">
              {{ $slaDue->format('M d, Y') }}
            </span>
          </div>
          @if(!in_array($status, ['resolved','rejected']))
            <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
              @if($slaBreached)
                <div class="flex items-center gap-2 text-rose-600 dark:text-rose-400 text-xs font-semibold">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  Overdue by {{ abs($daysUntilSla) }} {{ abs($daysUntilSla) === 1 ? 'day' : 'days' }}
                </div>
              @else
                <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 text-xs font-semibold">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  {{ $daysUntilSla }} {{ $daysUntilSla === 1 ? 'day' : 'days' }} remaining
                </div>
              @endif
            </div>
          @endif
        </div>
      </div>

      {{-- Update Status --}}
      @if(auth()->user()->is_admin && Route::has('admin.reports.status'))
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
          <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-3">Update Status</h3>
          <form method="POST" action="{{ route('admin.reports.status', $report) }}" class="space-y-3">
            @csrf @method('PUT')
            <select name="status"
                    class="w-full px-3 py-2 text-sm bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
              <option value="pending"     @selected($status === 'pending')>Pending</option>
              <option value="in_progress" @selected($status === 'in_progress')>In Progress</option>
              <option value="resolved"    @selected($status === 'resolved')>Resolved</option>
              <option value="rejected"    @selected($status === 'rejected')>Rejected</option>
            </select>
            <button class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition-colors">
              Update Status
            </button>
          </form>
          <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">Selecting "Rejected" will delete this report.</p>
        </div>
      @endif

      {{-- Assignment --}}
      @if(auth()->user()->is_admin)
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
          <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-3">Assignment</h3>
          @if($report->assigned_to)
            <div class="flex items-center gap-2 mb-3">
              <div class="w-7 h-7 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-xs">
                {{ strtoupper(substr($report->assignedTo?->name ?? 'A', 0, 1)) }}
              </div>
              <div>
                <div class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $report->assignedTo?->name ?? 'Assigned' }}</div>
                @if($report->assigned_at)
                  <div class="text-xs text-slate-400">Assigned {{ $report->assigned_at->diffForHumans() }}</div>
                @endif
              </div>
            </div>
          @else
            <p class="text-sm text-slate-400 dark:text-slate-500 mb-3">Unassigned</p>
          @endif
          @if(Route::has('admin.reports.assign'))
            <form method="POST" action="{{ route('admin.reports.assign', $report) }}">
              @csrf
              <button class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Assign to Me
              </button>
            </form>
          @endif
        </div>
      @endif

    </aside>
  </div>
</div>

@push('scripts')
<script>
(function(){
  const btn = document.getElementById('copyLinkBtn');
  if (!btn) return;
  btn.addEventListener('click', async () => {
    try {
      await navigator.clipboard.writeText(window.location.href);
      const orig = btn.innerHTML;
      btn.textContent = 'Copied!';
      setTimeout(() => { btn.innerHTML = orig; }, 1500);
    } catch {}
  });
})();
</script>
@endpush
@endsection
