@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary">📋 All Submitted City Issue Reports</h2>
        <a href="{{ route('report.create') }}" class="btn btn-success">+ Submit New Report</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            ✅ {{ session('success') }}
        </div>
    @endif

    {{-- 🔍 City Corporation Filter --}}
<form method="GET" action="{{ route('reports.index') }}" class="row g-3 mb-4">
        <div class="col-md-6">
            <label for="city_corporation" class="form-label">🏙️ Filter by City Corporation:</label>
            <select name="city_corporation" id="city_corporation" class="form-select" onchange="this.form.submit()">
                <option value="">-- Show All --</option>
                @php
                    $cities = [
                        'Dhaka North City Corporation',
                        'Dhaka South City Corporation',
                        'Chittagong City Corporation',
                        'Rajshahi City Corporation',
                        'Khulna City Corporation',
                        'Sylhet City Corporation',
                        'Barisal City Corporation',
                        'Rangpur City Corporation',
                        'Mymensingh City Corporation',
                        'Narayanganj City Corporation',
                        'Comilla City Corporation',
                        'Bogura City Corporation',
                    ];
                @endphp
                @foreach ($cities as $cityName)
                    <option value="{{ $cityName }}" {{ request('city_corporation') == $cityName ? 'selected' : '' }}>
                        {{ $cityName }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>

    @if(request('city_corporation'))
        <p class="mb-3">📍 Showing reports for: <strong>{{ request('city_corporation') }}</strong></p>
    @endif

    @if(Auth::check())
        <form method="POST" action="{{ route('logout') }}" class="mb-4">
            @csrf
            <button type="submit" class="btn btn-outline-danger">Logout</button>
        </form>
    @endif

    @if($reports->isEmpty())
        <div class="alert alert-info">
            No reports submitted yet.
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
                                <img src="{{ asset("storage/{$report->photo}") }}" class="img-fluid rounded mb-2" alt="Report Photo">
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
