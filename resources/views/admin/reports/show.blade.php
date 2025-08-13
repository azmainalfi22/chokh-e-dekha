@extends(auth()->user()->is_admin ? 'layouts.admin' : 'layouts.app')
@section('title', 'Report Details')

@section('content')
<div class="relative">
  <div class="pointer-events-none absolute -top-20 -right-24 h-80 w-80 rounded-full blur-3xl opacity-20 bg-gradient-to-br from-amber-300 to-rose-300"></div>
  <div class="pointer-events-none absolute -bottom-24 -left-24 h-96 w-96 rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-orange-300 to-pink-300"></div>

  <div class="max-w-5xl mx-auto p-4 md:p-8 relative">

    {{-- flashes --}}
    @if(session('success'))
      <div class="mb-4 rounded-xl bg-green-50 ring-1 ring-green-200 px-4 py-3 text-green-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
      <div class="mb-4 rounded-xl bg-rose-50 ring-1 ring-rose-200 px-4 py-3 text-rose-800">
        <ul class="list-disc space-y-1 pl-5">
          @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    @php
      $status = $report->status ?? 'pending';
      $map = [
        'pending'     => 'bg-amber-100 text-amber-800 ring-amber-200',
        'in_progress' => 'bg-blue-100 text-blue-800 ring-blue-200',
        'resolved'    => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
        'rejected'    => 'bg-rose-100 text-rose-800 ring-rose-200',
      ];
      $cls = $map[$status] ?? 'bg-gray-100 text-gray-800 ring-gray-200';
    @endphp

    <div class="bg-white/80 backdrop-blur rounded-2xl shadow-2xl p-6 ring-1 ring-amber-100">
      {{-- header --}}
      <div class="flex items-start justify-between gap-4 mb-6">
        <div>
          <h1 class="text-3xl font-extrabold text-amber-800">{{ $report->title ?? ('Report #'.$report->id) }}</h1>
          <p class="text-sm text-gray-500 mt-1">
            Submitted by <span class="font-medium">{{ $report->user->name ?? 'Unknown' }}</span> •
            {{ $report->created_at?->format('M d, Y h:i a') }}
          </p>
        </div>
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ring-1 ring-inset {{ $cls }}">
          {{ str($status)->headline() }}
        </span>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- left side --}}
        <div class="lg:col-span-2 space-y-6">
          {{-- details --}}
          <div class="rounded-2xl bg-white/85 backdrop-blur ring-1 ring-amber-900/10 shadow p-6">
            <h2 class="text-lg font-semibold text-amber-800 mb-3">Details</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
              <div>
                <dt class="text-amber-900/70">Location</dt>
                <dd class="font-medium text-amber-900">{{ $report->location ?? '—' }}</dd>
              </div>
              <div>
                <dt class="text-amber-900/70">Category</dt>
                <dd class="font-medium text-amber-900">{{ $report->category ?? '—' }}</dd>
              </div>
              <div>
                <dt class="text-amber-900/70">City Corporation</dt>
                <dd class="font-medium text-amber-900">{{ $report->city_corporation ?? '—' }}</dd>
              </div>
              <div>
                <dt class="text-amber-900/70">Current Status</dt>
                <dd class="font-medium text-amber-900">{{ str($status)->headline() }}</dd>
              </div>
              <div class="sm:col-span-2">
                <dt class="text-amber-900/70">Description</dt>
                <dd class="mt-1 whitespace-pre-line text-gray-700">{{ $report->description ?? 'No description provided.' }}</dd>
              </div>
            </dl>
          </div>

          {{-- attachment --}}
          <div class="rounded-2xl bg-white/85 backdrop-blur ring-1 ring-amber-900/10 shadow p-6">
            <h2 class="text-lg font-semibold text-amber-800 mb-3">Attachment</h2>
            @php $files = $report->attachments ?? []; @endphp
            @if(!empty($files))
              <ul class="space-y-2 text-sm">
                @foreach($files as $file)
                  <li class="flex items-center justify-between gap-3 rounded-xl bg-amber-50 px-3 py-2">
                    <span class="truncate">{{ basename($file) }}</span>
                    <a href="{{ Storage::url($file) }}" target="_blank" class="text-rose-700 hover:underline">View</a>
                  </li>
                @endforeach
              </ul>
            @elseif($report->photo_url ?? false)
              <img src="{{ $report->photo_url }}" alt="Attachment"
                   class="w-full h-64 object-cover rounded-xl ring-1 ring-amber-900/10 shadow" loading="lazy">
            @elseif($report->photo ?? false)
              <img src="{{ asset('storage/'.$report->photo) }}" alt="Attachment"
                   class="w-full h-64 object-cover rounded-xl ring-1 ring-amber-900/10 shadow" loading="lazy">
            @else
              <div class="w-full h-48 grid place-items-center rounded-xl ring-1 ring-amber-900/10 text-amber-900/60">
                No attachment
              </div>
            @endif
          </div>

          {{-- admin notes (public) --}}
          <section id="notes" class="rounded-2xl bg-white/85 backdrop-blur ring-1 ring-amber-900/10 shadow p-6">
            <h2 class="text-lg font-semibold text-amber-800 mb-4">Admin Notes</h2>

            @if(auth()->user()->is_admin && Route::has('admin.reports.notes.store'))
              <form method="POST" action="{{ route('admin.reports.notes.store', $report) }}" class="space-y-3 mb-5">
                @csrf
                <textarea name="body" rows="4" class="w-full rounded-xl border border-amber-200 px-3 py-2 focus:ring-2 focus:ring-amber-300"
                          placeholder="Write an update/response visible to everyone..." required></textarea>
                <div class="flex justify-end">
                  <button class="rounded-xl bg-slate-900 text-white px-4 py-2 font-semibold hover:bg-slate-950">
                    Publish Note
                  </button>
                </div>
              </form>
            @endif

            @forelse($report->notes as $note)
              <div class="rounded-xl bg-amber-50 ring-1 ring-amber-100 p-4 mb-3">
                <div class="text-sm text-amber-900/90">{{ $note->body }}</div>
                <div class="mt-2 text-xs text-amber-900/70 flex items-center justify-between">
                  <span>— {{ $note->admin?->name ?? 'Admin' }} • {{ $note->created_at->diffForHumans() }}</span>

                  @if(auth()->user()->is_admin && Route::has('admin.reports.notes.destroy'))
                    <form method="POST" action="{{ route('admin.reports.notes.destroy', [$report, $note]) }}"
                          onsubmit="return confirm('Delete this note?');">
                      @csrf
                      @method('DELETE')
                      <button class="text-rose-700 hover:underline text-xs">Delete</button>
                    </form>
                  @endif
                </div>
              </div>
            @empty
              <p class="text-sm text-amber-900/70">No notes yet.</p>
            @endforelse
          </section>
        </div>

        {{-- right actions --}}
        <div class="space-y-6">
          @if(auth()->user()->is_admin && Route::has('admin.reports.status'))
            <div id="status" class="rounded-2xl bg-white/85 backdrop-blur ring-1 ring-amber-900/10 shadow p-6">
              <h3 class="text-lg font-semibold text-amber-800 mb-3">Update Status</h3>
              <form method="POST" action="{{ route('admin.reports.status', $report) }}" class="space-y-3">
                @csrf
                @method('PUT')
                <select name="status" class="w-full rounded-xl border border-amber-200 px-3 py-2 focus:ring-2 focus:ring-amber-300">
                  <option value="pending"     @selected(($report->status ?? '') === 'pending')>Pending</option>
                  <option value="in_progress" @selected(($report->status ?? '') === 'in_progress')>In progress</option>
                  <option value="resolved"    @selected(($report->status ?? '') === 'resolved')>Resolved</option>
                </select>
                <button class="w-full rounded-xl bg-gradient-to-r from-amber-600 to-rose-600 px-4 py-2 text-white font-semibold shadow hover:shadow-lg">
                  Update Status
                </button>
              </form>
              <p class="mt-2 text-xs text-amber-900/60">Tip: choose “In progress” when a team is assigned.</p>
            </div>
          @endif

          <a href="{{ route('admin.reports.index') }}"
             class="block text-center rounded-xl px-4 py-2 bg-white ring-1 ring-amber-900/10
                    text-amber-900/90 shadow hover:shadow-md transition">
            ← Back
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
