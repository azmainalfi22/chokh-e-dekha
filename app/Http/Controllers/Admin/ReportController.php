<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\ReportNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Notifications\ReportStatusNotification;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private function notifyReportAuthor(Report $report, string $status): void
    {
        // Guard: report may have no user (seeded/anon)
        if ($report->relationLoaded('user') === false) {
            $report->loadMissing('user');
        }
    if ($report->user) {
        $report->user->notify(new ReportStatusNotification($status, $report));
    }
}

    /** All statuses used in UI */
    private const STATUSES = ['pending', 'in_progress', 'resolved', 'rejected'];

    /** GET /admin/reports */
    public function index(Request $request)
    {
        // Gather filters from request
        $q        = trim((string) $request->query('q', ''));
        $city     = $request->query('city');
        $category = $request->query('category');
        $status   = $request->query('status');
        $from     = $request->query('from');
        $to       = $request->query('to');
        $sort     = $request->query('sort', 'newest');
        $perPage  = max(6, min(60, (int) $request->integer('per_page', 18)));

        // sanitize status
        if ($status && !in_array($status, self::STATUSES, true)) {
            $status = null;
        }

        // dropdown sources
        $cities = Report::query()
            ->whereNotNull('city_corporation')
            ->distinct()->orderBy('city_corporation')->pluck('city_corporation');

        $categories = Report::query()
            ->whereNotNull('category')
            ->distinct()->orderBy('category')->pluck('category');

        // safe LIKE builder
        $like = fn(string $s) => '%' . str_replace(['\\','%','_'], ['\\\\','\\%','\\_'], $s) . '%';

        // Start query
        $query = Report::query()->with(['user:id,name,email']);

        // Apply filters
        if ($q !== '') {
            $query->where(function ($w) use ($q, $like) {
                $w->where('title', 'like', $like($q))
                  ->orWhere('description', 'like', $like($q))
                  ->orWhere('id', $q)
                  ->orWhereHas('user', fn($uq) =>
                      $uq->where('name', 'like', $like($q))
                         ->orWhere('email', 'like', $like($q))
                  );
            });
        }
        if ($city)     { $query->where('city_corporation', $city); }
        if ($category) { $query->where('category', $category); }
        if ($status)   { $query->where('status', $status); }
        if ($from)     { $query->whereDate('created_at', '>=', $from); }
        if ($to)       { $query->whereDate('created_at', '<=', $to); }

        // Priority filter (age-based like your Blade)
        if ($level = $request->get('priority')) {
            $query->where(function ($q2) use ($level) {
                if ($level === 'high') {
                    $q2->where(function ($x) {
                        $x->where('status', 'pending')
                          ->whereRaw('TIMESTAMPDIFF(DAY, created_at, NOW()) > 3');
                    })->orWhereRaw('TIMESTAMPDIFF(DAY, created_at, NOW()) > 7');
                } elseif ($level === 'medium') {
                    $q2->whereRaw('TIMESTAMPDIFF(DAY, created_at, NOW()) BETWEEN 4 AND 7');
                } elseif ($level === 'low') {
                    $q2->whereRaw('TIMESTAMPDIFF(DAY, created_at, NOW()) <= 3');
                }
            });
        }

        // Sorting
        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'status':
                $query->orderBy('status')->orderByDesc('created_at');
                break;
            case 'city':
                $query->orderBy('city_corporation')->orderByDesc('created_at');
                break;
            case 'category':
                $query->orderBy('category')->orderByDesc('created_at');
                break;
            case 'updated':
                $query->orderByDesc('updated_at');
                break;
            case 'priority':
                $query->orderByRaw("
                    CASE
                        WHEN status='pending' THEN 0
                        WHEN status='in_progress' THEN 1
                        WHEN status='resolved' THEN 2
                        ELSE 3
                    END
                ")->orderByDesc('created_at');
                break;
            default:
                $query->orderByDesc('created_at');
        }

        // CSV Export (button sets ?export=csv)
        if ($request->get('export') === 'csv') {
            $rows = (clone $query)->orderBy('id','desc')->get();

            return new StreamedResponse(function() use ($rows) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['ID','Title','User','Email','City','Category','Status','Created','URL']);
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->id,
                        $r->title,
                        optional($r->user)->name,
                        optional($r->user)->email,
                        $r->city_corporation,
                        $r->category,
                        $r->status,
                        optional($r->created_at)?->toDateTimeString(),
                        route('admin.reports.show', $r),
                    ]);
                }
                fclose($out);
            }, 200, [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename="reports_export_'.now()->toDateString().'.csv"',
            ]);
        }

        // stats for header (real counts, not just current page)
        $statusCounts = Report::query()
            ->selectRaw('status, COUNT(*) c')
            ->groupBy('status')
            ->pluck('c','status');

        $reports = $query->paginate($perPage)->withQueryString();

        return view('admin.reports.index', [
            'reports'       => $reports,
            'cities'        => $cities,
            'categories'    => $categories,
            'statuses'      => self::STATUSES,
            'statusCounts'  => $statusCounts,
        ]);
    }

    /** GET /admin/reports/{report} */
    public function show(Report $report)
    {
        // only eager-load notes if the table exists (fresh installs)
        if (Schema::hasTable('report_notes')) {
            $report->load(['user', 'notes.admin']);
        } else {
            $report->load(['user']);
        }

        return view('admin.reports.show', compact('report'));
    }
public function approve(Request $request, Report $report)
{
    $report->update([
        'status' => 'in_progress',
        'status_updated_at' => now(),
    ]);

    $this->notifyReportAuthor($report, 'approved');

    if ($request->expectsJson()) {
        return response()->json(['ok' => true, 'id' => $report->id, 'status' => $report->status]);
    }
    return back()->with('success', 'Report approved.');
}

