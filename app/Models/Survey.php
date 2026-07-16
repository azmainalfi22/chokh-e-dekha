<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Survey extends Model
{
    protected $fillable = [
        'title','slug','questions','target_scope','status','created_by'
    ];

    protected $casts = [
        'questions' => 'array',
    ];

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }
}


