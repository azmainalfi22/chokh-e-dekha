<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // <-- add

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $city = $request->query('city_corporation');

        $reports = Report::query()
            ->when($city, fn ($q) => $q->where('city_corporation', $city))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('reports.index', compact('reports', 'city'));
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
            'photo'            => 'nullable|image|max:4096', // 4MB and image mime
        ]);

        // ✅ Save to the *public* disk so we can serve it
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('reports', 'public'); // e.g. reports/abc.jpg
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
        abort_unless(
            $report->user_id === Auth::id() || (Auth::check() && Auth::user()->is_admin),
            403
        );

        $report->load('user');

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
