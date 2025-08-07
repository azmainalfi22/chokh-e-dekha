@extends('layouts.admin')

@section('content')
<style>
    body {
        background: linear-gradient(to bottom right, #f3f4f6, #e0e7ff);
        background-attachment: fixed;
    }

    .floating-bg {
        position: absolute;
        top: -50px;
        right: -50px;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle at center, #c7d2fe, #6366f1);
        border-radius: 50%;
        z-index: -1;
        animation: float 8s ease-in-out infinite;
        opacity: 0.1;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(20px); }
    }

    .stat-count {
        transition: transform 0.3s ease;
    }

    .stat-count:hover {
        transform: scale(1.05);
    }
</style>

<div class="floating-bg"></div>

<header class="mb-10">
    <div class="text-4xl font-bold text-indigo-800 mb-2">👑 Welcome, Admin</div>
    <div class="text-gray-600 text-md">Here’s what’s happening at a glance.</div>
</header>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
    <div class="bg-gradient-to-br from-blue-700 to-indigo-900 text-white p-6 rounded-xl shadow-xl text-center stat-count">
        <div class="text-5xl font-extrabold counter" data-target="{{ $totalReports }}">0</div>
        <div class="text-lg mt-2">Total Reports</div>
    </div>
    <div class="bg-gradient-to-br from-yellow-500 to-yellow-700 text-white p-6 rounded-xl shadow-xl text-center stat-count">
        <div class="text-5xl font-extrabold counter" data-target="{{ $pendingReports }}">0</div>
        <div class="text-lg mt-2">Pending</div>
    </div>
    <div class="bg-gradient-to-br from-green-500 to-emerald-700 text-white p-6 rounded-xl shadow-xl text-center stat-count">
        <div class="text-5xl font-extrabold counter" data-target="{{ $resolvedReports }}">0</div>
        <div class="text-lg mt-2">Resolved</div>
    </div>
    <div class="bg-gradient-to-br from-purple-700 to-pink-800 text-white p-6 rounded-xl shadow-xl text-center stat-count">
        <div class="text-5xl font-extrabold counter" data-target="{{ $totalUsers }}">0</div>
        <div class="text-lg mt-2">Users</div>
    </div>
</div>

<section class="bg-white rounded-xl shadow-xl p-6 mb-10">
    <h2 class="text-2xl font-semibold text-indigo-700 mb-4">📍 Reports by City</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($reportsByCity as $item)
            <div class="bg-indigo-100 text-indigo-900 p-4 rounded-lg shadow-md text-center hover:bg-indigo-200 transition">
                <div class="text-base font-semibold">{{ $item->city_corporation }}</div>
                <div class="text-3xl font-bold">{{ $item->count }}</div>
            </div>
        @endforeach
    </div>
</section>

<section class="bg-white rounded-xl shadow-xl p-6 mb-16">
    <h2 class="text-2xl font-semibold text-indigo-700 mb-4">🆕 Recent Reports</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-indigo-100 text-indigo-800 uppercase text-xs tracking-wider">
                    <th class="px-4 py-2 text-left">Title</th>
                    <th class="px-4 py-2 text-left">User</th>
                    <th class="px-4 py-2 text-left">City</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-left">Date</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                @foreach ($recentReports as $report)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="px-4 py-2 font-medium">{{ $report->title }}</td>
                        <td class="px-4 py-2">{{ $report->user->name ?? 'N/A' }}</td>
                        <td class="px-4 py-2">{{ $report->city_corporation }}</td>
                        <td class="px-4 py-2">
                            <span class="px-2 py-1 text-xs rounded-full font-semibold {{ $report->status === 'resolved' ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800' }}">
                                {{ ucfirst($report->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-2">{{ $report->created_at->format('M d, Y h:i a') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<footer class="text-center text-sm text-gray-500 mt-10 pb-4">
    © {{ now()->year }} Chokh-e-Dekha Admin Dashboard. All rights reserved.
</footer>

<script>
    // Animate stat counters
    document.addEventListener('DOMContentLoaded', () => {
        const counters = document.querySelectorAll('.counter');
        counters.forEach(counter => {
            const updateCount = () => {
                const target = +counter.getAttribute('data-target');
                const count = +counter.innerText;
                const increment = Math.ceil(target / 80);

                if (count < target) {
                    counter.innerText = count + increment;
                    setTimeout(updateCount, 15);
                } else {
                    counter.innerText = target;
                }
            };
            updateCount();
        });
    });
</script>
@endsection
