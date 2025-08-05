@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="text-primary">📂 My Submitted Reports</h2>

    @if(session('success'))
        <div class="alert alert-success">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if($reports->isEmpty())
        <div class="alert alert-info">
            You haven't submitted any reports yet.
        </div>
    @else
        <div class="row row-cols-1 row-cols-md-2 g-4">
            @foreach($reports as $report)
                <div class="col">
                    <div class="card shadow-sm h-100 border-primary">
                        <div class="card-body">
                            <h5 class="card-title text-primary">
                                📝 {{ $report->title }}
                                <span class="badge bg-secondary">{{ $report->category }}</span>
                            </h5>
                            <p class="card-text"><strong>🧾 Description:</strong> {{ $report->description }}</p>

                            @if ($report->photo)
                                <img src="{{ asset("storage/{$report->photo}") }}" class="img-fluid rounded mb-2">
                            @endif

                            <ul class="list-unstyled mb-3">
                                <li><strong>📍 Location:</strong> {{ $report->location ?? 'Not specified' }}</li>
                                <li><strong>🏙️ City:</strong> {{ $report->city_corporation }}</li>
                                <li><strong>📌 Status:</strong>
                                    <span class="badge {{ $report->status === 'resolved' ? 'bg-success' : 'bg-warning text-dark' }}">
                                        {{ ucfirst($report->status) }}
                                    </span>
                                </li>
                                <li><small>📅 Submitted on {{ $report->created_at->format('F j, Y, g:i a') }}</small></li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
