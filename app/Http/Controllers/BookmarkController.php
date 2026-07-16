<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookmarkController extends Controller
{
    /**
     * Toggle bookmark for a report
     */
    public function toggle(Request $request, Report $report): JsonResponse
    {
        $user = $request->user();

        // Check if already bookmarked
        $exists = DB::table('report_bookmarks')
            ->where('user_id', $user->id)
            ->where('report_id', $report->id)
            ->exists();

        if ($exists) {
            // Remove bookmark
            DB::table('report_bookmarks')
                ->where('user_id', $user->id)
                ->where('report_id', $report->id)
                ->delete();

            return response()->json([
                'success' => true,
                'bookmarked' => false,
                'message' => 'Report removed from bookmarks',
            ]);
        } else {
            // Add bookmark
            DB::table('report_bookmarks')->insert([
                'user_id' => $user->id,
                'report_id' => $report->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'bookmarked' => true,
                'message' => 'Report bookmarked successfully',
            ]);
        }
    }

    /**
     * Get user's bookmarked reports
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $bookmarks = Report::query()
            ->join('report_bookmarks', 'reports.id', '=', 'report_bookmarks.report_id')
            ->where('report_bookmarks.user_id', $user->id)
            ->with('user:id,name')
            ->withCount(['likes', 'comments'])
            ->select('reports.*', 'report_bookmarks.created_at as bookmarked_at')
            ->orderBy('report_bookmarks.created_at', 'desc')
            ->paginate(15);

        // Add liked_by_user flag
        $bookmarks->getCollection()->each(function ($report) use ($user) {
            $report->liked_by_user = $report->likes()
                ->where('user_id', $user->id)
                ->exists();
        });

        return view('bookmarks.index', compact('bookmarks'));
    }
}
