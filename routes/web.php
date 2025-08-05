<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ⛔ Guest users redirected to login from homepage
Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// ✅ Authenticated user routes
Route::middleware('auth')->group(function () {
    // 📌 Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/report/create', [ReportController::class, 'create'])->name('report.create');
    Route::post('/report', [ReportController::class, 'store'])->name('report.store');
    Route::get('/my-reports', [ReportController::class, 'myReports'])->name('reports.my');

    // 📌 User Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 📌 Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['verified'])->name('dashboard');
});

// 🔐 Admin-only routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard'); // ✅ NEW
    Route::get('/admin/reports', [ReportController::class, 'index'])->name('admin.reports.index');
    Route::put('/admin/reports/{report}', [ReportController::class, 'update'])->name('admin.reports.update');
    Route::get('/report/{id}/toggle-status', [ReportController::class, 'toggleStatus'])->name('report.toggleStatus');
});

// ✅ Breeze authentication routes
require __DIR__ . '/auth.php';
