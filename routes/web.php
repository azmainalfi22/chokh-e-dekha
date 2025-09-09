<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

// USER controllers (non-admin)
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\CommentController;

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

    // === ENGAGEMENT ROUTES ===
    // Likes
    Route::post('/reports/{report}/like', [LikeController::class, 'toggle'])
        ->whereNumber('report')
        ->middleware('throttle:60,1')
        ->name('reports.like');

    // Comments
    Route::get('/reports/{report}/comments', [CommentController::class, 'index'])
        ->whereNumber('report')
        ->name('reports.comments.index');
    
    Route::post('/reports/{report}/comments', [CommentController::class, 'store'])
        ->whereNumber('report')
        ->middleware('throttle:20,1')
        ->name('reports.comments.store');
    
    Route::delete('/reports/{report}/comments/{comment}', [CommentController::class, 'destroy'])
        ->whereNumber('report')
        ->whereNumber('comment')
        ->middleware('throttle:60,1')
        ->name('reports.comments.destroy');

    // User profile (needed by layouts.app link)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/* ---------------------------
 | Notifications (auth only; NO email verification needed)
 * --------------------------- */
Route::middleware('auth')->group(function () {
    // === BASIC NOTIFICATION ROUTES ===
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])
        ->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAll'])
        ->name('notifications.readAll');
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    // === ENHANCED ROUTES ===
    // Clear/Delete notifications
    Route::delete('/notifications/{id}/clear', [NotificationController::class, 'clear'])
        ->name('notifications.clear');
    Route::delete('/notifications/clear-all', [NotificationController::class, 'clearAll'])
        ->name('notifications.clearAll');

    // Bulk operations
    Route::post('/notifications/bulk-read', [NotificationController::class, 'bulkMarkRead'])
        ->name('notifications.bulkRead');
    Route::delete('/notifications/bulk-clear', [NotificationController::class, 'bulkClear'])
        ->name('notifications.bulkClear');

    // Statistics and analytics
    Route::get('/notifications/stats', [NotificationController::class, 'stats'])
        ->name('notifications.stats');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])
        ->name('notifications.unreadCount');

    // Real-time features
    Route::get('/notifications/poll', [NotificationController::class, 'poll'])
        ->name('notifications.poll');

    // Advanced features
    Route::post('/notifications/{id}/snooze', [NotificationController::class, 'snooze'])
        ->name('notifications.snooze');
    Route::delete('/notifications/archive-old', [NotificationController::class, 'archiveOld'])
        ->name('notifications.archiveOld');
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

        Route::post('/reports/{report}/approve', [AdminReportController::class, 'approve'])
            ->whereNumber('report')
            ->name('reports.approve');

        Route::delete('/reports/{report}/reject', [AdminReportController::class, 'reject'])
            ->whereNumber('report')
            ->name('reports.reject');

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
            ->whereNumber('note')  // FIXED: removed "parameters:"
            ->name('reports.notes.destroy');

        // LIVE map data (JSON for dashboard map)
        Route::get('/reports/map', [ReportMapController::class, 'index'])->name('reports.map');
    });

// Temporary debug route
Route::get('/debug-engagement', function() {
    return response()->json([
        'tables_exist' => [
            'report_likes' => Schema::hasTable('report_likes'),
            'report_comments' => Schema::hasTable('report_comments'),
        ],
        'columns_exist' => Schema::hasColumns('reports', ['likes_count', 'comments_count']),
        'routes_exist' => [
            'like' => Route::has('reports.like'),
            'comments_index' => Route::has('reports.comments.index'),
            'comments_store' => Route::has('reports.comments.store'),
        ],
        'user_authenticated' => auth()->check(),
        'user_name' => auth()->user()->name ?? 'Not logged in',
        'first_report_id' => \App\Models\Report::first()?->id ?? 'No reports',
        'csrf_token' => csrf_token()
    ]);
});

require __DIR__.'/auth.php';