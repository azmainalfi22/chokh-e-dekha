@extends('layouts.app')

@section('content')
<style>
    body {
        background: linear-gradient(135deg, #f0f8ff, #e6f0fa);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .report-card {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .report-card h2 {
        color: #0d6efd;
        margin-bottom: 25px;
    }

    label {
        font-weight: 600;
        color: #333;
    }

    .form-control:focus, .form-select:focus {
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        border-color: #0d6efd;
    }

    .btn-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #084298;
        border-color: #084298;
    }

    .btn-link {
        color: #6c757d;
    }

    .alert-danger {
        border-left: 6px solid #dc3545;
        background-color: #f8d7da;
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="report-card">
                <h2>📢 Submit a City Issue Report</h2>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>Whoops! Something went wrong.</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>⚠️ {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('report.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="title" class="form-label">📝 Title</label>
                        <input name="title" type="text" class="form-control" required placeholder="Enter issue title...">
                    </div>

                    <div class="mb-3">
                        <label for="city_corporation" class="form-label">🏙️ City Corporation</label>
                        <select name="city_corporation" class="form-select" required>
                            <option value="">Select a city</option>
                            @foreach([
                                'Dhaka North', 'Dhaka South', 'Chittagong', 'Rajshahi',
                                'Khulna', 'Sylhet', 'Barisal', 'Rangpur',
                                'Mymensingh', 'Narayanganj', 'Comilla', 'Bogura'
                            ] as $city)
                                <option value="{{ $city }} City Corporation">{{ $city }} City Corporation</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">🧾 Description</label>
                        <textarea name="description" class="form-control" rows="3" required placeholder="Describe the issue..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label">📂 Category</label>
                        <select name="category" class="form-select" required>
                            <option>Garbage</option>
                            <option>Broken Road</option>
                            <option>Drainage</option>
                            <option>Electricity</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="location" class="form-label">📍 Location (optional)</label>
                        <input name="location" type="text" class="form-control" placeholder="E.g., Road 27, Mirpur-2">
                    </div>

                    <div class="mb-4">
                        <label for="photo" class="form-label">📸 Upload Photo (optional)</label>
                        <input name="photo" type="file" class="form-control">
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('reports.index') }}" class="btn btn-link">← Back to All Reports</a>
                        <button type="submit" class="btn btn-primary px-4">🚀 Submit Report</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
