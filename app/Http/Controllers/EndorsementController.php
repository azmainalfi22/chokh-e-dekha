<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Endorsement;
use Illuminate\Http\Request;

class EndorsementController extends Controller
{
    public function toggle(Request $request, Report $report)
    {
        $existing = Endorsement::where('report_id',$report->id)
            ->where('user_id',$request->user()->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $msg = 'You removed your endorsement.';
        } else {
            Endorsement::create([
                'report_id' => $report->id,
                'user_id'   => $request->user()->id,
            ]);
            $msg = 'Thanks for endorsing this report.';
        }

        // Optional: for AJAX, return JSON
        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'endorsed' => !$existing,
                'count' => Endorsement::where('report_id',$report->id)->count(),
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }
}
