<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Report extends Model
{
    use HasFactory;

    /* ----------------------------
     | Constants
     * ---------------------------- */
    public const STATUSES   = ['pending', 'in_progress', 'resolved', 'rejected'];

    public const CATEGORIES = [
        'Road Damage', 'Waste Management', 'Street Light', 'Water Supply',
        'Electricity', 'Drainage', 'Garbage', 'Broken Road', 'Other',
    ];

    /** Optional color hints for pins/badges */
    public const STATUS_COLORS = [
        'pending'      => '#eab308', // amber-500
        'in_progress'  => '#3b82f6', // blue-500
        'resolved'     => '#16a34a', // green-600
        'rejected'     => '#ef4444', // red-500
    ];

    /* ----------------------------
     | Mass assignment / casting
     * ---------------------------- */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category',
        'city_corporation',
        'location',
        'status',
        'admin_note',
        'photo',                // storage path like "reports/xxx.jpg"
        // Geo fields:
        'latitude',
        'longitude',
        'formatted_address',
        'place_id',
        'geocoded_at',
    ];

    protected $casts = [
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'geocoded_at' => 'datetime',
        'latitude'    => 'float',
        'longitude'   => 'float',
    ];

    /** Accessors appended to JSON (used in Blade) */
    protected $appends = [
        'photo_url',
        'photo_name',
        'short_address',
        'has_coords',
        'static_map_url',
    ];

    /* ----------------------------
     | Relationships
     * ---------------------------- */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Admin/internal notes on a report (newest first) */
    public function notes(): HasMany
    {
        return $this->hasMany(ReportNote::class)->latest();
    }

    /** Public comments (newest first) */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->latest();
    }

    /** Endorsements / upvotes */
    public function endorsements(): HasMany
    {
        return $this->hasMany(Endorsement::class);
    }

    /* ----------------------------
     | Convenience helpers for UI
     * ---------------------------- */

    public function statusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? '#6b7280'; // gray-500
    }

    public function isEndorsedBy(?int $userId): bool
    {
        if (!$userId) return false;
        if (!Schema::hasTable('endorsements') || !Schema::hasColumn('endorsements', 'report_id')) {
            return false;
        }
        return $this->endorsements()->where('user_id', $userId)->exists();
    }

    /* ----------------------------
     | Accessors
     * ---------------------------- */

    /**
     * Return a host-agnostic URL that always works on the current domain.
     * If the value is already a full URL, return it.
     * Otherwise return a relative path like "/storage/reports/xxx.jpg".
     */
    public function getPhotoUrlAttribute(): ?string
    {
        $path = $this->photo;
        if (!$path) return null;

        // If already a URL, pass through.
        if (Str::startsWith($path, ['http://', 'https://', '//'])) {
            return $path;
        }

        // Normalize separators and strip any accidental prefixes.
        $path = $this->normalizeStoragePath($path);

        // Prefer the public disk existence check, but always return the same relative URL.
        try {
            if (Storage::disk('public')->exists($path)) {
                return '/storage/'.ltrim($path, '/');
            }
        } catch (\Throwable $e) {
            // ignore and continue to fallbacks
        }

        // Direct file presence via the public symlink.
        if (is_file(public_path('storage/'.ltrim($path, '/')))) {
            return '/storage/'.ltrim($path, '/');
        }

        // Last resort: still return the relative path; if missing it'll 404 (better than wrong host).
        return '/storage/'.ltrim($path, '/');
    }

    public function getPhotoNameAttribute(): ?string
    {
        return $this->photo ? basename(str_replace('\\', '/', $this->photo)) : null;
    }

    public function getShortAddressAttribute(): ?string
    {
        if ($this->formatted_address) {
            $parts = array_map('trim', explode(',', $this->formatted_address));
            $take  = count($parts) >= 3 ? 3 : 2;
            return implode(', ', array_slice($parts, -$take));
        }
        return $this->location ?: null;
    }

    public function getHasCoordsAttribute(): bool
    {
        return is_numeric($this->latitude) && is_numeric($this->longitude);
    }

    /** Small static map thumbnail for lists/cards. */
    public function getStaticMapUrlAttribute(): ?string
    {
        if (!$this->has_coords) return null;
        $key = config('services.google_maps.key');
        if (!$key) return null;

        $lat = $this->latitude;
        $lng = $this->longitude;
        $params = http_build_query([
            'center'   => "{$lat},{$lng}",
            'zoom'     => 15,
            'size'     => '600x220',
            'scale'    => 2,
            'maptype'  => 'roadmap',
            'markers'  => "color:red|{$lat},{$lng}",
            'key'      => $key,
        ]);

        return "https://maps.googleapis.com/maps/api/staticmap?{$params}";
    }

    /* ----------------------------
     | Mutators (normalize)
     * ---------------------------- */

    public function setPhotoAttribute(?string $value): void
    {
        if (!$value) {
            $this->attributes['photo'] = null;
            return;
        }

        $this->attributes['photo'] = $this->normalizeStoragePath($value);
    }

    public function setStatusAttribute(?string $value): void
    {
        $v = $value ? strtolower(trim($value)) : null;
        $this->attributes['status'] = in_array($v, self::STATUSES, true) ? $v : $value;
    }

    public function setCategoryAttribute($value): void
    {
        if ($value === null) { $this->attributes['category'] = null; return; }
        $v = trim((string) $value);
        foreach (self::CATEGORIES as $opt) {
            if (strcasecmp($opt, $v) === 0) { $this->attributes['category'] = $opt; return; }
        }
        $this->attributes['category'] = $v;
    }

    public function setLatitudeAttribute($value): void
    {
        $this->attributes['latitude'] = is_null($value) ? null : (float) $value;
    }

    public function setLongitudeAttribute($value): void
    {
        $this->attributes['longitude'] = is_null($value) ? null : (float) $value;
    }

    /* ----------------------------
     | Model events – tidy storage
     * ---------------------------- */

    protected static function booted(): void
    {
        static::deleting(function (Report $report) {
            $path = $report->normalizeStoragePath($report->photo);
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        });

        static::updating(function (Report $report) {
            if ($report->isDirty('photo')) {
                $original = $report->normalizeStoragePath($report->getOriginal('photo'));
                if ($original && $original !== $report->photo && Storage::disk('public')->exists($original)) {
                    Storage::disk('public')->delete($original);
                }
            }
        });
    }

    /* ----------------------------
     | Simple filter/search scopes
     * ---------------------------- */

    public function scopeSearch($q, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') return $q;

        $like = '%'.str_replace(['\\','%','_'], ['\\\\','\\%','\\_'], $term).'%';
        return $q->where(function ($x) use ($like) {
            $x->where('title', 'like', $like)
              ->orWhere('description', 'like', $like)
              ->orWhere('formatted_address', 'like', $like)
              ->orWhere('location', 'like', $like);
        });
    }

    public function scopeCity($q, ?string $city)
    {
        return $city ? $q->where('city_corporation', $city) : $q;
    }

    public function scopeCategory($q, ?string $cat)
    {
        return $cat ? $q->where('category', $cat) : $q;
    }

    public function scopeStatus($q, ?string $status)
    {
        return $status ? $q->where('status', $status) : $q;
    }

    public function scopeDateBetween($q, ?string $from, ?string $to)
    {
        if ($from) $q->whereDate('created_at', '>=', $from);
        if ($to)   $q->whereDate('created_at', '<=', $to);
        return $q;
    }

    /* ----------------------------
     | GEO scopes (maps, clustering)
     * ---------------------------- */

    public function scopeWithCoords($q)
    {
        return $q->whereNotNull('latitude')->whereNotNull('longitude');
    }

    public function scopeInBounds($q, array $ne, array $sw)
    {
        return $q->withCoords()
                 ->whereBetween('latitude',  [min($sw['lat'], $ne['lat']), max($sw['lat'], $ne['lat'])])
                 ->whereBetween('longitude', [min($sw['lng'], $ne['lng']), max($sw['lng'], $ne['lng'])]);
    }

    protected static function geoColumnsExist(): bool
    {
        return Schema::hasColumns('reports', ['latitude', 'longitude']);
    }

    public function scopeWithDistance($q, float $lat, float $lng)
    {
        if (!self::geoColumnsExist()) return $q;

        $lat = max(-90, min(90, $lat));
        $lng = max(-180, min(180, $lng));

        $expr = "(
            6371 * acos(
              least(1.0, greatest(-1.0,
                cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) +
                sin(radians(?)) * sin(radians(latitude))
              ))
            )
        )";

        $table = $q->getModel()->getTable();

        return $q->select($table.'.*')
                 ->selectRaw("$expr as distance_km", [$lat, $lng, $lat]);
    }

    public function scopeWithinRadiusKm($q, float $lat, float $lng, float $radiusKm)
    {
        if (!self::geoColumnsExist()) return $q;
        return $q->withDistance($lat, $lng)->having('distance_km', '<=', $radiusKm);
    }

    public function scopeOrderByDistance($q, float $lat, float $lng)
    {
        if (!self::geoColumnsExist()) return $q->latest();

        $q->withDistance($lat, $lng);

        return $q->orderBy('distance_km')->orderByDesc('id');
    }

    public function scopePopular($q)
    {
        if (Schema::hasTable('endorsements') && Schema::hasColumn('endorsements', 'report_id')) {
            return $q->withCount('endorsements')->orderByDesc('endorsements_count');
        }
        return $q->latest();
    }

    public function scopeDiscussed($q)
    {
        if (Schema::hasTable('comments') && Schema::hasColumn('comments', 'report_id')) {
            return $q->withCount('comments')->orderByDesc('comments_count');
        }
        return $q->latest();
    }

    /* ----------------------------
     | Internals
     * ---------------------------- */

    /** Normalize any stored path to something like "reports/file.jpg". */
    protected function normalizeStoragePath(?string $path): ?string
    {
        if (!$path) return null;

        $path = str_replace('\\', '/', $path);         // windows → web separators
        $path = ltrim($path, '/');                     // drop leading slash
        $path = preg_replace('#^public/#', '', $path); // drop "public/"
        $path = preg_replace('#^storage/#', '', $path);// drop "storage/" if mistakenly saved
        return $path;
    }
    // app/Models/Report.php
public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }

}
