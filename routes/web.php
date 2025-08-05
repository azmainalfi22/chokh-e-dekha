<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Protecting report routes with authentication
Route::middleware('auth')->group(function () {
    Route::resource('reports', ReportController::class)->except(['index', 'update']);
});

// Protecting admin routes with both authentication and admin middleware
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/reports', [ReportController::class, 'index'])->name('admin.reports.index');
    Route::put('/admin/reports/{report}', [ReportController::class, 'update'])->name('admin.reports.update');
    // Admin-only: toggle report status
    Route::get('/report/{id}/toggle-status', [ReportController::class, 'toggleStatus'])->name('report.toggleStatus');
});

// Homepage: View all reports
Route::get('/', [ReportController::class, 'index'])->name('home');

// Report submission (publicly accessible)
Route::get('/report/create', [ReportController::class, 'create'])->name('report.create');
Route::post('/report', [ReportController::class, 'store'])->name('report.store');

// Dashboard (Breeze default)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


// Authenticated user routes
Route::middleware('auth')->group(function () {
    // Profile routes (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
