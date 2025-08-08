<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;

class DashboardController extends Controller
{
public function index()
{
    $stats = [
        'total'    => Report::count(),
        'pending'  => Report::where('status', 'pending')->count(),
        'resolved' => Report::where('status', 'resolved')->count(),
        'users'    => User::count(),
    ];

    $reportsByCity = Report::select('city_corporation')
        ->selectRaw('COUNT(*) as count')
        ->groupBy('city_corporation')
        ->orderByDesc('count')
        ->get();

    $recentReports = Report::with('user:id,name')
        ->latest()
        ->take(8)
        ->get(['id','user_id','title','city_corporation','status','created_at']);

    // add the individual vars for the current Blade
    $totalReports    = $stats['total'];
    $pendingReports  = $stats['pending'];
    $resolvedReports = $stats['resolved'];
    $totalUsers      = $stats['users'];

    return view('admin.dashboard', compact(
        'stats', 'reportsByCity', 'recentReports',
        'totalReports', 'pendingReports', 'resolvedReports', 'totalUsers'
    ));
}
}