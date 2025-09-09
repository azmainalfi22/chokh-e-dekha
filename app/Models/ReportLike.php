<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
class ReportLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

protected static function boot()
{
    parent::boot();

    static::created(function ($like) {
        if (Schema::hasColumn('reports', 'likes_count')) {
            $like->report->increment('likes_count');
        }
    });

    static::deleted(function ($like) {
        if (Schema::hasColumn('reports', 'likes_count')) {
            $like->report->decrement('likes_count');
        }
    });
}
}