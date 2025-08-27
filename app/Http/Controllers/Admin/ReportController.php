<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\ReportNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
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

    /** PUT/PATCH /admin/reports/{report}/status (used by named route admin.reports.status) */
    public function updateStatus(Request $request, Report $report)
    {
        $validated = $request->validate([
            'status' => ['required','string', Rule::in(self::STATUSES)],
        ]);

        $report->update([
            'status' => strtolower(trim($validated['status'])),
            'status_updated_at' => now(),
        ]);

        return response()->json(['status' => $report->status]);
    }

    /** POST /admin/reports/{report}/quick-action (used by Blade JS) */
    public function quickAction(Request $request, Report $report)
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(array_merge(['approve','reject'], self::STATUSES))],
        ]);

        $map = [
            'approve' => 'in_progress',
            'reject'  => 'rejected',
        ];
        $new = $map[$data['action']] ?? $data['action'];

        $report->update([
            'status' => $new,
            'status_updated_at' => now(),
        ]);

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

        $q = Report::query()->whereIn('id', $data['report_ids']);
        $processed = 0;

        switch ($data['action']) {
            case 'delete':
                $processed = (clone $q)->delete();
                break;
            case 'approve':
                $processed = (clone $q)->update([
                    'status' => 'in_progress',
                    'status_updated_at' => now(),
                ]);
                break;
            case 'reject':
                $processed = (clone $q)->update([
                    'status' => 'rejected',
                    'status_updated_at' => now(),
                ]);
                break;
            case 'resolved':
            case 'in_progress':
            case 'pending':
                $processed = (clone $q)->update([
                    'status' => $data['action'],
                    'status_updated_at' => now(),
                ]);
                break;
        }

        return response()->json(['processed' => $processed]);
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
