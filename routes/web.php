<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// USER controllers (non-admin)
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;

// ADMIN controllers
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AdminUserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing page: send to correct dashboard or login
Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    return Auth::user()->is_admin
        ? redirect()->route('admin.dashboard')
        : redirect()->route('dashboard');
})->name('home');

// ========================
// User (non-admin) routes
// ========================
Route::middleware(['auth', 'verified'])->group(function () {
    // User dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/my-reports', [ReportController::class, 'myReports'])->name('reports.my');
    Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');

    // Only non-admins can create reports
    Route::middleware('prevent-admin-report-create')->group(function () {
        Route::get('/report/create', [ReportController::class, 'create'])->name('report.create');
        Route::post('/report', [ReportController::class, 'store'])->name('report.store');
    });

    // User profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ========================
// Admin routes
// ========================
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'is_admin'])
    ->group(function () {
        // Admin dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Admin profile
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [AdminProfileController::class, 'update'])->name('profile.update');

        // Admin users list
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');

        // Admin reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/{report}', [ReportController::class, 'adminShow'])->name('reports.show');
        Route::put('/reports/{report}', [ReportController::class, 'update'])->name('reports.update');
        Route::get('/reports/{report}/toggle-status', [ReportController::class, 'toggleStatus'])->name('reports.toggle');
    });

// Breeze / auth scaffolding
require __DIR__.'/auth.php';
