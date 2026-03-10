<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Meeting;
use App\Models\DutyRoster;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function index()
    {
        return Inertia::render('Calendar/Index');
    }

    public function fetchEvents(Request $request)
    {
        $start = $request->query('start');
        $end = $request->query('end');
        $user = Auth::user();

        $eventsResponse = [];

        // 1. Fetch Global Church Events
        $query = Event::query();
        if ($start) $query->where('start_time', '>=', $start);
        if ($end) $query->where('start_time', '<=', $end);

        if ($user->hasRole(['Super_Admin', 'Pastor'])) {
            // See all
        } elseif ($user->hasRole(['Head_Of_Deacons', 'Deacon'])) {
            $query->whereIn('visibility', ['public', 'internal', 'leadership']);
        } elseif (Auth::check()) {
            $query->whereIn('visibility', ['public', 'internal']);
        } else {
            $query->where('visibility', 'public');
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
                    'visibility' => $event->visibility,
                ]
            ];
        }

        // 2. Fetch Module Meetings (Ban ngành)
        if (Auth::check()) {
            $meetingQuery = Meeting::with('department');
            if ($start) $meetingQuery->where('date', '>=', substr($start, 0, 10));
            if ($end) $meetingQuery->where('date', '<=', substr($end, 0, 10));
            
            // Lọc meeting theo quyền hạn
            if (!$user->hasRole(['Super_Admin', 'Pastor'])) {
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

        // 3. Fetch Duty Rosters (Phân công trực)
        if (Auth::check()) {
            $dutyQuery = DutyRoster::with(['meeting.department', 'user', 'role']);
            // Only fetch duties assigned to $user or if user is admin
            if (!$user->hasRole(['Super_Admin', 'Pastor', 'Head_Of_Deacons', 'Deacon'])) {
                $dutyQuery->where('user_id', $user->id);
            }
            
            $duties = $dutyQuery->get();
            
            foreach ($duties as $duty) {
                if (!$duty->meeting) continue;
                $startDateTime = $duty->meeting->date . 'T' . ($duty->meeting->start_time ?? '00:00:00');
                
                $eventsResponse[] = [
                    'id' => 'duty_' . $duty->id,
                    'title' => 'Trực: ' . ($duty->role->name ?? 'Nhiệm vụ'),
                    'start' => $startDateTime,
                    'allDay' => false,
                    'backgroundColor' => '#f59e0b', // Amber/Orange
                    'borderColor' => '#d97706',
                    'extendedProps' => [
                        'type' => 'duty',
                        'description' => "Nhân sự: {$duty->user->name}\nBuổi: " . ($duty->meeting->name ?? 'N/A') . "\nBan: " . ($duty->meeting->department->name ?? 'N/A'),
                        'db_id' => $duty->id,
                    ]
                ];
            }
        }

        return response()->json($eventsResponse);
    }

    public function store(Request $request) {
        $request->validate([
            'title' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'color' => 'nullable|string',
            'type' => 'required|string',
            'visibility' => 'required|string',
        ]);

        Event::create([
            'title' => $request->title,
            'description' => $request->description,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'is_all_day' => $request->is_all_day ?? false,
            'type' => $request->type,
            'color' => $request->color ?? '#3788d8',
            'location' => $request->location,
            'visibility' => $request->visibility,
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Đã thêm Sự kiện vảo Lịch Hội Thánh.');
    }
    
    public function update(Request $request, Event $event) {
        if (!Auth::user()->hasRole(['Super_Admin', 'Pastor']) && $event->created_by !== Auth::id()) {
            abort(403);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'start_time' => 'required|date',
        ]);

        $event->update($request->all());

        return redirect()->back()->with('success', 'Đã cập nhật Sự kiện.');
    }

    public function destroy(Event $event) {
        if (!Auth::user()->hasRole(['Super_Admin', 'Pastor']) && $event->created_by !== Auth::id()) {
            abort(403);
        }
        
        $event->delete();
        return redirect()->back()->with('success', 'Đã xóa Sự kiện khỏi Lịch.');
    }
}
