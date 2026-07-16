<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = $user->notifications()->latest();
        if ($request->boolean('unread_only')) $query->whereNull('read_at');
        return response()->json($query->paginate($request->integer('per_page', 15)));
    }

    public function poll(Request $request)
    {
        $user = $request->user();
        $lastPoll = $request->input('last_poll');
        $query = $user->notifications()->latest();
        if ($lastPoll) $query->where('created_at', '>', Carbon::parse($lastPoll));
        else $query->limit(10);
        $new = $query->get();
        return response()->json([
            'notifications' => $new,
            'unread_count' => $user->unreadNotifications()->count(),
            'last_poll' => now()->toISOString(),
            'has_new' => $new->isNotEmpty(),
        ]);
    }

    public function stats(Request $request)
    {
        $u = $request->user();
        $stats = [
            'total' => $u->notifications()->count(),
            'unread' => $u->unreadNotifications()->count(),
        ];
        return response()->json($stats);
    }

    public function unreadCount(Request $request)
    {
        $u = $request->user();
        $count = Cache::remember("unread_notifications_count_{$u->id}", now()->addMinutes(5), fn() => $u->unreadNotifications()->count());
        return response()->json(['unread_count' => $count]);
    }

    public function markRead(Request $request, string $id)
    {
        $u = $request->user();
        $n = $u->notifications()->findOrFail($id);
        $n->markAsRead();
        Cache::forget("unread_notifications_count_{$u->id}");
        return response()->json(['ok' => true]);
    }

    public function clear(Request $request, string $id)
    {
        $u = $request->user();
        $u->notifications()->findOrFail($id)->delete();
        Cache::forget("unread_notifications_count_{$u->id}");
        return response()->json(['ok' => true]);
    }

    public function snooze()
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }

    public function bulkMarkRead(Request $request)
    {
        $request->validate(['notification_ids' => 'required|array']);
        $u = $request->user();
        $u->notifications()->whereIn('id', $request->notification_ids)->update(['read_at' => now()]);
        Cache::forget("unread_notifications_count_{$u->id}");
        return response()->json(['ok' => true]);
    }

    public function bulkClear(Request $request)
    {
        $request->validate(['notification_ids' => 'required|array']);
        $u = $request->user();
        $u->notifications()->whereIn('id', $request->notification_ids)->delete();
        Cache::forget("unread_notifications_count_{$u->id}");
        return response()->json(['ok' => true]);
    }

    public function clearAll(Request $request)
    {
        $u = $request->user();
        $u->notifications()->delete();
        Cache::forget("unread_notifications_count_{$u->id}");
        return response()->json(['ok' => true]);
    }

    public function archiveOld()
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }
}


