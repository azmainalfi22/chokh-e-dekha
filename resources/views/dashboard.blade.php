@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center">
                    <h2>Welcome, {{ Auth::user()->name }}!</h2>
                </div>
                <div class="card-body text-center">
                    <p class="lead mb-4">Your dashboard is here. Explore your options below.</p>
                    <a href="{{ route('reports.index') }}" class="btn btn-outline-primary btn-lg mb-2">
                        <i class="fas fa-file-alt"></i> View Submitted Reports
                    </a>
                    <br>
                    <a href="/" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-home"></i> Go to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- Bootstrap 4/5 and FontAwesome CDN for icons (if not already included in your layout) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
@endpush
