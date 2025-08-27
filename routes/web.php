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

// Engagement controllers
use App\Http\Controllers\CommentController;
use App\Http\Controllers\EndorsementController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    return Auth::user()->is_admin
        ? redirect()->route('admin.dashboard')
        : redirect()->route('dashboard');
})->name('home');

/* ---------------------------
 | Public browsing (no auth)
 * --------------------------- */
// Reports list (public)
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

// Map markers JSON (public; same filters as index)
Route::get('/reports/map.json', [ReportController::class, 'mapData'])->name('reports.map.json');

/* ---------------------------
 | Authenticated user (non-admin flows)
 * --------------------------- */
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Show a single report
    Route::get('/reports/{report}', [ReportController::class, 'show'])
        ->whereNumber('report')
        ->name('reports.show');

    // My reports
    Route::get('/my-reports', [ReportController::class, 'myReports'])->name('reports.my');

    // Create/store (prevent admins from posting)
    Route::middleware('prevent-admin-report-create')->group(function () {
        Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
        Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');

        // Back-compat aliases (optional; remove when views updated)
        Route::get('/report/create', [ReportController::class, 'create'])->name('report.create');
        Route::post('/report', [ReportController::class, 'store'])->name('report.store');
    });

    // User profile (needed by layouts.app link)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/* ---------------------------
 | Engagement (auth only, NO email verification)
 * --------------------------- */
Route::middleware('auth')->group(function () {
    // Comments
    Route::post('/reports/{report}/comments', [CommentController::class, 'store'])
        ->whereNumber('report')
        ->middleware('throttle:20,1')
        ->name('reports.comments.store');

    Route::delete('/reports/{report}/comments/{comment}', [CommentController::class, 'destroy'])
        ->whereNumber('report')
        ->whereNumber('comment')
        ->middleware('throttle:60,1')
        ->name('reports.comments.destroy');

    // Endorse (like)
    Route::post('/reports/{report}/endorse', [EndorsementController::class, 'toggle'])
        ->whereNumber('report')
        ->middleware('throttle:60,1')
        ->name('reports.endorse');

    // Back-compat alias
    Route::post('/reports/{report}/endorse-toggle', [EndorsementController::class, 'toggle'])
        ->whereNumber('report')
        ->middleware('throttle:60,1')
        ->name('reports.endorse.toggle');
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
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [AdminProfileController::class, 'update'])->name('profile.update');

        // Users
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');

        // Reports (admin views)
        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/{report}', [AdminReportController::class, 'show'])
            ->whereNumber('report')
            ->name('reports.show');

        // Status update (PUT/PATCH)
        Route::match(['put','patch'], '/reports/{report}/status', [AdminReportController::class, 'updateStatus'])
            ->whereNumber('report')
            ->name('reports.status');

        // Matches Blade JS: POST /admin/reports/{id}/quick-action
        Route::post('/reports/{report}/quick-action', [AdminReportController::class, 'quickAction'])
            ->whereNumber('report')
            ->name('reports.quick-action');

        // Assign & bulk
        Route::post('/reports/{report}/assign', [AdminReportController::class, 'assignToMe'])
            ->whereNumber('report')
            ->name('reports.assign');

        Route::post('/reports/bulk-action', [AdminReportController::class, 'bulkAction'])
            ->name('reports.bulk');

        // Notes
        Route::post('/reports/{report}/notes', [AdminReportController::class, 'storeNote'])
            ->whereNumber('report')
            ->name('reports.notes.store');
        Route::delete('/reports/{report}/notes/{note}', [AdminReportController::class, 'destroyNote'])
            ->whereNumber('report')
            ->whereNumber('note')
            ->name('reports.notes.destroy');

        // LIVE map data (JSON for dashboard map)
        Route::get('/reports/map', [ReportMapController::class, 'index'])->name('reports.map');
    });

require __DIR__.'/auth.php';
