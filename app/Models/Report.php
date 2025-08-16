<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

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

    /* Optional color hints for pins/badges */
    public const STATUS_COLORS = [
        'pending'      => '#eab308', // amber-500
        'in_progress'  => '#3b82f6', // blue-500
        'resolved'     => '#16a34a', // green-600
        'rejected'     => '#ef4444', // red-500
    ];

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category',
        'city_corporation',
        'location',
        'status',
        'admin_note',
        'photo',                // storage path "reports/xxx.jpg"
        // Geo fields:
        'latitude',
        'longitude',
        'formatted_address',
        'place_id',
        'geocoded_at',
    ];

    protected $casts = [
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
        'geocoded_at'       => 'datetime',
        'latitude'          => 'float',
        'longitude'         => 'float',
    ];

    protected $appends = [
        'photo_url',
        'photo_name',
        'short_address',
        'has_coords',
        // Uncomment if you want thumbnails in lists:
        // 'static_map_url',
    ];

    /* ----------------------------
     | Relationships
     * ---------------------------- */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ReportNote::class)->latest();
    }

    /* ----------------------------
     | Accessors
     * ---------------------------- */
    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo) return null;
        return Storage::disk('public')->url($this->photo);
    }

    public function getPhotoNameAttribute(): ?string
    {
        return $this->photo ? basename($this->photo) : null;
    }

    public function getShortAddressAttribute(): ?string
    {
        if (!$this->formatted_address) return null;

        // Keep last 2–3 parts for a compact label
        $parts = array_map('trim', explode(',', $this->formatted_address));
        $take  = count($parts) >= 3 ? 3 : 2;
        return implode(', ', array_slice($parts, -$take));
    }

    public function getHasCoordsAttribute(): bool
    {
        return is_numeric($this->latitude) && is_numeric($this->longitude);
    }

    /**
     * Optional: small static map thumbnail for lists/cards.
     * Requires GOOGLE_MAPS_KEY in config('services.google_maps.key').
     */
    public function getStaticMapUrlAttribute(): ?string
    {
        if (!$this->has_coords) return null;
        $key = config('services.google_maps.key');
        if (!$key) return null;

        $lat = $this->latitude;
        $lng = $this->longitude;
        $marker = "color:red|{$lat},{$lng}";
        $params = http_build_query([
            'center'   => "{$lat},{$lng}",
            'zoom'     => 15,
            'size'     => '400x220',
            'scale'    => 2,
            'maptype'  => 'roadmap',
            'markers'  => $marker,
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

        $normalized = str_starts_with($value, 'public/')
            ? substr($value, 7)
            : $value;

        $this->attributes['photo'] = $normalized;
    }

    public function setStatusAttribute(?string $value): void
    {
        $v = $value ? strtolower(trim($value)) : null;
        $this->attributes['status'] = in_array($v, self::STATUSES, true) ? $v : $value;
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
            if ($report->photo && Storage::disk('public')->exists($report->photo)) {
                Storage::disk('public')->delete($report->photo);
            }
        });

        static::updating(function (Report $report) {
            if ($report->isDirty('photo')) {
                $original = $report->getOriginal('photo');
                if ($original && $original !== $report->photo && Storage::disk('public')->exists($original)) {
                    Storage::disk('public')->delete($original);
                }
            }
        });
    }

    /* ----------------------------
     | Query scopes (search/filters)
     * ---------------------------- */
    public function scopeSearch($q, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') return $q;

        $like = '%'.str_replace(['\\','%','_'], ['\\\\','\\%','\\_'], $term).'%';
        return $q->where(function ($x) use ($like) {
            $x->where('title',       'like', $like)
              ->orWhere('description','like', $like)
              ->orWhere('formatted_address','like', $like)
              ->orWhere('location',  'like', $like);
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

    /** Only rows that have valid coordinates */
    public function scopeWithCoords($q)
    {
        return $q->whereNotNull('latitude')->whereNotNull('longitude');
    }

    /**
     * Filter by map viewport bounds.
     * $ne = ['lat'=>..,'lng'=>..], $sw = ['lat'=>..,'lng'=>..]
     */
    public function scopeInBounds($q, array $ne, array $sw)
    {
        return $q->withCoords()
                 ->whereBetween('latitude',  [min($sw['lat'], $ne['lat']), max($sw['lat'], $ne['lat'])])
                 ->whereBetween('longitude', [min($sw['lng'], $ne['lng']), max($sw['lng'], $ne['lng'])]);
    }

    /**
     * Haversine distance (in KM). Returns distance as a selectable column if $asColumn provided.
     * NOTE: This uses MySQL trig functions; works on MariaDB/MySQL/Postgres with minor tweaks.
     */
    /* ----------------------------
 | Geo scopes (Haversine, km)
 * ---------------------------- */
public function scopeWithDistance($q, float $lat, float $lng)
{
    // Guard if columns don’t exist (keeps app resilient)
    if (!\Illuminate\Support\Facades\Schema::hasColumns($this->getTable(), ['latitude','longitude'])) {
        return $q;
    }

    $haversine = '(6371 * acos(least(1.0, greatest(-1.0, cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))))';
    return $q->select($this->getTable().'.*')
             ->selectRaw("$haversine as distance_km", [$lat, $lng, $lat]);
}

public function scopeWithinRadiusKm($q, float $lat, float $lng, float $radiusKm)
{
    // compute distance first, then filter
    return $q->withDistance($lat, $lng)->having('distance_km', '<=', $radiusKm);
}

public function scopeOrderByDistance($q, float $lat, float $lng)
{
    // ensure distance is present; if not, add it
    $q = $q->getQuery()->columns ? $q : $q->withDistance($lat, $lng);
    return $q->orderBy('distance_km');
}

    /* ----------------------------
     | Helpers
     * ---------------------------- */
    public function statusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? '#6b7280'; // gray-500 fallback
    }
    
}
