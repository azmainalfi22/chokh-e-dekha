@extends('layouts.app')
@section('title', 'Dashboard')

@push('styles')
<style>
  :root {
    --cd-card-light: rgba(255,255,255,0.92);
    --cd-card-dark: rgba(241, 230, 216, 0.51);
    --cd-amber: 245, 158, 11;
    --page-light: #fffaf5;
    --page-dark:  #0b0e12;
    --page-dark-grad-1: rgba(255,179,0,.06);
    --page-dark-grad-2: rgba(244,63,94,.07);
    --text-body: #0f172a; /* slate-900 */
  }

  /* Page background: dark mode changes ONLY the background, not text color */
  body{
    min-height:100vh;
    background: radial-gradient(1200px 400px at -10% -10%, rgba(251,191,36,.12), transparent 40%),
                radial-gradient(1000px 300px at 110% 110%, rgba(244,63,94,.10), transparent 45%),
                var(--page-light);
    color: var(--text-body);
  }
  .dark body{
    background:
      radial-gradient(1200px 400px at -10% -10%, var(--page-dark-grad-1), transparent 40%),
      radial-gradient(1000px 300px at 110% 110%, var(--page-dark-grad-2), transparent 45%),
      var(--page-dark);
    /* no text color override on purpose */
  }

  /* Cards: switch surface in dark, but KEEP text dark/slate */
  .cd-card {
    background: var(--cd-card-light);
    backdrop-filter: blur(8px);
    transition: transform .15s ease, box-shadow .15s ease, background-color .2s ease;
    color: var(--text-body);
  }
  .cd-card:hover { transform: translateY(-1px); box-shadow: 0 4px 16px rgba(0,0,0,.08); }
  .dark .cd-card { background: var(--cd-card-dark); color: var(--text-body); }

  /* Table readability in both modes */
  .cd-card table, .cd-card th, .cd-card td { color: var(--text-body); }
  .cd-card tbody tr { border-color: rgba(251,191,36,.35); }

  .cd-chip {
    box-shadow: inset 0 1px 0 rgba(255,255,255,.6), 0 1px 2px rgba(0,0,0,.06);
    transition: background-color .18s ease, transform .08s ease;
  }
  .cd-chip:active { transform: translateY(1px) }
</style>
@endpush

