<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\TenantUser;
use App\Models\UserNotification;

class NotificationController extends Controller
{
    /**
     * Resolve the current tenant user.
     */
    protected function resolveCurrentUser(): ?TenantUser
    {
        $user = Auth::guard('tenant')->user() ?? Auth::guard('web')->user() ?? auth()->user();
        if ($user instanceof TenantUser) {
            return $user;
        }
        if ($user && !empty($user->id)) {
            $found = TenantUser::on('tenant')->find($user->id);
            if ($found) {
                return $found;
            }
        }
        return null;
    }

    /**
     * List current user's notifications.
     */
    public function apiList(Request $request)
    {
        $user = $this->resolveCurrentUser();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'unread_count' => 0,
                'notifications' => [],
            ], 401);
        }

        $notifications = UserNotification::on('tenant')
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->take(50)
            ->get();

        $unreadCount = UserNotification::on('tenant')
            ->where('user_id', $user->id)
            ->where('read', false)
            ->count();

        $items = $notifications->map(function ($n) {
            return [
                'id' => (int)$n->id,
                'title' => $n->title,
                'message' => $n->message,
                'channel' => $n->channel ?: 'in_app',
                'read' => (bool)$n->read,
                'link_module' => $n->link_module ?: 'leads',
                'link_id' => $n->link_id ? (string)$n->link_id : null,
                'created_at' => $n->created_at ? $n->created_at->toIso8601String() : null,
                'time_ago' => $n->created_at ? $n->created_at->diffForHumans() : 'Just now',
            ];
        });

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
            'notifications' => $items,
        ]);
    }

    /**
     * Mark single notification as read.
     */
    public function apiMarkRead(Request $request, $id)
    {
        $user = $this->resolveCurrentUser();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        UserNotification::on('tenant')
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->update(['read' => true]);

        $unreadCount = UserNotification::on('tenant')
            ->where('user_id', $user->id)
            ->where('read', false)
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark all notifications as read for current user.
     */
    public function apiMarkAllRead(Request $request)
    {
        $user = $this->resolveCurrentUser();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        UserNotification::on('tenant')
            ->where('user_id', $user->id)
            ->where('read', false)
            ->update(['read' => true]);

        return response()->json([
            'success' => true,
            'unread_count' => 0,
            'message' => 'All notifications marked as read',
        ]);
    }
}
