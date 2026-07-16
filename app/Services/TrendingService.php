<?php

namespace App\Services;

use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TrendingService
{
    /**
     * Calculate trending score for a report
     * Score = (likes × 2) + (comments × 3) + (shares × 5) + (views × 0.1) × time_decay
     */
    public function calculateTrendingScore(Report $report): float
    {
        $likes = $report->likes_count ?? 0;
        $comments = $report->comments_count ?? 0;
        $shares = $report->shares_count ?? 0;
        $views = $report->views_count ?? 0;
        
        // Base engagement score
        $engagementScore = ($likes * 2) + ($comments * 3) + ($shares * 5) + ($views * 0.1);
        
        // Time decay factor (newer content gets boost)
        $hoursOld = Carbon::parse($report->created_at)->diffInHours(now());
        $timeDecay = $this->calculateTimeDecay($hoursOld);
        
        // Status bonus (resolved reports get slight boost to celebrate)
        $statusBonus = $report->status === 'resolved' ? 1.2 : 1.0;
        
        return $engagementScore * $timeDecay * $statusBonus;
    }

    /**
     * Calculate time decay factor
     * Recent content (0-24h): 2.0x boost
     * 1-3 days: 1.5x
     * 3-7 days: 1.0x
     * 7-30 days: 0.5x
     * 30+ days: 0.2x
     */
    protected function calculateTimeDecay(int $hoursOld): float
    {
        return match(true) {
            $hoursOld <= 24 => 2.0,
            $hoursOld <= 72 => 1.5,
            $hoursOld <= 168 => 1.0,  // 7 days
            $hoursOld <= 720 => 0.5,  // 30 days
            default => 0.2,
        };
    }

    /**
     * Get trending reports
     */
    public function getTrendingReports(int $limit = 10, ?string $timeframe = 'week'): Collection
    {
        $cacheKey = "trending_reports_{$timeframe}_{$limit}";
        
        return Cache::remember($cacheKey, now()->addMinutes(15), function() use ($limit, $timeframe) {
            $query = Report::query()
                ->with('user:id,name')
                ->withCount(['likes', 'comments']);

            // Apply timeframe filter
            if ($timeframe) {
                $date = match($timeframe) {
                    'today' => now()->startOfDay(),
                    'week' => now()->subWeek(),
                    'month' => now()->subMonth(),
                    default => now()->subWeek(),
                };
                
                $query->where('created_at', '>=', $date);
            }

            // Get reports and calculate trending scores
            $reports = $query->get();
            
            return $reports->map(function($report) {
                $report->trending_score = $this->calculateTrendingScore($report);
                return $report;
            })
            ->sortByDesc('trending_score')
            ->take($limit)
            ->values();
        });
    }

    /**
     * Get trending by category
     */
    public function getTrendingByCategory(string $category, int $limit = 5): Collection
    {
        $cacheKey = "trending_category_{$category}_{$limit}";
        
        return Cache::remember($cacheKey, now()->addMinutes(15), function() use ($category, $limit) {
            $reports = Report::query()
                ->where('category', $category)
                ->where('created_at', '>=', now()->subWeek())
                ->with('user:id,name')
                ->withCount(['likes', 'comments'])
                ->get();

            return $reports->map(function($report) {
                $report->trending_score = $this->calculateTrendingScore($report);
                return $report;
            })
            ->sortByDesc('trending_score')
            ->take($limit)
            ->values();
        });
    }

    /**
     * Get personalized recommendations for a user
     */
    public function getPersonalizedFeed($userId, int $limit = 20): Collection
    {
        // Get user's engagement history
        $userLikes = DB::table('report_likes')
            ->where('user_id', $userId)
            ->pluck('report_id');

        $userComments = DB::table('report_comments')
            ->where('user_id', $userId)
            ->pluck('report_id');

        $engagedReportIds = $userLikes->merge($userComments)->unique();

        // Get categories user has engaged with
        $preferredCategories = Report::whereIn('id', $engagedReportIds)
            ->pluck('category')
            ->filter()
            ->unique()
            ->take(3);

        // Get user's location (if available)
        $user = \App\Models\User::find($userId);
        $userCity = $user->city ?? null;

        // Build personalized query
        $query = Report::query()
            ->with('user:id,name')
            ->withCount(['likes', 'comments'])
            ->where('user_id', '!=', $userId) // Exclude own reports
            ->whereNotIn('id', $engagedReportIds) // Exclude already engaged
            ->where('created_at', '>=', now()->subMonth()); // Recent only

        // Boost preferred categories
        if ($preferredCategories->isNotEmpty()) {
            $query->where(function($q) use ($preferredCategories, $userCity) {
                $q->whereIn('category', $preferredCategories)
                  ->orWhere('city_corporation', $userCity);
            });
        }

        $reports = $query->get();

        // Calculate personalized scores
        return $reports->map(function($report) use ($preferredCategories, $userCity) {
            $baseScore = $this->calculateTrendingScore($report);
            
            // Boost for preferred category
            $categoryBoost = $preferredCategories->contains($report->category) ? 1.5 : 1.0;
            
            // Boost for same city
            $locationBoost = $report->city_corporation === $userCity ? 1.3 : 1.0;
            
            $report->personalized_score = $baseScore * $categoryBoost * $locationBoost;
            return $report;
        })
        ->sortByDesc('personalized_score')
        ->take($limit)
        ->values();
    }

    /**
     * Get trending hashtags
     */
    public function getTrendingHashtags(int $limit = 10): array
    {
        $cacheKey = "trending_hashtags_{$limit}";
        
        return Cache::remember($cacheKey, now()->addHours(1), function() use ($limit) {
            // Extract hashtags from recent reports
            $recentReports = Report::where('created_at', '>=', now()->subWeek())
                ->select('title', 'description')
                ->get();

            $hashtags = [];
            
            foreach ($recentReports as $report) {
                $text = $report->title . ' ' . $report->description;
                preg_match_all('/#(\w+)/', $text, $matches);
                
                foreach ($matches[1] as $tag) {
                    $tag = strtolower($tag);
                    $hashtags[$tag] = ($hashtags[$tag] ?? 0) + 1;
                }
            }

            arsort($hashtags);
            
            return array_slice($hashtags, 0, $limit, true);
        });
    }

    /**
     * Get related reports based on similarity
     */
    public function getRelatedReports(Report $report, int $limit = 5): Collection
    {
        $cacheKey = "related_reports_{$report->id}_{$limit}";
        
        return Cache::remember($cacheKey, now()->addHours(6), function() use ($report, $limit) {
            // Find reports with similar attributes
            $query = Report::query()
                ->where('id', '!=', $report->id)
                ->where('status', '!=', 'rejected')
                ->with('user:id,name')
                ->withCount(['likes', 'comments']);

            // Same category gets highest priority
            $sameCategory = (clone $query)
                ->where('category', $report->category)
                ->inRandomOrder()
                ->limit($limit)
                ->get();

            // If not enough, add from same city
            if ($sameCategory->count() < $limit && $report->city_corporation) {
                $sameCity = (clone $query)
                    ->where('city_corporation', $report->city_corporation)
                    ->whereNotIn('id', $sameCategory->pluck('id'))
                    ->inRandomOrder()
                    ->limit($limit - $sameCategory->count())
                    ->get();
                    
                $sameCategory = $sameCategory->merge($sameCity);
            }

            // If still not enough, add popular reports
            if ($sameCategory->count() < $limit) {
                $popular = (clone $query)
                    ->whereNotIn('id', $sameCategory->pluck('id'))
                    ->orderByDesc('likes_count')
                    ->limit($limit - $sameCategory->count())
                    ->get();
                    
                $sameCategory = $sameCategory->merge($popular);
            }

            return $sameCategory->take($limit);
        });
    }

    /**
     * Clear all trending caches
     */
    public function clearTrendingCaches(): void
    {
        Cache::flush(); // Simple approach for now
        // In production, use cache tags for more granular control
    }

    /**
     * Update report views count
     */
    public function incrementViews(Report $report): void
    {
        // Use atomic increment to avoid race conditions
        DB::table('reports')
            ->where('id', $report->id)
            ->increment('views_count');
            
        // Clear related caches
        Cache::forget("trending_reports_week_10");
        Cache::forget("trending_reports_month_10");
    }
}

