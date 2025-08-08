@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="relative">
    {{-- background blobs --}}
    <div class="pointer-events-none absolute -top-20 -right-24 h-80 w-80 rounded-full blur-3xl opacity-20
                bg-gradient-to-br from-amber-300 to-rose-300"></div>
    <div class="pointer-events-none absolute -bottom-24 -left-24 h-96 w-96 rounded-full blur-3xl opacity-20
                bg-gradient-to-tr from-orange-300 to-pink-300"></div>

    {{-- flash --}}
    @if (session('error'))
        <div class="mb-4 rounded-xl px-4 py-3 bg-rose-50 text-rose-800 ring-1 ring-rose-200">
            {{ session('error') }}
        </div>
    @endif

    {{-- welcome / actions --}}
    <section class="mb-6">
        <div class="rounded-2xl bg-white/85 backdrop-blur ring-1 ring-amber-900/10 shadow p-6">
            <h1 class="text-2xl md:text-3xl font-extrabold text-transparent bg-clip-text
                       bg-gradient-to-r from-amber-700 via-orange-700 to-rose-700">
                👋 Welcome, {{ auth()->user()->name }}!
            </h1>
            <p class="text-sm text-amber-900/70 mt-1">This is your command center for civic impact.</p>

            <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="{{ route('report.create') }}" class="group rounded-2xl bg-white ring-1 ring-amber-900/10 shadow hover:shadow-lg transition p-5 flex flex-col items-center text-center">
                    <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl
                                bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow mb-3">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor"><path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z"/></svg>
                    </div>
                    <div class="font-semibold">Submit Report</div>
                    <div class="text-xs text-amber-900/70">Let the city know</div>
                </a>

                <a href="{{ route('reports.my') }}" class="group rounded-2xl bg-white ring-1 ring-amber-900/10 shadow hover:shadow-lg transition p-5 flex flex-col items-center text-center">
                    <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl
                                bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow mb-3">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor"><path d="M4 6h16v2H4zm0 4h10v2H4zm0 4h16v2H4z"/></svg>
                    </div>
                    <div class="font-semibold">My Reports</div>
                    <div class="text-xs text-amber-900/70">Track your submissions</div>
                </a>

                <a href="{{ route('profile.edit') }}" class="group rounded-2xl bg-white ring-1 ring-amber-900/10 shadow hover:shadow-lg transition p-5 flex flex-col items-center text-center">
                    <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl
                                bg-gradient-to-br from-amber-500 to-rose-600 text-white shadow mb-3">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zm7 2H5a2 2 0 00-2 2v5h18v-5a2 2 0 00-2-2z"/></svg>
                    </div>
                    <div class="font-semibold">Edit Profile</div>
                    <div class="text-xs text-amber-900/70">Manage your identity</div>
                </a>
            </div>
        </div>
    </section>

    {{-- stats --}}
    <section class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-white p-6 rounded-2xl shadow-2xl text-center bg-gradient-to-br from-amber-500 to-rose-600">
                <div class="text-4xl font-extrabold counter" data-target="{{ $stats['total'] }}">0</div>
                <div class="text-xs mt-2 tracking-wide uppercase opacity-90">Total Reports</div>
            </div>
            <div class="text-white p-6 rounded-2xl shadow-2xl text-center bg-gradient-to-br from-yellow-400 to-orange-500">
                <div class="text-4xl font-extrabold counter" data-target="{{ $stats['pending'] }}">0</div>
                <div class="text-xs mt-2 tracking-wide uppercase opacity-90">Pending</div>
            </div>
            <div class="text-white p-6 rounded-2xl shadow-2xl text-center bg-gradient-to-br from-emerald-500 to-green-700">
                <div class="text-4xl font-extrabold counter" data-target="{{ $stats['resolved'] }}">0</div>
                <div class="text-xs mt-2 tracking-wide uppercase opacity-90">Resolved</div>
            </div>
        </div>
    </section>

    {{-- recent reports --}}
    <section class="rounded-2xl bg-white/85 backdrop-blur ring-1 ring-amber-900/10 shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-amber-900">Recent Reports</h2>
            <a href="{{ route('reports.my') }}" class="text-sm text-rose-700 hover:underline">View all</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-amber-100 text-amber-900 uppercase text-xs tracking-wider">
                        <th class="px-4 py-2 text-left">Title</th>
                        <th class="px-4 py-2 text-left">City</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left">Date</th>
                        <th class="px-4 py-2 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="text-amber-900/90">
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
                                <a href="{{ route('reports.show', $report) }}" class="inline-flex items-center gap-2 rounded-xl px-3 py-1.5 text-sm text-white bg-gradient-to-r from-amber-600 to-rose-600 shadow hover:shadow-lg transition">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-amber-900/70">No reports yet. Create your first one!</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <footer class="text-center text-sm text-amber-900/60 mt-8">
        © {{ now()->year }} {{ config('app.name', 'Chokh-e-Dekha') }}. Made with ❤️ for civic good.
    </footer>
</div>

{{-- counters --}}
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
@endsection
