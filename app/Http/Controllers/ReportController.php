<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ReportController extends Controller
{
    /* ----------------------------
     | Lightweight schema guards
     * ---------------------------- */
    protected function hasGeoColumns(): bool
    {
        return Schema::hasColumns('reports', ['latitude', 'longitude']);
    }

    protected function hasComments(): bool
    {
        return Schema::hasTable('comments');
    }

    protected function hasEndorsements(): bool
    {
        return Schema::hasTable('endorsements');
    }

    protected function hasRatings(): bool
    {
        return Schema::hasTable('ratings') && Schema::hasColumn('ratings', 'score');
    }

    protected function validPoint($lat, $lng): bool
    {
        return is_numeric($lat) && is_numeric($lng)
            && $lat >= -90 && $lat <= 90
            && $lng >= -180 && $lng <= 180;
    }

    /* ----------------------------
     | Public listing
     * ---------------------------- */
    public function index(Request $request)
    {
        $q        = trim((string) $request->query('q', ''));
        $city     = $request->query('city_corporation');
        $category = $request->query('category');
        $status   = $request->query('status');
        $perPage  = max(6, min(48, (int) $request->integer('per_page', 12)));

        // Optional geo filters (enabled only if columns exist)
        $geoOk    = $this->hasGeoColumns();
        $nearLat  = $geoOk ? $request->float('near_lat') : null;
        $nearLng  = $geoOk ? $request->float('near_lng') : null;
        $radiusKm = $geoOk ? (float) $request->query('radius_km', 0) : 0;

        // Sorting
        $sortAllow = ['newest', 'oldest', 'status', 'city', 'category', 'nearest', 'popular', 'discussed'];
        $sort      = $request->query('sort', ($geoOk && $this->validPoint($nearLat, $nearLng)) ? 'nearest' : 'newest');
        if (!in_array($sort, $sortAllow, true)) $sort = 'newest';
        if ($sort === 'nearest' && !$geoOk)     $sort = 'newest';

        // Allowed statuses
        $statuses = ['pending', 'in_progress', 'resolved', 'rejected'];
        if ($status && !in_array($status, $statuses, true)) {
            $status = null;
        }

        // Dropdown sources
        $cities = Report::query()
            ->whereNotNull('city_corporation')
            ->distinct()
            ->orderBy('city_corporation')
            ->pluck('city_corporation');

        $categories = Report::query()
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $like = fn(string $s) => '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $s) . '%';

        $reports = Report::query()->with('user:id,name');

        // Optional aggregates (don’t break if tables don’t exist)
        if ($this->hasComments()) {
            $reports->withCount('comments');
        }
        if ($this->hasEndorsements()) {
            $reports->withCount('endorsements');
        }
        if ($this->hasRatings()) {
            $reports->withCount('ratings')->withAvg('ratings', 'score'); // ratings_count, ratings_avg_score
        }

        // Text/field filters
        $reports->when($q !== '', function ($qb) use ($q, $like) {
                $qb->where(function ($x) use ($q, $like) {
                    $x->where('title', 'like', $like($q))
                      ->orWhere('description', 'like', $like($q))
                      ->orWhere('formatted_address', 'like', $like($q))
                      ->orWhere('location', 'like', $like($q));
                });
            })
            ->when($city, fn ($qb, $v) => $qb->where('city_corporation', $v))
            ->when($category, fn ($qb, $v) => $qb->where('category', $v))
            ->when($status, fn ($qb, $v) => $qb->where('status', $v));

        // Geo filtering / distance (only if geo columns exist)
        if ($geoOk && $this->validPoint($nearLat, $nearLng)) {
            if ($radiusKm > 0) {
                $reports = $reports->withinRadiusKm($nearLat, $nearLng, $radiusKm);
            } else {
                $reports = $reports->withDistance($nearLat, $nearLng);
            }
        }

        // Sorting
        $reports = match ($sort) {
            'oldest'    => $reports->oldest(), // created_at asc
            'status'    => $reports->orderBy('status')->latest('id'),
            'city'      => $reports->orderBy('city_corporation')->latest('id'),
            'category'  => $reports->orderBy('category')->latest('id'),
            'nearest'   => ($geoOk && $this->validPoint($nearLat, $nearLng))
                               ? $reports->orderByDistance($nearLat, $nearLng)
                               : $reports->latest(),
            'popular'   => ($this->hasEndorsements() ? $reports->orderByDesc('endorsements_count') : $reports)
                               ->when($this->hasComments(), fn($q)=>$q->orderByDesc('comments_count'))
                               ->when($this->hasRatings(),  fn($q)=>$q->orderByDesc('ratings_count'))
                               ->latest('id'),
            'discussed' => $this->hasComments()
                               ? $reports->orderByDesc('comments_count')->latest('id')
                               : $reports->latest(),
            default     => $reports->latest(), // newest first
        };

        $reports = $reports->paginate($perPage)->withQueryString();

        // For Blade compatibility
        $cat = $category;

        return view('reports.index', compact(
            'reports', 'q', 'city', 'category', 'cat',
            'cities', 'categories', 'status', 'statuses', 'sort',
            'nearLat', 'nearLng', 'radiusKm'
        ));
    }

    /* ----------------------------
     | Create + Store
     * ---------------------------- */
    public function create()
    {
        if (Auth::user()?->is_admin) {
            abort(403, 'Admins cannot submit reports.');
        }

        return view('reports.create', [
            'googleApiKey' => config('services.google_maps.key'),
        ]);
    }

    public function store(Request $request)
    {
        if (Auth::user()?->is_admin) {
            abort(403, 'Admins cannot submit reports.');
        }

        $validated = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['required', 'string'],
            'category'         => ['required', 'string'],
            'city_corporation' => ['required', 'string'],
            'location'         => ['required', 'string'],

            // Google Maps fields (from the map form)
            'latitude'          => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'         => ['nullable', 'numeric', 'between:-180,180'],
            'place_id'          => ['nullable', 'string', 'max:128'],
            'formatted_address' => ['nullable', 'string', 'max:255'],

            'photo'             => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:4096'],
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('reports', 'public');
        }

        $validated['user_id'] = Auth::id();
        $validated['status']  = 'pending';
        $validated = $this->normalizeGeo($validated);

        Report::create($validated);

        return redirect()
            ->route('reports.index')
            ->with('success', 'Report submitted successfully!');
    }

    /* ----------------------------
     | My reports
     * ---------------------------- */
    public function myReports(Request $request)
    {
        $statuses = ['pending', 'in_progress', 'resolved', 'rejected'];
        $q        = trim((string) $request->query('q', ''));
        $city     = $request->query('city_corporation');
        $category = $request->query('category');
        $status   = $request->query('status');
        if ($status && !in_array($status, $statuses, true)) $status = null;

        $like = fn($s) => '%'.str_replace(['\\','%','_'], ['\\\\','\\%','\\_'], $s).'%';

        $reports = Report::with('user')->where('user_id', $request->user()->id);

        if ($this->hasComments())     $reports->withCount('comments');
        if ($this->hasEndorsements()) $reports->withCount('endorsements');
        if ($this->hasRatings())      $reports->withCount('ratings')->withAvg('ratings', 'score');

        $reports = $reports
            ->when($q !== '', fn($qrb) => $qrb->where(function ($x) use ($q, $like) {
                $x->where('title', 'like', $like($q))
                  ->orWhere('description', 'like', $like($q))
                  ->orWhere('formatted_address', 'like', $like($q))
                  ->orWhere('location', 'like', $like($q));
            }))
            ->when($city,     fn($qrb, $v) => $qrb->where('city_corporation', $v))
            ->when($category, fn($qrb, $v) => $qrb->where('category', $v))
            ->when($status,   fn($qrb, $v) => $qrb->where('status', $v))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $cities = Report::where('user_id', $request->user()->id)
            ->whereNotNull('city_corporation')
            ->distinct()
            ->orderBy('city_corporation')
            ->pluck('city_corporation');

        $categories = Report::where('user_id', $request->user()->id)
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('reports.my', compact('reports', 'cities', 'categories'));
    }

    /* ----------------------------
     | Show (public) + Admin Show
     * ---------------------------- */
    public function show(Report $report, Request $request)
    {
        abort_unless(auth()->check(), 403);

        // Always load author
        $report->load('user');

        // Optionally load notes (admin side-table)
        if (Schema::hasTable('report_notes')) {
            // if you have a notes->admin relation, this will eager-load it
            $report->loadMissing('notes.admin');
        }

        // Optional comments
        $comments = null;
        if ($this->hasComments()) {
            $comments = $report->comments()
                ->with('user:id,name')
                ->latest()
                ->paginate(10)
                ->withQueryString();
        }

        return view('reports.show', compact('report', 'comments'));
    }

    public function adminShow(Report $report)
    {
        abort_unless(Auth::check() && Auth::user()->is_admin, 403);
        $report->load('user');

        return view('admin.reports.show', compact('report'));
    }

    /* ----------------------------
     | Admin update/toggle
     * ---------------------------- */
    public function update(Request $request, Report $report)
    {
        abort_unless(Auth::check() && Auth::user()->is_admin, 403);

        $validated = $request->validate([
            'status'     => ['required', 'in:pending,in_progress,resolved,rejected'],
            'admin_note' => ['nullable', 'string', 'max:5000'],

            // Allow admins to correct location if needed (optional)
            'latitude'          => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'         => ['nullable', 'numeric', 'between:-180,180'],
            'place_id'          => ['nullable', 'string', 'max:128'],
            'formatted_address' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['status'] = strtolower(trim($validated['status']));

        // If any geo field provided, re-normalize
        if ($request->filled('latitude') || $request->filled('longitude') || $request->filled('place_id')) {
            $validated = $this->normalizeGeo($validated);
        }

        $report->update($validated);

        return back()->with('success', 'Report updated successfully.');
    }

    public function toggleStatus(Report $report)
    {
        abort_unless(Auth::check() && Auth::user()->is_admin, 403);

        $report->status = $report->status === 'pending' ? 'resolved' : 'pending';
        $report->save();

        return back()->with('success', 'Report status updated!');
    }

    /* ----------------------------
     | Geolocation normalization
     * ---------------------------- */
    /**
     * Normalize geolocation fields.
     * - If place_id is present, try Place Details.
     * - Else if lat/lng are present, try Reverse Geocode.
     * - If service class is missing, trust client fields.
     */
    protected function normalizeGeo(array $data): array
    {
        $hasGeocodedAt = Schema::hasColumn('reports', 'geocoded_at');

        $setIf = function (&$arr, string $key, $value) {
            if ($value !== null && $value !== '') {
                $arr[$key] = $value;
            }
        };

        // Normalize basic types from client
        if (array_key_exists('latitude', $data)) {
            $lat = is_numeric($data['latitude']) ? max(-90, min(90, (float) $data['latitude'])) : null;
            $setIf($data, 'latitude', $lat !== null ? round($lat, 7) : null);
        }
        if (array_key_exists('longitude', $data)) {
            $lng = is_numeric($data['longitude']) ? max(-180, min(180, (float) $data['longitude'])) : null;
            $setIf($data, 'longitude', $lng !== null ? round($lng, 7) : null);
        }
        if (array_key_exists('place_id', $data)) {
            $setIf($data, 'place_id', trim((string) $data['place_id']));
        }
        if (array_key_exists('formatted_address', $data)) {
            $setIf($data, 'formatted_address', trim((string) $data['formatted_address']));
        }

        $serviceClass = 'App\\Services\\GoogleMapsService';

        // No service available: only stamp geocoded_at if we have coords and column exists
        if (!class_exists($serviceClass)) {
            if (!empty($data['latitude']) && !empty($data['longitude']) && $hasGeocodedAt) {
                $data['geocoded_at'] = now();
            }
            return $data;
        }

        /** @var \App\Services\GoogleMapsService $maps */
        $maps = app($serviceClass);

        try {
            // Prefer place_id → details
            if (!empty($data['place_id'])) {
                $d = $maps->placeDetails($data['place_id']) ?? [];

                $setIf($data, 'latitude',  isset($d['lat']) ? round((float) $d['lat'], 7) : null);
                $setIf($data, 'longitude', isset($d['lng']) ? round((float) $d['lng'], 7) : null);
                $setIf($data, 'formatted_address', $d['formatted_address'] ?? null);

                if ($hasGeocodedAt) $data['geocoded_at'] = now();
            }
            // Else if we have coords → reverse geocode
            elseif (!empty($data['latitude']) && !empty($data['longitude'])) {
                $lat = (float) $data['latitude'];
                $lng = (float) $data['longitude'];

                $d = $maps->reverseGeocode($lat, $lng) ?? [];

                $setIf($data, 'formatted_address', $d['formatted_address'] ?? null);
                $setIf($data, 'place_id', $d['place_id'] ?? null);

                if ($hasGeocodedAt) $data['geocoded_at'] = now();
            }
        } catch (\Throwable $e) {
            // Fail-soft: keep user-provided fields; don’t break submission
            // report($e);
        }

        return $data;
    }
    public function mapData(Request $request)
{
    // Reuse the same filter logic as index()
    $q        = trim((string) $request->query('q', ''));
    $city     = $request->query('city_corporation');
    $category = $request->query('category');
    $status   = $request->query('status');

    $like = fn(string $s) => '%' . str_replace(['\\','%','_'], ['\\\\','\\%','\\_'], $s) . '%';

    $query = \App\Models\Report::query()
        ->withCoords()
        ->when($q !== '', function ($qb) use ($q, $like) {
            $qb->where(function ($x) use ($q, $like) {
                $x->where('title', 'like', $like($q))
                  ->orWhere('description', 'like', $like($q))
                  ->orWhere('formatted_address', 'like', $like($q))
                  ->orWhere('location', 'like', $like($q));
            });
        })
        ->when($city, fn ($qb, $v) => $qb->where('city_corporation', $v))
        ->when($category, fn ($qb, $v) => $qb->where('category', $v))
        ->when($status, fn ($qb, $v) => $qb->where('status', $v))
        ->latest('id')
        ->limit(500);

    $items = $query->get(['id','title','status','category','latitude','longitude','formatted_address']);

    return response()->json([
        'ok'    => true,
        'items' => $items->map(function ($r) {
            return [
                'id'       => $r->id,
                'title'    => $r->title,
                'status'   => $r->status,
                'category' => $r->category,
                'lat'      => (float) $r->latitude,
                'lng'      => (float) $r->longitude,
                'address'  => $r->formatted_address,
                'url'      => route('reports.show', $r),
            ];
        }),
    ]);
}

}
