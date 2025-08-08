@extends('layouts.admin')
@section('title', 'Reports')

@section('content')
<div class="relative">
    {{-- Header + filters --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold bg-clip-text text-transparent
                       bg-gradient-to-r from-amber-700 via-orange-700 to-rose-700">
                Reports
            </h1>
            <p class="text-sm text-amber-900/70">Search, filter and review all incoming reports.</p>
        </div>

        <form method="GET" action="{{ route('admin.reports.index') }}"
              class="bg-white/85 backdrop-blur rounded-2xl ring-1 ring-amber-900/10 shadow p-3 md:p-4 grid grid-cols-1 md:grid-cols-6 gap-3 md:gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search title or user…"
                   class="md:col-span-2 rounded-xl border-0 ring-1 ring-amber-900/10 focus:ring-2 focus:ring-rose-400/60
                          bg-white/80 px-3 py-2.5 text-sm placeholder:text-amber-900/40">

            <select name="status"
                    class="rounded-xl border-0 ring-1 ring-amber-900/10 bg-white/80 px-3 py-2.5 text-sm focus:ring-2 focus:ring-rose-400/60">
                <option value="">All status</option>
                <option value="pending"  @selected(request('status')==='pending')>Pending</option>
                <option value="resolved" @selected(request('status')==='resolved')>Resolved</option>
            </select>

            <select name="city"
                    class="rounded-xl border-0 ring-1 ring-amber-900/10 bg-white/80 px-3 py-2.5 text-sm focus:ring-2 focus:ring-rose-400/60">
                <option value="">All cities</option>
                @foreach($cities ?? [] as $city)
                    <option value="{{ $city }}" @selected(request('city')===$city)>{{ $city }}</option>
                @endforeach
            </select>

            <input type="date" name="from" value="{{ request('from') }}"
                   class="rounded-xl border-0 ring-1 ring-amber-900/10 bg-white/80 px-3 py-2.5 text-sm focus:ring-2 focus:ring-rose-400/60">
            <input type="date" name="to" value="{{ request('to') }}"
                   class="rounded-xl border-0 ring-1 ring-amber-900/10 bg-white/80 px-3 py-2.5 text-sm focus:ring-2 focus:ring-rose-400/60">

            <button class="md:col-span-1 inline-flex justify-center rounded-xl px-4 py-2.5 text-white font-semibold
                           bg-gradient-to-r from-amber-600 to-rose-600 shadow hover:shadow-lg hover:-translate-y-0.5 transition">
                Apply
            </button>
        </form>
    </div>

    {{-- Table --}}
    <div class="rounded-2xl bg-white/85 backdrop-blur ring-1 ring-amber-900/10 shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-amber-100 text-amber-900 uppercase text-xs tracking-wider">
                        <th class="px-4 py-3 text-left">Title</th>
                        <th class="px-4 py-3 text-left">User</th>
                        <th class="px-4 py-3 text-left">City</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Created</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber-900/10">
                    @forelse($reports as $report)
                        <tr class="hover:bg-amber-50/50">
                            <td class="px-4 py-3 font-medium">
                                <a href="{{ route('admin.reports.show', $report) }}" class="text-rose-700 hover:underline">
                                    {{ $report->title }}
                                </a>
                            </td>
                            <td class="px-4 py-3">{{ $report->user->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ $report->city_corporation }}</td>
                            <td class="px-4 py-3">
                                @php $resolved = $report->status === 'resolved'; @endphp
                                <span class="px-2 py-1 text-xs rounded-full font-semibold
                                    {{ $resolved ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800' }}">
                                    {{ ucfirst($report->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $report->created_at->format('M d, Y h:i a') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.reports.show', $report) }}"
                                   class="inline-flex items-center gap-2 rounded-xl px-3 py-1.5 text-sm text-white
                                          bg-gradient-to-r from-amber-600 to-rose-600 shadow hover:shadow-lg transition">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-amber-900/70">No reports found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if(method_exists($reports, 'links'))
            <div class="px-4 py-3 border-t border-amber-900/10">
                {{ $reports->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
