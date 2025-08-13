@extends(auth()->user()->is_admin ? 'layouts.admin' : 'layouts.app')

@section('title', 'Report Details')

@section('content')
<div class="relative">
  <div class="pointer-events-none absolute -top-20 -right-24 h-80 w-80 rounded-full blur-3xl opacity-20 bg-gradient-to-br from-amber-300 to-rose-300"></div>
  <div class="pointer-events-none absolute -bottom-24 -left-24 h-96 w-96 rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-orange-300 to-pink-300"></div>

  <div class="max-w-5xl mx-auto p-4 md:p-8 relative">

    {{-- flashes (optional) --}}
    @if(session('success'))
      <div class="mb-4 rounded-xl bg-green-50 ring-1 ring-green-200 px-4 py-3 text-green-800">
        {{ session('success') }}
      </div>
    @endif
    @if($errors->any())
      <div class="mb-4 rounded-xl bg-rose-50 ring-1 ring-rose-200 px-4 py-3 text-rose-800">
        <ul class="list-disc pl-5 space-y-1">
          @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    @php
      $status = $report->status ?? 'pending';
      $badge = [
        'pending'     => 'bg-amber-100 text-amber-800 ring-amber-200',
        'in_progress' => 'bg-blue-100 text-blue-800 ring-blue-200',
        'resolved'    => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
        'rejected'    => 'bg-rose-100 text-rose-800 ring-rose-200',
      ][$status] ?? 'bg-gray-100 text-gray-800 ring-gray-200';
    @endphp

    <div class="bg-white/80 backdrop-blur rounded-2xl shadow-2xl p-6 ring-1 ring-amber-100">
      {{-- Header --}}
      <div class="flex items-start justify-between gap-4 mb-6">
        <div>
          <h1 class="text-3xl font-extrabold text-amber-800">
            {{ $report->title ?? ('Report #'.$report->id) }}
          </h1>
          <p class="text-sm text-gray-500 mt-1">
            Submitted by <span class="font-medium">{{ $report->user->name ?? 'Unknown' }}</span> •
            {{ optional($report->created_at)->format('M d, Y h:i a') }}
          </p>
        </div>
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ring-1 ring-inset {{ $badge }}">
          {{ \Illuminate\Support\Str::headline($status) }}
        </span>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Details + Attachment + Admin Notes --}}
        <div class="lg:col-span-2 space-y-6">

          {{-- Details --}}
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
                <dd class="font-medium text-amber-900">{{ \Illuminate\Support\Str::headline($status) }}</dd>
              </div>
              <div class="sm:col-span-2">
                <dt class="text-amber-900/70">Description</dt>
                <dd class="mt-1 whitespace-pre-line text-gray-700">{{ $report->description ?? 'No description provided.' }}</dd>
              </div>
            </dl>
          </div>

          {{-- Attachment (files array OR single photo) --}}
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

          {{-- Admin Notes (read-only) --}}
          <section class="rounded-2xl bg-white/85 backdrop-blur ring-1 ring-amber-900/10 shadow p-6">
            <h2 class="text-lg font-semibold text-amber-800 mb-4">Admin Notes</h2>
            @if(method_exists($report, 'notes') && ($report->relationLoaded('notes') || filled(optional($report)->notes)))
              @forelse($report->notes as $note)
                <div class="rounded-xl bg-amber-50 ring-1 ring-amber-100 p-4 mb-3">
                  <div class="text-sm text-amber-900/90">{{ $note->body }}</div>
                  <div class="mt-2 text-xs text-amber-900/70">
                    — {{ $note->admin?->name ?? 'Admin' }} • {{ $note->created_at->diffForHumans() }}
                  </div>
                </div>
              @empty
                <p class="text-sm text-amber-900/70">No notes yet.</p>
              @endforelse
            @else
              <p class="text-sm text-amber-900/70">No notes yet.</p>
            @endif
          </section>
        </div>

        {{-- Right: Back --}}
        <div class="space-y-6">
          <a href="{{ route('reports.index') }}"
             class="block text-center rounded-xl px-4 py-2 bg-white ring-1 ring-amber-900/10
                    text-amber-900/90 shadow hover:shadow-md transition">
            ← Back to All Reports
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
