<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => NotificationResource::collection($notifications)->resolve(),
        ]);
    }

    public function unreadCount(Request $request)
    {
        return response()->json([
            'success' => true,
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markRead(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        if ($notification->read_at === null) {
            $notification->markAsRead();
            $notification->refresh();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
            'data' => (new NotificationResource($notification))->resolve(),
        ]);
    }

    public function markAllRead(Request $request)
    {
        $request->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
            'unread_count' => 0,
        ]);
    }
}
