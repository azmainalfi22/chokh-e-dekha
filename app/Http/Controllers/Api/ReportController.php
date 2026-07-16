<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q        = trim((string) $request->query('q', ''));
        $city     = $request->query('city_corporation');
        $category = $request->query('category');
        $status   = $request->query('status');
        $perPage  = max(6, min(48, (int) $request->integer('per_page', 12)));

        $query = Report::query()
            ->with('user:id,name')
            ->withCount(['likes','comments'])
            ->when($q !== '', fn($qb) => $qb->search($q))
            ->when($city, fn($qb,$v) => $qb->city($v))
            ->when($category, fn($qb,$v) => $qb->category($v))
            ->when($status, fn($qb,$v) => $qb->status($v))
            ->latest();

        return response()->json($query->paginate($perPage));
    }

    public function show(Report $report): JsonResponse
    {
        $report->load(['user:id,name'])->loadCount(['likes','comments']);
        return response()->json($report);
    }

    public function mapData(Request $request): JsonResponse
    {
        $q        = trim((string) $request->query('q', ''));
        $city     = $request->query('city_corporation');
        $category = $request->query('category');
        $status   = $request->query('status');
        $nearLat  = $request->float('near_lat');
        $nearLng  = $request->float('near_lng');
        $radiusKm = (float) $request->query('radius_km', 0);

        $qb = Report::query()
            ->withCoords()
            ->withCount(['likes','comments'])
            ->when($q !== '', fn($qb) => $qb->search($q))
            ->when($city, fn($qb,$v) => $qb->city($v))
            ->when($category, fn($qb,$v) => $qb->category($v))
            ->when($status, fn($qb,$v) => $qb->status($v));

        if (is_numeric($nearLat) && is_numeric($nearLng)) {
            $qb = $radiusKm > 0
                ? $qb->withinRadiusKm($nearLat, $nearLng, $radiusKm)
                : $qb->withDistance($nearLat, $nearLng);
        }

        $items = $qb->latest('id')->limit(1000)->get([
            'id','title','status','category','latitude','longitude','formatted_address','created_at','likes_count','comments_count'
        ]);

        return response()->json([
            'count' => $items->count(),
            'items' => $items,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        return $this->index($request);
    }

    public function categories(): JsonResponse
    {
        $categories = Report::query()
            ->whereNotNull('category')
            ->distinct()->orderBy('category')->pluck('category');
        return response()->json(['categories' => $categories]);
    }

    public function cities(): JsonResponse
    {
        $cities = Report::query()
            ->whereNotNull('city_corporation')
            ->distinct()->orderBy('city_corporation')->pluck('city_corporation');
        return response()->json(['cities' => $cities]);
    }

    // Protected endpoints
    public function myReports(Request $request): JsonResponse
    {
        $perPage = max(6, min(48, (int) $request->integer('per_page', 12)));
        $query = Report::where('user_id', $request->user()->id)
            ->withCount(['likes','comments'])
            ->latest();
        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required','string','max:255'],
            'description' => ['required','string'],
            'category' => ['required','string'],
            'city_corporation' => ['required','string'],
            'location' => ['nullable','string'],
            'latitude' => ['nullable','numeric','between:-90,90'],
            'longitude' => ['nullable','numeric','between:-180,180'],
            'place_id' => ['nullable','string','max:128'],
            'formatted_address' => ['nullable','string','max:255'],
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['status']  = 'pending';

        $report = Report::create($validated);
        return response()->json($report, 201);
    }

    public function update(Request $request, Report $report): JsonResponse
    {
        abort_unless($request->user()->id === $report->user_id || ($request->user()->is_admin ?? false), 403);

        $validated = $request->validate([
            'title' => ['sometimes','string','max:255'],
            'description' => ['sometimes','string'],
            'category' => ['sometimes','string'],
            'city_corporation' => ['sometimes','string'],
            'location' => ['sometimes','string','nullable'],
            'status' => ['sometimes','in:pending,in_progress,resolved,rejected'],
        ]);

        $report->update($validated);
        return response()->json($report->fresh()->loadCount(['likes','comments']));
    }

    public function destroy(Request $request, Report $report): JsonResponse
    {
        abort_unless($request->user()->id === $report->user_id || ($request->user()->is_admin ?? false), 403);
        $report->delete();
        return response()->json(['deleted' => true]);
    }

    // Admin endpoints (minimal implementations)
    public function adminIndex(Request $request): JsonResponse
    {
        abort_unless($request->user()->is_admin ?? false, 403);
        return $this->index($request);
    }

    public function adminStats(Request $request): JsonResponse
    {
        abort_unless($request->user()->is_admin ?? false, 403);
        $counts = Report::selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c','status');
        return response()->json(['by_status' => $counts]);
    }

    public function export(): JsonResponse
    {
        $rows = Report::with('user:id,name,email')->latest('id')->limit(2000)->get();
        return response()->json($rows);
    }

    public function approve(Request $request, Report $report): JsonResponse
    {
        abort_unless($request->user()->is_admin ?? false, 403);
        $report->update(['status' => 'in_progress', 'status_updated_at' => now()]);
        return response()->json(['status' => $report->status]);
    }

    public function reject(Request $request, Report $report): JsonResponse
    {
        abort_unless($request->user()->is_admin ?? false, 403);
        $report->delete();
        return response()->json(['deleted' => true]);
    }

    public function updateStatus(Request $request, Report $report): JsonResponse
    {
        abort_unless($request->user()->is_admin ?? false, 403);
        $data = $request->validate(['status' => ['required','in:pending,in_progress,resolved,rejected']]);
        $report->update(['status' => $data['status'], 'status_updated_at' => now()]);
        return response()->json(['status' => $report->status]);
    }

    public function assignToMe(Request $request, Report $report): JsonResponse
    {
        abort_unless($request->user()->is_admin ?? false, 403);
        $report->update(['assigned_to' => $request->user()->id, 'assigned_at' => now()]);
        return response()->json(['assigned_to' => $report->assigned_to]);
    }

    public function getNotes(Request $request, Report $report): JsonResponse
    {
        abort_unless($request->user()->is_admin ?? false, 403);
        $report->loadMissing('notes.admin');
        return response()->json($report->notes);
    }

    public function storeNote(Request $request, Report $report): JsonResponse
    {
        abort_unless($request->user()->is_admin ?? false, 403);
        $data = $request->validate(['body' => ['required','string','max:5000']]);
        $note = \App\Models\ReportNote::create([
            'report_id' => $report->id,
            'admin_id' => $request->user()->id,
            'body' => $data['body'],
        ]);
        return response()->json($note, 201);
    }

    public function destroyNote(Request $request, Report $report, \App\Models\ReportNote $note): JsonResponse
    {
        abort_unless($request->user()->is_admin ?? false, 403);
        abort_unless($note->report_id === $report->id, 404);
        $note->delete();
        return response()->json(['deleted' => true]);
    }

    public function bulkAction(Request $request): JsonResponse
    {
        abort_unless($request->user()->is_admin ?? false, 403);
        return response()->json(['message' => 'Bulk action not implemented'], 501);
    }

    public function bulkAssign(Request $request): JsonResponse
    {
        abort_unless($request->user()->is_admin ?? false, 403);
        return response()->json(['message' => 'Bulk assign not implemented'], 501);
    }

    public function bulkStatus(Request $request): JsonResponse
    {
        abort_unless($request->user()->is_admin ?? false, 403);
        return response()->json(['message' => 'Bulk status not implemented'], 501);
    }

    public function dashboardStats(): JsonResponse
    {
        $total = Report::count();
        $resolved = Report::where('status','resolved')->count();
        $pending = Report::where('status','pending')->count();
        return response()->json(compact('total','resolved','pending'));
    }

    public function myReportsSummary(Request $request): JsonResponse
    {
        $uid = $request->user()->id;
        $total = Report::where('user_id',$uid)->count();
        $resolved = Report::where('user_id',$uid)->where('status','resolved')->count();
        return response()->json(compact('total','resolved'));
    }

    public function heatmap(Request $request): JsonResponse
    {
        $q        = trim((string) $request->query('q', ''));
        $city     = $request->query('city_corporation');
        $category = $request->query('category');
        $status   = $request->query('status');

        $points = Report::query()
            ->withCoords()
            ->when($q !== '', fn($qb) => $qb->search($q))
            ->when($city, fn($qb,$v) => $qb->city($v))
            ->when($category, fn($qb,$v) => $qb->category($v))
            ->when($status, fn($qb,$v) => $qb->status($v))
            ->latest('id')
            ->limit(5000)
            ->get(['latitude','longitude']);

        return response()->json([
            'points' => $points->map(fn($r) => ['lat' => (float) $r->latitude, 'lng' => (float) $r->longitude])
        ]);
    }
}


