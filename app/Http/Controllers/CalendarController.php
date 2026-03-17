<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Meeting;
use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function index()
    {
        $departments = Department::select('id', 'name')->get();
        return Inertia::render('Calendar/Index', [
            'departments' => $departments
        ]);
    }

    public function fetchEvents(Request $request)
    {
        $start = $request->query('start');
        $end = $request->query('end');
        $user = Auth::user();

        $eventsResponse = [];

        // 1. Fetch Global Church Events
        $query = Event::with('department');
        if ($start) $query->where('start_time', '>=', $start);
        if ($end) $query->where('start_time', '<=', $end);

        if ($user && $user->isSuperAdmin()) {
            // See all
        } elseif ($user && $user->isSuperAdmin()) {
            $query->whereIn('scope_type', ['global', 'internal', 'leadership', 'department']);
        } elseif (Auth::check()) {
            $userDepartments = $user->member ? $user->member->departments->pluck('id')->toArray() : [];
            $query->where(function ($q) use ($userDepartments, $user) {
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
        } else {
            $query->where('scope_type', 'global');
        }

        $events = $query->get();
        foreach ($events as $event) {
            $eventsResponse[] = [
                'id' => 'evt_' . $event->id,
                'title' => $event->title,
                'start' => $event->start_time->toIso8601String(),
                'end' => $event->end_time ? $event->end_time->toIso8601String() : null,
                'allDay' => $event->is_all_day,
                'backgroundColor' => $event->color,
                'borderColor' => $event->color,
                'extendedProps' => [
                    'type' => 'event',
                    'description' => $event->description,
                    'location' => $event->location,
                    'db_id' => $event->id,
                    'scope_type' => $event->scope_type,
                    'scope_id' => $event->scope_id,
                    'department_name' => $event->department ? $event->department->name : null,
                ]
            ];
        }

        // 2. Fetch Module Meetings (Ban ngành)
        if (Auth::check()) {
            $meetingQuery = Meeting::with('department');
            if ($start) $meetingQuery->where('date', '>=', substr($start, 0, 10));
            if ($end) $meetingQuery->where('date', '<=', substr($end, 0, 10));
            
            // Lọc meeting theo quyền hạn
            if (!$user->isSuperAdmin()) {
                // Tạm thời đơn giản: get all branch meetings if logged in (or filter by specific role if needed)
                // Implement advanced MAC here if required. Right now let everyone see the church's global branch schedule
            }
            
            $meetings = $meetingQuery->get();
            foreach ($meetings as $meeting) {
                $startDateTime = $meeting->date . 'T' . ($meeting->start_time ?? '00:00:00');
                $endDateTime = $meeting->date . 'T' . ($meeting->end_time ?? '23:59:59');
                
                $eventsResponse[] = [
                    'id' => 'mtg_' . $meeting->id,
                    'title' => ($meeting->department->name ?? 'Nhóm') . ': ' . $meeting->name,
                    'start' => $startDateTime,
                    'end' => $endDateTime,
                    'allDay' => false,
                    'backgroundColor' => '#10b981', // Emerald
                    'borderColor' => '#059669',
                    'extendedProps' => [
                        'type' => 'meeting',
                        'description' => "Ban: {$meeting->department->name}\nDiễn giả: " . ($meeting->speaker ? $meeting->speaker->name : ($meeting->speaker_name ?? 'N/A')),
                        'location' => $meeting->location,
                        'db_id' => $meeting->id,
                    ]
                ];
            }
        }

        // 3. Fetch Duty Assignments (Phân công trực)
        if (Auth::check()) {
            $dutyQuery = \App\Models\DutyAssignment::with(['meeting.department', 'member', 'role']);
            // Only fetch duties assigned to $user or if user is admin
            if (!$user->isSuperAdmin()) {
                $memberId = $user->member ? $user->member->id : -1;
                $dutyQuery->where('member_id', $memberId);
            }
            
            // Limit by date
            if ($start) {
                $dutyQuery->whereHas('meeting', function($q) use ($start) {
                    $q->where('date', '>=', date('Y-m-d', strtotime($start)));
                });
            }
            if ($end) {
                $dutyQuery->whereHas('meeting', function($q) use ($end) {
                    $q->where('date', '<=', date('Y-m-d', strtotime($end)));
                });
            }
            
            $duties = $dutyQuery->get();
            
            foreach ($duties as $duty) {
                if (!$duty->meeting) continue;
                $startDateTime = $duty->meeting->date . 'T' . ($duty->meeting->time ?? '00:00:00');
                
                $eventsResponse[] = [
                    'id' => 'duty_' . $duty->id,
                    'title' => 'Trực: ' . ($duty->role->name ?? 'Nhiệm vụ'),
                    'start' => $startDateTime,
                    'allDay' => false,
                    'backgroundColor' => '#10b981', // emerald-500
                    'borderColor' => '#059669', // emerald-600
                    'extendedProps' => [
                        'type' => 'duty',
                        'description' => "Nhân sự phụ trách: " . ($duty->member->full_name ?? 'N/A') . "\nBuổi: " . ($duty->meeting->topic ?? 'N/A') . "\nPhạm vi: " . ($duty->meeting->department->name ?? 'Chung HT'),
                        'db_id' => $duty->id,
                    ]
                ];
            }
        }

        return response()->json($eventsResponse);
    }

    public function store(Request $request) {
        $user = Auth::user();
        $isPersonal = $request->scope_type === 'personal';
        
        if (!$isPersonal && !$user->isSuperAdmin()) {
            abort(403, 'Chỉ có SuperAdmin mới có thể tạo sự kiện chung.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'color' => 'nullable|string',
            'type' => 'nullable|string',
            'scope_type' => 'required|string|in:global,internal,leadership,department,personal',
            'scope_id' => 'required_if:scope_type,department|nullable|exists:departments,id',
        ]);

        Event::create([
            'title' => $request->title,
            'description' => $request->description,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'is_all_day' => $request->is_all_day ?? false,
            'type' => $request->type ?? 'other',
            'color' => $request->color ?? '#8b5cf6',
            'location' => $request->location,
            'scope_type' => $request->scope_type,
            'scope_id' => $request->scope_type === 'department' ? $request->scope_id : null,
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Đã thêm Sự kiện vảo Lịch Hội Thánh.');
    }
    
    public function update(Request $request, Event $event) {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !($event->scope_type === 'personal' && $event->created_by === $user->id)) {
            abort(403, 'Bạn không có quyền chỉnh sửa sự kiện này.');
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'scope_type' => 'required|string|in:global,internal,leadership,department,personal',
            'scope_id' => 'required_if:scope_type,department|nullable|exists:departments,id',
        ]);

        $data = $request->all();
        if ($data['scope_type'] !== 'department') {
            $data['scope_id'] = null;
        }

        $event->update($data);

        return redirect()->back()->with('success', 'Đã cập nhật Sự kiện.');
    }

    public function destroy(Event $event) {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !($event->scope_type === 'personal' && $event->created_by === $user->id)) {
            abort(403, 'Bạn không có quyền xóa sự kiện này.');
        }
        
        $event->delete();
        return redirect()->back()->with('success', 'Đã xóa Sự kiện.');
    }
}
