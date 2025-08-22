<?php

namespace App\Http\Controllers;

use App\Models\Endorsement;
use App\Models\Report;
use Illuminate\Http\Request;

class EndorsementController extends Controller
{
    public function toggle(Request $request, Report $report)
    {
        $request->validate([], []); // placeholder in case you add rules later

        $userId = $request->user()->id;

        // Toggle: delete if exists, otherwise create.
        $existing = Endorsement::where('report_id', $report->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            $existing->delete();
            $endorsed = false;
        } else {
            Endorsement::create([
                'report_id' => $report->id,
                'user_id'   => $userId,
            ]);
            $endorsed = true;
        }

        // Fresh count
        $count = Endorsement::where('report_id', $report->id)->count();

        // If the UI asked for JSON (our index does), return JSON.
        if ($request->wantsJson()) {
            return response()->json([
                'ok'        => true,
                'endorsed'  => $endorsed,
                'count'     => $count,
                'report_id' => $report->id,
            ]);
        }

        // Otherwise, standard redirect back.
        return back()->with('success', 'Thanks for your feedback!');
    }
}
