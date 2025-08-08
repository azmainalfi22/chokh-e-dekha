<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Top counters
        $totalReports    = Report::count();
        $pendingReports  = Report::where('status', 'pending')->count();
        $resolvedReports = Report::where('status', 'resolved')->count();
        $totalUsers      = User::count();

        // Reports by city
        $reportsByCity = Report::selectRaw('city_corporation, COUNT(*) as count')
            ->groupBy('city_corporation')
            ->orderByDesc('count')
            ->get();

        // Recent reports
        $recentReports = Report::with('user')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalReports',
            'pendingReports',
            'resolvedReports',
            'totalUsers',
            'reportsByCity',
            'recentReports'
        ));
    }
}
