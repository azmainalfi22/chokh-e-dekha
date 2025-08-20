<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Report $report)
    {
        $request->validate([
            'body' => ['required','string','max:5000'],
        ]);

        $report->comments()->create([
            'user_id'  => $request->user()->id,
            'body'     => trim($request->body),
            'is_admin' => (bool) optional($request->user())->is_admin,
        ]);

        return back()->with('success','Comment posted.');
    }

    public function destroy(Request $request, Report $report, Comment $comment)
    {
        // allow: admins OR comment owner
        abort_unless(
            $request->user()->is_admin || $comment->user_id === $request->user()->id,
            403
        );

        $comment->delete();

        return back()->with('success','Comment deleted.');
    }
}
