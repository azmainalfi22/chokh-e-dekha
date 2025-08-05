<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function index()
    {
        $reports = Report::latest()->get();
        return view('reports.index', compact('reports'));
    }

    public function create()
    {
        return view('reports.create');
    }

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

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('photos', 'public');
        }

        // Assign report details
        $report = new Report($validated);
        $report->user_id = Auth::id(); // make sure you're logged in!
        $report->save();

        return redirect('/')->with('success', 'Report submitted successfully!');
    }

    public function toggleStatus($id)
    {
        $report = Report::findOrFail($id);

        // Optional: check if user is admin here
        $report->status = !$report->status;
        $report->save();

        return redirect()->back()->with('success', 'Report status updated!');
    }
}
