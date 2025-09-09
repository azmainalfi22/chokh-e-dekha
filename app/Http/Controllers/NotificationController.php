<?php
// app/Http/Controllers/NotificationController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Mark a single notification as read
     */
    public function markRead(string $id)
    {
        $user = request()->user();
        $notification = $user->notifications()->findOrFail($id);
        
        if ($notification->read_at) {
            return response()->json(['message' => 'Already marked as read'], 400);
        }
        
        $notification->markAsRead();
        
        // Clear user's unread count cache
        Cache::forget("unread_notifications_count_{$user->id}");
        
        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'unread_count' => $user->unreadNotifications()->count()
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAll()
    {
        $user = request()->user();
        $unreadCount = $user->unreadNotifications()->count();
        
        if ($unreadCount === 0) {
            return response()->json(['message' => 'No unread notifications'], 400);
        }
        
        $user->unreadNotifications->markAsRead();
        
        // Clear cache
        Cache::forget("unread_notifications_count_{$user->id}");
        
        return response()->json([
            'success' => true,
            'message' => "Marked {$unreadCount} notifications as read",
            'unread_count' => 0
        ]);
    }

    /**
     * Clear (delete) a single notification
     */
    public function clear(string $id)
    {
        $user = request()->user();
        $notification = $user->notifications()->findOrFail($id);
        
        // Store if it was unread for response
        $wasUnread = is_null($notification->read_at);
        
        $notification->delete();
        
        // Clear cache
        Cache::forget("unread_notifications_count_{$user->id}");
        
        return response()->json([
            'success' => true,
            'message' => 'Notification removed',
            'was_unread' => $wasUnread,
            'unread_count' => $user->unreadNotifications()->count(),
            'total_count' => $user->notifications()->count()
        ]);
    }

    /**
     * Clear all notifications for the user
     */
    public function clearAll()
    {
        $user = request()->user();
        $totalCount = $user->notifications()->count();
        
        if ($totalCount === 0) {
            return response()->json(['message' => 'No notifications to clear'], 400);
        }
        
        $user->notifications()->delete();
        
        // Clear cache
        Cache::forget("unread_notifications_count_{$user->id}");
        
        return response()->json([
            'success' => true,
            'message' => "Cleared {$totalCount} notifications",
            'unread_count' => 0,
            'total_count' => 0
        ]);
    }

    /**
     * Get notifications with advanced filtering and real-time updates
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = $user->notifications()->latest();
        
        // Filter by read status
        if ($request->has('unread_only') && $request->boolean('unread_only')) {
            $query->whereNull('read_at');
        }
        
        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        // Search in notification data
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('data->title', 'like', "%{$search}%")
                  ->orWhere('data->message', 'like', "%{$search}%")
                  ->orWhere('data->status', 'like', "%{$search}%");
            });
        }
        
        $perPage = $request->integer('per_page', 15);
        $notifications = $query->paginate($perPage);
        
        // If AJAX request, return JSON
        if ($request->expectsJson()) {
            return response()->json([
                'notifications' => $notifications->items(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total()
                ],
                'unread_count' => $user->unreadNotifications()->count()
            ]);
        }
        
        return view('notifications.index', compact('notifications'));
    }

    /**
     * Get notification statistics
     */
    public function stats()
    {
        $user = request()->user();
        
        $stats = [
            'total' => $user->notifications()->count(),
            'unread' => $user->unreadNotifications()->count(),
            'today' => $user->notifications()->whereDate('created_at', today())->count(),
            'this_week' => $user->notifications()->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'this_month' => $user->notifications()->whereMonth('created_at', now()->month)->count()
        ];
        
        // Notification types breakdown
        $typeStats = $user->notifications()
            ->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get()
            ->mapWithKeys(function ($item) {
                $shortType = class_basename($item->type);
                return [$shortType => $item->count];
            });
        
        $stats['by_type'] = $typeStats;
        
        return response()->json($stats);
    }

    /**
     * Mark notifications as read in bulk
     */
    public function bulkMarkRead(Request $request)
    {
        $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'required|string'
        ]);
        
        $user = $request->user();
        $notifications = $user->notifications()
            ->whereIn('id', $request->notification_ids)
            ->whereNull('read_at')
            ->get();
        
        $notifications->markAsRead();
        
        Cache::forget("unread_notifications_count_{$user->id}");
        
        return response()->json([
            'success' => true,
            'message' => "Marked {$notifications->count()} notifications as read",
            'unread_count' => $user->unreadNotifications()->count()
        ]);
    }

    /**
     * Clear notifications in bulk
     */
    public function bulkClear(Request $request)
    {
        $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'required|string'
        ]);
        
        $user = $request->user();
        $count = $user->notifications()
            ->whereIn('id', $request->notification_ids)
            ->delete();
        
        Cache::forget("unread_notifications_count_{$user->id}");
        
        return response()->json([
            'success' => true,
            'message' => "Cleared {$count} notifications",
            'unread_count' => $user->unreadNotifications()->count(),
            'total_count' => $user->notifications()->count()
        ]);
    }

    /**
     * Get unread notification count (cached for performance)
     */
    public function unreadCount()
    {
        $user = request()->user();
        
        $count = Cache::remember(
            "unread_notifications_count_{$user->id}",
            now()->addMinutes(5),
            fn() => $user->unreadNotifications()->count()
        );
        
        return response()->json(['unread_count' => $count]);
    }

    /**
     * Real-time notification polling endpoint
     */
    public function poll(Request $request)
    {
        $user = $request->user();
        $lastPoll = $request->input('last_poll');
        
        $query = $user->notifications()->latest();
        
        // Only get notifications newer than last poll
        if ($lastPoll) {
            $query->where('created_at', '>', Carbon::parse($lastPoll));
        } else {
            // First poll - get recent notifications
            $query->limit(10);
        }
        
        $newNotifications = $query->get();
        
        return response()->json([
            'notifications' => $newNotifications,
            'unread_count' => $user->unreadNotifications()->count(),
            'last_poll' => now()->toISOString(),
            'has_new' => $newNotifications->isNotEmpty()
        ]);
    }

    /**
     * Snooze a notification (mark as read and set a reminder)
     */
    public function snooze(Request $request, string $id)
    {
        $request->validate([
            'snooze_until' => 'required|date|after:now'
        ]);
        
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($id);
        
        // Mark as read for now
        $notification->markAsRead();
        
        // Store snooze data in the notification data
        $data = $notification->data;
        $data['snoozed_until'] = $request->snooze_until;
        $data['original_read_at'] = $notification->read_at;
        
        $notification->update(['data' => $data]);
        
        Cache::forget("unread_notifications_count_{$user->id}");
        
        return response()->json([
            'success' => true,
            'message' => 'Notification snoozed',
            'snooze_until' => $request->snooze_until
        ]);
    }

    /**
     * Archive old notifications
     */
    public function archiveOld(Request $request)
    {
        $user = $request->user();
        $days = $request->integer('days', 30);
        
        $count = $user->notifications()
            ->where('created_at', '<', now()->subDays($days))
            ->where('read_at', '!=', null) // Only archive read notifications
            ->delete();
        
        Cache::forget("unread_notifications_count_{$user->id}");
        
        return response()->json([
            'success' => true,
            'message' => "Archived {$count} old notifications",
            'total_count' => $user->notifications()->count()
        ]);
    }
}