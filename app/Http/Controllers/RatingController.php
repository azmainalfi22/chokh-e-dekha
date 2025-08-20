<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Report;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request, Report $report)
    {
        $data = $request->validate([
            'score' => ['required','integer','min:1','max:5'],
        ]);

        // Upsert one rating per user per report
        $report->ratings()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['score'   => $data['score']]
        );

        return back()->with('success', 'Rating submitted.');
    }
}
