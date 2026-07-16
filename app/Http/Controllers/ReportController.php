<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Http\Requests\StoreReportRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Services\TrendingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

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

        // Add user's like state for the heart
        if (Auth::check()) {
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

    public function store(StoreReportRequest $request)
{
    // ✅ Authorization and validation handled by StoreReportRequest
    $validated = $request->validated();

    // 🎯 NEW: Handle multiple photo uploads properly
    $photoPath = null;
    $additionalPhotos = [];

    if ($request->hasFile('photo')) {
        // Single photo upload (legacy support)
        $photoPath = $request->file('photo')->store('reports', 'public');
    } elseif ($request->hasFile('photos')) {
        // Multiple photos upload (new feature)
        $files = array_values((array) $request->file('photos'));
        
        foreach ($files as $index => $file) {
            if ($index === 0) {
                // First photo becomes the main photo
                $photoPath = $file->store('reports', 'public');
            } else {
                // Additional photos stored separately
                $additionalPhotos[] = [
                    'file_path' => $file->store('reports', 'public'),
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'file_name' => $file->getClientOriginalName(),
                ];
            }
        }
    }

    if ($photoPath) {
        $validated['photo'] = $photoPath;
    }

    $validated['user_id'] = Auth::id();
    $validated['status']  = 'pending';
    $validated = $this->normalizeGeo($validated);

    // ✅ Wrap in try-catch for better error handling
    try {
        $report = Report::create($validated);
        
        // 🎯 NEW: Store additional photos in report_media table
        foreach ($additionalPhotos as $photoData) {
            $report->media()->create($photoData);
        }
        
        // ✅ Logging handled by ReportObserver

        // If the request came from your fetch() (AJAX), return JSON
        if ($request->ajax()) {
            return response()->json([
                'ok' => true,
                'success' => true,
                'redirect' => route('reports.show', $report),
                'message' => __('app.report_submitted'),
                'report_id' => $report->id,
                'photos_count' => count($additionalPhotos) + ($photoPath ? 1 : 0),
            ]);
        }

        return redirect()
            ->route('reports.show', $report)
            ->with('success', __('app.report_submitted'));

    } catch (\Exception $e) {
        Log::error('Report creation failed', [
            'user_id' => Auth::id(),
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'ok' => false,
                'success' => false,
                'error' => __('app.error_occurred'),
            ], 500);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => __('app.error_occurred')]);
    }
}

    /* ----------------------------
     | My reports
     * ---------------------------- */
    /**
     * Track share action
     */
    public function trackShare(Report $report)
    {
        try {
            \DB::table('reports')
                ->where('id', $report->id)
                ->increment('shares_count');
                
            return response()->json([
                'success' => true,
                'shares_count' => $report->fresh()->shares_count,
            ]);
        } catch (\Exception $e) {
            \Log::error('Share tracking failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to track share',
            ], 500);
        }
    }

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
    public function show(Report $report, Request $request, TrendingService $trendingService)
    {
        abort_unless(auth()->check(), 403);

        // Track view (increment atomically)
        $trendingService->incrementViews($report);

        // Load relationships including engagement data
        $report->load([
            'user',
            'likes' => fn ($q): mixed => $q->with('user:id,name')->latest(),
            'logs' => fn ($q) => $q->with('admin:id,name')->latest('created_at'),
        ])->loadCount(['likes', 'comments']);

        // liked_by_user flag
        if (Auth::check()) {
            $report->loadExists([
                'likes as liked_by_user' => fn ($q) => $q->where('user_id', Auth::id()),
            ]);
        }

        // Get related reports
        $relatedReports = $trendingService->getRelatedReports($report, 5);

        // Optionally load notes (admin side-table)
        if (Schema::hasTable('report_notes')) {
            $report->loadMissing('notes.admin');
        }

        return view('reports.show', compact('report', 'relatedReports'));
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
    public function update(UpdateReportRequest $request, Report $report)
    {
        // ✅ Authorization handled by UpdateReportRequest
        $validated = $request->validated();

        $validated['status'] = strtolower(trim($validated['status']));

        // If any geo field provided, re-normalize
        if ($request->filled('latitude') || $request->filled('longitude') || $request->filled('place_id')) {
            $validated = $this->normalizeGeo($validated);
        }

        try {
            $report->update($validated);
            // ✅ Status change logging handled by ReportObserver

            return back()->with('success', __('app.updated'));

        } catch (\Exception $e) {
            Log::error('Report update failed', [
                'report_id' => $report->id,
                'error' => $e->getMessage(),
                'admin_id' => Auth::id(),
            ]);

            return back()->withErrors(['error' => __('app.error_occurred')]);
        }
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