@section('content')
<div class="relative">
  {{-- background blobs --}}
  <div class="pointer-events-none absolute -top-20 -right-24 h-80 w-80 rounded-full blur-3xl opacity-20 bg-gradient-to-br from-amber-300 to-rose-300"></div>
  <div class="pointer-events-none absolute -bottom-24 -left-24 h-96 w-96 rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-orange-300 to-pink-300"></div>

  {{-- flash --}}
  @if (session('error'))
    <div class="mb-4 rounded-xl px-4 py-3 bg-rose-50 text-rose-800 ring-1 ring-rose-200">
      {{ session('error') }}
    </div>
  @endif

  {{-- welcome / actions --}}
  <section class="mb-6">
    <div class="cd-card rounded-2xl ring-1 ring-amber-900/10 shadow p-6">
      <h1 class="text-2xl md:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-amber-700 via-orange-700 to-rose-700">
        👋 Welcome, {{ auth()->user()->name }}!
      </h1>
      <p class="text-sm text-gray-800 mt-1">This is your command center for civic impact.</p>

      <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-4">
        @php
          $actions = [
            [
              'route' => 'report.create',
              'icon'  => '<path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z"/>',
              'bg'    => 'from-emerald-500 to-green-600',
              'title' => 'Submit Report',
              'desc'  => 'Let the city know'
            ],
            [
              'route' => 'reports.my',
              'icon'  => '<path d="M4 6h16v2H4zm0 4h10v2H4zm0 4h16v2H4z"/>',
              'bg'    => 'from-indigo-500 to-violet-600',
              'title' => 'My Reports',
              'desc'  => 'Track your submissions'
            ],
            [
              'route' => 'profile.edit',
              'icon'  => '<path d="M12 12a5 5 0 100-10 5 5 0 000 10zm7 2H5a2 2 0 00-2 2v5h18v-5a2 2 0 00-2-2z"/>',
              'bg'    => 'from-amber-500 to-rose-600',
              'title' => 'Edit Profile',
              'desc'  => 'Manage your identity'
            ],
          ];
        @endphp

        @foreach($actions as $a)
          <a href="{{ route($a['route']) }}" class="cd-card group rounded-2xl ring-1 ring-amber-900/10 shadow hover:shadow-lg transition p-5 flex flex-col items-center text-center">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br {{ $a['bg'] }} text-white shadow mb-3">
              <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">{!! $a['icon'] !!}</svg>
            </div>
            <div class="font-semibold">{{ $a['title'] }}</div>
            <div class="text-xs text-gray-700">{{ $a['desc'] }}</div>
          </a>
        @endforeach
      </div>
    </div>
  </section>

  {{-- stats --}}
  <section class="mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      @php
        $statBoxes = [
          ['val' => $stats['total'],   'bg' => 'from-amber-500 to-rose-600',   'label' => 'Total Reports'],
          ['val' => $stats['pending'], 'bg' => 'from-yellow-400 to-orange-500','label' => 'Pending'],
          ['val' => $stats['resolved'],'bg' => 'from-emerald-500 to-green-700','label' => 'Resolved'],
        ];
      @endphp
      @foreach($statBoxes as $sb)
        <div class="text-white p-6 rounded-2xl shadow-2xl text-center bg-gradient-to-br {{ $sb['bg'] }}">
          <div class="text-4xl font-extrabold counter" data-target="{{ $sb['val'] }}">0</div>
          <div class="text-xs mt-2 tracking-wide uppercase opacity-90">{{ $sb['label'] }}</div>
        </div>
      @endforeach
    </div>
  </section>

  {{-- recent reports --}}
  <section class="cd-card rounded-2xl ring-1 ring-amber-900/10 shadow p-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold text-amber-900">Recent Reports</h2>
      <a href="{{ route('reports.my') }}" class="text-sm text-rose-700 hover:underline">View all</a>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="uppercase text-xs tracking-wider">
            <th class="px-4 py-2 text-left">Title</th>
            <th class="px-4 py-2 text-left">City</th>
            <th class="px-4 py-2 text-left">Status</th>
            <th class="px-4 py-2 text-left">Date</th>
            <th class="px-4 py-2 text-right">Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentReports as $report)
            <tr class="border-b hover:bg-amber-50 transition">
              <td class="px-4 py-2 font-medium">{{ $report->title }}</td>
              <td class="px-4 py-2">{{ $report->city_corporation }}</td>
              <td class="px-4 py-2">
                @php $resolved = $report->status === 'resolved'; @endphp
                <span class="px-2 py-1 text-xs rounded-full font-semibold
                  {{ $resolved ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800' }}">
                  {{ ucfirst($report->status) }}
                </span>
              </td>
              <td class="px-4 py-2">{{ $report->created_at->format('M d, Y h:i a') }}</td>
              <td class="px-4 py-2 text-right">
                <a href="{{ route('reports.show', $report) }}" class="cd-chip inline-flex items-center gap-2 rounded-xl px-3 py-1.5 text-sm text-white bg-gradient-to-r from-amber-600 to-rose-600 shadow hover:shadow-lg transition">
                  View
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-4 py-6 text-center text-gray-700">No reports yet. Create your first one!</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>

  <footer class="text-center text-sm text-gray-700 mt-8">
    © {{ now()->year }} {{ config('app.name', 'Chokh-e-Dekha') }}. Made with ❤️ for civic good.
  </footer>
</div>

{{-- counters --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.counter').forEach(counter => {
    const target = Number(counter.getAttribute('data-target') || 0);
    let current = 0;
    const step = Math.max(1, Math.ceil(target / 80));
    const tick = () => {
      current = Math.min(target, current + step);
      counter.textContent = current.toLocaleString();
      if (current < target) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
  });
});
</script>
@endsection
