@extends('layouts.admin')

@section('title', 'All Users')

@section('content')
<div class="relative">
    <div class="absolute -top-24 -left-24 w-96 h-96 bg-gradient-to-br from-amber-200 via-indigo-200 to-rose-200 opacity-20 rounded-full blur-3xl z-0"></div>

    <div class="bg-white rounded-2xl shadow-2xl p-8 relative z-10">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-extrabold text-indigo-800">All Users</h1>
                <p class="text-slate-500 text-sm mt-1">Manage and review all registered users.</p>
            </div>

            <form method="GET" class="flex items-center gap-2">
                <input type="text" name="q" value="{{ $search }}" placeholder="Search name, email, ID"
                       class="border rounded-lg p-2 w-64 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button class="px-4 py-2 rounded-lg text-white btn-primary">Search</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-indigo-50 text-indigo-900 uppercase text-xs tracking-wider">
                        <th class="px-4 py-3 text-left">ID</th>
                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="px-4 py-3 text-left">Email</th>
                        <th class="px-4 py-3 text-left">Role</th>
                        <th class="px-4 py-3 text-left">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3 font-semibold text-slate-700">#{{ $u->id }}</td>
                            <td class="px-4 py-3">{{ $u->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $u->email }}</td>
                            <td class="px-4 py-3">
                                @if($u->is_admin)
                                    <span class="px-2 py-1 text-xs rounded-full bg-indigo-100 text-indigo-800 font-semibold">Admin</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-emerald-100 text-emerald-800 font-semibold">User</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $u->created_at?->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-6 text-center text-slate-500" colspan="5">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
