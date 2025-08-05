<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\User;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard with statistics.
     */
    public function dashboard()
    {
        // Basic Stats
        $totalReports = Report::count();
        $pendingReports = Report::where('status', 'pending')->count();
        $resolvedReports = Report::where('status', 'resolved')->count();

        // Reports by City Corporation
        $reportsByCity = Report::selectRaw('city_corporation, COUNT(*) as count')
            ->groupBy('city_corporation')
            ->get();

        // Recent Reports
        $recentReports = Report::with('user')->latest()->take(5)->get();

        // User count
        $totalUsers = User::count();

        return view('admin.dashboard', compact(
            'totalReports',
            'pendingReports',
            'resolvedReports',
            'reportsByCity',
            'recentReports',
            'totalUsers'
        ));
    }
}
