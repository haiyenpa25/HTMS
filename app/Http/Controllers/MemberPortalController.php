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
                'household.members',
                'sensitiveInfo',
                'memberships.model',
                'memberships.role',
                'talents',
                'attendances' => function($q) {
                    $q->latest('created_at')->take(1)->with('meeting:id,date');
                },
                'visitations' => function($q) {
                    $q->latest('visit_date')->with('visitors:id,full_name');
                }
            ])
            ->first();

        $hasPortalAccess = $user->isSuperAdmin() || \DB::table('user_department_features')->where('user_id', $user->id)->exists();

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

        // Sự kiện tiếp theo (từ bảng meetings nếu có & bảng events)
        $upcomingEvents = [];
        $userDepartments = $member ? $member->departments->pluck('id')->toArray() : [];
        try {
            $meetings = \App\Models\Meeting::where('date', '>=', now()->toDateString())
                ->orderBy('date')
                ->take(3)
                ->get()
                ->map(fn($m) => [
                    'id' => 'mtg_'.$m->id,
                    'title' => $m->topic ?: 'Buổi nhóm',
                    'meeting_date' => $m->date . ' ' . $m->time,
                    'location' => null,
                    'type' => $m->type,
                ])
                ->toArray();
                
            $eventsQuery = \App\Models\Event::where('start_time', '>=', now());
            $eventsQuery->where(function ($q) use ($userDepartments, $user) {
                $q->whereIn('scope_type', ['global', 'internal'])
                  ->orWhere(function ($subQ) use ($userDepartments) {
                      $subQ->where('scope_type', 'department')
                           ->whereIn('scope_id', $userDepartments);
                  })
                  ->orWhere(function ($subQ) use ($user) {
                      $subQ->where('scope_type', 'personal')
                           ->where('created_by', $user->id);
                  });
            });
            $events = $eventsQuery->orderBy('start_time')->take(3)->get()->map(function($e) {
                return [
                    'id' => 'evt_'.$e->id,
                    'raw_id' => $e->id,
                    'title' => $e->title,
                    'meeting_date' => $e->start_time->format('Y-m-d H:i:s'),
                    'location' => $e->location,
                    'type' => $e->type,
                    'scope_type' => $e->scope_type, // pass scope type to allow delete validation on frontend
                ];
            })->toArray();
            
            $upcomingEvents = array_merge($meetings, $events);
            
            // Lấy Lịch phân công (Duty Assignments) của Tín hữu
            if ($member) {
                $duties = \App\Models\DutyAssignment::with(['meeting.department', 'role'])
                    ->where('member_id', $member->id)
                    ->whereHas('meeting', function($q) {
                        $q->where('date', '>=', now()->toDateString());
                    })
                    ->get()
                    ->map(function($duty) {
                        return [
                            'id' => 'duty_'.$duty->id,
                            'raw_id' => $duty->id,
                            'title' => '[Phân công] - ' . ($duty->role->name ?? 'Nhiệm vụ'),
                            'meeting_date' => $duty->meeting->date . ' ' . ($duty->meeting->time ?? '00:00:00'),
                            'location' => $duty->meeting->department->name ?? 'Hội Thánh',
                            'type' => 'training', // Sử dụng màu xanh emerald (badge training)
                            'status' => $duty->status,
                            'reason' => $duty->reason,
                        ];
                    })->toArray();
                $upcomingEvents = array_merge($upcomingEvents, $duties);
            }

            usort($upcomingEvents, function($a, $b) {
                return strtotime($a['meeting_date']) - strtotime($b['meeting_date']);
            });
            $upcomingEvents = array_slice($upcomingEvents, 0, 5); // Hiển thị 5 sự kiện cho phong phú
        } catch (\Exception $e) {
            // Error merging events
        }

        // Lấy sự kiện toàn bộ lịch trình tháng để chấm màu (Dots) trên giao diện Lịch
        $monthEvents = [];
        try {
            $monthEventsQuery = clone $eventsQuery; // $eventsQuery is already built with permission scopes
            $monthEventsList = $monthEventsQuery->get()->map(function($e) {
                return [
                    'start' => $e->start_time->format('Y-m-d H:i:s'),
                ];
            })->toArray();
            
            $monthEvents = array_merge($monthEvents, $monthEventsList);
            
            if ($member) {
                $monthDuties = \App\Models\DutyAssignment::with('meeting')
                    ->where('member_id', $member->id)
                    ->get()
                    ->map(function($duty) {
                        return [
                            'start' => $duty->meeting->date . ' ' . ($duty->meeting->time ?? '00:00:00'),
                        ];
                    })->toArray();
                $monthEvents = array_merge($monthEvents, $monthDuties);
            }
        } catch (\Exception $e) {}


        return Inertia::render('MemberPortal/Index', [
            'member'         => $member,
            'careRequests'   => $careRequests,
            'notifications'  => $notifications,
            'announcements'  => $announcements,
            'upcomingEvents' => $upcomingEvents,
            'monthEventsData' => $monthEvents,
            'careCategories' => [
                'prayer'     => 'Cầu nguyện',
                'visitation' => 'Thăm viếng',
                'counseling' => 'Tư vấn tâm linh',
                'help'       => 'Hỗ trợ vật chất',
                'other'      => 'Khác',
            ],
            'hasPortalAccess' => $hasPortalAccess,
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
