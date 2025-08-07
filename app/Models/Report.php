<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'category',
        'city_corporation',
        'location',
        'photo',
        'status',
        'user_id',
    ];
    public function user()
{
    return $this->belongsTo(\App\Models\User::class, 'user_id');
}

}
