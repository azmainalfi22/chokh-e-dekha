<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportBookmark extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'report_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * User who bookmarked the report
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Bookmarked report
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
