<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::where('UtilisateurID', $request->user()->UtilisateurID)
            ->orderByDesc('DateCreation')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = Notification::where('UtilisateurID', $request->user()->UtilisateurID)
            ->where('EstLue', false)
            ->count();

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    public function markAsRead(Request $request, int $id): JsonResponse
    {
        Notification::where('NotificationID', $id)
            ->where('UtilisateurID', $request->user()->UtilisateurID)
            ->update(['EstLue' => true]);

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        Notification::where('UtilisateurID', $request->user()->UtilisateurID)
            ->where('EstLue', false)
            ->update(['EstLue' => true]);

        return response()->json(['success' => true, 'message' => 'Toutes les notifications marquées comme lues.']);
    }
}
