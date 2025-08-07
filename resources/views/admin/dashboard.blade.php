@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="mb-4 text-center fw-bold text-primary">🛠️ Admin Dashboard</h2>

    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card text-white bg-primary shadow h-100" style="border-radius:1rem;">
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <i class="bi bi-bar-chart-fill display-4 mb-2"></i>
                    <div class="fw-bold fs-3">{{ $totalReports }}</div>
                    <div>Total Reports</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning shadow h-100" style="border-radius:1rem;">
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <i class="bi bi-hourglass-split display-4 mb-2"></i>
                    <div class="fw-bold fs-3">{{ $pendingReports }}</div>
                    <div>Pending Reports</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success shadow h-100" style="border-radius:1rem;">
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <i class="bi bi-check-circle-fill display-4 mb-2"></i>
                    <div class="fw-bold fs-3">{{ $resolvedReports }}</div>
                    <div>Resolved Reports</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-dark shadow h-100" style="border-radius:1rem;">
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <i class="bi bi-people-fill display-4 mb-2"></i>
                    <div class="fw-bold fs-3">{{ $totalUsers }}</div>
                    <div>Total Users</div>
                </div>
            </div>
        </div>
    </div>

    <h4 class="text-primary fw-bold mb-3">📊 Reports by City Corporation</h4>
    <div class="row mb-5">
        @forelse($reportsByCity as $cityStat)
            <div class="col-md-3 mb-3">
                <div class="card border-info h-100">
                    <div class="card-body text-center">
                        <span class="fw-bold">{{ $cityStat->city_corporation }}</span>
                        <br>
                        <span class="badge bg-info text-dark fs-5">{{ $cityStat->count }}</span>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted">No city corporation data available.</p>
        @endforelse
    </div>

    <h4 class="text-primary fw-bold mb-3">🆕 Recent Reports</h4>
    <div class="table-responsive">
        <table class="table table-hover shadow-sm">
            <thead class="table-primary">
                <tr>
                    <th>Title</th>
                    <th>User</th>
                    <th>City</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentReports as $report)
                <tr>
                    <td>{{ $report->title }}</td>
                    <td>{{ $report->user->name ?? 'Unknown' }}</td>
                    <td>{{ $report->city_corporation }}</td>
                    <td>
                        <span class="badge {{ $report->status == 'resolved' ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ ucfirst($report->status) }}
                        </span>
                    </td>
                    <td>{{ $report->created_at->format('M d, Y h:i a') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No recent reports.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="text-end mt-4">
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-primary btn-lg">
            <i class="bi bi-card-list"></i> View All Reports
        </a>
    </div>
</div>
@endsection

@push('styles')
<!-- Bootstrap Icons CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
@endpush
