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

        // Sorting (now includes popular/discussed)
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

        // Main list query — ensure counts are loaded for every card
        $reports = Report::query()
            ->with('user:id,name')
            ->withCount(['likes', 'comments']);

        // Add user's like state for the heart (when supported)
        if (Auth::check() && method_exists($reports->getQuery(), 'withExists')) {
            $reports->withExists([
                'likes as liked_by_user' => fn ($q) => $q->where('user_id', Auth::id()),
            ]);
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

        // Sorting (including engagement-based)
        $reports = match ($sort) {
            'oldest'     => $reports->oldest(),
            'status'     => $reports->orderBy('status')->latest('id'),
            'city'       => $reports->orderBy('city_corporation')->latest('id'),
            'category'   => $reports->orderBy('category')->latest('id'),
            'popular'    => $reports->orderByDesc('likes_count')->latest('id'),
            'discussed'  => $reports->orderByDesc('comments_count')->latest('id'),
            'nearest'    => ($geoOk && $this->validPoint($nearLat, $nearLng))
                                ? $reports->orderByDistance($nearLat, $nearLng)
                                : $reports->latest(),
            default      => $reports->latest(),
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

    // Validate regular fields + accept either single "photo" or array "photos[]"
    $validated = $request->validate([
        'title'            => ['required', 'string', 'max:255'],
        'description'      => ['required', 'string'],
        'category'         => ['required', 'string'],
        'city_corporation' => ['required', 'string'],
        'location'         => ['required', 'string'],

        // Google Maps fields (optional)
        'latitude'          => ['nullable', 'numeric', 'between:-90,90'],
        'longitude'         => ['nullable', 'numeric', 'between:-180,180'],
        'place_id'          => ['nullable', 'string', 'max:128'],
        'formatted_address' => ['nullable', 'string', 'max:255'],

        // Media
        'photo'     => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:4096'],
        'photos'    => ['nullable', 'array'],
        'photos.*'  => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp,gif,mp4,mov,avi', 'max:10240'],
    ]);

    // Save ONE path into reports.photo:
    // 1) prefer single "photo" if present
    // 2) otherwise pick the first image from "photos[]", or fall back to the first file
    $photoPath = null;

    if ($request->hasFile('photo')) {
        $photoPath = $request->file('photo')->store('reports', 'public'); // => "reports/xxxx.png"
    } elseif ($request->hasFile('photos')) {
        $files  = array_values((array) $request->file('photos'));
        $chosen = collect($files)->first(function ($f) {
            return str_starts_with($f->getMimeType(), 'image/');
        }) ?? $files[0];

        if ($chosen) {
            $photoPath = $chosen->store('reports', 'public'); // => "reports/xxxx.png"
        }
    }

    if ($photoPath) {
        // IMPORTANT: store the STORAGE PATH, not just filename
        $validated['photo'] = $photoPath;
    }

    $validated['user_id'] = Auth::id();
    $validated['status']  = 'pending';
    $validated = $this->normalizeGeo($validated);

    $report = Report::create($validated);

    // If the request came from your fetch() (AJAX), return JSON so the front-end can redirect
    if ($request->ajax()) {
        return response()->json([
            'ok'       => true,
            'redirect' => route('reports.show', $report),
        ]);
    }

    return redirect()
        ->route('reports.show', $report)
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

        $reports = Report::with('user')
            ->withCount(['likes', 'comments'])
            ->where('user_id', $request->user()->id);

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

        // Load relationships including engagement data
        $report->load([
            'user',
            'likes' => fn ($q): mixed => $q->with('user:id,name')->latest(),
        ])->loadCount(['likes', 'comments']);

        // liked_by_user flag (when supported)
        if (Auth::check() && method_exists($report, 'loadExists')) {
            $report->loadExists([
                'likes as liked_by_user' => fn ($q) => $q->where('user_id', Auth::id()),
            ]);
        }

        // Optionally load notes (admin side-table)
        if (Schema::hasTable('report_notes')) {
            $report->loadMissing('notes.admin');
        }

        return view('reports.show', compact('report'));
    }

    public function adminShow(Report $report)
    {
        abort_unless(Auth::check() && Auth::user()->is_admin, 403);

        $report->load(['user'])->loadCount(['likes', 'comments']);

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
            // Fail-soft: keep user-provided fields; don't break submission
            // report($e);
        }

        return $data;
    }

    /* ----------------------------
     | Map markers JSON for client
     * ---------------------------- */
    public function mapData(Request $request)
    {
        // Same filters as index()
        $q        = trim((string) $request->query('q', ''));
        $city     = $request->query('city_corporation');
        $category = $request->query('category');
        $status   = $request->query('status');
        $nearLat  = $request->float('near_lat');
        $nearLng  = $request->float('near_lng');
        $radiusKm = (float) $request->query('radius_km', 0);

        $like = fn(string $s) => '%' . str_replace(['\\','%','_'], ['\\\\','\\%','\\_'], $s) . '%';

        $qbuilder = Report::query()
            ->with('user:id,name')
            ->withCount(['likes', 'comments'])
            ->whereNotNull('latitude')->whereNotNull('longitude')
            ->when($q !== '', function ($qb) use ($q, $like) {
                $qb->where(function ($x) use ($q, $like) {
                    $x->where('title','like',$like($q))
                      ->orWhere('description','like',$like($q))
                      ->orWhere('formatted_address','like',$like($q))
                      ->orWhere('location','like',$like($q));
                });
            })
            ->when($city, fn($qb,$v) => $qb->where('city_corporation',$v))
            ->when($category, fn($qb,$v) => $qb->where('category',$v))
            ->when($status, fn($qb,$v) => $qb->where('status',$v));

        if ($this->validPoint($nearLat, $nearLng)) {
            $qbuilder = $radiusKm > 0
                ? $qbuilder->withinRadiusKm($nearLat, $nearLng, $radiusKm)
                : $qbuilder->withDistance($nearLat, $nearLng);
        }

        $items = $qbuilder->latest('id')->limit(500)->get([
            'id','title','status','category','latitude','longitude','formatted_address','created_at','likes_count','comments_count'
        ]);

        return response()->json([
            'count' => $items->count(),
            'items' => $items->map(fn($r) => [
                'id'             => $r->id,
                'title'          => $r->title,
                'status'         => $r->status,
                'category'       => $r->category,
                'lat'            => (float) $r->latitude,
                'lng'            => (float) $r->longitude,
                'address'        => $r->formatted_address,
                'created'        => optional($r->created_at)->toIso8601String(),
                'likes_count'    => $r->likes_count,
                'comments_count' => $r->comments_count,
                'url'            => route('reports.show', $r),
            ]),
        ]);
    }
}
