<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\CacheService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected CacheService $cache;

    public function __construct(CacheService $cache)
    {
        $this->cache = $cache;
    }

    public function index()
    {
        $userId = Auth::id();

        // ✅ OPTIMIZATION: Use caching for dashboard stats
        $stats = $this->cache->getDashboardStats($userId);

        // ✅ OPTIMIZATION: Select only needed columns + eager load user
        $recentReports = Report::where('user_id', $userId)
            ->select(['id', 'title', 'city_corporation', 'status', 'category', 'location', 'created_at'])
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'recentReports'));
    }
}
