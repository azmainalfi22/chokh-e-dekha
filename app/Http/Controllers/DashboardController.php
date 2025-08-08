<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $stats = [
            'total'    => Report::where('user_id', $userId)->count(),
            'pending'  => Report::where('user_id', $userId)->where('status', 'pending')->count(),
            'resolved' => Report::where('user_id', $userId)->where('status', 'resolved')->count(),
            // keep keys consistent even if unused in user UI:
            'users'    => null,
        ];

        $recentReports = Report::where('user_id', $userId)
            ->latest()
            ->take(6)
            ->get(['id','title','city_corporation','status','created_at']);

        return view('dashboard', compact('stats', 'recentReports'));
    }
}
