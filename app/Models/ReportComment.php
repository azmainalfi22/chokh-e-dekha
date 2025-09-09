<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
class ReportComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'report_id',
        'user_id',
        'parent_id',
        'body',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ReportComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ReportComment::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }

    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

protected static function boot()
{
    parent::boot();

    static::created(function ($comment) {
        // Only increment for top-level comments
        if (!$comment->parent_id && Schema::hasColumn('reports', 'comments_count')) {
            $comment->report->increment('comments_count');
        }
    });

    static::deleted(function ($comment) {
        // Only decrement for top-level comments
        if (!$comment->parent_id && Schema::hasColumn('reports', 'comments_count')) {
            $comment->report->decrement('comments_count');
        }
    });
}
}