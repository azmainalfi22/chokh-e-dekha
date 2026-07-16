<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;

class AnalyticsController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'reports_total' => Report::count(),
            'reports_resolved' => Report::where('status','resolved')->count(),
            'users_total' => User::count(),
        ]);
    }

    public function reports()
    {
        $byStatus = Report::selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c','status');
        return response()->json(['by_status' => $byStatus]);
    }

    public function users()
    {
        return response()->json(['total' => User::count()]);
    }

    public function engagement()
    {
        $popular = Report::withCount(['likes','comments'])->orderByRaw('(likes_count + comments_count) DESC')->limit(10)->get(['id','title','likes_count','comments_count']);
        return response()->json(['top' => $popular]);
    }
}


