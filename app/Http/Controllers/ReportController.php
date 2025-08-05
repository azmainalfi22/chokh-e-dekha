<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reports = Report::latest()->get();
        return view('reports.index', compact('reports'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('reports.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'category' => 'required',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos', 'public');
        }

        Report::create([
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'location' => $request->location,
            'photo' => $path,
        ]);

        return redirect('/')->with('success', 'Report submitted!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Report $report)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        //
    }
    /**
     * Toggle the status of the specified report.
     */
    public function toggleStatus($id)
    {
        if (!\Illuminate\Support\Facades\Auth::user()->is_admin) {
            abort(403, 'Unauthorized');
        }

        $report = Report::findOrFail($id);
        $report->status = $report->status === 'pending' ? 'resolved' : 'pending';
        $report->save();

        return redirect()->back()->with('success', 'Status updated!');
    }
}
