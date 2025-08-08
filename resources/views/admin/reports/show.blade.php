@extends('layouts.admin')
@section('title', 'Report Details')

@section('content')
<div class="relative space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold bg-clip-text text-transparent
                       bg-gradient-to-r from-amber-700 via-orange-700 to-rose-700">
                {{ $report->title }}
            </h1>
            <p class="text-sm text-amber-900/70">
                Submitted by <span class="font-medium">{{ $report->user->name ?? 'N/A' }}</span>
                • {{ $report->created_at->format('M d, Y h:i a') }}
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.reports.index') }}"
               class="inline-flex items-center gap-2 rounded-xl px-3 py-2 bg-white ring-1 ring-amber-900/10
                      text-amber-900/90 shadow hover:shadow-md transition">
                Back
            </a>

            {{-- Toggle status --}}
            @php $isResolved = $report->status === 'resolved'; @endphp
            <form method="POST" action="{{ route('admin.reports.update', $report) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="{{ $isResolved ? 'pending' : 'resolved' }}">
                <button class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-white font-semibold
                               bg-gradient-to-r from-amber-600 to-rose-600 shadow hover:shadow-lg transition">
                    {{ $isResolved ? 'Mark Pending' : 'Mark Resolved' }}
                </button>
            </form>
        </div>
    </div>

    {{-- Meta + status --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 rounded-2xl bg-white/85 backdrop-blur ring-1 ring-amber-900/10 shadow p-6">
            <h2 class="text-lg font-semibold text-amber-900 mb-3">Details</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div>
                    <dt class="text-amber-900/70">City Corporation</dt>
                    <dd class="font-medium text-amber-900">{{ $report->city_corporation }}</dd>
                </div>
                <div>
                    <dt class="text-amber-900/70">Status</dt>
                    <dd>
                        <span class="px-2 py-1 text-xs rounded-full font-semibold
                            {{ $isResolved ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800' }}">
                            {{ ucfirst($report->status) }}
                        </span>
                    </dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-amber-900/70">Description</dt>
                    <dd class="mt-1 whitespace-pre-line text-amber-900/90">{{ $report->description }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-2xl bg-white/85 backdrop-blur ring-1 ring-amber-900/10 shadow p-6">
            <h2 class="text-lg font-semibold text-amber-900 mb-3">Attachments</h2>
            @php $files = $report->attachments ?? []; @endphp
            @if(!empty($files))
                <ul class="space-y-2 text-sm">
                    @foreach($files as $file)
                        <li class="flex items-center justify-between gap-3 rounded-xl bg-amber-50 px-3 py-2">
                            <span class="truncate">{{ basename($file) }}</span>
                            <a href="{{ Storage::url($file) }}" target="_blank"
                               class="text-rose-700 hover:underline">View</a>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-amber-900/70">No attachments.</p>
            @endif
        </div>
    </div>

    {{-- Timeline / comments (optional placeholder) --}}
    @if(isset($comments))
    <section class="rounded-2xl bg-white/85 backdrop-blur ring-1 ring-amber-900/10 shadow p-6">
        <h2 class="text-lg font-semibold text-amber-900 mb-3">Admin Notes</h2>
        @forelse($comments as $note)
            <div class="rounded-xl bg-amber-50 p-3 mb-2">
                <div class="text-xs text-amber-900/70">{{ $note->user->name ?? 'Admin' }} • {{ $note->created_at->diffForHumans() }}</div>
                <div class="text-sm text-amber-900/90">{{ $note->body }}</div>
            </div>
        @empty
            <p class="text-sm text-amber-900/70">No notes yet.</p>
        @endforelse
    </section>
    @endif
</div>
@endsection
