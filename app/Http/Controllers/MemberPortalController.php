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
                'departments:id,name,block,color',
                'household',
                'sensitiveInfo',
            ])
            ->first();

        // Các yêu cầu chăm sóc của tín hữu này
        $careRequests = [];
        if ($member) {
            $careRequests = CareRequest::where('submitted_by', $user->id)
                ->latest()
                ->take(5)
                ->get(['id', 'title', 'category', 'status', 'is_urgent', 'created_at'])
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

        $validated['submitted_by'] = $request->user()->id;
        $validated['status']       = 'pending';

        CareRequest::create($validated);

        return back()->with('success', 'Yêu cầu của bạn đã được gửi thành công! Chúng tôi sẽ liên hệ sớm.');
    }
}
