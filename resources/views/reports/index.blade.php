@extends('layouts.app')

@section('content')
<div class="container">
    <h2>All Submitted Issues</h2>

    @if(session('success'))
        <p style="color:green;">{{ session('success') }}</p>
    @endif

    <a href="/report/create">+ Submit New Report</a><br><br>

    @if(Auth::check())
        <form method="POST" action="/logout" style="margin-bottom: 20px;">
            @csrf
            <button type="submit">Logout</button>
        </form>
    @endif

    @if($reports->isEmpty())
        <p>No reports submitted yet.</p>
    @else
        @foreach($reports as $report)
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">{{ $report->title }} ({{ $report->category }})</h5>
                    <p><strong>Description:</strong> {{ $report->description }}</p>
                    @if ($report->photo)
                        <img src="{{ asset("storage/{$report->photo}") }}" width="200"><br>
                    @endif
                    <p><strong>Location:</strong> {{ $report->location ?? 'Not specified' }}</p>
                    <p><strong>City Corporation:</strong> {{ $report->city_corporation }}</p>
                    <p><strong>Status:</strong> {{ ucfirst($report->status) }}</p>
                    <p><small>Submitted on {{ $report->created_at->format('F j, Y, g:i a') }}</small></p>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
