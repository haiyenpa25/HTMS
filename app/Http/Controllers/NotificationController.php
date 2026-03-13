<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Announcement;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Lấy danh sách thông báo cho Dropdown Vue
     */
    public function getList(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['notifications' => [], 'unreadCount' => 0]);

        $limit = $request->input('limit', 5);

        $systemNotifsQuery = $user->notifications();
        if ($limit !== 'all') {
            $systemNotifsQuery->limit((int)$limit);
        }
        
        $systemNotifs = $systemNotifsQuery->get()->map(function($n) {
            return [
                'id' => $n->id,
                'type' => 'system',
                'data' => $n->data,
                'created_at' => $n->created_at,
                'read_at' => $n->read_at,
            ];
        });

        $departmentIds = \DB::table('org_memberships')
            ->where('member_id', $user->member_id ?? 0)
            ->where('model_type', 'App\Models\Department')
            ->where('is_active', 1)
            ->pluck('model_id')
            ->toArray();

        // Subquery để nhận diện user đã đọc chưa
        $announcementsQuery = Announcement::where(function($query) use ($departmentIds) {
                $query->where('scope_type', 'global')
                ->orWhere(function($q) use ($departmentIds) {
                    $q->where('scope_type', 'department')
                      ->whereIn('scope_id', $departmentIds);
                });
            })
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->with(['readByUsers' => function($q) use ($user) {
                $q->where('users.id', $user->id);
            }])
            ->orderBy('created_at', 'desc');

        if ($limit !== 'all') {
            $announcementsQuery->limit((int)$limit);
        }

        $announcements = $announcementsQuery->get()->map(function($a) {
                return [
                    'id' => $a->id,
                    'type' => 'announcement',
                    'data' => [
                        'title' => $a->title,
                        'message' => mb_strimwidth(strip_tags($a->content), 0, 50, '...'),
                        'icon' => 'speakerphone',
                        'bg_color' => 'bg-purple-100',
                        'color' => 'text-purple-600',
                        'action_url' => null, // Hoặc link tới chi tiết
                        'content' => $a->content // Nguyên gốc cho Hộp Thư
                    ],
                    'created_at' => $a->created_at,
                    'read_at' => $a->readByUsers->first() ? $a->readByUsers->first()->pivot->read_at : null,
                ];
            });

        $merged = $systemNotifs->concat($announcements)->sortByDesc('created_at')->values();
        if ($limit !== 'all') {
            $merged = $merged->take((int)$limit);
        }

        // Fetch unread count reusing logic
        $systemUnreadCount = $user->unreadNotifications()->count();
        $announcementsUnreadCount = Announcement::where(function($query) use ($departmentIds) {
                $query->where('scope_type', 'global')
                ->orWhere(function($q) use ($departmentIds) {
                    $q->where('scope_type', 'department')
                      ->whereIn('scope_id', $departmentIds);
                });
            })
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->whereDoesntHave('readByUsers', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->count();

        return response()->json([
            'notifications' => $merged,
            'unreadCount' => $systemUnreadCount + $announcementsUnreadCount,
        ]);
    }

    /**
     * Màn hình chính Hộp Thư
     */
    public function index(Request $request)
    {
        return Inertia::render('Notifications/Index');
    }

    /**
     * Lấy số lượng thông báo chưa đọc kết hợp (System + Announcements)
     */
    public function getUnreadCount(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['unread_count' => 0]);
        
        // 1. System Notifications count
        $systemUnreadCount = $user->unreadNotifications()->count();
        
        // 2. Announcements count
        $departmentIds = \DB::table('org_memberships')
            ->where('member_id', $user->member_id ?? 0)
            ->where('model_type', 'App\Models\Department')
            ->where('is_active', 1)
            ->pluck('model_id')
            ->toArray();
            
        $announcementsUnreadCount = Announcement::where(function($query) use ($departmentIds) {
                $query->where('scope_type', 'global')
                ->orWhere(function($q) use ($departmentIds) {
                    $q->where('scope_type', 'department')
                      ->whereIn('scope_id', $departmentIds);
                });
            })
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->whereDoesntHave('readByUsers', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->count();

        return response()->json([
            'unread_count' => $systemUnreadCount + $announcementsUnreadCount,
        ]);
    }

    /**
     * Đánh dấu 1 thông báo là đã đọc
     */
    public function markAsRead(Request $request, $id)
    {
        $type = $request->input('type', 'system'); // 'system' or 'announcement'
        
        if ($type === 'system') {
            $notification = $request->user()->notifications()->find($id);
            if ($notification) {
                $notification->markAsRead();
            }
        } elseif ($type === 'announcement') {
            $request->user()->readAnnouncements()->syncWithoutDetaching([
                $id => ['read_at' => now()]
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Đánh dấu tất cả là đã đọc
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        
        // System
        $user->unreadNotifications->markAsRead();
        
        // Announcements
        $departmentIds = \DB::table('org_memberships')
            ->where('member_id', $user->member_id ?? 0)
            ->where('model_type', 'App\Models\Department')
            ->where('is_active', 1)
            ->pluck('model_id')
            ->toArray();

        $unreadAnnouncements = Announcement::where(function($query) use ($departmentIds) {
                $query->where('scope_type', 'global')
                ->orWhere(function($q) use ($departmentIds) {
                    $q->where('scope_type', 'department')
                      ->whereIn('scope_id', $departmentIds);
                });
            })
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->whereDoesntHave('readByUsers', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->pluck('id');

        if ($unreadAnnouncements->isNotEmpty()) {
            $syncData = [];
            foreach ($unreadAnnouncements as $id) {
                $syncData[$id] = ['read_at' => now()];
            }
            $user->readAnnouncements()->syncWithoutDetaching($syncData);
        }

        return back()->with('success', 'Đã đánh dấu đọc tất cả.');
    }
}
