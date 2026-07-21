<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use App\Support\SidebarBadgeCounts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationPageController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    public function index(Request $request): View
    {
        $viewer = $request->user();
        $items = $this->notifications->allForUser($viewer);
        $this->notifications->markAllRead($viewer);

        return view('web.notifications.index', [
            'viewer' => $viewer,
            'items' => $items,
        ]);
    }

    public function badgeCounts(Request $request): JsonResponse
    {
        $counts = SidebarBadgeCounts::forUser($request->user());

        return response()->json([
            'success' => true,
            'data' => [
                'unread_messages' => $counts['messages'],
                'unread_notifications' => $counts['notifications'],
            ],
        ]);
    }

    public function poll(Request $request): JsonResponse
    {
        $viewer = $request->user();
        $since = null;

        if ($rawSince = $request->query('since')) {
            try {
                $since = \Carbon\Carbon::parse($rawSince);
            } catch (\Throwable) {
                $since = null;
            }
        }

        $items = $since
            ? $this->notifications->itemsSince($viewer, $since)
            : collect();

        $counts = SidebarBadgeCounts::forUser($viewer);

        return response()->json([
            'success' => true,
            'data' => [
                'unread_messages' => $counts['messages'],
                'unread_notifications' => $counts['notifications'],
                'html' => $items->isNotEmpty()
                    ? view('web.notifications.partials.list-items', ['items' => $items])->render()
                    : '',
                'latest_at' => now()->toIso8601String(),
                'retention_hours' => \App\Models\AdminBroadcast::TTL_HOURS,
            ],
        ]);
    }
}
