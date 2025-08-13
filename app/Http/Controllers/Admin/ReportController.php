<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\ReportNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    // include all statuses used in UI
    private const STATUSES = ['pending', 'in_progress', 'resolved', 'rejected'];

    /** GET /admin/reports */
    public function index(Request $request)
    {
        $q        = trim((string) $request->query('q', ''));
        $city     = $request->query('city');
        $category = $request->query('category');
        $status   = $request->query('status');
        $from     = $request->query('from');
        $to       = $request->query('to');
        $sort     = $request->query('sort', 'newest');
        $perPage  = max(6, min(48, (int) $request->integer('per_page', 12)));

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

        // safe LIKE
        $like = fn(string $s) => '%' . str_replace(['\\','%','_'], ['\\\\','\\%','\\_'], $s) . '%';

        $reports = Report::query()
            ->with('user:id,name')
            ->when($q !== '', function ($qb) use ($q, $like) {
                $qb->where(function ($w) use ($q, $like) {
                    $w->where('title', 'like', $like($q))
                      ->orWhere('description', 'like', $like($q))
                      ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', $like($q)));
                });
            })
            ->when($city, fn($qb, $v) => $qb->where('city_corporation', $v))
            ->when($category, fn($qb, $v) => $qb->where('category', $v))
            ->when($status, fn($qb, $v) => $qb->where('status', $v))
            ->when($from, fn($qb, $v) => $qb->whereDate('created_at', '>=', $v))
            ->when($to,   fn($qb, $v) => $qb->whereDate('created_at', '<=', $v))
            ->when($sort === 'oldest',    fn($qb) => $qb->oldest())
            ->when($sort === 'status',    fn($qb) => $qb->orderBy('status')->latest('created_at'))
            ->when($sort === 'city',      fn($qb) => $qb->orderBy('city_corporation')->latest('created_at'))
            ->when($sort === 'category',  fn($qb) => $qb->orderBy('category')->latest('created_at'))
            ->when($sort === 'newest' || !$sort, fn($qb) => $qb->latest())
            ->paginate($perPage)
            ->withQueryString();

        $statuses = self::STATUSES; // pass to Blade

        return view('admin.reports.index', compact('reports', 'cities', 'categories', 'statuses'));
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

    /** PUT /admin/reports/{report}/status */
    public function updateStatus(Request $request, Report $report)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(self::STATUSES)],
        ]);

        $report->update(['status' => strtolower(trim($validated['status']))]);

        return $request->wantsJson()
            ? response()->json(['ok' => true, 'status' => $report->status, 'message' => 'Status updated.'])
            : back()->with('success', 'Status updated successfully.')->withFragment('status');
    }

    /** POST /admin/reports/{report}/notes */
    public function storeNote(Request $request, Report $report)
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'min:3'],
        ]);

        ReportNote::create([
            'report_id' => $report->id,
            'admin_id'  => $request->user()->id,
            'body'      => trim($data['body']),
        ]);

        return $request->wantsJson()
            ? response()->json(['ok' => true, 'message' => 'Note added.'])
            : back()->with('success', 'Note added.')->withFragment('notes');
    }

    /** DELETE /admin/reports/{report}/notes/{note} */
    public function destroyNote(Report $report, ReportNote $note)
    {
        abort_unless($note->report_id === $report->id, 404);
        $note->delete();

        return back()->with('success', 'Note removed.')->withFragment('notes');
    }
}
