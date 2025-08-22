@extends('layouts.app')
@section('title', 'Dashboard')

@push('styles')
<style>
  /* Page-specific styles only (theme tokens come from partials._theme via the layout) */

  /* Soft background accents */
  .blob{ position:absolute; border-radius:9999px; filter:blur(36px); opacity:.2; pointer-events:none; }

  /* Glassy card */
  .cd-card{
    position:relative; background: var(--surface); color: var(--text);
    border:1px solid var(--ring); border-radius: var(--radius-2xl); padding: var(--space-6);
    backdrop-filter: blur(10px); box-shadow: var(--shadow-lg);
    transition: transform var(--duration-fast) var(--ease-in-out),
                box-shadow var(--duration-fast) var(--ease-in-out),
                border-color var(--duration-fast) var(--ease-in-out);
  }
  .cd-card::before{
    content:""; position:absolute; inset:0; pointer-events:none; border-radius:inherit;
    background:
      radial-gradient(1200px 400px at -10% -10%, rgba(251,191,36,.12), transparent 40%),
      radial-gradient(1000px 300px at 110% 110%, rgba(244,63,94,.10), transparent 45%);
  }
  .cd-card:hover{ transform: translateY(-2px); box-shadow: var(--shadow-xl); border-color: var(--accent); }

  /* Chips / small buttons */
  .cd-chip{
    display:inline-flex; align-items:center; gap:.5rem; font-weight:600;
    border-radius: var(--radius-xl); padding:.4rem .75rem; line-height:1; border:1px solid var(--ring);
    background: var(--surface); color: var(--text);
    box-shadow: var(--shadow-sm);
    transition: transform var(--duration-fast) var(--ease-in-out),
                box-shadow var(--duration-normal) var(--ease-in-out),
                border-color var(--duration-normal) var(--ease-in-out);
  }
  .cd-chip:hover{ transform: translateY(-1px); box-shadow: var(--shadow-md); border-color: var(--accent); }

  /* Status pills (token-based) */
  .status-pill{
    display:inline-flex; align-items:center; gap:.35rem; padding:.25rem .6rem;
    border-radius: var(--radius-full); font-size: var(--text-xs); font-weight:700;
    border:1px solid transparent; line-height:1; white-space:nowrap; box-shadow: var(--shadow-sm);
  }
  .status-pending     { background: var(--status-pending-bg);     color: var(--status-pending-text);     border-color: var(--status-pending-border); }
  .status-in_progress { background: var(--status-in-progress-bg); color: var(--status-in-progress-text); border-color: var(--status-in-progress-border); }
  .status-resolved    { background: var(--status-resolved-bg);    color: var(--status-resolved-text);    border-color: var(--status-resolved-border); }
  .status-rejected    { background: var(--status-rejected-bg);    color: var(--status-rejected-text);    border-color: var(--status-rejected-border); }

  /* Counters (stat tiles) */
  .stat-tile{
    color:#fff; padding: var(--space-6); border-radius: var(--radius-2xl);
    box-shadow: var(--shadow-xl); text-align:center;
  }
  .stat-value{ font-size: var(--text-4xl); font-weight: 800; }
  .stat-label{ font-size: var(--text-xs); margin-top:.35rem; letter-spacing:.06em; text-transform:uppercase; opacity:.9; }

  /* Table */
  table th, table td { color: var(--text); }
  thead th{
    font-size: var(--text-xs); text-transform: uppercase; letter-spacing:.06em;
    color: var(--text-secondary);
    border-bottom: 1px solid var(--ring);
    padding: .5rem 1rem;
  }
  tbody td{ padding:.5rem 1rem; border-bottom:1px solid var(--ring); }

  /* Footer text tone */
  .muted{ color: var(--text-secondary); }
</style>
@endpush

