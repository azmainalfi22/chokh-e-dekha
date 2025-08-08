@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="relative">
    {{-- Background --}}
    <div class="pointer-events-none absolute -top-20 -right-24 h-80 w-80 rounded-full blur-3xl opacity-20 bg-gradient-to-br from-amber-300 to-rose-300"></div>
    <div class="pointer-events-none absolute -bottom-24 -left-24 h-96 w-96 rounded-full blur-3xl opacity-20 bg-gradient-to-tr from-orange-300 to-pink-300"></div>

    {{-- Header --}}
    <header class="mb-8 relative">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-amber-700 via-orange-700 to-rose-700">
                    Admin Dashboard
                </h1>
                <p class="text-sm text-amber-900/70">Here’s what’s happening at a glance.</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.users.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl px-4 py-2 font-medium
                          bg-white ring-1 ring-amber-900/10 text-amber-900/90 shadow-md
                          hover:shadow-lg hover:-translate-y-0.5 transition">
                    Manage Users
                </a>
                <a href="{{ route('admin.reports.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl px-4 py-2 font-semibold text-white
                          bg-gradient-to-r from-amber-600 to-rose-600 shadow-lg
                          hover:shadow-xl hover:-translate-y-0.5 transition">
                    View Reports
                </a>
            </div>
        </div>
    </header>

    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
        <div class="text-white p-6 rounded-2xl shadow-2xl text-center transform transition hover:scale-[1.02] bg-gradient-to-br from-amber-500 to-rose-600">
            <div class="text-4xl md:text-5xl font-extrabold counter" data-target="{{ $totalReports ?? 0 }}">0</div>
            <div class="text-xs mt-2 tracking-wide uppercase opacity-90">Total Reports</div>
        </div>
        <div class="text-white p-6 rounded-2xl shadow-2xl text-center transform transition hover:scale-[1.02] bg-gradient-to-br from-yellow-400 to-orange-500">
            <div class="text-4xl md:text-5xl font-extrabold counter" data-target="{{ $pendingReports ?? 0 }}">0</div>
            <div class="text-xs mt-2 tracking-wide uppercase opacity-90">Pending</div>
        </div>
        <div class="text-white p-6 rounded-2xl shadow-2xl text-center transform transition hover:scale-[1.02] bg-gradient-to-br from-emerald-500 to-green-700">
            <div class="text-4xl md:text-5xl font-extrabold counter" data-target="{{ $resolvedReports ?? 0 }}">0</div>
            <div class="text-xs mt-2 tracking-wide uppercase opacity-90">Resolved</div>
        </div>
        <div class="text-white p-6 rounded-2xl shadow-2xl text-center transform transition hover:scale-[1.02] bg-gradient-to-br from-indigo-600 to-violet-700">
            <div class="text-4xl md:text-5xl font-extrabold counter" data-target="{{ $totalUsers ?? 0 }}">0</div>
            <div class="text-xs mt-2 tracking-wide uppercase opacity-90">Users</div>
        </div>
    </div>

    {{-- Reports by City --}}
    <section class="bg-white rounded-2xl ring-1 ring-amber-900/10 shadow-xl p-6 mb-8">
        <h2 class="text-lg font-semibold text-amber-900 mb-4">Reports by City</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @forelse($reportsByCity ?? [] as $item)
                <div class="bg-amber-50 text-amber-900 p-4 rounded-xl shadow hover:shadow-lg transition">
                    <div class="text-sm font-semibold">{{ $item->city_corporation }}</div>
                    <div class="text-3xl font-extrabold mt-1">{{ $item->count }}</div>
                </div>
            @empty
                <p class="text-sm text-amber-900/70">No data yet.</p>
            @endforelse
        </div>
    </section>

    {{-- Recent Reports --}}
    <section class="bg-white rounded-2xl ring-1 ring-amber-900/10 shadow-xl p-6 mb-12">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-amber-900">Recent Reports</h2>
            <a href="{{ route('admin.reports.index') }}" class="text-sm text-rose-700 hover:underline">View all</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-amber-100 text-amber-900 uppercase text-xs tracking-wider">
                        <th class="px-4 py-2 text-left">Title</th>
                        <th class="px-4 py-2 text-left">User</th>
                        <th class="px-4 py-2 text-left">City</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left">Date</th>
                    </tr>
                </thead>
                <tbody class="text-amber-900/90">
                    @forelse (($recentReports ?? []) as $report)
                        <tr class="border-b hover:bg-amber-50 transition">
                            <td class="px-4 py-2 font-medium">
                                <a href="{{ route('admin.reports.show', $report) }}" class="text-rose-700 hover:underline">
                                    {{ $report->title }}
                                </a>
                            </td>
                            <td class="px-4 py-2">{{ $report->user->name ?? 'N/A' }}</td>
                            <td class="px-4 py-2">{{ $report->city_corporation }}</td>
                            <td class="px-4 py-2">
                                @php $resolved = ($report->status === 'resolved'); @endphp
                                <span class="px-2 py-1 text-xs rounded-full font-semibold {{ $resolved ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800' }}">
                                    {{ ucfirst($report->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-2">{{ $report->created_at->format('M d, Y h:i a') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-amber-900/70">No recent reports.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <footer class="text-center text-sm text-amber-900/60 mt-10 pb-4">
        © {{ now()->year }} {{ config('app.name', 'Chokh-e-Dekha') }} Admin. All rights reserved.
    </footer>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
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
