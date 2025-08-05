@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">

            <!-- Welcome Card -->
            <div class="card bg-light shadow-lg mb-4 border-0 animate__animated animate__fadeInDown">
                <div class="card-body text-center">
                    <h2 class="card-title text-primary fw-bold">
                        <i class="fas fa-user-circle me-2"></i>Welcome back, {{ Auth::user()->name }}!
                    </h2>
                    <p class="lead text-secondary">Here’s your control panel. Let’s keep the city clean and efficient together!</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row text-center animate__animated animate__fadeInUp">
                <div class="col-md-4 mb-3">
                    <a href="{{ route('report.create') }}" class="btn btn-lg btn-success w-100">
                        <i class="fas fa-plus-circle me-2"></i>Submit New Report
                    </a>
                </div>
                <div class="col-md-4 mb-3">
                    <a href="{{ route('reports.my') }}" class="btn btn-lg btn-primary w-100">
                        <i class="fas fa-list-ul me-2"></i>My Submitted Reports
                    </a>
                </div>
                <div class="col-md-4 mb-3">
                    <a href="{{  route('reports.index') }}" class="btn btn-lg btn-secondary w-100">
                        <i class="fas fa-home me-2"></i>Go to Homepage
                    </a>
                </div>
            </div>

            <!-- Profile & Logout -->
            <div class="row mt-5 text-center">
                <div class="col-md-6 mb-2">
                    <a href="{{ route('profile.edit') }}" class="btn btn-outline-info w-100">
                        <i class="fas fa-user-edit me-2"></i>Edit Profile
                    </a>
                </div>
                <div class="col-md-6 mb-2">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- Bootstrap and FontAwesome -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
@endpush
