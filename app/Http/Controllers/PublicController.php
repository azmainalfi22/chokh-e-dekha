<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicController extends Controller
{
    /**
     * Public transparency dashboard
     */
    public function transparency()
    {
        // Overall statistics
        $totalReports = Report::count();
        $resolvedReports = Report::where('status', 'resolved')->count();
        $pendingReports = Report::where('status', 'pending')->count();
        $inProgressReports = Report::where('status', 'in_progress')->count();
        $resolutionRate = $totalReports > 0 ? round(($resolvedReports / $totalReports) * 100) : 0;

        // Average resolution time
        $avgResolutionTime = Report::where('status', 'resolved')
            ->whereNotNull('status_updated_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, status_updated_at)) as avg_hours')
            ->value('avg_hours');

        // Category breakdown
        $categoryStats = Report::select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->get();

        // Monthly trend (last 6 months)
        $monthlyTrend = Report::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as total')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Recent resolved cases (public showcase)
        $recentResolved = Report::where('status', 'resolved')
            ->with('user:id,name')
            ->latest('status_updated_at')
            ->take(10)
            ->get();

        // SLA performance
        $onTimeSLA = Report::where('status', 'resolved')
            ->whereColumn('status_updated_at', '<=', 'sla_due_at')
            ->count();
        $slaComplianceRate = $resolvedReports > 0 ? round(($onTimeSLA / $resolvedReports) * 100) : 0;

        return view('public.transparency', compact(
            'totalReports',
            'resolvedReports',
            'pendingReports',
            'inProgressReports',
            'resolutionRate',
            'avgResolutionTime',
            'categoryStats',
            'monthlyTrend',
            'recentResolved',
            'slaComplianceRate'
        ));
    }

    /**
     * Open Data API endpoint (JSON export)
     */
    public function openData(Request $request)
    {
        $query = Report::query()
            ->select(['id', 'title', 'category', 'status', 'priority', 'location', 'latitude', 'longitude', 'created_at', 'status_updated_at'])
            ->where('status', '!=', 'draft'); // Exclude drafts

        // Optional filters
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }

        $data = $query->latest()->paginate(100);

        return response()->json([
            'data' => $data->items(),
            'meta' => [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
            ],
            'generated_at' => now()->toIso8601String(),
            'source' => config('app.name'),
        ]);
    }
}

