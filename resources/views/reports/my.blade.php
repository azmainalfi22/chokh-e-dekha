@extends(auth()->user()->is_admin ? 'layouts.admin' : 'layouts.app')

@section('title', 'My Reports')

@push('styles')
<style>
  /* Soft grain overlay (subtle) */
  .grainy::before{
    content:"";
    position:absolute; inset:0; pointer-events:none; z-index:0;
    opacity:.18; mix-blend:multiply;
    background-size: 220px 220px;
    background-repeat: repeat;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='180' height='180'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3CfeColorMatrix type='saturate' values='0'/%3E%3C/feTurbulence%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.20'/%3E%3C/svg%3E");
  }
</style>
@endpush

@section('content')
<div class="relative grainy">
  {{-- Ambient blobs --}}
  <div class="pointer-events-none absolute -top-20 -right-24 h-80 w-80 rounded-full blur-3xl opacity-20 bg-gradient-to-br from-amber-300 to-rose-300"></div>
  <div class="pointer-events-none absolute -bottom-24 -left-24 h-96 w-96 rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-orange-300 to-pink-300"></div>

  <div class="max-w-7xl mx-auto p-4 md:p-8 relative z-[1]">
    {{-- Header --}}
    <header class="mb-8">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="min-w-0">
          <h1 class="text-3xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-amber-700 via-orange-700 to-rose-700">
            My Reports
          </h1>
          <p class="text-sm text-amber-900/70">Your submitted issues, all in one place.</p>
        </div>

        @if(Route::has('report.create'))
          <a href="{{ route('report.create') }}"
             class="inline-flex items-center gap-2 px-4 py-2 rounded-xl shadow hover:shadow-md bg-amber-600 text-white hover:bg-amber-700 transition self-start md:self-auto">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11 5h2v14h-2zM5 11h14v2H5z"/></svg>
            New Report
          </a>
        @endif
      </div>
    </header>

    @php
      $badge = function($report) {
        $status = $report->status ?? 'pending';
        $map = [
          'pending'     => 'bg-amber-100 text-amber-800 ring-amber-200',
          'in_progress' => 'bg-blue-100 text-blue-800 ring-blue-200',
          'resolved'    => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
          'rejected'    => 'bg-rose-100 text-rose-800 ring-rose-200',
        ];
        $cls = $map[$status] ?? 'bg-gray-100 text-gray-800 ring-gray-200';
        return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ring-1 ring-inset '.$cls.'">'.\Illuminate\Support\Str::headline($status).'</span>';
      };
    @endphp

    {{-- Empty state --}}
    @if($reports->isEmpty())
      <div class="rounded-2xl border border-dashed border-amber-300 bg-white/60 backdrop-blur px-6 py-12 text-center shadow">
        <div class="mx-auto mb-3 inline-flex h-12 w-12 items-center justify-center rounded-full ring-1 ring-amber-200 bg-amber-50">
          <svg class="h-5 w-5 text-amber-700" viewBox="0 0 24 24" fill="currentColor"><path d="M3 5h18v2H3zM3 10h18v2H3zM3 15h12v2H3z"/></svg>
        </div>
        <h3 class="text-lg font-semibold text-amber-800">You haven’t submitted any reports</h3>
        <p class="text-sm text-amber-900/70 mt-1">Create your first one to help improve the city.</p>
        @if(Route::has('report.create'))
          <a href="{{ route('report.create') }}" class="mt-4 inline-flex px-4 py-2 rounded-xl bg-amber-600 text-white hover:bg-amber-700">New Report</a>
        @endif
      </div>
    @else
      {{-- Cards --}}
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($reports as $report)
          <div class="rounded-2xl bg-white/80 backdrop-blur shadow hover:shadow-lg transition overflow-hidden ring-1 ring-amber-100">
            <div class="p-5 flex flex-col gap-3">
              <div class="flex items-start justify-between gap-3">
                <h3 class="text-lg font-bold text-gray-900 leading-snug line-clamp-2">{{ $report->title }}</h3>
                {!! $badge($report) !!}
              </div>

              <ul class="text-sm text-gray-700 space-y-1">
                <li class="flex items-center gap-2">
                  <svg class="h-4 w-4 text-amber-800/80" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8 2 5 5 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-4-3-7-7-7zm0 9.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z"/></svg>
                  <span class="font-medium">{{ $report->location ?? 'N/A' }}</span>
                </li>
                <li class="flex items-center gap-2">
                  <svg class="h-4 w-4 text-amber-800/80" viewBox="0 0 24 24" fill="currentColor"><path d="M4 6h16v2H4zM4 10h16v8H4z"/></svg>
                  <span>{{ $report->category ?? 'General' }}</span>
                </li>
                <li class="flex items-center gap-2">
                  <svg class="h-4 w-4 text-amber-800/80" viewBox="0 0 24 24" fill="currentColor"><path d="M5 4h14v2H5zM5 8h14v12H5z"/></svg>
                  <span>{{ $report->city_corporation ?? '—' }}</span>
                </li>
                <li class="flex items-center gap-2">
                  <svg class="h-4 w-4 text-amber-800/80" viewBox="0 0 24 24" fill="currentColor"><path d="M7 2h10v2H7zM5 6h14v14H5zM9 8h6v6H9z"/></svg>
                  <span>{{ optional($report->created_at)->format('M d, Y h:i a') }}</span>
                </li>
              </ul>

              <div class="mt-3 flex items-center justify-between">
                <a href="{{ route('reports.show', $report) }}"
                   class="inline-flex items-center gap-1 text-amber-700 hover:text-amber-800 font-medium">
                  View details
                  <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M10 6l6 6-6 6-1.4-1.4L12.2 12 8.6 7.4z"/></svg>
                </a>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      {{-- Pagination --}}
      @if(method_exists($reports,'links'))
        <div class="mt-6">
          {{ $reports->links() }}
        </div>
      @endif
    @endif
  </div>
</div>
@endsection
