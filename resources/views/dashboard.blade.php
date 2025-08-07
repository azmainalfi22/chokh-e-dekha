@extends('layouts.app')

@section('content')
@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow-lg border-0 mb-4" style="border-radius: 1.5rem;">
                <div class="card-body text-center">
                    <h2 class="text-primary fw-bold mb-2">
                        👋 Welcome, {{ Auth::user()->name }}!
                    </h2>
                    <p class="lead mb-4">
                        This is your personal dashboard. Track your reports and take quick action!
                    </p>

                    <div class="row g-4 justify-content-center">
                        <div class="col-md-4">
                            <a href="{{ route('report.create') }}" class="text-decoration-none">
                                <div class="card h-100 border-0 shadow-sm hover-shadow" style="border-radius:1rem;">
                                    <div class="card-body text-center">
                                        <i class="bi bi-plus-circle display-4 text-success mb-2"></i>
                                        <h5 class="fw-bold mb-1">Submit New Report</h5>
                                        <p class="mb-0 text-muted">Report an issue in your city.</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('reports.my') }}" class="text-decoration-none">
                                <div class="card h-100 border-0 shadow-sm hover-shadow" style="border-radius:1rem;">
                                    <div class="card-body text-center">
                                        <i class="bi bi-folder2-open display-4 text-primary mb-2"></i>
                                        <h5 class="fw-bold mb-1">My Submitted Reports</h5>
                                        <p class="mb-0 text-muted">View or manage your reports.</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('profile.edit') }}" class="text-decoration-none">
                                <div class="card h-100 border-0 shadow-sm hover-shadow" style="border-radius:1rem;">
                                    <div class="card-body text-center">
                                        <i class="bi bi-person-lines-fill display-4 text-secondary mb-2"></i>
                                        <h5 class="fw-bold mb-1">My Profile</h5>
                                        <p class="mb-0 text-muted">Edit your profile details.</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            <div class="text-center mt-4">
                <a href="{{ route('reports.index') }}" class="btn btn-outline-primary btn-lg shadow-sm">
                    <i class="bi bi-card-list"></i> View All Submitted Issues
                </a>
            </div>

        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- Bootstrap Icons CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
@endpush
