<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportComment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreReportCommentRequest;

class CommentController extends Controller
{
    public function index(Report $report)
    {
        $comments = $report->comments()
            ->with('user:id,name')
            ->latest()
            ->paginate(10);

        // NOTE: pass $report so the partial can print correct data-* attrs
        return view('partials.comments._list', compact('comments', 'report'))->render();
    }

    public function store(StoreReportCommentRequest $request, Report $report)
    {
        $comment = $report->comments()->create([
            'user_id' => auth()->id(),
            'body'    => $request->validated()['body'],
        ])->load('user:id,name');

        // render a single comment row
        $html  = view('partials.comments._item', ['comment' => $comment, 'report' => $report])->render();
        $count = $report->comments()->count();

        return response()->json([
            'success'        => true,     // JS checks this
            'comment'        => $html,    // JS expects 'comment' not 'html'
            'comments_count' => $count,   // used to live-update button count
        ]);
    }

    public function destroy(Report $report, ReportComment $comment)
    {
        // extra guard: only delete if this comment belongs to this report
        if ($comment->report_id !== $report->id) {
            abort(404);
        }

        // Option 1: Use Gate facade instead of $this->authorize
        if (!Gate::allows('destroy', $comment)) {
            abort(403, 'Unauthorized');
        }
        
        // Alternative Option 2 (simpler): Direct permission check
        // if (auth()->id() !== $comment->user_id && !auth()->user()->is_admin) {
        //     abort(403, 'Unauthorized');
        // }

        $comment->delete();

        return response()->json([
            'success'        => true,                         // JS checks this
            'comments_count' => $report->comments()->count(), // update count
        ]);
    }
}