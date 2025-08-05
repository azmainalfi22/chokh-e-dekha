<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Display a list of reports, optionally filtered by city.
     */
    public function index(Request $request)
    {
        $city = $request->input('city_corporation');

        $reports = Report::when($city, function ($query, $city) {
            return $query->where('city_corporation', $city);
        })->latest()->get();

        return view('reports.index', compact('reports', 'city'));
    }

    /**
     * Show the form to create a new report.
     */
    public function create()
    {
        return view('reports.create');
    }

    /**
     * Store a newly submitted report.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'city_corporation' => 'required|string',
            'location' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
        ]);

        // Upload photo if provided
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('photos', 'public');
        }

        // Assign user ID and default status
        $validated['user_id'] = Auth::id();
        $validated['status'] = 'pending';

        // Create report
        Report::create($validated);

        // ✅ Redirect to defined route
        return redirect()->route('reports.index')->with('success', 'Report submitted successfully!');
    }

    public function myReports()
    {
        $reports = Report::where('user_id', Auth::id())->latest()->get();
        return view('reports.my', compact('reports'));
    }

    /**
     * Admin can toggle a report's status.
     */

    public function toggleStatus($id)
    {
        $report = Report::findOrFail($id);

        // Only admin can toggle
        if (!Auth::user()->is_admin) {
            abort(403, 'Unauthorized');
        }

        $report->status = $report->status === 'pending' ? 'resolved' : 'pending';
        $report->save();

        return redirect()->back()->with('success', 'Report status updated!');
    }
}
