<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportLike;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function toggle(Report $report): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $existingLike = ReportLike::where([
                'report_id' => $report->id,
                'user_id' => $user->id,
            ])->first();

            if ($existingLike) {
                $existingLike->delete();
                $liked = false;
            } else {
                ReportLike::create([
                    'report_id' => $report->id,
                    'user_id' => $user->id,
                ]);
                $liked = true;
            }

            // Refresh the report to get updated counts
            $report->refresh();

            return response()->json([
                'liked' => $liked,
                'likes_count' => $report->likes_count,
                'success' => true,
            ]);

        } catch (\Exception $e) {
            \Log::error('Like toggle failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update like status',
            ], 500);
        }
    }
}