<?php

// app/Models/Comment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $fillable = ['report_id','user_id','body'];

    public function report(): BelongsTo { return $this->belongsTo(Report::class); }
    public function user(): BelongsTo   { return $this->belongsTo(User::class); }

}


