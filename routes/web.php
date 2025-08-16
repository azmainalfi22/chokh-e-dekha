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
use App\Http\Controllers\Admin\ReportMapController;

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

/* ---------------------------
 | User (non-admin)
 * --------------------------- */
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Reports (list, mine, show)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/my-reports', [ReportController::class, 'myReports'])->name('reports.my');
    Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');

    // Create/store (prevent admins from posting)
    Route::middleware('prevent-admin-report-create')->group(function () {
        // Preferred RESTful names
        Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
        Route::post('/reports',        [ReportController::class, 'store'])->name('reports.store');

        // Backwards-compat aliases (can be removed after views updated)
        Route::get('/report/create', [ReportController::class, 'create'])->name('report.create');
        Route::post('/report',       [ReportController::class, 'store'])->name('report.store');
    });

    // Profile
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',[ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');
});

/* ---------------------------
 | Admin
 * --------------------------- */
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'is_admin'])
    ->group(function () {
        // Dashboard & profile
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile',   [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [AdminProfileController::class, 'update'])->name('profile.update');

        // Users
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');

        // Reports (admin views)
        Route::get('/reports',           [AdminReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/{report}',  [AdminReportController::class, 'show'])->name('reports.show');

        // Status update (PUT/PATCH)
        Route::match(['put','patch'], '/reports/{report}/status', [AdminReportController::class, 'updateStatus'])
            ->name('reports.status');

        // Notes
        Route::post('/reports/{report}/notes',            [AdminReportController::class, 'storeNote'])->name('reports.notes.store');
        Route::delete('/reports/{report}/notes/{note}',   [AdminReportController::class, 'destroyNote'])->name('reports.notes.destroy');

        // LIVE map data (JSON for dashboard map)
        Route::get('/reports/map', [ReportMapController::class, 'index'])->name('reports.map');
    });

require __DIR__ . '/auth.php';
