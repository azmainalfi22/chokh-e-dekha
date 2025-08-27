<?php

// app/Http/Controllers/CommentController.php
namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

use App\Models\Comment;

class CommentController extends Controller
{
    public function store(Request $request, Report $report)
    {
        // Must be logged in (route should use auth middleware)
        $validated = $request->validate([
            'body' => ['required','string','max:2000'],
        ]);

        // Trim so "   " doesn't pass validation in practice
        $body = (string) Str::of($validated['body'])->trim();
        if ($body === '') {
            $errors = ['body' => ['Comment can’t be empty.']];
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            return back()->withErrors($errors)->withInput();
        }

        try {
            $comment = DB::transaction(function () use ($request, $report, $body) {
                return $report->comments()->create([
                    'user_id' => $request->user()->id,
                    'body'    => $body,
                ])->load('user:id,name');
            });

            $payload = [
                'ok'   => true,
                'id'   => $comment->id,
                'body' => $comment->body,
                'name' => $comment->user->name ?? 'User',
                'time' => $comment->created_at->diffForHumans(),
            ];

            // If it’s an AJAX/JSON request, reply JSON 201; otherwise redirect back with a flash
            if ($request->expectsJson()) {
                return response()->json($payload, Response::HTTP_CREATED);
            }

            return back()->with('status', 'Comment posted.');
        } catch (\Throwable $e) {
            Log::error('Comment post failed', [
                'report_id' => $report->id,
                'user_id'   => optional($request->user())->id,
                'error'     => $e->getMessage(),
            ]);

            $message = 'Failed to post comment. Please try again.';
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $message], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            return back()->with('error', $message)->withInput();
        }
    }
    public function destroy(Request $request, Report $report, Comment $comment)
    {
        // 404 if the comment doesn't belong to the report (safety even without scoped bindings)
        if ($comment->report_id !== $report->id) {
            abort(404);
        }

        // Only the comment owner or an admin can delete
        $user = $request->user();
        if (! $user || ($user->id !== $comment->user_id && ! $user->is_admin)) {
            abort(403);
        }

        $comment->delete();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Comment deleted.');
    }
}
