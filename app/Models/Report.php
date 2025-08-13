<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category',
        'city_corporation',
        'location',
        'status',
        'admin_note',
        'photo', // stores "reports/xxx.jpg" on the public disk
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // expose nice URLs/filename to Blade
    protected $appends = ['photo_url', 'photo_name'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? Storage::url($this->photo) : null;
    }

    public function getPhotoNameAttribute(): ?string
    {
        return $this->photo ? basename($this->photo) : null;
    }

    // keep storage tidy if a report is deleted
    protected static function booted(): void
    {
        static::deleting(function (Report $report) {
            if ($report->photo && Storage::disk('public')->exists($report->photo)) {
                Storage::disk('public')->delete($report->photo);
            }
        });
    }
    public function notes() {
    return $this->hasMany(\App\Models\ReportNote::class)->latest();
}

}

