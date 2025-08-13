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
use App\Http\Controllers\Admin\ReportController as AdminReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (! Auth::check()) return redirect()->route('login');

    return Auth::user()->is_admin
        ? redirect()->route('admin.dashboard')
        : redirect()->route('dashboard');
})->name('home');

/* User (non-admin) */
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/my-reports', [ReportController::class, 'myReports'])->name('reports.my');
    Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');

    Route::middleware('prevent-admin-report-create')->group(function () {
        Route::get('/report/create', [ReportController::class, 'create'])->name('report.create');
        Route::post('/report', [ReportController::class, 'store'])->name('report.store');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/* Admin */
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'is_admin'])
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [AdminProfileController::class, 'update'])->name('profile.update');

        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');

        // Reports
        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index'); // if you have index view
        Route::get('/reports/{report}', [AdminReportController::class, 'show'])->name('reports.show');

        // Status (PUT) — fixes method not allowed
        Route::put('/reports/{report}/status', [AdminReportController::class, 'updateStatus'])
            ->name('reports.status');

        // Notes
        Route::post('/reports/{report}/notes', [AdminReportController::class, 'storeNote'])
            ->name('reports.notes.store');
        Route::delete('/reports/{report}/notes/{note}', [AdminReportController::class, 'destroyNote'])
            ->name('reports.notes.destroy');
    });

require __DIR__ . '/auth.php';
