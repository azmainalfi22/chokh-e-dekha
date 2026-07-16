<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Report;

class CacheService
{
    /**
     * Cache duration in seconds (15 minutes)
     */
    protected int $duration = 900;

    /**
     * Get dashboard statistics with caching
     */
    public function getDashboardStats(int $userId): array
    {
        $cacheKey = "dashboard_stats_{$userId}";

        return Cache::remember($cacheKey, $this->duration, function () use ($userId) {
            $statsQuery = Report::where('user_id', $userId)
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as in_progress,
                    SUM(CASE WHEN status = "resolved" THEN 1 ELSE 0 END) as resolved
                ')
                ->first();

            return [
                'total' => $statsQuery->total ?? 0,
                'pending' => $statsQuery->pending ?? 0,
                'in_progress' => $statsQuery->in_progress ?? 0,
                'resolved' => $statsQuery->resolved ?? 0,
            ];
        });
    }

    /**
     * Get global statistics for admin dashboard
     */
    public function getGlobalStats(): array
    {
        $cacheKey = "global_stats";

        return Cache::remember($cacheKey, $this->duration, function () {
            $statsQuery = Report::selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = "resolved" THEN 1 ELSE 0 END) as resolved,
                SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected
            ')->first();

            return [
                'total' => $statsQuery->total ?? 0,
                'pending' => $statsQuery->pending ?? 0,
                'in_progress' => $statsQuery->in_progress ?? 0,
                'resolved' => $statsQuery->resolved ?? 0,
                'rejected' => $statsQuery->rejected ?? 0,
            ];
        });
    }

    /**
     * Get category statistics
     */
    public function getCategoryStats(): array
    {
        $cacheKey = "category_stats";

        return Cache::remember($cacheKey, $this->duration, function () {
            return Report::selectRaw('category, COUNT(*) as count')
                ->whereNotNull('category')
                ->groupBy('category')
                ->orderByDesc('count')
                ->pluck('count', 'category')
                ->toArray();
        });
    }

    /**
     * Get city statistics
     */
    public function getCityStats(): array
    {
        $cacheKey = "city_stats";

        return Cache::remember($cacheKey, $this->duration, function () {
            return Report::selectRaw('city_corporation, COUNT(*) as count')
                ->whereNotNull('city_corporation')
                ->groupBy('city_corporation')
                ->orderByDesc('count')
                ->pluck('count', 'city_corporation')
                ->toArray();
        });
    }

    /**
     * Clear dashboard cache for a specific user
     */
    public function clearDashboardCache(int $userId): void
    {
        Cache::forget("dashboard_stats_{$userId}");
    }

    /**
     * Clear global statistics cache
     */
    public function clearGlobalCache(): void
    {
        Cache::forget("global_stats");
        Cache::forget("category_stats");
        Cache::forget("city_stats");
    }

    /**
     * Clear all report-related caches
     */
    public function clearAllReportCaches(): void
    {
        Cache::flush();
    }
}

