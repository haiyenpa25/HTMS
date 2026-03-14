<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Member;
use App\Models\CareRequest;
use App\Models\Notification;

class MemberPortalController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Tìm hồ sơ tín hữu liên kết với tài khoản
        $member = Member::where('user_id', $user->id)
            ->with([
                'departments:id,name,block',
                'household',
                'sensitiveInfo',
            ])
            ->first();

        // Các yêu cầu chăm sóc của tín hữu này
        $careRequests = [];
        if ($member) {
            $careRequests = CareRequest::where('user_id', $user->id)
                ->latest()
                ->take(5)
                ->get(['id', 'title', 'category', 'status', 'priority', 'created_at'])
                ->toArray();
        }

        // Thông báo chưa đọc
        $notifications = $user->unreadNotifications()
            ->take(5)
            ->get()
            ->map(fn($n) => [
                'id'      => $n->id,
                'title'   => $n->data['title'] ?? 'Thông báo',
                'message' => $n->data['message'] ?? '',
                'time'    => $n->created_at->diffForHumans(),
            ]);

        // Tin Tức Hội Thánh (Announcements) global hoặc theo ban ngành
        $userDepartments = $member ? $member->departments->pluck('id')->toArray() : [];
        $announcements = \App\Models\Announcement::where('scope_type', 'global')
            ->orWhere(function($q) use ($userDepartments) {
                $q->where('scope_type', 'department')
                  ->whereIn('scope_id', $userDepartments);
            })
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            })
            ->with('author:id,name')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'content' => $a->content,
                'author' => $a->author ? $a->author->name : 'Hệ thống',
                'time' => $a->created_at->diffForHumans(),
            ]);

        // Sự kiện tiếp theo (từ bảng meetings nếu có)
        $upcomingEvents = [];
        try {
            $upcomingEvents = \App\Models\Meeting::where('meeting_date', '>=', now())
                ->orderBy('meeting_date')
                ->take(3)
                ->get(['id', 'title', 'meeting_date', 'location', 'type'])
                ->toArray();
        } catch (\Exception $e) {
            // Bảng meetings không tồn tại hoặc lỗi khác
        }

        return Inertia::render('MemberPortal/Index', [
            'member'         => $member,
            'careRequests'   => $careRequests,
            'notifications'  => $notifications,
            'announcements'  => $announcements,
            'upcomingEvents' => $upcomingEvents,
            'careCategories' => [
                'prayer'     => 'Cầu nguyện',
                'visitation' => 'Thăm viếng',
                'counseling' => 'Tư vấn tâm linh',
                'help'       => 'Hỗ trợ vật chất',
                'other'      => 'Khác',
            ],
        ]);
    }

    public function submitCare(Request $request)
    {
        $validated = $request->validate([
            'category'   => 'required|string',
            'title'      => 'required|string|max:255',
            'content'    => 'required|string',
            'is_urgent'  => 'boolean',
            'is_private' => 'boolean',
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['status']       = 'pending';
        $validated['priority']     = isset($validated['is_urgent']) && $validated['is_urgent'] ? 'urgent' : 'normal';
        unset($validated['is_urgent']);

        CareRequest::create($validated);

        return back()->with('success', 'Yêu cầu của bạn đã được gửi thành công! Chúng tôi sẽ liên hệ sớm.');
    }
}
