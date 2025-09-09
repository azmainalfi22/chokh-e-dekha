@extends(auth()->user()->is_admin ? 'layouts.admin' : 'layouts.app')
@section('title', 'Report Details')

@push('styles')
<style>
  /* ---- Page-specific polish (keeps your global tokens if present) ---- */
  .cd-card{ background: var(--surface, rgba(255,255,255,.88)); backdrop-filter: blur(8px);
            border: 1px solid var(--ring, #e2e8f0); border-radius: 1rem; box-shadow: var(--shadow-lg, 0 20px 40px -20px rgba(0,0,0,.25));}
  .cd-chip{ display:inline-flex; align-items:center; gap:.5rem; padding:.25rem .6rem; border-radius: 999px;
            font-weight: 600; font-size: .75rem; line-height: 1; border:1px solid transparent; }
  .cd-meta dt{ color: #6b7280 } /* slate-500/600 */
  .cd-meta dd{ color: var(--text, #0f172a) }

  /* Status colors (light & dark) */
  .st-pending     { background:#fef3c7; color:#92400e; border-color:#fde68a; }    /* amber */
  .st-in_progress { background:#dbeafe; color:#1e40af; border-color:#bfdbfe; }    /* blue */
  .st-resolved    { background:#dcfce7; color:#065f46; border-color:#bbf7d0; }    /* emerald */
  .st-rejected    { background:#ffe4e6; color:#9f1239; border-color:#fecdd3; }    /* rose */

  /* Timeline (visual only; no feature change) */
  .step { position:relative; display:flex; align-items:center; gap:.5rem; font-size:.8rem; }
  .step:before{ content:""; width:.75rem; height:.75rem; border-radius:999px; border:2px solid currentColor; }
  .step.active:before{ background: currentColor; }
  .step + .step { margin-top:.5rem; }
  .step:after{ content:""; position:absolute; left:.31rem; top:1rem; width:2px; height: calc(100% - 1rem); background: currentColor; opacity:.2; }
  .step:last-child:after{ display:none; }

  /* Attachment grid */
  .att-grid{ display:grid; grid-template-columns: repeat(1, minmax(0,1fr)); gap:.75rem; }
  @media (min-width:768px){ .att-grid{ grid-template-columns: repeat(2, minmax(0,1fr)); } }
  .att-item{ display:flex; align-items:center; justify-content:space-between; gap:.75rem;
             padding:.6rem .75rem; border-radius:.75rem; border:1px solid var(--ring,#e5e7eb);
             background: rgba(250, 250, 250, .7); }
  .btn{ display:inline-flex; align-items:center; justify-content:center; gap:.5rem; font-weight:600;
        border-radius:.75rem; padding:.6rem .9rem; transition: box-shadow .2s, transform .2s, background .2s; }
  .btn-ghost{ background: var(--surface, #fff); border:1px solid var(--ring, #e5e7eb); color:#0f172a; }
  .btn-ghost:hover{ box-shadow: 0 10px 20px -12px rgba(0,0,0,.25); transform: translateY(-1px);}
  .btn-primary{ color:white; background: linear-gradient(90deg, #d97706, #e11d48); } /* amber → rose */
  .btn-primary:hover{ filter:brightness(0.98); box-shadow: 0 16px 30px -18px rgba(225,29,72,.6); transform: translateY(-1px); }

  /* Dark mode adjustments (respect your global .dark) */
  .dark .cd-card{ background: var(--surface, rgba(15,23,42,.75)); border-color: var(--ring, rgba(148,163,184,.25)); }
  .dark .cd-meta dt{ color:#94a3b8 } .dark .cd-meta dd{ color:#e5e7eb }
  .dark .att-item{ background: rgba(2,6,23,.35); border-color: rgba(148,163,184,.25); }
  .dark .btn-ghost{ background: rgba(2,6,23,.4); border-color: rgba(148,163,184,.25); color:#e5e7eb; }
</style>
@endpush

@section('content')
<div class="relative">
  {{-- background accents --}}
  <div class="pointer-events-none absolute -top-20 -right-24 h-80 w-80 rounded-full blur-3xl opacity-20 bg-gradient-to-br from-amber-300 to-rose-300 z-0"></div>
  <div class="pointer-events-none absolute -bottom-24 -left-24 h-96 w-96 rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-orange-300 to-pink-300 z-0"></div>

  <div class="max-w-6xl mx-auto p-4 md:p-8 relative z-10">
    {{-- flashes --}}
    @if(session('success'))
      <div role="alert" class="mb-4 rounded-xl bg-green-50 ring-1 ring-green-200 px-4 py-3 text-green-800 dark:bg-emerald-900/20 dark:text-emerald-100 dark:ring-emerald-800/40">
        {{ session('success') }}
      </div>
    @endif
    @if($errors->any())
      <div role="alert" class="mb-4 rounded-xl bg-rose-50 ring-1 ring-rose-200 px-4 py-3 text-rose-800 dark:bg-rose-900/20 dark:text-rose-100 dark:ring-rose-800/40">
        <ul class="list-disc space-y-1 pl-5">
          @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    @php
      $status = $report->status ?? 'pending';
      $map = [
        'pending'     => 'st-pending',
        'in_progress' => 'st-in_progress',
        'resolved'    => 'st-resolved',
        'rejected'    => 'st-rejected',
      ];
      $statusClass = $map[$status] ?? 'st-pending';
    @endphp

    <div class="cd-card p-6 md:p-8">
      {{-- Header --}}
      <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
        <div class="space-y-1">
          <h1 class="text-2xl md:text-3xl font-extrabold text-amber-800 dark:text-amber-300">
            {{ $report->title ?? ('Report #'.$report->id) }}
          </h1>
          <p class="text-sm text-gray-600 dark:text-slate-300">
            Submitted by <span class="font-medium">{{ $report->user->name ?? 'Unknown' }}</span>
            • {{ $report->created_at?->format('M d, Y h:i a') }}
            @if($report->updated_at && $report->updated_at->ne($report->created_at))
              <span class="text-gray-400 dark:text-slate-400"> • Updated {{ $report->updated_at->diffForHumans() }}</span>
            @endif
          </p>
          <div class="flex items-center gap-2 pt-1">
            <span class="cd-chip {{ $statusClass }}" title="Current status">
              {{-- tiny indicator --}}
              <svg width="10" height="10" viewBox="0 0 10 10" class="-ml-0.5"><circle cx="5" cy="5" r="5" fill="currentColor" /></svg>
              {{ \Illuminate\Support\Str::headline($status) }}
            </span>

            {{-- Reference / copy link --}}
            <span class="cd-chip btn-ghost" title="Internal reference">#{{ $report->id }}</span>
            <button type="button" id="copyLinkBtn" class="btn btn-ghost" title="Copy link">
              {{-- link icon --}}
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13.5 6.75h3.75a2.25 2.25 0 012.25 2.25v6a2.25 2.25 0 01-2.25 2.25H13.5m-3 0H6.75A2.25 2.25 0 014.5 15V9a2.25 2.25 0 012.25-2.25H10.5m-3 6h9" />
              </svg>
              Copy link
            </button>
          </div>
        </div>

        {{-- Status quick visual (timeline) --}}
        <div class="hidden md:block min-w-[220px]">
          <div class="cd-card p-4">
            <div class="text-xs font-semibold text-slate-500 mb-2 dark:text-slate-300">Progress</div>
            <div class="step {{ in_array($status, ['pending','in_progress','resolved','rejected']) ? 'active text-amber-700 dark:text-amber-300' : '' }}">Pending</div>
            <div class="step {{ in_array($status, ['in_progress','resolved']) ? 'active text-blue-700 dark:text-blue-300' : 'text-slate-400 dark:text-slate-500' }}">In progress</div>
            <div class="step {{ $status==='resolved' ? 'active text-emerald-700 dark:text-emerald-300' : 'text-slate-400 dark:text-slate-500' }}">Resolved</div>
          </div>
        </div>
      </div>

      {{-- Body --}}
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: details + attachment + notes --}}
        <div class="lg:col-span-2 space-y-6">
          {{-- Details --}}
          <section class="cd-card p-6">
            <h2 class="text-lg font-semibold text-amber-800 dark:text-amber-300 mb-3">Details</h2>
            <dl class="cd-meta grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
              <div>
                <dt>Location</dt>
                <dd class="font-medium">{{ $report->location ?? '—' }}</dd>
              </div>
              <div>
                <dt>Category</dt>
                <dd class="font-medium">{{ $report->category ?? '—' }}</dd>
              </div>
              <div>
                <dt>City Corporation</dt>
                <dd class="font-medium">{{ $report->city_corporation ?? '—' }}</dd>
              </div>
              <div>
                <dt>Current Status</dt>
                <dd class="font-medium">{{ \Illuminate\Support\Str::headline($status) }}</dd>
              </div>
              <div class="sm:col-span-2">
                <dt>Description</dt>
                <dd class="mt-1 whitespace-pre-line text-slate-700 dark:text-slate-200">
                  {{ $report->description ?? 'No description provided.' }}
                </dd>
              </div>
            </dl>
          </section>

          {{-- Attachments --}}
          <section class="cd-card p-6">
            <h2 class="text-lg font-semibold text-amber-800 dark:text-amber-300 mb-3">Attachment</h2>
            @php
              $files = $report->attachments ?? [];
              $files = is_array($files) ? $files : (empty($files) ? [] : [$files]);
            @endphp
            @if(!empty($files))
              <div class="att-grid">
                @foreach($files as $file)
                  <div class="att-item">
                    <div class="min-w-0">
                      <div class="text-sm font-medium truncate">{{ basename($file) }}</div>
                      <div class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $file }}</div>
                    </div>
                    <a href="{{ \Illuminate\Support\Facades\Storage::url($file) }}"
                       target="_blank" class="btn btn-ghost">View</a>
                  </div>
                @endforeach
              </div>
            @elseif(!empty($report->photo_url))
              <img src="{{ $report->photo_url }}" alt="Attachment"
                   class="w-full max-h-[26rem] object-cover rounded-xl border border-[var(--ring,#e5e7eb)]" loading="lazy">
            @elseif(!empty($report->photo))
              <img src="{{ asset('storage/'.$report->photo) }}" alt="Attachment"
                   class="w-full max-h-[26rem] object-cover rounded-xl border border-[var(--ring,#e5e7eb)]" loading="lazy">
            @else
              <div class="w-full h-44 grid place-items-center rounded-xl border border-[var(--ring,#e5e7eb)] text-slate-500 dark:text-slate-400">
                No attachment
              </div>
            @endif
          </section>

          {{-- Admin Notes (public) --}}
          <section id="notes" class="cd-card p-6">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold text-amber-800 dark:text-amber-300">Admin Notes</h2>
              <span class="text-xs text-slate-500 dark:text-slate-400">(visible to everyone)</span>
            </div>

            @if(auth()->user()->is_admin && Route::has('admin.reports.notes.store'))
              <form method="POST" action="{{ route('admin.reports.notes.store', $report) }}" class="space-y-3 mb-5">
                @csrf
                <label class="sr-only" for="noteBody">Write an update visible to everyone</label>
                <textarea id="noteBody" name="body" rows="4"
                          class="w-full rounded-xl border border-[var(--ring,#e5e7eb)] px-3 py-2 focus:ring-2 focus:ring-amber-300 dark:bg-transparent dark:text-slate-100"
                          placeholder="Write an update/response visible to everyone..." required></textarea>
                <div class="flex justify-end">
                  <button class="btn btn-primary">Publish Note</button>
                </div>
              </form>
            @endif

            <div class="space-y-3">
              @forelse($report->notes as $note)
                <article class="rounded-xl border border-[var(--ring,#e5e7eb)] bg-amber-50/60 dark:bg-amber-900/10 p-4">
                  <div class="text-sm text-slate-800 dark:text-slate-100">{{ $note->body }}</div>
                  <div class="mt-2 text-xs text-slate-600 dark:text-slate-300 flex items-center justify-between">
                    <span>— {{ $note->admin?->name ?? 'Admin' }} • {{ $note->created_at->diffForHumans() }}</span>
                    @if(auth()->user()->is_admin && Route::has('admin.reports.notes.destroy'))
                      <form method="POST" action="{{ route('admin.reports.notes.destroy', [$report, $note]) }}"
                            onsubmit="return confirm('Delete this note?');">
                        @csrf
                        @method('DELETE')
                        <button class="text-rose-700 dark:text-rose-300 hover:underline text-xs">Delete</button>
                      </form>
                    @endif
                  </div>
                </article>
              @empty
                <p class="text-sm text-slate-500 dark:text-slate-300">No notes yet.</p>
              @endforelse
            </div>
          </section>
        </div>

        {{-- Right: actions --}}
        <aside class="space-y-6">
          @if(auth()->user()->is_admin && Route::has('admin.reports.status'))
            <section id="status" class="cd-card p-6">
              <h3 class="text-lg font-semibold text-amber-800 dark:text-amber-300 mb-3">Update Status</h3>
              <form method="POST" action="{{ route('admin.reports.status', $report) }}" class="space-y-3">
                @csrf
                @method('PUT')
                <label class="sr-only" for="statusSel">Status</label>
                <select id="statusSel" name="status"
                        class="w-full rounded-xl border border-[var(--ring,#e5e7eb)] px-3 py-2 focus:ring-2 focus:ring-amber-300 dark:bg-transparent dark:text-slate-100">
                  <option value="pending"     @selected(($report->status ?? '') === 'pending')>Pending</option>
                  <option value="in_progress" @selected(($report->status ?? '') === 'in_progress')>In progress</option>
                  <option value="resolved"    @selected(($report->status ?? '') === 'resolved')>Resolved</option>
                </select>
                <button class="w-full btn btn-primary">Update Status</button>
              </form>
              <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Tip: choose “In progress” when a team is assigned.</p>
            </section>
          @endif

          <a href="{{ route('admin.reports.index') }}" class="block text-center btn btn-ghost">
            ← Back
          </a>
        </aside>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  // Copy current page URL
  (function(){
    const btn = document.getElementById('copyLinkBtn');
    if(!btn) return;
    btn.addEventListener('click', async () => {
      try {
        await navigator.clipboard.writeText(window.location.href);
        btn.textContent = 'Copied!';
        setTimeout(()=>{ btn.textContent = 'Copy link'; }, 1200);
      } catch {}
    });
  })();
</script>
@endpush
@endsection
