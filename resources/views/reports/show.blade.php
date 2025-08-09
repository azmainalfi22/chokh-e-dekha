@extends(auth()->user()->is_admin ? 'layouts.admin' : 'layouts.app')

@section('title', 'Report Details')

@section('content')
<div class="relative">
  <div class="pointer-events-none absolute -top-20 -right-24 h-80 w-80 rounded-full blur-3xl opacity-20 bg-gradient-to-br from-amber-300 to-rose-300"></div>
  <div class="pointer-events-none absolute -bottom-24 -left-24 h-96 w-96 rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-orange-300 to-pink-300"></div>

  <div class="max-w-4xl mx-auto p-4 md:p-8 relative">
    @php
      $status = $report->status ?? 'pending';
      $map = [
        'pending' => 'bg-amber-100 text-amber-800 ring-amber-200',
        'in_progress' => 'bg-blue-100 text-blue-800 ring-blue-200',
        'resolved' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
        'rejected' => 'bg-rose-100 text-rose-800 ring-rose-200',
      ];
      $cls = $map[$status] ?? 'bg-gray-100 text-gray-800 ring-gray-200';
    @endphp

    <div class="bg-white/80 backdrop-blur rounded-2xl shadow-2xl p-6 ring-1 ring-amber-100">
      <div class="flex items-start justify-between mb-6">
        <div>
          <h1 class="text-3xl font-extrabold text-amber-800">{{ $report->title }}</h1>
          <p class="text-sm text-gray-500 mt-1">
            Submitted by <span class="font-medium">{{ $report->user->name ?? 'Unknown' }}</span> •
            {{ $report->created_at?->format('M d, Y h:i a') }}
          </p>
        </div>
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ring-1 ring-inset {{ $cls }}">
          {{ str($status)->headline() }}
        </span>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-3 text-sm text-gray-700">
          <div>📍 <span class="font-medium">Location:</span> {{ $report->location ?? '—' }}</div>
          <div>🏷️ <span class="font-medium">Category:</span> {{ $report->category ?? '—' }}</div>
          <div>🏛️ <span class="font-medium">City Corp:</span> {{ $report->city_corporation ?? '—' }}</div>
        </div>
        @if(($report->photo_url ?? false) || ($report->photo ?? false))
          <div class="rounded-xl overflow-hidden ring-1 ring-amber-100">
            <img src="{{ $report->photo_url ?? asset('storage/'.($report->photo ?? '')) }}" alt="Attachment" class="w-full h-64 object-cover">
          </div>
        @endif
      </div>

      <div class="mt-6">
        <h2 class="text-lg font-semibold text-amber-800 mb-2">Description</h2>
        <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $report->description ?? 'No description provided.' }}</p>
      </div>

      <div class="mt-8 flex items-center justify-between">
        <a href="{{ auth()->id() === ($report->user_id ?? null) ? route('reports.my') : route('reports.index') }}" class="text-gray-600 hover:text-gray-800">← Back</a>


    @if ($report->photo_url)
      <img src="{{ $report->photo_url }}"
          alt="Attachment"
          class="w-full h-64 object-cover rounded-xl ring-1 ring-amber-900/10 shadow"
          loading="lazy">
    @else
      <div class="w-full h-64 grid place-items-center rounded-xl ring-1 ring-amber-900/10 text-amber-900/60">
        No attachment
      </div>
    @endif





        @if(auth()->user()->is_admin && isset($report->id) && Route::has('admin.reports.update'))
          <form action="{{ route('admin.reports.update', $report->id) }}" method="POST" class="inline-flex items-center gap-2">
            @csrf
            @method('PATCH')
            <select name="status" class="rounded-xl border-gray-300 focus:border-amber-500 focus:ring-amber-500 text-sm">
              <option value="pending" @selected(($report->status ?? '') === 'pending')>Pending</option>
              <option value="in_progress" @selected(($report->status ?? '') === 'in_progress')>In Progress</option>
              <option value="resolved" @selected(($report->status ?? '') === 'resolved')>Resolved</option>
              <option value="rejected" @selected(($report->status ?? '') === 'rejected')>Rejected</option>
            </select>
            <button class="px-4 py-2 rounded-xl bg-amber-600 text-white hover:bg-amber-700">Update Status</button>
          </form>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
