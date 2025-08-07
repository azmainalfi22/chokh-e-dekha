@extends('layouts.admin')

@section('content')
<div class="text-2xl font-bold text-blue-600 mb-4">Welcome, Admin 👋</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-blue-500 text-white p-6 rounded-lg shadow text-center">
        <div class="text-4xl font-bold">{{ $totalReports }}</div>
        <div>Total Reports</div>
    </div>
    <div class="bg-yellow-400 text-white p-6 rounded-lg shadow text-center">
        <div class="text-4xl font-bold">{{ $pendingReports }}</div>
        <div>Pending</div>
    </div>
    <div class="bg-green-500 text-white p-6 rounded-lg shadow text-center">
        <div class="text-4xl font-bold">{{ $resolvedReports }}</div>
        <div>Resolved</div>
    </div>
    <div class="bg-gray-800 text-white p-6 rounded-lg shadow text-center">
        <div class="text-4xl font-bold">{{ $totalUsers }}</div>
        <div>Users</div>
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow mb-6">
    <h2 class="text-xl font-semibold mb-4">📍 Reports by City</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($reportsByCity as $item)
            <div class="border rounded p-4 text-center text-blue-700 bg-blue-50 font-semibold">
                <div>{{ $item->city_corporation }}</div>
                <div class="text-2xl">{{ $item->count }}</div>
            </div>
        @endforeach
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow">
    <h2 class="text-xl font-semibold mb-4">🆕 Recent Reports</h2>
    <table class="table-auto w-full text-sm">
        <thead>
            <tr class="bg-blue-100 text-left">
                <th class="p-2">Title</th>
                <th class="p-2">User</th>
                <th class="p-2">City</th>
                <th class="p-2">Status</th>
                <th class="p-2">Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($recentReports as $report)
                <tr class="border-b">
                    <td class="p-2">{{ $report->title }}</td>
                    <td class="p-2">{{ $report->user->name ?? 'N/A' }}</td>
                    <td class="p-2">{{ $report->city_corporation }}</td>
                    <td class="p-2">
                        <span class="px-2 py-1 text-xs rounded {{ $report->status === 'resolved' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ ucfirst($report->status) }}
                        </span>
                    </td>
                    <td class="p-2">{{ $report->created_at->format('M d, Y h:i a') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
