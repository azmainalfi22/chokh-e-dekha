<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // keep
use Illuminate\Support\Facades\Schema;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $q        = trim((string) $request->query('q', ''));
        $city     = $request->query('city_corporation');
        $category = $request->query('category');
        $status   = $request->query('status');
        $perPage  = max(6, min(48, (int) $request->integer('per_page', 12)));

        // NEW: sorting
        $sort      = $request->query('sort', 'newest');
        $sortAllow = ['newest','oldest','status','city','category'];
        if (!in_array($sort, $sortAllow, true)) {
            $sort = 'newest';
        }

        // Allowed statuses for dropdown + validation
        $statuses = ['pending','in_progress','resolved','rejected'];
        if ($status && !in_array($status, $statuses, true)) {
            $status = null; // ignore unknown values
        }

        // Distinct lists for filter dropdowns
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

        $like = fn(string $s) => '%' . str_replace(['\\','%','_'], ['\\\\','\\%','\\_'], $s) . '%';

        $reports = Report::query()
            ->with('user:id,name')
            ->when($q !== '', function ($qb) use ($q, $like) {
                $qb->where(function ($x) use ($q, $like) {
                    $x->where('title', 'like', $like($q))
                      ->orWhere('description', 'like', $like($q));
                });
            })
            ->when($city, fn ($qb, $v) => $qb->where('city_corporation', $v))
            ->when($category, fn ($qb, $v) => $qb->where('category', $v))
            ->when($status, fn ($qb, $v) => $qb->where('status', $v));

        // NEW: apply sort
        $reports = match ($sort) {
            'oldest'   => $reports->oldest(), // created_at asc
            'status'   => $reports->orderBy('status')->latest('id'),
            'city'     => $reports->orderBy('city_corporation')->latest('id'),
            'category' => $reports->orderBy('category')->latest('id'),
            default    => $reports->latest(), // newest first
        };

        $reports = $reports->paginate($perPage)->withQueryString();

        // For Blade compatibility
        $cat = $category;

        return view('reports.index', compact(
            'reports', 'q', 'city', 'category', 'cat',
            'cities', 'categories', 'status', 'statuses',
            'sort' // NEW
        ));
    }

    public function create()
    {
        if (Auth::user()?->is_admin) {
            abort(403, 'Admins cannot submit reports.');
        }

        return view('reports.create');
    }

    public function store(Request $request)
    {
        if (Auth::user()?->is_admin) {
            abort(403, 'Admins cannot submit reports.');
        }

        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'required|string',
            'category'         => 'required|string',
            'city_corporation' => 'required|string',
            'location'         => 'required|string',
            'photo'            => 'nullable|image|max:4096',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('reports', 'public');
        }

        $validated['user_id'] = Auth::id();
        $validated['status']  = 'pending';

        Report::create($validated);

        return redirect()->route('reports.index')->with('success', 'Report submitted successfully!');
    }

    public function myReports(Request $request)
    {
        $reports = Report::with('user')
            ->where('user_id', $request->user()->id)
            ->when($request->filled('city_corporation'),
                fn ($q) => $q->where('city_corporation', $request->city_corporation))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('reports.my', compact('reports'));
    }

    public function show(Report $report)
    {
        // Anyone logged in may view any report (read-only)
        if (!auth()->check()) {
            abort(403);
        }

        // Load relations; guard notes if table isn't migrated yet
        if (Schema::hasTable('report_notes')) {
            $report->load(['user', 'notes.admin']);
        } else {
            $report->load('user');
        }

        return view('reports.show', compact('report'));
    }

    public function adminShow(Report $report)
    {
        abort_unless(Auth::check() && Auth::user()->is_admin, 403);

        $report->load('user');

        return view('admin.reports.show', compact('report'));
    }

    public function update(Request $request, Report $report)
    {
        abort_unless(Auth::check() && Auth::user()->is_admin, 403);

        $validated = $request->validate([
            'status'     => 'required|in:pending,in_progress,resolved,rejected',
            'admin_note' => 'nullable|string|max:5000',
        ]);

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
}