@section('content')
<div class="relative">
  {{-- Background blobs (behind everything) --}}
  <div class="blob pointer-events-none absolute -top-20 -right-24 h-80 w-80" style="background:linear-gradient(135deg,#fbbf24,#fb7185)"></div>
  <div class="blob pointer-events-none absolute -bottom-24 -left-24 h-96 w-96" style="background:linear-gradient(135deg,#fb923c,#f472b6)"></div>

  {{-- Flash error --}}
  @if (session('error'))
    <div class="mb-4 rounded-xl px-4 py-3"
         style="background:var(--error-50); color:var(--error-700); border:1px solid var(--error-100); box-shadow:var(--shadow-sm);">
      {{ session('error') }}
    </div>
  @endif

  {{-- Welcome / quick actions --}}
  <section class="mb-6">
    <div class="cd-card rounded-2xl ring-1 ring-amber-900/10 shadow p-6">
      <h1 class="text-2xl md:text-3xl font-extrabold text-transparent bg-clip-text"
          style="background-image:linear-gradient(90deg,#fbbf24,#ea580c,#f43f5e)">
        👋 Welcome, {{ auth()->user()->name }}!
      </h1>
      <p class="text-sm text-secondary mt-1">This is your command center for civic impact.</p>

      <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-4">
        @php
          $actions = [
            [
              'route' => 'report.create',
              'icon'  => '<path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z"/>',
              'bg'    => 'background-image:linear-gradient(135deg,#10b981,#059669);',
              'title' => 'Submit Report',
              'desc'  => 'Let the city know'
            ],
            [
              'route' => 'reports.my',
              'icon'  => '<path d="M4 6h16v2H4zm0 4h10v2H4zm0 4h16v2H4z"/>',
              'bg'    => 'background-image:linear-gradient(135deg,#6366f1,#7c3aed);',
              'title' => 'My Reports',
              'desc'  => 'Track your submissions'
            ],
            [
              'route' => 'profile.edit',
              'icon'  => '<path d="M12 12a5 5 0 100-10 5 5 0 000 10zm7 2H5a2 2 0 00-2 2v5h18v-5a2 2 0 00-2-2z"/>',
              'bg'    => 'background-image:linear-gradient(135deg,#f59e0b,#f43f5e);',
              'title' => 'Edit Profile',
              'desc'  => 'Manage your identity'
            ],
          ];
        @endphp

        @foreach($actions as $a)
          <a href="{{ route($a['route']) }}"
             class="cd-card group rounded-2xl ring-1 ring-amber-900/10 shadow hover:shadow-lg transition p-5 flex flex-col items-center text-center">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl text-white shadow mb-3"
                 style="{{ $a['bg'] }}">
              <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">{!! $a['icon'] !!}</svg>
            </div>
            <div class="font-semibold">{{ $a['title'] }}</div>
            <div class="text-xs text-secondary">{{ $a['desc'] }}</div>
          </a>
        @endforeach
      </div>
    </div>
  </section>

  {{-- Stats --}}
  <section class="mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      @php
        $statBoxes = [
          ['val' => $stats['total']    ?? 0, 'bg' => 'linear-gradient(135deg,#f59e0b,#f43f5e)', 'label' => 'Total Reports'],
          ['val' => $stats['pending']  ?? 0, 'bg' => 'linear-gradient(135deg,#fbbf24,#ea580c)', 'label' => 'Pending'],
          ['val' => $stats['resolved'] ?? 0, 'bg' => 'linear-gradient(135deg,#10b981,#047857)', 'label' => 'Resolved'],
        ];
      @endphp

      @foreach($statBoxes as $sb)
        <div class="stat-tile" style="background:{{ $sb['bg'] }}">
          <div class="stat-value counter" data-target="{{ $sb['val'] }}">0</div>
          <div class="stat-label">{{ $sb['label'] }}</div>
        </div>
      @endforeach
    </div>
  </section>

  {{-- Recent reports --}}
  <section class="cd-card rounded-2xl ring-1 ring-amber-900/10 shadow p-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold" style="color:var(--text);">Recent Reports</h2>
      <a href="{{ route('reports.my') }}" class="text-sm text-accent hover:underline">View all</a>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr>
            <th class="text-left">Title</th>
            <th class="text-left">City</th>
            <th class="text-left">Status</th>
            <th class="text-left">Date</th>
            <th class="text-right">Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentReports as $report)
            @php
              $status = $report->status ?? 'pending';
              $statusClass = match($status){
                'resolved'     => 'status-resolved',
                'in_progress'  => 'status-in_progress',
                'rejected'     => 'status-rejected',
                default        => 'status-pending'
              };
            @endphp
            <tr class="hover:bg-[rgba(245,158,11,0.05)] transition-colors">
              <td class="font-medium">{{ $report->title }}</td>
              <td>{{ $report->city_corporation }}</td>
              <td>
                <span class="status-pill {{ $statusClass }}">
                  {{ \Illuminate\Support\Str::headline($status) }}
                </span>
              </td>
              <td>{{ optional($report->created_at)->format('M d, Y h:i a') }}</td>
              <td class="text-right">
                <a href="{{ route('reports.show', $report) }}"
                   class="cd-chip">
                  View
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-4 py-6 text-center text-secondary">No reports yet. Create your first one!</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>

  <footer class="text-center text-sm muted mt-8">
    © {{ now()->year }} {{ config('app.name', 'Chokh-e-Dekha') }}. Made with ❤️ for civic good.
  </footer>
</div>

{{-- Counters --}}
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
