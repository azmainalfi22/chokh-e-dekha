<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ReportController as ApiReportController;
use App\Http\Controllers\Api\CommentController as ApiCommentController;
use App\Http\Controllers\Api\NotificationController as ApiNotificationController;
use App\Http\Controllers\Api\EndorsementController as ApiEndorsementController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned the "api" middleware group. Make something great!
|
*/

// Public API routes (no authentication required)
Route::prefix('v1')->group(function () {
    
    // === AUTHENTICATION ===
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    // === PUBLIC REPORTS ===
    Route::prefix('reports')->group(function () {
        Route::get('/', [ApiReportController::class, 'index']);
        Route::get('/{report}', [ApiReportController::class, 'show'])->whereNumber('report');
        Route::get('/map/data', [ApiReportController::class, 'mapData']);
        Route::get('/search', [ApiReportController::class, 'search']);
        Route::get('/categories', [ApiReportController::class, 'categories']);
        Route::get('/cities', [ApiReportController::class, 'cities']);
    });
});

// Protected API routes (authentication required)
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    
    // === USER AUTHENTICATION & PROFILE ===
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::patch('/me', [AuthController::class, 'updateProfile']);
        Route::delete('/me', [AuthController::class, 'deleteAccount']);
    });

    // === REPORTS MANAGEMENT ===
    Route::prefix('reports')->group(function () {
        // User's own reports
        Route::get('/my-reports', [ApiReportController::class, 'myReports']);
        
        // Create reports (non-admins only)
        Route::post('/', [ApiReportController::class, 'store'])
            ->middleware('throttle:5,60'); // 5 reports per hour
        
        // Update/delete own reports
        Route::patch('/{report}', [ApiReportController::class, 'update'])
            ->whereNumber('report')
            ->middleware('can:update,report');
        
        Route::delete('/{report}', [ApiReportController::class, 'destroy'])
            ->whereNumber('report')
            ->middleware('can:delete,report');

        // === REPORT ENDORSEMENTS ===
        Route::post('/{report}/endorse', [ApiEndorsementController::class, 'toggle'])
            ->whereNumber('report')
            ->middleware('throttle:likes');

        // === REPORT COMMENTS ===
        Route::prefix('{report}/comments')->whereNumber('report')->group(function () {
            // List comments
            Route::get('/', [ApiCommentController::class, 'index']);
            Route::get('/search', [ApiCommentController::class, 'search'])
                ->middleware('throttle:search');
            
            // Create comments
            Route::post('/', [ApiCommentController::class, 'store'])
                ->middleware('throttle:comments');
            
            // Individual comment actions
            Route::prefix('{comment}')->whereNumber('comment')->group(function () {
                // View single comment
                Route::get('/', [ApiCommentController::class, 'show']);
                
                // Update comment
                Route::patch('/', [ApiCommentController::class, 'update'])
                    ->middleware(['throttle:comments', 'can:update,comment']);
                
                // Delete comment
                Route::delete('/', [ApiCommentController::class, 'destroy'])
                    ->middleware('can:delete,comment');
                
                // Like/unlike comment
                Route::post('/like', [ApiCommentController::class, 'toggleLike'])
                    ->middleware('throttle:likes');
                
                // Flag comment for moderation
                Route::post('/flag', [ApiCommentController::class, 'flag'])
                    ->middleware('throttle:flags');
                
                // View edit history (author/moderators only)
                Route::get('/history', [ApiCommentController::class, 'history'])
                    ->middleware('can:viewHistory,comment');
                
                // Pin/unpin comment (moderators only)
                Route::post('/pin', [ApiCommentController::class, 'togglePin'])
                    ->middleware('can:moderate,comment');
            });
        });
    });

    // === NOTIFICATIONS ===
    Route::prefix('notifications')->group(function () {
        // List notifications
        Route::get('/', [ApiNotificationController::class, 'index']);
        
        // Real-time polling
        Route::get('/poll', [ApiNotificationController::class, 'poll'])
            ->middleware('throttle:notifications');
        
        // Statistics
        Route::get('/stats', [ApiNotificationController::class, 'stats']);
        Route::get('/unread-count', [ApiNotificationController::class, 'unreadCount'])
            ->middleware('throttle:notifications');
        
        // Individual notification actions
        Route::prefix('{id}')->group(function () {
            Route::post('/read', [ApiNotificationController::class, 'markRead']);
            Route::delete('/clear', [ApiNotificationController::class, 'clear']);
            Route::post('/snooze', [ApiNotificationController::class, 'snooze']);
        });
        
        // Bulk operations
        Route::post('/bulk-read', [ApiNotificationController::class, 'bulkMarkRead']);
        Route::delete('/bulk-clear', [ApiNotificationController::class, 'bulkClear']);
        Route::post('/read-all', [ApiNotificationController::class, 'markAll']);
        Route::delete('/clear-all', [ApiNotificationController::class, 'clearAll']);
        
        // Maintenance operations
        Route::delete('/archive-old', [ApiNotificationController::class, 'archiveOld']);
    });

    // === USER DASHBOARD DATA ===
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [ApiReportController::class, 'dashboardStats']);
        Route::get('/recent-activity', [ApiNotificationController::class, 'recentActivity']);
        Route::get('/my-reports-summary', [ApiReportController::class, 'myReportsSummary']);
    });
});

