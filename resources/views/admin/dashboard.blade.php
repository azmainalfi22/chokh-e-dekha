@extends('layouts.admin')
@section('title', 'Dashboard')

{{-- Optional page header slots (supported by the updated admin layout) --}}
@section('page_title', 'Admin Dashboard')
@section('page_subtitle', "Here’s what’s happening at a glance.")
@section('page_actions')
    <a href="{{ route('admin.users.index') }}"
       class="inline-flex items-center gap-2 rounded-xl px-3 py-2 font-medium
              bg-white ring-1 ring-amber-900/10 text-amber-900/90 shadow hover:shadow-md transition">
        Manage Users
    </a>
    <a href="{{ route('admin.reports.index') }}"
       class="inline-flex items-center gap-2 rounded-xl px-3 py-2 font-semibold text-white
              bg-gradient-to-r from-amber-600 to-rose-600 shadow hover:shadow-lg transition">
        View Reports
    </a>
@endsection

@section('content')
<div class="relative">
    {{-- Background blobs (subtle, matches site theme) --}}
    <div class="pointer-events-none absolute -top-20 -right-24 h-80 w-80 rounded-full blur-3xl opacity-20 bg-gradient-to-br from-amber-300 to-rose-300"></div>
    <div class="pointer-events-none absolute -bottom-24 -left-24 h-96 w-96 rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-orange-300 to-pink-300"></div>

    {{-- Stats --}}
    <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        @php
            $cards = [
                ['label' => 'Total Reports', 'value' => (int)($totalReports ?? 0), 'from' => 'from-amber-500', 'to' => 'to-rose-600'],
                ['label' => 'Pending',       'value' => (int)($pendingReports ?? 0), 'from' => 'from-yellow-400', 'to' => 'to-orange-500'],
                ['label' => 'Resolved',      'value' => (int)($resolvedReports ?? 0), 'from' => 'from-emerald-500', 'to' => 'to-green-700'],
                ['label' => 'Users',         'value' => (int)($totalUsers ?? 0), 'from' => 'from-indigo-600', 'to' => 'to-violet-700'],
            ];
        @endphp

        @foreach($cards as $c)
            <div class="text-white p-6 rounded-2xl shadow-2xl text-center transform transition hover:scale-[1.015] bg-gradient-to-br {{ $c['from'] }} {{ $c['to'] }}">
                <div class="text-4xl md:text-5xl font-extrabold counter" data-target="{{ $c['value'] }}">0</div>
                <div class="text-xs mt-2 tracking-wide uppercase/90 opacity-90">{{ $c['label'] }}</div>
            </div>
        @endforeach
    </section>

    {{-- Reports by City --}}
    <section class="bg-white/85 backdrop-blur rounded-2xl ring-1 ring-amber-900/10 shadow p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-amber-900">Reports by City</h2>
            <a href="{{ route('admin.reports.index') }}" class="text-sm text-rose-700 hover:underline">Filter & View</a>
        </div>

        @if(!empty($reportsByCity ?? []))
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($reportsByCity as $item)
                    <div class="rounded-xl border border-amber-100 bg-amber-50/70 text-amber-900 p-4 shadow-sm hover:shadow transition">
                        <div class="text-sm font-semibold truncate">{{ $item->city_corporation }}</div>
                        <div class="text-3xl font-extrabold mt-1">{{ number_format($item->count) }}</div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-amber-900/70">No data yet.</p>
        @endif
    </section>

    {{-- Recent Reports --}}
    <section class="bg-white/85 backdrop-blur rounded-2xl ring-1 ring-amber-900/10 shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-amber-900">Recent Reports</h2>
            <a href="{{ route('admin.reports.index') }}" class="text-sm text-rose-700 hover:underline">View all</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-amber-100/80 text-amber-900 uppercase text-xs tracking-wider">
                        <th class="px-4 py-2 text-left">Title</th>
                        <th class="px-4 py-2 text-left">User</th>
                        <th class="px-4 py-2 text-left">City</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left">Date</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="text-amber-900/90">
                    @php
                        $badge = fn($s) => match($s) {
                            'resolved'    => 'bg-green-200 text-green-800',
                            'in_progress' => 'bg-blue-200 text-blue-800',
                            'pending'     => 'bg-yellow-200 text-yellow-800',
                            'rejected'    => 'bg-rose-200 text-rose-800',
                            default       => 'bg-gray-200 text-gray-800',
                        };
                    @endphp
                    @forelse(($recentReports ?? []) as $report)
                        <tr class="border-b last:border-0 hover:bg-amber-50/60 transition">
                            <td class="px-4 py-2 font-medium max-w-[320px] truncate">
                                <a href="{{ route('admin.reports.show', $report) }}" class="text-rose-700 hover:underline">
                                    {{ $report->title ?? ('Report #'.$report->id) }}
                                </a>
                            </td>
                            <td class="px-4 py-2">{{ $report->user->name ?? 'N/A' }}</td>
                            <td class="px-4 py-2">{{ $report->city_corporation ?? '—' }}</td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 text-xs rounded-full font-semibold {{ $badge($report->status) }}">
                                    {{ \Illuminate\Support\Str::headline((string)$report->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ optional($report->created_at)->format('M d, Y h:i a') }}</td>
                            <td class="px-4 py-2 text-right">
                                <a href="{{ route('admin.reports.show', $report) }}"
                                   class="inline-flex items-center rounded-xl px-3 py-1.5 text-xs font-medium
                                          bg-white ring-1 ring-amber-900/10 text-amber-900/90 shadow hover:shadow-md">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-amber-900/70">No recent reports.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <footer class="text-center text-sm text-amber-900/60 mt-10">
        © {{ now()->year }} {{ config('app.name', 'Chokh-e-Dekha') }} Admin. All rights reserved.
    </footer>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Smooth number counters
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
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
@endpush
