<?php

// app/Http/Controllers/CommentController.php
namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Report $report)
    {
        // Must be logged in (route has auth middleware)
        $data = $request->validate([
            'body' => ['required','string','max:2000'],
        ]);

        $comment = $report->comments()->create([
            'user_id' => $request->user()->id,
            'body'    => $data['body'],
        ])->load('user');

        // Return JSON so fetch() sees 2xx and we can render the name like FB
        return response()->json([
            'ok'      => true,
            'id'      => $comment->id,
            'body'    => $comment->body,
            'name'    => $comment->user->name ?? 'User',
            'time'    => $comment->created_at->diffForHumans(),
        ], 201);
    }
}
