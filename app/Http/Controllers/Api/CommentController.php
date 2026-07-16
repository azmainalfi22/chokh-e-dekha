<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\ReportComment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    public function index(Report $report): JsonResponse
    {
        $comments = $report->comments()->with('user:id,name')->paginate(10);
        return response()->json($comments);
    }

    public function store(Request $request, Report $report): JsonResponse
    {
        $data = $request->validate([
            'body' => ['required','string','min:1','max:1000'],
            'parent_id' => ['nullable','exists:report_comments,id']
        ]);
        $comment = ReportComment::create([
            'report_id' => $report->id,
            'user_id' => $request->user()->id,
            'parent_id' => $data['parent_id'] ?? null,
            'body' => $data['body'],
        ])->load('user:id,name');
        return response()->json($comment, 201);
    }

    public function show(Report $report, ReportComment $comment): JsonResponse
    {
        abort_unless($comment->report_id === $report->id, 404);
        return response()->json($comment->load('user:id,name'));
    }

    public function update(Request $request, Report $report, ReportComment $comment): JsonResponse
    {
        abort_unless($comment->report_id === $report->id, 404);
        abort_unless($request->user()->id === $comment->user_id || ($request->user()->is_admin ?? false), 403);
        $data = $request->validate(['body' => ['required','string','min:1','max:1000']]);
        $comment->update(['body' => $data['body']]);
        return response()->json($comment);
    }

    public function destroy(Request $request, Report $report, ReportComment $comment): JsonResponse
    {
        abort_unless($comment->report_id === $report->id, 404);
        abort_unless($request->user()->id === $comment->user_id || ($request->user()->is_admin ?? false), 403);
        $comment->delete();
        return response()->json(['deleted' => true]);
    }

    // Placeholders for extended features
    public function toggleLike(): JsonResponse { return response()->json(['message' => 'Not implemented'], 501); }
    public function flag(): JsonResponse { return response()->json(['message' => 'Not implemented'], 501); }
    public function history(): JsonResponse { return response()->json(['message' => 'Not implemented'], 501); }
    public function togglePin(): JsonResponse { return response()->json(['message' => 'Not implemented'], 501); }
    public function flagged(): JsonResponse { return response()->json(['message' => 'Not implemented'], 501); }
    public function recent(): JsonResponse { return response()->json(['message' => 'Not implemented'], 501); }
    public function moderate(): JsonResponse { return response()->json(['message' => 'Not implemented'], 501); }
}


