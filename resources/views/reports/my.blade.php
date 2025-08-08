@extends(auth()->user()->is_admin ? 'layouts.admin' : 'layouts.app')

@section('title', 'My Reports')

@section('content')
<div class="relative">
  <div class="pointer-events-none absolute -top-20 -right-24 h-80 w-80 rounded-full blur-3xl opacity-20 bg-gradient-to-br from-amber-300 to-rose-300"></div>
  <div class="pointer-events-none absolute -bottom-24 -left-24 h-96 w-96 rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-orange-300 to-pink-300"></div>

  <div class="max-w-7xl mx-auto p-4 md:p-8 relative">
    <header class="mb-8">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 class="text-3xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-amber-700 via-orange-700 to-rose-700">My Reports</h1>
          <p class="text-sm text-amber-900/70">Your submitted issues, all in one place.</p>
        </div>
        <div class="flex items-center gap-3">
          @if(Route::has('report.create'))
          <a href="{{ route('report.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl shadow hover:shadow-md bg-amber-600 text-white hover:bg-amber-700 transition">
            <span class="text-lg">＋</span><span>New Report</span>
          </a>
          @endif
        </div>
      </div>
    </header>

    @php
      $badge = function($report) {
        $status = $report->status ?? 'pending';
        $map = [
          'pending' => 'bg-amber-100 text-amber-800 ring-amber-200',
          'in_progress' => 'bg-blue-100 text-blue-800 ring-blue-200',
          'resolved' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
          'rejected' => 'bg-rose-100 text-rose-800 ring-rose-200',
        ];
        $cls = $map[$status] ?? 'bg-gray-100 text-gray-800 ring-gray-200';
        return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ring-1 ring-inset '.$cls.'">'.str($status)->headline().'</span>';
      };
    @endphp

    @if($reports->isEmpty())
      <div class="rounded-2xl border border-dashed border-amber-300 bg-white/60 backdrop-blur px-6 py-12 text-center shadow">
        <div class="text-4xl mb-2">📝</div>
        <h3 class="text-lg font-semibold text-amber-800">You haven’t submitted any reports</h3>
        <p class="text-sm text-amber-900/70 mt-1">Create your first one to help improve the city.</p>
        @if(Route::has('report.create'))
        <a href="{{ route('report.create') }}" class="mt-4 inline-flex px-4 py-2 rounded-xl bg-amber-600 text-white hover:bg-amber-700">New Report</a>
        @endif
      </div>
    @else
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($reports as $report)
          <div class="rounded-2xl bg-white/80 backdrop-blur shadow hover:shadow-lg transition overflow-hidden ring-1 ring-amber-100">
            <div class="p-5 flex flex-col gap-3">
              <div class="flex items-start justify-between gap-3">
                <h3 class="text-lg font-bold text-gray-900 leading-snug line-clamp-2">{{ $report->title }}</h3>
                {!! $badge($report) !!}
              </div>

              <ul class="text-sm text-gray-600 space-y-1">
                <li>📍 <span class="font-medium">{{ $report->location ?? 'N/A' }}</span></li>
                <li>🏷️ <span>{{ $report->category ?? 'General' }}</span></li>
                <li>🏛️ <span>{{ $report->city_corporation ?? '—' }}</span></li>
                <li>📅 <span>{{ $report->created_at?->format('M d, Y h:i a') }}</span></li>
              </ul>

              <div class="mt-3 flex items-center justify-between">
                <a href="{{ route('reports.show', $report) }}" class="inline-flex items-center gap-1 text-amber-700 hover:text-amber-800 font-medium">
                  View details <span>→</span>
                </a>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      @if(method_exists($reports,'links'))
        <div class="mt-6">
          {{ $reports->links() }}
        </div>
      @endif
    @endif
  </div>
</div>
@endsection
