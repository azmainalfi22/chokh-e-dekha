@extends('layouts.app')

@section('content')
<div class="container">
    <h2>All Submitted Issues</h2>

    @if(session('success'))
        <p style="color:green;">{{ session('success') }}</p>
    @endif

    <a href="/report/create">+ Submit New Report</a><br><br>

    {{-- City Corporation Filter --}}
    <form method="GET" action="{{ route('home') }}" class="mb-4">
        <label for="city_corporation"><strong>Filter by City Corporation:</strong></label>
        <select name="city_corporation" id="city_corporation" onchange="this.form.submit()">
            <option value="">-- All --</option>
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
    </form>

    @if(request('city_corporation'))
        <p>Showing reports for: <strong>{{ request('city_corporation') }}</strong></p>
    @endif

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