// === ADMIN API ROUTES ===
Route::middleware(['auth:sanctum', 'is_admin'])->prefix('v1/admin')->group(function () {
    
    // === ADMIN REPORTS MANAGEMENT ===
    Route::prefix('reports')->group(function () {
        // List all reports with admin filters
        Route::get('/', [ApiReportController::class, 'adminIndex']);
        Route::get('/stats', [ApiReportController::class, 'adminStats']);
        Route::get('/export', [ApiReportController::class, 'export']);
        
        // Individual report admin actions
        Route::prefix('{report}')->whereNumber('report')->group(function () {
            Route::post('/approve', [ApiReportController::class, 'approve']);
            Route::delete('/reject', [ApiReportController::class, 'reject']);
            Route::patch('/status', [ApiReportController::class, 'updateStatus']);
            Route::post('/assign', [ApiReportController::class, 'assignToMe']);
            
            // Admin notes
            Route::prefix('notes')->group(function () {
                Route::get('/', [ApiReportController::class, 'getNotes']);
                Route::post('/', [ApiReportController::class, 'storeNote']);
                Route::delete('/{note}', [ApiReportController::class, 'destroyNote'])
                    ->whereNumber('note');
            });
        });
        
        // Bulk operations
        Route::post('/bulk-action', [ApiReportController::class, 'bulkAction']);
        Route::post('/bulk-assign', [ApiReportController::class, 'bulkAssign']);
        Route::post('/bulk-status', [ApiReportController::class, 'bulkStatus']);
    });

    // === ADMIN COMMENT MODERATION ===
    Route::prefix('comments')->group(function () {
        Route::get('/flagged', [ApiCommentController::class, 'flagged']);
        Route::get('/recent', [ApiCommentController::class, 'recent']);
        Route::post('/{comment}/moderate', [ApiCommentController::class, 'moderate'])
            ->whereNumber('comment');
    });

    // === ADMIN USERS MANAGEMENT ===
    Route::prefix('users')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\UserController::class, 'index']);
        Route::get('/{user}', [\App\Http\Controllers\Api\UserController::class, 'show'])
            ->whereNumber('user');
        Route::patch('/{user}/status', [\App\Http\Controllers\Api\UserController::class, 'updateStatus'])
            ->whereNumber('user');
    });

    // === ADMIN ANALYTICS ===
    Route::prefix('analytics')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Api\AnalyticsController::class, 'dashboard']);
        Route::get('/reports', [\App\Http\Controllers\Api\AnalyticsController::class, 'reports']);
        Route::get('/users', [\App\Http\Controllers\Api\AnalyticsController::class, 'users']);
        Route::get('/engagement', [\App\Http\Controllers\Api\AnalyticsController::class, 'engagement']);
    });
});

// === HEALTH CHECK & STATUS ===
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version', '1.0.0')
    ]);
});

// === API FALLBACK ===
Route::fallback(function () {
    return response()->json([
        'message' => 'API endpoint not found.',
        'available_versions' => ['v1'],
        'documentation' => url('/docs/api')
    ], 404);
});