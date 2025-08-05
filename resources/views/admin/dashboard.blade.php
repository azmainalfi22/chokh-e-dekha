@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="mb-4 text-center text-primary fw-bold">📊 Admin Dashboard</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary shadow h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <h5 class="card-title">Total Reports</h5>
                    <h3 class="fw-bold">{{ $totalReports }}</h3>
                    <i class="bi bi-clipboard-data-fill fs-2"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-warning shadow h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <h5 class="card-title">Pending Reports</h5>
                    <h3 class="fw-bold">{{ $pendingReports }}</h3>
                    <i class="bi bi-hourglass-split fs-2"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-success shadow h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <h5 class="card-title">Resolved Reports</h5>
                    <h3 class="fw-bold">{{ $resolvedReports }}</h3>
                    <i class="bi bi-check-circle-fill fs-2"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-white bg-dark shadow h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <h5 class="card-title">Total Users</h5>
                    <h3 class="fw-bold">{{ $totalUsers }}</h3>
                    <i class="bi bi-people-fill fs-2"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="text-end mt-5">
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-primary">🔍 View All Reports</a>
    </div>
</div>
@endsection
