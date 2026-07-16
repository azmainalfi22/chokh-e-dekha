<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportMedia extends Model
{
    protected $fillable = ['report_id','file_path','original_name','mime_type','file_size','sha256','provenance','redactions'];

    protected $casts = [
        'provenance' => 'array',
        'redactions' => 'array',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
