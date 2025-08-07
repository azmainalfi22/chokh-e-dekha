@extends('layouts.app')

@section('content')
@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

<style>
    body {
        background: linear-gradient(to right, #fdfcfb, #e2d1c3); /* Royal cream gradient */
    }
    .royal-card {
        background-color: #fff8f0;
        border-radius: 1rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25); /* Darker shadow */
        transition: transform 0.2s ease-in-out;
    }
    .royal-card:hover {
        transform: translateY(-3px);
    }
    .royal-icon {
        font-size: 3rem;
    }
</style>

<div class="container py-5 position-relative">
    <!-- 🌤️ Floating Illustration -->
    <img src="https://www.transparenttextures.com/patterns/gplay.png" class="position-absolute top-0 start-0 opacity-10 w-100 h-100" style="z-index:-1; object-fit:cover;">

    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card royal-card mb-4 text-center">
                <div class="card-body">
                    <h2 class="text-warning fw-bold mb-2">👑 Welcome, {{ Auth::user()->name }}!</h2>
                    <p class="lead mb-4">This is your command center for civic impact.</p>

                    <div class="row g-4 justify-content-center">
                        <div class="col-md-4">
                            <a href="{{ route('report.create') }}" class="text-decoration-none">
                                <div class="card royal-card h-100">
                                    <div class="card-body text-center">
                                        <i class="bi bi-plus-circle royal-icon text-success mb-2"></i>
                                        <h5 class="fw-bold">Submit Report</h5>
                                        <p class="text-muted">Let the city know.</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('reports.my') }}" class="text-decoration-none">
                                <div class="card royal-card h-100">
                                    <div class="card-body text-center">
                                        <i class="bi bi-folder2-open royal-icon text-primary mb-2"></i>
                                        <h5 class="fw-bold">My Reports</h5>
                                        <p class="text-muted">Track your submissions.</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('profile.edit') }}" class="text-decoration-none">
                                <div class="card royal-card h-100">
                                    <div class="card-body text-center">
                                        <i class="bi bi-person-lines-fill royal-icon text-dark mb-2"></i>
                                        <h5 class="fw-bold">Edit Profile</h5>
                                        <p class="text-muted">Manage your identity.</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            <!-- 🔢 Stats Section -->
            <div class="row text-center mb-4">
                <div class="col-md-4">
                    <div class="card royal-card">
                        <div class="card-body">
                            <h3 class="text-primary">📝 {{ \App\Models\Report::where('user_id', Auth::id())->count() }}</h3>
                            <p class="text-muted">Total Reports</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card royal-card">
                        <div class="card-body">
                            <h3 class="text-warning">⏳ {{ \App\Models\Report::where('user_id', Auth::id())->where('status', 'pending')->count() }}</h3>
                            <p class="text-muted">Pending</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card royal-card">
                        <div class="card-body">
                            <h3 class="text-success">✅ {{ \App\Models\Report::where('user_id', Auth::id())->where('status', 'resolved')->count() }}</h3>
                            <p class="text-muted">Resolved</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 📋 View All Link -->
            <div class="text-center mt-4">
                <a href="{{ route('reports.index') }}" class="btn btn-outline-dark btn-lg shadow-sm">
                    <i class="bi bi-card-list"></i> View All Submitted Issues
                </a>
            </div>

        </div>
    </div>
</div>

<!-- 🌙 Footer -->
<footer class="text-center mt-5 py-3" style="background-color: #fff3e0; border-top: 1px solid #e3caa5;">
    <p class="mb-0 text-muted">&copy; {{ date('Y') }} Chokh-e-Dekha. Made with ❤️ for civic good.</p>
</footer>
@endsection

@push('styles')
<!-- Bootstrap Icons CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
@endpush