public function reject(Request $request, Report $report)
{
    // notify before delete (so notification can reference title/id)
    $this->notifyReportAuthor($report, 'rejected');

    $report->delete();

    if ($request->expectsJson()) {
        return response()->json(['ok' => true, 'deleted' => true, 'id' => $report->id]);
    }
    return back()->with('success', 'Report rejected and deleted.');
}

    /** PUT/PATCH /admin/reports/{report}/status (used by named route admin.reports.status) */
/** PUT/PATCH /admin/reports/{report}/status (used by named route admin.reports.status) */
public function updateStatus(Request $request, Report $report)
{
    $validated = $request->validate([
        'status' => ['required','string', Rule::in(self::STATUSES)],
    ]);

    $newStatus = strtolower(trim($validated['status']));

    // If rejected via this endpoint, notify then delete
    if ($newStatus === 'rejected') {
        $this->notifyReportAuthor($report, 'rejected');
        $report->delete();

        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json(['ok' => true, 'deleted' => true, 'id' => $report->id]);
        }
        return to_route('admin.reports.index')->with('success', 'Report rejected and deleted.');
    }

    $report->update([
        'status' => $newStatus,
        'status_updated_at' => now(),
    ]);

    // notify for other statuses (optional; keep if you want users to know every change)
    $this->notifyReportAuthor($report, $newStatus);

    if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
        return response()->json([
            'ok'     => true,
            'id'     => $report->id,
            'status' => $report->status,
            'label'  => \Illuminate\Support\Str::headline($report->status),
        ]);
    }

    return to_route('admin.reports.show', $report)
        ->with('success', 'Status updated to '.\Illuminate\Support\Str::headline($report->status).'.');
}



    /** POST /admin/reports/{report}/quick-action (used by Blade JS) */
    public function quickAction(Request $request, Report $report)
{
    $data = $request->validate([
        'action' => ['required', Rule::in(array_merge(['approve','reject'], self::STATUSES))],
    ]);

    $action = $data['action'];

    // Approve → set in_progress + notify
    if ($action === 'approve') {
        $report->update([
            'status' => 'in_progress',
            'status_updated_at' => now(),
        ]);
        $this->notifyReportAuthor($report, 'approved');

        return response()->json(['status' => $report->status]);
    }

    // Reject (button or explicit) → notify then hard delete
    if ($action === 'reject' || $action === 'rejected') {
        $this->notifyReportAuthor($report, 'rejected');
        $report->delete();

        return response()->json(['deleted' => true]);
    }

    // Other statuses (pending | in_progress | resolved)
    $report->update([
        'status' => $action,
        'status_updated_at' => now(),
    ]);
    $this->notifyReportAuthor($report, $action);

    return response()->json(['status' => $report->status]);
}


    /** POST /admin/reports/{report}/assign */
    public function assignToMe(Request $request, Report $report)
    {
        $report->update([
            'assigned_to' => $request->user()->id,
            'assigned_at' => now(),
        ]);

        return response()->json(['assigned_to' => $report->assigned_to]);
    }

    /** POST /admin/reports/bulk-action */
    public function bulkAction(Request $request)
{
    $data = $request->validate([
        'action'       => ['required', Rule::in(array_merge(['approve','reject','delete'], self::STATUSES))],
        'report_ids'   => ['required','array','min:1'],
        'report_ids.*' => ['integer','exists:reports,id'],
    ]);

    $ids = $data['report_ids'];
    $action = $data['action'];
    $processed = 0;

    if ($action === 'reject') {
        // notify each, then delete
        $reports = Report::with('user')->whereIn('id', $ids)->get();
        foreach ($reports as $r) {
            $this->notifyReportAuthor($r, 'rejected');
        }
        $processed = Report::whereIn('id', $ids)->delete();

        return response()->json(['processed' => $processed, 'deleted' => true]);
    }

    if ($action === 'delete') {
        $processed = Report::whereIn('id', $ids)->delete();
        return response()->json(['processed' => $processed, 'deleted' => true]);
    }

    if ($action === 'approve') {
        $processed = Report::whereIn('id', $ids)->update([
            'status' => 'in_progress',
            'status_updated_at' => now(),
        ]);

        // optional: notify users about approval
        $reports = Report::with('user')->whereIn('id', $ids)->get();
        foreach ($reports as $r) {
            $this->notifyReportAuthor($r, 'approved');
        }

        return response()->json(['processed' => $processed, 'status' => 'in_progress']);
    }

    // direct bulk status set (pending | in_progress | resolved)
    $processed = Report::whereIn('id', $ids)->update([
        'status' => $action,
        'status_updated_at' => now(),
    ]);

    // optional: notify for other statuses
    $reports = Report::with('user')->whereIn('id', $ids)->get();
    foreach ($reports as $r) {
        $this->notifyReportAuthor($r, $action);
    }

    return response()->json(['processed' => $processed, 'status' => $action]);
}


    /** Notes (your routes expect these) */
    public function storeNote(Request $request, Report $report)
    {
        $data = $request->validate([
            'body' => ['required','string','max:5000'],
        ]);

        ReportNote::create([
            'report_id' => $report->id,
            'admin_id'  => $request->user()->id,
            'body'      => $data['body'],
        ]);

        return back()->with('success', 'Note added.');
    }

    public function destroyNote(Report $report, ReportNote $note)
    {
        abort_unless($note->report_id === $report->id, 404);
        $note->delete();

        return back()->with('success', 'Note deleted.');
    }
}
