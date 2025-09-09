<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'address',
        'is_admin',
    ];

    /**
     * Attributes hidden in arrays / JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casting.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_admin'          => 'boolean',
        ];
    }

    /* ----------------------------
     | Report Relationships
     * ---------------------------- */

    /**
     * Reports created by this user
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    /* ----------------------------
     | Engagement Relationships
     * ---------------------------- */

    /**
     * Likes given by this user
     */
    public function reportLikes(): HasMany
    {
        return $this->hasMany(ReportLike::class);
    }

    /**
     * Comments made by this user
     */
    public function reportComments(): HasMany
    {
        return $this->hasMany(ReportComment::class);
    }

    /**
     * Reports this user has liked (many-to-many through likes)
     */
    public function likedReports(): BelongsToMany
    {
        return $this->belongsToMany(Report::class, 'report_likes')
                    ->withTimestamps()
                    ->orderByPivot('created_at', 'desc');
    }

    /* ----------------------------
     | Engagement Helper Methods
     * ---------------------------- */

    /**
     * Check if user has liked a specific report
     */
    public function hasLiked(Report $report): bool
    {
        return $this->reportLikes()
                    ->where('report_id', $report->id)
                    ->exists();
    }

    /**
     * Like a report (toggle behavior)
     */
    public function toggleLike(Report $report): bool
    {
        $existingLike = $this->reportLikes()
                            ->where('report_id', $report->id)
                            ->first();

        if ($existingLike) {
            $existingLike->delete();
            return false; // unliked
        } else {
            $this->reportLikes()->create([
                'report_id' => $report->id
            ]);
            return true; // liked
        }
    }

    /**
     * Get user's engagement statistics
     */
    public function getEngagementStats(): array
    {
        return [
            'reports_created' => $this->reports()->count(),
            'likes_given' => $this->reportLikes()->count(),
            'comments_made' => $this->reportComments()->count(),
            'reports_liked' => $this->likedReports()->count(),
        ];
    }

    /**
     * Get user's recent activity
     */
    public function getRecentActivity(int $limit = 10): array
    {
        $recentLikes = $this->reportLikes()
                           ->with('report:id,title,created_at')
                           ->latest()
                           ->limit($limit)
                           ->get()
                           ->map(function ($like) {
                               return [
                                   'type' => 'like',
                                   'action' => 'liked a report',
                                   'report' => $like->report,
                                   'created_at' => $like->created_at,
                               ];
                           });

        $recentComments = $this->reportComments()
                              ->with('report:id,title,created_at')
                              ->latest()
                              ->limit($limit)
                              ->get()
                              ->map(function ($comment) {
                                  return [
                                      'type' => 'comment',
                                      'action' => 'commented on a report',
                                      'report' => $comment->report,
                                      'comment' => $comment,
                                      'created_at' => $comment->created_at,
                                  ];
                              });

        $recentReports = $this->reports()
                             ->latest()
                             ->limit($limit)
                             ->get()
                             ->map(function ($report) {
                                 return [
                                     'type' => 'report',
                                     'action' => 'created a report',
                                     'report' => $report,
                                     'created_at' => $report->created_at,
                                 ];
                             });

        return $recentLikes->concat($recentComments)
                          ->concat($recentReports)
                          ->sortByDesc('created_at')
                          ->take($limit)
                          ->values()
                          ->toArray();
    }

    /* ----------------------------
     | User Scopes
     * ---------------------------- */

    /**
     * Scope to get active users (users who have engaged recently)
     */
    public function scopeActive($query, int $days = 30)
    {
        $date = now()->subDays($days);
        
        return $query->where(function ($q) use ($date) {
            $q->whereHas('reports', function ($reportQuery) use ($date) {
                $reportQuery->where('created_at', '>=', $date);
            })
            ->orWhereHas('reportLikes', function ($likeQuery) use ($date) {
                $likeQuery->where('created_at', '>=', $date);
            })
            ->orWhereHas('reportComments', function ($commentQuery) use ($date) {
                $commentQuery->where('created_at', '>=', $date);
            });
        });
    }

    /**
     * Scope to get users by engagement level
     */
    public function scopeByEngagement($query, string $level = 'all')
    {
        return match($level) {
            'high' => $query->withCount(['reports', 'reportLikes', 'reportComments'])
                           ->havingRaw('(reports_count + report_likes_count + report_comments_count) >= 10'),
            'medium' => $query->withCount(['reports', 'reportLikes', 'reportComments'])
                             ->havingRaw('(reports_count + report_likes_count + report_comments_count) BETWEEN 3 AND 9'),
            'low' => $query->withCount(['reports', 'reportLikes', 'reportComments'])
                          ->havingRaw('(reports_count + report_likes_count + report_comments_count) BETWEEN 1 AND 2'),
            'none' => $query->withCount(['reports', 'reportLikes', 'reportComments'])
                           ->havingRaw('(reports_count + report_likes_count + report_comments_count) = 0'),
            default => $query
        };
    }

    /* ----------------------------
     | Accessors & Helpers
     * ---------------------------- */

    /**
     * Get user's avatar initials
     */
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', trim($this->name));
        $initials = '';
        
        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
            if (strlen($initials) >= 2) break;
        }
        
        return $initials ?: 'U';
    }

    /**
     * Check if user is active (has recent engagement)
     */
    public function getIsActiveAttribute(): bool
    {
        $thirtyDaysAgo = now()->subDays(30);
        
        return $this->reports()->where('created_at', '>=', $thirtyDaysAgo)->exists() ||
               $this->reportLikes()->where('created_at', '>=', $thirtyDaysAgo)->exists() ||
               $this->reportComments()->where('created_at', '>=', $thirtyDaysAgo)->exists();
    }

    /**
     * Get user's reputation score based on engagement
     */
    public function getReputationScoreAttribute(): int
    {
        $stats = $this->getEngagementStats();
        
        // Simple scoring: reports = 10 points, comments = 3 points, likes = 1 point
        return ($stats['reports_created'] * 10) + 
               ($stats['comments_made'] * 3) + 
               ($stats['likes_given'] * 1);
    }

    /* ----------------------------
     | Admin Helper Methods
     * ---------------------------- */

    /**
     * Check if user can moderate content
     */
    public function canModerate(): bool
    {
        return $this->is_admin;
    }

    /**
     * Check if user can delete a comment
     */
    public function canDeleteComment(ReportComment $comment): bool
    {
        return $this->is_admin || $this->id === $comment->user_id;
    }

    /**
     * Check if user can edit a report
     */
    public function canEditReport(Report $report): bool
    {
        return $this->is_admin || $this->id === $report->user_id;
    }
}